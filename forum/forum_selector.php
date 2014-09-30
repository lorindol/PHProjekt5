<?php
/**
* forum selector script
*
* @package    forum
* @module     selector
* @author     Gustavo Solt, $Author: nina $
* @licence    GPL, see www.gnu.org/copyleft/gpl.html
* @copyright  2000-2006 Mayflower GmbH www.mayflower.de
* @version    $Id: forum_selector.php,v 1.9 2006/10/18 11:30:59 nina Exp $
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
$selector_options = get_selector_config($_SESSION['forumdata']['formdata']['_selector_type'], $_SESSION['forumdata']['formdata']['_title']);
$extras = $selector_options['extras'];
$opt_where = $selector_options['opt_where'];
$opt = $selector_options['opt'];

switch($_SESSION['forumdata']['formdata']['_selector_type']) {
    case "member":
        $usersextras = $extras;
        break;
}
// --------- End Selektor config ---------

$selector_name = $_SESSION['forumdata']['formdata']['_selector_name'];
if (isset($delete_selector_filters)) $filters[$selector_name] = array();
require_once(LIB_PATH."/selector/class.selector.php");

// new Selektor
switch ($_SESSION['forumdata']['formdata']['_selector_type']) {
    case "member":
        $sel = new PHProjektSelector($selector_name, 'users', $opt, 'multiple', 'select');
        break;
}    

$sel->finishFormSubmitName = 'finishForm.'.$_SESSION['forumdata']['formdata']['_return'];
$sel->set_hidden_fields(array(  'ID' => $_SESSION['forumdata']['formdata']['_ID'],
                                'fID' => $_SESSION['forumdata']['formdata']['_fID'],
                                'mode' => $_SESSION['forumdata']['formdata']['_mode'],
                                'view' => $_SESSION['forumdata']['formdata']['_view']));

// get the selected values
if (!isset($stuff['preselect'])) {
    $stuff['preselect'] = array();
    if (is_array($_SESSION['forumdata']['formdata']['_selector'])) {
        foreach ($_SESSION['forumdata']['formdata']['_selector'] as $tmp_id) {
            if ($_SESSION['forumdata']['formdata']['_selector_type'] == "member") {
                $tmp_id = slookup('users', 'ID', 'kurz', $tmp_id);
            }
            $stuff['preselect'][$tmp_id] = "on";
        }
    } else {
        $stuff['preselect'][$_SESSION['forumdata']['formdata']['_selector']] = "on";
    }
    unset($tmp_id);
}
require_once(LIB_PATH."/selector/selector_filter_operations.php");

// print all the stuff!
$sel->show_window($stuff['preselect'], 15, "./forum.php");
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

// persons
if (isset($_POST['persons'])) {
    $_SESSION['forumdata']['formdata']['persons'] = xss_array($_POST['persons']);
    $_SESSION['forumdata']['formdata']['acc']     = xss($_POST['acc']);
}
if (isset($_SESSION['forumdata']['formdata']['persons'])) {
    foreach($_SESSION['forumdata']['formdata']['persons'] as $k => $v) {
        echo "<input type='hidden' name='persons[]' value='".xss($v)."' />\n";
    }
    echo "<input type='hidden' name='acc' value='".xss($_SESSION['forumdata']['formdata']['acc'])."' />\n";
}

// titel
if (isset($_POST['titel'])) {
    $_SESSION['forumdata']['formdata']['titel'] = xss($_POST['titel']);
}
if (isset($_SESSION['forumdata']['formdata']['titel'])) {
    echo "<input type='hidden' name='titel' value='".xss($_SESSION['forumdata']['formdata']['titel'])."' />\n";
}

// remark
if (isset($_POST['remark'])) {
    $_SESSION['forumdata']['formdata']['remark'] = xss($_POST['remark']);
}
if (isset($_SESSION['forumdata']['formdata']['remark'])) {
    echo "<input type='hidden' name='remark' value='".xss($_SESSION['forumdata']['formdata']['remark'])."' />\n";
}

// notify_others
if (isset($_POST['notify_others'])) {
    $_SESSION['forumdata']['formdata']['notify_others'] = xss($_POST['notify_others']);
}
if (isset($_SESSION['forumdata']['formdata']['notify_others'])) {
    echo "<input type='hidden' name='notify_others' value='".xss($_SESSION['forumdata']['formdata']['notify_others'])."' />\n";
}

// notify_me
if (isset($_POST['notify_me'])) {
    $_SESSION['forumdata']['formdata']['notify_me'] = xss($_POST['notify_me']);
}
if (isset($_SESSION['forumdata']['formdata']['notify_me'])) {
    echo "<input type='hidden' name='notify_me' value='".xss($_SESSION['forumdata']['formdata']['notify_me'])."' />\n";
}

// new
if ($_SESSION['forumdata']['formdata']['_fID'] == '') {
    echo "<input type='hidden' name='newfor' value='new' />\n";
// newbei
} else if ($_SESSION['forumdata']['formdata']['_ID'] == '') {
    echo "<input type='hidden' name='newbei' value='New posting' />\n";
}

$keys = array_keys($stuff['preselect']);
if ($keys[0] == '') $disabled = 'disabled="true"';
else                $disabled = '';

echo get_buttons(array(array("type" => "submit", "name" => $_SESSION['forumdata']['formdata']['_return'], "value" => __('OK'), "extrahtml" => 'id="submit_selector" '.$disabled)));
echo get_buttons(array(array("type" => "submit", "name" => $_SESSION['forumdata']['formdata']['_cancel'], "value" => __('Cancel'))));
echo "</form>\n";

echo "</div>\n";
// ---------- Finishform end ---------
?>
