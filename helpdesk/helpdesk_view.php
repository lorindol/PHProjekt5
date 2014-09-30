<?php
/**
 * helpdesk list view script
 *
 * @package    helpdesk
 * @subpackage main
 * @author     Albrecht Guenther, Nina Schmitt, $Author: nina $
 * @licence    GPL, see www.gnu.org/copyleft/gpl.html
 * @copyright  2000-2006 Mayflower GmbH www.mayflower.de
 * @version    $Id: helpdesk_view.php,v 1.77 2008-03-07 09:42:47 nina Exp $
 */

if (!defined('lib_included')) die('Please use index.php!');

// check role
if (check_role("helpdesk") < 1) die("You are not allowed to do this!");


// diropen_mode($element_mode,$element_ID);
filter_mode($filter_ID);
sort_mode($module,'submit', 'DESC');
read_mode($module);
archive_mode($module);
html_editor_mode($module);
group_mode($module);
// Archive Flag
// set_archiv_flag = 1 -> Move to archive
// set_archiv_flag = 0 -> Take back from archive
if (isset($set_archiv_flag))   set_archiv_flag($ID_s, $module, $set_archiv_flag);
$set_read_flag = isset($set_read_flag) ? (int) $set_read_flag : 0;
if ($set_read_flag > 0)            set_read_flag($ID_s,$module);
if (isset($save_tdwidth))                 store_column_width($module);
$tree_mode = isset($tree_mode) ? qss($tree_mode) : '';
$is_related_obj = isset($is_related_obj) ? (bool) $is_related_obj : false;
$rule = isset($rule) ? check_rule($rule) : '';
$filter_ID = isset($filter_ID) ? (int) $filter_ID : null;
$operator = isset($operator) ? qss($operator) : '';
$sort = isset($sort) ? qss($sort) : '';
$set_status = isset($_REQUEST['set_status']) ? intval($_REQUEST['set_status']) : 0;

if ($set_status > 0) {
    setHelpdeskStatus($ID_s, $set_status, 'helpdesk');
}
// ************
// context menu

$csrftoken = make_csrftoken();

// entries for right mouse menu - action for selected records
$listentries_selected = array(
    '0'=>array('proc_marked',PATH_PRE.$module."/".$module.".php?mode=data&amp;csrftoken=$csrftoken&amp;tree_mode=$tree_mode&amp;action=contacts&amp;delete_b=1&amp;ID_s=",'',__('Are you sure?'),__('Delete')),
    '1'=>array('proc_marked',PATH_PRE."lib/set_links.inc.php?module=".$module."&amp;csrftoken=$csrftoken&amp;ID_s=",'_blank','',__('Add to link list')),
    '2'=>array('doLink',PATH_PRE.$module."/".$module.".php?mode=data&amp;action=lockfile&amp;csrftoken=$csrftoken&amp;lock=true&amp;ID=",'','',__('Lock ticket')),
    '3'=>array('doLink',PATH_PRE.$module."/".$module.".php?mode=data&amp;action=lockfile&amp;csrftoken=$csrftoken&amp;unlock=true&amp;ID=",'','',__('Unlock ticket')),
    '4'=>array('proc_marked',PATH_PRE.$module."/".$module.".php?set_archiv_flag=1&amp;csrftoken=$csrftoken&amp;ID_s=",'','',__('Move to archive')),
    '5'=>array('proc_marked',PATH_PRE.$module."/".$module.".php?set_archiv_flag=0&amp;csrftoken=$csrftoken&amp;ID_s=",'','',__('Take back from Archive')),
    '6'=>array('proc_marked',PATH_PRE.$module."/".$module.".php?set_read_flag=1&amp;csrftoken=$csrftoken&amp;ID_s=",'','',__('Mark as read')),
    '7'=>array('proc_marked',PATH_PRE.$module."/".$module.".php?set_status=3&amp;csrftoken=$csrftoken&amp;ID_s=",'','',__('Set Status: Solved')),
    '8'=>array('proc_marked',PATH_PRE.$module."/".$module.".php?set_status=5&amp;csrftoken=$csrftoken&amp;ID_s=",'','',__('Set Status: Closed')),
    '9'=>array('proc_marked',PATH_PRE.$module."/".$module.".php?set_status=2&amp;csrftoken=$csrftoken&amp;ID_s=",'','',__('Assign to me')),
);

