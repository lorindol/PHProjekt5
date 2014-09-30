<?php

// setup_function.php - PHProjekt Version 5.2
// copyright  ©  2000-2006 Albrecht Guenther  ag@phprojekt.com
// www.phprojekt.com
// Author: Eduardo Polidor, $Author: polidor $
// $Id: setup_functions.php,v 1.40.2.5 2007/05/22 05:02:56 polidor Exp $

/*
This functions will be used on setup script. The check_* functions are functions to get a 'default'
or 'correct' value.
The get_*_options functions will create an array with all options for a field.
The display_* functions just have html parts to be printed.
*/

/**
 * This function tries to determine the correct language for installation. 
 *
 * @return string The language code (e.g. 'de').
 */
function check_language()
{
    // This function will try to get the language on the following order:
    // request, session, http request (browser), en (default)

    // 1st: the request (the user has selected his prefered language
    if (isset($_REQUEST['langua']) && strlen($_REQUEST['langua']) < 3 && preg_match("/^[0-9a-zA-Z]*$/", $_REQUEST['langua'])) 
    {
        $langua = $_REQUEST['langua'];
    }
    // 2nd: the session (the user has previously selected his language
    elseif (isset($_SESSION['langua']))
    {
        $langua = $_SESSION['langua'];
    }
    // 3th: the http request, aka the client browser.
    else
    {
        // Note: using getallheaders instead of apache_request_headers for compatibility with versions < 4.3.0
        // Note: getallheaders does not exist in other sapis than apache, then we will check it
        if (function_exists('getallheaders')) {
            $headers = getallheaders();
        } else {
            $headers['Accept-Language'] = $_ENV['HTTP_ACCEPT_LANGUAGE'];
        }

        // Lets check the Accept-Lenguage
        if (isset($headers['Accept-Language']) && $headers['Accept-Language'] <> '')
        {
            $langua = substr($headers['Accept-Language'],0,2);

            // Now, lets check the existence of this language
            if (!file_exists('lang/'.$langua.'.inc.php')) {
                // Why en? whe have found the languange header, but we haven't it on our available languages
                $langua = 'en';
            }
        }
        else
        {
            // why en? because whe didn't found the Accept-Language header.
            $langua = 'en';
        }
    }

    return $langua;

}


/**
 * Used to check if localhost is a valid host if the host is not provided
 *
 * @return string 'Localhost' if is a valid mysql server
 */
function check_db_host()
{
    // Trying to know if 'localhost' is a valid mysql server.
    $toReturn = '';

    if (isset($_SESSION['db_host']))
    {
        $toReturn = $_SESSION['db_host'];
    }
    elseif (function_exists('mysql_connect'))
    {
        $result = mysql_connect('localhost','root','');
        if ($result) {
            $toReturn = 'localhost';
            $resutl = mysql_close($result);
        }
    }

    return $toReturn;
}

/**
 * Checks if gd library is installed (necesary to create charts)
 *
 * @return boolean depending if the library gd is installed or not.
 */
function check_support_chart()
{
    $toReturn = 0;

    if (extension_loaded('gd'))
    {
        $toReturn = 1;
    }

    return $toReturn;
}

/**
 * Return the correct mail end of line depending on the operating system of the server
 *
 * @return string the espefic end of line for the server
 */
function check_mail_eol()
{
    $toReturn = '\r\n';

    if (isset($_SERVER["OS"]) && strpos(strtolower($_SERVER["OS"]), 'windows') !== false)
    {
        $toReturn = '\r\n'; // end of line in body; e.g. \r\n (conform to RFC 2821 / 2822)
    }
    elseif (isset($_SERVER["OS"]) && strpos(strtolower($_SERVER["OS"]), 'mac') !== false)
    {
        $toReturn = '\r'; // end of line in body; e.g. \r\n (conform to RFC 2821 / 2822)
    }
    else
    {
        $toReturn = '\n'; // end of line in body; e.g. \r\n (conform to RFC 2821 / 2822)
    }
    return $toReturn;
}



