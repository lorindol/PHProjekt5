<?php

// notes_data.php - PHProjekt Version 5.2
// copyright  ©  2000-2005 Albrecht Guenther  ag@phprojekt.com
// www.phprojekt.com
// Author: Albrecht Guenther, $Author: gustavo $
// $Id: notes_data.php,v 1.38.2.1 2007/01/13 15:00:46 gustavo Exp $

// check whether the lib has been included - authentication!
if (!defined("lib_included")) die("Please use index.php!");

// check role
if (check_role("notes") < 2) die("You are not allowed to do this!");

// check form token
check_csrftoken();

include_once(LIB_PATH."/permission.inc.php");

if (!$parent) $parent = 0;
$error=false;
if ($cancel) {}

if ($delete_b) {
    manage_delete_records($ID, $module);
}
else if ($copy){
    $fields = change_fields_for_copy($fields,'name'); 
}
else if ($delete_c) {
  if      ($ID > 0)     manage_delete_records($ID, $module);
  else if ($ID_s <> '') manage_delete_records($ID_s, $module);
}
// delete a file attached to a record
else if ($delete_file) {
    delete_attached_file($file_field_name, $ID, 'notes');
}
else if (!$ID) {
    $accessstring = insert_access('notes');
    sqlstrings_create();
    $query = "INSERT INTO ".DB_PREFIX."notes
                     (        gruppe      ,        von      ,        parent  , sync1     ,  sync2    ,  acc             ,  acc_write       ,".$sql_fieldstring.")
              VALUES (".(int)$user_group.",".(int)$user_ID.",".(int)$parent.",'$dbTSnull','$dbTSnull','$accessstring[0]','$accessstring[1]',".$sql_valuestring.")";
    $result = db_query($query) or db_die();

    if ($create_update_b) {
        // find the ID of the last created user and assign it to ID
        $result = db_query("SELECT MAX(ID)
                              FROM ".DB_PREFIX."notes
                             WHERE von = ".(int)$user_ID) or db_die();
        $row = db_fetch_row($result);
        $ID = $row[0];
        $query_str = "notes.php?mode=forms&ID=".$ID."&justform=".$justform;
        header('Location: '.$query_str);
    }
}
else if ($ID > 0) {
    $perm_modify = check_perm_modify($ID, $module, 'acc');
    if ($perm_modify <> 'write' && $perm_modify <> 'owner') {
        $err_msg[] = 'You cannot modify the records with the ID '.$ID;
        $error = true;
    }
    if (!$error) {
        // keep history
        if (PHPR_HISTORY_LOG > 0) {
            sqlstrings_create();
            history_keep('notes', 'acc,acc_write,'.$sql_fieldstring, $ID);
        }
        $accessstring = update_access('notes',$perm_modify=='owner'?$user_ID:0);
        $sql_string = sqlstrings_modify();
        // update record in db
        $query = "UPDATE ".DB_PREFIX."notes
                     SET $sql_string
                         parent = ".(int)$parent.",
                         $accessstring
                         sync2 = '$dbTSnull'
                   WHERE ID = ".(int)$ID;
        $result = db_query($query) or db_die();                         
    }
    if ($modify_update_b) {
        $query_str = "notes.php?mode=forms&ID=".$ID."&justform=".$justform;
        header('Location: '.$query_str);
    }
}

if ($copy){
   include_once("./notes_forms.php");
}
else if (!$justform) {
  $ID = 0;
  $mode = 'view';
  include_once("./notes_view.php");
}

else {
    echo '<script type="text/javascript">ReloadParentAndClose();</script>';
}


function delete_record($ID) {
    global $fields, $user_ID;

    // check permission
    $result = db_query("SELECT von, acc_write
                          FROM ".DB_PREFIX."notes
                         WHERE ID = ".(int)$ID) or db_die();
    $row = db_fetch_row($result);
    if ($row[0] == 0) die("no entry found.");
    if ($row[0] <> $user_ID and !$row[1]) die("You are not privileged to do this!");

    // check whether there are subelements below this record ..
    $result = db_query("SELECT ID
                          FROM ".DB_PREFIX."notes
                         WHERE parent = ".(int)$ID) or db_die();
    $row = db_fetch_row($result);
    if ($row[0] > 0) {
        message_stack_in(__('Please delete all subelements first')."!", "notes", "error");
    }
    // no sub element? -> delete!
    else {
        // delete all files associated with this record
        foreach ($fields as $field_name=>$field) {
            if ($field['form_type'] == 'upload') {
                $sql_value = upload_file_delete($field_name, $ID, 'notes');
            }
        }
        // delete record in db
        $result = db_query("DELETE
                              FROM ".DB_PREFIX."notes
                             WHERE ID = ".(int)$ID) or db_die();
        // delete corresponding entry from db_record
        remove_link($ID, 'notes');
        // delete history for this db entry
        if (PHPR_HISTORY_LOG > 0) history_delete('notes', $ID);

    }
}

?>
