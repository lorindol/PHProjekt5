<?php

// dbman_forms.inc.php - PHProjekt Version 5.2
// copyright  ©  2000-2005 Albrecht Guenther  ag@phprojekt.com
// www.phprojekt.com
// Author: Albrecht Guenther, $Author: polidor $
// $Id: dbman_forms.inc.php,v 1.115.2.5 2007/07/24 16:04:56 polidor Exp $


// check whether the lib has been included - authentication!
if (!defined('lib_included')) die('Please use index.php!');

// output of all elements of a form, uses the function form_element to display the different types of form elements like text, textarea  etc.
function build_form($fields) {
    global $js_html_textarea, $module;
    $field_pos = 'left';
    $rowspan_stack = array();
    $row_count = 0;
    //prints all the fields;
    $output = '<br style="clear:both"/><table>
    <colgroup>
      <col width="410"/>
      <col width="410"/>
    </colgroup>';
    foreach ($fields as $field_name => $field) {
        if($field_pos == 'left'){
            $output .= '<tr>';

            if($field['form_colspan'] == '2'){

                $field['size'] = 'fullsize';
                $output .= '<td colspan="2">'.form_element($field, $field_name, false).'</td></tr>';
                $field_pos = 'left';
            }
            else{
                $field['size'] = 'halfsize';
                $output .= '<td style="vertical-align:top">'.form_element($field, $field_name, false).'</td>';
                $field_pos = 'right';
            }
        }
        else{ // $field_pos == 'right'

            if($field['form_colspan'] == '2' or $field['form_type']=='textarea'){
                $field['size'] = 'fullsize';
                $output .= '<td></td></tr><tr><td colspan="2">'.form_element($field, $field_name, false).'</td></tr>';
                $field_pos = 'left';
            }

            else{

                $field['size'] = 'halfsize';
                $output .= sprintf('<td%s style="vertical-align:top">'.form_element($field, $field_name, false).'</td></tr>', $rowspan);
                $field_pos = 'left';
            }

        }
    }
    if($field_pos == 'right'){
        $output .= '<td></td></tr>';
    }
    $output .= '</table>';
    // check for textarea with html editor
    if ($_SESSION['show_html_editor'][$module] == 1 and is_array($js_html_textarea)) {
        $output .= "<script type=\"text/javascript\">window.onload = function() {\n";
        foreach ($js_html_textarea as $f) {
            $output .= "newFCKeditor('".$f."','".PATH_PRE."');\n";
        }
        $output .=  '}</script>';
    }
    return $output;
}

