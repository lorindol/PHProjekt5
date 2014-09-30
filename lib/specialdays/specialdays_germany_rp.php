<?php
/**
 * holiday file for Rhineland-Palatinate
 *
 * Does NOT include german holidays, but since it is a child of the german file
 * it also considers them
 *
 * @package    calendar
 * @subpackage main
 * @author     Alex Reil, David Soria Parra, $Author: gustavo $
 * @licence    GPL, see www.gnu.org/copyleft/gpl.html
 * @copyright  2000-2006 Mayflower GmbH www.mayflower.de
 * @version    $Id: specialdays_germany_rp.php,v 1.3 2007-05-31 08:12:02 gustavo Exp $
 */
if (!defined('lib_included')) die('Please use index.php!');


class SpecialDays_Germany_RP
{
	var $name;

	function SpecialDays_Germany_RP()
	{
		$this->name =  __("Rhineland-Palatinate");
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
		$data[] = array("date"=>mktime(0,0,0,3,21+$es+60,$year),
						"name"=>'Fronleichnam',
						"type"=>PHPR_SD_HOLIDAYS);	
		$data[] = array("date"=>mktime(0,0,0,11,1,$year),
						"name"=>'Allerheiligen',
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
