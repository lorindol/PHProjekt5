<?php
/**
* provides selector data for the contacts
*
* This file check the $_REQUEST for see what selector was clicked
* In first case, (Form to Selector) the data are stored on the SESSION and call the selector file.
* In second case, (Selector to Form) get the selected data and continue with the form.
*
* @package    contacts
* @module     selector
* @author     Gustavo Solt, $Author: alexander $
* @licence    GPL, see www.gnu.org/copyleft/gpl.html
* @copyright  2000-2006 Mayflower GmbH www.mayflower.de
* @version    $Id: contacts_selector_data.php,v 1.7.2.2 2007/01/23 15:35:48 alexander Exp $
*/
if (!defined('lib_included')) die('Please use index.php!');

// Common values
$formdata['_ID']            = $ID;
$formdata['_view']          = isset($view) ? qss($view) : '';
$formdata['_selector_name'] = 'contact_selector_';
    
// Selector for contacts (Form)
if (isset($_REQUEST['action_form_to_contact_selector']) ||
    isset($_REQUEST['action_form_to_contact_selector_x'])) {
    $formdata['_selector_type'] = 'contact';
    $formdata['_title']         = __('Contact selection');
    $formdata['_selector']      = $parent;
    $formdata['_mode']          = 'forms';
    $formdata['_return']        = 'action_contact_selector_to_form_ok';
    $formdata['_cancel']        = 'action_contact_selector_to_form_cancel';
    $_SESSION['contactdata']['formdata'] = $formdata;
    $delete_selector_filters = true;

} else if (isset($_REQUEST['action_contact_selector_to_form_ok']) ||
           isset($_REQUEST['action_contact_selector_to_form_cancel'])) {
    // back from selector (okay or cancel)
    if (isset($_REQUEST['action_contact_selector_to_form_ok'])) {
    // pressed okay
        $selector = xss_array($_POST[$_SESSION['contactdata']['formdata']['_selector_name']."srcs"]);
        $_SESSION['contactdata']['formdata']['parent'] = $selector[0];
    }
    // okay & cancel
    $formdata = $_SESSION['contactdata']['formdata'];
    unset($_SESSION['contactdata']['formdata']);
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
    $_SESSION['contactdata']['formdata'] = $formdata;
    $delete_selector_filters = true;

} else if (isset($_REQUEST['action_access_selector_to_form_ok']) ||
           isset($_REQUEST['action_access_selector_to_form_cancel'])) {
    // back from selector (okay or cancel)
    if (isset($_REQUEST['action_access_selector_to_form_ok'])) {
        // pressed okay
        $selector = xss_array($_POST[$_SESSION['contactdata']['formdata']['_selector_name']."dsts"]);
        if (is_array($selector)) {
            foreach($selector as $k => $u_id) {
                $_SESSION['contactdata']['formdata']['persons'][] = slookup('users','kurz','ID',$u_id,'1');
            }
        } else $_SESSION['contactdata']['formdata']['persons'] = array();
    }
    // okay & cancel
    $formdata = $_SESSION['contactdata']['formdata'];
    unset($_SESSION['contactdata']['formdata']);
    unset($_REQUEST['filterform']);

// Selector for projects (Form)
} else if (isset($_REQUEST['action_form_to_project_selector']) ||
          (isset($_REQUEST['action_form_to_project_selector_x']) && ($_REQUEST['action_form_to_project_selector_x'] > 0) )) {
    $formdata['_selector_type'] = 'project';
    $formdata['_title']    = __('Project selection');
    $formdata['_selector'] = $project_personen;
    $formdata['_mode']     = 'forms';
    $formdata['_return']   = 'action_project_selector_to_form_ok';
    $formdata['_cancel']   = 'action_project_selector_to_form_cancel';
    $_SESSION['contactdata']['formdata'] = $formdata;
    $delete_selector_filters = true;

} else if (isset($_REQUEST['action_project_selector_to_form_ok']) ||
           isset($_REQUEST['action_project_selector_to_form_cancel'])) {
    // back from selector (okay or cancel)
    if (isset($_REQUEST['action_project_selector_to_form_ok'])) {
        // pressed okay
        $selector = xss_array($_POST[$_SESSION['contactdata']['formdata']['_selector_name']."dsts"]);
        if (is_array($selector)) {
            $_SESSION['contactdata']['formdata']['project_personen'] = $selector;
        } else $_SESSION['contactdata']['formdata']['project_personen'] = array();
    }
    // okay & cancel
    $formdata = $_SESSION['contactdata']['formdata'];
    unset($_SESSION['contactdata']['formdata']);
    unset($_REQUEST['filterform']);
   
// Selector for contacts (Profiles)
} else if ( isset($_REQUEST['action_form_to_profile_contact_selector']) ||
            isset($_REQUEST['action_form_to_profile_contact_selector_x'])) {
    $formdata['_selector_type'] = 'profile_contact';
    $formdata['_title']         = __('Contact selection');
    $formdata['_selector']      = $contact_personen;
    $formdata['_mode']          = 'profiles_forms';
    if (isset($_REQUEST['neu'])) $formdata['_neu']          = $_REQUEST['neu'];
    if (isset($_REQUEST['aendern'])) $formdata['_neu']      = $_REQUEST['aendern'];
    $formdata['_action']        = 'contacts';
    $formdata['_return']        = 'action_profile_contact_selector_to_form_ok';
    $formdata['_cancel']        = 'action_profile_contact_selector_to_form_cancel';
    $_SESSION['contactdata']['formdata'] = $formdata;
    $delete_selector_filters = true;

} else if (isset($_REQUEST['action_profile_contact_selector_to_form_ok']) ||
           isset($_REQUEST['action_profile_contact_selector_to_form_cancel'])) {
    // back from selector (okay or cancel)
    if (isset($_REQUEST['action_profile_contact_selector_to_form_ok'])) {
    // pressed okay
        $selector = xss_array($_POST[$_SESSION['contactdata']['formdata']['_selector_name']."dsts"]);
        if (is_array($selector)) {
            $_SESSION['contactdata']['formdata']['contact_personen'] = $selector;
        } else $_SESSION['contactdata']['formdata']['contact_personen'] = array();
    }
    // okay & cancel
    $formdata = $_SESSION['contactdata']['formdata'];
    unset($_SESSION['contactdata']['formdata']);
    unset($_REQUEST['filterform']);

// Selector for Members (Related Object - TODO - von)
} else if ( isset($_REQUEST['action_form_to_list_von_selector']) ||
            isset($_REQUEST['action_form_to_list_von_selector_x'])) {
    $formdata['_selector_type'] = 'user';
    $formdata['_title']    = __('Member selection');
    $formdata['_selector'] = 'von';
    $formdata['_mode']     = 'forms';
    $formdata['_return']   = 'action_list_von_selector_to_form_ok';
    $formdata['_cancel']   = 'action_list_von_selector_to_form_cancel';
    $_SESSION['contactdata']['formdata'] = $formdata;
    $delete_selector_filters = true;

} else if ( isset($_REQUEST['action_list_von_selector_to_form_ok']) ||
            isset($_REQUEST['action_list_von_selector_to_form_cancel'])) {
    // back from selector (okay or cancel)
    if (isset($_REQUEST['action_list_von_selector_to_form_ok'])) {
        // pressed okay
        $selector = xss_array($_POST[$_SESSION['contactdata']['formdata']['_selector_name']."srcs"]);
        if (is_array($selector)) {
            put_filter_value('von','exact',$selector[0],'todo'); 
        }
    }
    // okay & cancel
    unset($_SESSION['contactdata']['formdata']);
    unset($_REQUEST['filterform']);

// Selector for Members (Related Object - TODO - to)
} else if ( isset($_REQUEST['action_form_to_list_ext_selector']) ||
            isset($_REQUEST['action_form_to_list_ext_selector_x'])) {
    $formdata['_selector_type'] = 'user';
    $formdata['_title']    = __('Member selection');
    $formdata['_selector'] = 'ext';
    $formdata['_mode']     = 'forms';
    $formdata['_return']   = 'action_list_ext_selector_to_form_ok';
    $formdata['_cancel']   = 'action_list_ext_selector_to_form_cancel';
    $_SESSION['contactdata']['formdata'] = $formdata;
    $delete_selector_filters = true;

} else if ( isset($_REQUEST['action_list_ext_selector_to_form_ok']) ||
            isset($_REQUEST['action_list_ext_selector_to_form_cancel'])) {
    // back from selector (okay or cancel)
    if (isset($_REQUEST['action_list_ext_selector_to_form_ok'])) {
        // pressed okay
        $selector = xss_array($_POST[$_SESSION['contactdata']['formdata']['_selector_name']."srcs"]);
        put_filter_value('ext','exact',$selector[0],'todo'); 
    }
    // okay & cancel
    unset($_SESSION['contactdata']['formdata']);
    unset($_REQUEST['filterform']);

// Selector for projects (Related Object - TODO - project)
} else if ( isset($_REQUEST['action_form_to_list_project_selector']) ||
            isset($_REQUEST['action_form_to_list_project_selector_x'])) {
    $formdata['_selector_type'] = 'one_project';
    $formdata['_title']    = __('Project selection');
    $formdata['_selector'] = 'project';
    $formdata['_mode']     = 'forms';
    $formdata['_return']   = 'action_list_project_selector_to_form_ok';
    $formdata['_cancel']   = 'action_list_project_selector_to_form_cancel';
    $_SESSION['contactdata']['formdata'] = $formdata;
    $delete_selector_filters = true;

} else if ( isset($_REQUEST['action_list_project_selector_to_form_ok']) ||
            isset($_REQUEST['action_list_project_selector_to_form_cancel'])) {
    // back from selector (okay or cancel)
    if (isset($_REQUEST['action_list_project_selector_to_form_ok'])) {
        // pressed okay
        $selector = xss_array($_POST[$_SESSION['contactdata']['formdata']['_selector_name']."srcs"]);
        put_filter_value('project','exact',$selector[0],'todo'); 
    }
    // okay & cancel
    unset($_SESSION['contactdata']['formdata']);
    unset($_REQUEST['filterform']);

// Selector for contacts (Related Object - NOTES - contacts)
} else if ( isset($_REQUEST['action_form_to_list_notes_contact_selector']) ||
            isset($_REQUEST['action_form_to_list_notes_contact_selector_x'])) {
    $formdata['_selector_type'] = 'contact';
    $formdata['_title']    = __('Contact selection');
    $formdata['_selector'] = 'contact';
    $formdata['_mode']     = 'forms';
    $formdata['_return']   = 'action_list_notes_contact_selector_to_form_ok';
    $formdata['_cancel']   = 'action_list_notes_contact_selector_to_form_cancel';
    $_SESSION['contactdata']['formdata'] = $formdata;
    $delete_selector_filters = true;

} else if ( isset($_REQUEST['action_list_notes_contact_selector_to_form_ok']) ||
            isset($_REQUEST['action_list_notes_contact_selector_to_form_cancel'])) {
    // back from selector (okay or cancel)
    if (isset($_REQUEST['action_list_notes_contact_selector_to_form_ok'])) {
        // pressed okay
        $selector = xss_array($_POST[$_SESSION['contactdata']['formdata']['_selector_name']."srcs"]);
        put_filter_value('contact','exact',$selector[0],'notes'); 
    }
    // okay & cancel
    unset($_SESSION['contactdata']['formdata']);
    unset($_REQUEST['filterform']);

// Selector for projects (Related Object - NOTES - projekt)
} else if ( isset($_REQUEST['action_form_to_list_projekt_selector']) ||
            isset($_REQUEST['action_form_to_list_projekt_selector_x'])) {
    $formdata['_selector_type'] = 'one_project';
    $formdata['_title']    = __('Project selection');
    $formdata['_selector'] = 'projekt';
    $formdata['_mode']     = 'forms';
    $formdata['_return']   = 'action_list_projekt_selector_to_form_ok';
    $formdata['_cancel']   = 'action_list_projekt_selector_to_form_cancel';
    $_SESSION['contactdata']['formdata'] = $formdata;
    $delete_selector_filters = true;

} else if ( isset($_REQUEST['action_list_projekt_selector_to_form_ok']) ||
            isset($_REQUEST['action_list_projekt_selector_to_form_cancel'])) {
    // back from selector (okay or cancel)
    if (isset($_REQUEST['action_list_projekt_selector_to_form_ok'])) {
        // pressed okay
        $selector = xss_array($_POST[$_SESSION['contactdata']['formdata']['_selector_name']."srcs"]);
        put_filter_value('projekt','exact',$selector[0],'notes'); 
    }
    // okay & cancel
    unset($_SESSION['contactdata']['formdata']);
    unset($_REQUEST['filterform']);

// Selector for contacts (Related Object - FILEMANAGER - contact)
} else if ( isset($_REQUEST['action_form_to_list_dateien_contact_selector']) ||
            isset($_REQUEST['action_form_to_list_dateien_contact_selector_x'])) {
    $formdata['_selector_type'] = 'contact';
    $formdata['_title']    = __('Contact selection');
    $formdata['_selector'] = 'contact';
    $formdata['_mode']     = 'forms';
    $formdata['_return']   = 'action_list_dateien_contact_selector_to_form_ok';
    $formdata['_cancel']   = 'action_list_dateien_contact_selector_to_form_cancel';
    $_SESSION['contactdata']['formdata'] = $formdata;
    $delete_selector_filters = true;

} else if ( isset($_REQUEST['action_list_dateien_contact_selector_to_form_ok']) ||
            isset($_REQUEST['action_list_dateien_contact_selector_to_form_cancel'])) {
    // back from selector (okay or cancel)
    if (isset($_REQUEST['action_list_dateien_contact_selector_to_form_ok'])) {
        // pressed okay
        $selector = xss_array($_POST[$_SESSION['contactdata']['formdata']['_selector_name']."srcs"]);
        put_filter_value('contact','exact',$selector[0],'dateien'); 
    }
    // okay & cancel
    unset($_SESSION['contactdata']['formdata']);
    unset($_REQUEST['filterform']);

// Selector for projects (Related Object - FILEMANAGER - div2)
} else if ( isset($_REQUEST['action_form_to_list_div2_selector']) ||
            isset($_REQUEST['action_form_to_list_div2_selector_x'])) {
    $formdata['_selector_type'] = 'one_project';
    $formdata['_title']    = __('Project selection');
    $formdata['_selector'] = 'div2';
    $formdata['_mode']     = 'forms';
    $formdata['_return']   = 'action_list_div2_selector_to_form_ok';
    $formdata['_cancel']   = 'action_list_div2_selector_to_form_cancel';
    $_SESSION['contactdata']['formdata'] = $formdata;
    $delete_selector_filters = true;

} else if ( isset($_REQUEST['action_list_div2_selector_to_form_ok']) ||
            isset($_REQUEST['action_list_div2_selector_to_form_cancel'])) {
    // back from selector (okay or cancel)
    if (isset($_REQUEST['action_list_div2_selector_to_form_ok'])) {
        // pressed okay
        $selector = xss_array($_POST[$_SESSION['contactdata']['formdata']['_selector_name']."srcs"]);
        put_filter_value('div2','exact',$selector[0],'dateien'); 
    }
    // okay & cancel
    unset($_SESSION['contactdata']['formdata']);
    unset($_REQUEST['filterform']);
} 

