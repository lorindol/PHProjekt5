<?php
/**
 * Branches functions
 *
 * @package    	lib
 * @subpackage 	main
 * @author     	Albrecht Guenther, $Author: gustavo $
 * @licence     GPL, see www.gnu.org/copyleft/gpl.html
 * @copyright  	2000-2006 Mayflower GmbH www.mayflower.de
 * @version    	$Id: branches.inc.php,v 1.28 2008-03-04 14:31:50 gustavo Exp $
 */

// check whether the lib has been included - authentication!
if (!defined("lib_included")) die("Please use index.php!");

/**
 * Copies a full project branch
 *
 * @param int          $ID            	        		- ID of full project branch to be copied
 * @param int          $parent_ID     			- ID of parent where the new branch will be copied
 * @param int          $change_von    			- New von ID that will have all projects on new branch
 * @param boolean  $copy_todo 		      	- If true all related todos of the project will be copied
 * @param int          $baseTimestamp 		- Timestamp to be used as base for date changes
 * @param boolean  $skip_access_check		- Skip check?
 * @return int          								New project ID (of the first project of the branch)
 */
function copy_branch($ID, $new_parent_rootID, $change_von = 0, $copy_todo = false, $baseTimestamp = 0, $skip_access_check = false) {
    if ($skip_access_check || check_access_subecord($ID)) {
        // first check whether the target isn't a subrecord of the source
        check_subrecord($ID,$new_parent_rootID);
        // first copy the root record
        $new_ID = copy_record($ID,$new_parent_rootID, $change_von, $copy_todo, $baseTimestamp);
        // now fetch all children and walk thorugh the whole branch
        fetch_children($ID, $new_ID, $change_von, $copy_todo, $baseTimestamp);
    } else {
        message_stack_in(__("You don't have write access on this project or subprojects"),"projects","error");
    }

    return $new_ID;
}

/**
 * Copies all children of a project
 *
 * @param int $ID            			- ID of full project branch to be copied
 * @param int $parent_ID     		- ID of parent where the new branch will be copied
 * @param int $change_von    		- New von ID that will have all projects on new branch
 * @param int $baseTimestamp 	- Timestamp to be used as base for date changes
 * @return int              				New project ID (of the first project of the branch)
 */
