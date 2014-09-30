<?php
/**
 * holiday file for England (United Kingdoms)
 *
 * @package    calendar
 * @subpackage main
 * @author     Alex Reil, David Soria Parra, $Author: gustavo $
 * @licence    GPL, see www.gnu.org/copyleft/gpl.html
 * @copyright  2000-2006 Mayflower GmbH www.mayflower.de
 * @version    $Id: specialdays_latvia.php,v 1.3 2007-05-31 08:12:02 gustavo Exp $
 */
if (!defined('lib_included')) die('Please use index.php!');


class SpecialDays_Latvia
{
	var $name;

	function SpecialDays_Latvia()
	{
		$this->name =  __("Latvia");
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
		$data = array();

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
		$data[] = array("date"=>mktime(0,0,0,3,21+$es-2,$year),
						"name"=>'Good Friday',
						"type"=>PHPR_SD_HOLIDAYS);
		$data[] = array("date"=>mktime(0,0,0,3,21+$es,$year),
						"name"=>'Easter Sunday',
						"type"=>PHPR_SD_HOLIDAYS);
		$data[] = array("date"=>mktime(0,0,0,3,21+$es+1,$year) ,
						"name"=>'Easter Monday',
						"type"=>PHPR_SD_HOLIDAYS);						
		$data[] = array("date"=>mktime(0,0,0,5,1,$year),
						"name"=>'Labour Day',
						"type"=>PHPR_SD_HOLIDAYS);
		$data[] = array("date"=>mktime(0,0,0,5,4,$year),
						"name"=>'Declaration of independence',
						"type"=>PHPR_SD_HOLIDAYS);
		$data[] = array("date"=>mktime(0,0,0,6,23,$year),
						"name"=>'Midsummer Eve',
						"type"=>PHPR_SD_HOLIDAYS);
		$data[] = array("date"=>mktime(0,0,0,6,24,$year),
						"name"=>'Midsummer',
						"type"=>PHPR_SD_HOLIDAYS);
		$data[] = array("date"=>mktime(0,0,0,11,18,$year),
						"name"=>'Proclamation of the Republic of Latvia',
						"type"=>PHPR_SD_HOLIDAYS);
		$data[] = array("date"=>mktime(0,0,0,12,25,$year),
						"name"=>'Christmas Day',
						"type"=>PHPR_SD_HOLIDAYS);
		$data[] = array("date"=>mktime(0,0,0,12,26,$year),
						"name"=>'2. Christmas Day',
						"type"=>PHPR_SD_HOLIDAYS);
		$data[] = array("date"=>mktime(0,0,0,12,31,$year),
						"name"=>'New Year\'s Eve',
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
