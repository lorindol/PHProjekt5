<?php

// import_cal_data.php - PHProjekt Version 5.2
// copyright © 2000-2005 Albrecht Guenther  ag@phprojekt.com
// www.phprojekt.com
// Author: Albrecht Guenther, $Author: polidor $
// $Id: import_cal_data.php,v 1.11 2006/08/25 05:34:25 polidor Exp $

if (!defined('UPDATE_SCRIPT')) die('You are not allowed to do this!');


include_once('./lib/lib.inc.php');
//include_once('./setup/db_var.inc.php');

// maybe this stuff here needs a lot of time..
set_time_limit(3600);

if($ressourcen_old){
    $ret = do_import_cal_data();
}

function do_import_cal_data() {
    global $dbIDnull;

    // get the lowest group id (should be the "default" group)
    $query = "SELECT MIN(ID)
                FROM ".DB_PREFIX."gruppen";
    $res = db_query($query) or db_die();
    $row = db_fetch_row($res);
    if (!$row[0]) {
        echo "ERROR: Problems getting lowest group id for resource update!<br />\n";
        return false;
    }
    $res_grp_id = $row[0];

    // get all resources and insert them into the users table
    $query = "SELECT ID, name, bemerkung, kategorie
                FROM ".DB_PREFIX."ressourcen";
    $res = db_query($query) or db_die();
    while ($row = db_fetch_row($res)) {
        $kurz     = 'r'.$row[0];
        $nachname = $row[1];
        $remark   = addslashes(trim($row[2])."\n---\n".trim($row[3])."\n");
        // insert into users table
        $query2 = "INSERT INTO ".DB_PREFIX."users
                               ( ID, kurz, nachname, remark, gruppe, usertype, status )
                        VALUES ( $dbIDnull, '$kurz', '$nachname', '$remark', '$res_grp_id', '1', '0' )";
        $res2 = db_query($query2) or db_die();
        // get the last inserted item
        $query2 = "SELECT ID
                     FROM ".DB_PREFIX."users
                    WHERE kurz = '$kurz'
                      AND nachname = '$nachname'";
        $res2 = db_query($query2) or db_die();
        $row2 = db_fetch_row($res2);
        if (!$row2[0]) {
            echo "ERROR: Problems getting users id for resource update!<br />\n";
            return false;
        }
        // add the new resource to the group
        $query2 = "INSERT INTO ".DB_PREFIX."grup_user
                               ( ID, grup_ID, user_ID )
                        VALUES ( $dbIDnull, '$res_grp_id', '".$row2[0]."' )";
        $res2 = db_query($query2) or db_die();
    }

    // get all resource events from termine_res_rel and create the termine/parent stuff
    $query = "SELECT termin_ID, res_ID
                FROM ".DB_PREFIX."termine_res_rel";
    $res = db_query($query) or db_die();
    while ($row = db_fetch_row($res)) {
        // get the event data
        $query2 = "SELECT ID, serie_id, serie_typ, serie_bis, von, event, remark,
                          projekt, datum, anfang, ende, ort, contact, remind,
                          visi, status, sync1, sync2, upload
                     FROM ".DB_PREFIX."termine
                    WHERE ID = ".(int)$row[0];
        $res2 = db_query($query2) or db_die();
        $row2 = db_fetch_row($res2);
        if (!$row2[0]) {
            echo "WARNING: Problems getting event data for resource update!<br />\n";
            continue;
        }
        // get the appr. user/resource id
        $kurz   = 'r'.$row[1];
        $query3 = "SELECT ID
                     FROM ".DB_PREFIX."users
                    WHERE kurz = '$kurz'";
        $res3 = db_query($query3) or db_die();
        $row3 = db_fetch_row($res3);
        if (!$row3[0]) {
            echo "WARNING: Problems getting appr. user/resource id for resource update!<br />\n";
            continue;
        }
        // add a copy of the event for the resource
        $query2 = "INSERT INTO ".DB_PREFIX."termine
                               ( ID, parent, serie_id, serie_typ, serie_bis, von, event, remark,
                                 projekt, datum, anfang, ende, ort, contact, remind, visi,
                                 status, sync1, sync2, upload, an, partstat )
                        VALUES ( $dbIDnull, '".$row[0]."', '".$row2[1]."', '".$row2[2]."',
                                 '".$row2[3]."', '".$row2[4]."', '".$row2[5]."', '".$row2[6]."',
                                 '".$row2[7]."', '".$row2[8]."', '".$row2[9]."', '".$row2[10]."',
                                 '".$row2[11]."', '".$row2[12]."', '".$row2[13]."', '".$row2[14]."',
                                 '".$row2[15]."', '".$row2[16]."', '".$row2[17]."', '".$row2[18]."',
                                 '".$row3[0]."', '2' )";
        $res2 = db_query($query2) or db_die();
    }


// TODO: we should drop the unneeded tables
//       "ressourcen" and "termine_res_rel" here...
    $res = db_query("ALTER TABLE ".DB_PREFIX."ressourcen
                          RENAME ".DB_PREFIX."_ressourcen_obsolete") or db_die();
    $res = db_query("ALTER TABLE ".DB_PREFIX."termine_res_rel
                          RENAME ".DB_PREFIX."_termine_res_rel_obsolete") or db_die();
    return true;
}

?>
