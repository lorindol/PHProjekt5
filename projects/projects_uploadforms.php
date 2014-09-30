<?php
/**
 * Show a upload field for the gantt bridge
 *
 * @package    projects
 * @subpackage pbridge
 * @author     Albrecht Guenther, $Author: albrecht $
 * @licence    GPL, see www.gnu.org/copyleft/gpl.html
 * @copyright  2000-2006 Mayflower GmbH www.mayflower.de
 * @version    $Id: projects_uploadforms.php,v 1.4 2008-03-04 10:52:00 albrecht Exp $
 */

// check whether the lib has been included - authentication!
if (!defined("lib_included")) die("Please use index.php!");

// check role
if (check_role("projects") < 1) die("You are not allowed to do this!");

include_once(LIB_PATH.'/access_form.inc.php');

$output .= breadcrumb($module, array(array('title'=>__('Import a Gantt File'))));
$output .= '</div>';
$output .= $content_div;

// button bar
$buttons = array();
$buttons[] = array('type' => 'link', 'href' => 'projects.php?mode=forms&amp;action=new'.$sid, 'text' => __('New'), 'active' => false);
$buttons[] = array('type' => 'link', 'href' => 'projects.php?mode=options'.$sid, 'text' => __('Options'), 'active' => true);
$buttons[] = array('type' => 'link', 'href' => 'projects.php?mode=stat'.$sid, 'text' => __('Statistics'), 'active' => false);
$buttons[] = array('type' => 'link', 'href' => 'projects.php?mode=stat&amp;mode2=mystat'.$sid, 'text' => __('My Statistic'), 'active' => false);
$buttons[] = array('type' => 'link', 'href' => 'projects.php?mode=gantt'.$sid, 'text' => __('Gantt'), 'active' => false);
$buttons[] = array('type' => 'link', 'href' => 'projects.php?type='.$type.'&amp;mode=view', 'text' => __('List View'), 'active' => false);
$output .= get_buttons_area($buttons);

// Keep ID
$hidden_fields = array ( "project_ID"     => $project_ID);
                             
$html  = "
<br />
<form action='gp2phprojekt.php' method='post' enctype='multipart/form-data'>
".hidden_fields($hidden_fields)."
<fieldset>
<legend>".__('Import a Gantt File').": </legend>
<label class='label_block' for='userfile'>".__('Gantt file')."</label>
<input type='file' name='userfile' title='".__('Gantt file')."' />
</fieldset>";
if (SID) $html .= "<input type='hidden' name='".session_name()."' value='".session_id()."' />\n";
$html .= "<br />".get_buttons(array(array('type' => 'submit', 'value' => __('OK'), 'active' => false)))
."&nbsp;</form>\n";

$output .= $html;
echo $output;
?>
