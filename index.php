<?php

//Bootstrap FreePBX
$bootstrap_settings['freepbx_auth'] = false;
if(!@include_once(getenv('FREEPBX_CONF') ? getenv('FREEPBX_CONF') : '/etc/freepbx.conf')) {
	include_once('/etc/asterisk/freepbx.conf');
}

//Query the location info
if(function_exists('isymphony_location_get')) {
	$location = isymphony_location_get();
} else {
	echo "Module not installed";
	die;
}

//Check host
if(!isset($location['isymphony_host']) || $location['isymphony_host'] == "") {
	echo "No host found";
	die;
}

//Check port
if(!isset($location['isymphony_client_port']) || $location['isymphony_client_port'] == "") {
	echo "No port found";
    die;
}

//Check location
if(!isset($location['isymphony_location']) || $location['isymphony_location'] == "") {
	echo "No location found";
    die;
}

//Check tenant
if(!isset($location['isymphony_tenant']) || $location['isymphony_tenant'] == "") {
	echo "No tenant found";
    die;
}

//If host is localhost rewrite it with the server host
if($location['isymphony_host'] == "localhost" || $location['isymphony_host'] == "127.0.0.1") {
        $values = explode(':', $_SERVER['HTTP_HOST']);
        $location['isymphony_host'] = $values[0];
}

//Create forward url
$forwardUrl = "http://" . $location['isymphony_host'] . ":" . $location['isymphony_client_port'] . "/launch.jnlp?location=" . $location['isymphony_location'] . "&tenant=" . $location['isymphony_tenant'];

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd"> 
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en"> 
<head> 
<title>Operator Panel for Asterisk - iSymphony</title> 
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" /> 
<meta name="description" content="Asterisk Based Operator Panel" /> 
<meta name="keywords" content="freepbx, Asterisk operator panel, operator panel, asterisk, call center, trixbox, piaf, pbx in a flash, flash operator panel, fop, hud, hudlite, fonality" /> 
<meta name="robots" content="INDEX,FOLLOW" /> 
<link rel="icon" href="http://www.getisymphony.com/skin/frontend/default/modern/favicon.ico" type="image/x-icon" /> 
<link rel="shortcut icon" href="http://www.getisymphony.com/skin/frontend/default/modern/favicon.ico" type="image/x-icon" /> 
<!--[if lt IE 7]>
<script type="text/javascript">
//<![CDATA[
    var BLANK_URL = 'http://www.getisymphony.com/js/blank.html';
    var BLANK_IMG = 'http://www.getisymphony.com/js/spacer.gif';
