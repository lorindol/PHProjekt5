<?php
/**
* contacts list view
*
* @package    contacts
* @module     external contacts
* @author     Albrecht Guenther, Norbert Ku:ck, $Author: alexander $
* @licence    GPL, see www.gnu.org/copyleft/gpl.html
* @copyright  2000-2006 Mayflower GmbH www.mayflower.de
* @version    $Id: contacts_view.php,v 1.100.2.8 2007/01/23 15:35:48 alexander Exp $
*/
if (!defined('lib_included')) die('Please use index.php!');

// check role
if (check_role('contacts') < 1) die('You are not allowed to do this!');

// diropen_mode($element_mode, $element_ID);
sort_mode($module,'nachname');
read_mode($module);
archive_mode($module);
html_editor_mode($module);
group_mode($module);

// Archive Flag
// set_archiv_flag = 1 -> Move to archive
// set_archiv_flag = 0 -> Take back from archive
$set_read_flag = isset($set_read_flag) ? (int) $set_read_flag : 0;
if (isset($set_archiv_flag))   set_archiv_flag($ID_s, $module, $set_archiv_flag);
if ($set_read_flag > 0)         set_read_flag($ID_s, $module);
if (isset($save_tdwidth))              store_column_width($module);

// ************
// context menu
// entries for right mouse menu - action for single record

$csrftoken = make_csrftoken();

$listentries_single = array(
    '0'=>array('doLink',PATH_PRE."index.php?module=todo&amp;mode=forms&amp;justform=1&amp;csrftoken=$csrftoken&amp;contact_ID=",'_top','',__('New todo')),
    '1'=>array('doLink',PATH_PRE."index.php?module=notes&amp;mode=forms&amp;justform=1&amp;csrftoken=$csrftoken&amp;contact_ID=",'_top','',__('New note'))
);

// entries for right mouse menu - action for selected records
$up = isset($up) ? (int) $up : 0;
$sort = isset($sort) ? qss($sort) : '';
$tree_mode = isset($tree_mode) ? qss($tree_mode) : '';
$listentries_selected = array(
    '0'=>array('proc_marked',PATH_PRE.$module."/".$module.".php?mode=data&amp;up=$up&amp;sort=$sort&amp;csrftoken=$csrftoken&amp;tree_mode=$tree_mode&amp;action=contacts&amp;delete_b=1&amp;ID_s=",'',__('Are you sure?'),__('Delete')),
    '1'=>array('proc_marked',PATH_PRE."lib/set_links.inc.php?module=".$module."&amp;csrftoken=$csrftoken&amp;ID_s=",'_blank','',__('Add to link list')),
    '2'=>array('proc_marked',PATH_PRE.$module."/".$module.".php?set_archiv_flag=1&amp;csrftoken=$csrftoken&amp;ID_s=",'','',__('Move to archive')),
    '3'=>array('proc_marked',PATH_PRE.$module."/".$module.".php?set_archiv_flag=0&amp;csrftoken=$csrftoken&amp;ID_s=",'','',__('Take back from Archive')),
    '4'=>array('proc_marked',PATH_PRE.$module."/".$module.".php?set_read_flag=1&amp;csrftoken=$csrftoken&amp;ID_s=",'','',__('Mark as read')),
    '5'=>array('proc_marked',PATH_PRE."misc/export.php?file=$module&amp;medium=csv&amp;csrftoken=$csrftoken&amp;ID_s=",'_blank','',__('Export as csv file'))
);

// context menu
include_once(LIB_PATH.'/contextmenu.inc.php');
$is_related_obj = isset($is_related_obj) ? (bool) $is_related_obj : false;
$link = isset($link) ? xss($link) : '';
contextmenu::draw_contextmenus($module, $link, $is_related_obj, $listentries_single, $listentries_selected, $contextmenu);
// end context menu
// ****************
$approve_contacts = isset($approve_contacts) ? (int) $approve_contacts : 0;
if ($approve_contacts) $where2 = " AND import LIKE '1'";
else $where2 = '';

// call the main filter routine
$rule = isset($rule) ? check_rule($rule) : '';
$filter_ID = isset($filter_ID) ? (int) $filter_ID : null;
$operator = isset($operator) && $operator == 'OR' ? $operator : ' AND ';
$where = main_filter($filter, $rule, $keyword, $filter_ID, 'contacts','',$operator).$where2;
$query = "SELECT ID
                      FROM ".DB_PREFIX."contacts
                           ".sql_filter_flags($module, array('archive', 'read'))."
                     WHERE (acc_read LIKE 'system'
                            OR ((von = ".(int)$user_ID." 
                            OR acc_read LIKE 'group'
                            OR acc_read LIKE '%\"$user_kurz\"%')
                           ".group_string($module)."))
                            $where ".sql_filter_flags($module, array('archive', 'read'), false);
