<?php
/**
* sqlite driver
*
* @package    db layer
* @module     main
* @author     Johann Hartmann, $Author: gustavo $
* @licence    GPL, see www.gnu.org/copyleft/gpl.html
* @copyright  2000-2006 Mayflower GmbH www.mayflower.de
* @version    $Id: sqlite.inc.php,v 1.10 2006/11/07 00:28:28 gustavo Exp $
*/
if (!defined('lib_included')) die('Please use index.php!');

// Connect
$dbIDnull = "null";

// name of the database file
define('SQLITEDBFILENAME', dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR.$db_name.".db");

if (!$_database_ressource_identifier = sqlite_open(SQLITEDBFILENAME, 0666, $sqliteerror)) {
    die("<b>Database connection failed: $sqliteerror!</b><br />Call admin, please.");
}

// execute sql query
function db_query($query) {
    global $_database_ressource_identifier;
    $query = ereg_replace("\\\'", "''", $query);
    $query = ereg_replace('\\\"', '""', $query);
    // remote secondary keys from create table statement
    $query = preg_replace('#(,\s*(KEY|INDEX)\s*\([^)]+\))#msiU', '', $query);
    $res = sqlite_query($_database_ressource_identifier, $query);
    if ( !$res ) {
        echo "Error executing sqlite_query($_database_ressource_identifier, $query)<br />";
        echo sqlite_error_string(sqlite_last_error($_database_ressource_identifier)).'<br />';
    }
    return $res;
}

// fetch row statement
function db_fetch_row($result) {
    return sqlite_fetch_array($result, SQLITE_NUM);
}

// Error-Messages
function db_die() {
    global $_database_ressource_identifier;
    echo '<pre>';
    echo sqlite_error_string(sqlite_last_error($_database_ressource_identifier));
    die("</div></body></html>");
}

// error code
function get_sql_errno($resource) {
    return sqlite_error_string();
}

?>
