<?php
/*
 * Movabls by LikeStripes LLC
 */
?>

<form action="/" method="post" enctype="multipart/form-data">
    <input type="file" name="file" /><br />
    <input type="submit" value="go" />
</form>

<?
function __autoload($name) {
    if ($name == "Movabls")
	$name = "Movabls_Movabls";
    $fname = str_replace('_','/',$name);
    if (file_exists($fname.'.php'))
	require_once($fname.'.php');
    else
	throw new Exception ("Class $name not found",500);
}

try {
//Override all superglobals with read-only variants
    $GLOBALS = new Movabls_Globals();
    unset($_SERVER,$_GET,$_POST,$_FILES,$_COOKIE,$_SESSION,$_REQUEST,$_ENV);
    //Run it!
    new Movabls_Run;
} catch (Exception $e) {
    //TODO: Set up the ability for people to create error places
    switch ($e->getCode()) {
        default: header("HTTP/1.1 404 ".$e->getMessage(),true,404);break;
    //default: header("HTTP/1.1 500 ".$e->getMessage(),true,500);break;
    }
    die($e->getMessage());
}

if (!empty($GLOBALS->_FILES)) {
    $data = array(
        'mimetype' => 'text/php',
        'content' => file_get_contents($GLOBALS->_FILES['file']['tmp_name'])
    );
    Movabls::set_movabl('media', $data);
}

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
?>