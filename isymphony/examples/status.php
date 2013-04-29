<?php
/*
 *Name         : status.php
 *Author       : Michael Yara
 *Created      : June 15, 2008
 *Last Updated : Feb 14, 2012
 *History      : 0.1 Beta  
 *Purpose      : Contains an example of updating and querying user status(This example is based off of elements created in build.php). 
 *Copyright    : 2008 HEHE Enterprises, LLC
 */

error_reporting(E_ALL);

include("../isymphony.php");

$isymphony = new iSymphony("localhost");

if(!$isymphony->iSymphonyConnect()) {
	echo iSymphony::$ISERROR;
	die;
}

echo "Setting status on extension 100<br>";
if(!$isymphony->setISymphonyExtensionStatus("default","default","100","Out to lunch")) {
	echo iSymphony::$ISERROR;
	die;	
}

echo "Setting note on extension 100<br>";
if(!$isymphony->setISymphonyExtensionNote("default","default","100","This is my note for extension 100.")) {
	echo iSymphony::$ISERROR;
	die;	
}

echo "Setting return time on extension 100<br>";
if(!$isymphony->setISymphonyExtensionReturnTime("default","default","100",strtotime("now"))) {
	echo iSymphony::$ISERROR;
	die;	
}

echo "Current status for extension 100=";
if(($reply = $isymphony->getISymphonyExtensionStatus("default","default","100")) !==false) {
	echo $reply . "<br>";
} else {
	echo iSymphony::$ISERROR;
	die;
}

echo "Current note for extension 100=";
if(($reply = $isymphony->getISymphonyExtensionNote("default","default","100")) !==false) {
	echo $reply . "<br>";
} else {
	echo iSymphony::$ISERROR;
	die;
}

echo "Current return time for extension 100=";
if(($reply = $isymphony->getISymphonyExtensionReturnTime("default","default","100")) !==false) {
	echo date("F j, Y, g:i a",$reply) . "<br>";
} else {
	echo iSymphony::$ISERROR;
	die;
}

if(!$isymphony->iSymphonyDisconnect()) {
	echo iSymphony::$ISERROR;
	die;
}