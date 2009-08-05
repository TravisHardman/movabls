-- phpMyAdmin SQL Dump
-- version 3.1.2deb1ubuntu0.1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Aug 05, 2009 at 04:15 AM
-- Server version: 5.0.75
-- PHP Version: 5.2.6-3ubuntu4.1

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `db_ribeye`
--

-- --------------------------------------------------------

--
-- Table structure for table `css`
--

CREATE TABLE IF NOT EXISTS `css` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `library` tinytext collate utf8_unicode_ci NOT NULL,
  `css` longtext collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=2 ;

--
-- Dumping data for table `css`
--

INSERT INTO `css` (`id`, `library`, `css`) VALUES
(1, 'tabs.css', 'html{overflow:hidden; }\r\n\r\n* {font-family: Consolas, sans-serif; font-size:13px;}\r\n\r\nbody { font-size:.75em; font-family:Andale Mono, consolas, sans-serif; background:#d0d0d0;margin:0px; overflow:hidden;}\r\n\r\na {text-decoration:none;}\r\nul {text-indent:0px; padding:0px;  margin:0px; list-style:none;}\r\nul li{left:0px;text-indent:0px; padding:0px; margin:0px; list-style:none;}\r\n\r\n#globalstatus{background:yellow; width:40%;}\r\n\r\n#movabl_anchor #tab0{font-size:16px; font-weight:bold; padding-left:10px; position:absolute; left:0px; top:0px;  width:85px; font-style:italic; height:100%;color:black;border-top: solid 4px #EF1B2B; color:#EF1B2B; display:block; line-height:16px; background:white;}\r\n\r\n\r\n#movabl_anchor #tab0:hover {background:#EF1B2B; color:white; fondt-size:14px; fondt-weight:bold; padding-left:10px; position:absolute; left:0px; top:0px; bottom:0px; width:100px;  font-style:italic; }\r\n\r\ndiv#layers {position:absolute; top:0px;left:85px;display:block;  height:25; width:150px; background:black; border-top:solid 4px #08A55E;}\r\ndiv#layers ul li { border:solid 0px yellow; padding-left:0px; padding-top:0px; height:42px; left:0px; right:0px; width:auto; line-height:42px; font-style:italic; color:white; }\r\ndiv#layers ul li a {font-style:italic; color:white;  padding-left:5%; border:solid 0px red; font-size:12px; font-weight:normal; display:block; height:100%; width:95%; line-height:42px;}\r\ndiv#layers ul li a:hover {font-style:italic; color:black; padding-left:2%; background:white; border:solid 0px red; font-size:15px; font-weight:normal; display:block; height:100%; width:98%; line-height:42px;}\r\ndiv#layers ul ul#interface_list li{line-height:10px; font-size:5px; }\r\ndiv#layers ul ul#interface_list a{line-height:10px; font-size:5px; }\r\n\r\ndiv#layers ul li.ele{padding-left:5px;line-height:15px; height:15px;}\r\ndiv#layers ul li.ele a{font-size:12px;line-height:15px; height:15px; }\r\ndiv#layers ul li.ele a:hover{font-size:12px;line-height:15px; height:15px; }\r\n\r\n\r\n#application_layer{backgrfound:#363636; }\r\n\r\n#header{position:absolute; top:0px; left:15px; height:35px; right:18px; z-Index:10000;}\r\n#trough{position:absolute; top:0px; left:235px; height:35px; right:0px;border-top:solid 4px #0099FF; z-Index:100032; background:white; }\r\n#mcenu:hover { font-style:italic; color:white;  background:#08A55E;font-size:12px; font-weight:normal; }\r\n#explore_menu { border-top:solid 0px #08A55E; position:absolute; top:0px; left:0px; font-style:italic; color:#0099FF;   text-align:center; font-size:12px; font-weight:normal; display:block; height:100%; width:100%; line-height:35px; background:white;}\r\n#explore_menu:hover { font-style:italic; color:white;  background:;font-size:12px; font-weight:normal; }\r\n\r\n\r\ndiv#presentation{position:absolute; top:0px; left:0px; bottom:0px; right:0px; height:auto; width:auto; border:solid 10px #e2e2e2; border-bottom:none; background: #e2e2e2; }\r\n\r\n#footer{position:absolute; bottom:0px; left:15px; height:25px; right:18px;borsder-bottom:solid 4px #e2e2e2;  background:white; }\r\n#logo{position:absolute; bottom:0px; right:90px; font-family:Century, serif; font-size:11pt; font-weight:500;}\r\n.inactiveCls{display:none;}\r\n#tag{position:absolute; bottom:0px; width:90px; height:100%; right:0px; font-family:Century, serif; font-size:11pt; line-height:25px; font-weight:500;}\r\n\r\n#tabs{position:absolute; top:0px; left:0px;right:30px; height:30px; overflow:hidden;}\r\n\r\n#tabs_bar{position:absolute; top:0px; left:0px; right:18px; display:block; height:30px; width:auto; background:white; overdflow:hiddd; z-Index:100003;}\r\n.inactiveTab{position:relative; display:block;  background:white; color:black; height:30px; width:120px; padding-left:20px; line-height:30px; float:left; padding-right:20px; border:solid 0px gainsboro;}\r\n.activeTab{position:relative;  float:left; display:block; background:gainsboro;color:#343434;height:30px; line-height:30px; width:120px; padding-left:20px; padding-right:20px; border:solid 00px yellow;}\r\n.inactiveDoc{display:none;}\r\n.activeDoc{background:gainsboro;  display:block; position:absolute; top:10px; }\r\n#window{position:absolute; top:0px; bottom:0px; left:0px; right:0px; height:auto; width:auto;overflow:hidden; background:transparent;z-Index:10;}\r\n#presentation_window{position:absolute; top:0px; bottom:50px; left:0px; right:0px; height:auto; width:auto; padding:0px; background:pink; overflow:hidden;}\r\n#action{ position:absolute; bottom:0px; left:00px; right:0px; height:40px; width:auto; background:black;}\r\n\r\n\r\ndiv#window_presentation{ overflow:auto; display:block; position:absolute; height:auto; width:auto; top:45px; left:15px; right:0px; bottom:26px;  border-right:solid 0px black; padding-top:0px; padding-left:0px; padding-right:0; background:gainsboro; z-Index:10; }\r\n\r\ndiv#window_presentation iframe{height:90%; width:100%; border:0;}\r\n\r\n#windosw_presentation{position:absolute; top:00px; bottom:0px; left:0px; right:0px; height:auto; width:auto;overflow:hidden; background:gainsboro; OVERFLOW:AUTO;z-Index:10;}\r\n\r\n.menu_on{background:yellow;}\r\n.list_sel {border:solid 1px gainsboro; background:white; color:#363636; width:100px;}\r\n\r\n#actionsList { z-Index:10000; border-top:solid 4px #08A55E; position:absolute; top:0px; left:85px; font-style:italic; color:#08A55E;   text-align:center; font-size:12px; font-weight:normal; display:block; height:30px; width:150px; overflow:hidden; line-height:35px; padding-bottom:5px; background:white; }\r\n#actionsList li{text-align:left; padding-left:15px; display:block; }\r\n#actionsList li a:hover{background:gainsboro;color:#363636; display:block; height:100%; width:100%;}\r\n#actionsList li a{background:white; color:#363636;display:block; height:100%; width:100%;}\r\n#actionsList li a.first:hover{po7sition:relative; background:gainsboro;color:#363636; display:block; height:100%; width:70%; float:left;  }\r\n#actionsList li a.first{posi7tion:relative; background:white; color:#363636;display:block; height:100%;  width:70%;float:left; }\r\n#actionsList li a.sec:hover{posit7ion:relative; background:gainsboro;color:#363636; display:inline; height:100%; width:28%;  }\r\n#actionsList li a.sec{po7sition:relative; background:white; color:#363636;display:inline; height:100%; width:28%;   }\r\n\r\n#actionsList li.blank{text-align:left; padding-left:5px;font-size:12px; color:#838383;line-height:14px; height:14px;}\r\n\r\n#cycleTabsRight{position:absolute; top:0px; right:0px; height:30px; width:30px; z-Index:100003; background:gainsboro;\r\n z-Index:10000; font-style:italic; color:#08A55E;   text-align:center; font-size:12px; font-weight:normal; display:block; overflow:hidden; line-height:35px; background:white; }\r\n#cycleTabsRight li{text-align:left;  z-Index:100003; width:150px;background:yellow; }\r\n#cycleTabsRight li a:hover{background:yellow;color:#363636; z-Index:100003; }\r\n#cycleTabsRight li a{background:yellow; color:#363636;  z-Index:100003; }\r\n\r\n\r\n.listedCont{background:red; width:150px;overflow:hidden;}\r\n');

