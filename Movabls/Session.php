<?php
/**
 * Session class sets and maintains user authentication
 * @author Travis Hardman
 */
class Movabls_Session {

    /**
     * Uses the cookies variable to get the session and create the $_USER global
     * @global array $_USER
     */
    public static function get_session() {

        //This code can only be called in bootstrapping before $GLOBALS is created
        if (!empty($GLOBALS->_USER))
            return;

        global $_USER;
        $mvsdb = Movabls_Session::db_link();

        if (!empty($_COOKIE['sslsession'])) {
            $type = 'ssl';
            $session = $_COOKIE['sslsession'];
            $request = $_COOKIE['sslrequest'];
        }
        elseif (!empty($_COOKIE['httpsession'])) {
            $type = 'http';
            $session = $_COOKIE['httpsession'];
            $request = $_COOKIE['httprequest'];
        }

        unset($_COOKIE['httpsession'],$_COOKIE['httprequest'],$_COOKIE['sslsession'],$_COOKIE['sslrequest']);

        if (!isset($type)) {
            $_USER = array();
            return;
        }
            
        $results = $mvsdb->query("SELECT * FROM mvs_sessions
                                  WHERE {$type}session = '$session'");
        if ($results->num_rows > 0) {
            $row = $results->fetch_assoc();
            //Request key is incorrect, meaning somebody is trying to gain unauthorized access
            //or has successfully gained access via a replay - destroy session for safety
            if ($request != $row[$type.'request']) {
                Movabls_Session::delete_session($row['session_id'],$mvsdb);
                $_USER = array();
                return;
            }
            else {
                $token = Movabls_Session::get_token();
                $expiration = date('Y-m-d h:i:s',time()+$row['term']);
                $mvsdb->query("UPDATE mvs_sessions SET {$type}request = '$token', expiration = '$expiration'
                               WHERE session_id = {$row['session_id']}");
                Movabls_Session::set_cookie($type,'request',$token,$row['term']);
                $_USER = json_decode($row['userdata'],true);
                $_USER['session_id'] = $row['session_id'];
            }
        }
        else {
            Movabls_Session::remove_cookies();
            $_USER = array();
            return;
        }

    }

    /**
     * Creates an authentication token to tie the cookie to the database
     */
    private static function get_token() {

        return uniqid(sha1(mt_rand(0,100000).time().@$_SERVER['REMOTE_ADDR']), true);

    }

    /**
     * Sets a movabls session cookie
     * @param string $type = 'ssl' or 'http'
     * @param string $name = 'request' or 'session'
     * @param string $token
     * @param string $expiration
     */
    private static function set_cookie($type,$name,$token,$term) {

        $name = $type.$name;
        $expiration = time()+$term;
        $secure = $type == 'ssl';
        setcookie($name,$token,$expiration,'/',$_SERVER['HTTP_HOST'],$secure,true);

    }

    /**
     * Delete the specified session from the database and remove session cookies
     * @param int $session_id
     * @param mysqli handle $mvsdb
     */
    private static function delete_session($session_id = null,$mvsdb = null) {
        
        if (empty($mvsdb))
            $mvsdb = Movabls_Permissions::db_link();

        if (!empty($session_id))
            $mvsdb->query("DELETE FROM mvs_sessions WHERE session_id = $session_id");
        Movabls_Session::remove_cookies();
        
    }

    /**
     * Remove all session-related cookies
     */
    private static function remove_cookies() {

        Movabls_Session::set_cookie('ssl','request',false,-86400);
        Movabls_Session::set_cookie('http','request',false,-86400);
        Movabls_Session::set_cookie('ssl','session',false,-86400);
        Movabls_Session::set_cookie('http','session',false,-86400);

    }

    /**
     * Public wrapper function to destroy the current user's session
     */
    public static function destroy_session() {

        $session_id = $GLOBALS->_USER['session_id'];
        Movabls_Session::delete_session($session_id);

    }

    /**
     * Gets the handle to access the database
     * @return mysqli handle
     */
    private static function db_link() {

        $mvsdb = new mysqli('localhost','root','h4ppyf4rmers','db_filet');
        if (mysqli_connect_errno()) {
            printf("Connect failed: %s\n", mysqli_connect_error());
            exit();
        }
        return $mvsdb;

    }

}