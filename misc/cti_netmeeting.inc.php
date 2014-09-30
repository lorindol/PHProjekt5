<?php
//
// Start of PHProjekt specific code.
// 

// check whether the lib has been included - authentication!
if (!defined('lib_included')) die('Please use index.php!');

define('PATH_PRE','../');
include_once(PATH_PRE."lib/gpcs_vars.inc.php");
$returnvalue = Call_Contact($phonenumber);


//
// End of PHProjekt specific code.
//

//
// Start of phone system specific code.
//

//
// This example shows how to call using the Netmeeting ActiveX. 
// Remember, this works only within Internet Explorer and you
// must have Netmeeting 3.x installed.
// 
function Call_Contact($destinationnumber) {
	Header("Cache-Control: no-cache");
	Header("Pragma: no-cache");
	Header("Expires: Sat, Jan 01 2000 01:01:01 GMT");
   echo "<html>";
   echo "<head>";
   print "</head><body><div id=\"global-main\">Die Nummer $destinationnumber wird angerufen";
   print "<object ID=NetMeeting CLASSID=\"CLSID:3E9BAF2D-7A79-11d2-9334-0000F875AE17\">";
   print "<PARAM NAME = \"MODE\" VALUE = \"Telephone\">";
   print "</object><script>";
   print "NetMeeting.CallTo('$destinationnumber+type=phone');";
   print "</script>";
}
?>
</div>
</body></html>
