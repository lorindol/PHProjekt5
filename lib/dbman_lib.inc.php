<?php
/**
 * Misc functions for all the scripts
 *
 * @package    	lib
 * @subpackage 	main
 * @author     	Albrecht Guenther, $Author: gustavo $
 * @licence     GPL, see www.gnu.org/copyleft/gpl.html
 * @copyright  	2000-2006 Mayflower GmbH www.mayflower.de
 * @version    	$Id: dbman_lib.inc.php,v 1.94 2007-05-31 08:11:52 gustavo Exp $
 */

// check whether the lib has been included - authentication!
if (!defined("lib_included")) die("Please use index.php!");

// space for element names which are displayed on the left side before the input element
$text_width = 75;

// include lib to fetch the sessiond data and to perform check
if (!PATH_PRE) define('PATH_PRE','../');
include_once(PATH_PRE.'lib/lib.inc.php');

include_once(LIB_PATH.'/dbman_forms.inc.php');
include_once(LIB_PATH.'/dbman_list.inc.php');
include_once(LIB_PATH.'/dbman_filter.inc.php');
include_once(LIB_PATH.'/dbman_data.inc.php');

// include context menu in any other case
$contextmenu = isset($contextmenu) ? (int) $contextmenu : 0;
if ($contextmenu == 1) {
    include_once(LIB_PATH.'/contextmenu.inc.php');
    $menu2 = new contextmenu();
}

/**
 * Fetch all elements of a form
 * it's properties and the related values if an ID is given
 *
 * @param string 	$module 	- Module name
 * @param int    		$ID     	- Record ID
 * @param string 	$mode   	- Type of view: list_alt/forms/data/show
 * @return array         			Array with all the data field
 */