/**
 * This function tries to find a valid user for mysql connections
 *
 * @return string A suggested user to be used on mysql connection
 */
function check_db_user()
{
    // trying to check if 'root' no pass is a valid user
    $toReturn = '';

    if (isset($_SESSION['db_user']))
    {
        $toReturn = $_SESSION['db_user'];
    }
    elseif (function_exists('mysql_connect'))
    {
        $result = mysql_connect('localhost','root','');
        if ($result) {
            $toReturn = 'root';
            $resutl = mysql_close($result);
        }
    }

    return $toReturn;
}

/**
 * Checks if imap functions are installed
 *
 * @return boolean if imap is installed
 */
function check_quickmail()
{
    // trying to check if imap functions exist
    $toReturn = 1;

    if (function_exists('imap_open'))
    {
        $toReturn = 2;
    }

    return $toReturn;
}

/**
 * Try to find for mysql a valid database name to use on installation.
 *
 * @return string suggested database name
 */
function check_db_name()
{
    // looking for phprojekt database
    $toReturn = '';

    if (isset($_SESSION['db_name']))
    {
        $toReturn = $_SESSION['db_name'];
    }
    elseif (function_exists('mysql_connect'))
    {
        $result = mysql_connect('localhost','root','');
        if ($result)
        {
            $result = mysql_select_db('phprojekt');
            if ($result)
            {
                $toReturn = 'phprojekt';
            }
            elseif (mysql_query("create database phprojekt"))
            {
                $toReturn = 'phprojekt';
                mysql_query('drop database phprojekt');
            }

            $resutl = mysql_close();
        }
    }

    return $toReturn;
}


/**
 * Tries to find a valid table prefix for installation
 *
 * @return string suggested prefix (e.g. 'phpr_')
 */
function check_db_prefix()
{
    // will try to select the prefix starting with phpr_* and going to phpr5000_* prefix

    $toReturn = 'phpr_';

    if (isset($_SESSION['db_prefix']))
    {
        $toReturn = $_SESSION['db_prefix'];
    }
    elseif (function_exists('mysql_connect'))
    {
        $result = mysql_connect('localhost','root','');
        if ($result)
        {
            $result = mysql_select_db('phprojekt');
            if ($result)
            {
                // we have a database!! let's try the name of tables
                $result = mysql_query("show tables like '%users'");
                $temp = array();
                while ($db_row = mysql_fetch_row($result)) {
                    $temp[] = $db_row[0];
                }
                if (in_array('phpr_users',$temp)) {
                    $i = -1;
                    $found = false;
                    while (($i < 5000) && !$found) {
                        $i++;
                        if (!in_array('phpr'.$i.'_users',$temp)) {
                            $found = true;
                        }
                    }
                    if ($found) {
                        $toReturn = 'phpr'.$i.'_';
                    }
                }
                else {
                    $toReturn = 'phpr_';
                }

            }
            $resutl = mysql_close();
        }
    }

    return $toReturn;
}


/**
 * Gets the list of languages availables on the current versin
 *
 * @return array A list of available languages
 */
function get_language_options()
{
    $lang_options = array();
    define('lib_included','yes');
    include './lib/languages.inc.php';

    settype($languages, 'array');
    foreach ($languages as $key => $value) {
        $lang_options[$key] = $value;
    }

    return $lang_options;

}

/**
 * Checks the available libraries installed on PHP to connect to different database engines
 *
 * @return array List of available database connections
 */
function get_db_type_options()
{
    $db_type_options = array();
    if (function_exists('mysql_connect')) $db_type_options['mysql']      = 'MySQL';
    if (function_exists('sqlite_open'))   $db_type_options['sqlite']     = 'SQLite';
    if (function_exists('ocilogon'))      $db_type_options['oracle']     = 'Oracle';
    if (function_exists('ifx_connect'))   $db_type_options['informix']   = 'Informix';
    if (function_exists('pg_connect'))    $db_type_options['postgresql'] = 'Postgres';
    if (function_exists('mssql_connect')) $db_type_options['ms_sql']     = 'MS SQL';
    if (function_exists('ibase_connect')) $db_type_options['interbase']  = 'Interbase';
    // if (function_exists('odbc_connect'))  $db_type_options['odbc']       = 'ODBC';
    if (function_exists('db2_connect'))   $db_type_options['db2']        = 'db2';

    return $db_type_options;
}

