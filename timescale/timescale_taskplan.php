<?php
/**
 * @package    misc
 * @subpackage timescale
 * @author     Gustavo Solt, $Author: gustavo $
 * @licence    GPL, see www.gnu.org/copyleft/gpl.html
 * @copyright  2000-2006 Mayflower GmbH www.mayflower.de
 * @version    $Id: timescale_taskplan.php
 */

// include function
include_once ('timescale.inc.php');
include_once ('timescale.class.php');
require_once (LIB_PATH.'/dbman_lib.inc.php');

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
$projectlist    = (isset($_POST['projectlist']))    ? xss_array($_POST['projectlist'])  : array();
$nonuser        = (isset($_POST['nonuser']))        ? xss($_POST['nonuser'])            : '';
$fields         = (isset($_POST['fields']))         ? xss_array($_POST['fields'])       : array();
$direction      = (isset($_POST['direction']))      ? xss($_POST['direction'])          : 'desc';
$sort_field     = (isset($_POST['sort_field']))     ? xss($_POST['sort_field'])         : 'anfang';

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

if (!in_array($sort_field,$fields)&&(!empty($sort_field))) {
    $fields[] = $sort_field;
}

if (!empty($anfang)) {
    global $settings;
    $holidays = new SpecialDays((array) $settings['cal_hol_file']);

    $start = $date_format_object->convert_user2db($anfang);
    $end   = $date_format_object->convert_user2db($ende);

    $data         = array();
    $project_data = get_project_data2($projectlist,$sort_field,$direction,$data);
    $query_text = "SELECT db_name, form_name
                     FROM ".DB_PREFIX."db_manager
                    WHERE db_table LIKE 'projekte'
                      AND db_inactive <> 1
                 ORDER BY form_pos, ID";
    $result_text = db_query($query_text) or db_die();
    while ($row_text = db_fetch_row($result_text)) {
        $text[$row_text[0]] = enable_vars($row_text[1]);
    }
    $text['costcentre_id'] = __('Costcentre');
    $text['costunit_id']   = __('Costunit');
    $text['aufwand_gebucht']   = 'bereits gebuchter Aufwand';
    $text['ID']   = 'ID';
    $todo_data=get_todos($nonuser,$projectlist,$end,$start);

    foreach ($project_data as $project => $project_array){

        $user = '';
        $display = '&nbsp; &nbsp; '.$project_array['name'];
        $link_display = PATH_PRE.'projects/projects.php?mode=forms&amp;ID='.$project.'&amp;justform=1';
        $secondtype = 'P';
        $pos = count($data);

        $data[$pos] = array('project'    => $project,
        'type'       => $type,
        'secondtype' => $secondtype,
        'user'       => $user,
        'alt_text'	 => "ID: ".$project.", ".__('Title').": ".$project_array['name']."<br /> ".__('Priority').": ".$project_array['wichtung'].", ".__('Status').": ".$project_array['status'].", ".__('Budget').": ".$project_array['budget']."<br /> ".__('Begin').": ".$project_array['anfang'].", ".__('End').": ".$project_array['ende']."<br /> ".__('Note').": ".str_replace("\r\n","<br />", $project_array['note']),
        'titles'     =>
        array('0' => array ('title'         =>$text['name'],
        'title_value'   => $project_array['name'],
        'title_link'    => ''),
        '1' => array ('title'         =>$text['name'],
        'title_value'   => $display,
        'title_link'    => $link_display)),
        'empty_field_link' => PATH_PRE.'todo/todo.php?mode=forms&amp;new_note=1&amp;ext='.$user.'&amp;projekt_ID='.$project.'&amp;justform=1&amp;anfang=--anfang--&amp;deadline=--deadline--',
        'data'       => array());

        $fields_array= build_array('projekte',$project,'forms');
        foreach($fields as $field) {
            array_push($data[$pos]['titles'],array ('title'         => $text[$field],
            'title_value'   => $project_array[$field],
            'title_internal'=> $project_array[$field],
            'title_type'=> $fields_array[$field]['form_type'],
            'title_link'    => ''));
        }

        $style = "text-decoration:none; background-color:#FFFFFF; font-size:10px;";
        if (is_array($todo_data[$project]))  {
            foreach($todo_data[$project] as $todo_ID => $todo){
                // New insert
                $add = true;
                $pos = count($data);
                foreach($data as $tmp_pos => $content) {
                    if ($content['project'] == $project) {
                        if (check_dates($content['data'],$todo['start'],$todo['end'])) {
                            $add = false;
                            $pos = $tmp_pos;
                            break;
                        }
                    }
                }
                if($add){
                    $user = '';
                    $display = '&nbsp; &nbsp; '.$project_array['name'];
                    $link_display = PATH_PRE.'projects/projects.php?mode=forms&amp;ID='.$project.'&amp;justform=1';
                    $secondtype = 'P';
                    $pos = count($data);

                    $data[$pos] = array('project'    => $project,
                    'type'       => $type,
                    'secondtype' => $secondtype,
                    'user'       => $user,
                    'alt_text'	 => "ID: ".$project.", ".__('Title').": ".$project_array['name']."<br /> ".__('Priority').": ".$project_array['wichtung'].", ".__('Status').": ".$project_array['status'].", ".__('Budget').": ".$project_array['budget']."<br /> ".__('Begin').": ".$project_array['anfang'].", ".__('End').": ".$project_array['ende']."<br /> ".__('Note').": ".str_replace("\r\n","<br />", $project_array['note']),
                    'titles'     =>
                    array('0' => array ('title'         =>$text['name'],
                    'title_value'   => $project_array['name'],
                    'title_link'    => ''),
                    '1' => array ('title'         =>$text['name'],
                    'title_value'   => $display,
                    'title_link'    => $link_display)),
                    'empty_field_link' => PATH_PRE.'todo/todo.php?mode=forms&amp;new_note=1&amp;ext='.$user.'&amp;projekt_ID='.$project.'&amp;justform=1&amp;anfang=--anfang--&amp;deadline=--deadline--',
                    'data'       => array());

                    $fields_array= build_array('projekte',$project,'forms');
                    foreach($fields as $field) {
                        array_push($data[$pos]['titles'],array ('title'         => $text[$field],
                        'title_value'   => '',
                        'title_internal'=> '',
                        'title_type'=> $fields_array[$field]['form_type'],
                        'title_link'    => ''));
                    }
                }
                
                array_push($data[$pos]['data'],array('text'       => $todo['name'],
                'text_link'  => PATH_PRE.'todo/todo.php?mode=forms&amp;ID='.$todo_ID.'&amp;justform=1',
                'text_alt'   => ''.__('Title').': '.$todo['name'].', '.__('Project').': '.$project_array['name'].'<br /> '.__('Priority').': '.$todo['prio'].', '.__('Budget').': '.$todo['budget'].'<br />'.__('Begin').': '.$todo['start'].', '.__('End').': '.$todo['ende'].'<br />'.__('User').': '.$todo['user'].'<br />'.__('Note').':'.$todo['note'].'',
                'start'      => $todo['start'],
                'end'        => $todo['end'],
                'style'      => $style,
                'td_color'   => '#336699'));
            }
        }
    }

    // Sort projects
    if ($sort_field == '0' && $projectlist[0] != 'gesamt') {
        $new_data = array();
        foreach($projectlist as $num => $project_ID) {
            foreach ($data as $tmp => $array) {
                if ($array['project'] == $project_ID) {
                    $new_data[$tmp] = $array;
                }
            }
        }
        $data = $new_data;
    }

    $table = new Create_table($start,$end,$holidays);
    $output .= $table->show($data);
}

