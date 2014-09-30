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
$selector_options = get_selector_config($_SESSION['maildata']['formdata']['_selector_type'], $_SESSION['maildata']['formdata']['_title']);
$extras = $selector_options['extras'];
$opt_where = $selector_options['opt_where'];
$opt = $selector_options['opt'];
switch($_SESSION['maildata']['formdata']['_selector_type']) {
    case "contact_multiple":
    case "contact_one":
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

$selector_name = $_SESSION['maildata']['formdata']['_selector_name'];
if (isset($delete_selector_filters)) $filters[$selector_name] = array();
require_once(LIB_PATH."/selector/class.selector.php");

// new Selektor
switch ($_SESSION['maildata']['formdata']['_selector_type']) {
    case "contact_multiple":
        $sel = new PHProjektSelector($selector_name, 'contacts', $opt, 'multiple', 'select');
        break;
    case "contact_one":
        $sel = new PHProjektSelector($selector_name, 'contacts', $opt, 'single', 'select');
        break;
    case "member":
        $sel = new PHProjektSelector($selector_name, 'users', $opt, 'multiple', 'select');
        break;
    case "project":
        $sel = new PHProjektSelector($selector_name, 'projects', $opt, 'single', 'select');
        break;
}    

$sel->finishFormSubmitName = 'finishForm.'.$_SESSION['maildata']['formdata']['_return'];
$sel->set_hidden_fields(array(  'form' => $_SESSION['maildata']['formdata']['_form'],
                                'mode' => $_SESSION['maildata']['formdata']['_mode'],
                                'view' => $_SESSION['maildata']['formdata']['_view']));

// get the selected values
if (!isset($stuff['preselect'])) {
    $stuff['preselect'] = array();
    
    if (is_array($_SESSION['maildata']['formdata']['_selector'])) {
        foreach ($_SESSION['maildata']['formdata']['_selector'] as $tmp_id) {
            $stuff['preselect'][$tmp_id] = "on";
        }
    } else {
        $stuff['preselect'][$_SESSION['maildata']['formdata']['_selector']] = "on";
    }
    unset($tmp_id);
}
require_once(LIB_PATH."/selector/selector_filter_operations.php");

// print all the stuff!
$sel->show_window($stuff['preselect'], 15, "./mail.php");
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

// subj
if (isset($_POST['subj'])) {
    $_SESSION['maildata']['formdata']['subj'] = xss($_POST['subj']);
}
if (isset($_SESSION['maildata']['formdata']['subj'])) {
    echo "<input type='hidden' name='subj' value='".xss($_SESSION['maildata']['formdata']['subj'])."' />\n";
}

// body
if (isset($_POST['body'])) {
    $_SESSION['maildata']['formdata']['body'] = xss($_POST['body']);
}
if (isset($_SESSION['maildata']['formdata']['body'])) {
    echo "<input type='hidden' name='body' value='".xss($_SESSION['maildata']['formdata']['body'])."' />\n";
}

// placehold
if (isset($_POST['placehold'])) {
    $_SESSION['maildata']['formdata']['placehold'] = xss($_POST['placehold']);
}
if (isset($_SESSION['maildata']['formdata']['placehold'])) {
    echo "<input type='hidden' name='placehold' value='".xss($_SESSION['maildata']['formdata']['placehold'])."' />\n";
}

// receipt
if (isset($_POST['receipt'])) {
    $_SESSION['maildata']['formdata']['receipt'] = xss($_POST['receipt']);
}
if (isset($_SESSION['maildata']['formdata']['receipt'])) {
    echo "<input type='hidden' name='receipt' value='".xss($_SESSION['maildata']['formdata']['receipt'])."' />\n";
}

// single
if (isset($_POST['single'])) {
    $_SESSION['maildata']['formdata']['single'] = xss($_POST['single']);
}
if (isset($_SESSION['maildata']['formdata']['single'])) {
    echo "<input type='hidden' name='single' value='".xss($_SESSION['maildata']['formdata']['single'])."' />\n";
}

// additional_fax
if (isset($_POST['additional_fax'])) {
    $_SESSION['maildata']['formdata']['additional_fax'] = xss($_POST['additional_fax']);
}
if (isset($_SESSION['maildata']['formdata']['additional_fax'])) {
    echo "<input type='hidden' name='additional_fax' value='".xss($_SESSION['maildata']['formdata']['additional_fax'])."' />\n";
}

// additional_mail
if (isset($_POST['additional_mail'])) {
    $_SESSION['maildata']['formdata']['additional_mail'] = xss($_POST['additional_mail']);
}
if (isset($_SESSION['maildata']['formdata']['additional_mail'])) {
    echo "<input type='hidden' name='additional_mail' value='".xss($_SESSION['maildata']['formdata']['additional_mail'])."' />\n";
}

// cc
if (isset($_POST['cc'])) {
    $_SESSION['maildata']['formdata']['cc'] = xss($_POST['cc']);
}
if (isset($_SESSION['maildata']['formdata']['cc'])) {
    echo "<input type='hidden' name='cc' value='".xss($_SESSION['maildata']['formdata']['cc'])."' />\n";
}

// bcc
if (isset($_POST['bcc'])) {
    $_SESSION['maildata']['formdata']['bcc'] = xss($_POST['bcc']);
}
if (isset($_SESSION['maildata']['formdata']['bcc'])) {
    echo "<input type='hidden' name='bcc' value='".xss($_SESSION['maildata']['formdata']['bcc'])."' />\n";
}

// token field
echo "<input type='hidden' name='csrftoken' value='".make_csrftoken()."' />\n";

// ID
if (isset($_POST['ID'])) {
    $_SESSION['maildata']['formdata']['ID'] = xss($_POST['ID']);
}
if (isset($_SESSION['maildata']['formdata']['ID'])) {
    echo "<input type='hidden' name='ID' value='".xss($_SESSION['maildata']['formdata']['ID'])."' />\n";
}

// aendern
if (isset($_POST['aendern'])) {
    $_SESSION['maildata']['formdata']['aendern'] = xss($_POST['aendern']);
}
if (isset($_SESSION['maildata']['formdata']['aendern'])) {
    echo "<input type='hidden' name='aendern' value='".xss($_SESSION['maildata']['formdata']['aendern'])."' />\n";
}

// aendern
if (isset($_POST['make'])) {
    $_SESSION['maildata']['formdata']['make'] = xss($_POST['make']);
}
if (isset($_SESSION['maildata']['formdata']['make'])) {
    echo "<input type='hidden' name='make' value='".xss($_SESSION['maildata']['formdata']['make'])."' />\n";
}

// typ
if (isset($_POST['typ'])) {
    $_SESSION['maildata']['formdata']['typ'] = xss($_POST['typ']);
}
if (isset($_SESSION['maildata']['formdata']['typ'])) {
    echo "<input type='hidden' name='typ' value='".xss($_SESSION['maildata']['formdata']['typ'])."' />\n";
}

// up
if (isset($_POST['up'])) {
    $_SESSION['maildata']['formdata']['up'] = xss($_POST['up']);
}
if (isset($_SESSION['maildata']['formdata']['up'])) {
    echo "<input type='hidden' name='up' value='".xss($_SESSION['maildata']['formdata']['up'])."' />\n";
}

// sort
if (isset($_POST['sort'])) {
    $_SESSION['maildata']['formdata']['sort'] = xss($_POST['sort']);
}
if (isset($_SESSION['maildata']['formdata']['sort'])) {
    echo "<input type='hidden' name='sort' value='".xss($_SESSION['maildata']['formdata']['sort'])."' />\n";
}

// perpage
if (isset($_POST['perpage'])) {
    $_SESSION['maildata']['formdata']['perpage'] = xss($_POST['perpage']);
}
if (isset($_SESSION['maildata']['formdata']['perpage'])) {
    echo "<input type='hidden' name='perpage' value='".xss($_SESSION['maildata']['formdata']['perpage'])."' />\n";
}

// page
if (isset($_POST['page'])) {
    $_SESSION['maildata']['formdata']['page'] = xss($_POST['page']);
}
if (isset($_SESSION['maildata']['formdata']['page'])) {
    echo "<input type='hidden' name='page' value='".xss($_SESSION['maildata']['formdata']['page'])."' />\n";
}

// page
if (isset($_POST['filter'])) {
    $_SESSION['maildata']['formdata']['filter'] = xss($_POST['filter']);
}
if (isset($_SESSION['maildata']['formdata']['filter'])) {
    echo "<input type='hidden' name='filter' value='".xss($_SESSION['maildata']['formdata']['filter'])."' />\n";
}

// keyword
if (isset($_POST['keyword'])) {
    $_SESSION['maildata']['formdata']['keyword'] = xss($_POST['keyword']);
}
if (isset($_SESSION['maildata']['formdata']['keyword'])) {
    echo "<input type='hidden' name='keyword' value='".xss($_SESSION['maildata']['formdata']['keyword'])."' />\n";
}

// c_m
if (isset($_POST['c_m'])) {
    $_SESSION['maildata']['formdata']['c_m'] = xss($_POST['c_m']);
}
if (isset($_SESSION['maildata']['formdata']['c_m'])) {
    echo "<input type='hidden' name='c_m' value='".xss($_SESSION['maildata']['formdata']['c_m'])."' />\n";
}

// parent
if (isset($_POST['parent'])) {
    $_SESSION['maildata']['formdata']['parent'] = xss($_POST['parent']);
}
if (isset($_SESSION['maildata']['formdata']['parent'])) {
    echo "<input type='hidden' name='parent' value='".xss($_SESSION['maildata']['formdata']['parent'])."' />\n";
}

// remark
if (isset($_POST['remark'])) {
    $_SESSION['maildata']['formdata']['remark'] = xss($_POST['remark']);
}
if (isset($_SESSION['maildata']['formdata']['remark'])) {
    echo "<input type='hidden' name='remark' value='".xss($_SESSION['maildata']['formdata']['remark'])."' />\n";
}

// kat
if (isset($_POST['kat'])) {
    $_SESSION['maildata']['formdata']['kat'] = xss($_POST['kat']);
}
if (isset($_SESSION['maildata']['formdata']['kat'])) {
    echo "<input type='hidden' name='kat' value='".xss($_SESSION['maildata']['formdata']['kat'])."' />\n";
}

// new_category
if (isset($_POST['new_category'])) {
    $_SESSION['maildata']['formdata']['new_category'] = xss($_POST['new_category']);
}
if (isset($_SESSION['maildata']['formdata']['new_category'])) {
    echo "<input type='hidden' name='new_category' value='".xss($_SESSION['maildata']['formdata']['new_category'])."' />\n";
}

// projekt
if (isset($_POST['projekt'])) {
    $_SESSION['maildata']['formdata']['projekt'] = xss($_POST['projekt']);
}
if (isset($_SESSION['maildata']['formdata']['projekt'])) {
    echo "<input type='hidden' name='projekt' value='".xss($_SESSION['maildata']['formdata']['projekt'])."' />\n";
}

// projekt
if (isset($_POST['contact'])) {
    $_SESSION['maildata']['formdata']['contact'] = xss($_POST['contact']);
}
if (isset($_SESSION['maildata']['formdata']['contact'])) {
    echo "<input type='hidden' name='contact' value='".xss($_SESSION['maildata']['formdata']['contact'])."' />\n";
}

// profil
if (isset($_POST['profil'])) {
    $_SESSION['maildata']['formdata']['profil'] = xss($_POST['profil']);
}
if (isset($_SESSION['maildata']['formdata']['profil'])) {
    echo "<input type='hidden' name='profil' value='".xss($_SESSION['maildata']['formdata']['profil'])."' />\n";
}

// mem
if (isset($_POST['mem'])) {
    $_SESSION['maildata']['formdata']['mem'] = xss_array($_POST['mem']);
}
if (isset($_SESSION['maildata']['formdata']['mem'])) {
    foreach($_SESSION['maildata']['formdata']['mem'] as $k => $v) {
        echo "<input type='hidden' name='mem[]' value='".xss($v)."' />\n";
    }
}

// con
if (isset($_POST['con'])) {
    $_SESSION['maildata']['formdata']['con'] = xss_array($_POST['con']);
}
if (isset($_SESSION['maildata']['formdata']['con'])) {
    foreach($_SESSION['maildata']['formdata']['con'] as $k => $v) {
        echo "<input type='hidden' name='con[]' value='".xss($v)."' />\n";
    }
}

// dirname
if (isset($_POST['dirname'])) {
    $_SESSION['maildata']['formdata']['dirname'] = xss($_POST['dirname']);
}
if (isset($_SESSION['maildata']['formdata']['dirname'])) {
    echo "<input type='hidden' name='dirname' value='".xss($_SESSION['maildata']['formdata']['dirname'])."' />\n";
}
    
// all form fields
global $fields;
if (isset($_REQUEST['filterform']) || isset($_REQUEST['filterdel'])) {
    $fields = $_SESSION['maildata']['formdata']['fields'];
} else {
    $_SESSION['maildata']['formdata']['fields'] = xss_array($fields);
}
if (is_array($fields) && count($fields) > 0) {
    foreach($fields as $field_name => $field_array) {
        echo "<input type='hidden' name='".xss($field_name)."' value='".xss($field_array['value'])."' />\n";
    }
}

// persons
if (isset($_POST['persons'])) {
    $_SESSION['maildata']['formdata']['persons']    = xss_array($_POST['persons']);
    $_SESSION['maildata']['formdata']['acc']        = xss($_POST['acc']);
    $_SESSION['maildata']['formdata']['acc_write']  = xss($_POST['acc_write']);
}
if (isset($_SESSION['maildata']['formdata']['persons'])) {
    foreach($_SESSION['maildata']['formdata']['persons'] as $k => $v) {
        echo "<input type='hidden' name='persons[]' value='".xss($v)."' />\n";
    }
    echo "<input type='hidden' name='acc' value='".xss($_SESSION['maildata']['formdata']['acc'])."' />\n";
    echo "<input type='hidden' name='acc_write' value='".xss($_SESSION['maildata']['formdata']['acc_write'])."' />\n";
}

$keys = array_keys($stuff['preselect']);
if ($keys[0] == '') $disabled = 'disabled="true"';
else                $disabled = '';

echo get_buttons(array(array("type" => "submit", "name" => $_SESSION['maildata']['formdata']['_return'], "value" => __('OK'), "extrahtml" => 'id="submit_selector" '.$disabled)));
echo get_buttons(array(array("type" => "submit", "name" => $_SESSION['maildata']['formdata']['_cancel'], "value" => __('Cancel'))));
echo "</form>\n";

echo "</div>\n";
// ---------- Finishform end ---------
?>
