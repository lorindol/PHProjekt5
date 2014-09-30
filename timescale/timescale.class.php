<?php
/**
 * @package    misc
 * @subpackage timescale
 * @author     Gustavo Solt, $Author: gustavo $
 * @licence    GPL, see www.gnu.org/copyleft/gpl.html
 * @copyright  2000-2006 Mayflower GmbH www.mayflower.de
 * @version    $Id: timescale.class.php
 */

define('PATH_PRE','../');

include_once(PATH_PRE.'lib/lib.inc.php');
require_once(PATH_PRE.'lib/specialdays.php');

// Error Level
//error_reporting(E_ALL);

class Create_table {
    var $end_date   = '';
    var $end_year = '';
    var $end_month = '';
    var $end_day = '';

    var $start_date = '';
    var $start_year = '';
    var $start_month = '';
    var $start_day = '';

    var $data = array();

    var $day_week = array ( '0' => 'D',
                            '1' => 'M',
                            '2' => 'T',
                            '3' => 'W',
                            '4' => 'J',
                            '5' => 'F',
                            '6' => 'S');
    var $weekend_color;

    var $holidays_class = '';
    var $holidays_color = '';

    function Create_table($start,$end,$holidays_class = '', $weekend_color = '#808080', $holidays_color = 'red') {
        $this->start    = $start;
        $this->end      = $end;

        list($this->start_year,$this->start_month,$this->start_day) = split('-',$this->start);
        list($this->end_year,$this->end_month,$this->end_day)       = split('-',$this->end);

        $this->weekend_color = $weekend_color;
        
        $this->holidays_class = $holidays_class;
        $this->holidays_color = $holidays_color;
    }

    function make_years_title($spaces = 0,$max = array()) {
        $output = '<tr align="center">'."\n";
        for($i=1 ; $i < $spaces; $i++) {
            $output .= '<td style="color:#FFFFFF">'.str_repeat("#", $max[$i]).'</td>'."\n";
        }
        foreach($this->data as $year => $months) {
            $count = 0;
            foreach($months as $month => $weeks) {
                foreach($weeks as $week => $days) {
                    foreach($days as $day => $day_week) {
                        $count++;
                    }
                }
            }
            $output .= '<td colspan="'.$count.'">'.$year.'</td>'."\n";
        }
        
        $output .= '</tr>'."\n";
        return $output;
    }

    function make_months_title($titles = array()) {
        $output = '<tr align="center">'."\n";
        foreach ($titles as $pos => $title) {
            if ($pos > 0) {
                if (isset($title['title_alt']) && (!empty($title['title_alt']))) {
                    $title_alt = ereg_replace("\n",'<br />',html_entity_decode($title['title_alt']));
                    $title_alt = addslashes(htmlentities(ereg_replace("\r",'',html_entity_decode($title_alt))));
                    $alt       = 'onmouseover="show_alternative_view(\''.$title_alt.'\',300,150,\'alternative_view\')" onmouseout="hide_alternative_view(\'alternative_view\')"';
                } else {
                    $alt = '';
                }
                if (!empty($title['title_link'])) { 
                    $output .= '<td align="'.$align.'"><nobr><a href="#" onclick="window.open(\''.$title['title_link'].'\', \'_blank\', \'width=1140px,height=540px,scrollbars=yes\');"  '.$alt.'>'.$title['title'].'</a></nobr></td>'."\n";
                } else {
                    $output .= '<td align="'.$align.'"><nobr><a href="#">'.$title['title'].'</a></nobr></td>'."\n";
                }
            }
        }
        foreach($this->data as $year => $months) {
            foreach($months as $month => $weeks) {
                $count = 0;
                foreach($weeks as $week => $days) {
                    foreach($days as $day => $day_week) {
                        $count++;
                    }
                }
                $name = date("F",mktime(0,0,0,$month,$day,$year));
                $output .= '<td colspan="'.$count.'">'.$name.'</td>'."\n";
            }
        }
        
        $output .= '</tr>'."\n";
        return $output;
    }

    function make_weeks_title($spaces = 0) {
        $output = '<tr align="center">'."\n";
        for($i=1 ; $i < $spaces ; $i++) {
            $output .= '<td>&nbsp;</td>'."\n";
        }
        foreach($this->data as $year => $months) {
            foreach($months as $month => $weeks) {
                foreach($weeks as $week => $days) {
                    $count = 0;
                    foreach($days as $day => $day_week) {
                        $count++;
                    }
                    $output .= '<td colspan="'.$count.'">'.$week.'.Week</td>'."\n";
                }

            }
        }
        
        $output .= '</tr>'."\n";
        return $output;
    }