/**
 * Creates the option list for quickmail configuration
 *
 * @return array list of email mode options
 */
function get_quickmail_options()
{
    $quickmail_options = array();
    $quickmail_options[1] = __('Send email only');
    $quickmail_options[0] = __('Do not send');
    if(function_exists('imap_open')) $quickmail_options[2] = __('Full mail client');

    return $quickmail_options;
}

/**
 * Checks if ldap library is available
 *
 * @return boolean True if ldap PHP library is installed
 */
function check_ldap()
{
    if (function_exists('ldap_connect'))
    {
        return true;
    }
    else
    {
        return false;
    }
}


/**
 * This function is used to convert into numeric the old PHProjekt installation where
 * the timecard_change variable is not a number
 *
 * @return integer a valid number to be used on timecard
 */
function check_timecard_add()
{

    if (is_numeric(PHPR_TIMECARD_CHANGE))
    {
        return PHPR_TIMECARD_CHANGE;
    }
    else
    {
        return 60;
    }
}


/**
 * Created a list of valid timezones array
 *
 * @return array list of timezones
 */
function get_timezone_options()
{
    $timezone_options = array();
    for ($i=-23; $i<24; $i++) {
        $timezone_options[$i] = $i;
    }
    return $timezone_options;
}


/**
 * Generates a message for the administrator who is installing the system
 *
 * @param string $message message to be displayed
 * @param boolean $die true if the system needs to stop after showing the message
 * @param boolean $buttons true if it is necessary to show the buttons
 */
function alert_message($message, $die = true, $buttons = true) {
    global $step, $mode;
    
    // starting with header
    if (!$_SESSION['dont_die'])
    {
        display_header();
        // echo "<div class='inner_content'><br /><div class='boxHeader'>".__('Setup PHProjekt')."</div>";
        display_help($step,$mode);
        echo "<div class='boxContent'><fieldset class='settings'><legend>".__('Setup PHProjekt')."</legend>";
        echo $message;

        display_botton();

        echo "</legend></fieldset>";
        if ($buttons) {
            display_buttons($step,$mode);
        }
        echo "</div>";
        die();

    }
    else
    {
        $pos = strpos($message,'<br />',2);
        if ($pos > 0)
        {
            echo substr($message,0,$pos);
        }
        else
        {
            echo $message;
        }
    }

}

/**
 * Display the login html information
 *
 */
function display_login () {
    echo '
  <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
  <html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
  <head>
    <title>Setup Login</title>
    <style type="text/css" media="screen">@import "layout/default/default_css.php";</style>
    <!--[if gte IE 5]><style type="text/css" media="screen">@import "layout/default/default_css_ie.php";</style><![endif]-->
    <script type="text/javascript" >var SID = "";</script>
    <script type="text/javascript" src="lib/chkform.js"></script>
     <link rel="shortcut icon" href="favicon.ico" />
  </head>
  <body>
    <div id="global-main">
    <a style="display:none" href="#content" title="go to content">go to content</a>
    <br />
    <br />
    <div style="background-color:#DC6417;height:41px;width:100%">
      <img src="layout/default/img/logo.png">
    </div>
    <br />

    <div style="text-align:center;">
    <div id="logo" style="text-align:center;""></div>
    <form action="setup.php" method="post" name="frm">
        <input type="hidden" name="loginform" value="1" />
        <fieldset style="width: 35em;height: 10em;padding:0em;margin:auto;">
            <legend>Log in, please</legend>
            <label for="loginstring" style="clear:both;float: left;text-align: right;width: 6.3em;margin: 0.4em;"> Login</label>
            <input style=" font-size:1em;float: left;text-align: left;margin: 0.2em;" type="text" tabindex="1" name="loginstring" id="loginstring" size="33" title="Please enter your user name here." /><br />
            <label for="user_pw" style="clear:both;float: left;text-align: right;width: 6.3em;margin: 0.4em;">Password</label>
            <input style=" font-size:1em;float: left;text-align: left;margin: 0.2em;" type="password" tabindex="2" name="admin_pw" id="user_pw" size="33" title="Please enter your password here." /><br style="clear:both"/>
            <br style="clear:both"/>
            <input class="button" style="float: left;text-align: left;margin: 0.2em;margin-left: 6.9em;" type="submit" value="go" title="Click here to login." />
        </fieldset>
    </form>
    
</div>
    <script type="text/javascript">
<!--
    if (document.frm.loginstring.value == "") {
        document.frm.loginstring.focus();
    }
//-->
    </script>
    </div>
  </body>
</html>';

}

