<?php

// lib.inc.php - PHProjekt Version 5.2
// copyright  ©  2000-2005 Albrecht Guenther  ag@phprojekt.com
// www.phprojekt.com

// Author: Albrecht Guenther, $Author: polidor $
// $Id: lib.inc.php,v 1.468.2.62 2007/08/30 04:08:58 polidor Exp $

/**
 * translation of strings
 * @author Alex Haslberger
 * @param string $textid textstring to identify the entry in language file
 * @return string
 */
if (!function_exists('__')) {
    function __($textid) {
        static $translated = array();
        if (!isset($GLOBALS['langua']) or empty($GLOBALS['langua'])) $GLOBALS['langua'] = 'en';
        if(!isset($translated[$GLOBALS['langua']])){
            if(!defined('LANG')){
                define('LANG',$GLOBALS['langua']);
            }
            include(dirname(__FILE__).'/../lang/'.LANG.'.inc.php');
            $translated[$GLOBALS['langua']] = $_lang;
        }
        return (isset($translated[$GLOBALS['langua']][$textid]) && !empty($translated[$GLOBALS['langua']][$textid])) ? $translated[$GLOBALS['langua']][$textid] : $textid;
    }
}


if (!defined('soap_request')) ob_start('ob_clean_referer');

//***************
// include config

// fetch parameters from config.inc.php - could be placed in the PHProjekt root or two levels above = outside the webroot!

// only avoid including the config if the setup routine is active ...
if (!defined('setup_included')) {
    define('CONFIG_PATH',PATH_PRE.'config.inc.php');
    // check config path for files in subdir
    if (is_readable(CONFIG_PATH)) {
        $config_loaded = require_once(CONFIG_PATH);
    }
    elseif (is_readable('../../'.CONFIG_PATH)) {
        // if the config.inc.php file is not in the root directory, serch two levels above

        $config_loaded2 = include_once('../../'.CONFIG_PATH);

    }
    else {
        // oh, it cna't be found there either? -> die with panic message
        die("panic: config.inc.php doesn't exist!! Did you restore it after installation? ...<br />(If you run this tool for the first time: please read the file INSTALL in the PHProjekt root directory)<br />To install or upgrade PHProjekt please go to <a href='setup.php'>setup routine</a>");
    }
}

// ****************************
// set variables and contstants
// ****************************
// set include path.
$var_ini_set = ini_set('include_path', ini_get('include_path').':./:');

// set constant to ensure that the lib is included (especially for those who want to access a script directly)
define('lib_included', '1');

// check whether PATH_PRE doesn't redirect to some outer place
// avoid redirection to outer space
if (!preg_match("#^[./]*$#",PATH_PRE)) die('You are not allowed to do this');

// parse transmitted variables
require_once(PATH_PRE.'lib/gpcs_vars.inc.php');
if (!preg_match("#^[./]*$#",PATH_PRE)) die('You are not allowed to do this');
// change path if a script from a subdir is calling ...
define('LANG_PATH',PATH_PRE.'lang');
define('LIB_PATH',PATH_PRE.'lib');
define('IMG_PATH',PATH_PRE.'img');

// custom settings, have to be placed after gpcs_vars.inc.php cause of session_start
if (defined('PHPR_CUSTOM_ENVIRONMENT') && is_readable(PHPR_CUSTOM_ENVIRONMENT)) {
    require_once(PHPR_CUSTOM_ENVIRONMENT);
}

require_once(PATH_PRE."/lib/specialdays.php");

if (defined('PHPR_COMPATIBILITY_MODE') && PHPR_COMPATIBILITY_MODE == 1) {
    constants_to_vars();
}

// define db_prefix
if (defined('PHPR_DB_PREFIX')) define('DB_PREFIX', PHPR_DB_PREFIX);
else define('DB_PREFIX', $db_prefix);

// set time
$dbTSnull = date('YmdHis', time() + PHPR_TIMEZONE*3600);


// ****************************
// set arrays
// ****************************
$helplink_map = array(
'onlinemanual'     => 'Navigation_bar',
'calendar'         => 'Calendar',
'calendar_easy'    => 'Creat_an_easy_event',
'modifying_events' => 'Modifying_events',
'time_card'        => 'Time_card',
'contact_manager'  => 'Contact_manager',
'help_desk'        => 'Help_desk',
'mail_client'      => 'Mail_client',
'bookmarks'        => 'Bookmarks',
'chat'             => 'Chat',
'to_dos'           => 'To_dos',
'notes'            => 'Notes',
'resources'        => 'Resources',
'projects'         => 'Projects',
'file_storage'     => 'File_storage',
'surveys'          => 'Surveys',
'reminder'         => 'Reminder',
'fulltextsearch'   => 'Fulltextsearch',
'forum'            => 'Forum',
'user_profiles'    => 'User_profiles',
'import'           => 'Import',
'settings'         => 'Settings',
'fulltextsearch'   => 'Fulltextsearch',
'Protokoll'        => 'Protokoll'
);

$translated_helps = array(
'de', 'en'
);

// special days
$specialdays_hierachy = array(  "specialdays_argentina" => array(),
"specialdays_austria" => array(),
"specialdays_belgium" => array(),
"specialdays_denmark" => array(),
"specialdays_france" => array(),
"specialdays_germany" => array(),
"specialdays_bavaria" => array("specialdays_germany"),
"specialdays_poland" => array(),
"specialdays_russia" => array(),
"specialdays_spain" => array(),
"specialdays_sweden" => array(),
"specialdays_switzerland" => array(),
"specialdays_switzerland_ag" => array("specialdays_switzerland"),
"specialdays_switzerland_ai" => array("specialdays_switzerland"));

// Port definition to get emails
if (defined('PHPR_QUICKMAIL') && PHPR_QUICKMAIL > 1) {
    $port = array(
    'pop3'            => '110/pop3',
    'pop3s'           => '995/pop3/ssl',
    'pop3 NOTLS'      => '110/pop3/NOTLS',
    'pop3s NVC'       => '995/pop3/ssl/novalidate-cert',
    'imap'            => '143/imap',
    'imap3'           => '220/imap3',
    'imaps'           => '993/imap/ssl',
    'imaps NVC'       => '993/imap/ssl/novalidate-cert',
    'imap4ssl'        => '585/imap4/ssl',
    'imap NOTLS'      => '143/imap/NOTLS',
    'imap NOVAL'      => '143/imap/NOVALIDATE' );
}

// Relation PHProjekt lang - DatePicker lang
$projekt_datepicker_lang = array(
'al' => 'en',
'br' => 'pt-br',
'bg' => 'en',
'ct' => 'en',
'zh' => 'zh',
'cz' => 'en',
'da' => 'en',
'nl' => 'nl',
'ee' => 'en',
'en' => 'en',
'es' => 'es',
'sp' => 'es',
'eh' => 'en',
'fi' => 'fi',
'fr' => 'fr',
'ge' => 'en',
'de' => 'de',
'gr' => 'en',
'he' => 'en',
'hu' => 'hu',
'it' => 'it',
'is' => 'en',
'jp' => 'ja',
'ko' => 'ko',
'lt' => 'en',
'lv' => 'en',
'no' => 'en',
'pt' => 'pt',
'pl' => 'en',
'ro' => 'en',
'ru' => 'en',
'si' => 'en',
'sk' => 'en',
'se' => 'en',
'th' => 'en',
'tr' => 'en',
'tw' => 'zh-tw',
'uk' => 'en');

// *********************
// error and security 1
// *********************

// define the error level
if (!defined('PHPR_ERROR_REPORTING_LEVEL') or !PHPR_ERROR_REPORTING_LEVEL) error_reporting(4);
else error_reporting( E_ALL & ~E_NOTICE);

// initialize some global vars
$lang_cfg= '';
$css_inc = array();
$js_inc  = array();
$he_add  = array();
$onload  = array();

if(isset($_REQUEST['page_change']{0})) $_SESSION['page'][$page_module]  = (int) $_REQUEST['page_change'];

if (!isset($_REQUEST['direction']) || !in_array(strtolower($_REQUEST['direction']), array('asc', 'desc'))) {
    $_REQUEST['direction'] = '';
}
$direction = $_REQUEST['direction'];

// ****************
// language part 1
// ****************
$found = 0;

// language given? -> include language file
if (isset($langua) && (strlen($langua) <= 3)){
    define('LANG',substr($langua,0,3));
    include_once(LANG_PATH.'/'.LANG.'.inc.php');
}

// determine language for login and -if no language is given in the db- further on
else {
    // determine language of browser
    $lang_browser = getenv('HTTP_ACCEPT_LANGUAGE');
    include_once(LIB_PATH."/languages.inc.php");

    $langua = substr($lang_browser,0,2);

    // special patch for canadian users
    if (eregi('ca', $lang)) {
        if (eregi('en', $lang_browser)) { $langua = 'en'; $found = 1; } // english canadian
        if (eregi('fr', $lang_browser)) { $langua = 'fr'; $found = 1; } // french canadian
    }
    // special patch for user with konqueror :-)
    else if (eregi('queror', $lang_browser)) { $langua = 'en'; $found = 1; }
    // otherwise check if language is available
    else {
        if (isset($languages) and isset($languages[$langua])) {
            $found = 1;
        }
    }
    // include the found language
    if ($found){
        define('LANG',substr($langua,0,2));
        include_once(LANG_PATH.'/'.LANG.'.inc.php');
    }
    // nothing found? -> take english
    else {
        $langua = 'en';
        include_once(LANG_PATH.'/en.inc.php'); }
}

// check and secure some special global vars for sorting, filtering, listing, ...
get_charset($langua);
$sort_module = isset($sort_module) ? xss($sort_module) : '';
$filter      = isset($filter) ? xss($filter) : '';
$keyword     = isset($keyword) ? xss($keyword) : '';
$searchterm  = isset($searchterm) ? xss($searchterm) : '';


// ************************
// do the date format stuff
// ************************
require_once(PATH_PRE.'lib/date_format.php');
$date_format_object = new Date_Format();
$date_format = $date_format_object->get_user_format();

// *********************
// error and security 2
// *********************
// avoid this d... error warning since it does not affect the scritps here
$var_ini_set = ini_set('session.bug_compat_42', 1);
$var_ini_set = ini_set('session.bug_compat_warn', 0);

// limit session to a certain time [minutes]
if (defined('PHPR_SESSION_TIME_LIMIT') && PHPR_SESSION_TIME_LIMIT <> 0 && ((!isset($_SESSION['do_not_expire_login'])) || $_SESSION['do_not_expire_login'] <> true)) {
    if (!$sess_begin) {
        $sess_begin = time();
        $_SESSION['sess_begin'] =& $sess_begin;
    }
    else {
        $now = time();
        if (($now - $sess_begin) > (PHPR_SESSION_TIME_LIMIT*60)) {
            // destroy custom session environment if required
            if (defined('PHPR_CUSTOM_ACTIVE') && PHPR_CUSTOM_ACTIVE) {
                unset($_SESSION[PHPR_CUSTOM_SESSION_ARRAY]);
            }
            // destroy the session - on some system the first,
            // on some system the second function doesn't work :-|
            @session_unset();
            @session_destroy();
            $indexpath = PATH_PRE.'index.php';
            // append return path to redirect the user to where he wanted to go
            $return_path = urlencode(xss($_SERVER['REQUEST_URI']));
            die ("<a href='$indexpath?return_path=$return_path' target='_top'>".__('Session time over, please login again')."!</a>");
        }
        else {
            $sess_begin = $now;
            $_SESSION['sess_begin'] =& $sess_begin;
        }
    }
}

// ************
// db functions
// ************
// in setup mode there are no config constants
if (defined('PHPR_DB_HOST')) $db_host = PHPR_DB_HOST;
if (defined('PHPR_DB_USER')) $db_user = PHPR_DB_USER;
if (defined('PHPR_DB_PASS')) $db_pass = PHPR_DB_PASS;
if (defined('PHPR_DB_NAME')) $db_name = PHPR_DB_NAME;

if (defined('PHPR_DB_TYPE')){
    require_once(LIB_PATH.'/db/'.PHPR_DB_TYPE.'.inc.php');
}
else if (isset($_SESSION['db_type']) && $_SESSION['db_type'] != '') {
    define('DB_TYPE',$_SESSION['db_type']);
    require_once(LIB_PATH.'/db/'.DB_TYPE.'.inc.php');
}

// how many records on one page should be displayed?
$perpage_values = array( '3', '10', '20', '30', '50', '100' );

// set default skin, if not already set in the session.
if (!isset($skin)) $skin = PHPR_SKIN;

// ***************************
// Authentication and settings
// ***************************

// fetch user data
// pass this check only it the constant 'avoid_auth' is set in the script
if (!defined('avoid_auth')) {
    require_once(LIB_PATH.'/auth.inc.php');
}
// end authentication
// ***************

// set style again to undertake session settings
set_style();

// ****************
// language part 2
// ****************
// default direction of text, will be overwritten by certain languages

$tmp_charset_conf = get_charset($langua);
$lcfg    = $tmp_charset_conf['lcfg'];
$dir_tag = $tmp_charset_conf['dir_tag'];


if ($lcfg <> '') { $lang_cfg = '<meta http-equiv="Content-Type" content="text/html; '.$lcfg.'" />'."\n"; }
else {$lang_cfg = '';}

// assign help files
// list all languages without own help files, they have to take english
if (eregi('br|da|ee|he|hu|is|jp|ko|lt|lv|no|pl|pt|ru|se|sk',$langua)) { $doc = PATH_PRE.'/help/en'; }
else if ($langua=='tw') { $doc = PATH_PRE.'/help/zh'; }
// assuming catalan users would like to read spanish help  :)
else if ($langua=='ct') { $doc = PATH_PRE.'help/es'; }
// the rest gets their own help files
else { $doc = PATH_PRE.'help/'.$langua; }
// end help files
// end language definitions
// ************************

// ******
// layout
// ******
/*
// skins & css
// include the chosen skin
$css_loaded = @include_once(PATH_PRE.'layout/'.PHPR_SKIN.'/'.PHPR_SKIN.'.php');
//fallback to default layout doesn't exist anymore
if (!$css_loaded) {
include(PATH_PRE.'layout/default/default.php');
$skin = 'default';
} else {
$skin = PHPR_SKIN;
}
*/

// end skins & css

// ****************
// menu & separator

$start_perpage = isset($start_perpage) ? (int) $start_perpage : $perpage_values[4];
// perpage values
$perpage = isset($perpage) ? (int) $perpage : $start_perpage;

// end layout
// **********

// group string for sql queries
if ($user_group) {
    if (isset($module) && $module == 'links') $sql_user_group = "(t_gruppe = ".(int)$user_group.")";
    else                    $sql_user_group = "(gruppe = ".(int)$user_group.")";
}
// all groups available for e.g. admin root, must be true in all cases
else {
    $sql_user_group = "(1 = 1)";
}

// transmit SID in GET-strings if needed (no cookies) only
$sid = (SID ? '&amp;'.SID : '');

// adds hidden fields to some forms
//   - for modules that have different forms for create and modify data
$view_param = array('keyword' => $keyword );


$untouched = "(touched IS NULL OR touched NOT LIKE '%\"$user_kurz\"%')";

// prepare for htmla editor
if (PHPR_SUPPORT_HTML and (isset($module) && isset($_SESSION['show_html_editor']["$module"]) && $_SESSION['show_html_editor']["$module"] == 1)) {
    $js_inc[]  = " src='".PATH_PRE."lib/javascript/fckeditor.js'>";
}

// JavaScript global vars for contexmenu
$js_inc[] = ">var hiliColor = '".PHPR_BGCOLOR_HILI."'; var markColor = '".PHPR_BGCOLOR_MARK."'; var sessid = '$sid';";

// module name and table name may differ -> here is the translation table
$tablename['contacts']    = 'contacts';
$tablename['projects']    = 'projekte';
$tablename['notes']       = 'notes';
$tablename['helpdesk']    = 'rts';
$tablename['rts']         = 'rts';
$tablename['todo']        = 'todo';
$tablename['files']       = 'dateien';
$tablename['mail']        = 'mail_client';
$tablename['links']       = 'db_records';
$tablename['calendar']    = 'termine';
$tablename['bookmarks']   = 'lesezeichen';
$tablename['forum']       = 'forum';
$tablename['filemanager'] = 'dateien';
$tablename['costs'] = 'costs';

//acc fields for the modules
$acc_field['contacts']    = 'acc_read';
$acc_field['projects']    = 'acc';
$acc_field['notes']       = 'acc';
$acc_field['helpdesk']    = 'acc_read';
$acc_field['todo']        = 'acc';
$acc_field['files']       = 'acc';
$acc_field['mail']        = 'acc';
$acc_field['links']       = 't_acc';
$acc_field['forum']       = 'acc';
$acc_field['filemanager'] = 'acc';
$acc_field['costs']       = 'acc';

/**
 * Redirect external links to avoid session propagation by referer
 * Include redirect script
 *
 * @param array $ary
 * @return string
 */
function ob_replace_link($ary) {
    $newlink = sprintf('%sbookmarks/bookmarks.php?lesezeichen=%s', PATH_PRE, urlencode($ary[3]));
    $return = sprintf('%s%s%s%s', $ary[1], $ary[2], $newlink, $ary[4]);
    return $return;
}

/**
 * Redirect external links to avoid session propagation by referer
 * do that using output buffering, if a transparent session is detected
 *
 * @param string $input
 * @return sring
 */
function ob_clean_referer($input) {
    if (SID != '') {
        return preg_replace_callback('#(<a.*href=)(["\'])(http.*)(\\2[^>]*>)#msiU', 'ob_replace_link', $input);
    } else {
        return $input;
    }
}


// ****************
// string functions
// ****************
// safe HTML output
// This is a white list filter, that only allows p, br, b, i, ul, u,
// ol, li, strong, em as valid text.
// It is secure but won't work with more complicated html from
// FCKedit.
function html_out($outstr) {
    // first clean using blacklist
    $outstr = xss($outstr);
    if ($outstr <> '') {
        // replacing the ' is important for input-fields!
        $outstr = str_replace("'","&#39;",htmlspecialchars($outstr, ENT_NOQUOTES));
    }
    return $outstr;
}


