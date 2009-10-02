<?php
/**
 * Movabls Permissions API
 * @author Travis Hardman
 */
class Movabls_Permissions {

    /**
     * Checks whether a user with the given group memberships has permission to access
     * the given movabl with the given permission type
     * @param string $movabl_type
     * @param string $movabl_guid
     * @param array $groups
     * @param string $permission_type
     * @param mysqli handle $mvsdb
     */
    public static function check_permission($movabl_type,$movabl_guid,$groups,$permission_type,$mvsdb = null) {

        if (empty($mvsdb))
            $mvsdb = Movabls_Permissions::db_link();

        $movabl_type = $mvsdb->real_escape_string($movabl_type);
        $movabl_guid = $mvsdb->real_escape_string($movabl_guid);
        foreach ($groups as $k => $group)
            $groups[$k] = $mvsdb->real_escape_string($group);
        $groups = "'".implode("','",$groups)."'";
        $permission_type = $mvsdb->real_escape_string($permission_type);

        $results = $mvsdb->query("SELECT permission_id FROM mvs_permissions
                                WHERE movabl_type = '$movabl_type'
                                AND movabl_guid = '$movabl_guid'
                                AND permission_type = '$permission_type'
                                AND group_guid IN ($groups)");

        if ($results->num_rows == 0)
            return false;
        else
            return true;

    }

    /**
     * Takes information on new permissions, constructs an array of new permissions,
     * diffs that array with the existing permissions in the database, and makes the
     * necessary changes to the db
     * @param string $movabl_type
     * @param string $movabl_guid
     * @param array $groups = array('guid'=>'fooguid','r'=>bool,'w'=>bool,'x'=>bool)
     * @param int $inheritance = permission_id to be used as inheritance
     * @param mysqli handle $mvsdb
     * @return true
     */
    public static function set_permission($movabl_type,$movabl_guid,$groups,$inheritance = null,$mvsdb = null) {

        if (empty($mvsdb))
            $mvsdb = Movabls_Permissions::db_link();
        if (!Movabls_Permissions::permissions_editor($GLOBALS->_USER['groups'],$mvsdb))
            throw new Exception('You do not have permission to edit permissions.');

        $data = Movabls_Permissions::escape_data($movabl_type,$movabl_guid,$groups,$mvsdb);
        foreach ($data['groups'] as $group) {
            if ($group['r'])
                $new_data[] = array('group_GUID'=>$group['guid'],'movabl_type'=>$data['movabl_type'],'movabl_GUID'=>$data['movabl_guid'],'permission_type'=>'read');
            if ($group['w'])
                $new_data[] = array('group_GUID'=>$group['guid'],'movabl_type'=>$data['movabl_type'],'movabl_GUID'=>$data['movabl_guid'],'permission_type'=>'write');
            if ($group['x'])
                $new_data[] = array('group_GUID'=>$group['guid'],'movabl_type'=>$data['movabl_type'],'movabl_GUID'=>$data['movabl_guid'],'permission_type'=>'execute');
            $groupstring[] = $group['guid'];
        }
        $groupstring = "'".implode("','",$groupstring)."'";

        //Get the relevant existing permissions and put them into an array and index for reference
        $results = $mvsdb->query("SELECT * FROM mvs_permissions
                                WHERE movabl_type = '{$data['movabl_type']}'
                                AND movabl_GUID = '{$data['movabl_guid']}'
                                AND group_GUID IN ($groupstring)");

        $old_data_index = array();
        while ($row = $results->fetch_assoc()) {
            $old_data_index[] = array(
                'group_GUID' => $row['group_GUID'],
                'movabl_type' => $data['movabl_type'],
                'movabl_GUID' => $row['movabl_GUID'],
                'permission_type' => $row['permission_type']
            );
            $row['permission_id'] = (int)$row['permission_id'];
            $row['inheritance'] = json_decode($row['inheritance']);
            $old_data[] = $row;
        }

        foreach ($new_data as $data) {
            $key = array_search($data,$old_data_index);
            //If there's not a row for this permission yet
            if ($key === false) {
                if (empty($inheritance))
                    $inheritance_string = '';
                else
                    $inheritance_string = json_encode(array($inheritance));
                $mvsdb->query("INSERT INTO mvs_permissions
                               (group_GUID,movabl_type,movabl_GUID,permission_type,inheritance)
                               VALUES ('{$data['group_GUID']}','{$data['movabl_type']}','{$data['movabl_GUID']}','{$data['permission_type']}','$inheritance_string')");
                if (empty($inheritance))
                    $mvsdb->query("UPDATE mvs_permissions SET inheritance = '[$mvsdb->insert_id]' WHERE permission_id = $mvsdb->insert_id");
            }
            else { //the row exists
                //determine what permission_id to use as the new inheritance
                if (empty($inheritance))
                    $new = $old_data[$key]['permission_id'];
                else
                    $new = $inheritance;
                //If the new inheritance is not already set
                if (!in_array($new,$old_data[$key]['inheritance'])) {
                    $old_data[$key]['inheritance'][] = $new;
                    $old_data[$key]['inheritance'] = json_encode($old_data[$key]['inheritance']);
                    $mvsdb->query("UPDATE mvs_permissions SET inheritance = '{$old_data[$key]['inheritance']}' WHERE permission_id = {$old_data[$key]['permission_id']}");
                }
                unset($old_data[$key],$old_data_index[$key]);
            }             
        }

        //old_data that were not included in new_data should be removed
        if (!empty($old_data)) {
            foreach ($old_data as $data) {
                //If this was the only inheritance, delete the row
                if ($data['inheritance'] == array($data['permission_id']))
                    $mvsdb->query("DELETE FROM mvs_permissions WHERE permission_id = {$data['permission_id']}");
                //Otherwise just remove the inheritance
                else {
                    foreach ($data['inheritance'] as $k => $id) {
                        if ($id == $data['permission_id']) {
                            unset($data['inheritance'][$k]);
                            break;
                        }
                    }
                    $data['inheritance'] = json_encode(array_values($data['inheritance']));
                    $mvsdb->query("UPDATE mvs_permissions SET inheritance = '{$data['inheritance']}' WHERE permission_id = {$data['permission_id']}");
                }
            }
        }
    }

    /**
     * Escapes data passed to a set function for use in a SQL query
     * @param string $movabl_type
     * @param string $movabl_guid
     * @param array $groups
     * @param mysqli handle $mvsdb
     * @return array 
     */
    private static function escape_data($movabl_type,$movabl_guid,$groups,$mvsdb = null) {

        if (empty($mvsdb))
            $mvsdb = Movabls_Permissions::db_link();

        $data['movabl_type'] = $mvsdb->real_escape_string($movabl_type);
        $data['movabl_guid'] = $mvsdb->real_escape_string($movabl_guid);
        foreach ($groups as $k => $group) {
            $data['groups'][$k]['guid'] = $mvsdb->real_escape_string($group['guid']);
            $data['groups'][$k]['r'] = $group['r'] ? true : false;
            $data['groups'][$k]['w'] = $group['w'] ? true : false;
            $data['groups'][$k]['x'] = $group['x'] ? true : false;
        }

        return $data;

    }

    /**
     * Determines whether the current user has permission to edit permissions
     * @param array $groups
     * @param mysqli handle $mvsdb
     * @return bool
     */
    public static function permissions_editor($groups,$mvsdb = null) {

        if (empty($mvsdb))
            $mvsdb = Movabls_Permissions::db_link();
        
        $groups = "'".implode("','",$groups)."'";
        $results = $mvsdb->query("SELECT group_id FROM mvs_groups
                                    WHERE group_GUID IN ($groups)
                                    AND permissions_editor = 1");
        if ($results->num_rows == 0)
            return false;
        else
            return true;

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