/**
 * Save the form information into the session depending on a provided source
 *
 * @param array $source where the new values are stored
 * @param array $template Templated to be used with the source
 * @param boolean $validate determine if is necesary to validate the information or not
 * @param array $old_config_array array with the old values
 * @return boolean true if the save information was sucessful
 */
function save_form_values($source, $template = array(), $validate, $old_config_array = array())
{

    // This function save any value passed by post or try to catch the corresponding 'default' value
    foreach ($template as $oneValue => $oneField) {

        if (isset($source[$oneValue]))
        {
            $_SESSION[$oneValue] = $source[$oneValue];
        }
        else
        {
            $temp_name = "hidden_$oneValue";
            if ($oneField['type'] == 'checkbox' && isset($source[$temp_name]) && $source[$temp_name] == 'yes' && $_SESSION['step']==$oneField['step'] && ($_SESSION['mode']==$oneField['mode'] || $oneField['mode']=='basic') && ($oneField['noconfigure']==false || $_SESSION['configure']<>true)) {

                $_SESSION[$oneValue] = 0;

            }
            else
            {
                // If we haven't a value for some var, we will store the default value
                if (!isset($_SESSION[$oneValue]))
                {
                    if (isset($old_config_array["PHPR_".strtoupper($oneValue)]))
                    {
                        $_SESSION[$oneValue] = $old_config_array["PHPR_".strtoupper($oneValue)];
                    }
                    elseif (isset($oneField['default']))
                    {
                        $_SESSION[$oneValue] = $oneField['default'];
                    }
                    elseif (isset($old_config_array["PHPR_".strtoupper($oneValue)]))
                    {
                        $_SESSION[$oneValue] = $old_config_array["PHPR_".strtoupper($oneValue)];
                    }
                    elseif (function_exists($oneField['callback']))
                    {
                        $_SESSION[$oneValue] = $oneField['callback']();
                    }
                    else
                    {
                        $_SESSION[$oneValue] = '';
                    }
                }
            }
        }
        $$oneValue = $_SESSION[$oneValue];

        if (isset($oneField['validate']) && function_exists($oneField['validate']) && $validate)
        {
            $oneField['validate']();
        }

    }

    return true;
}


/**
 * Tries to connect to a database
 *
 * @param boolean $drop_database will determine if database is necesary to be deleted after testing
 * @return booean true if the test works properly
 */
