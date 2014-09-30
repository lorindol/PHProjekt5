<?php

// contacts_profiles_forms.php - PHProjekt Version 5.2
// copyright  ©  2000-2005 Albrecht Guenther  ag@phprojekt.com
// www.phprojekt.com
// Author: Albrecht Guenther, $Author: alexander $
// $Id: contacts_profiles_forms.php,v 1.43.2.1 2007/01/23 15:35:48 alexander Exp $

// check whether the lib has been included - authentication!
if (!defined("lib_included")) die("Please use index.php!");

if(!defined('PATH_PRE')){
    define('PATH_PRE','../');
}
require_once(PATH_PRE.'lib/selector/selector.inc.php');

// if 'new record' is chosen, free a possible ID
if (isset($neu) && $neu) $ID = 0;
else $neu = false;

$aendern = isset($aendern) ? qss($aendern) : false;

// first form, for choose the profiles
$hidden_fields = array ( "mode"     => "profiles_data",
                         "action"   => $action);


$tabs = array();
// form start
$output .= '<div id="global-header">';
$output .= get_tabs_area($tabs);
$output .= '</div>';


$output .= "<div id=\"global-content\">\n";
if (!$neu and !$aendern)$output .= show_profiles_box($hidden_fields);
$output .= '</form>';
echo $output;

// second form, for edit/add the profiles
$output = "
<form style='display:inline' action='".$_SERVER['SCRIPT_NAME']."' method='post' />
".hidden_fields($hidden_fields)."\n";
if ($neu or $aendern) $output .= edit_profile();
$output .= '</form>
</div>
';
echo $output;

function show_profiles_box($hidden_fields) {
    global $user_ID, $ID, $action;
    
    $buttons[] = array('type' => 'form_start', 'hidden' => $hidden_fields, 'enctype' => 'multipart/form-data', 'name' => 'frm');
    $buttons[] = array('type' => 'submit', 'name' => 'aendern', 'value' => __('Modify'), 'active' => false);
    $buttons[] = array('type' => 'submit', 'name' => 'loeschen', 'value' => __('Delete'), 'active' => false, 'onclick' =>"\"return confirm('".__('Are you sure?')."');\"");
    $buttons[] = array('type' => 'submit', 'name' => 'neu', 'value' => __('New profile'), 'active' => false);
    $buttons[] = array('type' => 'link', 'href' => "contacts.php", 'text' => __('List view'), 'active' => false);

    $output = get_buttons_area($buttons);

    $output .= '<br />
            <fieldset>
                <legend>'.__('Profiles').'</legend>
                '.message_stack_out_all().
                __('<h2>Profiles</h2>In this section you can create, modify or delete profiles:').'
                <br /><br />
                <select name="ID">
                    <option value=""></option>
';

    // profiles for contacts
    $result = db_query("select ID, name
                        from ".DB_PREFIX."contacts_profiles
                        where von =".(int)$user_ID." 
                        order by name") or db_die();

    while ($row = db_fetch_row($result)) {
        $output.= "<option value='$row[0]'";
        if ($row[0] == $ID) $output.= " selected='selected'";
        $output .= ">".html_out($row[1])."</option>\n";
    }

    $output .= "
                </select>
                <br /><br />
            </fieldset>";

    return $output;
}

function edit_profile() {
    global $ID, $user_ID, $sql_user_group, $action, $user_group;
    global $remark, $name, $contact_personen;
    
    $buttons[] = array('type' => 'form_start', 'hidden' => array(), 'enctype' => 'multipart/form-data', 'name' => 'frm');
    if ($ID) $buttons[] = array('type' => 'submit', 'name' => 'db_aendern', 'value' => __('Modify'), 'active' => false);
    else $buttons[] = array('type' => 'submit', 'name' => 'db_neu', 'value' => __('OK'), 'active' => false);
    $buttons[] = array('type' => 'submit', 'name' => 'cancel_b', 'value' => __('Back'), 'active' => false);

    $out = get_buttons_area($buttons);
    // check permission
    if ($ID) {
        include_once(LIB_PATH."/permission.inc.php");
        check_permission("contacts_profiles", "von", $ID);
        $legend=__('Edit profile');
    }
    else $legend=__('New profile');
    // modify? -> fetch properties from this record and the selected contacts
    if ($ID) {
        $result = db_query("SELECT name, remark
                              FROM ".DB_PREFIX."contacts_profiles
                             WHERE ID = ".(int)$ID) or db_die();
        $row    = db_fetch_row($result);
        if (!isset($name))      $name   = html_out($row[0]);
        if (!isset($remark))    $remark = html_out($row[1]);
        // fetch all selected contacts and store them in an array
        $result = db_query("SELECT contact_ID
                              FROM ".DB_PREFIX."contacts_prof_rel
                             WHERE contacts_profiles_ID = ".(int)$ID) or db_die();
        while ($row = db_fetch_row($result)) {
            $selected[] = $row[0];
        }
    }

    $out .= '
        <br />
        <input type="hidden" name="ID" value='.$ID.' />
        <fieldset>
            <legend>'.$legend.'</legend>
            <label class="label_block" for="name">'.__('Description').': </label>
            <input class="halfsize" type=text name="name" value="'.$name.'" />
            <br />
            <br />
            <label class="label_block" for="remark">'.__('Remark').': </label>
            <textarea name=remark style="width:400px;height:150px">'.$remark.'</textarea>
            <br />
        </fieldset>'."\n";

    // begin table with all contacts
    // values of the contacts
    if (!isset($contact_personen)) {
        if (!isset($_POST[$contact_personen])) {
            $tmp_result = db_query("SELECT contact_ID FROM ".DB_PREFIX."contacts_prof_rel
                                    WHERE contacts_profiles_ID = ".(int)$ID) or db_die();
            while ($tmp_row = db_fetch_row($tmp_result)) {
                $contact_personen[] = $tmp_row[0];
            }
        } else $contact_personen = xss_array($_POST[$contact_personen]);
    }

    $contact_assignment = '
        <fieldset>
        <legend>'.__('Contacts').'</legend>'.
        selector_create_select_contacts('contact_personen[]', $contact_personen, 'action_form_to_profile_contact_selector', '0', '', '7', '1').'
        </fieldset>
    ';
    
    $out .= '<br /><br />'.$contact_assignment;

    $out .= "<br /><br />\n";

    return $out;
}

?>
