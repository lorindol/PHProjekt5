<?php
/**
* provides selector snippets for the contacts
*
* @package    contacts
* @module     selector
* @author     Albrecht Guenther, $Author: polidor $
* @licence    GPL, see www.gnu.org/copyleft/gpl.html
* @copyright  2000-2006 Mayflower GmbH www.mayflower.de
* @version    $Id: contacts_selector.php,v 1.29 2006/11/10 04:50:46 polidor Exp $
*/
if (!defined('lib_included')) die('Please use index.php!');
require_once(LIB_PATH.'/selector/selector.inc.php');

$tabs = array();
// form start
echo '<div id="global-header">';
echo get_tabs_area($tabs);
echo '</div>';
echo '<div id="global-content">'."\n";

// --------- Selektor config ---------
$selector_options = get_selector_config($_SESSION['contactdata']['formdata']['_selector_type'], $_SESSION['contactdata']['formdata']['_title']);
$extras = $selector_options['extras'];
$opt_where = $selector_options['opt_where'];
$opt = $selector_options['opt'];
switch($_SESSION['contactdata']['formdata']['_selector_type']) {
    case "contact":
    case "profile_contact":
        $contactsextras = $extras;
        break;
    case "project":
    case "one_project":
        $projectsextras = $extras;
        break;
    case "member":
    case "user":
        $usersextras = $extras;
        break;
}
// --------- End Selektor config ---------

$selector_name = $_SESSION['contactdata']['formdata']['_selector_name'];
if (isset($delete_selector_filters)) $filters[$selector_name] = array();
require_once(LIB_PATH."/selector/class.selector.php");

// new Selektor
switch ($_SESSION['contactdata']['formdata']['_selector_type']) {
    case "contact":
        $sel = new PHProjektSelector($selector_name, 'contacts', $opt, 'single', 'select');
        break;
    case "profile_contact":
        $sel = new PHProjektSelector($selector_name, 'contacts', $opt, 'multiple', 'select');
        break;
    case "member":
        $sel = new PHProjektSelector($selector_name, 'users', $opt, 'multiple', 'select');
        break;
    case "project":
        $sel = new PHProjektSelector($selector_name, 'projects', $opt, 'multiple', 'select');
        break;
    case "user":
        $sel = new PHProjektSelector($selector_name, 'users', $opt, 'single', 'select');
        break;
    case "one_project":
        $sel = new PHProjektSelector($selector_name, 'projects', $opt, 'single', 'select');
        break;
}    

$sel->finishFormSubmitName = 'finishForm.'.$_SESSION['contactdata']['formdata']['_return'];
$sel->set_hidden_fields(array(  'ID' => $_SESSION['contactdata']['formdata']['_ID'],
                                'mode' => $_SESSION['contactdata']['formdata']['_mode'],
                                'action' => $_SESSION['contactdata']['formdata']['_action'],
                                'view' => $_SESSION['contactdata']['formdata']['_view']));

// get the selected values
if (!isset($stuff['preselect'])) {
    $stuff['preselect'] = array();
    if (is_array($_SESSION['contactdata']['formdata']['_selector'])) {
        foreach ($_SESSION['contactdata']['formdata']['_selector'] as $tmp_id) {
            if ($_SESSION['contactdata']['formdata']['_selector_type'] == "member") {
                $tmp_id = slookup('users', 'ID', 'kurz', $tmp_id);
            }
            $stuff['preselect'][$tmp_id] = "on";
        }
    } else {
        $stuff['preselect'][$_SESSION['contactdata']['formdata']['_selector']] = "on";
    }
    unset($tmp_id);
}
require_once(LIB_PATH."/selector/selector_filter_operations.php");

// print all the stuff!
$sel->show_window($stuff['preselect'], 15, "./contacts.php");
// ---------- Selektor end ---------


// ---------- Finishform begin ---------
echo "
<br />
<br />
";

// values of the form
if (SID) {
    echo "    <input type='hidden' name='".session_name()."' value='".session_id()."' />\n";
}

