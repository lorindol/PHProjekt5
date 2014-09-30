<?php

//ini_set('error_reporting',1024);

// env_test.php - PHProjekt Version 5.2
// copyright  ©  2000-2005 Albrecht Guenther  ag@phprojekt.com
// www.phprojekt.com
// Author: Albrecht Guenther, $Author: albrecht $
// $Id: env_test.php,v 1.20.2.3 2007/04/20 14:45:46 albrecht Exp $

/*
Welcome to the test routine of PHProjekt!
But already now I can tell you that it won't work -
because you see this text here! :-)
This means that your php parser is not working -
otherwise this text would be recognized as a comment and
not shown onthe screen :-|
The requirements for a setup of PHProjekt
are a webserver with a php parser and a sql database.
A typical combination would be a LAMP or WAMP system.
Installation tutorials can be found e.g. here:
http://www.dynamic-webpages.de/07.installation.php
'til later!
*/

// this script will produce it's own error messages, in this case we
// don't need any additional warnings from the parser :-)
//error_reporting(0);

// run env_test.php only if this is the first installation!
if (file_exists('./config.inc.php') || file_exists('../../config.inc.php')) die('You are not allowed to do this!<br />(there are a configuration file)');

// the variables for this script will partly be treansferred via sessions
// include the gpc_vars library to get all post and get variables, regardsless the value of register_globals
include_once("./lib/gpcs_vars.inc.php");

// to restart the script all session data have to be deleted
if (isset($_GET['free']) and $_GET["free"] == 1) {
    session_destroy();
}

$red1 = "<div style='color:red;'>";
$red2 = '</div>';

echo "<html><head><title>PHProjekt SYSTEM tests</title></head><body bgcolor=#E2E2E2><div id=\"global-main\"><br />";
echo "<h2>PHProjekt environment test</h2>";
echo "Thank you for taking the time to check whether your<br />environment meets to the needs of a PHProjekt installation<br /><br />";

// PHP Version  - exclude PHP3
echo "<br />**********************";
echo "<h4>PHP Parser test</h4>";
if (!isset($_REQUEST['parser_test']) and !isset($_SESSION['parser_test'])) {
    echo "<form><input type='hidden' name='parser_test' value='1'>\n";
    echo "<input type='hidden' name='".session_name()."' value='".session_id()."'>\n";
    echo "First we have a look on the used PHP version<br /><br />\n";
    echo "<input type='submit' value='Parser test'></form>";
}
else if ($_REQUEST['parser_test'] == '1') {
    // PHP Version  - exclude PHP3
    if (substr(phpversion(),0,1) == '3') {
        ("<b>sorry, at least PHP 4.3 required!</b><br /><br /> Please download the current version at <a href='http://www.php.net'>www.php.net</a>. (exit)\n");
        $_REQUEST['parser_test'] = 'failed';
    }
    else if (substr(phpversion(),0,1) == '4' and ereg('0|1|2',substr(phpversion(),2,1))) {
        echo "The used PHP version older than 4.2 - we strongly recommend you to update to a newer version.<br /><br />";
        $_REQUEST['parser_test'] = 'update required';
    }
    else {
        echo 'The version of the used PHP parser seems to be valid - you need php version 4.3 or higher!';
        $parser_test = 'o.k.';
    }
    $_SESSION['parser_test'] =& $parser_test;
}
else echo "<i>PHP version test done, result: ".$_SESSION['parser_test']."</i>";
// end parser test



