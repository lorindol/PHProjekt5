<?php
/**
* helpdesk forms script
*
* @package    helpdesk
* @module     main
* @author     Albrecht Guenther, Nina Schmitt, $Author: gustavo $
* @licence    GPL, see www.gnu.org/copyleft/gpl.html
* @copyright  2000-2006 Mayflower GmbH www.mayflower.de
* @version    $Id: helpdesk_forms.php,v 1.73.2.2 2007/10/04 14:09:23 gustavo Exp $
*/
if (!defined('lib_included')) die('Please use index.php!');

include_once(LIB_PATH.'/access_form.inc.php');
// fetch permission routine
include_once(LIB_PATH.'/permission.inc.php');

// check role
if (check_role("helpdesk") < 1) die("You are not allowed to do this!");


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
                           AND ID = ".(int)$ID) or db_die();
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
$ID=prepare_ID_for_copy($ID,$copy);
// tabs
$tabs    = array();
// this can be exported in own method later on

if($submode=="discussion"){
    $tabs[] = array('href' => 'helpdesk.php?mode=forms&amp;ID='.$ID.'&amp;submode=basic', 'active' => false, 'id' => 'tab1', 'target' => '_self', 'text' => __('Basis data'), 'position' => 'left');
    $tabs[] = array('href' => 'helpdesk.php?mode=forms&amp;ID='.$ID.'&amp;submode=discussion', 'active' =>true, 'id' => 'tab1', 'target' => '_self', 'text' => __('Discussion'), 'position' => 'left');
}
else{
    $tabs[] = array('href' => 'helpdesk.php?mode=forms&amp;ID='.$ID.'&amp;submode=basic', 'active' => true, 'id' => 'tab1', 'target' => '_self', 'text' => __('Basis data'), 'position' => 'left');
    $tabs[] = array('href' => 'helpdesk.php?mode=forms&amp;ID='.$ID.'&amp;submode=discussion', 'active' =>false, 'id' => 'tab1', 'target' => '_self', 'text' => __('Discussion'), 'position' => 'left');

}
// form start
$hidden  = array();
$buttons = array();
$hidden  = array('mode' => 'data', 'ID' =>$ID);
if (SID) $hidden[session_name()] = session_id();
global $date_format_object;
$date_format_text = __('Date format').' = '.$date_format_object->get_user_format();
$output = '<div id="global-header">';
$output .= get_tabs_area($tabs);
$title = $ID > 0 ? htmlentities($row[1]) : __('New');
$output .= breadcrumb($module, array(array('title'=>$title)));
$output .= '</div>';


if ($justform > 0) {
    $output .= '<div id="global-content" class="popup">';
} else {
    $output .= '<div id="global-content">';
}
unset($title);

// project-related times
include_once(LIB_PATH."/timeproj.inc.php");
$project_specific_times = "";
if ((!empty($fields['proj']['value'])or !$ID) and  PHPR_PROJECTS) {
    $project_specific_times = timeproj_get_list_box($ID, 'helpdesk', $user_ID);
}

