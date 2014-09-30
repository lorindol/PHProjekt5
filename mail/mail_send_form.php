<?php

// mail_send_form.php - PHProjekt Version 5.2
// copyright  ©  2000-2005 Albrecht Guenther  ag@phprojekt.com
// www.phprojekt.com
// Author: Albrecht Guenther, $Author: polidor $
// $Id: mail_send_form.php,v 1.69.2.2 2007/03/20 16:14:27 polidor Exp $

// check whether the lib has been included - authentication!
if (!defined("lib_included")) die("Please use index.php!");

// check role
if (check_role("mail") < 2) die("You are not allowed to do this!");

// selector-tranformation stuff
require_once(LIB_PATH.'/selector/selector.inc.php');

if ($justform == 2) $onload = array( 'window.opener.location.reload();', 'window.close();' );
else if ($justform > 0) $justform++;

// html default value
if (!isset($html)) {
    if ($compose_mail_default == 'html') {
        $html = true;
    }
    else {
        $html = false;
    }
}

$output = '';

// if no mode is set (email, fax, sms), set default to email
if (!$form) $form = "email";

// only for mail section: check whether mail client sended some data
if ($action2 || $ID) {
    // check permission and fetch data
    $result = db_query("select ID, von, sender, replyto, recipient, cc, subject, body, subject, body_html, account_ID, header   
                          from ".DB_PREFIX."mail_client
                         where ID = ".(int)$ID) or db_die();
    $row = db_fetch_row($result);
    if ($row[0] == 0) { die("no entry found."); }
    if ($row[1] <> $user_ID) { die("You are not allowed to do this!"); }

    // reply? -> give sender as recipient
    if (ereg("&lt;",$row[2])) {
        $sender = explode("&lt;",$row[2]);
        $row[2] = substr($sender[1],0,-4);
    }
    // build email of recipient
    if ($action2 == "reply" or $action2 == "replyall") {
        // take adress from replyto if given
        if ($row[3] <> "")  { $recipient = $row[3]; }
        // otherwise the from adress
        else { $recipient = $row[2]; }
        // add cc and recipients: to recipient if replyall is given
        if ($action2 == "replyall") {
            if ($row[4] <> "") { $recipient .=",".$row[4]; }
            if ($row[5] <> "") { $recipient .=",".$row[5]; }
        }
        
        $account_ID = $row[10];
        $mail_header = $row[11];
        // *********************************
        // Delete own account from recipient
        if ($account_ID > 0) {
            $result_account = db_query("SELECT ID, accountname, username 
                                         FROM ".DB_PREFIX."mail_account 
                                         WHERE ID = ".(int)$account_ID) or db_die();
            
            if ($row_account = db_fetch_row($result_account)) {
                
                // checking if username is the full email (else we will try with the account name)
                if (strpos($row_account[2],"@") > 0) {
                    $account_email = $row_account[2];
                }
                elseif (strpos($row_account[1],"@") > 0) {
                    $account_email = $row_account[1];
                }
                
                // delete own email
                if ($account_email <> "" && !(strpos($recipient,$account_email) === false)) {
                    
                    // This regex will strip the acount email from recipient list
                    $recipient = preg_replace("/(^|,)[^,]*$account_email>?/s", "", $recipient);
                    
                    // if my account is the first on recipient, then the regex will let a ',' at the first char, then, this
                    // will remove it
                    if (strpos($recipient,",") === 0) {
                        $recipient = substr($recipient,1);
                    }
                    
                }
                
            }
        }
        
        
        // End of delete own account
        //***********************************
        
        $formdata['additional_mail'] = $recipient;
        
        // subject
        $subject = "Re: ".$row[6];
    }
    // forwarding ...
    elseif ($action2 == "forward") {
        $subject = "Fw: ".$row[6];
    }
    
    $formdata['subj'] = $subject;
    
    // format body text.
    if ($row[7] <> '' && $row[7] <> ' ') {
        
        // it the email is UTF-8 we will convert it
        if (strpos($mail_header, 'charset=UTF-8') > 0) {
            $row[7] = utf8_decode($row[7]);
        }
        
        
        $body = ereg_replace("\n","\n>",$row[7]);
        $body = ">".wordwrap($body,78,"\n>");
    }
    else {
        $body = "<br />\n<br />\n<i>".$row[2]." ".__("Wrote").":</i><br />\n<br />\n".xss($row[9]); 
        $body = clean_email($body);
        $html = true;
    }
    
    $formdata['body'] = $body;
} // end only for mail section - reply or forward a mail ...


// **************
// begin the form

// tabs
$tabs = array();
if ($justform == 2) $justform = 1;
$buttons = array();
// form start
$hidden = array('mode' => 'send','html'=>$html, 'form'=>$form, 'justform' => $justform);
if (isset($_REQUEST['contact_ID']) && $_REQUEST['contact_ID'] > 0) {
    $hidden['contact_ID'] = (int)$_REQUEST['contact_ID'];
}
if (isset($_REQUEST['projekt_ID']) && $_REQUEST['projekt_ID'] > 0) {
    $hidden['projekt_ID'] = (int)$_REQUEST['projekt_ID'];
}

if (SID) $hidden[session_name()] = session_id();
$buttons[] = array('type' => 'form_start','name'=>'frm', 'hidden' => $hidden, 'enctype' => 'multipart/form-data');
$output = "<div id='global-header' $content_div>";
$output .= get_buttons($buttons);
$output .= get_tabs_area($tabs);
$output .= breadcrumb($module, array(array('title'=>__('New'))));
$output .= '</div>';

// button bar
$buttons = array();

// the 'mail' button is not necessary on mail form
if (!($form == "email")) {
    $buttons[] = array('type' => 'link', 'href' => 'mail.php?mode=send_form&amp;form=email'.$sid.'&amp;csrftoken='.make_csrftoken(), 'text' => __('Mail'), 'active' => false);
}
if (PHPR_FAXPATH) {
    $buttons[] = array('type' => 'link', 'href' => 'mail.php?mode=send_form&amp;form=fax'.$sid.'&amp;csrftoken='.make_csrftoken(), 'text' => __('Fax'), 'active' => false);
}
if ($smspath) {
    $buttons[] = array('type' => 'link', 'href' => 'mail.php?mode=send_form&amp;form=sms'.$sid.'&amp;csrftoken='.make_csrftoken(), 'text' => __('SMS'), 'active' => false);
}
// send mail button
$buttons[] = array('type' => 'submit', 'name' => 'versenden', 'value' => __('Send'), 'active' => false);
// back
if (PHPR_QUICKMAIL == 2) {
    $buttons[] = array('type' => 'link', 'href' => 'mail.php?mode=view'.$sid.'&amp;csrftoken='.make_csrftoken(), 'text' => __('list View'), 'active' => false);
}
$output .= '<div id="global-content">';
$output .= get_buttons_area($buttons);
$output .= get_status_bar();

/*******************************
*    create message fields
*******************************/
// email: account, sender/signature
$form_fields = array();

if ($form == "email") {
    // sender
    // if it is the full mail client, offer the choice of all sender/signatures
    //if ($html!='true') $form_fields[] = array('type' => 'string',  'text' => "<a href='mail.php?mode=send_form&amp;ID=$ID&amp;html=true'>".__('send html mail')."</a>");
    if ($html!='true' && $html != 1 ) $form_fields[] = array('type' => 'string',  'text' => "<label class='label_block' for='activateHtml'></label><a href='#' onclick='activate_html()'>".__('Send HTML Mail')."</a><br />");
    if (PHPR_QUICKMAIL == 2) {
        // Using sockets? Offer the choice of accounts
        if (PHPR_MAIL_MODE) {
            $options = array();
            $options[] = array('value' => '', 'text' => '*'.__('Standard').'*');
            $result = db_query("select ID, accountname
                                  from ".DB_PREFIX."mail_account
                                 where von = ".(int)$user_ID." and smtp_hostname <>''
                              order by accountname") or db_die();
            while ($row = db_fetch_row($result)) {
                $options[] = array('value' => $row[0], 'text' => $row[1]);
            }
            $form_fields[] = array('type' => 'select', 'name' => 'account_ID', 'label' => __('Standard').__(':'), 'options' => $options);
        }
        // begin dropdown menu
        $options = array();
        
        // if not is set any sender, we will select the default sender
        if (!isset($formdata['sender_ID']) || ($formdata['sender_ID'] == '')) {
            $option_selected = 'default';
            $options[] = array('value' => 'default', 'text' => strip_tags($user_email), 'selected' => true);
        }
        else {
            $option_selected = $formdata['sender_ID'];
            $options[] = array('value' => 'default', 'text' => strip_tags($user_email));
        }
        
        
        
        $result = db_query("select ID, title
                              from ".DB_PREFIX."mail_sender
                             where von = ".(int)$user_ID) or db_die();
        while ($row = db_fetch_row($result)) {
            if ($option_selected == $row[0]) {
                $options[] = array('value' => (int)$row[0], 'text' => strip_tags($row[1]), 'selected' => true);
            }
            else {
                $options[] = array('value' => (int)$row[0], 'text' => strip_tags($row[1]));
            }
        }
        
        $form_fields[] = array('type' => 'select', 'name' => 'sender_ID', 'label' => __('Sender').__(':'), 'options' => $options, 'width' => '500px', 'label_class' => 'label_block', 'no_blank_option' => true);
    }
    // otherwise just display the normal string
    else {
        $form_fields[] = array('type' => 'string', 'text' => $user_email.'<br />' );
    }
    // end sender
}

// nothing similar for fax and sms :-(
// subject field not for sms
if ($form <> "sms") {
    $form_fields[] = array('type' => 'text', 'name' => 'subj', 'label' => __('Subject').__(':'), 'width' => '700px', 'label_class' => 'label_block', 'value' => html_out($formdata['subj']));
}
// body for all three modes :-)
$textarea_id = 'id=body';
//$js_html_textarea= array();
$js_html_textarea[] = 'body';
if ($html=='true' || $html == true) {
    $output .= "<script type=\"text/javascript\">window.onload = function() {\n";
    foreach ($js_html_textarea as $f) {
        $output .= "newFCKeditor('".$f."','".PATH_PRE."');\n";
    }
    $output .=  '}</script>';
}

$form_fields[] = array('type' => 'textarea', 'name' => 'body', 'label' => __('Text').__(':'), 'style' => 'width:700px;height:200px;', 'label_class' => 'label_block', 'value' => $formdata['body']);

// option for customized newsletter
// insert db-field
if ($form == "email") {
    $options = array();
    $options[] = array('value' => '', 'text' => '');
    // fetch all active fields
    // 1. include db_man and fetch fields
    require_once(PATH_PRE.'lib/dbman_lib.inc.php');
    $fields = build_array('contacts', 0);
    // loop over all active fields
    foreach ($fields as $field_name => $field_values)  {
        
        $field_values['form_name'] = enable_vars($field_values['form_name']);
        
        $options[] = array('value' => '|db-field:'.$field_name.'('.$field_values['form_name'].')|', 'text' => $field_values['form_name']);
    }
    $form_fields[] = array('type' => 'select', 'name' => 'placehold', 'label' => '&nbsp;', 'options' => $options, 'onchange' => 'insPlHold();', 'text_after' => __('insert db field (only for contacts)')."&nbsp;&nbsp;&nbsp;&nbsp;", 'label_class' => 'label_block', 'style' => 'float:left', 'value' => $formdata['placehold'], "no_break" => true);
    $form_fields[] = array('type' => 'string',  'text' => "<a href='#' onclick='remove_dbfields()'>".__('Remove all db fields from email')."</a><br />");
    
}

$html = get_buttons(array(array('type' => 'submit', 'name' => 'versenden', 'value' => __('Send'), 'active' => false)));
// back button to the mail client
if (PHPR_QUICKMAIL == 2) {
    $html .= get_buttons(array(array('type' => 'link', 'href' => 'mail.php?mode=view'.$sid.'&amp;csrftoken='.make_csrftoken(), 'text' => __('list view'), 'active' => false)));
}
$form_fields[] = array('type' => 'parsed_html', 'html' => $html);
$form_fields[] = array('type' => 'hidden', 'name' => 'csrftoken', 'value' => make_csrftoken());
$create_message_fields = get_form_content($form_fields);


/*******************************
*     attachment fields
*******************************/
$form_fields = array();
// Fax form
if ($form == "fax") {
    $form_fields[] = array('type' => 'hidden', 'name' => 'action', 'value' => 'fax');
    $form_fields[] = array('type' => 'text', 'name' => 'additional_fax', 'label' => __('Additional number').__(':'), 'value' => '', 'label_class' => 'label_block', 'value' => $formdata['additional_fax']);
}
// SMS form
elseif ($form == "sms") {
    $form_fields[] = array('type' => 'hidden', 'name' => 'action', 'value' => 'sms');
    $form_fields[] = array('type' => 'text', 'name' => 'smsnumber', 'label' => __('Additional number').__(':'), 'value' => '', 'label_class' => 'label_block');
}
else {
    // 1. attachment
    $form_fields[] = array('type' => 'file', 'name' => 'userfile[]', 'id' => 'userfile1', 'label' => __('Attachment').' 1'.__(':'), 'label_right' => '&nbsp;', 'label_class' => 'label_block', "no_break" => true);
    $form_fields[] = array('type' => 'parsed_html', 'html' => '<br />');
    // 2. attachment
    $form_fields[] = array('type' => 'file', 'name' => 'userfile[]', 'id' => 'userfile2', 'label' => __('Attachment').' 2'.__(':'), 'label_right' => '&nbsp;', "no_break" => true);
    $form_fields[] = array('type' => 'parsed_html', 'html' => '<br />');
    // 3. attachment
    $form_fields[] = array('type' => 'file', 'name' => 'userfile[]', 'label' => __('Attachment').' 3'.__(':'), 'id' => 'userfile3');
}
$attachment_fields = get_form_content($form_fields);

/*******************************
*     recipient fields
*******************************/
// right content
$form_fields = array();
if ($form == "email") {
    $form_fields[] = array('type' => 'hidden', 'name' => 'action', 'value' => 'email');
    // recipient
    $form_fields[] = array('type' => 'text', 'name' => 'additional_mail', 'label' => __('Additional address').__(':'), 'value' => str_replace("'","",str_replace('"','',$formdata['additional_mail'])), 'label_class' => 'label_block', 'width' => '500px');
    // cc
    $form_fields[] = array('type' => 'text', 'name' => 'cc', 'label' => __('CC').__(':'), 'value' => '', 'label_class' => 'label_block', 'width' => '500px', 'value' => $formdata['cc']);
    // bcc
    $form_fields[] = array('type' => 'text', 'name' => 'bcc', 'label' => __('BCC').__(':'), 'value' => '', 'label_class' => 'label_block', 'width' => '500px', 'value' => $formdata['bcc']);
    // carry a flag so the script can look for attachments
    if ($action2 == "forward") {
        $form_fields[] = array('type' => 'hidden', 'name' => 'forwarded_mail', 'value' => $ID);
    }
    if ($action2 == "reply" || $action2 == "replyall") {
        $form_fields[] = array('type' => 'hidden', 'name' => 'replied_mail', 'value' => $ID);
    }
    
    
    
}

$recipient_fields .= get_form_content($form_fields);

global $formdata;

// Members selection
if (!isset($formdata['mem'])) {
    $formdata['mem'] = array();

}
$recipient_fields .= '<label class="label_block">'.__('Group members').__(':')."</label>";
$recipient_fields .= selector_create_select_users('mem[]', $formdata['mem'], 'action_form_to_user_selector', '0', 'id="choose_members" class="small" style="width:300px;vertical-align:top;"');
$recipient_fields .= '<br /><br />';

// Contact Selection
$recipient_fields .= '<label class="label_block">'.__('External contacts').__(':')."</label>";
$recipient_fields .= selector_create_select_contacts('con[]', $formdata['con'], 'action_form_to_contact_selector', '0', 'class="small" style="width:300px;vertical-align:top;"', '7', '1');

// At last, the two buttons for notice and send single mails
$form_fields = array();

// notice of receipt

$recipient_fields .= '<br /><br />';

// Contact Selection
$form_fields[] = array('type' => 'checkbox', 'name' => 'receipt', 'label' => __('Notice of receipt'), 'label_class' => 'label_block', 'checked' => $formdata['receipt']);
// send single mails
$form_fields[] = array('type' => 'parsed_html', 'html' => '<br />');
$form_fields[] = array('type' => 'checkbox', 'name' => 'single', 'label' => __('Send single mails'), 'label_class' => 'label_block', 'checked' => $formdata['single']);
// end drop down menu personalized newsletter - only for mail
$form_fields[] = array('type' => 'hidden', 'name' => 'csrftoken', 'value' => make_csrftoken());

$recipient_fields .= get_form_content($form_fields);

$output .= '
<br />
<fieldset>
<legend>'.__('Create new message').'</legend>
'.$create_message_fields.'
</fieldset>

<fieldset>
<legend>'.__('Attachments').'</legend>
'.$attachment_fields.'
</fieldset>

<fieldset>
<legend>'.__('Recipients').'</legend>
'.$recipient_fields.'
</fieldset>
';

$output .= '</form>';
echo $output;

?>
