<?php
/**
* Holiday calculation for Spain
*
* @package    calendar
* @module     main
* @author     Manuel J. Paredes <php@onagenda.com>, David Soria Parra, $Author: albrecht $
* @licence    GPL, see www.gnu.org/copyleft/gpl.html
* @copyright  2000-2006 Mayflower GmbH www.mayflower.de
* @version    $Id: specialdays_spain.php,v 1.2 2006/08/22 08:05:49 albrecht Exp $
*/
if (!defined('lib_included')) die('Please use index.php!');

class SpecialDays_Spain

{
	var $name;
	
	function SpecialDays_Spain() 
	{
		$this->name = __("spain");
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
						"name"=>'Año nevo',
						"type"=>PHPR_SD_HOLIDAYS);
		$data[] = array("date"=>mktime(0,0,0,1,6,$year), 	
						"name"=>'Reyes Magos',
						"type"=>PHPR_SD_HOLIDAYS);
		$data[] = array("date"=>mktime(0,0,0,3,21+$es-2,$year) , 
						"name"=>'Viernes Santo',
						"type"=>PHPR_SD_HOLIDAYS);
		$data[] = array("date"=>mktime(0,0,0,5,1,$year),	
						"name"=>'Día del trabajo',
						"type"=>PHPR_SD_HOLIDAYS);
		$data[] = array("date"=>mktime(0,0,0,8,15,$year), 		
						"name"=>'Virgen de Agosto',
						"type"=>PHPR_SD_HOLIDAYS);
		$data[] = array("date"=>mktime(0,0,0,10,12,$year), 
						"name"=>'La Hispanidad',
						"type"=>PHPR_SD_HOLIDAYS);
		$data[] = array("date"=>mktime(0,0,0,11,1,$year), 
						"name"=>'Todos los Santos',
						"type"=>PHPR_SD_HOLIDAYS);
		$data[] = array("date"=>mktime(0,0,0,12,6,$year), 
						"name"=>'Constitución',
						"type"=>PHPR_SD_HOLIDAYS);
		$data[] = array("date"=>mktime(0,0,0,12,8,$year), 
						"name"=>'Virgen del Pilar',
						"type"=>PHPR_SD_HOLIDAYS);
        	$data[] = array("date"=>mktime(0,0,0,12,25,$year), 
						"name"=>'Navidad',
						"type"=>PHPR_SD_HOLIDAYS);							
		
		return  $this->prevent_sun($data);
	}
	
	/**
	 * Calculate special days (Holidays in some regions of Spain)
	 *
	 * @param integer $year
	 * @return array
	 * @access private
	 */
	function calculate_special_days($year)
	{
		$es = easter_days($year);
		$data 	= array();
		$data[] = array("date"=>mktime(0,0,0,2,28,$year), 	
						"name"=>'Andaluc&iacute;a',
						"type"=>PHPR_SD_SPECIALDAYS);
		$data[] = array("date"=>mktime(0,0,0,3,1,$year), 	
						"name"=>'Baleares',
						"type"=>PHPR_SD_SPECIALDAYS);
		$data[] = array("date"=>mktime(0,0,0,3,19,$year) , 
						"name"=>'San Jos&eacute;',
						"type"=>PHPR_SD_SPECIALDAYS);
		$data[] = array("date"=>mktime(0,0,0,3,21+$es-3,$year) , 
						"name"=>'Jueves Santo',
						"type"=>PHPR_SD_SPECIALDAYS);
		$data[] = array("date"=>mktime(0,0,0,3,21+$es+1,$year),	
						"name"=>'Lunes de Pascua',
						"type"=>PHPR_SD_SPECIALDAYS);
		$data[] = array("date"=>mktime(0,0,0,4,23,$year), 		
						"name"=>'Castilla-Le&oacute;n y Arag&oacute;n',
						"type"=>PHPR_SD_SPECIALDAYS);
		$data[] = array("date"=>mktime(0,0,0,5,1,$year), 
						"name"=>'Madrid',
						"type"=>PHPR_SD_SPECIALDAYS);
		$data[] = array("date"=>mktime(0,0,0,5,17,$year), 
						"name"=>'Galicia',
						"type"=>PHPR_SD_SPECIALDAYS);
		$data[] = array("date"=>mktime(0,0,0,5,30,$year), 
						"name"=>'Canarias',
						"type"=>PHPR_SD_SPECIALDAYS);
		$data[] = array("date"=>mktime(0,0,0,5,31,$year), 
						"name"=>'Castilla La Mancha',
						"type"=>PHPR_SD_SPECIALDAYS);
		$data[] = array("date"=>mktime(0,0,0,6,9,$year), 
						"name"=>'La Rioja y Murcia',
						"type"=>PHPR_SD_SPECIALDAYS);
		$data[] = array("date"=>mktime(0,0,0,6,24,$year), 
						"name"=>'San Juan',
						"type"=>PHPR_SD_SPECIALDAYS);						
	    	$data[] = array("date"=>mktime(0,0,0,7,25,$year), 
						"name"=>'Santiago Ap&oacute;stol',
						"type"=>PHPR_SD_SPECIALDAYS);
		$data[] = array("date"=>mktime(0,0,0,7,28,$year), 
						"name"=>'Cantabria',
						"type"=>PHPR_SD_SPECIALDAYS);
		$data[] = array("date"=>mktime(0,0,0,9,2,$year), 
						"name"=>'Ceuta',
						"type"=>PHPR_SD_SPECIALDAYS);
		$data[] = array("date"=>mktime(0,0,0,9,8,$year), 
						"name"=>'Extremadura y Asturias',
						"type"=>PHPR_SD_SPECIALDAYS);
		$data[] = array("date"=>mktime(0,0,0,9,11,$year), 
						"name"=>'Catalu&ntilde;a',
						"type"=>PHPR_SD_SPECIALDAYS);
		$data[] = array("date"=>mktime(0,0,0,9,15,$year), 
						"name"=>'Cantabria',
						"type"=>PHPR_SD_SPECIALDAYS);
		$data[] = array("date"=>mktime(0,0,0,10,9,$year), 
						"name"=>'Valencia',
						"type"=>PHPR_SD_SPECIALDAYS);
		$data[] = array("date"=>mktime(0,0,0,12,4,$year), 
						"name"=>'Navarra',
						"type"=>PHPR_SD_SPECIALDAYS);
		$data[] = array("date"=>mktime(0,0,0,12,26,$year), 
						"name"=>'Baleares y Catalu&ntilde;a',
						"type"=>PHPR_SD_SPECIALDAYS);												
		
		return  $this->prevent_sun($data);
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
    
	/**
	 * If holiday would be on sunday it is celebrated the next day (monday)
	 *
	 * @param array element
	 * @return array
	 * @access private
	 */
	function prevent_sun($data) {
	 for($i=0;$i<count($data);$i++)
	 {
	   $hday = getdate($data[$i]["date"]);
	   if( $hday["wday"] == 0 ) {
      	 	 $data[$i]["date"] +=86400; //Add a day 
      	 }
	 }
	 return $data;	
    }
    
   
}
