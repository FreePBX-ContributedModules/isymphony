<?php
/*
 *Name         : page.isymphony.php
 *Author       : Michael Yara
 *Created      : July 2, 2008
 *Last Updated : Sept 28, 2011
 *History      : 1.0
 *Purpose      : Information page for iSymphony module
 *Copyright    : 2008 HEHE Enterprises, LLC
 */


//Build include path for page info
$inc_path = __FILE__;
$pos = strrpos($inc_path, "/");
$inc_path = substr($inc_path, 0, $pos) . "/";

//Include page info
require_once($inc_path . "module_page_info_include.php");

//Check to see if the server is running
$serverRunning = false;
$serverRunningDisplay = "";

//Check for a location settings update action
if(isset($_REQUEST["isymphony_update_location_settings"])) {

	//Update data base
	isymphony_location_update(	trim($_REQUEST["isymphony_admin_user_name"]), 
								trim($_REQUEST["isymphony_admin_password"]), 
								trim($_REQUEST["isymphony_originate_timeout"]), 
								isset($_REQUEST["isymphony_auto_reload"]) ? "1" : "0", 
								isset($_REQUEST["isymphony_page_status_enabled"]) ? "1" : "0",  
								trim($_REQUEST["isymphony_jabber_host"]), 
								trim($_REQUEST["isymphony_jabber_domain"]),
								trim($_REQUEST["isymphony_jabber_resource"]), 
								trim($_REQUEST["isymphony_jabber_port"]),
								isset($_REQUEST["isymphony_log_jabber_messages"]) ? "1" : "0",
								trim($_REQUEST["isymphony_admin_host"]),
								trim($_REQUEST["isymphony_admin_location"]),
								trim($_REQUEST["isymphony_admin_tenant"]),
								trim($_REQUEST["isymphony_client_port"]),
								trim($_REQUEST["isymphony_asterisk_host"]));

	//Flag FreePBX for reload							
	needreload();							
}

//Connect to server
if(($locationInformation = isymphony_location_get()) !== null) {
	$isymphony = new iSymphony($locationInformation["isymphony_host"]);
	if(($isymphony->iSymphonyConnect()) !== false) {
		$serverRunning = true;
	}
}

$serverRunningDisplay = $serverRunning ? "<span style=\"color: #00FF00\">Up</span>" : "<span style=\"color: #FF0000\">Down</span>";

//Check for a reload action
if($serverRunning && isset($_REQUEST["isymphony_reload"])) {
	
	//Reload server and block for 5 secs so server can repopulate
	$isymphony->reloadISymphonyServer();
	sleep(5);
}

//Grab location information
$locationInformation = isymphony_location_get();

//Check for a license activation action
$licenseActivationError = "";
if($serverRunning && isset($_REQUEST["isymphony_activate_license"]) && isset($_REQUEST["isymphony_license_key"]) && (trim($_REQUEST["isymphony_license_key"]) != "")) {
	
	if($isymphony->activateISymphonyLicense(trim($locationInformation["isymphony_location"]), trim($locationInformation["isymphony_tenant"]), trim($_REQUEST["isymphony_license_key"])) === false) {
		$licenseActivationError = iSymphony::$ISERROR;
	}
	
	//Block for 5 secs to let location reload
	sleep(5);
	
	//Close iSymphony connection
	$isymphony->iSymphonyDisconnect();
}

//Debug url additions
$debugUrlAdditions = "";

