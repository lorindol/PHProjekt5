<?php
/**
* provides form for import operation
*
* @package    contacts
* @module     import
* @author     Albrecht Guenther, $Author: nina $
* @licence    GPL, see www.gnu.org/copyleft/gpl.html
* @copyright  2000-2006 Mayflower GmbH www.mayflower.de
* @version    $Id: contacts_import_forms.php,v 1.38 2006/11/16 13:13:10 nina Exp $
*/
if (!defined('lib_included')) die('Please use index.php!');

// import routines are available for the following adress books:
$abooks = array('vcard'=>'vcard','oe'=>'Outlook Express','outlook'=>'Outlook','kde3'=>'KDE 3 Adressbook','other'=>'Other List');

//tabs
$tabs = array();
$outputimf = '<div id="global-header">';
if($import_contacts <> '' and $import_contacts != "import_contacts"){
    // form start
    $hidden = array('import' => 1, 'ID' => $ID, 'action' => $action);
    foreach($view_param as $key => $value){
        $hidden[$key] = $value;
    }
    if(SID) $hidden[session_name()] = session_id();
    $buttons = array();
    $buttons[] = array('type' => 'form_start', 'hidden' => $hidden, 'enctype' => 'multipart/form-data', 'name' => 'frm', 'onsubmit' => 'return chkForm(\'frm\',\'userfile\',\''.__('Please specify a description!').'!\');');
    $outputimf.=get_buttons($buttons);
}
$buttons = array();
$outputimf .= get_tabs_area($tabs);
$outputimf .= breadcrumb($module, breadcrumb_data($action, 'Import'));

if(!($import_contacts <> '' and $import_contacts != "import_contacts")) {
    // form start
    $hidden = array('mode' => 'import_forms', 'ID' => $ID, 'action' => $action);
    foreach($view_param as $key => $value){
        $hidden[$key] = $value;
    }
    if(SID) $hidden[session_name()] = session_id();
    $buttons[] = array('type' => 'form_start', 'hidden' => $hidden);

    //$but[] = array('type' => 'form_start', 'hidden' => $hidden);
    //$outputimf .= get_buttons($but);


    // button bar

    $buttons[] = array('type' => 'text', 'text' => __('Import'));

    $select_field = '
    &nbsp;
    <select name="import_contacts" onchange="this.form.submit();">
    <option value="import_contacts">'.__('Please select!').'</option>
    ';
    foreach($abooks as $abook1 => $abook2) {
        $select_field .= '<option value="'.$abook1.'"';
        if ($import_contacts == $abook1){
            $select_field .= 'selected="selected"';
        }
        $select_field .= '>'.$abook2.'</option>';
    }
    $select_field .= '
    </select></span>
    <noscript>
    '.get_go_button().'</noscript><span class="nav_area">';
    $buttons[] = array('type' => 'text', 'text' => $select_field);

    $buttons[] = array('type' => 'submit', 'name' => 'create', 'value' => __('OK'), 'active' => false);

    $buttons[] = array('type' => 'form_end');
}
else {
    $buttons[] = array('type' => 'submit', 'name' => 'create', 'value' => __('OK'), 'active' => false);
}
$buttons[] = array('type' => 'link', 'href' => 'contacts.php?action=contacts&amp'.$sid, 'text' => __('List View'), 'active' => false);
// form end


$outputimf .= '</div>';
$outputimf .= $content_div;
$outputimf .= get_buttons_area($buttons);
$outputimf .= '
    <br />
    <div class="inner_content">
    <a name="content"></a>
    ';


