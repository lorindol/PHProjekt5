<?php
/**
 * @package    notes
 * @subpackage main
 * @author     Albrecht Guenther, $Author: nina $
 * @licence    GPL, see www.gnu.org/copyleft/gpl.html
 * @copyright  2000-2006 Mayflower GmbH www.mayflower.de
 * @version    $Id: notes_forms.php,v 1.72 2008-01-09 14:01:47 nina Exp $
 */

// check whether the lib has been included - authentication!
if (!defined("lib_included")) die("Please use index.php!");

// check role
if (check_role("notes") < 1) die("You are not allowed to do this!");


if (eregi("xxx", $projekt_ID)) $projekt_ID = substr($projekt_ID, 11);
if (eregi("xxx", $contact_ID)) $contact_ID = substr($contact_ID, 11);

include_once(LIB_PATH."/access_form.inc.php");
include_once(LIB_PATH."/permission.inc.php");

if ($justform == 2) $onload = array('window.opener.location.reload();', 'window.close();');
else if ($justform > 0) $justform++;
//echo set_body_tag();

// fetch data from record
if ($ID > 0) {
    // mark that the user has touched the record
    touch_record('notes', $ID);

    // fetch values from db
    $query = "SELECT ID, von, name, remark, contact, ext, div1, div2, projekt, sync1, sync2, acc, acc_write, parent,gruppe
                FROM ".DB_PREFIX."notes
               WHERE (acc LIKE 'system' OR ((von = ".(int)$user_ID." OR acc LIKE 'group' OR acc LIKE '%\"$user_kurz\"%')".group_string()."))
                 AND ".DB_PREFIX."notes.ID = ".(int)$ID."
                 AND is_deleted is NULL";
    $result = db_query($query) or db_die();
    $row = db_fetch_row($result);
    if (!$row[0]) die("You are not privileged to do this!");
    if (($row[1] <> $user_ID and $row[12] <> 'w') or check_role("notes") < 2) $read_o = 1;
    else $read_o = 0;
    if ($row[1] <> $user_ID and PHPR_ALTER_ACC!=1) $read_acc = 1;
    else $read_acc = 0;
    change_group($row[14]);      
}
//unset ID when copying project
$ID=prepare_ID_for_copy($ID,$copyform);
if ($ID) $head = slookup('notes', 'name', 'ID', $ID, true);
else     $head = __('New note');
if (!$head) $head = __('New note');

// tabs
$tabs    = array();
if ($justform == 2) $justform = 1;

$hidden = array('justform' => $justform, 'ID' => $ID, 'mode' => 'data');

if (SID) $hidden[session_name()]= session_id();

foreach ($view_param as $key=>$value) {

    $hidden[$key] = $value;

}

$buttons = array();
if (SID) $hidden[session_name()] = session_id();

// form start

$buttons[] = array('type' => 'form_start', 'name' => 'frm', 'hidden' => $hidden, 'onsubmit' => 'return chkForm(\'frm\',\'name\',\''.__('Please insert a name').'\');');

$output .= breadcrumb($module, array(array('title' => htmlentities($head))));
$output .= '</div>';

$output .= get_buttons($buttons);


