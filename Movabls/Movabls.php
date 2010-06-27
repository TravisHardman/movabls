<?php
/**
 * Movabls API
 * @author Travis Hardman
 */
class Movabls {

    /**
     * Gets a list of all packages on the site
     * @param mysqli_handle $mvsdb
     * @return array 
     */
    public static function get_packages($mvsdb=null) {

        if(empty($mvsdb))
            $mvsdb = self::db_link();

        $result = $mvsdb->query("SELECT package_id,package_GUID FROM `mvs_packages`");
        if(empty($result))
            return array();

        while ($row = $result->fetch_assoc()) {
            $ids[] = $row['package_GUID'];
            $packages[$row['package_GUID']] = $row;
            $packages[$row['package_GUID']]['meta'] = array();
        }

        $result->free();

        $allmeta = self::get_meta('package',$ids,$mvsdb);

        foreach ($allmeta as $guid => $meta)
            $packages[$guid]['meta'] = $meta;

        return $packages;

    }

    /**
     * Adds a movabl to the given package
     * @param string $package_guid
     * @param string $movabl_type
     * @param string $movabl_guid
     * @param mysqli handle $mvsdb
     * @return bool
     */
    public static function add_to_package($package_guid, $movabl_type, $movabl_guid, $mvsdb=null) {

        if(empty($mvsdb))
            $mvsdb = self::db_link();

        $package = self::get_movabl('package', $package_guid, $mvsdb);
        foreach ($package['contents'] as $movabl) {
            if ($movabl['movabl_type'] == $movabl_type && $movabl['movabl_GUID'] == $movabl_guid)
                return true;
        }

        $package['contents'][] = array(
            'movabl_type' => $movabl_type,
            'movabl_GUID' => $movabl_guid
        );
        self::set_movabl('package',$package,$mvsdb);
        return true;

    }

    /**
     * Removes a movabl from the given package
     * @param string $package_guid
     * @param string $movabl_type
     * @param string $movabl_guid
     * @param mysqli handle $mvsdb
     * @return bool
     */
    public static function remove_from_package($package_guid, $movabl_type, $movabl_guid, $mvsdb=null) {

        if(empty($mvsdb))
            $mvsdb = self::db_link();

        $package = self::get_movabl('package', $package_guid, $mvsdb);
        foreach ($package['contents'] as $key => $movabl) {
            if ($movabl['movabl_type'] == $movabl_type && $movabl['movabl_GUID'] == $movabl_guid) {
                unset($package['contents'][$key]);
                $package['contents'] = array_values($package['contents']);
                self::set_movabl('package',$package,$mvsdb);
                break;
            }
        }
        return true;

    }

    /**
     * Takes a url and an array of inputs and constructs the place url
     * @param string $url
     * @param array $inputs
     * @return string
     */
    private static function construct_place_url($url,$inputs) {

        if (!empty($inputs)) {
            foreach ($inputs as $key => $input)
                $inputs[$key] = '{{'.$input.'}}';
            $url = str_replace('%','%s',$url);
            $url = vsprintf($url,$inputs);
        }

        return $url;

    }
    
    /**
     * Gets all movabls that match the specified filters
     * @param string/array $types
     * @param string $sort
     * @param array $packages
     * @param string $filter
     * @param mysqli handle $mvsdb
     */
    public static function get_index($types = 'all', $sort = null, $packages = 'all', $filter = null, $mvsdb=null) {
                
        if(empty($mvsdb))
            $mvsdb = self::db_link();

        if($types == 'all')
            $movabls = array('media'=>array(),'functions'=>array(),'interfaces'=>array(),'places'=>array());
        elseif(!is_array($types)) {
            if (!in_array($types,array('media','functions','interfaces','places')))
                throw new Exception ("Invalid type specified: $type");
            $movabls = array($types=>array());
        }
        else {
            $movabls = array();
            foreach($types as $type) {
                if (!in_array($type,array('media','functions','interfaces','places')))
                    throw new Exception ("Invalid type specified: $type");
                $movabls[$type] = array();
            }
        }

        if(in_array('media',$movabls)) {
            $query = "SELECT media_GUID,mimetype FROM mvs_media";
            $query = self::query_filter_packages('media',$query,$packages,$mvsdb);
            $query = self::query_filter_text('media',$query,$filter,$mvsdb);
            $query = self::query_sort($query,$sort,$mvsdb);
            $results = $mvsdb->query($query);
            //TODO: Finish getting index
        }
    
    }

