<?php
/**
 * script for make stats of the projects
 * form and view
 *
 * @package    projects
 * @subpackage statistics
 * @author     Albrecht Guenther, $Author: gustavo $
 * @licence    GPL, see www.gnu.org/copyleft/gpl.html
 * @copyright  2000-2006 Mayflower GmbH www.mayflower.de
 * @version    $Id: projects_stat.php,v 1.84 2008-03-13 17:31:36 gustavo Exp $
 */

// check whether the lib has been included - authentication!
if (!defined("lib_included")) die("Please use index.php!");

define ("PSM_RAW", 0);
define ("PSM_STRING", 1);
define ("PSM_NUMBER", 2);
define ("PSM_TIME", 3);
define ("PSM_CALLBACK", 4);

define ("PSC_COSTCENTRES", 'costcentre');
define ("PSC_COSTUNITS",   'costunit');
define ("PSC_CONTRACTOR",  'contractor');

include_once(PATH_PRE.'projects/projects_matrix.php');
include_once(PATH_PRE.'lib/lib.inc.php');
include_once(PATH_PRE.'lib/save_selections_form.php');
include_once(PATH_PRE.'projects/costlib.inc.php');

$projectlist    = (isset($_REQUEST['projectlist'])) ? xss_array($_REQUEST['projectlist'])   : array();
$userlist       = (isset($_REQUEST['userlist']))    ? xss_array($_REQUEST['userlist'])      : array();
$display        = (isset($_REQUEST['display']))     ? xss($_REQUEST['display'])             : 'normal';
$filter_mode    = (isset($_REQUEST['filter_mode'])) ? xss($_REQUEST['filter_mode'])         : 'project';

// Show error messages
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

