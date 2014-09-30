<?php
/**
* filemanager controller script
*
* @package    filemanager
* @module     main
* @author     Albrecht Guenther, $Author: alexander $
* @licence    GPL, see www.gnu.org/copyleft/gpl.html
* @copyright  2000-2006 Mayflower GmbH www.mayflower.de
* @version    $Id: filemanager.php,v 1.38.2.1 2007/01/22 09:03:04 alexander Exp $
*/

$module = 'filemanager';
define('PATH_PRE','../');
$contextmenu = 1;
require_once(PATH_PRE.'lib/lib.inc.php');
require_once(LIB_PATH.'/dbman_lib.inc.php');

$_SESSION['common']['module'] = 'filemanager';


filemanager_init();

// call the distinct selectors
require_once('filemanager_selector_data.php');

echo set_page_header();

if ($justform > 0) {
    $content_div = '<div id="global-content" class="popup">';
} else {
    include_once(LIB_PATH.'/navigation.inc.php');
    $content_div = '<div id="global-content">';
}

// put the values in the form
$fields = build_array('filemanager', $ID, $mode);
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
    else{
        $projekt_ID = -1;
    }
}
if (isset($formdata['persons']) && $mode == "forms") {
    $persons = $formdata['persons'];
}
define('MODE',$mode);
include_once('./filemanager_'.MODE.'.php');

if ($justform < 1) echo '</div>';

echo '
</div>
</div>
</body>
</html>
';


/**
 * initialize the filemanager stuff and make some security checks
 *
 * @return void
 */
function filemanager_init() {
    global $ID, $contact_ID, $projekt_ID, $justform, $typ, $output, $mode;

    $output = '';

    $ID         = isset($_REQUEST['ID']) ? (int) $_REQUEST['ID'] : 0;
    $justform   = isset($_REQUEST['justform']) ? (int) $_REQUEST['justform'] : 0;
    $contact_ID = isset($_REQUEST['contact_ID']) ? xss($_REQUEST['contact_ID']) : 0;
    $projekt_ID = isset($_REQUEST['projekt_ID']) ? xss($_REQUEST['projekt_ID']) : 0;
    $typ        = isset($_REQUEST['typ']) ? xss($_REQUEST['typ']) : 0;

    if ( !isset($_REQUEST['mode']) ||
         !in_array($_REQUEST['mode'], array('view', 'forms', 'data', 'down')) ) {
        $_REQUEST['mode'] = 'view';
    }
    $mode = xss($_REQUEST['mode']);
}


/**
 * provide data
 * @param int $ID id-request-param
 */
function breadcrumb_data($ID) {
    $tuples = array();

    if ($ID) {
        $tuples[] = array('title'=> htmlentities(slookup('dateien','filename','ID',$ID,'1')));
    } else {
        $tuples[] = array('title'=> __('New'));
    }
    
    return $tuples;
}

?>
