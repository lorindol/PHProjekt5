<?php
/**
* calendar month view
*
* @package    calendar
* @module     view
* @author     Albrecht Guenther, $Author: gustavo $
* @licence    GPL, see www.gnu.org/copyleft/gpl.html
* @copyright  2000-2006 Mayflower GmbH www.mayflower.de
* @version    $Id: calendar_view_month.php,v 1.56.2.1 2007/01/10 14:18:29 gustavo Exp $
*/
if (!defined('lib_included')) die('Please use index.php!');
require_once(PATH_PRE.'/lib/specialdays.php');

/**
 * get the main month view.
 *
 * @return string  the main month view
 */
function calendar_view_month_get_view() {
    global $view, $year, $month, $day, $sid, $name_day2, $date_format_object, $settings;

    $user_params    = calendar_get_user_params();
    $first_day_week = (isset($settings['first_day_week'])) ? $settings['first_day_week'] : 1;
    $ret = '
    <table class="calendar_month">
        <caption>'.calendar_view_prevnext_header('m', $user_params).'</caption>
        <thead>
            <tr>
                <td>'.substr(__('Week'),0,1).'</td>
    ';
    // the top line with the short name of the days
    for ($ii=$first_day_week-1; $ii<$first_day_week+6; $ii++) {
        $_name_day2 = ($ii>6) ? $ii-7 : (($ii<0) ? 6 : $ii);
        $ret .= '<td>'.$name_day2[$_name_day2]."</td>\n";
    }
    $ret .= '
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>'.calendar_view_month_get_week_nr($user_params, $first_day_week, 1).'</td>
            '.
            // days of the previous month
            calendar_view_month_prev_month($user_params, $first_day_week);




    // get the (probably filtered) events of the month'
    $res = calendar_view_month_get_events($user_params);
    $sp       = new SpecialDays((array)$settings['cal_hol_file']);
    $holidays = $sp->get_masked_days_for_month($month, $year, PHPR_SD_ALL);
    for ($d=1; $d<=date('t', mktime(0,0,0, $month+1, 0, $year)); $d++) {
        $ts = mktime(0,0,0, $month, $d, $year);

        $hol_string = '';
        if ($sp->is_masked_day($ts, PHPR_SD_ALL)) {
            foreach ($holidays[$ts] as $holiday) {
                switch($holiday['type']) {
                    case PHPR_SD_HOLIDAYS:
                        $hol_string .= '<span class="calendar_holiday_anywhere">'.$holiday['name']."</span><br />\n";
                        break;
                    case PHPR_SD_SPECIALDAYS:
                        $hol_string .= '<span class="calendar_holiday_nonfree">'.$holiday['name']."</span><br />\n";
                        break;
                }
            }
        }

        $d     = substr('0'.$d, -2);
        $month = substr('0'.$month, -2);
        $datum = "$year-$month-$d";
        $found_event = false;

        // beautify output ..
        if ($datum == calendar_get_ymd(true)) {
            $class = 'calendar_day_today';
        }
        else if (date('w', mktime(0,0,0,$month,$d,$year)) == 0 || date('w', mktime(0,0,0,$month,$d,$year)) == 6) {
            $class = 'calendar_day_weekend';
        }
        else {
            $class = 'calendar_day_current';
        }
        if ($datum != calendar_get_ymd(true) && $d == $day) {
            $class = 'calendar_day_sameday';
        }

        $add = '';
        if (calendar_can_edit_events($user_params['user_id'], 'public')) {
            $add = '&nbsp;<a href="./calendar.php?mode=forms&amp;view='.$view.
                '&amp;year='.$year.'&amp;month='.((int) $month).'&amp;day='.((int) $d).
                $user_params['act_param'].$sid.'" title="'.__('Create new event').'">'.PHPR_CALENDAR_ADD_SIGN.'</a>';
        }
        $dd = (int) $d;
        $ret .= '
            <td class="'.$class.'">
                <a href="./calendar.php?mode=1&amp;view='.$view.'&amp;year='.$year.
                '&amp;month='.((int) $month).'&amp;day='.$dd.$user_params['act_param'].$sid.
                '" title="'.$date_format_object->convert_db2user($datum).'">'.
            ($dd<10?"&nbsp;$dd&nbsp;":$dd)."</a>".$add."<br />\n";
        $ret .= $hol_string;

        foreach ($res as $row) {
            if ($row['datum'] != $datum) {
                continue;
            }
            $found_event = true;
            $text        = calendar_get_event_text($row);
            $alt_title   = calendar_get_alt_title_tag($row);
            // add link to edit event
            if ( calendar_can_read_events($user_params['user_id'], $row['visi']) ||
                calendar_can_edit_events($user_params['user_id'], $row['visi']) ) {
                $text = '<a href="./calendar.php?ID='.$row['ID'].'&amp;mode=forms&amp;view='.$view.
                        $user_params['act_param'].$sid.'" title="'.$alt_title.'">'.$text.'</a>';
            }
            else {
                $text = '<span class="calendar_month_nolink" title="'.$alt_title.'">'.$text.'</span>';
            }
            $ret .= $text.'<br />';
        }

        if (!$found_event) $ret .= '&nbsp;';

        $ret .= "</td>\n";
        if ( date('w', mktime(0,0,0, $month, $d+1, $year)) == $first_day_week &&
             date('t', mktime(0,0,0, $month+1, 0, $year)) > $d ) {
            $ret .= '
        </tr>
        <tr>
            <td>
                '.calendar_view_month_get_week_nr($user_params, $first_day_week, $d+1)."
            </td>\n";
        }
    }
    // days of the next month
    $ret .= calendar_view_month_next_month($user_params, $first_day_week);
    $ret .= '
            </tr>
        </tbody>
    </table>
    ';
    return $ret;
}

