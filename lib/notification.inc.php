<?php

// notification.inc.php - PHProjekt Version 5.1
// copyright  ©  2000-2005 Albrecht Guenther  ag@phprojekt.com
// www.phprojekt.com
// Author: Franz Graf, $Author: polidor $
// $Id: notification.inc.php,v 1.21.2.5 2007/04/24 01:19:03 polidor Exp $

// check whether lib.inc.php has been included
if (!defined('lib_included')) die('Please use index.php!');

/**
* Base notification class.
* example:
* $notify = new Notification($user_ID, $user_group, "TODO", "all",
*                            "todo.php?&mode=forms&ID=54",
*                            "this is the body");
* // set a specific title (Different from default)
* $notify->text_title = "A new TODO reached phprojekt!";
* $notify->notify();
*/
class Notification {

    var $user_id     = null;
    var $module      = null;
    var $backlink    = null;
    var $logintoken_validity = 7; // logintoken expires after this amount of days

    // sender-data
    var $sender      = array('firstname' => '', 'lastname' => '', 'email' => '');

    // message-stuff
    var $text_title = '';
    var $text_body  = array(); // array containing one line per array-element
    var $attachment = null;

    /**
     * each component of the array is itself an array with the keys:
     * user_id => (int) id, email => (string) email, setting => (int)
     * setting is one of $this->EMAIL, $this->NONE, .. (see below)
     * 
     * @access private
     * @var array 
     */
    var $recipients = array();

    /**
     * notification settings (regard these as finals)
     * add new options (InstantMessenger) here or extend the class
     *
     * @access public
     * @final
     */
    var $NONE  = 0;
    var $EMAIL = 1;

    /**
     * creates a Notification
     * If an attachment is needed, set it after creating the notification-object.
     * Don't continue blowing up the constructor.
     * $selection usually originates from the output of access.inc.php:assign_acc()
     *
     * @access public
     * @param string $module directoryname of the module
     * @param string $selection 'all' or 'group' (meaning all members of the user's group except himself)
                            OR 'private' (no notification at all)
                            OR an array containing user_ids
                            OR a serialized array containing shortnames as returned from access.inc.php:assign_acc()
     * @param string $backlink url relative to the module-directory 
                        (i.e. $backlink = todo.php?mode=foo&...)
     */
    function Notification($user_id, $user_group, $module, $selection, $backlink, $body,$head='',$title='', $cancel_default_text=0,$alt_sender='') {
        $this->user_id    = $user_id;
        $this->module     = $module;
        
        // if it is an addon the backlink will have the complete url
        if (substr($backlink,0,6) == 'addons') {
            $this->backlink   = PHPR_HOST_PATH.PHPR_INSTALL_DIR.$backlink;
        }
        else {
            $this->backlink   = PHPR_HOST_PATH.PHPR_INSTALL_DIR."index.php?module=".$this->module.$backlink;
        }


        // some defaults
        if ($alt_sender <> '') $this->sender['email'] = $alt_sender;
        else $this->sender   = $this->get_sender_data($user_id);
        
        if ($title == ''){
            $this->text_title = $module;
        }
        else {
            $this->text_title = $title;
        }
        $this->text_body  = $this->create_body($body,$cancel_default_text);
        //optional headers
        $this->head       = $head;

        // unserialize $selection if needed (assign_acc() sometimes really gives weird output)
        if ( false != unserialize((string)$selection) ) {
            $selection = $this->shortnames_to_ids($selection);
        }
        if (is_string($selection)) $selection = strtolower($selection);
        $this->recipients = $this->get_recipients($selection, $user_group);
    }


    /**
     * translate a serialized shortname-string into corresponding userids
     *
     * @access private
     * @param string $selection serialized array with shortnames
     * @param array with userIDs as values (empty array possible)
     */
    function shortnames_to_ids($selection) {
        $ids = array();
        $names = unserialize($selection);
        if (!is_array($names)){
            return $ids;
        }

        $names = "('".implode("','", $names)."')";

        $query = "SELECT id FROM ".DB_PREFIX."users WHERE kurz IN ".$names;
        $result = db_query($query);
        while ($row = db_fetch_row($result)) {
            $ids[] = $row[0];
        }

        return $ids;
    }

