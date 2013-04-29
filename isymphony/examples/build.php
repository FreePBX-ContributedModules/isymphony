<?php
/*
 *Name         : build.php
 *Author       : Michael Yara
 *Created      : June 15, 2008
 *Last Updated : Feb 14, 2012
 *History      : 0.4
 *Purpose      : Contains an example of building configuration objects and adding them to the configuration tree. 
 *Copyright    : 2008 HEHE Enterprises, LLC
 */

error_reporting(E_ALL);

include("../isymphony.php");

$isymphony = new iSymphony("localhost");

if(!$isymphony->iSymphonyConnect()) {
	echo iSymphony::$ISERROR;
	die;
}

echo "Building default location<br>";
$object = new ISymphonyLocation;
$object->name = "default";
$object->admin_password = "secret";
$object->asterisk_host = "localhost";
$object->asterisk_port = "5038";
$object->asterisk_login = "manager";
$object->asterisk_password = "pass";
$object->originate_timeout = "30000";
$object->jabber_host = "";
$object->jabber_domain = "";
$object->jabber_resource = "";
$object->jabber_port = "5222";
$object->mask_jabber_user_name_with_profile = "false";
$object->log_jabber_messages = "false";
$object->device_user_mode = "false";
$object->reload_on_dial_plan_reload = "true";
$object->force_client_update = "true";
$object->voice_mail_directory = "/var/spool/asterisk/voicemail";
if(!$object->add()) {
	echo iSymphony::$ISERROR;
	die;
}

echo "Building default tenant<br>";
$object = new ISymphonyTenant;
$object->name = "default";
$object->admin_password = "secret";
$object->originating_context = "default";
$object->redirecting_context = "default";
$object->agent_login_context = "default";
$object->music_on_hold_class = "default";
$object->outside_line_number = "";
$object->record_file_name = "test";
$object->record_file_extension = "wav";
$object->mix_mode = "true";
$object->page_status_enabled = "true";
$object->page_context = "page";
if(!$object->add("default")) {
	echo iSymphony::$ISERROR;
	die;
}

echo "Building extension 100<br>";
$object = new ISymphonyExtension;
$object->extension_val = "100";
$object->name = "Extension 1";
$object->cell_phone = "5555555555";
$object->email = "ext1@i9technologies.com";
$object->peer = "SIP/100";
$object->alt_origination_method = "";
$object->voice_mail = "100";
$object->voice_mail_context = "default";
$object->agent = "100";
$object->originating_context = "";
$object->redirecting_context = "";
$object->agent_login_context = "";
$object->music_on_hold_class = "";
$object->auto_answer = "false";
if(!$object->add("default","default")) {
	echo iSymphony::$ISERROR;
	die;
}

echo "Building extension 200<br>";
$object = new ISymphonyExtension;
$object->extension_val = "200";
$object->name = "Extension 2";
$object->cell_phone = "5555555555";
$object->email = "ext2@i9technologies.com";
$object->peer = "SIP/200";
$object->alt_origination_method = "";
$object->voice_mail = "200";
$object->voice_mail_context = "default";
$object->agent = "200";
$object->originating_context = "";
$object->redirecting_context = "";
$object->agent_login_context = "";
$object->music_on_hold_class = "";
$object->auto_answer = "false";
if(!$object->add("default","default")) {
	echo iSymphony::$ISERROR;
	die;
}

echo "Building extension 300<br>";
$object = new ISymphonyExtension;
$object->extension_val = "300";
$object->name = "Ext 3";
$object->cell_phone = "5555555555";
$object->email = "ext3@i9technologies.com";
$object->peer = "SIP/300";
$object->alt_origination_method = "";
$object->voice_mail = "300";
$object->voice_mail_context = "default";
$object->agent = "300";
$object->originating_context = "";
$object->redirecting_context = "";
$object->agent_login_context = "";
$object->music_on_hold_class = "";
$object->auto_answer = "false";
if(!$object->add("default","default")) {
	echo iSymphony::$ISERROR;
	die;
}

