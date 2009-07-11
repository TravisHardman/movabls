-- phpMyAdmin SQL Dump
-- version 2.11.9.4
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Jul 11, 2009 at 12:37 AM
-- Server version: 5.1.34
-- PHP Version: 5.2.9

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `db_cheeseburger`
--

-- --------------------------------------------------------

--
-- Table structure for table `mvs_applications`
--

CREATE TABLE IF NOT EXISTS `mvs_applications` (
  `application_id` int(11) NOT NULL AUTO_INCREMENT,
  `guid` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL,
  `url` text COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`application_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=DYNAMIC AUTO_INCREMENT=2 ;

--
-- Dumping data for table `mvs_applications`
--

INSERT INTO `mvs_applications` (`application_id`, `guid`, `url`) VALUES
(1, NULL, 'cb.movabls.com');

-- --------------------------------------------------------

--
-- Table structure for table `mvs_arguments`
--

CREATE TABLE IF NOT EXISTS `mvs_arguments` (
  `argument_id` int(11) NOT NULL AUTO_INCREMENT,
  `function_id` int(11) NOT NULL,
  `tag` varchar(512) COLLATE utf8_unicode_ci NOT NULL,
  `argument_type` enum('instance','function') COLLATE utf8_unicode_ci NOT NULL,
  `default_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`argument_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=6 ;

--
-- Dumping data for table `mvs_arguments`
--

INSERT INTO `mvs_arguments` (`argument_id`, `function_id`, `tag`, `argument_type`, `default_id`) VALUES
(1, 1, 'arg1', 'instance', 5),
(2, 2, 'arg1', 'instance', NULL),
(3, 1, 'arg2', 'instance', NULL),
(4, 2, 'hidoggie', 'function', 3),
(5, 4, 'functionargument', 'instance', 9);

-- --------------------------------------------------------

--
-- Table structure for table `mvs_functions`
--

CREATE TABLE IF NOT EXISTS `mvs_functions` (
  `function_id` int(11) NOT NULL AUTO_INCREMENT,
  `guid` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL,
  `code` text COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`function_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=DYNAMIC AUTO_INCREMENT=6 ;

--
-- Dumping data for table `mvs_functions`
--

INSERT INTO `mvs_functions` (`function_id`, `guid`, `code`) VALUES
(1, NULL, 'return $arg1;'),
(2, NULL, 'return $arg1.''hi dad and ''.$hidoggie();'),
(3, NULL, 'return ''hi doggie!'';'),
(4, 'helloworld', '//return "hello world: $functionargument";\r\n\r\nreturn Array ("a"=>"d", "b"=>"e", "c"=>"f");'),
(5, 'get globals', 'return $GLOBALS;');

-- --------------------------------------------------------

--
-- Table structure for table `mvs_instances`
--

CREATE TABLE IF NOT EXISTS `mvs_instances` (
  `instance_id` int(11) NOT NULL AUTO_INCREMENT,
  `movabls_type` enum('function','media','php') COLLATE utf8_unicode_ci NOT NULL,
  `movabls_id` varchar(512) COLLATE utf8_unicode_ci NOT NULL,
  `interface_ids` text COLLATE utf8_unicode_ci COMMENT 'comma-separated, optional if movabls_type is media',
  PRIMARY KEY (`instance_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=16 ;

--
-- Dumping data for table `mvs_instances`
--

INSERT INTO `mvs_instances` (`instance_id`, `movabls_type`, `movabls_id`, `interface_ids`) VALUES
(1, 'media', '1', '1,2'),
(2, 'function', '1', NULL),
(3, 'function', '2', NULL),
(4, 'media', '2', '3'),
(5, 'function', '4', NULL),
(6, 'media', '4', NULL),
(7, 'media', '5', NULL),
(8, 'media', '6', NULL),
(9, 'media', '3', NULL),
(10, 'media', '7', '4'),
(11, 'media', '8', NULL),
(12, 'media', '9', NULL),
(13, 'media', '10', NULL),
(14, 'media', '11', NULL),
(15, 'function', '5', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `mvs_interfaces`
--

CREATE TABLE IF NOT EXISTS `mvs_interfaces` (
  `interface_id` int(11) NOT NULL AUTO_INCREMENT,
  `guid` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`interface_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=DYNAMIC AUTO_INCREMENT=4 ;

--
-- Dumping data for table `mvs_interfaces`
--

INSERT INTO `mvs_interfaces` (`interface_id`, `guid`) VALUES
(1, NULL),
(2, NULL),
(3, 'asdf');

-- --------------------------------------------------------

--
-- Table structure for table `mvs_lookups`
--

CREATE TABLE IF NOT EXISTS `mvs_lookups` (
  `lookup_id` int(11) NOT NULL AUTO_INCREMENT,
  `interface_id` int(11) NOT NULL,
  `instance_id` int(11) DEFAULT NULL COMMENT 'null if top-level lookup',
  `argument_id` int(11) DEFAULT NULL COMMENT 'only for lookups of function args',
  `tag` varchar(512) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'only for top-level lookups',
  `lookup_type` enum('instance','tag') COLLATE utf8_unicode_ci NOT NULL,
  `content` varchar(512) COLLATE utf8_unicode_ci NOT NULL,
  `order` tinyint(4) DEFAULT NULL COMMENT 'Null if not executed in sequence',
  PRIMARY KEY (`lookup_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=DYNAMIC AUTO_INCREMENT=13 ;

--
-- Dumping data for table `mvs_lookups`
--

INSERT INTO `mvs_lookups` (`lookup_id`, `interface_id`, `instance_id`, `argument_id`, `tag`, `lookup_type`, `content`, `order`) VALUES
(1, 1, NULL, NULL, 'testnumber', 'instance', '6', 1),
(2, 2, NULL, NULL, 'testbool', 'instance', '7', NULL),
(3, 1, NULL, NULL, 'mynumber', 'instance', '2', 2),
(4, 1, NULL, NULL, 'mystring', 'instance', '3', 3),
(5, 1, 3, 2, NULL, 'instance', '8', NULL),
(6, 1, 2, 3, NULL, 'tag', 'testnumber', NULL),
(7, 1, 3, 4, NULL, 'instance', '3', NULL),
(8, 3, NULL, NULL, 'mytag', 'instance', '5', 1),
(12, 4, NULL, NULL, 'GLOBALS', 'instance', '15', 1);

-- --------------------------------------------------------

--
-- Table structure for table `mvs_media`
--

CREATE TABLE IF NOT EXISTS `mvs_media` (
  `media_id` int(11) NOT NULL AUTO_INCREMENT,
  `guid` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL,
  `mimetype` enum('text/plain','text/html','text/css','text/javascript','serial') COLLATE utf8_unicode_ci NOT NULL,
  `content` text COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`media_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=DYNAMIC AUTO_INCREMENT=12 ;

--
-- Dumping data for table `mvs_media`
--

INSERT INTO `mvs_media` (`media_id`, `guid`, `mimetype`, `content`) VALUES
(1, NULL, 'text/plain', 'hi mom {mystring}\r\n\r\nhow are you?\r\n\r\nnumber: {testnumber}'),
(2, 'IDE', 'text/html', '<html>\r\n<head>\r\n<title>/// !</title>\r\n\r\n<script> \r\nvar responseDiv="focusajax";\r\nrequestInProgress=0;\r\nvar saveZ = 0;\r\n\r\nscrimAr= new Array();\r\nscrimTokenAr=new Array();\r\n\r\n\r\n \r\nfunction getElementsByClassName(classname, node)  {\r\n    if(!node) node = document.getElementsByTagName("body")[0];\r\n    var a = [];\r\n    var re = new RegExp(''\\b'' + classname + ''\\b'');\r\n var els = node.getElementsByTagName("*");\r\n    for(var i=0; i<=els.length; i++) \r\n        if(re.test(els[i].className))a.push(els[i]); \r\n	return a; \r\n \r\n	} \r\n	\r\n \r\n\r\n</script> \r\n<script src="/~/ClientSide/JS/ajaxbase.js"> </script> \r\n<!--<base target="_new" />-->\r\n</head>\r\n<body>\r\n<div class="appGroup">\r\n{{mytag}}\r\n{{for mytag as abc=>def }}\r\n{{abc}} >>> {{def}}\r\n{{endfor}}\r\n\r\n< ? foreach ($applist as $desktopitem) { ? >\r\n<span class="desktopItem"><?=$desktopitem["value"]?></span>\r\n< ? } ? >\r\n<span class="desktopItem"><a href="/~/MVS/APP-qcJCfDxzACw2C4ftT6ilt1z6aND58qv">all open</a></span>\r\n\r\n<span class="desktopItem">todo:</span>\r\n<span class="desktopItem">new /page</span>\r\n<span class="desktopItem">new form</span>\r\n<span class="desktopItem">new post</span>\r\n<span class="desktopItem">media extensions? huh?</span>\r\n<span class="desktopItem">alerts!</span>\r\n<span class="desktopItem">manage users</span>\r\n<span class="desktopItem">mvs community</span>\r\n<span class="desktopItem">your shard[!]s</span>\r\n<span class="desktopItem">this shard[!]</span>\r\n<span class="desktopItem">help!</span>\r\n<span class="desktopItem">& ?</span></div>\r\n<div id="mvs">\r\n<a href="javascript:void(0);" onclick=''scrimManager("open","newapp","/newapp");'' class="mvsDesktopIcon"><img src="/~/File/apps-sm.png"><br>+ App!</a> <a href="javascript:void(0);" onclick=''scrimManager("open","newmedia","/newmedia");'' class="mvsDesktopIcon"><img src="/~/File/mda-sm.png"><br>+ Media!</a> <a href="javascript:void(0);" onclick=''scrimManager("open","newblock","/newblock");'' class="mvsDesktopIcon"><img src="/~/File/blk-sm.png"><br>+ Block</a><a href="javascript:void(0);" onclick=''scrimManager("open","fib34fwe3o","/You");'' class="mvsDesktopIcon"><img src="/~/File/you-sm.png"><br>You!</a> \r\n\r\n<a href="javascript:void(0);" onclick=''scrimManager("open","fib23fe3o","/Community");'' class="mvsDesktopIcon" ><img src="/~/File/community-sm.png"><br>Community</a>\r\n\r\n<form class="mvsDesktopItem" action="javascript:void(0);" onsubmit=''scrimManager("open","apps","/Apps");'' >\r\n<a href="javascript:void(0);" onclick=''scrimManager("open","fib3o","/Apps");'' ><img src="/~/File/apps-sm.png"></a>\r\n<input type="text" id="apps_query" value="Apps" onmouseover="if(this.value==''Apps'')this.value='''';" onmouseout="if(this.value=='''')this.value=''Apps'';" onfocus="if(this.value==''Apps'')this.value='''';" onblur="if(this.value=='''')this.value=''Media'';"><a class="close" href="javascript:void(0);" onclick="if(seek(''apps_query'',1).value!=''Media'') seek(''apps_query'',1).value=''Apps'';" >x</a>\r\n</form><br>\r\n\r\n<form class="mvsDesktopItem" action="javascript:void(0);" onsubmit=''scrimManager("open","asdfasdfsdfasdf","/Media");'' > \r\n<a href="javascript:void(0);" onclick=''scrimManager("open","adsggfaasdf","/Media");'' ><img src="/~/File/mda-sm.png" ></a>\r\n<input type="text" id="mda_query" value="Media" onmouseover="if(this.value==''Media'')this.value='''';" onmouseout="if(this.value=='''')this.value=''Media'';" onfocus="if(this.value==''Media'')this.value='''';" onblur="if(this.value=='''')this.value=''Media'';"><a class="close" href="javascript:void(0);" onclick="if(seek(''mda_query'',1).value!=''Media'') seek(''mda_query'',1).value=''Media'';" >x</a><input type="submit" style="display:none" onclick=''scrimManager("open","adsggfaasdf","/Media");'' >\r\n</form><br>\r\n<form class="mvsDesktopItem" action="javascript:void(0);" onsubmit=''scrimManager("open","Blocks","/Blocks");'' > \r\n<a href="javascript:void(0);" onclick=''scrimManager("open","asdfasdfasdf","/Blocks");'' ><img src="/~/File/blk-sm.png" ></a>\r\n<input type="text" id="blk_query" value="Blocks" onmouseover="if(this.value==''Blocks'')this.value='''';" onmouseout="if(this.value=='''')this.value=''Blocks'';" onfocus="if(this.value==''Blocks'')this.value='''';" onblur="if(this.value=='''')this.value=''Blocks'';"><a class="close" href="javascript:void(0);" onclick="if(seek(''blk_query'',1).value!=''Blocks'') seek(''blk_query'',1).value=''Blocks'';" >x</a>\r\n</form><br>\r\n<form class="mvsDesktopItem" action="javascript:void(0);" onsubmit=''scrimManager("open","Interfaces","/Interfaces");'' >\r\n<a href="javascript:void(0);" onclick=''scrimManager("open","3qfef","/Interfaces");'' ><img src="/~/File/int-sm.png"  ></a>\r\n<input type="text" id="int_query" value="Interfaces" onmouseover="if(this.value==''Interfaces'')this.value='''';" onmouseout="if(this.value=='''')this.value=''Interfaces'';" onfocus="if(this.value==''Interfaces'')this.value='''';" onblur="if(this.value=='''')this.value=''Interfaces'';"><a class="close" href="javascript:void(0);" onclick="if(seek(''int_query'',1).value!=''Interfaces'') seek(''int_query'',1).value=''Interfaces'';" >x</a>\r\n</form><br>\r\n<form class="mvsDesktopItem" action="javascript:void(0);" onsubmit=''scrimManager("open","States","/States");'' >\r\n<a href="javascript:void(0);" onclick=''scrimManager("open","3sdfasdfqfef","/States");'' ><img src="/~/File/int-sm.png"  ></a>\r\n<input type="text" id="ste_query" value="States" onmouseover="if(this.value==''States'')this.value='''';" onmouseout="if(this.value=='''')this.value=''States'';" onfocus="if(this.value==''States'')this.value='''';" onblur="if(this.value=='''')this.value=''States'';"><a class="close" href="javascript:void(0);" onclick="if(seek(''ste_query'',1).value!=''States'') seek(''ste_query'',1).value=''States'';" >x</a>\r\n</form><br>\r\n\r\n\r\n\r\n<form class="mvsDesktopItem" > \r\n<a href="javascript:void(0);" onclick=''scrimManager("open","fib23sds3o","///Media");'' ><img src="/~/File/os-sm.png" alt="Interfaces"></a>\r\n<select>\r\n<option>phyla.movabls.com</option>\r\n<option></option>\r\n<option>my.movabls.com</option>\r\n<option>b</option>\r\n</select>\r\n<br>\r\n<a href="javascript:void(0);" onclick="scrimManager(''open'',''thisshard'',''/~///'');"  class="subnav">settings</a>\r\n<br>\r\n<a href="javascript:void(0);" onclick="scrimManager(''open'',''MDA-CMYkixmEZSmAec4jEVw8PNFUm7I2U0Gw'',''/~/MVS/MDA-CMYkixmEZSmAec4jEVw8PNFUm7I2U0Gw/ideEditor'');"  class="subnav" >edit this page</a>\r\n</form>\r\n\r\n\r\n<br>\r\n\r\n<br>\r\n</div>\r\n<a href="javascript:void(0);" id="hiddenCloseA" style="display:none;" onclick="scrimManager(''close'',this.id)" >x</a>\r\n\r\n</body>\r\n</html>'),
(3, 'tagged', 'serial', '{s:5:"value";s:3:"100";}'),
(4, NULL, 'serial', 'i:5;'),
(5, NULL, 'serial', 'b:1;'),
(6, NULL, 'serial', 's:11:"hi mom and ";'),
(7, 'app details', 'text/html', 'app details\r\n<br>\r\nGlobals\r\n\r\n{GLOBALS}'),
(8, 'media details', 'text/html', 'media details'),
(9, 'code details', 'text/html', 'code details'),
(10, 'interface details', 'text/html', 'interface details'),
(11, 'IDE', 'text/html', '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">\r\n<html>\r\n<head>\r\n	<title>Movabls!</title>\r\n<link rel="stylesheet" type="text/css" href="http://fajita.movabls.com/~/CSS/tabs.css">\r\n\r\n<script>\r\nvar responseDiv="focusajax";\r\nrequestInProgress=0;\r\n</script>\r\n\r\n<script src="http://fajita.movabls.com/~/JS/base"></script>\r\n<script src="http://fajita.movabls.com/~/JS/tabs"></script>\r\n\r\n<script>\r\n\r\n\r\nvar saveZ = 0;\r\nfunction over(id) {\r\n	seek(id,0).height = ''auto'';\r\n	if (id=="cycleTabsRight") 	seek(id,0).width = ''150px'';\r\n	\r\n}\r\nfunction out(id) {\r\n	seek(id,0).height = ''30px'';\r\n	if (id=="cycleTabsRight") 	seek(id,0).width = ''30px'';\r\n}\r\n\r\n</script>\r\n\r\n</head>\r\n\r\n<body>\r\n\r\n\r\n\r\n<div id="header"><span id="movabl_anchor" ><a href="javascript:void(0);" onclick="switchDoc(this.id)" id="tab0" class="activeTab" onfocus="this.blur()" >[X]<br></a></span><ul id="act" onMouseOver="over(this.id);" onMouseOut="out(this.id);" class="menu" style="z-Index:10000"><li class="topnav">Movabls!</li>\r\n<li><a href="javascript:void(0);" onclick="openDoc(''http://cb.movabls.com///Apps'', ''Apps'')"  class="first" >Apps</a><a class="sec" href="javascript:void(0);" onclick="openDoc(''/new/App'', ''+ App'')"> + </a></li>\r\n<li class="meneader"> </li>\r\n<li><a href="javascript:void(0);" onclick="openDoc(''http://cb.movabls.com///Interfaces'', ''Interfaces'')"  class="first" >Interfaces</a><a class="sec" href="javascript:void(0);" onclick="openDoc(''/new/Interface'', ''+ Interface'')"> + </a></li>\r\n<li><a href="javascript:void(0);" onclick="openDoc(''http://cb.movabls.com///Media'', ''Media'')"  class="first" >Media<a class="sec" href="javascript:void(0);" onclick="openDoc(''/new/Media'', ''+ Media'')"> + </a></li>\r\n<li><a href="javascript:void(0);" onclick="openDoc(''http://cb.movabls.com///Code'', ''Code'')"  class="first" >Code</a><a class="sec" href="javascript:void(0);" onclick="openDoc(''/new/Block'', ''+ Function'')"> + </a></li>\r\n\r\n</ul><span style="display:none"><span id="cont0"><a href="javascript:void(0);" onclick="closeDoc(this.id)"  id="cls0" class="activeTab" style="width:20px; padding:3px;" >X</a></span></span><div id="trough"><div id="tabs_bar"><span id="tabs"></span><ul onMouseOver="over(this.id);" onMouseOut="out(this.id);" class="menu" style="z-Index:10000" id="cycleTabsRight"><li id="name" >+</li></ul></div></div></div>\r\n\r\n\r\n\r\n\r\n				\r\n<div id="window_presentation"><iframe id="doc0" class="activeDoc" src="/">\r\n</iframe></div>\r\n\r\n\r\n\r\n<div id="footer"><div id="globalstatus"></div> <img id="logo" src="http://phyla.movabls.com/~/File/logo.png"><span id="tag">LikeStripes</span></div>\r\n\r\n\r\n<script language="JavaScript" type="text/javascript">\r\n\r\nrawlocation= document.location.href;\r\nif (rawlocation.indexOf("#") > 0){\r\nlocAr=rawlocation.split("#");\r\nopenDoc(locAr[1],locAr[1] );\r\n}\r\n\r\n</script>\r\n</body>\r\n\r\n</html>\r\n');

-- --------------------------------------------------------

--
-- Table structure for table `mvs_places`
--

CREATE TABLE IF NOT EXISTS `mvs_places` (
  `place_id` int(11) NOT NULL AUTO_INCREMENT,
  `application_id` int(11) NOT NULL,
  `url` text COLLATE utf8_unicode_ci NOT NULL,
  `instance_id` int(11) NOT NULL,
  PRIMARY KEY (`place_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=DYNAMIC AUTO_INCREMENT=8 ;

--
-- Dumping data for table `mvs_places`
--

INSERT INTO `mvs_places` (`place_id`, `application_id`, `url`, `instance_id`) VALUES
(1, 1, '/', 1),
(2, 1, '///', 4),
(3, 1, '///Apps/%', 10),
(4, 1, '///Media/%', 11),
(5, 1, '///Code/%', 12),
(6, 1, '///Interfaces/%', 13),
(7, 1, '///!', 14);
