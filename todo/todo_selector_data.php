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
$formdata['_ID']       = $ID;
$formdata['_view']     = isset($view) ? qss($view) : '';
$formdata['_selector_name'] = 'todo_selector_';
    
// Selector for Members (List view - von)
if (isset($_REQUEST['action_form_to_list_von_selector']) ||
    isset($_REQUEST['action_form_to_list_von_selector_x'])) {
    $formdata['_selector_type'] = 'user';
    $formdata['_title']    = __('Member selection');
    $formdata['_selector'] = 'von';
    $formdata['_mode']     = $mode;
    $formdata['_return']   = 'action_list_von_selector_to_form_ok';
    $formdata['_cancel']   = 'action_list_von_selector_to_form_cancel';
    $_SESSION['tododata']['formdata'] = $formdata;
    $delete_selector_filters = true;
    
} else if ( isset($_REQUEST['action_list_von_selector_to_form_ok']) ||
            isset($_REQUEST['action_list_von_selector_to_form_cancel'])) {
    // back from selector (okay or cancel)
    if (isset($_REQUEST['action_list_von_selector_to_form_ok'])) {
        // pressed okay
        $selector = xss_array($_POST[$_SESSION['tododata']['formdata']['_selector_name']."srcs"]);
        if (is_array($selector)) {
            put_filter_value('von','exact',$selector[0]); 
        }
    }            
    // okay & cancel
    unset($_SESSION['tododata']['formdata']);
    unset($_REQUEST['filterform']);

// Selector for Members (List view - to)
} else if (isset($_REQUEST['action_form_to_list_ext_selector']) ||
           isset($_REQUEST['action_form_to_list_ext_selector_x'])) {
    $formdata['_selector_type'] = 'user';
    $formdata['_title']    = __('Member selection');
    $formdata['_selector'] = 'ext';
    $formdata['_mode']     = $mode;
    $formdata['_return']   = 'action_list_ext_selector_to_form_ok';
    $formdata['_cancel']   = 'action_list_ext_selector_to_form_cancel';
    $_SESSION['tododata']['formdata'] = $formdata;
    $delete_selector_filters = true;
    
} else if (isset($_REQUEST['action_list_ext_selector_to_form_ok']) ||
    isset($_REQUEST['action_list_ext_selector_to_form_cancel'])) {
    // back from selector (okay or cancel)
    if (isset($_REQUEST['action_list_ext_selector_to_form_ok'])) {
        // pressed okay
        $selector = xss_array($_POST[$_SESSION['tododata']['formdata']['_selector_name']."srcs"]);
        put_filter_value('ext','exact',$selector[0]); 
    }
    // okay & cancel
    unset($_SESSION['tododata']['formdata']);
    unset($_REQUEST['filterform']);

// Selector for project (List view - project)
} else if ( isset($_REQUEST['action_form_to_list_project_selector']) ||
            isset($_REQUEST['action_form_to_list_project_selector_x'])) {
    $formdata['_selector_type'] = 'project';
    $formdata['_title']    = __('Project selection');
    $formdata['_selector'] = 'project';
    $formdata['_mode']     = $mode;
    $formdata['_return']   = 'action_list_project_selector_to_form_ok';
    $formdata['_cancel']   = 'action_list_project_selector_to_form_cancel';
    $_SESSION['tododata']['formdata'] = $formdata;
    $delete_selector_filters = true;
    
} else if ( isset($_REQUEST['action_list_project_selector_to_form_ok']) ||
            isset($_REQUEST['action_list_project_selector_to_form_cancel'])) {
    // back from selector (okay or cancel)
    if (isset($_REQUEST['action_list_project_selector_to_form_ok'])) {
        // pressed okay
        $selector = xss_array($_POST[$_SESSION['tododata']['formdata']['_selector_name']."srcs"]);
        put_filter_value('project','exact',$selector[0]); 
    }
    // okay & cancel
    unset($_SESSION['tododata']['formdata']);
    unset($_REQUEST['filterform']);

// Selector for contacts (Form)
} else if (isset($_REQUEST['action_form_to_contact_selector']) ||
           isset($_REQUEST['action_form_to_contact_selector_x'])) {
    $formdata['_selector_type'] = 'contact';
    $formdata['_title']         = __('Contact selection');
    $formdata['_selector']      = $contact;
    $formdata['_mode']          = 'forms';
    $formdata['_return']        = 'action_contact_selector_to_form_ok';
    $formdata['_cancel']        = 'action_contact_selector_to_form_cancel';
    $_SESSION['tododata']['formdata'] = $formdata;
    $delete_selector_filters = true;

} else if (isset($_REQUEST['action_contact_selector_to_form_ok']) ||
           isset($_REQUEST['action_contact_selector_to_form_cancel'])) {
    // back from selector (okay or cancel)
    if (isset($_REQUEST['action_contact_selector_to_form_ok'])) {
    // pressed okay
        $selector = xss_array($_POST[$_SESSION['tododata']['formdata']['_selector_name']."srcs"]);
        $_SESSION['tododata']['formdata']['contact'] = $selector[0];
    }
    // okay & cancel
    $formdata = $_SESSION['tododata']['formdata'];
    unset($_SESSION['tododata']['formdata']);
    unset($_REQUEST['filterform']);
    
// Selector for project (Form)
} else if (isset($_REQUEST['action_form_to_project_selector']) ||
           isset($_REQUEST['action_form_to_project_selector_x'])) {
    $formdata['_selector_type'] = 'project';
    $formdata['_title']    = __('Project selection');
    $formdata['_selector'] = $project;
    $formdata['_mode']     = 'forms';
    $formdata['_return']   = 'action_project_selector_to_form_ok';
    $formdata['_cancel']   = 'action_project_selector_to_form_cancel';
    $_SESSION['tododata']['formdata'] = $formdata;
    $delete_selector_filters = true;

} else if (isset($_REQUEST['action_project_selector_to_form_ok']) ||
           isset($_REQUEST['action_project_selector_to_form_cancel'])) {
    // back from selector (okay or cancel)
    if (isset($_REQUEST['action_project_selector_to_form_ok'])) {
        // pressed okay
        $selector = xss_array($_POST[$_SESSION['tododata']['formdata']['_selector_name']."srcs"]);
        $_SESSION['tododata']['formdata']['project'] = $selector[0];
    }
    // okay & cancel
    $formdata = $_SESSION['tododata']['formdata'];
    unset($_SESSION['tododata']['formdata']);
    unset($_REQUEST['filterform']);
    
// Selector for user (Form)
} else if (isset($_REQUEST['action_form_to_user_selector']) ||
           isset($_REQUEST['action_form_to_user_selector_x'])) {
    $formdata['_selector_type'] = 'user';
    $formdata['_title']    = __('User selection');
    $formdata['_selector'] = $ext;
    $formdata['_mode']     = 'forms';
    $formdata['_return']   = 'action_user_selector_to_form_ok';
    $formdata['_cancel']   = 'action_user_selector_to_form_cancel';
    $_SESSION['tododata']['formdata'] = $formdata;
    $delete_selector_filters = true;

} else if (isset($_REQUEST['action_user_selector_to_form_ok']) ||
           isset($_REQUEST['action_user_selector_to_form_cancel'])) {
    // back from selector (okay or cancel)
    if (isset($_REQUEST['action_user_selector_to_form_ok'])) {
        // pressed okay
        $selector = xss_array($_POST[$_SESSION['tododata']['formdata']['_selector_name']."srcs"]);
        $_SESSION['tododata']['formdata']['ext'] = $selector[0];
    }
    // okay & cancel
    $formdata = $_SESSION['tododata']['formdata'];
    unset($_SESSION['tododata']['formdata']);
    unset($_REQUEST['filterform']);

// Selector for access (Form)
} else if (isset($_REQUEST['action_form_to_access_selector']) ||
           isset($_REQUEST['action_form_to_access_selector_x'])) {
    $formdata['_selector_type'] = 'member';
    $formdata['_title']    = __('Access selection');
    $formdata['_selector'] = $persons;
    $formdata['_mode']     = 'forms';
    $formdata['_return']   = 'action_access_selector_to_form_ok';
    $formdata['_cancel']   = 'action_access_selector_to_form_cancel';
    $_SESSION['tododata']['formdata'] = $formdata;
    $delete_selector_filters = true;

} else if (isset($_REQUEST['action_access_selector_to_form_ok']) ||
           isset($_REQUEST['action_access_selector_to_form_cancel'])) {
    // back from selector (okay or cancel)
    if (isset($_REQUEST['action_access_selector_to_form_ok'])) {
        // pressed okay
        $selector = xss_array($_POST[$_SESSION['tododata']['formdata']['_selector_name']."dsts"]);
        if (is_array($selector)) {
            foreach($selector as $k => $u_id) {
                $_SESSION['tododata']['formdata']['persons'][] = slookup('users','kurz','ID',$u_id,'1');
            }
        } else $_SESSION['tododata']['formdata']['persons'] = array();
    }
    // okay & cancel
    $formdata = $_SESSION['tododata']['formdata'];
    unset($_SESSION['tododata']['formdata']);
    unset($_REQUEST['filterform']);
}

// If is defined one of this selectors, call the selector file.
if (isset($_REQUEST['action_form_to_list_von_selector']) ||
    isset($_REQUEST['action_form_to_list_von_selector_x']) ||
    isset($_REQUEST['action_form_to_list_ext_selector']) ||
    isset($_REQUEST['action_form_to_list_ext_selector_x']) ||
    isset($_REQUEST['action_form_to_list_project_selector']) ||
    isset($_REQUEST['action_form_to_list_project_selector_x']) ||
    isset($_REQUEST['action_form_to_contact_selector']) ||
    isset($_REQUEST['action_form_to_contact_selector_x']) ||
    isset($_REQUEST['action_form_to_project_selector']) ||
    isset($_REQUEST['action_form_to_project_selector_x']) ||
    isset($_REQUEST['action_form_to_user_selector']) ||
    isset($_REQUEST['action_form_to_user_selector_x']) ||
    isset($_REQUEST['action_form_to_access_selector']) ||
    isset($_REQUEST['action_form_to_access_selector_x']) ||
    isset($_REQUEST['action_selector_to_selector']) ||
    isset($_REQUEST['filterform']) ||
    isset($_REQUEST['filterdel'])) {
    $mode = 'selector';
}
?>
