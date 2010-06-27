<?php
/**
 * Users class manages user data, and controls session creation
 * @author Travis Hardman
 */
class Movabls_Users {

    /**
     * Creates a user in the system and user databases
     * @param int $user_id
     * @param string $password
     * @param array $userfields = array(fieldname => value)
     * @param mysqli handle $mvsdb
     * @return user_id
     */
    public static function create($password,$userfields,$mvsdb = null) {

        if (empty($mvsdb))
            $mvsdb = self::db_link();

        $nonce = self::generate_nonce();
        $password = self::generate_password($password, $nonce);

        $fields = array();
        foreach ($userfields as $k => $v)
            $fields[$mvsdb->real_escape_string($k)] = $mvsdb->real_escape_string($v);

        $fieldnames = $fieldvalues = '';
        if (!empty($fields)) {
            $fieldnames = ',`'.implode('`,`',array_keys($fields)).'`';
            $fieldvalues = ",'".implode("','",array_values($fields))."'";
        }

        $mvsdb->query("INSERT INTO `movabls_user`.`mvs_users` (password,nonce$fieldnames)
                       VALUES ('$password','$nonce'$fieldvalues)");

        if ($mvsdb->errno)
            throw new Exception('MYSQL Error: '.$mvsdb->error,500);
        else
            return $mvsdb->insert_id;

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

        $results = $mvsdb->query("SELECT user_id,password,nonce FROM `movabls_user`.`mvs_users`
                                  WHERE `$field` = '$value'");
        if ($mvsdb->errno)
            throw new Exception('MYSQL Error: '.$mvsdb->error,500);
        elseif ($results->num_rows > 1)
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
     * Change a user's password
     * @param user_id $user_id
     * @param string $password
     * @param mysqli handle $mvsdb
     */
    public static function change_password($user_id,$password,$mvsdb = null) {

        if (empty($mvsdb))
            $mvsdb = self::db_link();

        $user_id = $mvsdb->real_escape_string($user_id);
        $nonce = self::generate_nonce();
        $password = self::generate_password($password, $nonce);

        $mvsdb->query("UPDATE `movabls_user`.`mvs_users` SET password = '$password',nonce = '$nonce'
                       WHERE user_id = $user_id");

        if ($mvsdb->errno)
            throw new Exception('MYSQL Error: '.$mvsdb->error,500);

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
        if (mysqli_connect_errno())
            throw new Exception("Database connection failed: ".mysqli_connect_error());
        return $mvsdb;

    }

}