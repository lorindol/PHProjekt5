<?php



// timecard_data.php - PHProjekt Version 5.2

// copyright  ©  2000-2005 Albrecht Guenther  ag@phprojekt.com

// www.phprojekt.com

// Author: Albrecht Guenther, $Author: nina $

// $Id: timecard_data.php,v 1.52.2.6 2007/04/19 12:09:37 nina Exp $



$module = 'timecard';

if(!defined('PATH_PRE')){

    define('PATH_PRE','../');

}

include_once(PATH_PRE.'lib/lib.inc.php');

include_once('./timecard_date.inc.php');



// check role

if (check_role("timecard") < 2) die("You are not allowed to do this!");



// check form token

check_csrftoken();



// some date variables

$die_ip = $_SERVER["REMOTE_ADDR"];

$now    = strtotime("now");

if(isset($deli))$action   = "delete";

// filter all speacial characters ou of the time string

if (!isset($time)){

    $set_timezone      = isset($settings['timezone'])    ? $settings['timezone']    : PHPR_TIMEZONE;
    $time  = date("Hi", mktime(date("H") + $set_timezone, date("i"), date("s"), date("m"), date("d"), date("Y")));

}

else{

    $time = ereg_replace("[.:,;/]", "", $time);

}



// activate stoppwatch for timecard

