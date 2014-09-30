<?php
/**
* calendar year view as a table
*
* @package    calendar
* @module     view
* @author     Albrecht Guenther, $Author: alexander $
* @licence    GPL, see www.gnu.org/copyleft/gpl.html
* @copyright  2000-2006 Mayflower GmbH www.mayflower.de
* @version    $Id: calendar_view_year.php,v 1.46.2.2 2007/01/23 12:28:54 alexander Exp $
*/
if (!defined('lib_included')) die('Please use index.php!');

/**
 * get the main year view.
 *
 * @return string  the main year view
 */
function calendar_view_year_get_view() {
    global $view, $day, $year, $name_month, $sid;

    $user_params = calendar_get_user_params();

    // get the (probably filtered) events of the year
    $user_params['_year'] = $year;
    $events = calendar_view_year_get_events($user_params);

    $year_data = calendar_view_year_prepare_data($events, $user_params);

    $ret = '
    <table cellspacing="1" cellpadding="0" class="calendar_table" width="100%" border="0">
    <caption>'.calendar_view_prevnext_header('y', $user_params).'</caption>
';

    for ($ii=1; $ii<7; $ii++) {

        $class1 = calendar_view_year_get_class($year, substr('0'.$ii, -2));
        $class2 = calendar_view_year_get_class($year, substr('0'.($ii+6), -2));

        $ret .= '
        <tr>
            <td width="50%" class="calendar_year calendar_year_header'.$class1.'">
                <a href="./calendar.php?mode=4&amp;view='.$view.'&amp;year='.$year.'&amp;month='.$ii.'&amp;day='.$day.$user_params['act_param'].$sid.'" title="'.$name_month[$ii].'. '.$year.'">'.$name_month[$ii].'.</a>
            </td>
            <td rowspan="2">&nbsp;</td>
            <td width="50%" class="calendar_year calendar_year_header'.$class2.'">
                <a href="./calendar.php?mode=4&amp;view='.$view.'&amp;year='.$year.'&amp;month='.($ii+6).'&amp;day='.$day.$user_params['act_param'].$sid.'" title="'.$name_month[$ii+6].'. '.$year.'">'.$name_month[$ii+6].'.</a>
            </td>
        </tr>
        <tr>
            <td class="calendar_year calendar_year_month'.$class1.'">
                '.$year_data[$ii].'
            </td>
            <td class="calendar_year calendar_year_month'.$class2.'">
                '.$year_data[$ii+6].'
            </td>
        </tr>'."\n";
        if ($ii < 6) {
            $ret .= '<tr><td colspan="3" style="height:5px;border:1px solid transparent;"> </td></tr>'."\n";
        }
    }

    $ret .= '
    </table>

    <br /><br />
';
    return $ret;
}

/**
 * get the (probably filtered) events of the year.
 *
 * @param  array $user_params  a reference to the user parameters and other data
 * @return array  the collected events
 */
function calendar_view_year_get_events(&$user_params) {
    $ret = array();
    $viewer = array(0);
    $reader = array(0);
    $proxy  = array(0);
    $viewer = array_merge($viewer,calendar_get_represented_users('viewer'));
    $reader = array_merge($reader,calendar_get_represented_users('reader'));
    $proxy  = array_merge($proxy, calendar_get_represented_users('proxy'));
    $query = "SELECT ID, von, an, event, anfang, ende, visi, partstat, datum, status
                FROM ".DB_PREFIX."termine
               WHERE datum LIKE '".$user_params['_year']."-%'
                 AND (    (an IN (".(int)$user_params['user_id'].") AND visi IN (0,1,2))
                        OR (an IN (".implode($viewer,",").") AND visi IN (1))
                        OR (an IN (".implode($reader,",").") AND visi IN (0,2))
                        OR (an IN (".implode($proxy,",").") AND visi IN (0,1,2))  
                      )
            ORDER BY datum, anfang, event";
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
        $ret[substr($row[8], 5, 2)][] = $event;
    }
    return $ret;
}

/**
 * prepare the year data.
 *
 * @param  array $events  a reference to the collected events
 * @param  array $user_params  a reference to the user parameters
 * @return array  the prepared data
 */
function calendar_view_year_prepare_data(&$events, &$user_params) {
    global $view, $year, $date_format_object, $sid;

    $ret = array();
    for ($ii=1; $ii<13; $ii++) {

        $hits    = 0;
        $output  = '';
        $ret[$ii] = '';
        $z_month = substr('0'.$ii, -2);

        if (empty($events[$z_month])) {
            $events[$z_month] = array();
        }
        foreach ($events[$z_month] as $row) {
            $m_day = substr($row['datum'], 8, 2);
            $m_day = '<a href="./calendar.php?mode=1&amp;view='.$view.'&amp;year='.$year.'&amp;month='.$ii.
                     '&amp;day='.$m_day.$user_params['act_param'].$sid.
                     '" title="'.$date_format_object->convert_db2user($row['datum']).'">'.$m_day.'.</a>';
            $event = calendar_get_event_text($row);
            $alt_title = calendar_get_alt_title_tag($row);
            if ( calendar_can_read_events($user_params['user_id'], $row['visi']) ||
                 calendar_can_edit_events($user_params['user_id'], $row['visi']) ) {
                $event = '<a href="./calendar.php?ID='.$row['ID'].'&amp;mode=forms&amp;view='.$view.
                         $user_params['act_param'].$sid.'" title="'.$alt_title.'">'.$event.'</a>';
            }
            else {
                $event = '<span title="'.$alt_title.'">'.$event.'</span>';
            }

            $output .= '&nbsp;'.$m_day.'&nbsp;-&nbsp;'.$event."<br />\n";
            $hits++;
        }

        // fill in the table with blank lines
        for ($jj=$hits; $jj<10; $jj++) {
            $output .= '<br />';
        }
        $ret[$ii] .= $output."\n";
    }

    return $ret;
}

/**
 * get a css class to beautify the output.
 *
 * @param  string $year  a year
 * @param  string $month  a month
 * @return string  a css class
 */
function calendar_view_year_get_class($year, $month) {
    $ret = '';
    if (substr(calendar_get_ymd(true), 0, 7) == $year.'-'.$month) {
        $ret = ' calendar_year_current_month';
    }
    else if (substr(calendar_get_ymd(), 5, 2) == $month) {
        $ret = ' calendar_year_selected_month';
    }
    return $ret;
}

?>
