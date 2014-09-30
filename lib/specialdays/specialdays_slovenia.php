<?php
/**
 * holiday file for Slovenia
 *
 * @package    calendar
 * @subpackage main
 * @author     Alex Reil, David Soria Parra, $Author: gustavo $
 * @licence    GPL, see www.gnu.org/copyleft/gpl.html
 * @copyright  2000-2006 Mayflower GmbH www.mayflower.de
 * @version    $Id: specialdays_slovenia.php,v 1.3 2007-05-31 08:12:03 gustavo Exp $
 */
if (!defined('lib_included')) die('Please use index.php!');

class SpecialDays_Slovenia

{
	var $name;

	function SpecialDays_Slovenia()
	{
		$this->name = __("Slovenia");
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
		$data[] = array("date"=>mktime(0,0,0,2,8,$year),
						"name"=>'Prešeren Day',
						"type"=>PHPR_SD_HOLIDAYS);
		$data[] = array("date"=>mktime(0,0,0,3,21+$es,$year),
						"name"=>'Easter Sunday',
						"type"=>PHPR_SD_HOLIDAYS);
		$data[] = array("date"=>mktime(0,0,0,3,21+$es+1,$year),
						"name"=>'Easter Monday',
						"type"=>PHPR_SD_HOLIDAYS);						
		$data[] = array("date"=>mktime(0,0,0,4,27,$year),
						"name"=>'Day of Uprising Against Occupation',
						"type"=>PHPR_SD_HOLIDAYS);						
		$data[] = array("date"=>mktime(0,0,0,5,1,$year),
						"name"=>'Labour Day',
						"type"=>PHPR_SD_HOLIDAYS);
		$data[] = array("date"=>mktime(0,0,0,5,2,$year),
						"name"=>'Labour Day',
						"type"=>PHPR_SD_HOLIDAYS);
		$data[] = array("date"=>mktime(0,0,0,3,21+$es+49,$year),
						"name"=>'Pentecostal Sunday',
						"type"=>PHPR_SD_HOLIDAYS);

		$data[] = array("date"=>mktime(0,0,0,6,25,$year),
						"name"=>'Statehood Day',
						"type"=>PHPR_SD_HOLIDAYS);
		$data[] = array("date"=>mktime(0,0,0,8,15,$year),
						"name"=>'Assumption Day',
						"type"=>PHPR_SD_HOLIDAYS);
		$data[] = array("date"=>mktime(0,0,0,8,17,$year),
						"name"=>'Slovenians in Prekmurje Incorporated into the Mother Nation Day',
						"type"=>PHPR_SD_HOLIDAYS);

		$data[] = array("date"=>mktime(0,0,0,9,15,$year),
						"name"=>'Restoration of Primorska to the Motherland Day',
						"type"=>PHPR_SD_HOLIDAYS);
		$data[] = array("date"=>mktime(0,0,0,10,31,$year),
						"name"=>'Reformation Day',
						"type"=>PHPR_SD_HOLIDAYS);
		$data[] = array("date"=>mktime(0,0,0,11,1,$year),
						"name"=>'Remembrance Day',
						"type"=>PHPR_SD_HOLIDAYS);												

		$data[] = array("date"=>mktime(0,0,0,12,23,$year),
						"name"=>'Rudolf Maister Day',
						"type"=>PHPR_SD_HOLIDAYS);
		$data[] = array("date"=>mktime(0,0,0,12,25,$year),
						"name"=>'Christmas',
						"type"=>PHPR_SD_HOLIDAYS);
		$data[] = array("date"=>mktime(0,0,0,12,26,$year),
						"name"=>'Independence and Unity Day',
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

		$data[] = array("date"=>mktime(0,0,0,4,23,$year),
						"name"=>'St. George\'s Day',
						"type"=>PHPR_SD_SPECIALDAYS);
		$data[] = array("date"=>mktime(0,0,0,11,11,$year),
						"name"=>'St. Martin\'s day',
						"type"=>PHPR_SD_SPECIALDAYS);
		$data[] = array("date"=>mktime(0,0,0,12,6,$year),
						"name"=>'Saint Nicholas Day',
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
