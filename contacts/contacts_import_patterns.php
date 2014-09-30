<?php
/**
* import patters for contacts for multiple import from the same source
*
* @package    contacts
* @module     import
* @author     Albrecht Guenther, $Author: gustavo $
* @licence    GPL, see www.gnu.org/copyleft/gpl.html
* @copyright  2000-2006 Mayflower GmbH www.mayflower.de
* @version    $Id: contacts_import_patterns.php,v 1.27.2.1 2007/01/13 15:00:44 gustavo Exp $
*/
if (!defined('lib_included')) die('Please use index.php!');

// check role
if (check_role("contacts") < 1) die("You are not allowed to do this!");

// array for field_delimiters
$delimiters = array(0=>',',1=>';');

if ($make <> '') {
    // flag for forms
    $import_contacts = 'other';
    // insert new record
    if ($make == "neu") {
        $result = db_query("INSERT INTO ".DB_PREFIX."contacts_import_patterns
                                   (  name ,                 von,                        pattern           )
                            VALUES ('".xss($name)."',".(int)$user_ID.",'".serialize(xss($pattern_field))."')") or db_die();
    }
    // update existing record
    else {
        $result = db_query("UPDATE ".DB_PREFIX."contacts_import_patterns
                               SET name = '".xss($name)."',
                                   pattern = '".serialize(xss($pattern_field))."'
                             WHERE ID = ".(int)$ID) or db_die();
    }
    $import="yes";
    include_once('./contacts_import_forms.php');
}
elseif ($loeschen) {
    $result = db_query("DELETE
                          FROM ".DB_PREFIX."contacts_import_patterns
                         WHERE ID = ".(int)$ID) or db_die();
    include_once('./contacts_import_forms.php');
}

// form
if (!$make) {
    //tabs

    $tabs = array();
    $outputex .= '<div id="global-header">';
    $outputex .= get_tabs_area($tabs);
    $outputex .= breadcrumb($module, breadcrumb_data($action, 'Import'));
    // button bar
    $buttons = array();
    $outputex .= '</div>';
    $outputex .= $content_div;
    $hidden = array('mode' => 'import_patterns', 'ID' => $ID, 'action' => $action);
    if(SID) $hidden[session_name()] = session_id();
    if ($aendern <> '')  {
        $hidden['make'] = 'aendern';
    }
    else  {
        $hidden['make'] = 'neu';
    }
    $buttons[] = array('type' => 'form_start', 'hidden' => $hidden, 'enctype' => 'multipart/form-data', 'name' => 'frm', 'onsubmit' => 'return chkForm(\'frm\',\'name\',\''.__('Please fill in the following field').': name '.__('Name').'\');');
    $buttons[] = array('type' => 'submit', 'name' => 'create', 'value' => __('OK'), 'active' => false);

    $buttons[] = array('type' => 'link', 'href' => "contacts.php?mode=import_forms&amp;import_contacts=other{$sid}", 'text' => __('List view'), 'active' => false);

    $outputex .= get_buttons_area($buttons);
    $outputex.="<form action='contacts.php' method='post' name=frm onSubmit=\"return chkForm('frm','name','".__('Please fill in the following field').": name ".__('Name')."')\">\n";
    if ($aendern <> '') {
        // fetch values
        $result = db_query("SELECT pattern, von, name
                              FROM ".DB_PREFIX."contacts_import_patterns
                             WHERE ID = ".(int)$ID) or db_die();
        $row = db_fetch_row($result);
        // check permission - the hacker doesn't deserve any message!
        if ($row[1] <> $user_ID) {
            echo "<br /><br /><b>".__("Pattern ID to be edited not found!")."<br /><a href='contacts.php?mode=forms&amp;import=other".$sid."'>".__('Cancel')."</a></b><br /><br />\n";
            exit;
        }
        // convert serialized pattern to array
        $pattern = unserialize($row[0]);
    }

    // title
    $outputex.='<br /><fieldset><legend>'.__('Import pattern')."</legend>\n";
    // name
    $outputex.="<br /><label for='name' class='options'>".__('Name').":</label> <input type='text' name='name' value='$row[2]' size='30' maxlength='40' /><br style='clear:both;'/><br />\n";
    // list headers
    $fp = fopen($userfile, "r");
    if (!$fp) {
        echo "<br /><br /><b>".__("Please upload a file!")."<br /><a href='contacts.php?mode=forms&amp;import=other".$sid."'>".__('Cancel')."</a></b><br /><br />\n";
        exit;
    }
    $outputex.="<table class='ruler'><thead>\n";
    $outputex.="<tr><th>Position (Value of imported list)</th>\n<th>Reference to contacts table</th></tr></thead><tbody>\n";
    // fetch array of available fields
    include_once('../lib/dbman_lib.inc.php');

    $a = fgetcsv($fp, 4000, "$delimiters[$csv_field_delimiter]");
    for ($i=0; $i<count($a);$i++) {
        $outputex.="<tr><td>".__('Position')." <b>$i</b> (".$a[$i].")</td>\n";
        $outputex.="<td><select name='pattern_field[]'><option value='void'>".__('Skip field')."</option>\n";
        // display all available fields
        foreach ($fields as $field_name => $field) {
            $outputex.="<option value='$field_name'";
            if ($field_name == $pattern[$i]) $outputex.=" selected='selected'";
            $outputex.=">".enable_vars($field['form_name'])."</option>\n";
        }
        $outputex.="</select></td></tr>\n";
    }

    $butsub[]=array('type' => 'submit', 'name' => 'create', 'value' => __('OK'), 'active' => false);
    $butsub[] = array('type' => 'link', 'href' => "contacts.php?mode=import_forms&amp;import_contacts=other{$sid}", 'text' => __('List view'), 'active' => false);
    $outputex.='</tbody></table></fieldset>'.get_buttons($butsub);
    $outputex.="</form>\n";
    echo $outputex;
}

?>
