<?php
/**
 * Static functions to provide CRUD functionality to the user
 * @author Travis Hardman
 */
class Movabls {

    public static function get($type,$id) {
	$conn = new Mongo();
        $db = $conn->selectDB("movabls");
	if ($type != 'media')
	    $type = substr($type,0,strlen($type)-1);
	return $db->getDBRef(array('$ref'=>$type,'$id'=>new MongoID($id)));
    }

    public static function getAll($type) {
	$conn = new Mongo();
        $db = $conn->selectDB("movabls");
	if ($type != 'media')
	    $type = substr($type,0,strlen($type)-1);
	$results = $db->selectCollection($type)->find();
	$return = array();
	foreach ($results as $id => $result) {
	    $return[$id] = $result;
	    unset($return[$id]['_id']);
	    $return[$id]['id'] = $id;
	}
	return $return;
    }

}
?>
