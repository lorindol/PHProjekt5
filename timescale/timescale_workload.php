<?php
/**
 * @package    misc
 * @subpackage timescale
 * @author     Gustavo Solt, $Author: gustavo $
 * @licence    GPL, see www.gnu.org/copyleft/gpl.html
 * @copyright  2000-2006 Mayflower GmbH www.mayflower.de
 * @version    $Id: timescale_workload.class.php
 */

define('TIMESCALE_WORKLOAD_LIMIT_GREEN','85');
define('TIMESCALE_WORKLOAD_LIMIT_YELLOW','105'); 

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
    $anfang = (isset($_REQUEST['anfang']))     ? xss($_REQUEST['anfang'])         : '';
    $ende   = (isset($_REQUEST['ende']))       ? xss($_REQUEST['ende'])           : '';
}
if (isset($_POST['userlist'])) {
    $userlist = xss_array($_POST['userlist']);
} else {
    if (isset($_GET['userlist'])) {
        $userlist = explode('_',xss($_GET['userlist']));
    } else {
        $userlist = array();
    }
}
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

if (!empty($anfang)) {
    global $settings;
    $holidays = new SpecialDays((array) $settings['cal_hol_file']);

    $data = array();
    $start = $date_format_object->convert_user2db($anfang);
    $end   = $date_format_object->convert_user2db($ende);

    $result = db_query("SELECT p.ID as project_id, p.name as project_name,
                               p.anfang as project_anfang, p.ende as project_ende,
                               pur.user_ID as user, pur.user_unit as unit
                         FROM ".DB_PREFIX."projekte as p
                         LEFT JOIN ".DB_PREFIX."project_users_rel as pur ON p.ID = pur.project_ID
                         WHERE (p.ende <= '".$end."' AND p.anfang >= '".$start."')
                           AND p.is_deleted is NULL
                           AND pur.is_deleted is NULL
                         ORDER BY pur.user_ID, p.anfang ASC") or db_die();

    $data = array();
    $count = 0;
    $skip  = 0;
    $new_user = 0;
    $alldates = array();

    while($row = db_fetch_row($result)) {
        $assigned_user = $row[4];
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
            'empty_title_link'     => '',
            'titles'     =>
            array(
            '0' => array (  'title'         => 'User',
            'title_value'   => $name,
            'title_link'    => ''),
            '1' => array (  'title'         => 'User',
            'title_value'   => "<b>".$name."</b>",
            'title_link'    => PATH_PRE.'contacts/members.php?mode=forms&amp;ID='.$user)),
            'empty_field_link' => '',
            'data'       => array());
        } else {
            $user = $data[$user_pos]['user'];
            $name = $data[$user_pos]['titles'][0]['title_value'];
            $new_user = 0;
        }

        // New insert
        $type = 'WL';
        $add = true;
        $pos = count($data);
        $project = $row[0];
        $projectname = $row[1];
        $unit = $row[5];

        $alldates[$user] = collect_unit_per_day($row[2],$row[3],$unit,$alldates[$user]);
        
        if ($add) {
            $data[$pos] = array('project'    => $project,
            'user'       => $user,
            'type'       => $type,
            'empty_title_link'     => '',
            'alt_text'	 => '',
            'titles'     =>
            array(  '0' => array (  'title'         => 'User',
            'title_value'   => $name,
            'title_link'    => ''),
            '1' => array (  'title'         => 'User',
            'title_value'   => $projectname,
            'title_link'    => PATH_PRE.'projects/projects.php?mode=forms&amp;ID='.$project.'&amp;justform=1')),
            'empty_field_link' => '',
            'data'       => array());
        }
        $style = "text-decoration:none; background-color:#FFFFFF; font-size:10px;";

        array_push($data[$pos]['data'],array('text'       => $unit.'%',
        'text_link'  => PATH_PRE.'projects/projects.php?mode=forms&amp;ID='.$project.'&amp;justform=1',
        'text_alt'   => '',
        'start'      => $row[2],
        'end'        => $row[3],
        'style'      => $style,
        'td_color'   => '#C3C3C3'));
    }

    // Draw the sum of porcents
    foreach ($alldates as $user => $dates_array) {
        // New line
        $newpos = count($data);
        $data[$newpos] = array('project'    => '',
            'user'       => $user,
            'type'       => 'V',
            'empty_title_link'     => '',
            'alt_text'	 => '',
            'titles'     =>
            array(  '0' => array (  'title'         => 'User',
            'title_value'   => $user,
            'title_link'    => ''),
            '1' => array (  'title'         => 'User',
            'title_value'   => '',
            'title_link'    => '')),
            'empty_field_link' => '',
            'data'       => array());

        $pos = search_pos($data,'user',$user);
        $style = "text-decoration:none; background-color:#FFFFFF; font-size:10px;";

        $allstart = null;
        $endstart = null;
        $allunit  = 0;
        foreach ($dates_array as $date => $unit) {
            if ($allunit == 0) {
                $allstart = $date;
                $allunit = $unit;
            } else {
                if ($allunit <= TIMESCALE_WORKLOAD_LIMIT_GREEN) {
                    $color = '#00FF00';
                } else if ($allunit <= TIMESCALE_WORKLOAD_LIMIT_YELLOW) {
                    $color = '#FFFF00';
                } else {
                    $color = '#FF0000';
                }
                // Draw the previus porcent
                if ($allunit != $unit) {
                    $allend = get_date_before($date);
                    array_push($data[$pos]['data'],array('text'       => $allunit.'%',
                    'text_link'  => '',
                    'text_alt'   => '',
                    'start'      => $allstart,
                    'end'        => $allend,
                    'style'      => $style,
                    'td_color'   => $color));

                    $allunit = $unit;
                    $allstart = $date;
                }
            }
            $last_date = $date;
        }
        
        if ($allunit <= TIMESCALE_WORKLOAD_LIMIT_GREEN) {
            $color = '#00FF00';
        } else if ($allunit <= TIMESCALE_WORKLOAD_LIMIT_YELLOW) {
            $color = '#FFFF00';
        } else {
            $color = '#FF0000';
        }
        // Draw the last porcent
        $allunit = $dates_array[$last_date];
        $allend = $last_date;
        array_push($data[$pos]['data'],array('text'       => $allunit.'%',
        'text_link'  => '',
        'text_alt'   => '',
        'start'      => $allstart,
        'end'        => $allend,
        'style'      => $style,
        'td_color'   => $color));
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
