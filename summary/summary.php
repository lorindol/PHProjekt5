<?php

// summary.php - PHProjekt Version 5.2
// copyright © 2000-2005 Albrecht Guenther  ag@phprojekt.com
// www.phprojekt.com
// Author: Albrecht Guenther, $Author: albrecht $
// $Id: summary.php,v 1.94.2.3 2007/02/09 13:35:52 albrecht Exp $

define('PATH_PRE','../');
$module = 'summary';
include_once(PATH_PRE.'lib/lib.inc.php');
include_once(LIB_PATH.'/dbman_filter.inc.php');

include_once('./summary.inc.php');
summary_init();

$_SESSION['common']['module'] = 'summary';

$tdwidth    = 300;
$tdelements = 5;

// set time and date
$today1 = date('Y-m-d', mktime(date('H')+PHPR_TIMEZONE, date('i'), date('s'), date('m'), date('d'), date('Y')));
$now    = (date('H')+PHPR_TIMEZONE)*60 + date('i', mktime());

// **********
// db actions
// **********

// POLLS: insert vote
if ($votum_ID) summary_insert_vote($votum_ID);

// call the distinct selectors
require_once('summary_selector_data.php');

// *****************
// start html output
// *****************
echo set_page_header();
include_once(LIB_PATH.'/navigation.inc.php');

if (isset($_REQUEST['action_form_to_list_von_selector']) ||
    isset($_REQUEST['action_form_to_list_von_selector_x']) ||
    isset($_REQUEST['action_form_to_list_ext_selector']) ||
    isset($_REQUEST['action_form_to_list_ext_selector_x']) ||
    isset($_REQUEST['action_form_to_list_project_selector']) ||
    isset($_REQUEST['action_form_to_list_project_selector_x']) ||
    isset($_REQUEST['action_form_to_list_notes_contact_selector']) ||
    isset($_REQUEST['action_form_to_list_notes_contact_selector_x']) ||
    isset($_REQUEST['action_form_to_list_projekt_selector']) ||
    isset($_REQUEST['action_form_to_list_projekt_selector_x']) ||
    isset($_REQUEST['action_form_to_list_dateien_contact_selector']) ||
    isset($_REQUEST['action_form_to_list_dateien_contact_selector_x']) ||
    isset($_REQUEST['action_form_to_list_div2_selector']) ||
    isset($_REQUEST['action_form_to_list_div2_selector_x']) ||
    isset($_REQUEST['action_form_to_list_assigned_selector']) ||
    isset($_REQUEST['action_form_to_list_assigned_selector_x']) ||
    isset($_REQUEST['filterform']) ||
    isset($_REQUEST['filterdel'])) {

    require_once('./summary_selector.php');
} else {
    // tabs
    $tabs = array();
    $output = "<div id='global-header' title='PHProjekt ".PHPR_VERSION.", ".__('logged in as').": ".$_SESSION['user_firstname']." ".$_SESSION['user_name'].", ".__('Group').": ".$_SESSION['user_group']."'>";
    $output .= get_tabs_area($tabs);
    $output .= breadcrumb($module, array());

    // timecard start/stop buttons and project watch button
    if (PHPR_TIMECARD and check_role('timecard') > 1)   $buttons = summary_show_timecard();
    else                                                $buttons = array();
    $output .= '</div>';
    $output .= '<div id="global-content">';
    $output .= get_buttons_area($buttons);

    $output .= '
    <div class="inner_content">
    <a name="content"></a>
    ';

    include_once(LIB_PATH.'/dbman_lib.inc.php');
    include_once(LIB_PATH.'/show_related.inc.php');
    include_once(LIB_PATH.'/contextmenu.inc.php');

    $since_last = true; // for configuration!
    $lastlogin = summary_get_last_login();

    if (PHPR_CALENDAR and check_role('calendar') > 0)       $output .= summary_show_calendar();
    if (PHPR_TODO and check_role('todo') > 0)               $output .= summary_show_latest('todo');
    if (PHPR_RTS  and check_role('helpdesk') > 0)           $output .= summary_show_latest('helpdesk');
    if (PHPR_VOTUM and check_role('votum') > 0)             $output .= summary_show_votum();
    if (PHPR_CONTACTS and check_role('contacts') > 0)       $output .= summary_show_latest('contacts');
    if (PHPR_FORUM and check_role('forum') > 0)             $output .= summary_show_forum();
    if (PHPR_FILEMANAGER and check_role('filemanager') > 0) $output .= summary_show_latest('filemanager');
    if (PHPR_PROJECTS and check_role('projects') > 0)       $output .= summary_show_latest('projects');
    if (PHPR_NOTES and check_role('notes') > 0)             $output .= summary_show_latest('notes');

    // Check last 5 emails
    if (PHPR_QUICKMAIL and check_role('mail') > 0)             $output .= summary_show_latest('mail');
    
    // This function will check the summaries of each addon
    $output .= summary_show_addons();

    echo $output;
    echo '
            <br /><br />
        </div>';
}
echo '
    </div>
</div>


</body>
</html>
';


/**
 * initialize the summary stuff and make some security checks
 *
 * @return void
 */
function summary_init() {
    global $votum_ID, $output;

    $output = '';

    if (!isset($_REQUEST['votum_ID'])) $_REQUEST['votum_ID'] = 0;
    $votum_ID = $_REQUEST['votum_ID'] = (int) $_REQUEST['votum_ID'];
}

?>