-- --------------------------------------------------------

--
-- Table structure for table `js`
--

CREATE TABLE IF NOT EXISTS `js` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `library` tinytext collate utf8_unicode_ci NOT NULL,
  `function` tinytext collate utf8_unicode_ci NOT NULL,
  `arguments` tinytext collate utf8_unicode_ci NOT NULL,
  `code` mediumtext collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=3 ;

--
-- Dumping data for table `js`
--

INSERT INTO `js` (`id`, `library`, `function`, `arguments`, `code`) VALUES
(1, 'tabs.js', '', '', '\r\n/* \r\nclose\r\ncopies a div\r\ninputs: \r\n	nodeId = an element ID\r\n	parentNode = the parent element to attach the new div to\r\n	newNodeId = an optional element ID to use for the resulting element\r\n*/\r\nfunction clone(nodeId,parentNode,newNodeId){\r\nnewNode=seek(nodeId,1).cloneNode(true);\r\nnewNode.id=(newNodeId!="")?newNodeId:nodeId+Math.round(Math.random()*1000);\r\nnewNode.innerHTML=newNode.id;\r\nseek(parentNode,1).appendChild(newNode); \r\nreturn newNode.id;\r\n}\r\n\r\n\r\n\r\n\r\n\r\n/* \r\nopenDoc\r\ncreates a tab for a given path\r\n	path = a URL to load into the destination frame\r\n	title = a string title to display on the tab\r\n*/\r\n\r\n\r\nfunction openDoc(path, title){\r\nnewDocId=Math.round(Math.random()*1000);\r\nclone(''cont0'',''tabs'',''cont''+newDocId);\r\nseek(''cont''+newDocId,1).innerHTML="";\r\n\r\nclone(''tab0'',''cont''+newDocId,''tab''+newDocId);\r\nclone(''cls0'',''cont''+newDocId,''cls''+newDocId);\r\n\r\nclone(''doc0'',''window_presentation'',''doc''+newDocId);\r\n\r\nswitchDoc(newDocId);\r\nseek(''tab''+newDocId,1).innerHTML=title;\r\nseek(''cls''+newDocId,1).innerHTML="X";\r\n\r\nseek(''doc''+newDocId,1).src=path;\r\n\r\nleftTabs=new Array();\r\nrightTabs=new Array();\r\nshownTabs=new Array();\r\nhcnt=0;\r\nscnt=0;\r\nlcnt=0;\r\n\r\nallInTabs=seek("tabs",1).childNodes;\r\nallInList=seek("cycleTabsRight",1).childNodes;\r\nbarWidth=parseInt(seek("tabs",1).offsetWidth);\r\ncurrWidth=0;\r\nlisted=0;\r\nexposed=0;\r\n\r\nallInTabsAr=new Array();\r\nallInListAr=new Array();\r\nfor(j=0; j<allInTabs.length; j++)allInTabsAr[j]=allInTabs[j];\r\nfor(j=0; j<allInList.length; j++) if ((allInList[j].id !=undefined)&&(allInList[j].id.substring(0,6) =="l_cont")) allInListAr[j]=allInList[j];\r\n\r\n\r\nalltogether=allInTabsAr.concat(allInListAr);\r\n\r\nfor(j=0; j<allInListAr.length; j++){\r\nif ((allInListAr[j]!=undefined)&&(allInListAr[j].id!=undefined)){\r\ntabChildren=seek(allInListAr[j].id,1).childNodes;\r\nfor(r=0; r<tabChildren.length; r++) {\r\n\r\ntabChildren[r].parentNode.removeChild(tabChildren[r]);\r\n\r\n}\r\nseek("cycleTabsRight",1).removeChild(allInListAr[j]);\r\n}\r\n}\r\n\r\n\r\nfor(j=0; j<alltogether.length; j++){\r\n		currWidth +=150;\r\n		if (currWidth+30>=barWidth){\r\n		listed++;\r\nnewli=''l_''+alltogether[j].id;\r\nclone("name",''cycleTabsRight'',newli);\r\nseek(newli,1).innerHTML="";\r\n\r\nseek(newli,1).className="listedCont";\r\n\r\ntabChildren=seek(alltogether[j].id,1).childNodes;\r\nfor(r=0; r<tabChildren.length; r++){\r\nclone(tabChildren[r].id,newli,''l_''+tabChildren[r].id);\r\nseek(''l_''+tabChildren[r].id,1).innerHTML=seek(tabChildren[r].id,1).innerHTML;\r\nseek(''l_''+tabChildren[r].id,1).className="listed";\r\n\r\n\r\n}\r\n\r\n		}else{\r\n		exposed++;\r\n		}\r\n}\r\n\r\n\r\n\r\n}\r\n\r\n\r\n\r\n/*\r\nswitchDoc\r\nmakes a given document active/visible\r\n	docId= the document to be made active.\r\n*/\r\nfunction switchDoc(docID){\r\nidNum=docID;\r\n\r\nif (isNaN(parseInt(idNum) )){\r\n\r\nfirst=docID.substr(0,3);\r\nif ((first=="tab")||(first=="doc")) idNum=parseInt(docID.substr(3));\r\nif (first=="l_t") idNum=parseInt(docID.substr(5));\r\n\r\n}else{\r\nidNum=parseInt(idNum)\r\n}\r\nx=seek("window_presentation",1).childNodes;\r\nfor (i=0;i<x.length;i++)\r\n{\r\ncurrid=parseInt(x[i].id.substr(3));\r\nseek("tab"+currid, 1).className="inactiveTab";\r\nseek("cls"+currid, 1).className="inactiveCls";\r\n\r\nseek("doc"+currid, 1).className="inactiveDoc";\r\n\r\n}\r\nseek("tab"+idNum, 1).className="activeTab";\r\nseek("cls"+idNum, 1).className="activeTab";\r\n\r\nseek("doc"+idNum, 1).className="activeDoc";\r\n}\r\nfunction closeDoc(docID){\r\nidNum=docID;\r\n\r\n\r\n\r\nif (isNaN(parseInt(idNum) )){\r\n\r\nfirst=docID.substr(0,3);\r\nif ((first=="tab")||(first=="doc")||(first=="cls")) idNum=parseInt(docID.substr(3));\r\nif (first=="l_c") idNum=parseInt(docID.substr(5));\r\n}else{\r\nidNum=parseInt(idNum)\r\n}\r\n\r\n\r\nseek("doc"+idNum,1).parentNode.removeChild(seek("doc"+idNum,1));\r\nseek("tab"+idNum,1).parentNode.removeChild(seek("tab"+idNum,1));\r\nseek("cls"+idNum,1).parentNode.removeChild(seek("cls"+idNum,1));\r\nseek("cont"+idNum,1).parentNode.removeChild(seek("cont"+idNum,1));\r\n\r\nseek("l_tab"+idNum,1).parentNode.removeChild(seek("l_tab"+idNum,1));\r\nseek("l_cls"+idNum,1).parentNode.removeChild(seek("l_cls"+idNum,1));\r\nseek("l_cont"+idNum,1).parentNode.removeChild(seek("l_cont"+idNum,1));\r\n\r\n\r\n\r\nswitchDoc("doc"+0);\r\n}\r\n\r\n\r\n/*\r\nshowOverflow\r\nused to show the full height of a semi-hidden <ul> (for the drop downs/overflow list) \r\n*/\r\n\r\nfunction showOverflow(id) {\r\n	seek(id,0).height = ''auto'';\r\n	if (id=="cycleTabsRight") 	seek(id,0).width = ''150px'';\r\n	\r\n}\r\n\r\n/*\r\nhideOverflow\r\nused to semi-hide a <ul> (for the drop downs/overflow list) \r\n*/\r\nfunction hideOverflow(id) {\r\n	seek(id,0).height = ''30px'';\r\n	if (id=="cycleTabsRight") 	seek(id,0).width = ''30px'';\r\n}'),
(2, 'base.js', '', '', 'function seek(ele,type){\r\nif (ele=document.getElementById(ele)) return (type) ? ele:ele.style; else return false;}\r\n\r\n\r\nfunction stateChange(a){\r\n	if (xmlhttp.readyState==4){\r\n		 if (xmlhttp.status==200){\r\nresponse=xmlhttp.responseText;\r\nscriptstartpos=response.indexOf("script>");\r\nscriptendpos=response.indexOf("/script>")\r\nloadscript="";\r\nif (scriptendpos>0){\r\nloadscript=response.substring(scriptstartpos+7, scriptendpos-1);\r\n\r\n}\r\n\r\nstylestartpos=response.indexOf("style>");\r\nstyleendpos=response.indexOf("/style>")\r\n\r\nloadstyle="";\r\nif (styleendpos>0){\r\nloadstyle=response.substring(stylestartpos+7, styleendpos-1);\r\n\r\n}\r\n\r\n\r\n var head = document.getElementsByTagName("head")[0];\r\n   if (loadscript !=""){\r\nscript = document.createElement(''script'');\r\n   script.id = ''jstem'';\r\n   script.type = ''text/javascript'';\r\n   script.text= loadscript;\r\n   head.appendChild(script);\r\n}\r\n if (loadstyle !=""){\r\nvar cssStr = ".activeTab{background:green;} .inactiveTab{background:blue;}";\r\nvar style = document.createElement("style");\r\nstyle.setAttribute("type", "text/css");\r\nif(style.styleSheet){// IE\r\nstyle.styleSheet.cssText = loadstyle ;\r\n} else {// w3c\r\nvar cssText = document.createTextNode(loadstyle);\r\nstyle.appendChild(cssText);\r\n}\r\n   head.appendChild(style);\r\n\r\n}	\r\n\r\n\r\nif(responseDiv.substring(0,5)=="form_") seek(responseDiv,1).value=response;	\r\nelse document.getElementById(responseDiv).innerHTML=response;	\r\n\r\n			requestInProgress=0;\r\n		}  else  {\r\n			requestInProgress=2;\r\n	 	} \r\n	 } }\r\n	 \r\n	 \r\n	 \r\n	 \r\n	 \r\nfunction ajaxSubmitForm(pass1,pass2,parentId){\r\nvar d = new Date();\r\nvar t = d.getTime();\r\ntim=t+"";\r\ntimestamp=tim.substr(0,6);\r\n	if (pass1!="update"){\r\n		doRequest("ajaxSubmission/"+pass1+"/"+pass2, pass1+pass2+"Status",pass1+pass2+"Form",parentId)\r\n	}else{\r\n		doRequest("update/"+pass2, "globalstatus",pass1+pass2+"Form",parentId)\r\n	}\r\n}\r\n\r\n\r\nfunction doRequest(pass,destDiv,formId,parentId){\r\nresponseDiv=(destDiv=="")?"focusajax":destDiv;\r\nvar boundaryString = ''AaB03x'';\r\nvar boundary = ''--'' + boundaryString;\r\n\r\nsendStr = [\r\nboundary\r\n].join(''\\r\\n'');\r\n\r\n	\r\nif ((formId!=undefined)&&(formId!="")){\r\n\r\n\r\nif (parentId!="") formChildrenAr=seek(parentId,1).contentWindow.document.getElementById(formId).childNodes;\r\nelse formChildrenAr=seek(formId,1).childNodes;\r\n	strCnt=0;\r\n	for(j=0; j<formChildrenAr.length; j++) {\r\n\r\n		if ((formChildrenAr[j].name!=undefined)&&(formChildrenAr[j].name!="")) {\r\n\r\n			if ((formChildrenAr[j].type =="radio" )&&(formChildrenAr[j].checked ==0)) {\r\n\r\n			}else{\r\n\r\n				strCnt++;\r\n\r\n				sendStr+=''\\r\\n'';\r\n				sendStr+=''Content-Disposition: form-data; name="''+formChildrenAr[j].name+''"'';\r\n				sendStr+=''\\r\\n'';\r\n				sendStr+=''\\r\\n'';\r\n				sendStr+=formChildrenAr[j].value;\r\n				sendStr+=''\\r\\n'';\r\n				sendStr+=boundary;\r\n\r\n			}\r\n		}\r\n	}\r\n}\r\n\r\nif (requestInProgress==1) {\r\n	setTimeout("doRequest(''"+pass+"'',''"+destDiv+"'',''"+formId+"'',''"+id+"'',''"+parentId+"'')", 10);\r\n	return false;\r\n}\r\n	\r\nif (window.XMLHttpRequest) {\r\n\r\n	xmlhttp=new XMLHttpRequest()\r\n	xmlhttp.onreadystatechange=stateChange\r\n	requestInProgress=1;\r\n	xmlhttp.open("POST",pass,true)\r\n	xmlhttp.setRequestHeader(''Content-Type'',''multipart/form-data; boundary='' + boundaryString);\r\n	xmlhttp.setRequestHeader("Content-length", sendStr.length);\r\n	xmlhttp.setRequestHeader("Connection", "close");\r\n	xmlhttp.send(sendStr);\r\n\r\n}else if (window.ActiveXObject) {\r\n	xmlhttp=new ActiveXObject("Microsoft.XMLHTTP")\r\n	if (xmlhttp){\r\n		xmlhttp.onreadystatechange=stateChange\r\n		xmlhttp.open("POST",pass,true)\r\n\r\n		xmlhttp.setRequestHeader(''Content-Type'',''multipart/form-data; boundary='' + boundaryString);\r\n		xmlhttp.setRequestHeader("Content-length", sendStr.length);\r\n		xmlhttp.setRequestHeader("Connection", "close");\r\n\r\n		xmlhttp.send(sendStr);\r\n\r\n	}\r\n}}\r\n');

