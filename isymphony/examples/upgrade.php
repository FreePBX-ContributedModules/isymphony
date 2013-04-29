<?php
/*
 *Name         : upgrade.php
 *Author       : Michael Yara
 *Created      : Dec 2, 2009
 *Last Updated : Feb 14, 2012
 *History      : 0.1  
 *Purpose      : Contains an example of upgrading the server. 
 *Copyright    : 2009 HEHE Enterprises, LLC
 */

error_reporting(E_ALL);

include("../isymphony.php");

$isymphony = new iSymphony("localhost");

if(!$isymphony->iSymphonyConnect()) {
	echo iSymphony::$ISERROR;
	die;
}

//Check for available update
echo "Checking for upgrade<br>";
if(($return = $isymphony->checkForiSymphonyUpdates()) !== false) {

	//Check return value to see if an update is available. If so run the update.
	if(strpos("Update Available", $return) === 0) {
		echo "Update available: Updating now<br>";
		
		//Perform update on server
		$isymphony->iSymphonyUpdate();
		
		//Check server update status every second for 5 seconds
		for($i = 0; $i < 5; $i++) {
			
			sleep(1);
			
			if(($object = $isymphony->iSymphonyGetUpdateState()) !== false) {
				var_dump($object);
			} else {
				echo iSymphony::$ISERROR;
				die;
			}
		}
	} else {
		echo "No Update available<br>";
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