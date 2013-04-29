This library allows you to modify and query your iSymphony configuration from php scripts.

-To get started simply place the isymphony-php-library folder in an area you php script can access.
-To utilize this library in your php script simply include the isymphony.php file.
-documentation.txt lists information about all global variables, constructor, functions and classes that are contained within the library.
-The examples directory includes example scripts on how to use some features of the library.
    -build.php: Shows you how to build and add some configuration elements to an empty configuration.
    -update.php: Shows you how to update some elements of the configuration.
    -status.php: Shows you how to modify and query extension users state elements(i.e. Availability, Note, Return Time).
    -query.php: Shows you how to query elements to grab their current configuration.
    -upgrade.php: Shows you how to perform an upgrade.
-NOTE: If you are using a server version below 2.1.15 this library must reside on the same system as the iSymphony server.

Change Log:
1.6 Removed uses of deprecated php methods.
	Modified the socket communication methods to scale automatically with the amount of objects that are handled.
1.5 Added support for DND flags and voice mail agent configuration.
1.4	If a class property is not set by the user the library will no longer attempt to write out that property to the configuration when a commit is issued. This modification
	will prevent the php library from overwriting the server defaults on a new item with blank values removing the need for users to specify all properties when adding a new item to the configuration.

	Added support for the following. 
	-Voicemail agent configuration.
	-Do not disturb button permission and display flag.
	-Disable ringing status flag.
	-Profile extension state color settings.
	-MeetMe room peer origination flag.
	-Agent login name.
	-ChanSpy barge flag.
1.3 BUG FIX: Property arrays are now cleared when a disconnect is issued so that subsequent connections will not re-issue property commits multiple times.
1.2 Added support for display queue and conference room permissions along with the extension agent interface and agent penalty properties.
1.1 Added support for the jabber logging setting.
1.0 Added support for all of the iSymphony 2.1 configuration options along with integration with the new update system.
0.3 Added support for device user mode flag in iSymphony 2.1
0.2 Added support for php 2.4
0.1 First Release