<?php

/*
 * This file check the $_REQUEST for see what selector was clicked
 * In first case, (Form to Selector) the data are stored on the SESSION and call the selector file.
 * In second case, (Selector to Form) get the selected data and continue with the form.
 * Author: Gustavo Solt gustavo.solt@gmail.com
 */
 
 if (!defined("lib_included")) die("Please use index.php!"); 
 
// Common values
$formdata['_ID']            = $ID;
$formdata['_view']          = isset($view) ? qss($view) : '';
$formdata['_form']          = isset($_POST['form']) ? xss($_POST['form']) : '';
$formdata['_selector_name'] = 'mail_selector_';

// Selector for contacts (Forms)
if (isset($_REQUEST['action_form_to_contact_selector']) ||
    isset($_REQUEST['action_form_to_contact_selector_x'])) {
    $formdata['_title']         = __('Contact selection');
    // contact for write mail
    if ($mode == "send") {
        $formdata['_selector_type'] = 'contact_multiple';
        $formdata['_selector']      = $con;
        $formdata['_mode']          = 'send_form';
    // contact for edit mail
    } else if ($mode == "data") {
        $formdata['_selector_type'] = 'contact_one';
        $formdata['_selector']      = $contact;
        $formdata['_mode']          = 'forms';
    }
    $formdata['_return']        = 'action_contact_selector_to_form_ok';
    $formdata['_cancel']        = 'action_contact_selector_to_form_cancel';
    $_SESSION['maildata']['formdata'] = $formdata;
    $delete_selector_filters = true;

} else if (isset($_REQUEST['action_contact_selector_to_form_ok']) ||
           isset($_REQUEST['action_contact_selector_to_form_cancel'])) {
    // back from selector (okay or cancel)
    if (isset($_REQUEST['action_contact_selector_to_form_ok'])) {
        // pressed okay
        // contact for write mail
        if ($_POST['mode'] == "send_form") {
            $selector = $_POST[$_SESSION['maildata']['formdata']['_selector_name']."dsts"];
            if (is_array($selector)) {
                $_SESSION['maildata']['formdata']['con'] = $selector;
            } else $_SESSION['maildata']['formdata']['con'] = array();
        // contact for edit mail
        } else if ($_POST['mode'] == "forms") {
                $selector = $_POST[$_SESSION['maildata']['formdata']['_selector_name']."srcs"];
                $_SESSION['maildata']['formdata']['contact'] = $selector[0];
        }
    }
    // okay & cancel
    $formdata = $_SESSION['maildata']['formdata'];
    unset($_SESSION['maildata']['formdata']);
    unset($_REQUEST['filterform']);
    
// Selector for Members (Form)
} else if (isset($_REQUEST['action_form_to_user_selector']) ||
           isset($_REQUEST['action_form_to_user_selector_x'])) {
    $formdata['_selector_type'] = 'member';
    $formdata['_title']         = __('User selection');
    $formdata['_selector']      = $mem;
    $formdata['_mode']          = 'send_form';
    $formdata['_return']        = 'action_user_selector_to_form_ok';
    $formdata['_cancel']        = 'action_user_selector_to_form_cancel';
    $_SESSION['maildata']['formdata'] = $formdata;
    $delete_selector_filters = true;

} else if (isset($_REQUEST['action_user_selector_to_form_ok']) ||
           isset($_REQUEST['action_user_selector_to_form_cancel'])) {
    // back from selector (okay or cancel)
    if (isset($_REQUEST['action_user_selector_to_form_ok'])) {
        $selector = xss_array($_POST[$_SESSION['maildata']['formdata']['_selector_name']."dsts"]);
        if (is_array($selector)) {
            $_SESSION['maildata']['formdata']['mem'] = $selector;
        } else $_SESSION['maildata']['formdata']['mem'] = array();
    }
    // okay & cancel
    $formdata = $_SESSION['maildata']['formdata'];
    unset($_SESSION['maildata']['formdata']);
    unset($_REQUEST['filterform']);

// Selector for Access (Form)
} else if (isset($_REQUEST['action_form_to_access_selector']) ||
           isset($_REQUEST['action_form_to_access_selector_x'])) {
    $formdata['_selector_type'] = 'member';
    $formdata['_title']         = __('Access selection');
    $formdata['_selector']      = $persons;
    $formdata['_mode']          = 'forms';
    $formdata['_return']        = 'action_access_selector_to_form_ok';
    $formdata['_cancel']        = 'action_access_selector_to_form_cancel';
    $_SESSION['maildata']['formdata'] = $formdata;
    $delete_selector_filters = true;
    
} else if (isset($_REQUEST['action_access_selector_to_form_ok']) ||
           isset($_REQUEST['action_access_selector_to_form_cancel'])) {
    // back from selector (okay or cancel)
    if (isset($_REQUEST['action_access_selector_to_form_ok'])) {
        // pressed okay
        $selector = xss_array($_POST[$_SESSION['maildata']['formdata']['_selector_name']."dsts"]);
        if (is_array($selector)) {
            foreach($selector as $k => $u_id) {
                $_SESSION['maildata']['formdata']['persons'][] = slookup('users','kurz','ID',$u_id,'1');
            }
        } else $_SESSION['maildata']['formdata']['persons'] = array();
    }
    // okay & cancel
    $formdata = $_SESSION['maildata']['formdata'];
    unset($_SESSION['maildata']['formdata']);
    unset($_REQUEST['filterform']);

// Selector for project (Form)
} else if (isset($_REQUEST['action_form_to_project_selector']) ||
           isset($_REQUEST['action_form_to_project_selector_x'])) {
    $formdata['_selector_type'] = 'project';
    $formdata['_title']    = __('Project selection');
    $formdata['_selector'] = $projekt;
    $formdata['_mode']     = 'forms';
    $formdata['_return']   = 'action_project_selector_to_form_ok';
    $formdata['_cancel']   = 'action_project_selector_to_form_cancel';
    $_SESSION['maildata']['formdata'] = $formdata;
    $delete_selector_filters = true;

} else if (isset($_REQUEST['action_project_selector_to_form_ok']) ||
           isset($_REQUEST['action_project_selector_to_form_cancel'])) {
    // back from selector (okay or cancel)
    if (isset($_REQUEST['action_project_selector_to_form_ok'])) {
        // pressed okay
        $selector = xss_array($_POST[$_SESSION['maildata']['formdata']['_selector_name']."srcs"]);
        $_SESSION['maildata']['formdata']['projekt'] = $selector[0];
    }
    // okay & cancel
    $formdata = $_SESSION['maildata']['formdata'];
    unset($_SESSION['maildata']['formdata']);
    unset($_REQUEST['filterform']);
}

// If is defined one of this selectors, call the selector file.
if (isset($_REQUEST['action_form_to_contact_selector']) ||
    isset($_REQUEST['action_form_to_contact_selector_x']) ||
    isset($_REQUEST['action_form_to_user_selector']) ||
    isset($_REQUEST['action_form_to_user_selector_x']) ||
    isset($_REQUEST['action_form_to_access_selector']) ||
    isset($_REQUEST['action_form_to_access_selector_x']) ||
    isset($_REQUEST['action_form_to_project_selector']) ||
    isset($_REQUEST['action_form_to_project_selector_x']) ||
    isset($_REQUEST['filterform']) ||
    isset($_REQUEST['filterdel'])) {
    $mode = 'selector';
}
?>
