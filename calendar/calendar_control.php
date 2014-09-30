<?php
/**
* handles the controls for the default calendar view
*
* @package    calendar
* @module     main
* @author     Paolo Panto, $Author: alexander $
* @licence    GPL, see www.gnu.org/copyleft/gpl.html
* @copyright  2000-2006 Mayflower GmbH www.mayflower.de
* @version    $Id: calendar_control.php,v 1.129.2.2 2007/01/24 14:27:35 alexander Exp $
*/
if (!defined('lib_included')) die('Please use index.php!');


$event_owner = $user_ID;
$event_group = $user_group;
$date_selector_form_header = false;

// context menu
// Only for the list mode view
$oncontextmenu = '';
if ($mode == "view") {
    require_once(PATH_PRE.'lib/contextmenu.inc.php');
    $menu3 = new contextmenu();
    echo $menu3->menu_page('calendar');
    $oncontextmenu = 'oncontextmenu="startMenu(\''.$menu3->menusysID.'\',\'\',this)"';
}
// end context menu
// ****************

$buttons = array();
$output = '<div id="global-header">';
$output .= calendar_tab_selection();
$output .= breadcrumb($module, breadcrumb_data($mode, $view));
$output .= '</div>';


if ($justform > 0) {
    $output .= '<div id="global-content" class="popup">';
} else {
    $output .= '<div id="global-content">';
}

if (!calendar_is_selector_view()) {
    $output .= calendar_view_selection($oncontextmenu);
}

if ( !in_array($mode, array('forms','view','data')) &&
     ($view != 4 || ($view == 4 && $act_for)) ) {
    if (!$date_selector_form_header) {
        $output .= calendar_date_selection_header();
    }
    #$output .= calendar_date_selection();
}

if ($view == 3 && !in_array($mode, array('forms','data'))) {
    if (!$date_selector_form_header) {
        $output .= calendar_date_selection_header();
    }
    $output .= calendar_combi_selection();
}
else if ($view == 4) {
    $output .= calendar_act_for_selection();
}

// add date selection footer
if ($date_selector_form_header) {
    $output .= calendar_date_selection_footer();
}

if (!message_stack_is_empty()) {
    $output .= calendar_view_status();
}
if (!in_array($mode, array('forms','view','data'))) {
    $output .= '<div class="calendar_datepicker">'."\n";
    //if ($view && $view != 4) $output .= calendar_combi_selection();
    //else                     $output .= calendar_show_datepicker();
    $output .= "\n</div>\n\n";
}

echo $output;


/**
 * get the calendar tab selection
 *
 * @return string
 */
function calendar_tab_selection() {
    global $mode, $view, $year, $month, $day, $sid;

    // common checks
    if ((!$view || $view == 4) && !in_array($mode, array(1,2,3,4,'year','view','forms','data'))) {
        $mode = 1;
    }

    $_mode   = ($mode=='forms' || $mode=='data') ? 1 : $mode;
    $_mode2  = (is_numeric($_mode) || $_mode=='year') ? $_mode : 1;
    $_params = '&amp;year='.$year.'&amp;month='.$month.'&amp;day='.$day.$sid;
    //$a = '        <a href="./calendar.php';
    //$i = '<input type="submit" ';
    //$x = "</a>\n";

    // tabs
    $tabs = array();
    $tabs[] = array('href' => $_SERVER['SCRIPT_NAME'].'?mode='.$_mode.'&amp;view=0'.$_params, 'active' => !$view, 'id' => 'tab1', 'target' => '_self', 'text' => __('Self'), 'position' => 'left');
    if (count(calendar_get_represented_users('proxy')) > 0) {
        $tabs[] = array('href' => $_SERVER['SCRIPT_NAME'].'?mode='.$_mode.'&amp;view=4'.$_params, 'active' => $view==4, 'id' => 'tab3', 'target' => '_self', 'text' => __('Substitution'), 'position' => 'left');
    }
    $tabs[] = array('href' => $_SERVER['SCRIPT_NAME'].'?mode='.$_mode2.'&amp;view=3'.$_params, 'active' => $view==3, 'id' => 'tab2', 'target' => '_self', 'text' => __('Selection'), 'position' => 'left');
    $tmp    = get_export_link_data('calendar', false);
    $tabs[] = array('href' => $tmp['href'], 'active' => $tmp['active'], 'id' => 'tab4', 'target' => '_self', 'text' => $tmp['text'], 'position' => 'right');

    return get_tabs_area($tabs);
}


