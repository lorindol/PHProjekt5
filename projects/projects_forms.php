<?php

// projects_forms.php - PHProjekt Version 5.2
// copyright  ©  2000-2005 Albrecht Guenther  ag@phprojekt.com
// www.phprojekt.com 
// Author: Albrecht Guenther, $Author: gustavo $
// $Id: projects_forms.php,v 1.111.2.14 2007/10/04 14:09:23 gustavo Exp $

// check whether the lib has been included - authentication!
if (!defined("lib_included")) die("Please use index.php!");

// check role
if (check_role("projects") < 1) die("You are not allowed to do this!");

include_once(LIB_PATH.'/selector/selector.inc.php');
include_once(LIB_PATH.'/access_form.inc.php');
if ($justform == 2) {
    $onload[] = 'window.opener.location.reload();';
    $onload[] = 'window.close();';
}
else if ($justform > 0) {
    $justform++;
}

if (isset($personen) && ($ID!=0)) {
    update_project_personen_table($ID, $personen,'user',xss_array($_POST));
}

if (isset($contact_personen) && ($ID!=0)) {
    update_project_personen_table($ID, $contact_personen,'contact',xss_array($_POST));
}

// update project? -> fetch values form record
if ($action <> "new" and $ID > 0) {
    $result = db_query("SELECT ID, name, anfang, ende, chef, contact, stundensatz, budget, wichtung,
                               ziel, note, depend_mode, depend_proj, next_mode, next_proj, probability,
                               ende_real, kategorie, status, statuseintrag, parent, acc,
                               acc_write, von,gruppe
                          FROM ".DB_PREFIX."projekte
                         WHERE (acc LIKE 'system'
                                OR ((von = ".(int)$user_ID." OR acc LIKE 'group' OR acc LIKE '%\"$user_kurz\"%')
                                    ".group_string()."))
                           AND ID = ".(int)$ID) or db_die();
    $row = db_fetch_row($result);
    // check access
    // genreal acces - either the user has direct access to it or the user has chief status
    if (!$row[0] and $user_type!=3) die("You are not privileged to do this!");

    if (($row[23] <> $user_ID and $row[22] <> 'w') or check_role("projects") < 2) $read_o = 1;
    if ($row[23] <> $user_ID and PHPR_ALTER_ACC!=1) $read_acc = 1;
    else $read_acc = 0;

    // get values
    $project_name   = html_out($row[1]);
    $anfang         = $row[2];
    $ende           = $row[3];
    $chef           = $row[4];
    $contact        = $row[5];
    $stundensatz    = $row[6];
    $budget         = $row[7];
    $wichtung       = $row[8];
    $ziel           = $row[9];
    $note           = $row[10];
    $depend_mode    = $row[11];
    $depend_proj    = $row[12];
    $next_mode      = $row[13];
    $next_proj      = $row[14];
    $probability    = $row[15];
    $ende_real      = $row[16];
    $category       = $row[17];
    $status         = $row[18];
    $statuseintrag  = $row[19];
    if (!isset($parent)) $parent   = $row[20];
    $acc            = $row[21];
    $acc_write      = $row[22];
    change_group($row[24]);
}
// set variables for a new project:
else {

    set_new_project();

}
//unset ID when copying project
$ID=prepare_ID_for_copy($ID,$copy);
// tabs
$tabs = array();
$buttons = array();
if ($justform == 2) $justform = 1;
$hidden  = array('justform' => $justform);
if (SID) $hidden[session_name()] = session_id();

// form start
$date_format_text = __('Date format').' = '.$date_format_object->get_user_format();
$buttons[] = array( 'type'     => 'form_start',
'hidden'   => $hidden,
'enctype'=>"multipart/form-data",
'name'     => 'frm',
'onsubmit' => "return chkForm('frm','name','".__('Please insert a name')."') &amp;&amp; ".
"checkUserDateFormat('anfang','".__('Begin').':\n'.$date_format_text."') &amp;&amp; ".
"checkUserDateFormat('ende','".__('End').':\n'.$date_format_text."') &amp;&amp;".
"checkDates('anfang','ende','".__('Begin > End')."!') &amp;&amp; ".
"chkNumbers('frm','budget','".__('Calculated budget has a wrong format')."') &amp;&amp; ".
"chkNumbers('frm','stundensatz','".__('Hourly rate has a wrong format')."');");
$output = '<div id="global-header">';
$output .= get_tabs_area($tabs);

// breadcrumb-stuff
// show a project
if (!empty($ID)) {
    $title = htmlentities(slookup('projekte', 'name', 'ID', (int) $ID,'1'));
}
else {
    $title = __('New');
}

$output .= breadcrumb($module, array(array('title'=>$title)));unset($title);
$output .= '</div>';
$output .= get_buttons($buttons);
$output .= $content_div;

unset($title);

// check if project is deleteable
$deleteable = false; // default deleteable status

if (!$read_o and check_role("projects") > 1 and $ID > '0') {
    $result2 = db_query("SELECT ID
                           FROM ".DB_PREFIX."projekte
                          WHERE parent = ".(int)$ID) or db_die();
    $row2 = db_fetch_row($result2);
    if ($row2[0] == '' and $row[23] == $user_ID) {
        $deleteable = true;
    }
}

$buttons = get_default_buttons($read_o, $ID, $justform, $module, $deleteable, $sid);

/*************************************
anlegen hidden value
************************************
if (!$read_o && $user_role > 1 && $ID == 0) {
$buttons[] = array('type' => 'hidden', 'name' => 'anlegen', 'value' => 'neu_anlegen');
}

************************************
aendern hidden value
*************************************/
if (!$read_o && $user_role > 1 && $ID > 0) {
    $buttons[] = array('type' => 'hidden', 'name' => 'aendern', 'value' => 'aendern');
}


if ($read_o and check_role("projects") > 1 and $user_ID == $chef) {
    // modify status
    $buttons[] = array('type' => 'submit', 'name' => 'modify_status_b', 'value' => __('Modify status'), 'active' => false);
    // hidden
    $buttons[] = array('type' => 'hidden', 'name' => 'modify_status', 'value' => 'modify_status');
}

$output .= get_buttons_area($buttons);

$output .= '
<div class="inner_content">
<a name="content"></a>
<fieldset>
';

/*************************************
Header Box 1 (Basis data)
*************************************/
$output .= get_box_header(__('Basis data'), 'oben');

$basis_data = "";
// calculate hidden fields
$hidden = array_merge(array('ID'=>$ID, 'type'=>$type, 'mode'=>'data', 'gruppe'=>'user_group', 'justform'=>$justform, 'project_name'=>$project_name), $view_param);
// add hidden fields
$basis_data .= hidden_fields($hidden);
// fields html
$basis_data .= build_form($fields);
$basis_data .= '
    </fieldset>
</div>
';

$output .= $basis_data;

/*************************************
Header Box 2 (Categorization)
*************************************/
$output .= '<fieldset>';
$output .= get_box_header(__('Categorization'), 'unten');

$categorization .= '
    <label for="parent" class="label_block">'.__('Sub-Project of').': </label>'.
selector_create_select_projects('parent', $parent, 'action_form_to_parent_selector', $ID, 'class="halfsize" id="parent"'.read_o($read_o));

// sort subprojects
if ($ID > 0) {
    $result2 = db_query("SELECT COUNT(ID)
                           FROM ".DB_PREFIX."projekte
                          WHERE parent = ".(int)$ID) or db_die();
    $row2 = db_fetch_row($result2);
    if ($row2[0] > 1) {
        $categorization .= "&nbsp; &nbsp; (<a target='_blank' href='./projects_sortbox.php?action=sort&amp;extra_value=".$ID."'>".__('Sort sub projects')."</a>)\n";
    }
}    
$categorization .= "<br />\n";

// status - progres
$read_o_status = $user_ID == $chef ? 0 : 1;
$categorization .= "<label for='parent' class='label_block'>".__('Status')." [%]: </label>\n";
$categorization .= "<input name='status' value='$status' type='text' class='halfsize' ".read_o($read_o_status, 'readonly')."/>\n";
$categorization .= '<br style="clear:both" /><br />'."\n";

// sort subprojects - first look which records in the same branch exist
if ($ID > 0) {
    $categorization_elements = '';
    $result2 = db_query("SELECT ID, name
                           FROM ".DB_PREFIX."projekte
                          WHERE (acc LIKE 'system'
                                OR ((von = ".(int)$user_ID." OR acc LIKE 'group' OR acc LIKE '%\"$user_kurz\"%')
                                    ".group_string('projects').")) 
                            AND parent = ".(int)$parent." 
                            AND ID <> ".(int)$ID." 
                          ORDER BY name") or db_die();
  
    while ($row2 = db_fetch_row($result2)) {
        $categorization_elements .= "<option value='".$row2[0]."'";
        if ($row2[0] == $depend_proj) $categorization_elements .= ' selected="selected"';
        $categorization_elements .= ">".$row2[1]."</option>\n";    
    }
    // show dependency box only if there are elements in the same branch
    if ($categorization_elements <> '') {
        // dependency
            // check where there are any other projects on this level
        $categorization .= "<label for='depend_mode' class='label_block'>".__('Dependency').":</label>\n";
        $categorization .= "<select class='halfsize' name='depend_mode'".read_o($read_o)."><option value='0'>\n";
        foreach ($dependencies as $dep1 => $dep2) {
            $categorization .= "<option value='$dep1'";
            if ($dep1 == $depend_mode) $categorization .= ' selected="selected"';
            $categorization .= ">$dep2:</option>\n";
        }
        $categorization .= "</select>\n";
        // fetch all of these neighbours and display them
        $categorization .= "<label for='depend_mode' class='label_block'>".__('Dependend projects').":</label>\n";
        $categorization .= "<select class='halfsize' name='depend_proj'".read_o($read_o)."><option value='0'>\n";
        $categorization .= $categorization_elements;
        $categorization .= "</select>\n";
    }
    // otherwise set the dependency to 0 to avoid that this project has an 'old' dependency
    else {
        $output.= "<input type='hidden' name='dependency' value='0' />\n";
    }
}
$categorization .= "</fieldset>\n\n";


$output .= $categorization;

/**************************************************
Header Box 3 (Assignment of Participants)
**************************************************/
$box_right_data = array();
$box_right_data['type']         = 'anker';
$box_right_data['anker_target'] = 'oben';

// access
// select participants

// values of the access
if (!isset($persons)) {
    if (!isset($_POST[$persons])) $str_persons = $row[21];
    else $str_persons = xss($_POST[$persons]);
} else $str_persons = $acc = serialize($persons);

if (!isset($acc_write)) {
    if (!isset($_POST['acc_write'])) $acc_write = $row[22];
    else $acc_write = xss($_POST['acc_write']);
}

// acc_read, exclude the user itself, acc_write, no parent possible, write access=yes
$output .= access_form2($str_persons, 1, $acc_write, 0, 1,'acc',$read_acc);
/**************************************************
Buttons
**************************************************/

$buttons = get_default_buttons($read_o, $ID, $justform, $module, $deleteable, $sid);

/*************************************
anlegen hidden value
************************************
if (!$read_o && $user_role > 1 && $ID == 0) {
$buttons[] = array('type' => 'hidden', 'name' => 'anlegen', 'value' => 'neu_anlegen');
}

************************************
aendern hidden value
*************************************/
if (!$read_o && $user_role > 1 && $ID > 0) {
    $buttons[] = array('type' => 'hidden', 'name' => 'aendern', 'value' => 'aendern');
}


if ($read_o and check_role("projects") > 1 and $user_ID == $chef) {
    // modify status
    $buttons[] = array('type' => 'submit', 'name' => 'modify_status_b', 'value' => __('Modify status'), 'active' => false);
    // hidden
    $buttons[] = array('type' => 'hidden', 'name' => 'modify_status', 'value' => 'modify_status');
}


$output .= '<div class="hline"></div>';
$output .= get_buttons_area($buttons);
$output .= '<div class="hline"></div>';

/**************************************************
(Participants)
**************************************************/

// values of the participants
if (!isset($personen)) {
    if (!isset($_POST['personen'])) {
        if($ID>0){
            $tmp_result = db_query("SELECT user_ID FROM ".DB_PREFIX."project_users_rel
                                                   WHERE project_ID = ".(int)$ID." 
                                                   AND user_ID <> 0") 
                          or db_die();
            while ($tmp_row = db_fetch_row($tmp_result)) {
                $personen[] = $tmp_row[0];
            }
        }
        else $personen = array();
    }
    else $personen = xss_array($_POST['personen']);
}

if (is_array($personen)) $acc_read = $personen;
else                     $acc_read = unserialize($personen);

$assignment = '
    <fieldset>
        <legend>'.__('Participants').'</legend>'.
        selector_create_select_users('personen[]', $acc_read, 'action_form_to_participants_selector', '0', read_o($read_o)).'<br />
    </fieldset>
';

// table of selected users
$user_table = '
    <table class="relations">
        <caption>'.__('Participants').'</caption>
        <thead>
            <tr>
                <td title="'.__('Family Name').'">'.__('Family Name').'</td>
                <td title="'.__('First Name').'">'.__('First Name').'</td>
                <td title="'.__('email').'">'.__('email').'</td>
                <td title="'.__('Role').'">'.__('Role').'</td>
                <td title="'.__('or new Role').'">'.__('or new Role').'</td>
            </tr>
        </thead>
        <tbody>
    ';

if (isset($ID)&&($ID!=0)) {
    $query = "SELECT user_ID, role FROM ".DB_PREFIX."project_users_rel
               WHERE project_ID = ".(int)$ID;
    $result = db_query($query);
    while ($row = db_fetch_row($result)) {
        $user_query = "SELECT ID, vorname, nachname, email
                         FROM ".DB_PREFIX."users
                     WHERE ID = ".(int)$row[0];
        $user_result = db_query($user_query);
        $user_row = db_fetch_row($user_result);
        $user_table .= "
            <tr>
                <td>".$user_row[2]."</td>
                <td>".$user_row[1]."</td>
                <td>".$user_row[3]."</td>
                <td>".make_select_roles($row[0],$row[1],'users')."</td>
                <td><input name='u_".$row[0]."_text_role' type='text' size='8' maxlength='200'></td>
            </tr>
        ";
    }
} else if (is_array($personen)&&(!empty($personen))) {
    foreach($personen as $personen_id) {
        $personen_id = (int) $personen_id;
        $user_query = "SELECT ID, vorname, nachname, email
                         FROM ".DB_PREFIX."users
                     WHERE ID = ".$personen_id;
        $user_result = db_query($user_query);
        $user_row = db_fetch_row($user_result);
        $user_role_str = 'u_'.$personen_id.'_role';
        $user_role = (isset($_POST[$user_role_str])) ? xss($_POST[$user_role_str]) : '';
        $user_table .= "
            <tr>
                <td>".$user_row[2]."</td>
                <td>".$user_row[1]."</td>
                <td>".$user_row[3]."</td>
                <td>".make_select_roles($personen_id,$user_role,'users')."</td>
                <td><input name='u_".$personen_id."_text_role' type='text' size='8' maxlength='200'></td>
            </tr>
        ";
    }
}
$user_table .= '</tbody></table>';

$output .= '
        <div style="float:left;width:10%;padding:10px;">'.$assignment.'</div>
        <div style="float:right;width:70%;padding:23px;">'.$user_table.'</div>
        <br style="clear:both"/>';

$output .= '<hr />';


/**************************************************
(Contacts)
**************************************************/

// values of the contacts
if (!isset($contact_personen)) {
    if (!isset($_POST['contact_personen'])) {
        $tmp_result = db_query("SELECT contact_ID FROM ".DB_PREFIX."project_contacts_rel
                                WHERE project_ID = ".(int)$ID) or db_die();
        while ($tmp_row = db_fetch_row($tmp_result)) {
            $contact_personen[] = $tmp_row[0];
        }
    } else $contact_personen = xss_array($_POST['contact_personen']);
}

$contact_assignment = '
    <fieldset>
        <legend>'.__('Contacts').'</legend>'.
        selector_create_select_contacts('contact_personen[]', $contact_personen,'action_form_to_contact_selector', '0', read_o($read_o), '7', '1').'<br />
    </fieldset>
';

// table of selected contact
$contact_table = '
    <table class="relations">
        <caption>'.__('Contacts').'</caption>
        <thead>
            <tr>
                <td title="'.__('Family Name').'">'.__('Family Name').'</td>
                <td title="'.__('First Name').'">'.__('First Name').'</td>
                <td title="'.__('email').'">'.__('email').'</td>
                <td title="'.__('Role').'">'.__('Role').'</td>
                <td title="'.__('or new Role').'">'.__('or new Role').'</td>
            </tr>
        </thead>
        <tbody>';

if (isset($ID)&&($ID!=0)) {
    $query = "SELECT contact_ID, role FROM ".DB_PREFIX."project_contacts_rel
              WHERE project_ID = ".(int)$ID;
    $result = db_query($query) or db_die();
    while ($row = db_fetch_row($result)) {
        $contact_query = "SELECT ID, vorname, nachname, email
                  FROM ".DB_PREFIX."contacts
                  WHERE ID = ".(int)$row[0];
        $contact_result = db_query($contact_query) or db_die();
        $contact_row = db_fetch_row($contact_result);
        $contact_table .= "
            <tr>
                <td>".$contact_row[2]."</td>
                <td>".$contact_row[1]."</td>
                <td>".$contact_row[3]."</td>
                <td>".make_select_roles($row[0],$row[1],'contacts')."</td>
                <td><input name='c_".$row[0]."_text_role' type='text' size='8' maxlength='200'></td>
            </tr>
        ";
    }
} else if (is_array($contact_personen)&&(!empty($contact_personen))) {
    foreach($contact_personen as $tmp => $contact_id) {
        $contact_id = (int) $contact_id;
        $contact_query = "SELECT ID, vorname, nachname, email
                         FROM ".DB_PREFIX."contacts
                     WHERE ID = ".$contact_id;
        $contact_result = db_query($contact_query);
        $contact_row = db_fetch_row($contact_result);
        $contact_role_str = 'c_'.$contact_id.'_role';
        $contact_role = (isset($_POST[$contact_role_str])) ? xss($_POST[$contact_role_str]) : '';
        $contact_table .= "
            <tr>
                <td>".$contact_row[2]."</td>
                <td>".$contact_row[1]."</td>
                <td>".$contact_row[3]."</td>
                <td>".make_select_roles($contact_id,$contact_role,'contacts')."</td>
                <td><input name='c_".$contact_id."_text_role' type='text' size='8' maxlength='200'></td>
            </tr>
        ";
    }
}
$contact_table .= '</tbody></table>';
if (isset($ID)&&($ID!=0)) {
    $hidden_fields = array ( "ID"   => $ID,
    "mode" => "data");
} else {
    $hidden_fields = array ( "mode" => "data");
}

$output .= '
    '.hidden_fields($hidden_fields).'
    <div style="float:left;width:10%;padding:10px;">'.$contact_assignment.'</div>
    <div style="float:right;width:70%;padding:23px;">'.$contact_table.'</div>
    <br style="clear:both"/>
';

/**************************************************
Buttons
**************************************************/

$buttons = get_default_buttons($read_o, $ID, $justform, $module, $deleteable, $sid);



/*************************************
anlegen hidden value
************************************
if (!$read_o && $user_role > 1 && $ID == 0) {
$buttons[] = array('type' => 'hidden', 'name' => 'anlegen', 'value' => 'neu_anlegen');
}

************************************
aendern hidden value
*************************************/
if (!$read_o && $user_role > 1 && $ID > 0) {
    $buttons[] = array('type' => 'hidden', 'name' => 'aendern', 'value' => 'aendern');
}


if ($read_o and check_role("projects") > 1 and $user_ID == $chef) {
    // modify status
    $buttons[] = array('type' => 'submit', 'name' => 'modify_status_b', 'value' => __('Modify status'), 'active' => false);
    // hidden
    $buttons[] = array('type' => 'hidden', 'name' => 'modify_status', 'value' => 'modify_status');
}



if ($ID) {
    $output .= '<div class="hline"></div>';
    $output .= get_buttons_area($buttons);
    $output .= '<div class="hline"></div>';
}

// close the big form
$output .= '</form>';

/**************************************************
related objects
**************************************************/
if (($ID > 0) && ($justform < 1)) {
    $output .= "<br />\n";
    $projekt_ID = $ID;
    // include the lib
    include_once(LIB_PATH."/show_related.inc.php");
    $referer = "projects.php?mode=forms&amp;ID=$ID";
    // show related todos
    if (PHPR_TODO and check_role("todo") > 0) {
        $query = "project = ".(int)$ID;
        $output .= show_related_todo($query, $referer);
        $output .= "<br />\n";
    }

    // related notes, show only for existing projects
    if (PHPR_NOTES and check_role("notes") > 0) {
        $query = "projekt = ".(int)$ID;
        $output .= show_related_notes($query, $referer);
        $output .= "<br />\n";
    }

    // show related files
    if (PHPR_FILEMANAGER and check_role("filemanager") > 0) {
        $query = "div2 = '$ID'";
        $output .= show_related_files($query, $referer);
        $output .= "<br />\n";
    }

    // show related events
    if (PHPR_CALENDAR and check_role("calendar") > 0) {
        $query = "projekt = ".(int)$ID;
        $output .= show_related_events($query, $referer);
        $output .= "<br />\n";
    }

    // show related helpdesk
    if (PHPR_RTS > 0 && check_role("helpdesk") > 0) {
        $query = "proj = ".(int)$ID;
        $output.= show_related_helpdesk($query,$referer);
        $output.= "<br />\n";
    }

    // show related emails
    if (PHPR_QUICKMAIL > 0 && check_role("mail") > 0) {
        $query = "projekt = ".(int)$ID;
        $output.= show_related_mail($query,$referer);
        $output.= "<br />\n";
    }
    
    // show related protocolls (please add to config.inc.php define('PHP_PROTOCOLL', '1'); )
    if(PHP_PROTOCOLL > 0){
        $query = "project = ".(int)$ID;
        $output.= show_related_protokolls($query,$referer);
        $output.= "<br />\n";
        
    }

    // show history
    if (PHPR_HISTORY_LOG == 2) $output .= history_show('projekte', $ID);
}
// end show related objects
// ************************

$output .= '</div>';
echo $output;

// end  of big form :-)


// set variables for a new root project
function set_new_project() {
    global $ID, $anfang, $ende, $row;

    $ID      = $row[0] = 0;
    $anfang  = date("Y")."-".date("m")."-".date("d");
    $ende    = date("Y")."-12-31";
    $row[16] = 0;   // stundensatz / hourly rate
    $row[17] = 0;   // budget
}

?>
