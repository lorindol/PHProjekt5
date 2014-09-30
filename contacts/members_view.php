<?php
/**
 * members list view
 *
 * @package    contacts
 * @subpackage members
 * @author     Albrecht Guenther, $Author: florian $
 * @licence    GPL, see www.gnu.org/copyleft/gpl.html
 * @copyright  2000-2006 Mayflower GmbH www.mayflower.de
 * @version    $Id: members_view.php,v 1.22 2007-11-19 16:09:15 florian Exp $
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
                               AND ".DB_PREFIX."users.is_deleted is NULL
                               AND (".DB_PREFIX."users.usertype = 0 OR 
                                    ".DB_PREFIX."users.usertype = 2 OR 
                                    ".DB_PREFIX."users.usertype = 3)");
} else {    // if user is not assigned to a group or group system is not activated
    $result = db_query("SELECT COUNT(ID)
                          FROM ".DB_PREFIX."users
                         WHERE status = 0
                           AND is_deleted is NULL
                           AND (".DB_PREFIX."users.usertype = 0 OR 
                                ".DB_PREFIX."users.usertype = 2 OR 
                                ".DB_PREFIX."users.usertype = 3)") or db_die();
}

$output.= "<table class=\"ruler\" id=\"members\" summary=\"$tc_sum\"><thead><tr>\n";

$e1 = "<th class=\"column2\" scope=\"col\" width='";
$e2 = "%' id='";
$e21 = "";
$e22 = "' ><a class='white' href='members.php?mode=view".$sid."&amp;sort=";
$e3  = "&amp;direction=$new_direction$sid'>";
$e4  = "</a></th>\n";

if (defined('PHPROJEKT_HAVE_VACATION') && PHPROJEKT_HAVE_VACATION == 1) {
    $output .= '<th class="column2" width="5%">'.__('Status')."</th>\n";
}

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
               AND ".DB_PREFIX."users.is_deleted is NULL
               AND (".DB_PREFIX."users.usertype = 0 OR 
                    ".DB_PREFIX."users.usertype = 2 OR 
                    ".DB_PREFIX."users.usertype = 3)
             ORDER BY ".qss($sort)." $direction";
} else {    // if user is not assigned to a group or group system is not activated
    $sql = "SELECT ID, vorname, nachname, kurz, firma, email,
                   tel1, tel2, mobil, fax, ldap_name
              FROM ".DB_PREFIX."users
             WHERE status = 0
                AND is_deleted is NULL
                AND (".DB_PREFIX."users.usertype = 0 OR 
                    ".DB_PREFIX."users.usertype = 2 OR 
                    ".DB_PREFIX."users.usertype = 3)
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

$output .= get_module_tabs($tabs,$buttons, '');
$output .= '<div class="hline"></div>';
$output .= get_status_bar();
$output .= get_top_page_navigation_bar();
$output .= '<a name="content"></a>';


// START vacation/absence check
if (defined('PHPROJEKT_HAVE_VACATION') && PHPROJEKT_HAVE_VACATION == 1) {
    $vac_data = get_vacation_data();
    $bg = ' style="background: %s;text-align:center;%s"';
}
// END vacation/absence check

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
    $output .= "<tr>\n";
    
    // START vacation/absence check
    if (defined('PHPROJEKT_HAVE_VACATION') && PHPROJEKT_HAVE_VACATION == 1) {
        $v_user = $row_data[$nr_record][0];
        $vnow = date('Hi');
        $v_official_end_time = 1600;
        
        $vborder = '';
        if (isset($vac_data['offtime'][$v_user]) && $vac_data['offtime'][$v_user] == 1) {
            $vcolor = '#f17a71'; // light red
            $vtext = __('absent');
        } else if (isset($vac_data['timecard'][$v_user]) && $vac_data['timecard'][$v_user] == 1) {
            $vcolor = '#8af56d'; // light green
            $vtext = __('present');
        } else if ((isset($vac_data['timecard'][$v_user]) && $vac_data['timecard'][$v_user] == 2) && $vnow >= $v_official_end_time) {
            $vcolor = '#f18f6f'; // medium orange
            $vtext = __('gone');
        } else {
            $vcolor = '#f7ef6b'; // light orange
            $vtext = __('expected');
        }
        $vHasApp = false;
        if (isset($vac_data['cal'][$v_user])) {
            foreach ($vac_data['cal'][$v_user] as $vtmp) {
                $vtmp2 = split('-', $vtmp);
                // this user has an appointment right now
                if ($vtmp2[0] <= $vnow && $vnow <= $vtmp2[1]) {
                    $vcolor = '#9fc1ff'; // light blue
                    $vtext = __('appointment');
                }
                // this user has any appointment today
                $vHasApp = true;
            }
            if ($vHasApp) {
                $vborder = 'border: 2px #5555ff solid'; // dark blue
                $vlink = '../calendar/calendar.php?action_selector_to_combi_ok=1&view=3&dsts%5B%5D='.$v_user; // Link to the Users Calendar
                sort($vac_data['cal'][$v_user]);
                $v_cal = preg_replace("/(\d{2})(\d{2})/", "\\1:\\2", $vac_data['cal'][$v_user]);
                $vtooltip = __('Appointments').':<br/>'.str_replace('---------', '('.__('full-time').')', implode(',<br/>', $v_cal));
                $vtooltip = '<span class="tooltip">'.str_replace('&#10;','', $vtooltip).'</span>';
            }
        }
        if (!$vHasApp) {
            $vtooltip = false;
            $vlink = false;
        }
        
        $vstyle = sprintf($bg, $vcolor, $vborder);
        if ($vtooltip) $vtext .= $vtooltip;
        if ($vlink) $vtext = '<a href="'.$vlink.'">'.$vtext.'</a>';
        $output .= '<td'.$vstyle.'>'.$vtext."&nbsp;</td>\n";
    }
    // END vacation/absence check
    
    
    $output .= "<td><a href='$ref'>".$row_data[$nr_record][2]."</a></td>\n";
    $output .= "<td>".$row_data[$nr_record][1]."&nbsp;&nbsp;</td>\n";
    $output .= "<td>".$row_data[$nr_record][4]."&nbsp;</td>\n";
    $output .= "<td>".showmail_link($row_data[$nr_record][5])."&nbsp;</td>\n";
    $output .= "<td>".$row_data[$nr_record][6]."&nbsp;</td>\n";
    $output .= "<td>".$row_data[$nr_record][7]."&nbsp;</td>\n";
    $output .= "<td>".$row_data[$nr_record][8]."&nbsp;</td>\n";
    $output .= "<td>".$row_data[$nr_record][9]."&nbsp;</td>\n";
    $output .= "</tr>\n";
    $nr_record++;
}

$output .= "</tbody></table><br />\n";

$output .= get_bottom_page_navigation_bar();

echo $output;

$_SESSION['arrproj'] =& $arrproj;

/**
 * If the vacation module is installed, show a summary of group members' 
 * absence status based on vacation, timecard and calendat
 *
 * @return array
 */
