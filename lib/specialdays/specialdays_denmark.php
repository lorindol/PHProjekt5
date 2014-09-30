<?php


/**
 * Holiday calculation for Denmark
 *
 * @author Liza Overgaard <liza@io.dk>
 * @since PHProjekt 5.1
 * @package PHProjekt
 * @subpackage Calendar
 * @since $Id: specialdays_denmark.php,v 1.1.2.1 2007/01/19 14:56:43 alexander Exp $
 */
 
// check whether the lib has been included - authentication!
if (!defined("lib_included")) die("Please use index.php!");
 
class SpecialDays_Denmark
{
	var $name;
	
	function SpecialDays_Denmark() 
	{
		$this->name = __("denmark");
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
	  
	  $data[] = array("name"=>'Nyt&aring;rsdag', 
			  "date"=>mktime(0,0,0,1,1,$year),
			  "type"=>PHPR_SD_HOLIDAYS);
	  $data[] = array("name"=>'Fastelavn', 
			  "date"=>mktime(0,0,0,3,21+$es-49,$year),
			  "type"=>PHPR_SD_HOLIDAYS);
	  $data[] = array("name"=>'Palmes&oslash;ndag', 
			  "date"=>mktime(0,0,0,3,21+$es-7,$year),
			  "type"=>PHPR_SD_HOLIDAYS);
	  $data[] = array("name"=>'Sk&aelig;rtorsdag', 
			  "date"=>mktime(0,0,0,3,21+$es-3,$year),
			  "type"=>PHPR_SD_HOLIDAYS);
	  $data[] = array("name"=>'Langfredag', 
			  "date"=>mktime(0,0,0,3,21+$es-2,$year),
			  "type"=>PHPR_SD_HOLIDAYS);
	  $data[] = array("name"=>'P&aring;ske', 
			  "date"=>mktime(0,0,0,3,21+$es,$year),
			  "type"=>PHPR_SD_HOLIDAYS);
	  $data[] = array("name"=>'2. p&aring;skedag', 
			  "date"=>mktime(0,0,0,3,21+$es+1,$year),
			  "type"=>PHPR_SD_HOLIDAYS);
	  $data[] = array("name"=>'Store Bededag', 
			  "date"=>mktime(0,0,0,3,21+$es+26,$year),
			  "type"=>PHPR_SD_HOLIDAYS);
	  $data[] = array("name"=>'Kristi Himmelfartsdag', 
			  "date"=>mktime(0,0,0,3,21+$es+39,$year),
			  "type"=>PHPR_SD_HOLIDAYS);
	  $data[] = array("name"=>'Pinse', 
			  "date"=>mktime(0,0,0,3,21+$es+49,$year),
			  "type"=>PHPR_SD_HOLIDAYS);
	  $data[] = array("name"=>'2. pinsedag', 
			  "date"=>mktime(0,0,0,3,21+$es+50,$year),
			  "type"=>PHPR_SD_HOLIDAYS);
	  $data[] = array("name"=>'Trinitatis', 
			  "date"=>mktime(0,0,0,3,21+$es+56,$year),
			  "type"=>PHPR_SD_HOLIDAYS);
	  $data[] = array("name"=>'1. juledag', 
			  "date"=>mktime(0,0,0,12,25,$year),
			  "type"=>PHPR_SD_HOLIDAYS);
	  $data[] = array("name"=>'2. juledag', 
			  "date"=>mktime(0,0,0,12,26,$year),
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
  
	  $es = easter_days($year);
	  $data 	= array();

	  $data[] = array("name"=>'Hellig 3-konger', 
			  "date"=>mktime(0,0,0,1,6,$year),
			  "type"=>PHPR_SD_SPECIALDAYS);
	  $data[] = array("name"=>'Kvindernes internationale kampdag', 
			  "date"=>mktime(0,0,0,3,8,$year),
			  "type"=>PHPR_SD_SPECIALDAYS);	  
	  $data[] = array("name"=>'1. maj', 
			  "date"=>mktime(0,0,0,5,1,$year),
			  "type"=>PHPR_SD_SPECIALDAYS);
	  $data[] = array("name"=>'Danmarks befrielse', 
			  "date"=>mktime(0,0,0,5,5,$year),
			  "type"=>PHPR_SD_SPECIALDAYS);
          $data[] = array("name"=>'Mors Dag', 
			  "date"=>mktime(0,0,0,5,1+(($wd_05_01==0)?(7-$wd_05_01):(14-$wd_05_01)),$year),
			  "type"=>PHPR_SD_SPECIALDAYS);
	  $data[] = array("name"=>'Grundlovsdag', 
			  "date"=>mktime(0,0,0,6,5,$year),
			  "type"=>PHPR_SD_SPECIALDAYS);
	  $data[] = array("name"=>'Fars dag', 
			  "date"=>mktime(0,0,0,6,5,$year),
			  "type"=>PHPR_SD_SPECIALDAYS);
	  $data[] = array("name"=>'Valdemarsdag', 
			  "date"=>mktime(0,0,0,6,15,$year),
			  "type"=>PHPR_SD_SPECIALDAYS);
	  $data[] = array("name"=>'Sommersolhverv', 
			  "date"=>mktime(0,0,0,6,21,$year),
			  "type"=>PHPR_SD_SPECIALDAYS);
	  $data[] = array("name"=>'Sankt Hans', 
			  "date"=>mktime(0,0,0,6,24,$year),
			  "type"=>PHPR_SD_SPECIALDAYS);
	  $data[] = array("name"=>'Mortensaften', 
			  "date"=>mktime(0,0,0,11,10,$year),
			  "type"=>PHPR_SD_SPECIALDAYS);
	  $data[] = array("name"=>'Luciadag', 
			  "date"=>mktime(0,0,0,12,13,$year),
			  "type"=>PHPR_SD_SPECIALDAYS);
	  $data[] = array("name"=>'Vintersolhverv', 
			  "date"=>mktime(0,0,0,12,21,$year),
			  "type"=>PHPR_SD_SPECIALDAYS);
	  $data[] = array("name"=>'Lille juleaften', 
			  "date"=>mktime(0,0,0,12,23,$year),
			  "type"=>PHPR_SD_SPECIALDAYS);
	  $data[] = array("name"=>'Juleaften', 
			  "date"=>mktime(0,0,0,12,24,$year),
			  "type"=>PHPR_SD_SPECIALDAYS);
	  $data[] = array("name"=>'Nyt&aring;rsaften', 
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

