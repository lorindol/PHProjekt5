<?php

// todo.inc.php - PHProjekt Version 5.2
// copyright  ©  2000-2005 Albrecht Guenther  ag@phprojekt.com
// www.phprojekt.com
// Author: Franz Graf, $Author: albrecht $
// $Id: todo.inc.php,v 1.4 2006/08/22 08:05:54 albrecht Exp $

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
    $result = db_query(xss($query)) or db_die();
    $row = db_fetch_row($result);
    
    if ($row === false) return -1;
    
    return $row[0];
}

?>