// begin environment test
echo "<br />**********************";
echo "<h4>PHP environment test</h4>";
// offer env test
if (!isset($_REQUEST['env_test'])  and !isset($_SESSION['env_test'])) {
    echo "<form><input type='hidden' name='env_test' value='1'>\n";
    echo "<input type='hidden' name='".session_name()."' value='".session_id()."'>\n";
    echo "Now we check whether how the configuration of PHP is set.<br /><br />\n";
    echo "<input type='submit' value='env test'></form>";
}
// env_test submitted, start test
else if ($_REQUEST['env_test'] == '1') {
    echo '<ul>';
    // magic quotes runtime test
    if (ini_get("magic_quotes_runtime")) {
        echo "<li>".$red1."Please change the value of 'magic_quotes_runtime' in the php.ini to 'off'\n".$red2;
        // add this string to the env-test variable
        $_REQUEST['env_test'] = "<li>change magic_quotes_runtime to 'off' in the php.ini";
    }
    else echo "<li>variable 'magic_quotes_runtime' is set to off - o.k.!";

    // use_trans_sid test
    if (in_array(strtolower(ini_get("session.use_trans_sid")), array('on', '1'))) {
        echo "<li>".$red1."The variable session.use_trans_sid is set to 1 -<br />
                    this will cause problems if users run PHProjekt without cookies!<br />
                    We recommend to set session.use_trans_sid to 0\n".$red2;
        // add this string to the env-test variable
        $_REQUEST['env_test'] = "<li>Recommendation: set session.use_trans_sid to 0";
    }
    else echo "<li>variable 'session.use_trans_sid' is set to off - o.k.!";

    // open_basedir test
    if (ini_get("open_basedir")) {
        echo "<li>".$red1."Please delete the value of 'open_basedir' in the php.ini\n".$red2;
        $_REQUEST['env_test'] .= "<li>delete the value of 'open_basedir' in the php.ini\n";
    }
    // open basedir correct?
    else echo "<li>variable 'open_basedir' is empty - o.k.!";

    // safe mode test
    if ((ini_get("safe_mode") == "on") or (ini_get("safe_mode") == 1))  {
        echo "<li>".$red1."your php runs in the safe mode configuration.<br />
        In this case the PHProjekt directory and all subdirectories have to be owned by the webserver.".$red2;
        $_REQUEST['env_test'] .= "safe mode is on! -> the webserver must own all subdirectories of PHProjekt";
    }
    else echo "<li>variable 'safe_mode' is 'off' - o.k.!";

    echo '</ul>';

    // if none of the above test has written an error message into the variable -> "o.k."! :-)
    if ($_REQUEST['env_test'] == 1) $env_test = "o.k.";
    // write variable into session
    $_SESSION['env_test'] =& $env_test;
}
// env_test done, show result
else echo "<i>PHP environment test done, result: ".$_SESSION['env_test']."</i>";

// session test
echo "<br />**********************";
echo "<h4>Session Test</h4>";

	$a = 1;
	$_SESSION['session_ok'] =& $a; 

if (!isset($_REQUEST['session_test']) and !isset($_SESSION['session_test'])) {
    echo "<form><input type=hidden name=session_test value=1>\n";
    echo "<input type=hidden name='".session_name()."' value='".session_id()."'>\n";
    echo "PHProjekt uses sessions to store information <br />Now we check whether the server has a working session management.<br /><br />\n";
    echo "<input type=submit value='session test'></form>";
}
else if ($_REQUEST['session_test'] == 1) {
    // check whether session are enabled at all!!
    if (!extension_loaded('session')) {
        echo "<br /><h2>Panic - the php parser has been compiled without session support! </h2><br />";
        $_SESSION['session_test'] = "failed";
    }

    if ($_SESSION['session_ok'] == 1) {
        echo "<i>Session management works!</i>";
        $_SESSION['session_test'] = "o.k.";
    }
    else {
        echo "oops - can't find my session!";
        $_SESSION['session_test'] = "failed";
    }
}
else echo "<i>Session management test done, result: ".$_SESSION['session_test']."</i>";



// sql test
echo "<br />**********************";
echo "<h4>Database Access</h4>";

