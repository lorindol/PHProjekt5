<?php
/**
 * @package    misc
 * @subpackage timescale
 * @author     Gustavo Solt, $Author: gustavo $
 * @licence    GPL, see www.gnu.org/copyleft/gpl.html
 * @copyright  2000-2006 Mayflower GmbH www.mayflower.de
 * @version    $Id: timescale_capacityplan.class.php
 */

// include function
include_once ('timescale.inc.php');
include_once ('timescale.class.php');
require_once (LIB_PATH.'/dbman_lib.inc.php');


// Config
// 0 => just periods taken from 'urlaub'/#vacation' is used
// 1 => all entries from table 'vacations' are displayed
define('TIMESCALE_ABSENT','1');

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

global $date_format_object, $user_group;

$period_select  = (isset($_POST['period_select']))  ? xss($_POST['period_select'])  : '';
$periodtype     = (isset($_POST['periodtype']))     ? xss($_POST['periodtype'])     : '';
if (($period_select != '') && ($periodtype)) {
    $pieces = periode_parse_selectbox_value($period_select);
    $anfang = date("Y-m-d", periode_get_start_date($pieces["type"], $pieces["cycles"]));
    $ende 	= date("Y-m-d", periode_get_end_date($pieces["type"], $pieces["cycles"]));
} else {
    $anfang = (isset($_POST['anfang']))     ? xss($_POST['anfang'])         : '';
    $ende   = (isset($_POST['ende']))       ? xss($_POST['ende'])           : '';
}
$userlist  = (isset($_POST['userlist']))    ? xss_array($_POST['userlist']) : array();
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
$fields    = (isset($_POST['fields']))         ? xss_array($_POST['fields'])       : array();
if ($fields[0] == 'gesamt') {
    $fields = array();
    $query = "SELECT db_name, form_name
                FROM ".DB_PREFIX."db_manager
               WHERE db_table LIKE 'projekte'
                 AND db_inactive <> 1
                 AND db_name <> 'name'
            ORDER BY form_pos, ID";
    $result = db_query($query) or db_die();
    while ($row = db_fetch_row($result)) {
        $fields[] = $row[0];
    }
    $fields[] = 'costcentre_id';
    $fields[] = 'costunit_id';
    $fields[] = 'aufwand_gebucht';
    $fields[] = 'ID';
}

$query_text = "SELECT db_name, form_name
                     FROM ".DB_PREFIX."db_manager
                    WHERE db_table LIKE 'projekte'
                      AND db_inactive <> 1
                      AND db_name <> 'name'
                 ORDER BY form_pos, ID";
$result_text = db_query($query_text) or db_die();
while ($row_text = db_fetch_row($result_text)) {
    $text[$row_text[0]] = enable_vars($row_text[1]);
}
$text['costcentre_id'] = __('Costcentre');
$text['costunit_id']   = __('Costunit');
$text['aufwand_gebucht']   = 'bereits gebuchter Aufwand';
$text['ID']   = 'ID';


if (!in_array($sort_field,$fields)&&(!empty($sort_field))) {
    $fields[] = $sort_field;
}

