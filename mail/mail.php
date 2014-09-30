<?php
// mail.php - PHProjekt Version 5.2
// copyright  ©  2000-2004 Albrecht Guenther  ag@phprojekt.com
// www.phprojekt.com
// Author: Albrecht Guenther, $auth$
// $Id: mail.php,v 1.51.2.5 2007/08/30 04:09:01 polidor Exp $

$module = 'mail';
$contextmenu = 1;

define('PATH_PRE','../');
include_once(PATH_PRE.'lib/lib.inc.php');

mail_init();

$_SESSION['common']['module'] = 'mail';

require_once(PATH_PRE.'lib/dbman_lib.inc.php');

// check if exists the trash can and get his number
$trash_can_ID = check_trash_can($user_ID);

// check if the user is replying a folder
if ($mode == 'send_form' && isset($action2) && is_mail_folder($ID)) {
    message_stack_in(__('It is not possible to reply/forward folders'),$module,"error");
    $mode = 'view';
}

// ********************************
// Adding contacts from email

if ($mode == 'send_form' && isset($action2) && $action2 == 'addContact' && $ID > 0) {

    // We will get the sender information
    $querySender = "SELECT sender FROM ".DB_PREFIX."mail_client WHERE ID = ".(int)$ID;
    $resultSender = db_query($querySender) or db_die();

    if ($rowSender = db_fetch_row($resultSender)) {

        // The sender field has all the sender information, we need to divide it (e.g. John Doe<jdoe@example.com>)
        $fullEmail = $rowSender[0];

        // chech if is only an email or if it is an email and the name (if it has a "<" I supose is the name<email> format>)
        if (strpos($fullEmail,">") > 0) {

            // getting the email and full name
            $email = substr($fullEmail,strpos($fullEmail,"<")+1,-1);

            $fullName = substr($fullEmail,0,strpos($fullEmail,"<"));

            // getting the firstname and lastname from full name
            $names = explode(" ",$fullName);
            $lastName = array_pop($names);
            $firstName = implode(" ",$names);

        }
        else {
            // the format of sender field is only the email (e.g. jdoe@example.com only)
            $email = $fullEmail;
            $firstName = '';
            $lastName = '';

        }
        if ($email <> '') {

            // checking if contact exists on contact table
            $querySender = "SELECT ID FROM ".DB_PREFIX."contacts WHERE email = '$email'";
            $resultSender = db_query($querySender) or db_die();
            if ($rowSender = db_fetch_row($resultSender)) {
                message_stack_in(__('There is a contact with the email')." $email",$module,"error");
                $mode = 'view';
            }
            else {
                // Inserting the email on contact table
                header("Location: ../contacts/contacts.php?mode=forms&action=new&nachname=$firstName&vorname=$lastName&email=$email");
                die();
            }
        }
    }
    $mode = 'view';
}

//$sent_ID = check_sent_folder($user_ID);

// array with port types, will be used on several stages
// array for mail account types and ports
// mind that the database's columnsize is currently only 10 chars for the arraykeys!


/* moved to lib.inc.php (this array is used by reminder to fecth emails
$port = array( 'pop3'            => '110/pop3',
'pop3s'           => '995/pop3/ssl',
'pop3 NOTLS'      => '110/pop3/NOTLS',
'pop3s NVC'       => '995/pop3/ssl/novalidate-cert',
'imap'            => '143/imap',
'imap3'           => '220/imap3',
'imaps'           => '993/imap/ssl',
'imaps NVC'       => '993/imap/ssl/novalidate-cert',
'imap4ssl'        => '585/imap4/ssl',
'imap NOTLS'      => '143/imap/NOTLS',
'imap NOVAL'      => '143/imap/NOVALIDATE' );
*/

// no full mail client installed? -> only send mail possible
if (PHPR_QUICKMAIL == 1 and !$action) $mode = 'send_form';

if ($mode=='send_form') $js_inc[] = "src='../lib/javascript/fckeditor.js'>";

// call the distinct selectors
require_once('mail_selector_data.php');

echo set_page_header();

require_once(LIB_PATH.'/dbman_lib.inc.php');
// include the navigation
if ($justform > 0 || $action == 'showhtml') {
    $content_div = "class='popup'";
} else {
    include_once(LIB_PATH.'/navigation.inc.php');
    $content_div = '';
}

// now the actual content
if ($mode=='view' or $mode=='forms') $fields = build_array('mail', $ID, $mode);
else if ($mode=='data')              $fields = build_array('mail', $ID, 'forms');

// other values of the form
if (isset($_POST['subj']))              $formdata['subj']               = xss($_POST['subj']);
if (isset($_POST['body']))              $formdata['body']               = xss($_POST['body']);
if (isset($_POST['placehold']))         $formdata['placehold']          = xss($_POST['placehold']);
if (isset($_POST['receipt']))           $formdata['receipt']            = xss($_POST['receipt']);
if (isset($_POST['single']))            $formdata['single']             = xss($_POST['single']);
if (isset($_POST['sender_ID']))         $formdata['sender_ID']          = (int)$_POST['sender_ID'];
if (isset($_POST['dirname']))           $formdata['dirname']            = xss($_POST['dirname']);

if (isset($_POST['additional_fax']))    $formdata['additional_fax']     = xss($_POST['additional_fax']);

// Added GET option when email is sent by the 'mailto' javascript funtion
if (isset($_GET['recipient']))          $formdata['additional_mail']    = xss($_GET['recipient']);
if (isset($_POST['additional_mail']))   $formdata['additional_mail']    = xss($_POST['additional_mail']);