/**
 * get the calendar view selection
 *
 * @param  string $oncontextmenu
 * @return string
 */
function calendar_view_selection($oncontextmenu='') {
    global $ID, $mode, $view, $year, $month, $day, $sid, $act_for, $act_as, $name_day;
    global $combisel, $axis, $dist;

    $_act = ($act_as) ? '&amp;act_as='.$act_as : (($act_for) ? '&amp;act_for='.$act_for : '');

    $_params = ($view == 3) ? '&amp;axis='.$axis.'&amp;dist='.$dist : '';
    $_params = '&amp;view='.$view.'&amp;year='.$year.'&amp;month='.((int) $month).
               '&amp;day='.((int) $day).$_params.$_act.$sid;

    $ret = '';
    if ($oncontextmenu) {
        $ret .= ' '.$oncontextmenu;
    }
    $ret .= ' >';

    $buttons = array();
    // new event
    $buttons[] = array('type' => 'link', 'href' => 'calendar.php?mode=forms'.$_params, 'text' => __('New'), 'active' => (!$ID && ($mode=='forms' || $mode=='data')));
    $buttons[] = array('type' => 'text', 'text' => __('View').':');
    // day
    $buttons[] = array('type' => 'link', 'href' => 'calendar.php?mode=1'.$_params, 'text' => __('Day'), 'active' => ($mode == 1 || $mode == 5));
    // working week
    if ($view != 3 || ($view == 3 && count($combisel) < 2)) {
        $buttons[] = array('type' => 'link', 'href' => 'calendar.php?mode=2'.$_params, 'text' => __('Working week'), 'active' => ($mode == 2 || $mode == 6));
    }
    // week
    $buttons[] = array('type' => 'link', 'href' => 'calendar.php?mode=3'.$_params, 'text' => __('Week'), 'active' => ($mode == 3 || $mode == 7));
    // month
    $buttons[] = array('type' => 'link', 'href' => 'calendar.php?mode=4'.$_params, 'text' => __('Month'), 'active' => ($mode == 4 || $mode == 8));
    // year
    if ($view != 3 || ($view == 3 && count($combisel) < 2)) {
        $buttons[] = array('type' => 'link', 'href' => 'calendar.php?mode=year'.$_params, 'text' => __('Year'), 'active' => ($mode=='year'));
    }
    // completely disabled in combi view
    if ($view != 3) {
        // event list
        $buttons[] = array('type' => 'link', 'href' => 'calendar.php?mode=view'.$_params, 'text' => __('List'), 'active' => ($mode=='view'));
    }
    // show the current weekday/name, date
    $_year  = date('Y', mktime(date('H')+PHPR_TIMEZONE, date('i'), date('s'), date('m'), date('d'), date('Y')));
    $_month = date('m', mktime(date('H')+PHPR_TIMEZONE, date('i'), date('s'), date('m'), date('d'), date('Y')));
    $_day   = date('d', mktime(date('H')+PHPR_TIMEZONE, date('i'), date('s'), date('m'), date('d'), date('Y')));
    $_weekday = date('w', mktime(0,0,0, $_month, $_day, $_year));
    $buttons[] = array('type' => 'text', 'text' => $name_day[$_weekday].', '.$_year.'-'.$_month.'-'.$_day, 'active' => false);
    return get_buttons_area($buttons, $oncontextmenu);
}


/**
 * get the calendar view status
 *
 * @return string
 */
function calendar_view_status() {
    $ret = '
<!-- begin calendar_view_status -->
<div class="div1">
    <span class="path">'.__('Status').': </span>'.message_stack_out_all().'
</div>
<!-- end calendar_view_status -->'."\n";
    
    return $ret;
}


/**
 * get the calendar act_for selection
 *
 * @return string
 */
