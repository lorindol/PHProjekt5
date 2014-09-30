<?php
/**
 * Ical functions
 *
 * @package    	misc
 * @subpackage 	export
 * @author     	Gustavo Solt, $Author: gustavo $
 * @licence     GPL, see www.gnu.org/copyleft/gpl.html
 * @copyright  	2000-2006 Mayflower GmbH www.mayflower.de
 * @version    	$Id: export_ical.php
 */

/**
 * Build an ical/vcal-export-string from $export_array (generated by export.php)
*
 * @author Franz Graf
 * @see http://www.faqs.org/rfcs/rfc2445.html Internet Calendaring and Scheduling Core Object Specification (iCalendar)
 * @see http://www.imc.org/pdi/pdiproddev.html vcalendar specification
 *
 * @param array $export_array as created by export.php for file=calendar
 * @param bool $toUTF8 whether output should be converted to UTF8 or not
 * @param string ical|vcal
 * @param array $todo_array - Todo data
 * @param array $helpdesk_array - Helpdesk data
 * @return string ical- or vcal-export-file as string
 */

include_once('Date.php');

function export_user_cal($export_array, $format, $todo_array = array(), $helpdesk_array = array()) {
    $end = chr(13).chr(10);

    // Vcal and ical are _almost_ the same.
    // ical default:
    $encoding = "";
    $version  = "2.0";

    // vcal diffenrences
    if ($format == 'vcal') {
        $version  = "1.0";
        $encoding = ";ENCODING=8-bit";
    }

    // Format of export-array:
    // 0 : id
    // 1 : event (one line w/out \n)
    // 2 : date
    // 3 : begin
    // 4 : end
    // 5 : remark
    // 6 : contact-vorname
    // 7 : contact-nachname
    // 8 : contact-firma
    // 9 : contact-email
    // 10: visibility
    // 11: sync2
    // 12: ical_ID
    // 13: serie_id
    // 14: serie_typ
    // 15: serie_bis

    // create header
    $outString  = 'BEGIN:VCALENDAR'.$end;
    $outString .= 'PRODID:-//PHProject//PHProject 5.3//EN'.$end;
    $outString .= 'VERSION:'.$version.$end;
    $outString .= 'METHOD:REQUEST'.$end;

    // process all events
    foreach ($export_array as $line) {
        // build "head" of an event
        $outString .= 'BEGIN:VEVENT'.$end;

        $outString .= 'UID:PHPROJEKT-EVENT-'.$line[0].$end;

        if (isset($line[16])) {
        	switch ($line[16]) {
        		case 1:
        		  $status = 'TENTATIVE';
        		  break;
        		case 2:
        		  $status = 'CONFIRMED';
        		  break;
        		case 3:
        		  $status = 'CANCELLED';
                  break;
        		default:
        		  $status = false;
        	}
        	if ($status) {
        	   $outString .= 'STATUS:'.$status.$end;
        	}
        }
        
        if (isset($line[11])) {
            $line[11] = $line[11] - date("Z");
            $year   = (int) substr($line[11],0,4);
            $month  = (int) substr($line[11],4,2);
            $day    = (int) substr($line[11],6,2);
            $hour   = (int) substr($line[11],0,2);
            $minute = (int) substr($line[11],2,2);

            $date = $year."-".$month."-".$day;
            $time = $hour.$minute;
            $outString .= 'LAST-MODIFIED:'.get_utc_date($date,$time).$end;
        }

        if ($line[3] != '----') {
            $outString .= 'DTSTART:'.get_utc_date($line[2],$line[3]).$end;
        } else {
            $outString .= 'DTSTART;VALUE=DATE:'.get_normal_date($line[2]).$end;
        }
        if ($line[4] != '----') {
            $outString .= 'DTEND:'.get_utc_date($line[2],$line[4]).$end;
        } else {
            list($year,$month,$day) = split('-',$line[2]);
            $day++;
            $line[2] = $year.'-'.$month.'-'.$day;
            $outString .= 'DTEND;VALUE=DATE:'.get_normal_date($line[2]).$end;
        }
        if (null !== $line[14] && $line[14] != '' && strstr($line[14],'weekday')) {
            $serie_typ     = unserialize($line[14]);
            $serie_weekday = $serie_typ['weekday'];
            $serie_typ     = $serie_typ['typ'];
            $serie_bis     = $line[15];

            $multipleData['serie_typ'] = $serie_typ;
            $multipleData['serie_weekday'] = $serie_weekday;
            $multipleData['datum'] = $line[2];
            $multipleData['serie_bis'] = $serie_bis;

            $outString .= 'RRULE:'.makeRuleString($serie_typ,$serie_weekday,$serie_bis).$end;

            // Check and remove from the Rule, the deleted events
            $events = calendar_calculate_serial_events($multipleData);
            // Get current events
            $query = "SELECT ID, datum
                        FROM ".DB_PREFIX."termine
                       WHERE serie_id = ".(int)$line[0]."
                         AND is_deleted is NULL";
            $res = db_query($query) or db_die();
            $activeEvents = array();
            while ($row = db_fetch_row($res)) {
                $activeEvents[] = $row[1];
            }
            $diff = array_diff($events, $activeEvents);
            foreach ($diff as $tmp => $eventDate) {
                $outString .= 'EXDATE;VALUE=DATE:'.get_normal_date($eventDate).$end;
            }
        }
        $outString .= 'SUMMARY'.$encoding.':'.prepareString($line[1],$end).$end;

        // visibility
        if ($line[10] == 1) $outString .= 'CLASS:PRIVATE'.$end;
        if ($line[10] == 2) $outString .= 'CLASS:PUBLIC'.$end;

        if (!empty($line['invitees'])) {
            foreach ($line['invitees'] as $iid => $invitee) {
            	if ($iid == $line[18]) {
            		$outString .= 'ORGANIZER;CN='.prepareString($invitee['name'], $end)
                                . ':MAILTO:'.prepareString($invitee['email'], $end).$end;
            	} else {
                    $outString .= 'ATTENDEE;CN='.prepareString($invitee['name'], $end)
                               . ':MAILTO:'.prepareString($invitee['email'], $end).$end;
            	}
            }
        }
        
        // prepare remark
        if (!empty($line[5])) {
            $outString .= 'DESCRIPTION'.$encoding.':'.prepareString($line[5],$end).$end;
        }
        
        // prepare contacts
        /*
        if (!empty($line[6]) or !empty($line[7]) or !empty($line[8])) {
            // vorname name (firma)
            $name = prepareString($line[6],$end)." ".prepareString($line[7],$end);
            if (!empty($line[8])) $name .= " (".prepareString($line[8],$end).")";
            $outString .= 'ATTENDEE;CN="'.$name.'"';
            unset($name);

            // email
            if (!empty($line[9])) $outString .= ':mailto:'.prepareString($line[9],$end);
            // iCal requires a colon if email is missing, bug?
            else $outString .= ':';
            $outString .= $end;
        }// end contacts
        */
        $outString .= 'END:VEVENT'.$end;

    } // end foreach
    
    // Add todo events
    if (!empty($todo_array)) {
        $outString .= get_todos_vcal($todo_array);
    }
    
    // Add helpdesk events
    if (!empty($helpdesk_array)) {
        $outString .= get_helpdesks_vcal($helpdesk_array);
    }

    $outString .= 'END:VCALENDAR'.$end;

    return utf8_encode($outString);
}

