<?php
/**
 * Movabls Permissions API
 * @author Travis Hardman
 */
class Movabls_Permissions {

    /**
     * Checks whether the current user has permission to access the given movabl with the given permission type
     * @param string $movabl_type
     * @param string $movabl_guid
     * @param array $user
     * @param string $permission_type
     * @param mysqli handle $mvsdb
     */
    public static function check_permission($movabl_type,$movabl_guid,$permission_type,$mvsdb = null) {

        if (in_array(1,$GLOBALS->_USER['groups']))
            return true;

        if (empty($GLOBALS->_USER['groups']))
            return false;

        if (empty($mvsdb))
            $mvsdb = self::db_link();

        $movabl_type = $mvsdb->real_escape_string($movabl_type);
        $movabl_guid = $mvsdb->real_escape_string($movabl_guid);
        foreach ($GLOBALS->_USER['groups'] as $k => $group)
            $groups[$k] = $mvsdb->real_escape_string($group);
        $groups = "'".implode("','",$groups)."'";
        $permission_type = $mvsdb->real_escape_string($permission_type);

        $guid = empty($movabl_guid) ? "IS NULL" : "= '".$movabl_guid."'";
        $results = $mvsdb->query("SELECT permission_id FROM mvs_permissions
                                WHERE movabl_type = '$movabl_type'
                                AND movabl_guid $guid
                                AND permission_type = '$permission_type'
                                AND group_id IN ($groups)");

        if ($results->num_rows == 0)
            return false;
        else
            return true;

    }

    /**
     * Public wrapper function for set_permission that only allows setting parents, not inheritances,
     * ensuring that all children are set correctly by the toplevel of set_children
     * @param string $movabl_type
     * @param string $movabl_guid
     * @param array $groups = array('id'=>1,'read'=>bool,'write'=>bool,'execute'=>bool)
     * @param mysqli handle $mvsdb
     */
    public static function set_movabl_permissions($movabl_type,$movabl_guid,$groups,$mvsdb = null) {

        if (!in_array(1,$GLOBALS->_USER['groups']))
            throw new Exception('Only administrators can edit permissions.',500);
            
        self::set_permission($movabl_type, $movabl_guid, $groups, $mvsdb);

    }

    /**
     * Takes information on new permissions, constructs an array of new permissions,
     * diffs that array with the existing permissions in the database, and makes the
     * necessary changes to the db
     * @param string $movabl_type (or 'site')
     * @param string $movabl_guid
     * @param array $groups = array('id'=>1,'read'=>bool,'write'=>bool,'execute'=>bool)
     * @param string $inheritance_type
     * @param string $inheritance_GUID
     * @param mysqli handle $mvsdb
     * @return array of new children given this inheritance in this iteration
     */
    private static function set_permission($movabl_type,$movabl_guid,$groups,$inheritance_type = null,$inheritance_GUID = null,$mvsdb = null) {

        if (empty($mvsdb))
            $mvsdb = self::db_link();

        $escaped_data = self::escape_data($movabl_type,$movabl_guid,$groups,$inheritance_type,$inheritance_GUID,$mvsdb);

        //Set the permissions of all the children of this movabl
        if (empty($inheritance_type)) {

            //Get all the movabls that previously inherited permissions
            $guid = empty($escaped_data['movabl_GUID']) ? "IS NULL" : "= '".$escaped_data['movabl_GUID']."'";
            $results = $mvsdb->query("SELECT movabl_type,movabl_GUID FROM mvs_permissions WHERE inheritance_type = '{$escaped_data['movabl_type']}' AND inheritance_GUID $guid");
            while ($row = $results->fetch_assoc())
                $old_children[] = $row;
            $results->free();
            //Get and set movabls that now inherit permissions
            $new_children = self::set_children($escaped_data,true,$mvsdb);
            
            //Remove permissions for old_children that are not in the new_children array
            if (!empty($old_children)) {
                foreach ($old_children as $old_child) {
                    if (array_search($old_child,$new_children) === false) {
                        $mvsdb->query("DELETE FROM mvs_permissions
                                       WHERE movabl_type = '{$old_child['movabl_type']}'
                                       AND movabl_GUID = '{$old_child['movabl_GUID']}'
                                       AND inheritance_type = '{$escaped_data['movabl_type']}'
                                       AND inheritance_GUID $guid");
                    }
                }
            }

        }
        else
            $new_children = self::set_children($escaped_data,false,$mvsdb);
        
        //Prepare the new data array to set this movabl
        $groupstring = array();
        foreach ($escaped_data['groups'] as $group) {
            $template = array(
                'group_id' => $group['id'],
                'movabl_type' => $escaped_data['movabl_type'],
                'movabl_GUID' => $escaped_data['movabl_GUID'],
                'permission_type' => null,
                'inheritance_type' => $escaped_data['inheritance_type'],
                'inheritance_GUID' => $escaped_data['inheritance_GUID']
            );
            if ($group['read']) {
                $template['permission_type'] = 'read';
                $new_data[] = $template;
            }
            if ($group['write']) {
                $template['permission_type'] = 'write';
                $new_data[] = $template;
            }
            if ($group['execute']) {
                $template['permission_type'] = 'execute';
                $new_data[] = $template;
            }
            $groupstring[] = $group['id'];
        }
        $groupstring = "'".implode("','",$groupstring)."'";

        if ($escaped_data['movabl_type'] !== 'site')
            $guidstring = "= '".$escaped_data['movabl_GUID']."'";
        else
            $guidstring = "IS NULL";

        //Get the relevant existing permissions and put them into the old data array
        $results = $mvsdb->query("SELECT * FROM mvs_permissions
                                WHERE movabl_type = '{$escaped_data['movabl_type']}'
                                AND movabl_GUID $guidstring
                                AND group_id IN ($groupstring)
                                AND inheritance_type ".(empty($escaped_data['inheritance_type']) ? 'IS NULL' : "= '{$escaped_data['inheritance_type']}'")."
                                AND inheritance_GUID ".(empty($escaped_data['inheritance_GUID']) ? 'IS NULL' : "= '{$escaped_data['inheritance_GUID']}'"));

        $old_data_index = array();
        while ($row = $results->fetch_assoc()) {
            $row['permission_id'] = (int)$row['permission_id'];
            $row['inheritance_type'] = empty($row['inheritance_type']) ? null : $row['inheritance_type'];
            $row['inheritance_GUID'] = empty($row['inheritance_GUID']) ? null : $row['inheritance_GUID'];
            $old_data[] = $row;
            $old_data_index[] = array(
                'group_id' => $row['group_id'],
                'movabl_type' => $escaped_data['movabl_type'],
                'movabl_GUID' => $row['movabl_GUID'],
                'permission_type' => $row['permission_type'],
                'inheritance_type' => $row['inheritance_type'],
                'inheritance_GUID' => $row['inheritance_GUID']
            );
        }
        $results->free();

        //Add new data if the rows don't already exist
        if (!empty($new_data)) {
            foreach ($new_data as $data) {
                $key = array_search($data,$old_data_index);
                if ($key === false) {
                    $data['inheritance_type'] = $data['inheritance_type'] !== null ? "'".$data['inheritance_type']."'" : "NULL";
                    $data['inheritance_GUID'] = $data['inheritance_GUID'] !== null ? "'".$data['inheritance_GUID']."'" : "NULL";
                    $data['movabl_GUID'] = $data['movabl_GUID'] !== null ? "'".$data['movabl_GUID']."'" : "NULL";
                    $mvsdb->query("INSERT INTO mvs_permissions
                                   (group_id,movabl_type,movabl_GUID,permission_type,inheritance_type,inheritance_GUID)
                                   VALUES ('{$data['group_id']}','{$data['movabl_type']}',{$data['movabl_GUID']},'{$data['permission_type']}',{$data['inheritance_type']},{$data['inheritance_GUID']})");
                }
                else
                    unset($old_data[$key],$old_data_index[$key]);
            }
        }

        //old_data that were not included in new_data should be removed
        if (!empty($old_data)) {
            foreach ($old_data as $data)
                $mvsdb->query("DELETE FROM mvs_permissions WHERE permission_id = {$data['permission_id']}");
        }

        return $new_children;
    }

    /**
     * Gets all of the children of the current movabl and sets their permissions, then
     * returns a list of them and their children
     * @param array $escaped_data
     * @param bool $toplevel
     * @param mysqli handle $mvsdb
     * @return children set in this level of the tree and below
     */
    private static function set_children($escaped_data,$toplevel,$mvsdb = null) {

        if (empty($mvsdb))
            $mvsdb = self::db_link();

        $new_children = array();

        switch ($escaped_data['movabl_type']) {
            case 'site':
                $results = $mvsdb->query("SELECT media_GUID FROM mvs_media");
                while ($row = $results->fetch_assoc())
                    $extras[] = array('movabl_type'=>'media','movabl_GUID'=>$row['media_GUID']);
                $results->free();
                $results = $mvsdb->query("SELECT function_GUID FROM mvs_functions");
                while ($row = $results->fetch_assoc())
                    $extras[] = array('movabl_type'=>'function','movabl_GUID'=>$row['function_GUID']);
                $results->free();
                $results = $mvsdb->query("SELECT interface_GUID FROM mvs_interfaces");
                while ($row = $results->fetch_assoc())
                    $extras[] = array('movabl_type'=>'interface','movabl_GUID'=>$row['interface_GUID']);
                $results->free();
                $results = $mvsdb->query("SELECT place_GUID FROM mvs_places");
                while ($row = $results->fetch_assoc())
                    $extras[] = array('movabl_type'=>'place','movabl_GUID'=>$row['place_GUID']);
                $results->free();
                $results = $mvsdb->query("SELECT package_GUID FROM mvs_packages");
                while ($row = $results->fetch_assoc())
                    $extras[] = array('movabl_type'=>'package','movabl_GUID'=>$row['package_GUID']);
                $results->free();
                break;
            case 'place':
                $results = $mvsdb->query("SELECT media_GUID,interface_GUID FROM mvs_places WHERE place_GUID = '{$escaped_data['movabl_GUID']}'");
                $row = $results->fetch_assoc();
                $extras = Movabls::get_submovabls('place',$row);
                $results->free();
                break;
            case 'interface':
                $results = $mvsdb->query("SELECT content FROM mvs_interfaces WHERE interface_GUID = '{$escaped_data['movabl_GUID']}'");
                $row = $results->fetch_assoc();
                $row['content'] = json_decode($row['content'],true);
                $extras = Movabls::get_submovabls('interface',$row);
                $results->free();
                break;
            case 'package':
                $results = $mvsdb->query("SELECT contents FROM mvs_packages WHERE package_GUID = '{$escaped_data['movabl_GUID']}'");
                $row = $results->fetch_assoc();
                $row['contents'] = json_decode($row['contents'],true);
                $extras = Movabls::get_submovabls('package',$row);
                $results->free();
                break;
        }
        //TODO: This is actually setting permission for a movabl every time it's used, instead of just the one time
        //It would be more efficient to get a complete list of new children, then set them one at a time
        if (!empty($extras)) {
            $new_children = $extras;
            if ($toplevel) {
                foreach ($extras as $extra) {
                    $new = self::set_permission($extra['movabl_type'], $extra['movabl_GUID'], $escaped_data['groups'], $escaped_data['movabl_type'], $escaped_data['movabl_GUID'], $mvsdb);
                    $new_children = array_merge($new_children,$new);
                }
            }
            else {
                foreach ($extras as $extra) {
                    $new = self::set_permission($extra['movabl_type'], $extra['movabl_GUID'], $escaped_data['groups'], $escaped_data['inheritance_type'], $escaped_data['inheritance_GUID'], $mvsdb);
                    $new_children = array_merge($new_children,$new);
                }
            }
        }
        foreach ($new_children as $k => $v)
            $new_children[$k] = serialize($v);
        $new_children = array_values(array_unique($new_children));
        foreach ($new_children as $k => $v)
            $new_children[$k] = unserialize($v);
        return $new_children;
    }

    /**
     * Adds existing site-level permissions to a new movabl
     * @param string $movabl_type
     * @param string $movabl_guid
     */
    public static function add_site_permissions($movabl_type,$movabl_guid,$mvsdb = null) {

        if (empty($mvsdb))
            $mvsdb = self::db_link();

        $movabl_type = $mvsdb->real_escape_string($movabl_type);
        $movabl_guid = $mvsdb->real_escape_string($movabl_guid);

        $current = self::get_permissions('site',null,null,$mvsdb);

        foreach ($current as $group_id => $permissions) {
            $groups[] = array(
                'id' => $group_id,
                'read' => $permissions['read'] !== false,
                'write' => $permissions['write'] !== false,
                'execute' => $permissions['execute'] !== false
            );
        }
        self::set_permission($movabl_type, $movabl_guid, $groups, 'site', null, $mvsdb);

    }

    /**
     * Gets all movabls that are parents of the specified movabl, gets their current permissions,
     * and re-sets their current permissions, as well as the permissions of the current movabl,
     * thereby adding any new children and removing any old children that may no longer be present
     * @param string $movabl_type
     * @param string $movabl_guid
     * @param mysqli handle $mvsdb
     */
    public static function reinforce_permissions($movabl_type,$movabl_guid,$mvsdb = null) {

        if (empty($mvsdb))
            $mvsdb = self::db_link();

        //Reset this movabl with its current permissions
        $results = $mvsdb->query("SELECT * FROM mvs_permissions
                                  WHERE movabl_type = '$movabl_type'
                                  AND movabl_guid = '$movabl_guid'
                                  AND inheritance_type IS NULL");

        while ($row = $results->fetch_assoc()) {
            if (!isset($current[$row['group_id']])) {
                $current[$row['group_id']] = array(
                    'id' => $row['group_id'],
                    'read' => false,
                    'write' => false,
                    'execute' => false
                );
            }
            $current[$row['group_id']][$row['permission_type']] = true;
        }
        if (!empty($current))
            $current = array_values($current);
        else
            $current = array();

        $results->free();
        self::set_permission($movabl_type,$movabl_guid,$current,null,null,$mvsdb);

        //Now set all of the parents
        $movabl_type = $mvsdb->real_escape_string($movabl_type);
        $movabl_guid = $mvsdb->real_escape_string($movabl_guid);
        $results = $mvsdb->query("SELECT * FROM mvs_permissions
                                  WHERE movabl_type = '$movabl_type'
                                  AND movabl_GUID = '$movabl_guid'
                                  AND inheritance_type IS NOT NULL");

        while ($row = $results->fetch_assoc()) {
            $key = serialize(array($row['inheritance_type'],$row['inheritance_GUID']));
            if (!isset($parents[$key][$row['group_id']])) {
                $parents[$key][$row['group_id']] = array(
                    'id' => $row['group_id'],
                    'read' => false,
                    'write' => false,
                    'execute' => false
                );
            }
            $parents[$key][$row['group_id']][$row['permission_type']] = true;
        }

        $results->free();

        if (!empty($parents)) {
            foreach ($parents as $key => $groups) {
                $key = unserialize($key);
                self::set_permission($key[0],$key[1],array_values($groups),null,null,$mvsdb);
            }
        }

    }

    /**
     * Gets permissions for all groups for a single movabl
     * @param string $movabl_type
     * @param string $movabl_guid
     * @param array $groups
     * @param mysqli handle $mvsdb
     * @return permissions array
     */
    public static function get_permissions($movabl_type,$movabl_guid,$groups = null,$mvsdb = null) {

        if (empty($mvsdb))
            $mvsdb = self::db_link();

        if (empty($groups)) {
            $allgroups = self::get_groups();
            $groups = array();
            foreach ($allgroups as $group)
                $groups[] = $group['group_id'];
        }

        foreach ($groups as $k => $v) {
            $groups[$k] = $mvsdb->real_escape_string($v);
            $return[$v] = array('read'=>false,'write'=>false,'execute'=>false);
        }
        $movabl_type = $mvsdb->real_escape_string($movabl_type);
        if (empty($movabl_guid))
            $guid = 'IS NULL';
        else
            $guid = "= '".$mvsdb->real_escape_string($movabl_guid)."'";

        $results = $mvsdb->query("SELECT * FROM mvs_permissions
                                  WHERE movabl_type = '$movabl_type'
                                  AND movabl_GUID $guid
                                  AND group_id IN ('".implode("','",$groups)."')");
        
        while ($row = $results->fetch_assoc()) {
            $array = array(
                'inheritance_type' => $row['inheritance_type'],
                'inheritance_GUID' => $row['inheritance_GUID']
            );
            $return[$row['group_id']][$row['permission_type']][] = $array;
        }
        $results->free();

        return $return;

    }

    /**
     * Gets all groups present on this site
     * @param mysqli handle $mvsdb
     * @return groups array
     */
    public static function get_groups($mvsdb = null) {

        if (empty($mvsdb))
            $mvsdb = self::db_link();

        $results = $mvsdb->query("SELECT * FROM `{$GLOBALS->_SERVER['DATABASE']}`.mvs_groups");
        while($row = $results->fetch_assoc())
            $return[] = $row;
        $results->free();

        return $return;

    }

    /**
     * Deletes all permissions tied to a specific movabl, including permissions inherited from that movabl
     * @param <type> $movabl_type
     * @param <type> $movabl_guid
     * @param <type> $mvsdb
     */
    public static function delete_permissions($movabl_type,$movabl_guid,$mvsdb = null) {

        self::reinforce_permissions($movabl_type,$movabl_guid,$mvsdb);

        if (empty($mvsdb))
            $mvsdb = self::db_link();

        $movabl_type = $mvsdb->real_escape_string($movabl_type);
        $movabl_guid = $mvsdb->real_escape_string($movabl_guid);
        $mvsdb->query("DELETE FROM mvs_permissions
                       WHERE (movabl_type = '$movabl_type' AND movabl_GUID = '$movabl_guid')
                       OR (inheritance_type = '$movabl_type' AND inheritance_GUID = '$movabl_guid')");

    }

    /**
     * Escapes data passed to a set function for use in a SQL query
     * @param string $movabl_type
     * @param string $movabl_guid
     * @param array $groups
     * @param string $inheritance_type
     * @param string $inheritance_GUID
     * @param mysqli handle $mvsdb
     * @return array 
     */
    private static function escape_data($movabl_type,$movabl_guid,$groups,$inheritance_type,$inheritance_GUID,$mvsdb = null) {

        if (empty($mvsdb))
            $mvsdb = self::db_link();

        $data['movabl_type'] = $mvsdb->real_escape_string($movabl_type);
        $data['movabl_GUID'] = !empty($movabl_guid) ? $mvsdb->real_escape_string($movabl_guid) : null;
        $data['inheritance_type'] = !empty($inheritance_type) ? $mvsdb->real_escape_string($inheritance_type) : null;
        $data['inheritance_GUID'] = !empty($inheritance_GUID) ? $mvsdb->real_escape_string($inheritance_GUID) : null;

        if (!empty($groups)) {
            foreach ($groups as $k => $group) {
                $data['groups'][$k]['id'] = $mvsdb->real_escape_string($group['id']);
                $data['groups'][$k]['read'] = $group['read'] ? true : false;
                $data['groups'][$k]['write'] = $group['write'] ? true : false;
                $data['groups'][$k]['execute'] = $group['execute'] ? true : false;
            }
        }
        else
            $data['groups'] = array();

        return $data;

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