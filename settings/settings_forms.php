<?php

// settings_forms.php - PHProjekt Version 5.2
// copyright  ©  2000-2005 Albrecht Guenther  ag@phprojekt.com
// www.phprojekt.com
// Author: Albrecht Guenther, $Author: polidor $
// $Id: settings_forms.php,v 1.131.2.5 2007/02/22 04:31:50 polidor Exp $

// check whether the lib has been included - authentication!
if (!defined("lib_included")) die("Please use index.php!");


// deliver an array with available modules
require_once(LIB_PATH.'/show_modules.inc.php');

// needed for profiles and stuff
require_once(LIB_PATH.'/selector/selector.inc.php');
require_once(LIB_PATH.'/specialdays.php');

// import language-array
require_once(LIB_PATH.'/languages.inc.php');
$languages = array_flip($languages);

$start_tree_modes           = array('close' => __('closed'), 'open' => __('open'));
$cont_action_values         = array('contacts' => __('External contacts'), 'members' => __('Group members'));
$view_both_values           = array('0' => __('On a separate page'), '1' => __('Below the list'));
if (PHPR_DOWNLOAD_INLINE_OPTION == 1) {
    $file_download_type_values  = array('attachment' => __('Attachment'), 'inline' => __('Inline'));
}
else {
    $file_download_type_values  = array('attachment' => __('Attachment'));
}

$reminder_values            = array('0' => __('none'), '1' => __('Reminder'), '2' => __('Additional alert box'));
$timecard_first_submode_values = array('0' => __('Working times'), '1' => __('Project bookings'));
$reminder_mail_values       = array('0' => __('No'), '1' => __('Yes'));
$timestep_values            = array('5','10','15','20','30','60');
$cut_values                 = array('0' => __('No'), '1' => __('Yes'));
$cal_mode_values            = array('1' => __('Day'), '2' => __('Working week'), '3' => __('Week'), '4' => __('Month'), 'year' => __('Year'), 'view' => __('List'));
$cal_visi_values            = array('0' => __('normal'), '1' => __('private'), '2' => __('public'));
$cal_freq_values            = array('15','30','60');
$chat_entry_type_values     = array('textfield' => __('single line'), 'textarea' => __('multi lines'));
$chat_direction_values      = array('top' => __('Newest messages on top'), 'bottom' => __('Newest messages at bottom'));
$date_format_values         = $date_format_object->get_date_formats(true);
$cal_mail_notify_values     = array('0' => __('Members only'), '1' => __('Members and me'));
$summary_perpage_values     = array('5','10','20','100');
$summary_show_last_login_values = array('0' => __('No'), '1' => __('Yes'));
$accessibility_mode_values       = array('0' => __('Disabled'), '1' => __('Enabled'));
$allow_logintoken_values          = array('0' => __('Disabled'), '1' => __('Enabled'));

$output = get_status_bar();

/******************************
*      change password
******************************/
// Dialog Password
if (PHPR_PW_CHANGE) {
    $hidden_fields = array ( "mode" => "password");
    $password_change = '
    <a name="password"></a>
    <form action="settings.php" method="post" name="password"';
    
    if (PHPR_PW_CHANGE == 2) {
        $password_change .= " onSubmit =\"return chkEqualFields('password','newpw1','newpw1_confirm','".__('The password and confirmation are different')."');\"";
    }
    $password_change .= '>
        '.hidden_fields($hidden_fields).'
        <fieldset>
        <legend>'.__('Password change').'</legend>
';
    // random pw
    if (PHPR_PW_CHANGE == '1') {
        $password_change .= '
        <input type="hidden" name="action" value="1" />
        '.__('In this section you can choose a new random generated password.').'
        <br /><br />
        <label for="password" class="label_block">'.__('Old Password').':</label>
        <input class="settings_options" id="password" type="password" name="password" />
        <br class="clear" />
';
    }

    // choose own pw
    if (PHPR_PW_CHANGE == '2') {
        $password_change .= '
        <input type="hidden" name="action" value="2" />
        <label for="password" class="label_block">'.__('Old password').':</label>
        <input class="settings_options" id="password" type="password" name="password" />
        <br />
        <label for="newpw1" class="label_block">'.__('New Password').':</label>
        <input class="settings_options" type="password" id="newpw1" name="newpw1" />
        <br />
        <label for="newpw1_confirm" class="label_block">'.__('Retype new password').':</label>
        <input class="settings_options" type="password" id="newpw1_confirm" name="newpw1_confirm" />
        <br />
        <hr />
        <b>'.__('Valid characters').':</b><br />
            %!?/#*|().:,;-_123456789<br />
            abcdefghijkmnopqrstuvwxyz<br />
            ABCDEFGHIJKLMANOPQRSTUVWXYZ
        
';
    }

    // submit
    $password_change .= $pout.'
        </fieldset>
        <input type="submit" class="button" name="action_update_password" value="'.__('Modify password').'" />
        <br class="clear" />
    </form>
    <br /><hr />
';
}

