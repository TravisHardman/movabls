<?php
/*
 * Movabls by LikeStripes LLC
 */

function __autoload($name) {

    if ($name == "Movabls")
        $name = "Movabls_Movabls";
    $fname = str_replace('_','/',$name);
    if (file_exists($fname.'.php'))
        require_once($fname.'.php');
    else
        throw new Exception ("Class $name not found",500);
    
}

//Override all superglobals with read-only variants
Movabls_Session::get_session();
//TODO: Delete this placeholder
$_USER['is_owner'] = true;
$GLOBALS = new Movabls_Globals();
unset($_SERVER,$_GET,$_POST,$_FILES,$_COOKIE,$_SESSION,$_REQUEST,$_ENV,$_USER);

//TODO: Delete this once you have a way to log in via the IDE
if (empty($GLOBALS->_USER['session_id'])) {
    Movabls_Users::login('email','test@test.test','testpassword');
    header('Location: http://'.$GLOBALS->_SERVER['HTTP_HOST'].$GLOBALS->_SERVER['REQUEST_URI']);
    die();
}

print_r(Movabls::get_meta('interface'));die();

//Insert API calls here temporarily (famously awesome UI)

/*Places
Movabls::set_movabl('place',array(
	'url' => '///api/get_index',
	'https' => false,
	'media_GUID' => 'mda000000014hpxfa2r865p786oe5d55',
	//'interface_GUID' => '',
));
//*/

/*Interfaces
Movabls::set_movabl('interface',array(
	'content' => array(
    
    )
));
//*/

/*Media / functions
Movabls::set_movabl('media',array(
	'inputs' => array(),
    'mimetype' => 'image/png',
	'content' => <<<HEY
HEY
));
//*/

//Run it!
new Movabls_Run;

/*
$iterations = 1000;
ob_start();
$times = array();
$squares = array();
for ($i=1;$i<=$iterations;$i++) {
	$start = microtime(true);
	new Movabls_Run;
	$time = microtime(true) - $start;
	$times[] = $time;
	$squares[] = $time*$time;
}
ob_end_clean();
$variance = (array_sum($squares) - array_sum($times)*array_sum($times)/count($times)) / count($times);
echo "<br /><br />\n\n";
echo "mean run: ".(array_sum($times)/count($times))."<br />\n";
echo "max run: ".max($times)."<br />\n";
echo "std dev: ".sqrt($variance)."<br />\n";
// */