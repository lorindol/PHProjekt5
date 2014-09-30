<?php
/**
* selector library routines
*
* @package    selector
* @module     main
* @author     Franz Graf, Gustavo Solt, $Author: alexander $
* @licence    GPL, see www.gnu.org/copyleft/gpl.html
* @copyright  2000-2006 Mayflower GmbH www.mayflower.de
* @version    $Id: selector.inc.php,v 1.49.2.2 2007/01/24 14:27:36 alexander Exp $
*/
if (!defined('lib_included')) die('Please use index.php!');

/**
* Build a selectbox filled with users.
* Selected users are shown selected and on top.
* A certain number of other users is shown below.
* This function is only to be used in connection w/ the selector!
* The valus will be the users' shortnames. The displayed text: 'surname, name (group)'
*
* @access public
* @param string $name           Name of the selectbox
* @param array  $selected_users array of shortnames or IDs that should be selected
* @param string $selector_name  name of the selector
* @param int    $exclude_ID     (optional) exclude this ID in the select
* @param string $html           (optional) String printed into the select-tag (ie: "id='bar' class='foo'")
* @param int    $size           (optional) size of the multiple-select
* @param int    $multiple       (optional) select multiple or normal (default = 1 = multiple)
* @return string complete select or empty string on errors
*/
function selector_create_select_users($name, $selected_users=array(), $selector_name, $exclude_ID = 0, $html = "", $size = 7, $multiple = '1') {
    global $read_o;

    // check input
    $size = (int) $size;
    if (empty($name))               return "";
    else $name = xss($name);
    // only get int values
    $selected_users_tmp = array();
    if (is_array($selected_users)) {
        foreach ($selected_users as $key => $value) {
            $key = xss($key);
            $selected_users_tmp[$key] = intval(xss($value));
        }
    } else {
        $selected_users_tmp[0] = intval(xss($selected_users));
    }
    $selected_users = $selected_users_tmp;

    if (empty($size) or $size <= 0) return "";
    else $size = (int) $size;
    // input is not completely nonsense now

    // create head
    if ($multiple) { 
        $str_multiple = "multiple='multiple' size='". $size ."'"; 
    } else {
        $str_multiple = "";
    }
    $output = "<select $html $str_multiple name='$name' ".read_o($read_o).">\n";

    // get all selected users if the array is not empty
    if (count($selected_users) && $selected_users[0] != 0) {
        $temp_users = selector_get_users($selected_users, $exclude_ID);
        foreach ($temp_users as $temp) {
            $output .= " <option value='".$temp[0]."' selected='selected' title='".$temp[2]."'>".$temp[1]."</option>\n";
        }
        unset($temp_users, $temp);
    }
    $output .= " <option value=''>- - - - - - -</option>\n";

    // get _all_ users and show the first PHPR_FILTER_MAXHITS except the selected users
    $temp_users = selector_get_users(array(), $exclude_ID);
    $remaining_users = PHPR_FILTER_MAXHITS;
    while ((list(,$temp) = each($temp_users)) and $remaining_users > 0) {
        if (!in_array($temp[0],$selected_users)) {
            $remaining_users--; 
            $output .= " <option value='".$temp[0]."' title='".$temp[2]."'>".$temp[1]."</option>\n";
        }
    }
    // is there more data than currently shown?
    if ($remaining_users<=0 and count($temp_users) > PHPR_FILTER_MAXHITS) {
        $output .= " <option value=''>. . .</option>\n";
    }
    unset($temp_users, $temp, $remaining_users);

    // add the footer
    $output .= "</select>\n";
    $output .= "<input type=\"image\" src=\"../img/cont.gif\" title=\"".__('Member selector')."\" name=\"".$selector_name."\" id=\"".$selector_name."\" ". read_o($read_o) ."/>\n";

    return $output;
}