/******************************
*        settings
******************************/
// ************
// fetch values
if (is_array($_SESSION['settings'])) {
  $tmp_settings = extract($_SESSION['settings']);
}
// end fetch values
// ****************


// *************
// form settings
// *************

// ****************
// general settings

$out = array();
$out['language'] = $out['group'] = $out['date_format'] = $out['skin'] = $out['startmodule'] = '';
$out['timezone'] = $out['page_reload'] = $out['treemode'] = $out['perpage'] = '';
$out['accessibility_mode'] = $out['allow_logintoken'] = $out['first_day_week'] = '';
$out['defvisi'] = $out['fieldtype'] = $out['chatdir'] = '';

// language
foreach ($languages as $l_long => $l_short) {
    $out['language'] .= '        <option value="'.$l_short.'"'.
    ($l_short == $langua ? ' selected="selected"' : '').
    '>'.$l_long."</option>\n";
}
// gruppen 
foreach ($_SESSION['user_all_groups'] as $group_ID =>$details){
      $out['group'] .= '        <option value="'.$group_ID.'"'.
    ($group_ID == $preferred_group ? ' selected="selected"' : '').
    '>'.$details['kurz']."</option>\n";
    
}
// date format
foreach ($date_format_values as $s_value) {
    $out['date_format'] .= '        <option value="'.$s_value.'"'.
    ($s_value == $date_format ? ' selected="selected"' : '').
    '>'.$s_value."</option>\n";
}
// holiday file
$holiday_classes        = array_keys($specialdays_hierachy);
$sp                     = new SpecialDays($holiday_classes);
$holiday_classes_names  = $sp->get_instances_names();
$cal_hol_file   = (array) $cal_hol_file;
$out['holfile'] = '';
foreach ($specialdays_hierachy as $name=>$arr) {
    $selected = ($name == $cal_hol_file[0]) ? ' selected="selected"' : '';
    $out['holfile'] .= '        <option value="'.$name.'"'.$selected.'>'.
                       str_repeat("&nbsp;",count($arr)*4).
                       $holiday_classes_names[$name]."</option>\n";
}
// skin
$fp = opendir('../layout');
while ($file = readdir($fp)) {
    // Name of dir must not be:
    // 1. the index file or the script an
    // 2. the old dir named css from 3.
    // 3 in case someone left it during the update
    if (!eregi("index.html|CVS|\.", $file) and $file <> 'css') {
        $out['skin'] .= '        <option value="'.$file.'"'.
        ($file == $skin ? ' selected="selected"' : '').
        '>'.$file."</option>\n";
    }
}
// start module
foreach ($mod_arr as $start_mod) {
    list($s_value, , $s_text) = $start_mod;
    // skip few modules
    if(!$s_text || in_array($s_value, array('copyright', 'logout', 'help'))) {
        continue;
    }
    $out['startmodule'] .= '        <option value="'.$s_value.'"'.
    ($s_value == $startmodule ? ' selected="selected"' : '').
    '>'.$s_text."</option>\n";
}
// timezone
for ($i=-23; $i<24; $i++) {
    if (!isset($timezone)) { $timezone = PHPR_TIMEZONE; }
    $out['timezone'] .= '        <option value="'.$i.'"'.
    ($i == $timezone ? ' selected="selected"' : '').
    '>'.$i."</option>\n";
}

//page_reload
$page_array = array(1=>1, 2=>3, 3=>5, 4=>10, 5=>20, 6=>30, 7=>60);
foreach($page_array as $i){
    $sec=$i*60;
$out['page_reload'] .= '
    <option value="'.$sec.'"'.
    ($i == $page_reload/60 ? ' selected="selected"' : '').
    '>'.$i."</option>\n";
}
// treemode
foreach ($start_tree_modes as $s_value => $s_text) {
    $out['treemode'] .= '        <option value="'.$s_value.'"'.
    ($s_value == $start_tree_mode ? ' selected="selected"' : '').
    '>'.$s_text."</option>\n";
}
// perpage
foreach ($perpage_values as $i) {
    $out['perpage'] .= '        <option value="'.$i.'"'.
    ($i == $start_perpage ? ' selected="selected"' : '').
    '>'.$i."</option>\n";
}
foreach ($accessibility_mode_values as $s_value => $s_text) {
    $out['accessibility_mode'] .= '        <option value="'.$s_value.'"'.
    ($s_value == $accessibility_mode ? ' selected="selected"' : '').
    '>'.$s_text."</option>\n";
}

