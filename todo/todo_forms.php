<?php
/**
 * @package    todo
 * @subpackage main
 * @author     Albrecht Guenther, $Author: gustavo $
 * @licence    GPL, see www.gnu.org/copyleft/gpl.html
 * @copyright  2000-2006 Mayflower GmbH www.mayflower.de
 * @version    $Id: todo_forms.php,v 1.86 2008-01-10 03:10:55 gustavo Exp $
 */

// check whether the lib has been included - authentication!
if (!defined("lib_included")) die("Please use index.php!");

// check role
if (check_role("todo") < 1) die("You are not allowed to do this!");

if (eregi("xxx", $projekt_ID)) $projekt_ID = substr($projekt_ID, 11);
if (eregi("xxx", $contact_ID)) $contact_ID = substr($contact_ID, 11);

if ($justform == 2) $onload = array( 'window.opener.location.reload();', 'window.close();' );
else if ($justform > 0) $justform++;

include_once(LIB_PATH."/access_form.inc.php");
include_once(LIB_PATH."/permission.inc.php");

// fetch data from record
if ($ID > 0) {
    // mark that the user has touched the record
    touch_record('todo', $ID);

    // fetch values from db
    $result = db_query("SELECT ID, von, acc_write, status, ext, progress, acc, gruppe
                          FROM ".DB_PREFIX."todo
                         WHERE ID = ".(int)$ID." 
                           AND is_deleted is NULL
                           AND (acc LIKE 'system' OR von = ".(int)$user_ID." OR ext = ".(int)$user_ID." 
                                OR ((acc LIKE 'group' OR acc LIKE '%\"$user_kurz\"%')
                                    ".group_string()."))") or db_die();
    $row = db_fetch_row($result);
    if (!$row[0]) die("You are not privileged to do this!");
    if (($row[1] <> $user_ID and $row[2] <> 'w' and $row[4] <> $user_ID) or check_role("todo") < 2) $read_o = 1;
    else $read_o = 0;
    if ($row[1] <> $user_ID and PHPR_ALTER_ACC!=1) $read_acc = 1;
    else $read_acc = 0;
    change_group($row[7]);
}

//unset ID when copying project
$ID=prepare_ID_for_copy($ID,$copyform);
if ($ID) $head = slookup('todo', 'remark', 'ID', $ID,'1');
else     $head = __('New todo');

// tabs
$tabs   = array();
if ($justform == 2) $justform = 1;

$hidden = array('justform' => $justform, 'ID' => $ID, 'mode' => 'data');

if (SID) $hidden[session_name()]= session_id();

foreach ($view_param as $key=>$value) {

    $hidden[$key] = $value;

}

$buttons = array();
// form start
if (SID) $hidden[session_name()] = session_id();

global $date_format_object;
$date_format_text = __('Date format').' = '.$date_format_object->get_user_format();
$buttons[] = array( 'type' => 'form_start',
'hidden' => $hidden,
'enctype'=>"multipart/form-data",
'name' => 'frm',
'onsubmit' => "return chkForm('frm','remark','".__('Please insert a title')."') &amp;&amp; ".
"checkUserDateFormat('anfang','".__('Begin').':\n'.$date_format_text."') &amp;&amp; ".
"checkUserDateFormat('deadline','".__('Deadline').':\n'.$date_format_text."');");





$output.= breadcrumb($module, array(array('title'=> $head)));
$output.= '</div>';
$output.= get_buttons($buttons);

// button bar
$output .= $content_div;
$buttons = get_default_buttons($read_o,$ID,$justform,'todo',true,$sid);

if (!$read_o && $ID > 0 && $row[4] == 0) {
    $buttons[] = array('type' => 'link', 'href' => 'todo.php?mode=data&amp;undertake=1&amp;ID='.$row[0].$sid.'&amp;csrftoken='.make_csrftoken(), 'text' => __('Undertake'), 'active' => false);
}

// end buttons chief only
$output .= get_buttons_area($buttons);



$out_array=array();



/*******************************
*       basic fields
*******************************/
$form_fields   = array();



$basic_fields = build_form($fields);



/*******************************
*   categorization fields
*******************************/
$form_fields = array();
if (!$row[0] or ($row[1] == $user_ID and $row[3] == 1)) {
    $options = array();
    foreach ($status_arr as $statusnr => $statusname) {
        // possible values: accepted and rejected
        if ($statusnr >= 1 and $statusnr <= 3) {
            if ($statusnr == 3 && !PHPR_TODO_OPTION_ACCEPTED) continue;
            if ($statusnr == 2) {
                $selected = true;
            }
            else {
                $selected = false;
            }
            $options[] = array('value' => $statusnr, 'text' => $statusname, 'selected' => $selected);
        }
    }
    $form_fields[] = array('type' => 'select', 'name' => 'status', 'label' => __('Status').__(':'), 'options' => $options, 'no_blank_option' => 'true');
}
else if ($ID > 0) {
    $options = array();
    // select box only if the user is the recipient and the status is still pending ...
    if ($row[4] == $user_ID and ($row[3] == 2 || $row[3] == 0)) {
        foreach ($status_arr as $statusnr => $statusname) {
            // possible values: accepted and rejected
            if ($statusnr >= 2 and $statusnr <= 4) {
                $options[] = array('value' => $statusnr, 'text' => $statusname, 'selected' => false);
            }
        }
        $form_fields[] = array('type' => 'select', 'name' => 'status', 'label' => __('Status').__(':'), 'options' => $options, 'no_blank_option' => 'true');
    }
    // next possible mode: if accepted, give him a checkbox to mark this todo as done
    else if ($row[3] == 3) {
        if ($row[4] == $user_ID ) $form_fields[] = array('type' => 'checkbox', 'readonly'=>false,'name' => 'todo_done', 'label' => $status_arr[$row[3]], 'label_right' => __('done'));
        else $form_fields[] = array('type' => 'checkbox', 'readonly'=>true,'name' => 'todo_done', 'label' => $status_arr[$row[3]], 'label_right' => __('done'));
    }
    // otherwise just print the current status
    else {
        $form_fields[] = array('type' => 'parsed_html', 'html' => $status_arr[$row[3]]);
    }
}

// Progress
if ($row[4] == $user_ID and ($row[3] > 1 and $row[3] < 5)) {
    $form_fields[] = array('type' => 'hidden', 'name' => 'mode', 'value' => 'data');
    $form_fields[] = array('type' => 'hidden', 'name' => 'cstatus', 'value' => $GLOBALS['cstatus']);
    $form_fields[] = array('type' => 'hidden', 'name' => 'category', 'value' => $GLOBALS['category']);

    $form_fields[] = array('type' => 'text', 'name' => 'progress', 'label' => __('progress').__(':'), 'value' => $row[5], 'label_right' => ' %');
}
else {
    $form_fields[] = array('type' => 'parsed_html', 'html' => ' '.$row[5].'% &nbsp;');
}
if (($row[4] == $user_ID or $row[1]==$user_ID) and $row[3] == 5) {
	 $form_fields[] = array('type' => 'checkbox', 'readonly'=>false,'name' => 'todo_reopen', 'label' =>'', 'label_right' => __('reopen'));
}
$categorization_fields = get_form_content($form_fields);

/*******************************
*      assignment fields
*******************************/
$form_fields = array();

include_once(LIB_PATH."/access_form.inc.php");
// acc_read, exclude the user itself, acc_write, no parent possible, write access=yes
include_once(LIB_PATH."/access.inc.php");

// values of the access
if (!isset($persons)) {
    if (!isset($_POST[$persons])) $str_persons = $row[6];
    else $str_persons = xss($_POST[$persons]);
} else $str_persons = $acc = serialize($persons);

if (!isset($acc_write)) {
    if (!isset($_POST['acc_write'])) $acc_write = $row[2];
    else $acc_write = xss($_POST['acc_write']);
}

$form_fields[] = array('type' => 'parsed_html', 'html' => access_form($str_persons, 1, $acc_write, 0, 1,'acc',$read_acc));
$assignment_fields = get_form_content($form_fields);







// project-related times
include_once(LIB_PATH."/timeproj.inc.php");
$project_specific_times = "";
if ((!empty($fields['project']['value']) or !$ID) and PHPR_PROJECTS) {
    $project_specific_times = timeproj_get_list_box((int) $ID, 'todo');
}





$out_array = array_merge($out_array,$basic_fields);

if (!$ID){

    $out_array[]=array(__('Notification'),get_notify_fields());

}

$out_array[]=array(__('Categorization'),'<br/>'.$categorization_fields);

$out_array[]=array(__('project related times'),$project_specific_times);

$out_array[]=array(__('Release'),$assignment_fields);

if ($ID > 0 and PHPR_HISTORY_LOG == 2) $out_array[]=array(__('History'),history_show('todo', $ID));



// close the big form



$output.= generate_output($out_array);



$out_array=array();



$output .= '<div class="hline"></div>';

$output .= get_buttons_area($buttons);

$output .= '<div class="hline"></div>';



$output .= '</form>';

echo $output;

?>
