<?php

// notes.php - PHProjekt Version 5.2
// copyright  ©  2000-2005 Albrecht Guenther  ag@phprojekt.com
// www.phprojekt.com
// Author: Albrecht Guenther, $Author: thorsten $
// $Id: notes.php,v 1.29.2.2 2007/02/13 16:16:49 thorsten Exp $

$module = 'notes';
$contextmenu = 1;

define('PATH_PRE','../');
include_once(PATH_PRE.'lib/lib.inc.php');

$_SESSION['common']['module'] = 'notes';

notes_init();

require_once(LIB_PATH.'/dbman_lib.inc.php');
$fields = build_array('notes', $ID, $mode);

// call the distinct selectors
require_once('notes_selector_data.php');

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
    else $contact_ID = $fields['contact']['value'];
}
if (!isset($_GET['projekt_ID'])) {
    if (isset($formdata['project'])) $projekt_ID = xss($formdata['project']);
    elseif(isset($fields['project']['value'])){
        $projekt_ID = $fields['project']['value'];
    }
}
if (isset($formdata['persons']) && $mode == "forms") {
    $persons = xss($formdata['persons']);
}

define('MODE',$mode);
include_once('./notes_'.MODE.'.php');

if ($justform < 1) echo '</div>';

echo "\n</div>\n</body>\n</html>\n";


/**
 * initialize the notes stuff and make some security checks
 *
 * @return void
 */
function notes_init() {
    global $ID, $contact_ID, $projekt_ID, $justform, $output, $mode, $notes_view_both;

    $output = '';

    $ID       = $_REQUEST['ID']       = isset($_REQUEST['ID']) ? (int) $_REQUEST['ID'] : 0;
    $justform = $_REQUEST['justform'] = isset($_REQUEST['justform']) ? (int) $_REQUEST['justform'] : 0;
    $contact_ID = $_REQUEST['contact_ID'] = isset($_REQUEST['contact_ID']) ? xss($_REQUEST['contact_ID']) : '';
    $projekt_ID = $_REQUEST['projekt_ID'] = isset($_REQUEST['projekt_ID']) ? xss($_REQUEST['projekt_ID']) : '';

    if (!isset($_REQUEST['mode']) || !in_array($_REQUEST['mode'], array('view', 'forms', 'data'))) {
        if (!$notes_view_both && ($ID > 0 || $contact_ID > 0 || $projekt_ID > 0)) {
            $_REQUEST['mode'] = 'forms';
        }
        else {
            $_REQUEST['mode'] = 'view';
        }
    }
    $mode = xss($_REQUEST['mode']);
}
?>
