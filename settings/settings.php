<?php

// settings.php - PHProjekt Version 5.2
// copyright  ©  2000-2005 Albrecht Guenther  ag@phprojekt.com
// www.phprojekt.com
// Author: Albrecht Guenther, $Author: alexander $
// $Id: settings.php,v 1.53.2.2 2007/01/23 11:53:07 alexander Exp $

$module = 'settings';
define('PATH_PRE','../');
include_once(PATH_PRE.'lib/lib.inc.php');
$pout = '';
require_once('./settings.inc.php');
include_once(PATH_PRE.'calendar/calendar.inc.php');

settings_init();

$_SESSION['common']['module'] = 'settings';

if (PHPR_CALENDAR_ACCESS_MODE == 'special-1') {
    $private_events_title = __('Users, who can read my private events');
}
else {
    $private_events_title = __('Users, who can see my private events');
}

// call the distinct selectors
require_once('settings_selector_data.php');

// Normal View
// So either write the stuff to db 
if ($mode == "data") {

    if (isset($_REQUEST['action_save_settings'])) {
        include_once("./settings_data.php");
        message_stack_in(__('Your settings have been stored'), 'settings', 'notice');
        $mode = 'forms';
    }
// Change password
} else if ($mode == 'password') {
    include_once('./settings_data_password.php');
    $mode = 'forms';

// Profile
} else if ($mode == "profile") {

    // insert, update or delete a profile
    if (isset($_REQUEST["action_delete_profile"]) ||
        isset($_REQUEST["action_write_profile"])) {
        include_once("./settings_data_profile.php");
        $mode = 'forms';

    // show edit-form and reset relevant session-data
    } else if ( isset($_REQUEST["action_edit_profile"]) ||
                isset($_REQUEST['action_new_profile'])) {
        $_SESSION['settings_5']['formdata']['profile_id'] = xss($_REQUEST['profile_id']);
        if (isset($_REQUEST['action_new_profile'])) {
            $_SESSION['settings_5']['formdata']['profile_id'] = '';
        }
        $_SESSION['settings_5']['formdata']['profile_name']   = '';
        $_SESSION['settings_5']['formdata']['profile_users']  = array();
        $mode = 'forms';
    }
}

echo set_page_header();

// if no preselection for the multiples was done.
if (!isset($setting_cal_viewer)) $setting_cal_viewer = calendar_get_related_users('viewer');
if (!isset($setting_cal_reader)) $setting_cal_reader = calendar_get_related_users('reader');
if (!isset($setting_cal_proxy))  $setting_cal_proxy  = calendar_get_related_users('proxy');

// always include the settings overview
include_once(LIB_PATH.'/navigation.inc.php');



// tabs
$tabs = array();
echo '<div id="global-header">';
echo get_tabs_area($tabs);
echo breadcrumb($module, array());
echo '</div>';


// put the values in the form
if (isset($formdata['persons']) && $mode == "profile") {
    $persons = $formdata['persons'];
}

echo '
<div id="global-content">
';
define('MODE',$mode);
include_once('./settings_'.MODE.'.php');
echo '
    </div>
</div>

</div>
</body>
</html>
';


/**
* Check incoming data and set it to expectable values.
* @uses $_REQUEST
*/
function settings_init() {
    global $mode;

    if (!isset($_REQUEST['mode']) ||
        !in_array($_REQUEST['mode'], array('data','forms','profile','selector','password'))) {
        $_REQUEST['mode'] = 'forms';
    }
    $mode = xss($_REQUEST['mode']);

    if ($mode == "profile") {
        // validate types
        $_REQUEST['profile_id']    = isset($_REQUEST['profile_id']) ? (int) $_REQUEST['profile_id'] : 0;
        $_REQUEST['profile_name']  = isset($_REQUEST['profile_name']) ? xss($_REQUEST['profile_name']) : '';
        $_REQUEST['profile_users'] = isset($_REQUEST['profile_users']) ? (array)  $_REQUEST['profile_users'] : array();

        // validate types in name-array
        foreach ($_REQUEST['profile_users'] as $key => $val) {
            $_REQUEST['profile_users'][$key] = xss($val);
        }
    }
}

?>
