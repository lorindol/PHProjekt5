<?php
/**
* calendar day view
*
* @package    calendar
* @module     view
* @author     Albrecht Guenther, $Author: alexander $
* @licence    GPL, see www.gnu.org/copyleft/gpl.html
* @copyright  2000-2006 Mayflower GmbH www.mayflower.de
* @version    $Id: calendar_view_day.php,v 1.67.2.2 2007/02/07 15:04:50 alexander Exp $
*/
if (!defined('lib_included')) die('Please use index.php!');
require_once(PATH_PRE.'/lib/specialdays.php');

/**
* Basic class to display daily events (and much more).
*
* @package PHProjekt
* @author  Giampaolo Pantò, Munich-Germany <panto@mayflower.de>
*/
class Calendar_View_Day
{

    /**
    * vars from outside (globals)
    *
    */
    var $_view;
    var $_mode;
    var $_settings;
    var $_day;
    var $_month;
    var $_year;
    var $_time_step;
    var $_cal_class;
    var $_sid;
    var $_date_format_object;

    /**
    * vars used within this class
    *
    */
    var $_user_id;
    var $_act_param;
    var $_day_start;
    var $_day_end;
    var $_show_add;
    var $_show_remark;
    var $_day_columns;
    var $_all_columns;
    var $_the_matrix;
    var $_day_events;
    var $_all_events;
    var $_special_events;
    var $_top_line;
    var $_top_line_add_on;
    var $_date_timestamp;
    
    /**
    * Constructor
    *
    */
    function Calendar_View_Day()
    {
        global $view, $mode, $settings, $day, $month, $year;
        global $time_step, $cal_class, $sid, $date_format_object;
    
        $this->_view               =& $view;
        $this->_mode               =& $mode;
        $this->_settings           =& $settings;
        $this->_day                =& $day;
        $this->_month              =& $month;
        $this->_year               =& $year;
        $this->_time_step          =& $time_step;
        $this->_cal_class          =& $cal_class;
        $this->_sid                =& $sid;
        $this->_date_format_object =& $date_format_object;
        
        $this->_the_matrix         = array();
        $this->_all_events         = array();
        $this->_special_events     = array( 'all_day' => array(), 'outside' => array() );
        $this->_top_line           = '';
        $this->_top_line_add_on    = '';
    }

    /**
     * get the main day view.
     *
     * @return string  the main day view
     */
    function get_view()
    {
        $user_params        = calendar_get_user_params();
        $this->_user_id     =& $user_params['user_id'];
        $this->_act_param   =& $user_params['act_param'];
        $this->_show_add    = calendar_can_edit_events($this->_user_id, 'public');
        $this->_show_remark = true;
        $this->_day_start   = (!empty($this->_settings['tagesanfang'])) ? $this->_settings['tagesanfang'] : PHPR_DAY_START;
        $this->_day_end     = (!empty($this->_settings['tagesende']))   ? $this->_settings['tagesende']   : PHPR_DAY_END;

        // step thru the day(s); useless in day view, but ...
        for ($ii=0; $ii<=0; $ii++) {
            $this->_date_timestamp = mktime(0,0,0, $this->_month, $this->_day, $this->_year);
            $this->_get_events();
            $this->_prepare_special_events();
            $this->_build_the_matrix();
            $this->_build_top_line();
            // add the events/columns together
            $this->_all_events  += $this->_day_events;
            $this->_all_columns += $this->_day_columns;
        }

        $ret = '
    <table cellspacing="1" cellpadding="0" class="calendar_table" width="100%" border="0">
    <caption>
    '.calendar_view_prevnext_header('d', $user_params).'
    </caption>
        '.$this->_get_top_line().'
        '.$this->_get_main_view().'
    </table>
    '.$this->_get_bottom_line().'
    <br /><br />
';
        return $ret;
    }
    
