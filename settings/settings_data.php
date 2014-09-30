<?php

// settings_data.php - PHProjekt Version 5.2
// copyright  ©  2000-2005 Albrecht Guenther  ag@phprojekt.com
// www.phprojekt.com
// Author: Albrecht Guenther, $Author: gustavo $
// $Id: settings_data.php,v 1.36.2.1 2007/01/13 16:42:00 gustavo Exp $


// check whether the lib has been included - authentication!
if (!defined("lib_included")) die("Please use index.php!");

// check form token
check_csrftoken();

// write common settings:

// get former settings from SESSION
$tmp_settings = $_SESSION['settings'];
// for php5 compability
if (!is_array($tmp_settings)) $tmp_settings = array();
// import and merge settings from request
$settings = settings_get_request_settings();
if (array_key_exists($settings['cal_hol_file'], $specialdays_hierachy)) {
    $settings['cal_hol_file'] = array_merge(array($settings['cal_hol_file']),
    $specialdays_hierachy[$settings['cal_hol_file']]);
}

$tmp_settings = array_merge($tmp_settings, $settings);

//write new settings into database
$_SESSION['settings'] = $tmp_settings;

// serialize new settings-array and write it to db
$result = db_query("UPDATE ".DB_PREFIX."users
                       SET settings = '".serialize($tmp_settings)."'
                     WHERE ID = ".(int)$user_ID) or db_die();

// proxy user for calendar system
if (PHPR_CALENDAR) {
    include_once('../calendar/calendar.inc.php');
    include_once(LIB_PATH.'/selector/selector.inc.php');
    calendar_set_related_users($setting_cal_viewer, 'viewer');
    calendar_set_related_users($setting_cal_reader, 'reader');
    calendar_set_related_users($setting_cal_proxy,  'proxy');
}

?>
