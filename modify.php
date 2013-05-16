<?php
/*
 *Name         : modify.php
 *Author       : Michael Yara
 *Created      : May 10, 2011
 *Last Updated : Feb 14, 2012
 *History      : 0.5
 *Purpose      : FreePBX module that automatically updates the iSymphony configuration from the FreePBX configuration.
 *Copyright    : 2011 HEHE Enterprises, LLC
 */

//Check if we need to bootstrap for security
if($argv[9] == "true") {
	if (!@include_once(getenv('FREEPBX_CONF') ? getenv('FREEPBX_CONF') : '/etc/freepbx.conf')) { 
		  include_once('/etc/asterisk/freepbx.conf'); 
	}
	
	if (!defined('FREEPBX_IS_AUTH')) { die('No direct script access allowed');}
}

//Include iSymphony library
require_once(dirname(__FILE__)."/isymphony/isymphony.php");

//Include Pear DB
require_once('DB.php');

//Multiplier usesd to determine the execution timeout based on the number of extensions
$executionTimeoutMultiplier = 3;

//Set execution timeout to a large value so that we can wait for a lock if another instance of the script is running. The timeout will be modified later in the script based on the number of extension elements.
set_time_limit(6000);

//Grab web root
$ampWebroot = $argv[8];

//Open log files
$errorLogFile = fopen($ampWebroot . "/admin/modules/isymphony/error.txt", 'w');
if(($debugLogFile = fopen($ampWebroot . "/admin/modules/isymphony/debug.txt", 'w')) === false) {
	isymphony_modify_write_to_file($errorLogFile, "Could not open debug log for writing.\n");
}

//Attempt to acquire script lock and block until we have it. This prevents multiple instances of this script from running at the same time.
$lock = fopen($ampWebroot . "/admin/modules/isymphony/lock", "r+");
if(!flock($lock, LOCK_EX)) {
	isymphony_modify_write_to_file($errorLogFile, "Failed to acquire script lock.\n");
	fclose($lock);
	die;
}

//Reset execution timeout so that the script does not die if a small amount of time was left after the wait for the script lock. This is to prevent the script from dying without releasing the lock.
set_time_limit(60);

//Create running time tracker 
$runningTimeStart = microtime(true);		

//Grab argumnets
$DBHost = $argv[1];
$DBName = $argv[2];
$DBUser = $argv[3];
$DBPass = $argv[4];
$serverRevision = $argv[5];
$agentLoginContext  = $argv[6];
$agentInterfaceType  = $argv[7];

//Check arguments
if(	$DBHost == null || $DBHost == "" ||
	$DBName == null || $DBName == "" ||
	$DBUser == null || $DBUser == "" ||
	$DBPass == null || $DBPass == "" ||
	$serverRevision == null || $serverRevision == "" ||
	$agentLoginContext == null || $agentLoginContext == "" ||
	$agentInterfaceType == null || $agentInterfaceType == "") {

	isymphony_modify_write_to_file($errorLogFile, "Missing or null argument"  . "\n");
	flock($lock, LOCK_UN);
	fclose($debugLogFile);
	fclose($errorLogFile);
	fclose($lock);
	ob_end_clean();
	die;
}

//Connect to mysql database
$datasource = 'mysql://' . $DBUser . ':' . $DBPass . '@' . $DBHost . '/' . $DBName;
$db = DB::connect($datasource); 
if(DB::isError($db)) {
	isymphony_modify_write_to_file($errorLogFile, "Failed to connect to database:" . $db->getMessage()  . "\n");
	flock($lock, LOCK_UN);
	fclose($debugLogFile);
	fclose($errorLogFile);
	fclose($lock);
	ob_end_clean();
	die;
}

//Grab the location info
if(($locationInformation = isymphony_modify_location_get()) === null) {
	isymphony_modify_write_to_file($errorLogFile, "Failed to query location information:" . $db->getMessage()  . "\n");
	flock($lock, LOCK_UN);
	fclose($debugLogFile);
	fclose($errorLogFile);
	fclose($lock);
	$db->disconnect();
	ob_end_clean();
	die;
}

//Connect to isymphony server
$isymphony = new iSymphony($locationInformation["isymphony_host"]);
if(($isymphony->iSymphonyConnect()) === false) {
	isymphony_modify_write_to_file($errorLogFile, "Failed to connect to isymphony:" . iSymphony::$ISERROR . "\n");
	flock($lock, LOCK_UN);
	fclose($debugLogFile);
	fclose($errorLogFile);
	fclose($lock);
	$db->disconnect();
	ob_end_clean();
	die;
}

//Check if server properties need to be updated.
if(($object = $isymphony->getISymphonyServer()) !== false) {
	
	if(($object->username != $locationInformation["admin_user_name"]) || (($object->password != $locationInformation["admin_password"]))) {
		$object->username = $locationInformation["admin_user_name"];
		$object->password = $locationInformation["admin_password"];
		
		if($object->update()) {
			isymphony_modify_write_to_file($debugLogFile, "Updated server\n");
		} else {
			isymphony_modify_write_to_file($debugLogFile, "(Update server) . iSymphony::$ISERROR . \n");
		}
	}	
} else {
	isymphony_modify_write_to_file($errorLogFile, "(Query server)" . iSymphony::$ISERROR. "\n");
}
			