function calendar_act_for_selection() {
    global $mode, $view, $act_for, $event_owner, $event_group, $year, $month, $day;

    $ret = '';
    $act_for_user = calendar_get_represented_userdata('proxy');
    foreach ($act_for_user as $row) {
        $ret .= '<option value="'.$row['ID'].'"';
        if ($row['ID'] == $act_for) {
            $ret .= ' selected="selected"';
            $event_owner = $row['ID'];
            $event_group = $row['gruppe'];
        }
        $title = $row['nachname'].', '.$row['vorname'].' ('.$row['grpname'].')';
        $ret .= ' title="'.$title.'">'.$title."</option>\n";
    }
    $ret = '
<!-- begin calendar_act_for_selection -->
<div class="div1">
    <form action="calendar.php" method="post" name="act_for_form" style="display:inline;">
        <input type="hidden" name="mode"  value="'.$mode.'" />
        <input type="hidden" name="view"  value="'.$view.'" />
        <input type="hidden" name="year"  value="'.$year.'" />
        <input type="hidden" name="month" value="'.$month.'" />
        <input type="hidden" name="day"   value="'.$day.'" />
        '.(SID ? '<input type="hidden" name="'.session_name().'" value="'.session_id().'" />' : '').'
        <span class="col5">'.__('Substitute for').':</span>
        <select name="act_for">
<option value="" title="--- '.__('Please select!').' ---">--- '.__('Please select!').' ---</option>
'.$ret.'
        </select>
        &nbsp;&nbsp;<input type="submit" class="button" value="&#187;" name="act_for_selection" />
    </form>
</div>
<!-- end calendar_act_for_selection -->'."\n";
    
    return $ret;
}


/**
 * get the calendar date selector form header
 *
 * @return string
 */
function calendar_date_selection_header() {
    global $date_selector_form_header, $mode, $view, $act_for;

    $date_selector_form_header = true;
    $ret = '
<!-- begin calendar_date_selection -->
<form action="calendar.php" method="post" name="date_selection" style="display:inline;">
    <input type="hidden" name="mode" value="'.$mode.'" />
    <input type="hidden" name="view" value="'.$view.'" />
    '.($act_for ? '<input type="hidden" name="act_for" value="'.$act_for.'" />' : '').'
    '.(session_id() ? '<input type="hidden" name="'.session_name().'" value="'.session_id().'" />' : '');
    
    return $ret;
}


/**
* get closing html for date selection
* @return string
*/
function calendar_date_selection_footer() {
    return '
    </form>
    <!-- end calendar_date_selection -->'."\n";
}


/**
 * get the calendar date selector form
 *
 * @return string
 */
function calendar_date_selection() {
    global $date_selector_form_header, $mode, $view, $year, $month, $day, $name_month;

    // day stuff
    $out_day = '';
    for ($a=1; $a<32; $a++) {
        $out_day .= '<option value="'.$a.'"'.($day == $a ? ' selected="selected"' : '').'>'.$a."</option>\n";
    }
    // month stuff
    $out_month = '';
    for ($a=1; $a<13; $a++) {
        $m = date('n', mktime(0,0,0, $a, 1, $year));
        $out_month .= '<option value="'.$a.'"'.($month == $m ? ' selected="selected"' : '').'>'.$name_month[$m]."</option>\n";
    }
    // year stuff / find current year
    $y = date('Y', mktime(date('H') + PHPR_TIMEZONE, date('i'), date('s'), date('m'), date('d'), date('Y')));
    // select box for the year
    $out_year = '';
    if ($year < $y-2) {
        $out_year .= '<option value="'.$year.'" selected="selected">'.$year."</option>\n";
    }
    for ($i=$y-2; $i<=$y+5; $i++) {
        $out_year .= '<option value="'.$i.'"'.($i == $year ? ' selected="selected"' : '').'>'.$i."</option>\n";
    }
    if ($year > $y+5) {
        $out_year .= '<option value="'.$year.'" selected="selected">'.$year."</option>\n";
    }

    #$ret = (!$date_selector_form_header ? calendar_date_selection_header() : '').'
    $ret = '<br />
    <fieldset>
        <legend>'.__('Select date').':</legend>
        <select name="day">
'.$out_day.'
        </select>
        <select name="month">
'.$out_month.'
        </select>
        <select name="year">
'.$out_year.'
        </select>
        <input type="submit" class="button" value="&#187;" name="action_select_date" />
        <span class="col5">'.__('or').__(':').'</span>
        <input type="submit" class="button" value="'.__('today').'" name="action_select_today" />
    </fieldset>
'."\n";
    
    return $ret;
}


/**
 * get the datepicker
 *
 * @return string
 */
