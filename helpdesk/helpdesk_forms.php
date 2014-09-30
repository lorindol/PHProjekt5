<?php
/**
 * helpdesk forms script
 *
 * @package    helpdesk
 * @subpackage main
 * @author     Albrecht Guenther, Nina Schmitt, $Author: gustavo $
 * @licence    GPL, see www.gnu.org/copyleft/gpl.html
 * @copyright  2000-2006 Mayflower GmbH www.mayflower.de
 * @version    $Id: helpdesk_forms.php,v 1.83 2008-02-04 13:55:57 gustavo Exp $
 */

if (!defined('lib_included')) die('Please use index.php!');

include_once(LIB_PATH.'/access_form.inc.php');
// fetch permission routine
include_once(LIB_PATH.'/permission.inc.php');

// check role
if (check_role("helpdesk") < 1) die("You are not allowed to do this!");

if ($justform == 2) $onload = array('window.opener.location.reload();', 'window.close();');
else if ($justform > 0) $justform++;


// fetch data from record
if ($ID > 0) {
    // mark that the user has touched the record
    touch_record('rts', $ID);
    // fetch values from db
    $result = db_query("SELECT ID,note,assigned, proj, acc_write, von, visibility, acc_read,status, lock_user, gruppe
                          FROM ".DB_PREFIX."rts
                         WHERE (acc_read LIKE 'system'
                                OR ((von = ".(int)$user_ID." 
                                     OR assigned = '$user_ID'
                                     OR acc_read LIKE 'group'
                                     OR acc_read LIKE '%\"$user_kurz\"%')".group_string()."))
                            AND ID = ".(int)$ID."
                            AND is_deleted is NULL") or db_die();
    $row = db_fetch_row($result);
    if (!$row[0]) die("You are not privileged to do this!");
    if (($row[2] <> $user_ID and $row[5] <> $user_ID and $row[4] <> 'w') or !check_locked('rts','lock_user',$ID) or check_role("helpdesk") < 2 ) {
        $read_o = 1;
        $read_o_discussion = 1;
    }
    else if(RTS_WORKFLOW == 1 AND $row[2] <> $user_ID and $row[5] <> $user_ID ){
        $read_o =1;
        $read_o_discussion=0;
    }
    else{
        $read_o = 0;
        $read_o_discussion = 0;
    }
    //usertype cleint can never change bassi data
    if($user_type==4)$read_o=1;
    if ($row[5] <> $user_ID and PHPR_ALTER_ACC!=1 or (RTS_WORKFLOW == 1 AND $row[5] <> $user_ID AND $row[2] <> $user_ID)) $read_acc = 1;
    else $read_acc = 0;
    change_group($row[10]);
}
//unset ID when copying project
$ID=prepare_ID_for_copy($ID,$copyform);
// tabs
if ($justform == 2) $justform = 1;
$tabs    = array();
// this can be exported in own method later on

// form start
$hidden  = array();
$buttons = array();
$hidden  = array('justform' => $justform, 'mode' => 'data', 'ID' =>$ID, 'read_o'=>$read_o, 'read_o_discussion'=>$read_o_discussion);
if (SID) $hidden[session_name()] = session_id();
global $date_format_object;
$date_format_text = __('Date format').' = '.$date_format_object->get_user_format();
$title = $ID > 0 ? htmlentities($row[1]) : __('New');
$out_basic = breadcrumb($module, array(array('title'=>$title)));


if ($justform > 0) {
    $out_basic .= '<div id="global-content" class="popup">';
} else {
    $out_basic .= '<div id="global-content">';
}
unset($title);

// project-related times
include_once(LIB_PATH."/timeproj.inc.php");
$project_specific_times = "";
if ((!empty($fields['proj']['value'])or !$ID) and  PHPR_PROJECTS) {
    $project_specific_times = timeproj_get_list_box($ID, 'helpdesk', $user_ID);
}



$buttons=array();
$buttons[] = array( 'type' => 'form_start',
'name' => 'frm',
'enctype'=>'multipart/form-data',
'hidden' => $hidden,
'onsubmit' => 'return chkForm(\'frm\',\'name\',\''.__('Please insert a name').'\') &amp;&amp;
                                          checkUserDateFormat(\'due_date\',\''.__('Due date').':\n'.$date_format_text.'\');' );

$out_basic .= get_buttons($buttons);
// button bar
// show_buttons if assigning is fine
$buttons = get_default_buttons($read_o,$ID,$justform,'helpdesk',true,$sid);
if($read_o and $read_o_discussion==0){
    if ($ID == 0) {
        // Create new item
        $button_name = 'create_b';
        $button_name2 = 'create_update_b';
    }
    else {
        // Item modification
        $button_name = 'modify_b';
        $button_name2 = 'modify_update_b';
    }


    if ($justform > 0) {
            $buttons[] = array('type' => 'button', 'name' => 'close', 'value' => __('Close window'), 'active' => false, 'onclick' => 'window.close();');
    }

    $buttons[] = array('type' => 'submit', 'name' => $button_name, 'value' => __('OK'), 'active' => false);
    // Add apply button
    $buttons[] = array('type' => 'submit', 'name' => $button_name2, 'value' => __('Apply'), 'active' => false);
}


// lock unlock ticket
if (!$read_o ) {
    if ($row[9]>0 ) {
$buttons[] = array('type' => 'link', 'href' => 'helpdesk.php?mode=data&amp;action=lockfile&amp;unlock=true&amp;lock=false&amp;ID='.$ID.'&amp;csrftoken='.make_csrftoken(), 'text' => __('Unlock ticket'), 'active' => false);
    }
    else if ($ID > 0) {
        $buttons[] = array('type' => 'link', 'href' => 'helpdesk.php?mode=data&amp;action=lockfile&amp;unlock=false&amp;lock=true&amp;ID='.$ID.'&amp;csrftoken='.make_csrftoken(), 'text' => __('Lock ticket'), 'active' => false);
    }
}

if (!$read_o_discussion and check_role("helpdesk") > 1 and $ID > 0) {
    $buttons[] = array('type' => 'link', 'href' => 'helpdesk.php?ID='.$ID.'&amp;mode=forms&amp;submode=discussion&amp;new_remark=true#new_remark', 'text' => __('Add Remark'), 'active' => false);
}
$out_basic .= get_buttons_area($buttons);

/*******************************
*       basic fields
*******************************/
//alter read_o in case ticket is assigned:
$basic_fields  =  build_form($fields);
/*******************************
*       status fields
*******************************/
$form_fields = array();
// set category
$found_mandatory = false;
$tmp = '<br /><label class="label_block" for="status">'.__('Status').'</label>';
//Special treatment in complex Workflow:
// selected status index
if(RTS_WORKFLOW == 1){
    $db_status_ix = -1;
    foreach ($status_arr as  $status_nr=>$status1){
        if ($status1[0] == $row[8]) {
            $db_status_ix = $status_nr;
            break;
        }
    }
    foreach ($status_arr as $status_nr=>$status1) {
        if ($status1[2] != 2) { // filter active workflow states
            $tmp .=  '<input type="radio" name="status" value="'.$status_nr.'"';
            // conditions to select them: current status has to be 'earlier' then this status and the person has to be entitled
            $allowed_users = array();
            foreach ($status1[1] as $db_col) {
                $allowed_users[] = $row[$db_col];
            }
            if (!$found_mandatory and in_array($user_ID, $allowed_users)) {  $tmp .= read_o(0); }
            else $tmp .= read_o(1);
            // selected?
            if ($status1[0] == $row[8]) $tmp .= ' checked="checked"';
            $tmp .= " /> ".$status1[3]."<br />\n";
            if ($status1[2] == 1 and $status_nr > $db_status_ix) {
                $found_mandatory = true;
            }
        }
    }
}
else{
    $tmp.='<select class="halfsize" id="status" name="status" title="set the status of this project" '.read_o($read_o).'>';
    foreach ($status_arr as $status_nr=>$status1) {
        if ($status1[2] != 2) { // filter active workflow states
            $tmp .=  '<option value="'.$status_nr.'"';
            if ($status1[0] == $row[8]) $tmp .= ' selected="selected"';
            $tmp .= "> ".$status1[3]."</option>\n";
        }
    }
    $tmp .= '</select><br /><br />';
}
$status_fields =$tmp;

/*******************************
*      access assignment fields
*******************************/
include_once("../lib/access_form.inc.php");

// values of the access
if (!isset($persons)) {
    if (!isset($_POST[$persons])) $str_persons = $row[7];
    else $str_persons = xss($_POST[$persons]);
} else $str_persons = $acc = serialize($persons);

if (!isset($acc_write)) {
    if (!isset($_POST['acc_write'])) $acc_write = $row[4];
    else $acc_write = xss($_POST['acc_write']);
}
$form_fields = array();
$form_fields[] = array('type' => 'parsed_html', 'html' => access_form($str_persons, 1, $acc_write, 0, 1,'acc',$read_acc));
$assignment_fields = get_form_content($form_fields);
$notify_box = '<br />'.get_notify_fields(false).'<br />';
$out_array=array();
$out_array = array_merge($out_array,$basic_fields);
$count_basic= count($out_array);


$out_array[]=array(__('Ticket status'),$status_fields);
$out_array[]=array(__('Release'),$assignment_fields);
$out_array[]=array(__('project related times'),$project_specific_times);
if ($ID > 0 and PHPR_HISTORY_LOG == 2){
    $out_array[]=array(__('History'),history_show('rts', $ID));

}
// close the big form
//array for output

/***********************************************************
 * new remarkfield only if "new remark" or reply was chosen
 ***********************************************************/
$new_remark_field='';
if(!$read_o_discussion and $new_remark=='true'){
$out_array[]=array(__('New posting'),set_new_remark($ID, $parent_remark,$row[1]));
}
else $out_array[]=array(__('Notification'),$notify_box);
$selected = 1;
if($new_remark==true) $selected = $count_basic+5;
/*******************************
*      discussion fields
*******************************/
$discussion_table=build_discussion_table("helpdesk",$ID,"helpdesk.php?ID=$ID&amp;mode=forms&amp;submode=discussion");

$out_array[]=array(__('Discussion'),nl2br($row[1]).$discussion_table);


if($submode=='discussion' and $selected==1) $selected = $count_basic+6;

$out_basic.= generate_output($out_array, 1,$selected);

$out_array=array();

$out_basic .= '<div class="hline"></div>';
$out_basic .= get_buttons_area($buttons);
$out_basic .= '<div class="hline"></div>';

echo $out_basic;

?>
