<?php

// auth.inc.php - PHProjekt Version 5.2
// copyright    2000-2005 Albrecht Guenther  ag@phprojekt.com
// www.phprojekt.com
// Author: Albrecht Guenther, $Author: nina $
// $Id: auth.inc.php,v 1.65.2.12 2007/06/26 10:47:50 nina Exp $

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

// set default skin
if (!isset($skin)) {
    $skin = PHPR_SKIN;
}

// check for the appropiate login field ...
if (!PHPR_LOGIN_SHORT) {
    $label      = __('Last name');
    $field_name = 'nachname';
}
else if (PHPR_LOGIN_SHORT == '1') {
    $label      = __('Short name');
    $field_name = 'kurz';
}
else if ((PHPR_LOGIN_SHORT == '2') || (PHPR_LDAP == '1')) {
    $label      = __('Login name');
    $field_name = 'loginname';
}


// no values from the session or the login form?-> show login form
if (!$user_pw and !$_SESSION['logged_in']) {
    set_style();
    
    // Remember Me: We will check if there are a remember me token
    if (isset($_COOKIE['remember_me_token']) && is_md5($_COOKIE['remember_me_token'])) {
        
        // Getting the token from cookie
        $logintoken = qss($_COOKIE['remember_me_token']);
        
        // The login token based on remember me cookie will not take care about the expiration date on database
        $do_not_expire_token = true;
        
        // update cookie token expiration time to keep it alive one more week
        setcookie('remember_me_token', $logintoken, time()+60*60*24*7,'/'); // 7 days
        $_SESSION['do_not_expire_login'] = true;
    }
    
    if (!empty($logintoken) && is_md5($logintoken)) {
        $query = "SELECT l.user_ID, l.valid, l.used, l.ID, u.status, u.settings 
                    FROM ".DB_PREFIX."logintoken l, ".DB_PREFIX."users u
                   WHERE l.token = '$logintoken'
                     AND l.user_ID = u.ID
                     AND u.status = 0";
        $result = db_query($query) or db_die();
        $row = db_fetch_row($result);
        
        if (isset($row[5]) && strlen($row[5]) > 0 ) {
            $temp_settings = unserialize($row[5]);
            $temp_allow_logintoken = (isset($temp_settings['allow_logintoken']))? $temp_settings['allow_logintoken']:'';
        }
        
        //if ($now > mktime(substr($row[1], 8, 2), substr($row[1], 10, 2), substr($row[1], 12, 2), substr($row[1], 4, 2), substr($row[1], 6, 2), substr($row[1], 0, 4))) {
        if ($row[4] == '1') {
            // append return path to redirect the user to where he wanted to go
            // the return path might already be encoded
            $return_path = urldecode(xss($_REQUEST['return_path']));
            $return_path = '?return_path='.urlencode($return_path);
            die(set_page_header().__('Sorry you are not allowed to enter.')."!<br /><a href='index.php".$return_path."'>".__('back')."</a> ...\n</div>\n</body>\n</html>\n");
        }
        if ($temp_allow_logintoken <> 1 && (!$do_not_expire_token)) {
            $return_path = urldecode(xss($_REQUEST['return_path']));
            $return_path = '?return_path='.urlencode($return_path);
            die(set_page_header().__('Your user do not allow login with logintoken. To change this setting enable it on Settings section.')."!<br /><a href='index.php".$return_path."'>".__('back')."</a> ...\n</div>\n</body>\n</html>\n");
        }
        else if (($dbTSnull > $row[1]) && (!$do_not_expire_token)) { // remember me token will not expire
            die(__('Your token has already been expired.'));
        }
        else if ($row[2] <> '') {
            die(__('Your token has already been used.<br />If it wasnt you, who used the token please contact your administrator.'));
        }
        else {
            $fetch_uservalues = $row[0];
            
            // Setting token as used. For remember me token we will not set the used value because we can use it several times
            if (!$do_not_expire_token) {
                $query = "UPDATE ".DB_PREFIX."logintoken
                                 SET used = '".date('YmdHis', time() + PHPR_TIMEZONE * 3600)."'
                               WHERE ID = ".(int)$row[3];
                $result = db_query($query) or db_die();
            }
        }
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
    if (isset($file) && $file == 'admin') {
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
                    $enc_pw_enhanced = md5('phprojektmd5'.$user_pw);
                }
            }
            else if (PHPR_PW_CRYPT == 1 && !isset($_SESSION['user_pw'])) {
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
                $enc_pw_enhanced = $user_pw;
            }
            // great! I found an entry for you!
            
            if (($row[1] == $enc_pw) || (($row[1] == $enc_pw_enhanced) && ($enc_pw_enhanced != ''))) {

                // Is not md5? update to md5+prefix encryption
                if (($row[1] != $enc_pw_enhanced) && (!isset($_SESSION['user_pw'])) && ($enc_pw_enhanced <> '') && PHPR_PW_CRYPT == 1) {
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
                             WHERE ID =".(int)$fetch_uservalues) or db_die();
        $row = db_fetch_row($result);
        // fill the user data into variables
        if (!empty($logintoken)) {
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
        
        
        
        // fetch access
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
        if ($enc_pw != '') {
            $user_pw = $enc_pw;
        }

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
        
        
        // Remember me: after login, if the login page was displayed, we wll update the login token stored on database
        if (isset($_REQUEST['remember_me']) && isset($_REQUEST['user_pw'])) {
            
            // 1st, deleting old token (just cleaning the database)
            $query = "DELETE
                        FROM ".DB_PREFIX."logintoken
                      WHERE user_ID = ".(int)$user_ID."
                        AND url LIKE '".PHPR_HOST_PATH.PHPR_INSTALL_DIR."index.php?&logintoken=%'";
            db_query(xss($query)) or db_die();
           
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
    // do the date format stuff
    require_once(LIB_PATH.'/date_format.php');
    $date_format_object = new Date_Format($date_format);
    $date_format = $date_format_object->get_user_format();
    // set time
    $dbTSnull = date('YmdHis', mktime(date('H')+PHPR_TIMEZONE, date('i'), date('s'), date('m'), date('d'), date('Y')));

}


?>
