<?php
/**
* holiday file for france
*
* @package    calendar
* @module     main
* @author     Alex Reil, David Soria Parra, $Author: albrecht $
* @licence    GPL, see www.gnu.org/copyleft/gpl.html
* @copyright  2000-2006 Mayflower GmbH www.mayflower.de
* @version    $Id: specialdays_france.php,v 1.4 2006/08/22 08:05:49 albrecht Exp $
*/
if (!defined('lib_included')) die('Please use index.php!');
 
class SpecialDays_France
{
	var $name;
	
	function SpecialDays_France() 
	{
		$this->name = __("france");
	}
	
	function calculate($year)
	{
		$data 	= array();
		
		$data = array_merge($data, $this->calculate_holidays($year));
		$data = array_merge($data, $this->calculate_school_holidays($year));
		$data = array_merge($data, $this->calculate_special_days($year));
		
		return $data;
	}
	
	function calculate_holidays($year)
	{
		$es = easter_days($year);
		$data 	= array();
		$data[] = array("date"=>mktime(0,0,0,1,1,$year), 	
						"name"=>'Jour de l&#146;ans',
						"type"=>PHPR_SD_HOLIDAYS);
		$data[] = array("date"=>mktime(0,0,0,3,21+$es+1,$year), 	
						"name"=>'Lundi de P&acirc;ques',
						"type"=>PHPR_SD_HOLIDAYS);
		$data[] = array("date"=>mktime(0,0,0,3,21+$es-2,$year), 	
						"name"=>'Vendredi Saint',
						"type"=>PHPR_SD_HOLIDAYS);
		$data[] = array("date"=>mktime(0,0,0,3,21+$es+39,$year) , 
						"name"=>'Ascension',
						"type"=>PHPR_SD_HOLIDAYS);
		$data[] = array("date"=>mktime(0,0,0,5,1,$year),	
						"name"=>'F&ecirc;te du travail',
						"type"=>PHPR_SD_HOLIDAYS);
		$data[] = array("date"=>mktime(0,0,0,5,8,$year), 		
						"name"=>'Armistice de la Seconde Guerre Mondiale',
						"type"=>PHPR_SD_HOLIDAYS);
		$data[] = array("date"=>mktime(0,0,0,7,14,$year), 
						"name"=>'F&ecirc;te nationale',
						"type"=>PHPR_SD_HOLIDAYS);
		$data[] = array("date"=>mktime(0,0,0,8,15,$year), 
						"name"=>'Assomption de la Vierge',
						"type"=>PHPR_SD_HOLIDAYS);
		$data[] = array("date"=>mktime(0,0,0,11,1,$year), 
						"name"=>'Toussant',
						"type"=>PHPR_SD_HOLIDAYS);
		$data[] = array("date"=>mktime(0,0,0,11,11,$year), 
						"name"=>'Armistice de la Premi&egrave;re Guerre Mondiale',
						"type"=>PHPR_SD_HOLIDAYS);
		$data[] = array("date"=>mktime(0,0,0,12,25,$year), 
						"name"=>'No&euml;l',
						"type"=>PHPR_SD_HOLIDAYS);
							
		return $data;
	}
		
	function calculate_special_days($year)
	{
		$es = easter_days($year);
		$data 	= array();
						
		return $data;
	}
	
	function calculate_school_holidays($year)
	{
		$es = easter_days($year);
		$data = array();
		return $data;
	}
	
}