<?php
/**
 * informix driver
 *
 * @package    	db layer
 * @subpackage 	main
 * @author     		Albrecht Guenther, $Author: gustavo $
 * @licence     GPL, see www.gnu.org/copyleft/gpl.html
 * @copyright  	2000-2006 Mayflower GmbH www.mayflower.de
 * @version    		$Id: informix.inc.php,v 1.13 2007-05-31 08:11:59 gustavo Exp $
 */
if (!defined('lib_included')) die('Please use index.php!');

// Connect
$dbIDnull = "0";
if ($db_host == "") $db = $db_name;
else $db = $db_name."@".$db_host;
$_database_ressource_identifier = ifx_connect($db, $db_user, $db_pass);
if (!$_database_ressource_identifier) die("<b>Database connection failed!</b><br />Call admin, please.");

/**
 * Execute sql query
 *
 * @param string 	$query 	- Query string
 * @return void
 */
function db_query($query) {
    $rid = ifx_prepare($query, $_database_ressource_identifier);
    if (!ifx_do($rid)) {
        ifx_error();
    }
    return $rid;
}

/**
 * Fetch row statement
 *
 * @param array 	$result 	- Querry result
 * @return array       			Data array
 */
function db_fetch_row($result) {
    if ($row = ifx_fetch_row($result)) {
        $types = ifx_fieldtypes($result);
        $row = array_values($row);
    }
    return $row;
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
    echo ifx_error();
    die("</div></body></html>");
}

/**
 * Error code
 *
 * @param  object  $resource		- Resourse object of the query
 * @return string					Error message
 */
function get_sql_errno($resource) {
    return ifx_error();
}
?>
