<?php
/**
 * @package    misc
 * @subpackage timescale
 * @author     Gustavo Solt, $Author: gustavo $
 * @licence    GPL, see www.gnu.org/copyleft/gpl.html
 * @copyright  2000-2006 Mayflower GmbH www.mayflower.de
 * @version    $Id: timescale.inc.php
 */

/*
 * Search in which pos are the value
 *
 * @param  array    array          - Array to search
 * @param  string   key            - Key to search
 * @param  misc     value          - Value to search
 * @param  string   second_key     - Second Key to search
 * @param  misc     second_value   - Second Value to search
 * @param  string   third_key      - Third Key to search
 * @param  misc     third_value    - Third Value to search
 * @return int      id             - Pos into the array
 */
function search_pos($array,$key,$value,$second_key = '',$second_value = '',$third_key = '',$third_value = '') {
    $pos = -1;

    foreach ($array as $tmp => $data) {
        if (($data[$key] == $value) && ($data[$second_key] == $second_value) && ($data[$third_key] == $third_value)) {
            $pos = $tmp;
            break;
        }
    }

    return $pos;
}

/*
 * Sort the array by project,type
 */
function sort_for_project_type($a, $b) {
    if ($a['user'] == $b['user']) {
        if ($a['project'] == $b['project']) {
            if ($a['type'] == $b['type']) {
                return 0;
            }
            return ($a['type'] > $b['type']) ? -1 : 1;
        }
        return ($a['project'] < $b['project']) ? -1 : 1;
    } else {
        return ($a['user'] < $b['user']) ? -1 : 1;
    }
}

/* 
 * Sort the array by user_type
 */
function sort_for_user_type($a, $b) {
    if ($a['project'] == $b['project']) {
        if ($a['user'] == $b['user']) {
            if ($a['type'] == $b['type']) {
                return 0;
            } else {
                return ($a['type'] > $b['type']) ? -1 : 1;
            }
        } else {
            return ($a['user'] < $b['user']) ? -1 : 1;
        }
    }
    return ($a['project'] < $b['project']) ? -1 : 1;
}


/*
 * Sort the array by user,secondtype
 */
function sort_for_user_secondtype($a, $b) {
    if ($a['project'] == $b['project']) {
        if ($a['secondtype'] == $b['secondtype']) {
            if ($a['user'] == $b['user']) {
                if ($a['type'] == $b['type']) {
                    return 0;
                }
                return ($a['type'] > $b['type']) ? -1 : 1;
            }
            return ($a['user'] < $b['user']) ? -1 : 1;
        }
        return ($a['secondtype'] > $b['secondtype']) ? -1 : 1;
    }
    return ($a['project'] < $b['project']) ? -1 : 1;
}

/*
 * Sort the array by project,secondtype
 */
function sort_for_project_secondtype($a, $b) {
    if ($a['project'] == $b['project']) {
        if ($a['secondtype'] == $b['secondtype']) {
            if ($a['type'] == $b['type']) {
                return 0;
            }
            return ($a['type'] > $b['type']) ? -1 : 1;
        }
        return ($a['secondtype'] > $b['secondtype']) ? -1 : 1;
    }
    return ($a['project'] < $b['project']) ? -1 : 1;
}

function draw_timescale_save_setting_button() {

    global $module, $mode, $ID, $module, $mode2;

    $post_values = xss_array($_POST);
    unset($_SESSION['saved_settings']);
    foreach($post_values as $k => $v) {
        $_SESSION['saved_settings'][$module][$k] = $v;
    }
    unset($_SESSION['saved_settings'][$module]['do']);
    unset($_SESSION['saved_settings'][$module]['action']);
    unset($_SESSION['saved_settings'][$module]['settings_apply']);
    unset($_SESSION['saved_settings'][$module]['savedSetting']);

    $output = '<br /><br />';
    $output .= get_buttons(array(array(
    'type'=>'link',
    'title' => __('This link opens a popup window'),
    'href' => "#",
    'onclick' => "manage_saved_settings('".PATH_PRE."','$module','timescale/timescale.php','$mode','$mode2','timescale.php')",
    'text'=> __('Edit and Save Settings'), 'active' => false)));

    return $output;
}

