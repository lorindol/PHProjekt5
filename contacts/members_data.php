<?php
/**
* contacts member db data script
*
* @package    contacts
* @module     members
* @author     Albrecht Guenther, Gustavo Solt, $Author: polidor $
* @licence    GPL, see www.gnu.org/copyleft/gpl.html
* @copyright  2000-2006 Mayflower GmbH www.mayflower.de
* @version    $Id: members_data.php,v 1.6 2006/08/25 05:34:21 polidor Exp $
*/
if (!defined('lib_included')) die('Please use index.php!');

// check role
if (check_role('contacts') < 2) die('You are not allowed to do this!');

// check form token
check_csrftoken();

// Clear the message_stack
unset ($_SESSION['message_stack']['contacts']);

// work on a user entry
if ($members || $members_update) {
    $query = "UPDATE ".DB_PREFIX."users
                 SET firma   = '$firma',
                     email   = '$email',
                     tel1    = '$tel1',
                     tel2    = '$tel2',
                     fax     = '$fax',
                     strasse = '$strasse',
                     stadt   = '$stadt',
                     plz     = '$plz',
                     land    = '$land',
                     mobil   = '$mobil',
                     anrede  = '$anrede'
               WHERE ID = ".(int)$ID;
    $result = db_query(xss($query)) or db_die();
    $action = 'members';
    message_stack_in(__('The data set is now modified.')." :-)","contacts","notice");
}

if ($members_update) {
    $query_str = "members.php?mode=forms&ID=".$ID;
    header('Location: '.$query_str);
} else {
    include_once("./members_view.php");
}
?>
