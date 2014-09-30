<?php

// summary.inc.php - PHProjekt Version 5.2
// copyright © 2000-2005 Albrecht Guenther  ag@phprojekt.com
// www.phprojekt.com
// Author: Albrecht Guenther, $Author: polidor $
// $Id: summary.inc.php,v 1.48.2.4 2007/04/28 15:01:27 polidor Exp $

// check whether the lib has been included - authentication!
if (!defined('lib_included')) die('Please use index.php!');

function summary_show_timecard() {
    global $user_ID, $today1, $sid;

    $buttons = array();
    $result1 = db_query("SELECT ID
                           FROM ".DB_PREFIX."timecard
                          WHERE datum = '$today1'
                            AND ende IS NULL
                            AND users = ".(int)$user_ID) or db_die();
    $row1 = db_fetch_row($result1);
    // buttons for 'come' and 'leave', alternate display
    if ($row1[0] > 0) {
        $buttons[] = array('type' => 'link', 'href' => '/'.PHPR_INSTALL_DIR.'timecard/timecard.php?mode=data&amp;action=worktime_stop&amp;sure=1&amp;csrftoken='.make_csrftoken().$sid, 'text' => __('Working times stop'), 'stopwatch' => 'started');
    }
    else {
        $buttons[] = array('type' => 'link', 'href' => '/'.PHPR_INSTALL_DIR.'timecard/timecard.php?mode=data&amp;action=worktime_start&amp;csrftoken='.make_csrftoken().$sid, 'text' => __('Working times start'), 'stopwatch' => 'stopped');
    }

    //only show projectbookings if project module is activated!
    if (PHPR_PROJECTS and check_role('projects') > 0) {
        // Projektzuweisung
        $resultq = db_query("SELECT ID, div1, h, m
                           FROM ".DB_PREFIX."timeproj
                          WHERE users = ".(int)$user_ID."
                            AND (div1 LIKE '".date('Ym')."%')") or db_die();
        $rowq = db_fetch_row($resultq);
        // buttons for 'come' and 'leave', alternate display
        if ($rowq[0] > 0) {
            $buttons[] = array('type' => 'link', 'href' => '/'.PHPR_INSTALL_DIR.'timecard/timecard.php?mode=data&amp;action=clock_out'.$sid, 'text' => __('Project booking stop'), 'stopwatch' => 'started');
        }
        else {
            $buttons[] = array('type' => 'link', 'href' => '/'.PHPR_INSTALL_DIR.'timecard/timecard.php?mode=books&amp;action=clockin'.$sid, 'text' => str_replace('-', '', __('Project booking start')), 'stopwatch' => 'stopped');
        }
    }
    return $buttons;
}

function summary_show_calendar() {
    global $user_ID, $today1, $sid, $tdelements, $date_format_object;

    $output = '';
    /**************************
         events of today
    **************************/
    $output_calendar_1 = '
    <br /><table class="relations" style="width:100%">
        <caption>'.__('Calendar').'</caption>
        <thead>
            <tr>
                <td title="'.__('Title').'">'.__('Title').'</td>
                <td title="'.__('Date').'">'.__('Date').'</td>
                <td title="'.__('Start').'">'.__('Start').'</td>
                <td title="'.__('End').'">'.__('End').'</td>
            </tr>
        </thead>
        <tfoot>
            <tr>
                <td colspan="4">'.__('Todays Events').'</td>
            </tr>
        </tfoot>
        <tbody>';

    $now = date('Hi', mktime(date('H')+PHPR_TIMEZONE, date('i'), date('s'), date('m'), date('d'), date('Y')));
    $result = db_query("SELECT ID, event, datum, anfang, ende
                          FROM ".DB_PREFIX."termine
                         WHERE datum = '$today1'
                           AND an = ".(int)$user_ID."
                      ORDER BY anfang") or db_die();
    $nr = 0;
    $found = false;
    while ($row = db_fetch_row($result) and $nr < $tdelements) {
        $found = true;


        $output_calendar_1 .= '
                <tr>
                    <td><a href="../calendar/calendar.php?mode=forms&amp;ID='.$row[0].$sid.'">'.html_out($row[1]).'</a></td>
                    <td>'.$date_format_object->convert_db2user($row[2]).'</td>
                    <td>'.$row[3]{0}.$row[3]{1}.':'.$row[3]{2}.$row[3]{3}.'</td>
                    <td>'.$row[4]{0}.$row[4]{1}.':'.$row[4]{2}.$row[4]{3}.'</td>
                </tr>
        ';
        $nr++;
    }
    $output_calendar_1 .= '</tbody></table>';
    if ($found) $output .= $output_calendar_1;
    else        $output .= summary_no_entries_found(__('Calendar'), __('No Todays Events'));


    /**************************
         unconfirmed events
    **************************/
    $output_calendar_2 = '
    <br /><table class="relations" style="width:100%">
        <caption>'.__('Calendar').'</caption>
        <thead>
            <tr>
                <td title="'.__('Title').'">'.__('Title').'</td>
                <td title="'.__('Date').'">'.__('Date').'</td>
                <td title="'.__('Start').'">'.__('Start').'</td>
                <td title="'.__('End').'">'.__('End').'</td>
            </tr>
        </thead>
        <tfoot>
            <tr>
                <td colspan="4">'.__('Unconfirmed Events').'</td>
            </tr>
        </tfoot>
        <tbody>';
    // events to be accepted or rejected
    // events of today
    $now = date('Hi', mktime(date('H')+PHPR_TIMEZONE, date('i'), date('s'), date('m'), date('d'), date('Y')));
    $result = db_query("SELECT ID, event, datum, anfang, ende
                          FROM ".DB_PREFIX."termine
                         WHERE von <> ".(int)$user_ID."
                           AND an = ".(int)$user_ID."
                           AND partstat = 1
                           AND datum >= '$today1'
                      ORDER BY datum,anfang") or db_die();
    $found = false;
    while ($row = db_fetch_row($result) and $nr < $tdelements) {
        $found = true;
        $output_calendar_2 .= '
                <tr>
                    <td><a href="../calendar/calendar.php?mode=forms&amp;ID='.$row[0].$sid.'">'.html_out($row[1]).'</a></td>
                    <td>'.$date_format_object->convert_db2user($row[2]).'</td>
                    <td>'.$row[3]{0}.$row[3]{1}.':'.$row[3]{2}.$row[3]{3}.'</td>
                    <td>'.$row[4]{0}.$row[4]{1}.':'.$row[4]{2}.$row[4]{3}.'</td>
                </tr>';
    }
    $output_calendar_1 .= '</tbody></table>';
    if ($found) $output .= $output_calendar_2.'</tbody></table>';
    return $output;
}

function summary_show_forum() {
    global $user_ID, $user_kurz, $user_group, $sid, $tdelements, $date_format_object;

    if (isset($lastlogin['forum'])) $lastlogin_where = 'AND '.$lastlogin['forum'];
    else    $lastlogin_where = 'AND 1=1';

    $result = db_query("SELECT f.ID, f.titel, f.lastchange, vorname, nachname, f.datum, f.parent
                          FROM ".DB_PREFIX."forum f
                     LEFT JOIN ".DB_PREFIX."users u ON f.von = u.ID
                         WHERE (f.von = ".(int)$user_ID." 
                                OR f.acc LIKE 'system' 
                                OR ((f.acc LIKE 'group' 
                                     OR f.acc LIKE '%$user_kurz%') 
                                    AND f.gruppe = ".(int)$user_group."))
                                    $lastlogin_where
                      ORDER BY f.ID DESC") or db_die();
    $output_forum = '
    <br /><table id="forum" summary="table for forum" class="relations" style="width:100%">
        <caption>'.__('New forum postings').'</caption>
        <thead>
            <tr>
                <td title="'.__('Title').'">'.__('Title').'</td>
                <td title="'.__('Author').'">'.__('Author').'</td>
                <td title="'.__('Date').'">'.__('Date').'</td>
            </tr>
        </thead>
        <tbody>';

    $nr = 0;
    $found = false;
    while ($row = db_fetch_row($result) and $nr < $tdelements) {
        $found  = true;
        if($row[6]!=0)$ref    = '../forum/forum.php?mode=forms&amp;ID='.$row[0].'&amp;fID='.$row[6].$sid;
        else $ref    = '../forum/forum.php?fID='.$row[0].$sid;
        $row[5] = preg_replace("/^([0-9]{4})([0-9]{2})([0-9]{2})[0-9]{6}/", "\\1-\\2-\\3", $row[5]);
        $output_forum .= '
                <tr>
                    <td><a href="'.$ref.'" title="'.$row[1].'">'.html_out($row[1]).'</a></td>
                    <td>'.html_out($row[4].', '.$row[3]).'</td>
                    <td>'.$date_format_object->convert_db2user($row[5]).'</td>
                </tr>';
        $nr++;
    }
    $output_forum .='</tbody></table>';

    if ($found) return $output_forum;
    else        return summary_no_entries_found(__('Forum'), __('No new forum postings'));
}

function summary_show_votum() {
    global $user_ID, $tdelements;

    $output_votum = '
    <br class="clearboth" />
    <span class="modname">'.__('New Polls') .'</span><br />
';
    // fetch all votes from the database
    $result = db_query("SELECT ID, datum, von, thema, modus, an, fertig, text1,
                               text2, text3, zahl1, zahl2, zahl3, kein
                          FROM ".DB_PREFIX."votum
                         WHERE an LIKE '%\"$user_ID\"%'
                           AND (fertig IS NULL OR fertig NOT LIKE '%\"$user_ID\"%')
                      ORDER BY datum DESC") or db_die();
    $nr = 0;
    $found = false;
    while ($row = db_fetch_row($result) and $nr <= $tdelements) {
        $found = true;
        if ($row[5] == '') $row[5] = 'null';
        if ($row[6] == '') $row[6] = 'null';

        // have a look whether the user is 1. participant of this poll but not 2. already answered this poll :-)
        $day   = substr($row[1], 6, 2);
        $month = substr($row[1], 4, 2);

        // begin form to vote
        $output_votum .= '
    <div class="boxContent">
        <form action="./summary.php" method="post" style="display:inline;">
        <fieldset>
            <legend></legend>
            '.(SID ? "<input type='hidden' name='".session_name()."' value='".session_id()."' />" : '').'
            <input type="hidden" name="votum_ID" value="'.$row[0].'" />
            <input type="hidden" name="datum" value="" />
';

        // fetch author from user table
        $result2 = db_query("SELECT nachname
                               FROM ".DB_PREFIX."users
                              WHERE ID = ".(int)$row[2]) or db_die();
        $row2 = db_fetch_row($result2);
        // display poll
        $alt_title = __('Poll created on the ')." $month-$day / ".$row2[0];
        $output_votum .= "<img src='".IMG_PATH."/b.gif' alt='$alt_title' title='$alt_title' width='7' border='0' />&nbsp;".
                         html_out($row[3])."<br />\n";

        // is it a poll where you can vote 1. alternatively (-> radio button)
        if ($row[4] == 'r') {
            // scan all three available option fields
            for ($i=1; $i<=3; $i++) {
                // only display the option of a text is given
                if ($row[$i+6]) {
                    $output_votum .= "<input type='radio' name='radiopoll' value='zahl".$i."' /> &nbsp;".html_out($row[$i+6])."<br />\n";
                }
            }
        }
        // ... or to click several options at once (-> checkboxes)
        else {
            // scan all three available option fields
            for ($i=1; $i<=3; $i++) {
                // only display the option of a text is given
                if ($row[$i+6] <> '') {
                    $output_votum .= "<input type='checkbox' name='zahl".$i."' value='yes' />".html_out($row[$i+6])."<br />\n";
                }
            }
        }
        $output_votum .= get_go_button();
        $output_votum .= '
        </fieldset>
        </form>
    </div>
    <br class="clearboth" /><br class="clearboth" />
';
        $nr++;
    }

    if ($found) return $output_votum.'<br class="clearboth" />';
    else        return summary_no_entries_found(__('Voting system'), __('No New Polls'));
}

/**
* Returns an array with WHERE-Clauses for different modules
*/
function summary_get_last_login() {
    global $user_ID, $since_last;

    $retval = array();
    if (!$since_last) return $retval;
    if (!PHPR_LOGS) { return $retval; }

    // get timestamp for last login
    $result = db_query("SELECT login
                          FROM ".DB_PREFIX."logs
                         WHERE von = ".(int)$user_ID."
                      ORDER BY login DESC") or db_die();
    $row = db_fetch_row($result); // ignore current login
    $row = db_fetch_row($result);

    if (!$row) return $retval;

    $last_login = $row[0];

    // last login in date format
    $year = $last_login[0].$last_login[1].$last_login[2].$last_login[3];
    $month = $last_login[4].$last_login[5];
    $day = $last_login[6].$last_login[7];
    $last_login_date = $year."-".$month."-".$day;

    // today date
    $today_date = date('Y-m-d', time() + PHPR_TIMEZONE*3600);

    $retval['todo']         = "ext = ".(int)$user_ID." AND status < 4";      // For me and open
    $retval['helpdesk']     = "assigned = '$user_ID' AND status < '5'";  // For me and open

    $retval['contacts']     = "sync2 > '".$last_login."'";              // Modify time
    $retval['forum']        = "lastchange > '".$last_login."'";         // Modify time
    $retval['dateien']      = "datum > '".$last_login."'";              // Added time
    $retval['projects']     = "anfang >= '".$last_login_date."'";       // Added time
    $retval['notes']        = "div2 > '".$last_login."'";               // Modify time

    return $retval;
}

function summary_show_latest($module) {
    global $fields, $fieldlist, $user_ID, $user_kurz, $lastlogin;
    global $flist, $filter_module, $filter, $rule, $keyword, $filter_ID;
    global $build_table_records, $contextmenu;
    global $summary_perpage, $summary_show_last_login;

    $contextmenu = 0;

    $link      = 'summary';
    $fieldlist = array();

    $map_module2caption = array( 'contacts'    => __('Contacts')
                                ,'helpdesk'    => __('Helpdesk')
                                ,'filemanager' => __('Files')
                                ,'dateien'     => __('Files')
                                ,'notes'       => __('Notes')
                                ,'projects'    => __('Projects')
                                ,'todo'        => __('Todo')
                                ,'mail'        => __('Mail')
                                );

    if ($module == 'filemanager') {
        $module = 'dateien';
    }
    switch ($module){
        case"helpdesk":
        $acc="acc_read";
        break;
        default:
            $acc="acc";
            break;
    }
    if (in_array($module, array_keys($map_module2caption))) {
        $caption = $map_module2caption[$module];
    }
    else {
        $caption = 'o_'.$module;
        $caption = __("$caption");
    }

    $nrel_sess = show_nrel("$link.php?", $module);
    if ((isset($summary_perpage)) && ($summary_perpage != 0) && ($module != 'helpdesk')) {
        $anzahl    = $summary_perpage;
    } else {
        $anzahl    = $nrel_sess[$module];
    }

    $fields    = array();
    $fieldsall = build_array($module, null, 'view');
    $a = 0;
    foreach ($fieldsall as $key=>$value) {
        $fields[$key] = $value;
        $a++;
    }
	if ($filter_module == $module) {
        $where = main_filter($filter, $rule, $keyword, $filter_ID, $module,'','');
    }
    else {
        $where = main_filter('', '', '', '', $module, '','');
    }
    $filter_out = '';
    if (!empty($flist[$module])) $filter_out .= '&nbsp;'.display_filters($module, $link);
    $filter_out .= "&nbsp;&nbsp;".display_manage_filters($module, '#000000')."<br />\n";

    if ($summary_show_last_login) {
        if (isset($lastlogin[$module])) $where .= ' AND '.$lastlogin[$module];
    }
    $nwhere =  " WHERE ($acc LIKE 'system'
                        OR ((von = $user_ID
                             OR $acc LIKE 'group'
                             OR $acc LIKE '%\"$user_kurz\"%')
                            ".group_string($module)."))
                       $where ";
    switch ($module) {
        case 'mail':
            $nwhere .=  " and typ <> 'd' ORDER BY date_inserted DESC";
            break;
        case 'helpdesk':
            $nwhere .=  " ORDER BY submit DESC";
            break;
        case 'dateien':
            $nwhere .=  " ORDER BY div2, filename ASC";
            break;
        default:
            $nwhere .=  " ORDER BY div2 DESC";
    }

    $out = '<br />'.build_table(array('ID', 'von', $acc, 'parent'), $module, $nwhere, 0, $anzahl, $link, 600, false, $caption, $filter_out);

    if ($build_table_records == 0) {
        return summary_no_entries_found($caption, __('No Entries Found'));
    }
    else {
        return $out;
    }
}

function summary_insert_vote($votum_ID) {
	global $user_ID, $radiopoll, $zahl1, $zahl2, $zahl3;
	// make sure the user hasn't already voted
	$result = db_query("SELECT fertig, an
                          FROM ".DB_PREFIX."votum
                         WHERE ID = ".(int)$votum_ID) or db_die();
	$row = db_fetch_row($result);
	if (!ereg("\"$user_ID\"", $row[0])) {

		$stimme = false;
		// radiobutton?
		if (isset($radiopoll) && in_array($radiopoll, array('zahl1', 'zahl2', 'zahl3'))) {
			$votum_field = $radiopoll;
			$stimme = true;
		}
		// checkboxes?
		else {
			if (isset($zahl1)) {
				$votum_field = 'zahl1';
				$stimme = true;
			}
			else if (isset($zahl2)) {
				$votum_field = 'zahl2';
				$stimme = true;
			}
			else if (isset($zahl3)) {
				$votum_field = 'zahl3';
				$stimme = true;
			}
		}
		// no vote at all?
		if (!$stimme) $votum_field = 'kein';

		$votum_field = qss($votum_field);
		$result = db_query("UPDATE ".DB_PREFIX."votum
                               SET $votum_field = $votum_field + 1
                             WHERE ID = ".(int)$votum_ID) or db_die();

		// update list of users already voted
		$result = db_query("SELECT fertig
                              FROM ".DB_PREFIX."votum
                             WHERE ID = ".(int)$votum_ID) or db_die();
		$row = db_fetch_row($result);
		$pers = unserialize($row[0]);
		$pers[] = $user_ID;
		$fertig = serialize($pers);
		$result = db_query("UPDATE ".DB_PREFIX."votum
                               SET fertig = '$fertig'
                             WHERE ID = ".(int)$votum_ID) or db_die();
	} // close bracket from if query, whether the user already has been voted
}

/**
 * This function will check the addons directory to find summary pages and summary functions into each addon installed.
 *
 * @return (string) Output from addons summary functions
 */
function summary_show_addons() {
    $outAddons = '';

	// check whether the addon directory exists at all
    $addons_dir = dirname(__FILE__).'/../addons/';

    if(is_dir($addons_dir)){

    	// open the addon directory
        $fp = opendir($addons_dir);
        // read directory to check if summary.php exists.
        while($file = readdir($fp)){

            // but exclude links, index files, system files etc.
            if (is_dir($addons_dir.$file) && $file <> '.' && $file <> '..' && $file <> 'CVS' and !ereg('^_', $file) ) {

            	// checks if summary file exists
		        if(file_exists($addons_dir.$file."/summary.php") ) {

		        	    // header of summary section
		            	$outAddons .= '<br /><span class="modname">'.ucfirst($file).__(' Addon').__(':')."</span><br />\n";

		            	// include addon's summary file
		            	define('ADDON_DIR',$addons_dir.$file);
		                include_once(ADDON_DIR."/summary.php");

		                // if summary function was added, then lets get the string
		                $function_name = "summary_show_".$file;

		                if (function_exists($function_name)) {

		                	$outAddons .= $function_name();
		                }
		            }
                }
            }
            closedir($fp);
        }

    return $outAddons;
}

function summary_no_entries_found($caption, $message) {
    return '<h2>'.$caption.'</h2><p class="noEntries">'.$message.'</p>';
}


?>
