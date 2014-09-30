<?php
/**
* filemanager download script
*
* @package    filemanager
* @module     main
* @author     Albrecht Guenther, $Author: gustavo $
* @licence    GPL, see www.gnu.org/copyleft/gpl.html
* @copyright  2000-2006 Mayflower GmbH www.mayflower.de
* @version    $Id: filemanager_down.php,v 1.33.2.1 2007/07/02 12:25:01 gustavo Exp $
*/
define('PATH_PRE','../');
include_once(PATH_PRE.'lib/lib.inc.php');
include_once(PATH_PRE.'lib/dbman_lib.inc.php');

// check_role
if (check_role("filemanager") < 1) die("You are not allowed to do this!");

$module = 'filemanager';
$ID    = xss($ID);
$mode  = xss($mode);
$mode2 = xss($mode2);

if (eregi("xxx", $ID)) $ID = substr($ID, 14);
else                   $ID = (int) $ID;
unset($tempname);
unset($name);
// fetch values from db

//check is its a file from datei_history
if($history==true){
    $query = "SELECT parent, tempname, userfile FROM ".DB_PREFIX."datei_history WHERE ID = ".(int)$ID;
    $result = db_query($query);
    $row_history = db_fetch_row($result);
    $tempname = $row_history[1];
    $name = $row_history[2];
    $ID = $row_history[0];
}

$result = db_query("select ID, userfile, acc, tempname, typ, div1, pw, lock_user,filename
                      from ".DB_PREFIX."dateien
                     where ID = ".(int)$ID." and (acc like 'system' or ((von = ".(int)$user_ID." or acc like 'group' or acc like '%\"$user_kurz\"%') ".group_string($module)."))") or db_die();
$row = db_fetch_row($result);
// check privilege
if (!$row[0]) die("You are not allowed to do this");

if(!isset($tempname))$tempname=$row[3];
if(!isset($name))$name=$row[1];
if (!$name) $name = $row[8];
$name = ereg_replace("§", " ", $name);



switch (true) {
    // filename is cryptted
    case ($row[6] <> '' and !$pw):
        $hidden_fields = array ( "ID"       => $ID,
                                 "mode"     => $mode,
                                 "mode2"    => $mode2);
        echo __('One password should be given (file is encrypted)').": ";
        echo "
<form>
			<input type='password' name='pw' />
    ".hidden_fields($hidden_fields)."
</form>
";
        exit;
        break;

    // pw is set
    case ($pw <> ''):
        $encryptstring = encrypt($pw, $pw);
        if ($encryptstring <> $row[6]) die("<b>".__('Passwords dont match!')."!</b>");
        $path = PHPR_FILE_PATH."/".$row[3];
        break;

    // the file is locked by another user
    case (($row[7] > '0') and ($row[7] != $user_ID)):
        die("Sorry but this file locked by ".slookup('users', 'nachname,vorname', 'ID', $row[7],'1'));
        break;

    // link
    case ($row[4] == 'l'):
        $filelink = (eregi("://", $row[3])) ? $row[3] : "file://".$row[3];
        header("Location:".$filelink);
        exit;

    // case directory
    case ($row[4] == 'd'):
        die("You cannot download a directory :-)");
        break;

    // case normal file
    default:
        $path = PHPR_FILE_PATH."/".$tempname;
}


if (!file_exists($path)) {
    sysadmin_alert('Filemanager download: specified file not found:'.$path,'PHProjekt: Error on file download');
    die("Panic! specified file not found ...");
}

// include content type definition
include_once(LIB_PATH."/get_contenttype.inc.php");

// Send file contents, decrypt if needed

if (!$encryptstring) {
    // Just output the file
    readfile($path);
}
else {
    // first create an appropiate string:
    //1. crypt the password,
    //$encryptstring = encrypt($encryptstring, $encryptstring);
    $bytes = 65536;
    // 2: string must be longer than the content piece
    for ($i=0; $i <= floor($bytes/strlen($encryptstring)); $i++) {
        $encryptstringnew .= $encryptstring;
    }
    // open the file
    $file = fopen($path, "rb");
    while($line = fread($file, $bytes)) {
        // shift the content back ...
        $line2 = $encryptstringnew ^ $line ;
        // output
        echo $line2;
    }
}

?>
