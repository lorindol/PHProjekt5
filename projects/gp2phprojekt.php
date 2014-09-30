<?php
/**
 * Import a gantt file to phprojekt
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

// check form token
check_csrftoken();

if (!is_file($_FILES['userfile']['tmp_name'])) {
    die("<html><body><div id=\"global-main\">".__('Please upload a correct file')."</body></html>");
} else {
    $filename = $_FILES['userfile']['tmp_name']; 
    $fileoriginalname = stripslashes($_FILES['userfile']['name']); 
    if (!(strstr($fileoriginalname,'.gan') || strstr($fileoriginalname,'.xml'))) {
        die("<html><body><div id=\"global-main\">".__('Please upload a correct gantt file')."</body></html>");
    }
}


// First check the project_ID
if (isset($_POST['project_ID'])) {
    $project_ID = intval($_POST['project_ID']);
} else {
    die("<html><body><div id=\"global-main\">".__('Please select an existing project ID')."</body></html>");
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
        die("<html><body><div id=\"global-main\">".__('Please select an existing project ID')."</body></html>!");
    } else {
        // check access
        // general access - either the user has direct access to it or the user has chef status
        if (!$row[0] and $user_type != 3) {
            // die("You are not privileged to do this!");
        }
    }
}

// Get all IDs
if ($project_ID > 0) {
    $allIDs = get_IDs($project_ID);
} else {
    $allIDs = array();
}

// Parse the file
$xml_parser = new xml();
$xml_parser->parse(file_get_contents($filename));

$allprojects = array();

// Make all the projects class
$position = 0;
foreach($xml_parser->data['TASK'] as $tmp => $project_data) {
    $allprojects = get_project_class($project_data, $allprojects, $position);
    $position++;
}

// Convert the dependencies
$allprojects = convert_dependencies($allprojects);

// Asign members, chef, roles
$allprojects = make_members($allprojects,$xml_parser->data);

// Convert gp ID to phproject ID
$allprojects = convert_projects($allprojects,$project_ID,$allIDs);

// Get all the members
$allmembers  = collect_members($xml_parser->data);

// Delete parent mielstones
db_query("DELETE
            FROM ".DB_PREFIX."project_elements
           WHERE category = 1
             AND project_ID = ".(int)$project_ID);

// Insert/Update all the project data
foreach ($allprojects as $tmp => $data) {
    $id             = $data->pid;
    $porcent        = $data->porcent;
    $parent         = $data->pparent;
    $name           = $data->name;
    $priority       = ($data->priority*3)+1;
    $start          = $data->start;
    $ende           = $data->ende;
    $depend_mode    = (!empty($data->depend_mode))  ? $data->depend_mode : 0;
    $depend_proj    = (!empty($data->pdepend_proj)) ? $data->pdepend_proj: $data->depend_proj;
    if ($depend_proj == '') $depend_proj = 0;
    $next_proj      = (!empty($data->next_proj)) ? $data->next_proj: 0;
    $chef           = (!empty($data->chef)) ? $data->chef : $null;
    $kategorie      = 3;
    $personen       = $data->members;
    $roles          = $data->roles;
    $note           = $data->note;
    $milestone      = $data->milestone;

    // Project
    if (!$milestone) {

        // Delete mielstones
        db_query("DELETE
                    FROM ".DB_PREFIX."project_elements
                   WHERE category = 1
                     AND project_ID = ".(int)$id);

        // Collect gp IDs
        $gpIDs[]    = $id;
        $allIDs[]   = $id;
        $query = "SELECT ID
                    FROM ".DB_PREFIX."projekte
                   WHERE ID = ".(int)$id."
                     AND is_deleted is NULL";
        $result = db_query($query) or db_die();
        $row = db_fetch_row($result);
        if (empty($row)) {
            $query = "INSERT INTO ".DB_PREFIX."projekte (
                                               ID  ,        von      ,         gruppe      ,         parent  ,  acc   , acc_write,        name    ,         ende    ,         wichtung  ,         status   ,        anfang   ,        chef    ,         note    ,        kategorie  ,         depend_mode  ,         depend_proj ,        next_proj  )
                           VALUES (    ".(int)$id.",".(int)$user_ID.", ".(int)$user_group.", ".(int)$parent.", 'group', 'w'      ,'".addslashes($name)."', '".addslashes($ende)."', ".(int)$priority.", ".(int)$porcent.",'".addslashes($start)."','".addslashes($chef)."', '".addslashes($note)."',".(int)$kategorie.", ".(int)$depend_mode.", ".(int)$depend_proj.",".(int)$next_proj.")";
        } else {
            $query = "UPDATE ".DB_PREFIX."projekte
                         SET von        = ".(int)$user_ID.",
                             gruppe     = ".(int)$user_group.",
                             parent     = ".(int)$parent.",
                             name       = '".addslashes($name)."',
                             ende       = '".addslashes($ende)."',
                             wichtung   = ".(int)$priority.",
                             status     = ".(int)$porcent.",
                             anfang     = '".addslashes($start)."',
                             chef       = '".addslashes($chef)."',
                             note       = '".addslashes($note)."',
                             depend_mode= ".(int)$depend_mode.",
                             depend_proj= ".(int)$depend_proj.",
                             next_proj  = ".(int)$next_proj."
                       WHERE ID = ".(int)$id."
                         AND is_deleted is NULL";
        }

        // Roles
        update_project_personen_table($id, $personen, 'user', $roles);
        $result = db_query($query) or db_die();
    } else {
        // Milestones
        $query = "INSERT INTO ".DB_PREFIX."project_elements (
                                       name    ,        description,        project_ID,        begin    ,        end     , category,        von      )
                       VALUES ('".addslashes($name)."','".addslashes($note)."'   ,".(int)$parent."  ,'".addslashes($start)."','".addslashes($ende)."','1'      ,".(int)$user_ID.")";
        $result = db_query($query) or db_die();
    }
}

// Delete the old projects
foreach($gpIDs as $tmp => $ID) {
    if (!in_array($ID,$allIDs)) {
        $query = "DELETE FROM ".DB_PREFIX."projekte
                   WHERE id = ".(int)$ID."
                     AND is_deleted is NULL";
        $result = db_query($query) or db_die();
    }
}

// Insert/Update all the members data
foreach ($allmembers as $tmp => $data) {
    $id             = $data['id'];
    $vorname        = $data['vorname'];
    $email          = $data['email'];
    $tel1           = $data['tel1'];

    $query = "SELECT id FROM ".DB_PREFIX."users
               WHERE id = ".(int)$id;
    $result = db_query($query) or db_die();
    $row = db_fetch_row($result);
    if (empty($row)) {
        // Don 't insert new users
        // Delete relation with all the project
        $query = "DELETE FROM ".DB_PREFIX."project_users_rel
                   WHERE user_ID = ".(int)$id;
        $result = db_query($query) or db_die();
        $query = "UPDATE ".DB_PREFIX."projekte
                     SET chef = NULL
                   WHERE chef = ".(int)$id."
                     AND is_deleted is NULL";

    } else {
        $query = "UPDATE ".DB_PREFIX."users
                     SET vorname    = '".addslashes($vorname)."',
                         email      = '".addslashes($email)."',
                         tel1       = '".addslashes($tel1)."'
                   WHERE id = ".(int)$id;
    }
    $result = db_query($query) or db_die();
}

// Back to the project page
$query_str = PATH_PRE."projects/projects.php";
header('Location: '.$query_str);

/*
 * xml class
 * This class will parse the data into an array
 */
