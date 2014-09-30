<?php
/**
* members list view
*
* @package    contacts
* @module     members
* @author     Albrecht Guenther, $Author: albrecht $
* @licence    GPL, see www.gnu.org/copyleft/gpl.html
* @copyright  2000-2006 Mayflower GmbH www.mayflower.de
* @version    $Id: members_view.php,v 1.11.2.1 2007/08/20 11:49:26 albrecht Exp $
*/
if (!defined('lib_included')) die('Please use index.php!');

// check role
if (check_role('contacts') < 1) die('You are not allowed to do this!');

// diropen_mode($element_mode, $element_ID);
sort_mode($module,'nachname');
$sort = $f_sort['members']['sort'];
$direction = $f_sort['members']['direction'];

// set new direction
if ($direction == "ASC") {
    $new_direction  = "DESC";
} else {
    $new_direction  = "ASC";
}

// ************
// context menu
// entries for right mouse menu - action for single record

$csrftoken = make_csrftoken();

// tabs
$tabs = array();

$tmp_contacts = './contacts.php?mode=view&amp;direction='.$new_direction.'&amp;sort=nachname&amp;tree_mode='.$tree_mode.$sid;
$tmp_members = './members.php?mode=view&amp;direction='.$new_direction.'&amp;sort=nachname&amp;tree_mode='.$tree_mode.$sid;
$tabs[] = array('href' => $tmp_members, 'active' => true, 'id' => 'tab1', 'target' => '_self', 'text' => __('Group members'), 'position' => 'left');
$tabs[] = array('href' => $tmp_contacts, 'active' => false, 'id' => 'tab2', 'target' => '_self', 'text' => __('External contacts'), 'position' => 'left');

$exp = get_export_link_data('users');
$tabs[] = array('href' => $exp['href'], 'active' => $exp['active'], 'id' => 'export', 'target' => '_self', 'text' => $exp['text'], 'position' => 'right');
unset($exp);

$output = '<div id="global-header">';
$output .= get_tabs_area($tabs);
$output .= '</div>';
$output .= '<div id="global-content">';
// button bar
$buttons = array();

$where = '';
if ($user_group) {  // select user from this group
    $result = db_query("SELECT COUNT(".DB_PREFIX."users.ID)
                          FROM ".DB_PREFIX."users, ".DB_PREFIX."grup_user
                         WHERE ".DB_PREFIX."users.ID = user_ID
                               AND grup_ID = ".(int)$user_group." 
                               AND ".DB_PREFIX."users.status = 0
                               AND ".DB_PREFIX."users.usertype = 0");
} else {    // if user is not assigned to a group or group system is not activated
    $result = db_query("SELECT COUNT(ID)
                          FROM ".DB_PREFIX."users
                         WHERE status = 0
                           AND usertype = 0") or db_die();
}

$output.= "<table class=\"ruler\" id=\"members\" summary=\"$tc_sum\"><thead><tr>\n";

$e1 = "<th class=\"column2\" scope=\"col\" width='";
$e2 = "%' id='";
$e21 = "";
$e22 = "' ><a class='white' href='members.php?mode=view".$sid."&amp;sort=";
$e3  = "&amp;direction=$new_direction$sid'>";
$e4  = "</a></th>\n";

$output .= $e1.'15'.$e2.'nachname'.$e22.'nachname'.$e3.__('Family Name').$e4;
$output .= $e1.'15'.$e2.'vorname'.$e22.'vorname'.$e3.__('First Name').$e4;
$output .= $e1.'15'.$e2.'firma'.$e22.'firma'.$e3.__('Company').$e4;
$output .= $e1.'10'.$e2.'email'.$e22.'email'.$e3.'Email'.$e4;
$output .= $e1.'10'.$e2.'tel1'.$e22.'tel1'.$e3.__('Phone').' 1'.$e4;
$output .= $e1.'10'.$e2.'tel2'.$e22.'tel2'.$e3.__('Phone').' 2'.$e4;
$output .= $e1.'10'.$e2.'mobil'.$e22.'mobil'.$e3.__('Phone').' mobil'.$e4;
$output .= $e1.'10'.$e2.'fax'.$e22.'fax'.$e3.__('Fax').$e4;
$output .= "</tr>\n</thead><tbody>\n";

if (!$sort) $sort = 'nachname';

if ($user_group) {  // select user from this group
    $sql = "SELECT ".DB_PREFIX."users.ID, vorname, nachname, kurz, firma, email,
                   tel1, tel2, mobil, fax, ldap_name
              FROM ".DB_PREFIX."users, ".DB_PREFIX."grup_user
             WHERE ".DB_PREFIX."users.ID = user_ID
               AND grup_ID = ".(int)$user_group." 
               AND ".DB_PREFIX."users.status = 0
               AND ".DB_PREFIX."users.usertype = 0
             ORDER BY ".qss($sort)." $direction";
} else {    // if user is not assigned to a group or group system is not activated
    $sql = "SELECT ID, vorname, nachname, kurz, firma, email,
                   tel1, tel2, mobil, fax, ldap_name
              FROM ".DB_PREFIX."users
             WHERE status = 0
               AND usertype = 0
             ORDER BY ".qss($sort)." $direction";
}
$result = db_query($sql) or db_die();

$i = 0;
while ($row = db_fetch_row($result)) {
    $row_data[$i] = $row;
    $i++;
}

$result = db_query($sql) or db_die();
$liste = make_list($result);

$maxnr     = (count($row_data) < ($_SESSION['page'][$module]+1)*$perpage) ? count($row_data) : ($_SESSION['page'][$module]+1)*$perpage;
$nr_record = $_SESSION['page'][$module]*$perpage;

$output .= get_buttons_area($buttons);
$output .= '<div class="hline"></div>';
$output .= get_status_bar();
$output .= get_top_page_navigation_bar();
$output .= '<a name="content"></a>';

while ($nr_record < $maxnr) {
    if ((PHPR_LDAP != 0) && ($ldap_conf[$row_data[$nr_record][9]]["ldap_sync"] == "2")) {
        // fetch user data from ldap
        get_ldap_usr_data($row_data[$nr_record][10]);
    }

    $row_data[$nr_record] = explode("||", html_out(implode("||", $row_data[$nr_record])));

    // look whether a pic is in a dir with name of group's short name, e.g. "dem"
    $ref = "members.php?mode=forms&amp;ID=".$row_data[$nr_record][0]."&amp;sort=$sort&amp;direction=$direction".$sid;
    tr_tag($ref, "", $row_data[$nr_record][0], 'nachname');

    // optional img of this user
    $output .= "<tr><td><a href='$ref'>".$row_data[$nr_record][2]."</a></td>\n";
    $output .= "<td>".$row_data[$nr_record][1]."&nbsp;&nbsp;</td>\n";
    $output .= "<td>".$row_data[$nr_record][4]."&nbsp;</td>\n";
    $output .= "<td>".showmail_link($row_data[$nr_record][5])."&nbsp;</td>\n";
    $output .= "<td>".$row_data[$nr_record][6]."&nbsp;</td>\n";
    $output .= "<td>".$row_data[$nr_record][7]."&nbsp;</td>\n";
    $output .= "<td>".$row_data[$nr_record][8]."&nbsp;</td>\n";
    $output .= "<td>".$row_data[$nr_record][9]."&nbsp;</td>\n</tr>\n";
    $nr_record++;
}

$output .= "</tbody></table><br />\n";

$output .= get_bottom_page_navigation_bar();

echo $output;

$_SESSION['arrproj'] =& $arrproj;

?>
