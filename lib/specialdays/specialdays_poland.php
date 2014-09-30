<?php
/**
* holiday file for poland
*
* @package    calendar
* @module     main
* @author     Wilhelm Okarmus <okarmus@pk.edu.pl>, $Author: albrecht $
* @licence    GPL, see www.gnu.org/copyleft/gpl.html
* @copyright  2000-2006 Mayflower GmbH www.mayflower.de
* @version    $Id: specialdays_poland.php,v 1.3 2006/08/22 08:05:49 albrecht Exp $
*/
if (!defined('lib_included')) die('Please use index.php!');

class SpecialDays_Poland

{
	var $name;
	
	function SpecialDays_Poland() 
	{
		$this->name = __("poland");
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
		$wd_es58 = date("w", mktime(0,0,0,3,21+$es+58,$year));
		$data 	= array();
		$data[] = array("date"=>mktime(0,0,0,1,1,$year), 	
						"name"=>'Nowy Rok',
						"type"=>PHPR_SD_HOLIDAYS);
		$data[] = array("date"=>mktime(0,0,0,3,21+$es,$year), 
						"name"=>'Wielkanoc',
						"type"=>PHPR_SD_SPECIALDAYS);
		$data[] = array("date"=>mktime(0,0,0,3,21+$es+1,$year) , 
						"name"=>'¦migus Dyngus - Lany Ponidzia³ek',
						"type"=>PHPR_SD_HOLIDAYS);
		$data[] = array("date"=>mktime(0,0,0,3,21+$es+58+(($wd_es58<5)?(4-$wd_es58):(11-$wd_es58)),$year),
						"name"=>'Bo¿e Cia³o',
						"type"=>PHPR_SD_HOLIDAYS);
		$data[] = array("date"=>mktime(0,0,0,5,1,$year), 		
						"name"=>'¦wiêto Pracy',
						"type"=>PHPR_SD_HOLIDAYS);
                $data[] = array("date"=>mktime(0,0,0,5,3,$year),
                                                "name"=>'¦wiêto Konstytutcji 3 maja',
                                                "type"=>PHPR_SD_HOLIDAYS);
		$data[] = array("date"=>mktime(0,0,0,08,15,$year), 
						"name"=>'Bitwy Warszawskiej 1920r.',
						"type"=>PHPR_SD_HOLIDAYS);
		$data[] = array("date"=>mktime(0,0,0,11,1,$year), 
						"name"=>'Wszystkich ¦wiêtych',
						"type"=>PHPR_SD_HOLIDAYS);
		$data[] = array("date"=>mktime(0,0,0,11,11,$year), 
						"name"=>'Dzieñ Niepodleg³o¶ci',
						"type"=>PHPR_SD_HOLIDAYS);
		$data[] = array("date"=>mktime(0,0,0,12,25,$year), 
						"name"=>'Bo¿e Narodzenie',
						"type"=>PHPR_SD_HOLIDAYS);
		$data[] = array("date"=>mktime(0,0,0,12,26,$year), 
						"name"=>'¦w. Szczepana',
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
						"name"=>'¦wiêto Trzech Króli',
						"type"=>PHPR_SD_SPECIALDAYS);
		$data[] = array("date"=>mktime(0,0,0,1,21,$year), 
						"name"=>'Dzieñ Babci',
						"type"=>PHPR_SD_SPECIALDAYS);
		$data[] = array("date"=>mktime(0,0,0,1,22,$year), 
						"name"=>'Dzieñ Dziadka',
						"type"=>PHPR_SD_SPECIALDAYS);
		$data[] = array("date"=>mktime(0,0,0,2,14,$year), 
						"name"=>'¦w. Walentego',
						"type"=>PHPR_SD_SPECIALDAYS);
		$data[] = array("date"=>mktime(0,0,0,3,8,$year), 
						"name"=>'Dzieñ Kobiet',
						"type"=>PHPR_SD_SPECIALDAYS);
		$data[] = array("date"=>mktime(0,0,0,3,17,$year), 
						"name"=>'¦w. Patryka',
						"type"=>PHPR_SD_SPECIALDAYS);
		$data[] = array("date"=>mktime(0,0,0,3,21+$es-52,$year), 	
						"name"=>'T³usty Czwartek',
						"type"=>PHPR_SD_SPECIALDAYS);
		$data[] = array("date"=>mktime(0,0,0,3,21+$es-47,$year), 	
						"name"=>'¦ledziówka',
						"type"=>PHPR_SD_SPECIALDAYS);
		$data[] = array("date"=>mktime(0,0,0,3,21+$es-46,$year) , 
						"name"=>'¦roda Popielcowa',
						"type"=>PHPR_SD_SPECIALDAYS);
		$data[] = array("date"=>mktime(0,0,0,3,21+$es-7,$year),	
						"name"=>'Niedziela Palmowa',
						"type"=>PHPR_SD_SPECIALDAYS);
		$data[] = array("date"=>mktime(0,0,0,3,21+$es-3,$year), 		
						"name"=>'Wielki Czwartek',
						"type"=>PHPR_SD_SPECIALDAYS);
		$data[] = array("date"=>mktime(0,0,0,3,21+$es-2,$year), 	
						"name"=>'Wielki Pi±tek',
						"type"=>PHPR_SD_SPECIALDAYS);
		$data[] = array("date"=>mktime(0,0,0,4,1,$year), 
						"name"=>'Prima Aprilis',
						"type"=>PHPR_SD_SPECIALDAYS);
		$data[] = array("date"=>mktime(0,0,0,4,22,$year), 
						"name"=>'Dzieñ Ziemi',
						"type"=>PHPR_SD_SPECIALDAYS);
		$data[] = array("date"=>mktime(0,0,0,5,26,$year), 
						"name"=>'Dzieñ Matki',
						"type"=>PHPR_SD_SPECIALDAYS);
		$data[] = array("date"=>mktime(0,0,0,6,1,$year), 
						"name"=>'Dzieñ Dziecka',
						"type"=>PHPR_SD_SPECIALDAYS);
		$data[] = array("date"=>mktime(0,0,0,6,23,$year), 
						"name"=>'Dzieñ Ojca',
						"type"=>PHPR_SD_SPECIALDAYS);
		$data[] = array("date"=>mktime(0,0,0,12,6,$year), 
						"name"=>'¶w Miko³aj',
						"type"=>PHPR_SD_SPECIALDAYS);
		$data[] = array("date"=>mktime(0,0,0,12,24,$year), 
						"name"=>'Wigilia',
						"type"=>PHPR_SD_SPECIALDAYS);
		$data[] = array("date"=>mktime(0,0,0,12,31,$year), 
						"name"=>'Sylwester',
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