if ((!isset($allow_logintoken)) || (empty($allow_logintoken))) {
    $allow_logintoken = 0;
}
else {
    $allow_logintoken = (int) $allow_logintoken;
}
foreach ($allow_logintoken_values as $s_value => $s_text) {
    $out['allow_logintoken'] .= '        <option value="'.$s_value.'"'.
    ($s_value == $allow_logintoken ? ' selected="selected"' : '').
    '>'.$s_text."</option>\n";
}

$hidden_fields = array ( "mode" => "data");
$settings_html = '
    <a name="settings"></a>
    <form action="settings.php" name="settings" method="post">
    '.hidden_fields($hidden_fields).'
    <br />
    <input type="submit" class="button" name="action_save_settings" value="'.__('Settings').'/'.__('Save').'" />
    <br style="clear:both" />
    <fieldset>
    <legend>'.__('General Settings').'</legend>

    <label for="setting_langua" class="label_block">'.__('Language').':</label>
    <select class="settings_options" name="setting_langua" id="setting_langua">
        <option value=""></option>
        '.$out['language'].'
    </select>
    <br /><br />

    <label for="setting_date_format" class="label_block">'.__('Date format').':</label>
    <select class="settings_options" name="setting_date_format" id="setting_date_format">
        '.$out['date_format'].'
    </select>
    <br /><br />

    <label for="setting_cal_hol_file" class="label_block">'.__('Holiday file').':</label>
    '.((extension_loaded("calendar"))?('<select class="settings_options" name="setting_cal_hol_file" id="setting_cal_hol_file">
    <option value=""></option>
        '.$out['holfile'].'
    </select>
    <br /><br />'):('<span>'.__('Because the calendar extension is not installed, this function is not available.').'</span><br /><br />')).'

    <label for="setting_skin" class="label_block">'.__('Skin').':</label>
    <select class="settings_options" name="setting_skin" id="setting_skin">
        <option value=""></option>
        '.$out['skin'].'
    </select>
    <br /><br />

    <!-- screen resolution -->
    <label for="setting_screen" class="label_block">'.__('Horizontal screen resolution <br />(i.e. 1024, 800)').':</label>
    <input class="settings_options" type="text" maxlength="5" name="setting_screen" id="setting_screen" value="'.$screen.'" />
    <br /><br /><br />

    <label for="setting_group" class="label_block">'.__('First group view on startup').':</label>
    <select class="settings_options" name="setting_group" id="setting_group">
        <option value="0">'.__('Last group before logout').'</option>
        '.$out['group'].'
    </select>
    <br /><br />

    <label for="setting_startmodule" class="label_block">'.__('First module view on startup').':</label>
    <select class="settings_options" name="setting_startmodule" id="setting_startmodule">
        <option value=""></option>
        '.$out['startmodule'].'
    </select>
    <br /><br />

    <label for="setting_timezone" class="label_block">'.__('Timezone difference [h] Server - user').':</label>
    <select class="settings_options" name="setting_timezone" id="setting_timezone">
        <option value=""></option>
        '.$out['timezone'].'
    </select>
    <br /><br />
    <label for="setting_page_reload" class="label_block">'.__('Page Reload').':</label>
    <select class="settings_options" name="setting_page_reload" id="setting_page_reload">
        <option value=""></option>
        '.$out['page_reload'].'
    </select>
    <br /><br />

    <label for="setting_tree_mode" class="label_block">'.__('Treeview mode on module startup').':</label>
    <select class="settings_options" name="setting_tree_mode" id="setting_tree_mode">
        <option value=""></option>
'.$out['treemode'].'
    </select>
    <br /><br />

    <label for="setting_perpage" class="label_block">'.__('Elements per page on module startup').':</label>
    <select class="settings_options" name="setting_perpage" id="setting_perpage">
        <option value=""></option>
        '.$out['perpage'].'
    </select>
    <br /><br />
    
    <label for="setting_accessibility_mode" class="label_block">'.__('Accessibility mode').':</label>
    <select class="settings_options" name="setting_accessibility_mode" id="setting_accessibility_mode">
        <option value=""></option>
        '.$out['accessibility_mode'].'
    </select>
    <br /><br />
    
    <label for="setting_allow_logintoken" class="label_block">'.__('Logintoken for notification mails').':</label>
    <select class="settings_options" name="setting_allow_logintoken" id="setting_allow_logintoken">
        <option value=""></option>
        '.$out['allow_logintoken'].'
    </select>
    <br /><br />
    </fieldset>

