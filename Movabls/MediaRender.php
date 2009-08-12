<?php
/**
 * Media Renderer Class - Renders media templating system into PHP code
 * @author Travis Hardman
 */
class Movabls_MediaRender {

    public $output; //final output
    private $available; //array of available tags in the interface
    private $intags; //array of {x} tags taken from the view
    private $outtags; //array of <? x ? > tags to put into the output
    private $string; //string format to use for the final vsprintf

    /**
     * Constructor function renders the view and puts it in the output variable
     * @param $view = view content
     * @param $available = available tags in interface
     */
    public function __construct($view,$available) {
	 $this->intags = array();
	 $view = str_replace ('%', '%%',$view);
	 $view = str_replace ('<?', "<? echo '<?'; %1\$s",$view);
	 $view = str_replace ('?>', "<? echo '?>'; ?>",$view);
	 $view = sprintf($view,'?>');
	 $view = str_replace ('%', '%%',$view);
        $this->extract_tags($view);
        $this->available = $available;
        foreach ($this->intags as $tag) {
            $this->outtags[] = $this->render_tag($tag);
        }
        $this->output = vsprintf($this->string,$this->outtags);
    }

    /**
     * Extracts tags from the view template, then saves a string format to this->string
     * and the tags to this->intags
     * @param $view = view content
     */
    private function extract_tags($view) {
        //have to do these one at a time in order to test for "\" escape character and deal with that
        while(preg_match("/[^\\\]{{.*?}}/",$view,$tag) > 0) { //matches .{x} but not \{x}
            $this->intags[] = substr($tag[0],1);
            $fill = substr($tag[0],0,1);
            $view = preg_replace("/[^\\\]{{.*?}}/",$fill.'%s',$view,1);
        }
        $view = str_replace('\{{','{{',$view);
        $this->string = $view;
    }

    /**
     * Extracts string definitions from a string of code and replaces them with %s
     * @param $string = string of code
     * @return array with two keys - string=>code,array=>removed
     */
    private function extract_strings($string) {
        $array = array();
        //extract strings to account for spaces and parentheses in them
        $string = str_replace('%s','%%s',$string);
        while(preg_match('/[^\\\]".*?[^\\\]"/',$string,$seg) > 0) { //matches ."x" and "x\"y\"" but not \"x"
            $array[] = substr($seg[0],1);
            $fill = substr($seg[0],0,1);
            $string = preg_replace('/[^\\\]".*?[^\\\]"/',$fill.'%s',$string,1);
        }
        return array('string'=>$string,'array'=>$array);
    }

    /**
     * Renders a single {x} tag into php code
     * @param $tag = the tag content from the template
     * @return php code
     */
    private function render_tag($tag) {
        $tag = trim($tag," \t\n\r\0\x0B{}");
        //determine tag type
        if ($tag == 'else')
            return '<?php else: ?>';
        elseif ($tag == 'endif')
            return '<?php endif; ?>';
        elseif ($tag == 'endfor')
            return '<?php endforeach; ?>';
        elseif (strpos($tag,'if') === 0) {
            $tag = substr($tag,2);
            return '<?php if ('.$this->render_if(trim($tag)).'): ?>';
        }
        elseif (strpos($tag,'else if') === 0) {
            $tag = substr($tag,7);
            return '<?php elseif ('.$this->render_if(trim($tag)).'): ?>';
        }
        elseif (strpos($tag,'for') === 0) {
            $tag = substr($tag,3);
            return '<?php foreach ('.$this->render_for(trim($tag)).'): ?>';
        }
        else
            return "<?php print_r(".$this->variable($tag)."); ?>";
    }

    /**
     * Renders the conditional statement for an if into PHP code
     * @param $tag = conditional statement
     * @return php code
     */
    private function render_if ($tag) {
        $return = '';
        $extract_strings = $this->extract_strings($tag);
        $tag = $extract_strings['string'];
        $strings = $extract_strings['array'];
        while (strlen($tag) > 0) {
        //if it's a parenthetical, remove it and render it seperately
            if (substr($tag,0,1) == '(') {
                $open = 0;
                for ($i=0;$i<strlen($tag);$i++) {
                    $sub = substr($tag,$i,1);
                    if ($sub == '(')
                        $open++;
                    elseif ($sub == ')')
                        $open--;
                    if ($open === 0) {
                        $expr = substr($tag,1,$i-1);
                        $tag = trim(substr($tag,$i+1));
                        break;
                    }
                }
                $return .= '('.$this->render_if(vsprintf($expr,$strings)).')';
            }
            //if it's not a parenthetical, it's a value op value set
            else {
            //First piece of the string is a variable or value
                $space = strpos($tag,' ');
                $first = substr($tag,0,$space);
                if (strpos($first,'%s') !== false) {
                    $numstrings = preg_match_all('/[^%]?%s/',$first,$matches);
                    $first = vsprintf($first,$strings);
                    for($i=1;$i<=$numstrings;$i++)
                        array_shift($strings);
                }
                $first = $this->variable($first);
                $tag = trim(substr($tag,$space));
                //Second piece of the string is the operator
                $in = false;
                switch($op = substr($tag,0,strpos($tag,' '))) {
                    case '=':
                        $return .= "$first === ";
                        break;
                    case 'in':
                        $return .= " in_array($first,";
                        $in = true;
                        break;
                    case '!in':
                        $return .= " !in_array($first,";
                        $in = true;
                        break;
                    default:
                        $return .= "$first $op ";
                        break;
                }
                $tag = trim(substr($tag,strpos($tag,' ')));
                //Third piece of the string is a variable or value
                $space = strpos($tag,' ');
                if ($space === false)
                    $space = strlen($tag);
                $third = substr($tag,0,$space);
                if (strpos($third,'%s') !== false) {
                    $numstrings = preg_match_all('/[^%]?%s/',$third,$matches);
                    $third = vsprintf($third,$strings);
                    for($i=1;$i<=$numstrings;$i++)
                        array_shift($strings);
                }
                $return .= $this->variable($third);
                $tag = trim(substr($tag,$space));
                if ($in === true)
                    $return .= ',true)';
            }
            //Check for conjunction
            if (strlen($tag) > 0) {
                switch ($conj = substr($tag,0,3)) {
                    case 'and':
                        $return .= ' and ';
                        break;
                    case 'or ':
                        $return .= ' or ';
                        break;
                    case 'xor':
                        $return .= ' xor ';
                        break;
                    default:
                        throw new Exception ('Incorrect conjunction: '.$conj,500);
                        break;
                }
                $tag = trim(substr($tag,strpos($tag,' ')));
            }
        }
        return $return;
    }