//Check if a request to show the database was made
$databaseDisplay = "";
if(isset($_REQUEST["showmoduledb"])) {
	
	$debugUrlAdditions .= "&showmoduledb=yes";
	
	//Build location information table
	$databaseDisplay .= "	<table>
								<tr>
									<tr><td colspan = \"10\"><h5>Location</h5></td></tr>
								</tr>
								<tr>
									<td>Admin User Name&nbsp&nbsp&nbsp</td>
									<td>Admin Password&nbsp&nbsp&nbsp</td>
									<td>Originate Timeout&nbsp&nbsp&nbsp</td>
									<td>Auto Reload&nbsp&nbsp&nbsp</td>
									<td>Page Status Enabled&nbsp&nbsp&nbsp</td>
									<td>Jabber Host&nbsp&nbsp&nbsp</td>
									<td>Jabber Domain&nbsp&nbsp&nbsp</td>
									<td>Jabber Resource&nbsp&nbsp&nbsp</td>
									<td>Jabber Port&nbsp&nbsp&nbsp</td>
									<td>Log Jabber Messages&nbsp&nbsp&nbsp</td>
								</tr>
								<tr>
									<td colspan = \"10\"><hr></td>
								</tr>
								<tr>
									<td>{$locationInformation['admin_user_name']}</td>
									<td>{$locationInformation['admin_password']}</td>
									<td>{$locationInformation['originate_timeout']}</td>
									<td>{$locationInformation['auto_reload']}</td>
									<td>{$locationInformation['page_status_enabled']}</td>
									<td>{$locationInformation['jabber_host']}</td>
									<td>{$locationInformation['jabber_domain']}</td>
									<td>{$locationInformation['jabber_resource']}</td>
									<td>{$locationInformation['jabber_port']}</td>
									<td>{$locationInformation['log_jabber_messages']}</td>
								</tr>
							</table>";
	
	//Build extensions table
	$databaseDisplay .= "	<table>
								<tr>
									<tr><td colspan = \"15\"><h5>Extensions</h5></td></tr>
								</tr>
								<tr>
									<td>User ID&nbsp&nbsp&nbsp</td>
									<td>Add Extension&nbsp&nbsp&nbsp</td>
									<td>Add Profile&nbsp&nbsp&nbsp</td>
									<td>Password&nbsp&nbsp&nbsp</td>
									<td>Display Name&nbsp&nbsp&nbsp</td>
									<td>Peer&nbsp&nbsp&nbsp</td>
									<td>Email&nbsp&nbsp&nbsp</td>
									<td>Cell Phone&nbsp&nbsp&nbsp</td>
									<td>Auto Answer&nbsp&nbsp&nbsp</td>
									<td>Jabber Host&nbsp&nbsp&nbsp</td>
									<td>Jabber Domain&nbsp&nbsp&nbsp</td>
									<td>Jabber Resource&nbsp&nbsp&nbsp</td>
									<td>Jabber Port&nbsp&nbsp&nbsp</td>
									<td>Jabber User Name&nbsp&nbsp&nbsp</td>
									<td>Jabber Password&nbsp&nbsp&nbsp</td>
								<tr>
								</tr>
									<td colspan = \"15\"><hr></td>
								</tr>";

	foreach(isymphony_user_list() as $user) {
		$databaseDisplay .= "	<tr>
									<td>{$user['user_id']}</td>
									<td>{$user['add_extension']}</td>
									<td>{$user['add_profile']}</td>
									<td>{$user['password']}</td>
									<td>{$user['display_name']}</td>
									<td>{$user['peer']}</td>
									<td>{$user['email']}</td>
									<td>{$user['cell_phone']}</td>
									<td>{$user['auto_answer']}</td>
									<td>{$user['jabber_host']}</td>
									<td>{$user['jabber_domain']}</td>
									<td>{$user['jabber_resource']}</td>
									<td>{$user['jabber_port']}</td>
									<td>{$user['jabber_user_name']}</td>
									<td>{$user['jabber_password']}</td>
								</tr>";
	}

	$databaseDisplay .= "</table>";
	
	//Build queues table
	$databaseDisplay .= "	<table>
								<tr>
									<tr><td colspan = \"3\"><h5>Queues</h5></td></tr>
								</tr>
								<tr>
									<td>Queue ID&nbsp&nbsp&nbsp</td>
									<td>Add Queue&nbsp&nbsp&nbsp</td>
									<td>Display Name&nbsp&nbsp&nbsp</td>
								<tr>
								</tr>
									<td colspan = \"3\"><hr></td>
								</tr>";

	foreach(isymphony_queue_list() as $queue) {
		$databaseDisplay .= "	<tr>
									<td>{$queue['queue_id']}</td>
									<td>{$queue['add_queue']}</td>
									<td>{$queue['display_name']}</td>
								</tr>";
	}

	$databaseDisplay .= "</table>";
	
	//Build conference room table
	$databaseDisplay .= "	<table>
								<tr>
									<tr><td colspan = \"3\"><h5>Conference Rooms</h5></td></tr>
								</tr>
								<tr>
									<td>Conference Room ID&nbsp&nbsp&nbsp</td>
									<td>Add Conference Room&nbsp&nbsp&nbsp</td>
									<td>Display Name&nbsp&nbsp&nbsp</td>
								<tr>
								</tr>
									<td colspan = \"3\"><hr></td>
								</tr>";

	foreach(isymphony_conference_room_list() as $conferenceRoom) {
		$databaseDisplay .= "	<tr>
									<td>{$conferenceRoom['conference_room_id']}</td>
									<td>{$conferenceRoom['add_conference_room']}</td>
									<td>{$conferenceRoom['display_name']}</td>
								</tr>";
	}

	$databaseDisplay .= "</table>";
}

