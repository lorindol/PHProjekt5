<?php
/**
* resource script with library function for the calendar
*
* @package    calendar
* @module     main
* @author     Paolo Panto, $Author: polidor $
* @licence    GPL, see www.gnu.org/copyleft/gpl.html
* @copyright  2000-2006 Mayflower GmbH www.mayflower.de
* @version    $Id: calendar.inc.php,v 1.129.2.3 2007/02/22 04:31:50 polidor Exp $
*/
if (!defined('lib_included')) die('Please use index.php!');


/**********************************************************
BEGIN functions for event access modes
***********************************************************/

/**
 * Access modes for calendar events:
 *
 * 'standard'
 *  - private events are not visible by everyone (users defined in settings can see the event)
 *  - normal  events are visible by everyone (users defined in settings can read the whole event)
 *  - public  events are visible and readable by everyone
 *  - proxies can see/read/edit all events (private, normal, public)
 *
 * 'special-1'
 *  - private events are visible by everyone (users defined in settings can read the whole event)
 *  - normal  events are visible by everyone (users defined in settings can read the whole event)
 *  - public  events are visible and readable by everyone
 *  - proxies can not read private events (users defined in settings can read the whole event)
 *  - proxies can not edit private events
 *
 */
if (!defined('PHPR_CALENDAR_ACCESS_MODE')) {
    define('PHPR_CALENDAR_ACCESS_MODE', 'standard');
}


/**
 * check if the current user is allowed to represent the user with the given id
 *
 * @param  integer $uID  user id from db
 * @return boolean
 */
function calendar_can_act_for($uID) {
    return in_array($uID, calendar_get_represented_users('proxy'));
}

/**
 * check if the current user is allowed to see events with the given visi
 *
 * @param  integer $uID   user id of the event-owner
 * @param  mixed   $visi  visi of the event as a number or a string
 *                        (0=normal, 1=private, 2=public)
 * @return boolean
 */
function calendar_can_see_events($uID, $visi) {
    global $user_ID;

    // return always true on own events
    if ($uID == $user_ID) {
        return true;
    }

    $ret = false;
    switch ($visi) {
        case '0':
        case 'normal':
        case '2':
        case 'public':
            $ret = true;
            break;
        case '1':
        case 'private':
            switch (PHPR_CALENDAR_ACCESS_MODE) {
                case 'standard':
                    if ( calendar_can_act_for($uID) ||
                    in_array($uID, calendar_get_represented_users('viewer')) ) {
                        $ret = true;
                    }
                    break;
                case 'special-1':
                    $ret = true;
                    break;
            }
            break;
    }
    return $ret;
}

/**
 * check if the current user is allowed to read events with the given visi
 *
 * @param  integer $uID   user id of the event-owner
 * @param  mixed   $visi  visi of the event as a number or a string
 *                        (0=normal, 1=private, 2=public)
 * @return boolean
 */
function calendar_can_read_events($uID, $visi) {
    global $user_ID;

    // return always true on own events
    if ($uID == $user_ID) {
        return true;
    }

    $ret = false;
    switch ($visi) {
        case '0':
        case 'normal':
            if ( calendar_can_act_for($uID) ||
            in_array($uID, calendar_get_represented_users('reader')) ) {
                $ret = true;
            }
            break;
        case '1':
        case 'private':
            switch (PHPR_CALENDAR_ACCESS_MODE) {
                case 'standard':
                    if (calendar_can_act_for($uID)) {
                        $ret = true;
                    }
                    break;
                case 'special-1':
                    if (in_array($uID, calendar_get_represented_users('viewer'))) {
                        $ret = true;
                    }
                    break;
            }
            break;
        case '2':
        case 'public':
            $ret = true;
            break;
    }
    return $ret;
}

/**
 * check if the current user is allowed to edit events with the given visi
 *
 * @param  integer $uID   user id of the event-owner
 * @param  mixed   $visi  visi of the event as a number or a string
 *                        (0=normal, 1=private, 2=public)
 * @return boolean
 */
function calendar_can_edit_events($uID, $visi) {
    global $user_ID;

    // return always true on own events
    if ($uID == $user_ID) {
        return true;
    }

    $ret = false;
    switch ($visi) {
        case '0':
        case 'normal':
        case '2':
        case 'public':
            if (calendar_can_act_for($uID)) {
                $ret = true;
            }
            break;
        case '1':
        case 'private':
            switch (PHPR_CALENDAR_ACCESS_MODE) {
                case 'standard':
                    if (calendar_can_act_for($uID)) {
                        $ret = true;
                    }
                    break;
            }
            break;
    }
    return $ret;
}

