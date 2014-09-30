<?php
/**
 * organisations db data handling script
 *
 * @package    organisations
 * @subpackage organisations
 * @author     Gustavo Solt, $Author: nina $
 * @licence    GPL, see www.gnu.org/copyleft/gpl.html
 * @copyright  2000-2006 Mayflower GmbH www.mayflower.de
 * @version    $Id: 
 */

if (!defined('lib_included')) die('Please use index.php!');

// check role
if (check_role('organisations') < 2) die('You are not allowed to do this!');

// check form token
check_csrftoken();

// prepare array for doublet check
if ($doublet_action <> '' and count($doublet_fields) > 0) $ex_records = fetch_organisations();

// *************
// start actions

// Clear the message_stack
unset ($_SESSION['message_stack'][$module]);

// ****************************
// organisations operations
if ($cancel_b) {}
// delete a file attached to a record
else if ($delete_b) {
    if ($ID > 0) manage_delete_records($ID, $module);
    else if ($ID_s <> '')  manage_delete_records($ID_s, $module);
}
else if ($modify_b || $modify_update_b) {
    // check permission
    $result = db_query("SELECT ID, von, acc_write
                          FROM ".DB_PREFIX."organisations
                         WHERE ID = ".(int)$ID." AND
                               is_deleted is NULL AND
                               (acc LIKE 'system' OR ((von = ".(int)$user_ID." OR acc LIKE 'group' OR acc LIKE '%\"$user_kurz\"%') AND $sql_user_group))") or db_die();
    $row = db_fetch_row($result);
    if (!$row[0] or ($row[1] <> $user_ID and $row[2] <> 'w')) die ("You are not allowed to do this");

    // check whether this user is author of this record - if yes, allow him to change the permission status
    // otherwise don't change this field
    $accessstring = update_access($module,$row[1]);

    // keep history
    if (PHPR_HISTORY_LOG  > 0) {
        sqlstrings_create();
        history_keep('organisations', 'acc,acc_write,'.$sql_fieldstring, $ID);
    }
    $sql_string = sqlstrings_modify();
    // update record in db
    $result = db_query("UPDATE ".DB_PREFIX."organisations
                           SET $sql_string
                               parent = ".(int)$parent.",
                               $accessstring
                               sync2 = '$dbTSnull'
                         WHERE ID = ".(int)$ID) or db_die();

    // Contacts
    if (isset($contact_personen)) {
        update_organisation_personen_table(array($ID), $contact_personen, $_POST);
    }

    message_stack_in(__('The data set is now modified')." :-)", "organisations", "notice");
}
else if ($create_b || $create_update_b) {
    $accessstring = insert_access($module);
    if ($acc_write <> '') $acc_write = 'w';
    sqlstrings_create();
    $result = db_query("INSERT INTO ".DB_PREFIX."organisations
                                        (             gruppe,               von,          parent,     sync1,       sync2,               acc,         acc_write, ".$sql_fieldstring." )
                                 VALUES (".(int)$user_group.",".(int)$user_ID.",".(int)$parent.",'$dbTSnull','$dbTSnull','$accessstring[0]','$accessstring[1]', ".$sql_valuestring.")") or db_die();

    // Contacts
    if (isset($contact_personen)) {
        update_organisation_personen_table(array($ID), $contact_personen, $_POST);
    }

    message_stack_in(__('The new organisation has been added')." :-)", "organisations", "notice");
}

if ($create_update_b) {
    // find the ID of the last created user and assign it to ID
    $result = db_query("SELECT MAX(ID)
                        FROM ".DB_PREFIX."organisations
                        WHERE von = ".(int)$user_ID) or db_die();
    $row = db_fetch_row($result);
    $ID = $row[0];
    $query_str = "organisations.php?mode=forms&ID=".$ID;
    header('Location: '.$query_str);
} else if ($modify_update_b) {
    $query_str = "organisations.php?mode=forms&ID=".$ID;
    header('Location: '.$query_str);
}
else if ($copy){
    $fields = change_fields_for_copy($fields,'name'); 
    include_once("./organisations_forms.php");
}
else {
    // annoying but it has to be done once again - building the array of fields, but this time in the order of the list view
    $fields = build_array('organisations', $ID, 'view');
    include_once("./organisations_view.php");
}
// *************
// function area

// fetch organisations of this user into array for later doublet check
function fetch_organisations() {
    global $doublet_fields, $user_ID;

    settype($doublet_fields, "array");
    $new_val = array();
    foreach ($doublet_fields as $a_val) {
        $new_val[] = qss($a_val);
    }

    $result2 = db_query("SELECT ID, ".implode(',', $new_val)."
                           FROM ".DB_PREFIX."organisations
                          WHERE von = ".(int)$user_ID."
                            AND is_deleted is NULL") or db_die();
    while ($row2 = db_fetch_row($result2)) {
        $ex_records[$row2[0]]['ID'] = $row2[0];
        $i = 1;
        foreach ($doublet_fields as $doublet_field) {
            $ex_records[$row2[0]][$doublet_field] = $row2[$i];
            $i++;
        }
    }
    return $ex_records;
}

function delete_record($ID) {
    global $fields, $user_ID;

    // check permission
    $result = db_query("SELECT von, acc_write, name
                          FROM ".DB_PREFIX."organisations
                         WHERE ID = ".(int)$ID."
                           AND is_deleted is NULL") or db_die();
    $row = db_fetch_row($result);
    if ($row[0] == 0) {
        message_stack_in("no entry found","organisations","error");
        $error = 1;
    }
    if ($row[0] <> $user_ID and !$row[1]) {
        message_stack_in("No privilege to delete organisation!", "organisations", "error");
        $error = 1;
    }

    if (!$error) {
        // delete record in db
        delete_record_id('organisations',"WHERE ID = ".(int)$ID);
        // delete corresponding entry from db_record
        remove_link($ID, 'organisations');
        message_stack_in( $row[2].": ".__('The organisation has been deleted').".  ","organisations","notice");
        
        // finally delete history
        history_delete('organisations', $ID);
    }
}
?>
