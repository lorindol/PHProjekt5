<?php
/**
 * @package    settings
 * @subpackage main
 * @author     Franz Graf, $Author: gustavo $
 * @licence    GPL, see www.gnu.org/copyleft/gpl.html
 * @copyright  2000-2006 Mayflower GmbH www.mayflower.de
 * @version    $Id: settings_data.php,v 1.39 2007-05-31 08:13:46 gustavo Exp $
 */

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