/**
 * get the visi access of the given user depending on other access rights
 * the result of this function is mainly used for db queries
 *
 * @param  integer $uID  user id of the event-owner
 * @return string  of '0'=normal and/or '1'=private and/or '2'=public
 */
function calendar_get_event_visi_access($uID) {
    if (calendar_can_see_events($uID, 'private')) {
        $ret = "0,1,2";
    }
    else {
        $ret = "0,2";
    }
    return $ret;
}

/**
 * neutralize 'event' text if needed
 *
 * @param  integer $uID   the user ID
 * @param  integer $visi  the visi mode of the event (0=normal - 1=private - 2=public)
 * @param  string  $text  the event text
 * @return string
 */
function calendar_process_event_text($uID, $visi, $text) {
    if (calendar_can_read_events($uID, $visi)) {
        return $text;
    }
    return __('Event');
}

/**
 * neutralize 'remark' text if needed
 *
 * @param  integer $uID   the user ID
 * @param  integer $visi  the visi mode of the event (0=normal - 1=private - 2=public)
 * @param  string  $text  the remark text
 * @return string
 */
function calendar_process_remark_text($uID, $visi, $text) {
    if (calendar_can_read_events($uID, $visi)) {
        return $text;
    }
    return '';
}

/**********************************************************
END functions for event access modes
***********************************************************/


/**
 * Receive an incoming time (hours,minutes) in various formats and
 * reformat it into hhmm-format. Errors are not handled.
 *
 * @param string time-format with separators: .:,;/
 * @return string hhmm
 */
function calendar_format_incomingtime($time='') {
    if (empty($time)) return $time;

    // replace separators
    $time = ereg_replace("[.:,;/]", '', $time);

    // only digits allowed
    if (!preg_match("/^\d+$/", $time)) return preg_replace('~[^\d]~', '', $time);

    // if only one or 2 digits exist: the value is to be seen as hours
    // so we attach "00" for minutes
    if (strlen($time)<=2) $time .= "00";

    // now add leading zeros
    $time = sprintf("%04s", $time);

    return $time;
}

/**
 * get the number of the week, prefixed by 0, if needed
 * workaround because of insufficient strftime("%V", Timestamp)
 * we need ISO-weeknumber
 *
 * @param  integer $day
 * @return string
 */
function calendar_get_week_nr($day) {
    global $year, $month;
    $week_nr = date('W', mktime(1,0,0, $month, $day, $year));
    return substr('0'.$week_nr, -2);
}

/**
 * calc the width of text column and the length of text
 *
 * @param  integer $offset
 * @param  integer $cols
 * @return void
 */
function calendar_calc_wd($offset, $cols) {
    global $cal_wd, $wd, $ppc, $t_len;

    // width of text coloumn
    $wd = floor(($cal_wd - $offset - (2 * $cols))/$cols);
    // length of text
    $t_len = floor($wd/$ppc);
}

/**
 * get the user id and "get params" depending on the view which this function is called from
 *
 * @return array
 */
function calendar_get_user_params() {
    global $view, $act_as, $act_for, $user_ID;

    // set the act_as, act_for or own defs
    if ($view == 3 && $act_as) {
        $which_user = $act_as;
        $act_param  = '&amp;act_as='.$act_as;
    }
    else if ($view == 4 && $act_for) {
        $which_user = $act_for;
        $act_param  = '&amp;act_for='.$act_for;
    }
    else {
        $which_user = $user_ID;
        $act_param  = '';
    }

    return array( 'user_id'   => $which_user,
    'act_param' => $act_param );
}

/**
 * Get the ids of the users which the current user has more access rights.
 *
 * @param  string   $type identifies the user_*-table proxy,reader,viewer
 * @param  boolean  $force true to get data from db instead of session
 * @return array    Array of user-IDs
 */