/**
* Select a set of users for the selector-dropdown.
* If an non-empty array is referenced, the shortnames in the
* array are selected only. Otherwise, all users are selected.
* A tuple will look like this:
*  array('test1', 'surname, name (group)')
* The result depends on PHPR_ACCESS_GROUPS which may restrict access
* to users of certain groups.
* If the calling user acts as a proxy (global[act_for]) the permissions
* of the act_for-user are taken.
*
* @param  array $ids            (optional) array of user IDs
* @param  int   $exclude_ID     (optional) exclude this ID in the select
* @return array Array of arrays: array(ID, string)
*/
function selector_get_users($ids=array(), $exclude_ID = 0) {
    $ids              = (array) $ids; // select only these IDs
    $additional_where = array();      // additional where for query
    $userID           = $GLOBALS['user_ID'];
    $return_array     = array();
    $additional_where = array();

    // select only a certain set of users
    if (is_array($ids) && count($ids) > 0 && $ids[0] > 0) {
        $additional_where[] = " u.ID IN (". implode(",", $ids) .")";
    }

    // exclude this ID
    $exclude_ID = intval($exclude_ID);
    if ($exclude_ID) {
        $additional_where[] = " u.ID != $exclude_ID ";
    }
    
    if (PHPR_ACCESS_GROUPS != 2) { 
        $g_grup_ID = selector_get_groupIds();
        if (is_array($g_grup_ID) and $g_grup_ID[0] > 0) {
            $additional_where[] = "gu.grup_ID IN (".implode(",", $g_grup_ID).")";
        }
    }

    // build where
    if (count($additional_where) > 0) {
        $additional_where = ' AND '.implode(' AND ', $additional_where);
    }
    else {
        $additional_where = '';
    }

    if (PHPR_ACCESS_GROUPS != 2) {
        $query = "SELECT DISTINCT u.ID, u.nachname, u.vorname, g.name, g.kurz
                    FROM ".DB_PREFIX."users u
               LEFT JOIN ".DB_PREFIX."gruppen    g ON u.gruppe = g.ID
               LEFT JOIN ".DB_PREFIX."grup_user gu ON u.ID = gu.user_ID
                   WHERE 1=1 $additional_where
                ORDER BY u.nachname, u.vorname, g.kurz";
    }
    else {
        $query = "SELECT u.ID, u.nachname, u.vorname, g.name, g.kurz
                    FROM ".DB_PREFIX."users u, ".DB_PREFIX."gruppen g
                   WHERE u.gruppe = g.ID
                         $additional_where
                ORDER BY u.nachname, u.vorname, g.kurz";
    }

    $result = db_query($query) or db_die();
    while ($row = db_fetch_row($result)) {
        $return_array[] = array( $row[0],
                                 $row[1].", ".$row[2]." (".$row[4].")",
                                 $row[1].", ".$row[2]." (".$row[3].")"
                                );
    }

    return $return_array;
}


/**
* Get the groupIDs the user may access.
* This depends on PHPR_ACCESS_GROUPS.
* Once the data is selected it is 'cached' in a static-variable.
*
* @return array Array with allowed groupIds as values or empty array if all allowed.
*/
function selector_get_groupIds() {
    $userID = $GLOBALS['user_ID'];

    if (isset($GLOBALS['act_for']) && $GLOBALS['act_for'] > 0) {
        $userID = $GLOBALS['act_for'];
    }
    $userID = (int) $userID;

    // "caching"
    static $allowed_groups = null;
    if ($allowed_groups !== null) {
        return $allowed_groups;
    }
    $allowed_groups = array();

    // fetch all groups of this user
    $query = "SELECT grup_ID
                FROM ".DB_PREFIX."grup_user
               WHERE user_ID = ".(int)$userID;
    $user_groups = array();
    $result = db_query($query) or db_die();
    while ($row=db_fetch_row($result)) {
        $user_groups[] = $row[0];
    }

    // current group only
    if (PHPR_ACCESS_GROUPS == 0) {
        if (in_array($GLOBALS['user_group'], $user_groups)) {
            $allowed_groups = array($GLOBALS['user_group']);
        }
    }
    // all groups the user is member of
    else if (PHPR_ACCESS_GROUPS == 1) {
        $allowed_groups = $user_groups;
    }
    // no restrictions = all groups
    else if (PHPR_ACCESS_GROUPS == 2) {
        // fetch all groups of this user
        $query = "SELECT DISTINCT grup_ID
                    FROM ".DB_PREFIX."grup_user";
        $result = db_query($query) or db_die();
        while ($row=db_fetch_row($result)) {
            $allowed_groups[] = $row[0];
        }
    }

    return $allowed_groups;
}

