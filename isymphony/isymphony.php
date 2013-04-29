<?php
/*
 *Name         : isymphony.php
 *Author       : Michael Yara
 *Created      : June 15, 2008
 *Last Updated : Feb 09, 2012
 *History      : 0.9
 *Purpose      : Contains all functions used to interact with the iSymphony configuration.
 *Copyright    : 2011 HEHE Enterprises, LLC
 */
include_once(dirname(__FILE__) . "/classes.php");

class iSymphony{
	
	//Global Variables
	static $SOCKET 									= null;
	static $ISERROR 								= "";
	static $HOST 									= 'localhost';
	static $PORT 									= '50001';
	static $SOCKET_TIMEOUT 							= 10;
	static $READ_LENGTH								= 1024;
	static $PROMPT_TERMINATOR                       = "#";                                      
	static $NEW_LINE 								= "\n";
	static $amp_conf 								= '';
	
	//Tracker variables for improved running time
	static $CURRENT_LOCATION 						= null;
	static $CURRENT_TENANT 							= null;
	static $CURRENT_PROFILE							= null;
	static $CURRENT_GROUP 							= null;
	
	//Property_arrays
	static $serverPropertyArray 					= array("client_port","cli_port","username","password");
	static $locationPropertyArray 					= array("name","admin_password","asterisk_host","asterisk_port","asterisk_login","asterisk_password","originate_timeout","jabber_host","jabber_port");
	static $tenantPropertyArray 					= array("name","admin_password","originating_context","redirecting_context","music_on_hold_class","outside_line_number","record_file_name","record_file_extension","mix_mode");
	static $extensionPropertyArray 					= array("extension_val","name","cell_phone","email","peer","alt_origination_method","voice_mail","agent");
	static $profilePropertyArray 					= array("name","password","jabber_user_name","jabber_password","can_view_everyone_directory");
	static $queuePropertyArray 						= array("name","queue_val","extension_val","context");
	static $conferenceRoomPropertyArray 			= array("name","predefined","room_number","extension_val","context","announce_user_count","music_on_hold_for_single_user","exit_room_via_pound","present_menu_via_star","announce_user_join_leave","disable_join_leave_notification","record");
	static $permissionGroupPropertyArray 			= array("name");
	static $statusPropertyArray 					= array("name","type");
	static $localPermissionPropertyArray 			= array("call_voice_mail","hold_calls","transfer_call_to_voice_mail","mute","record","hangup","set_user_status_note","call_cell_phone","add_extension_directory","set_user_status","transfer_call_to_cell_phone","agent_login","add_temp_meetme_room");
	static $remotePermissionPropertyArray			= array("call_voice_mail","transfer_to","transfer_call_to_voice_mail","steal_call","record","originate_to","email","call_cell_phone","barge","transfer_call_to_cell_phone","chat","agent_login","view_calls","view_caller_id");
	static $parkPermissionPropertyArray 			= array("park_call","set_parked_call_note","unpark_call");
	static $queuePermissionPropertyArray 			= array("transfer_to","steal_call");
	static $conferenceRoomPermissionPropertyArray	= array("steal_call","transfer_to","originate_to","mute_users","kick_users");
	static $updateStatePropertyArray 				= array("task","message","work","work_done");
	
	function __construct($host){
		self::$HOST = $host;
	}
	
	//Connection methods-------------------------------------------------------------------------------
	/*###############################################
	 * Initializes connection to iSymphony server
	 * Takes: nothing
	 * Returns: true if successful or an error message if not
	 */
	static function iSymphonyConnect() {
		
		if((self::$SOCKET = socket_create(AF_INET, SOCK_STREAM, 0)) && (@socket_connect(self::$SOCKET, self::$HOST, self::$PORT))) {		

			//Set read timeout
			socket_set_option(self::$SOCKET,SOL_SOCKET,SO_RCVTIMEO,array('sec'=>self::$SOCKET_TIMEOUT,'usec'=>0));
			
			//Read out prompt
			self::readCLIData();
		
			//Check version and revision number to modify property arrays if necessary
			if((($versionResult = self::getiSymphonyServerVersion()) !== false) && (($revisionResult = self::getiSymphonyServerRevision()) !== false)) {
			
				//Modify for rev 2257
				if($revisionResult >= 2257) {
					self::isymphony_modify_property_arrays_2_2_2257();
				
				//Modify for rev 2205 through 2256
				} else if($revisionResult >= 2205) {
					self::isymphony_modify_property_arrays_2_2_2205();
			
				//Modify for rev 2203 through 2204
				} else if($revisionResult >= 2203) {
					self::isymphony_modify_property_arrays_2_2_2203();	
				
				//Modify for rev 2174 through 2202
				} else if($revisionResult >= 2174) {
					self::isymphony_modify_property_arrays_2_2_2174();		
				
				//Modify for rev 2124 through 2173
				} else if($revisionResult >= 2124) {
					self::isymphony_modify_property_arrays_2_2_2124();			

				//Modify for rev 2096 through 2123
				} else if($revisionResult >= 2096) {
					self::isymphony_modify_property_arrays_2_2_2096();	
				
				//Modify for rev 2041 through 2095
				} else if($revisionResult >= 2041) {
					self::isymphony_modify_property_arrays_2_2_2041();		
				
				//Modify for rev 1957 through 2040
				} else if($revisionResult >= 1957) {
					self::isymphony_modify_property_arrays_2_2_1957();
			
				//Modify for rev 1736 through 1956
				} else if($revisionResult >= 1736) {
					self::isymphony_modify_property_arrays_2_1_1736();
			
				//Modify for rev 1760 through 1735
				} else if($revisionResult >= 1760) {
					self::isymphony_modify_property_arrays_2_1_1706();
				
				//Modify for rev 1678 through 1759
				} else if($revisionResult >= 1678) {
					self::isymphony_modify_property_arrays_2_1_1678();
				
				//Modify for rev 1660 through 1677
				} else if($revisionResult >= 1660) {
					self::isymphony_modify_property_arrays_2_1_1660();
		
				//Modify for rev 1493 through 1659
				} else if($revisionResult >= 1493) {
					self::isymphony_modify_property_arrays_2_1_1493();
			
				//Modify for rev 1489 through 1493
				} else if($revisionResult >= 1489) {
					self::isymphony_modify_property_arrays_2_1_1489();

				//Modify for rev 1107 through 1492	
				} else if($revisionResult >= 1107) {
					self::isymphony_modify_property_arrays_2_1();
				}
			}
			return true;
		} else {
			self::$ISERROR = "Could not connect to server: " . socket_strerror(socket_last_error());
			return false;
		}
	}

	/*###############################################
	 * Disconnects from iSymphony server
	 * Takes: nothing
	 * Returns: nothing 
	 */
	static function iSymphonyDisconnect() {
	
		if((self::$SOCKET !== null) && (self::$SOCKET)) {
			self::sendAndRecive("exit", false);
			socket_close(self::$SOCKET);
		}
		self::isymphony_reset_property_arrays();
		self::update_tracker();
	}
	
	//List methods-------------------------------------------------------------------------------------
	/*###############################################
	 * Gets list of locations
	 * Takes: nothing
	 * Returns: array containing the names of the locations 
	 */
	static function getISymphonyLocationList() {
		//Move to server mode
		if(!self::moveToServer()) {
			return false;
		}
	
		//Get location list
		return self::checkAndSetErrorList("list locations");
	}

	/*###############################################
	 * Gets list of tenants for a given location
	 * Takes: location name
	 * Returns: array containing the names of the tenants 
	 */
	static function getISymphonyTenantList($location) {

		//Move to location mode
		if(!self::moveToLocation($location)) {
			return false;
		}
	
		//Get tenant list
		return self::checkAndSetErrorList("list tenants");
	}


	/*###############################################
	 * Gets list of extensions for a given tenant
	 * Takes: location name, tenant name
	 * Returns: array containing the names of the extensions 
	 */
	static function getISymphonyExtensionList($location, $tenant) {

		//Move to tenant mode
		if(!self::moveToTenant($location, $tenant)) {
			return false;
		}
	
		//Get extension list
		return self::checkAndSetErrorList("list extensions");
	}

	/*###############################################
	 * Gets list of profiles for a given tenant
	 * Takes: location name, tenant name
	 * Returns: array containing the names of the profiles 
	 */
	static function getISymphonyProfileList($location, $tenant) {

		//Move to tenant mode
		if(!self::moveToTenant($location, $tenant)) {
			return false;
		}
	
		//Get profile list
		return self::checkAndSetErrorList("list profiles");
	}

	/*###############################################
	 * Gets list of queues for a given tenant
	 * Takes: location name, tenant name
	 * Returns: array containing the names of the queues 
	 */
	static function getISymphonyQueueList($location, $tenant) {

		//Move to tenant mode
		if(!self::moveToTenant($location, $tenant)) {
			return false;
		}
	
		//Get queue list
		return self::checkAndSetErrorList("list queues");
	}

	/*###############################################
	 * Gets list of conference rooms for a given tenant
	 * Takes: location name, tenant name
	 * Returns: array containing the names of the conference rooms 
	 */
	static function getISymphonyConferenceRoomList($location, $tenant) {

		//Move to tenant mode
		if(!self::moveToTenant($location, $tenant)) {
			return false;
		}
	
		//Get conference room list
		return self::checkAndSetErrorList("list meetme");
	}

	/*###############################################
	 * Gets list of statuses for a given tenant
	 * Takes: location name, tenant name
	 * Returns: array containing the names of the statuses 
	 */
	static function getISymphonyStatusList($location, $tenant) {

		//Move to tenant mode
		if(!self::moveToTenant($location, $tenant)) {
			return false;
		}
	
		//Get status list
		return self::checkAndSetErrorList("list statuses");
	}

	/*###############################################
	 * Gets list of permission groups for a given tenant
	 * Takes: location name, tenant name
	 * Returns: array containing the names of the permission groups 
	 */
	static function getISymphonyPermissionGroupList($location, $tenant) {

		//Move to tenant mode
		if(!self::moveToTenant($location, $tenant)) {
			return false;
		}
	
		//Get permission group list
		return self::checkAndSetErrorList("list permgroups");
	}

	/*###############################################
	 * Gets list of remote extension permissions of a specified extension group
	 * Takes: location name, tenant name, permission group name
	 * Returns: array containing the names of the extensions included in the permission list  
	 */
	static function getISymphonyPermissionGroupRemoteExtensionPermissionList($location, $tenant, $group) {

		//Move to permission group mode
		if(!self::moveToPermissionGroup($location, $tenant, $group)) {
			return false;
		}
	
		//Get permission group remote extension permission list
		return self::checkAndSetErrorList("list remote");
	}

	/*###############################################
	 * Gets list of queue permissions of a specified extension group
	 * Takes: location name, tenant name, permission group name
	 * Returns: array containing the names of the queues included in the permission list  
	 */
	static function getISymphonyPermissionGroupQueuePermissionList($location, $tenant, $group) {

		//Move to permission group mode
		if(!self::moveToPermissionGroup($location, $tenant, $group)) {
			return false;
		}
	
		//Get permission group queue permission list
		return self::checkAndSetErrorList("list queue");
	}

	/*###############################################
	 * Gets list of conference room permissions of a specified extension group
	 * Takes: location name, tenant name, permission group name
	 * Returns: array containing the names of the conference rooms included in the permission list  
	 */
	static function getISymphonyPermissionGroupConferenceRoomPermissionList($location, $tenant, $group) {

		//Move to permission group mode
		if(!self::moveToPermissionGroup($location, $tenant, $group)) {
			return false;
		}
	
		//Get permission group conference room permission list
		return self::checkAndSetErrorList("list meetme");
	}

