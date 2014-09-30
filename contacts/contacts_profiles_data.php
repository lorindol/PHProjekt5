<?php

// contacts_profiles_data.php - PHProjekt Version 5.2
// copyright  ©  2000-2005 Albrecht Guenther  ag@phprojekt.com
// www.phprojekt.com
// Author: Albrecht Guenther, $Author: thorsten $
// $Id: contacts_profiles_data.php,v 1.23.2.3 2007/02/26 14:50:03 thorsten Exp $

// check whether the lib has been included - authentication!
if (!defined("lib_included")) die("Please use index.php!");

// check form token
check_csrftoken();

if (isset($loeschen)) {
    delete_profile();
}
else if (isset($db_neu)) {
    insert_profile();
}
else if (isset($db_aendern)) {
    update_profile();
}
else if (isset($use_profile)) {
    use_profile($ID);
}

include_once("./contacts_profiles_forms.php");

function delete_profile() {
    global $ID, $action;

    if ($ID <> '') {
        // check permission
        include_once(LIB_PATH."/permission.inc.php");
        check_permission("contacts_profiles", "von", $ID);
        // delete record in db
        $result = db_query("DELETE FROM ".DB_PREFIX."contacts_profiles
                            WHERE ID = ".(int)$ID) or db_die();
        // delete all entries in the other table
        $result = db_query("DELETE FROM ".DB_PREFIX."contacts_prof_rel
                            WHERE contacts_profiles_ID = ".(int)$ID) or db_die();
        message_stack_in(__('The profile has been deleted.'), "profiles", "notice");
    }
}

function check_values() {
    global $error, $name, $contact_personen, $user_ID, $ID, $action;

    // forgot to give a name?
    if (!$name) {
        $error = 1;
        message_stack_in(__('Please specify a description! '), "profiles", "error");
    }
    //forgot to select at least one record?
    if (!$contact_personen) {
        $error = 1;
        message_stack_in(__('Please select at least one name! '), "profiles", "error");
    }
    // check whether this name already exists
    $result = db_query("SELECT name
                          FROM ".DB_PREFIX."contacts_profiles
                         WHERE von = ".(int)$user_ID."
                           AND ID <> ".(int)$ID) or db_die();
    while ($row = db_fetch_row($result)) {
        if ($row[0] == $name) {
            message_stack_in(__('This name already exists'),"profiles","error");
            $error = 1;
        }
    }
}

function insert_profile() {
    global $error, $user_ID, $name, $remark;
    global $kategorie, $acc, $contact_personen, $ID, $user_ID, $action;

    check_values();

    if (!$error) {
        // insert profile for contacts
        // insert record itself
        $result = db_query("INSERT INTO ".DB_PREFIX."contacts_profiles
                                        (        von    ,                 name,                    remark )
                                 VALUES (".(int)$user_ID.",'".strip_tags($name)."', '".strip_tags($remark)."')") or db_die();
        if ($result) {
            message_stack_in(strip_tags($name). __(' is created as a profile.<br />'),"profiles","notice");
        }
        // fetch ID from last insert
        $result = db_query("SELECT ID
                              FROM ".DB_PREFIX."contacts_profiles
                             WHERE von  = ".(int)$user_ID."
                               AND name = '".strip_tags($name)."'") or db_die();
        $row = db_fetch_row($result);
        // insert the new values
        foreach($contact_personen as $s1) {
            $result = db_query("INSERT INTO ".DB_PREFIX."contacts_prof_rel
                                          (contact_ID, contacts_profiles_ID)
                                   VALUES (".(int)$s1.",".(int)$row[0].")") or db_die();
        }
    }
}

function update_profile() {
    global $error, $user_ID, $name, $remark, $kategorie, $acc, $contact_personen, $ID, $action;

    check_values();

    if (!$error) {
        // update relatd entries:
        // 1. delete all old entries
        $result = db_query("DELETE FROM ".DB_PREFIX."contacts_prof_rel
                             WHERE contacts_profiles_ID = ".(int)$ID) or db_die();
        // 2. insert the new values
        foreach($contact_personen as $s1) {
            $result = db_query("INSERT INTO ".DB_PREFIX."contacts_prof_rel
                                           (contact_ID, contacts_profiles_ID)
                                    VALUES (".(int)$s1.",".(int)$ID.")") or db_die();
        }
        // update record itself
        $result = db_query("UPDATE ".DB_PREFIX."contacts_profiles
                                   SET name = '".strip_tags($name)."',
                                       remark = '".strip_tags($remark)."'
                                 WHERE ID = ".(int)$ID) or db_die();
        if ($result) {
            message_stack_in(strip_tags($name). __('is changed.<br />'),"profiles","notice");
        }
    }
}

function use_profile($ID) {
    global $flist, $remark;

    unset($flist['contacts']);
    $filters = explode('|', $remark);
    foreach ($filters as $filter) {
        if ($filter <> '') {
            $flist['contacts'][] = explode(' ', $filter);
        }
    }
    $_SESSION['flist'] =& $flist;
}

?>
