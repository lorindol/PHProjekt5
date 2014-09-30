<?php
/**
 * mysql driver
 *
 * @package    	db layer
 * @subpackage 	main
 * @author     		Albrecht Guenther, $Author: gustavo $
 * @licence     GPL, see www.gnu.org/copyleft/gpl.html
 * @copyright  	2000-2006 Mayflower GmbH www.mayflower.de
 * @version    		$Id: mysql.inc.php,v 1.25 2008-02-15 17:33:36 gustavo Exp $
 */
if (!defined('lib_included')) die('Please use index.php!');

define('PATH_PRE','.');

// Connect
$dbIDnull = "null";

$_database_ressource_identifier = mysql_connect($db_host, $db_user, $db_pass) or mysql_error();
$_database_connection = mysql_select_db($db_name);
if (!$_database_ressource_identifier or (isset($_database_connection) and !$_database_connection)) die("<b>Database connection failed!</b><br />Call admin, please.");

/**
 * Fetch row statement
 *
 * @param array $result 	- Querry result
 * @return array       		Data array
 */
function db_fetch_row($result) {
    return mysql_fetch_row($result);
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
    if (PHPR_ERROR_REPORTING_LEVEL == 1) {
        echo '<p><b>Database error.</b> Error message: ', mysql_error(), '</p><p>Backtrace:</p><pre>';
        print_r(debug_backtrace());
        die("</pre></div></body></html>");
    } else die(__('Sorry, there has been a database error. Please contact your local system administrator for help.'));
}

/**
 * Error code
 *
 * @param  object  $resource		- Resourse object of the query
 * @return string					Error message
 */
function get_sql_errno($resource) {
    // $resource not needed, but keep it in parameter list
    return mysql_errno();
}

/**
 * Execute sql query
 *
 * @param string 	$query 	- Query string
 * @return void
 */
function db_query($query) {
    global $query_counter;
    $start_time = microtime(true);
    $res        = mysql_query($query);
    $end_time   = microtime(true);
    $time       = $end_time - $start_time;

    if (PHPR_ERROR_REPORTING_LEVEL == 2) {
        // count queries for benchmark in libn.inc.php
        $query_counter++;

        // SQL Reporting is activated
        static $query_time;
        if (!isset($query_time)) {
            $query_time = 0;
        };
        $query_time += $time;
        $numrows = 0;

        if ($res) {
            if (strtolower(substr(trim($query), 0, 6)) == 'select') {
            	$numrows = mysql_num_rows($res);
                $explain      = 'explain '.$query;
                $explain_res  = mysql_query($explain);
                // parse explain result
                $explain_data  = array();
                $affected_rows = 1;
                while ($explain_row = mysql_fetch_assoc($explain_res)) {
                    $explain_data[] = $explain_row;
                    $affected       = ($explain_row['rows'] == 0) ? 1 : $explain_row['rows'];
                    $affected_rows  = $affected_rows * $affected;
                }
                // simple calculation for the effiency of the query: found rows / scanned rows
                if ($numrows == 0) {
                    $explain_efficiency = null;
                } else {
                    $explain_efficiency = ($numrows / $affected_rows) * 100;
                }
                $backtrace = debug_backtrace();
                $origin    = $backtrace[count($backtrace) - 1];
                $info = sprintf("%02.3f;%02.3f;%s;%s;%s\n",
                                $time * 1000,
                                $explain_efficiency,
                                $origin['file'],
                                $origin['line'],
                                preg_replace("#[\s\r\n]+#m", ' ', $query));
				error_log($info,'3',PATH_PRE.'/sql_log.csv');
				if(!file_exists(PATH_PRE.'/sql_log.csv')) error_log("file doesnt exist");
            }
        }
    }
    return $res;
}
?>