    function make_days_title($spaces = 0) {
        $output1 = '<tr align="center" height=3>'."\n";
        $output2 = '<tr align="center">'."\n";
        for($i=1 ; $i < $spaces ; $i++) {
            $output1 .= '<td bgcolor="#BBBBBB">&nbsp;</td>'."\n";
            $output2 .= '<td bgcolor="#BBBBBB">&nbsp;</td>'."\n";
        }
        foreach($this->data as $year => $months) {
            foreach($months as $month => $weeks) {
                foreach($weeks as $week => $days) {
                    foreach($days as $day => $day_data) {
                        if (strlen($day) == 1) {
                            $day = '0'.$day;
                        }
                        $output1 .= '<td bgcolor="'.$day_data['title_color'].'">'.$day.'</td>'."\n";
                        $output2 .= '<td bgcolor="'.$day_data['title_color'].'">'.$this->day_week[$day_data['day_week']].'</td>'."\n";
                    }
                }

            }
        }
        
        $output1 .= '</tr>'."\n";
        $output2 .= '</tr>'."\n";
        return $output1.$output2;
    }

    function make_separators($spaces = 0,$empty_title_link = '') {
        $output = '<tr align="center" style="height=12px;">'."\n";
        for($i=1 ; $i < $spaces ; $i++) {
            if ($i==1) {
                if (!empty($empty_title_link)) {
                    $output .= '<td bgcolor="#BBBBBB" onclick="window.open(\''.$empty_title_link.'\', \'_blank\', \'width=1140px,height=540px,scrollbars=yes\');"></td>'."\n";
                } else {
                    $output .= '<td bgcolor="#BBBBBB"></td>'."\n";
                }
            } else {
                $output .= '<td bgcolor="#BBBBBB"></td>'."\n";
            }
        }
        foreach($this->data as $year => $months) {
            foreach($months as $month => $weeks) {
                foreach($weeks as $week => $days) {
                    foreach($days as $day => $day_data) {
                        $output .= '<td bgcolor="'.$day_data['title_color'].'"></td>'."\n";
                    }
                }

            }
        }
        
        $output .= '</tr>'."\n";
        return $output;
    }

    function make_data () {
        for($y = $this->start_year; $y <= $this->end_year ; $y++) {
            if ($y == $this->start_year) {
                $start_month = intval($this->start_month);
            } else {
                $start_month = 1;
            }
            $holidays = $this->holidays_class->_calculate($y);
            for($m = $start_month; $m <= 12 ; $m++) {
                if (($y < $this->end_year) || (($y == $this->end_year) && ($m <= $this->end_month)) ) {
                    $lastday = date("t",mktime(0,0,0,$m,1,$y));
                    if (($y == $this->start_year)&&($m == $this->start_month)) {
                        $start_day = intval($this->start_day);
                    } else {
                        $start_day = 1;
                    }
                    for($d = $start_day; $d <= 31 ; $d++) {
                        if ($d <= $lastday) {
                            if ($y == $this->end_year && $m == $this->end_month && $d == $this->end_day+1) {
                                break;
                            }
                            $mktime = mktime(0,0,0,$m,$d,$y);
                            $W = date("W",$mktime); // Week
                            $w = date("w",$mktime); // Week day
             
                            $color       = '#FFFFFF';
                            $title_color = '#BBBBBB';
                            if (($w == 0) || ($w == 6)) {
                               $color       = $this->weekend_color;
                               $title_color = $this->weekend_color;
                            }

                            if (is_array($holidays)) {
                                foreach($holidays as $tmp => $holiday_data) {
                                    if ($holiday_data['date'] == $mktime) {
                                        $color       = $this->holidays_color;
                                        $title_color = $this->holidays_color;
                                    }
                                }
                            }
                            $this->data[$y][$m][$W][$d] = array('day_week'      => $w,
                                                                'color'         => $color,
                                                                'title_color'   => $title_color);
                        }
                    }
                }
            }
        }
    }