function calendar_show_datepicker() {
    global $mode, $view, $year, $month, $day, $name_day2, $l_text23, $sid, $act_for, $act_as;
    global $event_owner, $event_group;

// FIXME: style definitions here should be declared as classes in the stylesheet file directly!!!

    $_act = ($act_as) ? '&amp;act_as='.$act_as : (($act_for) ? '&amp;act_for='.$act_for : '');

    // show all days of a month
    // name of week days
    $ret = '';
    for ($i=0; $i<7; $i++) {
        $ret .= '        <td width="21" class="calendar_pick1">'.$name_day2[$i]."&nbsp;</td>\n";
    }

    $ret = '
    <table cellpadding="1" cellspacing="0" border="0">
        <tr>
            <td width="15">&nbsp;</td>
            <td width="25" class="calendar_pick2">'.$l_text23.'&nbsp;</td>
'.$ret.'
            <td width="15">&nbsp;</td>
        </tr>
        <tr>'."\n";

    // first week, still in last month?
    if (date('w', mktime(0,0,0, $month, 1, $year)) == 0) {
        $da = -6;
    }
    else if (date('w', mktime(0,0,0, $month, 1, $year)) <> 1) {
        $da = - date('w', mktime(0,0,0, $month, 1, $year)) +1;
    }
    else {
        $da = 1;
    }

    // set week number for the first time
    $week_nr = calendar_get_week_nr($da+1);
    $ret .= '
            <td>&nbsp;</td>
            <td class="calendar_pick2">
                <a href="./calendar.php?mode=2&amp;view='.$view.'&amp;year='.$year.'&amp;month='.$month.'&amp;day='.$da.'" title="'.$week_nr.'">'.$week_nr.'</a>&nbsp;&nbsp;
            </td>'."\n";

    // show days of the previous month
    if (date('w', mktime(0,0,0, $month, 1, $year)) == 0) {
        $start = 7;
    }
    else {
        $start = date('w', mktime(0,0,0, $month, 1, $year));
    }

    for ($a=($start-2); $a>=0; $a--) {
        $d = date('t', mktime(0,0,0, $month, 0, $year)) - $a;
        $ret .= '        <td class="calendar_pick3">'.$d."&nbsp;</td>\n";
    }


    // show days of the current month
    $now_date = date('Y-m-d', mktime(date('H') + PHPR_TIMEZONE, date('i'), date('s'), date('m'), date('d'), date('Y')));

    for ($d=1; $d<=date('t', mktime(0,0,0, ($month+1), 0, $year)); $d++) {
        // XXX next line inserted to check for holidays / special days
        //$holidays = calendar_get_holidays(mktime(0,0,0, $month, $d, $year));
        // XXX - end

        // fetch events
        $datum2 = date('Y-m-d', mktime(0,0,0, $month, $d, $year));
        $query = "SELECT ID
                    FROM ".DB_PREFIX."termine
                   WHERE datum LIKE '$datum2'
                     AND an = ".(int)$event_owner;
        $result3 = db_query($query) or db_die();
        $row3 = db_fetch_row($result3);

        // XXX This is to display the days in datepicker. We take care of:
        // - is the day to display "today"? Paint with background color and red and bold.
        // - same as above, but holiday, too? Same painting, with mouse_over for holiday text
        // - holiday? Paint in holiday color + mouse_over for holiday text
        // - holiday + event? Paint in holiday color and bold + mouse_over for holiday text
        // - only event? Paint in black and bold
        $da_format = '';
        if ($now_date == $datum2) { // if the day to show is today
            if (count($holidays) > 0) { // and there is a legal holiday (in some regions) to display
                foreach ($holidays as $day_to_show) { // foreach is necessary because there could be 2+ texts for one day
                    if ($day_to_show['type'] != '0') {
                        $m_over_txt = $m_over_txt.$m_over_txt_delim.$day_to_show['name'];
                        $m_over_txt_delim = ' &#124; ';
                    }
                }
                $da_format  = 'class="calendar_pick4" alt="'.$m_over_txt.'" title="'.$m_over_txt.'"';
                $m_over_txt = '';
                $m_over_txt_delim = '';
            }
            else {
                $da_format  = 'class="calendar_pick4"';
            }
        }
// FIXME: this "var control & definiton by css class or id" stuff should be rewritten !!!
        else if (count($holidays) > 0) { // it's not today, but it is a holiday (in some regions)
            if ($row3[0] > 0 and !$view) { // and we have dates
                foreach ($holidays as $day_to_show) { // maybe there are more holiday-texts to display
                    if ( $day_to_show['type'] != '0' ) {
                        $m_over_txt = $m_over_txt.$m_over_txt_delim.$day_to_show['name'];
                        $m_over_txt_delim = ' &#124; ';
                    }
                    if ( $day_to_show['type'] == '1' ) {
                        $day_color = $day_to_show['class'];
                    }
                    if ( $day_to_show['type'] == '2' and
                         $day_color != 'calendar_holiday_anywhere' ) {
                        $day_color = $day_to_show['class'];
                    }
                    if ( $day_to_show['type'] == '0' and
                         $day_color != 'calendar_holiday_anywhere' and
                         $day_color != 'calendar_holiday_somewhere' ) {
                        $day_color = $day_to_show['class'];
                    }
                }
                $da_format  = 'class="'.$day_color.'" style="font-weight:bold;" alt="'.$m_over_txt.'" title="'.$m_over_txt.'"';
                $day_color  = '';
                $m_over_txt = '';
                $m_over_txt_delim = '';
            }
            else { // we have holidays (in some regions) to display but no dates
                foreach ($holidays as $day_to_show) { // maybe there are more holiday-texts to display
                    if ( $day_to_show['type'] != '0' ) {
                        $m_over_txt = $m_over_txt.$m_over_txt_delim.$day_to_show['name'];
                        $m_over_txt_delim = ' &#124; ';
                    }
                    if ( $day_to_show['type'] == '1' ) {
                        $day_color = $day_to_show['class'];
                    }
                    if ( $day_to_show['type'] == '2' and
                         $day_color != 'calendar_holiday_anywhere' ) {
                        $day_color = $day_to_show['class'];
                    }
                }
                $da_format  = 'class="'.$day_color.'" alt="'.$m_over_txt.'" title="'.$m_over_txt.'"';
                $day_color  = '';
                $m_over_txt = '';
                $m_over_txt_delim = '';
            }
        }
        else { // no holidays but dates
            if ($row3[0]>0 and !$view) $da_format = 'style="color:black;font-weight:bold;"';
            else                       $da_format = '';
        }
        $da = '<div '.$da_format.'>'.$d.'&nbsp;</div>';
        // XXX End of holiday extension

        // day link
        $ret .= '
            <td class="calendar_pick1">
                <a href="./calendar.php?mode=1&amp;view='.$view.'&amp;year='.$year.'&amp;month='.$month.'&amp;day='.$d.$_act.$sid.'" title="'.$d.'">'.$da.'</a>
            </td>'."\n";

        if (date('w', mktime(0,0,0, $month, $d, $year))==0 and date('t', mktime(0,0,0, ($month+1), 0, $year))>$d) {
            $ret .= '
            <td>&nbsp;</td>
        </tr>
        <tr>'."\n";
            // set values to show week number
            $da = $d + 1;
            $week_nr = calendar_get_week_nr($da);
            $ret .= '
            <td>&nbsp;</td>
            <td class="calendar_pick2">
                <a href="./calendar.php?mode=2&amp;view='.$view.'&amp;year='.$year.'&amp;month='.$month.'&amp;day='.$da.$_act.$sid.'" title="'.$week_nr.'">'.$week_nr.'</a>&nbsp;&nbsp;
            </td>'."\n";
        }
    }

    // show days of the next month
    if (date('w', mktime(0,0,0, $month+1, 1, $year)) <> 1) {
        $d = 1;
        while (date('w', mktime(0,0,0, ($month+1), $d, $year)) <> 1) {
            $ret .= '        <td class="calendar_pick3">'.$d.'&nbsp;</td>'."\n";
            $d++;
        }
        $ret .= "        <td>&nbsp;</td>\n";
    }
    $ret .= "    </tr>\n</table>\n";

    return $ret;
}