function test_db_connection($drop_database = true)
{
    //*** db test ***
    $db_type = $_SESSION['db_type'];
    $db_host = $_SESSION['db_host'];
    $db_user = $_SESSION['db_user'];
    $db_pass = $_SESSION['db_pass'];
    $db_name = $_SESSION['db_name'];

    // test mysql access
    if ($db_type == "mysql") {
        if ($link = mysql_connect($db_host, $db_user, $db_pass))
        {

            if (!mysql_select_db($db_name, $link))
            {
                if (!mysql_query("create database $db_name"))
                {
                    alert_message(__('Sorry, I cannot create the database! <br />Please close all browser windows and restart the installation.'));
                    return __('Sorry, I cannot create the database! <br />Please close all browser windows and restart the installation.');
                }
                else
                {
                    if ($drop_database)
                    {
                        mysql_query("drop database $db_name");
                    }
                    return true;

                }
            }
        }
        else
        {
            alert_message(__('Sorry, I cannot connect to the database! <br />Please close all browser windows and restart the installation.'));
            return __('Sorry, I cannot connect to the database! <br />Please close all browser windows and restart the installation.');
        }
    }
    // test sqlite access
    else if ($db_type == "sqlite") {
        $dbfilename = dirname(dirname(__FILE__))."/$db_name.db";
        if (! $link = sqlite_open($dbfilename , 0666, $sqliteerror) ) {
            alert_message(__('Sorry, I cannot create the database!'));
        }
    }
    // test interbase access
    else if ($db_type == "interbase") {
        $db_host2 =  $db_host.":".$db_name;
        $link = ibase_connect($db_host2, $db_user, $db_pass) or alert_message(__('Sorry, I cannot connect to the database! <br />Please close all browser windows and restart the installation.'));
    }
    // test ms_sql
    else if ($db_type == "ms_sql") {
        $link = mssql_connect($db_host, $db_user, $db_pass) or alert_message(__('Sorry, I cannot connect to the database! <br />Please close all browser windows and restart the installation.'));
    }
    // test oracle
    else if ($db_type == "oracle") {
        $link = OCILogon($db_user, $db_pass, $db_name) or alert_message(__('Sorry, I cannot connect to the database! <br />Please close all browser windows and restart the installation.'));
        $datestmt = OCIParse($link, "alter session set NLS_DATE_FORMAT='YYYY-MM-DD HH:MI:SS'");
        OCIExecute($datestmt);
    }
    // test informix
    else if ($db_type == "informix") {
        if ($db_host == "") { $db = $db_name; }
        else { $db = $db_name."@".$db_host; }
        $link = ifx_connect($db, $db_user, $db_pass) or alert_message(__('Sorry, I cannot connect to the database! <br />Please close all browser windows and restart the installation.'));
    }
    // test postgres
    else if ( $db_type == "postgresql" ) {
        echo "<br />Trying to connect to ".$db_name;
        $link = $link = pg_connect((($db_host == "") ? "" : "host= ".$db_host." ").(($db_pass == "") ? "" : "password=".$db_pass." ")."dbname=".$db_name." user=".$db_user);
        echo "<br />$link";
        if (!$link) {
            echo ("<br />Trying to connect to template1");
            $link = pg_connect((($db_host == "") ? "" : "host = ".$db_host." ").(($db_pass == "") ? "" : "password=".$db_pass." ")."dbname=template1 user=".$db_user) or alert_message("Can't connect to db, nor to template1");
            echo("<br />Trying to create database ".$db_name);
            $result = pg_query($link, "CREATE DATABASE ".$db_name) or alert_message("Can't create database ".$db_name);
            echo("<br />Database create, closing connection to template1");
            $link = pg_close($link) or alert_message("Can't close database connection");
            echo("<br />Opening new connection to db ".$db_name);
            $link = pg_connect((($db_host == "" or $db_host == "localhost") ? "" : "host = ".$db_host." ").(($db_pass == "") ? "" : "password=".$db_pass." ")."dbname=".$db_name." user=".$db_user) or alert_message("Can't connect to newly created db.");
        }
    }
    else if ($db_type =="db2") {
  	    
  	    //echo "<br /> Trying to connect to ". $db_name;
	    $conn_string = "DRIVER={IBM DB2 ODBC DRIVER};DATABASE=$db_name;"."HOSTNAME=$db_host;PORT=50000;PROTOCOL=TCPIP;UID=$db_user;PWD=$db_pass;";
 	    $link = db2_connect($conn_string, '', '');
	    if($link){ 
	        echo("Connection to DB2 - Databse ".$db_name."sucessful!");
            db2_close($link);
        }
        else {
            alert_message("Connection to DB2 - Can't connect to ".$db_name);
        }
    }
    //** end test db access

    return true;

}


/**
 * Depending on language selected will return the corresponding charset
 *
 * @param string $langua language code
 * @return string with the charcode
 */
