<?php
/**
* filemanager list view script
*
* @package    filemanager
* @module     main
* @author     Albrecht Guenther, $Author: alexander $
* @licence    GPL, see www.gnu.org/copyleft/gpl.html
* @copyright  2000-2006 Mayflower GmbH www.mayflower.de
* @version    $Id: filemanager_view.php,v 1.75.2.6 2007/01/23 15:35:47 alexander Exp $
*/
if (!defined('lib_included')) die('Please use index.php!');

// check role
if (check_role("filemanager") < 1) die("You are not allowed to do this!");
//diropen_mode($element_mode,$element_ID);
sort_mode($module,'filename');
read_mode($module);
archive_mode($module);
html_editor_mode($module);
group_mode($module);

// Archive Flag
// set_archiv_flag = 1 -> Move to archive
// set_archiv_flag = 0 -> Take back from archive
$set_read_flag = isset($set_read_flag) ? (int) $set_read_flag : 0;
if (isset($set_archiv_flag))   set_archiv_flag($ID_s, $module, $set_archiv_flag);
if ($set_read_flag > 0)            set_read_flag($ID_s,$module);
if (isset($save_tdwidth))                 store_column_width($module);
$tree_mode = isset($tree_mode) ? qss($tree_mode) : '';

// ************
// context menu
// entries for right mouse menu - action for single record

$csrftoken = make_csrftoken();

$listentries_single = array(
    '0'=>array('doLink',PATH_PRE.$module."/".$module."_down.php?mode=down&amp;mode2=attachment&amp;ID=",'','',__('Download').": ".__('Attachment')),
    '1'=>array('doLink',PATH_PRE.$module."/".$module."_down.php?mode=down&amp;mode2=inline&amp;ID=",'','',__('Download').": ".__('Inline')),
    '2'=>array('doLink',PATH_PRE.$module."/".$module.".php?mode=data&amp;action=lockfile&amp;csrftoken=$csrftoken&amp;lock=true&amp;ID=",'','',__('Lock file')),
    '3'=>array('doLink',PATH_PRE.$module."/".$module.".php?mode=data&amp;action=lockfile&amp;csrftoken=$csrftoken&amp;unlock=true&amp;ID=",'','',__('Unlock file')),
    '4'=>array('doLink',PATH_PRE.$module."/".$module.".php?mode=forms&amp;typ=f&amp;csrftoken=$csrftoken&amp;parent=",'','',__('New file here')),
    '5'=>array('doLink',PATH_PRE.$module."/".$module.".php?mode=forms&amp;typ=d&amp;csrftoken=$csrftoken&amp;parent=",'','',__('New directory here'))
);
if (PHPR_DOWNLOAD_INLINE_OPTION <> 1) {
    unset($listentries_single[1]);
}

// entries for right mouse menu - action for selected records
$listentries_selected = array(
    '0'=>array('proc_marked',PATH_PRE.$module."/".$module.".php?mode=data&amp;csrftoken=$csrftoken&amp;tree_mode=".xss($tree_mode)."&amp;action=contacts&amp;delete_b=1&amp;ID_s=",'',__('Are you sure?'),__('Delete')),
    '1'=>array('proc_marked',PATH_PRE."lib/set_links.inc.php?module=".$module."&amp;csrftoken=$csrftoken&amp;ID_s=",'_blank','',__('Add to link list')),
    '2'=>array('proc_marked',PATH_PRE.$module."/".$module.".php?set_archiv_flag=1&amp;csrftoken=$csrftoken&amp;ID_s=",'','',__('Move to archive')),
    '3'=>array('proc_marked',PATH_PRE.$module."/".$module.".php?set_archiv_flag=0&amp;csrftoken=$csrftoken&amp;ID_s=",'','',__('Take back from Archive')),
    '4'=>array('proc_marked',PATH_PRE.$module."/".$module.".php?set_read_flag=1&amp;csrftoken=$csrftoken&amp;ID_s=",'','',__('Mark as read'))
);

// context menu
include_once(LIB_PATH.'/contextmenu.inc.php');
$is_related_obj = isset($is_related_obj) ? (bool) $is_related_obj : false;
$rule = isset($rule) ? check_rule($rule) : '';
$filter_ID = isset($filter_ID) ? (int) $filter_ID : null;
$operator = isset($operator) && $operator == 'OR' ? $operator : ' AND ';
$firstchar = isset($firstchar) ? qss($firstchar) : '';
$link = isset($link) ? xss($link) : '';
contextmenu::draw_contextmenus($module, $link, $is_related_obj, $listentries_single, $listentries_selected, $contextmenu);

// end context menu
// ****************
// ****************
$where = main_filter($filter, $rule, $keyword, $filter_ID, $module, $firstchar,$operator);
$result = db_query("SELECT ID
                    FROM ".DB_PREFIX."dateien
                    ".sql_filter_flags($module, array('archive', 'read'))."
                    WHERE (acc LIKE 'system' OR ((von = ".(int)$user_ID." OR acc LIKE 'group' OR acc LIKE '%\"$user_kurz\"%')".group_string($module).")) AND (parent = 0 OR parent IS NULL) 
                    $where
                    ".sql_filter_flags($module, array('archive', 'read'), false))or db_die();

$liste= make_list($result);

// tabs
$tabs = array();
$output = '<div id="global-header">';
$output .= get_tabs_area($tabs);
$output .= breadcrumb($module);
$output .= '</div>';
$output .= $content_div;

// button bar
$buttons = array();
if (check_role("filemanager") > 1) {
    $buttons[] = array('type' => 'link', 'href' => 'filemanager.php?mode=forms&amp;new_note=1&amp;'.$sid, 'text' => __('New'), 'active' => false);
}
$output .= get_buttons_area($buttons, 'oncontextmenu="startMenu(\''.$menu3->menusysID.'\',\'\',this)"');

// begin list output - first the navigation bar

// get all filter bars
$sql = " where (acc like 'system' or ((von = $user_ID or acc like 'group' or acc like '%\"$user_kurz\"%') ".group_string($module)."))
       $where
       ".sql_filter_flags($module, array('archive', 'read'), false)."
       ".sort_string();
$contextmenu = 1;

$result_rows = '<a name="content"></a>'.build_table(array('ID','von','acc','parent'), $module, $sql, $_SESSION['page'][$module], $perpage, 'filemanager');
$output .= get_all_filter_bars('filemanager', $result_rows);
$output .= '</div>';
echo $output;

?>
