<?php
/**
* bookmarks db data handling script
*
* @package    bookmarks
* @module     main
* @author     Albrecht Guenther, $Author: thorsten $
* @licence    GPL, see www.gnu.org/copyleft/gpl.html
* @copyright  2000-2006 Mayflower GmbH www.mayflower.de
* @version    $Id: bookmarks_data.php,v 1.20.2.3 2007/02/26 13:46:09 thorsten Exp $
*/
if (!defined('lib_included')) die('Please use index.php!');

// check role
if (check_role("bookmarks") < 2) die("You are not allowed to do this!");

// check form token
check_csrftoken();

if ($loeschen)    delete_bookmark();
else if ($ID > 0) update_bookmark();
else              insert_bookmark();

include_once("../bookmarks/bookmarks_view.php");


function delete_bookmark() {
    global $ID, $user_ID;

    if ($ID > 0) {
        $res = db_query("SELECT bezeichnung
                           FROM ".DB_PREFIX."lesezeichen
                          WHERE ID = ".(int)$ID."
                            AND von = ".(int)$user_ID) or db_die();
        $row = db_fetch_row($res);
        if ($row[0]) {
            $row[0] = stripslashes($row[0]);
            $res = db_query("DELETE
                               FROM ".DB_PREFIX."lesezeichen
                              WHERE ID = ".(int)$ID."
                                AND von = ".(int)$user_ID) or db_die();
            if ($res) {
                message_stack_in("$row[0] ".__(' is deleted.'), "boookmarks", "notice");
            }
        }
    }
}


function update_bookmark() {
    global $url, $bezeichnung, $bemerkung, $ID, $user_ID;

    $res = false;
    if (check_values()) {
        $query = "UPDATE ".DB_PREFIX."lesezeichen
                         SET url = '$url',
                             bezeichnung = '".strip_tags($bezeichnung)."',
                             bemerkung = '".strip_tags($bemerkung)."'
                       WHERE ID = ".(int)$ID."
                         AND von = ".(int)$user_ID;
        $res = db_query($query) or db_die();
    }
    if ($res) {
        message_stack_in(xss($bezeichnung)." ".__(' is changed.'), "bookmarks", "notice");
        if ($_REQUEST['modify_update_b']) {
            $query_str = "bookmarks.php?mode2=bookmarks&ID=".$ID."&mode=forms&aendern=1";
            header('Location: '.$query_str);
        }
    }
}


function insert_bookmark() {
    global $url, $bezeichnung, $dbTSnull, $user_ID, $bemerkung, $user_group;

    $res = false;
    if (check_values()) {
        $query = "INSERT INTO ".DB_PREFIX."lesezeichen
                              (datum, von, url, bezeichnung, bemerkung, gruppe)
                       VALUES ('".$dbTSnull."', ".(int)$user_ID.", '".strip_tags($url)."', '".strip_tags($bezeichnung)."', '".strip_tags($bemerkung)."', ".(int)$user_group.")";
        $res = db_query($query) or db_die();
    }
    if ($res) {
        message_stack_in(xss($bezeichnung)." ".__('is taken to the bookmark list.'), "bookmarks", "notice");
        if ($_REQUEST['create_update_b']) {
            // find the ID of the last created user and assign it to ID
            $result = db_query("SELECT MAX(ID)
                                  FROM ".DB_PREFIX."lesezeichen
                                 WHERE von = ".(int)$user_ID) or db_die();
            $row = db_fetch_row($result);
            $ID = $row[0];
            $query_str = "bookmarks.php?mode2=bookmarks&ID=".$ID."&mode=forms&aendern=1";
            header('Location: '.$query_str);
        }
    }
}


function check_values() {
    global $ID, $url, $bezeichnung, $sql_user_group;

    $ret = true;
    if (!$url) {
        message_stack_in(__('Insert a valid Internet address! ')."!", "bookmarks", "error");
        $ret = false;
    }
    if (!$bezeichnung) {
        message_stack_in(__('Please specify a description!')."!", "bookmarks", "error");
        $ret = false;
    }
    if (!ereg("^http",$url) and !ereg("^ftp://",$url)) $url = "http://".$url;

    // fetch all bookmarks from this group
    if (!$ID) $ID = 0;
    $result = db_query("SELECT ID, url, bezeichnung
                          FROM ".DB_PREFIX."lesezeichen
                         WHERE ID <> ".(int)$ID."
                           AND $sql_user_group") or db_die();
    while ($row = db_fetch_row($result)) {
        $row[1] = stripslashes($row[1]);
        $row[2] = stripslashes($row[2]);
        // check for double url entries
        if ($url == $row[1]) {
            message_stack_in(__('This address already exists with a different description')."! $row[2]", "bookmarks", "error");
            $ret = false;
        }
        // check for double names
        if ($bezeichnung == $row[2]) {
            message_stack_in("$row[2] ".__(' already exists. ')."!", "bookmarks", "error");
            $ret = false;
        }
    }
    return $ret;
}

?>
