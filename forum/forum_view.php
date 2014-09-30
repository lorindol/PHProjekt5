<?php
/**
* forum list view script
*
* @package    forum
* @module     main
* @author     Albrecht Guenther, $Author: polidor $
* @licence    GPL, see www.gnu.org/copyleft/gpl.html
* @copyright  2000-2006 Mayflower GmbH www.mayflower.de
* @version    $Id: forum_view.php,v 1.55.2.4 2007/08/11 16:27:28 polidor Exp $
*/
if (!defined('lib_included')) die('Please use index.php!');

// check role
if (check_role("forum") < 1) die("You are not allowed to do this!");

// diropen_mode($element_mode, $element_ID);
read_mode($module);
archive_mode($module);

// Archive Flag
// set_archiv_flag = 1 -> Move to archive
// set_archiv_flag = 0 -> Take back from archive
$set_read_flag = isset($set_read_flag) ? (int) $set_read_flag : 0;
if (isset($set_archiv_flag))   set_archiv_flag($ID_s, $module, $set_archiv_flag);
if ($set_read_flag > 0)         set_read_flag($ID_s, $module);

$acc= "(von = ".(int)$user_ID." or acc like 'system' or
                             ((acc like 'group'  or acc like '%$user_kurz%') and
                             ".DB_PREFIX."forum.gruppe = ".(int)$user_group.")) ";
$where = " where "; // we need a default when there is no filter
// open and close node
if ($element_mode == "open") {
  $arrproj[$ID] = "1";
  $ID = 0;
}
elseif ($element_mode == "close") {
  $arrproj[$ID] = "";
  $ID = 0;
}

//tabs
$tabs = array();
$output = '<div id="global-header">';
$output .= get_tabs_area($tabs);
if(!isset($newbei)){
    $newbei = '';
}
if(!isset($newfor)){
    $newfor = '';
}
$output .= breadcrumb($module, breadcrumb_data($fID, $ID, $newbei, $newfor, $mode));

if(!isset($do)){
    $do = '';
}
if($do=="niente");
// Liste Foren
elseif(empty($fID) || $fID == -1) {
    $result = db_query("SELECT * from ".DB_PREFIX."forum $where $acc
    AND (parent=0 OR parent IS NULL) ")or db_die();
    $liste= make_list($result);

    // button bar
    $buttons = array();
    // form start
    $hidden = array('mode' => 'forms');
    if(SID) $hidden[session_name()] = session_id();
    $buttons[] = array('type' => 'form_start', 'hidden' => $hidden);
    // create new forum button
    if ( check_role("forum") > 1 ) {
        $buttons[] = array('type' => 'submit', 'name' => 'newfor', 'value' => __('New'), 'active' => false);
    }
    // form end
    $buttons[] = array('type' => 'form_end');
    // delete forum
    $buttons[] = array('type' => 'link', 'href' => $_SERVER['SCRIPT_NAME'].'?mode=options&amp;tree_mode='.$tree_mode.'&amp;fID='.(int) $fID.$sid.'&amp;csrftoken='.make_csrftoken(), 'text' => __('Delete forum'), 'active' => false);
    $output .='</div>';
    $output .='<div id="global-content">';
    $output .= get_buttons_area($buttons);
    $output .= get_top_page_navigation_bar();
    $output .= get_status_bar();
    $output .= '<a name="content"></a><br />';

    $output.="<table class=\"ruler\" id=\"contacts\" summary=\"__('In this table you can find all forums listed')\" width=\"100%\">
    <thead>
    <tr>
        <th class=\"column-1\" scope=\"col\" title=\"".__('Forum')."\">".__('Forum')."</th>
        <th scope=\"col\" title=\"".__('Description')."\">".__('Description')."</th>
        <th scope=\"col\" title=\"".__('Topics')."\">".__('Topics')."</th>
        <th scope=\"col\" title=\"".__('Threads')."\">".__('Threads')."</th>
        <th scope=\"col\" title=\"".__('Latest Thread')."\">".__('Latest Thread')."</th>
    </tr>
</thead><tbody>";
    $int=0;
    if($max>count($liste))$max=count($liste);
    if(count($liste)>0){
        for ($i=($_SESSION['page']['forum']*$perpage); $i < $max; $i++) {
            $cons ="AND topic='0'";
            $cons1 = "AND antwort = 0";
            $result = db_query("SELECT * from ".DB_PREFIX."forum where ID = ".(int)$liste[$i]) or db_die();
            $row = db_fetch_row($result);
            $output1='';
            tr_tag("forum.php?fID=$row[0]",'',$row[0]);
            $output.=$output1;
            $output.=" <td scope='row' class=\"column-1\">
        <a href='forum.php?fID=$row[0]'> $row[3]</a></td>
        <td>".html_out($row[4])."</td>
        <td>".get_articles($row[0],"parent","$cons1")."</td>
        <td>".get_articles($row[0], "parent","$cons2")."</td>
        <td>".get_lastarticle($row[0], "parent","$cons1")."</td>
        </tr>\n";
            $int++;
        }
    }
    else $output.="<tr><td></td><td></td><td></td><td></td><td></td></tr>";
    $output .= "</tbody></table>";


    $output .= '<br />';
    $output .= get_bottom_page_navigation_bar();

}
// show list of threads if no posting is selected
else if ($fID){
    $output .='</div>';
    $output .= threads($fID);
}

echo "$output<br /><br />";

$_SESSION['arrproj'] =& $arrproj;
?>
