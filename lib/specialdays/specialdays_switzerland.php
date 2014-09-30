<?php

/**
 * Holiday calculation for switzerland 
 *
 * @author David Soria Parra <soria_parra@mayflower.de>
 * @since PHProjekt 5.1
 * @package PHProjekt
 * @subpackage Calendar
 * @since $Id: specialdays_switzerland.php,v 1.3 2006/12/18 14:24:04 nina Exp $
 */
if (!defined('lib_included')) die('Please use index.php!');
class SpecialDays_Switzerland

{
	var $name;
	
	function SpecialDays_Switzerland() 
	{
		$this->name = __("switzerland");
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
						"name"=>'Neujahrstag',
						"type"=>PHPR_SD_HOLIDAYS);
		$data[] = array("date"=>mktime(0,0,0,8,1,$year), 	
						"name"=>'Bundesfeier',
						"type"=>PHPR_SD_HOLIDAYS);
		$data[] = array("date"=>mktime(0,0,0,3,21+$es+39,$year),	
						"name"=>'Auffahrt',
						"type"=>PHPR_SD_HOLIDAYS);
		$data[] = array("date"=>mktime(0,0,0,12,25,$year), 
						"name"=>'Weihnachtstag',
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
		$data[] = array("date"=>mktime(0,0,0,1,2,$year), 	
						"name"=>'Berchtoldstag',
						"type"=>PHPR_SD_SPECIALDAYS);
		$data[] = array("date"=>mktime(0,0,0,1,6,$year), 	
						"name"=>'Heilige Drei K&ouml;nige',
						"type"=>PHPR_SD_SPECIALDAYS);
		$data[] = array("date"=>mktime(0,0,0,3,19,$year), 	
						"name"=>'Josefstag',
						"type"=>PHPR_SD_SPECIALDAYS);
		$data[] = array("date"=>mktime(0,0,0,3,21+$es-2,$year), 	
						"name"=>'Karfreitag',
						"type"=>PHPR_SD_SPECIALDAYS);
		$data[] = array("date"=>mktime(0,0,0,3,21+$es+1,$year) , 
						"name"=>'Ostermontag',
						"type"=>PHPR_SD_SPECIALDAYS);
		$data[] = array("date"=>mktime(0,0,0,5,1,$year) , 
						"name"=>'Tag der Arbeit',
						"type"=>PHPR_SD_SPECIALDAYS);
		$data[] = array("date"=>mktime(0,0,0,3,21+$es+50,$year), 
						"name"=>'Pfingstmontag',
						"type"=>PHPR_SD_SPECIALDAYS);
		$data[] = array("date"=>mktime(0,0,0,3,21+$es+60,$year), 
						"name"=>'Fronleichnam',
						"type"=>PHPR_SD_SPECIALDAYS);
		$data[] = array("date"=>mktime(0,0,0,8,15,$year), 
						"name"=>'Mari&auml; Himmelfahrt',
						"type"=>PHPR_SD_SPECIALDAYS);
		$data[] = array("date"=>mktime(0,0,0,11,1,$year), 
						"name"=>'Allerheiligen',
						"type"=>PHPR_SD_SPECIALDAYS);
		$data[] = array("date"=>mktime(0,0,0,12,8,$year), 
						"name"=>'Mari&auml; Empf&auml;ngnis',
						"type"=>PHPR_SD_SPECIALDAYS);
		$data[] = array("date"=>mktime(0,0,0,12,26,$year), 
						"name"=>'Stephanstag',
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