';
// ********
// calendar
if (PHPR_CALENDAR) {

    $cal_leftframe = isset($cal_leftframe) ? (int) $cal_leftframe : 210;
    $timestep_daily = isset($timestep_daily) ? (int) $timestep_daily : 15;
    $timestep_weekly = isset($timestep_weekly) ? (int) $timestep_weekly : 15;
    $ppc = isset($ppc) ? (int) $ppc : 6;
    $cut = isset($cut) ? qss($cut) : '1';

    $out = array();
    $out['starttime'] = $out['endtime'] = '';
    $out['stepday'] = $out['stepweek'] = $out['textcut'] = $out['defview1'] = $out['calrefresh'] = '';
    $out['calnotify'] = $out['timecard_first_submode'] = $out['remwindow'] = $out['remmail'] = '';
    $out['first_day_week'] = $out['defvisi'] = '';

    // first day of the week
    if (isset($_POST['setting_first_day_week'])) {
        $first_day_week = xss($_POST['setting_first_day_week']);
    }
    else if (!isset($settings['first_day_week'])) {
        $first_day_week = 1;
    }
    else {
        $first_day_week = $settings['first_day_week'];
    }
    for ($ii=0; $ii<7; $ii++) {
        $out['first_day_week'] .= '        <option value="'.$ii.'"'.
        ($ii == $first_day_week ? ' selected="selected"' : '').
        '>'.$name_day[$ii]."</option>\n";
    }
    
    // start time
    if (isset($_POST['setting_tagesanfang'])) {
        $tagesanfang = xss($_POST['setting_tagesanfang']);
    }
    else if (!isset($settings['tagesanfang'])) {
        $tagesanfang = PHPR_DAY_START;
    }
    else {
        $tagesanfang = $settings['tagesanfang'];
    }
    for ($i=0; $i<24; $i++) {
        $out['starttime'] .= '        <option value="'.$i.'"'.
        ($i == $tagesanfang ? ' selected="selected"' : '').
        '>'.$i."</option>\n";
    }
    // end time
    if (isset($_POST['setting_tagesende'])) {
        $tagesende = xss($_POST['setting_tagesende']);
    }
    else if (!isset($settings['tagesende'])) {
        $tagesende = PHPR_DAY_END;
    }
    else {
        $tagesende = $settings['tagesende'];
    }

    if ($tagesanfang >= $tagesende) {
        $tagesanfang = PHPR_DAY_START;
        $tagesende   = PHPR_DAY_END;
    }

    for ($i=1; $i<=24; $i++) {
        $out['endtime'] .= '        <option value="'.$i.'"'.
        ($i == $tagesende ? ' selected="selected"' : '').
        '>'.$i."</option>\n";
    }
    // timestep day
    foreach ($timestep_values as $i) {
        $out['stepday'] .= '        <option value="'.$i.'"'.
        ($i == $timestep_daily ? ' selected="selected"' : '').
        '>'.$i."</option>\n";
    }
    // timestep week
    foreach ($timestep_values as $i) {
        $out['stepweek'] .= '        <option value="'.$i.'"'.
        ($i == $timestep_weekly ? ' selected="selected"' : '').
        '>'.$i."</option>\n";
    }
    foreach ($cut_values as $s_value => $s_text) {
        $out['textcut'] .= '        <option value="'.$s_value.'"'.
        ($s_value == $cut ? ' selected="selected"' : '').
        '>'.$s_text."</option>\n";
    }
    // default view 1
    foreach ($cal_mode_values as $s_value => $s_text) {
        $out['defview1'] .= '        <option value="'.$s_value.'"'.
        ($s_value == $cal_mode ? ' selected="selected"' : '').
        '>'.$s_text."</option>\n";
    }
    // calendar refresh rate
    foreach ($cal_freq_values as $s_value) {
        $out['calrefresh'] .= '        <option value="'.$s_value.'"'.
        ($s_value == $cal_freq ? ' selected="selected"' : '').
        '>'.$s_value."</option>\n";
    }
    // default visibility
    foreach ($cal_visi_values as $s_value => $s_text) {
        $out['defvisi'] .= '        <option value="'.$s_value.'"'.
        ($s_value == $cal_visi ? ' selected="selected"' : '').
        '>'.$s_text."</option>\n";
    }
    // calendar email notify
    foreach ($cal_mail_notify_values as $s_value => $s_text) {
        $out['calnotify'] .= '        <option value="'.$s_value.'"'.
        ($s_value == $cal_mail_notify ? ' selected="selected"' : '').
        '>'.$s_text."</option>\n";
    }

    // ***
    // begin getting related user
    require_once('../calendar/calendar.inc.php');
    // end getting related user
    // ***
    
    // timecard first submode
    $timecard_first_submode_selected = isset($_REQUEST['setting_timecard_first_submode']) ? xss($_REQUEST['setting_timecard_first_submode']) : (isset($settings['timecard_first_submode']) ? $settings['timecard_first_submode'] : 0);
    foreach ($timecard_first_submode_values as $s_value => $s_text) {
        $out['timecard_first_submode'] .= '        <option value="'.$s_value.'"'.
        ($s_value == $timecard_first_submode_selected ? ' selected="selected"' : '').
        '>'.$s_text."</option>\n";
    }
    
    // reminder window
    $reminder_selected = isset($_REQUEST['setting_reminder']) ? xss($_REQUEST['setting_reminder']) : (isset($settings['reminder']) ? $settings['reminder'] : PHPR_REMINDER);
    foreach ($reminder_values as $s_value => $s_text) {
        $out['remwindow'] .= '        <option value="'.$s_value.'"'.
        ($s_value == $reminder_selected ? ' selected="selected"' : '').
        '>'.$s_text."</option>\n";
    }
    // reminder mail
    if (PHPR_QUICKMAIL == 2) {
        foreach ($reminder_mail_values as $s_value => $s_text) {
            $out['remmail'] .= '        <option value="'.$s_value.'"'.
            ($s_value == $reminder_mail ? ' selected="selected"' : '').
            '>'.$s_text."</option>\n";
        }
    }

    $settings_html .= '
    <fieldset>
    <legend>'.__('Calendar').'</legend>

    <label for="setting_first_day_week" class="label_block">'.__('First day of the week:').'</label>
    <select class="settings_options" name="setting_first_day_week" id="setting_first_day_week">
        '.$out['first_day_week'].'
    </select>
    <br /><br />

    <label for="setting_tagesanfang" class="label_block">'.__('First hour of the day:').'</label>
    <select class="settings_options" name="setting_tagesanfang" id="setting_tagesanfang">
        <option value=""></option>
        '.$out['starttime'].'
    </select>
    <br /><br />

    <label for="setting_tagesende" class="label_block">'.__('Last hour of the day:').'</label>
    <select class="settings_options" name="setting_tagesende" id="setting_tagesende">
        <option value=""></option>
    '.$out['endtime'].'
    </select>
    <br /><br />

    <label for="setting_timestep_daily" class="label_block">'.__('Timestep Daywiew [min]').':</label>
    <select class="settings_options" name="setting_timestep_daily" id="setting_timestep_daily">
    '.$out['stepday'].'
    </select>
    <br /><br />

    <label for="setting_timestep_weekly" class="label_block">'.__('Timestep Weekwiew [min]').':  </label>
    <select class="settings_options" name="setting_timestep_weekly" id="setting_timestep_weekly">
    '.$out['stepweek'].'
    </select>
    <br /><br />

<!-- TODO: this could be removed now... hm..
    <label for="setting_ppc" class="label_block">'.__('px per char for event text<br />(not exact in case of proportional font)').'</label>
    <input class="settings_options" type="text" maxlength="2" name="setting_ppc" id="setting_ppc" size="2" value="'.$ppc.'" />
    <br /><br /><br />

    <label for="setting_cut" class="label_block">'.__('Text length of events will be cut').':</label>
    <select class="settings_options" name="setting_cut" id="setting_cut">
    '.$out['textcut'].'
    </select>
    <br /><br />
//-->

    <label for="setting_cal_mode" class="label_block">'.__('Standard View').':</label>
    <select class="settings_options" name="setting_cal_mode" id="setting_cal_mode">
    '.$out['defview1'].'
    </select>
    <br /><br />

    <label for="setting_cal_freq" class="label_block">'.__('View refresh rate [min]').':</label>
    <select class="settings_options" name="setting_cal_freq" id="setting_cal_freq">
    <option value=""></option>
    '.$out['calrefresh'].'
    </select>
    <br /><br />

    <label for="setting_cal_visi" class="label_block">'.__('Visibility presetting when creating an event').':</label>
    <select class="settings_options" name="setting_cal_visi" id="setting_cal_visi">
    '.$out['defvisi'].'
    </select>
    <br /><br />

    <label for="setting_cal_mail_notify" class="label_block">'.__('Send email notification').':</label>
    <select class="settings_options" name="setting_cal_mail_notify" id="setting_cal_mail_notify">
    '.$out['calnotify'].'
    </select>
    <br /><br />

    <label for="setting_cal_viewer" class="label_block">'.$private_events_title.':</label>
    '.selector_create_select_users("setting_cal_viewer[]", $setting_cal_viewer, 'action_related_viewer_to_selector', $user_ID, 'id="setting_cal_viewer" style="vertical-align:top;"').'
    <br /><br />

    <label for="setting_cal_reader" class="label_block">'.__('Users, who can read my normal events').':</label>
    '.selector_create_select_users("setting_cal_reader[]", $setting_cal_reader, 'action_related_reader_to_selector',  $user_ID, 'id="setting_cal_reader" style="vertical-align:top;"').'
    <br /><br />

    <label for="setting_cal_proxy" class="label_block">'.__('Users, who can represent me').':</label>
    '.selector_create_select_users("setting_cal_proxy[]", $setting_cal_proxy, 'action_related_proxy_to_selector', $user_ID, 'id="setting_cal_proxy" style="vertical-align:top;"').'
    <br /><br />
    </fieldset>

    <fieldset>
    <legend>'.__('Timecard').'</legend>
    <label for="setting_timecard_first_submode" class="label_block">'.__('First view on module startup').':</label>
    <select class="settings_options" name="setting_timecard_first_submode" id="setting_timecard_first_submode">
    '.$out['timecard_first_submode'].'
    </select>
    <br /><br />
    </fieldset>

    <fieldset>
    <legend>'.__('Reminder').'</legend>

    <label for="setting_reminder" class="label_block">'.__('Reminder').':</label>
    <select class="settings_options" name="setting_reminder" id="setting_reminder">
    '.$out['remwindow'].'
    </select>
    <br /><br />

    <label for="setting_remind_freq" class="label_block">'.__('max. minutes before the event').':</label>
    <input class="settings_options" type="text" name="setting_remind_freq" id="setting_remind_freq" size="2" value="'.
    (isset($_REQUEST['setting_remind_freq']) ? xss($_REQUEST['setting_remind_freq']) : (isset($settings['remind_freq']) ? $settings['remind_freq'] : PHPR_REMIND_FREQ))
    .'" />
    <br /><br />
';

    if(PHPR_QUICKMAIL == 2){
        $settings_html .= '
        <label for="setting_reminder_mail" class="label_block">'.__('Check for mail').':</label>
        <select class="settings_options" name="setting_reminder_mail" id="setting_reminder_mail">
        '.$out['remmail'].'
        </select>
        <br /><br />';
    }

    $settings_html .= '
    </fieldset>
';
}
// ********
// contacts
if (PHPR_CONTACTS) {
    $out = '';
    foreach ($cont_action_values as $s_value => $s_text) {
        $out .= '        <option value="'.$s_value.'"'.
        ($s_value == $cont_action ? ' selected="selected"' : '').
        '>'.$s_text."</option>\n";
    }

    $settings_html .= '
    <fieldset>
    <legend>'.__('Contacts').'</legend>

    <label for="setting_cont_action" class="label_block">'.__('First view on module startup').':</label>
    <select class="settings_options" name="setting_cont_action" id="setting_cont_action">
    <option value=""></option>
    '.$out.'
    </select>
    <br />
    </fieldset>
';
}