//Check if default location exists if not create it else update manager connection values to stay consistent
if(($iSymphonyLocations = $isymphony->getISymphonyLocationList()) !== false) {
	
	$maskedAutoReloadFlag = ($locationInformation["auto_reload"] == 1) ? "true" : "false";	
	$maskedLogJabberMessagesFlag = ($locationInformation["log_jabber_messages"] == 1) ? "true" : "false";	
	
	if(!in_array($locationInformation["isymphony_location"], $iSymphonyLocations)) {
		$object = new ISymphonyLocation;
		$object->name = $locationInformation["isymphony_location"];
		$object->admin_password = "secret";
		$object->asterisk_host = $locationInformation["asterisk_host"];
		$object->asterisk_port = "5038";
		$object->asterisk_login = "isymphony";
		$object->asterisk_password = "ismanager*con";
		$object->originate_timeout = $locationInformation["originate_timeout"];
		$object->jabber_host = $locationInformation["jabber_host"];
		$object->jabber_domain = $locationInformation["jabber_domain"];
		$object->jabber_resource = $locationInformation["jabber_resource"];
		$object->jabber_port = $locationInformation["jabber_port"];
		$object->mask_jabber_user_name_with_profile = "false";
		$object->log_jabber_messages = $maskedLogJabberMessagesFlag;
		$object->device_user_mode = "true";
		$object->reload_on_dial_plan_reload = $maskedAutoReloadFlag;
		$object->force_client_update = "true";
		$object->voice_mail_directory = "/var/spool/asterisk/voicemail";
		$object->conf_bridge = ($amp_conf['ASTCONFAPP'] == 'app_confbridge') ? "true" : "false";
		
		if($object->add()) {
			isymphony_modify_write_to_file($debugLogFile, "Added location default\n");
		} else {
			isymphony_modify_write_to_file($errorLogFile, "(Add location)" . iSymphony:: $ISERROR . "\n");
		}
	} else {
		if(($object = $isymphony->getISymphonyLocation($locationInformation["isymphony_location"])) !== false) {
			
			//Check if update needs to occur		
			if(isymphony_modify_location_update_check($serverRevision, $locationInformation, $maskedAutoReloadFlag, $maskedLogJabberMessagesFlag, $object)) {	
				
				$object->asterisk_host = $locationInformation["asterisk_host"];
				$object->asterisk_port = "5038";
				$object->asterisk_login = "isymphony";
				$object->asterisk_password = "ismanager*con";
				$object->originate_timeout = $locationInformation["originate_timeout"];
				$object->jabber_host = $locationInformation["jabber_host"];
				$object->jabber_domain = $locationInformation["jabber_domain"];
				$object->jabber_resource = $locationInformation["jabber_resource"];
				$object->jabber_port = $locationInformation["jabber_port"];
				$object->log_jabber_messages = $maskedLogJabberMessagesFlag;
				$object->device_user_mode = "true";
				$object->reload_on_dial_plan_reload = $maskedAutoReloadFlag;
				$object->conf_bridge = ($amp_conf['ASTCONFAPP'] == 'app_confbridge') ? "true" : "false";
				
				if($object->update()) {
					isymphony_modify_write_to_file($debugLogFile, "Updated location default\n");
				} else {
					isymphony_modify_write_to_file($errorLogFile, "(Update location)" . iSymphony::$ISERROR . "\n");	
				}
			}	
		} else {
			isymphony_modify_write_to_file($errorLogFile, "(Query location)" . iSymphony::$ISERROR . "\n");
		}
	}
} else {
	isymphony_modify_write_to_file($errorLogFile, "(List locations)" . iSymphony::$ISERROR . "\n");
}
			
