<?php

// mail_sender.php - PHProjekt Version 5.2
// copyright  ©  2000-2005 Albrecht Guenther  ag@phprojekt.com
// www.phprojekt.com
// Author: Albrecht Guenther, $Author: polidor $
// $Id: mail_sender.php,v 1.23.2.1 2007/05/27 00:41:07 polidor Exp $

// check whether the lib has been included - authentication!
if (!defined("lib_included")) { die("Please use index.php!"); }

// check role
if (check_role("mail") < 1) { die("You are not allowed to do this!"); }

// check form token
check_csrftoken();

// Check if it is a delete
if ($loeschen) {
    $result = db_query("delete from ".DB_PREFIX."mail_sender
                         where ID = ".(int)$ID) or db_die();
    if ($result) { $action = ""; }
    $query_str = "mail.php?mode=options&csrftoken=".make_csrftoken();
    header('Location: '.$query_str);
    die();
} 
elseif(!$make) {
    //tabs
    $tabs = array();

    // button bar
    if ($aendern){
        $result = db_query("select ID, von, title, sender, signature
                          from ".DB_PREFIX."mail_sender
                         where ID = ".(int)$ID) or db_die();
        $row = db_fetch_row($result);
        $hidden=array('mode'=>'sender','ID'=>$ID,'action'=>'sender','make'=>'aendern');
    }
    else  $hidden=array('mode'=>'sender','action'=>'sender','make'=>'neu');
    if(SID) $hidden=array_merge($hidden,array(session_name()=>session_id()));
    $buttons = array();
    $buttons[] = array('type' => 'form_start', 'name' => 'frm', 'hidden' => $hidden, 'onsubmit'=>"return chkForm('frm','title','".__('Please fill in the following field').": ".__('Name')."','sender','".__('Please fill in the following field')."')");


    $output = '<div id="global-header">'.get_tabs_area($tabs).'</div>';
    $output .= '<div id="global-content">';
    $output .= get_buttons($buttons);
    $buttons = array();
    $buttons[]=(array('type' => 'submit', 'name' => 'go', 'value' => __('OK'), 'active' => false));
    $buttons[]=(array('type' => 'link', 'href' => 'mail.php?mode=options&amp;csrftoken='.make_csrftoken(), 'text' => __('List View'), 'active' => false));
    $output .= get_buttons_area($buttons);

    // title
    // title of this record
    $form_fields[] = array('type' => 'text', 'name' => 'title', 'label' => __('Name'),'value' =>$row[2] );
    // sender name
    $form_fields[] = array('type' => 'text', 'name' => 'sender', 'label' => __('Sender'),'value' =>$row[3]);
    // signature
    $form_fields[] = array('type' => 'textarea', 'name' => 'signature', 'label' => __('Signature'),'value' =>$row[4]);

    $form_fields[] = array('type' => 'hidden', 'name' => 'csrftoken', 'value' => make_csrftoken());

    $create_message_fields = get_form_content($form_fields);
    $output .= '
            <br />
            <fieldset>
            <legend>'.__('Sender').' / '.__('Signature').'</legend>
            '.$create_message_fields.'
            </fieldset>';
                $output.=get_buttons(array(array('type' => 'submit', 'name' => 'go', 'value' => __('OK'), 'active' => false)));
                $output.=get_buttons(array(array('type' => 'link', 'href' => 'mail.php?mode=options&amp;csrftoken='.make_csrftoken(), 'text' => __('List View'), 'active' => false)))."
            </form>";
}
//insert record into db
elseif ($make) {
    if (!$error) {
        // insert new record
        if ($make == "neu") {
            $result = db_query("insert into ".DB_PREFIX."mail_sender
                                   (von,      title,   sender,   signature )
                            values (".(int)$user_ID.",'$title','$sender','$signature')") or db_die();
            if ($result) {} // send message on success
        }
        // update existing record
        else {
            $result = db_query("update ".DB_PREFIX."mail_sender
                               set title = '$title',
                                   sender = '$sender',
                                   signature = '$signature'
                             where ID = ".(int)$ID) or db_die();
            if ($result) {} // send message on success
        }
    }
    $query_str = "mail.php?mode=options&csrftoken=".make_csrftoken();
    header('Location: '.$query_str);
    die();
}

echo $output;

?>
