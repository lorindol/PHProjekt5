<?php

// votum_forms.php - PHProjekt Version 5.2
// copyright  ©  2000-2005 Albrecht Guenther  ag@phprojekt.com
// www.phprojekt.com
// Author: Albrecht Guenther, $Author: albrecht $
// $Id: votum_forms.php,v 1.36.2.1 2007/04/11 16:52:43 albrecht Exp $

// check whether lib.inc.php has been included
if (!defined("lib_included")) die("Please use index.php!");

// check role
if (check_role("votum") < 2) die("You are not allowed to do this!");


// tabs
$tabs = array();

// form start
$buttons = array();
$hidden  = array('action' => 'new', 'mode' => 'data');
if (SID) $hidden[session_name()] = session_id();
$buttons[] = array('type' => 'form_start', 'hidden' => $hidden, 'onsubmit' => 'return chkForm(\'frm\',\'thema\',\''.__('Please specify a description!').'!\')', 'name' => 'frm');

$output = '<div id="global-header">';
$output .= get_tabs_area($tabs);
$output .= breadcrumb($module, array(array('title'=>__('New'))));
$output .= '</div>';
$output .= get_buttons($buttons);

// button bar
$buttons = array();

$buttons[] = array('type' => 'submit', 'name' => 'submit', 'value' => __('OK'), 'active' => false);
$buttons[] = array('type' => 'link', 'href' => 'votum.php', 'text' => __('Cancel'), 'active' => false);
$output .= '<div id="global-content">';
$output .= get_buttons_area($buttons);

/*******************************
*       basic fields
*******************************/
$form_fields = array();
$form_fields[] = array('type' => 'text', 'name' => 'text1', 'class' => 'halfsize', 'label' => __('Text').' 1'.__(':'), 'value' => '');
$form_fields[] = array('type' => 'text', 'name' => 'text2', 'class' => 'halfsize', 'label' => __('Text').' 2'.__(':'), 'value' => '');
$form_fields[] = array('type' => 'text', 'name' => 'text3', 'class' => 'halfsize', 'label' => __('Text').' 3'.__(':'), 'value' => '');


$html = '

<label for="modus" class="label_block">'.__('Alternatives').__(':').'</label>

<fieldset>
<input type="radio" name="modus" id="modus" value="r" checked="checked" /> '.__('just one <b>Alternative</b> or').'<br />
<input type="radio" name="modus" value="c" /> '.__('several to choose?').'<br /><br />
'.get_form_content($form_fields).'
</fieldset>
';

$form_fields = array();
$form_fields[] = array('type' => 'text', 'name' => 'thema', 'class' => 'fullsize', 'label' => __('Question:'), 'value' => '');
$form_fields[] = array('type' => 'parsed_html', 'html' => $html);

$basic_fields = get_form_content($form_fields);



/*******************************
*    participants fields
*******************************/
$form_fields = array();
$html = '';

// manual selection

    $query = "SELECT u.ID, nachname, vorname, role
                FROM ".DB_PREFIX."users u, ".DB_PREFIX."grup_user
               WHERE grup_ID = ".(int)$user_group." 
                 AND user_ID = u.ID
                 AND u.status = 0
                 AND u.usertype <> 1
            ORDER BY nachname";

$result2 = db_query($query) or db_die();
while ($row2 = db_fetch_row($result2)) {
    // only show these users which are allowed to take part in a vote
    $result = db_query("SELECT ".DB_PREFIX."roles.ID, votum
                          FROM ".DB_PREFIX."roles, ".DB_PREFIX."users
                         WHERE role=".DB_PREFIX."roles.ID") or db_die();
    $row = db_fetch_row($result);
    if (!$row[0] or $row[1] > 0) {
        $html .= "<input type='checkbox' name='s[]' value='$row2[0]' /> $row2[1], $row2[2]\n";
    }
}

// profiles
$html .= "&nbsp;-&nbsp;".__('or profile').": <select name='profil'>\n";
$html .= "<option value='0'></option>\n";
$query = "SELECT ID, bezeichnung, von
            FROM ".DB_PREFIX."profile
           WHERE (acc LIKE 'system'
                  OR ((von = ".(int)$user_ID." OR acc LIKE 'group' OR acc LIKE '%\"$user_kurz\"%')
                      AND $sql_user_group))
        ORDER BY bezeichnung";
$result = db_query($query) or db_die();
while ($row = db_fetch_row($result)) {
    $von = str_replace(',', ', ', slookup('users', 'nachname,vorname', 'ID', $row[2],'1'));
    $html .= "<option value='$row[0]'>".html_out($row[1])." ($von)</option>\n";
}
$html .= "</select>\n<br />\n";

$form_fields[] = array('type' => 'parsed_html', 'html' => $html);
$participants_fields = get_form_content($form_fields);

$output .= '
<br />
<div class="inner_content">
    <a name="content"></a>
    <fieldset>
    <legend>'.__('Basis data').'</legend>
    '.$basic_fields.'
    </fieldset>

    <fieldset>
    <legend>'.__('Participants:').'</legend>
    '.$participants_fields.'
    </fieldset>
</div>
';
$output .= '</div>';
echo $output;

?>
