<?php

include("_configs.inc.php");

header("Access-Control-Allow-Origin: *");

$load = ""; $playurl = "";
if(isset($_REQUEST['load'])) {
    $load = trim($_REQUEST['load']);
}

if(empty($load)) {
    exit("Payload Missing");
}

if($load == "promo.ts")
{
    exit(@file_get_contents("jiotvmini.ts"));
}

$iload = hidestreamz("decrypt", $load);
if(filter_var($iload, FILTER_VALIDATE_URL))
{
    header("Content-Type: video/mp2t");
    $playurl = $iload;
}

if(empty($playurl)) {
    exit("Payload Invalid");
}

$playurl = $playurl."?".jio_livetoken("144");
$process = curl_init($playurl);
curl_setopt($process, CURLOPT_HTTPHEADER, array("User-Agent: plaYtv/7.0.8 (Linux;Android 9) ExoPlayerLib/2.11.7"));
curl_setopt($process, CURLOPT_HEADER, 0);
curl_setopt($process, CURLOPT_TIMEOUT, 10);
curl_setopt($process, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($process, CURLOPT_FOLLOWLOCATION, 1);
$return = curl_exec($process);
$httpcode = curl_getinfo($process, CURLINFO_HTTP_CODE);
curl_close($process);

if($httpcode == 200 ||  $httpcode == 206)
{
    header("Content-Type: video/mp2t");
    exit($return);
}
else
{
    http_response_code(404);
    exit();
}


?>