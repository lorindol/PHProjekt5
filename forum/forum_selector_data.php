<?php
/**
* forum selector data script
*
* @package    forum
* @module     selector
* @author     Gustavo Solt, $Author: polidor $
* @licence    GPL, see www.gnu.org/copyleft/gpl.html
* @copyright  2000-2006 Mayflower GmbH www.mayflower.de
* @version    $Id: forum_selector_data.php,v 1.4 2006/09/24 22:16:46 polidor Exp $
*/
if (!defined('lib_included')) die('Please use index.php!');
 
// Selector for access (Form)
if (isset($_REQUEST['action_form_to_access_selector']) ||
    isset($_REQUEST['action_form_to_access_selector_x'])) {
    $formdata['_selector_type'] = 'member';
    $formdata['_title']    = __('Access selection');
    $formdata['_selector'] = $persons;
    $formdata['_ID']       = $ID;
    $formdata['_fID']       = $fID;
    $formdata['_view']     = $view;
    $formdata['_mode']     = 'forms';
    $formdata['_return']   = 'action_access_selector_to_form_ok';
    $formdata['_cancel']   = 'action_access_selector_to_form_cancel';
    $formdata['_selector_name'] = 'forum_selector_';
    $_SESSION['forumdata']['formdata'] = $formdata;
    $delete_selector_filters = true;

} else if (isset($_REQUEST['action_access_selector_to_form_ok']) ||
           isset($_REQUEST['action_access_selector_to_form_cancel'])) {
    // back from selector (okay or cancel)
    if (isset($_REQUEST['action_access_selector_to_form_ok'])) {
        // pressed okay
        $selector = xss_array($_POST[$_SESSION['forumdata']['formdata']['_selector_name']."dsts"]);
        if (is_array($selector)) {
            foreach($selector as $k => $u_id) {
                $_SESSION['forumdata']['formdata']['persons'][] = slookup('users','kurz','ID',$u_id,'1');
            }
        } else $_SESSION['forumdata']['formdata']['persons'] = array();
    }
    // okay & cancel
    $formdata = $_SESSION['forumdata']['formdata'];
    unset($_SESSION['forumdata']['formdata']);
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
