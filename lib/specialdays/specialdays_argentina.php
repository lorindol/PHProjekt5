<?php
/**
 * holiday file for argentina
 *
 * @package    calendar
 * @subpackage main
 * @author     Gustavo Solt, $Author: gustavo $
 * @licence    GPL, see www.gnu.org/copyleft/gpl.html
 * @copyright  2000-2006 Mayflower GmbH www.mayflower.de
 * @version    $Id: specialdays_argentina.php,v 1.6 2007-05-31 08:12:02 gustavo Exp $
 */
if (!defined('lib_included')) die('Please use index.php!');


class SpecialDays_Argentina
{
	var $name;
	
	function SpecialDays_Argentina() 
	{
		$this->name = __("argentina");
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
						"name"=>'A�o Nuevo',
						"type"=>PHPR_SD_HOLIDAYS);
		$data[] = array("date"=>mktime(0,0,0,3,24,$year),
						"name"=>'D�a Nacional de la Memoria por la Verdad y la Justicia',
						"type"=>PHPR_SD_HOLIDAYS);
		$data[] = array("date"=>mktime(0,0,0,4,2,$year),
						"name"=>'D�a del Veterano y de los Ca�dos en la Guerra de Malvinas',
						"type"=>PHPR_SD_HOLIDAYS);
		$data[] = array("date"=>mktime(0,0,0,3,21+$es-3,$year) , 
						"name"=>'Jueves Santo',
						"type"=>PHPR_SD_HOLIDAYS);
		$data[] = array("date"=>mktime(0,0,0,3,21+$es-2,$year),
						"name"=>'Viernes Santo',
						"type"=>PHPR_SD_HOLIDAYS);
		$data[] = array("date"=>mktime(0,0,0,5,1,$year),	
						"name"=>'D�a del Trabajador',
						"type"=>PHPR_SD_HOLIDAYS);
		$data[] = array("date"=>mktime(0,0,0,5,25,$year),	
						"name"=>'Primer Gobierno Patrio',
						"type"=>PHPR_SD_HOLIDAYS);
        	$data[] = array("date"=>mktime(0,0,0,6,20,$year),
						"name"=>'D�a de la Bandera Nacional',
						"type"=>PHPR_SD_HOLIDAYS);
		$data[] = array("date"=>mktime(0,0,0,7,9,$year),	
						"name"=>'D�a de la Independencia',
						"type"=>PHPR_SD_HOLIDAYS);
		$data[] = array("date"=>mktime(0,0,0,8,17,$year), 		
						"name"=>'Aniversario de la Muerte del Gral. Jos� de San Mart�n',
						"type"=>PHPR_SD_HOLIDAYS);
		$data[] = array("date"=>mktime(0,0,0,10,12,$year), 
						"name"=>'D�a de la Raza',
						"type"=>PHPR_SD_HOLIDAYS);
		$data[] = array("date"=>mktime(0,0,0,12,8,$year), 
						"name"=>'D�a de la Inmaculada Concepci�n de Mar�a',
						"type"=>PHPR_SD_HOLIDAYS);
        	$data[] = array("date"=>mktime(0,0,0,12,25,$year), 
						"name"=>'Navidad',
						"type"=>PHPR_SD_HOLIDAYS);							
		
		return  $data;
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
		return  $data;
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