    /**
     * Renders the parenthetical for a foreach statement into PHP code
     * @param $tag = parenthetical
     * @return php code
     */
    private function render_for ($tag) {
        $return = '';
        $return .= $this->variable(substr($tag,0,strpos($tag,' ')));
        $tag = trim(substr($tag,strpos($tag,' ')));
        if (substr($tag,0,3) !== 'as ')
            throw new Exception ("Media could not be compiled: Improper for statement",500);
        $return .= ' as $';
        $tag = trim(substr($tag,2));
        //Note: under all circumstances, add new variables to available array so they validate later
        if (($length = strpos($tag,':')) === false) {
            if (!$this->valid_name($tag))
                throw new Exception ("Improper variable name: '$tag'",500);
            $this->available[] = $tag;
            return $return.$tag;
        }
        $key = trim(substr($tag,0,$length));
        if (!$this->valid_name($key))
            throw new Exception ("Improper variable name: '$key'",500);
        $this->available[] = $key;
        $return .= $key;
        $tag = trim(substr(trim(substr($tag,$length)),1));
        if (!$this->valid_name($tag))
            throw new Exception ("Improper variable name: '$tag'",500);
        $this->available[] = $tag;
        return $return.' => $'.$tag;
    }

    /**
     * Checks a value or to see if it's included in the interface, then renders it in PHP
     * @param $var = value or tag
     * @return php code
     */
    private function variable ($var) {
    //If variable is actually a number or boolean, keep it as is
        if ($var === 'true' || $var === 'false' || preg_match('/[^0-9\.]/',$var) === 0)
            return $var;
        //If variable is a string, escape $ signs and return as is
        if (substr($var,0,1) === '"' && substr($var,strlen($var)-1,1) === '"')
            return str_replace('$','\$',$var);
        //If variable is an array, run each item through this function and return the array
        if (substr($var,0,1) === '(' && substr($var,strlen($var)-1,1) === ')') {
            $var = substr($var,1);
            $var = substr($var,0,strlen($var)-1);
            $array = explode(',',$var);
            foreach ($array as $key => $value)
                $array[$key] = $this->variable(trim($value));
            return "array(".implode(',',$array).")";
        }
        //If variable is an element in an array, render all of the [] pieces as separate variables
        if (strpos($var,'[') !== false) {
            $return = $this->variable(substr($var,0,strpos($var,'[')));
            $var = substr($var,strpos($var,'['));
            $extract_strings = $this->extract_strings($var);
            $var = $extract_strings['string'];
            $strings = $extract_strings['array'];
            while (strpos($var,'[') !== false) {
                $open = 0;
                for ($i=0;$i<strlen($var);$i++) {
                    $sub = substr($var,$i,1);
                    if ($sub == '[')
                        $open++;
                    elseif ($sub == ']')
                        $open--;
                    if ($open === 0) {
                        $expr = substr($var,1,$i-1);
                        $numstrings = preg_match_all('/[^%]%s/',$expr,$matches);
                        $var = trim(substr($var,$i+1));
                        break;
                    }
                }
                $return .= '['.$this->variable(vsprintf($expr,$strings)).']';
                for($i=1;$i<=$numstrings;$i++)
                    array_shift($strings);
            }
            return $return;
        }
        if (!in_array($var,$this->available))
            throw new Exception("Media could not be compiled: tag \"$var\" not recognized",500);//TODO: Line number? Or something...
        return '$'.$var;
    }

    /**
     * Checks a variable name to make sure it's kosher in PHP
     * @param $var = variable name
     * @return boolean
     */
    private function valid_name ($var) {
        if (preg_match('/[^-_a-zA-Z0-9]/',$var) > 0)
            return false;
        if (preg_match('/[0-9]/',substr($var,0,1)) > 0)
            return false;
        else
            return true;
    }

}
?>
