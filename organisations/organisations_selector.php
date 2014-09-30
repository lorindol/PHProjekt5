<?php
/**
 * provides selector snippets for the organisations
 *
 * @package    organisations
 * @subpackage selector
 * @author     Gustavo Solt, $Author: gustavo $
 * @licence    GPL, see www.gnu.org/copyleft/gpl.html
 * @copyright  2000-2006 Mayflower GmbH www.mayflower.de
 * @version    $Id: 
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
$selector_options = get_selector_config($_SESSION['organisationsdata']['formdata']['_selector_type'], $_SESSION['organisationsdata']['formdata']['_title']);
$extras = $selector_options['extras'];
$opt_where = $selector_options['opt_where'];
$opt = $selector_options['opt'];
switch($_SESSION['organisationsdata']['formdata']['_selector_type']) {
    case "member":
    case "user":
        $usersextras = $extras;
        break;
    case "contact":
    case "one_contact":
        $contactsextras = $extras;
        break;
}
// --------- End Selektor config ---------

$selector_name = $_SESSION['organisationsdata']['formdata']['_selector_name'];
if (isset($delete_selector_filters)) $filters[$selector_name] = array();
require_once(LIB_PATH."/selector/class.selector.php");

// new Selektor
switch ($_SESSION['organisationsdata']['formdata']['_selector_type']) {
    case "member":
        $sel = new PHProjektSelector($selector_name, 'users', $opt, 'multiple', 'select');
        break;
    case "user":
        $sel = new PHProjektSelector($selector_name, 'users', $opt, 'single', 'select');
        break;
    case "contact":
        $sel = new PHProjektSelector($selector_name, 'contacts', $opt, 'multiple', 'select');
        break;
}    

$sel->finishFormSubmitName = 'finishForm.'.$_SESSION['organisationsdata']['formdata']['_return'];
$sel->set_hidden_fields(array(  'ID' => $_SESSION['organisationsdata']['formdata']['_ID'],
                                'mode' => $_SESSION['organisationsdata']['formdata']['_mode'],
                                'action' => $_SESSION['organisationsdata']['formdata']['_action'],
                                'view' => $_SESSION['organisationsdata']['formdata']['_view']));

// get the selected values
if (!isset($stuff['preselect'])) {
    $stuff['preselect'] = array();
    if (is_array($_SESSION['organisationsdata']['formdata']['_selector'])) {
        foreach ($_SESSION['organisationsdata']['formdata']['_selector'] as $tmp_id) {
            if ($_SESSION['organisationsdata']['formdata']['_selector_type'] == "member") {
                $tmp_id = slookup('users', 'ID', 'kurz', $tmp_id);
            }
            $stuff['preselect'][$tmp_id] = "on";
        }
    } else {
        $stuff['preselect'][$_SESSION['organisationsdata']['formdata']['_selector']] = "on";
    }
    unset($tmp_id);
}
require_once(LIB_PATH."/selector/selector_filter_operations.php");

// print all the stuff!
$sel->show_window($stuff['preselect'], 15, "./organisations.php");
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
    $fields = $_SESSION['organisationsdata']['formdata']['fields'];
} else {
    $_SESSION['organisationsdata']['formdata']['fields'] = xss_array($fields);
}
if (is_array($fields) && count($fields) > 0) {
    foreach($fields as $field_name => $field_array) {
        echo "    <input type='hidden' name='".xss($field_name)."' value='".xss($field_array['value'])."' />\n";
    }
}

// parent
if (isset($_POST['parent'])) {
    $_SESSION['organisationsdata']['formdata']['parent'] = xss($_POST['parent']);
}
if (isset($_SESSION['organisationsdata']['formdata']['parent'])) {
    echo "<input type='hidden' name='parent' value='".xss($_SESSION['organisationsdata']['formdata']['parent'])."' />\n";
}

// persons
if (isset($_POST['persons'])) {
    $_SESSION['organisationsdata']['formdata']['persons']   = xss_array($_POST['persons']);
    $_SESSION['organisationsdata']['formdata']['acc']       = xss($_POST['acc']);
    $_SESSION['organisationsdata']['formdata']['acc_write'] = xss($_POST['acc_write']);
}
if (isset($_SESSION['organisationsdata']['formdata']['persons'])) {
    foreach($_SESSION['organisationsdata']['formdata']['persons'] as $k => $v) {
        echo "<input type='hidden' name='persons[]' value='".xss($v)."' />\n";
    }
    echo "<input type='hidden' name='acc' value='".xss($_SESSION['organisationsdata']['formdata']['acc'])."' />\n";
    echo "<input type='hidden' name='acc_write' value='".xss($_SESSION['organisationsdata']['formdata']['acc_write'])."' />\n";
}

// contact_personen
if (isset($_POST['contact_personen'])) {
    $_SESSION['organisationsdata']['formdata']['contact_personen'] = xss_array($_POST['contact_personen']);
    foreach($_POST['contact_personen'] as $k => $v) {
        if (!empty($_POST['c_'.$v.'_text_role'])) {
            $_SESSION['organisationsdata']['formdata']['c_'.$v.'_role'] = xss($_POST['c_'.$v.'_text_role']);
        } else {
            $_SESSION['organisationsdata']['formdata']['c_'.$v.'_role'] = xss($_POST['c_'.$v.'_role']);
        }
    }
}
if (isset($_SESSION['organisationsdata']['formdata']['contact_personen'])) {
    foreach($_SESSION['organisationsdata']['formdata']['contact_personen'] as $k => $v) {
        echo "<input type='hidden' name='contact_personen[]' value='".xss($v)."' />\n";
        echo "<input type='hidden' name='c_".$v."_role' value='".xss($_SESSION['organisationsdata']['formdata']['c_'.$v.'_role'])."' />\n";
    }
}

$keys = array_keys($stuff['preselect']);
if ($keys[0] == '') $disabled = 'disabled="true"';
else                $disabled = '';

echo get_buttons(array(array("type" => "submit", "name" => $_SESSION['organisationsdata']['formdata']['_return'], "value" => __('OK'), "extrahtml" => 'id="submit_selector" '.$disabled)));
echo get_buttons(array(array("type" => "submit", "name" => $_SESSION['organisationsdata']['formdata']['_cancel'], "value" => __('Cancel'))));
echo "</form>\n";

echo "</div>\n";
// ---------- Finishform end ---------
?>
