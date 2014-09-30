<?php

// dbman_data.inc.php - PHProjekt Version 5.2
// copyright  ©  2000-2005 Albrecht Guenther  ag@phprojekt.com
// www.phprojekt.com
// Author: Albrecht Guenther, $Author: nina $
// $Id: dbman_data.inc.php,v 1.45.2.10 2007/05/14 08:41:46 nina Exp $


// check whether the lib has been included - authentication!
if (!defined('lib_included')) die('Please use index.php!');


// *************
// db operations
// *************

// prepare values for storage in database
function sqlstrings_create() {
    global $fields, $sql_fieldstring, $sql_valuestring, $dbTSnull, $user_ID, $date_format_object;

    // field counter
    $i = 0;

    foreach ($fields as $field_name => $field) {
        $post_value = $_POST[$field_name];
        if($field['form_type'] != 'textarea'){
            $post_value = strip_tags($post_value);
        }
        else $post_value = xss($post_value);

        // if the field is an upload form and the user uploaded a file - store the file and return the tempname
        if ($field['form_type'] == 'upload' ) {
            if ($_FILES[$field_name]['tmp_name'] <> '' and $_FILES[$field_name]['tmp_name'] <> 'none') $sql_value = upload_file_create($field_name);
            else $sql_value = '';
        }
        else if ($field['form_type'] == 'select_multiple') {
            $sql_value = store_select_multiple(strip_tags($field_name));
        }
        else if ($field['form_type'] == 'select_category') {
            if ($_POST['new_category'] <> '') $sql_value = strip_tags($_POST['new_category']);
            else $sql_value = $post_value;
        }
        else if ($field['form_type'] == 'timestamp_modify') {
            $sql_value = $dbTSnull;
        }
        else if ($field['form_type'] == 'timestamp_create') {
            $sql_value = $dbTSnull;
        }
        else if ($field['form_type'] == 'authorID') {
            $sql_value = (int)$user_ID;
        }
        // textarea - add remark field. username and date will be stored as well
        else if ($field['form_type'] == 'textarea_add_remark' and $post_value <> '') {
            if ($post_value <> '') {
	            $sql_string .= $field_name." = '".addslashes(slookup($field['tablename'],$field_name,'ID',$ID,'1'))."\n-----------------------------------\n".
                                                     date('Y-m-d')." ".slookup('users','nachname','ID',$user_ID,'1').": ".strip_tags($post_value)."',";
           }
	    }


        else if ($field['form_type'] == 'time') {
            $hour   = $field_name.'_hour';
            $minute = $field_name.'_minute';
            if (strlen($_POST["$hour"])   == 1) $_POST["$hour"]   = '0'.$_POST["$hour"];
            if (strlen($_POST["$minute"]) == 1) $_POST["$minute"] = '0'.$_POST["$minute"];
            $sql_string .= $field_name." = '".strip_tags($_POST["$hour"]).":".strip_tags($_POST["$minute"])."',";
        }
        // Date fields
        else if ($field['form_type'] == 'date') {
            $sql_value = strip_tags($_POST[$field_name]);
            if ($date_format_object->is_user_date($sql_value)) {
                $sql_value = $date_format_object->convert_user2db($sql_value);
            } else {
                $sql_value = '';
            }
             $sql_string .= $field_name." = '".$sql_value."', ";
        }
        // in all other cases simply store the value of the field
        else {
             $sql_value = $post_value;
        }

        // store the name and the value - except for the simple 'display' function
        if ($field['form_type'] <> 'display') {
            $sql_fields[] = $field_name;
            $sql_values[$i]['value'] = $sql_value;
            $sql_values[$i]['field_type'] = $field['field_type'];
            $i++;
        }
    }

    $sql_fieldstring = implode(',', $sql_fields);

    $sql_valuestring = "";

    if (is_array($sql_values) && count($sql_values) > 0) {

        foreach ($sql_values as $tmp_id => $one_value) {

            if ($one_value['field_type'] == 'integer' || $one_value['field_type'] == 'decimal') {
                $sql_valuestring .= (int)$one_value['value'].",";
            }
            else {
                $sql_valuestring .= "'".$one_value['value']."',";
            }

        }

        $sql_valuestring = substr($sql_valuestring,0,-1);
    }

}


