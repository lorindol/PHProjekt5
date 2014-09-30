<?php
/**
* contacts controller script
*
* @package    contacts
* @module     main
* @author     Albrecht Guenther, $Author: alexander $
* @licence    GPL, see www.gnu.org/copyleft/gpl.html
* @copyright  2000-2006 Mayflower GmbH www.mayflower.de
* @version    $Id: contacts.php,v 1.49.2.1 2007/01/17 12:57:09 alexander Exp $
*/

$module = 'contacts';
define('PATH_PRE','../');
require_once(PATH_PRE.'lib/lib.inc.php');
$_SESSION['common']['module'] = 'contacts';
$contextmenu = 1;

contacts_init();

if (!isset($action)) $action = (isset($cont_action)) ? $cont_action : 'contacts';
$action = xss($action);

// Clear the message_stack
if(isset($_SESSION['message_stack'][$module])){
    unset ($_SESSION['message_stack'][$module]);
}

if (PHPR_LDAP) {
    include_once(LIB_PATH.'/ldap.php');
}

// fields for possible doublet scan:
$doublet_fields_all = array( 'vorname'  => __('First Name'),
'nachname' => __('Family Name'),
'firma'    => __('Company'),
'email'    => 'Email',
'strasse'  => __('Street'),
'plz'      => __('Zip code'),
'stadt'    => __('City'),
'land'     => __('Country') );

echo set_page_header();

if (!$mode) $mode = 'view';
else        $mode = xss($mode);

$ID = (int) $ID;
$justform = (int) $justform;

// fetch elements of the form from the db
require_once(LIB_PATH.'/dbman_lib.inc.php');

if ($mode == 'import_patterns') {
    $fields = build_array('contacts', $ID, 'data');
}
else {
    $fields = build_array('contacts', $ID, $mode);
}
// call the distinct selectors
require_once('contacts_selector_data.php');

// put the values in the form
global $fields;
$fields_temp = $fields;
foreach($fields_temp as $field_name => $field_array) {
    if (isset($_REQUEST[$field_name])) $fields[$field_name]['value'] = xss($_REQUEST[$field_name]);
}
if (isset($formdata['persons']) && $mode == "forms")                 $persons = $formdata['persons'];
if (isset($formdata['parent']) && $mode == "forms")                   $parent = $formdata['parent'];
if (isset($formdata['project_personen']))                   $project_personen = $formdata['project_personen'];
if (isset($_POST['project_personen']) && $mode == "data")   $project_personen = xss_array($_POST['project_personen']);

if (isset($formdata['contact_personen'])) $contact_personen = $formdata['contact_personen'];
if (isset($_POST['name']))      $remark = xss($_POST['name']);
if (isset($_POST['remark']))    $remark = xss($_POST['remark']);

if (isset($_REQUEST['action_form_to_project_selector_x']) && ($_REQUEST['action_form_to_project_selector_x'] < 1)) {
    $modify_contact_roles = true;
}

if ($justform != 1) {
    include_once(LIB_PATH.'/navigation.inc.php');
    $content_div = '<div id="global-content">';
}
else $content_div = '<div class="justformcontent">';

define('MODE',$mode);
include_once('contacts_'.MODE.'.php');

if ($justform != 1) echo '</div>';

echo "\n</div>\n</body>\n</html>\n";


/**
 * initialize the contacts stuff and make some security checks
 *
 * @return void
 */
function contacts_init() {
    global $ID, $mode, $mode2, $output;

    $output = '';

    $ID = $_REQUEST['ID'] = isset($_REQUEST['ID']) ?(int) $_REQUEST['ID'] : 0;

    if (!isset($_REQUEST['mode']) || !in_array($_REQUEST['mode'], array('view', 'forms', 'data', 'import_data', 'import_forms', 'import_patterns', 'profiles_data', 'profiles_forms', 'selector'))) {
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
    $url = "contacts.php?action=$action";

    $tuples[] = array('title'=>__('External contacts'), 'url'=>$url);

    if ( 'new' == $action ) { $tuples[] = array('title'=>__('New'), 'url'=>$url); }

    if (!empty($last)) { $tuples[] = array('title'=>__($last)); }

    return $tuples;
}
?>
