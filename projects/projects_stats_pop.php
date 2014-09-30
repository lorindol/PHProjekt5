<?php

/**
 * Management of setting filters
 *
 * @package    Projects
 * @module     Project Statistics
 * @author     Gustavo Solt
 * @licence    GPL, see www.gnu.org/copyleft/gpl.html
 * @copyright  2000-2006 Mayflower GmbH www.mayflower.de
 * @version    $Id: projects_stats_pop.php,v 1.9.2.1 2007/02/27 08:14:10 thorsten Exp $
 */

define('PATH_PRE','../');
include_once(PATH_PRE.'lib/lib.inc.php');
include(LIB_PATH.'/dbman_list.inc.php');
if (empty($_REQUEST['module'])) die('Wrong call');

// clean up some vars
$ID      = (int) $_REQUEST['ID'];
$use     = (int) $_REQUEST['use'];
$dele    = (int) $_REQUEST['dele'];
$add     = xss($_REQUEST['add']);
$mode    = xss($_REQUEST['mode']);
$module  = xss($_REQUEST['module']);
$opener  = xss($_REQUEST['opener']);

$js_close = "
<script type='text/javascript'>
<!--
// window.opener.location.href = '../$opener/$opener.php?$actionstring&mode=$mode&ID=$ID"."$sid&';
window.close();
//-->
</script>\n";

$js_reload = "
<script type='text/javascript'>
<!--
window.location.href = '../projects/projects_stats_pop.php?module=projects$sid';
//-->
</script>\n";

echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
    "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>'.__('Filter configuration').'</title>
<style type="text/css" media="screen">@import "../layout/default/default_css.php";</style>
<style type="text/css">
body {
    background-image: none;
}
</style>
<link rel="shortcut icon" href="/'.PHPR_INSTALL_DIR.'favicon.ico" />
</head>
<body>
<div id="global-main">
<div class="topline"></div>
<div class="inner_content">
    <h3>'.__('Filter configuration').__(':').'</h3>
';
    if ($use) {
    	print use_statistic_setting($use);
        echo $js_close;
    } else if ($dele) {
        delete_statistic_setting($dele);
        echo $js_reload;
    } else if ($speichern) {
        save_statistic_settings($speichern);
        echo $js_reload;
    } else {
        $filter = get_statistic_settings();
        $hiddenfields = "<input type='hidden' name='module' value='$module' />\n".
                        "<input type='hidden' name='opener' value='$opener' />\n".
                        "<input type='hidden' name='mode'   value='$mode' />\n".
                        "<input type='hidden' name='ID'     value='$ID' />\n";
        if (SID) $hiddenfields .= "<input type='hidden' name='".session_name()."' value='".session_id()."' />";

        echo '
    <form action="projects_stats_pop.php" method="get">
        <div style="width:40%;float:left">'.__('Save currently set filters').'</div>
        <div style="width:60%;float:right">
            <input name="speichern" />
            '.get_buttons(array(array('type' => 'submit', 'active' => false, 'name' => '', 'value' => __('Save')))).'
        </div>
        '.$hiddenfields.'
    </form>
    <br style="clear:both" />

    <form action="projects_stats_pop.php" method="get">
        <div style="width:40%;float:left">'.__('Load filter').'</div>
        <div style="width:60%;float:right">
        <select name="use">
            <option value=""></option>
';
        foreach ($filter as $id=>$value) {
            echo '<option value="'.$id.'">'.$value."</option>\n";
        }

        echo '
        </select>
        '.get_buttons(array(array('type' => 'submit', 'active' => false, 'name' => '', 'value' => __('use')))).'</div>
        '.$hiddenfields.'
    </form>
    <br style="clear:both" />

    <form action="projects_stats_pop.php" method="get">
        <div style="width:40%;float:left">'.__('Delete saved filter').'</div>
        <div style="width:60%;float:right">
        <select name="dele">
            <option value=""></option>
';
        foreach ($filter as $id=>$value) {
            echo '<option value="'.$id.'">'.$value."</option>\n";
        }
        echo '
        </select>
        '.get_buttons(array(array('type' => 'submit', 'active' => false, 'name' => '', 'value' => __('Delete'), 'onclick' => 'return window.confirm(\''.__('Are you sure?').'\');'))).'</div>
        '.$hiddenfields.'
    </form>
    <br style="clear:both" />

    <a href="javascript:window.close()">'.__('Close window').'</a>
</div>

</div>
</body>
</html>
';
}

/**
 * save the setting into the database
 *
 * save into projekt_statistik_einstellungen the setting
 * save into projekt_statistik_projekte the project-setting relation
 * save into projekt_statistik_user the user-setting relation
 *
 * @param string $name - name of the setting
 * @return void
 */
