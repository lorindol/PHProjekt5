<?php
/**
* selector for internal users
*
* @package    selector
* @module     main
* @author     Martin Brotzeller, Gustavo Solt, $Author: alexander $
* @licence    GPL, see www.gnu.org/copyleft/gpl.html
* @copyright  2000-2006 Mayflower GmbH www.mayflower.de
* @version    $Id: datasource_users.php,v 1.54.2.1 2007/01/24 14:27:36 alexander Exp $
*/
if (!defined('lib_included')) die('Please use index.php!');
// since lib.inc.php is already included, lib_path contains the correct value
include_once(LIB_PATH.'/selector/selector.inc.php');

/** 
 * fetch_fields() - Query the data source 
 *
 * @param options Array containing the definition of the data source
 *                'table'     - Table to be queried 
 *                'where'     - where criteria for this table
 *                'order'     - comma separated list of fields to sort by 
 *                'ID'        - name of the id column
 *                'display'   - array containing the fields to be displayed
 *                'dstring'   - Format string for the fields to be displayed
 *                'tisplay'   - array containing the column titles in xhtml
 *                'tstring'   - format string for the titles
 *                'filter'    - array containing data to display and set the filters.
 *                'limit'     - maximum number of entries returned 
 * @param preselect Array containing selected options
 * @access public
 */
function usersfetch_fields($options,$preselect) {
    global $selektor_answer, $filter_answer;

    // default values if options fields are not set
    $maxdisp = (isset($options['limit'])) ? $options['limit'] : PHPR_FILTER_MAXHITS;
    if (!isset($options['table'])) {
        $options['table'] = 'users';
    }
    if (is_array($options['table'])) {
        $options['table'] = implode(", ".DB_PREFIX, $options['table']);
    }

    $where = "";
    if (is_array($options['where'])) {
        $where = implode(" AND ", $options['where']);
    }
    if ($where == "") {
        $where = "1=1";
    }
    if (!isset($options['order'])) {
        $options['order'] = "nachname";
    }

    $order = "ORDER BY ".$options['order'];
    if (!isset($options['ID'])) {
        $options['ID'] = "ID";
    }
    if (!isset($options['display'])) {
        $options['display'] = array("vorname","nachname");
    }
    if (!isset($options['dstring'])) {
        $options['dstring'] = "%s";
        for ($i=1; $i<count($options['display']);$i++) {
            $options['dstring'] .= " %s";
        }
    }
    if (!isset($options['tisplay'])) {
        $options['tisplay'] = $options['display'];
    }
    if (!isset($options['tstring'])) {
        $options['tstring'] = $options['dstring'];
        for ($i=1; $i<count($options['tisplay']);$i++) {
            $options['tstring'] .= " %s";
        }
    }

    // collect data and return search hits
    $fields = $options['ID'].",".implode(",", $options['display']);

    // Show a quickadd result
    if (isset($filter_answer) && !empty($filter_answer)) {
        $filter_answer += $preselect;
        foreach($filter_answer as $k => $v) {
            // return if there are too many hits
            if ($maxdisp>0 && count($hits['display'])>$maxdisp) {
                $hits['overflow'] = true;
                break;
            }

            $query = "SELECT ".$options['ID'].", u.nachname, u.vorname, gr.kurz, gr.name
                FROM ".DB_PREFIX."grup_user AS g
           LEFT JOIN ".DB_PREFIX."users AS u ON g.user_ID=u.ID
           LEFT JOIN ".DB_PREFIX."gruppen AS gr ON g.grup_ID=gr.ID
               WHERE ".$options['ID']." = ".xss($k);
            $result = db_query($query) or db_die();
    
            $row = db_fetch_row($result);
            $hits['display'][$row[0]] = "$row[1], $row[2] ($row[3])";
            $hits['tisplay'][$row[0]] = "$row[1], $row[2] ($row[4])";
        }
    } else {
        // normal results
        // optimized query for big numbers of users and groups
        $query = "SELECT ".$options['ID'].", u.nachname, u.vorname, gr.kurz, gr.name
                    FROM ".DB_PREFIX."grup_user AS g
               LEFT JOIN ".DB_PREFIX."users AS u ON g.user_ID=u.ID
               LEFT JOIN ".DB_PREFIX."gruppen AS gr ON g.grup_ID=gr.ID
                   WHERE $where $order";
        $result = db_query($query) or db_die();

        $hits = array('display'=>array(), 'tisplay'=>array(), 'overflow'=>false);
        while ($row=db_fetch_row($result)) {
            // return if there are too many hits
            if ($maxdisp>0 && count($hits['display'])>$maxdisp) {
                $hits['overflow'] = true;
                break;
            }
            $hits['display'][$row[0]] = "$row[1], $row[2] ($row[3])";
            $hits['tisplay'][$row[0]] = "$row[1], $row[2] ($row[4])";
        }
    }

    // No result found
    if (!isset($selektor_answer)) {
        if (count($hits['display']) == 0) $selektor_answer = __('there were no hits found.')."<br /><br />\n";
        else {
            $tmp_srcslct = array();
            $tmp_dstslct = array();
            if (!empty($hits['display'])) {
                foreach ($hits['display'] as $k => $v) {
                    if (isset($preselect[$k])) $tmp_dstslct[$k] = $v;
                    else                       $tmp_srcslct[$k] = $v;
                }
            }
            if (count($tmp_srcslct) == 0) $selektor_answer = __('there were no hits found.')."<br /><br />\n";
            unset($tmp_srcslct);
            unset($tmp_dstslct);
        }
    }

    return $hits;
}