class xml  {
    var $parser;
    var $data;
    var $level;
    var $use_cdata = false;

    function xml() {
        $this->parser = xml_parser_create();
        xml_set_object($this->parser,$this);
        xml_set_element_handler($this->parser,"tag_open","tag_close");
        xml_set_character_data_handler($this->parser,"cdata");
    }

    function parse($data) { 
        xml_parse($this->parser,$data);
    }

    function tag_open($parser,$tag,$attributes) { 
        if ($tag == 'TASK') {
            // New Parent task
            if (count($this->level) == 0) {
                $this->data[$tag][] = $attributes;
                $this->level[] = array(  'pos'  => count($this->data[$tag])- 1,
                                         'ID'   => $attributes['ID'],
                                         'type' => 'parent');
            } else {
                // Subproject
                $str = "";
                foreach($this->level as $level => $level_data) {
                    $str .= "['TASK'][".$level_data['pos']."]";
                }
                $str .= "['TASK']";
                eval("\$last_pos = count(\$this->data$str);");
                foreach($attributes as $index => $value) {
                    $value = xss(addslashes($value));
                    eval("\$this->data$str"."[".$last_pos."]"."[".$index."] = \"$value\";");
                }
                $this->level[] = array( 'pos'  => $last_pos,
                                        'ID'   => $attributes['ID'],
                                        'type' => 'subproject');
            }
        } else if ($tag == 'DEPEND') {
            // Insert the depend tag into a parent task
            if (count($this->level) == 0) {
                $pos = $this->level[0]['pos'];
                $this->data['TASK'][$pos]['DEPEND'][] = $attributes;
            } else {
                // Insert the depend tag into a subproject
                $str = "";
                foreach($this->level as $level => $level_data) {
                    $str .= "['TASK'][".$level_data['pos']."]";
                }
                $str .= "['DEPEND']";
                eval("\$last_pos = count(\$this->data$str);");
                foreach($attributes as $index => $value) {
                    $value = xss(addslashes($value));
                    eval("\$this->data$str"."[".$last_pos."]"."[".$index."] = \"$value\";");
                }
            }
        } else if ($tag == 'NOTES') {
            // Insert notes
            $this->use_cdata = true;
        } else {
            // Normal tag
            $this->data[$tag][] = $attributes;
        }
    }

