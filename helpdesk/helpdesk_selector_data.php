<?php
/**
* helpdesk selector data script
*
* @package    helpdesk
* @module     selector
* @author     Gustavo Solt, $Author: alexander $
* @licence    GPL, see www.gnu.org/copyleft/gpl.html
* @copyright  2000-2006 Mayflower GmbH www.mayflower.de
* @version    $Id: helpdesk_selector_data.php,v 1.5.2.2 2007/01/23 15:35:47 alexander Exp $
*/
if (!defined('lib_included')) die('Please use index.php!');
 
// Common values
$formdata['_ID']       = $ID;
$formdata['_view']          = isset($view) ? qss($view) : '';
$formdata['_selector_name'] = 'helpdesk_selector_';
    
// Selector for Members (List view - assigned)
if (isset($_REQUEST['action_form_to_list_assigned_selector']) ||
    isset($_REQUEST['action_form_to_list_assigned_selector_x'])) {
    $formdata['_selector_type'] = 'member';
    $formdata['_title']    = __('Member selection');
    $formdata['_selector'] = 'assigned';
    $formdata['_mode']     = $mode;
    $formdata['_return']   = 'action_list_assigned_selector_to_form_ok';
    $formdata['_cancel']   = 'action_list_assigned_selector_to_form_cancel';
    $_SESSION['helpdeskdata']['formdata'] = $formdata;
    $delete_selector_filters = true;

} else if (isset($_REQUEST['action_list_assigned_selector_to_form_ok']) ||
           isset($_REQUEST['action_list_assigned_selector_to_form_cancel'])) {
    // back from selector (okay or cancel)
    if (isset($_REQUEST['action_list_assigned_selector_to_form_ok'])) {
        // pressed okay
        $selector = xss_array($_POST[$_SESSION['helpdeskdata']['formdata']['_selector_name']."dsts"]);
        if (is_array($selector)) {
            put_filter_value('assigned','exact',$selector); 
        }
    }
    // okay & cancel
    unset($_SESSION['helpdeskdata']['formdata']);
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
    $_SESSION['helpdeskdata']['formdata'] = $formdata;
    $delete_selector_filters = true;

} else if (isset($_REQUEST['action_contact_selector_to_form_ok']) ||
           isset($_REQUEST['action_contact_selector_to_form_cancel'])) {
    // back from selector (okay or cancel)
    if (isset($_REQUEST['action_contact_selector_to_form_ok'])) {
    // pressed okay
        $selector = xss_array($_POST[$_SESSION['helpdeskdata']['formdata']['_selector_name']."srcs"]);
        $_SESSION['helpdeskdata']['formdata']['contact'] = $selector[0];
    }
    // okay & cancel
    $formdata = $_SESSION['helpdeskdata']['formdata'];
    unset($_SESSION['helpdeskdata']['formdata']);
    unset($_REQUEST['filterform']);
    
// Selector for projects (Form)
} else if (isset($_REQUEST['action_form_to_project_selector']) ||
           isset($_REQUEST['action_form_to_project_selector_x'])) {
    $formdata['_selector_type'] = 'project';
    $formdata['_title']    = __('Project selection');
    $formdata['_selector'] = $proj;
    $formdata['_mode']     = 'forms';
    $formdata['_return']   = 'action_project_selector_to_form_ok';
    $formdata['_cancel']   = 'action_project_selector_to_form_cancel';
    $_SESSION['helpdeskdata']['formdata'] = $formdata;
    $delete_selector_filters = true;

} else if (isset($_REQUEST['action_project_selector_to_form_ok']) ||
           isset($_REQUEST['action_project_selector_to_form_cancel'])) {
    // back from selector (okay or cancel)
    if (isset($_REQUEST['action_project_selector_to_form_ok'])) {
        // pressed okay
        $selector = xss_array($_POST[$_SESSION['helpdeskdata']['formdata']['_selector_name']."srcs"]);
        $_SESSION['helpdeskdata']['formdata']['project'] = $selector[0];
    }
    // okay & cancel
    $formdata = $_SESSION['helpdeskdata']['formdata'];
    unset($_SESSION['helpdeskdata']['formdata']);
    unset($_REQUEST['filterform']);
    
// Selector for assigned (Form)
} else if (isset($_REQUEST['action_form_to_user_selector']) ||
           isset($_REQUEST['action_form_to_user_selector_x'])) {
    $formdata['_selector_type'] = 'user';
    $formdata['_title']    = __('User selection');
    $formdata['_selector'] = $assigned;
    $formdata['_mode']     = 'forms';
    $formdata['_return']   = 'action_user_selector_to_form_ok';
    $formdata['_cancel']   = 'action_user_selector_to_form_cancel';
    $_SESSION['helpdeskdata']['formdata'] = $formdata;
    $delete_selector_filters = true;

} else if (isset($_REQUEST['action_user_selector_to_form_ok']) ||
           isset($_REQUEST['action_user_selector_to_form_cancel'])) {
    // back from selector (okay or cancel)
    if (isset($_REQUEST['action_user_selector_to_form_ok'])) {
        // pressed okay
        $selector = xss_array($_POST[$_SESSION['helpdeskdata']['formdata']['_selector_name']."srcs"]);
        $_SESSION['helpdeskdata']['formdata']['assigned'] = $selector[0];
    }
    // okay & cancel
    $formdata = $_SESSION['helpdeskdata']['formdata'];
    unset($_SESSION['helpdeskdata']['formdata']);
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
    $_SESSION['helpdeskdata']['formdata'] = $formdata;
    $delete_selector_filters = true;

} else if (isset($_REQUEST['action_access_selector_to_form_ok']) ||
           isset($_REQUEST['action_access_selector_to_form_cancel'])) {
    // back from selector (okay or cancel)
    if (isset($_REQUEST['action_access_selector_to_form_ok'])) {
        // pressed okay
        $selector = xss_array($_POST[$_SESSION['helpdeskdata']['formdata']['_selector_name']."dsts"]);
        if (is_array($selector)) {
            foreach($selector as $k => $u_id) {
                $_SESSION['helpdeskdata']['formdata']['persons'][] = slookup('users','kurz','ID',$u_id,'1');
            }
        } else $_SESSION['helpdeskdata']['formdata']['persons'] = array();
    }
    // okay & cancel
    $formdata = $_SESSION['helpdeskdata']['formdata'];
    unset($_SESSION['helpdeskdata']['formdata']);
    unset($_REQUEST['filterform']);
}

// If is defined one of this selectors, call the selector file.
if (isset($_REQUEST['action_form_to_list_assigned_selector']) ||
    isset($_REQUEST['action_form_to_list_assigned_selector_x']) ||
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
