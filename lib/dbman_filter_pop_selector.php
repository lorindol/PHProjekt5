<?php
/**
 * Filter popup selector
 *
 * @package    	lib
 * @subpackage 	main
 * @author     	Johannes Schlueter
 * @licence     GPL, see www.gnu.org/copyleft/gpl.html
 * @copyright  	2000-2006 Mayflower GmbH www.mayflower.de
 * @version    	$Id: 
 */

// check whether the lib has been included - authentication!
if (!defined('lib_included')) die('Please use index.php!');

require_once(LIB_PATH."/selector/selector.inc.php");

$tabs = array();
// form start

echo '<div id="global-header">';
echo get_tabs_area($tabs);
echo '</div>';
echo '<div id="global-content" class="popup">'."\n";

// --------- Selektor config ---------
$selector_options = get_selector_config($_SESSION['tododata']['formdata']['_selector_type'], $_SESSION['tododata']['formdata']['_title']);
$extras = $selector_options['extras'];
$opt_where = $selector_options['opt_where'];
$opt = $selector_options['opt'];

switch($_SESSION['tododata']['formdata']['_selector_type']) {
    case "contact":
        $contactsextras = $extras;
        break;
    case "project":
        $projectsextras = $extras;
        break;
    case "member":
    case "user":
        $usersextras = $extras;
        break;
}
// --------- End Selektor config ---------

$selector_name = $_SESSION['tododata']['formdata']['_selector_name'];
if (isset($delete_selector_filters)) $filters[$selector_name] = array();
require_once(LIB_PATH."/selector/class.selector.php");

// new Selektor
switch ($_SESSION['tododata']['formdata']['_selector_type']) {
    case "contact":
        $sel = new PHProjektSelector($selector_name, 'contacts', $opt, 'single', 'select');
        break;
    case "project":
        $sel = new PHProjektSelector($selector_name, 'projects', $opt, 'single', 'select');
        break;
    case "member":
        $sel = new PHProjektSelector($selector_name, 'users', $opt, 'multiple', 'select');
        break;
    case "user":
        $sel = new PHProjektSelector($selector_name, 'users', $opt, 'single', 'select');
        break;
}    

$sel->finishFormSubmitName = 'finishForm.'.$_SESSION['tododata']['formdata']['_return'];
$sel->set_hidden_fields(array(  'ID'        => $_SESSION['tododata']['formdata']['_ID'],
                                'mode'      => $_SESSION['tododata']['formdata']['_mode'],
                                'view'      => $_SESSION['tododata']['formdata']['_view'],
                                'justform'  => $justform));

// get the selected values
if (!isset($stuff['preselect'])) {
    $stuff['preselect'] = array();
    if (is_array($_SESSION['tododata']['formdata']['_selector'])) {
        foreach ($_SESSION['tododata']['formdata']['_selector'] as $tmp_id) {
            if ($_SESSION['tododata']['formdata']['_selector_type'] == "member") {
                    $tmp_id = slookup('users', 'ID', 'kurz', $tmp_id);
            }
            $stuff['preselect'][$tmp_id] = "on";
        }
    } else {
        $stuff['preselect'][$_SESSION['tododata']['formdata']['_selector']] = "on";
    }
    unset($tmp_id);
}
require_once(LIB_PATH."/selector/selector_filter_operations.php");

// print all the stuff!
$sel->show_window($stuff['preselect'], 15, "./dbman_filter_pop.php");
// ---------- Selektor end ---------


// ---------- Finishform begin ---------
echo "
<br />
<br />
";
if (SID) {
    echo "<input type='hidden' name='".session_name()."' value='".session_id()."' />\n";
}

if ($justform) $_SESSION['tododata']['formdata']['justform'] = $justform;
if (isset($_SESSION['tododata']['formdata']['justform']) && ($_SESSION['tododata']['formdata']['justform'])) {
    echo "<input type='hidden' name='justform' value='1' />\n";
}

// values of the form
// all form fields
global $fields;
if (isset($_REQUEST['filterform']) || isset($_REQUEST['filterdel'])) {
    $fields = $_SESSION['tododata']['formdata']['fields'];
} else {
    $_SESSION['tododata']['formdata']['fields'] = xss_array($fields);
}
if (is_array($fields) && count($fields) > 0) {
    foreach($fields as $field_name => $field_array) {
        echo "<input type='hidden' name='".xss($field_name)."' value='".xss($field_array['value'])."' />\n";
    }
}

// persons
if (isset($_POST['persons'])) {
    $_SESSION['tododata']['formdata']['persons']    = xss_array($_POST['persons']);
    $_SESSION['tododata']['formdata']['acc']        = xss($_POST['acc']);
    $_SESSION['tododata']['formdata']['acc_write']  = xss($_POST['acc_write']);
}
if (isset($_SESSION['tododata']['formdata']['persons'])) {
    foreach($_SESSION['tododata']['formdata']['persons'] as $k => $v) {
        echo "<input type='hidden' name='persons[]' value='".xss($v)."' />\n";
    }
    echo "<input type='hidden' name='acc' value='".xss($_SESSION['tododata']['formdata']['acc'])."' />\n";
    echo "<input type='hidden' name='acc_write' value='".xss($_SESSION['tododata']['formdata']['acc_write'])."' />\n";
}

$keys = array_keys($stuff['preselect']);
if ($keys[0] == '') $disabled = 'disabled="true"';
else                $disabled = '';

echo get_buttons(array(array("type" => "submit", "name" => $_SESSION['tododata']['formdata']['_return'], "value" => __('OK'), "extrahtml" => 'id="submit_selector" '.$disabled)));
echo get_buttons(array(array("type" => "submit", "name" => $_SESSION['tododata']['formdata']['_cancel'], "value" => __('Cancel'))));
echo "</form>\n";

echo "</div>\n";
// ---------- Finishform end ---------
?>
