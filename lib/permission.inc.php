<?php
/**
 * Check permissions functions
 *
 * @package    	lib
 * @subpackage 	main
 * @author     	Albrecht Guenther, $Author: gustavo $
 * @licence     GPL, see www.gnu.org/copyleft/gpl.html
 * @copyright  	2000-2006 Mayflower GmbH www.mayflower.de
 * @version    	$Id: permission.inc.php,v 1.11 2007-05-31 08:11:53 gustavo Exp $
 */

// check whether lib.inc.php has been included
if (!defined('lib_included')) die('Please use index.php!');

/**
 * Check permission
 *
 * @param string 	$table  	- Associated table
 * @param string 	$author 	- Field name
 * @param int 		$ID        	- ID of record
 * @return void
 */
function check_permission($table, $author, $ID) {
    global $user_ID;

    $result = db_query("SELECT ".qss($author)."
                          FROM ".qss(DB_PREFIX.$table)."
                         WHERE ID = ".(int)$ID) or db_die();
    $row = db_fetch_row($result);
    if ($row[0] == 0) die('no entry found.');
    if ($row[0] <> $user_ID) die('You are not privileged to do this!');
}

/**
 * Checks whether file is locked and user has write permissions
 *
 * @param string 	$table  	- Associated table
 * @param string 	$locked 	- Name of db_field
 * @param int 		$ID        	- ID of record
 * @return boolean
 */
function check_locked($table, $locked, $ID) {
    global $user_ID, $user_type;
    $query="SELECT ".qss($locked)."
                   FROM ".qss(DB_PREFIX.$table)."
                   WHERE ID = ".(int)$ID;
    $result = db_query($query) or db_die();
    $row = db_fetch_row($result);
    if ($row[0] <> $user_ID and $row[0]>0 and $user_type!=2) return false;
    else return true;
}
?>
