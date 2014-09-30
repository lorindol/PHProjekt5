<?php
/**
 * Import a ical calendar file to phprojekt
 *
 * @package    calendar
 * @subpackage main
 * @author     Gustavo Solt , $Author: gustavo $
 * @licence    GPL, see www.gnu.org/copyleft/gpl.html
 * @copyright  2000-2006 Mayflower GmbH www.mayflower.de
 * @version    $Id: 
 */

define('PATH_PRE','../');
include_once(PATH_PRE.'lib/lib.inc.php');
require_once(LIB_PATH.'/dbman_lib.inc.php');
include_once('import_ical.php');

// check form token
check_csrftoken();

if (!is_file($_FILES['userfile']['tmp_name'])) {
    die("<html><body><div id=\"global-main\">".__('Please upload a correct file')."</body></html>");
} else {
    $filename = $_FILES['userfile']['tmp_name']; 
    $fileoriginalname = stripslashes($_FILES['userfile']['name']); 
    if (!strstr($fileoriginalname,'.ics')) {
        die("<html><body><div id=\"global-main\">".__('Please upload a correct calendar file')."</body></html>");
    }
}

$event_data = parse_ical($filename);
if (is_array($event_data)) {
    foreach ($event_data as $tmp => $content) {
        $datum      = date("Y-m-d",$content['StartTime']);
        $anfang     = date("Hi",$content['StartTime']);
        $datum_ende = date("Y-m-d",$content['EndTime']);
        $ende       = date("Hi",$content['EndTime']);
        $event      = addslashes(xss($content['Summary']));
        $remark     = addslashes(xss($content['Description']));

        $data['event']  = $event;
        $data['remark'] = $remark;
        $data['datum']  = $datum;
        $data['anfang'] = $anfang;
        $data['ende']   = $ende;

        // Same day
        if ($datum == $datum_ende) {
            insert_imported_data($data);
        } else {
            $difference = $content['EndTime'] - $content['StartTime'];
            $diffday = round($difference/86400);

            // Insert only one time the events that finish on 0000
            if ($diffday > 0 && $data['ende'] == '0000') {
                $data['ende']   = '2359';
                insert_imported_data($data);
            } else {
                for($i=0;$i<=$diffday;$i++) {
                    $day = (86400 * $i) + $content['StartTime'];
                    $data['datum']  = date("Y-m-d",$day);
                    $databis = $data;
                    if ($data['datum'] != $datum) {
                        $databis['anfang'] = '0000';
                    }
                    if ($data['datum'] != $datum_ende) {
                        $databis['ende']   = '2359';
                    }
                    insert_imported_data($databis);
                }
            }
        }
    }
}

// Back to the calendar page
$query_str = PATH_PRE."calendar/calendar.php?mode=view";
header('Location: '.$query_str);

function insert_imported_data($data) {
    global $user_ID;
    $values = "(0,".(int)$user_ID.",".(int)$user_ID.",'".$data['event']."','".$data['remark']."',0,
                    '".$data['datum']."','".$data['anfang']."','".$data['ende']."',
                    0,NULL,NULL,NULL,0,NULL,0,2,
                    0,'".$dbTSnull."','".$dbTSnull."',NULL,0)";
    $query = "INSERT INTO ".DB_PREFIX."termine
                    (parent, von, an, event, remark, projekt,
                    datum, anfang, ende,
                    serie_id, serie_typ, serie_bis, ort, contact, remind, visi, partstat,
                    priority, sync1, sync2, upload, status)
                    VALUES ".$values;
    $result = db_query($query) or db_die();
}
?> 