if (!isset($_REQUEST['db_test']) and !isset($_SESSION['db_test'])) {
    echo "Please enter your db access parameters, the script will try to connect to the database:\n";
    echo "<form>\n";
    echo "<input type='hidden' name='".session_name()."' value='".session_id()."'>\n";
    echo "<input type='hidden' name='db_test' value='1'>\n";
    // select database
    echo "<table><tr><td>db_type:</td><td> <select name=db_type>\n";
    echo "<option value='mysql'>mysql\n";
    echo "<option value='oracle'>oracle\n";
    echo "<option value='informix'>informix\n";
    echo "<option value='postgresql'>postgres\n";
    echo "<option value='interbase'>interbase\n";
    echo "<option value='ms_sql'>MS SQL\n";
    //echo "<option value='odbc'>ODBC\n";
    echo "<option value='db2'>db2\n";        
    echo "</select></td></tr>\n";
    echo "<tr><td>Host:</td><td><input type='text' name='db_host'></td></tr>\n";
    echo "<tr><td>Username:</td><td><input type='text' name='db_user'></td></tr>\n";
    echo "<tr><td>Password:</td><td><input type='Password' name='db_pass'></td></tr>\n";
    echo "<tr><td>Database name:</td><td><input type='text' name='db_name'></td></tr>\n";
    echo "<tr><td>&nbsp;</td><td><input type='submit' value='db test'></td></tr>\n";
    echo "</table></form>\n";
}
else if($_REQUEST['db_test'] == 1) {

    // well ;-)
    $db_host  = preg_replace("/[^0-9a-z-_.@:$]/i", "",$_REQUEST['db_host']);
    $db_user  = preg_replace("/[^0-9a-z-_.@]/i", "",$_REQUEST['db_user']);
    $db_pass  = preg_replace("/[^0-9a-z-_.@]/i", "",$_REQUEST['db_pass']);
    $db_host2 = preg_replace("/[^0-9a-z-_.@:$]/i", "",$_REQUEST['db_host2']);
    $db_name  = preg_replace("/[^0-9a-z-_.@]/i", "",$_REQUEST['db_name']);
    $db_type  = preg_replace("/[^0-9a-z-_]/i", "",$_REQUEST['db_type']);

    // *** db test ***

    // test mysql access
    if ($db_type == "mysql") {
        $link = mysql_connect($db_host,$db_user,$db_pass) or $_REQUEST['db_test'] = "failed";
    }

    // test interbase access
    else if ($db_type == "interbase") {
        $db_host2 =  "$db_host:$db_name";
        $link = ibase_connect($db_host2, $db_user, $db_pass) or $_REQUEST['db_test'] = "failed";
    }
    // test ms_sql
    else if ($db_type == "ms_sql") {
        $link = mssql_connect($db_host, $db_user, $db_pass) or $_REQUEST['db_test'] = "failed";
    }
    // test oracle
    else if ($db_type == "oracle") {
        $link = OCILogon($db_user, $db_pass, $db_name) or $_REQUEST['db_test'] = "failed";
        $datestmt = OCIParse($link, "alter session set NLS_DATE_FORMAT='YYYY-MM-DD HH:MI:SS'");
        OCIExecute($datestmt);
    }
    // test informix
    else if ($db_type == "informix") {
        if ($db_host == "") $db = $db_name;
        else                $db = $db_name."@".$db_host;
        $link = ifx_connect($db, $db_user, $db_pass) or $_REQUEST['db_test'] = "failed";
    }
    // test postgres
    else if ( $db_type == "postgresql" ) {
        echo "<br />Trying to connect to $db_name";
        $link = pg_connect((($db_host == "") ? "" : "host = $db_host ").(($db_pass == "") ? "" : "password=$db_pass ")."dbname=$db_name user=$db_user");
        echo "<br />$link";
        if (!$link) {
            echo "<br />Trying to connect to template1";
            $link = pg_connect((($db_host == "") ? "" : "host = $db_host ").(($db_pass == "") ? "" : "password=$db_pass ")."dbname=template1 user=$db_user") or $_REQUEST['db_test'] = "failed";
            echo "<br />Trying to create database $db_name";
            $result = pg_exec($link, "CREATE DATABASE $db_name") or $_REQUEST['db_test'] = "failed";
            echo "<br />Database create, closing connection to template1";
            $link = pg_close($link) or die("Can't close database connection");
            echo "<br />Opening new connection to db $db_name";
            $link = pg_connect((($db_host == "") ? "" : "host = $db_host ").(($db_pass == "") ? "" : "password=$db_pass ")."dbname=$db_name user=$db_user") or $_REQUEST['db_test'] = "failed";
        }
    }
    
    // test DB2
    else if ($db_type =="db2") {
  	    
  	    echo "<br /> Trying to connect to ". $db_name."<br />";
	    $conn_string = "DRIVER={IBM DB2 ODBC DRIVER};DATABASE=$db_name;HOSTNAME=$db_host;PORT=50000;PROTOCOL=TCPIP;UID=$db_user;PWD=$db_pass;";
 	    $link = db2_connect($conn_string, '', '') or $_REQUEST['db_test'] = "failed";
	    if ($link) { 
	        echo " Connection to DB2 - Database ".$db_name."sucessful!";
            db2_close($link);
        }
        else {
             echo "Connection to DB2 - Can't connect to ".$db_name."<br />";
        }
    }
    // *** end test db access ***

    // output error message
    if (!$link or (isset($conn) and !$conn)) {
        echo "oops - no connection to the database!<br />Reasons could be:<ul>
        <li>Wrong access parameter, <li>The database is not running, <li>The database hasn't been setup correctly,
        <li>The PHP parser has not been installed with this db support
        </ul>Please fix it and try it again ...<br /><br />";
        $_REQUEST['db_test'] = "failed";
    }
    else {
        echo "Seems that connecting the database has been successful :-)";
        $_REQUEST['db_test'] = "o.k.";
    }
    $_SESSION['db_test'] =& $db_test;
}
else echo "<i>Database access test done, result: ".$_SESSION['db_test']."</i>";
// end db test



