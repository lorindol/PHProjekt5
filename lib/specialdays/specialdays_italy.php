<?php
/**
 * holiday file for Italy
 *
 * @package    calendar
 * @subpackage main
 * @author     Alex Reil, David Soria Parra, $Author: gustavo $
 * @licence    GPL, see www.gnu.org/copyleft/gpl.html
 * @copyright  2000-2006 Mayflower GmbH www.mayflower.de
 * @version    $Id: specialdays_italy.php,v 1.3 2007-05-31 08:12:02 gustavo Exp $
 */
if (!defined('lib_included')) die('Please use index.php!');

class SpecialDays_Italy

{
	var $name;

	function SpecialDays_Italy()
	{
		$this->name = __("Italy");
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
						"name"=>'Capodanno',
						"type"=>PHPR_SD_SPECIALDAYS);
		$data[] = array("date"=>mktime(0,0,0,1,6,$year),
						"name"=>'Epifania',
						"type"=>PHPR_SD_SPECIALDAYS);						
		$data[] = array("date"=>mktime(0,0,0,3,21+$es+1,$year),
						"name"=>'Lunedì di Pasqua',
						"type"=>PHPR_SD_SPECIALDAYS);						
		$data[] = array("date"=>mktime(0,0,0,4,25,$year),
						"name"=>'Liberazione Italia',
						"type"=>PHPR_SD_SPECIALDAYS);
		$data[] = array("date"=>mktime(0,0,0,5,1,$year),
						"name"=>'Festa del lavoro',
						"type"=>PHPR_SD_SPECIALDAYS);						
		$data[] = array("date"=>mktime(0,0,0,3,21+$es+50,$year),
						"name"=>'Lunedì di Pentecoste',
						"type"=>PHPR_SD_SPECIALDAYS);
		$data[] = array("date"=>mktime(0,0,0,6,2,$year),
						"name"=>'Festa della Repubblica Italia',
						"type"=>PHPR_SD_SPECIALDAYS);
		$data[] = array("date"=>mktime(0,0,0,8,15,$year),
						"name"=>'Ferragosto',
						"type"=>PHPR_SD_SPECIALDAYS);						
		$data[] = array("date"=>mktime(0,0,0,11,1,$year),
						"name"=>'Ognissanti',
						"type"=>PHPR_SD_SPECIALDAYS);
		$data[] = array("date"=>mktime(0,0,0,12,8,$year),
						"name"=>'Immacolata Concezione',
						"type"=>PHPR_SD_SPECIALDAYS);
		$data[] = array("date"=>mktime(0,0,0,12,25,$year),
						"name"=>'Natale',
						"type"=>PHPR_SD_SPECIALDAYS);						
		$data[] = array("date"=>mktime(0,0,0,12,26,$year),
						"name"=>'Santo Stefano',
						"type"=>PHPR_SD_SPECIALDAYS);
		$data[] = array("date"=>mktime(0,0,0,12,31,$year),
						"name"=>'San Silvestro',
						"type"=>PHPR_SD_SPECIALDAYS);						
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
