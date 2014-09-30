<?php
/**
* helpdesk controller script
*
* @package    helpdesk
* @module     main
* @author     Albrecht Guenther, Nina Schmitt, $Author: thorsten $
* @licence    GPL, see www.gnu.org/copyleft/gpl.html
* @copyright  2000-2006 Mayflower GmbH www.mayflower.de
* @version    $Id: helpdesk.php,v 1.50.2.2 2007/02/14 12:45:05 thorsten Exp $
*/
$module = 'helpdesk';
define('PATH_PRE','../');
$contextmenu = 1;
require_once(PATH_PRE.'lib/lib.inc.php');
require_once(PATH_PRE.'lib/dbman_lib.inc.php');
$_SESSION['common']['module'] = 'helpdesk';


helpdesk_init();

// check whether there is a general rts mail in the config. if not, assign give the email of the user
if (!PHPR_RTS_MAIL) $rts_mail = $user_email;
else                $rts_mail = PHPR_RTS_MAIL;


// access: = = n/a, 1 = internal, 2 = open. only solved requests with access = 2 are in the knowledge base
$access = array( '0' => __('internal'), '2' => __('open') );

// database cols according to user role
$recipient_column = '2';  // to whom the ticket is assigned to
$author_column    = '4'; // who has checked in this ticket


// helpdesk states
// field status:  0 = optional, 1 = mandatory, 2 = remove from workflow
$helpdesk_states = array(
    array('mandatory' => 0, 'key' => '1', 'label' => __('open')),
    array('mandatory' => 1, 'key' => '2', 'label' => __('assigned')),
    array('mandatory' => 0, 'key' => '3', 'label' => __('solved')), /* this state is a must state -> after reaching this state customers will be notified */
    array('mandatory' => 0, 'key' => '4', 'label' => __('verified')),
    array('mandatory' => 1, 'key' => '5', 'label' => __('closed'))
);


$status_arr = array();
$hd_i = 1;
foreach($helpdesk_states as $hd_state) {
    $status_arr[$hd_i] = array ($hd_state['key'], array($recipient_column, $author_column), $hd_state['mandatory'], $hd_state['label']);
    $hd_i++;
}

require_once(PATH_PRE.'lib/dbman_lib.inc.php');
$fields = build_array('helpdesk', $ID, $mode);


// call the distinct selectors
require_once('helpdesk_selector_data.php');
// reload only in view  later this will be in the settings per module!
$page_reload = isset($page_reload) ? (int) $page_reload : 0;
if($page_reload > 0 and $mode == view){
    $he_add[]='  <meta http-equiv="refresh" content="'.$page_reload.
    '; URL=helpdesk.php?mode=view" />'."\n";
}
echo set_page_header();
if ($justform <= 0) {
    include_once(PATH_PRE.'lib/navigation.inc.php');
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
    elseif(isset($fields['contact']['value'])){
        $contact_ID = $fields['contact']['value'];
    }
}
if (!isset($_GET['projekt_ID'])) {
    if (isset($formdata['project'])) $projekt_ID = xss($formdata['project']);
    elseif(isset($fields['project']['value'])){
        $projekt_ID = $fields['project']['value'];
    }
}
if (isset($formdata['assigned']) && $mode == "forms") {
    $fields['assigned']['value'] = (int)$formdata['assigned'];
}
if (isset($formdata['persons']) && $mode == "forms") {
    $persons = xss_array($formdata['persons']);
}
if(!isset($submode)){
	if($user_type==4)$submode='discussion';
	else $submode='basic';
}

define('MODE',$mode);
include_once('./helpdesk_'.MODE.'.php');

echo '
</div>

</div>
</body>
</html>
';


/**
 * initialize the helpdesk stuff and make some security checks
 *
 * @return void
 */
function helpdesk_init() {
    global $ID, $mode, $output, $due_date, $date_format_object, $justform;

    $output = '';

    $ID       = $_REQUEST['ID']       = isset($_REQUEST['ID']) ? (int) $_REQUEST['ID'] : 0;
    $justform = $_REQUEST['justform'] = isset($_REQUEST['justform']) ? (int) $_REQUEST['justform'] : 0;
    // convert user date format back to db/iso date format (from the form)
    // use $_POST here, cause dbman_data.inc.php uses also the superglobal $_POST
    if (isset($_POST['due_date'])) {
        $due_date = $_POST['due_date'] = $date_format_object->convert_user2db(xss($_POST['due_date']));
    }

    if (!isset($_REQUEST['mode']) || !in_array($_REQUEST['mode'], array('view', 'forms', 'data'))) {
        $_REQUEST['mode'] = 'view';
    }
    $mode = xss($_REQUEST['mode']);

}