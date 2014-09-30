<?php
/**
 * @package    todo
 * @subpackage main
 * @author     Franz Graf, $Author: nina $
 * @licence    GPL, see www.gnu.org/copyleft/gpl.html
 * @copyright  2000-2006 Mayflower GmbH www.mayflower.de
 * @version    $Id: todo.inc.php,v 1.6 2007-11-09 10:28:54 nina Exp $
 */

// check whether lib.inc.php has been included
if (!defined('lib_included')) die('Please use index.php!');


/**
 * Get the 'last inserted' id of a todo.
 *
 * @param int $von
 * @param int $an
 * @return int id of the last inserted todo from user $von to user $an or -1 if no such record exists
 */
function todo_get_last_id($von = 0, $an = 0) {
    $query = "SELECT MAX(id)
                FROM ".DB_PREFIX."todo 
               WHERE von = ".(int)$von."  AND ext = ".(int)$an;
    $result = db_query($query) or db_die();
    $row = db_fetch_row($result);
    
    if ($row === false) return -1;
    
    return $row[0];
}

?>
