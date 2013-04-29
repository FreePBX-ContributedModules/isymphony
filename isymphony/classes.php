<?php
/*
 *Name         : classes.php
 *Author       : Michael Yara
 *Created      : June 15, 2008
 *Last Updated : Dec 10, 2009
 *History      : 0.8
 *Purpose      : Contains declarations of all property classes. 
 *Copyright    : 2008 HEHE Enterprises, LLC
 */

require_once(dirname(__FILE__) . '/isymphony.php');
/*###############################################
 * Class that represents the server properties
 * Attributes: 
 *		client_port: Port that server listens for client connections on.(INTEGER)
 *		cli_port: Port that servers listens for CLI requests on.(INTEGER)
 *		http_port: Port used for embedded http server.(INTEGER)
 *		username: Admin username.(STRING)
 *		password: Admin password.(STRING)
 *		update_site_url: URL used for updates.(INTEGER)
 *		hold_extension_button_enabled: Enable hold button.(BOOLEAN[true or false])
 *		park_extension_button_enabled: Enable park button.(BOOLEAN[true or false])
 *		record_extension_button_enabled: Enable record button.(BOOLEAN[true or false])
 *		hangup_extension_button_enabled: Enable hangup button.(BOOLEAN[true or false])
 *		cell_phone_extension_button_enabled: Enable cell phone button.(BOOLEAN[true or false])
 *		voice_mail_extension_button_enabled: Enable voice mail button.(BOOLEAN[true or false])
 *		agent_extension_button_enabled: Enable agent button.(BOOLEAN[true or false])
 *		email_extension_button_enabled: Enable email button.(BOOLEAN[true or false])
 *		chat_extension_button_enabled: Enable chat button.(BOOLEAN[true or false])
 *		mute_extension_button_enabled: Enable mute button.(BOOLEAN[true or false])
 *		barge_extension_button_enabled: Enable barge button.(BOOLEAN[true or false])
 *		do_not_disturb_extension_button_enabled: Enable DND button.(BOOLEAN[true or false])
 *
 * Methods:
 *		update: Commits changes made to the property configuration
 *			Takes: nothing
 *			Returns: true if successful false if not
 */
 class ISymphonyServer {
 	
 	//Property attributes
 	var $client_port = null;
 	var $cli_port = null;
 	var $http_port = null;
 	var $username = null;
 	var $password = null;
 	var $update_site_url = null;
 	var $hold_extension_button_enabled = null;
 	var $park_extension_button_enabled = null;
 	var $record_extension_button_enabled = null;
 	var $hangup_extension_button_enabled = null;
 	var $cell_phone_extension_button_enabled = null;
 	var $voice_mail_extension_button_enabled = null;
 	var $agent_extension_button_enabled = null;
 	var $email_extension_button_enabled = null;
 	var $chat_extension_button_enabled = null;
 	var $mute_extension_button_enabled = null;
 	var $barge_extension_button_enabled = null;
 	var $do_not_disturb_extension_button_enabled = null;
 	 		
 	//Methods
 	function update() {

 		//Move to server mode
		if(!iSymphony::moveToServer()) {
			return false;
		}
		
		//Modify properties
		foreach(iSymphony::$serverPropertyArray as $val) {
			if($this->$val !== null) {
				if(!iSymphony::checkAndSetErrorNone("$val {$this->$val}")) {
					return false;
				}
			}
		}
		
		//Commit changes
		return iSymphony::checkAndSetErrorNone("commit");
 	}
 }

/*###############################################
 * Class that represents a locations's properties
 * Attributes: 
 *		name: Location name(STRING)
 *		admin_password: Admin password(STRING)
 *		asterisk_host: Asterisk manager host name or IP(STRING)
 *		asterisk_port: Asterisk manager port(INTEGER)
 *		asterisk_login: Asterisk manager username(STRING)
 *		asterisk_password: Asterisk manager password(STRING)
 *		originate_timeout: Amount of time an originator will be rung on an origination event in milliseconds(INTEGER)
 *		jabber_host: Jabber host name(STRING)
 *		jabber_domain: Jabber domain name(STRING)
 *		jabber_resource: Jabber resource name(STRING)
 *		jabber_port: Jabber port(INTEGER)
 *		mask_jabber_user_name_with_profile: Overwrite display of Jabber usernames with configured profile names.(BOOLEAN[true or false])
 *		log_jabber_messages: If set to true all jabber conversations will be logged on the server.(BOOLEAN[true or false])
 *		device_user_mode: Handle user device mappings when in FreePBX Device User Mode.(BOOLEAN[true or false])
 *		reload_on_dial_plan_reload: Flag used to reload this location when the dial plan reloads.(BOOLEAN[true or false])
 *		force_client_update: If set to true users will not be prompted for update authorization.(BOOLEAN[true or false])
 *      voice_mail_directory: Root directory where voice mail files are stored.(STRING)
 *      use_voice_mail_agent: Flag specify if the server should use the local file system or a voice mail agent to server voicemail.(BOOLEAN[true or false])
 *      voice_mail_agent_host: Host or IP of voice mail agent server.(STRING)
 *      voice_mail_agent_port: Port of voice mail agent server.(INTEGER)
 *      voice_mail_agent_user_name: Username of voice mail agent server.(STRING)
 *      voice_mail_agent_password: Password of voice mail agent server.(STRING)
 *
 * Methods:
 *		update: Commits changes made to the property configuration
 *			Takes: nothing
 *			Returns: true if successful false if not
 *		add: Adds the given location with specified configuration
 *			Takes: nothing
 *			Returns: true if successful false if not
 */
 class ISymphonyLocation {
 	
 	//Mode attributes
 	var $original_name = "";
 	
 	//Property attributes
	var $name = null;
	var $admin_password = null;
	var $asterisk_host = null;
	var $asterisk_port = null;
	var $asterisk_login = null;
	var $asterisk_password = null;
	var $originate_timeout = null;
	var $jabber_host = null;
	var $jabber_domain = null;
	var $jabber_resource = null;
	var $jabber_port = null;
	var $mask_jabber_user_name_with_profile = null;
	var $log_jabber_messages = null;
	var $device_user_mode = null;
	var $reload_on_dial_plan_reload = null;
	var $force_client_update = null;
	var $voice_mail_directory = null;
	var $use_voice_mail_agent = null;
	var $voice_mail_agent_host = null;
	var $voice_mail_agent_port = null;
	var $voice_mail_agent_user_name = null;
	var $voice_mail_agent_password = null;
	
 	//Methods
 	function update() {
 
 		//Move to location mode
		if(!iSymphony::moveToLocation($this->original_name)) {
			return false;
		}
		
		//Modify properties
		foreach(iSymphony::$locationPropertyArray as $val) {
			if($this->$val !== null) {
				if(!iSymphony::checkAndSetErrorNone("$val {$this->$val}")) {
					return false;
				}
			}
		}
		
		//Commit changes
		if(iSymphony::checkAndSetErrorNone("commit")) {
			
			//Set mode properties if edit was successful so user can call subsequent commits
			$this->original_name = $this->name;
			return true;
			
		} else {
			return false;
		}
 	}
 	
 	function add() {

 		//Move to server mode
		if(!iSymphony::moveToServer()) {
			return false;
		}
		
		//Initiate new command
		if(!iSymphony::checkAndSetErrorNone("new location {$this->name}")) {
			return false;
		}
		
		//Modify properties
		foreach(iSymphony::$locationPropertyArray as $val) {
			if($this->$val !== null) {
				if(!iSymphony::checkAndSetErrorNone("$val {$this->$val}")) {
					return false;
				}
			}
		}
		
		//Commit addition
		if(iSymphony::checkAndSetErrorNone("commit")) {
			
			//Set mode properties if addition was successful so user can call commit
			$this->original_name = $this->name;
			return true;
			
		} else {
			return false;
		}
 	}
 }