// list view

// Group View
$out=array();
$show_all_groups_selected = isset($_REQUEST['setting_show_all_groups']) ? xss($_REQUEST['setting_show_all_groups']) : (isset($settings['show_all_groups']) ? $settings['show_all_groups'] : 0);
$out['show_all_groups'] = '        <option value="0"';
$out['show_all_groups'] .=(0 == $show_all_groups_selected ? ' selected="selected"' : '').
'>'.__('Show current group only')."</option>\n";
$out['show_all_groups'] .= '        <option value="1"';
$out['show_all_groups'] .=(1 == $show_all_groups_selected ? ' selected="selected"' : '').
'>'.__('Show all groups')."</option>\n";

$settings_html .= '
    </fieldset>
    <fieldset>
    <legend>'.__('List View').'</legend>
    <label for="setting_show_all_groups" class="label_block">'.__('Elements shown on startup').':</label>
    <select name="setting_show_all_groups" id="setting_show_all_groups">
    '.$out['show_all_groups'] .'
    </select>
    <br /><br />
    </fieldset>';

// ********
// chat
if (PHPR_CHAT) {
    $out = array();
    $out['fieldtype'] = '';
    $out['chatdir'] = '';
    // type input field
    foreach ($chat_entry_type_values as $s_value => $s_text) {
        $out['fieldtype'] .= '<option value="'.$s_value.'"'.
                             ($s_value == $chat_entry_type ? ' selected="selected"' : '').
                             '>'.$s_text."</option>\n";
    }
    // chat direction - newest message on top or bottom
    foreach ($chat_direction_values as $s_value => $s_text) {
        $out['chatdir'] .= '<option value="'.$s_value.'"'.
                           ($s_value == $chat_direction ? ' selected="selected"' : '').
                           '>'.$s_text."</option>\n";
    }

    $settings_html .= '
    <fieldset>
    <legend>'.__('Chat').'</legend>

    <label for="setting_chat_entry_type" class="label_block">'.__('Chat Entry').':</label>
    <select name="setting_chat_entry_type" id="setting_chat_entry_type">
    <option value=""></option>
'.$out['fieldtype'].'
    </select>
    <br /><br />

    <label for="setting_chat_direction" class="label_block">'.__('Chat Direction').':</label>
    <select name="setting_chat_direction" id="setting_chat_direction">
    <option value=""></option>
'.$out['chatdir'].'
    </select>
    <br />
    </fieldset>
';
}
// ********
// file manager
if (PHPR_FILEMANAGER) {
    $out = '';
    foreach ($file_download_type_values as $s_value => $s_text) {
        $out .= '<option value="'.$s_value.'"'.
        ($s_value == $file_download_type ? ' selected="selected"' : '').
        '>'.$s_text."</option>\n";
    }

    $settings_html .= '
    <fieldset>
    <legend>'.__('Files').'</legend>

    <label for="setting_file_download_type" class="label_block">'.__('File Downloads').':</label>
    <select name="setting_file_download_type" id="setting_file_download_type">
    <option value=""></option>
'.$out.'
    </select>
    <br />
    </fieldset>
';
}

