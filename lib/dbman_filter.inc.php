<?php
/**
 * Manage filters functions
 *
 * @package    	lib
 * @subpackage 	main
 * @author     	Albrecht Guenther, $Author: gustavo $
 * @licence     GPL, see www.gnu.org/copyleft/gpl.html
 * @copyright  	2000-2006 Mayflower GmbH www.mayflower.de
 * @version    	$Id: dbman_filter.inc.php,v 1.145 2008-02-04 15:09:31 gustavo Exp $
 */

// check whether the lib has been included - authentication!
if (!defined("lib_included")) die("Please use index.php!");

// selector-tranformation stuff
require_once(LIB_PATH.'/selector/selector.inc.php');

/**
 * Provides column name as input field for make the filters
 *
 * @param string 	$module   	- Module name
 * @param string 	$col_name 	- Field name
 * @param string 	$link     		- Special links for no standar modules
 * @param int    		$cw       		-
 * @return string          				HTML output
 */
function col_filter($module, $col_name, $link=null, $cw) {
    global $field_name, $field, $f_sort, $getstring;
    global $ID, $perpage, $mode;
    global $is_related_obj;

    $sort       = (isset($f_sort[$module]['sort']) ? $f_sort[$module]['sort'] : '');
    $direction  = (isset($f_sort[$module]['direction']) ? $f_sort[$module]['direction'] : '');

    // Sort direction
    if ($direction == "ASC") {
        $new_direction = "DESC";
        $new_direction_text = enable_vars(__('descending'));
    } else {
        $new_direction = "ASC";
        $new_direction_text = enable_vars(__('ascending'));
    }

    $cw = $cw-10;
    if ($link == null) $link = $module;
    $direction_img = '';
    if(isset($_REQUEST['direction']) and isset($_REQUEST['sort']) and xss($_REQUEST['direction']) == 'ASC' and xss($_REQUEST['sort']) == $col_name){
        $direction_img = "<span class='sort'>&#8657;</span>";
    }
    elseif(isset($_REQUEST['direction']) and isset($_REQUEST['sort']) and xss($_REQUEST['direction']) == 'DESC' and xss($_REQUEST['sort']) == $col_name){
        $direction_img = "<span class='sort'>&#8659;</span>";
    }
    // check for addon
    $is_addon = $_SESSION['common']['module'] == 'addons';
    $addon = $is_addon ? '&amp;addon='.$module : '';

    // related object?
    if ($mode == 'forms' && isset($ID) && $ID > 0) {
        global $ID;
        $sort_link = basename($_SERVER['SCRIPT_NAME'])."?mode=forms&amp;ID=".$ID."&amp;sort_module=".$module."&amp;direction=".$new_direction."&amp;sort=".$col_name;
    } else {
        $sort_link = $link.".php?mode=view&amp;sort_module=".$module.$addon."&amp;direction=".$new_direction."&amp;sort=".$col_name;
    }

    // prepare the sort strings
    $sortstr1 = "<a href=\"".$sort_link."\"><u>";
    $sortstr2 = "</u></a>&nbsp;". $direction_img;
    unset($addon);
    unset($is_addon);

    if (($module == 'organisations') && ($field_name == 'ID')) {
        return $sortstr1.enable_vars($field['form_name']).$sortstr2."<br />";
    }

    // start form
    $str = "
<form action='".$link.".php?$getstring' name='".$field_name."' method='post'>
    <input type='hidden' name='mode' value='view' />
    <input type='hidden' name='filter_module' value='$module' />
";
    // offer a select Box
    if ($field['form_type'] == 'select_values') {
        $str .= "<input type='hidden' name='rule' value='exact' />\n";
        $str .= $sortstr1.enable_vars($field['form_name']).$sortstr2."<br />";
        $str .= "<select class='filter_fields' name='keyword'";
        if ($field['form_tooltip'] <> '') $str .= " title='".$field['form_tooltip']."'";
        $str .= read_o($read_o)." onchange='this.form.submit();'>\n";
        // blank value with name of field
        $str .= "<option value=''>--</option>\n";
        foreach (explode('|',$field['form_select']) as $select_value) {
            // split the entry into key and value
            if(strpos($select_value, '#') !== false){
                list($key,$value) = explode('#',$select_value);
            }
            else{
                $key = $value = $select_value;
            }
            $str .= "<option value='".$key."'";
            $str .= '>'.enable_vars($value)."</option>\n";
        }
        $str .= "</select>\n";
    }

    // project list
    else if ($field['form_type'] == 'project') {
        $str .= "<input type='hidden' name='rule' value='exact' />\n";
        $str .= $sortstr1.enable_vars($field['form_name']).$sortstr2."<br />";
        $html = "class='filter_fields'";
        if ($field['form_tooltip'] <> '') $html .= " title='".$field['form_tooltip']."'";
        $html .= read_o($read_o)." onchange='this.form.submit();'";
        $str .= selector_create_select_projects('keyword', '', 'action_form_to_list_'.$field_name.'_selector', '0', $html);
    }
    // user value
    else if ($field['form_type'] == 'userID' or $field['form_type'] == 'user_show' or $field['form_type'] == 'authorID') {
        $str .= "<input type='hidden' name='rule' value='exact' />\n";
        $str .= $sortstr1.enable_vars($field['form_name']).$sortstr2."<br />";
        $html = "class='filter_fields'";
        if ($field['form_tooltip'] <> '') $html .= " title='".$field['form_tooltip']."'";
        $html .= read_o($read_o)." onchange='this.form.submit();'";
        if($module != "todo") {
            $str .= selector_create_select_users('keyword','', 'action_form_to_list_'.$field_name.'_selector', '0', $html, '1', '');
        } else {
            $str .= selector_create_select_users('keyword','', 'action_form_to_list_'.$field_name.'_selector', '0', $html, '1', '');
        }
    }
    // select Box on all users where the ID has been stored in this field
    else if ( $field['form_type'] == 'select_sql' ) {
        $str .= $sortstr1.enable_vars($field['form_name']).$sortstr2."<br />";
        $str .= "<select class='filter_fields' name='keyword'";
        if ($field['form_tooltip'] <> '') $str .= " title='".$field['form_tooltip']."'";
        $str .= read_o($read_o)." onchange='this.form.submit();'>";
        // blank value with name of field
        $str .= "<option value=''>--</option>\n";
        $result = db_query(enable_vars($field['form_select']));
        while ($row = db_fetch_row($result)) {
            $first_element = array_shift($row);
            $str .= "<option value='".$first_element."'";
            $str .= ">".implode(',',$row)."</option>\n";
        }
        $str .= "</select>\n";
    }
    // contact value
    else if ($field['form_type'] == 'contact') {
        $str .= "<input type='hidden' name='rule' value='exact' />\n";
        $str .= $sortstr1.enable_vars($field['form_name']).$sortstr2."<br />";
        $html = "class='filter_fields'";
        if ($field['form_tooltip'] <> '') $html .= " title='".$field['form_tooltip']."'";
        $html .= read_o($read_o)." onchange='this.form.submit();'";
        $str .= selector_create_select_contacts('keyword', '', 'action_form_to_list_'.$module.'_'.$field_name.'_selector', '0', $html);
    }
    // otherwise a simple input box
    else {
        // define length of input field
        $field_length = ( enable_vars($field['form_name']) > 10) ? enable_vars($field['form_name']) : '10';
        $str .= "<input type='hidden' name='rule' value='like' />\n";
        $str .= $sortstr1.enable_vars($field['form_name']).$sortstr2."<br />";
        $str .= "<input type='text' name='keyword' value=''";
        if ($field['form_type'] == 'contact') $str .= read_o(1). " />\n";
        else $str .= " onfocus=\"this.value=''\" />\n";
    }
    // close form
    $hidden = array('filter_module'=>$module, 'mode'=>$mode,'filter'=>$field_name, 'ID'=>$ID,
    'perpage'=>$perpage,'sort'=>$sort, 'direction'=>$direction);
    if (SID) $hidden[session_name()] = session_id();
    $str .= hidden_fields($hidden);
    $str .= "</form>\n";
    // show icons to sort up and down

    return $str;
}

