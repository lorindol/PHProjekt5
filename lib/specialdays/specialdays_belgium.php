<?php
/**
* holiday file for belgium
*
* @package    calendar
* @module     main
* @author     Alex Reil, David Soria Parra, $Author: albrecht $
* @licence    GPL, see www.gnu.org/copyleft/gpl.html
* @copyright  2000-2006 Mayflower GmbH www.mayflower.de
* @version    $Id: specialdays_belgium.php,v 1.2 2006/08/22 08:05:49 albrecht Exp $
*/
if (!defined('lib_included')) die('Please use index.php!');
 
class SpecialDays_Belgium

{
	var $name;
	
	function SpecialDays_Belgium() 
	{
		$this->name = __("belgium");
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
		$data[] = array("date"=>mktime(0,0,0,3,21+$es+1,$year) , 
						"name"=>'Paasmaandag',
						"type"=>PHPR_SD_HOLIDAYS);
		$data[] = array("date"=>mktime(0,0,0,5,1,$year), 		
						"name"=>'Feest van de Arbeid',
						"type"=>PHPR_SD_HOLIDAYS);
	        $data[] = array("date"=>mktime(0,0,0,3,21+$es+39,$year),
                        "name"=>'Hemelvaartsdag',
                        "type"=>PHPR_SD_HOLIDAYS);
		$data[] = array("date"=>mktime(0,0,0,3,21+$es+50,$year), 
						"name"=>'Pinkstermaandag',
						"type"=>PHPR_SD_HOLIDAYS);
		$data[] = array("date"=>mktime(0,0,0,7,11,$year), 
						"name"=>'Feest van de Vlaamse Gemeenschap',
						"type"=>PHPR_SD_HOLIDAYS);
		$data[] = array("date"=>mktime(0,0,0,7,21,$year), 
						"name"=>'Nationale Feestdag',
						"type"=>PHPR_SD_HOLIDAYS);
		$data[] = array("date"=>mktime(0,0,0,8,15,$year), 
						"name"=>'Maria Hemelvaartsdag',
						"type"=>PHPR_SD_HOLIDAYS);
		$data[] = array("date"=>mktime(0,0,0,11,1,$year), 
						"name"=>'Allerheiligen',
						"type"=>PHPR_SD_HOLIDAYS);
		$data[] = array("date"=>mktime(0,0,0,11,11,$year), 
						"name"=>'Wapenstilstand',
						"type"=>PHPR_SD_HOLIDAYS);
		$data[] = array("date"=>mktime(0,0,0,12,25,$year), 
						"name"=>'Kerstmis',
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
		$data[] = array("date"=>mktime(0,0,0,1,6,$year), 
						"name"=>'Driekoningen',
						"type"=>PHPR_SD_SPECIALDAYS);
		$data[] = array("date"=>mktime(0,0,0,3,21+$es,$year), 
						"name"=>'Pasen',
						"type"=>PHPR_SD_SPECIALDAYS);
		$data[] = array("date"=>mktime(0,0,0,3,21+$es+49,$year), 
						"name"=>'Pinksteren',
						"type"=>PHPR_SD_SPECIALDAYS);
		$data[] = array("date"=>mktime(0,0,0,12,6,$year), 
						"name"=>'St Niklaas',
						"type"=>PHPR_SD_SPECIALDAYS);
		$data[] = array("date"=>mktime(0,0,0,12,31,$year), 
						"name"=>'Zingenskensdag',
						"type"=>PHPR_SD_SPECIALDAYS);
		$data[] = array("date"=>mktime(0,0,0,12,26,$year), 
						"name"=>'Tweede Kerstdag',
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