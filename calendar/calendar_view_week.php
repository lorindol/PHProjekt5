<?php
/**
* calendar week view
*
* @package    calendar
* @module     view
* @author     Albrecht Guenther, $Author: alexander $
* @licence    GPL, see www.gnu.org/copyleft/gpl.html
* @copyright  2000-2006 Mayflower GmbH www.mayflower.de
* @version    $Id: calendar_view_week.php,v 1.57 2006/10/26 10:13:35 alexander Exp $
*/
if (!defined('lib_included')) die('Please use index.php!');
include_once('./calendar_view_day.php');

/**
* Extended class to display weekly events, based on daily views.
*
* @package PHProjekt
* @author  Giampaolo Pantò, Munich-Germany <panto@mayflower.de>
*/
class Calendar_View_Week extends Calendar_View_Day
{

    /**
    * Constructor
    *
    */
    //function Calendar_View_Week()
    //{
        // note: use the constructor of the base class
    //}
    
    /**
     * get the main week view.
     *
     * @return string  the main week view
     */
    function get_view()
    {
        $user_params        = calendar_get_user_params();
        $this->_user_id     =& $user_params['user_id'];
        $this->_act_param   =& $user_params['act_param'];
        $this->_show_add    = false;
        $this->_show_remark = false;
        $this->_day_start   = (!empty($this->_settings['tagesanfang'])) ? $this->_settings['tagesanfang'] : PHPR_DAY_START;
        $this->_day_end     = (!empty($this->_settings['tagesende']))   ? $this->_settings['tagesende']   : PHPR_DAY_END;
        $first_day_week     = (isset($this->_settings['first_day_week'])) ? $this->_settings['first_day_week'] : 1;
        
        // calculate the start of the week
        $timestamp_start = mktime(0,0,0, $this->_month, $this->_day, $this->_year);
        $day_number      = date('w', $timestamp_start);
        if ($day_number < $first_day_week) {
            $day_number += 7;
        }
        $day_number = ($day_number > $first_day_week) ? $day_number - $first_day_week : 0;
        $timestamp_start -= (86400 * $day_number);
        
        // step thru the day(s)
        for ($ii=0; $ii<7; $ii++) {
            $this->_date_timestamp = $timestamp_start + $ii * 86400;
            if ($this->_mode == 2 && $this->_date_format_object->is_weekend($this->_date_timestamp)) {
                // skip this day on $mode = 2 cause it belongs to a weekend (sat/sun)
                continue;
            }
            $this->_get_events();
            $this->_prepare_special_events();
            $this->_build_the_matrix();
            $this->_build_top_line_add_on();
            $this->_build_top_line();
            // add the events/columns together
            $this->_all_events  += $this->_day_events;
            $this->_all_columns += $this->_day_columns;
        }

        $ret = '
    <table cellspacing="1" cellpadding="0" class="calendar_table" width="100%" border="0">
    <caption>'.calendar_view_prevnext_header('w', $user_params).'</caption>
        '.$this->_get_top_line().'
        '.$this->_get_main_view().'
    </table>
    '.$this->_get_bottom_line().'
    <br /><br />
';
        return $ret;
    }

    
    /**
     * build the add on string for the top line.
     *
     */
    function _build_top_line_add_on()
    {
        global $name_day2;
        
        $name_day2_int = (date('w', $this->_date_timestamp)-1 < 0) ? 6 : date('w', $this->_date_timestamp) - 1;
        $href_text = $name_day2[$name_day2_int].'.&nbsp;'.
                     $this->_date_format_object->convert_db2user(date('Y-m-d', $this->_date_timestamp));

        $args = '?mode=1&amp;view='.$this->_view.'&amp;year='.date('Y', $this->_date_timestamp).
                '&amp;month='.date('n', $this->_date_timestamp).'&amp;day='.date('j', $this->_date_timestamp).
                $this->_act_param.$this->_sid;

        if (calendar_can_edit_events($this->_user_id, 'public')) {
            $add = '&nbsp;<a href="./calendar.php?mode=forms&amp;view='.$this->_view.
                   '&amp;year='.date('Y', $this->_date_timestamp).'&amp;month='.date('n', $this->_date_timestamp).
                   '&amp;day='.date('j', $this->_date_timestamp).$this->_act_param.$this->_sid.
                   '" title="'.__('Create new event').'">'.PHPR_CALENDAR_ADD_SIGN.'</a>';
        } else {
            $add = '';
        }

        $this->_top_line_add_on = '<a href="./calendar.php'.$args.'" title="'.$href_text.'">'.$href_text."</a>".$add;
    }

}

?>
