<?php
/*
 *Name         : functions.inc.php
 *Author       : Michael Yara
 *Created      : June 27, 2008
 *Last Updated : Mar 8, 2012
 *History      : 2.9
 *Purpose      : FreePBX module that automatically updates the iSymphony configuration from the FreePBX configuration.
 *Copyright    : 2012 HEHE Enterprises, LLC
 */

//Include iSymphony library
require_once(dirname(__FILE__)."/isymphony/isymphony.php");

//ParkAndAnnounce Class
class ext_isymphony_parkandannounce {
	var $template;
	var $timeout;
	var $dial;
	var $return_context;
	
	function ext_isymphony_parkandannounce($template, $timeout, $dial, $return_context) {
		$this->template = $template;
		$this->timeout = $timeout;
		$this->dial = $dial;
		$this->return_context = $return_context;
	}
	
	function output() {
		return "ParkAndAnnounce(" . $this->template . "," . $this->timeout . "," . $this->dial . "," . $this->return_context . ")";
	}
}

//Control
class ext_isymphony_controlplayback {
	var $fileName;
	var $skipMinutes;
	var $ff;
	var $rew;
	var $stop;
	var $pause;
	var $restart;
		
	function ext_isymphony_controlplayback($fileName, $skipMinutes, $ff, $rew, $stop, $pause, $restart) {
		$this->fileName = $fileName;
		$this->skipMinutes = $skipMinutes;
		$this->ff = $ff;
		$this->rew = $rew;
		$this->stop = $stop;
		$this->pause = $pause;
		$this->restart = $restart;
	}
	
	function output() {
		return "ControlPlayback(" . $this->fileName . "," . $this->skipMinutes . "," . $this->ff . "," . $this->rew . "," . $this->stop . "," . $this->pause . "," . $this->restart . ")";
	}
}

//ChanSpy Class
class ext_isymphony_chanspy {
	var $prefix;
	var $options;
	
	function ext_isymphony_chanspy($prefix, $options) {
		$this->prefix = $prefix;
		$this->options = $options;
	}
	
	function output() {
		return "ChanSpy(" . $this->prefix . "," . $this->options . ")";
	}
}
//If check box GUI element does not exist add it
if(!class_exists("gui_checkbox")) {
	class gui_checkbox extends guiinput {
	       function gui_checkbox($elemname, $checked=false, $prompttext='', $helptext='', $value='on', $post_text = '', $jsonclick = '', $disable=false) {
	               $parent_class = get_parent_class($this);
	               parent::$parent_class($elemname, '', $prompttext, $helptext);
	
	               $itemchecked = $checked ? 'checked' : '';
	               $disable_state = $disable ? 'disabled="true"' : '';
	               $js_onclick_include = ($jsonclick != '') ? 'onclick="' . $jsonclick. '"' : '';
	               $tabindex = function_exists("guielement::gettabindex") ? "tabindex=" . guielement::gettabindex() : ""; 

	               $this->html_input = "<input type=\"checkbox\" name=\"$this->_elemname\" id=\"$this->_elemname\" $disable_state $tabindex value=\"$value\" $js_onclick_include $itemchecked/>$post_text\n";
	       }
	}
}

