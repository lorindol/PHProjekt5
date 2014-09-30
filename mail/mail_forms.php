<?php

// mail_forms.php - PHProjekt Version 5.2
// copyright  ©  2000-2005 Albrecht Guenther  ag@phprojekt.com
// www.phprojekt.com
// Author: Albrecht Guenther, $Author: nina $
// $Id: mail_forms.php,v 1.59.2.2 2007/05/14 08:41:50 nina Exp $

// check whether the lib has been included - authentication!
if (!defined("lib_included")) die("Please use index.php!");

// check role
if (check_role("mail") < 1) die("You are not allowed to do this!");

include_once(LIB_PATH.'/access_form.inc.php');

// selector-tranformation stuff
require_once(LIB_PATH.'/selector/selector.inc.php');

// Creating a token to be used later
$csrftoken = make_csrftoken();

// fetch original data
if ($ID) {
    $result = db_query("select ID, von, subject, body, sender, recipient, cc, kat, remark, date_received,
                               touched, date_sent, body_html, parent, contact, projekt, typ, acc, acc_write, gruppe, header 
                          from ".DB_PREFIX."mail_client
                         where ID = ".(int)$ID." 
                         AND (von = ".(int)$user_ID." OR (acc LIKE 'system' OR
                              ((von = ".(int)$user_ID." 
                                OR acc LIKE 'group'
                                OR acc LIKE '%\"$user_kurz\"%')
                               ".group_string().")))") or db_die();
    $row = db_fetch_row($result);
    
    $mail_header = $row[20];

    // check permission
    if ($row[0] == 0) { die("You are not privileged to do this!"); }
    if ($row[1] <> $user_ID) {
        $read_o = 1;
        //die("You are not allowed to do this!");
    }
    else {
        $read_o = 0;
    }

    $form = $row[16];
    $parent= (int)$row[13];
    change_group($row[19]); 

}
//unset ID when copying project
if ($form == "d") $ID=prepare_ID_for_copy($ID,$copy);
$disabled_code = read_o($read_o);
$fields_temp = $fields;
foreach($fields_temp as $field_name => $field_array) {
    if (isset($formdata[$field_name])) $fields[$field_name]['value'] = xss($formdata[$field_name]);
    else if (isset($_POST[$field_name])) $fields[$field_name]['value'] = xss($_POST[$field_name]);
}
// form for mails
if ($form <> "d") {
    // mark mail as red.
    if(!$row[10]) {
        $result = db_query("update ".DB_PREFIX."mail_client
                             set touched = 1
                          where ID = ".(int)$ID) or db_die();
    }
    $head=__('Mail');
    // format 'sender' string from db
    if (ereg("&lt;",$row[4])) {$sender=explode("&lt;",$row[4]);$sender1=substr($sender[1],0,-4);$sender2=$sender[0];}
    elseif (ereg("<",$row[4])) {$sender=explode("<",$row[4]);$sender1=substr($sender[1],0,-1);$sender2=$sender[0];}
    else { $sender2 = $row[4]; }

    // create receiver string
    // if there are several receivers, split them
    $receiver_string = "";
    if (ereg(", ",$row[5])) { $receivers = explode(", ",$row[5]); }
    else { $receivers[0] = $row[5]; }
    for ($i=0; $i < count($receivers); $i++) {
        $rec = explode("<",$receivers[$i]);
        // try to find the email adress
        // 1. option: test<test@test.de> -> split was successful
        if ($rec[1] <> "") { $receiver1 = html_out(substr($rec[1],0,-1)); }
        // 2. option text@test.de -> take the email adress as it is
        else { $receiver1 = html_out($rec[0]); }
        $receiver2 = html_out($rec[0]);
        if ($i > 0) { $receiver_string .= "; "; }
        $receiver_string .= "<a class='pa'href='mail.php?mode=send_form&amp;form=email&amp;recipient=".strip_tags($receiver1)."$sid'>".strip_tags($receiver2)."</a>";
    }
    // echo sender and receiver string
    $outmail='<br style="clear:left" /><br />';
    $box_right_data = array();
    $box_right_data['type']         = 'anker';
    $box_right_data['anker_target'] = 'oben';
    $box_right_data['link_text']    = __('Basis data');
    $outmail.="<div class='boxHeaderSmallLeft'>".__('Message from:') ." <a class='mail_pa' href='mail.php?mode=send_form&amp;form=email&amp;csrftoken=$csrftoken&amp;recipient=$sender1$sid'>$sender2</a></div>";
    $outmail.='<div class="boxHeaderSmallRight">'.__('Recipients').'</div><div class="boxContentSmallLeft">';

    $outmail.="<br />
    <span class='formbody'>".__('Title').":</span> ".strip_tags($row[2])."<br class='clear' /><br />\n";
    // body: add linebreaks
    $body = $row[3];
    
    // check if the mail is an UTF-8 mail encoded
    if (strpos($mail_header,'charset=UTF-8') > 0) {
        $body = utf8_decode($body);
    }
    
    
    // try to avoid scripting which can be poisoned
    $body = html_out($body);
    // convert web and mail links to clickable links ... but only for a text mail!
    // if (!eregi("<href=",$body) and !eregi("mailto:",$body)) {
    $body = @eregi_replace("(((f|ht){1}tp://)[a-zA-Z0-9@:%_.~#-\?&]+[a-zA-Z0-9@:%_~#\?&])", "<a href=\"\\1\" target=\"_blank\">\\1</a>", $body); //http
    $body = @eregi_replace("([[:space:]()[{}])(www.[a-zA-Z0-9@:%_.~#-\?&]+[a-zA-Z0-9@:%_~#\?&])", "\\1<a href=\"http://\\2\" target=\"_blank\">\\2</a>", $body); // www.
    $body = @eregi_replace("([_\.0-9a-z-]+@([0-9a-z][0-9a-z-]+\.)+[a-z]{2,3})","<a href=\"mailto:\\1\">\\1</a>", $body); // @
    //}
    $body = nl2br($body);

    $csrftoken = make_csrftoken();

    // show link to html output and display body in plain text
    $outmail.="$body<br class='clear' />\n";
    if (!empty($row[12])) {
        $outmail .= "<a href='./mail.php?mode=data&amp;action=showhtml&amp;ID=$ID&csrftoken=$csrftoken'>HTML Body</a><br />\n";
    }
    $outmail.="<br />";
    // reply, reply all and forward

    $outmail.="<input type='button' onclick=\"self.location.href='mail.php?mode=send_form&amp;form=email&amp;action2=reply&amp;ID=$row[0]$sid&amp;csrftoken=$csrftoken'\" value='".__('Reply')."' name='".__('Reply')."' class='button' />&nbsp;\n";
    $outmail.="<input type='button' onclick=\"self.location.href='mail.php?mode=send_form&amp;form=email&amp;action2=replyall&amp;ID=$row[0]$sid&amp;csrftoken=$csrftoken'\" value='".__('Reply to all')."' name='".__('reply all')."' class='button' />&nbsp;\n";
    $outmail.="<input type='button' onclick=\"self.location.href='mail.php?mode=send_form&amp;form=email&amp;action2=forward&amp;ID=$row[0]$sid&amp;csrftoken=$csrftoken'\" value='".__('Forward')."' name='".__('Forward')."' class='button' />&nbsp;\n";
    $outmail.="<br style='clear: both;' /></div>";
    // links zu rechts auf
    $outmail.='<div class="boxContentSmallRight">';

    $outmail.= "<br style='clear:both;' /><span class='formbody'>".__('to').": &nbsp;</span> $receiver_string";
    // cc string
    if ($row[6]) {
        $cc_string = "";
        if (ereg(", ",$row[6])) { $ccs = explode(", ",$row[6]); }
        else { $ccs[0] = $row[6]; }
        for ($i=0; $i < count($ccs); $i++) {
            $ccstrings = explode("<",$ccs[$i]);
            $cc1 = html_out(substr($ccstrings[1],0,-1));
            $cc2 = html_out($ccstrings[0]);
            if ($i > 0) { $cc_string .= "; "; }
            $cc_string .= "<a href='mail.php?mode=send_form&amp;form=email&amp;recipient=$cc1$sid'>$cc2</a>";
        }
    }
    $outmail.="<br class='clear' /><span class='formbody'>CC: &nbsp;</span>$cc_string
    <br class='clear' />";
    $outmail.="<hr class='mail' />";
    $date_received = $date_format_object->convert_dbdatetime2user($row[9]);
    $date_sent     = $date_format_object->convert_dbdatetime2user($row[11]);

    $outmail.="<span class='formbody'>".__('Send date').":</span> $date_sent<br class='clear' />
    <span class='formbody'>".__('Received').":</span> $date_received <br class='clear' />
    <hr class='mail' />\n";
    $outmail.="<span class='formbody'>".__('Attachment').":</span><br class='clear' />";

    $result3 = db_query("select ID, parent, filename, tempname, filesize
                           from ".DB_PREFIX."mail_attach
                          where parent = ".(int)$ID) or db_die();
    while ($row3 = db_fetch_row($result3)) {
        // determine filesize
        if ($row3[4] > 1000000)  {$fsize = floor($row3[4]/1000000)." M";}
        elseif ($row3[4] > 1000) {$fsize = floor($row3[4]/1000)." k";}
        else {$fsize = $row3[4];}
        // write data to the array for downloading
        $rnd = rnd_string();
        $file_ID[$rnd] = "$row3[2]|$row3[3]|$row3[0]";
        $outmail.="<a href='mail_down.php?rnd=".$rnd.$sid."' target=_blank>$row3[2] ($fsize)</a><br />\n";
    }
    $outmail.= "<br /><br />";
    //$outmail.= "<br /><br /></div>";

    $_SESSION['file_ID'] =& $file_ID;
    $outdir ='
    <br style="clear: both;" />
    <div class="inner_content"><a name="content"></a>';
    $outdir.=get_box_header(__('Basis data'), 'oben').'<div class="boxContent">';
    $outmail1.='<br style="clear: both;" />
    <fieldset>
    <legend>'.__('Basis data').'</legend>';

}

// form for directories
elseif ($form == "d") {
    if (isset($formdata['dirname'])) $dirname = $formdata['dirname'];
    else                             $dirname = $row[2];

    $head=__('Directory');
    $outdir ='
    <br style="clear: both;" />
    <a name="content"></a>
    <fieldset>
    ';
    $outdir.=get_box_header(__('Basis data'), 'oben').'<div class="boxContent">';
    $outdir.="<div class='formBodyRow'><label class='label_block' for='dirname'>".__('Name').":</label><input type='text' name='dirname' id='dirname' value='".strip_tags($dirname)."' ".$disabled_code." /><br style='clear:both;' /></div>";
}
//tabs
$tabs = array();
if ($ID){
    $hidden=array('mode'=>'data','ID'=>$ID,'aendern'=>'anlegen','make'=>'aendern','action'=>$action,'typ'=>$form,'form'=>$form);
}
else{
    $hidden=array('mode'=>'data','anlegen'=>'neu_anlegen','action'=>$action,'typ'=>$form,'form'=>$form);
}
$hidden = array_merge($hidden, $view_param);
$buttons = array();
if ($form == "d") {
    $buttons[] = array('type' => 'form_start', 'enctype'=>'multipart/form-data','hidden' => $hidden, 'name' => 'frm', 'onsubmit'=>"return chkForm('frm','dirname','".__('Please specify a description!')."')");
}
else {
    $buttons[] = array('type' => 'form_start', 'enctype'=>'multipart/form-data','hidden' => $hidden, 'name' => 'frm');
}
$output = '<div id="global-header">';
$output .= get_tabs_area($tabs);
$output .= breadcrumb($module, array(array('title'=>__('Create directory'))));
$output .= '</div>';
$output .= get_buttons($buttons);

// button bar
$buttons = array();

$deleteable = false;

$result2 = db_query("select ID
     from ".DB_PREFIX."projekte
    where parent = ".(int)$ID) or db_die();
$row2 = db_fetch_row($result2);

if ($row2[0] == '') {
    $deleteable = true;
}

$buttons = get_default_buttons($read_o,$ID,$justform,'mail',$deleteable,$sid,true);

if ($form <> 'd') {
    $buttons[]=(array('type' => 'link', 'href' => "mail.php?mode=send_form&amp;form=email&amp;action2=reply&amp;ID=$row[0]$sid&amp;csrftoken=$csrftoken", 'text' => __('Reply'), 'active' => false));
    $buttons[]=(array('type' => 'link', 'href' => "mail.php?mode=send_form&amp;form=email&amp;action2=replyall&amp;ID=$row[0]$sid&amp;csrftoken=$csrftoken", 'text' => __('Reply to all'), 'active' => false));
    $buttons[]=(array('type' => 'link', 'href' => "mail.php?mode=send_form&amp;form=email&amp;action2=forward&amp;ID=$row[0]$sid&amp;csrftoken=$csrftoken", 'text' => __('Forward'), 'active' => false));
}
$output .= '<div id="global-content">';
$output .= get_buttons_area($buttons);

if($outmail<>'') $output.=$outmail.'</div>';
// general part over


//form data
if($form == "d")$output.= $outdir;
else $output.=$outmail1;

// now the module_manager output
// continue with rest of the form which applies to all three types of records




$output.="<div class='formBodyRow'><label class='label_block' for='parent'>".__('Directory').":</label>";
$output.= "<select name='parent' id='parent' $disabled_code><option value='0'></option>\n";
$output.=show_elements_of_tree("mail_client",
"subject",
"where typ like 'd' and  von = ".(int)$user_ID." ",
'von',"",strip_tags($parent),"parent",(int)$ID);
$output.= "</select><br style='clear:both;' /></div>\n";


$output.=build_form($fields);
if ($ID) {
    if (!$read_o) {
        $output.=get_buttons(array(array('type' => 'submit', 'name' => 'modify_b', 'value' => __('OK'),  'active' => false)));

        $output.=get_buttons(array(array('type' => 'submit', 'name' => 'modify_c', 'value' => __('Apply'),  'active' => false)));

        if ($row2[0]== '')$output.=get_buttons(array(array('type'=>'submit', 'name'=>'delete_b','value'=>__('Delete'), 'onclick' => "return confirm('".__('Are you sure?'))));
    }
}
else {
    $output.=get_buttons(array(array('type' => 'submit', 'name' => 'create_b', 'value' => __('Create'),'active' => false)));
}

$output.=get_buttons(array(array('type' => 'link', 'href' => 'mail.php?mode=view', 'text' => __('List View'), 'active' => false)));
$output .= '</fieldset>';

/*
$output.='<div class="inner_content">';
if($outmail<>'') $output.=$outmail.'</div></div>';
else $output.='</div>';
// general part over
*/


/* Sharing section */
include_once("../lib/access_form.inc.php");
$form_fields = array();


// values of the access
if (!isset($persons)) {
    if (!isset($_POST[$persons])) $str_persons = $row[17];
    else $str_persons = xss($_POST[$persons]);
} else $str_persons = $acc = serialize($persons);


// set default mode, normally it depends on PHPR_ACC_DEFAULT, nut the default for emails is always private
if (!isset($str_persons) || $str_persons == '') {
    $str_persons = 'private';
}

if (!$acc_write) {
    if (!isset($_POST['acc_write'])) $acc_write = $row[18];
    else $acc_write = xss($_POST['acc_write']);
}

$form_fields[] = array('type' => 'parsed_html', 'html' => access_form2($str_persons, 1, $acc_write, 0, 1,'acc',$read_o)); // acc_read, exclude the user itself, acc_write, no parent possible, write access=yes
$form_fields[] = array('type' => 'hidden', 'name' => 'csrftoken', 'value' => make_csrftoken());

$assignment_fields = '</fieldset>'.get_form_content($form_fields);

$output .='<br style="clear:both"/><br />
        <div class="boxHeaderLeft">'.__('Sharing').'</div>
        <div class="boxContent">'.$assignment_fields.'</div>
        <br style="clear:both"/></form><br />';
$output.='</div>';
echo $output;

?>
