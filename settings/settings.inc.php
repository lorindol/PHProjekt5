<?php

// settings.inc.php - PHProjekt Version 5.1
// copyright  ©  2000-2005 Albrecht Guenther  ag@phprojekt.com
// www.phprojekt.com
// Author: Franz Graf, $Author: alexander $
// $Id: settings.inc.php,v 1.34.2.1 2007/01/18 11:41:10 alexander Exp $

// check whether the lib has been included - authentication!
if (!defined("lib_included")) die("Please use index.php!");


/**
* Get the variables from _REQUEST that should be serialized.
*
* @return array settings-array
*/
function settings_get_request_settings() {

    if (isset($_REQUEST['setting_tagesanfang']) && isset($_REQUEST['setting_tagesende']) && $_REQUEST['setting_tagesanfang'] >= $_REQUEST['setting_tagesende']) {
        $_REQUEST['setting_tagesanfang'] = PHPR_DAY_START;
        $_REQUEST['setting_tagesende']   = PHPR_DAY_END;
    }

    $temp = array();
    $temp['langua']                 = $GLOBALS['langua']                = isset($_REQUEST['setting_langua']) ? xss($_REQUEST['setting_langua']) : '';
    $temp['preferred_group']        = $GLOBALS['preferred_group']       = isset($_REQUEST['setting_group']) ? xss($_REQUEST['setting_group']) : '';
    $temp['skin']                   = $GLOBALS['skin']                  = isset($_REQUEST['setting_skin']) ? xss($_REQUEST['setting_skin']) : '';
    $temp['screen']                 = $GLOBALS['screen']                = isset($_REQUEST['setting_screen']) ? xss($_REQUEST['setting_screen']) : '';
    $temp['startmodule']            = $GLOBALS['startmodule']           = isset($_REQUEST['setting_startmodule']) ? xss($_REQUEST['setting_startmodule']) : '';
    $temp['timezone']               = $GLOBALS['timezone']              = isset($_REQUEST['setting_timezone']) ? xss($_REQUEST['setting_timezone']) : '';
    $temp['page_reload']            = $GLOBALS['page_reload']           = isset($_REQUEST['setting_page_reload']) ? xss($_REQUEST['setting_page_reload']) : '';
    $temp['start_tree_mode']        = $GLOBALS['start_tree_mode']       = isset($_REQUEST['setting_tree_mode']) ? xss($_REQUEST['setting_tree_mode']) : '';
    $temp['start_perpage']          = $GLOBALS['start_perpage']         = isset($_REQUEST['setting_perpage']) ? xss($_REQUEST['setting_perpage']) : '';
    $temp['accessibility_mode']     = $GLOBALS['accessibility_mode']    = isset($_REQUEST['setting_accessibility_mode']) ? xss($_REQUEST['setting_accessibility_mode']) : '';
    $temp['allow_logintoken']       = $GLOBALS['allow_logintoken']      = isset($_REQUEST['setting_allow_logintoken']) ? xss($_REQUEST['setting_allow_logintoken']) : '';
    $temp['tagesanfang']            = $GLOBALS['tagesanfang']           = isset($_REQUEST['setting_tagesanfang']) ? xss($_REQUEST['setting_tagesanfang']) : '';
    $temp['tagesende']              = $GLOBALS['tagesende']             = isset($_REQUEST['setting_tagesende']) ? xss($_REQUEST['setting_tagesende']) : '';
    $temp['cal_hol_file']           = $GLOBALS['cal_hol_file']          = isset($_REQUEST['setting_cal_hol_file']) ? xss($_REQUEST['setting_cal_hol_file']) : '';
    $temp['cal_visi']               = $GLOBALS['cal_visi']              = isset($_REQUEST['setting_cal_visi']) ? xss($_REQUEST['setting_cal_visi']) : '';
    $temp['timestep_daily']         = $GLOBALS['timestep_daily']        = isset($_REQUEST['setting_timestep_daily']) ? xss($_REQUEST['setting_timestep_daily']) : '';
    $temp['timestep_weekly']        = $GLOBALS['timestep_weekly']       = isset($_REQUEST['setting_timestep_weekly']) ? xss($_REQUEST['setting_timestep_weekly']) : '';
    $temp['ppc']                    = $GLOBALS['ppc']                   = isset($_REQUEST['setting_ppc']) ? xss($_REQUEST['setting_ppc']) : '';
    $temp['cut']                    = $GLOBALS['cut']                   = isset($_REQUEST['setting_cut']) ? xss($_REQUEST['setting_cut']) : '';
    $temp['cal_mode']               = $GLOBALS['cal_mode']              = isset($_REQUEST['setting_cal_mode']) ? xss($_REQUEST['setting_cal_mode']) : '';
    $temp['cal_freq']               = $GLOBALS['cal_freq']              = isset($_REQUEST['setting_cal_freq']) ? xss($_REQUEST['setting_cal_freq']) : '';
    $temp['timecard_submode']       = $GLOBALS['timecard_submode']      = isset($_REQUEST['setting_timecard_submode']) ? xss($_REQUEST['setting_timecard_submode']) : '';
    $temp['timecard_first_submode'] = $GLOBALS['timecard_first_submode']= isset($_REQUEST['setting_timecard_first_submode']) ? xss($_REQUEST['setting_timecard_first_submode']) : '';
    $temp['reminder']               = $GLOBALS['reminder']              = isset($_REQUEST['setting_reminder']) ? xss($_REQUEST['setting_reminder']) : '';
    $temp['remind_freq']            = $GLOBALS['remind_freq']           = isset($_REQUEST['setting_remind_freq']) ? xss($_REQUEST['setting_remind_freq']) : '';
    $temp['reminder_mail']          = $GLOBALS['reminder_mail']         = isset($_REQUEST['setting_reminder_mail']) ? xss($_REQUEST['setting_reminder_mail']) : '';
    $temp['cont_action']            = $GLOBALS['cont_action']           = isset($_REQUEST['setting_cont_action']) ? xss($_REQUEST['setting_cont_action']) : '';
    $temp['chat_entry_type']        = $GLOBALS['chat_entry_type']       = isset($_REQUEST['setting_chat_entry_type']) ? xss($_REQUEST['setting_chat_entry_type']) : '';
    $temp['chat_direction']         = $GLOBALS['chat_direction']        = isset($_REQUEST['setting_chat_direction']) ? xss($_REQUEST['setting_chat_direction']) : '';
    $temp['forum_view_both']        = $GLOBALS['forum_view_both']       = isset($_REQUEST['setting_forum_view_both']) ? xss($_REQUEST['setting_forum_view_both']) : '';
    $temp['file_download_type']     = $GLOBALS['file_download_type']    = isset($_REQUEST['setting_file_download_type']) ? xss($_REQUEST['setting_file_download_type']) : '';
    $temp['notes_view_both']        = $GLOBALS['notes_view_both']       = isset($_REQUEST['setting_notes_view_both']) ? xss($_REQUEST['setting_notes_view_both']) : '';
    $temp['date_format']            = $GLOBALS['date_format']           = isset($_REQUEST['setting_date_format']) ? xss($_REQUEST['setting_date_format']) : '';
    $temp['first_day_week']         = $GLOBALS['first_day_week']        = isset($_REQUEST['setting_first_day_week']) ? xss($_REQUEST['setting_first_day_week']) : '';
    $temp['compose_mail_default']   = $GLOBALS['compose_mail_default']  = isset($_REQUEST['compose_mail_default']) ? xss($_REQUEST['compose_mail_default']) : '';
    $temp['cal_mail_notify']        = $GLOBALS['cal_mail_notify']       = isset($_REQUEST['setting_cal_mail_notify']) ? xss($_REQUEST['setting_cal_mail_notify']) : '';
    $temp['show_all_groups_settings']        = $GLOBALS['show_all_groups_settings']     = isset($_REQUEST['setting_show_all_groups']) ? xss($_REQUEST['setting_show_all_groups']) : '';
    $temp['summary_perpage']        = $GLOBALS['summary_perpage']       = isset($_REQUEST['setting_summary_perpage']) ? xss($_REQUEST['setting_summary_perpage']) : '';
    $temp['summary_show_last_login']         = $GLOBALS['summary_show_last_login']      = isset($_REQUEST['setting_summary_show_last_login']) ? xss($_REQUEST['setting_summary_show_last_login']) : '';

    return $temp;
}