    function cdata($parser,$cdata) { 
        if ($this->use_cdata) {
            // Notes for a parent task
            if (count($this->level) == 0) {
                $pos = $this->level[0]['pos'];
                $this->data['TASK'][$pos]['NOTES'][] = $attributes;
            } else {
                // Notes for a subproject
                $str = "";
                foreach($this->level as $level => $level_data) {
                    $str .= "['TASK'][".$level_data['pos']."]";
                }
                $str .= "['NOTES']";
                $cdata = xss(addslashes($cdata));
                eval("\$this->data$str"."[".$last_pos."] = \"$cdata\";");
            }
            $this->use_cdata = false;
        } 
    }

    function tag_close($parser,$tag) { 
        // Delete one level of task
        if ($tag == 'TASK') {
            array_pop($this->level);
        }
    }

} // end of class xml

/*
 * PHPprojekt_project class
 * This class will get all the data of each project
 */
class PHPprojekt_project {
    var $id;
    var $pid;
    var $name;
    var $start;
    var $ende;
    var $procent;
    var $priority;
    var $parent;
    var $pparent;
    var $depend;
    var $note;
    var $depend_proj;
    var $pdepend_proj;
    var $depend_mode;
    var $next_proj;
    var $members    = array();
    var $roles      = array();
    var $chef;
    var $milestone;

    function PHPprojekt_project($data, $position, $parent = '') {
        list($year,$month,$day) = split('-',$data['START']);
        $ende = date("Y-m-d",mktime(0, 0, 0, $month, $day+$data['DURATION'], $year));
        $weekend_count = -1;
        $tmp_start = $data['START'];
        $tmp_ende  = $ende;
        $total_count = 0;
        // Recursive get weekend's day
        while($weekend_count != 0) {
            $weekend_count = get_not_laboral_days($tmp_start,$tmp_ende);
            list($tmp_year,$tmp_month,$tmp_day) = split('-',$tmp_ende);
            $tmp_start = date("Y-m-d",mktime(0, 0, 0, $tmp_month, $tmp_day+1, $tmp_year));
            $tmp_ende  = date("Y-m-d",mktime(0, 0, 0, $tmp_month, $tmp_day+$weekend_count, $tmp_year));

            $week_start = date("w",mktime(0, 0, 0, $tmp_month, $tmp_day+1, $tmp_year)); 
            $week_end   = date("w",mktime(0, 0, 0, $tmp_month, $tmp_day+$weekend_count, $tmp_year)); 
            if ( (($week_start == 0) || ($week_start == 6)) && (($week_end == 0) || ($week_end == 6)) && $weekend_count < 2) {
                break;
            }
            $total_count += $weekend_count;
        }
        // Get final ende
        $ende  = date("Y-m-d",mktime(0, 0, 0, $month, $day+$data['DURATION']+$total_count, $year));

        if ($data['MEETING'] == 'false') {
            $this->id           = $data['ID'];
            $this->pid          = $data['ID'];
            $this->name         = utf8_decode(trim($data['NAME']));
            $this->start        = $data['START'];
            $this->ende         = $ende;
            $this->porcent      = $data['COMPLETE'];
            $this->priority     = $data['PRIORITY'];
            $this->parent       = $parent;
            $this->pparent      = $parent;
            $this->depend       = $data['DEPEND'];
            $this->next_proj    = $position;
            $this->note         = utf8_decode(trim($data['NOTES'][0]));
            $this->milestone    = false;
        } else {
            $this->id           = '';
            $this->pid          = '';
            $this->name         = utf8_decode(trim($data['NAME']));
            $this->start        = $data['START'];
            $this->ende         = $ende;
            $this->porcent      = '';
            $this->priority     = '';
            $this->parent       = $parent;
            $this->pparent      = $parent;
            $this->depend       = $data['DEPEND'];
            $this->next_proj    = '';
            $this->note         = utf8_decode(trim($data['NOTES'][0]));
            $this->milestone    = true;
        }
    }
}

