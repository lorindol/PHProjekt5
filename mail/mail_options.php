<?php

// mail_options.php - PHProjekt Version 5.2
// copyright  ©  2000-2005 Albrecht Guenther  ag@phprojekt.com
// www.phprojekt.com
// Author: Albrecht Guenther, $Author: albrecht $
// $Id: mail_options.php,v 1.29.2.1 2007/02/20 11:16:46 albrecht Exp $

// check whether the lib has been included - authentication!
if (!defined("lib_included")) { die("Please use index.php!"); }

// check role
if (check_role("mail") < 1) { die("You are not allowed to do this!"); }

// check form token
check_csrftoken();

//tabs
$tabs = array();


$output = '<div id="global-header">'.get_tabs_area($tabs).'</div>';
$output .= breadcrumb($module, array(array('title'=>__('Options'))));

$output .= '<div id="global-content">';

// button bar
$buttons = array();
$buttons[] = array('type' => 'link', 'href' => $_SERVER['SCRIPT_NAME'].'?mode=send_form&amp;form=email'.$sid.'&amp;csrftoken='.make_csrftoken(), 'text' => __('Write'), 'active' => false);
$buttons[] = array('type' => 'link', 'href' => $_SERVER['SCRIPT_NAME'].'?mode=view&amp;action=fetch_new_mail&amp;view_only=1'.$sid.'&amp;csrftoken='.make_csrftoken(), 'text' => __('view mail list'), 'active' => false);
$buttons[] = array('type' => 'link', 'href' => $_SERVER['SCRIPT_NAME'].'?mode=view&amp;action=fetch_new_mail'.$sid.'&amp;csrftoken='.make_csrftoken(), 'text' => __('Receive'), 'active' => false);
$buttons[] = array('type' => 'link', 'href' => $_SERVER['SCRIPT_NAME'].'?mode=forms&amp;form=d'.$sid.'&amp;csrftoken='.make_csrftoken(), 'text' => __('Directory').' '.__('Create'), 'active' => false);
$buttons[] = array('type' => 'link', 'href' => $_SERVER['SCRIPT_NAME'].'?mode=options'.$sid.'&amp;csrftoken='.make_csrftoken(), 'text' => __('Options'), 'active' => false);
$output .= get_buttons_area($buttons);

$output .= '<a name="content"></a>';

// mail accounts