//Check if default tenant exists if not create it else update context values to stay consistent
if(($iSymphonyTenants = $isymphony->getISymphonyTenantList($locationInformation["isymphony_location"])) !== false) {
	
	//Mask auto answer flag
	$maskedPagedStatusEnabledFlag = ($locationInformation["page_status_enabled"] == 1) ? "true" : "false";	
	$recordFileNameMask = ($serverRevision >= 1520) ? "%EXT%-%DATE_TIME_FORMAT[yyyyMMdd-HHmmss]%-%ID%-%LINKED_ID%" : "%EXT%-%NAME%-%CID_NAME%-%CID_NUMBER%-%DATE%-%TIME%";
	
	if(!in_array($locationInformation["isymphony_tenant"], $iSymphonyTenants)) {
		$object = new ISymphonyTenant;
		$object->name = $locationInformation["isymphony_tenant"];
		$object->admin_password = "secret";
		$object->originating_context = "c-x-operator-panel-orig-proxy";
		$object->redirecting_context = "from-internal";
		$object->agent_login_context = $agentLoginContext;
		$object->music_on_hold_class = "default";
		$object->page_status_enabled = $maskedPagedStatusEnabledFlag;
		$object->page_context = "ext-paging";
		$object->outside_line_number = "";
		$object->record_file_name = $recordFileNameMask;
		$object->record_file_extension = "wav";
		$object->mix_mode = "true";
		if($object->add($locationInformation["isymphony_location"])) {
			isymphony_modify_write_to_file($debugLogFile, "Added tenant default\n");
		} else {
			isymphony_modify_write_to_file($errorLogFile, "(Add tenant)" . iSymphony::$ISERROR . "\n");
		}
	} else {
		if(($object = $isymphony->getISymphonyTenant($locationInformation["isymphony_location"], $locationInformation["isymphony_tenant"])) !== false) {
			
			//Check if update needs to occur
			if(isymphony_modify_tenant_update_check($serverRevision, $locationInformation, $maskedPagedStatusEnabledFlag, $agentLoginContext, $object)) {
				
				$object->originating_context = "c-x-operator-panel-orig-proxy";
				$object->redirecting_context = "from-internal";
				$object->agent_login_context = $agentLoginContext;
				$object->page_status_enabled = $maskedPagedStatusEnabledFlag;
				$object->page_context = "ext-paging";
				$object->record_file_name = $recordFileNameMask;
				
				if($object->update()) {
					isymphony_modify_write_to_file($debugLogFile, "Updated tenant default\n");
				} else {
					isymphony_modify_write_to_file($errorLogFile, "(Update tenant)" . iSymphony::$ISERROR . "\n");	
				}	
			}
		} else {
			isymphony_modify_write_to_file($errorLogFile, "(Query tenant)" . iSymphony::$ISERROR . "\n");
		}
	}
} else {
	isymphony_modify_write_to_file($errorLogFile, "(List tenants)" . iSymphony::$ISERROR . "\n");
}

//Grab list of configured extensions from the database	
$freePBXTempUsers = isymphony_modify_user_list();
		
//Set execution timeout based on the number of extension elements
set_time_limit(30 + (count($freePBXTempUsers) * $executionTimeoutMultiplier));

//Filter list to exclude extensions that are not marked for addition
$freePBXUsers = array();
foreach($freePBXTempUsers as $freePBXTempUser) {
	if($freePBXTempUser["add_extension"] == "1") {
		array_push($freePBXUsers, $freePBXTempUser);
	}
}
		
//Filter the previous list for profiles excluding ones not marked for profile addition
$freePBXProfiles = array();
foreach($freePBXUsers as $freePBXUser) {
	if($freePBXUser["add_profile"] == "1") {
		array_push($freePBXProfiles, $freePBXUser);
	}
}

