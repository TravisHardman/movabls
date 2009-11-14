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

        $permissions = Movabls::join_permissions('package');

        $result = $mvsdb->query("SELECT x.package_id,x.package_GUID FROM `mvs_packages` AS x $permissions");
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
     * Gets a full index of the site, filtering for certain packages
     * Places return the full place info
     * Media, Functions, Interfaces return meta
     * @param array $packages
     * @param bool $uncategorized = whether to get movabls not in a package
     * @return array ('places','media','functions','interfaces')
     */
    public static function get_index($packages = 'all',$uncategorized = true) {
        
        $mvsdb = Movabls::db_link();
        
        $index = Movabls::get_packages_content($packages,$mvsdb);

        //If uncategorized, get all of the movabls not in any packages
        if ($uncategorized)
            Movabls::add_uncategorized_to_index($index,$mvsdb);

        //Get meta for all four index arrays
        if (!empty($index['media'])) {
            $all_meta = Movabls::get_meta('media',array_keys($index['media']),$mvsdb);
            foreach ($all_meta as $guid => $meta)
                $index['media'][$guid] = $meta;
        }
        if (!empty($index['functions'])) {
            $all_meta = Movabls::get_meta('function',array_keys($index['functions']),$mvsdb);
            foreach ($all_meta as $guid => $meta)
                $index['functions'][$guid] = $meta;
        }
        if (!empty($index['interfaces'])) {
            $interfaces_meta = Movabls::get_meta('interface',array_keys($index['interfaces']),$mvsdb);
            foreach ($interfaces_meta as $guid => $meta)
                $index['interfaces'][$guid]['meta'] = $meta;
        }
        if (!empty($index['places'])) {
            $places_meta = Movabls::get_meta('place',array_keys($index['places']),$mvsdb);
            foreach ($places_meta as $guid => $meta)
                $index['places'][$guid]['meta'] = $meta;
        }

        //Loop through interfaces and add meta to toplevel tags
        if (!empty($index['interfaces'])) {
            $tagmeta = Movabls::get_tags_meta('interface',array_keys($index['interfaces']),$mvsdb);
            foreach ($index['interfaces'] as $row) {
                if(is_array($row['content'])) {
                    foreach ($row['content'] as $tag => $value)
                        $row['content'][$tag]['meta'] = isset($tagmeta[$row['interface_GUID']][$tag]) ? $tagmeta[$row['interface_GUID']][$tag] : array();
                }
                $index['interfaces'][$row['interface_GUID']] = $row;
            }
        }

        return $index;
	
    }

    /**
     * Gets an index of all movabls contained in the specified packages
     * @param array $packages
     * @param mysqli handle $mvsdb
     * @return array 
     */
    private static function get_packages_content($packages = 'all',$mvsdb = null) {

        if (empty($mvsdb))
            $mvsdb = Movabls::db_link();

        if (!empty($packages) && $packages != 'all') {
            foreach ($packages as $k => $package)
                $packages[$k] = $mvsdb->real_escape_string($package);
        }

        $index = array(
            'media' => array(),
            'functions' => array(),
            'interfaces' => array(),
            'places' => array()
        );

        $where = array();

        if ($packages != 'all') {
            $package_string = "'".explode("','",$packages)."'";
            $where[] = "package_GUID IN ($package_string)";
        }

        $permissions = Movabls::join_permissions('package',$where);

        $result = $mvsdb->query("SELECT * FROM mvs_packages x $permissions");
        
        //Add package elements to the index array
        while ($row = $result->fetch_assoc()) {
            $contents = json_decode($row['contents'],true);
            foreach ($contents as $movabl)
                Movabls::add_item_to_index($index,$movabl['movabl_type'],$movabl['movabl_GUID']);
        }
        $result->free();
        
        //Get all sub-elements of places and interfaces in the selected packages
        if (!empty($index['places']) || !empty($index['interfaces']))
            $new = true;
        else
            $new = false;
        while ($new) {
            
            $new = false;

            //See if new places came from the last iteration, and add their sub-elements
            $places = array();
            foreach ($index['places'] as $guid => $sub) {
                if ($sub === false)
                    $places[] = $guid;
            }
            if (!empty($places)) {
                $places_string = "'".implode("','",$places)."'";
                $result = $mvsdb->query("SELECT * FROM mvs_places WHERE place_GUID IN ($places_string)");
                if ($result->num_rows == 0) {
                    foreach ($places as $guid)
                        unset($index['places'][$guid]);
                }
                while ($row = $result->fetch_assoc()) {
                    $row['inputs'] = json_decode($row['inputs']);
                    $row['url'] = Movabls::construct_place_url($row['url'],$row['inputs']);
                    $index['places'][$row['place_GUID']] = $row;
                    Movabls::add_item_to_index($index,'media',$row['media_GUID']);
                    Movabls::add_item_to_index($index,'interface',$row['interface_GUID']);
                }
                $result->free();
            }

            //See if new interfaces came from the last iteration, and add their sub-elements
            $interfaces = array();
            foreach ($index['interfaces'] as $guid => $sub) {
                if ($sub === false)
                    $interfaces[] = $guid;
            }
            if (!empty($interfaces)) {
                $interfaces_string = "'".implode("','",$interfaces)."'";
                $result = $mvsdb->query("SELECT * FROM mvs_interfaces WHERE interface_GUID IN ($interfaces_string)");
                if ($result->num_rows == 0) {
                    foreach ($interfaces as $guid)
                        unset($index['interfaces'][$guid]);
                }
                while ($row = $result->fetch_assoc()) {
                    $row['content'] = json_decode($row['content'],true);
                    $index['interfaces'][$row['interface_GUID']] = $row;
                    Movabls::add_tags_to_index($index,$row['content']);
                }
                $result->free();
            }

            //If there are new places or interfaces, loop through again
            if (array_search(false,$index['places'],true) !== false)
                $new = true;
            else if (array_search(false,$index['interfaces'],true) !== false)
                $new = true;

        }

        return $index;

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
     * Checks if an item is in the index and adds it if not
     * @param array $index
     * @param string $type
     * @param string $guid
     */
    private static function add_item_to_index(&$index,$type,$guid) {

        if (empty($type) || empty($guid))
            return;

        $type = Movabls::table_name($type);
        if (!isset($index[$type][$guid]))
            $index[$type][$guid] = false;

    }

    /**
     * Recurs through the tags and adds all movabls to the index array
     * @param array $index
     * @param array $tags
     */
    private static function add_tags_to_index(&$index,$tags) {
        
        if (!empty($tags)) {
            foreach ($tags as $value) {
                if (isset($value['movabl_type']))
                    Movabls::add_item_to_index($index,$value['movabl_type'],$value['movabl_GUID']);
                if (isset($value['tags']))
                    Movabls::add_tags_to_index($index,$value['tags']);
                elseif (isset($value['interface_GUID'])) {
                    Movabls::add_item_to_index($index,'interface',$value['interface_GUID']);
                }
            }
        }

    }

    /**
     * Gets all movabls that aren't part of any package or sub-element of a package
     * and adds them to the index
     * @param array $index
     * @param mysqli handle $mvsdb 
     */
    private static function add_uncategorized_to_index(&$index,$mvsdb = null) {

        if (empty($mvsdb))
            $mvsdb = Movabls::db_link();

        $categorized = Movabls::get_packages_content();

        $permissions = Movabls::join_permissions('media');
        $result = $mvsdb->query("SELECT media_GUID FROM mvs_media AS x $permissions");
        while($row = $result->fetch_assoc()) {
            if (!isset($categorized['media'][$row['media_GUID']]))
                Movabls::add_item_to_index($index,'media',$row['media_GUID']);
        }

        $permissions = Movabls::join_permissions('function');
        $result = $mvsdb->query("SELECT function_GUID FROM mvs_functions AS x $permissions");
        while($row = $result->fetch_assoc()) {
            if (!isset($categorized['functions'][$row['function_GUID']]))
                Movabls::add_item_to_index($index,'function',$row['function_GUID']);
        }

        $permissions = Movabls::join_permissions('interface');
        $result = $mvsdb->query("SELECT * FROM mvs_interfaces AS x $permissions");
        while($row = $result->fetch_assoc()) {
            if (!isset($categorized['interfaces'][$row['interface_GUID']])) {
                $row['content'] = json_decode($row['content'],true);
                $index['interfaces'][$row['interface_GUID']] = $row;
            }
        }

        $permissions = Movabls::join_permissions('place');
        $result = $mvsdb->query("SELECT * FROM mvs_places AS x $permissions");
        while($row = $result->fetch_assoc()) {
            if (!isset($categorized['places'][$row['place_GUID']])) {
                $row['inputs'] = json_decode($row['inputs']);
                $row['url'] = Movabls::construct_place_url($row['url'],$row['inputs']);
                $index['places'][$row['place_GUID']] = $row;
            }
        }

    }

    /**
     * Creates a string to append to a SQL query to join permissions
     * @param string $type
     * @param array $where = existing where array
     * @return string
     */
    private static function join_permissions($type,$where = array()) {
        
        $group = $join = '';

        if (!$GLOBALS->_USER['is_owner']) {

            $where = array();
            $groups = "'".implode("','",$GLOBALS->_USER['groups'])."'";
            
            if ($type == 'meta')
                $join = " INNER JOIN mvs_permissions AS p ON p.movabl_GUID = m.movabls_GUID AND p.movabl_type = m.movabls_type";
            else
                $join = " INNER JOIN mvs_permissions AS p ON p.movabl_GUID = x.{$type}_GUID";

            if ($type != 'meta')
                $where[] = "p.movabl_type = '$type'";
            $where[] = "p.permission_type = 'read'";
            $where[] = "p.group_GUID IN ($groups)";

            $group = " GROUP BY p.movabl_type,p.movabl_GUID";

        }

        if (!empty($where))
            $where = 'WHERE '.implode(' AND ',$where);
        else
            $where = '';

        return "$join $where $group";

    }

    /**
     * Gets a single movabl by type and GUID
     * @param string $movabl_type
     * @param array
     */
    public static function get_movabl($movabl_type, $movabl_guid) {

        $mvsdb = Movabls::db_link();

        if (!Movabls_Permissions::check_permission($movabl_type, $movabl_guid, 'read', $mvsdb))
            throw new Exception("You do not have permission to view this Movabl",403);

        $movabl_type = $mvsdb->real_escape_string($movabl_type);
        $movabl_guid = $mvsdb->real_escape_string($movabl_guid);

        $table = Movabls::table_name($movabl_type);
            
        $result = $mvsdb->query("SELECT x.* FROM `mvs_$table` AS x WHERE x.{$movabl_type}_GUID = '$movabl_guid'");

        if (empty($result))
            throw new Exception ("Movabl ($movabl_type: $movabl_guid) not found",500);

        $movabl = $result->fetch_assoc();
            
        $result->free();

        $meta = Movabls::get_meta($movabl_type,$movabl_guid,$mvsdb);
        $movabl['meta'] = isset($meta[$movabl_guid]) ? $meta[$movabl_guid] : array();

        $tagmeta = Movabls::get_tags_meta($movabl_type,$movabl_guid,$mvsdb);

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
    public static function get_meta($types,$guids = null,$mvsdb = null) {

        if (empty($mvsdb))
            $mvsdb = Movabls::db_link();

        $meta = array();

        $query = "SELECT m.* FROM `mvs_meta` AS m";

        //If it's a single item, check whether they have permission to view it
        if (!empty($types) && !is_array($types) && !empty($guids) && !is_array($guids)) {
            if (!Movabls_Permissions::check_permission($types, $guids, 'read', $mvsdb))
                throw new Exception("You do not have permission to view this Movabl",403);
        }        

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

        $query .= ' '.Movabls::join_permissions('meta',$where);

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

        $query = "SELECT m.* FROM `mvs_meta` AS m";

        //If it's a single item, check whether they have permission to view it
        if (!empty($types) && !is_array($types) && !empty($guids) && !is_array($guids)) {
            if (!Movabls_Permissions::check_permission($types, $guids, 'read', $mvsdb))
                throw new Exception("You do not have permission to view this Movabl",403);
        }

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

        $query .= ' '.Movabls::join_permissions('meta',$where);

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
     * @param mysqli handle $mvsdb
     * @return bool
     */
    public static function set_movabl($movabl_type,$data,$movabl_guid = null, $mvsdb = null) {

        if (empty($mvsdb))
            $mvsdb = Movabls::db_link();

        if (!Movabls_Permissions::check_permission($movabl_type, $movabl_guid, 'write', $mvsdb))
            throw new Exception("You do not have permission to edit this Movabl",500);

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
            //If it's new, we need to give it permissions that pertain to the site
            Movabls_Permissions::add_site_permissions($movabl_type,$movabl_guid,$mvsdb);
        }

        //If it has children, we need to clean up any permissions old children may have
        //inherited from this function or its parents
        if (in_array($movabl_type,array('place','interface','package')))
            Movabls_Permissions::reinforce_permissions($movabl_type,$movabl_guid,$mvsdb);

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

        if (!Movabls_Permissions::check_permission($movabl_type, $movabl_guid, 'write', $mvsdb))
            throw new Exception("You do not have permission to edit this Movabl",500);

        $old_meta = Movabls::get_meta($movabl_type,$movabl_guid);
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

        if (!Movabls_Permissions::check_permission($movabl_type, $movabl_guid, 'write', $mvsdb))
            throw new Exception("You do not have permission to edit this Movabl",500);

        $sanitized_guid = $mvsdb->real_escape_string($movabl_guid);
        $sanitized_type = $mvsdb->real_escape_string($movabl_type.'_tag');

        $old_tags_meta = Movabls::get_tags_meta($movabl_type,$movabl_guid,$mvsdb);
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

        if (!Movabls_Permissions::check_permission($movabl_type, $movabl_guid, 'write', $mvsdb))
            throw new Exception("You do not have permission to delete this Movabl",500);

        $table = Movabls::table_name($movabl_type);
        $sanitized_guid = $mvsdb->real_escape_string($movabl_guid);
        $sanitized_type = $mvsdb->real_escape_string($movabl_type);

        $result = $mvsdb->query("DELETE FROM `mvs_$table` WHERE {$sanitized_type}_GUID = '$sanitized_guid'");

        Movabls::set_meta(array(),$movabl_type,$movabl_guid,$mvsdb);
        Movabls::set_tags_meta(array(),$movabl_type,$movabl_guid,$mvsdb);
        Movabls::delete_references($sanitized_type,$sanitized_guid,$mvsdb);
        Movabls_Permissions::delete_permissions($sanitized_type,$sanitized_guid,$mvsdb);

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
        while ($row = $results->fetch_assoc()) {
            unset($row['package_id']);
            $row['contents'] = json_decode($row['contents'],true);
            foreach ($row['contents'] as $k => $content) {
                if ($content == array('movabl_type'=>$movabl_type,'movabl_GUID'=>$movabl_guid))
                    unset($row['contents'][$k]);
            }
            Movabls::set_movabl('package', $row, $row['package_GUID'], $mvsdb);
        }
        $results->free();

        //Places
        $results = $mvsdb->query("SELECT * FROM mvs_places
                                  WHERE {$movabl_type}_GUID LIKE '%$movabl_guid%'");
        while ($row = $results->fetch_assoc()) {
            unset($row['place_id'],$row[$movabl_type.'_GUID']);
            Movabls::set_movabl('place', $row, $row['place_GUID'], $mvsdb);
        }
        $results->free();

        //Interface
        $results = $mvsdb->query("SELECT * FROM mvs_interfaces
                                  WHERE content LIKE '%$movabl_guid%'");
        while ($row = $results->fetch_assoc()) {
            unset($row['interface_id']);
            $row['content'] = json_decode($row['content'],true);
            $row['content'] = Movabls::delete_from_interface($row['content'],$movabl_type,$movabl_guid,$mvsdb);
            Movabls::set_movabl('interface', $row, $row['interface_GUID'], $mvsdb);
        }
        $results->free();

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
                    $tree[$tagname]['tags'] = Movabls::delete_from_interface($tree[$tagname]['tags'],$movabl_type,$movabl_guid,$mvsdb);
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
                $data = array(
                    'url'           => $mvsdb->real_escape_string(urlencode($data['url'])),
                    'inputs'        => !empty($data['inputs']) ? $mvsdb->real_escape_string(json_encode($data['inputs'])) : '',
                    'https'         => $data['https'] ? '1' : '0',
                    'media_GUID'    => $mvsdb->real_escape_string($data['media_GUID']),
                );
                if (!empty($data['interface_GUID']))
                    $data['interface_GUID'] = $mvsdb->real_escape_string($data['interface_GUID']);
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
            throw new Exception ('Datastring Generator Only Works for Updates and Inserts',500);
        return $datastring;

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
        
        $mvsdb = new mysqli('localhost','root','h4ppyf4rmers','db_filet');
        if (mysqli_connect_errno()) {
            printf("Connect failed: %s\n", mysqli_connect_error());
            exit();
        }
        return $mvsdb;
        
    }

}
?>