    function put_data($data, $change, $line) {
        $output = '<tr align="left">'."\n";
        $data['titles'][0]['title_value'] = "&nbsp;";
        foreach ($data['titles'] as $pos => $title) {
            if ($pos > 0) {
                if (empty($title['title_value'])) {
                    $title['title_value'] = "&nbsp";
                }
                if ($pos == 1) {
                    $align = "left";
                } else {
                    $align = "left";
                }
                
                if($title['title_type']!='textarea'){
                    $nobr_anf='<nobr>';
                    $nobr_ende='</nobr>';
                }
                else{
                    $nobr_anf='';
                    $nobr_ende='';
                }
                if (!empty($title['title_link'])) { 
                
                    if (isset($data['alt_text']) && (!empty($title['alt_text']))) {
                        $alt = 'onmouseover="show_alternative_view(\''.addslashes(htmlentities($data['alt_text'])).'\',300,150,\'alternative_view\')" onmouseout="hide_alternative_view(\'alternative_view\')"';
                    } else {
                        $alt = '';
                    }
                    $output .= '<td align="'.$align.'">'.$nobr_anf.'<a href="#" onclick="window.open(\''.$title['title_link'].'\', \'_blank\', \'width=1140px,height=540px,scrollbars=yes\');" '.$alt.'>'.$title['title_value'].'</a>'.$nobr_ende.'</td>'."\n";
                } else {
                    $output .= '<td align="'.$align.'">'.$nobr_anf.$title['title_value'].$nobr_ende.'</td>'."\n";
                }
            }
        }

        foreach($this->data as $year => $months) {
            foreach($months as $month => $weeks) {
                if (strlen($month) == 1) { $month = '0'.$month; }
                foreach($weeks as $week => $days) {
                    foreach($days as $day => $day_data) {
                        if (strlen($day) == 1) { $day = '0'.$day; }
                        $display = 0;
                        foreach ($data['data'] as $content) {
                            list($start_year,$start_month,$start_day) = split('-',$content['start']);
                            list($end_year,$end_month,$end_day) = split('-',$content['end']);

                            if (strlen($start_month) == 1) { $start_month = '0'.$start_month; }
                            if (strlen($start_day) == 1) { $start_day = '0'.$start_day; }
                            if (strlen($end_month) == 1) { $end_month = '0'.$end_month; }
                            if (strlen($end_day) == 1) { $end_day = '0'.$end_day; }
        
                            $start_date = "$start_year-$start_month-$start_day";
                            $end_date = "$end_year-$end_month-$end_day";

                            if (($year == $start_year) && ($month == $start_month) && ($day == $start_day)) {
                                $display = 1;
                                $count     = $this->get_diff_days($content['start'],$content['end']) + 1;
                                $td_color  = $content['td_color'];
                                $text_link = $content['text_link'];
                                $text      = $content['text'];
                                $style     = $content['style'];
                            
                                if (isset($content['text_alt']) && (!empty($content['text_alt']))) {
                                    $text_alt = ereg_replace("\n",'<br />',html_entity_decode($content['text_alt']));
                                    $text_alt = addslashes(htmlentities(ereg_replace("\r",'',html_entity_decode($text_alt))));
                                    $alt       = 'onmouseover="show_alternative_view(\''.$text_alt.'' .'\',300,150,\'alternative_view\')" onmouseout="hide_alternative_view(\'alternative_view\')"';
                                } else {
                                    $alt = '';
                                }
                                break;
                            } else if (("$year-$month-$day" >= $start_date) && ("$year-$month-$day" <= $end_date)) {
                                $display = 2;
                                break;
                            }
                        }
                        if ($display == 1) {
                            $output .= '<td id="id_'.$line.'_'.$year.'_'.$month.'_'.$day.'" bgcolor="'.$td_color.'" colspan="'.$count.'" align="left">'."\n";
                            if (strlen($text) > 8) {
                                $text = substr($text,0,8)."...";
                            }
                            if (empty($text_link)) { 
                                $output .= '<a href="#" '.$alt.'><span style="'.$style.'">'.$text.'</span></a>'."\n";
                            } else {
                                $output .= '<a href="#" onclick="window.open(\''.$text_link.'\', \'_blank\', \'width=1140px,height=540px,scrollbars=yes\');" '.$alt.'><span style="'.$style.'">'.$text.'</span></a>'."\n";
                            }
                            $output .= '</td>'."\n";
                        } else if ($display == 0) {
                            if (isset($data['empty_field_link'])) {
                                $todo_start_date = "$year-$month-$day";
                                $todo_end_date = $this->get_end_date($todo_start_date);
                                $empty_field_link = $data['empty_field_link'];
                                $empty_field_link = ereg_replace('--anfang--',$todo_start_date,$empty_field_link);
                                $empty_field_link = ereg_replace('--deadline--',$todo_end_date,$empty_field_link);
                                $output .= '<td bgcolor="'.$day_data['color'].'" onclick="window.open(\''.$empty_field_link.'\', \'_blank\', \'width=1140px,height=540px,scrollbars=yes\');">&nbsp;</td>'."\n";
                            } else {
                                $output .= '<td bgcolor="'.$day_data['color'].'">&nbsp;</td>'."\n";
                            }
                        }
                    }
                }
            }
        }
        
        $output .= '</tr>'."\n";
        return $output;
    }

