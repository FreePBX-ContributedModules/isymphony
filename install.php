<?php
/*
 *Name         : install.php
 *Author       : Michael Yara
 *Created      : August 15, 2008
 *Last Updated : June 23, 2012
 *History      : 0.10
 *Purpose      : Create, upgrade, and populate isymphony tables
 *Copyright    : 2008 HEHE Enterprises, LLC
 */

global $db, $amp_conf;

//Column descriptor class
class isymphony_column {

	var $name = "";
	var $type = "";
	var $defaultValue = "";
	var $freePBXKey = "";
	var $isUnique = false;
	var $isNotNull = false;
	
	function isymphony_column($nameVal, $typeVal, $defaultValueVal, $freePBXKeyVal, $isUniqueVal, $isNotNullVal) {
		$this->name = $nameVal;
		$this->type = $typeVal;
		$this->defaultValue = $defaultValueVal;
		$this->freePBXKey = $freePBXKeyVal;
		$this->isUnique = $isUniqueVal;
		$this->isNotNull = $isNotNullVal;
	}
}

//Table descriptor class
class isymphony_table {

	var $name = "";
	var $columns = array();
	
	function isymphony_table($nameVal, $columnsVal) {
		$this->name = $nameVal;
		$this->columns = $columnsVal;
	}
}

//Table builder class
class isymphony_table_builder {

	var $table = null;

	function isymphony_table_builder($tableVal) {
		$this->table = $tableVal;
	}
	
	function build($entries) {
		
		echo "Creating \"" . $this->table->name . "\" Table....<br>";
		
		if($this->createTableIfItDoesNotExist()) {
			echo "Populating(New) \"" . $this->table->name. "\"....<br>";
			$this->populateTableNew($entries);
		} else {
			echo "Upgrading \"" . $this->table->name. "\"....<br>";
			$addedColumns = $this->upgradeTableColumns();
			if(!empty($addedColumns)){
				echo "Populating(Upgrade) \"" . $this->table->name. "\"....<br>";
				$this->populateTableUpgrade($addedColumns);
			}
		}
		echo "Done<br>";
	}
	
	function createTableIfItDoesNotExist() {
		
		global $db;
		
		//Build query to create table if it does not exists
		$query = "CREATE TABLE " . $this->table->name . "(";
		
		foreach($this->table->columns as $column) {			
			$query .= $this->buildColumnEntry($column) . ",";
		}
		
		$query = substr_replace($query, "", -1);
		$query .= ")";
		
		$result = $db->query($query);
		if(DB::IsError($result)) {
			
			if($result->getCode() != DB_ERROR_ALREADY_EXISTS) {
				die_freepbx($result->getDebugInfo()); 
			}
			
			return false;
		}
		 
		return true;
	}
	
	function upgradeTableColumns() {
	
		global $db;
		
		$addedColumns = array();
		
		//Insert any missing columns
		foreach($this->table->columns as $column) {
			$query = "SELECT $column->name FROM " . $this->table->name;
			$check = $db->getRow($query, DB_FETCHMODE_ASSOC);
			
			if(DB::IsError($check)) {
		    	$query = "ALTER TABLE " . $this->table->name . " ADD " . $this->buildColumnEntry($column);
		    	$result = $db->query($query);
		    	
		    	if(DB::IsError($result)) { 
		    		die_freepbx($result->getDebugInfo()); 
		    	} else {
		    		array_push($addedColumns, $column);
		    	}
			}
		}
		
		return $addedColumns;
	}
	
	function populateTableNew($entries) {

		global $db;		
		
		//Populate a newly created table
		foreach($entries as $entry) {
			
			$queryKeys = "";
			$queryValues = "";
			$queryValueArray = array();
			
			foreach($this->table->columns as $column) {
				$queryKeys .= $column->name . ",";
				$queryValues .= "?,";
				$freePBXKey = $column->freePBXKey;
				
				if($freePBXKey != "") {
					array_push($queryValueArray,  $entry[$freePBXKey]);
				} else {
					array_push($queryValueArray,  $column->defaultValue);
				}
			}
			
			$queryKeys = substr_replace($queryKeys, "", -1);
			$queryValues = substr_replace($queryValues, "", -1);
		
			$query = $db->prepare("INSERT INTO " . $this->table->name . " (" . $queryKeys . ") VALUES (" . $queryValues . ")");
			$result = $db->execute($query, $queryValueArray);
			if(DB::IsError($result)) { 
				die_freepbx($result->getDebugInfo());
			}	
		}
	}
	
