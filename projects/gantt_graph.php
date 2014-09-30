<?php

// gantt_graph.php - PHProjekt Version 5.1
// copyright  ©  2000-2005 Albrecht Guenther  ag@phprojekt.com
// www.phprojekt.com
// Author: Nina Schmitt

// assign colours to categories
$colours = array( "#000000", "#00ff00", "#0000ff", "#ffff00", "#ff00ff", "#00ffff",
"#800080", "#c0c0c0", "#008000", "#000080", "#808000", "#ff0000" );

define('PATH_PRE','../');
include_once('../lib/lib.inc.php');
ini_set(max_execution_time, 200);

//categories: 1=offered, 2=ordered, 3=at work, 4=ended, 5=stopped, 6=reopened 7 = waiting, 10=container, 11=ext. project
$categories = array( "1" => __('offered'), "2" => __('ordered'), "3" => __('Working'), "4" => __('ended'),
"5" => __('stopped'), "6" => __('Re-Opened'), "7" => __('waiting') );
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
unset($GLOBALS['php_errormsg']);
if (!is_file(LIB_PATH."/chart/src/jpgraph.php")) die("Panic - cannot include plot library!");

include_once(LIB_PATH."/chart/src/jpgraph.php");
include_once(LIB_PATH."/chart/src/jpgraph_gantt.php");

$graph = new GanttGraph(0, 0, "auto");
$graph->SetBox();
$graph->SetShadow();
$graph->ShowHeaders(GANTT_HMONTH);
$graph->scale->tableTitle->SetFont(FF_FONT1,FS_BOLD);
$graph->scale->SetTableTitleBackground("silver");
$inarray[] = __('Project Name');
if ($chefl)     $inarray[] = __('Leader');
if ($person)    $inarray[] = __('Participants');
if ($categorie) $inarray[] = __('Category');
//$inarray[] = array(100);
$graph->scale->actinfo->SetColTitles( $inarray,array(100));
// Show day, week and month scale
if(empty($scaling2))$scaling2="week";
else $scaling2 = qss($scaling2);
if($scaling2=='auto'){
    $nrofdays = round((strtotime($ende)-strtotime($anfang))/(3600*24));
    if($nrofdays<=200)$scaling2="week";
    elseif($nrofdays<=400)$scaling2="month";
    else $scaling2="year";
}
// Show day, week and month scale
if($scaling2=="week"){
    $graph->ShowHeaders(GANTT_HWEEK | GANTT_HMONTH);
    // Instead of week number show the date for the first day in the week
    // on the week scale
    $graph->scale->week->SetStyle(WEEKSTYLE_FIRSTDAY);
    // Make the week scale font smaller than the default
    $graph->scale->week->SetFont(FF_FONT0);
    // make the month names longer
    $graph->scale->month->SetStyle(MONTHSTYLE_SHORTNAMEYEAR4);
    $graph->scale->month->SetFont(FF_FONT1,FS_BOLD);
    $graph->scale->month->SetFontColor("white");
    $graph->scale->month->SetBackgroundColor("blue");
}
if($scaling2=="month"){
    $graph->ShowHeaders(GANTT_HMONTH | GANTT_HYEAR);
    // Use the short name of the together with a 2 digit year
    // on the month scale
    $graph->scale->month->SetStyle(MONTHSTYLE_SHORTNAME);
    $graph->scale->month->SetFont(FF_FONT1,FS_BOLD);
    $graph->scale->month->SetFontColor("white");
    $graph->scale->month->SetBackgroundColor("blue");
    //$graph->scale->year->SetFontColor("white");
    //$graph->scale->year->SetBackgroundColor("blue");

}

if($scaling2=="year"){
    $graph->ShowHeaders(GANTT_HYEAR);
    //$graph->scale->year->SetStyle();
    $graph->scale->year->SetFontColor("white");
    $graph->scale->year->SetBackgroundColor("blue");
}
// 0 % vertical label margin
$graph->SetLabelVMarginFactor(1);
// Only show part of the Gantt
if ($scaling == 'manual') {
    $graph->SetDateRange($start_year.'-'.$start_month.'-01',$end_year.'-'.$end_month.'-30');
}
$graph->SetDateRange($start_year.'-'.$start_month.'-01',$end_year.'-'.$end_month.'-30');
// create bars
foreach ($projectlist as $project) {
    add_chart($project);
}

// add constraints
$dep1 = array( '4' => 'STARTEND', '3' => 'STARTSTART', '2' => 'ENDSTART', '5' => 'ENDEND');
foreach ($projectlist as $project) {
    if ($project[4] > 0 && $activity_list[$project[4]]>0) {
        // since the current project only listens to another project,
        // we have to start the contraint at the target project and end at the current project
        $activity_list[$project[4]]->SetConstrain($projects_ref[$project[0]], 'CONSTRAIN_'.$dep1[$project[10]]);
    }
}
if (sizeof($activity_list)>0) {
    // Finally add the bar to the graph
    foreach ($activity_list as $activity) {
        $graph->Add($activity);
    }
}
$graph->Stroke();
// end print projects
// ************************

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
    global $use_filters,$exclude_archived,$exclude_read;
    $flags=array();
    $flags['exclude_archived']=$exclude_archived;
    $flags['exclude_read']=$exclude_read;
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
        require_once(PATH_PRE.'lib/dbman_lib.inc.php');
        $before_where = special_sql_filter_flags('projects', $flags);
        $where="";
        $where = special_sql_filter_flags('projects', $flags, false);
        if (isset($use_filters) && ($use_filters == "on") ) {
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
    global $graph, $number, $activity_list, $projects_ref,$colour,$colours;
    global $namel, $chefl, $person, $categorie, $categories;

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

    /* deprecated
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


?>