//Add, edit and remove extensions and profiles
if((($iSymphonyExtensions = $isymphony->getISymphonyExtensionList($locationInformation["isymphony_location"], $locationInformation["isymphony_tenant"])) !== false) 
	&& (($iSymphonyProfiles = $isymphony->getISymphonyProfileList($locationInformation["isymphony_location"], $locationInformation["isymphony_tenant"])) !== false)) {
			
	//Build array of freePBX extensions to compare to iSymphony list for deletes 
	$freePBXExtensionCheckDeleteArray = array();
	foreach($freePBXUsers as $freePBXUser) {
		array_push($freePBXExtensionCheckDeleteArray, $freePBXUser["user_id"]);
	}
				
	//Delete appropriate extensions
	$deleteExtensionArray = array_diff($iSymphonyExtensions, $freePBXExtensionCheckDeleteArray);
	foreach($deleteExtensionArray as $val) {
		if($isymphony->removeISymphonyExtension($locationInformation["isymphony_location"], $locationInformation["isymphony_tenant"], $val)) {
			isymphony_modify_write_to_file($debugLogFile, "Deleted extension {$val}\n");
			$iSymphonyExtensions = isymphony_modify_remove_array_item($iSymphonyExtensions, $val);
		} else {
			isymphony_modify_write_to_file($errorLogFile, "(Delete extension)" . iSymphony::$ISERROR . "\n");
		}
	}
							
	//Add and update extensions
	foreach($freePBXUsers as $freePBXUser) {
	
		//Mask auto answer flag
		$maskedAutoAnswerFlag = ($freePBXUser["auto_answer"] == 1) ? "true" : "false";
		
		//Build agent login interface for extension
		$agentLoginInterface = "";
		switch($agentInterfaceType) {
			case "peer":
				$agentLoginInterface = $freePBXUser["peer"];
				break;
			case "hint":
				$agentLoginInterface = ($serverRevision >= "2223") ? ("Hint:" . $freePBXUser["user_id"] . "@ext-local") : ($freePBXUser["user_id"] . "@ext-local");
				break;
			case "none";	
				$agentLoginInterface = "";
				break;	
		}
					
		//If extension does not exist add it else update it
		if(!in_array($freePBXUser["user_id"], $iSymphonyExtensions)) {
						
			//Add extension
			$object = new ISymphonyExtension;
			$object->extension_val = $freePBXUser["user_id"];
			$object->name = $freePBXUser["display_name"];
			$object->peer = $freePBXUser["peer"];
			$object->alt_origination_method = "";
			$object->voice_mail = $freePBXUser["user_id"];
			$object->cell_phone = $freePBXUser["cell_phone"];
			$object->email = $freePBXUser["email"];
			$object->auto_answer = $maskedAutoAnswerFlag;
			$object->agent_login_interface = $agentLoginInterface;
			$object->agent_login_name = $freePBXUser["display_name"];
			$object->agent_login_penalty = "0";
						
			if($object->add($locationInformation["isymphony_location"], $locationInformation["isymphony_tenant"])) {
				isymphony_modify_write_to_file($debugLogFile, "Added extension {$freePBXUser['user_id']}\n");
			} else {
				isymphony_modify_write_to_file($errorLogFile, "(Add extension)" . iSymphony::$ISERROR . "\n");
			}
		} else {
					
			//Query extension configuration and update values
			if(($object = $isymphony->getISymphonyExtension($locationInformation["isymphony_location"], $locationInformation["isymphony_tenant"], $freePBXUser["user_id"])) !== false) {		
									
				//Check if update needs to occur
				if(isymphony_modify_extension_update_check($serverRevision, $freePBXUser, $maskedAutoAnswerFlag, $agentLoginInterface, $object)) {
							
					$object->name = $freePBXUser["display_name"];
					$object->voice_mail = $freePBXUser["user_id"];
					$object->peer = $freePBXUser["peer"];
					$object->cell_phone = $freePBXUser["cell_phone"];
					$object->email = $freePBXUser["email"];
					$object->auto_answer = $maskedAutoAnswerFlag;
					$object->agent_login_interface = $agentLoginInterface;
					$object->agent_login_name = $freePBXUser["display_name"];
					
					if($object->update()) {
						isymphony_modify_write_to_file($debugLogFile, "Updated extension {$freePBXUser['user_id']}\n");
					} else {
						isymphony_modify_write_to_file($errorLogFile, "(Update extension)" . iSymphony::$ISERROR . "\n");	
					}	
				}
			} else {
				isymphony_modify_write_to_file($errorLogFile, "(Query extension)" . iSymphony::$ISERROR . "\n"); 
			}
		}
	}
						
	//Build array of freePBX extensions to compare to iSymphony list for deletes 
	$freePBXProfileCheckDeleteArray = array();
	foreach($freePBXProfiles as $freePBXProfile) {
		array_push($freePBXProfileCheckDeleteArray, $freePBXProfile["user_id"]);
	}
					
	//Delete appropriate profiles
	$deleteProfileArray = array_diff($iSymphonyProfiles, $freePBXProfileCheckDeleteArray);
	foreach($deleteProfileArray as $val) {
		if($isymphony->removeISymphonyProfile($locationInformation["isymphony_location"], $locationInformation["isymphony_tenant"], $val)) {
			isymphony_modify_write_to_file($debugLogFile, "Deleted profile {$val}\n");
			$iSymphonyProfiles = isymphony_modify_remove_array_item($iSymphonyProfiles, $val);
		} else {
			isymphony_modify_write_to_file($errorLogFile, "(Delete profile)" . iSymphony::$ISERROR . "\n");
		}
	}	
						
	//Add and update profiles
	foreach($freePBXProfiles as $freePBXProfile) {
				
		//If profile does not exist add it else update it
		if(!in_array($freePBXProfile["user_id"], $iSymphonyProfiles)) {
					
			//Add profile
			$object = new ISymphonyProfile;
			$object->name = $freePBXProfile["user_id"];
			$object->password = $freePBXProfile["password"];
			$object->can_view_everyone_directory = "true";
			$object->jabber_host = $freePBXProfile["jabber_host"];
			$object->jabber_domain = $freePBXProfile["jabber_domain"];     
			$object->jabber_resource = $freePBXProfile["jabber_resource"];        
			$object->jabber_port = $freePBXProfile["jabber_port"]; 
			$object->jabber_user_name = $freePBXProfile["jabber_user_name"];
			$object->jabber_password = $freePBXProfile["jabber_password"];	
							
			if($object->add($locationInformation["isymphony_location"], $locationInformation["isymphony_tenant"])) {
				isymphony_modify_write_to_file($debugLogFile, "Added profile {$freePBXProfile['user_id']}\n");
			} else {
				isymphony_modify_write_to_file($errorLogFile, "(Add profile)" . iSymphony::$ISERROR . "\n");
			}
	
			//Add extension to profiles managed list
			if($isymphony->addISymphonyProfileManagedExtension($locationInformation["isymphony_location"], $locationInformation["isymphony_tenant"],$freePBXProfile["user_id"],$freePBXProfile["user_id"])) {
				isymphony_modify_write_to_file($debugLogFile, "Added profile managed extension {$freePBXProfile['user_id']}\n");
			} else {
				isymphony_modify_write_to_file($errorLogFile, "(Add profile managed extension)" . iSymphony::$ISERROR . "\n");
			}
		} else {
							
			//Query profile configuration and update values
			if(($object = $isymphony->getISymphonyProfile($locationInformation["isymphony_location"], $locationInformation["isymphony_tenant"], $freePBXProfile["user_id"])) !== false) {		
								          
				//If jabber port is set to blank in the db rewrite with a -1 to prevent this profile from always being updated. Since the server will store a -1 as the port value if none is set.					
				$maskedJabberPort = ($object->jabber_port == -1) ? "" : $object->jabber_port; 
														                 
				//Check if update needs to occur
				if(isymphony_modify_profile_update_check($serverRevision, $freePBXProfile, $maskedJabberPort, $object)) {                  
											
					$object->password = $freePBXProfile["password"];
					$object->jabber_host = $freePBXProfile["jabber_host"];
					$object->jabber_domain = $freePBXProfile["jabber_domain"];     
					$object->jabber_resource = $freePBXProfile["jabber_resource"];        
					$object->jabber_port = $freePBXProfile["jabber_port"]; 
					$object->jabber_user_name = $freePBXProfile["jabber_user_name"];
					$object->jabber_password = $freePBXProfile["jabber_password"];	
										
					if($object->update()) {
						isymphony_modify_write_to_file($debugLogFile, "Updated profile {$freePBXProfile['user_id']}\n");
					} else {
						isymphony_modify_write_to_file($errorLogFile, "(Update profile)" . iSymphony::$ISERROR . "\n");	
					}	
				}
									
				//Attempt to add relative extension to profile
				if($isymphony->addISymphonyProfileManagedExtension($locationInformation["isymphony_location"], $locationInformation["isymphony_tenant"],$freePBXProfile["user_id"],$freePBXProfile["user_id"])) {
					isymphony_modify_write_to_file($debugLogFile, "Added profile managed extension {$freePBXProfile['user_id']}\n");
				}
			} else {
				isymphony_modify_write_to_file($errorLogFile, "(Query profile)" . iSymphony::$ISERROR . "\n"); 
			}
		}												
	}
} else {
	isymphony_modify_write_to_file($errorLogFile, "(List extensions/profiles)" . iSymphony::$ISERROR . "\n");
}	
				
