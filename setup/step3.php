<?php

// step3.php - PHProjekt Version 5.2
// copyright © 2000-2005 Albrecht Guenther  ag@phprojekt.com
// www.phprojekt.com
// Author: Albrecht Guenther, $Author: polidor $
// $Id: step3.php,v 1.192.2.6 2007/08/02 16:43:00 polidor Exp $

// check whether setup.php calls this script - authentication!
if (!defined("setup_included")) die("Please use setup.php!");


// save default language to avoid new setting in the lib
$langua_save = $langua;

// set contstant avoid_auth in order to bypass authentication in lib
if (!defined('avoid_auth')) define("avoid_auth", "1");

// include lib
include_once("./lib/lib.inc.php");

// fetch db definitions
include("./setup/db_var.inc.php");

// restore language
$langua = $langua_save;

// starting with header and conteng
display_header();
// echo "<div class='inner_content'><br /><div class='boxHeader'>".__('Setup PHProjekt')."</div>";
echo "<div class='boxContent'><fieldset class='settings'><legend>".__('Setup PHProjekt')."</legend>";
echo "<div id='login_text' name='login_text' class='settings'>&nbsp;</div>";

include_once('setup/setup_configuration.php');

// ************************
// Test user password check
// ************************
if (isset($_SESSION['change_test']) && $_SESSION['change_test']) {
    $config_array['testcreation']['noconfigure'] = false;
    $config_array['testpass']['noconfigure']     = false;
    $config_array['testpass2']['noconfigure']    = false;
}

// get the old values and store as '_old vlues
if(!isset($old_config_array)) {
    $old_config_array = get_defined_constants();
}



// updates
include_once("./setup/updates.php");


