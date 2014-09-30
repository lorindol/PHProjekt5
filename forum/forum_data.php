<?php
/**
* forum db data script
*
* @package    forum
* @module     main
* @author     Albrecht Guenther, $Author: gustavo $
* @licence    GPL, see www.gnu.org/copyleft/gpl.html
* @copyright  2000-2006 Mayflower GmbH www.mayflower.de
* @version    $Id: forum_data.php,v 1.29.2.1 2007/01/13 15:00:44 gustavo Exp $
*/
if (!defined('lib_included')) die('Please use index.php!');

// check role
if (check_role("forum") < 2) { die("You are not allowed to do this!"); }

// check form token
check_csrftoken();

$include_path3 = PATH_PRE."lib/access.inc.php";
include_once $include_path3;
$access = assign_acc($acc, 'forum');

function insert($fID="", $ID="") {

    global $user_ID, $titel, $remark, $user_group, $dbTSnull,
    $notify_others, $notify_me, $user_email, $mail, $user_name,
    $access, $acc_write;

    // if it's a root posting, st the 'lastchanged' field to NOW so it appears on the top of the list
    if ($ID == 0) { $lastchange = $dbTSnull; }
    // otherwiese theis field doesn't matter :-)
    else { $lastchange = "NULL"; }
    // write access is not really necessary at the moment since we do not have an update function :-))
    if ($acc_write <> '') { $acc_write = 'w'; }
    // database action - insert record
    $result = db_query("INSERT INTO ".DB_PREFIX."forum
                               (        von      ,        titel    ,        remark    ,  datum    ,        gruppe       ,        parent ,        antwort,        lastchange    ,        notify       ,  acc    ,  acc_write )
                        VALUES (".(int)$user_ID.",'".xss($titel)."','".xss($remark)."','$dbTSnull',".(int)$user_group." ,".(int)$fID."  ,".(int)$ID."   ,'".xss($lastchange)."','".xss($notify_me)."','$access','$acc_write')") or db_die();
    // update root posting to the current date
    if ($ID > 0) { update_root($ID); }
    if ($fID > 0) { update_root($fID); }


    // send out notifications to the group members if the flag is set
    if (PHPR_FORUM_NOTIFY and $notify_others) {
        // get last insert id
        $result = db_query("SELECT MAX(ID)
                              FROM ".DB_PREFIX."forum");
        $row = db_fetch_row($result);
        // include the library from lib
        include_once(LIB_PATH."/notification.inc.php");
        // call routine to send mails with notification about the new record
        $notify = new Notification($user_ID, $user_group , "forum", "all",
        "&mode=forms&ID=$row[0]&fID=$fID",
        __('Module')." ".__('Forum').": ".__('New Thread'));
        $notify->text_body[] = $remark;
        $notify->notify();
    }
    // next notification: check whether the poster of the parent posting wants to be informed
    if ($fID > 0) {
        $bei=$fID;
        if($ID>0)$bei=$ID;
        $result = db_query("SELECT titel, notify, email
                              FROM ".DB_PREFIX."forum, ".DB_PREFIX."users
                             WHERE ".DB_PREFIX."forum.ID = ".(int)$bei."
                               AND ".DB_PREFIX."forum.von = ".DB_PREFIX."users.ID") or db_die();
        $row = db_fetch_row($result);
        if ($row[1] == "on" and $row[2] <> "" ) {
            use_mail('1');
            $titel1 = __('You got an answer to your posting').' '.$row[0].' '.__('by').' '.$user_name.', '.__('Title').': '.$titel;
            $mail->go($row[2],__('Answer to your posting in the forum'),$titel1,$user_email);
        }
    }
    // end routines email notification
}

// find the root posting and update the 'lastchanged' value
function update_root($antwort) {
    global $dbTSnull;

    while ($antwort > 0) {
        $result = db_query("SELECT id, antwort
                              FROM ".DB_PREFIX."forum
                             WHERE ID = ".(int)$antwort) or db_die();
        $row = db_fetch_row($result);
        $antwort = $row[1];
    }
    $result=db_query("UPDATE ".DB_PREFIX."forum
                         SET lastchange='$dbTSnull'
                       WHERE ID = ".(int)$row[0]) or db_die();
}

if($createfor){
    insert();
    $createfor="";
}

elseif($createbei){
    insert($fID);
    $createbei="";
}
elseif($answer){
    insert($fID,$ID);
    $createbei="";
}
elseif(empty($back)){
    // insert record into database ....
    insert();
}
// ... and call the list
include_once("./forum_view.php");


?>