-- --------------------------------------------------------

--
-- Table structure for table `mvs_functions`
--

CREATE TABLE IF NOT EXISTS `mvs_functions` (
  `function_id` int(11) NOT NULL auto_increment,
  `function_GUID` varchar(512) NOT NULL,
  `inputs` text NOT NULL,
  `content` text NOT NULL,
  PRIMARY KEY  (`function_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=7 ;

--
-- Dumping data for table `mvs_functions`
--

INSERT INTO `mvs_functions` (`function_id`, `function_GUID`, `inputs`, `content`) VALUES
(1, 'NESTED_FUNCTION', '["tentimes"]', '$return = '''';\r\nfor($i=1;$i<=10;$i++) {\r\n    $return .= $tentimes;\r\n}\r\nreturn $return;'),
(2, 'triplefunction', '', 'return "triplefunction";'),
(3, 'placeList', '', '$link = mysqli_connect(''localhost'',''root'',''h4ppyf4rmers'',''db_ribeye'');\r\n\r\nif (mysqli_connect_errno()) {\r\n    printf("Connect failed: %s\\n", mysqli_connect_error());\r\n    exit();\r\n}\r\n\r\n$query = "SELECT * FROM `mvs_places`";\r\n$result = mysqli_query($link, $query);\r\n\r\n\r\n\r\nif ($result = mysqli_query($link, $query)) {\r\n\r\n    while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC) ) \r\n        $resultsAr[]=$row;\r\n    \r\n\r\n    mysqli_free_result($result);\r\n}\r\n\r\n\r\n\r\nreturn $resultsAr;\r\n'),
(4, 'GLOBALS', '', 'return $GLOBALS->GLOBALS["HTTP_SERVER_VARS"];'),
(5, 'getJS', '', '\r\n$library=substr($GLOBALS->GLOBALS["HTTP_SERVER_VARS"]["REQUEST_URI"],6);\r\n\r\n\r\n\r\n$link = mysqli_connect(''localhost'',''root'',''h4ppyf4rmers'',''db_ribeye'');\r\n\r\nif (mysqli_connect_errno()) {\r\n    printf("Connect failed: %s\\n", mysqli_connect_error());\r\n    exit();\r\n}\r\n\r\n$query = "SELECT * FROM `js` WHERE library=''$library''";\r\n$result = mysqli_query($link, $query);\r\n\r\n\r\n\r\nif ($result = mysqli_query($link, $query)) {\r\n\r\n    while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC) ) \r\n        $resultsAr[]=$row;\r\n    \r\n\r\n    mysqli_free_result($result);\r\n}\r\n\r\nreturn $resultsAr;\r\n\r\n'),
(6, 'getCSS', '', '\r\n$library=substr($GLOBALS->GLOBALS["HTTP_SERVER_VARS"]["REQUEST_URI"],7);\r\n\r\n\r\n\r\n$link = mysqli_connect(''localhost'',''root'',''h4ppyf4rmers'',''db_ribeye'');\r\n\r\nif (mysqli_connect_errno()) {\r\n    printf("Connect failed: %s\\n", mysqli_connect_error());\r\n    exit();\r\n}\r\n\r\n$query = "SELECT * FROM `css` WHERE library=''$library''";\r\n$result = mysqli_query($link, $query);\r\n\r\n\r\n\r\nif ($result = mysqli_query($link, $query)) {\r\n\r\n    while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC) ) \r\n        $resultsAr[]=$row;\r\n    \r\n\r\n    mysqli_free_result($result);\r\n}\r\n\r\nreturn $resultsAr;\r\n');

-- --------------------------------------------------------

--
-- Table structure for table `mvs_interfaces`
--

CREATE TABLE IF NOT EXISTS `mvs_interfaces` (
  `interface_id` int(11) NOT NULL auto_increment,
  `interface_GUID` varchar(512) character set latin1 NOT NULL,
  `content` text character set latin1 NOT NULL,
  PRIMARY KEY  (`interface_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC AUTO_INCREMENT=6 ;

--
-- Dumping data for table `mvs_interfaces`
--

INSERT INTO `mvs_interfaces` (`interface_id`, `interface_GUID`, `content`) VALUES
(1, 'TEST_INT', '{"mytag":{"movabl_GUID":"NESTED_MEDIA","movabl_type":"media","tags":{"footag":{"movabl_GUID":"FOO_MEDIA","movabl_type":"media"}}},"othertag":{"toplevel_tag":"mytag"},"functiontag":{"movabl_GUID":"NESTED_FUNCTION","movabl_type":"function","tags":{"tentimes":{"movabl_GUID":"FOO_MEDIA","movabl_type":"media"}}}}'),
(2, 'tripleslashintguid', '{"tripletag":{"movabl_GUID":"FOO_MEDIA","movabl_type":"media"},"tripletag2":{"movabl_GUID":"triplefunction","movabl_type":"function"}}'),
(3, 'excite_interface', '{"placeList":{"movabl_GUID":"placeList","movabl_type":"function"},"GLOBALS":{"movabl_GUID":"GLOBALS","movabl_type":"function"}}'),
(4, 'JSLOOKUP', '{"JS":{"movabl_GUID":"getJS","movabl_type":"function"}}'),
(5, 'CSSLOOKUP', '{"CSS":{"movabl_GUID":"getCSS","movabl_type":"function"}}');

-- --------------------------------------------------------

--
-- Table structure for table `mvs_media`
--

CREATE TABLE IF NOT EXISTS `mvs_media` (
  `media_id` int(11) NOT NULL auto_increment,
  `media_GUID` varchar(512) character set latin1 NOT NULL,
  `mimetype` varchar(512) character set latin1 NOT NULL,
  `inputs` text character set latin1 NOT NULL,
  `content` longblob NOT NULL,
  PRIMARY KEY  (`media_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC AUTO_INCREMENT=11 ;

--
-- Dumping data for table `mvs_media`
--

INSERT INTO `mvs_media` (`media_id`, `media_GUID`, `mimetype`, `inputs`, `content`) VALUES
(1, 'TEST_MEDIA', 'text/html', '["mytag","functiontag","othertag"]', 0x22546865206d65646961207461672073617973207b7b6d797461677d7d3c6272202f3e3c6272202f3e0d0a0d0a5468652066756e6374696f6e207461672073617973207b7b66756e6374696f6e7461677d7d2e3c6272202f3e3c6272202f3e0d0a0d0a546865206f74686572207461672073617973207b7b6f746865727461677d7d22),
(2, 'NESTED_MEDIA', 'text/html', '["footag"]', 0x227468657265277320616e6f746865722074616720746861742073617973207b7b666f6f7461677d7d22),
(3, 'FOO_MEDIA', 'text/html', '', 0x776f666f6f),
(4, 'tripleslashmediaguid', 'text/html', '["tripletag", "tripletag2"]', 0x747269706c65736c6173686d65646961677569643a207b7b696620747269706c65746167203d3d20747269706c65746167327d7d207361646661736466207b7b656c73657d7d207b7b747269706c65746167327d7d207b7b656e6469667d7d),
(5, 'placeslist', 'text/html', '["placeList","GLOBALS"]', 0x213c62723e0d0a7b7b666f7220706c6163654c69737420617320706c6163657d7d0d0a3c6120687265663d22687474703a2f2f7b7b474c4f42414c535b22485454505f484f5354225d7d7d7b7b706c6163655b2275726c225d7d7d223e7b7b706c6163655b2275726c225d7d7d3c2f613e203c6120687265663d22687474703a2f2f7b7b474c4f42414c535b22485454505f484f5354225d7d7d2f217b7b706c6163655b2275726c225d7d7d223e65646974213c2f613e3c62723e0d0a7b7b656e64666f727d7d0d0a0d0a),
(6, 'usefuljunk', 'text/plain', '', 0x213c62723e0d0a7b7b666f7220706c6163654c69737420617320706c6163657d7d0d0a7b7b706c6163655b2275726c225d7d7d0d0a3c212d2d0d0a7b7b666f7220706c616365206173206b3d3e767d7d0d0a7b7b6966206b203d202275726c227d7d0d0a7b7b767d7d203c62723e0d0a7b7b656e6469667d7d0d0a7b7b656e64666f727d7d0d0a2d2d3e0d0a3c62723e3c62723e0d0a7b7b656e64666f727d7d0d0a0d0a7b7b706c6163654c6973747d7d),
(7, 'excitetabsmedia', 'text/html', '', 0x3c21444f43545950452068746d6c205055424c494320222d2f2f5733432f2f445444205848544d4c20312e30205472616e736974696f6e616c2f2f454e222022687474703a2f2f7777772e77332e6f72672f54522f7868746d6c312f4454442f7868746d6c312d7472616e736974696f6e616c2e647464223e200d0a3c68746d6c3e200d0a3c686561643e200d0a093c7469746c653e4d6f7661626c7320213c2f7469746c653e200d0a0d0a3c6c696e6b2072656c3d227374796c6573686565742220747970653d22746578742f6373732220687265663d222f7e2f4353532f746162732e637373223e200d0a3c736372697074207372633d222f7e2f4a532f626173652e6a73223e3c2f7363726970743e200d0a3c736372697074207372633d222f7e2f4a532f746162732e6a73223e3c2f7363726970743e200d0a0d0a3c2f686561643e200d0a200d0a3c626f64793e200d0a200d0a200d0a3c212d2d20746162206261722f6865616465723a0d0a20206d6f7661626c5f616e63686f72202d20746865205b585d207468696e67202d2069742072657475726e7320746f20746865206261736520646f63756d656e7420617320646566696e656420696e2074686520696672616d65207372630d0a2020616374696f6e734c697374202d207468652064726f70646f776e206f6620617661696c61626c6520636f6d6d616e64732e202061203c756c3e207573696e672073686f774f766572666c6f77202620686964654f766572666c6f77282920200d0a2020636f6e7430202d207468652074656d706c617465207461622074686174277320636c6f6e656420696e206f70656e446f630d0a202074726f756768202d20746865206d61696e207461622077656c6c20696e636c7564696e6720746865206f766572666c6f772064726f70646f776e0d0a202074616273202d20746865207370656369666963206469762077686572652074616273206c6976652c206e6f7420696e636c7564696e6720746865206f766572666c6f772077656c6c0d0a20202d2d3e0d0a3c6469762069643d22686561646572223e3c7370616e2069643d226d6f7661626c5f616e63686f7222203e3c6120687265663d226a6176617363726970743a766f69642830293b22206f6e636c69636b3d22737769746368446f6328746869732e696429222069643d22746162302220636c6173733d2261637469766554616222206f6e666f6375733d22746869732e626c7572282922203e5b585d3c62723e3c2f613e3c2f7370616e3e3c756c2069643d22616374696f6e734c69737422206f6e4d6f7573654f7665723d2273686f774f766572666c6f7728746869732e6964293b22206f6e4d6f7573654f75743d22686964654f766572666c6f7728746869732e6964293b2220636c6173733d226d656e7522207374796c653d227a2d496e6465783a3130303030223e3c6c6920636c6173733d22746f706e6176223e4d6f7661626c73213c2f6c693e200d0a3c6c693e3c6120687265663d226a6176617363726970743a766f69642830293b22206f6e636c69636b3d226f70656e446f632827687474703a2f2f63622e6d6f7661626c732e636f6d2f2f2f41707073272c2027417070732729222020636c6173733d22666972737422203e417070733c2f613e3c6120636c6173733d227365632220687265663d226a6176617363726970743a766f69642830293b22206f6e636c69636b3d226f70656e446f6328272f6e65772f417070272c20272b204170702729223e202b203c2f613e3c2f6c693e200d0a3c6c6920636c6173733d22626c616e6b223e203c2f6c693e200d0a3c6c693e3c6120687265663d226a6176617363726970743a766f69642830293b22206f6e636c69636b3d226f70656e446f632827687474703a2f2f63622e6d6f7661626c732e636f6d2f2f2f496e7465726661636573272c2027496e74657266616365732729222020636c6173733d22666972737422203e496e74657266616365733c2f613e3c6120636c6173733d227365632220687265663d226a6176617363726970743a766f69642830293b22206f6e636c69636b3d226f70656e446f6328272f6e65772f496e74657266616365272c20272b20496e746572666163652729223e202b203c2f613e3c2f6c693e200d0a3c6c693e3c6120687265663d226a6176617363726970743a766f69642830293b22206f6e636c69636b3d226f70656e446f632827687474703a2f2f63622e6d6f7661626c732e636f6d2f2f2f4d65646961272c20274d656469612729222020636c6173733d22666972737422203e4d656469613c6120636c6173733d227365632220687265663d226a6176617363726970743a766f69642830293b22206f6e636c69636b3d226f70656e446f6328272f6e65772f4d65646961272c20272b204d656469612729223e202b203c2f613e3c2f6c693e200d0a3c6c693e3c6120687265663d226a6176617363726970743a766f69642830293b22206f6e636c69636b3d226f70656e446f632827687474703a2f2f63622e6d6f7661626c732e636f6d2f2f2f436f6465272c2027436f64652729222020636c6173733d22666972737422203e436f64653c2f613e3c6120636c6173733d227365632220687265663d226a6176617363726970743a766f69642830293b22206f6e636c69636b3d226f70656e446f6328272f6e65772f426c6f636b272c20272b2046756e6374696f6e2729223e202b203c2f613e3c2f6c693e200d0a3c2f756c3e3c7370616e207374796c653d22646973706c61793a6e6f6e65223e3c7370616e2069643d22636f6e7430223e3c6120687265663d226a6176617363726970743a766f69642830293b22206f6e636c69636b3d22636c6f7365446f6328746869732e69642922202069643d22636c73302220636c6173733d2261637469766554616222207374796c653d2277696474683a323070783b2070616464696e673a3370783b22203e583c2f613e3c2f7370616e3e3c2f7370616e3e3c6469762069643d2274726f756768223e3c6469762069643d22746162735f626172223e3c7370616e2069643d2274616273223e3c2f7370616e3e3c756c206f6e4d6f7573654f7665723d2273686f774f766572666c6f7728746869732e6964293b22206f6e4d6f7573654f75743d22686964654f766572666c6f7728746869732e6964293b2220636c6173733d226d656e7522207374796c653d227a2d496e6465783a3130303030222069643d226379636c65546162735269676874223e3c6c692069643d226e616d6522203e2b3c2f6c693e3c2f756c3e3c2f6469763e3c2f6469763e3c2f6469763e200d0a200d0a200d0a200d0a3c212d2d206d61696e2070726573656e746174696f6e207370616365202e20206e6f74652074686520696672616d65207372632077696c6c206265207468652064656661756c742f626173652f6261636b67726f756e6420646f63756d656e742d2d3e0d0a3c6469762069643d2277696e646f775f70726573656e746174696f6e223e3c696672616d652069643d22646f63302220636c6173733d22616374697665446f6322207372633d222f21706c61636573223e200d0a3c2f696672616d653e3c2f6469763e200d0a200d0a200d0a200d0a3c736372697074206c616e67756167653d224a6176615363726970742220747970653d22746578742f6a617661736372697074223e200d0a200d0a2f2a0d0a20616c6c6f777320612074616220746f20626520637265617465642066726f6d20612023616e63686f7220696e207468652075726c2e2020666f72206578616d706c653a0d0a20746162732e68746d6c23687474703a2f2f676f6f676c652e636f6d0d0a2077696c6c206f70656e20676f6f676c652e636f6d20696e206120746162206f6e206c6f61642e0d0a202a2f0d0a200d0a7261776c6f636174696f6e3d20646f63756d656e742e6c6f636174696f6e2e687265663b0d0a696620287261776c6f636174696f6e2e696e6465784f662822232229203e2030297b0d0a6c6f6341723d7261776c6f636174696f6e2e73706c697428222322293b0d0a6f70656e446f63286c6f6341725b315d2c6c6f6341725b315d20293b0d0a7d0d0a200d0a3c2f7363726970743e200d0a3c2f626f64793e200d0a200d0a3c2f68746d6c3e200d0a20),
(8, 'JSLOOKUP', 'application/x-javascript', '["JS"]', 0x0d0a7b7b666f72204a53206173206b3a767d7d0d0a7b7b765b22636f6465225d7d7d0d0a7b7b656e64666f727d7d),
(9, 'CSSLOOKUP', 'text/css', '["CSS"]', 0x0d0a7b7b666f7220435353206173206b3a767d7d0d0a7b7b765b22637373225d7d7d0d0a7b7b656e64666f727d7d),
(10, 'binmedia', 'image/jpeg', '', 0xffd8ffe000104a46494600010100000100010000ffdb0043000a07070807060a0808080b0a0a0b0e18100e0d0d0e1d15161118231f2424221f2221262b372f26293429212230413134393b3e3e3e242e4449433c48373d3e3bffdb0043010a0b0b0e0d0e1c10101c3b2822283b3b3b3b3b3b3b3b3b3b3b3b3b3b3b3b3b3b3b3b3b3b3b3b3b3b3b3b3b3b3b3b3b3b3b3b3b3b3b3b3b3b3b3b3b3b3b3b3b3bffc00011080074007403012200021101031101ffc4001b00000105010100000000000000000000000002030506070104ffc40042100001040002040a07070302070000000001000203040511061221511422314161718191b2d11323334273a1b1325253547293c17494f015c224243443556283ffc4001a010002030101000000000000000000000002050103040600ffc400291100020201020504020301000000000000010200030411122131324151051322b171a12381f133ffda000c03010002110311003f00d91ce0d6973880d033249d8152b17d399a699f57018d8f0dd8eb720e28fd239facf773a34e7179269d980d593543dbaf69e399bccdede53d9d2a9f34cdd5104035616ec007bcb06464153b123ac1c157516583f027a6d5db365c4ddc5ae5971e56b242d60ec1b179f5a13ef5afee1c996b538024c5d8f331d0ad00d008e0f447dfb5fdc39743623efdafdf72480960203637993b16744717e25afdf72e8863fc4b3fbeef34009602036bf991b5624411fe249fdf779aef078ff16cfefbbcd2c04a010fbafe60ed11ae0d1fe2d9fdf779a50ac1a41659b71b87239b3bb34e8094028f7acf320813d34f1fc7f0a707476ce2100e58ac6d765d0ee5ff003915db02d22a58fd72fae4b2667b581ff6987f91d2a83c899334f875c662b44eacf09cdede691bce0ff9f40b7e366b03b5f94c39184968d546866b285e6c3ef438961f05d80fab9981c378de0f483b109d739ce9041d0ccaef59759b9895d273758b2e634ee68390f92f0b5a9e76d81dfd449e229002e7dcfc899d9d6004004b0e8661f5310c4a78edc0c998d8758070e43ac36ab7bf44f0478cb8035bfa5ee1fcaad6810cb16b1f03fdc15f532c5446ab52220f50bac4bc856239779599f41b0e782609a784f36d0e1f3dbf35116f42b10af9babbe3b2d1cc38aeee3b3e6af8baac7c4a5bb6928afd43213bebf9993cd5a6ab298a789f13c7baf6e452405aa59a95eec462b30b2466e70cf2eadcaab8ae873a30e9b0d717b794c2f3b7b0f3f6a5b760ba714e23f71a51ea55d9c1f81fd4ab809402eba3746f2c7b4b5cd3910464415d012e8c35800ba849739448e7073921aee3647683b0ae38a6f5b8c3ad1010c0961d10c723c2f0b9e94d9b84365e19d0d201fa92850553dadbf8e7c2d427f5dadb044f76356d61267972ce077f512788ae0094d19c2efea24f115d012973f231ca74c546f7c4738dee613ced710bd70e2988c3ecef596fff005765f55e9c070618cda92033987523d7cc375b3da06f1bd4c4ba0b3b47a9bd1bcee7b0b7e84a24a6e65dc8384cd6e463ab6cb0f1fc4f0d5d2dc5a023d24acb0ddd23067de3243f434c69582196d8eacf3ef7da677f28ee55bb7a378ad305cfac6460f7a23adf2e5f928d032443272293a37ee50d8d8d78d574fea6ad1c8c998248ded7b1c330e69cc15d59b61d8a5bc324d6ad290d278d1bb6b5dd63f9577c231bad8b47937d5ced1c6889f98de132c7cc4bb81e0628c9c27a788e227318c0abe2b197ec8ec01c5940e5e83bc2a35aa9351b0e82c30b1edee2378de169aa3b19c222c5aa963b26ccddb1c9b8ee3d0872b1058372757dc3c4cc351d8fd3f533b73936e7272cc32d5b0f8266164919c9cd2bcee7247b4ebc6744ba11a8839c9bd6e30eb5c739203b8e3ad5804b27b2a7b5b7f1cf85a8454f6b6fe39f0b509ad7d022cb3a8c623f62ff00ea24f114a017211ea5ff001e4f114b012bb4fccc60bd32c9a0e3fe693fc0ff00705785996178a58c26774d58465cf6ea90f048cb3cf7f429cafa6d601ff88a71bc6f8dc5a7e79a618b955575ed631366e25b6da5d07097151d8960747136932c4192f34acd8eeddfda9187e9061f8890c8e431ca792393613d5cc54a263fc772f9114ff252ddc199e62b825ac264f583d2424e4d95a361e83b8af1452c904ad9627963d8736b81da0ad3658a39e27452b03d8f1939ae1982a878fe0cfc2a60f8f375690f11c7dd3f74a4f95886af9a72fa8eb13305df07e7f72d180e38cc56131c9936cc638ed1ef0de14bacb2b5c9695a8ecc0ed5923398e9e83d0b49c36fc589d18ed45b03c6d6e7b5a79c2dd8793eeaed6e6261cec4f65b72f49fd485d2fc1b85d437e06faf8071c0f7d9e6397bd505ce5b0900ec597e92e19fe958bc90b0650c9eb22fd279bb0e63b9539b4007dc1fdcdbe9991b87b4ddb948a739241e3b7ac24b9c92d3c76f5858808ea49d4f6b6fe39f0b508a9ed6dfc73e16a130afa045767518d403d4bfe3c9e229c0122bfb293e3c9e229d0129b7accdcbca0024009dad5a5b5619042dd692439342b854d0fa51c63853e49a43cbaaed568eae7454e3d9774ccf7e4d74f5194c0159302d247c2e6d5bef2e88ec6caee5675ef1f44f627a24c642e970f73cb9a33313ce79f51deaa8e723d2ec57d7fc32b069cc4d3fd1355191da0a62e538af5492b4e33648323bc6e23a4280d10c5cd889d87ccecdf10ce3279dbbbb3e9d4aca9ed762dd5eeec620b6b6a2cda7989955fab2d0bb2d59bedc6ecb3de398f68535a178a1af88ba8c8ef57676b73e678f31f40bd7a77406a4188b06d07d149f569fa8ed0a9b1587d79e39e2393e3707b4f483984a369c7bb876fa9d1211978dc7bfdcd8d5674ea8708c185b68e3d57664ff00ea761f9e47b1586ad86daab1588fec4ac0f6f5119ae5cacdb94a6acffb3346e61ed192736287423cce769b0d3686f0662e4ae34fac6f585c90398f731fb1cd3911d2121a7d637f5049809d949aa9ed6dfc73e16a1153dadbf8e7c2d42d75f408aacea3115bd949f1e4f114f009aaa0b44f1b864e65991ae1d3ac9f0128bbfe866d07849fd0e630e2d239c06b3613abde3357559ae1f764c3ae476a2c8961da0f238738577a9a43865b8c3b85321765b59290d23bf61ec4d702e409b09d0c49ea14b9b3781a8926b32c643198c5c6b320d133b2cbad5bf16d2aa5520736a4adb1608c9ba9b5ade927f854192473dee7b9c5ce71cc93ce5067da8fa2a9d7497fa650ebabb0d019e9c32f1a189d7b20e418f1adfa4ec3f2246a7ccb1d71d8569f4719c3dd42b992fd66bcc4d2e0e99a08390cf3da8b01b40ca647aa544ed6021a475b8568fdd8f2da222f6f5b78dfc2ca1ce5acd8c570b92b4b19c46a1d661197a76f38eb59167b07522cc00b0224be95b823291de6a3a19638468d56cce6622e8cf61397cb243bcca9ba0b8952ad82cb1d9b9042e161c436495ad396ab76ed2ac9feb784ff00e4e9ff0070cf35b6961ed8d4c5595530bdf41de655a471707d21c4220320277103ace7fca8d61f5adfd414be97cd0cda4f725af2b258de584398e0e0788de70a1e06992cc6c1cef1f54b9c68c67534926a527c0fa93b53dadbf8e7c2d427b0bab3da36e48632e68b05b98de1ad42bab07608b2c650e78cf463f4ce15a53618e19417cfa788f36b7bc3bf3ef0bcfc8afda4581438fe1c6bbcfa399875a1940dac7791e759dcc6ce1d64d1c563304edfb2f3f6641bc1ff003b39166cdc621b7ac9c2c816a053cc471ce4db9cbae6bb2cc0cc6f09a767b8a5a0462041ce4d39cbaed6dc7b936e0efba7b95a0439c73936e72410efba7b9365affbaeee568126249482e4a2d7fdc77726cb5ff71ddc55a0499c242094a2c7fdc77715c6c134872644f3d4d28c6926364af65168af1bef4a38ac04460fbce4368b2b8f4b7a40c6f288c1cdce56dd19d199f13b30e2388c061a50e4eaf5dc32321e6246efaf57292a9b4ed599b23212a4d49963d0ec2df86e8ec2d9d994f3933c808da0bb907700853c84dd4051a09c8d8e5d8b1ef05e6bd87d3c4a0305daf1cf19e678cf2e90798f5210a79c10483a894cc7344686171fa6a566e420ff00db1282d1de33f9a82e08ff00ced9ef6f921097588bbb94e968626b04987047fe76cf7b7c91c124fced9ef6f9210abd8be25dac38249f9db3dedf2470493f3b67bdbe4842f6c5f13da9870493f3d67bdbe48e09267ff5d67bdbe4842f6c5f13dac38249f9eb3dedf24f55c30dab0c864bd6c35dcbaae683f44214845f12093a4bae17a1d8361af6ced81d627e512d876b907a0727c94f21098a001784e6af666b0ee3ac10842394cfffd9);

-- --------------------------------------------------------

--
-- Table structure for table `mvs_places`
--

CREATE TABLE IF NOT EXISTS `mvs_places` (
  `place_id` int(11) NOT NULL auto_increment,
  `place_GUID` varchar(512) character set latin1 NOT NULL,
  `url` varchar(512) character set latin1 NOT NULL,
  `https` tinyint(1) NOT NULL,
  `media_GUID` varchar(512) character set latin1 NOT NULL,
  `interface_GUID` varchar(512) character set latin1 default NULL,
  PRIMARY KEY  (`place_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC AUTO_INCREMENT=9 ;

--
-- Dumping data for table `mvs_places`
--

INSERT INTO `mvs_places` (`place_id`, `place_GUID`, `url`, `https`, `media_GUID`, `interface_GUID`) VALUES
(1, '', '/test', 0, 'TEST_MEDIA', 'TEST_INT'),
(2, '', '/', 0, 'TEST_MEDIA', 'TEST_INT'),
(3, '', '/!/%', 0, 'tripleslashmediaguid', 'tripleslashintguid'),
(4, 'excite', '/!places', 0, 'placeslist', 'excite_interface'),
(5, 'excitetabs', '/!', 0, 'excitetabsmedia', NULL),
(6, 'JS', '/~/JS/%', 0, 'JSLOOKUP', 'JSLOOKUP'),
(7, 'CSS', '/~/CSS/%', 0, 'CSSLOOKUP', 'CSSLOOKUP'),
(8, 'bin', '/bin', 0, 'binmedia', NULL);