// ********
// Mail
if (PHPR_QUICKMAIL) {
    $default_html = $default_text = '';
    if ($compose_mail_default == 'html') {
        $default_html = 'selected';
    }
    else {
        $default_text = 'selected';
    }
    
    $settings_html .= '
    <fieldset>
    <legend>'.__('Mail').'</legend>

    <label for="setting_compose_mail_Default" class="label_block">'.__('Mail compose default').':</label>
    <select name="compose_mail_default">
        <option value="text" '.$default_text.' >'.__("Text").'</option>
        <option value="html" '.$default_html.' >'.__("HTML").'</option>
    </select>
    <br />
    </fieldset>
';
}

// ********
// Summary
// perpage
$out = array();
$out['summary_perpage'] = '';
$out['summary_show_last_login'] = '';
foreach ($summary_perpage_values as $i) {
    $out['summary_perpage'] .= '        <option value="'.$i.'"'.
    ($i == $summary_perpage ? ' selected="selected"' : '').
    '>'.$i."</option>\n";
}

// show since last login
foreach ($summary_show_last_login_values as $s_value => $s_text) {
    $out['summary_show_last_login'] .= '        <option value="'.$s_value.'"'.
    ($s_value == $summary_show_last_login ? ' selected="selected"' : '').
    '>'.$s_text."</option>\n";
}