/**
 * get the calendar select event box
 *
 * @return string
 */
function calendar_combi_selection() {
    global $date_selector_form_header, $axis, $dist, $l_text31a, $l_text31b, $combisel;
    global $user_ID, $user_kurz, $sql_user_group;
    
    // profiles
    $profiles = '';
    $query = "SELECT ID, bezeichnung, von
                FROM ".DB_PREFIX."profile
               WHERE (acc LIKE 'system'
                      OR ((von = ".(int)$user_ID." OR acc LIKE 'group' OR acc LIKE '%\"$user_kurz\"%')
                          AND $sql_user_group))
            ORDER BY bezeichnung";
    $result = db_query($query) or db_die();
    while ($row = db_fetch_row($result)) {
        $von = str_replace(',', ', ', slookup('users', 'nachname,vorname', 'ID', $row[2],'1'));
        $title = html_out($row[1])." ($von)";
        $profiles .= '<option value="'.$row[0].'" title="'.$title.'">'.$title."</option>\n";
    }
    
    // time axis
    //$ret = (!$date_selector_form_header ? calendar_date_selection_header() : '').'
    $ret = '
    <!-- begin calendar_combi_selection -->
    <div id="bars">
    <div class="filter_execute_bar">
        &nbsp;&nbsp;&nbsp;
        <select name="user_or_profile_selection" style="width:150px">
            <option value="action_combi_to_selector" title="'.__('User selection').'">'.__('User selection').'</option>
            <option value="" disabled="disabled" style="color:black">'.str_repeat('- ', strlen(__('User selection'))).'</option>
            <option value="" disabled="disabled" style="color:black">'.__('or profile').':</option>
'.$profiles.'
        </select>
        <input type="submit" class="button" value="&#187;" name="action_select_user_or_profile" /></div>'."\n";
    
    if (count($combisel) > 1) {
        $ret .= '
        <span class="col5">'.__('time-axis:').'</span>
        <select name="axis">
            <option value="v"'.($axis != 'h' && $axis != 'x' ? ' selected="selected"' : '').' title="'.__('vertical').'">'.__('vertical').'</option>
            <option value="h"'.($axis == 'h' ? ' selected="selected"' : '').' title="'.__('horizontal').'">'.__('horizontal').'</option>
            <option value="x"'.($axis == 'x' ? ' selected="selected"' : '').' title="'.__('Horz. Narrow').'">'.__('Horz. Narrow').'</option>
        </select>
        <span class="col5">'.__('-interval:').'</span>
        <select name="dist">'."\n";
        if (!isset($dist)) $dist = 0;
        for ($ii=0; $ii<count($l_text31a); $ii++) {
            $ret .= '<option value="'.$l_text31b[$ii].'"'.
                    ($dist == $l_text31b[$ii] ? ' selected="selected"' : '').
                    ' title="'.$l_text31a[$ii].'">'.$l_text31a[$ii]."</option>\n";
        }
        $ret .= '
        </select>
        &nbsp;&nbsp;&nbsp;<input type="submit" class="button" value="&#187;" name="action_select_date" />'."\n";
    }
    $ret .= '
    </div>
    <!-- end calendar_combi_selection -->'."\n";

    return $ret;
}


