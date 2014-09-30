<?php
error_reporting(0);
include_once('setup_functions.php');

// Check language
$langua = check_language();
$_SESSION['langua'] = $langua;
$GLOBALS['langua'] = $langua;

// another scurity check
if (!preg_match("#^[./]*$#",$langua)) die('You are not allowed to do this');
define('LANG',substr($langua,0,3));

// If language ok, then we can start the translation
include_once("./lang/".LANG.".inc.php");

function __($textid) 
{ 
  return isset($GLOBALS['_lang'][$textid]) ? $GLOBALS['_lang'][$textid] : $textid;
}

if (isset($_REQUEST['db_name']) && isset($_REQUEST['db_type'])) {
  //*** db test ***
  $db_type = xss($_REQUEST['db_type']);
  $db_host = xss($_REQUEST['db_host']);
  $db_user = xss($_REQUEST['db_user']);
  $db_pass = xss($_REQUEST['db_pass']);
  $db_name = xss($_REQUEST['db_name']);

  // test mysql access
  if ($db_type == "mysql") {
    $link = mysql_connect($db_host, $db_user, $db_pass) or popup_message(__('Sorry, I cannot connect to the database!.'));
    
    if (!mysql_select_db($db_name, $link))
    {
      if (mysql_query("create database $db_name"))
      {
        popup_message(__('Sorry, I cannot create the database!.'));
      }
    }
    
  }
  // test sqlite access
  else if ($db_type == "sqlite") {
      $dbfilename = dirname(dirname(__FILE__))."/$db_name.db";
      if (! $link = sqlite_open($dbfilename , 0666, $sqliteerror) ) {
          $db_test = "failed";
      }
  }
  // test interbase access
  else if ($db_type == "interbase") {
      $db_host2 =  $db_host.":".$db_name;
      $link = ibase_connect($db_host2, $db_user, $db_pass) or popup_message(__('Sorry, I cannot connect to the database!.'));
  }
  // test ms_sql
  else if ($db_type == "ms_sql") {
      $link = mssql_connect($db_host, $db_user, $db_pass) or popup_message(__('Sorry, I cannot connect to the database!.'));
  }
  // test oracle
  else if ($db_type == "oracle") {
      $link = OCILogon($db_user, $db_pass, $db_name) or popup_message(__('Sorry, I cannot connect to the database!.'));
      $datestmt = OCIParse($link, "alter session set NLS_DATE_FORMAT='YYYY-MM-DD HH:MI:SS'");
      OCIExecute($datestmt);
  }
  // test informix
  else if ($db_type == "informix") {
      if ($db_host == "") { $db = $db_name; }
      else { $db = $db_name."@".$db_host; }
      $link = ifx_connect($db, $db_user, $db_pass) or popup_message(__('Sorry, I cannot connect to the database!.'));
  }
  // test postgres
  else if ( $db_type == "postgresql" ) {
      echo "<br />Trying to connect to ".$db_name;
      $link = $link = pg_connect((($db_host == "") ? "" : "host= ".$db_host." ").(($db_pass == "") ? "" : "password=".$db_pass." ")."dbname=".$db_name." user=".$db_user);
      echo "<br />$link";
      if (!$link) {
          echo "<br />Trying to connect to template1";
          $link = pg_connect((($db_host == "") ? "" : "host = ".$db_host." ").(($db_pass == "") ? "" : "password=".$db_pass." ")."dbname=template1 user=".$db_user) or popup_message("Can't connect to db, nor to template1");
          echo "<br />Trying to create database ".$db_name;
          $result = pg_query($link, "CREATE DATABASE ".$db_name) or popup_message("Can't create database ".$db_name);
          echo "<br />Database create, closing connection to template1";
          $link = pg_close($link) or popup_message("Can't close database connection");
          echo "<br />Opening new connection to db ".$db_name;
          $link = pg_connect((($db_host == "" or $db_host == "localhost") ? "" : "host = ".$db_host." ").(($db_pass == "") ? "" : "password=".$db_pass." ")."dbname=".$db_name." user=".$db_user) or popup_message(__("Can't connect to newly created db."));
      }
  }
  //** end test db access
  
  popup_message(__('Seems that You have a valid database connection!'), false);
}
else
{
  popup_message(__("invalid data"));
  die();
}

function popup_message($message, $error = true) {

echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>
<title>'.__('Database Connection Test').'</title>
<style type="text/css" media="screen">@import "../layout/default/default_css.php";</style>
<!--[if gte IE 5]><style type="text/css" media="screen">@import "../layout/default/default_css_ie.php";</style><![endif]-->
</head>
<body style="margin:0px;background-color:#E0E0E0;">
<div id="global-main">
<table cellpadding="0" cellspacing="0" border="0">
    <tr><td class="setting_options">';
  if ($error) {
    echo "<font color='red'><b>".$message."</b></font>";
  }
  else
  {
    echo $message;
  }
  echo "</td></tr>
<tr><td><input type='button' name='close' value= '".__('Close window')."' onclick='window.close()' class='button'></td></tr>
</table>

</div>
</body>
</html>";
  die();  
}

?>
