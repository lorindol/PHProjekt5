<?php
/**
 * holiday file for Russia
 *
 *  To reduce the size of the array with holidays they are build only for the
 *  current month. The problem: Holidays depending on i.e. easterday can appear
 *  in different months. So some holiday must be defined for several months.
 *  So eastersunday can appear between 22nd of march and 25th of april.
 *
 * @package    calendar
 * @subpackage main
 * @author     Hermann Zheboldov, David Soria Parra, $Author: gustavo $
 * @licence    GPL, see www.gnu.org/copyleft/gpl.html
 * @copyright  2000-2006 Mayflower GmbH www.mayflower.de
 * @version    $Id: specialdays_russia.php,v 1.6 2007-05-31 08:12:03 gustavo Exp $
 */
// check whether the lib has been included - authentication!
if (!defined("lib_included")) die("Please use index.php!");

class SpecialDays_Russia

{
	var $name;

	function SpecialDays_Russia()
	{
		$this->name = __("Russia");
	}

	/**
	 * Calculate special days and return array with keys "date", "time" and "type"
	 *
	 * @param integer $year
	 * @return array
	 * @access public
	 */
	function calculate($year)
	{
		$data 	= array();

		$data = array_merge($data, $this->calculate_holidays($year));
		$data = array_merge($data, $this->calculate_school_holidays($year));
		$data = array_merge($data, $this->calculate_special_days($year));

		return $data;
	}

	/**
	 * Calculate holidays
	 *
	 * @param integer $year
	 * @return array
	 * @access private
	 */
	function calculate_holidays($year)
	{
		$es = easter_days($year);
		$data 	= array();
		$data[] = array("date"=>mktime(0,0,0,1,1,$year),
						"name"=>'New Year',
						"type"=>PHPR_SD_HOLIDAYS);
		$data[] = array("date"=>mktime(0,0,0,1,7,$year),
						"name"=>'Christmas',
						"type"=>PHPR_SD_HOLIDAYS);
		$data[] = array("date"=>mktime(0,0,0,3,23,$year),
						"name"=>'Defender of the Fatherland Day',
						"type"=>PHPR_SD_HOLIDAYS);
		$data[] = array("date"=>mktime(0,0,0,3,8,$year) ,
						"name"=>'International Women\'s Day',
						"type"=>PHPR_SD_HOLIDAYS);
		$data[] = array("date"=>mktime(0,0,0,5,1,$year),
						"name"=>'Spring and Labour Day',
						"type"=>PHPR_SD_HOLIDAYS);
		$data[] = array("date"=>mktime(0,0,0,5,9,$year),
						"name"=>'Victory Day',
						"type"=>PHPR_SD_HOLIDAYS);
		$data[] = array("date"=>mktime(0,0,0,6,12,$year),
						"name"=>'Russia Day',
						"type"=>PHPR_SD_HOLIDAYS);
		$data[] = array("date"=>mktime(0,0,0,11,4,$year),
						"name"=>'Unity Day',
						"type"=>PHPR_SD_HOLIDAYS);

		return $data;
	}

	/**
	 * Calculate special days
	 *
	 * @param integer $year
	 * @return array
	 * @access private
	 */
	function calculate_special_days($year)
	{
		$es = easter_days($year);
		$data 	= array();
		$data[] = array("date"=>mktime(0,0,0,1,25,$year),
						"name"=>'Tatiana Day',
						"type"=>PHPR_SD_SPECIALDAYS);
		$data[] = array("date"=>mktime(0,0,0,3,21+$es,$year),
						"name"=>'Easter',
						"type"=>PHPR_SD_SPECIALDAYS);						
		$data[] = array("date"=>mktime(0,0,0,4,12,$year),
						"name"=>'Cosmonautics Day',
						"type"=>PHPR_SD_SPECIALDAYS);
		$data[] = array("date"=>mktime(0,0,0,5,7,$year) ,
						"name"=>'Radio Day',
						"type"=>PHPR_SD_SPECIALDAYS);
		$data[] = array("date"=>mktime(0,0,0,7,7,$year),
						"name"=>'Ivan Kupala Day',
						"type"=>PHPR_SD_SPECIALDAYS);

		return $data;
	}

	/**
	 * Calculate school holidays
	 *
	 * @param integer $year
	 * @return array
	 * @access private
	 */
	function calculate_school_holidays($year)
	{
		$es = easter_days($year);
		$data = array();
		return $data;
	}
}
?>
