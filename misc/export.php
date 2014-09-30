<?php
/**
 * @package    misc
 * @subpackage export
 * @author     Albrecht Guenther, Norbert Ku:ck , $Author: polidor $
 * @licence    GPL, see www.gnu.org/copyleft/gpl.html
 * @copyright  2000-2006 Mayflower GmbH www.mayflower.de
 * @version    $Id: export.php,v 1.83 2008-01-02 02:21:53 polidor Exp $
 */

define('PATH_PRE','../');
include_once(PATH_PRE.'lib/lib.inc.php');
require_once(LIB_PATH.'/dbman_lib.inc.php');
include_once(PATH_PRE.'misc/export_ical.php');

if (!$medium) die("<html><body><div id=\"global-main\">Please select an export format!</body></html>!");

if (isset($_SESSION['saved_settings']['projects']['projectlist'])) {
    $projectlist  = $_SESSION['saved_settings']['projects']['projectlist'];
}

if (isset($_SESSION['saved_settings']['projects']['userlist'])) {
    $userlist  = $_SESSION['saved_settings']['projects']['userlist'];
}

switch ($file){
    case "timecard":
        $fields = array("datum",     "anfang",   "ende"   );
        $f_lang = array(__('Date'),__('Begin'),__('End'));
        $query = "SELECT ".implode(",",$fields)."
                    FROM ".DB_PREFIX."timecard
                   WHERE users = ".(int)$user_ID." AND
                         datum LIKE '%-$month-%' AND
                         datum LIKE '$year-%'
                ORDER BY datum";
        $export_array = make_export_array($query);
        break;

    case "timecard_admin":
        check_admin_perm();
        
        $fields = array(   "datum",   "anfang",   "ende"   );
        $f_lang = array(__('Date'),__('Begin'),__('End'), __('Hours'));
        $query = "SELECT ".implode(",",$fields)."
                    FROM ".DB_PREFIX."timecard
                   WHERE users = ".(int)$pers_ID." AND
                         datum LIKE '%-$month-%' AND
                         datum LIKE '$year-%'
                ORDER BY datum DESC";
        $export_array = make_export_array($query);
        foreach ($export_array as $tmp => $data) {
            // Keep the first
            if ($tmp == 0) {
                $index = $tmp;
                $h=0;
                $m=0;
                $bsum = 0;
                $date = $data[0];
                $data[1] = check_4d($data[1]);
                $data[2] = check_4d($data[2]);
                $bsum+=(substr($data[2],0,2) - substr($data[1],0,2))*60 + substr($data[2],2,4) - substr($data[1],2,4);
            } else {
                // Sum the same date
                if ($date == $data[0]) {
                    $data[1] = check_4d($data[1]);
                    $data[2] = check_4d($data[2]);
                    $bsum+=(substr($data[2],0,2) - substr($data[1],0,2))*60 + substr($data[2],2,4) - substr($data[1],2,4);
                    $export_array[$tmp][3] = '';
                } else {
                    // Get the sum
                    $h= floor($bsum/60);
                    $m = ($bsum - $h * 60)/60;
                    $out_sum = ereg_replace("\.",",",sprintf("%.2f",$h+$m));
                    $export_array[$index][3] = $out_sum;

                    // Start the new date
                    $index = $tmp;
                    $bsum = 0;
                    $h=0;
                    $m=0;
                    $date = $data[0];
                    $data[1] = check_4d($data[1]);
                    $data[2] = check_4d($data[2]);
                    $bsum+=(substr($data[2],0,2) - substr($data[1],0,2))*60 + substr($data[2],2,4) - substr($data[1],2,4);
                    $export_array[$tmp][3] = '';
                }
            }

            // Last date
            $h= floor($bsum/60);
            $m = ($bsum - $h * 60)/60;
            $out_sum = ereg_replace("\.",",",sprintf("%.2f",$h+$m));
            $export_array[$index][3] = $out_sum;
        }
        break;

    case "users":
        $fields = array(   "anrede",        "vorname",       "nachname",       "kurz",          "firma",   "email",   "tel1",         "tel2",         "fax",    "strasse",   "plz",         "stadt",   "land"   );
        $f_lang = array(__('Salutation'),__('First Name'),__('Family Name'),__('Short Form'),__('Company'),"Email",__('Phone')."1",__('Phone')."2",__('Fax'),__('Street'),__('Zip code'),__('City'),__('Country'));
        $query = "SELECT ".implode(",",$fields)."
                    FROM ".DB_PREFIX."users
                   WHERE $sql_user_group
                     AND is_deleted is NULL
                ORDER BY nachname";
        $export_array = make_export_array($query);
        break;

    case "contacts":
        $acc_where = "(acc_read LIKE 'system' OR ((von = ".(int)$user_ID." OR acc_read LIKE 'group' OR acc_read LIKE '%\"$user_kurz\"%') AND $sql_user_group))";
        $order = "nachname";
        list($fields, $fields_names, $f_lang, $query) = prepare_export_fields('contacts', 'contacts', $acc_where, $order, $ID_s, xss_array($_POST['export_dsts']));
        $export_array = make_export_array($query, $fields_names, $fields);
        break;

    case "projects":
        $acc_where = "(acc LIKE 'system' OR ((von = ".(int)$user_ID." OR acc LIKE 'group' OR acc LIKE '%\"$user_kurz\"%') AND $sql_user_group)) AND is_deleted is NULL";
        $order = 'name';
        list($fields, $fields_names, $f_lang, $query) = prepare_export_fields('projects', 'projekte', $acc_where, $order, $ID_s, xss_array($_POST['export_dsts']));
        $export_array = make_export_array_projects($query,0,$fields_names,$fields);
        break;

    case "bookmarks":
        $fields = array("url", "bezeichnung", "bemerkung");
        $f_lang = array("url", __('Description'), __('Comment'));
        $query = "SELECT ".implode(",",$fields)."
                    FROM ".DB_PREFIX."lesezeichen
                   WHERE gruppe = ".(int)$user_group." 
                     AND is_deleted is NULL
                ORDER BY bezeichnung";
        $export_array = make_export_array($query);
        break;

    case "timeproj":
        $fields = array(DB_PREFIX.'timeproj.datum', DB_PREFIX.'projekte.name',   DB_PREFIX.'timeproj.h',        DB_PREFIX.'timeproj.m',
        DB_PREFIX.'timeproj.note',  DB_PREFIX.'timeproj.module', DB_PREFIX.'timeproj.module_id', );
        $f_lang = array( __('Date'),  __('Project Name'), __('Hours'), __('minutes'), __('Comment'), __('Module'), __('ID'));
        $query = "SELECT ".implode(",", $fields)."
                    FROM ".DB_PREFIX."timeproj, ".DB_PREFIX."projekte
                   WHERE ".DB_PREFIX."timeproj.projekt = ".DB_PREFIX."projekte.ID AND
                         ".DB_PREFIX."timeproj.users = ".(int)$user_ID." AND
                         ".DB_PREFIX."timeproj.datum LIKE '$year-$month-%' AND
                         ".DB_PREFIX."projekte.is_deleted is NULL
                ORDER BY ".DB_PREFIX."timeproj.datum ASC";
        $export_array = make_export_array($query);
        break;

    case "project_stat":
        include_once('../projects/projects_matrix.php');
        foreach ($_SESSION['saved_settings']['projects'] as $k => $v) {
        	$GLOBALS[$k] = $v;
        }

		if (($period_select != '') && ($periodtype))
		{
			$pieces = periode_parse_selectbox_value($period_select);
			$anfang = date("Y-m-d", periode_get_start_date($pieces["type"], $pieces["cycles"]));
			$ende 	= date("Y-m-d", periode_get_end_date($pieces["type"], $pieces["cycles"]));
		}

        $export_array = array();
        $_POST['export_titles'] = "on";
        
        $query = "SELECT p.id, cc.id, cc.name
	                FROM ".DB_PREFIX."projekte p
	           LEFT JOIN ".DB_PREFIX."projekte_costunit pc
	                  ON p.ID = pc.projekte_ID
	           LEFT JOIN ".DB_PREFIX."controlling_costunits cc
	                  ON cc.id = pc.costunit_ID
	           	   WHERE p.ID IN (".implode(',', (array)$_SESSION['statistic']['projectlist']).")";

	    $result = db_query($query) or db_die();
	    $costunits = array();
		while($row = db_fetch_row($result)) {
			if (!is_null($row[1])) {
				$costunits[$row[1]] = $row[2];
			}
		}
        
        if ($_SESSION['statistic']['userlist'] and $_SESSION['statistic']['projectlist']) {
            foreach ((array)$_SESSION['statistic']['userlist'] as $person) {
                foreach ((array)$_SESSION['statistic']['projectlist'] as $project) {

                    $fields = array(DB_PREFIX.'users.vorname',DB_PREFIX.'users.nachname',DB_PREFIX.'projekte.name',DB_PREFIX.'timeproj.datum',DB_PREFIX.'timeproj.h',DB_PREFIX.'timeproj.m',DB_PREFIX.'timeproj.note',DB_PREFIX.'timeproj.module',DB_PREFIX.'timeproj.module_id');
                    $f_lang = array(__('Name'),         __('Project Name'),       __('Date'),                __('Hours'),           __('minutes'),         __('Comment'),         __('Module'),         __('ID'),__('name'), __('Costunit'));
                    $query = "SELECT ".implode(",", $fields)."
                                FROM ".DB_PREFIX."users, ".DB_PREFIX."projekte, ".DB_PREFIX."timeproj
                               WHERE ".DB_PREFIX."timeproj.projekt = ".DB_PREFIX."projekte.ID AND
                                     ".DB_PREFIX."timeproj.users = ".DB_PREFIX."users.ID AND
                                     ".DB_PREFIX."timeproj.projekt = ".(int)$project." AND
                                     ".DB_PREFIX."timeproj.users = ".(int)$person." AND
                                     ".DB_PREFIX."timeproj.datum >= '$anfang' AND
                                     ".DB_PREFIX."timeproj.datum <= '$ende' AND
                                     ".DB_PREFIX."projekte.is_deleted is NULL AND
                                     ".DB_PREFIX."users.is_deleted is NULL
                            ORDER BY ".DB_PREFIX."timeproj.datum";
                    $arr = make_export_array($query);

                    foreach ($arr as $k => $v) {
                  	  	$arr[$k][9] = "";
                  	  	$arr[$k][10] = "";
					    foreach ((array) $costunits as $id => $unit) {
	                    	$sum = ($v[4]*60 + $v[5]) * ps_get_fraction($id, $project);
	                    	if ($sum > 0) {
	                    		$h = floor($sum/60);
	                    		$m = round($sum - $h*60);
	                    		$arr[$k][10] .= $h.":".$m." (".$unit.")\n";
	                    	}
	                    }
                	}
                    $export_array = array_merge($export_array, $arr);

                }

            }
            foreach($export_array as $key=>$val){
                // tie first and last name into the first column
                $val[0] =  $val[0].' '.$val[1];
                for($i=1;$i< count($val);$i++){
                    $val[$i]=$val[$i+1];
                }
                array_pop($val);
                // if the booking has been done at the module itself we have to fetch the name of the record
                if(($val[6]<>'') and ($val[7]>0)){

                    switch($val[6]){
                        case'todo':
                        $val[8] = slookup($tablename['todo'],'remark','ID',$val[7],'1');
                        $val[6]=__('Todo');
                        break;
                        case 'helpdesk':
                            $val[8] = slookup($tablename['helpdesk'],'name','ID',$val[7],'1');
                            $val[6]=__('Helpdesk');
                            break;
                        default:
                            $val[8] = '';
                            $val[6]=__($val[6]);
                            break;
                    }

                }
                else {
                    $val[6]='-';
                    $val[7]='-';
                    $val[8]='-';
                }

                $export_array[$key]=$val;
            }
        }
        break;
    case "project_stat_date":
        $_POST['export_titles'] = "on";
    	foreach ($_SESSION['saved_settings']['projects'] as $k => $v) {
        	$GLOBALS[$k] = $v;
        }

		if (($period_select != '') && ($periodtype))
		{
			$pieces = periode_parse_selectbox_value($period_select);
			$anfang = date("Y-m-d", periode_get_start_date($pieces["type"], $pieces["cycles"]));
			$ende 	= date("Y-m-d", periode_get_end_date($pieces["type"], $pieces["cycles"]));
		}

        if ($userlist and $projectlist) {
        	$projectlist = array();
			foreach ( (array)$_SESSION['statistic']['projectlist'] as $p) {
				foreach ($p as $pid) {
					$projectlist[] = $pid;
				}
			}

		    $query = "SELECT p.id, cc.id, cc.name
		                FROM ".DB_PREFIX."projekte p
		           LEFT JOIN ".DB_PREFIX."projekte_costunit pc
		                  ON p.ID = pc.projekte_ID
		           LEFT JOIN ".DB_PREFIX."controlling_costunits cc
		                  ON cc.id = pc.costunit_ID
		           	   WHERE p.ID IN (".implode(',',$projectlist).")";

		    $result = db_query($query) or db_die();
		    $costunits = array();
			while($row = db_fetch_row($result)) {
				if (!is_null($row[1])) {
					$costunits[$row[1]] = $row[2];
				}
			}
            $f_lang[] = __('Date');
            $f_lang[] = __('Project Name');
            foreach ((array)$_SESSION['statistic']['userlist'] as $person) {
                $resultuser = db_query("SELECT vorname, nachname
                                          FROM ".DB_PREFIX."users
                                         WHERE ID = ".(int)$person."
                                           AND is_deleted is NULL") or db_die();
                $rowuser = db_fetch_row($resultuser);
                $f_lang[]=$rowuser[0]." ".$rowuser[1];
            }
            $f_lang[]=__('Sum');
            $f_lang[]=__('Costunits');

            foreach ((array)$_SESSION['statistic']['projectlist'] as $project) {
                unset($line);
                foreach ($project as $datum => $projID) {
                    if ($datum >=$anfang and $datum <= $ende) {
                        $result = db_query("SELECT name
                                              FROM ".DB_PREFIX."projekte
                                             WHERE ID = ".(int)$projID."
                                               AND is_deleted is NULL");
                        $row = db_fetch_row($result);
                        $line[] = $datum;
                        $line[] = $row[0];
                        foreach ((array)$_SESSION['statistic']['userlist'] as $person) {
                            $result2 = db_query("SELECT datum, h, m, note
                                                   FROM ".DB_PREFIX."timeproj
                                                  WHERE projekt = ".(int)$projID."
                                                    AND users = ".(int)$person."
                                                    AND datum = '$datum'
                                               ORDER BY datum")or db_die();
                            while ($row2 = db_fetch_row($result2)) {
                                $books.= $row2[1].' : '.$row2[2].'  '.$row2[3]."\n";
                                $sum1  = $sum1 + $row2[1]*60+$row2[2];
                            }
                            $line[] = $books;
                            $books = '';
                        }
                        $h = floor($sum1/60);
                        $m = $sum1 - $h*60;
                        $line[] = "$h : $m";

                        $coutput = "";
                        foreach ($costunits as $id => $unit) {
                        	$sum = $sum1*ps_get_fraction($id, $projID);
                        	if ($sum > 0) {
	                    		$h = floor($sum/60);
	                    		$m = round($sum - $h*60);
	                    		$coutput.= $h.":".$m." (".$unit.")\n";
	                    	}
                        }
                        $line[] = $coutput;
                        $sum1 = 0;
                        $export_array[] =$line;
                    }
                }
            }
        }
        break;

    case 'project_stat_costs':
        require_once(PATH_PRE.'projects/costlib.inc.php');
        $export_array = array();
        $projectlist  = $_SESSION['saved_settings']['projects']['projectlist'];
        $_POST['export_titles'] = "on";
        if ($projectlist) {
            $f_lang = array(__('Project'), __('Special costs'), __('Costs for work'), __('Sum'));
            $where  = ($projectlist[0] == 'gesamt') ? '' :  "WHERE id IN(".implode(',', xss_array($projectlist)).")";
            $query  = "SELECT id, anfang, ende FROM ".DB_PREFIX."projekte ".$where." AND is_deleted is NULL";
            $result = db_query ($query) or db_die();
            while ($row = db_fetch_row($result)) {
                $project       = $row[0];

                $special_costs = ps_specialcosts  ($project, $projectlist);
                $days          = ps_costs_daycount($project, $projectlist);
                $work_costs    = ps_workcosts     ($project, $projectlist);
                if ($days > 0) {
                    $view_end = Date_Format::get_timestamp_from_date($ende);
                    $proj_end = Date_Format::get_timestamp_from_date($row[2]);
                    $tu_end   = ($view_end > $proj_end) ? $proj_end : $view_end;

                    $view_begin = Date_Format::get_timestamp_from_date($anfang);
                    $proj_begin = Date_Format::get_timestamp_from_date($row[1]);
                    $tu_begin   = ($view_begin < $proj_begin) ? $proj_begin : $view_begin;
                    
                    $view_days     = ($tu_end - $tu_begin) / 86400;
                    $per_day_costs = $special_costs / $days;
                    // $per_day_costs = number_format($per_day_costs, 2, ',', ' ');
                    $special_costs = $per_day_costs * $view_days;
                    $sum2_costs     = $special_costs + $work_costs;
                } else {
                    $special_costs = 0;
                    $sum_costs     = $work_costs;
                }

                $export_array[] = array($row[1],
                number_format($special_costs, 2, ',', '.').' '.PHPR_CUR_SYMBOL,
                number_format($work_costs, 2, ',', '.').' '.PHPR_CUR_SYMBOL,
                number_format($sum_costs, 2, ',', '.').' '.PHPR_CUR_SYMBOL);

            }

        }

        break;

    case 'todo':
        $acc_where = "(acc LIKE 'system' OR ((von = ".(int)$user_ID." OR ext = ".(int)$user_ID." OR acc LIKE 'group' OR acc LIKE '%\"$user_kurz\"%') AND gruppe = ".(int)$user_group."))";
        $order = 'deadline';
        list($fields, $fields_names, $f_lang, $query) = prepare_export_fields('todo', 'todo', $acc_where, $order, $ID_s, xss_array($_POST['export_dsts']));
        $export_array = make_export_array($query, $fields_names, $fields);
        break;

    case "notes":
        $acc_where = "(acc LIKE 'system' OR ((von = ".(int)$user_ID." OR acc LIKE 'group' OR acc LIKE '%\"$user_kurz\"%') AND gruppe = ".(int)$user_group."))";
        $order = 'name';
        list($fields, $fields_names, $f_lang, $query) = prepare_export_fields('notes', 'notes', $acc_where, $order, $ID_s, xss_array($_POST['export_dsts']));
        $export_array = make_export_array($query, $fields_names, $fields);
        break;

    case 'calendar':
    case 'calendar_detail':
        $fields = array( "ID"         => array('filter_show' => 1),
        "event"      => array('filter_show' => 1),
        "datum"      => array('filter_show' => 1),
        "anfang"     => array('filter_show' => 1),
        "ende"       => array('filter_show' => 1),
        "remark"     => array('filter_show' => 1),
        "visi"       => array('filter_show' => 1));

        $where = '';
        if (isset($_POST['use_filters']) && ($_POST['use_filters'] == "on") ) {
            $where.= main_filter('', '', '', '', 'calendar','','');
        }

        foreach($fields as $field_name => $field) {
            $fields2[] = DB_PREFIX."termine.$field_name";
        }

        $fields_contact = array("vorname"   => array('filter_show' => 1),
        "nachname"  => array('filter_show' => 1),
        "firma"     => array('filter_show' => 1),
        "email"     => array('filter_show' => 1));
        foreach($fields_contact as $field_name => $field) {
            $fields2[] = DB_PREFIX."contacts.$field_name";
        }

        $f_lang = array('ID',
        __('Text'),
        __('Date'),
        __('From'),
        __('Until'),
        __('Remark'),
        __('Contacts').'_'.__('First Name'),
        __('Contacts').'_'.__('Family Name'),
        __('Contacts').'_'.__('Company'),
        __('Contacts').'_Email',
        __('Visibility'));

        $query = "SELECT ".implode(", ", $fields2)."
                    FROM ".DB_PREFIX."termine
                         ".special_sql_filter_flags('calendar', xss_array($_POST))."
               LEFT JOIN ".DB_PREFIX."contacts ON ".DB_PREFIX."termine.contact = ".DB_PREFIX."contacts.ID
                   WHERE ".DB_PREFIX."termine.an = ".(int)$user_ID." 
                     AND ".DB_PREFIX."termine.is_deleted is NULL
                     AND ".DB_PREFIX."contacts.is_deleted is NULL
                         $where
                         ".special_sql_filter_flags('calendar', xss_array($_POST), false);

        if ($file == "calendar_detail") {
            $query .= " AND ".DB_PREFIX."termine.ID = ".(int)$ID[0]." ";
        }

        $export_array = make_export_array($query);
        break;

    case 'filemanager':
        die('Sorry, no file export available!');
        break;

    case 'helpdesk':
        $acc_where = "(acc_read LIKE 'system' OR ((von = ".(int)$user_ID." OR assigned = '$user_ID' OR acc_read LIKE 'group' OR acc_read LIKE '%\"$user_kurz\"%') AND gruppe = ".(int)$user_group."))";
        $order = 'due_date';
        list($fields, $fields_names, $f_lang, $query) = prepare_export_fields('helpdesk', 'rts', $acc_where, $order, $ID_s, xss_array($_POST['export_dsts']));
        $export_array = make_export_array($query,$fields_names,$fields);
        break;

    case "costs":
        $acc_where = "(acc LIKE 'system' OR ((von = ".(int)$user_ID." OR acc LIKE 'group' OR acc LIKE '%\"$user_kurz\"%') AND gruppe = ".(int)$user_group."))";
        $order = 'name';
        list($fields, $fields_names, $f_lang, $query) = prepare_export_fields('costs', 'costs', $acc_where, $order, $ID_s, xss_array($_POST['export_dsts']));
        $export_array = make_export_array($query, $fields_names, $fields);
        break;

    default:
        die("You are not allowed to do this!");
}

function check_for_status_field($prefix,$string) {
    //special hack for the todo export: since we have a 'status' field in todo ANd in the users table, we have to make sure
    // that the filters will be applid correctly
    return eregi_replace('status',$prefix.'.status',$string);
}


// **********
// set header
// print and html should be shown inline, the rest delivered as attachment
if (ereg("html|print", $medium) && PHPR_DOWNLOAD_INLINE_OPTION == 1) {
    $file_download_type = "inline";
    $name = $file.".html";
}
else {
    $name = $file.".".$medium;
    $file_download_type = "attachment";
}

if (!ereg("pdf", $medium)){
    include_once(LIB_PATH."/get_contenttype.inc.php");
}

// end set header


switch ($medium) {

    // ***********************
    // iCal export
    case "ics":
        if ($file != 'calendar') die("ical/vcal export not supported for this file");
        echo export_user_cal($export_array, 'ical');
        break;

    case "vcs":
        if ($file != 'calendar' and $file != 'calendar_detail') die("ical/vcal export not supported for this file");
        echo export_user_cal($export_array, 'vcal');
        break;

        // **********
        // pdf output
    case "pdf":
        $table = array();

        if (!is_file(LIB_PATH.'/pdf/class.ezpdf.php')) die("Panic - cannot find the required pdf classes! Read the faq_install.html for the required steps to install this library or disable the pdf support in the config or choose another export format");
        else include(LIB_PATH.'/pdf/class.ezpdf.php');
        if ($file == 'contacts') {
            $wordwrap_map = array(
            0 => 4,
            1 => 8,
            2 => 8,
            3 => 14,
            4 => 12,
            5 => 17,
            6 => 17,
            7 => 17,
            8 => 20,
            9 => 12,
            10 => 8,
            11 => 12,
            12 => 20,
            13 => 12,
            14 => 14,
            15 => 17,
            16 => 14,
            17 => 10,
            18 => 10,
            19 => 10
            );
            $e_count = 0;
            foreach ($export_array as $exp_entry) {
                $c_count = 0;
                foreach ($exp_entry as $col) {
                    $export_array[$e_count][$c_count] = wordwrap ($col, $wordwrap_map[$c_count], "\n");
                    $c_count ++;
                }
                $e_count ++;
            }
            $pdf = new Cezpdf(array(0,0,1441.89,595.28), 'landscape');
        }
        else {
            $pdf = new Cezpdf('A4', 'landscape');
        }

        $pdf->selectFont(LIB_PATH.'/pdf/fonts/Helvetica');
        foreach ($export_array as $line) {
            $line2 = array();
            for ($i=0; $i < count($line); $i++) {
                $line2[replace_special_chars($f_lang[$i])] = $line[$i];
            }
            $table[] = $line2;
        }

        // Show titles
        if ($_POST['export_titles'] == "on") {
            $showHeadings = 1;
        } else {
            $showHeadings = 0;
        }

        $pdf->ezTable($table, '', 'PHProjekt export file', array('fontSize'=>8, 'showHeadings'=> $showHeadings));
        $pdf->ezStream();
        break;

        // ************
        // chart output
        // ************

    case "chart":
        break;

        // **********
        // xml output
    case "xml":
        $xmlstring = "<?xml version=\"1.0\"?>\n";
        $xmlstring .= "<table>\n";
        if ($export_array) {
            foreach ($export_array as $line) {
                $xmlstring .= "  <record>\n";
                for ($i=0; $i < count($line); $i++) {
                    $xmlstring .= "    <".wellformed($f_lang[$i])."><![CDATA[".$line[$i]."]]></".wellformed($f_lang[$i]).">\n";
                }
                $xmlstring .= "  </record>\n";
            }
        }
        $xmlstring .= "</table>";
        echo $xmlstring;
        break;

        // **********
        // rtf output
    case "rtf":
        $rtfstring = "{\\rtf1\\ansi\\ansicpg1252\\deff0\\deflang2055{\\fonttbl{\\f0\fnil\\fcharset0 Helvetica;}}\n";
        $rtfstring .= "\\viewkind4\\uc1\\pard\\f0\\fs20";

        // Show titles
        if ($_POST['export_titles'] == "on") {
            for ($i=0; $i<count($f_lang)-1;$i++) {
                $rtfstring .= "\\b $f_lang[$i]\\b0\\tab";
            }
            $rtfstring .= "\\b $f_lang[$i]\\b0\\par\n";
        }

        // Show the content
        foreach ($export_array as $line) {
            for ($i=0;$i < count($line)-1; $i++) {
                $rtfstring .= " $line[$i] \\tab";
            }
            $rtfstring .= " $line[$i]\\par\n";
        }
        $rtfstring .= "}";
        echo replace_special_chars($rtfstring);
        break;

        // **********
        // doc output - same as rtf, but only theheader is slightly different :-)
    case "doc":
        $rtfstring = "{\\rtf1\\ansi\\ansicpg1252\\deff0\\deflang2055{\\fonttbl{\\f0\fnil\\fcharset0 Helvetica;}}\n";
        $rtfstring .= "\\viewkind4\\uc1\\pard\\f0\\fs20";

        // Show titles
        if ($_POST['export_titles'] == "on") {
            for ($i=0; $i<count($f_lang)-1;$i++) {
                $rtfstring .= "\\b $f_lang[$i]\\b0\\tab";
            }
            $rtfstring .= "\\b $f_lang[$i]\\b0\\par\n";
        }

        // Show the content
        foreach ($export_array as $line) {
            for ($i=0;$i < count($line)-1; $i++) {
                if (!$line[$i]) $line[$i] = " ";
                $rtfstring .= " $line[$i]\\tab";
            }
            if (!$line[$i]) $line[$i] = " ";
            $rtfstring .= " $line[$i]\\par\n";
        }
        $rtfstring .= "}";
        echo replace_special_chars($rtfstring);
        break;

        // ****************
        // normal print page
    case "print":
        // begin body of html page and table
        echo "<html><body bgcolor=ffffff onLoad='self.print()'><div id=\"global-main\"><table border=1 cellpadding=1 cellspacing=0>\n";

        // Show titles
        if ($_POST['export_titles'] == "on") {
            echo "<tr>";
            for ($i=0; $i<count($f_lang);$i++) echo "<td>$f_lang[$i]</td>\n";
            echo "</tr>\n";
        }

        // Show the content
        foreach ($export_array as $line) {
            echo "<tr>\n";
            foreach ($line as $element) {
                if (empty($element)) {
                    echo "<td>&nbsp;</td>\n";
                } else {
                    echo "<td>".xss($element)."</td>\n";
                }
            }
            echo "</tr>\n";
        }

        // end of table and html page
        echo "</table></div></body></html>";
        break;

        // ***********************
        // xls export - very similar to csv
    case "xls":
        // begin file
        $xlsstring = pack( "ssssss", 0x809, 0x08, 0x00,0x10, 0x0, 0x0 );
        
        $a = 0;
        // Show titles
        if ($_POST['export_titles'] == "on") {
            for ($i=0; $i<count($f_lang);$i++) {
              // special patch for xl since it doesn't understand \r as the line ende
                $f_lang[$i] = str_replace("\r", "", $f_lang[$i]);
                $xlsstring .= pack( "s*", 0x0204, 8+strlen($f_lang[$i]), $a+1, $i, 0x00, strlen($f_lang[$i]) );
                $xlsstring .= $f_lang[$i];

            }
            $a = 1;
        }
        

        // Show content
        
        foreach ($export_array as $line) {
            for ($i=0;$i < count($line); $i++) {
                // special patch for xl since it doesn't understand \r as the line ende
                $line[$i] = str_replace("\r", "", $line[$i]);
                $xlsstring .= pack( "s*", 0x0204, 8+strlen($line[$i]), $a+1, $i, 0x00, strlen($line[$i]) );
                $xlsstring .= $line[$i];
            }
            $a++;
        }
        $xlsstring .= pack("ss", 0x0A, 0x00);
        echo $xlsstring;
        break;

        // ***********************
        // html - similar to print page
    case "html":
        define('FILE_SKIN',dirname(__FILE__).'/../layout/default/default.inc.php');
        include(FILE_SKIN);

        // begin body of html page and table
        echo "<html>\n<body bgcolor=".PHPR_BGCOLOR3.">\n<div id=\"global-main\">\n<table border='1' cellpadding='1' cellspacing='0'>\n";

        // Show titles
        if ($_POST['export_titles'] == "on") {
            echo "<tr bgcolor='".PHPR_BGCOLOR2."'>\n";
            for ($i=0; $i<count($f_lang);$i++) echo "<td><b>$f_lang[$i]</b></td>\n";
            echo "</tr>\n";
        }

        // Show the content
        if ($export_array) {
            foreach ($export_array as $line) {
                // alternate bgcolor
                if (($cnr/2) == round($cnr/2)) {
                    $color = PHPR_BGCOLOR1;
                    $cnr++;
                }
                else {
                    $color = PHPR_BGCOLOR2;
                    $cnr++;
                }
                echo "<tr bgcolor='$color'>\n";
                foreach ($line as $element) {
                    if (empty($element)) {
                        echo "<td>&nbsp;</td>\n";
                    } else {
                        echo "<td>".$element."</td>\n";
                    }
                }
                echo "</tr>\n";
            }
        }
        // end of table and html page
        echo "</table>\n</div>\n</body>\n</html>\n";
        break;

        // ***********************
        // default case: csv export
    default:
        // Show titles
        if ($_POST['export_titles'] == "on") {
            foreach ($f_lang as $f_lang_element) { $f_lang2[] = "\"".$f_lang_element."\"";}
            echo implode(',',$f_lang2)."\n";
        }


        // now the body
        $i=0;
        foreach ($export_array as $line) {
            foreach ($line as $element) {
                // delete end of lines in data
                $element = trim(eregi_replace("\n|\r", " ", $element));

                // mask doublequotes in data for reimport
                $element = ereg_replace('"', '""', $element);
                echo "\"$element\"";
                if ($i < (count($line)-1)) {
                    echo ",";
                    $i++;
                }
                else {
                    echo "\n";
                    $i = 0;
                }
            }
        }
        break;
}


// check whether this admin has the permission to export the timecard from this user
function check_admin_perm() {
    global $user_group, $user_type, $pers_ID;

    // 1. check: is this user an admin?
    if ($user_type!=3) die("you are not allowed to do this!");

    // 2. check for the right group - only if it is a group admin
    if ($user_group > 0) {
        // loop over all groups where the mentioned user is member
        $result = db_query("SELECT grup_ID
                              FROM ".DB_PREFIX."grup_user
                             WHERE user_ID = ".(int)$pers_ID) or db_die();
        while ($row = db_fetch_row($result)) {
            // one entry matches the group of the admin? -> fine :-)
            if ($row[0] == $user_group) $ok = 1;
        }
        // no entry found -> die ...
        if (!$ok) die("You are not allowed to do this!");
    }
}

/*
* This function make a database query and return the data in a array with an array per row.
* @param  string $query        - The query for get the data
* @param  array  $fields_names - Array with field names
* @param  Array  $fields       - Array with the data of the fields
* @return array  $export_array
*/
function make_export_array($query, $fields_names = array(), $fields = array()) {

    $result = db_query($query) or db_die();
    $export_array = array();
    while ($row = db_fetch_row($result)) {
        $line = array();
        $i = 0;
        foreach ($row as $element) {
            if (isset($fields[$fields_names[$i]])) {
                $data = get_correct_value($element,$fields[$fields_names[$i]]);
                $element = $data['value'];
            }
            $line[] = $element;
            $i++;
        }
        $export_array[] = $line;
    }
    return $export_array;
}
/*
* This function is similar to make_export_array, but recursive for sub_projects
* @param  string $query  - The query for get the data
* @param  int    $parent - The parent project
* @param  array  $fields_names - Array with field names
* @param  Array  $fields       - Array with the data of the fields
* @return array  $export_array
*/
function make_export_array_projects($query,$parent, $fields_names, $fields, $export_array=array()) {

    $result = db_query($query." AND parent = ".(int)$parent." ORDER BY name") or db_die();
    while ($row = db_fetch_row($result)) {
        $line = array();
        $i = 0;
        foreach ($row as $element) {
            if (isset($fields[$fields_names[$i]])) {
                $data = get_correct_value($element,$fields[$fields_names[$i]]);
                $element = $data['value'];
            }
            $line[] = $element;
            $i++;
        }
        $export_array[] = $line;
        $export_array = make_export_array_projects($query, $row[0], $fields_names, $fields, $export_array);
    }
    return $export_array;
}


/*
* Simple replace function
* @param  string $str - The text
* @return string      - Replaced text
*/
function wellformed($str) {
    return preg_replace('#[^a-zA-Z0-9]#', '_', $str);
}

/**
 * replace some escaped chars to "printable" chars
 *
 * @author Alex Haslberger
 * @return string $str
 */
function replace_special_chars($str) {
    $search = array(
    '°&auml;°',
    '°&ouml;°',
    '°&uuml;°',
    '°&Auml;°',
    '°&Ouml;°',
    '°&Uuml;°',
    );
    $replace = array(
    'ä',
    'ö',
    'ü',
    'Ä',
    'Ö',
    'Ü',
    );
    return (preg_replace($search, $replace, $str));
}

/**
 * Prepare some vars to use for export the data
 *
 * @param string $module            - The module to export
 * @param string $table             - Table of the module
 * @param string $acc_where         - Where for access
 * @param string $order             - Field to order by
 * @param Array  $ID_s              - Special ID to show
 * @param Array  $selected_fields   - Array with selected fields for export
 * @return Array                    - array($fields, $fields_names, $f_lang, $query)
 */
function prepare_export_fields($module, $table, $acc_where, $order, $ID_s, $selected_fields = array()) {

    global $fields;

    // first check whether all available records should be exported or just a selection
    $wherein = '';
    if ($ID_s) {
        settype($ID_s, "array");
        $wherein = " AND ID IN (".implode("','", (int)$ID_s).") ";
    }

    // fields
    $fields = build_array($module, 0, "forms");

    if (!empty($selected_fields)) {
        foreach($fields as $field_name => $field) {
            if (!in_array($field_name,$selected_fields)) {
                unset($fields[$field_name]);
            }
        }
    }

    // Sort them
    $tmp = $fields;
    $fields = array();
    if (!empty($selected_fields)) {
        foreach($selected_fields as $field_name) {
            $fields[$field_name] = $tmp[$field_name];
        }
    }

    // where
    if (isset($_POST['use_filters']) && ($_POST['use_filters'] == "on") ) {
        if ($module == "todo") {
            $where = check_for_status_field('',main_filter('','','','', $module,'',''));
        } else {
            $where = main_filter('','','','', $module,'','');
        }
    }

    // fields_names and f_lang
    $fields_names = array('ID');
    $f_lang       = array('ID');
    foreach($fields as $field_name => $field) {
        if ($field_name != 'ID') {
            $fields_names[] = $field_name;
            $f_lang[] = enable_vars($field['form_name']);
        }
    }

    $where = $acc_where." ".$where." ".$wherein;
    $query = "SELECT ".implode(",", $fields_names)."
                FROM ".DB_PREFIX."$table
                         ".special_sql_filter_flags($module, xss_array($_POST))."
                   WHERE $where
                         ".special_sql_filter_flags($module, xss_array($_POST), false);

    if ($module != 'projects') {
        $query .= "ORDER BY $order";
    }

    return array($fields, $fields_names, $f_lang, $query);
}
?>
