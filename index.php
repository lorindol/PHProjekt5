<?php

// index.php - PHProjekt Version 5.2
// copyright  ©  2000-2007 Albrecht Guenther  ag@phprojekt.com
// www.phprojekt.com
// Author: Albrecht Guenther, $Author: albrecht $
// $Id: index.php,v 1.54.2.5 2007/05/09 19:02:28 albrecht Exp $


// ***********
// preparation

// define the error level for the next lines, it will be changed in the lib
// to the desired value.
error_reporting(0);

// set some other variables
$var_ini_set = ini_set('include_path',     './');
// avoid this d... error warning since it does not affect the scritps here
$var_ini_set = ini_set('session.bug_compat_42',   1);
$var_ini_set = ini_set('session.bug_compat_warn', 0);

// authentification etc.
define('PATH_PRE','./');
require_once(PATH_PRE.'lib/lib.inc.php');

// set baseurl
$bu1 = explode('index.php', $_SERVER['SERVER_NAME'].xss($_SERVER['REQUEST_URI']));
$_SESSION['baseurl'] = $bu1[0];

// redirect
redirect();

// ´define today
if (!$day) today();

// *******
// actions
// *******

// fix GET
$module = (isset($_REQUEST['module'])) ? xss($_REQUEST['module']) : '';

// 1. action: logout
// logout  -> login!
if ($module == 'logout') { logout(); }

// 2. action: change groups
// if change of group, set it in variable
if ($change_group) {
	check_csrftoken();
    // is the user member of the requested group?
    $result = db_query("SELECT grup_ID
                          FROM ".DB_PREFIX."grup_user
                         WHERE user_ID = ".(int)$user_ID) or db_die();
    while ($row = db_fetch_row($result)) {
        $groups_[] = $row[0];
    }
    if (!in_array($change_group, $groups_)) {
        exit;
    }
    $user_group = $change_group;
    $sql_user_group = "(gruppe = ".(int)$user_group.")";
    $_SESSION['user_group'] =& $user_group;

}

// 3. action: close chat
// close chat? -> delete alivefile & chatfile
if ($chataction == 'logout') {
    $alivefile = $user_group.'_alive';
    $chatfile  = $user_group.'_'.$chatfile;

    // last personen closes the light :-)
    if (file_exists('chat/'.$alivefile)) {
        $lines = file('chat/'.$alivefile);
    }
    if (!$lines[1]) {
        // save chat file only if a flag in the config is set
        if ($save_chat) {
            // prepare name of file to save
            $datum   = date("D_d_M_Hui");
            $newname = $datum.'-'.$user_group.'.txt';
            copy("$chatfile","$newname");
        }
        if (file_exists("chat/$chatfile")) {
            unlink("chat/$chatfile");
        }
        if (file_exists("chat/$alivefile")) {
            unlink("chat/$alivefile");
        }
    }
}


// 4. action: call frames

// define how a modules starts: with tree view open or closed and x items/per page
if (!$tree_mode) {
    if ($start_tree_mode) {
        $tree_mode = $start_tree_mode;
    } else {
        $tree_mode = 'open';
    }
}

// no module chosen?
if (!$module) {
    if ($startmodule <> '') {
        // take the start module for the settings ...
        $module = $startmodule;
    } else {
        // or as the default value summary
        $module = 'summary';
    }
}


// redirect to where the user wanted to go, except logout page
if (!empty($_REQUEST['return_path']) and !ereg('logout|index.php', $_REQUEST['return_path'])) {
    $return_path = xss(urldecode($_REQUEST['return_path']));
    if ($return_path == '/') {
        $return_path = 'index.php';
    }

    if (strpos($return_path, "/")===0) {
        $url = substr($return_path, 1);
    }

    if (strstr($url, '?')) {
        $url .= '&'.SID;
    }
    else {
        $url .= '?'.SID;
    }
    $url = preg_replace('#([\r\n]+)#', '', $url);
    header('Location: '.$url);
    exit;
}


if ($module != 'logout') {
    // check for members contacts exception
    if ($module == 'members') {
        $module_dir = 'contacts';
    }
    else {
        $module_dir = $module;
    }

    $location = 'Location: '.strip_tags($module_dir).'/'.strip_tags($module).'.php?'.strip_tags($_SERVER['QUERY_STRING']);
    $location = preg_replace('#([\r\n]+)#', '', $location);
    header($location);
    exit;
}


// ****************
// logout functions
function logout() {

    // Remember me: when user clicks logout the remember me cookie must be deleted

    // delete the token from database
    if (isset($_COOKIE['remember_me_token']) && is_md5($_COOKIE['remember_me_token'])) {

        $tmp_logintoken = qss($_COOKIE['remember_me_token']);

        $tmp_query = "DELETE FROM ".DB_PREFIX."logintoken
                                      WHERE
                                          token = '$tmp_logintoken'";

        $tmp_result = db_query($tmp_query);
    }

    // unset remember me cookie
    setcookie('remember_me_token','',time() - 36000);

    // at last, unset it
    unset($_COOKIE['remember_me_token']);

    track_logout();
    // store settings: filter, column width, sort
    save_settings();
    // destroy custom session environment if required
    if (defined('PHPR_CUSTOM_ACTIVE') && PHPR_CUSTOM_ACTIVE) {
        unset($_SESSION[PHPR_CUSTOM_SESSION_ARRAY]);
    }
    // destroy the session - on some system the first,
    // on some system the second function doesn't work :-|
    @session_unset();
    @session_destroy();
    unset($user_pw, $user_name, $module);
    // call the loginscreen again
    require(PATH_PRE.'lib/auth.inc.php');
}

// track logout
function track_logout() {
    global $dbTSnull;
    if (PHPR_LOGS and $GLOBALS['logID']) {
        $logID = $GLOBALS['logID'];
        $result2 = db_query("UPDATE ".DB_PREFIX."logs
                                SET logout = '$dbTSnull'
                              WHERE ID = ".(int)$logID) or db_die();
    }
}

function save_settings() {
    global $user_ID, $user_group, $f_sort, $flist, $diropen, $tdw;
    $tmp_settings = $_SESSION['settings'];
    if ($f_sort)  $tmp_settings['f_sort_store']  = $f_sort;
    if ($flist)   $tmp_settings['flist_store']   = $flist;
    if ($diropen) $tmp_settings['diropen_store'] = $diropen;
    if ($tdw)     $tmp_settings['tdw_store']     = $tdw;
    if ($_SESSION['show_read_elements']) {
        $tmp_settings['show_read_elements_settings'] = $_SESSION['show_read_elements'];
    }
    if ($_SESSION['show_archive_elements']) {
        $tmp_settings['show_archive_elements_settings'] = $_SESSION['show_archive_elements'];
    }
    if ($_SESSION['show_html_editor']) {
        $tmp_settings['show_html_editor_settings'] = $_SESSION['show_html_editor'];
    }
    $tmp_settings['last_group']=$user_group;
    $result = db_query("UPDATE ".DB_PREFIX."users
                           SET settings = '".serialize($tmp_settings)."'
                         WHERE ID = ".(int)$user_ID) or db_die();
}

?>
