<?php 
/**
* pfolders - WebDAV for PHProjekt
*
* This addon for PHProjekt extends the the filemanager so
* one can use it via the WebDAV protcol (aka "web folder")
*
* @author Johannes Schlueter <schlueter@phpbar.de>
* @version $Id: pfolders.php,v 1.7 2008-02-25 06:58:33 gustavo Exp $
*/

/**
 * WebDAV for PHProjekt version
 */
define('WD_VERSION', '0.8');

/**
 * Path to PHProjekt's files
 */
define('PHPROJEKT_PATH', './');

/**
 * Path to my files
 */
define('WEBDAV_PATH', './pfolders/');

/**
 * All files created through WebDAV outside the fileview go to folder with ID=0
 */
define('DEFAULT_FOLDER_ID', 0);

/**
 * All files created through WebDAV at the rootlevel get access rights 'private'
 */
define('DEFAULT_ACC', 'private');

/**
 * All files created through WebDAV at the rootlevel get access write rights '' 
 *
 */
define('DEFAULT_ACC_WRITE', '');

/**
 * List all the user_ID that can see the task for all the groups rather than only one group
 */
define('USERS_THAT_CAN_SEE_ALL_GROUPS', '1,2');

// change the include path so a bundled WebDAV class can be found
ini_set('include_path', '.'.PATH_SEPARATOR.     // First look at the current folder
                        PHPROJEKT_PATH.PATH_SEPARATOR. // Old install documentation told to put it at the PHProjekt folder
                        PHPROJEKT_PATH.'pear/'.PATH_SEPARATOR. // then look at the PHProjekt-pear-folder
                        ini_get('include_path')); // Maybe one isn't using the bundled version
                        
define('PATH_PRE', PHPROJEKT_PATH);
define('avoid_auth', true);
$module = 'pfolder';
include_once(PATH_PRE.'lib/lib.inc.php');

if (!include_once(PHPROJEKT_PATH.'config.inc.php')) {
    define('PHPROJEKT_NOT_FOUND', 1);
}
error_reporting(E_ALL);
ini_set('display_errors', 0);
// PHProjekt 5 compatiblity...
if (defined('PHPR_VERSION')) {
    $version = PHPR_VERSION;

    $db_type   = PHPR_DB_TYPE;
    $db_host   = PHPR_DB_HOST;
    $db_user   = PHPR_DB_USER;
    $db_pass   = PHPR_DB_PASS;
    $db_name   = PHPR_DB_NAME;
    $db_prefix = PHPR_DB_PREFIX;

    $dateien  = PHPR_FILE_PATH;
    $adressen = PHPR_CONTACTS;
    $projekte = PHPR_PROJECTS;

    $ldap = PHPR_LDAP;

    $dat_crypt = 1;

    $login_kurz = PHPR_LOGIN_SHORT;
    $pw_crypt   = PHPR_PW_CRYPT;
}

require_once(WEBDAV_PATH.'util.php');
require_once(WEBDAV_PATH.'class_webdav.php');

require_once(WEBDAV_PATH.'class_phprojekt.php');

$views = array();
require_once(WEBDAV_PATH.'class_fileview.php');
require_once(WEBDAV_PATH.'class_projectview.php');
require_once(WEBDAV_PATH.'class_contactview.php');
require_once(WEBDAV_PATH.'class_caldav.php');
require_once(WEBDAV_PATH.'class_acl.php');

// Check some critical errors
if (version_compare(PHP_VERSION, '4.3.0', '<')) {
    show_help('Sorry, your PHP version is too old to run this PHProjekt-Addon! Please update to PHP 4.3 or higher.');
} elseif (defined('PHPROJEKT_NOT_FOUND')) {
    show_help('Your PHProjekt installation could not be found at '.PHPROJEKT_PATH.'!');
} elseif (defined('WEBDAV_NOT_FOUND')) {
    show_help('PEAR::HTTP_WebDAV_Server could no be found!<br>Search path: '.ini_get('include_path'));
} elseif (PHP_SAPI == 'cgi' ) {
    show_help('Sorry, this PHProjekt Addon don\'t work with PHP configured as '.PHP_SAPI.' module.');
} elseif (empty($dateien)) {
    show_help('Filemanager not available! Please setup PHProjekt to support the filemanager.');
} elseif ($dat_crypt != 1) {
    show_help('Your PHProjekt installation doesn\'t use scrambeled file names. This is required by this AddOn.');
} elseif ($ldap) {
    show_help('Your PHProjekt installation is configured to use LDAP for authorization but LDAP is not yet supported.');
} elseif (!is_dir($dateien)) {
    show_help('Absolute path to the upload directory not found, please check your PHProjekt configuration.');
}

// $db_prefix is new since PHProjekt 4.1
if (!isset($db_prefix)) {
    $db_prefix = '';
}

define('WD_TAB_FILES',    $db_prefix.'dateien');
define('WD_TAB_USER',     $db_prefix.'users');
define('WD_TAB_PROJECTS', $db_prefix.'projekte');
define('WD_TAB_CONTACTS', $db_prefix.'contacts');
define('WD_TAB_GROUPS',   $db_prefix.'gruppen');
define('WD_TAB_GROUUSER', $db_prefix.'grup_user');
define('WD_TAB_EVENTS',   $db_prefix.'termine');
define('WD_TAB_TODOS',    $db_prefix.'todo');
define('WD_TAB_HELPDESK', $db_prefix.'rts');

$webdav = &new webdav();
$webdav->ServeRequest();


/**
* Try to guess the encoding and onvert it to ISO-8859-1
* This is a ugly hack...
* @var string either utf-8 or iso-8859-1 encoded
* @return string
*/
function guessed_encoding_to_iso_8859_1($str) {
	$iso = 0;
	$utf8 = 0;
	
	$strlen = strlen($str);
	
	$utfchars = array(chr(0xC3), chr(0xC2));
	$isochars = array(chr(0xE4), chr(0xF6), chr(0xFC), chr(0xD6), chr(0xDC));
	
	for ($i = 0; $i < $strlen; $i++) {
		if (in_array($str{$i}, $isochars)) {
			$iso++;
		} elseif (in_array($str{$i}, $utfchars)) {
			$utf8++;
		}
	}
	
	if ($iso < $utf8) {
		return utf8_decode($str);
	}
	
	return $str;
}


/**
* Try to guess the encoding and onvert it to ISO-8859-1
* This is a ugly hack...
* @var string either utf-8 or iso-8859-1 encoded
* @return string
*/
function guessed_encoding_to_utf8($str) {
	$iso = 0;
	$utf8 = 0;
	
	$strlen = strlen($str);
	
	$utfchars = array(chr(0xC3), chr(0xC2));
	$isochars = array(chr(0xE4), chr(0xF6), chr(0xFC), chr(0x41), chr(0xD6), chr(0xDC));
	
	for ($i = 0; $i < $strlen; $i++) {
		if (in_array($str{$i}, $isochars)) {
			$iso++;
		} elseif (in_array($str{$i}, $utfchars)) {
			$utf8++;
		}
	}
	
	if ($iso > $utf8) {
		return utf8_encode($str);
	}
	
	return $str;
}