$projectlist = (isset($_REQUEST['projectlist'])) ? xss_array($_REQUEST['projectlist']) : array();
$userlist    = (isset($_REQUEST['userlist'])) ? xss_array($_REQUEST['userlist']) : array();

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
        $users = array();
        $projects = array();
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
            $isAllUsers    = ((boolean)$row[9]) ? true : false;
            $show_group = $row[10];
            $period_select = $row[11];
        }

        $projectlist = $userlist = array();

        if($isAllProjects) $projectlist[0]  = "gesamt";
        else               $projectlist     = array_keys($projects);

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
            $ende     = date("Y-m-d", periode_get_end_date($pieces["type"], $pieces["cycles"]));
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

    // button bar
    $buttons = array();
    $buttons[] = array('type' => 'link', 'href' => 'projects.php?mode=forms&amp;action=new'.$sid, 'text' => __('New'), 'active' => false);
    $buttons[] = array('type' => 'link', 'href' => 'projects.php?mode=options'.$sid, 'text' => __('Options'), 'active' => false);
    $buttons[] = array('type' => 'link', 'href' => 'projects.php?mode=stat'.$sid, 'text' => __('Statistics'), 'active' => ((isset($mode2) && $mode2 == 'mystat')) ? false : true);
    $buttons[] = array('type' => 'link', 'href' => 'projects.php?mode=stat&amp;mode2=mystat'.$sid, 'text' => __('My Statistic'), 'active' => ((isset($mode2) && $mode2 == 'mystat')) ? true : false);
    $buttons[] = array('type' => 'link', 'href' => 'projects.php?mode=gantt'.$sid, 'text' => __('Gantt'), 'active' => false);
    $buttons[] = array('type' => 'link', 'href' => 'projects.php?type='.$type.'&amp;mode=view', 'text' => __('List View'), 'active' => false);
    
    // tabs
    $tabs = array();
    $title = isset($mode2) && $mode2 == 'mystat' ? 'My Statistic' : 'Statistics';
    $output .= breadcrumb($module, array(array('title'=>__($title))));
    $output .= '</div>';
    $output .= $content_div;
    unset($title);
    $output .= get_buttons_area($buttons);
    $output .= '
    <br />
    <a name="content"></a>
    ';

    // Config
    $page   = 'projects.php';
    $error  = '';
    if($mode2 == "mystat") {
        $radio_buttons_filter = array( 'text_display' => '',
                                       'field_name'   => 'show_group',
                                       'field_value'  => $show_group,
                                       'field_data'   => array( array('value' => '0',
                                                                      'text'  => __('show all projects')),
                                                                array('value' => '1',
                                                                      'text'  => __('only show group projects'))));
    } else {
        $radio_buttons_filter = array();
    }
    $form_js = "onsubmit=\"return checkUserDateFormat('anfang','".__('Begin').':\n'.$date_format_text."') && checkUserDateFormat('ende','".__('End').':\n'.$date_format_text."') && checkDates('anfang','ende','".__('Begin > End')."!');\"";
    $use_project = true;
    $use_users   = true;
    $check_buttons_select = array( __('Show bookings') => 'showbookingdates',
                                   __('remark')        => 'showbookingnotes',
                                   __('Debit value') => 'usedebitvalue');

     if (empty($display)) {
        $display = 'normal';
    }
    $radio_buttons_select = array( 'text_display' => __('sort by'),
                                   'field_name'   => 'display',
                                   'field_value'  => $display,
                                   'field_data'   => array(    array('value' => 'normal',
                                                                     'text'  => __('Project')),
                                                               array('value' => 'date',
                                                                     'text'  => __('Date')),
                                                               array('value' => 'cost',
                                                                     'text'  => __('Costs'))));

    // Class
    $class = new save_selections_form($page,$mode2,$module,$_REQUEST);

    // Saved Settings
    $output .= $class->saved_settings();

    // Filter Options
    $output .= $class->filters_options($radio_buttons_filter);

    // Form
    $output .= $class->start_make_form($page,'frm','',$form_js);

    // Period
    $output .= $class->period();

    $filter_mode = $class->get_post_value('filter_mode');
    if ($filter_mode == 'costcentres') {
        $checkP = '';
        $checkC = 'checked="checked"';
    } else {
        $checkP = 'checked="checked"';
        $checkC = '';
    }
    
    $parameters['in_div']   = 'float:left;margin-right:30px';
    $parameters['class']    = 'label_inline';
    $parameters['name']     = 'costcentrelist';
    $parameters['text']     = __('Costcentre');
    $parameters['style']    = 'float:left;min-width:200px;';
    $parameters['size']     = '10';
    $parameters['values']   = array();
    $result = db_query("SELECT id, name FROM ".DB_PREFIX."controlling_costcentres ORDER BY id") or db_die();
    while ($row = db_fetch_row($result)) {
        $parameters['values'][$row[0]] = $row[1]." (".$row[0].")";
    }
    $additional .= $class->extra_fields('multiple_select',$parameters);
    unset($parameters);

    $parameters['in_div']   = 'float:left;margin-right:30px';
    $parameters['class']    = 'label_inline';
    $parameters['name']     = 'costunitlist';
    $parameters['text']     = __('Costunit');
    $parameters['style']    = 'float:left;min-width:200px;';
    $parameters['size']     = '10';
    $parameters['values']   = array();
    $result = db_query("SELECT id, name FROM ".DB_PREFIX."controlling_costunits ORDER BY id") or db_die();
    while ($row = db_fetch_row($result)) {
        $parameters['values'][$row[0]] = $row[1]." (".$row[0].")";
    }
    $additional .= $class->extra_fields('multiple_select',$parameters);
    unset($parameters);

    $parameters['in_div']   = 'float:left;margin-right:30px';
    $parameters['class']    = 'label_inline';
    $parameters['name']     = 'contractorlist';
    $parameters['text']     = __('Contractor');
    $parameters['style']    = 'float:left;min-width:200px;';
    $parameters['size']     = '10';
    $parameters['values']   = array();
    $result = db_query("SELECT u.id, u.vorname, u.nachname
                          FROM ".DB_PREFIX."users u,
                               ".DB_PREFIX."projekte p
                         WHERE p.contractor_id = u.id
                      GROUP BY u.id
                      ORDER BY u.nachname") or db_die();
    while ($row = db_fetch_row($result)) {
        $parameters['values'][$row[0]] = $row[2].", ".$row[1];
    }
    $additional .= $class->extra_fields('multiple_select',$parameters);
    $additional .= '<br style="clear:both" />';
    unset($parameters);

    $additional = "<br style='clear:both' />".$additional; 
    $output .= $class->multiple_selects($use_project,$use_users,$check_buttons_select,$radio_buttons_select, $additional);

    // End Form
    $output .= $class->end_make_form();
}

//******************
//statistic list
// *****************

