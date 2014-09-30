<?php
//
// Start of PHProjekt specific code.
// 

// check whether the lib has been included - authentication!
if (!defined('lib_included')) die('Please use index.php!');

define('PATH_PRE','../');
include_once(PATH_PRE."config.inc.php");
include_once(PATH_PRE."lib/gpcs_vars.inc.php");
$returnvalue = Call_Contact($phonenumber);


//
// End of PHProjekt specific code.
//

//
// Start of phone system specific code.
//

//
// This example shows how to call using a simple redirect
// which utilizes whatever application has been registered
// with the callto: protocol
// 
function Call_Contact($destinationnumber) {
   Header("Cache-Control: no-cache");
   Header("Pragma: no-cache");
   Header("Expires: Sat, Jan 01 2000 01:01:01 GMT");
   echo "<meta http-equiv='refresh' content='1; URL=callto:$destinationnumber'>";
	
}
?></div></body></html>
