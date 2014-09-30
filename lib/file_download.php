<?php
/**
 * Download function
 *
 * @package    	lib
 * @subpackage 	main
 * @author     	Nina, $Author: gustavo $
 * @licence     GPL, see www.gnu.org/copyleft/gpl.html
 * @copyright  	2000-2006 Mayflower GmbH www.mayflower.de
 * @version    	$Id:
 */

define('PATH_PRE','../');
include_once(PATH_PRE.'lib/lib.inc.php');
download_attached_file($download_attached_file, $module);

/**
 * Download a file using a random value stored on $file_ID array
 *
 * @param string 	$rnd 		- Random string used to find the file inside $file_ID array
 * @param string 	$module 	- Module name where the file was requested (unused)
 * @return void
 */
function download_attached_file($rnd, $module='') {
    global $name, $file_ID;

    $arr = explode('|', $file_ID[$rnd]);

    // Prevent escaping from the attach dir
    if ((ereg('/', $arr[1])) or (ereg('^\.+$', $arr[1]))) die("You are not allowed to do this!");

    // assign the filename
    $name = $arr[0];

    // have a look whether this file exists
    if (!file_exists(PATH_PRE.PHPR_DOC_PATH.'/'.$arr[1])) die("panic! specified file not found ...");

    // include content type definition
    include_once(LIB_PATH.'/get_contenttype.inc.php');

    // stream the file
    readfile(PATH_PRE.PHPR_DOC_PATH.'/'.$arr[1]);
}
?>
