<?php

// cronjob.inc.php - PHProjekt Version 5.2
// copyright  ©  2000-2006 Albrecht Guenther  ag@phprojekt.com
// www.phprojekt.com
// Author: Nina Schmitt

$ip = getenv ("REMOTE_ADDR"); // get the ip number of the user

include_once('../config.inc.php');

// usual authorization not needed
if($ip==PHPR_SERVER_IP){   
  define('avoid_auth','1');
}

define('PATH_PRE','../');
// include lib
include_once('lib.inc.php');

// if the script isn't called by server, user has to be admin!
if($ip != PHPR_SERVER_IP and $user_type != 3) die('You are not allowed to do this');

// run global cronjob
global_cronjob();

//get all modules
include_once('show_modules.inc.php');

//check if local cronjob exists for module and include it
foreach($mod_arr as $key => $mod){
    $cron_name = PATH_PRE.$mod[0].'/'.$mod[0].'_cron.php';
    if(file_exists($cron_name)){
        include_once($cron_name);
    }
}

/**
 * This function calls all global functions for the cronjob
 * 
 * @author Nina Schmitt
 * @param void
 * @return void
 *
 */
function global_cronjob(){
    $emails = get_new_mails();
    if(is_array($emails['reply'])){
        insert_replies($emails['reply']);
    }
    if(is_array($emails['remark'])){
        insert_remarks($emails['remark']);
    }
    //next call module inserts at the moment only helpdesk!
    if(is_array($emails['helpdesk'])){
        insert_new_helpdesk_tickets($emails['helpdesk']);
    }
}

function get_new_mails(){
    //first get all emails
    // fetch all mails
    // Include the lib
    include_once(LIB_PATH.'/fetchmail.php');
    // first: get all email accounts which belong to a categorie(later this will be separated for different account
    // types - at the moment only mail is possible)!
    $query = "SELECT module, accountname, hostname, type, username, password, name, users, gruppe, cat.ID
                FROM ".DB_PREFIX."db_accounts as cat 
           LEFT JOIN ".DB_PREFIX."global_mail_account as acc
                  ON cat.account_ID= acc.ID 
               WHERE cat.account_type='mail'";
    $result = db_query(xss($query)) or db_die();

    //now get all emails and return them
    $email_array = array();
    while ($row = db_fetch_row($result)) {
        // New object
        // host,pop_user,pop_pass,boolean: delete mail or not, type: pop3 is default)
        if($row[2]<>'' and $row[4]<>'' and $row[5]<>''){
            $mail_obj = new fetchmail( $row[2],$row[4],$row[5],$row[3]);
            $mail_obj->get_mail('all','all',PHPR_DOC_PATH);
            foreach ($mail_obj->email as $val) {
                //first of all delete mails, in case sender isn't known
                $ID=slookup('users','ID','email',$val['senderemail']);
                if($ID>0){
                    $arr = explode('@', $val['parent']);
                    // if messagedID refers to ticket:
                    $email_data=array('sender'=>$ID,'subject'=>$val['subject'],'body'=>parse_body($val['body_text'].$val['body_html']),
                    'date'=>$val['date'],'attachment'=>$val['attachment']);
                    //mark emails which are replies/remarks
                    if ($arr[1] == PHPR_SERVER_IP){
                        $tmp=explode('|',$arr[0]);
                        if($tmp[0]=="remark"){
                            $email_data['ticket_ID']=$tmp[1];
                            $email_data['module']=$row[0];
                            $email_array['remark'][]=$email_data;
                        }
                        else if($tmp[0]=="reply"){
                            $email_data['parent']=$tmp[1];
                            $email_data['module']=$row[0];
                            $email_array['reply'][]=$email_data;
                        }
                        else
                        //new elements
                        $email_array[$row[0]][]=array_merge(array('name'=>$row[6],'users'=>$row[7],'gruppe'=>$row[8],'cat_ID'=>$row[9]),$email_data);

                    }
                    else
                    //new elements
                    $email_array[$row[0]][]=array_merge(array('name'=>$row[6],'users'=>$row[7],'gruppe'=>$row[8],'cat_ID'=>$row[9]),$email_data);
                }

                else error_log("email von $val[senderemail] wurde enfternt");
            }
        }
    }
    return $email_array;
}
/**
 * This functions inserts replies (parent >0) into db_remarks table
 * 
 * @author Nina Schmitt
 * @param array $replies array with new replies
 * @return void
 */