	/*###############################################
	 * Gets list of remote extension permission overrides of a specified profile
	 * Takes: location name, tenant name, profile name
	 * Returns: array containing the names of the extensions included in the permission list  
	 */
	static function getISymphonyOverrideRemoteExtensionPermissionList($location, $tenant, $profile) {

		//Move to profile mode
		if(!self::moveToProfile($location, $tenant, $profile)) {
			return false;
		}
	
		//Get override remote extension permission list
		return self::checkAndSetErrorList("list remote");
	}

	/*###############################################
	 * Gets list of queue permission overrides of a specified profile
	 * Takes: location name, tenant name, profile name
	 * Returns: array containing the names of the queues included in the permission list  
	 */
	static function getISymphonyOverrideQueuePermissionList($location, $tenant, $profile) {

		//Move to profile mode
		if(!self::moveToProfile($location, $tenant, $profile)) {
			return false;
		}
	
		//Get override queue permission list
		return self::checkAndSetErrorList("list queue");
	}

	/*###############################################
	 * Gets list of conference room permission overrides of a specified profile
	 * Takes: location name, tenant name, profile name
	 * Returns: array containing the names of the conference rooms included in the permission list  
	 */
	static function getISymphonyOverrideConferenceRoomPermissionList($location, $tenant, $profile) {

		//Move to profile mode
		if(!self::moveToProfile($location, $tenant, $profile)) {
			return false;
		}
	
		//Get override conference room permission list
		return self::checkAndSetErrorList("list meetme");
	}

	/*###############################################
	 * Gets list of profile's managed extensions
	 * Takes: location name, tenant name, profile name
	 * Returns: array containing the extensions managed by the specified profile  
	 */
	static function getISymphonyProfileExtensionList($location, $tenant, $profile) {

		//Move to profile mode
		if(!self::moveToProfile($location, $tenant, $profile)) {
			return false;
		}
	
		//Get extension list
		return self::checkAndSetErrorList("list extensions");
	}

	/*###############################################
	 * Gets list of permission group members
	 * Takes: location name, tenant name, permission group name
	 * Returns: array containing the permission group's member's  
	 */
	static function getISymphonyPermissionGroupMemberList($location, $tenant, $group) {

		//Move to permission group mode
		if(!self::moveToPermissionGroup($location, $tenant, $group)) {
			return false;
		}
	
		//Get member list
		return self::checkAndSetErrorList("list members");
	}

	//Property class query methods---------------------------------------------------------------------
	/*###############################################
	 * Gets an object that represents the servers properties
	 * Takes: nothing
	 * Returns: an ISymphonyServer object or false if error occurred  
	 */
	static function getISymphonyServer() {
	 	//Move to server mode
		if(!self::moveToServer()) {
			return false;
		}
 	
	 	//Get list of properties
		if(($propertyArray = self::checkAndSetErrorList("view")) !== false) {
		
			//Convert return array to associative array
			$propertyArray = self::convertPropertyArray($propertyArray);

			//Create object from properties
			$object = new ISymphonyServer;
		
			//Set properties of object
			foreach(self::$serverPropertyArray as $val) {
			
				//Check if key exists in the associative array if it dose modify object if not set error and return false
				if(array_key_exists($val, $propertyArray)) {
					$object->$val = $propertyArray[$val];
				} else {
					self::$ISERROR = "Error: property \"$val\" was not provided by the server.";
					return false;
				}
			}		
	
			return $object;
		} else {
			return false;
		}
	 }
 
	/*###############################################
	 * Gets an object that represents a location's properties
	 * Takes: location name
	 * Returns: an ISymphonyLocation object or false if error occurred  
	 */
	static function getISymphonyLocation($location) {
 	
	 	//Move to location mode
		if(!self::moveToLocation($location)) {
			return false;
		}
 	
	 	//Get list of properties
		if(($propertyArray = self::checkAndSetErrorList("view")) !== false) {
		
			//Convert return array to associative array
			$propertyArray = self::convertPropertyArray($propertyArray);

			//Create object from properties
			$object = new ISymphonyLocation;
		
			//Set mode attributes
			if(array_key_exists("name", $propertyArray)) {
				$object->original_name = $propertyArray["name"];
			} else {
				self::$ISERROR = "Error: mode property \"name\" was not provided by the server.";
				return false;
			}
		
			//Set properties of object
			foreach(self::$locationPropertyArray as $val) {
			
				//Check if key exists in the associative array if it dose modify object if not set error and return false
				if(array_key_exists($val, $propertyArray)) {
					$object->$val = $propertyArray[$val];
				} else {
					self::$ISERROR = "Error: property \"$val\" was not provided by the server.";
					return false;
				}
			}		
	
			return $object;
		} else {
			return false;
		}
	 }
 
	/*###############################################
	 * Gets an object that represents a tenant's properties
	 * Takes: location name, tenant name
	 * Returns: an ISymphonyTenant object or false if error occurred  
	 */
	static function getISymphonyTenant($location, $tenant) {
 	
	 	//Move to tenant mode
		if(!self::moveToTenant($location, $tenant)) {
			return false;
		}
 	
	 	//Get list of properties
		if(($propertyArray = self::checkAndSetErrorList("view")) !== false) {
		
			//Convert return array to associative array
			$propertyArray = self::convertPropertyArray($propertyArray);

			//Create object from properties
			$object = new ISymphonyTenant;
		
			//Set mode attributes
			if(array_key_exists("name", $propertyArray)) {
				$object->mode_location = $location;
				$object->original_name = $propertyArray["name"];
			} else {
				self::$ISERROR = "Error: mode property \"name\" was not provided by the server.";
				return false;
			}
		
			//Set properties of object
			foreach(self::$tenantPropertyArray as $val) {
			
				//Check if key exists in the associative array if it dose modify object if not set error and return false
				if(array_key_exists($val, $propertyArray)) {
					$object->$val = $propertyArray[$val];
				} else {
					self::$ISERROR = "Error: property \"$val\" was not provided by the server.";
					return false;
				}
			}		
	
			return $object;
		} else {
			return false;
		}
	 }
 
	/*###############################################
	 * Gets an object that represents an extensions's properties
	 * Takes: location name, tenant name, extension name
	 * Returns: an ISymphonyExtension object or false if error occurred  
	 */
	static function getISymphonyExtension($location, $tenant, $extension) {

	 	//Move to extension mode
		if(!self::moveToExtension($location, $tenant, $extension)) {
			return false;
		}
 	
	 	//Get list of properties
		if(($propertyArray = self::checkAndSetErrorList("view")) !== false) {
		
			//Convert return array to associative array
			$propertyArray = self::convertPropertyArray($propertyArray);

			//Create object from properties
			$object = new ISymphonyExtension;
		
			//Set mode attributes
			if(array_key_exists("name", $propertyArray)) {
				$object->mode_location = $location;
				$object->mode_tenant = $tenant;
				$object->original_extension_val = $propertyArray["extension_val"];
			} else {
				self::$ISERROR = "Error: mode property \"name\" was not provided by the server.";
				return false;
			}
				
			//Set properties of object
			foreach(self::$extensionPropertyArray as $val) {
						
				//Check if key exists in the associative array if it dose modify object if not set error and return false
				if(array_key_exists($val, $propertyArray)) {
					$object->$val = $propertyArray[$val];
				} else {
					self::$ISERROR = "Error: property \"$val\" was not provided by the server.";
					return false;
				}
			}		
	
			return $object;
		} else {
			return false;
		}
	 }
 
	/*###############################################
	 * Gets an object that represents a profiles's properties
	 * Takes: location name, tenant name, profile name
	 * Returns: an ISymphonyProfile object or false if error occurred  
	 */
	static function getISymphonyProfile($location, $tenant, $profile) {
 	
 	
	 	//Move to profile mode
		if(!self::moveToProfile($location, $tenant, $profile)) {
			return false;
		}
 	
	 	//Get list of properties
		if(($propertyArray = self::checkAndSetErrorList("view")) !== false) {
		
			//Convert return array to associative array
			$propertyArray = self::convertPropertyArray($propertyArray);

			//Create object from properties
			$object = new ISymphonyProfile;
		
			//Set mode attributes
			if(array_key_exists("name", $propertyArray)) {
				$object->mode_location = $location;
				$object->mode_tenant = $tenant;
				$object->original_name = $propertyArray["name"];
			} else {
				self::$ISERROR = "Error: mode property \"name\" was not provided by the server.";
				return false;
			}
				
			//Set properties of object
			foreach(self::$profilePropertyArray as $val) {
						
				//Check if key exists in the associative array if it dose modify object if not set error and return false
				if(array_key_exists($val, $propertyArray)) {
					$object->$val = $propertyArray[$val];
				} else {
					self::$ISERROR = "Error: property \"$val\" was not provided by the server.";
					return false;
				}
			}		
	
			return $object;
		} else {
			return false;
		}
	} 

	/*###############################################
	 * Gets an object that represents a queues's properties
	 * Takes: location name, tenant name, queue name
	 * Returns: an ISymphonyQueue object or false if error occurred  
	 */
	static function getISymphonyQueue($location, $tenant, $queue) {
 	
	 	//Move to queue mode
		if(!self::moveToQueue($location, $tenant, $queue)) {
			return false;
		}
 	
	 	//Get list of properties
		if(($propertyArray = self::checkAndSetErrorList("view")) !== false) {
		
			//Convert return array to associative array
			$propertyArray = self::convertPropertyArray($propertyArray);

			//Create object from properties
			$object = new ISymphonyQueue;
		
			//Set mode attributes
			if(array_key_exists("name", $propertyArray)) {
				$object->mode_location = $location;
				$object->mode_tenant = $tenant;
				$object->original_name = $propertyArray["name"];
			} else {
				self::$ISERROR = "Error: mode property \"name\" was not provided by the server.";
				return false;
			}
				
			//Set properties of object
			foreach(self::$queuePropertyArray as $val) {
						
				//Check if key exists in the associative array if it dose modify object if not set error and return false
				if(array_key_exists($val, $propertyArray)) {
					$object->$val = $propertyArray[$val];
				} else {
					self::$ISERROR = "Error: property \"$val\" was not provided by the server.";
					return false;
				}
			}		
	
			return $object;
		} else {
			return false;
		}
	}  

	/*###############################################
	 * Gets an object that represents a conference room's properties
	 * Takes: location name, tenant name, room name
	 * Returns: an ISymphonyConferenceRoom object or false if error occurred  
	 */
	static function getISymphonyConferenceRoom($location, $tenant, $room) {
	
	 	//Move to conference room mode
		if(!self::moveToConferenceRoom($location, $tenant, $room)) {
			return false;
		}
 	
	 	//Get list of properties
		if(($propertyArray = self::checkAndSetErrorList("view")) !== false) {
		
			//Convert return array to associative array
			$propertyArray = self::convertPropertyArray($propertyArray);

			//Create object from properties
			$object = new ISymphonyConferenceRoom;
		
			//Set mode attributes
			if(array_key_exists("name", $propertyArray)) {
				$object->mode_location = $location;
				$object->mode_tenant = $tenant;
				$object->original_name = $propertyArray["name"];
			} else {
				self::$ISERROR = "Error: mode property \"name\" was not provided by the server.";
				return false;
			}
				
			//Set properties of object
			foreach(self::$conferenceRoomPropertyArray as $val) {
						
				//Check if key exists in the associative array if it dose modify object if not set error and return false
				if(array_key_exists($val, $propertyArray)) {
					$object->$val = $propertyArray[$val];
				} else {
					self::$ISERROR = "Error: property \"$val\" was not provided by the server.";
					return false;
				}
			}		
	
			return $object;
		} else {
			return false;
		}
	}  