// Check between dates
function check_dates($data,$start,$end) {
    foreach($data as $pos => $content) {
        if ($start >= $content['start'] && $start <= $content['end']) {
            return false;
        } else if ($end >= $content['start'] && $end <= $content['end']) {
            return false;
        } else if ($start <= $content['start'] && $end >= $content['end']) {
            return false;
        }
    }
    return true;
}

/**
 * This function sorts the fields in the order stated by chip
 *
 * @param array $fields unsorted
 * @return array $fields sorted
 */
function sort_fields_for_chip($fields){
    $order = array();
    $order['ID'] =0;
    $order['costunit_id']=1;
    $order['costcentre_id']=2;
    $order['user_ID']=3;
    $order['aufwand_gebucht']=4;
    $order['note']=5;
    $order['kategorie']=6;
    $order['status']=7;
    $order['wichtung']=8;
    $order['chef']=9;
    $order['anfang']=10;
    $order['ende']=11;
    $i=0;
    foreach ($fields as $key => $val){
        $i++;
        $pos = $order[$val] >= 0 ? $order[$val]: count($fields)+$i;
        if($val <>'')$fields_tmp[(int)$pos]="$val";
    }
    ksort($fields_tmp);
    return $fields_tmp;
}


/*
* Recursive function for get an array with all the data of a project
* and all sub projects
* @param   int   $project_ID   - Id of the project
* @param   array $data         - Return array
* @return  array               - All the data in an array
*/
function get_project_data($project_ID = 0, $sort_field='ID', $direction='DESC') {
    // Get the project data
    global $user_ID, $user_kurz, $user_type;
    require_once (LIB_PATH.'/dbman_lib.inc.php');
    $data = array();
    // fetch parent project
    $before_where = special_sql_filter_flags('projects', $_REQUEST);
    $where = "";
    $where = special_sql_filter_flags('projects', $_REQUEST, false);
    if (isset($_REQUEST['use_filters']) && ($_REQUEST['use_filters'] == "on") ) {
        // List of fields in the db table, needed for filter
        require_once(LIB_PATH.'/dbman_lib.inc.php');
        $fields = build_array('projects', '', 'view');
        $where .= main_filter('', '', '', '', 'projects','','');
    }
    $query = "SELECT DISTINCT p.ID, name, note, kategorie, status, wichtung,
                     chef, anfang, ende,
                     costcentre_id, c.costunit_id, budget,kategorie,wichtung,user_ID
                FROM ".DB_PREFIX."projekte p
           LEFT JOIN ".DB_PREFIX."projekte_costunit c on c.projekte_ID = p.ID 
           LEFT JOIN ".DB_PREFIX."project_users_rel on p.ID = project_ID
               $before_where
               WHERE p.is_deleted is NULL
                     AND (acc LIKE 'system'
                  OR ((von = ".(int)$user_ID." OR acc LIKE 'group' OR acc LIKE '%\"$user_kurz\"%')
                     ".$where.group_string()."))";
    if ($project_ID > 0 ) {
        $query .= "AND p.ID = ".(int)$project_ID;
    }
    $query .=" GROUP BY p.ID";
    if ($sort_field != '0') {
        $query .=" ORDER BY $sort_field";
        if ($direction != '0') {
            $query .=" ".$direction;
        }
    }

    $result = db_query($query)or db_die();

    $row = db_fetch_row($result);

    // check access
    // genreal acces - either the user has direct access to it or the user has chef status
    if ($row[0] or $user_type==3) {
        // get booked time
        $booked_query="SELECT SUM(h), SUM(m)
                       FROM ".DB_PREFIX."timeproj
                       WHERE projekt= ".(int)$row[0];
        $booked_result=db_query($booked_query);
        $booked_row = db_fetch_row($booked_result);
        $hours   = (int) ($booked_row[0] + floor($booked_row[1]/60)) ;
        $minutes = (int) $booked_row[1]%60;
        $booked=floor(($booked_row[0]*60 + $booked_row[1])/3.6);
        $booked = $booked/100;

        // get values
        $fields_array= build_array('projekte',$row[0],'filter');
        $data['ID']             = $row[0];
        $data['name']           = get_correct_value($row[1],$fields_array['name']);
        $data['note']           = get_correct_value($row[2],$fields_array['note']);
        $data['kategorie']      = get_correct_value($row[3],$fields_array['kategorie']);
        $data['status']         = get_correct_value($row[4],$fields_array['status']);
        $data['wichtung']       = get_correct_value($row[5],$fields_array['wichtung']);
        $data['chef']           = get_correct_value($row[6],$fields_array['chef']);
        $data['anfang']         = get_correct_value($row[7],$fields_array['anfang']);
        $data['ende']           = get_correct_value($row[8],$fields_array['ende']);
        $data['budget']			= get_correct_value($row[11],$fields_array['budget']);
        $data['kategorie']	    = get_correct_value($row[12],$fields_array['kategorie']);
        $data['wichtung']		= get_correct_value($row[13],$fields_array['wichtung']);
        $data['user_ID']		= get_correct_value($row[14],$fields_array['user_ID'],$row[0]);
        $data_tmp = array();
        foreach ($data as $key=>$val){
            if ($key != "ID") {
                $data_tmp[$key]=$val['value'];
            } else {
                $data_tmp[$key] = $val;
            }
        }
        $data=$data_tmp;
        $data['aufwand_gebucht']= $booked;
        $data['costcentre_id']  = slookup('controlling_costcentres','name','ID',$row[9]);
        $data['costunit_id']    = slookup('controlling_costunits','name','ID',$row[10]);
        // Sub projects
        $result = db_query("SELECT ID
                              FROM ".DB_PREFIX."projekte
                             WHERE is_deleted is NULL
                               AND parent = ".(int)$project_ID."
                          ORDER BY next_proj") or db_die();
        while($row = db_fetch_row($result)) {
            $data['subproject'][$row[0]] = get_project_data($row[0],$sort_field,$direction);
        }
    }

    if ($project_ID > 0) {
        return $data;
    } else {
        $tmp = array();
        return $tmp[$project_ID] = $data;
    }
}

/*
* Recursive function for get an array with all the data of a project
* and all sub projects
* @param   int   $project_ID   - Id of the project
* @param   array $data         - Return array
* @return  array               - All the data in an array
*/
function get_project_data2($project_IDs = array(0=>'gesamt'), $sort_field='ID', $direction='DESC',$parent=0,$project_data=array()) {
    // Get the project data
    global $user_ID, $user_kurz, $user_type;
    require_once (LIB_PATH.'/dbman_lib.inc.php');
    // fetch parent project
    $before_where = special_sql_filter_flags('projects', $_REQUEST);
    $where = "";
    $where = special_sql_filter_flags('projects', $_REQUEST, false);
    if (isset($_REQUEST['use_filters']) && ($_REQUEST['use_filters'] == "on") ) {
        // List of fields in the db table, needed for filter
        require_once(LIB_PATH.'/dbman_lib.inc.php');
        $fields = build_array('projects', '', 'view');
        $where .= main_filter('', '', '', '', 'projects','','');
    }

    $query = "SELECT DISTINCT p.ID, name, note, kategorie, status, wichtung,
                     chef, anfang, ende,
                     costcentre_id, c.costunit_id, budget,kategorie,wichtung,user_ID
                FROM ".DB_PREFIX."projekte p
           LEFT JOIN ".DB_PREFIX."projekte_costunit c on c.projekte_ID = p.ID 
           LEFT JOIN ".DB_PREFIX."project_users_rel on p.ID = project_ID
               $before_where
               WHERE p.is_deleted is NULL
                     AND parent = ".(int)$parent." AND (acc LIKE 'system'
                  OR ((von = ".(int)$user_ID." OR acc LIKE 'group' OR acc LIKE '%\"$user_kurz\"%')
                     ".$where.group_string()."))";
    if ($project_IDs[0] != 'gesamt' ) {
        $query .= "AND p.ID IN (".implode(',',$project_IDs).")";
    }
    $query .=" GROUP BY p.ID";

    if ($sort_field != '0') {
        $query .=" ORDER BY $sort_field";
        if ($direction != '0') {
            $query .=" ".$direction;
        }
    }
    $result = db_query($query)or db_die();

    while ($row = db_fetch_row($result)) {
        // check access
        // genreal acces - either the user has direct access to it or the user has chef status
        // get booked time
        $booked_query="SELECT SUM(h), SUM(m)
                       FROM ".DB_PREFIX."timeproj
                       WHERE projekt= ".(int)$row[0];
        $booked_result=db_query($booked_query);
        $booked_row = db_fetch_row($booked_result);
        $hours   = (int) ($booked_row[0] + floor($booked_row[1]/60)) ;
        $minutes = (int) $booked_row[1]%60;
        $booked=floor(($booked_row[0]*60 + $booked_row[1])/3.6);
        $booked = $booked/100;

        // get values
        $data = array();
        $fields_array= build_array('projekte',$row[0],'filter');

        $data['name']           = get_correct_value($row[1],$fields_array['name']);
        $data['note']           = get_correct_value($row[2],$fields_array['note']);
        $data['kategorie']      = get_correct_value($row[3],$fields_array['kategorie']);
        $data['status']         = get_correct_value($row[4],$fields_array['status']);
        $data['wichtung']       = get_correct_value($row[5],$fields_array['wichtung']);
        $data['chef']           = get_correct_value($row[6],$fields_array['chef']);
        $data['anfang']         = get_correct_value($row[7],$fields_array['anfang']);
        $data['ende']           = get_correct_value($row[8],$fields_array['ende']);
        $data['budget']	        = get_correct_value($row[11],$fields_array['budget']);
        $data['kategorie']	    = get_correct_value($row[12],$fields_array['kategorie']);
        $data['wichtung']		= get_correct_value($row[13],$fields_array['wichtung']);
        $data['user_ID']		= get_correct_value($row[14],$fields_array['user_ID'],$row[0]);
        
        foreach ($data as $key=>$val){
            $project_data[$row[0]][$key]=$val['value'];
        }
        $project_data[$row[0]]['ID']             = $row[0];
        $project_data[$row[0]]['aufwand_gebucht']= $booked;
        $project_data[$row[0]]['costcentre_id']  = slookup('controlling_costcentres','name','ID',$row[9]);
        $project_data[$row[0]]['costunit_id']    = slookup('controlling_costunits','name','ID',$row[10]);
        // Sub projects
        $result2 = db_query("SELECT ID
                               FROM ".DB_PREFIX."projekte
                              WHERE parent = $row[0]
                                AND is_deleted is NULL
                           ORDER BY next_proj") or db_die();
        $row2 = db_fetch_row($result2);
        if($row2[0]>0)$project_data = get_project_data2($project_IDs,$sort_field,$direction,$row[0],$project_data);

    }
    return $project_data;
}

/*
 * Return the day before of the parsed date
 *
 * @param string $date Date
 *
 * @return string Date - 1
 */
function get_date_before($date) {
    list($year,$month,$day) = split("-",$date);
    $before_date = date("Y-m-d",mktime(0,0,0,$month,$day-1,$year));
    return $before_date;
}

/**
 * Save the unit per day, if the day is setted, sum the unit
 *
 * @param string $start Start date
 * @param string $end   End date
 * @param int    $unit  Unit to save
 * @param array  $data  Data for check the seted value
 *
 * @return array
 */
function collect_unit_per_day($start, $end, $unit, $data) {
    list($syear,$smonth,$sday) = split("-",$start);
    list($eyear,$emonth,$eday) = split("-",$end);

    for($y = $syear; $y <= $eyear ; $y++) {
        if ($y == $syear) {
            $start_month = intval($smonth);
        } else {
            $start_month = 1;
        }
        for($m = $start_month; $m <= 12 ; $m++) {
            if (($y < $eyear) || (($y == $eyear) && ($m <= $emonth)) ) {
                $lastday = date("t",mktime(0,0,0,$m,1,$y));
                if (($y == $syear)&&($m == $smonth)) {
                    $start_day = intval($sday);
                } else {
                    $start_day = 1;
                }
                for($d = $start_day; $d <= 31 ; $d++) {
                    if ($d <= $lastday) {
                        if ($y == $eyear && $m == $emonth && $d == $eday+1) {
                            break;
                        }

                        $date = "$y-$m-$d";
                        if (isset($data["$y-$m-$d"])) {
                            $data[$date] += $unit;
                        } else {
                            $data[$date] = $unit;
                        }
                    }
                }
            }
        }
    }

    return $data;
}
?>
