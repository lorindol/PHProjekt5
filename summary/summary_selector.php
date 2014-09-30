<?php

// check whether the lib has been included - authentication!
if (!defined('lib_included')) die('Please use summary.php!');

require_once(LIB_PATH."/selector/selector.inc.php");

$tabs = array();
// form start
echo '<div id="global-header">';
echo get_tabs_area($tabs);
echo '</div>';
echo '<div id="global-content">'."\n";

// --------- Selektor config ---------
$selector_options = get_selector_config($_SESSION['summarydata']['formdata']['_selector_type'], $_SESSION['summarydata']['formdata']['_title']);
$extras = $selector_options['extras'];
$opt_where = $selector_options['opt_where'];
$opt = $selector_options['opt'];

switch($_SESSION['summarydata']['formdata']['_selector_type']) {
    case "contact":
    case "one_contact":
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

$selector_name = $_SESSION['summarydata']['formdata']['_selector_name'];
if (isset($delete_selector_filters)) $filters[$selector_name] = array();
require_once(LIB_PATH."/selector/class.selector.php");

// new Selektor
switch ($_SESSION['summarydata']['formdata']['_selector_type']) {
    case "contact":
        $sel = new PHProjektSelector($selector_name, 'contacts', $opt, 'multiple', 'select');
        break;
    case "one_contact":
        $sel = new PHProjektSelector($selector_name, 'contacts', $opt, 'single', 'select');
        break;
    case "project":
    case "one_project":
        $sel = new PHProjektSelector($selector_name, 'projects', $opt, 'single', 'select');
        break;
    case "member":
        $sel = new PHProjektSelector($selector_name, 'users', $opt, 'multiple', 'select');
        break;
    case "user":
        $sel = new PHProjektSelector($selector_name, 'users', $opt, 'single', 'select');
        break;
}    

$sel->finishFormSubmitName = 'finishForm.'.$_SESSION['summarydata']['formdata']['_return'];
$sel->set_hidden_fields(array(  'ID' => $_SESSION['summarydata']['formdata']['_ID'],
                                'mode' => $_SESSION['summarydata']['formdata']['_mode'],
                                'view' => $_SESSION['summarydata']['formdata']['_view']));

if (!isset($stuff['preselect'])) {
    $stuff['preselect'] = array();

    // array of user selected in a form
    if ( isset($_POST[$_SESSION['summarydata']['formdata']['_selector']]) &&
         is_array($_POST[$_SESSION['summarydata']['formdata']['_selector']])) {
        foreach ($_POST[$_SESSION['summarydata']['formdata']['_selector']] as $tmp_id) {
            $tmp2_id = slookup('users', 'ID', 'kurz', $tmp_id);
            $stuff['preselect'][$tmp2_id] = "on";
        }
    }
    // array of contact selected in a form
    if ( isset($_POST['contact_personen']) &&
         is_array($_POST['contact_personen'])) {
        foreach ($_POST['contact_personen'] as $tmp_id) {
            $stuff['preselect'][$tmp_id] = "on";
        }
    }
    unset($tmp_id);
}

require_once(LIB_PATH."/selector/selector_filter_operations.php");

// print all the stuff!
$sel->show_window($stuff['preselect'], 15, "./summary.php");
// ---------- Selektor end ---------


// ---------- Finishform begin ---------
echo "
<br />
<br />
";
// values of the form
if (SID) {
    echo "<input type='hidden' name='".session_name()."' value='".session_id()."' />\n";
}

// persons
if (isset($_POST['persons'])) {
    $_SESSION['summarydata']['formdata']['persons'] = xss_array($_POST['persons']);
    $_SESSION['summarydata']['formdata']['acc']     = xss($_POST['acc']);
}
if (isset($_SESSION['summarydata']['formdata']['persons'])) {
    foreach($_SESSION['summarydata']['formdata']['persons'] as $k => $v) {
        echo "<input type='hidden' name='persons[]' value='".xss($v)."' />\n";
    }
    echo "<input type='hidden' name='acc' value='".xss($_SESSION['summarydata']['formdata']['acc'])."' />\n";
}

// parent
if (isset($_POST['parent'])) {
    $_SESSION['summarydata']['formdata']['parent'] = xss($_POST['parent']);
}
if (isset($_SESSION['summarydata']['formdata']['parent'])) {
    echo "<input type='hidden' name='parent' value='".xss($_SESSION['summarydata']['formdata']['parent'])."' />\n";
}

// personen
if (isset($_POST['personen'])) {
    $_SESSION['summarydata']['formdata']['personen'] = xss_array($_POST['personen']);
}
if (isset($_SESSION['summarydata']['formdata']['personen'])) {
    foreach($_SESSION['summarydata']['formdata']['personen'] as $k => $v) {
        echo "<input type='hidden' name='personen[]' value='".xss($v)."' />\n";
    }
}

// contact_personen
if (isset($_POST['contact_personen'])) {
    $_SESSION['summarydata']['formdata']['contact_personen'] = xss_array($_POST['contact_personen']);
}
if (isset($_SESSION['summarydata']['formdata']['contact_personen'])) {
    foreach($_SESSION['summarydata']['formdata']['contact_personen'] as $k => $v) {
        echo "<input type='hidden' name='contact_personen[]' value='".xss($v)."' />\n";
    }
}

$keys = array_keys($stuff['preselect']);
if ($keys[0] == '') $disabled = 'disabled="true"';
else                $disabled = '';

echo get_buttons(array(array("type" => "submit", "name" => $_SESSION['summarydata']['formdata']['_return'], "value" => __('OK'), "extrahtml" => 'id="submit_selector" '.$disabled)));
echo get_buttons(array(array("type" => "submit", "name" => $_SESSION['summarydata']['formdata']['_cancel'], "value" => __('Cancel'))));
echo "</form>\n";

echo "</div>\n";
// ---------- Finishform end ---------
?>
