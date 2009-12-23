<?php
/**
 * Users class manages user data, and controls session creation
 * @author Travis Hardman
 */
class Movabls_Users {

    /**
     * Creates a user in the system database
     * @param int $user_id
     * @param string $password
     * @param mysqli handle $mvsdb
     */
    public static function create($user_id,$password,$mvsdb = null) {

        if (empty($mvsdb))
            $mvsdb = self::db_link();

        $user_id = $mvsdb->real_escape_string($user_id);
        $nonce = self::generate_nonce();
        $password = self::generate_password($password, $nonce);

        $mvsdb->query("INSERT INTO mvs_users (user_id,password,nonce)
                       VALUES ($user_id,'$password','$nonce')");

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
            Movabls_Session::create_session($user['user_id'],$mvsdb);

    }

    /**
     * Public wrapper function to destroy the current user's session
     */
    public static function logout() {

        $session_id = $GLOBALS->_USER['session_id'];
        Movabls_Session::delete_session($session_id);

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