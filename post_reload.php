<?php
/*
 *Name         : post_reload.php
 *Author       : Michael Yara
 *Created      : June 27, 2008
 *Last Updated : July 28, 2008
 *History      : 0.2
 *Purpose      : Script that is called once FreePBX dial plan reload has occurred to reload the iSymphony server. Called via the POST_RELOAD variable in amportal.conf.
 *Copyright    : 2008 HEHE Enterprises, LLC
 */

//Include iSymphony library
require_once(dir(__FILE__)."/isymphony/isymphony.php");
//Connect to iSymphony server and submit a reload command
$isymphony = new iSymphony();
if($isymphony) {
	$isymphony->reloadISymphonyServer();
}
?>