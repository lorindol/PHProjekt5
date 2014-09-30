<?php

// timecard.inc.php - PHProjekt Version 5.2

// copyright  ©  2000-2004 Albrecht Guenther  ag@phprojekt.com

// www.phprojekt.com

// Author: Franz Graf

// $Id: timecard.inc.php,v 1.19.2.2 2007/04/02 11:17:34 nina Exp $



// check whether the lib has been included - authentication!

if (!defined('lib_included')) die('Please use index.php!');



/**

 * Parse template rows

 * @param string $template identifier for the templatestring (form|row|modulehead)

 * @param int	 $project_id

 * @param array  $values  i.e.: array("ID" => 4)

 * @return string replaced template

 */

function timecard_parse_template($template, $project_id, $values,$indent=0) {

    global $arrproj1, $date;

    $html = "";

    switch ($template) {

        case "project_time_form":

            // form-row for a project-related MODULE-time

            // M_ID:   ID of the referencing row in the corresponding-module-table

            // M_TYPE: module in plaintext

            $html = "<tr class='book3'>

	                  <td class='book3' style='padding-left:10px;'>";

            for ($i=0; $i < $indent; $i++) { $html.= "&nbsp;&nbsp;&nbsp;&nbsp;"; }

            $html.= "<img src='".IMG_PATH."/t.gif' width='9' height='9' />&nbsp;";

            $id="{M_ID}".$project_id;

            $html.=" {DESCRIPTION}:</td>

	                  <td class='book3'><input type='text' name='note[]'  size='30' /></td>

	                  <td style='white-space:nowrap;text-align:right;'>

	                        <input type='hidden' name='module_id[]'   value='{M_ID}'>

	                        <input type='hidden' name='module_name[]' value='{M_TYPE}'>

	                        <input type='text' name='h[]' id='h_$id' size='2' maxlength='2' onchange=\"chktime('h_$id','0 - 23!',/[2][0-3]|[0-1]?\d?/)\" />

	                        <input type='text' name='m[]' id='m_$id' size='2' maxlength='2' onchange=\"chktime('m_$id','0 - 59!',/[0-5]?\d?/)\"/> </td>

	                  <td><input type='hidden' name='nr[]' value='$project_id' />\n</td>

	             </tr>\n";

            break;



        case "project_time_row":

            // row for project-related module-time

            // ID: timeproj-id

            $html = "<tr class='book2'>

		                  <td class='book2'></td>

		                  <td class='book2'>{DESCRIPTION}</td>

		                  <td class='book2'>{HOURS} h {MINUTES} m</td>

		                  <td class='book2'><input type='checkbox' name='del[]' value='{ID}'/></td>

		             </tr>\n";

            break;



        case 'project_time_modulehead':

            // header indicating current module

            $html = "<tr class='book1'>

	                  <td class='book1' >";

            for ($i=0; $i < $indent; $i++) { $html.= "&nbsp;&nbsp;&nbsp;&nbsp;"; }

            $html.="<img src='".IMG_PATH."/t.gif' width='9' height='9' />";

            $mod=$values["M_TYPE"];

            if (!$arrproj1[$mod][$project_id]) {

                $no = "open".$mod.$project_id;

                $html.= "<a href='timecard.php?submode=proj&date=$date&element2_mode=modopen&element2_ID=$project_id&mod=$mod'>

         		  ".tree_icon("open","name='$no' style='border-style:none;'")."</a>&nbsp; ";

            }

            else {

                $nc= "close".$mod.$project_id;

                $html.= "<a href='timecard.php?submode=proj&date=$date&element2_mode=modclose&element2_ID=$project_id&mod=$mod'>

          			".tree_icon("close","name='$no' style='border-style:none;'")."</a>&nbsp;";

            }

            $html.=" {MODULE_NAME}:</td><td></td><td></td><td></td></tr>";

            break;

    }



    // replace

    foreach ($values AS $key => $value) {

        $html = str_replace("{".strtoupper($key)."}", html_out($value), $html);

    }

    return $html;

}