/**
* Build a selectbox filled with contacts.
* Selected contacts are shown selected and on top.
* A certain number of other users is shown below.
* This function is only to be used in connection w/ the selector!
* The valus will be the contact' shortnames. The displayed text: 'nachname,vorname,(firma)'
*
* @access public
* @param string $name               Name of the selectbox
* @param array  $selected_contacts  array of shortnames or IDs that should be selected
* @param string $selector_name      name of the selector
* @param int    $exclude_ID         (optional) exclude this ID in the select
* @param string $html               (optional) String printed into the select-tag (ie: "id='bar' class='foo'")
* @param int    $size               (optional) size of the multiple-select
* @param int    $multiple           (optional) select multiple or normal (default = 0 = single)
* @return string complete select or empty string on errors
*/
function selector_create_select_contacts($name, $selected_contacts=array(), $selector_name, $exclude_ID = 0, $html = "", $size = 1, $multiple = '0') {
    global $read_o;

    // only get int values
    $selected_contacts_tmp = array();
    if (is_array($selected_contacts)) {
        foreach ($selected_contacts as $key => $value) {
            $key = xss($key);
            $selected_contacts_tmp[$key] = intval(xss($value));
        }
    } else {
        $selected_contacts_tmp[0] = intval(xss($selected_contacts));
    }
    $selected_contacts = $selected_contacts_tmp;

    // create head
    if ($multiple) { 
        $str_multiple = "multiple='multiple' size='". $size ."'"; 
    } else {
        $str_mulitple = "";
    }
    $output = "<select $html $str_multiple name='$name' ".read_o($read_o).">\n";
    
    // get all selected contacts if the array is not empty
    if (count($selected_contacts)) {
        $temp_contacts = selector_get_contacts($selected_contacts, $exclude_ID);
        foreach ($temp_contacts as $temp) {
            $output .= " <option value='".$temp[0]."' selected='selected' title='".$temp[2]."'>".$temp[1]."</option>\n";
        }
        unset($temp_contacts, $temp);
    }
    $output .= " <option value=''>- - - - - - -</option>\n";

    // get _all_ contacts and show the first PHPR_FILTER_MAXHITS except the selected contacts
    $temp_contacts = selector_get_contacts(array(), $exclude_ID);
    $remaining_contacts = PHPR_FILTER_MAXHITS;
    while ((list(,$temp) = each($temp_contacts)) and $remaining_contacts > 0) {
        if (!in_array($temp[0],$selected_contacts)) {
            $remaining_contacts--; 
            $output .= " <option value='".$temp[0]."' title='".$temp[2]."'>".$temp[1]."</option>\n";
        }
    }
    // is there more data than currently shown?
    if ($remaining_contacts<=0 and count($temp_contacts) > PHPR_FILTER_MAXHITS) {
        $output .= " <option value=''>. . .</option>\n";
    }
    unset($temp_contacts, $temp, $remaining_contacts);

    // add the footer
    $output .= "</select>\n";
    $output .= "<input type=\"image\" src=\"../img/cont.gif\" title=\"".__('Contact selector')."\" name=\"".$selector_name."\"". read_o($read_o) ."/>\n";

    return $output;
}


