<?php
/**
* Basic class for holiday files
*
* This file stores common functions for reading and writing
* project-related times that are referenced from modules.
*
* @package    	lib
* @subpackage 	holidays
* @author     		David Soria-Parra, Alex Reil, $Author: gustavo $
* @licence     GPL, see www.gnu.org/copyleft/gpl.html
* @copyright  	2000-2006 Mayflower GmbH www.mayflower.de
* @version    		$Id: specialdays.php,v 1.17 2007-05-31 08:11:55 gustavo Exp $
*/
if (!defined('lib_included')) die('Please use index.php!');

/**
 * Type management
 */
define("PHPR_SD_HOLIDAYS",          1);
define("PHPR_SD_SPECIALDAYS",       2);
define("PHPR_SD_SCHOOL_HOLIDAYS",   4);
define("PHPR_SD_ALL",               7);

// check whether PATH_PRE doesn't redirect to some outer place
// avoid redirection to outer space
if (!preg_match("#^[./]*$#",PATH_PRE)) die('You are not allowed to do this');
require_once(PATH_PRE."lib/date_format.php");

/**
 * Handles holidays, school holidays and other important dates (called special days).
 * Gives you the possibility to merge the calculations of different classes,
 * implementing SpecialDays_DataClass.
 * @package lib
 */
class SpecialDays
{
    /**
     * Temp var used by get_masked_days_for_period. Timestamp
     *
     * @access private
     * @var int
     */
    var $_firstDateTimestamp;

    /**
     * Temp var used by get_masked_days_for_period. Timestamp
     *
     * @access private
     * @var int
     */
    var $_lastDateTimestamp;

    /**
     * Temp var used by get_masked_days_for_period. Level
     *
     * @access private
     * @var int
     */
    var $_mask;

    /**
     * Array of calculation objects (e.g.: SpecialDays_Germany)
     *
     * @var array
     * @access private
     */
    var $_instances = array();

    /**
     * User defined days. Each element of that array is an array with the following structure:
     *  [element] array("date" => [int (timestamp)], "name" => [string], "type"=>[int (type)]);
     *
     * @access private
     * @var array
     */
    var $_added = array();

    /**
     * The last calculated set generated by get_masked_days_for_period
     *
     * @access private
     * @var array
     */
    var $_cache = array();

    /**
     * Constructor. Each element of $countries holds the name of a class, extending SpecialDays_DataClass
     * and is able to calculate holidays, school holidays and important dates.
     * The constructor creates an instance of each class an stores the object in $this->_instances.
     * The classes are used by get_masked_days_for_period()
     *
     * @see get_masked_days_for_period
     * @param array 	$countries
     * @return SpecialDays
     * @access public
     */
    function SpecialDays($countries)
    {

        $this->_added               = array();
        $this->_instances           = array();
        $this->_lastDateTimestamp   = 0;
        $this->_firstDateTimestamp  = 0;

        $countries = (array) $countries;
        foreach($countries as $country) {
            // check for possible hacks
            $file = sprintf(PATH_PRE."/lib/specialdays/%s.php", preg_replace("[^a-zA-Z0-9]","",strtolower($country)));
            if(file_exists($file)) {
                include_once($file);
            if(class_exists($country)) {
                $this->_instances[] = new $country();
            }
        }
    }
    }

    /**
     * Adds an user defined day to the list of holidays. The days are stored separated from the main calculation and
     * are merged after finish calculation. Returns FALSE if fails.
     *
     * @param int			$imestamp
     * @param string		$name
     * @param int			$type
     * @return boolean
     * @access public
     */
    function add_day($timestamp, $name, $type)
    {
        $timestamp    = Date_Format::get_timestamp_at_midnight($timestamp);
        $this->_cache = NULL; // DROP CACHE

        if(in_array($type, array(PHPR_SD_HOLIDAYS, PHPR_SD_SCHOOL_HOLIDAYS, PHPR_SD_SPECIALDAYS))) {
            $this->_added[] = array("date"=>$timestamp, "name"=>$name, "type"=>$type);
            return true;
        }

        return false;
    }