	/*###############################################
	 * Gets an object that represents a permission groups's properties
	 * Takes: location name, tenant name, group name
	 * Returns: an ISymphonyPermissionGroup object or false if error occurred  
	 */
	static function getISymphonyPermissionGroup($location, $tenant, $group) {
	 	//Move to permission group mode
		if(!self::moveToPermissionGroup($location, $tenant, $group)) {
			return false;
		}
 	
	 	//Get list of properties
		if(($propertyArray = self::checkAndSetErrorList("view")) !== false) {
		
			//Convert return array to associative array
			$propertyArray = self::convertPropertyArray($propertyArray);

			//Create object from properties
			$object = new ISymphonyPermissionGroup;
		
			//Set mode attributes
			if(array_key_exists("name", $propertyArray)) {
				$object->mode_location = $location;
				$object->mode_tenant = $tenant;
				$object->original_name = $propertyArray["name"];
			} else {
				self::$ISERROR = "Error: mode property \"name\" was not provided by the server.";
				return false;
			}
				
			//Set properties of object
			foreach(self::$permissionGroupPropertyArray as $val) {
						
				//Check if key exists in the associative array if it dose modify object if not set error and return false
				if(array_key_exists($val, $propertyArray)) {
					$object->$val = $propertyArray[$val];
				} else {
					self::$ISERROR = "Error: property \"$val\" was not provided by the server.";
					return false;
				}
			}		
	
			return $object;
		} else {
			return false;
		}
	}  

	/*###############################################
	 * Gets an object that represents a status's properties
	 * Takes: location name, tenant name, status name
	 * Returns: an ISymphonyStatus object or false if error occurred  
	 */
	static function getISymphonyStatus($location, $tenant, $status) {
 	
	 	//Move to status mode
		if(!self::moveToStatus($location, $tenant, $status)) {
			return false;
		}
 	
	 	//Get list of properties
		if(($propertyArray = self::checkAndSetErrorList("view")) !== false) {
		
			//Convert return array to associative array
			$propertyArray = self::convertPropertyArray($propertyArray);

			//Create object from properties
			$object = new ISymphonyStatus;
		
			//Set mode attributes
			if(array_key_exists("name", $propertyArray)) {
				$object->mode_location = $location;
				$object->mode_tenant = $tenant;
				$object->original_name = $propertyArray["name"];
			} else {
				self::$ISERROR = "Error: mode property \"name\" was not provided by the server.";
				return false;
			}
				
			//Set properties of object
			foreach($statusPropertyArray as $val) {
						
				//Check if key exists in the associative array if it dose modify object if not set error and return false
				if(array_key_exists($val, $propertyArray)) {
					$object->$val = $propertyArray[$val];
				} else {
					self::$ISERROR = "Error: property \"$val\" was not provided by the server.";
					return false;
				}
			}		
	
			return $object;
		} else {
			return false;
		}
	}   

	/*###############################################
	 * Gets an object that represents the default local permissions
	 * Takes: nothing
	 * Returns: an ISymphonyDefaultLocalPermission object or false if error occurred  
	 */
	static function getISymphonyDefaultLocalPermissions() {
 	
	 	//Move to default local permission mode
		if(!self::moveToDefaultLocalPermission()) {
			return false;
		}
 	
	 	//Get list of properties
		if(($propertyArray = self::checkAndSetErrorList("view")) !== false) {
		
			//Convert return array to associative array
			$propertyArray = self::convertPropertyArray($propertyArray);

			//Create object from properties
			$object = new ISymphonyDefaultLocalPermission;
						
			//Set properties of object
			foreach($localPermissionPropertyArray as $val) {
						
				//Check if key exists in the associative array if it dose modify object if not set error and return false
				if(array_key_exists($val, $propertyArray)) {
					$object->$val = $propertyArray[$val];
				} else {
					self::$ISERROR = "Error: property \"$val\" was not provided by the server.";
					return false;
				}
			}		
	
			return $object;
		} else {
			return false;
		}
	} 

	/*###############################################
	 * Gets an object that represents the default remote permissions
	 * Takes: nothing
	 * Returns: an ISymphonyDefaultRemotePermission object or false if error occurred  
	 */
	static function getISymphonyDefaultRemotePermissions() {

		//Move to default remote permission mode
		if(!self::moveToDefaultRemotePermission()) {
			return false;
		}
	 	
	 	//Get list of properties
		if(($propertyArray = self::checkAndSetErrorList("view")) !== false) {
		
			//Convert return array to associative array
			$propertyArray = self::convertPropertyArray($propertyArray);

			//Create object from properties
			$object = new ISymphonyDefaultRemotePermission;
						
			//Set properties of object
			foreach(self::$remotePermissionPropertyArray as $val) {
						
				//Check if key exists in the associative array if it dose modify object if not set error and return false
				if(array_key_exists($val, $propertyArray)) {
					$object->$val = $propertyArray[$val];
				} else {
					self::$ISERROR = "Error: property \"$val\" was not provided by the server.";
					return false;
				}
			}		
	
			return $object;
		} else {
			return false;
		}
	}

	/*###############################################
	 * Gets an object that represents the default park permissions
	 * Takes: nothing
	 * Returns: an ISymphonyDefaultParkPermission object or false if error occurred  
	 */
	static function getISymphonyDefaultParkPermissions() {
		//Move to default park permission mode
		if(!self::moveToDefaultParkPermission()) {
			return false;
		}
 	
	 	//Get list of properties
		if(($propertyArray = self::checkAndSetErrorList("view")) !== false) {
		
			//Convert return array to associative array
			$propertyArray = self::convertPropertyArray($propertyArray);

			//Create object from properties
			$object = new ISymphonyDefaultParkPermission;
						
			//Set properties of object
			foreach(self::$parkPermissionPropertyArray as $val) {
						
				//Check if key exists in the associative array if it dose modify object if not set error and return false
				if(array_key_exists($val, $propertyArray)) {
					$object->$val = $propertyArray[$val];
				} else {
					self::$ISERROR = "Error: property \"$val\" was not provided by the server.";
					return false;
				}
			}		
	
			return $object;
		} else {
			return false;
		}
	} 

	/*###############################################
	 * Gets an object that represents the default queue permissions
	 * Takes: nothing
	 * Returns: an ISymphonyDefaultQueuePermission object or false if error occurred  
	 */
	static function getISymphonyDefaultQueuePermissions() {

		//Move to default queue permission mode
		if(!self::moveToDefaultQueuePermission()) {
			return false;
		}
	 	
	 	//Get list of properties
		if(($propertyArray = self::checkAndSetErrorList("view")) !== false) {
		
			//Convert return array to associative array
			$propertyArray = convertPropertyArray($propertyArray);

			//Create object from properties
			$object = new ISymphonyDefaultQueuePermission;
						
			//Set properties of object
			foreach(self::$queuePermissionPropertyArray as $val) {
						
				//Check if key exists in the associative array if it dose modify object if not set error and return false
				if(array_key_exists($val, $propertyArray)) {
					$object->$val = $propertyArray[$val];
				} else {
					self::$ISERROR = "Error: property \"$val\" was not provided by the server.";
					return false;
				}
			}		
	
			return $object;
		} else {
			return false;
		}
	}  

	/*###############################################
	 * Gets an object that represents the default conference room permissions
	 * Takes: nothing
	 * Returns: an ISymphonyDefaultConferenceRoomPermission object or false if error occurred  
	 */
	static function getISymphonyDefaultConferenceRoomPermissions() {
	
		//Move to default conference room permission mode
		if(!self::moveToDefaultConferenceRoomPermission()) {
			return false;
		}
	 	
	 	//Get list of properties
		if(($propertyArray = self::checkAndSetErrorList("view")) !== false) {
		
			//Convert return array to associative array
			$propertyArray = self::convertPropertyArray($propertyArray);

			//Create object from properties
			$object = new ISymphonyDefaultConferenceRoomPermission;
						
			//Set properties of object
			foreach(self::$conferenceRoomPermissionPropertyArray as $val) {
						
				//Check if key exists in the associative array if it dose modify object if not set error and return false
				if(array_key_exists($val, $propertyArray)) {
					$object->$val = $propertyArray[$val];
				} else {
					self::$ISERROR = "Error: property \"$val\" was not provided by the server.";
					return false;
				}
			}		
	
			return $object;
		} else {
			return false;
		}
	} 

	/*###############################################
	 * Gets an object that represents a group's local permissions
	 * Takes: location name, tenant name, group name
	 * Returns: an ISymphonyGroupLocalPermission object or false if error occurred  
	 */
	static function getISymphonyGroupLocalPermissions($location, $tenant, $group) {

		//Move to local permission mode
		if(!self::moveToLocalGroupPermission($location, $tenant, $group)) {
			return false;
		}
	 	
	 	//Get list of properties
		if(($propertyArray = self::checkAndSetErrorList("view")) !== false) {
		
			//Convert return array to associative array
			$propertyArray = self::convertPropertyArray($propertyArray);

			//Create object from properties
			$object = new ISymphonyGroupLocalPermission;
				
			//Set mode properties
			$object->mode_location = $location;
			$object->mode_tenant = $tenant;
			$object->mode_group = $group;
			
			//Set properties of object
			foreach(self::$localPermissionPropertyArray as $val) {
						
				//Check if key exists in the associative array if it dose modify object if not set error and return false
				if(array_key_exists($val, $propertyArray)) {
					$object->$val = $propertyArray[$val];
				} else {
					self::$ISERROR = "Error: property \"$val\" was not provided by the server.";
					return false;
				}
			}		
	
			return $object;
		} else {
			return false;
		}
	}

	/*###############################################
	 * Gets an object that represents a group's park permissions
	 * Takes: location name, tenant name, group name
	 * Returns: an ISymphonyGroupParkPermission object or false if error occurred  
	 */
	static function getISymphonyGroupParkPermissions($location, $tenant, $group) {
	
		//Move to local permission mode
		if(!self::moveToParkGroupPermission($location, $tenant, $group)) {
			return false;
		}
	 	
	 	//Get list of properties
		if(($propertyArray = self::checkAndSetErrorList("view")) !== false) {
		
			//Convert return array to associative array
			$propertyArray = self::convertPropertyArray($propertyArray);

			//Create object from properties
			$object = new ISymphonyGroupParkPermission;
				
			//Set mode properties
			$object->mode_location = $location;
			$object->mode_tenant = $tenant;
			$object->mode_group = $group;
			
			//Set properties of object
			foreach(self::$parkPermissionPropertyArray as $val) {
						
				//Check if key exists in the associative array if it dose modify object if not set error and return false
				if(array_key_exists($val, $propertyArray)) {
					$object->$val = $propertyArray[$val];
				} else {
					self::$ISERROR = "Error: property \"$val\" was not provided by the server.";
					return false;
				}
			}		
	
			return $object;
		} else {
			return false;
		}
	}

