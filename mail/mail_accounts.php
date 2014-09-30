<?php

// mail_accounts.php - PHProjekt Version 5.2
// copyright  ©  2000-2005 Albrecht Guenther  ag@phprojekt.com
// www.phprojekt.com
// Author: Albrecht Guenther, $Author: polidor $
// $Id: mail_accounts.php,v 1.38.2.1 2007/01/15 18:34:36 polidor Exp $

// check whether the lib has been included - authentication!
if (!defined("lib_included")) die("Please use index.php!");

// check role
if (check_role('mail') < 1) {
    die('You are not allowed to do this!');
}

// check form token
check_csrftoken();

// convert checkbox flag
$collect ? $collect = 1 : $collect = 0;
// convert checkbox flag (delete mails from server during download)
$deletion ? $deletion = 1 : $deletion = 0;

if ($make <> '') {
    // insert new record
    if ($make == "neu") {
        $result = db_query("insert into ".DB_PREFIX."mail_account
                                        (von,       accountname,   hostname,   type,   username,   password,   mail_auth,   pop_hostname,   pop_account,   pop_password,   smtp_hostname,   smtp_account,   smtp_password, collect, deletion)
                                 values (".(int)$user_ID." ,'$accountname','$hostname','$type','$username','$password', ".(int)$cmail_auth." ,'$cpop_hostname','$cpop_account','$cpop_password','$csmtp_hostname','$csmtp_account','$csmtp_password',".(int)$collect.",".(int)$deletion.")") or db_die();
        if ($result) {
            // send message on success
            $output.= "$hostname ".__('has been created')."<hr />";
            $action = '';
        }
    }
    // update existing record
    else {
        if ($collect) $collect = 1;
        else $collect = 0;
        // convert checkbox flag (delete mails from server during download)
        $deletion ? $deletion = 1 : $deletion = 0;
        
        if ($mail_auth == '') {
            $mail_auth = 0;
        }

        $result = db_query("update ".DB_PREFIX."mail_account
                               set accountname = '$accountname',
                                   hostname = '$hostname',
                                   type = '$type',
                                   username = '$username',
                                   password = '$password',
                                   mail_auth =  ".(int)$cmail_auth.",
                                   pop_account = '$cpop_account',
                                   pop_password = '$cpop_password',
                                   pop_hostname = '$cpop_hostname',
                                   smtp_hostname = '$csmtp_hostname',
                                   smtp_account = '$csmtp_account',
                                   smtp_password = '$csmtp_password',
                                   collect = ".(int)$collect.",
                                   deletion = ".(int)$deletion." 
                             where ID = ".(int)$ID) or db_die();
        if ($result) {
            // send message on success
            $output.= "$hostname ".__('has been changed')."<hr />";
            $action = '';
        }
    }
    include_once('./mail_options.php');
} else if ($loeschen) {
    $result = db_query("delete from ".DB_PREFIX."mail_account
                              where ID = ".(int)$ID) or db_die();
    include_once('./mail_options.php');
}

// form
if (!$make) {
    //tabs
    $tabs = array();
    $buttons = array();
    
    // check form
    if (PHPR_MAIL_MODE) {
        $chkForm = "return chkEqualFields('frm','password','password_confirm','".__('The password and confirmation are different')."')
                           && chkEqualFields('frm','cpop_password','cpop_password_confirm','".__('The POP password and confirmation are different')."')
                           && chkEqualFields('frm','csmtp_password','csmtp_password_confirm','".__('The SMTP password and confirmation are different')."');";
    }
    
    $buttons[] = array('type' => 'form_start', 'name' => 'frm', 'hidden' => $hidden, 'enctype' => 'multipart/form-data',
                       'onsubmit' => $chkForm);
    $output = '<div id="global-header">'.get_tabs_area($tabs).'</div>';
    $output .= '<div id="global-content">';
    $output .= get_buttons($buttons);
    // button bar
    $buttons = array();
    $buttons[]=(array('type' => 'submit', 'name' => 'go', 'value' => __('OK'), 'active' => false));
    $buttons[]=(array('type' => 'link', 'href' => 'mail.php?mode=options&amp;csrftoken='.make_csrftoken(), 'text' => __('List View'), 'active' => false));
    $output .= get_buttons_area($buttons);

    if ($aendern <> '') {
        // fetch values
        $result = db_query("select ID, von, accountname, hostname, type, username, password,
                                   mail_auth, pop_hostname, pop_account, pop_password,
                                   smtp_hostname, smtp_account, smtp_password, collect, deletion
                              from ".DB_PREFIX."mail_account
                             where ID = ".(int)$ID) or db_die();
        $row = db_fetch_row($result);
        // check permission - the hacker doesn't deserve any message!
        if ($row[1] <> $user_ID) exit;
    }

    // title

    $form_fields[] = array('type' => 'text', 'name' => 'accountname', 'label' => __('mailaccount name'),'value' =>$row[2] );
    $form_fields[] = array('type' => 'text', 'name' => 'hostname', 'label' => __('host name'),'value' =>$row[3]);
    $options = array();
    $options[] = array('value' => '', 'text' => '');
    foreach ($port as $port1=>$port2) {
        $options[]= array('value' => $port1, 'text' => $port1, 'selected' => $row[4] == $port1);
        //  if ($port1 == $row[4])
    }
    $form_fields[] = array('type' => 'select', 'name' => 'type', 'label' => __('Type'), 'options' => $options,'value' =>$row[4]);

    $form_fields[] = array('type' => 'checkbox', 'name' => 'collect', 'label' => __('Include account for default receipt'),'value' =>$row[14], 'checked' => $row[14] == 1);
    $form_fields[] = array('type' => 'parsed_html', 'html' => '<br /><br /><br />');
    //delete mails from server during download new checkbox 23.09.05
    $form_fields[] = array('type' => 'checkbox', 'name' => 'deletion', 'label' => __('delete mails from server after retrieval'),'value' =>$row[15], 'checked' => $row[15] == 1);
    $form_fields[] = array('type' => 'parsed_html', 'html' => '<br /><br /><br />');
    $form_fields[] = array('type' => 'text', 'name' => 'username', 'label' => __('Username'),'value' =>$row[5]);
    $form_fields[] = array('type' => 'password_confirm', 'name' => 'password', 'label' => __('Password'),'value' =>$row[6]);
    $form_fields[] = array('type' => 'hidden', 'name' => 'csrftoken', 'value' => make_csrftoken());
    $create_message_fields = get_form_content($form_fields);
    // send configuration

    $output .= '
                <br />
                <fieldset>
                <legend>'.__('Accounts').'</legend>
                '.$create_message_fields.'
                </fieldset>';
    if (PHPR_MAIL_MODE) {
        $output.='<fieldset><legend>'.__('send mailss').'</legend>
                  <div class="boxContent">';
        $send_fields[] = array('type' => 'text',     'name' => 'csmtp_hostname', 'label' => __('the real address of the SMTP mail server, you have access to (maybe localhost)'),'value' =>$row[11]);
        $send_fields[] = array('type' => 'password_confirm', 'name' => 'csmtp_password', 'label' => __('password for this account'),'value' =>$row[13]);
        $mail_auth_values = array('0' => __('No Authentication'), '1' => __('with POP before SMTP'), '2' => __('SMTP auth (via socket only!)') );
        $options = array();
        $options[] = array('value' => '', 'text' => '');
        foreach ($mail_auth_values as $auth_mail_value => $auth_mail_text) {
            if ($row[7] == $auth_mail_value) {
                $options[]= array('value' => $auth_mail_value, 'text' => $auth_mail_text, 'selected' => true);
            }
            else {
                $options[]= array('value' => $auth_mail_value, 'text' => $auth_mail_text);
            }

        }
        $send_fields[] = array('type' => 'select', 'name' => 'cmail_auth', 'label' => __('Authentication'),'options'=>$options,'value' =>$row[7]);
        $form_fields[] = array('type' => 'hidden', 'name' => 'csrftoken', 'value' => make_csrftoken());
        $create_send_fields = get_form_content($send_fields);
        $output .= $create_send_fields;

        $output.='</fieldset><fieldset><legend>'.__('fill out in case of authentication via POP before SMTP').'</legend>
                  <div class="boxContent">';
        $pop_fields[] = array('type' => 'text', 'name' => 'cpop_hostname', 'label' => __('the POP server'),'value' =>$row[8]);
        $pop_fields[] = array('type' => 'text', 'name' => 'cpop_account', 'label' => __('real username for POP before SMTP'),'value' =>$row[9]);
        $pop_fields[] = array('type' => 'password_confirm', 'name' => 'cpop_password', 'label' => __('password for this pop account'),'value' =>$row[10]);
        $form_fields[] = array('type' => 'hidden', 'name' => 'csrftoken', 'value' => make_csrftoken());
        $create_pop_fields = get_form_content($pop_fields);
        $output .= $create_pop_fields;
        //fill out in case of SMTP authentication
        $output.='</fieldset><fieldset><legend>'.__('fill out in case of SMTP authentication').'</legend>
                  <div class="boxContent">';
        $smtp_fields[] = array('type' => 'text', 'name' => 'csmtp_account', 'label' => __('real username for SMTP auth'),'value' =>$row[9]);
        $smtp_fields[] = array('type' => 'password_confirm', 'name' => 'csmtp_password', 'label' => __('password for this account'),'value' =>$row[10]);
        $form_fields[] = array('type' => 'hidden', 'name' => 'csrftoken', 'value' => make_csrftoken());
        $create_smtp_fields = get_form_content($smtp_fields);
        $output .= $create_smtp_fields;
    }
    $output.= "<input type='hidden' name='ID' value='$ID' />\n";
    if (SID) $output.= "<input type='hidden' name='".session_name()."' value='".session_id()."' />\n";
    if ($aendern <> '') {
        $output.= "<input type='hidden' name='make' value='aendern' />\n";
    } else {
        $output.= "<input type='hidden' name='make' value='neu' />\n";
    }
    $output.= "<input type='hidden' name='mode' value='accounts' />\n";
    $output.=get_buttons(array(array('type' => 'submit', 'name' => 'go', 'value' => __('OK'), 'active' => false)));
    $output.=get_buttons(array(array('type' => 'link', 'href' => 'mail.php?mode=options&amp;csrftoken='.make_csrftoken(), 'text' => __('List View'), 'active' => false)))."
    </form>";


    echo '</fieldset>'.$output;
}

?>