/**
* Select a set of contacts for the selector-dropdown.*
* @param  int    $exclude_ID  (optional) exclude this ID in the select
* @param  array  $ids         (optional) array of contacts IDs
* @return array  Array of arrays: array(ID, string)
*/
function selector_get_contacts($ids=array(), $exclude_ID = 0) {
    global $user_group;
    
    $ids              = (array) $ids;                // select only these IDs
    $opt_where        = (!empty($user_group) ? array("gruppe=".qss($user_group)) : array()); // additional where for query
    $return_array     = array();

    $g_grup_ID = selector_get_groupIds();
    if (is_array($g_grup_ID) && count($g_grup_ID) > 0) {
        $opt_where[] = "gruppe IN ('".implode("','", $g_grup_ID)."')";
    }

    $order = 'ORDER BY nachname ASC, vorname ASC';

    $where = implode(" AND ", $opt_where);
    if ($where == "") {
        $where = "1=1";
    }

    // All values, in tree view
    if (empty($ids)) {
        $tmp = get_elements_of_tree('contacts', 'nachname, vorname, firma', 'WHERE '. $where, 'acc_read', $order, '', 'parent', $exclude_ID);
        foreach($tmp as $option_data){
            $return_array[] = array( $option_data['value'],
                                     (str_repeat('&nbsp;&nbsp;', $option_data['depth'])).$option_data['text']
                                    );
        }
    } else {
        foreach($ids as $key => $val){
            $ids[$key] = (int) $val;
        }
        // select only a certain set of contacts
        $opt_where[] = " ID IN (".implode(",", $ids).")";

        // exclude this ID
        $exclude_ID = intval($exclude_ID);
        if ($exclude_ID) {
            $opt_where[] = " ID != $exclude_ID ";
        }

        $where = implode(" AND ", $opt_where);
        if ($where == "") {
            $where = "1=1";
        }

        $query = "SELECT ID, nachname, vorname, firma
                    FROM ".DB_PREFIX."contacts
                   WHERE $where $order";
        $result = db_query($query) or db_die();

        while ($row = db_fetch_row($result)) {
            $return_array[] = array( $row[0],
                                     $row[1].", ".$row[2]." (".$row[3].")"
                                    );
        }
    }

    return $return_array;
}

/**
* Build a selectbox filled with projects.
* Selected projects are shown selected and on top.
* A certain number of other projects is shown below.
* This function is only to be used in connection w/ the selector!
* The valus will be the project' names. The displayed text: 'name'
*
* @access public
* @param string $name               Name of the selectbox
* @param array  $selected_projects  array of names or IDs that should be selected
* @param string $selector_name      name of the selector
* @param int    $exclude_ID         (optional) exclude this ID in the select
* @param string $html               (optional) String printed into the select-tag (ie: "id='bar' class='foo'")
* @param int    $size               (optional) size of the multiple-select
* @param int    $multiple           (optional) select multiple or normal (default = 0 = single)
* @return string complete select or empty string on errors
*/
function selector_create_select_projects($name, $selected_projects=array(), $selector_name, $exclude_ID = 0, $html = "", $size = 1, $multiple = '0') {
    global $read_o;

    // only get int values
    $selected_projects_tmp = array();
    if (is_array($selected_projects)) {
        foreach ($selected_projects as $key => $value) {
            $key = xss($key);
            $selected_projects_tmp[$key] = intval(xss($value));
        }
    } else {
        $selected_projects_tmp[0] = intval(xss($selected_projects));
    }
    $selected_projects = $selected_projects_tmp;

    // create head
    if ($multiple) { 
        $str_multiple = "multiple='multiple' size='". $size ."'"; 
    } else {
        $str_multiple = "";
    }
    $output = "<select $html $str_multiple name='$name' ".read_o($read_o).">\n";
    
    // get all selected projects if the array is not empty
    if (count($selected_projects)) {
        $temp_projects = selector_get_projects($selected_projects, $exclude_ID);
        foreach ($temp_projects as $temp) {
            $output .= " <option value='".$temp[0]."' selected='selected'>".$temp[1]."</option>\n";
        }
        unset($temp_projects, $temp);
    }
    $output .= " <option value=''>- - - - - - -</option>\n";

    // get _all_ projects and show the first PHPR_FILTER_MAXHITS except the selected projects
    $temp_projects = selector_get_projects(array(), $exclude_ID);
    $remaining_projects = PHPR_FILTER_MAXHITS;
    while ((list(,$temp) = each($temp_projects)) and $remaining_projects > 0) {
        if (!in_array($temp[0],$selected_projects)) {
            $remaining_projects--; 
            $output .= " <option value='".$temp[0]."'>".$temp[1]."</option>\n";
        }
    }
    // is there more data than currently shown?
    if ($remaining_projects<=0 and count($temp_projects) > PHPR_FILTER_MAXHITS) {
        $output .= " <option value=''>. . .</option>\n";
    }
    unset($temp_projects, $temp, $remaining_projects);

    // add the footer
    $output .= "</select>\n";
    $output .= "<input type=\"image\" src=\"../img/cont.gif\" title=\"".__('Project selector')."\" name=\"".$selector_name."\"". read_o($read_o) ."/>\n";

    return $output;
}