function calendar_get_represented_users($type, $force=false) {
    global $user_ID;

    $type = qss($type);

    // safety test: force getting data from db, if session data is expired (see below)
    if ( !$force && (!isset($_SESSION['calendardata']['represented_user']['_expire']) ||
    $_SESSION['calendardata']['represented_user']['_expire'] < time()) ) {
        $force = true;
    }

    if ($force || !isset($_SESSION['calendardata']['represented_user'][$type])) {
        $represented_user = array();
        $query = "SELECT ".DB_PREFIX."users.ID, ".DB_PREFIX."users.nachname,
                         ".DB_PREFIX."users.vorname, ".DB_PREFIX."users.gruppe,
                         ".DB_PREFIX."gruppen.name
                    FROM ".DB_PREFIX."users, ".DB_PREFIX."users_".$type.", ".DB_PREFIX."gruppen
                   WHERE ".DB_PREFIX."users_".$type.".".$type."_ID = ".(int)$user_ID." 
                     AND ".DB_PREFIX."users_".$type.".user_ID = ".DB_PREFIX."users.ID
                     AND ".DB_PREFIX."users.gruppe = ".DB_PREFIX."gruppen.ID
                ORDER BY ".DB_PREFIX."users.nachname, ".DB_PREFIX."users.vorname";

        $res = db_query($query) or db_die();
        while ($row = db_fetch_row($res)) {
            $represented_user[] = array(
                 'ID'       => $row[0]
                ,'nachname' => $row[1]
                ,'vorname'  => $row[2]
                ,'gruppe'   => $row[3]
                ,'grpname'  => $row[4]
                );
        }
        $_SESSION['calendardata']['represented_user'][$type] = $represented_user;
        // set the date of session expiration (current time + 6 h)
        $_SESSION['calendardata']['represented_user']['_expire'] = time() + 21600;
    }
    $ret = array();
    foreach ($_SESSION['calendardata']['represented_user'][$type] as $item) {
        $ret[] = $item['ID'];
    }
    return $ret;
}

/**
 * get the data of the users which the current user can represent
 *
 * @param  string   $type
 * @param  boolean  $force true to get data from db instead of session
 * @return array
 */
function calendar_get_represented_userdata($type, $force=false) {
    if ($force || !isset($_SESSION['calendardata']['represented_user'][$type])) {
        calendar_get_represented_users($type, true);
    }
    return $_SESSION['calendardata']['represented_user'][$type];
}

/**
 * set (save) the related users which can do a specific job
 * or have more access rights than other for the current user
 *
 * @param  array  $data  id(s) of the related user(s)
 * @param  string $type
 * @return void
 */
function calendar_set_related_users(&$data, $type) {
    global $user_ID;

    $type = qss($type);

    // first delete all related db entries..
    $query = "DELETE FROM ".DB_PREFIX."users_".$type."
                    WHERE user_ID = ".(int)$user_ID;
    $res = db_query($query) or db_die();

    settype($data, 'array');
    if (count($data) > 0) {
        $values = array();
        
        foreach ($data as $k => $v) {
            // skip zero/empty ids, the own id and double entries
            if ($v == 0 || $v == $user_ID || in_array($v, $values)) {
                unset($data[$k]);
                continue;
            }
            $values[] = $v;
            $query = "INSERT INTO ".DB_PREFIX."users_".$type."
                                      (        user_ID  ,".$type."_ID)
                               VALUES (".(int)$user_ID.",".(int)$v.")";
            $res = db_query($query) or db_die();
        }
    }
    $_SESSION['calendardata']['related_user'][$type] = $data;
}

/**
 * Get the related users which can do a specific job
 * or have more access rights than other for the current user
 *
 * @param  string  $type  proxy,reader,viewer
 * @param  boolean $force true to get data from db instead of session
 * @return array   Array of userIDs
 */
function calendar_get_related_users($type, $force=false) {
    global $user_ID;

    $type = qss($type);

    if ($force || !isset($_SESSION['calendardata']['related_user'][$type])) {
        $ret = array();
        $query = "SELECT ".$type."_ID
                    FROM ".DB_PREFIX."users_".$type."
                   WHERE user_ID = ".(int)$user_ID;
        $res = db_query($query) or db_die();
        while ($row = db_fetch_row($res)) {
            $ret[] = $row[0];
        }
        $_SESSION['calendardata']['related_user'][$type] = $ret;
    }
    return $_SESSION['calendardata']['related_user'][$type];
}

/**
 * get the previous day, week, month or year
 *
 * @param  string $what
 * @return array
 */
