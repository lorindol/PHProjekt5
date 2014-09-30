<?php
/**
 * Holiday calculation for switzerland / aargau
 *
 * @package    calendar
 * @subpackage main
 * @author     David Soria Parra <soria_parra@mayflower.de>
 * @licence    GPL, see www.gnu.org/copyleft/gpl.html
 * @copyright  2000-2006 Mayflower GmbH www.mayflower.de
 * @version    $Id: specialdays_switzerland_ag.php,v 1.6 2007-05-31 08:12:03 gustavo Exp $
 */
if (!defined('lib_included')) die('Please use index.php!');

class SpecialDays_Switzerland_AG

{
	var $name;
	
	function SpecialDays_Switzerland_AG() 
	{
		$this->name = __("aargau");
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
		$data[] = array("date"=>mktime(0,0,0,3,21+$es-2,$year), 	
						"name"=>'Karfreitag',
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
