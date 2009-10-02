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
     * Replaces existing non-inherited permissions for a media item with new permissions
     * @param string $movabl_guid
     * @param array $groups
     * @param bool $r
     * @param bool $w
     * @param bool $x
     * @param mysqli handle $mvsdb
     * @return true
     */
    public static function set_media_permission($movabl_guid,$groups,$r,$w,$x,$mvsdb = null) {

        if (empty($mvsdb))
            $mvsdb = Movabls_Permissions::db_link();
        if (!Movabls_Permissions::permissions_editor($GLOBALS->_USER['groups'],$mvsdb))
            throw new Exception('You do not have permission to edit permissions.');

        $data = Movabls_Permissions::escape_data($movabl_guid,$groups,$mvsdb);
        foreach ($data['groups'] as $group) {
            if ($r)
                $new_data[] = array('group_GUID'=>$group,'movabl_type'=>'media','movabl_GUID'=>$data['movabl_guid'],'permission_type'=>'read');
            if ($w)
                $new_data[] = array('group_GUID'=>$group,'movabl_type'=>'media','movabl_GUID'=>$data['movabl_guid'],'permission_type'=>'write');
            if ($x)
                $new_data[] = array('group_GUID'=>$group,'movabl_type'=>'media','movabl_GUID'=>$data['movabl_guid'],'permission_type'=>'execute');
        }
        $groupstring = "'".implode("','",$data['groups'])."'";
        $results = $mvsdb->query("SELECT * FROM mvs_permissions
                                WHERE movabl_type = 'media'
                                AND movabl_GUID = '{$data['movabl_guid']}'
                                AND group_GUID IN ($groupstring)
                                HAVING inheritance LIKE CONCAT('%\"',permission_id,'\"%')");

        while ($row = $results->fetch_assoc())
            $old_data[$row['group_GUID']][$row['movabl_GUID']][$row['permission_type']] = $row;

        foreach ($new_data as $data) {
            if (!isset($old_data[$data['group_GUID']][$data['movabl_GUID']][$data['permission_type']]))
                die();//insert
            else
                unset($old_data[$data['group_GUID']][$data['movabl_GUID']][$data['permission_type']]);     
        }

        foreach ($old_data as $data) {
            if ($data['inheritance'] == '["'.$data['permission_id'].'"]')
                die();//delete
            else
                die();//update inheritance
        }
    }

    /**
     * Escapes data passed to a set function for use in a SQL query
     * @param string $movabl_guid
     * @param array $groups
     * @return array 
     */
    private static function escape_data($movabl_guid,$groups,$mvsdb = null) {

        if (empty($mvsdb))
            $mvsdb = Movabls_Permissions::db_link();

        $data['movabl_guid'] = $mvsdb->real_escape_string($movabl_guid);
        foreach ($groups as $group)
            $data['groups'][] = $mvsdb->real_escape_string($group);

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