/**
 * Manage the filters
 * Create/update/delete the filters in the Session
 * return the where clause
 *
 * @param string 	$filter    		- Field module
 * @param string 	$rule      		- Rule to use
 * @param string 	$keyword   	- Key to search
 * @param int    		$filter_ID 	- ID of a exists filter
 * @param string 	$module    	- Module name
 * @param string 	$firstchar 	- Look for a first char?
 * @param string 	$operator  	- OR/AND operator
 * @return string           			WHERE clause
 */
function main_filter($filter, $rule, $keyword, $filter_ID, $module, $firstchar = '',$operator = '') {
    global $fields, $flist, $flist_store;

    $all_fields = build_array($module, $ID, 'forms');
    if ((!is_array($all_fields)) || count($all_fields) == 0) {
        $all_fields = $fields;
    }
    if ($module == 'mail' || $module == 'todo' || $module == 'helpdesk') {
        $all_fields = array_merge($all_fields, $fields);
    }

    // Set operator value to last saved value if no new value is given
    if ($operator == '') {
        if (isset($_SESSION['flist']['operators'][$module])) {
            $operator = $_SESSION['flist']['operators'][$module];
        }
        elseif (isset($flist_store['operators'][$module])) {
            $operator = $flist_store['operators'][$module];
        }

    }

    if($operator != 'OR') $operator = " AND ";

    // -1. action: delete all filters
    if (isset($filter_ID) && $filter_ID == '-1') {
        unset($flist[$module]);
        $flist['operators'][$module] = '';
        unset($filter_ID);
    }

    // 0. action: take values from storage
    if (!isset($flist[$module]) && $flist_store[$module] && !isset($_SESSION['flist']['operators'][$module])) $flist[$module] = $flist_store[$module];


    // 1. action: check whether a filter element should be removed
    if (isset($filter_ID) && is_array($flist[$module])) {
        unset($flist[$module][$filter_ID]);
    }
    elseif (isset($filter_ID) && is_string($flist[$module])) {
        unset($flist[$module]);
    }

    // 2. action: add the current filter to the filter list
    // 2/a special filter for contacts - select all records where the last name begins with this char
    if (is_string($flist[$module])) {

        $_SESSION['flist']['operators'][$module] = $operator;
        //if (substr($where,0,2) == "OR") $where = "AND (".substr($where,2).")";
        //if (!$where) $where = ' AND 1=1 ';

        $_SESSION['flist'] =& $flist;

        return 'AND ('.$flist[$module].')';

    }
    if ($firstchar <> '') {
        $flist[$module][] = array('nachname', 'begins', $firstchar);
    }
    // 2/b look for a 'normal filter
    else if (isset($keyword) && strlen($keyword) != 0) {
        put_filter_value($filter, $rule, $keyword,$module);
    }

    // 3. action: apply the filter list
    if (isset($flist[$module]) && is_array($flist[$module]) && count($flist[$module]) > 0) {
        // 3.1 apply the filter
        $unique = array();
        foreach ($flist[$module] as $key=>$p_filter) {
            if (in_array(serialize($flist[$module][$key]), $unique)) {
                // remove multiple entries
                unset($flist[$module][$key]);
                continue;
            }
            if ($p_filter[2] != '') {
                // rebuild keyword cause output differs from sql-query
                $p_filter = rebuild_keyword($module, $p_filter);
                // if the field string is 'all', it has to be looped over all applicable fields
                $tmp = '';
                if ($p_filter[0] == 'all') $tmp .= apply_full_filter($p_filter[1], $p_filter[2], $module);
                else {
                    $field_name = $p_filter[0];

                    // Do not apply filters over inexistent fields
                    if (is_array($all_fields) && isset($all_fields[$field_name])) {
                        $field_type = $all_fields[$field_name]['field_type'];
                        $tmp .= apply_filter($p_filter[0], $p_filter[1], $p_filter[2], $module, $field_type);
                    }
                }

                if (strlen($tmp)) $where .= $operator.' ('.$tmp.') ';

            }
            $unique[] = serialize($flist[$module][$key]);
        }
    }

    //if there is an OR to start with, remove it!
    $_SESSION['flist']['operators'][$module] = $operator;
    if (isset($where) && substr($where,0,2) == "OR") $where = "AND (".substr($where,2).")";
    if (!isset($where)) {
        $where = ' AND 1 = 1 ';
    }

    $_SESSION['flist'] =& $flist;
    // one result of the whole thing: the where clause for the sql query
    return $where;
}