switch($submode){
    case "discussion":

        /*******************************
        *      form start
        *******************************/
        $hidden['submode']='discussion';
        $hidden['proj']=$row[3];
        $buttons[] = array( 'type' => 'form_start',
        'name' => 'frm', 
        'enctype'=>'multipart/form-data',
        'hidden' => $hidden);
        $output .= get_buttons($buttons);

        /*******************************
        *      buttons bar
        *******************************/
        $discussion_buttons=$read_o_discussion;
        if(!isset($new_remark))$discussion_buttons=1;
        $buttons = get_main_buttons($discussion_buttons,$ID,$justform,'helpdesk',true,$sid);
        // new remark
        if (!$read_o_discussion and !isset($new_remark) and check_role("helpdesk") > 1 and $ID > 0) {
            $buttons[] = array('type' => 'link', 'href' => 'helpdesk.php?ID='.$ID.'&amp;mode=forms&amp;submode=discussion&amp;new_remark=true#new_remark', 'text' => __('Add Remark'), 'active' => false);
        }
        $output .= get_buttons_area($buttons);

        /*******************************
        *      discussion fields
        *******************************/
        $discussion_table=build_discussion_table("helpdesk",$ID,"helpdesk.php?ID=$ID&amp;mode=forms&amp;submode=discussion");

        // new remarkfield only if "new remark" or reply was chosen
        $new_remark_field='';
        if ($new_remark) {
            $new_remark_field = set_new_remark($ID, $parent_remark,$row[1]).$project_specific_times;
            $new_remark_field .= get_buttons_area($buttons);
        }
        $output .= '
                <br />
                <div class="inner_content">';
        $output .= $new_remark_field;
        $output .= '<br />
        <h1>'.__('Discussion').'</h1>
        <br>
        <fieldset>
            <legend>'.__('Problem Description').'</legend>
            '.$row[1].'
        </fieldset>
        <div class="boxContent">'.$discussion_table.'</div>
        <br style="clear:both" /><br />';
        break;
    default:
        $buttons[] = array( 'type' => 'form_start',
        'name' => 'frm',
        'enctype'=>'multipart/form-data',
        'hidden' => $hidden,
        'onsubmit' => 'return chkForm(\'frm\',\'name\',\''.__('Please insert a name').'\') &amp;&amp;
                                          checkUserDateFormat(\'due_date\',\''.__('Due date').':\n'.$date_format_text.'\');' );

        $output .= get_buttons($buttons);
        // button bar
        // show_buttons if assigning is fine
        $buttons = get_default_buttons($read_o,$ID,$justform,'helpdesk',true,$sid);
        // new subproject
        if (!$read_o_discussion and check_role("helpdesk") > 1 and $ID > 0) {
            $buttons[] = array('type' => 'link', 'href' => 'helpdesk.php?ID='.$ID.'&amp;mode=forms&amp;submode=discussion&amp;new_remark=true#new_remark', 'text' => __('Add Remark'), 'active' => false);
        }

        // lock unlock ticket
        if (!$read_o ) {
            if ($row[9]>0 ) {
                $buttons[] = array('type' => 'link', 'href' => 'helpdesk.php?mode=data&amp;action=lockfile&amp;unlock=true&amp;lock=false&amp;ID='.$ID.'&amp;csrftoken='.make_csrftoken(), 'text' => __('Unlock ticket'), 'active' => false);
            }
            else {
                $buttons[] = array('type' => 'link', 'href' => 'helpdesk.php?mode=data&amp;action=lockfile&amp;unlock=false&amp;lock=true&amp;ID='.$ID.'&amp;csrftoken='.make_csrftoken(), 'text' => __('Lock ticket'), 'active' => false);
            }
        }
        $output .= get_buttons_area($buttons);

        /*******************************
        *       basic fields
        *******************************/
        $form_fields = array();
        if (SID) $form_fields[] = array('type' => 'hidden', 'name' => session_name(), 'value' => session_id());
        foreach ($view_param as $key => $value){
            $form_fields[] = array('type' => 'hidden', 'name' => $key, 'value' => $value);
        }
        //alter read_o in case ticket is assigned:
        $form_fields[] = array('type' => 'parsed_html', 'html' => build_form($fields));
        $basic_fields  = get_form_content($form_fields);

        /*******************************
        *       status fields
        *******************************/
        $form_fields = array();
        // set category
        $found_mandatory = false;
        $tmp = '
        <label for="status" class="label_block">'.__('Status').':</label>
        <div>
        ';
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
            $tmp .= '</select>';
        }
        $form_fields[] = array('type' => 'parsed_html', 'html' => $tmp);
        $status_fields = get_form_content($form_fields);

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
        $form_fields[] = array('type' => 'parsed_html', 'html' => access_form2($str_persons, 1, $acc_write, 0, 1,'acc',$read_acc));
        $access_assignment_fields = get_form_content($form_fields);
        $notify_box .=get_notify_fields(true);

        $output .= '
        <br />
        <div class="inner_content">
        <a name="content"></a>
        <a name="oben" id="oben"></a>
        <fieldset>
        <legend>'.__('Basis data').'</legend>
        '.$basic_fields.'
        </fieldset>
    
        <fieldset>
        <legend>'.__('Ticket status').'</legend>
        '.$status_fields.'
        </fieldset>

        '.
        $access_assignment_fields.
        $notify_box
        .$project_specific_times;
        $output .= get_buttons_area($buttons);
        $output .= '</div>';
        if (PHPR_HISTORY_LOG == 2) $output .= history_show('rts', $ID);
}
$output .= '
        </div>
        </form>
        <br style="clear:both" /><br />';

echo $output;
?>
