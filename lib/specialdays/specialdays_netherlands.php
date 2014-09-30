<?php
/**
 * holiday file for the Netherlands
 *
 * @package    calendar
 * @subpackage main
 * @author     Alex Reil, David Soria Parra, $Author: gustavo $
 * @licence    GPL, see www.gnu.org/copyleft/gpl.html
 * @copyright  2000-2006 Mayflower GmbH www.mayflower.de
 * @version    $Id: specialdays_netherlands.php,v 1.3 2007-05-31 08:12:02 gustavo Exp $
 */
if (!defined('lib_included')) die('Please use index.php!');

class SpecialDays_Netherlands

{
	var $name;

	function SpecialDays_Netherlands()
	{
		$this->name = __("Netherlands");
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
						"name"=>'Nieuwjaar',
						"type"=>PHPR_SD_HOLIDAYS);
		$data[] = array("date"=>mktime(0,0,0,4,30,$year),
						"name"=>'Koninginnedag',
						"type"=>PHPR_SD_HOLIDAYS);
		$data[] = array("date"=>mktime(0,0,0,5,4,$year) ,
						"name"=>'Dodenherdenking',
						"type"=>PHPR_SD_HOLIDAYS);
		$data[] = array("date"=>mktime(0,0,0,5,5,$year),
						"name"=>'Bevrijdingsdag',
						"type"=>PHPR_SD_HOLIDAYS);
		$data[] = array("date"=>mktime(0,0,0,3,21+$es+39,$year),
						"name"=>'Hemelvaartsdag',
						"type"=>PHPR_SD_HOLIDAYS);
		$data[] = array("date"=>mktime(0,0,0,3,21+$es+49,$year),
						"name"=>'Pinksteren',
						"type"=>PHPR_SD_HOLIDAYS);					
		$data[] = array("date"=>mktime(0,0,0,3,21+$es+50,$year),
						"name"=>'Pinksteren',
						"type"=>PHPR_SD_HOLIDAYS);
		$data[] = array("date"=>mktime(0,0,0,12,5,$year),
						"name"=>'Sinterklaasavond',
						"type"=>PHPR_SD_HOLIDAYS);
		$data[] = array("date"=>mktime(0,0,0,12,25,$year),
						"name"=>'Kerstmis',
						"type"=>PHPR_SD_HOLIDAYS);
		$data[] = array("date"=>mktime(0,0,0,12,26,$year),
						"name"=>'Kerstmis',
						"type"=>PHPR_SD_HOLIDAYS);
		$data[] = array("date"=>mktime(0,0,0,12,31,$year),
						"name"=>'Oudejaar',
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