/**
* Select a set of projects for the selector-dropdown.*
* @param  array  $ids        (optional) array of projects IDs
* @param  int    $exclude_ID (optional) exclude this ID in the select
* @return array Array of arrays: array(ID, string)
*/
function selector_get_projects($ids=array(), $exclude_ID = 0) {
    global $user_ID,$user_kurz;
    $allowed_groups='';
    $g_grup_ID = selector_get_groupIds();
    if (is_array($g_grup_ID) && $g_grup_ID[0] > 0) {
        $allowed_groups = "gruppe IN (".implode(",", $g_grup_ID).")";
    }
    if($allowed_groups == '')$allowed_groups = '1=1';
    $ids              = (array) $ids;                // select only these IDs
    $opt_where        = array("(acc LIKE 'system'
                                OR ((von = ".(int)$user_ID." OR acc LIKE 'group' OR acc LIKE '%\"$user_kurz\"%')
                                AND $allowed_groups))"); // additional where for query
    $return_array     = array();

   
    $order = 'ORDER BY name ASC';

    $where = implode(" AND ", $opt_where);
    if ($where == "") {
        $where = "1=1";
    }

    // All values, in tree view
    if (empty($ids)) {
        $tmp = get_elements_of_tree('projekte', 'name', 'WHERE '. $where, 'acc', $order, '', 'parent', $exclude_ID);
        foreach($tmp as $option_data){
            $return_array[] = array( $option_data['value'],
                                     (str_repeat('&nbsp;&nbsp;', $option_data['depth'])).$option_data['text']
                                    );
        }
    } else {
        foreach($ids as $key => $val){
            $ids[$key] = (int) $val;
        }
    // selected items
        // select only a certain set of users
        $opt_where[] = " ID IN (".implode(",",$ids).")";

        // exclude this ID
        $exclude_ID = intval($exclude_ID);
        if ($exclude_ID) {
            $opt_where[] = " ID != $exclude_ID ";
        }

        $where = implode(" AND ", $opt_where);
        if ($where == "") {
            $where = "1=1";
        }
        
        $query = "SELECT ID, name
                    FROM ".DB_PREFIX."projekte
                   WHERE $where $order";
        $result = db_query($query) or db_die();

        while ($row = db_fetch_row($result)) {
            $return_array[] = array( $row[0],
                                     $row[1]
                                    );
        }
    }

    return $return_array;
}

/**
 * Return the config options for the selectot
 * @author Gustavo Solt
 *
 * @param  string $type  - Type of the selector contact/project/user/etc
 * @param  string $title - Title to display
 * @return array  - (extras => extras for quickadd, opt_where => where options, opt => general optiopns)
 */