function sqlstrings_modify() {
    global $fields, $dbTSnull, $user_ID, $ID, $module,$date_format_object;

    foreach ($fields as $field_name => $field) {
        $post_value = $_POST[$field_name];
        if($field['form_type'] != 'textarea'){
            $post_value = strip_tags($post_value);
        }
        else $post_value = xss($post_value);

        // if the field is an upload form and the user uploaded a file - store the file and return the tempname
        if ($field['form_type'] == 'upload') {
            if ($_FILES[$field_name]['tmp_name'] <> '' and $_FILES[$field_name]['tmp_name'] <> 'none') {
                $sql_string .= $field_name." = '".upload_file_modify($field_name, $ID, $module)."',";
            }
        }
        // fields with value 'display' will not be filled
        else if (in_array($field['form_type'], array('display', 'user_show', 'contact_create', 'authorID'))) {}
        else if ($field['form_type'] == 'select_multiple') {
            $sql_string .= $field_name." = '".store_select_multiple($field_name)."',";
        }
        else if ($field['form_type'] == 'select_category') {
            if ($_POST['new_category'] <> '') $sql_string .= $field_name." = '".strip_tags($_POST['new_category'])."',";
            else $sql_string .= $field_name." = '".$post_value."',";
        }
        // skip any field with type = create since this value shouldn't be touched anymore
        else if (eregi('create',$field['form_type'])) {}

        else if (eregi('userID_access',$field['form_type'])) {
            if (slookup($field['tablename'],$field['form_select'],'ID',$ID,'1') == $user_ID) {
                $sql_string .= $field_name." = '".strip_tags($post_value)."',";
            }
            else {}
        }

	    // textarea - add remark field. username and date will be stored as well
        else if ($field['form_type'] == 'textarea_add_remark') {
            if ($post_value <> '') {
	            $sql_string .= $field_name." = '".addslashes(slookup($field['tablename'],$field_name,'ID',$ID,'1'))."\n-----------------------------------\n".
                                                     date('Y-m-d')." ".slookup('users','nachname','ID',$user_ID,'1').": ".xss($post_value)."',";
           }
	    }

        // timestamp ...
        else if ($field['form_type'] == 'timestamp_modify') {
            $sql_string .= $field_name." = '".$dbTSnull."',";
        }
        else if ($field['form_type'] == 'time') {
            $hour = $field_name.'_hour';
            $minute = $field_name.'_minute';
            if (strlen($_POST["$hour"]) == 1) $_POST["$hour"] = '0'.$_POST["$hour"];
            if (strlen($_POST["$minute"]) == 1) $_POST["$minute"] = '0'.$_POST["$minute"];
            $sql_string .= $field_name." = '".strip_tags($_POST["$hour"]).":".strip_tags($_POST["$minute"])."',";
        }
        // Date fields

        else if ($field['form_type'] == 'date') {
            $sql_value = strip_tags($_POST[$field_name]);
            if ($date_format_object->is_user_date($sql_value)) {
                $sql_value = $date_format_object->convert_user2db($sql_value);
            } else {
                $sql_value = '';
            }
            $sql_string .= $field_name."= '".$sql_value."',";
        }
        // in all other cases simply store the value of the field
        else {

            if ($field['field_type'] == 'integer' || $field['field_type'] == 'decimal') {
                $sql_string .= $field_name." = ".(int)$post_value.",";
            }
            else {
                $sql_string .= $field_name." = '".$post_value."',";
            }

        }

    }
    return $sql_string;
}


// return string with results for 'select multiple'
function store_select_multiple($field) {
    if ($_POST[$field] <> '') return implode('|', xss_array($_POST[$field]));
}


// ***********
// functions to upload, modify and delete a file in the form
function upload_file_create($field_name) {
    global $module;
    //first check if upload exists
    $upload='';
    if (!isset($_FILES[$field_name]['error'])) return '';
    switch ($_FILES[$field_name]['error']){
        case 0:
            // add extension to random name

            $tempname = rnd_string().strstr(xss($_FILES[$field_name]['name']), '.');
            if (copy(xss($_FILES[$field_name]['tmp_name']), PATH_PRE.PHPR_DOC_PATH.'/'.$tempname)){
                // since only one field as available we have to store both values together
                $upload= addslashes($_FILES[$field_name]['name']).'|'.$tempname;
            }
            else {
                // upload interrupted
                message_stack_in(__('Upload has been interrupted'), $module, 'error');
            }
            break;
        case 1:
        case 2:
            // upload filesize too big
            message_stack_in(__('Upload file size is too big'), $module, 'error');
            break;
        case 3:
            // upload interrupted
            message_stack_in(__('Upload has been interrupted'), $module, 'error');
            break;
        case 4:
            // no file uploaded -> nothing to do
            break;
    }
    return $upload;

}


