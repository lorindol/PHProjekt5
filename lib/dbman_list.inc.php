<?php

// dbman_list.inc.php - PHProjekt Version 5.2
// copyright  ©  2000-2005 Albrecht Guenther  ag@phprojekt.com
// www.phprojekt.com
// Author: Albrecht Guenther, $Author: nina $
// $Id: dbman_list.inc.php,v 1.184.2.20 2007/06/18 09:49:58 nina Exp $


// check whether the lib has been included - authentication!
if (!defined('lib_included')) die('Please use index.php!');

$element_mode = isset($element_mode) ? qss($element_mode) : '';
$element_ID = isset($element_ID) ? (int) $element_ID : -1;
diropen_mode($element_mode, $element_ID);


function build_table($addfields, $module, $where, $page=0, $perpage=30, $link=null, $origin='', $is_related_obj=false, $caption='', $tfoot='') {
    global $field_name, $field, $fields, $flist, $tablename, $nr_record, $children, $row;
    global $tdw, $tdw_store, $output1, $menu2, $contextmenu;
    global $listentries_single, $listentries_selected, $fieldlist, $addcols, $build_table_records;
    $output1 = '';

    if (!$tdw and $tdw_store) $tdw = $tdw_store;
    // fetch additional colums
    $addcols = get_additional_columns($module, $fieldlist, $fields, '0', array());

    $output1 .= "
<script type='text/javascript'>
//<![CDATA[
function resizeImage(dx,mode) {
    var obj = eval(\"document.img\"+recID);
    if (mode =='relative') {
        if (dx>0) obj.width = column.offsetWidth + dx;
        else obj.width = obj.width + dx;
    }
    else {
        obj.width = dx;
    }
    document.onmouseup = nop;
}
function showsize() {
";
    if (is_array($fields)) {
        foreach ($fields as $field_name => $field) {
            $output1 .= "document.tdwfrm.ii".$field_name.".value = document.images['img".$field_name."'].width;\n";
        }
    }
    $output1 .= "
    return true;
}//]]>
</script>";

    // table header
    $output1.= "<table id=\"$module\" summary=\"$module\" width=\"100%\">";
    if($caption){
        $output1 .= '<caption>'.$caption.'</caption>';
    }
    $output1.= "\n<thead>\n<tr>\n";
    // fetch the relevant fields to display in list view
    if (is_array($fields)) {
        foreach ($fields as $field_name => $field) {
            if ($field['list_pos'] > 0) {
                $fieldlist[] = $field_name;
            }
        }
    }

    // additional custom column as first cell?
    if (is_array($addcols) and $addcols[0] <> '') {
        $output1 .= "<th scope='col' class='column2' style='width:5%;'>&nbsp;</th>\n";
    }
    if (is_array($fields)) {
        foreach ($fields as $field_name => $field) {
            if ($field['list_pos'] > 0) {
                // define width of table cell
                $cw = floor(97/(count($fieldlist)+1));
                if (!isset($tdw[$module][$field_name])) $tdw[$module][$field_name] = $cw;
                // um valides xhtml zu erhalten
                if ($contextmenu > 0)$output1.= "<th id=\"".$field_name."_".$module."\" scope=\"col\" oncontextmenu=\"startMenu('".$menu2->menucolID."','".$field_name."',this);\">";
                else $output1.= "<th id=\"".$field_name."_".$module."\" scope=\"col\" style='width:".$cw."%;'>";
                $output1.= col_filter($module, $field_name, $link, $cw);
                $output1.= "<img src='".PATH_PRE."img/t.gif' name=\"img".$field_name."\" height='1' width='".$tdw[$module][$field_name]."' alt='alter direction' title='alter direction' />";
                $output1.= "</th>\n";
            }
        }
    }
    // additional custom column as first cell?
    if (is_array($addcols) and isset($addcols[1]) and $addcols[1] <> '') { $output1 .= '<th>&nbsp;</th>'; }
if ($module == 'vacation')
    $output1 .= "</tr>\n</thead>\n";

    if($tfoot){
        $output1 .= '
            <tfoot>
                <tr>
                    <td colspan="'.(count($fields) + count($addcols)).'">'.$tfoot.'</td>
                </tr>
            </tfoot>
        ';
    }


    $table = isset($tablename[$module]) ? $tablename[$module] : $module;
    // start rows
    // store the result in two arrays: one contains all records, the other one just the root records (just in case no filter is active)
    /**
     * add alt_list elements to row
     */
    if(isset($ID)){
        $fields2=build_array($module,$ID,"list_alt");
    }
    else{
        $fields2=build_array($module,0,"list_alt");
    }
    // fetch the relevant fields to display in list_alt
    $fieldlist2=array();
    if (is_array($fields2)) {
        foreach ($fields2 as $field_name => $field) {
            if ($field['list_alt'] =='on') {
                $fieldlist2[] = $field_name;
            }
        }
    }

    $select_array= array_merge($addfields, $fieldlist,$fieldlist2);

    // Fix for addons
    if (is_array($fields2)) {
        $fields_array= array_merge($fields, $fields2);
    } else {
        $fields_array= $fields;
    }

    $row_data = get_row_data($fields_array, $select_array, $table, $module, $where);
    $id_row=array();
    for($i=0;$i<count($select_array);$i++){
        $id_row[$select_array[$i]]=$i;
    }

    $mainrecords = array();

    foreach($row_data as $int => $row2) {
        $row[$row2[0]['value']] = $row_data[$int];
        // build array of children
        if ($row2[3]['value'] > 0) {
            $children[$row2[3]['value']][] = $row2[0]['value'];
            $parent[$row2[0]['value']] = $row2[3]['value'];
        }
        // depending on tree view or flat view (due to filter setting) add the record to the list
        if (((!isset($flist[$module]) || (!$flist[$module] <> '') || (is_array($flist[$module]) && count($flist[$module]) == 0)) && !$row2[3]['value'])) {
            $mainrecords[] = $row2[0]['value'];
        }
        else { 
            $allrecords[] = $row2[0]['value']; 
        }
    }
    //strip foundrecords from children which are already listed with parentproject
    if (isset($allrecords) && is_array($allrecords)&&(!empty($allrecords))) {
        foreach($allrecords as $key=>$kid){
            if($parent[$kid]>0 and (in_array($parent[$kid],$mainrecords) or in_array($parent[$kid],$allrecords))){}
            else $foundrecords[$key]=$kid;
        }
    }


    // end of transfer from db, begin output
 // if ($module == 'vacation') { $page = 1;}

    $maxnr     = (count($row) < ($page+1)*$perpage) ? count($row) : ($page+1)*$perpage;
    $nr_record = $page*$perpage;
    $entries   = 0;


    while ($nr_record < $maxnr) {
        if ((!isset($flist[$module])) || (is_array($flist[$module]) && count($flist[$module]) == 0 ) && $mainrecords) {
            if ($mainrecords[$nr_record] > 0) {
                $entries++;
                list_records($mainrecords[$nr_record], $module, $fieldlist, $fields, $addfields, $page, $perpage,$fields2,$fieldlist2,$id_row,$is_related_obj);
            }
        }
        // ... otherwise over the found records according to the filter
        else {
            if (isset($foundrecords[$nr_record]) && is_numeric($foundrecords[$nr_record]) && $foundrecords[$nr_record] > 0) {
                $entries++;
                list_records($foundrecords[$nr_record], $module, $fieldlist, $fields, $addfields, $page, $perpage,$fields2,$fieldlist2, $id_row,$is_related_obj);
            }
        }
        $nr_record++;
    }

    if ($entries == 0) {
        $output1 .= "<tbody><tr><td></td></tr></tbody>\n";
    }

    $build_table_records = $entries;

    $output1 .= "</table>\n";
    return $output1;
}


function list_records($ID, $module, $fieldlist, $fields, $addfields, $page, $perpage,$fields2,$fieldlist2,$id_row, $is_related_obj=false) {
    global $level, $diropen, $tree_mode,$sid;
    global $nr_record, $output1, $flist, $getstring, $children, $row, $menu2, $int;
    global $addcols;

    if ($module == 'dateien') $module = 'filemanager';

    foreach($row[$ID] as $int => $array) {
        // use the xss function only for textareas!
        if ($row[$ID][$int]['form_type'] == 'textarea') $row[$ID][$int]['value'] = xss($array['value']);
        else $row[$ID][$int]['value'] = htmlentities($array['value']);
    }

    // fill main array with values
    $alternative_view="";
    $alt_height=20;
    $alt_width=190;

    //print_r($fields2);
    for ($i=0; $i < count($fieldlist); $i++) {
        $records[$ID][$fieldlist[$i]] = $row[$ID][$i+count($addfields)]['value'];
    }
    for ($i=0; $i < count($fieldlist2); $i++) {

        $newline = ucfirst(enable_vars($fields2[$fieldlist2[$i]]['form_name'])).": ";

        $data = get_correct_value($row[$ID][$id_row[$fieldlist2[$i]]]['value'],$fields2[$fieldlist2[$i]]);
        $newline .= $data['value'];
        if(strlen($newline)>100) $newline = substr($newline,0,100)." ...";
        $newline = wordwrap($newline, 32, "<br />", 1)."<br />";

        $alt_height = $alt_height + (12 * (substr_count($newline, '<br />') - 1));

        $temp_enters = substr_count($newline, "\n");
        $alt_height = $alt_height + ($temp_enters * 12) - 1;
        $newline = str_replace("\n","<br />",$newline);
        $newline = str_replace("\r","",$newline);

        $alternative_view .= $newline;

        $alt_height = $alt_height + 12;
    }
    for ($i=0; $i < count($fieldlist); $i++) {
        $records[$ID][$fieldlist[$i]] = $row[$ID][$i+count($addfields)]['value'];
    }
    // TODO: this should be solved in a better way
    // handle/convert special field entries
    convert_special_entries($module, $records[$ID]);

    // get the background-color of a listitem
    $bg_class = get_background_class($module, $records[$ID], $ID);
    // fetch additional colums
    $addcols  = get_additional_columns($module, $fieldlist, $fields, $ID, $records[$ID]);

    // create different links for modules and addons
    $link = "";
    if ($_SESSION['common']['module'] == 'addons') { $link = "./addon.php"; }
    else if(strstr($getstring, 'addon=')){
        $link="../addons/addon.php";
    }
    else { $link = "../$module/$module.php"; }

    if ($is_related_obj) {
        $ref = "javascript:void(0);'\" title='".__('This link opens a popup window')."' onclick='window.open(\"$link?justform=1&amp;$getstring&amp;mode=forms&amp;ID=$ID$sid\", \"related_object\", \"width=760px,height=540px,scrollbars=yes\");";
        tr_tag('', "", $ID, $records[$ID][$fieldlist[0]], '', $module, $bg_class); // draw <tr>
    }
    else {
        $ref = "$link?$getstring&amp;mode=forms&amp;ID=$ID".$sid;
        tr_tag($ref, "", $ID, $records[$ID][$fieldlist[0]], '', $module, $bg_class); // draw <tr>
    }

    // fetch additional columns which will be drwawn first - and as last cells
    if (isset($addcols[0])) $output1 .= $addcols[0];

    // begin row, first cell
    $output1 .= "<td class='column2'";
    $in = 10;
    for ($i2=1; $i2 <= $level; $i2++) {
        if ($module=='filemanager1') $in += 16;
        else $in += 10;
    }
    if (!isset($children[$ID])) {
        if ($module=='filemanager1') $in += 18;
        else $in += 12;
    }
    $output1 .= " style='padding-left:".$in."px'>";
    // indent until level in tree

    // buttons

    $output1 .= buttons($ID, $module);

    // if this value is blank, add a '[blank]'
    $alt='';
    if ($alternative_view <>'') {
        $alternative_view = str_replace("'","",$alternative_view);
        $alternative_view = str_replace("&#39;","",$alternative_view);
        $alternative_view = xss(str_replace('"',"",$alternative_view));

        $alt='onmouseover="show_alternative_view(\''.$alternative_view.'\','.$alt_width.','.$alt_height.',\'alternative_view\')" onmouseout="hide_alternative_view(\'alternative_view\')"';
    }
    $data = get_correct_value($records[$ID][$fieldlist[0]],$fields[$fieldlist[0]]);
    $first_value = $data['value'];
    $first_value = ($first_value) ? stripslashes($first_value) : '['.__('No Value').']';

    // special class
    if ($bg_class <> '') $aclass = "class='$bg_class'";
    else $aclass = '';
    $output1 .= "<a href='".$ref."' $alt $aclass>".$first_value."</a></td>\n";

    for ($i=1; $i<count($fieldlist); $i++) {
        // link
        switch ($fields[$fieldlist[$i]]['form_type']) {
            // link
            case 'email':
            case 'url':
            case 'upload':
                $data = get_correct_value($records[$ID][$fieldlist[$i]],$fields[$fieldlist[$i]], $ID);
                $output1.= "<td class='column2'>".$data['link']."</td>\n";
                break;
            // values
            default:
                $data = get_correct_value($records[$ID][$fieldlist[$i]],$fields[$fieldlist[$i]], $ID);
                $output1.= "<td class='column2'>".$data['value']."</td>\n";
                break;
        }
    }

    if (isset($addcols[1])) $output1 .= $addcols[1];

    $output1 .= "</tr>\n";

    // display children
    if (($diropen[$module][$ID] or $tree_mode=='open') and !empty($children[$ID])) {
        foreach ($children[$ID] as $child) {
            // $nr_record++;
            $level++;
            list_records((int) $child, $module, $fieldlist, $fields, $addfields, $page, $perpage,$fields2,$fieldlist2,$id_row,$is_related_obj);
            $level--;
        }
    }
}


function show_string_list($content, $fields, $content_fields, $field_offset=0) {
    // paste the values of the fields into the array
    foreach ($fields as $field_name => $field) {
        $content = ereg_replace($field_name, $content_fields[$field_offset], $content);
        $field_offset++;
    }
    return preg_replace_callback("#\[(.*)\]#siU", 'f2_list', $content);
}

function f2_list($f) {
    eval('$y = '.$f[1].';');
    return $y;
}
// end list functions
// ******************


// *************
// start functions for archive flag and read flag
function read_mode($module) {
    // no value in session?
    if (!isset($_SESSION['show_read_elements']["$module"])) {
        // check for settings
        if (isset($GLOBALS['show_read_elements_settings']["$module"])) {
            $_SESSION['show_read_elements']["$module"] = $GLOBALS['show_read_elements_settings']["$module"];
        }
        // if we cannot find a value in the session nor in the settings, assume that the user is used to see all records
        else {
            $_SESSION['show_read_elements']["$module"] = 0;
        }
    }
    // now check whether the user has toggled the flag for show/hide
    if (isset($_REQUEST['toggle_read_flag']) && $_REQUEST['toggle_read_flag'] == 1) {
        if ($_SESSION['show_read_elements']["$module"] == 0) $_SESSION['show_read_elements']["$module"] = 1;
        else $_SESSION['show_read_elements']["$module"] = 0;
    }
}


function archive_mode($module) {
    // no value in session?
    if (!isset($_SESSION['show_archive_elements']["$module"])) {
        // check for settings
        if (isset($GLOBALS['show_archive_elements_settings']["$module"])) {
            $_SESSION['show_archive_elements']["$module"] = $GLOBALS['show_archive_elements_settings']["$module"];
        }
        // if we cannot find a value in the session nor in the settings, assume that the user is used to see all records
        else {
            $_SESSION['show_archive_elements']["$module"] = 0;
        }
    }
    // now check whether the user has toggled the flag for show/hide
    if (isset($_REQUEST['toggle_archive_flag']) && $_REQUEST['toggle_archive_flag'] == 1) {
        if ($_SESSION['show_archive_elements']["$module"] == 0) $_SESSION['show_archive_elements']["$module"] = 1;
        else $_SESSION['show_archive_elements']["$module"] = 0;
    }
}


function html_editor_mode($module) {
    // no value in session?
    if (!isset($_SESSION['show_html_editor']["$module"])) {
        // check for settings
        if (isset($GLOBALS['show_html_editor_settings']["$module"])) {
            $_SESSION['show_html_editor']["$module"] = $GLOBALS['show_html_editor_settings']["$module"];
        }
        // if we cannot find a value in the session nor in the settings, assume that the user is used to see the html editor
        else {
            $_SESSION['show_html_editor']["$module"] = 0;
        }
    }
    // now check whether the user has toggled the flag for show/hide
    if (isset($_REQUEST['toggle_html_editor_flag']) && $_REQUEST['toggle_html_editor_flag'] == 1) {
        if ($_SESSION['show_html_editor']["$module"] == 0) $_SESSION['show_html_editor']["$module"] = 1;
        else $_SESSION['show_html_editor']["$module"] = 0;
    }
}
/**
 * Function to switch views: all groups current grouo
 *
 * @author Nina Schmitt
 * @param string $module current module
 */
function group_mode($module) {
    // no value in session?
    if (!isset($_SESSION['show_all_groups']["$module"])) {
        // check for settings
        if (isset($GLOBALS['show_all_groups_settings'])) {
            $_SESSION['show_all_groups']["$module"] = $GLOBALS['show_all_groups_settings'];
        }
        // if we cannot find a value in the session nor in the settings, assume that the user is used to see records from current group ony
        else {
            $_SESSION['show_all_groups']["$module"] = 0;
        }
    }
    // now check whether the user has toggled the flag for show/hide
    if (isset($_REQUEST['toggle_show_all_groups']) && $_REQUEST['toggle_show_all_groups'] == 1) {
        echo"1";
        if ($_SESSION['show_all_groups']["$module"] == 0) $_SESSION['show_all_groups']["$module"] = 1;
        else{
           $_SESSION['show_all_groups']["$module"] = 0;
        }
    }
}


/**
* return additional sql string to filter archived and/or read elements
* @author Alex Haslberger
* @param string $module module to which the entry belongs
* @param arr    $flags flags to be checked
* @return string $str additional query string
*/
function sql_filter_flags($module, $flags, $before_where = true) {
    global $user_ID;

    if ($module == 'links') {
        return ''; // module reminder should not LEFT JOIN to itself
    }
    // return empty string if no restriction to archived or read elements is made
    if (isset($_SESSION['show_archive_elements']["$module"]) and $_SESSION['show_archive_elements']["$module"] == 0 and isset($_SESSION['show_read_elements']["$module"]) && $_SESSION['show_read_elements']["$module"] == 0) {
        $str = '';
    }
    else {
        $str = '';
        // perform the left join
        if ( in_array('archive',$flags) || in_array('read',$flags) ) {
            $table = get_table_by_module($module);
            if ($before_where) {
                $str .= ' LEFT JOIN  '.DB_PREFIX.'db_records ON ('.$table.'.ID = '.DB_PREFIX.'db_records.t_record AND '.DB_PREFIX.'db_records.t_module = \''.DB_PREFIX.qss($module).'\' AND '.DB_PREFIX.'db_records.t_author = '.$user_ID.') ';
            }
            // perform the where clause
            else {
                $tmp = array();
                if (in_array('archive',$flags)) {
                    // filter archived elements. if value is 1, show them, if value is 0, dont show them
                    if (($_SESSION['show_archive_elements']["$module"] == 1)) {
                        $tmp[] = ' ('.DB_PREFIX.'db_records.t_archiv = 0 OR '.DB_PREFIX.'db_records.t_archiv IS NULL) ';
                    }
                }
                if (in_array('read',$flags)) {
                    // filter read elements - if value is 1, show them, if value is 0, don't show them
                    if (isset($_SESSION['show_read_elements'][$module]) && $_SESSION['show_read_elements'][$module] == 1) {
                        $tmp[] = ' ('.DB_PREFIX.'db_records.t_touched = 0 OR '.DB_PREFIX.'db_records.t_touched IS NULL) ';
                    }
                }
                // implode separate statements
                if ($tmp != array()) $str .= ' AND ('.implode(' AND ', $tmp).')';
            }
        }
    }
    return $str;
}

/**
* return additional sql string to filter archived and/or read elements
* @author Gustavo Solt
* @param string $module module to which the entry belongs
* @param arr    $flags flags to be checked
* @return string $str additional query string
*/
function special_sql_filter_flags($module, $flags, $before_where=true) {
    global $user_ID;

    $str = '';
    // perform the left join
    if ( (isset($flags['exclude_archived']) && $flags['exclude_archived'] == "on") ||
         (isset($flags['exclude_read']) && $flags['exclude_read'] == "on") ) {
        $table = get_table_by_module($module);
        if ($before_where) {
            $str .= ' LEFT JOIN  '.DB_PREFIX.'db_records ON ('.$table.'.ID = '.DB_PREFIX.'db_records.t_record AND '.DB_PREFIX.'db_records.t_module = \''.DB_PREFIX.qss($module).'\' AND '.DB_PREFIX.'db_records.t_author = '.$user_ID.') ';
        }
        // perform the where clause
        else {
            $tmp = array();
            if (isset($flags['exclude_archived']) && $flags['exclude_archived'] == "on") {
                // filter archived elements
                $tmp[] = ' ('.DB_PREFIX.'db_records.t_archiv = 0 OR '.DB_PREFIX.'db_records.t_archiv IS NULL) ';
            }
            if (isset($flags['exclude_read']) && $flags['exclude_read'] == "on") {
                // filter read elements
                $tmp[] = ' ('.DB_PREFIX.'db_records.t_touched = 0 OR '.DB_PREFIX.'db_records.t_touched IS NULL) ';
            }
            // implode separate statements
            if ($tmp != array()) $str .= ' AND ('.implode(' AND ', $tmp).')';
        }
    }
    return $str;
}

function sort_mode($module,$default_column, $default_direction='ASC') {
    global $f_sort, $f_sort_store;

    if (isset($_GET['sort']) && strlen($_GET['sort']) > 0) {
        
        // the sort could be for a related object, it is necessary to check
        if (isset($_GET['sort_module']) && strlen($_GET['sort_module']) > 0) {
            $tmp_sort_module = xss($_GET['sort_module']);
        } 
        else $tmp_sort_module = $module;
        
        $f_sort[$tmp_sort_module]['sort'] = xss($_GET['sort']);
        $f_sort[$tmp_sort_module]['direction'] = isset($_GET['direction']) ? xss($_GET['direction']) : '';
    }
    else if ($_SESSION['f_sort'][$module]['sort'] <> '') {
        $f_sort[$module]['sort'] = $_SESSION['f_sort'][$module]['sort'];
        $f_sort[$module]['direction'] = $_SESSION['f_sort'][$module]['direction'];
    }
    else if ($f_sort_store[$module]['sort'] <> '') {
        $f_sort[$module]['sort'] = $f_sort_store[$module]['sort'];
        $f_sort[$module]['direction'] = $f_sort_store[$module]['direction'];
    }
    else if (!$f_sort[$module]['sort']) {
        $f_sort[$module]['sort'] = $default_column;
        $f_sort[$module]['direction'] = $default_direction;
    }
    $_SESSION['f_sort'] =& $f_sort;
}


// provides sql string to order result sets
function sort_string($sort_module=null, $has_to_be_sorted = false) {
    global $direction_rel, $sort_col, $f_sort, $module;

    if ($has_to_be_sorted == true && isset($sort_col) && isset($direction_rel) && $sort_col != '' && $direction_rel != '') {
        return 'ORDER BY '.$sort_col.' '.$direction_rel;
    }
    if (!$sort_module) $sort_module = $module;
    
    if ($f_sort[$sort_module]['sort'] <> '') {
        return 'ORDER BY '.$f_sort[$sort_module]['sort'].' '.$f_sort[$sort_module]['direction'];
    }
}

// stores the column width of a module
function store_column_width($module) {
    global $fields, $tdw;

    foreach ($fields as $field_name => $field) {
        $tdw[$module][$field_name] = xss($_POST["ii$field_name"]);
    }
    $_SESSION['tdw'] =& $tdw;
}


function diropen_mode($element_mode, $ID) {
    global $element_module, $diropen, $diropen_store;

    // take the stored set
    if (!$diropen and $diropen_store) {
        $diropen = $diropen_store;
    }
    // open and close main contact
    if ($element_mode == 'open') {
        $diropen[$element_module][$ID] = 1;
    }
    else if ($element_mode == 'close') {
        $diropen[$element_module][$ID] = 0;
    }
    $_SESSION['diropen'] =& $diropen;
}
// end archive and read flag
// *************************


function buttons($element_ID, $element_module) {
    global $diropen, $sid, $filter, $tree_mode;
    global $tablename, $children, $module, $mode, $ID, $getstring;
    $str = '';
    if(!empty($getstring)){
        $addget=xss($getstring)."&";
    }
    $buttons = 0;
    // if the radio button 'open' was selected: set all main projects to open:
    if ($tree_mode == 'open')  $diropen[$element_module][$element_ID] = 1;
    if ($tree_mode == 'close') $diropen[$element_module][$element_ID] = 0;
    if (isset($children[$element_ID])) {
        // show button 'open'
        if (!$diropen[$element_module][$element_ID]) { $str = "<a name='A".$row[0]."' href='".$module.".php?$addget"."mode=$mode&amp;element_mode=open&amp;element_ID=$element_ID&amp;element_module=$element_module&amp;csrftoken=".make_csrftoken()."&amp;ID=$ID".$sid."#A$row[0]'>".tree_icon('open')."&nbsp;</a>"; }
        // show button 'close'
        else { $str = "<a name='A".$row[0]."' href='".$module.".php?$addget"."mode=$mode&amp;element_mode=close&amp;element_ID=$element_ID&amp;element_module=$element_module&amp;csrftoken=".make_csrftoken()."&amp;ID=$ID".$sid."#A$row[0]'>".tree_icon('close')."</a>&nbsp;"; }
    }
    //else $str = '&nbsp;&nbsp;&nbsp;&nbsp;';
    return $str;
}


function save_filter($module, $name, $ID=NULL, $dat=NULL) {
    global $user_ID;

    $filter     = '';
    $sort       = '';
    $direction  = '';
    $operator   = 'AND'; // default value for filter operator

    // Assign filter
    if (isset($dat)) $filter = serialize($dat);
    else $filter = serialize(xss_array($_SESSION['flist'][$module]));

    // Assign sort and direction
    if (is_array($_SESSION['f_sort'][$module])) {
        $sort = qss($_SESSION['f_sort'][$module]['sort']);
        $direction = qss($_SESSION['f_sort'][$module]['direction']);

        // getting the operator
        if (isset($_SESSION['flist']['operators'][$module])) {
            $operator = qss($_SESSION['flist']['operators'][$module]);
        }
    }



    // Update a filter
    if ($ID) {
        $result = db_query("UPDATE ".DB_PREFIX."filter
                                   SET filter           = '".strip_tags($filter)."',
                                       filter_sort      = '".strip_tags($sort)."',
                                       filter_direction = '".strip_tags($direction)."',
                                       filter_operator  = '".strip_tags($operator)."'
                                 WHERE ID = ".(int)$ID."
                                   AND von = ".(int)$user_ID."
                                   AND module = '".qss($module)."'") or db_die();
    // Create a filter
    } else {
        $result = db_query("INSERT INTO ".DB_PREFIX."filter
                                            (von, module, name, filter, filter_sort, filter_direction, filter_operator)
                                     VALUES (".(int)$user_ID.", '".qss($module)."', '".strip_tags($name)."', '".strip_tags($filter)."', '".strip_tags($sort)."', '".strip_tags($direction)."', '".strip_tags($operator)."')") or db_die();
    }
}

function load_filter($ID, $module) {
    global $user_ID;

    $result = db_query("SELECT filter, filter_sort, filter_direction, filter_operator
                            FROM ".DB_PREFIX."filter
                           WHERE ID = ".(int)$ID."
                             AND von = ".(int)$user_ID."
                             AND module = '$module'") or db_die();
    $row = db_fetch_row($result);
    return array(unserialize($row[0]),$row[1],$row[2], $row[3]);
}


function get_filters($module) {
    global $user_ID;

    $result = db_query("SELECT ID, name
                          FROM ".DB_PREFIX."filter
                         WHERE module = '$module'
                           AND von = ".(int)$user_ID) or db_die();

    $retval = array();
    while ($row = db_fetch_row($result)) {
        $retval[$row[0]] = $row[1];
    }
    return $retval;
}


function delete_filter($ID, $module) {
    global $user_ID;

    $result = db_query("DELETE FROM ".DB_PREFIX."filter
                              WHERE ID = ".(int)$ID."
                                AND von = ".(int)$user_ID) or db_die();
}


/**
* Set background-color of a listitem
* This function can be used to highlight important entries for a module
*
* @param  $module string Modulename
* @param  $data   array This is the data for the current dataset (row) with fieldname as array key
* @param  $ID     integer This is the ID of the row to be displayed
* @return string  Either a css class or an empty string
*/
function get_background_class($module, $data, $ID = 0) {
    switch ($module) {
        case 'todo':
            if (strlen($data['deadline']) && (strtotime($data['deadline']) < time()) && $data['status'] < 4 ) {
                return 'todo_deadline_exceeded';
            }
            break;

        case 'calendar':
            if ($data['status'] == '1') {
                // FIXME: this does not work at this time cause the field 'status' is missing
                // event cancelled
                return 'calendar_event_canceled';
            }
            switch ($data['partstat']) {
                // yet not decided
                case '1':
                    return 'calendar_event_open';
                    break;
                    // accepted
                case '2':
                    return 'calendar_event_accept';
                    break;
                    // rejected
                case '3':
                    return 'calendar_event_reject';
                    break;
            }
            break;

        case 'mail':

            // check if it is a sent, received or folder element
            $result = db_query("SELECT ID, typ, date_sent, date_received, touched
                                FROM ".DB_PREFIX."mail_client
                               WHERE ID = ".(int)$ID);

            if ($row = db_fetch_row($result)) {
                if ($row[1] <> 'd') {

                    if ($row[4] == 0) {
                        return 'mail_unread';  // in fact, it is mail_touched
                    }

                }

            }

            break;
    }
    return '';
}


/**
* Converts special list entries
*
* @param  $module string Modulename
* @param  $data   array This is the data for the current dataset (row) with fieldname as array key
* @return void    works on a reference
*/
function convert_special_entries($module, &$data) {
    global $date_format_object;

    switch ($module) {
        case 'calendar':
            $data['datum']  = $date_format_object->convert_db2user($data['datum']);
            $data['anfang'] = substr($data['anfang'],0,2).':'.substr($data['anfang'],2,2);
            $data['ende']   = substr($data['ende'],0,2).':'.substr($data['ende'],2,2);
            break;
    }
}


/**
* Create first/last column defined by module
* The returned value needs to include the <td>-Tags
* @param $module string Modulename
* @param $fieldlist array A numeric array with the activated fields
* @param $fields array Holds the definition of the fields
* @param $ID int ID
* @param $data array This is the data for the current dataset (row) with fieldname as array key
* @return array(0 => fisrt column, 1 => last column)
*/
function get_additional_columns($elementmodule, $fieldlist, $fields, $element_ID, $data) {
    global $user_ID, $tree_mode, $children, $diropen, $module, $file_ID, $mode, $ID;

    switch ($elementmodule) {
        case 'todo_alt':
            $result = db_query("SELECT progress, status
                                  FROM ".DB_PREFIX."todo
                                 WHERE ID = ".(int)$element_ID);
            list($progress, $status) = db_fetch_row($result);
            if ($data['von'] == $user_ID and ($status > 1 and $status < 5)) {
                $lastcol = "
        <form action='todo.php' method='post'>
            <td>
                <input type='hidden' name='mode'     value='data' />
                <input type='hidden' name='cstatus'  value='".$GLOBALS['cstatus']."' />
                <input type='hidden' name='category' value='".$GLOBALS['category']."' />
                <input type='hidden' name='ID'       value='$element_ID' />
                <input type='hidden' name='step'     value='update_progress' />
            </td>
            <td align='right'>
                <input type='text' size='3' name='progress' value='$progress' onblur='this.form.submit();' />%
            </td>
        </form>\n";
            }
            else {
                $lastcol = "<td align='right'>$progress%</td>\n";
            }
            return array(1 => $lastcol);
            break;

        case 'test' :

            if (!isset($_GET['submode']) || (isset($_GET['submode']) && $_GET['submode'] == 'round')) {

                if ($element_ID > 0) {

                    $assessment = 0;
                    $fertig = 0;
                    $count = 0;

                    $sql = "SELECT test_itemID, fertig,  assessment FROM ".DB_PREFIX."test_round_item WHERE test_roundID = ".$element_ID;

                    $result = db_query($sql);

                    while ($row = db_fetch_row($result)) {
                        if ($row[1] <> '') {
                            $fertig++;
                        }
                        if ($row[2] <> '') {
                            $assessment++;
                        }
                        $count ++;
                    }
		      $failed = $fertig - $assessment;
                    if ($failed > 0) $failed = "<font color=red>$failed</font>";

                   $lastcol = "<td>".$count." - Performed: ".$fertig." - Failed: ".$failed."</td>";

                   return array(1 => $lastcol);
                } else {
                    return array(1 => __('Test items'));
                }
            }
        break;

        case 'filemanager':
        case 'dateien':
            //$firstcol = "<td class='column2' style='width:20px !important;'>&nbsp;$mime_img</td>\n";
            //return array(0 => $firstcol);

            $csrftoken = make_csrftoken();
            $url = "../filemanager/filemanager_down.php?mode=down&amp;mode2=attachment&amp;ID=$element_ID&amp;csrftoken=$csrftoken";
            $file_parts = explode('.', slookup('dateien', 'userfile', 'ID', $element_ID, true));
            $file_lock  = slookup('dateien', 'lock_user', 'ID', $element_ID,'1');
            $file_type  = slookup('dateien', 'typ', 'ID', $element_ID,'1');
            $file_mime  = $file_parts[count($file_parts)-1];
            if ($file_type == 'd') {
                if ($tree_mode == 'open')  $diropen[$elementmodule][$element_ID] = 1;
                if ($tree_mode == 'close') $diropen[$elementmodule][$element_ID] = 0;
                if ($children[$element_ID]) {
                    // show button 'open'
                    if (!$diropen[$elementmodule][$element_ID]) { $mime_img= "<a name='A".$row[0]."' href='".$module.".php?mode=$mode&amp;element_mode=open&amp;element_ID=$element_ID&amp;element_module=$elementmodule&amp;ID=$ID".$sid."#A$row[0]'><img src='../img/folder_yellow.png' alt='close Element' title='close Element' border='0' />&nbsp;</a>"; }
                    // show button 'close'
                    else { $mime_img = "<a name='A".$row[0]."' href='".$module.".php?mode=$mode&amp;element_mode=close&amp;element_ID=$element_ID&amp;element_module=$elementmodule&amp;ID=$ID".$sid."#A$row[0]'><img src='../img/folder_yellow_open.png' alt='open Element' title='open Element' border='0' /></a>&nbsp;"; }
                }
                else { $mime_img = "<img src='../img/folder_yellow.png' alt='open Element' title='open Element' border='0' />&nbsp;"; }
            }
            else if (($file_lock > '0') and ($file_lock != $user_ID )) {
                if ($file_type == 'l') {
                    $mime_img ="<a href='".$url."' target='_blank'><img src='../img/encrypted.png' alt='encrypted' title='encrypted' border='0' /></a>";
                }
                else {
                    $mime_img ="<a href='".$url."'><img src='../img/encrypted.png' alt='encrypted' title='encrypted' border='0' /></a>";
                }
            }

            else {
                // get the icon name and alt description
                $image_data = get_file_icon($file_mime, true);

                if ($file_type == 'l') {
                    $mime_img ="<a href='".$url."' target='_blank'><img src='../img/{$image_data['icon']}' alt='{$image_data['alt']}' title='{$image_data['alt']}' border='0' /></a>";
                }
                else {
                    $mime_img ="<a href='".$url."'><img src='../img/{$image_data['icon']}' alt='{$image_data['alt']}' title='{$image_data['alt']}' border='0' /></a>";
                }
            }

            $firstcol = "<td class='column2' style='width:20px !important;'>&nbsp;$mime_img</td>\n";

            return array(0 => $firstcol);

            break;

        case 'links':
            $url_parts = explode(',', slookup('db_records', 't_module,t_record', 't_ID', $element_ID,'1'));
            $url_parts[0] = str_replace(DB_PREFIX,'',$url_parts[0]);
            if($url_parts[0]=="rts")$url_parts[0]="helpdesk";
            $url = PHPR_HOST_PATH.PHPR_INSTALL_DIR.$url_parts[0].'/'.$url_parts[0].'.php?mode=forms&amp;ID='.$url_parts[1];
            $firstcol = "<td width='20px'><a href='".$url."'><img src='../img/goto.png' alt='goto' title='goto' border='0' /></a>&nbsp;</td>\n";
            return array(0 => $firstcol);
            break;
        case 'mail':

            $result = db_query("SELECT ID, filename, tempname, filesize
                                FROM ".DB_PREFIX."mail_attach
                               WHERE parent = ".(int)$element_ID);

            $lastcol = "<td align='center'>";

            while ($row = db_fetch_row($result)) {

                if ($row[3] > 1000000)  {$fsize = floor($row[3]/1000000)." M";}
                elseif ($row[3] > 1000) {$fsize = floor($row[3]/1000)." k";}
                else {$fsize = $row[3];}
                // write data to the array for downloading
                $rnd = rnd_string();
                $file_ID[$rnd] = "$row[1]|$row[2]|$row[0]";

                $file_icon = get_file_icon($row[1]);

                $lastcol .= "<a href='mail_down.php?rnd=".$rnd.$sid."' target='_blank'><img src='../img/{$file_icon['icon']}' alt='${row[1]} ($fsize)' title='{$row[1]} ($fsize)' border='0' /></a>&nbsp;";
            }
            $lastcol .= "</td>\n";

            // check if it is a sent, received or folder element
            $result = db_query("SELECT ID, typ, date_sent, date_received, trash_can, touched, replied, forwarded
                                FROM ".DB_PREFIX."mail_client
                               WHERE ID = ".(int)$element_ID);
            $symbol = '';
            if ($row = db_fetch_row($result)) {
                if ($row[1] == 'd') {
                    if ($row[4] == 'Y') {
                        $symbol = "<img src='../img/trash_can.png' alt='".__('Trash Can')."' title='".__('Trash Can')."' border='0' />";
                    }
                    else {
                        $symbol = "<img src='../img/folder_yellow.png' alt='".__('Directory')."' title='".__('Directory')."' border='0' />";
                    }
                }
                else {
                    if ($row[7] > 0 && $row[6] > 0) {
                        $symbol = "<img src='../img/mail_replied_forwarded.gif' alt='".__('Replied and Forwarded')."' title='".__('Replied and Forwarded')."' border='0' />";
                    }
                    elseif ($row[7] > 0) {
                        $symbol = "<img src='../img/mail_forwarded.gif' alt='".__('Forwarded')."' title='".__('Forwarded')."' border='0' />";
                    }
                    elseif ($row[6] > 0) {
                        $symbol = "<img src='../img/mail_replied.gif' alt='".__('Replied')."' title='".__('Replied')."' border='0' />";
                    }
                    elseif ($row[5] == 0) {
                        $symbol = "<img src='../img/mail_unread.gif' alt='".__('Unread')."' title='".__('Unread')."' border='0' />";
                    }
                    else {
                        $symbol = "<img src='../img/mail_open.gif' alt='".__('Read')."' title='".__('Read')."' border='0' />";
                    }
                }
            }
            $firstcol = "<td width='1%' valign='middle'>$symbol</td>\n";

            return array(0 => $firstcol , 1 => $lastcol);
            break;
    }
}

/**
 * Returns the file icon according with file extension
 *
 * @param varchar $fileName name of the file
 * @param bool $extension if the provided name is only the file extension then it needs to be
 *                        set as true
 * @return varchar name of the icon file on img directory
 */
function get_file_icon($fileName = '', $extension = false) {

    // getting the file extension
    if (!$extension) {
        $file_extension = strtolower(substr($fileName,strrpos($fileName,'.') + 1));
    }
    else {
        $file_extension = $fileName;
    }

    // default file icon
    $file_icon = 'ico_unknown.gif';
    $file_alt = __('unknown');

    // image format
    $temp_array = array('jpg','jpeg','gif','tif','tiff','bmp', 'png');
    if (in_array($file_extension,$temp_array)) {
        $file_icon = 'image.png';
        $file_alt = __('image');
    }
    // doc format
    $temp_array = array('doc','dot','rtf');
    if (in_array($file_extension,$temp_array)) {
        $file_icon = 'ico_doc.gif';
        $file_alt = __('document');
    }
    // ooo calc format
    $temp_array = array('sxw','sxc','ods');
    if (in_array($file_extension,$temp_array)) {
        $file_icon = 'ooo_calc.png';
        $file_alt  = __('calc');
    }
    // ooo impress format
    $temp_array = array('sxi','odp');
    if (in_array($file_extension,$temp_array)) {
        $file_icon = 'ooo_impress.png';
        $file_alt  = __('presentation');
    }
    // ooo draw format
    $temp_array = array('sxd','odg','odp');
    if (in_array($file_extension,$temp_array)) {
        $file_icon = 'ooo_draw.png';
        $file_alt  = __('drawing');
    }
    // ooo document format
    $temp_array = array('sxw','odt','stw');
    if (in_array($file_extension,$temp_array)) {
        $file_icon = 'ooo_writer.png';
        $file_alt  = __('document');
    }
    // pdf format
    $temp_array = array('pdf');
    if (in_array($file_extension,$temp_array)) {
        $file_icon = 'ico_pdf.gif';
        $file_alt  = 'PDF';
    }
    // ppt y pps  format
    $temp_array = array('ppt','pps');
    if (in_array($file_extension,$temp_array)) {
        $file_icon = 'ico_ppt.gif';
        $file_alt  = __('presentation');
    }
    // txt format
    $temp_array = array('txt','ini');
    if (in_array($file_extension,$temp_array)) {
        $file_icon = 'ico_txt.gif';
        $file_alt  = __('plain text');
    }
    // web format
    $temp_array = array('html','xml','htm');
    if (in_array($file_extension,$temp_array)) {
        $file_icon = 'ico_web.gif';
        $file_alt  = __('web file');
    }
    // zip format
    $temp_array = array('zip','rar','tar','ace','gz');
    if (in_array($file_extension,$temp_array)) {
        $file_icon = 'ico_zip.gif';
        $file_alt  = __('compressed');
    }
    // xls format
    $temp_array = array('xls','csv');
    if (in_array($file_extension,$temp_array)) {
        $file_icon = 'ico_xls.gif';
        $file_alt  = __('spread sheet');
    }
    // sound format
    $temp_array = array('mp3','wav','mid','ape','ogg');
    if (in_array($file_extension,$temp_array)) {
        $file_icon = 'mp3.png';
        $file_alt  = __('sound');
    }
    // script format
    $temp_array = array('pl','c','cc','js','php');
    if (in_array($file_extension,$temp_array)) {
        $file_icon = 'ico_script.gif';
        $file_alt  = __('script');
    }

    return array ('icon' => $file_icon, 'alt' => $file_alt);
}

?>