//Grab list of configured queues from the database	
$freePBXTempQueues = isymphony_modify_queue_list();

//Filter list to exclude queues that are not marked for addition
$freePBXQueues = array();
foreach($freePBXTempQueues as $freePBXTempQueue) {
	if($freePBXTempQueue["add_queue"] == "1") {
		array_push($freePBXQueues, $freePBXTempQueue);
	}
}

//Add, edit and remove queues
if(($iSymphonyQueues = $isymphony->getISymphonyQueueList($locationInformation["isymphony_location"], $locationInformation["isymphony_tenant"])) !== false) {
	
	//Build array of freePBX queues to compare to iSymphony list for deletes 
	$freePBXQueueCheckDeleteArray = array();
	foreach($freePBXQueues as $freePBXQueue) {
		array_push($freePBXQueueCheckDeleteArray, $freePBXQueue["display_name"]);
	}
		 	
 	//Delete appropriate queues
	$deleteQueueArray = array_diff($iSymphonyQueues, $freePBXQueueCheckDeleteArray);
	foreach($deleteQueueArray as $val) {
		if($isymphony->removeISymphonyQueue($locationInformation["isymphony_location"], $locationInformation["isymphony_tenant"], $val)) {
			isymphony_modify_write_to_file($debugLogFile, "Deleted queue {$val}\n");
			$iSymphonyQueues = isymphony_modify_remove_array_item($iSymphonyQueues, $val);
		} else {
			isymphony_modify_write_to_file($errorLogFile, "(Delete queue)" . iSymphony::$ISERROR . "\n");
		}
	}
					
	//Add and update queues
 	foreach($freePBXQueues as $freePBXQueue) {
		 					 		
 		//If queue does not exist add it
		if(!in_array($freePBXQueue["display_name"], $iSymphonyQueues)) {
			
			//Add queue
			$object = new ISymphonyQueue;
			$object->name = $freePBXQueue["display_name"];
			$object->queue_val = $freePBXQueue["queue_id"];
			$object->extension_val = $freePBXQueue["queue_id"];
			$object->context = "from-internal";

			if($object->add($locationInformation["isymphony_location"], $locationInformation["isymphony_tenant"])) {
				isymphony_modify_write_to_file($debugLogFile, "Added queue {$freePBXQueue['display_name']}\n");
			} else {
				isymphony_modify_write_to_file($errorLogFile, "(Add queue)" . iSymphony::$ISERROR . "\n");
			}
					
		//If queue does exist update it		
		} else {
					
			//Query queue configuration and update values
			if(($object = $isymphony->getISymphonyQueue($locationInformation["isymphony_location"], $locationInformation["isymphony_tenant"], $freePBXQueue["display_name"])) !== false) {		
									
				//Check if update needs to occur
				if(($object->name != $freePBXQueue["display_name"]) || ($object->queue_val != $freePBXQueue["queue_id"]) || ($object->extension_val != $freePBXQueue["queue_id"]) || ($object->context != "from-internal")) {					
									
					$object->name = $freePBXQueue["display_name"];
					$object->queue_val = $freePBXQueue["queue_id"];
					$object->extension_val = $freePBXQueue["queue_id"];
					$object->context = "from-internal";
					
					if($object->update()) {
						isymphony_modify_write_to_file($debugLogFile, "Updated queue {$freePBXQueue['display_name']}\n");
					} else {
						isymphony_modify_write_to_file($errorLogFile, "(Update queue)" . iSymphony::$ISERROR . "\n");	
					}	
				}
			} else {
				isymphony_modify_write_to_file($errorLogFile, "(Query queue)" . iSymphony::$ISERROR . "\n"); 
			}
		}	
 	}				
} else {
	isymphony_modify_write_to_file($errorLogFile, "(List queues)" . iSymphony::$ISERROR . "\n");
}
	