/*
 * Create the phproject class for each project
 *
 * @param  Array $project_data - Array with all the parse xml data
 * @param  Array $allprojects  - Array with all the class projects
 * @param  int   $parent       - Parent id
 * @param  int   $position     - next_proj valur for sort thr projects
 * @return Array $allprojects  - All the projects class into an array
 */
function get_project_class($project_data, $allprojects, $position, $parent = '') {
    $position++;
    $project = new PHPprojekt_project($project_data, $position, $parent);
    $allprojects[] = $project;
    if (isset($project_data['TASK'])) {
        $subposition = 0;
        foreach ($project_data['TASK'] as $tmp => $subproject_data) {
            $allprojects = get_project_class($subproject_data, $allprojects, $subposition, $project->id);
            $subposition++;
        }
    }

    return $allprojects;
}

/*
 * Move the gantt dependencies to the phproject dependencies
 *
 * @param  Array $projects - All the projects array
 * @return Array $projects - All the corrected projects array
 */
function convert_dependencies($projects) {
    foreach ($projects as $tmp => $data) {
        if (isset($data->depend)) {
            foreach($data->depend as $tmp2 => $depend) {
                $pos = search_pos($projects,$depend['ID']);

                // Correct the depend
                switch ($depend['TYPE']) {
                    case "2":
                        //cannot start before the end of project
                        $type = 2;
                        break;
                    case "1":
                        //cannot start before the start of project
                        $type = 3;
                        break;
                    case "4":
                        //cannot end before the start of project
                        $type = 4;
                        break;
                    case "3":
                        //cannot end before the end of project
                        $type = 5;
                        break;
                }
                $projects[$pos]->depend_proj = $data->id;
                $projects[$pos]->depend_mode = $type;
                unset($data->depend[$tmp2]);
            }
            $data->depend = '';
        }
    }
    return $projects;
}

/*
 * Asign members, chef, roles
 *
 * @param  Array $projects   - All the projects array
 * @param  Array $parsedata  - Parsed data from the xml
 *
 * @return Array $projects  - All the corrected projects array
 */
function make_members($projects,$parsedata) {
    foreach($parsedata['ALLOCATION'] as $tmp => $data) {
        $pos = search_pos($projects,$data['TASK-ID']);
            
        if ($data['RESPONSIBLE'] == 'true') {
            $projects[$pos]->chef = $data['RESOURCE-ID'];
        }
        
        $function = '';
        if (strstr(':', $data['FUNCTION'])) {
            $function = $data['FUNCTION'];
        } else {
            if ($data['FUNCTION'] != '') {
                $role_pos = search_pos($parsedata['ROLE'],$data['FUNCTION'],'array');
                $function = $parsedata['ROLE'][$role_pos]['NAME'];
            }
        }
        $projects[$pos]->members[]                                     = $data['RESOURCE-ID'];
        $projects[$pos]->roles['u_'.$data['RESOURCE-ID'].'_text_role'] = utf8_decode(trim($function));
        $projects[$pos]->roles['u_'.$data['RESOURCE-ID'].'_unit']      = $data['LOAD'];
    }
    return $projects;
}

/*
 * Collect all members data
 *
 * @param  Array $parsedata   - Parsed data from the xml
 * @return Array $allmembers  - All the members with data
 */