function build_array($module, $ID, $mode='forms', $id_field='ID') {
    global $tablename;

    // determine whether the list of the form mode is active - and then just fetch those records which have a position value > 0
    switch($mode){
        case 'list_alt':
            $mode2 = 'list_alt != \'\'';
            $order = 'list_alt'; // we will order the list_alt by itself
            break;
        case 'forms':
        case 'data':
            $mode2 = 'form_pos > 0';
            $order = 'form_pos';
            break;
        case 'show':
            $mode2 = '(filter_show > \'\' or filter_show=\'on\') ';
            $order = 'list_pos';
            break;
        default:
            $mode2 = 'list_pos > 0';
            $order = 'list_pos';
            break;
    }
    $table = isset($tablename[$module]) ? $tablename[$module] : $module;
    $table = ($table == 'files') ? 'dateien' : $table;
    // form array for fields
    $query="SELECT db_name, form_name, form_type, form_tooltip, form_pos, form_regexp,
                               form_default, form_select, list_pos, filter_show, list_alt, db_table,
                               form_colspan, form_rowspan,form_length, field_type, form_tab 
                          FROM ".DB_PREFIX."db_manager
                         WHERE db_table LIKE '".qss($table)."'
                           AND $mode2
                           AND db_inactive <> 1
                      ORDER BY ".qss($order).", ID";
    $result = db_query($query) or db_die();
    while ($row = db_fetch_row($result)) {
        $fields[$row[0]] = array( 'form_name'    => $row[1],
                                  'form_type'    => $row[2],
                                  'form_tooltip' => $row[3],
                                  'form_pos'     => $row[4],
                                  'form_regexp'  => $row[5],
                                  'form_default' => $row[6],
                                  'form_select'  => $row[7],
                                  'list_pos'     => $row[8],
                                  'filter_show'  => $row[9],
                                  'list_alt'     => $row[10],
                                  'tablename'    => $row[11],
                                  'form_colspan' => $row[12],
                                  'form_rowspan' => $row[13],
                                  'form_length'  => $row[14],
                                  'field_type'   => $row[15],
                                  'db_name'      => $row[0],
                                  'form_tab'     => $row[16] );

    }
    if (count($fields)>=1) $db_fields = array_keys($fields);


    // fetch the values of this record - either with a valid ID (>0) or as dummy entries
    if (!ereg(',', $ID) and  $mode2 == 'form_pos > 0' and $ID > 0 and count($db_fields)>=1) {
        $i = 0;
        $result = db_query("SELECT ".implode(',', $db_fields)."
                              FROM ".qss(DB_PREFIX.$table)."
                             WHERE ".qss($id_field)." = ".(int)$ID) or db_die();
        $row = db_fetch_row($result);
        for ($i=0; $i < count($row); $i++) {
            $fields[$db_fields[$i]]['value'] = $row[$i];
        }
    }
    else {
        for ($i=0; $i < count($db_fields); $i++) {
            $fields[$db_fields[$i]]['value'] = '';
        }
        // add projectID and contactID
    }

    return $fields;
}

/**
 * If the mail module is installed, then all mailto: links
 * should point to this module
 *
 * @param string 	$mailadress 	- The mail
 * @param string 	$class      		- Class to use
 * @return string            			HTML output for mailto
 */
function showmail_link($mailadress, $class = '') {

    if ($class <> '') {
        $class = "class='$class'";
    }

    if (PHPR_QUICKMAIL > 0) {
        $str =  "<a href=\"javascript:mailto(0,'$mailadress','".(SID ? session_id() : '')."')\" $class>".format_mailaddress($mailadress)."</a>&nbsp;\n";
    } else {
        $str = "<a href='mailto:$mailadress' $class>".format_mailaddress($mailadress)."</a>&nbsp;\n";
    }
    return $str;
}

/**
 * returns the name of an email address
 *
 * @param string 	$mailaddress	- The mail
 * @return string            				The name
 */
function format_mailaddress($mailaddress) {
  if (ereg('&lt;',$mailaddress)) {
    $mail_parts = explode('&lt;',$mailaddress);
    return $mail_parts[0];
  }
  else return $mailaddress;
}

/**
 * Replaces strings with a dollars prefix with the value of the variables of the same name
 * OR constants declared between '@', eg. @DB_PREFIX@
 *
 * @param string 	$string  	- The string to fix
 * @return string         			The fixed string
 */
function enable_vars($string) {

    if (strpos($string, '__(') !== false) {

        // I'll get the information to be translated
        $matches = array();

        // single quotes
        preg_match_all("/__\('([^']*)'\)/", $string, $matches);
        if (is_array($matches[1]) && count($matches[1]) > 0) {
            foreach ($matches[1] as $dummy => $onePhrase) {
                $temp = __($onePhrase);
                $string = str_replace("__('".$onePhrase."')", $temp, $string);
                $string = str_replace('__("'.$onePhrase.'")', $temp, $string);
            }
        }

        $matches = array();

        // double quotes
        preg_match_all("/__\(\"([^\"]*)\"\)/", $string, $matches);

        if (is_array($matches[1]) && count($matches[1]) > 0) {
            foreach ($matches[1] as  $dummy => $onePhrase) {
                $temp = __($onePhrase);
                $string = str_replace("__('".$onePhrase."')", $temp, $string);
                $string = str_replace('__("'.$onePhrase.'")', $temp, $string);
            }
        }
    }

    $ret = preg_replace_callback('#\$\{?(\w+)\}?#si', 'enable_variable', $string);
    $ret = preg_replace_callback('#@(\w+)@#si', 'enable_constant', $ret);
    return $ret;
}

/**
 * Get the translated string
 *
 * @param string 	$varname  	- Name of the var
 * @return string          				Translated string
 */
function enable_variable($varname) {
    return $GLOBALS[$varname[1]];
}

/**
 * Get the translated string
 *
 * @param string 	$varname  	- Name of the constant
 * @return string          				Translated string
 */
function enable_constant($conname) {
    if (defined($conname[1])) {
        $defined_constants = get_defined_constants();
        return $defined_constants[$conname[1]];
    }
    return '';
}

/**
 * Sets the archiv flag to several entries
 *
 * @param array  	$ID     	- Ids of the entries
 * @param string 	$module 	- Module to which the entry belongs
 * @param int    		$flag   	- 1 to put in archive, 0 to remove them
 * @return void
 */
function set_archiv_flag($ID, $module, $flag) {
    global $user_ID, $dbTSnull;

    if ($flag == "0") $flag = "NULL";
    else $flag = " 1 ";

    $arr_ID = explode(',',$ID);
    // check which ID has already an entry
    $query = "SELECT t_record
                FROM ".DB_PREFIX."db_records
               WHERE t_record IN (".implode(",", xss_array($arr_ID)).") AND
                     t_author = ".(int)$user_ID." AND
                     t_module = '".DB_PREFIX.qss($module)."'";
    $result = db_query($query) or db_die();
    $ids = array();
    while($result_id = db_fetch_row($result)) {
        $ids = array_merge ($result_id,$ids);
    }

    //if (!is_array($ids)){ return; }
    if (!is_array($ids)){
        $ids=array();
    }

    foreach ($arr_ID as $ID) {
        if (in_array($ID, $ids)) {
            $result = db_query("UPDATE ".DB_PREFIX."db_records
                                       SET t_datum  = '$dbTSnull',
                                           t_archiv = ".(int)$flag."
                                     WHERE t_record = ".(int)$ID."
                                       AND t_module = '".DB_PREFIX.qss($module)."'
                                       AND t_author = ".(int)$user_ID) or db_die();
        }
        else {
            $query =  "INSERT INTO ".DB_PREFIX."db_records
                                   (        t_author ,                 t_module    ,    t_record,  t_datum   , t_archiv)
                            VALUES (".(int)$user_ID.", '".DB_PREFIX.qss($module)."',".(int)$ID.", '$dbTSnull', $flag)";
           $result = db_query($query) or db_die();
        }
    }
}

/**
 * Check the archiv flag to several entries
 *
 * @param array  	$ID     	- Ids of the entries
 * @param string 	$module 	- Module to which the entry belongs
 * @return boolean      			Value of the t_archiv field
 */
function check_archiv_flag($ID, $module) {
    global $user_ID;

    $result = db_query("SELECT t_archiv
                          FROM ".DB_PREFIX."db_records
                         WHERE t_record = ".(int)$ID."
                           AND t_author = ".(int)$user_ID."
                           AND t_module = '".DB_PREFIX."$module'");
    $ids = db_fetch_row($result);
    if (!is_array($ids)){ return False; }

    if ($ids[0] == 1) return True;
    else return False;
}

/**
 * Sets the read flag to several entries
 *
 * @param array  	$ID     	- Ids of the entries
 * @param string 	$module 	- Module to which the entry belongs
 * @return void
 */
function set_read_flag($ID, $module) {
    global $user_ID, $dbTSnull;

    $arr_ID = explode(',',$ID);
    // check which ID has already an entry
    $result = db_query("SELECT t_record
                          FROM ".DB_PREFIX."db_records
                         WHERE t_record IN (".implode(",", $arr_ID).")
                           AND t_author = ".(int)$user_ID."
                           AND t_module = '".DB_PREFIX."$module'");
    $ids = array();
    while($result_id = db_fetch_row($result)) {
        $ids = array_merge ($result_id,$ids);
    }

    //if (!is_array($ids)){ return; }
    if (!is_array($ids)){
        $ids=array();
    }

    foreach ($arr_ID as $ID) {
        if (in_array($ID, $ids)) {
            $query = "UPDATE ".DB_PREFIX."db_records
                         SET t_datum  = '".$dbTSnull."',
                             t_touched = 1
                       WHERE t_record = ".(int)$ID." AND
                             t_module = '".DB_PREFIX.qss($module)."' AND
                             t_author = ".(int)$user_ID;
            $result = db_query($query) or db_die();
        }
        else {
            $query = "INSERT INTO ".DB_PREFIX."db_records
                                  (       t_author,               t_module,          t_record,    t_datum, t_touched)
                           VALUES (".(int)$user_ID.", '".DB_PREFIX.qss($module)."',".(int)$ID.", '$dbTSnull', 1)";
            $result = db_query($query) or db_die();
        }
    }
}

/**
 * Sets the status flag to several entries
 *
 * @param array  	$ID     	- Ids of the entries
 * @param string 	$module 	- Module to which the entry belongs
 * @param int    		$status	- Status value
 * @return void
 */
function set_status($ID, $module, $status) {
    global $user_ID, $tablename;

    $arr_ID = explode(',', $ID);
    foreach ($arr_ID as $ID) {
        $query = "UPDATE ".qss(DB_PREFIX.$tablename[$module])."
                     SET status = ".(int)$status."
                   WHERE ID = ".(int)$ID;
       $result = db_query($query) or db_die();
    }
}

/**
 * Make the submit button
 *
 * @param string 	$url         			- Action for the form
 * @param string 	$method      		- Method of the form
 * @param array  	$hidden      		- Array with hidden values
 * @param string 	$submitname  	- Name of the submit button
 * @param string 	$submitvalue 	- Value of the submit button
 * @return string            				HTML output
 */
function set_button($url, $method='post', $hidden, $submitname='submit', $submitvalue) {
    $str = "<form action='$url' method='$method'>\n";
    foreach ($hidden as $hidden_name => $hidden_value) {
        $str .= "    <input type='hidden' name='$hidden_name' value='$hidden_value' />\n";
    }
    $str .= "    <input class='center' type='submit' name='$submitname' value='$submitvalue' />\n";
    $str .= "</form>\n";
    return $str;
}

/**
 * Functions determines wether all groups are shown or not
 *
 * @param string 	$module 	- Module name
 * @return string        			WHERE clause
 */
function group_string($module='') {
    global $sql_user_group, $user_ID;
        //get all groups the current user is allowed to access
        if($module<>'' AND (isset($_SESSION['show_all_groups']["$module"]) && $_SESSION['show_all_groups']["$module"] != 1)) {
            return " AND $sql_user_group";
        }
        else if(count($_SESSION['user_all_groups'])>0){
            $groups=array_keys($_SESSION['user_all_groups']);
            return 'AND gruppe IN ('.implode(',',$groups).')';
        }
        //else: admin!
        return '';
}
?>
