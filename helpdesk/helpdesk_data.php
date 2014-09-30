<?php
/**
* helpdesk db data script
*
* @package    helpdesk
* @module     main
* @author     Albrecht Guenther, Nina Schmitt, $Author: thorsten $
* @licence    GPL, see www.gnu.org/copyleft/gpl.html
* @copyright  2000-2006 Mayflower GmbH www.mayflower.de
* @version    $Id: helpdesk_data.php,v 1.80.2.2 2007/02/26 15:06:40 thorsten Exp $
*/
if (!defined('lib_included')) die('Please use index.php!');
// check role
if (check_role("helpdesk") < 2) die("You are not allowed to do this!");

// check form token
check_csrftoken();

use_mail('1');
// fetch permission routine
include_once(LIB_PATH."/permission.inc.php");
include_once(LIB_PATH."/notification.inc.php");
include_once(LIB_PATH."/timeproj.inc.php");

// delete request
if ($delete_b) {
    if      ($ID > 0)     manage_delete_records($ID, $module);
    else if ($ID_s <> '') manage_delete_records($ID_s, $module);
}
else if ($copy){
    $fields = change_fields_for_copy($fields,'name');
}
else if(isset($new_remark)){
    $remark_ID =add_remark($new_remark,$parent_remark,$ID,'helpdesk');
    //notify recipient
    if($notify_recipient=="on"){
        $recipients = get_recipients($ID);
        $ticket_title = get_title($ID);
        remark_notification($new_remark,$remark_ID,$ID,'helpdesk',$recipients, $ticket_title);
    }
    $query_str = "helpdesk.php?mode=forms&submode=discussion&ID=".$ID."#discussion";
    header('Location: '.$query_str);
}
// delete a file attached to a record
else if ($delete_file) delete_attached_file($file_field_name, $ID, 'helpdesk');