/*###############################################
 * Class that represents a tenants's properties
 * Attributes: 
 *		name: Name of the tenant(STRING)
 *		admin_password: Admin password(STRING)
 *		originating_context: Context used for originating calls(STRING)
 *		redirecting_context: Context used for redirecting calls(STRING)
 *		music_on_hold_class: Music on hold class(STRING)
 *		record_file_name: File name used for recorded calls. See code legend in administration interface for a list of input variables(STRING)
 *		record_file_extension: File extensions used for recorded calls(STRING)
 *		mix_mode: Flag used to mix incoming and outgoing recorded call channels(BOOLEAN[true or false])
 *		page_status_enabled: Flag used to enable page status(BOOLEAN[true or false])
 *		page_context: Page context used to suppress paging status(STRING)
 *		outside_line_number: Number used to dial outside numbers(STRING)
 *		ringing_status_enabled: Flag used to specify if ringing status should be enabled(BOOLEAN[true or false])
 *
 * Methods:
 *		update: Commits changes made to the property configuration
 *			Takes: nothing
 *			Returns: true if successful false if not
 *		add: Adds the given tenant with specified configuration
 *			Takes: the location you wish to add the tenant to
 *			Returns: true if successful false if not
 */
 class ISymphonyTenant {
 	
 	//Mode attributes
 	var $mode_location = "";
 	var $original_name = "";
 	
 	//Property attributes
	var $name = null;
	var $admin_password = null;
	var $originating_context = null;
	var $redirecting_context = null;
	var $agent_login_context = null;
	var $music_on_hold_class = null;
	var $record_file_name = null;
	var $record_file_extension = null;
	var $mix_mode = null;
	var $page_status_enabled = null;
	var $page_context = null;
	var $outside_line_number = null;
	var $ringing_status_enabled = null;
	
 	//Methods
 	function update() {

 		//Move to tenant mode
		if(!iSymphony::moveToTenant($this->mode_location, $this->original_name)) {
			return false;
		}
		
		//Modify properties
		foreach(iSymphony::$tenantPropertyArray as $val) {
			if($this->$val !== null) {
				if(!iSymphony::checkAndSetErrorNone("$val {$this->$val}")) {
					return false;
				}
			}
		}
		
		//Commit changes
		if(iSymphony::checkAndSetErrorNone("commit")) {
			
			//Set mode properties if edit was successful so user can call subsequent commits
			$this->original_name = $this->name;
			return true;
			
		} else {
			return false;
		}
 	}
 	
 	function add($location) {

 		//Move to location mode
		if(!iSymphony::moveToLocation($location)) {
			return false;
		}
		
		//Initiate new command
		if(!iSymphony::checkAndSetErrorNone("new tenant {$this->name}")) {
			return false;
		}
		
		//Modify properties
		foreach(iSymphony::$tenantPropertyArray as $val) {
			if($this->$val !== null) {
				if(!iSymphony::checkAndSetErrorNone("$val {$this->$val}")) {
					return false;
				}
			}
		}
		
		//Commit addition
		if(iSymphony::checkAndSetErrorNone("commit")) {
			
			//Set mode properties if addition was successful so user can call commit
			$this->original_name = $this->name;
			$this->mode_location = $location;
			return true;
			
		} else {
			return false;
		}
 	}
 }
 
 /*###############################################
  * Class that represents an extension's properties
  * Attributes: 
  *		extension_val: Extension number(STRING)
  *		name: Extension display name(STRING)
  *		cell_phone: Cell phone number(STRING)
  *		email: Email address(STRING)
  *		peer: Peer associated with this extension(STRING)
  *		alt_origination_method: Alternate method for originating calls if it differers from the peer(STRING)
  *		voice_mail: Voice mail box number of this extension(STRING)
  *		voice_mail_context: Voice mail box context of this extension(STRING)
  *		originating_context: Originating context override(STRING)
  *		redirecting_context: Redirecting context override(STRING)
  *		agent_login_context: Agent login context override(STRING);
  * 		agent_login_interface: Dynamic agent login state interface(STRING);
  * 		agent_login_penalty: Dynamic agent login penalty(INTEGER);
  *		music_on_hold_class: Music on hold class override(STRING)
  *		agent: Agent number associated with this extension(STRING)
  *		auto_answer: Flag used to indicate if origination call back should be auto answered.
  *		agent_login_name: specifies the name used for a dynamic agent login(STRING)
  *
  * Methods:
  *		update: Commits changes made to the property configuration
  *			Takes: nothing
  *			Returns: true if successful false if not
  *		add: Adds the given tenant with specified configuration
  *			Takes: the location you wish to add the extension to, the tenant that you would like to add the extension to
  *			Returns: true if successful false if not
  */
  class ISymphonyExtension {
 	
 	//Mode attributes
 	var $mode_location = "";
 	var $mode_tenant = "";
 	var $original_extension_val = "";
 	
 	//Property attributes
	var $extension_val = null;
	var $name = null;
	var $cell_phone = null;
	var $email = null;
	var $peer = null;
	var $alt_origination_method = null;
	var $voice_mail = null;
	var $voice_mail_context = null;
	var $originating_context = null;
	var $redirecting_context = null;
	var $agent_login_context = null;
	var $agent_login_interface = null;
	var $agent_login_penalty = null;
	var $music_on_hold_class = null;
	var $agent = null;
	var $auto_answer = null;
	var $agent_login_name = null;
	
 	//Methods
 	function update() {

 		//Move to extension mode
		if(!iSymphony::moveToExtension($this->mode_location, $this->mode_tenant, $this->original_extension_val)) {
			return false;
		}
		
		//Modify properties
		foreach(iSymphony::$extensionPropertyArray as $val) {
			if($this->$val !== null) {
				if(!iSymphony::checkAndSetErrorNone("$val {$this->$val}")) {
					return false;
				}
			}
		}
		
		//Commit changes
		if(iSymphony::checkAndSetErrorNone("commit")) {
			
			//Set mode properties if edit was successful so user can call subsequent commits
			$this->original_extension_val = $this->extension_val;
			return true;
			
		} else {
			return false;
		}
 	}
 	
 	function add($location, $tenant) {
 	
 		//Move to tenant mode
		if(!iSymphony::moveToTenant($location, $tenant)) {
			return false;
		}
		
		//Initiate new command
		if(!iSymphony::checkAndSetErrorNone("new extension {$this->extension_val}")) {
			return false;
		}
		
		//Modify properties
		foreach(iSymphony::$extensionPropertyArray as $val) {
			if($this->$val !== null) {
				if(!iSymphony::checkAndSetErrorNone("$val {$this->$val}")) {
					return false;
				}
			}
		}
		
		//Commit addition
		if(iSymphony::checkAndSetErrorNone("commit")) {
			
			//Set mode properties if addition was successful so user can call commit
			$this->original_extension_val = $this->extension_val;
			$this->mode_location = $location;
			$this->mode_tenant = $tenant;
			return true;
			
		} else {
			return false;
		}
 	}
 }
 
  /*###############################################
  * Class that represents a profile's properties
  * Attributes: 
  * 	name: Username for profile(STRING)
  * 	password: Password for profile(STRING)
  *		jabber_host: Jabber host name(STRING)
  *		jabber_domain: Jabber domain name(STRING)
  *		jabber_resource: Jabber resource name(STRING)
  *		jabber_port: Jabber port(INTEGER)
  * 	jabber_user_name: Jabber username(STRING)
  * 	jabber_password: Jabber password(STRING)
  * 	can_view_everyone_directory: Flag that allows this profile to see the Everyone directory(BOOLEAN[true or false])
  * 	unregistered_color: Sets the unregistered color.(RGB)
  *		registered_color: Sets the registered color.(RGB)
  *		ringing_color: Sets the ringing color.(RGB)
  * 	pending_color: Sets the pending color.(RGB)
  *		local_linked_color: Sets the local linked color.(RGB)
  *		outside_linked_color: Sets the outside linked color.(RGB)
  *		queue_linked_color: Sets the queue linked color.(RGB)
  *		chan_spy_barge: Sets the usage of ChanSpy to perform barges(BOOLEAN[true or false])
  *
  * Methods:
  *		update: Commits changes made to the property configuration
  *			Takes: nothing
  *			Returns: true if successful false if not
  *		add: Adds the given profile with specified configuration
  *			Takes: the location you wish to add the profile to, the tenant that you would like to add the profile to
  *			Returns: true if successful false if not
  */
  class ISymphonyProfile {
 	
 	//Mode attributes
 	var $mode_location = "";
 	var $mode_tenant = "";
 	var $original_name = "";
 	
 	//Property attributes
	var $name = null;
	var $password = null;
	var $jabber_host = null;
	var $jabber_domain = null;
	var $jabber_resource = null;
	var $jabber_port = null;	
	var $jabber_user_name = null;
	var $jabber_password = null;
	var $can_view_everyone_directory = null;
	var $unregistered_color = null;
	var $registered_color = null;
	var $ringing_color = null;
	var $pending_color = null;
	var $local_linked_color = null;
	var $outside_linked_color = null;
	var $queue_linked_color = null;
	var $chan_spy_barge = null;
	
 	//Methods
 	function update() {

 		//Move to profile mode
		if(!iSymphony::moveToProfile($this->mode_location, $this->mode_tenant, $this->original_name)) {
			return false;
		}
		
		//Modify properties
		foreach(iSymphony::$profilePropertyArray as $val) {
			if($this->$val !== null) {
				if(!iSymphony::checkAndSetErrorNone("$val {$this->$val}")) {
					return false;
				}
			}
		}
		
		//Commit changes
		if(iSymphony::checkAndSetErrorNone("commit")) {
			
			//Set mode properties if edit was successful so user can call subsequent commits
			$this->original_name = $this->name;
			return true;
			
		} else {
			return false;
		}
 	}
 	
 	function add($location, $tenant) {

 		//Move to tenant mode
		if(!iSymphony::moveToTenant($location, $tenant)) {
			return false;
		}
		
		//Initiate new command
		if(!iSymphony::checkAndSetErrorNone("new profile {$this->name}")) {
			return false;
		}
		
		//Modify properties
		foreach(iSymphony::$profilePropertyArray as $val) {
			if($this->$val !== null) {
				if(!iSymphony::checkAndSetErrorNone("$val {$this->$val}")) {
					return false;
				}
			}
		}
		
		//Commit addition
		if(iSymphony::checkAndSetErrorNone("commit")) {
			
			//Set mode properties if addition was successful so user can call commit
			$this->original_name = $this->name;
			$this->mode_location = $location;
			$this->mode_tenant = $tenant;
			return true;
			
		} else {
			return false;
		}
 	}
 }
 
 /*###############################################
  * Class that represents a queue's properties
  * Attributes: 
  *		name: Display name of queue(STRING)
  *		queue_val: Name of queue as defined in queues.conf(STRING)
  *		extension_val: Extension used to access queue(STRING)
  *		context: Context used to access queue(STRING)
  *
  * Methods:
  *		update: Commits changes made to the property configuration
  *			Takes: nothing
  *			Returns: true if successful false if not
  *		add: Adds the given queue with specified configuration
  *			Takes: the location you wish to add the queue to, the tenant that you would like to add the queue to
  *			Returns: true if successful false if not
  */
  class ISymphonyQueue {
 	
 	//Mode attributes
 	var $mode_location = "";
 	var $mode_tenant = "";
 	var $original_name = "";
 	
 	//Property attributes
	var $name = null;
	var $queue_val = null;
	var $extension_val = null;
	var $context = null;
	
 	//Methods
 	function update() {

 		//Move to queue mode
		if(!iSymphony::moveToQueue($this->mode_location, $this->mode_tenant, $this->original_name)) {
			return false;
		}
		
		//Modify properties
		foreach(iSymphony::$queuePropertyArray as $val) {
			if($this->$val !== null) {
				if(!iSymphony::checkAndSetErrorNone("$val {$this->$val}")) {
					return false;
				}
			}
		}
		
		//Commit changes
		if(iSymphony::checkAndSetErrorNone("commit")) {
			
			//Set mode properties if edit was successful so user can call subsequent commits
			$this->original_name = $this->name;
			return true;
			
		} else {
			return false;
		}
 	}
 	
 	function add($location, $tenant) {

 		//Move to tenant mode
		if(!iSymphony::moveToTenant($location, $tenant)) {
			return false;
		}
		
		//Initiate new command
		if(!iSymphony::checkAndSetErrorNone("new queue {$this->name}")) {
			return false;
		}
		
		//Modify properties
		foreach(iSymphony::$queuePropertyArray as $val) {
			if($this->$val !== null) {
				if(!iSymphony::checkAndSetErrorNone("$val {$this->$val}")) {
					return false;
				}
			}
		}
		
		//Commit addition
		if(iSymphony::checkAndSetErrorNone("commit")) {
			
			//Set mode properties if addition was successful so user can call commit
			$this->original_name = $this->name;
			$this->mode_location = $location;
			$this->mode_tenant = $tenant;
			return true;
			
		} else {
			return false;
		}
 	}
 }
 
 /*###############################################
  * Class that represents a conference room's properties
  * Attributes: 
  *		name: Display name of conference room(STRING)
  *		predefined: Flag describing if this is a predefined or custom room(BOOLEAN[true or false])
  *		room_number: Room number for predefined room as it appears in meetme.conf(STRING)
  *		extension_val: Extension used to access predefined room(STRING)
  *		context: Context used to access predefined room(STRING)
  *		announce_user_count: Custom room option to turn on/off announcing user count to entering users(BOOLEAN[true or false])
  *		music_on_hold_for_single_user: Custom room option to turn on/off music on hold when a single user is in a room(BOOLEAN[true or false])
  *		exit_room_via_pound: Custom room option to turn on/off the ability for users to exit the room via the # key(BOOLEAN[true or false])
  *		present_menu_via_star: Custom room option to turn on/off the ability for users to be presented a menu via the * key(BOOLEAN[true or false])
  *		announce_user_join_leave: Custom room option to turn on/off the ability to announce the name of incoming users.(BOOLEAN[true or false])
  *		disable_join_leave_notification: Custom room option to turn on/off a chime notification of entering and exiting users.(BOOLEAN[true or false])
  *		record: Custom room option to turn on/off recording of a room.(BOOLEAN[true or false])
  *		peer_originate: Flag specifying if originations to this conference room should utilize the raw peer.(BOOLEAN[true or false])
  *
  * Methods:
  *		update: Commits changes made to the property configuration
  *			Takes: nothing
  *			Returns: true if successful false if not
  *		add: Adds the given conference room with specified configuration
  *			Takes: the location you wish to add the conference room to, the tenant that you would like to add the conference room to
  *			Returns: true if successful false if not
  */
  class ISymphonyConferenceRoom {
 	
 	//Mode attributes
 	var $mode_location = "";
 	var $mode_tenant = "";
 	var $original_name = "";
 	
 	//Property attributes
 	var $name = null;
 	var $predefined = null;
 	var $room_number = null;
 	var $extension_val = null;
 	var $context = null;
 	var $announce_user_count = null;
 	var $music_on_hold_for_single_user = null;
 	var $exit_room_via_pound = null;
 	var $present_menu_via_star = null;
 	var $announce_user_join_leave = null;
 	var $disable_join_leave_notification = null;
 	var $record = null;
 	var $peer_originate = null;
	
 	//Methods
 	function update() {
	
 		//Move to conference room mode
		if(!iSymphony::moveToConferenceRoom($this->mode_location, $this->mode_tenant, $this->original_name)) {
			return false;
		}
		
		//Modify properties
		foreach($conferenceRoomPropertyArray as $val) {
			if($this->$val !== null) {
				if(!iSymphony::checkAndSetErrorNone("$val {$this->$val}")) {
					return false;
				}
			}
		}
		
		//Commit changes
		if(iSymphony::checkAndSetErrorNone("commit")) {
			
			//Set mode properties if edit was successful so user can call subsequent commits
			$this->original_name = $this->name;
			return true;
			
		} else {
			return false;
		}
 	}
 	
 	function add($location, $tenant) {
 		//Move to tenant mode
		if(!iSymphony::moveToTenant($location, $tenant)) {
			return false;
		}
		
		//Initiate new command
		if(!iSymphony::checkAndSetErrorNone("new meetme {$this->name}")) {
			return false;
		}
		
		//Modify properties
		foreach(iSymphony::$conferenceRoomPropertyArray as $val) {
			if($this->$val !== null) {
				if(!iSymphony::checkAndSetErrorNone("$val {$this->$val}")) {
					return false;
				}
			}
		}
		
		//Commit addition
		if(iSymphony::checkAndSetErrorNone("commit")) {
			
			//Set mode properties if addition was successful so user can call commit
			$this->original_name = $this->name;
			$this->mode_location = $location;
			$this->mode_tenant = $tenant;
			return true;
			
		} else {
			return false;
		}
 	}
 } 

 /*###############################################
  * Class that represents a permission group's properties
  * Attributes: 
  *		name: Name of permission group(STRING)
  *
  * Methods:
  *		update: Commits changes made to the property configuration
  *			Takes: nothing
  *			Returns: true if successful false if not
  *		add: Adds the given permission group with specified configuration
  *			Takes: the location you wish to add the permission group to, the tenant that you would like to add the permission group to
  *			Returns: true if successful false if not
  */
  class ISymphonyPermissionGroup {
 	
 	//Mode attributes
 	var $mode_location = "";
 	var $mode_tenant = "";
 	var $original_name = "";
 	
 	//Property attributes
 	var $name = null;
	
 	//Methods
 	function update() {
 		//Move to permission group mode
		if(!iSymphony::moveToPermissionGroup($this->mode_location, $this->mode_tenant, $this->original_name)) {
			return false;
		}
		
		//Modify properties
		foreach(iSymphony::$permissionGroupPropertyArray as $val) {
			if($this->$val !== null) {
				if(!iSymphony::checkAndSetErrorNone("$val {$this->$val}")) {
					return false;
				}
			}
		}
		
		//Commit changes
		if(iSymphony::checkAndSetErrorNone("commit")) {
			
			//Set mode properties if edit was successful so user can call subsequent commits
			$this->original_name = $this->name;
			return true;
			
		} else {
			return false;
		}
 	}
 	
 	function add($location, $tenant) {

 		//Move to tenant mode
		if(!iSymphony::moveToTenant($location, $tenant)) {
			return false;
		}
		
		//Initiate new command
		if(!iSymphony::checkAndSetErrorNone("new permgroup {$this->name}")) {
			return false;
		}
		
		//Modify properties
		foreach(iSymphony::$permissionGroupPropertyArray as $val) {
			if($this->$val !== null) {
				if(!iSymphony::checkAndSetErrorNone("$val {$this->$val}")) {
					return false;
				}
			}
		}
		
		//Commit addition
		if(iSymphony::checkAndSetErrorNone("commit")) {
			
			//Set mode properties if addition was successful so user can call commit
			$this->original_name = $this->name;
			$this->mode_location = $location;
			$this->mode_tenant = $tenant;
			return true;
			
		} else {
			return false;
		}
 	}
 }   
 
 /*###############################################
  * Class that represents a status's properties
  * Attributes: 
  *		name: Name of status(STRING)
  *		type: Type of status(TYPE[available,unavailable,out])
  * Methods:
  *		update: Commits changes made to the property configuration
  *			Takes: nothing
  *			Returns: true if successful false if not
  *		add: Adds the given status with specified configuration
  *			Takes: the location you wish to add the status to, the tenant that you would like to add the status to
  *			Returns: true if successful false if not
  */ 
  class ISymphonyStatus {
 	
 	//Mode attributes
 	var $mode_location = "";
 	var $mode_tenant = "";
 	var $original_name = "";
 	
 	//Property attributes
 	var $name = null;
 	var $type = null;
	
	var $pres;
 	//Methods
 	function update() {
 		//Move to status mode
		if(!iSymphony::moveToStatus($this->mode_location, $this->mode_tenant, $this->original_name)) {
			return false;
		}
		
		//Modify properties
		foreach(iSymphony::$statusPropertyArray as $val) {
			if($this->$val !== null) {
				if(!iSymphony::checkAndSetErrorNone("$val {$this->$val}")) {
					return false;
				}
			}
		}
		
		//Commit changes
		if(iSymphony::checkAndSetErrorNone("commit")) {
			
			//Set mode properties if edit was successful so user can call subsequent commits
			$this->original_name = $this->name;
			return true;
			
		} else {
			return false;
		}
 	}
 	
 	function add($location, $tenant) {

 		//Move to tenant mode
		if(!iSymphony::moveToTenant($location, $tenant)) {
			return false;
		}
		
		//Initiate new command
		if(!iSymphony::checkAndSetErrorNone("new status {$this->name}")) {
			return false;
		}
		
		//Modify properties
		foreach(iSymphony::$statusPropertyArray as $val) {
			if($this->$val !== null) {
				if(!iSymphony::checkAndSetErrorNone("$val {$this->$val}")) {
					return false;
				}
			}
		}
		
		//Commit addition
		if(iSymphony::checkAndSetErrorNone("commit")) {
			
			//Set mode properties if addition was successful so user can call commit
			$this->original_name = $this->name;
			$this->mode_location = $location;
			$this->mode_tenant = $tenant;
			return true;
			
		} else {
			return false;
		}
 	}
 } 
 
 /*###############################################
  * Class that represents the default local permissions
  * Attributes: 
  * 	call_voice_mail: Allows users to call there own extension's voice mail(BOOLEAN[allow or deny])
  * 	hold_calls: Allows users to place their calls on hold(BOOLEAN[allow or deny])
  * 	transfer_call_to_voice_mail: Allows users to transfer call to their voice mail(BOOLEAN[allow or deny])
  * 	mute: Allows users to mute/unmute themselves when in a conference room or barged call(BOOLEAN[allow or deny])
  * 	record: Allows users to record their calls(BOOLEAN[allow or deny])
  * 	hangup: Allows users to hangup their calls via the panel(BOOLEAN[allow or deny])
  * 	set_user_status_note: Allows users to set their status notes and return time(BOOLEAN[allow or deny])	
  * 	call_cell_phone: Allows users to call their cell phone(BOOLEAN[allow or deny])
  * 	add_extension_directory: Allows users to add extension directories(BOOLEAN[allow or deny])	
  * 	set_user_status: Allows users to set their status(BOOLEAN[allow or deny])
  * 	transfer_call_to_cell_phone: Allows users to transfer calls to their cell phone(BOOLEAN[allow or deny])
  * 	agent_login: Allows users to login/logout of their agent via the panel(BOOLEAN[allow or deny])
  * 	add_temp_meetme_room: Allows users to create temporary conference rooms BOOLEAN[allow or deny]
  *		listen_to_voice_mail: Allows users to listen to their voice mail(BOOLEAN[allow or deny])
  *		delete_voice_mail: Allows users to delete their voice mail(BOOLEAN[allow or deny])
  *		move_voice_mail: Allows users to move their voice mail(BOOLEAN[allow or deny])
  *		pause_member: Allows users to pause themselves in a queue(BOOLEAN[allow or deny])
  *		do_not_disturb: Allows user to place the extension in DND(BOOLEAN[allow or deny])
  *
  * Methods:
  *		update: Commits changes made to the property configuration
  *			Takes: nothing
  *			Returns: true if successful false if not
  */ 
  class ISymphonyDefaultLocalPermission {
 	 	
 	//Property attributes
 	var $call_voice_mail = null;
 	var $hold_calls = null;
 	var $transfer_call_to_voice_mail = null;
 	var $mute = null;
 	var $record = null;
 	var $hangup = null;
 	var $set_user_status_note = null;
 	var $call_cell_phone = null;
 	var $add_extension_directory = null;
 	var $set_user_status = null;
 	var $transfer_call_to_cell_phone = null;
 	var $agent_login = null;
 	var $add_temp_meetme_room = null;
 	var $listen_to_voice_mail = null;
 	var $delete_voice_mail = null;
 	var $move_voice_mail = null;
 	var $pause_member = null;
 	var $do_not_disturb = null;
	
 	//Methods
 	function update() {
		
 		//Move to default local permission mode
		if(!iSymphony::moveToDefaultLocalPermission()) {
			return false;
		}
				
		//Modify properties
		foreach(iSymphony::$localPermissionPropertyArray as $val) {
			if($this->$val !== null) {
				if(!iSymphony::checkAndSetErrorNone("$val {$this->$val}")) {
					return false;
				}
			}
		}
		
		//Commit changes
		if(iSymphony::checkAndSetErrorNone("commit")) {
			return true;
		} else {
			return false;
		}
 	}
 }    
 
 /*###############################################
  * Class that represents the default remote permissions
  * Attributes: 
  *		call_voice_mail: Allows user to call remote extension voice mail(BOOLEAN[allow or deny])
  *		transfer_to: Allows users to transfer calls to remote extensions(BOOLEAN[allow or deny])
  *		transfer_call_to_voice_mail: Allows users to transfer calls to remote extension voice mail(BOOLEAN[allow or deny])
  *		steal_call: Allows users to steal calls from remote extensions(BOOLEAN[allow or deny])
  *		record: Allows users to record remote extension calls(BOOLEAN[allow or deny])	
  *		originate_to: Allows users to originate calls to remote extensions via the panel(BOOLEAN[allow or deny])
  *		email: Allows users to email remote extensions(BOOLEAN[allow or deny])	
  *		call_cell_phone: Allows users to call remote extension cell phones(BOOLEAN[allow or deny])
  *		barge: Allows users to barge in on remote extension calls(BOOLEAN[allow or deny])
  *		transfer_call_to_cell_phone: Allows users to transfer calls to remote extension voice mail(BOOLEAN[allow or deny])
  *		chat: Allows users to initiate chat sessions with remote extensions(BOOLEAN[allow or deny])
  *		agent_login: Allows users to login/logout remote extension agents(BOOLEAN[allow or deny])	
  *		view_calls: Allows users to see remote extension call status(BOOLEAN[allow or deny])	
  *		view_caller_id: Allows users to see remote extension callerID(BOOLEAN[allow or deny])
  *		forward_voice_mail_to: Allows users to forward voice mail to remote extensions(BOOLEAN[allow or deny])
  *		set_user_status: Allows users to set extension status(BOOLEAN[allow or deny])
  *		set_user_status_note: Allows users to set extension status note and return time(BOOLEAN[allow or deny])
  *		pause_member: Allows users to pause extension's agent in queue(BOOLEAN[allow or deny])
  *		do_not_disturb: Allows user to place the extension in DND(BOOLEAN[allow or deny])
  *
  * Methods:
  *		update: Commits changes made to the property configuration
  *			Takes: nothing
  *			Returns: true if successful false if not
  */ 
  class ISymphonyDefaultRemotePermission {
 	 	
 	//Property attributes
	var $call_voice_mail = null;
	var $transfer_to = null;
	var $transfer_call_to_voice_mail = null;
	var $steal_call = null;
	var $record = null;
	var $originate_to = null;
	var $email = null;
	var $call_cell_phone = null;
	var $barge = null;
	var $transfer_call_to_cell_phone = null;
	var $chat = null;
	var $agent_login = null;	
	var $view_calls = null;	
	var $view_caller_id = null;
	var $forward_voice_mail_to = null;
	var $set_user_status = null;
	var $set_user_status_note = null;
	var $pause_member = null;
	var $do_not_disturb = null;
	
 	//Methods
 	function update() {

 		//Move to default remote permission mode
		if(!iSymphony::moveToDefaultRemotePermission()) {
			return false;
		}
				
		//Modify properties
		foreach(iSymphony::$remotePermissionPropertyArray as $val) {
			if($this->$val !== null) {
				if(!iSymphony::checkAndSetErrorNone("$val {$this->$val}")) {
					return false;
				}
			}
		}
		
		//Commit changes
		if(iSymphony::checkAndSetErrorNone("commit")) {
			return true;
		} else {
			return false;
		}
 	}
 }  
 
 /*###############################################
  * Class that represents the default park permissions
  * Attributes: 
  *		park_call: Allows users to park calls(BOOLEAN[allow or deny])
  *		set_parked_call_note: Allows users to set parked call notes(BOOLEAN[allow or deny])
  *		unpark_call: Allows users to take calls from the parking lot via the panel(BOOLEAN[allow or deny])
  *
  * Methods:
  *		update: Commits changes made to the property configuration
  *			Takes: nothing
  *			Returns: true if successful false if not
  */
  class ISymphonyDefaultParkPermission {
 	 	
 	//Property attributes
	var $park_call = null;
	var $set_parked_call_note = null;
	var $unpark_call = null;
	
 	//Methods
 	function update() {
 
 		//Move to default park permission mode
		if(!iSymphony::moveToDefaultParkPermission()) {
			return false;
		}
				
		//Modify properties
		foreach(iSymphony::$parkPermissionPropertyArray as $val) {
			if($this->$val !== null) {
				if(!iSymphony::checkAndSetErrorNone("$val {$this->$val}")) {
					return false;
				}
			}
		}
		
		//Commit changes
		if(iSymphony::checkAndSetErrorNone("commit")) {
			return true;
		} else {
			return false;
		}
 	}
 }    
 
 /*###############################################
  * Class that represents the default queue permissions
  * Attributes: 
  *		transfer_to: Allows users to transfer calls to a queue(BOOLEAN[allow or deny])
  *		steal_call: Allows users to steal calls from a queue(BOOLEAN[allow or deny])
  *		dynamic_login: Allows users to log into queue dynamically (BOOLEAN[allow or deny])
  *		display: Allows users to view this queue (BOOLEAN[allow or deny])
  *
  * Methods:
  *		update: Commits changes made to the property configuration
  *			Takes: nothing
  *			Returns: true if successful false if not
  */ 
  class ISymphonyDefaultQueuePermission {
 	 	
 	//Property attributes
	var $transfer_to = null;
	var $steal_call = null;
	var $dynamic_login = null;
	var $display = null;

 	//Methods
 	function update() {
 		
 
 		//Move to default queue permission mode
		if(!iSymphony::moveToDefaultQueuePermission()) {
			return false;
		}
				
		//Modify properties
		foreach(iSymphony::$queuePermissionPropertyArray as $val) {
			if($this->$val !== null) {
				if(!iSymphony::checkAndSetErrorNone("$val {$this->$val}")) {
					return false;
				}
			}
		}
		
		//Commit changes
		if(iSymphony::checkAndSetErrorNone("commit")) {
			return true;
		} else {
			return false;
		}
 	}
 }  
 
 /*###############################################
  * Class that represents the default conference room permissions
  * Attributes: 
  *		steal_call: Allows users to steal calls from conference rooms(BOOLEAN[allow or deny])
  *		transfer_to: Allows users to transfer calls to conference rooms(BOOLEAN[allow or deny])
  *		originate_to: Allows users to originate calls to a conference room(BOOLEAN[allow or deny])
  *		mute_users: Allows users to mute conference room users(BOOLEAN[allow or deny])
  *		kick_users: Allows users to kick conference room users from the room(BOOLEAN[allow or deny])
  *		display: Allows users to view this conference room (BOOLEAN[allow or deny])
  *
  * Methods:
  *		update: Commits changes made to the property configuration
  *			Takes: nothing
  *			Returns: true if successful false if not
  */  
  class ISymphonyDefaultConferenceRoomPermission {
 	 	
 	//Property attributes
	var $steal_call = null;
	var $transfer_to = null;
	var $originate_to = null;
	var $mute_users = null;
	var $kick_users = null;
	var $display = null;
 	
 	//Methods
 	function update() {

 		//Move to default conference room permission mode
		if(!iSymphony::moveToDefaultConferenceRoomPermission()) {
			return false;
		}
				
		//Modify properties
		foreach(iSymphony::$conferenceRoomPermissionPropertyArray as $val) {
			if($this->$val !== null) {
				if(!iSymphony::checkAndSetErrorNone("$val {$this->$val}")) {
					return false;
				}
			}
		}
		
		//Commit changes
		if(iSymphony::checkAndSetErrorNone("commit")) {
			return true;
		} else {
			return false;
		}
 	}
 }    
 
 /*###############################################
  * Class that represents a group's local permissions
  * Attributes: 
  * 	call_voice_mail: Allows users to call there own extension's voice mail(BOOLEAN[allow or deny])
  * 	hold_calls: Allows users to place their calls on hold(BOOLEAN[allow or deny])
  * 	transfer_call_to_voice_mail: Allows users to transfer call to their voice mail(BOOLEAN[allow or deny])
  * 	mute: Allows users to mute/unmute themselves when in a conference room or barged call(BOOLEAN[allow or deny])
  * 	record: Allows users to record their calls(BOOLEAN[allow or deny])
  * 	hangup: Allows users to hangup their calls via the panel(BOOLEAN[allow or deny])
  * 	set_user_status_note: Allows users to set their status notes and return time(BOOLEAN[allow or deny])	
  * 	call_cell_phone: Allows users to call their cell phone(BOOLEAN[allow or deny])
  * 	add_extension_directory: Allows users to add extension directories(BOOLEAN[allow or deny])	
  * 	set_user_status: Allows users to set their status(BOOLEAN[allow or deny])
  * 	transfer_call_to_cell_phone: Allows users to transfer calls to their cell phone(BOOLEAN[allow or deny])
  * 	agent_login: Allows users to login/logout of their agent via the panel(BOOLEAN[allow or deny])
  * 	add_temp_meetme_room: Allows users to create temporary conference rooms BOOLEAN[allow or deny]
  *		listen_to_voice_mail: Allows users to listen to their voice mail(BOOLEAN[allow or deny])
  *		delete_voice_mail: Allows users to delete their voice mail(BOOLEAN[allow or deny])
  *		move_voice_mail: Allows users to move their voice mail(BOOLEAN[allow or deny])
  *		pause_member: Allows users to pause themselves in a queue(BOOLEAN[allow or deny])
  *		do_not_disturb: Allows user to place the extension in DND(BOOLEAN[allow or deny])
  *
  * Methods:
  *		update: Commits changes made to the property configuration
  *			Takes: nothing
  *			Returns: true if successful false if not
  */ 
  class ISymphonyGroupLocalPermission {
 	 	
 	//Mode attributes
 	var $mode_location = "";
 	var $mode_tenant = "";
 	var $mode_group = "";	
 	 	
 	//Property attributes
 	var $call_voice_mail = null;
 	var $hold_calls = null;
 	var $transfer_call_to_voice_mail = null;
 	var $mute = null;
 	var $record = null;
 	var $hangup = null;
 	var $set_user_status_note = null;
 	var $call_cell_phone = null;
 	var $add_extension_directory = null;
 	var $set_user_status = null;
 	var $transfer_call_to_cell_phone = null;
 	var $agent_login = null;
 	var $add_temp_meetme_room = null;
 	var $listen_to_voice_mail = null;
 	var $delete_voice_mail = null;
 	var $move_voice_mail = null;
 	var $pause_member = null;
 	var $do_not_disturb = null;
	
 	//Methods
 	function update() {

 		//Move to group local permission mode
		if(!iSymphony::moveToLocalGroupPermission($this->mode_location, $this->mode_tenant, $this->mode_group)) {
			return false;
		}
				
		//Modify properties
		foreach(iSymphony::$localPermissionPropertyArray as $val) {
			if($this->$val !== null) {
				if(!iSymphony::checkAndSetErrorNone("$val {$this->$val}")) {
					return false;
				}
			}
		}
		
		//Commit changes
		if(iSymphony::checkAndSetErrorNone("commit")) {
			return true;
		} else {
			return false;
		}
 	}
 }  
 
 /*###############################################
  * Class that represents a specified group remote permission set
  * Attributes: 
  *		call_voice_mail: Allows user to call remote extension voice mail(BOOLEAN[allow or deny])
  *		transfer_to: Allows users to transfer calls to remote extensions(BOOLEAN[allow or deny])
  *		transfer_call_to_voice_mail: Allows users to transfer calls to remote extension voice mail(BOOLEAN[allow or deny])
  *		steal_call: Allows users to steal calls from remote extensions(BOOLEAN[allow or deny])
  *		record: Allows users to record remote extension calls(BOOLEAN[allow or deny])	
  *		originate_to: Allows users to originate calls to remote extensions via the panel(BOOLEAN[allow or deny])
  *		email: Allows users to email remote extensions(BOOLEAN[allow or deny])	
  *		call_cell_phone: Allows users to call remote extension cell phones(BOOLEAN[allow or deny])
  *		barge: Allows users to barge in on remote extension calls(BOOLEAN[allow or deny])
  *		transfer_call_to_cell_phone: Allows users to transfer calls to remote extension voice mail(BOOLEAN[allow or deny])
  *		chat: Allows users to initiate chat sessions with remote extensions(BOOLEAN[allow or deny])
  *		agent_login: Allows users to login/logout remote extension agents(BOOLEAN[allow or deny])	
  *		view_calls: Allows users to see remote extension call status(BOOLEAN[allow or deny])	
  *		view_caller_id: Allows users to see remote extension callerID(BOOLEAN[allow or deny])
  *  	forward_voice_mail_to: Allows users to forward voice mail to remote extensions(BOOLEAN[allow or deny])
  *		set_user_status: Allows users to set extension status(BOOLEAN[allow or deny])
  *		set_user_status_note: Allows users to set extension status note and return time(BOOLEAN[allow or deny])
  *		pause_member: Allows users to pause extension's agent in queue(BOOLEAN[allow or deny])
  *		do_not_disturb: Allows user to place the extension in DND(BOOLEAN[allow or deny])
  *
  * Methods:
  *		update: Commits changes made to the property configuration
  *			Takes: nothing
  *			Returns: true if successful false if not
  *		add: Adds the given permission with specified configuration
  *			Takes: location, tenant, permission group, extension
  *			Returns: true if successful false if not
  */  
  class ISymphonyGroupRemotePermission {
 	 	
 	//Mode attributes
 	var $mode_location = "";
 	var $mode_tenant = "";
 	var $mode_group = "";
 	var $mode_extension = "";	 	
 	 	
 	//Property attributes
	var $call_voice_mail = null;
	var $transfer_to = null;
	var $transfer_call_to_voice_mail = null;
	var $steal_call = null;
	var $record = null;
	var $originate_to = null;
	var $email = null;
	var $call_cell_phone = null;
	var $barge = null;
	var $transfer_call_to_cell_phone = null;
	var $chat = null;
	var $agent_login = null;	
	var $view_calls = null;	
	var $view_caller_id = null;
	var $forward_voice_mail_to = null;
	var $set_user_status = null;
	var $set_user_status_note = null;
	var $pause_member = null;
	var $do_not_disturb = null;	
 	//Methods
 	function update() {

 		//Move to group remote permission mode
		if(!iSymphony::moveToRemoteGroupPermission($this->mode_location, $this->mode_tenant, $this->mode_group, $this->mode_extension)) {
			return false;
		}
				
		//Modify properties
		foreach(iSymphony::$remotePermissionPropertyArray as $val) {
			if($this->$val !== null) {
				if(!iSymphony::checkAndSetErrorNone("$val {$this->$val}")) {
					return false;
				}
			}
		}
		
		//Commit changes
		if(iSymphony::checkAndSetErrorNone("commit")) {
			return true;
		} else {
			return false;
		}
 	}
 	
 	function add($location, $tenant, $group, $extension) {
	
 		//Move to group mode
		if(!iSymphony::moveToPermissionGroup($location, $tenant, $group)) {
			return false;
		}
		
		//Initiate new command
		if(!iSymphony::checkAndSetErrorNone("new remote $extension")) {
			return false;
		}
		
		//Modify properties
		foreach(iSymphony::$remotePermissionPropertyArray as $val) {
			if($this->$val !== null) {
				if(!iSymphony::checkAndSetErrorNone("$val {$this->$val}")) {
					return false;
				}
			}
		}
		
		//Commit addition
		if(iSymphony::checkAndSetErrorNone("commit")) {
			
			//Set mode properties if addition was successful so user can call commit
			$this->mode_location = $location;
			$this->mode_tenant = $tenant;
			$this->mode_group = $group;
			$this->mode_extension = $extension;
			return true;
			
		} else {
			return false;
		}
 	} 	
 }    
 
 /*###############################################
  * Class that represents a group's park permissions
  * Attributes: 
  *		park_call: Allows users to park calls(BOOLEAN[allow or deny])
  *		set_parked_call_note: Allows users to set parked call notes(BOOLEAN[allow or deny])
  *		unpark_call: Allows users to take calls from the parking lot via the panel(BOOLEAN[allow or deny])
  *
  * Methods:
  *		update: Commits changes made to the property configuration
  *			Takes: nothing
  *			Returns: true if successful false if not
  */
  class ISymphonyGroupParkPermission {
 	 	
 	//Mode attributes
 	var $mode_location = "";
 	var $mode_tenant = "";
 	var $mode_group = "";	 	
 	 	
 	//Property attributes
	var $park_call = null;
	var $set_parked_call_note = null;
	var $unpark_call = null;
	
 	//Methods
 	function update() {
 
 		//Move to group park permission mode
		if(!iSymphony::moveToParkGroupPermission($this->mode_location, $this->mode_tenant, $this->mode_group)) {
			return false;
		}
				
		//Modify properties
		foreach(iSymphony::$parkPermissionPropertyArray as $val) {
			if($this->$val !== null) {
				if(!iSymphony::checkAndSetErrorNone("$val {$this->$val}")) {
					return false;
				}
			}
		}
		
		//Commit changes
		if(iSymphony::checkAndSetErrorNone("commit")) {
			return true;
		} else {
			return false;
		}
 	}
 }   
 
 /*###############################################
  * Class that represents a specified group queue permission set
  * Attributes: 
  *		transfer_to: Allows users to transfer calls to a queue(BOOLEAN[allow or deny])
  *		steal_call: Allows users to steal calls from a queue(BOOLEAN[allow or deny])
  *		dynamic_login: Allows users to log into queue dynamically (BOOLEAN[allow or deny])
  *		display: Allows users to view this queue (BOOLEAN[allow or deny])
  *
  * Methods:
  *		update: Commits changes made to the property configuration
  *			Takes: nothing
  *			Returns: true if successful false if not
  *		add: Adds the given permission with specified configuration
  *			Takes: location, tenant, permission group, queue
  *			Returns: true if successful false if not
  */   
  class ISymphonyGroupQueuePermission {
 	 	
 	//Mode attributes
 	var $mode_location = "";
 	var $mode_tenant = "";
 	var $mode_group = "";
 	var $mode_queue = "";	 	
 	 	
 	//Property attributes
	var $transfer_to = null;
	var $steal_call = null;
	var $dynamic_login = null;
	var $display = null;
	
 	//Methods
 	function update() {
 
 		//Move to group mode
		if(!iSymphony::moveToQueueGroupPermission($this->mode_location, $this->mode_tenant, $this->mode_group, $this->mode_queue)) {
			return false;
		}
				
		//Modify properties
		foreach(iSymphony::$queuePermissionPropertyArray as $val) {
			if($this->$val !== null) {
				if(!iSymphony::checkAndSetErrorNone("$val {$this->$val}")) {
					return false;
				}
			}
		}
		
		//Commit changes
		if(iSymphony::checkAndSetErrorNone("commit")) {
			return true;
		} else {
			return false;
		}
 	}
 	
 	function add($location, $tenant, $group, $queue) {
	
 		//Move to group mode
		if(!iSymphony::moveToPermissionGroup($location, $tenant, $group)) {
			return false;
		}
		
		//Initiate new command
		if(!iSymphony::checkAndSetErrorNone("new queue $queue")) {
			return false;
		}
		
		//Modify properties
		foreach(iSymphony::$queuePermissionPropertyArray as $val) {
			if($this->$val !== null) {
				if(!iSymphony::checkAndSetErrorNone("$val {$this->$val}")) {
					return false;
				}
			}
		}
		
		//Commit addition
		if(iSymphony::checkAndSetErrorNone("commit")) {
			
			//Set mode properties if addition was successful so user can call commit
			$this->mode_location = $location;
			$this->mode_tenant = $tenant;
			$this->mode_group = $group;
			$this->mode_queue = $queue;
			return true;
			
		} else {
			return false;
		}
 	} 	
 }  
 
 /*###############################################
  * Class that represents a specified group conference room permission set
  * Attributes: 
  *		steal_call: Allows users to steal calls from conference rooms(BOOLEAN[allow or deny])
  *		transfer_to: Allows users to transfer calls to conference rooms(BOOLEAN[allow or deny])
  *		originate_to: Allows users to originate calls to a conference room(BOOLEAN[allow or deny])
  *		mute_users: Allows users to mute conference room users(BOOLEAN[allow or deny])
  *		kick_users: Allows users to kick conference room users from the room(BOOLEAN[allow or deny])
  *		display: Allows users to view this conference room (BOOLEAN[allow or deny])
  *
  * Methods:
  *		update: Commits changes made to the property configuration
  *			Takes: nothing
  *			Returns: true if successful false if not
  *		add: Adds the given permission with specified configuration
  *			Takes: location, tenant, permission group, room
  *			Returns: true if successful false if not
  */  
  class ISymphonyGroupConferenceRoomPermission {
 	 	
 	//Mode attributes
 	var $mode_location = "";
 	var $mode_tenant = "";
 	var $mode_group = "";
 	var $mode_room = "";	 	
 	 	
 	//Property attributes
	var $steal_call = null;
	var $transfer_to = null;
	var $originate_to = null;
	var $mute_users = null;
	var $kick_users = null;
	var $display = null;
	
 	//Methods
 	function update() {
	
 		//Move to group mode
		if(!iSymphony::moveToConferenceRoomGroupPermission($this->mode_location, $this->mode_tenant, $this->mode_group, $this->mode_room)) {
			return false;
		}
				
		//Modify properties
		foreach(iSymphony::$conferenceRoomPermissionPropertyArray as $val) {
			if($this->$val !== null) {
				if(!iSymphony::checkAndSetErrorNone("$val {$this->$val}")) {
					return false;
				}
			}
		}
		
		//Commit changes
		if(iSymphony::checkAndSetErrorNone("commit")) {
			return true;
		} else {
			return false;
		}
 	}
 	
 	function add($location, $tenant, $group, $room) {
 
 		//Move to group mode
		if(!iSymphony::moveToPermissionGroup($location, $tenant, $group)) {
			return false;
		}
		
		//Initiate new command
		if(!iSymphony::checkAndSetErrorNone("new meetme $room")) {
			return false;
		}
		
		//Modify properties
		foreach(iSymphony::$conferenceRoomPermissionPropertyArray as $val) {
			if($this->$val !== null) {
				if(!iSymphony::checkAndSetErrorNone("$val {$this->$val}")) {
					return false;
				}
			}
		}
		
		//Commit addition
		if(iSymphony::checkAndSetErrorNone("commit")) {
			
			//Set mode properties if addition was successful so user can call commit
			$this->mode_location = $location;
			$this->mode_tenant = $tenant;
			$this->mode_group = $group;
			$this->mode_room = $room;
			return true;
			
		} else {
			return false;
		}
 	} 	
 }   
 
 /*###############################################
  * Class that represents a profile's local override permissions
  * Attributes: 
  * 	call_voice_mail: Allows users to call there own extension's voice mail(BOOLEAN[allow or deny])
  * 	hold_calls: Allows users to place their calls on hold(BOOLEAN[allow or deny])
  * 	transfer_call_to_voice_mail: Allows users to transfer call to their voice mail(BOOLEAN[allow or deny])
  * 	mute: Allows users to mute/unmute themselves when in a conference room or barged call(BOOLEAN[allow or deny])
  * 	record: Allows users to record their calls(BOOLEAN[allow or deny])
  * 	hangup: Allows users to hangup their calls via the panel(BOOLEAN[allow or deny])
  * 	set_user_status_note: Allows users to set their status notes and return time(BOOLEAN[allow or deny])	
  * 	call_cell_phone: Allows users to call their cell phone(BOOLEAN[allow or deny])
  * 	add_extension_directory: Allows users to add extension directories(BOOLEAN[allow or deny])	
  * 	set_user_status: Allows users to set their status(BOOLEAN[allow or deny])
  * 	transfer_call_to_cell_phone: Allows users to transfer calls to their cell phone(BOOLEAN[allow or deny])
  * 	agent_login: Allows users to login/logout of their agent via the panel(BOOLEAN[allow or deny])
  * 	add_temp_meetme_room: Allows users to create temporary conference rooms BOOLEAN[allow or deny]
  *		listen_to_voice_mail: Allows users to listen to their voice mail(BOOLEAN[allow or deny])
  *		delete_voice_mail: Allows users to delete their voice mail(BOOLEAN[allow or deny])
  *		move_voice_mail: Allows users to move their voice mail(BOOLEAN[allow or deny])
  *		pause_member: Allows users to pause themselves in a queue(BOOLEAN[allow or deny])
  *		do_not_disturb: Allows user to place the extension in DND(BOOLEAN[allow or deny])
  *
  * Methods:
  *		update: Commits changes made to the property configuration
  *			Takes: nothing
  *			Returns: true if successful false if not
  */ 
  class ISymphonyOverrideLocalPermission {
 	 	
 	//Mode attributes
 	var $mode_location = "";
 	var $mode_tenant = "";
 	var $mode_profile = "";	
 	 	
 	//Property attributes
 	var $call_voice_mail = null;
 	var $hold_calls = null;
 	var $transfer_call_to_voice_mail = null;
 	var $mute = null;
 	var $record = null;
 	var $hangup = null;
 	var $set_user_status_note = null;
 	var $call_cell_phone = null;
 	var $add_extension_directory = null;
 	var $set_user_status = null;
 	var $transfer_call_to_cell_phone = null;
 	var $agent_login = null;
 	var $add_temp_meetme_room = null;
 	var $listen_to_voice_mail = null;
 	var $delete_voice_mail = null;
 	var $move_voice_mail = null;
 	var $pause_member = null;
 	var $do_not_disturb = null;
	
 	//Methods
 	function update() {

 		//Move to profile local permission mode
		if(!iSymphony::moveToLocalOverridePermission($this->mode_location, $this->mode_tenant, $this->mode_profile)) {
			return false;
		}
				
		//Modify properties
		foreach(iSymphony::$localPermissionPropertyArray as $val) {
			if($this->$val !== null) {
				if(!iSymphony::checkAndSetErrorNone("$val {$this->$val}")) {
					return false;
				}
			}
		}
		
		//Commit changes
		if(iSymphony::checkAndSetErrorNone("commit")) {
			return true;
		} else {
			return false;
		}
 	}
 }   

 /*###############################################
  * Class that represents a profile's specified remote override permission set
  * Attributes: 
  *		call_voice_mail: Allows user to call remote extension voice mail(BOOLEAN[allow or deny])
  *		transfer_to: Allows users to transfer calls to remote extensions(BOOLEAN[allow or deny])
  *		transfer_call_to_voice_mail: Allows users to transfer calls to remote extension voice mail(BOOLEAN[allow or deny])
  *		steal_call: Allows users to steal calls from remote extensions(BOOLEAN[allow or deny])
  *		record: Allows users to record remote extension calls(BOOLEAN[allow or deny])	
  *		originate_to: Allows users to originate calls to remote extensions via the panel(BOOLEAN[allow or deny])
  *		email: Allows users to email remote extensions(BOOLEAN[allow or deny])	
  *		call_cell_phone: Allows users to call remote extension cell phones(BOOLEAN[allow or deny])
  *		barge: Allows users to barge in on remote extension calls(BOOLEAN[allow or deny])
  *		transfer_call_to_cell_phone: Allows users to transfer calls to remote extension voice mail(BOOLEAN[allow or deny])
  *		chat: Allows users to initiate chat sessions with remote extensions(BOOLEAN[allow or deny])
  *		agent_login: Allows users to login/logout remote extension agents(BOOLEAN[allow or deny])	
  *		view_calls: Allows users to see remote extension call status(BOOLEAN[allow or deny])	
  *		view_caller_id: Allows users to see remote extension callerID(BOOLEAN[allow or deny])
  *  	forward_voice_mail_to: Allows users to forward voice mail to remote extensions(BOOLEAN[allow or deny])
  *		set_user_status: Allows users to set extension status(BOOLEAN[allow or deny])
  *		set_user_status_note: Allows users to set extension status note and return time(BOOLEAN[allow or deny])
  *		pause_member: Allows users to pause extension's agent in queue(BOOLEAN[allow or deny])
  *		do_not_disturb: Allows user to place the extension in DND(BOOLEAN[allow or deny])
  *
  * Methods:
  *		update: Commits changes made to the property configuration
  *			Takes: nothing
  *			Returns: true if successful false if not
  *		add: Adds the given permission with specified configuration
  *			Takes: location, tenant, profile, extension
  *			Returns: true if successful false if not
  */  
  class ISymphonyOverrideRemotePermission {
 	 	
 	//Mode attributes
 	var $mode_location = "";
 	var $mode_tenant = "";
 	var $mode_proile = "";
 	var $mode_extension = "";	 	
 	 	
 	//Property attributes
	var $call_voice_mail = null;
	var $transfer_to = null;
	var $transfer_call_to_voice_mail = null;
	var $steal_call = null;
	var $record = null;
	var $originate_to = null;
	var $email = null;
	var $call_cell_phone = null;
	var $barge = null;
	var $transfer_call_to_cell_phone = null;
	var $chat = null;
	var $agent_login = null;	
	var $view_calls = null;	
	var $view_caller_id = null;
	var $forward_voice_mail_to = null;
	var $set_user_status = null;
	var $set_user_status_note = null;
	var $pause_member = null;
	var $do_not_disturb = null;
	
 	//Methods
 	function update() {
 
 		//Move to profile remote permission mode
		if(!iSymphony::moveToRemoteOverridePermission($this->mode_location, $this->mode_tenant, $this->mode_profile, $this->mode_extension)) {
			return false;
		}
				
		//Modify properties
		foreach(iSymphony::$remotePermissionPropertyArray as $val) {
			if($this->$val !== null) {
				if(!iSymphony::checkAndSetErrorNone("$val {$this->$val}")) {
					return false;
				}
			}
		}
		
		//Commit changes
		if(iSymphony::checkAndSetErrorNone("commit")) {
			return true;
		} else {
			return false;
		}
 	}
 	
 	function add($location, $tenant, $profile, $extension) {

 		//Move to profile mode
		if(!iSymphony::moveToProfile($location, $tenant, $profile)) {
			return false;
		}
		
		//Initiate new command
		if(!iSymphony::checkAndSetErrorNone("new remote $extension")) {
			return false;
		}
		
		//Modify properties
		foreach(iSymphony::$remotePermissionPropertyArray as $val) {
			if($this->$val !== null) {
				if(!iSymphony::checkAndSetErrorNone("$val {$this->$val}")) {
					return false;
				}
			}
		}
		
		//Commit addition
		if(iSymphony::checkAndSetErrorNone("commit")) {
			
			//Set mode properties if addition was successful so user can call commit
			$this->mode_location = $location;
			$this->mode_tenant = $tenant;
			$this->mode_profile = $profile;
			$this->mode_extension = $extension;
			return true;
			
		} else {
			return false;
		}
 	} 	
 }    
 
 /*###############################################
  * Class that represents a profile's park override permissions
  * Attributes: 
  *		park_call: Allows users to park calls(BOOLEAN[allow or deny])
  *		set_parked_call_note: Allows users to set parked call notes(BOOLEAN[allow or deny])
  *		unpark_call: Allows users to take calls from the parking lot via the panel(BOOLEAN[allow or deny])
  *
  * Methods:
  *		update: Commits changes made to the property configuration
  *			Takes: nothing
  *			Returns: true if successful false if not
  */
  class ISymphonyOverrideParkPermission {
 	 	
 	//Mode attributes
 	var $mode_location = "";
 	var $mode_tenant = "";
 	var $mode_profilegroup = "";	 	
 	 	
 	//Property attributes
	var $park_call = null;
	var $set_parked_call_note = null;
	var $unpark_call = null;
	
 	//Methods
 	function update() {

 		//Move to profile park permission mode
		if(!iSymphony::moveToParkOverridePermission($this->mode_location, $this->mode_tenant, $this->mode_profile)) {
			return false;
		}
				
		//Modify properties
		foreach(iSymphony::$parkPermissionPropertyArray as $val) {
			if($this->$val !== null) {
				if(!iSymphony::checkAndSetErrorNone("$val {$this->$val}")) {
					return false;
				}
			}
		}
		
		//Commit changes
		if(iSymphony::checkAndSetErrorNone("commit")) {
			return true;
		} else {
			return false;
		}
 	}
 }   
 
 /*###############################################
  * Class that represents a profile's specified override queue permission set
  * Attributes: 
  *		transfer_to: Allows users to transfer calls to a queue(BOOLEAN[allow or deny])
  *		steal_call: Allows users to steal calls from a queue(BOOLEAN[allow or deny])
  *		dynamic_login: Allows users to log into queue dynamically (BOOLEAN[allow or deny])
  * 	display: Allows users to view this queue (BOOLEAN[allow or deny])
  *
  * Methods:
  *		update: Commits changes made to the property configuration
  *			Takes: nothing
  *			Returns: true if successful false if not
  *		add: Adds the given permission with specified configuration
  *			Takes: location, tenant, profile, queue
  *			Returns: true if successful false if not
  */   
  class ISymphonyOverrideQueuePermission {
 	 	
 	//Mode attributes
 	var $mode_location = "";
 	var $mode_tenant = "";
 	var $mode_profile = "";
 	var $mode_queue = "";	 	
 	 	
 	//Property attributes
	var $transfer_to = null;
	var $steal_call = null;
	var $dynamic_login = null;
	var $display = null;
	
 	//Methods
 	function update() {
 
 		//Move to profile queue permission mode
		if(!iSymphony::moveToQueueOverridePermission($this->mode_location, $this->mode_tenant, $this->mode_profile, $this->mode_queue)) {
			return false;
		}
				
		//Modify properties
		foreach(iSymphony::$queuePermissionPropertyArray as $val) {
			if($this->$val !== null) {
				if(!iSymphony::checkAndSetErrorNone("$val {$this->$val}")) {
					return false;
				}
			}
		}
		
		//Commit changes
		if(iSymphony::checkAndSetErrorNone("commit")) {
			return true;
		} else {
			return false;
		}
 	}
 	
 	function add($location, $tenant, $profile, $queue) {
 		
 		//Move to profile mode
		if(!iSymphony::moveToProfile($location, $tenant, $profile)) {
			return false;
		}
		
		//Initiate new command
		if(!iSymphony::checkAndSetErrorNone("new queue $queue")) {
			return false;
		}
		
		//Modify properties
		foreach(iSymphony::$queuePermissionPropertyArray as $val) {
			if($this->$val !== null) {
				if(!iSymphony::checkAndSetErrorNone("$val {$this->$val}")) {
					return false;
				}
			}
		}
		
		//Commit addition
		if(iSymphony::checkAndSetErrorNone("commit")) {
			
			//Set mode properties if addition was successful so user can call commit
			$this->mode_location = $location;
			$this->mode_tenant = $tenant;
			$this->mode_profile = $profile;
			$this->mode_queue = $queue;
			return true;
			
		} else {
			return false;
		}
 	} 	
 }  
 
 /*###############################################
  * Class that represents a profile's specified override conference room permission set
  * Attributes: 
  *		steal_call: Allows users to steal calls from conference rooms(BOOLEAN[allow or deny])
  *		transfer_to: Allows users to transfer calls to conference rooms(BOOLEAN[allow or deny])
  *		originate_to: Allows users to originate calls to a conference room(BOOLEAN[allow or deny])
  *		mute_users: Allows users to mute conference room users(BOOLEAN[allow or deny])
  *		kick_users: Allows users to kick conference room users from the room(BOOLEAN[allow or deny])
  *		display: Allows users to view this conference room (BOOLEAN[allow or deny])
  *
  * Methods:
  *		update: Commits changes made to the property configuration
  *			Takes: nothing
  *			Returns: true if successful false if not
  *		add: Adds the given permission with specified configuration
  *			Takes: location, tenant, profile, room
  *			Returns: true if successful false if not
  */  
  class ISymphonyOverrideConferenceRoomPermission {
 	 	
 	//Mode attributes
 	var $mode_location = "";
 	var $mode_tenant = "";
 	var $mode_profile = "";
 	var $mode_room = "";	 	
 	 	
 	//Property attributes
	var $steal_call = null;
	var $transfer_to = null;
	var $originate_to = null;
	var $mute_users = null;
	var $kick_users = null;
	var $display = null;
	
 	//Methods
 	function update() {
	
 		//Move to profile mode
		if(!iSymphony::moveToConferenceRoomOverridePermission($this->mode_location, $this->mode_tenant, $this->mode_profile, $this->mode_room)) {
			return false;
		}
				
		//Modify properties
		foreach(iSymphony::$conferenceRoomPermissionPropertyArray as $val) {
			if($this->$val !== null) {
				if(!iSymphony::checkAndSetErrorNone("$val {$this->$val}")) {
					return false;
				}
			}
		}
		
		//Commit changes
		if(iSymphony::checkAndSetErrorNone("commit")) {
			return true;
		} else {
			return false;
		}
 	}
 	
 	function add($location, $tenant, $profile, $room) {

 		//Move to profile mode
		if(!iSymphony::moveToProfile($location, $tenant, $profile)) {
			return false;
		}
		
		//Initiate new command
		if(!iSymphony::checkAndSetErrorNone("new meetme $room")) {
			return false;
		}
		
		//Modify properties
		foreach(iSymphony::$conferenceRoomPermissionPropertyArray as $val) {
			if($this->$val !== null) {
				if(!iSymphony::checkAndSetErrorNone("$val {$this->$val}")) {
					return false;
				}
			}
		}
		
		//Commit addition
		if(iSymphony::checkAndSetErrorNone("commit")) {
			
			//Set mode properties if addition was successful so user can call commit
			$this->mode_location = $location;
			$this->mode_tenant = $tenant;
			$this->mode_profile = $profile;
			$this->mode_room = $room;
			return true;
			
		} else {
			return false;
		}
 	} 	
  }
 	
 /*###############################################
  * Class that represents the current update state
  * Attributes: 
  *		task: the current task state flag of the update. Values are [NONE, CHECKING_FOR_UPDATES, UPDATE_AVAILABLE, NO_UPDATE_AVAILABLE, DOWNLOADING, DECOMPRESSING, READY_TO_INSTALL, INSTALLING, ERROR](STRING)
  *		message: detail for the specified task(STRING)
  *		work: total work required for the current task(INTEGER)
  *		work_done: work completed for the current task(INTEGER)
  *
  * Methods:
  */ 
  class ISymphonyUpdateState {
 	  	
  	//Property attributes
	var $task = "";
	var $message = "";
	var $work = "";
	var $work_done = "";
  	
  }	
?>