	/*###############################################
	 * Gets an object that represents a group remote extension permission set
	 * Takes: location name, tenant name, group name, extension name
	 * Returns: an ISymphonyGroupRemotePermission object or false if error occurred  
	 */
	static function getISymphonyGroupRemotePermissions($location, $tenant, $group, $extension) {

		//Move to remote permission mode
		if(!self::moveToRemoteGroupPermission($location, $tenant, $group, $extension)) {
			return false;
		}
	 	
	 	//Get list of properties
		if(($propertyArray = self::checkAndSetErrorList("view")) !== false) {
		
			//Convert return array to associative array
			$propertyArray = self::convertPropertyArray($propertyArray);

			//Create object from properties
			$object = new ISymphonyGroupRemotePermission;
				
			//Set mode properties
			$object->mode_location = $location;
			$object->mode_tenant = $tenant;
			$object->mode_group = $group;
			$object->mode_extension = $extension;
			
			//Set properties of object
			foreach($remotePermissionPropertyArray as $val) {
						
				//Check if key exists in the associative array if it dose modify object if not set error and return false
				if(array_key_exists($val, $propertyArray)) {
					$object->$val = $propertyArray[$val];
				} else {
					self::$ISERROR = "Error: property \"$val\" was not provided by the server.";
					return false;
				}
			}		
	
			return $object;
		} else {
			return false;
		}
	}

	/*###############################################
	 * Gets an object that represents a group queue permission set
	 * Takes: location name, tenant name, group name, queue name
	 * Returns: an ISymphonyGroupQueuePermission object or false if error occurred  
	 */
	static function getISymphonyGroupQueuePermissions($location, $tenant, $group, $queue) {
	
		//Move to queue permission mode
		if(!self::moveToQueueGroupPermission($location, $tenant, $group, $queue)) {
			return false;
		}
	 	
	 	//Get list of properties
		if(($propertyArray = self::checkAndSetErrorList("view")) !== false) {
		
			//Convert return array to associative array
			$propertyArray = self::convertPropertyArray($propertyArray);

			//Create object from properties
			$object = new ISymphonyGroupQueuePermission;
				
			//Set mode properties
			$object->mode_location = $location;
			$object->mode_tenant = $tenant;
			$object->mode_group = $group;
			$object->mode_queue = $queue;
			
			//Set properties of object
			foreach(self::$queuePermissionPropertyArray as $val) {
						
				//Check if key exists in the associative array if it dose modify object if not set error and return false
				if(array_key_exists($val, $propertyArray)) {
					$object->$val = $propertyArray[$val];
				} else {
					self::$ISERROR = "Error: property \"$val\" was not provided by the server.";
					return false;
				}
			}		
	
			return $object;
		} else {
			return false;
		}
	} 

	/*###############################################
	 * Gets an object that represents a group conference room permission set
	 * Takes: location name, tenant name, group name, room name
	 * Returns: an ISymphonyGroupConferenceRoomPermission object or false if error occurred  
	 */
	static function getISymphonyGroupConferenceRoomPermissions($location, $tenant, $group, $room) {

		//Move to conference room permission mode
		if(!self::moveToConferenceRoomGroupPermission($location, $tenant, $group, $room)) {
			return false;
		}
	 	
	 	//Get list of properties
		if(($propertyArray = self::checkAndSetErrorList("view")) !== false) {
		
			//Convert return array to associative array
			$propertyArray = self::convertPropertyArray($propertyArray);

			//Create object from properties
			$object = new ISymphonyGroupConferenceRoomPermission;
				
			//Set mode properties
			$object->mode_location = $location;
			$object->mode_tenant = $tenant;
			$object->mode_group = $group;
			$object->mode_room = $room;
			
			//Set properties of object
			foreach(self::$conferenceRoomPermissionPropertyArray as $val) {
						
				//Check if key exists in the associative array if it dose modify object if not set error and return false
				if(array_key_exists($val, $propertyArray)) {
					$object->$val = $propertyArray[$val];
				} else {
					self::$ISERROR = "Error: property \"$val\" was not provided by the server.";
					return false;
				}
			}		
	
			return $object;
		} else {
			return false;
		}
	}  

	/*###############################################
	 * Gets an object that represents a profiles's local override permissions
	 * Takes: location name, tenant name, profile name
	 * Returns: an ISymphonyOverrideLocalPermission object or false if error occurred  
	 */
	static function getISymphonyOverrideLocalPermissions($location, $tenant, $profile) {

		//Move to local permission mode
		if(!self::moveToLocalOverridePermission($location, $tenant, $profile)) {
			return false;
		}
	 	
	 	//Get list of properties
		if(($propertyArray = self::checkAndSetErrorList("view")) !== false) {
		
			//Convert return array to associative array
			$propertyArray = self::convertPropertyArray($propertyArray);

			//Create object from properties
			$object = new ISymphonyOverrideLocalPermission;
				
			//Set mode properties
			$object->mode_location = $location;
			$object->mode_tenant = $tenant;
			$object->mode_profile = $profile;
			
			//Set properties of object
			foreach(self::$localPermissionPropertyArray as $val) {
						
				//Check if key exists in the associative array if it dose modify object if not set error and return false
				if(array_key_exists($val, $propertyArray)) {
					$object->$val = $propertyArray[$val];
				} else {
					self::$ISERROR = "Error: property \"$val\" was not provided by the server.";
					return false;
				}
			}		
	
			return $object;
		} else {
			return false;
		}
	}

	/*###############################################
	 * Gets an object that represents a profiles's park override permissions
	 * Takes: location name, tenant name, profile name
	 * Returns: an ISymphonyOverrideParkPermission object or false if error occurred  
	 */
	static function getISymphonyOverrideParkPermissions($location, $tenant, $profile) {
	
		//Move to park permission mode
		if(!self::moveToParkOverridePermission($location, $tenant, $profile)) {
			return false;
		}
 	
	 	//Get list of properties
		if(($propertyArray = self::checkAndSetErrorList("view")) !== false) {
		
			//Convert return array to associative array
			$propertyArray = self::convertPropertyArray($propertyArray);

			//Create object from properties
			$object = new ISymphonyOverrideParkPermission;
				
			//Set mode properties
			$object->mode_location = $location;
			$object->mode_tenant = $tenant;
			$object->mode_profile = $profile;
			
			//Set properties of object
			foreach(self::$parkPermissionPropertyArray as $val) {
						
				//Check if key exists in the associative array if it dose modify object if not set error and return false
				if(array_key_exists($val, $propertyArray)) {
					$object->$val = $propertyArray[$val];
				} else {
					self::$ISERROR = "Error: property \"$val\" was not provided by the server.";
					return false;
				}
			}		
	
			return $object;
		} else {
			return false;
		}
	}

	/*###############################################
	 * Gets an object that represents a profile's remote extension permission set
	 * Takes: location name, tenant name, profile name, extension name
	 * Returns: an ISymphonyOverrideRemotePermission object or false if error occurred  
	 */
	static function getISymphonyOverrideRemotePermissions($location, $tenant, $profile, $extension) {
	
		//Move to remote permission mode
		if(!self::moveToRemoteOverridePermission($location, $tenant, $profile, $extension)) {
			return false;
		}
	 	
	 	//Get list of properties
		if(($propertyArray = self::checkAndSetErrorList("view")) !== false) {
		
			//Convert return array to associative array
			$propertyArray = self::convertPropertyArray($propertyArray);

			//Create object from properties
			$object = new ISymphonyOverrideRemotePermission;
				
			//Set mode properties
			$object->mode_location = $location;
			$object->mode_tenant = $tenant;
			$object->mode_profile = $profile;
			$object->mode_extension = $extension;
			
			//Set properties of object
			foreach(self::$remotePermissionPropertyArray as $val) {
						
				//Check if key exists in the associative array if it dose modify object if not set error and return false
				if(array_key_exists($val, $propertyArray)) {
					$object->$val = $propertyArray[$val];
				} else {
					self::$ISERROR = "Error: property \"$val\" was not provided by the server.";
					return false;
				}
			}		
	
			return $object;
		} else {
			return false;
		}
	}

	/*###############################################
	 * Gets an object that represents a profile's queue permission set
	 * Takes: location name, tenant name, profile name, queue name
	 * Returns: an ISymphonyOverrideQueuePermission object or false if error occurred  
	 */
	static function getISymphonyOverrideQueuePermissions($location, $tenant, $profile, $queue) {
	
		//Move to queue permission mode
		if(!self::moveToQueueOverridePermission($location, $tenant, $profile, $queue)) {
			return false;
		}
	 	
	 	//Get list of properties
		if(($propertyArray = self::checkAndSetErrorList("view")) !== false) {
		
			//Convert return array to associative array
			$propertyArray = self::convertPropertyArray($propertyArray);

			//Create object from properties
			$object = new ISymphonyOverrideQueuePermission;
				
			//Set mode properties
			$object->mode_location = $location;
			$object->mode_tenant = $tenant;
			$object->mode_profile = $profile;
			$object->mode_queue = $queue;
			
			//Set properties of object
			foreach(self::$queuePermissionPropertyArray as $val) {
						
				//Check if key exists in the associative array if it dose modify object if not set error and return false
				if(array_key_exists($val, $propertyArray)) {
					$object->$val = $propertyArray[$val];
				} else {
					self::$ISERROR = "Error: property \"$val\" was not provided by the server.";
					return false;
				}
			}		
	
			return $object;
		} else {
			return false;
		}
	} 

	/*###############################################
	 * Gets an object that represents a profile's conference room permission set
	 * Takes: location name, tenant name, profile name, conference room name
	 * Returns: an ISymphonyOverrideQueuePermission object or false if error occurred  
	 */
	static function getISymphonyOverrideConferenceRoomPermissions($location, $tenant, $profile, $room) {
	
		//Move to conference room permission mode
		if(!self::moveToConferenceRoomOverridePermission($location, $tenant, $profile, $room)) {
			return false;
		}
 	
	 	//Get list of properties
		if(($propertyArray = self::checkAndSetErrorList("view")) !== false) {
		
			//Convert return array to associative array
			$propertyArray = self::convertPropertyArray($propertyArray);

			//Create object from properties
			$object = new ISymphonyOverrideConferenceRoomPermission;
				
			//Set mode properties
			$object->mode_location = $location;
			$object->mode_tenant = $tenant;
			$object->mode_profile = $profile;
			$object->mode_room = $room;
			
			//Set properties of object
			foreach(self::$queuePermissionPropertyArray as $val) {
						
				//Check if key exists in the associative array if it dose modify object if not set error and return false
				if(array_key_exists($val, $propertyArray)) {
					$object->$val = $propertyArray[$val];
				} else {
					self::$ISERROR = "Error: property \"$val\" was not provided by the server.";
					return false;
				}
			}		
	
			return $object;
		} else {
			return false;
		}
	}

	//Remove methods-----------------------------------------------------------------------------------
	/*###############################################
	 * Removes a location
	 * Takes: location name
	 * Returns: true if successful or false if error occurred  
	 */
	static function removeISymphonyLocation($location) {
	
		//Move to server mode
		if(!self::moveToServer()) {
			return false;
		}
	
		//Remove element
		return self::checkAndSetErrorNone("remove location $location");
	}

	/*###############################################
	 * Removes a tenant
	 * Takes: location name, tenant name
	 * Returns: true if successful or false if error occurred  
	 */
	static function removeISymphonyTenant($location, $tenant) {
	
		//Move to location mode
		if(!self::moveToLocation($location)) {
			return false;
		}
	
		//Remove element
		return self::checkAndSetErrorNone("remove tenant $tenant");
	}

	/*###############################################
	 * Removes an extension
	 * Takes: location name, tenant name, extension name
	 * Returns: true if successful or false if error occurred  
	 */
	static function removeISymphonyExtension($location, $tenant, $extension) {
	
		//Move to tenant mode
		if(!self::moveToTenant($location, $tenant)) {
			return false;
		}
	
		//Remove element
		return self::checkAndSetErrorNone("remove extension $extension");
	}

