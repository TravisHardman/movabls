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
            foreach($row as $k => $v)
                $row->$k = stripslashes($v);
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
        foreach($movabl as $k => $v)
            $movabl->$k = stripslashes($v);
            
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

        while($row = $result->fetch_object()) {
            foreach($row as $k => $v)
                $row->$k = stripslashes($v);
            $meta->{$row->movabls_GUID}->{$row->key} = $row->value;
        }

        $result->free();

        return $meta;

    }

    /**
     * Runs an update or insert that sets the specified movabl with this data
     * @param string $movabl_type
     * @param array $data
     * @param string $movabl_guid
     * @return bool
     */
    public static function set_movabl($movabl_type,$data,$movabl_guid = null) {
	
        $mvsdb = Movabls::db_link();
        $meta = $data['meta'];
        $data = Movabls::sanitize_data($movabl_type,$data,$mvsdb);
        $table = Movabls::table_name($movabl_type);
        $sanitized_guid = $mvsdb->real_escape_string($movabl_guid);
        $sanitized_type = $mvsdb->real_escape_string($movabl_type);
        
        //TODO: File uploads to media (how do we do this without using fopen (which will have to be disabled)?

        if (!empty($movabl_guid)) {
            $datastring = Movabls::generate_datastring('update',$data);
            $result = $mvsdb->query("UPDATE `mvs_$table` SET $datastring WHERE {$sanitized_type}_GUID = '$sanitized_guid'");
        }
        else {
            $data["{$movabl_type}_guid"] = Movabls::generate_guid($movabl_type);
            $datastring = Movabls::generate_datastring('insert',$data);
            $result = $mvsdb->query("INSERT INTO `mvs_$table` $datastring");
            $movabl_guid = $data["{$movabl_type}_guid"];
        }

        Movabls::set_meta($meta,$movabl_type,$movabl_guid,$mvsdb);

        return true;
	
    }

    /**
     * Takes an array of metadata for a particular movabl and updates the existing metadata
     * entries to the entries specified
     * @param array $new_meta
     * @param string $movabl_type
     * @param string $movabl_guid
     * @param mysqli handle $mvsdb
     * @return bool 
     */
    public static function set_meta($new_meta,$movabl_type,$movabl_guid,$mvsdb = null) {
        
        //TODO: set meta for media/func inputs and interface outputs

        if (empty($mvsdb))
            $mvsdb = Movabls::db_link();

        $old_meta = Movabls::get_meta($movabl_type,$movabl_guid);

        foreach ($new_meta as $new_k => $new_v) {
            if (isset($old_meta[$new_k])) {
                if ($old_meta[$new_k] != $new_v)
                    $updates[$new_k] = $new_v;
                unset($old_meta[$new_k]);
            }
            else
                $inserts[$new_k] = $new_v;
        }

        $inserts = Movabls::sanitize_data('meta',$inserts,$mvsdb);
        $updates = Movabls::sanitize_data('meta',$updates,$mvsdb);
        $sanitized_guid = $mvsdb->real_escape_string($movabl_guid);
        $sanitized_type = $mvsdb->real_escape_string($movabl_type);

        foreach ($inserts as $k => $v)
            $mvsdb->query("INSERT INTO `mvs_meta` (`movabls_GUID`,`movabls_type`,`tag_name`,`key`,`value`) VALUES ('$sanitized_guid','$sanitized_type',NULL,'$k','$v')");
        foreach ($updates as $k => $v)
            $mvsdb->query("UPDATE `mvs_meta` SET value = '$v' WHERE movabls_type = '$sanitized_type' AND movabls_GUID = '$sanitized_guid' AND key = '$k'");
        foreach ($old_meta as $k => $v)
            $mvsdb->query("DELETE FROM `mvs_meta` WHERE movabls_type = '$sanitized_type' AND movabls_GUID = '$sanitized_guid' AND key = '$k'");

        return true;
        
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
                    'content'       => $mvsdb->real_escape_string(uft8_encode($data['content']))
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
                $pre_data = $data;
                $data = array();
                foreach ($pre_data as $k => $v)
                    $data[$mvsdb->real_escape_string($k)] = $mvsdb->real_escape_string($v);
                break;
            default:
                throw new Exception('Incorrect Movabl Type');
                break;
        }
        return $data;

    }

    private static function generate_guid($movabl_type) {
        //TODO: guid generation.  remember that guid should reflect the site it was created on
        //to ensure global uniqueness - what if a matching guid was created on this site
        //and then deleted?  is there a better way to check uniqueness?
        return rand(10000,99999);
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