function insert_replies($replies){
    global $dbTSnull;
    foreach ($replies as $value){
        $file=array();
        foreach ($value['attachment'] as $val){
            $file[]=$val['att_name'].'|'.$val['att_tempname'];
        }
        $files=implode("#",$file);
        //first check if parent exists!
        $query="SELECT ID,module_ID FROM ".DB_PREFIX."db_remarks
                 WHERE module='$value[module]'
                   AND ID = ".(int)$value[parent];
        $result=db_query(xss($query))or db_die();
        $row=db_fetch_row($result);

        // if data exists  insert new Reply
        if($row[0]>0){
            $query = "INSERT INTO ".DB_PREFIX."db_remarks
                             (        module             ,        module_ID, date    ,        remark           ,    author            ,          parent            ,    upload  )
                      VALUES ('".qss($value['module'])."',".(int)$row[1]." ,$dbTSnull,'".xss($value['body'])."','".$value['sender']."', '".(int)$value['parent']."','".$files."')";
            $result = db_query(xss($query)) or db_die();

        }
    }
}

/**
 * This functions inserts remarks (parent=0) into db_remarks table
 * 
 * @author Nina Schmitt
 * @param array $remarks array with new remarks
 * @return void
 */
function insert_remarks($remarks){
    global $dbTSnull, $tablename;
    foreach ($remarks as $value){
        $file=array();
        foreach ($value['attachment'] as $val){
            $file[]=$val['att_name'].'|'.$val['att_tempname'];
        }
        $files=implode("#",$file);
        //first check if element in module exists!
        $query="SELECT ID
                  FROM ".DB_PREFIX.$tablename[$value['module']]."
                 WHERE ID = ".(int)$value[ticket_ID];
        $result=db_query(xss($query))or db_die();
        $row=db_fetch_row($result);
        // if data exists insert new Reply
        if($row[0] > 0){
            $query = "INSERT INTO ".DB_PREFIX."db_remarks
                            (        module             ,        module_ID         , date    ,        remark           ,    author            ,parent,    upload  )
                     VALUES ('".qss($value['module'])."',".(int)$value[ticket_ID].",$dbTSnull,'".xss($value['body'])."','".$value['sender']."',0     ,'".$files."')";        
            $result = db_query(xss($query)) or db_die();

        }
    }
}

/**
 * This functions inserts new helpdesk tickets into rts tables
 * 
 * @author Nina Schmitt
 * @param array $tickets array with new tickets data
 * @return void
 */
function insert_new_helpdesk_tickets($tickets){
    global $dbTSnull, $tablename;
    foreach ($tickets as $ticket){
        $file=array();
        if (is_array($ticket['attachment'])) {
            foreach ($ticket['attachment'] as $value){
                $file[]=$value['att_name'].'|'.$value['att_tempname'];
            }
        }
        $files=implode("#",$file);
        $query="INSERT INTO ".DB_PREFIX.$tablename['helpdesk']."
                       (    submit           ,    name                ,    note             , acc_read, acc_write,        von               ,        gruppe            ,  filename  ,    category           )
                VALUES ('".$ticket['date']."','".$ticket['subject']."','".$ticket['body']."', 'group' , 'w'      ,".(int)$ticket['sender'].",".(int)$ticket['gruppe'].",'$files','".$ticket['cat_ID']."')";
        $result = db_query(xss($query)) or db_die();
        $recipients = array(0 => $ticket['sender']);
        $query = "SELECT MAX(ID) 
                    FROM ".DB_PREFIX.$tablename['helpdesk']."
                   WHERE von = ".(int)$ticket[sender];
        $result = db_query(xss($query)) or db_die();
        $row = db_fetch_row($result);
	    $remark = slookup('db_accounts','message','ID',$ticket['cat_ID'],'1');
	    $remark= str_replace('{TICKETID}',$row[0],$remark);	
	  	$remark= str_replace('{TICKETBODY}',$ticket['body'],$remark);
        remark_notification($remark,0,$row[0],'helpdesk',$recipients);
    }
}

/**
 * Prepares Email body for database
 *
 * @author Nina Schmitt
 * @param string $body emailbody
 * @return string $parsed_body 
 */
function parse_body($body){
    $parsed_body = addslashes(ereg_replace("\r","",$body));
    $parsed_body = strip_tags($parsed_body);
    $parsed_body = xss($parsed_body);
    return $parsed_body;
}