// insert new request
else if (!$ID && isset($_REQUEST['name'])) {
    $accessstring=insert_access('helpdesk');
    sqlstrings_create();
    //with extended workflow you have to folllow the status order!
    if(RTS_WORKFLOW==1){
        $status = $helpdesk_states[0]['key'];
    }
    //set status to assigned in case user is assigned and status wasn't set!
    if ($status==$helpdesk_states[0]['key'] and $assigned<>''){
        $status=2;
    }
    $result = db_query("INSERT INTO ".DB_PREFIX."rts
                               (        gruppe      ,        parent  ,  acc_read        ,  acc_write       ,        status    ,".$sql_fieldstring.")
                        VALUES (".(int)$user_group.",".(int)$parent.",'$accessstring[0]','$accessstring[1]','".xss($status)."',".$sql_valuestring.")") or db_die();

    $result = db_query("SELECT MAX(ID)
                          FROM ".DB_PREFIX."rts
                         WHERE von = ".(int)$user_ID) or db_die();
    $row = db_fetch_row($result);
    $ID = $row[0];

    // inform the receiver about his new ticket
    if ($notify_recipient <> '' and $assigned <> '') {
        $notify = new Notification($user_ID, $user_group, "helpdesk", array($assigned),
        "&mode=forms&ID=".$ID,
        slookup('users','nachname,vorname','ID',$user_ID,'1')." ".__("has created the following request").": ".$name."\n ".
        stripslashes(strip_tags(html_entity_decode($note))),'',__("Helpdesk Ticket Nr.")." $ID: ".$name,1);

        $notify->notify();
        unset($notify);
    }

    if ($create_update_b) {
        $query_str = "helpdesk.php?mode=forms&ID=".$ID;
        header('Location: '.$query_str);
    }
}
//lockfile
else if ($action == 'lockfile') {
    lock_file($ID,$module);
}

// update request
else if ($ID > 0 && isset($_REQUEST['name'])) {
    // check permission
    $result = db_query("SELECT ID, note,assigned, acc_write, von, status, lock_user
                          FROM ".DB_PREFIX."rts
                         WHERE ID = ".(int)$ID."
                           AND (acc_read LIKE 'system'
                                OR ((von = ".(int)$user_ID."
                                     OR assigned = '$user_ID'
                                     OR acc_read LIKE 'group'
                                     OR acc_read LIKE '%\"$user_kurz\"%')
                                    AND $sql_user_group))") or db_die();
    $row = db_fetch_row($result);
    if (!$row[0] or ($row[2] <> $user_ID and $row[4] <> $user_ID and $row[3] <> 'w') or $user_type==4) die("You are not allowed to do this1");
    if(!check_locked("rts","lock_user",$ID)) die("This record was locked");
    if (($row[2] <> $user_ID and $row[4] <> $user_ID AND RTS_WORKFLOW == 1))die("You are not allowed to do this2");

    // check whether this record is assigned to this user - if yes, allow him to change the permission status
    // otherwise don't change this field
    $accessstring = update_access('helpdesk',$row[4],'acc_read');
    if (RTS_WORKFLOW == 1 AND $row[2] <> $user_ID AND $row[4] <> $user_ID) {
        $accessstring      = '';
    }

    //if status can't be changed set it to old value
    $allowed_users=array();
    foreach ($status_arr as  $status_nr=>$status1){
        if ($status1[0] ==$status) {
            $allowed_users = $status1[1];
        }
    }
    if(RTS_WORKFLOW == 1 AND !in_array($user_ID, $allowed_users)){
        $status=$row[5];
    }
    // end check permission

    //keep history
    if (PHPR_HISTORY_LOG > 0) {
        sqlstrings_create();
        history_keep('rts', 'acc_read,acc_write,'.$sql_fieldstring, $ID);
    }

    // notify if the assigned user has been changed
    if ($assigned <> $row[2]){

        //set status to assigned in case it wasn't set before
        if ($status==$helpdesk_states[0]['key']){
            $status=2;
        }
        if($notify_recipient <> '') {

            // notify the author, the old and new assigned user

            $notify = new Notification($user_ID, $user_group, "helpdesk", array($assigned, $row[2]),
            "&mode=forms&ID=".$ID,
            slookup('users','nachname,vorname','ID',$user_ID,'1')." ".__("has reassigned the following request").": ".$name,"",__("Helpdesk Ticket Nr.")."  $ID: ".$name);
            $notify->notify();
            unset($notify);

        }
    }

    // notify if the status has been changed
    if ($status <> $row[5]) {
        if( $notify_recipient <> ''){
            // notify author and assigned user
            $notify = new Notification($user_ID, $user_group, "helpdesk", array($assigned),
            "&mode=forms&ID=".$ID,
            $name.": ".html_entity_decode(__("Ticket status changed")."(".$helpdesk_states[$status-1]["label"].")"),"",__("Helpdesk Ticket Nr.")." $ID: ".$name);
            $notify->notify();
            unset($notify);
        }
        //reset solved in case status was reopened
        if(($row[5] == 3 or ($row[5] == 'solved')and($status != 'solved' or $status != 3))){
            $result = db_query("UPDATE ".DB_PREFIX."rts
                                   SET solved = 0,
                                       solve_time = ''
                                 WHERE ID = ".(int)$ID) or db_die();
        }

    }

    $sql_string = sqlstrings_modify();
    // update record in db
    $query="UPDATE ".DB_PREFIX."rts
                SET $sql_string
                    $accessstring
                    parent = ".(int)$parent.",
                    status = '".xss($status)."',
                    contact = ".(int)$contact_ID."
              WHERE ID = ".(int)$ID;
    $result = db_query($query) or db_die();

    // update project-related times
    timeproj_change_project_id ('helpdesk', $ID, $proj);

    // ********
    // solve request, mail to customer, set access
    if ($status==3&&$status <> $row[5]) {
        //if ($action == 'solve') {
        $result = db_query("UPDATE ".DB_PREFIX."rts
                               SET solved     = ".(int)$user_ID.",
                                   solve_time = '$dbTSnull'
                             WHERE ID = ".(int)$ID) or db_die();
    }

    // ********
    // move request
    else if ($action == 'moveto') {
        $result = db_query("SELECT ID, name, note, remark
                              FROM ".DB_PREFIX."rts
                             WHERE ID = ".(int)$ID) or db_die();
        $row = db_fetch_row($result);
        $result2 = db_query("SELECT ID, name, note, remark
                               FROM ".DB_PREFIX."rts
                              WHERE ID = ".(int)$moveto) or db_die();
        $row2 = db_fetch_row($result2);

        $name   = quote_runtime($row2[1]."\n+ Nr. $row[0]:\n ".$row[1]);
        $note   = quote_runtime($row2[2]."\n+ Nr. $row[0]:\n ".$row[2]);
        $remark = quote_runtime($row2[3]."\n+ Nr. $row[0]:\n ".$row[3]);
        // update new record
        $result = db_query("UPDATE ".DB_PREFIX."rts
                                   SET remark = '".strip_tags($remark)."',
                                       note   = '".xss($note)."',
                                       name   = '".strip_tags($name)."'
                                 WHERE ID = ".(int)$moveto) or db_die();

        // put a remark into the old record that it has moved
        $result = db_query("UPDATE ".DB_PREFIX."rts
                                   SET remark = 'moved to ".strip_tags($moveto)."'
                                 WHERE ID = ".(int)$ID) or db_die();
    }

    if ($modify_update_b) {
        $query_str = "helpdesk.php?mode=forms&ID=".$ID;
        header('Location: '.$query_str);
    }

}

// modify project-related times
if (isset($timeproj_add) || isset($timeproj_delete)) {
    timeproj_insert_record($user_ID, $ID, $proj, 'helpdesk', $timeproj_add);
    timeproj_delete_record($timeproj_delete);
}

//After copying the form should be displayed again
if ($copy){
    include_once("./helpdesk_forms.php");
}
else{
    // show the helpdesk list :-)
    $fields = build_array('helpdesk', $ID, 'view');
    include_once("./helpdesk_view.php");
}


function delete_record($ID) {
    global $fields, $user_ID;

    // check permission
    $result = db_query("SELECT assigned, acc_write, ID, von
                          FROM ".DB_PREFIX."rts
                         WHERE ID = ".(int)$ID) or db_die();
    $row = db_fetch_row($result);
    if ($row[2] == 0) die("no entry found.");
    if(!check_locked("rts","lock_user",$ID))die("You are not privileged to do this!");
    if ($row[0] <> $user_ID and !$row[1] and (!(($row[0]==0 or $row[0]=='')and $row[3]==$user_ID ))) die("You are not privileged to do this!");

    // delete all files associated with this record
    foreach ($fields as $field_name => $field) {
        if ($field['form_type'] == 'upload' ) {
            upload_file_delete($field_name, $ID, 'helpdesk');
        }
    }

    // delete cooresponding entry from timeproj
    timeproj_unlink_moduletimes($ID, 'helpdesk');

    // delete record in db
    $result = db_query("DELETE
                          FROM ".DB_PREFIX."rts
                         WHERE ID = ".(int)$ID) or db_die();
    // delete corresponding entry from db_record
    remove_link($ID,'helpdesk');
    remove_link($ID,'rts');
    delete_remark('helpdesk',$ID);

    // delete history for this db entry
    if (PHPR_HISTORY_LOG > 0) history_delete('rts', $ID);
}

/**
 * Function returns the recipents
 * @author Nina Schmitt
 * @param int $ID ID of element
 * @return array $recipients
 */
function get_recipients($ID){
    global $user_ID;
    $recipients=array();
    $result = db_query("SELECT von,assigned,lock_user
                          FROM ".DB_PREFIX."rts
                         WHERE ID = ".(int)$ID) or db_die();
    $row=db_fetch_row($result);
    if($row[0]!=$user_ID)$recipients[]=$row[0];
    if($row[1]!=$user_ID)$recipients[]=$row[1];
    if($row[2]!=$user_ID)$recipients[]=$row[2];
    return $recipients;
}

/**
 * Function returns the title (name) of the ticket
 * @param int $ID ID of element
 * @return string title of ticket
 */
function get_title($ID){

    $title = '';

    $result = db_query("SELECT name
                          FROM ".DB_PREFIX."rts
                         WHERE ID = ".(int)$ID) or db_die();
    if ($row = db_fetch_row($result)) {
        $title = $row[0];
    }

    return $title;
}

/**
 * Function locks /unlocks ticket if user has the right permission
 * @author Nina Schmitt
 * @param int $ID ID of element
 * @param string $module associates module
 */
function lock_file($ID,$module){
    global $user_ID,$tablename,$user_type,$lock,$unlock;
    $error = '';
    $result = db_query("SELECT von, acc_read, acc_write, assigned,lock_user
                          FROM ".DB_PREFIX.$tablename[$module]."
                         WHERE ID = ".(int)$ID) or db_die();
    $row = db_fetch_row($result);
    if ($row[0] == 0) die('no entry found.');
    if ($row[0] <> $user_ID && $row[3] <> $user_ID && $row[4] <> $user_ID && (strpos($row[1], $_SESSION['user_kurz']) === false ||
    strpos($row[1], $_SESSION['user_kurz']) !== false && strpos($row[2], 'w') === false)) {
        $error=1;
        message_stack_in(__('You are not privileged to do this!'),$module,"error");
    }
    if (eregi("xxx", $ID)) $ID = substr($ID, 14);
    $lock_user=$row[4];
    // define the locking status
    // checkbox 'lock this field' has been selected by the user
    //set lock_user to old value
    if ($lock == 'true') {
        // simply return the user_ID of this user
        // locking is not allowed in case ticket is allready locked or in case the user is a client
        if ($lock_user > 0 or $user_type==4) {
            $error=1;
            message_stack_in(__('You are not privileged to do this!'),$module,"error");
        }
        else $lock_user=$user_ID;
    }
    else if ($unlock == 'true') {
        // first check whether this user has the right to unlock the file (lock user himself or chef user)
        if ($user_type==2 or $lock_user==$user_ID) {
            $lock_user=0;
        }
        else {
            $error=1;
            message_stack_in(__('You are not privileged to do this!'),$module,"error");
        }
    }
    // finally: the db action :)
    if (!$error && $lock_user<>$row[4]) {
        $query = "UPDATE ".DB_PREFIX.qss($tablename[$module])."
                     SET lock_user = ".(int)$lock_user."
                   WHERE ID = ".(int)$ID;
        $result = db_query($query) or db_die();
    }
}


?>