		/*###############################################
		 * Removes a profile
		 * Takes: location name, tenant name, profile name
		 * Returns: true if successful or false if error occurred  
		 */
		static function removeISymphonyProfile($location, $tenant, $profile) {

			//Move to tenant mode
			if(!self::moveToTenant($location, $tenant)) {
				return false;
			}

			//Remove element
			return self::checkAndSetErrorNone("remove profile $profile");
		}

		/*###############################################
		 * Removes a queue
		 * Takes: location name, tenant name, queue name
		 * Returns: true if successful or false if error occurred  
		 */
		static function removeISymphonyQueue($location, $tenant, $queue) {

			//Move to tenant mode
			if(!self::moveToTenant($location, $tenant)) {
				return false;
			}

			//Remove element
			return self::checkAndSetErrorNone("remove queue $queue");
		}

		/*###############################################
		 * Removes a conference room
		 * Takes: location name, tenant name, room name
		 * Returns: true if successful or false if error occurred  
		 */
		static function removeISymphonyConferenceRoom($location, $tenant, $room) {

			//Move to tenant mode
			if(!self::moveToTenant($location, $tenant)) {
				return false;
			}

			//Remove element
			return self::checkAndSetErrorNone("remove meetme $room");
		}

		/*###############################################
		 * Removes a status
		 * Takes: location name, tenant name, status name
		 * Returns: true if successful or false if error occurred  
		 */
		static function removeISymphonyStatus($location, $tenant, $status) {

			//Move to tenant mode
			if(!self::moveToTenant($location, $tenant)) {
				return false;
			}

			//Remove element
			return self::checkAndSetErrorNone("remove status $status");
		}

		/*###############################################
		 * Removes a permission group
		 * Takes: location name, tenant name, group name
		 * Returns: true if successful or false if error occurred  
		 */
		static function removeISymphonyPermissionGroup($location, $tenant, $group) {

			//Move to tenant mode
			if(!self::moveToTenant($location, $tenant)) {
				return false;
			}

			//Remove element
			return self::checkAndSetErrorNone("remove permgroup $group");
		}

		/*###############################################
		 * Removes a group remote permission
		 * Takes: location name, tenant name, group name, extension name
		 * Returns: true if successful or false if error occurred  
		 */
		static function removeISymphonyGroupRemotePermission($location, $tenant, $group, $extension) {

			//Move to permission group mode
			if(!self::moveToPermissionGroup($location, $tenant, $group)) {
				return false;
			}

			//Remove element
			return self::checkAndSetErrorNone("remove remote $extension");
		}

		/*###############################################
		 * Removes a group queue permission
		 * Takes: location name, tenant name, group name, queue name
		 * Returns: true if successful or false if error occurred  
		 */
		static function removeISymphonyGroupQueuePermission($location, $tenant, $group, $queue) {

			//Move to permission group mode
			if(!self::moveToPermissionGroup($location, $tenant, $group)) {
				return false;
			}

			//Remove element
			return self::checkAndSetErrorNone("remove queue $queue");
		}

		/*###############################################
		 * Removes a group conference room permission
		 * Takes: location name, tenant name, group name, room name
		 * Returns: true if successful or false if error occurred  
		 */
		static function removeISymphonyGroupConferenceRoomPermission($location, $tenant, $group, $room) {

			//Move to permission group mode
			if(!self::moveToPermissionGroup($location, $tenant, $group)) {
				return false;
			}

			//Remove element
			return self::checkAndSetErrorNone("remove meetme $room");
		}

		/*###############################################
		 * Removes a profile override remote permission
		 * Takes: location name, tenant name, profile name, extension name
		 * Returns: true if successful or false if error occurred  
		 */
		static function removeISymphonyOverrideRemotePermission($location, $tenant, $profile, $extension) {

			//Move to profile mode
			if(!self::moveToProfile($location, $tenant, $profile)) {
				return false;
			}

			//Remove element
			return self::checkAndSetErrorNone("remove remote $extension");
		}

		/*###############################################
		 * Removes a profile override queue permission
		 * Takes: location name, tenant name, profile name, queue name
		 * Returns: true if successful or false if error occurred  
		 */
		static function removeISymphonyOverrideQueuePermission($location, $tenant, $profile, $queue) {

			//Move to profile mode
			if(!self::moveToProfile($location, $tenant, $profile)) {
				return false;
			}

			//Remove element
			return self::checkAndSetErrorNone("remove queue $queue");
		}

		/*###############################################
		 * Removes a profile override conference room permission
		 * Takes: location name, tenant name, profile name, queue name
		 * Returns: true if successful or false if error occurred  
		 */
		static function removeISymphonyOverrideConferenceRoomPermission($location, $tenant, $profile, $room) {

			//Move to profile mode
			if(!self::moveToProfile($location, $tenant, $profile)) {
				return false;
			}

			//Remove element
			return self::checkAndSetErrorNone("remove meetme $room");
		}

		//License methods-----------------------------------------------------------------------------------
		/*###############################################
		 * Activates a tenant license 
		 * Takes: location name, tenant name, serial
		 * Returns: true if successful or false if error occurred  
		 */
		static function activateISymphonyLicense($location, $tenant, $serial) {

			//Move to tenant mode
			if(!self::moveToTenant($location, $tenant)) {
				return false;
			}

			//Activate license
			return self::checkAndSetErrorNone("license activate $serial");
		} 

		/*###############################################
		 * Gets a name associated with a license
		 * Takes: location name, tenant name
		 * Returns: the license name if successful or false if error occurred  
		 */
		static function getISymphonyLicenseName($location, $tenant) {

			//Move to tenant mode
			if(!self::moveToTenant($location, $tenant)) {
				return false;
			}

			//Query license info
			return self::checkAndSetErrorString("license name");
		} 

		/*###############################################
		 * Gets the number of clients of a licenses
		 * Takes: location name, tenant name
		 * Returns: the number of clients if successful or false if error occurred  
		 */
		static function getISymphonyLicenseClients($location, $tenant) {

			//Move to tenant mode
			if(!self::moveToTenant($location, $tenant)) {
				return false;
			}

			//Query license info
			return self::checkAndSetErrorString("license clients");
		} 

		/*###############################################
		 * Gets the number of queues of a licenses
		 * Takes: location name, tenant name
		 * Returns: the number of queues if successful or false if error occurred  
		 */
		static function getISymphonyLicenseQueues($location, $tenant) {

			//Move to tenant mode
			if(!self::moveToTenant($location, $tenant)) {
				return false;
			}

			//Query license info
			return self::checkAndSetErrorString("license queues");
		}

		/*###############################################
		 * Gets the number of trial days left of a licenses
		 * Takes: location name, tenant name
		 * Returns: the number of trial days left if successful or false if error occurred  
		 */
		static function getISymphonyLicenseTrialDays($location, $tenant) {

			//Move to tenant mode
			if(!self::moveToTenant($location, $tenant)) {
				return false;
			}

			//Query license info
			return self::checkAndSetErrorString("license days");
		}

		//User status methods------------------------------------------------------------------------------
		/*###############################################
		 * Sets an extension's status
		 * Takes: location name, tenant name, extension name, status
		 * Returns: true if successful or false if error occurred  
		 */ 
		static function setISymphonyExtensionStatus($location, $tenant, $extension, $status) {
			//Move to extension mode
			if(!self::moveToExtension($location, $tenant, $extension)) {
				return false;
			}

			//Set status
			return self::checkAndSetErrorNone("userstatus $status");
		} 

		/*###############################################
		 * Sets an extension's status note
		 * Takes: location name, tenant name, extension name, note
		 * Returns: true if successful or false if error occurred  
		 */ 
		static function setISymphonyExtensionNote($location, $tenant, $extension, $note) {

			//Move to extension mode
			if(!self::moveToExtension($location, $tenant, $extension)) {
				return false;
			}

			//Set note
			return self::checkAndSetErrorNone("note $note");	
		} 

		/*###############################################
		 * Sets an extension's return time
		 * Takes: location name, tenant name, extension name, time(unix time stamp)
		 * Returns: true if successful or false if error occurred  
		 */ 
		static function setISymphonyExtensionReturnTime($location, $tenant, $extension, $returntime) {

			//Move to extension mode
			if(!self::moveToExtension($location, $tenant, $extension)) {
				return false;
			}

			//Set return time
			return self::checkAndSetErrorNone("returntime $returntime");	
		} 

		/*###############################################
		 * Gets an extension's status
		 * Takes: location name, tenant name, extension name
		 * Returns: the status name if successful or false if error occurred  
		 */ 
		static function getISymphonyExtensionStatus($location, $tenant, $extension) {

			//Move to extension mode
			if(!self::moveToExtension($location, $tenant, $extension)) {
				return false;
			}

			//Get status
			return self::checkAndSetErrorString("query userstatus");
		}

		/*###############################################
		 * Gets an extension's status note
		 * Takes: location name, tenant name, extension name
		 * Returns: the note if successful or false if error occurred  
		 */ 
		static function getISymphonyExtensionNote($location, $tenant, $extension) {

			//Move to extension mode
			if(!self::moveToExtension($location, $tenant, $extension)) {
				return false;
			}

			//Get note
			return self::checkAndSetErrorString("query note");
		}

		/*###############################################
		 * Gets an extension's returnTime
		 * Takes: location name, tenant name, extension name
		 * Returns: the return time as a unix time stamp if successful or false if error occurred  
		 */ 
		static function getISymphonyExtensionReturnTime($location, $tenant, $extension) {

			//Move to extension mode
			if(!self::moveToExtension($location, $tenant, $extension)) {
				return false;
			}

			//Get return time
			return self::checkAndSetErrorString("query returntime");
		}

		//Profile managed extension methods----------------------------------------------------------------
		/*###############################################
		 * Adds an extension to a profile's managed list
		 * Takes: location name, tenant name, profile name, extension name
		 * Returns: true if successful or false if error occurred  
		 */ 
		static function addISymphonyProfileManagedExtension($location, $tenant, $profile, $extension) {

			//Move to profile mode
			if(!self::moveToProfile($location, $tenant, $profile)) {
				return false;
			}

			//Add managed extension
			return self::checkAndSetErrorNone("add extension $extension");
		}

		/*###############################################
		 * Removes an extension from a profile's managed list
		 * Takes: location name, tenant name, profile name, extension name
		 * Returns: true if successful or false if error occurred  
		 */ 
		static function removeISymphonyProfileManagedExtension($location, $tenant, $profile, $extension) {

			//Move to profile mode
			if(!self::moveToProfile($location, $tenant, $profile)) {
				return false;
			}

			//Add managed extension
			return self::checkAndSetErrorNone("remove extension $extension");
		}

		//Permission group member methods------------------------------------------------------------------
		/*###############################################
		 * Adds a profile to a permission group's member list
		 * Takes: location name, tenant name, permission group name, profile name
		 * Returns: true if successful or false if error occurred  
		 */ 
		static function addISymphonyPermissionGroupMember($location, $tenant, $group, $profile) {

			//Move to permission group mode
			if(!self::moveToPermissionGroup($location, $tenant, $group)) {
				return false;
			}

			//Add member
			return self::checkAndSetErrorNone("add member $profile");
		}

		/*###############################################
		 * Removes a profile from a permission group's member list
		 * Takes: location name, tenant name, permission group name, profile name
		 * Returns: true if successful or false if error occurred  
		 */ 
		static function removeISymphonyPermissionGroupMember($location, $tenant, $group, $profile) {

			//Move to permission group mode
			if(!self::moveToPermissionGroup($location, $tenant, $group)) {
				return false;
			}

			//Remove member
			return self::checkAndSetErrorNone("remove member $profile");
		}

