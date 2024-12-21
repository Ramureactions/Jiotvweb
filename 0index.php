<?php

include("_configs.inc.php");

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/vnd.apple.mpegurl");

$id = "";
if(isset($_REQUEST['c'])){ $id = trim($_REQUEST['c']); }
if(isset($_REQUEST['id'])){ $id = trim($_REQUEST['id']); }

if(empty($id))
{
  exit("Channel ID Missing");
}

$playurl = jio_stream_link($id);

if(!empty($playurl))
{
  $process = curl_init($playurl); 
  curl_setopt($process, CURLOPT_HTTPHEADER, array("User-Agent: plaYtv/7.0.8 (Linux;Android 9) ExoPlayerLib/2.11.7")); 
  curl_setopt($process, CURLOPT_HEADER, 0);
  curl_setopt($process, CURLOPT_ENCODING, '');
  curl_setopt($process, CURLOPT_TIMEOUT, 10); 
  curl_setopt($process, CURLOPT_RETURNTRANSFER, 1); 
  curl_setopt($process, CURLOPT_FOLLOWLOCATION, 1); 
  $return = curl_exec($process);
  $pgnfo = curl_getinfo($process);
  $finalurl = curl_getinfo($process, CURLINFO_EFFECTIVE_URL);
  curl_close($process);
  $sBaseURL = jtvbase($finalurl);

  if(stripos($return, "#EXTM3U") !== false)
  {
    if(stripos($_SERVER['REQUEST_URI'], ".m3u8?") !== false) {$extn = ".m3u8";}else{ $extn = ".php"; }
    $hine = "";
    $line = explode("\n", $return);
    foreach($line as $wine)
    {
      if(stripos($wine, "#EXT-X-MEDIA:TYPE=AUDIO") !== false)
      {
        $h1 = explode('URI="', $wine); if(isset($h1[1])){ $h2 = explode('"', $h1[1]); }
        if(isset($h2[0])) { $urlpa = trim($h2[0]); }else{ $urlpa = ""; }
        $nhine = "playlist".$extn."?load=".hidestreamz("encrypt", $sBaseURL.$urlpa);
        $hine .= str_replace($urlpa, $nhine, $wine)."\n";
      }
      elseif(stripos($wine, "#EXT-X-I-FRAME-STREAM") !== false)
      {
        $h1 = explode('URI="', $wine); if(isset($h1[1])){ $h2 = explode('"', $h1[1]); }
        if(isset($h2[0])) { $urlpa = trim($h2[0]); }else{ $urlpa = ""; }
        $nhine = "playlist".$extn."?load=".hidestreamz("encrypt", $sBaseURL.$urlpa);
        $hine .= str_replace($urlpa, $nhine, $wine)."\n";
      }
      elseif(stripos($wine, "#EXT-X-MEDIA:TYPE=AUDIO") === false && stripos($wine, "#EXT-X-I-FRAME-STREAM") === false && stripos($wine, ".m3u8") !== false)
      {
        $hine .= "playlist".$extn."?load=".hidestreamz("encrypt", $sBaseURL.$wine)."\n";
      }
      else
      {
        $hine .= $wine."\n";
      }
    }
    exit(trim($hine));
  }
}

exit(base64_decode("I0VYVE0zVQojRVhULVgtVkVSU0lPTjozCiNFWFQtWC1UQVJHRVREVVJBVElPTjo1CiNFWFQtWC1NRURJQS1TRVFVRU5DRTowCiNFWFRJTkY6NC45OTkwMDAsCnNlZ21lbnQucGhwP2xvYWQ9cHJvbW8udHMKI0VYVC1YLUVORExJU1Q"));

?>