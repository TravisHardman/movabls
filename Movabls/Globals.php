<?php
/**
 * Read-only globals class replaces all superglobals with read-only variants
 * @author Travis Hardman
 */
class Movabls_Globals {

    private $data = array();
    private $lock = false;

    function __construct() {

        if (is_object($GLOBALS))
            throw new Exception('Cannot create a new instance of Movabls_Globals',500);

        global $_USER,$_SESSION;

        $this->data['_SERVER'] = $_SERVER;
        $this->data['_SERVER']['SITE_ID'] = 1;
        $this->data['_SERVER']['DATABASE'] = 'movabls_user';
        $this->data['_GET'] = $_GET;
        $this->data['_POST'] = $_POST;
        $this->data['_FILES'] = $_FILES;
        $this->data['_COOKIE'] = $_COOKIE;

        $this->data['_USER'] = $_USER;
        $this->data['_USER']['groups'][] = 2;
        $this->data['_USER']['groups'] = array_unique($this->data['_USER']['groups']);
        $this->data['_USER']['groups'] = array_values($this->data['_USER']['groups']);
        sort($this->data['_USER']['groups']);
        
        $this->data['_SESSION'] = $_SESSION;
        $this->data['_PLACE'] = array();
        $this->data['_ERRORS'] = array();
    }

    function __get($var) {
        return $this->data[$var];
    }

    function __set($var,$value) {
        if ($this->lock)
            throw new Exception ("Global variables are read-only. \$$var cannot be set",500);
        else
            $this->data[$var] = $value;
    }

    function __isset($var) {
        return isset($this->data[$var]);
    }

    function __unset($var) {
        if ($this->lock)
            throw new Exception ("Global variables are read-only. \$$var cannot be unset",500);
        else
            unset($this->data[$var]);
    }

    function lock() {
        $this->lock = true;
    }

    function add_error($type,$fatal,$message,$line,$movabl,$stack,$http_status) {

        $this->data['_ERRORS'][] = array(
            'type' => $type,
            'fatal' => $fatal,
            'message' => $message,
            'line' => $line,
            'movabl' => $movabl,
            'stack' => $stack,
            'http_status' => $http_status
        );
        
    }

    function set_session_data($key,$value) {

        if (empty($value))
            unset($this->data->_SESSION[$key]);
        else
            $this->data->_SESSION[$key] = $value;

    }
}