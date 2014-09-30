<?php
/**
* login/logout buttons for timecard
*
* Provides start and end button for the timecard
*
* @package    	lib
* @subpackage 	timecard
* @author     		Albrecht Guenther, $Author: gustavo $
* @licence     GPL, see www.gnu.org/copyleft/gpl.html
* @copyright  	2000-2006 Mayflower GmbH www.mayflower.de
* @version    		$Id: tc_login.inc.php,v 1.14 2007-05-31 08:11:55 gustavo Exp $
*/
if (!defined('lib_included')) die('Please use index.php!');


/**
 * Display the timecards button
 *
 * @param void
 * @return  string		HTML tags
 */
function show_timecard_button() {
    global $user_ID, $sid;

    // fetch current date and time
    $datum = date('Y-m-d', mktime(date('H')+PHPR_TIMEZONE, date('i'), date('s'), date('m'), date('d'), date('Y')));
    $time  = date('H:i',   mktime(date('H')+PHPR_TIMEZONE, date('i'), date('s'), date('m'), date('d'), date('Y')));


    // fetch an entry of this user from today where the record hasn't been completed (means: the user is still in the office)
    $result = db_query("SELECT ID
                          FROM ".DB_PREFIX."timecard
                         WHERE datum = '$datum'
                           AND (ende = 0 OR ende IS NULL)
                           AND users = ".(int)$user_ID) or db_die();
    $row = db_fetch_row($result);
    // buttons for 'come' and 'leave', alternate display
    if ($row[0] > 0) {
    // button 'leave' only if one record from today is open
        echo "href='../index.php?module=timecard&mode=data&action=2".$sid."' target='_top'><img src='".IMG_PATH."/tc_logout.gif' alt='".__('End')."' title='".__('End')."' border=0></a>\n";
    }
    else {
        // 'come' button only if the user is not logged into the timecard today
        echo "href='../index.php?module=timecard&mode=data&action=1".$sid."' target='_top'><img src='".IMG_PATH."/tc_login.gif' alt='".__('Begin')."' title='".__('Begin')."' border=0></a>\n";
    }
}
?>