function get_char_set($langua) {


    if (eregi('pl|cz|hu|si',$langua)) {   $dir_tag = 'ltr'; $LANG_CODE = 'ISO-8859-2'; }
    else if (eregi('sk',$langua)) {       $dir_tag = 'ltr'; $LANG_CODE = 'windows-1250'; }
    else if (eregi('ru|uk',$langua)) {    $dir_tag = 'ltr'; $LANG_CODE = 'windows-1251'; }
    else if (eregi('he',$langua)) {       $dir_tag = 'rtl'; $LANG_CODE = 'windows-1255';}
    else if (eregi('lv|lt|ee',$langua)) { $dir_tag = 'ltr'; $LANG_CODE = 'windows-1257';}
    else if (eregi('tw',$langua)) {       $dir_tag = 'ltr'; $LANG_CODE = 'big5';}
    else if (eregi('zh',$langua)) {       $dir_tag = 'ltr'; $LANG_CODE = 'gb2312';}
    else if(eregi('jp',$langua)) {        $dir_tag = 'ltr'; $LANG_CODE = 'EUC-JP';}
    else if(eregi('kr|ko',$langua)) {     $dir_tag = 'ltr'; $LANG_CODE = 'EUC-KR';}
    else if(eregi('th',$langua)) {        $dir_tag = 'ltr'; $LANG_CODE = 'utf8';}
    else {                                $dir_tag = 'ltr'; $LANG_CODE = 'ISO-8859-1'; }
    if(!defined('LANG_CODE')){
        define('LANG_CODE', $LANG_CODE);
    }
    $lcfg = 'charset='.LANG_CODE;
    return $lcfg;

}



/**
 * Shows te header of the setup routine
 *
 * @param string $lcfg charset to be used
 * @return true if display works properly
 */
function display_header ($lcfg = 'charset=iso-8859-1') {
    echo '
  <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
  <html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
    <head>
      <title>Setup</title>
      <style type="text/css" media="screen">@import "layout/default/default.css";</style>
      <!--[if gte IE 5]><style type="text/css" media="screen">@import "layout/default/default_ie.css";</style><![endif]-->
  
      <script type="text/javascript">var hiliColor = "#ffffff"; var markColor = "#E6DE90"; var sessid = "";</script>
      <script type="text/javascript">var SID = "";</script>
      <link rel="shortcut icon" href="favicon.ico">
      <meta http-equiv="Content-Type" content="text/html; '.$lcfg.'">
    </head>
    <body>
    
      <div id="global-main">
      <form name="setup_form" action="setup.php" method="post" onsubmit="return validate_form()">
      <div id="global-navigation">
       <!--  <img id="logo" src="layout/default/img/logo.png" />
        <br class="navbr">
        <br class="navbr"> -->

      </div>
      
      <div id="global-header">
    <!-- begin tab_selection -->
    <div id="global-panels-top">

    <ul>
        <li>Setup PHProjekt</li>	</ul></div>	<div id="global-panels-top-right"><ul>
	<li><a href="help/faq_install.html" id="help" target="_blank" title="Help">Help</a></li></ul></div>
    
    <!-- end tab_selection --><div class="breadcrumb" style="display:none;">You are here: Setup</div></div>
<div id="global-content">

<br />
<div class="inner_content">

    <a name="content"></a>
    
      
      
      <div class="outer_content">
        <div class="content">
  
          <!-- begin tab_selection
          <div class="topline"></div>
          <div class="tabs_area">
          <div class="tabs_area_modname">
            <span class="tabs_area_modname">Setup</span>
          </div>
          <div class="tabs_area_left">
            <span class="tabs_area"></span>
          </div>
          <div class="tabs_area_right">
            <span class="tabs_area">&nbsp;
              <a href="help/faq_install.html" class="navbutton navbutton_inactive" id="help" target="_blank" title="?">?</a>
            </span>
          </div>
        </div> -->
        <div class="tabs_bottom_line"></div>
        <div class="hline"></div>
        <!-- end tab_selection -->';

    return true;

}

