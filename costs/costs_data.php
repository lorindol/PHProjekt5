<?php
/**
 * @package    cost
 * @subpackage main
 * @author     Gustavo Solt, $Author: polidor $
 * @licence    GPL, see www.gnu.org/copyleft/gpl.html
 * @copyright  2000-2006 Mayflower GmbH www.mayflower.de
 * @version    $Id: cost_data.php
 */

// check whether the lib has been included - authentication!
if (!defined("lib_included")) die("Please use index.php!");

// check role
if (check_role("costs") < 2) die("You are not allowed to do this!");

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
else if (!$ID) {
    $accessstring = insert_access('costs');
    sqlstrings_create();
    $query = "INSERT INTO ".DB_PREFIX."costs
                     (        gruppe      ,        von      ,        parent  ,  sync1    ,  sync2    ,  acc             ,  acc_write       ,".$sql_fieldstring.")
              VALUES (".(int)$user_group.",".(int)$user_ID.",".(int)$parent.",'$dbTSnull','$dbTSnull','$accessstring[0]','$accessstring[1]',".$sql_valuestring.")";
    $result = db_query($query) or db_die();

    if ($create_update_b) {
        // find the ID of the last created user and assign it to ID
        $result = db_query("SELECT MAX(ID)
                              FROM ".DB_PREFIX."costs
                             WHERE von = ".(int)$user_ID."
                               AND is_deleted is NULL") or db_die();
        $row = db_fetch_row($result);
        $ID = $row[0];
        $query_str = "costs.php?mode=forms&ID=".$ID."&justform=".$justform;
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
            history_keep('costs', 'acc,acc_write,'.$sql_fieldstring, $ID);
        }
        $accessstring = update_access('costs',$perm_modify=='owner'?$user_ID:0);
        $sql_string = sqlstrings_modify();
        // update record in db
        $query = "UPDATE ".DB_PREFIX."costs
                     SET $sql_string
                         parent = ".(int)$parent.",
                         $accessstring
                         sync2 = '$dbTSnull'
                   WHERE ID = ".(int)$ID."
                     AND is_deleted is NULL";
        $result = db_query($query) or db_die();                         
    }
    if ($modify_update_b) {
        $query_str = "costs.php?mode=forms&ID=".$ID."&justform=".$justform;
        header('Location: '.$query_str);
    }
}

if ($copy){
   include_once("./costs_forms.php");
}
else if (!$justform) {
  $ID = 0;
  $mode = 'view';
  include_once("./costs_view.php");
}

else {
    echo '<script type="text/javascript">ReloadParentAndClose();</script>';
}


function delete_record($ID) {
    global $fields, $user_ID;

    // check permission
    $result = db_query("SELECT von, acc_write
                          FROM ".DB_PREFIX."costs
                         WHERE ID = ".(int)$ID."
                           AND is_deleted is NULL") or db_die();
    $row = db_fetch_row($result);
    if ($row[0] == 0) die("no entry found.");
    if ($row[0] <> $user_ID and !$row[1]) die("You are not privileged to do this!");

    // check whether there are subelements below this record ..
    $result = db_query("SELECT ID
                          FROM ".DB_PREFIX."costs
                         WHERE parent = ".(int)$ID."
                           AND is_deleted is NULL") or db_die();
    $row = db_fetch_row($result);
    if ($row[0] > 0) {
        message_stack_in(__('Please delete all subelements first')."!", "costs", "error");
    }
    // no sub element? -> delete!
    else {
        // delete all files associated with this record
        foreach ($fields as $field_name=>$field) {
            if ($field['form_type'] == 'upload') {
                $sql_value = upload_file_delete($field_name, $ID, 'costs');
            }
        }
        // delete record in db
        delete_record_id('costs','WHERE ID = '.(int)$ID);
        // delete corresponding entry from db_record
        remove_link($ID, 'costs');
        // delete history for this db entry
        if (PHPR_HISTORY_LOG > 0) history_delete('costs', $ID);

    }
}

?>
