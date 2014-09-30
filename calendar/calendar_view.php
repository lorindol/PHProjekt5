<?php
/**
* calendar list view with context menu
*
* @package    calendar
* @module     view
* @author     Albrecht Guenther, $Author: alexander $
* @licence    GPL, see www.gnu.org/copyleft/gpl.html
* @copyright  2000-2006 Mayflower GmbH www.mayflower.de
* @version    $Id: calendar_view.php,v 1.56.2.4 2007/01/23 15:35:48 alexander Exp $
*/
if (!defined('lib_included')) die('Please use index.php!');
require_once(PATH_PRE.'lib/dbman_lib.inc.php');

$output = '';
$fields = build_array('calendar', $ID, $mode); ;
sort_mode($module,'datum');
html_editor_mode($module);
$is_related_obj = isset($is_related_obj) ? (bool) $is_related_obj : false;
$rule = isset($rule) ? check_rule($rule) : '';
$filter_ID = isset($filter_ID) ? (int) $filter_ID : null;
$operator = isset($operator) && $operator == 'OR' ? $operator : ' AND ';

if (isset($save_tdwidth)) store_column_width($module);

// perform status actions: accept or reject events
require_once('./calendar.inc.php');
$action = isset($action) ? qss($action) : '';
if ($action == 'set_status') {
    calendar_set_event_status($ID_s, $action2);
}

// ************
// context menu

$listentries_single = array();

$csrftoken = make_csrftoken();

// entries for right mouse menu - action for selected records
$listentries_selected = array(
    '1'=>array('proc_marked',PATH_PRE."calendar/calendar.php?mode=view&amp;view=$view&amp;action=set_status&amp;csrftoken=$csrftoken&amp;action2=1&amp;ID_s=",'','',__('not yet decided')),
    '2'=>array('proc_marked',PATH_PRE."calendar/calendar.php?mode=view&amp;view=$view&amp;action=set_status&amp;csrftoken=$csrftoken&amp;action2=2&amp;ID_s=",'','',__('accept')),
    '3'=>array('proc_marked',PATH_PRE."calendar/calendar.php?mode=view&amp;view=$view&amp;action=set_status&amp;csrftoken=$csrftoken&amp;action2=3&amp;ID_s=",'','',__('reject')),
    '4'=>array('proc_marked',PATH_PRE."calendar/calendar.php?mode=view&amp;view=$view&amp;set_archiv_flag=1&amp;csrftoken=$csrftoken&amp;ID_s=",'','',__('Move to archive')),
    '5'=>array('proc_marked',PATH_PRE."calendar/calendar.php?mode=view&amp;view=$view&amp;set_archiv_flag=0&amp;csrftoken=$csrftoken&amp;ID_s=",'','',__('Take back from Archive')),
);

// context menu
include_once(PATH_PRE.'lib/contextmenu.inc.php');
$link = isset($link) ? xss($link) : '';
contextmenu::draw_contextmenus($module, $link, $is_related_obj, $listentries_single, $listentries_selected, $contextmenu);
// ****************

// start navigation

// call the main filter routine
// if there isn't any filter defined, you get own future events.
if ( !isset($flist[$module][0]) && !isset($flist_store[$module][0]) &&
     !isset($_GET['filter']) && !$filter_ID && !isset($_GET['direction'])) {
    $f_sort['calendar']['sort']      = 'datum,anfang';
    $f_sort['calendar']['direction'] = 'ASC';

    $_filter  = 'datum';
    $_rule    = '>=';
    $_keyword = sprintf("%04d-%02d-%02d", $year, $month, $day);
    $_keyword = $date_format_object->convert_db2user($_keyword);
    $where0   = main_filter($_filter, $_rule, $_keyword, 0, 'calendar','',$operator);

    $_filter  = 'an';
    $_rule    = 'exact';
    $_keyword = ($view == 4 && $act_for) ? $act_for : $user_ID;
    $where1   = main_filter($_filter, $_rule, $_keyword, 1, 'calendar','',$operator);
}

// distinction
if ($view == 4 && $act_for) {
    // 1. case: act as proxy
    $where_array = array("von = ".(int)$act_for." ", "an = ".(int)$act_for." ");
}
else {
    // 2. case: my own calendar (default)
    $where_array = array("von = ".(int)$user_ID." ", "an = ".(int)$user_ID." ");
}

// FIXME: the next one is a performance hack; make it better asap!
$where = '(';
$ii = 0;
foreach ($where_array as $where_an) {
    if ($ii == 0)
        $where .= $where_an;
    else
        $where .= ' OR '. $where_an;
    $ii++;
}
$where .= ') ';
$where .= main_filter($filter, $rule, $keyword, $filter_ID, 'calendar','',$operator);


$result = db_query("SELECT ID
                      FROM ".DB_PREFIX."termine
                           ".sql_filter_flags($module, array('archive', 'read'))."
                     WHERE $where
                           ".sql_filter_flags($module, array('archive', 'read'), false)) or db_die();
$liste = make_list($result);

$add_paras = array();
if ($act_for) {
    $add_paras['hidden'] = array('act_for' => $act_for);
    $add_paras['hidden'] = array('view'    => $view);
}
$output .= '<div id="bars">';
$output .= get_filter_execute_bar('calendar', true, $add_paras);
$output .= get_filter_edit_bar();
$output .= get_status_bar();
$output .= get_top_page_navigation_bar();
$output .= '</div>';

$where = " WHERE $where
                 ".sql_filter_flags($module, array('archive', 'read'), false)."
                 ".sort_string();

// transmit the 'act_for' ID
$getstring = ($view == 4 && $act_for) ? 'act_for='.$act_for : '';

$output .= build_table(array('ID', 'von', 'ID', 'serie_id'), 'calendar', $where, $_SESSION['page'][$module], $perpage);
$output .= get_bottom_page_navigation_bar();

echo $output;

?>
