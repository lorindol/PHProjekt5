<?php

// authform.inc.php - PHProjekt Version 5.2
// copyright  ©  2000-2005 Albrecht Guenther  ag@phprojekt.com
// www.phprojekt.com
// Author: Albrecht Guenther, $Author: johann $
// $Id: authform.inc.php,v 1.45 2006/12/13 07:45:32 johann Exp $

// check whether the lib has been included - authentication!
if (!defined("lib_included")) die("Please use index.php!");

if (!PATH_PRE) define('PATH_PRE','../');

if (!$langua) {
    $langua = getenv('HTTP_ACCEPT_LANGUAGE');
    $found  = false;
    foreach ($languages as $langua1) {
        if (eregi($langua1, $langua)) {
            $langua = $langua1;
            $found  = true;
            break;
        }
    }
    define('LANG',substr($langua,0,2));
    if ($found) {
        include_once(LANG_PATH.'/'.LANG.'.inc.php');
    }
    else {
        $langua = 'en';
        include_once(LANG_PATH.'/en.inc.php');
    }
}

$support_html = '';
$css_style    = '';

if (!strstr($_SERVER['QUERY_STRING'], 'module=logout')) {
    $return_path = urlencode('/'.xss($_SERVER['REQUEST_URI']));
}
else {
    $return_path = 'index.php';
}

$module = "login";

// Charset type related to language of browser
$tmp_charset_conf = get_charset($langua);
$lcfg    = $tmp_charset_conf['lcfg'];
$dir_tag = $tmp_charset_conf['dir_tag'];


if ($lcfg <> '') { $lang_cfg = '<meta http-equiv="Content-Type" content="text/html; '.$lcfg.'" />'."\n"; }
else {$lang_cfg = '';}

echo set_page_header();

// check if there is set a different logo on config.inc.php
if (defined('PHPR_LOGO') && file_exists(PHPR_INSTALL_DIR.PHPR_LOGO)) {
    $logo_src = "/".PHPR_INSTALL_DIR.PHPR_LOGO;
}
else {
    $logo_src = "/".PHPR_INSTALL_DIR."layout/default/img/logo.png";
}


echo '
<br /><br />
<div title="PHProjekt Login" style="background-color:#DC6417;height:41px;width:100%;">
';
if (strlen($logo_src)) {
    echo '<img src="'.$logo_src.'" alt="PHProjekt Logo" />';
}
echo '</div>'."\n";

// security: $login_error should not come from somewhere outside
if (isset($_REQUEST['login_error']) || empty($login_error)) {
    $login_error = '';
}
else {
    $login_error = '
<fieldset style="width: 35em;height: 10em;padding:0em;margin:auto;" style="border:1px solid red;height:auto;padding:10px 0px 10px 0px;">
    '.xss($login_error).'
</fieldset>'."\n";
}

?>

<br />

<div style="text-align:center;">
    <div id="logo" style="text-align:center;""></div>
    <form action="<?php echo PATH_PRE; ?>index.php" method="post" name="frm">
        <input type="hidden" name="loginform" value="1" />
        <input type="hidden" size="100" name="return_path" value="<?php echo xss($return_path); ?>" />
        <fieldset style="width: 35em;height: 10em;padding:0em;margin:auto;">
            <legend><?php echo __('Log in, please'); ?></legend>
            <label for="loginstring" style="clear:both;float: left;text-align: right;width: 6.3em;margin: 0.4em;"> <?php echo __('Login'); ?></label>
            <input style=" font-size:1em;float: left;text-align: left;margin: 0.2em;" type="text" tabindex="1" name="loginstring" id="loginstring" size="33" title="<?php echo __('Please enter your user name here.'); ?>" /><br />
            <label for="user_pw" style="clear:both;float: left;text-align: right;width: 6.3em;margin: 0.4em;"><?php echo __('Password'); ?></label>
            <input style=" font-size:1em;float: left;text-align: left;margin: 0.2em;" type="password" tabindex="2" name="user_pw" id="user_pw" size="33" title="<?php echo __('Please enter your password here.'); ?>" /><br style="clear:both"/>
            <span style=" font-size:1em;float: left;text-align: left;margin: 0.2em;margin-left: 6.9em;"><input  type="checkbox" tabindex="2" name="remember_me" id="remember_me" title="<?php echo __('Remember me on this computer.'); ?>" /><?php echo " ".__('Remember me on this computer'); ?></span><br style="clear:both"/>
            <input class="button" style="float: left;text-align: left;margin: 0.2em;margin-left: 6.9em;" type="submit" value="<?php echo __('go'); ?>" title="<?php echo __('Click here to login.'); ?>" />
        </fieldset>
    </form>
    <?php echo $login_error; ?>
</div>

<script type="text/javascript">
<!--
if (document.frm.loginstring.value == "") {
    document.frm.loginstring.focus();
}
<?php
// to determine if is a logout or not we need to check the requested module
if ($_REQUEST['module'] == 'logout') {
?>
// WRem is created previusly on this page (logout) to logout the reminder
if (WRem) {
    WRem.close();
}
<?php
}
?>
//-->
</script>

</div>
</body>
</html>