/** 
 * Display Filters 
 *
 * @param $options    - options, see fetch_fields
 * @param $object     - serialized selector object containing selected entries 
 * @param $name       - name of the current filter objects 
 * @access public
 */
function usersdisplay_filters1($options, $object, $name, $getprm=array()) {
    global $filters, $usersextras, $sid, $getstring;

    $_SESSION['filters'] =& $filters;
    $sarr =& $filters[$name];

    $fform = "
    <input type='hidden' name='sthis' value='$object' />
    <input type='hidden' name='filterform' value='done' />
    <br />
    <table border='0' class='selector_head'>
        <tr>";

    if (isset($options['title'])) {
        $fform .= "
            <td class='selector_head'>
                <span class='selector_title'>".str_replace("<br />", " ", $options['title'])."</span>
            </td>
        </tr>
        <tr>";
    }

    if (!empty($options['filter']['text'])) {
        $fform .= "
            <td class='selector_head'>
                <table border='0' class='selector_filter'>
                    <tr>
                        <td colspan='3' class='selector_filter'>
                            <b>".__("set filter")."</b>
                        </td>
                    </tr>
                    <tr>";
    }

    if (!empty($options['filter']['text'])) {
        $fform .= "
                        <td class='selector_filter'>
                            <select class='selector_filter' name='textfilter' tabindex='10'>\n";
        foreach ($options['filter']['text'] as $key => $value) {
            $key = xss($key);
            $value = xss($value);
            $fform .= "<option value='$key' title='$value'>$value</option>\n";
        }
        $fform .= "                            </select>
                        </td>
                        <td class='selector_filter'>
                            <select name='textfiltermode' tabindex='11'>
                                <option value='begins with' title='".__('starts with')."'>".__('starts with')."</option>
                                <option value='contains' selected='selected' title='".__('contains')."'>".__('contains')."</option>
                                <option value='ends with' title='".__('ends with')."'>".__('ends with')."</option>
                                <option value='is equal' title='".__('exact')."'>".__('exact')."</option>
                            </select>
                        </td>
                        <td class='selector_filter'>
                            <input type='text' name='textfilterstring' size='40' maxlength='20' tabindex='12' style='width:200px;' />
                        </td>\n";
    }

    if (!empty($options['filter']['text'])) {
        $fform .= "                    </tr>
                </table>
            </td>\n";
    } else {
        $fform .= "                        <td></td>\n";
    }

    $fform .= "                        <td rowspan='2' class='selector_head_submit_cell' style='vertical-align:top;'>";
    $fform .= '<div style="margin-top: 20px;">'.get_go_button_with_name('filterset')."</div></td>";
    $fform .= "
                    </tr>
                    <tr>
                        <td class='selector_head'>
                            <table border='0' class='selector_filter'>";

    // Selector object
    $sobject = unserialize(urldecode($object));
    $selector_name = $sobject['this']->name;
    $selector_type = $sobject['this']->type;

    // Show quick add
    if (!empty($usersextras)) {
        $fform .= "
                    <tr>
                        <td class='selector_filter' colspan='3'>
                            <b>".__("quickadd")."</b>
                        </td>
                    </tr>\n";

        foreach ($usersextras as $key=>$val) {
            $val = xss($val);
            $fform .= $val['getform']($selector_name);
        }
    }

    $fform .= "
                </table>
            </td>
        </tr>
    </table>\n";

    if (is_array($sarr) && count($sarr) > 0) {
        $hrefprm = '';
        if (count($getprm) > 0) {
            foreach ($getprm as $k=>$v) {
                $hrefprm .= "&amp;$k=".xss($v);
            }
        }

        $filter_list_arr = array();
        foreach ($sarr as $k=>$v) {
            if ($_SESSION[$selector_name]['javascript']) {
                $filter_link_1 = xss($_SERVER['SCRIPT_NAME'])."?filterdel=$k".$hrefprm;
                $filter_link_2 = "&amp;".$getstring.$sid;
                $filter_list_arr[] = " <a href='#' onclick=\"get_filter_delete_link('".$filter_link_1."', '".$filter_link_2."', '".$selector_type."', '".$selector_name."');\" ".
                                     "class='filter_active' title='".__('Delete')."'>&nbsp;".str_replace("%", "", $v)."&nbsp;</a>\n";
            } else {
                $filter_list_arr[] = " <a href='".xss($_SERVER['SCRIPT_NAME'])."?filterdel=$k".$hrefprm."&amp;".$getstring.$sid.
                                     "' class='filter_active' title='".__('Delete')."'>&nbsp;".str_replace("%", "", $v)."&nbsp;</a>\n";
            }                                 
        }
        // link to delete all filter
        if ($_SESSION[$selector_name]['javascript']) {
            $filter_link_1 = xss($_SERVER['SCRIPT_NAME'])."?filterdel=-1".$hrefprm;
            $filter_link_2 = "&amp;".$getstring.$sid;
            $fform .= "<b>".__('Filtered').":</b> ".implode('+', $filter_list_arr).
                      "&nbsp;&nbsp;|&nbsp;&nbsp;<a href='#' onclick=\"get_filter_delete_link('".$filter_link_1."', '".$filter_link_2."', '".$selector_type."', '".$selector_name."');\" ".
                      "class='filter_manage' title='".__('Delete all filter')."'>".__('Delete all filter')."</a>\n";            
        } else {
            $fform .= "<b>".__('Filtered').":</b> ".implode('+', $filter_list_arr).
                      "&nbsp;&nbsp;|&nbsp;&nbsp;<a href='".xss($_SERVER['SCRIPT_NAME'])."?filterdel=-1".$hrefprm."&amp;".$getstring.$sid.
                      "' class='filter_manage' title='".__('Delete all filter')."'>".__('Delete all filter')."</a>\n";
        }
        $fform .= "<br /><br />\n";
    }

    return $fform;
}

