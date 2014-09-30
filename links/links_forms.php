<?php
/**
 * @package    links
 * @subpackage main
 * @author     Albrecht Guenther, $Author: gustavo $
 * @licence    GPL, see www.gnu.org/copyleft/gpl.html
 * @copyright  2000-2006 Mayflower GmbH www.mayflower.de
 * @version    $Id: links_forms.php,v 1.33 2007-05-31 08:12:07 gustavo Exp $
 */

// check whether the lib has been included - authentication!
if (!defined("lib_included")) die("Please use index.php!");

// check role ... check deactivated since we do not see any security problem
// if (check_role("links") < 1) die("You are not allowed to do this!");

include_once(LIB_PATH."/permission.inc.php");

// special treatment of the fieldname of ID. since this db-table will be used often, it's name has been chenged into t_ID (like the other fields)

// tabs
$tabs    = array();
$buttons = array();
$hidden  = array('ID' => $ID, 'mode' => 'data');

if (SID) $hidden[session_name()] = session_id();
global $date_format_object;
$date_format_text = __('Date format').' = '.$date_format_object->get_user_format();
$buttons[] = array( 'type' => 'form_start',
                    'hidden' => $hidden,
                    'enctype' => 'multipart/form-data',
                    'name' => 'frm',
                    'onsubmit' => 'return checkUserDateFormat(\'t_reminder_datum\',\''.__('Resubmission').':\n'.$date_format_text.'\');');
$output = get_buttons($buttons);
$output .= $content_div;
// button bar
$buttons = array();

// copy
if ($cop_b <> '') {
    $buttons[] = array('type' => 'submit', 'name' => 'create_b', 'value' => __('Copy'), 'active' => false);
    $buttons[] = array('type' => 'submit', 'name' => 'cancel_b', 'value' => __('back'), 'active' => false);
}
// modify/import or delete/undo
else if ($ID > 0) {
    $buttons[] = array('type' => 'submit', 'name' => 'modify_b', 'value' => __('OK'), 'active' => false);
    $buttons[] = array('type' => 'submit', 'name' => 'modify_update_b', 'value' => __('Apply'), 'active' => false);
    $buttons[] = array('type' => 'submit', 'name' => 'cancel_b', 'value' => __('List View'), 'active' => false);
    $buttons[] = array('type' => 'submit', 'name' => 'delete_b', 'value' => __('Delete'), 'active' => false);
}
else if (!$ID) {
    $buttons[] = array('type' => 'submit', 'name' => 'create_b', 'value' => __('Create'), 'active' => false);
    $buttons[] = array('type' => 'submit', 'name' => 'cancel_b', 'value' => __('back'), 'active' => false);
}
else {
    $buttons[] = array('type' => 'submit', 'name' => 'cancel_b', 'value' => __('back'), 'active' => false);
}
$output .= get_buttons_area($buttons);



// fetch data from record
if ($ID > 0) {
    // fetch values from db and
    $result = db_query("SELECT t_ID, t_author
                          FROM ".DB_PREFIX."db_records
                         WHERE t_ID = ".(int)$ID." AND
                               t_author = ".(int)$user_ID) or db_die();
    $row = db_fetch_row($result);
    if (!$row[0]) die("You are not privileged to do this!");
}

/*******************************
*       basic fields
*******************************/
$basic_fields =build_form($fields);

$out_array = $basic_fields;

$output.= generate_output($out_array);

$out_array=array();

$output .= '<div class="hline"></div>';
$output .= get_buttons_area($buttons);
$output .= '<div class="hline"></div>';

$output .= '</form>';

echo $output;

?>