/**
 * Display all the filters of a module
 *
 * @param string 	$module   - Module name
 * @param string 	$link      	- Special link for no standar modules
 * @return string           		HTML output
 */
function display_filters($module, $link=null) {
    global $flist, $action, $sid, $ID, $getstring, $is_related_obj2;
    // avoid double save
    $mode = ($GLOBALS['mode'] != 'data') ? $GLOBALS['mode'] : 'view';
    // All fields
    $fields = build_array($module, '');
    if ($module == 'mail' || $module == 'todo' || $module == 'helpdesk') {
        $tmp = build_array($module,'','view');
        $fields = array_merge($fields, $tmp);
    }

    $filter_list_text = '';
    if (!$link) $link = $module;
    if (isset($flist[$module]) && is_string($flist[$module])) {
        return
        " <a href='".$link.".php?mode=$mode&amp;$getstring&amp;ID=$ID&amp;filter_module=$module&amp;action=$action&amp;filter_ID=2".$sid.
        "' class='filter_active' title='".__('Delete')."'>".expert_filter_translate($flist[$module],$fields,true)."</a>\n";
    }
    if (isset($flist[$module]) && is_array($flist[$module]) && count($flist[$module]) > 0) {
        $filter_list_arr = array();
        foreach ($flist[$module] as $key=>$p_filter) {
            // first fetch the name
            foreach ((array)$fields as $field_name=>$field) {
                if ($field_name == $p_filter[0]) $filtername = enable_vars($field['form_name']);
            }
            // The value is "all"
            if (empty($filtername)) {
                $filtername = __('All');
            }

            # 2005-10-12 Eduardo
            # we will try to get the 'value' of filter using the id

            // show more than one similar filters
            if (is_array($p_filter[2])) {
                $str = '';
                $i = 0;
                foreach ($p_filter[2] as $filter_array) {
                    if ($i != 0) $str .= ' - ';
                    $str .= get_filter_value_text($module,$p_filter[0],$filter_array);
                    $i++;
                }
                $filter_value = $str;
            } else $filter_value = get_filter_value_text($module,$p_filter[0],$p_filter[2]);

            // get the rules name
            $filter_rules = get_filter_rules_array();
            $filterrule = $filter_rules[$p_filter[1]];

            // click on link removes the filter
            $hreftext = '&nbsp;'.$filtername.'&nbsp;'.$filterrule.'&nbsp;'.$filter_value."&nbsp;";

            $filter_list_arr[] = " <a href='".$link.".php?mode=$mode&amp;$getstring&amp;ID=$ID&amp;filter_module=$module&amp;action=$action&amp;filter_ID=$key".$sid.
            "' class='filter_active' title='".__('Delete')."'>".$hreftext."</a>\n";

        }

        // Set operator value to last saved value if no new value is given
        if ($operator == '') {
            if (isset($_SESSION['flist']['operators'][$module])) {
                $operator = $_SESSION['flist']['operators'][$module];
            } else {
                $operator = $flist['operators'][$module];
            }
        }

        if($operator != 'OR') $operator = " AND ";

        //Assign the right display Symbol for logic operation
        if ($operator == "OR") {
            $op = __('or');
        }
        else $op = __('and');

        if (!$is_related_obj2) {
            $str='|&nbsp;<form action="'.$link.'.php?mode='.$mode.'&amp;'.$getstring.'" method="post" style="display:inline;" name="formOp">';
            $str.="<input type='radio' name='operator' value='AND' ";
            if($operator!="OR")$str.="checked='checked'";
            $str.="onclick='document.formOp.submit()'/>".__('and')."&nbsp;
     	    	<input type='radio' value='OR' name='operator'";
            if($operator=="OR")$str.="checked='checked'";
            $str.=" onclick='document.formOp.submit()'/>".__('or')."</form>&nbsp;&nbsp;";
        }

        if(count($flist[$module])<2 or (is_string($flist[$module])))$str='';
        $filter_list_text = "<b>".__('Filtered').":</b> ".implode($op, $filter_list_arr).
        "&nbsp;&nbsp;$str|
                            &nbsp;&nbsp;<a href='".$link.
                            ".php?mode=$mode&amp;ID=$ID&amp;filter_module=$module&amp;$getstring&amp;action=$action&amp;filter_ID=-1".$sid.
                            "' class='filter_manage' title='".__('Delete all filter')."'>".__('Delete all filter')."</a>\n";
    }

    return $filter_list_text;
}

