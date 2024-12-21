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

$iload = hidestreamz("decrypt", $load);
if(filter_var($iload, FILTER_VALIDATE_URL))
{
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
$sunEFURL = curl_getinfo($process, CURLINFO_EFFECTIVE_URL);
curl_close($process);
$sBaseURL = jtvbase($sunEFURL);

if(stripos($return, "#EXTM3U") !== false)
{
    if(stripos($_SERVER['REQUEST_URI'], ".m3u8?") !== false) {$extn = ".ts";}else{ $extn = ".php"; }
    $hine = "";
    $line = explode("\n", $return);
    foreach($line as $wine)
    {
        if(stripos($wine, "#EXT-X-KEY") !== false)
        {
            $h1 = explode('URI="', $wine); if(isset($h1[1])){ $h2 = explode('"', $h1[1]); }
            if(isset($h2[0])) { $urlpa = trim($h2[0]); }else{ $urlpa = ""; }
            $nhine = "key.php?load=".hidestreamz("encrypt", $urlpa);
            $hine .= str_replace($urlpa, $nhine, $wine)."\n";
        }
        elseif(stripos($wine, ".aac") !== false)
        {
            $sBaseURL = str_replace("jiotvmblive.cdn.jio.com", "jiotv.live.cdn.jio.com", $sBaseURL);
            if(isset($WORLDWIDE_PROXY) && $WORLDWIDE_PROXY !== "OFF")
            {
                $hine .= "segment".$extn."?load=".hidestreamz("encrypt", $sBaseURL.$wine)."\n";
            }
            else
            {
                $hine .= $sBaseURL.$wine."?".jiooldtoken()."\n";
            }
        }
        elseif(stripos($wine, ".ts") !== false)
        {
            if(isset($WORLDWIDE_PROXY) && $WORLDWIDE_PROXY !== "OFF")
            {
                $hine .= "segment".$extn."?load=".hidestreamz("encrypt", $sBaseURL.$wine)."\n";
            }
            else
            {
                $sBaseURL = str_replace("jiotvmblive.cdn.jio.com", "jiotv.live.cdn.jio.com", $sBaseURL);
                $hine .= $sBaseURL.$wine."?".jiooldtoken()."\n";
            }
        }
        else
        {
            $hine .= $wine."\n";
        }
    }
    header("Content-Type: application/vnd.apple.mpegurl");
    exit(trim($hine));
}

exit("Playlist Not Found");

?>