//Check if a request to show module debug log was made
$debugLogDisplay = "";
if(isset($_REQUEST["showmoduledebuglog"])) {
	
	$debugUrlAdditions .= "&showmoduledebuglog=yes";
	
	$debugLogDisplay .= "	<tr><td colspan=\"2\"><h5>Module Debug Log<hr></h5></td></tr>
							<tr><td colspan=\"2\"><p>";
	
	//Open debug log
	if(($debugLogFile = fopen($amp_conf['AMPWEBROOT'] . "/admin/modules/isymphony/debug.txt", 'r')) !== false) {
		while (!feof($debugLogFile)) {
        	$line = fgets($debugLogFile, 4096);
        	$debugLogDisplay .= htmlspecialchars($line) . "<br>";
    	}
    	fclose($debugLogFile);
	}

	$debugLogDisplay .= "</p></td></tr>";
}

//Check if a request to show module error log was made
if(isset($_REQUEST["showmoduleerrorlog"])) {
	
	$debugUrlAdditions .= "&showmoduleerrorlog=yes";
	
	$debugLogDisplay .= "	<tr><td colspan=\"2\"><h5>Module Error Log<hr></h5></td></tr>
							<tr><td colspan=\"2\"><p>";

	//Open error log
	if(($errorLogFile = fopen($amp_conf['AMPWEBROOT'] . "/admin/modules/isymphony/error.txt", 'r')) !== false) {
		while (!feof($errorLogFile)) {
        	$line = fgets($errorLogFile, 4096);
        	$debugLogDisplay .= htmlspecialchars($line) . "<br>";
    	}
    	fclose($errorLogFile);
	}
	
	$debugLogDisplay .= "</p></td></tr>";
}

//Check if a request to show server error log was made
if(isset($_REQUEST["showservererrorlog"])) {
	
	$debugUrlAdditions .= "&showservererrorlog=yes";
	
	$debugLogDisplay .= "	<tr><td colspan=\"2\"><h5>Server Error Log<hr></h5></td></tr>
							<tr><td colspan=\"2\"><p>";

	//Open error log
	if(($errorLogFile = fopen("/opt/isymphony/server/logs/error.txt", 'r')) !== false) {
		while (!feof($errorLogFile)) {
        	$line = fgets($errorLogFile, 4096);
        	$debugLogDisplay .= htmlspecialchars($line) . "<br>";
    	}
    	fclose($errorLogFile);
	}
	
	$debugLogDisplay .= "</p></td></tr>";
}

//Check if a request to show server core log was made
if(isset($_REQUEST["showservercorelog"])) {
	
	$debugUrlAdditions .= "&showservercorelog=yes";
	
	$debugLogDisplay .= "	<tr><td colspan=\"2\"><h5>Server Core Log<hr></h5></td></tr>
							<tr><td colspan=\"2\"><p>";

	//Open error log
	if(($errorLogFile = fopen("/opt/isymphony/server/logs/core-events.txt", 'r')) !== false) {
		while (!feof($errorLogFile)) {
        	$line = fgets($errorLogFile, 4096);
        	$debugLogDisplay .= htmlspecialchars($line) . "<br>";
    	}
    	fclose($errorLogFile);
	}
	
	$debugLogDisplay .= "</p></td></tr>";
}