		//Server methods-----------------------------------------------------------------------------------
		/*###############################################
		 * Gets the server version
		 * Takes: nothing
		 * Returns: server version if successful or false if error occurred  
		 */ 
		static function getiSymphonyServerVersion() {
			//Move to server mode
			if(!self::moveToServer()) {
				return false;
			}

			//Get server version
			return self::checkAndSetErrorString("version");
		}

		/*###############################################
		 * Gets the server revision
		 * Takes: nothing
		 * Returns: server revision if successful or false if error occurred  
		 */ 
		static function getiSymphonyServerRevision() {

			if(($serverVersion = self::getiSymphonyServerVersion()) === false) {
				return false;
			}

			//Parse our server revision number from server version
			$serverRevison = preg_replace("/[^0-9]+/", '', substr($serverVersion, strpos($serverVersion, "rev")));

			//If revision number is not numeric something went wrong in the parsing. If so return false.
			if(!is_numeric($serverRevison)) {
				self::$ISERROR = "Error: Parsed server version was non numeric.";
				return false;
			}

			return $serverRevison;
		}

		/*###############################################
		 * Shutdown the iSymphony server
		 * Takes: nothing
		 * Returns: nothing
		 */ 
		static function shutdownISymphonyServer() {

			//Move to server mode
			if(!self::moveToServer()) {
				return false;
			}

			//Shutdown server
			self::sendAndRecive("shutdown");
		}

		/*###############################################
		 * Reload the iSymphony server
		 * Takes: nothing
		 * Returns: nothing
		 */ 
		static function reloadISymphonyServer() {

			//Move to server mode
			if(!self::moveToServer()) {
				return false;
			}

			//Reload server
			self::sendAndRecive("reload");
		}

		/*###############################################
		 * Reload a location
		 * Takes: location name
		 * Returns: true if successful or false if error occurred 
		 */ 
		static function reloadISymphonyLocation($location) {

			//Move to location mode
			if(!self::moveToLocation($location)) {
				return false;
			}

			//Reload location
			return self::checkAndSetErrorNone("reload");
		}

		/*###############################################
		 * Gets a locations Asterisk connection status
		 * Takes: location name
		 * Returns: connection status if successful or false if error occurred  
		 */ 
		static function getiSymphonyLocationConnectionStatus($location) {

			//Move to location mode
			if(!self::moveToLocation($location)) {
				return false;
			}

			//Reload location
			return self::checkAndSetErrorString("connection");
		}

		/*###############################################
		 * Checks for avaliable software updates
		 * Takes: nothing
		 * Returns: "Update Available: <version>" if an update is available, 
		 * 			"No Update Available" if there is no update available, and 
		 * 			false if an error occurred   
		 */ 
		static function checkForiSymphonyUpdates() {

			//Check for updates
			return self::checkAndSetErrorString("check_for_updates");
		}

		/*###############################################
		 * Perform software updates
		 * Takes: nothing
		 * Returns: nothing
		 */ 
		static function iSymphonyUpdate() {

			//Perform updates
			return self::checkAndSetErrorString("update");
		}

		/*###############################################
		 * Cancel currently running update
		 * Takes: nothing
		 * Returns: nothing
		 */ 
		static function iSymphonyCancelUpdate() {

			//Cancel update
			return self::checkAndSetErrorString("cancel_update");
		}

		/*###############################################
		 * Get current update state
		 * Takes: nothing
		 * Returns: an instance of ISymphonyUpdateState which describes the current state of an update or false if an error occurred. See ISymphonyUpdateState class definition for more detail.  
		 */ 
		static function iSymphonyGetUpdateState() {

		 	//Get list of properties
			if(($propertyArray = self::checkAndSetErrorList("update_state")) !== false) {

				//Convert return array to associative array
				$propertyArray = self::convertPropertyArray($propertyArray);

				//Create object from properties
				$object = new ISymphonyUpdateState;

				//Set properties of object
				foreach(self::$updateStatePropertyArray as $val) {

					//Check if key exists in the associative array if it dose modify object if not set error and return false
					if(array_key_exists($val, $propertyArray)) {
						$object->$val = $propertyArray[$val];
					} else {
						self::$ISERROR = "Error: property \"$val\" was not provided by the server.";
						return false;
					}
				}		

				return $object;
			} else {
				return false;
			}
		}

		//Permission override activation methods-----------------------------------------------------------
		/*###############################################
		 * Activates a profile's local permission overrides
		 * Takes: location name, tenant name, profile name
		 * Returns: true if successful or false if error occurred 
		 */ 
		static function activateISymphonyLocalOverridePermissions($location, $tenant, $profile) {

			//Move to profile mode
			if(!self::moveToProfile($location, $tenant, $profile)) {
				return false;
			}

			//Reload location
			return self::checkAndSetErrorNone("activate local");
		}

		/*###############################################
		 * Deactivates a profile's local permission overrides
		 * Takes: location name, tenant name, profile name
		 * Returns: true if successful or false if error occurred 
		 */ 
		static function deactivateISymphonyLocalOverridePermissions($location, $tenant, $profile) {

			//Move to profile mode
			if(!self::moveToProfile($location, $tenant, $profile)) {
				return false;
			}

			//Reload location
			return self::checkAndSetErrorNone("deactivate local");
		}

		/*###############################################
		 * Activates a profile's park permission overrides
		 * Takes: location name, tenant name, profile name
		 * Returns: true if successful or false if error occurred 
		 */ 
		static function activateISymphonyParkOverridePermissions($location, $tenant, $profile) {

			//Move to profile mode
			if(!self::moveToProfile($location, $tenant, $profile)) {
				return false;
			}

			//Reload location
			return self::checkAndSetErrorNone("activate park");
		}

		/*###############################################
		 * Deactivates a profile's park permission overrides
		 * Takes: location name, tenant name, profile name
		 * Returns: true if successful or false if error occurred 
		 */ 
		static function deactivateISymphonyParkOverridePermissions($location, $tenant, $profile) {

			//Move to profile mode
			if(!self::moveToProfile($location, $tenant, $profile)) {
				return false;
			}

			//Reload location
			return self::checkAndSetErrorNone("deactivate park");
		}

		/*******************************
		* HELPERS *
		*******************************/
		/*###############################################
		 * Sends a message to the server and listens for a reply
		 * Takes: request
		 * Returns: an error or the server reply
		 */
		static function sendAndRecive($request, $read = true) {
			//Add new line to request
			$request = $request . self::$NEW_LINE;

			//Check if socket is initialized 
			if((self::$SOCKET !== null) && (self::$SOCKET !== false)) {

				//Send request
				if(socket_write(self::$SOCKET, $request, strlen($request)) === false) {
					return "Error: Could not write to socket. " . socket_strerror(socket_last_error());
				}
			
				//Recieve reply
				if($read) {
					if(($data = self::readCLIData()) !== false) {
						return preg_replace("/.*\s#\s/", "", $data);
					} else {
						return "Error: Could not read from socket. " . socket_strerror(socket_last_error());
					}
				} else {
					return "";
				}
	
			} else {
				return "Error: Socket not connected. Call iSymphonyConnect() to initialize connection.";
			}	
		}

		/*###############################################
		 * Reads data from a socket untill the prompt is reached
		 * Takes: nothing
		 * Returns: data read from the socket or false on eror
		 */	
		static function readCLIData() {
			$returnVal = "";
			while(($buf = socket_read(self::$SOCKET, self::$READ_LENGTH, PHP_BINARY_READ)) !== false) {
				$returnVal .= $buf;
				if(self::endsWith($returnVal," " . self::$PROMPT_TERMINATOR . " ")){
					break;
				}
			}
			
			if($buf === false) {
				return false;
			}
			
			return $returnVal;
		}
		
		/*###############################################
		 * Checks if reply was an error
		 * Takes: server reply
		 * Returns: true or false
		 */	
		static function isError($reply) {
			return strpos($reply, "Error") === 0;
		}	

		/*###############################################
		 * Makes a request that returns nothing and checks reply for error. If error is found $ISERROR is set
		 * Takes: request
		 * Returns: true if successful and false if error 
		 */
		static function checkAndSetErrorNone($request) {
			if(self::isError($reply = self::sendAndRecive($request))) {
				self::$ISERROR = $reply;
				return false;
			} else {
				return true;
			}
		}

		/*###############################################
		 * Makes a request that returns a string and checks reply for error. If error is found $ISERROR is set
		 * Takes: request
		 * Returns: the string if successful and false if error 
		 */
		static function checkAndSetErrorString($request) {	
			if(self::isError($reply = self::sendAndRecive($request))) {
				self::$ISERROR = $reply;
				return false;
			} else {
				return trim($reply);
			}
		}

		/*###############################################
		 * Makes a request that returns a list and checks reply for error. If error is found $ISERROR is set
		 * Takes: request
		 * Returns: an array of values if successful and false if error 
		 */
		static function checkAndSetErrorList($request) {

			if(self::isError($reply = self::sendAndRecive($request))) {
				self::$ISERROR = $reply;
				return false;
			} else {
				$explodeArray = explode(self::$NEW_LINE, trim($reply));
				$returnArray = array();

				//Search array for empty values and remove them
				foreach($explodeArray as $val) {
					if(strlen($val) != 0) {
						array_push($returnArray, $val);
					}
				}

				return $returnArray;
			}
		}

		/*###############################################
		 * Coverts a property list into a associative key => value array 
		 * Takes: an array of properties with values structured like so (property = value)
		 * Returns: an associative array with the structure (property => value)
		 */
		static function convertPropertyArray($array) {

			$returnArray = array();

			foreach($array as $val) {

				//Check if the property has a value and set the key value pair appropriately 
				if(self::endsWith($val," =")) {
					$keyValuePair = array(rtrim($val," =") ,"");
				} else if(self::endsWith($val," = ")) {
					$keyValuePair = array(rtrim($val," = "),"");
				} else {
					$keyValuePair = explode(" = ", $val);
				}

				$returnArray[$keyValuePair[0]] = $keyValuePair[1];
			}

			return $returnArray;
		}

		/*###############################################
		 * Checks if a string ends with another specified string
		 * Takes: a string, the string you wish to check is at the end
		 * Returns: true if $endString is at the end of $string false if not
		 */
		static function endsWith($string, $endString){
		    return strrpos($string, $endString) === strlen($string)-strlen($endString);
		}

		/*###############################################
		 * Removes an element from an array by value
		 * Takes: an array and the element to be removed
		 * Returns: and new re-orderd array without the removed element
		 */
		static function remove_element($arr, $val){
			foreach ($arr as $key => $value){
				if ($arr[$key] == $val){
					unset($arr[$key]);
				}
			}
			return $arr = array_values($arr);
		}

		/*###############################################
		 * Sets the current state of the tracker variables
		 * Takes: the new location, tenant, profile, and/or group
		 * Returns: nothing
		 */
		static function update_tracker($location = null, $tenant = null, $profile = null, $group = null) {
			self::$CURRENT_LOCATION = $location;
			self::$CURRENT_TENANT = $tenant;
			self::$CURRENT_PROFILE = $profile;
			self::$CURRENT_GROUP = $group;
		}

		/*###############################################
		 * Checks to see if CLI is in the given location
		 * Takes: location
		 * Returns: true if the CLI is currently in this location and false otherwise
		 */
		static function in_location($location) {
			return (self::$CURRENT_LOCATION === $location);
		}