$disabl = "disabled='disabled'";
// copy project branches
$output.= "<br /><fieldset>
<legend>".__('Accounts')."</legend>";
$output.= "<form action='mail.php' method='post' >\n";
$output.= "<input type='hidden' name='mode' value='accounts'/>\n";
$output.= "<input type='hidden' name='csrftoken' value='".make_csrftoken()."'/>\n";
$output.= "<input type='hidden' name='action' value='accounts' />\n";
$output.= "<input type='submit' class='button' name='neu' value='".__('Create')."' /> &nbsp;&nbsp;".__('or')." \n";
$output.= "<select name='ID' ><option value=''></option>";
$result = db_query("select ID,von,accountname,hostname,type,username,password,deletion
                        from ".DB_PREFIX."mail_account
                       where von = ".(int)$user_ID) or db_die();
while ($row = db_fetch_row($result)) {$output.= "<option value='".(int)$row[0]."'>".strip_tags($row[2])."</option>\n";$disabl ="";}
$output.= "</select>&nbsp;&nbsp;";
if(SID)$output.= "<input type='hidden' name='".session_name()."' value='".session_id()."' />\n";
$output.= "<input type='submit' name='aendern' class='button' value='".__('Modify')."' $disabl />&nbsp;&nbsp;";
$output.= "<input type='submit' name='loeschen' class='button' value='".__('Delete')."' $disabl onclick=\"return confirm('".__('Are you sure?')."')\" />&nbsp;&nbsp;\n";
$output.= "</form></fieldset>\n";

// query one single account
$disabl = "disabled='disabled'";
$output.= "<fieldset>
<legend>".__('Accounts').": ".__('Single account query')."</legend>";
$output.= "<form action='mail.php' method='post'>\n";
$output.= "<input type='hidden' name='mode' value='view' />\n";
$output.= "<input type='hidden' name='action' value='fetch_new_mail' />\n";
$output.= "<input type='hidden' name='csrftoken' value='".make_csrftoken()."'/>\n";
$output.= "<select name='account_ID'><option value=''></option>";
$result = db_query("select ID,von,accountname,hostname,type,username,password,deletion
                        from ".DB_PREFIX."mail_account
                       where von = ".(int)$user_ID) or db_die();
while ($row = db_fetch_row($result)) {$output.= "<option value='".(int)$row[0]."'>".strip_tags($row[2])."</option>\n"; $disabl ="";}
$output.= "</select>&nbsp;&nbsp;";
if(SID)$output.= "<input type='hidden' name='".session_name()."' value='".session_id()."' />\n";
$output.= "<input class='button' type='submit' value='".__('go')."' $disabl />\n";
$output.= "</form></fieldset>\n";



if (check_role("mail") > 1) {
    // senders and signatures
    $disabl = "disabled='disabled'";
    $output.= "<fieldset><legend>".__('Sender')." / ".__('Signature')."</legend>";
    $output.= "<form action='mail.php' method='post'>\n";
    $output.= "<input type='hidden' name='mode' value='sender' />\n";
    $output.= "<input type='hidden' name='action' value='sender' />\n";
    $output.= "<input type='hidden' name='csrftoken' value='".make_csrftoken()."'/>\n";
    $output.= "<input class='button' type='submit' name='neu' value='".__('Create')."' />&nbsp;&nbsp; ".__('or')." \n";
    $output.= "<select name='ID'><option value=''></option>";
    $result = db_query("select ID,von,title,sender,signature
                          from ".DB_PREFIX."mail_sender
                         where von = ".(int)$user_ID) or db_die();
    while ($row = db_fetch_row($result)) {$output.= "<option value='".(int)$row[0]."'>".strip_tags($row[2])."</option>\n"; $disabl ="";}
    $output.= "</select>&nbsp;&nbsp;";
    if(SID)$output.= "<input type='hidden' name='".session_name()."' value='".session_id()."' />\n";
    $output.= "<input type='submit' class='button' name='aendern' value='".__('Modify')."' $disabl />&nbsp;&nbsp;";
    $output.= "<input type='submit' class='button'  name='loeschen' value='".__('Delete')."' $disabl onclick=\"return confirm('".__('Are you sure?')."')\" />&nbsp;&nbsp;\n";
    $output.= "</form></fieldset>\n";
}

// rules
$disabl = "disabled='disabled'";
$output.= "<fieldset><legend>".__('Rules')."</legend>";
$output.= "<form action='mail.php' method='post'>";
$output.= "<input type='hidden' name='mode' value='rules' />\n";
$output.= "<input type='hidden' name='action' value='rules' />\n";
$output.= "<input type='hidden' name='csrftoken' value='".make_csrftoken()."'/>\n";
$output.= "<input type='submit' class='button' name='neu' value='".__('Create')."' />&nbsp;&nbsp; ".__('or')." \n";
$output.= "<select name='ID'><option value=''></option>";
$result = db_query("SELECT ID,von,title,phrase,type,is_not,parent,action
                        FROM ".DB_PREFIX."mail_rules
                        WHERE von = ".(int)$user_ID."  
                            AND type <> 'incoming' 
                            AND type <> 'outgoing' 
                            AND type <> 'mail2con'") or db_die();
while ($row = db_fetch_row($result)) {$output.= "<option value='".(int)$row[0]."'>".strip_tags($row[2])."</option>\n"; $disabl ="";}
$output.= "</select>&nbsp;&nbsp;";
if(SID)$output.= "<input type='hidden' name='".session_name()."' value='".session_id()."' />\n";
$output.= "<input type='submit' class='button' name='aendern' value='".__('Modify')."' $disabl />&nbsp;&nbsp;";
$output.= "<input type='submit' class='button' name='loeschen' value='".__('Delete')."' $disabl onclick=\"return confirm('".__('Are you sure?')."')\" />&nbsp;&nbsp;\n";
$output.= "</form></fieldset>\n";

// default dir for incoming mails
$disabl = "disabled='disabled'";
// first fetch the current parent
$result = db_query("select parent
                        from ".DB_PREFIX."mail_rules
                       where von = ".(int)$user_ID." and
                             type like 'incoming'") or db_die();
$row = db_fetch_row($result);
$output.= "<fieldset><legend>".__('imcoming Mails')."</legend>";
$output.= "<form action='mail.php' method='post'>";
$output.= "<input type='hidden' name='mode' value='rules' />\n";
if(SID)$output.= "<input type='hidden' name='".session_name()."' value='".session_id()."' />\n";
$output.= "<input type='hidden' name='action' value='rules' />\n";
$output.= "<input type='hidden' name='mode2' value='incoming' />\n";
$output.= "<input type='hidden' name='csrftoken' value='".make_csrftoken()."'/>\n";
// text 'all incoming mail'
$output.= __('All')." ".__('imcoming Mails')." ".__('in').":\n";
$output.= "<select name='dir'><option value=''></option>";
$result2 = db_query("select ID, subject
                           from ".DB_PREFIX."mail_client
                          where von = ".(int)$user_ID." and
                                typ like 'd'") or db_die();
while ($row2 = db_fetch_row($result2)) {
    $output.= "<option value='".(int)$row2[0]."'";
    $disabl ="";
    if ($row2[0] == $row[0]) {$output.= " selected"; }
    $output.= ">".strip_tags($row2[1])."</option>\n";
}
$output.= "</select>";
$output.= " <input type='submit' class='button' name='make' value='".__('Modify')."' $disabl />&nbsp;";
$output.= "</form></fieldset>\n";

// default dir for outgoing mails
$disabl = "disabled='disabled'";
// first fetch the current parent
$result = db_query("select parent
                        from ".DB_PREFIX."mail_rules
                       where von = ".(int)$user_ID." and
                             type like 'outgoing'") or db_die();
$row = db_fetch_row($result);
$output.= "<fieldset><legend>".__('sent Mails')."</legend>";
$output.= "<form action='mail.php' method='post'>";
$output.= "<input type='hidden' name='mode' value='rules' />\n";
if(SID)$output.= "<input type='hidden' name='".session_name()."' value='".session_id()."' />\n";
$output.= "<input type='hidden' name='action' value='rules' />\n";
$output.= "<input type='hidden' name='mode2' value='outgoing' />\n";
$output.= "<input type='hidden' name='csrftoken' value='".make_csrftoken()."'/>\n";
// text 'all outgoing mail'
$output.= __('All')." ".__('sent Mails')." ".__('in').":\n";
$output.= "<select name='dir'> <option value=''></option>";
$result2 = db_query("select ID, subject
                           from ".DB_PREFIX."mail_client
                          where von = ".(int)$user_ID." and
                                typ like 'd'") or db_die();
while ($row2 = db_fetch_row($result2)) {
    $output.= "<option value='".(int)$row2[0]."'";
    $disabl ="";
    if ($row2[0] == $row[0]) {$output.= " selected"; }
    $output.= ">".strip_tags($row2[1])."</option>\n";
}
$output.= "</select>";
$output.= " <input type='submit' class='button' name='make' value='".__('Modify')."' $disabl />&nbsp;";
$output.= "</form></fieldset>\n";

// checkbox: search for email in contact list and assign mail to this contact automatically
$output.= "<fieldset><legend>".__('Assign to contact according to address')."</legend>";
$output.= "<form action='mail.php' method='post'>";
$output.= "<input type='hidden' name='mode' value='rules' />\n";
if(SID)$output.= "<input type='hidden' name='".session_name()."' value='".session_id()."' />\n";
$output.= "<input type='hidden' name='action' value='rules' />\n";
$output.= "<input type='hidden' name='mode2' value='mail_to_contact' />\n";
$output.= "<input type='hidden' name='csrftoken' value='".make_csrftoken()."'/>\n";
$result = db_query("select ID
                        from ".DB_PREFIX."mail_rules
                       where von = ".(int)$user_ID." and
                             type like 'mail2con'") or db_die();
$row = db_fetch_row($result);
$output.= "<input type='checkbox' name='mail2con'";
if ($row[0] > 0) {$output.= ' checked'; }
$output.= " /> ".__('Assign to contact according to address')." <input type='submit' class='button' name='make' value='".__('go')."' /></form></fieldset>\n";

echo '</div>'.$output;
?>
