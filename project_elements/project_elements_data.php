<?php
/**
 * project_elements db data script
 *
 * @package    projects
 * @subpackage project_elements
 * @author     Nina Schmitt
 * @licence    GPL, see www.gnu.org/copyleft/gpl.html
 * @copyright  2000-2006 Mayflower GmbH www.mayflower.de
 * @version    
 */

if (!defined('lib_included')) die('Please use index.php!');
define('PATH_PRE','../');
include_once(PATH_PRE.'lib/permission.inc.php');
if ($cancel) {}

if ($delete_b) {
    delete_record($ID, $project_ID);
}
else if ($copy){
    $fields = change_fields_for_copy($fields,'name'); 
}
else if (!$ID) {
    sqlstrings_create();
    $query = "INSERT INTO ".DB_PREFIX."project_elements
                          (project_ID, von,".$sql_fieldstring." )
                   VALUES ('$project_ID',$user_ID,".$sql_valuestring.")";
    $result = db_query($query) or db_die();                   

    if ($create_update_b) {
        // find the ID of the last created user and assign it to ID
        $result = db_query("SELECT MAX(ID)
                            FROM ".DB_PREFIX."project_elements
                            WHERE von = ".(int)$user_ID) or db_die();
        $row = db_fetch_row($result);
        $ID = $row[0];
        $query_str = "project_elements.php?mode=forms&ID=".$ID."&justform=".$justform;
        header('Location: '.$query_str);
    }
}
else if ($ID > 0) {
    //$perm_modify = check_perm_modify($ID, $module, 'acc');
    $perm_modify='write';
    if ($perm_modify <> 'write' && $perm_modify <> 'owner') {
        $err_msg[] = 'You cannot modify the records with the ID '.$ID;
        $error = true;
    }
    if (!$error) {
        // keep history
        if (PHPR_HISTORY_LOG > 0) {
            sqlstrings_create();
            history_keep('project_elements', $sql_fieldstring, $ID);
        }
        //$accessstring = update_access('notes',$perm_modify=='owner'?$user_ID:0);
        $sql_string = sqlstrings_modify();
        // update record in db
        $query = "UPDATE ".DB_PREFIX."project_elements
                     SET $sql_string 
                     von = ".(int)$user_ID."
                     WHERE ID = ".(int)$ID;
        $result = db_query($query) or db_die();                         
    }
    if ($modify_update_b) {
        $query_str = "project_elements.php?mode=forms&ID=".$ID."&justform=".$justform;
        header('Location: '.$query_str);
    }
}


if (!$justform) {
  $mode = 'forms';
  include_once("./project_elements_forms.php");
}

else {
    echo '<script type="text/javascript">ReloadParentAndClose();</script>';
}


function delete_record($ID, $project_ID) {
    global $fields, $user_ID;

    // check permission
    $result = db_query("SELECT von, acc_write
                          FROM ".DB_PREFIX."projekte
                         WHERE ID = ".(int)$project_ID) or db_die();
    $row = db_fetch_row($result);
    if ($row[0] == 0) die("no entry found.");
    if ($row[0] <> $user_ID and !$row[1]) die("You are not privileged to do this!");

    // delete record in db
    $result = db_query("DELETE FROM ".DB_PREFIX."project_elements
                                  WHERE ID = ".(int)$ID) or db_die();
    // delete history for this db entry
    if (PHPR_HISTORY_LOG > 0) history_delete('project_elements', $ID);
}
?>
