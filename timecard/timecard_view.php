<?php
/**
 * @package    timecard
 * @subpackage main
 * @author     Albrecht Guenther, $Author: nina $
 * @licence    GPL, see www.gnu.org/copyleft/gpl.html
 * @copyright  2000-2006 Mayflower GmbH www.mayflower.de
 * @version    $Id: timecard_view.php,v 1.72 2007-10-02 11:48:58 nina Exp $
 */

// check whether the lib has been included - authentication!
if (!defined("lib_included")) die("Please use index.php!");

// check role
if (check_role("timecard") < 1) die("You are not allowed to do this!");

// wird benötigt um Projekte auz/zu zuklappen
$tree2_mode = isset($tree2_mode) ? qss($tree2_mode) : '';
$element2_mode = isset($element2_mode) ? qss($element2_mode) : '';
$element2_ID = isset($element2_ID) ? (int) $element2_ID : null;
$mod = isset($mod) ? qss($mod) : '';
manage_treeview($tree2_mode,$element2_mode,$arrproj1,$element2_ID, $show_tree, $mod);

// Set favorite Flag for project 
if (isset($_REQUEST['set_favorite'])) {
	include_once('../projects/projects.inc.php');
    set_favorite($ID_s, $user_ID, $_REQUEST['set_favorite'] == '0' ? 0 : 1);
}
error_get_last(__LINE__);
//tabs
$tabs = array();
$tmp = get_export_link_data('timeproj', false);
$tabs[] = array('href' => $tmp['href'], 'active' => $tmp['active'], 'id' => 'export1', 'target' => '_self', 'text' => $tmp['text'].' '.__('Show bookings'), 'position' => 'right');

$buttons1 = array();

$burl = 'timecard.php?submode=%s&amp;year='.$year.'&amp;date='.$date.'&amp;month='.$month;
$buttons[] = array('type' => 'link','href' => sprintf($burl, 'days'), 'active' => ($submode=="days"), 'id' => 'tab1', 'target' => '_self', 'text' => __('Working times'), 'position' => 'left');
//only show projectbookings if project module is activated!
if (PHPR_PROJECTS) {
	$buttons[] = array('type' => 'link','href' => sprintf($burl, 'proj'), 'active' => ($submode=="proj" ), 'id' => 'tab2', 'target' => '_self', 'text' => __('Project bookings'), 'position' => 'left');
    $buttons[] = array('type' => 'link','href' => sprintf($burl, 'favorites'), 'active' => ($submode=="favorites" ), 'id' => 'tab3', 'target' => '_self', 'text' => __('Favorites'), 'position' => 'left');
}

// button bar
$buttons1[] = array('type' => 'text', 'text' => '<b>'.__('stop watches').__(':').' </b>');

// stop watch working time
$result = db_query("select ID
                    from ".DB_PREFIX."timecard
                   where datum = '$date' and
                         (ende = 0 or ende is NULL) and
                         users = ".(int)$user_ID) or db_die();
$row = db_fetch_row($result);

// buttons for 'come' and 'leave', alternate display
if ($row[0] > 0) {
	$buttons1[] = array('type' => 'link', 'href' => 'timecard.php?mode=data&amp;action=worktime_stop&amp;sure=1&amp;csrftoken='.make_csrftoken(), 'text' => __('Working times stop'), 'stopwatch' => 'started');
}
else{
	$buttons1[] = array('type' => 'link', 'href' => 'timecard.php?mode=data&amp;action=worktime_start&amp;csrftoken='.make_csrftoken(), 'text' => __('Working times start'), 'stopwatch' => 'stopped');
}
$tmp = get_export_link_data('timecard', false);
$tabs[] = array('href' => $tmp['href'], 'active' => $tmp['active'], 'id' => 'export', 'target' => '_self', 'text' => $tmp['text'].' '.__('Timecard'), 'position' => 'right');

//only show projectbookings if project module is activated!
if (PHPR_PROJECTS) {
	// stop watch project time
	$resultq = db_query("select ID, div1, h, m
                        from ".DB_PREFIX."timeproj
                       where users = ".(int)$user_ID." and
                             (div1 like '".date("Ym")."%')") or db_die();
	$rowq = db_fetch_row($resultq);
	// buttons for 'come' and 'leave', alternate display
	if ($rowq[0] > 0) {
		$buttons1[] = array('type' => 'link', 'href' => 'timecard.php?mode=data&amp;submode='.$submode.'&amp;action=clock_out&amp;csrftoken='.make_csrftoken(), 'text' => __('Project booking stop'), 'stopwatch' => 'started');
	}  else {
		$buttons1[] = array('type' => 'link', 'href' => 'timecard.php?mode=books&amp;submode='.$submode.'&amp;action=clockin'.$sid.'&amp;csrftoken='.make_csrftoken(), 'text' => str_replace('-', '', __('Project booking start')), 'stopwatch' => 'stopped');
	}
}
$output .= breadcrumb($module);
$output .= '</div>';
$output .= $content_div;
$output .= get_module_tabs($tabs,$buttons,  'oncontextmenu="startMenu(\''.$menu3->menusysID.'\',\'\',this)"');
$output .= get_module_tabs(array(),$buttons1);
$output .= get_status_bar();
$output .='<a name="content"></a>';


if ($submode=='days') {
    include_once('timecard_forms.php');
}
else {
    include_once('timecard_books.php');
}

$output .= '</div>';
echo $output;


function manage_treeview($tree2_mode,$element2_mode,$arrproj1,$ID, $show_tree, $mod){
    if($tree2_mode=="close"){
        $arrproj1= $empt_arr;
    }

    else if($tree2_mode=="open") {
        
        $order = sort_string('projects');
        
        $result2t = db_query("SELECT ID
                                FROM ".DB_PREFIX."projekte
                               WHERE is_deleted is NULL
                                     $order") or db_die();
        while ($row2t = db_fetch_row($result2t)) {
            $arrproj1[$row2t[0]] ="1";        
            $arrproj1['todo'][$row2t[0]] ="1";
            $arrproj1['helpdesk'][$row2t[0]] ="1";
        }
    }
    $tree2_mode="";
    if ($element2_mode == "open") {
        $arrproj1[$ID] = "1";
    }
    elseif ($element2_mode == "close"){
        $arrproj1[$ID] = "";
    }
    elseif ($element2_mode == "treeopen") {
		$show_tree[$ID] = "1";
	}
	elseif ($element2_mode == "treeclose"){
		$show_tree[$ID] = "";
	}
	elseif ($element2_mode == "modopen"){
		$arrproj1[$mod][$ID] = "1";
	}
	elseif ($element2_mode == "modclose"){
		$arrproj1[$mod][$ID] = "";
	}
	
	$_SESSION['arrproj1'] =$arrproj1;
	$_SESSION['show_tree'] =$show_tree;

}

?>
