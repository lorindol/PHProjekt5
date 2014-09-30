<?php
/**
* handles module related working time
*
* This file stores common functions for reading and writing
* project-related times that are referenced from modules.
*
* @package    library
* @module     timecard
* @author     Franz Graf, $Author: thorsten $
* @licence    GPL, see www.gnu.org/copyleft/gpl.html
* @copyright  2000-2006 Mayflower GmbH www.mayflower.de
* @version    $Id: timeproj.inc.php,v 1.14.2.4 2007/02/27 07:42:40 thorsten Exp $
*/
if (!defined('lib_included')) die('Please use index.php!');


/**
* Build html-container with all related times.
* The output can be used directly within an inner-content-div.
* It is like a 'view' for the data provided by timeproj_get_records()
* Used in modules to display project related times.
*
* @uses timeproj_get_records()
* @param int $record_ID id of the record whose times should be listed
* @param string $module the module wich calls the method (todo, ...) (dirty)
* @param int $user_ID optional userId if only entries of this user should be shown
* @return string complete HTML-string
*/
function timeproj_get_list_box($record_ID, $module, $user_ID = null) {
    // get relevant data
    $records = timeproj_get_records($record_ID, $module, $user_ID);
    $sum = array("hour" => 0, "minute" => 0);

    // build tablerows
    $rows = "";
    foreach ($records AS $a_record) {
        $rows .= '<tr>
                    <td class="book2">'.html_out($a_record['note']).'</td>
                    <td class="book2">'.$a_record['date'].'</td>
                    <td class="book2">'.$a_record['h'].'</td>
                    <td class="book2">'.$a_record['m'].'</td>
                    <td class="book2"><input type="checkbox" name="timeproj_delete[]" value="'.$a_record['id'].'"/></td>
                  </tr>';
        $sum["hour"]   += $a_record['h'];
        $sum["minute"] += $a_record['m'];
    }
    unset($a_record);

    $sum["hour"]   += floor($sum["minute"] / 60);
    $sum["minute"]  = $sum["minute"] % 60;

    // build table
    $output = '
    <fieldset>
    <legend>'.__('project related times').'</legend>
        <table>
          <thead>
            <tr><th>'.__('Comment').'</th> <th>'.__('Date').'</th> <th colspan="2">'.__('Hours').'</th> <th>'.__('Delete').'</th></tr>
          </thead>
          <tbody>
            <tr>
                <td><input type="text" size="30" name="timeproj_add[note]"/></td>
                <td><input type="text" size="10" name="timeproj_add[date]"    maxlength="10" value="'.date('Y-m-d').'"/></td>
                <td><input type="text" size="3"  name="timeproj_add[hours]"   maxlength="2"/></td>
                <td><input type="text" size="3"  name="timeproj_add[minutes]" maxlength="2"/></td>
                <td></td>
            </tr>
            '.$rows.'
            <tr>
                <td>'.__('Sum').'</td>
                <td></td>
                <td>'.$sum["hour"].'</td>
                <td>'.$sum["minute"].'</td>
                <td></td>
            </tr>
          </tbody>
        </table>
    </fieldset>
    ';

    return $output;
}


/**
* Get records from timeproj that are associated with a certain module
* and record-ID.
*
* @param int    $record_ID ID of the parentrecord in the module-table
* @param string module tablename of the module containing the parent. without prefix!
* @param int	$user_ID optional userId if only entries of this user should be shown
* @return array of associative arrays with keys: id, date, h, m, note
*/
function timeproj_get_records($record_ID, $module, $user_ID = null) {
    $records = array();
    $where_user = "";

    // restrict selection to a specific user?
    if ($user_ID != null) {
    	$where_user = " AND users = ".(int)$user_ID." ";
    }

    $query = "SELECT id, datum, h, m, note
                FROM ".DB_PREFIX."timeproj
               WHERE module='$module' AND module_id = ".(int)$record_ID." $where_user
            ORDER BY datum, note";
    $result = db_query(xss($query)) or db_die();
    while ($row = db_fetch_row($result)) {
        $records[] = array('id'   => $row[0],
                           'date' => $row[1],
                           'h'    => $row[2],
                           'm'    => $row[3],
                           'note' => $row[4]);
    }
    unset($query, $result, $row);

    return $records;
}