    /**
     * Get the sender's data.
     *
     * @param int $user_id id of the sender
     * @return array sender
     */
    function get_sender_data($user_id) {
        $query = "SELECT vorname, nachname, email
                    FROM ".DB_PREFIX."users
                   WHERE ID = ".(int)$user_id;

        $result = db_query($query) or db_die();
        $row = db_fetch_row($result);
        $sender['firstname'] = $row[0];
        $sender['lastname']  = $row[1];
        $sender['email']     = $row[2];

        if (empty($sender['email'])) $sender['email'] = "nobody@example.com";

        return $sender;
    }


    /**
     * Send the notification. Extend the class and overload this method if a completely
     * different behaviour is wanted in an addon/module.
     *
     * @access public
     * @uses use_mail();
     */
    function notify() {
        // init mail
        global $user_group;
        $mailobject = use_mail('1');
        // loop through all the recipients
        foreach ($this->recipients as $recipient) {
            if ($recipient['setting'] == $this->NONE) {
                continue;
            }

            // create a logintoken

            // If user don't allow logintokens, then the URL will not have the token
            if (!strstr($this->backlink, "?")) {
                $logintoken = $this->backlink ."?";
            }
            $logintoken = $this->backlink."&change_group=".$user_group;

            // Checking if the recipient allow logintoken
            $query = "SELECT settings from ".DB_PREFIX."users where ID = ".(int)$recipient['user_id'];
            $tmp_result = db_query($query);

            if ($tmp_row = db_fetch_row($tmp_result)) {

                // Checking if there are setting values
                if (isset($tmp_row[0]) && strlen($tmp_row[0]) > 0 ) {

                    // getting the allow logintoken value
                    $temp_settings = unserialize($tmp_row[0]);
                    $temp_allow_logintoken = (isset($temp_settings['allow_logintoken']))? $temp_settings['allow_logintoken']:'';

                    // If the user has setting to allow tokens, then we will append to link the token
                    if ($temp_allow_logintoken == 1) {
                        
                        // Checking if user has a remember me token available
                        // Note: If a user has a remember me token in use it is not necessary to send him the token
                        $query = "SELECT count(*) FROM ".DB_PREFIX."logintoken 
                                      WHERE 
                                          user_ID = ".(int)$recipient['user_id']." 
                                          AND (used = '' OR used is null) 
                                          AND url like '".PHPR_HOST_PATH.PHPR_INSTALL_DIR."index.php?&logintoken=%'";
                        $tmp_result = db_query($query);
                        
                                    
                        if ($tmp_row = db_fetch_row($tmp_result)) {
                            if ($tmp_row[0] == 0) {
                                $logintoken = $this->create_logintoken($this->backlink, $recipient['user_id'], $this->user_id)."&change_group=$user_group ";
                            }
                        }
                        
                    }

                }
            }
            // each "notification method" has it's own function
            // the user may (at some time) decide his preferred notification-method
            if ($recipient['setting'] == $this->EMAIL) {
                $this->send_email($recipient['user_id'], $recipient['email'], $logintoken, $mailobject);
            }
            // else if ($recipient['setting'] == $this->JABBER) {
            //  $this->send_jabber(...);
            // }
        }
    }


    /**
     * sends an email notification to the specified user
     * @access private
     * @uses sendmail::go()
     * @param int    $user       recipient's user_id (not yet needed?)
     * @param string $to         recipients email_address
     * @param string $logintoken 
     * @param send_mail $mailobject the mailobject used to fire out the mails
     */
    function send_email($user_id, $to, $logintoken, $mailobject) {
        // add the logintoken to the body
        $body = $this->text_body;
        $body[] = $logintoken;
        $body = implode(PHPR_MAIL_EOL, $body);

        // fire it out!
        // TODO add an attachment if configured
        $mailobject->go($to, $this->text_title, $body, $this->sender['email'],$this->head);
    }


