<?php
/**
 * holiday file for United States
 *
 * @package    calendar
 * @subpackage main
 * @author     Alex Reil, David Soria Parra, $Author: gustavo $
 * @licence    GPL, see www.gnu.org/copyleft/gpl.html
 * @copyright  2000-2006 Mayflower GmbH www.mayflower.de
 * @version    $Id: specialdays_usa.php,v 1.3 2007-05-31 08:12:03 gustavo Exp $
 */
if (!defined('lib_included')) die('Please use index.php!');

class SpecialDays_USA

{
	var $name;

	function SpecialDays_USA()
	{
		$this->name = __("USA");
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
						"name"=>'New Year\'s Day',
						"type"=>PHPR_SD_HOLIDAYS);
		$data[] = array("date"=>strtotime("3 monday", mktime(0,0,0,1,1,$year)),
						"name"=>'Birthday of Martin Luther King, Jr.',
						"type"=>PHPR_SD_HOLIDAYS);
		$data[] = array("date"=>strtotime("3 monday", mktime(0,0,0,2,1,$year)),
						"name"=>'Washington\'s Birthday',
						"type"=>PHPR_SD_HOLIDAYS);
		$data[] = array("date"=>strtotime("last monday", mktime(0,0,0,5,1,$year)),
						"name"=>'Memorial Day',
						"type"=>PHPR_SD_HOLIDAYS);
		$data[] = array("date"=>mktime(0,0,0,6,4,$year),
						"name"=>'Independence Day',
						"type"=>PHPR_SD_HOLIDAYS);
		$data[] = array("date"=>strtotime("1 monday", mktime(0,0,0,9,1,$year)),
						"name"=>'Labor Day',
						"type"=>PHPR_SD_HOLIDAYS);
		$data[] = array("date"=>strtotime("2 monday", mktime(0,0,0,10,1,$year)),
						"name"=>'Columbus Day',
						"type"=>PHPR_SD_HOLIDAYS);
		$data[] = array("date"=>mktime(0,0,0,11,11,$year),
						"name"=>'Veterans Day',
						"type"=>PHPR_SD_HOLIDAYS);
		$data[] = array("date"=>strtotime("4 thursday", mktime(0,0,0,11,1,$year)),
						"name"=>'Thanksgiving Days',
						"type"=>PHPR_SD_HOLIDAYS);
		$data[] = array("date"=>mktime(0,0,0,12,25,$year),
						"name"=>'Christmas Day',
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