/**
 * Show the "Edit filters" Link
 * This function creates the required JavaScript function and Link
 * for every module.
 *
 * @param string 	$filtermodule 	- Name of the filtered module
 * @param string 	$color        		- Without use
 * @return string              				HTML output
 */
function display_manage_filters($filtermodule, $color='') {
    global $module, $mode, $ID, $flist, $module;

    $ret = '';
    if (count($flist[$module]) > 0) $ret .= '&nbsp;&nbsp;|&nbsp;&nbsp;';

    if (is_string($flist[$module]) && strlen($flist[$module]) > 0 && PHPR_EXPERT_FILTERS == 1) {
        $expert = 1;
    }
    else {
        $expert = 0;
    }

    $ret .= '<a title="'.__('This link opens a popup window').'" href="#" onclick="manage_filters(\''.PATH_PRE.'\',\''.$filtermodule.'\',\''.$module.'\',\''.$mode.'\',\''.$ID.'\',\''.$expert.'\')">';
    $ret .= __('Edit filter');
    $ret .= '</a>';
    return $ret;
}

/**
 * Create the where clause
 *
 * @param string 	$field      		- Field module
 * @param string 	$rule       		- Rule to use
 * @param string 	$keyword    	- Key to search
 * @param string 	$module     	- Module name
 * @param string 	$field_type 	- Type of the field: varchar/integer/decimal/numeric
 * @return string            			WHERE clause
 */
