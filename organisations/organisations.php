<?php
/**
 * organisations controller script
 *
 * @package    organisations
 * @subpackage main
 * @author     Gustavo Solt, $Author: gustavo $
 * @licence    GPL, see www.gnu.org/copyleft/gpl.html
 * @copyright  2000-2006 Mayflower GmbH www.mayflower.de
 * @version    $Id:
 */

$module = 'organisations';
define('PATH_PRE','../');
require_once(PATH_PRE.'lib/lib.inc.php');
$_SESSION['common']['module'] = 'organisations';
$contextmenu = 1;

organisations_init();

if (!isset($action)) $action = (isset($cont_action)) ? $cont_action : 'organisations';
$action = xss($action);

// Clear the message_stack
unset ($_SESSION['message_stack'][$module]);

if (PHPR_LDAP) {
    include_once(LIB_PATH.'/ldap.php');
}

// fields for possible doublet scan:
$doublet_fields_all = array( 'name'  => __('Name'));

echo set_page_header();

if (!$mode) $mode = 'view';
else        $mode = xss($mode);

$ID = (int) $ID;
$justform = (int) $justform;

// fetch elements of the form from the db
require_once(LIB_PATH.'/dbman_lib.inc.php');

$fields = build_array('organisations', $ID, $mode);

// call the distinct selectors
require_once('organisations_selector_data.php');

// put the values in the form
global $fields;
$fields_temp = $fields;
foreach($fields_temp as $field_name => $field_array) {
    if (isset($_REQUEST[$field_name])) $fields[$field_name]['value'] = xss($_REQUEST[$field_name]);
}
if (isset($formdata['persons']) && $mode == "forms")$persons = $formdata['persons'];
if (isset($formdata['parent']) && $mode == "forms") $parent  = xss($formdata['parent']);
if (isset($formdata['contact_personen'])) {
    $contact_personen = $formdata['contact_personen'];
}

if (isset($_POST['name']))      $remark = xss($_POST['name']);
if (isset($_POST['remark']))    $remark = xss_purifier($_POST['remark']);

include_once(LIB_PATH.'/navigation.inc.php');
$content_div = '<div id="global-content">';
define('MODE',$mode);
include_once('organisations_'.MODE.'.php');
echo '</div>';
echo "\n</div>\n</body>\n</html>\n";


/**
 * initialize the organisations stuff and make some security checks
 *
 * @return void
 */
function organisations_init() {
    global $ID, $mode, $mode2, $output;

    $output = '';

    $ID = $_REQUEST['ID'] = (int) $_REQUEST['ID'];

    if (!isset($_REQUEST['mode']) || !in_array($_REQUEST['mode'], array('view','forms','data','selector'))) {
        $_REQUEST['mode'] = 'view';
    }
    $mode = xss($_REQUEST['mode']);
}

/**
 * create the array needed as input for the breadcrumb-function
 * @see breadcrumb()
 * @uses __()
 * @param string $action gloabl $action
 * @param string $last string used a s last element
 */
function breadcrumb_data($action, $last='') {
    $tuples = array();
    $url = "organisations.php?action=$action";

    $tuples[] = array('title'=>__('Organisations'), 'url'=>$url);

    if ( 'new' == $action ) { $tuples[] = array('title'=>__('New'), 'url'=>$url); }

    if (!empty($last)) { $tuples[] = array('title'=>__($last)); }

    return $tuples;
}
?>
