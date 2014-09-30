<?php
if (!@include_once(PHPROJEKT_PATH.'lib/db/'.$db_type.'.inc.php')) {
    define('PHPROJEKT_NOT_FOUND', 1);
}

/**
* Wrapper over the PHProjekt Database-Abstraction-Layer
*
* This class offers an OO-API to the procedural
* Database-Abstraction-Layer of PHProjekt
*
* @author Johannes Schlueter <johannes@schlueters.de>
* @version $Id: class_db.php,v 1.1 2007-10-29 08:46:33 gustavo Exp $
*/
class db_conn {
    var $null;

    function db_conn() {
        global $dbIDnull;
        $this->null = $dbIDnull;
    }
    
    function quote($text) {
    	return addslashes($text);
    }
	
    /**
    * Execute a query
    *
    * In case of an error false is being returned
    *
    * @param string SQL-Query to be executed
    * @return class db_result
    * @access public
    */
    function query($sql) {
        $sql = guessed_encoding_to_iso_8859_1($sql);
        $query = db_query($sql);
        a('query', __LINE__, $sql);
        
        if (!$query) {
            return false;
        }
        
        return new db_result($query);
    }
}

/**
* Wrapper over a DB result
*
* This class offers access to a result-set from a DB-Query
*/
class db_result {
    var $handle;
    
    function db_result($handle) {
        $this->handle = $handle;
    }
    
    function get_next_row() {
        $result = db_fetch_row($this->handle);
        return $result ? array_map('guessed_encoding_to_utf8', $result) : $result;
    }
}
