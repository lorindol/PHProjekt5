<?php
/**
 * Management of saved setting filters
 *
 * @package    	lib
 * @subpackage 	main
 * @author     	Gustavo Solt
 * @licence     GPL, see www.gnu.org/copyleft/gpl.html
 * @copyright  	2000-2006 Mayflower GmbH www.mayflower.de
 * @version    	$Id:
 */

define('PATH_PRE','../');
include_once(PATH_PRE.'lib/lib.inc.php');
include(LIB_PATH.'/dbman_list.inc.php');
if (empty($_REQUEST['module'])) die('Wrong call');

// clean up some vars
$use     = (int) $_REQUEST['use'];
$dele    = (int) $_REQUEST['dele'];
$add     = xss($_REQUEST['add']);
$mode    = xss($_REQUEST['mode']);
$mode2   = xss($_REQUEST['mode2']);
$module  = xss($_REQUEST['module']);
$back    = xss($_REQUEST['back']);
$opener  = (isset($_REQUEST['opener'])) ? xss($_REQUEST['opener']) : '';

$js_close = "
<script type='text/javascript'>
<!--
window.close();
//-->
</script>\n";

$js_reload = "
<script type='text/javascript'>
<!--
window.location.href = '../lib/save_selections_pop.php?module=$module&back=$back&mode=$mode&mode2=$mode2&opener=$opener';
//-->
</script>\n";

echo '
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>'.__('Filter configuration').'</title>
<link type="text/css" rel="stylesheet" media="screen" href="../layout/default/default.css">
<link type="text/css" rel="stylesheet" media="print" href="../layout/default/default_print.css">
<!--[if gte IE 5]><link type="text/css" rel="stylesheet" media="screen" href="../layout/default/default_ie.css"><![endif]-->

<style type="text/css">
body {
    background-image: none;
}
</style>
<link rel="shortcut icon" href="/'.PHPR_INSTALL_DIR.'favicon.ico">
</head>
<body>
<div id="global-navigation"></div>
<div id="global-header">
    <div id="global-panels-top"></div>
</div>
<div id="global-content">
<div class="module_bar_top">
<a  class="button_link_inactive" href="javascript:window.close()">'.__('Close window').'</a>
</div>
<br />
    <div class="inner_content">
    <fieldset>
    <legend>'.__('Filter configuration').__(':').'</legend>
    <br style="clear:both"/>
    <table>
        <colgroup>
        <col width="410">
        <col width="410">
        </colgroup>
    <tr>
        <td colspan="2">
';
    if ($use) {
    	print use_statistic_setting($use,$back,$actionstring,$mode,$mode2,$opener);
        echo $js_close;
    } else if ($dele) {
        delete_statistic_setting($dele);
        echo $js_reload;
    } else if ($speichern) {
        save_statistic_settings($speichern,$module);
        echo $js_reload;
    } else {
        $filter = (array) get_statistic_settings($module,$mode2);
        $hiddenfields = "<input type='hidden' name='module' value='$module' />\n".
                        "<input type='hidden' name='back'   value='$back' />\n".
                        "<input type='hidden' name='opener' value='$opener' />\n".
                        "<input type='hidden' name='mode'   value='$mode' />\n".
                        "<input type='hidden' name='mode2'  value='$mode2' />\n";
        if (SID) {
            $hiddenfields .= "<input type='hidden' name='".session_name()."' value='".session_id()."' />";
        }

        echo '
        <form action="save_selections_pop.php" method="get">
        <fieldset>
        <legend>'.__('Save currently set filters').'</legend>
            <input name="speichern" class="halfsize" />
        '.get_buttons(array(array('type' => 'submit', 'active' => false, 'name' => 'Save', 'value' => __('Save')))).'
        '.$hiddenfields.'
        </fieldset>
        </form>
        <br style="clear:both" />

        <form action="save_selections_pop.php" method="get">
        <fieldset>
            <legend>'.__('Load filter').'</legend>
            <select name="use" class="fullsize">
            <option value="">-</option>
';
        foreach ($filter as $id => $value) {
            echo '<option value="'.$id.'">'.$value."</option>\n";
        }

        echo '
            </select>
        '.get_buttons(array(array('type' => 'submit', 'active' => false, 'name' => 'Use', 'value' => __('use')))).'
        '.$hiddenfields.'
        </fieldset>
        </form>
        <br style="clear:both" />

        <form action="save_selections_pop.php" method="get">
        <fieldset>
            <legend>'.__('Delete saved filter').'</legend>
            <select name="dele" class="fullsize">
            <option value="">-</option>
';
        foreach ($filter as $id => $value) {
            echo '<option value="'.$id.'">'.$value."</option>\n";
        }
        echo '
            </select>
            '.get_buttons(array(array('type' => 'submit', 'active' => false, 'name' => 'Delete', 'value' => __('Delete'), 'onclick' => 'return window.confirm(\''.__('Are you sure?').'\');'))).'
            '.$hiddenfields.'
        </fieldset>
        </form>
        <br style="clear:both" />
        </td>
    </tr>
    </table>
    </fieldset>
    </div>
