<?php
/**
 * Core Movabls Class - Instantiating this class runs the system
 * @author Travis Hardman
 */
class Movabls_Run {

    private $mvsdb; //MySQLi database handle
    private $media; //Media lambda handles
    private $functions; //Function lambda handles
    private $places; //Place urls
    private $interfaces; //Interface objects
    private $stack; //Current running stack

    public function __construct() {

        try {

            //Set up error reporting
            error_reporting(E_ALL);
            ini_set('display_errors',0);
            set_error_handler(array($this,'error_handler'));
            register_shutdown_function(array($this,'shutdown_handler'));

            //Run it!
            $this->mvsdb = new mysqli('localhost','root','h4ppyf4rmers','db_filet');
            print_r($this->run_place());
            
        }
        catch (Exception $e) {
            
            $this->error_handler('Exception',$e->getMessage(),$e->getFile(),$e->getLine(),$e->getCode());
                       
        }

    }

    /**
     * Runs a place and returns the output
     * @param string $url
     * @param bool $original = whether this is the url sent by the client
     * @return output
     */
    private function run_place($url = null) {

        //Instantiate / reset containers
        $this->media = new StdClass();
        $this->functions = new StdClass();
        $this->places = new StdClass();
        $this->interfaces = new StdClass();
        $this->stack = new Movabls_Stack();

        if (empty($url)) {
            if (strpos($GLOBALS->_SERVER['REQUEST_URI'],'?') !=0)
                $url = substr($GLOBALS->_SERVER['REQUEST_URI'],0,strpos($GLOBALS->_SERVER['REQUEST_URI'],'?'));
            else
                $url =$GLOBALS->_SERVER['REQUEST_URI'];
        }
        $place = $this->get_place($url);

        //We're about to execute user code, so we need to lock globals now that we're done with it.
        $GLOBALS->lock();
        
        if (!empty($place->interface_GUID))
            $this->get_interface($place->interface_GUID);
        else
            $place->interface_GUID = null;
        $this->select_movabls($place->media_GUID);
        
        //Add to stack and run
        $info = array(
            'movabl_type' => 'media',
            'movabl_GUID' => $place->media_GUID
        );
        $this->stack->push($info);
        $output = $this->run_movabl('media',$place->media_GUID,$place->interface_GUID);
        $this->stack->pop();

        //Set the content-type to that of the primary media before outputting
        header('Content-Type: '.$this->media->{$place->media_GUID}->mimetype);
        return $output;

    }

