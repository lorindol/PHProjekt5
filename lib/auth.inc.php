<?php
/**
 * Authorization rutine
 *
 * @package    	lib
 * @subpackage 	main
 * @author     	Albrecht Guenther, $Author: polidor $
 * @licence     GPL, see www.gnu.org/copyleft/gpl.html
 * @copyright  	2000-2006 Mayflower GmbH www.mayflower.de
 * @version    	$Id: auth.inc.php,v 1.84 2008-01-20 15:19:00 polidor Exp $
 */

// check whether lib.inc.php has been included
if (!defined('lib_included')) die('Please use index.php!');

include(LIB_PATH.'/languages.inc.php');

// filled with the login error message if available.
// this may also be set by a custom auth module.
$login_error = '';

// hints for $fetch_uservalues:
// null  = initial value. this may also be set by a custom auth module ifx
//         PHPR_CUSTOM_AUTH_PHPR_DB or PHPR_CUSTOM_AUTH_PHPR_LDAP is also
//         set to true to allow a login check with the phprojekt auth (db or ldap).
// false = login is denied. this may also be set by a custom auth module if
//         PHPR_CUSTOM_AUTH_PHPR_DB or PHPR_CUSTOM_AUTH_PHPR_LDAP is also
//         set to true to deny a login check with the phprojekt auth (db or ldap).
// true  = login is allowed. this may also be set by a custom auth module
//         if available (defined with PHPR_CUSTOM_ENVIRONMENT).
$fetch_uservalues = null;

// custom environment is not active if not defined previously
if (!defined('PHPR_CUSTOM_ACTIVE')) {
    define('PHPR_CUSTOM_ACTIVE', false);
}

// fetch language ...
$lang_found = false;
// no language in settings -> choose browser language
if (!isset($langua)) {
    $langua = getenv('HTTP_ACCEPT_LANGUAGE');
    foreach ($languages as $langua1 => $langua2) {
        if (eregi($langua1,$langua)) {
            $langua = $langua1;
            $lang_found = true;
        }
    }
    define('LANG',substr($langua,0,2));
    if ($lang_found) {
        include(PATH_PRE.'lang/'.LANG.'.inc.php');
    }
    else {
        $langua = 'en';
        include(PATH_PRE.'lang/en.inc.php');
    }
}


// 1. Get login field name (and label)
list($label, $field_name) = auth_get_fieldname();