		/*###############################################
		 * Checks to see if CLI is in the given tenant
		 * Takes: location and tenant
		 * Returns: true if the CLI is currently in this tenant and false otherwise
		 */
		static function in_tenant($location, $tenant) {
			return ((self::$CURRENT_LOCATION === $location) && 
					(self::$CURRENT_TENANT === $tenant));
		}

		/*###############################################
		 * Checks to see if CLI is in the given profile
		 * Takes: location, tenant, and profile
		 * Returns: true if the CLI is currently in this profile and false otherwise
		 */
		static function in_profile($location, $tenant, $profile) {
			return ((self::$CURRENT_LOCATION === $location) && 
					(self::$CURRENT_TENANT === $tenant) &&
					(self::$CURRENT_PROFILE === $profile));
		}

		/*###############################################
		 * Checks to see if CLI is in the given group
		 * Takes: location, tenant, and group
		 * Returns: true if the CLI is currently in this group and false otherwise
		 */
		static function in_group($location, $tenant, $group) {
			return ((self::$CURRENT_LOCATION === $location) && 
					(self::$CURRENT_TENANT === $tenant) &&
					(self::$CURRENT_GROUP === $group));
		}

		/*******************************
		* NAVIGATION *
		*******************************/

		/*###############################################
		 * Moves CLI to server mode
		 * Takes: nothing
		 * Returns: true if successful or false if not
		 */
		static function moveToServer() {

			//Move to server
			$moved = self::checkAndSetErrorNone("server");

			//Update tracker
			if($moved) {
				self::update_tracker();
			}

			return $moved;
		}

		/*###############################################
		 * Moves CLI to location mode
		 * Takes: location name
		 * Returns: true if successful or false if not
		 */
		static function moveToLocation($location) {

			//Move to location
			$moved = self::checkAndSetErrorNone("location $location");
			//Update tracker
			if($moved) {
				self::update_tracker($location);
			}

			return $moved;
		}

		/*###############################################
		 * Moves CLI to tenant mode
		 * Takes: location name, tenant name
		 * Returns: true if successful or false if not
		 */
		static function moveToTenant($location, $tenant) {

			//Move to location
			if(!self::in_location($location)) {
				if(!self::moveToLocation($location)) {
					return false;
				}
			}

			//Move to tenant
			$moved = self::checkAndSetErrorNone("tenant $tenant");

			//Update tracker
			if($moved) {
				self::update_tracker($location, $tenant);
			}

			return $moved;
		}

		/*###############################################
		 * Moves CLI to extension mode
		 * Takes: location name, tenant name, extension name
		 * Returns: true if successful or an error if not
		 */
		static function moveToExtension($location, $tenant, $extension) {

			//Move to tenant
			if(!self::in_tenant($location, $tenant)) {
				if(!self::moveToTenant($location, $tenant)) {
					return false;
				}
			}

			//Move to extension
			$moved = self::checkAndSetErrorNone("extension $extension");

			//Update tracker
			if($moved) {
				self::update_tracker($location, $tenant);
			}

			return $moved;
		}

		/*###############################################
		 * Moves CLI to profile mode
		 * Takes: location name, tenant name, profile name
		 * Returns: true if successful or an error if not
		 */
		static function moveToProfile($location, $tenant, $profile) {

			//Move to tenant
			if(!self::in_tenant($location, $tenant)) {
				if(!self::moveToTenant($location, $tenant)) {
					return false;
				}
			}

			//Move to profile
			$moved = self::checkAndSetErrorNone("profile $profile");

			//Update tracker
			if($moved) {
				self::update_tracker($location, $tenant, $profile);
			}

			return $moved;
		}

		/*###############################################
		 * Moves CLI to queue mode
		 * Takes: location name, tenant name, queue name
		 * Returns: true if successful or an error if not
		 */
		static function moveToQueue($location, $tenant, $queue) {

			//Move to tenant
			if(!self::in_tenant($location, $tenant)) {
				if(!self::moveToTenant($location, $tenant)) {
					return false;
				}
			}

			//Move to queue
			$moved = self::checkAndSetErrorNone("queue $queue");

			//Update tracker
			if($moved) {
				self::update_tracker($location, $tenant);
			}

			return $moved;
		}

		/*###############################################
		 * Moves CLI to conference room mode
		 * Takes: location name, tenant name, room name
		 * Returns: true if successful or an error if not
		 */
		static function moveToConferenceRoom($location, $tenant, $room) {

			//Move to tenant
			if(!self::in_tenant($location, $tenant)) {
				if(!self::moveToTenant($location, $tenant)) {
					return false;
				}
			}

			//Move to conference room
			$moved = self::checkAndSetErrorNone("meetme $room");

			//Update tracker
			if($moved) {
				self::update_tracker($location, $tenant);
			}

			return $moved;
		}

		/*###############################################
		 * Moves CLI to status mode
		 * Takes: location name, tenant name, status name
		 * Returns: true if successful or an error if not
		 */
		static function moveToStatus($location, $tenant, $status) {

			//Move to tenant
			if(!self::in_tenant($location, $tenant)) {
				if(!self::moveToTenant($location, $tenant)) {
					return false;
				}
			}

			//Move to status
			$moved = self::checkAndSetErrorNone("status $status");

			//Update tracker
			if($moved) {
				self::update_tracker($location, $tenant);
			}

			return $moved;
		}

		/*###############################################
		 * Moves CLI to permission group mode
		 * Takes: location name, tenant name, group name
		 * Returns: true if successful or an error if not
		 */
		static function moveToPermissionGroup($location, $tenant, $group) {

			//Move to tenant
			if(!self::in_tenant($location, $tenant)) {
				if(!self::moveToTenant($location, $tenant)) {
					return false;
				}
			}

			//Move to permission group
			$moved = self::checkAndSetErrorNone("permgroup $group");

			//Update tracker
			if($moved) {
				self::update_tracker($location, $tenant, $group);
			}

			return $moved;
		}

		/*###############################################
		 * Moves CLI to default local permission mode
		 * Takes: nothing
		 * Returns: true if successful or an error if not
		 */
		static function moveToDefaultLocalPermission() {

			//Move to server
			if(!self::moveToServer()) {
				return false;
			}

			//Move to default local permissions
			$moved = self::checkAndSetErrorNone("permission local");

			//Update tracker
			if($moved) {
				self::update_tracker();
			}

			return $moved;
		}

		/*###############################################
		 * Moves CLI to default remote permission mode
		 * Takes: nothing
		 * Returns: true if successful or an error if not
		 */
		static function moveToDefaultRemotePermission() {

			//Move to server
			if(!self::moveToServer()) {
				return false;
			}

			//Move to default remote permissions
			$moved = self::checkAndSetErrorNone("permission remote");

			//Update tracker
			if($moved) {
				self::update_tracker();
			}

			return $moved;
		}

		/*###############################################
		 * Moves CLI to default queue permission mode
		 * Takes: nothing
		 * Returns: true if successful or an error if not
		 */
		static function moveToDefaultQueuePermission() {

			//Move to server
			if(!self::moveToServer()) {
				return false;
			}

			//Move to default queue permissions
			$moved = self::checkAndSetErrorNone("permission queue");

			//Update tracker
			if($moved) {
				self::update_tracker();
			}

			return $moved;
		}

		/*###############################################
		 * Moves CLI to default park permission mode
		 * Takes: nothing
		 * Returns: true if successful or an error if not
		 */
		static function moveToDefaultParkPermission() {

			//Move to server
			if(!self::moveToServer()) {
				return false;
			}

			//Move to default park permissions
			$moved = self::checkAndSetErrorNone("permission park");

			//Update tracker
			if($moved) {
				self::update_tracker();
			}

			return $moved;
		}

		/*###############################################
		 * Moves CLI to default conference room permission mode
		 * Takes: nothing
		 * Returns: true if successful or an error if not
		 */
		static function moveToDefaultConferenceRoomPermission() {

			//Move to server
			if(!self::moveToServer()) {
				return false;
			}

			//Move to default conference room permissions
			$moved = self::checkAndSetErrorNone("permission meetme");

			//Update tracker
			if($moved) {
				self::update_tracker();
			}

			return $moved;
		}

		/*###############################################
		 * Moves CLI to local permission override mode
		 * Takes: location name, tenant name, profile name
		 * Returns: true if successful or an error if not
		 */
		static function moveToLocalOverridePermission($location, $tenant, $profile) {

			//Move to profile
			if(!self::in_profile($location, $tenant, $profile)) {
				if(!self::moveToProfile($location, $tenant, $profile)) {
					return false;
				}
			}

			//Move to local permission override
			$moved =  self::checkAndSetErrorNone("permission local");

			//Update tracker
			if($moved) {
				self::update_tracker($location, $tenant, $profile);
			}

			return $moved;
		}

		/*###############################################
		 * Moves CLI to park permission override mode
		 * Takes: location name, tenant name, profile name
		 * Returns: true if successful or an error if not
		 */
		static function moveToParkOverridePermission($location, $tenant, $profile) {

			//Move to profile
			if(!self::in_profile($location, $tenant, $profile)) {
				if(!self::moveToProfile($location, $tenant, $profile)) {
					return false;
				}
			}

			//Move to park permission override
			$moved = self::checkAndSetErrorNone("permission park");

			//Update tracker
			if($moved) {
				self::update_tracker($location, $tenant, $profile);
			}

			return $moved;
		}

		/*###############################################
		 * Moves CLI to remote permission override mode
		 * Takes: location name, tenant name, profile name, extension name
		 * Returns: true if successful or an error if not
		 */
		static function moveToRemoteOverridePermission($location, $tenant, $profile, $extension) {

			//Move to profile
			if(!self::in_profile($location, $tenant, $profile)) {
				if(!self::moveToProfile($location, $tenant, $profile)) {
					return false;
				}
			}

			//Move to remote permission override
			$moved = self::checkAndSetErrorNone("permission remote $extension");

			//Update tracker
			if($moved) {
				self::update_tracker($location, $tenant, $profile);
			}

			return $moved;
		}

		/*###############################################
		 * Moves CLI to queue permission override mode
		 * Takes: location name, tenant name, profile name, queue name
		 * Returns: true if successful or an error if not
		 */
		static function moveToQueueOverridePermission($location, $tenant, $profile, $queue) {

			//Move to profile
			if(!self::in_profile($location, $tenant, $profile)) {
				if(!self::moveToProfile($location, $tenant, $profile)) {
					return false;
				}
			}

			//Move to queue permission override
			$moved = self::checkAndSetErrorNone("permission queue $queue");

			//Update tracker
			if($moved) {
				self::update_tracker($location, $tenant, $profile);
			}

			return $moved;
		}

		/*###############################################
		 * Moves CLI to conference room permission override mode
		 * Takes: location name, tenant name, profile name, queue name
		 * Returns: true if successful or an error if not
		 */
		static function moveToConferenceRoomOverridePermission($location, $tenant, $profile, $room) {

			//Move to profile
			if(!self::in_profile($location, $tenant, $profile)) {
				if(!self::moveToProfile($location, $tenant, $profile)) {
					return false;
				}
			}

			//Move to conference room permission override
			$moved = self::checkAndSetErrorNone("permission meetme $room");

			//Update tracker
			if($moved) {
				self::update_tracker($location, $tenant, $profile);
			}

			return $moved;
		}

		/*###############################################
		 * Moves CLI to local group permission mode
		 * Takes: location name, tenant name, permission group name
		 * Returns: true if successful or an error if not
		 */
		static function moveToLocalGroupPermission($location, $tenant, $group) {

			//Move to permission group
			if(!self::in_group($location, $tenant, $group)) {
				if(!self::moveToPermissionGroup($location, $tenant, $group)) {
					return false;
				}
			}

			//Move to group local permission
			$moved = self::checkAndSetErrorNone("permission local");

			//Update tracker
			if($moved) {
				self::update_tracker($location, $tenant, null, $group);
			}

			return $moved;
		}