function apply_filter($field, $rule, $keyword, $module, $field_type = 'varchar') {

    $table = get_table_by_module($module);

    if ($field_type == 'integer' || $field_type == 'decimal' || $field_type == 'numeric') {
        $quote = "";
    } else $quote = "'";

    if (!is_array($keyword)) {
        switch ($rule) {
            case 'begins':
                $w = "$table.$field LIKE '$keyword%'";
                break;
            case 'ends':
                $w = "$table.$field LIKE '%$keyword'";
                break;
            case 'exact':
                if ($quote == "'" || is_numeric($keyword)) {
                    $w = "$table.$field = {$quote}$keyword{$quote}";
                }
                break;
            case '>':
                if ($quote == "'" || is_numeric($keyword)) {
                    $w = "$table.$field > {$quote}$keyword{$quote}";
                }
                break;
            case '>=':
                if ($quote == "'" || is_numeric($keyword)) {
                    $w = "$table.$field >= {$quote}$keyword{$quote}";
                }
                break;
            case '<=':
                if ($quote == "'" || is_numeric($keyword)) {
                    $w = "$table.$field <= {$quote}$keyword{$quote}";
                }
                break;
            case '<':
                if ($quote == "'" || is_numeric($keyword)) {
                    $w = "$table.$field < {$quote}$keyword{$quote}";
                }
                break;
            case 'not like':
                $w = "$table.$field NOT LIKE '%$keyword%'";
                break;
                // default rule: like
            default:
                $w = "$table.$field LIKE '%$keyword%'";
        }
    } else {
        $i=0;
        $added = false;
        foreach($keyword as $k) {
            if (($i != 0) && $added) {
                $w .= ' OR ';
                $added = false;
            }
            switch ($rule) {
                case 'begins':
                    $w .= "$table.$field LIKE '$k%'";
                    $added = true;
                    break;
                case 'ends':
                    $w .= "$table.$field LIKE '%$k'";
                    $added = true;
                    break;
                case 'exact':
                    if ($quote == "'" || is_numeric($keyword)) {
                        $w .= "$table.$field = {$quote}$k{$quote}";
                        $added = true;
                    }
                    break;
                case '>':
                    if ($quote == "'" || is_numeric($keyword)) {
                        $w .= "$table.$field > {$quote}$k{$quote}";
                        $added = true;
                    }
                    break;
                case '>=':
                    if ($quote == "'" || is_numeric($keyword)) {
                        $w .= "$table.$field >= {$quote}$k{$quote}";
                        $added = true;
                    }
                    break;
                case '<=':
                    if ($quote == "'" || is_numeric($keyword)) {
                        $w .= "$table.$field <= {$quote}$k{$quote}";
                        $added = true;
                    }
                    break;
                case '<':
                    if ($quote == "'" || is_numeric($keyword)) {
                        $w .= "$table.$field < {$quote}$k{$quote}";
                        $added = true;
                    }
                    break;
                case 'not like':
                    $w .= "$table.$field NOT LIKE '%$k%'";
                    $added = true;
                    break;
                    // default rule: like
                default:
                    $w .= "$table.$field LIKE '%$k%'";
                    $added = true;
            }
            $i++;
        }
    }
    return $w;
}

/**
 * Create the where clause for all the fields
 *
 * @param string 	$rule       		- Rule to use
 * @param string 	$keyword    	- Key to search
 * @param string 	$module     	- Module name
 * @return string            			WHERE clause
 */
function apply_full_filter($rule, $keyword, $module) {
    global $fields;
    foreach ($fields as $field_name => $field) {
        if ($field['filter_show'] != '') {
            $temp = apply_filter($field_name, $rule, $keyword, $module, $field['field_type']);
            if (!empty($temp)) {
                $f_list[] = $temp;
            }
        }
    }
    // The particular case of 'not like' needs a AND on implode instead of OR
    if ($rule == 'not like') {
        $w = implode(' AND ', $f_list);
    }
    else {
        $w = implode(' OR ', $f_list);
    }
    return $w;
}

/**
 * Checks whether a filter element in the list should be removed
 *
 * @param int $filter_ID  	- ID of the filter in the SESSION
 * @return void
 */
function filter_mode($filter_ID) {
    if (isset($filter_ID)) {
        $_SESSION['flist'] =& $flist;
    }
}

/**
 * Filter in navigation bar
 * Make the select of fields, rules and input for keywords
 *
 * @param array $fields   	- Array with all the field data
 * @return string         		HTML output
 */