// initializing remote user
if (PHPR_NTLM_AUTH_ENABLED==1) {
    if(!isset($remote_user)) $remote_user = '';
    
    $login_via_get = (isset($_GET['loginstring']) && isset($_GET['user_pw']));
    $login_via_post = (isset($_POST['loginstring']) && isset($_POST['user_pw']));
    if(!isset($_SESSION['login_mode']) && !$login_via_post && !$login_via_get && !isset($_SESSION['user_authenticated_via_NTML']) && $_SESSION['user_loginname'] != 'root' &&
    strpos($_SERVER['QUERY_STRING'], 'module=logout') === false){
            $_SESSION['myurl']=$_SERVER['SCRIPT_NAME'];
        	header('location: '.PATH_PRE.'login/ntlm.inc.php');
        	die();
    }
}
// 2. check if there is an existing user logged
// no values from the session or the login form?-> show login form
if (!$user_pw and !$_SESSION['logged_in']) {
    set_style();

    // 2.a check logintoken
    list ($logintoken_user_ID, $logintoken) = auth_check_logintoken($logintoken);
    if ($logintoken_user_ID > 0) {
        $fetch_uservalues = $logintoken_user_ID;
    }
    
    if ((!empty($remote_user)) && empty($fetch_uservalues) && $_SESSION['user_authenticated_via_NTML'] == 'true') {
        $query = "SELECT ID, status 
                    FROM ".DB_PREFIX."users  
                   WHERE loginname = '$remote_user' 
                     AND is_deleted is NULL
                     AND status = 0";
        $result = db_query($query) or db_die();
        $row = db_fetch_row($result);

        $fetch_uservalues = $row[0];
        
        if (PHPR_ERROR_REPORTING_LEVEL == 1 && $fetch_uservalues == 0) {
            echo "No user found on database based on loginname = '$remote_user' <br />";
        }
        
        //$_SESSION['user_authenticated_via_NTML'] == 'false';

        // end check for &pw
    }
    
    if (empty($fetch_uservalues)) {
        // show form
        require_once(LIB_PATH.'/authform.inc.php');
        exit();
    }
    
}
// values exist -> check authentication
else {
    // add additional condition if logged to the admin section
    $admin_login = '';
    if (FILE == 'admin') {
        $admin_login = "AND usertype = 3";
    }
    // custom authentication system (if available)
    if ( PHPR_CUSTOM_ACTIVE &&
    ((PHPR_CUSTOM_AUTH && $loginstring != 'root' && $loginstring != 'test') ||
    (PHPR_CUSTOM_AUTH_TEST && $loginstring == 'test') ||
    (PHPR_CUSTOM_AUTH_ROOT && $loginstring == 'root')) ) {
        //require_once('auth_custom.php');
        $auth_custom =& Auth_Custom::singleton();
        $fetch_uservalues = $auth_custom->can_login($loginstring, $user_pw);
        if ($fetch_uservalues === true) {
            $fetch_uservalues = $auth_custom->get_db_user_id($loginstring);
        }
        // check if there are auth errors and catch them
        if (!empty($_SESSION[PHPR_CUSTOM_SESSION_ARRAY]['error'])) {
            $login_error = $_SESSION[PHPR_CUSTOM_SESSION_ARRAY]['error'];
        }
    }

    // ldap authentication system
    if ( $fetch_uservalues === null && PHPR_LDAP == '1' && (!PHPR_CUSTOM_ACTIVE ||
    (PHPR_CUSTOM_ACTIVE && PHPR_CUSTOM_AUTH_PHPR_LDAP)) ) {
        require_once(LIB_PATH.'/ldap_auth.inc.php');

        // set this also for the custom stuff (if available) to
        // avoid further useless logon trials on the custom side
        if (!empty($fetch_uservalues) && PHPR_CUSTOM_ACTIVE && PHPR_CUSTOM_AUTH) {
            $_SESSION[PHPR_CUSTOM_SESSION_ARRAY]['can_login'] = true;
        }
    }

    $enc_pw_enhanced = '';

    // normal authentication system
    if ( $fetch_uservalues === null && PHPR_LDAP != '1' && (!PHPR_CUSTOM_ACTIVE ||
    (PHPR_CUSTOM_ACTIVE && PHPR_CUSTOM_AUTH_PHPR_DB)) ) {
        $query = "SELECT ID, pw
                    FROM ".DB_PREFIX."users
                   WHERE ".qss($field_name)." = '$loginstring'
                     AND is_deleted is NULL
                     AND status = 0 $admin_login";
        $result = db_query($query);
        // loop through all names in the table users and check password
        while ($row = db_fetch_row($result)) {
            
            
            // check for password encryption and if yes, crypt the value from the form
            if (isset($user_pwenc) && !empty($user_pwenc)) {
                if (strlen($user_pwenc) == 32) {
                    $enc_pw = $user_pwenc;
                    $enc_pw_enhanced = $user_pwenc;
                } else {
                    $enc_pw = md5($user_pw);
                    $enc_pw_enhanced = $user_pw;
                }
            }
            else if (PHPR_PW_CRYPT && !isset($_SESSION['user_pw'])) {
                // Use MD5
                if (strlen($row[1]) == 32) {
                    $enc_pw = md5($user_pw);
                    $enc_pw_enhanced = md5('phprojektmd5'.$user_pw);

                } else {
                    $enc_pw = encrypt($user_pw, $row[1]);
                    $enc_pw_enhanced = md5('phprojektmd5'.$user_pw);
                }
            }
            // just the unencrypted password
            else {
                $enc_pw = $user_pw;
                $enc_pw_enhanced = $user_pw; //md5('phprojektmd5'.$user_pw);
            }
            // great! I found an entry for you!

            if (($row[1] == $enc_pw) || (($row[1] == $enc_pw_enhanced) && ($enc_pw_enhanced != ''))) {

                // Is not md5? update to md5+prefix encryption
                if (($row[1] != $enc_pw_enhanced) && (!isset($_SESSION['user_pw'])) && ($enc_pw_enhanced <> '') && PHPR_PW_CRYPT) {
                    $enc_pw = update_users_pw ($row[0], $user_pw, $admin_login);

                }

                $enc_pw = $enc_pw_enhanced;

                // store the found user_ID
                $fetch_uservalues = $row[0];

                // set this also for the custom stuff (if available) to
                // avoid further useless logon trials on the custom side
                if (PHPR_CUSTOM_ACTIVE && PHPR_CUSTOM_AUTH) {
                    $_SESSION[PHPR_CUSTOM_SESSION_ARRAY]['can_login'] = true;
                }
            }
           
        }

    }
}

