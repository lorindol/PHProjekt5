<?php
/**
* contacts db data handling script
*
* @package    contacts
* @module     external contacts
* @author     Albrecht Guenther, $Author: polidor $
* @licence    GPL, see www.gnu.org/copyleft/gpl.html
* @copyright  2000-2006 Mayflower GmbH www.mayflower.de
* @version    $Id: contacts_data.php,v 1.54.2.4 2007/03/13 04:13:57 polidor Exp $
*/
if (!defined('lib_included')) die('Please use index.php!');

// check role
if (check_role('contacts') < 2) die('You are not allowed to do this!');

// check form token
check_csrftoken();

// prepare array for doublet check
if ($doublet_action <> '' and count($doublet_fields) > 0) $ex_records = fetch_contacts();

// check whether the name field is empty
$nachname = check_lastname($nachname, $firma);

// *************
// start actions

// Clear the message_stack
unset ($_SESSION['message_stack'][$module]);

// ***************
// import routines
if ($imp_approve) {
    if ($ID > 0) {
        $result = db_query("UPDATE ".DB_PREFIX."contacts
                               SET import = '0',
                                   sync2  = '$dbTSnull'
                             WHERE ID = ".(int)$ID) or db_die();
    }
    // approve the whole list
    else {
        $result = db_query("UPDATE ".DB_PREFIX."contacts
                               SET import = '0',
                                   sync2  = '$dbTSnull'
                             WHERE von = ".(int)$user_ID."
                               AND $sql_user_group
                               AND import = '1'") or db_die();
        message_stack_in(__('The list has been imported.'), "contacts", "notice");
        $approve_contacts = '';
        $_SESSION['approve_contacts'] =& $approve_contacts;
    }
}
else if ($imp_undo) {
    // remove a single entry from the import list
    if ($ID > 0) {
        $result = db_query("DELETE
                              FROM ".DB_PREFIX."contacts
                             WHERE ID = ".(int)$ID) or db_die();

        // delete corresponding entry from db_record
        remove_link($ID, 'contacts');
    }
    // remove the complete import list
    else {
        $result = db_query("DELETE
                              FROM ".DB_PREFIX."contacts
                             WHERE von = ".(int)$user_ID."
                               AND $sql_user_group
                               AND import = '1'") or db_die();
        message_stack_in(__('The list has been rejected.'), "contacts", "notice");
        $approve_contacts = '';
        $_SESSION['approve_contacts'] =& $approve_contacts;
    }
}
else if ($import_contacts) {
    include_once('./contacts_import_data.php');
}
// end import
// **********

// separate check: for insert or modify, update the profiles
if (PHPR_CONTACTS_PROFILES and !$cancel) {
    // case update first delete all existing records connected with this contact (very dangerous if the system crashes! :-()
    // case delete: you can delete the entries anyway :-)
    if ($modify_b  or $modify_update_b or $modify_contact) {
        $result = db_query("SELECT ID
                              FROM ".DB_PREFIX."contacts_profiles
                             WHERE von = ".(int)$user_ID) or db_die();
        $profile_ids = "0";
        while($row = db_fetch_row($result)) {
            $profile_ids .= ",$row[0]";
        }
        $result = db_query("DELETE
                              FROM ".DB_PREFIX."contacts_prof_rel
                             WHERE contact_ID = ".(int)$ID ."
                               AND contacts_profiles_ID IN ($profile_ids)") or db_die();
    }
    else if ($create_b or $create_update_b or $create_contact) {
        // find the ID of the last created user and assign it to ID
        $result = db_query("SELECT MAX(ID)
                              FROM ".DB_PREFIX."contacts
                             WHERE von = ".(int)$user_ID) or db_die();
        $row = db_fetch_row($result);
        $ID = $row[0];
    }
    //now insert all selected profiles, but only if the contacts shouldn't be deleted
    if (!$delete_b and $profile_lists[0] > 0) {
        foreach ($profile_lists as $profile) {
            $result = db_query("INSERT INTO ".DB_PREFIX."contacts_prof_rel
                                       (        contact_ID,        contacts_profiles_ID)
                                VALUES (".(int)$ID."      ,".(int)$profile."           )") or db_die();
        }
    }
}


// store the selection as a result of the current filter list
if ($action == 'store_selection') {
    $sql_user_group = "(".DB_PREFIX."contacts.gruppe = ".(int)$user_group.")";

    // 1. step: create a new profile with the timestamp as the name
    foreach ($flist['contacts'] as $key => $p_filter) {
        foreach ($fields as $field_name => $field) {
            if ($field_name == $p_filter[0]) {
                $filtername = enable_vars($field['form_name']);
            }
        }
        $filters .= $filtername." ".$p_filter[1]." ".$p_filter[2]." | ";
    }
    $result = db_query("INSERT INTO ".DB_PREFIX."contacts_profiles
                                        (        von      ,   name                        ,  remark)
                                 VALUES (".(int)$user_ID.",'".show_iso_date1($dbTSnull)."','".xss($filters)."')") or db_die();
    // fetch the ID number for later reference
    $result = db_query("SELECT MAX(ID)
                          FROM ".DB_PREFIX."contacts_profiles
                         WHERE von = ".(int)$user_ID) or db_die();
    $row = db_fetch_row($result);

    // build the where clause
    foreach ($flist['contacts'] as $key => $p_filter) {
        $where .= ' and (';
        // if the field string is 'all', it has to belloped over all applicable fields
        if ($p_filter[0] == 'all') $where .= apply_full_filter($p_filter[1], $p_filter[2], 'contacts');
        else $where .= apply_filter($p_filter[0], $p_filter[1], $p_filter[2], 'contacts');
        $where .= ')';
    }

    // do the query
    $result2 = db_query("SELECT ID
                           FROM ".DB_PREFIX."contacts
                          WHERE (acc_read LIKE 'system'
                                 OR ((von = ".(int)$user_ID."
                                 OR acc_read LIKE 'group'
                                 OR acc_read LIKE '%\"$user_kurz\"%')
                                 AND $sql_user_group))
                                 $where") or db_die();
    while ($row2 = db_fetch_row($result2)) {
        $result3 = db_query("INSERT INTO ".DB_PREFIX."contacts_prof_rel
                                    (        contact_ID,        contacts_profiles_ID)
                             VALUES (".(int)$row2[0]." ,".(int)$row[0]."            )") or db_die();
    }
}
else if ($action == 'store_filter') {
    save_filter('contacts', date('Y-m-d'));
}
else if ($action == 'load_filter') {}


// ****************************
// external contacts operations

if ($cancel_b) {}
// delete a file attached to a record
else if ($delete_file) delete_attached_file($file_field_name, $ID, 'contacts');
else if ($delete_b) {
    if ($ID > 0) manage_delete_records($ID, $module);
    else if ($ID_s <> '')  manage_delete_records($ID_s, $module);
}
else if ($modify_b || $modify_update_b) {
    // check permission
    $result = db_query("SELECT ID, von, acc_write
                          FROM ".DB_PREFIX."contacts
                         WHERE ID = ".(int)$ID."
                           AND (acc_read LIKE 'system'
                                OR ((von = ".(int)$user_ID."
                                OR acc_read LIKE 'group'
                                OR acc_read LIKE '%\"$user_kurz\"%')
                                AND $sql_user_group))") or db_die();
    $row = db_fetch_row($result);
    if (!$row[0] or ($row[1] <> $user_ID and $row[2] <> 'w')) die ("You are not allowed to do this");

    // check whether this user is author of this record - if yes, allow him to change the permission status
    // otherwise don't change this field
    $accessstring = update_access($module,$row[1], 'acc_read');

    // keep history
    if (PHPR_HISTORY_LOG  > 0) {
        sqlstrings_create();
        history_keep('contacts', 'acc_read,acc_write,'.$sql_fieldstring, $ID);
    }
    $sql_string = sqlstrings_modify();
    // update record in db
    $result = db_query("UPDATE ".DB_PREFIX."contacts
                           SET $sql_string
                               import = '0',
                               parent = ".(int)$parent.",
                               $accessstring
                               sync2 = '$dbTSnull'
                         WHERE ID = ".(int)$ID) or db_die();

    if (is_array($project_personen)) {
        // update the contact_rel table
        update_project_personen_table($ID, $project_personen,'project');

        foreach($project_personen as $tmp_id => $project_ID) {
            if (isset($_POST[$project_ID."_text_role"]) || isset($_POST[$project_ID."_role"]) ) {
                if($_POST[$project_ID."_text_role"] != '')  $role = xss($_POST[$project_ID."_text_role"]);
                else                                        $role = xss($_POST[$project_ID."_role"]);
                $role = xss($role);
                db_query("UPDATE ".DB_PREFIX."project_contacts_rel
                             SET role        = '$role'
                           WHERE contact_ID  = ".(int)$ID."
                             AND project_ID  = ".(int)$project_ID);
            }
        }
    }
   message_stack_in(__('The data set is now modified')." :-)", "contacts", "notice");
}
else if ($create_b || $create_update_b) {
    $accessstring = insert_access($module);
    if ($acc_write <> '') $acc_write = 'w';
    sqlstrings_create();
    $result = db_query("INSERT INTO ".DB_PREFIX."contacts
                               (        gruppe      ,        von      ,        parent ,   sync1    ,  sync2    ,  acc_read        , acc_write        , ".$sql_fieldstring." )
                        VALUES (".(int)$user_group.",".(int)$user_ID.",".(int)$parent.",'$dbTSnull','$dbTSnull','$accessstring[0]','$accessstring[1]', ".$sql_valuestring.")") or db_die();
    message_stack_in(__('The new contact has been added')." :-)", "contacts", "notice");
}

if ($create_update_b) {
    // find the ID of the last created user and assign it to ID
    $result = db_query("SELECT MAX(ID)
                          FROM ".DB_PREFIX."contacts
                         WHERE von = ".(int)$user_ID) or db_die();
    $row = db_fetch_row($result);
    $ID = $row[0];
    $query_str = "contacts.php?action=contacts&mode=forms&ID=".$ID;
    header('Location: '.$query_str);
} else if ($modify_update_b) {
    $query_str = "contacts.php?action=contacts&mode=forms&ID=".$ID;
    header('Location: '.$query_str);
}
else if ($copy){
    $fields = change_fields_for_copy($fields,'nachname');
    include_once("./contacts_forms.php");
}
else {
    // annoying but it has to be done once again - building the array of fields, but this time in the order of the list view
    $fields = build_array('contacts', $ID, 'view');
    include_once("./contacts_view.php");
}
// *************
// function area

// fetch contacts of this user into array for later doublet check
function fetch_contacts() {
    global $doublet_fields, $user_ID;

    settype($doublet_fields, "array");
    $new_val = array();
    foreach ($doublet_fields as $a_val) {
        $new_val[] = qss($a_val);
    }

    $result2 = db_query("SELECT ID, ".implode(',', $new_val)."
                           FROM ".DB_PREFIX."contacts
                          WHERE von = ".(int)$user_ID) or db_die();
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


function check_for_doublettes($imp_records, $ex_records) {
    global $doublet_fields;

    if (!$ex_records) { return 0; }
    foreach ($ex_records as $ex_record) {
        $failed = 0;
        if ($doublet_fields) {
            foreach ($doublet_fields as $doublet_field) {
                if ($imp_records[$doublet_field] <> $ex_record[$doublet_field]) $failed = 1;
            }
            if (!$failed) return $ex_record['ID'];
        }
    }
    return 0;
}


// if no last name is given - e.g. during import - use the company name
function check_lastname($nachname, $firma) {
    $nachname ? $nachname : $nachname = $firma;
    return $nachname;
}


function delete_record($ID) {
    global $fields, $user_ID;

    // check permission
    $result = db_query("SELECT von, acc_write, nachname
                          FROM ".DB_PREFIX."contacts
                         WHERE ID = ".(int)$ID) or db_die();
    $row = db_fetch_row($result);
    if ($row[0] == 0) {
        message_stack_in("no entry found","contacts","error");
        $error = 1;
    }
    if ($row[0] <> $user_ID and !$row[1]) {
        message_stack_in("No privilege to delete contact!", "contacts", "error");
        $error = 1;
    }

    if (!$error) {
        // delete the entry from table contacts_profiles
        $result = db_query("DELETE
                              FROM ".DB_PREFIX."contacts_prof_rel
                             WHERE contact_ID = ".(int)$ID) or db_die();
        // delete all files associated with this record
        foreach($fields as $field_name => $field) {
            if ($field['form_type'] == 'upload' ) {
                $sql_value = upload_file_delete($field_name, $ID, 'contacts');
            }
        }
        // delete record in db
        $result = db_query("DELETE
                              FROM ".DB_PREFIX."contacts
                             WHERE ID = ".(int)$ID) or db_die();
        // delete corresponding entry from db_record
        remove_link($ID, 'contacts');
        message_stack_in( $row[2].": ".__('The contact has been deleted').".  ","contacts","notice");

        $result = db_query("DELETE
                              FROM ".DB_PREFIX."project_contacts_rel
                             WHERE contact_ID = ".(int)$ID) or db_die();

        message_stack_in(__('Removed contact - project relation.'), "contacts", "notice");

        // finally delete history
        history_delete('contacts', $ID);
    }
}
?>
