<?php
/**
 * holiday file for germany
 *
 * @package    calendar
 * @subpackage main
 * @author     Alex Reil, David Soria Parra, $Author: gustavo $
 * @licence    GPL, see www.gnu.org/copyleft/gpl.html
 * @copyright  2000-2006 Mayflower GmbH www.mayflower.de
 * @version    $Id: specialdays_germany.php,v 1.6 2007-05-31 08:12:02 gustavo Exp $
 */
if (!defined('lib_included')) die('Please use index.php!');
 
class SpecialDays_Germany

{
	var $name;
	
	function SpecialDays_Germany() 
	{
		$this->name = __("germany");
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
		$data[] = array("date"=>mktime(0,0,0,3,21+$es-2,$year), 	
						"name"=>'Karfreitag',
						"type"=>PHPR_SD_HOLIDAYS);
		$data[] = array("date"=>mktime(0,0,0,3,21+$es+1,$year) , 
						"name"=>'Ostermontag',
						"type"=>PHPR_SD_HOLIDAYS);
		$data[] = array("date"=>mktime(0,0,0,3,21+$es+39,$year),	
						"name"=>'Christi Himmelfahrt',
						"type"=>PHPR_SD_HOLIDAYS);
		$data[] = array("date"=>mktime(0,0,0,5,1,$year), 		
						"name"=>'Tag der Arbeit',
						"type"=>PHPR_SD_HOLIDAYS);
		$data[] = array("date"=>mktime(0,0,0,3,21+$es+50,$year), 
						"name"=>'Pfingstmontag',
						"type"=>PHPR_SD_HOLIDAYS);
		$data[] = array("date"=>mktime(0,0,0,10,3,$year), 
						"name"=>'Tag der deutschen Einheit',
						"type"=>PHPR_SD_HOLIDAYS);
		$data[] = array("date"=>mktime(0,0,0,12,25,$year), 
						"name"=>'1. Weihnachtsfeiertag',
						"type"=>PHPR_SD_HOLIDAYS);
		$data[] = array("date"=>mktime(0,0,0,12,26,$year), 
						"name"=>'2. Weihnachtsfeiertag',
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
		$data[] = array("date"=>mktime(0,0,0,3,21+$es-48,$year), 	
						"name"=>'Rosenmontag',
						"type"=>PHPR_SD_SPECIALDAYS);
		$data[] = array("date"=>mktime(0,0,0,3,21+$es-47,$year), 	
						"name"=>'Fastnacht',
						"type"=>PHPR_SD_SPECIALDAYS);
		$data[] = array("date"=>mktime(0,0,0,3,21+$es-46,$year) , 
						"name"=>'Aschermittwoch',
						"type"=>PHPR_SD_SPECIALDAYS);
		$data[] = array("date"=>mktime(0,0,0,3,21+$es-7,$year),	
						"name"=>'Palmsonntag',
						"type"=>PHPR_SD_SPECIALDAYS);
		$data[] = array("date"=>mktime(0,0,0,3,21+$es-3,$year), 		
						"name"=>'Gr&uuml;ndonnerstag',
						"type"=>PHPR_SD_SPECIALDAYS);
		$data[] = array("date"=>mktime(0,0,0,3,21+$es,$year), 
						"name"=>'Ostersonntag',
						"type"=>PHPR_SD_SPECIALDAYS);
		$data[] = array("date"=>mktime(0,0,0,3,21+$es+49,$year), 
						"name"=>'Pfingstsonntag',
						"type"=>PHPR_SD_SPECIALDAYS);
		$data[] = array("date"=>mktime(0,0,0,12,6,$year), 
						"name"=>'Nikolaus',
						"type"=>PHPR_SD_SPECIALDAYS);
		$data[] = array("date"=>mktime(0,0,0,12,31,$year), 
						"name"=>'Silvester',
						"type"=>PHPR_SD_SPECIALDAYS);
		$data[] = array("date"=>mktime(0,0,0,12,24,$year), 
						"name"=>'Heilig Abend',
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
