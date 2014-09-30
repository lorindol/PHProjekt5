<?php
/**
* informix driver
*
* @package    db layer
* @module     main
* @author     Albrecht Guenther, $Author: gustavo $
* @licence    GPL, see www.gnu.org/copyleft/gpl.html
* @copyright  2000-2006 Mayflower GmbH www.mayflower.de
* @version    $Id: informix.inc.php,v 1.8 2006/11/07 00:28:28 gustavo Exp $
*/
if (!defined('lib_included')) die('Please use index.php!');

// Connect
$dbIDnull = "0";
if ($db_host == "") $db = $db_name;
else $db = $db_name."@".$db_host;
$_database_ressource_identifier = ifx_connect($db, $db_user, $db_pass);
if (!$_database_ressource_identifier) die("<b>Database connection failed!</b><br />Call admin, please.");

// execute sql query
function db_query($query) {
    $rid = ifx_prepare($query, $_database_ressource_identifier);
    if (!ifx_do($rid)) {
        ifx_error();
    }
    return $rid;
}

// fetch row statement
function db_fetch_row($result) {
    if ($row = ifx_fetch_row($result)) {
        $types = ifx_fieldtypes($result);
        $row = array_values($row);
    }
    return $row;
}

// Error-Messages
function db_die() {
    echo ifx_error();
    die("</div></body></html>");
}

// error code
function get_sql_errno($resource) {
    return ifx_error();
}

?>
