<?php
/**
 * @package    votum
 * @subpackage main
 * @author     Albrecht Guenther, $Author: polidor $
 * @licence    GPL, see www.gnu.org/copyleft/gpl.html
 * @copyright  2000-2006 Mayflower GmbH www.mayflower.de
 * @version    $Id: votum_data.php,v 1.29 2008-01-07 03:12:59 polidor Exp $
 */

// check whether lib.inc.php has been included
if (!defined("lib_included")) die("Please use index.php!");

// check role
if (check_role("votum") < 2) die("You are not allowed to do this!");

// check form token
check_csrftoken();

// insert a vote of an user
if ($votum_ID) {
    // make sure the user hasn't already voted
    $result = db_query("SELECT fertig, an
                          FROM ".DB_PREFIX."votum
                         WHERE ID = ".(int)$votum_ID."
                           AND is_deleted is NULL") or db_die();
    $row = db_fetch_row($result);
    if (!ereg("\"$user_ID\"", $row[0])) {

        $stimme = false;
        
        // if poll type is multiple option then the user can select several fields
        $votum_field = array();

        $stimme = false;
        // radiobutton?
        if (isset($radiopoll) && in_array($radiopoll, array('zahl1', 'zahl2', 'zahl3'))) {
            $votum_field[] = $radiopoll;
            $stimme = true;
        }
        // checkboxes?
        else {
            if (isset($zahl1) && $zahl1 == 'yes') {
                $votum_field[] = 'zahl1';
                $stimme = true;
            }
            if (isset($zahl2) && $zahl2 == 'yes') {
                $votum_field[] = 'zahl2';
                $stimme = true;
            }
            if (isset($zahl3) && $zahl3 == 'yes') {
                $votum_field[] = 'zahl3';
                $stimme = true;
            }
        }
        // no vote at all?
        if (!$stimme) $votum_field[] = 'kein';

        if (count($votum_field) > 0) {
            foreach ($votum_field as $dummy => $oneField) {
                $oneField = qss($oneField);
                $result = db_query("UPDATE ".DB_PREFIX."votum
                               SET $oneField = $oneField + 1
                             WHERE ID = ".(int)$votum_ID) or db_die();
            }
        }

        // update list of users already voted
        $result = db_query("SELECT fertig
                              FROM ".DB_PREFIX."votum
                             WHERE ID = ".(int)$votum_ID."
                               AND is_deleted is NULL") or db_die();
        $row = db_fetch_row($result);
        $pers = unserialize($row[0]);
        $pers[] = $user_ID;
        $fertig = serialize($pers);
        $result = db_query("UPDATE ".DB_PREFIX."votum
                               SET fertig = '$fertig'
                             WHERE ID = ".(int)$votum_ID) or db_die();
    } // close bracket from if query, whether the user already has been voted
}
else {
    if ($action == "new") {
        // don't forget the damned thing a title
        if (!$thema) die(__('Please specify the question for the poll! ')."<a href='votum.php?mode=forms&".SID."'>".__('back')."</a>");
        // at least one alternative should be listed ;-)
        if (!$text1 and !$text2 and !$text3) die(__('You should give at least one answer! ')." <a href='votum.php?mode=forms&".SID."'>".__('back')."</a>");
        // no prile and no person chosen? -> error
        if ($s[0] == "" and !$profil) die("<br /></div><div id='global-content'>".__('Please select at least one name! ')."</div>");

        // manual selection
        if (!$profil) $personen = serialize($s);
        // fetch profile
        else {
            $result = db_query("SELECT personen
                                  FROM ".DB_PREFIX."profile
                                 WHERE ID = ".(int)$profil) or db_die();
            $row = db_fetch_row($result);
            $personen = $row[0];
        }
        $dbTSnull_day = date("Ymd");
        $result = db_query("INSERT INTO ".DB_PREFIX."votum
                    (datum, von, thema, modus, an, text1, text2, text3, zahl1, zahl2, zahl3, kein)
                 VALUES
                    ('$dbTSnull_day', ".(int)$user_ID.", '".strip_tags($thema)."', '".strip_tags($modus)."', '".strip_tags($personen)."', '".strip_tags($text1)."', '".strip_tags($text2)."', '".strip_tags($text3)."', 0    ,0    ,0    ,0   )") or db_die();
        if ($result) {
            message_stack_in(__('Your call for votes is now active. '), "votum", "notice");
        }
    }
    else if ($action == "delete") {

        // check permission
        include_once(PATH_PRE."lib/permission.inc.php");
        check_permission("votum", "von", $ID);
        if ($ID > 0) {
            delete_record_id('votum',"WHERE ID = ".(int)$ID);
        }
    }
}

include_once(PATH_PRE.'votum/votum_view.php');

?>
