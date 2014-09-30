<?php
if (!defined('lib_included')) die('Please use index.php!');
//  Method to get mail
class fetchmail
{
    var $pop_host;
    var $pop_type;
    var $pop_user;
    var $pop_pass;
    var $acc_string;

    // number of emails in the server
    var $numofmails;

    // array email per each mail => (message_ID, date, header,
    //                               sender, recipient, cc, reply_to,
    //                               body_text, body_html,
    //                               attachment=>(att_enc, att_size, att_name, att_file))
    var $email;


    /**
     * Class constructor
     * @author: Gustavo Solt
     * @param string host   - POP Host of mail connection (it could be the access string complete)
     * @param string user   - POP username
     * @param string pass   - POP password
     * @param string type   - Type of mail connection, for use with the var $port in lib.inc.php
     * @param string folder - Folder to be open, default value INBOX
     * @return class inicialized
     */
    function fetchmail($host, $user, $pass, $type = 'pop3', $folder = 'INBOX') {
        
        // The param is directly the acess string
        if (strpos($host,":") > 0) {
            $this->acc_string = $host;
        }
        else { // whe need host and type
            if (empty($host)) {
                die("<b>The function need hostname or connection string</b>");
            }
            
            $this->pop_host   = $host;
            $this->pop_type   = $type;
            $this->pop_folder = $folder;
            
        }
        if (empty($user) && strpos($host,":") > 0) {
            
            die("<b>The function need a username</b>");
        }
        if (empty($pass)) {
            die("<b>The function need a password</b>");
        }
        $this->pop_user = $user;
        $this->pop_pass = $pass;
    }


    /**
     * Function to connect to mail server
     * @author: Gustavo Solt
     *
     * @return unknown
     */
    function connect() {
        global $port;
        
        if (empty($this->acc_string)) {
            // connect to POP3
            if (version_compare(phpversion(),'5.0.0') < 0) {
                $this->acc_string = "{".$this->pop_host.":".$port[$this->pop_type]."}".$this->pop_folder;
            }
            else {
                $this->acc_string = "{".$this->pop_host.":".$port[$this->pop_type]."}".$this->pop_folder;
            }
        }

        // old version (without check for PHP4) ?
        // $acc_string = "{".$this->pop_host.":".$port[$this->pop_type]."}INBOX";
        
        $mbox = imap_open ($this->acc_string, $this->pop_user, $this->pop_pass);
        
        
        if (!$mbox) {
            echo "<b>".__('Access error for mailbox')." {$this->pop_host}!</b>:<br />".imap_last_error()."<br />";
        }
        return $mbox;
    }

