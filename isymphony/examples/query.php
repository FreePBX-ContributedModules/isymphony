<?php
/*
 *Name         : query.php
 *Author       : Michael Yara
 *Created      : June 15, 2008
 *Last Updated : Feb 14, 2012
 *History      : 0.2
 *Purpose      : Contains an example of querying some of the configuration tree and printing. 
 *Copyright    : 2008 HEHE Enterprises, LLC
 */

error_reporting(E_ALL);

include("../isymphony.php");

$isymphony = new iSymphony("localhost");

if(!$isymphony->iSymphonyConnect()) {
	echo iSymphony::$ISERROR;
	die;
}

echo "(Server)<br>";
if(($object = $isymphony->getISymphonyServer()) !== false) {
	var_dump($object);
} else {
	echo iSymphony::$ISERROR;
	die;
}

//Locations-------------------------------------------------
if(($locarray = $isymphony->getISymphonyLocationList()) !== false) {
	
	foreach($locarray as $locval) {
		
		if(($locobject = $isymphony->getISymphonyLocation($locval)) !== false) {
			
			echo "Location \"$locval\"<br>";
			var_dump($locobject);
			
			//Tenants-----------------------------------------------
			if(($tenarray = $isymphony->getISymphonyTenantList($locval)) !== false) {
				
				foreach($tenarray as $tenval) {
					
					if(($tenobject = $isymphony->getISymphonyTenant($locval, $tenval)) !== false) {
						
						echo "Tenant \"$tenval\"<br>";
						var_dump($tenobject);
						
						//Extensions-------------------------------------------------
						if(($extarray = $isymphony->getISymphonyExtensionList($locval,$tenval)) !== false) {
							
							foreach($extarray as $extval) {
								
								if(($extobject = $isymphony->getISymphonyExtension($locval, $tenval, $extval)) !== false) {
									
									echo "Extension \"$extval\"<br>";
									var_dump($extobject);
									
								} else {
									echo iSymphony::$ISERROR;
									die;
								}
							}
						} else {
							echo iSymphony::$ISERROR;
							die;
						}
						
						//Profiles-------------------------------------------------
						if(($proarray = $isymphony->getISymphonyProfileList($locval,$tenval)) !== false) {
							
							foreach($proarray as $proval) {
								
								if(($proobject = $isymphony->getISymphonyProfile($locval, $tenval, $proval)) !== false) {
									
									echo "Profile \"$proval\"<br>";
									var_dump($proobject);
									
								} else {
									echo iSymphony::$ISERROR;
									die;
								}
							}
						} else {
							echo iSymphony::$ISERROR;
							die;
						}
						
						//Queues-------------------------------------------------
						if(($quearray = $isymphony->getISymphonyQueueList($locval,$tenval)) !== false) {
							
							foreach($quearray as $queval) {
								
								if(($queobject = $isymphony->getISymphonyQueue($locval, $tenval, $queval)) !== false) {
									
									echo "Queue \"$queval\"<br>";
									var_dump($queobject);
									
								} else {
									echo iSymphony::$ISERROR;
									die;
								}
							}
						} else {
							echo iSymphony::$ISERROR;
							die;
						}
						
						//Conference rooms-------------------------------------------------
						if(($confarray = $isymphony->getISymphonyConferenceRoomList($locval,$tenval)) !== false) {
							
							foreach($confarray as $confval) {
								
								if(($confobject = $isymphony->getISymphonyConferenceRoom($locval, $tenval, $confval)) !== false) {
									
									echo "Room \"$confval\"<br>";
									var_dump($confobject);
									
								} else {
									echo iSymphony::$ISERROR;
									die;
								}
							}
						} else {
							echo iSymphony::$ISERROR;
							die;
						}
					}
				}
			} else {
				echo iSymphony::$ISERROR;
				die;
			}	
		} else {
			echo iSymphony::$ISERROR;
			die;
		}	
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