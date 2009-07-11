<?php
/*
 * Movabls by LikeStripes LLC
 */

/**
 * Read-only globals class replaces all superglobals with read-only variants
 * @author Travis Hardman



///Now on github


 *
 */
class Globals {

	private $data = array();
	
	function __construct() {
		$this->data['GLOBALS'] = $GLOBALS;
		$this->data['_SERVER'] = $_SERVER;
		$this->data['_GET'] = $_GET;
		$this->data['_POST'] = $_POST;
		$this->data['_FILES'] = $_FILES;
		$this->data['_COOKIE'] = $_COOKIE;
		if (isset($_SESSION))
			$this->data['_SESSION'] = $_SESSION;
		$this->data['_REQUEST'] = $_REQUEST;
		$this->data['_ENV'] = $_ENV;
	}
	
	function __get($var) {
		return $this->data[$var];
	}
	
	function __set($var,$value) {
		throw new Exception ("Global variables are read-only. \$$var cannot be set",500);
	}
	
	function __isset($var) {
		return isset($this->data[$var]);
	}
	
	function __unset($var) {
		throw new Exception ("Global variables are read-only. \$$var cannot be unset",500);
	}
	
}

/**
 * Core Movabls Class - Instantiating this class runs the system
 * @author Travis Hardman
 */
class Movabls {
	
	//TODO: Is there a way to not load media and functions one at a time from the db?
	//TODO: A couple use cases to consider - blog with media as entries, or a store with products as media
	
	private $mvsdb; //DB Connection
	private $queries; //DB Prepared Statements
	private $lambdas; //Holds all active user function handles
	private $mimetype; //The mimetype of this page, set by the first media called
	private $interfaces; //Placeholder for interfaces order array, used by sort function
	
	public function __construct() {
		
		$this->lambdas = array();
		
		//Database configuration
		$dbconfig = new StdClass();
		$dbconfig->host = 'localhost';
		$dbconfig->db_name = 'db_cheeseburger';
		$dbconfig->user = 'fajita';
		$dbconfig->password = 'jupy700f';
		$dbconfig->engine = 'mysql';
				
		//Connect to the database
		try {
	    	$this->mvsdb = new PDO("$dbconfig->engine:dbname=$dbconfig->db_name;host=$dbconfig->host", $dbconfig->user, $dbconfig->password);
		} catch (PDOException $e) {
		    throw new Exception ('Database connection failed (Details: ' . $e->getMessage().')',500);
		}
		
		//Get the place instance and run it
		print_r($this->run_instance($this->get_place()));
	}
	