/**
* Fetch the profiledata of ID $id from the DB.
* It is checked whether this profile really belongs to the current user or not.
* The values of the array will be empty if a profile is selected that
* does not belong to the current user,
*
* @access public
* @uses $user_ID    ID of the current user
* @param int $id    ID of the profile to fetch
* @return array     Array with the keys: ID, bezeichnung, personen.
*                   'personen' references the deserialized arrays of shortnames(!)
*/
function get_profile_from_user($id) {
    global $user_ID;

    $id = (int) $id;
    $data = array( "ID"          => $id,
                   "bezeichnung" => "",
                   "personen"    => array(),
                   "acc"         => "" );

    $query = "SELECT bezeichnung, personen, acc
                FROM ".DB_PREFIX."profile
               WHERE ID  = ".(int)$id." 
                 AND von = ".(int)$user_ID;
    $res = db_query($query) or db_die();
    if ($row = db_fetch_row($res)) {
        $data['bezeichnung'] = $row[0];
        $data['personen']    = unserialize($row[1]);
        if (!$data['personen']) {
            $data['personen'] = array();
        }
        $data['acc'] = $row[2];
    }

    // convert user(s) 'kurz' to 'id' for the selector => urghs
    if (count($data['personen']) > 0) {
        $kurz = "'".implode("','", $data['personen'])."'";
        $data['personen'] = array();
        $query = "SELECT ID
                    FROM ".DB_PREFIX."users
                   WHERE kurz IN ($kurz)";
        $res = db_query($query) or db_die();
        while ($row = db_fetch_row($res)) {
            $data['personen'][] = $row[0];
        }
    }

    return $data;
}

?>