$output .= draw_timescale_save_setting_button();

echo $output;

echo '
</body>
</html>
';


function sort_array_by_field($sort_field,$direction,$data,$text) {

    $getpos = 0;
    $new_data = array();
    $found_positions = array();
    for($tmp = 0 ; $tmp < count($data) ; $tmp++) {
        $array = $data[$tmp];
        $getpos = get_max($sort_field,$direction,$data,$text,$found_positions);
        array_push($found_positions,$getpos);
        $new_data[$tmp] = $data[$getpos];
    }
    return $new_data;
}

function get_max($sort_field,$direction,$data,$text,$found_positions) {

    $getpos  = -1;
    $found   = 0;
    $keeppos = 0;
    for($tmp = 0 ; $tmp < count($data) ; $tmp++) {
        $array = $data[$tmp];
        foreach($array['titles'] as $pos => $titles) {
            $keeppos = $pos;
            if ($titles['title'] == $text[$sort_field]) {
                if ($getpos == -1) {
                    $value = $titles['title_internal'];
                    $getpos = $tmp;
                } else {
                    if ($direction == "asc") {
                        $check = ($titles['title_internal'] < $value) ? 1 : 0;
                    } else {
                        $check = ($titles['title_internal'] > $value) ? 1 : 0;
                    }

                    if (($check)&&(!in_array($tmp,$found_positions))) {
                        $value = $titles['title_internal'];
                        $getpos = $tmp;
                        $found = 1;
                    }
                }
            }
        }
    }

    if (!$found) {
        for($i=0;$i<count($data);$i++) {
            if (!in_array($i,$found_positions)) {
                $value = $data[$i]['titles'][$keeppos]['title_internal'];
                $getpos = $i;
                break;
            }
        }
    }

    return $getpos;
}

function get_todos($nonuser,$projectlist,$end,$start){
    $result = db_query("SELECT ID, remark, note, project, anfang, deadline, ext, priority
                          FROM ".DB_PREFIX."todo
                         WHERE (deadline <= '".$end."' AND anfang >= '".$start."')
                           AND is_deleted is NULL
                         ORDER BY project,von,anfang ASC") or db_die();
    $todo_data=array();

    while($row = db_fetch_row($result)) {
        if ($projectlist[0] != 'gesamt') {
            if (!in_array($row[3],$projectlist)) {
                continue;
            }
        }

        if ($nonuser == 'on') {
            if (!empty($row[6])) {
                continue;
            }
        }

        $display = '&nbsp; &nbsp; '.slookup('projekte', 'name', 'ID', $row[3]);
        $link_display = PATH_PRE.'projects/projects.php?mode=forms&amp;ID='.$row[3].'&amp;justform=1';
        $secondtype = 'P';
        $note = str_replace("\r\n","<br />", $row[2]);
        $name = $row[1];
        $type = 'WOP';
        $todo_data[$row[3]][$row[0]] = array(
            'user'          => slookup('users', 'nachname, vorname', 'ID', $row[6]),
            'display'       => $display,
            'link_display'  => $link_display,
            'secondtype'    => $secondtype,
            'name'          => $name,
            'start'         => $row[4],
            'end'           => $row[5],
            'prio'          => $row[7],
            'note'          => $note,
            'budget'        => $row[3],
            'type'          => $type);
    }
    return $todo_data;
}

?>