//Check if a request to show server communication log was made
if(isset($_REQUEST["showservercommunicationlog"])) {
	
	$debugUrlAdditions .= "&showservercommunicationlog=yes";
	
	$debugLogDisplay .= "	<tr><td colspan=\"2\"><h5>Server Communication Log<hr></h5></td></tr>
							<tr><td colspan=\"2\"><p>";

	//Open error log
	if(($errorLogFile = fopen("/opt/isymphony/server/logs/communication.txt", 'r')) !== false) {
		while (!feof($errorLogFile)) {
        	$line = fgets($errorLogFile, 4096);
        	$debugLogDisplay .= htmlspecialchars($line) . "<br>";
    	}
    	fclose($errorLogFile);
	}
	
	$debugLogDisplay .= "</p></td></tr>";
}

//Check if a request to show server aj external log was made
if(isset($_REQUEST["showserverajexternallog"])) {
	
	$debugUrlAdditions .= "&showserverajexternallog=yes";
	
	$debugLogDisplay .= "	<tr><td colspan=\"2\"><h5>Server AJ External Log<hr></h5></td></tr>
							<tr><td colspan=\"2\"><p>";

	//Open error log
	if(($errorLogFile = fopen("/opt/isymphony/server/logs/asterisk-java-external.txt", 'r')) !== false) {
		while (!feof($errorLogFile)) {
        	$line = fgets($errorLogFile, 4096);
        	$debugLogDisplay .= htmlspecialchars($line) . "<br>";
    	}
    	fclose($errorLogFile);
	}
	
	$debugLogDisplay .= "</p></td></tr>";
}

//Check if a request to show server aj internal log was made
if(isset($_REQUEST["showserverajinternallog"])) {
	
	$debugUrlAdditions .= "&showserverajinternallog=yes";
	
	$debugLogDisplay .= "	<tr><td colspan=\"2\"><h5>Server AJ Internal Log<hr></h5></td></tr>
							<tr><td colspan=\"2\"><p>";

	//Open error log
	if(($errorLogFile = fopen("/opt/isymphony/server/logs/asterisk-java-internal.txt", 'r')) !== false) {
		while (!feof($errorLogFile)) {
        	$line = fgets($errorLogFile, 4096);
        	$debugLogDisplay .= htmlspecialchars($line) . "<br>";
    	}
    	fclose($errorLogFile);
	}
	
	$debugLogDisplay .= "</p></td></tr>";
}

//Connect to iSymphony server to query information
if($serverRunning) {
				
	//Get server version
	if(($versionDisplay = $isymphony->getiSymphonyServerVersion()) === false) {
		$versionDisplay = "";
	}

	//Get licensed to name
	if(($licensedToDisplay = $isymphony->getISymphonyLicenseName($locationInformation["isymphony_location"], $locationInformation["isymphony_tenant"])) === false) {
		$licensedToDisplay = "";
	}
	
	//Get license days
	if(($licenseTrialDaysDisplay = $isymphony->getISymphonyLicenseTrialDays($locationInformation["isymphony_location"], $locationInformation["isymphony_tenant"])) === false) {
		$licenseTrialDaysDisplay = "";
	}
	
	//Get license clients
	if(($licenseClientsDisplay = $isymphony->getISymphonyLicenseClients($locationInformation["isymphony_location"], $locationInformation["isymphony_tenant"])) === false) {
		$licenseClientsDisplay = "";
	}
	
	//Get license queues
	if(($licenseQueuesDisplay = $isymphony->getISymphonyLicenseQueues($locationInformation["isymphony_location"], $locationInformation["isymphony_tenant"])) === false) {
		$licenseQueuesDisplay = "";
	}

	//Build action buttons
	$reloadServerButton = "	<form name=\"isymphony_reload_form\" action=\"config.php?type=setup&display=isymphony$debugUrlAdditions\" method=\"post\">
								<input type=\"Submit\" name=\"isymphony_reload\" value=\"Reload\">
							</form>";
	$activateLicenseButton = "	<form name=\"isymphony_activate_license_form\" action=\"config.php?type=setup&display=isymphony$debugUrlAdditions\" method=\"post\">
									<input type=\"text\" name=\"isymphony_license_key\">
									<input type=\"Submit\" name=\"isymphony_activate_license\" value=\"Activate\">
								</form>";

	//Close iSymphony connection
	$isymphony->iSymphonyDisconnect();

} else {
	$versionDisplay = "";
	$licensedToDisplay = "";
	$licenseTrialDaysDisplay = "";
	$licenseClientsDisplay = "";
	$licenseQueuesDisplay = "";
	$reloadServerButton = "";
	$activateLicenseButton = "";
}