// all form fields
global $fields;
if (isset($_REQUEST['filterform']) || isset($_REQUEST['filterdel'])) {
    $fields = $_SESSION['contactdata']['formdata']['fields'];
} else {
    $_SESSION['contactdata']['formdata']['fields'] = xss_array($fields);
}
if (is_array($fields) && count($fields) > 0) {
    foreach($fields as $field_name => $field_array) {
        echo "    <input type='hidden' name='".xss($field_name)."' value='".xss($field_array['value'])."' />\n";
    }
}
// persons
if (isset($_POST['persons'])) {
    $_SESSION['contactdata']['formdata']['persons']   = xss_array($_POST['persons']);
    $_SESSION['contactdata']['formdata']['acc']       = xss($_POST['acc']);
    $_SESSION['contactdata']['formdata']['acc_write'] = xss($_POST['acc_write']);
}
if (isset($_SESSION['contactdata']['formdata']['persons'])) {
    foreach($_SESSION['contactdata']['formdata']['persons'] as $k => $v) {
        echo "<input type='hidden' name='persons[]' value='".xss($v)."' />\n";
    }
    echo "<input type='hidden' name='acc' value='".xss($_SESSION['contactdata']['formdata']['acc'])."' />\n";
    echo "<input type='hidden' name='acc_write' value='".xss($_SESSION['contactdata']['formdata']['acc_write'])."' />\n";
}

// parent
if (isset($_POST['parent'])) {
    $_SESSION['contactdata']['formdata']['parent'] = xss($_POST['parent']);
}
if (isset($_SESSION['contactdata']['formdata']['parent'])) {
    echo "    <input type='hidden' name='parent' value='".xss($_SESSION['contactdata']['formdata']['parent'])."' />\n";
}

// project_personen
if (isset($_POST['project_personen'])) {
    $_SESSION['contactdata']['formdata']['project_personen'] = xss_array($_POST['project_personen']);
}
if (isset($_SESSION['contactdata']['formdata']['project_personen'])) {
    foreach($_SESSION['contactdata']['formdata']['project_personen'] as $k => $v) {
        echo "<input type='hidden' name='project_personen[]' value='".xss($v)."' />\n";
    }
}

// contact_personen
if (isset($_POST['contact_personen'])) {
    $_SESSION['contactdata']['formdata']['contact_personen'] = xss_array($_POST['contact_personen']);
}
if (isset($_SESSION['contactdata']['formdata']['contact_personen'])) {
    foreach($_SESSION['contactdata']['formdata']['contact_personen'] as $k => $v) {
        echo "<input type='hidden' name='contact_personen[]' value='".xss($v)."' />\n";
    }
}

if ($_SESSION['contactdata']['formdata']['_selector_type'] == "profile_contact") {

    // name
    if (isset($_POST['name'])) {
        $_SESSION['contactdata']['formdata']['name'] = xss($_POST['name']);
    }
    if (isset($_SESSION['contactdata']['formdata']['name'])) {
        echo "<input type='hidden' name='name' value='".xss($_SESSION['contactdata']['formdata']['name'])."' />\n";
    }

    // remark
    if (isset($_POST['remark'])) {
        $_SESSION['contactdata']['formdata']['remark'] = xss($_POST['remark']);
    }
    if (isset($_SESSION['contactdata']['formdata']['remark'])) {
        echo "<input type='hidden' name='remark' value='".xss($_SESSION['contactdata']['formdata']['remark'])."' />\n";
    }
        
    // neu
    if ($_SESSION['contactdata']['formdata']['_ID'] == '') {
        echo "<input type='hidden' name='neu' value='".__('New profile')."' />\n";
    // aendern
    } else {
        echo "<input type='hidden' name='aendern' value='".__('Modify')."' />\n";
    }
}

$keys = array_keys($stuff['preselect']);
if ($keys[0] == '') $disabled = 'disabled="true"';
else                $disabled = '';

echo get_buttons(array(array("type" => "submit", "name" => $_SESSION['contactdata']['formdata']['_return'], "value" => __('OK'), "extrahtml" => 'id="submit_selector" '.$disabled)));
echo get_buttons(array(array("type" => "submit", "name" => $_SESSION['contactdata']['formdata']['_cancel'], "value" => __('Cancel'))));
echo "</form>\n";

echo "</div>\n";
// ---------- Finishform end ---------
?>