function get_selector_config($type, $title) {
    global $user_group;
    
    switch($type) {
        case "contact":
        case "one_contact":
        case "contact_multiple":
        case "contact_one":
        case "contact":
        case "profile_contact":
            // Options for Quickaddings
            $extras = array(
                            'profiles' => array(    'getform'  => 'contactsextra_profiles',
                                                    'evalform' => 'contactseval_extra_profiles',
                                                    'formname' => array('contactsextra_profile')
                                                ),
                            'projects'  => array(   'getform'  => 'contactsextra_projects',
                                                    'evalform' => 'contactseval_extra_projects',
                                                    'formname' => array('contactsextra_project'))
                                );
            // Options for datasource
            $opt_where = array("c.gruppe=$user_group");
            $g_grup_ID = selector_get_groupIds();
            if (is_array($g_grup_ID) && $g_grup_ID[0] > 0) {
                $opt_where[] = "c.gruppe IN (".implode(",", $g_grup_ID).")";
            }
        
            $opt = array(   'title'     => $title,
                            'table'     => array('contacts AS c'),
                            'where'     => $opt_where,
                            'order'     => 'c.nachname ASC, c.vorname ASC',
                            'ID'        => 'c.ID',
                            'display'   => array('c.nachname','c.vorname'),
                            'dstring'   => '%s, %s',
                            'filter'    => array('text' => array(   'nachname' => __('Family Name'),
                                                                    'vorname' => __('First Name')
                                                                )
                                                )
                        );
            break;

    case "project":
    case "one_project":
        // Options for Quickaddings
        $extras = array();

        // Options for datasource
        $opt_where = array("p.gruppe=$user_group");
        $g_grup_ID = selector_get_groupIds();
        if (is_array($g_grup_ID) && $g_grup_ID[0] > 0) {
            $opt_where[] = "p.gruppe IN (".implode(",", $g_grup_ID).")";
        }

        $opt = array(   'title'     => $title,
                        'table'     => array('projekte AS p'),
                        'where'     => $opt_where,
                        'order'     => 'p.name ASC',
                        'ID'        => 'p.ID',
                        'display'   => array('p.name'),
                        'dstring'   => '%s',
                        'filter'    => array('text' => array('name' => __('Name')))
                    );
        break;

    case "member":
    case "user":
    case "access":
        // Options for Quickaddings
        $extras = array(
                            'profiles'  => array(   'getform'  => 'usersextra_profiles',
                                                    'evalform' => 'userseval_extra_profiles',
                                                    'formname' => array('usersextra_profile')),
                            'groups'    => array(   'getform'  => 'usersextra_groups',
                                                    'evalform' => 'userseval_extra_groups',
                                                    'formname' => array('usersextra_group')),
                            'projects'  => array(   'getform'  => 'usersextra_projects',
                                                    'evalform' => 'userseval_extra_projects',
                                                    'formname' => array('usersextra_project'))
                            );
        // Options for datasource
        $opt_where = array("g.grup_ID=$user_group", "g.user_ID=u.ID");
        $g_grup_ID = selector_get_groupIds();
        if (is_array($g_grup_ID) && $g_grup_ID[0] > 0) {
            $opt_where[] = "g.grup_ID IN (".implode(",", $g_grup_ID).")";
        }

        $opt = array(   'title'     => $title,
                        'table'     => array('users AS u', 'grup_user AS g'),
                        'where'     => $opt_where,
                        'order'     => 'u.nachname ASC, u.vorname ASC',
                        'ID'        => 'u.ID',
                        'display'   => array('u.nachname','u.vorname'),
                        'dstring'   => '%s, %s (%s)',
                        'filter'    => array('text' => array(   'nachname'  => __('Family Name'),
                                                                'vorname'   => __('First Name')
                                                            ) 
                                            )
                    );
        break;
    }

    unset($g_grup_ID);
    return array( 'extras'    => $extras,
                  'opt_where' => $opt_where,
                  'opt'       => $opt);
}
?>
