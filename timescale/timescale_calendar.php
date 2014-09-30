<?php
/**
 * @package    misc
 * @subpackage timescale
 * @author     Gustavo Solt, $Author: gustavo $
 * @licence    GPL, see www.gnu.org/copyleft/gpl.html
 * @copyright  2000-2006 Mayflower GmbH www.mayflower.de
 * @version    $Id: timescale_calendar.php
 */

// include function
include_once ('timescale.inc.php');
include_once ('timescale.class.php');
    
$module = 'Timescale';

echo set_page_header();

echo "
<style>
#timescale td {
    border:0px solid #000000;
    border-top-width: 1px;
    border-left-width: 1px;
    font-family: Arial, Helvetica, sans-serif;
    font-size: 8pt;
}
#timescale table {
    border:0px solid #000000;
    border-bottom-width: 1px;
    border-right-width: 1px;
}
</style>
";

global $date_format_object;

$userlist       = (isset($_POST['userlist']))    ? xss_array($_POST['userlist']) : array();
if ($userlist[0] == 'gesamt') {
    $userlist  = array();
  	// fetch all users from this group
    $result = db_query("SELECT ".DB_PREFIX."users.ID, nachname, vorname, kurz
                          FROM ".DB_PREFIX."users, ".DB_PREFIX."grup_user
                         WHERE ".DB_PREFIX."users.ID = user_ID
                           AND grup_ID = ".(int)$user_group."
                      ORDER BY nachname") or db_die();
    while ($row = db_fetch_row($result)) {
        $userlist[] = $row[0];
    }
}
$period_select  = (isset($_POST['period_select'])) ? xss($_POST['period_select']) : '';
$periodtype     = (isset($_POST['periodtype'])) ? xss($_POST['periodtype']) : '';
if (($period_select != '') && ($periodtype)) {
    $pieces = periode_parse_selectbox_value($period_select);
	$anfang = date("Y-m-d", periode_get_start_date($pieces["type"], $pieces["cycles"]));
	$ende 	= date("Y-m-d", periode_get_end_date($pieces["type"], $pieces["cycles"]));
} else {
    $anfang = (isset($_POST['anfang'])) ? xss($_POST['anfang']) : '';
    $ende   = (isset($_POST['ende'])) ? xss($_POST['ende']) : '';
}
if (!empty($anfang)) {
    global $settings;
    $holidays = new SpecialDays((array) $settings['cal_hol_file']);

    $data = array();
    $start = $date_format_object->convert_user2db($anfang);
    $end   = $date_format_object->convert_user2db($ende);

    $colors = array('0' => '#E7E7E7',
                    '1' => '#E7E7E7',
                    '2' => '#E7E7E7');

    $result = db_query("SELECT ID, von, event, datum
                          FROM ".DB_PREFIX."termine
                         WHERE (datum <= '".$end."' AND datum >= '".$start."')
                           AND serie_id = 0
                           AND is_deleted is NULL
                         ORDER BY von,datum ASC") or db_die();

    $count = 0;
    while($row = db_fetch_row($result)) {
        if (!isset($user)) {
            $user = $row[1];
            $name = slookup('users', 'nachname, vorname', 'ID', $row[1]);
            $color = $colors[$count%3];
        } else {
            if ($user != $row[1]) {
                $count++;
                $user = $row[1];
                $name = slookup('users', 'vorname', 'ID', $row[1]);
                $color = $colors[$count%3];
            }
        }
        
        if (!in_array($user,$userlist)) {
            continue;
        }

        $add = true;
        $pos = count($data);
        foreach($data as $tmp_pos => $content) {
            if (($content['user'] == $user)) {
                if (check_dates($content['data'],$row[3],$row[3])) {
                    $add = false;
                    $pos = $tmp_pos;
                    break;
                }
            }
        }
        if ($add) {
            $data[$pos] = array('titles' => array('0' => array ('title'       => 'User',
                                                                'title_value' => $name,
                                                                'title_link'  => PATH_PRE.'contacts/members.php?mode=forms&amp;ID='.$row[1]),
                                                  '1' => array ('title'       => 'User',
                                                                'title_value' => $name,
                                                                'title_link'  => PATH_PRE.'contacts/members.php?mode=forms&amp;ID='.$row[1])),
                                'user'   => $user,
                                'data'   => array());
        }
        array_push($data[$pos]['data'],array('text'       => $row[2],
                                             'text_link'  => PATH_PRE.'calendar/calendar.php?mode=forms&amp;ID='.$row[0],
                                             'start'      => $row[3],
                                             'end'        => $row[3],
                                             'style'      => 'color:black; font-weight:bold; text-decoration:none; font-size:10px;',
                                             'td_color'   => $color));
        
        // Sub events
        $subresult = db_query("SELECT ID, event, datum
                                 FROM ".DB_PREFIX."termine
                                WHERE (datum <= '".$end."' AND datum >= '".$start."')
                                  AND serie_id = ".(int)$row[0]."
                                ORDER BY von,datum ASC") or db_die();
        while($subrow = db_fetch_row($subresult)) {
            list($pyear,$pmonth,$pday) = split("-",$subrow[2]);
            $previus_date = date('Y-m-d',mktime(0,0,0,$pmonth,$pday-1,$pyear));
            $eventpos = search_pos($data[$pos]['data'],'end',$previus_date,'text',$subrow[1]);
            if ($eventpos >= 0) {
                $data[$pos]['data'][$eventpos]['end'] = $subrow[2];
            } else {
                array_push($data[$pos]['data'],array('text'       => $subrow[1],
                                                     'text_link'  => PATH_PRE.'calendar/calendar.php?mode=forms&amp;ID='.$event_row[0],
                                                     'start'      => $subrow[2],
                                                     'end'        => $subrow[2],
                                                     'style'      => 'color:black; font-weight:bold; text-decoration:none; font-size:10px;',
                                                     'td_color'   => $color));
            }
        }
    }

    // Sort Users
    $new_data = array();
    foreach($userlist as $num => $users_ID) {
        foreach ($data as $tmp => $array) {
            if ($array['user'] == $users_ID) {
                $new_data[$tmp] = $array;
            }
        }
    }

    $table = new Create_table($start,$end,$holidays);
    $output .= $table->show($new_data);
}

$output .= draw_timescale_save_setting_button();

echo $output;

echo '
</body>
</html>
';
?>
