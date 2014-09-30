<?php
/**
* odbc driver
*
* @package    db layer
* @module     main
* @author     Albrecht Guenther, $Author: gustavo $
* @licence    GPL, see www.gnu.org/copyleft/gpl.html
* @copyright  2000-2006 Mayflower GmbH www.mayflower.de
* @version    $Id: odbc.inc.php,v 1.5 2006/11/07 00:28:28 gustavo Exp $
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

// execute sql query
function db_query($query) {
    return odbc_exec($_database_ressource_identifier,$query);
}

// fetch row statement
function db_fetch_row($result) {
    return odbc_fetch_array($result);
}

// Error-Messages
// only display them if error reporting for phprojekt is activated. 
// else exit with a non path disclosing error message
function db_die() {
    // if (PHPR_ERROR_REPORTING_LEVEL == 1) {
        echo '<p><b>Database error.</b> Error message: ', odbc_errormsg(), '</p><p>Backtrace:</p><pre>';
        print_r(debug_backtrace());
        die("</pre></div></body></html>");
    // } else die(__('Sorry, there has been a database error. Please contact your local system administrator for help.'));
}

// error code
function get_sql_errno($resource) {
    // $resource not needed, but keep it in parameter list
    return odbc_error();
}

?>