/**
* Write a record into the timeproj-table.
* The record is ONLY written, if
* - $timeproj is set with hour, minute and date fields filled.
* - $user_ID and $project_ID set
* - date not older than PHPR_TIMECARD_ADD days
*
* @uses PHPR_TIMECARD_ADD
* @param int $user_ID
* @param int $record_ID id of the module-data-row (module_id)
* @param int $project_ID
* @param string $module tablename of the module the record belongs to
* @param array $timeproj with keys: date (ISO), hours, minutes, note
*/
function timeproj_insert_record($user_ID, $record_ID, $project_ID, $module, $timeproj) {

    // check if $user_ID is set
    if (!isset($user_ID) or empty($user_ID)) {
        message_stack_in(sprintf (__('You cannot add  bookings as no user ID is availabe.'), $diff, PHPR_TIMECARD_ADD), $module, "error");
        return;
    }

    // check if data is empty
    if (!isset($timeproj) or
        empty($timeproj['date']) or
        (empty($timeproj['hours']) and empty($timeproj['minutes']))) {
        return;
    }

        // check if $project_ID is set
    if (!isset($project_ID) or empty($project_ID))  {
        message_stack_in(sprintf (__('A project must be selected in order to add bookings.'), $diff, PHPR_TIMECARD_ADD), $module, "error");
        return;
    }

    // check if the date is within the valid range
    $diff = time() - strtotime($timeproj['date']);
    $diff = floor($diff/86400);
    if ($diff > PHPR_TIMECARD_ADD) {
        message_stack_in(sprintf (__('You cannot add  bookings at this date. Since there have been %s days. You just can add bookings for entries not older than %s days.'), $diff, PHPR_TIMECARD_ADD), "timecard", "error");
        return;
    }
    unset($diff);

    // check date
    $tmp_date = explode("-", $timeproj['date']);
    if (!checkdate($tmp_date[1], $tmp_date[2], $tmp_date[0])) {
        message_stack_in(__('Please check the date!'), 'todo', 'error');
        return;
    }
    unset($tmp_date);

    $query = "INSERT INTO ".DB_PREFIX."timeproj
        (users, projekt, datum, h, m, note, module, module_id)
    VALUES  (".(int)$user_ID.", ".(int)$project_ID.", '".strip_tags($timeproj['date'])."', ".(int)$timeproj['hours'].", ".(int)$timeproj['minutes'].", '".strip_tags($timeproj['note'])."', '".qss($module)."', ".(int)$record_ID.")";
    $result = db_query($query) or db_die();
}


/**
* Delete record(s) from timeproj by ID
*
* @param misc $ids row-ID or array of IDs
*/
function timeproj_delete_record($ids) {
    if (!isset($ids) or empty($ids)) { return; }

    if (is_array($ids)) { $ids = implode("','", $ids); }

    $query = "DELETE
                FROM ".DB_PREFIX."timeproj
               WHERE id IN ('$ids')";
    db_query(xss($query)) or db_die();
}


/**
* Delete rows in timeproj that belong to the rowID $parent_ID in the
* module $module
*
* @param int $parent_ID id of the record in the module
* @param string $module
*/
function timeproj_delete_moduletimes($parent_ID, $module) {
    if (empty($module) or empty($parent_ID)) { return; }

    $query = "DELETE
                FROM ".DB_PREFIX."timeproj
               WHERE module='".qss($module)."' AND module_id = ".(int)$parent_ID;
    db_query($query) or db_die();
}


/**
 * Remove the link between an entry in timeproj and the module.
 * I.e: a todo is deleted, but the project-times should remain =>
 * clear module & module_id
 *
 * @param string $module  the module-identifier (i.e todo)
 * @param int    $module_id id of the deleted record in the module-table
 */
function timeproj_unlink_moduletimes ($module_id, $module) {
	if (empty($module) || empty($module_id)) {
		return;
	}

	$query = "UPDATE ".DB_PREFIX."timeproj
	             SET module = NULL, module_id = NULL
	           WHERE module='".qss($module)."' AND module_id = ".(int)$module_id;
	db_query($query) or db_die();
}


/**
 * Change the project id of module-related times
 *
 * @param string $module
 * @param int_type $module_id
 * @param int $project_id
 */
function timeproj_change_project_id ($module, $module_id, $project_id) {
	if (empty($module) || empty($module_id) || empty($project_id) || 0 == $project_id) {
		return;
	}

	$query = "UPDATE ".DB_PREFIX."timeproj
	             SET projekt = ".(int)$project_id."
	           WHERE module = '".qss($module)."' AND module_id = ".(int)$module_id;
	db_query($query) or db_die();
}

?>