$settings_html .= '
    <fieldset>
    <legend>'.__('Summary').'</legend>

    <label for="setting_summary_perpage" class="label_block">'.__('Records to show of each module').':</label>
    <select class="settings_options" name="setting_summary_perpage" id="setting_summary_perpage">
        <option value=""></option>
        '.$out['summary_perpage'].'
    </select>
    
    <br />
    <br />
    <label for="setting_summary_show_last_login" class="label_block">'.__('Show only records since my last login').':</label>
    <select name="setting_summary_show_last_login" id="setting_summary_show_last_login">
        <option value=""></option>
        '.$out['summary_show_last_login'].'
    </select>
    </fieldset>
';

// form end
$settings_html .= '
    <input type="submit" class="button" name="action_save_settings" value="'.__('Settings').'/'.__('Save').'" />
    </form>
    <br /><hr />
';


$hidden_fields = array ( "mode" => "profile");
$profiles_html = '
<a name="profile"></a>
    <form action="settings.php#profile" method="post" name="choose_profile">
        '.hidden_fields($hidden_fields).'
        <fieldset>
        <legend>'.__('Profiles').'</legend>
        <input type="submit" class="button" name="action_new_profile" value="'.__("New").'" />
        <span class="strich">&nbsp;</span>&nbsp;
        <select name="profile_id">
            <option value=""></option>
'.list_profilenames().'
        </select>
        <input type="submit" class="button" name="action_edit_profile"   value="'.__("Modify").'" />
        <input type="submit" class="button" name="action_delete_profile" value="'.__("Delete").'" />
        </fieldset>
    </form>
