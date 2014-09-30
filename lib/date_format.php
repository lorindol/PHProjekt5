<?php

// date_format.php - PHProjekt Version 5.2
// copyright  ©  2000-2005 Albrecht Guenther  ag@phprojekt.com
// www.phprojekt.com
// Author: Giampaolo Pantò, $Author: albrecht $
// $Id: date_format.php,v 1.13.2.3 2007/02/12 11:00:34 albrecht Exp $

// check whether the lib has been included - authentication!
if (!defined("lib_included")) die("Please use index.php!");


/**
* Basic class to do the date conversion stuff (and something more).
*
* @package PHProjekt
* @author  Giampaolo Pantò, Munich-Germany <panto@mayflower.de>
*/
class Date_Format
{

    /**
    * The available date formats
    * @var array
    */
    var $_date_formats;

    /**
    * The date format stored in the db (iso)
    * @var string
    */
    var $_db_format;

    /**
    * The date format of the current user
    * @var string
    */
    var $_user_format;

    /**
    * The the separator of the user date format
    * @var string
    */
    var $_user_separator;

    /**
    * The default (fallback) date format
    * @var string
    */
    var $_default_format;

    /**
    * True if the conversion runs in an error, false otherwise
    * @var bool
    */
    var $_convert_error;


    /**
    * Constructor
    *
    * Define here more date formats:
    * 'short definition' => 'php date() format'
    *
    * @param string $user_format  the date format of the user (eg. "dd-mm-yyyy")
    *
    */
    function Date_Format($user_format='')
    {
        $this->_convert_error = false;
        $this->_db_format = 'Y-m-d';
        $this->_date_formats = array( 'dd.mm.yyyy' => 'd.m.Y'
                                     ,'mm/dd/yyyy' => 'm/d/Y'
                                     ,'yyyy-mm-dd' => 'Y-m-d'
                                    );
        // default format must be defined in $this->_date_formats !!!
        $this->_default_format = 'yyyy-mm-dd';

        if (array_key_exists($user_format, $this->_date_formats)) {
            $this->_user_format = $user_format;
        } else {
            $this->_user_format = $this->_default_format;
        }

        $separator = preg_replace("/[a-z0-9]/i", "", $this->_user_format);
        $this->_user_separator = $separator{0};
    }


    /**
    * Get the available date formats
    *
    * @param bool $as_values  true to get the array keys as values
    *
    * @return array  the available date formats
    */
    function get_date_formats($as_values=false)
    {
        if ($as_values) {
            return array_keys($this->_date_formats);
        } else {
            return $this->_date_formats;
        }
    }


    /**
    * Get the date format of the user
    *
    * @return string  the date format of the user (eg. "dd-mm-yyyy")
    */
    function get_user_format()
    {
        return $this->_user_format;
    }


    /**
    * Get the separator of the user date format
    *
    * @return string  the separator as a single char
    */
    function get_user_separator()
    {
        return $this->_user_separator;
    }


    /**
    * Check if the given date is a valid user format
    *
    * @param string $date  the user date which should be checked
    *
    * @return bool  true if valid, false otherwise
    */
    function is_user_date($date)
    {
        return $this->is_db_date($this->convert_user2db($date));
    }


    /**
    * Check if the given date is a valid db/iso format ("yyyy-mm-dd")
    *
    * @param string $date  the db/iso date which should be checked
    *
    * @return bool  true if valid, false otherwise
    */
    function is_db_date($date)
    {
        $d = (int) substr($date, -2);
        $m = (int) substr($date, 5, 2);
        $y = (int) substr($date, 0, 4);

        return checkdate($m, $d, $y);
    }


    /**
    * Check if the given timestamp belongs to a weekend
    *
    * @param string $timestamp  the timestamp which should be checked
    *
    * @return bool  true if this is the case, false otherwise
    */
    function is_weekend($timestamp)
    {
        if (date('w', $timestamp) == 0 || date('w', $timestamp) == 6) {
            return true;
        }
        return false;
    }


    /**
    * Converts the given date from the user format to the db/iso format ("yyyy-mm-dd")
    *
    * @param string $date  the date which should be converted into the db/iso format
    *
    * @return string  the converted date or $date on an error
    */
    function convert_user2db($date)
    {
        return $this->convert_date($date, $this->_date_formats[$this->_user_format], $this->_db_format);
    }

    /**
    * Converts the given date from the user format to the js Date format ("yyyy/mm/dd")
    *
    * @param string $date  the date which should be converted into the db/iso format
    *
    * @return string  the converted date or $date on an error
    */
    function convert_user2jsDate($date)
    {
        return $this->convert_date($date, $this->_date_formats[$this->_user_format], 'Y/m/d');
    }

    /**
    * Converts the given date from the db format to the js Date format ("yyyy/mm/dd")
    *
    * @param string $date  the date which should be converted into the db/iso format
    *
    * @return string  the converted date or $date on an error
    */
    function convert_db2jsDate($date)
    {
        return $this->convert_date($date, $this->_db_format, 'Y/m/d');
    }

    /**
    * Converts the given date from the db/iso format ("yyyy-mm-dd") to the user format
    *
    * @param string $date  the date which should be converted into the user format
    *
    * @return string  the converted date or $date on an error
    */
    function convert_db2user($date)
    {
        return $this->convert_date($date, $this->_db_format, $this->_date_formats[$this->_user_format]);
    }


