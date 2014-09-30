<?php

// access.inc.php - PHProjekt Version 5.2
// copyright  ©  2000-2005 Albrecht Guenther  ag@phprojekt.com
// www.phprojekt.com
// Author: Albrecht Guenther, $Author: polidor $
// $Id: access.inc.php,v 1.9 2006/09/24 22:16:47 polidor Exp $

// check whether the lib has been included - authentication!
if (!defined("lib_included")) die("Please use index.php!");


//  access selection
function assign_acc($acc, $table) {
    global $profil, $persons, $parent, $user_ID;

    // profile
    if ($acc == '4') {
        $result = db_query("SELECT personen
                              FROM ".DB_PREFIX."profile
                             WHERE ID = ".(int)$profil) or db_die();
        $row = db_fetch_row($result);
        $acc = $row[0];
    }

    // option "same access as directory"?
    else if ($acc == 'same_as_parent') {
        $result = db_query("SELECT acc
                              FROM ".DB_PREFIX.$table."
                             WHERE ID = ".(int)$parent) or db_die();
        $row = db_fetch_row($result);
        if ($parent > 0) $acc = $row[0];
        // no parent directory found? -> private
        else             $acc = 'private';
    }

    // manual selection of users in this group
    else if ($acc == '3') {
        $persons=(array)$persons;
        $user = slookup('users','kurz','ID',$user_ID,'1');
        if (!in_array($user,$persons)) {
            $persons[] = $user;
        }
       	$acc = serialize($persons);
    }

    // else: personal access or access for all -> leave value
    // -> no action.

    return $acc;
}

?>