//]]>
</script>
<![endif]--> 
<link rel="stylesheet" type="text/css" href="http://www.getisymphony.com/skin/frontend/default/modern/css/isstyle.css" media="all" /> 
<link rel="stylesheet" type="text/css" href="http://www.getisymphony.com/skin/frontend/default/modern/css/custom-theme/jquery-ui-1.8.2.custom.css" media="all" /> 
<link rel="stylesheet" type="text/css" href="http://www.getisymphony.com/skin/frontend/default/modern/css/widgets.css" media="all" /> 
<link rel="stylesheet" type="text/css" href="http://www.getisymphony.com/skin/frontend/default/modern/css/styles.css" media="all" /> 
<link rel="stylesheet" type="text/css" href="http://www.getisymphony.com/skin/frontend/base/default/mailchimp/css/MailChimp.css" media="all" /> 
<link rel="stylesheet" type="text/css" href="http://www.getisymphony.com/skin/frontend/default/modern/css/print.css" media="print" /> 
<script type="text/javascript" src="http://www.getisymphony.com/js/prototype/prototype.js"></script> 
<script type="text/javascript" src="http://www.getisymphony.com/js/lib/ccard.js"></script> 
<script type="text/javascript" src="http://www.getisymphony.com/js/prototype/validation.js"></script> 
<script type="text/javascript" src="http://www.getisymphony.com/js/scriptaculous/builder.js"></script> 
<script type="text/javascript" src="http://www.getisymphony.com/js/scriptaculous/effects.js"></script> 
<script type="text/javascript" src="http://www.getisymphony.com/js/scriptaculous/dragdrop.js"></script> 
<script type="text/javascript" src="http://www.getisymphony.com/js/scriptaculous/controls.js"></script> 
<script type="text/javascript" src="http://www.getisymphony.com/js/scriptaculous/slider.js"></script> 
<script type="text/javascript" src="http://www.getisymphony.com/js/varien/js.js"></script> 
<script type="text/javascript" src="http://www.getisymphony.com/js/varien/form.js"></script> 
<script type="text/javascript" src="http://www.getisymphony.com/js/varien/menu.js"></script> 
<script type="text/javascript" src="http://www.getisymphony.com/js/mage/translate.js"></script> 
<script type="text/javascript" src="http://www.getisymphony.com/js/mage/cookies.js"></script> 
<script type="text/javascript" src="http://www.getisymphony.com/js/jquery-1.4.2.min.js"></script> 
<script type="text/javascript" src="http://www.getisymphony.com/js/jquery-ui-1.8.2.custom.min.js"></script> 
<script type="text/javascript" src="http://www.getisymphony.com/skin/frontend/base/default/mailchimp/js/MailChimp.js"></script> 
<!--[if lt IE 8]>
<link rel="stylesheet" type="text/css" href="http://www.getisymphony.com/skin/frontend/default/modern/css/styles-ie.css" media="all" />
<![endif]--> 
<!--[if lt IE 7]>
<script type="text/javascript" src="http://www.getisymphony.com/js/lib/ds-sleight.js"></script>
<script type="text/javascript" src="http://www.getisymphony.com/skin/frontend/base/default/js/ie6.js"></script>
<![endif]--> 
 
<script type="text/javascript"> 
//<![CDATA[
optionalZipCountries = [];
//]]>
</script> 
<script type="text/javascript">var Translator = new Translate({"Credit card number doesn't match credit card type":"Credit card number does not match credit card type","Please use only letters (a-z or A-Z), numbers (0-9) or underscore(_) in this field, first character should be a letter.":"Please use only letters (a-z or A-Z), numbers (0-9) or underscores (_) in this field, first character must be a letter."});</script></head> 
<body class=" cms-index-index cms-home"> 
 
<!-- BEGIN GOOGLE ANALYTICS CODE --> 
<script type="text/javascript"> 
//<![CDATA[
    (function() {
        var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
        ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
        (document.getElementsByTagName('head')[0] || document.getElementsByTagName('body')[0]).appendChild(ga);
    })();
 
    var _gaq = _gaq || [];
    _gaq.push(["_setAccount", "UA-952625-6"]);
    _gaq.push(["_trackPageview", "/"]);
//]]>
</script> 
<!-- END GOOGLE ANALYTICS CODE --> 
        <div class="wrapper"> 
        <noscript> 
        <div class="noscript">
            <div class="noscript-inner">
                <p><strong>JavaScript seem to be disabled in your browser.</strong></p>
                <p>You must have JavaScript enabled in your browser to utilize the functionality of this website.</p>
            </div>
        </div>
    </noscript> 
    <div class="page"> 
        <div class="header-container"> 
    <div class="header"> 
                <h1 class="logo"><strong>iSymphony</strong><a href="http://www.getisymphony.com/" title="iSymphony" class="logo"><img src="http://www.getisymphony.com/skin/frontend/base/default/images/logo-is.png" alt="iSymphony" /></a></h1> 
                <div class="header-nav-container"> 
    <div class="header-nav"> 
        <h4 class="no-display">Category Navigation:</h4> 
        <ul id="nav"> 
 
        <!-- WORKING ACTIVE STATE HOME BUTTON HACK --> 
        <li class="home"><a href="http://www.getisymphony.com/features/">Features</a></li> 
 
	<!-- WORKING ACTIVE STATE HOME BUTTON HACK --> 
 
                    <li class="level0 nav-1 parent" onmouseover="toggleMenu(this,1)" onmouseout="toggleMenu(this,0)"> 
