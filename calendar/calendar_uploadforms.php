<?php
/**
 * Show a upload field for import a ical calendar
 *
 * @package    calendar
 * @subpackage main
 * @author     Gustavo Solt, $Author: albrecht $
 * @licence    GPL, see www.gnu.org/copyleft/gpl.html
 * @copyright  2000-2006 Mayflower GmbH www.mayflower.de
 * @version    $Id: calendar_uploadforms.php,
 */

// check whether the lib has been included - authentication!
if (!defined("lib_included")) die("Please use index.php!");

// check role
if (check_role("calendar") < 1) die("You are not allowed to do this!");

include_once(LIB_PATH.'/access_form.inc.php');

$output .= '<div id="bars">';
$output .= breadcrumb($module, array(array('title'=>__('Import a ical calendar'))));
$output .= '</div>';

$html  = "
<br />
<form action='ical2phprojekt.php' method='post' enctype='multipart/form-data'>
".hidden_fields(array())."
<fieldset>
<legend>".__('Import a ical calendar').": </legend>
<label class='label_block' for='userfile'>".__('ICAL file')."</label>
<input type='file' name='userfile' title='".__('ICAL file')."' />
</fieldset>";
if (SID) $html .= "<input type='hidden' name='".session_name()."' value='".session_id()."' />\n";
$html .= "<br />".get_buttons(array(array('type' => 'submit', 'value' => __('OK'), 'active' => false)))
."&nbsp;</form>\n";

$output .= $html;
echo $output;
?>
