<?php

// projects_view.php - PHProjekt Version 5.2
// copyright  ©  2000-2005 Albrecht Guenther  ag@phprojekt.com
// www.phprojekt.com
// Author: Albrecht Guenther, $Author: polidor $
// $Id: projects_view.php,v 1.72.2.8 2007/04/15 22:56:23 polidor Exp $

// check whether the lib has been included - authentication!
if (!defined("lib_included")) die("Please use index.php!");

// check role
if (check_role('projects') < 1) die("You are not allowed to do this!");

//diropen_mode($element_mode,$element_ID);
sort_mode($module,'next_proj,name');
read_mode($module);
archive_mode($module);
html_editor_mode($module);
group_mode($module);
// Archive Flag
// set_archiv_flag = 1 -> Move to archive
// set_archiv_flag = 0 -> Take back from archive
$set_read_flag = isset($set_read_flag) ? (int) $set_read_flag : 0;
if (isset($save_tdwidth))                 store_column_width($module);
$tree_mode = isset($tree_mode) ? qss($tree_mode) : '';
$is_related_obj = isset($is_related_obj) ? (bool) $is_related_obj : false;
$rule = isset($rule) ? check_rule($rule) : '';
$filter_ID = isset($filter_ID) ? (int) $filter_ID : null;
$operator = isset($operator) && $operator == 'OR' ? $operator : ' AND ';
if (isset($set_archiv_flag))   set_archiv_flag($ID_s, $module, $set_archiv_flag);
if ($set_read_flag > 0)            set_read_flag($ID_s,$module);
if (isset($save_tdwidth))                 store_column_width($module);



// ************
// context menu

$csrftoken = make_csrftoken();

// entries for right mouse menu - action for single record
$listentries_single = array(
    '0'=>array('doLink',PATH_PRE."index.php?module=todo&amp;mode=forms&amp;justform=1&amp;csrftoken=$csrftoken&amp;projekt_ID=",'_blank','',__('New todo')),
    '1'=>array('doLink',PATH_PRE."index.php?module=notes&amp;mode=forms&amp;justform=1&amp;csrftoken=$csrftoken&amp;projekt_ID=",'_blank','',__('New note'))
);

  // entries for right mouse menu - action for selected records
$listentries_selected = array(
    '0'=>array('proc_marked',PATH_PRE."$module/$module.php?mode=data&amp;csrftoken=$csrftoken&amp;tree_mode=$tree_mode&amp;delete_c=1&amp;ID_s=",'',__('Are you sure?'),__('Delete')),
    '1'=>array('proc_marked',PATH_PRE."lib/set_links.inc.php?module=$module&amp;csrftoken=$csrftoken&amp;ID_s=",'_blank','',__('Add to link list')),
    '2'=>array('proc_marked',PATH_PRE.$module."/".$module.".php?set_archiv_flag=1&amp;csrftoken=$csrftoken&amp;ID_s=",'','',__('Move to archive')),
    '3'=>array('proc_marked',PATH_PRE.$module."/".$module.".php?set_archiv_flag=0&amp;csrftoken=$csrftoken&amp;ID_s=",'','',__('Take back from Archive')),
    '4'=>array('proc_marked',PATH_PRE.$module."/".$module.".php?set_read_flag=1&amp;csrftoken=$csrftoken&amp;ID_s=",'','',__('Mark as read')),
    '5'=>array('proc_marked',PATH_PRE."misc/export.php?file=$module&amp;medium=csv&amp;csrftoken=$csrftoken&amp;ID_s=",'_blank','','csv Export')
);

// context menu
include_once(LIB_PATH.'/contextmenu.inc.php');
$link = isset($link) ? xss($link) : '';
contextmenu::draw_contextmenus($module, $link, $is_related_obj, $listentries_single, $listentries_selected, $contextmenu);

// end context menu
// ****************

//anfang navi


// define category
// call the main filter routine
$where = main_filter($filter, $rule, $keyword, $filter_ID, 'projects','',$operator);

// define category
if (isset($category)) $where .= " AND kategorie = ".(int) $category;

$result = db_query("SELECT ID
                    FROM ".DB_PREFIX."projekte
                    ".sql_filter_flags($module, array('archive', 'read'))."
                    WHERE (acc LIKE 'system' OR ((von = ".(int)$user_ID." OR acc LIKE 'group' OR acc LIKE '%\"$user_kurz\"%')".group_string($module).")) AND (parent = 0 OR parent IS NULL) 
                    $where ".sql_filter_flags($module, array('archive', 'read'), false)) or db_die();
$liste= make_list($result);


//tabs
$tabs = array();
$exp = get_export_link_data('projects');
$tabs[] = array('href' => $exp['href'], 'active' => $exp['active'], 'id' => 'export', 'target' => '_self', 'text' => $exp['text'], 'position' => 'right');
unset($exp);
$output = '<div id="global-header">';
$output .= get_tabs_area($tabs);
$output .= breadcrumb($module);
$output .= '</div>';
$output .= $content_div;


// button bar
$buttons = array();
if ( check_role("projects") > 1 ) {
    $buttons[] = array('type' => 'link', 'href' => $_SERVER['SCRIPT_NAME'].'?mode=forms&amp;action=new'.$sid, 'text' => __('New'), 'active' => false);
}
$buttons[] = array('type' => 'link', 'href' => $_SERVER['SCRIPT_NAME'].'?mode=options'.$sid, 'text' => __('Options'), 'active' => false);
$buttons[] = array('type' => 'link', 'href' => $_SERVER['SCRIPT_NAME'].'?mode=stat'.$sid, 'text' => __('Statistics'), 'active' => false);
$buttons[] = array('type' => 'link', 'href' => $_SERVER['SCRIPT_NAME'].'?mode=stat&amp;mode2=mystat'.$sid, 'text' => __('My Statistic'), 'active' => false);
$buttons[] = array('type' => 'link', 'href' => $_SERVER['SCRIPT_NAME'].'?mode=gantt'.$sid, 'text' => __('Gantt'), 'active' => false);

$output .= get_buttons_area($buttons, 'oncontextmenu="startMenu(\''.$menu3->menusysID.'\',\'\',this)"');

// get all filter bars
$where = " WHERE (acc LIKE 'system' OR ((von = ".(int)$user_ID." OR acc LIKE 'group' OR acc LIKE '%\"$user_kurz\"%')".group_string($module)."))
         $where
         ".sql_filter_flags($module, array('archive', 'read'), false)."
         ".sort_string('projects');
$result_rows = '<a name="content"></a>'.build_table(array('ID','von','acc','parent'), 'projects', $where, $_SESSION['page'][$module], $perpage);
$output .= get_all_filter_bars('projects', $result_rows);
$output .= '</div>';
echo $output;
$_SESSION['arrproj'] =& $arrproj;

?>