//Display license activation error if any
if($licenseActivationError != "") {
	$licenseActivationError = "	<span style=\"color: #FF0000\">
									An error occurred while activating your license:<br>
									$licenseActivationError
								</span>";
}

$display = "<script language=\"javascript\">
			<!--
				
				function checkForm() {
					
					var settingsForm = document.getElementById('isymphony_settings_form');				

					if(settingsForm.elements['isymphony_admin_user_name'].value.length == 0) {
						alert('Admin User Name cannot be blank.');
						return false;
					}

					if(settingsForm.elements['isymphony_admin_password'].value.length == 0) {
						alert('Admin Password cannot be blank.');
						return false;
					}

					if(settingsForm.elements['isymphony_originate_timeout'].value.length == 0) {
						alert('Originate Timeout cannot be blank.');
						return false;
					}

					if(settingsForm.elements['isymphony_originate_timeout'].value != parseInt(settingsForm.elements['isymphony_originate_timeout'].value)) {
						alert('Originate Timeout must be numeric.');
						return false;
					}

					if(settingsForm.elements['isymphony_jabber_port'].value.length == 0) {
						alert('Jabber Port cannot be blank.');
						return false;
					}

					if(settingsForm.elements['isymphony_jabber_port'].value != parseInt(settingsForm.elements['isymphony_jabber_port'].value)) {
						alert('Jabber Port must be numeric.');
						return false;
					}

					return true;
				}
			//-->
			</script>
			
			<div class=\"content\">
				$licenseActivationError
				<table>
					<tr><td colspan=\"2\"><h2 id=\"title\">iSymphony</h2></td></tr>
					<tr><td colspan=\"2\"><h5>Server<hr></h5></td></tr>
					<tr>
						<td><a href=\"#\" class=\"info\">Status:<span>Displays if the iSymphony server is running.</span></a></td>
						<td>$serverRunningDisplay</td>						
					</tr>
					<tr>
						<td><a href=\"#\" class=\"info\">Version:<span>Displays the version of the iSymphony server.</span></a></td>
						<td>$versionDisplay</td>						
					</tr>
					<tr>
						<td><a href=\"#\" class=\"info\">Reload Server:<span>Reloads the iSymphony server.</span></a>&nbsp&nbsp</td>
						<td>$reloadServerButton</td>						
					</tr>

					<tr><td colspan=\"2\"><h5>License<hr></h5></td></tr>
					<tr>
						<td><a href=\"#\" class=\"info\">Licensed To:<span>Displays the name of the person or company this server is licensed to.</span></a></td>
						<td>$licensedToDisplay</td>						
					</tr>
					<tr>
						<td><a href=\"#\" class=\"info\">Trial Days:<span>Displays the number of remaining license trial days.</span></a></td>
						<td>$licenseTrialDaysDisplay</td>						
					</tr>
					<tr>
						<td><a href=\"#\" class=\"info\">Clients:<span>Displays the number of licensed clients.</span></a></td>
						<td>$licenseClientsDisplay</td>						
					</tr>
					<tr>
						<td><a href=\"#\" class=\"info\">Queues:<span>Displays the number of licensed queues.</span></a></td>
						<td>$licenseQueuesDisplay</td>						
					</tr>
					<tr>
						<td><a href=\"#\" class=\"info\">Activate:<span>Activates a license with a given serial key.</span></a></td>
						<td>$activateLicenseButton</td>						
					</tr>

					<form name=\"isymphony_settings_form\" id=\"isymphony_settings_form\" action=\"config.php?type=setup&display=isymphony$debugUrlAdditions\" method=\"post\" onsubmit=\"return checkForm();\">
					<tr><td colspan=\"2\"><h5>Server Settings<hr></h5></td></tr>
					<tr>
						<td><a href=\"#\" class=\"info\">Admin User Name:<span>User name used to login to the administration section in the panel.</span></a></td>
						<td><input size=\"20\" type=\"text\" name=\"isymphony_admin_user_name\" value=\"" . htmlspecialchars($locationInformation['admin_user_name']) . "\"></td>
					</tr>
					<tr>
						<td><a href=\"#\" class=\"info\">Admin Password:<span>Password used to login to the administration section in the panel.</span></a></td>
						<td><input size=\"20\" type=\"text\" name=\"isymphony_admin_password\" value=\"" . htmlspecialchars($locationInformation['admin_password']) . "\"></td>
					</tr>
					<tr>
						<td><a href=\"#\" class=\"info\">Originate Timeout:<span>Number of milliseconds that the originating extension will be rung when placing a call via the panel before timing out.</span></a></td>
						<td><input size=\"20\" type=\"text\" name=\"isymphony_originate_timeout\" value=\"" . htmlspecialchars($locationInformation['originate_timeout']) . "\"></td>
					</tr>
					<tr>
						<td><a href=\"#\" class=\"info\">Auto Reload:<span>Tells the server to automatically reload the location when the dial plan is reloaded.</span></a></td>
						<td><input type=\"checkbox\" name=\"isymphony_auto_reload\"" . (($locationInformation['auto_reload'] == "1") ? " checked" : "") . "></td>
					</tr>
					<tr>
						<td><a href=\"#\" class=\"info\">Enable Page Status:<span>If checked allows you to see paging status on extensions in the panel.</span></a></td>
						<td><input type=\"checkbox\" name=\"isymphony_page_status_enabled\"" . (($locationInformation['page_status_enabled'] == "1") ? " checked" : "") . "></td>
					</tr>
					<tr>
                    		<td><a href=\"#\" class=\"info\">iSymphony Server Host:<span>IP Address or FQDN of iSymphony Server</span></a></td>
                    		<td><input size=\"20\" type=\"text\" name=\"isymphony_admin_host\" value=\"" . htmlspecialchars($locationInformation['isymphony_host']) . "\"></td>
                    </tr>
                    <tr>
                    		<td><a href=\"#\" class=\"info\">Location:<span>iSymphony Server Location:</span></a></td>
                    		<td><input size=\"20\" type=\"text\" name=\"isymphony_admin_location\" value=\"" . htmlspecialchars($locationInformation['isymphony_location']) . "\"></td>
                    </tr>
					<tr>
                    		<td><a href=\"#\" class=\"info\">Tenant:<span>iSymphonyServer Tenant:</span></a></td>
                    		<td><input size=\"20\" type=\"text\" name=\"isymphony_admin_tenant\" value=\"" . htmlspecialchars($locationInformation['isymphony_tenant']) . "\"></td>
                    </tr>
                    <tr>
                    		<td><a href=\"#\" class=\"info\">Client Port:<span>Sets the port on the server that serves the client files.</span></a></td>
                    		<td><input size=\"20\" type=\"text\" name=\"isymphony_client_port\" value=\"" . htmlspecialchars($locationInformation['isymphony_client_port']) . "\"></td>
                    </tr>
					<tr>
                            <td><a href=\"#\" class=\"info\">Asterisk Server Host:<span>IP Address or FQDN of your Asterisk Server</span></a></td>
                    		<td><input size=\"20\" type=\"text\" name=\"isymphony_asterisk_host\" value=\"" . htmlspecialchars($locationInformation['asterisk_host']) . "\"></td>
                     </tr>
					<tr><td colspan=\"2\"><h5>Global Jabber Settings<hr></h5></td></tr>
					<tr>
						<td><a href=\"#\" class=\"info\">Host:<span>Jabber host to be used unless otherwise overridden by the extension settings.</span></a></td>
						<td><input size=\"20\" type=\"text\" name=\"isymphony_jabber_host\" value=\"" . htmlspecialchars($locationInformation['jabber_host']) . "\"></td>
					</tr>
					<tr>
						<td><a href=\"#\" class=\"info\">Domain:<span>Jabber domain to be used unless otherwise overridden by the extension settings.</span></a></td>
						<td><input size=\"20\" type=\"text\" name=\"isymphony_jabber_domain\" value=\"" . htmlspecialchars($locationInformation['jabber_domain']) . "\"></td>
					</tr>
					<tr>
						<td><a href=\"#\" class=\"info\">Resource:<span>Jabber resource to be used unless otherwise overridden by the extension settings.</span></a></td>
						<td><input size=\"20\" type=\"text\" name=\"isymphony_jabber_resource\" value=\"" . htmlspecialchars($locationInformation['jabber_resource']) . "\"></td>
					</tr>
					<tr>
						<td><a href=\"#\" class=\"info\">Port:<span>Jabber port to be used unless otherwise overridden by the extension settings.</span></a></td>
						<td><input size=\"20\" type=\"text\" name=\"isymphony_jabber_port\" value=\"" . htmlspecialchars($locationInformation['jabber_port']) . "\"></td>
					</tr>
					<tr>
						<td><a href=\"#\" class=\"info\">Log Messages:<span>Tells the server to log all chat conversations.</span></a></td>
						<td><input type=\"checkbox\" name=\"isymphony_log_jabber_messages\"" . (($locationInformation['log_jabber_messages'] == "1") ? " checked" : "") . "></td>
					</tr>
					<tr>
						<td colspan=\"2\"><input type=\"Submit\" name=\"isymphony_update_location_settings\" value=\"Submit Changes\"></td>
					</tr>
					</form>
					<tr><td colspan=\"2\"><h5>Module Debug<hr></h5></td></tr>
					<tr><td><a href=\"config.php?type=setup&display=isymphony&showmoduledebuglog=yes$debugUrlAdditions\">View Debug Log</a></td></tr>
					<tr><td><a href=\"config.php?type=setup&display=isymphony&showmoduleerrorlog=yes$debugUrlAdditions\">View Error Log</a></td></tr>
					<tr><td><a href=\"config.php?type=setup&display=isymphony&showmoduledb=yes$debugUrlAdditions\">View Database</a></td></tr>
					<tr><td colspan=\"2\"><h5>Server Debug<hr></h5></td></tr>
					<tr><td><a href=\"config.php?type=setup&display=isymphony&showservererrorlog=yes$debugUrlAdditions\">View Error Log</a></td></tr>
					<tr><td><a href=\"config.php?type=setup&display=isymphony&showservercorelog=yes$debugUrlAdditions\">View Core Log</a></td></tr>
					<tr><td><a href=\"config.php?type=setup&display=isymphony&showservercommunicationlog=yes$debugUrlAdditions\">View Communication Log</a></td></tr>
					<tr><td><a href=\"config.php?type=setup&display=isymphony&showserverajexternallog=yes$debugUrlAdditions\">View AMI External Log</a></td></tr>
					<tr><td><a href=\"config.php?type=setup&display=isymphony&showserverajinternallog=yes$debugUrlAdditions\">View AMI Internal Log</a></td></tr>		
					$debugLogDisplay
				</table>
				$databaseDisplay
				$brandInfo
			<br>
			<br>
			For more information please visit <a href=\"http://www.getisymphony.com\">http://www.getisymphony.com</a>
			</div>";

echo $display;
echo "<br>";
