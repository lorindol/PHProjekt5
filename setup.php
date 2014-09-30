<?php

// setup.php - PHProjekt Version 5.2
// copyright © 2000-2005 Albrecht Guenther  ag@phprojekt.com
// www.phprojekt.com
// Author: Albrecht Guenther, $Author: polidor $
// $Id: setup.php,v 1.55.2.6 2007/06/07 04:33:36 polidor Exp $
// set some variables
error_reporting(0);

/*
Definition and include section
*/
// Previous definitions, I'll not touch this...
// bypass lib authentication (will use it's own) ...
define('avoid_auth','1');
define('PATH_PRE','./');
if (session_id() == "") {
    session_start();
}
if (!defined('PHPR_SESSION_NAME')) define('PHPR_SESSION_NAME', 'PHPRSETUP');

// include routine for session_register
include_once('lib/gpcs_vars.inc.php');

// include specific setup functions
include_once('setup/setup_functions.php');

/*
Some checks before starting
*/
// Check step
$step = (isset($_POST['step']))? $_POST['step'] : (isset($_SESSION['step']))? $_SESSION['step'] :'initial';

// Check mode
$mode = (isset($_POST['mode']))? $_POST['mode'] : (isset($_SESSION['mode']))? $_SESSION['mode'] : 'basic';

// Check language
$langua = check_language();
$_SESSION['langua'] = $langua;
$GLOBALS['langua'] = $langua;
define('LANG',$langua);

// If language ok, then we can start the translation
include_once("./lang/".LANG.".inc.php");

function __($textid)
{
    return (isset($GLOBALS['_lang'][$textid]) && $GLOBALS['_lang'][$textid] <> '') ? $GLOBALS['_lang'][$textid] : $textid;
}


// Check PHP version. Note: if php version < 3, please update
if (substr(phpversion(),0,1) == '3')
{
    alert_message(__('<b>Sorry, PHP 4 or 5 required!</b><br /><br /> Please download the current version at <a href="http://www.php.net">www.php.net</a>'));
}

// Check config.inc.php is writable
if (file_exists("config.inc.php") || file_exists("config.inc.php"))
{
    if (!is_writable("config.inc.php") and !is_writable("../../config.inc.php"))
    {
        alert_message(__("<br /><b>PANIC! config.inc.php can't be written! Please ensure that the webserver is able to write a new config"));
        die();
    }
}
else
{
    // config file don't exists, let's try to create

    $fp = @fopen("config.inc.php", 'wb+');
    // impossible to write the test file? -> error message
    if (!$fp)
    {
        alert_message(__('Alert: Cannot create file config.inc.php!<br />The webserver needs the permission to write the file config.inc.php in the PHProjekt root directory.'));
        die();
    }
    else
    {
        fclose($fp);
        unlink("config.inc.php");
        $_SESSION['ok'] =& $ok;

        // the config.inc.php file didn't exists: then it is an installation
        $configure = false;
        $_SESSION['configure'] = false;
    }

}


// Check session. Check if is registered the lang session value
//if (!is_dir(session_save_path()) and !preg_match("/^[a-zA-Z0-9]*\.[a-zA-Z0-9]*$/", session_save_path())) Note: deprecated: the session_save_path could return a not valid directy
if (!isset($_SESSION['langua']))
{
    alert_message(__('<b>There is no path to store the sessions given in the php.ini. Please run the session test in the tst script env_test.php to check whether sessions work in your environment. If this is not the case then you should define a temp path in the variable session.save_path which has write permissions for the webserver.<br /><br />'));
}

