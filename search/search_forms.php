<?php

// search_forms.php - PHProjekt Version 5.2
// copyright  ©  2000-2005 Albrecht Guenther  ag@phprojekt.com
// www.phprojekt.com
// Author: Albrecht Guenther, $Author: polidor $
// $Id: search_forms.php,v 1.33.2.4 2007/03/13 04:29:41 polidor Exp $

// check whether lib.inc.php has been included
if (!defined('lib_included')) die('Please use index.php!');
if(!defined('PATH_PRE')){
    define('PATH_PRE','../');
}
require_once(PATH_PRE.'lib/dbman_lib.inc.php');

$ou = '';
// check whether variable $hits exists
if (!PHPR_MAXHITS) $maxhits = 50;
else               $maxhits = PHPR_MAXHITS;

$geshits = 0;

// calendar
if (($gebiet == 'termine' or $gebiet == 'all') and PHPR_CALENDAR and check_role('calendar') > 0) {
    $hits = 0;
    $fields = array(    'event'     => array('filter_show' => 1),
                        'remark'    => array('filter_show' => 1));

    $where = build_where($fields);

    if (isset($_POST['use_filters']) && ($_POST['use_filters'] == "on") ) {
        $where .= main_filter('', '', '', '', 'calendar','','');
    }

    $result = db_query("SELECT COUNT(ID)
                          FROM ".DB_PREFIX."termine
                               ".special_sql_filter_flags('calendar', xss_array($_POST))."
                               $where
                               ".special_sql_filter_flags('calendar', xss_array($_POST), false)."
                           AND an = ".(int)$user_ID) or db_die();
    $row = db_fetch_row($result);
    if ($row[0] > 0) {

        $result = db_query("SELECT ID, event, datum
                              FROM ".DB_PREFIX."termine
                                   ".special_sql_filter_flags('calendar', xss_array($_POST))."
                                   $where
                                   ".special_sql_filter_flags('calendar', xss_array($_POST), false)."
                               AND an = ".(int)$user_ID) or db_die();
        $tmp = "
        <table class='relations'>
            <caption>".__('Calendar')."</caption>
            <thead>
                <tr>
                    <td title='".__('Date')."'>".__('Date')."</td>
                    <td title='".__('Text')."'>".__('Text')."</td>
                </tr>
            </thead>
            <tbody>";
        $tbody = '';
        while ($row = db_fetch_row($result)) {
            if ($hits <= $maxhits) {
                $ref = '../calendar/calendar.php?mode=forms&amp;ID='.$row[0].$sid;
                tr_tag($ref,"parent.");
                $tbody .= "<tr><td scope='row'>$row[2]</td>\n";
                $tbody .= "<td><a href='$ref' target='_top'>".html_out($row[1])."</a></td></tr>\n";
            }
            $hits++;
        }
        $tmp .= "
            <tfoot>
                <tr>
                    <td colspan='2'>";
        if ($hits > $maxhits) $tmp .= "$maxhits ".__('of')." $hits ".__('hits were shown for').":\n";
        else $tmp .= "$hits ".__('hits were shown for')."\n";

        $tmp .= "</td>
                </tr>
            </tfoot>";
        $tmp .= $tbody."</tbody></table>\n";
        $geshits = $geshits + $hits;

        $ou .= $tmp;
        unset($tmp);
    }
    else {
        $ou .= get_no_hits_found_message('Calendar');
    }
}


// Forum
if (($gebiet == 'forum' or $gebiet == 'all') and PHPR_FORUM and check_role('forum') > 0) {
    $hits = 0;
    $fields = array('titel' => __('Title'), 'remark' => __('Text'));
    $where = build_where($fields);
    $query = "SELECT ID, antwort, von, titel, remark, kat, datum, gruppe, lastchange, notify
                FROM ".DB_PREFIX."forum
              $where
                 AND $sql_user_group";

    $result  = db_query($query) or db_die();
    $row = db_fetch_row($result);
    if ($row[0] > 0) {

        $result = db_query($query)or db_die();
        $tmp = "
        <table class='relations'>
            <caption>".__('Forum')."</caption>
            <thead>
                <tr>
                    <td title='".__('Title')."'>".__('Title')."</td>
                    <td title='".__('Text')."'>".__('Text')."</td>
                    <td title='".__('Date')."'>".__('Date')."</td>
                </tr>
            </thead>
            <tbody>";
        $tbody = '';
        while ($row = db_fetch_row($result)) {
            if ($hits <= $maxhits) {
                $ref = '../index.php?module=forum&amp;mode=forms&amp;ID='.$row[0].'&amp;action=writetext'.$sid;
                tr_tag($ref, 'parent.');
                $datum = substr($row[6],6,2).'-'.substr($row[6],4,2).'-'.substr($row[6],0,4).' '.substr($row[6],8,2).":".substr($row[6],10,2);
                $remark = nl2br(html_out(substr($row[4],0,300)));
                if (strlen($remark) > 300) $remark = $remark.'...';
                $tbody .= "<tr><td scope='row' class='column-1'><a href='$ref' target='_top'>$row[3]</a></td>\n";
                $tbody .= "<td>$remark</td><td>$datum</td></tr>";
            }
            $hits++;
        }

        $tmp .= "
            <tfoot>
                <tr>
                    <td colspan='3'>";
        if ($hits == 0) { $tmp .= strip_tags($searchterm).": ".__('there were no hits found.')."!"; }
        else if ($hits > $maxhits) { $tmp .= "$maxhits ".__('of')." $hits ".__('hits were shown for').":\n"; }
        else { $tmp .= "$hits ".__('hits were shown for').":\n"; }
        $tmp .= "</td>
                </tr>
            </tfoot>";
        $tmp .= $tbody."</tbody></table>\n";
        $geshits = $geshits + $hits;

        $ou .= $tmp;
        unset($tmp);
    }
    else {
        $ou .= get_no_hits_found_message('Forum');
    }
}


// file module
if (($gebiet == 'files' or $gebiet == 'all') and PHPR_FILEMANAGER and check_role('filemanager') > 0) {
    // set the total size to zero because we want to build the list entirely new.
    $total_size = 0;

    $hits = 0;
    $fields = array(    'filename'  => array('filter_show' => 1),
                        'remark'    => array('filter_show' => 1),
                        'kat'       => array('filter_show' => 1));

    $where = build_where($fields);

    if (isset($_POST['use_filters']) && ($_POST['use_filters'] == "on") ) {
        $where .= main_filter('', '', '', '', 'filemanager','','');
    }

    $query = "SELECT ID, von, filename, remark, kat, acc, datum, filesize, gruppe,
                     tempname, typ, div1, div2, pw, acc_write, version, lock_user, contact
                FROM ".DB_PREFIX."dateien".
               special_sql_filter_flags('filemanager', xss_array($_POST)) 
              ." $where ".
               special_sql_filter_flags('filemanager', xss_array($_POST), false) 
                 ." AND (von = ".(int)$user_ID." OR acc LIKE 'system' OR ((acc = 'group' OR acc LIKE '%\"$user_kurz\"%') AND $sql_user_group))
            ORDER BY filename";
    $result  = db_query($query) or db_die();
    $row = db_fetch_row($result);
    if ($row[0] > 0) {

        $result  = db_query($query) or db_die();
        $tmp = "
        <table class='relations'>
            <caption>".__('Files')."</caption>
            <thead>
                <tr>
                    <td title='".__('Name')."'>".__('Name')."</td>
                    <td title='".__('Date')."'>".__('Date')."</td>
                    <td title='".__('Byte')."'>".__('Byte')."</td>
                    <td title='".__('Category')."'>".__('Category')."</td>
                    <td title='".__('Comment')."'>".__('Comment')."</td>
                </tr>
            </thead>";
        $parent = "parent.";
        $total_size=0;
        $tbody = '';
        while ($row = db_fetch_row($result)) {
            if ($hits <= $maxhits) {
                $total_size= $total_size+$row[7];
                $ref = "../index.php?module=filemanager&amp;mode=forms&amp;ID=$row[0]&amp;action=writetext$sid";
                tr_tag($ref, 'parent.');
                $datum = substr($row[6],6,2)."-".substr($row[6],4,2)."-".substr($row[6],0,4)." ".substr($row[6],8,2).":".substr($row[6],10,2);
                $tbody .= "<tr><td scope='row'><a href='$ref' target='_top'>$row[2]</a></td>\n";
                $tbody .= "<td>$datum</td><td>$row[7]</td><td>$row[4]</td><td>$row[3]</td></tr>";
            }
            $hits++;
        }
        $tmp .= "
            <tfoot>
                <tr>
                    <td colspan='5'>";
        if (!$hits) $hits = '0';
        if (!$total_size) $total_size = '0';
        $tmp .= " &nbsp;<i>".__('Sum').": $hits ".__('objects').", $total_size Byte</i>\n";

        $tmp .= "</td>
                </tr>
            </tfoot>";
        $tmp .= $tbody."</tbody></table>\n";

        $geshits = $geshits + $hits;

        $ou .= $tmp;
        unset($tmp);
    }
    else {
        $ou .= get_no_hits_found_message('Files');
    }
}


// *******************
// result for contacts
if (($gebiet == 'contacts' or $gebiet == 'all') and PHPR_CONTACTS and check_role('contacts') > 0) {
    $hits = 0;
    $fields = array(    "vorname"   => array('filter_show' => 1),
                        "nachname"  => array('filter_show' => 1),
                        "firma"     => array('filter_show' => 1),
                        "gruppe"    => array('filter_show' => 1),
                        "email"     => array('filter_show' => 1),
                        "strasse"   => array('filter_show' => 1),
                        "land"      => array('filter_show' => 1),
                        "state"     => array('filter_show' => 1),
                        "tel1"      => array('filter_show' => 1),
                        "tel2"      => array('filter_show' => 1),
                        "fax"       => array('filter_show' => 1),
                        "bemerkung" => array('filter_show' => 1),
                        "div1"      => array('filter_show' => 1),
                        "div2"      => array('filter_show' => 1));

    $where = build_where($fields);

    if (isset($_POST['use_filters']) && ($_POST['use_filters'] == "on") ) {
        $where .= main_filter('', '', '', '', 'contacts','','');
    }

    $where2 = "(acc_read like 'system' or ((von = ".(int)$user_ID." or acc_read like 'group' or acc_read like '%\"$user_kurz\"%') and $sql_user_group))";



    $query = "SELECT ID,vorname,nachname,gruppe,firma,email,tel1,tel2,fax,strasse,stadt,plz,
                     land,kategorie,bemerkung
              FROM   ".DB_PREFIX."contacts
                     ".special_sql_filter_flags('contacts', xss_array($_POST))."
                               $where and
                               $where2
                     ".special_sql_filter_flags('contacts', xss_array($_POST), false);

    $result  = db_query($query) or db_die();
    $row= db_fetch_row($result);
    if ($row[0] > 0) {

        $result  = db_query($query) or db_die();
        $tmp = "
        <table class='relations'>
            <caption>".__('Contacts')."</caption>
            <thead>
                <tr>
                    <td title='".__('Family Name')."'>".__('Family Name')."</td>
                    <td title='".__('First Name')."'>".__('First Name')."</td>
                    <td title='".__('Company')."'>".__('Company')."</td>
                    <td title='".__('Street')."'>".__('Street')."</td>
                    <td title='".__('City')."'>".__('City')."</td>
                    <td title='".__('Country')."'>".__('Country')."</td>
                </tr>
            </thead>";
        $tbody = '';
        while ($row = db_fetch_row($result)) {
            if ($hits <= $maxhits) {
                $ref = "../index.php?module=contacts&amp;action=contacts&amp;ID=$row[0]&amp;mode=forms&amp;modify=1$sid";
                tr_tag($ref,"parent.");
                $tbody .= "<tr><td scope='row' class='column-1'>&nbsp;<a href='$ref' target=_top>$row[2]</a></td>\n";
                $tbody .= "<td>&nbsp;$row[1]</td>\n";
                $tbody .= "<td>&nbsp;$row[4]</td><td>&nbsp;$row[9]</td><td>&nbsp;$row[11] $row[10]</td><td>&nbsp;$row[12]</td></tr>\n";
            }
            $hits++;
        }
        $tmp .= "
            <tfoot>
                <tr>
                    <td colspan='6'>";
        if ($hits == 0) { $tmp .= strip_tags($searchterm).": ".__('there were no hits found.')."!"; }
        else if ($hits > $maxhits) { $tmp .= "$maxhits ".__('of')." $hits ".__('hits were shown for').":\n"; }
        else { $tmp .= "$hits ".__('hits were shown for').":\n"; }

        $tmp .= "</td>
                </tr>
            </tfoot>";
        $tmp .= $tbody."</tbody></table>\n";
        $geshits = $geshits + $hits;
        $ou .= $tmp;
        unset($tmp);
    }
    else {
        $ou .= get_no_hits_found_message('Contacts');
    }
}


// *****************
// results for notes
if (($gebiet == 'notes' or $gebiet == 'all') and PHPR_NOTES and check_role('notes') > 0) {
    $hits = 0;
    $fields = array(    "name"   => array('filter_show' => 1),
                        "remark" => array('filter_show' => 1));

    $where = build_where($fields);

    if (isset($_POST['use_filters']) && ($_POST['use_filters'] == "on") ) {
        $where.= main_filter('', '', '', '', 'notes','','');
    }

    $where2 = "(acc LIKE 'system' OR ((von = ".(int)$user_ID." OR acc LIKE 'group' OR acc LIKE '%\"$user_kurz\"%') AND gruppe = ".(int)$user_group."))";

    $query = "SELECT ID, name, remark
                FROM ".DB_PREFIX."notes
                     ".special_sql_filter_flags('notes', xss_array($_POST))."
                     $where
                 AND $where2
                     ".special_sql_filter_flags('notes', xss_array($_POST), false);

    $result = db_query($query) or db_die();
    $row = db_fetch_row($result);
    if ($row[0] > 0) {

        $result  = db_query($query) or db_die();
        $tmp = "
        <table class='relations'>
            <caption>".__('Notes')."</caption>
            <thead>
                <tr>
                    <td title='".__('Title')."'>".__('Title')."</td>
                    <td title='".__('Text')."'>".__('Text')."</td>
                </tr>
            </thead>";
        $tbody = '';
        while ($row = db_fetch_row($result)) {
            if ($hits <= $maxhits) {
                $ref = '../index.php?module=notes&amp;mode=forms&amp;ID='.$row[0].$sid;
                tr_tag($ref, 'parent.');
                $remark = nl2br(html_out(substr($row[2], 0, 300)));
                if (strlen($remark) > 300) $remark = $remark.'...';
                $tbody .= "<tr><td scope='row' class='column-1'><a href='$ref' target='_top'>$row[1]</a></td>\n";
                $tbody .= "<td>$remark</td></tr>\n";  // text
            }
            $hits++;
        }
        $tmp .="
            <tfoot>
                <tr>
                    <td colspan='2'>";
        if ($hits == 0) { $tmp .= strip_tags($searchterm).": ".__('there were no hits found.')."!"; }
        elseif ($hits > $maxhits) { $tmp .= "$maxhits ".__('of')." $hits ".__('hits were shown for').":\n"; }
        else { $tmp .= "$hits ".__('hits were shown for').":\n"; }
        $tmp .= "</td>
                </tr>
            </tfoot>";

        $tmp .= $tbody."</tbody></table>\n";
        $geshits = $geshits + $hits;
        $ou .= $tmp;
        unset($tmp);
    }
    else {
        $ou .= get_no_hits_found_message('Notes');
    }
}


// ****************
// results for todo
if (($gebiet == 'todo' or $gebiet == 'all') and PHPR_TODO and check_role('todo') > 0) {
    $hits = 0;
    $fields = array(    "note"   => array('filter_show' => 1),
                        "remark"     => array('filter_show' => 1));

    $where = build_where($fields);

    if (isset($_POST['use_filters']) && ($_POST['use_filters'] == "on") ) {
        $where.= main_filter('', '', '', '', 'todo','','');
    }

    $where2 = "(acc LIKE 'system' OR ((von = ".(int)$user_ID." OR acc LIKE 'group' OR acc LIKE '%\"$user_kurz\"%') AND gruppe = ".(int)$user_group."))";

    $query = "SELECT ID, remark, note
                FROM ".DB_PREFIX."todo
                     ".special_sql_filter_flags('todo', xss_array($_POST))."
                     $where
                 AND $where2
                     ".special_sql_filter_flags('todo', xss_array($_POST), false);

    $result = db_query($query) or db_die();
    $row = db_fetch_row($result);
    if ($row[0] > 0) {

        $result = db_query($query) or db_die();
        $tmp = "
        <table class='relations'>
            <caption>".__('Todo')."</caption>
            <thead>
                <tr>
                    <td title='".__('Title')."'>".__('Title')."</td>
                    <td title='".__('Text')."'>".__('Text')."</td>
                </tr>
            </thead>";
        $tbody = '';
        while ($row = db_fetch_row($result)) {
            if ($hits <= $maxhits) {
                $ref = "../index.php?module=todo&amp;mode=forms&amp;ID=$row[0]$sid";
                tr_tag($ref, 'parent.');
                $tbody .= "<tr><td scope='row' class='column-1'><a href='$ref' target='_top'>$row[1]</a></td>\n";
                $tbody .= "<td>".nl2br(html_out(substr($row[2],0,300)))."</td></tr>\n";  // text
            }
            $hits++;
        }
        $tmp .= "
            <tfoot>
                <tr>
                    <td colspan='2'>";
        if ($hits == 0) { $tmp .= strip_tags($searchterm).": ".__('there were no hits found.').'!'; }
        elseif ($hits > $maxhits) { $tmp .= "$maxhits ".__('of')." $hits ".__('hits were shown for').":\n"; }
        else { $tmp .= "$hits ".__('hits were shown for').":\n"; }

        $tmp .= "</td>
                </tr>
            </tfoot>";
        $tmp .= $tbody."</tbody></table>\n";
        $geshits = $geshits + $hits;
        $ou .= $tmp;
        unset($tmp);
    }
    else {
        $ou .= get_no_hits_found_message('Todo');
    }
}


// results for mails
if (($gebiet == 'mails' or $gebiet == 'all') and PHPR_QUICKMAIL == 2 and check_role('mail') > 0) {
    $hits = 0;
    $fields = array('subject' => array(), 'body' => array(), 'sender' => array(), 'recipient' => array(), 'remark' => array(), 'header' => array());
    $where = build_where($fields);
    $query= "SELECT ID, subject, body, sender
                              FROM ".DB_PREFIX."mail_client
                            $where
                               AND von = ".(int)$user_ID;
    $result = db_query($query) or db_die();
    $row = db_fetch_row($result);
    if ($row[0] > 0) {

        $result  = db_query($query) or db_die();
        $tmp = "
        <table class='relations'>
            <caption>".__('Mail')."</caption>
            <thead>
                <tr>
                    <td title='".__('Subject')."'>".__('Subject')."</td>
                    <td title='".__('Text')."'>".__('Text')."</td>
                    <td title='".__('Receiver')."'>".__('Receiver')."</td>
                </tr>
            </thead>";
        $tbody = '';
        while ($row = db_fetch_row($result)) {
            if ($hits < $maxhits) {
                $ref = "../index.php?module=mail&amp;mode=forms&amp;ID=$row[0]$sid";
                tr_tag($ref,"parent.");
                // subject with link
                $tbody .= "<tr><td scope='row' class='column-1'><a href='$ref' target='_top'>$row[1]&nbsp;</a></td>\n";
                $remark = nl2br(html_out(substr($row[2],0,300)));
                if(strlen($remark) > 300) { $remark = $remark."..."; }
                $tbody .= "<td>$row[3]&nbsp;</td><td>$remark&nbsp;</td></tr>\n";
            }
            $hits++;
        }
        $tmp .= "
            <tfoot>
                <tr>
                    <td colspan='3'>";
        if ($hits == 0) { $tmp .= strip_tags($searchterm).": ".__('there were no hits found.')."!"; }
        elseif ($hits > $maxhits) { $tmp .= "$maxhits ".__('of')." $hits ".__('hits were shown for').":\n"; }
        else { $tmp .= "$hits ".__('hits were shown for').":\n"; }
        $tmp .= "</td>
                </tr>
            </tfoot>";
        $tmp .= $tbody."</tbody></table>\n";
        $geshits = $geshits + $hits;
        $ou .= $tmp;
        unset($tmp);
    }
    else {
        $ou .= get_no_hits_found_message('Mail');
    }
}


// results for helpdesk
if (($gebiet == 'helpdesk' or $gebiet == 'all') and PHPR_RTS and check_role('helpdesk') > 0) {
    $hits = 0;
    $fields = array(    'contact' => array('filter_show' => 1),
                        'name' => array('filter_show' => 1),
                        'note' => array('filter_show' => 1));

    $where = build_where($fields);

    if (isset($_POST['use_filters']) && ($_POST['use_filters'] == "on") ) {
        $where.= main_filter('', '', '', '', 'helpdesk','','');
    }

    if ($user_type!=2) {
        $where .= " AND (assigned = '$user_kurz'";
        $result = db_query("SELECT kurz
                              FROM ".DB_PREFIX."gruppen
                             WHERE ID = ".(int)$user_group) or db_die();
        $row = db_fetch_row($result);
        $where .= " OR assigned = '$row[0]')";
    }

    $query = "SELECT ID, name, note
                FROM ".DB_PREFIX."rts
                ".special_sql_filter_flags('helpdesk', xss_array($_POST))."
                $where
                ".special_sql_filter_flags('helpdesk', xss_array($_POST), false);
    $result = db_query($query) or db_die();
    $row = db_fetch_row($result);
    if ($row[0] > 0) {

        // additional limitation for normal users: only those requests which are assigned to you or your group
        // group system? fetch short name of the group
        $result = db_query($query) or db_die();
        $tmp = "
        <table class='relations'>
            <caption>".__('Helpdesk')."</caption>
            <thead>
                <tr>
                    <td title='".__('Text')."'>".__('Text')."</td>
                    <td title='".__('Remark')."'>".__('Remark')."</td>
                </tr>
            </thead>";
        $tbody = '';
        while ($row = db_fetch_row($result)) {
            if ($hits < $maxhits) {
                $ref = "../index.php?module=helpdesk&amp;mode=forms&amp;ID=$row[0]$sid";
                tr_tag($ref,"parent.");
                // subject with link
                $tbody .= "<tr><td scope='row' class='column-1'><a href='$ref' target='_top'>$row[1]&nbsp;</a></td>\n";
                $remark = nl2br(html_out(substr($row[2],0,300)));
                if(strlen($remark) > 300) { $remark = $remark."..."; }
                $tbody .= "<td>$remark </td></tr>\n";
            }
            $hits++;
        }
        $tmp .="
            <tfoot>
                <tr>
                    <td colspan='2'>";
        if ($hits == 0) { $tmp .= strip_tags($searchterm).": ".__('there were no hits found.')."!"; }
        elseif ($hits > $maxhits) { $tmp .= "$maxhits ".__('of')." $hits ".__('hits were shown for').":\n"; }
        else { $tmp .= "$hits ".__('hits were shown for').":\n"; }
        $tmp .= "</td>
                </tr>
            </tfoot>";

        $tmp .= $tbody."</tbody></table>\n";
        $geshits = $geshits + $hits;
        $ou .= $tmp;
        unset($tmp);
    }
    else {
        $ou .= get_no_hits_found_message('Helpdesk');
    }
}


// results for bookmarks
if (($gebiet == 'bookmarks' or $gebiet == 'all') and PHPR_BOOKMARKS and check_role('bookmarks') > 0) {
    $hits = 0;
    $fields = array('url' => array(), 'bezeichnung' => array(), 'bemerkung' => array());
    $where = build_where($fields);
    $query = "SELECT ID, url, bezeichnung, bemerkung
                FROM ".DB_PREFIX."lesezeichen
              $where
                 AND (von = ".(int)$user_ID." OR gruppe = ".(int)$user_group.")";
    $result = db_query($query) or db_die();
    $row = db_fetch_row($result);
    if ($row[0] > 0) {

        $result  = db_query($query) or db_die();
        $tmp = "
        <table class='relations'>
            <caption>".__('Bookmarks')."</caption>
            <thead>
                <tr>
                    <td title='".__('Description')."'>".__('Description')."</td>
                    <td title='".__('Comment')."'>".__('Comment')."</td>
                </tr>
            </thead>";
        $tbody = '';
        while ($row = db_fetch_row($result)) {
            if ($hits <= $maxhits) {
                $ref = "../index.php?module=bookmarks&amp;mode=forms&amp;aendern=1&amp;ID=$row[0]$sid";
                tr_tag($ref,"parent.");
                $tbody .= "<tr><td scope='row' class='column-1'><a href='$row[1]' target=_blank>$row[2]</a></td>\n";
                $tbody .= "<td>".nl2br(html_out(substr($row[3],0,300)))."</td></tr>\n";  // text
            }
            $hits++;
        }
        $tmp .= "
            <tfoot>
                <tr>
                    <td colspan='2'>";
        if ($hits == 0) { $tmp .= strip_tags($searchterm).": ".__('there were no hits found.')."!"; }
        elseif ($hits > $maxhits) { $tmp .= "$maxhits ".__('of')." $hits ".__('hits were shown for').":\n"; }
        else { $tmp .= "$hits ".__('hits were shown for').":\n"; }
        $tmp .= "</td>
                </tr>
            </tfoot>";
        $tmp .= $tbody."</tbody></table>\n";
        $geshits = $geshits + $hits;
        $ou .= $tmp;
        unset($tmp);

    }
    else {
        $ou .= get_no_hits_found_message('Bookmarks');
    }
}

// *********


function build_where($fields) {
    global $searchterm;

    $where = 'WHERE';
    // split the string into keywords
    if (ereg(" AND ", $searchterm)) $words = explode(' AND ', $searchterm);
    else $words[0] = $searchterm;
    $where .= ' (';
    $flag1 = 0;
    foreach ($words as $keyword) {
        if ($flag1) $where .= ') AND (';
        $flag2 = 0;
        foreach ($fields as $field => $show_value) {
            if ($flag2) $where .= ' OR ';
            $where .= $field." LIKE '%".$keyword."%'";
            $flag2 = 1;
        }
        $flag1 = 1;
    }
    $where .= ')';
    return $where;
}