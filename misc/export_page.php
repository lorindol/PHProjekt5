<?php
/**
 * @package    misc
 * @subpackage export
 * @author     Franz Graf, $Author: polidor $
 * @licence    GPL, see www.gnu.org/copyleft/gpl.html
 * @copyright  2000-2006 Mayflower GmbH www.mayflower.de
 * @version    $Id: export_page.php,v 1.37 2008-01-02 02:21:53 polidor Exp $
 */

define('PATH_PRE','../'); 
$module = 'export';
include_once(PATH_PRE.'lib/lib.inc.php');
include_once(PATH_PRE.'lib/dbman_lib.inc.php');

$hiddenfields = hidden_fields($_REQUEST);

$radio_array = export_create_radio();

// ------------------------------------
echo set_page_header();

include_once(LIB_PATH.'/navigation.inc.php');
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
        ($_REQUEST['file'] == "costs") ||
        ($_REQUEST['file'] == "todo") ) {

    echo "
        <hr />";
    echo get_special_flags();
    echo"
        <hr />
        <br style='clear:both;'/>";
}

$use_selector_fields = false;
$use_selector_module = '';

// Select fields
if (    ($_REQUEST['file'] == "projects") ||
        ($_REQUEST['file'] == "contacts") ||
        ($_REQUEST['file'] == "helpdesk") ||
        ($_REQUEST['file'] == "notes") ||
        ($_REQUEST['file'] == "costs") ||
        ($_REQUEST['file'] == "todo") ) {
    $use_selector_fields = true;
    $use_selector_module = xss($_REQUEST['file']);
}

if ($use_selector_fields) {

    $srcslct = array();
    $dstslct = array();

    $fields = build_array($use_selector_module, 0, 'forms');

    foreach ($fields as $field_name => $field_data) {
        $dstslct[$field_name] = $field_data['form_name'];
    }

    $selstr.= "<table border='0'>\n\t<tr>\n\t<td width='200' valign='top'>\n";
    $selstr.= "\t\t".__('found elements')."<br />\n";
    $selstr.= "\t\t<select size='$size' name='export_srcs[]' multiple='multiple'>\n";
    foreach ($srcslct as $k => $v) {
        $selstr.= "\t\t\t<option value='".xss($k)."' title='".xss($fields['tisplay'][$k])."'>".xss($v)."</option>\n";
    }
    $selstr.= "\t\t</select>\n";
    $selstr.= "\t</td>\n\t<td width='50px' valign='middle'>\n";
    $selstr.= "\t\t<input class='button' type='submit' name='movsrcdst' value='&rarr;' onclick=\"moveOption('export_srcs[]','export_dsts[]'); return false;\" /><br /><br />\n";
    $selstr.= "\t\t<input class='button' type='submit' name='movdstsrc' value='&larr;' onclick=\"moveOption('export_dsts[]','export_srcs[]'); return false;\" />\n";
    $selstr.= "\t</td>\n\t<td width='200' valign='top'>\n";
    $selstr.= "\t\t".__('chosen elements')."<br />\n";
    $selstr.= "\t\t<select size='$size' name='export_dsts[]' multiple='multiple'>\n";
    foreach ($dstslct as $k => $v) {
        $v = xss(enable_vars($v));
        $selstr.= "\t\t\t<option value='".$k."' title='".$v."' selected='selected'>".$v."</option>\n";
    }
    $selstr.= "\t\t</select>\n";
    $selstr.= "\t</td>\n\t<td valign='middle'>\n";
    $selstr.= "\t\t<input class='button' type='submit' name='movdownup' value='&uarr;' onclick=\"movePosOption('export_dsts[]','up'); return false;\" /><br /><br />\n";
    $selstr.= "\t\t<input class='button' type='submit' name='movupdown' value='&darr;' onclick=\"movePosOption('export_dsts[]','down'); return false;\" />\n";
    $selstr.= "\t</td>\n\t</tr>\n</table>\n";

    echo $selstr;
}

echo '
    <br />
    <input type="submit" class="button" value="'.__('OK').'" ';
/*
if ($use_selector_fields) {
    echo ' onclick="selector_selectAll(\'export_dsts[]\');" ';
}
*/
echo ' />
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
    if ($_REQUEST['file'] == "timecard") {          $header = __('export_timecard'); }
    if ($_REQUEST['file'] == "timecard_admin") {    $header = __('export_timecard'); }
    if ($_REQUEST['file'] == "users") {             $header = __('export_users'); }
    if ($_REQUEST['file'] == "contacts") {          $header = __('export_contacts'); }
    if ($_REQUEST['file'] == "projects") {          $header = __('export_projects'); }
    if ($_REQUEST['file'] == "bookmarks") {         $header = __('export_bookmarks'); }
    if ($_REQUEST['file'] == "timeproj") {          $header = __('export_timeproj'); }
    if ($_REQUEST['file'] == "project_stat") {      $header = __('export_project_stat'); }
    if ($_REQUEST['file'] == "project_stat_date") { $header = __('export_project_stat'); }
    if ($_REQUEST['file'] == "project_stat_costs"){ $header = __('export_project_stat'); }
    if ($_REQUEST['file'] == 'todo') {              $header = __('export_todo'); }
    if ($_REQUEST['file'] == "notes") {             $header = __('export_notes'); }
    if ($_REQUEST['file'] == 'calendar') {          $header = __('export_calendar'); }
    if ($_REQUEST['file'] == 'calendar_detail'){    $header = __('export_calendar_detail'); }
    if ($_REQUEST['file'] == 'costs'){              $header = __('export_costs'); }
    return $header;
}

function export_create_link() {
    $back = "";
    if(($_REQUEST['file'] == "project_stat") || ($_REQUEST['file'] == "project_stat_date") || ($_REQUEST['file'] == "project_stat_costs")) {
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