if (!empty($anfang)) {
    global $settings;
    $holidays = new SpecialDays((array) $settings['cal_hol_file']);

    $data = array();
    $start = $date_format_object->convert_user2db($anfang);
    $end   = $date_format_object->convert_user2db($ende);

    $colour_array = array(0  => '#FFFFFF',
    1  => '#CCCCCC',
    2  => '#336699',
    3  => '#FFFF00',
    4  => '#009900',
    5  => '#CC3333',
    6  => '#FF6666',
    7  => '#CC0099',
    8  => '#33FF99',
    9  => '#CCCC66',
    10 => '#CCFFCC',
    11 => '#FFCCCC');

    $result = db_query("SELECT ID, remark, note, project, anfang, deadline, phase, ext, priority
                          FROM ".DB_PREFIX."todo
                         WHERE (deadline <= '".$end."' AND anfang >= '".$start."')
                           AND is_deleted is NULL
                         ORDER BY von,project,anfang ASC") or db_die();

    $data = array();
    $count = 0;
    $skip  = 0;
    $new_user = 0;
    $remaining_holiday_last_year    = 0;
    $official_vacation_current_year = 0;
    $total_holiday_current_year     = 0;
    $used_holiday_current_year      = 0;
    $remaining_holidays             = 0;
    while($row = db_fetch_row($result)) {
        $assigned_user = $row[7];
        $user_pos = search_pos($data,'user',$assigned_user);
        if ($user_pos == '-1') {
            $user = $assigned_user;
            // Skip user that are not selected
            if (!in_array($user,$userlist)) {
                continue;
            }

            $name = slookup('users', 'nachname, vorname', 'ID', $user);
            $new_user = 1;

            $pos = count($data);
            $data[$pos] = array('project'    => '',
            'user'       => $user,
            'type'       => 'U',
            'empty_title_link'     => PATH_PRE.'todo/todo.php?mode=forms&amp;new_note=1&amp;ext='.$user.'&amp;justform=1',
            'titles'     =>
            array(
            '0' => array (  'title'         => 'User',
            'title_value'   => $name,
            'title_link'    => ''),
            '1' => array (  'title'         => 'User/Project',
            'title_value'   => "<b>".$name."</b>",
            'title_link'    => PATH_PRE.'contacts/members.php?mode=forms&amp;ID='.$user)),
            'empty_field_link' => PATH_PRE.'todo/todo.php?mode=forms&amp;new_note=1&amp;ext='.$user.'&amp;projekt_ID='.$project.'&amp;justform=1&amp;anfang=--anfang--&amp;deadline=--deadline--',
            'data'       => array());

            $fields_array= build_array('projekte',$project,'forms');
            foreach($fields as $field) {
                array_push($data[$pos]['titles'],array ('title'         => $text[$field],
                'title_value'   => '',
                'title_internal'=>  '',
                'title_type'=>  '',
                'title_link'    => ''));
            }
        } else {
            $user = $data[$user_pos]['user'];
            $name = $data[$user_pos]['titles'][0]['title_value'];
            $new_user = 0;
        }
        $project = $row[3];
        $proj = db_query("SELECT name,wichtung,status,budget,anfang,ziel,note FROM ".DB_PREFIX."projekte where ID='".$row[3]."'");
        $pdata = db_fetch_row( $proj );
        $projectname = $pdata[0];
        $project_data = get_project_data($project);

        $event_pos = search_pos($data,'user',$user,'project',$project);
        if ($event_pos == '-1') {
            $event_result = db_query("SELECT ID, event, datum
                                        FROM ".DB_PREFIX."termine
                                       WHERE (datum <= '".$end."' AND datum >= '".$start."')
                                         AND serie_id = 0
                                         AND projekt = ".(int)$project."
                                         AND von = ".(int)$user."
                                         AND is_deleted is NULL
                                       ORDER BY von,datum ASC") or db_die();

            while($event_row = db_fetch_row($event_result)) {
                $pos = count($data);
                $data[$pos] = array('project'    => $project,
                'user'       => $user,
                'type'       => 'C',
                'empty_title_link'     => PATH_PRE.'todo/todo.php?mode=forms&amp;new_note=1&amp;ext='.$user.'&amp;justform=1',
                'titles'     =>
                array( '0' => array (  'title'         => 'User',
                'title_value'   => $name,
                'title_link'    => ''),
                '1' => array (  'title'         => 'User/Project',
                'title_value'   => $projectname,
                'title_link'    => PATH_PRE.'projects/projects.php?mode=forms&amp;ID='.$project.'&amp;justform=1')),
                'empty_field_link' => PATH_PRE.'todo/todo.php?mode=forms&amp;new_note=1&amp;ext='.$user.'&amp;projekt_ID='.$project.'&amp;justform=1&amp;anfang=--anfang--&amp;deadline=--deadline--',
                'data'       => array());
                array_push($data[$pos]['data'],array('text'       => $event_row[1],
                'text_link'  => PATH_PRE.'calendar/calendar.php?mode=forms&amp;justform=1&amp;ID='.$event_row[0],
                'start'      => $event_row[2],
                'end'        => $event_row[2],
                'style'      => 'text-decoration:none; background-color:#FFFFFF; font-size:10px;',
                'td_color'   => 'green'));
                $fields_array= build_array('projekte',$project,'forms');

                foreach($fields as $field) {
                    array_push($data[$pos]['titles'],array ('title'         => $text[$field],
                    'title_value'   => $project_data[$field],
                    'title_internal'=> $project_data[$field],
                    'title_type'=> $fields_array[$field]['form_type'],
                    'title_link'    => ''));
                }
                // Sub events
                $subresult = db_query("SELECT ID, event, datum
                                         FROM ".DB_PREFIX."termine
                                        WHERE (datum <= '".$end."' AND datum >= '".$start."')
                                          AND serie_id = ".(int)$event_row[0]."
                                          AND is_deleted is NULL
                                     ORDER BY von,datum ASC") or db_die();
                while($subrow = db_fetch_row($subresult)) {
                    list($pyear,$pmonth,$pday) = split("-",$subrow[2]);
                    $previus_date = date('Y-m-d',mktime(0,0,0,$pmonth,$pday-1,$pyear));
                    $eventpos = search_pos($data[$pos]['data'],'end',$previus_date,'text',$subrow[1]);
                    if ($eventpos >= 0) {
                        $data[$pos]['data'][$eventpos]['end'] = $subrow[2];
                    } else {
                        array_push($data[$pos]['data'],array('text'       => $subrow[1],
                        'text_link'  => PATH_PRE.'calendar/calendar.php?mode=forms&amp;justform=1&amp;ID='.$event_row[0],
                        'start'      => $subrow[2],
                        'end'        => $subrow[2],
                        'style'      => 'text-decoration:none; background-color:#FFFFFF; font-size:10px;',
                        'td_color'   => 'green'));
                    }
                }
            }
        }

        // Phase
        // New insert
        if ($row[6] > 0) {
            $type = 'WP';
        } else {
            $type = 'WOP';
        }
        $add = true;
        $pos = count($data);
        foreach($data as $tmp_pos => $content) {
            if (($content['user'] == $user)&&($content['project'] == $project)) {
                if (check_dates($content['data'],$row[4],$row[5])) {
                    $add = false;
                    $pos = $tmp_pos;
                    break;
                }
            }
        }

        if ($add) {
            $data[$pos] = array('project'    => $project,
            'user'       => $user,
            'type'       => $type,
            'empty_title_link'     => PATH_PRE.'todo/todo.php?mode=forms&amp;new_note=1&amp;ext='.$user.'&amp;justform=1',
            'alt_text'	 => "ID: ".$project_ID.", ".__('Title').": ".$pdata[0]."<br /> ".__('Priority').": ".$pdata[1].", ".__('Status').": ".$pdata[2].", ".__('Budget').": ".$pdata[3]."<br /> ".__('Start').": ".$pdata[4].", ".__('End').": ".$pdata[5]."<br /> ".__('Note').": ".str_replace("\r\n","<br />",$pdata[6]),
            'titles'     =>
            array(  '0' => array (  'title'         => 'User',
            'title_value'   => $name,
            'title_link'    => ''),
            '1' => array (  'title'         => 'User/Project',
            'title_value'   => $projectname,
            'title_link'    => PATH_PRE.'projects/projects.php?mode=forms&amp;ID='.$project.'&amp;justform=1')),
            'empty_field_link' => PATH_PRE.'todo/todo.php?mode=forms&amp;new_note=1&amp;ext='.$user.'&amp;projekt_ID='.$project.'&amp;justform=1&amp;anfang=--anfang--&amp;deadline=--deadline--',
            'data'       => array());
            $project_data = get_project_data($project);
            $fields_array= build_array('projekte',$project,'forms');
            foreach($fields as $field) {
                array_push($data[$pos]['titles'],array ('title'         => $text[$field],
                'title_value'   => $project_data[$field],
                'title_internal'=> $project_data[$field],
                'title_type'=> $fields_array[$field]['form_type'],
                'title_link'    => ''));
            }
        }
        $style = "text-decoration:none; background-color:#FFFFFF; font-size:10px;";
        $phase_name = $row[1];
        if ($phase_name == '') {
        	$phase_name = '-';
        }
        array_push($data[$pos]['data'],array('text'       => $phase_name,
        'text_link'  => PATH_PRE.'todo/todo.php?mode=forms&amp;ID='.$row[0].'&amp;justform=1',
        'text_alt'   => ''.__('Title').': '.$phase_name.', '.__('Project').': '.$pdata[0].'<br /> '.__('Priority').': '.$row[8].', '.__('Phase').': '.$row[6].', '.__('Budget').': '.$row[3].'<br />'.__('Start').': '.$row[4].', '.__('End').': '.$row[5].'<br />'.__('Note').':'.str_replace("\r\n","<br />", $row[2]).'',
        'start'      => $row[4],
        'end'        => $row[5],
        'style'      => $style,
        'td_color'   => $colour_array[$row[6]]));
    }

    uasort($data,'sort_for_project_type');

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