//Grab list of configured conference rooms from the database	
$freePBXTempConferenceRooms = isymphony_modify_conference_room_list();

//Filter list to exclude conference rooms that are not marked for addition
$freePBXConferenceRooms = array();
foreach($freePBXTempConferenceRooms as $freePBXTempConferenceRoom) {
	if($freePBXTempConferenceRoom["add_conference_room"] == "1") {
		array_push($freePBXConferenceRooms, $freePBXTempConferenceRoom);
	}
}
	
//Add, edit and remove conference rooms
if(($iSymphonyConferenceRooms = $isymphony->getISymphonyConferenceRoomList($locationInformation["isymphony_location"], $locationInformation["isymphony_tenant"])) !== false) {
	
	//Build array of freePBX conference rooms to compare to iSymphony list for deletes 
	$freePBXConferenceRoomCheckDeleteArray = array();
	foreach($freePBXConferenceRooms as $freePBXConferenceRoom) {
		array_push($freePBXConferenceRoomCheckDeleteArray, $freePBXConferenceRoom["display_name"]);
	}
 	
 	//Delete appropriate conference rooms
	$deleteConferenceRoomArray = array_diff($iSymphonyConferenceRooms, $freePBXConferenceRoomCheckDeleteArray);
	foreach($deleteConferenceRoomArray as $val) {
		if($isymphony->removeISymphonyConferenceRoom($locationInformation["isymphony_location"], $locationInformation["isymphony_tenant"], $val)) {
			isymphony_modify_write_to_file($debugLogFile, "Deleted conference room {$val}\n");
			$iSymphonyConferenceRooms = isymphony_modify_remove_array_item($iSymphonyConferenceRooms, $val);
		} else {
			isymphony_modify_write_to_file($errorLogFile, "(Delete conference room)" . iSymphony::$ISERROR . "\n");
		}
	}
					
	//Add and update conference rooms
 	foreach($freePBXConferenceRooms as $freePBXConferenceRoom) {
 					 		
 		//If conference room does not exist add it
		if(!in_array($freePBXConferenceRoom["display_name"], $iSymphonyConferenceRooms)) {
			
			//Add conference room
			$object = new ISymphonyConferenceRoom;
			$object->name = $freePBXConferenceRoom["display_name"];
			$object->predefined = "true";
			$object->room_number = $freePBXConferenceRoom["conference_room_id"];
			$object->extension_val = $freePBXConferenceRoom["conference_room_id"];
			$object->context = "from-internal";
			$object->announce_user_count = "false";
			$object->music_on_hold_for_single_user = "false";
			$object->exit_room_via_pound = "false";
			$object->present_menu_via_star = "false";
			$object->announce_user_join_leave = "false";
			$object->disable_join_leave_notification = "false";
			$object->record = "false";

			if($object->add($locationInformation["isymphony_location"], $locationInformation["isymphony_tenant"])) {
				isymphony_modify_write_to_file($debugLogFile, "Added conference room {$freePBXConferenceRoom['display_name']}\n");
			} else {
				isymphony_modify_write_to_file($errorLogFile, "(Add conference room)" . iSymphony::$ISERROR . "\n");
			}
							
		//If conference room does exist update it		
		} else {
			
			//Query conference room configuration and update values
			if(($object = $isymphony->getISymphonyConferenceRoom($locationInformation["isymphony_location"], $locationInformation["isymphony_tenant"], $freePBXConferenceRoom["display_name"])) !== false) {		
								
				//Check if update needs to occur
				if(($object->name != $freePBXConferenceRoom["display_name"]) || ($object->predefined != "true") || ($object->room_number != $freePBXConferenceRoom["conference_room_id"]) || ($object->extension_val != $freePBXConferenceRoom["conference_room_id"]) || ($object->context != "from-internal")) {
						
					$object->name = $freePBXConferenceRoom["display_name"];
					$object->predefined = "true";
					$object->room_number = $freePBXConferenceRoom["conference_room_id"];
					$object->extension_val = $freePBXConferenceRoom["conference_room_id"];
					$object->context = "from-internal";
					
					if($object->update()) {
						isymphony_modify_write_to_file($debugLogFile, "Updated conference room {$freePBXConferenceRoom['display_name']}\n");
					} else {
						isymphony_modify_write_to_file($errorLogFile, "(Update conference room)" . iSymphony::$ISERROR . "\n");	
					}
				}	
			} else {
				isymphony_modify_write_to_file($errorLogFile, "(Query conference room)" . iSymphony::$ISERROR . "\n"); 
			}
		}	
 	}				
} else {
	isymphony_modify_write_to_file($errorLogFile, "(List conference rooms)" . iSymphony::$ISERROR . "\n");
}	
	
//Close iSymphony connection
$isymphony->iSymphonyDisconnect();
					
$runningTimeStop = microtime(true);
isymphony_modify_write_to_file($debugLogFile, "Total Running Time:" . ($runningTimeStop - $runningTimeStart) . "s\n");
		
//Close DB
$db->disconnect();

//Release script lock
flock($lock, LOCK_UN);