// If is defined one of this selectors, call the selector file.
if (isset($_REQUEST['action_form_to_contact_selector']) ||
    isset($_REQUEST['action_form_to_contact_selector_x']) ||
    isset($_REQUEST['action_form_to_access_selector']) ||
    isset($_REQUEST['action_form_to_access_selector_x']) ||
    isset($_REQUEST['action_form_to_project_selector']) ||
    (isset($_REQUEST['action_form_to_project_selector_x']) && ($_REQUEST['action_form_to_project_selector_x'] > 0)) ||
    isset($_REQUEST['action_selector_to_selector']) ||
    isset($_REQUEST['action_form_to_profile_contact_selector']) ||
    isset($_REQUEST['action_form_to_profile_contact_selector_x']) ||
    isset($_REQUEST['action_form_to_list_von_selector']) ||
    isset($_REQUEST['action_form_to_list_von_selector_x']) ||
    isset($_REQUEST['action_form_to_list_ext_selector']) ||
    isset($_REQUEST['action_form_to_list_ext_selector_x']) ||
    isset($_REQUEST['action_form_to_list_project_selector']) ||
    isset($_REQUEST['action_form_to_list_project_selector_x']) ||
    isset($_REQUEST['action_form_to_list_notes_contact_selector']) ||
    isset($_REQUEST['action_form_to_list_notes_contact_selector_x']) ||
    isset($_REQUEST['action_form_to_list_projekt_selector']) ||
    isset($_REQUEST['action_form_to_list_projekt_selector_x']) ||
    isset($_REQUEST['action_form_to_list_dateien_contact_selector']) ||
    isset($_REQUEST['action_form_to_list_dateien_contact_selector_x']) ||
    isset($_REQUEST['action_form_to_list_div2_selector']) ||
    isset($_REQUEST['action_form_to_list_div2_selector_x']) ||
    isset($_REQUEST['filterform']) ||
    isset($_REQUEST['filterdel'])) {
    $mode = 'selector';
}
?>