function calendar_get_prev_date($what) {
    return calendar_get_next_date($what, false);
}

/**
 * get the next (or previous) day, week, month or year
 *
 * @param  string  $what
 * @param  boolean $next
 * @return array
 */
function calendar_get_next_date($what, $next=true) {
    global $year, $month, $day;

    $d = 0;
    $m = 0;
    $y = 0;
    if      ($what == 'w') $d = 7;
    else if ($what == 'm') $m = 1;
    else if ($what == 'y') $y = 1;
    else                   $d = 1;

    if ($next) $r = explode(',', date('Y,n,j', mktime(0,0,0, $month+$m, $day+$d, $year+$y)));
    else       $r = explode(',', date('Y,n,j', mktime(0,0,0, $month-$m, $day-$d, $year-$y)));

    return array('y'=>$r[0], 'm'=>$r[1], 'd'=>$r[2]);
}

/**
 * get the $year-$month-$day string with current date vars or from today
 *
 * @param  boolean $today
 * @return string
 */
function calendar_get_ymd($today=false) {
    global $year, $month, $day;

    if ($today) {
        $y = date('Y', mktime(date('H')+PHPR_TIMEZONE, date('i'), date('s'), date('m'), date('d'), date('Y')));
        $m = date('m', mktime(date('H')+PHPR_TIMEZONE, date('i'), date('s'), date('m'), date('d'), date('Y')));
        $d = date('d', mktime(date('H')+PHPR_TIMEZONE, date('i'), date('s'), date('m'), date('d'), date('Y')));
    }
    else {
        $y = $year;
        $m = substr('0'.$month, -2);
        $d = substr('0'.$day, -2);
    }
    return "$y-$m-$d";
}

/**
 * get the data of an event
 *
 * @param  integer $id the event id
 * @param  boolean $session store data in session on true
 * @return mixed   false if not found
 */
function calendar_get_event($id, $session=true) {

    $query = "SELECT ID, parent, von, an, event, remark, projekt, datum, anfang, ende,
                     ort, contact, remind, visi, partstat, sync1, sync2, upload, priority,
                     serie_id, serie_typ, serie_bis, status
                FROM ".DB_PREFIX."termine
               WHERE ID = ".(int)$id;
    $res = db_query($query) or db_die();
    $row = db_fetch_row($res);

    if (!$row[0]) {
        return false;
    }

    $ret = array(  'ID'             => $row[0]
    ,'parent'         => $row[1]
    ,'von'            => $row[2]
    ,'an'             => $row[3]
    ,'event'          => stripslashes($row[4])
    ,'remark'         => stripslashes($row[5])
    ,'projekt'        => $row[6]
    ,'datum'          => $row[7]
    ,'anfang'         => $row[8]
    ,'ende'           => $row[9]
    ,'ort'            => stripslashes($row[10])
    ,'contact'        => $row[11]
    ,'remind'         => $row[12]
    ,'visi'           => $row[13]
    ,'partstat'       => $row[14]
    ,'sync1'          => $row[15]
    ,'sync2'          => $row[16]
    ,'upload'         => stripslashes($row[17])
    ,'priority'       => $row[18]
    ,'serie_id'       => $row[19]
    ,'serie_typ'      => $row[20]
    ,'serie_bis'      => $row[21]
    ,'status'         => $row[22]
    ,'invitees'       => array()
    );
    // convert serial stuff if needed
    if ($ret['serie_typ'] != '') {
        $ret['serie_typ']     = unserialize($ret['serie_typ']);
        $ret['serie_weekday'] = $ret['serie_typ']['weekday'];
        $ret['serie_typ']     = $ret['serie_typ']['typ'];
    }
    $parent = ($ret['parent']) ? $ret['parent'] : $ret['ID'];
    // collect invitees..
    $data = calendar_get_event_invitees($parent, $ret['serie_id']);
    if (count($data) > 0) {
        foreach ($data as $item) {
            if (in_array($item['an'], $ret['invitees'])) {
                continue;
            }
            $ret['invitees'][] = $item['an'];
        }
    }
    if ($session) {
        // save the data in session to prevent modifications via post/get
        // (access rights, ...) - also called: paranoia ;-)
        $_SESSION['calendardata']['current_event'] = $ret;
    }
    return $ret;
}