function fetch_children($old_parent_ID, $new_parent_ID, $change_von = 0, $copy_todo = false, $baseTimestamp = 0) {
    $result = db_query("SELECT ID
                          FROM ".DB_PREFIX."projekte
                         WHERE parent = ".(int)$old_parent_ID."
                           AND is_deleted is NULL") or db_die();
    while ($row = db_fetch_row($result)) {
        $old_ID = $row[0];
        $new_ID = copy_record($row[0], $new_parent_ID, $change_von, $copy_todo, $baseTimestamp);
        fetch_children($old_ID, $new_ID, $change_von, $copy_todo, $baseTimestamp);
    }
}

/**
 * Copies a project
 *
 * @param int $ID         		- ID of project to be copied
 * @param int $parent_ID  	- ID of parent where the new project will be copied
 * @param int $change_von 	- New von ID that will have the new project
 * @return int           			New project ID (of the first project of the branch)
 */
function copy_record($ID, $parent_ID, $change_von = 0, $copy_todo = false, $baseTimestamp = 0) {
    global $date_format_object;

    $result = db_query("SELECT ".implode(',', get_project_fields()).", costcentre_id, contractor_id
                          FROM ".DB_PREFIX."projekte
                         WHERE ID = ".(int)$ID."
                           AND is_deleted is NULL") or db_die();
    $row = db_fetch_row($result);

    // and insert this as a new record
    $result = db_query("INSERT INTO ".DB_PREFIX."projekte
                                    (".implode(',', get_project_fields()).", costcentre_id, contractor_id)
                             VALUES ('".implode("','", $row)."')") or db_die();

    // fetch the ID of this new record
    $result = db_query("SELECT max(ID)
                          FROM ".DB_PREFIX."projekte") or db_die();
    $row = db_fetch_row($result);

    // copy of users and contacts
    $result = db_query("SELECT contact_ID, role
                          FROM ".DB_PREFIX."project_contacts_rel
                         WHERE project_ID = ".(int)$ID."
                           AND is_deleted is NULL") or db_die();

    while ($temp_row = db_fetch_row($result)) {


        $temp_result = db_query("INSERT INTO ".DB_PREFIX."project_contacts_rel
                                             (   project_ID,           contact_ID,             role)
                                      VALUES (".(int)$row[0].",".(int)$temp_row[0].",".(int)$temp_row[1].")") or db_die();
    }

    // at last, users
    $result = db_query("SELECT user_ID, role
                          FROM ".DB_PREFIX."project_users_rel
                         WHERE project_ID = ".(int)$ID."
                           AND is_deleted is NULL") or db_die();

    while ($temp_row = db_fetch_row($result)) {


        $temp_result = db_query("INSERT INTO ".DB_PREFIX."project_users_rel
                                    (    project_ID  ,             user_ID ,             role)
                             VALUES (".(int)$row[0].",".(int)$temp_row[0].",".(int)$temp_row[1].")") or db_die();
    }

    // don't forget to assign the new record to the current parent!
    $result = db_query("UPDATE ".DB_PREFIX."projekte
                               SET parent = ".(int)$parent_ID."
                             WHERE ID = ".(int)$row[0]) or db_die();

    // at last, update the von of the new project
    if ($change_von <> 0) {
        $result = db_query("UPDATE ".DB_PREFIX."projekte
                                   SET von = ".(int)$change_von."
                                 WHERE ID = ".(int)$row[0]) or db_die();
    }

    if ($copy_todo) {
        $result = copy_record_todo($ID,$row[0],$change_von, $baseTimestamp);

        // we will also copy the costunits
        $result = copy_record_costunit($ID,$row[0]);
    }

    // If is set a base time then we will move the dates of the new project
    if (isset($baseTimestamp) && $baseTimestamp > 0) {

        // Getting the difference between today and the base date
        $deltaT = date('U',mktime(0,0,0,date('m'),date('d'),date('Y'))) - $baseTimestamp;

        // now, we will get the dates from the project
        $result = db_query("SELECT anfang, ende
                              			FROM ".DB_PREFIX."projekte
                             		   WHERE ID = ".(int)$row[0]."
                               			  AND is_deleted is NULL") or db_die();

        if ($dateRow = db_fetch_row($result)) {
            // starting with anfang
            // if it is not a db date we can't do anything (because we havent the original user format)
            if ($date_format_object->is_db_date($dateRow[0])) {

                // the new start date is the original date plus the delta time
                $newAnfang = $date_format_object->get_date_from_timestamp($date_format_object->get_timestamp_from_date($dateRow[0]) + $deltaT);
            }
            else {
                $newAnfang = $dateRow[0];
            }

            // The same process with ende
            if ($date_format_object->is_db_date($dateRow[1])) {
                $newEnde = $date_format_object->get_date_from_timestamp($date_format_object->get_timestamp_from_date($dateRow[1]) + $deltaT);
            }
            else {
                $newEnde = $dateRow[0];
            }

            // updating copied project
            $result = db_query("UPDATE ".DB_PREFIX."projekte
                                   			    SET anfang = '".$newAnfang."',
                                       					    ende = '".$newEnde."'
                                			   WHERE ID = ".(int)$row[0]) or db_die();
        }
    }
    return $row[0];
}

/**
 * Check the subrecord for validate the move action
 *
 * @param int $ID                 			- ID of project to be copied
 * @param int $new_parent_rootID  - ID of parent where the new project will be copied
 * @return void
 */
function check_subrecord($ID, $new_parent_rootID) {
    $proj_err_mes  = 'Sorry but the target record is a subrecord of the source! ';
    $proj_err_mes .= 'This would lead to a vanishing project branch. Please try it again';

    // check whether the current record is the target
    if ($ID == $new_parent_rootID) {
        die("$proj_err_mes");
    }
    else {
        // loop over children
        $result = db_query("SELECT ID
                              FROM ".DB_PREFIX."projekte
                             WHERE parent = ".(int)$ID."
                               AND is_deleted is NULL") or db_die();
        while ($row = db_fetch_row($result)) {
            check_subrecord($row[0], $new_parent_rootID);
        }
    }
}

/**
 * Move the date of the project (Recursive)
 *
 * @param int    		$ID        - ID of parent project
 * @param string 	$field     - Date field
 * @param int    		$days     - Difference days
 * @return void
 */
function move_branch($ID, $field, $days) {
    if (check_access_subecord($ID)) {
        // get the value
        $result = db_query("SELECT ".qss($field)."
        	                  FROM ".DB_PREFIX."projekte
            	             WHERE ID = ".(int)$ID."
                	           AND is_deleted is NULL") or db_die();
        $row = db_fetch_row($result);
        $olddate = explode('-',$row[0]);
        $newdate = date('Y-m-d', mktime(0, 0, 0, $olddate[1], $olddate[2]+$days, $olddate[0]));
        $result = db_query("UPDATE ".DB_PREFIX."projekte
        	                   SET ".qss($field)." = '$newdate'
            	             WHERE ID = ".(int)$ID) or db_die();

        // Moving todos of the project
        if ($field =='ende') {
            $todo_field = 'deadline';
        } else $todo_field = $field;

        $result_todo = db_query("SELECT ID, ".qss($todo_field)."
        	                  FROM ".DB_PREFIX."todo
            	             WHERE project = ".(int)$ID."
                	           AND is_deleted is NULL
                	           AND ".qss($todo_field)." <> '' ") or db_die();
        
        
        while ($row_todo = db_fetch_row($result_todo)) {
            $olddate = explode('-',$row_todo[1]);
            $newdate = date('Y-m-d', mktime(0, 0, 0, $olddate[1], $olddate[2]+$days, $olddate[0]));
            $result = db_query("UPDATE ".DB_PREFIX."todo 
        	                   SET ".qss($todo_field)." = '$newdate'
            	             WHERE ID = ".(int)$row_todo[0]) or db_die();
        }


        // loop over children
        $result = db_query("SELECT ID
        	                  FROM ".DB_PREFIX."projekte
            	             WHERE parent = ".(int)$ID."
                	           AND is_deleted is NULL") or db_die();
        while ($row = db_fetch_row($result)) {
            move_branch($row[0], $field, $days);
        }
    }
    else {
        message_stack_in(__("You don't have write access on this project or subprojects"),"projects","error");
    }
}

/**
 * Return an array with project fields
 *
 * @param  void
 * @return array  Fields of the project
 */
function get_project_fields() {
    $project_fields = array('div1','div2','acc','acc_write','gruppe');
    $query = "SELECT db_name
              FROM ".DB_PREFIX."db_manager
              WHERE db_table = 'projekte'
               AND form_pos > 0
               AND db_inactive <> 1";
    $result = db_query($query) or db_die();
    while ($row = db_fetch_row($result)) { $project_fields[] = $row[0]; }
    return $project_fields;
}

/**
 * Copies all todos related to a project
 *
 * @param int $source_ID      		- ID of project where the source todos are
 * @param int $destination_ID 	- ID of project where todos needs to be copied
 * @param int $change_von     		- New von ID that will have the new todos
 * @return boolean           			True if ti works fine
 */
function copy_record_todo($source_ID, $destination_ID, $change_von = 0, $baseTimestamp = 0) {
    global $dbTSnull, $date_format_object;

    if (isset($baseTimestamp) && $baseTimestamp > 0) {
        // Getting the difference between today and the base date
        $deltaT = date('U',mktime(0,0,0,date('m'),date('d'),date('Y'))) - $baseTimestamp;
    }
    else $deltaT = 0;

    $main_result = db_query("SELECT anfang, deadline, ".implode(',', get_todo_fields())."
                               FROM ".DB_PREFIX."todo
                              WHERE project = ".(int)$source_ID."
                                AND is_deleted is NULL") or db_die();

    while ($temp_row = db_fetch_row($main_result)) {
        if ($deltaT <> 0) {

            if ($date_format_object->is_db_date($temp_row[0])) {
                // the new start date is the original date plus the delta time
                $temp_row[0] = $date_format_object->get_date_from_timestamp($date_format_object->get_timestamp_from_date($temp_row[0]) + $deltaT);
            }
            if ($date_format_object->is_db_date($temp_row[1])) {
                // the new end date is the original date plus the delta time
                $temp_row[1] = $date_format_object->get_date_from_timestamp($date_format_object->get_timestamp_from_date($temp_row[1]) + $deltaT);
            }

        }

        // and insert this as a new record
        $temp_result = db_query("INSERT INTO ".DB_PREFIX."todo
                                        (datum, anfang, deadline, ".implode(',', get_todo_fields()).", von, project)
                                 VALUES ('$dbTSnull','".implode("','", $temp_row)."',".(int)$change_von.",".(int)$destination_ID.")") or db_die();
    }
    return true;
}


/**
 * Copies all cost units related to a project
 *
 * @param int $source_ID        - ID of project source
 * @param int $destination_ID 	- ID of project destination
  * @return boolean    			True if it works fine
 */
function copy_record_costunit($source_ID, $destination_ID) {
    global $dbTSnull, $date_format_object;


    $main_result = db_query("SELECT costunit_id, fraction
                               FROM ".DB_PREFIX."projekte_costunit 
                              WHERE projekte_ID = ".(int)$source_ID) or db_die();

    while ($temp_row = db_fetch_row($main_result)) {

        // and insert this as a new record
        $temp_result = db_query("INSERT INTO ".DB_PREFIX."projekte_costunit
                                        (projekte_ID, costunit_id, fraction)
                                 VALUES (".(int)$destination_ID.",".(int)$temp_row[0].",'".$temp_row[1]."')") or db_die();
    }
    return true;
}

/**
 * Gets the complete list of todo fields
 *
 * @param  void
 * @return array  Array with todo field names
 */
function get_todo_fields() {
    // phone, sync1, sync2
    $todo_fields = array('div1','div2','acc','acc_write','gruppe');
    $query = "select db_name
                from ".DB_PREFIX."db_manager
               where db_table = 'todo'";
    $result = db_query($query) or db_die();
    while ($row = db_fetch_row($result)) {
        if ($row[0] <> 'project' && $row[0] <> 'von' && $row[0] <> 'datum' && $row[0] <> 'deadline' && $row[0] <> 'anfang') {
            $todo_fields[] = $row[0];
        }
    }
    return $todo_fields;
}

function check_access_subecord($ID)  {
    global $user_ID,$user_kurz;

    $access = true;
    $result = db_query("SELECT acc
                          FROM ".DB_PREFIX."projekte
                         WHERE (acc LIKE 'system'
                                OR ((von = ".(int)$user_ID."
                                OR acc LIKE 'group'
                                OR acc LIKE '%\"$user_kurz\"%'))
                                AND acc_write = 'w'
                                AND ID = ".(int)$ID.")") or db_die();
    $row = db_fetch_row($result);
    if (!empty($row)) {
        $result2 = db_query("SELECT ID
                               FROM ".DB_PREFIX."projekte
                              WHERE parent = ".(int)$ID) or db_die();
        while ($row2 = db_fetch_row($result2)) {
            $access = check_access_subecord($row2[0]);
        }
    } else {
        $access = false;
    }
    return $access;
}
?>