/**
 * Return a date and time in utc format
 *
 * @param date $date - date value
 * @param time $time - time value
 * @return string    - UTC format
 */
function get_utc_date($date, $time)
{
    if (strstr($date,'-')) {
        list($year,$month,$day) = split('-',$date);
    } else {
    	$year  = substr($date, 0, 4);
        $month = substr($date, 4, 2);
        $day   = substr($date, 6, 2);
    }
    $hour    = (int) substr($time,0,-2);
    $minute  = (int) substr($time,-2,2);
    	 
    $date = mktime($hour, $minute, 0, $month, $day, $year);
    
    // Can we use DateTime and DateTimeZone? PHP >= 5.1
    if (class_exists('DateTime') && class_exists('DateTimeZone')) {
        $date = new DateTime(date('Y-m-d\TH:i:s', $date));
        $date->setTimezone(timezone_open('UTC'));
        $date = $date->format('Ymd\THis\Z');
    // Can we use PEAR::Date
    } else if (class_exists('Date')) {
        $date = new Date($date);
        $date->toUTC();
        $date = $date->getDate(DATE_FORMAT_ISO_BASIC);
    // Don't use Timezones
    } else {
    	$date = date('Ymd\THis', $date);
    }
    
    return $date;
}

/**
 * Return a date without -
 *
 * @param date $date - date value
 * @return string
 */
