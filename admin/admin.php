<?php
/**
* main script for administration of users, groups etc.
*
* @package    admin
* @module     main
* @author     Albrecht Guenther, $Author: polidor $
* @licence    GPL, see www.gnu.org/copyleft/gpl.html
* @copyright  2000-2006 Mayflower GmbH www.mayflower.de
* @version    $Id: admin.php,v 1.116.2.11 2007/08/02 16:42:59 polidor Exp $
*/
//this is very important for right authentication!
$file = 'admin';
define('PATH_PRE','../');
$module = 'admin';
require_once(PATH_PRE.'lib/lib.inc.php');
require_once(LIB_PATH.'/languages.inc.php');
require_once('./admin.inc.php');
// get all modules
include_once(LIB_PATH.'/show_modules.inc.php');
include_once(LIB_PATH.'/access_form.inc.php');
$_SESSION['common']['module'] = 'admin';

//titles for the modules
$titlenames['contacts']    = 'nachname';
$titlenames['projects']    = 'name';
$titlenames['notes']       = 'name';
$titlenames['helpdesk']    = 'name';
$titlenames['todo']        = 'remark';
$titlenames['files']       = 'filename';
$titlenames['mail']        = 'subject';
$titlenames['links']       = 't_name';
$titlenames['forum']       = 'titel';
$titlenames['filemanager'] = 'filename';

if (defined('PHPR_COSTS') && PHPR_COSTS == 1) {
    $titlenames['costs'] = 'costs';
}


// include the ldap library
if (PHPR_LDAP) {
    include_once(LIB_PATH.'/ldapconf.inc.php');
}

// only root can select a group
if ($group_select) {
    $group_ID = $group_select;
    $_SESSION['group_ID'] =& $group_ID;
    $sql_group = "(gruppe = ".(int)$group_ID.")";
}
// a group admin is assigned to his group
else if ($user_group <> '') {
    $group_ID = $user_group;
    $_SESSION['group_ID'] =& $group_ID;
    $sql_group = "(gruppe = ".(int)$group_ID.")";
}
else {
    $sql_group = "(gruppe = ".(int)$group_ID.")";
}


// array with the different user modes:
$acc_level   = array( "0" => __('No access'), "1" => __('Read access'), "2" => __('Write access') );
$user_types  = array( '0' => __('Normal user'), '1' => __('Resource'), "2" => __('User w/Chief Rights'), "3" => __('Administrator'), "4" => __('Client') );
$user_status = array( '0' => __('Active'), '1' => __('Inactive') );
// TODO: obsolete now, remove this next time
//$vis         = array( "y" => __('schedule readable to others'), "v" => __('schedule visible but not readable'), "n" => __('schedule invisible to others') );

// Menu und content
echo set_page_header();
include_once(LIB_PATH.'/navigation.inc.php');

$tabs   = array();
$output = '<div id="global-header">';
$output .= get_tabs_area($tabs);
$output .= '</div>';

$context_output = '';

$output .= "<div id='global-content'>\n<div id='left_container'>";

