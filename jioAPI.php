<?php

include("_configs.inc.php");


//------------------------------------------------------------------------//

$action = "";
if(isset($_REQUEST['action']))
{
    $action = trim($_REQUEST['action']);
}

if($action == "channels")
{
    $items = array();
    $tvsuns = jtv_channels();
    if(isset($tvsuns[0]))
    {
        $items = $tvsuns;
        response("success", 200, "OK", array("count" => count($items), "list" => $items));
    }
    else
    {
        response("error", 404, "No Channels Found", "");
    }
}
else
{
    response("error", 500, "Please Provide Valid Action To Execute", "");
}



?>