function get_normal_date($date)
{
    return ereg_replace('-','',$date);
}

/**
 * Converts a value to a corrrect vcs/ics-value
 * At least Mozilla Sunbird looses trailing umlauts when importing ICS-files
 * Don't know whether that's a bug of kde-korganizer or sunbird
 */
function prepareString ($string,$end) {
    $string = addslashes($string);
    $string = str_replace($end, "\n", $string);
    $string = str_replace("\n", "\\n", $string);
    return utf8_decode($string);
}

function makeRuleString($serie_typ, $serie_weekday, $serie_bis) {
    $return = '';

    // Frequency
    switch ($serie_typ) {
        // Daily
        case 'd1':
            $return = 'FREQ=DAILY;';
            break;
        // weekly 
        case 'w1':
            $return = 'FREQ=WEEKLY;INTERVAL=1;';
            break;
        // every 2 weeks
        case 'w2':
            $return = 'FREQ=WEEKLY;INTERVAL=2;';
            break;
        // every 3 weeks
        case 'w3':
            $return = 'FREQ=WEEKLY;INTERVAL=3;';
            break;
        // every 4 weeks
        case 'w4':
            $return = 'FREQ=WEEKLY;INTERVAL=4;';
            break;
        // monthly
        case 'm1':
            $return = 'FREQ=MONTHLY;INTERVAL=1;';
            break;
        // annually
        case 'y1':
            $return = 'FREQ=YEARLY;INTERVAL=1;';
            break;
    }

    // Weeks days
    $weeksDays = array( 0 => 'MO', 1 => 'TU', 2 => 'WE', 3 => 'TH',
                        4 => 'FR', 5 => 'SA', 6 => 'SU');
    if (!empty($serie_weekday)) {
        $weekDay = array();
        foreach ($serie_weekday as $day => $tmp) {
            $weekDay[] = $weeksDays[$day];
        }
        $return .= 'BYDAY='.implode(',',$weekDay).';';
    }

    // End date
    if ($serie_bis != '') {
        $return .= 'UNTIL='.get_utc_date($serie_bis,'1300');
    }

    return $return;
}

/**
 * Build the todos for the ical/vcal
 *
 * @author Gustavo Solt
 *
 * @param array $data
 * @return string vtodos
 */
function get_todos_vcal($data)
{
    $end = chr(13).chr(10);
    $outString = '';

    // Format of data
    // 0 : id
    // 1 : remark
    // 2 : ext
    // 3 : note
    // 4 : deadline
    // 5 : datum
    // 6 : status
    // 7 : priority
    // 8 : progress
    // 9 : sync2
    // 10: anfang
    
    foreach ($data as $line) {
        $outString .= 'BEGIN:VTODO'.$end;

        if (isset($line[5])) {
            $line[5] = $line[5] - date("Z");
            $year   = (int) substr($line[5],0,4);
            $month  = (int) substr($line[5],4,2);
            $day    = (int) substr($line[5],6,2);
            $hour   = (int) substr($line[5],0,2);
            $minute = (int) substr($line[5],2,2);

            $date = $year."-".$month."-".$day;
            $time = $hour.$minute;
            $outString .= 'CREATED:'.get_utc_date($date,$time).$end;
        }

        if (isset($line[9])) {
            $line[9] = $line[9] - date("Z");
            $year   = (int) substr($line[9],0,4);
            $month  = (int) substr($line[9],4,2);
            $day    = (int) substr($line[9],6,2);
            $hour   = (int) substr($line[9],0,2);
            $minute = (int) substr($line[9],2,2);

            $date = $year."-".$month."-".$day;
            $time = $hour.$minute;
            $outString .= 'LAST-MODIFIED:'.get_utc_date($date,$time).$end;
        }

        $outString .= 'UID:PHPROJEKT-TODO-'.$line[0].$end;
        $outString .= 'SUMMARY:'.prepareString($line[1],$end).$end;

        switch ($line[6]) {
            case 2:
                $status = 'NEEDS-ACTION';
                break;
            case 3:
                $status = 'IN-PROCESS';
                break;
            case 4:
                $status = 'CANCELLED';
                break;
            case 5:
                $status = 'COMPLETED';
                break;
            default:
                $status = '';
                break;
        }
        if (!empty($status)) {
            $outString .= 'STATUS:'.$status.$end;
        }
        $outString .= 'DTSTART:'.get_utc_date($line[10],'1200').$end;
        $outString .= 'DUE:'.get_utc_date($line[4],'1200').$end;
        $outString .= 'CATEGORIES:Todo'.$end;
        $outString .= 'PERCENT-COMPLETE:'.$line[8].$end;
        
        $outString .= 'DESCRIPTION:'.prepareString($line[3],$end).$end;

        $outString .= 'END:VTODO'.$end;
    }

    return $outString;
}