if (isset($_POST['cc']))                $formdata['cc']                 = xss($_POST['cc']);
if (isset($_POST['bcc']))               $formdata['bcc']                = xss($_POST['bcc']);

if (isset($_POST['modify_c']))          $formdata['modify_c']           = xss($_POST['modify_c']);

if (!isset($formdata['mem'])) {
    if (isset($_POST['mem']))               $formdata['mem']                = xss_array($_POST['mem']);
}
if (!isset($formdata['con'])) {
    if (isset($_POST['con']))               $formdata['con']                = xss_array($_POST['con']);
}

if (isset($formdata['persons']) && ($mode == "forms" || $formdata['modify_c']<> '')) {
    $persons = $formdata['persons'];
}
define('MODE',$mode);
include_once('./mail_'.MODE.'.php');

if ($justform > 0) echo "\n</div>\n";

echo '

</div>
</body>
</html>
';


/**
 * initialize the mail stuff and make some security checks
 *
 * @return void
 */
function mail_init() {
    global $ID, $mode, $action, $output, $justform;

    $output = '';

    $ID       = $_REQUEST['ID']       = isset($_REQUEST['ID'])       ? (int) $_REQUEST['ID']       : 0;
    $action   = $_REQUEST['action']   = isset($_REQUEST['action'])   ? xss($_REQUEST['action'])    : '';
    $justform = $_REQUEST['justform'] = isset($_REQUEST['justform']) ? (int) $_REQUEST['justform'] : 0;

    if (!isset($_REQUEST['mode']) ||
    !in_array($_REQUEST['mode'], array('view', 'forms', 'data', 'accounts', 'down', 'fetch', 'forms',
    'list', 'options', 'rules', 'send', 'send_form', 'sender'))) {
        $_REQUEST['mode'] = 'view';
    }
    $mode = xss($_REQUEST['mode']);
}

/**
 * Checks if there ara a trash can for the provided user
 *
 * @param int $user_ID user ID
 * @return int trash can ID
 */
function check_trash_can($user_ID) {
    global $dbTSnull, $user_group;

    // first we will check if trash can exists
    $query = "SELECT ID FROM ".DB_PREFIX."mail_client
               WHERE trash_can = 'Y' 
               AND von = ".(int)$user_ID;
    $result = db_query($query) or db_die();

    if ($row = db_fetch_row($result)) {
        $trashCanId = $row[0];
    }
    else {
        // no trash can found, we will create one
        $query = "INSERT INTO ".DB_PREFIX."mail_client
                               (von,       date_received,typ, subject,              parent,acc,      acc_write,gruppe,     date_inserted, trash_can )
                        values (".(int)$user_ID.", '$dbTSnull',   'd', '".__("Trash Can")."',0,    'private','',".(int)$user_group.",'$dbTSnull',   'Y')";
        $result = db_query($query) or db_die();

        $query = "SELECT ID FROM ".DB_PREFIX."mail_client
               WHERE trash_can = 'Y' 
               AND von = ".(int)$user_ID;

        $result = db_query($query);
        if ($row = db_fetch_row($result)) {
            $trashCanId = $row[0];
        }
        else {
            die("Please, contact the sysamdin!");
        }

    }

    return $trashCanId;
}

/**
 * This function will check if the mail is into the trash can or not
 *
 * @param int $mail_ID mail ID
 * @param int $trash_can_ID trash can folder ID
 * @return boolena true or false if the mail is on trash can
 */
function in_tash_can($mail_ID, $trash_can_ID) {

    while (($mail_ID <> $trash_can_ID) && ($mail_ID <> 0) && ($mail_ID <> '')) {

        $query = "SELECT parent from ".DB_PREFIX."mail_client
                            where ID = ".(int)$mail_ID;

        $result = db_query($query);

        if ($row = db_fetch_row($result)) {
            $mail_ID = $row[0];
        }
        else {
            $mail_ID = 0;
        }
    }
    if ($mail_ID == $trash_can_ID) {
        return true;
    }
    else {
        return false;
    }

}


/**
 * Enter description here...
 *
 * @param unknown_type $mail2con
 */
function update_mail_to_contact($mail2con) {
    global $user_ID, $dbIDnull;
    // first of all - delete any rule for incoming/outgoing mail for this user :-))
    $result = db_query("delete from ".DB_PREFIX."mail_rules
                         where von = ".(int)$user_ID." and
                               type like 'mail2con'") or db_die();
    if ($mail2con <> '') {
        $result = db_query("insert into ".DB_PREFIX."mail_rules
                               (von,       type    )
                       values  (".(int)$user_ID.", 'mail2con')") or db_die();
    }
}

/**
 * This function will remove the tags not necessary on body emails (also the tags not acepted by the html editor)
 *
 * @param string $text Body email
 * @return string body email without selected tags and content
 */
function clean_email($text) {


    // Remove only the tags
    $tags = Array("html","body","!DOCTYPE");
    foreach ($tags as $tag) {
        $text = preg_replace("/<\/?".$tag."(.|\s)*?>/","",$text);
    }

    // Remove the tag and the content
    $tags = Array("head","script");
    foreach ($tags as $tag) {
        $text = preg_replace("/<".$tag.">(.|\s)*?<\/".$tag.">/","",$text);
    }
    // Remove emails between tags (e.g. <john@example.com> )
    $text = preg_replace("/<([^>]+@[^>]+)>/", ' $1 ', $text);

    return $text;
}
?>
