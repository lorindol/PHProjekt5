<?php
/**
 * oracle driver
 *
 * @package    	db layer
 * @subpackage 	main
 * @author     	Albrecht Guenther, $Author: gustavo $
 * @licence     GPL, see www.gnu.org/copyleft/gpl.html
 * @copyright  	2000-2006 Mayflower GmbH www.mayflower.de
 * @version    	$Id: oracle.inc.php,v 1.14 2007-05-31 08:11:59 gustavo Exp $
 */
if (!defined('lib_included')) die('Please use index.php!');

// Connect
$dbIDnull = "null";

$_database_ressource_identifier = OCILogon($db_user, $db_pass, $db_name);
$datestmt = OCIParse($_database_ressource_identifier, "alter session set NLS_DATE_FORMAT='YYYY-MM-DD HH:MI:SS'");
OCIExecute($datestmt);
if (!$_database_ressource_identifier) die("<b>Database connection failed!</b><br />Call admin, please.");

/**
 * Execute sql query
 *
 * @param string 	$query 	- Query string
 * @return void
 */
function db_query($query) {
    global $_database_ressource_identifier;

    if (eregi('insert|update|delete|create',$query)) {
        if ( (eregi('insert', $query)) && (stristr($query, '(null,')) ) {
            $tok = explode (" ", $query);
            $seq_name = $tok[2];
            $seq_name = '('.$seq_name."_id_seq.nextval";
            $query = str_replace ('(null', $seq_name, $query);
        }
        $stmt = OCIParse($_database_ressource_identifier, $query);
        OCIExecute($stmt);
        $commit_stmt = OCIParse($_database_ressource_identifier, 'commit');
        OCIExecute($commit_stmt);
    }
    else {
        $stmt = OCIParse($_database_ressource_identifier, $query);
        OCIExecute($stmt);
    }
    return $stmt;
}

/**
 * Fetch row statement
 *
 * @param array 	$result	 - Querry result
 * @return array       			Data array
 */
function db_fetch_row ($result) {
    OCIFetchInto($result, $row, OCI_RETURN_NULLS+OCI_RETURN_LOBS);
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
    echo OCIError($stmt);
    die("</div></body></html>");
}

/**
 * Error code
 *
 * @param  object  $resource		- Resourse object of the query
 * @return string					Error message
 */
function get_sql_errno($resource) {
    $error = OCIError();
    if ($error !== false) {
        return $error['code'];
    }
    return '';
}
?>
