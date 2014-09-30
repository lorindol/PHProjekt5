<?php
/**
* filemanager selector data script
*
* @package    filemanager
* @module     selector
* @author     Gustavo Solt, $Author: alexander $
* @licence    GPL, see www.gnu.org/copyleft/gpl.html
* @copyright  2000-2006 Mayflower GmbH www.mayflower.de
* @version    $Id: filemanager_selector_data.php,v 1.5.2.2 2007/01/23 15:35:47 alexander Exp $
*/
if (!defined('lib_included')) die('Please use index.php!');

// Common values
$formdata['_ID']       = $ID;
$formdata['_view']     = isset($view) ? qss($view) : '';
$formdata['_selector_name'] = 'filemanager_selector_';
    
// Selector for contacts (List view - contact)
if (isset($_REQUEST['action_form_to_list_filemanager_contact_selector']) ||
    isset($_REQUEST['action_form_to_list_filemanager_contact_selector_x'])) {
    $formdata['_selector_type'] = 'contact';
    $formdata['_title']    = __('Contact selection');
    $formdata['_selector'] = 'contact';
    $formdata['_mode']     = $mode;
    $formdata['_return']   = 'action_list_filemanager_contact_selector_to_form_ok';
    $formdata['_cancel']   = 'action_list_filemanager_contact_selector_to_form_cancel';
    $_SESSION['filemanagerdata']['formdata'] = $formdata;
    $delete_selector_filters = true;

} else if ( isset($_REQUEST['action_list_filemanager_contact_selector_to_form_ok']) ||
            isset($_REQUEST['action_list_filemanager_contact_selector_to_form_cancel'])) {
    // back from selector (okay or cancel)
    if (isset($_REQUEST['action_list_filemanager_contact_selector_to_form_ok'])) {
        // pressed okay
        $selector = xss_array($_POST[$_SESSION['filemanagerdata']['formdata']['_selector_name']."srcs"]);
        put_filter_value('contact','exact',$selector[0]); 
    }
    // okay & cancel
    unset($_SESSION['filemanagerdata']['formdata']);
    unset($_REQUEST['filterform']);

// Selector for projects (List view - project)
} else if ( isset($_REQUEST['action_form_to_list_div2_selector']) ||
            isset($_REQUEST['action_form_to_list_div2_selector_x'])) {
    $formdata['_selector_type'] = 'project';
    $formdata['_title']    = __('Project selection');
    $formdata['_selector'] = 'div2';
    $formdata['_mode']     = $mode;
    $formdata['_return']   = 'action_list_div2_selector_to_form_ok';
    $formdata['_cancel']   = 'action_list_div2_selector_to_form_cancel';
    $_SESSION['filemanagerdata']['formdata'] = $formdata;
    $delete_selector_filters = true;

} else if ( isset($_REQUEST['action_list_div2_selector_to_form_ok']) ||
            isset($_REQUEST['action_list_div2_selector_to_form_cancel'])) {
    // back from selector (okay or cancel)
    if (isset($_REQUEST['action_list_div2_selector_to_form_ok'])) {
        // pressed okay
        $selector = xss_array($_POST[$_SESSION['filemanagerdata']['formdata']['_selector_name']."srcs"]);
        put_filter_value('div2','exact',$selector[0]); 
    }
    // okay & cancel
    unset($_SESSION['filemanagerdata']['formdata']);
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
    $_SESSION['filemanagerdata']['formdata'] = $formdata;
    $delete_selector_filters = true;

} else if (isset($_REQUEST['action_contact_selector_to_form_ok']) ||
           isset($_REQUEST['action_contact_selector_to_form_cancel'])) {
    // back from selector (okay or cancel)
    if (isset($_REQUEST['action_contact_selector_to_form_ok'])) {
    // pressed okay
        $selector = xss_array($_POST[$_SESSION['filemanagerdata']['formdata']['_selector_name']."srcs"]);
        $_SESSION['filemanagerdata']['formdata']['contact'] = $selector[0];
    }
    // okay & cancel
    $formdata = $_SESSION['filemanagerdata']['formdata'];
    unset($_SESSION['filemanagerdata']['formdata']);
    unset($_REQUEST['filterform']);
    
// Selector for projects (Form)
} else if (isset($_REQUEST['action_form_to_project_selector']) ||
           isset($_REQUEST['action_form_to_project_selector_x'])) {
    $formdata['_selector_type'] = 'project';
    $formdata['_title']    = __('Project selection');
    $formdata['_selector'] = $div2; 
    $formdata['_mode']     = 'forms';
    $formdata['_return']   = 'action_project_selector_to_form_ok';
    $formdata['_cancel']   = 'action_project_selector_to_form_cancel';
    $_SESSION['filemanagerdata']['formdata'] = $formdata;
    $delete_selector_filters = true;

} else if (isset($_REQUEST['action_project_selector_to_form_ok']) ||
           isset($_REQUEST['action_project_selector_to_form_cancel'])) {
    // back from selector (okay or cancel)
    if (isset($_REQUEST['action_project_selector_to_form_ok'])) {
        // pressed okay
        $selector = xss_array($_POST[$_SESSION['filemanagerdata']['formdata']['_selector_name']."srcs"]);
        $_SESSION['filemanagerdata']['formdata']['project'] = $selector[0];
    }
    // okay & cancel
    $formdata = $_SESSION['filemanagerdata']['formdata'];
    unset($_SESSION['filemanagerdata']['formdata']);
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
    $_SESSION['filemanagerdata']['formdata'] = $formdata;
    $delete_selector_filters = true;

} else if (isset($_REQUEST['action_access_selector_to_form_ok']) ||
           isset($_REQUEST['action_access_selector_to_form_cancel'])) {
    // back from selector (okay or cancel)
    if (isset($_REQUEST['action_access_selector_to_form_ok'])) {
        // pressed okay
        $selector = xss_array($_POST[$_SESSION['filemanagerdata']['formdata']['_selector_name']."dsts"]);
        if (is_array($selector)) {
            foreach($selector as $k => $u_id) {
                $_SESSION['filemanagerdata']['formdata']['persons'][] = slookup('users','kurz','ID',$u_id,'1');
            }
        } else $_SESSION['filemanagerdata']['formdata']['persons'] = array();
    }
    // okay & cancel
    $formdata = $_SESSION['filemanagerdata']['formdata'];
    unset($_SESSION['filemanagerdata']['formdata']);
    unset($_REQUEST['filterform']);
}

// If is defined one of this selectors, call the selector file.
if (isset($_REQUEST['action_form_to_list_filemanager_contact_selector']) ||
    isset($_REQUEST['action_form_to_list_filemanager_contact_selector_x']) ||
    isset($_REQUEST['action_form_to_list_div2_selector']) ||
    isset($_REQUEST['action_form_to_list_div2_selector_x']) ||
    isset($_REQUEST['action_form_to_contact_selector']) ||
    isset($_REQUEST['action_form_to_contact_selector_x']) ||
    isset($_REQUEST['action_form_to_project_selector']) ||
    isset($_REQUEST['action_form_to_project_selector_x']) ||
    isset($_REQUEST['action_form_to_access_selector']) ||
    isset($_REQUEST['action_form_to_access_selector_x']) ||
    isset($_REQUEST['action_selector_to_selector']) ||
    isset($_REQUEST['filterform']) ||
    isset($_REQUEST['filterdel'])) {
    $mode = 'selector';
}
?>
