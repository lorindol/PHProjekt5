<?php
/**
* projects gantt script
*
* @package    projects
* @module     main
* @author     Albrecht Guenther, $Author: albrecht $
* @licence    GPL, see www.gnu.org/copyleft/gpl.html
* @copyright  2000-2006 Mayflower GmbH www.mayflower.de
* @version    $Id: projects_gantt.php,v 1.57.2.2 2007/03/01 22:20:31 albrecht Exp $
*/

define('PATH_PRE','../');
include_once(PATH_PRE.'lib/lib.inc.php');
require_once(LIB_PATH.'/dbman_lib.inc.php');
ini_set(max_execution_time, 200);
if ($_REQUEST['use_sort'] == "on")  {
    $f_sort['project_gantt']['sort'] =  xss($f_sort['projects']['sort']);
    $f_sort['project_gantt']['direction'] = xss($f_sort['projects']['direction']);
}
else{
    $f_sort['project_gantt']['sort'] = 'next_proj,name';
    $f_sort['project_gantt']['direction'] = 'ASC';
}
// include library to sort the projects

//categories: 1=offered, 2=ordered, 3=at work, 4=ended, 5=stopped, 6=reopened 7 = waiting, 10=container, 11=ext. project
$categories = array( "1" => __('offered'), "2" => __('ordered'), "3" => __('Working'), "4" => __('ended'),
"5" => __('stopped'), "6" => __('Re-Opened'), "7" => __('waiting') );

// assign colours to categories
$colours = array( "#000000", "#00ff00", "#0000ff", "#ffff00", "#ff00ff", "#00ffff",
"#800080", "#c0c0c0", "#008000", "#000080", "#808000", "#ff0000" );
$values = 12;
$pixel  = 780;
$width_proj_names = 220;
if (empty($scaling)) $scaling = "auto";
else $scaling = qss($scaling);
// clear projectlist
$projectlist = array();

// define start and end time
define_timeframe();

// fetch list of projects
fetch_projects();


