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

        $mvsdb = Movabls::db_link();
        $result = $mvsdb->query("SELECT package_id,package_GUID FROM `mvs_packages`");
        if(empty($result))
            return array();

        while ($row = $result->fetch_assoc()) {
            $ids[] = $row['package_GUID'];
            $packages[$row['package_GUID']] = $row;
            $packages[$row['package_GUID']]['meta'] = array();
        }

        $result->free();

        $allmeta = Movabls::get_meta('package',$ids,$mvsdb);

        foreach ($allmeta as $guid => $meta)
            $packages[$guid]['meta'] = $meta;

        return $packages;

    }

    /**
     * Gets a list of all places on the site
     * @return array
     */
    public static function get_places() {
        
        $mvsdb = Movabls::db_link();
        $result = $mvsdb->query("SELECT * FROM `mvs_places` ORDER BY url ASC");
        if(empty($result))
            return array();

        while ($row = $result->fetch_assoc()) {
            $ids[] = $row['place_GUID'];
            $places[$row['place_GUID']] = $row;
            $places[$row['place_GUID']]['meta'] = array();
        }
            
        $result->free();

        $allmeta = Movabls::get_meta('place',$ids,$mvsdb);

        foreach ($allmeta as $guid => $meta)
            $places[$guid]['meta'] = $meta;

        return $places;
	
    }

    /**
     * Gets a single movabl by type and GUID
     * @param string $movabl_type
     * @param array
     */
    public static function get_movabl($movabl_type, $movabl_guid) {

        $mvsdb = Movabls::db_link();
        $movabl_type = $mvsdb->real_escape_string($movabl_type);
        $movabl_guid = $mvsdb->real_escape_string($movabl_guid);

        $table = Movabls::table_name($movabl_type);
            
        $result = $mvsdb->query("SELECT * FROM `mvs_$table` WHERE {$movabl_type}_GUID = '$movabl_guid'");

        if (empty($result))
            throw new Exception ("Movabl ($movabl_type: $movabl_guid) not found");

        $movabl = $result->fetch_assoc();
            
        $result->free();

        $meta = Movabls::get_meta($movabl_type,$movabl_guid,$mvsdb);
        $movabl['meta'] = isset($meta[$movabl_guid]) ? $meta[$movabl_guid] : array();

        $tagmeta = Movabls::get_tags_meta($movabl_type,$movabl_guid,$mvsdb);

        switch ($movabl_type) {
            case 'interface':
                $movabl['content'] = json_decode($movabl['content'],true);
                foreach ($movabl['content'] as $tag => $value)
                    $movabl['content'][$tag]['meta'] = isset($tagmeta[$movabl_guid][$tag]) ? $tagmeta[$movabl_guid][$tag] : array();
                break;
            case 'package':
                $movabl['contents'] = json_decode($movabl['contents'],true);
                break;
            case 'media':
            case 'function':
                $inputs = json_decode($movabl['inputs'],true);
                $movabl['inputs'] = array();
                foreach ($inputs as $input)
                    $movabl['inputs'][$input] = isset($tagmeta[$movabl_guid][$input]) ? $tagmeta[$movabl_guid][$input] : array();
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
     * @return array
     */
    public static function get_meta($types,$guids = null,$mvsdb = null) {

        if (empty($mvsdb))
            $mvsdb = Movabls::db_link();

        $meta = array();

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

        while($row = $result->fetch_assoc())
            $meta[$row['movabls_GUID']][$row['key']] = $row['value'];

        $result->free();

        return $meta;

    }

    /**
     * Gets the metadata for the inputs / outputs for an array of Movabls or types, 
     * or an individual Movabl or type
     * @param mixed $types (array or string)
     * @param mixed $guids (array or string)
     * @param mysqli handle $mvsdb
     * @return array
     */
    public static function get_tags_meta($types = null,$guids = null,$mvsdb = null) {

        if (empty($mvsdb))
            $mvsdb = Movabls::db_link();

        $meta = array();

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
                $types[$k] = $mvsdb->real_escape_string($type.'_tag');
            $in_string = "'".implode("','",$types)."'";
            $where[] = "movabls_type IN ($in_string)";
        }

        if (!empty($where))
            $query .= " WHERE ".implode(' AND ',$where);
        $result = $mvsdb->query($query);

        if (empty($result))
            return $meta;

        while($row = $result->fetch_assoc())
            $meta[$row['movabls_GUID']][$row['tag_name']][$row['key']] = $row['value'];

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
        if (!empty($data['meta']))
            $meta = $data['meta'];

        switch($movabl_type) {
            case 'media':
            case 'function':
                $tagsmeta = $data['inputs'];
                $data['inputs'] = array_keys($data['inputs']);
                break;
            case 'interface':
                foreach ($data['content'] as $tagname => $tag) {
                    $tagsmeta[$tagname] = $tag['meta'];
                    unset($data['content'][$tagname]['meta']);
                }
                break;
        }

        $data = Movabls::sanitize_data($movabl_type,$data,$mvsdb);
        $table = Movabls::table_name($movabl_type);
        $sanitized_guid = $mvsdb->real_escape_string($movabl_guid);
        $sanitized_type = $mvsdb->real_escape_string($movabl_type);

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

        if (!empty($meta))
            Movabls::set_meta($meta,$movabl_type,$movabl_guid,$mvsdb);
        if (!empty($tagsmeta))
            Movabls::set_tags_meta($tagsmeta,$movabl_type,$movabl_guid,$mvsdb);

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

        if (empty($mvsdb))
            $mvsdb = Movabls::db_link();

        $old_meta = Movabls::get_meta($movabl_type,$movabl_guid);
        $old_meta = $old_meta[$movabl_guid];

        $inserts = array();
        $updates = array();

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

        if (!empty($inserts)) {
            foreach ($inserts as $k => $v)
                $mvsdb->query("INSERT INTO `mvs_meta` (`movabls_GUID`,`movabls_type`,`tag_name`,`key`,`value`) VALUES ('$sanitized_guid','$sanitized_type',NULL,'$k','$v')");
        }
        if (!empty($updates)) {
            foreach ($updates as $k => $v)
                $mvsdb->query("UPDATE `mvs_meta` SET `value` = '$v' WHERE `movabls_type` = '$sanitized_type' AND `movabls_GUID` = '$sanitized_guid' AND `key` = '$k' AND `tag_name` IS NULL");
        }
        if (!empty($old_meta)) {
            foreach ($old_meta as $k => $v)
                $mvsdb->query("DELETE FROM `mvs_meta` WHERE `movabls_type` = '$sanitized_type' AND `movabls_GUID` = '$sanitized_guid' AND `key` = '$k' AND `tag_name` IS NULL");
        }
        
        return true;
        
    }

    /**
     * Takes an array of metadata for a particular movabl and updates the existing metadata
     * entries for the tags of that movabl to the entries specified
     * @param array $new_meta
     * @param string $movabl_type
     * @param string $movabl_guid
     * @param mysqli handle $mvsdb
     * @return bool
     */
    public static function set_tags_meta($new_tags_meta,$movabl_type,$movabl_guid,$mvsdb = null) {

        if (empty($mvsdb))
            $mvsdb = Movabls::db_link();

        $sanitized_guid = $mvsdb->real_escape_string($movabl_guid);
        $sanitized_type = $mvsdb->real_escape_string($movabl_type.'_tag');

        $old_tags_meta = Movabls::get_tags_meta($movabl_type,$movabl_guid,$mvsdb);
        $old_tags_meta = $old_tags_meta[$movabl_guid];

        foreach ($new_tags_meta as $new_tag => $new_meta) {

            $old_meta = isset($old_tags_meta[$new_tag]) ? $old_tags_meta[$new_tag] : array();
            unset($old_tags_meta[$new_tag]);
            
            $inserts = array();
            $updates = array();

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
            $sanitized_tag = $mvsdb->real_escape_string($new_tag);

            if (!empty($inserts)) {
                foreach ($inserts as $k => $v)
                    $mvsdb->query("INSERT INTO `mvs_meta` (`movabls_GUID`,`movabls_type`,`tag_name`,`key`,`value`) VALUES ('$sanitized_guid','$sanitized_type','$sanitized_tag','$k','$v')");
            }
            if (!empty($updates)) {
                foreach ($updates as $k => $v)
                    $mvsdb->query("UPDATE `mvs_meta` SET `value` = '$v' WHERE `movabls_type` = '$sanitized_type' AND `movabls_GUID` = '$sanitized_guid' AND `key` = '$k' AND `tag_name` = '$sanitized_tag'");
            }
            if (!empty($old_meta)) {
                foreach ($old_meta as $k => $v)
                    $mvsdb->query("DELETE FROM `mvs_meta` WHERE `movabls_type` = '$sanitized_type' AND `movabls_GUID` = '$sanitized_guid' AND `key` = '$k' AND `tag_name` = '$sanitized_tag'");
            }
        }

        //Remove old tags' meta for tags tied to the movabl but not in the new tags set
        if (!empty($old_tags_meta)) {
            foreach ($old_tags_meta as $old_tag => $v) {
                $sanitized_tag = $mvsdb->real_escape_string($old_tag);
                $mvsdb->query("DELETE FROM `mvs_meta` WHERE `movabls_type` = '$sanitized_type' AND `movabls_GUID` = '$sanitized_guid' AND `tag_name` = '$sanitized_tag'");
            }
        }

        return true;

    }

    /**
     * Delete a movabl from the system
     * @param mixed $movabl_type
     * @param mixed $movabl_guid
     * @return true
     */
    public static function delete_movabl($movabl_type,$movabl_guid) {

        $mvsdb = Movabls::db_link();

        $table = Movabls::table_name($movabl_type);
        $sanitized_guid = $mvsdb->real_escape_string($movabl_guid);
        $sanitized_type = $mvsdb->real_escape_string($movabl_type);

        $result = $mvsdb->query("DELETE FROM `mvs_$table` WHERE {$sanitized_type}_GUID = '$sanitized_guid'");

        Movabls::set_meta(array(),$movabl_type,$movabl_guid,$mvsdb);
        Movabls::set_tags_meta(array(),$movabl_type,$movabl_guid,$mvsdb);

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

        if (empty($data))
            return $data;
            
        switch($movabl_type) {
            case 'media':
                $data = array(
                    'mimetype'      => !empty($data['mimetype']) ? $mvsdb->real_escape_string($data['mimetype']) : '',
                    'inputs'        => !empty($data['inputs']) ? $mvsdb->real_escape_string(json_encode($data['inputs'])) : '',
                    'content'       => !empty($data['content']) ? $mvsdb->real_escape_string($data['content']) : ''
                );
                break;
            case 'function':
                $data = array(
                    'inputs'        => !empty($data['inputs']) ? $mvsdb->real_escape_string(json_encode($data['inputs'])) : '',
                    'content'       => !empty($data['content']) ? $mvsdb->real_escape_string(utf8_encode($data['content'])) : ''
                );
                break;
            case 'interface':
                $data = array(
                    'content'       => !empty($data['inputs']) ? $mvsdb->real_escape_string(json_encode($data['content'])) : ''
                );
                break;
            case 'place':
                $data = array(
                    'url'           => $mvsdb->real_escape_string(urlencode($data['url'])),
                    'https'         => $data['https'] ? '1' : '0',
                    'media_GUID'    => $mvsdb->real_escape_string($data['media_GUID']),
                    'interface_GUID'=> !empty($data['interface_GUID']) ? $mvsdb->real_escape_string($data['interface_GUID']) : null
                );
                break;
            case 'meta':
                $pre_data = $data;
                $data = array();
                foreach ($pre_data as $k => $v)
                    $data[$mvsdb->real_escape_string($k)] = $mvsdb->real_escape_string($v);
                break;
            case 'package':
                $data = array(
                    'contents' => $mvsdb->real_escape_string(json_encode($data['contents']))
                );
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
        elseif (in_array($movabl_type,array('place','interface','function','package')))
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