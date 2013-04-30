<?php
/*
 *Name         : uninstall.php
 *Author       : Michael Yara
 *Created      : August 15, 2008
 *Last Updated : June 23, 2012
 *History      : 0.8
 *Purpose      : Remove iSymphony tables and manager include
 *Copyright    : 2008 HEHE Enterprises, LLC
 */

global $db, $amp_conf;

//Retore operator panel web root to default
if(class_exists("freepbx_conf")) {
	echo "Restoring operator panel web root to default....<br>";
	$set["FOPWEBROOT"] = $_SERVER['DOCUMENT_ROOT'] . "/panel";
	$freepbx_conf =& freepbx_conf::create();
	$freepbx_conf->set_conf_values($set, true, true);
}

//Remove client forward symlink
echo "Removing client symlink....<br>";
unlink($amp_conf['AMPWEBROOT'] . '/isymphony');
unlink($amp_conf['AMPWEBROOT'] . '/admin/isymphony');

//Drop location table
$query = "DROP TABLE IF EXISTS isymphony_location";
echo "Removing \"isymphony_location\" Table....<br>";
$results = $db->query($query);
if(DB::IsError($results)) {
	echo "ERROR: could not remove table.<br>";
}

//Drop users table
$query = "DROP TABLE IF EXISTS isymphony_users";
echo "Removing \"isymphony_users\" Table....<br>";
$results = $db->query($query);
if(DB::IsError($results)) {
	echo "ERROR: could not remove table.<br>";
}

//Drop queues table
$query = "DROP TABLE IF EXISTS isymphony_queues";
echo "Removing \"isymphony_queues\" Table....<br>";
$results = $db->query($query);
if(DB::IsError($results)) {
	echo "ERROR: could not remove table.<br>";
}

//Drop conference rooms table
$query = "DROP TABLE IF EXISTS isymphony_conference_rooms";
echo "Removing \"isymphony_conference_rooms\" Table....<br>";
$results = $db->query($query);
if(DB::IsError($results)) {
	echo "ERROR: could not remove table.<br>";
}

//Remove manager entry
$query = "DELETE FROM manager WHERE name = 'isymphony'";
echo "Removing manager entry....<br>";
$results = $db->query($query);
if(DB::IsError($results)) {
	echo "ERROR: could not remove manager entry.<br>";
}
?>
