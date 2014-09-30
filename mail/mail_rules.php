<?php

// mail_rules.php - PHProjekt Version 5.2
// copyright  ©  2000-2005 Albrecht Guenther  ag@phprojekt.com
// www.phprojekt.com
// Author: Albrecht Guenther, $Author: gustavo $
// $Id: mail_rules.php,v 1.27 2006/11/07 00:28:30 gustavo Exp $

// check whether the lib has been included - authentication!
if (!defined("lib_included")) { die("Please use index.php!"); }

// check role
if (check_role("mail") < 1) { die("You are not allowed to do this!"); }

// check form token
check_csrftoken();

// fields for rules
$rules_fields = array("subject" => __('Title'), 
                      "body" => __('Body'), 
                      "sender" => __('Sender'),
                      "recipient" => __('Receiver'), 
                      "cc" => "Cc");
// action for rules
$rules_action  = array("copy" => __('Copy'), "move" => __('Move'), "delete" => __('Delete'));
//insert record into db
if ($make) {

    switch ($mode2) {
        case 'mail_to_contact':
            update_mail_to_contact($mail2con);
            include_once('./mail_options.php');
            break;
        case 'incoming':
            update_general_rule('incoming', $dir);
            break;
        case 'outgoing':
            update_general_rule('outgoing', $dir);
            break;
        default:
            // only assign to contact/project if the respective checkbox is checked
            if (!$parent_contact || $parent_contact == '') $contact = 0;
            if (!$parent_projekt || $parent_projekt == '') $projekt = 0;
            // insert new record
            if ($parent == '') {
                $parent = 0;
            }
            if ($dir == '') {
                $dir = 0;
            }
            
            if (!$ID) {
                $result = db_query("INSERT INTO ".DB_PREFIX."mail_rules
                                   (von,      title,   phrase,   type,  parent,       action ,   projekt,  contact)
                            VALUES (".(int)$user_ID.",'$title','$phrase','$type',".(int)$dir.", '$rules_action1', ".(int)$projekt.", ".(int)$contact.")") or db_die();
                if ($result) { $output.="$title ".__('has been created')."<hr />"; }
            }
            // update existing record
            else {
                
                
                
                $result = db_query("UPDATE ".DB_PREFIX."mail_rules
                               SET title='$title',
                                   phrase='$phrase',
                                   type='$type',
                                   parent= $dir ,
                                   action='$rules_action1',
                                   projekt= ".(int)$projekt.",
                                   contact= ".(int)$contact." 
                             WHERE ID = ".(int)$ID) or db_die();
                if ($result) { $output.="$title ".__('has been changed')."<hr />"; }
            }
            break;
    }
    include_once('./mail_options.php');
}
elseif ($loeschen) {
    $result = db_query("DELETE FROM ".DB_PREFIX."mail_rules
                        WHERE ID = ".(int)$ID) or db_die();
    include_once('./mail_options.php');
}


// *******
// form
else {

    // tabs
    $tabs    = array();
    $buttons = array();
    $hidden  = array('mode'=>'rules','ID'=>$ID,'action'=>'rules','make'=>'make');
    if (SID) $hidden = array_merge($hidden, array(session_name()=>session_id()));
    $buttons[] = array('type' => 'form_start', 'hidden' => $hidden);
    $output = get_buttons($buttons);
    $output .= '<div id="global-header">'.get_tabs_area($tabs).'</div>';
    $output .= '<div id="global-content">';

    // button bar
    $buttons = array();
    $buttons[]=(array('type' => 'submit', 'name' => 'go', 'value' => __('OK'), 'active' => false));
    $buttons[]=(array('type' => 'link', 'href' => 'mail.php?mode=options&amp;csrftoken='.make_csrftoken(), 'text' => __('List View'), 'active' => false));

    $output .= get_buttons_area($buttons);

    // fetch values
    if ($ID > 0) {
        $result = db_query("select ID, von, title, phrase, type, is_not, parent, action, projekt, contact
                          from ".DB_PREFIX."mail_rules
                         where ID = ".(int)$ID) or db_die();
        $row = db_fetch_row($result);
        if ($row[1] <> $user_ID) { die("you are not allowed to do this!"); }
    }


    $form_fields[] = array('type' => 'text', 'name' => 'title', 'label' => __('name of the rule'),'value' =>$row[2] );
    $form_fields[] = array('type' => 'text', 'name' => 'phrase', 'label' => __('part of the word'),'value' =>$row[3]);
    $options = array();
    //$options[] = array('value' => '', 'text' => '');
    foreach ($rules_fields as $type1 => $type2) {
        
        if ($row[4] == $type1) {
            $options[]= array('value' => $type1, 'text' => $type2, 'selected' => true);
        }
        else {
            $options[]= array('value' => $type1, 'text' => $type2);
        }
        
    }
    $form_fields[] = array('type' => 'select', 'name' => 'type', 'label' => __('is in field'), 'options' => $options);
    $form_fields[] = array('type' => 'hidden', 'name' => 'csrftoken', 'value' => make_csrftoken());
    $create_message_fields = get_form_content($form_fields);

    //Duplicate messages
    $create_duplicate_fields='<br style="clear: both;" />';

    // delete, copy or move into a directory
    $create_duplicate_fields.="
 <label for='parent' class='formbody'>".__('Mail')."</label><input type='checkbox' id='parent' name='parent'";
    if ($row[7] <> '') $create_duplicate_fields.=' checked';
    $create_duplicate_fields.=" /> &nbsp;&nbsp;<select name='rules_action1' >\n";
    foreach ($rules_action as $action1 => $action2) {
        $create_duplicate_fields.="<option value='$action1'";
        if ($action1 == $row[7]) {  $create_duplicate_fields.=" selected"; }
        $create_duplicate_fields.=">$action2</option>\n";
    }
    $create_duplicate_fields.="</select>\n";
    // select directory
    $create_duplicate_fields.="&nbsp;&nbsp;<label for='dir'>".__('in')." ".__('Directory')."</label>\n";
    $create_duplicate_fields.="<select name='dir' id='dir'><option value=''></option>\n";
    $result2 = db_query("select ID, subject
                         from ".DB_PREFIX."mail_client
                        where von = ".(int)$user_ID." and
                              typ like 'd'") or db_die();
    while ($row2 = db_fetch_row($result2)) {
        $create_duplicate_fields.="<option value='$row2[0]'";
        if ($row2[0] == $row[6]) { $create_duplicate_fields.=" selected"; }
        $create_duplicate_fields.=">$row2[1]</option>\n";
    }
    $create_duplicate_fields.="</select><br style='clear:both;' />";

    // assign to a project
    $create_duplicate_fields.="<label for='parent_projekt' class='formbody'>".__('Assign to project').":</label>
  <input type='checkbox' id='parent_projekt' name='parent_projekt'";
    if ($row[8] > 0) $create_duplicate_fields.=' checked';
    $create_duplicate_fields.=" /> &nbsp;&nbsp;<select name='projekt' id='projekt'><option value='0'></option>\n";
    //prepare values for function
    $where = "where $sql_user_group";
    // call function to show all required elemts in a tree structure in the select box
    $create_duplicate_fields.=show_elements_of_tree("projekte","name",$where,"acc"," order by name",$row[8],'parent',0);
    $create_duplicate_fields.="</select><br style='clear:both;' />";

    // assign to a contact
    $create_duplicate_fields.="<label for='parent_contact' class='formbody'>".__('Assign to contact').":</label>
  <input type='checkbox' name='parent_contact' id='parent_contact'";
    if ($row[9] > 0) $create_duplicate_fields.=' checked';
    $create_duplicate_fields.=" /> &nbsp;&nbsp; <select name='contact' id='contact'><option value='0'></option>\n";
    $create_duplicate_fields.=show_elements_of_tree("contacts",
    "nachname,vorname,firma",
    "where (acc_read like 'system' or ((von = $user_ID or acc_read like 'group' or acc_read like '%\"$user_kurz\"%') and $sql_user_group))",
    "acc"," order by nachname",$row[9],"parent",0);
    $create_duplicate_fields.="</select><br style='clear:both;' />";

    $output .= '
<br />
<fieldset>
<legend>'.__('Rules').'</legend>
'.$create_message_fields.'
</fieldset>

<fieldset>
<legend>'.__('Action for duplicates').'</legend>
'.$create_duplicate_fields.'
</fieldset>';

// hidden values and go button

$output.=get_buttons(array(array('type' => 'submit', 'name' => 'go', 'value' => __('OK'), 'active' => false)));
$output.=get_buttons(array(array('type' => 'link', 'href' => 'mail.php?mode=options&amp;csrftoken='.make_csrftoken(), 'text' => __('List View'), 'active' => false)))."</div></div></form>";


echo '</div>'.$output;
}




?>
