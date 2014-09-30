<?php
/**
* filemanager db data script
*
* @package    filemanager
* @module     main
* @author     Albrecht Guenther, $Author: polidor $
* @licence    GPL, see www.gnu.org/copyleft/gpl.html
* @copyright  2000-2006 Mayflower GmbH www.mayflower.de
* @version    $Id: filemanager_data.php,v 1.84.2.3 2007/04/28 16:01:51 polidor Exp $
*/
if (!defined('lib_included')) die('Please use index.php!');
define('PATH_PRE','../');
include_once(PATH_PRE.'lib/permission.inc.php');

// check_role
if (check_role("filemanager") < 2) die("You are not allowed to do this!");

// check form token
check_csrftoken();
// get userfile from db in case of copy
if($_POST['copy_ID']>0) prepare_userfile_for_copy($copy_ID);
$userfile      = $_FILES['userfile']['tmp_name'];
$userfile_name = $_FILES['userfile']['name'];
$userfile_size = $_FILES['userfile']['size'];

if (!$_POST['filename']) $_POST['filename'] = $userfile_name;

if ($delete_b <> '' || $action == 'delete') {
    // delete many entries at once (contextmenu)
    if (isset($ID_s)) $ID = $ID_s;
    manage_delete_records($ID, $module);
}
else if ($copy){
    $fields = change_fields_for_copy($fields,'filename'); 
}
else if (($modify_b <> '' and $ID > 0) || $modify_update_b <> '') {
    update_record($ID);
    if ($modify_update_b) {
        $query_str = "filemanager.php?mode=forms&ID=".$ID."&justform=".$justform;
        header('Location: '.$query_str);
    }
}
else if ($create_b <> '' || $create_update_b) {
    create();
    if ($create_update_b) {
        $result = db_query("SELECT MAX(ID)
                              FROM ".DB_PREFIX."dateien") or db_die();
        $row = db_fetch_row($result);
        $ID = $row[0];
        $query_str = "filemanager.php?mode=forms&ID=".$ID."&justform=".$justform;
        header('Location: '.$query_str);
    }
}
else if ($action == 'lockfile') {
    if (eregi("xxx", $ID)) $ID = substr($ID, 14);
    $error = '';
    // define the locking status
    $locked = define_locking_status($ID);
    // finally: the db action :)
    if (!$error) {
        $result = db_query("UPDATE ".DB_PREFIX."dateien
                               SET lock_user = ".(int)$locked."
                             WHERE ID = ".(int)$ID) or db_die();
    }
    else message_stack_in(__('You are not allowed to change the locking status'),'filemanager','error');
}


function update_record() {
    global $ID, $acc, $kat, $typ, $parent, $div2,
           $user_ID, $remark, $dbTSnull,$filename, $filepath,
           $userfile, $userfile_size, $userfile_name, $cryptstring, $cryptstring2,
           $locked, $contact, $versioning, $user_ID, $new_sub_dir, $new_category;

    assign_cat();
    // fetch missing values from old record
    $result = db_query("SELECT ID,von,filename,remark,kat,acc,datum,filesize,gruppe,tempname,
                               typ,parent,div2,pw,acc_write,version,lock_user,contact,userfile
                          FROM ".DB_PREFIX."dateien
                         WHERE ID = ".(int)$ID) or db_die();
    $row = db_fetch_row($result);
    
    $accessstring = update_access($module,$row[1]);
    
    // this field is only for uploads displayed
	if ($new_sub_dir <> '') $parent = set_new_subdir($parent, $new_sub_dir);
	$parent_string    = "parent=".(int)$parent.",";


    // *******************
    // update or move item
   
        // if it's a file and new upload -> replace file
        if (ereg("f", $typ) and $userfile <> "none" and $userfile) {

            // check whether it's really an upload
            check_upload($userfile);
            if($versioning){
                $oldfilename = $row[9];
                if ($oldfilename != '') {
                    //copy old file to history
                    $copy_query="INSERT INTO ".DB_PREFIX."datei_history 
                                              ( date    ,         remark    ,         author   ,         parent,   version ,   tempname,  userfile )
                                       VALUES ($dbTSnull, '".xss($remark)."', ".(int)$user_ID.", ".(int)$ID."  , '$row[15]', '$row[9]' ,'$row[18]' )";
                    $copy_result=db_query($copy_query) or db_die(); 
                    $oldfilename = rnd_string();
                }
                else {
                    $oldfilename = rnd_string();
                }
                
            }
            else{
                $oldfilename = $row[9];
                if ($oldfilename != '') {
                    // delete old file
                    delete_file($oldfilename);
                }
                else {
                    $oldfilename = rnd_string();              
                }
            }
            // set filename and build string for db query
             $stringfilename = "filename='".addslashes($filename)."',userfile='$userfile_name', tempname='$oldfilename',";
             $stringfilesize =  "filesize = ".(int)$userfile_size.",";

            // action: copy file!
            // first case: no file content encryption
            if (!$cryptstring) {
                // fetch file from tmp directory and move it into specified dir ...
                copy_file($userfile, '', $oldfilename, PHPR_FILE_PATH);
            }
            // oh, crypting!! :-)
            else {
                copy_crypt($userfile, '', $oldfilename, PHPR_FILE_PATH, $cryptstring, $cryptstring2);
                $cryptstring = encrypt($cryptstring, $cryptstring);
            }

            // last action - count the version number one up
            // exception: this file has a flag for versioning
            $result = db_query("UPDATE ".DB_PREFIX."dateien
                                   SET version = version+1
                                 WHERE ID = ".(int)$ID) or db_die();
        }

        // next case: move or update existing file -> carry password
        if (ereg("f", $typ) and ($userfile == "none" or !$userfile)) {
            $stringfilename = "filename='".addslashes($filename)."',";
            $cryptstring    = $row[13];
        }
        // for dir and link: assign new filename
        else if (!ereg("f", $typ)) {
            $stringfilename = "filename='$filename',tempname='$filepath',";
        }
        // define the locking status
        $locked = define_locking_status($ID);
        
        if (PHPR_HISTORY_LOG > 0) {
            sqlstrings_create();
            global $sql_fieldstring;
            history_keep('dateien', 'acc,acc_write,userfile,'.$sql_fieldstring, $ID);
            unset($sql_fieldstring);
        }
        $versioning = ($versioning == 'on') ? 1 : 0;
        // finally: the db action :)
        
        $result = db_query("UPDATE ".DB_PREFIX."dateien
                               SET $stringfilename
                                   $stringfilesize
                                   remark = '".xss($remark)."',
                                   kat = '$kat',
                                   $accessstring
                                   $parent_string
                                   div2 = '$div2',
                                   datum = '$dbTSnull',
                                   pw = '$cryptstring',
                                   versioning = ".(int)$versioning.",
                                   lock_user = ".(int)$locked.",
                                   contact = ".(int)$contact."
                             WHERE ID = ".(int)$ID) or db_die("");
    
}


//insert a link or a directory
function create() {
    global $userfile, $userfile_size, $userfile_name, $kat, $remark,
           $user_ID, $parent, $dbTSnull, $user_group,$module,
           $typ, $project, $cryptstring, $cryptstring2, $locked, $versioning,
           $new_sub_dir, $contact, $sql_fieldstring, $sql_valuestring, $filepath;
           
    $accessstring = insert_access($module);
    
    // upload the file
    if ($userfile <> '' and $userfile_size <> '') {
        $filepath = insert_file();
    }
    // file versioning?
    $versioning = ($versioning == 'on') ? 1 : 0;

    sqlstrings_create();
    
    // this field is only for uploads displayed
    if ($new_sub_dir <> '') $parent = set_new_subdir($parent, $new_sub_dir);
    
    // everythings fine? -> insert record into database
    $result = db_query("INSERT INTO ".DB_PREFIX."dateien
                               (        von      ,  pw          ,  acc             ,        gruppe      ,        filesize       ,        tempname    ,        userfile         ,        typ    ,        parent  ,  acc_write       ,version,       versioning   ,".$sql_fieldstring.")
	                    VALUES (".(int)$user_ID.",'$cryptstring','$accessstring[0]',".(int)$user_group.",".(int)$userfile_size.",'".xss($filepath)."','".xss($userfile_name)."','".qss($typ)."',".(int)$parent.",'$accessstring[1]','1'    ,".xss($versioning).",".$sql_valuestring.")") or db_die();
}


// insert a new file
function insert_file() {
    global $parent, $sql_user_group, $userfile, $userfile_size, $userfile_name, $cryptstring, $cryptstring2;

    // loop over all objects with the same name in the same virtual directory
    $result = db_query("SELECT ID, filename, tempname
                          FROM ".DB_PREFIX."dateien
                         WHERE filesize > 0
                           AND parent =".(int)$parent."
                           AND $sql_user_group") or db_die();
    while ($row = db_fetch_row($result)) {
        // same name? -> delete file
        if ($row[1] == $userfile_name) {
            // check if overwriting is o.k.
            check_overwrite();
            // first delete old record ...
            $result = db_query("DELETE
                                  FROM ".DB_PREFIX."dateien
                                 WHERE ID = ".(int)$row[0]) or db_die();
            // ... then the file itself
            delete_file($row[2]);
        }
    }
    // scramble filename
    $newfilename = rnd_string();

    // first case: no file content encryption
    if (!$cryptstring) {
        // fetch file from tmp directory and move it into specified dir ...
        copy_file($userfile, "", $newfilename, PHPR_FILE_PATH);
    }
    // oh, crypting!! :-)
    else {
        copy_crypt($userfile, "", $newfilename, PHPR_FILE_PATH, $cryptstring, $cryptstring2);
        $cryptstring = encrypt($cryptstring, $cryptstring);
    }

    // ... and check whether the file really went into the directory! if not: give some advices
    if (!file_exists(PHPR_FILE_PATH."/$newfilename")) {
        die( "Oops! Something went wrong ...<br />Please check whether the file exists in the upload directory<br />
        (Maybe the webserver is not allowed to copy the file from the tmp dir into the upload dir)<br />
        and the variable dat_rel in the config has the correct value.<br />
        Typical values would be:<br /> dateien = \"/usr/local/httpd/phprojekt/file\";
        and dat_rel = \"file\"; for Linux or dateien = \"c:\htdocs/phprojekt/file\"; and dat_rel = \"file\"; for windows");
    } // end check
    return $newfilename;
}


// this function encrypts the incoming file and copies into the upload dir
function copy_crypt($oldfilename, $olddir, $newfilename, $newdir, $cryptstring,$cryptstring2) {
    // first check whether the two passwords are the same
    if ($cryptstring != $cryptstring2) {
        die("<h3> ".__('Passwords dont match!')."! <a href='filemanager.php?mode=forms&action=upload'>".__('back')." ...</a></h3>");
    }

    // then create an appropiate string:
    //1. crypt the password ...
    $cryptstring = encrypt($cryptstring, $cryptstring);
    $bytes = 65536;

    // 2: string must be longer than the content piece
    for ($i = 0; $i <= floor($bytes/strlen($cryptstring)); $i++) {
        $pwnew .= $cryptstring;
    }
    // then open both files
    if ($olddir <> '') $old_path = $olddir.'/'.$oldfilename;
    else               $old_path = $oldfilename;

    if ($newdir <> '') $new_path = $newdir.'/'.$newfilename;
    else               $new_path = $newfilename;

    $old = fopen($old_path, "rb");
    $new = fopen($new_path, "w");
    // crypt the content and write it into the new file
    while($line = fread($old, $bytes)) {
        $line2 = $line ^ $pwnew;
        fputs($new, $line2);
    }
    // close both files
    fclose($old);
    fclose($new);
}


function delete_file($filename) {
    $path = PHPR_FILE_PATH."/".$filename;
    @unlink($path);
}


function copy_file($oldfilename, $olddir, $newfilename, $newdir) {
    if ($olddir <> '') $old_path = $olddir.'/'.$oldfilename;
    else               $old_path = $oldfilename;

    if ($newdir <> '') $new_path = $newdir.'/'.$newfilename;
    else               $new_path = $newfilename;
    if($_POST['copy_ID']>0) $success = copy($old_path, $new_path);
    else $success = move_uploaded_file($old_path, $new_path);
    if (!$success) die("Panic - Could not copy $old_path to $new_path!<br />");
}


function check_overwrite() {
    global $overwrite, $sid, $acc, $kat, $remark;
    if (!$overwrite) {
        die(__('A file with this name already exists!').
            "! <br /><a href='./filemanager.php?mode=forms&acc=$acc&kat=$kat&remark$remark$sid'>".
            __('back')."</a>");
    }
}


function check_owner() {
    global $row, $user_ID, $sid, $acc, $kat, $remark;
    if ($row[2] <> $user_ID) {
        die(__('You are not allowed to overwrite this file since somebody else uploaded it').
            " <br /><a href='./filemanager.php?mode=forms&acc=$acc&kat=$kat&remark=$remark$sid'>".
            __('back')."</a>");
    }
}


// delete records from database
function delete_record($ID) {
    // fetch file name etc.
    $result = db_query("SELECT ID, filename, tempname, typ, filesize
                          FROM ".DB_PREFIX."dateien
                         WHERE ID = ".(int)$ID) or db_die();
    $row = db_fetch_row($result);

    // unlink file
    if ($row[4] > 0) @unlink(PHPR_FILE_PATH."/$row[2]");

    // delete record in db
    $result2 = db_query("DELETE
                           FROM ".DB_PREFIX."dateien
                          WHERE ID = ".(int)$ID) or db_die();
                               
    // delete corresponding entry from db_record
	remove_link($ID, 'filemanager');
                              
    // delete history
    if (PHPR_HISTORY_LOG  > 0) history_delete('dateien', $ID);
     // delete file history log
     file_history_delete($ID);
    
    if ($row[3] == "d" or $row[3] == "fv") del($row[0]); // look for files in the subdirectory
    $action = "";
}


// delete subdirectories
function del($ID) {
    $result = db_query("SELECT ID, filename, tempname, typ, filesize
                          FROM ".DB_PREFIX."dateien
                         WHERE parent = ".(int)$ID) or db_die();
    while ($row = db_fetch_row($result)) {
        // only delete file when it is not a link
        if ($row[4] > 0) unlink(PHPR_FILE_PATH."/$row[2]");
        // delete record as such
        $result2 = db_query("DELETE
                               FROM ".DB_PREFIX."dateien
                              WHERE ID = ".(int)$row[0]) or db_die();
        remove_link($ID, 'filemanager');
        if ($row[3] == "d") del($row[0]); // look for files/links etc. in the subdirectory
    }
}


// check for same record name
function check_name() {
    global $sql_user_group, $ID, $filename, $typ, $sid;
    $result = db_query("SELECT ID, filename, typ
                          FROM ".DB_PREFIX."dateien") or db_die();
    while ($row = db_fetch_row($result)) {
        if ($row[0] <> $ID and $row[1] == $filename and ereg("f", $row[2])) {
            die(__('This name already exists')."! <br /><a href='filemanager.php?mode=forms$sid'>".__('back')."</a>");
        }
    }
}


// assign category
function assign_cat() {
    global $kat, $new_category;
    // if no manual category is given, use the one from the select box
    if (isset($new_category) && strlen($new_category) > 0) $kat = $new_category;
}


// check whether it's really an upload
function check_upload($userfile) {
    if (!is_uploaded_file($userfile)) die("Oops, the uploaded file is not in the upload directory!");
}


// this routine sends out an email notification to all users of the group which have access to the file
function notify_members($user_ID, $user_group) {
    global $acc, $filename, $userfile_name;

    // if the object is a file, assign the value to $filename. For links and directories the value is already in $filename
    if ($userfile_name) $filename = $userfile_name;

    // record free for all users of this group?
    if ($acc == "group") $acc = "all";
    // or personal?  -> end this routine
    else if ($acc == "private") return 1;

    // include the library from lib
    include_once(LIB_PATH."/notification.inc.php");
    // call routine to send mails with notification about the new record
    // email_notification(__('Files'), $acc, $filename);
    $notify = new Notification($user_ID, $user_group, "Filemanager", $acc,
                               "&mode=forms&ID=",
                               $userfile_name);
    // set a specific title (Different from default)
    $notify->notify();
}

// the locking status needs to be defined
function define_locking_status($ID) {
    // $lock and $unlock are the respective values from the form
    global $lock, $unlock, $user_ID, $error;

    // fetch value from db
    $result = db_query("SELECT von, lock_user, typ, acc, acc_write
                          FROM ".DB_PREFIX."dateien
                         WHERE ID = ".(int)$ID) or db_die();
    $row = db_fetch_row($result);
    // checkbox 'lock this field' has been selected by the user
    if ($lock == 'true') {
        // simply return the user_ID of this user
        if ($row[1] > 0 or ($row[2] != 'f')) {
            $error = 1;
            return 0;
        }
        else return $user_ID;
    }
    else if ($unlock == 'true') {
    // first check whether this user has the right to unlock the file
        if ($row[0] <> $user_ID and $row[1] <> $user_ID) {
          $error=1;
        }
        // now unlock the file by deleting the old value in this db field
        return 0;
    }
    // no action at all? return the old value from the db record ...
    else return $row[1];
}



function set_new_subdir($parent, $new_sub_dir) {
    global $user_ID, $dbTSnull, $user_group,$module;
    $accessstring=insert_access($module);

    $result = db_query("INSERT INTO ".DB_PREFIX."dateien
                               (        von      ,        filename       ,  acc             ,  datum    ,        gruppe      ,typ,        parent  , acc_write        ,         remark              )
                        VALUES (".(int)$user_ID.",'".xss($new_sub_dir)."','$accessstring[0]','$dbTSnull',".(int)$user_group.",'d',".(int)$parent.",'$accessstring[1]', '".xss($new_sub_dir)."'     )") or db_die();
    // fetch ID from new record
    $result = db_query("SELECT ID
                          FROM ".DB_PREFIX."dateien
                         WHERE filename='".xss($new_sub_dir)."'
                           AND datum = '$dbTSnull'") or db_die();
    $row = db_fetch_row($result);
    return $row[0];
}
 function prepare_userfile_for_copy($ID){
    $result = db_query("SELECT tempname,userfile,filesize
                          FROM ".DB_PREFIX."dateien
                         WHERE ID = ".(int)$ID) or db_die();
    $row = db_fetch_row($result);
    $_FILES['userfile']['tmp_name']=PHPR_FILE_PATH.'/'.$row[0];
    $_FILES['userfile']['name']==__('copy')." ".$row[1];
    $_FILES['userfile']['size']=$row[2];

 }
// extra routine: notify colleagues about the new record
// FIXME is $notify set anywhere?
if (PHPR_FILEMANAGER_NOTIFY and $notify) notify_members($user_ID, $user_group);
if ($copy){
   include_once("./filemanager_forms.php");
}
else if (!$justform) {
    $mode = 'view';
    include_once("./filemanager_view.php");
}
else {
    echo '<script type="text/javascript">ReloadParentAndClose();</script>';
}

?>
