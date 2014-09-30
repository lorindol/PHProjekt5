<?php
/**
* provides selector snippets for the calendar
*
* @package    calendar
* @module     selector
* @author     Albrecht Guenther, $Author: nina $
* @licence    GPL, see www.gnu.org/copyleft/gpl.html
* @copyright  2000-2006 Mayflower GmbH www.mayflower.de
* @version    $Id: calendar_selector.php,v 1.54 2006/10/18 11:21:03 nina Exp $
*/
if (!defined('lib_included')) die('Please use index.php!');
require_once(LIB_PATH.'/selector/selector.inc.php');

// --------- Selektor config ---------
$selector_options = get_selector_config($_SESSION['calendardata']['formdata']['_selector_type'], $_SESSION['calendardata']['formdata']['_title']);
$extras = $selector_options['extras'];
$opt_where = $selector_options['opt_where'];
$opt = $selector_options['opt'];
switch($_SESSION['calendardata']['formdata']['_selector_type']) {
    case "contact":
        $contactsextras = $extras;
        break;
    case "project":
        $projectsextras = $extras;
        break;
    case "member":
        $usersextras = $extras;
        break;
}
// --------- End Selektor config ---------

$selector_name = $_SESSION['calendardata']['formdata']['_selector_name'];
if (isset($delete_selector_filters)) $filters[$selector_name] = array();
require_once(LIB_PATH."/selector/class.selector.php");

// new Selektor
switch ($_SESSION['calendardata']['formdata']['_selector_type']) {
    case "contact":
        $sel = new PHProjektSelector($selector_name, 'contacts', $opt, 'single', 'select');
        break;
    case "project":
        $sel = new PHProjektSelector($selector_name, 'projects', $opt, 'single', 'select');
        break;
    case "member":
          default:
        $sel = new PHProjektSelector($selector_name, 'users', $opt, 'multiple', 'select');
        break;
}    

$sel->finishFormSubmitName = 'finishForm.'.$_SESSION['calendardata']['formdata']['_return'];
$sel->set_hidden_fields(array('mode' => $_SESSION['calendardata']['formdata']['_mode'],
                              'view' => $_SESSION['calendardata']['formdata']['_view'],
                              'ID' => $_SESSION['calendardata']['formdata']['_ID']));

// get the selected values
if (!isset($stuff['preselect'])) {
    $stuff['preselect'] = array();
    if (is_array($_SESSION['calendardata']['formdata']['_selector'])) {
        foreach ($_SESSION['calendardata']['formdata']['_selector'] as $tmp_id) {
            $stuff['preselect'][$tmp_id] = "1";
        }
    } else {
        $stuff['preselect'][$_SESSION['calendardata']['formdata']['_selector']] = "1";
    }
    unset($tmp_id);
}
require_once(LIB_PATH."/selector/selector_filter_operations.php");

// print all the stuff!
$sel->show_window($stuff['preselect'], 15, "./calendar.php");
// ---------- Selektor end ---------


// ---------- Finishform begin ---------
// Now build the Finishform that fires us back to the site from where we linked in
echo "
<br />
<br />
";

// values of the form
if ($_SESSION['calendardata']['formdata']['_act_for']) {
    echo "    <input type='hidden' name='act_for' value='".xss($_SESSION['calendardata']['formdata']['_act_for'])."' />\n";
}
if ($_SESSION['calendardata']['formdata']['_axis']) {
    echo "    <input type='hidden' name='axis' value='".xss($_SESSION['calendardata']['formdata']['_axis'])."' />\n";
}
if ($_SESSION['calendardata']['formdata']['_dist']) {
    echo "    <input type='hidden' name='dist' value='".xss($_SESSION['calendardata']['formdata']['_dist'])."' />\n";
}
if (SID) {
    echo "    <input type='hidden' name='".session_name()."' value='".session_id()."' />\n";
}

$keys = array_keys($stuff['preselect']);
if ($keys[0] == '') $disabled = 'disabled="true"';
else                $disabled = '';

echo get_buttons(array(array("type" => "submit", "name" => $_SESSION['calendardata']['formdata']['_return'], "value" => __('OK'), "extrahtml" => 'id="submit_selector" '.$disabled)));
echo get_buttons(array(array("type" => "submit", "name" => $_SESSION['calendardata']['formdata']['_cancel'], "value" => __('Cancel'))));
echo "</form>\n";

// ---------- Finishform end ---------
?>