/**

 * Get all Todos for the given user & project and return an array of all TODOs.

 * result will look like

 * array(

 * 		0 => array (id => 1, remark => "todo title1"),

 * 		1 => array (id => 2, remark => "todo title12")

 * )

 *

 * @param int $user_ID

 * @param int $project_id

 * @return array array of todos.

 */

function timecard_get_project_todos($user_ID, $project_id, $include_IDs=array()) {

    $include='';

    if (is_array($include_IDs)) $ids = implode(',',$include_IDs);

    else                        $ids = $include_IDs;

    if ($ids<>''){

        $include=" OR ID in ($ids)";

    }

    $today=date("Y-m-d");

    $where='';

    

    if (PHPR_TIMECARD_BOOK_USER == 1) $where.= " AND ext =".(int)$user_ID." OR von =".(int)$user_ID." ";

    if (PHPR_TIMECARD_BOOK_STATUS==1) $where.= " AND status < 4 OR status IS NULL";

    

    $query = "SELECT id, remark

                FROM ".DB_PREFIX."todo

               WHERE (project = ".(int)$project_id." $where)

                 $include

                 ORDER BY remark ";

    $result = db_query($query) or db_die();



    $todos = array(); // return this

    while ($a_todo = db_fetch_row($result)) {

        $todos[] = array('id' => $a_todo[0], 'remark' => $a_todo[1]);

    }

    return $todos;

}



/**

 * Get all Tickets for the given user & project and return an array of all Tickets.

 * result will look like

 * array(

 * 		0 => array (id => 1, remark => "ticket title1"),

 * 		1 => array (id => 2, remark => "ticket title12")

 * )

 *

 * @param int $user_ID

 * @param int $project_id

 * @return array array of tickets.

 */

function timecard_get_project_tickets($user_ID, $project_id) {

    $where='';

    if(PHPR_TIMECARD_BOOK_USER==1)$where.= " AND assigned = '$user_ID' OR von = ".(int)$user_ID;

    if(PHPR_TIMECARD_BOOK_STATUS==1)$where.= " AND status < 3";

    $query = "SELECT id, name

                FROM ".DB_PREFIX."rts

               WHERE proj = ".(int)$project_id." ".$where." 

            ORDER BY name ";

    $result = db_query($query) or db_die();



    $tickets = array(); // return this

    while ($a_ticket = db_fetch_row($result)) {

        $tickets[] = array('id' => $a_ticket[0], 'remark' => $a_ticket[1]);

    }

    return $tickets;

}





/**

 * Get all project and module-related times for the specified day, module and module_id

 * Keys of the returned array-components:

 * h, m, note, id

 *

 * @param int $user_ID

 * @param string $module

 * @param int $module_id the referenced element (or empty)

 * @param string $day

 * @return array array of project-times

 */

function timecard_get_module_bookings($user_ID, $module, $module_id, $day) {

    if ($module == "-") $module = "";

    // get the related times and show them

    $query = "SELECT h, m, note, id

                FROM ".DB_PREFIX."timeproj

               WHERE users = ".(int)$user_ID." 

                 AND module_id = ".(int)$module_id." 

                 AND module = '$module'

                 AND datum like '$day'

    	    ORDER BY id ";

    $result = db_query($query) or db_die();



    $times = array();

    while ( $a_time = db_fetch_row($result)) {

        $times[] = array("h" => sprintf("%01d", $a_time[0]),

        "m" => sprintf("%01d", $a_time[1]),

        "note" => $a_time[2],

        "id" => $a_time[3]);

    }

    return $times;

}





/**

 *

 * Displays a form to enter project-related times for the modules referenced in $modules

 * non-module-related times can be accessed by using "-" as modulename.

 * Below the form the project-related times are shown.

 *

 * Output will look like ([field] = formfield):

 * Todos: |        |                  |

 *        | todo 1 | [comment]        | [hours] [minutes]

 *        |        | note for todo1   | 1h 0m

 *        |        | another  todo1   | 0h 30m

 *        | todo 2 | [comment]        | [hours] [minutes]

 *        |        | note for todo2   | 1h 0m

 *        |        | another  todo2   | 0h 30m

 *

 * @param int    $user_ID

 * @param string $day iso-date-format

 * @param int    $project_id projektID

 * @param string $modules comma,separated list of modules

 * @param int    $field_nr some strange global stuff

 * @return string HTML-Rows

 */