/**
 * get the days of the previous month.
 *
 * @return array  the collected days
 */
function calendar_view_month_prev_month(&$user_params, $first_day_week) {
    global $month, $year, $view, $sid, $date_format_object;

    $ret = '';
    if (date('w', mktime(0,0,0, $month, 1, $year)) == $first_day_week) {
        // the first day of the current month is equal the first day of the week => return
        return $ret;   
    }
    
    $weekday = array('Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday');
    $start = date('d', strtotime('-1 week', strtotime($weekday[$first_day_week], mktime(0,0,0, $month, 1, $year))));
    $end   = date('t', mktime(0,0,0, $month-1, 1, $year));
    
    $prev_date = calendar_get_prev_date('m');
    for ($d=$start; $d<=$end; $d++) {
        $datum = $prev_date['y'].'-'.substr('0'.$prev_date['m'], -2).'-'.substr('0'.$d, -2);
        $dd = '<a href="./calendar.php?mode=1&amp;view='.$view.'&amp;year='.$prev_date['y'].
              '&amp;month='.$prev_date['m'].'&amp;day='.$d.$user_params['act_param'].$sid.
              '" title="'.$date_format_object->convert_db2user($datum).'">'.$d.'</a>';
        $add = '';
        if (calendar_can_edit_events($user_params['user_id'], 'public')) {
            $add = '&nbsp;<a href="./calendar.php?mode=forms&amp;view='.$view.
                   '&amp;year='.$prev_date['y'].'&amp;month='.$prev_date['m'].'&amp;day='.$d.
                   $user_params['act_param'].$sid.'" title="'.__('Create new event').'">'.PHPR_CALENDAR_ADD_SIGN.'</a>';
        }
        $ret .= '        <td>'.$dd.$add."<br /></td>\n";
    }
    return $ret;
}

/**
 * get the days of the next month.
 *
 * @return array  the collected days
 */