/**
 * This function will display the help seccion
 *
 * @param string $step Step where the user are
 * @param string $mode Mode to configure the system (basic or advanced)
 * @param string $setupType Setup routine
 * @param string $charser_encoding
 */
function display_help($step = 'initial', $mode = 'basic', $setupType = 'install')
{
    // The text starting each step
    if ($step == 'initial')
    {
        echo "<div class='boxContent'><fieldset class='settings'><legend>".__('Welcome to the setup of PHProject!')."</legend>";
        switch ($setupType) {
            case 'installation':
                echo __('No system configuration file has been found.  Please continue for PHProject <b>installation</b>.')."<br /><br />\n";
                break;
            case 'upgrade':
                echo __('The current version of the configuration file is not the latest. Continue if you want to make an <b>upgrade</b>')."<br /><br />\n";
                break;
            case 'configuration':
                echo __('A configuration file has been found for latest PHProject version. Please continue to <b>configure</b> PHProject.')."<br /><br />\n";
                break;
        }

        echo __('Please remark:<ul><li>A blank database must be available<li>Please ensure that the webserver is able to write the file config.inc.php')."<br />\n";

        echo __('<li>If you encounter any errors during the installation, please look into the <a href=help/faq_install.html target=_blank>install faq</a>or visit the <a href=http://www.PHProjekt.com/forum.html target=_blank>Installation forum</a></i>')."<br />\n";



        echo __('Please fill in the fields below')."<br /><br />\n";
        echo __('(In few cases the script wont respond.<br />Cancel the script, close the browser and try it again).<br />');


        if ((!($fh = fopen("upload/fopen_test.txt", 'w'))))
        {
            echo "<br />".__('<b>WARNING: upload directory cannot be written.</b><br />');
        }
        else
        {
            fclose($fh);
            unlink("upload/fopen_test.txt");
        }

        if ((!($fh = fopen("attach/fopen_test.txt", 'w'))))
        {
            echo __('<b>WARNING: attach directory cannot be written.</b><br />');
        }
        else
        {
            fclose($fh);
            unlink("attach/fopen_test.txt");
        }
        
        if (version_compare(phpversion(), "4.3.0", "<")) 
        {
            echo __('<b>WARNING: Your PHP version is too old. For security reasons we highly recommend upgrading it.</b><br />');
        }
        
        if (defined('LANG_CODE') && LANG_CODE <> 'ISO-8859-1' && LANG_CODE <> 'utf8' && !function_exists('iconv'))
        {
            echo "<br />".__('<b>WARNING: the function iconv don\'t exists. It is necessary for language conversion.</b><br />');
        }

        echo "</fieldset></div><br />";
    }

    if ($step == 'configuration')
    {
        echo "<div class='boxContent'><fieldset class='settings'><legend>".__('Configuration')."</legend>";

        echo __('Please select the modules you are going to use.<br />(You can disable them later in the config.inc.php)');

        echo "</fieldset></div><br />";
    }

}

/**
 * Function to centralize button printing
 *
 * @param string  $step step where the button function was called
 * @param string $mode mode selected
 * @param string $setupType Type of setup selected
 * @return unknown true if function works propperly
 */
