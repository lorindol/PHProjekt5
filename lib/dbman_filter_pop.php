<?php

// dbman_filter_pop.php - PHProjekt Version 5.2
// copyright  ©  2000-2005 Albrecht Guenther  ag@phprojekt.com
// www.phprojekt.com
// Author: Johannes Schlueter
// $Id: dbman_filter_pop.php,v 1.33.2.1 2007/09/04 21:25:41 polidor Exp $

define('PATH_PRE','../');
include_once(PATH_PRE.'lib/lib.inc.php');

include(PATH_PRE.'lib/dbman_list.inc.php');
if (empty($_REQUEST['module'])) die('Wrong call');

// clean up some vars
$ID      = (int) $_REQUEST['ID'];
$use     = (int) $_REQUEST['use'];
$dele    = (int) $_REQUEST['dele'];
$add     = xss($_REQUEST['add']);
$mode    = xss($_REQUEST['mode']);
$module  = xss($_REQUEST['module']);
$opener  = xss($_REQUEST['opener']);
$caption = xss($_REQUEST['caption']);
$skin    = (isset($skin))?qss($skin): 'default';

if ($module == 'contacts') $actionstring = 'action=contacts';
else                       $actionstring = 'module='.$module;

$caption = 'o_'.$module;
$caption = $$caption;

$js_close = "
<script type='text/javascript'>
<!--
window.opener.location.href = '../$opener/$opener.php?$actionstring&mode=$mode&ID=$ID"."$sid&';
window.close();
//-->
</script>\n";

$js_reload = "
<script type='text/javascript'>
<!--
window.location.href = '../$module/$module.php?$actionstring"."$sid';
//-->
</script>\n";

//echo set_page_header();

if ($nav == $module) {
    list($flist[$module],$sort,$directio, $operator) = load_filter($use, $module);
    header('Location: ../'.$module.'/'.$module.'.php?'.$actionstring.'&'.'mode='.$mode.'&sort='.$sort.'&direction='.$direction.'&operator='.$operator.$add.$sid);
    exit;
}
else {
    set_style();
    echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
    "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>'.__('Filter configuration').'</title>
'.(count($css_inc) > 0 ? implode("", $css_inc) : '').'
<style type="text/css">
body {
    background-image: none;
}
</style>
<link type="text/css" rel="shortcut icon" href="/'.PHPR_INSTALL_DIR.'favicon.ico" />
<link type="text/css" rel="stylesheet" media="screen" href="../layout/'.$skin.'/'.$skin.'.css" />
<!--[if gte IE 5]><link type="text/css" rel="stylesheet" media="screen" href="../layout/'.$skin.'/'.$skin.'_ie.css" /><![endif]-->
</head>
<body>
<div id="global-main">
<div class="module_bar_top">'.__('Filter configuration').'</div>
<div id="global-content" class="popup">
    <fieldset>
    <legend>'.__('Filter configuration').' '.$caption.'</legend>
    <a href="./dbman_filter_pop.php?aufheben=1&amp;module='.$module.'&amp;opener='.$opener.'&amp;mode='.$mode.'&amp;'.$actionstring.'&amp;ID='.$ID.$sid.'">'.__('Disable set filters').'</a>
    <hr />
';

    if ($use) {
        list($flist[$module],$sort,$direction, $operator) = load_filter($use, $module);
        $_SESSION['f_sort'][$module]['sort'] = $sort;
        $_SESSION['f_sort'][$module]['direction'] = $direction;
        $_SESSION['flist']['operators'][$module] = $operator;
        echo $js_close;
    } else if ($dele) {
        delete_filter($dele, $module);
        echo $js_close;
    } else if ($aufheben) {
        $flist[$module] = array();
        echo $js_close;
    } else if ($speichern) {
        save_filter($module, $speichern);
        echo $js_close;
    } else {
        $filter = get_filters($module);

        $hiddenfields = "<input type='hidden' name='module' value='$module' />\n".
                        "<input type='hidden' name='opener' value='$opener' />\n".
                        "<input type='hidden' name='mode'   value='$mode' />\n".
                        "<input type='hidden' name='ID'     value='$ID' />\n";
        if (SID) $hiddenfields .= "<input type='hidden' name='".session_name()."' value='".session_id()."' />";

echo '
    <form action="dbman_filter_pop.php" method="get">
        <label class="label_block">'.__('Save currently set filters').'</label>
            <input name="speichern" />
            '.get_buttons(array(array('type' => 'submit', 'active' => false, 'name' => '', 'value' => __('Save')))).'
        '.$hiddenfields.'
    </form>
    <br />
    <form action="dbman_filter_pop.php" method="get">
        <label class="label_block">'.__('Load filter').'</label>
        <select name="use">
            <option value=""></option>
';
        foreach ($filter as $id=>$value) {
            echo '<option value="'.$id.'">'.$value."</option>\n";
        }

        echo '
        </select>
        '.get_buttons(array(array('type' => 'submit', 'active' => false, 'name' => '', 'value' => __('use')))).'
        '.$hiddenfields.'
    </form>
    <form action="dbman_filter_pop.php" method="get">
        <label class="label_block">'.__('Delete saved filter').'</label>
        <select name="dele">
            <option value=""></option>
';
        foreach ($filter as $id=>$value) {
            echo '<option value="'.$id.'">'.$value."</option>\n";
        }
        echo '
        </select>
        '.get_buttons(array(array('type' => 'submit', 'active' => false, 'name' => '', 'value' => __('Delete'), 'onclick' => 'return window.confirm(\''.__('Are you sure?').'\');'))).'
        '.$hiddenfields.'
    </form>
    </fieldset>
    <a href="javascript:window.close()">'.__('Close window').'</a>
';



        echo '
</div>

</div>
</body>
</html>
';
    }

    $_SESSION['flist'] =& $flist;
}

?>
