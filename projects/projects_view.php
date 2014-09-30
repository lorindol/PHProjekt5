<?php
/**
 * View list for projects
 *
 * @package    projects
 * @subpackage main
 * @author     Albrecht Guenther, $Author: gustavo $
 * @licence    GPL, see www.gnu.org/copyleft/gpl.html
 * @copyright  2000-2006 Mayflower GmbH www.mayflower.de
 * @version    $Id: projects_view.php,v 1.84 2008-02-04 15:09:32 gustavo Exp $
 */

// check whether the lib has been included - authentication!
if (!defined("lib_included")) die("Please use index.php!");

// check role
if (check_role('projects') < 1) die("You are not allowed to do this!");

include_once(LIB_PATH."/module_navigation.inc.php");

//diropen_mode($element_mode,$element_ID);
filter_mode($filter_ID);
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
$operator = isset($operator) ? qss($operator) : '';
if (isset($set_archiv_flag))   set_archiv_flag($ID_s, $module, $set_archiv_flag);
if ($set_read_flag > 0)            set_read_flag($ID_s,$module);
if (isset($save_tdwidth))                 store_column_width($module);
if (isset($_REQUEST['set_favorite'])) {
	set_favorite($ID_s, $user_ID, $_REQUEST['set_favorite'] == '0' ? 0 : 1);
}


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
    '5'=>array('proc_marked',PATH_PRE."misc/export.php?file=$module&amp;medium=csv&amp;csrftoken=$csrftoken&amp;ID_s=",'_blank','','csv Export'),
    '6'=>array('proc_marked',PATH_PRE.$module."/".$module.".php?set_favorite=1&amp;csrftoken=$csrftoken&amp;ID_s=",'','',__('Add to favorites'))
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
                    WHERE (acc LIKE 'system' OR ((von = ".(int)$user_ID." OR acc LIKE 'group' OR acc LIKE '%\"$user_kurz\"%')".
                          group_string($module)."))
                      AND (parent = 0 OR parent IS NULL)
                          $where ".sql_filter_flags($module, array('archive', 'read'), false)."
                      AND ".DB_PREFIX."projekte.is_deleted is NULL") or db_die();

$liste= make_list($result);

if (PHPR_PROJECT_PROGRESS == 1) {
    $progress_ratio_array = get_project_ratio_info($liste);
}

//tabs
$tabs = array();
$exp = get_export_link_data('projects');
$tabs[] = array('href' => $exp['href'], 'active' => $exp['active'], 'id' => 'export', 'target' => '_self', 'text' => $exp['text'], 'position' => 'right');
unset($exp);

$output .= breadcrumb($module);
$output .= '</div>';
$output .= $content_div;
// button bar

$buttons[] = array();
$module_nav = new PHProjekt_Module_Navigation($tabs, $buttons, 'oncontextmenu="startMenu(\''.$menu3->menusysID.'\',\'\',this)"');
$output .= $module_nav ->get_output();

// get all filter bars
$where = " WHERE (acc LIKE 'system' OR ((von = ".(int)$user_ID." OR acc LIKE 'group' OR acc LIKE '%\"$user_kurz\"%')".group_string($module)."))
         $where
         ".sql_filter_flags($module, array('archive', 'read'), false)."
         ".sort_string();
$result_rows = '<a name="content"></a>'.build_table(array('ID','von','acc','parent'), 'projects', $where, $_SESSION['page'][$module], $perpage);
$output .= get_all_filter_bars('projects', $result_rows);
$output .= '</div>';
echo $output;
$_SESSION['arrproj'] =& $arrproj;

?>
