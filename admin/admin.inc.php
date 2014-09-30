<?php
/**
 * resource script with library functions for admin.php
 *
 * @package    admin
 * @subpackage main
 * @author     Albrecht Guenther, $Author: gustavo $
 * @licence    	GPL, see www.gnu.org/copyleft/gpl.html
 * @copyright  2000-2006 Mayflower GmbH www.mayflower.de
 * @version    $Id: admin.inc.php,v 1.15 2007-05-31 08:10:09 gustavo Exp $
 */
if (!defined('lib_included')) die('Please use index.php!');


// subroutine for deleting subdirectories, taken from filemanager.php
function del($delete_ID) {
    $result = db_query("SELECT ID, filename, tempname, typ, filesize
                          FROM ".DB_PREFIX."dateien
                         WHERE div1 = '$delete_ID'
                           AND is_deleted is NULL") or db_die();
    while ($row = db_fetch_row($result)) {
        // only delete file when it is not a link
        if ($row[4] > 0) {
            $path = PHPR_FILE_PATH."/$row[2]";
            unlink($path);
        }
        delete_record_id("dateien","WHERE ID = ".(int)$row[0]);
        if ($row[3] == 'd') {
            del($row[0]); // look for files/links etc. in the subdirectory
        }
    }
}

// subroutine to delete all comments to a posting
function delete_comments($ID) {
    $result = db_query("SELECT ID
                          FROM ".DB_PREFIX."forum
                         WHERE antwort = ".(int)$ID."
                           AND is_deleted is NULL") or db_die();
    while ($row = db_fetch_row($result)) {
        delete_comments($row[0]);
        delete_record_id('forum',"WHERE ID = ".(int)$row[0]);
    }
}

// display the option 0 = no access, 1 = read, 2 = write for the roles
function role1($module, $roles_ID) {
    global $acc_level;
    $module_ID=get_application_module_ID($module);
    // check the db only if it is in the modify mode
    if ($roles_ID > 0) {
        $result = db_query("SELECT access
                              FROM ".DB_PREFIX."module_role_rel
                              WHERE role_ID = ".(int)$roles_ID." and module_ID = ".(int)$module_ID) or db_die();
        $row = db_fetch_row($result);
        $string = "<select name='module_acc[$module_ID]'>\n";
        foreach ($acc_level as $acc1 => $acc2) {
            $string .= "<option value='$acc1'";
            if ($row[0] == $acc1) $string .= ' selected="selected"';
            $string .= ">$acc2</option>\n";
        }
        $string .= "</select>\n";
    }
    else {
        $string =  "<select name='module_acc[$module_ID]'>\n";
        foreach ($acc_level as $acc1 => $acc2) {
            $string .= "<option value='$acc1'>$acc2</option>\n";
        }
        $string .= "</select>\n";
    }
    return $string;
}

/**
 * This function displays the rolkes
 *
 * @version 1
 * @author Nina Schmitt
 * @param int $ID
 * @return string $context_output
 */
function render_roles($ID='',$name='',$comment=''){
    
    $context_output = "<form action='admin.php' method='post'><fieldset><legend>".__('Roles')."</legend>\n";
    
    if($ID=='') $hidden_fields = array( "action1"   => "roles",
                                        "anlegen"   => "neu_anlegen");
    else  $hidden_fields = array( "action1"   => "roles",
                                  "anlegen"   => "aendern",
                                  "roles_ID"  => $ID);
    
    $context_output .= hidden_fields($hidden_fields)."\n";
    // title of the role
    $context_output .= "<label class='label_block' for='title'>".__('Name').":</label><input type=text name=title maxlength=30 value='$name'/><br />\n";
    // remark
    $context_output .= "<label class='label_block' for='remark'>".__('Comment').":</label><textarea name=remark>$comment</textarea><br />\n";

    $all_modules = $_SESSION['main_modules_data'];

    $sub_nav = $_SESSION['sub_nav'];

    $sub_modules = $_SESSION['sub_modules'];
    
    $view = $_SESSION['view_data'];
    
    $forms = $_SESSION['forms_data'];
    
    $context_output .= "<fieldset><legend>".__('Modules')."</legend>\n";

    foreach($all_modules as $mod_name=>$module){

        $context_output .= "<label class='label_block' for='$mod_name'>".enable_vars($module[1])."</label>".role1($mod_name, $ID)."<br />\n";

        //2nd row
        $mod_ID=get_application_module_ID($mod_name);
        if (is_array($forms[$mod_ID]))foreach ($forms[$mod_ID] as $form_name =>$form_data){

            $context_output .= "<label class='label_block' style='width: 180px'; for='$form_name'>".enable_vars($form_data[1])."</label>".role1($form_name, $ID)."<br />\n";
        } 
        if (is_array($view[$mod_ID]))foreach ($view[$mod_ID] as $view_name =>$view_data){

            $context_output .= "<label class='label_block' style='width: 180px'; for='$view_name'>".enable_vars($view_data[1])."</label>".role1($view_name, $ID)."<br />\n";
        }

        //2nd row
        if (is_array($sub_nav[$mod_name]))foreach ($sub_nav[$mod_name] as $sub_name =>$sub){

            $context_output .= "<label class='label_block' style='width: 180px' for='$sub_name'>".enable_vars($sub[1])."</label>".role1($sub_name, $ID)."<br />\n";

            //3rd row

            $sub_ID=get_application_module_ID($sub_name);
            if (is_array($forms[$sub_ID]))foreach ($forms[$sub_ID] as $form_name =>$form_data){

            $context_output .= "<label class='label_block' style='width: 180px'; for='$form_name'>".enable_vars($form_data[1])."</label>".role1($form_name, $ID)."<br />\n";
            } 

            if (is_array($view[$sub_ID]))foreach ($view[$sub_ID] as $view_name =>$view_data){

                $context_output .= "<label class='label_block' style='width:200px;' for='$view_name'>".enable_vars($view_data[1])."</label>".role1($view_name, $ID)."<br />\n";
            }

        }

    }

    $context_output .= "</fieldset>";
    
    $context_output .= "<input type='submit' class='button' value='".__('OK')."' />\n";
    
    $context_output .= "</fieldset></form>\n";
    
    return $context_output;
}

function delete_roles_rel($roles_ID){
    $result = db_query("DELETE FROM ".DB_PREFIX."module_role_rel
                               WHERE role_ID = ".(int)$roles_ID) or db_die();
}
function insert_roles_rel($roles_ID, $module_acc){
    foreach ($module_acc as $module_ID => $access){
        $query= "INSERT INTO ".DB_PREFIX."module_role_rel
                        (von,role_ID,module_ID,access)
                         VALUES(".(int)$user_ID.", ".(int)$roles_ID.", ".(int)$module_ID.", ".(int)$access.")";
        $result= db_query($query) or db_die();
    }
}
?>