    /**
     * Function to read the mails from mail server and return the mail list in an array
     * @author: Gustavo Solt
     *
     * @param boolean $get_body determine if the body needs to be downloaded or not
     * @return array with the mail list
     */
    function get_mail_list($get_body = false) {


        // initialized return var
        $email = array();

        // connect to POP3
        $mbox = $this->connect();

        // how many mails are on server?
        $numofmails = imap_num_msg($mbox);
        $this->numofmails = $numofmails;

        // get emails detail from server
        $messages_detail = imap_fetch_overview($mbox,"1:$numofmails",0);

        // check if there are messages on server
        if (is_array($messages_detail) && count($messages_detail) > 0) {


            // foreach message we will add the necessary information on email array
            foreach ($messages_detail as $dummy => $one_message) {

                // message possion (for reference)
                $i = $one_message->msgno;

                // calculating the message size
                if ($one_message->size > 1000000)  {
                    $msize = floor($one_message->size/1000000)." M";
                }
                elseif ($one_message->size > 1000) {
                    $msize = floor($one_message->size/1000)." k";
                }
                else {
                    $msize = $one_message->size;
                }
                $email[$i]['message_ID'] = substr($one_message->message_id,1,-1);

                // get subject
                $email[$i]['subject'] = "";
                
                $subject_array = imap_mime_header_decode($one_message->subject);
                
                // get each part of the subject
                for ($j=0; $j<count($subject_array); $j++) {
                    
                    if (strpos($subject_array[$j]->charset, "TF-8") > 0) {
                        $subject_array[$j]->text = utf8_decode($subject_array[$j]->text);
                    }
                    
                    $email[$i]['subject'] .= $subject_array[$j]->text;
                }
                

                $email[$i]['from']       = $one_message->from;
                $email[$i]['date']       = $one_message->date;
                $email[$i]['size']       = $msize;

                if ($get_body) {
                    // first get the  plain part ...
                    $body_tmp = $this->get_part($mbox, $i, "TEXT/PLAIN");
                    $body_tmp1 =imap_mime_header_decode($body_tmp);
                    $email[$i]['body_text'] = addslashes(ereg_replace("\r","",$body_tmp1[0]->text));
                    // ... and then the html part
                    $body_tmp = $this->get_part($mbox, $i, "TEXT/HTML");
                    $email[$i]['body_html'] = addslashes($body_tmp);
                }

            }
        }

        /*
        for ($i = 1; $i <= $numofmails; $i++) {
        // get the header
        $head = imap_header ($mbox,$i);

        // message ID
        $email[$i]['message_ID'] = substr($head->message_id,1,-1);

        // header information
        $date = $head->date;
        $email[$i]["date"] = strftime("%Y%m%d%H%M%S",$head->udate);

        // get subject
        $subject_array = imap_mime_header_decode($head->subject);
        $email[$i]['subject'] = $subject_array[0]->text;
        }
        */
        // close access to mailbox
        imap_close($mbox);

        return $email;
    }

