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
* @module     main
* @author     Hermann Zheboldov, David Soria Parra, $Author: albrecht $
* @licence    GPL, see www.gnu.org/copyleft/gpl.html
* @copyright  2000-2006 Mayflower GmbH www.mayflower.de
* @version    $Id: specialdays_russia.php,v 1.2 2006/08/22 08:05:49 albrecht Exp $
*/
if (!defined('lib_included')) die('Please use index.php!');

// check whether the lib has been included - authentication!
if (!defined("lib_included")) die("Please use index.php!");

class SpecialDays_Russia

{
	var $name;
	
	function SpecialDays_Russia() 
	{
		$this->name = __("russia");
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
						"name"=>'Новый Год',
						"type"=>PHPR_SD_HOLIDAYS);
		$data[] = array("date"=>mktime(0,0,0,1,2,$year), 	
						"name"=>'Новый Год',
						"type"=>PHPR_SD_HOLIDAYS);
		$data[] = array("date"=>mktime(0,0,0,1,3,$year), 	
						"name"=>'Новый Год',
						"type"=>PHPR_SD_HOLIDAYS);
		$data[] = array("date"=>mktime(0,0,0,1,4,$year), 	
						"name"=>'Новый Год',
						"type"=>PHPR_SD_HOLIDAYS);
		$data[] = array("date"=>mktime(0,0,0,1,5,$year), 	
						"name"=>'Новый Год',
						"type"=>PHPR_SD_HOLIDAYS);
		$data[] = array("date"=>mktime(0,0,0,1,7,$year), 	
						"name"=>'Рождество',
						"type"=>PHPR_SD_HOLIDAYS);
		$data[] = array("date"=>mktime(0,0,0,2,23,$year), 	
						"name"=>'День Защитника Отечества',
						"type"=>PHPR_SD_HOLIDAYS);
		$data[] = array("date"=>mktime(0,0,0,3,8,$year), 	
						"name"=>'Женский день',
						"type"=>PHPR_SD_HOLIDAYS);
		$data[] = array("date"=>mktime(0,0,0,5,1,$year), 	
						"name"=>'День Весны',
						"type"=>PHPR_SD_HOLIDAYS);
		$data[] = array("date"=>mktime(0,0,0,5,9,$year), 	
						"name"=>'День Победы',
						"type"=>PHPR_SD_HOLIDAYS);
		$data[] = array("date"=>mktime(0,0,0,6,12,$year), 	
						"name"=>'День Независимости',
						"type"=>PHPR_SD_HOLIDAYS);
		$data[] = array("date"=>mktime(0,0,0,11,4,$year), 	
						"name"=>'День Согласия',
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
		$data[] = array("date"=>mktime(0,0,0,4,1,$year), 	
						"name"=>'День Дурака',
						"type"=>PHPR_SD_SPECIALDAYS);
		$data[] = array("date"=>mktime(0,0,0,12,31,$year), 	
						"name"=>'День Подготовки к Новому Году',
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
