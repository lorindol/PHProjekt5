<?php
/**
 * Displays related records in a module
 *
 * main records are e.g. contacts, projects, related records are e.g. events, notes, todos, mails etc.
 *
 * This file stores common functions for reading and writing
 * project-related times that are referenced from modules.
 *
 * @package    	lib
 * @subpackage 	main
 * @author     	Franz Graf, $Author: albrecht $
 * @licence     GPL, see www.gnu.org/copyleft/gpl.html
 * @copyright  	2000-2006 Mayflower GmbH www.mayflower.de
 * @version    	$Id: show_related.inc.php,v 1.70 2008-03-04 10:51:59 albrecht Exp $
 */

if (!defined('lib_included')) die('Please use index.php!');

/**
 * Show realted objects from module designer
 *
 * @param string 	$related_module     	- Module name
 * @param string 	$linkb              			- Module link
 * @param string 	$query              			- Query string
 * @param string 	$referer            			- Referer
 * @return string								HTML output
 */
function show_related($related_module, $linkb, $query='', $referer='') {
    global $mode,  $ID, $user_ID, $user_kurz, $sql_user_group, $fields, $fieldlist,$link,$operator;
    global $filter_module, $filter, $rule, $keyword, $filter_ID, $flist, $projekt_ID, $contact_ID;
    global $nrel_get, $nrel_sess, $sort_module;
    global $module, $related_objects;

    // we have to change the global(!) $module here for the filter-stuff.
    // Thus backup it now to undo that step at the end of the function
    $backup_module = $module;
    $module = $related_module;

    $link = $linkb;
    $outputrel = '';
    $contextmenu=0;
    switch ($module) {
        case 'dateien':
            $caption = __('Files');
            $news = __('New files');
            $table='dateien';
            break;
        case 'todo':
            $caption = __('Todo');
            $news = __('New todo');
            $table='todo';
            break;
        case 'notes':
            $caption = __('Notes');
            $news = __('New notes');
            $table='notes';
            break;
        case 'projects':
            $caption = __('Projects');
            $news = __('New project');
            $table='projekte';
            break;
        case 'mail':
            $caption = __('Mail');
            $news = __('New Mail');
            $table='mail_client';
            break;
        case 'helpdesk':
            $caption = __('Helpdesk');
            $news = __('New Helpdesk');
            $table='rts';
            break;
        case 'calendar':
            $caption = __('Calendar');
            $news = __('New Event');
            $table='termine';
            break;
        case 'costs':
            $caption = __('Costs');
            $news = __('New cost');
            $table='costs';
            break;
        default:
            // to be completed .....
            $caption = $module.': $caption not defined in '.__FILE__.' in line '.__LINE__;
            $news = $module.': $news not defined in '.__FILE__.' in line '.__LINE__;
            break;
    }

    $module_alias = $module;
    if ($module == 'dateien') $module_alias = 'filemanager';

    if (isset($nrel_get[$module])) {
        $perpage = $nrel_get[$module];
        $nrel_sess[$module] = $perpage;
        $_SESSION['nrel_sess'] =& $nrel_sess;
    }
    elseif (isset($related_objects) && 0 < (int)$related_objects) {
        $perpage = $related_objects;
    }
    elseif (isset($nrel_sess[$module])) {
        $perpage = $nrel_sess[$module];
    }
    else {
        $perpage = 5;
    }
    
    if (isset($query) && !empty($query)) {
        $query = ' and '.$query;
    }
    $nrel_sess = show_nrel("$link.php?mode=$mode&ID=$ID", $module);
    $fields = build_array($module, null, 'view');

    if ($filter_module == $module) {
        $where = main_filter($filter, $rule, $keyword, $filter_ID, $module, '',$operator);
    } else {
        if ($module == 'calendar') {
            // if there isn't any filter defined, you get future events.
            if (!$flist[$module]) {
                $filter  = 'datum';
                $rule    = '>=';
                list($year,$month,$day) = split("-",date("Y-m-d"));
                $keyword = sprintf("%04d-%02d-%02d", $year, $month, $day);
                $f_sort['calendar']['sort']      = 'datum,anfang';
                $f_sort['calendar']['direction'] = 'ASC';
                $where = main_filter($filter, $rule, $keyword, $filter_ID, $module, '',$operator);
            } else {
                $where = main_filter('', '', '', '', $module, '',$operator);
            }
        } else {
            $where = main_filter('', '', '', '', $module, '',$operator);
        }
    }

    $has_to_be_sorted = ($sort_module == $module);
    if($module=='helpdesk') {
        $nwhere = " WHERE (acc_read LIKE 'system' OR ((von = ".(int)$user_ID." OR acc_read LIKE 'group' OR acc_read LIKE '%\"$user_kurz\"%') AND $sql_user_group)) ";
    }
    else if ($module != 'calendar') {
        $nwhere = " WHERE (acc LIKE 'system' OR ((von = ".(int)$user_ID." OR acc LIKE 'group' OR acc LIKE '%\"$user_kurz\"%') AND $sql_user_group)) ";
    } else {
        $nwhere = " WHERE (von = ".(int)$user_ID." OR an = ".(int)$user_ID.") ";
    }

    $nwhere .= "
                    $query
                    $where
                    ".sql_filter_flags($module, array('archive', 'read'), false);

    $res=db_query("SELECT COUNT(*) FROM ".qss(DB_PREFIX.$table).
                    sql_filter_flags($module, array('archive', 'read')) .
                    $nwhere) or db_die();
    $rowcount= db_fetch_row($res);
    $relcount= $rowcount[0];

    $fieldlist = array();

    $outputrel = '
        <input title="'.__('This button opens a popup window').'" name="tcstart" value="'.$news.'" class="button" onclick ="manage_related_object(\''.PATH_PRE.'\',\''.$module_alias.'\',\''.$projekt_ID.'\',\''.$contact_ID.'\',\''.$sid.'\');" type="button" />

    ';

    $outputrel .= get_filter_edit_bar(true,$link);
    $outputrel .= get_status_bar();

    //reset module!
    $element_module= $module;
    $module = $backup_module;
    
    if( $element_module == 'helpdesk'){
        $outputrel .= build_table(array('ID', 'von', 'acc_read', 'parent'), $element_module, $nwhere, 0, $perpage, $link, 700, true);
    } else if ($element_module != 'calendar') {
        $outputrel .= build_table(array('ID', 'von', 'acc', 'parent'), $element_module, $nwhere, 0, $perpage, $link, 700, true);
    } else {
        $outputrel .= build_table(array('ID', 'von', 'event'), $element_module, $nwhere, 0, $perpage, $link, 700, true);
    }

    // everything's over - undo the module-change now in order to keep
    // the environment unchanged

    return $outputrel;
}