$buttons = array();
if (!$read_o) {
    if (!$ID) {
        // create new note
        $buttons[] = array('type' => 'submit', 'name' => 'create_b', 'value' => __('OK'), 'active' => false);
        $buttons[] = array('type' => 'submit', 'name' => 'create_update_b', 'value' => __('Apply'), 'active' => false);
        // hidden
        $buttons[] = array('type' => 'hidden', 'name' => 'anlegen', 'value' => 'neu_anlegen');

        // cancel
        if ($justform > 0) {
            $buttons[] = array('type' => 'button', 'name' => 'close', 'value' => __('Close window'), 'active' => false, 'onclick' => 'window.close();');
        }
        else {
            $buttons[] = array('type' => 'link', 'href' => 'notes.php?type='.$type.'&amp;mode=view', 'text' => __('List View'), 'active' => false);
        }
    } // modify and delete
    else {
        // modify note
        $buttons[] = array('type' => 'submit', 'name' => 'modify_b', 'value' => __('OK'), 'active' => false);
        $buttons[] = array('type' => 'submit', 'name' => 'modify_update_b', 'value' => __('Apply'), 'active' => false);
        $buttons[]  = array('type' => 'link', 'href' => "notes.php?mode=forms&ID=".$ID."&copyform=1&justform=".$justform, 'text' => __('copy'), 'active' => false);

        // cancel
        if ($justform > 0) {
            $buttons[] = array('type' => 'button', 'name' => 'close', 'value' => __('Close window'), 'active' => false, 'onclick' => 'window.close();');
        }
        else {
            $buttons[] = array('type' => 'link', 'href' => 'notes.php?type='.$type.'&amp;mode=view', 'text' => __('List View'), 'active' => false);
        }

        // print
        if (($ID > 0) && ($justform < 1) ) {
        // archived or not
        if (check_archiv_flag($ID.$sid,$module)) {
            $buttons[]  = array('type' => 'link', 'href' => $_SERVER['SCRIPT_NAME'].'?mode=view&set_archiv_flag=0&amp;ID_s='.$ID.$sid, 'text' => __('Take back from Archive'), 'active' => false);
        }
        else {
            $buttons[]  = array('type' => 'link', 'href' => $_SERVER['SCRIPT_NAME'].'?mode=view&set_archiv_flag=1&amp;ID_s='.$ID.$sid, 'text' => __('Move to archive'), 'active' => false);
        }
            $buttons[] = array('type' => 'link', 'href' => $_SERVER['SCRIPT_NAME'].'?mode=view&amp;set_read_flag=1&amp;ID_s='.$ID.$sid, 'text' => __('Mark as read'), 'active' => false);
        }

        // hidden
        $buttons[] = array('type' => 'hidden', 'name' => 'aendern', 'value' => 'aendern');

        if ($row[1] == $user_ID) {
            $buttons[] = array('type' => 'submit', 'name' => 'delete_b', 'value' => __('Delete'), 'active' => false, 'onclick' => 'return confirm(\''.__('Are you sure?').'\');');
        }
    }
} // end buttons chief only


$output .= $content_div;
$output .= get_buttons_area($buttons);



$out_array=array();



/*******************************
*       basic fields
*******************************/



$basic_fields  = build_form($fields);



/*******************************
*    categorization fields
*******************************/
$form_fields = array();
$select_field = '<label for="parent" class="label_block">'.__('Parent object').':</label>
                 <select id="parent" class="halfsize" name="parent"'.read_o($read_o).'><option value="0"></option>';
$select_field .= show_elements_of_tree("notes",
                        "name",
                        "WHERE (acc LIKE 'system' OR ((von = $user_ID OR acc LIKE 'group' OR acc LIKE '%\"$user_kurz\"%') AND $sql_user_group)) AND is_deleted is NULL",
                        "acc",
                        " ORDER BY name", $row[13], "parent", $ID);
$select_field .= '</select><br /><br />';
$form_fields[] = array('type' => 'parsed_html', 'html' => $select_field);
$categorization_fields = get_form_content($form_fields);

/*******************************
*     assignment fields
*******************************/
$form_fields = array();
include_once("../lib/access_form.inc.php");
// acc_read, exclude the user itself, acc_write, no parent possible, write access=yes

// values of the access
if (!isset($persons)) {
    if (!isset($_POST[$persons])) $str_persons = $row[11];
    else $str_persons = xss($_POST[$persons]);
} else $str_persons = $acc = serialize($persons);

if (!isset($acc_write)) {
    if (!isset($_POST['acc_write'])) $acc_write = $row[12];
    else $acc_write = xss($_POST['acc_write']);
}

$form_fields[] = array('type' => 'parsed_html', 'html' => access_form($str_persons, 1, $acc_write, 0, 1,'acc',$read_acc));
$assignment_fields = get_form_content($form_fields);





$out_array = array_merge($out_array,$basic_fields);

$out_array[]=array(__('Categorization'),'<br/>'.$categorization_fields);

$out_array[]=array(__('Release'),$assignment_fields);

if ($ID > 0 and PHPR_HISTORY_LOG == 2) $out_array[]=array(__('History'),history_show('notes', $ID));



$output.= generate_output($out_array);



$output .= '<div class="hline"></div>';

$output .= get_buttons_area($buttons);

$output .= '<div class="hline"></div>';



$output .= '</form>';



echo $output;

?>
