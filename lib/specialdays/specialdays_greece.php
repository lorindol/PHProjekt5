<?php
/**
 * holiday file for Greece
 *
 * @package    calendar
 * @subpackage main
 * @author     Alex Reil, David Soria Parra, $Author: gustavo $
 * @licence    GPL, see www.gnu.org/copyleft/gpl.html
 * @copyright  2000-2006 Mayflower GmbH www.mayflower.de
 * @version    $Id: specialdays_greece.php,v 1.3 2007-05-31 08:12:02 gustavo Exp $
 */
if (!defined('lib_included')) die('Please use index.php!');

class SpecialDays_Greece

{
	var $name;

	function SpecialDays_Greece()
	{
		$this->name = __("Greece");
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
		$data[] = array("date"=>mktime(0,0,0,1,6,$year),
						"name"=>'Epiphany',
						"type"=>PHPR_SD_HOLIDAYS);
		$data[] = array("date"=>mktime(0,0,0,1,30,$year),
						"name"=>'The Three Holy Hierarchs',
						"type"=>PHPR_SD_HOLIDAYS);
		$data[] = array("date"=>strtotime("monday", strtotime("-7 week",mktime(0,0,0,3,21+$es,$year))),
						"name"=>'Ash Monday',
						"type"=>PHPR_SD_HOLIDAYS);						
		$data[] = array("date"=>mktime(0,0,0,3,25,$year),
						"name"=>'25th of March',
						"type"=>PHPR_SD_HOLIDAYS);
		$data[] = array("date"=>mktime(0,0,0,3,21+$es,$year),
						"name"=>'Easter Sunday',
						"type"=>PHPR_SD_HOLIDAYS);
		$data[] = array("date"=>mktime(0,0,0,3,21+$es+1,$year),
						"name"=>'Easter Monday',
						"type"=>PHPR_SD_HOLIDAYS);
		$data[] = array("date"=>mktime(0,0,0,5,1,$year),
						"name"=>'Labor Day',
						"type"=>PHPR_SD_HOLIDAYS);
		$data[] = array("date"=>mktime(0,0,0,7,25,$year),
						"name"=>'The "Restoration of Democracy"',
						"type"=>PHPR_SD_HOLIDAYS);

		$data[] = array("date"=>mktime(0,0,0,8,15,$year),
						"name"=>'The Falling Asleep of the Virgin',
						"type"=>PHPR_SD_HOLIDAYS);
		$data[] = array("date"=>mktime(0,0,0,10,28,$year),
						"name"=>'The "Ochi day"',
						"type"=>PHPR_SD_HOLIDAYS);
		$data[] = array("date"=>mktime(0,0,0,11,17,$year),
						"name"=>'Polytechneio',
						"type"=>PHPR_SD_HOLIDAYS);

		$data[] = array("date"=>mktime(0,0,0,12,25,$year),
						"name"=>'Christmas',
						"type"=>PHPR_SD_HOLIDAYS);
		$data[] = array("date"=>mktime(0,0,0,12,26,$year),
						"name"=>'Boxing Day',
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