/** 
 * Parse filter and put it in the session
 *
 * @param  $object    - current selection object which gets the filter added 
 * @access
 */
function usersparse_filters(&$object) {
    global $textfilter, $textfilterstring, $textfiltermode, $filters, $filterform;

    if ($filterform == "done") {
        $_SESSION['filters'] =& $filters;

        $sarr =& $filters[$object->name];

        // only put the filter['text'] strings
        $options = $object->sourcedata['filter']['text'];
        
        if (    isset($textfilterstring) &&
                $textfilterstring != ""  &&
                array_key_exists($textfilter,$options)) {
            $c = "$textfilter ";
            switch ($textfiltermode) {
                case 'contains':
                    $c .= "like '%$textfilterstring%'";
                    break;
                case 'begins with':
                    $c .= "like '$textfilterstring%'";
                    break;
                case 'ends with':
                    $c .= "like '%$textfilterstring'";
                    break;
                case 'is equal':
                    $c .= "= '$textfilterstring'";
                    break;
            }
            // avoid duplicate entries
            if (!is_array($sarr) || !in_array($c, $sarr)) {
                $sarr[] = $c;
            }
        }
    }
}

/** 
 * Display input and select box for profiles 
 *
 * @param string selector_name - Name of the selector
 * @return string
 * @access public
 */