// foreach defined form element type the html snippet
function form_element($field, $field_name, $force_newline) {
    global $ID, $read_o, $user_ID, $contact_ID, $js_html_textarea;
    global $projekt_ID, $file_ID, $selected, $module;
    global $date_format_object,$getstring;

    // if it's a new record, give the default value predefined in the db
    if (!$ID) {
        if (empty($field['value'])) $field['value'] = enable_vars($field['form_default']);
    }
    $field['value'] = stripslashes(xss($field['value']));

    $output1 = '';

    $label_style = 'label_block';
    $field_size = isset($field['size']) ? $field['size'] : ($field['form_colspan'] == '2' ? 'fullsize' : 'halfsize');
// @@todo
if(defined('IN_DEV_MODE') && !in_array($field['form_type'], array('textarea','text','email','phone','url','select_category','select_values','date','contact_create','contact','project','user_show','timestamp_modify','userID','timestamp_create','display', 'authorID', 'select_sql', 'upload', 'timestamp_show'))){
    echo '<script>document.body.innerHTML="fieldtype not implemented: '.$field['form_type'].'"</script>';
}
    switch ($field['form_type']) {

        case 'timestamp_show':
            $output1 .= sprintf('<label class="%s" for="%s">%s</label>%s',
                            $label_style,
                            $field_name,
                            enable_vars($field[form_name]),
                            $field['value'] <> '' ? $date_format_object->convert_dbdatetime2user($field['value']) : '-'
                        );
            break;

        // skip display on creation, onlyshow value at modification
        case 'timestamp_create':
            $html = $date_format_object->convert_dbdatetime2user($field['value']);
            $output1 .= sprintf('<label class="%s" for="%s">%s</label>%s',
                                $label_style,
                                $field_name,
                                enable_vars($field[form_name]),
                                strlen($html) ? $html : '-'
                              );
            break;

        case 'timestamp_modify':
            if ($ID > 0) {
                $html = $date_format_object->convert_dbdatetime2user($field['value']);
                $output1 .= sprintf('<label class="%s">%s</label>%s',
                                $label_style,
                                enable_vars($field[form_name]),
                                $html
                            );
            }
            break;

        // display: no chance to edit this, just plain view
        case 'display':
            $output1 .= sprintf('<label class="%s" for="%s">%s</label>%s',
                                $label_style,
                                $field_name,
                                enable_vars($field[form_name]),
                                $field['value']
                              );
            break;

        // display: value taken out of a foreign table via sql
        case 'display_sql':
            $result = db_query(enable_vars($field['form_select']));
            $row = array();
            if($result) $row = db_fetch_row($result);
            $output1.= "<label class='form$ff' for='$field_name'>";
            $output1.= enable_vars($field[form_name])."</label><div class='form_div'";
            if ($field['form_tooltip'] <> '') { $output1.= " title='".$field['form_tooltip']."'"; }
            $output1.= '>'.implode(' ',$row);
            $output1.= "</div>\n";
            break;

        // more complex string, even with calculations
        case 'display_string':
            $output1.= "<label class='$label_style' for='$field_name'>";
            $output1.= enable_vars($field[form_name])."</label><div class='form_div'>";
            $output1.= show_string_list($field['form_select'],$GLOBALS['fields'],'','');
            $output1.= "</div>\n";
            break;

        // text-create means that the user can only insert a text at the time he craetes the record but cannot modify the value later
        case 'text_create':
            $output1.= "<label class='$label_style' for='$field_name'>";
            $output1.= enable_vars($field[form_name])."</label>";
            if (!$ID) {
                $output1.= "<input type='text' size='".set_size($field)."' name='". $field_name."' value='".$field['value']."'";
                if ($field['form_tooltip'] <> '') { $output1.= " title='".$field['form_tooltip']."'"; }
                $output1.= read_o($read_o)." ".build_regexp($field['regexp'], $field_name)."/>";
            }
            else $output1.="<div class='form_div'> $field[value]</div>";
            $output1.= "\n";
            break;

        // if the value is empty, the next fieldtype fetches the value from the next non-empty parent record
        case 'text_parent_value':
            $output1.= "<label class='$label_style' for='$field_name'>";
            $output1.= enable_vars($field[form_name])."</label>"; echo $field['parent'];
            // fetch value from parent - search until you find a content
            while (!$field['value'] and $field['parent'] > 0) {
                $result = db_query("select ".$field_name." from ".$module." where ID = ".(int)$field['parent']);
                if($result) $row = db_fetch_row($result);
            }

            $output1.= " <input class='form$ff' id='$field_name' type='text' size='".set_size($field)."' name='". $field_name."' value='".$field['value']."'";
            if ($field['form_tooltip'] <> '') { $output1.= " title='".$field['form_tooltip']."'"; }
            $output1.= read_o($read_o)." ".build_regexp($field['form_regexp'], $field_name)." />\n";
            $output1.=" \n";
            break;


        // textarea - only add text,  no editing. previous text will be shown below
    	 case 'textarea_add_remark':
            // use html editor?
            if ($_SESSION['show_html_editor'][$module] > 0) {
                $textarea_id = 'id="'.$field_name.'"';
                $js_html_textarea[] = $field_name;
                $txt_value = nl2br($field['value']);
            }
            else {
                $textarea_id = 'id="'.$field_name.'"';
                $txt_value = nl2br(strip_tags(html_entity_decode($field['value'])));
            }
            $output1.= "<label class='formsmall' for='$field_name'>";
            $output1.= enable_vars($field[form_name])."</label>";
            $output1.= "<textarea class='form' ".set_size($field)." name='". $field_name."' ".$textarea_id;
            $output1.= read_o($read_o, 'readonly')."></textarea><br /><div style='margin-left:100pt;'>".$txt_value."</div>";
            $output1.= "\n";
            break;

        case 'textarea':
            // use html editor?
            if ($_SESSION['show_html_editor'][$module] > 0) {
                $js_html_textarea[] = $field_name;
                $txt_value = $field['value'];
            } else {
                $txt_value = strip_tags(html_entity_decode($field['value']));
            }
            $output1 .= sprintf('<label class="%s" for="%s">%s</label>
                                <textarea class="%s" name="%s" id="%s" %s %s>%s</textarea><p class="print">%s</p>',
                            $label_style,
                            $field_name,
                            enable_vars($field[form_name]),
                            $field_size,
                            $field_name,
                            $field_name,
                            ($field['form_tooltip'] <> '') ? ' title="'.$field['form_tooltip'].'"' : '',
                            read_o($read_o, 'readonly'),
                            $txt_value,
                            $txt_value
                        );
            break;

        // single checkbox - another 'no comment'
        case 'checkbox':
            $output1.= "<label class='$label_style' for='$field_name'>";
            $output1.= enable_vars($field[form_name])."</label>";
            $output1.= "<input style='width:15px;' type='checkbox' name='".$field_name."' value='yes'";
            if ($field['form_tooltip'] <> '') { $output1.= " title='".$field['form_tooltip']."'"; }
            if ($field['value'] <> '') $output1.= " checked";
            $output1.= read_o($read_o)." />\n";
            $output1.= "\n";
            break;

        // range of predefined, fixed values from the field 'form_select'
        case 'select_values':
            $options = '';
            foreach (explode('|', $field['form_select']) as $select_value) {
                // split the entry into key and value
                list($key,$value) = explode('#',$select_value);
                if (!$value) $value = $key;
                $options .= "<option value='".$key."'";
                if ($key == $field['value']) $options .= ' selected="selected"';
                // Fix the 0 value to -
                if ($value == '0') $value = '-';
                $options .= '>'.enable_vars($value)."</option>\n";
            }

            $output1 .= sprintf('<label class="%s" for="%s">%s</label>
                                <select class="%s" id="%s" name="%s"%s %s>
                                %s
                                </select>',
                            $label_style,
                            $field_name,
                            enable_vars($field[form_name]),
                            $field_size,
                            $field_name,
                            $field_name,
                            ($field['form_tooltip'] <> '') ? ' title="'.$field['form_tooltip'].'"' : '',
                            read_o($read_o),
                            $options
                        );
            break;

            // range of predefined, fixed values from the field 'form_select', text area to allow extra values
        case 'select_values_text':
            $output1.= "<label class='$label_style' for='$field_name'>";
            $output1.= enable_vars($field[form_name])."</label>";
            $output1.= "<select class='form$ff' id='$field_name'  name='". $field_name."'";
            if ($field['form_tooltip'] <> '') { $output1.= " title='".$field['form_tooltip']."'"; }
            $output1.= read_o($read_o);
            $output1.= " onchange='getElementById(\"{$field_name}_other\").value=\"\";'" ;
            $output1.= '>';
            $output1.= "<option value=''></option>\n";

            $found = false;
            foreach (explode('|', $field['form_select']) as $select_value) {
                // split the entry into key and value
                list($key,$value) = explode('#',$select_value);
                if (!$value) $value = $key;
                $output1.= "<option value='".$key."'";
                if ($key == $field['value']) {
                    $output1.= ' selected="selected"';
                    $found = true;
                }
                $output1.= '>'.enable_vars($value)."</option>\n";
            }
            $output1.= "</select>\n";
            $output1.= "<label class='$label_style; ' for='{$field_name}_other'>Other</label>";
            if (!$found) {
                $value = $field['value'];
            }
            else {
                $value = '';
            }
            $output1.= "<input type='text' class='form$ff;' id='{$field_name}_other'  name='{$field_name}_other' ";
            $output1.= read_o($read_o);
            $output1.= " value='$value' onchange='getElementById(\"{$field_name}\").value=\"\";' />\n";
            break;

        // multiple select
        case 'select_multiple':
            $output1.= "<label class='".$label_style."' for='$field_name'>";
            $output1.= enable_vars($field[form_name])."</label>";
            $output1.= "<select multiple='multiple' class='form$ff' size='".set_size($field)."' name='". $field_name."[]'";
            if ($field['form_tooltip'] <> '') { $output1.= " title='".$field['form_tooltip']."'"; }
            $output1.= read_o($read_o).'>';
            // move previously selected values into array
            $selected = explode('|',$field['value']);
            foreach (explode('|', $field['form_select']) as $select_value) {
                $output1.= "<option value='".$select_value."'";
                if (in_array($select_value,$selected))$output1.= ' selected="selected"';
                $output1.= '>'.$select_value."</option>\n";
            }
            $output1.= "</select>\n";
            break;

        // fetches the result of a sql statement and displays it in a select box
        case 'select_sql':
            $options = '';
            $result = db_query(enable_vars($field['form_select']));
            while ($row = db_fetch_row($result)) {
                $first_element = array_shift($row);
                $options .= "<option value='".$first_element."'";
                if ($first_element == $field['value']) $options .= ' selected="selected"';
                $options .= ">".implode(',',$row)."</option>\n";
            }
            $output1 .= sprintf('<label class="%s" for="%s">%s</label>
                                <select name="%s"%s %s>
                                %s
                                </select>',
                                    $label_style,
                                    $field_name,
                                    enable_vars($field[form_name]),
                                    $field_name,
                                    $field['form_tooltip'] <> '' ? ' title="'.$field['form_tooltip'].'"' : '',
                                    read_o($read_o),
                                    $options
                               );
            break;

        // for any kind of phone operation, kind of call system is set in the config.inc.php
        case 'phone':
            $output1 .= sprintf('<label class="%s" for="%s">%s</label>
                                <input class="%s" type="text" id="%s" name="%s" value="%s" %s %s/>',
                            $label_style,
                            $field_name,
                            ($ID > 0) ? 
                                "<a title='".__('This link opens a popup window')."' href=\"javascript:go_phone('".PHPR_CALLTYPE."','".$field['value']."');\">".enable_vars($field[form_name])."</a>"
                                :
                                enable_vars($field[form_name]),
                            $field_size,
                            $field_name,
                            $field_name,
                            $field['value'],
                            ($field['form_tooltip'] <> '') ? ' title="'.$field['form_tooltip'].'"' : '',
                            read_o($read_o)
                        );
            break;

        // combines the option to select an entry from previous records or to insert a new value
        case 'select_category':
            // define access rule
            $result = db_query("SELECT DISTINCT ".qss($field_name)."
                                           FROM ".qss(DB_PREFIX.$field['tablename'])."
                                          WHERE ".qss($field_name)." <> ''
                                            AND von = ".(int)$user_ID." 
                                       ORDER BY ".qss($field_name)) or db_die();
            $cats_all = array();
            while ($row = db_fetch_row($result)) { $cats_all[] = $row[0]; }
            // look whether the displayed value is in the list of previouse values. if not, add it.
            if (!in_array($field['value'],$cats_all)) array_unshift($cats_all,$field['value']);

            $options = '';
            foreach($cats_all as $cat_current) {
                $options .= sprintf('<option value="%s"%s>%s</option>',
                                $cat_current,
                                ($cat_current == $field['value']) ? ' selected="selected"' : '',
                                $cat_current
                            );
            }
            $output1 .= sprintf('<label class="%s" for="%s">%s</label>
                                <select class="%s" id="%s" name="%s" %s %s>
                                <option value=""></option>%s
                                </select>
                                %s <br /><label class="%s" for="%s">or new:</label><input class="%s" type="text" name="new_category"%s%s/>',
                            $label_style,
                            $field_name,
                            enable_vars($field[form_name]),
                            $field_size,
                            $field_name,
                            $field_name,
                            // @todo set background-color using css
                            ($read_o != 0) ? " background-color:".PHPR_BGCOLOR3."' disabled='disabled" : '',
                            ($field['form_tooltip'] <> '') ? ' title="'.$field['form_tooltip'].'"' : '',
                            $options,
                            $admin_texttext64,
                            $label_style,
                            'new_category',
                            $field_size,
                            ($read_o != 0) ? " background-color:".PHPR_BGCOLOR3."' disabled='disabled'" : '',
                            ($field['form_tooltip'] <> '') ? ' title="'.$field['form_tooltip'].'"' : ''
                        );
            break;

        case 'upload':
            $files=array();
            //possibility to save more than one file
            $files= explode('#',$field['value']);
            $output1 .= sprintf('<label class="%s" for="%s">%s</label>
                                <input type="file" name="%s"%s %s/>',
                                    $label_style,
                                    $field_name,
                                    enable_vars($field[form_name]),
                                    $field_name,
                                    empty($field['form_tooltip']) ? '' : " title='".$field['form_tooltip']."'",
                                    read_o($read_o)
                               );
            // link to file and red button to delete this file separately
            foreach ($files as $value){
                list($filename,$tempname) = explode('|',$value);
                if ($filename <> '') {
                    $rnd = rnd_string(9);
                    $file_ID[$rnd] = $value;
                    $output1.= "<br />(<a href='".PATH_PRE."lib/file_download.php?module=$module&amp;download_attached_file=".$rnd.$sid."' target=_blank>$filename</a> \n";
                    if (!$read_o) {
                        $output1.= "<a href='$module.php?mode=data&amp;delete_file=1&amp;ID=$ID&amp;file_field_name=".$field_name."&amp;".$getstring."'><img src='".IMG_PATH."/r.gif' height=7 alt='".__('Delete')."' border=0></a>";
                    }
                    $output1 .= ")\n";
                }
            }
            $_SESSION['file_ID'] =& $file_ID;
            break;

        case 'date':
            //  take predefined values from global space or set to 'today'
            if ($GLOBALS[$field_name] <> '') { $field['value'] = $GLOBALS[$field_name]; }
            $date_format_len = $date_format_object->get_maxlength_attribute();
            if ($field['form_tooltip'] <> '') {
                $date_format_title = $field['form_tooltip'];
            }
            else {
                $date_format_title = '';
            }
            // if dafult_value = 0000-00-00 then disable the dojo picker
            if (eregi('0000-00-00|null',$field['form_default']) and strlen($field['value'] < 6)) $dojo_datepicker_string = '';
            else $dojo_datepicker_string = dojoDatepicker($field_name, $field['value']);

            // determine default date
            // if (!$ID and $field['form_default'] == 'null') { $field['value'] = ''; }
            $output1 .= sprintf('<label class="%s" for="%s">%s</label>
                                 <input class="%s" type="text" %s name="%s" id="%s" %s %s/>',
                        $label_style,
                        $field_name,
                        enable_vars($field[form_name]),
                        $field_size,
                        $date_format_len,
                        $field_name,
                        $field_name,
                        $date_format_object->get_title_attribute($date_format_title),
                        read_o($read_o,'disabled','width:80px;').$dojo_datepicker_string
                        );
            break;

        case 'time':
            // split time into hours and minutes
            $hour   = substr($field['value'], 0, 2);
            $minute = substr($field['value'], 3, 5);
            // hours
            $hours='';
            for ($i=0; $i<=23; $i++) {
                if ($i<10) $i = '0'.$i;
                $hours.= "<option value='".$i."'";
                if ($i == $hour) $hours .= ' selected="selected"';
                $hours.= ">$i</option>\n";
            }
            // minutes     
            $minutes='';      
            for ($i=0; $i<=59; $i++) {
                if ($i<10) $i = '0'.$i;
                $minutes.= "<option value='".$i."'";
                if ($i == $minute) $minutes .= ' selected="selected"';
               $minutes.= ">$i</option>\n";
            }
            $output1 .= sprintf('<label class="%s" for="%s">%s</label>
                                                                
                                <select class="" id="%s" name="%s_hour" %s %s>
                                %s
                                </select>
                                <select class="" id="%s" name="%s_minute" %s %s>
                                %s
                                </select>',
                            $label_style,
                            $field_name,
                            enable_vars($field[form_name]),
                            $field_name,
                            $field_name,
                            // @todo set background-color using css
                            ($read_o != 0) ? " background-color:".PHPR_BGCOLOR3."' disabled='disabled" : '',
                            ($field['form_tooltip'] <> '') ? ' title="'.$field['form_tooltip'].'"' : '',
                            $hours,
                            $field_name,
                            $field_name,
                            // @todo set background-color using css
                            ($read_o != 0) ? " background-color:".PHPR_BGCOLOR3."' disabled='disabled" : '',
                            ($field['form_tooltip'] <> '') ? ' title="'.$field['form_tooltip'].'"' : '',
                            $minutes
                           
                        );
            break;

        // mail address - gives link zu mail reader
        case 'email':
            $output1 .= sprintf('<label class="%s" for="%s">%s</label>
                                <input class="%s" type="text" id="%s" name="%s" value="%s" %s %s/>',
                            $label_style,
                            $field_name,
                            ($ID > 0) ?
                                "<a href=\"javascript:mailto('email','".$field['value']."','".(SID? session_id() :"")."',".PHPR_QUICKMAIL.")\">".enable_vars($field[form_name])."</a>"
                                :
                                enable_vars($field[form_name]),
                            $field_size,
                            $field_name,
                            $field_name,                        
                            $field['value'],
                            ($field['form_tooltip'] <> '') ? ' title="'.$field['form_tooltip'].'"' : '',
                            read_o($read_o)
                       );
            break;

        // give the mail adress at creation of record
        case 'email_create':
            $output1.= "<label class='$label_style' for='$field_name'>";
            if ($ID > 0) {
                $output1.= "<a href=\"javascript:mailto(0,'".$field['value']."','".(SID? session_id() :"")."',".PHPR_QUICKMAIL.")\">".enable_vars($field[form_name])."</a>";
                $output1.= "</label><div class='form_div'>".$field['value']."</div>";
            }
            else {
                $output1.= enable_vars($field[form_name]);
                $output1.= "</label><input class='form$ff' type='text' size='".set_size($field)."' name='".$field_name."' value='".$field['value']."'";
                if ($field['form_tooltip'] <> '') { $output1.= " title='".$field['form_tooltip']."'".read_o($read_o); }
                $output1 .= " />";
            }
            $output1 .= "\n";
            break;

        // url - opens address in a new window
        case 'url':
            $output1 .= sprintf('<label class="%s" for="%s">%s</label>
                                <input class="%s" type="text" name="%s" id="%s" value="%s" %s %s/>',
                            $label_style,
                            $field_name,
                            ($ID > 0) ?
                                "<a title='".__('This link opens a popup window')."' href=\"javascript:go_web();\">".enable_vars($field[form_name])."</a>"
                                :
                                enable_vars($field[form_name]),
                            $field_size,
                            $field_name,
                            $field_name,
                            $field['value'],
                            ($field['form_tooltip'] <> '') ? ' title="'.$field['form_tooltip'].'"' : '',
                            read_o($read_o)
                        );
            break;

        // refers to an entry from the table 'contacts', selectable
        case 'contact':
        case 'contact_create':
            if (PHPR_CONTACTS) {
                // transmit the contact ID if the call comes from a foreign module
                if ($contact_ID > 0) $field['value'] = $contact_ID;
                // special hack for forms - if the contact ID is given, mark this one as selected
                $contact_ID > 0 ? $selected = $contact_ID : $selected = $field['value'];
                $html = 'id="'.$field_name.'" class="'.$field_size.'"';
                if ($field['form_tooltip'] <> '') { $html .= ' title="'.$field['form_tooltip'].'"'; }
                $html .= read_o($read_o);
                
                $output1 .= sprintf('<label class="%s" for="%s">%s</label>%s',
                                $label_style,
                                $field_name,
                                enable_vars($field[form_name]),
                                selector_create_select_contacts('contact', $selected, 'action_form_to_contact_selector', '0', $html)
                            );
            }
            break;

        // refers to an entry from the table 'project'
        case 'project':
            if (PHPR_PROJECTS) {
                // transmit the project ID if the call comes from a foreign module
                if ($projekt_ID > 0) $field['value'] = $projekt_ID;
                // special hack for forms - if the projekt ID is given, mark this one as selected
                $projekt_ID > 0 ? $selected = $projekt_ID : $selected = $field['value'];
                $html = 'id="'.$field_name.'" class="'.$field_size.'"';
                if ($field['form_tooltip'] <> '') { $html .= ' title="'.$field['form_tooltip'].'"'; }
                $html .= read_o($read_o);
                $output1 .= sprintf('<label class="%s" for="%s">%s</label>%s',
                                $label_style,
                                $field_name,
                                enable_vars($field[form_name]),
                                selector_create_select_projects($field_name, $selected, 'action_form_to_project_selector', '0', $html)
                            );
            }
            break;

        // author ID - store the name of the user which has written the record
        case 'authorID':
            $value = slookup('users','nachname,vorname','ID',$field['value'],'1');
            $output1 .= sprintf('<label class="%s" for="%s">%s</label>%s',
                            $label_style,
                            $field_name,
                            enable_vars($field[form_name]),
                            strlen($value) ? $value : '-'
                        );
            break;

        // handles the access to this field - if the userID equals the content of the given field, the user is able to edit this field, otherwise just display
        case 'userID_access':
            $output1.= "<span class='form$ff' for='$field_name'>";
            $output1.= enable_vars($field[form_name])."</label>";
            // check whether content(field) == userID
            if (slookup($field['tablename'],$field['form_select'],'ID',$ID,'1') == $user_ID) {
                $output1.= "<input type='text' class='form$ff' size='".set_size($field)."' id='$field_name' name='". $field_name."' value='".$field['value']."'";
                if ($field['form_tooltip'] <> '') { $output1.= " title='".$field['form_tooltip']."'"; }
                $output1.= read_o($read_o)." ".build_regexp($field['regexp'], $field_name)." />";
            }
            else $output1.= $field['value'];
            $output1.= "\n";
            break;

        // user ID - select an user of this group and store the ID
        case 'userID':
            $html = 'id="'.$field_name.'" class="'.$field_size.'"';
            if ($field['form_tooltip'] <> '') { $html .= ' title="'.$field['form_tooltip'].'"'; }
            $html .= read_o($read_o);
            $output1 .= sprintf('<label class="%s" for="%s">%s</label>%s',
                            $label_style,
                            $field_name,
                            enable_vars($field[form_name]),
                            selector_create_select_users($field_name,$field['value'], 'action_form_to_user_selector', '0', $html, '1', '0')
                        );
            break;

        // select Box on all users where the ID has been stored in this field
        case 'user_select_distinct':
            $output1.= "<label class='form$ff' for='$field_name'>";
            $output1.= enable_vars($field[form_name])."</label>";
            $output1.= "<select class='form$ff' name='". $field_name."'";
            if ($field['form_tooltip'] <> '') { $output1.= " title='".$field['form_tooltip']."'"; }
            $output1.= read_o($read_o).'><option value="0"></option>';
            $result = db_query("select ".DB_PREFIX."users.ID, ".DB_PREFIX."users.nachname, ".DB_PREFIX."users.vorname
                                  from ".DB_PREFIX."users, ".qss(DB_PREFIX.$field['tablename'])."
                                 where ".DB_PREFIX."users.ID = ".qss(DB_PREFIX.$field['tablename'].$field_name)."
                                   and ".DB_PREFIX."users.status = 0
                                   and ".DB_PREFIX."users.usertype = 0
                              group by ".DB_PREFIX."users.ID
                              order by nachname");
            while ($row = db_fetch_row($result)) {
                $output1.= "<option value='".$row[0]."'";
                if ($row[0] == $field['value']) $output1.= ' selected="selected"';
                $output1.= ">".$row[1].",".$row[2]."</option>\n";
            }
            $output1.= "</select>\n";
            break;


        // just display the user name
        case 'user_show':
            $output1 .= sprintf('<label class="%s">%s</label> %s',
                            $label_style,
                            enable_vars($field[form_name]).':',
                            $field['value'] > 0 ? slookup('users','nachname,vorname','ID',$field['value'],'1') : '---'
                        );
            break;

        // processes field values and formulas
        case 'formula':
            $output1.= "<label class='form$ff' for='$field_name'>";
            $output1.= enable_vars($field[form_name])."</label>";
            $output1.= "<div class='form_div'>";
            $output1.= build_formula($field['form_select']);
            $output1.= "</div>\n";
            break;

        // field element 'text' is default
        default:
            $output1 .= sprintf('<label class="%s" for="%s">%s</label>
                                 <input class="%s" id="%s" type="text" name="%s" value="%s"%s %s %s/>',
                            $label_style,
                            $field_name,
                            enable_vars($field[form_name]),
                            $field_size,
                            $field_name,
                            $field_name,
                            $field['value'],
                            ($field['form_tooltip'] <> '') ? ' title="'.$field['form_tooltip'].'"' : '',
                            read_o($read_o),
                            build_regexp($field['form_regexp'], $field_name)
                        );
            break;
    }

    return $output1;
}


// next two function below to case 'display_string'
// but provide values from other fields and formulas
function build_formula($content) {
    global $fields;
    foreach ($fields as $field_name => $field) {
        $content = ereg_replace($field_name,$field['value'], $content);
        // special hack on special request: replaces german title 'Herr ' with 'Herrn' :)
        $content = ereg_replace('Herr ','Herrn ', $content);
    }
    return preg_replace_callback("#\[(.*)\]#siU", 'f2', $content);
}

// Security: this does get called only if the dbmanager includes a field with
// type formula, and the value is taken from the select column.
// So this can (as of 18.07.05) only be exploited if you are able to enter the
// module designer, and it's not a security flaw by itself - only as a part
// of an exploit vector (admin privileges, sql injection)
function f2($f) {
    //eval('$y = '.$f[1].';');
    $y = $f[1];
    return $y;
}




// return size of input element - especially if we have a rowspan/colspan > 1
function set_size($field) {
    global $text_width;

    if (!PHPR_DEFAULT_SIZE) $default_size1 = 40;
    else $default_size1 = PHPR_DEFAULT_SIZE;

    if ($field['form_colspan'] > 1) {
        $default_size1 = $default_size1*$field['form_colspan']+$text_width/4*($field['form_colspan']-1);
    }

    // for some elements we have to calculate another size number for a nice table layout
    switch ($field['form_type']) {
        case 'upload':
            return ($default_size1 - 22);
            break;
        case 'textarea':
            $hor_size=floor($default_size1*0.7);
            if ($field['form_rowspan'] > 1) { $ver_size = $field['form_rowspan']*2 - 1; }
            else $ver_size = 2;
            return 'rows="'.$ver_size.'" cols="'.$hor_size.'"';
            break;
        case 'textarea_add_remark':
            $hor_size=floor($default_size1*0.7);
            if ($field['form_rowspan'] > 1) { $ver_size = $field['form_rowspan']*2 - 1; }
            else $ver_size = 2;
            return 'rows="'.$ver_size.'" cols="'.$hor_size.'"';
            break;
        case 'select_multiple':
            if ($field['form_rowspan'] > 1)  { $ver_size = $field['form_rowspan']*2 - 1; }
            else $ver_size = 2;
            return $ver_size;
            break;
        case 'select_category':
            return ($default_size1 - 20);
            break;
        default:
            if (!empty($field['form_length'])) {
                return $field['form_length'];
            } else {
                return $default_size1;
            }
            break;
    }
}


// adds the reagexp definition for the javascript check at submission of form
function build_regexp($regexp, $field_name) {
    if ($regexp <> '') {
        return "onblur=\"reg_exp('frm','".$field_name."','".__('Check the content of the previous field!')."',/".$regexp."/)\"";
    }
}


// *******
// buttons
function set_buttons($mode) {
    switch ($mode) {
        case 'create':
            return button_create().button_back();
            break;
        case 'modify':
            return button_modify().button_delete().button_back();
            break;
        case 'copy':
            return button_copy().button_back();
            break;
    }
}

function button_create() {
    return get_buttons(array(array('type' => 'submit', 'name' => 'create_b', 'value' => __('Create'), 'active' => false)));
}
function button_modify() {
    return get_buttons(array(array('type' => 'submit', 'name' => 'modify_b', 'value' => __('Modify'), 'active' => false)));
}
function button_delete() {
    global $ID, $module, $tablename;
    if (!slookup($tablename[$module],get_db_fieldname($module,'ID'),get_db_fieldname($module,'parent'),$ID,'1')) {
        return get_buttons(array(array('type' => 'submit', 'name' => 'delete_b', 'value' => __('Delete'), 'active' => false)));
    }
}
function button_copy() {
    return get_buttons(array(array('type' => 'submit', 'name' => 'create_b', 'value' => __('Copy'), 'active' => false)));
}
function button_back() {
    return get_buttons(array(array('type' => 'submit', 'name' => 'cancel_b', 'value' => __('back'), 'active' => false)));
}


/**
 * This function will show the main buttons, e.g. OK, apply, list view
 *
 * @param int $read_o if the user has read only permission
 * @param int $ID id of element on the form
 * @param int $justform 1 if only shows the form
 * @param string $module module name
 * @param boolean $deleteable true if the user has delete rights over the element
 * @param int $sid list of IDs
 * @return array list of buttons to be showed
 */
function get_main_buttons($read_o, $ID, $justform, $module, $deleteable, $sid) {
    global $type;

    $buttons = array();

    // getting the user role for the current module
    $user_role = check_role($module);

    /*************************************
      Ok button
    *************************************/
    if (!$read_o && $user_role > 1) {

        if ($ID == 0) {
            // Create new item
            $button_name = 'create_b';
        }
        else {
            // Item modification
            $button_name = 'modify_b';
        }

        // Add ok button
        $buttons[] = array('type' => 'submit', 'name' => $button_name, 'value' => __('OK'), 'active' => false);
    }

    /*************************************
      Apply button
    *************************************/
    if (!$read_o && $user_role > 1) {

        if ($ID == 0) {
            // Create new item
            $button_name = 'create_update_b';
        }
        else {
            // Item modification
            $button_name = 'modify_update_b';
        }

        // Add apply button
        $buttons[] = array('type' => 'submit', 'name' => $button_name, 'value' => __('Apply'), 'active' => false);
    }

    /*************************************
      anlegen / create hidden value
    *************************************/
    if (!$read_o && $user_role > 1 && $ID == 0) {
        $buttons[] = array('type' => 'hidden', 'name' => 'anlegen', 'value' => 'neu_anlegen');

        $buttons[] = array('type' => 'hidden', 'name' => 'step', 'value' => 'create');
    }


    /*************************************
      List View / Close Button
    *************************************/
    if ($justform > 0) {
        $buttons[] = array('type' => 'button', 'name' => 'close', 'value' => __('Close window'), 'active' => false, 'onclick' => 'window.close();');
    } else {
        $buttons[] = array('type' => 'link', 'href' => $_SERVER['SCRIPT_NAME'].'?type='.$type.'&amp;mode=view', 'text' => __('List View'), 'active' => false);
    }



    /*************************************
      aendern / update hidden value
    *************************************/
    if (!$read_o && $user_role > 1 && $ID > 0) {
        $buttons[] = array('type' => 'hidden', 'name' => 'aendern', 'value' => 'aendern');

        $buttons[] = array('type' => 'hidden', 'name' => 'step', 'value' => 'update');
    }

    return $buttons;

}

/**
 * This function will show the archive buttons, e.g. move to archive, take from archive, mark as read
 *
 * @param int $read_o if the user has read only permission
 * @param int $ID id of element on the form
 * @param int $justform 1 if only shows the form
 * @param string $module module name
 * @param boolean $deleteable true if the user has delete rights over the element
 * @param int $sid list of IDs
 * @return array list of buttons to be showed
 */
function get_archive_buttons($read_o, $ID, $justform, $module, $deleteable, $sid) {
    global $type, $id;

    $buttons = array();

    // getting the user role for the current module
    $user_role = check_role($module);

    /*************************************
      Archive Buttons
    *************************************/
    if ($ID > 0 && $justform < 1) {
        if (check_archiv_flag($ID.$sid,$module)) {
            $buttons[]  = array('type' => 'link', 'href' => $_SERVER['SCRIPT_NAME'].'?mode=view&amp;set_archiv_flag=0&amp;ID_s='.$ID.$sid.'&amp;id_s='.$id.$sid, 'text' => __('take back from archive'), 'active' => false);
        }
        else {
            $buttons[]  = array('type' => 'link', 'href' => $_SERVER['SCRIPT_NAME'].'?mode=view&amp;set_archiv_flag=1&amp;ID_s='.$ID.$sid.'&amp;id_s='.$id.$sid, 'text' => __('move to archive'), 'active' => false);
        }
    }

    /*************************************
      Mark as read
    *************************************/
    if ($ID > 0 && $justform < 1) {
        $buttons[] = array('type' => 'link', 'href' => $_SERVER['SCRIPT_NAME'].'?mode=view&amp;set_read_flag=1&amp;ID_s='.$ID.$sid.'&amp;id_s='.$id.$sid, 'text' => __('mark as read'), 'active' => false);
    }


    return $buttons;

}


/**
 * This function will show the action buttons, e.g. copy, move
 *
 * @param int $read_o if the user has read only permission
 * @param int $ID id of element on the form
 * @param int $justform 1 if only shows the form
 * @param string $module module name
 * @param boolean $deleteable true if the user has delete rights over the element
 * @param int $sid list of IDs
 * @return array list of buttons to be showed
 */
function get_action_buttons($read_o, $ID, $justform, $module, $deleteable, $sid, $copy=true) {
    global $type, $id;

    $buttons = array();

    // getting the user role for the current module
    $user_role = check_role($module);

    /*************************************
      Copy Button
    *************************************/
    if (!$read_o && $user_role > 1 && $copy == true && $ID > 0) {
        $buttons[] = array('type' => 'submit', 'name' => 'copy', 'value' => __('copy'), 'active' => false);
    }

    /*************************************
      Delete Button
    *************************************/
    if (!$read_o && $user_role > 1 && $ID > 0 && $deleteable) {
        $buttons[] = array('type' => 'submit', 'name' => 'delete_b', 'value' => __('Delete'), 'active' => false, 'onclick' => 'return confirm(\''.__('Are you sure?').'\');');
    }

    return $buttons;

}

/**
 * This function will show all default buttons for the corresponding module (ok, apply, etc)
 *
 * @param int $read_o if the user has read only permission
 * @param int $ID id of element on the form
 * @param int $justform 1 if only shows the form
 * @param string $module module name
 * @param boolean $deleteable true if the user has delete rights over the element
 * @param int $sid list of IDs
 * @return array list of buttons to be showed
 */
function get_default_buttons($read_o, $ID, $justform, $module, $deleteable, $sid, $copy=true) {

    // get ok, apply, list view buttons
    $temp1 = get_main_buttons($read_o, $ID, $justform, $module, $deleteable, $sid);

    // get move to archive, mark as read buttons
    $temp2 = get_archive_buttons($read_o, $ID, $justform, $module, $deleteable, $sid);

    // get copy, delete buttons
    $temp3 = get_action_buttons($read_o, $ID, $justform, $module, $deleteable, $sid,$copy);

    $buttons = array_merge($temp1,$temp2,$temp3);

    // return all default buttons
    return $buttons;

}

// end buttons
// ***********

function build_costs_form($fields) {
    global $js_html_textarea, $module, $read_o;

    $out = array();
    $new_fields = array();
    $new_fields['default']=array();
    //prints all the fields;
    foreach ($fields as $field_name => $field) {

        if (!$field['form_tab']) {
            $new_fields['default'][$field_name] = $field;
        }
        else $new_fields[$field['form_tab']][$field_name] = $field;
    }
    foreach ($new_fields as $tabs => $tab_fields) {

        $nbr_cols = 2;

        if(check_role($tabs_name[0]) or $tabs=='default') {

            //reset $reado if its not defaul tab
            $read_o_tmp = $read_o;
            if($tabs != 'default') {
                $read_o = check_role($tabs_name[0])>1? 0 : 1;
            }
            $field_pos = 1;
            $rowspan_stack = array();
            $row_count = 0;
            $output = '<br style="clear:both"/><table>
                        <colgroup>';
            $col_width =(int)( 820 / $nbr_cols);
            $output .= str_repeat("<col width='$col_width' />\n",$nbr_cols);
            $output .= '</colgroup>';

            foreach ($tab_fields as $field_name => $field) {

                // getting the correct field size
                if ($field['form_colspan'] > 1 or $field['form_type'] == 'textarea') {
                    $field['size'] = 'fullsize';
                }
                elseif ($nbr_cols <= 3) {
                    $field['size'] = 'halfsize';
                }
                else { // more than 3 cols
                    $field['size'] = 'smallsize';
                }

                if($field_pos == 1) {
                    $output .= '<tr>';

                    if($field['form_colspan'] > 1 or $field['form_type'] == 'textarea') {

                        //$field['size'] = 'fullsize';
                        $output .= '<td colspan="'.(int)$nbr_cols.'">'.form_element($field, $field_name, false).'</td></tr>';
                        $field_pos = 1;
                    }
                    else {
                        //$field['size'] = 'halfsize';
                        $output .= '<td style="vertical-align:top">'.form_element($field, $field_name, false).'</td>';
                        $field_pos = 2;
                    }
                }
                else { // $field_pos > 1

                    if ($field['form_colspan'] == '2' or $field['form_type']=='textarea') {
                        //$field['size'] = 'fullsize';
                        $temp_remaining_cols = $nbr_cols - $field_pos + 1;
                        $output .= '<td colspan="'.(int)$temp_remaining_cols.'"></td></tr><tr><td colspan="'.(int)$nbr_cols.'">'.form_element($field, $field_name, false).'</td></tr>';
                        $field_pos = 1;
                    }

                    else{

                        //$field['size'] = 'halfsize';
                        $output .= '<td style="vertical-align:top">'.form_element($field, $field_name, false).'</td>';
                        if ($field_pos == $nbr_cols) {
                            $output .= "</tr>";
                            $field_pos = 1;
                        }
                        else {
                            $field_pos++;
                        }
                    }

                }
            }
            if($field_pos > 1) {
                $temp_remaining_cols = $nbr_cols - $field_pos + 1;
                $output .= '<td colspan="'.(int)$temp_remaining_cols.'"></td>';
                $output .= '</tr>';
            }
            $output .= '</table>';

            // check for textarea with html editor
            if ($_SESSION['show_html_editor'][$module] == 1 and is_array($js_html_textarea)) {
                $output .= "<script type=\"text/javascript\">window.onload = function() {\n";
                foreach ($js_html_textarea as $f) {
                    $output .= "newFCKeditor('".$f."','".PATH_PRE."');\n";
                }
                $output .=  '}</script>';
            }
            //reset read_o
            $read_o = $read_o_tmp;
            $tabs_name_out = $tabs_name[1] <> '' ? enable_vars($tabs_name[1]) :__('Basis data');
            $out[]=array($tabs_name_out,$output);
        }

    }
    return $out;
}

/**
 * This Function generates Form output according to the settings
 *
 * @param array   	$out  			- Array with the data of the content
 * @param boolean 	$subtabs		- Use dojo tabs?
 * @param string 	$selected	- Selected tab
 * @return string   					HTML output
 */
function generate_output($out,$subtabs=false, $selected='') {
    // js in this function will be moved to
    // project.js as soon as it is stable
    $outstring='';
    switch($_SESSION['settings']['output_type']) {
        case'1':
        $tabs=array();
        $buttons=array();
        if(!$subtabs)$outstring.='<script type="text/javascript">
	                     dojo.require("dojo.widget.TabContainer");
	                     dojo.require("dojo.widget.LinkPane");
	                     dojo.require("dojo.widget.ContentPane");
	                     dojo.require("dojo.widget.LayoutContainer");
                         </script>';
        $tabid = $subtabs? 'subTabContainer':'mainTabContainer';
        $tab_name = $subtabs ? $tabid.$subtabs : 'tab';
        $selected = $selected <>'' ? $selected : 1;
        $outstring.='<div id="dojoParentTab" dojoType="TabContainer" doLayout="false" selectedChild="tab'.$selected.'">';
        $i=1;
        foreach ($out as $value) {
            list($name, $content) = $value;
            switch($name) {
                case 'html':
                    $outstring.=$content;
                    break;
                default:
                    $outstring.='
                <div id="tab'.$i.'" dojoType="ContentPane" label="'.$name.'">'.
                    //$buttons[0].
                    $content.'</div>';
                    $i++;
            }
        }

        $outstring.='</div>';
        break;

        case'2':
        $outstring.='
                <script language="JavaScript" type="text/javascript">
	               dojo.require("dojo.widget.AccordionContainer");
	               dojo.require("dojo.widget.ContentPane");
                </script>';
        $outstring.='<div  id="dojoParentTab" dojoType="AccordionContainer"  labelNodeClass="label" containerNodeClass="accBody"
			class="dojoAccordion">';
        $i =0;
        foreach ($out as $value) {
            list($name, $content) = $value;
            switch($name) {
                case 'html':
                    $outstring.=$content;
                    break;
                default:

                    $outstring.= '<div dojoType="ContentPane"';
                    $outstring.= $i==0 ?' selected="true" ':'';
                    $outstring.=' label="'.$name.'">'.
                    $content.'</div>';
                    $i++;
            }
        }
        $outstring.='</div>';
        break;
        default:
            foreach ($out as $value) {
                list($name, $content) = $value;
                switch($name) {
                    case 'html':
                        $outstring.=$content;
                        break;
                    default:
                        $outstring.='
                    <fieldset>
                    <legend>'.__($name).'</legend>'.
                        $content.
                        '</fieldset>';
                }
            }
            break;
    }

    //return '';
    return $outstring;
}

?>