// Check if config exists. NOTE: If exissts, then this will be a configuration or upgrade, not a installation.
if ((file_exists("./config.inc.php") and filesize('./config.inc.php') > 0) or
(file_exists('../../config.inc.php') and filesize('../../config.inc.php') > 0))
{

    // Check if user (root) logged in
    if (!isset($_SESSION['ok']))
    {

        // if not logged in, then we will check if he sent the login request
        if (isset($_REQUEST['admin_pw']))
        {

            include_once('./lib/lib.inc.php');

            if ((!isset($db_type)) && defined("PHPR_DB_TYPE")) {
                $db_type = PHPR_DB_TYPE;
            }

            define('DB_TYPE',$db_type);
            include_once('./lib/db/'.DB_TYPE.'.inc.php');

            constants_to_vars();

            $result = db_query("SELECT pw, usertype, gruppe, acc
                          FROM ".DB_PREFIX."users
                          WHERE nachname = '$loginstring'");

            if (is_bool($result) && $result === false || ($result === true && $db_type = 'postgresql')) {
                $result = db_query("SELECT pw, ID, gruppe, acc
                          FROM ".DB_PREFIX."users
                          WHERE nachname = '$loginstring'");
            }

            // we will check if a row has received
            $pass_tested = false;

            while ($row = db_fetch_row($result))
            {
                // check we received a row
                $pass_tested = true;
                $enc_pw_enhanced = 'dummy value';

                // crypting password
                if (PHPR_PW_CRYPT == 1 || $pw_crypt == 1) {

                    if (strlen($row[0]) == 32) {
                        $enc_pw = md5($admin_pw);
                        $enc_pw_enhanced = md5('phprojektmd5'.$admin_pw);
                    } else {
                        $enc_pw = encrypt($admin_pw, $row[0]);
                    }
                    
                }
                else $enc_pw = $admin_pw;

                // check authentification
                // Note: The second part of the or is necessary to allow enter admin users from version older than 5.2
                if (($row[1] == 3 && ($enc_pw == $row[0] || $enc_pw_enhanced == $row[0]) && $row[2] == 0) || ($row[3] == 'an' && ($enc_pw == $row[0] || $enc_pw_enhanced == $row[0]) && $row[2] == 0))
                {
                    $ok = 1;  // admin password valid
                    $_SESSION['ok'] = 1;

                    // config.inc.php exists, then this is a configuration or update. Also, this is the first step inside the setup.
                    $configure = true;
                    $_SESSION['configure'] = true;
                    $_SESSION['step'] = 'initial';
                    $_SESSION['step'] = $step;
                    
                    // *********************
                    // Test account password
                    // *********************
                    
                    // Getting password for test user (only if status = 0)
                    if (isset($version) && substr($version,0,1) == '4') {
                        // the table user has not status value
                        $query = "SELECT pw 
                          FROM ".DB_PREFIX."users 
                          WHERE nachname = 'test' AND vorname = 'test'";
                    }
                    else {
                        $query = "SELECT pw 
                          FROM ".DB_PREFIX."users 
                          WHERE nachname = 'test' AND vorname = 'test' and status = 0";
                    }
                    $result = db_query($query);
                    
                    // there are a test user on system
                    if ($row = db_fetch_row($result)) {
                        $test_current_pass = $row[0];
                        
                        if ($test_current_pass == 'test' || $test_current_pass == crypt('test','test') || $test_current_pass == md5('test')) {
                            $_SESSION['change_test'] = true;
                        }
                        
                        
                    }
                    

                }
                else
                {
                    // not an admin password
                    // destroy the session - on some system the first, on some system the second function doesn't work :-))
                    @session_unset();
                    @session_destroy();
                    alert_message(__('This is not an admin login combination! Please check whether you have given the <u>last</u> name of the admin login!'));
                }
            }

            // the query retunrs 0 rows
            if (!$pass_tested) {
                // not an admin password
                // destroy the session - on some system the first, on some system the second function doesn't work :-))
                @session_unset();
                @session_destroy();
                alert_message(__('This is not an admin login combination! Please check whether you have given the <u>last</u> name of the admin login!'));
            }
        }
        else
        {
            // User not loggued in and login information don't send... let's show the login page
            display_login ();
            die();
        }
    }

}



/*
Process setup
*/
define("setup_included", "1");

// include the configuration array (with all setup fields
include_once('setup/setup_configuration.php');

// ************************
// Test user password check
// ************************

if (isset($_SESSION['change_test']) && $_SESSION['change_test']) {
    $config_array['testcreation']['noconfigure'] = false;
    $config_array['testpass']['noconfigure']     = false;
    $config_array['testpass2']['noconfigure']    = false;
}


// First, if configurable (noconfigurable=false), then, we will include the config.inc.php
if ($configure == true)
{
    include_once("config.inc.php");

    // get the old values and store as '_old values
    $old_config_array = get_defined_constants();

    if (isset($old_config_array['PHPR_VERSION']) && $old_config_array['PHPR_VERSION'] < $config_array['version']['default']) {
        $setupType = 'upgrade';
    }
    elseif (!isset($old_config_array['PHPR_VERSION'])) {

        // to allow compativility with versions older than 5.0
        if (isset($version)) {
            $old_config_array = array();
            foreach ($config_array as $oneName => $oneField) {
                if (isset($oneField['old_name'])) {
                    $oneNameOld = $oneField['old_name'];
                }
                else {
                    $oneNameOld = $oneName;
                }
                if (isset($$oneNameOld)) {
                    $cons_name = 'PHPR_'.strtoupper($oneName);
                    $old_config_array[$cons_name] = $$oneNameOld;
                }
            }
            $setupType = 'upgrade';

        }
        else {
            $setupType = 'installation';
        }
    }
    else {
        $setupType = 'configuration';
    }

}
else {
    $setupType = 'installation';
}

// Well, after all checks, lets start processing
if (isset($_POST)) // && !isset($_POST['admin_pw']))
{
    // This function will save on $_SESSION all the values sent by $_POST

    // If we are on advenced or basic or default step, it will be necessary to validate the values received by post.
    if (isset($_POST['action_setup_advanced']) || isset($_POST['action_setup_basic']) || isset($_POST['action_setup_default']))
    {
        $validate = true;
    }
    else
    {
        $validate = false;
    }

    // this function will save the post information received from previous page.
    save_form_values($_POST,$config_array, $validate, $old_config_array);

    // select the next step (the step to be displayed). This depends on the button pressed.
    if (isset($_POST['action_setup_basic']))
    {
        $step = 'configuration';
        $mode = 'basic';
        $_SESSION['mode'] = $mode;
        $_SESSION['step'] = $step;
    }
    elseif (isset($_POST['action_setup_advanced']))
    {
        $step = 'configuration';
        $mode = 'advanced';
        $_SESSION['mode'] = $mode;
        $_SESSION['step'] = $step;
    }
    elseif (isset($_POST['action_setup_go_back']))
    {
        $step = 'configuration';
        $mode = $_SESSION['mode'];
        $_SESSION['step'] = $step;
    }
    elseif (isset($_POST['action_setup_finish']))
    {
        $step = 'summary';
        $_SESSION['step'] = $step;

    }
    // This option (do or default option) will call the step3.php, to install phprojekt.
    elseif (isset($_POST['action_setup_default']) || (isset($_POST['action_setup_do'])))
    {
        if ($configure)
        {
            if (isset($old_config_array['PHPR_VERSION']) && $old_config_array['PHPR_VERSION'] < $config_array['version']['default'])
            {
                $setup = 'update';
                $_SESSION['setup'] = 'update';
            }
            else
            {
                $setup = 'configure';
                $_SESSION['setup'] = 'configure';
            }
        }
        else
        {
            $setup = 'install';
            $_SESSION['setup'] = 'install';
        }


        // If database didn't exists, we will try to create it
        test_db_connection(false);

        include_once("setup/step3.php");
        die();

    }
    else
    {
        // If no button pressed we will display the initial
        $step = 'initial';
        $mode = 'basic';
        $_SESSION['step'] = $step;
        $_SESSION['mode'] = $mode;
    }

}


// Getting the correct charset depending on languaje
$lcfg = get_char_set($langua);

// starting with header
display_header();

// content div
//echo "<div class='inner_content'><br /><div class='boxHeader'>".__('Setup PHProjekt')."</div>";

// the help box (messages before display the setup form
display_help($step,$mode,$setupType);

// process configuration array
if (isset($config_array))
{
    // this flag will check if a separator was displayed
    $first_separator = true;

    // the content div
    echo "<div class='boxContent'>"; // <fieldset class='settings'><legend>".__('Setup PHProjekt')."</legend>";

    // We will get each value and...
    foreach ($config_array as $oneValue => $oneField)
    {

        //... this is the corresponding step and...
        if ($oneField['step'] == $step || $step == 'summary')
        {
            // ... this is the corresponding 'configurable' and...
            if (!$oneField['noconfigure'] || $configure == false)
            {

                // at last, the corresponding mode
                if ($oneField['mode'] == 'basic' || $oneField['mode'] == $mode)
                {

                    // at really last, we will check if exists a 'check' function applied to field
                    $var_checked = true;

                    // if is set a check value, and the function exists, we will perform the check function before display the field.
                    if (isset($oneField['check']) && function_exists($oneField['check']))
                    {
                        $var_checked = $oneField['check']();
                    }
                    if ($var_checked)
                    {
                        //ready to start

                        // getting the actual value (1st session, 2nd config.inc.php (old value if exists), 3th default, 4th callback, 5th empty)
                        if (isset($_SESSION[$oneValue]))
                        {
                            $field_value = $_SESSION[$oneValue];

                        }
                        elseif (isset($old_config_array["PHPR_".strtoupper($oneValue)]))
                        {
                            $field_value = $old_config_array["PHPR_".strtoupper($oneValue)];

                        }
                        elseif (isset($oneField['default']))
                        {
                            $field_value = $oneField['default'];

                        }
                        elseif (function_exists($oneField['callback']))
                        {
                            $field_value = $oneField['callback']();

                        }
                        else
                        {
                            $field_value = '';
                        }

                        // Echo the field label (separators and hidden fields haven't label)
                        if ($oneField['type']<> 'separator' && $oneField['type']<> 'hidden')
                        {
                            echo "<label for='$oneValue' class='label_block'>{$oneField['label']}:</label>";
                        }

                        // on summary page, all the fields are converted to text (you can't change any value)
                        if ($step == 'summary')
                        {
                            if ($oneField['type']<> 'separator' && $oneField['type']<> 'hidden')
                            {
                                $original_type = $oneField['type'];
                                $oneField['type'] = 'label';
                            }
                        }

                        // Each different kind of form field will be displayed different.
                        switch($oneField['type'])
                        {
                            case "text":

                                if (isset($oneField['size']))
                                {
                                    $field_size = $oneField['size'];
                                }
                                else {
                                    $field_size = 25;
                                }

                                echo "<input type='text' name='$oneValue' value='$field_value' size='$field_size'  class='settings_options'>\n";

                                break;

                            case "label":
                                if ($original_type == 'text')
                                {
                                    echo $field_value;
                                }
                                elseif ($original_type == 'select')
                                {
                                    if (isset($oneField['values'][$field_value]))
                                    {
                                        echo $oneField['values'][$field_value];
                                    }
                                    else
                                    {
                                        echo $field_value;
                                    }
                                }
                                elseif($original_type == 'checkbox')
                                {
                                    if ($field_value == 1) {
                                        echo "X";
                                    }
                                }
                                break;

                            case "password":

                                if (isset($oneField['size']))
                                {
                                    $field_size = $oneField['size'];
                                }
                                else
                                {
                                    $field_size = 25;
                                }

                                echo "<input type='password' name='$oneValue' value='$field_value' size='$field_size'  class='settings_options'>\n";

                                break;

                            case "hidden":

                                echo "<input type='hidden' name='$oneValue' value='$field_value'>";

                                break;

                            case "checkbox":
                                echo "<input type='checkbox' name='$oneValue' value='1' ";
                                if ($field_value == 1 || $field_value === 'Y')
                                {
                                    echo " checked";
                                }
                                if (isset($oneField['extra_code'])) {
                                    echo " ".$oneField['extra_code'];
                                }
                                
                                echo "> Activate\n";

                                // this hidden field is created to confirm the existence of the checkbox when saving the information
                                echo "<input type='hidden' name='hidden_$oneValue' value='yes' />";

                                break;

                                // This special case is for check database button.
                            case "button":
                                echo "<input type='hidden' name='button_pressed' value=''>";
                                echo "<input type='button' name='$oneValue' value='$field_value' class='button'";
                                if ($oneField['on_click'] <> '')
                                {
                                    echo " onclick=\"{$oneField['on_click']}\"";
                                }
                                echo ">\n";

                                if (isset($oneField['alert']) && function_exists($oneField['alert']) && $oneValue == $_POST['button_pressed']) {
                                    $_SESSION['dont_die'] = true;
                                    if ($oneField['alert']() === true)
                                    {
                                        echo __('Seems that You have a valid database connection!');
                                    }
                                    $_SESSION['dont_die'] = false;
                                }

                                break;

                            case "select":
                                // lets get the range: 1st values field, 2nd get_values, 3th empty
                                if (isset($oneField['values']) && is_array($oneField['values']))
                                {
                                    $field_range = $oneField['values'];
                                }
                                elseif (isset($oneField['range']) && function_exists($oneField['range']))
                                {
                                    $field_range = $oneField['range']();
                                }
                                else
                                {
                                    $field_range = array();
                                }

                                // writing the select
                                echo "<select name='$oneValue' id='$oneValue' class='settings_options'>";
                                foreach ($field_range as $key => $value)
                                {
                                    echo "<option value='$key'";
                                    if ($key == $field_value)
                                    {
                                        echo " selected";
                                    }
                                    echo ">$value</option>\n";
                                }
                                echo "</select>\n";

                                break;

                            case "separator":
                                if (!$first_separator)
                                {
                                    //echo "</legend></fieldset>\n";
                                    echo "</fieldset>\n";
                                }
                                else
                                {
                                    $first_separator = false;
                                }
                                echo "<fieldset class='settings'>\n<legend>\n".$oneField['label']."</legend>\n";
                                break;

                        }
                        if ($oneField['type']<> 'separator' && $oneField['type']<> 'hidden')
                        {
                            echo "<br /><br />\n";
                        }
                    }
                }

            }

        }

    }
    //echo "</legend></fieldset>";
    echo "</fieldset>";

    // the action buttons
    display_buttons($step,$mode,$setupType);

    echo "</div>";

}
else {
    alert_message(__('Configuration array not found.'));
}
display_botton();
?>
