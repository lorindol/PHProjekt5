<?php

// links_view.php - PHProjekt Version 5.2
// copyright  ©  2000-2005 Albrecht Guenther  ag@phprojekt.com
// www.phprojekt.com
// Author: Albrecht Guenther, $Author: alexander $
// $Id: links_view.php,v 1.39.2.1 2007/01/17 13:05:20 alexander Exp $

// check whether the lib has been included - authentication!
if (!defined("lib_included")) die("Please use index.php!");

$module = 'links';
$_SESSION['common']['module'] = 'links';

//diropen_mode($element_mode, $element_ID);
sort_mode($module,'t_wichtung');
if ($save_tdwidth) store_column_width($module);

$csrftoken = make_csrftoken();

// entries for right mouse menu - action for selected records
$listentries_selected = array(
    '0'=>array('proc_marked', PATH_PRE."links/links.php?mode=data&amp;csrftoken=$csrftoken&amp;tree_mode=$tree_mode&amp;delete_b=1&amp;ID_s=", '', '', __('Delete'))
);

/*
// context menu
include_once(PATH_PRE.'lib/contextmenu.inc.php');
$menu3 = new contextmenu();
echo $menu3->menu_page($module);
*/

// Button reminder
if (isset($_REQUEST['reminder'])) {
    $today = date('Y-m-d');
    put_filter_value('t_reminder_datum','<=',$today);
}

// call the main filter routine
$where = main_filter($filter, $rule, $keyword, $filter_ID, 'links','',$operator);

// sort & direction
if (!$sort) $sort = "t_wichtung";

$result = db_query("SELECT t_ID
                      FROM ".DB_PREFIX."db_records
                     WHERE t_author = ".(int)$user_ID." AND t_reminder = 1
                           $where") or db_die();
$liste= make_list($result);

// tabs
$tabs = array();
$output = '<div id="global-header">';
$output .= get_tabs_area($tabs);
$output .= breadcrumb($module);
$output .= '</div>';
$output .= '<div id="global-content">';
// button bar
$output .= get_buttons_area(array(), 'oncontextmenu="startMenu(\''.$menu3->menusysID.'\',\'\',this)"');
$output .= '<div class="hline"></div>';

// ***********
// record list
// ***********
$where = " WHERE t_author = ".(int)$user_ID." AND t_reminder = 1
                 $where
                 ".sort_string();

$result_rows = '<a name="content"></a>'.build_table(array('t_ID', 't_author', 't_acc', 't_parent'), 'links', $where, $_SESSION['page'][$module], $perpage);

$output .= get_all_filter_bars('links', $result_rows);

echo $output;

?>