// crypt passwords
// If checked pw_encrypt and it was not encrypted before, then, we will encrypt the stored passwords right now
if (($pw_crypt == 1) && ($old_config_array["PHPR_PW_CRYPT"] <> 1) && ($setup <> 'install'))
{

    echo __('Passwords will now be encrypted ...')." ...<br />";
    $result = db_query("SELECT ID, pw, nachname
                          FROM ".$old_config_array("PHPR_DB_PREFIX")."users") or db_die();
    while ($row = db_fetch_row($result)) {
        $enc_pw = md5('phprojektmd5'.$row[1]);
        $result2 = db_query("UPDATE ".$old_config_array("PHPR_DB_PREFIX")."users
                                SET pw = '$enc_pw'
                              WHERE ID = ".(int)$row[0]) or db_die();
    }
    $pw_crypt = "1";
    echo __('finished password encryption')."!<br /><br />";
}

// crypt filenames
if ($pw_crypt && ($old_config_array["PHPR_PW_CRYPT"] <> 1) && ($setup <> 'install')) {
    echo __('Filenames will now be crypted ...')." ...<br />";
    // loop over all files in the upload directory
    $result = db_query("SELECT ID, filename
                          FROM ".DB_PREFIX."dateien") or db_die();
    while ($row = db_fetch_row($result)) {
        // exclude directories
        if (is_file("$dat_rel/$row[1]")) {
            $rnd = rnd_string(9);
            $result2 = db_query("UPDATE ".DB_PREFIX."dateien
                                    SET tempname = '$rnd'
                                  WHERE ID = ".(int)$row[0]) or db_die();
            // because renaming is difficult on various OS, use copy and unlink
            copy("$dat_rel/$row[1]", "$dat_rel/$rnd");
            unlink("$dat_rel/$row[1]");
        }
    }
    echo __('finished filenames encryption')."!<br /><br />";
}

// ******************************
// Begin table setup ************
// Filemanagement    ************

$result = db_query("
        CREATE TABLE ".DB_PREFIX."dateien (
        ID ".$db_int8_auto[$db_type].",
        von ".$db_int8[$db_type].",
        filename ".$db_varchar255[$db_type].",
        userfile ".$db_varchar255[$db_type].",
        remark ".$db_text[$db_type].",
        kat ".$db_varchar40[$db_type].",
        acc ".$db_text[$db_type].",
        datum ".$db_varchar20[$db_type].",
        filesize ".$db_int11[$db_type].",
        gruppe ".$db_int8[$db_type].",
        tempname ".$db_varchar255[$db_type].",
        typ ".$db_varchar40[$db_type].",
        div1 ".$db_varchar40[$db_type].",
        div2 ".$db_varchar40[$db_type].",
        pw ".$db_varchar255[$db_type].",
        acc_write ".$db_text[$db_type].",
        version ".$db_varchar4[$db_type].",
        lock_user ".$db_int6[$db_type].",
        contact ".$db_int6[$db_type].",
        parent ".$db_int8[$db_type].",
        versioning ".$db_int1[$db_type].",
        PRIMARY KEY (ID)
      ) ");

if ($result) {
    echo __('Table dateien (for file-handling) created').".<br />\n";
}
elseif(get_sql_errno($result) != $db_error_code_table_exists[$db_type]) {
    echo __('An error ocurred while creating table: ')." dateien<br />\n"; $error = 1;
}
if ($db_type == "oracle") { sequence("dateien"); }
if ($db_type == "interbase") {ib_autoinc("dateien"); }

// datei_history

$result = db_query("
        CREATE TABLE ".DB_PREFIX."datei_history (
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


//**************************************

// Todo lists
$result = db_query("
      CREATE TABLE ".DB_PREFIX."todo (
      ID ".$db_int8_auto[$db_type].",
      von ".$db_int8[$db_type].",
      remark ".$db_varchar255[$db_type].",
      ext ".$db_int8[$db_type].",
      div1 ".$db_text[$db_type].",
      div2 ".$db_varchar40[$db_type].",
      note ".$db_text[$db_type].",
      deadline ".$db_varchar20[$db_type].",
      datum ".$db_varchar20[$db_type].",
      status ".$db_int1[$db_type].",
      priority ".$db_int1[$db_type].",
      progress ".$db_int4[$db_type].",
      project ".$db_int6[$db_type].",
      contact ".$db_int8[$db_type].",
      sync1 ".$db_varchar20[$db_type].",
      sync2 ".$db_varchar20[$db_type].",
      comment1 ".$db_text[$db_type].",
      comment2 ".$db_text[$db_type].",
      anfang ".$db_varchar20[$db_type].",
      gruppe ".$db_int4[$db_type].",
      acc ".$db_text[$db_type].",
      acc_write ".$db_text[$db_type].",
      parent ".$db_int11[$db_type].",
      PRIMARY KEY (ID)
    ) ");
if ($result) { echo __('Table todo (for todo-lists) created').".<br />\n"; }
elseif(get_sql_errno($result) != $db_error_code_table_exists[$db_type]) {
    echo __('An error ocurred while creating table: ')." todo<br />\n"; $error = 1;
}
if ($db_type == "oracle") { sequence("todo"); }
if ($db_type == "interbase") {ib_autoinc("todo"); }


//**************************************

//  Forum
$result = db_query("
      CREATE TABLE ".DB_PREFIX."forum (
      ID ".$db_int8_auto[$db_type].",
      antwort ".$db_int8[$db_type].",
      von ".$db_int8[$db_type].",
      titel ".$db_varchar80[$db_type].",
      remark ".$db_text[$db_type].",
      kat ".$db_varchar20[$db_type].",
      datum ".$db_varchar20[$db_type].",
      gruppe ".$db_int4[$db_type].",
      lastchange ".$db_varchar20[$db_type].",
      notify ".$db_varchar2[$db_type].",
      acc ".$db_text[$db_type].",
      acc_write ".$db_text[$db_type].",
      parent ".$db_int8[$db_type].",
      PRIMARY KEY (ID)
    ) ");
if ($result) { echo __('Table forum (for discssions etc.) created').".<br />\n"; }
elseif(get_sql_errno($result) != $db_error_code_table_exists[$db_type]) {
    echo __('An error ocurred while creating table: ')." forum<br />\n"; $error = 1;
}
// create own index for antworten
$result = db_query("CREATE INDEX forum_antwort ON ".DB_PREFIX."forum (antwort)");

if ($db_type == "oracle") { sequence("forum"); }
if ($db_type == "interbase") { ib_autoinc("forum"); }

//******************************************

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
if ($result) { echo __('Table sync_rel (for synchronization) created').".<br />\n"; }
elseif(get_sql_errno($result) != $db_error_code_table_exists[$db_type]) {
    echo __('An error ocurred while creating table: ')." sync_rel<br />\n"; $error = 1;
}

if ($db_type == "oracle") { sequence("sync_rel"); }
if ($db_type == "interbase") { ib_autoinc("sync_rel"); }


//******************************************

// chat
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
if ($result) { echo __('Table chat (for communication) created').".<br />\n"; }
elseif(get_sql_errno($result) != $db_error_code_table_exists[$db_type]) {
    echo __('An error ocurred while creating table: ')." chat<br />\n"; $error = 1;
}

if ($db_type == "oracle") { sequence("chat"); }
if ($db_type == "interbase") { ib_autoinc("chat"); }

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
if ($result) { echo __('Table chat_alive (for communication) created').".<br />\n"; }
elseif(get_sql_errno($result) != $db_error_code_table_exists[$db_type]) {
    echo __('An error ocurred while creating table: ')." chat_alive<br />\n"; $error = 1;
}

if ($db_type == "oracle") { sequence("chat_alive"); }
if ($db_type == "interbase") { ib_autoinc("chat_alive"); }

$result = db_query("CREATE INDEX chat_alive_gruppe ON ".DB_PREFIX."chat_alive (gruppe)");

//******************************************

// polls/votum/Umfragen
$result = db_query("
      CREATE TABLE ".DB_PREFIX."votum (
      ID ".$db_int8_auto[$db_type].",
      datum ".$db_varchar20[$db_type].",
      von ".$db_int8[$db_type].",
      thema ".$db_varchar255[$db_type].",
      modus ".$db_char1[$db_type].",
      an ".$db_text[$db_type].",
      fertig ".$db_text[$db_type].",
      text1 ".$db_varchar60[$db_type].",
      text2 ".$db_varchar60[$db_type].",
      text3 ".$db_varchar60[$db_type].",
      zahl1 ".$db_int4[$db_type].",
      zahl2 ".$db_int4[$db_type].",
      zahl3 ".$db_int4[$db_type].",
      kein  ".$db_int4[$db_type].",
      PRIMARY KEY (ID)
    ) ");
if ($result) { echo __('Table votum (for polls) created').".<br />\n"; }
elseif(get_sql_errno($result) != $db_error_code_table_exists[$db_type]) {
    echo __('An error ocurred while creating table: ')." votum<br />\n"; $error = 1;
}
if ($db_type == "oracle") { sequence("votum"); }
if ($db_type == "interbase") { ib_autoinc("votum"); }

//*******************************************

// Lesezeichen - bookmarks
$result = db_query("
      CREATE TABLE ".DB_PREFIX."lesezeichen (
      ID ".$db_int8_auto[$db_type].",
      datum ".$db_varchar20[$db_type].",
      von ".$db_int8[$db_type].",
      url ".$db_varchar255[$db_type].",
      bezeichnung ".$db_varchar255[$db_type].",
      bemerkung ".$db_varchar255[$db_type].",
      gruppe ".$db_int6[$db_type].",
      PRIMARY KEY (ID)
    ) ");
if ($result) { echo __('Table lesezeichen (for bookmarks) created').".<br />\n"; }
elseif(get_sql_errno($result) != $db_error_code_table_exists[$db_type]) {
    echo __('An error ocurred while creating table: ')." lesezeichen<br />\n"; $error = 1;
}
if ($db_type == "oracle") { sequence("lesezeichen"); }
if ($db_type == "interbase") { ib_autoinc("lesezeichen"); }

//********************************************



//*******************************************
// Costs
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
        is_deleted ".$db_int1[$db_type].",
        PRIMARY KEY  (ID)) ");

if ($result) { echo __('Table costs created').".<br />\n"; }
elseif(get_sql_errno($result) != $db_error_code_table_exists[$db_type]) {
    echo __('An error ocurred while creating table: ')." costs<br />\n"; $error = 1;
}

// Projekte - projects
$result = db_query("
      CREATE TABLE ".DB_PREFIX."projekte (
      ID ".$db_int8_auto[$db_type].",
      name ".$db_varchar80[$db_type].",
      ende ".$db_varchar10[$db_type].",
      personen ".$db_text[$db_type].",
      wichtung ".$db_varchar20[$db_type].",
      status ".$db_int3[$db_type].",
      statuseintrag ".$db_varchar10[$db_type].",
      anfang ".$db_varchar10[$db_type].",
      gruppe ".$db_int4[$db_type].",
      chef ".$db_varchar20[$db_type].",
      typ ".$db_varchar40[$db_type].",
      parent ".$db_int6[$db_type].",
      ziel ".$db_text[$db_type].",
      note ".$db_text[$db_type].",
      kategorie ".$db_varchar40[$db_type].",
      contact ".$db_int8[$db_type].",
      stundensatz ".$db_int8[$db_type].",
      budget ".$db_int11[$db_type].",
      div1 ".$db_varchar40[$db_type].",
      div2 ".$db_varchar40[$db_type].",
      depend_mode ".$db_int2[$db_type].",
      depend_proj ".$db_int6[$db_type].",
      next_mode ".$db_int2[$db_type].",
      next_proj ".$db_int6[$db_type].",
      probability ".$db_int3[$db_type].",
      ende_real ".$db_varchar10[$db_type].",
      acc ".$db_text[$db_type].",
      acc_write ".$db_text[$db_type].",
      von ".$db_int8[$db_type].",
      PRIMARY KEY (ID)
    ) ");
if ($result) { echo __('Table projekte (for project management) created').".<br />\n"; }
elseif(get_sql_errno($result) != $db_error_code_table_exists[$db_type]) {
    echo __('An error ocurred while creating table: ')." projekte<br />\n"; $error = 1;
}

$result = db_query("CREATE INDEX parent ON ".DB_PREFIX."projekte (parent)");

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
        period ".$db_varchar3[$db_type].",
        PRIMARY KEY (id)
    )");
if ($result) { echo __('Table projekt_statistik_einstellungen (for project management) created').".<br />\n"; }
elseif(get_sql_errno($result) != $db_error_code_table_exists[$db_type]) {
    echo __('An error ocurred while creating table: ')." projekt_statistik_einstellungen<br />\n"; $error = 1;
}

$result = db_query("
    CREATE TABLE ".DB_PREFIX."projekt_statistik_projekte (
        stat_einstellung_ID ".$db_int8[$db_type].",
        projekt_ID ".$db_int8[$db_type]."
    )");
if ($result) { echo __('Table projekt_statistik_projekte (for project management) created').".<br />\n"; }
elseif(get_sql_errno($result) != $db_error_code_table_exists[$db_type]) {
    echo __('An error ocurred while creating table: ')." projekt_statistik_projekte'<br />\n"; $error = 1;
}

$result = db_query("
    CREATE TABLE ".DB_PREFIX."projekt_statistik_user (
        stat_einstellung_ID ".$db_int8[$db_type].",
        user_ID ".$db_int8[$db_type]."
    )");
if ($result) { echo __('Table projekt_statistik_user (for project management) created').".<br />\n"; }
elseif(get_sql_errno($result) != $db_error_code_table_exists[$db_type]) {
    echo __('An error ocurred while creating table: ')." projekt_statistik_user<br />\n"; $error = 1;
}

if ($db_type == "oracle") {
    sequence("projekte");
    sequence("projekt_statistik_einstellungen");
}

if ($db_type == "interbase") {
    ib_autoinc("projekte");
    ib_autoinc("projekt_statistik_einstellungen");
}

// book work time on projects
$result = db_query("
      CREATE TABLE ".DB_PREFIX."timeproj (
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
      PRIMARY KEY (ID)
    ) ");
if ($result) { echo __('Table timeproj (assigning work time to projects) created').".<br />\n"; }
elseif(get_sql_errno($result) != $db_error_code_table_exists[$db_type]) {
    echo __('An error ocurred while creating table: ')." timeproj<br />\n"; $error = 1;
}
if ($db_type == "oracle") { sequence("timeproj"); }
if ($db_type == "interbase") { ib_autoinc("timeproj"); }

//********************************************

// Kontakte - contacts
$result = db_query("
      CREATE TABLE ".DB_PREFIX."contacts (
      ID ".$db_int8_auto[$db_type].",
      vorname ".$db_varchar20[$db_type].",
      nachname ".$db_varchar40[$db_type].",
      gruppe ".$db_int4[$db_type].",
      firma ".$db_varchar60[$db_type].",
      email ".$db_varchar80[$db_type].",
      tel1 ".$db_varchar60[$db_type].",
      tel2 ".$db_varchar60[$db_type].",
      fax ".$db_varchar60[$db_type].",
      strasse ".$db_varchar60[$db_type].",
      stadt ".$db_varchar60[$db_type].",
      plz ".$db_varchar10[$db_type].",
      land ".$db_varchar40[$db_type].",
      kategorie ".$db_varchar40[$db_type].",
      bemerkung ".$db_text[$db_type].",
      von ".$db_int8[$db_type].",
      acc ".$db_varchar4[$db_type].",
      email2 ".$db_varchar80[$db_type].",
      mobil ".$db_varchar60[$db_type].",
      url ".$db_varchar80[$db_type].",
      div1 ".$db_varchar60[$db_type].",
      div2 ".$db_varchar60[$db_type].",
      anrede ".$db_varchar20[$db_type].",
      state ".$db_varchar40[$db_type].",
      import ".$db_char1[$db_type].",
      parent ".$db_int8[$db_type].",
      sync1 ".$db_varchar20[$db_type].",
      sync2 ".$db_varchar20[$db_type].",
      acc_read ".$db_text[$db_type].",
      acc_write ".$db_text[$db_type].",
      PRIMARY KEY (ID)
    ) ");
if ($result) { echo __('Table contacts (for external contacts) created').".<br />\n"; }
elseif(get_sql_errno($result) != $db_error_code_table_exists[$db_type]){
    echo __('An error ocurred while creating table: ')." contacts<br />\n"; $error = 1;
}
if ($db_type == "oracle") { sequence("contacts"); }
if ($db_type == "interbase") { ib_autoinc("contacts"); }

//********************************************

// Notizen - notes
$result = db_query("
      CREATE TABLE ".DB_PREFIX."notes (
      ID ".$db_int8_auto[$db_type].",
      von ".$db_int8[$db_type].",
      name ".$db_varchar255[$db_type].",
      remark ".$db_text[$db_type].",
      contact ".$db_int8[$db_type].",
      ext ".$db_int8[$db_type].",
      div1 ".$db_varchar40[$db_type].",
      div2 ".$db_varchar40[$db_type].",
      projekt ".$db_int6[$db_type].",
      sync1 ".$db_varchar20[$db_type].",
      sync2 ".$db_varchar20[$db_type].",
      acc ".$db_text[$db_type].",
      acc_write ".$db_text[$db_type].",
      gruppe ".$db_int4[$db_type].",
      parent ".$db_int6[$db_type].",
      kategorie ".$db_varchar100[$db_type].",
      PRIMARY KEY (ID)
    ) ");
if ($result) { echo __('Table notes (for notes) created').".<br />\n"; }
elseif(get_sql_errno($result) != $db_error_code_table_exists[$db_type]) {
    echo __('An error ocurred while creating table: ')." notes<br />\n"; $error = 1;
}
if ($db_type == "oracle") { sequence("notes"); }
if ($db_type == "interbase") { ib_autoinc("notes"); }

//********************************************

// Zeitkarte - timecard
$result = db_query("
      CREATE TABLE ".DB_PREFIX."timecard (
      ID ".$db_int8_auto[$db_type].",
      users ".$db_int8[$db_type].",
      datum ".$db_varchar10[$db_type].",
      projekt ".$db_int8[$db_type].",
      anfang ".$db_int4[$db_type].",
      ende ".$db_int4[$db_type].",
      nettoh ".$db_int2[$db_type].",
      nettom ".$db_int2[$db_type].",
      ip_address ".$db_varchar255[$db_type].",
      PRIMARY KEY (ID)
    ) ");
if ($result) { echo __('Table timecard (for time sheet system) created').".<br />\n"; }
elseif(get_sql_errno($result) != $db_error_code_table_exists[$db_type]) {
    echo __('An error ocurred while creating table: ')." timecard<br />\n"; $error = 1;
}
if ($db_type == "oracle") { sequence("timecard"); }
if ($db_type == "interbase") { ib_autoinc("timecard"); }

//********************************************

// Gruppen - groups
$result = db_query("
      CREATE TABLE ".DB_PREFIX."gruppen (
      ID ".$db_int8_auto[$db_type].",
      name ".$db_varchar255[$db_type].",
      kurz ".$db_varchar10[$db_type].",
      kategorie ".$db_varchar255[$db_type].",
      bemerkung ".$db_varchar255[$db_type].",
      chef ".$db_int8[$db_type].",
      div1 ".$db_varchar255[$db_type].",
      div2 ".$db_varchar255[$db_type].",
      ldap_name ".$db_text[$db_type].",
      PRIMARY KEY (ID)
    ) ");
if (!$result) {
    if(get_sql_errno($result) != $db_error_code_table_exists[$db_type]) {
        echo __('An error ocurred while creating table: ')." gruppen<br />\n"; $error = 1;
    }
}
if ($db_type == "oracle") { sequence("gruppen"); }
if ($db_type == "interbase") { ib_autoinc("gruppen"); }

// additional groups
$result = db_query("
      CREATE TABLE ".DB_PREFIX."grup_user (
      ID ".$db_int8_auto[$db_type].",
      grup_ID ".$db_int4[$db_type].",
      user_ID ".$db_int8[$db_type].",
      PRIMARY KEY (ID)
    ) ");
if ($result) { echo __('Table gruppen (for group management) created').".<br />\n"; }
elseif(get_sql_errno($result) != $db_error_code_table_exists[$db_type]) {
    echo __('An error ocurred while creating table: ')." grup_user<br />\n"; $error = 1;
}
// create own index for user_ID and grup_ID
$result = db_query("CREATE INDEX grup_user_user_ID ON ".DB_PREFIX."grup_user (user_ID)");
$result = db_query("CREATE INDEX grup_user_grup_ID ON ".DB_PREFIX."grup_user (grup_ID)");

if ($db_type == "oracle") { sequence("grup_user"); }
if ($db_type == "interbase") { ib_autoinc("grup_user"); }

//********************************************

// helpdesk
$result = db_query("
      CREATE TABLE ".DB_PREFIX."rts (
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
      PRIMARY KEY (ID)
    ) ");
if (!$result) {
    if(get_sql_errno($result) != $db_error_code_table_exists[$db_type]) {
        echo __('An error ocurred while creating table: ')." rts<br />\n"; $error = 1;
    }
}
if ($db_type == "oracle") { sequence("rts"); }
if ($db_type == "interbase") { ib_autoinc("rts"); }

//helpdesk_cat
$result = db_query("
      CREATE TABLE ".DB_PREFIX."rts_cat (
      ID ".$db_int8_auto[$db_type].",
      name ".$db_varchar60[$db_type].",
      users ".$db_varchar10[$db_type].",
      gruppe ".$db_varchar10[$db_type].",
      PRIMARY KEY (ID)
    ) ");
if ($result) { echo __('Table rts and rts_cat (for the help desk) created').".<br />\n"; }
elseif(get_sql_errno($result) != $db_error_code_table_exists[$db_type]) {
    echo __('An error ocurred while creating table: ')." rts_cat<br />\n"; $error = 1;
}
if ($db_type == "oracle") { sequence("rts_cat"); }
if ($db_type == "interbase") { ib_autoinc("rts_cat"); }


// db_remarks
$result = db_query("
      CREATE TABLE ".DB_PREFIX."db_remarks (
      ID ".$db_int8_auto[$db_type].",
      module ".$db_varchar40[$db_type].",
      module_ID ".$db_int8[$db_type].",
      date ".$db_varchar20[$db_type].",
      remark ".$db_text[$db_type].",
      author ".$db_varchar80[$db_type].",
      parent ".$db_int8[$db_type].",
      upload ".$db_text2[$db_type].",
      PRIMARY KEY (ID)
    ) ");
if ($result) { echo __('Table db_remarks (for the help desk) created').".<br />\n"; }
elseif(get_sql_errno($result) != $db_error_code_table_exists[$db_type]) {
    echo __('An error ocurred while creating table: ')." db_remarks<br />\n"; $error = 1;
}
if ($db_type == "oracle") { sequence("db_remarks"); }
if ($db_type == "interbase") { ib_autoinc("db_remarks"); }

// db_accounts
$result = db_query("
      CREATE TABLE ".DB_PREFIX."db_accounts (
      ID ".$db_int8_auto[$db_type].",
      name ".$db_varchar60[$db_type].",
      users ".$db_varchar10[$db_type].",
      gruppe ".$db_varchar10[$db_type].",
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
$result = db_query("
      CREATE TABLE ".DB_PREFIX."global_mail_account (
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

//********************************************

// mail reader
// mail account
$result = db_query("
      CREATE TABLE ".DB_PREFIX."mail_account (
      ID ".$db_int8_auto[$db_type].",
      von ".$db_int8[$db_type].",
      accountname ".$db_varchar40[$db_type].",
      hostname ".$db_varchar80[$db_type].",
      type ".$db_varchar60[$db_type].",
      username ".$db_varchar60[$db_type].",
      password ".$db_varchar60[$db_type].",
      mail_auth ".$db_int2[$db_type].",
      pop_hostname ".$db_varchar40[$db_type].",
      pop_account ".$db_varchar40[$db_type].",
      pop_password ".$db_varchar40[$db_type].",
      smtp_hostname ".$db_varchar40[$db_type].",
      smtp_account ".$db_varchar40[$db_type].",
      smtp_password ".$db_varchar40[$db_type].",
      collect ".$db_int2[$db_type].",
      deletion ".$db_int2[$db_type].",
      PRIMARY KEY (ID)
    ) ");
if (!$result) {
    if(get_sql_errno($result) != $db_error_code_table_exists[$db_type]) {
        echo __('An error ocurred while creating table: ')." mail_account<br />\n"; $error = 1;
    }
}
if ($db_type == "oracle") { sequence("mail_account"); }
if ($db_type == "interbase") { ib_autoinc("mail_account"); }

// mail attachments
$result = db_query("
      CREATE TABLE ".DB_PREFIX."mail_attach (
      ID ".$db_int8_auto[$db_type].",
      parent ".$db_int8[$db_type].",
      filename ".$db_varchar255[$db_type].",
      tempname ".$db_varchar255[$db_type].",
      filesize ".$db_int11[$db_type].",
      PRIMARY KEY (ID)
    ) ");
if (!$result) {
    if(get_sql_errno($result) != $db_error_code_table_exists[$db_type]) {
        echo __('An error ocurred while creating table: ')." mail_attach<br />\n"; $error = 1;
    }
}
if ($db_type == "oracle") { sequence("mail_attach"); }
if ($db_type == "interbase") { ib_autoinc("mail_attach"); }
// create own index for field parent (reference to field ID of table mail_client
// -> the mail ID
$result = db_query("CREATE INDEX mail_attach_parent ON ".DB_PREFIX."mail_attach (parent)");

// mail sender/signature
$result = db_query("
      CREATE TABLE ".DB_PREFIX."mail_sender (
      ID ".$db_int8_auto[$db_type].",
      von ".$db_int8[$db_type].",
      title ".$db_varchar80[$db_type].",
      sender ".$db_varchar255[$db_type].",
      signature ".$db_text[$db_type].",
      PRIMARY KEY (ID)
    ) ");
if (!$result) {
    if(get_sql_errno($result) != $db_error_code_table_exists[$db_type]) {
        echo __('An error ocurred while creating table: ')." mail_sender<br />\n"; $error = 1;
    }
}
if ($db_type == "oracle") { sequence("mail_sender"); }
if ($db_type == "interbase") { ib_autoinc("mail_sender"); }

// mail client
$result = db_query("
      CREATE TABLE ".DB_PREFIX."mail_client (
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
      account_ID ".$db_int8[$db_type].",
      PRIMARY KEY (ID)
    ) ");
if (!$result) {
    if(get_sql_errno($result) != $db_error_code_table_exists[$db_type]) {
        echo __('An error ocurred while creating table: ')." mail_client<br />\n"; $error = 1;
    }
}
if ($db_type == "oracle") { sequence("mail_client"); }
if ($db_type == "interbase") { ib_autoinc("mail_client"); }
// create own index for field 'von' (fererence to field ID, table users)
$result = db_query("CREATE INDEX mail_client_von ON ".DB_PREFIX."mail_client (von)");

// mail rules
$result = db_query("
      CREATE TABLE ".DB_PREFIX."mail_rules (
      ID ".$db_int8_auto[$db_type].",
      von ".$db_int8[$db_type].",
      title ".$db_varchar80[$db_type].",
      phrase ".$db_varchar60[$db_type].",
      type ".$db_varchar60[$db_type].",
      is_not ".$db_varchar3[$db_type].",
      parent ".$db_int8[$db_type].",
      action ".$db_varchar10[$db_type].",
      projekt ".$db_int6[$db_type].",
      contact ".$db_int6[$db_type].",
      PRIMARY KEY (ID)
    ) ");
if ($result) { echo __('Table mail_account, mail_attach, mail_client und mail_rules (for the mail reader) created').".<br />\n"; }
if (!$result) {
    if(get_sql_errno($result) != $db_error_code_table_exists[$db_type]) {
        echo __('An error ocurred while creating table: ')." mail_rules<br />\n"; $error = 1;
    }
}
if ($db_type == "oracle") { sequence("mail_rules"); }
if ($db_type == "interbase") { ib_autoinc("mail_rules"); }

//********************************************

// Logging
$result = db_query("
      CREATE TABLE ".DB_PREFIX."logs (
      ID ".$db_int8_auto[$db_type].",
      von ".$db_int8[$db_type].",
      login ".$db_varchar20[$db_type].",
      logout ".$db_varchar20[$db_type].",
      PRIMARY KEY (ID)
    ) ");
if ($result) { echo __('Table logs (for user login/-out tracking) created').".<br />\n"; }
elseif(get_sql_errno($result) != $db_error_code_table_exists[$db_type]) {
    echo __('An error ocurred while creating table: ')." logs<br />\n"; $error = 1;
}
if ($db_type == "oracle") { sequence("logs"); }
if ($db_type == "interbase") { ib_autoinc("logs"); }

//********************************************

// history
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
if ($result) { echo __('Table logs (for user login/-out tracking) created').".<br />\n"; }
elseif(get_sql_errno($result) != $db_error_code_table_exists[$db_type]) {
    echo __('An error ocurred while creating table: ')." history<br />\n"; $error = 1;
}
if ($db_type == "oracle") { sequence("history"); }
if ($db_type == "interbase") { ib_autoinc("history"); }

//*******************************************

// Contact profiles
$result = db_query("
      CREATE TABLE ".DB_PREFIX."contacts_profiles (
      ID ".$db_int8_auto[$db_type].",
      von ".$db_int8[$db_type].",
      name ".$db_varchar128[$db_type].",
      remark ".$db_text[$db_type].",
      kategorie ".$db_varchar20[$db_type].",
      acc ".$db_varchar4[$db_type].",
      PRIMARY KEY (ID)
    ) ");
if (!$result) {
    if(get_sql_errno($result) != $db_error_code_table_exists[$db_type]) {
        echo __('An error ocurred while creating table: ')." contacts_profiles'br>\n"; $error = 1;
    }
}
if ($db_type == "oracle") { sequence("contacts_profiles"); }
if ($db_type == "interbase") { ib_autoinc("contacts_profiles"); }

$result = db_query("
      CREATE TABLE ".DB_PREFIX."contacts_prof_rel (
      ID ".$db_int8_auto[$db_type].",
      contact_ID ".$db_int8[$db_type].",
      contacts_profiles_ID ".$db_int8[$db_type].",
      PRIMARY KEY (ID)
    ) ");
if ($result) { echo __('Tables contacts_profiles und contacts_prof_rel created').".<br />\n"; }
elseif(get_sql_errno($result) != $db_error_code_table_exists[$db_type]) {
    echo __('An error ocurred while creating table: ')." contacts_prof_rel<br />\n"; $error = 1;
}
if ($db_type == "oracle") { sequence("contacts_prof_rel"); }
if ($db_type == "interbase") { ib_autoinc("contacts_prof_rel"); }

$result = db_query("
      CREATE TABLE ".DB_PREFIX."contacts_import_patterns (
        ID ".$db_int8_auto[$db_type].",
        name ".$db_varchar40[$db_type].",
        von ".$db_int6[$db_type].",
        pattern ".$db_text[$db_type].",
      PRIMARY KEY (ID)
    ) ");
if ($db_type == "oracle") { sequence("contacts_import_patterns"); }
if ($db_type == "interbase") { ib_autoinc("contacts_import_patterns"); }

$result = db_query("
          CREATE TABLE ".DB_PREFIX."project_users_rel (
          ID ".$db_int8_auto[$db_type].",
          project_ID ".$db_int8[$db_type].",
          user_ID ".$db_int8[$db_type].",
          role ".$db_varchar255[$db_type].",
          PRIMARY KEY (ID)
        ) ");
if (!$result) {
    if(get_sql_errno($result) != $db_error_code_table_exists[$db_type]) {
        echo __('An error ocurred while creating table: ')." project_users_rel<br />\n"; $error = 1;
    }
}
if ($db_type == "oracle") { sequence("project_users_rel"); }
if ($db_type == "interbase") { ib_autoinc("project_users_rel"); }


$result = db_query("
          CREATE TABLE ".DB_PREFIX."project_contacts_rel (
          ID ".$db_int8_auto[$db_type].",
          project_ID ".$db_int8[$db_type].",
          contact_ID ".$db_int8[$db_type].",
          role ".$db_varchar255[$db_type].",
          PRIMARY KEY (ID)
        ) ");
if (!$result) {
    if(get_sql_errno($result) != $db_error_code_table_exists[$db_type]) {
        echo __('An error ocurred while creating table: ')." project_contacts_rel<br />\n"; $error = 1;
    }
}
if ($db_type == "oracle") { sequence("project_contacts_rel"); }
if ($db_type == "interbase") { ib_autoinc("project_contacts_rel"); }


//********************************************
// end of the step 'creating tables' according to the settings in the config.inc.php
// the next four tables users, termine, roles & setings will be created in all cases
// *******************************************


if ($setup == "install") {

    // profiles
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
    if ($result) { echo __('Table profiles (for user-profiles) created').".<br />\n"; }
    else { echo __('An error ocurred while creating table: ')." profile <br />\n"; $error = 1; }
    if ($db_type == "oracle") { sequence("profile"); }
    if ($db_type == "interbase") { ib_autoinc("profile"); }


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
    if ($result) { echo __('logintoken (...)').".<br />\n"; }
    else { echo __('An error ocurred while creating table: ')." logintoken<br />"; $error = 1; }
    if ($db_type == "oracle") { sequence("logintoken"); }
    if ($db_type == "interbase") { ib_autoinc("logintoken"); }
    $result = db_query("
    CREATE TABLE ".DB_PREFIX."users (
    ID ".$db_int8_auto[$db_type].",
    vorname ".$db_varchar40[$db_type].",
    nachname ".$db_varchar40[$db_type].",
    kurz ".$db_varchar40[$db_type].",
    pw ".$db_varchar40[$db_type].",
    firma ".$db_varchar40[$db_type].",
    gruppe ".$db_int4[$db_type].",
    email ".$db_varchar60[$db_type].",
    acc ".$db_varchar4[$db_type].",
    tel1 ".$db_varchar40[$db_type].",
    tel2 ".$db_varchar40[$db_type].",
    fax ".$db_varchar40[$db_type].",
    strasse ".$db_varchar40[$db_type].",
    stadt ".$db_varchar40[$db_type].",
    plz ".$db_varchar10[$db_type].",
    land ".$db_varchar40[$db_type].",
    sprache ".$db_varchar2[$db_type].",
    mobil ".$db_varchar40[$db_type].",
    loginname ".$db_varchar40[$db_type].",
    ldap_name ".$db_varchar40[$db_type].",
    anrede ".$db_varchar10[$db_type].",
    sms ".$db_varchar60[$db_type].",
    role ".$db_int4[$db_type].",
    settings ".$db_text[$db_type].",
    hrate ".$db_varchar20[$db_type].",
    remark ".$db_text[$db_type].",
    usertype ".$db_int1[$db_type].",
    status ".$db_int1[$db_type].",
    PRIMARY KEY (ID)
  ) ");
    if ($result) { echo __('Table users (for authentification and address management) created').".<br />\n"; }
    else { echo __('An error ocurred while creating table: ')." users<br />"; $error = 1; }
    // create own index for kurz and gruppe
    $result = db_query("CREATE INDEX users_kurz ON ".DB_PREFIX."users (kurz)");
    $result = db_query("CREATE INDEX users_gruppe ON ".DB_PREFIX."users (gruppe)");
    if ($db_type == "oracle") { sequence("users"); }
    if ($db_type == "interbase") { ib_autoinc("users"); }

    // set up user proxy table
    $result = db_query("CREATE TABLE ".DB_PREFIX."users_proxy (
        ID ".$db_int8_auto[$db_type].",
        user_ID ".$db_int8[$db_type].",
        proxy_ID ".$db_int8[$db_type].",
        PRIMARY KEY (ID)
  )");
    if ($result) { echo __('Table users_proxy (for user rights proxy) created').".<br />\n"; }
    else { echo __('An error ocurred while creating table: ')." 'users_proxy'<br />"; $error = 1; }
    // create own index for proxy_ID and user_ID
    $result = db_query("CREATE INDEX users_proxy_pr_ID ON ".DB_PREFIX."users_proxy (proxy_ID)");
    $result = db_query("CREATE INDEX users_proxy_usr_ID ON ".DB_PREFIX."users_proxy (user_ID)");
    if ($db_type == "oracle") { sequence("users_proxy"); }
    if ($db_type == "interbase") { ib_autoinc("users_proxy"); }

    // set up users reader table
    $result = db_query("CREATE TABLE ".DB_PREFIX."users_reader (
        ID ".$db_int8_auto[$db_type].",
        user_ID ".$db_int8[$db_type].",
        reader_ID ".$db_int8[$db_type].",
        PRIMARY KEY (ID)
  )");
    if ($result) { echo __('Table users_reader (for user reader rights) created').".<br />\n"; }
    else { echo __('An error ocurred while creating table: ')." users_reader<br />"; $error = 1; }
    // create own index for reader_ID and user_ID
    $result = db_query("CREATE INDEX users_reader_rd_ID ON ".DB_PREFIX."users_reader (reader_ID)");
    $result = db_query("CREATE INDEX users_reader_us_ID ON ".DB_PREFIX."users_reader (user_ID)");
    if ($db_type == "oracle") { sequence("users_reader"); }
    if ($db_type == "interbase") { ib_autoinc("users_reader"); }

    // set up users viewer table
    $result = db_query("CREATE TABLE ".DB_PREFIX."users_viewer (
        ID ".$db_int8_auto[$db_type].",
        user_ID ".$db_int8[$db_type].",
        viewer_ID ".$db_int8[$db_type].",
        PRIMARY KEY (ID)
  )");
    if ($result) { echo __('Table users_viewer (for user viewer rights) created').".<br />\n"; }
    else { echo __('An error ocurred while creating table: ')." users_viewer<br />"; $error = 1; }
    // create own index for viewer_ID and user_ID
    $result = db_query("CREATE INDEX users_viewer_vw_ID ON ".DB_PREFIX."users_viewer (viewer_ID)");
    $result = db_query("CREATE INDEX users_viewer_us_ID ON ".DB_PREFIX."users_viewer (user_ID)");
    if ($db_type == "oracle") { sequence("users_viewer"); }
    if ($db_type == "interbase") { ib_autoinc("users_viewer"); }


    //********************************************
    $q = "CREATE TABLE ".DB_PREFIX."termine (
        ID ".$db_int11_auto[$db_type].",
        parent ".$db_int11[$db_type].",
        serie_id ".$db_int11[$db_type].",
        serie_typ ".$db_text[$db_type].",
        serie_bis ".$db_varchar10[$db_type].",
        von ".$db_int8[$db_type].",
        an ".$db_int8[$db_type].",
        event ".$db_varchar128[$db_type].",
        remark ".$db_text[$db_type].",
        projekt ".$db_int8[$db_type].",
        datum ".$db_varchar10[$db_type].",
        anfang ".$db_varchar4[$db_type].",
        ende ".$db_varchar4[$db_type].",
        ort ".$db_varchar40[$db_type].",
        contact ".$db_int8[$db_type].",
        remind ".$db_int4[$db_type].",
        visi ".$db_int1[$db_type].",
        partstat ".$db_int1[$db_type].",
        priority ".$db_int1[$db_type].",
        status ".$db_int1[$db_type].",
        sync1 ".$db_varchar20[$db_type].",
        sync2 ".$db_varchar20[$db_type].",
        upload ".$db_text[$db_type].",
        PRIMARY KEY (ID)
    )";
    $result = db_query($q);
    if ($result) { echo __('Table termine (for events) created').".<br />\n"; }
    else { echo __('An error ocurred while creating table: ')." termine<br />\n"; $error = 1; }
    // create own index for anfang, ende, von, an and visi
    $result = db_query("CREATE INDEX termine_parent ON ".DB_PREFIX."termine (parent)");
    $result = db_query("CREATE INDEX termine_serie_id ON ".DB_PREFIX."termine (serie_id)");
    $result = db_query("CREATE INDEX termine_anfang ON ".DB_PREFIX."termine (anfang)");
    $result = db_query("CREATE INDEX termine_ende ON ".DB_PREFIX."termine (ende)");
    $result = db_query("CREATE INDEX termine_von ON ".DB_PREFIX."termine (von)");
    $result = db_query("CREATE INDEX termine_an ON ".DB_PREFIX."termine (an)");
    $result = db_query("CREATE INDEX termine_visi ON ".DB_PREFIX."termine (visi)");
    if ($db_type == "oracle") { sequence("termine"); }
    if ($db_type == "interbase") { ib_autoinc("termine"); }


    // Roles
    $result = db_query("
    CREATE TABLE ".DB_PREFIX."roles (
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
    costs ".$db_int1[$db_type].",
    PRIMARY KEY (ID)
  ) ");
    if ($db_type == "oracle") { sequence("roles"); }
    if ($db_type == "interbase") { ib_autoinc("roles"); }

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
    rights ".$db_varchar4[$db_type].",
    ownercolumn ".$db_varchar255[$db_type].",
    form_length ".$db_int11[$db_type].",
    field_type ".$db_varchar20[$db_type].",
    PRIMARY KEY (ID)
  ) ");
    if ($db_type == "oracle") { sequence("db_manager"); }
    if ($db_type == "interbase") { ib_autoinc("db_manager"); }
/*
    // FIX: Enable IDENTITY_INSERT
    if ($db_type == "ms_sql") {
        echo __("Turning on IDENTITY_INSERT... (only for MS SQL) <br />");
        $result = db_query("SET IDENTITY_INSERT ".DB_PREFIX."db_manager ON");
    }
*/
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
    if ($db_type == "oracle") { sequence("db_records","t_ID"); }
    if ($db_type == "interbase") { ib_autoinc("db_records"); }


    //filter
    $result = db_query("
    CREATE TABLE ".DB_PREFIX."filter (
    ID ".$db_int8_auto[$db_type].",
    von ".$db_int8[$db_type].",
    module ".$db_varchar255[$db_type].",
    name ".$db_varchar255[$db_type].",
    remark ".$db_text[$db_type].",
    filter ".$db_text[$db_type].",
    filter_sort ".$db_varchar100[$db_type].",
    filter_direction ".$db_varchar4[$db_type].",
    filter_operator ".$db_varchar10[$db_type].",
    PRIMARY KEY (ID)
  ) ");
    if ($db_type == "oracle") { sequence("filter"); }
    if ($db_type == "interbase") { ib_autoinc("filter"); }


    // 2. now add the values
    // ******* Contacts table ********
    //                                                              ID       , db_table , db_name   , form_name      , form_type , form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn) VALUES ('contacts','vorname','__(\"First Name\")','text','Type in the first name of the person',4,1,1,NULL,NULL,NULL,2,NULL,'1',0,NULL,NULL)") or db_die();
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn) VALUES ('contacts','firma','__(\"Company\")','text','Name of associated team or company',6,1,1,NULL,NULL,NULL,4,NULL,'1',0,NULL,NULL)") or db_die();
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn) VALUES ('contacts','nachname','__(\"Family Name\")','text','give the description: last name, company name or organisation etc.',5,1,1,NULL,NULL,NULL,1,NULL,'1',0,NULL,NULL)") or db_die();
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn) VALUES ('contacts','anrede','__(\"Salutation\")','text','Title of the person: Mr, Mrs, Dr., Majesty etc. ...',1,1,1,NULL,NULL,NULL,0,NULL,'0',0,NULL,NULL)") or db_die();
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn) VALUES ('contacts','email2','__(\"Email\") 2','email','enter an alternative mail address of this contact',18,1,1,NULL,NULL,NULL,0,'0','0',0,NULL,NULL)") or db_die();
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn) VALUES ('contacts','tel1','__(\"Phone\") 1','text','enter the primary phone number of this contact',8,1,1,NULL,NULL,NULL,5,NULL,'0',0,NULL,NULL)") or db_die();
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn) VALUES ('contacts','strasse','__(\"Street\")','text','the street where the person lives or the company is located',11,1,1,NULL,NULL,NULL,0,NULL,'0',0,NULL,NULL)") or db_die();
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn) VALUES ('contacts','tel2','__(\"Phone\") 2','text','enter the secondary phone number of this contact',9,1,1,NULL,NULL,NULL,0,'3','0',0,NULL,NULL)") or db_die();
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn) VALUES ('contacts','fax','__(\"Fax\")','text','enter the fax number of this contact',10,1,1,NULL,NULL,NULL,0, '0' ,'0',0,NULL,NULL)") or db_die();
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn) VALUES ('contacts','stadt','__(\"City\")','text','the city where the person lives or the company is located',12,1,1,NULL,NULL,NULL,0,NULL,'0',0,NULL,NULL)") or db_die();
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn) VALUES ('contacts','plz','__(\"Zip code\")','text','the coresponding zip code',13,1,1,NULL,NULL,NULL,0,NULL,'0',0,NULL,NULL)") or db_die();
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn) VALUES ('contacts','land','__(\"Country\")','text','the country',15,1,1,NULL,NULL,NULL,0,NULL,'0',0,NULL,NULL)") or db_die();
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn) VALUES ('contacts','bemerkung','__(\"Comment\")','textarea','a comment about this record',17,1,3,NULL,NULL,NULL,0,'2','0',0,NULL,NULL)") or db_die();
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn) VALUES ('contacts','mobil','__(\"Mobile\")','phone','enter the cellular phone number',19,1,1,NULL,NULL,NULL,0,'4','0',0,NULL,NULL)") or db_die();
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn) VALUES ('contacts','url','__(\"URL\")','url','the homepage - private or business',20,1,1,NULL,NULL,NULL,0,NULL,'0',0,NULL,NULL)") or db_die();
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn) VALUES ('contacts','div1','$cont_usrdef1','text','a default userdefined field',21,1,1,NULL,NULL,NULL,0,NULL,'0',0,NULL,NULL)") or db_die();
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn) VALUES ('contacts','div2','$cont_usrdef2','text','another default userdefined field',22,1,1,NULL,NULL,NULL,0,NULL,'0',0,NULL,NULL)") or db_die();
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn) VALUES ('contacts','state','__(\"State\")','text','region or state (USA)',17,1,1,NULL,NULL,NULL,0,NULL,'0',0,NULL,NULL)") or db_die();
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn) VALUES ('contacts','email','__(\"Email\")','email','enter the main email address of this contact',5,1,1,NULL,NULL,NULL,3,NULL,'1',0,NULL,NULL)") or db_die();
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn) VALUES ('contacts','kategorie','__(\"Category\")','select_category','Select an existing category or insert a new one',23,1,1,NULL,NULL,'(acc like system or ((von = $user_ID or acc like group or acc like %\\\"$user_kurz\\\"%) and ".addslashes($sql_user_group)."))',0,NULL,'1',0,NULL,NULL)") or db_die();

    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn, field_type) VALUES ('contacts','von'   ,'__(\"Author\")','user_show', NULL        , 0       , 1           , 1           , NULL       , NULL        , NULL       , 0        , '1'      , '1'        , 0          , NULL  , NULL, 'integer')") or db_die();

    // ******* Notes ********
    //                                                              ID       , db_table , db_name, form_name      , form_type , form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn) VALUES ('notes','remark','__(\"Comment\")','textarea','bodytext of the note',4,1,5,NULL,NULL,NULL,2,NULL,'1',0,NULL,NULL)") or db_die();
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn) VALUES ('notes','name','__(\"Title\")','text','Title of this note',1,1,1,'',NULL,NULL,1,NULL,'1',0,NULL,NULL)") or db_die();
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn, field_type) VALUES ('notes','contact','__(\"Contact\")','contact','Contact related to this note',5,1,1,NULL,NULL,NULL,3,NULL,NULL,0,NULL,NULL,'integer')") or db_die();
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn, field_type) VALUES ('notes','projekt','__(\"Projects\")','project','Project related to this note',6,1,1,NULL,NULL,NULL,4,NULL,NULL,0,NULL,NULL,'integer')") or db_die();
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn) VALUES ('notes','kategorie','__(\"Category\")','select_category','Select an existing category or insert a new one',7,1,1,NULL,NULL,'(acc like system or ((von = $user_ID or acc like group or acc like %\\\"$user_kurz\\\"%) and ".addslashes($sql_user_group)."))',0,NULL,'1',0,NULL,NULL)") or db_die();
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn) VALUES ('notes','div1','__(\"added\")','timestamp_create','',100,1,1,NULL,NULL,'',0,'2','0',0,NULL,NULL)") or db_die();
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn) VALUES ('notes','div2','__(\"changed\")','timestamp_modify','',101,1,1,NULL,NULL,'',0,'3','0',0,NULL,NULL)") or db_die();
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn, field_type) VALUES ('notes','von'   ,'__(\"Author\")','user_show', NULL        , 0       , 1           , 1           , NULL       , NULL        , NULL       , 0        , '1'      , '1'        , 0          , NULL  , NULL,'integer')") or db_die();

    // ******* Projekte ********
    //                                                              ID       , db_table , db_name, form_name      , form_type , form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn) VALUES ('projekte','ende','__(\"End\")','date','planned end',6,1,1,NULL,NULL,NULL,3,NULL,'1',0,NULL,NULL)") or db_die();
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn) VALUES ('projekte','name','__(\"Project Name\")','text','the name of the project',1,1,1,NULL,NULL,NULL,1,NULL,'1',0,NULL,NULL)") or db_die();
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn) VALUES ('projekte','wichtung','__(\"Priority\")','select_values','set the priority of this project',4,1,1,NULL,NULL,'0|1|2|3|4|5|6|7|8|9',0,'2','1',0,NULL,NULL)") or db_die();
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn) VALUES ('projekte','anfang','__(\"Begin\")','date','start day',5,1,1,NULL,NULL,NULL,2,NULL,'1',0,NULL,NULL)") or db_die();
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn, field_type) VALUES ('projekte','status','__(\"Status\") [%]','text','current completion status',NULL,NULL,NULL,NULL,NULL,'0',NULL,'3','1',0,NULL,NULL, 'integer')") or db_die();
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn, field_type) VALUES ('projekte','statuseintrag','__(\"Last status change\")','display','date of last change of status',12,1,1,NULL,NULL,NULL,0,'0','1',1,NULL,NULL, 'integer')") or db_die();
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn) VALUES ('projekte','ziel','__(\"Aim\")','textarea','descirbe the aim of this project',8,1,4,NULL,NULL,NULL,0,'0','0',1,NULL,NULL)") or db_die();
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn) VALUES ('projekte','note','__(\"Remark\")','textarea','remarks',7,1,4,NULL,NULL,NULL,0,'3','1',0,NULL,NULL)") or db_die();
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn, field_type) VALUES ('projekte','contact','__(\"Contact\")','contact','select the customer/contact',NULL,NULL,NULL,NULL,NULL,NULL,0,'0','0',1,NULL,NULL, 'integer')") or db_die();
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn, field_type) VALUES ('projekte','stundensatz','__(\"Hourly rate\")','text','hourly rate of this project',9,1,1,NULL,NULL,NULL,0,'0','0',0,NULL,NULL, 'integer')") or db_die();
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn, field_type) VALUES ('projekte','budget','__(\"Calculated budget\")','text','',10,1,1,NULL,NULL,NULL,0,'0','0',0,NULL,NULL, 'integer')") or db_die();
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn, field_type) VALUES ('projekte','von'   ,'__(\"Author\")','user_show', NULL        , 0       , 1           , 1           , NULL       , NULL        , NULL       , 0        , '1'      , '1'        , 0          , NULL  , NULL, 'integer')") or db_die();
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn) VALUES ('projekte','kategorie', '__(\"Category\")', 'select_values', 'current category (or status) of this project', 14, 1, 1, NULL, NULL, '1#__(\"offered\")|2#__(\"ordered\")|3#__(\"Working\")|4#__(\"ended\")|5#__(\"stopped\")|6#__(\"Re-Opened\")|7#__(\"waiting\")', 1, '3', '1', 0, NULL, NULL)") or db_die();
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn) VALUES ('projekte','chef', '__(\"Leader\")', 'userID', 'Select a user of this group as the project leader', 15, 1, 1, NULL, NULL, NULL, 0, '0', '0', 0, NULL, NULL)") or db_die();

    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn) VALUES ('rts', 'name', '__(\"Title\")', 'text', 'the title of the request', 1, 1, 1, NULL, NULL, NULL, 1, '0', '1', 0, NULL, NULL)") or db_die();
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn) VALUES ('rts', 'note', '__(\"Problem Description\")', 'textarea', 'the body of the request set by the customer', 15, 1, 5, '', '', '', 0, 'on', '1', 0, NULL, NULL)") or db_die();
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn) VALUES ('rts', 'submit', '__(\"Date\")', 'timestamp_create', 'date/time the request ha been submitted', 5, 1, 1, '', '', '', 2, 'on', '1', 0, NULL, NULL)") or db_die();
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn, field_type) VALUES ('rts', 'von', '__(\"Author\")', 'authorID', 'the user who wrote this request', 3, 1, 1, '', '', '', 3, 'on', '1', 0, NULL, NULL, 'integer')") or db_die();
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn, field_type) VALUES ('rts', 'contact', '__(\"Contact\")', 'contact_create', 'contact related to this request', 7, 1, 1, NULL, NULL, NULL, 0, '0', '0', 0, NULL, NULL, 'integer')") or db_die();
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn) VALUES ('rts', 'due_date', '__(\"Due date\")', 'date', 'due date of this request', 6, 1, 1, '', '', '', 0, '3', '1', 0, NULL, NULL)") or db_die();
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn) VALUES ('rts', 'assigned', '__(\"Assigned\")', 'userID', 'assign the request to this user', 4, 1, 1, '', '', '', 4, 'on', '1', 0, NULL, NULL)") or db_die();
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn, field_type) VALUES ('rts', 'priority', '__(\"Priority\")', 'select_values', 'set the priority of this project', 10, 1, 1, NULL, NULL, '0|1|2|3|4|5|6|7|8|9', 5, NULL, '1', 0, NULL, NULL, 'integer')") or db_die();
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn, field_type) VALUES ('rts', 'lock_user', '__(\"Locked by\")', 'userID', 'This ticket was locked by the follwing user', 0, 1, 1, NULL, NULL, NULL, 0, '5', NULL, 0, NULL, NULL, 'integer')") or db_die();
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn) VALUES ('rts', 'solution', '__(\"Solve\")', 'textarea', 'A text will cause: a mail to the customer and closing the request', 0, 1, 1, NULL, NULL, NULL, 0, '0', '1', 0, NULL, NULL)") or db_die();
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn, field_type) VALUES ('rts', 'solved', '__(\"Solved\") __(\"From\")', 'user_show', 'the user who has solved this request', 13, 1, 1, '', '', '', 0, 'on', '1', 0, NULL, NULL, 'integer')") or db_die();
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn) VALUES ('rts', 'solve_time', '__(\"Solved\")', 'timestamp_show', 'date and time when the request has been solved', 14, 1, 1, NULL, NULL, NULL, 0, '0', '0', 0, NULL, NULL)") or db_die();
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn) VALUES ('rts', 'category', '__(\"Account\")', 'select_sql', '', 8, 1, 1, '', '', 'SELECT ID,name                                                FROM @DB_PREFIX@db_accounts', 0, '', '1', 0, NULL, NULL)") or db_die();
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn, field_type) VALUES ('rts', 'proj', '__(\"Projects\")', 'project', 'project related to this request', 9, 1, 1, NULL, NULL, '', 0, '0', '0', 0, NULL, NULL,'integer')") or db_die();
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn) VALUES ('rts', 'status', '__(\"Status\")', 'select_values', 'state of this request', 0, 1, 1, '', '', '1#__(\"open\")|2# __(\"assigned\")|3#__(\"solved\")|4# __(\"verified\")|5# __(\"closed\")', 6, 'on', '1', 0, NULL, NULL)") or db_die();
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn) VALUES ('rts', 'filename', '__(\"Attachment\")', 'upload', '', 11, 1, 1, '', '', '', 0, '', '', 0, NULL, NULL)") or db_die();
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn, field_type) VALUES ('rts', 'ID', '__(\"Ticket ID\")', 'display', '', 2, 1, 1, '', '', '', 0, '4', '1', 0, NULL, NULL, 'integer')") or db_die();

    // ******* Datein (filemanager) ********
    //                                                              ID       , db_table , db_name, form_name      , form_type , form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn) VALUES ('dateien','filename','__(\"Change Filename\")','text','Title of the file or directory',1,1,1,'',NULL,NULL,1,NULL,'1',0,NULL,NULL)") or db_die();
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn) VALUES ('dateien','remark','__(\"Comment\")','textarea','remark related to this file',4,1,5,NULL,NULL,NULL,0,NULL,'1',0,NULL,NULL)") or db_die();
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn, field_type) VALUES ('dateien','contact','__(\"Contact\")','contact','Contact related to this file',5,1,1,NULL,NULL,NULL,4,NULL,NULL,0,NULL,NULL, 'integer')") or db_die();
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn) VALUES ('dateien','div2','__(\"Projects\")','project','Project related to this file',6,1,1,NULL,NULL,NULL,3,NULL,NULL,0,NULL,NULL)") or db_die();
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn) VALUES ('dateien','kat','__(\"Category\")','select_category','Select an existing category or insert a new one',7,1,1,NULL,NULL,'(acc like system or ((von = $user_ID or acc like group or acc like %\\\"$user_kurz\\\"%) and ".addslashes($sql_user_group)."))',0,'2','1',0,NULL,NULL)") or db_die();
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn) VALUES ('dateien','datum','__(\"changed\")','timestamp_modify','',101,1,1,NULL,NULL,'',0,NULL,'0',0,NULL,NULL)") or db_die();
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn, field_type) VALUES ('dateien','filesize','__(\"filesize (Byte)\")','display_byte','',0,1,1,NULL,NULL,'',2,NULL,'0',0,NULL,NULL, 'integer')") or db_die();
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn, field_type) VALUES ('dateien','lock_user','__(\"locked by\")','user_show','Name of the user who has locked this file temporarly',11,1,1,'','','',0,'3','0',0,NULL,NULL, 'integer')") or db_die();
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn, field_type) VALUES ('dateien' ,'von'   ,'__(\"Author\")','user_show', NULL        , 0       , 1           , 1           , NULL       , NULL        , NULL       , 0        , '1'      , '1'        , 0          , NULL  , NULL, 'integer')") or db_die();

    // ******* Todo ********
    //                                                              ID       , db_table , db_name, form_name      , form_type , form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn) VALUES ('todo','remark','__(\"Title\")','text','Kurze Beschreibung',1,1,1,NULL,NULL,NULL,1,NULL,'1',0,NULL,NULL)") or db_die();
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn) VALUES ('todo','deadline','__(\"Deadline\")','date','',7,1,1,'','','',4,'','1',0,NULL,NULL)") or db_die();
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn) VALUES ('todo','datum','__(\"Date\")','timestamp_create','',5,1,1,'','','',0,'','1',0,NULL,NULL)") or db_die();
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn, field_type) VALUES ('todo','priority','__(\"Priority\")','select_values',NULL,4,1,1,NULL,NULL,'0|1|2|3|4|5|6|7|8|9',0,'2','1',0,NULL,NULL, 'integer')") or db_die();
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn, field_type) VALUES ('todo','project','__(\"Project\")','project',NULL,9,1,1,NULL,NULL,NULL,6,NULL,'1',0,NULL,NULL, 'integer')") or db_die();
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn, field_type) VALUES ('todo','contact','__(\"Contact\")','contact',NULL,8,1,1,NULL,NULL,NULL,0,NULL,'1',0,NULL,NULL, 'integer')") or db_die();
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn) VALUES ('todo','note','__(\"Describe your request\")','textarea',NULL,11,2,3,NULL,NULL,NULL,0,NULL,'1',0,NULL,NULL)") or db_die();
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn) VALUES ('todo','comment1','__(\"Remark\") __(\"Author\")','textarea',NULL,12,2,3,NULL,NULL,NULL,NULL,NULL,'1',1,'o','von')") or db_die();
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn) VALUES ('todo','comment2','__(\"Remark\") __(\"Receiver\")','textarea',NULL,13,2,3,NULL,NULL,NULL,NULL,NULL,'1',1,'o','ext')") or db_die();
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn, field_type) VALUES ('todo','von','__(\"Of\")','user_show',NULL,2,1,1,NULL,NULL,NULL,2,'1','1',0,NULL,NULL,'integer')") or db_die();  // changed list_alt to 1 per case #542
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn) VALUES ('todo','anfang','__(\"Begin\")','date',NULL,6,1,1,NULL,NULL,NULL,0,NULL,'1',0,NULL,NULL)") or db_die();
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn, field_type) VALUES ('todo','ext','__(\"To\")','userID',NULL,3,1,1,NULL,NULL,NULL,3,NULL,'1',0,NULL,NULL, 'integer')") or db_die();
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn, field_type) VALUES ('todo', 'progress', '__(\"Progress\") [%]', 'text', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, 0, NULL, NULL, 'integer')") or db_die();
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn, field_type) VALUES ('todo', 'status', '__(\"Status\")', 'select_values', NULL, NULL, NULL, NULL, NULL, NULL, '1#__(\"waiting\")|2#__(\"Open\")|3#__(\"accepted\")|4#__(\"rejected\")|5#__(\"ended\")', 7, NULL, NULL, 0, NULL, NULL, 'integer')") or db_die();

    // ******* Termine ********
    //                                                              ID       , db_table , db_name, form_name      , form_type , form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn) VALUES ('termine', 'event', '__(\"Title\")', 'text', 'Title of this event', 1, 1, 1, '', NULL, NULL, 1, NULL, '1', 0, NULL, NULL)") or db_die();
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn) VALUES ('termine', 'datum', '__(\"Date\")', 'text', 'Date of this event', 2, 1, 1, '', NULL, NULL, 4, NULL, '1', 0, NULL, NULL)") or db_die();
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn) VALUES ('termine', 'anfang', '__(\"Start\")', 'text', 'Title of this event', 3, 1, 1, '', NULL, NULL, 5, NULL, '1', 0, NULL, NULL)") or db_die();
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn) VALUES ('termine', 'ende', '__(\"End\")', 'text', 'end of this event', 4, 1, 1, '', NULL, NULL, 6, NULL, '1', 0, NULL, NULL)") or db_die();
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn, field_type)
                             VALUES ('termine', 'von', '__(\"Author\")', 'select_sql', 'Author of this event', 5, 1, 1, '', NULL,
                                     'SELECT DISTINCT u.ID, u.nachname, u.vorname
                                                FROM @DB_PREFIX@termine AS t, @DB_PREFIX@users AS u
                                               WHERE t.von = u.ID
                                                 AND t.an  = \$user_ID
                                            ORDER BY u.nachname, u.vorname', 2, '1' , '0', 0, NULL, NULL, 'integer')") or db_die(); // changed list_alt to 1 per case #542
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn, field_type)
                             VALUES ('termine', 'an', '__(\"Recipient\")', 'select_sql', 'Recipient', 6, 1, 1, '', NULL,
                                     'SELECT DISTINCT u.ID, u.nachname, u.vorname
                                                FROM @DB_PREFIX@termine AS t, @DB_PREFIX@users AS u
                                               WHERE t.an  = u.ID
                                                 AND t.von = \$user_ID
                                            ORDER BY u.nachname, u.vorname', 3, NULL, '0', 0, NULL, NULL, 'integer')") or db_die();
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn, field_type) VALUES ('termine', 'partstat', '__(\"Participation\")', 'select_values', 'Title of this event', 7, 1, 1, '', NULL, '1#__(\"untreated\")|2#__(\"accepted\")|3#__(\"rejected\")', 7, NULL, '1', 0, NULL, NULL, 'integer')") or db_die();

    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn) VALUES ('termine', 'remark', '__(\"Remark\")', 'text', '', 0, 0, 0, '', NULL, NULL, 0, '2', '0', 0, NULL, NULL)") or db_die();
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn) VALUES ('termine', 'ort', '__(\"Location\")', 'text', '', 0, 0, 0, '', NULL, NULL, 0, '3', '0', 0, NULL, NULL)") or db_die();

    // ******* Mail Client ********
    //                                                              ID       , db_table     , db_name        , form_name         , form_type        ,form_tooltip,form_pos,form_colspan,form_rowspan,form_regexp,form_default,form_select,list_pos,list_alt,filter_show,db_inactive,rights,ownercolumn
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn) VALUES ('mail_client', 'remark'       , '__(\"Comment\")' , 'textarea'       , NULL       , 1      , 2          , 2          , NULL      , NULL       , NULL      , 0      , '2'      , 'on'      , 0         , NULL , NULL)") or db_die();
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn) VALUES ('mail_client', 'subject'      , '__(\"subject\")' , 'text'           , NULL       , 0      , 0          , 0          , NULL      , NULL       , NULL      , 1      , NULL   , 'on'      , 0         , NULL , NULL)") or db_die();
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn) VALUES ('mail_client', 'sender'       , '__(\"Sender\")'  , 'email'          , NULL       , 0      , 0          , 0          , NULL      , NULL       , NULL      , 2      , NULL   , 'on'      , 0         , NULL , NULL)") or db_die();
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn) VALUES ('mail_client', 'kat'          , '__(\"Category\")', 'select_category', NULL       , 2      , 2          , 1          , NULL      , NULL       , NULL      , 0      , '3'      , 'on'      , 0         , NULL , NULL)") or db_die();
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn, field_type) VALUES ('mail_client', 'projekt'      , '__(\"Project\")' , 'project'        , NULL       , 3      , 2          , 1          , NULL      , NULL       , NULL      , 0      , '4'      , 'on'      , 0         , NULL , NULL, 'integer')") or db_die();
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn) VALUES ('mail_client', 'date_inserted', '__(\"Date\")'    , 'timestamp'      , NULL       , 0      , 0          , 0          , NULL      , NULL       , NULL      , 4      , NULL   , 'on'      , 0         , NULL , NULL)") or db_die();
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn, field_type) VALUES ('mail_client', 'contact'      , '__(\"Contact\")' , 'contact'        , NULL       , 4      , 2          , 1          , NULL      , NULL       , NULL      , 0      , NULL   , 'on'      , 0         , NULL , NULL, 'integer')") or db_die();
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn, field_type) VALUES ('mail_client' ,'von'          , '__(\"Author\")'  , 'user_show'      , NULL       , 0      , 1          , 1          , NULL      , NULL       , NULL      , 0      , '1'      , 'on'      , 0         , NULL , NULL, 'integer')") or db_die();
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn) VALUES ('mail_client' ,'recipient'    , '__(\"To\")'      , 'email'          , NULL       , 0      , 0          , 0          , NULL      , NULL       , NULL      , 3      , '0'      , 'on'      , 0         , NULL , NULL)") or db_die();
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn) VALUES ('mail_client' ,'body'         , '__(\"Body\")'    , 'text'           , NULL       , 0      , 0          , 0          , NULL      , NULL       , NULL      , 0      , '5'      , 'on'      , 0         , NULL , NULL)") or db_die();

    // ******* DB_records ********
    //                                                              ID       , db_table , db_name, form_name      , form_type , form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn) VALUES ('db_records', 't_module', '__(\"Module\")', 'display', 'Module name', 1, 1, 1, NULL, NULL, NULL, 1, NULL, '1', 0, NULL, NULL)") or db_die();
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn) VALUES ('db_records', 't_remark', '__(\"Remark\")', 'text', 'Remark', 3, 1, 1, NULL, NULL, NULL, 2, NULL, '1', 0, NULL, NULL)") or db_die();
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn, field_type) VALUES ('db_records', 't_archiv', '__(\"Archive\")', 'text', 'Archive', 0, 1, 1, NULL, NULL, NULL, 0, NULL, '1', 0, NULL, NULL, 'integer')") or db_die();
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn, field_type) VALUES ('db_records', 't_touched', '__(\"Touched\")', 'text', 'Touched', 0, 1, 1, NULL, NULL, NULL, 0, NULL, '1', 0, NULL, NULL, 'integer')") or db_die();
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn) VALUES ('db_records', 't_name', '__(\"Title\")', 'text', 'Title', 2, 1, 1, NULL, NULL, NULL, 1, NULL, '1', 0, NULL, NULL)") or db_die();
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn, field_type) VALUES ('db_records', 't_wichtung', '__(\"Priority\")', 'select_values', 'Priority', 4, 1, 1, NULL, NULL, '0|1|2|3|4|5|6|7|8|9', 4, NULL, '1', 0, NULL, NULL, 'integer')") or db_die();
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn) VALUES ('db_records', 't_reminder_datum', '__(\"Resubmission at:\")', 'date', 'Date', 5, 1, 1, NULL, NULL, '', 5, NULL, '1', 0, NULL, NULL)") or db_die();
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn, field_type) VALUES ('db_records', 't_record', '__(\"Record set\")', 'display', 'ID of the target record', 0, 1, 1, '', NULL, NULL, 0, NULL, '0', 0, NULL, NULL, 'integer')") or db_die();
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn) VALUES ('db_records', 't_datum', '__(\"Date\")', 'date', '', 0, 1, 1, NULL, NULL, '', 0, NULL, '0', 0, NULL, NULL)") or db_die();
    
    // ******* DB_records ********
    // costs
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn ,form_length, field_type) VALUES ('costs', 'name',   '__(\"Title\")'   , 'text'           , 'Title of this note'          , 1, 1, 1, NULL, NULL, NULL, 1, NULL, '1', 0, NULL, NULL, NULL, NULL)") or db_die();
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn ,form_length, field_type) VALUES ('costs', 'remark', '__(\"Comment\")' , 'textarea'       , 'bodytext of the note'        , 2, 1, 5, NULL, NULL, NULL, 2, NULL, '1', 0, NULL, NULL, NULL, NULL)") or db_die();
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn ,form_length, field_type) VALUES ('costs', 'amount', '__(\"Amount\")'  , 'text'           , 'Amount of the cost'          , 3, 1, 1, NULL, NULL, NULL, 0, NULL, '1', 0, NULL, NULL, NULL, NULL)") or db_die();
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn ,form_length, field_type) VALUES ('costs', 'contact','__(\"Contact\")' , 'contact'        , 'Contact related to this note', 4, 1, 1, NULL, NULL, NULL, 4, NULL, '1', 0, NULL, NULL, NULL, 'integer')") or db_die();
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn ,form_length, field_type) VALUES ('costs', 'projekt','__(\"Projects\")', 'project'        , 'Project related to this note', 5, 1, 1, NULL, NULL, NULL, 5, NULL, '1', 0, NULL, NULL, NULL, 'integer')") or db_die();
    $result = db_query("INSERT INTO ".DB_PREFIX."db_manager (db_table, db_name, form_name, form_type, form_tooltip, form_pos, form_colspan, form_rowspan, form_regexp, form_default, form_select, list_pos, list_alt, filter_show, db_inactive, rights, ownercolumn ,form_length, field_type) VALUES ('costs', 'datum',  '__(\"Date\")'    , 'date'           , 'Datum'                       , 6, 1, 1, NULL, NULL, NULL, 6, NULL, '1', 0, NULL, NULL, NULL, NULL)") or db_die();

    //*********************************************
    // Example user and group data ****************

    if (trim($rootpass) == '') $rootpass = 'root';
    if (trim($rootpass) == '') $testpass = 'test';

    // crypt example user data?
    if ($pw_crypt) {
        $pw_root = md5('phprojektmd5'.$rootpass);
        $pw_test = md5('phprojektmd5'.$testpass);
    }
    else {
        $pw_root = $rootpass;
        $pw_test = $testpass;
    }

    // short names
    $l_root = "root1";
    $l_test = "test1";

    // if groups -> create group default
    if (($setup=='install' or ($old_config_array["PHPR_".strtoupper('groups')] <> 1))) { //if ($groups) { old version
        $result = db_query("INSERT INTO ".DB_PREFIX."gruppen
                                    (name,     kurz,kategorie,chef)
                             VALUES ('default','def','default', 1 )") or db_die();
        if (!$result) $error = 1;
        $result = db_query("INSERT INTO ".DB_PREFIX."grup_user
                                    (grup_ID, user_ID)
                             VALUES (1,       2)") or db_die();
        if (!$result) $error = 1;
        echo " ".__('The group default has been created').".<br />";
        $gr_var = "1";   // Flag for next insert: user test is assigned to group default
    }
    // create user root and test
    $result = db_query("INSERT INTO ".DB_PREFIX."users
                             (vorname,nachname,kurz,     pw,      acc,  sprache,loginname,remark,usertype,status)
                      VALUES ('root','root','$l_root','$pw_root','an','$langua','root','Administrator',3,0)") or db_die();
    if (!$result) $error = 1;
    
    if (isset($testcreation) && $testcreation == 1) {
        $result = db_query("INSERT INTO ".DB_PREFIX."users
                                 (vorname,nachname,kurz,pw,gruppe,acc,sprache,loginname,remark,usertype,status)
                          VALUES ('test','test','$l_test','$pw_test',$gr_var,'cy','$langua','test','Test User',0,0)") or db_die();
        if (!$result) $error = 1;
    
        echo "<br /> ".__('The following users have been inserted successfully in the table user:<br />root - (superuser with all administrative privileges)<br />test - (chief user with restricted access)').".<br /><br />";
        
        if      ($login_short == 0) echo "<b>LOGIN: root/$rootpass - test/$testpass</b><br />";
        else if ($login_short == 1) echo "<b>LOGIN: $l_root/$rootpass - $l_test/$testpass</b><br />";
        else if ($login_short == 2) echo "<b>LOGIN: root/$rootpass - test/$testpass</b><br />";
        
    }
    // tell the login parameters - login via short name and normal condition
    else {
        if      ($login_short == 0) echo "<b>LOGIN: root/$rootpass<br />";
        else if ($login_short == 1) echo "<b>LOGIN: $l_root/$rootpass</b><br />";
        else if ($login_short == 2) echo "<b>LOGIN: root/$rootpass</b><br />";
    }
} // Ende Klammer install  - end of install brace



// ************************************************
// compose string to be written into the config file.

// writing the config.inc.php using $config_array, values for gr_config and comments
$config_file = "<?php\n";

// compose string to be written into the config file.
$eol_trans = array("\r" =>'\r', "\n" =>'\n');
$mail_eoh  = strtr($mail_eoh, $eol_trans);
$mail_eol  = strtr($mail_eol, $eol_trans);

// file-based version-number to mark snapshot & cvs checkout date
if (file_exists('./VERSION')) {
    $version_nr = join(file('./VERSION'));
}
else {
    $version_nr = '5.1';
}
// On $sections_array (setup_configuration.php script) we have all the availabe sections to write
foreach ($sections_array as $oneSection) {
    $config_file .= "\n//$oneSection\n";

    foreach ($config_array as $oneConfig => $oneField) {
        if (isset($oneField['gr_config']) && $oneField['gr_config'] == $oneSection) {


            // ***************************************
            // exceptions! (we always have exceptions)
            // ***************************************

            // The version will be get from config array always and never from old config.inc.php (cos maybe we are doing an update)
            if ($oneConfig == 'version') {
                $_SESSION[$oneConfig] = $oneField['default'];
            }

            // The session name will be generated random before save the values.
            if ($oneConfig == 'session_name') {
                $_SESSION[$oneConfig] = "PHPR".strtoupper(substr(md5(time()), 0, 5));
            }

            // The timecard delete changes from boolean to timestamp, then it is necessary to force the default value
            // on upgrades
            if ($oneConfig == 'timecard_delete' && $setup == 'update') {
                if ($_SESSION[$oneConfig] == 1 || $_SESSION[$oneConfig] == 0) {

                    $_SESSION[$oneConfig] = $oneField['default'];
                }
            }

            // The filter_maxhits is used on 5.1 to personen filters, then it cannot be zero (only on upgrades)
            if ($oneConfig == 'filter_maxhits' && $setup == 'update') {
                if ($_SESSION[$oneConfig] == 0) {

                    $_SESSION[$oneConfig] = $oneField['default'];
                }
            }

            // mail_eoh and mail_eol needs to be stored with double " instead of single '
            if ($oneConfig == 'mail_eoh' || $oneConfig == 'mail_eol') {

                $_SESSION[$oneConfig] = check_mail_eol();
                $config_file .= "define('PHPR_".strtoupper($oneConfig)."', \"".$_SESSION[$oneConfig]."\");  // ".$oneField['comment']."\n";
            }
            else {
                $config_file .= "define('PHPR_".strtoupper($oneConfig)."', '".$_SESSION[$oneConfig]."');  // ".$oneField['comment']."\n";
            }
        }

    }

}

$config_file .= "\n?>\n";

if (!$error) {
    $fp = @fopen("config.inc.php", 'wb+');
    $fw = fwrite($fp, $config_file);
    if (!$fw) {
        $error = 1;
        echo "<br /><b>PANIC! <br /> config.inc.php can't be written!</b><br />";
    }
    fclose($fp);
}

// error or success?
if (!$error)
{
    echo "<b>".__('Finished')."!</b>\n";

    if ($setup == "install") {

        echo "<br /><p style='font-size: 10pt;'>";
        echo __('All required tables are installed and <br />the configuration file config.inc.php is rewritten<br />It would be a good idea to makea backup of this file.<br />')."<br /><br />\n";

        if ($groups) {

            echo __('The user test is now member of the group default.<br />Now you can create new groups and add new users to the group')."<br />"; }
            echo "<br />".__('To use PHProject with your Browser go to <b>index.php</b><br />Please test your configuration, especially the modules Mail and Files.')."</p>";

            // try to create the directory for uploads
            if ($setup == "install" and $file_path and !is_dir($file_path)) {

                $result = mkdir($file_path,0700);
                // no created? -> show error message
                if (!result) echo "<br /><b>".__('Please create the file directory').": '$file_path'</b><br /><br />";
            }

            // try to create the directory for attachments
            if ($old_config_array["PHPR_".strtoupper('quickmail')] < 2 and $quickmail == 2 and !is_dir(getcwd().'/attach')) {

                $result = mkdir(getcwd().'/attach',0700);
                // not created? -> show error message
                if (!$result) echo "<br /><b>".__('Please create the file directory').": 'attach'</b><br /><br />";
            }

            // create a directory for uploads in all cases since this dir will be used by several modules (future)
            mkdir(getcwd().'/docs',0700);

            // ensure that the webserver has the read/write priviledge for attach and upload dir
            if ($dateien or $quickmail == 2) {
                echo __('The server needs the privilege to write to the directories').":<br />\n";
                if ($filemanager) echo "<i>'upload'</i><br />\n";
                if ($quickmail == 2) echo "<i>'attach'</i><br />\n";
            }
    }
    // last message: call index.php :-)
    echo "<br />".__('Please run index.php: ')." <a href='index.php'>index.php</a><br />\n";

    // if instalation was sucessfull, then this javascrtip script will display the message at top of page
    echo "<script  languaje='javascript'>
              document.getElementById('login_text').innerHTML = '".__('Thanks for installing PHProjekt, please Login')."<a href=\"index.php\"> ".__('here')."</a><br />';
          </script>
        ";

}
// errors!!
else {
    echo "<b>".__('There were errors, please have a look at the messages above')."!</b>\n";
    echo __('<li>If you encounter any errors during the installation, please look into the <a href=help/faq_install.html target=_blank>install faq</a>or visit the <a href=http://www.PHProjekt.com/forum.html target=_blank>Installation forum</a></i>')."\n";
}


// End of page. Thanks for installing PHProjekt!
echo "</legend></fieldset>\n";
echo "</div>\n";
display_botton();

// destroy the session - on some system the first, on some system the second function doesn't work :-))
@session_unset();
@session_destroy();

?>