//Close file handlers
fclose($debugLogFile);
fclose($errorLogFile);
fclose($lock);

//iSymphony module API location functions
function isymphony_modify_location_get() {
	global $db;	
	$query = "SELECT * FROM isymphony_location";
	$results = $db->query($query);
	if((DB::IsError($results)) || (empty($results))) {
		return null;
	} else {
		$results = $results->fetchRow(DB_FETCHMODE_ASSOC);
		return $results;
	}
}

//iSymphony module API user functions
function isymphony_modify_user_list() {
	global $db;	
	$query = "SELECT * FROM isymphony_users";
	$results = $db->getAll($query, array(), DB_FETCHMODE_ASSOC);
	if((DB::IsError($results)) || (empty($results))) {
		return array();
	} else {
		return $results;
	}
}

//iSymphony module API queue functions
function isymphony_modify_queue_list() {
	global $db;	
	$query = "SELECT * FROM isymphony_queues";
	$results = $db->getAll($query, array(), DB_FETCHMODE_ASSOC);
	if((DB::IsError($results)) || (empty($results))) {
		return array();
	} else {
		return $results;
	}
}

//iSymphony module API conference room functions
function isymphony_modify_conference_room_list() {
	global $db;	
	$query = "SELECT * FROM isymphony_conference_rooms";
	$results = $db->getAll($query, array(), DB_FETCHMODE_ASSOC);
	if((DB::IsError($results)) || (empty($results))) {
		return array();
	} else {
		return $results;
	}
}

//Update checks
function isymphony_modify_location_update_check($serverRevision, $databaseValues, $maskedAutoReloadFlag, $maskedLogJabberMessagesFlag, $object) {
	
	//Checks fo revision 4295 and above
	if($serverRevision >= 4295) {
		$conf_bridge = ($amp_conf['ASTCONFAPP'] == 'app_confbridge') ? "true" : "false";
		return (($object->asterisk_host != "localhost") ||
		($object->asterisk_port != "5038") ||
		($object->asterisk_login != "isymphony") ||
		($object->asterisk_password != "ismanager*con") ||
		($object->originate_timeout != $databaseValues["originate_timeout"]) ||
		($object->reload_on_dial_plan_reload != $maskedAutoReloadFlag) ||
		($object->jabber_host != $databaseValues["jabber_host"]) ||
		($object->jabber_domain != $databaseValues["jabber_domain"]) ||
		($object->jabber_resource != $databaseValues["jabber_resource"]) ||
		($object->jabber_port != $databaseValues["jabber_port"]) ||
		($object->device_user_mode != "true") ||
		($object->log_jabber_messages != $maskedLogJabberMessagesFlag) ||
		($object->conf_bridge != $conf_bridge));
	
	//Checks for revision 1736 and 4294
	} else if($serverRevision >= 1736) {
			return (($object->asterisk_host != "localhost") || 
			($object->asterisk_port != "5038") || 
			($object->asterisk_login != "isymphony") || 
			($object->asterisk_password != "ismanager*con") || 
			($object->originate_timeout != $databaseValues["originate_timeout"]) ||
			($object->reload_on_dial_plan_reload != $maskedAutoReloadFlag) ||
			($object->jabber_host != $databaseValues["jabber_host"]) ||
			($object->jabber_domain != $databaseValues["jabber_domain"]) ||
			($object->jabber_resource != $databaseValues["jabber_resource"]) ||
			($object->jabber_port != $databaseValues["jabber_port"]) ||
			($object->device_user_mode != "true") ||
			($object->log_jabber_messages != $maskedLogJabberMessagesFlag));
	
	//Checks for revision 1489-1735
	} else if($serverRevision >= 1489) {
		return (($object->asterisk_host != "localhost") || 
				($object->asterisk_port != "5038") || 
				($object->asterisk_login != "isymphony") || 
				($object->asterisk_password != "ismanager*con") || 
				($object->originate_timeout != $databaseValues["originate_timeout"]) ||
				($object->reload_on_dial_plan_reload != $maskedAutoReloadFlag) ||
				($object->jabber_host != $databaseValues["jabber_host"]) ||
				($object->jabber_domain != $databaseValues["jabber_domain"]) ||
				($object->jabber_resource != $databaseValues["jabber_resource"]) ||
				($object->jabber_port != $databaseValues["jabber_port"]) ||
				($object->device_user_mode != "true"));
				
	//Checks for 1105-1488			
	} else if($serverRevision > 1104) {
		return (($object->asterisk_host != "localhost") || 
				($object->asterisk_port != "5038") || 
				($object->asterisk_login != "isymphony") || 
				($object->asterisk_password != "ismanager*con") || 
				($object->originate_timeout != $databaseValues["originate_timeout"]) ||
				($object->jabber_host != $databaseValues["jabber_host"]) ||
				($object->jabber_port != $databaseValues["jabber_port"]) ||
				($object->device_user_mode != "true"));
	
	//Checks for rev 1104 and below
	} else {
		
		return	(($object->asterisk_host != "localhost") || 
				($object->asterisk_port != "5038") || 
				($object->asterisk_login != "isymphony") || 
				($object->asterisk_password != "ismanager*con") || 
				($object->originate_timeout != $databaseValues["originate_timeout"]) ||
				($object->jabber_host != $databaseValues["jabber_host"]) ||
				($object->jabber_port != $databaseValues["jabber_port"]));
	}	
}