    function show($data) {
        
        $this->make_data();
        $output = '<div id="timescale">';
        $output .= '<script type="text/javascript">var fields = new Array(); var count = 0;</script>'."\n";
        $output .= '<table border="1" cellpadding="1" cellspacing="0">';
        $spaces = count($data[0]['titles']);
        $titles = $data[0]['titles'];
        $max = array();
        if (empty($data)) {
            $error = __('No Entries Found');
            die('<html><body><h3>'.$error.'</h3>'.__('Possible reasons why you do not get a result are:<br /><br />- The start date AND the end date must be between the two dates that you put. (If the project start before the start date or if the project end after the end date that you put, the project will not be listed)<br />- You must select at least one user and one project').'</body></html>');
        }
        foreach ($data[0]['titles'] as $tmp => $title_array) {
            $length1 = strlen($title_array['title']);
            $length2 = strlen($title_array['title_value']);
            if (!isset($max[$tmp])) {
                if ($length1 > $length2) {
                    $max[$tmp] = $length1;
                } else { 
                    $max[$tmp] = $length2;
                }
            } else {
                if (($length1 > $length2) && ($length1 > $max[$tmp])) {
                    $max[$tmp] = $length1;
                } else if (($length2 > $length1) && ($length2 > $max[$tmp])) {
                    $max[$tmp] = $length2;
                }
            }
        }
        $output .= $this->make_years_title($spaces,$max);
        $output .= $this->make_months_title($titles);
        $output .= $this->make_weeks_title($spaces);
        $first      = 1;
        $last_title = '';
        foreach ($data as $line => $d) {
        
            if ($last_title != $d['titles'][0]['title_value']) {
                $last_title = $d['titles'][0]['title_value'];
                if ($first) {
                    $output .= $this->make_days_title($spaces);
                    $first = 0;
                }
                $empty_title_link = (isset($d['empty_title_link']) ? $d['empty_title_link'] : '');
                $output .= $this->make_separators($spaces,$empty_title_link);
                $change = 1;
            }
            $output .= $this->put_data($d,$change,$line);
        }
        $output .= '</table>';
        $output .= '</div>';
        /*
        $output .= '
        <script type="text/javascript">
            function findPos(obj) {
	            var curleft = curtop = 0;
    	        if (obj.offsetParent) {
	            	curleft = obj.offsetLeft
            		curtop = obj.offsetTop
            		while (obj = obj.offsetParent) {
            			curleft += obj.offsetLeft
            			curtop += obj.offsetTop
            		}
            	}
            	return [curleft,curtop];
            }
            for (i = 0; i < fields.length ; i++) {
                var coors = findPos(document.getElementById(fields[i]));
                x = document.getElementById(\'text_\' + fields[i]);
                coors[0] = coors[0] + 3;
                coors[1] = coors[1] + 3;
        	    x.style.left = coors[0] + \'px\'
            	x.style.top = coors[1] + \'px\';
                x.style.display = \'block\';
                x.style.width = x.innerHTML.length + \'px\';
            }
         </script>';
        */
        return $output;
    }

    /*
     * Get the days between two dates
     * @param   date $startdate - First date
     * @param   date $enddate   - Second date
     * @return  int             - Number of days
     */
    function get_diff_days($startdate,$enddate) {

        list($syear,$smonth,$sday) = split('-',$startdate);
        list($eyear,$emonth,$eday) = split('-',$enddate);

        $start = mktime ( 0,0,0,$smonth, $sday, $syear);
        $end   = mktime ( 0,0,0,$emonth, $eday, $eyear);

        // Days
        return round(((($end - $start)/60)/60)/24);
    }
    
    /*
     * Return the end_date 7 days before the parsed date
     * @param string  - Date
     * @return string - Date + 7 days 
     */
    function get_end_date($start_date) {
        list($year,$month,$day) = split("-",$start_date);
        $end_date   = date("Y-m-d",mktime(0,0,0,$month,$day+6,$year));

        return $end_date;
    }
}
?>