function usersextra_profiles($selector_name) {
    global $user_ID, $user_kurz;

    $options = "<option selected='selected'></option>\n";
    $query = "SELECT ID, bezeichnung, von
                FROM ".DB_PREFIX."profile
               WHERE von = ".(int)$user_ID." 
                  OR acc LIKE '%\"$user_kurz\"%'";
    $res = db_query($query) or db_die();
    $num_res = 0;
    while ($row = db_fetch_row($res)) {
        $von = $row[1].' ('.str_replace(',', ', ', slookup('users', 'nachname,vorname', 'ID', $row[2], true)).')';
        $options .= "<option value='".$row[0]."' title='$von'>$von</option>\n";
        $num_res++;
    }

    // put disabled or not the select
    if ($num_res == 0) {
        $disabled = "disabled='disabled'";
    } else {
        $disabled = "";
    }

    $ret = "
    <tr>
        <td class='selector_filter'>".__('add profile').":</td>
        <td class='selector_filter'>&nbsp;</td>
        <td class='selector_filter'>
            <select class='selector_filter' name='usersextra_profile' onchange=\"selector_selectAll('".$selector_name."dsts[]'); submit();\" style='width:200px;' ".$disabled.">\n";
    $ret .= $options;
    $ret .= "</select>
        </td>
    </tr>\n";

    return $ret;
}

/**
 * Evaluate select box extra_profiles 
 *
 * @access public
 */
function userseval_extra_profiles() {
    global $usersextra_profile, $user_ID, $selektor_answer;

    $addprof = array();
    if (isset($usersextra_profile) && $usersextra_profile!=0) {
        $personen = unserialize(slookup('profile','personen','ID',$usersextra_profile,'1'));
        $kurz = "'".implode("','", $personen)."'";
        $query = "SELECT ID
                    FROM ".DB_PREFIX."users
                   WHERE kurz IN ($kurz)";
        $res = db_query($query) or db_die();
        while ($row = db_fetch_row($res)) {
            $addprof[$row[0]] = $row[0];
        }
        $selektor_answer = __('Added profile')."<br /><br />\n";
    }
    if (count($addprof) == 0) $selektor_answer = __('there were no hits found.')."<br /><br />\n";
        
    return $addprof;
}

/** 
 * display input and select box for projects
 *
 * @param string selector_name - Name of the selector
 * @return string
 * @access public
 */
function usersextra_projects($selector_name) {
    global $user_ID, $user_kurz, $sql_user_group;
    
    $options = "<option selected='selected'></option>\n";
    $query = "SELECT ID, name
                FROM ".DB_PREFIX."projekte
               WHERE (acc LIKE 'system' OR ((von = ".(int)$user_ID." OR acc LIKE 'group' OR acc LIKE '%\"$user_kurz\"%') AND $sql_user_group))";
    $res = db_query($query) or db_die();
    $num_res = 0;
    while ($row = db_fetch_row($res)) {
        $options .= "<option value='".$row[0]."' title='".$row[1]."'>".$row[1]."</option>\n";
        $num_res++;
    }
    // put disabled or not the select
    if ($num_res == 0) {
        $disabled = "disabled='disabled'";
    } else {
        $disabled = "";
    }

    $ret = "
    <tr>
        <td class='selector_filter'>".__('add participants of project').":</td>
        <td class='selector_filter'>&nbsp;</td>
        <td class='selector_filter'>
        <select class='selector_filter' name='usersextra_project' onchange=\"selector_selectAll('".$selector_name."dsts[]'); submit();\" style='width:200px;' ".$disabled.">\n";
    $ret .= $options;
    $ret .= "</select>
        </td>
    </tr>\n";

    return $ret;
}

/** 
 * Evaluates the select box extra_project
 *
 * @access public
 */