	function populateTableUpgrade($addedColumns) {
		
		global $db;		
		
		//Upgrade a table
		foreach($addedColumns as $column) {
			$query = $db->prepare("UPDATE " . $this->table->name . " SET " . $column->name . " = ?");
			$result = $db->execute($query, array($column->defaultValue));
			if(DB::IsError($result)) { 
				die_freepbx($result->getDebugInfo());
			}	
		}
	}
	
	function buildColumnEntry($column) {
	
		$columnEntry = $column->name;
		$modifierEntry = ($column->isUnique ? " UNIQUE" : "") . ($column->isNotNull ? " NOT NULL" : "");
		
		switch ($column->type) {
			case "primary":
		        $columnEntry .= " INT NOT NULL AUTO_INCREMENT PRIMARY KEY";
		        break;
	    	case "string":
		        $columnEntry .= " VARCHAR(100)" . $modifierEntry;
		        break;
		    case "integer":
		        $columnEntry .= " INTEGER(10)" . $modifierEntry;
		        break;
		    case "boolean":
		        $columnEntry .= "  INTEGER(1)" . $modifierEntry;
		        break;
		}	
	
		return $columnEntry;
	}
}

//Set operator panel web root
if(class_exists("freepbx_conf")) {
	echo "Setting operator panel web root....<br>";
	$set["FOPWEBROOT"] = "isymphony";
	$freepbx_conf =& freepbx_conf::create();
	$freepbx_conf->set_conf_values($set, true, true);
	echo "Done<br>";
}

//Create symlink that points to the module directory in order to run the client redirect script
echo "Creating client symlink....<br>";
if(file_exists($amp_conf['AMPWEBROOT'] . '/isymphony')) {
	unlink($amp_conf['AMPWEBROOT'] . '/isymphony');
}
symlink($amp_conf['AMPWEBROOT'] .'/admin/modules/isymphony/', $amp_conf['AMPWEBROOT'] . '/isymphony');

if(file_exists($amp_conf['AMPWEBROOT'] . '/admin/isymphony')) {
        unlink($amp_conf['AMPWEBROOT'] . '/admin/isymphony');
        }
        symlink($amp_conf['AMPWEBROOT'] .'/admin/modules/isymphony/', $amp_conf['AMPWEBROOT'] . '/admin/isymphony');
echo "Done<br>";
	
//Build location table
$columns = array(	new isymphony_column("admin_user_name", "string", "admin", "", false, false),
					new isymphony_column("admin_password", "string", "secret", "", false, false),
					new isymphony_column("originate_timeout", "integer", 30000, "", false, false),
					new isymphony_column("auto_reload", "boolean", 1, "", false, false),
					new isymphony_column("page_status_enabled", "boolean", 1, "", false, false),
					new isymphony_column("jabber_host", "string", "", "", false, false),
					new isymphony_column("jabber_domain", "string", "", "", false, false),
					new isymphony_column("jabber_resource", "string", "iSymphony", "", false, false),
					new isymphony_column("jabber_port", "integer", 5222, "", false, false),
					new isymphony_column("log_jabber_messages", "boolean", 0, "", false, false),
					new isymphony_column("isymphony_host", "string", "localhost", "", false, false),
					new isymphony_column("isymphony_location", "string", "default", "", false, false),
					new isymphony_column("isymphony_tenant", "string", "default", "", false, false),
					new isymphony_column("isymphony_client_port", "integer", 50003, "", false, false),
					new isymphony_column("asterisk_host", "string", "localhost", "", false, false));
					
$table = new isymphony_table("isymphony_location", $columns);
$builder = new isymphony_table_builder($table);
$builder->build(array(array("junk")));