switch ($action){

	case "worktime_start":

	//check if time has the right format

	if(validate_time($time)==false){

		message_stack_in(__('Please check your date and time format! '),"timecard","error");

	}

	else{

		// check whether there is still one entry open

		get_entry_open($today1);





		if (get_entry_open($today1)==true){

			message_stack_in(__('Theres an error in your time sheet: '));

		}

		else $result = db_query("INSERT INTO ".DB_PREFIX."timecard

                                                 (        users,     datum,  anfang,  ip_address)

                                          VALUES (".(int)$user_ID.",'".strip_tags($today1)."',".(int)$time.",'".strip_tags($die_ip)."')") or db_die();

	}



	break;

	case "worktime_stop":

	if(validate_time($time)==false){

		message_stack_in(__('Please check your date and time format! '),"timecard","error");

	}

	elseif (get_entry_open($today1)==false){

		message_stack_in(__('Theres an error in your time sheet: '));

	}

	// close quicktimer

	else{

		clock_out();

		$result = db_query("UPDATE ".DB_PREFIX."timecard

                                   SET ende = ".(int)$time."

                                 WHERE datum = '".strip_tags($today1)."' AND

                                       users = ".(int)$user_ID." AND

                                       (anfang <> 0 or anfang is not NULL) and

                                       (ende = 0 or ende is NULL)") or db_die();

	}

	break;

	case "add":

	$datc = strtotime($date);

	$diff = $now-$datc;

	$diff = (floor($diff/86400));

	// first option: it is a new record - no ID

	// check wether the time is in the given range

	if ($diff > PHPR_TIMECARD_ADD) {

		$error = 1;

		message_stack_in(sprintf (__('You cannot add entries at this date. Since there have been %s days. You just can edit entries not older than %s days.'), $diff, PHPR_TIMECARD_ADD), "timecard", "error");

	}

	elseif(!validate_time($timestart)){

		$error = 1;

		message_stack_in(__('Please check the given time'), "timecard", "error");

	}

	elseif ($timestop <> '') {

		if(!validate_time($timestop)){

			$error = 1;

			message_stack_in(__('Please check the given time'), "timecard", "error");

		}

		elseif ($timestop <= $timestart){

			$error = 1;

			message_stack_in(__('Please check the given time'), "timecard", "error");

		}

		if(!$error)

		$result = db_query("INSERT INTO ".DB_PREFIX."timecard

                                            (        users,     datum,    anfang,     ende,        ip_address )

                                     VALUES (".(int)$user_ID.",'".strip_tags($date)."',".(int)$timestart.",".(int)$timestop.", '".$_SERVER["REMOTE_ADDR"]."')") or db_die();

	}

	else {

		if(!$error)

		$result = db_query("INSERT INTO ".DB_PREFIX."timecard

                                            (        users,                   datum,            anfang,                  ip_address )
                                     VALUES (".(int)$user_ID.",'".strip_tags($date)."',".(int)$timestart.", '".$_SERVER["REMOTE_ADDR"]."')") or db_die();
	}

	break;

	case "worktime_insert_after":

	$datc = strtotime($date);

	$diff = $now-$datc;

	$diff = (floor($diff/86400));

	// first option: it is a new record - no ID

	// check wether the time is in the given range

	if ($diff > PHPR_TIMECARD_ADD) {

		$error = 1;

		message_stack_in(sprintf (__('You cannot add entries at this date. Since there have been %s days. You just can edit entries not older than %s days.'), $diff, PHPR_TIMECARD_ADD), "timecard", "error");

	}

	foreach ($ende as $k =>$v) {

		if ($v) {

			$res = db_query("SELECT anfang

                               FROM ".DB_PREFIX."timecard

                              WHERE ID = ".(int)$k)or db_die();

			$er    = db_fetch_row($res);

			$anf = check_4d($er[0]);

			$ende  = check_4d($v);

			// check whether

			// 1. end time is bigger than start time

			if ($anf  >= $v) {

				message_stack_in(__('Please check the given time'), "timecard", "error");

				$error = 1;

			}

			elseif (!validate_time($v)) {

				message_stack_in(__('Please check the given time'), "timecard", "error");

				$error = 1;

			}



			if(!$error)

			$result = db_query("UPDATE ".DB_PREFIX."timecard

                                   SET ende   = ".(int)$v."

                                 WHERE ID = ".(int)$k)or db_die();

		}

	}



	// mail note to the chief

	if (PHPR_TIMECARD == '2'&& $date < $today1) {

		// group system?

		if ($user_group > 0) {

			// search for chef of the group - if ther is one!

			$result2 = db_query("SELECT email

                                   FROM ".DB_PREFIX."gruppen, ".DB_PREFIX."users

                                  WHERE ".DB_PREFIX."gruppen.ID = ".(int)$user_group."

                                    AND ".DB_PREFIX."gruppen.chef = ".DB_PREFIX."users.ID

                                    AND ".DB_PREFIX."users.status = 0

                                    AND ".DB_PREFIX."users.usertype = 0") or db_die();

			$row2 = db_fetch_row($result2);

		}

		// no chief given or no group system available? -> then take the first available chief

		else if ($row2[0] == '') {

			$result2 = db_query("SELECT email

                                   FROM ".DB_PREFIX."users

                                  WHERE acc LIKE '%c%'

                                    AND status = 0

                                    AND usertype = 0") or db_die();

			$row2 = db_fetch_row($result2);

		}

		// only send mail if a mail adress exists

		if ($row2[0] <> '') {

			if ($type == 'anfang') $type2 = __('Begin');

			if ($type == 'ende') $type2 = __('End');

			use_mail('1');

			$success = $mail->go($row2[0], __('Change in the timecard'),

			"$user_name  - $date - $type2: $time", $user_email);

		}

	}

	break;

	case "delete":

	// check permission

	foreach($del as $k =>$v) {

		if ($k < $maxa) {

			$result = db_query("DELETE FROM ".DB_PREFIX."tc_temp

                                      WHERE ID = ".(int)$v) or db_die();

		}

		else {

			$del1[] = $v;

		}

	}

	foreach ($del1 as $k =>$ID) {

		$result = db_query("SELECT users, datum

                              FROM ".DB_PREFIX."timecard

                             WHERE ID = ".(int)$ID) or db_die();

		$row = db_fetch_row($result);

		if ($row[0] == "") die("no entry found.");

		if ($user_ID <> $row[0]) die("you are not privileged for this!");



		$datc = strtotime($row[1]);

		$diff = $now-$datc;

		$diff = (floor($diff/86400));

		if (($diff >PHPR_TIMECARD_DELETE)) {

			message_stack_in(sprintf (__('You cannot delete entries at this date. Since there have been %s days. You just can edit entries not older than %s days.'), $diff, PHPR_TIMECARD_DELETE), "timecard", "error");

			$error = 1;

		}



		if (!$error) {

			// db actions - delete record in timecard ...

			$result2 = db_query("DELETE FROM ".DB_PREFIX."timecard

                                       WHERE ID = ".(int)$ID) or db_die();



			// ... and in timeproj as well, but only if there isn't any further entry

			// find out if there is another entry for this day in the timecard

			$result2 = db_query("SELECT ID

                                   FROM ".DB_PREFIX."timecard

                                  WHERE datum = '$row[1]'

                                    AND users = ".(int)$user_ID) or db_die();

			$row2 = db_fetch_row($result2);

			// if there isn't any further entry in the timecard, delete all relations to projects

			if (!$row2[0]) {

				$result = db_query("DELETE FROM ".DB_PREFIX."timeproj

                                          WHERE users = ".(int)$user_ID."

                                            AND datum LIKE '$row[1]'") or db_die();

			}

		}

	}

	break;

	// Projektbezug - assign to project

	case "proj":

	// delete separate

	if ($delsep) {

		if($del){

			$result = db_query("SELECT users, datum

                          FROM ".DB_PREFIX."timeproj

                         WHERE ID = ".(int)$del[0]) or db_die();

			$row = db_fetch_row($result);

			if ($row[0] <> $user_ID) die("You are not allowed to do this!");



			$datc = strtotime($row[1]);

			$diff = $now-$datc;

			$diff = (floor($diff/86400));

			if ($diff > PHPR_TIMECARD_ADD) {

				$error = 1;

				message_stack_in(sprintf (__('You cannot delete bookings at this date. Since there have been %s days. You just can edit bookings of entries not older than %s days.'), $diff, PHPR_TIMECARD_ADD), "timecard", "error");

			}

			// delete entry

			if (!$error) {

				foreach($del as $k =>$v) {

					$result = db_query("DELETE FROM ".DB_PREFIX."timeproj

                                      WHERE ID = ".(int)$v) or db_die();

				}

			}

		}

	}

	else{



		$datc = strtotime($date);

		$diff = $now-$datc;

		$diff = (floor($diff/86400));

		if ($diff > PHPR_TIMECARD_ADD) {

			$error = 1;

			message_stack_in(sprintf (__('You cannot add  bookings at this date. Since there have been %s days. You just can add bookings for entries not older than %s days.'), $diff, PHPR_TIMECARD_ADD), "timecard", "error");

		}

		// check dates

		if (!checkdate($month, $day, $year)){

			$error=1;

			message_stack_in(__('Theres an error in your time sheet: '),"timecard");

		}

		if (!$error) {

			// loop over all entries

			for ($i = 0; $i < count($nr); $i++) {

				unset($error);

				if ($h[$i] >0 && !validate_hours($h[$i])){

					$error=1;

					message_stack_in(__('Theres an error in your time sheet: ').$note[$i]." ".$h[$i]." h ".$m[$i]." m" ,"timecard");

				}

				else if ($m[$i]>0&&!validate_minutes($m[$i])){

					$error=1;

					message_stack_in(__('Theres an error in your time sheet: ').$note[$i]." ".$h[$i]." h ".$m[$i]." m","timecard");

				}

				if(!$error){

					$note[$i]=addslashes($note[$i]);

					// Is not used at the moment, but will be implemented later

					if ($timeproj_ID[$i]) {

						$result = db_query("UPDATE ".DB_PREFIX."timeproj

                                           SET h = ".(int)$h[$i].",

                                               m = ".(int)$m[$i].",

                                               note = '".strip_tags($note[$i])."'

                                         WHERE ID = ".(int)$timeproj_ID[$i]) or db_die();

					}

					else if ($h[$i] > 0 or $m[$i] > 0) {

						// add module/-id

						$query = "INSERT INTO ".DB_PREFIX."timeproj

                                    (        users,          projekt,    datum,          h,              m,        note,      module,                   module_id     )

                             VALUES (".(int)$user_ID.",".(int)$nr[$i].",'".strip_tags($datum)."',".(int)$h[$i].",".(int)$m[$i].",'".strip_tags($note[$i])."','".qss($module_name[$i])."',".(int)$module_id[$i].")";

                        $result = db_query($query) or db_die();

					}

				}

			}

		}

	}

	break;

	// clock the time for a project  - aka quicktimer

	case "clock_in":

	clock_in($projekt,$note);

	break;

	case "clock_out":

	clock_out();

	break;

	default:

}



include_once("./timecard_view.php");

/**

 * Function validates the format of the given time

 *

 * @param string $time

 * @return boolean

 */

function validate_time($time){

	$searchfor = "(^[0-2][0-9][0-6][0-9]$)";

	if (!ereg($searchfor,$time) or substr($time,0,2)>23 or substr($time,2,2)>59){

		return false;

	}

	return true;

}

function validate_hours($time){

	$searchfor = "(^[0-2][0-3]$)";

	$searchfor2="(^[0-1]?[0-9]?$)";

	if (!ereg($searchfor,$time)and !ereg($searchfor2,$time)){

		return false;

	}

	return true;

}

function validate_minutes($time){

	$searchfor = "(^[0-5]?[0-9]?$)";

	if (!ereg($searchfor,$time)){

		return false;

	}

	return true;

}



/**

 * Function which checks if there is all ready on open worktime entry on the given day

 * @param string $date

 * $return boolean

 */

function get_entry_open($date){

	global $user_ID;

	$result = db_query("SELECT ID, anfang

                          FROM ".DB_PREFIX."timecard

                         WHERE datum = '$date'

                           AND users = ".(int)$user_ID."

                           AND (anfang <> 0 OR anfang IS NOT NULL)

                           AND (ende = 0 OR ende IS NULL)") or db_die();

	$row    = db_fetch_row($result);

	if($row[0] > 0)return true;

	else return false;

}



function clock_in($projekt,$note) {

	global $today1, $dbTSnull, $user_ID;

	if ($projekt>0) {

		if (PHPR_PROJECTS) {

			$result = db_query("INSERT INTO ".DB_PREFIX."timeproj

                                        (users,                    projekt,   datum,    div1,        note    )

                                 VALUES (".(int)$user_ID.",".(int)$projekt.",'".strip_tags($today1)."','$dbTSnull', '".strip_tags($note)."')") or db_die();

		}

		header('Location: timecard.php?'.$sid);

	}

	else {

		message_stack_in(__('Please Select a Project'),"timecard","error");

		header('Location:timecard.php?mode=books&submode=days&action=clockin');

	}

}





function clock_out() {

	global $today1, $user_ID, $dbTSnull;

	//only if module "projects" is activated!

	if (PHPR_PROJECTS) {

		//calculate difference between clock_in and clock_out

		$result = db_query("SELECT ID, div1, h, m

                          FROM ".DB_PREFIX."timeproj

                         WHERE users = ".(int)$user_ID."

                           AND (div1 LIKE '".date("Ym")."%')") or db_die();

		$row = db_fetch_row($result);

		if ($row[0] > 0) {

			// calculate the sconds of the quicktimer span

			// calculate the sconds of the quicktimer span

			$seconds = $row[2]*3600 + $row[3]*60 +

			((substr($dbTSnull,8,2) - substr($row[1],8,2))*3600 +

			(substr($dbTSnull,10,2) - substr($row[1],10,2))*60 +

			(substr($dbTSnull,12,2) - substr($row[1],12,2)));

			// calculate the seconds of the existing value

			$h = floor($seconds/3600);

			$m = floor(($seconds - $h*3600)/60);

			$result = db_query("UPDATE ".DB_PREFIX."timeproj

                                       SET h    = ".(int)$h.",

                                           m    = ".(int)$m.",

                                           div1 = ''

                                     WHERE ID = ".(int)$row[0]) or db_die();

			message_stack_in(__('Project booking has been stopped'),"timecard","notice");

		}

	}

}

