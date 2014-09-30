<?php
/**
 * This file check the $_REQUEST for see what selector was clicked
 * In first case, (Form to Selector) the data are stored on the SESSION and call the selector file.
 * In second case, (Selector to Form) get the selected data and continue with the form.
 *
 * @package    	lib
 * @subpackage 	main
 * @author     	Gustavo Solt
 * @licence     GPL, see www.gnu.org/copyleft/gpl.html
 * @copyright  	2000-2006 Mayflower GmbH www.mayflower.de
 * @version    	$Id: 
 */

// check whether the lib has been included - authentication!
if (!defined('lib_included')) die('Please use index.php!'); 
 
// Common values
$formdata['_ID']       = $ID;
$formdata['_view']     = $view;
$formdata['_selector_name'] = 'todo_selector_';
    

// Selector for access (Form)
if (isset($_REQUEST['action_form_to_access_selector']) ||
           isset($_REQUEST['action_form_to_access_selector_x'])) {
    $formdata['_selector_type'] = 'member';
    $formdata['_title']    = __('Access selection');
    $formdata['_selector'] = $persons;
    $formdata['_mode']     = 'forms';
    $formdata['_return']   = 'action_access_selector_to_form_ok';
    $formdata['_cancel']   = 'action_access_selector_to_form_cancel';
    
    // form data
    $formdata['_ID']            = $ID;
    $formdata['_use']           = $use;
    $formdata['_dele']          = $dele;
    $formdata['_add']           = $add;
    $formdata['_mode']          = $mode;
    $formdata['_module']        = $module;
    $formdata['_open']          = $opener;
    $formdata['_caption']       = $caption;
    $formdata['_expert']        = $expert;
    $formdata['_expert_filter'] = $expert_filter;
    
    $_SESSION['tododata']['formdata'] = $formdata;
    $delete_selector_filters = true;

} else if (isset($_REQUEST['action_access_selector_to_form_ok']) ||
           isset($_REQUEST['action_access_selector_to_form_cancel'])) {
    // back from selector (okay or cancel)
    if (isset($_REQUEST['action_access_selector_to_form_ok'])) {
        // pressed okay
        $selector = xss_array($_POST[$_SESSION['tododata']['formdata']['_selector_name']."dsts"]);
        if (is_array($selector)) {
            foreach($selector as $k => $u_id) {
                $_SESSION['tododata']['formdata']['persons'][] = slookup('users','kurz','ID',$u_id,'1');
            }
        } else $_SESSION['tododata']['formdata']['persons'] = array();
    }
    // okay & cancel
    $formdata = $_SESSION['tododata']['formdata'];
    $persons = $formdata['persons']; 
    
    // restore form data
    $ID            = $formdata['_ID'];
    $use           = $formdata['_use'];
    $dele          = $formdata['_dele'];
    $add           = $formdata['_add'];
    $mode          = $formdata['_mode'];
    $module        = $formdata['_module'];
    $opener        = $formdata['_open'];
    $caption       = $formdata['_caption'];
    $expert        = $formdata['_expert'];
    $expert_filter = $formdata['_expert_filter'];
    
    unset($_SESSION['tododata']['formdata']);
    unset($_REQUEST['filterform']);
}

// If is defined one of this selectors, call the selector file.
if (isset($_REQUEST['action_form_to_access_selector']) ||
    isset($_REQUEST['action_form_to_access_selector_x']) ||
    isset($_REQUEST['action_selector_to_selector']) ||
    isset($_REQUEST['filterform']) ||
    isset($_REQUEST['filterdel'])) {
    $mode = 'selector';
}
?>
