<?php

// links_forms.php - PHProjekt Version 5.2
// copyright  ©  2000-2005 Albrecht Guenther  ag@phprojekt.com
// www.phprojekt.com
// Author: Albrecht Guenther, $Author: nina $
// $Id: links_forms.php,v 1.31.2.1 2007/03/12 15:57:57 nina Exp $

// check whether the lib has been included - authentication!
if (!defined("lib_included")) die("Please use index.php!");

// check role ... check deactivated since we do not see any security problem
// if (check_role("links") < 1) die("You are not allowed to do this!");

include_once(LIB_PATH."/permission.inc.php");

// special treatment of the fieldname of ID. since this db-table will be used often, it's name has been chenged into t_ID (like the other fields)

// tabs
$tabs    = array();
$buttons = array();
$hidden  = array();

if (SID) $hidden[session_name()] = session_id();
global $date_format_object;
$date_format_text = __('Date format').' = '.$date_format_object->get_user_format();
$buttons[] = array( 'type' => 'form_start',
                    'hidden' => $hidden,
                    'enctype' => 'multipart/form-data',
                    'name' => 'frm',
                    'onsubmit' => 'return checkUserDateFormat(\'t_reminder_datum\',\''.__('Resubmission').':\n'.$date_format_text.'\');');
$output = '<div id="global-header">';
$output .= get_tabs_area($tabs);
$output .= breadcrumb($module, array(array('title'=> ($ID>0) ? __('Modify'):__('New'))));
$output .= '</div>';
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
$form_fields = array();
$form_fields[] = array('type' => 'hidden', 'name' => 'ID', 'value' => $ID);
$form_fields[] = array('type' => 'hidden', 'name' => 'mode', 'value' => 'data');
$form_fields[] = array('type' => 'parsed_html', 'html' => build_form($fields));
$basic_fields = get_form_content($form_fields);


$output .= '
    <br />
    <a name="content"></a>
    <a name="oben" id="oben"></a>
    <fieldset>
    <legend>'.__('Basis data').'</legend>
    '.$basic_fields.'
    </fieldset>
</form>
';

echo $output;

?>
