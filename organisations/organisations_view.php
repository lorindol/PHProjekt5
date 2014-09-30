<?php
/**
 * organisations list view
 *
 * @package    organisations
 * @subpackage organisations
 * @author     Gustavo Solr, Norbert Ku:ck, $Author: gustavo $
 * @licence    GPL, see www.gnu.org/copyleft/gpl.html
 * @copyright  2000-2006 Mayflower GmbH www.mayflower.de
 * @version    $Id: 
 */

if (!defined('lib_included')) die('Please use index.php!');

// check role
if (check_role('organisations') < 1) die('You are not allowed to do this!');

// diropen_mode($element_mode, $element_ID);
filter_mode($filter_ID);
sort_mode($module,'name');
read_mode($module);
archive_mode($module);
html_editor_mode($module);
group_mode($module);
// Archive Flag
// set_archiv_flag = 1 -> Move to archive
// set_archiv_flag = 0 -> Take back from archive
if (isset($set_archiv_flag))    set_archiv_flag($ID_s, $module, $set_archiv_flag);
if ($set_read_flag > 0)         set_read_flag($ID_s, $module);
if ($save_tdwidth)              store_column_width($module);

// ************
// context menu
// entries for right mouse menu - action for single record

$csrftoken = make_csrftoken();

$listentries_single = array(
);

// entries for right mouse menu - action for selected records
$listentries_selected = array(
    '0'=>array('proc_marked',PATH_PRE.$module."/".$module.".php?mode=data&amp;up=$up&amp;sort=$sort&amp;csrftoken=$csrftoken&amp;tree_mode=$tree_mode&amp;delete_b=1&amp;ID_s=",'',__('Are you sure?'),__('Delete')),
    '1'=>array('proc_marked',PATH_PRE."lib/set_links.inc.php?module=".$module."&amp;csrftoken=$csrftoken&amp;ID_s=",'_blank','',__('Add to link list')),
    '2'=>array('proc_marked',PATH_PRE.$module."/".$module.".php?set_archiv_flag=1&amp;csrftoken=$csrftoken&amp;ID_s=",'','',__('Move to archive')),
    '3'=>array('proc_marked',PATH_PRE.$module."/".$module.".php?set_archiv_flag=0&amp;csrftoken=$csrftoken&amp;ID_s=",'','',__('Take back from Archive')),
    '4'=>array('proc_marked',PATH_PRE.$module."/".$module.".php?set_read_flag=1&amp;csrftoken=$csrftoken&amp;ID_s=",'','',__('Mark as read')),
    '5'=>array('proc_marked',PATH_PRE."misc/export.php?file=$module&amp;medium=csv&amp;csrftoken=$csrftoken&amp;ID_s=",'_blank','',__('Export as csv file'))
);

// context menu
include_once(LIB_PATH.'/contextmenu.inc.php');
contextmenu::draw_contextmenus($module, $link, $is_related_obj, $listentries_single, $listentries_selected, $contextmenu);
// end context menu
// ****************

// call the main filter routine
$where = main_filter($filter, $rule, $keyword, $filter_ID, 'organisations','',$operator);
$query = "SELECT ID
            FROM ".DB_PREFIX."organisations
                 ".sql_filter_flags($module, array('archive', 'read'))."
           WHERE ".DB_PREFIX."organisations.is_deleted is NULL
                 AND (acc LIKE 'system'
                 OR ((von = ".(int)$user_ID." 
                 OR acc LIKE 'group'
                 OR acc LIKE '%\"$user_kurz\"%')
                 ".group_string($module)."))
                 $where ".sql_filter_flags($module, array('archive', 'read'), false);
$result = db_query($query) or db_die();

$liste = make_list($result);

// tabs
$tabs = array();
$tabs[] = array('href' => '../contacts/contacts.php', 'active' => false, 'id' => '', 'target' => '_self', 'text' => __('Contacts'), 'position' => 'left');
$tabs[] = array('href' => '../contacts/members.php', 'active' => false, 'id' => '', 'target' => '_self', 'text' => __('Group members'), 'position' => 'left');

$exp = get_export_link_data($module);
$tabs[] = array('href' => $exp['href'], 'active' => $exp['active'], 'id' => 'export', 'target' => '_self', 'text' => $exp['text'], 'position' => 'right');
unset($exp);

$output = '<div id="global-header">';
$output .= get_tabs_area($tabs);
$output .= breadcrumb($module, breadcrumb_data($action));
$output .= '</div>';
$output .= $content_div;

// button bar
$buttons = array();
if(check_role($module)==2){
    $buttons[] = array('type' => 'link', 'href' => $_SERVER['SCRIPT_NAME'].'?mode=forms&amp;action=new'.$sid, 'text' => __('New'), 'active' => false);
}

$output .= get_buttons_area($buttons, 'oncontextmenu="startMenu(\''.$menu3->menusysID.'\',\'\',this)"');
$add['hidden'] = array();
$output .= '<div id="bars">';
$output .= get_filter_execute_bar('organisations_manager', true,$add);
$output .= get_filter_edit_bar(true);
$output .= get_status_bar();
$output .= get_top_page_navigation_bar();
$output .= '</div>';
$output .= '<a name="content"></a>';

// build sql string
$sql = " WHERE (acc LIKE 'system'
               OR ((von = ".(int)$user_ID." 
               OR acc LIKE 'group'
               OR acc LIKE '%\"$user_kurz\"%')
               ".group_string($module)."))
               $where
               ".sql_filter_flags($module, array('archive', 'read'), false)."
               ".sort_string();

// *******************************
// list view for organisations
$getstring = '';
$output .= build_table(array('ID','von','acc','parent'), $module, $sql, $_SESSION['page'][$module], $perpage);

$output .= get_bottom_page_navigation_bar();

echo $output;

$_SESSION['arrproj'] =& $arrproj;

?>