function display_buttons($step = 'initial', $mode = 'basic', $setupType = 'upgrade')
{
    // the buttons at end of each step
    if ($step == 'initial') {

        switch ($setupType) {
            case 'installation':
                echo "<br />
                  <input class='button' name='action_setup_default' value='".__('Default Install')."' type='submit'>
                  &nbsp;".__('Install default configuration')."<br /><br />
                  <input class='button' name='action_setup_basic' value='".__('Basic Installation')."' type='submit'>
                  &nbsp;".__('Module selection')."<br /><br />
                  <input class='button' name='action_setup_advanced' value='".__('Advanced Installation')."' type='submit'>
                  &nbsp;".__('Advanced install')."<br />
                  ";
                break;
            case 'upgrade':
                echo "<br />
                  <input class='button' name='action_setup_default' value='".__('Default upgrade')."' type='submit'>
                  &nbsp;".__('Upgrade to default configuration')."<br /><br />
                  <input class='button' name='action_setup_basic' value='".__('Basic upgrade')."' type='submit'>
                  &nbsp;".__('Module selection')."<br /><br />
                  <input class='button' name='action_setup_advanced' value='".__('Advanced upgrade')."' type='submit'>
                  &nbsp;".__('Advanced configuration')."<br />
                  ";
                break;
            case 'configuration':
                echo "<br />
                  <input class='button' name='action_setup_default' value='".__('Default Configuration')."' type='submit'>
                  &nbsp;".__('Setup default configuration')."<br /><br />
                  <input class='button' name='action_setup_basic' value='".__('Basic Configuration')."' type='submit'>
                  &nbsp;".__('Module selection')."<br /><br />
                  <input class='button' name='action_setup_advanced' value='".__('Advanced Configuration')."' type='submit'>
                  &nbsp;".__('Advanced configuration')."<br />
                  ";
                break;
        }

    }
    elseif ($step == 'configuration') {
        echo "<br />
          <input class='button' name='action_setup_go_home' value='".__('Go back')."' type='submit'>
          &nbsp;
          <input class='button' name='action_setup_finish' value='".__('Finish')."' type='submit'>
          ";

    }
    elseif ($step == 'summary') {
        echo "<br />
          <input class='button' name='action_setup_go_back' value='".__('Go back')."' type='submit'>
          &nbsp;
          <input class='button' name='action_setup_do' value='".__('Install')."' type='submit'>
          ";

    }
    return true;

}

/**
 * Gets the correct path for upload files
 *
 * @return varchar the full path before the folder name for uploads
 */

function get_upload_pre_path () {
    if (strpos(getcwd(),"/") === 0 || strpos(getcwd(),"/") > 0) {
        return getcwd()."/";
    }
    else {
        return getcwd()."\\";
    }
}

/**
 * Displays the botton of the page
 *
 * @return boolean $true if works properly
 */
function display_botton()
{
    // Page end
    echo "
              <br /><br /><br /><br />
            </form>
          </div>

<script type='text/javascript'>
<!--
function check_db(a){
  document.forms[0].button_pressed.value = a;  
  document.forms[0].submit();  
}
function validate_form()
{
  toReturn = true;
  
  if (typeof(document.forms[0].rootpass) =='object') {
    if (document.forms[0].rootpass.value == '')
    {
      alert('".str_replace("'",'"',__('Please, select a password for "root" user.'))."');
      toReturn = false;
    }
    if (document.forms[0].rootpass.value == 'root')
    {
      alert('".str_replace("'",'"',__('Please, select a password other than "root" for "root" user.'))."');
      toReturn = false;
    }
    else {
      if (document.forms[0].rootpass.value != document.forms[0].rootpass2.value) {
        alert('".str_replace("'",'"',__("root password and confirmation are different."))."');
        toReturn = false;
      }
    }
  } 
  
  if ((typeof(document.forms[0].testpass) =='object') && (toReturn == true)) {
      if (document.forms[0].testcreation.checked == true) {
        if (document.forms[0].testpass.value == '' || document.forms[0].testpass.value == 'test')
        {
          alert('".str_replace("'",'"',__('Please, select a password for "test" user (please, do not use "test").'))."');
          toReturn = false;
        }
        else {
          if (document.forms[0].testpass.value != document.forms[0].testpass2.value) {
            alert('".str_replace("'",'"',__("Test user password and confirmation are different."))."');
            toReturn = false;
          }
        }
      }
  } 
  
  return toReturn;
}

function testCreationChange () {
   var temp = false;
   if (document.forms[0].testcreation.checked == true) {
     document.forms[0].testpass.disabled = false;
     document.forms[0].testpass2.disabled = false;
   }
   else {
     temp = confirm('Are you sure? (unselecting this option test user will be disabled or not created)');
     if (temp == true) {
         document.forms[0].testpass.disabled = true;
         document.forms[0].testpass2.disabled = true;
     }
     else {
         document.forms[0].testcreation.checked = true;
     }
   }
}

//-->
</script>
        </div>
        </body>
      </html>
      ";

    return true;
}

?>
