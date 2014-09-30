<?php
/**
* reminder for upcoming events and incoming mails
*
* @package    calendar
* @module     reminder
* @author     Albrecht Guenther, $Author: polidor $
* @licence    GPL, see www.gnu.org/copyleft/gpl.html
* @copyright  2000-2006 Mayflower GmbH www.mayflower.de
* @version    $Id: reminder.php,v 1.32.2.2 2007/04/23 23:55:19 polidor Exp $
*/
// if (!defined('lib_included')) die('Please use index.php!');
define('PATH_PRE','../');
require_once(PATH_PRE.'lib/lib.inc.php');
require_once(PATH_PRE.'lib/email_getpart.inc.php');

if (isset($skin)) {
    define('FILE',dirname(__FILE__).'/../layout/'.$skin.'/'.$skin.'.inc.php');
    include_once(FILE);
}
$mess2 = '';
// page header
$remind_refresh = 15 * 60000;
$output = set_html_tag();
$output .= '
<head>
<title>'.__('Reminder').'</title>
';
if (isset($css_inc) && is_array($css_inc) && count($css_inc) > 0) {
    foreach ($css_inc as $css) {
        $output .= $css;
    }
}
$output .= '
<script type="text/javascript">
<!--
window.setTimeout(\'location.reload()\', '.$remind_refresh.');

function reminder_close() {
    window.close();
}
//-->
</script>
<link type="text/css" rel="shortcut icon" href="/'.PHPR_INSTALL_DIR.'favicon.ico" />
</head>
';

$set_timezone      = isset($settings['timezone'])    ? $settings['timezone']    : PHPR_TIMEZONE;
$set_reminder      = isset($settings['reminder'])    ? $settings['reminder']    : PHPR_REMINDER;
$set_reminder_freq = isset($settings['remind_freq']) ? $settings['remind_freq'] : PHPR_REMIND_FREQ;

// *************************
// query calendar for events
// *************************

// define time and date
$now = (date('H') + $set_timezone) * 60 + date('i', mktime());
$str_now = substr('0'.(date('H') + $set_timezone).date('i', mktime()), -4);
$query = "SELECT event, anfang, ende
            FROM ".DB_PREFIX."termine 
           WHERE an = ".(int)$user_ID." 
             AND datum = '".date("Y-m-d")."' 
             AND anfang >= '$str_now' 
        ORDER BY anfang";
$res = db_query($query) or db_die();
$mess = '';
while ($row = db_fetch_row($res)) {
    $text = html_out(substr($row[0], 0, 16));
    // string has more than 16 characters? cut and insert '...'
    if (strlen($row[0]) > 16) $text .= '..';

    // add event
    $mess .= "$row[1] - $row[2]: $text<br />\n";

    $begin = substr($row[1], 0, 2) * 60 + substr($row[1], 2, 2);
    $now   = (date('H') + $set_timezone)*60 + date('i', mktime());
    if ($set_reminder == '2' and ($begin <= ($now + $set_reminder_freq)) and ($begin > $now)) {
        $a = $begin - $now;
        $mess2 = "$row[0] ".__('Starts in')." $a ".__('minutes');
    }
}
// end event query

// *******************
// check for new mails
// *******************
$mail_list = '';
if ($reminder_mail && PHPR_QUICKMAIL == 2) {
    require_once(PATH_PRE.'lib/fetchmail.php');

    // reset mail counter
    $i = 0;

    // if no special account is given - loop over all mail accounts
    $query = "SELECT ID, von, accountname, hostname, type, username, password, deletion
                 FROM ".DB_PREFIX."mail_account 
                 WHERE von = ".(int)$user_ID."  
                   AND collect = 1";

    $res = db_query($query) or db_die();

    while ($row = db_fetch_row($res) and $i < 11) {

        $conn = new fetchmail($row[3], $row[5], $row[6], $row[4]);

        $mails_on_server = $conn->get_mail_list();

        if (is_array($mails_on_server) && count($mails_on_server) > 0) {

            foreach ($mails_on_server as $msgno => $one_message) {
                $i++;
                if ($i > 10) {
                    break;
                }
                $mail_list .= "{$one_message['subject']} ({$one_message['from']})\n";

            }
        }
    }
    if ($i == 10) $mail_list .= '...';
    
}
// end check mail
// **************

// no events found for today?
if (!$mess) $mess = __('No events yet today')."\n";

// if the alert option is on and there is en avent for alert or incoming mail, activate the alert box
if (($mess2 and $set_reminder == 2) or ($mail_list and $reminder_mail == 2)) {
    $message = '';
    if ($mess2)     $message .= "$mess2\\n\\n";
    if ($mail_list) $message .= __('New mail arrived');
    $output .= '<body style="margin:0px;background-color:'.PHPR_BGCOLOR3.
    ';" onload="self.focus();alert(\''.$message.'\');"><div id="global-main">'."\n";
}
// otherwise simply show the list in the window
else {
    $output .= '<body style="margin:0px;background-color:'.PHPR_BGCOLOR3.';"><div id="global-main">'."\n";
}

// write mail message
if (!empty($mail_list)) {
    $output .= '<div title="'.$mail_list.'">'.__('New mail arrived').'</div><br />'."\n";
}

// write events
$output .= '
'.$mess.'

</div>
</body>
</html>
';

echo $output;
?>
