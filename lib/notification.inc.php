<?php
/**
 * Notification class
 *
 * @package    	lib
 * @subpackage 	main
 * @author     	Franz Graf, $Author: nina $
 * @licence     GPL, see www.gnu.org/copyleft/gpl.html
 * @copyright  	2000-2006 Mayflower GmbH www.mayflower.de
 * @version    	$Id: notification.inc.php,v 1.40 2007-11-16 10:46:20 nina Exp $
 */

// check whether lib.inc.php has been included
if (!defined('lib_included')) die('Please use index.php!');

/**
 * Base notification class.
 * @example $notify = new Notification($user_ID, $user_group, "TODO", "all",
 *                            "todo.php?&mode=forms&ID=54",
 *                            "this is the body");
 * // set a specific title (Different from default)
 * $notify->text_title = "A new TODO reached phprojekt!";
 * $notify->notify();
 * @package lib
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
    var $text_body  = array(); // one line per body content
    var $attachment = null;

    /**
     * Each component of the array is itself an array with the keys:
     * user_id => (int) id, email => (string) email, setting => (int)
     * setting is one of $this->EMAIL, $this->NONE, .. (see below)
     *
     * @var array
     */
    var $recipients = array();

    /**
     * Notification settings (regard these as finals)
     * add new options (InstantMessenger) here or extend the class
     *
     * @final
     */
    var $NONE  = 0;
    var $EMAIL = 1;

    /**
     * Use for specials bodys with multipart
     *
     */
    var $use_multipart = false;

    /**
     * Use for vcal attachment
     *
     */
    var $vcal = '';

    /**
     * Creates a Notification
     * If an attachment is needed, set it after creating the notification-object.
     * Don't continue blowing up the constructor.
     * $selection usually originates from the output of access.inc.php:assign_acc()
     *
     * @param int 				$user_id 					- User id
     * @param int 				$user_group 			- Group id from user
     * @param string 			$module_name 			- Name of the model
     * @param mixed 			$selection 				- 'all' or 'group' (meaning all members of the user's group except himself)
     *                   	    					  			        OR 'private' (no notification at all)
     *                       						  					OR an array containing user_ids
     *                       						 				 	OR a serialized array containing shortnames as returned from access.inc.php:assign_acc()
     * @param int 				$backlink_id 				- Task id for subject description
     * @param string 			$backlink 				- Direct link to task
     * @param string 			$body 					- Message for mail
     * @param string 			$subject 					- Subject description without module information
     * @param string 			$action 					- The executed action 'new' or 'change'
     * @param string 			$head 					- Additional mail headers
     * @param unknown_type 	$cancel_default_text	-
     * @param string 	   									alt_sender
     */
    function Notification($user_id, $user_group, $module_name, $selection, $backlink_id, $backlink, $body, $subject, $action = 'new', $head = '', $cancel_default_text = 0, $alt_sender = '', $vcal = '')
    {
    	$this->user_id    = $user_id;
        $this->module     = $module_name;
        $this->text_body = array();

        switch($action)
        {
        	case 'changed':
        		$action_text = __('has changed item in');
        		break;
        	default:
        		$action_text = __('has added a new entry in');
        }

        $user_name 	= slookup('users','nachname,vorname','ID',$user_id,'1');
        if($cancel_default_text==0) $this->text_body[] = $user_name." ".$action_text." ".$module_name.":\n\n";

        if ( substr($backlink,0,6) == 'addons' ) {
            $this->backlink   = PHPR_HOST_PATH.PHPR_INSTALL_DIR.$backlink;
        } else {
            $this->backlink   = PHPR_HOST_PATH.PHPR_INSTALL_DIR.$this->module."/".$this->module.".php?".$backlink;
        }

        if ($alt_sender <> '') 	$this->sender['email'] = $alt_sender;
        else 					$this->sender   = $this->get_sender_data($user_id);

        if ($backlink_id != -1) {
            $this->text_title 	= "[".$module_name."]"."#".$backlink_id.": ".$subject;
        } else {
        	$this->text_title  = "[".$module_name."]"." ".$subject;
        }
        $this->text_body 	= array_merge($this->text_body, $this->create_body($body));
        $this->head 		= $head;

        if (!empty($vcal)) {
            $this->vcal = $vcal;
            $this->use_multipart = true;
        }

        if ( false != unserialize((string)$selection) )
            $selection = $this->shortnames_to_ids($selection);

        if (is_string($selection))
        	$selection = strtolower($selection);

        $this->recipients = $this->get_recipients($selection, $user_group);
    }

    /**
     * translate a serialized shortname-string into corresponding userids
     *
     * @param string		$selection 	- Serialized array with shortnames
     * @param array            				Array with userIDs as values (empty array possible)
     */
    function shortnames_to_ids($selection) {
        $ids = array();
        $names = unserialize($selection);
        if (!is_array($names)){
            return $ids;
        }

        $names = "('".implode("','", $names)."')";

        $query = "SELECT id FROM ".DB_PREFIX."users WHERE kurz IN ".$names." AND is_deleted is NULL";
        $result = db_query($query);
        while ($row = db_fetch_row($result)) {
            $ids[] = $row[0];
        }

        return $ids;
    }

    /**
     * Get the sender's data.
     *
     * @param int			$user_id 		- ID of the sender
     * @return array      					Sender
     */
    function get_sender_data($user_id) {
        $query = "SELECT vorname, nachname, email
                    FROM ".DB_PREFIX."users
                   WHERE ID = ".(int)$user_id."
                     AND is_deleted is NULL";

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
     * @uses use_mail();
     * @param void
     * @return void
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
            if ($recipient['user_id'] == -1) {
            	  $logintoken = '';
            } else {
	            // create a logintoken
	
	            // If user don't allow logintokens, then the URL will not have the token
	            if (!strstr($this->backlink, "?")) {
	                $logintoken = $this->backlink ."?";
	            }
	            $logintoken = $this->backlink."&change_group=".$user_group;
	
	            // Checking if the recipient allow logintoken
	            $query = "SELECT settings from ".DB_PREFIX."users where ID = ".(int)$recipient['user_id']." AND is_deleted is NULL";
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
     * Sends an email notification to the specified user
     *
     * @uses sendmail::go()
     * @param int    			$user          	- Recipient's user_id (not yet needed?)
     * @param string			$to            	- Recipients email_address
     * @param string			$logintoken 	-
     * @param	send_mail		$mailobject 	- The mailobject used to fire out the mails
     * @return void
     */
    function send_email($user_id, $to, $logintoken, $mailobject) {

        $head = "Date: " . date("r")."\n";

        // add the logintoken to the body
        $body = $this->text_body;
        if ($logintoken != null) {
            $body[] = $logintoken;
        }
        $body = implode(PHPR_MAIL_EOL, $body);
        if ($this->use_multipart) {
            $this->head = $head.$this->head.$this->calendar_multipart($body);
            $body = '';
        } else {
            $this->head = $head.$this->head;
        }

        // fire it out!
        // TODO add an attachment if configured
        $mailobject->go($to, $this->text_title, $body, $this->sender['email'],$this->head);
    }

    /**
     * Creates a logintoken and returnes the URL that must be used to login.
     * (based on add_notification_link())
     *
     * @param	string	$link      		- The backlink-url to which the token is appended
     * @param int    	$recipient 	- User_id of the recipient
     * @param int    	$user_ID   	- ID of the user that triggers the notification
     * @param int    	$validity  		- Logintoken expires after this amount of days
     * @return	string					Link with the appended logintoken-parameter
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
     * @param string		$body 	 - Text that should appear in the body
     * @return array        			Array of lines for the message-body
     */
    function create_body($body) {
        $text = array();
        if (!empty($body)) $text[] = xss_purifier($body);
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
     * @param string		$selection   		- 'all' or array of userIds
     * @param int    		$user_group  	- Group_id
     * @return array             				Indexed array with values:
     *                             						array('user_id'=>int, 'email' => string, 'setting' => this->NONE or this->EMAIL)
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
                             AND u.is_deleted is NULL
                             AND u.ID <> ".(int)$this->user_id;
            }
            // or from all users if the group system is not enabled
            else {
                $query = "SELECT u.email, u.ID
                            FROM ".DB_PREFIX."users AS u
                           WHERE u.ID <> '$this->user_id'
                             AND u.is_deleted is NULL";
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
            	if (!is_numeric($uid)) {
            		$recipients[] = array(
            		    'user_id' => -1,
	                    'email'   => $uid,
	                    'setting' => $default_setting
                    );
            	} else {
	                $result = db_query("SELECT email, ID
	                                      FROM ".DB_PREFIX."users
	                                     WHERE ID = ".(int)$uid."
	                                       AND is_deleted is NULL") or db_die();
	                $row = db_fetch_row($result);
	                // prevent recipients with empty email address
	                if ((!empty($row[0])) && strlen($row[0]) > 0) {
	                    $recipients[] = array('user_id' => $row[1],
	                    'email'   => $row[0],
	                    'setting' => $default_setting);
	                }
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
     * @param array 	$recipients 	- Indexed array with (relevan) value:  array('user_id'=>int, ...)
     * @return array             		Same as input just uniquized
     */
    function unique_recipients($recipients) {
        if (!is_array($recipients)) return array(); // this should NEVER happen!!!
        if (empty($recipients)) return array();

        $count = count($recipients);
        // choose master
        for ($i=0; $i<$count; $i++) {
            $master = $recipients[$i];
            if ($master['user_id'] == -1) continue;

            // remove all copies of master in the remaining array
            for ($j=$i+1; $j<$count; $j++) {
                if ($recipients[$j]['user_id'] == $master['user_id']) {
                    unset($recipients[$j]);
                    $j--;     // unset() removed the element, so we gotta recheck this place
                    $count--; // unset() removed the element, so the array is smaller now
                }
            }
        }

        return $recipients;
    }

    // line length respecting RFC 2822
    function finish($text) {
        return wordwrap($text,78);
    }

    function calendar_multipart($body) {
        $parts = array();

        $body = $this->finish($body);

        // add the body to the string
        $parts[] = array ("ctype"     => "text/plain; charset=UTF-8;\n",
                          "message"   => $body,
                          "encode"    => 'Content-Transfer-Encoding: 8bit');
        
        // begin the mail string with identification
        $boundary = "--------------".md5(uniqid(time()));
        $multipart = "\nContent-Type: multipart/mixed;\n boundary=\"".$boundary."\"\n\nThis is a multi-part message in MIME format.\n--".$boundary;

        // add vcal
        $parts[] = array ("ctype"     => "text/calendar; method=REQUEST;\n name=\"calendar.ics\"\n",
                          "message"   => $this->vcal,
                          "encode"    => "Content-Transfer-Encoding: 7bit\nContent-Disposition: inline;\n filename=\"calendar.ics\"");

        
        $multipart .= "\n".$this->build_multipart($parts[0])."\n--".$boundary;
        $multipart .= "\n".$this->build_multipart($parts[1])."\n--".$boundary;
        
        // terminate the multipart string
        $multipart .= "--\n";
        return trim($multipart);
    }

    //function build mail part
    function build_multipart($part) {
        $message = $part["message"];
        return "Content-Type: ".$part['ctype'].$part['encode']."\n\n$message\n";
    }
}
?>
