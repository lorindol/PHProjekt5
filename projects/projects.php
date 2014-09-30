<?php
/**
 * projects controller script
 *
 * @package    projects
 * @subpackage main
 * @author     Albrecht Guenther, $Author: gustavo $
 * @licence    GPL, see www.gnu.org/copyleft/gpl.html
 * @copyright  2000-2006 Mayflower GmbH www.mayflower.de
 * @version    $Id: projects.php,v 1.50 2008-01-08 16:44:19 gustavo Exp $
 */

$module = 'projects';
$contextmenu = 1;

define('PATH_PRE','../');
include_once(PATH_PRE.'lib/lib.inc.php');
include_once('./projects.inc.php');

projects_init();

$_SESSION['common']['module'] = 'projects';

// List of fields in the db table, needed for filter
$fields = array( "all" => __('all fields'), "name" => __('Name'), "chef" => __('Leader'),
                 "ziel" => __('Aim'), "contact" => __('Contact'), "note" => __('Comment') );

//categories: 1=offered, 2=ordered, 3=at work, 4=ended, 5=stopped, 6=reopened 7 = waiting, 10=container, 11=ext. project
$categories = array( "1" => __('offered'), "2" => __('ordered'), "3" => __('Working'), "4" => __('ended'),
                     "5" => __('stopped'), "6" => __('Re-Opened'), "7" => __('waiting'));

// dependencies between projects on the same level
// 2 = cannot start before the end of project B,
// 3 = cannot start before start of project B,
// 4 = cannot end before start of project B,
// 5 = cannot end before end of project B
$dependencies =  array( '2' => __('cannot start before the end of project'),
                        '3' => __('cannot start before the start of project'),
                        '4' => __('cannot end before the start of project'),
                        '5' => __('cannot end before the end of project') );

// modes to define which project should appear in the list ...
// 1 = above the record
// 2 = below the record
$next_mode_arr = array('1' => __('Previous'), '2' => __('Next'));

if ($mode == 'view') $contextmenu = 1;
require_once(LIB_PATH.'/dbman_lib.inc.php');
$fields = build_array('projects', $ID, $mode);

// call the distinct selectors
require_once('projects_selector_data.php');

echo set_page_header();

if ($justform > 0) {
    $content_div = '<div id="global-content" class="popup">';
}
else {
    include_once(LIB_PATH.'/navigation.inc.php');
    $content_div = '<div id="global-content">';
}


if (isset($inclu) && $inclu == 'err_pro.php') {
    include('./'.'err_pro.php');
}
else {
    // put the values in the form
    global $fields;
    $fields_temp = $fields;
    foreach($fields_temp as $field_name => $field_array) {
        if (isset($_POST[$field_name])) $fields[$field_name]['value'] = strip_tags($_POST[$field_name]);
    }
    // Get value from another module or internar module value
    if (!isset($_GET['contact_ID'])) {
        if (isset($formdata['contact'])) $contact_ID = strip_tags($formdata['contact']);
        elseif(isset($fields['contact']['value'])) {
            $contact_ID = $fields['contact']['value'];
        }
        else {
           $contact_ID = -1;
        }
    }

    if (isset($formdata['project'])) $projekt_ID = strip_tags($formdata['project']);
    elseif(isset($fields['project']['value'])) {
        $projekt_ID = $fields['project']['value'];
    }
    else {
        $projekt_ID = -1;
    }

    if (isset($formdata['chef'])        && $mode == "forms")    $fields['chef']['value'] = strip_tags($formdata['chef']);
    if (isset($formdata['parent'])      && $mode == "forms")    $parent = (int)$formdata['parent'];
    if (isset($formdata['persons'])     && $mode == "forms")    $persons = $formdata['persons'];
    if (isset($formdata['personen'])    && $mode == "forms")    $personen = $formdata['personen'];
    if (isset($_POST['personen'])       && $mode == "data")     $personen = xss_array($_POST['personen']);

    if (isset($formdata['contact_personen'])) $contact_personen = xss_array($formdata['contact_personen']);
    if (isset($_POST['contact_personen']) && $mode == "data")   $contact_personen = xss_array($_POST['contact_personen']);

    if (isset($_REQUEST['action_form_to_participants_selector_x']) && ($_REQUEST['action_form_to_participants_selector_x'] < 1)) {
        $modify_user_roles = true;
        $modify_contact_roles = false;
    }

    if (isset($_REQUEST['action_form_to_contact_selector_x']) && ($_REQUEST['action_form_to_contact_selector_x'] < 1)) {
        $modify_user_roles = false;
        $modify_contact_roles = true;
    }

    define('MODE',$mode);
    include_once('./projects_'.MODE.'.php');
}
if ($justform > 0) echo '</div>';

echo "\n</div>\n</body>\n</html>\n";


/**
 * initialize the projects stuff and make some security checks
 *
 * @param void
 * @return void
 */
function projects_init() {
    global $ID, $mode, $mode2, $justform, $output, $treemode, $anfang, $ende, $date_format_object;

    $output = '';

    $ID       = $_REQUEST['ID']       = isset($_REQUEST['ID']) ? (int) $_REQUEST['ID'] : 0;
    $justform = $_REQUEST['justform'] = isset($_REQUEST['justform']) ? (int) $_REQUEST['justform'] : 0;

    // convert user date format back to db/iso date format (from the form)
    // use $_POST here, cause dbman_data.inc.php uses also the superglobal $_POST
    if (isset($_POST['anfang'])) {
        $anfang = $_POST['anfang'] = $date_format_object->convert_user2db(strip_tags($_POST['anfang']));
    }
    if (isset($_POST['ende'])) {
        $ende = $_POST['ende'] = $date_format_object->convert_user2db(strip_tags($_POST['ende']));
    }

    if (!isset($_REQUEST['treemode'])) {
        $_REQUEST['treemode'] = 'auf';
    }
    $treemode = $_REQUEST['treemode'] = strip_tags($_REQUEST['treemode']);

    if ( !isset($_REQUEST['mode']) ||
         !in_array($_REQUEST['mode'], array('view', 'forms', 'data', 'gantt', 'options', 'sort', 'stat', 'pdf', 'status_update', 'status_change','uploadforms')) ) {
        $_REQUEST['mode'] = 'view';
    }
    $mode = strip_tags($_REQUEST['mode']);

    if (isset($_REQUEST['mode2'])) {
        $mode2 = strip_tags($_REQUEST['mode2']);
    }
}

?>