// no record found? -> display error message
if (empty($fetch_uservalues)) {
    // destroy custom session environment if required
    if (PHPR_CUSTOM_ACTIVE) {
        unset($_SESSION[PHPR_CUSTOM_SESSION_ARRAY]);
    }
    // destroy the session - on some system the first,
    // on some system the second function doesn't work :-))
    @session_unset();
    @session_destroy();
    if (defined('soap_request')) {
        soapFaultDie(__('Sorry you are not allowed to enter.'), __('Sorry you are not allowed to enter.'));
    }
    set_style();
    // append return path to redirect the user to where he wanted to go
    $return_path = urlencode(xss($_REQUEST['return_path']) ? '?return_path='.xss($_REQUEST['return_path']) : '');
    if (empty($login_error)) {
        // set this if error message is not defined somewhere else
        $login_error = __('Sorry you are not allowed to enter.');
    }
    require_once(LIB_PATH.'/authform.inc.php');
    exit();
}
// fetch the user values and store them in the session!
else {
    
    //needs to be done only at login
    if(!$_SESSION['logged_in']) {
        
        // fetch the data ...
        $result = db_query("SELECT ID, vorname, nachname, kurz, email, loginname,
                                   sms, gruppe, settings, usertype, sprache, pw
                              FROM ".DB_PREFIX."users
                             WHERE ID =".(int)$fetch_uservalues."
                               AND is_deleted is NULL") or db_die();
        $row = db_fetch_row($result);
        // fill the user data into variables
        if ((!empty($logintoken) || !empty($remote_user))) {
            $loginstring = $row[5];
            $user_pwenc  = $row[11];
        }
        $user_ID        = $row[0];
        $user_firstname = $row[1];
        $user_name      = $row[2];
        $user_kurz      = $row[3];
        $user_email     = $row[4];
        $user_loginname = $row[5];
        $user_smsnr     = $row[6];  //sms nr
        // overwrite the found language of the browser with the amdin setting
        if ($row[10] <> '') {
            $langua = $row[10];
        }
        // Take the default group from the data set unless the user has chosen another one during the session
        if (!$user_group) {
            $user_group = $row[7] + 0;
        }

        //get all groups for user
        // add users of group later on!
        $user_all_groups = array();
        $members = array();
        $all_groups = array();
        $query_grup1 = "SELECT DISTINCT grup_ID
                          FROM ".DB_PREFIX."grup_user
                         WHERE user_ID=".(int)$user_ID;
        $result_grup1 = db_query($query_grup1) or db_die();
        while ($row_grup1=db_fetch_row($result_grup1)) {
            $all_groups[]=$row_grup1[0];
        }
        if(count($all_groups)>0){
            $query_grup2 = "SELECT ID, kurz, name
                              FROM ".DB_PREFIX."gruppen
                             WHERE ID IN ('".implode("','", $all_groups)."')
                          ORDER BY kurz, name";
            $result_grup2 = db_query($query_grup2) or db_die();
            while ($row_grup2=db_fetch_row($result_grup2)) {
                $members=array();
                $query_members = "SELECT DISTINCT user_ID, nachname, vorname, usertype,email
                                    FROM ".DB_PREFIX."grup_user, ".DB_PREFIX."users
                                   WHERE grup_ID = ".(int)$row_grup2[0]."
                                     AND ".DB_PREFIX."grup_user.user_ID = ".DB_PREFIX."users.ID
                                     AND ".DB_PREFIX."users.is_deleted is NULL
                                ORDER BY nachname";
                $result_members = db_query($query_members) or db_die();
                while($row_members=db_fetch_row($result_members)){
                    $members[] = $row_members[0];
                    $members_data[$row_members[0]] = array('name'=>$row_members[1], 'firstname'=>$row_members[2], 'type' => $row_members[3],
                    'email' => $row_members[4]);
                }
                $user_all_groups[$row_grup2[0]] =array('kurz' => $row_grup2[1], 'name' => $row_grup2[2], 'members'=>$members);
            }
        }



        // fetch access: first character is for the user status, second one for the visibility of his calendar
        $user_type = $row[9];

        $settings=unserialize($row[8]);

        //settings overrule admin
        if(isset($settings['preferred_group']) and $user_group){
            if($settings['preferred_group']==0)$user_group=$settings['last_group'];
            else $user_group=$settings['preferred_group'];
        }

        //if neither settings nor adminsettings were applied:
        $groups_tmp = array_keys($user_all_groups);
        if($user_group==0 and ($groups_tmp[0]>0) ){
            if($settings['last_group']>0)$user_group=$settings['last_group'];
            else $user_group = $groups_tmp[0];
        }
        // have a look into the group table: maybe he's the leader of the group _-> declare him as chief ;-)
        if ($user_group > 0 and $user_type==0) {
            $result2 = db_query("SELECT chef
                                   FROM ".DB_PREFIX."gruppen
                                  WHERE ID = ".(int)$user_group) or db_die();
            $row2 = db_fetch_row($result2);
            if ($row2[0] == $user_ID) {
                $user_type = 2;
            }
        }
        unset($row);
        include_once(PATH_PRE.'lang/'.LANG.'.inc.php');


        // track the login
        if (PHPR_LOGS) {
            if ((!isset($dbTSnull)) || $dbTSnull == 0) {
                $dbTSnull = date('YmdHis', mktime(date('H')+PHPR_TIMEZONE, date('i'), date('s'), date('m'), date('d'), date('Y')));
            }
            $result2 = db_query("INSERT INTO ".DB_PREFIX."logs
                                        (        von      ,   login    )
                                 VALUES (".(int)$user_ID.", '$dbTSnull')") or db_die();
            // store logID for the logout
            $result2 = db_query("SELECT ID
                                   FROM ".DB_PREFIX."logs
                                  WHERE von = ".(int)$user_ID."
                                    AND login = '$dbTSnull'") or db_die();
            $row2 = db_fetch_row($result2);
            $logID = $row2[0];
        }
        // crypt password in session
        $user_pw = $enc_pw;

        // register user variables in session
        $_SESSION['user_ID']        =& $user_ID;
        $_SESSION['user_name']      =& $user_name;
        $_SESSION['user_firstname'] =& $user_firstname;
        $_SESSION['user_pw']        =& $user_pw;
        $_SESSION['user_group']     =& $user_group;
        $_SESSION['user_kurz']      =& $user_kurz;
        $_SESSION['user_type']      =& $user_type;
        $_SESSION['user_loginname'] =& $user_loginname;
        $_SESSION['user_email']     =& $user_email;
        $_SESSION['langua']         =& $langua;
        $_SESSION['loginstring']    =& $loginstring;
        $_SESSION['user_pwenc']     =& $user_pwenc;
        $_SESSION['logID']          =& $logID;
        $_SESSION['user_smsnr']     =& $user_smsnr;
        $_SESSION['user_all_groups']=& $user_all_groups;
        $_SESSION['members_data']   =& $members_data;
        $_SESSION['logged_in']      = true;
        $_SESSION['settings']       = $settings;
        get_module_data();


        // Remember me: after login, if the login page was displayed, we wll update the login token stored on database
        if (isset($_REQUEST['remember_me']) && isset($_REQUEST['user_pw'])) {

            // 1st, deleting old token (just cleaning the database)
            $query = "DELETE
                        FROM ".DB_PREFIX."logintoken
                      WHERE user_ID = ".(int)$user_ID."
                        AND url LIKE '".PHPR_HOST_PATH.PHPR_INSTALL_DIR."index.php?&logintoken=%'";
            db_query($query) or db_die();

            // Including notification lib because there are the token stuff
            include_once(LIB_PATH."/notification.inc.php");

            // Creating a token
            $remember_me_token = Notification::create_logintoken(PHPR_HOST_PATH.PHPR_INSTALL_DIR."index.php",$user_ID,$user_ID, 7);

            // We will get only the md5 part of the token
            $remember_me_token = substr($remember_me_token,strpos($remember_me_token, '=')+1);

            // Setting the token on a cookie to remember the user across sessions
            setcookie('remember_me_token',$remember_me_token, time()+60*60*24*7,'/'); // 7 days
            $_SESSION['do_not_expire_login'] = true;

        }
        elseif (isset($_REQUEST['user_pw']) && isset($_REQUEST['loginstring'])) {

            // If the remember me checkbox was not checked, it that case we will delete all the old tokens
            $query = "DELETE
                        FROM ".DB_PREFIX."logintoken
                       WHERE user_ID = ".(int)$user_ID."
                         AND url LIKE '".PHPR_HOST_PATH.PHPR_INSTALL_DIR."index.php?&logintoken=%'";
            db_query($query) or db_die();

        }

    }
    // is required everytime
    // fetch settings
    $settings = $_SESSION['settings'];
    if ($settings) {
        foreach ($settings as $key => $value) {
            if ($value <> '') {
                $$key = $value;
            }
        }
    }

    // special check for expert filter
    if (PHPR_EXPERT_FILTERS == 1) {
        if (is_array($flist)) {
            foreach ($flist as $key => $value) {
                if (is_string($value)) {
                    $flist[$key] = stripslashes($value);
                }
            }
        }
    }

    // do the date format stuff
    require_once(LIB_PATH.'/date_format.php');
    $date_format_object = new Date_Format($date_format);
    $date_format = $date_format_object->get_user_format();
    // set time
    $dbTSnull = date('YmdHis', mktime(date('H')+PHPR_TIMEZONE, date('i'), date('s'), date('m'), date('d'), date('Y')));

}


?>