// **************
// business chart
// **************
if ($chart) {
    $post_string='';
    foreach ($_REQUEST as $k => $v){
        $post_string.= "".xss($k)."=".xss($v)."&";
    }
    $output.= '<img src="gantt_graph.php?'.$post_string.'">';
    if($colour=='on')$output.='<br /><br />'.add_Legend($categories,$colours);

}
else{
    // tabs
    $tabs = array();
    $output .= '<div id="global-header">';
    $output .= get_tabs_area($tabs);
    $output .= breadcrumb($module, array(array('title'=>__('Gantt'))));
    $output .= '</div>';
    $output .= $content_div;

    // button bar
    $buttons = array();
    $buttons[] = array('type' => 'link', 'href' => 'projects.php?mode=forms&amp;action=new'.$sid, 'text' => __('New'), 'active' => false);
    $buttons[] = array('type' => 'link', 'href' => 'projects.php?mode=options'.$sid, 'text' => __('Options'), 'active' => false);
    $buttons[] = array('type' => 'link', 'href' => 'projects.php?mode=stat'.$sid, 'text' => __('Statistics'), 'active' => false);
    $buttons[] = array('type' => 'link', 'href' => 'projects.php?mode=stat&amp;mode2=mystat'.$sid, 'text' => __('My Statistic'), 'active' => false);
    $buttons[] = array('type' => 'link', 'href' => 'projects.php?mode=gantt'.$sid, 'text' => __('Gantt'), 'active' => true);
    $buttons[] = array('type' => 'link', 'href' => 'projects.php?type='.$type.'&amp;sort='.$sort.'&amp;mode=view', 'text' => __('List View'), 'active' => false);
    $output .= get_buttons_area($buttons);

    $output .= "<form style='display:inline;' action='projects.php?mode=gantt' method='post' name='change_settings' target><br />";
    if($_REQUEST['use_filters']=="on")$checked=" checked='checked'";
    else $checked='';
    $output.= '<fieldset class="settings">';
    $output.= '<legend>'.__('Filter configuration').'</legend>';
    $output.= display_special_flag('use_filters',__('Consider current set filters'),$checked,"onchange='document.change_settings.submit();'")."<br />";
    if($_REQUEST['exclude_archived']=="on")$checked=" checked='checked'";
    else $checked='';
    $output.= display_special_flag('exclude_archived',__('Exclude archived elements'),$checked,"onchange='document.change_settings.submit();'")."<br />";
    if($_REQUEST['exclude_read']=="on")$checked=" checked='checked'";
    else $checked='';    
   
    $output .= display_special_flag('exclude_read',__('Exclude read elements'),$checked,"onchange='document.change_settings.submit();'")."<br />";
    
    if($_REQUEST['use_sort']=="on")$checked=" checked='checked'";
    else $checked='';       
    $output .= display_special_flag('use_sort',__('Consider order from listview'),$checked,"onchange='document.change_settings.submit();'")."<br />";
    $output .="<noscript>".get_buttons(array(array('type' => 'submit', 'value' => __('OK'), 'active' => false)))."</div></noscript>";
    $output .= "</fieldset>
                </form>";
    $output .= "<form style='display:inline;' action='projects_gantt.php?' method='get' name='theForm' target='_blank'>";
    
    if (SID) $output.= "<input type='hidden' name='".session_name()."' value='".session_id()."' />\n";
    $output .= "<input type='hidden' name='mode' value='gantt' />\n";
    $output .= "<input type='hidden' name='use_filters' value='$use_filters' />\n";
    $output .= "<input type='hidden' name='exclude_archived' value='$exclude_archived' />\n";
    $output .= "<input type='hidden' name='exclude_read' value='$exclude_read' />\n";
    $output .= "<input type='hidden' name='use_sort' value='$use_sort' />\n";
    $output.= '<fieldset class="settings">';
    $output.= '<legend>'.__('General settings').'</legend>';
    $output.= '<fieldset class="settings">';
    $output .= "<legend>".__('timescale').":</legend>
                <input type='radio' name='scaling' value='auto'";
    if ($scaling=="auto" or !$scaling) $output.= ' checked="checked"';
    $output.= " />".__('Automatic scaling');

    $output.= "&nbsp;&nbsp;<input type='radio' name='scaling' value='manual'";
    if ($scaling == "manual") $output.= ' checked="checked"';
    $output.= " />".__('Manual Scaling').":&nbsp;&nbsp;\n";
    // box for start year
    $output.= __('Begin:')." <select name='start_year'>\n";
    $year  = date('Y', mktime(date('H')+PHPR_TIMEZONE,date('i'),date('s'),date('m'),date('d'),date('Y')));
    $year_end=$year+5;
    for ($i=2000; $i <= $year; $i++) {
        $output.= "<option value='$i'";
        if ($i == $start_year) $output.= ' selected="selected"';
        $output.= ">$i</option>\n";
    }
    $output.= "</select>\n";
    // box for start month
    $output.= "<select name='start_month'>\n";
    for ($i=1; $i <= 12; $i++) {
        if ($i < 10) { $j = "0".$i; }
        else { $j = $i; }
        $output.= "<option value='$j'";
        if ($i == $start_month) $output.= ' selected="selected"';
        $output.=">$j</option>\n";
    }
    $output.="</select>&nbsp;&nbsp;\n";
    // end year
    $output.= __('End:')." <select name='end_year'>\n";
    for ($i=2000; $i <= $year_end; $i++) {
        $output.= "<option value='$i'";
        if ($i == $end_year) $output.= ' selected="selected"';
        $output.= ">$i</option>\n";
    }
    $output.= "</select>\n";
    // end month
    $output.="<select name='end_month'>\n";
    for ($i=1; $i <= 12; $i++) {
        if ($i < 10) { $j = "0".$i; }
        else { $j = $i; }
        $output.="<option value='$j'";
        if ($i == $end_month) $output.= ' selected="selected"';
        $output.= ">$j</option>\n";
    }
    $output.="</select>  \n";
    // automatic scaling

    $output.= "</fieldset>\n";
    
    $output.= '<fieldset class="settings">';
    // second row of navigation table
    // check whether the user just wants to have one major project
    $output.= "<legend>".__('project choice')."</legend>&nbsp;&nbsp;<label for='single_project'>".__('Only this project').":</label>
    <select name='single_project' id='single_project'><option value='0'></option>\n";

    // include filter/ archiv options to $query
    $before_where = special_sql_filter_flags('projects', xss_array($_POST));
    $where="";
    $where = special_sql_filter_flags('projects', xss_array($_POST), false);
    if (isset($_POST['use_filters']) && ($_POST['use_filters'] == "on") ) {
        $where.= main_filter('', '', '', '', 'projects','','');
    }
    // call function to show all required elements in a tree structure in the select box
    $output.= show_elements_of_tree('projekte',
    'name',
    "$before_where WHERE (acc LIKE 'system' OR ((von = ".$user_ID." OR acc LIKE 'group' OR acc LIKE '%\"$user_kurz\"%') AND $sql_user_group)) $where",
    'acc',sort_string('project_gantt'),$single_project,'parent',0);

    $output.= "</select> | \n";

    // checkbox for option 'only display main projects
    $output.="<input type='checkbox' name='only_main' value='1'";
    if ($only_main) $output.= ' checked="checked"';
    $output.= " />".__('Only main projects')." ";
    $output.="</fieldset>";
    

    // show chart
    if (PHPR_SUPPORT_CHART) {
        if (!$scaling2) $scaling2 = 'week';
        $output.= '<fieldset class="settings">';
        $output.="<legend>".__('display format').":</legend><input type='radio' name='chart' onchange='formSubmit2(this.form);' value='' />html 
        <input type='radio' name='chart' value='chart' checked='checked' onchange='formSubmit1(this.form);' />&nbsp; chart</fieldset>\n";
        $output.="</fieldset>";
        $output.= '<fieldset class="settings">';
        $output.="<legend>".__('for chart only')."</legend>";
        $output.= '<fieldset class="settings">';
        $output.="<legend>".__('column view').":</legend><input type='checkbox' name='chefl' id='chef1' /><label for='chef1'>".__('Leader').
        "</label>&nbsp;&nbsp;<input type='checkbox' name='person'id='person' /><label for='person'>".__('Participants')."</label>
        &nbsp;&nbsp;<input type='checkbox' name='categorie' id='categorie' /><label for='categorie'>".__('Category')."</label></fieldset>";
        $output.= '<fieldset class="settings">';
        $output.="<legend>".__('colours:')."</legend>
        <input type='checkbox' name='colour' id='colour' /> <label for='colour'>".__('display project colours').":</label></fieldset>";
        $output.= '<fieldset class="settings">';
        $output.="<legend>".__('scaling:')."</legend>\n";
        $output.="<input type='radio' name='scaling2' id='auto' value='auto'";
        if ($scaling2 == "auto") $output.= " checked='checked'";
        $output.= " /><label for='auto'>".__('automatic')."</label>\n&nbsp;&nbsp;";
        $output.="<input type='radio' name='scaling2' id='week' value='week'";
        if ($scaling2 == "week") $output.= " checked='checked'";
        $output.= " /><label for='week'>".__('weekly')."</label>\n&nbsp;&nbsp;";
        $output.="<input type='radio' id='month' name='scaling2' value='month'";
        if ($scaling2 == "month") $output.= " checked='checked'";
        $output.= " /><label for='month'>".__('monthly')."</label>\n&nbsp;&nbsp;";
        $output.="<input type='radio' id='year' name='scaling2' value='year'";
        if ($scaling2 == "year") $output.= " checked='checked'";
        $output.= " /><label for='year'>".__('annually')."</label>\n&nbsp;&nbsp;";
        $output.= '</fieldset>';
        $output.= '</fieldset>';


    }
    else $output.="</fieldset>";
    $output.= "&nbsp; &nbsp;<input type='submit' class='buttonklein' value=".__('GO')." />\n";
    $output.= "</form>\n";

    $output.= "<br /><br /><br />\n<table border='1' cellspacing='0' cellpadding='0'>\n<tr>\n";
    $output.= "<td>&nbsp;</td>\n";
    // write values
    $months = round((mktime(0,0,0,$end_month,1,$end_year)-mktime(0,0,0,$start_month,1,$start_year))/(86400*30))+1;

    // short range? -> print each month
    if ($months <= $values) {
        $width = $pixel/$values;
        for ($i = 0; $i < $months; $i++) {
            $a = date("Y-m", mktime(0,0,0,($start_month+$i),1,$start_year));
            $output.= "<td width='$width'>$a</td>\n";
        }
        $output.= "</tr>\n";
    }
    // higher range: -> just x values
    else {
        // try to find within reasonable frame whether more values would fit it
        if ($months <= 24) $values = $months;
        // number of months which will fall additionally into the last table cell
        $last_part = (($months/$values)-(floor($months/$values)))*$values;
        for ($i = 0; $i < $values; $i++) {
            if ($i < ($values-1)) {
                if ($last_part > 0) {
                    // calculate the width for all cells except the last one
                    $total_width = $pixel - floor($pixel*($last_part/$months));
                    $width = floor($total_width/$values);
                }
                else {
                    $width = floor($pixel/$values);
                }
                $sumwidth = $sumwidth + $width;
            }
            else {
                $width = $pixel - $sumwidth;
            }
            $j = $i*floor($months/$values);
            $a = date("Y-m", mktime(0,0,0,($start_month+$j),1,$start_year));
            $output.= "<td width='$width'>$a</td>\n";
        }
        $output.= "</tr>\n";
    }
    // end display range
    // *****************


    // ******************
    // print the projects
    foreach ($projectlist as $project) {

        // check whether this project is within the timeframe
        if ( $project[3] < $anfang or $project[2] > $ende) {}
        else {

            // define left edge of block
            if ($project[2] <= $anfang) {
                $edgeleft = 0;
                $t1 = 0;
            }
            else {
                $st = explode("-", $project[2]);
                $edgeleft = floor((mktime(0,0,0,$st[1],$st[2],$st[0]) - mktime(0,0,0,$start_month,$start_day,$start_year))/86400*$pixel/$range);
                $t1 = $edgeleft;
            }
            // define right edge of block
            if ($project[3] >= $ende) $edgeright = $pixel;
            else {
                $st = explode("-", $project[3]);
                $edgeright = $pixel - floor((mktime(0,0,0,$end_month,$end_day,$end_year) - mktime(0,0,0,$st[1],$st[2],$st[0]))/86400*$pixel/$range);
            }

            // indent as transparent img on the left side
            if ($edgeright < $pixel) {
                $t2 = $pixel - $edgeright;
            }
            else {
                $t2 = 0;
            }
            $w1 = $edgeright - $edgeleft;
            $output.= "<tr><td class='align-left' width='$width_proj_names'>&nbsp;".indent_name($project[1],$project[11],'&nbsp;&nbsp;&nbsp;&nbsp;')."&nbsp;</td>\n";

            // define bar - special workaround for NN4
            if (eregi("4.7|4.6|4.5", $HTTP_USER_AGENT) and !eregi("Mozilla",$HTTP_USER_AGENT)){
                $output.= "<td colspan='$values' bgcolor='#eeeeee'><table cellspacing='0' cellpadding='0'><tr valign='bottom'>
                    <td valign='bottom'><img src='".IMG_PATH."/t.gif' width='$t1' height='5' border='0' vspace='5' alt='' /></td>
                    <td valign='bottom'><img src='".IMG_PATH."/s.gif' width='$w1' height='5' border='0' vspace='5' alt='$project[2] - $project[3]' title='$project[2] - $project[3]' /></td>
                    <td valign='bottom'><img src='".IMG_PATH."/t.gif' width='$t2' height='5' border='0' vspace='5' alt='' /></td>
                    </tr></table></td></tr>\n";
            }
            else {
                $output.= "<td colspan='$values' bgcolor='#eeeeee'><table cellspacing='0' cellpadding='0'><tr valign='bottom'>
                    <td valign='bottom'><img src='".IMG_PATH."/t.gif' width='$t1' height='5' border='0' vspace='5' alt='' style='background-color:transparent;' /></td>
                    <td valign='bottom'><img src='".IMG_PATH."/t.gif' width='$w1' height='5' border='0' vspace='5' style='background-color:".$colours[$project[6]].";' alt='$project[2] - $project[3]' title='$project[2] - $project[3]' /></td>
                    <td valign='bottom'><img src='".IMG_PATH."/t.gif' width='$t2' height='5' border='0' vspace='5' alt='' style='background-color:transparent;' /></td>
                    </tr></table></td></tr>\n";
            }
        }
    }
    $output.= "</table><br />\n";

    // add legend
    if (!$add_legend and (!eregi("4.7|4.6|4.5", $HTTP_USER_AGENT) or eregi("Mozilla",$HTTP_USER_AGENT))) {
        $output.= add_Legend($categories,$colours);

    }
}
// end print projects
// ************************