else if ((isset($projectlist[0]) and isset($userlist[0])) || $do == 'displaySavedSetting') {

    if ($do == "displaySavedSetting") {
        $class = new save_selections_form($page,$mode2,$module,$_REQUEST);
    }

    if (!is_array($projectlist)) {
        $projectlist = explode("-",$projectlist);
    }
    if (!is_array($userlist)) {
        $userlist = explode("-",$userlist);
    }
    if (($period_select != '') && ($periodtype))
    {
        $pieces = periode_parse_selectbox_value($period_select);
        $anfang = date("Y-m-d", periode_get_start_date($pieces["type"], $pieces["cycles"]));
        $ende     = date("Y-m-d", periode_get_end_date($pieces["type"], $pieces["cycles"]));
    }

    if(is_array($projectlist[0]))
        $projectlist = $projectlistsave;
    
    $projectlistsave = $projectlist;
    $userlistsave = $userlist;
    $_SESSION['projectlistsave'] =& $projectlistsave;
    $_SESSION['userlistsave']    =& $userlistsave;

    //tabs
    /*
    if      ($display=='normal') $exp = get_export_link_data('project_stat', false);
    else if ($display=='date')   $exp = get_export_link_data('project_stat_date', false);
    else if ($display=='cost')   $exp = get_export_link_data('project_stat_costs', false);
    */
    $tabs = array();
    if ($filter_mode=='project' && $display == 'normal') {
        $tmp = get_export_link_data('project_stat', false);
    } else if ($filter_mode=='project' && $display == 'date') {
        $tmp = get_export_link_data('project_stat_date', false);
    } else if ($filter_mode=='costcentres') {
        $tmp = get_export_link_data('project_stat_celement', false);
    }

    $tabs[] = array('href' => $tmp['href'], 'active' => $tmp['active'], 'id' => 'tab4', 'target' => '_self', 'text' => $tmp['text'], 'position' => 'right');

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
    $output .= get_module_tabs($tabs,$buttons);
    $output .="</form>\n";

    $output .= '<br /><h1>'.__('Statistics').'</h1>';
    // title
    $output .= $err;
    $output .= "<br /> ".__('Begin').": ".$date_format_object->convert_db2user($anfang).", ";
    $output .= __('End').": ".$date_format_object->convert_db2user($ende)."<br /><br />\n";

    if(!$date_format_object->is_db_date($anfang)) {
        $anfang = $date_format_object->convert_user2db($anfang);
    }
    if(!$date_format_object->is_db_date($ende)) {
        $ende = $date_format_object->convert_user2db($ende);
    }

    // check whether the given values are valid dates
    if (!checkdate(substr($anfang,5,2), substr($anfang,8,2), substr($anfang,0,4))) die(__('Please check the date!')." <br />".__('back')." ...");
    if (!checkdate(substr($ende,5,2),   substr($ende,8,2),   substr($ende,0,4)))   die(__('Please check the date!')." <br />".__('back')." ...");
    if ($ende < $anfang) {
        die(__('Please check start and end time! '));
    }

    $isAllProjects    = ($projectlist[0] == "gesamt");
    $isAllUsers       = ($userlist[0] == "gesamt");
    $isAllCostcentres = ($costcentrelist[0] == "gesamt");
    $isAllCostunits   = ($costunitlist[0] == "gesamt");
    $isAllContractors = ($contractorlist[0] == "gesamt");

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
            $query= "SELECT ".DB_PREFIX."projekte.ID, chef
                       FROM ".DB_PREFIX."projekte, ".DB_PREFIX."project_users_rel 
               WHERE ".DB_PREFIX."projekte.ID = ".DB_PREFIX."project_users_rel.project_ID and
                    user_ID = ".(int)$user_ID."
                        AND ".DB_PREFIX."projekte.anfang >= '$anfang'
                        AND ".DB_PREFIX."projekte.ende <= '$ende'
                        AND ".DB_PREFIX."projekte.is_deleted is NULL
                          ".sort_string('projects');
        }
        else {
            $query= "SELECT ID, chef
                       FROM ".DB_PREFIX."projekte $before_where
                       WHERE $sql_user_group 
                         AND ".DB_PREFIX."projekte.anfang >= '$anfang'
                         AND ".DB_PREFIX."projekte.ende <= '$ende'
                         AND is_deleted is NULL
                             $where
                          ".sort_string('projects');
        }
        $result = db_query($query) or db_die();
        while ($row = db_fetch_row($result)){
            $is_part = user_is_part($row[0],$user_ID);
            if ($is_part || ($row[1] == $user_ID) || ($user_type == 2)) {
                $projectlist[] = $row[0];
            }
        }

    }
    // get children,parents
    $foundrecords      = array();
    $allrecords        = array();
    $children          = array();
    $noprojectsmessage = __('Sorry but the list project is empty or the selected users are not participants on the selected projects');

    $filtered = array_keys(ps_get_projects($projectlist, $costcentrelist, $costunitlist, $contractorlist));
    $projectlist = array_values(array_intersect((array)$projectlist, $filtered));

    if ($projectlist[0] == '') {
        echo $output;
        die($noprojectsmessage);
    }
    $query = "SELECT ID,parent
               FROM ".DB_PREFIX."projekte
              WHERE ID in (".implode(',',$projectlist).")
                AND is_deleted is NULL
           ORDER BY next_proj,name";
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
    if ($display == 'date') {
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
                         AND p.is_deleted is NULL
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
                               AND ".DB_PREFIX."users.is_deleted is NULL
                          ORDER BY nachname") or db_die();
        while ($row = db_fetch_row($result)) $userlist[] = $row[0];
    }
    // begin output table
    $output .= " <table>";

    if ($display=='normal' || $display == 'cost') $output .= "<thead><tr><th><b>".__('Project Name')."</b></th>\n";
    else if ($display == 'date') $output .= "<thead><tr><th><b>".__('Date')."</b></th><th><b>".__('Project Name')."</b>\n";

    // first row: the users!
    if ($display == 'normal' || $display == 'date') {
        foreach ($userlist as $person) {
            $result = db_query("SELECT kurz
                                  FROM ".DB_PREFIX."users
                                 WHERE ID = ".(int)$person."
                                   AND is_deleted is NULL") or db_die();
            $row = db_fetch_row($result);
            $output .= "<th>$row[0]</th>\n";
        }
    }
    // end of the first row - display string 'sum'
    if($display=='normal' || $display == 'date' ) {
        $output .= "<th><b>".__('Sum')."</b>\n</th>";
    }
    if ($display=='normal') {
        $output .= "<th><b>".__('sum of subprojects')."</b>\n</th>";
    }
if($display=='normal' && $usedebitvalue == 'on') $output .= "<th><b>".__('Debit value')."</b>\n</th>";
	if($display=='normal') $output .= "<th><b>".__('Project period')."</b>\n</th>";

    if($display == 'cost' ) {
        $output .= "<th><b>".__('Special costs')."</b>\n</th>";
        $output .= "<th><b>".__('Costs for work')."</b>\n</th>";
        $output .= "<th><b>".__('Sum')."</b>\n</th>";
    }

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
            else if ($display == 'cost') {
                if(in_array($project,$foundrecords)){
                    $output .=list_costs($project);
                }
            }
            else if ($display == 'date') {
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

    if ($display == 'normal' || $display == 'date') {
        $output .= "<tr><td></td>";
        if ($display!='normal') $output .='<td></td>';
        
        foreach ($userlist as $person) {
            $result = db_query("SELECT kurz
                              FROM ".DB_PREFIX."users
                             WHERE ID = ".(int)$person."
                               AND is_deleted is NULL") or db_die();
            $row = db_fetch_row($result);
            $output .= "<td align='center'>$row[0]</td>";
        }
    }
    if ($display == 'normal') $output.="<td></td>";
    if ($display == 'normal' && $usedebitvalue == 'on') $output.= "<td></td>";
	if ($display == 'normal') $output.="<td></td>";
    if ($display == 'normal' || $display=='date') $output.="<td></td><td></td></tr>\n";
    
    $output .= "<tr><td><b>".__('Sum')."</b></td>\n";
    if ($display == 'date') $output .='<td></td>';
    if ($display == 'normal' || $display == 'date') {
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
    }

    
    
    	if ($display=='normal' && $usedebitvalue == 'on') {
		$debitvalue = 0;
		foreach (array_intersect($foundrecords, $projectlist) as $project) {
			$sums = ps_get_debitavalue($project);
			if (is_array($sums)) {
				$debitvalue+= array_sum($sums);
			}
		}

		$h = floor($debitvalue * 6);
		$m = (int)(($debitvalue * 6)%60);
		
		$debitvalue = number_format($debitvalue, 2, ',', '.');
		$output .= "<td><b>{$h}h {$m}m ($debitvalue MT)</b></td>\n";
	}
	if($display=='normal')
        $output .= "<td>&nbsp;</td><td>&nbsp;</td>\n";
    if($display == 'cost') {
        $special_costs = number_format($sumcosts['special_costs'], 2, ',', ' ');
        $work_costs    = number_format($sumcosts['work_costs'], 2, ',', ' ');
        $sum_costs     = number_format($sumcosts['sum_costs'], 2, ',', ' ');

        $output .= "<td><b>$special_costs ".PHPR_CUR_SYMBOL."</b></td>
                    <td><b>$work_costs ".PHPR_CUR_SYMBOL."</b></td>
                    <td><b>$sum_costs ".PHPR_CUR_SYMBOL."</b></td><td>&nbsp;</td>";
    }

    $output .= "</tr></tfoot></table>";
    $_SESSION['saved_settings']['projects']['projectlist'] = $projectlist;
    $_SESSION['saved_settings']['projects']['userlist'] = $userlist;

    // register the pojectlist and the participants so the user won't have to select them again
    $_SESSION['projectlist'] =& $projectlist;
    $_SESSION['userlist']    =& $userlist;
    $_SESSION['statistic'] = array(    "userlist"         => $userlist,
    "projectlist"     => $projectlist,
    "startDate"     => $anfang,
    "endDate"         => $ende,
    "withBooking"    => ($showbookingdates=="on")?true:false,
    "withComment"    => ($showbookingnotes=="on")?true:false,
    "sortBy"        => $display,
    "isAllProjects"    => $isAllProjects,
    "isAllUsers"    => $isAllUsers,
    "show_group"    => $show_group,
    "period"        => ($periodtype)? $period_select : ''
    );
}

echo $output;

/**
 * collect all the booking of one project and the subprojects of it
 * return the sum of the hours per user
 *
 * @param int project       - Project ID
 * @param array userlist    - Array with the selected users
 * @param date anfang       - Start day
 * @param date ende         - End day
 * @return array            - Array[user] => sum of the hours
 */
function fetch_bookings_with_subprojects($project, $userlist, $anfang, $ende)
{
    $subprojects = get_node_with_children("projekte", $project, " is_deleted is NULL");

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

/**
 * collect all the booking of one subproject
 * return the sum of the hours per user
 *
 * @param int project       - Project ID
 * @param date datum        - Date to get
 * @return array            - Array[user] => sum of the hours
 */
function get_subproject_times($project,$datum)
{
    $result = db_query("SELECT datum, h, m, users
                          FROM ".DB_PREFIX."timeproj as t, ".DB_PREFIX."projekte as p
                         WHERE t.projekt=p.ID
                           AND p.parent='$project'
                           AND t.datum='$datum'
                           AND p.is_deleted is NULL
                      ORDER BY datum") or db_die();

    $sum = array();

    while($row = db_fetch_row($result)) {
        $sum[$row[3]] += $row[1]*60+$row[2];
    }

    return $sum;
}

/**
 * Display the booking for one project and one user
 *
 * @param int project   - Project ID
 * @param int person    - User ID
 * @return string       - HTML output
 */
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

/**
 * Display the booking for one project and one user and one date
 *
 * @param int project   - Project ID
 * @param int person    - User ID
 * @param date datum    - Date to get
 * @return string       - HTML output
 */
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

/**
 * Display the project data
 * recursive function for display all the projects and subprojects
 * use global vars for keep the data
 *
 * @param int parent_ID   - parent project ID
 * @param int show_gorup  - 0 / 1 for show groups
 * @return void
 */
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
                 WHERE is_deleted is NULL
                   AND parent = ".(int)$parent_ID."
                       $where
                   ".sort_string('project_statistic');
    }
    // 2. case: query as project leader or chef - only for the group
    else {
        $query ="SELECT ID, name, chef
                   FROM ".DB_PREFIX."projekte $before_where
                  WHERE is_deleted is NULL
                   AND parent = ".(int)$parent_ID."
                       $where
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

/**
 * Display a button for open the popup
 *
 * @param void
 * @return string - HTML output
 */
function manage_project_statistic_button()
{
    global $module, $mode, $ID, $module, $mode2;

    $post_values = xss_array($_POST);
    unset($_SESSION['saved_settings']);
    foreach($post_values as $k => $v) {
        $_SESSION['saved_settings'][$module][$k] = $v;
    }
    unset($_SESSION['saved_settings'][$module]['do']);
    unset($_SESSION['saved_settings'][$module]['action']);
    unset($_SESSION['saved_settings'][$module]['settings_apply']);
    unset($_SESSION['saved_settings'][$module]['savedSetting']);

    $ret = get_buttons(array(array('type'=>'link', 'title' => __('This link opens a popup window'), 'href' => "#", 'onclick' => "manage_saved_settings('".PATH_PRE."','$module','projects/projects.php?mode=stat','$mode','$mode2')",
    'text'=> __('Edit and Save Settings'), 'active' => false)));
    return $ret;
}

/**
 * Display the select of saved settings
 *
 * @param int value   - Selected setting
 * @return string     - HTML output
 */
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

/**
 * Display the status image, open / close
 *
 * @param int element_ID        - Project ID
 * @param string element_module - Module
 * @return string               - HTML output
 */
function stat_buttons($element_ID, $element_module) {
    global $diropen, $sid, $tree_mode;
    global $children, $module, $mode, $ID, $getstring,$projectlist,$userlist;
    $getstring="";
    foreach($_REQUEST as $key=>$val) {
        $getstring.="$key=$val&amp;";
    }
    $getstring.="projectlist=".implode("-",$projectlist)."&amp;userlist=".implode("-",$userlist)."&amp;";
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

/**
 * Display all the project data in a list view
 * use all the global vars
 *
 * @param int project   - Project ID
 * @return void
 */
function  list_projects($project){
    global $children,$diropen,$userlist,$output,$level,$sumperson, $sumproject,$anfang,$ende,$cnr, $showcosts, $projectlist,$usedebitvalue, $date_format_object;
    // alternate tr colour
    if (($cnr/2) == round($cnr/2)) {
        $color = PHPR_BGCOLOR1;
        $cnr++;
    }
    else {
        $color = PHPR_BGCOLOR2;
        $cnr++;
    }

    $tree_elements = get_elements_of_tree("projekte", "name",  "", "acc",
                            "ORDER BY name", "",  "parent");


    $query="SELECT p.name, pc.costunit_id, cc.name
              FROM ".DB_PREFIX."projekte p
         LEFT JOIN ".DB_PREFIX."projekte_costunit pc
                ON p.ID = pc.projekte_id
         LEFT JOIN ".DB_PREFIX."controlling_costunits cc
                ON pc.costunit_id = cc.id
          WHERE p.ID = ".$project;

    $result = db_query($query) or db_die();

    while ($mrow = db_fetch_row($result)) {
        if (!is_null($mrow[1])) {
            $costunits[$mrow[1]] = $mrow[2];
        }
        $row = $mrow;
    }

    /*
    $query="SELECT name
              FROM ".DB_PREFIX."projekte
             WHERE ID = ".(int)$project."
               AND is_deleted is NULL";
    $result = db_query($query) or db_die();
    $row = db_fetch_row($result);
    */

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

    // $output .= "<td valign='bottom'><b>$h : $m</b></td>\n";

    if (!empty($costunits)) {
        foreach ($costunits as $id => $name) {
            $fraction = ps_get_fraction($id, $project);
            $h = floor(($sumproject[$project]*$fraction)/60);
            $m = round(($sumproject[$project]*$fraction) - $h*60);
            $foutput .= "<b>${h} : ${m}</b> ($name)<br />";
        }
    }

    $h = floor($sumproject[$project]/60);
    $m = $sumproject[$project] - $h*60;
    $output .= "<td valign='bottom'>$foutput<b>$h : $m</b></td>\n";

    $subpro_times = fetch_bookings_with_subprojects($project, $userlist, $anfang, $ende);

    if (is_array($subpro_times)) {
        foreach($subpro_times as $book){
            $subprojectsum += $book;
        }
    }
    $h = floor($subprojectsum/60);
    $m = $subprojectsum - $h*60;

    $output .= "<td valign='bottom'><b>$h : $m</b></td>";

    if ($usedebitvalue == 'on') {
        $debitvalue = array_sum(ps_get_debitavalue($project));
        $h = floor($debitvalue/60);
        $m = round($debitvalue - $h*60);

        $output .= "<td valign='bottom'><b>$debitvalue Tage</b></td>\n";
    }

    list ($anfang, $ende) = ps_get_realprojectperiod($project);

    $output .= "<td valign='bottom'><b>
                ".$date_format_object->convert_db2user($anfang)." -
                ".$date_format_object->convert_db2user($ende)."</b></td>\n";

    $output .= "<td>$row[0]</td>\n";
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

/**
 * Display the dates of each project
 * use all the global vars
 *
 * @param int project   - Project ID
 * @param date datum    - Date to get
 * @return void
 */
function  list_costs($project){
    global $children,$diropen,$userlist,$output,$level,$sumperson, $sumproject,$anfang,$ende,$cnr, $showcosts, $projectlist, $sumcosts;
    // alternate tr colour
    if (($cnr/2) == round($cnr/2)) {
        $color = PHPR_BGCOLOR1;
        $cnr++;
    }
    else {
        $color = PHPR_BGCOLOR2;
        $cnr++;
    }
    
    $query="SELECT name, anfang, ende
              FROM ".DB_PREFIX."projekte
             WHERE ID = ".(int)$project."
              AND is_deleted is NULL";
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
    $output .= stat_buttons($project, "projekt_stat")."$row[0]</td>\n";

    $special_costs = ps_specialcosts($project, $projectlist, $userlist);
    $work_costs    = ps_workcosts($project, $projectlist, $userlist);

    $sum_costs     = $special_costs + $work_costs;

    $sumcosts['special_costs'] += $special_costs;
    $sumcosts['work_costs']    += $work_costs;
    $sumcosts['sum_costs']     += $sum_costs;
    
    $per_day_costs = number_format($per_day_costs, 2, ',', ' ');
    $special_costs = number_format($special_costs, 2, ',', ' ');
    $work_costs = number_format($work_costs, 2, ',', ' ');
    $sum_costs = number_format($sum_costs, 2, ',', ' ');
    
    $output .= "<td valign='bottom' align='right'><b>$special_costs ".PHPR_CUR_SYMBOL."</b></td>\n";
    $output .= "<td valign='bottom' align='right'><b>$work_costs ".PHPR_CUR_SYMBOL."</b></td>\n";
    $output .= "<td valign='bottom' align='right'><b>$sum_costs ".PHPR_CUR_SYMBOL."</b></td>\n";
    
    $output .= "<td>$row[0]</td>\n";
    $output .= "</tr>\n";

    if (($diropen["projekt_stat"][$project] or $tree_mode=='open') and !empty($children[$project])) {
        foreach ($children[$project] as $child) {
            // $nr_record++;
            $level++;
            $output .= list_costs($child);
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

/**
 * Display the dates of each project
 * use all the global vars
 *
 * @param int project   - Project ID
 * @param date datum    - Date to get
 * @return void
 */
function  list_date_projects($project, $datum) {
    global $children,$diropen,$userlist,$output,$level,$sumperson, $sumproject,$sumdatum,$anfang,$ende,$cnr, $showcosts, $projectlist, $sumcosts;

    // alternate tr colour
    if (($cnr/2) == round($cnr/2)) {
        $color = PHPR_BGCOLOR1;
        $cnr++;
    }
    else {
        $color = PHPR_BGCOLOR2;
        $cnr++;
    }

    $query="SELECT p.name, pc.costunit_id, cc.name
              FROM ".DB_PREFIX."projekte p
         LEFT JOIN ".DB_PREFIX."projekte_costunit pc
                ON p.ID = pc.projekte_id
         LEFT JOIN ".DB_PREFIX."controlling_costunits cc
                ON pc.costunit_id = cc.id
          WHERE p.ID = ".(int)$project." 
                AND p.is_deleted is NULL";

    $result = db_query($query) or db_die();

    $costunits = array();
    while ($mrow = db_fetch_row($result)) {
        if (!is_null($mrow[1])) {
            $costunits[$mrow[1]] = $mrow[2];
        }
        $row = $mrow;
    }

    $output .= "<tr bgcolor=$color><td>$datum</td><td";
    $in = 10;
    for ($i2=1; $i2 <= $level; $i2++) {
        if ($module=='filemanager1') $in += 16;
        else $in += 10;
    }
    if (!$children[$project]) {
        $in += 12;
    }
    $output .= " style='padding-left:".$in."px'>";
    $output .= stat_buttons($project, "projekt_stat")."$row[0]</td>\n";

    $special_costs = ps_specialcosts($project, $projectlist, $userlist);
    $work_costs    = ps_workcosts($project, $projectlist, $userlist);

    $sum_costs     = $special_costs + $work_costs;

    $sumcosts['special_costs'] += $special_costs;
    $sumcosts['work_costs']    += $work_costs;
    $sumcosts['sum_costs']     += $sum_costs;
    
    $per_day_costs = number_format($per_day_costs, 2, ',', ' ');
    $special_costs = number_format($special_costs, 2, ',', ' ');
    $work_costs = number_format($work_costs, 2, ',', ' ');
    $sum_costs = number_format($sum_costs, 2, ',', ' ');
    
    $output .= "<td valign='bottom' align='right'><b>$special_costs ".PHPR_CUR_SYMBOL."</b></td>\n";
    $output .= "<td valign='bottom' align='right'><b>$work_costs ".PHPR_CUR_SYMBOL."</b></td>\n";
    $output .= "<td valign='bottom' align='right'><b>$sum_costs ".PHPR_CUR_SYMBOL."</b></td>\n";
    
    $output2='';
    // loop over list of persons and fetch the bookings
    foreach ($userlist as $person) {
        $output .= fetch_date_bookings($project, $person, $datum, $i);
    }

    foreach ($costunits as $id => $name) {
        $fraction = ps_get_fraction($id, $project);
        $h = floor(($sumdatum[$datum.$project]*$fraction)/60);
        $m = round(($sumdatum[$datum.$project]*$fraction) - $h*60);
        $foutput .= "<b>${h} : ${m}</b> ($name)<br />";
    }

    $h = floor($sumdatum[$datum.$project]/60);
    $m = $sumdatum[$datum.$project] - $h*60;
    // $output .= "<td>$row[0]</td>\n";
    $output .= "<td valign='bottom'>$foutput<b>$h : $m</b></td>\n";
    $output .= "<td>$row[0]</td>\n";

    $output .= "</tr>\n";

    // display children
    if (($diropen["projekt_stat"][$project] or $tree_mode=='open') and !empty($children[$project])) {
        foreach ($children[$project] as $child) {
            // $nr_record++;
            $level++;
            $output .= list_costs($child,$datum);
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

/*
 * Matrix display callbacks
 */
/**
 * Callack for PSM_CALLBACK
 *
 * @param array $column
 * @return string
 */
function ps_display_booking_column($column) {
    global $showbookingnotes;

    $output = "";
    if (is_array($column)) {
        foreach ($column as $booking) {
            $output.= sprintf("%02dh %02dm<br />",
                            floor($booking['booking']),
                            ( $booking['booking'] - floor($booking['booking']) ) * 60);
            if ($showbookingnotes == "on") {
                $output.= '<span style="font-size:11px">'.$booking['note']."</span><br />";
            }
        }
    }

    return $output;
}

/**
 * Callback for PSM_Callback
 *
 * @param array $project
 * @return string
 */
function ps_display_celement_projects($list) {

    static $fractions = NULL;

    $tree_elements = (array) get_elements_of_tree("projekte",
            "name",
            "",
            "acc",
            "ORDER BY name",
            "",
            "parent");

    // get_bookings expectes an array of projects, so we have to convert it
    $project  = $list[0];
    $celement = $list[1];
    $projects = array($project['pid'] => $project);
    $bookings = (array) ps_get_bookings($projects);

    if (is_array($project)) {
        $sum = 0.0;
        foreach ($bookings as $booking) {
            if ($celement['type'] == PSC_COSTUNITS) {
                $fraction = ps_get_fraction($celement['id'], $project['pid']);
                $booking['booking']*= $fraction;
            }

            $output.= sprintf("%s: <b>%02dh %02dm</b> by %s<br />",
                            $booking['date'],
                            floor($booking['booking']),
                            ( $booking['booking'] - floor($booking['booking']) ) * 60,
                            $booking['user']);

            $sum+= $booking['booking'];

            if ($showbookingnotes == "on") {
                $output.= '<span style="font-size:11px">'.$booking['note']."</span><br />";
            }
        }

        if (count($bookings) > 0) {

            if ($celement['type'] == PSC_COSTUNITS) {
                $project_parents_sum = 0.0;
                foreach(get_child_ids($tree_elements, $project['pid']) as $child_id) {
                    if ($celement['type'] == PSC_COSTUNITS) {
                        $cfraction = ps_get_fraction($celement['id'], $child_id);
                    } else {
                        $cfraction = 1.0;
                    }

                    $project_children_sum += ps_calc_project_booking_sum($child_id) * $cfraction;
                }

                $project_parents_sum = 0.0;
                foreach(get_parent_ids($tree_elements, $project['pid']) as $parent_id) {
                    if ($celement['type'] == PSC_COSTUNITS) {
                        $cfraction = ps_get_fraction($celement['id'], $parent_id);
                    } else {
                        $cfraction = 1.0;
                    }

                    $project_parents_sum+= ps_calc_project_booking_sum($parent_id) * $cfraction;
                }

                $output.= sprintf("<br />%s: %02dh %02dm<br />", __("Child"),
                                floor($project_children_sum),
                                ($project_children_sum - floor($project_children_sum)) * 60);
                $output.= sprintf("%s: %02dh %02dm<br />", __("Parent"),
                                floor($project_parents_sum),
                                ($project_parents_sum - floor($project_parents_sum)) * 60);
                $output.= sprintf("%s: %.2f%%<br />", __("Fraction"), $fraction * 100);
            }
            $output.= sprintf("<br />%s: <b>%02dh %02dm</b><br />",
                                __("Sum"),
                                floor($sum),
                                ( $sum - floor($sum) ) * 60);
        }

    }

    return $output;
}

?>