// **********
// mail check
echo "<br />**********************";
echo "<h4>Mail Test</h4>\n";

if (!isset($_REQUEST['email']) and !isset($_SESSION['email'])) {
    echo "Here you can test whether you are able to send and receive mails with PHProjekt.<br />
          Enter your email adress here, the script will send you an email:<br />\n";
    echo "<form><input type='text' name='email'>\n";
    echo "<input type='hidden' name='".session_name()."' value='".session_id()."'>\n";
    echo "<input type='submit' value='send mail'></form>\n";
}
else if ($_REQUEST['email'] <> 2) {
    mail(urldecode($_REQUEST['email']),"PHProjekt Mail Test","Congratulations!\n Now you know that you can
    use PHProjekt to send mails\n","From:$user_email\nReply-To:$user_email\nSender:$user_email\nReturn-Path:$user_email");
    echo "Please check your mailbox whether you got a mail from PHProjekt. If not, you should examine the mail settings in your php.ini";

    // imap extensions test
    if (function_exists('imap_open')) {
        echo "<br />Checking your mail configuration ... the IMAP library is active, therefore you can use the full mail client";
        $email_receive_test = "o.k.";
    }
    else {
        echo "<br />oh, the imap library from PHP is missing, at the moment you cannot install the full email client :-(
              <br /> please tell your sysadmin or provider to install the imap library of php (not to be mixed with the imap server).";
        $email_receive_test = "failed";
    }
    $_SESSION['email_receive_test'] =& $email_receive_test;
}
else echo "<i>Email test done, result: receive email $email_receive_test</i>";



// *********
// file test
echo "<br />**********************";
echo "<h4>File writing Test</h4>";

if (!isset($_REQUEST['file_test'])  and !isset($_SESSION['file_test'])) {
    echo "<form>\n";
    echo "<input type='hidden' name='".session_name()."' value='".session_id()."'>\n";
    echo "Now the script proofs whether it is able to write <br />a test file in the PHProjekt root diretory.<br /><br />\n";
    echo "<input type='hidden' name='file_test' value='1'>\n";
    echo "<input type='submit' value='file_test'></form><br />\n";
}
else if ($_REQUEST['file_test'] == 1) {
    $fp = fopen("test_phprojekt.txt", 'w');

    if (!$fp or $fp == "FALSE") {
        echo "I couldn't write this file!<br />Please give the webserver read and write permission for this directory!\n";
        $_REQUEST['file_test'] = "failed";
    }
    else {
        $fw = fwrite($fp,"This file was created for testing reasons. You can delete it.");
        echo "test file successfully written! Now it will be deleted again ...<br />\n";
        $_REQUEST['file_test'] = "o.k.";
        fclose($fp);
        $delete = unlink("test_phprojekt.txt");
        if (!$delete) {
            echo ".. but it failed to erase this file!";
            $_REQUEST['file_test'] = "failed";
        }
    }

    // additional test - check whether file_uploads is set to "on"
    if (ini_get("file_uploads") <> "1") {
        echo "Please change the value of 'file_uploads' in the php.ini to 'on', otherwise you can't use the file or mail module!";
        if ($_REQUEST['file_test'] == "o.k.") $_REQUEST['file_test'] .= " but please change 'file_uploads' in the php.ini to 'on'.";
    }
    $_SESSION['file_test'] =& $file_test;
}
else echo "<i>file management test done, result: ".$_SESSION['file_test']."</i>";


echo "<br />**********************";
echo "<br /><br />If you want to run this test another time, please follow <a href='env_test.php?free=1'>this link here</a>\n";
echo "<br /><br /><br />";

// read and write permissions for root, attach, chat and upload directory
// blank screen problem! -> include of en.inc.php possible?

// next proof: file_uploads in the php.ini must be set to on ..

?>
</div>
</body>
</html>
