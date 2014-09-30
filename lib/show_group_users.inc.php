<?php

// show_group_users.inc.php - PHProjekt Version 5.2
// copyright  ©  2000-2005 Albrecht Guenther  ag@phprojekt.com
// www.phprojekt.com
// Author: Albrecht Guenther, $Author: alexander $
// $Id: show_group_users.inc.php,v 1.18.2.1 2007/01/23 11:53:06 alexander Exp $

// check whether lib.inc.php has been included
if (!defined('lib_included')) die('Please use index.php!');


// show all users of a group
function show_group_users($user_group, $exclude_user, $field, $filtered=false) {
    global $user_ID;

    if ($filtered) $user_filter = "status = 0 AND (usertype = 0 OR usertype = 2)";
    else           $user_filter = '';

    // group system, fetch ID's from the other users
    if ($user_group) {
        // include user in the list?
        if ($exclude_user) $user_self = 'AND '.DB_PREFIX."users.ID <> ".(int)$user_ID;
        else               $user_self = '';

        if ($user_filter != '') $user_filter = "AND ".$user_filter;

        $field_arr = unserialize($field);
        settype($field_arr, 'array');
        if (count($field_arr) > 0) {
            $field_arr = " AND ".DB_PREFIX."users.kurz IN ('". (implode("','", array_values($field_arr))) ."')";
        } else {
            $field_arr = '';
        }
        // also add users that are not in group but selected
        $query = "SELECT DISTINCT user_ID, ".DB_PREFIX."users.nachname
                             FROM ".DB_PREFIX."grup_user, ".DB_PREFIX."users
                            WHERE ((grup_ID = ".$user_group."
                                    AND ".DB_PREFIX."grup_user.user_ID = ".DB_PREFIX."users.ID)
                                   OR (".DB_PREFIX."grup_user.user_ID = ".DB_PREFIX."users.ID
                                       $field_arr))
                                  $user_self
                                  $user_filter
                         ORDER BY nachname";
      $result3 = db_query($query) or db_die();

    }
    // if user is not assigned to a group or group system is not activated
    else {
        // include user in the list?
        if ($exclude_user) {
            $user_self = "WHERE ID <> ".(int)$user_ID;
            if ($user_filter != '') $user_filter = "AND ".$user_filter;
        }
        else {
            $user_self = '';
            if ($user_filter != '') $user_filter = "WHERE ".$user_filter;
        }

        $result3 = db_query("SELECT ID, nachname
                               FROM ".DB_PREFIX."users
                                    $user_self
                                    $user_filter
                           ORDER BY nachname") or db_die();
    }

    // loop over all user ID's of this group, fetch names and display them
    $users_in_group = array();

    $str_selected = '';
    $str_other = '';
    $remaining_users = PHPR_FILTER_MAXHITS;
    while ($row3 = db_fetch_row($result3)) {
        $users_in_group[$row3[1]] = true;
        $result4 = db_query("SELECT nachname, kurz, vorname
                               FROM ".DB_PREFIX."users
                              WHERE ID = ".(int)$row3[0]) or db_die();
        $row4 = db_fetch_row($result4);
        // str for selected items
        if (eregi("\"".$row4[1]."\";", $field)) {
            $str_selected .= '<option value="'.$row4[1].'"';
            $str_selected .= ' selected="selected"';
            $str_selected .= " title='$row4[0], $row4[2]'>$row4[0], $row4[2]</option>\n";
        } else if ($remaining_users > 0) {
            // other items
            $str_other .= '<option value="'.$row4[1].'"';
            $str_other .= " title='$row4[0], $row4[2]'>$row4[0], $row4[2]</option>\n";
            $remaining_users--; 
        }
    }
    // first show the selected items
    $str = $str_selected;
    $str .= " <option value=''>- - - - - - -</option>\n";
    // now show the others
    $str .= $str_other;

    return $str;
}

?>
