<?php
/**
 * @package    cost
 * @subpackage main
 * @author     Gustavo Solt, $Author: polidor $
 * @licence    GPL, see www.gnu.org/copyleft/gpl.html
 * @copyright  2000-2006 Mayflower GmbH www.mayflower.de
 * @version    $Id: costs_view.php,v 1.4.2.2 2007/08/02 16:42:59 polidor Exp $
 */

// check whether the lib has been included - authentication!
if (!defined("lib_included")) { die("Please use index.php!"); }

// check role


if (check_role("costs") < 1) { die("You are not allowed to do this!"); }

//diropen_mode($element_mode,$element_ID);
//filter_mode($filter_ID);
sort_mode($module,'name');
read_mode($module);
archive_mode($module);
html_editor_mode($module);
group_mode($module);

// Archive Flag
// set_archiv_flag = 1 -> Move to archive
// set_archiv_flag = 0 -> Take back from archive
if (isset($set_archiv_flag))   set_archiv_flag($ID_s, $module, $set_archiv_flag);
if ($set_read_flag > 0) set_read_flag($ID_s,$module);
if($save_tdwidth) { store_column_width($module); }

// ************
// context menu
   $csrftoken = make_csrftoken();
  // entries for right mouse menu - action for selected records
  $listentries_selected = array(
  '0'=>array('proc_marked',PATH_PRE.$module."/".$module.".php?mode=data&amp;csrftoken=$csrftoken&amp;tree_mode=$tree_mode&amp;action=contacts&amp;delete_c=1&amp;ID_s=",'',__('Are you sure?'),__('Delete')),
    '1'=>array('proc_marked',PATH_PRE."lib/set_links.inc.php?module=".$module."&amp;csrftoken=$csrftoken&amp;ID_s=",'_blank','',__('Add to link list')),
    '2'=>array('proc_marked',PATH_PRE.$module."/".$module.".php?mode=view&amp;set_archiv_flag=1&amp;csrftoken=$csrftoken&amp;ID_s=",'','',__('Move to archive')),
    '3'=>array('proc_marked',PATH_PRE.$module."/".$module.".php?mode=view&amp;set_archiv_flag=0&amp;csrftoken=$csrftoken&amp;ID_s=",'','',__('Take back from Archive')),
    '4'=>array('proc_marked',PATH_PRE.$module."/".$module.".php?mode=view&amp;set_read_flag=1&amp;csrftoken=$csrftoken&amp;ID_s=",'','',__('Mark as read')),
    '5'=>array('proc_marked',PATH_PRE."misc/export.php?file=$module&amp;medium=csv&amp;csrftoken=$csrftoken&amp;ID_s=",'_blank','','csv Export')
  );

  // context menu
  include_once(LIB_PATH.'/contextmenu.inc.php');
  contextmenu::draw_contextmenus($module, $link, $is_related_obj, $listentries_single, $listentries_selected, $contextmenu);
  // end context menu
// ****************

//anfang navi


// call the main filter routine
$where = main_filter($filter,$rule,$keyword,$filter_ID,'costs','',$operator);
$query = "SELECT ID
            FROM ".DB_PREFIX."costs
                 ".sql_filter_flags($module, array('archive', 'read'))."
           WHERE is_deleted is NULL
             AND (acc like 'system' or ((von = ".(int)$user_ID." or acc like 'group' or acc like '%\"$user_kurz\"%')".group_string($module)."))
                 $where ".sql_filter_flags($module, array('archive', 'read'), false);
$result = db_query(xss($query)) or db_die();

$liste= make_list($result);

//tabs
$exp = get_export_link_data('costs');
$tabs = array();
//$tabs[] = array('href' => $exp['href'], 'active' => $exp['active'], 'id' => 'export', 'target' => '_self', 'text' => $exp['text'], 'position' => 'right');
$output = '<div id="global-header">';
$output .= get_tabs_area($tabs);
$output .= breadcrumb($module);
$output .= '</div>';
$output .= $content_div;

// button bar
$buttons = array();
if ( check_role("costs") > 1 ) {
    $buttons[] = array('type' => 'link', 'href' => $_SERVER['SCRIPT_NAME'].'?mode=forms&amp;new_cost=1&amp;'.$sid, 'text' => __('New'), 'active' => false);
}
$output .= get_buttons_area($buttons, 'oncontextmenu="startMenu(\''.$menu3->menusysID.'\',\'\',this)"');
         // get_module_tabs($tabs,$buttons, 'oncontextmenu="startMenu(\''.$menu3->menusysID.'\',\'\',this)"');

// get all filter bars
if (!$sort) { $sort = "div2"; }
$where =   " where  (acc like 'system' or ((von = $user_ID or acc like 'group' or acc like '%\"$user_kurz\"%')".group_string($module)."))
                  $where
                  ".sql_filter_flags($module, array('archive', 'read'), false)."
                  ".sort_string();
$result_rows = '<a name="content"></a>'.build_table(array('ID','von','acc','parent'), 'costs', $where, $_SESSION['page'][$module], $perpage);
$output .= get_all_filter_bars('costs', $result_rows);
$output .= '</div>';
echo $output;

?>