    /**
     * Function to read the mails form the mailserver and return an array with data
     * If have attach, stored to PHPR_DOC_PATH
     * 
     * @author: Gustavo Solt
     * @param array $downloads array with emails ids to be downloaded from server. If is set to 'all' all emails will be download.
     * @param array $deletes array with the emails ids to be deleted from server. If is set to 'all' all emails will be deleted.
     * @param string $file_path path to save the attachments
     * @return boolean true
     */
    function get_mail($downloads = 'all', $deletes = 'all', $file_path = PHPR_ATT_PATH) {

        // connect to POP3
        $mbox = $this->connect();

        // how many mails have?
        $numofmails = imap_num_msg($mbox);
        $this->numofmails = $numofmails;

        // prepare email arrays (the list to be deleted and the list to be downloaded)
        if (!is_array($downloads) && $downloads == 'all') {
            $downloads = array( 0 =>'all');
        }
        else if (!is_array($downloads)) {
            $downloads = array();
        }

        if (!is_array($deletes) && $deletes == 'all') {
            $deletes = array( 0 =>'all');
        }
        else if (!is_array($deletes)) {
            $deletes = array();
        }

        // get the number of emails on server
        $numofmails = imap_num_msg($mbox);
        $this->numofmails = $numofmails;

        $email = array();
        for ($i = 1; $i <= $numofmails; $i++) {
            // get the header
            $head = imap_header ($mbox,$i);

            // check whether this message is marked to download
            if (in_array($i,$downloads) || $downloads[0] == 'all') {
                $skip_mail = false;
            }
            else {
                $skip_mail = true;
            }

            // if there is already an entry in
            if (!$skip_mail) {
                // message ID
                $email[$i]['message_ID'] = substr($head->message_id,1,-1);

                // parent
                if($head->references<>'')$email[$i]['parent'] =$head->references;
                else $email[$i]['parent'] = $head->in_reply_to;

                // header information
                $date = $head->date;
                $email[$i]["date"] = strftime("%Y%m%d%H%M%S",$head->udate);

                // save header in field
                $email[$i]['header'] = addslashes(imap_fetchheader($mbox,$i));

                // create 'from' string
                $from_head  = $head->from[0];
                // in case the name variable is empty, take the value from the mailbox
                $sender = imap_mime_header_decode($from_head->personal);
                $from_head->personal = addslashes($sender[0]->text);

                if ($from_head->personal == "") {
                    $from_head->personal = $from_head->mailbox."@".$from_head->host;
                }
                $email[$i]['sender'] = addslashes($from_head->personal."<".$from_head->mailbox."@".$from_head->host.">");
                $email[$i]['senderemail'] = $from_head->mailbox."@".$from_head->host;

                // create 'to' string
                $x = 0;
                $email[$i]['recipient'] = "";
                while ($head->to[$x] <> "") {
                    $to_head = $head->to[$x];
                    $to_head_pers = imap_mime_header_decode($to_head->personal);
                    $to_head_personal = addslashes($to_head_pers[0]->text);
                    if ($to_head_personal == "") {
                        $to_head_personal = $to_head->mailbox."@".$to_head->host;
                    }
                    if ($x > 0) {
                        $email[$i]['recipient'] .= ", ";
                    }
                    $email[$i]['recipient'] .= addslashes($to_head_personal."<".$to_head->mailbox."@".$to_head->host.">");
                    $x++;
                }

                // create 'cc' string
                $x = 0;
                $email[$i]['cc'] = "";
                while ($head->cc[$x] <> "") {
                    $cc_head = $head->cc[$x];
                    $cc_head_pers = imap_mime_header_decode($cc_head->personal);
                    $cc_head_personal = addslashes($cc_head_pers[0]->text);
                    if ($cc_head_personal == "") {
                        $cc_head_personal = $cc_head->mailbox."@".$cc_head->host;
                    }
                    if ($x > 0) {
                        $email[$i]['cc'] .= ", ";
                    }
                    $email[$i]['cc'] .= addslashes($cc_head_personal."<".$cc_head->mailbox."@".$cc_head->host.">");
                    $x++;
                }

                // reply to
                $reply_to_head = $head->reply_to[0];
                $reply_to_head_pers = imap_mime_header_decode($reply_to_head->personal);
                $reply_to_head_personal = addslashes($reply_to_head_pers[0]->text);
                if ($reply_to_head_personal == "") {
                    $reply_to_head_personal = $reply_to_head->mailbox."@".$reply_to_head->host;
                }
                $email[$i]['reply_to'] = addslashes($reply_to_head->personal."<".$reply_to_head->mailbox."@".$reply_to_head->host.">");

                // get subject
                //$subject_array = imap_mime_header_decode($head->subject);
                //$email[$i]['subject'] = addslashes($subject_array[0]->text);
                
                // get subject
                $email[$i]['subject'] = "";
                
                $subject_array = imap_mime_header_decode($head->subject);
                
                // get each part of the subject
                for ($j=0; $j<count($subject_array); $j++) {
                    
                    if (strpos($subject_array[$j]->charset, "TF-8") > 0) {
                        $subject_array[$j]->text = utf8_decode($subject_array[$j]->text);
                    }
                    
                    $email[$i]['subject'] .= addslashes($subject_array[$j]->text);
                }

                // body
                // first get the  plain part ...
                $body_tmp = $this->get_part($mbox, $i, "TEXT/PLAIN");
                $body_tmp1 =imap_mime_header_decode($body_tmp);
                $email[$i]['body_text'] = addslashes(ereg_replace("\r","",$body_tmp1[0]->text));
                // ... and then the html part
                $body_tmp = $this->get_part($mbox, $i, "TEXT/HTML");
                $email[$i]['body_html'] = addslashes($body_tmp);

                // attachments?
                $attach = imap_fetchstructure($mbox,$i);
                $attachments = $attach->parts;
                // loop over all attachments
                $int = 0;
                for ($a=0; $a < count($attachments); $a++) {
                    // check whether the info is in parameters or dparameters
                    if ($attachments[$a]->ifparameters) {
                        $count = count($attachments[$a]->parameters);
                    } else {
                        $count = count($attachments[$a]->dparameters);
                    }

                    for ($c = 0; $c < $count; $c++) {
                        // same thing here: infos could be in parameters or dparameters
                        if ($attachments[$a]->ifparameters) {
                            $param = $attachments[$a]->parameters[$c];
                        } else {
                            $param = $attachments[$a]->dparameters[$c];
                        }
                        if (eregi("name",$param->attribute)) {

                            // fetch encoding
                            $email[$i]['attachment'][$int]['att_enc'] = $attachments[$a]->encoding;

                            // fetch filesize
                            $email[$i]['attachment'][$int]['att_size'] = $attachments[$a]->bytes;

                            // fetch attachment name
                            $att_name1 = $param->value;

                            // decode name
                            $att_name2 =imap_mime_header_decode($att_name1);
                            $email[$i]['attachment'][$int]['att_name'] = addslashes($att_name2[0]->text);

                            // here is a workaround for a real strange problem - admins report that sometimes
                            // the file name to store begins with a " ' " causing a sql error -> workaround: delete the ' :-))
                            while (substr($email[$i]['attachment'][$int]['att_name'],0,1) == "'") {
                                $email[$i]['attachment'][$int]['att_name'] = substr($email[$i]['attachment'][$int]['att_name'],1);
                            }
                            // fetch content of attachment
                            $a1 = $a + 1;
                            $email[$i]['attachment'][$int]['att_content'] = imap_fetchbody($mbox, $i, $a1);
                            // decode
                            if ($email[$i]['attachment'][$int]['att_enc'] == 2) {
                                $email[$i]['attachment'][$int]['att_content'] = imap_binary($email[$i]['attachment'][$int]['att_content']);
                            }
                            if ($email[$i]['attachment'][$int]['att_enc'] == 3) {
                                $email[$i]['attachment'][$int]['att_content'] = imap_base64($email[$i]['attachment'][$int]['att_content']);
                            }
                            if ($email[$i]['attachment'][$int]['att_enc'] == 4) {
                                $email[$i]['attachment'][$int]['att_content'] = imap_qprint($email[$i]['attachment'][$int]['att_content']);
                            }
                            // save file, first create random name
                            $att_tempname = "";
                            srand((double)microtime()*1000000);
                            $char = "123456789abcdefghijkmnopqrstuvwxyzABCDEFGHIJKLMANOPQRSTUVWXYZ";
                            while (strlen($att_tempname) < 12) {
                                $att_tempname .= substr($char,(rand()%(strlen($char))),1);
                            }

                            // add extension to random name
                            $att_tempname .= strstr($att_name,'.');

                            // write file
                            $email[$i]['attachment'][$int]['att_file'] = PATH_PRE.$file_path.'/'.$att_tempname;
                            $email[$i]['attachment'][$int]['att_tempname'] = $att_tempname;
                            $fp = @fopen($email[$i]['attachment'][$int]['att_file'],'w+');
                            $fw = fwrite($fp,$email[$i]['attachment'][$int]['att_content']);
                            unset($email[$i]['attachment'][$int]['att_content']);
                            fclose($fp);

                            // Number of attachment
                            $int++;

                        }
                    }
                } // end loop over attachments
            } // end bracket to skip mail

            // mark mails for later deletion if $no_del is not set.
            if (in_array($i, $deletes)) {
                $no_del = false;
            }
            elseif ($deletes[0] <> 'all') {
                $no_del = true;
            }

            if (!$no_del)  {
                imap_delete($mbox, $i);
            }

        } // end for each mail

        // delete marked mails in the mailbox
        imap_expunge($mbox);

        // close access to mailbox
        imap_close($mbox);

        $this->email = $email;

        return true;
    }

