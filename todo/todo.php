<?php

// todo.php - PHProjekt Version 5.2
// copyright  ©  2000-2005 Albrecht Guenther  ag@phprojekt.com
// www.phprojekt.com
// Author: Albrecht Guenther, $Author: alexander $
// $Id: todo.php,v 1.38.2.1 2007/01/22 14:15:56 alexander Exp $

$module = 'todo';
$contextmenu = 1;

define('PATH_PRE','../');
include_once(PATH_PRE.'lib/lib.inc.php');

todo_init();

$_SESSION['common']['module'] = 'todo';

// Status mode: 1=waiting, 2=pending/open, 3=accepted, 4=rejected, 5=done
$status_arr = array("1" => __('waiting'), "2" => __('Active'), "3" => __('accepted'), "4" => __('rejected'),"5" => __('ended'));

require_once(LIB_PATH.'/dbman_lib.inc.php');
$fields = build_array($module, $ID, $mode);

// call the distinct selectors
require_once('todo_selector_data.php');

echo set_page_header();

if ($justform > 0) {
    $content_div = '<div id="global-content" class="popup">';
} else {
    include_once(LIB_PATH.'/navigation.inc.php');
    $content_div = '<div id="global-content">';
}

// put the values in the form
global $fields;
$fields_temp = $fields;
foreach($fields_temp as $field_name => $field_array) {
    if (isset($_POST[$field_name])) $fields[$field_name]['value'] = xss($_POST[$field_name]);
}
// Get value from another module or internar module value
if (!isset($_GET['contact_ID'])) {
    if (isset($formdata['contact'])) $contact_ID = xss($formdata['contact']);
    elseif(isset($fields['contact']['value'])) {
        $contact_ID = $fields['contact']['value'];
    }
    else {
        $contact_ID = 0;
    }
}
if (!isset($_GET['projekt_ID'])) {
    if (isset($formdata['project'])) $projekt_ID = xss($formdata['project']);
    else $projekt_ID = $fields['project']['value'];
}
if (isset($formdata['ext']) && $mode == "forms") {
    $fields['ext']['value'] = $formdata['ext'];
}
if (isset($formdata['persons']) && $mode == "forms") {
    $persons = $formdata['persons'];
}

define('MODE',$mode);
include_once('./todo_'.MODE.'.php');

if ($justform > 0) echo "\n</div>\n";

echo '

</div>
</body>
</html>
';


/**
 * initialize the todo stuff and make some security checks
 *
 * @return void
 */
function todo_init() {
    global $ID, $contact_ID, $projekt_ID, $justform, $mode, $notes_view_both, $output;

    $output = '';

    $ID       = $_REQUEST['ID']       = isset($_REQUEST['ID']) ? (int) $_REQUEST['ID'] : 0;
    $justform = $_REQUEST['justform'] = isset($_REQUEST['justform']) ? (int) $_REQUEST['justform'] : 0;
    $contact_ID = $_REQUEST['contact_ID'] = isset($_REQUEST['contact_ID']) ? xss($_REQUEST['contact_ID']) : '';
    $projekt_ID = $_REQUEST['projekt_ID'] = isset($_REQUEST['projekt_ID']) ? xss($_REQUEST['projekt_ID']) : '';

    if (!isset($_REQUEST['mode']) || !in_array($_REQUEST['mode'], array('view', 'forms', 'data'))) {
        if (!$notes_view_both and ($ID > 0 or $contact_ID > 0 or $projekt_ID > 0)) {
            $_REQUEST['mode'] = 'forms';
        }
        else {
            $_REQUEST['mode'] = 'view';
        }
    }
    $mode = xss($_REQUEST['mode']);
}

?>
