<?php
/*
 *Name         : status.php
 *Author       : Michael Yara
 *Created      : June 15, 2008
 *Last Updated : Feb 14, 2012
 *History      : 0.1 Beta  
 *Purpose      : Contains an example of updating elements in the configuration tree(This example is based off of elements created in build.php). 
 *Copyright    : 2008 HEHE Enterprises, LLC
 */

error_reporting(E_ALL);

include("../isymphony.php");

$isymphony = new iSymphony("localhost");

if(!$isymphony->iSymphonyConnect()) {
	echo iSymphony::$ISERROR;
	die;
}

//Update server config
echo "Updating server<br>";
if(($object = $isymphony->getISymphonyServer()) !== false) {
	$object->username = "modified";
	
	if($object->update()) {
		echo "Update successful<br>";
	} else {
		echo iSymphony::$ISERROR;
		die;
	}
} else {
	echo iSymphony::$ISERROR;
	die;
}

//Update location default config
echo "Updating location default<br>";
if(($object = $isymphony->getISymphonyLocation("default")) !== false) {
	$object->asterisk_login = "modified";
	$object->asterisk_password = "modified";
	$object->asterisk_host = "modified";
	
	if($object->update()) {
		echo "Update successful<br>";
	} else {
		echo iSymphony::$ISERROR;
		die;
	}
} else {
	echo iSymphony::$ISERROR;
	die;
}

//Update tenant default config
echo "Updating tenant default<br>";
if(($object = $isymphony->getISymphonyTenant("default","default")) !== false) {
	$object->originating_context = "modified";
	$object->redirecting_context = "modified";
	
	if($object->update()) {
		echo "Update successful<br>";
	} else {
		echo iSymphony::$ISERROR;
		die;
	}
} else {
	echo iSymphony::$ISERROR;
	die;
}

//Update extension 100 config
echo "Updating extension 100<br>";
if(($object = $isymphony->getISymphonyExtension("default","default","100")) !== false) {
	$object->name = "modified";
	
	if($object->update()) {
		echo "Update successful<br>";
	} else {
		echo iSymphony::$ISERROR;
		die;
	}
} else {
	echo iSymphony::$ISERROR;
	die;
}


if(!$isymphony->iSymphonyDisconnect()) {
	echo iSymphony::$ISERROR;
	die;
}
?>