<?php

// mail_send.php - PHProjekt Version 5.2
// copyright  ©  2000-2007 Albrecht Guenther  ag@phprojekt.com
// www.phprojekt.com
// Author: Albrecht Guenther, $Author: polidor $
// $Id: mail_send.php,v 1.34.2.8 2007/05/27 00:41:07 polidor Exp $

// check whether the lib has been included - authentication!
if (!defined("lib_included")) die("Please use index.php!");

// check role
if (check_role("mail") < 2) die("You are not allowed to do this!");

// check form token
check_csrftoken();

// patches
// patch for opera: opera still shows one entry even if you didn't select anything -> unset this first array element
if (count($mem)==1 and $mem[0]=="") unset($mem[0]);
if (count($con)==1 and $con[0]=="") unset($con[0]);
//next patch is for the value transmitted of the checkbox 'single'
if ($single == 'checked') { $single = 'on'; }


// build mail or fax list from all users and/or contacts
if ($action) {
    // if the feature 'insert db values' is chosen, no user can be added :-(
    // reason: the db table 'users' has a different structure
    // And no additional recipient (Cc, Bcc...) can be set.
    if (ereg("db-field:",$body) and ($mem or $profil1 or $cc or $bcc)){
        die("Personalized messages for external contacts only!");
    }
    else{
        for ($i=0; $i < count($mem); $i++) {
            // fetch all names and the respective db field from the users
            $result = db_query("select nachname, ".qss($action)."
                            from ".DB_PREFIX."users
                           where ID = ".(int)$mem[$i]) or db_die();
            $row = db_fetch_row($result);
            // the db field 'mail' or 'fax' has to be non empty
            if ($row[1] <> "") {
                // correct fax adresses
                if ($action == "fax") { $row[1] = preg_replace( "/\D/", "", $row[1] ); }
                // add this entry to the list
                $adr[] = trim($row[1]);
            }
            // no entry found? -> show a message re this record on the screen
            else { echo "$row[0]: $action ".__('Does not exist')." - $action ".__('has been canceled')." ...<br />\n"; }
        }

        // choose profile users
        // condition: profiles have to active in the config, a profile has been selected
        //(of course) and the 'insert db values' feature is inactive
        if (count($mem) == 0 and $profil1) {
            // fetch record
            $result = db_query("select personen
                          from ".DB_PREFIX."profile
                         where ID = ".(int)$profil1) or db_die();
            $row = db_fetch_row($result);
            $adr_profil = unserialize($row[0]);
            // loop over all short names in the array
            for ($i = 0; $i < count($adr_profil); $i++) {
                // fetch email or fax from user
                $result2 = db_query("select ".qss($action)."
                             from ".DB_PREFIX."users
                            where kurz like '$adr_profil[$i]'") or db_die();
                $row2 = db_fetch_row($result2);
                // if he has an email/fax, add him/her to the list
                if ($row2[0] <> "") { $adr[] = $row2[0];  }
            }
        }
    } // end if cond. 'insert db-values'

    // choose profile contacts
    if (count($con) == 0 and $profil2) {
        // fetch record
        $result = db_query("select contact_ID
                          from ".DB_PREFIX."contacts_prof_rel
                         where contacts_profiles_ID = ".(int)$profil2) or db_die();
        while ($row = db_fetch_row($result)) {
            // fetch email or fax from user
            $result2 = db_query("select ".qss($action)."
                             from ".DB_PREFIX."contacts
                            where ID = ".(int)$row[0]) or db_die();
            $row2 = db_fetch_row($result2);
            // if he has an email/fax, add him/her to the list
            if ($row2[0] <> "") { $adr[] = $row2[0];  }
        }
    }

    if (PHPR_CONTACTS) {
        // manual selection of contacts
        for ($i=0; $i < count($con); $i++) {
            $result = db_query("select nachname, ".qss($action)."
                            from ".DB_PREFIX."contacts
                           where ID = ".(int)$con[$i]) or db_die();
            $row = db_fetch_row($result);
            if ($row[1] <> "") {
                if ($action == "fax") { $row[1] = preg_replace( "/\D/", "", $row[1] ); }
                $adr[] = trim($row[1]);
            }
            // no entry found? -> show a message re this record on the screen
            else { echo "$row[0]: $action ".__('Does not exist')."<br /> $action -  ".__('has been canceled')."<br />\n"; }
        }
    }
    // add individual recipients
    if ($additional_mail) { $adr[] = trim($additional_mail); }
    if ($additional_fax)  { $adr[] = trim($additional_fax); }
    // no adresses selected? -> exit
    if (count($adr) == 0) { die(__('Please select at least one (valid) address.')."! <a href='mail.php?mode=send&subject=".strip_tags($subj)."&body=".strip_tags($body)."$sid&amp;csrftoken=".make_csrftoken()."'>&nbsp; ".__('back')."</a>");  }
} // end build recipient list


// **********************
// 1. action: send a mail
// **********************

if ($action == "email") {
    if($account_ID > 0) {
        $result = db_query("SELECT mail_auth, smtp_hostname, smtp_account, smtp_password, pop_hostname, pop_account, pop_password
                          FROM ".DB_PREFIX."mail_account
                         WHERE ID = ".(int)$account_ID) or db_die();
        list($mail_auth, $smtp_hostname, $smtp_account, $smtp_password, $pop_hostname, $pop_account, $pop_password) = db_fetch_row($result);
    }
    else {
        $account_ID = 0;
    }
    //initialize the mail class
    use_mail();
    $mail = new send_mail(PHPR_MAIL_MODE, PHPR_MAIL_EOH, PHPR_MAIL_EOL, $mail_auth, PHPR_LOCAL_HOSTNAME, $smtp_hostname, $smtp_account, $smtp_password, $pop_hostname, $pop_account, $pop_password);

    if($cc) $cc = str_replace(";",",",$cc);
    if($bcc) $bcc = str_replace(";",",",$bcc);


    // ************************
    // Sender/signature routine
    // if 'default' or empty selection - take the usual values
    if ($sender_ID == "default" or !$sender_ID) {
        $sender_email = strip_tags($user_email);
        $signature = "";
    }
    // if the user has chosen another signature/sender profile, fetch this
    elseif ($sender_ID > 0) {
        $result = db_query("SELECT sender, signature
                          FROM ".DB_PREFIX."mail_sender
                         WHERE ID = ".(int)$sender_ID) or db_die();
        list($sender_email, $signature) = db_fetch_row($result);
        $sender_email = strip_tags($sender_email);
        $signature = strip_tags($signature);
    }
    // add signature
    if ($signature) { 
        if ($html == true || $html == "true") {
            $body .= "<br />\n".str_replace("\n","<br />\n",$signature);
        }
        else {
            $body .= "\n".$signature;
        }
    }
    // end sender signature

    // **************
    // recipients part
    // check whether the body contains db-fields. If yes, switch to single mails
    if (ereg("db-field:",$body)) { $single = "on"; }
    // check whether the adresses contain some zz <zz@zz.de> - the <> chars will be taken as html tags :-(
    for ($i=0; $i<count($adr); $i++) {
        if (ereg("<",$adr[$i])) {
            $a1 = explode("<",$adr[$i]);
            $a2 = explode(">",$a1[1]);
            $adr[$i] = $a2[0];
            for($k=2; $k<sizeof($a1); $k++) {
                $a2 = explode(">",$a1[$k]);
                $adr[] = $a2[0];
            }
        }
    }
    // end recipients part

    // free strings from slashes (but keep them for db storage)
    $subj1 = trim(stripslashes($subj));
    $body1 = trim(stripslashes($body));

    // ***********
    // attachments
    // ***********

    // check beforehand whether a forwarded mail do have an attachment. if yes, store them in parts[]
    if ($forwarded_mail > 0) {
        $result2 = db_query("SELECT ID,parent,filename,tempname,filesize
                             FROM ".DB_PREFIX."mail_attach
                            WHERE parent = ".(int)$forwarded_mail) or db_die();
        // loop over all found attachments
        while ($row2 = db_fetch_row($result2)) {
            // assign name of the attachment ..
            $attfile = PATH_PRE.PHPR_ATT_PATH."/".$row2[3];
            // ... and type!
            $attfile_type = find_userfile_type($row2[2]);
            // open the attachtment and put it into the string 'attachment'
            $attachment = fread(fopen("$attfile", "rb"), filesize("$attfile"));
            // add one more element to the array 'parts'
            $parts[] = array ( "ctype" => $attfile_type,"message" => $attachment,"name" => $row2[2] );
        }
    } // end fetch attachments from a forwarded mail

    // fetch attachments from uploaded files
    if (($userfile[0] and $userfile[0] <> "none") or
    ($userfile[1] and $userfile[1] <> "none") or
    ($userfile[2] and $userfile[2] <> "none") or
    count($parts) > 0) {

        // flag for later check
        $attachment_exist = 1;
        // add the selected attachment to the string
        $i = 0;
        foreach($userfile as $userfile1) {
            if ($userfile1 and $userfile1 <> "none") {
                $attachment = fread(fopen("$userfile1", "rb"), filesize("$userfile1"));
                $parts[] = array ( "ctype" => $userfile_type[$i],"message" => $attachment,"name" => $userfile_name[$i]);
            }
            $i++;
        }
    } //end fetch attachments from upload

    // *********
    // send part
    // *********
    // though we switch now to the inmportant part - sending the email,

    // send one mail to all persons
    if ($single <> "on") {
        // add all adresses to the 'To:' field
        $to = implode(",",$adr);

        // additional headers (BUT NOT To, Cc, Bcc, From)
        $headers = headers($html);
        // build the whole mime string with attachments
        $body2 = finish($body1);

        if($attachment_exist) $body2 = multipart($body2);

        //finally the action: mail the mail and show success message
        if ($mail->go($to, $subj1, $body2, $sender_email, $headers, $cc, $bcc, PHPR_MAIL_SEND_ARG)) {
            message_stack_in(__('Your mail has been sent successfully'),"mail","notice");
        }
    }
    // send a single mail to each person
    else {
        if($cc) $adr = array_merge($adr,explode(",",$cc));
        if($bcc) $adr = array_merge($adr,explode(",",$bcc));
        $headers = headers();
        // personalized newsletters? -> fetch the values from db
        if (ereg("db-field:",$body1))  $body2 = insert_db_values($x, $body1);
        else $body2 = $body1;
        //build the whole mime string with attachments
        $body2 = finish($body2);
        if($attachment_exist) $body2 = multipart($body2);
        // now loop over all recipients and send out the mail
        foreach($adr as $x) {

            //finally the action: mail the mail and show success message
            if ($mail->go($x, $subj1, $body2, $sender_email, $headers, "", "", PHPR_MAIL_SEND_ARG)) {
                message_stack_in(__('Your mail has been sent successfully'),"mail","notice");
            }
        }
    } // end bracket send single mails

    // write record to database if mail reader is active
    if (PHPR_QUICKMAIL == 2) {
        // prepare receiver array $adr for storage - implode it to string $adr2
        $i = 0;
        while ($adr[$i]) {
            if ($i > 0) { $adr2 .= ", "; }
            $adr2 .= $adr[$i];
            $i++;
        }
        // look if a default folder for outgoing mails is defined
        $result = db_query("SELECT parent
                          FROM ".DB_PREFIX."mail_rules
                         WHERE von = ".(int)$user_ID." AND
                               type = 'outgoing'") or db_die();
        $row = db_fetch_row($result);
        if ($row[0] > 0) { $parents[0] = $row[0]; }
        // no rule given -> take root level
        else { $parents[0] = 0; }

        // If html is activated then the body needs to be saved on body_html instead of body
        if ($html == true || $html == "true") {
            $body_field = 'body_html';
            $body       = xss($body);
        }
        else {
            $body_field = 'body';
            $body       = strip_tags($body);
        }
        
        // this part is used when email is sent from related contact (i.e. is sent from contact module)
        if (isset($_REQUEST['contact_ID']) && $_REQUEST['contact_ID'] > 0) {
            $contact_ID = (int)$_REQUEST['contact_ID'];
        }
        else {
            $contact_ID = 0;
        }
        
        // idem with projects
        if (isset($_REQUEST['projekt_ID']) && $_REQUEST['projekt_ID'] > 0) {
            $projekt_ID = (int)$_REQUEST['projekt_ID'];
        }
        else {
            $projekt_ID = 0;
        }

        $result = db_query("INSERT INTO ".DB_PREFIX."mail_client
            (von, subject, ".$body_field.", sender, recipient, cc, date_received,touched,typ, parent, date_sent, date_inserted, replied, forwarded, gruppe, account_ID, contact, projekt)
            VALUES
                (".(int)$user_ID.", '".strip_tags($subj)."', '".$body."', '".strip_tags($sender_email)."', '".strip_tags($adr2)."', '".strip_tags($cc)."', '-', 1, 's', ".(int)$parents[0].", '$dbTSnull', '$dbTSnull', 0, 0, '".strip_tags($user_group)."', ".(int)$account_ID.",".(int)$contact_ID.",".(int)$projekt_ID.")") or db_die();

        $result3 = db_query("SELECT ID
                           FROM ".DB_PREFIX."mail_client
                          WHERE date_sent = '$dbTSnull' AND
                                von = ".(int)$user_ID) or db_die();
        $row3 = db_fetch_row($result3);
        $mail_ID = $row3[0];

        // If it is a reply or forward, then update the parent mail
        if ($replied_mail > 0) {
            $query = "UPDATE ".DB_PREFIX."mail_client SET replied = ".(int)$mail_ID."
                      WHERE ID =  ".(int)$replied_mail;
            $temp_result = db_query($query);
        }
        if ($forwarded_mail > 0) {
            $query = "UPDATE ".DB_PREFIX."mail_client SET forwarded = ".(int)$mail_ID."
                      WHERE ID = ".(int)$forwarded_mail;
            $temp_result = db_query($query);
        }

        $i = 0;
        // store attachment
        foreach ($userfile as $userfile1) {
            if ($userfile1 and $userfile1 <> "none") {
                // save file, first create random name
                $att_tempname = "";
                srand((double)microtime()*1000000);
                $char = "123456789abcdefghijkmnopqrstuvwxyzABCDEFGHIJKLMANOPQRSTUVWXYZ";
                while (strlen($att_tempname) < 12) { $att_tempname .= substr($char,(rand()%(strlen($char))),1); }
                // add extension to random name
                $att_tempname .= substr($att_name,-4,4);
                copy($userfile1,PATH_PRE.PHPR_ATT_PATH."/".$att_tempname);
                // write record to db
                $result3 = db_query("insert into ".DB_PREFIX."mail_attach
                                    (parent,   filename,                 tempname,      filesize     )
                             values (".(int)$mail_ID.",'$userfile_name[$i]','$att_tempname',".(int)$userfile_size[$i].")") or db_die();
                $i++;
            }
        }
    }

    // empty subject & body
    $subject = "";
    $body    = "";
    $action  = "";
    // set mode to email
    $form = "email";

}

// *********************
// 2. action: send a fax
// *********************

elseif ($action == "fax") {
    $fname = tempnam("", "fax");
    $fp = fopen("$fname", "a");
    fputs($fp,"$subj\n\n\n");
    fputs($fp, "$body\n");
    fclose($fp);
    for ($i=0; $i < count($adr); $i++) {
        // delete all non-numeric characters
        $adr[$i] = ereg_replace("[^0-9]","",$adr[$i]);
        // build fax string ...
        $faxstring = PHPR_FAXPATH." -n -f $user -R -r '$subject' -d $adr[$i] $fname";
        // ... and run sendfax
        $result = system(EscapeShellCmd($faxstring));
        if ($result) { echo "fax to $adr[$i] sent<br />"; }
        else { echo "error while sending fax to $adr[$i]!<br />"; }
    }
    $action = "";
    // set mode to fax
    $form = "fax";
}

// ********************
// last action: send SMS
// ********************

elseif ($action == "send_sms") {
    // limit the text length to 160 chars - tha's at least the limit in germany :-)
    $body = substr($body, 0, 159);
    // no receiver, no number given? -> die!
    if ($adr[0] == "" and !$smsnumber) { die(__('Please select at least one (valid) address.')); }
    else {
        for ($i=0; $i < count($adr); $i++) {
            $result = db_query("select mobil, nachname
                            from ".DB_PREFIX."contacts
                           where ID = ".(int)$adr[$i]) or db_die();
            $row = db_fetch_row($result);
            if ($row[0] <> "") {
                $nr = $row[0]."@".$smspath;
                if ($mail->go($nr, $subj, $body, $user_email)) { echo "$row[1]: $mail_text3a<br />"; }
            }
        }
        if ($smsnumber) {
            $nr = $smsnumber."@".$smspath;
            if ($mail->go($nr, $subj, $body, $user_email)) { echo "$smsnumber: $mail_text3a<br />"; }
        }
    }
    // set mode to sms
    $form = "sms";
}

$fields = build_array($module, $ID, 'view');

// if the call comes from any other module, don't display anything else :)
if (!$justform) {
    if ($viewmode == 'lib') {}
    // otherwise display the mail send form or the list view (in case the full mail reader has been installed
    elseif(PHPR_QUICKMAIL == 1) {
        //include(PATH_PRE.'mail/mail_send_form.php');
        $query_str = "mail.php?mode=send_form";
        header('Location: '.$query_str);
        die();
    }
    else {
        //include(PATH_PRE.'mail/mail_view.php');
        $query_str = "mail.php?mode=view";
        header('Location: '.$query_str);
        die();
    }
}
else {
    echo '<script type="text/javascript">ReloadParentAndClose();</script>';
}
// *********
// functions
// *********

// find out the type of the attachment of return the right value
function find_userfile_type($name) {

    $filetypes = array("gif" => "image/gif", "jpg" => "image/jpeg", "png" => "image/png", "xls" => "application/msexcel",
    "ppt" => "application/mspowerpoint", "doc" => "application/msword",  "pdf" => "application/pdf",
    "rtf" => "text/rtf", "zip" => "application/zip", "mp3" => "audio/mpeg", "txt" => "text/plain",
    "php" => "application/x-httpd-php", "tar.gz" => "application/x-gzip");

    if (substr($name,-4,1) == ".") {
        // check whether the extension is in the small list above
        foreach ($filetypes as $extension => $extstring) {
            if (substr($name,-3) == $extension) { return $extstring; }
        }
    }
    // nothing found - just give a simple text format, maybe it helps :-)
    $extstring = "unknown/unknown";
    return $extstring;
}

//function build mail part
function build_message($part) {
    $message = $part["message"];
    $message = chunk_split(base64_encode($message));
    return "Content-Type: ".$part["ctype"].($part["name"]?"; name = \"".$part["name"]."\"" : "")."\nContent-Transfer-Encoding: base64\n\n$message\n";
}

// search the body for special strings containing a db table name and replace it with the values from this contact
function insert_db_values($email,$body) {
    // search the body - go into the loop as long as you can find THE flag :-)
    while (ereg("\|db-field\:",$body)) {
        // only take the first part, leave possible other replaces for the moment, will be handled in the next loop
        // first extract the body until the first separator  ...
        $parts1 = explode("|db-field:",$body,2);
        // .. now the rest of the body ...
        $parts2 = explode(")|",$parts1[1],2);
        $parts3 = explode("(",$parts2[0]);
        $result = db_query("select ".$parts3[0]."
                          from ".DB_PREFIX."contacts
                         where email = '$email'") or db_die();
        $row = db_fetch_row($result);
        // rebuild the body string with the db field value
        $body = $parts1[0].$row[0].$parts2[1];
    }
    return $body;
}

function headers($html='false') {
    global $sender_email, $receipt;

    // This is the right place to add more headers!
    if ($html == 'true' || $html == true) {
        $mime  = 'MIME-Version: 1.0' . "\r\n";
        $mime .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
    }
    else {
        $mime .= 'Content-type: text/html; charset='.LANG_CODE. "\r\n";
    }

    $mime .= "Reply-To:".$sender_email."\nSender:".$sender_email."\nReturn-Path:".$sender_email."\n";

    // receipt
    if ($receipt) $mime .= "Disposition-Notification-To:".$sender_email."\n";
    $mime .= "Date: " . date("r")."\n";


    return trim($mime);
}

function multipart($body,$html='false') {
    global $parts,$headers;
    $ctype="text/plain";
    if($html=='true' || $html == true) "text/html";
    // add the body to the string
    if (!empty($body)) { $parts[] = array ( "ctype" => $ctype,"message" => xss($body),"name" => "" ); }
    // end of buiding the array parts[] with several contents,
    // continue to build the string '$mime' by imploding the array parts ...

    // begin the mail string with identification
    $boundary = "b".md5(uniqid(time()));
    $multipart = "MIME-Version: 1.0\nContent-Type: multipart/mixed; boundary=\"".$boundary."\"\n\nThis is a MIME encoded message.\n\n--".$boundary;
    // add each element (body, attachments) to the string
    for($i = count($parts)-1; $i >= 0; $i--) { $multipart .= "\n".build_message($parts[$i])."--".$boundary; }
    // terminate the multipart string\"
    $multipart .= "--\n";
    $headers .= "\n".trim($multipart);
    return "";
    // end build multipart.
}

// line length respecting RFC 2822
function finish($text) {
    return wordwrap($text,78);
}

?>