    /**
     * Creates a logintoken and returnes the URL that must be used to login.
     * (based on add_notification_link())
     *
     * @access private
     * @param string $link      the backlink-url to which the token is appended
     * @param int    $recipient user_id of the recipient
     * @param int    $user_ID   id of the user that triggers the notification
     * @param int    $validity  logintoken expires after this amount of days
     * @return string $link with the appended logintoken-parameter
     */
    function create_logintoken($link, $recipient, $user_ID, $validity = 0) {
        global $dbTSnull;

        if ($validity == 0) {
            $validity = $this->logintoken_validity;
        }

        // generate login token
        $token = md5(uniqid(rand()));
        $token = md5(encrypt($token, $token));

        // generate link and add token-parameter
        if (!strstr($link, "?")) $link .= "?";
        $link .= "&logintoken=".$token;

        // insert token data into database
        $valid_until = date('YmdHis', time() + PHPR_TIMEZONE*3600 + 86400 * $validity);
        $result = db_query("INSERT INTO ".DB_PREFIX."logintoken
                                    ( von,              token,     user_ID,           url, datum, valid )
                             VALUES (".(int)$user_ID.", '$token', ".(int)$recipient.",
                                      '$link', '$dbTSnull', '$valid_until' )") or db_die();
        return $link;
    }


    /**
     * Creates a message-body and returns it.
     * Body will look like ($body = 'hello Foo'):
     *   Todo created by Franz Graf:
     *   hello Foo
     * 
     * The body is returned as an array (one line per arrayelement) and must 
     * later be joined by appropriate end-of-line characters.
     *
     * @access private
     * @param  string $body text that should appear in the body
     * @return array array of lines for the message-body
     */
    function create_body($body,$cancel_default_text=0) {
        $text = array();

        if($cancel_default_text==0)$text[] = ucfirst($this->module)." ".__('Created by')." ".$this->sender['firstname']." ".$this->sender['lastname'].":";
        if (!empty($body)) $text[] = xss($body);

        return $text;
    }


    /**
     * Select all the recipients according to 'selection' and the user-group.
     * The code itself is taken - with some minor modifications - from email_notification()
     * Currently the setting defaults to $this->EMAIL. 
     *
     * @TODO as soon as instant-messengers are implemented, this function should be changed 
     * in order to select not just the email but also the settings (email | instant messenger | none)
     * and the IM-number.
     *
     * @access private
     * @param string $selection 'all' or array of userIds
     * @param int    $user_group group_id
     * @return array indexed array with values:  array('user_id'=>int, 'email' => string, 'setting' => this->NONE or this->EMAIL)
     */
    function get_recipients($selection, $user_group) {
        $recipients = array();
        $default_setting = $this->EMAIL;

        // select members - except the triggering user
        if ($selection == 'private') {
            return array();
        }

        // 1. case: all members of the group
        if ($selection == 'all' || $selection == 'group') {
            // group system: select all users from the current group except
            // the triggering user itself
            if ($user_group) {
                $query = "SELECT u.email, u.ID
                            FROM ".DB_PREFIX."grup_user AS gu,
                                 ".DB_PREFIX."users AS u
                           WHERE gu.grup_ID = ".(int)$user_group."
                             AND gu.user_ID = u.ID
                             AND u.ID <> ".(int)$this->user_id;
            }
            // or from all users if the group system is not enabled
            else {
                $query = "SELECT u.email, u.ID
                            FROM ".DB_PREFIX."users AS u
                           WHERE u.ID <> '$this->user_id'";
            }

            // create array with email adresses
            $result = db_query($query) or db_die();
            while ($row = db_fetch_row($result)) {
                // prevent recipients with empty email address
                if ((!empty($row[0])) && strlen($row[0]) > 0) {
                    $recipients[] = array('user_id' => $row[1],
                    'email'   => $row[0],
                    'setting' => $default_setting);
                }
            }
            unset($query, $result, $row);
        }

        // 2. case: only selected members
        else {
            if (!is_array($selection)) { return array(); }
            foreach ($selection as $uid) {
                $result = db_query("SELECT email, ID
                                   FROM ".DB_PREFIX."users
                                  WHERE ID = ".(int)$uid) or db_die();
                $row = db_fetch_row($result);
                // prevent recipients with empty email address
                if ((!empty($row[0])) && strlen($row[0]) > 0) {
                    $recipients[] = array('user_id' => $row[1],
                    'email'   => $row[0],
                    'setting' => $default_setting);
                }
            }
        }

        // remove duplicate entries
        $recipients = $this->unique_recipients($recipients);
        return $recipients;
    }

    /**
     * Remove duplicate users from the given recipients-array. Users are rgarded
     * duplicate if they have the same userId
     *
     * @param array $recipients indexed array with (relevan) value:  array('user_id'=>int, ...)
     * @return same as input just uniquized
     */
    function unique_recipients($recipients) {
        if (!is_array($recipients)) return array(); // this should NEVER happen!!!
        if (empty($recipients)) return array();

        // choose master
        for ($i=0; $i<count($recipients); $i++) {
            $master = $recipients[$i];

            // remove all copies of master in the remaining array
            for ($j=$i+1; $j<count($recipients); $j++) {
                if ($recipients[$j]['user_id'] == $master['user_id']) {
                    unset($recipients[$j]);
                    $j--; // unset() removed the element, so we gotta recheck this place
                }
            }
        }

        return $recipients;
    }

}

?>