		/*###############################################
		 * Moves CLI to park group permission mode
		 * Takes: location name, tenant name, permission group name
		 * Returns: true if successful or an error if not
		 */
		static function moveToParkGroupPermission($location, $tenant, $group) {

			//Move to permission group
			if(!self::in_group($location, $tenant, $group)) {
				if(!self::moveToPermissionGroup($location, $tenant, $group)) {
					return false;
				}
			}

			//Move to group park permission
			$moved = self::checkAndSetErrorNone("permission park");

			//Update tracker
			if($moved) {
				self::update_tracker($location, $tenant, null, $group);
			}

			return $moved;
		}

		/*###############################################
		 * Moves CLI to remote group permission mode
		 * Takes: location name, tenant name, permission group name, extension name
		 * Returns: true if successful or an error if not
		 */
		static function moveToRemoteGroupPermission($location, $tenant, $group, $extension) {

			//Move to permission group
			if(!self::in_group($location, $tenant, $group)) {
				if(!self::moveToPermissionGroup($location, $tenant, $group)) {
					return false;
				}
			}

			//Move to group remote permission
			$moved = self::checkAndSetErrorNone("permission remote $extension");

			//Update tracker
			if($moved) {
				self::update_tracker($location, $tenant, null, $group);
			}

			return $moved;
		}

		/*###############################################
		 * Moves CLI to queue group permission mode
		 * Takes: location name, tenant name, permission group name, queue name
		 * Returns: true if successful or an error if not
		 */
		static function moveToQueueGroupPermission($location, $tenant, $group, $queue) {

			//Move to permission group
			if(!self::in_group($location, $tenant, $group)) {
				if(!self::moveToPermissionGroup($location, $tenant, $group)) {
					return false;
				}
			}

			//Move to group queue permission
			$moved = self::checkAndSetErrorNone("permission queue $queue");

			//Update tracker
			if($moved) {
				self::update_tracker($location, $tenant, null, $group);
			}

			return $moved;
		}

		/*###############################################
		 * Moves CLI to conference room group permission mode
		 * Takes: location name, tenant name, permission group name, conference room name
		 * Returns: true if successful or an error if not
		 */
		static function moveToConferenceRoomGroupPermission($location, $tenant, $group, $room) {

			//Move to permission group
			if(!self::in_group($location, $tenant, $group)) {
				if(!self::moveToPermissionGroup($location, $tenant, $group)) {
					return false;
				}
			}

			//Move to group conference room permission
			$moved = self::checkAndSetErrorNone("permission meetme $room");

			//Update tracker
			if($moved) {
				self::update_tracker($location, $tenant, null, $group);
			}

			return $moved;
		}


		/******************************
		* PROPERTY_ARRAYS *
		******************************/
		//Property array modification static functions--------------------------------------------------------------------------------------------------------------
		static function isymphony_reset_property_arrays() {
           	self::$serverPropertyArray = array("client_port","cli_port","username","password");
           	self::$locationPropertyArray = array("name","admin_password","asterisk_host","asterisk_port","asterisk_login","asterisk_password","originate_timeout","jabber_host","jabber_port");
           	self::$tenantPropertyArray = array("name","admin_password","originating_context","redirecting_context","music_on_hold_class","outside_line_number","record_file_name","record_file_extension","mix_mode");
           	self::$extensionPropertyArray = array("extension_val","name","cell_phone","email","peer","alt_origination_method","voice_mail","agent");
           	self::$profilePropertyArray = array("name","password","jabber_user_name","jabber_password","can_view_everyone_directory");
           	self::$queuePropertyArray = array("name","queue_val","extension_val","context");
           	self::$conferenceRoomPropertyArray = array("name","predefined","room_number","extension_val","context","announce_user_count","music_on_hold_for_single_user","exit_room_via_pound","present_menu_via_star","announce_user_join_leave","disable_join_leave_notification","record");
           	self::$permissionGroupPropertyArray = array("name");
           	self::$statusPropertyArray = array("name","type");
           	self::$localPermissionPropertyArray = array("call_voice_mail","hold_calls","transfer_call_to_voice_mail","mute","record","hangup","set_user_status_note","call_cell_phone","add_extension_directory","set_user_status","transfer_call_to_cell_phone","agent_login","add_temp_meetme_room");
           	self::$remotePermissionPropertyArray = array("call_voice_mail","transfer_to","transfer_call_to_voice_mail","steal_call","record","originate_to","email","call_cell_phone","barge","transfer_call_to_cell_phone","chat","agent_login","view_calls","view_caller_id");
           	self::$parkPermissionPropertyArray = array("park_call","set_parked_call_note","unpark_call");
           	self::$queuePermissionPropertyArray = array("transfer_to","steal_call");
           	self::$conferenceRoomPermissionPropertyArray = array("steal_call","transfer_to","originate_to","mute_users","kick_users");
           	self::$updateStatePropertyArray = array("task","message","work","work_done");
          }
		static function isymphony_modify_property_arrays_2_1() {
			//New modifications
			array_push(self::$locationPropertyArray, "device_user_mode");

			array_push(self::$tenantPropertyArray, "page_status_enabled");
			array_push(self::$tenantPropertyArray, "page_context");

			self::$extensionPropertyArray = self::remove_element(self::$extensionPropertyArray, "agent");
		}

		static function isymphony_modify_property_arrays_2_1_1489() {
			//Apply 2.1 modifications
			self::isymphony_modify_property_arrays_2_1();

			//New modifications
			array_push(self::$locationPropertyArray, "reload_on_dial_plan_reload");
			array_push(self::$locationPropertyArray, "jabber_domain");
			array_push(self::$locationPropertyArray, "jabber_resource");

			array_push(self::$profilePropertyArray, "jabber_host");
			array_push(self::$profilePropertyArray, "jabber_domain");
			array_push(self::$profilePropertyArray, "jabber_resource");
			array_push(self::$profilePropertyArray, "jabber_port");
		}

		static function isymphony_modify_property_arrays_2_1_1493() {
			//Apply 2.1 1489 modifications
			self::isymphony_modify_property_arrays_2_1_1489();

			//New modifications
			array_push(self::$extensionPropertyArray, "auto_answer");
		}

		static function isymphony_modify_property_arrays_2_1_1660() {

			//Apply 2.1 1493 modifications
			self::isymphony_modify_property_arrays_2_1_1493();

			//New modifications
			array_push(self::$tenantPropertyArray, "agent_login_context");
		}

		static function isymphony_modify_property_arrays_2_1_1678() {
			//Apply 2.1 1660 modifications
			self::isymphony_modify_property_arrays_2_1_1660();

			//New modifications
			array_push(self::$serverPropertyArray, "hold_extension_button_enabled");
			array_push(self::$serverPropertyArray, "park_extension_button_enabled");
			array_push(self::$serverPropertyArray, "record_extension_button_enabled");
			array_push(self::$serverPropertyArray, "hangup_extension_button_enabled");
			array_push(self::$serverPropertyArray, "cell_phone_extension_button_enabled");
			array_push(self::$serverPropertyArray, "voice_mail_extension_button_enabled");
			array_push(self::$serverPropertyArray, "agent_extension_button_enabled");
			array_push(self::$serverPropertyArray, "email_extension_button_enabled");
			array_push(self::$serverPropertyArray, "chat_extension_button_enabled");
			array_push(self::$serverPropertyArray, "mute_extension_button_enabled");
			array_push(self::$serverPropertyArray, "barge_extension_button_enabled");

			array_push(self::$locationPropertyArray, "mask_jabber_user_name_with_profile");
			array_push(self::$locationPropertyArray, "force_client_update");
			array_push(self::$locationPropertyArray, "voice_mail_directory");

			array_push(self::$extensionPropertyArray, "voice_mail_context");
			array_push(self::$extensionPropertyArray, "originating_context");
			array_push(self::$extensionPropertyArray, "redirecting_context");
			array_push(self::$extensionPropertyArray, "agent_login_context");
			array_push(self::$extensionPropertyArray, "music_on_hold_class");

			array_push(self::$localPermissionPropertyArray, "listen_to_voice_mail");
			array_push(self::$localPermissionPropertyArray, "delete_voice_mail");
			array_push(self::$localPermissionPropertyArray, "move_voice_mail");
			array_push(self::$localPermissionPropertyArray, "pause_member");

			array_push(self::$remotePermissionPropertyArray, "forward_voice_mail_to");
			array_push(self::$remotePermissionPropertyArray, "set_user_status");
			array_push(self::$remotePermissionPropertyArray, "set_user_status_note");
			array_push(self::$remotePermissionPropertyArray, "pause_member");

			array_push(self::$queuePermissionPropertyArray, "dynamic_login");
		}

		static function isymphony_modify_property_arrays_2_1_1706() {

			//Apply 2.1 1678 modifications
			self::isymphony_modify_property_arrays_2_1_1678();

			//New modifications
			array_push(self::$serverPropertyArray, "http_port");
			array_push(self::$serverPropertyArray, "update_site_url");
		}

		static function isymphony_modify_property_arrays_2_1_1736() {

			//Apply 2.1 1706 modifications
			self::isymphony_modify_property_arrays_2_1_1706();

			//New modifications
			array_push(self::$locationPropertyArray, "log_jabber_messages");
		}
		
		static function isymphony_modify_property_arrays_2_1_1957() {

			//Apply 2.1 1736 modifications
			self::isymphony_modify_property_arrays_2_1_1736();

			//New modifications
			array_push(self::$extensionPropertyArray, "agent_login_interface"); 
			array_push(self::$extensionPropertyArray, "agent_login_penalty"); 
			array_push(self::$queuePermissionPropertyArray, "display"); 
			array_push(self::$conferenceRoomPermissionPropertyArray, "display");
		}

		static function isymphony_modify_property_arrays_2_2_2203() {

			//Apply 2.2 1957 modifications
			self::isymphony_modify_property_arrays_2_1_1957();

			//New modifications
			array_push(self::$extensionPropertyArray, "agent_login_name");
		}
		static function isymphony_modify_property_arrays_2_2_2205() {

			//Apply 2.2 2203 modifications
			self::isymphony_modify_property_arrays_2_2_2203();

			//New modifications

			array_push(self::$tenantPropertyArray, "ringing_status_enabled");

		}
		static function isymphony_modify_property_arrays_2_2_2257() {
	
           	//Apply 2.2 2205 modifications
           	self::isymphony_modify_property_arrays_2_2_2205();
	
           	//New modifications
           	array_push(self::$locationPropertyArray, "use_voice_mail_agent");
           	array_push(self::$locationPropertyArray, "voice_mail_agent_host");
           	array_push(self::$locationPropertyArray, "voice_mail_agent_port");
           	array_push(self::$locationPropertyArray, "voice_mail_agent_user_name");
           	array_push(self::$locationPropertyArray, "voice_mail_agent_password");

           	array_push(self::$remotePermissionPropertyArray, "do_not_disturb");
           	array_push(self::$localPermissionPropertyArray, "do_not_disturb");
        }
		static function getconf($filename) {
		  $file = file($filename);
		  foreach($file as $line => $cont){
		  	if(substr($cont,0,1)!='#'){
		  		$d=explode('=',$cont);
		  		if(isset($d['0'])&& isset($d['1'])){$conf[trim($d['0'])]=trim($d['1']);}
		  	}
		  }
		  return $conf;
		}
	}
?>