$result = db_query($query) or db_die();

$liste = make_list($result);

// tabs
$tabs = array();
$new_direction = isset($new_direction) && in_array($new_direction, array('ASC', 'DESC')) ? $new_direction : 'ASC';
if (!$approve_contacts) {
    $tmp_contacts = './contacts.php?mode=view&amp;direction='.$new_direction.'&amp;sort=nachname&amp;tree_mode='.$tree_mode.$sid;
    $tmp_members = './members.php?mode=view&amp;direction='.$new_direction.'&amp;sort=nachname&amp;tree_mode='.$tree_mode.$sid;
    $tabs[] = array('href' => $tmp_members, 'active' => false, 'id' => 'tab1', 'target' => '_self', 'text' => __('Group members'), 'position' => 'left');
    $tabs[] = array('href' => $tmp_contacts, 'active' => true, 'id' => 'tab2', 'target' => '_self', 'text' => __('External contacts'), 'position' => 'left');
}
if (check_role("contacts") > 1 && PHPR_CONTACTS_PROFILES) {
    $tmp = './contacts.php?mode=import_forms&amp;ID='.$ID.'&amp;action='.$action.'&amp;import_contacts='.$sid;
    $tabs[] = array('href' => $tmp, 'active' => false, 'id' => 'import', 'target' => '_self', 'text' => __('Import'), 'position' => 'right');
}

$import_contacts = isset($import_contacts) ? qss($import_contacts) : '';
if (!$import_contacts) {
    $exp = get_export_link_data($module);
    $tabs[] = array('href' => $exp['href'], 'active' => $exp['active'], 'id' => 'export', 'target' => '_self', 'text' => $exp['text'], 'position' => 'right');
    unset($exp);
}

$output = '<div id="global-header">';
$output .= get_tabs_area($tabs);
$output .= breadcrumb($module, breadcrumb_data($action));
$output .= '</div>';
$output .= $content_div;

// button bar
$buttons = array();
if (check_role("contacts") > 1) {
    $buttons[] = array('type' => 'link', 'href' => $_SERVER['SCRIPT_NAME'].'?mode=forms&amp;action=new'.$sid, 'text' => __('New'), 'active' => false);

    if (PHPR_CONTACTS_PROFILES) {
        $buttons[] = array('type' => 'link', 'href' => $_SERVER['SCRIPT_NAME'].'?mode=profiles_forms&amp;action=contacts'.$sid, 'text' => __('Profiles'), 'active' => false);
    }

}

if ($approve_contacts) {
    // form start
    $hidden = array('mode'=>'data', 'input'=>1);
    if (SID) $hidden[session_name()] = session_id();
    $buttons[] = array('type' => 'form_start', 'hidden' => $hidden);
    // text
    $buttons[] = array('type' => 'text', 'text' => '<b>&nbsp;&nbsp;&nbsp;'.__('Import list').' </b>');
    // approve contacts
    $buttons[] = array('type' => 'submit', 'name' => 'imp_approve', 'value' => __('approve'), 'active' => false);
    $buttons[] = array('type' => 'submit', 'name' => 'imp_undo', 'value' => __('undo'), 'active' => false);
    // form end
    $buttons[] = array('type' => 'form_end');
    // sql
    $where2  = " AND import LIKE '1'"; // fresh imported only
}


$output .= get_buttons_area($buttons, 'oncontextmenu="startMenu(\''.$menu3->menusysID.'\',\'\',this)"');
$add['hidden'] = array('action'=>'contacts');
$output .= '<div id="bars">';
$output .= get_filter_execute_bar('contact_manager', true,$add);
$output .= get_filter_edit_bar(true);
$output .= get_status_bar();
$output .= get_top_page_navigation_bar();
$output .= '</div>';
$output .= '<a name="content"></a>';

// call the main filter routine
$where.= $where2;
// build sql string
$sql = " WHERE (acc_read LIKE 'system'
               OR ((von = ".(int)$user_ID." 
               OR acc_read LIKE 'group'
               OR acc_read LIKE '%\"$user_kurz\"%')
               ".group_string($module)."))
               $where
               $where2
               ".sql_filter_flags($module, array('archive', 'read'), false)."
               ".sort_string();

// *******************************
// list view for external contacts
$getstring = 'action=contacts';
$output .= build_table(array('ID','von','acc_read','parent'), $module, $sql, $_SESSION['page'][$module], $perpage);

$output .= get_bottom_page_navigation_bar();

echo $output;

$_SESSION['arrproj'] =& $arrproj;

?>