function isymphony_modify_tenant_update_check($serverRevision, $databaseValues, $maskedPagedStatusEnabledFlag, $agentLoginContext, $object) {
	
	if($serverRevision >= 1660) {
			return (($object->originating_context != "c-x-operator-panel-orig-proxy") || 
					($object->redirecting_context != "from-internal") ||
					($object->agent_login_context != $agentLoginContext) ||
					($object->page_status_enabled != $maskedPagedStatusEnabledFlag) ||
					($object->page_context != "ext-paging") ||
					($object->record_file_name != "%EXT%-%DATE_TIME_FORMAT[yyyyMMdd-HHmmss]%-%ID%-%LINKED_ID%"));
	
	//Checks for 1520-1659
	} else if($serverRevision >= 1520) {
			return (($object->originating_context != "c-x-operator-panel-orig-proxy") || 
				($object->redirecting_context != "from-internal") ||
				($object->page_status_enabled != $maskedPagedStatusEnabledFlag) ||
				($object->page_context != "ext-paging") ||
				($object->record_file_name != "%EXT%-%DATE_TIME_FORMAT[yyyyMMdd-HHmmss]%-%ID%-%LINKED_ID%"));

	//Check for 1105-1519
	} else if($serverRevision > 1104) {
		return (($object->originating_context != "c-x-operator-panel-orig-proxy") || 
				($object->redirecting_context != "from-internal") ||
				($object->page_status_enabled != $maskedPagedStatusEnabledFlag) ||
				($object->page_context != "ext-paging"));
		
	//Checks for 1104 and below	
	} else {
		return ($object->originating_context != "c-x-operator-panel-orig-proxy") || ($object->redirecting_context != "from-internal");
	}														
}

function isymphony_modify_extension_update_check($serverRevision, $databaseValues, $maskedAutoAnswerFlag, $agentLoginInterface, $object) {
	
	if($serverRevision >= 2203) {
		return (($object->cell_phone != $databaseValues["cell_phone"]) || 
				($object->email != $databaseValues["email"]) || 
				($object->name != $databaseValues["display_name"]) || 
				($object->voice_mail != $databaseValues["user_id"]) || 
				($object->peer != $databaseValues["peer"]) ||
				($object->auto_answer != $maskedAutoAnswerFlag) ||
				($object->agent_login_interface != $agentLoginInterface)) ||
				($object->agent_login_name  != $databaseValues["display_name"]);
	//Checks for 1957 and above
	}else if($serverRevision >= 1957) {
		return (($object->cell_phone != $databaseValues["cell_phone"]) || 
				($object->email != $databaseValues["email"]) || 
				($object->name != $databaseValues["display_name"]) || 
				($object->voice_mail != $databaseValues["user_id"]) || 
				($object->peer != $databaseValues["peer"]) ||
				($object->auto_answer != $maskedAutoAnswerFlag) ||
				($object->agent_login_interface != $agentLoginInterface));

	//Checks for 1493-1956
	} else if($serverRevision >= 1493) {
		return (($object->cell_phone != $databaseValues["cell_phone"]) || 
				($object->email != $databaseValues["email"]) || 
				($object->name != $databaseValues["display_name"]) || 
				($object->voice_mail != $databaseValues["user_id"]) || 
				($object->peer != $databaseValues["peer"]) ||
				($object->auto_answer != $maskedAutoAnswerFlag));
				
	//Checks for 1492 and below	
	} else {
		return (($object->cell_phone != $databaseValues["cell_phone"]) || 
				($object->email != $databaseValues["email"]) || 
				($object->name != $databaseValues["display_name"]) || 
				($object->voice_mail != $databaseValues["user_id"]) || 
				($object->peer != $databaseValues["peer"]));
	}
}

function isymphony_modify_profile_update_check($serverRevision, $databaseValues, $maskedJabberPort, $object) {
	
	//Checks for revision 1489 and up
	if($serverRevision >= 1489) {
		return (($object->password != $databaseValues["password"]) ||         
			   	($object->jabber_host != $databaseValues["jabber_host"]) || 
				($object->jabber_domain != $databaseValues["jabber_domain"]) ||     
				($object->jabber_resource != $databaseValues["jabber_resource"]) ||  
				($maskedJabberPort != $databaseValues["jabber_port"]) || 
				($object->jabber_user_name != $databaseValues["jabber_user_name"]) || 
				($object->jabber_password != $databaseValues["jabber_password"]));
				
	//Checks for revision 1488 and below	
	} else {
		return (($object->password != $databaseValues["password"]) ||         
				($object->jabber_user_name != $databaseValues["jabber_user_name"]) || 
				($object->jabber_password != $databaseValues["jabber_password"]));		
	}
}	

//Helper functions
function isymphony_modify_write_to_file($file, $content) {
	if($file) {
		fwrite($file,$content);
	}
}

function isymphony_modify_remove_array_item($array, $item) {
	return explode(',',str_replace($item.',','',(join(',',$array))));
}

?>