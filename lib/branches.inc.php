<?php

// branches.inc.php - PHProjekt Version 5.2
// copyright  ©  2000-2005 Albrecht Guenther  ag@phprojekt.com
// www.phprojekt.com
// Author: Albrecht Guenther, $Author: gustavo $
// $Id: branches.inc.php,v 1.16.2.2 2007/01/12 21:02:32 gustavo Exp $

// check whether the lib has been included - authentication!
if (!defined("lib_included")) die("Please use index.php!");


function copy_branch($ID, $new_parent_rootID) {
    if (check_access_subecord($ID)) {
        // first check whether the target isn't a subrecord of the source
        check_subrecord($ID,$new_parent_rootID);
        // first copy the root record
        $new_ID = copy_record($ID,$new_parent_rootID);
        // now fetch all children and walk thorugh the whole branch
        fetch_children($ID, $new_ID);
    } else {
        message_stack_in(__('You don\'t have write access on this project or subprojects'),"projects","error");
    }
}


function fetch_children($old_parent_ID, $new_parent_ID) {
    $result = db_query("SELECT ID
                          FROM ".DB_PREFIX."projekte
                         WHERE parent = ".(int)$old_parent_ID) or db_die();
    while ($row = db_fetch_row($result)) {
        $old_ID = $row[0];
        $new_ID = copy_record($row[0], $new_parent_ID);
        fetch_children($old_ID, $new_ID);
    }
}


function copy_record($ID, $parent_ID) {

    $result = db_query("SELECT ".implode(',', get_project_fields())."
                          FROM ".DB_PREFIX."projekte
                         WHERE ID = ".(int)$ID) or db_die();
    $row = db_fetch_row($result);

    // and insert this as a new record
    $result = db_query("INSERT INTO ".DB_PREFIX."projekte
                               (".implode(',', get_project_fields()).")
                        VALUES ('".implode("','", $row)."')") or db_die();

    // fetch the ID of this new record
    $result = db_query("SELECT max(ID)
                          FROM ".DB_PREFIX."projekte") or db_die();
    $row = db_fetch_row($result);

    // copy of users and contacts
    $result = db_query("SELECT contact_ID, role
                          FROM ".DB_PREFIX."project_contacts_rel
                         WHERE project_ID = ".(int)$ID) or db_die();

    while ($temp_row = db_fetch_row($result)) {


        $temp_result = db_query("INSERT INTO ".DB_PREFIX."project_contacts_rel
                                             (        project_ID,        contact_ID   ,        role         )
                                      VALUES (".(int)$row[0]."  ,".(int)$temp_row[0].",".(int)$temp_row[1].")") or db_die();
    }

    // at last, users
    $result = db_query("SELECT user_ID, role
                          FROM ".DB_PREFIX."project_users_rel
                         WHERE project_ID = ".(int)$ID) or db_die();

    while ($temp_row = db_fetch_row($result)) {


        $temp_result = db_query("INSERT INTO ".DB_PREFIX."project_users_rel
                                    (        project_ID  ,        user_ID      ,        role         )
                             VALUES (".(int)$row[0]."    ,".(int)$temp_row[0].",".(int)$temp_row[1].")") or db_die();
    }


    // don't forget to assign the new record to the current parent!
    $result = db_query("UPDATE ".DB_PREFIX."projekte
                           SET parent = ".(int)$parent_ID."
                         WHERE ID = ".(int)$row[0]) or db_die();
    return $row[0];
}


function check_subrecord($ID, $new_parent_rootID) {
    $proj_err_mes  = 'Sorry but the target record is a subrecord of the source! ';
    $proj_err_mes .= 'This would lead to a vanishing project branch. Please try it again';

    // check whether the current record is the target
    if ($ID == $new_parent_rootID) {
        die("$proj_err_mes");
    }
    else {
        // loop over children
        $result = db_query("SELECT ID
                              FROM ".DB_PREFIX."projekte
                             WHERE parent = ".(int)$ID) or db_die();
        while ($row = db_fetch_row($result)) {
            check_subrecord($row[0], $new_parent_rootID);
        }
    }
}

function check_access_subecord($ID) {
    global $user_ID,$user_kurz;

    $access = true;
    $result = db_query("SELECT acc
                          FROM ".DB_PREFIX."projekte
                         WHERE (acc LIKE 'system' 
                                OR ((von = ".(int)$user_ID."
                                OR acc LIKE 'group'
                                OR acc LIKE '%\"$user_kurz\"%'))
                                AND acc_write = 'w'
                                AND ID = ".(int)$ID.")") or db_die();
    $row = db_fetch_row($result);
    if (!empty($row)) {
        $result2 = db_query("SELECT ID
                               FROM ".DB_PREFIX."projekte
                              WHERE parent = ".(int)$ID) or db_die();
        while ($row2 = db_fetch_row($result2)) {
            $access = check_access_subecord($row2[0]);
        }
    } else {
        $access = false;
    }

    return $access;
}

function move_branch($ID, $field, $days) {
    if (check_access_subecord($ID)) {
        // get the value
        $result = db_query("SELECT ".qss($field)."
                              FROM ".DB_PREFIX."projekte
                             WHERE ID = ".(int)$ID) or db_die();
        $row = db_fetch_row($result);
        $olddate = explode('-',$row[0]);
        $newdate = date('Y-m-d', mktime(0, 0, 0, $olddate[1], $olddate[2]+$days, $olddate[0]));
        $result = db_query("UPDATE ".DB_PREFIX."projekte
                               SET ".qss($field)." = '$newdate'
                             WHERE ID = ".(int)$ID) or db_die();
        // loop over children
        $result = db_query("SELECT ID
                              FROM ".DB_PREFIX."projekte
                             WHERE parent = ".(int)$ID) or db_die();
        while ($row = db_fetch_row($result)) {
            move_branch($row[0], $field, $days);
        }
    } else {
        message_stack_in(__('You don\'t have write access on this project or subprojects'),"projects","error");
    }
}


function get_project_fields() {
    $project_fields = array('div1','div2','acc','acc_write','gruppe');
    $query = "SELECT db_name
                FROM ".DB_PREFIX."db_manager
               WHERE db_table = 'projekte'";
    $result = db_query($query) or db_die();
    while ($row = db_fetch_row($result)) { $project_fields[] = $row[0]; }
    return $project_fields;
}
?>
