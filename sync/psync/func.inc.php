<?php

//
// $Id: func.inc.php,v 1.1.2.1 2007/08/02 09:54:48 albrecht Exp $
//

/*
 *
 *
 */
function startSyncLog() {
    appDebug("########################################################################", 0);
    appDebug("############################## SYNC START ##############################", 0);
    appDebug("########################################################################\n", 0);
}


/*
 *
 *
 */
function exitSync() {
    global $module, $sync_data;

    appDebug(array(__FUNCTION__, __LINE__, "[$module] - \$sync_data:".var_export($sync_data, true)."\n"), 64);
    appDebug("========================================================================", 0);
    appDebug("============================== SYNC DONE  ==============================", 0);
    appDebug("========================================================================\n\n\n", 0);
    exit();
}


/*
 *  user defined error handling function
 *
 */
function userErrorHandler($errno, $errmsg, $filename, $linenum, $vars) {
    global $user_email, $_SERVER;

    // define an assoc array of error string
    // in reality the only entries we should
    // consider are 2, 8, 256, 512 and 1024
    $errortype = array (
                   1 => 'Error',
                   2 => 'Warning',
                   4 => 'Parsing Error',
                   8 => 'Notice',
                  16 => 'Core Error',
                  32 => 'Core Warning',
                  64 => 'Compile Error',
                 128 => 'Compile Warning',
                 256 => 'User Error',
                 512 => 'User Warning',
                1024 => 'User Notice' );

    $header  = 'From: SOAP-PHP-ERROR@'.$_SERVER['SERVER_NAME'];
    $message = '
date-time:  '.date("Y-m-d H:i:s (T)").'
user-email: '.$user_email.'
err-no:     '.$errno.'
err-type:   '.$errortype[$errno].'
err-msg:    '.$errmsg.'
err-file:   '.$filename.'
err-line:   '.$linenum.'
err-vars:
'.var_export($vars, true)."\n";

    if ($errno != 8) {
        appDebug(array(__FUNCTION__, __LINE__, "Error handler:\n$message"), 64);
    }
}


/*
 *  set error log file
 *
 */
function setErrorFile() {
    if (!defined('APP_ERROR_FILE') || APP_ERROR_FILE=='') {
        return;
    }
    if ( (!file_exists(APP_ERROR_FILE) && is_writable(dirname(APP_ERROR_FILE))) ||
         (file_exists(APP_ERROR_FILE) && is_writable(APP_ERROR_FILE)) ) {
        ini_set('error_log', APP_ERROR_FILE);
    }
}


/*
 *  handle debugging output
 *
 */
function appDebug($msg, $level=1) {
    if ((APP_DEBUG_LEVEL & $level) || (APP_DEBUG_LEVEL && $level==0)) {
        if (is_array($msg)) {
            $msg = '['.$msg[0].']['.$msg[1].'] - '.$msg[2];
        }
        error_log($msg);
    }
}


/*
 *  check if sync_rel db-table exists
 *
 */
function syncRelExists() {
    $ret = false;
    $res = db_query("SHOW TABLES LIKE '".DB_PREFIX."sync_rel'");
    while ($row=db_fetch_row($res)) {
        if ($row[0]==DB_PREFIX.'sync_rel') {
            $ret = true;
            break;
        }
    }
    return $ret;
}


/*
 *  output misc environment settings and versions
 *
 */
function outputEnvironmentData() {
    $version = (defined(PHPR_VERSION)) ? PHPR_VERSION : 'Not set/defined.';
    $msg  = "--- ENVIRONMENT CHECK ---\n\n";
    $msg .= '   PHP Version       : '.phpversion()."\n";
    $msg .= '   PHProjekt Version : '.$version."\n";
    $msg .= '   DB-Table exists   : '.(syncRelExists()?'YES':'NO')."\n";
    error_log($msg);
}

?>