/**
 * get the data of the invitees for the given event id
 *
 * @param  integer $id
 * @param  integer $serie_id
 * @return array
 */
function calendar_get_event_invitees($id, $serie_id) {

    #$txs = microtime();

    static $ret        = array();
    static $s_id       = 0;
    static $s_serie_id = 0;
    if ($id == $s_id && $serie_id == $s_serie_id) {
        return $ret;
    }
    $s_id       = $id;
    $s_serie_id = $serie_id;

    $where_array   = array();
    $where_array[] = "ID = ".(int)$id;
    $where_array[] = "parent = ".(int)$id;
    if ($serie_id) {
        $where_array[] = "ID = ".(int)$serie_id;
        $where_array[] = "parent = ".(int)$serie_id;
        $where_array[] = "serie_id = ".(int)$serie_id;
    }
    //$serie_id = ($serie_id) ? " OR ID = $serie_id OR parent = $serie_id OR serie_id = $serie_id" : '';

    // FIXME: hm, due to the current db design it is much faster
    // to make multiple selects than an OR catenation ( => hack ).
    // this should be fixed next time with better indexes or something else...
    foreach ($where_array as $where) {
        $query = "SELECT ID, parent, von, an, partstat, sync2
                    FROM ".DB_PREFIX."termine
                   WHERE $where
                ORDER BY partstat";
        #echo $query."<br />\n";
        $res = db_query($query) or db_die();
        while ($row = db_fetch_row($res)) {
            $ret[$row[0]] = array(  'ID'       => $row[0]
            ,'parent'   => $row[1]
            ,'von'      => $row[2]
            ,'an'       => $row[3]
            ,'partstat' => $row[4]
            ,'sync2'    => $row[5]
            );
        }
    }
    /*
    $query = "SELECT ID, parent, von, an, partstat, sync2
    FROM ".DB_PREFIX."termine
    WHERE ID = ".(int)$id." 
    OR parent = ".(int)$id." 
    $serie_id
    ORDER BY partstat";
    echo $query."<br />\n";
    $res = db_query($query) or db_die();
    while ($row = db_fetch_row($res)) {
    $ret[$row[0]] = array(  'ID'       => $row[0]
    ,'parent'   => $row[1]
    ,'von'      => $row[2]
    ,'an'       => $row[3]
    ,'partstat' => $row[4]
    ,'sync2'    => $row[5]
    );
    }
    */
    #$txe = microtime();
    #$diff = number_format(((substr($txe,0,9)) + (substr($txe,-10)) - (substr($txs,0,9)) - (substr($txs,-10))),4);
    #echo "calendar_get_event_invitees() = $diff<br /><br />\n";

    return $ret;
}

/**
 * get all serial events which belongs to the given id
 *
 * @param  int    $uID    the user ID
 * @param  int    $id     the serie_id
 * @param  string $datum  begin at this date
 * @return array
 */
function calendar_get_serial_events($uID, $id, $datum='') {

    $ret = array();
    if ($datum == '') $datum = calendar_get_ymd();
    $query = "SELECT ID, datum
                FROM ".DB_PREFIX."termine
               WHERE an = ".(int)$uID." 
                 AND (ID = ".(int)$id." OR serie_id = ".(int)$id.")
                 AND datum >= '$datum'
            ORDER BY datum";
    $res = db_query($query) or db_die();
    while ($row = db_fetch_row($res)) {
        $ret[] = array( 'ID'    => $row[0]
        ,'datum' => $row[1]
        );
    }
    return $ret;
}

/**
 * build and get the corresponding header for a view
 *
 * @param  string  $type
 * @param  array   $user_params
 * @param  boolean $date_only
 * @return string
 */
