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

        if($movabl_type == 'media')
            $table = 'media';
        elseif (in_array($movabl_type,array('place','interface','function')))
            $table = $movabl_type.'s';
        else
            throw new Exception ('Please specify a valid type of Movabl');
            
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

        while($row = $result->fetch_object()) {
            $meta->{$row->movabls_GUID}->{$row->key} = $row->value;
        }

        $result->free();

        return $meta;

    }
	
    //TODO: The rest of this

    public static function set_place() {
	
        $place_id = substr($GLOBALS->_SERVER['REQUEST_URI'],11);
        if ($place_id == "")
            $place_id = "api";

        $mvsdb = Movabls::db_link();
        $query = "SELECT * FROM `mvs_places` WHERE place_GUID = '$place_id'";
        $result = mysqli_query($mvsdb, $query);

        if ($result = mysqli_query($mvsdb, $query)) {
            while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC))
                $movabl_array = $row;
        }
        mysqli_free_result($result);

        $query = "SELECT * FROM `mvs_meta` WHERE (movabl_GUID = '$place_id' AND movabl_type = 'place')";
        $result = mysqli_query($mvsdb, $query);

        if ($result = mysqli_query($mvsdb, $query)) {
            while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC) )
                $movabl_array[$row["name"]] = $row["content"];
            mysqli_free_result($result);
        }
        
        return $movabl_array;
	
    }

    public static function set_movabl($type,$movabl_guid,$data) {
	
        $data['content'] = utf8_encode($data['content']);

        $mvsdb = Movabls::db_link();
        $query = "UPDATE `mvs_media` SET content = '$content' WHERE media_GUID = '$media_id'";
        $result = mysqli_query($mvsdb, $query);
        $timestamp = date("H:i:s");
        return  "success! $timestamp";
	
    }

    public static function set_function() {
	
        $function_id = substr($GLOBALS->_SERVER['REQUEST_URI'],18);
        $content = utf8_encode ($GLOBALS->_POST["content"]);
        $content = addslashes($content);

        $mvsdb = Movabls::db_link();
        $query = "UPDATE `mvs_functions` SET content = '$content' WHERE function_GUID = '$function_id'";
        $result = mysqli_query($mvsdb, $query);
        $timestamp = date("H:i:s");

        return  "success! $timestamp";
	
    }

	public static function set_interface() {
	
        $interface_id = substr($GLOBALS->_SERVER['REQUEST_URI'],19);
        $content = utf8_encode ($GLOBALS->_POST["content"]);
        $content = addslashes($content);

        $mvsdb = Movabls::db_link();
        $query = "UPDATE `mvs_interfaces` SET content = '$content' WHERE interface_GUID = '$interface_id'";
        $result = mysqli_query($mvsdb, $query);
        $timestamp = date("H:i:s");
        return  "success! $timestamp";
	
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