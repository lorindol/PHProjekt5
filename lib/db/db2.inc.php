<?php
/**
 * db2ibm driver
 *
 * @package    	db layer
 * @subpackage 	main
 * @author     	Sinner, $Author: gustavo $
 * @licence     GPL, see www.gnu.org/copyleft/gpl.html
 * @copyright  	2000-2006 Mayflower GmbH www.mayflower.de
 * @version    	$Id: db2.inc.php,v 1.13 2007-05-31 08:11:59 gustavo Exp $
 */

// check whether lib.inc.php has been included
if (!defined("lib_included")) die("Please use index.php!");

// Connect
global $_database_ressource_identifier;
$_database_ressource_identifier = db2_connect($db_name, $db_user, $db_pass) or db2_conn_error();
//echo "identiefierinLIB:".$_database_ressource_identifier.".eoIdentifier<p>";
if (!isset($_database_ressource_identifier) or !$_database_ressource_identifier) die("<b>Database connection failed!</b><br />Call admin, please.<br />");

/**
 * Execute sql query
 *
 * @param string 	$query 	- Query string
 * @return void
 */
function db_query($query) {
	global $_database_ressource_identifier;
 	//echo "IDENTinFUNCTION:".$_database_ressource_identifier."eoIdentifierInfuntcion<p>";

	$stmt = db2_prepare($_database_ressource_identifier, $query);
	if ($stmt) {
  		//echo "<br /><br /> STATEMENT: <br />".$stmt."<br /><br />stmtEnde<br /><br />";
		$checkifdone = db2_execute($stmt);
		if ($checkifdone){
			//echo "<p><font color=\"#00C000\">Execute ausgeführt für query:<br /> db2-query:".$query."</font><br />";
			return $stmt;}
		else {echo"<font color=\"#FF0000\"> Execute fehlgeschlagen für Query:<br />".$query."<p></font>";return false;}
	}//ifstmt
}//function

/**
 * Fetch row statement
 *
 * @param array 	$result 	- Querry result
 * @return array       			Data array
 */
function db_fetch_row($result) {
    return db2_fetch_array($result);
}//end fct()

/**
 * Error-Messages
 * only display them if error reporting for phprojekt is activated.
 * else exit with a non path disclosing error message
 *
 * @param void
 * @return void
 */
function db_die() {
    if (PHPR_ERROR_REPORTING_LEVEL == 1) {
        echo '<p><b>Database error.</b> Error message: ', db2_conn_errormsg(), '</p><p>Backtrace:</p><pre>';
        print_r(debug_backtrace());
        die("</pre></body></html>");
    } else die(__('Sorry, there has been a database error. Please contact your local system administrator for help.'));
}

/**
 * Error code
 *
 * @param  object  $resource		- Resourse object of the query
 * @return string					Error message
 */
function get_sql_errno($resource) {
     global $_database_ressource_identifier;
     echo "dbERROR occured: ".db2_stmt_error()."<p>";
     $msg = db2_stmt_errormsg();
     echo $msg;
     return $msg;
}
?>