function nav_filter($fields) {
    global $operator;
    $filter_rules = get_filter_rules_array();
    $str = '<b>'.__('Filter').':</b> ';
    $str .= "<select name='filter'><option value='all'>".__('all fields')."</option>\n";
    $filter_list = array();
    if (is_array($fields)) {
        foreach ($fields as $field_name => $field) {
            if ($field['filter_show'] > 0 or $field['filter_show']=='on') $filter_list[$field_name] = enable_vars($field['form_name']);
        }
    }
    // sort array by name
    natcasesort($filter_list);
    reset($filter_list);
    foreach($filter_list as $filter_field => $filter_formname) {
        $str .= "<option value='".$filter_field ."'";
        if ($filter_field == $filter) $str .= ' selected="selected"';
        $str .= '>'.$filter_formname."</option>\n";
    }
    $str .= "</select>";
    // ... rule ...
    $str .= '<span class="strich">&nbsp;</span>';
    $str .= "&nbsp;<select name='rule'>\n";
    foreach ($filter_rules as $showrule => $ruletext) {
        $str .= "<option value='".$showrule."'>".$ruletext."</option>\n";
    }
    $str .= "</select>\n";
    $str .= "<input type='text' size='15' name='keyword' />\n";
    return $str;
}

/**
 * Show all users of a group
 *
 * @param int 	$user_group 	- Value of the group_ID of the user
 * @param int 	$value      		- Selected group
 * @return string         				HTML output
 */