//Build users table
$columns = array(	new isymphony_column("isymphony_user_id", "primary", "", "", true, true),
					new isymphony_column("user_id", "string", "", "user_id", true, true),
					new isymphony_column("add_extension", "boolean", 1, "", false, false),
					new isymphony_column("add_profile", "boolean", 1, "", false, false),
					new isymphony_column("password", "string", "secret", "", false, false),
					new isymphony_column("display_name", "string", "", "display_name", false, false),
					new isymphony_column("peer", "string", "", "peer", false, false),
					new isymphony_column("email", "string", "", "", false, false),
					new isymphony_column("cell_phone", "string", "", "", false, false),
					new isymphony_column("auto_answer", "boolean", 0, "", false, false),
					new isymphony_column("jabber_host", "string", "", "", false, false),
					new isymphony_column("jabber_domain", "string", "", "", false, false),
					new isymphony_column("jabber_resource", "string", "iSymphony", "", false, false),
					new isymphony_column("jabber_port", "integer", 5222, "", false, false),
					new isymphony_column("jabber_user_name", "string", "", "", false, false),
					new isymphony_column("jabber_password", "string", "", "", false, false));
$table = new isymphony_table("isymphony_users", $columns);
$builder = new isymphony_table_builder($table);

//Gather user info
$entries = array();
if((function_exists("core_users_list")) && (($freePBXUsers = core_users_list()) !== null)){
	foreach($freePBXUsers as $freePBXUser) {
		if(function_exists("core_devices_get")) {
			$freePBXDeviceInfo = core_devices_get($freePBXUser[0]);
			$userId = $freePBXUser[0];
			$displayName = $freePBXUser[1] == "" ? $freePBXUser[0] : $freePBXUser[1];
			$peer = ($freePBXDeviceInfo['dial'] != "") ? $freePBXDeviceInfo['dial'] : "SIP/$userId";
			array_push($entries, array("user_id" => $userId, "display_name" => $displayName, "peer" => $peer));
		}
	}
}

$builder->build($entries);					

//Build queues table
$columns = array(	new isymphony_column("isymphony_queue_id", "primary", "", "", true, true),
					new isymphony_column("queue_id", "string", "", "queue_id", true, true),
					new isymphony_column("add_queue", "boolean", 1, "", false, false),
					new isymphony_column("display_name", "string", "", "display_name", false, false));
$table = new isymphony_table("isymphony_queues", $columns);
$builder = new isymphony_table_builder($table);

//Gather queue info
$entries = array();
if((function_exists("queues_list")) && (($freePBXQueues = queues_list()) !== null)) {
	foreach($freePBXQueues as $freePBXQueue) {
		$queueId = $freePBXQueue[0];
		$displayName = $freePBXQueue[1] == "" ? $freePBXQueue[0] : $freePBXQueue[1];
		array_push($entries, array("queue_id" => $queueId, "display_name" => $displayName));
	}
}

$builder->build($entries);

//Build conference rooms table
$columns = array(	new isymphony_column("isymphony_conference_room_id", "primary", "", "", true, true),
					new isymphony_column("conference_room_id", "string", "", "conference_room_id", true, true),
					new isymphony_column("add_conference_room", "boolean", 1, "", false, false),
					new isymphony_column("display_name", "string", "", "display_name", false, false));
$table = new isymphony_table("isymphony_conference_rooms", $columns);
$builder = new isymphony_table_builder($table);

//Gather queue info
$entries = array();
if((function_exists("conferences_list")) && (($freePBXConferenceRooms = conferences_list()) !== null)) {
	foreach($freePBXConferenceRooms as $freePBXConferenceRoom) {
		$conferenceRoomId = $freePBXConferenceRoom[0];
		$conferenceRoomsDispalyName = $freePBXConferenceRoom[1] == "" ? $freePBXConferenceRoom[0] : $freePBXConferenceRoom[1];
		array_push($entries, array("conference_room_id" => $conferenceRoomId, "display_name" => $conferenceRoomsDispalyName));
	}
}

$builder->build($entries);

?>