/**
 * Build the todos-helpdesk for the ical/vcal
 *
 * @author Gustavo Solt
 *
 * @param array $data
 * @return string vtodos(helpdesk)
 */
function get_helpdesks_vcal($data)
{
    $end = chr(13).chr(10);
    $outString = '';

    // Format of data
    // 0 : id
    // 1 : name
    // 2 : von
    // 3 : note
    // 4 : due_date
    // 5 : submit
    // 6 : status
    // 7 : priority
    
    foreach ($data as $line) {
        $outString .= 'BEGIN:VTODO'.$end;

        if (isset($line[5])) {
            $line[5] = $line[5] - date("Z");
            $year   = (int) substr($line[5],0,4);
            $month  = (int) substr($line[5],4,2);
            $day    = (int) substr($line[5],6,2);
            $hour   = (int) substr($line[5],0,2);
            $minute = (int) substr($line[5],2,2);

            $date = $year."-".$month."-".$day;
            $time = $hour.$minute;
            $outString .= 'CREATED:'.get_utc_date($date,$time).$end;
        }

        $outString .= 'UID:PHPROJEKT-HELPDESK-'.$line[0].$end;
        $outString .= 'SUMMARY:'.prepareString($line[1],$end).$end;

        switch ($line[6]) {
            case 1:
                $status = 'NEEDS-ACTION';
                break;
            case 2:
                $status = 'IN-PROCESS';
                break;
            case 3:
            case 4:
            case 5:
                $status = 'COMPLETED';
                break;
            default:
                $status = '';
                break;
        }
        if (!empty($status)) {
            $outString .= 'STATUS:'.$status.$end;
        }
        $outString .= 'DTSTART:'.get_utc_date($line[4],'1200').$end;
        $outString .= 'DUE:'.get_utc_date($line[4],'1200').$end;
        $outString .= 'CATEGORIES:Helpdesk'.$end;
        if ($line[6] > 3) {
            $outString .= 'PERCENT-COMPLETE:100'.$end;
        } else {
            $outString .= 'PERCENT-COMPLETE:0'.$end;
        }
        
        $outString .= 'DESCRIPTION:'.prepareString($line[3],$end).$end;

        $outString .= 'END:VTODO'.$end;
    }

    return $outString;
}

/**
 * Build a VFREEBUSY response ical-file
 *
 * @param array $data       BUSY-Times
 * @param array $params     Additional Parameters to include in the ical-file
 * @return String           Content of the ical-file
 */
function get_vfreebusy_ical($data, $params = null) {
/*
BEGIN:VFREEBUSY
DTSTAMP:20050125T090000Z
DTSTART:20040902T090000Z
DTEND:20040902T170000Z
FREEBUSY:20050531T230000Z/20050601T010000Z
END:VFREEBUSY 
END:VCALENDAR
*/
    $version  = "2.0";
	
	$nl = chr(13).chr(10);
	$outString  = 'BEGIN:VCALENDAR'.$nl
                . 'PRODID:-//PHProject//PHProject 5.3//EN'.$nl
                . 'VERSION:'.$version.$nl
                . 'METHOD:REPLY'.$nl
	            . 'BEGIN:VFREEBUSY'.$nl
	            . 'DTSTAMP:'.date('Ymd\THis\Z').$nl;
	foreach ($params as $name => $value) {
		if ($value == null) continue;
		$outString .= $name.':'.$value.$nl;
	}
    foreach ($data as $termin) {
    	$outString .= 'FREEBUSY;FBTYPE=BUSY:'.get_utc_date($termin['datum'], $termin['anfang']).'/'.get_utc_date($termin['datum'], $termin['ende']).$nl;
    }
    $outString .= 'END:VFREEBUSY'.$nl
                . 'END:VCALENDAR'.$nl;
    return $outString;
}

?>