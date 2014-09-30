<?php
/**
 * provides selector data for the organisations
 *
 * This file check the $_REQUEST for see what selector was clicked
 * In first case, (Form to Selector) the data are stored on the SESSION and call the selector file.
 * In second case, (Selector to Form) get the selected data and continue with the form.
 *
 * @package    organisations
 * @subpackage selector
 * @author     Gustavo Solt, $Author: gustavo $
 * @licence    GPL, see www.gnu.org/copyleft/gpl.html
 * @copyright  2000-2006 Mayflower GmbH www.mayflower.de
 * @version    $Id: 
 */

if (!defined('lib_included')) die('Please use index.php!');

// Common values
$formdata['_ID']            = $ID;
$formdata['_view']          = $view;
$formdata['_mode']          = 'forms';
$formdata['_selector_name'] = 'organisations_selector_';
    
// Selector for access (Form)
if (isset($_REQUEST['action_form_to_access_selector']) ||
           isset($_REQUEST['action_form_to_access_selector_x'])) {
    $formdata['_selector_type'] = 'member';
    $formdata['_title']    = __('Access selection');
    $formdata['_selector'] = $persons;
    $formdata['_return']   = 'action_access_selector_to_form_ok';
    $formdata['_cancel']   = 'action_access_selector_to_form_cancel';
    $_SESSION['organisationsdata']['formdata'] = $formdata;
    $delete_selector_filters = true;

} else if (isset($_REQUEST['action_access_selector_to_form_ok']) ||
           isset($_REQUEST['action_access_selector_to_form_cancel'])) {
    // back from selector (okay or cancel)
    if (isset($_REQUEST['action_access_selector_to_form_ok'])) {
        // pressed okay
        $selector = xss_array($_POST[$_SESSION['organisationsdata']['formdata']['_selector_name']."dsts"]);
        if (is_array($selector)) {
            foreach($selector as $k => $u_id) {
                $_SESSION['organisationsdata']['formdata']['persons'][] = slookup('users','kurz','ID',$u_id,'1');
            }
        } else $_SESSION['organisationsdata']['formdata']['persons'] = array();
    }
    // okay & cancel
    $formdata = $_SESSION['organisationsdata']['formdata'];
    unset($_SESSION['organisationsdata']['formdata']);
    unset($_REQUEST['filterform']);

   // Selector for contacts (Form)
} else if (isset($_REQUEST['action_form_to_contact_selector']) ||
   (isset($_REQUEST['action_form_to_contact_selector_x']) && ($_REQUEST['action_form_to_contact_selector_x'] > 0)) ) {
    $formdata['_selector_type'] = 'contact';
    $formdata['_title']         = __('Contact selection');
    $formdata['_selector']      = $contact_personen;
    $formdata['_return']        = 'action_contact_selector_to_form_ok';
    $formdata['_cancel']        = 'action_contact_selector_to_form_cancel';
    $_SESSION['organisationsdata']['formdata'] = $formdata;
    $delete_selector_filters = true;

} else if (isset($_REQUEST['action_contact_selector_to_form_ok']) ||
           isset($_REQUEST['action_contact_selector_to_form_cancel'])) {
    // back from selector (okay or cancel)
    if (isset($_REQUEST['action_contact_selector_to_form_ok'])) {
    // pressed okay
        $selector = xss_array($_POST[$_SESSION['organisationsdata']['formdata']['_selector_name']."dsts"]);
        if (is_array($selector)) {
            $_SESSION['organisationsdata']['formdata']['contact_personen'] = $selector;
        } else $_SESSION['organisationsdata']['formdata']['contact_personen'] = array();
    }
    // okay & cancel
    $formdata = $_SESSION['organisationsdata']['formdata'];
    unset($_SESSION['organisationsdata']['formdata']);
    unset($_REQUEST['filterform']);
}

// If is defined one of this selectors, call the selector file.
if (isset($_REQUEST['action_form_to_access_selector']) ||
    isset($_REQUEST['action_form_to_access_selector_x']) ||
    isset($_REQUEST['action_form_to_contact_selector']) ||
    isset($_REQUEST['action_form_to_contact_selector_x']) ||
    isset($_REQUEST['filterform']) ||
    isset($_REQUEST['filterdel'])) {
    $mode = 'selector';
}
?>