$listentries_single = array();
// context menu
include_once(PATH_PRE.'lib/contextmenu.inc.php');
$link = isset($link) ? xss($link) : '';
contextmenu::draw_contextmenus($module, $link, $is_related_obj, $listentries_single, $listentries_selected, $contextmenu);
// end context menu
// ****************
$where = main_filter($filter, $rule, $keyword, $filter_ID, 'helpdesk','',$operator);
$result = db_query("SELECT ID
                      FROM ".DB_PREFIX."rts
                           ".sql_filter_flags($module, array('archive', 'read'))."
                     WHERE is_deleted is NULL
                       AND (acc_read LIKE 'system'
                            OR ((von = ".(int)$user_ID." 
                                 OR assigned LIKE '$user_ID'
                                 OR acc_read LIKE 'group'
                                 OR acc_read LIKE '%\"$user_kurz\"%')".group_string($module)."
                                ))
                           $where
                           ".sql_filter_flags($module, array('archive', 'read'),false)) or db_die();
$liste = make_list($result);

// **************
// navigation bar
//$output.="<div id=\"".$field_name."\" oncontextmenu=\"startMenu('".$menu3->menusysID."','$field_name',this)\">";

// tabs
$tabs = array();
$exp = get_export_link_data('helpdesk');
$tabs[] = array('href' => $exp['href'], 'active' => $exp['active'], 'id' => 'export', 'target' => '_self', 'text' => $exp['text'], 'position' => 'right');

$output .= breadcrumb($module);
$output .= '</div>';
$output .= '<div id="global-content">';

// button bar
$buttons = array();
if (check_role("helpdesk") > 1) {
    $buttons[] = array('type' => 'link', 'href' => 'helpdesk.php?mode=forms&amp;new_note=1&amp;'.$sid, 'text' => __('New'), 'active' => false);
}
$output .= get_module_tabs($tabs,$buttons, 'oncontextmenu="startMenu(\''.$menu3->menusysID.'\',\'\',this)"');

// get all filter bars
if (!$sort) $sort = "ID DESC"; // set default criteria
$where = "    WHERE (acc_read LIKE 'system'
                     OR ((von = ".(int)$user_ID." 
                          OR assigned LIKE '$user_ID'
                          OR acc_read LIKE 'group'
                          OR acc_read LIKE '%\"$user_kurz\"%')".group_string($module)."))
                  $where ".sql_filter_flags($module, array('archive', 'read'), false).
                         sort_string();
$result_rows = '<a name="content"></a>'.build_table(array('ID', 'von', 'acc_read', 'parent'), $module, $where, $_SESSION['page'][$module], $perpage);
$output .= get_all_filter_bars('help_desk', $result_rows);
$output .= '</div>';
echo $output;

/**
 * Set the status of helpdesk items to a desired value
 *
 * @param string $ID_s
 * @param int $status
 * @param string $module
 */
function setHelpdeskStatus($ID_s, $status, $module) {
    global $user_ID, $user_kurz;
    $ID_s = split(',', $ID_s);
    $status_text = array(2 => 'Assigned', 3 => 'Solved', 5 => 'Closed');
    foreach ($ID_s as $id) {
        $assign = '';
        // only edit the assigned field in the case of "assign to me"
        if ($status == 2) {
            $assign = sprintf(', assigned="%s"', $user_ID);
        }
        $perm = "  von='".$user_ID."'
                                OR assigned='$user_ID'
                                OR ( acc_read LIKE 'system' 
                                    OR ((acc_read LIKE 'group'
                                        OR acc_read LIKE '%\"$user_kurz\"%')".group_string().")
                                AND acc_write='w')";
        
        $sql = sprintf( 'UPDATE %srts SET status="%s" %s
                          WHERE ID="%s"
                            AND (%s)', DB_PREFIX, $status, $assign, $id, $perm);
        $result = db_query($sql);
        $kat = 'notice';
        if ($result === false) {
            $kat = 'error';
            $msg = sprintf('Helpdesk item #%s couldn\'t be set to status "%s".', $ID, $status_text[$status]);
        } else {
        	$msg = sprintf('Helpdesk item #%s successfully set to status "%s".', $id, $status_text[$status]);
        }
        
        message_stack_in($msg, $module, $kat);
    }
}
?>