function calendar_view_prevnext_header($type, $user_params, $date_only=false) {
    global $mode, $view, $year, $month, $day, $act_for, $act_as, $name_day, $name_month, $sid;
    global $user_firstname, $user_name, $user_group, $user_ID, $date_format_object;

    $prev_dat = calendar_get_prev_date($type);
    $prev_url = './calendar.php?mode='.$mode.'&amp;view='.$view.'&amp;year='.$prev_dat['y'].
    '&amp;month='.$prev_dat['m'].'&amp;day='.$prev_dat['d'].$user_params['act_param'].$sid;
    $next_dat = calendar_get_next_date($type);
    $next_url = './calendar.php?mode='.$mode.'&amp;view='.$view.'&amp;year='.$next_dat['y'].
    '&amp;month='.$next_dat['m'].'&amp;day='.$next_dat['d'].$user_params['act_param'].$sid;

    // get week day
    $wo_tag = date('w', mktime(0,0,0, $month, $day, $year));

    // show the date only (without username/group)
    if ($date_only) {
        $user_ident = '';
    }
    else {
        $group_name = slookup5('gruppen', 'name', 'ID', $user_group, false, true);
        $user_ident = __('User').':&nbsp;<b>'.$user_firstname.'&nbsp;'.$user_name.'&nbsp;('.$group_name.')</b>';
        if ($user_params['user_id'] != $user_ID) {
            $query = "SELECT ".DB_PREFIX."users.nachname, ".DB_PREFIX."users.vorname,
                             ".DB_PREFIX."users.gruppe, ".DB_PREFIX."gruppen.name
                        FROM ".DB_PREFIX."users, ".DB_PREFIX."gruppen
                       WHERE ".DB_PREFIX."users.ID = ".(int)$user_params['user_id']." 
                         AND ".DB_PREFIX."users.gruppe = ".DB_PREFIX."gruppen.ID";
            $res = db_query($query) or db_die();
            $row = db_fetch_row($res);
            if (!$row[0]) $which_user = '&middot;?&middot;';
            else          $which_user = $row[1].'&nbsp;'.$row[0].'&nbsp;('.$row[3].')';

            $user_ident = __('Calendar user').':&nbsp;<b>'.$which_user.'</b>'.
            '&nbsp;&nbsp;&middot;&nbsp;&nbsp;'.$user_ident;
        }
        $user_ident = '&nbsp;&nbsp;&middot;&nbsp;&nbsp;'.$user_ident;
    }

    switch ($type) {
        case 'w':
            $ret = calendar_get_week_nr($day).'.&nbsp;'.__('calendar week').$user_ident;
            break;
        case 'm':
            $ret = $name_month[$month].'.&nbsp;'.$year.$user_ident;
            break;
        case 'y':
            $ret = __('Year').'&nbsp;'.$year.$user_ident;
            break;
        case 'd':
        default:
            $ret = $name_day[$wo_tag].',&nbsp;'.$date_format_object->convert_db2user(calendar_get_ymd()).$user_ident;
    }
    // output
    $ret = '
        <a href="'.$prev_url.'" title="&lt;&lt;">&lt;&lt;</a>&nbsp;
        <a href="'.$next_url.'" title="&gt;&gt;">&gt;&gt;</a>
        '.$ret.'
    ';

    return $ret;
}

/**
 * set the partstat state for the event with the given id
 *
 * @param  integer  $ID
 * @param  string   $action
 * @return void
 */
function calendar_set_event_status($ID, $action) {
    // check whether the user has the privilege to access to all eventID's
    $arr_checked_ID = calendar_check_privilege($ID);
    foreach ($arr_checked_ID as $ID) {
        $query = "UPDATE ".DB_PREFIX."termine
                     SET partstat = ".(int)$action."
                   WHERE ID = ".(int)$ID;
        $result = db_query($query) or db_die();
    }
}

/**
 * check the privileges where the user has access to
 *
 * @param  array $arr_ID  array of event ids
 * @return array          array of event ids
 */
function calendar_check_privilege($arr_ID) {
    global $user_ID;
    $act_for_user = calendar_get_represented_users('proxy');
    if (is_array($act_for_user) && count($act_for_user) > 0) {
        $act_for_user = " OR an IN (".implode(",", $act_for_user).")";
    }
    else {
        $act_for_user = '';
    }

    if (strlen($arr_ID)>1) {
        $arr_ID = " AND ID IN ($arr_ID)";
    }
    else {
        $arr_ID =  " AND ID = $arr_ID";
    }

    $ret = array();
    $query = "SELECT ID
                FROM ".DB_PREFIX."termine
               WHERE (an = ".(int)$user_ID." $act_for_user)
                     $arr_ID";
    $res = db_query($query) or db_die();
    while ($row = db_fetch_row($res)) {
        $ret[] = $row[0];
    }
    return $ret;
}

/**
 * check if we can delete an uploaded file
 *
 * @return boolean
 */
