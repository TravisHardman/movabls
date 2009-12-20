<?php
/**
 * Session class sets and maintains user authentication
 * @author Travis Hardman
 */
class Movabls_Session {

    /**
     * Uses the cookies variable to get the session and create the $_USER global
     * @param mysqli handle $mvsdb
     * @global array $GLOBALS->_USER
     */
    public static function get_session($mvsdb = null) {

        //This code can only be called in bootstrapping before $GLOBALS is created
        if (!empty($GLOBALS->_USER))
            return;

        if (empty($mvsdb))
            $mvsdb = self::db_link();

        //Approx every 10000 requests, delete expired sessions
        if (mt_rand(1,10000) == 14)
            self::delete_expired_sessions($mvsdb);

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
            $GLOBALS->_USER = array();
            return;
        }
            
        $results = $mvsdb->query("SELECT * FROM mvs_sessions
                                  WHERE {$type}session = '$session'");
        if ($results->num_rows > 0) {
            $session = $results->fetch_assoc();
            $results->free();

            //If request key is incorrect, meaning somebody is trying to gain unauthorized access
            //or has successfully gained access via a replay - destroy session for safety
            $checks[0] = $request != $session[$type.'request'];
            //If session is expired
            $checks[1] = strtotime($session['expiration']) < time();
            
            if (in_array(false,$checks)) {
                self::delete_session($session['session_id'],$mvsdb);
                $GLOBALS->_USER = array();
                return;
            }
            else {
                //Regenerate request token
                $token = self::get_token();
                $expiration = date('Y-m-d h:i:s',time()+$session['term']);
                $mvsdb->query("UPDATE mvs_sessions SET {$type}request = '$token', expiration = '$expiration'
                               WHERE session_id = {$session['session_id']}");
                self::set_cookie($type,'request',$token,$session['term']);

                //Add _USER to globals
                $results = $mvsdb->query("SELECT * FROM `movabls_user`.`mvs_users`
                                          WHERE user_id = {$session['user_id']}");
                $user = $results->fetch_assoc();
                $results->free();
                $GLOBALS->_USER = $user;
                $GLOBALS->_USER['session_id'] = $session['session_id'];

                //Add _USER['groups'] to globals
                $results = $mvsdb->query("SELECT DISTINCT group_id FROM `movabls_user`.`mvs_group_memberships`
                                          WHERE user_id = {$session['user_id']}");
                $GLOBALS->_USER['groups'] = array();
                while($row = $results->fetch_assoc())
                    $GLOBALS->_USER['groups'][] = $row['group_id'];
                $results->free();
            }
        }
        else {
            self::remove_cookies();
            $GLOBALS->_USER = array();
            return;
        }

    }

    private static function create_session($user_id,$mvsdb = null) {

        if (empty($mvsdb))
            $mvsdb = self::db_link();
            
        if (!$GLOBALS->_SERVER['HTTPS'])
            throw new Exception('Users may only log in over a secure (HTTPS) connection',500);

        $sslsession = self::get_token();
        $sslrequest = self::get_token();
        $httpsession = self::get_token();
        $httprequest = self::get_token();
        $user_id = $mvsdb->real_escape_string($user_id);
        //TODO: How do we determine the term?  Have a term for each group, and use the shortest one?
        $term = 3600;
        $expiration = date('Y-m-d H:i:s',time()+$term);

        $mvsdb->query("INSERT INTO mvs_sessions (sslsession,sslrequest,httpsession,httprequest,user_id,term,expiration)
                       VALUES ('$sslsession','$sslrequest','$httpsession','$httprequest',$user_id,$term,'$expiration')");

        self::set_cookie('ssl', 'session', $sslsession, $term);
        self::set_cookie('ssl', 'request', $sslrequest, $term);
        self::set_cookie('http', 'session', $httpsession, $term);
        self::set_cookie('http', 'request', $httprequest, $term);
        
    }

    /**
     * Creates a session for a user based on a unique field => value and a password
     * @param string $field = unique field in the users table
     * @param string $value = value of that unique field for this user
     * @param string $password = the password the user entered
     */
    public static function login($field,$value,$password,$mvsdb = null) {

        if ($GLOBALS->_USER['session_id'])
            throw new Exception("Already logged in.  Log out before logging in again.", 500);

        if (empty($mvsdb))
            $mvsdb = self::db_link();

        $field = $mvsdb->real_escape_string($field);
        $value = $mvsdb->real_escape_string($value);

        $results = $mvsdb->query("SELECT s.* FROM `movabls_user`.`mvs_users` u
                                  INNER JOIN `movabls_system`.`mvs_users` s ON u.`user_id` = s.`user_id`
                                  WHERE u.`$field` = '$value'");
        if ($results->num_rows > 1)
            throw new Exception ("Login field must be unique",500);
        elseif ($results->num_rows < 1)
            throw new Exception ("Incorrect $field - password combination",500);

        $user = $results->fetch_assoc();
        $results->free();

        //TODO: Rate limit login attempts (ie. 3 attempts per minute)

        if (self::generate_password($password,$user['nonce']) != $user['password'])
            throw new Exception ("Incorrect $field - password combination",500);
        else
            self::create_session($user['user_id'],$mvsdb);

    }

    /**
     * Generates a password hash from a password and nonce
     * @param string $password
     * @param string $nonce
     */
    private static function generate_password($password,$nonce) {

        $combo = $password . $nonce;
        return hash('sha512',$combo);

    }

    /**
     * Generates a random nonce for salting passwords
     * @return string
     */
    private static function generate_nonce() {

        return md5(mt_rand());

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
            $mvsdb = self::db_link();

        if (!empty($session_id))
            $mvsdb->query("DELETE FROM mvs_sessions WHERE session_id = $session_id");
        self::remove_cookies();
        
    }

    /**
     * Remove all session-related cookies
     */
    private static function remove_cookies() {

        self::set_cookie('ssl','request',false,-86400);
        self::set_cookie('http','request',false,-86400);
        self::set_cookie('ssl','session',false,-86400);
        self::set_cookie('http','session',false,-86400);

    }

    /**
     * Public wrapper function to destroy the current user's session
     */
    public static function destroy_session() {

        $session_id = $GLOBALS->_USER['session_id'];
        self::delete_session($session_id);

    }

    /**
     * Runs through the database and deletes expired sessions
     * @param mysqli_handle $mvsdb
     */
    private static function delete_expired_sessions($mvsdb = null) {

        if (empty($mvsdb))
            $mvsdb = self::db_link();

        $mvsdb->query("DELETE FROM mvs_sessions WHERE expiration < NOW()");

    }

    /**
     * Gets the handle to access the database
     * @return mysqli handle
     */
    private static function db_link() {

        $mvsdb = new mysqli('localhost','root','h4ppyf4rmers','movabls_system');
        if (mysqli_connect_errno()) {
            printf("Connect failed: %s\n", mysqli_connect_error());
            exit();
        }
        return $mvsdb;

    }

}