';
if (isset($_REQUEST['action_edit_profile']) ||
    isset($_REQUEST['action_new_profile'])  ||
    isset($_REQUEST['action_selector_to_profile']) ||
    isset($_REQUEST['action_access_selector_to_form_ok'])) {
    $profiles_html .= show_profile_edit_form();
}

$output .= '
<br />
<div class="inner_content">
    <a name="content"></a>
    '.$password_change.'
    <div class="boxContent">'.$settings_html.'</div>
    <div class="boxContent">'.$profiles_html.'</div>
</div>
';


echo $output;


// -----------------------------------------------
// ------------ only functions below -------------
/**
* List all profiles of this user between option-tags and return that string
*/
function list_profilenames() {
    global $user_ID;

    $ret = '';
    $query = "SELECT ID, bezeichnung
                FROM ".DB_PREFIX."profile
               WHERE von = ".(int)$user_ID." 
            ORDER BY bezeichnung";
    $result = db_query($query) or db_die();
    $active_profile = (isset($_REQUEST['profile_id'])) ? intval($_REQUEST['profile_id']) : 0;
    while ($row = db_fetch_row($result)) {
        $selected = ($active_profile == $row[0]) ? ' selected="selected"' : '';
        $ret .= "<option value='".$row[0]."'$selected>".$row[1]."</option>\n";
    }
    return $ret;
}

/**
* create the form that is used to creade and modify a certain profile
*/
function show_profile_edit_form() {
    global $user_ID, $formdata;

    // Label the submit button
    if (isset($_REQUEST['action_new_profile'])) {
        $legend = __('Add profile');
        $submit = __('OK');
    }
    else {
        $legend = __('Edit profile');
        $submit = __('Modify');
    }

    // get profile-data if an existing profile should be edited
    // this can happen when clicking the modify button OR when coming back from the selector
    if ( isset($_REQUEST['action_new_profile']) ) {
        $formdata['profile_name']   = '';
        $formdata['profile_users']  = '';
        $formdata['persons']        = '';
    } else if (isset($_REQUEST['profile_id'])) {
        $tmp = get_profile_from_user(intval($_REQUEST['profile_id']));
        if (isset($_REQUEST['action_selector_to_profile'])) {
            $formdata['profile_name']   = xss($_REQUEST['profile_name']);
            $formdata['persons']        = serialize(xss_array($_REQUEST['persons']));
        } else if (isset($_REQUEST['action_access_selector_to_form_ok'])) {
            $formdata['profile_name']   = xss($_REQUEST['profile_name']);
            $formdata['profile_users']  = xss($_REQUEST['profile_users']);
            $formdata['persons']        = serialize($formdata['persons']);
        } else {
            $formdata['profile_name']  = $tmp['bezeichnung'];
            $formdata['profile_users'] = $tmp['personen'];
            $formdata['persons']       = $tmp['acc'];
        }
        unset($tmp);
    }

    require_once(LIB_PATH."/access_form.inc.php");
    $form_fields = array();

    $form_fields[] = array('type' => 'parsed_html', 'html' => access_form2($formdata['persons'], 1, 0, 0, 0));
    $assignment_fields = get_form_content($form_fields);

    // now build the form
    $hidden_fields = array ( "mode"         => "profile",
                             "profile_id"   => xss($_POST['profile_id']));
    $ret = '
    <div class="formbody">
    <form action="./settings.php#profile" method="post" name="edit_profile" onsubmit="return chkForm(\'edit_profile\',\'profile_name\',\''.__('Please insert a name').'!\');">
        <fieldset>
            <legend>'.$legend.'</legend>
            '.hidden_fields($hidden_fields).'
            <label for="profile_name" class="settings">'.__('Name').':</label>
            <input type="text" name="profile_name" maxlength="20" id="profile_name" value="'.$formdata['profile_name'].'" />
            <br /><br />

            <label for="profile_users" class="settings">'.__('Persons').':</label>
            '.selector_create_select_users("profile_users[]", $formdata['profile_users'], 'action_profile_to_selector', '0', 'style="vertical-align:top;" id="profile_users" ').'
            <br style="clear:both" /><br />

            <div class="boxContent">'.$assignment_fields.'</div>
            <br style="clear:both" /><br />

            <!-- <input type="submit" name="action_profile_to_selector" value="'.__('selector').'" /> -->
            <input type="submit" class="button" name="action_write_profile" value="'.$submit.'" />
        </fieldset>
    </form>
    </div>
';
    return $ret;
}

?>