function calendar_can_delete_file() {
    $ret = true;
    if ($_SESSION['calendardata']['current_event']['parent']) {
        message_stack_in(__('You are not allowed to do this!'), 'calendar', 'error');
        $ret = false;
    }
    return $ret;
}

/**
 * delete the uploaded file of the given event
 *
 * @param  integer $id
 * @return void
 */
function calendar_delete_file($id) {
    require_once('../lib/dbman_data.inc.php');
    delete_attached_file('upload', $id, 'calendar');
    $query = "UPDATE ".DB_PREFIX."termine
                 SET upload = ''
               WHERE parent = ".(int)$id;
    $result = db_query($query) or db_die();
    echo '<meta http-equiv="refresh" content="0;url='.xss($_GET['referer']).'" />';
    exit;
}

/**
 * upload file, delete old file and get tmpname
 *
 * @param  integer $error
 * @return string  $res  tmpname
 */
function calendar_get_upload_value($error) {
    $res = ''; // initial value
    switch ($error) {
        case 0:
            // upload successful
            require_once('../lib/dbman_data.inc.php');
            delete_attached_file('upload', intval($_REQUEST['ID']), 'calendar'); // delete old file
            $db_filename = upload_file_create('uploadfile');
            $res = addslashes($db_filename);
            break;
        case 1:
        case 2:
            // upload filesize too big
            message_stack_in(__('Upload file size is too big'), 'calendar', 'error');
            break;
        case 3:
            // upload interrupted
            message_stack_in(__('Upload has been interrupted'), 'calendar', 'error');
            break;
        case 4:
            // no file uploaded -> nothing to do
            break;
    }
    return $res;
}

/**
 * get the event text formatted with styles
 *
 * @param  array   $data
 * @param  integer $cut
 * @return string
 */
function calendar_get_event_text(&$data, $cut=0) {
    global $cal_class;

    $img = ($data['status'] == '1') ? 5 : $data['partstat'] + 1;
    $ret = '<img src="'.get_css_path().'/img/'.$cal_class[$img].'.png" style="vertical-align:middle;" alt="" border="0" />&nbsp;';
    if ($data['anfang'] != '----' && $data['ende'] != '----') {
        $ret .= substr($data['anfang'],0,2).':'.substr($data['anfang'],2,2).' - ';
        $ret .= substr($data['ende'],0,2).':'.substr($data['ende'],2,2).' ';
    }
    else if ($cut > 0) {
        $cut += 12;
    }

    $style = 'font-style:normal;text-decoration:none;';
    if      ($data['status'] == '1')   $style = 'font-style:italic;text-decoration:line-through;';
    else if ($data['partstat'] == '1') $style = 'font-style:italic;text-decoration:none;';
    else if ($data['partstat'] == '2') $style = 'font-style:normal;text-decoration:none;';
    else if ($data['partstat'] == '3') $style = 'font-style:normal;text-decoration:line-through;';

    if ($cut > 0) {
        $ret .= ' <span style="'.$style.'">'.substr(html_out($data['event']), 0, $cut).'</span>';
        $ret .= '<img src="'.IMG_PATH.'/dots.gif" align="bottom" alt="" border="0" />';
    }
    else {
        $ret .= ' <span style="'.$style.'">'.html_out($data['event']).'</span>';
    }
    return $ret;
}

/**
 * get the text for the html "alt" and "title" tag
 *
 * @param  array  $data
 * @return string
 */
function calendar_get_alt_title_tag(&$data) {
    $ret = '';
    if ($data['anfang'] == '----' && $data['ende'] == '----') {
        $ret .= __('All day event').' -';
    }
    else {
        $ret .= substr($data['anfang'],0,2).':'.substr($data['anfang'],2,2).'-';
        $ret .= substr($data['ende'],0,2).':'.substr($data['ende'],2,2);
    }
    $ret .= ' '.html_out($data['event']);
    return $ret;
}

/**
 * get the serie_id of the current serial event, if this is one
 *
 * @return integer
 */
function calendar_get_current_serie_id() {
    $ret = false;
    if ( isset($_SESSION['calendardata']['current_event']) &&
    $_SESSION['calendardata']['current_event']['serie_typ'] ) {
        if ($_SESSION['calendardata']['current_event']['serie_id']) {
            $ret = $_SESSION['calendardata']['current_event']['serie_id'];
        }
        else {
            $ret = $_SESSION['calendardata']['current_event']['ID'];
        }
    }
    return $ret;
}

