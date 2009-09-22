<?php
/**
 * Movabls API
 * @author Travis Donia
 */
class Movabls {

    public static function Get_Apps() {

        $link = mysqli_connect('localhost','root','h4ppyf4rmers','db_api');
        if (mysqli_connect_errno()) {
            printf("Connect failed: %s\n", mysqli_connect_error());
            exit();
        }

        $query = "SELECT DISTINCT app_GUID FROM `mvs_places` WHERE NOT app_GUID = '' ";
        $result = mysqli_query($link, $query);

        if ($result = mysqli_query($link, $query)) {
            while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC) )
                $app_id_array[] = $row["app_GUID"];
            mysqli_free_result($result);
        }

        $app_string = "";

        foreach ($app_id_array as $app_GUID)
            $app_string .= (strlen($app_string)<2) ? "movabl_GUID = '$app_GUID'" : "OR movabl_GUID = '$app_GUID'";

        $query = "SELECT * FROM `mvs_meta` WHERE $app_string ; ";
        $result = mysqli_query($link, $query);

        if ($result = mysqli_query($link, $query)) {
            while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC) )
                $app_meta[$row["movabl_GUID"]][$row["name"]] = $row["content"];
            mysqli_free_result($result);
        }

        return $app_meta;

    }

    public static function Get_Places() {
	
        $link = mysqli_connect('localhost','root','h4ppyf4rmers','db_api');
        if (mysqli_connect_errno()) {
            printf("Connect failed: %sn", mysqli_connect_error());
            exit();
        }

        $query = "SELECT * FROM `mvs_places` ORDER BY url ASC ";
        $appset = Array();
        $apps_string = "";
        $result = mysqli_query($link, $query);
        if ($result = mysqli_query($link, $query)) {
            while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC) ) {
                $place_array[$row["place_GUID"]] = $row;
                if(!in_array($row["app_GUID"], $appset)) {
                    if ($row["app_GUID"] !=  "") {
                        $appset[] = $row["app_GUID"];
                        $apps_string .= (strlen($apps_string)<2) ? "( movabl_GUID = '{$row["app_GUID"]}' AND name = 'label' AND movabl_type = 'app' )" : " OR ( movabl_GUID = '{$row["app_GUID"]}' AND name = 'label' AND movabl_type = 'app' )";
                    }
                }
            }
            mysqli_free_result($result);
        }

        $place_string = "";

        foreach ($place_array as $place_GUID => $place)
            $place_string .= (strlen($place_string)<2) ? " (movabl_GUID = '$place_GUID' AND movabl_type = 'place') " : "OR (movabl_GUID = '$place_GUID' AND movabl_type = 'place') ";

        $query = "SELECT * FROM `mvs_meta` WHERE $place_string";
        $result = mysqli_query($link, $query);
        if ($result = mysqli_query($link, $query)) {
            while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC) )
                $place_array[$row["movabl_GUID"]][$row["name"]] = $row["content"];
            mysqli_free_result($result);
        }

        $app_array = Array();
        $query = "SELECT * FROM `mvs_meta` WHERE $apps_string";
        $result = mysqli_query($link, $query);
        if ($result = mysqli_query($link, $query)) {
            while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC) ) {
                $app_array[$row["movabl_GUID"]][$row["name"]] = $row["content"];
            }
            mysqli_free_result($result);
        }

        foreach ($place_array as $place){
            if (array_key_exists($place["app_GUID"], $app_array) )
                $place_array[$place["place_GUID"]]["app_name"] = $app_array[$place["app_GUID"]]["label"];
        }

        return $place_array;
	
    }

    public static function Get_Movabls($filter) {
	
        $link = mysqli_connect('localhost','root','h4ppyf4rmers','db_api');
        if (mysqli_connect_errno()) {
            printf("Connect failed: %s\n", mysqli_connect_error());
            exit();
        }

        $filter_query = Array();
        $filter_query[""] = "movabl_type = 'media' OR movabl_type = 'function' OR movabl_type = 'interface'";
        $filter_query["media"] = "movabl_type = 'media'";
        $filter_query["function"] = "movabl_type = 'function'";
        $filter_query["interface"] = "movabl_type = 'interface'";
        $query = "SELECT * FROM `mvs_meta` WHERE ({$filter_query[$filter]}) AND (name = 'app' OR name = 'label' OR name = 'icon') ORDER BY content ASC; ";
        $result = mysqli_query($link, $query);

        if ($result = mysqli_query($link, $query)) {
            while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
                if (!isset($movabl_array[$row["movabl_type"]][$row["movabl_GUID"]][$row["name"]]))
                    $movabl_array[$row["movabl_type"]][$row["movabl_GUID"]][$row["name"]] = $row["content"];
                else {
                    $foo = $movabl_array[$row["movabl_type"]][$row["movabl_GUID"]][$row["name"]];
                    unset($movabl_array[$row["movabl_type"]][$row["movabl_GUID"]][$row["name"]]);
                    $movabl_array[$row["movabl_type"]][$row["movabl_GUID"]][$row["name"]][] = $foo;
                    $movabl_array[$row["movabl_type"]][$row["movabl_GUID"]][$row["name"]][] = $row["content"];
                }
                mysqli_free_result($result);
            }
        }

        return $movabl_array;

    }

    public static function Get_Place() {

        $place_id = substr($GLOBALS->GLOBALS["HTTP_SERVER_VARS"]["REQUEST_URI"],11);
        if ($place_id == "")
            $place_id = "api";

        $link = mysqli_connect('localhost','root','h4ppyf4rmers','db_api');

        if (mysqli_connect_errno()) {
            printf("Connect failed: %sn", mysqli_connect_error());
            exit();
        }

        $query = "SELECT * FROM `mvs_places` WHERE place_GUID = '$place_id' ORDER BY url ASC";
        $result = mysqli_query($link, $query);

        if ($result = mysqli_query($link, $query)) {
            while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC))
                $movabl_array = $row;
        }
        mysqli_free_result($result);

        $query = "SELECT * FROM `mvs_meta` WHERE (movabl_GUID = '$place_id' AND movabl_type = 'place')";
        $result = mysqli_query($link, $query);

        if ($result = mysqli_query($link, $query)) {
            while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC))
                $movabl_array[$row["name"]] = $row["content"];
            mysqli_free_result($result);
        }

        return $movabl_array;
	
    }

    public static function Get_Media() {
	
        $media_id = substr($GLOBALS->GLOBALS["HTTP_SERVER_VARS"]["REQUEST_URI"],11);
        if ($media_id == "")
            $media_id = "percentmedia";

        $link = mysqli_connect('localhost','root','h4ppyf4rmers','db_api');
        if (mysqli_connect_errno()) {
            printf("Connect failed: %s\n", mysqli_connect_error());
            exit();
        }

        $query = "SELECT * FROM `mvs_media` WHERE media_GUID = '$media_id'";
        $result = mysqli_query($link, $query);

        if ($result = mysqli_query($link, $query)) {
            while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
                $row["content"] = htmlspecialchars($row["content"]);
                $row["content"] = stripslashes($row["content"]);
                $movabl_array = $row;
            }
        }
        mysqli_free_result($result);

        $movabl_string = "";
        $query = "SELECT * FROM `mvs_meta` WHERE (movabl_GUID = '$media_id' AND movabl_type = 'media')";
        $result = mysqli_query($link, $query);

        if ($result = mysqli_query($link, $query)) {
            while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC))
                $movabl_array[$row["name"]] = $row["content"];
            mysqli_free_result($result);
        }

        return $movabl_array;
	
    }

    public static function Get_Function() {
	
	$function_id = substr($GLOBALS->GLOBALS["HTTP_SERVER_VARS"]["REQUEST_URI"],14);
        if ($function_id == "")
            $function_id = "Get_Media";

        $link = mysqli_connect('localhost','root','h4ppyf4rmers','db_api');
        if (mysqli_connect_errno()) {
            printf("Connect failed: %s\n", mysqli_connect_error());
            exit();
        }

        $query = "SELECT * FROM `mvs_functions` WHERE function_GUID = '$function_id'";
        $result = mysqli_query($link, $query);

        if ($result = mysqli_query($link, $query)) {
            while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
                $row["content"] = htmlspecialchars($row["content"]);
                $row["content"] = stripslashes($row["content"]);
                $movabl_array = $row;
            }
        }
        mysqli_free_result($result);

        $movabl_string = "";
        $query = "SELECT * FROM `mvs_meta` WHERE (movabl_GUID = '$function_id' AND movabl_type = 'function')";
        $result = mysqli_query($link, $query);

        if ($result = mysqli_query($link, $query)) {
            while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC))
                $movabl_array[$row["name"]] = $row["content"];
            mysqli_free_result($result);
        }

        return $movabl_array;
	
    }

    public static function Get_Interface() {
	
	$interface_id = substr($GLOBALS->GLOBALS["HTTP_SERVER_VARS"]["REQUEST_URI"],15);
        if ($interface_id == "")
            $interface_id = "TEST_INT";

        $link = mysqli_connect('localhost','root','h4ppyf4rmers','db_api');
        if (mysqli_connect_errno()) {
            printf("Connect failed: %s\n", mysqli_connect_error());
            exit();
        }

        $query = "SELECT * FROM `mvs_interfaces` WHERE interface_GUID = '$interface_id'";
        $result = mysqli_query($link, $query);

        if ($result = mysqli_query($link, $query)) {
            while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC) ) {
                $row["content"] = htmlspecialchars($row["content"]);
                $row["content"] = stripslashes($row["content"]);
                $movabl_array = $row;
            }
        }
        mysqli_free_result($result);

        $movabl_string = "";

        $query = "SELECT * FROM `mvs_meta` WHERE (movabl_GUID = '$interface_GUID' AND movabl_type = 'interface')";
        $result = mysqli_query($link, $query);

        if ($result = mysqli_query($link, $query)) {
            while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC))
                $movabl_array[$row["name"]] = $row["content"];
            mysqli_free_result($result);
        }

        return $movabl_array;
	
    }
	
    public static function Set_Place() {
	
        $place_id = substr($GLOBALS->GLOBALS["HTTP_SERVER_VARS"]["REQUEST_URI"],11);
        if ($place_id == "")
            $place_id = "api";

        $link = mysqli_connect('localhost','root','h4ppyf4rmers','db_api');
        if (mysqli_connect_errno()) {
            printf("Connect failed: %s\n", mysqli_connect_error());
            exit();
        }

        $query = "SELECT * FROM `mvs_places` WHERE place_GUID = '$place_id'";
        $result = mysqli_query($link, $query);

        if ($result = mysqli_query($link, $query)) {
            while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC))
                $movabl_array = $row;
        }
        mysqli_free_result($result);

        $query = "SELECT * FROM `mvs_meta` WHERE (movabl_GUID = '$place_id' AND movabl_type = 'place')";
        $result = mysqli_query($link, $query);

        if ($result = mysqli_query($link, $query)) {
            while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC) )
                $movabl_array[$row["name"]] = $row["content"];
            mysqli_free_result($result);
        }
        
        return $movabl_array;
	
    }

    public static function Set_Media() {
	
        $media_id = substr($GLOBALS->GLOBALS["HTTP_SERVER_VARS"]["REQUEST_URI"],15);
        $content = utf8_encode($GLOBALS->GLOBALS["HTTP_POST_VARS"]["content"]);
        $content = addslashes($content);

        $link = mysqli_connect('localhost','root','h4ppyf4rmers','db_api');
        if (mysqli_connect_errno()) {
            printf("Connect failed: %s\n", mysqli_connect_error());
            exit();
        }

        $query = "UPDATE `mvs_media` SET content = '$content' WHERE media_GUID = '$media_id'";
        $result = mysqli_query($link, $query);
        $timestamp = date("H:i:s");
        return  "success! $timestamp";
	
    }

    public static function Set_Function() {
	
        $function_id = substr($GLOBALS->GLOBALS["HTTP_SERVER_VARS"]["REQUEST_URI"],18);
        $content = utf8_encode ($GLOBALS->GLOBALS["HTTP_POST_VARS"]["content"]);
        $content = addslashes($content);

        $link = mysqli_connect('localhost','root','h4ppyf4rmers','db_api');
        if (mysqli_connect_errno()) {
            printf("Connect failed: %sn", mysqli_connect_error());
            exit();
        }

        $query = "UPDATE `mvs_functions` SET content = '$content' WHERE function_GUID = '$function_id'";
        $result = mysqli_query($link, $query);
        $timestamp = date("H:i:s");

        return  "success! $timestamp";
	
    }

	public static function Set_Interface() {
	
	$interface_id = substr($GLOBALS->GLOBALS["HTTP_SERVER_VARS"]["REQUEST_URI"],19);
        $content = utf8_encode ($GLOBALS->GLOBALS["HTTP_POST_VARS"]["content"]);
        $content = addslashes($content);

        $link = mysqli_connect('localhost','root','h4ppyf4rmers','db_api');
        if (mysqli_connect_errno()) {
            printf("Connect failed: %s\n", mysqli_connect_error());
            exit();
        }

        $query = "UPDATE `mvs_interfaces` SET content = '$content' WHERE interface_GUID = '$interface_id'";
        $result = mysqli_query($link, $query);
        $timestamp = date("H:i:s");
        return  "success! $timestamp";
	
    }

}
?>