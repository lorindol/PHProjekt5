<?php
/**
 * ms_sql driver
 *
 * @package    	db layer
 * @subpackage 	main
 * @author     		Albrecht Guenther, $Author: gustavo $
 * @licence     GPL, see www.gnu.org/copyleft/gpl.html
 * @copyright  	2000-2006 Mayflower GmbH www.mayflower.de
 * @version    		$Id: ms_sql.inc.php,v 1.13 2007-05-31 08:11:59 gustavo Exp $
 */
if (!defined('lib_included')) die('Please use index.php!');

// Connect
$dbIDnull = "msnull";
$_database_ressource_identifier = mssql_connect($db_host, $db_user, $db_pass);
$_database_connection = mssql_select_db($db_name);
if (!$_database_ressource_identifier or (isset($_database_connection) and !$_database_connection)) die("<b>Database connection failed!</b><br />Call admin, please.");

/**
 * Execute sql query
 *
 * @param string 	$query 	- Query string
 * @return void
 */
function db_query($query) {
    $sPattern  = 'msnull[ ]*,';
    $sNewQuery = eregi_replace($sPattern, '', $query);
    if ($sNewQuery != $query) {
        $sPattern  = '\([ ]*ID[ ]*,';
        $sNewQuery = eregi_replace($sPattern, '(', $sNewQuery);
        $sPattern  = '\,[ ]*ID[ ]*,';
        $sNewQuery = eregi_replace($sPattern, ',', $sNewQuery);
    }
    $sPattern  = '\\\\\'';
    $sSubst    = '\'\'';
    $sNewQuery = eregi_replace($sPattern, $sSubst, $sNewQuery);
    $sPattern  = '\\\\"';
    $sSubst    = '"';
    $sNewQuery = eregi_replace($sPattern, $sSubst, $sNewQuery);
    return mssql_query($sNewQuery);
}

/**
 * Fetch row statement
 *
 * @param array 	$result 	- Querry result
 * @return array       			Data array
 */
function db_fetch_row($result) {
    return mssql_fetch_row($result);
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
    echo mssql_get_last_message();
    die("</div></body></html>");
}

/**
 * Error code
 *
 * @param  object  $resource		- Resourse object of the query
 * @return string					Error message
 */
function get_sql_errno($resource) {
    return mssql_get_last_message();
}
?>