    /**
     * Returnes true if the requested period is in cached period and
     *
     * @param int		$firstDate		- Timestamp
     * @param int		$lastDate			- Timestamp
     * @param int		$mask				-
     * @return boolean
     */
    function _isCacheable($firstDateTimestamp, $lastDateTimestamp, $mask)
    {
        if(    $firstDateTimestamp >= $this->_firstDateTimestamp
           &&  $lastDateTimestamp  <= $this->_lastDateTimestamp
           &&  ($mask & $this->_mask) == $mask
           && !is_null($this->_cache)) {
               return true;
           }

       return false;
    }

    /**
     * Gets the cache
     *
     * @param void
     * @return mixed
     */
    function _getCache()
    {
        return $this->_cache;
    }

    function _setCache($content)
    {
        return $this->_cache = $content;
    }

    /**
     * Gets all holidays, school holidays and special days from the DataClasses stored in $_instances.
     * Merges the calculated days with the user defined days and calls _filterArray to perform
     * type and period filtering.
     * Most methods of SpecialDays uses this method.
     * Each element of the returned array is structured:
     *    array("date" => [int (timestamp)], "name" => [string], "type"=>[int (type)]);
     * The returned array is sorted.
     *
     * @see _filter_array()
     * @param int	$firstDate	.- Timestamp
     * @param int	$lastDate		- Timestamp
     * @param int	$mask			-
     * @return array
     * @access public
     */
    function get_masked_days_for_period($firstDateTimestamp, $lastDateTimestamp, $mask = PHPR_SD_ALL)
    {
        if($firstDateTimestamp>$lastDateTimestamp) return array();
        if($firstDateTimestamp <= 0 || $lastDateTimestamp <= 0) return array();

        if($this->_isCacheable($firstDateTimestamp, $lastDateTimestamp, $mask)) {
           $days = $this->_getCache();
        } else {
            $days = array();

            for($i=date("Y", $firstDateTimestamp); $i<=date("Y", $lastDateTimestamp); $i++) {
                $days = array_merge($days, $this->_calculate($i));
            }

            if(count($this->_added) > 0) {
                $days = array_merge($days, $this->_added);
            }

            $this->_setCache($days);
        }

        $this->_firstDateTimestamp  = $firstDateTimestamp;
        $this->_lastDateTimestamp   = $lastDateTimestamp;
        $this->_mask                = $mask;

        $days = (array) array_filter($days, array($this, "_filter_array"));
        $days = $this->_convert_date_to_array_key($days);
        ksort($days);
        reset($days);

        return $days;
    }

    /**
     * Counts the amount of days, calculates by get_masked_days_for_period()
     *
     * @see get_masked_days_for_period()
     * @param int	$firstDate	- Timestamp
     * @param int	$lastDate		- Timestamp
     * @param int	$mask			-
     * @return int
     * @access public
     */
    function get_masked_days_count_for_period($firstDateTimestamp, $lastDateTimestamp, $mask = PHPR_SD_ALL)
    {
        return (int) count($this->get_masked_days_for_period($firstDateTimestamp, $lastDateTimestamp, $mask));
    }

    /**
     * Recieves holidays in a given period
     *
     * @see get_masked_days_for_period()
     * @param int	$firstDate	- Timestamp
     * @param int	$lastDate		- Timestamp
     * @return array
     * @access public
     */
    function get_holidays_for_period($firstDateTimestamp, $lastDateTimestamp)
    {
        return $this->get_masked_days_for_period($firstDateTimestamp, $lastDateTimestamp, PHPR_SD_HOLIDAYS);
    }

    /**
     * Calculates the amount of holidays in a given period.
     *
     * @see get_masked_days_count_for_period()
     * @param int	$firstDate	- Timestamp
     * @param int	$lastDate		- Timestamp
     * @return int
     * @access public
     */
    function get_holidays_count_for_period($firstDateTimestamp, $lastDateTimestamp)
    {
        return $this->get_masked_days_count_for_period($firstDateTimestamp, $lastDateTimestamp, PHPR_SD_HOLIDAYS);
    }

    /**
     * Recieves special days in a given period
     *
     * @see get_masked_days_for_period()
     * @param int	$firstDate	- Timestamp
     * @param int	$lastDate		- Timestamp
     * @return array
     * @access public
     */
    function get_special_days_for_period($firstDateTimestamp, $lastDateTimestamp)
    {
        return $this->get_masked_days_for_period($firstDateTimestamp, $lastDateTimestamp, PHPR_SD_SPECIALDAYS);
    }

