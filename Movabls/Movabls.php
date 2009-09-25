<?php
/**
 * Movabls API
 * @author Travis Hardman
 */
class Movabls {

    /**
     * Gets a list of all packages on the site
     * @return array 
     */
    public static function get_packages() {

        //This requires definition of the package structure in the db
        //Packages can include any number of movabls - movabls can be in
        //multiple packages

    }

    /**
     * Gets a list of all places on the site
     * @return object
     */
    public static function get_places() {
        
        $mvsdb = Movabls::db_link();
        $result = $mvsdb->query("SELECT * FROM `mvs_places` ORDER BY url ASC");
        if(empty($result))
            return new StdClass();

        while ($row = $result->fetch_object()) {
            $ids[] = $row->place_GUID;
            $places->{$row->place_GUID} = $row;
            $places->{$row->place_GUID}->meta = array();
        }
            
        $result->free();

        $allmeta = Movabls::get_meta('place',$ids,$mvsdb);

        foreach ($allmeta as $guid => $meta)
            $places->$guid->meta = $meta;

        return $places;
	
    }

    /**
     * Gets a single movabl by type and GUID
     * @param string $movabl_type
     * @param object
     */
    public static function get_movabl($movabl_type, $movabl_guid) {

        $mvsdb = Movabls::db_link();
        $movabl_type = $mvsdb->real_escape_string($movabl_type);
        $movabl_guid = $mvsdb->real_escape_string($movabl_guid);

        $table = Movabls::table_name($movabl_type);
            
        $result = $mvsdb->query("SELECT * FROM `mvs_$table` WHERE {$movabl_type}_GUID = '$movabl_guid'");

        if (empty($result))
            throw new Exception ("Movabl ($movabl_type: $movabl_guid) not found");

        $movabl = $result->fetch_object();
        $result->free();

        $meta = Movabls::get_meta($movabl_type,$movabl_guid,$mvsdb);
        $movabl->meta = isset($meta->$movabl_guid) ? $meta->$movabl_guid : new StdClass();

        switch ($movabl_type) {
            case 'interface':
                $movabl->content = json_decode($movabl->content);
                break;
            case 'media':
            case 'function':
                $movabl->inputs = json_decode($movabl->inputs);
                break;
        }

        return $movabl;
	
    }

    /**
     * Gets the metadata for an array of Movabls or types, or an individual
     * Movabl or type
     * @param mixed $types (array or string)
     * @param mixed $guids (array or string)
     * @param mysqli handle $mvsdb
     * @return object
     */
    public static function get_meta($types = null,$guids = null,$mvsdb = null) {

        if (empty($mvsdb))
            $mvsdb = Movabls::db_link();

        $meta = new StdClass();

        $query = "SELECT * FROM `mvs_meta`";

        if (!empty($guids)) {
            if (!is_array($guids))
                $guids = array($guids);
            foreach($guids as $k => $guid)
                $guids[$k] = $mvsdb->real_escape_string($guid);
            $in_string = "'".implode("','",$guids)."'";
            $where[] = "movabls_GUID IN ($in_string)";
        }

        if (!empty($types)) {
            if (!is_array($types))
                $types = array($types);
            foreach($types as $k => $type)
                $types[$k] = $mvsdb->real_escape_string($type);
            $in_string = "'".implode("','",$types)."'";
            $where[] = "movabls_type IN ($in_string)";
        }

        if (!empty($where))
            $query .= " WHERE ".implode(' AND ',$where);
        $result = $mvsdb->query($query);

        if (empty($result))
            return $meta;

        while($row = $result->fetch_object())
            $meta->{$row->movabls_GUID}->{$row->key} = $row->value;

        $result->free();

        return $meta;

    }

