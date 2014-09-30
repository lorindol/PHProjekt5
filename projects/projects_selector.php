<?php

// check whether the lib has been included - authentication!
if (!defined("lib_included")) die("Please use index.php!");

require_once(LIB_PATH."/selector/selector.inc.php");

$tabs = array();
// form start
echo '<div id="global-header">';
echo get_tabs_area($tabs);
echo '</div>';
echo '<div id="global-content">'."\n";

// --------- Selektor config ---------
$selector_options = get_selector_config($_SESSION['projectdata']['formdata']['_selector_type'], $_SESSION['projectdata']['formdata']['_title']);
$extras = $selector_options['extras'];
$opt_where = $selector_options['opt_where'];
$opt = $selector_options['opt'];
switch($_SESSION['projectdata']['formdata']['_selector_type']) {
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

$selector_name = $_SESSION['projectdata']['formdata']['_selector_name'];
if (isset($delete_selector_filters)) $filters[$selector_name] = array();
require_once(LIB_PATH."/selector/class.selector.php");

// new Selektor
switch ($_SESSION['projectdata']['formdata']['_selector_type']) {
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

$sel->finishFormSubmitName = 'finishForm.'.$_SESSION['projectdata']['formdata']['_return'];
$sel->set_hidden_fields(array(  'ID'        => $_SESSION['projectdata']['formdata']['_ID'],
                                'mode'      => $_SESSION['projectdata']['formdata']['_mode'],
                                'view'      => $_SESSION['projectdata']['formdata']['_view'],
                                'justform'  => $justform));

// get the selected values
if (!isset($stuff['preselect'])) {
    $stuff['preselect'] = array();
    if (is_array($_SESSION['projectdata']['formdata']['_selector'])) {
        foreach ($_SESSION['projectdata']['formdata']['_selector'] as $tmp_id) {
            if ($_SESSION['projectdata']['formdata']['_selector_type'] == "member") {
                // Assignment
                if (!isset($_POST['personen'])) {
                    $tmp_id = slookup('users', 'ID', 'kurz', $tmp_id);
                }
            }
            $stuff['preselect'][$tmp_id] = "on";
        }
    } else {
        $stuff['preselect'][$_SESSION['projectdata']['formdata']['_selector']] = "on";
    }
    unset($tmp_id);
}
require_once(LIB_PATH."/selector/selector_filter_operations.php");

// print all the stuff!
$sel->show_window($stuff['preselect'], 15, "./projects.php");
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

// justform
if ($justform) $_SESSION['projectdata']['formdata']['justform'] = $justform;
if (isset($_SESSION['projectdata']['formdata']['justform']) && ($_SESSION['projectdata']['formdata']['justform'])) {
    echo "<input type='hidden' name='justform' value='1' />\n";
}

// all form fields 
global $fields;
if (isset($_REQUEST['filterform']) || isset($_REQUEST['filterdel'])) {
    $fields = $_SESSION['projectdata']['formdata']['fields'];
} else {
    $_SESSION['projectdata']['formdata']['fields'] = xss_array($fields);
}
if (is_array($fields) && count($fields) > 0) {
    foreach($fields as $field_name => $field_array) {
        echo "<input type='hidden' name='".xss($field_name)."' value='".xss($field_array['value'])."' />\n";
    }
}
// persons
if (isset($_POST['persons'])) {
    $_SESSION['projectdata']['formdata']['persons']   = xss_array($_POST['persons']);
    $_SESSION['projectdata']['formdata']['acc']       = xss($_POST['acc']);
    $_SESSION['projectdata']['formdata']['acc_write'] = xss($_POST['acc_write']);
}
if (isset($_SESSION['projectdata']['formdata']['persons'])) {
    foreach($_SESSION['projectdata']['formdata']['persons'] as $k => $v) {
        echo "<input type='hidden' name='persons[]' value='".xss($v)."' />\n";
    }
    echo "<input type='hidden' name='acc' value='".xss($_SESSION['projectdata']['formdata']['acc'])."' />\n";
    echo "<input type='hidden' name='acc_write' value='".xss($_SESSION['projectdata']['formdata']['acc_write'])."' />\n";
}

// parent
if (isset($_POST['parent'])) {
    $_SESSION['projectdata']['formdata']['parent'] = xss($_POST['parent']);
}
if (isset($_SESSION['projectdata']['formdata']['parent'])) {
    echo "<input type='hidden' name='parent' value='".xss($_SESSION['projectdata']['formdata']['parent'])."' />\n";
}

// personen
if (isset($_POST['personen'])) {
    $_SESSION['projectdata']['formdata']['personen'] = xss_array($_POST['personen']);
    foreach($_POST['personen'] as $k => $v) {
        if (!empty($_POST['u_'.$v.'_text_role'])) {
            $_SESSION['projectdata']['formdata']['u_'.$v.'_role'] = xss($_POST['u_'.$v.'_text_role']);
        } else {
            $_SESSION['projectdata']['formdata']['u_'.$v.'_role'] = xss($_POST['u_'.$v.'_role']);
        }
    }
}
if (isset($_SESSION['projectdata']['formdata']['personen'])) {
    foreach($_SESSION['projectdata']['formdata']['personen'] as $k => $v) {
        echo "<input type='hidden' name='personen[]' value='".xss($v)."' />\n";
        echo "<input type='hidden' name='u_".$v."_role' value='".xss($_SESSION['projectdata']['formdata']['u_'.$v.'_role'])."' />\n";
    }
}

// contact_personen
if (isset($_POST['contact_personen'])) {
    $_SESSION['projectdata']['formdata']['contact_personen'] = xss_array($_POST['contact_personen']);
    foreach($_POST['contact_personen'] as $k => $v) {
        if (!empty($_POST['c_'.$v.'_text_role'])) {
            $_SESSION['projectdata']['formdata']['c_'.$v.'_role'] = xss($_POST['c_'.$v.'_text_role']);
        } else {
            $_SESSION['projectdata']['formdata']['c_'.$v.'_role'] = xss($_POST['c_'.$v.'_role']);
        }
    }
}
if (isset($_SESSION['projectdata']['formdata']['contact_personen'])) {
    foreach($_SESSION['projectdata']['formdata']['contact_personen'] as $k => $v) {
        echo "<input type='hidden' name='contact_personen[]' value='".xss($v)."' />\n";
        echo "<input type='hidden' name='c_".$v."_role' value='".xss($_SESSION['projectdata']['formdata']['c_'.$v.'_role'])."' />\n";
    }
}

$keys = array_keys($stuff['preselect']);
if ($keys[0] == '') $disabled = 'disabled="true"';
else                $disabled = '';

echo get_buttons(array(array("type" => "submit", "name" => $_SESSION['projectdata']['formdata']['_return'], "value" => __('OK'), "extrahtml" => 'id="submit_selector" '.$disabled)));
echo get_buttons(array(array("type" => "submit", "name" => $_SESSION['projectdata']['formdata']['_cancel'], "value" => __('Cancel'))));
echo "</form>\n";

echo "</div>\n";
// ---------- Finishform end ---------
?>
