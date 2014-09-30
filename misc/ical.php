<?php
/**
 * This class Parse iCal standard.
 * @example
 * 	$ical = new ical();
 * 	$ical->parse($data);
 * 	$ical->get_all_data();
 */

include_once('Date.php');

class ical
{
	/**
	 * This array save iCalendar parse data
	 *
	 * @var array
	 */
	var $cal;

	/**
	 * Help variable save last key (multiline string)
	 *
	 * @var string
	 */
	var $last_key;

	/**
	 * Reference to the current active Element in $cal
	 *
	 * @var array (reference)
	 */
	var $pointer;

	/**
	 * Parse the data
	 *
	 * @param string $data
	 * @return array
	 */
	function parse($data)
	{
        // new empty array
		$this->cal = array();
		$this->pointer =& $this->cal;

		// Merge lines that were splitted
		$data = str_replace(array("\r", "\n "), '', $data);
		
		// read data
		$data = explode("\n", $data);
        for ($i = 0; $i < count($data); $i++) {
        	$text = $data[$i];

            // Fix new line
            $text = str_replace('\n', "\n", $text);
            
            // trim one line
			$text = trim($text);
			if (!empty($text)) {
				// get Key and Value VCALENDAR:Begin -> Key = VCALENDAR, Value = begin
				list($key, $value) = $this->return_key_value($text);
				
				if ($key == 'BEGIN') {
					if (in_array($value, array('VCALENDAR'))) {
						$this->pointer[$value] = array(
	                        '_parent' => &$this->pointer,
	                        '_type' => $value,
	                    );
			            $this->pointer =& $this->pointer[$value];
					} else { 
						if (!isset($this->pointer[$value])) {
							$this->pointer[$value] = array();
						}
						$idx = count($this->pointer[$value]);
			            $this->pointer[$value][$idx] = array(
	                        '_parent' => &$this->pointer,
	                        '_type' => $value,
	                    );
			            $this->pointer =& $this->pointer[$value][$idx];
					}
				} else if ($key == 'END') {
					$current = &$this->pointer;
					if (isset($current['_parent'])) {
						$this->pointer = &$this->pointer['_parent'];
						unset($current['_parent']);
					}
				} else {
					$this->add_to_array($key, $value);
				}
			}
		}
		$this->cal = $this->cal['VCALENDAR'];
		return $this->cal;
    }

	/**
     * Add to $this->ical array one value and key.
	 *
	 * @param string $key
	 * @param string $value
	 */
	function add_to_array($key, $value)
	{
        static $lastUser = '';
        
		if ($key == false) {
			$key = $this->last_key;
            $value = $this->pointer[$key].$value;
		}
		else if (($key == "DTSTAMP") or ($key == "LAST-MODIFIED") or ($key == "CREATED")) {
            list(, $value) = $this->ical_date($key, $value);
        }
        else if ($key == "RRULE" ) {
            $value = $this->ical_rrule($value);
        }
        else if (stristr($key,"DTSTART") or stristr($key,"DTEND") or stristr($key, "DUE") or stristr($key, "EXDATE")) {
            list($key, $value) = $this->ical_date($key, $value);
        }
        else if ($key == 'TZOFFSETFROM' or $key == 'TZOFFSETTO') {
        	$value = (int)substr($value,0,3) * 3600 + (int)substr($value,3);
        }
        else if ($key == "UID") {
            $value = $this->phprojekt_id($value);
        }
        else if ($key == 'DESCRIPTION') {
            $value = $this->pointer[$key].$value;
        }
        else if ($key == 'ATTENDEE') {
            $data = $this->ical_attende($value);
            if (isset($data['CN'])) {
                $lastUser = $data['CN'];
            } elseif (isset($data['http'])) {
            	$lastUser = 'http:'.$data['http'];
            } elseif (isset($data['mailto'])) {
            	$lastUser = 'mailto:'.$data['mailto'];
            }
            if (isset($this->pointer[$key])) {
                $value = $this->pointer[$key];
                if (isset($value[$lastUser])) {
                    foreach ($data as $k => $v) {
                        $value[$lastUser][$k] = $v;
                    }
                } else {
                    $value[$lastUser] = $data;
                }
            } else {
                $value = array($lastUser => $data);
            }
        }
        else if ($key == 'STATUS') {
        	switch($value) {
        		case 'TENTATIVE':
        			$value = 1;
        			break;
        		case 'CONFIRMED':
                    $value = 2;
                    break;
                case 'CANCELLED':
                    $value = 3;
                    break;
        	}
        }

		$this->pointer[$key] = $value;
		$this->last_key = $key;
    }

	/**
     * Parse text "XXXX:value text some with : "
     * and return array($key = "XXXX", $value="value"); 
	 *
	 * @param string $text
	 * @return array
	 */
	function return_key_value($text)
	{
        static $lastKey = '';

		preg_match("/([^:]+)[:]([\w\W]+)/", $text, $matches);
		
        // ATTENDEE
        if (strpos($text,"ATTENDEE") === 0) {
            $value = ereg_replace("ATTENDEE;",'',$text);
            $key = "ATTENDEE";
            $lastKey = $key;
            return array($key,$value);                  
        // Other like DESCRIPTION
        } else if (empty($matches)) {
            $key = $lastKey;
            return array($key,$text);
		} else  {
			$matches = array_splice($matches, 1, 2);
            $lastKey = $matches[0];
			return $matches;
		}
    }

