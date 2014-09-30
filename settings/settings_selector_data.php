<?php

/*
 * This file check the $_REQUEST for see what selector was clicked
 * In first case, (Form to Selector) the data are stored on the SESSION and call the selector file.
 * In second case, (Selector to Form) get the selected data and continue with the form.
 * Author: Gustavo Solt gustavo.solt@gmail.com
 */
 
 // check whether the lib has been included - authentication!
if (!defined("lib_included")) die("Please use index.php!"); 
 
// Common value
$formdata['_selector_name'] = 'setting_selector_';
$formdata['settigs']        = settings_get_request_settings();

if ($mode == "data") {
    // Selector for Members (Form - Viewers)
    if (isset($_REQUEST['action_related_viewer_to_selector']) ||
        isset($_REQUEST['action_related_viewer_to_selector_x'])) {
        $formdata['_selector_type'] = 'member';
        $formdata['_title']         = $private_events_title;
        $formdata['_selector']      = $_REQUEST['setting_cal_viewer'];
        $formdata['_mode']          = 'data';
        $formdata['_return']        = 'action_selector_to_data_viewer';
        $formdata['_cancel']        = 'action_selector_to_data_viewer_cancel';
        $_SESSION['settings_5']['formdata'] = $formdata;
        $delete_selector_filters = true;
        $mode = 'selector';
        // save the data
        include_once("./settings_data.php");
    } else if ( isset($_REQUEST["action_selector_to_data_viewer"]) ||
                isset($_REQUEST["action_selector_to_data_viewer_cancel"])) {
        // back from selector (okay or cancel)
        if (isset($_REQUEST["action_selector_to_data_viewer"])) {
            // pressed okay
            $setting_cal_viewer = xss_array($_POST[$_SESSION['settings_5']['formdata']['_selector_name']."dsts"]);
        }
        // okay & cancel
        unset($_SESSION['settings_5']['formdata']);
        unset($_REQUEST['filterform']);        
        $mode = 'forms';
    
    // Selector for Members (Form - Readers)
    } else if ( isset($_REQUEST['action_related_reader_to_selector']) ||
                isset($_REQUEST['action_related_reader_to_selector_x'])) {
        $formdata['_selector_type']     = 'member';
        $formdata['_title']             = __('Users, who can read my normal events');
        $formdata['_selector']          = $_REQUEST['setting_cal_reader'];
        $formdata['_mode']              = 'data';
        $formdata['_return']            = 'action_selector_to_data_reader';
        $formdata['_cancel']            = 'action_selector_to_data_reader_cancel';
        $_SESSION['settings_5']['formdata'] = $formdata;
        $delete_selector_filters = true;
        $mode = 'selector';
        // save the data
        include_once("./settings_data.php");
    } else if ( isset($_REQUEST["action_selector_to_data_reader"]) ||
                isset($_REQUEST["action_selector_to_data_reader_cancel"])) {
        // back from selector (okay or cancel)
        if (isset($_REQUEST["action_selector_to_data_reader"])) {
            // pressed okay
            $setting_cal_reader = xss_array($_POST[$_SESSION['settings_5']['formdata']['_selector_name']."dsts"]);
        }
        // okay & cancel
        unset($_SESSION['settings_5']['formdata']);
        unset($_REQUEST['filterform']);
        $mode = 'forms';
    
    // Selector for Members (Form - Repesents)
    } else if ( isset($_REQUEST['action_related_proxy_to_selector']) ||
                isset($_REQUEST['action_related_proxy_to_selector_x'])) {
        $formdata['_selector_type']     = 'member';
        $formdata['_title']             = __('Users, who can represent me');
        $formdata['_selector']          = $_REQUEST['setting_cal_proxy'];
        $formdata['_mode']              = 'data';
        $formdata['_return']            = 'action_selector_to_data_proxy';
        $formdata['_cancel']            = 'action_selector_to_data_proxy_cancel';
        $_SESSION['settings_5']['formdata'] = $formdata;
        $delete_selector_filters = true;
        $mode = 'selector';
        // save the data
        include_once("./settings_data.php");
    } else if ( isset($_REQUEST["action_selector_to_data_proxy"]) ||
                isset($_REQUEST["action_selector_to_data_proxy_cancel"])) {
        // back from selector (okay or cancel)
        if (isset($_REQUEST["action_selector_to_data_proxy"])) {
            // pressed okay
            $setting_cal_proxy = xss_array($_POST[$_SESSION['settings_5']['formdata']['_selector_name']."dsts"]);
        }
        // okay & cancel
        unset($_SESSION['settings_5']['formdata']);
        unset($_REQUEST['filterform']);
        $mode = 'forms';
    
    // Remain in the selector
    } else if ( isset($_REQUEST['action_selector_to_selector']) ||
                isset($_REQUEST['filterform']) ||
                isset($_REQUEST['filterdel'])) {
        $mode = 'selector';
    }

// Profiles
} else if ($mode == "profile") {

    // Selector for Members (Form - Profiles)
    if (isset($_REQUEST["action_profile_to_selector"]) ||
        isset($_REQUEST["action_profile_to_selector_x"])) {
        $formdata['_selector_type']     = 'member';
        $formdata['_title']             = __('Profiles');
        $formdata['_selector']          = $_REQUEST['profile_users'];
        $formdata['_mode']              = 'profile';
        $formdata['_return']            = 'action_selector_to_profile';
        $formdata['_cancel']            = 'action_selector_to_profile_cancel';
        // keep that data for the time when coming back
        $formdata['profile_id']         = $_SESSION['settings_5']['formdata']['profile_id'];
        $formdata['profile_name']       = $_REQUEST['profile_name'];
        $_SESSION['settings_5']['formdata'] = $formdata;
        $delete_selector_filters = true;
        $mode = 'selector';
    } else if ( isset($_REQUEST["action_selector_to_profile"]) ||
                isset($_REQUEST["action_selector_to_profile_cancel"])) {
        // back from selector (okay or cancel)
        if (isset($_REQUEST["action_selector_to_profile"])) {
            // pressed okay
            $formdata['profile_users'] = xss_array($_POST[$_SESSION['settings_5']['formdata']['_selector_name']."dsts"]);
        }
        // okay & cancel
        unset($_SESSION['settings_5']['formdata']);
        unset($_REQUEST['filterform']);
        $mode = 'forms';

    // Selector for access (Form)
    } else if (isset($_REQUEST['action_form_to_access_selector']) ||
               isset($_REQUEST['action_form_to_access_selector_x'])) {
        $formdata['_selector_type']     = 'member';
        $formdata['_title']             = __('Access selection');
        $formdata['_selector']          = $_REQUEST['persons'];
        $formdata['_mode']              = 'profile';
        $formdata['_return']            = 'action_access_selector_to_form_ok';
        $formdata['_cancel']            = 'action_access_selector_to_form_cancel';
        // keep that data for the time when coming back
        $formdata['profile_id']         = $_SESSION['settings_5']['formdata']['profile_id'];
        $formdata['profile_name']       = $_REQUEST['profile_name'];
        $formdata['profile_users']      = $formdata['profile_users'];
        $_SESSION['settings_5']['formdata'] = $formdata;
        $delete_selector_filters = true;
        $mode = 'selector';
    } else if (isset($_REQUEST['action_access_selector_to_form_ok']) ||
               isset($_REQUEST['action_access_selector_to_form_cancel'])) {
        // back from selector (okay or cancel)
        if (isset($_REQUEST['action_access_selector_to_form_ok'])) {
            // pressed okay
            $selector = xss_array($_POST[$_SESSION['settings_5']['formdata']['_selector_name']."dsts"]);
            unset($formdata['persons']);
            if (is_array($selector)) {
                foreach($selector as $k => $u_id) {
                   $formdata['persons'][] = slookup('users','kurz','ID',$u_id,'1');
                }
            } else $formdata['persons'] = array();
        }
        // okay & cancel
        unset($_SESSION['settings_5']['formdata']);
        unset($_REQUEST['filterform']);
        $mode = 'forms';
        
    // Remain in the selector
    } else if ( isset($_REQUEST['action_selector_to_selector']) ||
                isset($_REQUEST['filterform']) ||
                isset($_REQUEST['filterdel'])) {
        $mode = 'selector';
    }
}
?>
