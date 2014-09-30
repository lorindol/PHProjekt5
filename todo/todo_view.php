<?php

// todo_view.php - PHProjekt Version 5.2
// copyright  ©  2000-2005 Albrecht Guenther  ag@phprojekt.com
// www.phprojekt.com
// Author: Albrecht Guenther, $Author: polidor $
// $Id: todo_view.php,v 1.76.2.7 2007/04/28 16:56:48 polidor Exp $

// check whether the lib has been included - authentication!
if (!defined("lib_included")) die("Please use index.php!");

// check role
if (check_role("todo") < 1) die("You are not allowed to do this!");

sort_mode($module,'remark');
read_mode($module);
archive_mode($module);
html_editor_mode($module);
group_mode($module);
// Archive Flag
// set_archiv_flag = 1 -> Move to archive
// set_archiv_flag = 0 -> Take back from archive
if (isset($set_archiv_flag))   set_archiv_flag($ID_s, $module, $set_archiv_flag);
$set_read_flag = isset($set_read_flag) ? (int) $set_read_flag : 0;
$set_status = isset($set_status) ? (int) $set_status : -1;
if ($set_read_flag > 0)            set_read_flag($ID_s, $module);
if ($set_status == 3)              accept($ID_s, $module, 3);
if (isset($save_tdwidth))                 store_column_width($module);
$tree_mode = isset($tree_mode) ? qss($tree_mode) : '';
$is_related_obj = isset($is_related_obj) ? (bool) $is_related_obj : false;
$rule = isset($rule) ? check_rule($rule) : '';
$filter_ID = isset($filter_ID) ? (int) $filter_ID : null;

$listentries_single = array();
// ************
// context menu
// entries for right mouse menu - action for single record

$csrftoken = make_csrftoken();

// entries for right mouse menu - action for selected records
$listentries_selected = array(
    '0'=>array('proc_marked',PATH_PRE.$module."/".$module.".php?mode=data&amp;csrftoken=$csrftoken&amp;tree_mode=$tree_mode&amp;action=contacts&amp;delete_b=1&amp;ID_s=",'',__('Are you sure?'),__('Delete')),
    '1'=>array('proc_marked',PATH_PRE."lib/set_links.inc.php?module=".$module."&amp;csrftoken=$csrftoken&amp;ID_s=",'_blank','',__('Add to link list')),
    '2'=>array('proc_marked',PATH_PRE.$module."/".$module.".php?mode=view&amp;set_archiv_flag=1&amp;csrftoken=$csrftoken&amp;ID_s=",'','',__('Move to archive')),
    '3'=>array('proc_marked',PATH_PRE.$module."/".$module.".php?mode=view&amp;set_archiv_flag=0&amp;csrftoken=$csrftoken&amp;ID_s=",'','',__('Take back from Archive')),
    '4'=>array('proc_marked',PATH_PRE.$module."/".$module.".php?mode=view&amp;set_read_flag=1&amp;csrftoken=$csrftoken&amp;ID_s=",'','',__('Mark as read')),
    '5'=>array('proc_marked',PATH_PRE.$module."/".$module.".php?mode=view&amp;set_status=3&amp;csrftoken=$csrftoken&amp;ID_s=",'','',__('OK'))
);

// context menu
include_once(PATH_PRE.'lib/contextmenu.inc.php');
$link = isset($link) ? xss($link) : '';
contextmenu::draw_contextmenus($module, $link, $is_related_obj, $listentries_single, $listentries_selected, $contextmenu);
#$menu3 = new contextmenu();
#$output = $menu3->menu_page($module);

// end context menu
// ****************

$where = main_filter($filter, $rule, $keyword, $filter_ID, 'todo', '',$operator);

$query = "SELECT ID
              FROM ".DB_PREFIX."todo
                   ".sql_filter_flags($module, array('archive', 'read'))."
             WHERE (acc LIKE 'system'
                    OR ((von = ".(int)$user_ID."
                         OR acc LIKE 'group'
                         OR acc LIKE '%\"$user_kurz\"%')
                       ".group_string($module)."))
                   $where
                   ".sql_filter_flags($module, array('archive', 'read'), false);
$result = db_query($query) or db_die();

$liste = make_list($result);

// tabs
$tabs   = array();
$tmp    = get_export_link_data('todo', false);
$tabs[] = array('href' => $tmp['href'], 'active' => $tmp['active'], 'id' => 'tab4', 'target' => '_self', 'text' => $tmp['text'], 'position' => 'right');
$output .= '<div id="global-header">';
$output .= get_tabs_area($tabs);
$output .= breadcrumb($module);
$output .= '</div>';

// button bar
$buttons = array();
if (!isset($todo_view_both) and check_role("todo") > 1) {
    $buttons[] = array('type' => 'link', 'href' => 'todo.php?mode=forms&amp;new_note=1&amp;'.$sid, 'text' => __('New'), 'active' => false);
}
$output .= $content_div;
$output .= get_buttons_area($buttons, 'oncontextmenu="startMenu(\''.$menu3->menusysID.'\',\'\',this);"');

// get all filter bars
$where = " WHERE (acc LIKE 'system' OR
                  ((von = ".(int)$user_ID."
                    OR acc LIKE 'group'
                    OR acc LIKE '%\"$user_kurz\"%')
                   ".group_string($module)."))
         $where
         ".sql_filter_flags($module, array('archive', 'read'), false)."
         ".sort_string();
$result_rows = '<a name="content"></a>'.build_table(array('ID', 'von', 'acc', 'parent'), 'todo', $where, $_SESSION['page'][$module], $perpage);
$output .= get_all_filter_bars('todo', $result_rows);

$output .= '</div>';
echo $output;


function accept($ID, $module, $status) {
    global $user_ID;

    $arr_ID = explode(',', $ID);
    foreach ($arr_ID as $ID) {
        $result = db_query("SELECT ext, von, acc_write
                              FROM ".DB_PREFIX."todo
                             WHERE ID = ".(int)$ID) or db_die();
        $row = db_fetch_row($result);
        if (($row[0] <> $user_ID and $row[2] <> 'w' and $row[1] <> $user_ID) or check_role("todo") < 2) {}
        else if ($row[0] > 0 ) {}
        else {
            // assign this todo to the current user
            $result = db_query("UPDATE ".DB_PREFIX."todo
                                   SET ext    = $user_ID,
                                       sync2  = '$dbTSnull',
                                       status = 3
                                 WHERE ID = ".(int)$ID) or db_die();
        }
    }
}

?>