/**
 * Show notes related to a record
 *
 * @param string 	$where   	- Query string
 * @param string 	$referer 	- Referer
 * @return string				HTML output
 */
function show_related_notes($where, $referer) {
    global $module;
    $out = show_related('notes', $module, $where, $referer);
    return $out;
}

/**
 * Show files related to a record
 *
 * @param string 	$where   	- Query string
 * @param string 	$referer 	- Referer
 * @return string				HTML output
 */
function show_related_files($where, $referer) {
    global $module;
    $out = show_related('dateien', $module, $where, $referer);
    return $out;
}

/**
 * Show todos related to a record
 *
 * @param string 	$where   	- Query string
 * @param string 	$referer 	- Referer
 * @return string				HTML output
 */
function show_related_todo($where, $referer) {
    global $module;
    $out = show_related('todo', $module, $where, $referer);
    return $out;
}

/**
 * Show emails related to a record
 *
 * @param string 	$where   	- Query string
 * @param string 	$referer 	- Referer
 * @return string				HTML output
 */
function show_related_mail($where, $referer) {
    global $module;
    $out = show_related('mail', $module, $where, $referer);
    return $out;
}

/**
 * Show helpdesk related to a record
 *
 * @param string 	$where   	- Query string
 * @param string 	$referer 	- Referer
 * @return string				HTML output
 */
function show_related_helpdesk($query, $referer) {
    global $module;
    $out = show_related('helpdesk', $module, $query, $referer);
    return $out;
}

/**
 * Show events related to a record
 *
 * @param string 	$where   	- Query string
 * @param string 	$referer 	- Referer
 * @return string				HTML output
 */
function show_related_events($query, $referer) {
    global $module;
    $out = show_related('calendar', $module, $query, $referer);
    return $out;
}

/**
 * Show projects related to a record 8at the moment only contacts
 *
 * @param string 	$where   	- Query string
 * @param string 	$referer 	- Referer
 * @return string				HTML output
 */
function show_related_projects($query, $referer) {
    global $module;
    $out = show_related('projects', $module, $query, $referer);
    return $out;
}

/**
 * Show projects related to a record 8at the moment only contacts
 *
 * @param string 	$where   	- Query string
 * @param string 	$referer 	- Referer
 * @return string				HTML output
 */
function show_related_costs($query, $referer) {
    global $module;
    $out = show_related('costs', $module, $query, $referer);
    return $out;
}

/**
 *
 *
 * @param string	$referer		- Referer
 * @param string	$module		- Module name
 * @return array
 */
function show_nrel($referer, $module) {
    global $nrel_sess, $nrel_get, $sid;

    // set default
    if (!isset($nrel_get[$module]) and !isset($nrel_sess[$module])) {
        $nrel_sess[$module] = $nrel_get[$module] = '5';
    }
    // store into session
    if ($nrel_get[$module] <> '') {
        $nrel_sess[$module] = $nrel_get[$module];
        $_SESSION['nrel_sess'] =& $nrel_sess;
    }
    $values = array('0', '5', '20', '100');
    $out = '';
    foreach ($values as $value) {
        ($value == $nrel_sess[$module]) ? $style = "class='count_related'" : $style = '';
        $out .= "<li><a href=\"".$referer."&nrel_get[".$module."]=".$value.$sid."\" ".$style.'>'.$value."</a></li>";
    }
    $nrel_sess['out'] = $out;
    return $nrel_sess;
}

?>