/**
* xss cleaner
* uses html purifier
* @author Albrecht Guenther
* @param string $data
* @return string $data
*/
function xss($data) {
    if (is_array($data)) {
        return xss_array($data);
    }

    // HTMLPurifier has functions for PHP > 4.3.0, then we have to check it
    if (version_compare(phpversion(), "4.3.0", "<")) {

        // Using the old fashion way to clean the strings
        $data = xss_old_php($data);
    }
    else {
        static $purifier;
        // create new object
        if (!is_object($purifier)) {
            require_once PATH_PRE.'lib/html/library/HTMLPurifier.auto.php';
            $config = HTMLPurifier_Config::createDefault();
            $config->set('Core', 'Encoding', LANG_CODE); //replace with your encoding
            $config->set('Core', 'XHTML', false); //replace with false if HTML 4.01
            $purifier = new HTMLPurifier($config);
        }
        $data = $purifier->purify($data);
    }

    return $data;
}

/**
* xss cleaner for PHP older than 4.3.0
* uses html purifier
* This is a black list filter, that allows all tags and takes out
* some known xss issues. It is _not_ secury, as there are always new
* scripting issues in the browser being found. But at least it works
* with FCKedit generated content.
* taken from Horde_MIME_Viewer, licensed under GPL
* @author Anil Madhavapeddy, Jon Parise, Michael Slusarz
* @param string $data
* @return string $data
*/
function xss_old_php($data) {
    /* Deal with <base> tags in the HTML, since they will screw up
    * our own relative paths. */
    if (($i = stristr($data, '<base ')) && ($i = stristr($i, 'http')) &&
    ($j = strchr($i, '>'))) {
        $base = substr($i, 0, strlen($i) - strlen($j));
        $base = preg_replace('|(http.*://[^/]*/?).*|i', '\1', $base);
        if ($base[strlen($base) - 1] != '/') {
            $base .= '/';
        }
        /* Recursively call _cleanHTML() to prevent clever fiends
        * from sneaking nasty things into the page via $base. */
        $base = html_out2($base);
    }

    /* Change space entities to space characters. */
    $data = preg_replace('/&#(x0*20|0*32);?/i', ' ', $data);

    /* Nuke non-printable characters (a play in three acts). */

    /* Rule 1). If we have a semicolon, it is deterministically
    * detectable and fixable, without introducing collateral
    * damage. */
    $data = preg_replace('/&#x?0*([9A-D]|1[0-3]);/i', '&nbsp;', $data);

    /* Rule 2). Hex numbers (usually having an x prefix) are also
    * deterministic, even if we don't have the semi. Note that
    * some browsers will treat &#a or &#0a as a hex number even
    * without the x prefix; hence /x?/ which will cover those
    * cases in this rule. */
    $data = preg_replace('/&#x?0*[9A-D]([^0-9A-F]|$)/i', '&nbsp\\1', $data);

    /* Rule 3). Decimal numbers without trailing semicolons. The
    * problem is that some browsers will interpret &#10a as
    * "\na", some as "&#x10a" so we have to clean the &#10 to be
    * safe for the "\na" case at the expense of mangling a valid
    * entity in other cases. (Solution for valid HTML authors:
    * always use the semicolon.) */
    $data = preg_replace('/&#0*(9|1[0-3])([^0-9]|$)/i', '&nbsp\\2', $data);

    /* Remove overly long numeric entities. */
    $data = preg_replace('/&#x?0*[0-9A-F]{6,};?/i', '&nbsp;', $data);

    /* Get all attribute="javascript:foo()" tags. This is
    * essentially the regex /(=|url\()("?)[^>]*script:/ but
    * expanded to catch camouflage with spaces and entities. */
    $preg = '/((&#0*61;?|&#x0*3D;?|=)|' .
    '((u|&#0*85;?|&#x0*55;?|&#0*117;?|&#x0*75;?)\s*' .
    '(r|&#0*82;?|&#x0*52;?|&#0*114;?|&#x0*72;?)\s*' .
    '(l|&#0*76;?|&#x0*4c;?|&#0*108;?|&#x0*6c;?)\s*' .
    '(\()))\s*' .
    '(&#0*34;?|&#x0*22;?|"|&#0*39;?|&#x0*27;?|\')?' .
    '[^>]*\s*' .
    '(s|&#0*83;?|&#x0*53;?|&#0*115;?|&#x0*73;?)\s*' .
    '(c|&#0*67;?|&#x0*43;?|&#0*99;?|&#x0*63;?)\s*' .
    '(r|&#0*82;?|&#x0*52;?|&#0*114;?|&#x0*72;?)\s*' .
    '(i|&#0*73;?|&#x0*49;?|&#0*105;?|&#x0*69;?)\s*' .
    '(p|&#0*80;?|&#x0*50;?|&#0*112;?|&#x0*70;?)\s*' .
    '(t|&#0*84;?|&#x0*54;?|&#0*116;?|&#x0*74;?)\s*' .
    '(:|&#0*58;?|&#x0*3a;?)/i';
    $data = preg_replace($preg, '\1\8HordeCleaned', $data);

    /* Get all on<foo>="bar()". NEVER allow these. */
    $data = preg_replace('/([\s"\']+' .
    '(o|&#0*79;?|&#0*4f;?|&#0*111;?|&#0*6f;?)' .
    '(n|&#0*78;?|&#0*4e;?|&#0*110;?|&#0*6e;?)' .
    '\w+)\s*=/i', '\1HordeCleaned=', $data);

    /* Remove all scripts since they might introduce garbage if
    * they are not quoted properly. */
    $data = preg_replace('|<script[^>]*>.*?</script>|is', '<HordeCleaned_script />', $data);

    /* Get all tags that might cause trouble - <object>, <embed>,
    * <base>, etc. Meta refreshes and iframes, too. */
    $malicious = array(
    '/<([^>a-z]*)' .
    '(s|&#0*83;?|&#x0*53;?|&#0*115;?|&#x0*73;?)\s*' .
    '(c|&#0*67;?|&#x0*43;?|&#0*99;?|&#x0*63;?)\s*' .
    '(r|&#0*82;?|&#x0*52;?|&#0*114;?|&#x0*72;?)\s*' .
    '(i|&#0*73;?|&#x0*49;?|&#0*105;?|&#x0*69;?)\s*' .
    '(p|&#0*80;?|&#x0*50;?|&#0*112;?|&#x0*70;?)\s*' .
    '(t|&#0*84;?|&#x0*54;?|&#0*116;?|&#x0*74;?)\s*/i',

    '/<([^>a-z]*)' .
    '(e|&#0*69;?|&#0*45;?|&#0*101;?|&#0*65;?)\s*' .
    '(m|&#0*77;?|&#0*4d;?|&#0*109;?|&#0*6d;?)\s*' .
    '(b|&#0*66;?|&#0*42;?|&#0*98;?|&#0*62;?)\s*' .
    '(e|&#0*69;?|&#0*45;?|&#0*101;?|&#0*65;?)\s*' .
    '(d|&#0*68;?|&#0*44;?|&#0*100;?|&#0*64;?)\s*/i',

    '/<([^>a-z]*)' .
    '(b|&#0*66;?|&#0*42;?|&#0*98;?|&#0*62;?)\s*' .
    '(a|&#0*65;?|&#0*41;?|&#0*97;?|&#0*61;?)\s*' .
    '(s|&#0*83;?|&#x0*53;?|&#0*115;?|&#x0*73;?)\s*' .
    '(e|&#0*69;?|&#0*45;?|&#0*101;?|&#0*65;?)\s*' .
    '[^line]/i',

    '/<([^>a-z]*)' .
    '(m|&#0*77;?|&#0*4d;?|&#0*109;?|&#0*6d;?)\s*' .
    '(e|&#0*69;?|&#0*45;?|&#0*101;?|&#0*65;?)\s*' .
    '(t|&#0*84;?|&#x0*54;?|&#0*116;?|&#x0*74;?)\s*' .
    '(a|&#0*65;?|&#0*41;?|&#0*97;?|&#0*61;?)\s*/i',

    '/<([^>a-z]*)' .
    '(j|&#0*74;?|&#0*4a;?|&#0*106;?|&#0*6a;?)\s*' .
    '(a|&#0*65;?|&#0*41;?|&#0*97;?|&#0*61;?)\s*' .
    '(v|&#0*86;?|&#0*56;?|&#0*118;?|&#0*76;?)\s*' .
    '(a|&#0*65;?|&#0*41;?|&#0*97;?|&#0*61;?)\s*/i',

    '/<([^>a-z]*)' .
    '(o|&#0*79;?|&#0*4f;?|&#0*111;?|&#0*6f;?)\s*' .
    '(b|&#0*66;?|&#0*42;?|&#0*98;?|&#0*62;?)\s*' .
    '(j|&#0*74;?|&#0*4a;?|&#0*106;?|&#0*6a;?)\s*' .
    '(e|&#0*69;?|&#0*45;?|&#0*101;?|&#0*65;?)\s*' .
    '(c|&#0*67;?|&#x0*43;?|&#0*99;?|&#x0*63;?)\s*' .
    '(t|&#0*84;?|&#x0*54;?|&#0*116;?|&#x0*74;?)\s*/i',

    '/<([^>a-z]*)' .
    '(i|&#0*73;?|&#x0*49;?|&#0*105;?|&#x0*69;?)\s*' .
    '(f|&#0*70;?|&#0*46;?|&#0*102;?|&#0*66;?)\s*' .
    '(r|&#0*82;?|&#x0*52;?|&#0*114;?|&#x0*72;?)\s*' .
    '(a|&#0*65;?|&#0*41;?|&#0*97;?|&#0*61;?)\s*' .
    '(m|&#0*77;?|&#0*4d;?|&#0*109;?|&#0*6d;?)\s*' .
    '(e|&#0*69;?|&#0*45;?|&#0*101;?|&#0*65;?)\s*/i');

    $data = preg_replace($malicious, '<HordeCleaned_tag', $data);

    /* Comment out style/link tags, only if we are viewing inline.
    * NEVER show style tags to Netscape 4.x users since 1) the
    * output will really, really suck and 2) there might be
    * security issues. */
    $pattern = array('/\s+style\s*=/i',
    '|<style[^>]*>(?:\s*<\!--)*|i',
    '|(?:-->\s*)*</style>|i',
    '|(<link[^>]*>)|i');
    $replace = array(' HordeCleaned=',
    '<!--',
    '-->',
    '<!-- $1 -->');
    $data = preg_replace($pattern, $replace, $data);

    /* A few other matches. */
    $pattern = array('|<([^>]*)&{.*}([^>]*)>|',
    '|<([^>]*)mocha:([^>]*)>|i',
    '|<([^>]*)binding:([^>]*)>|i');
    $replace = array('<&{;}\3>',
    '<\1HordeCleaned:\2>',
    '<\1HordeCleaned:\2>');
    $data = preg_replace($pattern, $replace, $data);

    /* Attempt to fix paths that were relying on a <base> tag. */
    if (!empty($base)) {
        $pattern = array('|src=(["\'])/|i',
        '|src=[^\'"]/|i',
        '|href= *(["\'])/|i',
        '|href= *[^\'"]/|i');
        $replace = array('src=\1' . $base,
        'src=' . $base,
        'href=\1' . $base,
        'href=' . $base);
        $data = preg_replace($pattern, $replace, $data);
    }

    /* Check for phishing exploits. */
    if (preg_match('/href\s*=\s*["\']?\s*(http|https|ftp):\/\/\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}/i', $data)) {
        /* Check 1: Check for IP address links. */
        $phish_warn = true;
    } elseif (preg_match_all('/href\s*=\s*["\']?\s*(?:http|https|ftp):\/\/([^\s"\'>]+)["\']?[^>]*>\s*(?:(?:http|https|ftp):\/\/)?(.*?)<\/a/i', $data, $m)) {
        /* $m[1] = Link; $m[2] = Target
        * Check 2: Check for links that point to a different host than
        * the target url; if target looks like a domain name, check it
        * against the link. */
        $links = count($m[0]);
        for ($i = 0; $i < $links; $i++) {
            $m[2][$i] = strip_tags($m[2][$i]);
            if (preg_match('/^[.-_\da-z]+\.[a-z]{2,}/i', $m[2][$i]) &&
            strpos(urldecode($m[1][$i]), $m[2][$i]) !== 0 &&
            strpos($m[2][$i], urldecode($m[1][$i])) !== 0) {
                /* Don't consider the link a phishing link if the domain
                * is the same on both links (e.g. adtracking.example.com &
                * www.example.com). */
                preg_match('/\.?([^\.\/]+\.[^\.\/]+)\//', $m[1][$i], $host1);
                preg_match('/\.?([^\.\/]+\.[^\.\/]+)(\/.*)?$/', $m[2][$i], $host2);
                if (!(count($host1) && count($host2)) ||
                strcasecmp($host1[1], $host2[1]) !== 0) {
                    $phish_warn = true;
                }
            }
        }
    }

    return $data;
}

// allow only some chars for db table and fields names
function qss($data) {
    $data = preg_replace("/[^0-9a-z_,.]/i", "", $data);
    return $data;
}

// use xss function for each item of an array
function xss_array($data) {
    $new_data = array();
    if (is_array($data)) {
        foreach ($data as $key => $value) {
            // array in array
            if (is_array($value)) {
                $new_data[xss($key)] = xss_array($value);
            } else {
                $new_data[xss($key)] = xss($value);
            }
        }
        return $new_data;
    } else {
        // if is not array, why do you use this function?
        return xss($data);
    }
}

// the same specialy for hidden form fields and select field option values (uev -> UrlEncodedValues)
function uev_out($outstr) {
    return ereg_replace("'", "&#39;", htmlspecialchars(urlencode($outstr)));
}

// TODO: the next two functions should be obsolete next time cause
//       they are replaced by methods in the Date_Format class
// here we will build the iso format from a timestamp, e.g. 20040115235912 will be 2004-01-15 23:59
function show_iso_date1($date) {
    return substr($date,0,4).'-'.substr($date,4,2).'-'.substr($date,6,2).' '.substr($date,8,2).':'.substr($date,10,2);
}

function show_iso_date2($date) {
    return substr($date,0,4).'-'.substr($date,4,2).'-'.substr($date,6,2);
}


// quotation related string treatment for arrays
// to call with array_walk()
// function arr_addsl(&$item,$key){$item = addslashes($item);}
// is moved to gpcs_vars.inc.php because used before loading lib.inc.php
function arr_stripsl(&$item, $key) {
    $item = stripslashes($item);
}
function arr_dequote(&$item, $key) {
    $item = addslashes(ereg_replace('^\"|\"$', '', $item));
}

// change colours in list view
function tr_tag($dblclick, $parent='', $rec_id=0, $str_value='', $color=null, $module='id', $classname='') {
    global $cnr, $menu2, $output1, $contextmenu;

    $tr_class    = '';
    $tr_bgcolor  = '';
    $tr_hover_on = '';

    // class overrules given color
    if ($classname <> '') {
        $tr_class = "class='$classname'";
    }
    else if ($color <> '') {
        $tr_bgcolor = "style='background:$color'";
    }
    else {
        // alternate bgcolor
        if (($cnr/2) == round($cnr/2)){
            $tr_bgcolor = "style='background:".PHPR_BGCOLOR1."'";
            $color=PHPR_BGCOLOR1;
        }
        else{
            $tr_bgcolor = "style='background:".PHPR_BGCOLOR2."'";
            $color=PHPR_BGCOLOR2;
        }
    }
    $cnr++;

    // highlight and marker in table rows
    $i = ($rec_id ? $rec_id : $cnr);
    //$i = $cnr;
    #$i = strval("$module".'xxx'."$i");
    $str_mark = ' id="ID'.$i.'"';
    if ($rec_id) {
        $r_ID   = $rec_id;
        #$rec_id = strval("$module".'xxx'."$rec_id");
        if (!$str_value) $str_value = __('No Value');
        //für valides xhtml
        if ($contextmenu > 0) $str_mark .= " onclick=\"marker(this,'$rec_id')\" oncontextmenu=\"startMenu('".$menu2->menulistID."','$rec_id','','$r_ID')\"";
    }
    //für valides xhtml
    $output1 .= "<script type=\"text/javascript\">allRows['$i'] = new Array('$color','$str_value');</script>\n";
    if (PHPR_TR_HOVER) $tr_hover_on = "onmouseover=\"hiliOn(this,'$i')\" onmouseout=\"hiliOff(this,'$i')\" ondblclick=\"".$parent."location.href = '$dblclick'\"".$str_mark;

    // html output
    $output1 .= "<tr $tr_class $tr_bgcolor $tr_hover_on>\n";
}

// *************
// date and time
function today() {
    global $year, $month, $day;
    $year  = date('Y', mktime(date('H')+PHPR_TIMEZONE, date('i'), date('s'), date('m'), date('d'), date('Y')));
    $month = date('m', mktime(date('H')+PHPR_TIMEZONE, date('i'), date('s'), date('m'), date('d'), date('Y')));
    $day   = date('d', mktime(date('H')+PHPR_TIMEZONE, date('i'), date('s'), date('m'), date('d'), date('Y')));
}


// sets a form element (in fact: all elements of a form) to inactive
function read_o($read_o, $type='disabled',$style='') {
    if ($read_o == 0) return '';
    else {
        if ($type == 'readonly') return ' readonly="readonly" style="background-color:'.PHPR_BGCOLOR3.';'.$style.'"';
        else return ' disabled="disabled" style="background-color:'.PHPR_BGCOLOR3.';'.$style.'"';
    }
}