function show_filter_group_users($user_group,$value) {
    $str = "";
    // group system, fetch ID's from the other users
    if ($user_group) {
        $query = "SELECT DISTINCT user_ID, u.nachname, u.vorname
                    FROM ".DB_PREFIX."grup_user g, ".DB_PREFIX."users u
                   WHERE grup_ID = ".(int)$user_group."
                     AND g.user_ID = u.ID
                     AND u.is_deleted is NULL
                ORDER BY u.nachname";
        $result3 = db_query($query) or db_die();
    }
    // if user is not assigned to a group or group system is not activated
    else {
        $result3 = db_query("SELECT ID, nachname
                               FROM ".DB_PREFIX."users
                              WHERE is_deleted is NULL
                           ORDER BY nachname") or db_die();
    }

    // loop over all user ID's of this group, fetch names and display them
    while ($row3 = db_fetch_row($result3)) {
        if (!empty($value)&&($value == $row3[0])) $selected = "selected=selected";
        else $selected = '';
        $str .= '<option value="'.$row3[0].'"';
        $str .= $selected. ">$row3[1], $row3[2]</option>\n";
    }

    return $str;
}

/**
 * Convert special user inputs
 *
 * @param string 	$module   	- Module name
 * @param array  	$p_filter 		- Data of the filter to change
 * @return array           				Modified p_filter array
 */
function rebuild_keyword($module, $p_filter) {
    global $date_format_object;

    $ret = $p_filter;
    switch ($module) {
        case 'calendar':
            if ($p_filter[0] == 'anfang' || $p_filter[0] == 'ende') {
                $p_filter[2] = ereg_replace("[.:,;/]", '_', $p_filter[2]);
                $split = explode('_', $p_filter[2]);
                if (count($split) > 1) {
                    $split[0] = substr('__'.$split[0], -2);
                    $split[1] = substr($split[1].'__', 0, 2);
                    $p_filter[1] = 'begins';
                    $p_filter[2] = $split[0].$split[1];
                    $ret = $p_filter;
                }
            }
            else if ($p_filter[0] == 'datum') {
                if (strlen($p_filter[2]) == 10) {
                    $p_filter[2] = $date_format_object->convert_user2db($p_filter[2]);
                    $ret = $p_filter;
                }
            }
            break;
    }
    return $ret;
}

/**
 * Function to get the 'option value' of a filter from his id
 *
 * @param string 	$module      	- Module name
 * @param string 	$col_name    	- Field name
 * @param misc   	$field_value 	- Value of the field
 * @return string             			HTML output
 */
function get_filter_value_text($module, $col_name, $field_value) {
    // We need this global values
    global $field_name, $field, $fields, $is_related_obj;

    // Note: $field_name and $field will be get as global by col_filter function,
    // then, we need to save this values before call the function.
    $field_name_old = $field_name;
    $field_old = $field;

    // storing the values we need
    $field_name = $col_name;
    $field = $fields[$field_name];

    // we receive the html with the options
    $values = col_filter($module, $col_name, " ", 12);

    $field_name = $field_name_old;
    $field = $field_old;

    // we will 'parse' the options to get the
    $result_from = strpos($values,"<option value=\"$field_value\"");

    if ($result_from === false) {
        $result_from = strpos($values,"<option value='$field_value'");
    } else {
        $result_from = strpos($values,">",$result_from);
    }

    // if no 'option value' found, then we will return the id , else we will send the value
    if ($result_from === false) {
        $to_return = $field_value;
    } else {
        // getting the value between > and </option>
        $result_from = strpos($values,">",$result_from) + 1;

        $result_to = strpos($values,"</option>",$result_from);

        $to_return = substr($values,$result_from,$result_to - $result_from);
    }
    return $to_return;
}

/**
 * Put a value in the filter array
 *
 * @param string 	$name           		- Variable's name
 * @param string 	$rule           		- Filter's rule
 * @param misc   	$value          		- Variable's value
 * @param string 	$other_module  	- An optional module (for related object and summary page)
 * @return void
 */
function put_filter_value($name,$rule,$value,$other_module = '') {
    global $module, $flist;
    if (isset($other_module)&&($other_module != ''))    $use_module = $other_module;
    else                                                $use_module = $module;
    if (is_array($flist[$use_module])) {
        $flist[$use_module][] = array($name, $rule, $value);
    } else {
        $flist[$use_module] = array(array($name, $rule, $value));
    }
}

/**
 * Get a value from the filter array
 *
 * @param string 	$name     - Variable's name
 * @param string  	$module   - Module to show
 * @return string          			Variable's value
 */
function get_filter_value($name,$module) {
    global $flist;

    $value = '';
    if (is_array($flist[$module])) {
        if (!empty($flist[$module])) {
            foreach($flist[$module] as $key => $a_filter) {
                if ($a_filter[0] == $name) {
                    $value = $flist[$module][$key][2];
                }
            }
        }
    }
    return $value;
}

/**
 * Get the rules array
 * This is in a function because many functions need it
 *
 * @param void
 * @return array 		Array of filters rules
 */
function get_filter_rules_array() {
    $filter_rules = array( 'like'     => __('contains'),
    'exact'    => __('exact'),
    'begins'   => __('starts with'),
    'ends'     => __('ends with'),
    '>'        => __('>'),
    '>='       => __('>='),
    '<'     => __('<'),
    '<='    => __('<='),
    'not like' => __('does not contain')
    );
    return $filter_rules;
}

/**
 * Checks if the expert filter is valid (brackets and quotes)
 *
 * @param string 	$filter 	- Filter to be tested
 * @param string 	$table  	- Table where the filter will be aplied
 * @param array  	$fields 	- Array with field list of db_manager
 * @return boolean      			True or false depending on provided filter
 */
function expert_filter_check($filter, $table = '', $fields = array()) {

    $filter = stripslashes($filter);
    $valid = 1;
    $filter_len = strlen($filter);

    // *************************
    // Check invalid characters
    // *************************
    // TODO: filter [  and  ]
    if (eregi("[/&\\]",$filter)) {
        $valid = 0;
    }

    // *****************************
    // Check invalid first character
    // *****************************
    // the first character can't be ' or "
    if (eregi("['\"]",substr($filter,0,1))) {
        $valid = 0;
    }

    // *****************************
    // Check Comment tags
    // *****************************
    // filter can't have --
    if (strpos($filter,"--") <> false) {
        $valid = 0;
    }

    // **************************
    // Check brackets and quotes
    // **************************
    $bracked_open  = 0;
    $bracket_close = 0;
    $double_quote  = 0;
    $single_quote  = 0;
    $bracked_opened = false;

    for ($i = 0; ($i <= $filter_len) && $valid == 1; $i++) {
        if (($filter[$i] == '(') && ($double_quote % 2 == 0) && ($single_quote % 2 == 0) ) {
            $bracket_open++;
            $bracked_opened = true;
        }
        if (($filter[$i] == ')') && ($double_quote % 2 == 0) && ($single_quote % 2 == 0) ) {
            if (!$bracked_opened) {
                $valid = 0;

            }
            $bracket_close = $bracket_close + 1;
            if ($bracked_open == $bracket_close) {
                $bracked_opened = false;
            }
        }
        if (($filter[$i] == '"') && ($single_quote % 2 == 0) ) {
            $double_quote++;
        }
        if (($filter[$i] == "'") && ($double_quote % 2 == 0) ) {
            $single_quote++;
        }
    }
    if (($bracket_open <> $bracket_close) || ($bracket_opened == true) || ($double_quote % 2 <> 0) || ($single_quote % 2 <> 0) ) {
        $valid = 0;
    }
    if ($table <> '' && is_array($fields)) {
        $where = expert_filter_translate($filter, $fields);
        $query = "SELECT count(*) FROM $table WHERE ($where)";
        //echo($query);
        $result = db_query($query);
        if ($result === false) {
            $valid = 0;
        }
    } else {
        $valid = 0;
    }
    return $valid;
}

/**
 * Translate the field names of the filters to be displayed on filters
 * If unstraslate is true will convert db names to user names (e.g. from remark to title or titulo)
 * If unstralate is false will comvert user names to field names (e.g. title to remark)
 *
 * @param string 			$filter 		- Filter to be translated
 * @param array 			$fields 		- List of fields of db_manager
 * @param unknown_type 	$untraslate 	- If true will convert db_names to user translated names
 * @return string 							Filter translated
 */
function expert_filter_translate ($filter, $fields = array(), $untraslate = false) {

    //$filter = stripslashes($filter);
    $toReturn = '';

    // initializing vars
    $outTranslate = '';
    $separator = '';
    $wordBuffer = '';
    $textSeparator = array('"',"'");
    $wordSeparator = array (">","<","=","("," ",")",",","\\");
    $doNotTranslateList = array("IN","LIKE","EQUAL","NOT","AND","OR");
    $textArea = false;
    $temp = array(); // for debugging

    // create an array with words
    $words = array();
    foreach ($fields as $oneFieldName => $oneField) {
        if (isset($oneField['form_name'])) {
            if (substr($oneField['form_name'],0,3) == '__(') {
                $fieldNameTranslated = str_replace(" ","_",__(substr($oneField['form_name'],4,-2)));
            } else {
                $fieldNameTranslated = $oneField['form_name'];
            }
        }
        if ($untraslate) {
            $temp = $fieldNameTranslated;
            $fieldNameTranslated = $oneFieldName;
            $oneFieldName = $temp;
        }
        $words[strtolower($fieldNameTranslated)] = $oneFieldName;
    }

    // Start reading char by char the filter
    for ($i = 0; strlen($filter) > $i; $i++) {
        // get the current char. It could be:
        // a) a single char (e.g. A..Z),
        // b) a word separator (e.g. space),
        // c) a text delimiter (e.g. single quotes)
        // note: we will not translate the content of the condition
        // (e.g. a filter with "owner = 'owner'" will be replaced with "von = 'owner'" and NOT with "von = 'von'")
        $currentChar = substr($filter,$i,1);

        // Starting with option c), the current char is a 'text delimiter' (e.g. " ')
        if (in_array($currentChar,$textSeparator)) {
            // We need to know if we are inside the text area or not (inside the quotes or not)
            if ($textArea && $separator == $currentChar) {
                // if we are inside the text are (xxxx = "here!") we will clear the buffer and start checking the word
                // next to the quote
                $textArea = false;
                $wordBuffer = '';
                $separator = '';
                $outTranslate .= $currentChar;
            } elseif ($textArea && ($separator <> $currentChar)) {
                $outTranslate .= $currentChar;
            } elseif (!$textArea) {
                // it is starting a text area, then we need to check if there are a word in buffer
                // note: we also check if the word is a 'reserved' word of SQL
                if (strlen($wordBuffer) > 0) {
                    // translation of the word on buffer (if it exists)
                    if (array_key_exists(strtolower($wordBuffer), $words) && !in_array(strtoupper($wordBuffer),$doNotTranslateList)) {
                        $outTranslate .= $words[strtolower($wordBuffer)];
                    } else {
                        $outTranslate .= $wordBuffer;
                    }
                }

                // now, initializing the textarea (e.g. von = "this is a text area")
                $outTranslate .= $currentChar;
                $wordBuffer = '';
                $separator = $currentChar;
                $textArea = true;
            }
        }
        // we will check the word separators (spaces, minor simbol, equal simbol, etc)
        elseif (in_array($currentChar,$wordSeparator)) {
            // If we are inside a text area we will print the char
            if ($textArea) {
                $outTranslate .= $currentChar;
            } else {
                // checking if there is any word to be translated
                if (strlen($wordBuffer) > 0) {
                    // translation of the word on buffer (if it exists)
                    if (array_key_exists(strtolower($wordBuffer), $words) && !in_array(strtoupper($wordBuffer),$doNotTranslateList)) {
                        $outTranslate .= $words[strtolower($wordBuffer)];
                    } else {
                        $outTranslate .= $wordBuffer;
                    }
                }

                // now, the text area stuff
                $outTranslate .= $currentChar;
            }
            $wordBuffer = '';
        } elseif ((!in_array($currentChar,$textSeparator)) && (!in_array($currentChar,$wordSeparator))) {
            // If we are inside a text area we will print the char, othewise we will put it into the buffer to be translated
            if (!$textArea) {
                $wordBuffer .= $currentChar;
            } else {
                $outTranslate .= $currentChar;
            }
        }
        //$temp[$i]['bufer'] = $wordBuffer;$temp[$i]['out'] = $outTranslate; $temp[$i]['current'] = $currentChar;
    }
    // At last, we will clear the buffer (and translate it if it is necessary
    if (strlen($wordBuffer) > 0) {
        // translation of the word on buffer (if it exists)
        if (array_key_exists(strtolower($wordBuffer), $words) && !in_array(strtoupper($wordBuffer),$doNotTranslateList)) {
            $outTranslate .= $words[strtolower($wordBuffer)];
        } else {
            $outTranslate .= $wordBuffer;
        }
        $wordBuffer = '';
    }
    //print_r($temp);die();
    return $outTranslate;
}
?>