    /**
     * get the (probably filtered) events of the day.
     *
     */
    function _get_events()
    {
        $ret = array();
        $viewer = array(0);
        $reader = array(0);
        $proxy  = array(0);
        $viewer = array_merge($viewer,calendar_get_represented_users('viewer'));
        $reader = array_merge($reader,calendar_get_represented_users('reader'));
        $proxy  = array_merge($proxy, calendar_get_represented_users('proxy'));
        $query = "SELECT ID, von, an, event, anfang, ende, visi, partstat, status, remark, datum
                    FROM ".DB_PREFIX."termine
                   WHERE datum = '".$this->_date_format_object->get_date_from_timestamp($this->_date_timestamp)."'
                   AND (    (an IN (".(int)$this->_user_id.") AND visi IN (0,1,2))
                         OR (an IN (".implode($viewer,",").") AND visi IN (1))
                         OR (an IN (".implode($reader,",").") AND visi IN (0,2))
                         OR (an IN (".implode($proxy,",").") AND visi IN (0,1,2))  
                       )
                ORDER BY anfang, ende DESC, event";
        
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
                           ,'status'   => $row[8]
                           ,'remark'   => stripslashes($row[9])
                           ,'datum'    => $row[10]
                          );
            $event['event']  = calendar_process_event_text( $this->_user_id,
                                                            $event['visi'],
                                                            $event['event'] );
            $event['remark'] = calendar_process_remark_text( $this->_user_id,
                                                             $event['visi'],
                                                             $event['remark'] );
            $ret[$event['ID']] = $event;
        }
        
        $this->_day_events = $ret;
    }
    
    
    /**
     * build the top/first line of the day with (probably) holidays and special days.
     *
     */
    function _build_top_line()
    {
        $ret = '';
        if (!empty($this->_top_line_add_on)) {
            $ret = $this->_top_line_add_on."<br />\n";
        }
        // show holidays
        $sp = new SpecialDays((array) $this->_settings['cal_hol_file']);
        $holidays = $sp->get_masked_days_for_day( date('d', $this->_date_timestamp), 
                                                  date('m', $this->_date_timestamp), 
                                                  date('Y', $this->_date_timestamp) );
        if (count($holidays) > 0) {
            foreach ($holidays as $holiday) {
                switch ($holiday['type']) {
                    case PHPR_SD_HOLIDAYS:
                        $class = 'calendar_holiday_anywhere';
                        break;
                    case PHPR_SD_SPECIALDAYS:
                        $class = 'calendar_holiday_nonfree';
                        break;
                    default:
                        $class = '';
                }
                $ret .= '<span class="'.$class.'">'.$holiday['name']."</span><br />\n";
            }
        }
        // show "all day" events
        if (count($this->_special_events['all_day']) > 0) {
            foreach ($this->_special_events['all_day'] as $row) {
                $ret .= $row."<br />\n";
            }
        }
        // is there something to output?!
        if (!empty($ret)) {
            $class = $this->_get_header_css_class($this->_date_timestamp);
            $ret = '
                <td colspan="'.$this->_day_columns.'" class="calendar_day '.$class.'">
                    '.$ret.'
                </td>'."\n";
            $this->_top_line .= $ret;
        }
    }
    
    
    /**
     * get the top/first line of the day with (probably) holidays and special days.
     *
     * @return string  the formated/layouted top line of the day
     */
    function _get_top_line()
    {
        $ret = '';
        if (!empty($this->_top_line)) {
            $colspan = ($this->_show_add) ? 2 : 1;
            $ret = '
            <tr>
                <td colspan="'.$colspan.'"></td>
                '.$this->_top_line.'
            </tr>'."\n";
        }
        return $ret;
    }
    
    
    /**
     * get the bottom line of the day with events outside the time range.
     *
     * @return string  the formated/layouted bottom line of the day
     */
    function _get_bottom_line()
    {
        $ret = '';
        // show events outside the visible time range
        if (count($this->_special_events['outside']) > 0) {
            $ret .= "<br />\n".__('Further events').":<br />\n";
            foreach ($this->_special_events['outside'] as $row) {
                $ret .= $row."<br />\n";
            }
        }
        return $ret;
    }
    
    
    /**
     * prepare the day data, build the matrix.
     *
     */
    function _build_the_matrix()
    {
        $matrix       = array();
        $hour_start   = $this->_day_start;
        $minute_start = 0;
        $minute_end   = 0;
        $day_columns  = 1;
        $table_row    = 1;
    
        while ($hour_start < $this->_day_end) {
            $hour_end    = $hour_start;
            $minute_end += $this->_time_step;
            if ($minute_end > 59) {
                $hour_end++;
                $minute_end -= 60;
            }
    
            $time_start = substr('0'.$hour_start, -2).substr('0'.$minute_start, -2);
            $time_end   = substr('0'.$hour_end, -2).substr('0'.$minute_end, -2);
            $table_col  = 1;
            
            if (empty($this->_the_matrix)) {
                $matrix[$table_row][0] = $time_start;
            }
    
            // initial fill the matrix if $this->_day_events is an empty array
            $matrix[$table_row][$table_col] = 0;
            foreach ($this->_day_events as $key=>$row) {
                // handle this event only if it is in the current time range
                if ($row['anfang'] < $time_end && $row['ende'] > $time_start) {
                    if (empty($this->_day_events[$key]['_table_col'])) {
                        // search if we can place this event in a free column slot.
                        // the result may not end in the real column slot, cause
                        // the final column slot(s) are calculated in the next loop;
                        // the stuff here gives us the final amount of columns.
                        $found_free_slot = false;
                        for ($ii=1; $ii<=$day_columns; $ii++) {
                            if (empty($matrix[$table_row][$ii])) {
                                $found_free_slot = true;
                                $table_col = $ii;
                                break;
                            }
                        }
                        if (!$found_free_slot) {
                            // no free slots found, get the next table column
                            $day_columns++;
                            $table_col = $day_columns;
                        }
                        $this->_day_events[$key]['_table_col'] = $table_col;
                    }
                    else {
                        // column slot already found/set
                        $table_col = $this->_day_events[$key]['_table_col'];
                    }
    
                    // save the start row within the matrix
                    if (empty($this->_day_events[$key]['_startrow'])) {
                        $this->_day_events[$key]['_startrow'] = $table_row;
                    }
    
                    // flag this slot as used
                    $matrix[$table_row][$table_col] = -1;
                    $this->_day_events[$key]['_rowspan']++;
                }
            }
    
            $table_row++;
            $hour_start   = $hour_end;
            $minute_start = $minute_end;
        }
    
        // initialize/cleanup the whole matrix with
        // the negatived timestamp of the day
        for ($ii=1; $ii<=count($matrix); $ii++) {
            for ($jj=1; $jj<=$day_columns; $jj++) {
                $matrix[$ii][$jj] = -($this->_date_timestamp);
            }
        }

        // rebuild/refill the matrix
        $cp_events =& $this->_day_events;
        foreach ($this->_day_events as $k=>$v) {
            if (!empty($v['_colspan'])) {
                continue;
            }
            $col_events = array($k);
            foreach ($cp_events as $k2=>$v2) {
                if ( $k2 == $k || !empty($v2['_colspan']) ||
                     $v2['_startrow'] >= ($v['_startrow'] + $v['_rowspan']) ||
                     $v['_startrow'] > ($v2['_startrow'] + $v2['_rowspan']) ) {
                    continue;
                }
                // collect events which are colliding together
                $col_events[] = $k2;
            }
            reset($this->_day_events);
            $rest_columns = $day_columns;
            $count_events = count($col_events);
            for ($ii=0; $ii<count($col_events); $ii++) {
                $colspan = (int) ceil($rest_columns / $count_events);
                $this->_day_events[$col_events[$ii]]['_colspan'] = $colspan;
                $this->_day_events[$col_events[$ii]]['_startcol'] = $day_columns - $rest_columns + 1;
                if ( empty($this->_day_events[$col_events[$ii+1]]['_table_col']) ||
                     $this->_day_events[$col_events[$ii]]['_table_col'] != $this->_day_events[$col_events[$ii+1]]['_table_col'] ) {
                    $rest_columns -= $colspan;
                }
                $count_events--;
    
                $_startrow = $this->_day_events[$col_events[$ii]]['_startrow'];
                $_rowspan  = $this->_day_events[$col_events[$ii]]['_rowspan'];
                $_startcol = $this->_day_events[$col_events[$ii]]['_startcol'];
                $_colspan  = $this->_day_events[$col_events[$ii]]['_colspan'];
                for ($jj=$_startrow; $jj<$_startrow+$_rowspan; $jj++) {
                    for ($kk=$_startcol; $kk<$_startcol+$_colspan; $kk++) {
                        if ($matrix[$jj][$kk] == -($this->_date_timestamp)) {
                            $matrix[$jj][$kk] = $col_events[$ii];
                        }
                    }
                }
            }
        }

        $this->_day_columns = $day_columns;
        if (empty($this->_the_matrix)) {
            $this->_the_matrix = $matrix;
        } else {
            foreach ($this->_the_matrix as $key=>$val) {
                $this->_the_matrix[$key] = array_merge($val, $matrix[$key]);
            }
        }
    }
    
    
    /**
     * get the main day view.
     *
     * @return string  the builded part of table view
     */
    function _get_main_view()
    {
        $ret = '';
        $td_width = (($this->_all_columns > 1) ? (int) ceil(98/$this->_all_columns) : '98');
    
        foreach ($this->_the_matrix as $time_row=>$time_column) {
    
            $time = substr($time_column[0], 0, 2).':'.substr($time_column[0], -2);
            $time_link = substr($time_column[0], 0, 2).substr($time_column[0], -2);
            $ret .= "<tr>\n";
            $ret .= '<td class="calendar_table">&nbsp;'.$time."&nbsp;</td>\n";
            if ($this->_show_add) {
                $ret .= '<td class="calendar_table"><a href="./calendar.php?mode=forms&amp;view='.$this->_view.
                        '&amp;year='.$this->_year.'&amp;month='.$this->_month.'&amp;day='.$this->_day.
                        '&amp;formdata[anfang]='.urlencode($time_link).$this->_act_param.$this->_sid.
                        '" title="'.__('Create new event').'">'.PHPR_CALENDAR_ADD_SIGN."</a></td>\n";
            }
    
            $colspan = 1;
    
            for ($ii=1; $ii<=$this->_all_columns; $ii++) {
    
                // skip this if the last time entry has the same ID
                // cause this has defined a rowspan > 1
                if ($time_row > 1 && $time_column[$ii] > 0 &&
                    $this->_the_matrix[$time_row][$ii] == $this->_the_matrix[$time_row-1][$ii]) {
                    continue;
                }
                // do the colspan stuff
                if ($ii < $this->_all_columns) {
                    for ($jj=$ii+1; $jj<=$this->_all_columns; $jj++) {
                        if ($this->_the_matrix[$time_row][$jj] != $time_column[$ii]) {
                            break;
                        }
                        $colspan++;
                    }
                }
    
                // set default values
                $event   = '';
                $remark  = '';
                $rowspan = 1;
                $class   = $this->_cal_class[0];

                if ($time_column[$ii] < 0) {
                    // field is filled with a negatived timestamp
                    $class = $this->_get_common_css_class(abs($time_column[$ii]));
                } else if ($time_column[$ii] > 0) {
                    // ok, we have an event (id) to show
                    $row =& $this->_all_events[$time_column[$ii]];
                    $alt_title = calendar_get_alt_title_tag($row);
                    if ( calendar_can_read_events($this->_user_id, $row['visi']) ||
                         calendar_can_edit_events($this->_user_id, $row['visi']) ) {
                        $event = '<a href="./calendar.php?ID='.$row['ID'].'&amp;mode=forms&amp;view='.$this->_view.
                                 $this->_act_param.$this->_sid.
                                 '" class="calendar_day_event" title="'.$alt_title.'">'.calendar_get_event_text($row).'</a>';
                    }
                    else {
                        $event = '<span class="calendar_day_event" title="'.$alt_title.'">'.calendar_get_event_text($row).'</span>';
                    }
                    if ($this->_show_remark) {
                        $remark  = '<div style="margin:3px;border-bottom:1px dashed #203040;"></div>'.
                                   "\n".xss(nl2br($row['remark']))."\n";
                    }
                    $rowspan = $row['_rowspan'];
                    $status  = ($row['status'] == '1') ? 5 : $row['partstat'] + 1;
                    $class   = $this->_cal_class[$status];
                }
                $ret .= '<td colspan="'.$colspan.'" rowspan="'.$rowspan.'" width="'.$td_width.
                        '%" class="calendar_day '.$class.'">'."\n$event\n$remark\n</td>\n";
                // add the used colspans to the loop (skip)
                $ii += $colspan - 1;
                $colspan = 1;
            }
    
            $ret .= '</tr>';
        }
        
        return $ret;
    }
    
    
    /**
     * prepare special events such as all-day or out-of-range events.
     *
     */
    function _prepare_special_events()
    {
        $type = null;
        // empty all_day array cause this is a per day container
        $this->_special_events['all_day'] = array();
        
        foreach ($this->_day_events as $key=>$val) {
            if ($val['anfang'] == '----' && $val['ende'] == '----') {
                // collect "all day" events
                $type = 'all_day';
            }
            else if ( $val['ende'] <= $this->_day_start.'00' || 
                      $val['anfang'] >= $this->_day_end.'00' ) {
                // collect events outside the visible time range
                $type = 'outside';
            }
    
            if ($type !== null) {
                $prefix_date = '';
                if ($type == 'outside') {
                    // prefix the date on outside events cause week view(s) needs this
                    $prefix_date = $this->_date_format_object->convert_db2user($val['datum']).'&nbsp;:&nbsp;';
                }
                $alt_title = calendar_get_alt_title_tag($val);
                if ( calendar_can_read_events($this->_user_id, $val['visi']) ||
                     calendar_can_edit_events($this->_user_id, $val['visi']) ) {
                    // user has read/edit access; build the link stuff
                    $this->_special_events[$type][] = $prefix_date.'<a href="./calendar.php?ID='.$val['ID'].
                                                      '&amp;mode=forms&amp;view='.$this->_view.
                                                      $this->_act_param.$this->_sid.'" title="'.$alt_title.'">'.
                                                      calendar_get_event_text($val).'</a>';
                }
                else {
                    // this event is only visible to other
                    $this->_special_events[$type][] = $prefix_date.'<span title="'.$alt_title.'">'.
                                                      calendar_get_event_text($val).'</span>';
                }
                // remove this entry from the list cause this is not needed for the main view
                unset($this->_day_events[$key]);
                $type = null;
            }
        }
    }

    
    /**
     * get the proper common css class.
     *
     * @param  string $timestamp
     * @return string  the css class name
     */
    function _get_common_css_class($timestamp)
    {
        $ret = '';
        if ($this->_date_format_object->is_weekend($timestamp)) {
            $ret = 'calendar_day_weekend';
        } else {
            $ret = 'calendar_day_current';
        }
        return $ret;
    }


    /**
     * get the proper header css class.
     *
     * @param  string $timestamp
     * @return string  the css class name
     */
    function _get_header_css_class($timestamp)
    {
        $ret = '';
        if ( $this->_date_format_object->get_date_from_timestamp($timestamp) == $this->_date_format_object->get_date_from_timestamp(time()) ) {
            $ret = 'calendar_day_today';
        } else if ($this->_date_format_object->is_weekend($timestamp)) {
            $ret = 'calendar_day_weekend';
        } else {
            $ret = 'calendar_day_current';
        }
        if ( $this->_date_format_object->get_date_from_timestamp($timestamp) != $this->_date_format_object->get_date_from_timestamp(time()) && 
             date('d', $timestamp) == substr('0'.$this->_day, -2) ) {
            $ret = 'calendar_day_sameday';
        }
        return $ret;
    }

}

?>
