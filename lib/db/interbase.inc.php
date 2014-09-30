<?php
/**
 * interbase driver
 *
 * @package    	db layer
 * @subpackage 	main
 * @author     		Albrecht Guenther, $Author: gustavo $
 * @licence     GPL, see www.gnu.org/copyleft/gpl.html
 * @copyright  	2000-2006 Mayflower GmbH www.mayflower.de
 * @version    		$Id: interbase.inc.php,v 1.15 2007-05-31 08:11:59 gustavo Exp $
 */
if (!defined('lib_included')) die('Please use index.php!');

// Connect
$dbIDnull = "null";
$db_host2 = $db_host.":".$db_name;
$_database_ressource_identifier = ibase_connect($db_host2, $db_user, $db_pass);
if (!$_database_ressource_identifier) die("<b>Database connection failed!</b><br />Call admin, please.");

/**
 * Execute sql query
 *
 * @param string 	$query 	- Query string
 * @return void
 */
function db_query($query) {
    global $_database_ressource_identifier, $stmt;

    // check and repair blank data '' and quotes in queries
    if (eregi('select',$query)) {
        $query = ereg_replace("= ''","= '99999'",$query);
        $query = ereg_replace("=''","= '99999'",$query);
    }
    if (eregi('insert|update|delete|create',$query)) {
        $query = ereg_replace("= ''","= NULL",$query);
        $query = ereg_replace("=''","= NULL",$query);
        $query = ereg_replace(",''",",NULL",$query);
        $query = ereg_replace(", ''",",NULL",$query);
        $query = ereg_replace("\\\'","''",$query);
        $query = ereg_replace('\\\"','"',$query);
    }

    // Check for Interbase-Firebird reserved words
    $patterns[] = '/(\W)(role)(\W)/i';
    $patterns[] = '/(\W)(type)(\W)/i';
    $patterns[] = '/(\W)(password)(\W)/i';
    $patterns[] = '/(\W)(action)(\W)/i';
    $query = preg_replace($patterns, '\\1"\\2"\\3', $query);

    $stmt = ibase_query($_database_ressource_identifier, $query);
    if (eregi('insert|update|delete|create', $query)) {
        ibase_commit();
    }
    return $stmt;
}

/**
 * Fetch row statement
 *
 * @param array 	$result 	- Querry result
 * @return array       			Data array
 */
function db_fetch_row($result) {
    return ibase_fetch_row($result);
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
    echo ibase_errmsg();
    die("</div></body></html>");
}

/**
 * Error code
 *
 * @param  object  $resource		- Resourse object of the query
 * @return string					Error message
 */
function get_sql_errno($resource) {
    return ibase_errcode();
}
?>
