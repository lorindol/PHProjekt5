<?php
/**
 * @package    mail
 * @subpackage main
 * @author     Albrecht Guenther, $Author: polidor $
 * @licence    GPL, see www.gnu.org/copyleft/gpl.html
 * @copyright  2000-2006 Mayflower GmbH www.mayflower.de
 * @version    $Id: mail_down.php,v 1.15 2008-01-14 02:45:05 polidor Exp $
 */

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
$result = db_query("SELECT von
                      FROM ".DB_PREFIX."mail_client, ".DB_PREFIX."mail_attach
                     WHERE ".DB_PREFIX."mail_attach.ID = ".(int)$arr[2]." and
                           ".DB_PREFIX."mail_client.is_deleted is NULL and
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