/**
 * calculate each event depending on the start-, enddate and the stepping
 *
 * @param  array $data
 * @return array
 */
function calendar_calculate_serial_events(&$data) {
    $ret = array();

    // TODO: $max_serial_events should be defined as a constant in config.inc.php
    $max_serial_events = 100;

    $d = 0;
    $m = 0;
    $y = 0;
    $weekday = array("Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday");

    if ($data['serie_typ']{0} == 'd') {
        $d = 1;
    }
    else if ($data['serie_typ']{0} == 'w') {
        $d = 7 * $data['serie_typ']{1};
    }
    else if ($data['serie_typ']{0} == 'm') {
        $m = 1;
    }
    else if ($data['serie_typ']{0} == 'y') {
        $y = 1;
    }
    else {
        return $ret;
    }

    $serie_von = calendar_get_timestamp_from_ymd($data['datum']);
    $serie_bis = calendar_get_timestamp_from_ymd($data['serie_bis']);

    $last  = '';
    $count = 1;
    $datum = $serie_von;
    while ($datum <= $serie_bis && $count < $max_serial_events) {
        if (count($data['serie_weekday']) > 0 && $d > 1) {
            // weekday handling
            foreach ($data['serie_weekday'] as $k=>$v) {
                $time_string = '+'.($data['serie_typ']{1} * ($count-1)).' week '.$weekday[$k];
                $datum = strtotime($time_string, $serie_von);
                $last  = date('Y-m-d', $datum);
                $ret[] = $last;
            }
        }
        else {
            // rest handling (d, w, m or y)
            $last  = date('Y-m-d', $datum);
            $ret[] = $last;
            $datum = mktime(0,0,0, date('m',$datum)+$m, date('d',$datum)+$d, date('Y',$datum)+$y);
        }
        $count++;
    }
    // define here the real last date according to $max_serial_events
    if ($last != '') $data['serie_bis'] = $last;
    // remove the first element from stack if this is $data['datum']
    if ($ret[0] == $data['datum']) array_shift($ret);

    return $ret;
}

/**
 * get the timestamp of a date in the format "yyyy-mm-dd"
 *
 * @param  string  $data
 * @return integer
 */
function calendar_get_timestamp_from_ymd(&$data) {
    $d = (int) substr($data, -2);
    $m = (int) substr($data, 5, 2);
    $y = (int) substr($data, 0, 4);
    return mktime(0,0,0, $m, $d, $y);
}

/**
 * calculates the end time of an event (depends also on duration)
 *
 * @param  array  $data  the data from the form
 * @return string
 */
function calendar_calculate_endtime(&$data) {
    $ret = (isset($data['ende'])) ? $data['ende'] : '';
    if (!$data['ende'] && $data['duration'] > 0 && $data['duration'] <= 480) {
        $duration = $data['duration'];
        $duration += (substr($data['anfang'], 0, 2) * 60) + substr($data['anfang'], -2);
        $dh  = (int) ($duration / 60);
        $dm  = (int) ($duration % 60);
        $ret = $dh.substr('0'.$dm, -2);
    }
    return $ret;
}

/**
 * fill the assoc. session var with the users of the given profile
 *
 * @param  int  $id  the id of the selected profile
 * @return void
 */
function calendar_select_users_from_profile($id) {
    global $user_ID, $user_kurz, $sql_user_group;
    
    $query = "SELECT personen
                FROM ".DB_PREFIX."profile
               WHERE (acc LIKE 'system'
                      OR ((von = ".(int)$user_ID." OR acc LIKE 'group' OR acc LIKE '%\"$user_kurz\"%')
                          AND $sql_user_group))
                 AND ID = ".(int)$id;
    $res = db_query($query) or db_die();
    $row = db_fetch_row($res);

    if (!$row[0]) return;
    $personen = unserialize($row[0]);
    if (empty($personen)) return;
    $personen = "'".implode("','", $personen)."'";
    
    $ret = array();
    $query = "SELECT ID
                FROM ".DB_PREFIX."users
               WHERE kurz IN ($personen)";
    $res = db_query($query) or db_die();
    while ($row = db_fetch_row($res)) {
        $ret[] = $row[0];
    }
    
    if (!empty($ret)) {
        $_SESSION['calendardata']['combisel'] = $ret;
    }
}

?>