    /**
     * Adds the necessary text to a SQL query to filter the result by package membership
     * @param string $type
     * @param string $query
     * @param array $packages
     * @param mysqli handle $mvsdb
     * @return string
     */
    private static function query_filter_packages($type,$query,$packages,$mvsdb) {

        if ($packages == 'all')
            return $query;

        //TODO: this function
        return $query;

    }

    /**
     * Adds the necessary text to a SQL query to filter the result by meta text search
     * @param string $type
     * @param string $query
     * @param string $text
     * @param mysqli handle $mvsdb 
     * @return string
     */
    private static function query_filter_text($type,$query,$text,$mvsdb) {

        //TODO: this function
        return $query;

    }

    /**
     * Adds the necessary text to a SQL query to sort the result
     * @param string $query
     * @param string $sort
     * @param mysqli handle $mvsdb
     * @return string
     */
    private static function query_sort($query,$sort,$mvsdb) {

        //TODO: this function
        return $query;

    }

    /**
     * Gets a single movabl by type and GUID
     * @param string $movabl_type
     * @param string $movabl_guid
     * @param mysqli handle $mvsdb
     * @param array
     */
    public static function get_movabl($movabl_type, $movabl_guid, $mvsdb=null) {

        if(empty($mvsdb))
            $mvsdb = self::db_link();

        $movabl_type = $mvsdb->real_escape_string($movabl_type);
        $movabl_guid = $mvsdb->real_escape_string($movabl_guid);

        $table = self::table_name($movabl_type);
            
        $result = $mvsdb->query("SELECT x.* FROM `mvs_$table` AS x WHERE x.{$movabl_type}_GUID = '$movabl_guid'");

        if (empty($result))
            throw new Exception ("Movabl ($movabl_type: $movabl_guid) not found",500);

        $movabl = $result->fetch_assoc();
            
        $result->free();

        $meta = self::get_meta($movabl_type,$movabl_guid,$mvsdb);
        $movabl['meta'] = isset($meta[$movabl_guid]) ? $meta[$movabl_guid] : array();

        $tagmeta = self::get_tags_meta($movabl_type,$movabl_guid,$mvsdb);

        switch ($movabl_type) {
            case 'interface':
                $movabl['content'] = json_decode($movabl['content'],true);
                if(is_array($movabl['content'])) {
                    foreach ($movabl['content'] as $tag => $value)
                        $movabl['content'][$tag]['meta'] = isset($tagmeta[$movabl_guid][$tag]) ? $tagmeta[$movabl_guid][$tag] : array();
                }
                break;
            case 'package':
                $movabl['contents'] = json_decode($movabl['contents'],true);
                break;
            case 'media':
            case 'function':
                $inputs = json_decode($movabl['inputs'],true);
                $movabl['inputs'] = array();
                if(is_array($inputs)) {
                    foreach ($inputs as $input)
                        $movabl['inputs'][$input] = isset($tagmeta[$movabl_guid][$input]) ? $tagmeta[$movabl_guid][$input] : array();
                }
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
    public static function get_meta($types,$guids = null, $mvsdb = null) {

        if(empty($mvsdb))
            $mvsdb = self::db_link();

        $meta = array();

        $query = "SELECT m.* FROM `mvs_meta` AS m";       

        if (!empty($guids)) {
            if (!is_array($guids))
                $guids = array($guids);
            foreach($guids as $k => $guid)
                $guids[$k] = $mvsdb->real_escape_string($guid);
            $in_string = "'".implode("','",$guids)."'";
            $where[] = "m.movabls_GUID IN ($in_string)";
        }

        if (!empty($types)) {
            if (!is_array($types))
                $types = array($types);
            foreach($types as $k => $type)
                $types[$k] = $mvsdb->real_escape_string($type);
            $in_string = "'".implode("','",$types)."'";
            $where[] = "m.movabls_type IN ($in_string)";
        }

        $query .= 'WHERE ' . implode(' AND ',$where);

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

        if(empty($mvsdb))
            $mvsdb = self::db_link();

        $meta = array();

        $query = "SELECT m.* FROM `mvs_meta` AS m";

        if (!empty($guids)) {
            if (!is_array($guids))
                $guids = array($guids);
            foreach($guids as $k => $guid)
                $guids[$k] = $mvsdb->real_escape_string($guid);
            $in_string = "'".implode("','",$guids)."'";
            $where[] = "m.movabls_GUID IN ($in_string)";
        }

        if (!empty($types)) {
            if (!is_array($types))
                $types = array($types);
            foreach($types as $k => $type)
                $types[$k] = $mvsdb->real_escape_string($type.'_tag');
            $in_string = "'".implode("','",$types)."'";
            $where[] = "m.movabls_type IN ($in_string)";
        }

        $query .= 'WHERE ' . implode(' AND ',$where);

        $result = $mvsdb->query($query);

        if (empty($result))
            return $meta;

        while ($row = $result->fetch_assoc())
            $meta[$row['movabls_GUID']][$row['tag_name']][$row['key']] = $row['value'];

        $result->free();

        return $meta;

    }
    
    /**
     * Searches the specified meta field for the specified query and returns the resulting movabls
     * along with that meta field for each one.
     * @param array $types
     * @param string $field
     * @param string $query
     * @param mysqli handle $mvsdb
     * @return array of movabl types, guids, and fields
     */
    public static function quicksearch($types, $field, $query, $mvsdb = null) {
            
        if(empty($mvsdb))
            $mvsdb = self::db_link();

        $field = $mvsdb->real_escape_string($field);
        $query = $mvsdb->real_escape_string($query);
        foreach ($types as $k => $type)
            $types[$k] = $mvsdb->real_escape_string($type);
        $types = "'" . implode("','",$types) . "'";
            
        $result = $mvsdb->query("SELECT movabls_type, movabls_GUID, `value` FROM mvs_meta AS m
                                 WHERE `key` = '$field' AND `value` LIKE '%$query%'
                                 AND movabls_type IN ($types)
                                 ORDER BY `value` ASC");
        
        if (empty($result))
            return array();
        else {
            while($row = $result->fetch_assoc()) {
                $return[] = $row;
            }
            return $return;
        }
    
    }

    /**
     * Runs an update or insert that sets the specified movabl with this data
     * @param string $movabl_type
     * @param array $data
     * @param string $movabl_guid
     * @param mysqli handle $mvsdb
     * @return $movabl_guid
     */
    public static function set_movabl($movabl_type,$data,$movabl_guid = null, $mvsdb = null) {

        //TODO: Add a warning if this is a global movabl - maybe you have to specify an overwrite flag

        if(empty($mvsdb))
            $mvsdb = self::db_link();

        $mvsdb->autocommit(false);
        
        $original_data = $data;

        if (!empty($data['meta']))
            $meta = $data['meta'];

        switch($movabl_type) {
            case 'media':
            case 'function':
                $tagsmeta = $data['inputs'];
                $data['inputs'] = array_keys($data['inputs']);
                break;
            case 'interface':
                if (!empty($data['content'])) {
                    foreach ($data['content'] as $tagname => $tag) {
                        $tagsmeta[$tagname] = !empty($tag['meta']) ? $tag['meta'] : array();
                        unset($data['content'][$tagname]['meta']);
                    }
                }
                else
                    $tagsmeta = array();
                break;
            case 'place':
                //If url includes {{something}}, extract those and use them to replace the inputs
                if (preg_match_all('/{{.*}}/',$data['url'],$matches)) {
                    $data['url'] = preg_replace('/{{.*}}/','%',$data['url']);
                    $data['inputs'] = array();
                    foreach ($matches[0] as $match)
                        $data['inputs'][] = substr($match,2,-2);
                }
                break;
        }

        $data = self::sanitize_data($movabl_type,$data,$mvsdb);
        $table = self::table_name($movabl_type);
        $sanitized_guid = $mvsdb->real_escape_string($movabl_guid);
        $sanitized_type = $mvsdb->real_escape_string($movabl_type);

        if (!empty($movabl_guid)) {
            $datastring = self::generate_datastring('update',$data);
            $result = $mvsdb->query("UPDATE `mvs_$table` SET $datastring WHERE {$sanitized_type}_GUID = '$sanitized_guid'");
            //Delete old mvs_children and mvs_descendants entries
            if (in_array($movabl_type,array('place','interface','package'))) {
                $mvsdb->query("DELETE FROM mvs_children WHERE parent_type = '$sanitized_type' AND parent_GUID = '$sanitized_guid'");
                $mvsdb->query("DELETE FROM mvs_descendants WHERE ancestor_type = '$sanitized_type' AND ancestor_GUID = '$sanitized_guid'");
            }
        }
        else {
            $data["{$sanitized_type}_guid"] = self::generate_guid($sanitized_type);
            $datastring = self::generate_datastring('insert',$data);
            $result = $mvsdb->query("INSERT INTO `mvs_$table` $datastring");
            $movabl_guid = $data["{$sanitized_type}_guid"];
        }

        //Add new mvs_children and mvs_descendants entries
        if (in_array($movabl_type,array('place','interface','package'))) {
            $children = self::extract_children($sanitized_type, $original_data);
            foreach ($children as $child) {
                $mvsdb->query("INSERT INTO mvs_children (
                                child_type,child_GUID,parent_type,parent_GUID
                               ) VALUES (
                                '{$child['movabl_type']}','{$child['movabl_GUID']}','$sanitized_type','$sanitized_guid'
                               )");
            }
            $descendants = self::extract_descendants($sanitized_type, $sanitized_guid, $mvsdb);
            foreach ($descendants as $descendant) {
                $mvsdb->query("INSERT INTO mvs_descendants (
                                descendant_type,descendant_GUID,ancestor_type,ancestor_GUID
                               ) VALUES (
                                '{$descendant['movabl_type']}','{$descendant['movabl_GUID']}','$sanitized_type','$sanitized_guid'
                               )");
            }
        }

        if (!empty($meta))
            self::set_meta($meta,$movabl_type,$movabl_guid,$mvsdb);
        if (!empty($tagsmeta))
            self::set_tags_meta($tagsmeta,$movabl_type,$movabl_guid,$mvsdb);

        $mvsdb->commit();

        return $movabl_guid;    
    }
    
    /**
     * Gets a list of movabls that are directly beneath the one specified
     * @param string $movabl_type
     * @param array $data
     * @return array
     */
    private static function extract_children($movabl_type,$data) {
        
        $sub_movabls = array();
        switch ($movabl_type) {
            case 'package':
                $sub_movabls = $data['contents'];
                break;
            case 'place':
                if (!empty($data['media_GUID']))
                    $sub_movabls[] = array(
                        'movabl_type' => 'media',
                        'movabl_GUID' => $data['media_GUID']
                    );
                if (!empty($data['interface_GUID']))
                    $sub_movabls[] = array(
                        'movabl_type' => 'interface',
                        'movabl_GUID' => $data['interface_GUID']
                    );
                break;
            case 'interface':
                if (!empty($data['content']))
                    $sub_movabls = self::extract_tags($data['content']);
                break;
        }
        return $sub_movabls;
        
    }

    /**
     * Extracts children from an interface
     * @param tags $tags
     * @param extras array so far $extras
     * @return extras array after this round
     */
    private static function extract_tags($tags,$extras = array()) {

        if (!empty($tags)) {
            foreach ($tags as $value) {
                if (isset($value['movabl_type']))
                    $extras[] = array('movabl_type'=>$value['movabl_type'],'movabl_GUID'=>$value['movabl_GUID']);
                if (isset($value['tags']))
                    $extras = self::extract_tags($value['tags'],$extras);
                elseif (isset($value['interface_GUID']))
                    $extras[] = array('movabl_type'=>'interface','movabl_GUID'=>$value['interface_GUID']);
            }
        }

        return $extras;

    }

    /**
     * Extracts descendants from the mvs_children table
     * Note: mvs_children must be populated before this function is run
     * @param string $movabl_type
     * @param string $movabl_guid
     * @param mysqli handle $mvsdb
     * @param array $descendants
     * @return array
     */
    private static function extract_descendants($movabl_type,$movabl_guid,$mvsdb,$descendants) {

        //TODO: test this function and whole set_movabl process

        if (!in_array($movabl_type,array('place','interface','package')))
            return array();

        $guids = array();
        foreach ($descendants as $descendant)
            $guids[] = $descendant['movabl_guid'];
        $guids_string = "'".implode("','",$guids)."'";

        $children = array();
        $results = $mvsdb->query("SELECT child_type,child_GUID FROM mvs_children
                                  WHERE parent_type = '$movabl_type' AND parent_GUID = '$movabl_guid'
                                  AND child_GUID NOT IN ($guids_string)");
        if(empty($results))
            return array();
        else {
            while($row = $results->fetch_assoc()) {
                $children[] = array(
                    'movabl_type' => $row['child_type'],
                    'movabl_GUID' => $row['child_GUID']
                );
            }
        }

        $descendants = $children;
        foreach ($children as $child) {
            $more = self::extract_descendants($child['movabl_type'], $child['movabl_GUID'], $mvsdb, $descendants);
            $descendants = array_merge($descendants,$more);
        }
        
        return $descendants;

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
    public static function set_meta($new_meta,$movabl_type,$movabl_guid,$mvsdb=null) {

        if(empty($mvsdb))
            $mvsdb = self::db_link();

        $old_meta = self::get_meta($movabl_type,$movabl_guid,$mvsdb);
        if (!empty($old_meta[$movabl_guid]))
            $old_meta = $old_meta[$movabl_guid];
        else
            $old_meta = array();

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

        $inserts = self::sanitize_data('meta',$inserts,$mvsdb);
        $updates = self::sanitize_data('meta',$updates,$mvsdb);
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
    public static function set_tags_meta($new_tags_meta,$movabl_type,$movabl_guid,$mvsdb=null) {

        if(empty($mvsdb))
            $mvsdb = self::db_link();

        $sanitized_guid = $mvsdb->real_escape_string($movabl_guid);
        $sanitized_type = $mvsdb->real_escape_string($movabl_type.'_tag');

        $old_tags_meta = self::get_tags_meta($movabl_type,$movabl_guid,$mvsdb);
        if (!empty($old_tags_meta))
            $old_tags_meta = $old_tags_meta[$movabl_guid];
        else
            $old_tags_meta = array();

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

            $inserts = self::sanitize_data('meta',$inserts,$mvsdb);
            $updates = self::sanitize_data('meta',$updates,$mvsdb);
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
     * Gets all fields used for metadata on the site
     * @param mysqli handle $mvsdb
     * @return array
     */
    public static function get_meta_fields($mvsdb=null) {
            
        if(empty($mvsdb))
            $mvsdb = self::db_link();

        $result = $mvsdb->query("SELECT DISTINCT `key` FROM mvs_meta ORDER BY `key` ASC");

        $fields = array();
        if (!empty($result)) {
            while ($row = $result->fetch_assoc())
                $fields[] = $row['key'];
        }
        $result->free();
        
        return $fields;
    
    }

    /**
     * Delete a movabl from the system
     * @param mixed $movabl_type
     * @param mixed $movabl_guid
     * @param mysqli handle $mvsdb
     * @return true
     */
    public static function delete_movabl($movabl_type,$movabl_guid,$mvsdb=null) {

        if(empty($mvsdb))
            $mvsdb = self::db_link();

        $table = self::table_name($movabl_type);
        $sanitized_guid = $mvsdb->real_escape_string($movabl_guid);
        $sanitized_type = $mvsdb->real_escape_string($movabl_type);

        $result = $mvsdb->query("DELETE FROM `mvs_$table` WHERE {$sanitized_type}_GUID = '$sanitized_guid'");

        self::set_meta(array(),$movabl_type,$movabl_guid,$mvsdb);
        self::set_tags_meta(array(),$movabl_type,$movabl_guid,$mvsdb);
        self::delete_references($sanitized_type,$sanitized_guid,$mvsdb);

        $mvsdb->query("DELETE FROM `mvs_children` WHERE parent_type = '$movabl_type' AND parent_GUID = '$movabl_GUID'");

        return true;

    }

    /**
     * Deletes all references to this movabl in places, interfaces and packages
     * @param string $movabl_type
     * @param string $movabl_guid
     * @param mysqli handle $mvsdb
     */
    private static function delete_references($movabl_type,$movabl_guid,$mvsdb) {

        //Packages
        $results = $mvsdb->query("SELECT * FROM mvs_packages
                                  WHERE contents LIKE '%\"movabl_type\":\"$movabl_type\",\"movabl_GUID\":\"$movabl_guid\"%'
                                  OR contents LIKE '%\"movabl_GUID\":\"$movabl_guid\",\"movabl_type\":\"$movabl_type\"%'");
        if (!empty($results)) {
            while ($row = $results->fetch_assoc()) {
                unset($row['package_id']);
                $row['contents'] = json_decode($row['contents'],true);
                foreach ($row['contents'] as $k => $content) {
                    if ($content == array('movabl_type'=>$movabl_type,'movabl_GUID'=>$movabl_guid))
                        unset($row['contents'][$k]);
                }
                self::set_movabl('package', $row, $row['package_GUID'], $mvsdb);
            }
            $results->free();
        }

        //Places
        $results = $mvsdb->query("SELECT * FROM mvs_places
                                  WHERE {$movabl_type}_GUID LIKE '%$movabl_guid%'");
        if (!empty($results)) {
            while ($row = $results->fetch_assoc()) {
                unset($row['place_id'],$row[$movabl_type.'_GUID']);
                self::set_movabl('place', $row, $row['place_GUID'], $mvsdb);
            }
            $results->free();
        }

        //Interface
        $results = $mvsdb->query("SELECT * FROM mvs_interfaces
                                  WHERE content LIKE '%$movabl_guid%'");
        if (!empty($results)) {
            while ($row = $results->fetch_assoc()) {
                unset($row['interface_id']);
                $row['content'] = json_decode($row['content'],true);
                $row['content'] = self::delete_from_interface($row['content'],$movabl_type,$movabl_guid,$mvsdb);
                self::set_movabl('interface', $row, $row['interface_GUID'], $mvsdb);
            }
            $results->free();
        }

        $mvsdb->query("DELETE FROM mvs_children WHERE child_type = '$movabl_type' AND child_GUID = '$movabl_guid'");

    }

    /**
     * Runs through the interface tree and removes the given movabl
     * @param array $tree
     * @param string $movabl_type
     * @param string $movabl_guid
     * @param mysqli handle $mvsdb
     * @return array revised tree
     */
    private static function delete_from_interface($tree, $movabl_type, $movabl_guid, $mvsdb) {

        if (!empty($tree)) {
            foreach ($tree as $tagname => $tagvalue) {
                if (!empty($tagvalue['movabl_type']) && $tagvalue['movabl_type'] == $movabl_type && $tagvalue['movabl_GUID'] == $movabl_guid) {
                    $tree[$tagname]['movabl_type'] = null;
                    $tree[$tagname]['movabl_GUID'] = null;
                }
                elseif (!empty($tagvalue['interface_GUID']) && $movabl_type == 'interface' && $tagvalue['interface_GUID'] == $movabl_guid)
                    $tree[$tagname]['interface_GUID'] = null;
                elseif (!empty($tagvalue['tags']))
                    $tree[$tagname]['tags'] = self::delete_from_interface($tree[$tagname]['tags'],$movabl_type,$movabl_guid,$mvsdb);
            }
            return $tree;
        }
        else
            return array();

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
                    'content'       => !empty($data['content']) ? $mvsdb->real_escape_string(json_encode($data['content'])) : ''
                );
                break;
            case 'place':
                if (!empty($data['interface_GUID']))
                    $clean_interface_GUID =  $mvsdb->real_escape_string($data['interface_GUID']);
                $data = array(
                    'url'           => $mvsdb->real_escape_string($data['url']),
                    'inputs'        => !empty($data['inputs']) ? $mvsdb->real_escape_string(json_encode($data['inputs'])) : '',
                    'https'         => $data['https'] ? '1' : '0',
                    'media_GUID'    => $mvsdb->real_escape_string($data['media_GUID']),
                );
                if (!empty($clean_interface_GUID))
                    $data['interface_GUID'] = $clean_interface_GUID;
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
                throw new Exception('Incorrect Movabl Type',500);
                break;
        }
        return $data;

    }

    /**
     * Generates a globally unique 32-byte string consisting of 0-9 and a-z characters
     * @param string $movabl_type
     * @return string 
     */
    private static function generate_guid($movabl_type) {

        //Movabl type - 3 characters ensures uniqueness across types
        switch ($movabl_type) {
            case 'media': $type = 'mda'; break;
            case 'function': $type = 'fnc'; break;
            case 'interface': $type = 'int'; break;
            case 'place': $type = 'plc'; break;
            case 'package': $type = 'pkg'; break;
            default:
                throw new Exception ('Invalid movabl type specified for guid generation',500);
                break;
        }

        //Site ID - 6 characters in base 36 ensures uniqueness across sites
        $site_id = $GLOBALS->_SERVER['SITE_ID'];
        $site_id = base_convert($site_id,10,36);
        $site_id = str_pad($site_id,8,'0',STR_PAD_LEFT);

        //Microtime - 9 characters in base 36 ensures uniqueness within this site
        $microtime = microtime(true)*10000;
        $microtime = base_convert($microtime,10,36);

        //Random number - 12 characters in base 36 ensures randomness
        $rand = '';
        for ($i=1;$i<=12;$i++)
            $rand .= base_convert(mt_rand(0,35),10,36);

        return $type . $site_id . $microtime . $rand;

    }

    /**
     * Takes an array of sanitized data and prepares it as a string sql update or insert
     * @param string $query_type
     * @param array $data
     * @return string
     */
    private static function generate_datastring($query_type,$data) {

        if (empty($data))
            throw new Exception ('No Data Provided for '.uc_first($query_type),500);
        if ($query_type == 'update') {
            $datastring = '';
            $i = 1;
            foreach ($data as $k => $v) {
                $datastring .= $i==1 ? '' : ',';
                $datastring .= " `$k` = '$v'";
                $i++;
            }
        }
        elseif ($query_type == 'insert') {
            $datastring = '(`'.implode("`,`",array_keys($data)).'`) VALUES ';
            $datastring .= "('".implode("','",array_values($data))."')";
        }
        else
            throw new Exception ('Datastring Generator Only Works for Updates and Inserts',500);
        return $datastring;

    }

    /**
     * Gets all groups present on this site
     * @param mysqli handle $mvsdb
     * @return groups array
     */
    public static function get_groups($mvsdb=null) {

        if(empty($mvsdb))
            $mvsdb = self::db_link();

        $results = $mvsdb->query("SELECT * FROM `{$GLOBALS->_SERVER['DATABASE']}`.mvs_groups");
        while($row = $results->fetch_assoc())
            $return[] = $row;
        $results->free();

        return $return;

    }

    /**
     * Gets the name of the table associated with a type of movabl
     * @param string $movabl_type
     * @return string 
     */
    public static function table_name($movabl_type) {

        if($movabl_type == 'media')
            $table = 'media';
        elseif (in_array($movabl_type,array('place','interface','function','package')))
            $table = $movabl_type.'s';
        else
            throw new Exception ('Invalid Movabl Type Specified',500);
        return $table;
        
    }

    /**
     * Gets the handle to access the database
     * @return mysqli handle
     */
    private static function db_link() {

        if (!in_array(1,$GLOBALS->_USER['groups']))
            throw new Exception("You must be an administrator to access the Movabls API");

        $mvsdb = new mysqli('localhost','root','h4ppyf4rmers','movabls_system');
        if (mysqli_connect_errno())
            throw new Exception("Database connection failed: ".mysqli_connect_error());
        return $mvsdb;

    }

}