    /**
     * Runs an update or insert that sets the specified movabl with this data
     * @param string $movabl_type
     * @param array $data
     * @param string $movabl_guid
     * @return string = message
     */
    public static function set_movabl($movabl_type,$data,$movabl_guid = null) {
	
        $mvsdb = Movabls::db_link();
        $meta = $data['meta'];
        $data = Movabls::sanitize_data($movabl_type,$data,$mvsdb);
        $table = Movabls::table_name($movabl_type);

        if (!empty($movabl_guid)) {
            $datastring = Movabls::generate_datastring('update',$data);
            $result = $mvsdb->query("UPDATE `mvs_$table` SET $datastring WHERE {$movabl_type}_GUID = '$movabl_id'");
        }
        else {
            $data['movabl_guid'] = Movabls::generate_guid($movabl_type);
            $datastring = Movabls::generate_datastring('insert',$data);
            $result = $mvsdb->query("INSERT INTO `mvs_$table` $datastring");
            $movabl_guid = $data['movabl_guid'];
        }

        //TODO: Be more uniform with returns and throws from these functions
        //All errors should be thrown, selects should return data, inserts should
        //return true
        Movabls::set_meta($meta,$movabl_guid);

        return true;
	
    }

    public static function set_meta($data,$movabl_guid) {
        //TODO
    }

    /**
     * Takes an array of data for a specified type of Movabl and sanitizes it to
     * match the correct columns and be safe for the sql query
     * @param string $movabl_type
     * @param array $data
     * @param mysqli handle $mvsdb
     * @return array 
     */
    private static function sanitize_data($movabl_type,$data,$mvsdb) {
        switch($movabl_type) {
            case 'media':
                $data = array(
                    'mimetype'      => $mvsdb->real_escape_string($data['mimetype']),
                    'inputs'        => $mvsdb->real_escape_string(json_encode($data['inputs'])),
                    'content'       => $mvsdb->real_escape_string($data['content'])
                );
                break;
            case 'function':
                $data = array(
                    'inputs'        => $mvsdb->real_escape_string(json_encode($data['inputs'])),
                    'content'       => $mvsdb->real_escape_string(uft8_encode($data['content']))
                );
                break;
            case 'interface':
                $data = array(
                    'content'       => $mvsdb->real_escape_string(json_encode($data['content']))
                );
                break;
            case 'place':
                $data = array(
                    'url'           => $mvsdb->real_escape_string(urlencode($data['url'])),
                    'https'         => $data['https'] ? '1' : '0',
                    'media_GUID'    => $mvsdb->real_escape_string($data['media_GUID']),
                    'interface_GUID'=> $mvsdb->real_escape_string($data['interface_GUID'])
                );
                break;
            case 'meta':

                break;
            default:
                throw new Exception('Incorrect Movabl Type');
                break;
        }
        return $data;
    }

    private static function generate_guid($movabl_type) {
        //TODO: this function.  remember that guid should reflect the site it was created on
        //to ensure global uniqueness - what if a matching guid was created on this site
        //and then deleted?  is there a better way to check uniqueness?
    }

    /**
     * Takes an array of sanitized data and prepares it as a string sql update or insert
     * @param string $query_type
     * @param array $data
     * @return string
     */
    private static function generate_datastring($query_type,$data) {
        if (empty($data))
            throw new Exception ('No Data Provided for '.uc_first($query_type));
        if ($query_type == 'update') {
            $datastring = '';
            $i = 1;
            foreach ($data as $k => $v) {
                $datastring = $i==1 ? '' : ',';
                $datastring = " `$k` = '$v'";
                $i++;
            }
        }
        elseif ($query_type == 'insert') {
            $datastring = '(`'.implode("`,`",array_keys($data)).'`) VALUES ';
            $datastring .= "('".implode("','",array_values($data))."')";
        }
        else
            throw new Exception ('Datastring Generator Only Works for Updates and Inserts');
        return $datastring;
    }

    /**
     * Gets the name of the table associated with a type of movabl
     * @param string $movabl_type
     * @return string 
     */
    private static function table_name($movabl_type) {
        if($movabl_type == 'media')
            $table = 'media';
        elseif (in_array($movabl_type,array('place','interface','function')))
            $table = $movabl_type.'s';
        else
            throw new Exception ('Please specify a valid type of Movabl');
        return $table;
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
?>