echo "Building profile user1<br>";
$object = new ISymphonyProfile;
$object->name = "user1";
$object->password = "test";
$object->jabber_host = "";
$object->jabber_domain = "";
$object->jabber_resource = "";
$object->jabber_port = "";	
$object->jabber_user_name = "";
$object->jabber_password = "";
$object->can_view_everyone_directory = "true";
if(!$object->add("default","default")) {
	echo iSymphony::$ISERROR;
	die;
}

if(!$isymphony->addISymphonyProfileManagedExtension("default","default","user1","100")) {
	echo iSymphony::$ISERROR;
	die;
}

echo "Building profile user2<br>";
$object = new ISymphonyProfile;
$object->name = "user2";
$object->password = "test";
$object->jabber_host = "";
$object->jabber_domain = "";
$object->jabber_resource = "";
$object->jabber_port = "";	
$object->jabber_user_name = "";
$object->jabber_password = "";
$object->can_view_everyone_directory = "true";
if(!$object->add("default","default")) {
	echo iSymphony::$ISERROR;
	die;
}

if(!$isymphony->addISymphonyProfileManagedExtension("default","default","user2","200")) {
	echo iSymphony::$ISERROR;
	die;
}

echo "Building profile user3<br>";
$object = new ISymphonyProfile;
$object->name = "user3";
$object->password = "test";
$object->jabber_host = "";
$object->jabber_domain = "";
$object->jabber_resource = "";
$object->jabber_port = "";	
$object->jabber_user_name = "";
$object->jabber_password = "";
$object->can_view_everyone_directory = "true";
if(!$object->add("default","default")) {
	echo iSymphony::$ISERROR;
	die;
}

if(!$isymphony->addISymphonyProfileManagedExtension("default","default","user3","300")) {
	echo iSymphony::$ISERROR;
	die;
}

echo "Building queue queue1<br>";
$object = new ISymphonyQueue;
$object->name = "Queue1";
$object->queue_val = "queue1";
$object->extension_val = "400";
$object->context = "default";
if(!$object->add("default","default")) {
	echo iSymphony::$ISERROR;
	die;
}

echo "Building queue queue2<br>";
$object = new ISymphonyQueue;
$object->name = "Queue2";
$object->queue_val = "queue2";
$object->extension_val = "500";
$object->context = "default";
if(!$object->add("default","default")) {
	echo iSymphony::$ISERROR;
	die;
}

echo "Building meetme Room(Predefined)<br>";
$object = new ISymphonyConferenceRoom;
$object->name = "Room(Predefined)";
$object->predefined = "true";
$object->room_number = "1234";
$object->extension_val = "600";
$object->context = "default";
$object->announce_user_count = "false";
$object->music_on_hold_for_single_user = "false";
$object->exit_room_via_pound = "false";
$object->present_menu_via_star = "false";
$object->announce_user_join_leave = "false";
$object->disable_join_leave_notification = "false";
$object->record = "false";
if(!$object->add("default","default")) {
	echo iSymphony::$ISERROR;
	die;
}

echo "Building meetme Room(Custom)<br>";
$object = new ISymphonyConferenceRoom;
$object->name = "Room(Custom)";
$object->predefined = "false";
$object->room_number = "22222";
$object->extension_val = "222";
$object->context = "222";
$object->announce_user_count = "false";
$object->music_on_hold_for_single_user = "false";
$object->exit_room_via_pound = "false";
$object->present_menu_via_star = "false";
$object->announce_user_join_leave = "false";
$object->disable_join_leave_notification = "true";
$object->record = "false";
if(!$object->add("default","default")) {
	echo iSymphony::$ISERROR;
	die;
}

echo "Building status Out to lunch<br>";
$object = new ISymphonyStatus;
$object->name = "Out to lunch";
$object->type = "Out";
if(!$object->add("default","default")) {
	echo iSymphony::$ISERROR;
	die;
}

echo "Building status In a meeting<br>";
$object = new ISymphonyStatus;
$object->name = "In a meeting";
$object->type = "Unavailable";
if(!$object->add("default","default")) {
	echo iSymphony::$ISERROR;
	die;
}

echo "Activating license<br>";
$isymphony->activateISymphonyLicense("default", "default" ,"MySerialKey");

if(!$isymphony->iSymphonyDisconnect()) {
	echo iSymphony::$ISERROR;
	die;
}

?>