function upload_file_delete($field_name, $ID, $module) {
    global $tablename;

    // fetch tempname from db
    $result = db_query("SELECT ".qss($field_name)."
                          FROM ".qss(DB_PREFIX.$tablename[$module])."
                         WHERE ID = ".(int)$ID) or db_die();
    $row = db_fetch_row($result);
    // is there any previous file listed?
    if ($row[0] <> '') {
        list(,$t2) = explode('|', $row[0]);
        if (is_file('../'.PHPR_DOC_PATH.'/'.$t2)) unlink('../'.PHPR_DOC_PATH.'/'.$t2);
    }
}


function upload_file_modify($field_name, $ID, $module) {
    // 1. step: delete the old file
    upload_file_delete($field_name, $ID, $module);
    // 2. step
    return upload_file_create($field_name);
}


// delete a file attached to a record but leave the record as it is
function delete_attached_file ($field_name, $ID, $module) {
    global $tablename;

    // 1. step: unlink the file
    upload_file_delete($field_name, $ID, $module);
    // 2. step: update record
    $result = db_query("UPDATE ".qss(DB_PREFIX.$tablename[$module])."
                           SET ".qss($field_name)." = ''
                         WHERE ID = ".(int)$ID) or db_die();
}

function manage_delete_records($ID, $module, $delete_tree=false) {
    global $tablename;

    $arr_ID = explode(',', $ID);
    foreach ($arr_ID as $ID) {
        $error = false;
        if (!check_perm_delete($ID, $module)) {
            $err_msg[] = 'No permission for record ID '.$ID;
            message_stack_in(__('No permission for record ID ').$ID, $module,"error");
            $error = true;
        }
        if (check_children($ID, $module) == true and $delete_tree == false) {
            $err_msg[] = 'Record with ID '.$ID.' still has subelements!';
            message_stack_in(__('Record has still subelements ').$ID, $module,"error");
            $error = true;
        }
        if (!$error) delete_record($ID);
    }
}


function check_perm_delete($ID, $module) {
    global $user_type, $user_ID, $tablename;

    if ($module == 'links') {
        $id_field = 't_ID';
        $author   = 't_author';
    }
    else {
        $id_field = 'ID';
        $author   = 'von';
    }
    if ($user_type==3 or slookup($tablename[$module],$author,$id_field,$ID,'1') == $user_ID ) return true;
    else return false;
}


function check_children($ID, $module) {
    global $tablename;

    if ($module == 'links') {
        $id_field = 't_ID';
        $parent   = 't_parent';
    }
    else {
        $id_field = 'ID';
        $parent   = 'parent';
    }
    if (slookup($tablename[$module],$id_field,$parent,$ID,'1') > 0) return true;
    else return false;
}


function check_perm_modify($ID, $module, $acc_fieldname='acc') {
    global $user_ID, $user_kurz, $tablename, $sql_user_group;

    $acc_fieldname = qss($acc_fieldname);

    // check permission
    $result = db_query("SELECT ID, von, acc_write
                          FROM ".qss(DB_PREFIX.$tablename[$module])."
                         WHERE ID = ".(int)$ID."
                           AND (".$acc_fieldname." LIKE 'system'
                                OR ((von = ".(int)$user_ID."
                                     OR ".$acc_fieldname." LIKE 'group'
                                     OR ".$acc_fieldname." LIKE '%\"$user_kurz\"%')
                                    AND $sql_user_group))") or db_die();
    $row = db_fetch_row($result);
    if (!$row[0]) return 0;
    else if ($row[1] == $user_ID) return 'owner';
    else if ($row[2] == 'w') return 'write';
    else return 'read';
}


function update_access($table, $owner_ID, $acc_read_fieldname='acc') {
    global $user_ID;
    include_once(LIB_PATH.'/access.inc.php');

    if ($owner_ID == $user_ID or PHPR_ALTER_ACC == 1) {

        // set read string
         $accessstring = $acc_read_fieldname." = '".assign_acc(xss($_POST['acc']), $table)."',";

        // set write string
        if ($_POST['acc_write'] <> '') $accessstring .= "acc_write = 'w',";
        else $accessstring .= "acc_write = '',";
        return $accessstring;
    }
    else return '';
}


function insert_access($table) {

    include_once(LIB_PATH.'/access.inc.php');

    // set read string
    $accessstring[0]= assign_acc(xss($_POST['acc']), $table);

    //set write string
    if ($_POST['acc_write'] <> '') $accessstring[1] = 'w';
    else $accessstring[1] = '';

    return $accessstring;
}

?>
