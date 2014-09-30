<?php

// links_data.php - PHProjekt Version 5.2
// copyright  ©  2000-2005 Albrecht Guenther  ag@phprojekt.com
// www.phprojekt.com
// Author: Albrecht Guenther, $Author: gustavo $
// $Id: links_data.php,v 1.26.2.1 2007/01/13 16:42:00 gustavo Exp $

// check whether the lib has been included - authentication!
if (!defined("lib_included")) die("Please use index.php!");

// check role ... check deactivated since we do not see any security problem
// if (check_role("links") < 2) die("You are not allowed to do this!");

// check form token
check_csrftoken();

include_once(LIB_PATH."/permission.inc.php");

if (!$parent) $parent = 0;
if ($cancel) {}
if (isset($ID_s)) {
    $ID = $ID_s;
}

if ($delete_b and $ID <> '') {
    manage_delete_records($ID, $module);
}
// delete a file attached to a record
else if ($delete_file) {
    delete_attached_file($file_field_name, $ID, 'links');
}
else if ($create_b) {
    sqlstrings_create();
    $query = "INSERT INTO ".DB_PREFIX."links
                          (             gruppe ,       von,       archiv,".$sql_fieldstring.")
                   VALUES (".(int)$user_group.",".(int)$user_ID.",0     ,".$sql_valuestring.")";
    $result = db_query($query) or db_die();
}
else if (($modify_b and $ID > 0) || ($modify_update_b and $ID > 0 )) {
    // check permission
    $result = db_query("SELECT t_ID, t_author, t_acc
                          FROM ".DB_PREFIX."db_records
                         WHERE t_ID = ".(int)$ID." 
                           AND (t_acc LIKE 'system'
                                OR ((t_author = ".(int)$user_ID." 
                                     OR t_acc LIKE 'group'
                                     OR t_acc LIKE '%\"$user_kurz\"%') ))") or db_die();
    $row = db_fetch_row($result);
    if (!$row[0] or ($row[1] <> $user_ID and $row[2] <> 'w')) die("You are not allowed to do this");

    $sql_string = sqlstrings_modify();
    // update record in db
    $query = "UPDATE ".DB_PREFIX."db_records
                 SET $sql_string
                     t_author = ".(int)$user_ID."
               WHERE t_ID = ".(int)$ID;
    $result = db_query($query) or db_die();

    if ($modify_update_b) {
        $query_str = "links.php?mode=forms&ID=".$ID;
        header('Location: '.$query_str);
    }
}

$ID = 0;

$fields = build_array('links', $ID, 'view');
include_once("./links_view.php");


function delete_record($ID) {
    global $user_ID;

    // check permission
    $result = db_query("SELECT t_author, t_acc
                          FROM ".DB_PREFIX."db_records
                         WHERE t_ID = ".(int)$ID) or db_die();
    $row = db_fetch_row($result);
    if ($row[0] == 0) {
        echo "no entry found.";
        $error = 1;
    }
    if ($row[0] <> $user_ID and !$row[1]) {
        echo "You are not privileged to do this!";
        $error = 1;
    }
    if (!$error) {
        // delete record in db
        $result = db_query("DELETE FROM ".DB_PREFIX."db_records
                                  WHERE t_ID = ".(int)$ID) or db_die();
    }
}

?>
