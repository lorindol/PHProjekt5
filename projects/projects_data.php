<?php

// projects_data.php - PHProjekt Version 5.2
// copyright  ©  2000-2005 Albrecht Guenther  ag@phprojekt.com
// www.phprojekt.com
// Author: Albrecht Guenther, $Author: gustavo $
// $Id: projects_data.php,v 1.71.2.1 2007/01/12 21:02:32 gustavo Exp $

// check whether the lib has been included - authentication!
if (!defined("lib_included")) die("Please use index.php!");

// check role
if (check_role("projects") < 2) die("You are not allowed to do this!");

// check form token
check_csrftoken();

// Clear the message_stack
unset ($_SESSION['message_stack'][$module]);

include_once(LIB_PATH."/permission.inc.php");
include_once("err_pro.php");

if ($parent == '') $parent = 0;

$mode = "view";

switch (true) {
    case ($copy<>''):
        $fields = change_fields_for_copy($fields,'name');       
        $mode = "forms";
        break;
    case ($delete_file <> '') :
        delete_attached_file($file_field_name, $ID, 'projects');
        break;

        // **************
        // delete project
    case ($delete_b <> ''):
        manage_delete_records($ID, $module);
        break;

    case ($delete_c):
        if (isset($_REQUEST['ID_s'])) {
            $tmp = xss_array(explode(',', $_REQUEST['ID_s']));
            foreach ($tmp as $tmp_id) {
                $tmp_id = (int) $tmp_id;
                if (check_children($tmp_id, $module)) {
                    message_stack_in(__('project could not be deleted, because it has sub-projects'),"projects","error");
                }
                else if ($tmp_id > 0) {
                    manage_delete_records($tmp_id, $module);
                }
            }
        }
        else if (isset($_REQUEST['ID'])) {
            $tmp_id = (int) $_REQUEST['ID'];
            if (check_children($tmp_id, $module)) {
                message_stack_in(__('project could not be deleted, because it has sub-projects'),"projects","error");
            }
            else if ($tmp_id > 0) {
                manage_delete_records($tmp_id, $module);
            }
        }
        unset($tmp, $tmp_id);
        break;

        // *************
        // update status
    case ($modify_status_b and $ID > 0):
        if ($status <> '') {
            // check permission if you don't have chef level
            if (slookup('projekte', 'chef', 'ID', $ID,'1') <> $user_ID) $error = 1;
            // if the status is not between 0 and 100% or not a number -> forget it
            if (!is_numeric($status) or $status < 0 or $status > 100) {
                message_stack_in(__('please check the status!'),"projects","error");
                $error = 1;
            }
            if (!$error) {
                $result = db_query("UPDATE ".DB_PREFIX."projekte
                                       SET statuseintrag = '".date("Y-m-d")."',
                                           status = ".(int) $status."
                                     WHERE ID = ".(int)$ID) or db_die();
            }
        }
        break;

        // *************
        // update record
    case ($modify_b and $ID > 0):
    case ($modify_update_b and $ID > 0):
        // check permission
        
        // **********
        // set status
        if ($status <> '') {
            // check permission if you don't have chef level
            if (slookup('projekte', 'chef', 'ID', $ID,'1') <> $user_ID) $error = 1;
            // if the status is not between 0 and 100% or not a number -> forget it
            if (!is_numeric($status) or $status < 0 or $status > 100) {
                message_stack_in(__('please check the status!'), "projects", "error");
                $error = 1;
            }
            if (!$error) {
                $result = db_query("UPDATE ".DB_PREFIX."projekte
                                       SET statuseintrag = '".date("Y-m-d")."',
                                           status = ".(int) $status."
                                     WHERE ID = ".(int)$ID) or db_die();
            }
            $error = '';
        }

        check_anlegen();
        $perm_modify = check_perm_modify($ID, $module, 'acc');
        if ($perm_modify <> 'write' && $perm_modify <> 'owner') {
            $err_msg[] = 'You cannot modify the records with the ID '.$ID;
            $error = true;
        }
        // check whether the subproject has changed the parent and resides in a new branch -
        // in this case the next/previous entry and the dependency will be deleted
        if ($parent <> slookup('projekte', 'parent', 'ID', $ID,'1')) {
            unset($depend_mode);
            unset($depend_proj);
            // but also: scan for projects which have this project as next or dependency and delete this relations
            $result4 = db_query("UPDATE ".DB_PREFIX."projekte
                                    SET depend_proj=0,
                                        depend_mode=0
                                  WHERE depend_proj = ".(int)$ID) or db_die();
        }

        if (!$error) {
            // keep history
            if (PHPR_HISTORY_LOG > 0) {
                sqlstrings_create();
                history_keep('projekte', 'acc,acc_write,'.$sql_fieldstring, $ID);
            }
            $accessstring = update_access('projekte',$perm_modify=='owner'?$user_ID:0);
            $sql_string = sqlstrings_modify();

            $query = "UPDATE ".DB_PREFIX."projekte
                         SET $sql_string
                             $accessstring
                             gruppe      = ".(int)$user_group.",
                             parent      = ".(int)$parent.",
                             depend_mode = ".(int)$depend_mode.",
                             depend_proj = ".(int)$depend_proj.",
                             probability = ".(int)$probability."
                       WHERE ID = ".(int)$ID;
            $result = db_query($query) or db_die();

            // Participants
            if (is_array($personen)) {
                // update the user_rel table
                update_project_personen_table($ID, $personen,'user',xss_array($_POST));
            }

            // Contacts
            if (is_array($contact_personen)) {
                // update the contact_rel table
                update_project_personen_table($ID, $contact_personen,'contact',xss_array($_POST));
            }

            message_stack_in("$project_name: ".__('The project has been modified'), "projects", "notice");
            if ($modify_update_b) {
                $query_str = "projects.php?mode=forms&ID=".$ID."&justform=".$justform;
                header('Location: '.$query_str);
            }
        }
        break;

        // leave here on tree open/close mode
    case (isset($_GET['element_mode'])):
        break;

        // *************
        // insert record
    default:
        check_anlegen();

        sqlstrings_create();
        $accessstring = insert_access($module);

        $query = "INSERT INTO ".DB_PREFIX."projekte
                         (        von      ,        gruppe      ,        parent  ,        probability  ,   acc             ,  acc_write       , ".$sql_fieldstring." )
                  VALUES (".(int)$user_ID.",".(int)$user_group.",".(int)$parent.",".(int)$probability.", '$accessstring[0]','$accessstring[1]', ".$sql_valuestring." )";
        $result = db_query($query) or db_die();
        // message: project inserted
        message_stack_in("$project_name: ".__('The project is now in the list'), "projects", "notice");

        // find the ID of the last created user and assign it to ID
        $result = db_query("SELECT MAX(ID)
                              FROM ".DB_PREFIX."projekte
                             WHERE von = ".(int)$user_ID) or db_die();
        $row = db_fetch_row($result);
        $ID = $row[0];

        // Participants
        if (is_array($personen)) {
            // update the user_rel table
            update_project_personen_table($ID, $personen,'user',xss_array($_POST));
        }

        // Contacts
        if (is_array($contact_personen)) {
            // update the contact_rel table
            update_project_personen_table($ID, $contact_personen,'contact',xss_array($_POST));
        }

        if ($create_update_b) {
            $query_str = "projects.php?mode=forms&ID=".$ID."&justform=".$justform;
            header('Location: '.$query_str);
        }
        break;
}


function check_anlegen() {
    global $ende, $anfang, $sid, $ID, $wichtung, $project_name, $action, $error, $cat;
    global $depend_mode, $depend_proj, $project_name,$project_name, $chef, $note;
    global $contact, $stundensatz, $budget,  $parent, $probability;

    // check if end time is bigger than start time
    if ($ende < $anfang) die(__('The duration of the project is incorrect.')."!<br /><a href='projects.php?mode=forms&ID=$ID&name=$name&anfang=$anfang&ende=$ende&wichtung=$wichtung&chef=$chef&parent=$parent&note=$note&contact=$contact&stundensatz=$stundensatz&budget=$budget$sid'>".__('back')."</a> ");

    // if given, check whether budget and hourly rates are integer
    if ($budget <> '' and !is_numeric($budget)) die(__('Calculated budget').": ".__('Please check your date and time format! ')."!<br /><a href='projects.php?mode=forms&action=$action&ID=$ID&project_name=$project_name&anfang=$anfang&ende=$ende&wichtung=$wichtung&chef=$chef&parent=$parent&note=$note&contact=$contact&stundensatz=$stundensatz&budget=$budget$sid'>".__('back')."</a> ");
    if ($stundensatz <> '' and !is_numeric($stundensatz)) die(__('Calculated budget').": ".__('Please check your date and time format! ')."!<br /><a href='projects.php?mode=forms&action=$action&ID=$ID&project_name=$project_name&anfang=$anfang&ende=$ende&wichtung=$wichtung&chef=$chef&parent=$parent&note=$note&contact=$contact&stundensatz=$stundensatz&budget=$budget$sid'>".__('back')."</a> ");

    if ($parent > 0) {
        oerror($parent);
    }
    if ($ID > 0) {
        $resun = db_query("SELECT ID
                             FROM ".DB_PREFIX."projekte
                            WHERE parent = ".(int)$ID);
        $uproj[] = $arr_empt;
        while ($rowun = db_fetch_row($resun)) {
            if (!empty($rowun[0])) $uproj[] = $rowun[0];
        }
        if ($uproj) uerror($uproj);
    }

    // check dependencies
    if ($depend_mode > 1) $error = check_dependencies($ID, $depend_mode, $depend_proj, $cat, $project_name, $anfang, $ende);

    // probability check: in case the status is set to 'offered', the author can enter a percentage for the probability of a project
    // once the status turns into a higher level (e.g. ordered, at work etc.), the probability of course has to change to 'positive' = 100%
    settype($probability, "int");
    if ($cat > 1) $probability = 100;
}


function check_dependencies($ID, $depend_mode, $depend_proj, $cat, $project_name, $anfang, $ende) {
    global $dependencies, $categories;

    // fetch start and end date of the target project
    $result = db_query("SELECT anfang, ende, kategorie, name
                          FROM ".DB_PREFIX."projekte
                         WHERE ID = ".(int)$depend_proj) or db_die();
    $row = db_fetch_row($result);

    switch ($depend_mode) {

        // repeat the categiories here for those who don't want to have a look into the other skript:
        // 1=offered, 2=ordered, 3=at work, 4=ended, 5=stopped, 6=reopened 7 = waiting, 10=container, 11=ext. project
        // start means 'at work' or higher, but not 'waiting'
        // end means 'ended' or 'stopped'

        // 2 = this project cannot start before the end of project B
        case "2":
            // check logical
            if (($cat > "2" and $cat <> 7) and ($row[2] <> "4" and $row[2] <> "5")) {
                message_stack_in(__('Warning, violation of dependency').": $project_name \"$dependencies[$depend_mode] $row[3]\"<br />($row[3] = ".$categories[$row[2]].")","projects","error");
                $error = 1;
            }
            // check timeframe
            if ($anfang < $row[1]) {
                message_stack_in(__('Warning, violation of dependency').": ".__('Begin')."($project_name) < ".__('End')."($row[3])","projects","error");
            }
            break;

            // 3 = this project cannot start before start of project B
        case "3":
            if (($cat > "2" and $cat <> 7) and ($row[2] <= "2" or $row[2] == 7)) {
                message_stack_in(__('Warning, violation of dependency').": $project_name \"$dependencies[$depend_mode] $row[3]\"<br />($row[3] = ".$categories[$row[2]].")","projects","error");
                $error = 1;
            }
            // check timeframe
            if ($anfang < $row[0]) {
                message_stack_in(__('Warning, violation of dependency').": ".__('Begin')."($project_name) < ".__('Begin')."($row[3])","projects","error");
            }
            break;

            // 4 = this project cannot end before start of project B
        case "4":
            if (($cat == "4" or $cat == "5") and ($row[2] <= "2" or $row[2] == 7)) {
                message_stack_in(__('Warning, violation of dependency').": $project_name \"$dependencies[$depend_mode] $row[3]\"<br />($row[3] = ".$categories[$row[2]].")","projects","error");
                $error = 1;
            }
            // check timeframe
            if ($ende < $row[0]) {
                message_stack_in(__('Warning, violation of dependency').": ".__('End')."($project_name) < ".__('Begin')."($row[3])","projects","error");
            }
            break;

            // 5 = this project cannot end before end of project B
        case "5":
            if (($cat == "4" or $cat == "5")  and ($row[2] <> "4" and $row[2] <> "5")) {
                message_stack_in(__('Warning, violation of dependency').": $project_name \"$dependencies[$depend_mode] $row[3]\"<br />($row[3] = ".$categories[$row[2]].")","projects","error");
                $error = 1;
            }
            // check timeframe
            if ($ende < $row[1]) {
                message_stack_in(__('Warning, violation of dependency').": ".__('End')."($project_name) < ".__('End')."($row[3])","projects","error");
            }
            break;
    }
    return $error;
}


function delete_record($ID) {
    global $fields, $user_ID;

    // only if an ID is given of course ...
    if (!$ID) die(__('Please choose a project')."!<br /><a href='projects.php?".SID."'>".__('back')."</a>");

    // check whether there are subprojects below this record ..
    $result = db_query("SELECT ID
                          FROM ".DB_PREFIX."projekte
                         WHERE parent = ".(int)$ID) or db_die();
    $row = db_fetch_row($result);

    if ($row[0] > 0) {
        message_stack_in(__('Please delete all subelements first')."!","projects","error");
        return;
    }

    // delete project and show message
    $tmp = slookup('projekte','name','ID',$ID,'1');

    // delete corresponding entry from db_record
    remove_link($ID, 'projects');
    $result = db_query("DELETE
                          FROM ".DB_PREFIX."projekte
                         WHERE ID = ".(int)$ID) or db_die();
    message_stack_in($tmp." - ".__('The project is deleted'),"projects","notice");
    unset($tmp);

    // delete participants
    $result = db_query("DELETE
                          FROM ".DB_PREFIX."project_users_rel
                         WHERE project_ID = ".(int)$ID) or db_die();
    message_stack_in($tmp." - ".__('The project participants are deleted'),"projects","notice");

    // delete contacts
    $result = db_query("DELETE
                          FROM ".DB_PREFIX."project_contacts_rel
                         WHERE project_ID = ".(int)$ID) or db_die();
    message_stack_in($tmp." - ".__('The project contacts are deleted'),"projects","notice");


    // free events from project link

    if (PHPR_CALENDAR) {
        $result = db_query("UPDATE ".DB_PREFIX."termine
                               SET projekt = 0
                             WHERE projekt = ".(int)$ID) or db_die();
        message_stack_in(__('All links in events to this project are deleted'),"projects","notice");
    }

    // free files from project link
    if (PHPR_FILEMANAGER) {
        $result = db_query("UPDATE ".DB_PREFIX."dateien
                               SET div2 = 0
                             WHERE div2 = ".(int)$ID) or db_die();
    }
    // free notes from project link
    if (PHPR_NOTES) {
        $result = db_query("UPDATE ".DB_PREFIX."notes
                               SET projekt = 0
                             WHERE projekt = ".(int)$ID) or db_die();
    }
    // free timesheet from assignements from the timecard
    if (PHPR_PROJECTS > 1) {
        $result = db_query("DELETE
                              FROM ".DB_PREFIX."timeproj
                             WHERE projekt = ".(int)$ID) or db_die();
    }
    // free relations to this project
    $result = db_query("UPDATE ".DB_PREFIX."projekte
                           SET depend_mode = 0, depend_proj = 0
                         WHERE depend_proj = ".(int)$ID) or db_die();
    echo "<img src='".IMG_PATH."/s.gif' width='300px' height='1' vspace='2' border='0' alt='' />";

    // finally delete history
    if (PHPR_HISTORY_LOG > 0) history_delete('projekte', $ID);
}

// *******
// actions
// *******

if (!$justform) {
    if($mode=='view')$fields = build_array('projects', $ID, $mode);
    define('MODE_B',$mode);
	include_once("./projects_".MODE_B.".php");
}
else {
    echo '<script type="text/javascript">ReloadParentAndClose();</script>';
}

?>
