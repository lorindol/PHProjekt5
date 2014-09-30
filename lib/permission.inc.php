<?php

// permission.inc.php - PHProjekt Version 5.2
// copyright  ©  2000-2005 Albrecht Guenther  ag@phprojekt.com
// www.phprojekt.com
// Author: Albrecht Guenther, $Author: polidor $
// $Id: permission.inc.php,v 1.7 2006/08/25 05:34:24 polidor Exp $

// check whether lib.inc.php has been included
if (!defined('lib_included')) die('Please use index.php!');


// check permission
function check_permission($table, $author, $ID) {
    global $user_ID;

    $result = db_query("SELECT ".qss($author)."
                          FROM ".qss(DB_PREFIX.$table)."
                         WHERE ID = ".(int)$ID) or db_die();
    $row = db_fetch_row($result);
    if ($row[0] == 0) die('no entry found.');
    if ($row[0] <> $user_ID) die('You are not privileged to do this!');
}

/**
 * Checks whether file is locked and user has write permissions
 *
 * @author Nina Schmitt
 * @param string $table associated table
 * @param string $locked name of db_field
 * @param int $ID Id of record
 * @return boolean
 */
function check_locked($table, $locked, $ID) {
    global $user_ID, $user_type;
    $query="SELECT ".qss($locked)."
                   FROM ".qss(DB_PREFIX.$table)."
                   WHERE ID = ".(int)$ID;
    $result = db_query($query) or db_die();
    $row = db_fetch_row($result);
    if ($row[0] <> $user_ID and $row[0]>0 and $user_type!=2) return false;
    else return true;
}

?>
