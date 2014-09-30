<?php
/**
 * organisations form view
 *
 * @package    organisations
 * @subpackage organisations
 * @author     Gustavo Solt, $Author: gustavo $
 * @licence    GPL, see www.gnu.org/copyleft/gpl.html
 * @copyright  2000-2006 Mayflower GmbH www.mayflower.de
 * @version    $Id: 
 */

if (!defined('lib_included')) die('Please use index.php!');
include_once(LIB_PATH.'/access_form.inc.php');
// selector-tranformation stuff
require_once(LIB_PATH.'/selector/selector.inc.php');

// ******************
// create/edit organisations
// ******************

// Contacts
if (isset($contact_personen) && ($ID!=0)) {
    update_organisation_personen_table($ID, $contact_personen, $_POST);
}

// check permission and fetch values for viewing or modifying a record
if ($ID > 0) {
    // check permission
    $result = db_query("SELECT ID, von, acc_write,gruppe
                          FROM ".DB_PREFIX."organisations
                         WHERE ID = ".(int)$ID." 
                           AND is_deleted is NULL
                           AND (acc LIKE 'system' OR ((von = ".(int)$user_ID." OR acc LIKE 'group' OR acc LIKE '%\"$user_kurz\"%')".group_string()."))") or db_die();
    $row = db_fetch_row($result);
    if (!$row[0]) { die("You are not privileged to do this!"); }
    if ($row[1] <> $user_ID and $row[2] <> 'w') { $read_o = 1; }
    else $read_o = 0;
    if ($row[1] <> $user_ID and PHPR_ALTER_ACC!=1) $read_acc = 1;
    else $read_acc = 0;
    change_group($row[3]);
    
    // fetch values from db
    $query = "SELECT name,link,adress,category,
                     parent,von,acc_write,acc
                FROM ".DB_PREFIX."organisations
               WHERE ID = ".(int)$ID."
                 AND is_deleted is NULL";
    $result = db_query($query) or db_die();
    $row = db_fetch_row($result);
    touch_record('organisations', $ID);
}


//unset ID when copying project
$ID = prepare_ID_for_copy($ID,$copy);

// **********
// start form
if ($ID) $head = slookup('projekte','name','ID',$ID,'1');
else $head=__('New organisation');
if(!$head) $head=__('New organisation');


/******************************
 *           tabs
 ******************************/
$tabs = array();
$tabs[] = array('href' => '../contacts/contacts.php', 'active' => false, 'id' => '', 'target' => '_self', 'text' => __('Contacts'), 'position' => 'left');
$tabs[] = array('href' => '../contacts/members.php', 'active' => false, 'id' => '', 'target' => '_self', 'text' => __('Group members'), 'position' => 'left');
$buttons = array();

// form start
$hidden = array('mode'=>'data','input'=>1, 'action'=> $action, 'ID'=>$ID);
if (SID) $hidden[session_name()]= session_id();
foreach ($view_param as $key=>$value) {
    $hidden[$key] = $value;
}

$buttons[] = array('type' => 'form_start', 'hidden' => $hidden, 'enctype' => 'multipart/form-data', 'name' => 'frm');
$output = '<div id="global-header">';
$output .= get_tabs_area($tabs);
$output .= breadcrumb($module, breadcrumb_data($action));
$output .= '</div>';
$output.=get_buttons($buttons);
$output .= $content_div;

/******************************
 *         buttons
 ******************************/
$buttons = get_default_buttons($read_o,$ID,$justform,'organisations',true,$sid);
$output .= get_buttons_area($buttons);

$out_array = array();
/*******************************
 *       basic fields
 *******************************/
$basic_fields  =build_form($fields);

/******************************
 *       categorization
 ******************************/
$form_fields = array();
$select_field = '<label for="parent" class="center2">'.__('Parent object').':</label>
                 <select id="parent" class="options" name="parent"'.read_o($read_o).'><option value="0"></option>';
$select_field .= show_elements_of_tree("organisations",
                        "name",
                        "WHERE is_deleted is NULL and (acc LIKE 'system' OR ((von = $user_ID OR acc LIKE 'group' OR acc LIKE '%\"$user_kurz\"%') AND $sql_user_group))",
                        "acc",
                        " ORDER BY name", $row[4], "parent", $ID);
$select_field .= '</select>';
$form_fields[] = array('type' => 'parsed_html', 'html' => $select_field);
$categorization_fields = get_form_content($form_fields);

/**************************************************
(Contacts)
**************************************************/

// values of the contacts
if (!isset($contact_personen)) {
    if (!isset($_POST['contact_personen'])) {
        $tmp_result = db_query("SELECT contact_ID FROM ".DB_PREFIX."organisation_contacts_rel
                                WHERE organisation_ID = ".(int)$ID) or db_die();
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
    $query = "SELECT contact_ID, role FROM ".DB_PREFIX."organisation_contacts_rel
              WHERE organisation_ID = ".(int)$ID;
    $result = db_query($query) or db_die();
    while ($row = db_fetch_row($result)) {
        $contact_query = "SELECT ID, vorname, nachname, email
                            FROM ".DB_PREFIX."contacts
                           WHERE ID = ".(int)$row[0]."
                             AND is_deleted is NULL";
        $contact_result = db_query($contact_query) or db_die();
        $contact_row = db_fetch_row($contact_result);
        $contact_table .= "
            <tr>
                <td>".$contact_row[1]."</td>
                <td>".$contact_row[2]."</td>
                <td>".$contact_row[3]."</td>
                <td>".make_select_roles($row[0],$row[1],'contacts','organisation')."</td>
                <td><input name='c_".$row[0]."_text_role' type='text' size='8' maxlength='200'></td>
            </tr>
        ";
    }
} else if (is_array($contact_personen)&&(!empty($contact_personen))) {
    foreach($contact_personen as $tmp => $contact_id) {
        $contact_query = "SELECT ID, vorname, nachname, email
                            FROM ".DB_PREFIX."contacts
                           WHERE ID = ".(int)$contact_id."
                             AND is_deleted is NULL";
        $contact_result = db_query($contact_query);
        $contact_row = db_fetch_row($contact_result);
        $contact_role_str = 'c_'.$contact_id.'_role';
        $contact_role = (isset($_POST[$contact_role_str])) ? xss($_POST[$contact_role_str]) : '';
        $contact_table .= "
            <tr>
                <td>".$contact_row[1]."</td>
                <td>".$contact_row[2]."</td>
                <td>".$contact_row[3]."</td>
                <td>".make_select_roles($contact_id,$contact_role,'contacts','organisation')."</td>
                <td><input name='c_".$contact_id."_text_role' type='text' size='8' maxlength='200'></td>
            </tr>
        ";
    }
}
$contact_table .= '</tbody></table>';

$contact_form = '
<div style="float:left;width:30%;padding:10px;">'.$contact_assignment.'</div>
<div style="float:right;width:50%;padding:10px;">'.$contact_table.'</div>
<br style="clear:both"/>
';
$form_fields[] = array('type' => 'parsed_html', 'html' => $contact_form);
$contact_fields = get_form_content($form_fields);

/******************************
 *         assignment
 ******************************/
include_once("../lib/access_form.inc.php");
$form_fields = array();

// values of the access
if (!isset($persons)) {
    if (!isset($_POST[$persons])) $str_persons = $row[7];
    else $str_persons = xss($_POST[$persons]);
} else $str_persons = $acc = serialize($persons);

if (!isset($acc_write)) {
    if (!isset($_POST['acc_write'])) $acc_write = $row[6];
    else $acc_write = xss($_POST['acc_write']);
}

// acc, exclude the user itself, acc_write, no parent possible, write access=yes
$form_fields[] = array('type' => 'parsed_html', 'html' => access_form2($str_persons, 1, $acc_write, 0, 1,'acc',$read_acc));
$assignment_fields = get_form_content($form_fields);

$out_array = array_merge($out_array,$basic_fields);
$out_array[]=array(__('Categorization'),'<br/>'.$categorization_fields);
$out_array[]=array(__('Contacts'), $contact_fields);
$out_array[]=array(__('Release'),$assignment_fields);

$output.= generate_output($out_array);


$output .= '<div class="hline"></div>';

$output .= get_buttons_area($buttons);

$output .= '<div class="hline"></div>';
$output .= '</form>';

echo $output;

?>
