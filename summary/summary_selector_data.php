<?php

/*
 * This file check the $_REQUEST for see what selector was clicked
 * In first case, (Form to Selector) the data are stored on the SESSION and call the selector file.
 * In second case, (Selector to Form) get the selected data and continue with the form.
 * Author: Gustavo Solt gustavo.solt@gmail.com
 */
 
 // check whether the lib has been included - authentication!
if (!defined('lib_included')) die('Please use index.php!');
 
// Common values
$formdata['_ID']       = isset($ID) ? (int) $ID : 0;
$formdata['_view']     = isset($view) ? qss($view) : '';
$formdata['_mode']     = 'forms';
$formdata['_selector_name'] = 'summary_selector_';

// Selector for Members (Related Object - TODO - von)
if ( isset($_REQUEST['action_form_to_list_von_selector']) ||
     isset($_REQUEST['action_form_to_list_von_selector_x'])) {
    $formdata['_selector_type'] = 'user';
    $formdata['_title']    = __('Member selection');
    $formdata['_selector'] = 'von';
    $formdata['_return']   = 'action_list_von_selector_to_form_ok';
    $formdata['_cancel']   = 'action_list_von_selector_to_form_cancel';
    $_SESSION['summarydata']['formdata'] = $formdata;
    $delete_selector_filters = true;

} else if ( isset($_REQUEST['action_list_von_selector_to_form_ok']) ||
            isset($_REQUEST['action_list_von_selector_to_form_cancel'])) {
    // back from selector (okay or cancel)
    if (isset($_REQUEST['action_list_von_selector_to_form_ok'])) {
        // pressed okay
        $selector = xss_array($_POST[$_SESSION['summarydata']['formdata']['_selector_name']."srcs"]);
        if (is_array($selector)) {
            put_filter_value('von','exact',$selector[0],'todo'); 
        }
    }
    // okay & cancel
    unset($_SESSION['summarydata']['formdata']);
    unset($_REQUEST['filterform']);

// Selector for Members (Related Object - TODO - to)
} else if ( isset($_REQUEST['action_form_to_list_ext_selector']) ||
            isset($_REQUEST['action_form_to_list_ext_selector_x'])) {
    $formdata['_selector_type'] = 'user';
    $formdata['_title']    = __('Member selection');
    $formdata['_selector'] = 'ext';
    $formdata['_return']   = 'action_list_ext_selector_to_form_ok';
    $formdata['_cancel']   = 'action_list_ext_selector_to_form_cancel';
    $_SESSION['summarydata']['formdata'] = $formdata;
    $delete_selector_filters = true;

} else if ( isset($_REQUEST['action_list_ext_selector_to_form_ok']) ||
            isset($_REQUEST['action_list_ext_selector_to_form_cancel'])) {
    // back from selector (okay or cancel)
    if (isset($_REQUEST['action_list_ext_selector_to_form_ok'])) {
        // pressed okay
        $selector = xss_array($_POST[$_SESSION['summarydata']['formdata']['_selector_name']."srcs"]);
        put_filter_value('ext','exact',$selector[0],'todo'); 
    }
    // okay & cancel
    unset($_SESSION['summarydata']['formdata']);
    unset($_REQUEST['filterform']);

// Selector for project (Related Object - TODO - project)
} else if ( isset($_REQUEST['action_form_to_list_project_selector']) ||
            isset($_REQUEST['action_form_to_list_project_selector_x'])) {
    $formdata['_selector_type'] = 'one_project';
    $formdata['_title']    = __('Project selection');
    $formdata['_selector'] = 'project';
    $formdata['_return']   = 'action_list_project_selector_to_form_ok';
    $formdata['_cancel']   = 'action_list_project_selector_to_form_cancel';
    $_SESSION['summarydata']['formdata'] = $formdata;
    $delete_selector_filters = true;

} else if ( isset($_REQUEST['action_list_project_selector_to_form_ok']) ||
            isset($_REQUEST['action_list_project_selector_to_form_cancel'])) {
    // back from selector (okay or cancel)
    if (isset($_REQUEST['action_list_project_selector_to_form_ok'])) {
        // pressed okay
        $selector = xss_array($_POST[$_SESSION['summarydata']['formdata']['_selector_name']."srcs"]);
        put_filter_value('project','exact',$selector[0],'todo'); 
    }
    // okay & cancel
    unset($_SESSION['summarydata']['formdata']);
    unset($_REQUEST['filterform']);

// Selector for contacts (Related Object - NOTES - contact)
} else if ( isset($_REQUEST['action_form_to_list_notes_contact_selector']) ||
            isset($_REQUEST['action_form_to_list_notes_contact_selector_x'])) {
    $formdata['_selector_type'] = 'one_contact';
    $formdata['_title']    = __('Contact selection');
    $formdata['_selector'] = 'contact';
    $formdata['_return']   = 'action_list_notes_contact_selector_to_form_ok';
    $formdata['_cancel']   = 'action_list_notes_contact_selector_to_form_cancel';
    $_SESSION['summarydata']['formdata'] = $formdata;
    $delete_selector_filters = true;

} else if ( isset($_REQUEST['action_list_notes_contact_selector_to_form_ok']) ||
            isset($_REQUEST['action_list_notes_contact_selector_to_form_cancel'])) {
    // back from selector (okay or cancel)
    if (isset($_REQUEST['action_list_notes_contact_selector_to_form_ok'])) {
        // pressed okay
        $selector = xss_array($_POST[$_SESSION['summarydata']['formdata']['_selector_name']."srcs"]);
        put_filter_value('contact','exact',$selector[0],'notes'); 
    }
    // okay & cancel
    unset($_SESSION['summarydata']['formdata']);
    unset($_REQUEST['filterform']);

// Selector for project (Related Object - NOTES - project)
} else if ( isset($_REQUEST['action_form_to_list_projekt_selector']) ||
            isset($_REQUEST['action_form_to_list_projekt_selector_x'])) {
    $formdata['_selector_type'] = 'one_project';
    $formdata['_title']    = __('Project selection');
    $formdata['_selector'] = 'projekt';
    $formdata['_return']   = 'action_list_projekt_selector_to_form_ok';
    $formdata['_cancel']   = 'action_list_projekt_selector_to_form_cancel';
    $_SESSION['summarydata']['formdata'] = $formdata;
    $delete_selector_filters = true;

} else if ( isset($_REQUEST['action_list_projekt_selector_to_form_ok']) ||
            isset($_REQUEST['action_list_projekt_selector_to_form_cancel'])) {
    // back from selector (okay or cancel)
    if (isset($_REQUEST['action_list_projekt_selector_to_form_ok'])) {
        // pressed okay
        $selector = xss_array($_POST[$_SESSION['summarydata']['formdata']['_selector_name']."srcs"]);
        put_filter_value('projekt','exact',$selector[0],'notes'); 
    }
    // okay & cancel
    unset($_SESSION['summarydata']['formdata']);
    unset($_REQUEST['filterform']);

// Selector for contacts (Related Object - FILEMANAGER - contact)
} else if ( isset($_REQUEST['action_form_to_list_dateien_contact_selector']) ||
            isset($_REQUEST['action_form_to_list_dateien_contact_selector_x'])) {
    $formdata['_selector_type'] = 'one_contact';
    $formdata['_title']    = __('Contact selection');
    $formdata['_selector'] = 'contact';
    $formdata['_return']   = 'action_list_dateien_contact_selector_to_form_ok';
    $formdata['_cancel']   = 'action_list_dateien_contact_selector_to_form_cancel';
    $_SESSION['summarydata']['formdata'] = $formdata;
    $delete_selector_filters = true;

} else if ( isset($_REQUEST['action_list_dateien_contact_selector_to_form_ok']) ||
            isset($_REQUEST['action_list_dateien_contact_selector_to_form_cancel'])) {
    // back from selector (okay or cancel)
    if (isset($_REQUEST['action_list_dateien_contact_selector_to_form_ok'])) {
        // pressed okay
        $selector = xss_array($_POST[$_SESSION['summarydata']['formdata']['_selector_name']."srcs"]);
        put_filter_value('contact','exact',$selector[0],'dateien'); 
    }
    // okay & cancel
    unset($_SESSION['summarydata']['formdata']);
    unset($_REQUEST['filterform']);

// Selector for project (Related Object - FILEMANAGER - div2)
} else if ( isset($_REQUEST['action_form_to_list_div2_selector']) ||
            isset($_REQUEST['action_form_to_list_div2_selector_x'])) {
    $formdata['_selector_type'] = 'one_project';
    $formdata['_title']    = __('Project selection');
    $formdata['_selector'] = 'div2';
    $formdata['_return']   = 'action_list_div2_selector_to_form_ok';
    $formdata['_cancel']   = 'action_list_div2_selector_to_form_cancel';
    $_SESSION['summarydata']['formdata'] = $formdata;
    $delete_selector_filters = true;

} else if ( isset($_REQUEST['action_list_div2_selector_to_form_ok']) ||
            isset($_REQUEST['action_list_div2_selector_to_form_cancel'])) {
    // back from selector (okay or cancel)
    if (isset($_REQUEST['action_list_div2_selector_to_form_ok'])) {
        // pressed okay
        $selector = xss_array($_POST[$_SESSION['summarydata']['formdata']['_selector_name']."srcs"]);
        put_filter_value('div2','exact',$selector[0],'dateien'); 
    }
    // okay & cancel
    unset($_SESSION['summarydata']['formdata']);
    unset($_REQUEST['filterform']);

// Selector for Members (Related Object - HELPDESK - user)
} else if ( isset($_REQUEST['action_form_to_list_assigned_selector']) ||
            isset($_REQUEST['action_form_to_list_assigned_selector_x'])) {
    $formdata['_selector_type'] = 'user';
    $formdata['_title']    = __('Member selection');
    $formdata['_selector'] = 'assigned';
    $formdata['_return']   = 'action_list_assigned_selector_to_form_ok';
    $formdata['_cancel']   = 'action_list_assigned_selector_to_form_cancel';
    $_SESSION['summarydata']['formdata'] = $formdata;
    $delete_selector_filters = true;

} else if ( isset($_REQUEST['action_list_assigned_selector_to_form_ok']) ||
            isset($_REQUEST['action_list_assigned_selector_to_form_cancel'])) {
    // back from selector (okay or cancel)
    if (isset($_REQUEST['action_list_assigned_selector_to_form_ok'])) {
        // pressed okay
        $selector = xss_array($_POST[$_SESSION['summarydata']['formdata']['_selector_name']."srcs"]);
        if (is_array($selector)) {
            put_filter_value('assigned','exact',$selector[0],'helpdesk'); 
        }
    }
    // okay & cancel
    unset($_SESSION['summarydata']['formdata']);
    unset($_REQUEST['filterform']);
}
?>
