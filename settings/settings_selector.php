<?php

// settings_selector.php - PHProjekt Version 5.2
// copyright  ©  2000-2005 Albrecht Guenther  ag@phprojekt.com
// www.phprojekt.com
// Authors: Albrecht Guenther, Franz Graf, $Author: nina $
// $Id: settings_selector.php,v 1.33 2006/10/18 11:31:00 nina Exp $

// check whether the lib has been included - authentication!
if (!defined("lib_included")) die("Please use index.php!");

include_once(LIB_PATH."/selector/selector.inc.php");

// --------- Selektor config ---------
$selector_options = get_selector_config($_SESSION['settings_5']['formdata']['_selector_type'], $_SESSION['settings_5']['formdata']['_title']);
$extras = $selector_options['extras'];
$opt_where = $selector_options['opt_where'];
$opt = $selector_options['opt'];

// Options for Quickaddings
$usersextras = $extras;
// --------- End Selektor config ---------

$selector_name = $_SESSION['settings_5']['formdata']['_selector_name'];
if (isset($delete_selector_filters)) $filters[$selector_name] = array();
include_once(LIB_PATH."/selector/class.selector.php");

// new Selektor
$sel = new PHProjektSelector($selector_name, 'users', $opt, 'multiple', 'select');

$sel->finishFormSubmitName = 'finishForm.'.$_SESSION['settings_5']['formdata']['_return'];
$sel->set_hidden_fields(array('mode' => $_SESSION['settings_5']['formdata']['_mode']));

settype($_SESSION['settings_5']['formdata']['_selector'], 'array');
if (!isset($stuff['preselect'])) {
    $stuff['preselect'] = array();
    if (is_array($_SESSION['settings_5']['formdata']['_selector'])) {
        foreach ($_SESSION['settings_5']['formdata']['_selector'] as $tmp_id) {
            if ($_SESSION['settings_5']['formdata']['_selector_type'] == "member") {
                // Assignment
                if (isset($_POST['persons'])) {
                    $tmp_id = slookup('users', 'ID', 'kurz', $tmp_id);
                }
            }
            $stuff['preselect'][$tmp_id] = "on";
        }
    } else {
        $stuff['preselect'][$_SESSION['settings_5']['formdata']['_selector']] = "on";
    }
    unset($tmp_id);
}
require_once(LIB_PATH."/selector/selector_filter_operations.php");

// print all the stuff!
$sel->show_window($stuff['preselect'], 15, "./settings.php");
// ---------- Selektor end ---------


// ---------- Finishform begin ---------
// Now build the Finishform that fires us back to the site from where we linked in
echo "
<br />
<br />
    <input type='hidden' name='mode' value='".$_SESSION['settings_5']['formdata']['_mode']."' />
";

// values of the form
if (SID) echo "<input type='hidden' name='".session_name()."' value='".session_id()."' />\n";

// profile_id
if (isset($_POST['profile_id'])) {
    $_SESSION['settings_5']['formdata']['profile_id'] = xss($_POST['profile_id']);
}
if (isset($_SESSION['settings_5']['formdata']['profile_id'])) {
    echo "<input type='hidden' name='profile_id' value='".xss($_SESSION['settings_5']['formdata']['profile_id'])."' />\n";
}

// profile_name
if (isset($_POST['profile_name'])) {
    $_SESSION['settings_5']['formdata']['profile_name'] = xss($_POST['profile_name']);
}
if (isset($_SESSION['settings_5']['formdata']['profile_name'])) {
    echo "<input type='hidden' name='profile_name' value='".xss($_SESSION['settings_5']['formdata']['profile_name'])."' />\n";
}

// profile_users
if (isset($_POST['profile_users'])) {
    $_SESSION['settings_5']['formdata']['profile_users'] = xss($_POST['profile_users']);
}
if (isset($_SESSION['settings_5']['formdata']['profile_users'])) {
    foreach($_SESSION['settings_5']['formdata']['profile_users'] as $k => $v) {
        echo "<input type='hidden' name='profile_users[]' value='".xss($v)."' />\n";
    }
}

// persons
if (isset($_POST['persons'])) {
    $_SESSION['settings_5']['formdata']['persons'] = xss_array($_POST['persons']);
    $_SESSION['settings_5']['formdata']['acc']     = xss($_POST['acc']);
}
if (isset($_SESSION['settings_5']['formdata']['persons'])) {
    foreach($_SESSION['settings_5']['formdata']['persons'] as $k => $v) {
        echo "<input type='hidden' name='persons[]' value='".xss($v)."' />\n";
    }
    echo "<input type='hidden' name='acc' value='".xss($_SESSION['settings_5']['formdata']['acc'])."' />\n";
}

$keys = array_keys($stuff['preselect']);
if ($keys[0] == '') $disabled = 'disabled="true"';
else                $disabled = '';

echo get_buttons(array(array("type" => "submit", "name" => $_SESSION['settings_5']['formdata']['_return'], "value" => __('OK'), "extrahtml" => 'id="submit_selector" '.$disabled)));
echo get_buttons(array(array("type" => "submit", "name" => $_SESSION['settings_5']['formdata']['_cancel'], "value" => __('Cancel'))));
echo "</form>\n";
// ---------- Finishform end ---------

?>