function calendar_view_month_next_month(&$user_params, $first_day_week) {
    global $month, $year, $view, $sid, $date_format_object;

    $ret = '';
    $mm  = $month;
    $yy  = $year;
    if ($mm == 12) {
        $mm = 0;
        $yy++;
    }
// FIXME: this "if" should be obsolete now
    //if (date('w', mktime(0,0,0, $mm+1, 1, $yy)) != $first_day_week) {
        $d = 1;
        $next_date = calendar_get_next_date('m');
        while (date('w', mktime(0,0,0, ($mm+1), $d, $yy)) != $first_day_week) {
            $datum = $next_date['y'].'-'.substr('0'.$next_date['m'], -2).'-'.substr('0'.$d, -2);
            $dd = '<a href="./calendar.php?mode=1&amp;view='.$view.'&amp;year='.$next_date['y'].
                  '&amp;month='.$next_date['m'].'&amp;day='.$d.$user_params['act_param'].$sid.
                  '" title="'.$date_format_object->convert_db2user($datum).'">'.
                  ($d<10?"&nbsp;$d&nbsp;":$d).'</a>';
            $add = '';
            if (calendar_can_edit_events($user_params['user_id'], 'public')) {
                $add = '&nbsp;<a href="./calendar.php?mode=forms&amp;view='.$view.
                       '&amp;year='.$next_date['y'].'&amp;month='.$next_date['m'].'&amp;day='.$d.
                       $user_params['act_param'].$sid.'" title="'.__('Create new event').'">'.PHPR_CALENDAR_ADD_SIGN.'</a>';
            }
            $m2 = $mm + 1;
            $ret .= '        <td class="calendar_month calendar_day_prevnext">'.$dd.$add.'<br /></td>'."\n";
            $d++;
        }
    //}
    return $ret;
}

/**
 * get the (probably filtered) events of the month.
 *
 * @param  array $user_params  a reference to the user parameters
 * @return array  the collected events
 */
function calendar_view_month_get_events(&$user_params) {
    global $month, $year;

    $ret   = array();
    $viewer = array(0);
    $reader = array(0);
    $proxy  = array(0);
    $viewer = array_merge($viewer,calendar_get_represented_users('viewer'));
    $reader = array_merge($reader,calendar_get_represented_users('reader'));
    $proxy  = array_merge($proxy, calendar_get_represented_users('proxy'));
    $datum = $year.'-'.substr('0'.$month, -2);

    $query = "SELECT ID, von, an, event, anfang, ende, visi, partstat, datum, status
                FROM ".DB_PREFIX."termine
               WHERE datum LIKE '$datum%'
                 AND (    (an IN (".(int)$user_params['user_id'].") AND visi IN (0,1,2))
                        OR (an IN (".implode($viewer,",").") AND visi IN (1))
                        OR (an IN (".implode($reader,",").") AND visi IN (0,2))
                        OR (an IN (".implode($proxy,",").") AND visi IN (0,1,2))  
                      )
            ORDER BY anfang, event";
    $res = db_query($query) or db_die();
    while ($row = db_fetch_row($res)) {
        $event = array( 'ID'       => $row[0]
                       ,'von'      => $row[1]
                       ,'an'       => $row[2]
                       ,'event'    => stripslashes($row[3])
                       ,'anfang'   => $row[4]
                       ,'ende'     => $row[5]
                       ,'visi'     => $row[6]
                       ,'partstat' => $row[7]
                       ,'datum'    => $row[8]
                       ,'status'   => $row[9]
                      );
        $event['event'] = calendar_process_event_text( $user_params['user_id'],
                                                       $event['visi'],
                                                       $event['event'] );
        $ret[] = $event;
    }
    return $ret;
}


function calendar_view_month_get_week_nr(&$user_params, $first_day_week, $day) {
    global $view, $month, $year, $sid;
    
    $ret = '';
    $timestamp = array();
    if ($day > 1 || date('w', mktime(0,0,0, $month, $day, $year)) == $first_day_week) {
        $timestamp[0] = date('U', mktime(0,0,0, $month, $day, $year));
    } else {
        $weekday = array('Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday');
        $day     = date('d', strtotime('-1 week', strtotime($weekday[$first_day_week], mktime(0,0,0, $month, 1, $year))));
        $timestamp[0] = date('U', mktime(0,0,0, $month-1, $day, $year));
    }
    $timestamp[1] = $timestamp[0] + 86400 * 6;

    if (date('W', $timestamp[0]) != date('W', $timestamp[1])) {
        $week  = date('W', $timestamp[0]).'<br />'.date('W', $timestamp[1]);
        $title = str_replace('<br />', './', $week);
    }
    else {
        $week  = date('W', $timestamp[0]);
        $title = $week;
    }
    $title .= '. '.__('calendar week');
    
    $ret = '<a href="./calendar.php?mode=3&amp;view='.$view.
           '&amp;year='.date('Y', $timestamp[0]).'&amp;month='.date('m', $timestamp[0]).
           '&amp;day='.$day.$user_params['act_param'].$sid.
           '" title="'.$title.'">'.$week.'</a>';
    
    return $ret;
}

?>