</div>
</body>
</html>
';
}

/**
 * Save the setting into the database
 *
 * @param string 	$name 	- Name of the setting
 * @return void
 */
function save_statistic_settings($name,$module)
{
    global $user_ID;

    // insert the new setting
    if (isset($_SESSION['saved_settings'][$module]['phpbb2mysql_data'])) {
  		unset($_SESSION['saved_settings'][$module]['phpbb2mysql_data']);
    }
    $settings = serialize($_SESSION['saved_settings'][$module]);
	$query = "INSERT INTO ".DB_PREFIX."savedata
                     (        user_ID  ,        module    ,        name    ,     settings    )
              VALUES (".(int)$user_ID.",'".xss($module)."','".xss($name)."','".($settings)."')";
    $result = db_query($query) or db_die();
}

/**
 * Get all setting form one user
 *
 * @param string	$module		- Module name
 * @param string	$mode2		- Value of mode 2 to switch into many module views
 * @return array 					$settings [setting_id] => setting_name
 */
function get_statistic_settings($module,$mode2)
{
	global $user_ID;
	$result = db_query("SELECT ID, name, settings
                          			   FROM ".DB_PREFIX."savedata
                         			 WHERE user_ID = ".$user_ID."
                           				AND module  = '".$module."'") or db_die();

	while($row = db_fetch_Row($result)) {
		$set = unserialize($row[2]);
		if (isset($set['mode2'])&&($set['mode2'] == "$mode2")) {
			$settings[$row[0]] = $row[1];
		}
	}

	return $settings;
}

/**
 * Delete one setting
 *
 * @param int 	$id 		- Setting ID
 * @return void
 */
function delete_statistic_setting($id)
{
	global $user_ID;
    db_query(sprintf("DELETE FROM ".DB_PREFIX."savedata
                       WHERE id=%d
                         AND user_ID = %d", $id, $user_ID)) or db_die();
}

/**
 * Javascript function to use one setting filter
 *
 * @param int		$id					- ID of the selected setting
 * @param string	$back				- Back link
 * @param string	$actionstring		- Action value for the module
 * @param string	$mode			.- Mode value for the module
 * @param string	$mode2			- Mode2 value fot the module
 * @param string	$opener			- Window opener for reload
 * @return string						HTML output
 */
function use_statistic_setting($id,$back,$actionstring,$mode,$mode2,$opener = '')
{
	global $user_ID;
    $id = intval($id);
    $result = db_query("SELECT settings
                          FROM ".DB_PREFIX."savedata
                         WHERE user_ID = ".(int)$user_ID ."
                           AND id  = '".qss((int)$id)."'") or db_die();

    $row = db_fetch_Row($result);
    if (isset($row[0])) {
        $settings = unserialize($row[0]);
        $mode = $settings['mode'];
        $mode2 = $settings['mode2'];
    }

    if ($opener == 'undefined') {
	    return "
<script type='text/javascript'>
<!--
window.opener.location.href = '../$back?$actionstring&mode=$mode&mode2=$mode2&do=displaySavedSetting&action=calc&settings_apply=Apply&savedSetting=$id';
//-->
</script>";
    } else {
	    return "
<script type='text/javascript'>
<!--
window.opener.opener.location.href = '../$back?$actionstring&mode=$mode&mode2=$mode2&do=displaySavedSetting&settings_apply=Apply&savedSetting=$id';
window.opener.close();
//-->
</script>";
    }
}
?>