// button bar
$buttons = array();
if($user_type!=3)die(__('You are not allowed to access the admin area'));
if ($user_type==3 and $user_ID == 1) {
    $result = db_query("SELECT name
                          FROM ".DB_PREFIX."gruppen
                         WHERE ID = ".(int)$group_ID) or db_die();
    $row = db_fetch_row($result);
    // form start
    $hidden = array();
    if (SID) $hidden[session_name()] = session_id();
    $buttons[] = array('type' => 'form_start', 'hidden' => $hidden, 'name' => 'form1');
    $buttons[] = array('type' => 'text', 'text' => __('Choose group').__(':'));
    // group select
    $tmp = "<select name='group_select' onchange='document.form1.submit();'>\n";
    // no groups selected? show him a blank entry
    //if (!$group_ID) $output .= "<option value='0'></option>\n";
    $tmp .= "<option value='0'></option>\n";
    // fetch ID and name for db
    $result = db_query("SELECT ID, name
                          FROM ".DB_PREFIX."gruppen
                      ORDER BY name") or db_die();
    while ($row = db_fetch_row($result)) {
        $tmp .= "<option value='$row[0]'";
        if ($row[0] == $group_ID) $tmp .= ' selected="selected"';
        $tmp .= ">$row[1]</option>\n";
    }
    $tmp .= "</select>\n";
    $buttons[] = array('type' => 'text', 'text' => $tmp);
    // form end
    $buttons[] = array('type' => 'form_end');
    $buttons[] = array('type' => 'separator');
    $buttons[] = array('type' => 'text', 'text' => ' <a href="./module_designer.php?'.SID.'" target="_blank">'.__('Module Designer').'</a>');
}
$output .= get_buttons_area($buttons);

// Actions after the dialog
// ******************
// right frame
// ******************
//$output .= set_page_header();
//***************************
// output messages of actions
$output .= "<br />\n";


/**
*
*   group management
*
*/
if ($action1 == "groups") {

    // check form token
    check_csrftoken();

    // db action
    if ($mode == "anlegen") {
        if (!$name) {
            $output .= __('Please insert a name')."!<br class='clear' />\n";
        }
        else {
            $result = db_query("SELECT name, kurz
                                  FROM ".DB_PREFIX."gruppen
                                 WHERE ID <> ".(int)$group_nr."
                              ORDER BY name") or db_die();
            while ($row = db_fetch_row($result)) {
                if ($row[0] == $name) {
                    $output .= __('Name or short form already exists')."!";
                    $error   = 1;
                    $anlegen = '';
                }
                // don't check for existing short name when you want to modify the record
                if ($neu and $row[1] == $kurz) {
                    $output .= __('Name or short form already exists')."!";
                    $error   = 1;
                    $anlegen = '';
                }
            }
        }

        if (!$error) {
            if ($neu) {
                // write record to db
                $query = "INSERT INTO ".DB_PREFIX."gruppen
                                                (               name ,                  kurz,                   kategorie,                   bemerkung )
                                         VALUES ('".strip_tags($name)."','".strip_tags($kurz)."','".strip_tags($kategorie)."','".strip_tags($bemerkung)."')";
                $result = db_query($query) or db_die();
                $output .= strip_tags($name).': '.__('Group created').".<br class='clear' />\n";
            }
            // modify record
            else if ($aendern) {
                $result = db_query("UPDATE ".DB_PREFIX."gruppen
                                       SET name = '".strip_tags($name)."',
                                           kategorie = '".strip_tags($kategorie)."',
                                           bemerkung = '".strip_tags($bemerkung)."',
                                           chef = ".(int)$chef."
                                     WHERE ID = ".(int)$group_nr) or db_die();
            }
        }
    }
    $group_ID = $group_nr;
    $_SESSION['group_ID'] =& $group_ID;

    // merge group
    if ($loeschen1) {
        // fetch name of group to delete
        $result = db_query("SELECT name
                              FROM ".DB_PREFIX."gruppen
                             WHERE ID = ".(int)$group_nr) or db_die();
        $row = db_fetch_row($result);
        $name = $row[0];

        // update tables
        if (PHPR_CONTACTS) {
            $result = db_query("UPDATE ".DB_PREFIX."contacts
                                   SET gruppe = ".(int)$merge_target."
                                 WHERE gruppe = ".(int)$group_nr) or db_die();
        }
        if (PHPR_FILEMANAGER) {
            $result = db_query("UPDATE ".DB_PREFIX."dateien
                                   SET gruppe = ".(int)$merge_target."
                                 WHERE gruppe = ".(int)$group_nr) or db_die();
        }
        if (PHPR_FORUM) {
            $result = db_query("UPDATE ".DB_PREFIX."forum
                                   SET gruppe = ".(int)$merge_target."
                                 WHERE gruppe = ".(int)$group_nr) or db_die();
        }
        if (PHPR_BOOKMARKS) {
            $result = db_query("UPDATE ".DB_PREFIX."lesezeichen
                                   SET gruppe = ".(int)$merge_target."
                                 WHERE gruppe = ".(int)$group_nr) or db_die();
        }
        if (PHPR_PROJECTS) {
            $result = db_query("UPDATE ".DB_PREFIX."projekte
                                   SET gruppe = ".(int)$merge_target."
                                 WHERE gruppe = ".(int)$group_nr) or db_die();
        }
        if (PHPR_RTS) {
            $result = db_query("UPDATE ".DB_PREFIX."db_accounts
                                   SET gruppe = ".(int)$merge_target."
                                 WHERE gruppe = ".(int)$group_nr) or db_die();
        }

        // update table for assign user to groups, avoid double entries
        // select members of group to be deleted
        $result = db_query("SELECT user_ID
                              FROM ".DB_PREFIX."grup_user
                             WHERE grup_ID = ".(int)$group_nr) or db_die();
        while ($row = db_fetch_row($result)) {
            // look if this user is already member of the target group
            $result2 = db_query("SELECT ID
                                   FROM ".DB_PREFIX."grup_user
                                  WHERE user_ID = ".(int)$row[0]."
                                    AND grup_ID = ".(int)$merge_target) or db_die();
            $row2 = db_fetch_row($result2);
            // no entry? then move user to the new group
            if (!$row2[0]) {
                $result3 = db_query("UPDATE ".DB_PREFIX."grup_user
                                        SET grup_ID = ".(int)$merge_target."
                                      WHERE grup_ID = ".(int)$group_nr."
                                        AND user_ID = ".(int)$row[0]) or db_die();
            }
            // look whether the old group is the primary group of this user and change it
            $result2 = db_query("SELECT gruppe
                                   FROM ".DB_PREFIX."users
                                  WHERE ID = ".(int)$row[0]) or db_die();
            $row2 = db_fetch_row($result2);
            if ($row2[0] == $group_nr) {
                $result3 = db_query("UPDATE ".DB_PREFIX."users
                                        SET gruppe = ".(int)$merge_target."
                                      WHERE ID = ".(int)$row[0]) or db_die();
            }
        }
        // last action: delete record in table gruppen and grup_user
        $result4 = db_query("DELETE
                               FROM ".DB_PREFIX."gruppen
                              WHERE ID = ".(int)$group_nr) or db_die();
        $result5 = db_query("DELETE
                               FROM ".DB_PREFIX."grup_user
                              WHERE grup_ID = ".(int)$group_nr) or db_die();
        $output .= "<div class='admin_fields'>".strip_tags($name).' '.__(' is deleted.')."\n<br class='clear' />\n</div>\n";
    }
}


/**
*
*   user management
*
*/
if ($action1 == "user") {

    // check form token
    check_csrftoken();

    if ($anlegen == "aendern" && isset($_REQUEST['remove_settings'])) {
        // remove users settings
        $query = "UPDATE ".DB_PREFIX."users
                     SET settings = ''
                   WHERE ID = ".(int)$pers_ID;
        $result = db_query($query) or db_die();
        $output .= strip_tags($nachname).', '.strip_tags($vorname).': '.__('Settings removed.').".<br class='clear' />\n";
    }

    else if ($anlegen) {
        // crypt password
        if (PHPR_PW_CRYPT and $pw <> "") {
            $pw = md5('phprojektmd5'.$pw);
        }
        $result = db_query("SELECT ldap_name
                              FROM ".DB_PREFIX."users
                             WHERE ID = ".(int)$pers_ID);
        $row = db_fetch_row($result);
        if (PHPR_LDAP == 0) {
            $user_ldap_conf = 0;
        }
        else if (!isset($row[0]) || (strlen($row[0]) < 1)) {
            $user_ldap_conf = "1";
        }
        else {
            $user_ldap_conf = $row[0];
        }

        //*** checks***
        // no family name at all or pw or short name at creating? -> error!
        // ldap_sync == 2 means everything comes from LDAP
        // ldap_sync == 1 means everything comes from db therefore update
        if ((!PHPR_LDAP or (PHPR_LDAP and $ldap_conf[$user_ldap_conf]["ldap_sync"] == "1")) and
            (!$nachname or !$kurz or !$pw) and $anlegen == "neu_anlegen") {
            $output .= __('You have to fill in the following fields: family name, short name and password.');
            $error   = 1;
            $anlegen = '';
        }
        // in case ldap is activated a loginname must exist
        else if (PHPR_LDAP and (strlen($loginname) < 1) and $anlegen == "neu_anlegen") {
            $output .= __('You have to fill in the following fields: family name, short name and password.');
            $error   = 1;
            $anlegen = '';
        }

        // if admin is limited to his group, no group is specified and the new user must be in his group
        if (!$gruppe) $gruppe = $group_ID;
        // check whether default group is in group list
        $found = 0;
        for ($i = 0; $i < count($grup_user); $i++) {
            if ($gruppe == $grup_user[$i]) $found = 1;
        }
        // not selected? -> add it
        if (!$found) $grup_user[] = $gruppe;

        //$where = "WHERE gruppe = ".(int)$gruppe;
        $where = '';

        // check for double entries
        if (!PHPR_LDAP or ($ldap_conf[$user_ldap_conf]["ldap_sync"] == "1")) {
            if ($anlegen == "aendern") {
                if (isset($where) && strlen($where) > 4) {
                    $where .= " AND ";
                }
                else {
                    $where = "WHERE ";
                }
                $where .= "ID <> ".(int)$pers_ID;
            }

            $result = db_query("SELECT ID, vorname, nachname, kurz, gruppe, pw, loginname
                                  FROM ".DB_PREFIX."users
                                  $where") or db_die();
            while ($row = db_fetch_row($result)) {
                // same group can't have 2 users with same first AND last name (for the same group)
                if ($nachname == $row[2] && $vorname == $row[1] && $gruppe == $row[4]) {
                    $output .= __('This combination first name/family name already exists.');
                    $error = 1;
                }
                if ($loginname == $row[6]) {
                    $output .= __('This login name already exists! Please chosse another one.');
                    $error = 1;
                }
                if ($kurz == $row[3]) {
                    $output .= __('This short name already exists!');
                    $error = 1;
                }
                if ($ldap_conf[$user_ldap_conf]["ldap_sync"] == "1") {
                    if (strlen($ldap_name) < 1) {
                        $output .= __('ldap name');
                        $error = 1;
                    }
                }
            }
        }
        else {
            // still check loginname
            if (strlen($loginname) < 1) {
                $output .= __('You have to fill in the following fields: family name, short name and password.');
                $error = 1;
            }
            if (strlen($ldap_name) < 1) {
                $output .= __('ldap name');
                $error = 1;
            }
        } // *** end checks

        // *** no errors? -> db action
        if (!$error) {
            // *** new record
            if ($anlegen == "neu_anlegen") {
                // insert new record in db
                $query = "INSERT INTO ".DB_PREFIX."users
                             ( vorname  , nachname  ,  kurz,   pw,   firma,         gruppe           ,  email ,  tel1 ,  tel2 ,  fax ,  strasse ,   stadt ,   plz ,   land ,   sprache ,   mobil ,  loginname ,   ldap_name    ,   anrede ,   sms ,        role ,   hrate,    remark,         usertype ,         status  )
                      VALUES ('".strip_tags($vorname)."','".strip_tags($nachname)."','".strip_tags($kurz)."','".strip_tags($pw)."','".strip_tags($firma)."',".(int)$preferred_group.",'".strip_tags($email)."','".strip_tags($tel1)."','".strip_tags($tel2)."','".strip_tags($fax)."','".strip_tags($strasse)."', '".strip_tags($stadt)."', '".strip_tags($plz)."', '".strip_tags($land)."', '".strip_tags($sprache)."', '".strip_tags($mobil)."','".strip_tags($loginname)."', '".strip_tags($ldap_profile)."', '".strip_tags($anrede)."', '".strip_tags($sms)."',".(int)$role.",'".strip_tags($hrate)."', '".strip_tags($remark)."',".(int)$usertype.",".(int)$status.")";

                $result = db_query($query) or db_die();
                // fetch user ID from just created record
                $result = db_query("SELECT ID
                                      FROM ".DB_PREFIX."users
                                  ORDER BY ID DESC") or db_die();
                $row = db_fetch_row($result);
                // insert into grup_user
                for ($i = 0; $i < count($grup_user); $i++) {
                    $result = db_query("INSERT INTO ".DB_PREFIX."grup_user
                                                    (        grup_ID        ,        user_ID )
                                             VALUES (".(int)$grup_user[$i].",".(int)$row[0].")") or db_die();
                }

                //creating default sent folder (for emails)
                if (PHPR_QUICKMAIL > 1) {
                    $query = "INSERT INTO ".DB_PREFIX."mail_client
                               (        von     ,    date_received, typ,subject         ,parent,acc      ,acc_write,     gruppe           ,    date_inserted,trash_can )
                        VALUES (".(int)$row[0].",'".$dbTSnull."'  , 'd','".__("Sent")."',0     ,'private',''       ,".(int)$grup_user[0].",'".$dbTSnull."'  ,'N'       )";
                    $result = db_query($query) or db_die();

                    $query = "SELECT max(ID) FROM ".DB_PREFIX."mail_client
                               WHERE subject = '".__("Sent")."'
                                 AND von = ".(int)$row[0];

                    $result = db_query($query) or db_die();
                    if ($row4 = db_fetch_row($result)) {
                        $sentId = $row4[0];
                        update_general_rule('outgoing', $sentId, $row[0]);
                    }
                }
                $output .= strip_tags($nachname).", ".strip_tags($vorname).": ".__('the user is now in the list.')."<br class='clear' />\n";
            }

            // **** modify
            else if ($anlegen == "aendern") {

                // encrypt pw
                if (!$pw) $pw_string = "";
                else      $pw_string = "pw='$pw',";

                // create query string for groups, but only if admin = root
                if ($user_ID == 1 and $pers_ID > 1) $group_string = "gruppe = ".(int)$preferred_group.",";
                else $group_string = '';

                if ((PHPR_LDAP == 0) or ($ldap_conf[$user_ldap_conf]["ldap_sync"] == 1)) {
                    $query = "UPDATE ".DB_PREFIX."users
                                 SET vorname = '$vorname',
                                     nachname = '$nachname',
                                     firma = '$firma',
                                     $pw_string
                                     $group_string
                                     email = '$email',
                                     tel1 = '$tel1',
                                     tel2 = '$tel2',
                                     fax = '$fax',
                                     strasse = '$strasse',
                                     stadt = '$stadt',
                                     plz = '$plz',
                                     land = '$land',
                                     sprache = '$sprache',
                                     mobil = '$mobil',
                                     loginname = '$loginname',
                                     ldap_name = '$ldap_name',
                                     anrede = '$anrede',
                                     sms = '$sms',
                                     role = ".(int)$role.",
                                     hrate = '$hrate',
                                     remark = '$remark',
                                     usertype = ".(int)$usertype.",
                                     status = ".(int)$status."
                               WHERE ID = ".(int)$pers_ID;
                }
                else {
                    $query = "UPDATE ".DB_PREFIX."users
                                 SET $group_string
                                     loginname = '$loginname',
                                     sms = '$sms',
                                     role = ".(int)$role.",
                                     ldap_name = '$ldap_name'
                               WHERE ID = ".(int)$pers_ID;
                }

                // update db record in users table
                $result = db_query(xss($query)) or db_die();

                // update group status, but only if admin = root
                if ($user_ID == 1) {
                    $result = db_query("DELETE
                                          FROM ".DB_PREFIX."grup_user
                                         WHERE user_ID = ".(int)$pers_ID) or db_die();
                    if (!$grup_user[0]) $grup_user[0] = $group_ID;
                    for ($i = 0; $i < count($grup_user); $i++) {
                        $result = db_query("INSERT INTO ".DB_PREFIX."grup_user
                                                        (        grup_ID        ,        user_ID  )
                                                 VALUES (".(int)$grup_user[$i].",".(int)$pers_ID.")") or db_die();
                    }
                }
                $output .= strip_tags($nachname).', '.strip_tags($vorname)-': '.__('the data set is now modified.').".<br class='clear' />\n";
            }
        }
    }

    //**************
    // user: delete record
    if ($loeschen1) {

        // checks
        // 1. check: no user chosen
        if (!$pers_ID) {
            $output .= __('Please choose a user')."!";
            $error = 1;
        }
        // 2. check: don't delete the root!
        if ($pers_ID == 1) {
            $output .= __('Deletion of super admin root not possible')."!";
            $error = 1;
        }

        // no error? -> begin to delete ...
        if (!$error) {
            // fetch his name ..
            $result = db_query("SELECT nachname, kurz
                                  FROM ".DB_PREFIX."users
                                 WHERE ID = ".(int)$pers_ID) or db_die();
            while ($row = db_fetch_row($result)) {
                $name = $row[0];
                $kurz = $row[1];
            }

            // warn, if he's a member of a project team
            if (PHPR_PROJECTS) {

                // get the project listing
                $project_names = array();

                $result = db_query("SELECT name
                                      FROM ".DB_PREFIX."projekte as p, ".DB_PREFIX."project_users_rel pur
                                     WHERE p.ID = pur.project_ID
                                       AND pur.user_ID = ".(int)$pers_ID) or db_die();
                while ($row = db_fetch_row($result)) {
                    $project_names[] = $row[0];
                }

                if (count($project_names) > 0) {
                    // delete user
                    $result = db_query("DELETE
                                          FROM ".DB_PREFIX."project_users_rel
                                         WHERE user_ID = ".(int)$pers_ID) or db_die();


                    $output .= "<b>".strip_tags($name)." ".__('The user was removed from the following projects').": ".implode(', ',$project_names)."</b><br class='clear' />\n";
                }
            }

            // delete membership in groups
            $result = db_query("DELETE
                                  FROM ".DB_PREFIX."grup_user
                                 WHERE user_ID = ".(int)$pers_ID) or db_die();

            // delete profiles
            // 1. his own
            $result = db_query("DELETE
                                  FROM ".DB_PREFIX."profile
                                 WHERE von = ".(int)$pers_ID) or db_die();
            $output .= strip_tags($name).": ".__('All profiles are deleted').". <br class='clear' />\n";
            // 2. as a participant
            $result = db_query("SELECT ID, von, bezeichnung, personen
                                  FROM ".DB_PREFIX."profile
                                 WHERE personen LIKE '%".xss($kurz)."%'") or db_die();
            while ($row = db_fetch_row($result)) {
                $an = unserialize($row[3]);
                for ($i=0; $i<count($an); $i++) {
                    if ($an[$i] == $kurz) {
                        $a = $i;
                    }
                }
                unset($an[$a]);
                $an2 = serialize($an);
                $return2 = db_query("UPDATE ".DB_PREFIX."profile
                                        SET personen = '$an2'
                                      WHERE ID = ".(int)$row[0]) or db_die();
            }
            $output .= strip_tags($name)." ".__('is taken out of all user profiles').".<br class='clear' />\n";

            // delete todos
            if (PHPR_TODO) {
                $result = db_query("DELETE
                                      FROM ".DB_PREFIX."todo
                                     WHERE von = ".(int)$pers_ID) or db_die();
                $output .= strip_tags($name).": ".__('All todo lists of the user are deleted').". <br class='clear' />\n";
            }

            // delete his files, links and dirs set to private
            if (PHPR_FILEMANAGER) {
                $result = db_query("SELECT ID, filename, tempname, typ, filesize, acc, remark
                                      FROM ".DB_PREFIX."dateien
                                     WHERE von = ".(int)$pers_ID) or db_die();
                while ($row = db_fetch_row($result)) {
                    // delete files if they are set top private
                    if ($row[5] =="private") {
                        // only delete file when it is not a link
                        if ($row[4] > 0) {
                            $path = PHPR_FILE_PATH."/$row[2]";
                            unlink($path);
                        }
                        $result2 = db_query("DELETE
                                               FROM ".DB_PREFIX."dateien
                                              WHERE ID = ".(int)$row[0]) or db_die();
                        // look for files in the subdirectory or if it si a file with versioning
                        if ($row[3] == "d" or $row[3] == "fv") del($row[0]);
                    }
                    // if set to non-private, add a remark in the remark :)
                    else {
                        $remark = xss("[ $name ]".$row[6]);
                        $result2 = db_query("UPDATE ".DB_PREFIX."dateien
                                                SET remark = '$remark'
                                              WHERE ID = ".(int)$row[0]) or db_die();
                    }
                }
            }

            // delete notes set to private
            if (PHPR_NOTES) {
                $result = db_query("DELETE
                                      FROM ".DB_PREFIX."notes
                                     WHERE von = ".(int)$pers_ID."
                                       AND (ext IS NULL OR ext = 0)") or db_die();
            }

            // update polls
            if (PHPR_VOTUM) {
                $kurz = strip_tags($kurz);
                $result = db_query("SELECT ID, an
                                          FROM ".DB_PREFIX."votum
                                         WHERE an LIKE '%$kurz%'
                                           AND fertig NOT LIKE '%$kurz%'") or db_die();
                while ($row = db_fetch_row($result)) {
                    $ID = $row[0];
                    $an = unserialize($row[1]);
                    for ($i=0; $i<count($an); $i++) {
                        if ($an[$i] == $kurz) {
                            $a = $i;
                        }
                    }
                    unset($an[$a]);
                    $an2 = serialize($an);
                    $return2 = db_query("UPDATE ".DB_PREFIX."votum
                                            SET an = '$an2'
                                          WHERE ID = ".(int)$row[0]) or db_die();
                }
                $output .= strip_tags($name)." ".__('is taken out of these votes where he/she has not yet participated').".<br class='clear' />\n";
            }

            // delete schedule
            $result = db_query("DELETE
                                  FROM ".DB_PREFIX."termine
                                 WHERE an = ".(int)$pers_ID) or db_die();
            $output .= strip_tags($name).": ".__('All events are deleted').". <br class='clear' />\n";
            // delte user itself
            $result = db_query("DELETE
                                  FROM ".DB_PREFIX."users
                                 WHERE ID = ".(int)$pers_ID) or db_die();
            $output .= strip_tags($name).": ".__('user file deleted').". <br class='clear' />\n";
            $output .= "<i>".strip_tags($name).": ".__('bank account deleted')." ;-))</i><br class='clear' /><br class='clear' />\n";
            $output .= __('finished').".\n";
        }
    }
}


/**
*
*   role management
*
*/
if ($action1 == "roles" && PHPR_ROLES) {

    // check form token
    check_csrftoken();

    //delete
    if ($loeschen1) {
        // remove the assignment to users
        $result = db_query("UPDATE ".DB_PREFIX."users
                               SET role = 0
                             WHERE role = ".(int)$roles_ID) or db_die();
        // delete record itself
        $result = db_query("DELETE
                              FROM ".DB_PREFIX."roles
                             WHERE ID = ".(int)$roles_ID) or db_die();
        // show message
        $output .= __('Role deleted, assignment to users for this role removed').".<br class='clear' />\n";
    }

    if ($anlegen) {
        if (!$roles_ID) $roles_ID = 0;
        // check for double entries
        $result = db_query("SELECT ID
                              FROM ".DB_PREFIX."roles
                             WHERE title = '".xss($title)."'
                               AND ID <> ".(int)$roles_ID) or db_die();
        $row = db_fetch_row($result);
        if ($row[0] > 0) {
            if (($anlegen == "aendern" and $row[0] <> $roles_ID) or $anlegen == "neu_anlegen") {
                $output .= __('This name already exists');
                $error = 1;
            }
        }

        if (!$error) {
            // new
            if ($anlegen == "neu_anlegen") {
                if (!$title) die(__('Please insert a name')."!");

                $db_cols = array('von', 'title', 'remark');
                $db_vals = array($user_ID, "'$title'", "'$remark'");
                if (PHPR_TODO) {
                    $db_cols[] = 'todo';
                    $db_vals[] = (int)$todo_m;
                }
                if (PHPR_CALENDAR) {
                    $db_cols[] = 'calendar';
                    $db_vals[] = (int)$calendar_m;
                }
                if (PHPR_CONTACTS) {
                    $db_cols[] = 'contacts';
                    $db_vals[] = (int)$contacts_m;
                }
                if (PHPR_FORUM) {
                    $db_cols[] = 'forum';
                    $db_vals[] = (int)$forum_m;
                }
                if (PHPR_CHAT) {
                    $db_cols[] = 'chat';
                    $db_vals[] = (int)$chat_m;
                }
                if (PHPR_FILEMANAGER) {
                    $db_cols[] = 'filemanager';
                    $db_vals[] = (int)$filemanager_m;
                }
                if (PHPR_BOOKMARKS) {
                    $db_cols[] = 'bookmarks';
                    $db_vals[] = (int)$bookmarks_m;
                }
                if (PHPR_VOTUM) {
                    $db_cols[] = 'votum';
                    $db_vals[] = (int)$votum_m;
                }
                if (PHPR_QUICKMAIL) {
                    $db_cols[] = 'mail';
                    $db_vals[] = (int)$mail_m;
                }
                if (PHPR_NOTES) {
                    $db_cols[] = 'notes';
                    $db_vals[] = (int)$notes_m;
                }
                if (PHPR_RTS) {
                    $db_cols[] = 'helpdesk';
                    $db_vals[] = (int)$helpdesk_m;
                }
                if (PHPR_PROJECTS) {
                    $db_cols[] = 'projects';
                    $db_vals[] = (int)$projects_m;
                }
                if (PHPR_TIMECARD) {
                    $db_cols[] = 'timecard';
                    $db_vals[] = (int)$timecard_m;
                }
                $query = 'INSERT INTO '.DB_PREFIX.'roles
                                      ('.implode(',', $db_cols).')
                               VALUES ('.implode(',', $db_vals).')';
                $query = strip_tags($query);
                $result = db_query($query) or db_die();
                $output .= strip_tags($title).': '.__('The role has been created').".<br class='clear' />\n";
            }

            // modify
            if ($anlegen == "aendern") {
                if (!$title) die(__('Please insert a name')."!");

                $update_cols = '';
                if (PHPR_TODO) {
                    $update_cols .= ", todo=".(int)$todo_m;
                }
                if (PHPR_CALENDAR) {
                    $update_cols .= ", calendar=".(int)$calendar_m;
                }
                if (PHPR_CONTACTS) {
                    $update_cols .= ", contacts=".(int)$contacts_m;
                }
                if (PHPR_FORUM) {
                    $update_cols .= ", forum=".(int)$forum_m;
                }
                if (PHPR_CHAT) {
                    $update_cols .= ", chat=".(int)$chat_m;
                }
                if (PHPR_FILEMANAGER) {
                    $update_cols .= ", filemanager=".(int)$filemanager_m;
                }
                if (PHPR_BOOKMARKS) {
                    $update_cols .= ", bookmarks=".(int)$bookmarks_m;
                }
                if (PHPR_VOTUM) {
                    $update_cols .= ", votum=".(int)$votum_m;
                }
                if (PHPR_QUICKMAIL) {
                    $update_cols .= ", mail=".(int)$mail_m;
                }
                if (PHPR_NOTES) {
                    $update_cols .= ", notes=".(int)$notes_m;
                }
                if (PHPR_RTS) {
                    $update_cols .= ", helpdesk=".(int)$helpdesk_m;
                }
                if (PHPR_PROJECTS) {
                    $update_cols .= ", projects=".(int)$projects_m;
                }
                if (PHPR_TIMECARD) {
                    $update_cols .= ", timecard=".(int)$timecard_m;
                }
                $query = "UPDATE ".DB_PREFIX."roles
                             SET title = '".xss($title)."',
                                 remark = '".xss($remark)."' ".$update_cols."
                           WHERE ID = ".(int)$roles_ID;
                $result = db_query($query) or db_die();
                $output .= strip_tags($title).': '.__('The role has been modified').".<br class='clear' />\n";
            }
        }
    }
}


/**
*
*   actions on helpdesk categories
*
*/
if ($action1 == "accounts") {

    // check form token
    check_csrftoken();

    // delete
    if ($loeschen1) {
        $result = db_query("DELETE
                              FROM ".DB_PREFIX."db_accounts
                             WHERE ID = ".(int)$db_accounts_ID) or db_die();
        $output .= __('Category deleted').". <br class='clear' />\n";
    }

    if ($anlegen) {
        // check for double entries
        if ($anlegen == "aendern") $mod_strg = "WHERE ID <> ".(int)$db_accounts_ID;
        else                       $mod_strg = '';
        $result = db_query("SELECT name
                              FROM ".DB_PREFIX."db_accounts
                              $mod_strg") or db_die();
        while ($row = db_fetch_row($result)) {
            if ($name == $row[0]) {
                $output .= __('This name already exists');
                $error = 1;
            }
        }

        if (!$error) {
            // new
            if ($anlegen == "neu_anlegen") {
                if (!$name) die(__('Please insert a name')."!");
                $account_ID= save_mail_account_basic_fields($account_module,$accountname,$hostname,$type,$username,$password);
                //later this can be extended to other types
                if($account_ID) $account_type='mail';
                else $account_type='';
                $query = "INSERT INTO ".DB_PREFIX."db_accounts
                                          (  name ,       users  ,        gruppe  ,        account_ID  , account_type  ,        escalation  ,  message)
                                   VALUES ('".strip_tags($name)."',".(int)$user.",".(int)$gruppe.",".(int)$account_ID.",'".strip_tags($account_type)."',".(int)$escalation.",'".strip_tags($message)."')";
                $result = db_query($query) or db_die();
                $output .= strip_tags($name).': '.__('The category has been created').".<br class='clear' />";
            }
            // modify
            if ($anlegen == "aendern") {
                if (!$name) die(__('Please insert a name')."!");
                $result = db_query("UPDATE ".DB_PREFIX."db_accounts
                                           SET name = '".strip_tags($name)."',
                                               gruppe = ".(int)$gruppe.",
                                               escalation = ".(int)$escalation.",
                                               message = '".strip_tags($message)."',
                                               users = ".(int)$user."
                                         WHERE ID = ".(int)$db_accounts_ID) or db_die();
                $result = db_query("SELECT account_ID
                                      FROM ".DB_PREFIX."db_accounts
                                     WHERE ID = ".(int)$db_accounts_ID) or db_die();
                $row=db_fetch_row($result);
                $account_ID=$row[0];
                update_mail_account_basic_fields($account_module,$accountname,$hostname,$type,$username,$password,$account_ID);
                $output .= strip_tags($name).": ".__('The category has been modified').".<br class='clear' />";
            }
        }
    }
}/**
*
*   actions on access_rights
*
*/
if ($action1 == "access") {

    // check form token
    check_csrftoken();

    if ($gruppe == $gruppe_send) {
        include_once(LIB_PATH."/access.inc.php");
        $von=slookup('users','ID','kurz',$von);
        $access = assign_acc($acc, $module_ID);
        $query="UPDATE ".DB_PREFIX.$tablename[$acc_module]."
                   SET von = ".(int)$von.",
                       gruppe = ".(int)$gruppe.",
                       acc_write = '$acc_write',
                       $acc_field[$acc_module] = '$access'
                 WHERE ID = ".(int)$module_ID;
        $result = db_query($query) or db_die();
        if($recursive=='on'){
             $query = "UPDATE ".DB_PREFIX.$tablename[$acc_module]."
                          SET von = ".(int)$von.",
                              gruppe = ".(int)$gruppe.",
                              acc_write = '$acc_write',
                              $acc_field[$acc_module] = '$access'
                        WHERE parent = ".(int)$module_ID;
            $result = db_query($query) or db_die();
        }
        $output .= __('The Access Rights have been modified').".<br class='clear' />";
    }
    else $action ="access_rights";

}


/**
*
*   delete bookmarks
*
*/
if ($action == "lesezeichen") {

    // check form token
    check_csrftoken();

    if ($loeschen) {
        if (!$lesezeichen_ID) {
            $output .= __('Please select at least one bookmark')."!\n<br class='clear' />\n<a href='admin.php?".SID."'>".__('back')."</a>\n";
        }
        else {
            for ($i=0; $i < count($lesezeichen_ID); $i++) {
                $result = db_query("SELECT bezeichnung
                                      FROM ".DB_PREFIX."lesezeichen
                                     WHERE ID = ".(int)$lesezeichen_ID[$i]) or db_die();
                while ($row = db_fetch_row($result)) {
                    $output .= strip_tags($row[0]).": ".__('The bookmark is deleted').". <br class='clear' />\n";
                }
                $result = db_query("DELETE
                                      FROM ".DB_PREFIX."lesezeichen
                                     WHERE ID = ".(int)$lesezeichen_ID[$i]) or db_die();
            }
            $output .= __('finished').".<br class='clear' />\n";
        }
    }
}


/**
*
*   groups, second dialog: create, modify, delete group
*
*/
if ($action == "groups") {

    // check form token
    check_csrftoken();

    // create a new group
    if ($neu and $mode <> "anlegen") {
        // extended value for the input field of the short name
        $hidden_fields = array ( "action1"  => "groups",
                                 "mode"     => "anlegen");
        $context_output .= "<form action='admin.php' method='post' name='frm'>\n";
        $context_output .= hidden_fields($hidden_fields)."\n";
        $context_output .= '
            <fieldset>
            <legend>'.__('Group').'</legend>
        ';
        // input fields for groupname, short name and remark
        $context_output .= "<label class='label_block' for='name'>".__('Group name').":</label> <input class='halfsize' type='text' name='name'/><br />\n";
        $context_output .= "<label class='label_block' for='kurz'>".__('Short form').":</label> <input class='halfsize' type='text' name='kurz' maxlength='10' /><br />\n";
        $context_output .= "<label class='label_block' for='bemerkung'>".__('Remark').":</label> <input class='halfsize' type='text' name='bemerkung'/><br />\n";
        // if you create a new group, you can't assign a chief right now: yet no users in the group!
        $context_output.=get_go_button('button','button','neu',__('OK'));
       // $output .= "&nbsp;<input type='submit'  class='button' name='neu' value='".__('OK')."' />\n";
        $context_output .= "</fieldset></form>\n<br />\n";
    }

    // modify existing group
    else if ($aendern and $mode <> "anlegen") {
        $result = db_query("SELECT ID,name,kurz,kategorie,bemerkung,chef,div1,div2
                              FROM ".DB_PREFIX."gruppen
                             WHERE ID = ".(int)$group_nr) or db_die();
        $row = db_fetch_row($result);
        $hidden_fields = array ( "action1"  => "groups",
                                 "mode"     => "anlegen",
                                 "group_nr" => $group_nr);
        $context_output .= "<form action='admin.php' method='post'>\n";
        $context_output .= hidden_fields($hidden_fields)."\n";
        $context_output .= '
            <fieldset>
            <legend>'.__('Group').'</legend>
        ';
        // name of the group
        $context_output .= "<label class='label_block' for='name'>".__('Group name').":</label> <input class='halfsize' type='text' name='name' value='".$row[1]."' /><br />\n";
        // short name can't be changed after creation -> only display
        $context_output .= "<label class='label_block' for='kurz'>".__('Short form').":</label> ".$row[2]."<br />\n";
        $context_output .= "<label class='label_block' for='bemerkung'>".__('Remark').":</label> <input class='halfsize' type='text' name='bemerkung' value='".$row[4]."'/><br />\n";
        // select chef
        $context_output .= "<label class='label_block' for='chef'>".__('Leader').":</label><select class='halfsize' name='chef'><option value='0'></option>\n";
        $result2 = db_query("SELECT user_ID
                               FROM ".DB_PREFIX."grup_user
                              WHERE grup_ID = ".(int)$group_nr) or db_die();
        while ($row2 = db_fetch_row($result2)) {
            // fetch name from table users
            $result3 = db_query("SELECT vorname, nachname
                                   FROM ".DB_PREFIX."users
                                  WHERE ID = ".(int)$row2[0]) or db_die();
            $row3 = db_fetch_row($result3);
            $context_output .= "<option value='$row2[0]'";
            if ($row2[0] == $row[5]) $context_output .= ' selected="selected"';
            $context_output .= "> $row3[1], $row3[0]</option>\n";
        }
        $context_output .= "</select><br />\n";
        $context_output .= get_go_button('button','button','aendern',__('OK'))."\n";
        $context_output .= "</fieldset></form>\n<br />\n";
    }

    // confirm delete record, choose group to merge
    else if ($loeschen) {
        // delete default group forbidden
        $context_output .= "<form action='admin.php' method='post'><fieldset><legend>".__('Delete group')."</legend>\n";
        $context_output .= "<br /><br />\n";
        $context_output .= "<label for='merge_target'>".__('Delete group and merge contents with group').":</label><br />\n";
        $hidden_fields = array ( "action1"      => "groups",
                                 "group_nr"     => $group_nr);
        $context_output .= hidden_fields($hidden_fields)."\n";
        $context_output .= "<select name='merge_target'>\n";
        // show all groups except the chosen
        $result = db_query("SELECT ID, name
                              FROM ".DB_PREFIX."gruppen
                             WHERE ID <> ".(int)$group_nr."
                          ORDER BY name") or db_die();
        while ($row = db_fetch_row($result)) {
            $context_output .= "<option value='$row[0]'>$row[1]</option>\n";
        }
        $context_output .= "</select>\n";
        $context_output .=get_go_button('button','button','loeschen1',__('OK'))."\n";
        $context_output .= "</fieldset></form>\n<br />\n";
    }
}


/**
*
*   timecard
*
*/
if ($action == "timecard") {

    // check form token
    check_csrftoken();

    // modification
    if ($mode == "change") {
        if (!ereg("(^[0-9]*$)",$day) or !ereg("(^[0-9]*$)",$time)) {
            die("<br /><b>".__('Please check your date and time format! ')."<br /><a href='admin.php?".SID."'>".__('back')."</a> ...</b>");
        }
        if (strlen($time) == 3) {
            $time = "0".$time;   // Zeitangabe nur 3-stellig?
        }
        if (!checkdate($month,$day,$year)) {
            die("<br /><b>".__('Please check the date!')." <br /><a href='admin.php?".SID."'>".__('back')."</a> ...</b>");
        }
        if (strlen($month) == 1) {
            $month = "0".$month;   // Zeitangabe nur 3-stellig?
        }
        $datum = $year."-".$month."-".$day;
        $result = db_query("SELECT anfang,ende
                              FROM ".DB_PREFIX."timecard
                             WHERE users = ".(int)$pers_timecard." AND
                                   datum = '$datum'") or db_die();
        $row = db_fetch_row($result);
        if ($type == "ende" and $time <= $row[0]) {
            $output .= __('There is no open record with a begin time on this day!');
            $error = 1;
        }
        if ($type == "anfang" and $row[1]<>'' and $time >= $row[1]) {
            $output .= __('There is no open record with a begin time on this day!');
            $error = 1;
        }
        if (!$error) {
            $result = db_query("SELECT ID
                                      FROM ".DB_PREFIX."timecard
                                 WHERE users = ".(int)$pers_timecard." AND
                                       datum = '$datum'") or db_die();
            $row = db_fetch_row($result);
            if (!$row[0]) {
                $result = db_query("INSERT INTO ".DB_PREFIX."timecard
                                                    (        users ,  datum )
                                         VALUES (".(int)$pers_timecard.",'$datum')") or db_die();
            }
            $result = db_query("UPDATE ".DB_PREFIX."timecard
                                      SET ".qss($type)." = '".strip_tags($time)."'
                                    WHERE datum = '".strip_tags($datum)."'
                                      AND users = ".(int)$pers_timecard) or db_die();
        }
    }

    // form
    $result = db_query("SELECT nachname, vorname
                          FROM ".DB_PREFIX."users
                         WHERE ID = ".(int)$pers_timecard) or db_die();
    $row = db_fetch_row($result);
    $context_output .= "<form action='admin.php' method='post'><fieldset><legend>".__('Timecard')."</legend>\n";
    $context_output .= "<h2>".__('User').": $row[0], $row[1]</h2>\n";
    $hidden_fields = array( "action1"   => "timecard",
                            "action"    => "timecard",
                            "pers_timecard"      => $pers_timecard);
    $context_output .= hidden_fields($hidden_fields)."\n";
    $context_output .= "<label class='label_block' for='month'>".__('Month').":</label> <select name='month'>\n";
    // box for month and year
    for ($a=1; $a<13; $a++) {
        $mo = date("n", mktime(0,0,0, $a, 1, $year));
        $name_of_month = $name_month[$mo];
        if ($mo == $month) {
            $context_output .= "<option value='$a' selected='selected'>$name_of_month</option>\n";
        }
        else {
            $context_output .= "<option value='$a'>$name_of_month</option>\n";
        }
    }
    $context_output .= "</select> \n";
    $y = date("Y");
    $context_output .= "<select name='year'>\n";
    for ($i=$y-2; $i<=$y+5; $i++) {
        if ( $i == $year) {
            $context_output .= "<option selected='selected'>$i\n";
        }
        else {
            $context_output .="<option>$i</option>\n";
        }
    }
    $context_output .= "</select> ".get_go_button()."\n";
    $context_output .= "</fieldset></form>\n<br />\n";

    // Modification form
    $context_output .= "<form action='admin.php' method='post'><fieldset><legend>".__('Timecard')."</legend>\n";
    $hidden_fields = array( "action1"   => "timecard",
                            "action"    => "timecard",
                            "mode"      => "change",
                            "pers_timecard"      => $pers_timecard,
                            "month"     => $month,
                            "year"      => $year);
    $context_output .= hidden_fields($hidden_fields)."\n";
    $context_output .= "<label class='label_block' for='day'>".__('Day').":</label><input type='text' name='day' maxlength='2' value='01' /><br />\n";
    $context_output .= "<label class='label_block' for='time'>".__('Time').":</label><input type='text' name='time' maxlength='4' value='0800' />\n";
    $context_output .= "<select name='type'><br />\n";
    $context_output .= "<option value='anfang'>".__('Begin')."</option>\n<option value='ende'>".__('End')."</option>\n</select><br /><br />\n";
    $context_output .= get_go_button()."</fieldset></form><br />\n";
    // list of records
    $context_output .='<table><thead><tr>';
    $context_output .= "<th width='60'><b>".__('Day')."</b></th><th width='60'><b>".__('Begin')."</b></th><th width='60'><b>".__('End')."</b></th><th><b>".__('Hours')."</b></th>\n";
    $context_output .= "</tr></thead><tbody>\n";

    if (strlen($month) == 1) $month = "0".$month;
    $result = db_query("SELECT ID, users, datum, projekt, anfang, ende
                              FROM ".DB_PREFIX."timecard
                             WHERE users = ".(int)$pers_timecard."
                               AND datum LIKE '%-".(int)$month."-%'
                               AND datum LIKE '".(int)$year."-%'
                          ORDER BY datum DESC") or db_die();
       $datum_alt='';
    while ($row = db_fetch_row($result)) {
        if (($i/2) == round($i/2)) {
            $odd_even = 'odd';$i++;
        }
        else {
            $odd_even = 'even';$i++;
        }   //Farben abwechselnd

        $datum2 = explode("-", $row[2]);
        //Gesamtzeit pro Tag nur einmal berechnen
        if($row[2]!=$datum_alt){

        	$result2 = db_query("SELECT anfang, ende
                                   FROM ".DB_PREFIX."timecard
                         WHERE users = ".(int)$pers_timecard." AND
                               datum='$row[2]'
                               ORDER BY datum DESC") or db_die();
        	$bsum=0;
        	while ($row2 = db_fetch_row($result2)) {
        		$row2[0] = check_4d($row2[0]);
        		$row2[1] = check_4d($row2[1]);
        		//reset h and m for every loop
        		$h=0;
        		$m=0;
        		if($row2[0]&&$row2[1]){
        			//Nettozeit der einzelnen Buchung
        			$bsum+=(substr($row2[1],0,2) - substr($row2[0],0,2))*60 + substr($row2[1],2,4) - substr($row2[0],2,4);

        		}
        	}
        	$h= floor($bsum/60);
        	$m = $bsum - $h * 60;
        	$out_sum="$h h $m m";
        }
        else $out_sum="";
        $datum_alt=$row[2];
        $context_output .= "<tr class='$odd_even'><td>$datum2[2].$datum2[1].$datum2[0]</td>\n";
        $context_output .= "<td>$row[4]</td><td>$row[5]</td><td>$out_sum</td></tr>\n";
    }
    $context_output .= "</tbody></table>\n<br />\n";
    // csv export
    $context_output .= "<a href='../misc/export.php?medium=csv&file=timecard_admin&amp;pers_ID=$pers_timecard&amp;month=$month&amp;year=$year".$sid."'>".__('Export as csv file')."</a>\n";
    $context_output .= "| <a href='../misc/export.php?medium=xls&file=timecard_admin&amp;pers_ID=$pers_timecard&amp;month=$month&amp;year=$year".$sid."'>XLS ".__('export')."</a>\n";
    $context_output .= "<br /><br />\n";
}



/**
*
*   logging
*
*/
if ($action == "logs") {

    // check form token
    check_csrftoken();

    if (strlen($month) == 1) $month = "0".$month;
    $timestring = $year.$month;
    $context_output .= "<br /><table class='relations' style='width:300px'><caption>".__('Logging')." ".__('Month').": $month/$year</caption><thead><tr><td>";
    // column titles: login, logout, duration h:m
    $context_output .= __('Login')."</td><td>".__('Logout')."</td><td>h:m</td></tr></thead><tbody>\n";
    $result = db_query("SELECT login, logout
                          FROM ".DB_PREFIX."logs
                         WHERE von = ".(int)$pers_logs."
                           AND login LIKE '".xss($timestring)."%'
                      ORDER BY login DESC") or db_die();
    while ($row = db_fetch_row($result)) {
        $diff   = 0;
        $diff_h = 0;
        $diff_m = 0;
        $day0   = substr($row[0],6,2).".".$month;
        $h0     = substr($row[0],8,2);
        $m0     = substr($row[0],10,2);
        // only show values if the user has logged out -> means: a value exists
        if ($row[1]) {
            $day1 = substr($row[1],6,2).".".$month;
            $h1   = substr($row[1],8,2);
            $m1   = substr($row[1],10,2);
            $diff =($h1-$h0)*60 + $m1 - $m0;
            // look whether logout is on the next day . if yes, add theminutes of a day
            if (substr($row[1],0,8) > substr($row[0],0,8)) $diff += 1440;
            $diff_h = floor($diff/60);
            $diff_m = $diff - $diff_h*60;
            if (strlen($diff_h) == 1) $diff_h = "0".$diff_h;
            if (strlen($diff_m) == 1) $diff_m = "0".$diff_m;
        }
        else {
            $day1   = "--";
            $h1     = "--";
            $m1     = "--";
            $diff_h = "--";
            $diff_m = "--";
        }
        if (($i/2) == round($i/2)) {
            $odd_even = 'odd';
            $i++;
        }
        else {
            $odd_even = 'even';
            $i++;
        }   // Farben abwechselnd
        $context_output .= "<tr class='$odd_even'><td>$day0 $h0.$m0 </td><td>$day1 $h1.$m1 </td><td>$diff_h : $diff_m</td> </tr>\n";
    }
    $context_output .= "</tbody></table>\n<br />\n";
}


/**
*
*   user admistration
*
*/
if ($action == "user") {

    // check form token
    check_csrftoken();

    //
    // new user
    //
    if ($neu) {
        if (!PHPR_LDAP or
            (PHPR_LDAP and $ldap_profile != "off" and $ldap_profile and
             $ldap_conf[$ldap_profile]["ldap_sync"] == 1) or
            (PHPR_LDAP and $ldap_profile == "off")) {
            // extended value for the input field of the short name

            // begin form and check whether the short name has a blank in the string
            $context_output .= "<form action='admin.php' method='post' name='frm' onsubmit='return chkEqualFields(\"frm\", \"pw\", \"pw_confirm\", \"The password and confirmation are different\")'>\n";
            $context_output .= '
                <fieldset>
                    <legend>'.__('Access Rights').'</legend>';
            $hidden_fields = array( "neu"   => $neu);
            $context_output .= hidden_fields($hidden_fields)."\n";
            $context_output .= "<label class='label_block' for='anrede'>".__('Salutation').":</label> <input class='halfsize' type='text' name='anrede' maxlength='15' />\n<br />\n";
            $context_output .= "<label class='label_block' for='vorname'>".__('First Name').": </label><input class='halfsize' type='text' name='vorname' maxlength='40' /><br />\n";
            $context_output .= "<label class='label_block' for='nachname'>".__('Family Name')."(*):</label> <input class='halfsize' type='text' name='nachname' maxlength='40' /><br />\n";
            $context_output .= "<label class='label_block' for='kurz'>".__('Short Form')."(*): </label><input class='halfsize' type='text' name='kurz' onblur=\"chkChrs('frm','kurz','Alphanumerics only, please!',/[a-zA-Z0-9_]+/,1)\" /><br />\n";
            $context_output .= "<label class='label_block' for='loginname'>".__('Login name')."(*):</label> <input class='halfsize' type='text' name='loginname'/><br />\n";
            $context_output .= "<label class='label_block' for='password'>".__('Password')."(*):</label> <input class='halfsize' type='password' name='pw' maxlength='40' /><br />\n";
            $context_output .= "<label class='label_block' for='password'>".__('Confirm password')."(*):</label> <input class='halfsize' type='password' name='pw_confirm' maxlength='40' /><br />\n";

            // user type
            $context_output .= "<label class='label_block' for='usertype'>".__('Type').": </label>\n";
            $context_output .= "<select class='halfsize' name='usertype'>\n";
            foreach ($user_types as $user_types1 => $user_types2) {
                $context_output .= "<option value='$user_types1'>$user_types2</option>\n";
            }
            $context_output .= "</select><br />\n";

            // user status
            $context_output .= "<label class='label_block' for='status'>".__('Status').": </label>\n";
            $context_output .= "<select class='halfsize' name='status'>\n";
            foreach ($user_status as $user_status1 => $user_status2) {
                $context_output .= "<option value='$user_status1'>$user_status2</option>\n";
            }
            $context_output .= "</select>\n<br />\n";

            // assign to groups, only allowed to root
            if ($user_ID == 1) {
                // member in the following groups:
                $context_output .= "<label class='label_block' for='grup_user'>".__('Member of following groups').":</label><select class='halfsize' name='grup_user[]' id='grup_user' multiple='multiple' onchange='copy_values(\"grup_user\",\"preferred_group\",0)'>\n";
                $result2 = db_query("SELECT ID, name
                                       FROM ".DB_PREFIX."gruppen
                                   ORDER BY name") or db_die();
                while ($row2 = db_fetch_row($result2)) {
                    $context_output .= "<option value='$row2[0]'>$row2[1]</option>\n";
                }
                $context_output .= "</select><br />\n";

                                // default group
                $context_output .= "<label class='label_block' for='preferred_group'>".__('Preferred Startup Group').": </label><select name='preferred_group' id='preferred_group'>\n";
                $context_output .= "</select><br />\n";
            }

            $context_output .= "<label class='label_block' for='password'>".__('Company').":</label> <input class='halfsize' type='text' name='firma' maxlength='30' /><br />\n";
            $context_output .= "<label class='label_block' for='email'>Email:</label><input class='halfsize' type='text' name='email' maxlength='50' /><br />\n";
            // various fields, see field name
            $context_output .= "<label class='label_block' for='tel1'>".__('Phone')." 1:</label> <input class='halfsize' type=text name=tel1 maxlength=20 /><br />\n";
            $context_output .= "<label class='label_block' for='tel2'>".__('Phone')." 2:</label> <input class='halfsize' type=text name=tel2 maxlength=20 /><br />\n";
            $context_output .= "<label class='label_block' for='mobil'>".__('Phone')." ".__('mobile').":</label> <input class='halfsize' type=text name=mobil maxlength=30 /><br />\n";
            $context_output .= "<label class='label_block' for='fax'>".__('Fax').": </label><input class='halfsize' type=text name=fax maxlength=20 /><br />\n";
            $context_output .= "<label class='label_block' for='sms'>SMS:</label> <input class='halfsize' type=text name=sms maxlength=60 /><br />\n";
            $context_output .= "<label class='label_block' for='strasse'>".__('Street').":</label> <input class='halfsize' type=text name=strasse maxlength=30 /><br />\n";
            $context_output .= "<label class='label_block' for='stadt'>".__('City').":</label> <input class='halfsize' type=text name=stadt maxlength=30 /><br />\n";
            $context_output .= "<label class='label_block' for='plz'>".__('Zip code').": </label><input class='halfsize' type=text name=plz maxlength=10 /><br />\n";
            $context_output .= "<label class='label_block' for='land'>".__('Country').": </label><input class='halfsize' type=text name=land maxlength=20 /><br />\n";
            $context_output .= "<label class='label_block' for='hrate'>".__('Hourly rate').":</label> <input class='halfsize' type=text name=hrate maxlength=20 /><br />\n";

            // ldap name
            if (PHPR_LDAP) {
                $context_output .= "<input type='hidden' name='ldap_profile' value='".(($ldap_profile == "off") ? "" : $ldap_profile) ."' />\n";
            }

            // language, list of available languages in the header of this script as an array!
            $context_output .= "<label class='label_block' for='sprache'>".__('Language').":</label> <select class='halfsize' name='sprache'>\n";
            $context_output .= "<option value=''></option>\n";
            foreach ($languages as $l_short => $l_long) {
                $context_output .= "<option value='$l_short'>$l_long</option>\n";
            }
            $context_output .= "</select><br />\n";

            // Role
            $context_output .= "<label class='label_block' for='role'>".__('Role').":  </label><select class='halfsize' name='role'><option value='0'></option>\n";
            $result2 = db_query("SELECT ID, title
                                   FROM ".DB_PREFIX."roles
                               ORDER BY title") or db_die();
            while ($row2 = db_fetch_row($result2)) {
                $context_output .= "<option value='$row2[0]'>$row2[1]</option>\n";
            }
            $context_output .= "</select><br />\n";
            $context_output .= "<label class='label_block' for='remark'>".__('Remark').":</label> <textarea class='halfsize' name=remark></textarea><br />\n";
            $context_output .= "<br />\n";
            $context_output .= "<input type='hidden' name='action1' value='user' />\n";
            $context_output .= "<input type='hidden' name='anlegen' value='neu_anlegen' />\n";
            $context_output .= get_go_button('button','button','',__('OK'))."\n";
            $context_output .= "</fieldset></form>\n* ".__('these fields have to be filled in.')."<br /><br />\n";
        }
        else {
            if (!$ldap_profile) {
                $context_output .= "\n";
                $context_output .= "<form action='admin.php' method='post'>\n";
                $context_output .= "<fieldset><legend>LDAP</legend><label class='label_block' for='ldap_profile'>LDAP-Profile</label><select name='ldap_profile'>\n";
                for ($i = 0; $ldap_conf[++$i]["conf_name"] != ""; ) {
                    $context_output .= "<option value='$i'>".$ldap_conf[$i]['conf_name']."</option>\n";
                }
                $context_output .= "<option value='off'>LDAP off</option>\n";
                $context_output .= "</select>\n<br />";
                $hidden_fields = array( "action1"   => "user");
                $context_output .= hidden_fields($hidden_fields)."\n";
                $context_output .=get_go_button('button','button','neu',__('Proceed'))."\n";
                $context_output .= "</fieldset></form>\n";
            }
            else {
                // extended value for the input field of the short name
                $context_output .= "<form action='admin.php' method='post'><fieldset><legend>".__('Create')."</legend>\n";
                $context_output .= hidden_fields('')."\n";
                if (!$ldap_conf[$ldap_profile][1]) $context_output .= "<label class='label_block' for='vorname'>".__('First Name').":</label> <input type='text' name='vorname' maxlength='40' /><br />\n";
                if (!$ldap_conf[$ldap_profile][2]) $context_output .= "<label class='label_block' for='nachname'>".__('Family Name')."(*):</label> <input type='text' name='nachname' maxlength='40' /><br />\n";
                if (!$ldap_conf[$ldap_profile][3]) $context_output .= "<label class='label_block' for='kurz'>".__('Short Form')."(*):</label> <input type='text' name='kurz' /><br />\n";
                $context_output .= "<label class='label_block' for='loginname'>".__('Login name')."(*):</label> <input type=text name='loginname' />\n<br />\n";

                // only allowed to root
                if ($user_ID == 1) {
                    // member in the following groups:
                    $context_output .= "<label class='label_block' for='grup_user'>".__('Member of following groups').":</label><select class='halfsize' name='grup_user[]' id='grup_user' multiple='multiple' onchange='copy_values(\"grup_user\",\"preferred_group\",0)'>\n";
                    $result2 = db_query("SELECT ID, name
                                           FROM ".DB_PREFIX."gruppen
                                       ORDER BY name") or db_die();
                    while ($row2 = db_fetch_row($result2)) {
                        $context_output .= "<option value='$row2[0]'>$row2[1]</option>\n";
                    }
                    $context_output .= "</select><br />\n";

                    // default group
                    $context_output .= "<label class='label_block' for='preferred_group'>".__('Preferred Startup Group').": </label><select name='preferred_group' id='preferred_group'>\n";
                    $context_output .= "</select><br />\n";
                }
                if (!$ldap_conf[$ldap_profile][5]) $context_output .= "<label class='label_block' for='bemerkung'>".__('Company').":</label><input type=text name=firma maxlength=30 /><br /><br />\n";
                if (!$ldap_conf[$ldap_profile][7]) $context_output .= "<label class='label_block' for='email'>Email:</label><input type=text name=email maxlength=50 /><br />\n";
                if (!$ldap_conf[$ldap_profile][9])  $context_output .= "<label class='label_block' for=''>".__('Phone')." 1:</label> <input type=text name=tel1 maxlength=20 /><br />\n";
                if (!$ldap_conf[$ldap_profile][10]) $context_output .= "<label class='label_block' for=''>".__('Phone')." 2:</label> <input type=text name=tel2 maxlength=20 /><br />\n";
                if (!$ldap_conf[$ldap_profile][17]) $context_output .= "<label class='label_block' for=''>".__('Phone')." ".__('mobile').":</label> <input type=text name=mobil maxlength=30 /><br />\n";
                if (!$ldap_conf[$ldap_profile][11]) $context_output .= "<label class='label_block' for=''>".__('Fax').":</label> <input type=text name=fax maxlength=20 /><br />\n";
                if (!$ldap_conf[$ldap_profile][12]) $context_output .= "<label class='label_block' for=''>".__('Street').":</label> <input type=text name=strasse maxlength=30 /><br />\n";
                if (!$ldap_conf[$ldap_profile][13]) $context_output .= "<label class='label_block' for=''>".__('City').":</label> <input type=text name=stadt maxlength=30 /><br />\n";
                if (!$ldap_conf[$ldap_profile][14]) $context_output .= "<label class='label_block' for=''>".__('Zip code').":</label> <input type=text name=plz maxlength=10 /><br />\n";
                if (!$ldap_conf[$ldap_profile][15]) $context_output .= "<label class='label_block' for=''>".__('Country').":</label> <input type=text name=land maxlength=20 /><br />\n";
                $context_output .= "<input type='hidden' name='ldap_profile' value='$ldap_profile' /><br />\n";
                $context_output .= "<label class='label_block' for=''>".__('Language').":</label> <select name=sprache>\n";
                $context_output .= "<option value=''></option>\n";
                foreach ($languages as $l_short => $l_long) {
                    $context_output .= "<option value='$l_short'>$l_long</option>\n";
                }
                $context_output .= "</select><br />\n";
                $context_output .= "<input type='hidden' name=action1 value=user />\n";
                $context_output .= "<input type='hidden' name='anlegen' value='neu_anlegen' />\n";
                $context_output .= get_go_button('button','button','',__('OK'))."</fieldset></form>\n";
                $context_output .= "<br />* ".__('these fields have to be filled in.')."\n";
            }
        }
    }

    //
    // modify
    //
    if ($aendern) {
        if (!$pers_ID) {
            $output .=__('Please choose a user')." <a href='admin.php?".SID."'>".__('back')."</a>\n";
        }
        else {
            $result = db_query("SELECT ID, ldap_name, kurz, anrede, vorname, nachname, loginname,
                                       pw, gruppe, email, firma, usertype,tel1, tel2, mobil, fax, sms,
                                       strasse, stadt, plz, land, sprache, role, hrate, remark,
                                       status
                                  FROM ".DB_PREFIX."users
                                 WHERE ID = ".(int)$pers_ID) or db_die();
            $row = db_fetch_row($result);

            // for LDAP we depend on row 19, if it is either "" or NULL we will set it to a default value of 1
            if (PHPR_LDAP== 0) $user_ldap_conf = 0;
            else if (!isset($row[1]) or (strlen($row[1]) < 1)) $user_ldap_conf = "1";
            else $user_ldap_conf = $row[19];
            // end ldap mod

            $context_output .= "<form action='admin.php' method='post' name='frm' onsubmit='return chkEqualFields(\"frm\", \"pw\", \"pw_confirm\", \"The password and confirmation are different\")'><fieldset><legend>".__('Users')."</legend>\n";
            $hidden_fields = array( "pers_ID"   => $pers_ID,
                                    "aendern"   => $aendern,
                                    "kurz"      => $row[2]);
            $row[4] = html_entity_decode($row[4],ENT_NOQUOTES);
            $row[5] = html_entity_decode($row[5],ENT_NOQUOTES);
            $context_output .= hidden_fields($hidden_fields)."\n";
            if ((PHPR_LDAP == 0) or ($user_ldap_conf == "off") or ($ldap_conf[$user_ldap_conf]["ldap_sync"] != "2")) {
                $context_output .= "<label class='label_block' for='anrede'>".__('Salutation').":</label> <input type=text name=anrede maxlength=15 value='$row[3]' /><br />\n";
                $context_output .= "<label class='label_block' for='vorname'>".__('First Name').":</label> <input type=text name=vorname maxlength=40 value='$row[4]' /><br />\n";
                $context_output .= "<label class='label_block' for='nachname'>".__('Family Name')."(*):</label> <input type=text name=nachname maxlength=40 value='$row[5]' /><br />\n";
                $context_output .= "<label class='label_block'>".__('Short Form').":</label> $row[2]\n<br class='clear />'";
                $context_output .= "<label class='label_block' for='loginname'>".__('Login name')."(*):</label> <input type='text' name='loginname' value='$row[6]' /><br />\n";
                // password field (with remark: insert a value only if you want to have a new password)
                if (PHPR_LDAP == 0) {
                    $context_output .= "<label class='label_block' for='password'>".__('New password').":</label><input type='password' name='pw' maxlength='40' value='' /><br />\n";
                    $context_output .= "<label class='label_block' for='password_confirm'>".__('Confirm password').":</label><input type='password' name='pw_confirm' maxlength='40' value='' /><br />".__('(keep old password: leave empty)')."<br />\n";
                }
                else {
                    $context_output .= "<input type='hidden' name='pw' value='' />\n";
                    $context_output .= "<input type='hidden' name='pw_confirm' value='' />\n";
                }
            }
            else {
                $context_output .= "<label class='label_block' for='anrede'>".__('Salutation').":</label><input type=text name=anrede maxlength=15 value='$row[3]'".read_o('1')." /><br />\n";
                $context_output .= "<label class='label_block' for='vorname'>".__('First Name').":</label><input type=text name=vorname maxlength=40 value='$row[4]'".read_o('1')." /><br />\n";
                $context_output .= "<label class='label_block' for='nachname'>".__('Family Name')."(*):</label><input type=text name=nachname maxlength=40 value='$row[5]'".read_o('1')." /><br />\n";
                $context_output .= "<label class='label_block'>".__('Short Form').":</label> $row[7]\n";
                $context_output .= "<label class='label_block' for='loginname'>".__('Login name')."(*):</label><input type=text name='loginname' value='$row[6]' /><br />\n";
                $context_output .= "<input type='hidden' name='pw' value='' />\n";
            }

            // user type
            $context_output .= "<label class='label_block' for='usertype'>".__('Type').":</label>\n";
            $context_output .= "<select name='usertype'>\n";
            foreach ($user_types as $user_types1 => $user_types2) {
                $context_output .= "<option value='$user_types1'";
                if ($user_types1 == $row[11]) $context_output .= ' selected="selected"';
                $context_output .= ">$user_types2</option>\n";
            }
            $context_output .= "</select>\n<br />\n";

            // user status
            $context_output .= "<label class='label_block' for='status'>".__('Status').":</label>\n";
            $context_output .= "<select name='status'>\n";
            foreach ($user_status as $user_status1 => $user_status2) {
                $context_output .= "<option value='$user_status1'";
                if ($user_status1 == $row[25]) $context_output .= ' selected="selected"';
                $context_output .= ">$user_status2</option>\n";
            }
            $context_output .= "</select>\n<br class='clear' />\n";

            // define group membership
            // only allowed to root and if chosen user is not root
            if ($user_ID == 1 and $pers_ID > 1) {

               // member in the following groups:
                $context_output .= "<label class='label_block' for='grup_user'>".__('Member of following groups').":</label><select class='halfsize' name='grup_user[]' id='grup_user' multiple='multiple' onchange='copy_values(\"grup_user\",\"preferred_group\",$row[8])'>\n";
              $result2 = db_query("SELECT ID, name
                                     FROM ".DB_PREFIX."gruppen
                                 ORDER BY name") or db_die();
                while ($row2 = db_fetch_row($result2)) {
                    $result3 = db_query("SELECT ID
                                           FROM ".DB_PREFIX."grup_user
                                          WHERE grup_ID = ".(int)$row2[0]."
                                            AND user_ID = ".(int)$pers_ID) or db_die();
                    $row3 = db_fetch_row($result3);
                    $context_output .= "<option value='$row2[0]'";
                    if ($row3[0] > 0) $context_output .= ' selected="selected"';
                    $context_output .= ">$row2[1]</option>\n";
                }
                $context_output .= "</select><br />\n";

                                // default group
                $context_output .= "<label class='label_block' for='preferred_group'>".__('Preferred Startup Group').": </label><select name='preferred_group' id='preferred_group'>\n";
                 $result2 = db_query("SELECT ".DB_PREFIX."gruppen.ID, name
                                        FROM ".DB_PREFIX."gruppen,".DB_PREFIX."grup_user
                                       WHERE ".DB_PREFIX."gruppen.ID = grup_ID
                                         AND user_ID = ".(int)$pers_ID."
                                    ORDER BY name") or db_die();
                while ($row2 = db_fetch_row($result2)) {
                    $context_output .= "<option value='$row2[0]'";
                    if ($row[8] == "$row2[0]") $context_output .= ' selected="selected"';
                    $context_output .= ">$row2[1]</option>\n";
                }
                $context_output .= "</select><br />\n";
            }
            else {
                  // member in the following groups:
                $context_output .= "<label class='label_block' for='grup_user'>".__('Member of following groups').":</label><select class='halfsize' name='grup_user[]' id='grup_user' multiple='multiple' onchange='copy_values(\"grup_user\",\"preferred_group\",$row[8])'>\n";
             $result2 = db_query("SELECT DISTINCT ".DB_PREFIX."gruppen.ID, name, kurz
                                    FROM ".DB_PREFIX."gruppen, ".DB_PREFIX."grup_user
                                   WHERE grup_ID = ".DB_PREFIX."gruppen.ID
                                     AND user_ID = ".(int)$user_ID) or db_die();
                while ($row2 = db_fetch_row($result2)) {
                    $result3 = db_query("SELECT ID
                                           FROM ".DB_PREFIX."grup_user
                                          WHERE grup_ID = ".(int)$row2[0]."
                                            AND user_ID = ".(int)$pers_ID) or db_die();
                    $row3 = db_fetch_row($result3);
                    $context_output .= "<option value='$row2[0]'";
                    if ($row3[0] > 0) $context_output .= ' selected="selected"';
                    $context_output .= ">$row2[1]</option>\n";
                }
                $context_output .= "</select><br />\n";

                                // default group
                $context_output .= "<label class='label_block' for='preferred_group'>".__('Preferred Startup Group').": </label><select name='preferred_group' id='preferred_group'>\n";
                 $result2 = db_query("SELECT ".DB_PREFIX."gruppen.ID, name
                                        FROM ".DB_PREFIX."gruppen,".DB_PREFIX."grup_user
                                       WHERE ".DB_PREFIX."gruppen.ID = grup_ID
                                         AND user_ID = ".(int)$user_ID."
                                    ORDER BY name") or db_die();
                while ($row2 = db_fetch_row($result2)) {
                    $context_output .= "<option value='$row2[0]'";
                    if ($row[8] == "$row2[0]") $context_output .= ' selected="selected"';
                    $context_output .= ">$row2[1]</option>\n";
                }
                $context_output .= "</select><br />\n";
            }

            if ((PHPR_LDAP != 0) and (strcmp($user_ldap_conf, "off") != 0) and ($ldap_conf[$user_ldap_conf]["ldap_sync"] == "2")) {
                $context_output .= "<label class='label_block' for='email'>Email:</label> <input type=text name=email maxlength=50 value='$row[9]' $read_o /><br />\n";
            }
            else {
                $context_output .= "<label class='label_block' for='firma'>".__('Company').":</label> <input type=text name=firma maxlength=30 value='$row[10]' /><br />\n";
                $context_output .= "<label class='label_block' for='label'>Email:</label> <input type=text name=email maxlength=50 value='$row[9]' /><br />\n";
            }
            if ((PHPR_LDAP == 0) or ($user_ldap_conf == "off") or ($ldap_conf[$user_ldap_conf]["ldap_sync"] != "2")) {
                $context_output .= "<label class='label_block' for='tel1'>".__('Phone')." 1:</label>\n";
                $context_output .= "<input type=text name=tel1 maxlength=20 value='$row[12]' /><br />\n";
                $context_output .= "<label class='label_block' for='tel2'>".__('Phone')." 2:</label>\n";
                $context_output .= "<input type=text name=tel2maxlength=20 value='$row[13]' /><br />\n";
                $context_output .= "<label class='label_block' for='mobil'>".__('Phone')." ".__('mobile').":</label>\n";
                $context_output .= "<input type=text name=mobil maxlength=30 value='$row[14]' /><br />\n";
                $context_output .= "<label class='label_block' for='fax'>".__('Fax').": </label>\n";
                $context_output .= "<input type=text name=fax maxlength=20 value='$row[15]' /><br />\n";
            }
            $context_output .= "<label class='label_block' for='sms'>SMS:</label>\n";
            $context_output .= "<input type='text' name='sms' maxlength='60' value='$row[16]' /><br />\n";
            if ((PHPR_LDAP == 0) or ($user_ldap_conf == "off") or ($ldap_conf[$user_ldap_conf]["ldap_sync"] != "2")) {
                $context_output .= "<label class='label_block' for='strasse'>".__('Street').": </label>\n";
                $context_output .= "<input type=text name=strasse maxlength=30 value='$row[17]' /><br />\n";
                $context_output .= "<label class='label_block' for='stadt'>".__('City').":</label>\n";
                $context_output .= "<input type=text name=stadt maxlength=30 value='$row[18]' /><br />\n";
                $context_output .= "<label class='label_block' for='plz'>".__('Zip code').": </label>\n";
                $context_output .= "<input type=text name=plz maxlength=10 value='$row[19]' /><br />\n";
                $context_output .= "<label class='label_block' for='land'>".__('Country').":</label>\n";
                $context_output .= "<input type=text name=land maxlength=20 value='$row[20]' /><br />\n";
                $context_output .= "<label class='label_block' for='hrate'>".__('Hourly rate').":</label>\n";
                $context_output .= "<input type=text name=hrate maxlength=20 value='$row[23]' /><br />\n";
            }

            // language
            $context_output .= "<label class='label_block' for='sprache'>".__('Language').":</label> \n";
            $context_output .= "<select name='sprache'><option value=''></option>\n";
            foreach ($languages as $l_short => $l_long) {
                $context_output .= "<option value='$l_short'";
                if ($row[21] == $l_short) $context_output .= ' selected="selected"';
                $context_output .= ">$l_long</option>\n";
            }
            $context_output .= "</select>\n<br />\n";

            // Role
            $context_output .= "<label class='label_block' for='role'>".__('Role').":</label>\n";
            $context_output .= "<select name='role'>\n<option value='0'></option>\n";
            $result2 = db_query("SELECT ID, title
                                   FROM ".DB_PREFIX."roles
                               ORDER BY title") or db_die();
            while ($row2 = db_fetch_row($result2)) {
                $context_output .= "<option value='$row2[0]'";
                if ($row2[0] == $row[22]) $context_output .= ' selected="selected"';
                $context_output .= ">$row2[1]</option>\n";
            }
            $context_output .= "</select>\n<br />\n";

            // remark
            $context_output .= "<label class='label_block' for='remark'>".__('Remark').":</label>\n";
            $context_output .= "<textarea name='remark'>".stripslashes($row[24])."</textarea><br />\n";

            // ldap name
            if (PHPR_LDAP) {
                $context_output .= "<label class='label_block' for='ldap_name'>".__('ldap name').": </label>\n";
                $context_output .= "<select name='ldap_name'>\n";
                $result2 = db_query("SELECT DISTINCT ldap_name
                                       FROM ".DB_PREFIX."users") or db_die();
                while ($row2 = db_fetch_row($result2)) {
                    if ((strcasecmp($row2[0], "off") != 0) && (strcmp($row2[0], "1") != 0)) {
                        $context_output .= "<option value='$row2[0]'";
                        if ($row2[0] == $row[1]) $context_output .= ' selected="selected"';
                        $context_output .= ">$row2[0]</option>\n";
                    }
                }
                $context_output .= "<option value='1'";
                if (strcasecmp($row[1], "1") == 0) $context_output .= ' selected="selected"';
                $context_output .= ">Default (1)</option>\n"; /* XXX Need to nationalize this */

                $context_output .= "<option value='off'";
                if (strcasecmp($row[1], "off") == 0) $context_output .= ' selected="selected"';
                $context_output .= ">Off</option>\n"; /* XXX Need to nationalize this */
                $context_output .= "</select>\n<br />\n";
            }
            $context_output .= "<br />\n";
            $context_output .= "<input type='hidden' name='action1' value='user' />\n";
            $context_output .= "<input type='hidden' name='anlegen' value='aendern' />\n";
            $context_output .= get_go_button('button','button','modify_user',__('OK'))."\n";
            $context_output .= "<br /><br />\n";
            $context_output .= "* ".__('these fields have to be filled in.')."\n<br />";
            $context_output .= get_go_button('button','button','remove_settings',__('Remove settings only'))."\n";
            $context_output .= "</fieldset></form>\n";
            $context_output .= "<br /><br />\n";
        }
    }
    // db actions modify and create record are moved to the other frame -> see below

    // confirm delete record
    else if ($loeschen) {
        $context_output .= "<form action='admin.php' method='post'><fieldset><legend>".__('delete')."</legend>\n";
        $context_output .= "<h5>".__('Are you sure?')."</h5>\n";
        $hidden_fields = array( "action1"   => "user",
                                "pers_ID"   => $pers_ID);
        $context_output .= hidden_fields($hidden_fields)."\n";
        $context_output .= "<input type='submit' name='loeschen1' value='".__('OK')."' />\n";
        $context_output .= "</fieldset></form>\n";
    }
}

// *********************
// file management, orphan files
else if ($action == "files") {

    // check form token
    check_csrftoken();

    // fetch all users from this group, build array
    $result = db_query("SELECT user_ID
                          FROM ".DB_PREFIX."grup_user
                         WHERE grup_ID = ".(int)$group_ID) or db_die();

    while ($row = db_fetch_row($result)) {
        $user_group_ID[] = $row[0];
    }
    // end fetch all users from the group

    // loop over all files in this group
    $result = db_query("SELECT ID, von, filename, tempname
                          FROM ".DB_PREFIX."dateien
                         WHERE gruppe = ".(int)$group_ID) or db_die();
    while ($row = db_fetch_row($result)) {
        // if 1. owner not listed in array or 2. array is empty (means: no member in group) -> orphan!
        if (($user_group_ID and !in_array($row[1],$user_group_ID)) or !$user_group_ID) {
            if ($delete) {
                $output .= "<label class='admin_label'>".__('Delete').":</label> $row[2]<br class='clear' />\n";
                // unlink the file itself
                unlink(PHPR_FILE_PATH."/".$row[3]);

                // remove the record from the database
                $result2 = db_query("DELETE
                                       FROM ".DB_PREFIX."dateien
                                      WHERE ID = ".(int)$row[0]) or db_die();
            }
            else if ($move) {
                $output .="<label class='admin_label'>". __('Move').":</label> $row[2]<br class='clear' />\n";
                $result2 = db_query("UPDATE ".DB_PREFIX."dateien
                                        SET von = ".(int)$pers_ID."
                                      WHERE ID = ".(int)$row[0]) or db_die();
            }
        }
    }
    $output .= __('finished')."<br class='clear' />\n";
}

// roles
else if ($action == "roles" && PHPR_ROLES) {

    // check form token
    check_csrftoken();

    // new record
    if ($neu) {
        $context_output .= "<form action='admin.php' method='post'><fieldset><legend>".__('Roles')."</legend>\n";
        $hidden_fields = array( "action1"   => "roles");
        $context_output .= hidden_fields($hidden_fields)."\n";
        // title of the role
        $context_output .= "<label class='label_block' for='title'>".__('Name').":</label><input type=text name=title maxlength=30 /><br />\n";
        // remark
        $context_output .= "<label class='label_block' for='remark'>".__('Comment').":</label><textarea name=remark></textarea><br />\n";

        // loop over all modules
        if ($summary)         $context_output .= "<label class='label_block' for='summary'>".__('Summary')."</label>".role1("summary")."<br />\n";
        if (PHPR_CALENDAR)    $context_output .= "<label class='label_block' for='calendar'>".__('Calendar')."</label>".role1("calendar")."<br />\n";
        if (PHPR_CONTACTS)    $context_output .= "<label class='label_block' for='contacts'>".__('Contacts')."</label>".role1("contacts")."<br />\n";
        if (PHPR_CHAT)        $context_output .= "<label class='label_block' for='chat'>".__('Chat')."</label>".role1("chat")."<br />\n";
        if (PHPR_FORUM)       $context_output .= "<label class='label_block' for='forum'>".__('Forum')."</label>".role1("forum")."<br />\n";
        if (PHPR_FILEMANAGER) $context_output .= "<label class='label_block' for='filemanager'>".__('Files')."</label>".role1("filemanager")."<br />\n";
        if (PHPR_PROJECTS)    $context_output .= "<label class='label_block' for='projects'>".__('Projects')."</label>".role1("projects")."<br />\n";
        if (PHPR_TIMECARD)    $context_output .= "<label class='label_block' for='timecard'>".__('Timecard')."</label>".role1("timecard")."<br />\n";
        if (PHPR_NOTES)       $context_output .= "<label class='label_block' for='notes'>".__('Notes')."</label>".role1("notes")."<br />\n";
        if (PHPR_RTS)         $context_output .= "<label class='label_block' for='helpdesk'>".__('Helpdesk')."</label>".role1("helpdesk")."<br />\n";
        if (PHPR_QUICKMAIL)   $context_output .= "<label class='label_block' for='mail'>".__('Mail')."</label>".role1("mail")."<br />\n";
        if (PHPR_TODO)        $context_output .= "<label class='label_block' for='todo'>".__('Todo')."</label>".role1("todo")."<br />\n";
        if ($news)            $context_output .= "<label class='label_block' for='news'>".__('News')."</label>".role1("news")."<br />\n";
        if (PHPR_VOTUM)       $context_output .= "<label class='label_block' for='votum'>".__('Voting system')."</label>".role1("votum")."<br />\n";
        if (PHPR_BOOKMARKS)   $context_output .= "<label class='label_block' for='bookmarks'>".__('Bookmarks')."</label>".role1("bookmarks")."<br />\n";
        //if (PHPR_LINKS)       $context_output .= "<label class='label_block' for='links'>Links</label>".role1("links")."<br />\n";

        $context_output .= "<input type='hidden' name='anlegen' value='neu_anlegen' />\n";
        $context_output .= "<input type='submit' class='button' value='".__('OK')."' />\n";
        $context_output .= "</fieldset></form>\n";
    }
    // modify
    if ($aendern) {
        $result = db_query("SELECT ID, von, title, remark, summary, calendar, contacts,
                                   forum, chat, filemanager, bookmarks, votum, mail,
                                   notes, helpdesk, projects, timecard, todo, news
                              FROM ".DB_PREFIX."roles
                             WHERE ID = ".(int)$roles_ID) or db_die();
        $row = db_fetch_row($result);
        $context_output .= "<form action='admin.php' method='post'><fieldset style='width:400px'><legend>".__('Roles')."</legend>\n";
        $hidden_fields = array( "action1"   => "roles",
                                "anlegen"   => "aendern",
                                "roles_ID"  => $roles_ID);
        $context_output .= hidden_fields($hidden_fields)."\n";
        $context_output .= "<label class='label_block' for='title'>".__('Name').":</label><input type=text name='title' value='".html_out($row[2])."' maxlength=40 /><br />\n";
        // remark
        $context_output .= "<label class='label_block' for='remark'>".__('Comment').":</label><textarea name=remark style='width:240px'>".html_out($row[3])."</textarea><br />\n";

        // loop over all modules
        if ($summary)         $context_output .= "<label class='label_block' for='summary'>".__('Summary')."</label>".role1("summary")."<br />\n";
        if (PHPR_CALENDAR)    $context_output .= "<label class='label_block' for='calendar'>".__('Calendar')."</label>".role1("calendar")."<br />\n";
        if (PHPR_CONTACTS)    $context_output .= "<label class='label_block' for='contacts'>".__('Contacts')."</label>".role1("contacts")."<br />\n";
        if (PHPR_CHAT)        $context_output .= "<label class='label_block' for='chat'>".__('Chat')."</label>".role1("chat")."<br />\n";
        if (PHPR_FORUM)       $context_output .= "<label class='label_block' for='forum'>".__('Forum')."</label>".role1("forum")."<br />\n";
        if (PHPR_FILEMANAGER) $context_output .= "<label class='label_block' for=''>".__('Files')."</label>".role1("filemanager")."<br />\n";
        if (PHPR_PROJECTS)    $context_output .= "<label class='label_block' for='projects'>".__('Projects')."</label>".role1("projects")."<br />\n";
        if (PHPR_TIMECARD)    $context_output .= "<label class='label_block' for='timecard'>".__('Timecard')."</label>".role1("timecard")."<br />\n";
        if (PHPR_NOTES)       $context_output .= "<label class='label_block' for='notes'>".__('Notes')."</label>".role1("notes")."<br />\n";
        if (PHPR_RTS)         $context_output .= "<label class='label_block' for='helpdesk'>".__('Helpdesk')."</label>".role1("helpdesk")."<br />\n";
        if (PHPR_QUICKMAIL)   $context_output .= "<label class='label_block' for='mail'>".__('Mail')."</label>".role1("mail")."<br />\n";
        if (PHPR_TODO)        $context_output .= "<label class='label_block' for='todo'>".__('Todo')."</label>".role1("todo")."<br />\n";
        if ($news)            $context_output .= "<label class='label_block' for='news'>".__('News')."</label>".role1("news")."<br />\n";
        if (PHPR_VOTUM)       $context_output .= "<label class='label_block' for='votum'>".__('Voting system')."</label>".role1("votum")."<br />\n";
        if (PHPR_BOOKMARKS)   $context_output .= "<label class='label_block' for='bookmarks'>".__('Bookmarks')."</label>".role1("bookmarks")."<br />\n";
        //if (PHPR_LINKS)       $context_output .= "<label class='label_block' for='links'>Links</label>".role1("links")."<br />\n";

        $context_output .= "</select>\n<br />\n";
        $context_output .= "<input class='button' type='submit' value='".__('OK')."' />\n";
        $context_output .= "</fieldset></form>\n";
    }
    // confirm delete record
    else if ($loeschen) {
        $context_output .= "<form action='admin.php' method='post'><fieldset><legend>".__('Delete role')."</legend>\n";
        $context_output .= "<h2>".__('Are you sure?')."</h2><br />\n";
        $hidden_fields = array( "action1"   => "roles",
                                "roles_ID"  => $roles_ID);
        $context_output .= hidden_fields($hidden_fields)."\n";
        $context_output .= "<input type='submit' name='loeschen1' value='".__('OK')."' />\n";
        $context_output .= "</fieldset></form>\n<br />\n";
    }
}

// *********************
// helpdesk create categories
else if ($action == "accounts") {

    // check form token
    check_csrftoken();

    // new record
    if ($neu) {
        $context_output .= "<form action='admin.php' method='post'><fieldset><legend>".__(Accounts)."</legend>\n";
        $hidden_fields = array( "action1"   => "accounts");
        $context_output .= hidden_fields($hidden_fields)."\n";
        $context_output .= "<label class='label_block' for='name'>".__('Name').":</label><input type='text' name='name' maxlength='30' /><br /><br />\n";
        $context_output .= "<label class='label_block' for='account_module'>".__('Module').":</label><select name='account_module'>";
        foreach ($mod_arr as $mod) {
            $context_output .= "<option value='$mod[0]'>".__($mod[0])."</option>\n";
        }
        $context_output .= "</select><br />\n";
        // fields for email account
        $context_output .="<br />";
        $context_output .="<h2>".__('associated email account:')."</h2>";
        $context_output.= get_mail_account_basic_fields($port);
        $context_output .="<hr />";
        // assign the category to a group
        $context_output .= "<br /><label class='label_block' for='gruppe'>".__('Automatic assign to group:')."</label>\n";
        $context_output .= "<select name='gruppe'><option value=''></option>\n";
        $result = db_query("SELECT ID, name
                              FROM ".DB_PREFIX."gruppen
                          ORDER BY name") or db_die();
        while ($row = db_fetch_row($result)) {
            $context_output .= "<option value='$row[0]'>$row[1]</option>\n";
        }
        $context_output .= "</select>\n<br /><br />\n";

        // assign the category to an user
        $context_output .= "<label class='label_block' for='user'>".__('Automatic assign to user:').' '.('(After Escalation period has expired)')."</label><select name='user'><option value=''></option>\n";
        $result = db_query("SELECT ".DB_PREFIX."users.ID, nachname, vorname
                              FROM ".DB_PREFIX."users, ".DB_PREFIX."grup_user
                             WHERE ".DB_PREFIX."users.ID = user_ID
                               AND grup_ID = ".(int)$group_ID) or db_die();
        while ($row = db_fetch_row($result)) {
            $context_output .= "<option value='$row[0]'>$row[1], $row[2]</option>\n";
        }
        $context_output .= "</select><br /><br />\n";
        $context_output .= "<label class='label_block' for='escalation'>".(' Escalation period:')."</label><select name='escalation'><option value=''></option>\n";
        for ($i = 0; $i < 14; $i++) {
            $context_output .= "<option value='$i'>$i</option>\n";
        }
        $context_output .= "</select> ".__('days')."<br /> <br />\n";
        $message="Thank you for using our helpdesk.";
        $context_output .= "<label class='label_block' for='message'>".__('Automatic Response').":</label>
        <textarea name='message' cols='65'>".stripslashes($message)."</textarea><br /><br />\n";
        $context_output .= "<input type='hidden' name='anlegen' value='neu_anlegen' />\n";
        $context_output .= "<input type='submit' value='".__('OK')."' />\n";
        $context_output .= "</fieldset></form>\n";
    }
    // modify
    if ($aendern) {
        $result = db_query("SELECT ID,name, users, gruppe, account_ID, escalation, message
                              FROM ".DB_PREFIX."db_accounts
                             WHERE ID = ".(int)$db_accounts_ID) or db_die();
        $row = db_fetch_row($result);
        $result = db_query("SELECT accountname, hostname, type, username, password, module
                              FROM ".DB_PREFIX."global_mail_account
                             WHERE ID = ".(int)$row[4]) or db_die();
        $mail_value = db_fetch_row($result);
        $context_output .= "<form action='admin.php' method='post'><fieldset><legend>".__('Accounts')."</legend>\n";
        $hidden_fields = array( "action1"       => "accounts",
                                "anlegen"       => "aendern",
                                "db_accounts_ID"    => $db_accounts_ID);
        $context_output .= hidden_fields($hidden_fields)."\n";
        $row[1] = html_out($row[1]);
        $context_output .= "<label class='label_block' for='name'>".__('Name').":</label><input type=text name='name' value='$row[1]' maxlength=30 /><br /><br />\n";
        $context_output .= "<label class='label_block' for='account_module'>".__('Module').":</label><select name='account_module'>";
        foreach ($mod_arr as $mod) {
            $context_output .= "<option value='$mod[0]'";
            if ($mod[0]==  $mail_value[5]) $context_output .= ' selected="selected"';
            $context_output .= ">".__($mod[0])."</option>\n";

        }
        $context_output .= "</select><br />\n";
        // fields for email account
        $context_output .="<hr /><br />";
        $context_output .="<h2>".__('associated email account:')."</h2>";
        $form_fields = array();
        $context_output.=get_mail_account_basic_fields($port, $mail_value);
        $context_output .="<hr />";
        // assign the category to a group
        $context_output .= "<br /><label class='label_block' for='gruppe'>".__('Automatic assign to group:')."</label><select name='gruppe'><option value=''></option>\n";
        $result2 = db_query("SELECT ID, name
                               FROM ".DB_PREFIX."gruppen
                           ORDER BY name") or db_die();
        while ($row2 = db_fetch_row($result2)) {
            $context_output .= "<option value='$row2[0]'";
            if ($row2[0] == $row[3]) $context_output .= ' selected="selected"';
            $context_output .= ">$row2[1]</option>\n";
        }
        $context_output .= "</select><br />\n";

        // assign the category to an user
        $context_output .= "<br /><label class='label_block' for='user'>".__('Automatic assign to user:').' '.('(After Escalation period has expired)')."</label>\n";
        $context_output .= "<select name='user'>\n<option value=''></option>\n";
        $result2 = db_query("SELECT ".DB_PREFIX."users.ID, nachname, vorname
                               FROM ".DB_PREFIX."users, ".DB_PREFIX."grup_user
                              WHERE ".DB_PREFIX."users.ID = user_ID
                                AND grup_ID = ".(int)$group_ID) or db_die();
        while ($row2 = db_fetch_row($result2)) {
            $context_output .= "<option value='$row2[0]'";
            if ($row2[0] == $row[2]) $context_output .= ' selected="selected"';
            $context_output .= ">$row2[1], $row2[2]</option>\n";
        }
        $context_output .= "</select><br /><br />\n";
        $context_output .= "<label class='label_block' for='escalation'>".(' Escalation period:')."</label><select name='escalation'><option value=''></option>\n";
        for ($i = 0; $i < 14; $i++) {
            $context_output .= "<option value='$i'";
            if ($i == $row[5]) $context_output .= ' selected="selected"';
            $context_output .= ">$i</option>\n";
        }
        $context_output .= "</select> ".__('days')."<br /><br />\n";
        $context_output .= "<label class='label_block' for='message'>".__('Automatic Response').":</label>
        <textarea name='message'>".stripslashes($row[6])."</textarea><br /><br />\n";

        $context_output .= "<input type='submit' value='".__('OK')."' />\n";
        $context_output .= "</fieldset></form><br />\n";
    }
    // confirm delete record
    else if ($loeschen) {
        $context_output .= "<form action='admin.php' method='post'>\n<fieldset><legend>".__('Delete account')."</legend>";
        $context_output .= "<h2>".__('Are you sure?')."</h2><br />\n";
        $hidden_fields = array( "action1"       => "accounts",
                                "action"        => "accounts",
                                "db_accounts_ID"    => $db_accounts_ID);
        $context_output .= hidden_fields($hidden_fields)."\n";
        $context_output .= "<input type='submit' name='loeschen1' value='".__('OK')."' />\n";
        $context_output .= "</fieldset></form>\n<br />\n";
    }
}

//****************************
// Lesezeichen/bookmarks check for invalid links and delete them
else if ($action == "lesezeichen") {

    // check form token
    check_csrftoken();

    if ($proof) {
        $output .= "<form action='admin.php' method='post'>\n";
        $hidden_fields = array( "action"       => "lesezeichen",
                                "loeschen"     => "loeschen");
        $output .= hidden_fields($hidden_fields)."\n";
        $error = 0;
        $result = db_query("SELECT ID, datum, von, url, bezeichnung, bemerkung, gruppe
                              FROM ".DB_PREFIX."lesezeichen
                             WHERE $sql_group") or db_die();
        while ($row = db_fetch_row($result)) {
            $msg = '';
            // $url = eregi_replace("http://","",$row[3]);
            $url = parse_url($row[3]);
            if (!$url[port]) $url[port] = '80';
            $ok = fsockopen($url[host], $url[port]);
            if (!$ok) {
                $msg = 'No response';
            }
            else {
                fputs($ok, "GET / HTTP/1.0\r\n\r\n");
                $a =  fgets($ok,128);
                fclose($ok);
                if (substr($a,9,1) == 4 or substr($a,9,1) == 5) $msg = substr($a, 0, 50);
            }
            if ($msg) {
                $output .= strip_tags($row[4]).": ".__('The server sent an error message.')."&nbsp;($msg)&nbsp;";
                $output .= "<input type='Checkbox' name='lesezeichen_ID[]' value='".(int)$row[0]."' /> ".__('Delete')."<br class='clear' />\n";
                $error = 1;
            }
        }
        if (SID) $output .= "<input type='hidden' name='".session_name()."' value='".session_id()."' />\n";
        if ($error) $output .= "<input type='image' src='".IMG_PATH."/los.gif' onclick=\"return confirm('".__('Are you sure?')."')\" />\n<br class='clear' />\n";
        else        $output .= __('All Links are valid.').".\n<br class='clear' /><br class='clear' />\n";
        $output .= "</form>\n<br class='clear' />\n";
    }
}

//****************************
// delete forum threads
else if ($action == "forum") {

    // check form token
    check_csrftoken();

    // first case - only old threads
    if ($tage) {
        $treffer = 0;
        $zeit = mktime(0, 0, 0, date("m"), date("d")-$tage, date("Y"));
        $zeit = date("YmdHis", $zeit);
        $result = db_query("SELECT ID
                              FROM ".DB_PREFIX."forum
                             WHERE datum < '$zeit'
                               AND $sql_group") or db_die();
        while ($row = db_fetch_row($result)) {
            $treffer++;
        }
        $result = db_query("DELETE
                              FROM ".DB_PREFIX."forum
                             WHERE datum < '$zeit'
                               AND $sql_group") or db_die();
        $output .= "$treffer ".__('threads older than x days are deleted.').". (x=$tage)<br />\n";
    }
    // second case - specific threads
    else {
        $result = db_query("SELECT ID, titel, gruppe
                              FROM ".DB_PREFIX."forum
                             WHERE ID = ".(int)$ID) or db_die();
        $row = db_fetch_row($result);
        // check permission (except the root who has access to all groups)
        if ($user_group > 0 and $row[2] <> $user_group) die("you are not allowed to do this");

        // check whether such a posting exists
        if (!$row[0]) {
            $output .= "such a posting does not exist!";
            $error = 1;
        }
        // o.k.? -> begin to delete, first the comments
        if (!$error) {
            delete_comments($ID);
            // now delete the posting itself
            $result = db_query("DELETE
                                  FROM ".DB_PREFIX."forum
                                 WHERE parent = ".(int)$ID) or db_die();
            $result = db_query("DELETE
                                  FROM ".DB_PREFIX."forum
                                 WHERE ID = ".(int)$ID) or db_die();
            $output .= strip_tags($row[1])." ".__(' is deleted.')." \n";
        }
    }
}

// *****************************
// delete Chat files
else if ($action == "chat") {

    // check form token
    check_csrftoken();

    if ($mode == "kill") {
        if (empty($user_group)) $user_group = 0;
        $user_group = (int) $user_group;
        $result = db_query("DELETE
                              FROM ".DB_PREFIX."chat
                             WHERE gruppe = ".(int)$user_group."
                                OR gruppe = 0") or db_die();
        $result = db_query("DELETE
                              FROM ".DB_PREFIX."chat_alive
                             WHERE gruppe = ".(int)$user_group."
                                OR gruppe = 0") or db_die();
        $output .= __('All chat entries are removed').". <br class='clear' />\n";
    }
}

// *****************************
// change Access Rights
else if ($action == "access_rights") {

    // check form token
    check_csrftoken();
    $author='';
    $author_ser='';
    $acc_read='group';
    $acc_write='';
    $error=0;
    if(!isset($gruppe)){
        $query="SELECT von, $acc_field[$acc_module],acc_write,gruppe, $titlenames[$acc_module],ID
                  FROM ".DB_PREFIX.$tablename[$acc_module]."
                 WHERE ID = ".(int)$module_ID or db_die();
        $result = db_query($query) or db_die();
        $row_acc = db_fetch_row($result);
        if(!$row_acc[5]){
            $error=1;
            message_stack_in((sprintf(__('There is no entry in module %s with the ID %s'),$acc_module,(int)$module_ID)),'admin','error');
        }
        if($user_group and !array_key_exists($row_acc[3],$_SESSION['user_all_groups']) ){
            $error=1;
            message_stack_in((sprintf(__('You are not allowed to change records from this group'),$acc_module,$module_ID)),'admin','error');
        }
        $author=$row_acc[0];
        $author_ser=serialize(array(0=>slookup('users','kurz','ID',$row_acc[0])));
        $gruppe=$row_acc[3];
        $acc_read=$row_acc[1];
        $acc_write=$row_acc[2];
    }
    if($error!=1){
        $user_group_temp=$user_group;
        $user_group=$gruppe;
        $sql_user_group_temp = $sql_user_group;
        $sql_user_group = "(gruppe = ".(int)$user_group.")";
        $output .= "<form action='admin.php' method='post' onsubmit='return confirm(\"Are you sure you want to change the entry Nr. $module_ID ?\")'>\n";
        $hidden_fields = array( "action1"   => "access","acc_module"=>$acc_module, "module_ID"=>$module_ID, "gruppe_send"=>$gruppe);
        $output .= hidden_fields($hidden_fields)."\n";
        $output .= "<fieldset><legend>".__('Access rights')." / ".__($acc_module)."</legend>\n";
        $output .="<div>".__('Name').": $row_acc[4] (ID $row_acc[5])</div>";
        $output .= "<br /><label class='admin_label' for='gruppe'>".__('Group:')."</label>\n";
        $output .= "<select name='gruppe' onchange='this.form.submit()'><option value=''></option>\n";
        //root can see all groups
        if(!$user_group_temp){
            $result = db_query("SELECT ID, name
                                  FROM ".DB_PREFIX."gruppen
                              ORDER BY name") or db_die();
            while ($row = db_fetch_row($result)) {
                $output .= "<option value='$row[0]'";
                if($gruppe==$row[0])$output .=" selected";
                $output .= ">$row[1]</option>\n";
            }
        }
        else{
            $groups = $_SESSION['user_all_groups'];
            foreach ($groups as $key =>$item) {
                $output .='<option value="'.$key.'"'.
                ($gruppe == $key ? ' selected="selected"' : '').
                ' title="'.$item['name'].'">'.$item['kurz']."</option>";
            }
        }
        $output .= "</select>\n<br class='clear' /><br />\n";
        $output .= "<br /><label class='admin_label' for='von'>".__('Author:')."</label>\n";
        $output .= "<select name='von'>\n";
        $output .= show_group_users($user_group, '',$author_ser, true);
        $output .= "</select><br />\n";

        $output .= access_form2($acc_read,0,$acc_write,0,1);
        $output .= "<br /><label class='admin_label' for='recursive'>".__('Set access rights as well for all subelements').": \n";
        $output .= "<input type='checkbox' name='recursive'> <br /><br />\n";
        //reset usergroup und sql_usergroup
        $user_group=$user_group_temp;
        $sql_user_group=$sql_user_group_temp;
        $output .= "<input type='hidden' name='anlegen' value='update_record' />\n";
        $output .= "<input type='submit' class='button' value='".__('OK')."' />\n";
        $output .= "</fieldset></form>\n";
    }
}

// end code for right frame.

//****************************************
// LEFT FRAME ****************************
//****************************************
$output .= "<div class='admin_left'>\n";
$output .= message_stack_out('admin');
//****************************************
// DIALOG
//****************************************

// no groupID? -> You are superadmin! first dialog: choose a group or work on groups


/**
*
*   start main table
*   check for root
*
*/
if ($user_ID == 1) {
    // work on groups
    // form new group
    $output .= '<form action="admin.php" method="post">';
    $hidden_fields = array( "action"       => "groups");
    $output .= hidden_fields($hidden_fields)."\n";
    // group select
    $group_select = "<select name='group_nr'>\n";
    $result = db_query("SELECT ID, name
                          FROM ".DB_PREFIX."gruppen
                      ORDER BY name") or db_die();
    while ($row = db_fetch_row($result)) {
        $group_select .= "<option value='$row[0]'";
        if ($row[0] == $group_ID) $group_select .= ' selected="selected"';
        $group_select .= ">$row[1]</option>\n";
    }
    $group_select .= "</select>\n";

    $output .= '
        <fieldset>
            <legend>'.__('Group management').'</legend>
        <input type="submit" class="button" name="neu" value="'.__('New').'" /> '.__('or').'
        <input type="submit" class="button" name="aendern" value="'.__('Modify').'" />
        '.$group_select.'
        <input type="submit" class="button" name="loeschen" value="'.__('Delete').'" />
        </fieldset>
    ';
    $output .= "</form>\n\n";
}


//**************************
// groupID set -> main dialog

// user management
if ($group_ID) {
    // set query string for the user list
    $groupstring = " WHERE gruppe = $group_ID";

    $output .= '<form action="admin.php" method="post" name="frm1">';
    $hidden_fields = array( "action" => "user");
    $output .= hidden_fields($hidden_fields)."\n";
    $output .= '
        <fieldset>
            <legend>'.__('User management').'</legend>
            <input type="submit" class="button" name="neu" value="'.__('New').'" /> '.__('or').'
    ';
    $hidden_fields = array( "action" => "user");
    $output .= hidden_fields($hidden_fields)."\n";
    // user select
    $user_select = "<select name='pers_ID'><option value='0'></option>\n";
    $result2 = db_query("SELECT ".DB_PREFIX."users.ID, nachname, vorname
                           FROM ".DB_PREFIX."users, ".DB_PREFIX."grup_user
                          WHERE ".DB_PREFIX."users.ID = user_ID
                            AND grup_ID = ".(int)$group_ID."
                       ORDER BY nachname") or db_die();
    // loop over all entries
    while ($row2 = db_fetch_row($result2)) {
        $user_select .= "<option value='$row2[0]'>$row2[1], $row2[2]</option>\n";
    }
    $user_select .= "</select>&nbsp;\n";
    $output .= '
            <input type="submit" class="button" name="aendern" value="'.__('Modify').'" onclick="return chkForm(\'frm1\',\'pers_ID\',\''.__('Please choose an element').'!\');"/>
            '.$user_select.'
            <input type="submit" class="button" name="loeschen" value="'.__('Delete').'" onclick="return chkForm(\'frm1\',\'pers_ID\',\''.__('Please choose an element').'!\');"/>
        </fieldset>
        </form>
    ';


    // *****
    // roles
    // *****
    if (PHPR_ROLES) {
        $output .= '<form action="admin.php" method="post" name="frm2">';
        $hidden_fields = array( "action" => "roles");
        $output .= hidden_fields($hidden_fields)."\n";
        $output .= '
            <fieldset>
                <legend>'.__('Roles').'</legend>
                <input type="submit" class="button" name="neu" value="'.__('New').'"/> '.__('or').'
                <input type="submit" class="button" name="aendern" value="'.__('Modify').'" onclick="return chkForm(\'frm2\',\'roles_ID\',\''.__('Please choose an element').'!\')"/>
                <select name="roles_ID"><option value="0"></option>
        ';
        $result = db_query("SELECT ID, title
                              FROM ".DB_PREFIX."roles
                          ORDER BY title") or db_die();
        while ($row = db_fetch_row($result)) {
            $row[1] = html_out($row[1]);
            $output .= "<option value='$row[0]'>$row[1]</option>\n";
        }
        $output .= '
                </select>
                <input type="submit" class="button" name="loeschen" value="'.__('Delete').'" onclick="return chkForm(\'frm2\',\'roles_ID\',\''.__('Please choose an element').'!\')"/>
            </fieldset>
            </form>
        ';
    }

    // ****************************
    // helpdesk category management
    if (PHPR_ACCOUNTS) {
        $output .= '<form action="admin.php" method="post" name="frm3">';
        $hidden_fields = array( "action" => "accounts");
        $output .= hidden_fields($hidden_fields)."\n";
        $output .= '
            <fieldset>
                <legend>'.__('Account Management').'</legend>
                <input type="submit" class="button" name="neu" value="'.__('New').'"/> '.__('or').'
                <input type="submit" class="button" name="aendern" value="'.__('Modify').'" onsubmit="return chkForm(\'frm3\',\'db_accounts_ID\',\''.__('Please choose an element').'!\')"/>
                <select name="db_accounts_ID"><option value="0"></option>
        ';
        $result = db_query("SELECT ID, name
                              FROM ".DB_PREFIX."db_accounts
                          ORDER BY name") or db_die();
        while ($row = db_fetch_row($result)) {
            $row[1] = html_out($row[1]);
            $output .= "<option value='$row[0]'>$row[1]</option>\n";
        }
        $output .= '
                </select>
                <input type="submit" class="button" name="loeschen" value="'.__('Delete').'" onsubmit="return chkForm(\'frm3\',\'db_accounts_ID\',\''.__('Please choose an element').'!\')"/>
            </fieldset>
            </form>
        ';
    }

    // timecard
    if (PHPR_TIMECARD) {
        $output .= '<form action="admin.php" method="post">';
        $hidden_fields = array( "action" => "timecard");
        $output .= hidden_fields($hidden_fields)."\n";
        $output .= '
            <fieldset>
                <legend>'.__('Timecard Management').'</legend>
                <input type="submit" class="button" name="pers_timecard" value="'.__('View').'"/>
                <select name="pers_timecard">
        ';
        $result = db_query("SELECT ".DB_PREFIX."users.ID, nachname, vorname
                              FROM ".DB_PREFIX."users, ".DB_PREFIX."grup_user
                             WHERE grup_ID = ".(int)$group_ID."
                               AND user_ID = ".DB_PREFIX."users.ID
                          ORDER BY nachname") or db_die();
        while ($row = db_fetch_row($result)) {
            $output .= "<option value='$row[0]'";
            if ($row[0] == $pers_timecard) $output .= ' selected';
            $output .= ">$row[1], $row[2]</option>\n";
        }
        $output .= '
                </select>
                <select name="month">
        ';
        // Monatsbox
        if (!$month) $month = date("m");
        if (!$year)  $year  = date("Y");
        for ($a=1; $a<13; $a++) {
            $mo = date("n", mktime(0,0,0, $a, 1, $year));
            $name_of_month = $name_month[$mo];
            if ($mo == $month) $output .= "<option value='$a' selected='selected'>$name_of_month</option>\n";
            else               $output .= "<option value='$a'>$name_of_month</option>\n";
        }
        $output .= '
                </select>
                <select name="year">
        ';
        $y = date("Y");
        for ($i=$y-2; $i<=$y+5; $i++) {
            if ($i == $year) $output .= "<option value='$i' selected='selected'>$i</option>\n";
            else             $output .= "<option value='$i'>$i</option>\n";
        }
        $output .= '
                </select>
            </fieldset>
            </form>
        ';
    }

    // Logging
    if (PHPR_LOGS) {
        $output .= '<form action="admin.php" method="post">';
        $hidden_fields = array( "action" => "logs");
        $output .= hidden_fields($hidden_fields)."\n";
        $output .= '
            <fieldset>
                <legend>'.__('Logging').'</legend>
                <input type="submit" class="button" name="pers_logs" value="'.__('View').'"/>
                <select name="pers_logs">
        ';
        $result = db_query("SELECT ".DB_PREFIX."users.ID, nachname, vorname
                              FROM ".DB_PREFIX."users, ".DB_PREFIX."grup_user
                             WHERE ".DB_PREFIX."users.ID = user_ID
                               AND grup_ID = ".(int)$group_ID."
                          ORDER BY nachname") or db_die();
        while ($row = db_fetch_row($result)) {
            $output .= "<option value='$row[0]'";
            if ($row[0] == $pers_logs) $output .= ' selected';            
            $output .= ">$row[1], $row[2]</option>\n";
        }
        $output .= '
                </select>
                <select name="month">
        ';
        // Monatsbox
        if (!$month) $month = date("m");
        if (!$year)  $year  = date("Y");
        for ($a=1; $a<13; $a++) {
            $mo = date("n", mktime(0,0,0, $a, 1, $year));
            $name_of_month = $name_month[$mo];
            if ($mo == $month) $output .= "<option value='$a' selected='selected'>$name_of_month</option>\n";
            else               $output .= "<option value='$a'>$name_of_month</option>\n";
        }
        $output .= '
                </select>
                <select name="year">
        ';
        $y = date("Y");
        for ($i=$y-2; $i<=$y+5; $i++) {
            if ($i == $year) $output .= "<option value='$i' selected='selected'>$i</option>\n";
            else             $output .= "<option value='$i'>$i</option>\n";
        }
        $output .= '
                </select>
            </fieldset>
            </form>
        ';
    }

    // orphan files
    if (PHPR_FILEMANAGER) {
        $output .= '<form action="admin.php" method="post">';
        $hidden_fields = array( "action" => "files");
        $output .= hidden_fields($hidden_fields)."\n";
        $output .= '
            <fieldset>
                <legend>'.__('File management').'</legend>
                <label>'.__('Orphan files').':</label>
                <input type="submit" class="button" name="delete" value="'.__('Delete').'" /> '.__('or').'
                <input type="submit" class="button" name="move" value="'.__('Move').'"/>
                <select name="pers_ID">
        ';
        $result = db_query("SELECT ".DB_PREFIX."users.ID, nachname, vorname
                              FROM ".DB_PREFIX."users, ".DB_PREFIX."grup_user
                             WHERE ".DB_PREFIX."users.ID = user_ID
                               AND grup_ID = ".(int)$group_ID."
                          ORDER BY nachname") or db_die();
        while ($row = db_fetch_row($result)) {
            $output .= "<option value='$row[0]'>$row[1], $row[2]</option>\n";
        }
        $output .= '
                </select>
            </fieldset>
            </form>
        ';
    }

    // bookmark management
    if (PHPR_BOOKMARKS) {
        $output .= '<form action="admin.php" method="post">';
        $hidden_fields = array( "action" => "lesezeichen");
        $output .= hidden_fields($hidden_fields)."\n";
        $output .= '
            <fieldset>
                <legend>'.__('Bookmarks').'</legend>
                <select name="lesezeichen_ID[]" multiple="multiple">
        ';
        $result = db_query("SELECT ID, bezeichnung
                              FROM ".DB_PREFIX."lesezeichen
                             WHERE $sql_group
                          ORDER BY bezeichnung") or db_die();
        while ($row = db_fetch_row($result)) {
            $row[1] = html_out($row[1]);
            $output .= "<option value='$row[0]'>$row[1]</option>\n";
        }
        $output .= '
                </select>
                <input type="submit" class="button" name="loeschen" value="'.__('Delete').'"/>
                <input type="submit" class="button" name="proof" value="'.__('Check').'"/>
                <label>'.__('for invalid links').'</label>
            </fieldset>
            </form>
        ';
    }

    // Forum
    if (PHPR_FORUM) {
        $output .= '<form action="admin.php" method="post">';
        $hidden_fields = array( "action" => "forum");
        $output .= hidden_fields($hidden_fields)."\n";
        $output .= '
            <fieldset>
                <legend>'.__('Forum').'</legend>
                <input type="submit" class="button" name="loeschen" value="'.__('Delete').'" onclick="return confirm(\''.__('Are you sure?').'\')"/>
                <label>'.__('Threads older than').'</label>
                <select name="tage">
                <option value="15">15</option><option value="30">30</option><option value="45">45</option><option value="60">60</option>
                </select>
                <label>'.__(' days ').'</label>
                <br /><input type="submit" class="button" name="loeschen" value="'.__('Delete').'" onclick="return confirm(\''.__('Are you sure?').'\')"/>
                <label>'.__('posting (and all comments) with an ID').':</label> <input type="text" name="ID" size="4"/>
            </fieldset>
            </form>
        ';
    }

    // chat
    if (PHPR_CHAT) {
        $output .= '<form action="admin.php" method="post">';
        $hidden_fields = array( "action"    => "chat",
                                "mode"      => "kill");
        $output .= hidden_fields($hidden_fields)."\n";
        $output .= '
            <fieldset>
                <legend>'.__('Chat').'</legend>
                <input type="submit" class="button" value="'.__('Delete').'"/>
                <label>'.__('Chat entries').'</label>
            </fieldset>
            </form>
        ';
    }

    //Access Rights
    $output .= '<form action="admin.php" method="post">';
    $hidden_fields = array( "action" => "access_rights");
    $output .= hidden_fields($hidden_fields)."\n";
    $output .= '
        <fieldset>
            <legend>'.__('Access Rights').'</legend>
            <label>'.__('Module').'</label>
            <select name="acc_module">
    ';
    foreach ($mod_arr as $mod) {
        if($acc_field[$mod[0]]<>'')$output .= "<option value='$mod[0]'>".__($mod[0])."</option>\n";
    }
    $output .= '
            </select>
            <label>ID:</label> <input type="text" name="module_ID" size="4"/>
            <input type="submit" class="button" value="'.__('Change').'"/>
        </fieldset>
        </form>
    ';
} // close $group_ID

$output .= '
        </div>
        <br /><br />
    </div>

<div id="right_container">
<br /><br /><br />
'.$context_output.'
</div>

</div></div>
</div></div>
</body>
</html>
';

echo $output;

?>
