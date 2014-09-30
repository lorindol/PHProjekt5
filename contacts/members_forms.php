<?php
/**
* members form script
*
* @package    contacts
* @module     members
* @author     Albrecht Guenther, $Author: gustavo $
* @licence    GPL, see www.gnu.org/copyleft/gpl.html
* @copyright  2000-2006 Mayflower GmbH www.mayflower.de
* @version    $Id: members_forms.php,v 1.8 2006/11/07 00:28:21 gustavo Exp $
*/
if (!defined('lib_included')) die('Please use index.php!');
include_once(LIB_PATH.'/access_form.inc.php');

// show data details of members, edit own data
$result = db_query("SELECT ID, anrede, vorname, nachname, kurz, firma, email, tel1, tel2,
                           mobil, fax, strasse, stadt, plz, land, ldap_name
                      FROM ".DB_PREFIX."users
                     WHERE ID = ".(int)$ID) or db_die();
$row = db_fetch_row($result);
if ((PHPR_LDAP != 0) && ($ldap_conf[$row[15]]["ldap_sync"] == "2")) {
    get_ldap_usr_data($row[0]);
}

$row = explode("?", html_out(implode('?', $row)));
if(($user_ID <> $row[0]) or ((PHPR_LDAP != 0) && ($ldap_conf[$row[15]]['ldap_sync'] == '2'))) {
    read_o($read_o);
}


/******************************
*           tabs
******************************/
$tabs = array();
$buttons = array();

// form start
$hidden = array('ID' => $ID);
if(SID) $hidden[session_name()] = session_id();
$buttons[] = array('type' => 'form_start', 'hidden' => $hidden);
$output .= '<div id="global-header">';
$output .= get_buttons($buttons);
$output .= get_tabs_area($tabs);
$output .= '</div>';
    
/******************************
*         buttons
******************************/
$buttons = array();
$buttons[] = array('type' => 'text', 'text' => $row[3].', '.$row[2]);
$buttons[] = array('type' => 'separator');
// modify
if ($user_ID == $row[0]) {
    $buttons[] = array('type' => 'hidden', 'name' => 'mode', 'value' => 'data');
    $buttons[] = array('type' => 'submit', 'name' => 'members', 'value' => __('OK'), 'active' => false);
    $buttons[] = array('type' => 'submit', 'name' => 'members_update', 'value' => __('Apply'), 'active' => false);
}
// cancel
$buttons[] = array('type' => 'link', 'href' => 'members.php?sort='.$sort.'&amp;mode=view&amp;direction='.$direction, 'text' => __('List View'), 'active' => false);
$output .= '<div id="global-content">';
$output .= get_buttons_area($buttons);


/*******************************
*       basic fields
*******************************/
$form_fields = array();
$form_fields[] = array('type' => 'text', 'name' => 'anrede', 'label' => __('Salutation').__(':'), 'value' => $row[1], 'readonly' => true);
$form_fields[] = array('type' => 'text', 'name' => 'vorname', 'label' => __('First Name').__(':'), 'value' => $row[2], 'readonly' => true);
$form_fields[] = array('type' => 'text', 'name' => 'firma', 'label' => __('Company').__(':'), 'value' => $row[5], 'readonly' => ($read_o != 0));
$form_fields[] = array('type' => 'text', 'name' => 'email', 'label' => __('Email').__(':'), 'value' => $row[6], 'readonly' => ($read_o != 0));
$form_fields[] = array('type' => 'text', 'name' => 'fax', 'label' => __('Fax').__(':'), 'value' => $row[10], 'readonly' => ($read_o != 0));
$form_fields[] = array('type' => 'text', 'name' => 'plz', 'label' => __('Zip code').__(':'), 'value' => $row[13], 'readonly' => ($read_o != 0));
$form_fields[] = array('type' => 'text', 'name' => 'land', 'label' => __('Country').__(':'), 'value' => $row[14], 'readonly' => ($read_o != 0));
$basic_fields_left = get_form_content($form_fields);

$form_fields = array();
$form_fields[] = array('type' => 'text', 'name' => 'nachname', 'label' => __('Family Name').__(':'), 'value' => $row[3], 'readonly' => true);
$form_fields[] = array('type' => 'text', 'name' => 'kurz', 'label' => __('Short Form').__(':'), 'value' => $row[4], 'readonly' => true);
$form_fields[] = array('type' => 'text', 'name' => 'tel1', 'label' => __('Phone').'1'.__(':'), 'value' => $row[7], 'readonly' => ($read_o != 0));
$form_fields[] = array('type' => 'text', 'name' => 'tel2', 'label' => __('Phone').'2'.__(':'), 'value' => $row[8], 'readonly' => ($read_o != 0));
$form_fields[] = array('type' => 'text', 'name' => 'mobil', 'label' => __('Mobile Phone').__(':'), 'value' => $row[9], 'readonly' => ($read_o != 0));
$form_fields[] = array('type' => 'text', 'name' => 'strasse', 'label' => __('Street').__(':'), 'value' => $row[11], 'readonly' => ($read_o != 0));
$form_fields[] = array('type' => 'text', 'name' => 'stadt', 'label' => __('City').__(':'), 'value' => $row[12], 'readonly' => ($read_o != 0));
$basic_fields_right = get_form_content($form_fields);

$output .= '
<br />
<fieldset>
<legend>'.__('Basis data').'</legend>
<div id="left_container">
'.$basic_fields_left.'
</div>
<div id="right_container">
'.$basic_fields_right.'</div>
</fieldset>

</form>
';
$output .= '</div>';
echo $output;

?>