	/**
	 * Parse RRULE  return array
	 *
	 * @param string $value
	 * @return array
	 */
	function ical_rrule($value)
	{
		$rrule = explode(';',$value);
		foreach ($rrule as $line) {
			$rcontent = explode('=', $line);
            if ($rcontent[0] == 'UNTIL') {
                list(,$rcontent[1]) = $this->ical_date($rcontent[0], $rcontent[1]);
            }
			$result[$rcontent[0]] = $rcontent[1];
		}
		return $result;
    }

   	/**
	 * Parse ATTENDE return array
	 *
	 * @param string $value
	 * @return array
	 */
    function ical_attende($value)
    {
    	$result = array();
    	$value = explode(':', $value, 2);
        $value[1] = explode(':', $value[1], 2);
        $result[$value[1][0]] = $value[1][1];
		$rrule = explode(';',$value[0]);
		foreach ($rrule as $line) {
			$rcontent = explode('=', $line);
            if (isset($rcontent[1])) {
    			$result[trim($rcontent[0])] = $rcontent[1];
            }
		}
		return $result;
    }

	/**
	 * Return unix date from iCal date format
	 *
	 * @param string $key
	 * @param string $value
	 * @return array
	 */
	function ical_date($key, $value)
	{
        // Check if have TimeZone
        $tmp = explode(";",$key);
        $key = $tmp[0];
        unset($tmp[0]);
        $attrs = array();
        foreach ($tmp as $v) {
             $v = explode('=', $v);
             $attrs[$v[0]] = $v[1];
        }
        
        if (isset($attrs['VALUE']) && $attrs['VALUE'] == 'DATE') {
            $key .= '-ALLDAY';
        }

        // Detect whether Timezone is UTC
        if ($value[strlen($value)-1] === 'Z') {
            $attrs['TZID'] = 'UTC';
        }

	    $year  = substr($value, 0, 4);
        $month = substr($value, 4, 2);
        $day   = substr($value, 6, 2);
        if (strpos($value, 'T') == 8) {
            $hour   = substr($value, 9, 2);
            $minute = substr($value, 11, 2);
            $second = substr($value, 12, 2);
        } else {
            $hour   = 0;
            $minute = 0;
            $second = 0;
        }
        $date = mktime($hour, $minute, $second, $month, $day, $year);
        
        // Can we use DateTime and DateTimeZone? PHP >= 5.1
		if (class_exists('DateTimeZone') && class_exists('DateTime')) {
			$date = date('Y-m-d\TH:i:s', $date);
            if (isset($attrs['TZID'])) {
                $tz = new DateTimeZone($attrs['TZID']);
                $date = new DateTime("$date", $tz);
                $date->setTimezone(timezone_open(date_default_timezone_get()));
            } else {
            	$date = new DateTime($date);
            }
            $value = (int)$date->format('U');
        // Can we use PEAR::Date?
		} else if (class_exists('Date')) { 
			$date = new Date($value);
			if (isset($attrs['TZID'])) {
				$date->setTZByID($attrs['TZID']);
				$date->convertTZ(Date_TimeZone::getDefault());
			}
			$value = $date->getTime();
		// Don't use Timezones
		} else {
			$value = $date;
		}
		return array($key, $value);
    }

    /**
     * Return the PHProjekt id or 0 for a new item
     *
     * @param string $value
     * @return ineger
     */
    function phprojekt_id($value)
    {
		preg_match("/(PHPROJEKT)[-]([\w\W]+)[-]([\d]+)/", $value, $matches);

        if (empty($matches)) {
            return $value;
        } else {
            return $matches[3];
        }
    }

	/**
     * Return eventlist array
     * (not sort eventlist array)
	 *
	 * @return array
	 */
	function get_event_list()
    {
        if (isset($this->cal['VEVENT'])) {
            $array = $this->cal['VEVENT'];
        } else {
            $array = array();
        } 
		return $array;
    }

	/**
     * Return todo array
     * (not sort todo array)
	 *
	 * @return array
	 */
	function get_todo_list()
	{
        if (isset($this->cal['VTODO'])) {
            $array = $this->cal['VTODO'];
        } else {
            $array = array();
        }
        return $array;
    }

	/**
	 * Return base calendar data
	 *
	 * @return array
	 */
	function get_calender_data()
    {
        if (isset($this->cal['VCALENDAR'])) {
            $array = $this->cal['VCALENDAR'];
        } else {
            $array = array();
        }
        return $array;
    }

	/**
	 * Return array with all data
	 *
	 * @return array
	 */
	function get_all_data()
	{
        if (isset($this->cal)) {
            $array = $this->cal;
        } else {
            $array = array();
        }
		return $array;
	}
	
}
?>
