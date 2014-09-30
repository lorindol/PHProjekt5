<?php
/**
 * odbc driver
 *
 * @package    	db layer
 * @subpackage 	main
 * @author     	Albrecht Guenther, $Author: gustavo $
 * @licence     GPL, see www.gnu.org/copyleft/gpl.html
 * @copyright  	2000-2006 Mayflower GmbH www.mayflower.de
 * @version    	$Id: odbc.inc.php,v 1.10 2007-05-31 08:11:59 gustavo Exp $
 */
if (!defined('lib_included')) die('Please use index.php!');

// Connect
//db2 - integer?
$dbIDnull = "null";

// Woher?
$db_host = "thedb2"; //katalogisierter ODBC-DB2-Treiber;
$db_user = "db2admin"; //the user
$db_pass = "test"; //the pass

	try{
		$_database_ressource_identifier = odbc_connect($db_host, $db_user, $db_pass) or odbc_error();
		odbc_close($conn);
		echo "<font color='green'><b>connect successful</b></font>";
	}
	catch(Exception $e)
	{
		$temp = $e->getMessage();
	    echo $temp."<br />";
	    echo"<font color=red><b>DID NOT establish ODBC-Connection</b></font>";
	}

//db2_select_database
$_database_connection = mysql_select_db($db_name);
//if the one or the other failes -> Own errorcode output
if (isset($_database_ressource_identifier) or !$_database_ressource_identifier) die("<b>Database connection failed!</b><br />Call admin, please.");

/**
 * Execute sql query
 *
 * @param string 	$query 	- Query string
 * @return void
 */
function db_query($query) {
    return odbc_exec($_database_ressource_identifier,$query);
}

/**
 * Fetch row statement
 *
 * @param array 	$result 	- Querry result
 * @return array       			Data array
 */
function db_fetch_row($result) {
    return odbc_fetch_array($result);
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
    echo '<p><b>Database error.</b> Error message: ', odbc_errormsg(), '</p><p>Backtrace:</p><pre>';
    print_r(debug_backtrace());
    die("</pre></div></body></html>");
}

/**
 * Error code
 *
 * @param  object  $resource		- Resourse object of the query
 * @return string					Error message
 */
function get_sql_errno($resource) {
    // $resource not needed, but keep it in parameter list
    return odbc_error();
}
?>
