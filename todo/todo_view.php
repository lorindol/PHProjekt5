<?php
/**
 * @package    todo
 * @subpackage main
 * @author     Albrecht Guenther, $Author: gustavo $
 * @licence    GPL, see www.gnu.org/copyleft/gpl.html
 * @copyright  2000-2006 Mayflower GmbH www.mayflower.de
 * @version    $Id: todo_view.php,v 1.87 2008-02-04 15:09:33 gustavo Exp $
 */

// check whether the lib has been included - authentication!
if (!defined("lib_included")) die("Please use index.php!");

// check role
if (check_role("todo") < 1) die("You are not allowed to do this!");


include_once(LIB_PATH."/module_navigation.inc.php");

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
if ($set_status == 3)              changeTodoStatus($ID_s, $module, 3);
if ($set_status == 5)              changeTodoStatus($ID_s, $module, 5);
if (isset($save_tdwidth))                store_column_width($module);
$tree_mode = isset($tree_mode) ? qss($tree_mode) : '';
$is_related_obj = isset($is_related_obj) ? (bool) $is_related_obj : false;
$rule = isset($rule) ? check_rule($rule) : '';
$filter_ID = isset($filter_ID) ? (int) $filter_ID : null;
$operator = isset($operator) ? qss($operator) : '';

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
    '5'=>array('proc_marked',PATH_PRE.$module."/".$module.".php?mode=view&amp;set_status=3&amp;csrftoken=$csrftoken&amp;ID_s=",'','',__('Set Status to Accepted')),
    '6'=>array('proc_marked',PATH_PRE.$module."/".$module.".php?mode=view&amp;set_status=5&amp;csrftoken=$csrftoken&amp;ID_s=",'','',__('Set Status to Finished')),
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
             WHERE is_deleted is NULL
                   AND (acc LIKE 'system'
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
$output .= breadcrumb($module);
$output .= '</div>';

// button bar
$buttons = array();
$output .= $content_div;

$module_nav = new PHProjekt_Module_Navigation($tabs, $buttons, 'oncontextmenu="startMenu(\''.$menu3->menusysID.'\',\'\',this)"');
$output .= $module_nav ->get_output();
// get all filter bars
$where = " WHERE is_deleted is NULL AND (acc LIKE 'system' OR
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


function changeTodoStatus($ID, $module, $status) {
    global $user_ID;

    $arr_ID = explode(',', $ID);
    foreach ($arr_ID as $ID) {
        $result = db_query("SELECT ext, von, acc_write, remark, status
                              FROM ".DB_PREFIX."todo
                             WHERE ID = ".(int)$ID."
                               AND is_deleted is NULL") or db_die();
        $row = db_fetch_row($result);
        unset($result);
        // set to "accepted"
        $msg = null;
        if (3 == $status) {
        	if (($row[0] <> $user_ID and $row[2] <> 'w' and $row[1] <> $user_ID) or check_role("todo") < 2) {}
            else if ($row[0] > 0) {}
            else {
                // assign this todo to the current user
                $result = db_query("UPDATE ".DB_PREFIX."todo
                                   SET ext    = $user_ID,
                                       sync2  = '$dbTSnull',
                                       status = 3
                                 WHERE ID = ".(int)$ID) or db_die();
                $msg = sprintf('Todo item #%s "%s" successfully set to "Accepted".', $ID, $row[3]);
                $kat = 'notice';
            }

        // set to "finished"
        } else if (5 == $status) {
            if (5 == $row[4]) {
                $msg = sprintf('Todo item #%s "%s" is already "Finished".', $ID, $row[3]);
                $kat = 'notice';
            } elseif ($row[0] == $user_ID) {
                $result = db_query("UPDATE ".DB_PREFIX."todo
                                   SET ext    = $user_ID,
                                       sync2  = '$dbTSnull',
                                       progress = 100,
                                       status = 5
                                 WHERE ID = ".(int)$ID) or db_die();
                $msg = sprintf('Todo item #%s "%s" successfully set to "Finished".', $ID, $row[3]);
                $kat = 'notice';
            } else {
                $msg = sprintf('Failed to set todo item #%s "%s" status to "Finished".', $ID, $row[3]);
                $kat = 'error';
            }
        }
        if ($msg) message_stack_in($msg, $module, $kat);
        $msg = null;
    }
}
?>