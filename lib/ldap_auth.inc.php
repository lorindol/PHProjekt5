<?php

/**
 * ldap_auth.inc.php - PHProjekt Version 5.2
 * copyright  ©  2000-2005 Albrecht Guenther  ag@phprojekt.com
 * www.phprojekt.com
 * Author: Moritz Kiese
 * $Id: ldap_auth.inc.php,v 1.11.2.1 2007/02/26 15:33:19 thorsten Exp $
 *
 * Modified November, 2002 by James Bourne <jbourne@mtroyal.ab.ca>
 * - rewrote (reused Moritzs' code) to work more efficiently
 * - Added return value checking and calls to logit function
 * - Added group auth
 * - Added autocreate
 *
 * Modified March, 2003 by James Bourne <jbourne@mtroyal.ab.ca>
 * - Added default language insertion into the database
 * - Added correct language handling code
 *
 * Modified March 15, 2003 by James Bourne <jbourne@mtroyal.ab.ca>
 * - Added code to update db with data from ldap if needed
 *
 * Modified March 16, 2003 James Bourne <jbourne@mtroyal.ab.ca>
 *   - Changed short name to use uid as this should always be set
 *   - Updated to use db info if possible for ldap_user_conf value
 */

// check whether lib.inc.php has been included
if (!defined('lib_included')) die('Please use index.php!');


$user_ID = 0;

require_once(PATH_PRE.'lib/ldapconf.inc.php');