    /**
     * Takes the Request URI and determines which place to run, adding it to the place array along the way
     * @param string $url
     * @return array from database
     */
    private function get_place($url) {

        //Find correct place to use (static places [without %] take precedence over dynamic places [with %])
        $url = $this->mvsdb->real_escape_string($url);

        if ($url == '%')
            $error_place = '';
        else
            $error_place = 'AND url != "%"';
        $result = $this->mvsdb->query("SELECT place_GUID,url,inputs,https,media_GUID,interface_GUID FROM `mvs_places`
                                       WHERE ('$url' LIKE url OR '$url/' LIKE url) $error_place");

        //Look for the URL with the greatest length before a '%' sign
        $max = 0;
        while($row = $result->fetch_object()) {
            if ($row->url == $url) {
                $place = $row;
                break;
            }
            $static = strpos($row->url,'%');
            if ($static > $max) {
                $max = $static;
                $place = $row;
            }
        }
        $result->free();

        if (!isset($place))
            throw new Exception ('Place Not Found',404);

        if ($url != '%')
            $this->add_place($place,$url);
        else
            $this->add_place($place);

        if (!Movabls_Permissions::check_permission('place', $place->place_GUID, 'execute', $this->mvsdb))
            throw new Exception('You do not have permission to access this place',403);

        if ($place->https && !$GLOBALS->_SERVER['HTTPS']) {
            header('Location: https://'.$GLOBALS->_SERVER['HTTP_HOST'].$GLOBALS->_SERVER['REQUEST_URI']);
            die();
        }
        
        return $place;

    }

    /**
     * Takes the url pattern of the place, determines the values of the variables from the
     * actual url, and puts those values into the globals array
     * @param string $pattern
     * @param string $url
     * @param array $inputs
     * @return array = key/value pairings
     */
    private function extract_url_variables($pattern,$url,$inputs) {

        $pattern = '/'.preg_quote($pattern,'/').'/';
        $pattern = str_replace('%','(.*)?',$pattern);
        preg_match_all($pattern,$url,$matches);
        array_shift($matches);
        $return = array();
        foreach ($matches as $key => $match)
            $return[$inputs[$key]] = $match[0];
        return $return;

    }

    /**
     * Takes the url and inputs fields from a place and merges them together
     * @param string &$url
     * @param array &$inputs
     */
    private function construct_place_url(&$url,&$inputs) {

        if (!empty($inputs)) {
            foreach ($inputs as $input)
                $place_inputs[] = '{{'.$input.'}}';
            $url = str_replace('%','%s',$url);
            $url = vsprintf($url,$place_inputs);
        }
        else
            $inputs = array();

    }

    /**
     * Selects an interface, gets its tags, and adds it to the interfaces array
     * @param <string> $interface_GUID
     */
    private function get_interface($interface_GUID) {

        if (!isset($this->interfaces->$interface_GUID)) {
            $interface_GUID = $this->mvsdb->real_escape_string($interface_GUID);
            $result = $this->mvsdb->query("SELECT content FROM mvs_interfaces WHERE interface_GUID = '$interface_GUID'");
            $interface = $result->fetch_object();
            $result->free();
            if (empty($interface))
                return null;
                
            $interface = json_decode($interface->content);
            $this->interfaces->$interface_GUID = $interface;
            $this->get_tags($interface);
        }

    }

    /**
     * Runs through interface and Populates lists of media and functions to select
     * @param <stdClass> $tags
     */
    private function get_tags($tags) {
        
        if (empty($tags))
            return false;

        foreach($tags as $value) {
            if (isset($value->movabl_GUID) && $value->movabl_type == 'media') {
                if (!isset($this->media->{$value->movabl_GUID}))
                    $this->media->{$value->movabl_GUID} = null;
            }
            elseif (isset($value->movabl_GUID) && $value->movabl_type == 'function') {
                if (!isset($this->functions->{$value->movabl_GUID}))
                    $this->functions->{$value->movabl_GUID} = null;
            }
            elseif (isset($value->movabl_GUID) && $value->movabl_type == 'place') {
                if (!isset($this->places->{$value->movabl_GUID}))
                    $this->places->{$value->movabl_GUID} = null;
            }
            if (isset($value->tags))
                $this->get_tags($value->tags);
            elseif (isset($value->interface_GUID))
                $this->get_interface($value->interface_GUID);
        }

    }

    /**
     * Runs sql queries to collect all media and functions, and sets the mimetype
     * for the place
     * @param <string> $primary_GUID = the primary media for the place
     */
    private function select_movabls($primary_GUID) {

        //TODO: If a movabl is not found, needs to throw an error and reference the interface that calls the movabl
        if (!isset($this->media->$primary_GUID))
            $this->media->$primary_GUID = null;

        foreach ($this->media as $key => $val)
            $media[] = $this->mvsdb->real_escape_string($key);
        $media = '"'.implode('","',$media).'"';

        $result = $this->mvsdb->query("SELECT media_GUID,inputs,mimetype,content FROM mvs_media
                                       WHERE media_GUID IN ($media)");
        if ($result->num_rows == 0)
            throw new Exception ("No Media Found",500);
        
        while ($row = $result->fetch_object()) {

            $content_mime_type = split("/",$row->mimetype);

            if ($content_mime_type[0] != "text")
                $row->content = (binary)$row->content;
                
            $row->inputs = json_decode($row->inputs);
            if (empty($row->inputs)) {
                $row->inputs = array();
                $argstring = '';
            }
            else
                $argstring = '$'.implode(',$',$row->inputs);

            $info = array(
                'movabl_type' => 'media',
                'movabl_GUID' => $row->media_GUID
            );
            $this->stack->push($info);
                
            $renderer = new Movabls_MediaRender($row->content,$row->inputs);

            if ($content_mime_type[0] == "text")
                $code = 'ob_start(); ?>'.$renderer->output.'<?php return ob_get_clean();';
            else{ 
                $safe_binary_string = base64_encode($renderer->output);
                $code = "return base64_decode(\"$safe_binary_string\");";
            }

            $this->media->{$row->media_GUID} = new StdClass();
            $this->media->{$row->media_GUID}->inputs = $row->inputs;
            $this->media->{$row->media_GUID}->mimetype = $row->mimetype;

            if ($handle = create_function($argstring, $code))
                $this->media->{$row->media_GUID}->handle = $handle;
            else
                throw new Exception('Syntax Error in User-Defined Media',500);

            $this->stack->pop();
                
        }
        $result->free();

        if ($this->functions != new StdClass()) {
            
            foreach ($this->functions as $key => $val)
                $functions[] = $this->mvsdb->real_escape_string($key);
            $functions = '"'.implode('","',$functions).'"';

            $result = $this->mvsdb->query("SELECT function_GUID,inputs,content FROM mvs_functions
                                           WHERE function_GUID IN ($functions)");
            if ($result->num_rows == 0)
                throw new Exception ("Functions Not Found",500);
            while ($row = $result->fetch_object()) {
                $row->inputs = json_decode($row->inputs);
                if (empty($row->inputs)) {
                    $row->inputs = array();
                    $argstring = '';
                }
                else
                    $argstring = '$'.implode(',$',$row->inputs);
                $this->functions->{$row->function_GUID} = new StdClass();
                $this->functions->{$row->function_GUID}->inputs = $row->inputs;

                $info = array(
                    'movabl_type' => 'function',
                    'movabl_GUID' => $row->function_GUID
                );
                $this->stack->push($info);

                if ($handle = create_function($argstring, $row->content))
                    $this->functions->{$row->function_GUID}->handle = $handle;
                else
                    throw new Exception('Syntax Error in User-Defined Function',500);

                $this->stack->pop();

            }
            $result->free();
            
        }

        if ($this->places != new StdClass()) {

            foreach ($this->places as $key => $val)
                $places[] = $this->mvsdb->real_escape_string($key);
            $places = '"'.implode('","',$places).'"';

            $result = $this->mvsdb->query("SELECT place_GUID,url,inputs,https,media_GUID,interface_GUID FROM mvs_places
                                           WHERE place_GUID IN ($places)");
            if ($result->num_rows == 0)
                throw new Exception ("Places Not Found",500);
            while ($row = $result->fetch_object())
                $this->add_place($row);
            $result->free();

        }

    }

    /**
     * Adds place inputs and handle to $this->places.  If this is the original place, toplevel
     * will be set to the REQUEST_URI, and the variables from the URL will be extracted and added
     * to the $GLOBALS->_PLACE array
     * @param StdClass $place
     * @param string $toplevel 
     */
    private function add_place($place,$toplevel = null) {

        $place->inputs = json_decode($place->inputs);

        if (!empty($toplevel))
            $GLOBALS->_PLACE = $this->extract_url_variables($place->url,$toplevel,$place->inputs);

        if (empty($place->inputs)) {
            $place->inputs = array();
            $argstring = '';
        }
        else
            $argstring = '$'.implode(',$',$place->inputs);

        $this->construct_place_url($place->url,$place->inputs);
        $renderer = new Movabls_MediaRender($place->url,$place->inputs);
        $code = 'ob_start(); ?>'.$renderer->output.'<?php return ob_get_clean();';
        
        $this->places->{$place->place_GUID} = new StdClass();
        $this->places->{$place->place_GUID}->url = $place->url;
        $this->places->{$place->place_GUID}->inputs = $place->inputs;

        $info = array(
            'movabl_type' => 'place',
            'movabl_GUID' => $place->place_GUID
        );
        $this->stack->push($info);

        if ($handle = create_function($argstring, $code))
            $this->places->{$place->place_GUID}->handle = $handle;
        else
            throw new Exception('Syntax Error in Place URL',500);
            
        $this->stack->pop();

    }

    /**
     * Runs a movabl and returns the result
     * @param <string> $type = media or function
     * @param <string> $media_GUID
     * @param <string> $interface_GUID = interface we're running
     * @param <StdClass> $tags = tags passed to this media
     * @param <bool> $toplevel = whether this is the top level of the interface
     * @return <string> rendered content
     */
    private function run_movabl($type,$movabl_GUID,$interface_GUID,$tags = null,$toplevel = true) {

        $type = Movabls::table_name($type);

        if (empty($this->$type->$movabl_GUID))
            return null;

        $movabl = $this->$type->$movabl_GUID;

        if (empty($movabl->inputs) || (empty($tags) && !$toplevel))
            $inputs = array();
        elseif ($toplevel) {
            $info = array(
                'movabl_type' => 'interface',
                'movabl_GUID' => $interface_GUID
            );
            $this->stack->push($info);
            $inputs = $this->run_tags($interface_GUID,$this->interfaces->$interface_GUID,$movabl->inputs,true);
            $this->stack->pop();
        }
        else //Run tags within interface
            $inputs = $this->run_tags($interface_GUID,$tags,$movabl->inputs,false);

        return call_user_func_array($movabl->handle,$inputs);

    }

    /**
     * Runs tags specified at a level of an interface, be they movabls, toplevel tags, or expressions
     * Runs tags in order given by the interface (even the ones that don't apply to
     * an input), then applies them to the inputs given in the $inputs argument.
     * @param <string> $interface_GUID
     * @param <StdClass> $tags = all tags to set and their instructions
     * @param <array> $inputs = tags to return (null if for a php standard library function)
     * @param <bool> $toplevel = whether this is an interface top-level
     * @return <array> set inputs
     */
    private function run_tags($interface_GUID,$tags,$inputs,$toplevel = false) {

        foreach ($tags as $name => $tag) {

            if (isset($tag->expression)) {
                $info = array(
                    'expression' => $tag->expression
                );
                $this->stack->push($info,$name);
                $tags->$name = $this->run_expression($tag->expression,$interface_GUID);
                $this->stack->pop();
            }
            elseif (isset($tag->movabl_GUID)) {
                $info = array(
                    'movabl_type' => $tag->movabl_type,
                    'movabl_GUID' => $tag->movabl_GUID
                );
                $this->stack->push($info,$name);

                if (isset($tag->interface_GUID))
                    $tags->$name = $this->run_movabl($tag->movabl_type, $tag->movabl_GUID, $tag->interface_GUID);
                elseif (isset($tag->tags))
                    $tags->$name = $this->run_movabl($tag->movabl_type, $tag->movabl_GUID, $interface_GUID, $tag->tags, false);
                elseif (isset($tag->lambda)) {
                    $type = Movabls::table_name($tag->movabl_type);
                    $tags->$name = $this->$type->{$tag->movabl_GUID}->handle;
                }
                else
                    $tags->$name = $this->run_movabl($tag->movabl_type, $tag->movabl_GUID, $interface_GUID, null, false);

                $this->stack->pop();
            }
            else
                $tags->$name = null;

            if ($toplevel) //if this is top-level, set the tag in $this->interfaces
                $this->interfaces->$interface_GUID->$name = $tags->$name;
                
        }

        $return = array();
        //PHP standard library functions do not have defined inputs - use all tags in order
        if ($inputs === null) {
            foreach ($tags as $tag)
                $return[] = $tag;
        }
        //Otherwise, use inputs as specified
        else {
            foreach ($inputs as $input) {
                if (isset($tags->$input))
                    $return[] = $tags->$input;
                else
                    $return[] = null;
            }
        }
        
        return $return;
        
    }

    /**
     * Runs a php expression and returns its value - uses toplevel tags from the
     * current interface as arguments
     * @param <string> $expression - php expression
     * @return <mixed> evaluated value
     */
    private function run_expression($expression,$interface_GUID) {

        $values = array();

        foreach ($this->interfaces->$interface_GUID as $tag => $value) {
            $args[] = $tag;
            $values[] = $value;
        }
        if (empty($args))
            $args = '';
        else
            $args = '$'.implode(',$',$args);

        if ($handle = create_function($args,"return $expression;"))
            return call_user_func_array($handle,$values);
        else
            throw new Exception('Syntax Error in User-Defined Expression',500);

    }

    /**
     * This function is set as the error handler for running movabls.  It takes all errors and
     * puts them into the $GLOBALS->_ERRORS array.  On fatal errors, it runs the user's error place.
     * @param mixed $errno
     * @param string $errstr
     * @param string $errfile
     * @param int $errline
     * @param int $http_status
     * @return true
     */
    public function error_handler ($errno, $errstr, $errfile = '', $errline = '', $http_status = 200) {

        //TODO: Echo out warnings and notices?
        //TODO: Syntax should be checked by the API or the IDE, since syntax errors will not give line numbers

        //Change the errline to null if the error was not in a user-defined function
        if (strpos($errfile,'runtime-created function') === false)
            $errline = null;

        //Change the errfile to the Movabl that threw the error
        $errfile = $this->stack->top();

        //Add the error to the $GLOBALS->_ERRORS array
        switch ($errno) {

            case E_ERROR:
            case E_USER_ERROR:
                $GLOBALS->add_error('PHP Error',true,$errstr,$errline,$errfile,$this->stack->get(),500);
                break;

            case E_WARNING:
            case E_USER_WARNING:
                $GLOBALS->add_error('PHP Warning',false,$errstr,$errline,$errfile,$this->stack->get(),200);
                return true;
                break;

            case E_NOTICE:
            case E_USER_NOTICE:
                $GLOBALS->add_error('PHP Notice',false,$errstr,$errline,$errfile,$this->stack->get(),200);
                return true;
                break;

            case 'Exception':
                $GLOBALS->add_error('Uncaught Exception',true,$errstr,$errline,$errfile,$this->stack->get(),$http_status);
                break;

            default:
                $GLOBALS->add_error('PHP Unknown',false,$errstr,$errline,$errfile,$this->stack->get(),$http_status);
                return true;
                break;

        }
        
        //In case they haven't been locked yet when this error was thrown
        $GLOBALS->lock();

        //Try to run the user's custom error place
        try {
            //To prevent an infinite loop, check to see if we're already running the error place
            foreach ($this->places as $place) {
                if (!empty($place) && $place->url == '%')
                    throw new Exception('Error place contains errors!');
            }
            print_r($this->run_place('%'));
        }
        catch (Exception $error_place) {
            if ($error_place->getMessage() != 'Error place contains errors!')
                $this->error_handler('Exception',$error_place->getMessage(),$error_place->getFile(),$error_place->getLine(),$error_place->getCode());
            echo 'Error place contains errors!<br /><br />Dumping $GLOBALS->_ERRORS:<br /><br />';
            print_r($GLOBALS->_ERRORS);
        }
        exit(1);

    }

    /**
     * This function executes on shutdown of the script as a method for catching
     * errors of type E_ERROR and sending them through the default error reporting
     * mechanism.  Compiler, core, and parse errors will still not be caught.
     */
    public function shutdown_handler() {

        $error = error_get_last();
        if (isset($error['type']) && $error['type'] == E_ERROR)
            $this->error_handler($error['type'],$error['message'],$error['file'],$error['line']);

    }

}