function collect_members($parsedata) {
    $allmembers = array();
    foreach($parsedata['RESOURCE'] as $tmp => $data) {
        $allmembers[] = array(  'id'        => $data['ID'],
                                'vorname'   => utf8_decode(trim($data['NAME'])),
                                'email'     => $data['CONTACTS'],
                                'tel1'      => $data['PHONE']);
    }

    return $allmembers;
}

/*
 * Search in which pos are the id into the array
 *
 * @param  Array    $array    - Array to search
 * @param  int      $id       - Id to search
 * @param  string   $type     - class/array
 * @return int      $id       - Pos into the array
 */
function search_pos($array,$id, $type = 'class') {
    $pos = 0;
    if (is_array($array)) {
      foreach ($array as $tmp => $data) {
            if ($type == 'class') {
                if ($data->id == $id) {
                    $pos = $tmp;
                    break;
                }
            } else {
                if ($data['ID'] == $id) {
                    $pos = $tmp;
                    break;
                }
            }
        }
    return $pos;
    }
}

/*
 * Return the weekend and holidays days between two dates
 *
 * @param  string $start - Start date
 * @param  string $ende  - End date
 *
 * @return int    $count - Number of days
 */
function get_not_laboral_days($start,$ende) {
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
                        if ($y == $eyear && $m == $emonth && $d == $eday+1) {
                            break;
                        }
                        $mktime = mktime(0,0,0,$m,$d,$y);
                        $w = date("w",$mktime); // Week day
             
                        if (($w == 0) || ($w == 6)) {
                            $count++;
                        }

                        if (is_array($holidays_days)) {
                            foreach($holidays_days as $tmp => $holiday_data) {
                                if ($holiday_data['date'] == $mktime) {
                        //            $count++;
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

/*
 * Get all the  ID of the subprojects
 *
 * @param  Array $parent    - Parent ID
 * @param  Array $allIDs    - All the IDs
 *
 * @return Array $allIDs    - All the IDs
 */
function get_IDs($parent, $allIDs = array()) {
    $query = "SELECT id
                FROM ".DB_PREFIX."projekte
               WHERE parent = ".(int)$parent."
                 AND is_deleted is NULL";
    $result = db_query($query) or db_die();
    while($row = db_fetch_row($result)) {
        $allIDs[] = $row[0];
        $allIDs = get_IDs($row[0], $allIDs);
    }
    return $allIDs;
}

function convert_projects($allprojects,$project_ID,$allIDs) {
    $query = "SELECT MAX(id) FROM ".DB_PREFIX."projekte";
    $result = db_query($query) or db_die();
    $row = db_fetch_row($result);
    $last_id = $row[0];
    $convert = array();

    // Collect id to convert
    foreach ($allprojects as $tmp => $project_data) {
        if (!in_array($project_data->id,$allIDs)) {
            if (!$project_data->milestone) {
                $last_id++;
                $convert[] = array( 'actual' => $project_data->id,
                                    'new'    => $last_id);
            } else {
                $tmpconvert = $convert;
                $found = 0;
                foreach ($tmpconvert as $k => $v) {
                    if ($v['actual'] == $project->parent) {
                        $convert[] = array( 'actual' => $project_data->parent,
                                            'new'    => $v['new']);
                        $found = 1;
                        break; 
                    }
                }
                if (!$found) {
                    // Principal project
                    if ($project_data->parent == '') {
                        $allprojects[$tmp]->pparent = $project_ID;
                    }
                }
            }
        } else {
            if ($project_data->id == $project_ID) {
                $allprojects[$tmp]->pparent = $project_ID;
            }
            // Principal project
            if ($project_data->parent == '') {
                $allprojects[$tmp]->pparent = $project_ID;
            }
        }
    }

    foreach ($convert as $tmp2 => $data) {
        foreach ($allprojects as $tmp => $project_data) {
            // Principal project
            if ($project_data->parent == '') {
                $allprojects[$tmp]->pparent = $project_ID;
            }

            // Convert depend_proj
            if ($project_data->depend_proj == $data['actual']) {
                $allprojects[$tmp]->pdepend_proj = $data['new'];
            }
            
            // Convert parent
            if ($project_data->parent == $data['actual']) {
                $allprojects[$tmp]->pparent = $data['new'];
            }

            if ($project_data->id == $data['actual']) {
                $allprojects[$tmp]->pid = $data['new'];
            }
        }
    }

    return $allprojects;
}
?> 