function get_vacation_data() {
    $vtoday = date("Y-m-d");
    $vnow = date('Hi');
    $vac_data['offtime'] = array();
    $vac_data['timecard'] = array();
    $vac_data['cal'] = array();
    
    $vsql = sprintf('SELECT id_user, begin, end
                       FROM %s', DB_PREFIX.'vacation');
    $vresult = db_query($vsql) or db_die();
    while ($vres = db_fetch_row($vresult)) {
        if ($vres[1] <= $vtoday && $vtoday <= $vres[2]) {
            $vuid = $vres[0];
            $vac_data['offtime'][$vuid] = 1;
        }
    }
    unset($vresult);
    unset($vres);
    unset($vuid);
    
    $vsql = sprintf('SELECT users, anfang, ende, id
                       FROM %s
                      WHERE datum="%s"
                   ORDER BY id ASC', DB_PREFIX.'timecard', $vtoday);
    $vresult = db_query($vsql) or db_die();
    while ($vres = db_fetch_row($vresult)) {
        if ($vres[1] <= $vnow && is_null($vres[2])) {
            $vuid = $vres[0];
            $vac_data['timecard'][$vuid] = 1;
        } elseif ($vres[2] < $vnow) {
            $vuid = $vres[0];
            $vac_data['timecard'][$vuid] = 2;
        }
    }
    unset($vresult);
    unset($vres);
    unset($vuid);
    
    $vsql = sprintf('SELECT an, anfang, ende 
                       FROM %s
                      WHERE datum="%s"', DB_PREFIX.'termine', $vtoday);
    $vresult = db_query($vsql) or db_die();
    while ($vres = db_fetch_row($vresult)) {
        $vuid = $vres[0];
        $vac_data['cal'][$vuid][] = $vres[1].'-'.$vres[2];   
    }
    unset($vresult);
    unset($vres);
    unset($vuid);
    
    return $vac_data;
}
?>
