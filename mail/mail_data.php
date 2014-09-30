<?php

// mail_data.php - PHProjekt Version 5.2
// copyright  ©  2000-2005 Albrecht Guenther  ag@phprojekt.com
// www.phprojekt.com
// Author: Albrecht Guenther, $Author: nina $
// $Id: mail_data.php,v 1.41 2006/11/16 13:13:11 nina Exp $

// check whether the lib has been included - authentication!
if (!defined("lib_included")) die("Please use index.php!");

// check role
if (check_role("mail") < 1) die("You are not allowed to do this!");

// check form token
check_csrftoken();

// sadly enough here comes the third check whether the imap library is installed ;-)
if (!function_exists('imap_open')) die("Sorry but the full functionality of the mail client requires the imap-extension
                                        of php. Please ensure that this extension is active on your system.<br />
                                        In the meantime if you want to use the mail send module, set PHPR_QUICKMAIL=1; in the config.inc.php");

include_once(LIB_PATH."/email_getpart.inc.php");


// fetch permission routines
include_once(LIB_PATH."/permission.inc.php");
include_once(LIB_PATH."/timeproj.inc.php");

// unset all references to attachments and let the script create them
//session_unregister("file_ID");
//unset($file_ID);
unreg_sess_var($file_ID);

// seems that the last function doesn't work properly on some php installations -> additional function to make sure that ...
$file_ID = array();

if ($delete_c <> '' || $delete_b <>'') {
    if(isset($ID_s) && $ID_s != ''){
        $ID = $ID_s;
    }
    manage_delete_records($ID,$module);
}
if ($action == "showhtml") {
  // fetch original data
  if ($ID) {
    $result = db_query("select ID, von, body_html
                          from ".DB_PREFIX."mail_client
                         where ID = ".(int)$ID) or db_die();
    $row = db_fetch_row($result);
    // check permission
    if ($row[0] == 0) { die("no entry found."); }
    if ($row[1] <> $user_ID) { die("You are not allowed to do this!"); }
  }
  $body = stripslashes($row[2]);
  
  // remove text up to (including) <body> and the end of the HTML-Body
  $body = preg_replace("=(.*<body.*>)=isU", "", $body);
  $body = preg_replace("=(</body>.*)$=isU", "", $body);
    
  // add images into the html code
  $i = 0;
  while ($pos1 = strpos($body,"cid")) {
    $i++;
    $i2 = 0;
    $b1 = explode("cid",$body,2);
    $b2 = explode("\"",$b1[1],2);
    $result3 = db_query("select tempname
                           from ".DB_PREFIX."mail_attach
                          where parent = ".(int)$row[0]) or db_die();
    while ($row3 = db_fetch_row($result3)) {
      $i2++;
      if ($i == $i2) { $imgstring = PATH_PRE.PHPR_ATT_PATH."/".$row3[0]; }
    }
    $body = $b1[0].$imgstring."\"".$b2[1];
  }
  // look for scripts in the string and try to avoid poisoned code ...
  $body = eregi_replace("<script","&lt;script",$body);
  // look for images which should be loaded from outer space ...
  $body = eregi_replace("<img[^>]*http[^>]*>","", $body);

  // output of the body from message an stop
 die($body);
}

else if ($copy){
    if($typ == "d")$formdata['dirname']=__('copy')." ".$_POST['dirname'];
    else{
        $result = db_query("select ID,von,subject,body,sender,recipient,cc,kat,remark,date_received,touched,typ,
                               parent,date_sent,header,replyto,acc,body_html
                          from ".DB_PREFIX."mail_client
                         where ID = ".(int)$ID) or db_die(); // fetch missing values from old record
        $row = quote_runtime(db_fetch_row($result));
        $accessstring = insert_access('mail_client');
        sqlstrings_create();
        $result = db_query("insert into ".DB_PREFIX."mail_client
                               (von,   subject,  body,     sender,   recipient, cc,   date_received,touched,typ, date_sent, header,".$sql_fieldstring.",parent,   acc,    acc_write,   gruppe, date_inserted )
                        values (".(int)$user_ID." ,'".__('copy')." $row[2]','$row[3]','$row[4]','$row[5]','$row[6]','$dbTSnull',  1,'m','$row[13]','$row[14]',".$sql_valuestring.",".(int)$parent.",'$accessstring[0]', '$accessstring[1]',".(int)$user_group.",'$dbTSnull' )") or db_die();

        // copy attachments as well
        $ID = copy_attachments($ID);
    }

}
// ***********************
// actions for directories

elseif (($modify_b <> '' || $modify_update_b <> '') and $ID > 0) {
  // check for same record name
  $result = db_query("select ID, subject
                        from ".DB_PREFIX."mail_client
                       where typ like 'd'") or db_die();
  while ($row = db_fetch_row($result)) {
    if ($row[0] <> $ID and $row[1] == $subject) die(__('This name already exists')."!<br /><a href='mail.php?mode=view&action=form&amp;csrftoken=".make_csrftoken()."'>".__('back')."</a>");
  }
  // assign category
  if (!$kat) { $kat = $kat2; }
  // update record or move item
    // if type = dir, then allow to change the name
    if ($typ == "d") $dirname2 = "subject = '$dirname',";
    else             $dirname2 = '';
    $accessstring=update_access('mail_client',$user_ID);
    $sql_string=sqlstrings_modify();

    // update for all types (mail, dir)
    $result = db_query("update ".DB_PREFIX."mail_client
                           set   $sql_string
                                 $dirname2
                                  parent= ".(int)$parent.",
                                  $accessstring
                                  gruppe= $user_group 
                         where ID = ".(int)$ID) or db_die();
  
  $action = "";
}

// create new dir
elseif ($create_b <> '') {
  if (!$dirname) {  // filename doesn't exists?
    echo __('Please select a file');
  }
  // check for same record name
  $result = db_query("select subject
                        from ".DB_PREFIX."mail_client
                       where typ like 'd'") or db_die();
  while ($row = db_fetch_row($result)) {
    if ($row[0] == $subject) {
      echo __('This name already exists')."!";
      $error = 1;
    }
  }
  // write record to db
  sqlstrings_create();
  $accessstring = insert_access('mail_client');
  
  if (!$error) {
    $result = db_query("insert into ".DB_PREFIX."mail_client
                               (von,  date_received,typ,subject,parent,acc,acc_write,gruppe,".$sql_fieldstring.", date_inserted )
                        values (".(int)$user_ID.",'$dbTSnull', 'd','$dirname',".(int)$parent.",'$accessstring[0]','$accessstring[1]',".(int)$user_group.",".$sql_valuestring.",'$dbTSnull')") or db_die();
    $action = "";
  }
}

if ($action == 'empty_trash_can') {
    $temp = empty_trash_can($trash_can_ID);
    $action = '';
}


if ($copy){
   include_once("./mail_forms.php");
}

else if ($modify_update_b <> '') {
    $query_str = "mail.php?mode=forms&ID=$ID";
    header('Location: '.$query_str);
    die();
    
    //include_once("./mail_forms.php");
}
else {
    $fields = build_array($module, $ID, 'view');
    include_once("./mail_view.php");
}



function sendmail_link($mailadress,$m_name) {
  global  $sid;
  if (PHPR_QUICKMAIL > 0) {
    echo "<td><a href='mail.php?mode=send_form&recipient=$mailadress".$sid."')>$m_name</a>&nbsp;</td>\n";
  }
  else { echo "<td><a href='mailto:$mailadress'>$m_name</a>&nbsp;</td>\n"; }
}

function delete_record($delete_ID) {
  global $user_ID, $trash_can_ID;
  
  if (!in_tash_can($delete_ID,$trash_can_ID)) {
      
      $result = db_query("UPDATE ".DB_PREFIX."mail_client 
                          SET parent = ".(int)$trash_can_ID."  
                          WHERE ID = ".(int)$delete_ID) or db_die();
  }
  else {
  
      $result = db_query("select ID, von, typ, trash_can 
                            from ".DB_PREFIX."mail_client
                           where ID = ".(int)$delete_ID) or db_die();
      $row = db_fetch_row($result);
      // check permission
      if ($row[0] == 0) { die("no entry found."); }
      if ($row[1] <> $user_ID || $row[3] == 'Y') { die("You are not allowed to do this!"); }
      // delete attachments
      if ($row[2] <> "d") {
        // select files
        $result2 = db_query("select tempname 
                               from ".DB_PREFIX."mail_attach 
                              where parent = ".(int)$delete_ID) or db_die();
        while ($row2 = db_fetch_row($result2)) {
          $path = PATH_PRE.PHPR_ATT_PATH."/".$row2[0];
          unlink($path);
          
        }
        // delete records
        $result2 = db_query("delete from ".DB_PREFIX."mail_attach
                              where parent = ".(int)$delete_ID) or db_die();
      }
      elseif ($row[2] == "d") {
        // free rules
        $result2 = db_query("update ".DB_PREFIX."mail_rules
                                set parent = 0 
                              where parent = ".(int)$delete_ID) or db_die();
      }
      // delete record itself
      $result2 = db_query("delete from ".DB_PREFIX."mail_client
                            where ID = ".(int)$delete_ID) or db_die();
      // delete corresponding entry from db_record
      $result = db_query("delete from ".DB_PREFIX."db_records
                                where t_record = ".(int)$delete_ID." and t_module = 'mail'") or db_die();
    
      if ($row[2] == "d") del($row[0]); // look for files in the subdirectory
  }
}


function del($delete_ID) {

  $result = db_query("select ID, von, typ
                        from ".DB_PREFIX."mail_client
                       where parent = ".(int)$delete_ID) or db_die();
  while ($row = db_fetch_row($result)) {
    // delete attachments if it's not a directory
    if ($row[2] <> "d") {
      // select files
      $result2 = db_query("select tempname
                             from ".DB_PREFIX."mail_attach
                            where parent = ".(int)$delete_ID) or db_die();
      while ($row2 = db_fetch_row($result2)) {
        $path = PATH_PRE.PHPR_ATT_PATH."/".$row2[0];
        unlink($path);
      }
      // delete records
      $result2 = db_query("delete from ".DB_PREFIX."mail_attach
                            where parent = ".(int)$delete_ID) or db_die();
    }
    // directory? -> free rules
    else { $result2 = db_query("update ".DB_PREFIX."mail_rules
                                   set parent = 0 
                                 where parent = ".(int)$delete_ID) or db_die(); }

    // for all types: delete record itself
    $result2 = db_query("delete from ".DB_PREFIX."mail_client
                          where ID = ".(int)$row[0]) or db_die();
    if ($row[2] == "d") del($row[0]); // look for mails etc. in the subdirectory
  }
}

function copy_attachments($ID) {
  global $user_ID, $row, $dbTSnull;

  // 1. fetch the ID of the new record
  $result2 = db_query("select ID
                         from ".DB_PREFIX."mail_client
                        where date_received = '$dbTSnull' and
                              von = ".(int)$user_ID." and
                              subject = '".__('copy')." $row[2]'") or db_die(); // fetch missing values from old record
  $row2 = db_fetch_row($result2);
  // now fetch all attachments from the previous mail
  $result3 = db_query("select filename, filesize, tempname
                         from ".DB_PREFIX."mail_attach
                        where parent = ".(int)$ID) or db_die();
  while ($row3 = db_fetch_row($result3)) {
    // add extension to random name
    $att_tempname = rnd_string().substr($row3[0],-4,4);
    // copy file
    copy(PATH_PRE.PHPR_ATT_PATH."/".$row3[2],PATH_PRE.PHPR_ATT_PATH."/".$att_tempname);
    // write record to db
    $result4 = db_query("insert into ".DB_PREFIX."mail_attach
                                (parent,   filename,        tempname,  filesize )
                         values (".(int)$row2[0].",'$row3[0]','$att_tempname',".(int)$row3[1].")") or db_die();
  }
  return $row2[0];
}

function empty_trash_can ($trash_can_ID = 0) {
    global $trash_can_ID;
    
    $query = "SELECT ID FROM ".DB_PREFIX."mail_client 
              WHERE parent = ".(int)$trash_can_ID;
    
    $result = db_query($query) or db_die();
    
    while ($row = db_fetch_row($result)) {
        $temp = delete_record($row[0]);
    }
}


?>
