<?php

// ntlm.inc.php - PHProjekt Version 5.2
// copyright  ©  2000-2007 Albrecht Guenther  ag@phprojekt.com
// www.phprojekt.com
// Author: Nina Schmitt
//
// authentification etc.



// parse transmitted variables
define('PATH_PRE','../');
require_once(PATH_PRE.'config.inc.php');
require_once(PATH_PRE.'lib/gpcs_vars.inc.php');
if (PHPR_NTLM_AUTH_ENABLED==1) {
    check_ntlm();
}
else {
    die(__('Sorry, the NTML login is disabbled. Please, contact your system admin.'));
}

function check_ntlm(){
    
    if ($_REQUEST['no_NTLM'] <> 1 && isset($_SERVER['REMOTE_USER']) && !strstr($_SERVER['QUERY_STRING'], 'module=logout')) {

        // Getting user name
        $remote_user = stripslashes($_SERVER['REMOTE_USER']);
        if (PHPR_ERROR_REPORTING_LEVEL == 1) {
            error_log("Remote user set: ".$_SERVER['REMOTE_USER']."<br />");
        }
        $_SESSION['user_authenticated_via_NTML']='true';
        header('location:'.$_SESSION['myurl'].'?remote_user='.$remote_user);
        die();

    }
    if (PHPR_ERROR_REPORTING_LEVEL == 1 && !strstr($_SERVER['QUERY_STRING'], 'module=logout') && $_REQUEST['no_NTLM'] <> 1) {
        error_log("Remote user not set.<br />");
    }
    $_SESSION['user_authenticated_via_NTML']='false';
    header('location:'.$_SESSION['myurl']);
    die();

}

?>