    /**
     * Calculate the amount of special days in a given period
     *
     * @see get_masked_days_count_for_period()
     * @param int	$firstDate	- Timestamp
     * @param int	$lastDate		- Timestamp
     * @return int
     * @access public
     */
    function get_special_days_count_for_period($firstDateTimestamp, $lastDateTimestamp)
    {
        return $this->get_masked_days_count_for_period($firstDateTimestamp, $lastDateTimestamp, PHPR_SD_SPECIALDAYS);
    }

    /**
     * Returns true if given timestamp is part of the calculated array AND mask contains his type
     *
     * @param int		$timestamp	- Timescamp
     * @param int		$mask			-
     * @return boolean
     * @access public
     */
    function is_masked_day($timestamp, $mask = PHPR_SD_ALL)
    {
        if($timestamp<=0) return false;

        $timestamp = Date_Format::get_timestamp_at_midnight($timestamp);
        return ($this->get_masked_days_count_for_period($timestamp, $timestamp, $mask) > 0) ? true : false;
    }

    /**
     * Returns true if given timestamp is a holiday
     *
     * @see is_masked_day
     * @param int		$timestamp		-
     * @return boolean
     * @access public
     */
    function is_holiday($timestamp)
    {
        return $this->is_masked_day($timestamp, PHPR_SD_HOLIDAYS);
    }

    /**
     * Returns true if given timestamp is a(n) special/important day
     *
     * @see isMaskedDay
     * @param int		$timestamp		-
     * @return boolean
     * @access public
     */
    function is_special_day($timestamp)
    {
        return $this->is_masked_day($timestamp, PHPR_SD_SPECIALDAYS);
    }

    /**
     * Get masked days for a given month
     *
     * @see get_masked_days_for_period()
     * @param int		$month	-
     * @param int		$year		-
     * @access public
     * @return array
     */
    function get_masked_days_for_month($month, $year, $mask=PHPR_SD_ALL)
    {
        return $this->get_masked_days_for_period(
                                mktime(0,0,0,$month, 1, $year) ,mktime(0,0,0,$month+1, 0, $year), $mask);
    }

    /**
     * Get masked days for a given day
     *
     * @see get_masked_days_for_period()
     * @param int		$day		-
     * @param int		$month	-
     * @param int		$year		-
     * @access public
     * @return array
     */
    function get_masked_days_for_day($day, $month, $year, $mask=PHPR_SD_ALL)
    {
        return array_pop($this->get_masked_days_for_period(
                                mktime(0,0,0,$month, $day, $year) ,mktime(0,0,0,$month, $day, $year), $mask));
    }

    /**
     * Calculate holidays from instances
     *
     * @param int		$year		-
     * @return array
     * @access private
     */
    function _calculate($year)
    {
        $result = array();

        foreach($this->_instances as $instance) {
            $result = (array) array_merge($result, $instance->calculate($year));
        }
        return (array) $result;
    }

    /**
     * Replace array key with the date of the holiday
     *
     * @param array 	$array	-
     * @return array
     * @access private
     */
    function _convert_date_to_array_key($array)
    {
        $result = array();

        foreach($array as $key=>$value) {
              $result[$value['date']][] = $value;
        }

        return $result;
    }


    /**
     * Checks if the date of a holiday is between firstDateTimestamp and lastDateTimestamp
     * Also checks the mask.
     *
     * @param mixed		$element	-
     * @access private
     * @return boolean
     */
    function _filter_array($element)
    {
        if($element["date"] >= $this->_firstDateTimestamp
        && $element["date"] <= $this->_lastDateTimestamp
        && ($element["type"] & $this->_mask) == $element["type"])
            return true;

        return false;
    }

    /**
     * Returns the names of the instances
     *
     * @param void
     * @return array
     * @access public
     */
    function get_instances_names()
    {
        $names = array();

        foreach((array) $this->_instances as $instance) {
	  $names[strtolower(get_class($instance))] = $instance->name;
        }

        return $names;
    }

    /**
     * Gets all countries which are defined as a PHPR_SD_* constant
     *
     * @static static
     * @access public
     * @param void
     * @return array
     * @see get_instances_name()
     */
    function get_available_countries()
    {
        foreach(get_defined_constants() as $name => $value) {
            $file = sprintf(PATH_PRE."/lib/specialdays/%s.php", strtolower($value));
            if(strrpos($name, "PHPR_SD_") !== false && file_exists($file))
               $result[$name] = $value;
        }

        return $result;
    }
}
?>