    // return only one part of the mail
    // @param int mbox         - imap_stream
    // @param int msg          - number of the email
    // @param string mime_type - mime_type to get
    // @param string/bool      - structure od the mail
    // @param int part_number  - part_number
    // $return string          - a part of the email
    // @author: Albrecht Guenther
    function get_part($mbox, $msg, $mime_type, $structure = false, $part_number = false) {
        if (!$structure) {
            $structure = imap_fetchstructure($mbox, $msg);
        }

        if ($structure) {
            if (strtoupper($mime_type) == strtoupper($this->get_mime_type($structure))) {
                if(!$part_number) {
                    $part_number = "1";
                }
                $text = imap_fetchbody($mbox, $msg, $part_number);
                if($structure->encoding == 3) {
                    return imap_base64($text);
                }
                else if($structure->encoding == 4) {
                    return imap_qprint($text);
                }
                else {
                    return $text;
                }
            }

            /* multipart */
            if($structure->type == 1) {
                while(list($index, $sub_structure) = each($structure->parts)) {
                    if($part_number) {
                        $prefix = $part_number . '.';
                    }
                    $body_tmp = $this->get_part($mbox, $msg, $mime_type, $sub_structure, $prefix . ($index + 1));
                    if($body_tmp) {
                        return $body_tmp;
                    }
                }
            }
        }
        return false;
    }

