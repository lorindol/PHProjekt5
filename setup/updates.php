<?php

// updates.php - PHProjekt Version 5.2
// copyright © 2000-2005 Albrecht Guenther  ag@phprojekt.com
// www.phprojekt.com
// Author: Albrecht Guenther, $Author: polidor $
// $Id: updates.php,v 1.143.2.15 2007/08/02 16:43:00 polidor Exp $

// check whether setup.php calls this script - authentication!
if (!defined("setup_included")) die("Please use setup.php!");

$version_old = $old_config_array["PHPR_VERSION"];

if ((!isset($db_type) && is_empty($db_type)) && is_defined("PHPR_DB_TYPE")) {
    $db_type = PHPR_DB_TYPE;
}
else {
    $db_type = qss($db_type);
}

// update to version 1.3 ***********
// set plz (zip code) to varchar so the adress e.g. D-123456 is valid
if (($setup <> "install") and $version_old == "1.2") {
    $result = db_query("ALTER TABLE user CHANGE plz plz ".$db_varchar10[$db_type]) or db_die();
    $result = db_query("ALTER TABLE contacts CHANGE plz plz ".$db_varchar10[$db_type]) or db_die();
}

// update to version 2.0 ***********
if (($setup <> "install") and ($version_old == "1.3" or $version_old == "1.2")) {

    // prepare db to be compatible with other db's, extend several tables
    // extinct damned timestamp and date

    // users
    $result = db_query("ALTER TABLE user RENAME users") or db_die();
    $result = db_query("alter table users add mobil ".$db_varchar40[$db_type]) or db_die();
    $result = db_query("update users set mobil = ''") or db_die();
    $result = db_query("ALTER TABLE users CHANGE access acc ".$db_varchar4[$db_type]) or db_die();

    // termine = events
    $result = db_query("ALTER TABLE termine CHANGE text event ".$db_varchar40[$db_type]) or db_die();
    $result = db_query("alter table termine add ort ".$db_varchar40[$db_type]) or db_die();
    $result = db_query("update termine set ort = ''") or db_die();
    $result = db_query("alter table termine add contact ".$db_varchar255[$db_type]) or db_die();
    $result = db_query("update termine set contact = ''") or db_die();
    $result = db_query("alter table termine add note2 ".$db_text[$db_type]) or db_die();
    $result = db_query("update termine set note2 = ''") or db_die();
    $result = db_query("alter table termine add div1 ".$db_varchar40[$db_type]) or db_die();
    $result = db_query("update termine set div1 = ''") or db_die();
    $result = db_query("alter table termine add div2 ".$db_varchar40[$db_type]) or db_die();
    $result = db_query("update termine set div2 = ''") or db_die();
    $result = db_query("ALTER TABLE termine CHANGE erstellt erstellt ".$db_varchar20[$db_type]) or db_die();
    $result = db_query("ALTER TABLE termine CHANGE datum datum ".$db_varchar10[$db_type]) or db_die();

    // groups
    if ($groups and $old_config_array["PHPR_GROUPS"] and ($version_old <> "1.0b" and $version_old <> "0.9.3" and $version_old <> "0.9.2")) {
        $result = db_query("ALTER TABLE groups RENAME gruppen") or db_die();
    }
    // contacts
    if ($contacts  and $old_config_array["PHPR_CONTACTS"]) {
        $result = db_query("ALTER TABLE contacts CHANGE access acc ".$db_varchar4[$db_type]) or db_die();
        $result = db_query("alter table contacts add email2 ".$db_varchar60[$db_type]) or db_die();
        $result = db_query("update contacts set email2 = ''") or db_die();
        $result = db_query("alter table contacts add mobil ".$db_varchar40[$db_type]) or db_die();
        $result = db_query("update contacts set mobil = ''") or db_die();
        $result = db_query("alter table contacts add url ".$db_varchar40[$db_type]) or db_die();
        $result = db_query("update contacts set url = ''") or db_die();
        $result = db_query("alter table contacts add div1 ".$db_varchar40[$db_type]) or db_die();
        $result = db_query("update contacts set div1 = ''") or db_die();
        $result = db_query("alter table contacts add div2 ".$db_varchar40[$db_type]) or db_die();
        $result = db_query("update contacts set div2 = ''") or db_die();
    }
    // Forum
    if ($forum and $old_config_array["PHPR_FORUM"]) {
        $result = db_query("ALTER TABLE forum CHANGE text remark ".$db_text[$db_type]) or db_die();
        $result = db_query("ALTER TABLE forum CHANGE datum datum ".$db_varchar20[$db_type]) or db_die();
    }
    // lesezeichen - bookmarks
    if ($bookmarks and $old_config_array["PHPR_BOOKMARKS"]) {
        $result = db_query("ALTER TABLE lesezeichen CHANGE datum datum ".$db_varchar20[$db_type]) or db_die();
    }
    // Notes
    if ($notes and $old_config_array["PHPR_NOTES"] and ($version_old <> "0.9.3" and $version_old <> "0.9.2")) {
        $result = db_query("ALTER TABLE notes CHANGE text remark ".$db_text[$db_type]) or db_die();
        $result = db_query("alter table notes add contact ".$db_int8[$db_type]) or db_die();
        $result = db_query("update notes set contact = 0") or db_die();
        $result = db_query("alter table notes add ext ".$db_int8[$db_type]) or db_die();
        $result = db_query("update notes set ext = 0") or db_die();
        $result = db_query("alter table notes add div1 ".$db_varchar40[$db_type]) or db_die();
        $result = db_query("update notes set div1 = ''") or db_die();
        $result = db_query("alter table notes add div2 ".$db_varchar40[$db_type]) or db_die();
        $result = db_query("update notes set div2 = ''") or db_die();
    }
    // todo lists
    if ($todo and $old_config_array["PHPR_TODO"]) {
        $result = db_query("ALTER TABLE todo CHANGE text note ".$db_varchar40[$db_type]) or db_die();
        $result = db_query("alter table todo add ext ".$db_int8[$db_type]) or db_die();
        $result = db_query("update todo set ext = 0") or db_die();
        $result = db_query("alter table todo add div1 ".$db_text[$db_type]) or db_die();
        $result = db_query("update todo set div1 = ''") or db_die();
        $result = db_query("alter table todo add div2 ".$db_varchar40[$db_type]) or db_die();
        $result = db_query("update todo set div2 = ''") or db_die();
    }
    // rts request tracker system
    if ($rts and $old_config_array["PHPR_RTS"] and ($version_old <> "1.1" and $version_old <> "1.0b" and $version_old <> "0.9.3" and $version_old <> "0.9.2")) {
        $result = db_query("ALTER TABLE rts CHANGE text note ".$db_text[$db_type]) or db_die();
        $result = db_query("ALTER TABLE rts_cat CHANGE user users ".$db_varchar10[$db_type]) or db_die();
        $result = db_query("ALTER TABLE rts CHANGE access acc ".$db_int1[$db_type]) or db_die();
    }
    // project management
    if ($projects and $old_config_array["PHPR_PROJECTS"]) {
        $result = db_query("alter table projekte add chef ".$db_varchar20[$db_type]) or db_die();
        $result = db_query("update projekte set chef = 0") or db_die();
        $result = db_query("alter table projekte add typ ".$db_varchar40[$db_type]) or db_die();
        $result = db_query("update projekte set typ = ''") or db_die();
        $result = db_query("alter table projekte add parent ".$db_int4[$db_type]) or db_die();
        $result = db_query("update projekte set parent = 0") or db_die();
        $result = db_query("alter table projekte add ziel ".$db_varchar255[$db_type]) or db_die();
        $result = db_query("update projekte set ziel = ''") or db_die();
        $result = db_query("alter table projekte add note ".$db_text[$db_type]) or db_die();
        $result = db_query("update projekte set note = ''") or db_die();
        $result = db_query("alter table projekte add kategorie ".$db_varchar40[$db_type]) or db_die();
        $result = db_query("update projekte set kategorie = ''") or db_die();
        $result = db_query("alter table projekte add contact ".$db_int8[$db_type]) or db_die();
        $result = db_query("update projekte set contact = 0") or db_die();
        $result = db_query("alter table projekte add stundensatz ".$db_int8[$db_type]) or db_die();
        $result = db_query("update projekte set stundensatz = 0") or db_die();
        $result = db_query("alter table projekte add budget ".$db_int11[$db_type]) or db_die();
        $result = db_query("update projekte set budget = 0") or db_die();
        $result = db_query("alter table projekte add div1 ".$db_varchar40[$db_type]) or db_die();
        $result = db_query("update projekte set div1 = ''") or db_die();
        $result = db_query("alter table projekte add div2 ".$db_varchar40[$db_type]) or db_die();
        $result = db_query("update projekte set div2 = ''") or db_die();
        $result = db_query("ALTER TABLE projekte CHANGE ende ende ".$db_varchar10[$db_type]) or db_die();
        $result = db_query("ALTER TABLE projekte CHANGE statuseintrag statuseintrag ".$db_varchar10[$db_type]) or db_die();
        $result = db_query("ALTER TABLE projekte CHANGE anfang anfang ".$db_varchar10[$db_type]) or db_die();
    }
    // timeproj = assign work time to projects
    if ($projects == "2" and $old_config_array["PHPR_PROJECTS"] == "2" and ($version_old <> "0.9.3" and $version_old <> "0.9.2")) {
        $result = db_query("ALTER TABLE timeproj CHANGE user users ".$db_int4[$db_type]) or db_die();
        $result = db_query("alter table timeproj add note ".$db_varchar40[$db_type]) or db_die();
        $result = db_query("update timeproj set note = ''") or db_die();
        $result = db_query("alter table timeproj add ext ".$db_int2[$db_type]) or db_die();
        $result = db_query("update timeproj set ext = 0") or db_die();
        $result = db_query("alter table timeproj add div1 ".$db_varchar40[$db_type]) or db_die();
        $result = db_query("update timeproj set div1 = ''") or db_die();
        $result = db_query("alter table timeproj add div2 ".$db_varchar40[$db_type]) or db_die();
        $result = db_query("update timeproj set div2 = ''") or db_die();
        $result = db_query("ALTER TABLE timeproj CHANGE datum datum ".$db_varchar10[$db_type]) or db_die();
    }
    // timecard
    if ($timecard and $old_config_array["PHPR_TIMECARD"] and ($version_old <> "0.9.3" and $version_old <> "0.9.2")) {
        $result = db_query("ALTER TABLE timecard CHANGE user users ".$db_varchar255[$db_type]) or db_die();
        $result = db_query("ALTER TABLE timecard CHANGE begin anfang ".$db_varchar4[$db_type]) or db_die();
        $result = db_query("ALTER TABLE timecard CHANGE end ende ".$db_varchar4[$db_type]) or db_die();
        $result = db_query("alter table timecard add note ".$db_varchar40[$db_type]) or db_die();
        $result = db_query("update timecard set note = ''") or db_die();
        $result = db_query("alter table timecard add div1 ".$db_varchar40[$db_type]) or db_die();
        $result = db_query("update timecard set div1 = ''") or db_die();
        $result = db_query("alter table timecard add div2 ".$db_varchar40[$db_type]) or db_die();
        $result = db_query("update timecard set div2 = ''") or db_die();
        $result = db_query("ALTER TABLE timecard CHANGE datum datum ".$db_varchar10[$db_type]) or db_die();
    }
    // dateien = files
    if ($filemanager and $old_config_array["PHPR_FILEMANAGER"]) {
        $result = db_query("ALTER TABLE dateien CHANGE access acc ".$db_text[$db_type]) or db_die();
        $result = db_query("ALTER TABLE dateien CHANGE size filesize ".$db_int11[$db_type]) or db_die();
        $result = db_query("ALTER TABLE dateien CHANGE text remark ".$db_varchar255[$db_type]) or db_die();
        $result = db_query("ALTER TABLE dateien ADD tempname ".$db_varchar60[$db_type]) or db_die();
        $result = db_query("alter table dateien add typ ".$db_varchar40[$db_type]) or db_die();
        $result = db_query("update dateien set typ = ''") or db_die();
        $result = db_query("alter table dateien add div1 ".$db_varchar40[$db_type]) or db_die();
        $result = db_query("update dateien set div1 = ''") or db_die();
        $result = db_query("alter table dateien add div2 ".$db_varchar40[$db_type]) or db_die();
        $result = db_query("update dateien set div2 = ''") or db_die();
        $result = db_query("ALTER TABLE dateien CHANGE datum datum ".$db_varchar20[$db_type]) or db_die();
    }
    // ressourcen = resources
    if ($ressourcen and $old_config_array["PHPR_RESSOURCEN"]) {
        $result = db_query("alter table ressourcen add typ ".$db_varchar40[$db_type]) or db_die();
        $result = db_query("update ressourcen set typ = ''") or db_die();
        $result = db_query("alter table ressourcen add div1 ".$db_varchar40[$db_type]) or db_die();
        $result = db_query("update ressourcen set div1 = ''") or db_die();
        $result = db_query("alter table ressourcen add div2 ".$db_varchar40[$db_type]) or db_die();
        $result = db_query("update ressourcen set div2 = ''") or db_die();
    }
    // votum - polls
    if ($votum and $old_config_array["PHPR_VOTUM"]) {
        $result = db_query("ALTER TABLE votum CHANGE datum datum ".$db_varchar20[$db_type]) or db_die();
    }
}

