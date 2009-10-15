-- phpMyAdmin SQL Dump
-- version 3.1.1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Oct 15, 2009 at 12:06 AM
-- Server version: 5.1.30
-- PHP Version: 5.2.8

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `db_filet`
--

-- --------------------------------------------------------

--
-- Table structure for table `mvs_functions`
--

CREATE TABLE IF NOT EXISTS `mvs_functions` (
  `function_id` int(11) NOT NULL AUTO_INCREMENT,
  `function_GUID` varchar(512) NOT NULL,
  `inputs` text NOT NULL,
  `content` text NOT NULL,
  PRIMARY KEY (`function_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=7 ;

--
-- Dumping data for table `mvs_functions`
--

INSERT INTO `mvs_functions` (`function_id`, `function_GUID`, `inputs`, `content`) VALUES
(1, 'NESTED_FUNCTION', '["tentimes"]', '$return = '''';\r\nfor($i=1;$i<=10;$i++) {\r\n    $return .= $tentimes;\r\n}\r\nreturn $return;'),
(2, 'triplefunction', '', 'return "triplefunction";'),
(3, 'placeList', '', '$link = mysqli_connect(''localhost'',''root'',''h4ppyf4rmers'',''db_ribeye'');\r\n\r\nif (mysqli_connect_errno()) {\r\n    printf("Connect failed: %s\\n", mysqli_connect_error());\r\n    exit();\r\n}\r\n\r\n$query = "SELECT * FROM `mvs_places`";\r\n$result = mysqli_query($link, $query);\r\n\r\n\r\n\r\nif ($result = mysqli_query($link, $query)) {\r\n\r\n    while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC) ) \r\n        $resultsAr[]=$row;\r\n    \r\n\r\n    mysqli_free_result($result);\r\n}\r\n\r\n\r\n\r\nreturn $resultsAr;\r\n'),
(4, 'GLOBALS', '', 'return $GLOBALS->GLOBALS["HTTP_SERVER_VARS"];');

-- --------------------------------------------------------

--
-- Table structure for table `mvs_groups`
--

CREATE TABLE IF NOT EXISTS `mvs_groups` (
  `group_id` int(11) NOT NULL AUTO_INCREMENT,
  `group_GUID` varchar(512) NOT NULL,
  `permissions_editor` tinyint(1) NOT NULL,
  PRIMARY KEY (`group_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

--
-- Dumping data for table `mvs_groups`
--

INSERT INTO `mvs_groups` (`group_id`, `group_GUID`, `permissions_editor`) VALUES
(1, 'mysiteusers', 1);

-- --------------------------------------------------------

--
-- Table structure for table `mvs_interfaces`
--

CREATE TABLE IF NOT EXISTS `mvs_interfaces` (
  `interface_id` int(11) NOT NULL AUTO_INCREMENT,
  `interface_GUID` varchar(512) CHARACTER SET latin1 NOT NULL,
  `content` text CHARACTER SET latin1 NOT NULL,
  PRIMARY KEY (`interface_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC AUTO_INCREMENT=7 ;

--
-- Dumping data for table `mvs_interfaces`
--

INSERT INTO `mvs_interfaces` (`interface_id`, `interface_GUID`, `content`) VALUES
(1, 'TEST_INT', '{"mytag":{"movabl_GUID":"NESTED_MEDIA","movabl_type":"media","tags":{"footag":{"movabl_GUID":"FOO_MEDIA","movabl_type":"media"}}},"othertag":{"expression":"strtoupper($mytag)"},"functiontag":{"movabl_GUID":"NESTED_FUNCTION","movabl_type":"function","tags":{"tentimes":{"movabl_GUID":"FOO_MEDIA","movabl_type":"media"}}},"placetag":{"movabl_GUID":"NESTED_PLACE","movabl_type":"place"},"expressiontag":{"expression":"$GLOBALS->_SERVER[\\"REQUEST_URI\\"]"},"otherexpressiontag":{"expression":"(rand(0,10) > 5 ? \\"HIGH NUMBER!!!\\" : \\"LOW NUMBER!!!\\")"},"finalexpressiontag":{"expression":"Movabls::get_movabl(''place'',''test'')"},"phptag":{"php":"date","interface_GUID":null}}'),
(2, 'tripleslashintguid', '{"tripletag":{"movabl_GUID":"FOO_MEDIA","movabl_type":"media"},"tripletag2":{"movabl_GUID":"triplefunction","movabl_type":"function"}}'),
(3, 'excite_interface', '{"placeList":{"movabl_GUID":"placeList","movabl_type":"function"},"GLOBALS":{"movabl_GUID":"GLOBALS","movabl_type":"function"}}');

-- --------------------------------------------------------

--
-- Table structure for table `mvs_media`
--

CREATE TABLE IF NOT EXISTS `mvs_media` (
  `media_id` int(11) NOT NULL AUTO_INCREMENT,
  `media_GUID` varchar(512) CHARACTER SET latin1 NOT NULL,
  `mimetype` varchar(512) CHARACTER SET latin1 NOT NULL,
  `inputs` text CHARACTER SET latin1 NOT NULL,
  `content` longblob NOT NULL,
  PRIMARY KEY (`media_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC AUTO_INCREMENT=25 ;

--
-- Dumping data for table `mvs_media`
--

INSERT INTO `mvs_media` (`media_id`, `media_GUID`, `mimetype`, `inputs`, `content`) VALUES
(1, 'TEST_MEDIA', 'text/html', '["mytag","functiontag","othertag","placetag","expressiontag","otherexpressiontag","finalexpressiontag","phptag"]', 0x546865206d65646961207461672073617973207b7b6d797461677d7d3c6272202f3e3c6272202f3e0d0a0d0a5468652066756e6374696f6e207461672073617973207b7b66756e6374696f6e7461677d7d2e3c6272202f3e3c6272202f3e0d0a0d0a546865206f74686572207461672073617973207b7b6f746865727461677d7d3c6272202f3e3c6272202f3e0d0a0d0a54686520706c6163652075726c206973207b7b706c6163657461677d7d3c6272202f3e3c6272202f3e0d0a0d0a46697273742045787072657373696f6e3a207b7b65787072657373696f6e7461677d7d3c6272202f3e3c6272202f3e0d0a0d0a5365636f6e642045787072657373696f6e3a207b7b6f7468657265787072657373696f6e7461677d7d3c6272202f3e3c6272202f3e0d0a0d0a5468697264204578707265737373696f6e3a207b7b66696e616c65787072657373696f6e7461677d7d3c6272202f3e3c6272202f3e0d0a0d0a5965737465726461793a207b7b7068707461677d7d),
(2, 'NESTED_MEDIA', 'text/html', '["footag"]', 0x227468657265277320616e6f746865722074616720746861742073617973207b7b666f6f7461677d7d22),
(3, 'FOO_MEDIA', 'text/html', '', 0x776f666f6f),
(4, 'tripleslashmediaguid', 'text/html', '["tripletag", "tripletag2"]', 0x747269706c65736c6173686d65646961677569643a207b7b696620747269706c65746167203d3d20747269706c65746167327d7d207361646661736466207b7b656c73657d7d207b7b747269706c65746167327d7d207b7b656e6469667d7d),
(5, 'placeslist', 'text/html', '["placeList","GLOBALS"]', 0x213c62723e0d0a7b7b666f7220706c6163654c69737420617320706c6163657d7d0d0a3c6120687265663d22687474703a2f2f7b7b474c4f42414c535b22485454505f484f5354225d7d7d7b7b706c6163655b2275726c225d7d7d223e7b7b706c6163655b2275726c225d7d7d3c2f613e203c6120687265663d22687474703a2f2f7b7b474c4f42414c535b22485454505f484f5354225d7d7d2f217b7b706c6163655b2275726c225d7d7d223e65646974213c2f613e3c62723e0d0a7b7b656e64666f727d7d0d0a0d0a),
(7, 'excitetabsmedia', 'text/html', '', 0x3c21444f43545950452068746d6c205055424c494320222d2f2f5733432f2f445444205848544d4c20312e30205472616e736974696f6e616c2f2f454e222022687474703a2f2f7777772e77332e6f72672f54522f7868746d6c312f4454442f7868746d6c312d7472616e736974696f6e616c2e647464223e200d0a3c68746d6c3e200d0a3c686561643e200d0a093c7469746c653e4d6f7661626c7320213c2f7469746c653e200d0a0d0a3c6c696e6b2072656c3d227374796c6573686565742220747970653d22746578742f6373732220687265663d222f7e2f4353532f746162732e637373223e200d0a3c736372697074207372633d222f7e2f4a532f626173652e6a73223e3c2f7363726970743e200d0a3c736372697074207372633d222f7e2f4a532f746162732e6a73223e3c2f7363726970743e200d0a0d0a3c2f686561643e200d0a200d0a3c626f64793e200d0a200d0a200d0a3c212d2d20746162206261722f6865616465723a0d0a20206d6f7661626c5f616e63686f72202d20746865205b585d207468696e67202d2069742072657475726e7320746f20746865206261736520646f63756d656e7420617320646566696e656420696e2074686520696672616d65207372630d0a2020616374696f6e734c697374202d207468652064726f70646f776e206f6620617661696c61626c6520636f6d6d616e64732e202061203c756c3e207573696e672073686f774f766572666c6f77202620686964654f766572666c6f77282920200d0a2020636f6e7430202d207468652074656d706c617465207461622074686174277320636c6f6e656420696e206f70656e446f630d0a202074726f756768202d20746865206d61696e207461622077656c6c20696e636c7564696e6720746865206f766572666c6f772064726f70646f776e0d0a202074616273202d20746865207370656369666963206469762077686572652074616273206c6976652c206e6f7420696e636c7564696e6720746865206f766572666c6f772077656c6c0d0a20202d2d3e0d0a3c6469762069643d22686561646572223e3c7370616e2069643d226d6f7661626c5f616e63686f7222203e3c6120687265663d226a6176617363726970743a766f69642830293b22206f6e636c69636b3d22737769746368446f6328746869732e696429222069643d22746162302220636c6173733d2261637469766554616222206f6e666f6375733d22746869732e626c7572282922203e5b585d3c62723e3c2f613e3c2f7370616e3e3c756c2069643d22616374696f6e734c69737422206f6e4d6f7573654f7665723d2273686f774f766572666c6f7728746869732e6964293b22206f6e4d6f7573654f75743d22686964654f766572666c6f7728746869732e6964293b2220636c6173733d226d656e7522207374796c653d227a2d496e6465783a3130303030223e3c6c6920636c6173733d22746f706e6176223e4d6f7661626c73213c2f6c693e200d0a3c6c693e3c6120687265663d226a6176617363726970743a766f69642830293b22206f6e636c69636b3d226f70656e446f632827687474703a2f2f63622e6d6f7661626c732e636f6d2f2f2f41707073272c2027417070732729222020636c6173733d22666972737422203e417070733c2f613e3c6120636c6173733d227365632220687265663d226a6176617363726970743a766f69642830293b22206f6e636c69636b3d226f70656e446f6328272f6e65772f417070272c20272b204170702729223e202b203c2f613e3c2f6c693e200d0a3c6c6920636c6173733d22626c616e6b223e203c2f6c693e200d0a3c6c693e3c6120687265663d226a6176617363726970743a766f69642830293b22206f6e636c69636b3d226f70656e446f632827687474703a2f2f63622e6d6f7661626c732e636f6d2f2f2f496e7465726661636573272c2027496e74657266616365732729222020636c6173733d22666972737422203e496e74657266616365733c2f613e3c6120636c6173733d227365632220687265663d226a6176617363726970743a766f69642830293b22206f6e636c69636b3d226f70656e446f6328272f6e65772f496e74657266616365272c20272b20496e746572666163652729223e202b203c2f613e3c2f6c693e200d0a3c6c693e3c6120687265663d226a6176617363726970743a766f69642830293b22206f6e636c69636b3d226f70656e446f632827687474703a2f2f63622e6d6f7661626c732e636f6d2f2f2f4d65646961272c20274d656469612729222020636c6173733d22666972737422203e4d656469613c6120636c6173733d227365632220687265663d226a6176617363726970743a766f69642830293b22206f6e636c69636b3d226f70656e446f6328272f6e65772f4d65646961272c20272b204d656469612729223e202b203c2f613e3c2f6c693e200d0a3c6c693e3c6120687265663d226a6176617363726970743a766f69642830293b22206f6e636c69636b3d226f70656e446f632827687474703a2f2f63622e6d6f7661626c732e636f6d2f2f2f436f6465272c2027436f64652729222020636c6173733d22666972737422203e436f64653c2f613e3c6120636c6173733d227365632220687265663d226a6176617363726970743a766f69642830293b22206f6e636c69636b3d226f70656e446f6328272f6e65772f426c6f636b272c20272b2046756e6374696f6e2729223e202b203c2f613e3c2f6c693e200d0a3c2f756c3e3c7370616e207374796c653d22646973706c61793a6e6f6e65223e3c7370616e2069643d22636f6e7430223e3c6120687265663d226a6176617363726970743a766f69642830293b22206f6e636c69636b3d22636c6f7365446f6328746869732e69642922202069643d22636c73302220636c6173733d2261637469766554616222207374796c653d2277696474683a323070783b2070616464696e673a3370783b22203e583c2f613e3c2f7370616e3e3c2f7370616e3e3c6469762069643d2274726f756768223e3c6469762069643d22746162735f626172223e3c7370616e2069643d2274616273223e3c2f7370616e3e3c756c206f6e4d6f7573654f7665723d2273686f774f766572666c6f7728746869732e6964293b22206f6e4d6f7573654f75743d22686964654f766572666c6f7728746869732e6964293b2220636c6173733d226d656e7522207374796c653d227a2d496e6465783a3130303030222069643d226379636c65546162735269676874223e3c6c692069643d226e616d6522203e2b3c2f6c693e3c2f756c3e3c2f6469763e3c2f6469763e3c2f6469763e200d0a200d0a200d0a200d0a3c212d2d206d61696e2070726573656e746174696f6e207370616365202e20206e6f74652074686520696672616d65207372632077696c6c206265207468652064656661756c742f626173652f6261636b67726f756e6420646f63756d656e742d2d3e0d0a3c6469762069643d2277696e646f775f70726573656e746174696f6e223e3c696672616d652069643d22646f63302220636c6173733d22616374697665446f6322207372633d222f21706c61636573223e200d0a3c2f696672616d653e3c2f6469763e200d0a200d0a200d0a200d0a3c736372697074206c616e67756167653d224a6176615363726970742220747970653d22746578742f6a617661736372697074223e200d0a200d0a2f2a0d0a20616c6c6f777320612074616220746f20626520637265617465642066726f6d20612023616e63686f7220696e207468652075726c2e2020666f72206578616d706c653a0d0a20746162732e68746d6c23687474703a2f2f676f6f676c652e636f6d0d0a2077696c6c206f70656e20676f6f676c652e636f6d20696e206120746162206f6e206c6f61642e0d0a202a2f0d0a200d0a7261776c6f636174696f6e3d20646f63756d656e742e6c6f636174696f6e2e687265663b0d0a696620287261776c6f636174696f6e2e696e6465784f662822232229203e2030297b0d0a6c6f6341723d7261776c6f636174696f6e2e73706c697428222322293b0d0a6f70656e446f63286c6f6341725b315d2c6c6f6341725b315d20293b0d0a7d0d0a200d0a3c2f7363726970743e200d0a3c2f626f64793e200d0a200d0a3c2f68746d6c3e200d0a20),
(10, 'binmedia', 'image/jpeg', '', 0xffd8ffe000104a46494600010100000100010000ffdb0043000a07070807060a0808080b0a0a0b0e18100e0d0d0e1d15161118231f2424221f2221262b372f26293429212230413134393b3e3e3e242e4449433c48373d3e3bffdb0043010a0b0b0e0d0e1c10101c3b2822283b3b3b3b3b3b3b3b3b3b3b3b3b3b3b3b3b3b3b3b3b3b3b3b3b3b3b3b3b3b3b3b3b3b3b3b3b3b3b3b3b3b3b3b3b3b3b3b3b3bffc00011080074007403012200021101031101ffc4001b00000105010100000000000000000000000002030506070104ffc40042100001040002040a07070302070000000001000203040511061221511422314161718191b2d11323334273a1b1325253547293c17494f015c224243443556283ffc4001a010002030101000000000000000000000002050103040600ffc400291100020201020504020301000000000000010200030411122131324151051322b171a12381f133ffda000c03010002110311003f00d91ce0d6973880d033249d8152b17d399a699f57018d8f0dd8eb720e28fd239facf773a34e7179269d980d593543dbaf69e399bccdede53d9d2a9f34cdd5104035616ec007bcb06464153b123ac1c157516583f027a6d5db365c4ddc5ae5971e56b242d60ec1b179f5a13ef5afee1c996b538024c5d8f331d0ad00d008e0f447dfb5fdc39743623efdafdf72480960203637993b16744717e25afdf72e8863fc4b3fbeef34009602036bf991b5624411fe249fdf779aef078ff16cfefbbcd2c04a010fbafe60ed11ae0d1fe2d9fdf779a50ac1a41659b71b87239b3bb34e8094028f7acf320813d34f1fc7f0a707476ce2100e58ac6d765d0ee5ff003915db02d22a58fd72fae4b2667b581ff6987f91d2a83c899334f875c662b44eacf09cdede691bce0ff9f40b7e366b03b5f94c39184968d546866b285e6c3ef438961f05d80fab9981c378de0f483b109d739ce9041d0ccaef59759b9895d273758b2e634ee68390f92f0b5a9e76d81dfd449e229002e7dcfc899d9d6004004b0e8661f5310c4a78edc0c998d8758070e43ac36ab7bf44f0478cb8035bfa5ee1fcaad6810cb16b1f03fdc15f532c5446ab52220f50bac4bc856239779599f41b0e782609a784f36d0e1f3dbf35116f42b10af9babbe3b2d1cc38aeee3b3e6af8baac7c4a5bb6928afd43213bebf9993cd5a6ab298a789f13c7baf6e452405aa59a95eec462b30b2466e70cf2eadcaab8ae873a30e9b0d717b794c2f3b7b0f3f6a5b760ba714e23f71a51ea55d9c1f81fd4ab809402eba3746f2c7b4b5cd3910464415d012e8c35800ba849739448e7073921aee3647683b0ae38a6f5b8c3ad1010c0961d10c723c2f0b9e94d9b84365e19d0d201fa92850553dadbf8e7c2d427f5dadb044f76356d61267972ce077f512788ae0094d19c2efea24f115d012973f231ca74c546f7c4738dee613ced710bd70e2988c3ecef596fff005765f55e9c070618cda92033987523d7cc375b3da06f1bd4c4ba0b3b47a9bd1bcee7b0b7e84a24a6e65dc8384cd6e463ab6cb0f1fc4f0d5d2dc5a023d24acb0ddd23067de3243f434c69582196d8eacf3ef7da677f28ee55bb7a378ad305cfac6460f7a23adf2e5f928d032443272293a37ee50d8d8d78d574fea6ad1c8c998248ded7b1c330e69cc15d59b61d8a5bc324d6ad290d278d1bb6b5dd63f9577c231bad8b47937d5ced1c6889f98de132c7cc4bb81e0628c9c27a788e227318c0abe2b197ec8ec01c5940e5e83bc2a35aa9351b0e82c30b1edee2378de169aa3b19c222c5aa963b26ccddb1c9b8ee3d0872b1058372757dc3c4cc351d8fd3f533b73936e7272cc32d5b0f8266164919c9cd2bcee7247b4ebc6744ba11a8839c9bd6e30eb5c739203b8e3ad5804b27b2a7b5b7f1cf85a8454f6b6fe39f0b509ad7d022cb3a8c623f62ff00ea24f114a017211ea5ff001e4f114b012bb4fccc60bd32c9a0e3fe693fc0ff00705785996178a58c26774d58465cf6ea90f048cb3cf7f429cafa6d601ff88a71bc6f8dc5a7e79a618b955575ed631366e25b6da5d07097151d8960747136932c4192f34acd8eeddfda9187e9061f8890c8e431ca792393613d5cc54a263fc772f9114ff252ddc199e62b825ac264f583d2424e4d95a361e83b8af1452c904ad9627963d8736b81da0ad3658a39e27452b03d8f1939ae1982a878fe0cfc2a60f8f375690f11c7dd3f74a4f95886af9a72fa8eb13305df07e7f72d180e38cc56131c9936cc638ed1ef0de14bacb2b5c9695a8ecc0ed5923398e9e83d0b49c36fc589d18ed45b03c6d6e7b5a79c2dd8793eeaed6e6261cec4f65b72f49fd485d2fc1b85d437e06faf8071c0f7d9e6397bd505ce5b0900ec597e92e19fe958bc90b0650c9eb22fd279bb0e63b9539b4007dc1fdcdbe9991b87b4ddb948a739241e3b7ac24b9c92d3c76f5858808ea49d4f6b6fe39f0b508a9ed6dfc73e16a130afa045767518d403d4bfe3c9e229c0122bfb293e3c9e229d0129b7accdcbca0024009dad5a5b5619042dd692439342b854d0fa51c63853e49a43cbaaed568eae7454e3d9774ccf7e4d74f5194c0159302d247c2e6d5bef2e88ec6caee5675ef1f44f627a24c642e970f73cb9a33313ce79f51deaa8e723d2ec57d7fc32b069cc4d3fd1355191da0a62e538af5492b4e33648323bc6e23a4280d10c5cd889d87ccecdf10ce3279dbbbb3e9d4aca9ed762dd5eeec620b6b6a2cda7989955fab2d0bb2d59bedc6ecb3de398f68535a178a1af88ba8c8ef57676b73e678f31f40bd7a77406a4188b06d07d149f569fa8ed0a9b1587d79e39e2393e3707b4f483984a369c7bb876fa9d1211978dc7bfdcd8d5674ea8708c185b68e3d57664ff00ea761f9e47b1586ad86daab1588fec4ac0f6f5119ae5cacdb94a6acffb3346e61ed192736287423cce769b0d3686f0662e4ae34fac6f585c90398f731fb1cd3911d2121a7d637f5049809d949aa9ed6dfc73e16a1153dadbf8e7c2d42d75f408aacea3115bd949f1e4f114f009aaa0b44f1b864e65991ae1d3ac9f0128bbfe866d07849fd0e630e2d239c06b3613abde3357559ae1f764c3ae476a2c8961da0f238738577a9a43865b8c3b85321765b59290d23bf61ec4d702e409b09d0c49ea14b9b3781a8926b32c643198c5c6b320d133b2cbad5bf16d2aa5520736a4adb1608c9ba9b5ade927f854192473dee7b9c5ce71cc93ce5067da8fa2a9d7497fa650ebabb0d019e9c32f1a189d7b20e418f1adfa4ec3f2246a7ccb1d71d8569f4719c3dd42b992fd66bcc4d2e0e99a08390cf3da8b01b40ca647aa544ed6021a475b8568fdd8f2da222f6f5b78dfc2ca1ce5acd8c570b92b4b19c46a1d661197a76f38eb59167b07522cc00b0224be95b823291de6a3a19638468d56cce6622e8cf61397cb243bcca9ba0b8952ad82cb1d9b9042e161c436495ad396ab76ed2ac9feb784ff00e4e9ff0070cf35b6961ed8d4c5595530bdf41de655a471707d21c4220320277103ace7fca8d61f5adfd414be97cd0cda4f725af2b258de584398e0e0788de70a1e06992cc6c1cef1f54b9c68c67534926a527c0fa93b53dadbf8e7c2d427b0bab3da36e48632e68b05b98de1ad42bab07608b2c650e78cf463f4ce15a53618e19417cfa788f36b7bc3bf3ef0bcfc8afda4581438fe1c6bbcfa399875a1940dac7791e759dcc6ce1d64d1c563304edfb2f3f6641bc1ff003b39166cdc621b7ac9c2c816a053cc471ce4db9cbae6bb2cc0cc6f09a767b8a5a0462041ce4d39cbaed6dc7b936e0efba7b95a0439c73936e72410efba7b9365affbaeee568126249482e4a2d7fdc77726cb5ff71ddc55a0499c242094a2c7fdc77715c6c134872644f3d4d28c6926364af65168af1bef4a38ac04460fbce4368b2b8f4b7a40c6f288c1cdce56dd19d199f13b30e2388c061a50e4eaf5dc32321e6246efaf57292a9b4ed599b23212a4d49963d0ec2df86e8ec2d9d994f3933c808da0bb907700853c84dd4051a09c8d8e5d8b1ef05e6bd87d3c4a0305daf1cf19e678cf2e90798f5210a79c10483a894cc7344686171fa6a566e420ff00db1282d1de33f9a82e08ff00ced9ef6f921097588bbb94e968626b04987047fe76cf7b7c91c124fced9ef6f9210abd8be25dac38249f9db3dedf2470493f3b67bdbe4842f6c5f13da9870493f3d67bdbe48e09267ff5d67bdbe4842f6c5f13dac38249f9eb3dedf24f55c30dab0c864bd6c35dcbaae683f44214845f12093a4bae17a1d8361af6ced81d627e512d876b907a0727c94f21098a001784e6af666b0ee3ac10842394cfffd9);

-- --------------------------------------------------------

--
-- Table structure for table `mvs_meta`
--

CREATE TABLE IF NOT EXISTS `mvs_meta` (
  `meta_id` int(11) NOT NULL AUTO_INCREMENT,
  `movabls_GUID` varchar(512) NOT NULL,
  `movabls_type` enum('package','place','interface','function','media','function_tag','media_tag','interface_tag','group') NOT NULL,
  `tag_name` varchar(512) DEFAULT NULL,
  `key` varchar(512) NOT NULL,
  `value` text NOT NULL,
  PRIMARY KEY (`meta_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=16 ;

--
-- Dumping data for table `mvs_meta`
--

INSERT INTO `mvs_meta` (`meta_id`, `movabls_GUID`, `movabls_type`, `tag_name`, `key`, `value`) VALUES
(1, 'test', 'place', NULL, 'name', 'test place'),
(2, 'TEST_MEDIA', 'media_tag', 'mytag', 'description', 'newly revised tag description'),
(13, 'TEST_MEDIA', 'media_tag', 'othertag', 'description', 'add some other description but no label'),
(10, 'TEST_MEDIA', 'media_tag', 'mytag', 'label', 'awesome tag label'),
(14, 'TEST_MEDIA', 'media', NULL, 'test', 'test'),
(15, 'NESTED_MEDIA', 'media', NULL, 'test2', 'test2');

-- --------------------------------------------------------

--
-- Table structure for table `mvs_packages`
--

CREATE TABLE IF NOT EXISTS `mvs_packages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `package_GUID` varchar(512) NOT NULL,
  `contents` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

--
-- Dumping data for table `mvs_packages`
--

INSERT INTO `mvs_packages` (`id`, `package_GUID`, `contents`) VALUES
(1, 'TEST_PACKAGE', '[{"movabl_type":"media","movabl_GUID":"TEST_MEDIA"},{"movabl_type":"media","movabl_GUID":"NESTED_MEDIA"}]');

-- --------------------------------------------------------

--
-- Table structure for table `mvs_permissions`
--

CREATE TABLE IF NOT EXISTS `mvs_permissions` (
  `permission_id` int(11) NOT NULL AUTO_INCREMENT,
  `group_GUID` varchar(512) NOT NULL,
  `movabl_type` enum('site','package','place','interface','media','function') NOT NULL,
  `movabl_GUID` varchar(512) DEFAULT NULL,
  `permission_type` enum('read','write','execute') NOT NULL,
  `inheritance_type` varchar(512) DEFAULT NULL,
  `inheritance_GUID` varchar(512) DEFAULT NULL,
  PRIMARY KEY (`permission_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=266 ;

--
-- Dumping data for table `mvs_permissions`
--

INSERT INTO `mvs_permissions` (`permission_id`, `group_GUID`, `movabl_type`, `movabl_GUID`, `permission_type`, `inheritance_type`, `inheritance_GUID`) VALUES
(253, 'mysiteusers', 'place', 'abcdefg', 'read', 'site', NULL),
(207, 'mysiteusers', 'place', 'abcdefg', 'write', 'site', NULL),
(205, 'mysiteusers', 'place', 'bin', 'write', 'site', NULL),
(252, 'mysiteusers', 'place', 'bin', 'read', 'site', NULL),
(203, 'mysiteusers', 'place', 'excitetabs', 'write', 'site', NULL),
(251, 'mysiteusers', 'place', 'excitetabs', 'read', 'site', NULL),
(201, 'mysiteusers', 'place', 'excite', 'write', 'site', NULL),
(250, 'mysiteusers', 'place', 'excite', 'read', 'site', NULL),
(249, 'mysiteusers', 'place', 'myplaceguid', 'read', 'site', NULL),
(199, 'mysiteusers', 'place', 'myplaceguid', 'write', 'site', NULL),
(197, 'mysiteusers', 'place', 'test', 'write', 'site', NULL),
(248, 'mysiteusers', 'place', 'test', 'read', 'site', NULL),
(195, 'mysiteusers', 'place', 'othertest', 'write', 'site', NULL),
(247, 'mysiteusers', 'place', 'othertest', 'read', 'site', NULL),
(193, 'mysiteusers', 'interface', 'excite_interface', 'write', 'site', NULL),
(246, 'mysiteusers', 'interface', 'excite_interface', 'read', 'site', NULL),
(191, 'mysiteusers', 'interface', 'tripleslashintguid', 'write', 'site', NULL),
(245, 'mysiteusers', 'interface', 'tripleslashintguid', 'read', 'site', NULL),
(189, 'mysiteusers', 'interface', 'TEST_INT', 'write', 'site', NULL),
(244, 'mysiteusers', 'interface', 'TEST_INT', 'read', 'site', NULL),
(185, 'mysiteusers', 'place', 'NESTED_PLACE', 'write', 'site', NULL),
(242, 'mysiteusers', 'place', 'NESTED_PLACE', 'read', 'site', NULL),
(183, 'mysiteusers', 'media', '12345', 'write', 'site', NULL),
(241, 'mysiteusers', 'media', '12345', 'read', 'site', NULL),
(181, 'mysiteusers', 'function', 'GLOBALS', 'write', 'site', NULL),
(240, 'mysiteusers', 'function', 'GLOBALS', 'read', 'site', NULL),
(179, 'mysiteusers', 'function', 'placeList', 'write', 'site', NULL),
(239, 'mysiteusers', 'function', 'placeList', 'read', 'site', NULL),
(177, 'mysiteusers', 'function', 'triplefunction', 'write', 'site', NULL),
(238, 'mysiteusers', 'function', 'triplefunction', 'read', 'site', NULL),
(175, 'mysiteusers', 'function', 'NESTED_FUNCTION', 'write', 'site', NULL),
(237, 'mysiteusers', 'function', 'NESTED_FUNCTION', 'read', 'site', NULL),
(173, 'mysiteusers', 'media', 'binmedia', 'write', 'site', NULL),
(236, 'mysiteusers', 'media', 'binmedia', 'read', 'site', NULL),
(53, 'mysiteusers', 'site', NULL, 'write', NULL, NULL),
(258, 'mysiteusers', 'media', 'TEST_MEDIA', 'read', 'place', 'testdelete_place'),
(171, 'mysiteusers', 'media', 'excitetabsmedia', 'write', 'site', NULL),
(211, 'mysiteusers', 'media', 'NESTED_MEDIA', 'read', 'interface', 'TEST_INT'),
(235, 'mysiteusers', 'media', 'excitetabsmedia', 'read', 'site', NULL),
(167, 'mysiteusers', 'media', 'placeslist', 'write', 'site', NULL),
(234, 'mysiteusers', 'media', 'placeslist', 'read', 'site', NULL),
(165, 'mysiteusers', 'media', 'tripleslashmediaguid', 'write', 'site', NULL),
(233, 'mysiteusers', 'media', 'tripleslashmediaguid', 'read', 'site', NULL),
(163, 'mysiteusers', 'media', 'FOO_MEDIA', 'write', 'site', NULL),
(232, 'mysiteusers', 'media', 'FOO_MEDIA', 'read', 'site', NULL),
(161, 'mysiteusers', 'media', 'NESTED_MEDIA', 'write', 'site', NULL),
(160, 'mysiteusers', 'media', 'TEST_MEDIA', 'read', 'site', NULL),
(159, 'mysiteusers', 'media', 'TEST_MEDIA', 'write', 'site', NULL),
(254, 'mysiteusers', 'place', 'testdelete_place', 'read', 'site', NULL),
(209, 'mysiteusers', 'package', 'TEST_PACKAGE', 'write', 'site', NULL),
(257, 'mysiteusers', 'site', NULL, 'read', NULL, NULL),
(212, 'mysiteusers', 'media', 'NESTED_MEDIA', 'write', 'interface', 'TEST_INT'),
(213, 'mysiteusers', 'media', 'FOO_MEDIA', 'read', 'interface', 'TEST_INT'),
(214, 'mysiteusers', 'media', 'FOO_MEDIA', 'write', 'interface', 'TEST_INT'),
(215, 'mysiteusers', 'function', 'NESTED_FUNCTION', 'read', 'interface', 'TEST_INT'),
(216, 'mysiteusers', 'function', 'NESTED_FUNCTION', 'write', 'interface', 'TEST_INT'),
(217, 'mysiteusers', 'media', '12345', 'read', 'interface', 'TEST_INT'),
(218, 'mysiteusers', 'media', '12345', 'write', 'interface', 'TEST_INT'),
(219, 'mysiteusers', 'place', 'NESTED_PLACE', 'read', 'interface', 'TEST_INT'),
(220, 'mysiteusers', 'place', 'NESTED_PLACE', 'write', 'interface', 'TEST_INT'),
(225, 'mysiteusers', 'interface', 'TEST_INT', 'read', NULL, NULL),
(226, 'mysiteusers', 'interface', 'TEST_INT', 'write', NULL, NULL),
(231, 'mysiteusers', 'media', 'NESTED_MEDIA', 'read', 'site', NULL),
(255, 'mysiteusers', 'place', 'testdelete_place', 'write', 'site', NULL),
(256, 'mysiteusers', 'package', 'TEST_PACKAGE', 'read', 'site', NULL),
(259, 'mysiteusers', 'media', 'TEST_MEDIA', 'write', 'place', 'testdelete_place'),
(264, 'mysiteusers', 'place', 'testdelete_place', 'read', NULL, NULL),
(265, 'mysiteusers', 'place', 'testdelete_place', 'write', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `mvs_places`
--

CREATE TABLE IF NOT EXISTS `mvs_places` (
  `place_id` int(11) NOT NULL AUTO_INCREMENT,
  `place_GUID` varchar(512) CHARACTER SET latin1 NOT NULL,
  `url` varchar(512) CHARACTER SET latin1 NOT NULL,
  `https` tinyint(1) NOT NULL,
  `media_GUID` varchar(512) CHARACTER SET latin1 NOT NULL,
  `interface_GUID` varchar(512) CHARACTER SET latin1 DEFAULT NULL,
  PRIMARY KEY (`place_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC AUTO_INCREMENT=13 ;

--
-- Dumping data for table `mvs_places`
--

INSERT INTO `mvs_places` (`place_id`, `place_GUID`, `url`, `https`, `media_GUID`, `interface_GUID`) VALUES
(1, 'othertest', '/test', 0, 'TEST_MEDIA', 'TEST_INT'),
(2, 'test', '/', 0, 'TEST_MEDIA', 'TEST_INT'),
(3, 'myplaceguid', '/!/%', 0, 'tripleslashmediaguid', 'tripleslashintguid'),
(4, 'excite', '/!places', 0, 'placeslist', 'excite_interface'),
(5, 'excitetabs', '/!', 0, 'excitetabsmedia', NULL),
(8, 'bin', '/bin', 0, 'binmedia', NULL),
(9, 'NESTED_PLACE', '/this/is/a/nested/url', 0, '12345', NULL),
(10, 'abcdefg', '/testmedia', 0, 'TEST_MEDIA', NULL),
(12, 'testdelete_place', '/testdelete', 0, 'TEST_MEDIA', 'MY_SUPER_INTERFACE');

-- --------------------------------------------------------

--
-- Table structure for table `mvs_sessions`
--

CREATE TABLE IF NOT EXISTS `mvs_sessions` (
  `session_id` int(11) NOT NULL AUTO_INCREMENT,
  `sslsession` varchar(512) NOT NULL,
  `sslrequests` varchar(512) NOT NULL,
  `httpsession` varchar(512) NOT NULL,
  `httprequests` varchar(512) NOT NULL,
  `user_GUID` varchar(512) NOT NULL,
  `term` int(11) NOT NULL,
  `expiration` datetime DEFAULT NULL,
  `userdata` text NOT NULL,
  PRIMARY KEY (`session_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `mvs_sessions`
--

