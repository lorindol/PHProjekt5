<?php
/**
 * Export a phprojekt to a gantt file
 *
 * @package    projects
 * @subpackage pbridge
 * @author     Gustavo Solt , $Author: gustavo $
 * @licence    GPL, see www.gnu.org/copyleft/gpl.html
 * @copyright  2000-2006 Mayflower GmbH www.mayflower.de
 * @version    $Id: 
 */

define('PATH_PRE','../');
include_once(PATH_PRE.'lib/lib.inc.php');
require_once(LIB_PATH.'/dbman_lib.inc.php');
require_once(PATH_PRE.'/lib/specialdays.php');

// First check the project_ID
if (isset($_GET['project_ID'])) {
    $project_ID = intval($_GET['project_ID']);
} else {
    die("<html><body><div id=\"global-main\">".__('Please select an existing project ID')."</body></html>!");
} 
if ($project_ID > 0) {
    global $user_ID, $user_kurz, $user_type;
    $result = db_query("SELECT name
                          FROM ".DB_PREFIX."projekte
                         WHERE (acc LIKE 'system'
                               OR ((von = ".(int)$user_ID." OR acc LIKE 'group' OR acc LIKE '%\"$user_kurz\"%')
                               ".group_string('projects')."))
                               AND ID = ".(int)$project_ID."
                           AND is_deleted is NULL") or db_die();
    $row = db_fetch_row($result);

    if (empty($row)) {
        die("<html><body><div id=\"global-main\">".__('Please select an existing project ID')."</body></html>");
    } else {
        // check access
        // genereal acces - either the user has direct access to it or the user has chef status
        if (!$row[0] and $user_type != 3) {
            // die("You are not privileged to do this!");
        }
    }
}

// Get all the data of the project and subprojects
$data = get_project_data($project_ID);

// Set header
$name = $data['project_name'].".gan";
$file_download_type = "attachment";
include_once(LIB_PATH."/get_contenttype.inc.php");

// xml output
// Name, company, today date, laboral days and weekends
$xmlstring = '';
$xmlstring .= '<?xml version="1.0" encoding="UTF-8"?>
<project name="Mayflowers PHProjekt" company="Mayflowers" webLink="http://www.phprojekt.com" view-date="'.$data['anfang'].'" view-index="0" gantt-divider-location="355" resource-divider-location="405" version="2.0">
<view zooming-state="default:2"/>
<!-- -->
<calendars>
<day-types>
<day-type id="0"/>
<day-type id="1"/>
<calendar id="1" name="default">
<default-week sun="1" mon="0" tue="0" wed="0" thu="0" fri="0" sat="1"/>
<overriden-day-types/>
<days/>
</calendar>
</day-types>
</calendars>';

// Projects properties
$xmlstring .= '
<tasks color="#8cb6ce">
<taskproperties>
<taskproperty id="tpd0" name="type" type="default" valuetype="icon"/>
<taskproperty id="tpd1" name="priority" type="default" valuetype="icon"/>
<taskproperty id="tpd2" name="info" type="default" valuetype="icon"/>
<taskproperty id="tpd3" name="name" type="default" valuetype="text"/>
<taskproperty id="tpd4" name="begindate" type="default" valuetype="date"/>
<taskproperty id="tpd5" name="enddate" type="default" valuetype="date"/>
<taskproperty id="tpd6" name="duration" type="default" valuetype="int"/>
<taskproperty id="tpd7" name="completion" type="default" valuetype="int"/>
<taskproperty id="tpd8" name="coordinator" type="default" valuetype="text"/>
<taskproperty id="tpd9" name="predecessorsr" type="default" valuetype="text"/>
</taskproperties>';

// Projects and sub projects 
$xmlstring .= get_xml_project_str($data, $project_ID);

// End projects
$xmlstring .= '
</tasks>';

// Get Members and chefs
list($persons,$roles) = get_members_and_roles($data);

// Keep the roles in an array
$roles = array_unique($roles);

// Write All the members/chef
// Role by Default for each user will be the Default grantt role
$xmlstring .= '
<resources>';
foreach ($persons as $ID => $person_data) {
    $xmlstring .= '
<resource id="'.$ID.'" name="'.$person_data['vorname'].' '.$person_data[$nachname].'" function="Default:0" contacts="'.$person_data['email'].'" phone="'.$person_data['tel'].'"/>';
}
$xmlstring .= '
</resources>';

// Members and Chef per project with the specific role
$xmlstring .= '
<allocations>';
$xmlstring .= get_xml_chef_and_roles_str($data,$persons, $roles);
$xmlstring .= '
</allocations>
<vacations/>';

// Project Display
$xmlstring .= '
<taskdisplaycolumns>
<displaycolumn property-id="tpd10" order="0" width="25"/>
<displaycolumn property-id="tpd3" order="1" width="75"/>
<displaycolumn property-id="tpd4" order="2" width="75"/>
<displaycolumn property-id="tpd5" order="3" width="75"/>
</taskdisplaycolumns>
<previous/>';

// Roles
$xmlstring .= '
<roles roleset-name="Default"/>
<roles roleset-name="SoftwareDevelopment"/>';
if (!empty($roles)) {
    $xmlstring .= '
<roles>';
    foreach($roles as $role_ID => $role_name) {
        if (!empty($role_name)) {
            $xmlstring .= '
<role id="'.$role_ID.'" name="'.$role_name.'"/>';
        }
    }
    $xmlstring .= '
</roles>';
}

// End all
$xmlstring .= '
</project>';

// Write
//$xmlstring = ereg_replace("",'',$xmlstring);
echo $xmlstring;

/*
 * Functions
 */

/*
 * Recursive function for get an array with all the data of a project
 * the members in the project
 * and the same for all sub projects
 * @param   int   $project_ID   - Id of the project
 * @param   array $data         - Return array
 * @return  array               - All the data in an array
 */
function get_project_data($project_ID, $data = array()) {
    // Get the project data
    global $user_ID, $user_kurz, $user_type;
    
    $query = "SELECT ID, name, anfang, ende, chef, contact, stundensatz, budget, wichtung,
                     ziel, note, depend_mode, depend_proj, next_mode, next_proj, probability,
                     ende_real, kategorie, status, statuseintrag, parent, acc,
                     acc_write, von,gruppe
                FROM ".DB_PREFIX."projekte
               WHERE is_deleted is NULL
                     AND (acc LIKE 'system'
                     OR ((von = ".(int)$user_ID." OR acc LIKE 'group' OR acc LIKE '%\"$user_kurz\"%')
                     ".group_string('projects')."))"; 
    if ($project_ID > 0 ) {
        $query .= "AND ID = ".(int)$project_ID;
    }

    $result = db_query($query)or db_die();

    $row = db_fetch_row($result);

    // check access
    // genreal acces - either the user has direct access to it or the user has chef status
    if (!$row[0] and $user_type!=3) {
        //die("You are not privileged to do this!");
    }

    // get values
    $data['ID']             = $row[0];
    $data['project_name']   = utf8_encode(html_out($row[1]));
    $data['anfang']         = $row[2];
    $data['ende']           = $row[3];
    $data['chef']           = $row[4];
    $data['contact']        = $row[5];
    $data['stundensatz']    = $row[6];
    $data['budget']         = $row[7];
    $data['wichtung']       = $row[8];
    $data['ziel']           = $row[9];
    $data['note']           = utf8_encode($row[10]);
    $data['depend_mode']    = $row[11];
    $data['depend_proj']    = $row[12];
    $data['next_mode']      = $row[13];
    $data['next_proj']      = $row[14];
    $data['probability']    = $row[15];
    $data['ende_real']      = $row[16];
    $data['category']       = $row[17];
    $data['status']         = $row[18];
    $data['statuseintrag']  = $row[19];
    $data['parent']         = $row[20];
    $data['acc']            = $row[21];
    $data['acc_write']      = $row[22];
    $data['subproject']     = array();
    $data['dependencies']   = array();
    $data['persons']        = array();

    // Members
    $query = "SELECT u.ID, u.vorname, u.nachname, u.email, u.tel1,
                     pur.role, pur.user_unit
                FROM ".DB_PREFIX."project_users_rel AS pur      
           LEFT JOIN ".DB_PREFIX."users as u ON pur.user_ID = u.ID 
               WHERE pur.project_ID = ".(int)$project_ID;

    $result = db_query($query)or db_die();
    while($row = db_fetch_row($result)) {
        $unit = (!empty($row[6])) ? $row[6] : '0.1';
        $data['persons'][$row[0]] = array(  'vorname'   => utf8_encode($row[1]),
                                            'nachname'  => utf8_encode($row[2]),
                                            'email'     => $row[3],
                                            'tel'       => $row[4],
                                            'role'      => utf8_encode($row[5]),
                                            'unit'      => $unit);
    }

    // Milestones
    $result = db_query("SELECT ID,name,description,begin,end,von,assigned
                          FROM ".DB_PREFIX."project_elements
                          WHERE project_ID = ".(int)$project_ID."
                            AND category = 1
                       ORDER BY begin") or db_die();
    while($row = db_fetch_row($result)) {
        $data['milestone'][] = array (  'ID'            => ($project_ID*10000)+$row[0],
                                        'name'          => utf8_encode($row[1]),
                                        'description'   => utf8_encode($row[2]),
                                        'begin'         => (!empty($row[3])) ? $row[3] : '0000-00-00',
                                        'end'           => (!empty($row[4])) ? $row[4] : '0000-00-00',
                                        'von'           => $row[5],
                                        'assigned'      => $row[6]);
    }

    // Sub projects
    $result = db_query("SELECT ID
                          FROM ".DB_PREFIX."projekte
                          WHERE parent = ".(int)$project_ID."
                            AND is_deleted is NULL 
                       ORDER BY next_proj") or db_die();
    while($row = db_fetch_row($result)) {
        $data['subproject'][] = get_project_data($row[0]);
    }

    // Dependencies
    $result = db_query("SELECT ID, anfang, ende, depend_mode
                          FROM ".DB_PREFIX."projekte
                          WHERE depend_proj = ".(int)$project_ID."
                            AND is_deleted is NULL") or db_die();
    while($row = db_fetch_row($result)) {
        $data['dependencies'][] = array('ID'     => $row[0],
                                        'anfang' => $row[1],
                                        'ende'   => $row[2],
                                        'mode'   => $row[3]);
    }
    $result = db_query("SELECT ID, anfang, ende, depend_mode
                          FROM ".DB_PREFIX."projekte
                          WHERE id = ".(int)$data['depend_proj']."
                            AND is_deleted is NULL") or db_die();
    while($row = db_fetch_row($result)) {
        $data['dependencies'][] = array('ID'     => $row[0],
                                        'anfang' => $row[1],
                                        'ende'   => $row[2],
                                        'mode'   => $row[3]);
    }

    return $data;
}

/*
 * Make the <task> for a project 
 * and the <depend> for the sub projects
 * @param   array   $data   - Array with data of the project
 * @param   bool    $ommit  - Ommit this ID?
 *
 * @return  string          - <task>/<depend> string
 */
function get_xml_project_str($data, $project_ID) {
    $tmp    = '';
    $tmp2   = '';

    if ($data['ID'] == $project_ID) {
        $ommit = true;
    } else {
        $ommit = false;
    }

    if (!$ommit) {
        // Only 3 states for priority
        $priority = floor($data['wichtung']/3);

        $xmlstring = '
<task id="'.$data['ID'].'" name="'.$data['project_name'].'" color="#8cb6ce" meeting="false" start="'.$data['anfang'].'" duration="'.get_laboral_days($data['anfang'],$data['ende']).'" complete="'.intval($data['status']).'" priority="'.$priority.'" expand="true">';
        if (!empty($data['note'])) {
            $xmlstring .= '
<notes><![CDATA['.$data['note'].']]></notes>';
        }

        if (!empty($data['dependencies'])) {
            foreach ($data['dependencies'] as $subtmp => $dependencies) {
                // Get the type for the suprojects-subprojects dependencies
                switch ($dependencies['mode']) {
                    case "2":
                        //cannot start before the end of project
                        $diff = get_laboral_days($data['ende'],$dependencies['anfang']);
                        $type = 2;
                        break;
                    case "3":
                        //cannot start before the start of project
                        $diff = get_laboral_days($data['anfang'],$dependencies['anfang']);
                        $type = 1;
                        break;
                    case "4":
                        //cannot end before the start of project
                        $diff = get_laboral_days($data['anfang'],$dependencies['ende']);
                        $type = 4;
                        break;
                    case "5":
                        //cannot end before the end of project
                        $diff = get_laboral_days($data['ende'],$dependencies['ende']);
                        $type = 3;
                        break;
                   }

                $xmlstring .= '
<depend id="'.$dependencies['ID'].'" type="'.$type.'" difference="'.$diff.'" hardness="Strong"/>';
            }
        }
    }

    foreach ($data['subproject'] as $subdata) {
        // Keep the subprojects
        $tmp .= get_xml_project_str($subdata,$project_ID);
    }
    $xmlstring .= $tmp;

    // Milestones
    if (isset($data['milestone'])) {
        foreach ($data['milestone'] as $subdata) {
            $tmp2 .= get_xml_project_str2($subdata, $project_ID);
        }
        $xmlstring .= $tmp2;
    }

    if (!$ommit) {
        $xmlstring .= '
</task>';
    }

    return $xmlstring;
}

/*
 * Make the <task> for a milestone
 * @param   array   $data   - Array with data of the project
 *
 * @return  string          - <task> string
 */
function get_xml_project_str2($data, $project_ID) {
    $xmlstring = '
<task id="'.$data['ID'].'" name="'.$data['name'].'" color="#8cb6ce" meeting="true" start="'.$data['begin'].'" duration="'.get_laboral_days($data['begin'],$data['end']).'" expand="true">';
    if (!empty($data['description'])) {
        $xmlstring .= '
<notes><![CDATA['.$data['description'].']]></notes>';
    }

    $xmlstring .= '
</task>';

    return $xmlstring;
}

/*
 * Collect all the Members and roles 
 * from project and subprojects in one array
 * @param   array   $data        - Array with data of the project
 * @param   array   $allpersons  - Array with all the members
 * @param   array   $allroles    - Array with all the roles
 * @return  array($allpersons,$allroles)
 */
function get_members_and_roles($data = array(), $allpersons = array(), $allroles = array()) {
    
    foreach ($data['persons'] as $ID => $person_data) {
        $allpersons[$ID] = $person_data;
        $allroles[]      = $person_data['role'];
    }

    // The chef is not in the array? add it
    if (!empty($data['chef'])) {
        if (!isset($allpersons[$data['chef']])) {
            $result = db_query("SELECT vorname, nachname, email, tel1
                                  FROM ".DB_PREFIX."users
                                WHERE ID = ".(int)$data['chef']) 
                                or db_die();
            $row = db_fetch_row($result);
            $allpersons[$data['chef']] = array('vorname'   => $row[0],
                                               'nachname'  => $row[1],
                                               'email'     => $row[2],
                                               'tel'       => $row[3],
                                               'role'      => '',
                                               'unit'      => '0.1');
        }
    }
    
    // Recursive for subprojects
    foreach ($data['subproject'] as $tmp => $subdata) {
        list($allpersons,$allroles) = get_members_and_roles($subdata, $allpersons, $allroles);
    }

    return array($allpersons, $allroles);
}

/*
 * Make the <alocation> for each member/chef on each project
 * @param   array   $data     - Array with data of the project
 * @param   array   $persons  - Array with all the members
 * @param   array   $roles    - Array with all the roles
 * @return  string            - <alocation/> string
 */
function get_xml_chef_and_roles_str($data,$persons, $roles) {
    $tmp = '';
    $xmlstring = '';
  
    $included_chef = false;
    foreach($data['persons'] as $ID => $person_data) {
        if (empty($person_data['role'])) {
            $role = "Default:0";
        } else {
            // Put the position
            $role = array_search($person_data['role'],$roles);
        }
        if ($ID == $data['chef']) {
            $responsible    = 'true';
            $included_chef = true;
            $role = "Default:1";
        } else {
            $responsible = 'false';
        }
        $xmlstring .= '
<allocation task-id="'.$data['ID'].'" resource-id="'.$ID.'" function="'.$role.'" responsible="'.$responsible.'" load="'.$person_data['unit'].'" />';
    }

    // The chied is not included yet
    if (!$included_chef && !empty($data['chef'])) {
        if (empty($persons[$data['chef']]['role'])) {
            // Chef
            $role = "Default:1";
        } else {
            // Put the position
            $role = array_search($persons[$data['chef']],$roles);
        }
        $xmlstring .= '
<allocation task-id="'.$data['ID'].'" resource-id="'.$data['chef'].'" function="'.$role.'" responsible="true" load="0.1" />';
    }

    foreach ($data['subproject'] as $subdata) {
        $tmp .= get_xml_chef_and_roles_str($subdata,$persons,$roles);
    }
    
    $xmlstring .= $tmp;

    return $xmlstring;
}

/*
 * Return the weekend and holidays days between two dates
 *
 * @param  string $start - Start date
 * @param  string $ende  - End date
 *
 * @return int    $count - Number of days
 */
function get_laboral_days($start,$ende) {
    global $settings;
    $holidays = new SpecialDays((array) $settings['cal_hol_file']);

    list($syear,$smonth,$sday) = split('-',$start);
    list($eyear,$emonth,$eday) = split('-',$ende);

    $count = 0;
    for($y = $syear; $y <= $eyear ; $y++) {
        if ($y == $syear) {
            $start_month = intval($smonth);
        } else {
            $start_month = 1;
        }
        $holidays_days = $holidays->_calculate($y);
        for($m = $start_month; $m <= 12 ; $m++) {
            if (($y <= $eyear) && ($m <= $emonth)) {
                $lastday = date("t",mktime(0,0,0,$m,1,$y));
                if (($y == $syear)&&($m == $smonth)) {
                    $start_day = intval($sday);
                } else {
                    $start_day = 1;
                }
                for($d = $start_day; $d <= 31 ; $d++) {
                    if ($d <= $lastday) {
                        if ($y == $eyear && $m == $emonth && $d == $eday) {
                            break;
                        }
                        $mktime = mktime(0,0,0,$m,$d,$y);
                        $w = date("w",$mktime); // Week day
             
                        if (($w != 0) && ($w != 6)) {
                            $count++;
                            if (is_array($holidays_days)) {
                                foreach($holidays_days as $tmp => $holiday_data) {
                                    if ($holiday_data['date'] == $mktime) {
                            //            $count--;
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    return $count;
}
?>
