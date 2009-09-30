<?php
/**
 * Read-only globals class replaces all superglobals with read-only variants
 * @author Travis Hardman
 */
class Movabls_Globals {

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
        $this->data['_USER'] = array(
            'user_GUID' => '12345',
            'username' => 'travis',
            'email' => 'travis@likestripes.com',
            'is_owner' => true,
            'groups' => array('mysiteusers','mysiteadmins')
        );
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
?>
