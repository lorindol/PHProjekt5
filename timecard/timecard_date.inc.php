<?php

// timecard_date.inc.php - PHProjekt Version 5.2
// copyright  ©  2000-2005 Albrecht Guenther  ag@phprojekt.com
// www.phprojekt.com
// Author: Albrecht Guenther
// $Id: timecard_date.inc.php,v 1.17.2.2 2007/04/19 12:09:38 nina Exp $

// check whether the lib has been included - authentication!
if (!defined('lib_included')) die('Please use index.php!');


if(isset($date)&&check_date($date)==true){
    global $date_format_object;
    // convert user date format to db/iso date format
    $date = $date_format_object->convert_user2db($date);

	$datebits=explode('-',$date);
	$year = check_4dy($datebits[0]);
	$month = check_2d($datebits[1]);
	$day= check_2d($datebits[2]);
	$date="$year-$month-$day";
}
elseif(isset($month) && isset($year) && checkdate(check_2d($month),"01",check_4dy($year))){
	$month=check_2d($month);
	$year=check_4dy($year);
	$day= get_day();
	$date="$year-$month-$day";
}
else if (isset($_SESSION['timecard_date'])) {
    $date = $_SESSION['timecard_date'];
    $year=substr($date,0,4);
    $month=substr($date,5,2);
}
else{
	$day= get_day();
	$year=get_year();
	$month=get_month();
	$date="$year-$month-$day";
}
$_SESSION['timecard_date'] = $date;


$set_timezone      = isset($settings['timezone'])    ? $settings['timezone']    : PHPR_TIMEZONE;
$today1 = date('Y-m-d', mktime(date('H')+$set_timezone, date('i'), date('s'), date('m'), date('d'), date('Y')));


function get_year(){

    $set_timezone      = isset($settings['timezone'])    ? $settings['timezone']    : PHPR_TIMEZONE;
    $year = date("Y", mktime(date("H")+$set_timezone,date("i"),date("s"),date("m"),date("d"),date("Y")));
    return $year;

}

function get_month(){

    $set_timezone      = isset($settings['timezone'])    ? $settings['timezone']    : PHPR_TIMEZONE;
    $month=date("m", mktime(date("H")+$set_timezone,date("i"),date("s"),date("m"),date("d"),date("Y")));
    return $month;
}
function get_day(){

    $set_timezone      = isset($settings['timezone'])    ? $settings['timezone']    : PHPR_TIMEZONE;
    $day=date("d", mktime(date("H")+$set_timezone,date("i"),date("s"),date("m"),date("d"),date("Y")));
    return $day;
}

function check_date($date){
  global $date_format_object;
  // convert user date format to db/iso date format
  $date = $date_format_object->convert_user2db($date);

  if (count($datebits=explode('-',$date))!=3) return false;
  $year = $datebits[0];
  if(strlen($year)!=2 AND strlen($year)!=4)return false; 
  $year=intval($year);
  $month = intval($datebits[1]);
  $day=  intval($datebits[2]);
  return checkdate($month,$day,$year);	
}
function get_weekday($date){
	$datebits = explode("-", $date);
	$week_day = date('w', mktime(1,1,1,$datebits[1],$datebits[2],$datebits [0]));
	return $week_day;
}
/**
 * Function checks wether year has the right length
 * If it has only length 2 it is assumed that the right year is 20xx
 * @param int $value
 * @return string
 */
function check_4dy($value) {
    return substr('20'.sprintf('%02d', (int) $value), -4);
}
function check_2d($value) {
    return substr(sprintf('%02d', $value), 0, 2);
}
?>
