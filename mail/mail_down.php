<?php

// mail_down.php - PHProjekt Version 5.2
// copyright  ©  2000-2005 Albrecht Guenther  ag@phprojekt.com
// www.phprojekt.com
// Author: Albrecht Guenther, $Author: nina $
// $Id: mail_down.php,v 1.12 2006/10/04 11:05:36 nina Exp $

// intialise the array so noone can introduce poisoned variables
$arr = array();

// include lib to fetch the sessiond data and to perform check
define('PATH_PRE','../');
include_once(PATH_PRE."lib/lib.inc.php");

// check role
if (check_role("mail") < 1) { die("You are not allowed to do this!"); }

// @session_cache_limiter('public');   // suppress error messages for PHP version < 4.0.2


$arr = explode("|",$file_ID[$rnd]);

// check permission
$result = db_query("select von from ".DB_PREFIX."mail_client, ".DB_PREFIX."mail_attach
                     where ".DB_PREFIX."mail_attach.ID = ".(int)$arr[2]." and
                           ".DB_PREFIX."mail_attach.parent = ".DB_PREFIX."mail_client.ID") or db_die();
$row = db_fetch_row($result);
if ($row[0] <> $user_ID) { die("You are not allowed to do this"); }

// Prevent escaping from the attach dir
if ((ereg('/', $arr[1])) or (ereg('^\.+$', $arr[1]))) { die("You are not allowed to do this!");  }

// assign the filename
$name = $arr[0];

// have a look whether this file exists
if (!file_exists(PATH_PRE.PHPR_ATT_PATH."/".$arr[1])) { die("panic! specified file not found ..."); }

// include content type definition
include_once(LIB_PATH."/get_contenttype.inc.php");

// stream the file
readfile(PATH_PRE.PHPR_ATT_PATH."/".$arr[1]);
?>