    // Get the mime type
    // @param string structure - structure of the mail
    // @return string          - mime_type
    // @author: Albrecht Guenther
    function get_mime_type(&$structure) {
        $primary_mime_type = array("TEXT", "MULTIPART", "MESSAGE", "APPLICATION", "AUDIO", "IMAGE", "VIDEO", "OTHER");
        if($structure->subtype) {
            return $primary_mime_type[(int) $structure->type] . '/' . $structure->subtype;
        }
        return "TEXT/PLAIN";
    }

    // Parse subject to obtain some values
    // @param string module - Module to switch
    // @param int mail      - Number of mail
    // @return Array        - Array with mix data
    function parse_subject($module, $mail) {
        $data = array();
        switch ($module) {
            case "helpdesk":
                preg_match_all('%\[(.*)ID\#(.*)\](.*)%s', $this->email[$mail]['subject'], $res);
                $data['module'] = $res[1][0];
                $data['ID']     = $res[2][0];
                $data['title']  = $res[3][0];
                break;
        }
        return $data;
    }

    /**
     * Gets the list of emails on a server and displais it on a table
     *
     * @return string with a table with mail subject, body, sender, date and size
     */
    function get_mail_table() {
        
        // getting the mail list (the true parameter is to get the bodies)
        $mails_on_server = $this->get_mail_list(true);
        
        // if there are emails we will display the table
        if (is_array($mails_on_server) && count($mails_on_server) > 0) {
            
            // Table head
            $list = "<table 'width=100%'>
                         <thead>
                             <tr>
                                 <th align='center'>".__("Subject")."</th>
                                 <th align='center'>".__("Body")."</th>
                                 <th align='center'>".__("Sender")."</th>
                                 <th align='center'>".__("Date")."</th>
                                 <th align='center'>".__("Size")."</th>
                             </tr>
                         </thead>";
            
            // foreach mail on list
            foreach ($mails_on_server as $msgno => $one_message) {

                // mail list row
                $list .= "   <tr>
                                 <td>{$one_message['subject']}</td>
                                 <td>";
                
                // if there are a plain body we will use it, else we will use the html body
                if (!empty($one_message['body_text'])) {
                    $list .= html_out($one_message['body_text']);
                }
                else {
                    $list .= html_out($one_message['body_html']);
                }

                $list .="        </td>
                                 <td>".html_out($one_message['from'])."</td>
                                 <td>{$one_message['date']}</td>
                                 <td align='right'>{$one_message['size']}</td>
                             </tr>";
            }
            
            // end of table
            $list .= "
                          </table>
                      <br />";
        }
        else {  // there aren't emails on server
            $list .= "<br />$user_name / $row[2]: ".__('No new emails on server');
        }

        return $list;
    }
}

?>
