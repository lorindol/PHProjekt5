<?php
/**
* resource script with library functions for admin.php
*
* @package    admin
* @module     main
* @author     Albrecht Guenther, $Author: gustavo $
* @licence    GPL, see www.gnu.org/copyleft/gpl.html
* @copyright  2000-2006 Mayflower GmbH www.mayflower.de
* @version    $Id: admin.inc.php,v 1.11 2006/10/26 20:00:27 gustavo Exp $
*/
if (!defined('lib_included')) die('Please use index.php!');


// subroutine for deleting subdirectories, taken from filemanager.php
function del($delete_ID) {
    $result = db_query("SELECT ID, filename, tempname, typ, filesize
                          FROM ".DB_PREFIX."dateien
                         WHERE div1 = '$delete_ID'") or db_die();
    while ($row = db_fetch_row($result)) {
        // only delete file when it is not a link
        if ($row[4] > 0) {
            $path = PHPR_FILE_PATH."/$row[2]";
            unlink($path);
        }
        $result2 = db_query("DELETE FROM ".DB_PREFIX."dateien
                                   WHERE ID = ".(int)$row[0]) or db_die();
        if ($row[3] == 'd') {
            del($row[0]); // look for files/links etc. in the subdirectory
        }
    }
}

// subroutine to delete all comments to a posting
function delete_comments($ID) {
    $result = db_query("SELECT ID
                          FROM ".DB_PREFIX."forum
                         WHERE antwort = ".(int)$ID) or db_die();
    while ($row = db_fetch_row($result)) {
        delete_comments($row[0]);
        $result2 = db_query("DELETE FROM ".DB_PREFIX."forum
                                   WHERE ID = ".(int)$row[0]) or db_die();
    }
}

// display the option 0 = no access, 1 = read, 2 = write for the roles
function role1($module) {
    global $acc_level, $roles_ID;

    // check the db only if it is in the modify mode
    if ($roles_ID > 0) {
        $result = db_query("SELECT ".qss($module)."
                              FROM ".DB_PREFIX."roles
                             WHERE ID = ".(int)$roles_ID) or db_die();
        $row = db_fetch_row($result);
        $string = "<select class='halfsize' name='".$module."_m'>\n";
        foreach ($acc_level as $acc1 => $acc2) {
            $string .= "<option value='$acc1'";
            if ($row[0] == $acc1) $string .= ' selected="selected"';
            $string .= ">$acc2</option>\n";
        }
        $string .= "</select>\n";
    }
    else {
        $string =  "<select name='".$module."_m'>\n";
        foreach ($acc_level as $acc1 => $acc2) {
            $string .= "<option value='$acc1'>$acc2</option>\n";
        }
        $string .= "</select>\n";
    }
    return $string;
}
?>
