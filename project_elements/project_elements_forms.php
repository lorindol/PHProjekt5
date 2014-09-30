<?php
/**
 * project_elements forms script
 *
 * @package    projects
 * @subpackage project_elements
 * @author     Nina Schmitt
 * @licence    GPL, see www.gnu.org/copyleft/gpl.html
 * @copyright  2000-2006 Mayflower GmbH www.mayflower.de
 * @version    
 */

// check whether the lib has been included - authentication!
if (!defined("lib_included")) die("Please use index.php!");

include_once(LIB_PATH.'/access_form.inc.php');
if ($justform == 2) {
    $onload[] = 'window.opener.location.reload();';
    $onload[] = 'window.close();';
}
else if ($justform > 0) {
    $justform++;
}


// fetch data from record
if ($ID > 0) {
    // mark that the user has touched the record
    touch_record('notes', $ID);

    // fetch values from db
    $query = "SELECT ID, von, name, project_ID
                FROM ".DB_PREFIX."project_elements
               WHERE ID = ".(int)$ID;
    $result = db_query($query) or db_die();
    $row = db_fetch_row($result);
    if (!$row[0]) die("You are not privileged to do this!");
    if (($row[1] <> $user_ID)) $read_o = 1;
    else $read_o = 0;
    if ($row[1] <> $user_ID and PHPR_ALTER_ACC!=1) $read_acc = 1;
    else $read_acc = 0;
    $project_ID=$row[3]; 
}
else{
    $begin  = date("Y")."-".date("m")."-".date("d");
    $end    = date("Y")."-12-31";
}
//unset ID when copying project
$ID=prepare_ID_for_copy($ID,$copy);
if ($ID) $head = slookup('notes', 'name', 'ID', $ID, true);
else     $head = __('New phase');
if (!$head) $head = __('New phase');

// tabs
$tabs    = array();
if ($justform == 2) $justform = 1;
$hidden = array('justform' => $justform, 'ID' => $ID, 'mode' => 'data','project_ID'=>$project_ID);
if (SID) $hidden[session_name()]= session_id();
foreach ($view_param as $key=>$value) {
    $hidden[$key] = $value;
}
$buttons = array();
if (SID) $hidden[session_name()] = session_id();

// form start
$date_format_text = __('Date format').' = '.$date_format_object->get_user_format();
$buttons[] = array('type' => 'form_start', 'enctype' => 'multipart/form-data', 'name' => 'frm', 'hidden' => $hidden, 'onsubmit' => 'return chkForm(\'frm\',\'name\',\''.__('Please insert a name').'\') &amp;&amp; '.
"checkUserDateFormat('begin','".__('Begin').':\n'.$date_format_text."') &amp;&amp; ".
"checkUserDateFormat('end','".__('End').':\n'.$date_format_text."');");
$output .= breadcrumb($module, array(array('title' => htmlentities($head))));
$output .= '</div>';
$output .= get_buttons($buttons);

/**
 * buttons**
 */
if (!$ID) {
    // create new note
    $buttons[] = array('type' => 'submit', 'name' => 'create_b', 'value' => __('OK'), 'active' => false);
    $buttons[] = array('type' => 'submit', 'name' => 'create_update_b', 'value' => __('Apply'), 'active' => false);
    // hidden
    $buttons[] = array('type' => 'hidden', 'name' => 'anlegen', 'value' => 'neu_anlegen');

    // cancel
    if ($justform > 0) {
        $buttons[] = array('type' => 'button', 'name' => 'close', 'value' => __('Close window'), 'active' => false, 'onclick' => 'window.close();');
    }
    else {
        $buttons[] = array('type' => 'link', 'href' => 'notes.php?type='.$type.'&amp;mode=view', 'text' => __('List View'), 'active' => false);
    }
} // modify and delete

else {
    // modify note
    $buttons[] = array('type' => 'submit', 'name' => 'modify_b', 'value' => __('OK'), 'active' => false);
    $buttons[] = array('type' => 'submit', 'name' => 'modify_update_b', 'value' => __('Apply'), 'active' => false);
    $buttons[]  = array('type' => 'submit', 'name' => 'copy', 'value' => __('copy'), 'active' => false);
    $buttons[] = array('type' => 'submit', 'name' => 'delete_b', 'value' => __('Delete'), 'active' => false, 'onclick' => 'return confirm(\''.__('Are you sure?').'\');');

    // cancel
    if ($justform > 0) {
        $buttons[] = array('type' => 'button', 'name' => 'close', 'value' => __('Close window'), 'active' => false, 'onclick' => 'window.close();');
    }
    else {
        $buttons[] = array('type' => 'link', 'href' => 'notes.php?type='.$type.'&amp;mode=view', 'text' => __('List View'), 'active' => false);
    }
}
$output.= $content_div;
$output.= get_buttons_area($buttons);

$out_array=array();

/*******************************
*       basic fields
*******************************/

$basic_fields  = build_form($fields);
$out_array = array_merge($out_array,$basic_fields);
$output .= generate_output($out_array);
$output .= get_buttons_area($buttons);
$output .= '</form></div>';

echo $output;



?>