/* get either the correct ldap_name or use the default */
$user_ldap_conf = -1;
$res1 = db_query("SELECT ldap_name
                    FROM ".DB_PREFIX."users
                   WHERE loginname = '$loginstring'");
if ($row1 = db_fetch_row($res1)) {
    if (isset($row1[0]) && (strlen($row1[0]) > 0)) {
        /* take that as being correct */
        $user_ldap_conf = $row1[0];
    }
}

if ($user_ldap_conf == -1) {
    /* not set yet use the default ldap_name of 1 */
    $user_ldap_conf = 1;
}

if (($ldap_con = ldap_connect($ldap_conf[$user_ldap_conf]['srv'])) == false) {
    logit("Error connecting to LDAP server - ".ldap_error($ldap_con));
    die("Error connecting to LDAP server");
}

// try to set LDAPv3 protocol - fallback (and default) is LDAPv2, if this went wrong
// note: ldap_set_option must be between ldap_connect and ldap_bind
ldap_set_option($ldap_con, LDAP_OPT_PROTOCOL_VERSION, 3);

if ($ldap_conf[$user_ldap_conf]['nt_domain'] == '') {
    if (!ldap_bind($ldap_con, $ldap_conf[$user_ldap_conf]['srch_dn'], $ldap_conf[$user_ldap_conf]['srch_dn_pw'])) {
        logit("Can't login to LDAP server ".ldap_error($ldap_con));
        die("Can't login to LDAP server ");
    }

    if (($res = ldap_search($ldap_con, $ldap_conf[$user_ldap_conf]['base_dn'], "(uid=$loginstring)",
        array( 'sn', 'givenName', 'mail', 'uid', 'initials', 'o'),0,0)) == false) {
        logit("Could not find $loginstring in ldap ".ldap_error($ldap_con));
        die("Could not find $loginstring");
    }

    $user_info = ldap_get_entries($ldap_con, $res);

    if (!$user_info) die("Can't get the login information.");

    /* your ldap server must define at least these */
    $ldap_logon     = $user_info[0]['dn'];
    $ldap_mail      = $user_info[0]['mail'][0];
    $ldap_sn        = $user_info[0]['sn'][0];
    $ldap_givenName = $user_info[0]['givenname'][0];
    $ldap_uid       = $user_info[0]['uid'][0];
    $ldap_company   = $user_info[0]['o'][0];
    ldap_free_result($res);
}
else {
    $ldap_logon = 'cn='.$loginstring.',cn='.$ldap_conf[$user_ldap_conf]['nt_domain'];
}

switch ($ldap_conf[$user_ldap_conf]['ldap_sync']) {
    case 1:
        if (ldap_bind($ldap_con, $ldap_logon, $user_pw)) {
            $found = 1;
        }
        break;
    case 2:
        if (ldap_bind($ldap_con, $ldap_logon, $user_pw) && ($user_pw != '')) {
            $found = 1;
            $ldap_usr = ldap_search($ldap_con, $ldap_conf[$user_ldap_conf]['base_dn'], "(uid=$loginstring)");
            $ldap_user_data = ldap_get_entries($ldap_con, $ldap_usr);
            for ($i = 0; ++$i == 19; ) {
                if ($ldap_conf[$user_ldap_conf][$i] != '') {
                    $row[$i] = $ldap_user_data[0][$ldap_conf[$user_ldap_conf][$i]];
                }
            }
        }
        break;
}

if (($found == 1) && ($ldap_conf[$user_ldap_conf]['groupauth'] == 1)) {
    /* check for the ldap group */
    if (!ldap_bind($ldap_con, $ldap_conf[$user_ldap_conf]['srch_dn'], $ldap_conf[$user_ldap_conf]['srch_dn_pw'])) {
        logit("Can't login to LDAP server ".ldap_error($ldap_con));
        die("Can't login to LDAP server ");
    }

    $searchstr = '('.$ldap_conf[$user_ldap_conf]['memberattr'].'='.$ldap_logon.')';

    if (($res = ldap_search($ldap_con, $ldap_conf[$user_ldap_conf]["ldap_group_dn"], $searchstr)) == false) {
        logit("Could not find $loginstring in ldap ".ldap_error($ldap_con));
        die("Could not find $loginstring");
    }

    $num = ldap_count_entries($ldap_con, $res);
    if ($num < 1) {
        $found = 0;
        logit("Not allowing login for $ldap_logon returning 0 ($num) ".ldap_error($ldap_con));
    }
    ldap_free_result($res);
}


if ($found == 1) {
/* check to see if this client is in the database */
    $result = db_query("SELECT ID, vorname, nachname, kurz, firma, email, ldap_name
                          FROM ".DB_PREFIX."users
                         WHERE loginname = '$loginstring'
                               $admin_login") or db_die();
    $row = db_fetch_row($result);

    if (!$row) {
        logit("Error fetching DB row return $found = 0");
        /* if autocreate is set to 1 then create the account in sql */
        if ($ldap_conf[$user_ldap_conf]['autocreate'] == 1) {
            logit("Auto creating new client $ldap_uid");

            $query = "INSERT INTO ".DB_PREFIX."users
                               (vorname, nachname, kurz, firma, email, gruppe, acc, ldap_name, loginname, sprache)
                        VALUES ('$ldap_givenName','$ldap_sn','$ldap_givenName',".
                                  "'".$ldap_conf[$user_ldap_conf]["company"]."','$ldap_mail',".
                                  $ldap_conf[$user_ldap_conf]["newusergrp"].",'y',1,".
                                  "'$ldap_uid','".$ldap_conf[$user_ldap_conf]["ldap_lang"]."')";
            $result = db_query(xss($query)) or db_die();

            if (!$result) {
                logit("Could not insert into db new user");
            }
            else {
                logit("New account created");

                /* get the id from users table and add into the grup table */
                $qry = "SELECT ID
                          FROM ".DB_PREFIX."users
                         WHERE loginname = '$ldap_uid'";
                $result = db_query($qry);

                if (!$result) {
                    logit("Error with DB query");
                }
                else {
                    $row = db_fetch_row($result);
                    $user_ID = $row[0];
                    $query = "INSERT INTO ".DB_PREFIX."grup_user
                                          (grup_id,user_id)
                                   VALUES ('".$ldap_conf[$user_ldap_conf]['newusergrp']."', ".(int)$user_ID.")";
                    if(!($result = db_query($query))) {
                        logit("Not inserting grup_user info as query failed");
                    }
                }
            }
        }
    }
    else {
        if (!isset($row[6]) || ($row[6] == '')) $user_ldap_conf = '1';
        else $user_ldap_conf = $row[6];

        if (($user_ldap_conf != 'off') && ($ldap_conf[$user_ldap_conf]['ldap_sync'] == '2')) {
            /* check against ldap data and update if required */
            $qw = '';
            if (strcmp($row[1], $ldap_givenName) != 0) {
                $qw = "vorname='$ldap_givenName'";
            }
            if (strcmp($row[2], $ldap_sn) != 0) {
                if ($qw != '') $qw .= ',';
                $qw .= "nachname='$ldap_sn'";
            }
            if (strcmp($row[3],$ldap_uid) != 0) {
                if ($qw != '') $qw.=',';
                $qw .= "kurz='$ldap_uid'";
            }
            if (strcmp($row[4],$ldap_company) != 0) {
                if ($qw != '') $qw.=',';
                $qw .= "firma='$ldap_company'";
            }
            if (strcmp($row[5],$ldap_mail) != 0) {
                if ($qw != '') $qw.=',';
                $qw .= "firma='$ldap_mail'";
            }
            if ($qw != '') {
                $query = "UPDATE ".DB_PREFIX."users
                                 SET $qw
                               WHERE ID = ".(int)$row[0];
                if (!($result = db_query($query))) {
                    logit("Could not update db info with LDAP info \"$query\"");
                }
            }
        }
        $user_ID = $row[0];
    }
}

// transfer the user ID to auth.inc.php to fetch the user data
if ($user_ID > 0) {
    $fetch_uservalues = $user_ID;
}

?>
