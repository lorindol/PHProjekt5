<?php
/**
* postgresql driver
*
* @package    db layer
* @module     main
* @author     Albrecht Guenther, $Author: gustavo $
* @licence    GPL, see www.gnu.org/copyleft/gpl.html
* @copyright  2000-2006 Mayflower GmbH www.mayflower.de
* @version    $Id: postgresql.inc.php,v 1.12 2006/11/07 00:28:28 gustavo Exp $
*/
if (!defined('lib_included')) die('Please use index.php!');


ini_set("pgsql.ignore_notice", "1");


// Connect
$dbIDnull = "pgnull";
$_database_ressource_identifier = pg_connect((($db_host == "") ? "" : "host= ".$db_host." ").(($db_pass == "") ? "" : "password=".$db_pass." ")."dbname=".$db_name." user=".$db_user) or pg_errormessage();
if (!$_database_ressource_identifier)
$_database_ressource_identifier = pg_connect((($db_host == "" or $db_host == "localhost") ? "" : "host= ".$db_host." ").(($db_pass == "") ? "" : "password=".$db_pass." ")."dbname=".$db_name." user=".$db_user) or pg_errormessage();
if (!$_database_ressource_identifier)
die("<b>Database connection failed!</b><br />Call admin, please.");
// execute sql query
function db_query($query) {
    global $_database_ressource_identifier, $f_row;

    $dbIDnull = "";

    //get name of sequence
    if      (preg_match("/INTO\s*(\w*)\s*/i",$query,$matches)){
        // hack for db_records-sequence
        if ($matches[1]=="db_records") { $matches[1] .= "_t";}
        $dbIDnull = "nextval('" . $matches[1] . "_id_seq')"; 
    }
//    else if (preg_match("/into\s*(\w*)\s*/",$query,$matches)){
//      $dbIDnull = "nextval('" . $matches[1] . "_id_seq')";
//    }
    $query = ereg_replace("pgnull", $dbIDnull, $query);

    // the new pg 7.3 doesn't allow empty strings to store on integer fields - yes, yes, we did that! :-()
    // -> workaround for the moment
    $query = ereg_replace("<> ''", "is not NULL", $query);
    $query = ereg_replace("''", "NULL", $query);


    // since from php version 4.2 on new postgres functions are introduced we have to distinguish
    if (substr(phpversion(),0,1) == "4" and substr(phpversion(),2,1) <= "2") {
        $tmp = pg_exec($_database_ressource_identifier, $query);
    }
    else {
        $tmp = pg_query($_database_ressource_identifier, $query);
    }
    //
    if (!$tmp) {
        // Before jumping to a conclusion about the success of our query, first check if it might be a CREATE statement,
        // which does not return any results.
        if (substr(ltrim($query), 0, 7) == "CREATE ") {
            if (function_exists('pg_result_status')) {
                // Let's see what the results are. 0 or 1 means everything is fine.
                if (pg_result_status() <= 1) {
                    // If everything is fine, we just return a simple result set
                    // that won't hurt anyone.
                    $tmp = pg_query($_database_ressource_identifier, "SELECT 1");
                }
            }
        }
        else {
            //print "No result set in: $query<br />";
            $tmp = true;
        }
    }
    $f_row[$tmp] = 0;
    return($tmp);
}

// fetch row statement
function db_fetch_row($result) {
    return pg_fetch_row($result);
    //if (++$f_row[$result] > pg_numrows($result)) return 0;
    //else return pg_fetch_row($result, ($f_row[$result]-1));
}

// Error-Messages
function db_die() {
    // since from php version 4.2 on new postgres functions are introduced we have to distinguish
    if (substr(phpversion(),0,1) == "4" and substr(phpversion(),2,1) < "2") echo pg_errormessage($_database_ressource_identifier);
    else echo pg_last_error($_database_ressource_identifier);
    die("</div></body></html>");
}

// error code
function get_sql_errno($resource) {
    return pg_result_error($resource);
}

?>
