<?php
/**
* holiday file for sweden
*
* @package    calendar
* @module     main
* @author     Tore Agblad <tore.agblad@euromail.se>, David Soria Parra, $Author: albrecht $
* @licence    GPL, see www.gnu.org/copyleft/gpl.html
* @copyright  2000-2006 Mayflower GmbH www.mayflower.de
* @version    $Id: specialdays_sweden.php,v 1.2 2006/08/22 08:05:49 albrecht Exp $
*/
if (!defined('lib_included')) die('Please use index.php!');

class SpecialDays_Sweden
{
	var $name;
	
	function SpecialDays_Sweden() 
	{
		$this->name = __("sweden");
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
	  $wd_11_01 = date('w', mktime(0,0,0,11,1,$year));
	  $es = easter_days($year);
	  $data 	= array();
	  
	  $data[] = array("name"=>'Ny&aring;rsdagen', 
			  "date"=>mktime(0,0,0,1,1,$year),
			  "type"=>PHPR_SD_HOLIDAYS);
	  $data[] = array("name"=>'Juldagen', 
			  "date"=>mktime(0,0,0,12,25,$year),
			  "type"=>PHPR_SD_HOLIDAYS);
	  $data[] = array("name"=>'Annandag Jul', 
			  "date"=>mktime(0,0,0,12,26,$year),
			  "type"=>PHPR_SD_HOLIDAYS);
	  $data[] = array("name"=>'Thanksgiving', 
			  "date"=>mktime(0,0,0,11,1+(($wd_11_01>4)?(32-$wd_11_01):(25-$wd_11_01)),$year),
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
	  $wd_05_01 = date('w', mktime(0,0,0,5,1,$year));

	  $wd_09_01 = date('w', mktime(0,0,0,9,1,$year));
	  
	  $es = easter_days($year);
	  $data 	= array();
	  
	  $data[] = array("name"=>'Trettondagsafton',
			  "date"=>mktime(0,0,0,1,6,$year),
			  "type"=>PHPR_SD_SPECIALDAYS);
	  $data[] = array("name"=>'Valentine&#146;s Day',
			  "date"=>mktime(0,0,0,2,14,$year),
			  "type"=>PHPR_SD_SPECIALDAYS);
	  $data[] = array("name"=>'L&aring;ngfredag', 
			  "date"=>mktime(0,0,0,3,21+$es-2,$year),
			  "type"=>PHPR_SD_SPECIALDAYS);
	  $data[] = array("name"=>'P&aring;skafton', 
			  "date"=>mktime(0,0,0,3,21+$es-1,$year),
			  "type"=>PHPR_SD_SPECIALDAYS);
	  $data[] = array("name"=>'P&aring;skdagen', 
			  "date"=>mktime(0,0,0,3,21+$es,$year),
			  "type"=>PHPR_SD_SPECIALDAYS);
	  $data[] = array("name"=>'Annandag P&aring;sk', 
			  "date"=>mktime(0,0,0,3,21+$es+1,$year),
			  "type"=>PHPR_SD_SPECIALDAYS);
	  $data[] = array("name"=>'Konungens f&ouml;delsedag', 
			  "date"=>mktime(0,0,0,4,30,$year),
			  "type"=>PHPR_SD_SPECIALDAYS);
	  $data[] = array("name"=>'F&ouml;rsta Maj', 
			  "date"=>mktime(0,0,0,5,1,$year),
			  "type"=>PHPR_SD_SPECIALDAYS);
          $data[] = array("name"=>'Mors Dag', 
			  "date"=>mktime(0,0,0,5,1+(($wd_05_01==0)?(21-$wd_05_01):(28-$wd_05_01)),$year),
			  "type"=>PHPR_SD_SPECIALDAYS);
	  $data[] = array("name"=>'Nationaldagen och Svenska Flaggans Dag', 
			  "date"=>mktime(0,0,0,6,6,$year),
			  "type"=>PHPR_SD_SPECIALDAYS);
	  $data[] = array("name"=>'Gustaf Adolfsdagen', 
			  "date"=>mktime(0,0,0,9,6,$year),
			  "type"=>PHPR_SD_SPECIALDAYS);
	  $data[] = array("name"=>'Fars Dag',
			  "date"=>mktime(0,0,0,9,1+(($wd_09_01==0)?(14-$wd_09_01):(21-$wd_09_01)),$year),
			  "type"=>PHPR_SD_SPECIALDAYS);
	  $data[] = array("name"=>'Halloween', 
			  "date"=>mktime(0,0,0,10,31,$year),
			  "type"=>PHPR_SD_SPECIALDAYS);
	  $data[] = array("name"=>'Drottningens f&ouml;delsedag', 
			  "date"=>mktime(0,0,0,12,23,$year),
			  "type"=>PHPR_SD_SPECIALDAYS);
	  $data[] = array("name"=>'Julafton', 
			  "date"=>mktime(0,0,0,12,24,$year),
			  "type"=>PHPR_SD_SPECIALDAYS);
	  $data[] = array("name"=>'Ny&aring;rsafton', 
			  "date"=>mktime(0,0,0,12,31,$year),
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
