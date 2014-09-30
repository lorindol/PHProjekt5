<?php
/**
* oracle driver
*
* @package    db layer
* @module     main
* @author     Albrecht Guenther, $Author: gustavo $
* @licence    GPL, see www.gnu.org/copyleft/gpl.html
* @copyright  2000-2006 Mayflower GmbH www.mayflower.de
* @version    $Id: oracle.inc.php,v 1.9 2006/11/07 00:28:28 gustavo Exp $
*/
if (!defined('lib_included')) die('Please use index.php!');

// Connect
$dbIDnull = "null";

$_database_ressource_identifier = OCILogon($db_user, $db_pass, $db_name);
$datestmt = OCIParse($_database_ressource_identifier, "alter session set NLS_DATE_FORMAT='YYYY-MM-DD HH:MI:SS'");
OCIExecute($datestmt);
if (!$_database_ressource_identifier) die("<b>Database connection failed!</b><br />Call admin, please.");

// execute sql query
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

// fetch row statement
function db_fetch_row ($result) {
    OCIFetchInto($result, $row, OCI_RETURN_NULLS+OCI_RETURN_LOBS);
    return $row;
}

// Error-Messages
function db_die() {
    echo OCIError($stmt);
    die("</div></body></html>");
}

// error code
function get_sql_errno($resource) {
    $error = OCIError();
    if ($error !== false) {
        return $error['code'];
    }
    return '';
}

?>
