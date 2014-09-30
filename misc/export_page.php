<?php

// export_page.php - PHProjekt Version 5.2
// copyright    2000-2005 Albrecht Guenther  ag@phprojekt.com
// www.phprojekt.com
// Author: Franz Graf, $Author: alexander $
// $Id: export_page.php,v 1.28.2.1 2007/01/23 09:06:36 alexander Exp $

define('PATH_PRE','../'); 
$module = 'export';
include_once(PATH_PRE.'lib/lib.inc.php');

$hiddenfields = hidden_fields($_REQUEST);

$radio_array = export_create_radio();

// ------------------------------------
echo set_page_header();

include_once(LIB_PATH.'/navigation.inc.php');
$tabs = array();
$output = '<div id="global-header">';
$output .= get_tabs_area($tabs);
$output .= breadcrumb($module);
$output .= '</div>';
echo $output;

echo "
<!-- begin content -->
<div id='global-content'>
<b>".export_create_header()."</b>
        <br /><br />
        <form action='./export.php' method='post' style='margin:5px;'>
        $hiddenfields
";
foreach ($radio_array as $key => $value) {
    echo "
        <label for='$key' style='display:block;float:left;width:10em;'>$value</label>
        <input type='radio' name='medium' id='$key' value='$key' />
        <br />";
}

// filters and options for export
if (    ($_REQUEST['file'] == "calendar") ||
        ($_REQUEST['file'] == "contacts") ||
        ($_REQUEST['file'] == "users") ||
        ($_REQUEST['file'] == "projects") ||
        ($_REQUEST['file'] == "notes") ||
        ($_REQUEST['file'] == "helpdesk") ||
        ($_REQUEST['file'] == "todo") ) {
    
    echo "
        <hr />";
    echo get_special_flags();
    echo" 
        <hr />
        <br style='clear:both;'/>";
}

echo '
        <input type="submit" class="button" value="'.__('OK').'" />
        <a href="'.export_create_link().'" class="button_link_inactive">'.__('List View').'</a>
        </form>
</div>


<!-- end content -->
</div>
</body>
</html>
';


// -------------------------- Only Functions below --------------------------

/**
* creates the header depending on $file
*
* @return string header
*/
function export_create_header() {
    $header = "";
    if ($_REQUEST['file'] == "timecard") {        $header = __('export_timecard'); }
    if ($_REQUEST['file'] == "timecard_admin") {  $header = __('export_timecard'); }
    if ($_REQUEST['file'] == "users") {           $header = __('export_users'); }
    if ($_REQUEST['file'] == "contacts") {        $header = __('export_contacts'); }
    if ($_REQUEST['file'] == "projects") {        $header = __('export_projects'); }
    if ($_REQUEST['file'] == "bookmarks") {       $header = __('export_bookmarks'); }
    if ($_REQUEST['file'] == "timeproj") {        $header = __('export_timeproj'); }
    if ($_REQUEST['file'] == "project_stat") {    $header = __('export_project_stat'); }
    if ($_REQUEST['file'] == "project_stat_date") {$header = __('export_project_stat'); }
    if ($_REQUEST['file'] == 'todo') {            $header = __('export_todo'); }
    if ($_REQUEST['file'] == "notes") {           $header = __('export_notes'); }
    if ($_REQUEST['file'] == 'calendar') {        $header = __('export_calendar'); }
    if ($_REQUEST['file'] == 'calendar_detail'){  $header = __('export_calendar_detail'); }
    return $header;
}

function export_create_link() {
    $back = "";
    if(($_REQUEST['file'] == "project_stat") or($_REQUEST['file'] == "project_stat_date")){
        $back='/'.PHPR_INSTALL_DIR.'projects/projects.php?mode=stat';
    }
    else if ($_REQUEST['file'] == "users") {
        $back='/'.PHPR_INSTALL_DIR.'contacts/members.php';
    }
    else if ($_REQUEST['file'] == "timeproj") {
        $back='/'.PHPR_INSTALL_DIR.'timecard/timecard.php?submode=proj';
    }
    else{
         $back='/'.PHPR_INSTALL_DIR.xss($_REQUEST['file']).'/'.xss($_REQUEST['file']).'.php';
    }
    return $back;
}

/**
* creates the array of radiobuttons
*
* @return array (file-extension => label)
*/
function export_create_radio() {
    $radio = array();

    if ($_REQUEST['file'] == 'calendar') {
        $radio['csv'] = 'CSV';
        $radio['ics'] = 'iCal';
        $radio['xml'] = 'XML';
        $radio['xls'] = 'XLS';
    }
    else {
        if (PHPR_SUPPORT_PDF) $radio['pdf'] = "PDF";
        $radio['xml']   = "XML";
        $radio['html']  = "HTML";
        $radio['csv']   = "CSV";
        $radio['xls']   = "XLS";
        $radio['rtf']   = "RTF";
        $radio['doc']   = "DOC";
        $radio['print'] = __('print');
    }

    return $radio;
}

?>
