<?php
/**
 * Manage form data functions
 *
 * @package    	lib
 * @subpackage 	main
 * @author     	Albrecht Guenther, $Author: polidor $
 * @licence     GPL, see www.gnu.org/copyleft/gpl.html
 * @copyright  	2000-2006 Mayflower GmbH www.mayflower.de
 * @version    	$Id: dbman_data.inc.php,v 1.61 2008-03-10 14:57:25 polidor Exp $
 */

// check whether the lib has been included - authentication!
if (!defined('lib_included')) die('Please use index.php!');

// *************
// db operations
// *************

/**
 * Prepare values for storage in database
 * Create query
 *
 * @global sql_fieldstring, sql_valuestring
 * @param void
 * @return void
 */
function sqlstrings_create() {
    global $fields, $sql_fieldstring, $sql_valuestring, $dbTSnull, $user_ID, $date_format_object, $module;

    // field counter
    $i = 0;

    foreach ($fields as $field_name => $field) {
        
        // getting the tab name. Probably it is not set (e.g. all modules without tabs)
        if (isset($field['form_tab']) && $field['form_tab'] > 0) {
            $tab_name = slookup('modules','index_name','ID',$field['form_tab']);
        }
        else {
            // the default tab_name name
            $tab_name = 'default';
        }
        
        // If the user has not permission to access the tab, then it hasn't permission to edit the field values
        if(check_role($tab_name) > 1 || $tab_name == 'default') {
                
            $post_value = $_POST[$field_name];
            if (PHPR_SUPPORT_HTML and (isset($module) && isset($_SESSION['show_html_editor']["$module"]) && $_SESSION['show_html_editor']["$module"] == 1) and $field['form_type'] == 'textarea') {
           		$post_value = xss_purifier($post_value);
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
                $sql_value = date('Y-m-d')." ".slookup('users','nachname','ID',$user_ID,'1').": ".xss_purifier($post_value);
    	    }
            else if ($field['form_type'] == 'time') {
                $hour   = $field_name.'_hour';
                $minute = $field_name.'_minute';
                if (strlen($_POST["$hour"])   == 1) $_POST["$hour"]   = '0'.$_POST["$hour"];
                if (strlen($_POST["$minute"]) == 1) $_POST["$minute"] = '0'.$_POST["$minute"];
                $sql_value = strip_tags($_POST["$hour"]).':'.strip_tags($_POST["$minute"]);
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

/**
 * Prepare values for storage in database
 * Update query
 *
 * @global sql_fieldstring, sql_valuestring
 * @param void
 * @return void
 */
function sqlstrings_modify() {
    global $fields, $dbTSnull, $user_ID, $ID, $module,$date_format_object,$module;

    foreach ($fields as $field_name => $field) {
        
        // getting the tab name. Probably it is not set (e.g. all modules without tabs)
        if (isset($field['form_tab']) && $field['form_tab'] > 0) {
            $tab_name = slookup('modules','index_name','ID',$field['form_tab'],1);
        }
        else {
            // the default tab_name name
            $tab_name = 'default';
        }
        
        // If the user has not permission to access the tab, then it hasn't permission to edit the field values
        if(check_role($tab_name) > 1 || $tab_name == 'default') {
        
            $post_value = $_POST[$field_name];
            if (PHPR_SUPPORT_HTML and (isset($module) && isset($_SESSION['show_html_editor']["$module"]) && $_SESSION['show_html_editor']["$module"] == 1) and $field['form_type'] == 'textarea') {
           		$post_value = xss_purifier($post_value);
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
                                                         date('Y-m-d')." ".slookup('users','nachname','ID',$user_ID,'1').": ".xss_purifier($post_value)."',";
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
    }
    return $sql_string;
}

/**
 * Return string with results for 'select multiple'
 *
 * @param string $field 	- The field name
 * @return string       		Array data implode with |
 */
function store_select_multiple($field) {
    if ($_POST[$field] <> '') return implode('|', xss_array($_POST[$field]));
}

/**
 * Upload a file
 *
 * @param string $field_name 	- The field name
 * @return string            			"field_name | tempname"
 */
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


/**
 * Delete a file
 *
 * @param string 	$field_name 	- The field name
 * @param int    		$ID         		- Record ID
 * @param string 	$module     	- Module name
 * @return void
 */
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

/**
 * Modify a file
 *
 * @param string 	$field_name 		- The field name
 * @param int    		$ID         			- Record ID
 * @param string 	$module     		- Module name
 * @return string            				"field_name | tempname"
 */
function upload_file_modify($field_name, $ID, $module) {
    // 1. step: delete the old file
    upload_file_delete($field_name, $ID, $module);
    // 2. step
    return upload_file_create($field_name);
}

/**
 * Delete a file attached to a record but leave the record as it is
 *
 * @param string 	$field_name 	- The field name
 * @param int    		$ID         		- Record ID
 * @param string 	$module     	- Module name
 * @return void
 */
function delete_attached_file ($field_name, $ID, $module) {
    global $tablename;

    // 1. step: unlink the file
    upload_file_delete($field_name, $ID, $module);
    // 2. step: update record
    $result = db_query("UPDATE ".qss(DB_PREFIX.$tablename[$module])."
                           SET ".qss($field_name)." = ''
                         WHERE ID = ".(int)$ID) or db_die();
}

/**
 * Delete an array of records
 *
 * @param array    	$ID         			- Array of records ID
 * @param string 	$module       		- Module name
 * @param boolean 	$delete_tree 	- Delete all the childrens?
 * @return void
 */
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

/**
 * Check if the user can delete a record
 *
 * @param array    	$ID         	- Record ID
 * @param string 	$module   - Module name
 * @return boolean             		Can delete it?
 */
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

/**
 * Check if the user can delete a children record
 *
 * @param array  	$ID        	- Record ID
 * @param string 	$module  	- Module name
 * @return boolean             		Can delete it?
 */
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

/**
 * Check the permision of the record
 *
 * @param array  	$ID            		- Record ID
 * @param string 	$module        		- Module name
 * @param string 	$acc_fieldname 	- Name of the field acc
 * @param boolean   $check_group    - Enables check object vs user group
 * @return misc                 				0/owner/write/read
 */
function check_perm_modify($ID, $module, $acc_fieldname='acc', $check_group = true) {
    global $user_ID, $user_kurz, $tablename, $sql_user_group;

    $acc_fieldname = qss($acc_fieldname);
    
    if ($check_group) {
        $group_where = " AND $sql_user_group ";
    }
    else {
        $group_where = '';
    }

    // check permission
    $result = db_query("SELECT ID, von, acc_write
                          FROM ".qss(DB_PREFIX.$tablename[$module])."
                         WHERE ID = ".(int)$ID."
                           AND (".$acc_fieldname." LIKE 'system'
                                OR ((von = ".(int)$user_ID."
                                     OR ".$acc_fieldname." LIKE 'group'
                                     OR ".$acc_fieldname." LIKE '%\"$user_kurz\"%')
                                    $group_where))") or db_die();
    $row = db_fetch_row($result);
    if (!$row[0]) return 0;
    else if ($row[1] == $user_ID) return 'owner';
    else if ($row[2] == 'w') return 'write';
    else return 'read';
}

/**
 * Update the permision of a record
 *
 * @param string 	$table                 		- Table tu use
 * @param int    		$owner_ID              		- ID of the owner of the record
 * @param string 	$acc_read_fieldname  	- Name of the field acc_read
 * @return string                       				Sql sentence for acc_read and acc_write
 */
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

/**
 * Insert the permision of a record
 *
 * @param string table    	- Table tu use
 * @return string          		Sql sentence for acc_read and acc_write
 */
function insert_access($table) {

    include_once(LIB_PATH.'/access.inc.php');

    // set read string
    $accessstring[0]= assign_acc(xss($_POST['acc']), $table);

    //set write string
    if ($_POST['acc_write'] <> '') $accessstring[1] = 'w';
    else $accessstring[1] = '';

    return $accessstring;
}

/**
 * Delete a specific ID from a table
 * Use the config PHPR_SOFT_DELETE = 1 to make a soft delete
 * or delete the record forever
 *
 * @param string 	$table 	- Table of the record
 * @param string 	$where 	- WHERE clause
 * @return void
 */
function delete_record_id($table,$where) {
    if (PHPR_SOFT_DELETE) {
        $result = db_query("UPDATE ".DB_PREFIX.$table."
                               SET is_deleted = 1
                             ".$where) or db_die();
    } else {
        $result = db_query("DELETE FROM ".DB_PREFIX.$table."
                             ".$where) or db_die();
    }
}
?>
