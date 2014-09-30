<?php

// projects_stat.php - PHProjekt Version 5.2
// copyright  ©  2000-2005 Albrecht Guenther  ag@phprojekt.com
// www.phprojekt.com
// Author: Albrecht Guenther, $Author: alexander $
// $Id: projects_stat.php,v 1.73.2.1 2007/01/24 15:59:52 alexander Exp $

// check whether the lib has been included - authentication!
if (!defined("lib_included")) die("Please use index.php!");

// show error messages
if ($action == "calc" && $do!="displaySavedSetting") {
	if (!$userlist[0]) {
		$err    = "<b>".__('Please choose at least one person')."</b><br /><br />";
		$action = "";
	}
	if (!$projectlist[0]) {
		$err    = "<b>".__('Please choose at least one project')."</b><br /><br />";
		$action = "";
	}
}

if ($action == "calc" && $do=="displaySavedSetting") {
	if(empty ($savedSetting)) {
		$serr = "<b>".__("You have to choose a saved setting")."</b><br /></br />";
		$action = "";
	}
}

// show input box only if the table shouldn't be shown ...
if (($action <> "calc") || ($do == "displaySavedSetting" && isset($setting_apply))) {
    if ($_REQUEST['use_sort'] == "on")  {
        $f_sort['project_statistic']['sort'] =  xss($f_sort['projects']['sort']);
        $f_sort['project_statistic']['direction'] =  xss($f_sort['projects']['direction']);
    }
    else{
        $f_sort['project_statistic']['sort'] = 'next_proj,name';
        $f_sort['project_statistic']['direction'] = 'ASC';
    }
    // get the save settings
    if(($do == "displaySavedSetting") && (empty($serr)) && (intval($savedSetting)!= 0)) {
		$result = db_query(sprintf("
					SELECT pse.name, pse.startDate, pse.endDate, pse.withBooking, pse.withComment, pse.sortBy,
						   psp.projekt_ID, psu.user_ID,  pse.isAllProjects, pse.isAllUsers,
                           pse.show_group, pse.period
					  FROM ".DB_PREFIX."projekt_statistik_einstellungen pse
				 LEFT JOIN ".DB_PREFIX."projekt_statistik_projekte psp ON psp.stat_einstellung_ID = pse.id
				 LEFT JOIN ".DB_PREFIX."projekt_statistik_user psu ON psu.stat_einstellung_ID = pse.id
					 WHERE pse.id = %d", (int) $savedSetting));
		$i = 0;
		while($row = db_fetch_row($result)) {
			$i++;
			$anfang = $row[1];
			$ende = $row[2];
			$showbookingdates = ($row[3]) ? "on" : "";
			$showbookingnotes = ($row[4]) ? "on" : "";
			$display = $row[5];
			$projects[$row[6]] = $row[6];
			$users[$row[7]] = $row[7];
			$isAllProjects = ((boolean)$row[8]) ? true : false;
			$isAllUsers	= ((boolean)$row[9]) ? true : false;
            $show_group = $row[10];
            $period_select = $row[11];
		}

		$projectlist = $userlist = array();

		if($isAllProjects) $projectlist[0]  = "gesamt";
		else               $projectlist 	= array_keys($projects);

		if($isAllUsers) $userlist[0] = "gesamt";
		else            $userlist    = array_keys($users);

    // default values
	} else {
        if (isset($_SESSION['projectlist'])) $projectlist = $_SESSION['projectlist'];
        else                                 $projectlist = $projectlistsave;

        if (isset($_SESSION['userlist']))    $userlist = $_SESSION['userlist'];
        else                                 $userlist = $userlistsave;

        if (isset($_SESSION['statistic']['period']) && !empty($_SESSION['statistic']['period'])) {
		    $pieces = periode_parse_selectbox_value($_SESSION['statistic']['period']);
    		$anfang = date("Y-m-d", periode_get_start_date($pieces["type"], $pieces["cycles"]));
	    	$ende 	= date("Y-m-d", periode_get_end_date($pieces["type"], $pieces["cycles"]));
            $period_select = $_SESSION['statistic']['period'];
    	} else {
            if (isset($_SESSION['statistic']['startDate'])) {
                $anfang = $_SESSION['statistic']['startDate'];
            }
            if (isset($_SESSION['statistic']['endDate'])) {
                $ende = $_SESSION['statistic']['endDate'];
            }
        }

        if (isset($_SESSION['statistic']['withBooking'])) {
            $showbookingdates = $_SESSION['statistic']['withBooking'];
        }

        if (isset($_SESSION['statistic']['withComment'])) {
            $showbookingnotes = $_SESSION['statistic']['withComment'];
        }

        if (isset($_SESSION['statistic']['sortBy'])) {
            $display = $_SESSION['statistic']['sortBy'];
        }

        if (isset($_SESSION['statistic']['isAllProjects'])) {
            $isAllProjects = $_SESSION['statistic']['isAllProjects'];
        }

        if (isset($_SESSION['statistic']['isAllUsers'])) {
            $isAllUsers = $_SESSION['statistic']['isAllUsers'];
        }

        if (isset($_SESSION['statistic']['show_group'])) {
            $show_group = $_SESSION['statistic']['show_group'];
        }

        // set default date
        if (!$day)   $day   = date("d");
        if (!$month) $month = date("m");
        if (!$year)  $year  = date("Y");
	}
	// tabs
	$tabs = array();
	$output .= '<div id="global-header">';
	$output .= get_tabs_area($tabs);
	$title = isset($mode2) && $mode2 == 'mystat' ? 'My Statistic' : 'Statistics';
	$output .= breadcrumb($module, array(array('title'=>__($title))));
    $output .= '</div>';
    $output .= $content_div;
	unset($title);

	// button bar
	$buttons = array();
	$buttons[] = array('type' => 'link', 'href' => 'projects.php?mode=forms&amp;action=new'.$sid, 'text' => __('New'), 'active' => false);
	$buttons[] = array('type' => 'link', 'href' => 'projects.php?mode=options'.$sid, 'text' => __('Options'), 'active' => false);
	$buttons[] = array('type' => 'link', 'href' => 'projects.php?mode=stat'.$sid, 'text' => __('Statistics'), 'active' => ((isset($mode2) && $mode2 == 'mystat')) ? false : true);
	$buttons[] = array('type' => 'link', 'href' => 'projects.php?mode=stat&amp;mode2=mystat'.$sid, 'text' => __('My Statistic'), 'active' => ((isset($mode2) && $mode2 == 'mystat')) ? true : false);
	$buttons[] = array('type' => 'link', 'href' => 'projects.php?mode=gantt'.$sid, 'text' => __('Gantt'), 'active' => false);
	$buttons[] = array('type' => 'link', 'href' => 'projects.php?type='.$type.'&amp;mode=view', 'text' => __('List View'), 'active' => false);
	$output .= get_buttons_area($buttons);
    $output .= '
    <br />
    <a name="content"></a>
    <form action="projects.php" method="post">
    <fieldset>
    <input type="hidden" name="mode" value="stat" />
    <input type="hidden" name="mode2" value="'. $mode2 .'" />
    <legend>'.__('Project summary').'</legend>
    <label class="label_block" for="projectlist">'.__('Saved Settings').':</label>'.xss($serr).'
        '.show_saved_statistic_settings($savedSetting).'
        '.get_buttons(array(
            array("type" => "submit", "name" => "setting_ok", "value" => __("OK"), "active" => false),
            array("type" => "submit", "name" => "setting_apply", "value" => __("Apply"), "active" => false))).'
    </fieldset>
    </form>

    <form action="./projects.php" method="post" name="show_group_sel">
    <fieldset>
    <legend>'.__('Filter configuration').'</legend>
    <input type="hidden" name="mode" value="stat" />
    <input type="hidden" name="mode2" value="'.$mode2.'" />';

	if($mode2=="mystat"){
	    $checked=$unchecked='';
	    if($_REQUEST['show_group']==1)$checked="checked='checked'";
	    else $unchecked="checked='checked'";
	    $output .="
        <input type='radio' name='show_group' id='show_group1' value='0' $unchecked onchange='document.show_group_sel.submit();'><label for='show_group1'>".__('show all projects')."</label><br />
	    <input type='radio' name='show_group' id='show_group2' value='1' $checked onchange='document.show_group_sel.submit();'><label for='show_group2'>".__('only show group projects')."</label><br /><br />";
	}


	$fields_required= array(__('Consider current set filters')=>'use_filters',
                            __('Exclude archived elements')=>'exclude_archived',
                            __('Exclude read elements')=>'exclude_read',
                            __('Consider order from listview')=>'use_sort');
	$output .= get_special_flags($fields_required,$_REQUEST,"onchange='document.show_group_sel.submit();'" );
    $output .= '
    <noscript><br />'.get_buttons(array(array('type' => 'submit', 'value' => __('OK'), 'active' => false))).'</noscript>
    </fieldset></form>';
	if(!isset($_REQUEST['show_group'])) $show_group = $_SESSION['statistic']['show_group'];
	// begin input table
	// show boxes for start and end time
	// first time call: give default values
	if (!$start_day)   $start_day   = "01";
	if (!$start_month) $start_month = "01";
	if (!$start_year)  $start_year  = date("Y");
	if (!$end_day)     $end_day     = date("d");
	if (!$end_month)   $end_month   = date("m");
	if (!$end_year)    $end_year    = date("Y");

    // value of the radio button
    if ($period_select != '') {
        $checked_period = "checked='checked'";
        $checked_date = "";
    } else {
        $checked_date = "checked='checked'";
        $checked_period = "";
    }

	// start day value
	$saveDateAnfang = (!$anfang) ? "$start_year-$start_month-$start_day" : $anfang;
	$saveDateEnde	= (!$ende) ? "$end_year-$end_month-$end_day" : $ende;

    $output .= $err;
    $date_format_text = __('Date format').': '.$date_format_object->get_user_format();
    $output .= "
    <form action='./projects.php' method='post' name='frm' onsubmit=\"return checkUserDateFormat('anfang','".__('Begin').':\n'.$date_format_text."') && checkUserDateFormat('ende','".__('End').':\n'.$date_format_text."') && checkDates('anfang','ende','".__('Begin > End')."!');\">
    <fieldset>
    <legend>".__('Filter configuration')."</legend>
    ";
	if (SID) $output .= "<input type='hidden' name='".session_name()."' value='".session_id()."' />\n";
    $output .= "
	<input type='hidden' name='mode' value='stat' />
	<input type='hidden' name='show_group' value='$show_group' />
	<input type='hidden' name='use_filters' value='$use_filters' />
	<input type='hidden' name='exclude_archived' value='$exclude_archived' />
	<input type='hidden' name='exclude_read' value='$exclude_read' />
	<input type='hidden' name='mode2' value='$mode2' />
	<input type='hidden' name='action' value='calc' />
    <fieldset>
    <legend>".__('period')."</legend>
	<input type='radio' name='periodtype' id='periodtype0' value='0' $checked_date><label for='periodtype0'>".__('individual period')."</label>
    <br /><label class='label_block' for='anfang'>".__('Begin:')."</label>
    <input type='text' name='anfang' id='anfang' ".$date_format_object->get_maxlength_attribute()." ".$date_format_object->get_title_attribute()." ".dojoDatepicker('anfang', $saveDateAnfang)." />
    <br /><label class='label_block' for='ende'>".__('End:')."</label>
    <input type='text' name='ende' id='ende' ".$date_format_object->get_maxlength_attribute()." ".$date_format_object->get_title_attribute()." ".dojoDatepicker('ende', $saveDateEnde)." />
    <br style='clear:both'/><input type='radio' name='periodtype' id='periodtype1' value='1' $checked_period><label for='periodtype1'>".__('fixed period')."</label>
    <br /><label class='label_block' for='period_select'>".__('period').":</label>".periode_get_date_selectbox("period_select",$period_select,'onchange="document.forms.frm.periodtype1.checked=true;"')."<br />
    </fieldset>
    <br />
    <div style='float:left;margin-right:30px'>
    <label class='label_inline' for='projectlist'>".__('Projects').":</label><br />
    <select style='float:left;min-width:200px;' name='projectlist[]' id='projectlist' multiple='multiple' size='20'>
    <option value='gesamt'
    ";
    if ($projectlist[0]=='gesamt' || $_SESSION['statistic']['isAllProjects']) $output .= ' selected="selected"';
    $output .= ">".__('All')."</option>";
    show_projects("0",$show_group);
    $output .= "
    </select>
    </div>

    <div>
    <label for='userlist'>".__('Persons').":</label><br />";
	if ($mode2 == "mystat") {
		$output .= "<input type='hidden' name='userlist[]' id='userlist' value='$user_ID' />\n";
		$output .= "$user_name, $user_firstname<br style='clear:both'/>\n";
	}
	else {
		$output .= "<select style='min-width:200px;' name='userlist[]' id='userlist' multiple='multiple' size='20'>\n";
		// option 'all users' only available for usrs with chief status
		if ($user_type==2){
			$output .= "<option value='gesamt'";
			if ($userlist[0]=='gesamt' || $_SESSION['statistic']['isAllUsers']) $output .= ' selected="selected"';
			$output.= ">".__('All')."</option>\n";
		}

		// fetch all users from this group
		$result2 = db_query("SELECT ".DB_PREFIX."users.ID, nachname, vorname, kurz
                               FROM ".DB_PREFIX."users, ".DB_PREFIX."grup_user
                              WHERE ".DB_PREFIX."users.ID = user_ID
                                AND grup_ID = ".(int)$user_group." 
                           ORDER BY nachname") or db_die();
		while ($row2 = db_fetch_row($result2)) {
			// list them only if 1. the user is yourself, 2. you are an user with chief status or 3. you are leader of at least one project :-)
			if ($user_kurz == $row2[3] or $user_type==2 or $leader) {
				$output .= "<option value='$row2[0]'";
				if ($userlist[0] > 0 and in_array($row2[0], $userlist) && !$_SESSION['statistic']['isAllUsers']) $output .= " selected='selected'";
				$output .= ">$row2[1], $row2[2]</option>\n";
			}
		}
		$output .= "</select></div>\n";
	}



	// show check boxes for bookings
	// dates ...
	if ($showbookingdates == "on") $showbookingdatesflag = " checked='checked'";
	$output .= "<br /><input type='checkbox' name='showbookingdates' $showbookingdatesflag /> ".__('Show bookings')."\n";
	// ... and additionally the notes
	if ($showbookingnotes == "on") $showbookingnotesflag = " checked='checked'";
	$output .= "<input type='checkbox' name='showbookingnotes' id='showbookingnotes' $showbookingnotesflag /> <label for='showbookingnotes'>".__('remark')."</label>";
	if (!$display) $display = 'normal';
	if      ($display == "normal") {
        $normalflag = " checked='checked'";
        $dateflag = "";
    } elseif ($display == "date") {
        $dateflag = " checked='checked'";
        $normalflag = "";
    }
	$output .= "<br />".__('sort by').": <input type='radio' name='display' value='normal' id='normal' $normalflag> <label for='normal'>".__('Project')."</label>";
	$output .= " <input type='radio' name='display' value='date' id='date' $dateflag> <label for='date'>".__('Date')."</label> <br />\n";
	$output .= "<br />".get_buttons(array(array('type' => 'submit', 'value' => __('OK'), 'active' => false)))."\n";

	// end of input table
	unreg_sess_var("projectlist");
	unreg_sess_var("userlist");

    $output .= "
    </fieldset>
    </form>
    ";
}

//******************
//statistic list
// *****************

else if (($projectlist[0] and $userlist[0]) || $do=="displaySavedSetting") {
    // from the popup
	if($do == "displaySavedSetting" && empty ($serr)) {
		$result = db_query(sprintf("
					SELECT pse.name, pse.startDate, pse.endDate, pse.withBooking, pse.withComment, pse.sortBy,
						   psp.projekt_ID, psu.user_ID,  pse.isAllProjects, pse.isAllUsers,
                           pse.show_group, pse.period
					  FROM ".DB_PREFIX."projekt_statistik_einstellungen pse
				 LEFT JOIN ".DB_PREFIX."projekt_statistik_projekte psp ON psp.stat_einstellung_ID = pse.id
				 LEFT JOIN ".DB_PREFIX."projekt_statistik_user psu ON psu.stat_einstellung_ID = pse.id
					 WHERE pse.id = %d", (int) $savedSetting));
		$i = 0;
		while($row = db_fetch_row($result)) {
			$i++;
			$anfang = $row[1];
			$ende = $row[2];
			$showbookingdates = ($row[3]) ? "on" : "";
			$showbookingnotes = ($row[4]) ? "on" : "";
			$display = $row[5];
			$projects[$row[6]] = $row[6];
			$users[$row[7]] = $row[7];
			$isAllProjects = ((boolean)$row[8]) ? true : false;
			$isAllUsers	= ((boolean)$row[9]) ? true : false;
            $show_group = $row[10];
            $period_select = $row[11];
            if($period_select!='')$periodtype=1;
		}

		$projectlist = $userlist = array();

		if($isAllProjects)  $projectlist[0] = "gesamt";
		else                $projectlist 	= array_keys($projects);

		if($isAllUsers) $userlist[0] = "gesamt";
		else            $userlist    = array_keys($users);
	}

	if (($period_select != '') && ($periodtype))
	{
		$pieces = periode_parse_selectbox_value($period_select);
		$anfang = date("Y-m-d", periode_get_start_date($pieces["type"], $pieces["cycles"]));
		$ende 	= date("Y-m-d", periode_get_end_date($pieces["type"], $pieces["cycles"]));
	}

	if(is_array($projectlist[0])) $projectlist = $projectlistsave;
	$projectlistsave = $projectlist;
	$userlistsave = $userlist;
	$_SESSION['projectlistsave'] =& $projectlistsave;
	$_SESSION['userlistsave']    =& $userlistsave;

	//tabs
	$tabs = array();
	if      ($display=='normal') $tmp = get_export_link_data('project_stat', false);
	else if ($display=='date')   $tmp = get_export_link_data('project_stat_date', false);
	$tabs[] = array('href' => $tmp['href'], 'active' => $tmp['active'], 'id' => 'tab4', 'target' => '_self', 'text' => $tmp['text'], 'position' => 'right');


    $output .= '<div id="global-header">';
	$output .= get_tabs_area($tabs);

	if ( $userlist[0] == $user_ID && count($userlist) == 1) {
        $output .= breadcrumb($module, array(array('title'=>__('My Statistic'))));
    } else {
        $output .= breadcrumb($module, array(array('title'=>__('Statistic'))));
    }
    $output .= '</div>';
    $output .= $content_div;

	// button bar
	$buttons = array();
	$buttons[] = array('type' => 'text','text' =>manage_project_statistic_button(), 'active' => false);
	$buttons[] = array('type' => 'link', 'href' => 'projects.php?mode=view '.$sid, 'text' => __('List View'), 'active' => false);
	$buttons[] = array('type' => 'submit', 'name' => __('Back to statistic form'), 'value' => __('Back to statistic form'), 'active' => false);
	$output .= '<form action="projects.php" method="post">
                <input type="hidden" name="mode" value="stat" />
		        <input type="hidden" name="mode2" value="'. $mode2 .'" />
                <input type="hidden" name="do" value="displaySavedSetting"/>
                <input type="hidden" name="savedSetting" value="'.$savedSetting.'"/>';
	$output .= get_buttons_area($buttons);
	$output .="</form>\n";
    $output .= '<br /><h1>'.__('Statistics').'</h1>';
	// title
	$output .= $err;
	$output .= "<br /> ".__('Begin').": ".$date_format_object->convert_db2user($anfang).", ";
	$output .= __('End').": ".$date_format_object->convert_db2user($ende)."<br /><br />\n";

	// check whether the given values are valid dates
	if (!checkdate(substr($anfang,5,2), substr($anfang,8,2), substr($anfang,0,4))) die(__('Please check the date!')." <br />".__('back')." ...");
	if (!checkdate(substr($ende,5,2),   substr($ende,8,2),   substr($ende,0,4)))   die(__('Please check the date!')." <br />".__('back')." ...");
	if ($ende < $anfang) {
		die(__('Please check start and end time! '));
	}

	// fetch all projects
	if($projectlist[0] == "gesamt") $isAllProjects = true;

	if($userlist[0] == "gesamt")    $isAllUsers = true;

	// get Projects for option"all projects"
	if($isAllProjects==true){
	    unset($projectlist);
	    $before_where = special_sql_filter_flags('projects', xss_array($_POST));
	    $where="";
	    $where = special_sql_filter_flags('projects', xss_array($_POST), false);
	    if (isset($_POST['use_filters']) && ($_POST['use_filters'] == "on") ) {
	        $where.= main_filter('', '', '', '', 'projects','','');
	    }
	    if ($mode2== "mystat" and $show_group!=1) {
	        $query= "SELECT ID, chef
                            FROM ".DB_PREFIX."projekte $before_where
	                        WHERE 1=1 $where".sort_string('project_statistic');
	    }
	    else {
	        $query= "SELECT ID, chef
                            FROM ".DB_PREFIX."projekte $before_where
                            WHERE $sql_user_group $where".sort_string('project_statistic');
	    }
	    $result = db_query($query) or db_die();
	    while ($row = db_fetch_row($result)){

	        $is_part=user_is_part($row[0],$user_ID);
	        if ($is_part or $row[1] == $user_ID or $user_type==2) {
	            $projectlist[] = $row[0];
	        }
	    }

	}
	// get children,parents
	$foundrecords      = array();
	$allrecords        = array();
	$children          = array();
    $noprojectsmessage = __('Sorry but the project list is empty!');

	if ($projectlist[0] == '') die($noprojectsmessage);
	$query = "SELECT ID,parent
                     FROM ".DB_PREFIX."projekte
                     WHERE ID in (".implode(',',$projectlist).")
                     ORDER BY name";
	$result = db_query($query) or db_die();
	while ($row2 = db_fetch_row($result)) {
		$row[$row2[0]] = $row2;
		// build array of children
		if ($row2[1] > 0) {
			$children[$row2[1]][] = $row2[0];
			$parent[$row2[0]]=$row2[1];
		}
		// depending on tree view or flat view (due to filter setting) add the record to the list
		$allrecords[] = $row2[0];
	}
	//strip foundrecords from children which are already listed with parentproject
	foreach($allrecords as $key=>$kid){
		if($parent[$kid]>0 and (in_array($parent[$kid],$allrecords))){}
		else $foundrecords[$key]=$kid;
	}

	//get children and parents

	// fetch all projects
	if ($display != 'normal') {
		// where Klausel für Personen!
		$where_person = array();
		if ($userlist[0] != "gesamt") {
			foreach ($userlist as $person) {
				$where_person[] = "t.users='$person'";
			}
			unset($person);
		}
		$where_person = (0 == count($where_person)) ? "1=1" : implode(" or ", $where_person);
		// $where_person is now a boolean expression
		$where = '';
		foreach ($projectlist as $project) {
			$where .= "projekt='$project' or ";
		}
		if ($where <> '') $where .= "1!=1";
		unset($projectlist);
		$query = "SELECT datum, p.ID
                        FROM ".DB_PREFIX."projekte AS p,".DB_PREFIX."timeproj AS t
                       WHERE p.ID = t.projekt AND datum >= '$anfang'
                         AND datum <= '$ende'
                         AND $sql_user_group AND ($where) AND ($where_person)
                    GROUP BY p.name, datum
                    ORDER BY datum, p.name";
		$result = db_query($query) or db_die();
		$i = 0;
		while ($row = db_fetch_row($result)) {
			$projectlist[$i][$row[0]] = $row[1];
			$i++;
		}
	}
	// fetch all users from this group
	if ($userlist[0] == "gesamt") {
		unset($userlist);
		$result = db_query("SELECT user_ID
                              FROM ".DB_PREFIX."grup_user, ".DB_PREFIX."users
                             WHERE grup_ID = ".(int)$user_group." 
                               AND ".DB_PREFIX."users.ID = user_ID
                          ORDER BY nachname") or db_die();
		while ($row = db_fetch_row($result)) $userlist[] = $row[0];
	}
	// begin output table
	$output .= " <table>";

	if ($display=='normal') $output .= "<thead><tr><th><b>".__('Project Name')."</b></th>\n";
	else $output .= "<thead><tr><th><b>".__('Date')."</b></th><th><b>".__('Project Name')."</b>\n";

	// first row: the users!
	foreach ($userlist as $person) {
		$result = db_query("SELECT kurz
                              FROM ".DB_PREFIX."users
                             WHERE ID = ".(int)$person) or db_die();
		$row = db_fetch_row($result);
		$output .= "<th>$row[0]</th>\n";
	}
	// end of the first row - display string 'sum'
	$output .= "<th><b>".__('Sum')."</b>\n</th>";
	if($display=='normal')$output .= "<th><b>".__('sum of subprojects')."</b>\n</th>";
	$output .= "<th><b>".__('Project Name')."</b></th></tr></thead><tbody>\n";
	$cnr = 0;
	// now loop over project list

    if (is_array($projectlist)) {
    	foreach ($projectlist as $project) {
	    	// print project name
		    if ($display == 'normal') {
    			if(in_array($project,$foundrecords)){
	    			$output .=list_projects($project);
		    	}
    		}
	    	else {
		    	foreach($project as $datum => $projID){
			    	if(in_array($projID,$foundrecords)or(!in_array(array($datum=>$parent[$projID]),$projectlist))){
				    	$output .=list_date_projects($projID,$datum);
    				}
	    		}
    		}
        }
	}

	// last row: show sums for the projects
	$output .= "</tbody><tfoot>";

	$output .= "
    	<tr><td></td>";
	if ($display!='normal') $output .='<td></td>';
	foreach ($userlist as $person) {
		$result = db_query("SELECT kurz
                              FROM ".DB_PREFIX."users
                             WHERE ID = ".(int)$person) or db_die();
		$row = db_fetch_row($result);
		$output .= "<td align='center'>$row[0]</td>";
	}
	if ($display == 'normal') $output.="<td></td>";
	$output.="<td></td><td></td></tr>\n";
	$output .= "<tr><td><b>".__('Sum')."</b></td>\n";
	if ($display!='normal') $output .='<td></td>';
	foreach ($userlist as $person) {
		$h = floor($sumperson[$person]/60);
		$m = $sumperson[$person] - $h*60;
		// sum up for totalsum
		$totalsum += $sumperson[$person];
		$output .= "<td><b>$h : $m</b></td>";
	}

	// display total sum in the last cell of the table and close the table
	$h = floor($totalsum/60);
	$m = $totalsum - $h*60;

	$output .= "<td><b>$h : $m</b></td><td>&nbsp;</td>\n";
	if($display=='normal')
	$output .= "<td>&nbsp;</td></tr>\n";
	$output .= "</tfoot></table>";

	// register the pojectlist and the participants so the user won't have to select them again
	$_SESSION['projectlist'] =& $projectlist;
	$_SESSION['userlist']    =& $userlist;
	$_SESSION['statistic'] = array(	"userlist" 		=> $userlist,
	"projectlist" 	=> $projectlist,
	"startDate" 	=> $anfang,
	"endDate" 		=> $ende,
	"withBooking"	=> ($showbookingdates=="on")?true:false,
	"withComment"	=> ($showbookingnotes=="on")?true:false,
	"sortBy"		=> $display,
	"isAllProjects"	=> $isAllProjects,
	"isAllUsers"	=> $isAllUsers,
	"show_group"    => $show_group,
	"period"        => ($periodtype)? $period_select : ''
	);
}

echo $output;

function fetch_bookings_with_subprojects($project, $userlist, $anfang, $ende)
{
	$subprojects = get_node_with_children("projekte", $project);

	if(count($subprojects) == 0)
	return 0;

	$result = db_query("SELECT datum, h, m, users
                          FROM ".DB_PREFIX."timeproj
                         WHERE projekt IN(".implode(",",$subprojects).")
                           AND users IN(".implode(",",$userlist).")
                           AND datum >= '$anfang'
                           AND datum <= '$ende'
                      ORDER BY datum") or db_die();

	$sum = array();

	while($row = db_fetch_row($result)) {
		$sum[$row[3]] += $row[1]*60+$row[2];
	}

	return $sum;
}

function get_subproject_times($project,$datum)
{
	$result = db_query("SELECT datum, h, m, users
                          FROM ".DB_PREFIX."timeproj as t, ".DB_PREFIX."projekte as p
                         WHERE t.projekt=p.ID AND p.parent='$project' and t.datum='$datum'
                      ORDER BY datum") or db_die();

	$sum = array();

	while($row = db_fetch_row($result)) {
		$sum[$row[3]] += $row[1]*60+$row[2];
	}

	return $sum;
}

function fetch_bookings($project, $person) {
	global $anfang, $ende, $sumperson, $sumproject, $showbookingdates, $showbookingnotes;

	// start table cell

	// open the table of bookings if flag is set
	$out = '<td valign="bottom">';
	// fetch values from time card bookings  the bookings are between start and end time
	$result = db_query("SELECT datum, h, m, note
                          FROM ".DB_PREFIX."timeproj
                         WHERE projekt = ".(int)$project." 
                           AND users = ".(int)$person." 
                           AND datum >= '$anfang'
                           AND datum <= '$ende'
                      ORDER BY datum");
	while ($row = db_fetch_row($result)) {
		// detailed booking display?
		if ($showbookingdates) {
			$out .= "<div style='float:left; padding-right:10px;'>$row[0] - $row[1] : $row[2]</div>";
			if ($showbookingnotes) $out.= "<div align='right'>$row[3]&nbsp;</div>";
			$out .= "<br style='clear:both;' />\n";
		}
		// sum up

		$sum1  = $sum1 + $row[1]*60+$row[2];
		$datum = $row[0];
	}

	// close the table of bookings if flag is set
	// build hours and minutes of the sum and display it
	$h = floor($sum1/60);
	$m = $sum1 - $h*60;
	//if (!$showbookingdates)
	$out .= "<b>$h : $m</b>";
	$out .= "</td>\n";

	// add sum to the overall sum of this person
	$sumperson[$person]   += $sum1;
	$sumproject[$project] += $sum1;
	return  $out;
}

function fetch_date_bookings($project, $person, $datum, $i) {
	global $anfang, $ende, $show_bookings, $sumperson, $sumdatum, $showbookingdates, $showbookingnotes;

	// start table cell

	// open the table of bookings if flag is set
	$out='<td valign="bottom">';
	// fetch values from time card bookings  the bookings are between start and end time
	$result = db_query("SELECT datum, h, m, note
                          FROM ".DB_PREFIX."timeproj
                         WHERE projekt = ".(int)$project." 
                           AND datum = '$datum'
                           AND users = ".(int)$person." 
                      ORDER BY datum");
	while ($row = db_fetch_row($result)) {
		// detailed booking display?
		if ($showbookingdates) {
			$out .= "<div style='float:left; padding-right:10px;'>$row[0] - $row[1] : $row[2]</div>";
			if ($showbookingnotes)$out.= "<div align='right'>$row[3]&nbsp;</div>";
			$out .= "<br style='clear:both;'/>\n";
		}
		// sum up

		$sum1  = $sum1 + $row[1]*60+$row[2];
		$datum = $row[0];
	}

	// close the table of bookings if flag is set
	// build hours and minutes of the sum and display it
	$h = floor($sum1/60);
	$m = $sum1 - $h*60;
	//if (!$showbookingdates)
	$out .= "<b>$h : $m</b>";
	$out .= "</td>\n";

	// add sum to the overall sum of this person
	$sumperson[$person]        += $sum1;
	$sumdatum[$datum.$project] += $sum1;

	return $out;
}

// register the pojectlist and the participants so the user won't have to select them again
#reg_sess_vars(array("projectlist","userlist"));


function show_projects($parent_ID,$show_group=0) {
	global $indent, $user_ID, $leader, $sql_user_group, $projectlist, $mode2, $output, $user_type;
	// fetch parent project
	  $before_where = special_sql_filter_flags('projects', $_REQUEST);
	  $where="";
	  $where = special_sql_filter_flags('projects', $_REQUEST, false);
        if (isset($_POST['use_filters']) && ($_POST['use_filters'] == "on") ) {
            $where.= main_filter('', '', '', '', 'projects','','');
        }

	// 1. case myprojects - independent from the group
	if (($mode2== "mystat") and $show_group<>'1') {
	    $query="SELECT ID, name, chef
                       FROM ".DB_PREFIX."projekte $before_where
                       WHERE parent = ".(int)$parent_ID." $where".sort_string('project_statistic');
	}
	// 2. case: query as project leader or chef - only for the group
	else {
	    $query ="SELECT ID, name, chef
                        FROM ".DB_PREFIX."projekte $before_where
                        WHERE parent = ".(int)$parent_ID." $where
                        AND $sql_user_group".sort_string('project_statistic');
	}
	$result = db_query($query) or db_die();
	while ($row = db_fetch_row($result)) {
		// identify user as project leader, flag for later use at users list
		$leader=0;
		if ($row[2] == $user_ID) $leader = 1;
		$is_part=user_is_part($row[0],$user_ID);
		if ($is_part or $leader==1 or $user_type==2) {
			$output .= "<option value='$row[0]'";
			if ($projectlist[0] and in_array($row[0], $projectlist) && !$_SESSION['statistic']['isAllProjects']) $output.= ' selected="selected"';
			$output .= ">";
			for ($i = 1; $i <= $indent; $i++) {
				$output .= "&nbsp;&nbsp;";
			}
			$output .= "$row[1]</option>\n";
		}
		// look for subelements
		$indent++;
		show_projects($row[0],$show_group);
		$indent--;
	}
}

function manage_project_statistic_button()
{
	global $module, $mode, $ID, $module;

    $ret = get_buttons(array(array('type'=>'link', 'title' => __('This link opens a popup window'), 'href' => "#", 'onclick' => "manage_project_statistic('".PATH_PRE."','$module','$mode','$ID')",
    'text'=> __('Edit and Save Settings'), 'active' => false)));
	return $ret;
}

function show_saved_statistic_settings($value)
{
	global $user_ID;

	$result = db_query("SELECT id, name
                          FROM ".DB_PREFIX."projekt_statistik_einstellungen
                         WHERE user_ID = ".(int)$user_ID) or db_die("Error");

	while($row = db_fetch_row($result)) {
        if ($value == $row[0])  $selected = " selected='selected'";
        else                    $selected = "";
		$output.= sprintf("<option value='%s' %s>%s</option>\n", $row[0], $selected, $row[1]);
	}

	$output = '
                <input type="hidden" name="action" value="calc"/>
				<input type="hidden" name="do" value="displaySavedSetting"/>
				<select name="savedSetting">
				'.$output.'
				</select>';

	return $output;
}

function stat_buttons($element_ID, $element_module) {
	global $diropen, $sid, $tree_mode;
	global $children, $module, $mode, $ID, $getstring,$projectlist,$userlist;
	$getstring="";
	foreach($_REQUEST as $key=>$val) {
		$getstring.="$key=$val&amp;";
	}
	$getstring.="projectlist[0]=$projectlist[0]&amp;userlist[0]=$userlist[0]&amp;";
	$buttons = 0;
	// if the radio button 'open' was selected: set all main projects to open:
	if ($tree_mode == 'open')  $diropen[$element_module][$element_ID] = 1;
	if ($tree_mode == 'close') $diropen[$element_module][$element_ID] = 0;
	if ($children[$element_ID]) {
		// show button 'open'
		if (!$diropen[$element_module][$element_ID]) { $str = "<a name='A".$row[0]."' href='".$module.".php?$getstring"."mode=$mode&amp;element_mode=open&amp;element_ID=$element_ID&amp;element_module=$element_module&amp;csrftoken=".make_csrftoken()."&amp;ID=$ID".$sid."#A$row[0]'><img src='".IMG_PATH."/open.gif' alt='pen element' title='open element' border='0' />&nbsp;</a>"; }
		// show button 'close'
		else { $str = "<a name='A".$row[0]."' href='".$module.".php?$getstring"."mode=$mode&amp;element_mode=close&amp;element_ID=$element_ID&amp;element_module=$element_module&amp;csrftoken=".make_csrftoken()."&amp;ID=$ID".$sid."#A$row[0]'><img src='".IMG_PATH."/close.gif' alt='close element' title='close element' border='0' /></a>&nbsp;"; }
	}
	return $str;
}

function  list_projects($project){
	global $children,$diropen,$userlist,$output,$level,$sumperson, $sumproject,$anfang,$ende,$cnr;
	// alternate tr colour
	if (($cnr/2) == round($cnr/2)) {
		$color = PHPR_BGCOLOR1;
		$cnr++;
	}
	else {
		$color = PHPR_BGCOLOR2;
		$cnr++;
	}

	$query="SELECT name
                 FROM ".DB_PREFIX."projekte
                 WHERE ID = ".(int)$project;
	$result = db_query($query) or db_die();
	$row = db_fetch_row($result);

	$output .= "<tr bgcolor=$color><td";
	$in = 10;
	for ($i2=1; $i2 <= $level; $i2++) {
		if ($module=='filemanager1') $in += 16;
		else $in += 10;
	}
	if (!$children[$project]) {
		$in += 12;
	}
	$output .= " style='padding-left:".$in."px'>";
	$output.=stat_buttons($project, "projekt_stat")."$row[0]</td>\n";
	$output2='';
	// loop over list of persons and fetch the bookings
	foreach ($userlist as $person) {
		$output .= fetch_bookings($project, $person);
	}

	$h = floor($sumproject[$project]/60);
	$m = $sumproject[$project] - $h*60;
	$output .= "<td valign='bottom'><b>$h : $m</b></td>\n";

	$subpro_times = fetch_bookings_with_subprojects($project, $userlist, $anfang, $ende);
    if (is_array($subpro_times)) {
    	foreach($subpro_times as $book){
	        $subprojectsum += $book;
    	}
    }
	$h = floor($subprojectsum/60);
	$m = $subprojectsum - $h*60;
	$output .= "<td valign='bottom'><b>$h : $m</b></td><td>$row[0]</td>\n";

	$output .= "</tr>\n";
	if($output2<>''){
		$output .= "<tr bgcolor=$color><td";

		$in = 10;
		for ($i2=1; $i2 <= $level; $i2++) {
			if ($module=='filemanager1') $in += 16;
			else $in += 10;
		}
		if (!$children[$ID]) {
			if ($module=='filemanager1') $in += 18;
			else $in += 12;
		}
		$output .= " style='padding-left:".$in."px'> ";
		$output.=stat_buttons($project, "projekt_stat_todo").__('Todo')."</td>\n$output2<td></td><td></td><td></td><td></td></tr>";

	}

	// display children
	if (($diropen["projekt_stat"][$project] or $tree_mode=='open') and !empty($children[$project])) {
		foreach ($children[$project] as $child) {
			// $nr_record++;
			$level++;
			$output .= list_projects($child);
			$level--;
		}
	} else if (is_array($subpro_times)) {
	    foreach($subpro_times as $pers=>$book){
	        $sumperson[$pers]+=$book;
	    }
	}
}

function  list_date_projects($project, $datum) {
    global $children,$diropen,$userlist,$output,$level,$sumperson, $sumproject,$sumdatum,$anfang,$ende,$cnr;

	// alternate tr colour
	if (($cnr/2) == round($cnr/2)) {
		$color = PHPR_BGCOLOR1;
		$cnr++;
	}
	else {
		$color = PHPR_BGCOLOR2;
		$cnr++;
	}
	$query="SELECT name
                 FROM ".DB_PREFIX."projekte
                 WHERE ID = ".(int)$project;
	$result = db_query($query) or db_die();
	$row = db_fetch_row($result);

	$output .= "<tr bgcolor=$color><td>$datum</td><td";
	$in = 10;
	for ($i2=1; $i2 <= $level; $i2++) {
		$in += 10;
	}
	if (!$children[$project]) {
		$in += 12;
	}
	$output .= " style='padding-left:".$in."px'>";
	$output.=stat_buttons($project, "projekt_stat")."$row[0]</td>\n";
	$output2='';
	// loop over list of persons and fetch the bookings
	foreach ($userlist as $person) {
		$output .= fetch_date_bookings($project, $person, $datum, $i);
	}
	$h = floor($sumdatum[$datum.$project]/60);
	$m = $sumdatum[$datum.$project] - $h*60;

	$output .= "<td valign='bottom'><b>$h : $m</b></td>\n";
	$output .= "<td>$row[0]</td>\n";

	$output .= "</tr>\n";

	// display children
	if (($diropen["projekt_stat"][$project] or $tree_mode=='open') and !empty($children[$project])) {
		foreach ($children[$project] as $child) {
			// $nr_record++;
			$level++;
			$output .= list_date_projects($child,$datum);
			$level--;
		}
	}
	else{
	    $subpro_times=get_subproject_times($project,$datum);
	    foreach($subpro_times as $pers=>$book){
	        $sumperson[$pers]+=$book;
	    }
	}
}
?>