/**
* get elements of tree
* same as show_elements_of_tree() but returning data instead of rendered html output
* @author Alex Haslberger
* @param ...
* @return string $html
*/
function get_elements_of_tree($table, $name, $query, $acc, $order, $sel_record, $parent, $exclude_ID=0) {
    global $records, $selected_record, $db_table, $children;

    $records  = array();
    $children = array();
    $db_table = $table;
    $selected_record = $sel_record;

    $new_val = array();
    foreach(explode(',', $name) as $a_val) {
        $new_val[] = qss($a_val);
    }
    $name = implode(',', $new_val);

    $result = db_query("SELECT ID, ".qss($acc).", ".qss($parent).", $name
                          FROM ".qss(DB_PREFIX.$table)."
                               $query
                               $order") or db_die();

    $remaining_rows = PHPR_FILTER_MAXHITS;
    $mainrecords = array();
    while (($row = db_fetch_row($result)) and $remaining_rows > 0) {
        if ($row[0] <> $exclude_ID) {
            $remaining_rows--;
            $record = array();
            // first element will be an array which keeps the children of this record
            foreach ($row as $element) { $record[] = $element; }
            // ... one array for the main records ...
            if ($row[2] == 0 or !$row[2]) {$mainrecords[] = $record[0]; }
            // ... one array which keeps all elements below the current record
            else { $children[$row[2]][] = $row[0]; }
            // ... and one for all records :)
            $records[$record[0]] = $record;
        }
    }
    // end of creating the arrays, now loop over them and display them in the select box
    $data = array();
    foreach($mainrecords as $mainrecID) {
        $data = array_merge($data, get_subelements($mainrecID));
    }
    $children = array();
    return $data;
}

/**
* get subelements
* same as show_elem2() but returning data instead of rendered html output
* @author Alex Haslberger
* @param int $ID
* @return array $data
*/
function get_subelements($ID){
    global $db_table, $indent, $selected_record, $records, $children;

    // additional conditions for some modules
    switch ($db_table) {
        // if the table is table projects, check whether the user is a participant of the project
        case 'projekte':
            $allowed = 1;
            break;
        case 'contacts':
            // last name, first name in the select box gives a better distinction
            $records[$ID][3] = $records[$ID][3].",".$records[$ID][4];
            // if a company record is given, include him as well
            if ($records[$ID][5] <> '') $records[$ID][3] .= ' ('.$records[$ID][5].')';
            // since in the query the permission is already included we don't need another criterium
            $allowed = 1;
            break;
        case 'dateien':
            $records[$ID][3] = ereg_replace("§"," ",$records[$ID][3]);
            $allowed = 1;
            break;
        case 'notes':
            $allowed = 1;
            break;
    }
    $data = array();
    // first show the records itself if access is allowed
    if ($allowed == 1) {
        $tmp = array();
        $tmp['value']    = $records[$ID][0];
        $tmp['selected'] = ($records[$ID][0] == $selected_record) ? true : false;
        $tmp['depth']    = $indent;
        $tmp['text']     = $records[$ID][3];
        $data[] = $tmp;
    }
    // look for subelements
    if (!empty($children[$ID][0])) {
        foreach ($children[$ID] as $child) {
            $indent++;
            $data = array_merge($data, get_subelements((int) $child));
            $indent--;
        }
    }
    return $data;
}

// *********************
// show elements of tree
// this function returns the level of an select-element - useful to indent elements in a list
// parameter: table and column name, $query, $access column, order by, value of element to show as selected, name of parent column, exclude the selected ID select children?
function show_elements_of_tree($table, $name, $query, $acc, $order, $sel_record, $parent, $exclude_ID=0) {
    global $records, $selected_record, $db_table, $children;

    $records  = array();
    $children = array();
    $db_table = $table;
    $selected_record = $sel_record;

    $new_val = array();
    foreach(explode(',', $name) as $a_val) {
        $new_val[] = qss($a_val);
    }
    $name = implode(',', $new_val);
    //avoid errors in case module project isn't activated, for other modulesm no problems occur as they are only call this function within themselves
    if ((PHPR_PROJECTS and check_role('projects') > 0) or $table!='projekte') {
        $result = db_query("SELECT ID, ".qss($acc).", ".qss($parent).", $name
                          FROM ".qss(DB_PREFIX.$table)."
                               $query
                               $order") or db_die();
        while ($row = db_fetch_row($result)) {
            if ($row[0] <> $exclude_ID) {
                $record = array();
                // first element will be an array which keeps the children of this record
                foreach ($row as $element) { $record[] = $element; }
                // ... one array for the main records ...
                if ($row[2] == 0 or !$row[2]) {$mainrecords[] = $record[0]; }
                // ... one array which keeps all elements below the current record
                else { $children[$row[2]][] = $row[0]; }
                // ... and one for all records :)
                $records[$record[0]] = $record;
            }
        }
        // end of creating the arrays, now loop over them and display them in the select box
        if ($mainrecords) foreach($mainrecords as $mainrecID) {
            $output2 .= show_elem2($mainrecID);
        }
        $children = array();
    }
    return $output2;
}


function show_elem2($ID) {
    global $db_table, $indent, $user_kurz, $subdirs, $selected_record, $records, $children;
    // additional conditions for some modules
    switch ($db_table) {
        // if the table is table projects, check whether the user is a participant of the project
        case 'projekte':
            $allowed = 1;
            break;
        case 'contacts':
            // last name, first name in the select box gives a better distinction
            $records[$ID][3] = $records[$ID][3].",".$records[$ID][4];
            // if a company record is given, include him as well
            if ($records[$ID][5] <> '') $records[$ID][3] .= ' ('.$records[$ID][5].')';
            // since in the query the permission is already included we don't need another criterium
            $allowed = 1;
            break;
        case 'dateien':
            $records[$ID][3] = ereg_replace("§"," ",$records[$ID][3]);
            $allowed = 1;
            break;
        case 'notes':
            $allowed = 1;
            break;
        case 'mail_client':
            $allowed = 1;
            break;
    }
    // first show the records itself if access is allowed
    if ($allowed == 1) {
        $outputtree .= "<option value='".(int)$records[$ID][0]."'";
        if ($records[$ID][0] == $selected_record) $outputtree.= ' selected="selected"';
        $outputtree .= ' title="'.strip_tags($records[$ID][3]).'">';
        for ($i = 1; $i <= $indent; $i++) {
            $outputtree .= '&nbsp;&nbsp;';
        }
        $outputtree .= strip_tags($records[$ID][3])."</option>\n";
    }

    // look for subelements
    if (!empty($children[$ID][0])) {
        foreach ($children[$ID] as $child) {
            $indent++;
            $outputtree.= show_elem2((int) $child);
            $indent--;
        }
    }
    return $outputtree;
}
// end show elements of tree
// *************************

function hidden_fields($hid) {
    if (SID) $hid[session_name()] = session_id();
    $str = '';
    if (is_array($hid)) {
        foreach ($hid as $key=>$value) {
            $str .= "<input type='hidden' name='".$key."' value='".xss($value)."' />\n";
        }
    }

    $str .= "<input type='hidden' name='csrftoken' value='".make_csrftoken()."' />\n";

    return $str;
}

/*
* Make an array of random tokens
* for check it when submit the forms
*/
function make_csrftoken()
{
    if (!isset($_SESSION['form_token'])) {
        $_SESSION['form_token'] = array();
        $_SESSION['form_token_index'] = 1;
    }

    if ($_SESSION['form_token_index'] == 75) {
        $_SESSION['form_token_index'] = 1;
    } else {
        $_SESSION['form_token_index']++;
    }
    $token = md5 (uniqid (rand()));
    $_SESSION['form_token'][$_SESSION['form_token_index']] = $token;

    return $token;
}

/*
* Check if a token is in the SESSION array
* @return true or die the script
*/
function check_csrftoken(){

    $msg = __("You are not allowed to do this!");

    if (isset($_GET['csrftoken'])) {
        $token = xss($_GET['csrftoken']);
    } else if (isset($_POST['csrftoken'])) {
        $token = xss($_POST['csrftoken']);
    } else {
        die($msg);
    }

    if (!isset($_SESSION['form_token'])) {
        die($msg);
    }

    if (in_array($token, $_SESSION['form_token'])) {
        $pos = array_search($token, $_SESSION['form_token']);
        unset($_SESSION['form_token'][$pos]);
        return true;
    } else {
        die($msg);
    }
}

/**
* check which access status the user has concerning a module and according to his role
* first call: get userroles from database, further calls: return userroles from SESSION
* @author Alex Haslberger
* @param string $module module name
* @return string $role user role
*/
function check_role($module) {
    if(!PHPR_ROLES) {
        return '2';
    }
    global $user_ID;
    // not yet read the userroles from database
    if (!isset($_SESSION['userroles'])) {
        $mapping_active = array(
        'todo'        => PHPR_TODO,
        'votum'       => PHPR_VOTUM,
        'bookmarks'   => PHPR_BOOKMARKS,
        'links'       => PHPR_LINKS,
        'calendar'    => PHPR_CALENDAR,
        'projects'    => PHPR_PROJECTS,
        'timecard'    => PHPR_TIMECARD,
        'contacts'    => PHPR_CONTACTS,
        'notes'       => PHPR_NOTES,
        'mail'        => PHPR_QUICKMAIL,
        'filemanager' => PHPR_FILEMANAGER,
        'forum'       => PHPR_FORUM,
        'helpdesk'    => PHPR_RTS,
        'chat'        => PHPR_CHAT,
        'summary'     => 1,
        'links'       => 1,
        'costs'       => PHPR_COSTS,
        );

        // possible roles
        $roles = array( 'calendar',
        'contacts',
        'forum',
        'chat',
        'filemanager',
        'bookmarks',
        'votum',
        'mail',
        'notes',
        'helpdesk',
        'projects',
        'timecard',
        'todo');

        if (defined('PHPR_COSTS') && (PHPR_COSTS == 1)) {
            // possible roles
            $roles = array( 'calendar',
            'contacts',
            'forum',
            'chat',
            'filemanager',
            'bookmarks',
            'votum',
            'mail',
            'notes',
            'helpdesk',
            'projects',
            'timecard',
            'todo',
            'costs');
        }

        $query = "SELECT ".DB_PREFIX."roles.ID, ".implode(',', $roles)."
                    FROM ".DB_PREFIX."roles, ".DB_PREFIX."users
                   WHERE ".DB_PREFIX."users.role = ".DB_PREFIX."roles.ID
                     AND ".DB_PREFIX."users.ID = ".(int)$user_ID;
        $res = db_query($query) or db_die();
        $row = db_fetch_row($res);
        // is there a role for this user?
        if ($row[0] > 0) {
            $i = 0;
            foreach ($roles as $role) {
                ++$i;
                // is this module active at all ?
                // the numeric value of the status: 0 = no access, 1 = read, 2 = write
                $access = is_null($row[$i]) ? '2' : $row[$i];
                $_SESSION['userroles'][$role] = $mapping_active[$role] ? $access : '0';
            }
        }
        // otherwise give him the full rights
        else {
            foreach ($roles as $role) {
                // is this module active at all ?
                // the numeric value of the status: 0 = no access, 1 = read, 2 = write
                $_SESSION['userroles'][$role] = $mapping_active[$role] ? '2' : '0';
            }
        }
    }
    return $_SESSION['userroles'][$module];
}

/**
* calculate the users access from given roles, rights ...
*
* @author Alex Haslberger
* @param string $module module name
* @param string $right right to be checked
* @return boolean
*/
function calculate_user_access($module, $right){
    global $user_ID;
    // .....
    return false;
}

/**
* Create exportlink (formerly an exportform) that links to export_page.php
* from which the export is done.
*
* @param  string $file  identifier used in misc/export.php
* @param  string $class
* @return string link to the export-form
*/
function show_export_form($file, $class='') {
    global $month, $year, $anfang, $ende;

    if ($class == '') {
        $class = 'navbutton navbutton_inactive';
    }

    $hidden = array( 'file'      => $file,
    session_name() => session_id(),
    'month'     => $month,
    'year'      => $year );

    if ($file == 'project_stat') $hidden = array_merge(array('anfang'=>$anfang, 'ende'=>$ende), $hidden);

    $out = array();
    //Session!
    foreach ($hidden as $key => $value) {
        $out[] = $key.'='.urlencode($value);
    }
    unset($key, $value);
    $out = "<a class='$class' href='../misc/export_page.php?".implode("&amp;", $out)."'>".__('export')."</a>";
    return $out;

    // -----------------
    // the old function returning a drop-down-form.
    // could be used for a JS-version
    /*
    global $filter, $month, $year, $anfang, $ende;
    $hidden = array( 'file'      => $file,
    session_name() => session_id(),
    'filter'    => $filter,
    'month'     => $month,
    'year'      => $year );

    if ($file == 'project_stat') $hidden = array_merge(array('anfang'=>$anfang, 'ende'=>$ende), $hidden);
    $out  = "<form style='display:inline;' action='../misc/export.php' method='post' target='_blank'>\n";
    $out .= hidden_fields($hidden);
    $out .= "<select name='medium' onchange='submit();'>\n";
    $out .= "<option value=''>".__('export').":</option>\n";
    if ($file == 'calendar') {
    $out .= "<option value='ics'>iCal</option>\n";
    $out .= "<option value='xml'>XML</option>\n";
    $out .= "<option value='csv'>CSV</option>\n";
    }
    else {
    if (PHPR_SUPPORT_PDF) $out.= "<option value='pdf'>PDF</option>\n";
    $out .= "<option value='xml'>XML</option>\n";
    $out .= "<option value='html'></option>\n";
    $out .= "<option value='csv'>CSV</option>\n";
    $out .= "<option value='xls'>XLS</option>\n";
    $out .= "<option value='rtf'>RTF</option>\n";
    $out .= "<option value='doc'>DOC</option>\n";
    $out .= "<option value='print'>".__('print')."</option>\n";
    }
    $out .= "</select>\n</form>\n";
    return $out;
    */
}

/**
* nearly the same as show_export_form(), but returns data array instead of string and class parameter has changed to active parameter
* @author Alex Haslberger
* @param string $file module file name
* @param string $active indicates highlight status
* @return array $out array containing all export link data
*/
function get_export_link_data($file, $active = false) {
    global $month, $year, $anfang, $ende, $operator;
    $out = array();
    $hidden = array( 'file'         => $file,
    session_name() => session_id(),
    'month'        => $month,
    'year'         => $year,
    'operator'     =>$operator );
    if ($file == 'project_stat'){
        $hidden = array_merge(array('anfang' => $anfang, 'ende' => $ende), $hidden);
    }
    if ($file == 'project_stat_date'){
        $hidden = array_merge(array('anfang' => $anfang, 'ende' => $ende), $hidden);
    }
    $tmp = array();
    foreach ($hidden as $key => $value) {
        $tmp[] = $key.'='.urlencode($value);
    }
    $out['href']   = '../misc/export_page.php?'.implode('&amp;', $tmp);
    $out['text']   = __('export');
    $out['active'] = $active;
    return $out;
}

/**
* returns skin related stylesheet
*
* @author Alex Haslberger
* @todo   implement browser specific styles
* @return string
*/
// this function gets the OS of the browser and chooses the appropiate css file
function set_style() {
    global $skin, $css_inc, $css_void_background_image, $setting_skin, $justform;

    if ((isset($css_void_background_image) && $css_void_background_image == true) or $justform==1) {
        $vbi = '?void_background_image=1';
    }
    else {
        $vbi = '';
    }
    if (strstr($_SERVER['QUERY_STRING'], 'module=logout')) {
        $skin = PHPR_SKIN;
    }
    // comes from settings?
    if (isset($setting_skin)) {
        $skin = $setting_skin;
    }
    // custom skin?
    $found = false;
    if (isset($skin)) {
        if (file_exists(PATH_PRE."layout/".$skin."/".$skin.".css")) {
            $css_inc['mainstyle']  = '<link type="text/css" rel="stylesheet" media="screen" href="'.PATH_PRE.'layout/'.$skin.'/'.$skin.'.css'.$vbi.'" />'."\n";
            $css_inc['printstyle'] = '<link type="text/css" rel="stylesheet" media="print" href="'.PATH_PRE.'layout/'.$skin.'/'.$skin.'_print.css" />'."\n";
            // is there a browser-file?
            if (file_exists(PATH_PRE."layout/".$skin."/".$skin."_ie.css")) {
                $css_inc['iestyle'] = '<!--[if gte IE 5]><link type="text/css" rel="stylesheet" media="screen" href="'.PATH_PRE.'layout/'.$skin.'/'.$skin.'_ie.css" /><![endif]-->'."\n";
            }
            $found = true;
        }
    }
    // if no skin is set, fallback to default style
    if (!$found) {
        $css_inc['mainstyle']  = '<link type="text/css" rel="stylesheet" media="screen" href="'.PATH_PRE.'layout/default/default.css'.$vbi.'" />'."\n";
        $css_inc['printstyle'] = '<link type="text/css" rel="stylesheet" media="print" href="'.PATH_PRE.'layout/default/default_print.css" />'."\n";
        $css_inc['iestyle']    = '<!--[if gte IE 5]><link type="text/css" rel="stylesheet" media="screen" href="'.PATH_PRE.'layout/default/default_ie.css" /><![endif]-->'."\n";
    }

} // end find style sheet


// for ldap
function logit($message) {
    openlog('phprojekt', LOG_NDELAY|LOG_PID, LOG_USER);
    syslog(LOG_DEBUG, $message);
    closelog();
}

// for debugging :)
function get_mt() {
    list($usec, $sec) = explode(' ', microtime());
    return ((float)$usec + (float)$sec);
}

function show_mt($begin) {
    list($usec, $sec) = explode(' ', microtime());
    echo 'This action tooks '.sprintf('%.4f', ((float)$usec + (float)$sec) - $begin).' sec';
}

// returns a set of name properties like last name, first or short name, either from the users or contacts table
// FIXME: parameters with default arguments should be on the right side of any non-default arguments!
function slookup($table = 'users', $values = 'nachname,vorname', $inputfield = 'ID', $value, $int = '0') {
    $new_val = array();
    foreach(explode(',', $values) as $a_val) {
        $new_val[] = qss($a_val);
    }
    $values = implode(',', $new_val);
    if ($int == 0) $valuestring = "'$value'";
    else $valuestring = (int)$value;
    $result = db_query("SELECT ".$values."
                          FROM ".qss(DB_PREFIX.$table)."
                         WHERE ".qss($inputfield)." = ".$valuestring) or db_die();
    $row = db_fetch_row($result);
    if (count($row) < 2) return $row[0];
    else return implode(',', $row);
}

// new/extended version of slookup
function slookup5($table='users', $field='nachname,vorname', $var='ID', $value=0, $array=true, $integer_field = false) {
    $new_val = array();
    foreach(explode(',', $field) as $a_val) {
        $new_val[] = qss($a_val);
    }

    // check if the 'where' field is an integer or not
    if ($integer_field) {
        $value = (int)$value;
    }
    else {
        $value = " '$value' ";
    }

    $field = implode(',', $new_val);
    $query = "SELECT ".$field."
                FROM ".qss(DB_PREFIX.$table)."
               WHERE ".qss($var)." = ".$value;
    $res = db_query($query) or db_die();
    $row = db_fetch_row($res);
    settype($row, 'array');
    if ($array) {
        $ret = array();
        $fs = explode(',', $field);
        for ($ii=0; $ii<count($row); $ii++) {
            $ret[$fs[$ii]] = $row[$ii];
        }
        return $ret;
    }
    if (count($row) < 2) return $row[0];
    return implode(',', $row);
}


function close_window_link() {
    return '<a href="javascript:window.close()" title="'.__('Close window').'">'.__('Close window')."</a>\n";
}


// load class for sending e-mail
// and initialize the objekt "$mail" - if needed
function use_mail($init='') {
    global $mail;

    require_once(LIB_PATH.'/sendmail.inc.php');
    if ($init) {
        $mail = new send_mail( PHPR_MAIL_MODE, PHPR_MAIL_EOH, PHPR_MAIL_EOL, PHPR_MAIL_AUTH,
        PHPR_LOCAL_HOSTNAME, PHPR_SMTP_HOSTNAME, PHPR_SMTP_ACCOUNT,
        PHPR_SMTP_PASSWORD, PHPR_POP_HOSTNAME, PHPR_POP_ACCOUNT,
        PHPR_POP_PASSWORD );
        return $mail;
    }
}

/**
 * Sends an alert to sysamdin
 *
 * @param string $message message body
 * @param string $subject mail or alert subject
 * @param string $mode kind of alert (only available email)
 * @return boolean true or false if the email was sent sucessful
 */
function sysadmin_alert($message, $subject = 'PHProjekt warning!', $mode = 'email') {

    if (($mode == 'email' || $mode == 'mail') & PHPR_SYSADMIN_EMAIL <> '') {
        $mail = use_mail('1');

        // add contet information on message
        $message .= "\n \n".__("URL").": http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];

        $success = $mail->go(PHPR_SYSADMIN_EMAIL, $subject, $message, PHPR_SYSADMIN_EMAIL);

        return $success;
    }
    else return false;
}
/**
 * select medium and call associated class!
 *
 * @author Nina Schmitt
 * @param int $init initialises medium
 * @return $medium the send object  e.g mail, soap, icq
 */
function select_medium($init='') {
    //later the user can select this in settings, and the value will be retrieved from the database!
    $user_setting='mail';
    switch($user_setting){
        default:
            if($init){
                $medium=use_mail($init);
                return $medium;
            }
            else use_mail();
    }

}


/**
* sets the read flag to one users entry
* @author Albrecht Günther
* @param int $ID id of the entry
* @param string $module module to which the entry belongs
* @return string (maybe it should be an int or even boolean)
*/
function touch_record($module, $ID) {
    global $user_kurz, $user_ID, $tablename, $dbTSnull;

    // check if user has an entry for $ID
    $result = db_query("SELECT t_record from ".DB_PREFIX."db_records
                         WHERE t_record = ".(int)$ID."
                           AND t_author = ".(int)$user_ID."
                           AND t_module = '".DB_PREFIX.qss($tablename[$module])."'");
    $row = db_fetch_row($result);
    //  user has already an entry -> update entry
    if (isset($row[0])) {
        $result = db_query("UPDATE ".DB_PREFIX."db_records
                                   SET t_datum = '$dbTSnull',
                                       t_touched = 1
                                 WHERE t_record = ".(int)$ID."
                                   AND t_module = '".DB_PREFIX.qss($tablename[$module])."'
                                   AND t_author = ".(int)$user_ID) or db_die();
        return 1;
    } else {
        $result = db_query("INSERT INTO ".DB_PREFIX."db_records
                                        (t_author ,                       t_module   ,t_record,  t_datum  , t_touched)
                                 VALUES (".(int)$user_ID.", '".DB_PREFIX.qss($tablename[$module])."', ".(int)$ID.", '$dbTSnull', 1)") or db_die();
        return 0;
    }
}
// end touch record

// *****************
// history functions
// store the values of fields that have been changed in this record
function history_keep($table, $table_fields, $ID) {
    global $user_ID, $dbTSnull;

    // list of formtypes that should not be logged
    $blacklist = array('user_show', 'authorID', 'textarea_add_remark');
    include_once(LIB_PATH."/access.inc.php");
    // make the array fields
    $tmp_fields = build_array($table, $ID, 'forms');

    $table_fields = explode(',', $table_fields);
    foreach ($table_fields as $field) {
        // get the last value
        $type       = $tmp_fields[$field]['form_type'];
        $last_value = $tmp_fields[$field]['form_value'];

        // if the field is not defined in fields
        if ($last_value == '') {
            $last_value = slookup($table, $field, 'ID', $ID,'1');
        }

        // exceptions:
        // get the name of the file
        if ($type == "upload") {
            list($last_value,$tempname) = explode('|',$last_value);
        }

        // get the new value
        //if the name of the field is 'acc_read', it must be compared with the variable $acc
        if(($field == 'acc_read') || ($field == 'acc')) {
            //only if user has right to change those fields
            if ((slookup($table, 'von', 'ID', $ID,'1')) == $user_ID or PHPR_ALTER_ACC == 1){
                $new_value = assign_acc($GLOBALS['acc'], $table);
            }
            else $new_value=$last_value;
            //filemanager
            //if the name of the field is 'userfile', it must be compared with the variable userfile_name
        } else if($field == 'acc_write') {
            //only if user has right to change those fields
            if ((slookup($table, 'von', 'ID', $ID,'1')) == $user_ID or PHPR_ALTER_ACC == 1){
                $new_value = $GLOBALS[$field];
            }
            else $new_value=$last_value;
            //filemanager
            //if the name of the field is 'userfile', it must be compared with the variable userfile_name
        } else if ($field == "userfile") {
            $new_value = $GLOBALS['userfile_name'];

            //upload files
        } else if ($type == "upload") {
            $new_value = $_FILES[$field]['name'];

            // select category
        } else if ($type == "select_category") {
            if ($GLOBALS['new_category'] <> '') $new_value = $GLOBALS['new_category'];
            else                                $new_value = $GLOBALS[$field];

            // time
        } else if ($type == "time") {
            $new_value = $GLOBALS[$field."_hour"].":".$GLOBALS[$field."_minute"];

            // select_multiple
        } else if ($type == "select_multiple") {
            $new_value = implode('|',array_values($GLOBALS[$field]));

            // normal values
        } else {
            $new_value = $GLOBALS[$field];
        }

        $new_value = stripslashes($new_value);

        // no action if it's a new value or not changed
        if ( !$last_value and !$new_value ) { continue; }
        if ( $last_value   ==  $new_value ) { continue; }

        // no action if the new file is empty
        if (($field == "userfile") && (!$new_value)){ continue; }
        if (($type  ==   "upload") && (!$new_value)){ continue; }

        // check if the type it's a loggable field
        if ( strstr ($type, 'timestamp') ) { continue; }
        if ( in_array($type, $blacklist) ) { continue; }

        $last_value = addslashes($last_value);
        $new_value  = addslashes($new_value);

        db_query("INSERT INTO ".DB_PREFIX."history
                             (von     ,  h_date,   h_table ,h_field , h_record,  last_value ,  new_value)
                      VALUES (".(int)$user_ID.", '".qss($dbTSnull)."', '".qss($table)."', '".qss($field)."', ".(int)$ID."    ,'".strip_tags($last_value)."','".strip_tags($new_value)."')") or db_die();
    }
}

function history_delete($table, $ID) {
    $result = db_query("DELETE FROM ".DB_PREFIX."history
                              WHERE h_table = '$table'
                                AND h_record = ".(int)$ID) or db_die();
}

function history_show($table, $ID) {
    global $date_format_object;

    // get again the fields
    $fields = build_array($table, $ID, 'forms');

    // we will check if there are any register on history. If not we will not display the header.
    $has_values = false;

    // build field array
    $formfields1 = array();

    if (is_array($fields)) {
        foreach ($fields as $field_name => $field) {
            $formfields1[$field['form_name']] = $field_name;
        }
    }

    // add read and write access as well
    $form_fields = array_merge(array(   __('Assignment')    => 'acc',
    __('Read access')   => 'acc_read',
    __('Write access')  => 'acc_write'), $formfields1);
    $str = "<table width=100%><thead>\n";
    $str .= "<tr><th>".__('Date')."</th><th>".__('Field')."</th><th>".__('Old value')."</th><th>".
    __('New value')."</th><th>".__('Author')."</th></tr></thead><tbody>\n";

    $result = db_query("SELECT h_date, h_field, last_value, new_value, nachname, vorname
                          FROM ".DB_PREFIX."history, ".DB_PREFIX."users
                         WHERE ".DB_PREFIX."history.von = ".DB_PREFIX."users.ID
                           AND h_table = '$table'
                           AND h_record = ".(int)$ID."
                      ORDER BY h_date DESC") or db_die();

    while ($row = db_fetch_row($result)) {
        // check whether this field has a name in the form
        $form_name = array_search($row[1], $form_fields);
        $fieldname = ($form_name) ? enable_vars($form_name) : $row[1];

        // if it is a serialized string, split it into an array
        $data_old = get_correct_value($row[2], $fields[$row[1]], $ID, $row[1]);
        $data_new = get_correct_value($row[3], $fields[$row[1]], $ID, $row[1]);
        $str .= "<tr><td>".$date_format_object->convert_dbdatetime2user($row[0])."</td><td>".$fieldname."</td><td>".$data_old['value']."&nbsp;</td>";
        $str .= "<td>".$data_new['value']."&nbsp;</td><td>".$row[4].", ".$row[5]."</td></tr>\n";

        // there are registers on history
        $has_values = true;
    }
    $str .= '</tbody></table>';

    // if there aren't history we will not display the header
    if (!$has_values) {
        return '';
    }
    else {
        return $str;
    }
}

/**
 * This functions deletes the history of a sepcific file
 * @author Nina Schmitt
 * @param int $ID file ID
 * @return void
 */
function file_history_delete($ID) {
    $query = "DELETE FROM ".DB_PREFIX."datei_history
                              WHERE parent = ".(int)$ID;
    $result = db_query($query) or db_die();
}
/**
 * This function displays the file history
 *
 * @author Nina Schmitt
 * @param file ID $ID
 * @param  $date_format_object
 * @return string $str
 */
function file_history_show($ID,$date_format_object){

    $str ='';
    //get version array!
    $query = "SELECT ID, date, remark, author, version, tempname FROM ".DB_PREFIX."datei_history WHERE parent=$ID";
    $result = db_query($query);
    $file_data=array();
    while ($row = db_fetch_row($result)){
        $file_data[$row[0]]=array('date'=>$date_format_object->convert_dbdatetime2user($row[1]), 'remark'=>$row[2], 'author'=>slookup('users','nachname, vorname','ID',$row[3],'1'), 'version'=>$row[4], 'tempname'=>$row[5]);
    }
    //Alway show file history if exists
    if(count($file_data) > 0){

        $str .= "<table width=100%><thead>\n";
        $str .= "<tr><th>".__('Version')."</th><th>".__('Date')."</th><th>".__('Remark')."</th><th>".
        __('Author')."</th></tr></thead><tbody>\n";
        $csrftoken = make_csrftoken();
        $url = "../filemanager/filemanager_down.php?mode=down&amp;mode2=attachment&amp;history=true&amp;csrftoken=$csrftoken";
        foreach ($file_data as $key => $value){
            $str.= "<tr><td><a href='$url&amp;ID=$key'>".__('Version')." $value[version]</a></td>
                        <td>$value[date]</td>
                        <td>$value[remark]</td>
                        <td>$value[author]</td>
                    </tr>";
        }
        $str.='</tbody></table><br /><br />';
    }
    return $str;
}
// end history functions
// *********************


// ************* //
// header functions
function set_page_header() {
    return set_html_tag().set_head_tag().set_body_tag().'<a style="display:none" href="#content" title="'.__('go to content').'">'.__('go to content').'</a>';
}


/**
 * Create breadcrumb navigation
 * $breadcrumb = array(array('title' => .., 'url' =>..), array(..))
 *
 * @param  string $module modulename (used in __() and linkname)
 * @param  array  $breadcrumb array of tuples
 * @param  string $display value of css-display attribute (none, block, ...) defaults to "none"
 * @return string htmlstring
 */
function breadcrumb($module, $breadcrumb=array(), $display='none' ) {
    $links = array();
    $module_tuple = array('title'=> __(ucfirst($module)),
    'url'  => (count($breadcrumb)==0) ? '' : $module.'.php');
    array_unshift($breadcrumb, $module_tuple);

    // create components
    for ($i=0; $i<count($breadcrumb); $i++) {
        // create links if a URL is given
        if ( isset($breadcrumb[$i]['url'])
        && !empty($breadcrumb[$i]['url'])
        && $i < count($breadcrumb)-1 ) {
            $links[] = "<a href='".$breadcrumb[$i]['url']."'>".$breadcrumb[$i]['title']."</a>";
        }
        // else just display the title
        else {
            $links[] = $breadcrumb[$i]['title'];
        }
    }

    $string  = '<div class="breadcrumb" style="display:'.$display.';">'.__('breadcrumb start').' ';
    $string .= implode(" / ", $links);
    $string .= '</div>';
    return $string;
}


function set_html_tag() {
    global $langua, $dir_tag;
    if (empty($dir_tag)){
        $dir_tag = 'ltr';
    }
    else{
        $dir_tag = qss($dir_tag);
    }
    header("'Content-Type: text/html; charset=".LANG_CODE."'");
    $html_string  = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">'."\n";
    $html_string .= '<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="'.$langua.'" lang="'.$langua.'" dir="'.$dir_tag.'">'."\n";
    return $html_string;
}

// ************* //
function set_head_tag() {
    global $css_inc, $js_inc, $he_add, $date_format_object, $user_ID, $langua, $projekt_datepicker_lang;

    $head  = "<head>\n";
    $head .= $GLOBALS['lang_cfg'];
    $head .= '<meta name="robots" content="noindex, nofollow" />'."\n";
    $head .= "<title>PHProjekt: ".__(ucfirst($GLOBALS['module']))."</title>\n";

    // neu
    if (isset($css_inc) && is_array($css_inc) && count($css_inc) > 0) {
        foreach ($css_inc as $css) {
            $head .= $css;
        }
    }
    $date_fields = get_date_fields();
    // show this only if user is logged in
    if (!empty($user_ID)) {
        $head .= $date_format_object->get_javascript_convert_functions();
    }
    if (SID) $js_inc[] = '>var SID = "'.session_name().'='.session_id().'";';
    else     $js_inc[] = '>var SID = "";';

    // All phprojekt's Javascript
    $js_inc[] = 'src="/'.PHPR_INSTALL_DIR.'lib/javascript/phprojekt.js">';
    // 	Dojo
    if (eregi('timecard|chat',$_SERVER['SCRIPT_NAME']) || $_REQUEST['mode'] == 'forms' || $_REQUEST['addon'] == 'scrum') {

        $js_inc[]= ">var djConfig = { isDebug: false, extraLocale: ['en-us', '".$projekt_datepicker_lang[$langua]."'],
	    parseWidgets: false, searchIds: [".$date_fields."'date','t_datum','serie_bis','billorderidfrom','billfixdate']}";
        $js_inc[] = 'src="/'.PHPR_INSTALL_DIR.'lib/javascript/dojo/dojo.js">';
        $js_inc[]= '>dojo.require("dojo.widget.DropdownDatePicker"); dojo.require("dojo.widget.Button"); dojo.require("dojo.io.*"); ';
    }
    foreach ($js_inc as $js) {
        $head .= '<script type="text/javascript" '.$js."</script>\n";
    }
    if (isset($he_add) && is_array($he_add) && count($he_add) > 0) {
        foreach ($he_add as $he) {
            $head .= $he."\n";
        }
    }

    $head .= print_out_reminder_window();
    $head .= '<link type="text/css" rel="shortcut icon" href="/'.PHPR_INSTALL_DIR.'favicon.ico" />'."\n";
    $head .= "</head>\n";

    return $head;
}

function set_body_tag() {
    global $onload, $dir_tag, $mode;
    if (empty($dir_tag)){
        $dir_tag = 'ltr';
    }
    else{
        $dir_tag = qss($dir_tag);
    }
    $body = '<body';
    if (isset($onload) && is_array($onload) && count($onload) > 0) {
        $body .= ' onload="';
        foreach ($onload as $load) {
            $body .= $load;
        }
        $body .= '"' ;
    }
    $body = $body." dir='".$dir_tag."'>\n";
    $body .= "<div id='alternative_view'></div>";
    return $body."<div id=\"global-main\">\n";
}
// end header functions
// ********************

// Call the Dojo datepicker
function dojoDatepicker($field, $value) {
    global $date_format_object, $name_month, $name_day2, $first_day_week;
    global $langua, $projekt_datepicker_lang;

    $months = implode('-',$name_month);
    $days   = implode('-',$name_day2);

    if (!$date_format_object->is_db_date($value)) {
        $date_value = $date_format_object->convert_user2db($value);
    } else {
        $date_value = $value;
    }

    if (empty($date_value)) {
        $date_value = 'today';
    }

    switch($date_format_object->get_user_format()) {
        case 'dd.mm.yyyy':
            $displayFormat = "dd.MM.yyyy";
            break;
        case 'mm/dd/yyyy':
            $displayFormat = "MM/dd/yyyy";
            break;
        case 'yyyy-mm-dd':
            $displayFormat = "yyyy-MM-dd";
            break;
    }

    if (!isset($first_day_week)) {
        $first_day_week = 1;
    } else {
        $first_day_week = intval($first_day_week);
    }

    return 'inputName="'.$field.'" dojoType="dropdowndatepicker" value="'.$date_value.'" lang="'.$projekt_datepicker_lang[$langua].'" displayFormat="'.$displayFormat.'" saveFormat="yyyy-MM-dd" widgetId="picker_'.$field.'" weekStartsOn="'.$first_day_week.'" ';
}

// supply a random string, mostly used for a new filename
function rnd_string($length=12) {
    srand((double)microtime()*1000000);
    $char = '123456789abcdefghijkmnopqrstuvwxyzABCDEFGHIJKLMANOPQRSTUVWXYZ';
    $rnd_string = '';
    while (strlen($rnd_string) < $length) {
        $rnd_string .= substr($char, (rand()%(strlen($char))), 1);
    }
    return $rnd_string;
}

// crypt string
function encrypt($password, $saltstring) {
    $salt = substr($saltstring, 0, 2);
    $enc_pw = crypt($password, $salt);
    return $enc_pw;
}

/**
* function which prints page number and navigation parts
* @author Nina Schmitt
* @return string outputlist
*/
function show_page($page_module=null) {
    global $perpage, $liste, $ID, $fID, $module, $last, $getstring;
    if (!$page_module) $page_module = $module;
    if(strstr($getstring, 'addon=')){
        // We need $getstring to contain something like "addon=Protokoll" or similar
        // so that we do not need to insert every addon here;
        // to prevent tampering, we just retrieve "addon=xxx" from $getstring
        if(preg_match('°addon=[a-zA-Z_]*°i',$getstring,$addonmatch)) {
            $link="addon.php?".$addonmatch[0]."&";
        } else {
            $link="addon.php?";
        }
    }
    else {
        $link= $page_module.".php?";
    }
    $page = $_SESSION['page'][$page_module];
    $page_n = $page + 1;
    $page_p = $page - 1;
    $page_last = floor($last/$perpage) -1;
    $bis = ($page*$perpage) + $perpage;
    if ($bis>$last) $bis = $last;
    if (($last%$perpage)>0) $page_last++;
    $outputlist  = __('Count').": ";
    $outputlist .= 1+($page*$perpage);
    $outputlist .= ' - ';
    $outputlist .= "$bis  ".__('from')." ".$last;
    $outputlist .= '<span class="p2">&nbsp;';
    if ($page) {
        $outputlist.= "<a href='".$link."mode=view&amp;tree_mode=$tree_mode&amp;page_module=$page_module&amp;page_change=0&amp;ID=$ID&amp;fID=$fID&'$sid> |< ".__('Begin')."</a>
         <a href='".$link."mode=view&amp;tree_mode=$tree_mode&amp;page_change=$page_p&amp;ID=$ID&amp;fID=$fID'$sid> << ".__('back')."</a>";
    }
    if ($perpage < $last) {
        $outputlist .= ' | ';
        if($page>5)$outputlist .= ' ... ';
        for ($i=0; $i*$perpage<$last;$i++) {
            $i1 = $i + 1;
            if ($i==$page) $outputlist .= "<b> $i1 </b>";
            else if(($i>=$page-5 and $i<=$page+5)or ($i<11 and $page<6)or ($i>$page_last-11 and $page>($page_last-5))){
                $outputlist .= " <a class='und' href='".$link."mode=view&amp;page_module=$page_module&amp;tree_mode=$tree_mode&amp;page_change=$i&amp;ID=$ID&amp;fID=$fID'$sid>$i1</a> ";
            }
        }
        if($page_last>$page+5)$outputlist .= ' ... ';
        $outputlist .= ' | ';

    }

    if (count($liste) > $page_n*$perpage) {
        $outputlist .= "<a href='".$link."mode=view&amp;tree_mode=$tree_mode&amp;ID=$ID&amp;fID=$fID&amp;page_module=$page_module&amp;page_change=$page_n&amp;'$sid>".__('Next')." >></a> &nbsp;";
        $outputlist .= "<a href='".$link."mode=view&amp;tree_mode=$tree_mode&amp;ID=$ID&amp;fID=$fID&amp;page_module=$page_module&amp;page_change=$page_last&amp;'$sid>".__('End')." >| </a>  ";
    }
    $outputlist .= '</span>';
    return $outputlist;
}


function make_list($result) {
    global $perpage, $max, $last,$module;

    $liste = array();
    while ($row = db_fetch_row($result)) {
        $liste[] = $row[0];
    }
    //reset page if necessary
    if (!isset($_SESSION['page'][$module]) || (($_SESSION['page'][$module]+1)*$perpage) > count($liste)+$perpage) {
        $_SESSION['page'][$module]=0;
    }
    if ((($_SESSION['page'][$module]+1)*$perpage) > count($liste)+$perpage) $max = count($liste);
    else $max = ($_SESSION['page'][$module]+1)*$perpage;
    $last = count($liste);
    return $liste;
}

/**
* function to add messages to message_stack
* @author Nina Schmitt / Alex Haslberger
* @param string $message message to add
* @param string $module module to which the message belongs
* @param string $kat category of message (notice,warning,error)
*/
function message_stack_in($message, $module, $kat) {
    settype($_SESSION['message_stack'][$module], 'array');
    $_SESSION['message_stack'][$module][] = array($kat, $message);
}

/**
* function to return all messages of message_stack belonging to a specific module
* @author Nina Schmitt / Alex Haslberger
* @param string $module which modules messages should be returned
* @return string $out
*/
function message_stack_out($module) {
    if (!isset($_SESSION['message_stack'][$module]) ||
    !is_array($_SESSION['message_stack'][$module])) {
        return ''; // no module in message_stack -> return emtpy string
    }
    $out = array();
    foreach ($_SESSION['message_stack'][$module] as $data) {
        $out[] = "<span class='".$data[0]."'>".__('Module')." \"".$module.":\" ".$data[1]."</span>";
    }
    $out = implode('<br />', $out);
    if(count($_SESSION['message_stack'][$module]) > 1){
        $out = '<br />'.$out;
    }
    unset($_SESSION['message_stack'][$module]);
    return $out;
}

/**
 * function to return all messages of all modules in message_stack
 *
 * @author Alex Haslberger
 * @return string $out
 */
function message_stack_out_all() {
    if (!isset($_SESSION['message_stack']) ||
    !is_array($_SESSION['message_stack'])) {
        return ''; // no message_stack -> return empty string
    }
    $out = '';
    foreach ($_SESSION['message_stack'] as $modname => $data) {
        $out .= message_stack_out($modname);
    }
    return $out;
}

/**
 * check if the message stack is empty
 *
 * @return boolean
 */
function message_stack_is_empty() {
    if (isset($_SESSION['message_stack']) &&
    is_array($_SESSION['message_stack']) &&
    count($_SESSION['message_stack'])) {
        return false;
    }
    return true;
}

/**
 * function to generate help links
 *
 * @author Alex Haslberger
 * @param string $topic topic identifier
 * @return string link
 */
function get_helplink($topic='onlinemanual') {
    global $langua, $helplink_map, $translated_helps;
    $langua1 = in_array($langua, $translated_helps) ? $langua : '';
    $pfx = $langua1 ? '-'.$langua1 : '';
    if($langua=='en')$pfx='';
    return 'http://wiki'.$pfx.'.phprojekt.com/index.php/'.$helplink_map[$topic];
}

/**
 * get help button
 *
 * @author Alex Haslberger
 * @param string $topic topic
 * @return string
 */
function get_help_button($topic, $style='button') {
    switch ($style) {
        case 'tab':
            return '<a class="calendar_top_area_tabs_inactive" href="../index.php?redirect=help&amp;link='.$topic.'" target="_blank">?</a>';
        default:
            return '<a href="../index.php?redirect=help&amp;link='.$topic.'" target="_blank" class="navbutton navbutton_inactive">?</a>';
    }
}

/**
 * get go button
 *
 * @author Alex Haslberger
 * @param  string $class css class
 * @return string
 */
function get_go_button($class='button', $type='button', $name='',$value='') {
    if ($name) $name = ' name="'.$name.'"';
    if ($type == 'button') {
        if($value=='')$value='&#187;';
        return '<input type="submit" class="'.$class.'"'.$name.' value="'.$value.'" />';
    }
    else if($type == 'image') {
        return '<input class="'.$class.'" type="submit"'.$name.' value="" />';
    }
    return'';
}

/**
 * get go button with name parameter
 *
 * @param  string $name html name parameter
 * @return string
 */
function get_go_button_with_name($name) {
    return get_go_button('button', 'button', $name);
}

/**
 * get host path
 *
 * @author Alex Haslberger
 * @return string
 */
function get_host_path() {
    return PHPR_HOST_PATH;
}

/**
 * just a little debug helper
 *
 * @author Alex Haslberger
 * @param array data array
 * @return void
 */
function printr($array) {
    echo '<pre>';
    print_r($array);
    echo '</pre>';
}

/**
 * return filter execute bar
 *
 * @author Alex Haslberger
 * @param string $help_topic topic identifier for help link
 * @param boolean $show_nav_filter nav filters visible?
 * @param array $add_paras additional parameters array
 * @return string $res html code for filter execute bar
 */
function get_filter_execute_bar($help_topic='', $show_nav_filter=true, $add_paras=array()) {
    global $module;
    $fields = build_array($module,'','show');
    $hiddenfields = array("<input type='hidden' name='mode' value='view' />");
    if (SID) $hiddenfields[] = "<input type='hidden' name='".session_name()."' value='".session_id()."' />";
    // add some more hidden fields
    if (isset($add_paras['hidden'])) {
        foreach ($add_paras['hidden'] as $name=>$value) {
            $hiddenfields[] = "<input type='hidden' name='$name' value='".xss($value)."' />";
        }
    }
    $res = '
        <div class="filter_execute_bar">
        <form action="'.xss($_SERVER['SCRIPT_NAME']).'" method="post">';
    $res .= implode("\n", $hiddenfields)."\n";
    if ($show_nav_filter) {
        $hiddenfields[] = "<input type='hidden' name='module' value='".xss($module)."' />";
        $hiddenfields[] = "<input type='hidden' name='nav' value='".xss($module)."' />";
        $res .= nav_filter($fields).get_go_button();
        // Add reminder button only for the link module
        if ($module == "links") {
            $res .= "\n\n".get_go_button('button','button','reminder',__('Reminder'))."\n";
        }
        $filter = get_filters($module);
        if ($filter) {
            $res .= '
            </form>
            <form action="../lib/dbman_filter_pop.php" method="get">';
            $res .= implode("\n", $hiddenfields).__('Load filter').'
            <select name="use">
            <option value=""></option>';
            foreach ($filter as $id=>$value) {
                $res.= '<option value="'.$id.'">'.strip_tags($value)."</option>\n";
            }
            $res .= '</select>
            '.get_buttons(array(array('type' => 'submit', 'active' => false, 'name' => '', 'value' => __('use'))));
        }
    }
    $res .= '</form>';
    $res .= '</div>';
    return $res;
}

/**
 * return filter edit bar
 *
 * @author Alex Haslberger
 * @param boolean $show_filters filters visible?
 * @return string $ret html code for filter edit bar
 */
function get_filter_edit_bar($show_filters=true, $link='',$show_manage_filters=true, $local_module=null) {
    global $module, $tablename;
    if (!$local_module) $local_module = $module;
    if (!$show_filters) return '';
    // define direction
    if ($_SESSION['f_sort'][$local_module]['direction'] <> '') {
        $dir = ($_SESSION['f_sort'][$local_module]['direction'] == 'ASC') ? __('ascending') : __('descending');
    }
    else $dir = '';
    $resf      = db_query("SELECT form_name
                             FROM ".DB_PREFIX."db_manager
                            WHERE db_table = '$tablename[$local_module]'
                              AND db_name  = '".$_SESSION['f_sort'][$local_module]['sort']."'") or db_die();
    $resfrow   = db_fetch_row($resf);
    $form_name = enable_vars($resfrow[0]);

    $ret = '
    <div class="filter_edit_bar">
        '.display_filters($local_module, $link);
    if($show_manage_filters==true) {
        $ret .= display_manage_filters($local_module);
    }
    $ret .= __('Sorted by').': '.$form_name.'&nbsp;&nbsp;('.$dir.')
    </div>
    ';
    return $ret;
}

/**
 * return filter top status bar
 *
 * @author Alex Haslberger
 * @return string $ret html code for filter top status bar
 */
function get_status_bar() {
    if (message_stack_is_empty()) return '';
    $ret = '
    <div class="status_bar">
        <span class="status_bar">
            '.__('Status').':&nbsp;'.message_stack_out_all().'
        </span>
    </div>
';
    return $ret;
}


/**
 * return top page navigation bar
 *
 * @author Alex Haslberger
 * @return string $ret html code for top page navigation bar
 */
function get_top_page_navigation_bar($page_module=null) {
    return get_page_navigation_bar('unten', __('down'),$page_module);
}

/**
 * return bottom page navigation bar
 *
 * @author Alex Haslberger
 * @return string (html code for bottom page navigation bar)
 */
function get_bottom_page_navigation_bar($page_module=null) {
    return get_page_navigation_bar('oben', __('up'),$page_module);
}

/**
 * return page navigation bar
 *
 * @author Alex Haslberger
 * @param string $anker_link
 * @param string $anker_text
 * @return string $ret html code for page navigation bar
 */
function get_page_navigation_bar($anker_link, $anker_text,$page_module=null) {
    $ret = '
    <a name="'.($anker_link == 'oben' ? 'unten' : 'oben').'"></a>
    <div class="result_bar_'.($anker_link == 'oben' ? 'bottom' : 'top').'">
        '.show_page($page_module).'
    </div>
    ';
    return $ret;
}

/**
 * return filter bottom status bar
 *
 * @author Alex Haslberger
 * @param string $help_topic topic identifier for help link
 * @return string $res html code for filter bottom status bar
 */
function get_all_filter_bars($help_topic, $result_rows,$add_paras=array(),$link='',$show_manage_filters=true,$local_module=null){
    global $module;
    if (!$local_module) $local_module = $module;
    $res  = '<div id="bars">';
    $res .= get_filter_execute_bar($help_topic,true,$add_paras);
    $res .= get_filter_edit_bar(true,$link,$show_manage_filters,$local_module);
    $res .= get_status_bar();
    $res .= get_top_page_navigation_bar($local_module);
    $res .= '</div>';
    $res .= $result_rows;
    $res .= get_bottom_page_navigation_bar($local_module);
    return $res;
}

// do the reminder stuff
function print_out_reminder_window() {
    global $user_ID, $sid, $settings;

    $ret = '';
    $rem = isset($settings['reminder']) ? $settings['reminder'] : PHPR_REMINDER;
    if ($rem > 0 && $user_ID > 0 && !isset($_SESSION['show_reminder_window'])) {
        if (eregi("mac", $_SERVER['HTTP_USER_AGENT'])) {
            // width and height for macs ...
            $width  = 200;
            $height = 80;
        }
        else {
            // ... and for the rest!
            $width  = 170;
            $height = 50;
        }
        $ret = '
<script type="text/javascript">
<!--
WRem = window.open("'.PATH_PRE.'calendar/reminder.php?'.$sid.'","phproremind","toolbar=0,location=0,directories=0,status=0,menubar=0,scrollbars=0,left=400,top=300,width='.$width.',height='.$height.'");
if (WRem) {
    WRem.focus();
}
//-->
</script>
';
        $_SESSION['show_reminder_window'] = true;
    }
    return $ret;
}


/**
* get the mime tipe of a file
* @author Alex Haslberger
* @param string $filename filename of the file
* @return string $mt mimetype
*/
function get_mime_type($filename){
    if(function_exists('mime_content_type')){
        return mime_content_type($filename);
    }
    else{
        // try to guess the mimetype
        $filetype = explode('.', $filename);
        $filetype = $filetype[count($filetype) - 1];
        switch($filetype){
            case 'pdf':
                $mt = 'application/pdf';
                break;
            case 'gif':
                $mt = 'image/gif';
                break;
            case 'htm':
            case 'html':
                $mt = 'text/html';
                break;
            case 'jpg':
            case 'jpeg':
            case 'jpe':
                $mt = 'image/jpeg';
                break;
            case 'mpeg':
            case 'mpg':
            case 'mpe':
                $mt = 'video/mpeg';
                break;
            case 'mov':
            case 'qt':
                $mt = 'video/quicktime';
                break;
            case 'rtf':
                $mt = 'application/rtf';
                break;
            case 'movie':
                $mt = 'video/x-sgi-movie';
                break;
            case 'txt':
                $mt = 'text/plain';
                break;
            case 'avi':
                $mt = 'video/ms-video';
                break;
            case 'wav':
                $mt = 'audio/x-wav';
                break;
            case 'zip':
                $mt = 'application/zip';
                break;
            case 'zip':
                $mt = 'application/zip';
                break;
            case 'doc':
                $mt = 'application/msword';
                break;
            case 'xls':
                $mt = 'application/vnd.ms-excel';
                break;
            case 'js':
                $mt = 'application/x-javascript';
                break;
            case 'swf':
                $mt = 'application/x-shockwave-flash';
                break;
            case 'mp3':
                $mt = 'audio/mpeg';
                break;
            case 'bmp':
                $mt = 'image/bmp';
                break;
            case 'png':
                $mt = 'image/png';
                break;
            case 'css':
                $mt = 'text/css';
                break;
            case 'ppt':
                $mt = 'application/vnd.ms-powerpoint';
                break;
            default:
                $mt = 'application/octet-stream'; // default mime type
                break;
        }
        return $mt;
    }
}
/**
* redirects user if GET['redirect'] isset
* @author Alex Haslberger
* @return void
*/
function redirect($url = ''){
    if ($url <> '') {
        header('Location: '.xss($url));
    }
    elseif(isset($_GET['redirect'])){
        switch ($_GET['redirect']) {
            // help links redirect
            case 'help':
                header('Location: '.get_helplink(xss($_GET['link'])));
                exit;
        }
    }
}
/**
* get tabs area at top of content
* @author Alex Haslberger
* @param array $tabs array containing all tabs data
* @return string $html
*/
function get_tabs_area($tabs = array()) {
    // left tabs
    $left_tabs = '';
    foreach ($tabs as $tab) {
        if ($tab['position'] != 'left') {
            continue;
        }
        $left_tabs .= "\n\t".get_single_tab($tab);
    }
    // right tabs
    $right_tabs = '<div id="global-panels-top-right"><ul>';
    foreach ($tabs as $tab) {
        if ($tab['position'] != 'right') {
            continue;
        }
        $tab['type'] = 'link';
        $right_tabs .= "\n\t".get_single_button($tab);
    }
    $right_tabs .= "\n\t".get_single_button(array('type' => 'link', 'href' => '../index.php?redirect=help&amp;link='.$GLOBALS['module'], 'active' => false, 'text' => __('Help'), 'id' => 'help', 'target' => '_blank'));
    $right_tabs .= "</ul></div>";
    $html = '
    <!-- begin tab_selection -->
    <div id="global-panels-top">
    <ul>
        <li>'.__(ucfirst($GLOBALS['module'])).'</li>'.
    $left_tabs."\t</ul></div>\t".$right_tabs.'

    <!-- end tab_selection -->';
    return $html;
}
/**
* get single tab
* @author Alex Haslberger
* @param array $tab array containing all tab data
* @return string $html
*/
function get_single_tab($tab){
    $html  = '<li';
    if($tab['active']){
        $html .= ' class="active"';
    }
    $html .= '><a ';
    $html .= 'href="'.$tab['href'].'" ';
    $html .= 'id="'.$tab['id'].'" ';
    $html .= 'target="'.$tab['target'].'" ';
    $html .= 'title="'.$tab['text'].'" ';
    $html .= '>';
    $html .= $tab['text'];
    $html .= '</a></li>';
    return $html;
}
/**
* get single button
* @author Alex Haslberger
* @param array $tab array containing all tab data
* @return string $html
*/
function get_single_button($tab){
    switch($tab['type']){
        case 'link':
            $title = isset($tab['title']) ? $tab['title'] : $tab['text'];
            $html  = '<a ';
            $html .= 'href="'.$tab['href'].'" ';
            $html .= 'id="'.$tab['id'].'" ';
            $html .= 'target="'.$tab['target'].'" ';
            $html .= 'title="'.$title.'"';
            $html .= '>';
            $html .= $tab['text'];
            $html .= '</a>';
            if($tab['active']){
                $html = '<li class="active">'.$html.'</li>';
            }
            else{
                $html = '<li>'.$html.'</li>';
            }
            break;
        case 'button':
            // not yet implemented
            break;
    }
    return $html;
}

/**
* get filter and archiv flags
* @author Nina Schnmitt
* @param array $fields_required array with special flags which should be displayed
* @param array $checked array with current values of the special flags
* @param string $onchange in case an onchange event is needed
* @return string $output
*/
function get_special_flags($fields_required=array(),$checked=array(), $onchange='') {
    if(count($fields_required)==0){
        $fields_required= array(__('Consider current set filters')=>'use_filters',
        __('Exclude archived elements')=>'exclude_archived',
        __('Exclude read elements')=>'exclude_read',
        __('Export column titles as first row')=>'export_titles');
    }
    $output='';

    $disp_field = isset($display_field) && isset($checked[$display_field]) ? $checked[$display_field] : '';
    foreach($fields_required as $label_name => $display_field){
        if ($disp_field == "on"){
            $is_checked=" checked='checked'";
        }
        else{
            $is_checked="";
        }
        $output.=display_special_flag($display_field,$label_name,$is_checked,$onchange);
        $output.= "<br />";
    }
    return $output;

}

/**
* display filter and archiv flags
* @author Nina Schnmitt
* @param string $display_field name of checkbox
* @param string $label_name label for checkbox
* @param strings $is_checked defines if checkbox should be checked
* @param string $onchange in case an onchange event is needed
* @return string $output
*/
function display_special_flag($display_field,$label_name,$is_checked='',$onchange='') {
    $output="<input type='checkbox'$is_checked name='$display_field' $onchange id='$display_field' />
             <label for='$display_field'>$label_name</label>";
    return $output;
}
/**
* get buttons area at top of content
* @author Alex Haslberger
* @param array $buttons array containing all buttons data
* @param string $oncontextmenu javascript code
* @return string $html
*/
function get_buttons_area($buttons, $oncontextmenu = '') {
    $html = "\n<div class=\"module_bar_top\"";
    if ($oncontextmenu) {
        $html .= ' '.$oncontextmenu;
    }
    $html .= ">\n".get_buttons($buttons, 'span')."\n</div>\n";
    return $html;
}

/**
* get buttons area at bottom of content
* @author Alex Haslberger
* @param array $buttons array containing all buttons data
* @return string $html
*/
function get_bottom_buttons_area($buttons) {
    $html = '
    <div class="buttons_bottom" style="margin-top:5px;">
    '.get_buttons($buttons).'
    </div>
    ';
    return $html;
}

/**
* get buttons
* @author Alex Haslberger
* @param array $buttons array containing all buttons data
* @return string $html
*/
function get_buttons($buttons, $span='') {
    $html = '';
    foreach($buttons as $button){
        switch($button['type']){
            case 'submit':
            case 'button':
                $class = '';
                if(isset($button['stopwatch'])){
                    $class = 'button_link_inactive';
                }
                elseif(isset($button['active'])){
                    $class = $button['active'] ? 'button' : 'button';
                }
                elseif(!isset($button['active'])){
                    $class = 'button';
                }
                $html .= '<input type="'.$button['type'].'" name="'.$button['name'].'" value="'.$button['value'].'" class="'.$class.'"';
                if(isset($button['onclick'])){
                    $html .= ' onclick="'.$button['onclick'].'"';
                }
                if(isset($button['extrahtml'])){
                    $html .= ' '.ereg_replace('&quot;','"',xss($button['extrahtml']));
                }
                $html .= ' /> ';
                break;
            case 'hidden':
                $html .= '<input type="hidden" name="'.$button['name'].'" value="'.xss($button['value']).'" /> ';
                break;
            case 'link':
                $class = '';
                if(isset($button['active'])){
                    $class = $button['active'] ? 'button_link_active' : 'button_link_inactive';
                }
                elseif(isset($button['stopwatch'])){
                    $class = 'button_link_inactive';
                }
                $html .= '<a href="'.$button['href'].'" class="'.$class.'"';
                if(isset($button['onclick'])){
                    $html .= ' onclick="'.$button['onclick'].'"';
                }
                $html.='>'.$button['text'].'</a> ';
                break;
            case 'form_start':
                if($span)$html.='</span>';
                $html .= '<form style="display:inline" action="'.xss($_SERVER['SCRIPT_NAME']).'" method="post"  ';
                if(isset($button['onsubmit'])){
                    $html .= ' onsubmit="'.$button['onsubmit'].'"';
                }
                if(isset($button['enctype'])){
                    $html .= ' enctype="'.$button['enctype'].'"';
                }
                if(isset($button['name'])){
                    $html .= ' name="'.$button['name'].'"';
                }
                $html .= '> ';
                if($span)$html.='<span class="nav_area">';
                $html .= hidden_fields($button['hidden']);
                break;
            case 'form_end':
                if($span)$html.='</span> ';
                $html .= '</form>';
                if($span)$html.='<span class="nav_area">';
                break;
            case 'select':
                $html .= '<select name="'.$button['name'].'"';
                if(isset($button['onchange'])){
                    $html .= ' onchange="'.$button['onchange'].'"';
                }
                $html .= '>';
                foreach($button['options'] as $value => $text) {
                    $html .= '<option value="'.xss($value).'"';
                    if ($button['selected'] == $value){
                        $html .= ' selected="selected"';
                    }
                    $html .= '>'.$text.'</option>';
                }
                $html .= '</select> ';
                break;
            case 'separator':
                $html .= '<span class="strich">&nbsp;</span> ';
                break;
            case 'text':
                $html .= $button['text'];
                break;
            default:
                $html .= $button['type'];
                break;
        }
    }
    return $html;
}
/**
* get message "no hits found"
* @author Alex Haslberger
* @param string $module module name
* @return string $html
*/
function get_no_hits_found_message($module){
    $html = '
    <h2>'.__($module).'</h2>
    <p class="noEntries">'. __('there were no hits found.').'</p>
    ';
    return $html;
}
/**
* get header for box
* @author Alex Haslberger
* @param string $headline headline for box header
* @param string $anker_name name of the anker
* @return string $html
*/
function get_box_header($headline, $anker_name){
    $html = '
    <a name="'.$anker_name.'" id="'.$anker_name.'"></a>
    <legend>'.$headline.'</legend>';
    return $html;
}
/**
* get form body
* @author Alex Haslberger
* @param string $form_content all content of form (fields ...)
* @return string $html
*/
function get_form_body($form_content){
    $html = '
    <div class="formbody">
    <fieldset style="margin:0">
    <legend></legend>
    '.$form_content.'
    </fieldset></div>';
    return $html;
}
/**
* get form content
* @param array $form_fields data for all form fields
* @return string $html
*/
function get_form_content($form_fields){
    $html = '';
    if(is_array($form_fields))foreach($form_fields as $field){
        $id = '';
        // strip not allowed chars from id
        if(!isset($field['id']) && isset($field['name']))$id = preg_replace('/\[\]/', '', $field['name']);
        elseif(isset($field['id'])) $id=$field['id'];
        $label_class = isset($field['label_class']) ? $field['label_class'] : 'label_block';
        if (isset($field['label'])) {
            $html .= '<label class="'.$label_class.'"';
            if(strlen($id) > 0){
                $html .= ' for="'.$id.'"';
            }
            $html .= '>'.$field['label'].'</label>';
        }
        $styles = array();
        switch ($field['type']) {
            // string
            case 'string':
                $html .= $field['text'];
                break;
                // select
            case 'select':
                $html .= '<select id="'.$id.'" name="'.$field['name'].'"';
                if(isset($field['onchange'])){
                    $html .= ' onchange="'.$field['onchange'].'"';
                }
                if(isset($field['multiple']) && ($field['multiple'] == true || is_int($field['multiple']))) {
                    $multiple = (is_int($field['multiple']) ? $field['multiple'] : '5');
                    $html .= ' multiple="multiple" size="'.$multiple.'"';
                }
                if(isset($field['readonly']) && $field['readonly'] == true) {
                    $html .=  "disabled='disabled'";
                }
                $html .= ">\n";
                // check if no blank option is set
                if (!isset($field['no_blank_option']) || $field['no_blank_option'] <> true) {
                    $html .= "<option value=''></option>\n";
                }
                foreach($field['options'] as $option){
                    $html .= '<option value="'.$option['value'].'"';
                    if(isset($option['selected']) && $option['selected'] == true) {
                        $html .= ' selected="selected"';
                    }
                    $html .= '>'.$option['text'].'</option>';
                }
                $html .= '</select>';
                if(isset($field['text_after'])){
                    $html .= $field['text_after'];
                }
                if ((!isset($field['no_break'])) || $field['no_break'] <> true) {
                    $html .= '<br />';
                }
                break;
                // text
            case 'text':
                $html .= '<input id="'.$id.'" type="text" name="'.$field['name'].'" value="';
                if(isset($field['value'])){
                    $html .= xss($field['value']);
                }
                $html .= '"';
                if(isset($field['readonly']) && $field['readonly'] == true){
                    $html .=  "readonly='readonly'";
                }
                if(isset($field['onblur'])){
                    $html .= ' onblur="'.$field['onblur'].'"';
                }
                if(isset($field['class'])){
                    $html .= ' class="'.$field['class'].'"';
                }
                $html .= '/>';
                if(isset($field['label_right'])){
                    $html .= $field['label_right'];
                }
                if ((!isset($field['no_break'])) || $field['no_break'] <> true) {
                    $html .= '<br />';
                }
                break;
                // hidden
            case 'hidden':
                $html .= '<input id="'.$id.'" type="hidden" name="'.$field['name'].'" value="';
                if(isset($field['value'])){
                    $html .= xss($field['value']);
                }
                $html .= '"/>';
                break;
                // password
            case 'password':
                $html .= '<input id="'.$id.'" type="password" name="'.$field['name'].'" class="options" value="';
                if(isset($field['value'])){
                    $html .= xss($field['value']);
                }
                $html .= '"';
                if(isset($field['readonly']) && $field['readonly'] == true){
                    $html .= ' readonly style="background-color:'.PHPR_BGCOLOR3.';"';
                }
                $html .= '/>';
                if ((!isset($field['no_break'])) || $field['no_break'] <> true) {
                    $html .= '<br />';
                }
                break;
            case 'password_confirm':
                $html .= '<input id="'.$id.'" type="password" name="'.$field['name'].'" class="options" value="';
                if(isset($field['value'])){
                    $html .= xss($field['value']);
                }
                $html .= '"';
                if(isset($field['readonly']) && $field['readonly'] == true){
                    $html .= ' readonly style="background-color:'.PHPR_BGCOLOR3.';"';
                }
                $html .= '/>';

                $html .= '<br style="clear:both"/>';
                $html .= '<label class="'.$label_class.'"';
                $html .= '>'.__('Confirm Password').'</label>';
                $html .= '<input id="'.$id.'_confirm" type="password" name="'.$field['name'].'_confirm" class="options" value="';
                if(isset($field['value'])){
                    $html .= xss($field['value']);
                }
                $html .= '"';
                if(isset($field['readonly']) && $field['readonly'] == true){
                    $html .= ' readonly style="background-color:'.PHPR_BGCOLOR3.';"';
                }
                $html .= '/>';
                if ((!isset($field['no_break'])) || $field['no_break'] <> true) {
                    $html .= '<br />';
                }

                break;
                // textarea
            case 'textarea':
                $html .= '<textarea id="'.$id.'" name="'.$field['name'].'"';
                if(isset($field['style'])){
                    $html .= ' style="'.$field['style'].'"';
                }
                if(isset($field['class'])){
                    $html .= ' class="'.$field['class'].'"';
                }
                $html .= '>';
                if(isset($field['value'])){
                    $html .= $field['value'];
                }
                $html .= '</textarea><br />';
                break;
                //textarea_answ
                // checkbox
            case 'checkbox':
                $html .= '<input id="'.$id.'" type="checkbox" name="'.$field['name'].'"';
                if(isset($field['readonly']) && $field['readonly'] == true){
                    $html .= ' disabled style="width:15px; background-color:'.PHPR_BGCOLOR3.'"';
                }
                else $html.='style="width:15px;"';
                if(isset($field['checked']) && $field['checked'] == true){
                    $html .= ' checked ="'.$field['checked'].'"';
                }
                $html.=' />';
                if(isset($field['label_right'])){
                    $html .= $field['label_right'];
                }
                if ((!isset($field['no_break'])) || $field['no_break'] <> true) {
                    $html .= '<br />';
                }
                break;
                // file
            case 'file':
                $html .= '<input id="'.$id.'" type="file" name="'.$field['name'].'" class="options" />';

                if(isset($field['label_right'])){
                    $html .= $field['label_right'];
                }
                break;
                // parsed html
            case 'parsed_html':
                $html .= $field['html'];
                break;
        }
        unset($styles);
    }
    return $html;
}
/**
* get the css path
*
* @return string
*/
function get_css_path() {
    global $skin;
    if (isset($skin) && $skin != '') {
        $ret = $skin;
    }
    else if (defined(PHPR_SKIN) && PHPR_SKIN != '') {
        $ret = PHPR_SKIN;
    }
    else {
        $ret = 'default';
    }
    $ret = '/'.PHPR_INSTALL_DIR.'/layout/'.$ret.'/';
    $ret = str_replace('//', '/', $ret);
    return $ret;
}

/**
* returns names of commonb db fields like ID or parent
* @author Albrecht Guenther
* @param string module
* @return array
*/
function get_db_fieldname($module,$fieldname) {
    global $db_fieldnames;
    $ret_fieldname = isset($db_fieldnames[$module][$fieldname]) ? $db_fieldnames[$module][$fieldname] : $fieldname;
    return $ret_fieldname;
}

/**
 * get vars values from constants
 *
 * @return void
 */
function constants_to_vars() {
    $constants = array(
    'VERSION',
    'DB_TYPE',
    'DB_HOST',
    'DB_USER',
    'DB_PASS',
    'DB_NAME',
    'DB_PREFIX',
    'LOGIN',
    'PW_CHANGE',
    'PW_CRYPT',
    'GROUPS',
    'ACC_ALL_GROUPS',
    'ACC_DEFAULT',
    'ACC_WRITE_DEFAULT',
    'LDAP',
    'TIMEZONE',
    'SESSION_TIME_LIMIT',
    'MAXHITS',
    'LOGS',
    'HISTORY_LOG',
    'ERROR_REPORTING_LEVEL',
    'SUPPORT_PDF',
    'SUPPORT_HTML',
    'SUPPORT_CHART',
    'DOC_PATH',
    'ATT_PATH',
    'FILTER_MAXHITS',
    'TODO',
    'VOTUM',
    'LINKS',
    'CALENDAR',
    'EVENTS_PAR',
    'GROUPVIEWUSERHEADER',
    'MAIL_NEW_EVENT',
    'PROFILE',
    'RESSOURCEN',
    'REMINDER',
    'REMIND_FREQ',
    'SMS_REMIND_SERVICE',
    'TIMECARD',
    'CONT_USRDEF1',
    'CONT_USRDEF2',
    'CONTACTS_PROFILES',
    'CALLTYPE',
    'NOTES',
    'TODO_OPTION_ACCEPTED',
    'QUICKMAIL',
    'FAXPATH',
    'MAIL_SEND_ARG',
    'MAIL_EOL',
    'MAIL_EOH',
    'MAIL_MODE',
    'MAIL_AUTH',
    'SMTP_HOSTNAME',
    'LOCAL_HOSTNAME',
    'POP_ACCOUNT',
    'POP_PASSWORD',
    'POP_HOSTNAME',
    'SMTP_ACCOUNT',
    'SMTP_PASSWORD',
    'DAT_REL',
    'DAT_CRYPT',
    'FILEMANAGER_NOTIFY',
    'FORUM',
    'FORUM_TREE_OPEN',
    'FORUM_NOTIFY',
    'RTS',
    'RTS_MAIL',
    'RTS_DUEDATE',
    'RTS_CUST_ACC',
    'CHAT',
    'CHAT_ALIVE_FREQ',
    'CHAT_LAST_HOURS',
    'SKIN',
    'DEFAULT_SIZE',
    'CUR_SYMBOL',
    'BGCOLOR1',
    'BGCOLOR2',
    'BGCOLOR3',
    'BGCOLOR_MARK',
    'BGCOLOR_HILI',
    'LOGO',
    'HP_URL',
    'TR_HOVER'
    );

    foreach ($constants as $constant) {
        if (defined('PHPR_'.$constant)) {
            $GLOBALS[strtolower($constant)] = constant('PHPR_'.$constant);
        }
    }
    // some vars have been renamed to english
    global $login_kurz, $lesezeichen, $tagesanfang, $tagesende, $projekte, $adressen, $dateien;
    if (defined('PHPR_LOGIN_SHORT')){
        $login_kurz  = PHPR_LOGIN_SHORT;
    }
    if (defined('PHPR_BOOKMARKS')){
        $lesezeichen = PHPR_BOOKMARKS;
    }
    if (defined('PHPR_DAY_START')){
        $tagesanfang = PHPR_DAY_START;
    }
    if (defined('PHPR_DAY_END')){
        $tagesende = PHPR_DAY_END;
    }
    if (defined('PHPR_PROJECTS')){
        $projekte = PHPR_PROJECTS;
    }
    if (defined('PHPR_CONTACTS')){
        $adressen = PHPR_CONTACTS;
    }
    if (defined('PHPR_FILEMANAGER')){
        $dateien = PHPR_FILE_PATH;
    }
}

/**
 * Returns the first day of a period as a timestamp
 *
 * @author David Soria Parra
 * @param enum(m,d,y) $type
 * @param integer $cycles
 * @return integer
 */
function periode_get_start_date($type, $cycles=0) {
    switch($type) {
        case "q":
            $quarter = ceil(date("m")/3)-$cycles-1;
            return mktime(0, 0, 0, $quarter*3+1, 1, date("Y"));
            break;
        case "m":
            return mktime(0, 0, 0, date("m")-1*$cycles, 1, date("Y"));
            break;
        case "y":
            return mktime(0, 0, 0, 1, 1, date("Y")-$cycles);
            break;
    }
}

/**
 * Returns the last day of a period as a timestamp
 *
 * @author David Soria Parra
 * @param enum(m,d,y) $type
 * @param integer $cycles
 * @return integer
 */
function periode_get_end_date($type, $cycles=0) {
    switch($type) {
        case "q":
            $quarter = ceil(date("m")/3)-$cycles;
            return mktime(0, 0, 0, $quarter*3+1, 0, date("Y"));
            break;
        case "m":
            return mktime(0, 0, 0, date("m")+1-$cycles, 0, date("Y"));
            break;
        case "y":
            return mktime(0, 0, 0, 1, 0, date("Y")-$cycles+1);
            break;
    }
}

/**
 * Parses a given selectbox value. Format: (m|q|y):[0-9]*
 * m: month, q: quarter, y: year
 *
 * Returns an array which contains the parsed values
 *
 * @author David Soria Parra
 * @param string $value
 * @return array
 */
function periode_parse_selectbox_value($value) {
    $pieces = explode(":", $value);
    return array("type"=>$pieces[0], "cycles"=>$pieces[1]);
}

/**
 * Gets the selectbox
 *
 * @author David Soria Parra / Gustavo Solt
 * @param  string name           - name of the select
 * @param  string selected_value - selected value
 * @param  string params         - extra parameters for the select
 * @return string
 */
function periode_get_date_selectbox($name, $selected_value = '', $params = '') {
    $tmp_array = array ( 'm:0' => __("current month"),
    'q:0' => __("current quarter"),
    'y:0' => __("current year"),
    'm:1' => __("last month"),
    'q:1' => __("last quarter"),
    'y:1' => __("last year"));

    $return = "<select id='$name' name='$name' ";
    if ($params != '') $return .= $params;
    $return .= " >\n";
    $return .= "<option value=''></option>\n";

    foreach($tmp_array as $value => $text) {
        $return .= "<option value='". $value ."'";
        if ($selected_value == $value) {
            $return .= " selected='selected'";
        }
        $return .= ">".$text."</option>\n";
    }
    $return .= "</select>\n";

    return $return;
}

function get_node_with_children($table, $rootID, $where="") {
    if(!empty($where)) {
        $where = " WHERE ".$where;
    }
    $result = db_query("SELECT ID, parent
                            FROM ".DB_PREFIX.$table." ".$where) or db_die();
    while($row = db_fetch_row($result)) {
        if ($row[1] > 0) $nodes[$row[1]]['children'][] = $row[0];
    }
    $ids = array();
    get_children($nodes, $nodes[$rootID], $ids);
    return $ids;
}

function get_children($nodes, $current, &$ids) {

    if(is_array($current['children'])) {
        foreach($current['children'] as $childId) {
            $ids[] = $childId;
            get_children($nodes, $nodes[$childId], $ids);
        }
    }
}
/**
* function to remove corresponding link when a record is deleted
* @author Nina Schmitt
* @param int $ID ID of the record
* @param string $module module to which the record belongs
* @return void
*/
function remove_link($ID, $module) {
    $query="DELETE FROM ".DB_PREFIX."db_records
			WHERE t_record = ".(int)$ID." and t_module = '".DB_PREFIX."$module'";
    $result = db_query($query) or db_die();
}

/**
* function to update the contact and users of one project
* @author Gustavo Solt
* @param int    $project_ID id of the project
* @param string $personen group of selected users or contacts
* @param string $type  type of personen, contact, user or project
* @param array  $roles $_POST values for roles
* @return void
*/
function update_project_personen_table($project_ID, $personen, $type, $roles = array()) {

    if ($type == "user") {
        $table = DB_PREFIX."project_users_rel";
        if(is_array($personen)) {
            // delete the entries unselected
            $result = db_query("SELECT user_ID
                                  FROM ".$table."
                                 WHERE project_ID = ".(int)$project_ID) or db_die();
            while ($row = db_fetch_row($result)) {
                if (!in_array($row[0],$personen)) {
                    db_query("DELETE FROM ".$table."
                                    WHERE project_ID = ".(int)$project_ID."
                                      AND user_ID = ".(int)$row[0]) or db_die();
                }
            }

            foreach($personen as $id => $user_ID) {
                $add_result = db_query("SELECT ID
                                          FROM ".$table."
                                         WHERE project_ID = ".(int)$project_ID."
                                           AND user_ID = ".(int)$user_ID) or db_die();
                $add_row = db_fetch_row($add_result);
                // add the new entries
                if (!is_array($add_row)) {
                    db_query("INSERT INTO ".$table."
                                            (        project_ID,          user_ID)
                                     VALUES (".(int)$project_ID.",".(int)$user_ID.")") or db_die();
                }

                // update roles
                if (isset($roles['u_'.$user_ID."_text_role"]) || isset($roles['u_'.$user_ID."_role"]) ) {
                    if($roles['u_'.$user_ID."_text_role"] != '') $role = $roles['u_'.$user_ID."_text_role"];
                    else                                         $role = $roles['u_'.$user_ID."_role"];
                    $result = db_query("UPDATE ".DB_PREFIX."project_users_rel
                                           SET role        = '".xss($role)."'
                                         WHERE project_ID = ".(int)$project_ID." AND
                                               user_ID = ".(int)$user_ID);
                }
            }
        }
    }
    else if ($type == "rts") {
        $table = DB_PREFIX."rts_users_rel";
        if(is_array($personen)) {
            // delete the entries unselected
            $result = db_query("SELECT user_ID
                                  FROM ".$table."
                                 WHERE rts_ID = ".(int)$project_ID) or db_die();
            while ($row = db_fetch_row($result)) {
                if (!in_array($row[0],$personen)) {
                    db_query("DELETE FROM ".$table."
                                    WHERE rts_ID = ".(int)$project_ID."
                                      AND user_ID = ".(int)$row[0]) or db_die();
                }
            }

            foreach($personen as $id => $user_ID) {
                $add_result = db_query("SELECT ID
                                          FROM ".$table."
                                         WHERE rts_ID = ".(int)$project_ID."
                                           AND user_ID = ".(int)$user_ID) or db_die();
                $add_row = db_fetch_row($add_result);
                // add the new entries
                if (!is_array($add_row)) {
                    db_query("INSERT INTO ".$table."
                                            (            rts_ID,     user_ID)
                                     VALUES (".(int)$project_ID.",".(int)$user_ID.")") or db_die();
                }
            }
        }
    } else if ($type == "contact") {
        $table = DB_PREFIX."project_contacts_rel";
        if(is_array($personen)) {
            // make a new array of personen
            $tmp = $personen;
            $tmp_array = array();
            foreach ($tmp as $id => $contact_ID) {
                $tmp_array[] = $contact_ID;
            }
            // delete the entries unselected
            $result = db_query("SELECT contact_ID
                                  FROM ".$table."
                                 WHERE project_ID = ".(int)$project_ID) or db_die();
            while ($row = db_fetch_row($result)) {
                if (!in_array($row[0],$tmp_array)) {
                    db_query("DELETE FROM ".$table."
                                    WHERE project_ID = ".(int)$project_ID."
                                      AND contact_ID = ".(int)$row[0]) or db_die();
                }
            }

            foreach($personen as $id => $contact_ID) {
                $add_result = db_query("SELECT ID
                                          FROM ".$table."
                                         WHERE project_ID = ".(int)$project_ID."
                                           AND contact_ID = ".(int)$contact_ID) or db_die();
                $add_row = db_fetch_row($add_result);
                // add the new entries
                if (!is_array($add_row)) {
                    db_query("INSERT INTO ".qss($table)."
                                            (        project_ID  ,        contact_ID)
                                     VALUES (".(int)$project_ID.",".(int)$contact_ID.")") or db_die();
                }

                // update roles
                if (isset($roles['c_'.$contact_ID."_text_role"]) || isset($roles['c_'.$contact_ID."_role"]) ) {
                    if($roles['c_'.$contact_ID."_text_role"] != '')  $role = $roles['c_'.$contact_ID."_text_role"];
                    else                                             $role = $roles['c_'.$contact_ID."_role"];
                    $result = db_query("UPDATE ".DB_PREFIX."project_contacts_rel
                                           SET role = '".xss($role)."'
                                         WHERE project_ID  = ".(int)$project_ID." AND
                                               contact_ID  = ".(int)$contact_ID);
                }
            }
        }
    } else {
        // fix for search project by one contact
        $ID = $project_ID;
        $table = DB_PREFIX."project_contacts_rel";
        if(is_array($personen)) {
            // make a new array of personen
            $tmp = $personen;
            $tmp_array = array();
            foreach ($tmp as $project_ID) {
                $tmp_array[] = $project_ID;
            }
            // delete the entries unselected
            $result = db_query("SELECT project_ID
                                  FROM ".$table."
                                 WHERE contact_ID = ".(int)$ID) or db_die();
            while ($row = db_fetch_row($result)) {
                if (!in_array($row[0],$tmp_array)) {
                    db_query("DELETE FROM ".$table."
                                    WHERE contact_ID = ".(int)$ID."
                                      AND project_ID = ".(int)$row[0]) or db_die();
                }
            }
            // add the new entries
            foreach($personen as $id => $project_ID) {
                $add_result = db_query("SELECT ID
                                          FROM ".$table."
                                         WHERE contact_ID = ".(int)$ID."
                                           AND project_ID = ".(int)$project_ID) or db_die();
                $add_row = db_fetch_row($add_result);
                if (!is_array($add_row)) {
                    db_query("INSERT INTO ".qss($table)."
                                            (        project_ID  ,contact_ID)
                                     VALUES (".(int)$project_ID.",".(int)$ID.")") or db_die();
                }
            }
        }
    }
}

/**
* function for make the select of roles
* @author Gustavo Solt
* @param int    $person_id id of contact, user or project
* @param string $value selected value
* @param string $table table to find the values
* @return void
*/
function make_select_roles ($person_id, $value, $table) {

    if ($table == 'users')    $prefix = 'u_';
    else                      $prefix = 'c_';

    // found value flag
    $found_flag = false;

    $roles_array = "<select name=".$prefix.$person_id."_role>\n";
    $roles_array .= "<option value=''></option>\n";
    $roles_result = db_query("SELECT DISTINCT(role) FROM ".DB_PREFIX."project_".$table."_rel
                              WHERE role <> ''") or db_die();
    while($roles_row = db_fetch_row($roles_result)) {
        if ($value == $roles_row[0]) {
            $selected = "selected='selected'";
            $found_flag = true;
        } else {
            $selected = "";
        }
        $roles_array .= "<option value='".$roles_row[0]."' ".$selected.">".$roles_row[0]."</option>\n";
    }

    // add a new entry
    if (!$found_flag && !empty($value)) {
        $roles_array .= "<option value='".$value."' selected='selected'>".$value."</option>\n";
    }

    $roles_array .= "</select>\n";
    return $roles_array;
}

/**
* check if we have a valid md5 string
* @author Giampaolo Pantò
* @param string $value md5 string
* @return boolean
*/
function is_md5($value) {
    return preg_match('/^[A-Fa-f0-9]{32}$/', $value);
}
/**
 * Checks wether user is participant of specific project
 * @author Nina Schmitt
 * @version 1.0
 * @param int $project_ID
 * @param int $user_ID
 * @return boolean
 */
function user_is_part($project_ID, $user_ID){
    $query="SELECT ID FROM ".DB_PREFIX."project_users_rel
            WHERE project_ID = ".(int)$project_ID." and user_ID = ".(int)$user_ID;
    $result = db_query($query) or db_die();
    $row=db_fetch_row($result);
    if($row[0]>0){
        return true;
    }
    return false;
}

/**
 * Create or modify a new general mail rule
 *
 * @param string $mode type of general rule (ingoing, outcoing, mail2con)
 * @param int $dir parent directory to aplly the rule
 * @param int $dir parent directory to aplly the rule
 * @return boolean true or false if rule was created sucessful
 */
function update_general_rule($mode, $dir, $user_rule = 0) {
    global $user_ID;

    if ($user_rule == 0) {
        $user_rule = $user_ID;
    }

    // first of all - delete any rule for incoming/outgoing mail for this user :-))
    $result = db_query("delete from ".DB_PREFIX."mail_rules
                         where von = ".(int)$user_rule." and
                               type like '$mode'") or db_die();
    if ($dir > 0 || $mode == 'outgoing') {
        $result = db_query("insert into ".DB_PREFIX."mail_rules
                                       (         von       ,        type    ,    parent)
                                VALUES (".(int)$user_rule.",'".xss($mode)."',".(int)$dir.")") or db_die();
    }

    return true;
}

/**
 * Fuction to check if the ID provided correspond to a folder or an email.
 *
 * @param int $ID mail/folder id
 *  @return boolean true or false if the ID provided corresponds to a folder
 */
function is_mail_folder($message_id) {

    $toReturn = false;

    $result = db_query("SELECT typ FROM ".DB_PREFIX."mail_client
                         WHERE ID = ".(int)$message_id) or db_die();

    if ($row = db_fetch_row($result)) {
        if ($row[0] == 'd') {
            $toReturn = true;
        }
    }

    return $toReturn;

}

/**
 * returns the first $limit chars of a string
 *
 * 1. use: textarea columns in list view
 *
 * @param string $input text
 * @param int $limit max. number of returned chars
 * @return string
 */
function display_limit($input,$limit=100) {
    if (strlen($input) > $limit) return substr($input,0,$limit).'...';
    else return $input;
}
/**
 * unsets the ID in case an element is supposed to be copied
 * @author Nina Schmitt
 * @param int $ID the ID of the element
 * @param string $copy indicates if one is about to copy something
 * @return int $ID
 */
function prepare_ID_for_copy($ID,$copy) {
    if ($copy <> '') unset($ID);
    return $ID;
}
/**
 * changes fields for copy (at the moment only the title is changed)
 * @author Nina Schmitt
 * @param array $fields
 * @param string $title db_name of the field where title is stored
 * @return array $fields
 */
function change_fields_for_copy($fields,$title) {
    $fields[$title]['value']=__('copy')." ".$fields[$title]['value'];
    return $fields;
}


/**
 * returns the html string for the open/close/parent icon
 *
 * @return string
 */
function tree_icon($action, $additional_params='') {

    switch($action) {
        case 'open':
            return "<img src='".IMG_PATH."/open.gif' alt='".__('show sub elements')."' title='".__('show sub elements')."' border='0' ".$additional_params." />";
            break;
        case 'close':
            return "<img src='".IMG_PATH."/close.gif' alt='".__('hide sub elements')."' title='".__('hide sub elements')."' border='0' ".$additional_params." />";
            break;
        case 'parent':
            return "<img src='".IMG_PATH."/ASC_sel.gif' alt='".__('show parent element')."' title='".__('show parent element')."' border='0' ".$additional_params." />";
            break;
    }
}

/**
 * This function will return all basic input fields for a new email account
 * @author Nina Schmitt
 * @param array $port array with all connection options
 * @param array $value includes optional values for fields
 * @return string mail account fields
 *
 */
function get_mail_account_basic_fields($port, $value=array()){
    $form_fields = array();
    $form_fields[] = array('type' => 'text', 'name' => 'accountname', 'label' => __('mailaccount name'),'value' =>$value[0] );
    $form_fields[] = array('type' => 'text', 'name' => 'hostname', 'label' => __('host name'),'value' =>$value[1]);
    $options = array();
    $options[] = array('value' => '', 'text' => '');
    foreach ($port as $port1=>$port2) {
        $options[]= array('value' => $port1, 'text' => $port1, 'selected' => $value[2] == $port1);
    }
    $form_fields[] = array('type' => 'select', 'name' => 'type', 'label' => __('Type'), 'options' => $options,'value' =>$value[2]);
    $form_fields[] = array('type' => 'text', 'name' => 'username', 'label' => __('Username'),'value' =>$value[3]);
    $form_fields[] = array('type' => 'password_confirm', 'name' => 'password', 'label' => __('Password'),'value' =>$value[4]);
    return get_form_content($form_fields);

}

/**
 * This function saves all basic email fields to database
 * @author Nina Schmitt
 * @param string $module
 * @param string $accountname
 * @param string $hostname
 * @param string $type
 * @param string $username
 * @param string $password
 * @return int ID of mail account
 */
function save_mail_account_basic_fields($module,$accountname,$hostname,$type,$username,$password){
    $query = "INSERT INTO ".DB_PREFIX."global_mail_account
                         (  module , accountname  ,  hostname,   type,   username,   password )
                  VALUES ('".qss($module)."', '".strip_tags($accountname)."', '".strip_tags($hostname)."', '".strip_tags($type)."', '".strip_tags($username)."', '".strip_tags($password)."')";
    $result = db_query($query) or db_die();
    $query = "SELECT MAX(ID)
                    FROM ".DB_PREFIX."global_mail_account
                   WHERE accountname = '".strip_tags($accountname)."' AND
                  module = '".qss($module)."'";
    $result = db_query($query) or db_die();
    $row=db_fetch_row($result);
    return $row[0];
}

/**
 * This function updates all basic email fields in database
 * @author Nina Schmitt
 * @param string $module
 * @param string $accountname
 * @param string $hostname
 * @param string $type
 * @param string $username
 * @param string $password
 * @return void
 */
function update_mail_account_basic_fields($module,$accountname,$hostname,$type,$username,$password, $ID){
    $query="UPDATE ".DB_PREFIX."global_mail_account SET
                                   module='$module',
                                   accountname='$accountname',
                                   hostname='$hostname',
                                   type='$type',
                                   username='$username',
                                   password='$password'
                                   WHERE ID=".(int)$ID;
    $result = db_query($query) or db_die();

}

/**
 * Returns area for new remark
 * @author Nina Schmitt
 * @param void
 * @return string new remark area
 */
function set_new_remark($ID, $parent=0,$additional_quote=''){
    $value='';
    if($parent>0){
        $value = slookup('db_remarks','remark','ID',$parent,'1');
        // add quotes
        $value=quote($value);
        $name = __('Reply');
    }
    else{
        if($additional_quote<>'')$value = quote($additional_quote);
        $name = __('Add Remark');
    }
    $form_fields = array();
    $form_fields[] = array('type' => 'textarea', 'style' => 'width:65em;height:150px', 'name'=>'new_remark', 'label'=>$name, 'value'=>$value);
    $form_fields[] = array('type' => 'file', 'name'=>'upload', 'label'=>__('Upload'));
    $form_fields[] = array('type' => 'hidden','name'=>'parent_remark', 'value'=>$parent);
    $form_fields[] = array('type' => 'parsed_html', 'html' => '<br />');
    $remark_box = ' <a name="new_remark"></a><br />
    <fieldset>
    <legend>'.$name.'</legend>
    '.get_form_content($form_fields).'
    </fieldset>';
    $remark_box .=get_notify_fields(true);
    return $remark_box;
}
/**
 * This function quotes the content of e.g textareas
 *
 * @author Nina Schmitt
 * @param string $remark
 * @return string $txt_value
 */
function quote($remark){
    $txt_value = strip_tags(html_entity_decode($remark));
    $txt_value = preg_replace("/^|\n\r?/","> ",$txt_value);
    return $txt_value;
}
/**
 * This function writes data in global remark table. Important: Those entries can't be changed, only deleted!
 *
 * @author Nina Schmitt
 * @param string $remark the remark text
 * @param int $parent ID of parent element
 * @param int $ID ID of associated element
 * @param string $module associated module
 * @return int $remark_ID remark
 */
function add_remark($remark, $parent=0,$ID, $module) {
    global $user_ID, $dbTSnull;
    $fileupload=upload_file_create('upload');
    $query = "INSERT INTO ".DB_PREFIX."db_remarks
                             (  module ,module_ID,    date   ,  remark,  author ,  parent,      upload)
                      VALUES ('".qss($module)."', ".(int)$ID.", '$dbTSnull', '".strip_tags($remark)."', '".(int)$user_ID."', ".(int)$parent.", '".strip_tags($fileupload)."')";
    $result = db_query($query) or db_die();
    $query = "SELECT MAX(ID) FROM ".DB_PREFIX."db_remarks
                             WHERE module = '$module' AND
                             module_ID = ".(int)$ID."
                             AND author = '$user_ID'";
    $result = db_query($query) or db_die();
    $row = db_fetch_row($result);
    $remark_ID = $row[0];
    return $remark_ID;
}

/**
 * This function deletes all entries in table add_remark which belong to a certain record
 *
 * @author Nina Schmitt
 * @param string $module module of associated record
 * @param int $ID ID of associated record
 * @return void
 */
function delete_remark($module, $ID){
    $query = "DELETE FROM ".DB_PREFIX."db_remarks
                   WHERE module='$module' and module_ID=".(int)$ID;
    $result = db_query($query) or db_die();
}

/**
 * This function informs all recipients about new remark!
 *
 * @author Nina Schmitt
 * @param string $remark the remark text
 * @param int $parent ID of parent element
 * @param int $ID ID of associated element
 * @param string $module associated module
 * @param array $recipients array with all recipients of notification
 * @param string $title title of the element (to be added on mail subject)
 * @return void
 */
function remark_notification($remark,$parent=0,$ID,$module,$recipients, $title = '') {
    global $user_ID, $user_group;
    //link to remark
    $link="&mode=forms&ID=".$ID."&submode=discussion";

    //messageID
    if($parent>0)$messageid = "Message-ID: reply|".$parent."@".PHPR_SERVER_IP;
    else $messageid = "Message-ID: remark|".$ID."@".PHPR_SERVER_IP;

    //title: expand it with the module name and the ID of the element
    if ($title <> '') {
        $title = __('RE: ').$module." nr. $ID ($title)";
    }
    else {
        $title = __('RE: ').$module." nr. $ID";
    }


    $body=$remark;
    include_once(LIB_PATH."/notification.inc.php");
    //get accountname
    //first get category and related account
    $query="SELECT account_ID, h.ID FROM ".DB_PREFIX."db_accounts as a LEFT JOIN ".DB_PREFIX."rts as h
	ON a.ID = h.category WHERE h.ID = $ID";
    $result=db_query($query);
    $tmp=db_fetch_row($result);
    $account_ID=$tmp[0];
    $query="SELECT accountname FROM ".DB_PREFIX."global_mail_account WHERE ID =$account_ID";
    $result=db_query($query);
    $row=db_fetch_row($result);
    $notify = new Notification($user_ID, $user_group, $module, $recipients,$link,$body,$messageid,$title,1,$row[0]);
    $notify->notify();
    unset($notify);

}

/**
 * This function returns the table with all remarks in a certain module
 *
 * @author Nina Schmitt
 * @param string $module associated module
 * @param int $ID module element ID
 * @param string $answer link to answer ticket
 * @return string $output table
 */
function build_discussion_table($module,$ID, $answer){
    $output = "<br /><table id=\"$module\" summary=\"discussion table for $module\" width='98%'><thead><tr>\n";
    $output .= "<th style='width:10%;'>".__('Reply')."</th><th width='50%'>".__('Remark')."</th><th>".__('Date')."</th><th>".__('Author')."</th><th width='10%'>".__('Files')."</th></tr>
    </thead><tbody>\n";
    $output .= get_discussion_entries($module,$ID,$answer,0,0);
    $output .= "</tbody></table>";
    return $output;
}

function get_discussion_entries($module,$ID,$answer,$parent,$level){
    global $file_ID;
    $output="";
    $indent=10 + $level*10;
    $query = "SELECT date,remark,author, ID, upload FROM ".DB_PREFIX."db_remarks
                                        WHERE module='$module' AND module_ID=".(int)$ID." and parent=".(int)$parent."
                                        ORDER BY date ASC";
    $result = db_query($query) or db_die();
    while ($row = db_fetch_row($result)) {
        $author = slookup('users','nachname,vorname,email','ID',$row[2],'1');
        if(!isset($author))$author = $row[2];
        else{
            $author_parts = explode(',',$author);
            $author = "$author_parts[1] $author_parts[0]";
            if($author_parts[2]<>'')$author .= " <$author_parts[2]>";
        }
        $file_arr= explode('#',$row[4]);
        $files='';
        foreach ($file_arr as $value){
            list($filename,$tempname) = explode('|',$value);
            if ($filename <> '') {
                $rnd = rnd_string(9);
                $file_ID[$rnd] = $value;
                $files.= "<br /><a href='".PATH_PRE."lib/file_download.php?module=$module&download_attached_file=".$rnd.$sid."' target=_blank>$filename</a> \n";
            }
        }
        $output .= "<tr>
        <td><a href='".$answer."&amp;parent_remark=$row[3]&amp;new_remark=true#new_remark'><img src='".IMG_PATH."/goto.png' alt='reply this remark' title='reply this remark' border='0' /></a></td>
        <td style='padding-left:".$indent."px'>".preg_replace("/^|\n\r?/","<br /> ",$row[1])."</td><td>".show_iso_date1($row[0])."</td>
        <td>$author</td><td>$files</td></tr>";
        $output .= get_discussion_entries($module,$ID,$answer,$row[3],$level+1);
    }
    return $output;
}

// Return a transformed value depend the form_type or the fieldname
// @author Gustavo Solt
// @param mix value        - The value to transform
// @param Array field      - The array field with all the data of the field
// @param int ID           - Id of the row
// @param string fieldname - The field name for case like (acc, acc_write, etc)
// @param string limit     - limit for textareas etc.
// @return Array           - Array with ('value' => transformed value, 'link' => transformed link value)
function get_correct_value($value, $field, $ID = null, $fieldname = null,$limit = 100) {
    global $date_format_object;

    $data['value'] = $value;
    $data['link']  = '';

    switch ($field['form_type']) {
        // mail link
        case 'email':
            global $bg_class;
            $data['value'] = $value;
            $data['link']  = showmail_link($value, $bg_class);
            break;

            // date
        case 'date':
            $data['value'] = $date_format_object->convert_db2user($value);
            break;

            // url link
        case 'url':
            $url = $value;
            if ($url <> '' and !ereg("^http",$url) and !ereg("^ftp://",$url)) {
                $url = "http://".$url;
            }
            $data['value'] = $url;
            $data['link']  = "<a href='".$url."' target='_blank'>".$value."</a>";
            break;

            // select
        case 'select_values':
            // fetch values from database
            $values1 = explode('|',$field['form_select']);
            $values3 = array();
            // if value and text are different, split them again
            foreach($values1 as $value1) {
                if (eregi('#',$value1)) {
                    $values2 = explode('#', $value1);
                } else {
                    $values2[0] = $value1;
                    $values2[1] = $value1;
                }
                // Fix 0 value to -
                if ($values2[0] == '0') $values2[1] = '-';
                $values3[$values2[1]]=$values2[0];
            }
            $data['value'] = enable_vars(array_search($value,$values3));
            break;

            // select_sql
        case 'select_sql':
            $result2 = db_query(enable_vars($field['form_select']));
            $first_element = 0;
            while ($row = db_fetch_row($result2)) {
                $first_element = array_shift($row);
                if ($first_element == $value) {
                    $data['value'] = implode(',', $row);
                    break;
                }
            }
            break;

            // uploaded file
        case 'upload':
            list($filename,$tempname) = explode('|',$value);
            $data['value'] = $filename;
            $data['link']  = "<a href='".PATH_PRE.PHPR_DOC_PATH."/".$tempname.$sid."' target='_blank'>".$filename."</a>";
            break;

            // limit textarea to 100 chars
            // strip all the html tags for show in the list view and in the history
        case 'textarea':
        case 'textarea_add_remark':
            $data['value'] = display_limit(strip_tags(html_entity_decode($value)),$limit);
            break;

            // link to contact record
        case    "contact":
        case    "contact_create":
            $data['value'] = slookup('contacts','nachname,vorname,firma','ID',$value,'1');
            break;

        case 'project':
            $data['value'] = slookup('projekte','name','ID',$value,'1');
            break;

            // several user short names, serialized
        case 'user_ser':
            $pers_all = '';
            $pers = unserialize($value);
            foreach ($pers as $pers2) {
                $pers_all .= slookup('users', 'nachname', 'kurz', $pers2).',';
            }
            $data['value'] = $pers_all;
            break;

            // user fields
        case 'user_show':
        case 'userID':
        case 'authorID':
            $data['value'] = slookup('users', 'nachname, vorname', 'ID', $value,'1');
            break;

        case    "timestamp_create":
        case    "timestamp_modify":
        case    "timestamp_show":
        case    "timestamp":
            $data['value'] = $date_format_object->convert_dbdatetime2user($value);
            break;

            // display Byte
        case 'display_byte':
            $total_size = $value;
            if ($total_size > 1000000) {
                $fsize1 = $total_size/1000000;
                $fsize  = floor($fsize1).".".substr($total_size,1,2)." M";
            } else if ($total_size > 1000) {
                $fsize1 = $total_size/1000;
                $fsize  = floor($fsize1).".".substr($total_size,1,2)." k";
            }
            $data['value'] = $fsize;
            break;

            // display value from sql query
        case 'display_sql':
            // $ID is not global here, so enable_vars() does not correctly set it
            $q = str_replace('$ID',$ID,$field['form_select']);
            if (!isset($ID) || $ID == '') {
                $ID = 0;
            }

            $dbresult = db_query(enable_vars($q));
            if($dbresult) {
                $dbrow = db_fetch_row($dbresult);
            }
            if (is_array($dbrow)) {
                $data['value'] = implode(' ',$dbrow);
            } else {
                $data['value'] = '';
            }
            break;

            // without modification
        case 'userID_access':
            $data['value'] = $value;
            break;

            // others fields
        default:
            switch($fieldname) {
                // assignment field
                case "acc":
                case "acc_read":
                    // if it is serialized string
                    if (substr($value,-2) == ';}') {
                        $values = unserialize($value);
                        $return = '';
                        if (is_array($values)) {
                            foreach($values as $index => $user_kurz) {
                                $return .= slookup('users', 'nachname, vorname', 'kurz', $user_kurz);
                                $return .= "<br />";
                            }
                        }
                    } else {
                        $return = $value;
                    }
                    $data['value'] = $return;
                    break;

                    // assignment, write field
                case "acc_write":
                    $data['value'] = ($value == "w") ? __('read and write access to selection') : __('only read access to selection');
                    break;

                    // other fields
                default:
                    $value = strip_tags(preg_replace('/\&lt;.+\&gt;/U', ' ', $value));
                    $data['value'] = substr(nl2br($value),0,200);
                    break;
            }
            break;
    }

    return $data;
}

/**
 * This function changes automatically user group if necessary
 *
 * @author Nina Schmitt
 * @param int $current_group
 * @return void
 */
function change_group($current_group){
    global $user_ID, $tablename, $user_group, $sql_user_group;
    $sql_user_group = "(gruppe = ".(int)$current_group.")";
    $user_group = $current_group;
    $_SESSION['user_group'] =& $current_group;
}

/**
 * Return all the data from a table
 *
 * @param Array fields    - Data of the Fields
 * @param Array fieldlist - Fields to get
 * @param string table    - The table of the fields
 * @param string module   - Actual module
 * @param string where    - Special where
 * @param string order    - Special order
 * @return Array          - All the data (int => Array(name,form_type,form_select,value)
 */
function get_row_data($fields, $fieldlist, $table, $module, $where = '', $order = '') {

    // Initial value
    $data = array();

    // Make the query
    $query = "SELECT ".implode(',', $fieldlist)."
                FROM ".qss(DB_PREFIX.$table).
    sql_filter_flags($module, array('archive', 'read')).
    $where.
    $order;
    $result = db_query($query) or db_die();

    $int = 0;
    while ($row = db_fetch_row($result)) {
        for($i=0;$i<count($row);$i++) {
            // Initial value
            $data[$int][$i]['name']        = $fieldlist[$i];
            $data[$int][$i]['form_type']   = '';
            $data[$int][$i]['form_select'] = '';

            // Form type
            if (isset($fields[$fieldlist[$i]]['form_type'])) {
                $data[$int][$i]['form_type']   = $fields[$fieldlist[$i]]['form_type'];
            }
            // Form select
            if (isset($fields[$fieldlist[$i]]['form_select'])) {
                $data[$int][$i]['form_select'] = $fields[$fieldlist[$i]]['form_select'];
            }
            // Value
            $data[$int][$i]['value']       = $row[$i];
        }
        $int++;
    }

    return $data;
}

/**
* Get the table from a module
*
* @author Gustavo Solt
* @copyright (c) 2005 Mayflower GmbH
* @package PHProjekt
* @param string $module   - The module
* @return                 - The table of the module
*/
function get_table_by_module($module) {

    switch ($module) {
        case "filemanager":
            $table = DB_PREFIX.'dateien';
            break;
        case "calendar":
            $table = DB_PREFIX.'termine';
            break;
        case "projects":
            $table = DB_PREFIX.'projekte';
            break;
        case "helpdesk":
            $table = DB_PREFIX.'rts';
            break;
        case "links":
            $table = DB_PREFIX.'db_records';
            break;
        case "bookmarks":
            $table = DB_PREFIX.'lesezeichen';
            break;
        case "mail":
            $table = DB_PREFIX.'mail_client';
            break;
        default:
            $table = DB_PREFIX."$module";
            break;
    }

    return $table;
}

/**
* Complete with 0 for 4 digits
*
* @author Nina
* @copyright (c) 2005 Mayflower GmbH
* @package PHProjekt
* @param string $value - Value to convert
* @return              - 00Value
*/
function check_4d($value) {
    if (strlen($value) == 3) $value = "0".$value;
    if (strlen($value) == 2) $value = "00".$value;
    if (strlen($value) == 1) $value = "000".$value;
    return $value;
}
function get_notify_fields($checked=''){
    $html = '
    <fieldset>
    <legend>'.__('Notification').'</legend>
    '.get_form_content(array(array('type' => 'checkbox','checked'=>$checked,'name'=>'notify_recipient', 'label'=>__('Notify recipient')))).'
    </fieldset>
    ';
    return $html;
}

/**
 * This function will return an array with the dir tag and charset configuration according to
 * the language
 *
 * @param string $langua language accepted by the browser
 * @return array with keys 'lcfg' as the charset configuration and
 *                         'dir_tag' with the direction
 */
function get_charset ($langua = 'en') {


    if (eregi('pl|cz|hu|si',$langua)) {   $dir_tag = 'ltr'; $LANG_CODE = 'ISO-8859-2'; }
    else if (eregi('sk',$langua)) {       $dir_tag = 'ltr'; $LANG_CODE = 'windows-1250'; }
    else if (eregi('ru|uk',$langua)) {    $dir_tag = 'ltr'; $LANG_CODE = 'windows-1251'; }
    else if (eregi('he',$langua)) {       $dir_tag = 'rtl'; $LANG_CODE = 'windows-1255';}
    else if (eregi('lv|lt|ee',$langua)) { $dir_tag = 'ltr'; $LANG_CODE = 'windows-1257';}
    else if (eregi('tw',$langua)) {       $dir_tag = 'ltr'; $LANG_CODE = 'big5';}
    else if (eregi('zh',$langua)) {       $dir_tag = 'ltr'; $LANG_CODE = 'gb2312';}
    else if(eregi('jp',$langua)) {        $dir_tag = 'ltr'; $LANG_CODE = 'EUC-JP';}
    else if(eregi('kr|ko',$langua)) {     $dir_tag = 'ltr'; $LANG_CODE = 'EUC-KR';}
    else if(eregi('th',$langua)) {        $dir_tag = 'ltr'; $LANG_CODE = 'utf8';}
    else {                                $dir_tag = 'ltr'; $LANG_CODE = 'ISO-8859-1'; }
    if(!defined('LANG_CODE')){
        define('LANG_CODE', $LANG_CODE);
    }
    $lcfg = 'charset='.LANG_CODE;
    return array ('lcfg' => $lcfg , 'dir_tag' => $dir_tag);

}
/**
* check a given rule for cerrectness
*
* @param string $rule
* @return string the checked original rule or empty string
*/
function check_rule($rule) {
    if(in_array($rule, array('begins', 'ends', 'exact', '>', '>=', '<=', '<', 'not like'))) {
        return $rule;
    }
    return '';
}

/**
 * Updates user password from old crytp method or md5 method to md5 + prefix method
 *
 * @param int $user_ID user ID
 * @param string $user_pw user password
 * @param boolean $admin_login true or false if the update was sucessful or not
 */
function update_users_pw ($user_ID, $user_pw, $admin_login = '') {

    $toReturn = false;
    // invalid user or invalid password
    if ($user_ID == 0 || $user_pw == '') {
        $toReturn = false;
    }
    else {
        $enc_pw = md5('phprojektmd5'.$user_pw);

        // Make a new md5 password
        $query = "UPDATE ".DB_PREFIX."users
                    SET pw = '$enc_pw'
                    WHERE ID = ".(int)$user_ID."
                       AND status = 0 $admin_login";

        $result = db_query($query) or db_die();

        $toReturn = $enc_pw;
    }

    return $toReturn;
}

function get_date_fields(){
    $date_fields="";
    $query="SELECT DISTINCT db_name
                    FROM ".DB_PREFIX."db_manager
                    WHERE form_type ='date'
                          AND form_pos > 0
                          AND db_inactive <> 1
                            ";
    $result = db_query($query) or db_die();
    while($row=db_fetch_row($result)){
        $date_fields.="'$row[0]',";
    }
    return $date_fields;
}
?>