// update to Version 2.1 ***********
if (($setup == "update") and ($version_old == "2.0" or $version_old == "1.3" or $version_old == "1.2")) {
    if ($groups) {
        $result = db_query("
            CREATE TABLE grup_user (
            ID ".$db_int8_auto[$db_type].",
            grup_ID ".$db_int8[$db_type].",
            user_ID ".$db_int8[$db_type].",
            PRIMARY KEY (ID)
            ) ");
    }
}

// update to version 2.2 ***********
if (($setup == "update") and ($version_old == "2.1" or $version_old == "2.0" or $version_old == "1.3" or $version_old == "1.2")) {
    if ($groups) {
        $result = db_query("select ID, gruppe from users") or db_die();
        while ($row = db_fetch_row($result)) {
            if ($row[1] > 0) {
                $result2 = db_query("insert into grup_user (grup_ID, user_ID) values ('$row[1]','$row[0]')") or db_die();
            }
        }
    }
}

// update to Version 2.3 ***********
if (($setup == "update") and ($version_old == "2.2" or $version_old == "2.1" or $version_old == "2.0" or $version_old == "1.3" or $version_old == "1.2")) {
    $result = db_query("alter table users add loginname ".$db_varchar40[$db_type]) or db_die();
    $result = db_query("update users set loginname = ''") or db_die();
}

// update to Version 2.4 ***********
if (($setup == "update") and ($version_old == "2.3.1" or $version_old == "2.3" or $version_old == "2.2" or $version_old == "2.1" or $version_old == "2.0" or $version_old == "1.3" or $version_old == "1.2")) {
    if ($filemanager and $old_config_array["PHPR_FILEMANAGER"]) {
        $result = db_query("update dateien set div1 = '0'") or db_die();
        $result = db_query("update dateien set typ = 'f' where filesize > 0") or db_die();
        $result = db_query("update dateien set typ = 'l' where filesize = 0") or db_die();
    }
    if ($projects and $old_config_array["PHPR_PROJECTS"]) {
        $result = db_query("update projekte set parent = 0 where parent is null") or db_die();
    }
}

// update to Version 3.0 ***********
if (($setup == "update") and (ereg("2.4",$version_old) or ereg("2.3",$version_old) or $version_old == "2.2" or $version_old == "2.1" or $version_old == "2.0" or $version_old == "1.3" or $version_old == "1.2")) {
    $result = db_query("alter table users add ldap_name ".$db_varchar40[$db_type]) or db_die();
    $result = db_query("update users set ldap_name = ''") or db_die();
    if ($notes and $old_config_array["PHPR_NOTES"]) {
        $result = db_query("alter table notes add projekt ".$db_int6[$db_type]) or db_die();
        $result = db_query("update notes set projekt = 0") or db_die();
    }
}

// update to Version 3.1 ***********
if (($setup == "update") and (ereg("3.0",$version_old) or ereg("2.4",$version_old) or ereg("2.3",$version_old) or $version_old == "2.2" or $version_old == "2.1" or $version_old == "2.0" or $version_old == "1.3" or $version_old == "1.2")) {
    $result = db_query("alter table users add anrede ".$db_varchar10[$db_type]) or db_die();
    $result = db_query("update users set anrede = ''") or db_die();
    $result = db_query("alter table users add sms ".$db_varchar60[$db_type]) or db_die();
    $result = db_query("update users set sms = ''") or db_die();
    if ($contacts  and $old_config_array["PHPR_CONTACTS"]) {
        $result = db_query("alter table contacts add anrede ".$db_varchar10[$db_type]) or db_die();
        $result = db_query("update contacts set anrede = ''") or db_die();
        $result = db_query("alter table contacts add state ".$db_varchar20[$db_type]) or db_die();
        $result = db_query("update contacts set state = ''") or db_die();
    }
    $result = db_query("alter table termine add remind ".$db_int4[$db_type]) or db_die();
    $result = db_query("update termine set remind = 0") or db_die();
}

// update to Version 3.2 ***********
if (($setup == "update") and (ereg("3.1",$version_old) or ereg("3.0",$version_old) or ereg("2.4",$version_old) or ereg("2.3",$version_old) or $version_old == "2.2" or $version_old == "2.1" or $version_old == "2.0" or $version_old == "1.3" or $version_old == "1.2")) {
    // create separte field to store a flag for imported contacts until they are verified.
    if ($contacts  and $old_config_array["PHPR_CONTACTS"]) {
        $result = db_query("alter table contacts add import ".$db_char1[$db_type]) or db_die();
        $result = db_query("update contacts set import = ''") or db_die();
        echo "updating table contacts add field 'import' ...<br />\n";
    }
    // new field: flag whether the event is open to public calendars
    $result = db_query("alter table termine add visi ".$db_char1[$db_type]) or db_die();
    $result = db_query("update termine set visi = '0'") or db_die();
    echo "updating table termine add field 'visi' ...<br />\n";

    if ($forum and $old_config_array["PHPR_FORUM"]) {
        $result = db_query("alter table forum add lastchange ".$db_varchar20[$db_type]) or db_die();
        $result = db_query("update forum set lastchange = ''") or db_die();
        echo "updating table forum add field 'lastchange'...<br />\n";

        // set field lastchange of root postings to the date value of the last comment
        echo "updating table forum set date of last posting to each thread ...<br /><br />\n";
        $result = db_query("select ID, datum from forum where antwort > 0") or db_die();
        while ($row = db_fetch_row($result)) {
            $antwort = $row[0];
            while ($antwort > 0) {
                $result2 = db_query("select ID, antwort from forum where ID = ".(int)$antwort) or db_die();
                $row2 = db_fetch_row($result2);
                $antwort = $row2[1];
            }
            $result3 = db_query("select ID, datum from forum where ID = ".(int)$row2[0]) or db_die();
            $row3 = db_fetch_row($result3);
            if ($row3[1] < $row[1]) {
                $result3 = db_query("update forum set lastchange='$row[1]' where ID = ".(int)$row3[0]) or db_die();
            }
        }
    }

    // add field pw to store the password file file encryption
    if ($filemanager and $old_config_array["PHPR_FILEMANAGER"]) {
        $result = db_query("alter table dateien add pw ".$db_varchar255[$db_type]) or db_die();
        $result = db_query("update dateien set pw = ''") or db_die();
    }
} // end update to version 3.2

// update to Version 3.3 ***********
if (($setup == "update") and (ereg("3.2",$version_old) or ereg("3.1",$version_old) or ereg("3.0",$version_old) or ereg("2.4",$version_old) or ereg("2.3",$version_old) or $version_old == "2.2" or $version_old == "2.1" or $version_old == "2.0" or $version_old == "1.3" or $version_old == "1.2")) {

    // update catalan values from ca to ct
    $result = db_query("update users set sprache = 'ct' where sprache = 'ca'") or db_die();

    // add field 'notify' to forum module
    $result = db_query("alter table forum add notify ".$db_varchar2[$db_type]) or db_die();
    $result = db_query("update forum set notify = ''") or db_die();

    // add a new table for the mail client to create own sender/signature combinations
    if ($quickmail == 2) {
        $result = db_query("
            CREATE TABLE mail_sender (
            ID ".$db_int8_auto[$db_type].",
            von ".$db_int6[$db_type].",
            title ".$db_varchar80[$db_type].",
            sender ".$db_varchar255[$db_type].",
            signature ".$db_text[$db_type].",
            PRIMARY KEY (ID)
        ) ");
        if ($db_type == "oracle") { sequence("mail_sender"); }
        if ($db_type == "interbase") {ib_autoinc("mail_sender"); }
    }

    // update filemanager: set access value '1' to 'private', '2' to 'group'
    if ($filemanager  and $old_config_array["PHPR_FILEMANAGER"]) {
        $result = db_query("update dateien set acc = 'private' where acc = '1'") or db_die();
        $result = db_query("update dateien set acc = 'group' where acc = '2'") or db_die();
        // add more fields
        $result = db_query("alter table dateien add acc_write ".$db_text[$db_type]) or db_die();
        $result = db_query("update dateien set acc_write = ''") or db_die();
        $result = db_query("alter table dateien add version ".$db_varchar4[$db_type]) or db_die();
        $result = db_query("update dateien set version = ''") or db_die();
        $result = db_query("alter table dateien add lock_user ".$db_varchar10[$db_type]) or db_die();
        $result = db_query("update dateien set lock_user = ''") or db_die();
        $result = db_query("alter table dateien add contact ".$db_varchar10[$db_type]) or db_die();
        $result = db_query("update dateien set contact = ''") or db_die();
    }
} // end update to Version 3.3

// update to Version 4.0 ***********
if (($setup == "update") and (ereg("3.3",$version_old) or ereg("3.2",$version_old) or ereg("3.1",$version_old) or ereg("3.0",$version_old) or ereg("2.4",$version_old) or ereg("2.3",$version_old) or $version_old == "2.2" or $version_old == "2.1" or $version_old == "2.0" or $version_old == "1.3" or $version_old == "1.2")) {

    // update resources, create own table
    if ($ressourcen) {
        $result = db_query("
            CREATE TABLE termine_res_rel (
            ID ".$db_int8_auto[$db_type].",
            termin_ID ".$db_int8[$db_type].",
            res_ID ".$db_int8[$db_type].",
            PRIMARY KEY (ID)
        ) ");
    }
    if ($db_type == "oracle") { sequence("termine_res_rel"); }
    if ($db_type == "interbase") {ib_autoinc("termine_res_rel"); }

    // update resources, store them in an own table termine_res_rel
    $result = db_query("select ID, ressource from termine where ressource > 0") or db_die();
    while ($row = db_fetch_row($result)) {
        $result2 = db_query("insert into termine_res_rel (termin_ID,res_ID) values (".(int)$row[0].", ".(int)$row[1].")") or db_die();
        $result2 = db_query("update termine set ressource = 0 where ID = ".(int)$row[0]) or db_die();
    }

    // Roles
    // create table roles
    $result = db_query("
            CREATE TABLE roles (
            ID ".$db_int8_auto[$db_type].",
            von ".$db_int6[$db_type].",
            title ".$db_varchar60[$db_type].",
            remark ".$db_text[$db_type].",
            summary ".$db_int1[$db_type].",
            calendar ".$db_int1[$db_type].",
            contacts ".$db_int1[$db_type].",
            forum ".$db_int1[$db_type].",
            chat ".$db_int1[$db_type].",
            filemanager ".$db_int1[$db_type].",
            bookmarks ".$db_int1[$db_type].",
            votum ".$db_int1[$db_type].",
            mail ".$db_int1[$db_type].",
            notes ".$db_int1[$db_type].",
            helpdesk ".$db_int1[$db_type].",
            projects ".$db_int1[$db_type].",
            timecard ".$db_int1[$db_type].",
            todo ".$db_int1[$db_type].",
            news ".$db_int1[$db_type].",
            PRIMARY KEY (ID)
            ) ");
    if ($db_type == "oracle") { sequence("roles"); }
    if ($db_type == "interbase") {ib_autoinc("roles"); }

    // add field role in table users
    $result = db_query("alter table users add role ".$db_int4[$db_type]) or db_die();
    $result = db_query("update users set role = 0") or db_die();
    $result = db_query("alter table users add proxy ".$db_text[$db_type]) or db_die();
    $result = db_query("update users set proxy = ''") or db_die();
    $result = db_query("alter table users add settings ".$db_text[$db_type]) or db_die();
    $result = db_query("update users set settings = ''") or db_die();

    // add field parent in table contacts
    if ($contacts  and $old_config_array["PHPR_CONTACTS"]) {
        $result = db_query("alter table contacts add parent ".$db_int8[$db_type]) or db_die();
        $result = db_query("update contacts set parent = 0") or db_die();
    }
    //extend table todo
    if ($todo and $old_config_array["PHPR_TODO"]) {
        $result = db_query("alter table todo add note ".$db_text[$db_type]) or db_die();
        $result = db_query("update todo set note = ''") or db_die();
        $result = db_query("alter table todo add deadline ".$db_varchar20[$db_type]) or db_die();
        $result = db_query("update todo set deadline = ''") or db_die();
        $result = db_query("alter table todo add datum ".$db_varchar20[$db_type]) or db_die();
        $result = db_query("update todo set datum = ''") or db_die();
        $result = db_query("alter table todo add status ".$db_int1[$db_type]) or db_die();
        $result = db_query("update todo set status = ''") or db_die();
        $result = db_query("alter table todo add priority ".$db_int1[$db_type]) or db_die();
        $result = db_query("update todo set priority = 0") or db_die();
        $result = db_query("alter table todo add progress ".$db_int3[$db_type]) or db_die();
        $result = db_query("update todo set progress = 0") or db_die();
        $result = db_query("alter table todo add project ".$db_int6[$db_type]) or db_die();
        $result = db_query("update todo set project = 0") or db_die();
        $result = db_query("alter table todo add contact ".$db_int8[$db_type]) or db_die();
        $result = db_query("update todo set contact = 0") or db_die();

        // change the field values of todo - which nerd has done such stupid field naming anyway? ;)
        $result = db_query("select ID, von from todo") or db_die();
        while ($row = db_fetch_row($result)) {
            $result2 = db_query("update todo set ext = ".(int)$row[1].", von = 0 where ID = ".(int)$row[0]) or db_die();
        }
    }

    // add dependency and next-in-list in table projects
    if ($projects and $old_config_array["PHPR_PROJECTS"]) {
        $result = db_query("alter table projekte add depend_mode ".$db_int2[$db_type]) or db_die();
        $result = db_query("update projekte set depend_mode = 0") or db_die();
        $result = db_query("alter table projekte add depend_proj ".$db_int6[$db_type]) or db_die();
        $result = db_query("update projekte set depend_proj = 0") or db_die();
        $result = db_query("alter table projekte add next_mode ".$db_int2[$db_type]) or db_die();
        $result = db_query("update projekte set next_mode = 0") or db_die();
        $result = db_query("alter table projekte add next_proj ".$db_int6[$db_type]) or db_die();
        $result = db_query("update projekte set next_proj = 0") or db_die();
    }

    // add some fields for future syncing
    // in todo ...
    if ($todo and $old_config_array["PHPR_TODO"]) {
        $result = db_query("alter table todo add sync1 ".$db_varchar20[$db_type]) or db_die();
        $result = db_query("update todo set sync1 = ''") or db_die();
        $result = db_query("alter table todo add sync2 ".$db_varchar20[$db_type]) or db_die();
        $result = db_query("update todo set sync2 = ''") or db_die();
    }
    // ... contacts ...
    if ($contacts  and $old_config_array["PHPR_CONTACTS"]) {
        $result = db_query("alter table contacts add sync1 ".$db_varchar20[$db_type]) or db_die();
        $result = db_query("update contacts set sync1 = ''") or db_die();
        $result = db_query("alter table contacts add sync2 ".$db_varchar20[$db_type]) or db_die();
        $result = db_query("update contacts set sync2 = ''") or db_die();
    }
    // ... calendar ...
    $result = db_query("alter table termine add sync1 ".$db_varchar20[$db_type]) or db_die();
    $result = db_query("update termine set sync1 = ''") or db_die();
    $result = db_query("alter table termine add sync2 ".$db_varchar20[$db_type]) or db_die();
    $result = db_query("update termine set sync2 = ''") or db_die();

    // ... and notes!
    if ($notes and $old_config_array["PHPR_NOTES"]) {
        $result = db_query("alter table notes add sync1 ".$db_varchar20[$db_type]) or db_die();
        $result = db_query("update notes set sync1 = ''") or db_die();
        $result = db_query("alter table notes add sync2 ".$db_varchar20[$db_type]) or db_die();
        $result = db_query("update notes set sync2 = ''") or db_die();
    }
    // end preparation for sycning

    // forgot to add the fields comment fields inthe table todo :-)) -> here we go
    if ($todo and $old_config_array["PHPR_TODO"]) {
        $result = db_query("alter table todo add comment1 ".$db_text[$db_type]) or db_die();
        $result = db_query("update todo set comment1 = ''") or db_die();
        $result = db_query("alter table todo add comment2 ".$db_text[$db_type]) or db_die();
        $result = db_query("update todo set comment2 = ''") or db_die();
    }

} // end update to version 4.0

// update to Version 4.1 ***********
if ( ($setup == "update") and (ereg("4.0",$version_old) or ereg("3.3",$version_old) or
ereg("3.2",$version_old) or ereg("3.1",$version_old) or ereg("3.0",$version_old) or
ereg("2.4",$version_old) or ereg("2.3",$version_old) or $version_old == "2.2" or
$version_old == "2.1" or $version_old == "2.0" or $version_old == "1.3" or $version_old == "1.2") ) {

    // enhance the contactmanager with access rights
    $result = db_query("alter table ".DB_PREFIX."contacts add acc_read ".$db_text[$db_type]) or db_die();
    $result = db_query("update ".DB_PREFIX."contacts set acc_read = ''") or db_die();
    $result = db_query("alter table ".DB_PREFIX."contacts add acc_write ".$db_text[$db_type]) or db_die();
    $result = db_query("update ".DB_PREFIX."contacts set acc_write = ''") or db_die();

    // update table contacts, shift all values from field 'acc' to field 'access': NULL = private, a = group
    $result = db_query("select ID, acc from ".DB_PREFIX."contacts") or db_die();
    while ($row = db_fetch_row($result)) {
        if ($row[1] == 'a') { $access = 'group'; }
        else { $access = 'private'; }
        $result2 = db_query("update ".DB_PREFIX."contacts set acc_read = '$access', acc = '' where ID = ".(int)$row[0]) or db_die();
    }


    // make sure that a folder named /upload exists since we need it for the module desinger
    mkdir('docs',0600);

    // form designer
    // 1. the table itself
    $result = db_query("
            CREATE TABLE ".DB_PREFIX."db_manager (
            ID ".$db_int8_auto[$db_type].",
            db_table ".$db_varchar40[$db_type].",
            db_name ".$db_varchar40[$db_type].",
            form_name ".$db_varchar255[$db_type].",
            form_type ".$db_varchar20[$db_type].",
            form_tooltip ".$db_varchar255[$db_type].",
            form_pos ".$db_int4[$db_type].",
            form_colspan ".$db_int2[$db_type].",
            form_rowspan ".$db_int2[$db_type].",
            form_regexp ".$db_varchar255[$db_type].",
            form_default ".$db_varchar255[$db_type].",
            form_select ".$db_text[$db_type].",
            list_pos ".$db_int4[$db_type].",
            list_alt ".$db_varchar2[$db_type].",
            filter_show ".$db_varchar2[$db_type].",
            db_inactive ".$db_int1[$db_type].",
            PRIMARY KEY (ID)
            ) ");
    if ($db_type == "oracle") { sequence("db_manager"); }
    if ($db_type == "interbase") {ib_autoinc("db_manager"); }

    $result = db_query("insert into ".DB_PREFIX."db_manager (ID, db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive) VALUES (1, 'contacts', 'nachname', '__(\'Family Name\')', 'text', 'give the description: last name, company name or organisation etc.', 1, 1, 1, NULL, NULL, NULL, 1, NULL, '1', 0)") or db_die();
    $result = db_query("insert into ".DB_PREFIX."db_manager (ID, db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive) VALUES (2, 'contacts', 'vorname', '__(\'First Name\')', 'text', 'Type in the first name of the person', 2, 1, 1, NULL, NULL, NULL, 2, NULL, '1', 0)") or db_die();
    $result = db_query("insert into ".DB_PREFIX."db_manager (ID, db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive) VALUES (3, 'contacts', 'anrede', '__(\'Salutation\')', 'text', 'Title of the person: Mr, Mrs, Dr., Majesty etc. ...', 3, 1, 1, NULL, NULL, NULL, 0, NULL, '0', 0)") or db_die();
    $result = db_query("insert into ".DB_PREFIX."db_manager (ID, db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive) VALUES (4, 'contacts', 'firma', '__(\'Company\')', 'text', 'Name of associated team or company', 4, 1, 1, NULL, NULL, NULL, 0, NULL, '1', 0)") or db_die();
    $result = db_query("insert into ".DB_PREFIX."db_manager (ID, db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive) VALUES (5, 'contacts', 'email', 'email', 'email', 'enter the main email address of this contact', 5, 1, 1, NULL, NULL, NULL, 3, NULL, '1', 0)") or db_die();
    $result = db_query("insert into ".DB_PREFIX."db_manager (ID, db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive) VALUES (6, 'contacts', 'email2', 'email 2', 'email', 'enter an alternative mail address of this contact', 6, 1, 1, NULL, NULL, NULL, 0, '1', '0', 0)") or db_die();
    $result = db_query("insert into ".DB_PREFIX."db_manager (ID, db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive) VALUES (7, 'contacts', 'tel1', '__(\'Phone\') 1', 'phone', 'enter the primary phone number of this contact', 7, 1, 1, NULL, NULL, NULL, 0, NULL, '0', 0)") or db_die();
    $result = db_query("insert into ".DB_PREFIX."db_manager (ID, db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive) VALUES (8, 'contacts', 'tel2', '__(\'Phone\') 2', 'phone', 'enter the secondary phone number of this contact', 8, 1, 1, NULL, NULL, NULL, 0, NULL, '0', 0)") or db_die();
    $result = db_query("insert into ".DB_PREFIX."db_manager (ID, db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive) VALUES (9, 'contacts', 'mobil', '__(\'mobile // mobile phone\')', 'phone', 'enter the cellular phone number', 9, 1, 1, NULL, NULL, NULL, 0, NULL, '0', 0)") or db_die();
    $result = db_query("insert into ".DB_PREFIX."db_manager (ID, db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive) VALUES (10, 'contacts', 'fax', '__(\'Fax\')', 'text', 'enter the fax number of this contact', 10, 1, 1, NULL, NULL, NULL, 0, '1', '0', 0)") or db_die();
    $result = db_query("insert into ".DB_PREFIX."db_manager (ID, db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive) VALUES (11, 'contacts', 'strasse', '__(\'Street\')', 'text', 'the street where the person lives or the company is located', 11, 1, 1, NULL, NULL, NULL, 0, NULL, '0', 0)") or db_die();
    $result = db_query("insert into ".DB_PREFIX."db_manager (ID, db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive) VALUES (12, 'contacts', 'stadt', '__(\'City\')', 'text', 'the city where the person lives or the company is located', 12, 1, 1, NULL, NULL, NULL, 0, NULL, '0', 0)") or db_die();
    $result = db_query("insert into ".DB_PREFIX."db_manager (ID, db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive) VALUES (13, 'contacts', 'plz', '__(\'Zip code\')', 'text', 'the coresponding zip code', 13, 1, 1, NULL, NULL, NULL, 0, NULL, '0', 0)") or db_die();
    $result = db_query("insert into ".DB_PREFIX."db_manager (ID, db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive) VALUES (14, 'contacts', 'land', '__(\'Country\')', 'text', 'the country', 14, 1, 1, NULL, NULL, NULL, 0, NULL, '0', 0)") or db_die();
    $result = db_query("insert into ".DB_PREFIX."db_manager (ID, db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive) VALUES (15, 'contacts', 'state', '__(\'State\')', 'text', 'region or state (USA)', 15, 1, 1, NULL, NULL, NULL, 0, NULL, '0', 0)") or db_die();
    $result = db_query("insert into ".DB_PREFIX."db_manager (ID, db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive) VALUES (16, 'contacts', 'url', 'url', 'url', 'the homepage - private or business', 16, 1, 1, NULL, NULL, NULL, 4, NULL, '0', 0)") or db_die();
    $result = db_query("insert into ".DB_PREFIX."db_manager (ID, db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive) VALUES (17, 'contacts', 'div1', '', 'text', 'a default userdefined field', 17, 1, 1, NULL, NULL, NULL, 0, NULL, '0', 0)") or db_die();
    $result = db_query("insert into ".DB_PREFIX."db_manager (ID, db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive) VALUES (18, 'contacts', 'div2', '', 'text', 'another default userdefined field', 18, 1, 1, NULL, NULL, NULL, 0, NULL, '0', 0)") or db_die();
    $result = db_query("insert into ".DB_PREFIX."db_manager (ID, db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive) VALUES (19, 'contacts', 'bemerkung', '__(\'Comment\')', 'textarea', 'a comment about this record', 19, 2, 5, NULL, NULL, NULL, 0, NULL, '0', 0)") or db_die();
    $result = db_query("insert into ".DB_PREFIX."db_manager (ID, db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive) VALUES (20, 'contacts', 'kategorie', '__(\'Category\')', 'select_category', 'Select an existing category or insert a new one', 20, 1, 1, NULL, NULL, '(acc like \'system\' or ((von = \$user_ID or acc like \'group\' or acc like \'%\\\\\"\$user_kurz\\\\\"%\') and \$sql_user_group))', 4, NULL, '1', 0)") or db_die();

    if ($todo and $old_config_array["PHPR_TODO"]) {
        // oh, I forgot: module todo gets a 'start' field as well!
        $result = db_query("alter table ".DB_PREFIX."todo add anfang ".$db_varchar20[$db_type]) or db_die();
        $result = db_query("update ".DB_PREFIX."todo set anfang = ''") or db_die();

        // since we open the module todo for the whole group we have to set the group as well!
        // -> add new field 'gruppe' and assign the current records to the according group
        $result = db_query("alter table ".DB_PREFIX."todo add gruppe ".$db_int4[$db_type]) or db_die();
        $result = db_query("update ".DB_PREFIX."todo set gruppe = 0") or db_die();
        // extended access for objects in module todo ...
        $result = db_query("alter table ".DB_PREFIX."todo add acc ".$db_text[$db_type]) or db_die();
        $result = db_query("update ".DB_PREFIX."todo set acc = ''") or db_die();
        $result = db_query("alter table ".DB_PREFIX."todo add acc_write ".$db_text[$db_type]) or db_die();
        $result = db_query("update ".DB_PREFIX."todo set acc_write = ''") or db_die();

        if ($groups > 0) {
            $result = db_query("select ID, von, ext
                                    from ".DB_PREFIX."todo") or db_die();
            while ($row = db_fetch_row($result)) {
                // fetch the groupID's of this author
                $result2 = db_query("select grup_ID from ".DB_PREFIX."grup_user where user_ID = ".(int)$row[1]) or db_die();
                while ($row2 = db_fetch_row($result2)) { $author_groups[] = $row2[0]; }
                $result3 = db_query("select grup_ID from ".DB_PREFIX."grup_user where user_ID = ".(int)$row[2]) or db_die();
                while ($row3 = db_fetch_row($result3)) { $recipient_groups[] = $row3[0]; }
                // now we have the two arrays of group memeberships (mostly it will be one element) -> compare the values
                foreach ($recipient_groups as $rec_group) {
                    if (in_array($rec_group, $author_groups)) $same_group = $rec_group;
                }
                // if no group is found at all ... well, go to hell and take the first groupID from the author :)
                if (!$same_group) $same_group = $author_groups[0];
                // update the record
                $result = db_query("update ".DB_PREFIX."todo
                                    set gruppe = ".(int)$same_group.",
                                        acc = 'private'
                                    where ID = ".(int)$row[0]) or db_die();
            }
        }
    }

    // ... and now for notes!
    if ($notes and $old_config_array["PHPR_OLD"]) {
        $result = db_query("alter table ".DB_PREFIX."notes add acc ".$db_text[$db_type]) or db_die();
        $result = db_query("update ".DB_PREFIX."notes set acc = ''") or db_die();
        $result = db_query("alter table ".DB_PREFIX."notes add acc_write ".$db_text[$db_type]) or db_die();
        $result = db_query("update ".DB_PREFIX."notes set acc_write = ''") or db_die();
        $result = db_query("alter table ".DB_PREFIX."notes add gruppe ".$db_int4[$db_type]) or db_die();
        $result = db_query("update ".DB_PREFIX."notes set gruppe = 0") or db_die();
        // rewrite access rules in table notes
        $result = db_query("select ".DB_PREFIX."notes.ID, ext, ".DB_PREFIX."users.gruppe
                              from ".DB_PREFIX."notes, ".DB_PREFIX."users
                             where ".DB_PREFIX."notes.von = ".DB_PREFIX."users.ID") or db_die();
        while ($row = db_fetch_row($result)) {
            if ($row[1] > 0) {
                $access = 'group';
                $gruppe = $row[1];
            }
            else {
                $access = 'private';
                $gruppe = $row[2];
            }
            $result2 = db_query("update ".DB_PREFIX."notes set acc = '$access', gruppe = ".(int)$gruppe." where ID = ".(int)$row[0]) or db_die();
        }
    }
    if ($projects and $old_config_array["PHPR_PROJECTS"]) {
        $result = db_query("alter table ".DB_PREFIX."projekte add probability ".$db_int4[$db_type]) or db_die();
        $result = db_query("update ".DB_PREFIX."projekte set probability = 0") or db_die();
        $result = db_query("alter table ".DB_PREFIX."projekte add ende_real ".$db_varchar10[$db_type]) or db_die();
        $result = db_query("update ".DB_PREFIX."projekte set ende_real = ''") or db_die();
    }

    // mail accounts
    if ($quickmail > 1) {
        $result = db_query("alter table ".DB_PREFIX."mail_account add mail_auth ".$db_int2[$db_type]) or db_die();
        $result = db_query("update ".DB_PREFIX."mail_account set mail_auth = ''") or db_die();
        $result = db_query("alter table ".DB_PREFIX."mail_account add pop_hostname ".$db_varchar40[$db_type]) or db_die();
        $result = db_query("update ".DB_PREFIX."mail_account set pop_hostname = ''") or db_die();
        $result = db_query("alter table ".DB_PREFIX."mail_account add pop_account ".$db_varchar40[$db_type]) or db_die();
        $result = db_query("update ".DB_PREFIX."mail_account set pop_account = ''") or db_die();
        $result = db_query("alter table ".DB_PREFIX."mail_account add pop_password ".$db_varchar40[$db_type]) or db_die();
        $result = db_query("update ".DB_PREFIX."mail_account set pop_password = ''") or db_die();
        $result = db_query("alter table ".DB_PREFIX."mail_account add smtp_hostname ".$db_varchar40[$db_type]) or db_die();
        $result = db_query("update ".DB_PREFIX."mail_account set smtp_hostname = ''") or db_die();
        $result = db_query("alter table ".DB_PREFIX."mail_account add smtp_account ".$db_varchar40[$db_type]) or db_die();
        $result = db_query("update ".DB_PREFIX."mail_account set smtp_account = ''") or db_die();
        $result = db_query("alter table ".DB_PREFIX."mail_account add smtp_password ".$db_varchar40[$db_type]) or db_die();
        $result = db_query("update ".DB_PREFIX."mail_account set smtp_password = ''") or db_die();
        $result = db_query("alter table ".DB_PREFIX."mail_account add collect ".$db_int1[$db_type]) or db_die();
        $result = db_query("update ".DB_PREFIX."mail_account set collect = 1") or db_die();
    }

    // add table 'contacts_import_schemes' if contacts_profiles are enabled
    if ($contacts_profiles > 0) {
        $result = db_query("
            CREATE TABLE ".DB_PREFIX."contacts_import_patterns (
            ID ".$db_int8_auto[$db_type].",
            name ".$db_varchar40[$db_type].",
            von ".$db_int6[$db_type].",
            pattern ".$db_text[$db_type].",
            PRIMARY KEY (ID)
        ) ");
        if ($db_type == "oracle") { sequence("contacts_import_patterns"); }
        if ($db_type == "interbase") {ib_autoinc("contacts_import_patterns"); }
    }

    // since in 4.1 new parameters for sending mails are introduced we have to preset them here
    if (strpos(strtolower($_SERVER["OS"]), 'windows') !== false) {
        $mail_eol = "\r\n"; // end of line in body; e.g. \r\n (conform to RFC 2821 / 2822)
        $mail_eoh = "\r\n"; // end of header line; e.g. \r\n (conform to RFC 2821 / 2822)
    }
    else if (strpos(strtolower($_SERVER["OS"]), 'mac') !== false) {
        $mail_eol = "\r"; // end of line in body; e.g. \r\n (conform to RFC 2821 / 2822)
        $mail_eoh = "\r"; // end of header line; e.g. \r\n (conform to RFC 2821 / 2822)
    }
    else {
        $mail_eol = "\n"; // end of line in body; e.g. \r\n (conform to RFC 2821 / 2822)
        $mail_eoh = "\n"; // end of header line; e.g. \r\n (conform to RFC 2821 / 2822)
    }
    $mail_mode = "0";
    $mail_auth = "0";
    $smtp_hostname = "localhost";
    $local_hostname = "hereiam";
    $pop_account = "itsme";
    $pop_password = "mypw";
    $pop_hostname = "mypop.domain.net";
    $smtp_account = "itsme";
    $smtp_password = "mypw";
} // end update to version 4.1

// from now on all db tables have to be the db_prefix!


// *********************************
// update to Version 4.2 ***********

if ( ($setup == "update") and (ereg("4.1",$version_old) or ereg("4.0",$version_old) or
ereg("3.3",$version_old) or ereg("3.2",$version_old) or ereg("3.1",$version_old) or
ereg("3.0",$version_old) or ereg("2.4",$version_old) or ereg("2.3",$version_old) or
$version_old == "2.2" or $version_old == "2.1" or $version_old == "2.0" or
$version_old == "1.3" or $version_old == "1.2") ) {

    // extend module designer with other modules
    $result = db_query("select max(ID) from ".DB_PREFIX."db_manager") or db_die();
    $row = db_fetch_row($result);
    $ii = $row[0];

    // notes
    $result = db_query("insert into ".DB_PREFIX."db_manager (ID, db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive) VALUES (".($ii+=1).", 'notes', 'remark', '__(\'Remark\')', 'textarea', 'bodytext of the note', 2, 2, 5, NULL, NULL, NULL, 2, NULL, '1', 0)") or db_die();
    $result = db_query("insert into ".DB_PREFIX."db_manager (ID, db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive) VALUES (".($ii+=1).", 'notes', 'name', '__(\'Title\')', 'text', 'Title of this note', 1, 2, 1, '', NULL, NULL, 1, NULL, '1', 0)") or db_die();
    $result = db_query("insert into ".DB_PREFIX."db_manager (ID, db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive) VALUES (".($ii+=1).", 'notes', 'contact', '__(\'Contact\')', 'contact', 'Contact related to this note', 3, 1, 1, NULL, NULL, NULL, 3, NULL, NULL, 0)") or db_die();
    $result = db_query("insert into ".DB_PREFIX."db_manager (ID, db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive) VALUES (".($ii+=1).", 'notes', 'projekt', '__(\'Projects\')', 'project', 'Project related to this note', 4, 1, 1, NULL, NULL, NULL, 4, NULL, NULL, 0)") or db_die();
    $result = db_query("insert into ".DB_PREFIX."db_manager (ID, db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive) VALUES (".($ii+=1).", 'notes', 'div1', '__(\'added\')', 'timestamp_create', '', 5, 1, 1, NULL, NULL, '', 5, NULL, '0', 0)") or db_die();
    $result = db_query("insert into ".DB_PREFIX."db_manager (ID, db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive) VALUES (".($ii+=1).", 'notes', 'div2', '__(\'changed\')', 'timestamp_modify', '', 6, 1, 1, NULL, NULL, '', 6, NULL, '0', 0)") or db_die();
    $result = db_query("insert into ".DB_PREFIX."db_manager (ID, db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive) VALUES (".($ii+=1).", 'notes', 'kategorie', '__(\'Category\')', 'select_category', 'Select an existing category or insert a new one', 7, 1, 1, NULL, NULL, '(acc like \'system\' or ((von = \$user_ID or acc like \'group\' or acc like \'%\\\\\"\$user_kurz\\\\\"%\') and \$sql_user_group))', 0, NULL, '1', 0)") or db_die();

    // projects
    $result = db_query("insert into ".DB_PREFIX."db_manager (ID, db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive) VALUES (".($ii+=1).", 'projekte', 'name', '__(\'Project Name\')', 'text', 'the name of the project', 1, 1, 1, NULL, NULL, NULL, 1, NULL, '1', 0)") or db_die();
    $result = db_query("insert into ".DB_PREFIX."db_manager (ID, db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive) VALUES (".($ii+=1).", 'projekte', 'kategorie', '__(\'Category\')', 'select_values', 'current category (or status) of this project', 2, 1, 1, NULL, NULL, '1#\$proj_text20|2#\$proj_text21|3#\$proj_text23|4#\$proj_text24|5#\$proj_text25|6#\$proj_text26|7#\$proj_text27', 1, NULL, '1', 0)") or db_die();
    $result = db_query("insert into ".DB_PREFIX."db_manager (ID, db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive) VALUES (".($ii+=1).", 'projekte', 'ziel', '__(\'Aim\')', 'text', 'describe the aim of this project', 9, 1, 1, NULL, NULL, NULL, 0, '0', '0', 0)") or db_die();
    $result = db_query("insert into ".DB_PREFIX."db_manager (ID, db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive) VALUES (".($ii+=1).", 'projekte', 'anfang', '__(\'Start\')', 'date', 'start day', 3, 1, 1, NULL, NULL, NULL, 3, NULL, '1', 0)") or db_die();
    $result = db_query("insert into ".DB_PREFIX."db_manager (ID, db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive) VALUES (".($ii+=1).", 'projekte', 'ende', '__(\'End\')', 'date', 'planned end', 4, 1, 1, NULL, NULL, NULL, 4, NULL, '1', 0)") or db_die();
    $result = db_query("insert into ".DB_PREFIX."db_manager (ID, db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive) VALUES (".($ii+=1).", 'projekte', 'contact', '__(\'Contact\')', 'contact', 'select the customer/contact', 6, 1, 1, NULL, NULL, NULL, 0, '1', '0', 0)") or db_die();
    $result = db_query("insert into ".DB_PREFIX."db_manager (ID, db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive) VALUES (".($ii+=1).", 'projekte', 'chef', '__(\'Leader\')', 'userID', 'Seelct a user of this group as the project leader', 5, 1, 1, NULL, NULL, NULL, 0, '1', '0', 0)") or db_die();
    $result = db_query("insert into ".DB_PREFIX."db_manager (ID, db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive) VALUES (".($ii+=1).", 'projekte', 'wichtung', '__(\'Priority\')', 'select_values', 'set the priority of this project', 10, 1, 1, NULL, NULL, '1\|2\|3\|4\|5\|6\|7\|8\|9', 0, NULL, '1', 0)") or db_die();
    $result = db_query("insert into ".DB_PREFIX."db_manager (ID, db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive) VALUES (".($ii+=1).", 'projekte', 'stundensatz', '__(\'Hourly rate\')', 'text', 'hourly rate of this project', 7, 1, 1, NULL, NULL, NULL, 0, '1', '0', 0)") or db_die();
    $result = db_query("insert into ".DB_PREFIX."db_manager (ID, db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive) VALUES (".($ii+=1).", 'projekte', 'budget', '__(\'Budget\')', 'text', '', 8, 1, 1, NULL, NULL, NULL, 0, '1', '0', 0)") or db_die();
    $result = db_query("insert into ".DB_PREFIX."db_manager (ID, db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive) VALUES (".($ii+=1).", 'projekte', 'status', '__(\'Status\') [%]', 'userID_access', 'current completion status', 11, 1, 1, NULL, NULL,'chef', 4, NULL, '1', 0)") or db_die();
    $result = db_query("insert into ".DB_PREFIX."db_manager (ID, db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive) VALUES (".($ii+=1).", 'projekte', 'statuseintrag', '__(\'Last status change\')', 'display', 'date of last change of status', 12, 1, 1, NULL, NULL, NULL, 0, '1', '1', 0)") or db_die();
    $result = db_query("insert into ".DB_PREFIX."db_manager (ID, db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive) VALUES (".($ii+=1).", 'projekte', 'note', '__(\'Remark\')', 'textarea', 'remarks', 13, 2, 5, NULL, NULL, NULL, 0, '0', '1', 0)") or db_die();

    // acc & acc_write for projects
    if ($projects and $old_config_array["PHPR_PROJECTS"]) {

        // convert the project leader: from variable user_kurz to user_ID
        $result = db_query("select ID, chef
                            from ".DB_PREFIX."projekte") or db_die();
        while ($row = db_fetch_row($result)) {
            $result2 = db_query("update  ".DB_PREFIX."projekte set
                                        chef = '".slookup('users','ID','kurz',$row[1])."'
                                    where ID = ".(int)$row[0]) or db_die();
        }

        $result = db_query("alter table ".DB_PREFIX."projekte add acc ".$db_text[$db_type]) or db_die();
        $result = db_query("update ".DB_PREFIX."projekte set acc = ''") or db_die();
        $result = db_query("alter table ".DB_PREFIX."projekte add acc_write ".$db_text[$db_type]) or db_die();
        $result = db_query("update ".DB_PREFIX."projekte set acc_write = ''") or db_die();
        $result = db_query("alter table ".DB_PREFIX."projekte add von ".$db_int8[$db_type]) or db_die();
        $result = db_query("update ".DB_PREFIX."projekte set von = 0") or db_die();

        // set the author and the access for each project: owner will be project leader, or participant with chief status or a participant
        $result = db_query("select ID, chef, personen, gruppe , name
                            from ".DB_PREFIX."projekte") or db_die();
        while ($row = db_fetch_row($result)) {
            $von = 0;
            // project leader
            if ($row[1]) {
                $von = $row[1];
                $w = '';
            }
            // particpants
            elseif (strlen($row[2]) > 6) {
                foreach (unserialize($row[1]) as $participant) {
                    // if a user is chief, take him as the author
                    if (ereg('c', slookup('users','acc','kurz',$participant))) {
                        $von = slookup('users','ID','kurz',$participant);
                    }
                }
                // if no project leader and no user with chief status is amongs the participants, just take the first one
                if (!$von) {
                    $participants = unserialize($row[2]);
                    $von = slookup('users','ID','kurz',$participants[0]);
                    $w = 'w';
                }
            }
            // last chance: take a user with chief status in this group
            else {
                $result2 = db_query("select ID, acc
                                    from ".DB_PREFIX."users where gruppe = ".(int)$row[3]) or db_die();
                while ($row2 = db_fetch_row($result2)) {
                    if (ereg('c',$row2[1])) {
                        $von = $row2[0];
                        $w = 'w';
                    }
                }
                // oh no! stil havent found anyone? :-/ -> take the last user of this group and finish it!
                if (!$von) {
                    $von = $row2[0];
                    $w = 'w';
                }
            }
            // now update the record - set the participants as the group which is able to watch the record
            $result2 = db_query("update ".DB_PREFIX."projekte set
                                        von = ".(int)$von.",
                                        acc = 'group',
                                        acc_write = '$w'
                                where ID = ".(int)$row[0]) or db_die();

        }
    }

    // parent and category for notes
    if ($notes and $old_config_array["PHPR_NOTES"]) {
        $result = db_query("alter table ".DB_PREFIX."notes add parent ".$db_int8[$db_type]) or db_die();
        $result = db_query("update ".DB_PREFIX."notes set parent = 0") or db_die();
        $result = db_query("alter table ".DB_PREFIX."notes add kategorie ".$db_varchar40[$db_type]) or db_die();
        $result = db_query("update ".DB_PREFIX."notes set kategorie = ''") or db_die();
    }

    // access for forum and set all entries as 'group writeable'
    if ($forum and $old_config_array["PHPR_FORUM"]) {
        $result = db_query("alter table ".DB_PREFIX."forum add acc ".$db_text[$db_type]) or db_die();
        $result = db_query("update ".DB_PREFIX."forum set acc = 'group'") or db_die();
        $result = db_query("alter table ".DB_PREFIX."forum add acc_write ".$db_text[$db_type]) or db_die();
        $result = db_query("update ".DB_PREFIX."forum set acc_write = ''") or db_die();
    }

    // hourly rate per user
    $result = db_query("alter table ".DB_PREFIX."users add hrate ".$db_varchar10[$db_type]) or db_die();
    $result = db_query("update ".DB_PREFIX."users set hrate = ''") or db_die();

    // create history table
    if ($history_log) {
        $result = db_query("
            CREATE TABLE ".DB_PREFIX."history (
            ID ".$db_int8_auto[$db_type].",
            von ".$db_int8[$db_type].",
            h_date ".$db_varchar20[$db_type].",
            h_table ".$db_varchar60[$db_type].",
            h_field ".$db_varchar60[$db_type].",
            h_record ".$db_int8[$db_type].",
            last_value ".$db_text[$db_type].",
            new_value ".$db_text[$db_type].",
            PRIMARY KEY (ID)
        ) ");
    }

    // extend mail table with message ID, project and contacr relation
    if ($quickmail > 1) {
        $result = db_query("alter table ".DB_PREFIX."mail_client add contact ".$db_int8[$db_type]);
        $result = db_query("update ".DB_PREFIX."mail_client set contact = 0");
        $result = db_query("alter table ".DB_PREFIX."mail_client add projekt ".$db_int8[$db_type]);
        $result = db_query("update ".DB_PREFIX."mail_client set projekt = 0");
        $result = db_query("alter table ".DB_PREFIX."mail_client add message_ID ".$db_varchar128[$db_type]);
        $result = db_query("update ".DB_PREFIX."mail_client set message_ID = ''");


        // extend mail ruels for contacts and projects
        $result = db_query("alter table ".DB_PREFIX."mail_rules add projekt ".$db_int8[$db_type]);
        $result = db_query("update ".DB_PREFIX."mail_rules set projekt = 0");
        $result = db_query("alter table ".DB_PREFIX."mail_rules add contact ".$db_int8[$db_type]);
        $result = db_query("update ".DB_PREFIX."mail_rules set contact = 0");
    }

    // update sync fields
    // in todo ...
    if ($todo and $old_config_array["PHPR_TODO"]) {
        $result = db_query("update ".DB_PREFIX."todo set sync2 = '$dbTSnull'") or db_die();
        $result = db_query("update ".DB_PREFIX."todo set sync1 = sync2") or db_die();
    }
    // ... contacts ...
    if ($contacts  and $old_config_array["PHPR_CONTACTS"]) {
        $result = db_query("update ".DB_PREFIX."contacts set sync2 = '$dbTSnull'") or db_die();
        $result = db_query("update ".DB_PREFIX."contacts set sync1 = sync2") or db_die();
    }
    // ... calendar ...
    $result = db_query("update ".DB_PREFIX."termine set sync2 = '$dbTSnull'") or db_die();
    $result = db_query("update ".DB_PREFIX."termine set sync1 = sync2") or db_die();
    // ... and notes!
    if ($notes and $old_config_array["PHPR_NOTES"]) {
        $result = db_query("update ".DB_PREFIX."notes set sync2 = '$dbTSnull'") or db_die();
        $result = db_query("update ".DB_PREFIX."notes set sync1 = sync2") or db_die();
    }

    // add pathes in the config.inc.php to move the directories
    $doc_path       = 'docs';
    $att_path       = 'attach';
    $calltype       = 'callto';
    $history_log    = '0';
    $filter_maxhits = '0';
    $bgcolor_mark   = '#E6DE90';
    $bgcolor_hili   = '#FFFFFF';
    $support_pdf    = '0';
    $support_html   = '0';
    $support_chart  = '0';
    if (!$default_size) $default_size = '60';
} // end update 4.2


// *************
// update to 5.0
// *************
if ( ($setup == "update") and (ereg("4.2",$version_old) or ereg("4.1",$version_old) or
ereg("4.0",$version_old) or ereg("3.3",$version_old) or ereg("3.2",$version_old) or
ereg("3.1",$version_old) or ereg("3.0",$version_old) or ereg("2.4",$version_old) or
ereg("2.3",$version_old) or $version_old == "2.2" or $version_old == "2.1" or
$version_old == "2.0" or $version_old == "1.3" or $version_old == "1.2") ) {



    if($dat_crypt == 0){
        echo __('Filenames will now be crypted ...')." ...<br />";
        // crypt old files in filesystem and database
        $dir = $dateien;
        if(file_exists($dir)){
            $handle = opendir($dir);
            $ignore_files = array('..', '.', 'index.html');
            while ($file = readdir ($handle)) {
                if (!in_array($file, $ignore_files)) {
                    $new_filename = rnd_string();
                    // because renaming is difficult on various OS, use copy and unlink
                    copy($dir.'/'.$file, $dir.'/'.$new_filename);
                    unlink($dir.'/'.$file);
                    $result = db_query("UPDATE ".DB_PREFIX."dateien SET tempname = '$new_filename' WHERE tempname = '$file'") or db_die();
                }
            }
            closedir($handle);
        }
    }

    // update groupless systems
    if ($groups == 0) {
        $result = db_query("SELECT COUNT(*)
                              FROM ".DB_PREFIX."gruppen
                             WHERE name = 'default'") or db_die();
        $res = db_fetch_row($result);
        // create default group
        if (!$res[0]) {
            $result = db_query("INSERT INTO ".DB_PREFIX."gruppen
                                            ( name, kurz, bemerkung)
                                     VALUES ('default', 'def', 'default group')") or db_die();
        }
        // get group id of default group
        $result = db_query("SELECT ID
                              FROM ".DB_PREFIX."gruppen
                             WHERE name = 'default'") or db_die();
        $res = db_fetch_row($result);
        $default_group_id = $res[0];
        // put groupless users into default group -> we ask for gruppe = 0 to skip root account (gruppe = NULL)
        $result = db_query("UPDATE ".DB_PREFIX."users
                               SET gruppe = $default_group_id
                             WHERE gruppe = 0") or db_die();
    }

    // grup_user
    if ($old_config_array["PHPR_GROUPS"]) {
        // create own index for user_ID and grup_ID
        $result = db_query("CREATE INDEX grup_user_user_ID ON ".DB_PREFIX."grup_user (user_ID)") or db_die();
        $result = db_query("CREATE INDEX grup_user_grup_ID ON ".DB_PREFIX."grup_user (grup_ID)") or db_die();
        // test user normally has no entry in grup_user in version < 5.0 -> add entry to avoid errors in v5
        $result = db_query("SELECT gu.ID, u.ID FROM ".DB_PREFIX."users u LEFT JOIN ".DB_PREFIX."grup_user gu ON u.ID = gu.user_ID WHERE u.loginname = 'test'") or db_die();
        $res = db_fetch_row($result);
        if (is_null($res[0])) {
            $result = db_query("INSERT INTO ".DB_PREFIX."grup_user (grup_ID, user_ID) VALUES ( 1, '".$res[1]."')") or db_die();
        }
    }

    // history
    if ($history_log and $old_config_array["PHPR_HISTORY_LOG"]) {
        
        // note: on postgres all integer values are the same type
        if ($db_type <> 'postgresql') {
            $result = db_query("ALTER TABLE ".DB_PREFIX."history CHANGE von von ".$db_int8[$db_type]) or db_die() ;
        }
    }

    // notes
    if ($notes and $old_config_array["PHPR_NOTES"]) {
        
        // note: on postgres all integer values are the same type
        if ($db_type <> 'postgresql') {
            $result = db_query("ALTER TABLE ".DB_PREFIX."notes CHANGE parent parent ".$db_int6[$db_type]);
        }
        
        // for versions without it
        $result = db_query("ALTER TABLE ".DB_PREFIX."notes ADD parent".$db_int8[$db_type]);
        
        if ($db_type <> 'postgresql') {
            $result = db_query("ALTER TABLE ".DB_PREFIX."notes CHANGE kategorie kategorie ".$db_varchar100[$db_type]);
        }
        else {
            $result = db_query("ALTER TABLE ".DB_PREFIX."notes ALTER COLUMN kategorie TYPE ".$db_varchar100[$db_type]) or db_die() ;
        }
        // for versions without it
        $result = db_query("ALTER TABLE ".DB_PREFIX."notes ADD kategorie".$db_varchar100[$db_type]);
    }

    // rts
    if ($rts and $old_config_array["PHPR_RTS"]) {
        // for versions with parent field
        if ($db_type <> 'postgresql') {
            $result = db_query("ALTER TABLE ".DB_PREFIX."rts CHANGE parent parent ".$db_int8[$db_type]);
        }
        // for versions without it
        $result = db_query("ALTER TABLE ".DB_PREFIX."rts ADD parent".$db_int8[$db_type]);
    }

    // timecard
    if ($timecard and $old_config_array["PHPR_TIMECARD"]) {
        $result = db_query("ALTER TABLE ".DB_PREFIX."timecard ADD nettoh ".$db_int2[$db_type]) or db_die();
        $result = db_query("ALTER TABLE ".DB_PREFIX."timecard ADD nettom ".$db_int2[$db_type]) or db_die();
        $result = db_query("ALTER TABLE ".DB_PREFIX."timecard ADD ip_address ".$db_varchar255[$db_type]) or db_die();
    }

    // profiles
    if (!$profile) {
        $result = db_query("
            CREATE TABLE ".DB_PREFIX."profile (
            ID ".$db_int8_auto[$db_type].",
            von ".$db_int8[$db_type].",
            bezeichnung ".$db_varchar20[$db_type].",
            personen ".$db_text[$db_type].",
            gruppe ".$db_int8[$db_type].",
            acc ".$db_text[$db_type].",
            PRIMARY KEY (ID)
          ) ");
        if ($result) { echo __('profiles (for user-profiles) created').".<br />\n"; }
        elseif(get_sql_errno($result) != $db_error_code_table_exists[$db_type]) {
            echo __('An error ocurred while creating table: ')." profile<br />\n"; $error = 1;
        }
        if ($db_type == "oracle") sequence("profile");
        if ($db_type == "interbase") ib_autoinc("profile");
    }
    else {
        if ($db_type == 'postgres') {
            $result = db_query("ALTER TABLE ".DB_PREFIX."profile ALTER COLUMN bezeichnung TYPE ".$db_varchar20[$db_type]) or db_die() ;
        }
        else {
            $result = db_query("ALTER TABLE ".DB_PREFIX."profile CHANGE bezeichnung bezeichnung ".$db_varchar20[$db_type]) or db_die() ;
        }
    }

    // db_records
    $result = db_query("
        CREATE TABLE ".DB_PREFIX."db_records (
        t_ID ".$db_int8_auto[$db_type].",
        t_author ".$db_int6[$db_type].",
        t_module ".$db_varchar40[$db_type].",
        t_record ".$db_int8[$db_type].",
        t_name ".$db_varchar255[$db_type].",
        t_datum ".$db_varchar20[$db_type].",
        t_touched ".$db_int2[$db_type].",
        t_archiv ".$db_int2[$db_type].",
        t_reminder ".$db_int2[$db_type].",
        t_reminder_datum ".$db_varchar20[$db_type].",
        t_wichtung ".$db_int2[$db_type].",
        t_remark ".$db_text[$db_type].",
        t_acc ".$db_text[$db_type].",
        t_gruppe ".$db_int6[$db_type].",
        t_parent ".$db_int6[$db_type].",
        PRIMARY KEY (t_ID)
    ) ");
    if ($db_type == "oracle") { sequence("db_records", "t_ID"); }
    if ($db_type == "interbase") { ib_autoinc("db_records"); }

    // logintoken
    $result = db_query("
        CREATE TABLE ".DB_PREFIX."logintoken (
        ID ".$db_int8_auto[$db_type].",
        von ".$db_int8[$db_type].",
        token ".$db_varchar255[$db_type].",
        user_ID ".$db_int8[$db_type].",
        url ".$db_varchar255[$db_type].",
        valid ".$db_varchar20[$db_type].",
        used ".$db_varchar20[$db_type].",
        datum ".$db_varchar20[$db_type].",
        PRIMARY KEY (ID)
    ) ");
    if ($db_type == "oracle") { sequence("logintoken"); }
    if ($db_type == "interbase") { ib_autoinc("logintoken"); }

    // filter
    $result = db_query("
        CREATE TABLE ".DB_PREFIX."filter (
        ID ".$db_int8_auto[$db_type].",
        von ".$db_int8[$db_type].",
        module ".$db_varchar255[$db_type].",
        name ".$db_varchar255[$db_type].",
        remark ".$db_text[$db_type].",
        filter ".$db_text[$db_type].",
        PRIMARY KEY (ID)
    ) ");
    if ($db_type == "oracle") { sequence("filter"); }
    if ($db_type == "interbase") { ib_autoinc("filter"); }

    // sync
    $result = db_query("
        CREATE TABLE ".DB_PREFIX."sync_rel (
        ID ".$db_int11_auto[$db_type].",
        user_ID ".$db_int8[$db_type].",
        sync_type ".$db_varchar255[$db_type].",
        sync_version ".$db_varchar255[$db_type].",
        sync_ID ".$db_varchar255[$db_type].",
        sync_module ".$db_varchar255[$db_type].",
        sync_checksum ".$db_varchar40[$db_type].",
        phprojekt_ID ".$db_int8[$db_type].",
        phprojekt_module ".$db_varchar40[$db_type].",
        created ".$db_varchar20[$db_type].",
        modified ".$db_varchar20[$db_type].",
        PRIMARY KEY (ID)
    )");
    if ($db_type == "oracle") { sequence("sync_rel"); }
    if ($db_type == "interbase") { ib_autoinc("sync_rel"); }

    // add the records in the db manager
    $result = db_query("SELECT MAX(ID) FROM ".DB_PREFIX."db_manager") or db_die();
    $row = db_fetch_row($result);
    $ii = (int)$row[0];

    // db_manager
    $result = db_query("ALTER TABLE ".DB_PREFIX."db_manager ADD rights ".$db_varchar4[$db_type]) or db_die();
    $result = db_query("ALTER TABLE ".DB_PREFIX."db_manager ADD ownercolumn ".$db_varchar255[$db_type]);

    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (ID, db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn) VALUES (".($ii+=1).",'db_records', 't_module', '__(\"Module\")', 'display', 'Module name', 1, 1, 1, NULL, NULL, NULL, 1, NULL, '1', 0, NULL, NULL)") or db_die();
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (ID, db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn) VALUES (".($ii+=1).",'db_records', 't_remark', '__(\"Remark\")', 'text', 'Remark', 3, 1, 1, NULL, NULL, NULL, 2, NULL, '1', 0, NULL, NULL)") or db_die();
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (ID, db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn) VALUES (".($ii+=1).",'db_records', 't_archiv', '__(\"Archive\")', 'text', 'Archive', 0, 1, 1, NULL, NULL, NULL, 0, NULL, '1', 0, NULL, NULL)") or db_die();
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (ID, db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn) VALUES (".($ii+=1).",'db_records', 't_touched', '__(\"Touched\")', 'text', 'Touched', 0, 1, 1, NULL, NULL, NULL, 0, NULL, '1', 0, NULL, NULL)") or db_die();
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (ID, db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn) VALUES (".($ii+=1).",'db_records', 't_name', '__(\"Title\")', 'text', 'Title', 2, 1, 1, NULL, NULL, NULL, 1, NULL, '1', 0, NULL, NULL)") or db_die();
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (ID, db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn) VALUES (".($ii+=1).",'db_records', 't_wichtung', '__(\"Priority\")', 'select_values', 'Priority', 4, 1, 1, NULL, NULL, '0|1|2|3|4|5|6|7|8|9', 4, NULL, '1', 0, NULL, NULL)") or db_die();
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (ID, db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn) VALUES (".($ii+=1).",'db_records', 't_reminder_datum', '__(\"Resubmission at:\")', 'date', 'Date', 5, 1, 1, NULL, NULL, '', 5, NULL, '1', 0, NULL, NULL)") or db_die();
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (ID, db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn) VALUES (".($ii+=1).",'db_records', 't_record', '__(\"Record set\")', 'display', 'ID of the target record', 0, 1, 1, '', NULL, NULL, 0, NULL, 0, 0, NULL, NULL)") or db_die();
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (ID, db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn) VALUES (".($ii+=1).",'db_records', 't_datum', '__(\"Date\")', 'date', '', 0, 1, 1, NULL, NULL, '', 0, NULL, '0', 0, NULL, NULL)") or db_die();

    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (ID, db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive) VALUES (".($ii+=1).",'termine', 'event', '__(\"Title\")', 'text', 'Title of this event', 1, 1, 1, '', NULL, NULL, 1, NULL, '1', 0)") or db_die();
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (ID, db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive) VALUES (".($ii+=1).",'termine', 'datum', '__(\"Date\")', 'text', 'Date of this event', 2, 1, 1, '', NULL, NULL, 2, NULL, '1', 0)") or db_die();
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (ID, db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive) VALUES (".($ii+=1).",'termine', 'anfang', '__(\"Start\")', 'text', 'Title of this event', 3, 1, 1, '', NULL, NULL, 3, NULL, '1', 0)") or db_die();
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (ID, db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive) VALUES (".($ii+=1).",'termine', 'ende', '__(\"End\")', 'text', 'end of this event', 4, 1, 1, '', NULL, NULL, 4, NULL, '1', 0)") or db_die();
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (ID, db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive) VALUES (".($ii+=1).",'termine', 'von', '__(\"Author\")', 'userID', 'Author of this event', 5, 1, 1, '', NULL, NULL, 5, NULL, '1', 0)") or db_die();
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (ID, db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive) VALUES (".($ii+=1).",'termine', 'an', '__(\"Recipient\")', 'userID', 'Recipient', 6, 1, 1, '', NULL, NULL, 6, NULL, '1', 0)") or db_die();
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (ID, db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive) VALUES (".($ii+=1).",'termine', 'partstat', '__(\"Participation\")', 'select_values', 'Title of this event', 7, 1, 1, '', NULL, '1#__(\"untreated\")|2#__(\"accepted\")|3#__(\"rejected\")', 7, NULL, '1', 0)") or db_die();

    // mail
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (ID, db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive) VALUES (".($ii+=1).",'mail_client', 'remark', '__(\"Comment\")', 'textarea', NULL, 1, 2, 2, NULL, NULL, NULL, NULL, NULL, 'on', 0)") or db_die();
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (ID, db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive) VALUES (".($ii+=1).",'mail_client', 'subject', '__(\"subject\")', 'text', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, 'on', 0)") or db_die();
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (ID, db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive) VALUES (".($ii+=1).",'mail_client', 'sender', '__(\"Sender\")', 'text', NULL, 0, 0, 0, NULL, NULL, NULL, 2, NULL, 'on', 0)") or db_die();
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (ID, db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive) VALUES (".($ii+=1).",'mail_client', 'kat', '__(\"Category\")', 'select_category', NULL, 2, 2, 1, NULL, NULL, NULL, NULL, NULL, 'on', 0)") or db_die();
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (ID, db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive) VALUES (".($ii+=1).",'mail_client', 'projekt', '__(\"Project\")', 'project', NULL, 3, 2, 1, NULL, NULL, NULL, NULL, NULL, 'on', 0)") or db_die();
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (ID, db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive) VALUES (".($ii+=1).",'mail_client', 'sender', '__(\"To\")', 'text', NULL, 0, 0, 0, NULL, NULL, NULL, 3, NULL, 'on', 0)") or db_die();

    
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (ID, db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn) VALUES (".($ii+=1).",'todo','remark','__(\"Title\")','text','Kurze Beschreibung',1,2,1,NULL,NULL,NULL,1,NULL,'1',0,NULL,NULL)") or db_die();
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (ID, db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn) VALUES (".($ii+=1).",'todo','deadline','__(\"Deadline\")','date',NULL,7,1,1,NULL,NULL,NULL,2,NULL,'1',0,NULL,NULL)") or db_die();
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (ID, db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn) VALUES (".($ii+=1).",'todo','datum','__(\"Date\")','timestamp_create',NULL,5,1,1,NULL,NULL,NULL,0,NULL,'1',0,NULL,NULL)") or db_die();
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (ID, db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn) VALUES (".($ii+=1).",'todo','priority','__(\"Priority\")','select_values',NULL,4,1,1,NULL,NULL,'0|1|2|3|4|5|6|7|8|9',5,NULL,'1',0,NULL,NULL)") or db_die();
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (ID, db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn) VALUES (".($ii+=1).",'todo','project','__(\"Project\")','project',NULL,9,1,1,NULL,NULL,NULL,6,NULL,'1',0,NULL,NULL)") or db_die();
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (ID, db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn) VALUES (".($ii+=1).",'todo','contact','__(\"Contact\")','contact',NULL,8,1,1,NULL,NULL,NULL,0,NULL,'1',0,NULL,NULL)") or db_die();
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (ID, db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn) VALUES (".($ii+=1).",'todo','note','__(\"Describe your request\")','textarea',NULL,11,2,3,NULL,NULL,NULL,0,NULL,'1',0,NULL,NULL)") or db_die();
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (ID, db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn) VALUES (".($ii+=1).",'todo','comment1','__(\"Remark\") __(\"Author\")','textarea',NULL,12,2,3,NULL,NULL,NULL,NULL,NULL,'1',1,'o','von')") or db_die();
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (ID, db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn) VALUES (".($ii+=1).",'todo','comment2','__(\"Remark\") __(\"Receiver\")','textarea',NULL,13,2,3,NULL,NULL,NULL,NULL,NULL,'1',1,'o','ext')") or db_die();
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (ID, db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn) VALUES (".($ii+=1).",'todo','von','__(\"of\")','user_show',NULL,2,1,1,NULL,NULL,NULL,3,NULL,'1',0,NULL,NULL)") or db_die();
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (ID, db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn) VALUES (".($ii+=1).",'todo','anfang','__(\"Begin\")','date',NULL,6,1,1,NULL,NULL,NULL,0,NULL,'1',0,NULL,NULL)") or db_die();
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (ID, db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn) VALUES (".($ii+=1).",'todo','ext','__(\"to\")','userID',NULL,3,1,1,NULL,NULL,NULL,4,NULL,'1',0,NULL,NULL)") or db_die();
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (ID, db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn) VALUES (".($ii+=1).",'todo', 'progress', '__(\"progress\") [%]', 'text', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, 0, NULL, NULL)") or db_die();
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (ID, db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn) VALUES (".($ii+=1).",'todo', 'status', '__(\"Status\")', 'select_values', NULL, NULL, NULL, NULL, NULL, NULL, '1#__(\"waiting\")|2#__(\"Open\")|3#__(\"accepted\")|4#__(\"rejected\")|5#__(\"ended\")', 7, NULL, NULL, 0, NULL, NULL)") or db_die();
    
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (ID, db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn) VALUES (".($ii+=1).",'rts', 'name', '__(\"Title\")', 'text', 'the title of the request', 1, 1, 1, NULL, NULL, NULL, 1, '0', '1', 0, NULL, NULL)") or db_die();
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (ID, db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn) VALUES (".($ii+=1).",'rts', 'note', '__(\"Remark\")', 'textarea', 'the body of the request set by the customer', 6, 1, 1, NULL, NULL, NULL, 0, '1', '1', 0, NULL, NULL)") or db_die();
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (ID, db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn) VALUES (".($ii+=1).",'rts', 'submit', '__(\"Date\")', 'timestamp_create', 'date/time the request ha been submitted', 4, 1, 1, NULL, NULL, NULL, 0, '0', '0', 0, NULL, NULL)") or db_die();
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (ID, db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn) VALUES (".($ii+=1).",'rts', 'recorded', '__(\"Author\")', 'authorID', 'the user who wrote this request', 5, 1, 1, NULL, NULL, NULL, 0, '0', '0', 0, NULL, NULL)") or db_die();
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (ID, db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn) VALUES (".($ii+=1).",'rts', 'contact', '__(\"Contact\")', 'contact_create', 'contact related to this request', 9, 1, 1, NULL, NULL, NULL, 3, '0', '0', 0, NULL, NULL)") or db_die();
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (ID, db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn) VALUES (".($ii+=1).",'rts', 'email', '__(\"Email Address\")', 'email_create', 'insert the mail address in case the customer is not listed', 12, 1, 1, NULL, NULL, NULL, 0, '1', '0', 0, NULL, NULL)") or db_die();
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (ID, db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn) VALUES (".($ii+=1).",'rts', 'due_date', '__(\"Due date\")', 'date', 'due date of this request', 9, 1, 1, NULL, NULL, NULL, 0, '1', '0', 0, NULL, NULL)") or db_die();
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (ID, db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn) VALUES (".($ii+=1).",'rts', 'assigned', '__(\"Assigned\")', 'userID', 'assign the request to this user', 10, 1, 1, NULL, NULL, NULL, 4, '1', '0', 0, NULL, NULL)") or db_die();
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (ID, db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn) VALUES (".($ii+=1).",'rts', 'priority', '__(\"Priority\")', 'select_values', 'set the priority of this project', 11, 1, 1, NULL, NULL, '0|1|2|3|4|5|6|7|8|9', 5, NULL, '1', 0, NULL, NULL)") or db_die();
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (ID, db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn) VALUES (".($ii+=1).",'rts', 'remark', '__(\"remark\")', 'textarea', 'internal remark to this request', 7, 1, 1, NULL, NULL, NULL, 0, '1', '1', 0, NULL, NULL)") or db_die();
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (ID, db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn) VALUES (".($ii+=1).",'rts', 'solution', '__(\"solve\")', 'textarea', 'A text will cause: a mail to the customer and closing the request', 8, 1, 1, NULL, NULL, NULL, 6, '0', '1', 0, NULL, NULL)") or db_die();
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (ID, db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn) VALUES (".($ii+=1).",'rts', 'solved', '__(\"solved\") . __(\"From\")', 'user_show', 'the user who has solved this request', 14, 1, 1, '', '', '', 0, '0', '0', 0, NULL, NULL)") or db_die();
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (ID, db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn) VALUES (".($ii+=1).",'rts', 'solve_time', '__(\"solved\")', 'timestamp_show', 'date and time when the request has been solved', 16, 1, 1, NULL, NULL, NULL, 0, '0', '0', 0, NULL, NULL)") or db_die();
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (ID, db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn) VALUES (".($ii+=1).",'rts', 'acc', '__(\"access\")', 'select_values', 'requests with status open appear in the knowledge base!', 17, 1, 1, NULL, NULL, '0#n/a|1#intern|2#auf', 4, '0', '0', 0, NULL, NULL)") or db_die();
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (ID, db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn) VALUES (".($ii+=1).",'rts', 'proj', '__(\"Projects\")', 'project', 'project related to this request', 14, 1, 1, NULL, NULL, '', 0, '0', '0', 0, NULL, NULL)") or db_die();
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (ID, db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn) VALUES (".($ii+=1).",'dateien', 'filename', '__(\"Title\")', 'text', 'Title of the file or directory', 1, 2, 1, '', NULL, NULL, 1, NULL, '1', 0, NULL, NULL)") or db_die();
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (ID, db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn) VALUES (".($ii+=1).",'dateien', 'remark', '__(\"Comment\")', 'textarea', 'remark related to this file', 4, 2, 5, NULL, NULL, NULL, 2, NULL, '1', 0, NULL, NULL)") or db_die();
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (ID, db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn) VALUES (".($ii+=1).",'dateien', 'contact', '__(\"Contact\")', 'contact', 'Contact related to this file', 5, 1, 1, NULL, NULL, NULL, 3, NULL, NULL, 0, NULL, NULL)") or db_die();
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (ID, db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn) VALUES (".($ii+=1).",'dateien', 'div2', '__(\"Projects\")', 'project', 'Project related to this file', 6, 1, 1, NULL, NULL, NULL, 4, NULL, NULL, 0, NULL, NULL)") or db_die();
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (ID, db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn) VALUES (".($ii+=1).",'dateien', 'kat', '__(\"Category\")', 'select_category', 'Select an existing category or insert a new one', 7, 1, 1, NULL, NULL, '(acc like \"system\" or ((von =  or acc like \"group\" or acc like \"%\"\"%\") and (1 = 1)))', 0, NULL, '1', 0, NULL, NULL)") or db_die();
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (ID, db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn) VALUES (".($ii+=1).",'dateien', 'datum', '__(\"changed\")', 'timestamp_modify', '', 101, 1, 1, NULL, NULL, '', 6, NULL, '0', 0, NULL, NULL)") or db_die();
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (ID, db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn) VALUES (".($ii+=1).",'dateien', 'lock_user', '__(\"locked by\")', 'user_show', 'Name of the user who has locked this file temporarly', 11, 1, 1, '', '', '', 5, '0', '0', 0, NULL, NULL)") or db_die();
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (ID, db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn) VALUES (".($ii+=1).",'rts', 'ID', '__(\"ID\")', 'text', NULL, NULL, 1, 1, NULL, NULL, NULL, 2, NULL, '1', 0, NULL, NULL)") or db_die();

    // update old language vars
    $result = db_query("UPDATE ".DB_PREFIX."db_manager set form_name = '__(\"Family Name\")' WHERE form_name = '\$m1_text26'") or db_die();
    $result = db_query("UPDATE ".DB_PREFIX."db_manager set form_name = '__(\"First Name\")' WHERE form_name = '\$m1_text25'") or db_die();
    $result = db_query("UPDATE ".DB_PREFIX."db_manager set form_name = '__(\"Title\")' WHERE form_name = '\$info_text17'") or db_die();
    $result = db_query("UPDATE ".DB_PREFIX."db_manager set form_name = '__(\"Company\")' WHERE form_name = '\$m1_text28'") or db_die();
    $result = db_query("UPDATE ".DB_PREFIX."db_manager set form_name = '__(\"Phone\") 1' WHERE form_name = '\$m1_text29 1'") or db_die();
    $result = db_query("UPDATE ".DB_PREFIX."db_manager set form_name = '__(\"Phone\") 2' WHERE form_name = '\$m1_text29 2'") or db_die();
    $result = db_query("UPDATE ".DB_PREFIX."db_manager set form_name = '__(\"mobile\")' WHERE form_name = '\$admin_text92'") or db_die();
    $result = db_query("UPDATE ".DB_PREFIX."db_manager set form_name = '__(\"Fax\")' WHERE form_name = '\$m1_text30'") or db_die();
    $result = db_query("UPDATE ".DB_PREFIX."db_manager set form_name = '__(\"Street\")' WHERE form_name = '\$m1_text31'") or db_die();
    $result = db_query("UPDATE ".DB_PREFIX."db_manager set form_name = '__(\"City\")' WHERE form_name = '\$m1_text32'") or db_die();
    $result = db_query("UPDATE ".DB_PREFIX."db_manager set form_name = '__(\"Zip code\")' WHERE form_name = '\$admin_text26'") or db_die();
    $result = db_query("UPDATE ".DB_PREFIX."db_manager set form_name = '__(\"Country\")' WHERE form_name = '\$m1_text33'") or db_die();
    $result = db_query("UPDATE ".DB_PREFIX."db_manager set form_name = '__(\"State\")' WHERE form_name = '\$info_text18'") or db_die();
    $result = db_query("UPDATE ".DB_PREFIX."db_manager set form_name = '' WHERE form_name = '\$cont_usrdef1'") or db_die();
    $result = db_query("UPDATE ".DB_PREFIX."db_manager set form_name = '' WHERE form_name = '\$cont_usrdef2'") or db_die();
    $result = db_query("UPDATE ".DB_PREFIX."db_manager set form_name = '__(\"Comment\")' WHERE form_name = '\$m1_text36'") or db_die();
    $result = db_query("UPDATE ".DB_PREFIX."db_manager set form_name = '__(\"Category\")' WHERE form_name = '\$admin_text70'") or db_die();
    $result = db_query("UPDATE ".DB_PREFIX."db_manager set form_name = '__(\"Comment\")' WHERE form_name = '\$m1_text36'") or db_die();
    $result = db_query("UPDATE ".DB_PREFIX."db_manager set form_name = '__(\"Title\")' WHERE form_name = '\$info_text5'") or db_die();
    $result = db_query("UPDATE ".DB_PREFIX."db_manager set form_name = '__(\"Contact\")' WHERE form_name = '\$proj_text12'") or db_die();
    $result = db_query("UPDATE ".DB_PREFIX."db_manager set form_name = '__(\"Projects\")' WHERE form_name = '\$o_projects'") or db_die();
    $result = db_query("UPDATE ".DB_PREFIX."db_manager set form_name = '__(\"added\")' WHERE form_name = '\$notes_text2'") or db_die();
    $result = db_query("UPDATE ".DB_PREFIX."db_manager set form_name = '__(\"changed\")' WHERE form_name = '\$notes_text3'") or db_die();
    $result = db_query("UPDATE ".DB_PREFIX."db_manager set form_name = '__(\"Category\")' WHERE form_name = '\$admin_text70'") or db_die();
    $result = db_query("UPDATE ".DB_PREFIX."db_manager set form_name = '__(\"Project Name\")' WHERE form_name = '\$proj_name'") or db_die();
    $result = db_query("UPDATE ".DB_PREFIX."db_manager set form_name = '__(\"Category\")' WHERE form_name = '\$info_text8'") or db_die();
    $result = db_query("UPDATE ".DB_PREFIX."db_manager set form_name = '__(\"Begin\")' WHERE form_name = '\$proj_start'") or db_die();
    $result = db_query("UPDATE ".DB_PREFIX."db_manager set form_name = '__(\"End\")' WHERE form_name = '\$proj_end'") or db_die();
    $result = db_query("UPDATE ".DB_PREFIX."db_manager set form_name = '__(\"Leader\")' WHERE form_name = '\$proj_chef'") or db_die();
    $result = db_query("UPDATE ".DB_PREFIX."db_manager set form_name = '__(\"Contact\")' WHERE form_name = '\$proj_text12'") or db_die();
    $result = db_query("UPDATE ".DB_PREFIX."db_manager set form_name = '__(\"Hourly rate\"\")' WHERE form_name = '\$proj_text13'") or db_die();
    $result = db_query("UPDATE ".DB_PREFIX."db_manager set form_name = '__(\"Calculated budget\")' WHERE form_name = '\$proj_text14'") or db_die();
    $result = db_query("UPDATE ".DB_PREFIX."db_manager set form_name = '__(\"Aim\")' WHERE form_name = '\$proj_text11'") or db_die();
    $result = db_query("UPDATE ".DB_PREFIX."db_manager set form_name = '__(\"Priority\")' WHERE form_name = '\$proj_prio'") or db_die();
    $result = db_query("UPDATE ".DB_PREFIX."db_manager set form_name = '__(\"Status\") [%]' WHERE form_name = '\$proj_stat [%]'") or db_die();
    $result = db_query("UPDATE ".DB_PREFIX."db_manager set form_name = '__(\"Last status change\")' WHERE form_name = '\$proj_chan'") or db_die();
    $result = db_query("UPDATE ".DB_PREFIX."db_manager set form_name = '__(\"Remark\")' WHERE form_name = '\$admin_text71'") or db_die();
    $result = db_query("UPDATE ".DB_PREFIX."db_manager set form_select = '1#__(\"offered\")|2#__(\"ordered\")|3#__(\"Working\")|4#__(\"ended\")|5#__(\"stopped\")|6#__(\"Re-Opened\")|7#__(\"waiting\")' WHERE form_name = '__(\"Category\")' AND db_table='projekte' AND db_name='kategorie'") or db_die();
    // end reminder table

    // add author to db_manager
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (ID, db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn) VALUES (".($ii+=1).",'contacts'    ,'von'   ,'__(\"Author\")','user_show', NULL        , 0       , 1           , 1           , NULL       , NULL        , NULL       , 0        , 1      , '1'        , 0          , NULL  , NULL)") or db_die();
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (ID, db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn) VALUES (".($ii+=1).",'notes'       ,'von'   ,'__(\"Author\")','user_show', NULL        , 0       , 1           , 1           , NULL       , NULL        , NULL       , 0        , 1      , '1'        , 0          , NULL  , NULL)") or db_die();
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (ID, db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn) VALUES (".($ii+=1).",'projekte'    ,'von'   ,'__(\"Author\")','user_show', NULL        , 0       , 1           , 1           , NULL       , NULL        , NULL       , 0        , 1      , '1'        , 0          , NULL  , NULL)") or db_die();
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (ID, db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn) VALUES (".($ii+=1).",'dateien'     ,'von'   ,'__(\"Author\")','user_show', NULL        , 0       , 1           , 1           , NULL       , NULL        , NULL       , 0        , 1      , '1'        , 0          , NULL  , NULL)") or db_die();
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (ID, db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn) VALUES (".($ii+=1).",'mail_client' ,'von'   ,'__(\"Author\")','user_show', NULL        , 0       , 1           , 1           , NULL       , NULL        , NULL       , 0        , 1      , '1'        , 0          , NULL  , NULL)") or db_die();

    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (ID, db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn) VALUES (".($ii+=1).",'todo','von','__(\"of\")','user_show',NULL,2,1,1,NULL,NULL,NULL,3,1,'1',0,NULL,NULL)") or db_die();  // changed list_alt to 1 per case #542



    // table for touched records
    // db_records
    $result = db_query("
        CREATE TABLE ".DB_PREFIX."db_records (
        t_ID ".$db_int8_auto[$db_type].",
        t_author ".$db_int8[$db_type].",
        t_module ".$db_varchar40[$db_type].",
        t_record ".$db_int8[$db_type].",
        t_name ".$db_varchar255[$db_type].",
        t_datum ".$db_varchar20[$db_type].",
        t_touched ".$db_int2[$db_type].",
        t_archiv ".$db_int2[$db_type].",
        t_reminder ".$db_int2[$db_type].",
        t_reminder_datum ".$db_varchar20[$db_type].",
        t_wichtung ".$db_int2[$db_type].",
        t_remark ".$db_text[$db_type].",
        t_acc ".$db_text[$db_type].",
        t_gruppe ".$db_int6[$db_type].",
        t_parent ".$db_int6[$db_type].",
        PRIMARY KEY (t_ID)
    ) ");
    if ($db_type == "oracle") { sequence("db_records", "t_ID"); }
    if ($db_type == "interbase") { ib_autoinc("db_records"); }

    // set up user proxy table
    $result = db_query("
        CREATE TABLE ".DB_PREFIX."users_proxy (
        ID ".$db_int8_auto[$db_type].",
        user_ID ".$db_int8[$db_type].",
        proxy_ID ".$db_int8[$db_type].",
        PRIMARY KEY (ID)
    )");
    // create own index for proxy_ID and user_ID
    $result = db_query("CREATE INDEX users_proxy_pr_ID ON ".DB_PREFIX."users_proxy (proxy_ID)");
    $result = db_query("CREATE INDEX users_proxy_usr_ID ON ".DB_PREFIX."users_proxy (user_ID)");
    if ($db_type == "oracle") { sequence("users_proxy"); }
    if ($db_type == "interbase") { ib_autoinc("users_proxy"); }

    // set up users reader table
    $result = db_query("
        CREATE TABLE ".DB_PREFIX."users_reader (
        ID ".$db_int8_auto[$db_type].",
        user_ID ".$db_int8[$db_type].",
        reader_ID ".$db_int8[$db_type].",
        PRIMARY KEY (ID)
    )");
    // create own index for reader_ID and user_ID
    $result = db_query("CREATE INDEX users_reader_rd_ID ON ".DB_PREFIX."users_reader (reader_ID)");
    $result = db_query("CREATE INDEX users_reader_us_ID ON ".DB_PREFIX."users_reader (user_ID)");
    if ($db_type == "oracle") { sequence("users_reader"); }
    if ($db_type == "interbase") { ib_autoinc("users_reader"); }

    // set up users viewer table
    $result = db_query("
        CREATE TABLE ".DB_PREFIX."users_viewer (
        ID ".$db_int8_auto[$db_type].",
        user_ID ".$db_int8[$db_type].",
        viewer_ID ".$db_int8[$db_type].",
        PRIMARY KEY (ID)
    )");
    // create own index for viewer_ID and user_ID
    $result = db_query("CREATE INDEX users_viewer_vw_ID ON ".DB_PREFIX."users_viewer (viewer_ID)");
    $result = db_query("CREATE INDEX users_viewer_us_ID ON ".DB_PREFIX."users_viewer (user_ID)");
    if ($db_type == "oracle") { sequence("users_viewer"); }
    if ($db_type == "interbase") { ib_autoinc("users_viewer"); }

    // extend users table
    $result = db_query("ALTER TABLE ".DB_PREFIX."users ADD remark ".$db_text[$db_type]) or db_die();
    $result = db_query("ALTER TABLE ".DB_PREFIX."users ADD usertype ".$db_int1[$db_type]) or db_die();
    $result = db_query("ALTER TABLE ".DB_PREFIX."users ADD status ".$db_int1[$db_type]) or db_die();
    if ($db_type <> 'postgresql') {
        $result = db_query("ALTER TABLE ".DB_PREFIX."users CHANGE hrate hrate ".$db_varchar20[$db_type]) or db_die() ;
    }
    else {
        $result = db_query("ALTER TABLE ".DB_PREFIX."users ALTER COLUMN hrate TYPE ".$db_varchar20[$db_type]) or db_die() ;
    }
    $result = db_query("ALTER TABLE ".DB_PREFIX."users ADD INDEX (kurz)");
    $result = db_query("ALTER TABLE ".DB_PREFIX."users ADD INDEX (gruppe)");
    $result = db_query("UPDATE ".DB_PREFIX."users SET remark = '', usertype = 0, status = 0") or db_die();

    // calendar stuff
    // TODO: check if this query works on all db systems cause it has an auto increment (sequence)..
    // note this is not necessary on postgres because both are serial (auto(8) and auto(11))
    if ($db_type <> 'postgresql') {
        $result = db_query("ALTER TABLE ".DB_PREFIX."termine CHANGE ID ID ".$db_int11_auto[$db_type]) or db_die();
    }
    
    $result = db_query("ALTER TABLE ".DB_PREFIX."termine ADD parent ".$db_int11[$db_type]) or db_die();
    $result = db_query("ALTER TABLE ".DB_PREFIX."termine ADD partstat ".$db_int1[$db_type]) or db_die();
    $result = db_query("ALTER TABLE ".DB_PREFIX."termine ADD status ".$db_int1[$db_type]) or db_die();
    $result = db_query("ALTER TABLE ".DB_PREFIX."termine ADD priority ".$db_int1[$db_type]) or db_die();
    $result = db_query("ALTER TABLE ".DB_PREFIX."termine ADD serie_id ".$db_int11[$db_type]) or db_die();
    $result = db_query("ALTER TABLE ".DB_PREFIX."termine ADD serie_typ ".$db_text[$db_type]) or db_die();
    $result = db_query("ALTER TABLE ".DB_PREFIX."termine ADD serie_bis ".$db_varchar10[$db_type]) or db_die();
    $result = db_query("ALTER TABLE ".DB_PREFIX."termine ADD upload ".$db_text[$db_type]) or db_die();
    if ($db_type <> 'postgresql') {
        $result = db_query("ALTER TABLE ".DB_PREFIX."termine CHANGE note2 remark ".$db_text[$db_type]) or db_die();
    }
    else {
        $result = db_query("ALTER TABLE ".DB_PREFIX."termine RENAME COLUMN note2 TO remark") or db_die() ;
    }

    // When is set the strict mode the alter table from varchar to int could generate an error. This update will prevent it
    $result = db_query("UPDATE ".DB_PREFIX."termine SET visi = '0' WHERE visi = '' OR VISI = ' '") or db_die();
    if ($db_type <> 'postgresql') {
        $result = db_query("ALTER TABLE ".DB_PREFIX."termine CHANGE visi visi ".$db_int1[$db_type]) or db_die();
    }
    else {
        $result = db_query("ALTER TABLE ".DB_PREFIX."termine ADD tmp_visi ".$db_int1[$db_type]) or db_die() ;
        $result = db_query("UPDATE ".DB_PREFIX."termine SET tmp_visi = 0 WHERE visi = '0'");
        $result = db_query("UPDATE ".DB_PREFIX."termine SET tmp_visi = 1 WHERE visi = '1'");
        $result = db_query("UPDATE ".DB_PREFIX."termine SET tmp_visi = 2 WHERE visi = '2'");
        $result = db_query("UPDATE ".DB_PREFIX."termine SET tmp_visi = 3 WHERE visi = '3'");
        $result = db_query("ALTER TABLE ".DB_PREFIX."termine DROP visi");
        $result = db_query("ALTER TABLE ".DB_PREFIX."termine RENAME COLUMN tmp_visi TO visi") or db_die() ;
    }

    $result = db_query("ALTER TABLE ".DB_PREFIX."termine ADD INDEX (anfang)");
    $result = db_query("ALTER TABLE ".DB_PREFIX."termine ADD INDEX (ende)");
    $result = db_query("ALTER TABLE ".DB_PREFIX."termine ADD INDEX (von)");
    $result = db_query("ALTER TABLE ".DB_PREFIX."termine ADD INDEX (an)");
    $result = db_query("ALTER TABLE ".DB_PREFIX."termine ADD INDEX (visi)");
    $result = db_query("UPDATE ".DB_PREFIX."termine
                           SET parent = 0, partstat = 2, status = 0,
                               priority = 0, serie_id = 0, serie_typ = '',
                               serie_bis = '', upload = ''") or db_die();
    $result = db_query("UPDATE ".DB_PREFIX."termine SET visi = 0 WHERE visi IS NULL") or db_die();

    if ($forum && $old_config_array["PHPR_FORUM"]) {
        $result = db_query("ALTER TABLE ".DB_PREFIX."forum ADD parent ".$db_int8[$db_type]) or db_die();
        $result = db_query("UPDATE ".DB_PREFIX."forum SET parent = 0") or db_die();
    }
    if ($todo && $old_config_array["PHPR_TODO"]) {
        $result = db_query("ALTER TABLE ".DB_PREFIX."todo ADD parent ".$db_int11[$db_type]) or db_die();
        if ($db_type <> 'postgresql') {
            $result = db_query("ALTER TABLE ".DB_PREFIX."todo CHANGE progress progress ".$db_int4[$db_type]) or db_die() ;
        }
    }

    if ($projects && $old_config_array["PHPR_PROJECTS"]) {
        $result = db_query("UPDATE ".DB_PREFIX."db_manager SET db_inactive = 1 WHERE db_table='projekte' AND db_name='status'") or db_die();
        $result = db_query("UPDATE ".DB_PREFIX."db_manager SET db_inactive = 1 WHERE db_table='projekte' AND db_name='statuseintrag'") or db_die();
    }

    if (($filemanager && $old_config_array["PHPR_FILEMANAGER"]) || $file_path) {
        $result = db_query("ALTER TABLE ".DB_PREFIX."dateien ADD parent ".$db_int8[$db_type]) or db_die();
        $result = db_query("ALTER TABLE ".DB_PREFIX."dateien ADD userfile ".$db_varchar255[$db_type]." AFTER filename") or db_die() ;
        if ($db_type <> 'postgresql') {
            $result = db_query("ALTER TABLE ".DB_PREFIX."dateien CHANGE gruppe gruppe ".$db_int8[$db_type]) or db_die() ;
        }
        if ($db_type <> 'postgresql') {
            $result = db_query("ALTER TABLE ".DB_PREFIX."dateien CHANGE version version ".$db_varchar4[$db_type]) or db_die() ;
        }
        else {
            $result = db_query("ALTER TABLE ".DB_PREFIX."dateien ALTER COLUMN version TYPE ".$db_varchar4[$db_type]) or db_die() ;
        }
    }

    // Update query to get users when form_type is users_select_distinct
    $result = db_query("UPDATE ".DB_PREFIX."db_manager
                           SET form_type = 'select_sql',
                               form_select = 'select  ".DB_PREFIX."users.ID,  ".DB_PREFIX."users.nachname,  ".DB_PREFIX."users.vorname
                                                from  ".DB_PREFIX."users,  ".DB_PREFIX."termine
                                               where  ".DB_PREFIX."users.ID =  ".DB_PREFIX."termine.von and
                                                      ".DB_PREFIX.'termine.an = $user_ID
                                            group by  '.DB_PREFIX."users.ID
                                            order by nachname'
                         WHERE form_type = 'users_select_distinct'") or db_die();

    // update table "dateien"
    if (($filemanager && $old_config_array["PHPR_FILEMANAGER"]) || $file_path) {
        $result = db_query("UPDATE ".DB_PREFIX."dateien SET parent = div1") or db_die();
        $result = db_query("UPDATE ".DB_PREFIX."dateien SET div1 = NULL") or db_die();
    }

    // import old calendar data
    define('UPDATE_SCRIPT', true);
    echo "Updating resource/event tables/data ...<br />\n";
    include_once('./setup/import_cal_data.php');




} // end update 5.0


// *************
// update to 5.1
// *************

if ( ($setup == "update") and (ereg("5.0",$version_old) or ereg("4.2",$version_old) or ereg("4.1",$version_old) or
ereg("4.0",$version_old) or ereg("3.3",$version_old) or ereg("3.2",$version_old) or
ereg("3.1",$version_old) or ereg("3.0",$version_old) or ereg("2.4",$version_old) or
ereg("2.3",$version_old) or $version_old == "2.2" or $version_old == "2.1" or
$version_old == "2.0" or $version_old == "1.3" or $version_old == "1.2") ) {

    // dateien
    $result = db_query("ALTER TABLE ".DB_PREFIX."dateien ADD userfile ".$db_varchar255[$db_type]);
    $result = db_query("ALTER TABLE ".DB_PREFIX."dateien ADD parent ".$db_int8[$db_type]);

    // users **********
    if ($db_type <> 'postgresql') {
        $result = db_query("ALTER TABLE ".DB_PREFIX."users CHANGE kurz kurz ".$db_varchar40[$db_type]);
    }
    else {
        $result = db_query("ALTER TABLE ".DB_PREFIX."users ALTER COLUMN kurz TYPE ".$db_varchar40[$db_type]) or db_die() ;
    }

    // groups **********
    $result = db_query("ALTER TABLE ".DB_PREFIX."gruppen ADD ldap_name ".$db_text[$db_type]);

    // termine **********
    $result = db_query("CREATE INDEX termine_parent ON ".DB_PREFIX."termine (parent)");
    $result = db_query("CREATE INDEX termine_serie_id ON ".DB_PREFIX."termine (serie_id)");

    // profiles **********
    $result = db_query("ALTER TABLE ".DB_PREFIX."profile ADD gruppe ".$db_int8[$db_type]);
    $result = db_query("ALTER TABLE ".DB_PREFIX."profile ADD acc ".$db_text[$db_type]);
    $result = db_query("UPDATE ".DB_PREFIX."profile SET gruppe = 0");
    $result = db_query("UPDATE ".DB_PREFIX."profile SET acc = 'private'");

    // calendar **********
    if ($db_type <> 'postgresql') {
        $result = db_query("ALTER TABLE ".DB_PREFIX."termine CHANGE serie_typ serie_typ ".$db_text[$db_type]);
    }
    else {
        $result = db_query("ALTER TABLE ".DB_PREFIX."termine ALTER COLUMN serie_typ TYPE ".$db_text[$db_type]) or db_die() ;
    }

    $result = db_query("SELECT ID, serie_typ
                          FROM ".DB_PREFIX."termine
                         WHERE serie_typ = 'd'
                            OR serie_typ = 'w'
                            OR serie_typ = 'm'
                            OR serie_typ = 'y'");
    while ($row = db_fetch_row($result)) {
        $serie_typ = serialize(array( 'typ'     => $row[1].'1',
        'weekday' => array() ));
        $result2 = db_query("UPDATE ".DB_PREFIX."termine
                                SET serie_typ = '$serie_typ'
                              WHERE ID = ".(int)$row[0]);
    }
    $result = db_query("UPDATE ".DB_PREFIX."db_manager
                           SET filter_show = '0',
                               form_type = 'select_sql',
                               form_select = 'SELECT DISTINCT u.ID, u.nachname, u.vorname
                                                FROM @DB_PREFIX@termine AS t, @DB_PREFIX@users AS u
                                               WHERE t.von = u.ID
                                                 AND t.an  = \$user_ID
                                            ORDER BY u.nachname, u.vorname'
                         WHERE form_type = 'userID'
                           AND db_table = 'termine'
                           AND db_name  = 'von'");
    $result = db_query("UPDATE ".DB_PREFIX."db_manager
                           SET filter_show = '0',
                               form_type = 'select_sql',
                               form_select = 'SELECT DISTINCT u.ID, u.nachname, u.vorname
                                                FROM @DB_PREFIX@termine AS t, @DB_PREFIX@users AS u
                                               WHERE t.an  = u.ID
                                                 AND t.von = \$user_ID
                                            ORDER BY u.nachname, u.vorname'
                         WHERE form_type = 'userID'
                           AND db_table = 'termine'
                           AND db_name  = 'an'");

    // projekt statistik **********
    $result = db_query("
        CREATE TABLE ".DB_PREFIX."projekt_statistik_einstellungen (
            id ".$db_int8_auto[$db_type].",
            name ".$db_varchar60[$db_type].",
            user_ID ".$db_int8[$db_type].",
            startDate ".$db_varchar10[$db_type].",
            endDate ".$db_varchar10[$db_type].",
            withBooking ".$db_int1[$db_type].",
            withComment ".$db_int1[$db_type].",
            sortBy ".$db_varchar40[$db_type].",
            isAllProjects ".$db_int1[$db_type].",
            isAllUsers ".$db_int1[$db_type].",
            show_group ".$db_int1[$db_type].",
            PRIMARY KEY (id)
        )");

    $result = db_query("
        CREATE TABLE ".DB_PREFIX."projekt_statistik_projekte (
            stat_einstellung_ID ".$db_int8[$db_type].",
            projekt_ID ".$db_int8[$db_type]."
        )");

    $result = db_query("
        CREATE TABLE ".DB_PREFIX."projekt_statistik_user (
            stat_einstellung_ID ".$db_int8[$db_type].",
            user_ID ".$db_int8[$db_type]."
        )");


    // mail **********
    if ($db_type <> 'postgresql') {
        $result = db_query("alter table ".DB_PREFIX."mail_account CHANGE type type ".$db_varchar60[$db_type]);
    }
    else {
        $result = db_query("ALTER TABLE ".DB_PREFIX."mail_account ALTER COLUMN type TYPE ".$db_varchar60[$db_type]) or db_die() ;
    }
    
    $result = db_query("alter table ".DB_PREFIX."mail_account ADD deletion ".$db_int2[$db_type]);
    
    // add the records in the db manager
    $result = db_query("SELECT MAX(ID) FROM ".DB_PREFIX."db_manager") or db_die();
    $row = db_fetch_row($result);
    $ii = (int)$row[0];

    // Mail client db_manager fields modification (deleted remark, kat and projekt, added date_received)
    $result = db_query("DELETE FROM ".DB_PREFIX."db_manager WHERE db_table = 'mail_client' AND (db_name = 'remark' OR db_name = 'kat' OR db_name = 'projekt')") or db_die();
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (ID, db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn) VALUES (".($ii+=1).", 'mail_client', 'date_inserted', '__(\"Date\")', 'timestamp', NULL, 0, 0, 0, NULL, NULL, NULL, 4, NULL, 'on', 0, NULL, NULL)") or db_die();


    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (ID, db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn) VALUES (".($ii+=1).", 'mail_client', 'remark', '__(\"Comment\")',          'textarea', NULL, 1, 2, 2, NULL, NULL, NULL, 0, NULL, 'on', 0, NULL, NULL)") or db_die();
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (ID, db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn) VALUES (".($ii+=1).", 'mail_client', 'kat', '__(\"Category\")',     'select_category', NULL, 2, 2, 1, NULL, NULL, NULL, 0, NULL, 'on', 0, NULL, NULL)") or db_die();
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (ID, db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn) VALUES (".($ii+=1).", 'mail_client', 'projekt', '__(\"Project\")',          'project', NULL, 3, 2, 1, NULL, NULL, NULL, 0, NULL, 'on', 0, NULL, NULL)") or db_die();
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (ID, db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn) VALUES (".($ii+=1).", 'mail_client', 'contact', '__(\"Contact\")',          'contact', NULL, 4, 2, 1, NULL, NULL, NULL, 0, NULL, 'on', 0, NULL, NULL)") or db_die();
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (ID, db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn) VALUES (".($ii+=1).", 'mail_client' ,'recipient', '__(\"To\")',                'text', NULL, 0, 0, 0, NULL, NULL, NULL, 3, NULL, 'on', 0, NULL, NULL)") or db_die();

    // Mail Client: share and relation fields
    $result = db_query("alter table ".DB_PREFIX."mail_client add acc_write ".$db_text[$db_type]);
    $result = db_query("update ".DB_PREFIX."mail_client set acc_write = ''");

    $result = db_query("alter table ".DB_PREFIX."mail_client add gruppe ".$db_int8[$db_type]);
    $result = db_query("update ".DB_PREFIX."mail_client set gruppe = 0");

    $result = db_query("alter table ".DB_PREFIX."mail_client add date_inserted ".$db_varchar20[$db_type]);
    $result = db_query("alter table ".DB_PREFIX."mail_client add trash_can ".$db_char1[$db_type]);
    $result = db_query("alter table ".DB_PREFIX."mail_client add replied ".$db_int8[$db_type]);
    $result = db_query("alter table ".DB_PREFIX."mail_client add forwarded ".$db_int8[$db_type]);



    // project-related times **********
    // separated in two alter tables to add database compatibility
    $result = db_query("alter table ".DB_PREFIX."timeproj ADD module ".$db_varchar255[$db_type]);
    $result = db_query("alter table ".DB_PREFIX."timeproj ADD module_id ".$db_int8[$db_type]);

    // new chat **********
    $result = db_query("
        CREATE TABLE ".DB_PREFIX."chat (
            ID ".$db_int11_auto[$db_type].",
            gruppe ".$db_int4[$db_type].",
            von ".$db_int8[$db_type].",
            an ".$db_int8[$db_type].",
            zeile ".$db_text[$db_type].",
            eingabe ".$db_text[$db_type].",
            zeit ".$db_int11[$db_type].",
            PRIMARY KEY (ID)
        )");
    if ($db_type == "oracle") { sequence("chat"); }
    if ($db_type == "interbase") { ib_autoinc("chat"); }

    // TODO: create better indexes on this table if needed
    $result = db_query("CREATE INDEX chat_gruppe ON ".DB_PREFIX."chat (gruppe)");
    $result = db_query("CREATE INDEX chat_von ON ".DB_PREFIX."chat (von)");
    $result = db_query("CREATE INDEX chat_an ON ".DB_PREFIX."chat (an)");

    $result = db_query("
        CREATE TABLE ".DB_PREFIX."chat_alive (
            ID ".$db_int11_auto[$db_type].",
            gruppe ".$db_int4[$db_type].",
            user_name ".$db_varchar255[$db_type].",
            user_loginname ".$db_varchar255[$db_type].",
            chat_userfirstname ".$db_varchar255[$db_type].",
            zeit ".$db_int11[$db_type].",
            PRIMARY KEY (ID)
        )");
    if ($db_type == "oracle") { sequence("chat_alive"); }
    if ($db_type == "interbase") { ib_autoinc("chat_alive"); }

    $result = db_query("CREATE INDEX chat_alive_gruppe ON ".DB_PREFIX."chat_alive (gruppe)");



    // project contacts/user realation  **********
    $result = db_query("
      CREATE TABLE ".DB_PREFIX."project_users_rel (
      ID ".$db_int8_auto[$db_type].",
      project_ID ".$db_int8[$db_type].",
      user_ID ".$db_int8[$db_type].",
      role ".$db_varchar255[$db_type].",
      PRIMARY KEY (ID)
    ) ");

    if ($db_type == "oracle") { sequence("project_users_rel"); }
    if ($db_type == "interbase") { ib_autoinc("project_users_rel"); }

    // update personen to the new table
    $result = db_query("SELECT ID, personen FROM ".DB_PREFIX."projekte
                          WHERE personen <> 'N;' AND personen <> '' AND personen is not null order by ID");
    while($user_row = db_fetch_row($result)) {

        // get the users
        $personen = array();

        $tmp_array = unserialize($user_row[1]);

        if (is_array($tmp_array)) {
            foreach($tmp_array as $tmp_id => $user_data) {
                // old kurz stored
                $user_ID = slookup('users', 'ID', 'kurz', $user_data);

                // new ID stored
                if ($user_ID == '') {
                    echo __("User not found").": ".xss($user_data)." ".__("on project ID").": ".(int)$user_row[0]."<br />\n";
                }
                else {
                    // keep the user_ID
                    $personen[] = $user_ID;
                }
            }
        }

        foreach($personen as $user_ID) {
            // check if alredy exists
            $project_user_rel_result = db_query("SELECT ID FROM ".DB_PREFIX."project_users_rel
                                                  WHERE project_ID = ".(int)$user_row[0]."  
                                                    AND user_ID   = ".(int)$user_ID) or db_die();
            $project_user_rel_row = db_fetch_row($project_user_rel_result);


            if (!is_array($project_user_rel_row)) {
                // insert a new entry

                $insert_query = "INSERT INTO ".DB_PREFIX."project_users_rel
                                               (project_ID,   user_ID,  role) 
                                        VALUES (".(int)$user_row[0].", ".(int)$user_ID.", '')";

                $insert_result = db_query($insert_query) or db_die();
            }
        }


        $delete_result = db_query("UPDATE ".DB_PREFIX."projekte SET personen = ''
                                   WHERE ID = ".(int)$user_row[0]) or db_die(); 

    }

    $result = db_query("
      CREATE TABLE ".DB_PREFIX."project_contacts_rel ( 
      ID ".$db_int8_auto[$db_type].", 
      project_ID ".$db_int8[$db_type].", 
      contact_ID ".$db_int8[$db_type].", 
      role ".$db_varchar255[$db_type].", 
      PRIMARY KEY (ID)
    ) ");

    if ($db_type == "oracle") { sequence("project_contacts_rel"); }
    if ($db_type == "interbase") { ib_autoinc("project_contacts_rel"); }

    // update contacts to the new table
    $result = db_query("SELECT ID, contact FROM ".DB_PREFIX."projekte
                        WHERE contact <> '0'"); 
    while($contact_row = db_fetch_row($result)) {
        // check if alredy exists
        $project_contact_rel_result = db_query("SELECT ID FROM ".DB_PREFIX."project_contacts_rel
                                                WHERE project_ID = ".(int)$contact_row[0]."  
                                                AND contact_ID   = ".(int)$contact_row[1]) or db_die();

        $project_contact_rel_row = db_fetch_row($project_contact_rel_result);

        if (!is_array($project_contact_rel_row)) {
            // insert a new entry
            $insert_result = db_query("INSERT INTO ".DB_PREFIX."project_contacts_rel
                                           (project_ID,      contact_ID,     role) 
                                    VALUES (".(int)$contact_row[0].", ".(int)$contact_row[1].",'')") or db_die();
        }

        /* eduardo note: prepared to delete contact field
        $delete_result = db_query("UPDATE ".DB_PREFIX."projekte SET contact = 0
        WHERE ID = ".(int)$contact_row[0]) or db_die();
        */
    }

    // disable the old contact field
    $result = db_query("UPDATE ".DB_PREFIX."db_manager SET form_regexp = NULL , form_default = NULL , form_select = NULL , db_inactive = '1', rights = NULL , ownercolumn = NULL WHERE db_table = 'projekte' AND db_name = 'contact'");


    // Update profile table from 5.0 to 5.1 **********
    $query = "select p.ID, p.von, p.gruppe, u.gruppe
                     FROM ".DB_PREFIX."profile as p, ".DB_PREFIX."users as u 
                     WHERE p.von = u.ID ";

    $result = db_query($query);

    while ($row = db_fetch_row($result)) {
        $ID = $row[0];
        $von = $row[1];
        $gruppe = $row[2];
        $user_gruppe = $row[3];

        $query = "UPDATE ".DB_PREFIX."profile
                        SET 
                          acc = 'private' , 
                          gruppe = ".(int)$user_gruppe."  
                        WHERE ID = ".(int)$ID;

        $result2 = db_query($query);

    }

    // rts/helpdesk visibility update **********
    $result = db_query("ALTER TABLE ".DB_PREFIX."rts ADD visibility ".$db_varchar20[$db_type]);

    $result = db_query("UPDATE ".DB_PREFIX."rts SET visibility = acc
                              WHERE acc is not null 
                                    AND acc <> '' 
                                    AND (visibility is null 
                                         OR visibility = '')");

    $result = db_query("UPDATE ".DB_PREFIX."rts SET acc = ''
                              WHERE acc is not null 
                                    AND acc <> '' 
                                    AND (visibility is null 
                                         OR visibility = '')");


    // rts/helpdesk visibility update **********
    $result = db_query("ALTER TABLE ".DB_PREFIX."projekt_statistik_einstellungen ADD show_group ".$db_int1[$db_type]);
    $result = db_query("ALTER TABLE ".DB_PREFIX."projekt_statistik_einstellungen ADD period ".$db_varchar3[$db_type]);


    // update list_alt to 5.1 version **********
    $result = db_query("UPDATE ".DB_PREFIX."db_manager SET list_alt = 1 WHERE db_table = 'contacts' AND db_name = 'von'");
    $result = db_query("UPDATE ".DB_PREFIX."db_manager SET list_alt = 2 WHERE db_table = 'contacts' AND db_name = 'bemerkung'");
    $result = db_query("UPDATE ".DB_PREFIX."db_manager SET list_alt = 3 WHERE db_table = 'contacts' AND db_name = 'tel2'");
    $result = db_query("UPDATE ".DB_PREFIX."db_manager SET list_alt = 4 WHERE db_table = 'contacts' AND db_name = 'mobil'");
    $result = db_query("UPDATE ".DB_PREFIX."db_manager SET list_alt = 0 WHERE db_table = 'contacts' AND db_name = 'email2'");
    $result = db_query("update ".DB_PREFIX."db_manager SET list_alt = 0 WHERE db_table = 'contacts' AND db_name = 'fax'");
    $result = db_query("UPDATE ".DB_PREFIX."db_manager SET list_alt = 0 WHERE db_table = 'contacts' AND db_name = 'firma'");
    $result = db_query("UPDATE ".DB_PREFIX."db_manager SET list_alt = 0 WHERE db_table = 'contacts' AND db_name = 'tel1'");
    $result = db_query("UPDATE ".DB_PREFIX."db_manager SET list_alt = 1 WHERE db_table = 'dateien' AND db_name = 'von'");
    $result = db_query("UPDATE ".DB_PREFIX."db_manager SET list_alt = 2 WHERE db_table = 'dateien' AND db_name = 'kat'");
    $result = db_query("UPDATE ".DB_PREFIX."db_manager SET list_alt = 3 WHERE db_table = 'dateien' AND db_name = 'lock_user'");
    $result = db_query("UPDATE ".DB_PREFIX."db_manager SET list_alt = 1 WHERE db_table = 'projekte' AND db_name = 'von'");
    $result = db_query("UPDATE ".DB_PREFIX."db_manager SET list_alt = 2 WHERE db_table = 'projekte' AND db_name = 'wichtung'");
    $result = db_query("UPDATE ".DB_PREFIX."db_manager SET list_alt = 3 WHERE db_table = 'projekte' AND db_name = 'status'");
    $result = db_query("UPDATE ".DB_PREFIX."db_manager SET list_alt = 4 WHERE db_table = 'projekte' AND db_name = 'note'");
    $result = db_query("UPDATE ".DB_PREFIX."db_manager SET list_alt = 0 WHERE db_table = 'projekte' AND db_name = 'stundensatz'");
    $result = db_query("UPDATE ".DB_PREFIX."db_manager SET list_alt = 0 WHERE db_table = 'projekte' AND db_name = 'budget'");
    $result = db_query("UPDATE ".DB_PREFIX."db_manager SET list_alt = 0 WHERE db_table = 'projekte' AND db_name = 'chef'");
    $result = db_query("UPDATE ".DB_PREFIX."db_manager SET list_alt = 0 WHERE db_table = 'projekte' AND db_name = 'contact'");
    $result = db_query("UPDATE ".DB_PREFIX."db_manager SET list_alt = 1 WHERE db_table = 'notes' AND db_name = 'von'");
    $result = db_query("UPDATE ".DB_PREFIX."db_manager SET list_alt = 2 WHERE db_table = 'notes' AND db_name = 'div1'");
    $result = db_query("UPDATE ".DB_PREFIX."db_manager SET list_alt = 3 WHERE db_table = 'notes' AND db_name = 'div2'");
    $result = db_query("UPDATE ".DB_PREFIX."db_manager SET list_alt = 1 WHERE db_table = 'rts' AND db_name = 'von'");
    $result = db_query("UPDATE ".DB_PREFIX."db_manager SET list_alt = 2 WHERE db_table = 'rts' AND db_name = 'note'");
    $result = db_query("UPDATE ".DB_PREFIX."db_manager SET list_alt = 0 WHERE db_table = 'rts' AND db_name = 'due_date'");
    $result = db_query("UPDATE ".DB_PREFIX."db_manager SET list_alt = 0 WHERE db_table = 'rts' AND db_name = 'assigned'");
    $result = db_query("UPDATE ".DB_PREFIX."db_manager SET list_alt = 2 WHERE db_table = 'todo' AND db_name = 'priority'");
    $result = db_query("UPDATE ".DB_PREFIX."db_manager SET list_alt = 2 WHERE db_table = 'mail_client' AND db_name = 'remark'");
    $result = db_query("UPDATE ".DB_PREFIX."db_manager SET list_alt = 3 WHERE db_table = 'mail_client' AND db_name = 'kat'");
    $result = db_query("UPDATE ".DB_PREFIX."db_manager SET list_alt = 3 WHERE db_table = 'mail_client' AND db_name = 'projekt'");

    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (ID, db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn) VALUES (".($ii+=1).", 'termine', 'remark', '__(\"Remark\")', 'text', '', 0, 0, 0, '', NULL, NULL, 0, 2, 0, 0, NULL, NULL)") or db_die();
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (ID, db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn) VALUES (".($ii+=1).", 'termine', 'ort', '__(\"Location\")', 'text', '', 0, 0, 0, '', NULL, NULL, 0, 3, 0, 0, NULL, NULL)") or db_die();

    $result = db_query("UPDATE ".DB_PREFIX."db_manager SET form_name = '__(\"Change Filename\")' WHERE db_table = 'datein' AND db_name = 'filename'");

    // If exists mail client recipient or sender on db_manager, then we will change th field type;

    $result = db_query("UPDATE ".DB_PREFIX."db_manager SET form_type = 'email' WHERE db_table = 'mail_client' AND db_name = 'sender'");
    $result = db_query("UPDATE ".DB_PREFIX."db_manager SET form_type = 'email' WHERE db_table = 'mail_client' AND db_name = 'recipient'");


    // rts fields
    $result = db_query("ALTER TABLE ".DB_PREFIX."rts ADD acc_read ".$db_text[$db_type]);
    $result = db_query("ALTER TABLE ".DB_PREFIX."rts ADD acc_write ".$db_text[$db_type]);
    $result = db_query("ALTER TABLE ".DB_PREFIX."rts ADD von ".$db_int8[$db_type]);
    $result = db_query("ALTER TABLE ".DB_PREFIX."rts ADD gruppe ".$db_int6[$db_type]);
    $result = db_query("ALTER TABLE ".DB_PREFIX."rts ADD parent ".$db_int8[$db_type]);


} // end update 5.1

// *************
// update to 5.2
// *************

if ( ($setup == "update") and (ereg("5.1",$version_old) or ereg("5.0",$version_old) or ereg("4.2",$version_old) or ereg("4.1",$version_old) or
ereg("4.0",$version_old) or ereg("3.3",$version_old) or ereg("3.2",$version_old) or
ereg("3.1",$version_old) or ereg("3.0",$version_old) or ereg("2.4",$version_old) or
ereg("2.3",$version_old) or $version_old == "2.2" or $version_old == "2.1" or
$version_old == "2.0" or $version_old == "1.3" or $version_old == "1.2") ) {

    // ********************
    // * rts modifications

    $result = db_query("ALTER TABLE ".DB_PREFIX."rts ADD file ".$db_varchar255[$db_type]);
    $result = db_query("ALTER TABLE ".DB_PREFIX."rts ADD category ".$db_varchar255[$db_type]);
    $result = db_query("ALTER TABLE ".DB_PREFIX."rts ADD lock_user ".$db_int8[$db_type]);


    // db_remarks
    $result = db_query("CREATE TABLE ".DB_PREFIX."db_remarks (
          ID ".$db_int8_auto[$db_type].",
          module ".$db_varchar40[$db_type].",
          module_ID ".$db_int8[$db_type].",
          date ".$db_varchar20[$db_type].",
          remark ".$db_text[$db_type].",
          author ".$db_varchar80[$db_type].",
          parent ".$db_int8[$db_type].",
          upload text,
          PRIMARY KEY (ID)
        ) ");

    if ($result) { echo __('Table db_remarks (for the help desk) created').".<br />\n"; }
    elseif(get_sql_errno($result) != $db_error_code_table_exists[$db_type]) {
        echo __('An error ocurred while creating table: ')." db_remarks!<br />\n"; $error = 1;
    }
    if ($db_type == "oracle") { sequence("db_remarks"); }
    if ($db_type == "interbase") { ib_autoinc("db_remarks"); }

    // db_accounts
    $result = db_query("CREATE TABLE ".DB_PREFIX."db_accounts (
          ID ".$db_int8_auto[$db_type].",
          name ".$db_varchar60[$db_type].",
          users ".$db_int8[$db_type].",
          gruppe ".$db_int6[$db_type].",
          escalation ".$db_int8[$db_type].",
          account_ID ".$db_int8[$db_type].",
          account_type ".$db_varchar80[$db_type].",
          message ".$db_text[$db_type].",
          PRIMARY KEY (ID)
        ) ");
    if ($result) { echo __('Table db_accounts (for the help desk) created').".<br />\n"; }
    elseif(get_sql_errno($result) != $db_error_code_table_exists[$db_type]) {
        echo __('An error ocurred while creating table: ')." db_accounts<br />\n"; $error = 1;
    }
    if ($db_type == "oracle") { sequence("db_accounts"); }
    if ($db_type == "interbase") { ib_autoinc("db_accounts"); }


    // global_mail_account
    $result = db_query("CREATE TABLE ".DB_PREFIX."global_mail_account (
          ID ".$db_int8_auto[$db_type].",
          module ".$db_varchar40[$db_type].",
          accountname ".$db_varchar40[$db_type].",
          hostname ".$db_varchar80[$db_type].",
          type ".$db_varchar60[$db_type].",
          username ".$db_varchar60[$db_type].",
          password ".$db_varchar60[$db_type].",
          PRIMARY KEY (ID)
        ) ");

    if ($result) { echo __('Table global_mail_account (for the help desk) created').".<br />\n"; }
    elseif(get_sql_errno($result) != $db_error_code_table_exists[$db_type]) {
        echo __('An error ocurred while creating table: ')." global_mail_account<br />\n"; $error = 1;
    }
    if ($db_type == "oracle") { sequence("global_mail_account"); }
    if ($db_type == "interbase") { ib_autoinc("global_mail_account"); }
    
    // add the records in the db manager
    $result = db_query("SELECT MAX(ID) FROM ".DB_PREFIX."db_manager") or db_die();
    $row = db_fetch_row($result);
    $ii = (int)$row[0];

    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (ID, db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn) VALUES (".($ii+=1).", 'rts', 'name', '__(\"Title\")', 'text', 'the title of the request', 1, 1, 1, NULL, NULL, NULL, 1, '0', '1', 0, NULL, NULL)") or db_die();
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (ID, db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn) VALUES (".($ii+=1).", 'rts', 'note', '__(\"Problem Description\")', 'textarea', 'the body of the request set by the customer', 15, 1, 5, '', '', '', 0, 'on', '1', 0, NULL, NULL)") or db_die();
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (ID, db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn) VALUES (".($ii+=1).", 'rts', 'submit', '__(\"Date\")', 'timestamp_create', 'date/time the request ha been submitted', 5, 1, 1, '', '', '', 2, 'on', '1', 0, NULL, NULL)") or db_die();
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (ID, db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn) VALUES (".($ii+=1).", 'rts', 'von', '__(\"Author\")', 'authorID', 'the user who wrote this request', 3, 1, 1, '', '', '', 2, 'on', '1', 0, NULL, NULL)") or db_die();
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (ID, db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn) VALUES (".($ii+=1).", 'rts', 'contact', '__(\"Contact\")', 'contact_create', 'contact related to this request', 7, 1, 1, NULL, NULL, NULL, 0, '0', '0', 0, NULL, NULL)") or db_die();
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (ID, db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn) VALUES (".($ii+=1).", 'rts', 'due_date', '__(\"Due date\")', 'date', 'due date of this request', 6, 1, 1, '', '', '', 4, 'on', '1', 0, NULL, NULL)") or db_die();
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (ID, db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn) VALUES (".($ii+=1).", 'rts', 'assigned', '__(\"Assigned\")', 'userID', 'assign the request to this user', 4, 1, 1, '', '', '', 3, 'on', '1', 0, NULL, NULL)") or db_die();
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (ID, db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn) VALUES (".($ii+=1).", 'rts', 'priority', '__(\"Priority\")', 'select_values', 'set the priority of this project', 10, 1, 1, NULL, NULL, '0|1|2|3|4|5|6|7|8|9', 6, NULL, '1', 0, NULL, NULL)") or db_die();
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (ID, db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn) VALUES (".($ii+=1).", 'rts', 'lock_user', '__(\"locked by\")', 'userID', 'This ticket was locked by the follwing user', 0, 1, 1, NULL, NULL, NULL, 4, NULL, NULL, 0, NULL, NULL)") or db_die();
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (ID, db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn) VALUES (".($ii+=1).", 'rts', 'solution', '__(\"solve\")', 'textarea', 'A text will cause: a mail to the customer and closing the request', 0, 1, 1, NULL, NULL, NULL, 0, '0', '1', 0, NULL, NULL)") or db_die();
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (ID, db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn) VALUES (".($ii+=1).", 'rts', 'solved', '__(\"solved\") __(\"From\")', 'user_show', 'the user who has solved this request', 13, 1, 1, '', '', '', 0, 'on', '1', 0, NULL, NULL)") or db_die();
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (ID, db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn) VALUES (".($ii+=1).", 'rts', 'solve_time', '__(\"solved\")', 'timestamp_show', 'date and time when the request has been solved', 14, 1, 1, NULL, NULL, NULL, 0, '0', '0', 0, NULL, NULL)") or db_die();
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (ID, db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn) VALUES (".($ii+=1).", 'rts', 'category', '__(\"Account\")', 'select_sql', '', 8, 1, 1, '', '', 'SELECT ID,name                                                FROM @DB_PREFIX@db_accounts', 2, '', '1', 0, NULL, NULL)") or db_die();
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (ID, db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn) VALUES (".($ii+=1).", 'rts', 'proj', '__(\"Projects\")', 'project', 'project related to this request', 9, 1, 1, NULL, NULL, '', 0, '0', '0', 0, NULL, NULL)") or db_die();
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (ID, db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn) VALUES (".($ii+=1).", 'rts', 'status', '__(\"Status\")', 'select_values', 'state of this request', 0, 1, 1, '', '', '1#__(\"open\")|2# __(\"assigned\")|3#__(\"solved\")|4# __(\"verified\")|5# __(\"closed\")', 7, 'on', '1', 0, NULL, NULL)") or db_die();
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (ID, db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn) VALUES (".($ii+=1).", 'rts', 'file', '__(\"Attachment\")', 'upload', '', 11, 1, 1, '', '', '', 0, '', '', 0, NULL, NULL)") or db_die();
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (ID, db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn) VALUES (".($ii+=1).", 'rts', 'ID', '__(\"Ticket ID\")', 'display', '', 2, 1, 1, '', '', '', 5, '', '1', 0, NULL, NULL)") or db_die();

    // delete old fields from db_manager
    $result = db_query("DELETE FROM ".DB_PREFIX."db_manager WHERE db_table = 'rts' AND db_name = 'div1'");
    $result = db_query("DELETE FROM ".DB_PREFIX."db_manager WHERE db_table = 'rts' AND db_name = 'div2'");
    $result = db_query("DELETE FROM ".DB_PREFIX."db_manager WHERE db_table = 'rts' AND db_name = 'acc'");
    $result = db_query("DELETE FROM ".DB_PREFIX."db_manager WHERE db_table = 'rts' AND db_name = 'email'");
    $result = db_query("DELETE FROM ".DB_PREFIX."db_manager WHERE db_table = 'rts' AND db_name = 'remark'");


    // update remarks
    $result = db_query("SELECT ID, remark, von, parent FROM ".DB_PREFIX."rts");

    while ($row = db_fetch_row($result)) {
        $result2 = db_query("INSERT INTO ".DB_PREFIX."db_remarks
                             (module, module_ID,       date,      remark,    author,    parent, upload) 
                             VALUES 
                             ('helpdesk',".(int)$row[0].",  now(),'{$row[1]}',".(int)$row[2].",0 ,'')");
    }

    // drop of old fields
    $result = db_query("ALTER TABLE ".DB_PREFIX."rts DROP div1");
    $result = db_query("ALTER TABLE ".DB_PREFIX."rts DROP div2");
    $result = db_query("ALTER TABLE ".DB_PREFIX."rts DROP acc");
    $result = db_query("ALTER TABLE ".DB_PREFIX."rts DROP email");
    $result = db_query("ALTER TABLE ".DB_PREFIX."rts DROP remark");

    // end of rts update *
    // *******************


    // **********************
    // * history table update

    if ($db_type == 'postgresql') {
        $result = db_query("ALTER TABLE ".DB_PREFIX."history RENAME COLUMN _date TYPE h_date") or db_die() ;
        $result = db_query("ALTER TABLE ".DB_PREFIX."history RENAME COLUMN _table TYPE h_table") or db_die() ;
        $result = db_query("ALTER TABLE ".DB_PREFIX."history RENAME COLUMN _field TYPE h_field") or db_die() ;
        $result = db_query("ALTER TABLE ".DB_PREFIX."history RENAME COLUMN _record TYPE h_record") or db_die() ;
    }
    elseif ($db_type <> 'sqlite') {
        $result = db_query("ALTER TABLE ".DB_PREFIX."history CHANGE COLUMN _date h_date ".$db_varchar20[$db_type]);
        $result = db_query("ALTER TABLE ".DB_PREFIX."history CHANGE COLUMN _table h_table ".$db_varchar60[$db_type]);
        $result = db_query("ALTER TABLE ".DB_PREFIX."history CHANGE COLUMN _field h_field ".$db_varchar60[$db_type]);
        $result = db_query("ALTER TABLE ".DB_PREFIX."history CHANGE COLUMN _record h_record ".$db_int8[$db_type]);
    }
    else {
        $result = db_query("CREATE TABLE ".DB_PREFIX."history_new (
                              ID ".$db_int8_auto[$db_type].",
                              von ".$db_int8[$db_type].",
                              h_date ".$db_varchar20[$db_type].",
                              h_table ".$db_varchar60[$db_type].",
                              h_field ".$db_varchar60[$db_type].",
                              h_record ".$db_int8[$db_type].",
                              last_value ".$db_text[$db_type].",
                              new_value ".$db_text[$db_type].",
                              PRIMARY KEY (ID)) ");
        $result = db_query("INSERT INTO ".DB_PREFIX."history_new SELECT ID,von, _date, _table, _field, _record, last_value, new_value FROM ".DB_PREFIX."history");

        $result = db_query("DROP TABLE ".DB_PREFIX."history");

        $result = db_query("CREATE TABLE ".DB_PREFIX."history (
                              ID ".$db_int8_auto[$db_type].",
                              von ".$db_int8[$db_type].",
                              h_date ".$db_varchar20[$db_type].",
                              h_table ".$db_varchar60[$db_type].",
                              h_field ".$db_varchar60[$db_type].",
                              h_record ".$db_int8[$db_type].",
                              last_value ".$db_text[$db_type].",
                              new_value ".$db_text[$db_type].",
                              PRIMARY KEY (ID)) ");

        $result = db_query("INSERT INTO ".DB_PREFIX."history SELECT ID,von, h_date, h_table, h_field, h_record, last_value, new_value FROM ".DB_PREFIX."history_new");

        $result = db_query("DROP TABLE ".DB_PREFIX."history_new");

    }

    // end of history update *
    // ***********************

    // Alter table filter
    $result = db_query("ALTER TABLE ".DB_PREFIX."filter ADD filter_sort ".$db_varchar100[$db_type]);
    $result = db_query("ALTER TABLE ".DB_PREFIX."filter ADD filter_direction ".$db_varchar4[$db_type]);
    $result = db_query("ALTER TABLE ".DB_PREFIX."filter ADD filter_operator ".$db_varchar10[$db_type]);

    // Acc user field update

    // first normal users
    $result = db_query("UPDATE ".DB_PREFIX."users SET usertype = 0 WHERE acc = 'uy' OR acc = 'u' OR acc = 'un' AND usertype <> 1");
    $result = db_query("UPDATE ".DB_PREFIX."users SET acc = '' WHERE acc = 'uy' OR acc = 'u' OR acc = 'un' AND usertype <> 1");
    // chief right users
    $result = db_query("UPDATE ".DB_PREFIX."users SET usertype = 2 WHERE acc = 'cy' OR acc = 'cn' AND usertype <> 1");
    $result = db_query("UPDATE ".DB_PREFIX."users SET acc = '' WHERE acc = 'cy' OR acc = 'nu' AND usertype <> 1");
    // at last, the admins
    $result = db_query("UPDATE ".DB_PREFIX."users SET usertype = 3 WHERE (acc = 'an' OR acc = 'ay' OR acc = 'a') AND usertype <> 1");
    $result = db_query("UPDATE ".DB_PREFIX."users SET acc = '' WHERE (acc = 'an' OR acc = 'ay' OR acc = 'a') AND usertype <> 1");


    // *******************************
    // Update mail_client fileds     *

    // Update replied and forwarded email fields
    if ($db_type <> 'sqlite') {
        $result = db_query("ALTER TABLE ".DB_PREFIX."mail_client ADD COLUMN replied_new ".$db_int8[$db_type]);
        $result = db_query("ALTER TABLE ".DB_PREFIX."mail_client ADD COLUMN forwarded_new ".$db_int8[$db_type]);
        $result = db_query("UPDATE ".DB_PREFIX."mail_client SET forwarded_new = 1 WHERE forwarded = 'Y'");
        $result = db_query("UPDATE ".DB_PREFIX."mail_client SET replied_new = 1 WHERE forwarded = 'Y'");
        $result = db_query("ALTER TABLE ".DB_PREFIX."mail_client DROP COLUMN replied");
        $result = db_query("ALTER TABLE ".DB_PREFIX."mail_client DROP COLUMN forwarded");
        if ($db_type == 'postgresql') {
            $result = db_query("ALTER TABLE ".DB_PREFIX."mail_client RENAME COLUMN replied_new TO replied");
            $result = db_query("ALTER TABLE ".DB_PREFIX."mail_client RENAME COLUMN forwarded_new TO forwarded");
            $result = db_query("ALTER TABLE ".DB_PREFIX."mail_client ALTER COLUMN parent TYPE ".$db_int8[$db_type]);
        }
        else {
            $result = db_query("ALTER TABLE ".DB_PREFIX."mail_client CHANGE COLUMN replied_new replied ".$db_int8[$db_type]);
            $result = db_query("ALTER TABLE ".DB_PREFIX."mail_client CHANGE COLUMN forwarded_new forwarded ".$db_int8[$db_type]);
            $result = db_query("ALTER TABLE ".DB_PREFIX."mail_client MODIFY COLUMN parent ".$db_int8[$db_type]);
        }

    }
    else {
        $result = db_query("CREATE TABLE ".DB_PREFIX."mail_client_new (
                              ID ".$db_int8_auto[$db_type].",
                              von ".$db_int8[$db_type].",
                              subject ".$db_varchar255[$db_type].",
                              body ".$db_text[$db_type].",
                              sender ".$db_varchar128[$db_type].",
                              recipient ".$db_text[$db_type].",
                              cc ".$db_text[$db_type].",
                              kat ".$db_varchar40[$db_type].",
                              remark ".$db_text[$db_type].",
                              date_received ".$db_varchar20[$db_type].",
                              touched ".$db_int1[$db_type].",
                              typ ".$db_char1[$db_type].",
                              parent ".$db_int8[$db_type].",
                              date_sent ".$db_varchar20[$db_type].",
                              header ".$db_text[$db_type].",
                              replyto ".$db_varchar128[$db_type].",
                              acc ".$db_text[$db_type].",
                              acc_write ".$db_text[$db_type].",
                              gruppe ".$db_int8[$db_type].",
                              body_html ".$db_text[$db_type].",
                              message_ID ".$db_varchar255[$db_type].",
                              projekt ".$db_int6[$db_type].",
                              contact ".$db_int6[$db_type].",
                              date_inserted ".$db_varchar20[$db_type].",
                              trash_can ".$db_char1[$db_type].",
                              replied ".$db_int8[$db_type].",
                              forwarded ".$db_int8[$db_type].",
                              PRIMARY KEY (ID)) ");
        $query = "SELECT ID, von, subject, body, sender, recipient, cc, kat, remark, date_received, touched, typ,
                         parent, date_sent, header, replyto, acc, acc_write, gruppe, body_html, message_ID, projekt, 
                         contact, date_inserted, trash_can, replied, forwarded
                    FROM ".DB_PREFIX."mail_client";

        $result = db_query($query) or db_die();

        while ($row = db_fetch_row()) {

            if ($row[25] == 'Y') { $row[25] = 1; } else { $row[25] = 0; }

            if ($row[26] == 'Y') { $row[26] = 1; } else { $row[26] = 0; }

            $query = "INSERT INTO ".DB_PREFIX."mail_client_new (ID, von, subject, body, sender, recipient, cc, kat, remark, date_received, touched, typ,
                         parent, date_sent, header, replyto, acc, acc_write, gruppe, body_html, message_ID, projekt, 
                         contact, date_inserted, trash_can, replied, forwarded) VALUES 
                         ('".addslashes($row[0])."','".addslashes($row[1])."','".addslashes($row[2])."','".addslashes($row[3])."','".addslashes($row[4])."','".addslashes($row[5])."','".addslashes($row[6])."','".addslashes($row[7])."',
                         '".addslashes($row[8])."','".addslashes($row[9])."','".addslashes($row[10])."','".addslashes($row[11])."','".addslashes($row[12])."','".addslashes($row[13])."','".addslashes($row[14])."','".addslashes($row[15])."',
                         '".addslashes($row[16])."','".addslashes($row[17])."','".addslashes($row[18])."','".addslashes($row[19])."','".addslashes($row[20])."','".addslashes($row[21])."','".addslashes($row[22])."','".addslashes($row[23])."',
                         '".addslashes($row[24])."','".addslashes($row[25])."','".addslashes($row[26])."')";
        }

        $result = db_query("DROP TABLE ".DB_PREFIX."mail_client");

        $result = db_query("CREATE TABLE ".DB_PREFIX."mail_client (
                              ID ".$db_int8_auto[$db_type].",
                              von ".$db_int8[$db_type].",
                              subject ".$db_varchar255[$db_type].",
                              body ".$db_text[$db_type].",
                              sender ".$db_varchar128[$db_type].",
                              recipient ".$db_text[$db_type].",
                              cc ".$db_text[$db_type].",
                              kat ".$db_varchar40[$db_type].",
                              remark ".$db_text[$db_type].",
                              date_received ".$db_varchar20[$db_type].",
                              touched ".$db_int1[$db_type].",
                              typ ".$db_char1[$db_type].",
                              parent ".$db_int8[$db_type].",
                              date_sent ".$db_varchar20[$db_type].",
                              header ".$db_text[$db_type].",
                              replyto ".$db_varchar128[$db_type].",
                              acc ".$db_text[$db_type].",
                              acc_write ".$db_text[$db_type].",
                              gruppe ".$db_int8[$db_type].",
                              body_html ".$db_text[$db_type].",
                              message_ID ".$db_varchar255[$db_type].",
                              projekt ".$db_int6[$db_type].",
                              contact ".$db_int6[$db_type].",
                              date_inserted ".$db_varchar20[$db_type].",
                              trash_can ".$db_char1[$db_type].",
                              replied ".$db_int8[$db_type].",
                              forwarded ".$db_int8[$db_type].",
                              PRIMARY KEY (ID)) ");

        $result = db_query("INSERT INTO ".DB_PREFIX."mail_client SELECT ID, von, subject, body, sender, recipient, cc, kat, remark, date_received, touched, typ,
                         parent, date_sent, header, replyto, acc, acc_write, gruppe, body_html, message_ID, projekt, 
                         contact, date_inserted, trash_can, replied, forwarded FROM ".DB_PREFIX."mail_client_new");

        $result = db_query("DROP TABLE ".DB_PREFIX."mail_client_new");

    }

    $result = db_query("ALTER TABLE ".DB_PREFIX."mail_client ADD account_ID ".$db_int8[$db_type]);

    // End update mail_client fields *
    // *******************************

    // db_manager update
    $result = db_query("ALTER TABLE ".DB_PREFIX."db_manager ADD form_length ".$db_int11[$db_type]);

    // dateien update
    $result = db_query("ALTER TABLE ".DB_PREFIX."dateien ADD versioning ".$db_int1[$db_type]);

    $result = db_query("CREATE TABLE ".DB_PREFIX."datei_history (
        ID ".$db_int8_auto[$db_type].",
        date ".$db_varchar20[$db_type].",
        remark ".$db_text[$db_type].",
        author ".$db_int6[$db_type].",
        parent ".$db_int8[$db_type].",
        version ".$db_varchar4[$db_type].",
        tempname ".$db_varchar255[$db_type].",
        userfile ".$db_varchar255[$db_type].",
        PRIMARY KEY (ID)
      ) ");

    if ($result) {
        echo __('Table datei_history (for file-handling) created').".<br />\n";
    }
    elseif(get_sql_errno($result) != $db_error_code_table_exists[$db_type]) {
        echo __('An error ocurred while creating table: ')." datei_history<br />\n"; $error = 1;
    }
    if ($db_type == "oracle") { sequence("datei_history"); }
    if ($db_type == "interbase") {ib_autoinc("datei_history"); }

    $result = db_query("UPDATE ".DB_PREFIX."dateien SET versioning = 1, typ = 'f' where typ = 'fv'");

    // db_manager update
    $result = db_query("ALTER TABLE ".DB_PREFIX."db_manager ADD field_type ".$db_varchar20[$db_type]);

    // set integer values for strict mode compatibility
    $result = db_query("UPDATE ".DB_PREFIX."db_manager SET field_type ='integer' where db_table = 'contacts' and db_name in ('von')");
    $result = db_query("UPDATE ".DB_PREFIX."db_manager SET field_type ='integer' where db_table = 'notes' and db_name in ('von','contact','projekt')");
    $result = db_query("UPDATE ".DB_PREFIX."db_manager SET field_type ='integer' where db_table = 'projekte' and db_name in ('status','statuseintrag','contact','stundensatz','budget','von')");
    $result = db_query("UPDATE ".DB_PREFIX."db_manager SET field_type ='integer' where db_table = 'rts' and db_name in ('contact','priority','lock_user','solved','proj','ID','von')");
    $result = db_query("UPDATE ".DB_PREFIX."db_manager SET field_type ='integer' where db_table = 'dateien' and db_name in ('contact','filesizelock_user','von')");
    $result = db_query("UPDATE ".DB_PREFIX."db_manager SET field_type ='integer' where db_table = 'todo' and db_name in ('priority','project','contact','von','ext','progress','status','von')");
    $result = db_query("UPDATE ".DB_PREFIX."db_manager SET field_type ='integer' where db_table = 'termine' and db_name in ('von','an','partstat','von')");
    $result = db_query("UPDATE ".DB_PREFIX."db_manager SET field_type ='integer' where db_table = 'mail_client' and db_name in ('von','projekt','contact')");
    $result = db_query("UPDATE ".DB_PREFIX."db_manager SET field_type ='integer' where db_table = 'contacts' and db_name in ('t_archiv','t_touched','t_wichtung','t_record')");


    // alter filter table
    if ($db_type <> 'postgresql') {
        $result = db_query("ALTER TABLE ".DB_PREFIX."filter CHANGE von von ".$db_int8[$db_type]);
    }
    else {
        $result = db_query("ALTER TABLE ".DB_PREFIX."filter ALTER COLUMN von TYPE ".$db_int8[$db_type]) or db_die() ;
    }

    // Index for timecard data search
    $result = db_query("CREATE INDEX parent ON ".DB_PREFIX."projekte (parent)");


    // *********************
    // Test account password
    // *********************

    if (isset($_SESSION['change_test']) && $_SESSION['change_test'] && $_SESSION['testpass'] <> '' && $_SESSION['testpass'] <> 'test') {

        if (isset($testcreation) && $testcreation == 1) {
            if ($pw_crypt) {
                $pw_test = md5('phprojektmd5'.$testpass);
            }
            else {
                $pw_test = $testpass;
            }

            $query = "UPDATE ".DB_PREFIX."users
                         SET pw = '$pw_test'
                       WHERE nachname = 'test' AND vorname = 'test'";

            $result = db_query($query) or db_die();
        }
    }

    if (isset($_SESSION['change_test']) && $_SESSION['change_test'] && isset($_SESSION['testcreation']) && $_SESSION['testcreation'] == 0) {

        $query = "UPDATE ".DB_PREFIX."users SET status = 1 WHERE nachname = 'test' AND vorname = 'test'";
        $result = db_query($query) or db_die();
    }


    // update timeproj table
    if ($db_type == 'postgresql') {
        $result = db_query("ALTER TABLE ".DB_PREFIX."timeproj ALTER COLUMN note TYPE ".$db_text[$db_type]) or db_die() ;
    }
    elseif ($db_type <> 'sqlite') {
        $result = db_query("ALTER TABLE ".DB_PREFIX."timeproj MODIFY COLUMN note ".$db_text[$db_type]);

    }
    else {
        $result = db_query("CREATE TABLE ".DB_PREFIX."timeproj_new (
                              ID ".$db_int8_auto[$db_type].",
                              users ".$db_int4[$db_type].",
                              projekt ".$db_int4[$db_type].",
                              datum ".$db_varchar10[$db_type].",
                              h ".$db_int2[$db_type].",
                              m ".$db_int2[$db_type].",
                              kat ".$db_varchar255[$db_type].",
                              note ".$db_text[$db_type].",
                              ext ".$db_int2[$db_type].",
                              div1 ".$db_varchar40[$db_type].",
                              div2 ".$db_varchar40[$db_type].",
                              module ".$db_varchar255[$db_type].",
                              module_id ".$db_int8[$db_type].",
                              PRIMARY KEY (ID) ");
        $query = "SELECT ID, users, projekt, datum, h, m, kat, note, ext, div1, div2, module, module_id
                    FROM ".DB_PREFIX."timeproj";

        $result = db_query($query) or db_die();

        while ($row = db_fetch_row()) {

            $query = "INSERT INTO ".DB_PREFIX."timeproj_new (ID, users, projekt, datum, h, m, kat, note, ext, div1, div2, module, module_id ) VALUES
                         ('".addslashes($row[0])."','".addslashes($row[1])."','".addslashes($row[2])."','".addslashes($row[3])."','".addslashes($row[4])."','".addslashes($row[5])."','".addslashes($row[6])."','".addslashes($row[7])."',
                         '".addslashes($row[8])."','".addslashes($row[9])."','".addslashes($row[10])."','".addslashes($row[11])."','".addslashes($row[12])."')";
        }

        $result = db_query("DROP TABLE ".DB_PREFIX."timeproj");

        $result = db_query("CREATE TABLE ".DB_PREFIX."timeproj (
                              ID ".$db_int8_auto[$db_type].",
                              users ".$db_int4[$db_type].",
                              projekt ".$db_int4[$db_type].",
                              datum ".$db_varchar10[$db_type].",
                              h ".$db_int2[$db_type].",
                              m ".$db_int2[$db_type].",
                              kat ".$db_varchar255[$db_type].",
                              note ".$db_text[$db_type].",
                              ext ".$db_int2[$db_type].",
                              div1 ".$db_varchar40[$db_type].",
                              div2 ".$db_varchar40[$db_type].",
                              module ".$db_varchar255[$db_type].",
                              module_id ".$db_int8[$db_type].",
                              PRIMARY KEY (ID) ");

        $result = db_query("INSERT INTO ".DB_PREFIX."timeproj SELECT ID, users, projekt, datum, h, m, kat, note, ext, div1, div2, module, module_id FROM ".DB_PREFIX."timeproj_new");

        $result = db_query("DROP TABLE ".DB_PREFIX."timeproj_new");

    }

    // end of update timeproj.note for sqlite

    // update form layout
    $result = db_query("update ".DB_PREFIX."db_manager set form_colspan = 1 where db_table = 'dateien' and db_name = 'filename'");
    $result = db_query("update ".DB_PREFIX."db_manager set form_colspan = 1 where db_table = 'notes' and db_name = 'name'");





} // end update to 5.2

// ***************
// update to 5.2.2
// ***************

if ( ($setup == "update") and (ereg("5.2",$version_old) or ereg("5.1",$version_old) or ereg("5.0",$version_old) or ereg("4.2",$version_old) or ereg("4.1",$version_old) or
ereg("4.0",$version_old) or ereg("3.3",$version_old) or ereg("3.2",$version_old) or
ereg("3.1",$version_old) or ereg("3.0",$version_old) or ereg("2.4",$version_old) or
ereg("2.3",$version_old) or $version_old == "2.2" or $version_old == "2.1" or
$version_old == "2.0" or $version_old == "1.3" or $version_old == "1.2") ) {

    // rename file field to other name compatible with all dbms
    if ($db_type <> 'sqlite') {
        
        if ($db_type == 'postgresql') {
            $result = db_query("ALTER TABLE ".DB_PREFIX."rts RENAME COLUMN file TO filename");
        }
        else {
            $result = db_query("ALTER TABLE ".DB_PREFIX."rts CHANGE COLUMN file filename ".$db_varchar255[$db_type]);
        }
    }
    else {
        $result = db_query("CREATE TABLE ".DB_PREFIX."rts_temp (
                              ID ".$db_int8_auto[$db_type].",
                              contact ".$db_int4[$db_type].",
                              submit ".$db_varchar20[$db_type].",
                              recorded ".$db_int6[$db_type].",
                              name ".$db_varchar255[$db_type].",
                              note ".$db_text[$db_type].",
                              due_date ".$db_varchar20[$db_type].",
                              status ".$db_varchar20[$db_type].",
                              assigned ".$db_varchar20[$db_type].",
                              priority ".$db_int1[$db_type].",
                              solution ".$db_text[$db_type].",
                              solved ".$db_int4[$db_type].",
                              solve_time ".$db_varchar20[$db_type].",
                              proj ".$db_int6[$db_type].",
                              acc_read ".$db_text[$db_type].",
                              acc_write ".$db_text[$db_type].",
                              von ".$db_int8[$db_type].",
                              gruppe ".$db_int6[$db_type].",
                              parent ".$db_int8[$db_type].",
                              visibility ".$db_varchar20[$db_type].",
                              file ".$db_varchar255[$db_type].",
                              category ".$db_varchar255[$db_type].",
                              lock_user ".$db_int6[$db_type].",
                              PRIMARY KEY (ID))");

        $result = db_query("INSERT INTO ".DB_PREFIX."rts_temp SELECT ID, contact,submit,recorded,name,note,due_date,status,assigned,priority,solution,solved,solve_time,proj,acc_read,acc_write,von,gruppe,parent,visibility,file,category,lock_user FROM ".DB_PREFIX."rts");

        $result = db_query("DROP TABLE ".DB_PREFIX."rts");

        $result = db_query("CREATE TABLE ".DB_PREFIX."rts (
                              ID ".$db_int8_auto[$db_type].",
                              contact ".$db_int4[$db_type].",
                              submit ".$db_varchar20[$db_type].",
                              recorded ".$db_int6[$db_type].",
                              name ".$db_varchar255[$db_type].",
                              note ".$db_text[$db_type].",
                              due_date ".$db_varchar20[$db_type].",
                              status ".$db_varchar20[$db_type].",
                              assigned ".$db_varchar20[$db_type].",
                              priority ".$db_int1[$db_type].",
                              solution ".$db_text[$db_type].",
                              solved ".$db_int4[$db_type].",
                              solve_time ".$db_varchar20[$db_type].",
                              proj ".$db_int6[$db_type].",
                              acc_read ".$db_text[$db_type].",
                              acc_write ".$db_text[$db_type].",
                              von ".$db_int8[$db_type].",
                              gruppe ".$db_int6[$db_type].",
                              parent ".$db_int8[$db_type].",
                              visibility ".$db_varchar20[$db_type].",
                              filename ".$db_varchar255[$db_type].",
                              category ".$db_varchar255[$db_type].",
                              lock_user ".$db_int6[$db_type].",
                              PRIMARY KEY (ID))");

        $result = db_query("INSERT INTO ".DB_PREFIX."rts SELECT ID, contact,submit,recorded,name,note,due_date,status,assigned,priority,solution,solved,solve_time,proj,acc_read,acc_write,von,gruppe,parent,visibility,file,category,lock_user FROM ".DB_PREFIX."rts_temp");

        $result = db_query("DROP TABLE ".DB_PREFIX."rts_temp");

    }

    $result = db_query("update ".DB_PREFIX."db_manager set db_name = 'filename' where db_table = 'rts' and db_name = 'file'");

    // end update file field on rts table
    
    
    // *********************
    // Special Cost upgrade
    // *********************
    
    // *****************************************
    // Costs tables
    $result = db_query("CREATE TABLE ".DB_PREFIX."costs (
        ID ".$db_int8_auto[$db_type].",
        von ".$db_int8[$db_type].",
        name ".$db_varchar255[$db_type].",
        remark ".$db_text[$db_type].",
        amount ".$db_varchar255[$db_type].",
        contact ".$db_int8[$db_type].",
        projekt ".$db_int8[$db_type].",
        datum ".$db_varchar20[$db_type].",
        sync1 ".$db_varchar20[$db_type].",
        sync2 ".$db_varchar20[$db_type].",
        acc ".$db_text[$db_type].",
        acc_write ".$db_text[$db_type].",
        gruppe ".$db_int8[$db_type].",
        parent ".$db_int8[$db_type].",
        is_deleted ".$db_varchar20[$db_type].",
        PRIMARY KEY  (ID)) ");
    if ($result) { echo __('costs (...)').".<br />\n"; }
    else { echo __('An error ocurred while creating table: ')." costs<br />"; $error = 1; }
    if ($db_type == "oracle") { sequence("costs"); }
    if ($db_type == "interbase") { ib_autoinc("costs"); }
    
    // extend module designer with other modules
    $result = db_query("select max(ID) from ".DB_PREFIX."db_manager") or db_die();
    $row = db_fetch_row($result);
    $ii = $row[0];
    
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (ID, db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn ,form_length, field_type) VALUES (".($ii+=1).",'costs', 'name',   '__(\"Title\")'   , 'text'        , 'Title of this note'          , 1, 1, 1, NULL, NULL, NULL, 1, NULL, '1', 0, NULL, NULL, NULL, NULL)");
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (ID, db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn ,form_length, field_type) VALUES (".($ii+=1).",'costs', 'remark', '__(\"Comment\")' , 'textarea'    , 'bodytext of the note'        , 2, 1, 5, NULL, NULL, NULL, 2, NULL, '1', 0, NULL, NULL, NULL, NULL)");
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (ID, db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn ,form_length, field_type) VALUES (".($ii+=1).",'costs', 'amount', '__(\"Amount\")'  , 'text'        , 'Amount of the cost'          , 3, 1, 1, NULL, NULL, NULL, 0, NULL, '1', 0, NULL, NULL, NULL, NULL)");
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (ID, db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn ,form_length, field_type) VALUES (".($ii+=1).",'costs', 'contact','__(\"Contact\")' , 'contact'     , 'Contact related to this note', 4, 1, 1, NULL, NULL, NULL, 4, NULL, '1', 0, NULL, NULL, NULL, 'integer')");
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (ID, db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn ,form_length, field_type) VALUES (".($ii+=1).",'costs', 'projekt','__(\"Projects\")', 'project'     , 'Project related to this note', 5, 1, 1, NULL, NULL, NULL, 5, NULL, '1', 0, NULL, NULL, NULL, 'integer')");
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (ID, db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn ,form_length, field_type) VALUES (".($ii+=1).",'costs', 'datum',  '__(\"Date\")'    , 'date'        , 'Datum'                       , 6, 1, 1, NULL, NULL, NULL, 6, NULL, '1', 0, NULL, NULL, NULL, NULL)");    
    $result = db_query("ALTER TABLE ".DB_PREFIX."roles ADD costs ".$db_int1[$db_type]);
    
    // ****************************
    // End of cost tables creation
    // ****************************
    

} // end update to 5.2.2


?>