if($import_contacts <> '' and $import_contacts != "import_contacts"){
    $outputimf .='
            <fieldset>
            <legend>1. '.__('Add contact file and select format').' '.__('(Step 1 of 3)').'</legend>
        ';


    $x="<br /><br /><label class='options' for='kategorie'>".__('Category').": </label><input type='text' name='kategorie' id='kategorie' size='20' maxlength='30' /><br />";
    switch($import_contacts){
        case "vcard":
            $outputimf.= "<br /><label for='userfile' class='options'>".__('Please select a vcard (*.vcf)').":</label> ";
            break;
        case "oe":
            $outputimf.= __('Howto: Open your outlook express address book and select file/export/other book<br />Then give the file a name, select all fields in the next dialog and finish')."<br /><hr noshade='noshade' size='1' />\n";
            $outputimf.="<br /><label for='userfile' class='options'>".__('Please choose an export file (*.csv)').": </label>\n";
            break;
        case "outlook":
            $outputimf.= __('Open outlook at file/export/export in file,<br />choose comma separated values (Win), then select contacts in the next form,<br />give the export file a name and finish.')."<br /><hr noshade='noshade' size='1' />\n";
            $outputimf.= "<br /><label for='userfile' class='options'>".__('Please choose an export file (*.csv)').":</label> \n";
            break;
        case "kde3":
            $outputimf.= "<br /><label for='userfile' class='options'>".__('Please select a file (*.csv)').":</label> ";
            break;
        case "other":
            // explanation of the procedure
            $outputimf.= __('Please export your address book into a comma separated value file (.csv), and either<br />1) apply an import pattern OR<br />2) modify the columns of the table with a spread sheet to this format<br />(Delete colums in you file that are not listed here and create empty colums for fields that do not exist in your file):')."<br />";
            // list here all active fields
            foreach($fields as $field_name => $field) { $outputimf.= enable_vars($field['form_name']).', '; }
            $outputimf.= "<br /><img src='".IMG_PATH."/s.gif' width='300' height='1' vspace='3' alt='separation line' /><br />\n";
            $outputimf.="<br /><label for='userfile' class='options'>".__('Please select a file (*.csv)').":</label>\n";
            $x="";
            //
            break;
    }
    $outputimf.= "<input type='hidden' name='mode' value='data' />\n";
    $outputimf.= "<input type='hidden' name='import_contacts' value='$import_contacts' />\n";
    // file upload element
    $outputimf.="<input type='file' name='userfile' id='userfile' size='20' />\n";
    // some remarks - depending on the import source
    $outputimf.=$x;
    // select field delimiter - only in case of 'other format'
    if ($import_contacts == 'other') $outputimf.= "<br /><br />
    <label class='options' for='csv_field_delimiter'>".__('Field separator').":</label>
    <select class='options' name='csv_field_delimiter' id='csv_field_delimiter'><option value='0'>,
    </option><option value='1'>;</option></select><br />\n";

    // apply import patterns if contacts_profiles are enabled and cas 'other list'
    if (PHPR_CONTACTS_PROFILES and $import_contacts == 'other') {
        $outputimf.="<label class='options' for='apply_pattern'>".__('Apply import pattern').'<br />'
        . '('. __('Go to the').'&nbsp;<a href="#edit_pattern">'.__('bottom').'</a>&nbsp;'.__('to create or modify a new or existent pattern').")</label>
      <select class='options' name='apply_pattern' id='apply_pattern'><option value=''></option>\n";
        $result = db_query("select ID, name
                            from ".DB_PREFIX."contacts_import_patterns
                           where von = ".(int)$user_ID." 
                        order by name") or db_die();
        while ($row = db_fetch_row($result)) {
            $outputimf.="<option value='$row[0]'>$row[1]</option>\n";
        }
        $outputimf.= "</select>\n";

        // checkbox to mark first line as header
        $outputimf.= "<br /><label class='options' for='isheader'>".__('First row is header')."</label>
      	<input class='options' type='checkbox' checked='checked' name='isheader' value='1'>\n";
    }



    $outputimf .='
            </fieldset>
            <br style="clear:both"/><br />
            <fieldset>
              <legend>2. '.__('Assignment').' '.__('(Step 2 of 3)').'</legend>

        ';


    $outputimf.= '<div class="formbody">';
    // access mode
    include_once(LIB_PATH.'/access_form.inc.php');
    $outputimf.= "<br />".access_form2(0, 0, 0, 0, 1)."\n";
    // end of form and submit icon
    $outputimf.= "<br style='clear:both'/><br />\n</div>\n";
    $outputimf .='
            </fieldset>
            <br style="clear:both"/><br />
            <fieldset>
            <legend>3. '.__('Doublets').' '.__('(Step 3 of 3)').'</legend>

        ';

    // check for double entries
    $outputimf.=  "<label class='options' for='doublet_check'>".__('Check for duplicates during import').' '.__('(let unchecked to ignore duplicates)')."</label>
    <input type='checkbox' name='doublet_check' id='doublet_check' /><br /><br />";
    $outputimf.=  "<label class='options' for='doublet_fields'>".__('Fields to match')."</label>
     <select class='options' name='doublet_fields[]' id='doublet_fields' multiple='multiple' size='5'>\n";
    foreach ($doublet_fields_all as $db_name => $field_name) {  $outputimf.=  "<option value='$db_name'>$field_name</option>"; }
    $outputimf.=  "</select><br /><br />";
    $outputimf.=  "<label class='options' for='doublet_action'>".__('Action for duplicates')."</label> <select class='options' name='doublet_action' id='doublet_action'>\n";
    $outputimf.=  "<option value='discard'>".__('Discard duplicates')."</option> <option value='dispose_child'>".__('Dispose as child');
    $outputimf.= "</option><option value='replace'>".__('Use doublet')."</option> </select>";
    $outputimf.= "</fieldset>\n";
    $outputimf .='';

    $outputimf .= get_buttons_area($buttons);

    $outputimf .='</form>
            <br style="clear:both"/><br />

        ';
}


// additional mask: administrate import patterns
if ($import_contacts == 'other') {
    $outputimf .= '
    <div class="boxHeader">'.__('Import pattern').' - '.__('Create or modify an existing pattern for CSV files').'</div>
    <a name="edit_pattern"></a>
    <div class="boxContent">
    ';
    $outputimf.= "<fieldset>";
    $outputimf.= "<form action='contacts.php' method='post' enctype='multipart/form-data' name='frm_pattern'>\n";
    $outputimf.= "<input type='hidden' name='mode' value='import_patterns' />\n";
    // upload example file
    $outputimf.="<label class='options' for='userfile2'>".__('For modification or creation upload an example csv file').":</label>
     <input type='file' name='userfile' id='userfile2' size='12' class='options'  /><br /><br />\n";
    $outputimf.="<label class='options' for='csv_field_delimiter'>".__('Field separator').":</label>
   <select name='csv_field_delimiter' class='options' ><option value='0'>,</option><option value='1'>;</option></select>\n";
    $outputimf.="<br /><br /><div align='center'>";
    $outputimf .= get_buttons(array(array('type' => 'submit', 'name' => 'neu', 'value' => __('New'), 'active' => false, 'onclick' => "return chkForm('frm_pattern','userfile2','".__('Please upload an example of csv file')."')")));
    $outputimf.="&nbsp;&nbsp;".__('or')."&nbsp;&nbsp;  \n";
    $outputimf.= "<select name='ID' class='field_setting' ><option value=''></option>";
    $result = db_query("select ID, name
                      from ".DB_PREFIX."contacts_import_patterns
                     where von ='$user_ID'") or db_die();
    while ($row = db_fetch_row($result)) { $outputimf.= "<option value='$row[0]'>$row[1]</option>\n";}
    $outputimf.= "</select>&nbsp;&nbsp;";
    if(SID)  $outputimf.="<input type='hidden' name='".session_name()."' value='".session_id()."' />\n";
    $outputimf .= get_buttons(array(array('type' => 'submit', 'name' => 'aendern', 'value' => __('Modify'), 'active' => false, 'onclick' => "return chkForm('frm_pattern','userfile2','".__('Please upload an example of csv file')."','ID','".__('Select a pattern to modify')."')")));
    $outputimf .= get_buttons(array(array('type' => 'submit', 'name' => 'loeschen', 'value' => __('Delete'), 'active' => false, 'onclick' => 'return confirm(\''.__('Are you sure?').'\');')));
    $outputimf.= "</div></form></fieldset>\n"; ;

    $outputimf .= '
    </div>
    <br style="clear:both"/><br />
    ';

}

echo $outputimf;
?>
