<?php
/**
 * sqlite driver
 *
 * @package    	db layer
 * @subpackage 	main
 * @author     	Johann Hartmann, $Author: gustavo $
 * @licence     GPL, see www.gnu.org/copyleft/gpl.html
 * @copyright  	2000-2006 Mayflower GmbH www.mayflower.de
 * @version    	$Id: sqlite.inc.php,v 1.15 2007-05-31 08:11:59 gustavo Exp $
 */
if (!defined('lib_included')) die('Please use index.php!');

// Connect
$dbIDnull = "null";

// name of the database file
define('SQLITEDBFILENAME', dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR.$db_name.".db");

if (!$_database_ressource_identifier = sqlite_open(SQLITEDBFILENAME, 0666, $sqliteerror)) {
    die("<b>Database connection failed: $sqliteerror!</b><br />Call admin, please.");
}

/**
 * Execute sql query
 *
 * @param string 	$query 	- Query string
 * @return void
 */
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

/**
 * Fetch row statement
 *
 * @param array 	$result 	- Querry result
 * @return array       			Data array
 */
function db_fetch_row($result) {
    return sqlite_fetch_array($result, SQLITE_NUM);
}

/**
 * Error-Messages
 * only display them if error reporting for phprojekt is activated.
 * else exit with a non path disclosing error message
 *
 * @param void
 * @return void
 */
function db_die() {
    global $_database_ressource_identifier;
    echo '<pre>';
    echo sqlite_error_string(sqlite_last_error($_database_ressource_identifier));
    die("</div></body></html>");
}

/**
 * Error code
 *
 * @param  object  $resource		- Resourse object of the query
 * @return string					Error message
 */
function get_sql_errno($resource) {
    return sqlite_error_string();
}
?>
