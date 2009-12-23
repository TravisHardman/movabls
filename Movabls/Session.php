<?php
/**
 * Session class sets and maintains user authentication
 * @author Travis Hardman
 */
class Movabls_Session {

    /**
     * Uses the cookies variable to get the session and create the $_USER global
     * @param mysqli handle $mvsdb
     * @global array $_USER
     * @global array $_SESSION
     */
    public static function get_session($mvsdb = null) {

        //This code can only be called in bootstrapping before $GLOBALS is created
        if (!empty($GLOBALS->_USER))
            return;

        global $_USER,$_SESSION;
        $_USER = array();
        $_SESSION = array();

        if (empty($mvsdb))
            $mvsdb = self::db_link();

        //Approx every 10000 requests, delete expired sessions
        if (mt_rand(1,10000) == 14)
            self::delete_expired_sessions($mvsdb);

        if (!empty($_COOKIE['sslsession'])) {
            $type = 'ssl';
            $session = $_COOKIE['sslsession'];
        }
        elseif (!empty($_COOKIE['httpsession'])) {
            $type = 'http';
            $session = $_COOKIE['httpsession'];
        }

        unset($_COOKIE['httpsession'],$_COOKIE['sslsession']);

        if (!isset($type))
            return;
            
        $results = $mvsdb->query("SELECT * FROM mvs_sessions
                                  WHERE {$type}session = '$session'");
        if ($results->num_rows > 0) {
            $session = $results->fetch_assoc();
            $results->free();
            
            if (strtotime($session['expiration']) < time()) {
                self::delete_session($session['session_id'],$mvsdb);
                return;
            }
            else {
                $expiration = date('Y-m-d h:i:s',time()+$session['term']);
                $mvsdb->query("UPDATE mvs_sessions SET expiration = '$expiration'
                               WHERE session_id = {$session['session_id']}");
                //TODO: Regenerate cookies with new expiration? How does google do this
                //without sending cookies on each request?

                //Create $_SESSION array
                $results = $mvsdb->query("SELECT `key`,`value` FROM mvs_sessiondata
                                          WHERE session_id = {$session['session_id']}");
                while ($row = $results->fetch_assoc())
                    $_SESSION[$row['key']] = json_decode($row['value'],true);
                $results->free();
                
                //Create $_USER array
                $results = $mvsdb->query("SELECT * FROM `movabls_user`.`mvs_users`
                                          WHERE user_id = {$session['user_id']}");
                $_USER = $results->fetch_assoc();
                $_USER['session_id'] = $session['session_id'];
                $results->free();
                
                //Add $_USER['groups']
                $results = $mvsdb->query("SELECT DISTINCT group_id FROM `movabls_user`.`mvs_group_memberships`
                                          WHERE user_id = {$session['user_id']}");
                $_USER['groups'] = array();
                while($row = $results->fetch_assoc())
                    $_USER['groups'][] = $row['group_id'];
                $results->free();
            }
        }
        else {
            self::remove_cookies();
            return;
        }

    }

    /**
     * Sets a key => value pair of session data
     * @param string $key
     * @param mixed $value
     */
    public static function set($key,$value) {

        $mvsdb = self::db_link();

        $key = $mvsdb->real_escape_string($key);
        $value = json_encode($value);

        if (isset($GLOBALS->_SESSION[$key])) {
            $mvsdb->query("UPDATE mvs_sessiondata SET value = '$value'
                           WHERE session_id = {$GLOBALS->_USER['session_id']}
                           AND key = '$key'");
        }
        else {
            $mvsdb->query("INSERT INTO mvs_sessiondata (session_id,key,value)
                           VALUES ({$GLOBALS->_USER['session_id']},'$key','$value')");
        }
        $GLOBALS->set_session_data($key,$value);

    }

    /**
     * Unsets a session data key
     * @param string $key
     */
    public static function delete($key) {

        $mvsdb->query("DELETE mvs_sessiondata
                       WHERE session_id = {$GLOBALS->_USER['session_id']}
                       AND key = '$key'");
        $GLOBALS->set_session_data($key,null);

    }

    /**
     * Creates a session for the specified user
     * @param int $user_id
     * @param mysqli handle $mvsdb
     */
    public static function create_session($user_id,$mvsdb = null) {

        if (empty($mvsdb))
            $mvsdb = self::db_link();

        //TODO: Uncomment this when you have HTTPS set up
        //if (!$GLOBALS->_SERVER['HTTPS'])
          //  throw new Exception('Users may only log in over a secure (HTTPS) connection',500);

        $sslsession = self::get_token();
        $httpsession = self::get_token();
        $user_id = $mvsdb->real_escape_string($user_id);

        //To determine session term, take the term settings for each of the
        //user's groups and use the shortest term
        $results = $mvsdb->query("SELECT MIN(g.session_term) AS term FROM `movabls_user`.`mvs_groups` g
                                  INNER JOIN `movabls_user`.`mvs_group_memberships` m ON g.group_id = m.group_id
                                  WHERE m.user_id = $user_id AND g.session_term != 'NULL'");
        $row = $results->fetch_assoc();
        $results->free();
        $term = $row['term'];

        //If term is not defined. Session will remain open for a year.
        if (empty($term))
            $term = 31536000;

        $expiration = date('Y-m-d H:i:s',time()+$term);

        $mvsdb->query("INSERT INTO mvs_sessions (sslsession,httpsession,user_id,term,expiration)
                       VALUES ('$sslsession','$httpsession',$user_id,$term,'$expiration')");

        self::set_cookie('sslsession', $sslsession, $term);
        self::set_cookie('httpsession', $httpsession, $term);

    }

    /**
     * Creates an authentication token to tie the cookie to the database
     */
    private static function get_token() {

        return uniqid(sha1(mt_rand(0,100000).time().@$_SERVER['REMOTE_ADDR']), true);

    }

    /**
     * Sets a movabls session cookie
     * @param string $name
     * @param string $token
     * @param string $expiration
     */
    private static function set_cookie($name,$token,$term) {

        $expiration = time()+$term;
        $secure = $type == 'sslsession';
        setcookie($name,$token,$expiration,'/',$_SERVER['HTTP_HOST'],$secure,true);

    }

    /**
     * Delete the specified session from the database and remove session cookies
     * @param int $session_id
     * @param mysqli handle $mvsdb
     */
    public static function delete_session($session_id = null,$mvsdb = null) {
        
        if (empty($mvsdb))
            $mvsdb = self::db_link();

        if (!empty($session_id)) {
            $mvsdb->query("DELETE FROM mvs_sessions WHERE session_id = $session_id");
            $mvsdb->query("DELETE FROM mvs_sessiondata WHERE session_id = $session_id");
        }

        self::remove_cookies();
        
    }

    /**
     * Remove all session-related cookies
     */
    private static function remove_cookies() {

        self::set_cookie('ssl','session',false,-86400);
        self::set_cookie('http','session',false,-86400);

    }

    /**
     * Runs through the database and deletes expired sessions
     * @param mysqli_handle $mvsdb
     */
    private static function delete_expired_sessions($mvsdb = null) {

        if (empty($mvsdb))
            $mvsdb = self::db_link();

        $mvsdb->query("DELETE FROM mvs_sessiondata d
                       INNER JOIN mvs_sessions s ON d.session_id = s.session_id
                       WHERE s.expires < NOW()");
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