<?php
/**
* bookmarks form/edit script
*
* @package    bookmarks
* @module     main
* @author     Albrecht Guenther, $Author: gustavo $
* @licence    GPL, see www.gnu.org/copyleft/gpl.html
* @copyright  2000-2006 Mayflower GmbH www.mayflower.de
* @version    $Id: bookmarks_forms.php,v 1.30 2006/11/07 00:28:20 gustavo Exp $
*/
if (!defined('lib_included')) die('Please use index.php!');

// check role
if (check_role("bookmarks") < 2) die("You are not allowed to do this!");


// tabs
$tabs   = array();
$tmp    = get_export_link_data('bookmarks', false);
$tabs[] = array('href' => $tmp['href'], 'active' => $tmp['active'], 'id' => 'tab4', 'target' => '_self', 'text' => $tmp['text'], 'position' => 'right');

// form start
$hidden = array('mode'=>'data', 'mode2'=>'bookmarks', 'ID'=>$ID);
foreach ($view_param as $key => $value) {
    $hidden[$key] = $value;
}
if (SID) $hidden[session_name()] = session_id();
$buttons = array();
$buttons[] = array('type' => 'form_start', 'hidden' => $hidden, 'onsubmit' => 'return chkForm(\'frm\',\'url\',\''.__('Please specify a description!').'!\');', 'name' => 'frm');

$output = '<div id="global-header">';
$output .= get_tabs_area($tabs);
$output .= breadcrumb($module, array(array('title'=>($ID>0) ? slookup('lesezeichen','bezeichnung','ID', $ID,'1') : __('New') )));
$output .= '</div>';
$output .= get_buttons($buttons);

// button bar
$buttons = array();
if ($ID > 0) {
    $buttons[] = array('type' => 'submit', 'name' => 'modify_b', 'value' => __('OK'), 'active' => false);
    $buttons[] = array('type' => 'submit', 'name' => 'modify_update_b', 'value' => __('Apply'), 'active' => false);
    $buttons[] = array('type' => 'link', 'href' => 'bookmarks.php', 'text' => __('List View'), 'active' => false);
    $buttons[] = array('type' => 'submit', 'name' => 'loeschen', 'value' => __('Delete'), 'active' => false, 'onclick' => 'return confirm(\''.__('Are you sure?').'\');');
}
else {
    $buttons[] = array('type' => 'submit', 'name' => 'create_b', 'value' => __('OK'), 'active' => false);
    $buttons[] = array('type' => 'submit', 'name' => 'create_update_b', 'value' => __('Apply'), 'active' => false);
    $buttons[] = array('type' => 'link', 'href' => 'bookmarks.php', 'text' => __('List View'), 'active' => false);
}
$output .= '<div id="global-content">';
$output .= get_buttons_area($buttons);

if ($ID > 0) {
    $result = db_query("SELECT ID, datum, von, url, bezeichnung, bemerkung, gruppe
                          FROM ".DB_PREFIX."lesezeichen
                         WHERE ID = ".(int)$ID) or db_die();
    $row = db_fetch_row($result);
    if ($row[0]) {
        $row[3] = stripslashes($row[3]);
        $row[4] = stripslashes($row[4]);
        $row[5] = stripslashes($row[5]);

        // mark that the user has touched the record
        touch_record('bookmarks', $ID);
    }
}

/*******************************
*       basic fields
*******************************/
$form_fields   = array();
$form_fields[] = array('type' => 'text', 'class' => 'halfsize', 'name' => 'url', 'label' => __('URL').__(':'), 'value' => html_out($row[3]));
$form_fields[] = array('type' => 'text', 'class' => 'halfsize', 'name' => 'bezeichnung', 'label' => __('Description').__(':'), 'value' => html_out($row[4]));
$form_fields[] = array('type' => 'textarea', 'class' => 'halfsize', 'name' => 'bemerkung', 'label' => __('Comment').__(':'), 'value' => html_out($row[5]));
$basic_fields  = get_form_content($form_fields);

$output .= '
<br />
    <a name="content"></a>
    <a name="oben" id="oben"></a>
    <fieldset>
    <legend>'.__('Basis data').'</legend>
    '.$basic_fields.'
    </fieldset>

</form>
';

echo $output;

?>
