<?php
/**
 * holiday file for Estonia
 *
 * @package    calendar
 * @subpackage main
 * @author     Alex Reil, David Soria Parra, $Author: gustavo $
 * @licence    GPL, see www.gnu.org/copyleft/gpl.html
 * @copyright  2000-2006 Mayflower GmbH www.mayflower.de
 * @version    $Id: specialdays_estonia.php,v 1.3 2007-05-31 08:12:02 gustavo Exp $
 */
if (!defined('lib_included')) die('Please use index.php!');

class SpecialDays_Estonia

{
	var $name;

	function SpecialDays_Estonia()
	{
		$this->name = __("Estonia");
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
		$data[] = array("date"=>mktime(0,0,0,2,24,$year),
						"name"=>'Independence Day',
						"type"=>PHPR_SD_HOLIDAYS);
		$data[] = array("date"=>mktime(0,0,0,3,21+$es-2,$year),
						"name"=>'Good Friday',
						"type"=>PHPR_SD_HOLIDAYS);
		$data[] = array("date"=>mktime(0,0,0,3,21+$es,$year),
						"name"=>'Easter Sunday',
						"type"=>PHPR_SD_HOLIDAYS);
		$data[] = array("date"=>mktime(0,0,0,5,1,$year),
						"name"=>'May Day',
						"type"=>PHPR_SD_HOLIDAYS);
		$data[] = array("date"=>mktime(0,0,0,3,21+$es+49,$year),
						"name"=>'Pentecost',
						"type"=>PHPR_SD_HOLIDAYS);

		$data[] = array("date"=>mktime(0,0,0,6,23,$year),
						"name"=>'Victory Day',
						"type"=>PHPR_SD_HOLIDAYS);
		$data[] = array("date"=>mktime(0,0,0,3,24,$year),
						"name"=>'St. John\'s Day',
						"type"=>PHPR_SD_HOLIDAYS);
		$data[] = array("date"=>mktime(0,0,0,8,20,$year),
						"name"=>'Restoration',
						"type"=>PHPR_SD_HOLIDAYS);														

		$data[] = array("date"=>mktime(0,0,0,12,24,$year),
						"name"=>'Christmas Eve',
						"type"=>PHPR_SD_HOLIDAYS);
		$data[] = array("date"=>mktime(0,0,0,12,25,$year),
						"name"=>'Christmas Day',
						"type"=>PHPR_SD_HOLIDAYS);
		$data[] = array("date"=>mktime(0,0,0,12,26,$year),
						"name"=>'Second Day of Christmas',
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

		$data[] = array("date"=>mktime(0,0,0,3,21+$es+39,$year),
						"name"=>'Ascension Day',
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