//Main contributing module function------------------------------------------------------------------------------------------------------------------
function isymphony_get_config($engine) {
	global $ext, $amp_conf, $db;
  	
  	switch($engine) {
  		case 'asterisk':	
  	
			ob_start();	
			
			//Open file for error log
			$errorLogFile = fopen($amp_conf['AMPWEBROOT'] . "/admin/modules/isymphony/error.txt", 'w');
			
			//Open debug log
			if(($debugLogFile = fopen($amp_conf['AMPWEBROOT'] . "/admin/modules/isymphony/debug.txt", 'w')) === false) {
				isymphony_write_to_file($errorLogFile, "Could not open debug log for writing.\n");
			}
					
			//Check if a manager profile exists for iSymphony if not create it.
			$managerFound = false;
			if((function_exists("manager_list")) && (($managers = manager_list()) !== null)) {
				
				//Search for iSymphony manager
				foreach($managers as $manager) {
					if($manager['name'] == "isymphony" ) {
						$managerFound = true;
						break;
					}
				}
			}

			//If not found create a manager profile for isymphony
			if((function_exists("manager_add")) && (!$managerFound)) {
				manager_add("isymphony", "ismanager*con", "0.0.0.0/0.0.0.0", "127.0.0.1/255.255.255.0", "all", "all");
				
				if(function_exists("manager_gen_conf")) {
					manager_gen_conf();
				}
			}
		
			//Determine agent login context based on FreePBX Version
			$freepbxVersion = get_framework_version();
			$freepbxVersion = $freepbxVersion ? $freepbxVersion : getversion();
			$agentLoginContext = "from-internal";
			if(version_compare_freepbx($freepbxVersion, "2.6", ">=")) {
				$agentLoginContext = "from-queue";
			}
			
			//Connect to server
  			if(($locationInformation = isymphony_location_get()) !== null) {
  				$isymphony = new iSymphony($locationInformation["isymphony_host"]);
  				if(($isymphony->iSymphonyConnect()) === false) {
  					isymphony_write_to_file($errorLogFile, "Could not connect to server. iSymphony::$ISERROR \n");
  					fclose($debugLogFile);
					fclose($errorLogFile);
					ob_end_clean();
  					break;
  				}
  			} else {
  				isymphony_write_to_file($errorLogFile, "Could not connect to server: Failed to find location host \n");
  				break;
  			}
			
			//Grab server version and revision
			$serverVersion = "2.0";
			$serverRevision = "1104";
			if($isymphony) {
				if((($serverVersionCheck = $isymphony->getiSymphonyServerVersion()) !== false)) {
					$serverVersion = $serverVersionCheck;
				} else {
					isymphony_write_to_file($errorLogFile, "Could not get server version. iSymphony::$ISERROR \n");
				}
				
				if((($serverRevisonCheck = $isymphony->getiSymphonyServerRevision()) !== false)) {
					$serverRevision = $serverRevisonCheck;
				} else {
					isymphony_write_to_file($errorLogFile, "Could not get server revision. iSymphony::$ISERROR \n");
				}
				
				//$isymphony->iSymphonyDisconnect();
			} else {
				isymphony_write_to_file($errorLogFile, "Could not get server version/revision. iSymphony::$ISERROR \n");
			}
		
			isymphony_write_to_file($debugLogFile, "Server Version:" . $serverVersion . " Server Revision:" . $serverRevision . "\n");
		
			//Check if server version is 2.0 or 2.1 and add appropriate contexts
			if($serverRevision >= "1174") {
				
				isymphony_write_to_file($debugLogFile, "Using 2.1 contexts\n");
				
				//Determine context prefix
				if($serverRevision >= "1903") {
					$contextPrefix = "c-x-operator-panel";
				} else {
					$contextPrefix = "isymphony";
				}
				
				//Determine variable prefix
				if($serverRevision >= "1912") {
					$variablePrefix = "XMLNamespace";
				} else {
					$variablePrefix = "iSymphony";
				}
				
				//Query parking timeout
				$parkingTimeout = 200;
				$sql = "SELECT keyword, data FROM parkinglot WHERE id = '1'";
				$results = $db->getAssoc($sql);
				if(!DB::IsError($results)) {
					$parkingTimeout = $results['parkingtime'];
				}
				
				isymphony_write_to_file($debugLogFile, "Using context prefix \"{$contextPrefix}\"\n");
				isymphony_write_to_file($debugLogFile, "Using variable prefix \"{$variablePrefix}\"\n");
				
				$id = $contextPrefix . "-hold";
			    $c = '432111';
			    $ext->add($id, $c, '', new ext_musiconhold("\${{$variablePrefix}MusicOnHoldClass}"));
			    $ext->add($id, $c, '', new ext_hangup());
			
				$id = $contextPrefix . "-voice-mail";
			    $c = '432112';
			    $ext->add($id, $c, '', new ext_vm("\${{$variablePrefix}VoiceMailBox}@\${{$variablePrefix}VoiceMailBoxContext},u"));
			    $ext->add($id, $c, '', new ext_hangup());
			    
			    //Check if we are using confbridge or meetme
			    if ($amp_conf['ASTCONFAPP'] == 'app_confbridge') {
			    	$id = $contextPrefix . "-confbridge";
			    	$c = '432113';
			    	$ext->add($id, $c, '', new ext_meetme("\${{$variablePrefix}MeetMeRoomNumber}");
			    	$ext->add($id, $c, '', new ext_hangup());
			    } else {
			    	$id = $contextPrefix . "-meetme";
			    	$c = '432113';
			    	$ext->add($id, $c, '', new ext_meetme("\${{$variablePrefix}MeetMeRoomNumber}", "\${{$variablePrefix}MeetMeRoomOptions}", ""));
			    	$ext->add($id, $c, '', new ext_hangup());
			    }
			    			    			    
			    $id = $contextPrefix . "-park";
			    $c = '432114';
			    $ext->add($id, $c, '', new ext_isymphony_parkandannounce("pbx-transfer:PARKED", "$parkingTimeout", "Local/432116@" . $contextPrefix . "-park-announce-answer", "\${{$variablePrefix}ParkContext},\${{$variablePrefix}ParkExtension},1"));
			    $ext->add($id, $c, '', new ext_hangup());
			    
			    $id = $contextPrefix . "-park-announce-answer";
			    $c = '432116';
			    $ext->add($id, $c, '', new ext_answer());
			    $ext->add($id, $c, '', new ext_hangup());
			    
			    $id = $contextPrefix . "-listen-to-voice-mail";
			    $c = '432115';
			    $ext->add($id, $c, '', new ext_isymphony_controlplayback("\${{$variablePrefix}VoiceMailPath}", "1000", "*", "#", "7", "8" , "9"));
			    $ext->add($id, $c, '', new ext_hangup());

			   	$id = $contextPrefix . "-spy";
			    $c = '432117';
			    $ext->add($id, $c, '', new ext_isymphony_chanspy("\${{$variablePrefix}ChanSpyChannel}", "\${{$variablePrefix}ChanSpyOptions}"));
			    $ext->add($id, $c, '', new ext_hangup());
			    
			    $id = $contextPrefix . "-orig-proxy";
			    $c = '_X.';
			    $ext->add($id, $c, '', new ext_set("CC_RECALL", ""));
			    $ext->add($id, $c, '', new ext_goto("1", "\${EXTEN}", "from-internal"));
			    
			} else {
				isymphony_write_to_file($debugLogFile, "Using 2.0 contexts\n");
				
				$id = "musiconhold";
			    $c = '1000';
			    $ext->add($id, $c, '', new ext_answer(""));
			    $ext->add($id, $c, '', new ext_setvar("isHoldContext", "\${DB(iSymphonyChannelHoldContexts/\${CHANNEL})}"));
			    $ext->add($id, $c, '', new ext_musiconhold("\${isHoldContext}"));
			    $ext->add($id, $c, '', new ext_dbdel("iSymphonyChannelHoldContexts/\${CHANNEL}"));
			    
			    $id = "xfer-2-vm";
			    $c = "_.";
			    $ext->add($id, $c, '', new ext_goto("1", "\${VM_PREFIX}\${EXTEN}", "from-internal"));
			    $ext->add($id, $c, '', new ext_hangup());
	
			    $id = "ibarge";
				$ext->add($id, "_1X.", '', new ext_meetme("\${EXTEN:1}", "qd", "271721"));
			    $ext->add($id, "_1X.", '', new ext_hangup());
			    $ext->add($id, "_2X.", '', new ext_meetme("\${EXTEN:1}", "aq", "271721"));
			    $ext->add($id, "_2X.", '', new ext_hangup());
	
				$id = "imeetme";
				$c = "_.";
				$ext->add($id, "h", '', new ext_hangup());
				$ext->add($id, $c, '', new ext_setvar("roomOptions", "\${DB(iSymphonyMeetMeOptions/\${EXTEN})}"));
				$ext->add($id, $c, '', new ext_meetme("\${EXTEN}", "\${roomOptions}", "271721"));
				$ext->add($id, $c, '', new ext_hangup());
	
				$id = "ipark";
				$c = "_.";
				$ext->add($id, "h", '', new ext_hangup());
				$ext->add($id, $c, '', new ext_setvar("parkContext", "\${DB(iSymphonyParkContexts/\${EXTEN})}"));
				$ext->add($id, $c, '', new ext_answer(""));
				$ext->add($id, $c, '', new ext_wait("1"));
				$ext->add($id, $c, '', new ext_isymphony_parkandannounce("", "", "", "\${parkContext},\${EXTEN},1"));
				$ext->add($id, $c, '', new ext_dbdel("iSymphonyParkContexts/\${EXTEN}"));
			}

			//Check the Asterisk version, USEDEVSTATE, and USEQUEUESTATE info to determine how the agent login iterface should be set.
			$agentInterfaceType = "none";
			$info = engine_getinfo();
			$devStateEnabled = isset($amp_conf["USEDEVSTATE"]) && isset($amp_conf["USEQUEUESTATE"]) && $amp_conf["USEDEVSTATE"] === true && $amp_conf["USEQUEUESTATE"] === true;
			
			if(version_compare($info["version"], "1.6", ">=") || (version_compare($info["version"], "1.4.25", ">=") && !$devStateEnabled)) {
				isymphony_write_to_file($debugLogFile, "Using peer agent interface\n");
				$agentInterfaceType = "peer";
			} else if(version_compare($info["version"], "1.4.25", ">=") && $devStateEnabled) {
				isymphony_write_to_file($debugLogFile, "Using hint agent interface\n");
				$agentInterfaceType = "hint";
			} else {
				isymphony_write_to_file($debugLogFile, "Using no agent interface\n");
				$agentInterfaceType = "none";
			}

			//Close iSymphony connection
			$isymphony->iSymphonyDisconnect();
			
			//Close file handlers
			fclose($debugLogFile);
			fclose($errorLogFile);
			
			//Check if modify.php script needs to boot strap for security
			$bootStrapModify = version_compare_freepbx($freepbxVersion, "2.10", ">=") ? "true" : "false";
		
			//Execute modify script and continue on without waiting for return
			exec("php " . $amp_conf['AMPWEBROOT'] . "/admin/modules/isymphony/modify.php " . $amp_conf["AMPDBHOST"] . " " . $amp_conf["AMPDBNAME"] . " " . $amp_conf["AMPDBUSER"] . " " . $amp_conf["AMPDBPASS"] . " " . $serverRevision . " " . $agentLoginContext . " " . $agentInterfaceType . " " . $amp_conf['AMPWEBROOT'] . " " . $bootStrapModify . " > " . $amp_conf['AMPWEBROOT'] . "/admin/modules/isymphony/modify_error.txt 2>&1 &");
		
			ob_end_clean();
			break;
  	}
}

//iSymphony module API location functions
function isymphony_location_update($adminUserName, $adminPassword, $originateTimeout, $autoReload, $pageStatusEnabled, $jabberHost, $jabberDomain, $jabberResource, $jabberPort, $logJabberMessages, $isymphonyHost, $isymphonyLocation, $isymphonyTenant, $isymphonyClientPort, $asteriskHost) {
	global $db;	
	$autoReload = $autoReload ? "1" : "0";
	$pageStatusEnabled = $pageStatusEnabled ? "1" : "0";
	$prepStatement = $db->prepare("UPDATE isymphony_location SET admin_user_name = ?, admin_password = ?, originate_timeout = ?, auto_reload = ?, page_status_enabled = ?, jabber_host = ?, jabber_domain = ?, jabber_resource = ?, jabber_port = ?, log_jabber_messages = ?, isymphony_host = ?, isymphony_location = ?, isymphony_tenant = ?, isymphony_client_port = ?, asterisk_host = ?");
	$values = array($adminUserName, $adminPassword, $originateTimeout, $autoReload, $pageStatusEnabled, $jabberHost, $jabberDomain, $jabberResource, $jabberPort, $logJabberMessages, $isymphonyHost, $isymphonyLocation, $isymphonyTenant, $isymphonyClientPort, $asteriskHost);
	$db->execute($prepStatement, $values);
}

function isymphony_location_get() {
	global $db;	
	$query = "SELECT * FROM isymphony_location";
	$results = sql($query, "getRow", DB_FETCHMODE_ASSOC);
	if((DB::IsError($results)) || (empty($results))) {
		return null;
	} else {
		return $results;
	}
}

//iSymphony module API user functions
function isymphony_user_add($userId, $addExtension, $addProfile, $password, $displayName, $peer, $cell_phone, $email, $autoAnswer, $jabberHost, $jabberDomain, $jabberResource, $jabberPort, $jabberUserName, $jabberPassword) {
	global $db;	
	$addProfile = $addProfile ? "1" : "0";
	$addExtension = $addExtension ? "1" : "0";
	$autoAnswer = $autoAnswer ? "1" : "0";
	$prepStatement = $db->prepare("INSERT INTO isymphony_users (user_id, add_extension, add_profile, password, display_name, peer, cell_phone, email, auto_answer, jabber_host, jabber_domain, jabber_resource, jabber_port, jabber_user_name, jabber_password) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ? ,?, ?, ?)");
	$values = array($userId, $addExtension, $addProfile, $password, $displayName, $peer, $cell_phone, $email, $autoAnswer, $jabberHost, $jabberDomain, $jabberResource, $jabberPort, $jabberUserName, $jabberPassword);
	$db->execute($prepStatement, $values);
}

function isymphony_user_update($userId, $addExtension, $addProfile, $password, $displayName, $peer, $cell_phone, $email, $autoAnswer, $jabberHost, $jabberDomain, $jabberResource, $jabberPort, $jabberUserName, $jabberPassword) {
	global $db;	
	$addProfile = $addProfile ? "1" : "0";
	$addExtension = $addExtension ? "1" : "0";
	$autoAnswer = $autoAnswer ? "1" : "0";
	$prepStatement = $db->prepare("UPDATE isymphony_users SET add_extension = ?, add_profile = ?, password = ?, display_name = ?, peer = ?, cell_phone = ?, email = ?, auto_answer = ?, jabber_host = ?, jabber_domain = ?, jabber_resource = ?, jabber_port = ?, jabber_user_name = ?, jabber_password = ? WHERE user_id = $userId");
	$values = array($addExtension, $addProfile, $password, $displayName, $peer, $cell_phone, $email, $autoAnswer, $jabberHost, $jabberDomain, $jabberResource, $jabberPort, $jabberUserName, $jabberPassword);
	$db->execute($prepStatement, $values);
}

function isymphony_user_del($userId) {
	global $db;	
	$query = "DELETE FROM isymphony_users WHERE user_id = '$userId'";
    $db->query($query);
}

function isymphony_user_list() {
	global $db;	
	$query = "SELECT * FROM isymphony_users";
	$results = sql($query, "getAll", DB_FETCHMODE_ASSOC);
	if((DB::IsError($results)) || (empty($results))) {
		return array();
	} else {
		return $results;
	}
}

function isymphony_user_get($userId) {
	global $db;	
	$query = "SELECT * FROM isymphony_users WHERE user_id = '$userId'";
	$results = sql($query, "getRow", DB_FETCHMODE_ASSOC);
	if((DB::IsError($results)) || (empty($results))) {
		return null;
	} else {
		return $results;
	}
}

function isymphony_queue_eventwhencalled_modify($addQueue) {
	$addQueue = $addQueue ? "1" : "0";
	if ($addQueue == "1") {
		$_REQUEST['eventwhencalled'] = 'yes';
	}
}
		
function isymphony_queue_eventmemberstatus_modify($addQueue) {
	$addQueue = $addQueue ? "1" : "0";
	if ($addQueue == "1") {
		$_REQUEST['eventmemberstatus'] = 'yes';	
	}
}

//iSymphony module API queue functions
function isymphony_queue_add($queueId, $addQueue, $displayName) {
	global $db;
	$addQueue = $addQueue ? "1" : "0";	
	$prepStatement = $db->prepare("INSERT INTO isymphony_queues (queue_id, add_queue, display_name) VALUES (?, ?, ?)");
	$values = array($queueId, $addQueue, $displayName);
	$db->execute($prepStatement, $values);
}

function isymphony_queue_update($queueId, $addQueue, $displayName) {
	global $db;	
	$addQueue = $addQueue ? "1" : "0";	
	$prepStatement = $db->prepare("UPDATE isymphony_queues SET add_queue = ?, display_name = ? WHERE queue_id = $queueId");
	$values = array($addQueue, $displayName);
	$db->execute($prepStatement, $values);
}

function isymphony_queue_del($queueId) {
	global $db;	
	$query = "DELETE FROM isymphony_queues WHERE queue_id = '$queueId'";
    $db->query($query);
}

function isymphony_queue_list() {
	global $db;	
	$query = "SELECT * FROM isymphony_queues";
	$results = sql($query, "getAll", DB_FETCHMODE_ASSOC);
	if((DB::IsError($results)) || (empty($results))) {
		return array();
	} else {
		return $results;
	}
}

function isymphony_queue_get($queueId) {
	global $db;	
	$query = "SELECT * FROM isymphony_queues WHERE queue_id = '$queueId'";
	$results = sql($query, "getRow", DB_FETCHMODE_ASSOC);
	if((DB::IsError($results)) || (empty($results))) {
		return null;
	} else {
		return $results;
	}
}

//iSymphony module API conference room functions
function isymphony_conference_room_add($conferenceRoomId, $addConferenceRoom, $displayName) {
	global $db;	
	$addConferenceRoom = $addConferenceRoom ? "1" : "0";	
	$prepStatement = $db->prepare("INSERT INTO isymphony_conference_rooms (conference_room_id, add_conference_room, display_name) VALUES (?, ?, ?)");
	$values = array($conferenceRoomId, $addConferenceRoom, $displayName);
	$db->execute($prepStatement, $values);
}

function isymphony_conference_room_update($conferenceRoomId, $addConferenceRoom, $displayName) {
	global $db;	
	$addConferenceRoom = $addConferenceRoom ? "1" : "0";
	$prepStatement = $db->prepare("UPDATE isymphony_conference_rooms SET add_conference_room = ?, display_name = ? WHERE conference_room_id = $conferenceRoomId");
	$values = array($addConferenceRoom, $displayName);
	$db->execute($prepStatement, $values);
}

function isymphony_conference_room_del($conferenceRoomId) {
	global $db;	
	$query = "DELETE FROM isymphony_conference_rooms WHERE conference_room_id = '$conferenceRoomId'";
    $db->query($query);
}

function isymphony_conference_room_list() {
	global $db;	
	$query = "SELECT * FROM isymphony_conference_rooms";
	$results = sql($query, "getAll", DB_FETCHMODE_ASSOC);
	if((DB::IsError($results)) || (empty($results))) {
		return array();
	} else {
		return $results;
	}
}

function isymphony_conference_room_get($conferenceRoomId) {
	global $db;	
	$query = "SELECT * FROM isymphony_conference_rooms WHERE conference_room_id = '$conferenceRoomId'";
	$results = sql($query, "getRow", DB_FETCHMODE_ASSOC);
	if((DB::IsError($results)) || (empty($results))) {
		return null;
	} else {
		return $results;
	}
}

//Extension/User page hooks
function isymphony_configpageinit($pagename) {
	global $currentcomponent;

	//Query page state
	$action = isset($_REQUEST["action"]) ? $_REQUEST["action"] : null;
	$extdisplay = isset($_REQUEST["extdisplay"]) ? $_REQUEST["extdisplay"] : null;
	$extension = isset($_REQUEST["extension"]) ? $_REQUEST["extension"] : null;
	$tech_hardware = isset($_REQUEST["tech_hardware"]) ? $_REQUEST["tech_hardware"] : null;

	//Based on the page state determine if the display or process functions should be added
	if (($pagename != "users") && ($pagename != "extensions")) { 
		return;
	} else if ($tech_hardware != null || $pagename == "users") {
		isymphony_extension_applyhooks();
		$currentcomponent->addprocessfunc('isymphony_extension_configprocess', 8);
	} elseif ($action == "add" || $action == "edit") {
		$currentcomponent->addprocessfunc('isymphony_extension_configprocess', 8);
	} elseif ($extdisplay != '') {
		isymphony_extension_applyhooks();
		$currentcomponent->addprocessfunc('isymphony_extension_configprocess', 8);
	}
}

function isymphony_extension_applyhooks() {
	global $currentcomponent;
	$currentcomponent->addguifunc("isymphony_extension_configpageload");
}

function isymphony_extension_configpageload() {
	global $currentcomponent;

	//Query page state
	$action = isset($_REQUEST["action"]) ? $_REQUEST["action"] : null;
	$display = isset($_REQUEST["display"]) ? $_REQUEST["display"] : null;
	$extension = isset($_REQUEST["extdisplay"]) ? $_REQUEST["extdisplay"] : null;
	
	//Attempt to query element if not found set defaults
	if(($extension !== null) && (($iSymphonyUser = isymphony_user_get($extension)) !== null)) {
		$added = ($iSymphonyUser["add_extension"] == "1");
		$userProfile = ($iSymphonyUser["add_profile"] == "1");
		$password = $iSymphonyUser["password"];
		$email	= $iSymphonyUser["email"];
		$cell_phone = $iSymphonyUser["cell_phone"];
		$autoAnswer = $iSymphonyUser["auto_answer"];
		$jabberHost = $iSymphonyUser["jabber_host"];
		$jabberDomain = $iSymphonyUser["jabber_domain"];
		$jabberResource = $iSymphonyUser["jabber_resource"];
		$jabberPort = $iSymphonyUser["jabber_port"];
		$jabberUserName = $iSymphonyUser["jabber_user_name"];
		$jabberPassword = $iSymphonyUser["jabber_password"];
	
	} else {
		$added = true;
		$userProfile = true;
		$password = "secret";
		$email = "";
		$cell_phone = "";
		$autoAnswer = false;	
		$jabberHost = "";
		$jabberDomain = "";
		$jabberResource = "iSymphony";
		$jabberPort = "5222";
		$jabberUserName = "";
		$jabberPassword = "";
	}
	
	//Create GIU elements if not on delete page
	if ($action != "del") {
		$section = _("iSymphony Profile Settings");
		$currentcomponent->addguielem($section,	new gui_checkbox("isymphony_add_profile", $userProfile, "Create Profile", "Creates an iSymphony login profile which is associated with this extension.", "on", "", "", false));
		$currentcomponent->addguielem($section, new gui_textbox("isymphony_profile_password", $password, "Profile Password", "Specifies the password to be used for this profile.", "", "", true, "100", false));	
		
		$section = _("iSymphony Extension Settings");
		$currentcomponent->addguielem($section,	new gui_checkbox("isymphony_add_extension", $added, "Add to iSymphony", "Makes this Extension/User available in iSymphony.", "on", "", "", false));
		$currentcomponent->addguielem($section,	new gui_checkbox("isymphony_auto_answer", $autoAnswer, "Auto Answer", "Makes this extension automatically answer the initial call received from the system when performing an origination within the panel. Only works with Aastra, Grandstream, Linksys, Polycom, and Snom phones.", "on", "", "", false));
		$currentcomponent->addguielem($section,	new gui_textbox("isymphony_add_email", $email, "Email Address", "The email address entered here will be used whenever someone clicks the email icon for this extension.", "", "", true, "100"));
		$currentcomponent->addguielem($section,	new gui_textbox("isymphony_add_cell_phone", $cell_phone, "Cell Phone", "The number entered here will be used whenever someone clicks the cell phone icon for this extension.", "", "", true, "100"));

		$section = _("iSymphony Jabber Settings");
		$currentcomponent->addguielem($section,	new gui_textbox("isymphony_jabber_host", $jabberHost, "Host", "Host or IP of jabber server this extension will connect to.", "", "", true, "100"));
		$currentcomponent->addguielem($section,	new gui_textbox("isymphony_jabber_domain", $jabberDomain, "Domain", "Domain this extension will connect to the jabber server with.", "", "", true, "100"));
		$currentcomponent->addguielem($section,	new gui_textbox("isymphony_jabber_resource", $jabberResource, "Resource", "Resource this extension will connect to the jabber server with.", "", "", true, "100"));
		$currentcomponent->addguielem($section,	new gui_textbox("isymphony_jabber_port", $jabberPort, "Port", "Port this extension will connect to the jabber server with.", "", "", true, "100"));
		//We need to check for whitespace in the Jabber Username or else we could break iSymphony Chat
		$currentcomponent->addguielem($section,	new gui_textbox("isymphony_jabber_user_name", $jabberUserName, "UserName", "UserName this extension will connect to the jabber server with.", "", "", true, "100"));
		$currentcomponent->addguielem($section,	new gui_textbox("isymphony_jabber_password", $jabberPassword, "Password", "Password this extension will connect to the jabber server with.", "", "", true, "100"));
	}
}

function isymphony_extension_configprocess() {
	
	//Query page state
	$action = isset($_REQUEST["action"]) ? $_REQUEST["action"] : null;
	$ext = isset($_REQUEST["extdisplay"]) ? $_REQUEST["extdisplay"] : null;
	$extn = isset($_REQUEST["extension"]) ? $_REQUEST["extension"]: null;
	$name = isset($_REQUEST["name"]) ? $_REQUEST["name"] : null;
	$extension = ($ext == "") ? $extn : $ext;
	
	//Determine peer
	if(isset($_REQUEST["devinfo_dial"]) && ($_REQUEST["devinfo_dial"] != "")) {
		$peer = $_REQUEST["devinfo_dial"];
	} else if (isset($_REQUEST["tech"])){
		$peer = strtoupper($_REQUEST["tech"]) . "/" . $extension;
	} else {
		$peer = "SIP/$extension";
	}
		
	//Query iSymphony options
	$addExtension = isset($_REQUEST["isymphony_add_extension"]);
	$addProfile = isset($_REQUEST["isymphony_add_profile"]);
	$autoAnswer = isset($_REQUEST["isymphony_auto_answer"]);
	$password = isset($_REQUEST["isymphony_profile_password"]) ? $_REQUEST["isymphony_profile_password"]: null;
	$password = (($password === null) || (trim($password) == "")) ? "secret" : trim($password);

	// Cell Phone
	$cell_phone = isset($_REQUEST["isymphony_add_cell_phone"]) ? $_REQUEST["isymphony_add_cell_phone"] : null;		
	
	// Email 
	$email = isset($_REQUEST["isymphony_add_email"]) ? $_REQUEST["isymphony_add_email"] : null;	
	
	//Jabber info
	$jabberHost = isset($_REQUEST["isymphony_jabber_host"]) ? $_REQUEST["isymphony_jabber_host"] : null;	
	$jabberDomain = isset($_REQUEST["isymphony_jabber_domain"]) ? $_REQUEST["isymphony_jabber_domain"] : null;	
	$jabberResource = isset($_REQUEST["isymphony_jabber_resource"]) ? $_REQUEST["isymphony_jabber_resource"] : "iSymphony";	
	$jabberPort = (isset($_REQUEST["isymphony_jabber_port"]) && is_numeric($_REQUEST["isymphony_jabber_port"])) ? $_REQUEST["isymphony_jabber_port"] : "5222";	
	$jabberUserName = isset($_REQUEST["isymphony_jabber_user_name"]) ? $_REQUEST["isymphony_jabber_user_name"] : null;	
	$jabberPassword = isset($_REQUEST["isymphony_jabber_password"]) ? $_REQUEST["isymphony_jabber_password"] : null;	

	//Mask spaces in jabber user name
	if($jabberUserName != null) {
		$jabberUserName = str_replace(" ", "_", $jabberUserName);
		$_REQUEST["isymphony_jabber_user_name"] = $jabberUserName;
	}

	//Modify DB
	if(($extension !== null) && ($extension != "") && ($action !== null)) {
		
		//Check if this extension needs to be deleted, updated, or added
		if($action == "del") {
			isymphony_user_del($extension);
		} else if(($action == "add") || ($action == "edit") && ($name !== null)) {
			if(isymphony_user_get($extension) === null) {
				isymphony_user_add($extension, $addExtension, $addProfile, $password, $name, $peer, $cell_phone, $email, $autoAnswer, $jabberHost, $jabberDomain, $jabberResource, $jabberPort, $jabberUserName, $jabberPassword);
			} else {
				isymphony_user_update($extension, $addExtension, $addProfile, $password, $name, $peer, $cell_phone, $email, $autoAnswer, $jabberHost, $jabberDomain, $jabberResource, $jabberPort, $jabberUserName, $jabberPassword);
			}
		}
	}
}

//Queue page hooks
function isymphony_hook_queues($viewing_itemid, $target_menuid) {
		
	//Query page state
	$action = isset($_REQUEST["action"]) ? $_REQUEST["action"] : null;
	$display = "";
	
	//Only hook queues page
  	if(($target_menuid == "queues") && ($action != "delete")) {
  		
  		//Query queue info
  		if(($viewing_itemid != null) && ($iSymphonyQueue = isymphony_queue_get($viewing_itemid))) {
  			$checked = ($iSymphonyQueue["add_queue"] == "1") ? "checked" : "";
  		} else {
  			$checked = "checked";
  		}
  		
  		//Build display
  		$display = "	<tr><td colspan=\"2\"><h5>iSymphony<hr></h5></td></tr>
						<tr>
							<td><a href=\"#\" class=\"info\">" . _("Add to iSymphony") . "<span>" . _("Makes this queue available in iSymphony") . "</span></a></td>
							<td><input type=\"checkbox\" name=\"isymphony_add_queue\" id=\"isymphony_add_queue\" value=\"on\" $checked/></td>
						</tr>";
  	}
 
  	return $display;
}

function isymphony_hookProcess_queues($viewing_itemid, $request) {

	//Query page state
	$queue = isset($request["extdisplay"]) ? $request["extdisplay"] : null;
	$account = isset($request["account"]) ? $request["account"] : null;
	$action = isset($request["action"]) ? $request["action"] : null;
	$name = isset($request["name"]) ? $request["name"] : null;
	$queue = ($queue == null) ? $account : $queue;
	
	//Query iSymphony option
	$addQueue = isset($request["isymphony_add_queue"]);
	
	//Update DB
	if(($queue != null) && ($queue != "") && ($action != null)) {
		
		//Check if this queue needs to be deleted, updated, or added
		if($action == "delete") {
			isymphony_queue_del($queue);
		} else if(($action == "add") || ($action == "edit") && ($name !== null)) {
			if(isymphony_queue_get($queue) === null) {
				isymphony_queue_add($queue, $addQueue, $name);
			} else {
				isymphony_queue_update($queue, $addQueue, $name);
			}
		
			isymphony_queue_eventwhencalled_modify($addQueue);
			isymphony_queue_eventmemberstatus_modify($addQueue);
		}
	}
}

//Conference Room page hooks
function isymphony_hook_conferences($viewing_itemid, $target_menuid) {
		
	//Query page state
	$action = isset($_REQUEST["action"]) ? $_REQUEST["action"] : null;
	$display = "";
	
	//Only hook conferences page
  	if(($target_menuid == "conferences") && ($action != "delete")) {
  		
  		//Query conference info
  		if(($viewing_itemid != null) && ($iSymphonyConferenceRoom = isymphony_conference_room_get($viewing_itemid))) {
  			$checked = ($iSymphonyConferenceRoom["add_conference_room"] == "1") ? "checked" : "";
  		} else {
  			$checked = "checked";
  		}
  		
  		//Build display
  		$display = "	<tr><td colspan=\"2\"><h5>iSymphony<hr></h5></td></tr>
						<tr>
							<td><a href=\"#\" class=\"info\">" . _("Add to iSymphony") . "<span>" . _("Makes this conference room available in iSymphony") . "</span></a></td>
							<td><input type=\"checkbox\" name=\"isymphony_add_conference_room\" id=\"isymphony_add_conference_room\" value=\"on\" $checked/></td>
						</tr>";
  	}
 
  	return $display;
}

function isymphony_hookProcess_conferences($viewing_itemid, $request) {

	//Query page state
	$conferenceRoom = isset($request["extdisplay"]) ? $request["extdisplay"] : null;
	$account = isset($request["account"]) ? $request["account"] : null;
	$action = isset($request["action"]) ? $request["action"] : null;
	$name = isset($request["name"]) ? $request["name"] : null;
	$conferenceRoom = ($conferenceRoom == null) ? $account : $conferenceRoom;
	
	//Query iSymphony option
	$addConferenceRoom = isset($request["isymphony_add_conference_room"]);
	
	//Update DB
	if(($conferenceRoom != null) && ($conferenceRoom != "") && ($action != null)) {
		
		//Check if this conference room needs to be deleted, updated, or added
		if($action == "delete") {
			isymphony_conference_room_del($conferenceRoom);
		} else if(($action == "add") || ($action == "edit") && ($name !== null)) {
			if(isymphony_conference_room_get($conferenceRoom) === null) {
				isymphony_conference_room_add($conferenceRoom, $addConferenceRoom, $name);
			} else {
				isymphony_conference_room_update($conferenceRoom, $addConferenceRoom, $name);
			}
		}
	}
}

//Helper functions
function isymphony_write_to_file($file, $content) {
	if($file) {
		fwrite($file,$content);
	}
}

function isymphony_remove_array_item($array, $item) {
	return explode(',',str_replace($item.',','',(join(',',$array))));
}
?>