function save_statistic_settings($name)
{
	global $user_ID;

    // insert the new setting
	$query = "INSERT INTO ".DB_PREFIX."projekt_statistik_einstellungen
						  (name,user_ID,startDate,endDate, withBooking, withComment, sortBy, isAllProjects, isAllUsers, show_group, period)
				   VALUES ('".xss($name)."',
                           ".(int)$user_ID.",
                           '".$_SESSION['statistic']['startDate']."',
                           '".$_SESSION['statistic']['endDate']."',
                           ".(int)$_SESSION['statistic']['withBooking'].",
                           ".(int)$_SESSION['statistic']['withComment'].",
                           '".$_SESSION['statistic']['sortBy']."',
                           ".(int)$_SESSION['statistic']['isAllProjects'].",
                           ".(int)$_SESSION['statistic']['isAllUsers'].",
                           ".(int)$_SESSION['statistic']['show_group'].",
                           '".$_SESSION['statistic']['period']."')";
    $result = db_query($query) or db_die();
    // get the inserted ID
	$query = "SELECT ID
                    FROM ".DB_PREFIX."projekt_statistik_einstellungen
                   WHERE name = '".xss($name)."' AND
                         startDate = '".$_SESSION['statistic']['startDate']."' AND
                         endDate = '".$_SESSION['statistic']['endDate']."' AND
                         withBooking = ".(int)$_SESSION['statistic']['withBooking']." AND
                         withComment = ".(int)$_SESSION['statistic']['withComment']." AND
                         sortBy = '".$_SESSION['statistic']['sortBy']."' AND
                         user_ID = ".(int)$user_ID;
    $result = db_query($query) or db_die();
	$row = db_fetch_row($result);

    // insert the users
	if(is_array($_SESSION['statistic']['userlist']) && !$_SESSION['statistic']['isAllUsers']) {
		foreach($_SESSION['statistic']['userlist'] as $user) {
			db_query("INSERT INTO ".DB_PREFIX."projekt_statistik_user
                                  (stat_einstellung_ID, user_ID)
						   VALUES (".(int)$row[0].",".(int)$user.")");
		}
	}

    // insert the project
	if(is_array($_SESSION['statistic']['projectlist']) && !$_SESSION['statistic']['isAllProjects']) {

        // array (date => project_id)
        // get only the project ID
        if (is_array($_SESSION['statistic']['projectlist'][0])) {
            $array_tmp = array();
            foreach($_SESSION['statistic']['projectlist'] as $tmp => $array_date) {
                foreach($array_date as $project_ID) {
                    $array_tmp[] = $project_ID;
                }
            }
            $array_tmp = array_unique($array_tmp);
        // array only the project_ID
        } else {
            $array_tmp = $_SESSION['statistic']['projectlist'];
        }
		foreach($array_tmp as $project) {
			db_query("INSERT INTO ".DB_PREFIX."projekt_statistik_projekte
                                         (stat_einstellung_ID, projekt_ID)
							      VALUES (".(int)$row[0].",".(int)$project.")");
		}
	}
}

/**
 * Get all setting form one user
 *
 * @return array $settings [setting_id] => setting_name
 */
function get_statistic_settings()
{
	global $user_ID;
	$result = db_query("SELECT ID, name
                          FROM ".DB_PREFIX."projekt_statistik_einstellungen
                         WHERE user_ID=".$user_ID);

	while($row = db_fetch_Row($result)) {
		$settings[$row[0]] = $row[1];
	}

	return $settings;
}

/**
 * Delete one setting
 *
 * @param int id - setting_id
 * @return void
 */
function delete_statistic_setting($id)
{
	global $user_ID;
	db_query(sprintf("DELETE FROM ".DB_PREFIX."projekt_statistik_einstellungen
                       WHERE id=%d
                         AND user_ID = %d", $id, $user_ID)) or db_die("Error");
	db_query(sprintf("DELETE FROM ".DB_PREFIX."projekt_statistik_projekte
                       WHERE stat_einstellung_ID=%d", $id)) or db_die("Error");
	db_query(sprintf("DELETE FROM ".DB_PREFIX."projekt_statistik_user
                       WHERE stat_einstellung_ID=%d", $id)) or db_die("Error");
}

/**
 * Javascript function to use one setting filter
 */
function use_statistic_setting($id)
{
	return "
<script type='text/javascript'>
<!--
window.opener.location.href = '../projects/projects.php?mode=stat&action=calc&do=displaySavedSetting&savedSetting=$id';
//-->
</script>";
}
?>
