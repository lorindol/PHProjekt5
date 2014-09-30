<?php
/**
* filters for the selector
*
* @package    selector
* @module     main
* @author     Franz Graf, Gustavo Solt, $Author: albrecht $
* @licence    GPL, see www.gnu.org/copyleft/gpl.html
* @copyright  2000-2006 Mayflower GmbH www.mayflower.de
* @version    $Id: selector_filter_operations.php,v 1.17 2006/08/22 08:05:49 albrecht Exp $
*/
if (!defined('lib_included')) die('Please use index.php!');


$sd = array();
if (is_array($part_personen)) {
    foreach($part_personen as $pcid)
    $sd['usr'.$pcid] = "on";
}
if (is_array($part_contacts)) {
    foreach($part_contacts as $pcid)
    $sd['con'.$pcid] = "on";
}

$_SESSION['filters'] =& $filters;
if (!isset($stuff) && isset($_POST['sthis'])) {
    $stuff = unserialize(urldecode(xss($_POST['sthis'])));
}
if (!isset($stuff) && isset($_SESSION['sthis1']) && isset($selector_name)) {
    $stuff = $_SESSION['sthis1'][$selector_name];
}

// this is an absolutely mad hack to get preselected items
// from the form via GET, when a filter is deleted :-/
// this has to be transformed in a POST var... urghs
if (isset($_GET['preselect']) && $_GET['preselect'] != '') {
    $preselect_from_get = explode('-', $_GET['preselect']);
    if ($sel->type == "multiple") {
        foreach ($preselect_from_get as $val) {
            $_POST[$sel->name."dsts"][] = (int) xss($val);
        }
    } else {
        $_POST[$sel->name."srcs"][] = (int) xss($preselect_from_get[0]);
    }
    unset($_GET['preselect']);
}

// Filter
if (isset($filterdel)) {
    $_SESSION['filters'] =& $filters;
    $sarr =& $filters[$sel->name];
    // delete all filter on '-1'
    if ($filterdel == '-1') $sarr = array();
    else                    unset($sarr[$filterdel]);
    $stuff['preselect'] = $sel->get_chosen();
} else if (isset($_POST['textfilterstring'])) {
    $textfilterstring = xss($_POST['textfilterstring']);
    if ($textfilterstring != '') {
        $textfilter       = qss(xss($_POST['textfilter']));
        $textfiltermode   = qss(xss($_POST['textfiltermode']));
        $prse = $sel->datasource."parse_filters";
        $prse($sel);
        $stuff['preselect'] = $sel->get_chosen();
    }
}
if (isset($filterform) && $filterform == "done") {
    // no new filter is set, but we might have used some extras
    $stuff['preselect'] = $sel->get_chosen();
    foreach (${$sel->datasource.'extras'} as $val) {
        foreach ($val['formname'] as $formname) {
            if (isset(${$formname}) && !empty(${$formname})) {
                $filter_answer = $val['evalform']($sel->sourcedata);
            }
        }
    }
}
// actualize after submit

if (!isset($_SESSION[$selector_name])) {
    $_SESSION[$selector_name]['data'] = $stuff['preselect'];
    $_SESSION[$selector_name]['javascript'] = true;
} else if (isset($stuff['preselect']) && !empty($stuff['preselect'])) {
    unset($_SESSION[$selector_name]['data']);
    foreach ($stuff['preselect'] as $tmp_val => $str) {
        $_SESSION[$selector_name]['data'][$tmp_val] = "on";
    }
}

// if the values of the move buttons are submitted, javascript was not 
// active. 
if (isset($_POST['movsrcdst']) or isset($_POST['movdstsrc']) or !$_SESSION[$selector_name]['javascript']) {
    $_SESSION[$selector_name]['javascript'] = false;

    // Add: the entries from the left select box are valid and needs to be added to the selected entries in the session
    if (isset($_POST['movsrcdst']) && isset($_POST[$selector_name.'srcs'])) {
        foreach ($_POST[$selector_name.'srcs'] as $tmp_val) {
            $_SESSION[$selector_name]['data'][$tmp_val] = "on";
        }
    }
    unset($tmp_val);
    // Remove: Entries from the right box should be removed 
    if (isset($_POST['movdstsrc']) && isset($_POST[$selector_name.'dsts'])) {
        foreach ($_POST[$selector_name.'dsts'] as $tmp_val) {
            unset($_SESSION[$selector_name]['data'][$tmp_val]);
        }
    }
    unset($tmp_val);
    // write current data back into the session
    $stuff['preselect'] = $_SESSION[$selector_name]['data'];
}
?>