<a href="http://www.getisymphony.com/buyisymphony.html"> 
<span>Buy</span> 
</a> 
<ul class="level0"> 
<li class="level1 nav-1-1 first"> 
<a href="http://www.getisymphony.com/buyisymphony/isymphony-services.html"> 
<span>iSymphony Services</span> 
</a> 
</li><li class="level1 nav-1-2 last"> 
<a href="http://www.getisymphony.com/buyisymphony/isymphony-software.html"> 
<span>iSymphony Software</span> 
</a> 
</li> 
</ul> 
</li>        
<li class="home level0 nav-2 parent" onmouseover="toggleMenu(this,1)" onmouseout="toggleMenu(this,0)"> 
 
<a href="http://www.getisymphony.com/support">Support</a> 
<ul class="level0"> 
<li class="level1 nav-2-1 first"> 
<a href="http://www.getisymphony.com/support-options/"> 
<span>Support Options</span> 
</a> 
</li> 
<li class="level1 nav-2-2"> 
<a href="http://www.getisymphony.com/support"> 
<span>Submit a Ticket</span> 
</a> 
</li> 
<li class="level1 nav-2-3 last"> 
<a href="http://www.getisymphony.com/forum"> 
<span>Community Forum</span> 
</a> 
</li> 
</ul> 
</li> 
	<li class="home"><a href="http://www.getisymphony.com/contact/">Contact Us</a></li> 
        </ul> 
    </div> 
    </div> 
        
    </div> 
    <div class="quick-access"> 
    </div> 
    <div class="top-bar"><form id="search_mini_form" action="http://www.getisymphony.com/catalogsearch/result/" method="get"> 
    <div class="form-search"> 
    </div> 
</form></div>    </div> 
        <div class="main-container col1-layout"> 
            <div class="main"> 
                                <div class="col-main"> 
                                        <div class="std"><div id="mainbanner">

<div id="banner-bg">

<div id="banner-left">

<div id="banner-header">iSymphony Call Manager</div>

<div id="banner-text">Easy to use and affordable call management for FreePBX.</div>

<a class="button-download" href="<?php echo $forwardUrl; ?>"> <span><strong>Start iSymphony (Client)</strong><em>Windows, Mac OS X and Linux</em></span> </a> <a class="button-tour" href="features/"> <span><strong>Take the tour!</strong><em>Learn more about iSymphony</em></span> </a> <a class="button-upgrade" href="isymphony.html"><span><strong>Upgrade to Conductor Edition</strong></span></a>

<div class="comparetext"><a href="compare-features/">Compare Versions</a></div>

</div>

<div id="banner-right">&nbsp;</div>

<div id="whatis">

<div class="whatistext">

<h2>What is iSymphony?</h2>

iSymphony, an easy-to-use, Java-based client/server software application for managing phone calls via the Open Source FreePBX Platform.</div>

</div>

<div class="ataglance">

<h2>At a glance</h2>

There are many new features and enhancements in the latest version. Check out the <a href="features/">feature list</a> for full details. 			         

<ul>

<li>Multi-platform </li>

<li>Voicemail at a glance</li>

<li>Queue and conference room management</li>

<li>Prescense management</li>

</ul>

<a href="features/">Learn more about the features ...&gt;&gt;</a></div>

</div>

</div></div>                </div> 
            </div> 
        </div> 
        <div class="footer-container"> 
    <div class="footer"> 
                <div class="f-right"> 
            <!-- footer callout --> 
                    </div> 
        <div class="f-left"> 
                                                <address>&copy; 2010 <a href="http://www.i9technologies.com">i9 Technologies</a>. All Rights Reserved.</address> 
        </div> 
    </div> 
</div> 
 
            </div> 
</div> 
</body> 
</html> 