echo $output;

function define_timeframe() {
    global $anfang, $ende, $sql_user_group, $start_month, $start_day, $start_year;
    global $end_day, $end_month, $end_year, $single_project, $scaling;

    // set lowest stat month and year
    if ($scaling == "auto") {
        if ($single_project) {
            $result = db_query("SELECT ID, name, anfang, ende, depend_proj
                                  FROM ".DB_PREFIX."projekte
                                 WHERE ID = ".(int)$single_project) or db_die();
        }
        else {
            $result = db_query("SELECT ID, name, anfang, ende, depend_proj
                                  FROM ".DB_PREFIX."projekte
                                 WHERE parent = 0
                                   AND $sql_user_group") or db_die();
        }
        $projectlist = array();
        while ($row = db_fetch_row($result)) {
            $projectlist[] = $row;
        }

        foreach ($projectlist as $project) {
            // lowest
            if (!$lowest) $lowest = $project[2];
            elseif ($project[2] < $lowest) $lowest = $project[2];
            // highest
            if (!$highest) $highest = $project[3];
            elseif ($project[3] > $highest) $highest = $project[3];
        }
        $start_month = substr($lowest,5,2);
        $start_year  = substr($lowest,0,4);

        $end_month = substr($highest,5,2);
        $end_year  = substr($highest,0,4);
    }
    $start_day = "01";
    $end_day = date("t", mktime(0,0,0,$end_month+1,0,$end_year));
    $anfang  = $start_year."-".$start_month."-".$start_day;
    $ende    = $end_year."-".$end_month."-".$end_day;
    if ($ende <= $anfang) {
        $end_year  = $start_year;
        $end_month = $start_month+1;
        $ende      = $end_year."-".$end_month."-".$end_day;
    }
}

function fetch_projects() {
    global $start_day, $start_month, $start_year, $end_day, $end_month, $end_year, $single_project;
    global $sql_user_group, $ende, $anfang, $only_main, $range, $projectlist, $level, $user_ID, $user_kurz;

    // fetch projects
    $range = floor((mktime(0,0,0,$end_month,$end_day,$end_year) - mktime(0,0,0,$start_month,$start_day,$start_year))/86400);
    // fetch a branch from a single project
    if ($single_project) {
        $result = db_query("SELECT ID, ende, anfang
                              FROM ".DB_PREFIX."projekte
                             WHERE ID = ".(int)$single_project) or db_die();
    }
    // fetch main projects
    else {
        $before_where = special_sql_filter_flags('projects', xss_array($_POST));
        $where="";
        $where = special_sql_filter_flags('projects', xss_array($_POST), false);
        if (isset($_POST['use_filters']) && ($_POST['use_filters'] == "on") ) {
            $where.= main_filter('', '', '', '', 'projects','','');
        }
        $result = db_query("SELECT ID, ende, anfang
                              FROM ".DB_PREFIX."projekte $before_where
                             WHERE parent = 0 $where
                               AND (acc LIKE 'system'
                                    OR ((von = ".(int)$user_ID." OR acc LIKE 'group' OR acc LIKE '%\"$user_kurz\"%')
                                        AND $sql_user_group))".sort_string('project_gantt')) or db_die();
    }
    while ($row = db_fetch_row($result)) {
        if ($row[2] <= $ende and $row[1] >= $anfang) {
            $liste[] = $row[0];
        }
    }

    foreach ($liste as $ID) {
        $projectlist[] = $ID;
        $level = 0;
        // fetch subprojects
        if (!$only_main) {
            $level++;
            sub2($ID);
            $level --;
        } // end only main projects
    }  // end loop over all projects
    $projectlist = add_values($projectlist);
} // end function

// add subprojects to Gantt
function sub2($ID) {
    global $sql_user_group, $projectlist, $anfang, $ende, $level, $single_project, $user_ID, $user_kurz;

    $result = db_query("SELECT ID, ende, anfang
                          FROM ".DB_PREFIX."projekte
                         WHERE parent = ".(int)$ID." 
                           AND (acc LIKE 'system'
                                OR ((von = ".(int)$user_ID." OR acc LIKE 'group' OR acc LIKE '%\"$user_kurz\"%')
                                    AND $sql_user_group))".sort_string('project_gantt')) or db_die();
    while ($row = db_fetch_row($result)) {
        if ($row[2] <= $ende and $row[1] >= $anfang) { $liste[] = $row[0]; }
    }
    if ($liste[0] > 0) {
        // sort the projects on this sublevel
        foreach ($liste as $ID) {
            $projectlist[] = $ID;
            $level++;
            sub2($ID);
            $level--;
        }
    }
}

function add_values($liste) {
    global $level;
    $projectlist = array();
    foreach ($liste as $project) {
        $result = db_query("SELECT ID, name, anfang, ende, depend_proj, status,
                                   kategorie, chef, personen, status, depend_mode
                              FROM ".DB_PREFIX."projekte
                             WHERE ID = ".(int)$project) or db_die();
        $row = db_fetch_row($result);
        $level = 0;
        $row[] = fetch_sublevel($row[0]);
        $projectlist[] = $row;
    }
    return $projectlist;
}

function fetch_sublevel($ID) {
    global $level;

    $result = db_query("SELECT parent
                          FROM ".DB_PREFIX."projekte
                         WHERE ID = ".(int)$ID) or db_die();
    $row = db_fetch_row($result);
    if ($row[0] > 0) {
        $level++;
        fetch_sublevel($row[0]);
    }
    return $level;
}


// ************
// experimental

// chart part
function add_chart($project) {
    global $number, $activity_list, $projects_ref,$colour,$colours;
    global $chefl, $person, $categorie, $categories;

    $number++;

    // getting participants
    $participants = '';

    // use project_users_rel instead of personen
    $temp_result = db_query("SELECT user_ID FROM ".DB_PREFIX."project_users_rel
                                    WHERE project_ID = ".(int)$project[0]." ");

    while ($temp_row = db_fetch_row($temp_result)) {
        $participants .= slookup('users','nachname,vorname','ID',$temp_row[0],'1').',';
    }
    if (strlen($participants) > 0) {
        $participants = substr($participants,0,-1);
    }

    /*
    if (strlen($project[8]) < 6) $participants = '';
    else $participants = implode(',',unserialize($project[8]));
    */

    $inarraydata[] = indent_name($project[1],$project[11],'    ');
    if ($chefl == 'on'){
        $cheftemp=slookup('users','nachname','ID',$project[7],'1');
        //fix falls chef nicht mehr existiert
        if(empty($cheftemp)){
            $cheftemp='';
        }
        $inarraydata[]=$cheftemp;
    }

    if ($person=='on')    $inarraydata[] = $participants;
    if ($categorie=='on') $inarraydata[] = $categories[$project[6]];
    $activity = new GanttBar($number,
    $inarraydata,
    $project[2],
    $project[3]);

    // Yellow diagonal line pattern on a red background
    $activity->SetPattern(BAND_RDIAG,"yellow");
    if($colour=='on')$activity->SetFillColor($colours[$project[6]]);
    else $activity->SetFillColor("red");

    // set progress inline bar
    $status = $project[9]/100;
    $activity->progress->Set($status);

    // Set absolute height
    $activity->SetHeight(8);
    // $activity->progress->SetPattern(BAND_HVCROSS,"blue");

    $activity_list[$project[0]] = $activity;

    // save relation number - projectID
    $projects_ref[$project[0]] = $number;
}

function indent_name($name, $level, $blank=' ') {
    // first table cell: name of project and indentation
    for ($b = 0; $b < $level; $b++) {
        $indent .= $blank;
    }
    return $indent.html_out($name);
}

function add_Legend($categories,$colours){
    $out= "&nbsp;&nbsp;<b>".__('Legend')."</b><table cellpadding='3' cellspacing='0' border='1'>\n<tr>";
    // no value
    $out.= "<td><img src='".IMG_PATH."/t.gif' style='background-color:black;' width='7' alt='black' /> ".__('No value')."</td>\n";
    foreach ($categories as $cat_ID => $cat_name) {
        $out.= "<td><img src='".IMG_PATH."/t.gif' style='background-color:".$colours[$cat_ID].";' width='7' alt='$colours[$cat_ID]' /> ".$cat_name."</td>\n";
    }
    $out.= "</tr></table>\n";
    return $out;
}
?>