	private function get_place() {
		
		//Find application by URL
		$url = $GLOBALS->_SERVER['HTTP_HOST'].$GLOBALS->_SERVER['REQUEST_URI'];
		$url = (strpos($url,'?') ? substr($GLOBALS->_SERVER['HTTP_HOST'].$GLOBALS->_SERVER['REQUEST_URI'],0,strpos($url,'?')) : $url);
		$query = $this->mvsdb->query("SELECT application_id,url FROM `mvs_applications`
								WHERE '$url' LIKE CONCAT(url,'/','%')
								OR '$url' = url
								ORDER BY url DESC
								LIMIT 1");
		$application = $query->fetch(PDO::FETCH_ASSOC);
		if (empty($application))
			throw new Exception ('Application Not Found',404);
			
		//Find correct place to use (static places [without %] take precedence over dynamic places [with %])
		$url = substr($url,strlen($application['url']));
		$query = $this->mvsdb->query("SELECT place_id,url,instance_id FROM `mvs_places`
								WHERE application_id = {$application['application_id']}
								AND ('$url' LIKE url OR '$url/' LIKE url)");
		$place = $query->fetchAll(PDO::FETCH_ASSOC);
		if (count($place) > 1) {
			//Logic: Look for the URL with the greatest length before a '%' sign
			$max = 0;
			foreach($place as $k => $v) {
				if ($v['url'] == $url) {
					$place = $v;
					break;	
				}
				$static = strpos($v['url'],'%');
				if ($static > $max) {
					$max = $static;
					$place = $v;
				}
			}
		}
		elseif (count($place) == 1)
			$place = $place[0];
		else
			throw new Exception ('Place Not Found',404);
			
		//Get the instance specified by the place
		$query = $this->mvsdb->query(
			"SELECT instance_id,movabls_type,movabls_id,interface_ids FROM `mvs_instances`
			WHERE instance_id = {$place['instance_id']}");
		$instance = $query->fetch(PDO::FETCH_ASSOC);
		if (empty($instance))
			throw new Exception ('Place Instance Not Found',404);
		
		return $instance;
	}
	
	/**
	 * Gets or creates a prepared statement from the $this->queries array
	 * @param $name = statement key in the queries array
	 * @return PDO Statement
	 */
	private function get_query ($key) {
		//TODO: Specify fields instead of using * in all queries - much faster
		if (!isset($this->queries[$key])) {
			switch ($key) {
				case 'media': 
					$this->queries[$key] = $this->mvsdb->prepare(
						"SELECT media_id,mimetype,content 
						FROM `mvs_media`
						WHERE media_id = ?");
					break;
				case 'function':
					$this->queries[$key] = $this->mvsdb->prepare(
						"SELECT function_id,code 
						FROM `mvs_functions`
						WHERE function_id = ?");
					break;
				case 'arguments':
					$this->queries[$key] = $this->mvsdb->prepare(
						"SELECT a.argument_id,a.function_id,a.tag,a.argument_type,a.default_id,
                        i.movabls_type,i.movabls_id,i.interface_ids
						FROM `mvs_arguments` a
                        LEFT JOIN `mvs_instances` i ON a.default_id = i.instance_id
						WHERE function_id = ?");
					break;
				default:
					return false;
					break;
			}
		}
		return $this->queries[$key];
	}
	
	/**
	 * Runs an instance of a media, function, or php function
	 * @param $instance = instance row from the database
	 * @param $arguments = array of lookups to pass to a function or php function (from the interface)
	 * @return output
	 */
	public function run_instance ($instance,$arguments = array()) {
		switch ($instance['movabls_type']) {
			case 'php':
				return $this->run_php($instance['movabls_id'],$arguments);
				break;
			case 'media':
                return $this->run_media($instance['movabls_id'],$instance['interface_ids']);
				break;
			case 'function':
				return $this->run_function($instance['movabls_id'],$arguments);
				break;
		}
	}
	
	/**
	 * Runs a function and returns its output
	 * @param $function_id
	 * @param $arguments
	 * @return output
	 */
	private function run_function ($function_id,$arguments,$run = true) {
		$query = $this->get_query('function');
		$query->execute(array($function_id));
		$function = $query->fetch(PDO::FETCH_ASSOC);
		if (empty($function))
			throw new Exception ("Function $function_id not found",500);
		$tags = $this->run_lookups($arguments);
		if (!isset($this->lambdas['function-'.$function['function_id']])) {
			$argstring = $this->create_argstring(array_keys($tags));
			$this->lambdas['function-'.$function['function_id']] = create_function($argstring,$function['code']);
		}
		return call_user_func_array($this->lambdas['function-'.$function['function_id']],$tags);
	}
	
	/**
	 * Runs a php function and returns its output
	 * @param $function_id
	 * @param $arguments
	 * @return output
	 */
	private function run_php ($function_name,$arguments) {
		$tags = $this->run_lookups($arguments);
		return call_user_func_array($function_name,$tags);
	}
	
	/**
	 * Runs a media item, giving it one or more interfaces
	 * @param $media_id
	 * @param $interfaces = comma separated interface ids
	 * @return output
	 */
	private function run_media ($media_id,$interfaces = '') {
        $query = $this->get_query('media');
		$query->execute(array($media_id));
		$media = $query->fetch(PDO::FETCH_ASSOC);
        if (empty($media))
			throw new Exception ("Media $media_id not found",500);
		if (empty($this->mimetype)) {
            $this->mimetype = $media['mimetype'];
			header("Content-type: {$media['mimetype']}");
		}
        //Type 'serial' refers to a serialized variable, which should be returned as the correct data type
        if ($media['mimetype'] == 'serial')
           return unserialize($media['content']);
        if (!empty($interfaces))
			$tags = $this->run_interfaces($interfaces);
		else
			$tags = array();
        //Create the media function if it doesn't exist yet, then run it
		if (!isset($this->lambdas['media-'.$media['media_id']])) {
			$render = new Movabls_Media_Render($media['content'],array_keys($tags));
			$code = "ob_start(); ?>\n".$render->output."\n<? return ob_get_clean();";
			$argstring = $this->create_argstring(array_keys($tags));
			$this->lambdas['media-'.$media['media_id']] = create_function($argstring,$code);
		}
		return call_user_func_array($this->lambdas['media-'.$media['media_id']],$tags);
	}
	
	
	/**
	 * Turns an array of variable names into a string of arguments to use in create_function
	 * @param $args
	 * @return argstring
	 */
	private function create_argstring ($tags) {
		$argstring = '';
		foreach($tags as $tag) {
			$argstring .= '$'.$tag.',';
		}
		return substr($argstring,0,strlen($argstring)-1);
	}
	
	/**
	 * Pulls the interfaces tied to a media and renders them into tags
	 * @param $interfaces = comma-delimited list of interface ids
	 * @return associative array of tags
	 */
	private function run_interfaces($interfaces) {
		$query = $this->mvsdb->query(
			"SELECT l.lookup_id,l.interface_id,l.instance_id,l.argument_id,l.tag,l.lookup_type,l.content,l.order,i.movabls_type,i.movabls_id,i.interface_ids
			FROM `mvs_lookups` l
			LEFT JOIN `mvs_instances` i ON i.instance_id = l.content
			WHERE l.interface_id IN ($interfaces)
			ORDER BY l.`order` ASC");
		$lookups = $query->fetchAll(PDO::FETCH_ASSOC);
		//Construct a list of functions in this interface so we can get the arguments
		$functions = array();
		foreach ($lookups as $lookup) {
            if (($lookup['lookup_type'] == 'instance' && $lookup['movabls_type'] == 'function') || (!isset($lookup['lookup_type']) && $lookup['argument_type'] == 'instance' && $lookup['movabls_type'] == 'function'))
				$functions[] = $lookup['movabls_id'];
		}
		//Get arguments and default instances for functions, and add them to the functions as a lookups array
		if (!empty($functions)) {
            $query = $this->mvsdb->query(
                "SELECT a.argument_id,a.function_id,a.tag,a.argument_type,a.default_id,
                i.movabls_type,i.movabls_id,i.interface_ids
				FROM `mvs_arguments` a
                LEFT JOIN `mvs_instances` i ON a.default_id = i.instance_id
                WHERE function_id IN (".implode(',',$functions).")");
            $arguments = $query->fetchAll(PDO::FETCH_ASSOC);
            $argsets = array();
            foreach ($arguments as $arg) {
                $argsets[$arg['function_id']][$arg['argument_id']] = $arg;
            }
            foreach ($lookups as $k => $lookup) {
                if (($lookup['lookup_type'] == 'instance' && $lookup['movabls_type'] == 'function') || (!isset($lookup['lookup_type']) && $lookup['argument_type'] == 'instance' && $lookup['movabls_type'] == 'function'))
                    $lookups[$k]['lookups'] = $argsets[$lookup['movabls_id']];
            }
        }
		//Build the tree of lookups for the interface
		if (!empty($lookups))
			$lookups = $this->generate_tree($lookups);
		$this->interfaces = explode(',',$interfaces);
		usort($lookups,array('Movabls','sort_interfaces'));
		//Run through this tree running the necessary instances and passing lookups through to functions
		return $this->run_lookups($lookups, true);
	}
	
	/**
	 * Runs through an array of lookups and organizes it into a recursive tree - if arguments are already set,
	 * the function replaces arguments with lookups if there are lookups to overwrite
	 * @param $lookups = all lookups from the database in this interface
	 * @param $arguments = array of arguments already set (will be replaced with lookups if lookups set)
	 * @param $root = parent instance id
	 * @return recursive array of lookups
	 */
	private function generate_tree($lookups,$arguments = array(),$root = null) {
		foreach ($lookups as $key => $lookup) {
			if ($lookup['instance_id'] == $root) {
				unset($lookups[$key]); //we can remove this because we've found its place
				if ($lookup['lookup_type'] == 'instance' && $lookup['movabls_type'] == 'function') {
					$lookup['lookups'] = $this->generate_tree($lookups,$lookup['lookups'],$lookup['content']);
				}
				if (isset($arguments[$lookup['argument_id']])) {
					unset($lookup['tag']); //use the argument tag, not the lookup tag
					$arguments[$lookup['argument_id']] = $lookup + $arguments[$lookup['argument_id']];
				}
				else
					$arguments[] = $lookup;
			}
		}
		if (empty($arguments))
			$arguments = null;
		return $arguments;
	}
	
	/**
	 * Sorts lookups by their interface ids into the user-specified order for interfaces,
	 * while preserving ordering set by 'order' field in the database for lookups in each interface
	 * @param $a
	 * @param $b
	 * @return sort value
	 */
	private function sort_interfaces ($a,$b) {
		if ($a['interface_id'] == $b['interface_id']) {
			if ($a['order'] > $b['order'])
				return 1;
			elseif ($a['order'] < $b['order'])
				return -1;
			else
				return 0;
		}
	    foreach ($this->interfaces as $value) {
	    	if ($a['interface_id'] == $value)
	    		return -1;
	    	elseif ($b['interface_id'] == $value)
	    		return 1;
	    }
	}
	
	/**
	 * Take a tree of lookups and run them, constructing a tags array along the way
	 * @param $lookups = lookups tree
	 * @param $toplevel = boolean for whether this is the top level tag lookups in an interface
	 * @return array of tags
	 */
	private function run_lookups($lookups, $toplevel = false) {
        $tags = array();
		foreach ($lookups as $lookup) {
			if (isset($lookup['argument_type']) && $lookup['argument_type'] == 'function') {
				if (isset($lookup['content']))
					$tags[$lookup['tag']] = $this->get_lambda($lookup['content']);
				elseif (!empty($lookup['default']))
					$tags[$lookup['tag']] = $this->get_lambda($lookup['default_id']);
				else
					throw new Exception ("Lambda tag not set: ".$lookup['tag'],500);
			}
			elseif (isset($lookup['lookup_type']) && $lookup['lookup_type'] == 'tag') {
                //tag case only used when someone sets a tag as equal to another tag at the top level
                //since all other tags are replaced with $this->replace_tags below
                if (isset($tags[$lookup['content']]))
                    $tags[$lookup['tag']] = $tags[$lookup['content']];
                else
                    throw new Exception ("Referenced tag not set: ".$lookup['content'],500);
			}
            else {
                //If this is top-level, run through all tags and replace them with their values
                if ($toplevel && !empty($lookup['lookups']))
                    $lookup['lookups'] = $this->replace_tags($lookup['lookups'],$tags);
                $tags[$lookup['tag']] = $this->run_instance($lookup,$lookup['lookups']);
            }
		}
		return $tags;
	}
	
	private function get_lambda($function_id) {
		if (!isset($this->lambdas['function-'.$function_id])) {
			$query = $this->get_query('function');
			$query->execute(array($function_id));
			$function = $query->fetch(PDO::FETCH_ASSOC);
			if (empty($function))
				throw new Exception ("Function $function_id not found",500);
			$query = $this->get_query('arguments');
			$query->execute(array($function_id));
			$results = $query->fetchAll(PDO::FETCH_ASSOC);
			$arguments = array();
			foreach ($results as $arg) {
				$arguments[] = $arg['tag'];
			}
			$argstring = $this->create_argstring($arguments);
			return $this->lambdas['function-'.$function_id] = create_function($argstring,$function['code']);
		}
		else
			return $this->lambdas['function-'.$function_id];
	}
	
	/**
	 * Run from the top level of a tree, this function descends into a tree of lookups and replaces the tag
	 * lookups with the tags already set in the interface
	 * @param $lookups = lookups tree
	 * @param $tags = already set tags
	 * @return lookups tree with tags replaced by values
	 */
	private function replace_tags ($lookups, $tags) {
		foreach ($lookups as $k => $lookup) {
			if (isset($lookup['lookup_type']) && $lookup['lookup_type'] == 'tag') {
				if (isset($tags[$lookup['content']])) {
					$lookups[$k]['lookup_type'] = 'instance';
					$lookups[$k]['content'] = serialize($tags[$lookup['content']]);
				}
				else
					throw new Exception ("Referenced tag not set: ".$lookup['content'],500);
			}
			if (!empty($lookup['lookups']))
				$lookups[$k]['lookups'] = $this->replace_tags($lookup['lookups'],$tags);
		}
		return $lookups;
	}
	
}

/**
 * Media Renderer Class - Renders media templating system into PHP code
 * @author Travis Hardman
 */
class Movabls_Media_Render {
	
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
        $this->extract_tags($view);
		$this->available = $available;
		foreach ($this->intags as $tag) {
			$this->outtags[] = $this->render_tag($tag);
		}
		$this->output = vsprintf($this->string,$this->outtags);
	}
	
	/**
	 * Extracts tags from the view template, and saves a string format to this->string 
	 * and the tags to this->intags
	 * @param $view = view content
	 */
	private function extract_tags($view) {
		$view = str_replace('%s','%%s',$view);
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
			return '<? else: ?>';
		elseif ($tag == 'endif')
			return '<? endif; ?>';
		elseif ($tag == 'endfor')
			return '<? endforeach; ?>';
		elseif (strpos($tag,'if') === 0) {
			$tag = substr($tag,2);
			return '<? if ('.$this->render_if(trim($tag)).'): ?>';
		}
		elseif (strpos($tag,'else if') === 0) {
			$tag = substr($tag,7);
			return '<? elseif ('.$this->render_if(trim($tag)).'): ?>';
		}
		elseif (strpos($tag,'for') === 0) {
			$tag = substr($tag,3);
			return '<? foreach ('.$this->render_for(trim($tag)).'): ?>';
		}
		else
			return "<? print_r(".$this->variable($tag)."); ?>";
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
		if (($length = strpos($tag,'=>')) === false) {
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
		$tag = trim(substr(trim(substr($tag,$length)),2));
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

try {
	//Override all superglobals with read-only variants
	$GLOBALS = new Globals();
	unset($_SERVER,$_GET,$_POST,$_FILES,$_COOKIE,$_SESSION,$_REQUEST,$_ENV);
	//Run it!
	new Movabls;
} catch (Exception $e) {
	switch ($e->getCode()) {
		default: header("HTTP/1.1 404 ".$e->getMessage(),true,404);break;
		//default: header("HTTP/1.1 500 ".$e->getMessage(),true,500);break;
	}
	die($e->getMessage());
}

/*
$iterations = 1000;
ob_start();
$times = array();
$squares = array();
for ($i=1;$i<=$iterations;$i++) {
	$start = microtime(true);
	new Movabls;
	$time = microtime(true) - $start;
	$times[] = $time;
	$squares[] = $time*$time;
}
ob_end_clean();
$variance = (array_sum($squares) - array_sum($times)*array_sum($times)/count($times)) / count($times);
echo "\n\n";
echo "mean run: ".(array_sum($times)/count($times))."\n";
echo "max run: ".max($times)."\n";
echo "std dev: ".sqrt($variance)."\n";
*/
?>