function timecard_project_related_moduletimes($user_ID, $day, $project_id, $modules, $fld_nr,$indent=0,$include_IDs=array()) {

    global $arrproj1;

    $output = "";

    $indent+2;

    $modules = explode(",", $modules);



    foreach ($modules as $a_module) {

        // switch through all possible modules

        switch($a_module) {

            // non-module-times

            case "-":

                $output .= show_bookings1($day, $project_id);

                break;



                // todo-times

            case "todo":

                // get all applicable todos for this user and project

                $todos = timecard_get_project_todos($user_ID, $project_id,$include_IDs);

                if(sizeof($todos)>0)

                $output .= timecard_parse_template('project_time_modulehead', $project_id, array("MODULE_NAME" => __('Todo'),"M_TYPE"=> 'todo'),$indent);



                if ($arrproj1['todo'][$project_id]) {

                    foreach ($todos AS $a_todo) {

                        // begin each todo with a form

                        $values = array("MODULE"      => __('Todo'),

                        "DESCRIPTION" => $a_todo['remark'],

                        "M_ID"        => $a_todo['id'],

                        "FIELD_1"     => $fld_nr,

                        "FIELD_2"     => $fld_nr++,

                        "M_TYPE"      => 'todo');

                        $output .= timecard_parse_template('project_time_form', $project_id, $values,$indent);

                        unset($values);



                        // get the related times for this todo and show them

                        $times = timecard_get_module_bookings($user_ID, $a_module, $a_todo['id'], $day);

                        foreach ($times as $a_time) {

                            $values = array("DESCRIPTION" => $a_time['note'],

                            "HOURS"       => $a_time['h'],

                            "MINUTES"     => $a_time['m'],

                            "ID"          => $a_time['id']);

                            $output .= timecard_parse_template('project_time_row', $project_id, $values,$indent);

                            unset($values);

                        } // end while timeproj

                    }

                } // end while todos

                unset($todos, $a_todo, $times, $a_time);

                break;

                // helpdesk-times

            case "helpdesk":

                // get all applicable ticket for this user and project

                $tickets = timecard_get_project_tickets($user_ID, $project_id);

                if(sizeof($tickets)>0)

                $output .= timecard_parse_template('project_time_modulehead', $project_id, array("MODULE_NAME" => __('helpdesk'),"M_TYPE" => 'helpdesk'),$indent);



                if ($arrproj1['helpdesk'][$project_id]) {

                    foreach ($tickets AS $a_ticket) {

                        // begin each todo with a form

                        $values = array("MODULE"      => __('Helpdesk'),

                        "DESCRIPTION" => $a_ticket['remark'],

                        "M_ID"        => $a_ticket['id'],

                        "FIELD_1"     => $fld_nr,

                        "FIELD_2"     => $fld_nr++,

                        "M_TYPE"      => 'helpdesk');

                        $output .= timecard_parse_template('project_time_form', $project_id, $values,$indent);

                        unset($values);



                        // get the related times for this todo and show them

                        $times = timecard_get_module_bookings($user_ID, $a_module, $a_ticket['id'], $day);

                        foreach ($times as $a_time) {

                            $values = array("DESCRIPTION" => $a_time['note'],

                            "HOURS"       => $a_time['h'],

                            "MINUTES"     => $a_time['m'],

                            "ID"          => $a_time['id']);

                            $output .= timecard_parse_template('project_time_row', $project_id, $values,$indent);

                            unset($values);

                        } // end while timeproj



                    }

                } // end while todos

                unset($todos, $a_todo, $times, $a_time);

                break;

        } // end switch module

    }



    unset($a_module);

    return $output;

}



?>