/**
 * check if we are in the selector view
 *
 * @return boolean
 */
function calendar_is_selector_view() {
    //if ( isset($_REQUEST['action_combi_to_selector']) ||
    if ( (isset($_REQUEST['action_select_user_or_profile']) &&
          isset($_REQUEST['user_or_profile_selection']) &&
          $_REQUEST['user_or_profile_selection'] == 'action_combi_to_selector') ||
         isset($_REQUEST['action_selector_to_selector']) ||
         isset($_REQUEST['action_form_to_selector']) ||
         isset($_REQUEST['action_form_to_selector_x']) || 
         isset($_REQUEST['action_form_to_contact_selector']) ||
         isset($_REQUEST['action_form_to_contact_selector_x']) || 
         isset($_REQUEST['action_form_to_project_selector']) ||
         isset($_REQUEST['action_form_to_project_selector_x']) ||
         isset($_REQUEST['filterform']) ||
         isset($_REQUEST['filterdel'])) {
        return true;
    }
    return false;
}


/**
 * create the array needed as input for the breadcrumb-function
 * @see breadcrumb()
 * @uses __()
 * @param mixed $mode global $mode
 * @param mixed $view global $view
 */
function breadcrumb_data($mode, $view) {
    $view = qss($view);
    $tuples = array();
    $url = "calendar.php?view=$view";

    if (     0 == $view ) { $tuples[] = array('title'=>__('Self'),         'url'=>$url); }
    elseif ( 3 == $view ) { $tuples[] = array('title'=>__('Selection'),    'url'=>$url); }
    elseif ( 4 == $view ) { $tuples[] = array('title'=>__('Substitution'), 'url'=>$url); }
    
    if (     1 == $mode ) { $tuples[] = array('title'=>__('Day')); }
    elseif ( 2 == $mode ) { $tuples[] = array('title'=>__('Working week')); }
    elseif ( 3 == $mode ) { $tuples[] = array('title'=>__('Week')); }
    elseif ( 4 == $mode ) { $tuples[] = array('title'=>__('Month')); }
    elseif ( 'forms' == $mode ) { $tuples[] = array('title'=>__('New')); }
    elseif ( 'year'  == $mode ) { $tuples[] = array('title'=>__('Year')); }
    elseif ( 'view'  == $mode ) { $tuples[] = array('title'=>__('List')); }
    
    return $tuples;
}

?>