    /**
    * Converts the given phprojekt "timestamp" ("yyyymmdd") to the user format
    *
    * @param string $date  the phprojekt "timestamp" which should be converted into the user format
    *
    * @return string  the converted date or $date on an error
    */
    function convert_dbdate2user($date)
    {
        if (strlen($date) != 8) {
            return $date;
        }
        $ret = substr($date,0,4).'-'.substr($date,4,2).'-'.substr($date,6,2);
        return $this->convert_db2user($ret);
    }


    /**
    * Converts the given phprojekt "timestamp" ("yyyymmddhhmmss") to the user format
    *
    * @param string $date  the phprojekt "timestamp" which should be converted into the user format
    *
    * @return string  the converted date or $date on an error
    */
    function convert_dbdatetime2user($date)
    {
        if (strlen($date) != 14) {
            return $date;
        }
        $ret = substr($date,0,4).'-'.substr($date,4,2).'-'.substr($date,6,2);
        $ret = $this->convert_db2user($ret).'&nbsp;-&nbsp;'.substr($date,8,2).':'.substr($date,10,2);
        return $ret;
    }


    /**
    * Common method to convert date formats
    *
    * @param string $input_date     the date which should be converted
    * @param string $input_format   the input date format
    * @param string $output_format  the output date format
    *
    * @return string  the converted date or $input_date on an error
    */
    function convert_date($input_date, $input_format, $output_format)
    {
        if (empty($input_date)) {
            $this->_convert_error = true;
            return $input_date;
        }

        preg_match("/^([\w]*)/i", $input_date, $regs);

        $sep = substr($input_date, strlen($regs[0]), 1);
        if (empty($sep)) {
            $this->_convert_error = true;
            return $input_date;
        }

        $label = explode($sep, $input_format);
        $value = explode($sep, $input_date);

        if (count($label) != count($value)) {
            $this->_convert_error = true;
            return $input_date;
        }

        $date = array();
        for ($ii=0; $ii<count($label); $ii++) {
            $date[$label[$ii]] = (int) $value[$ii];
        }

        if (in_array('Y', $label)) {
            $year = $date['Y'];
        } else if (in_array('y', $label)) {
            $year = $date['y'];
        } else {
            $this->_convert_error = true;
            return $input_date;
        }

        $this->_convert_error = false;
        $ret = date($output_format, mktime(0,0,0, $date['m'], $date['d'], $year));
        return $ret;
    }


    /**
    * Get the filled maxlength attribute for the html input tag
    *
    * @return string  the filled maxlength attribute
    */
    function get_maxlength_attribute()
    {
        return 'maxlength="'.strlen($this->get_user_format()).'"';
    }


    /**
    * Get the filled title attribute for html tags
    *
    * @param string $prepend  prepend this stuff to the attribute
    *
    * @return string  the filled title attribute
    */
    function get_title_attribute($prepend='')
    {
        if ($prepend != '') {
            $prepend .= ' - ';
        }
        return 'title="'.$prepend.__('Date format').': '.$this->get_user_format().'"';
    }


    /**
    * Get the javascript values for the functions which are needed to convert the date format
    *
    * @return array ($seq,$user_separator,$searchfor);
    */
    function get_javascript_convert_value_functions()
    {
        $seq = explode($this->_user_separator, strtolower($this->_date_formats[$this->_user_format]));
        $seq = array_flip($seq);
                
        $user_separator =($this->_user_separator=="/"?"\/":$this->_user_separator);

        $searchfor = preg_replace( array('/d/', '/m/', '/y/', '/['.$user_separator.']/'),
                                   array('\d',  '\d',  '\d',  '['.$user_separator.']'),
                                   $this->_user_format );

        return array ($seq, $user_separator, $searchfor);
    }

    /**
    * Get the javascript functions which are needed to convert the date format
    *
    * @return string  the javascript code
    */
    function get_javascript_convert_functions()
    {
        list($seq, $user_separator, $searchfor) = $this->get_javascript_convert_value_functions();

        $ret = '
<script type="text/javascript">
<!--
    var searchfor     = /^'.$searchfor.'$/;
    var seq_y         = '.$seq['y'].';
    var seq_m         = '.$seq['m'].';
    var seq_d         = '.$seq['d'].';
    var userSeparator = "'.$this->_user_separator.'";
//-->
</script>
';
        return $ret;
    }

    /**
     * Converts a PHProject date (YYYY-mm-dd) into an u*nix timestamp
     *
     * @param string $date
     * @return integer
     *
     * @access public
     * @author David Soria Parra
     */
    function get_timestamp_from_date($date, $inputFormat='Y-m-d')
    {
        if($inputFormat!='Y-m-d')
            $date = Date_Format::convert_date($date, $inputFormat, $this->_db_format);

        $pieces = explode("-", $date);
        return mktime(0,0,0, $pieces[1], $pieces[2], $pieces[0]);
    }

    /**
     * Converst a timestamp to a PHProject date (YYYY-mm-dd) used in most of the date column of the DB
     *
     * @param integer $timestamp
     * @return string
     *
     * @static static
     * @access public
     * @author David Soria Parra
     */
    function get_date_from_timestamp($timestamp, $outputFormat='Y-m-d')
    {
        $date = date("Y-m-d", $timestamp);
        if($inputFormat != 'Y-m-d')
            $date = Date_Format::convert_date($date, 'Y-m-d', $outputFormat);

        return $date;
    }

    /**
     * Returns an u*nix timestamp without the hours, minutes and seconds offset
     *
     * @param integer $timestamp
     * @return integer
     * @author David Soria Parra
     */
    function get_timestamp_at_midnight($timestamp)
    {
        return mktime(0,0,0, date("m",$timestamp), date("d", $timestamp), date("Y", $timestamp));
    }
}

?>