function userseval_extra_projects() {
    global $usersextra_project, $selektor_answer, $user_ID, $user_kurz, $sql_user_group;
    global $user_group;

    $addproj = array();
    if (isset($usersextra_project) && $usersextra_project != 0) {
        $query = "SELECT user_ID
                FROM ".DB_PREFIX."project_users_rel
               WHERE project_ID = ". intval($usersextra_project) ." ";
        $res = db_query($query) or db_die();
        while ($row = db_fetch_row($res)) {
            list($u_id,$u_g) = split(",",slookup('users', 'ID,gruppe', 'ID', $row[0],'1'));
            if ($u_g == $user_group) {
                $addproj[$u_id] = $u_id[0];
            }
        }
        $selektor_answer = __('Added participants of project')."<br /><br />\n";
    }
    if (count($addproj) == 0) $selektor_answer = __('there were no hits found.')."<br /><br />\n";

    return $addproj;
}

/** 
 * Displays input and select box for groups 
 *
 * @param string selector_name - Name of the selector
 * @return string
 * @access public
 */
function usersextra_groups($selector_name) {
    global $user_ID, $user_kurz, $sql_user_group;

    // are there group-restrictions in effect?
    $additional_where = '';
    if (PHPR_ACCESS_GROUPS != 2) {
        $additional_where = selector_get_groupIds();
        if (is_array($additional_where) && count($additional_where) > 0) {
            $additional_where = " WHERE ID IN ('".implode("','", $additional_where)."')";
        } else {
            $additional_where = '';
        }
    }

    $options = "<option selected='selected'></option>\n"; 
    $query = "SELECT ID, name, kurz
                FROM ".DB_PREFIX."gruppen ".$additional_where;
    $res = db_query($query) or db_die();
    $num_res = 0;
    while ($row = db_fetch_row($res)) {
        $tmp = "$row[1] ($row[2])";
        $options .= "<option value='".$row[0]."' title='$tmp'>$tmp</option>\n";
        $num_res++;
    }
    // put disabled or not the select
    if ($num_res == 0) {
        $disabled = "disabled='disabled'";
    } else {
        $disabled = "";
    }

    $ret = "
    <tr>
        <td class='selector_filter'>".__('add group of participants').":</td>
        <td class='selector_filter'></td>
        <td class='selector_filter'>
        <select class='selector_filter' name='usersextra_group' onchange=\"selector_selectAll('".$selector_name."dsts[]'); submit();\" style='width:200px;' ".$disabled.">\n";
    $ret .= $options;
    $ret .= "</select>
        </td>
    </tr>\n";
    
    return $ret;
}

/** 
 * Evaluates the select box extra_groups 
 *
 * @access public
 */
function userseval_extra_groups() {
    global $usersextra_group, $selektor_answer;

    $addprof = array();
    if (!empty($usersextra_group)) {
        // group-restrictions in effect?
        $additional_where = '';
        if (PHPR_ACCESS_GROUPS != 2) {
            $additional_where = selector_get_groupIds();
            if (is_array($additional_where) && count($additional_where) > 0) {
                $additional_where = " AND grup_ID IN (".implode(",", $additional_where).")";
            } else {
                $additional_where = '';
            }
        }
        $query = "SELECT u.ID
                    FROM ".DB_PREFIX."grup_user AS g, ".DB_PREFIX."users AS u
                   WHERE u.ID=g.user_ID
                     AND g.grup_ID = ".(int)$usersextra_group." ".$additional_where;
        $res = db_query($query) or db_die();
        while ($row = db_fetch_row($res)) {
            $addproj[$row[0]] = $row[0];
        }
        $selektor_answer = __('Added group of participants')."<br /><br />\n";
    }
    if (count($addproj) == 0) $selektor_answer = __('there were no hits found.')."<br /><br />\n";

    return $addproj;
}

$usersextras = array(
    'profiles' => array('getform'  => 'usersextra_profiles',
                        'evalform' => 'userseval_extra_profiles',
                        'formname' => array('usersextra_profile', 'usersextra_profileglob')),
    'projects' => array('getform'  => 'usersextra_projects',
                        'evalform' => 'userseval_extra_projects',
                        'formname' => array('usersextra_project', 'usersextra_projectglob', 'usersextra_projectbutton')),
    'groups'   => array('getform'  => 'usersextra_groups',
                        'evalform' => 'userseval_extra_groups',
                        'formname' => array('usersextra_group'))
);
?>
