<?php

// todo_data.php - PHProjekt Version 5.2
// copyright  ©  2000-2005 Albrecht Guenther  ag@phprojekt.com
// www.phprojekt.com
// Author: Albrecht Guenther, $Author: polidor $
// $Id: todo_data.php,v 1.58.2.7 2007/05/09 14:05:25 polidor Exp $

// check whether the lib has been included - authentication!
if (!defined("lib_included")) die("Please use index.php!");

// check role
if (check_role("todo") < 2) die("You are not allowed to do this!");

// check form token
check_csrftoken();

// fetch permission routines
include_once(LIB_PATH."/permission.inc.php");
include_once(LIB_PATH."/timeproj.inc.php");
include_once('./todo.inc.php');

if ($ID == '') {
    $ID = 0;
}


switch (true) {

    case ($cancel):
        break;

        // undertake todo
    case ($undertake == 1):
        //check whether this todo is still free ..
        $result = db_query("SELECT ext
                              FROM ".DB_PREFIX."todo
                             WHERE ID = ".(int)$ID) or db_die();
        $row = db_fetch_row($result);
        if ($row[0] > 0) {}
        else {
            // assign this todo to the current user
            $result = db_query("UPDATE ".DB_PREFIX."todo
                                   SET ext    = $user_ID,
                                       sync2  = '$dbTSnull',
                                       status = '3'
                                 WHERE ID = ".(int)$ID) or db_die();
        }
        break;

    case ($delete_b):
        if      ($ID > 0)     manage_delete_records($ID, $module);
        else if ($ID_s <> '') manage_delete_records($ID_s, $module);
        break;

    case ($delete <> ''):
        delete_record($ID);
        break;

    // copy todo    
    case ($copy): 
    	$fields = change_fields_for_copy($fields,'remark');
    	$ID = 0;
    	break;         
        
    case (!$ID):
        // Status - if it an own todo, put it to accepted, otherwise to open
        if ($ext == $user_ID) $status = 3;
        // at the moment of creation the progress must be 0
        $progress = 0;

        $accessstring = insert_access('todo');
        // create record in db
        $_POST['von'] = $user_ID; // userID should not be set from outside
        sqlstrings_create(); 
        $result = db_query("INSERT INTO ".DB_PREFIX."todo
                                   (        status   ,  sync1    ,  sync2    ,        gruppe      ,  acc             ,  acc_write       , ".$sql_fieldstring.")
                            VALUES (".(int)$status." ,'$dbTSnull','$dbTSnull',".(int)$user_group.",'$accessstring[0]','$accessstring[1]', ".$sql_valuestring.")") or db_die();
        

        // notify recipient about the new todo
        if ($notify_recipient <> '' and $ext > 0) {
            include_once(LIB_PATH."/notification.inc.php");
            $notify = new Notification($user_ID, $user_group, $module, array($ext),
            "&mode=forms&ID=".todo_get_last_id($user_ID, $ext),
            strip_tags($remark.": ".$note));
            $notify->text_title = __('Todo').': '.strip_tags($remark);
            $notify->notify();
        }

        if ($modify_update_b || $create_update_b) {
            // find the ID of the last created user and assign it to ID
            $result = db_query("SELECT MAX(ID)
                                  FROM ".DB_PREFIX."todo") or db_die();
            $row = db_fetch_row($result);
            $ID = $row[0];
            $query_str = "todo.php?mode=forms&ID=".$ID."&justform=".$justform;
            header('Location: '.$query_str);
        }
        break;

    case ($ID > 0 && isset($_REQUEST['remark']) && (xss($_REQUEST['remark']) <> '')):
        // check permission
        $perm_modify = check_perm_modify($ID, $module, 'acc');
        if ($perm_modify <> 'write' && $perm_modify <> 'owner') {
            $err_msg[] = 'You cannot modify the records with the ID '.$ID;
            $error = true;
            
            /* SPECIAL CASE */
            // if a user has not write permission but it is assigned to him, then it is possible to modify ONLY the status
            
            //check whether this todo is assigned to this user
            $result_user = db_query("SELECT ext
                                  FROM ".DB_PREFIX."todo
                                 WHERE ID = ".(int)$ID) or db_die();
            
            $row_user = db_fetch_row($result_user);
            
            // if the todo is assigned to one user and it has only permission to read, the status can be modified
            if (($row_user[0] == $user_ID) && ($perm_modify == 'read')) {
                $result = db_query("UPDATE ".DB_PREFIX."todo
                                           SET sync2 = '$dbTSnull',
                                               status = ".(int)$status."
                                        WHERE ID = ".(int)$ID) or db_die();
            }
            // updating history
            history_keep('todo', 'status', $ID);
            /* END SPECIAL CASE */
            
            
        }
        if (!$error) {
            if ((PHPR_HISTORY_LOG > 0)) {
                sqlstrings_create();
                history_keep('todo', 'acc,acc_write,'.$sql_fieldstring, $ID);
            }
            
            // workaround if $status is not set
            if (!$status) $status = $row[2];
            
            // if the task is 100% complete, then the status will be closed (5)
            if ($progress == "100") $status = 5;
            
            // If the user creating the todo assign it to himself, then (we wil skip closed and rejected bugs)
            if ($ext == $user_ID && $row[1] != $user_ID && $status != 5 && $status != 4) $status = 3;
            
            // an owner of a todo can stop the todo without deleting it:
            if ($todo_done <> "") {
                $status = 5; 
                // can a todo be done without the 100% of progress done?  probably yes
                // $progress = 100; 
            }
            if ($todo_reopen <> "") {
                $status = 1; 
                $progress = 0;
            }
            
            if (isset($progress)) {
                $progressstring = " progress = ".(int)$progress.",";
            }
            
            $accessstring = update_access('todo',$perm_modify=='owner'?$user_ID:0);

            // update record in db
            $sql_string = sqlstrings_modify();
            
            // Special check for empty todo update (this line will check if not receive an empty remark
            $pos = strpos($sql_string, "remark = '',");
            if ($pos === false) {
                
                /*
                if ($status == '') {
                    $status = 0;
                }
                */
                $result = db_query("UPDATE ".DB_PREFIX."todo
                                       SET $accessstring                      
                                           $sql_string 
                                           $progressstring
                                           sync2 = '$dbTSnull',
                                           status = ".(int)$status.",
                                           comment1 = '".xss($comment1)."' 
                                     WHERE ID = ".(int)$ID) or db_die();
            }
            
            // update project-related times
            timeproj_change_project_id ('todo', $ID, $project);
        }
        else{
            message_stack_in('You cannot modify the record with the ID '.$ID,$module,"error");

        }

        break;
}


// modify project-related times
if (isset($timeproj_add) || isset($timeproj_delete)) {
    if($ID>0){
        timeproj_insert_record($user_ID, $ID, $project, 'todo', $timeproj_add);
        timeproj_delete_record($timeproj_delete);
    }
    else{
       timeproj_insert_record($user_ID, todo_get_last_id($user_ID, $ext), $project, 'todo', $timeproj_add);
    }
}

// show the todo list :-)
$status = 0;

if ($copy) {
   include_once("./todo_forms.php");	
}
else if ($modify_update_b || $create_update_b) {
    $query_str = "todo.php?mode=forms&ID=".$ID."&justform=".$justform;
    header('Location: '.$query_str);
    die();
}
elseif (!$justform) {
    if ($module <> 'todo') {
        $fields = build_array($module, $ID, 'view');
        include_once("./todo_view.php");
    }
    else {
        $query_str = "todo.php?mode=view&justform=".$justform;
        header('Location: '.$query_str);
        die();
    }
}
else {
    echo '<script type="text/javascript">ReloadParentAndClose();</script>';
}


function delete_record($ID) {
    global $user_ID, $module;
    $result = db_query("SELECT von, ext, acc_write
                          FROM ".DB_PREFIX."todo
                         WHERE ID = ".(int)$ID) or db_die();
    $row = db_fetch_row($result);
    // no entry found
    if (!$row[0]){
        message_stack_in('You are not privileged to do that '.$ID,$module,"error");
        $error=true;
    }
    $perm_modify = check_perm_modify($ID, 'todo', 'acc_write');
    if(strval($perm_modify)== 'write' or strval($perm_modify) == 'owner' or $user_ID==$row['1']){

        // delete request
        $result = db_query("DELETE
                              FROM ".DB_PREFIX."todo
                             WHERE ID = ".(int)$ID) or db_die();
        // delete corresponding entry from db_record
        remove_link($ID, 'todo');

        // delete cooresponding entry from timeproj
        timeproj_unlink_moduletimes($ID, 'todo');

        // delete history
        if (PHPR_HISTORY_LOG > 0) history_delete('todo', $ID);
    }
    else{
        message_stack_in('You are not allowed to do that '.$ID,$module,"error");

    }
}

?>
