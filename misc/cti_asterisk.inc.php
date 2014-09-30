<?php

// $Id: cti_asterisk.inc.php,v 1.7 2006/10/02 11:30:45 nina Exp $

// check whether the lib has been included - authentication!
if (!defined('lib_included')) die('Please use index.php!');

define('PATH_PRE','../');

include_once(PATH_PRE."lib/gpcs_vars.inc.php");
$returnvalue = Call_Contact($phonenumber);
if ($returnvalue == 0) echo "<script>window.close();</script>\n";


function Call_Contact($destinationnumber) {

$call_out_via = "CAPI/@807440"; // the channel for dialing out.
// the extension associated with the current user or IP. This must be read from a database. For this purpose it should be ok.
$users = array( "10.1.3.2" => "SIP/25", "10.1.3.5" => "CAPI/31" );

$user_extension = $users[$_SERVER["REMOTE_ADDR"]];

if ($user_extension == "") die("Can't find your extension");



//
// Clicking on the title of the phone field opens a small window, which, based
// on the PHPR_CALLTYPE constant in config.inc.php, executes a call to this file.
//
// After this, the function CallContact is used to dial the phone number.
//
// The function CallContact is installation and PBX specific. This file demonstrates
// the use with the Asterisk PBX (http://www.asterisk.org/), an open source PBX/IVR/AutoAttendent
// system.
//
// There are some issues with Asterisk with regards to the management interface which is
// used in this example. Therefor, at the moment, this should be considered more a proof of
// concept that a solution usable for high call-volumes.
//
// In any event, feedback (cti@theinternet.de) is greatly appreciated.
//
//

$voipchannel = "";

// We do some rudimentary string conversion.
//
if (substr($destinationnumber,0,3) == "+49") {
   $destinationnumber = "0" . substr($destinationnumber,3);
   $regular_phone = 1;
}

if (substr($destinationnumber,0,1) == "+") {
   $destinationnumber = "00" . substr($destinationnumber,1);
   $regular_phone = 1;
}

//
// The destinationnumber is purely nummeric. We assume it is a regular
// phone.
//
if ($destinationnumber * 1 <> 0) {
   $regular_phone = 1;
}

if ($regular_phone == 1) {
print "The destination is a regular phone";
   $destinationnumber = str_replace("-","",$destinationnumber);
   $destinationnumber = $call_out_via . $destinationnumber;
   $channel = "CAPI";
} else {
   $channel = "IAX";
}
//

print "Extension: " . $user_extension ."<br />";

print "is being transfered to  " . $destinationnumber . ".<br />";

// Damit der Benutzer auch sieht das etwas passiert.
flush();

//
return CallAsterisk($user_extension, $destinationnumber, $channel);
}


function CallAsterisk($src_phone, $dest_phone, $channel) {
//
// $src-phone Defines the extension of the user (format like extensions.conf)
//
// $dest-phone The phone number to be called. Again, like the DIAL statement
//             with in extentions.conf, like IAX/guest@theinternet.dyndns.org/25
//

$asterisk_server_host = "10.1.3.111";
$asterisk_server_port = 5038;
$asterisk_manager_user = "admin";
$asterisk_manager_secret = "mysecret";

// Next couple of lines are "borrowed" from sendmail.inc.php

// First we connect to the asterisk server. We don't care about the
// banner.

$fp = fsockopen($asterisk_server_host, $asterisk_server_port);
if (!$fp)die("<b>Error:</b> No connect to ".$asterisk_server_host);
$banner = fgets($fp, 2048);

// After the connection, we need to authenticate.
fputs($fp,"Action: Login\r\n");
fputs($fp,"Username: $asterisk_manager_user\r\n");
fputs($fp,"Secret: $asterisk_manager_secret\r\n");
fputs($fp,"\r\n");

// Again, ignoring the results. This should be checked more
// carefully.

$result = fgets($fp, 1024);
if (strpos($result, "Error")) {
   print "An error occured. Connection aborted.<br />";
   flush();
   break;
} else {
   print str_replace("\n","<br />",$result);
   flush();
}

// Now we are sending the information about the call to asterisk.
//
fputs($fp,"Action: Originate\r\n");
fputs($fp,"Channel: $src_phone\r\n");
// We don't use context and exten because of limitations of the type of call we could do.
// fputs($fp,"Context: default\r\n");
// fputs($fp,"Exten: $dest_phone\r\n");
// fputs($fp,"Priority: 1\r\n");
// fputs($fp,"Callerid: <$src_phone>\r\n");
fputs($fp,"Application: Dial\r\n");
fputs($fp,"Data: $dest_phone\r\n");
fputs($fp,"\r\n");
$returnvalue = 0;
while ($result = fgets($fp, 1024)) {
   print str_replace("\n","<br />",$result);
   if (strpos($result, "Error")) {
      $returnvalue = 1;
      print "An error occured after sending data to *. The connection is aborting. $returnvalue<br />";
      flush();
      break;
   }
   if (strpos($result, "Originate successfully queued")) {
      print "The connection will be closed (Originate queued)<br />";
      flush();
      break;
   }
   if (strpos($result, "Event: Hangup")) {
      print "The connection will be closed. (Event: Hangup)<br />";
      flush();
      break;
   }

   if (strpos($result, "Uniqueid:")) {
      $unique_id = substr($result,strpos($resul,"Uniqueid")+10,15);
      print "The unique identifier is " . $unique_id;
      flush();
   } else {
      flush();
   }
}
// We say goodbye to asterisk.
fputs($fp, "Action: Logoff\r\n");
fputs($fp,"\r\n");
// And close the connection.
fclose($fp);
return $returnvalue;
}

?>

</div>
</body>
</html>
