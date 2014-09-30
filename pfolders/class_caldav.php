<?php
require_once(WEBDAV_PATH.'class_view.php');
require_once(WEBDAV_PATH.'caldav/vevent.php');
require_once(WEBDAV_PATH.'caldav/vtodo.php');
require_once(WEBDAV_PATH.'caldav/vhelpdesk.php');
require_once(PHPROJEKT_PATH.'calendar/calendar.inc.php');
require_once(PHPROJEKT_PATH.'misc/export_ical.php');
require_once(PHPROJEKT_PATH.'misc/ical.php');

/**
 * Implementation of the PHProject CALDAV
 * 
 * @author Gustavo Solt <solt@mayflower.de>
 * @version $Id: class_caldav.php
 */
class caldav extends view
{
    function calendariew()
    {
        parent::view();
	}
	
	/**
	 * Check wether the PHProjekt calendar module is enabled
	 * @ return bool
	 */
    function checkAvailability()
    {
        global $adressen; // PHProjekt configuration option
        return $adressen > 0;
	}

    function PROPFIND($options, &$files)
    {
        foreach ($options['props'] as $tmp => $data) {
            switch ($data['name']) {
                case 'resourcetype':
		            $files['files'] = array();
                    $files['files'][] = $this->fileinfo('/');
                    return true;
                    break;
            }
        }
        die();
	}

    function REPORT($options, &$files)
    {
    	$method = webdav::getMethodName($options->name);
    	if (method_exists($this, $method)) {
    		return call_user_func_array(array($this, $method), array($options, &$files));
    	}
    	return true;
    }
    
    function POST($options, &$response) {
    	$ical = new ical();
        $cal = $ical->parse($options['data']);
        $return = false;
        if (isset($cal['VFREEBUSY'])) {
        	$return = $this->VFREEBUSY($cal['VFREEBUSY'], $response) || $return;
        }
        return $return;
    }

    function PUT(&$options)
    {
        $event    = new caldav_vevent($this);
        $todo     = new caldav_vtodo($this);
        $helpdesk = new caldav_vhelpdesk($this);
        
        $data = '';
        $file = $options['stream'];

        while (!feof($file)) {
            $data .= fgets($file, 4096);
        }
        $ical = new ical();
        $ical->parse($data);
a($ical);        
        $data_ID = $this->get_phprojekt_id($options['path']);
a($data_ID);               
        // EVENTS
        foreach ($ical->get_event_list() as $tmp => $data) {
            $code = $event->update($data_ID, $data, $options);
        }

        // TODOS && HELPDESK
        foreach ($ical->get_todo_list() as $tmp => $data) {
            if ($data['CATEGORIES'] == 'Helpdesk') {
                $code = $helpdesk->update($data_ID, $data, $options);
            } else {
                $code = $todo->update($data_ID, $data, $options);
            }
        }

        return $code;
    }

    /**
     * Delete an element
     *
     * @param array $options    Options to be applied
     * @return String           Stautuscode to be send to client 
     */
    function DELETE($options)
    {
        $data_ID = $this->get_phprojekt_id($options['path']);

        if ($data_ID['type'] == 'event') {
            $event = new caldav_vevent($this);
            $event->delete($data_ID);
        } else if ($data_ID['type'] == 'todo') {
            $todo = new caldav_vtodo($this);
            $todo->delete($data_ID);
        } else {
            $helpdesk = new caldav_vhelpdesk($this);
            $helpdesk->delete($data_ID);
        }

        return "204 Deleted";
    }

    function fileinfo($uri)
    {
        $retval = array();
        $path   = explode('/', $uri);
        $fname  = end($path);

        $retval['path']     = htmlspecialchars(str_replace('%2F', '/', rawurlencode($uri)));
        $retval['props'][]  = $this->caller->mkprop('getetag', $this->caller->getetag($uri));
        $retval['props'][]  = $this->caller->mkprop('getcontenttype', 'httpd/unix-directory');
        $retval['props'][]  = $this->caller->mkprop('resourcetype', 'collection/><calendar xmlns="urn:ietf:params:xml:ns:caldav"');
        return $retval;
    }

    /**
     * build the skeletton of an element holding a fileinforeport
     *
     * @param xml_object $options   options to be applied
     * @return array                property as array
     */
    function fileinforeport($options)
    {
        return array(
            'path' => $this->caller->absoluteUri($options->path),
        );
    }

	/**
     * Return databse time from ical date time fomrat
     * (YYYYMMDD[T]HHMMSS[Z] or YYYYMMDD[T]HHMMSS)
	 *
     * The second parameter set the orientation of the time zone
	 * @param string $ical_date
	 * @return long integer
	 */
	function ical_date_to_db($ical_date)
	{
		// Timestamp ?
		if (is_int($ical_date)) {
			return date("Y-m-d", $ical_date);
        // ISO ?
		} else if (strstr($ical_date, 'T')) {
            list($date,$time) = split('T',$ical_date);
            $year = (int) substr($date,0,4);
            $month = (int) substr($date,4,2);
            $day = (int) substr($date,6,2);

            $hour    = (int) substr($time,0,2);
            $minute  = (int) substr($time,2,2);

            $diff = date("Z");

            return date("Y-m-d", mktime($hour, $minute, $diff, $month, $day, $year));
        } else {
            return $ical_date;
        }
    }

    /**
     * Search for a ID
     * Can be a new item, so the ID and ical_ID is null
     * Can be a PHProjekt ID
     * Can be a ical_ID (id generated by the CALDAV client
     *
     * Where the ID is a ical_ID, we must chech if is a VTODO or a VEVENT
     *
     * @param string $path The path of the item to check
     * @return array (type new/todo/event)
     *               (ID PHProjekt ID)
     *               (ical_ID Caldav client ID)
     */
    function get_phprojekt_id($path) {
        if ($path == '/null' || $path == '/') {
            return array('type'     => 'new',
                         'ID'       => null,
                         'ical_ID'  => null);
        } else {
            preg_match("/(PHPROJEKT)[-]([\w\W]+)[-]([\d]+)/", $path, $matches);
            if (!empty($matches)) {
                return array('type'     => strtolower($matches[2]),
                             'ID'       => $matches[3],
                             'ical_ID'  => '');
            } else {
                $ical_ID = str_replace('.ics', '', basename($path));

                $item = new caldav_vevent($this);
                $query = $item->get_item(null, $ical_ID);
                $row   = $query->get_next_row();
                if (isset($row[0])) {
                    $type = 'event';
                    $event_ID = $row[0];
                } else {
                	$item = new caldav_vtodo($this);
                    $query = $item->get_item(null, $ical_ID);
                    $row   = $query->get_next_row();
                    if (isset($row[0])) {
                        $type = 'todo';
                        $event_ID = $row[0];
                    } else {
	                    $item = new caldav_vhelpdesk($this);
	                    $query = $item->get_item(null, $ical_ID);
	                    $row   = $query->get_next_row();
	                    if (isset($row[0])) {
	                        $type = 'helpdesk';
	                        $event_ID = $row[0];
	                    } else {
	                    	return $this->get_phprojekt_id('/');
	                    }
                    }
                }
                return array('type'     => $type,
                             'ID'       => $event_ID,
                             'ical_ID'  => $ical_ID);
            }
        }
    }
    
    /**
     * To be called by REPORT
     * Get the Free-Busy-Time of a user 
     *
     * @param array $options    Options to be applied
     * @param array $response   reference to the variable that holds the results
     * @return boolean          TRUE or FALSE
     */
    function VFREEBUSY($options, &$response) {
    	$result = false;
    	foreach ($options as $request) {
	        $attendee = $this->attendee_to_id($request['ATTENDEE']);
	    	if (empty($attendee)) continue;
	    	if (!$request['DTSTART'] || !$request['DTEND']) continue;
	    	$cur = array(
	    	   (int) date('Y', $request['DTSTART']),
	    	   (int) date('m', $request['DTSTART']),
	    	   (int) date('d', $request['DTSTART'])
	    	) ;
	    	$end = array(
	    	   (int) date('Y', $request['DTEND']),
	    	   (int) date('m', $request['DTEND']),
	    	   (int) date('d', $request['DTEND'])
	    	);
	    	$daterange = array();
	    	while ($cur[0] < $end[0] || $cur[1] < $end[1] || $cur[2] <= $end[2]) {
	    		$daterange[] = sprintf("%04d-%02d-%02d", $cur[0], $cur[1], $cur[2]);
	    		$cur[2]++;
	    		if ($cur[2] > 31) {
	    			$cur[2] = 1;
	    			$cur[1]++;
	    			if ($cur[1] > 12) {
	    				$cur[1] = 1;
	    				$cur[0]++;
	    			}
	    		}
	    	}
	    	if (empty($daterange)) continue;
		    $query = 'SELECT ID, von, an, event, anfang, ende, visi, partstat, datum, status
		                FROM '.DB_PREFIX.'termine
		               WHERE an IN ('.implode(', ', $attendee).')
		                 AND       is_deleted is NULL
		                 AND datum IN ("'.implode('", "', $daterange).'")';
		    $res = db_query($query) or db_die();
		    $termine = array();
		    while ($row = db_fetch_row($res)) {
		        $event = array( 'ID'       => $row[0]
		                       ,'von'      => $row[1]
		                       ,'an'       => $row[2]
		                       ,'event'    => stripslashes($row[3])
		                       ,'anfang'   => $row[4]
		                       ,'ende'     => $row[5]
		                       ,'visi'     => $row[6]
		                       ,'partstat' => $row[7]
		                       ,'datum'    => $row[8]
		                       ,'status'   => $row[9]
		                      );
		        if (!calendar_can_see_events($event['an'], $event['visi'])) {
		            continue;
		        }
		        $event['event'] = calendar_process_event_text( $event['an'],
		                                                       $event['visi'],
		                                                       $event['event'] );
	            $termine[] = $event;
		    }
		    $ical = get_vfreebusy_ical($termine, array(
		       'DTSTART' => date('Ymd\THis\Z', $request['DTSTART']),
		       'DTEND' => date('Ymd\THis\Z', $request['DTEND']),
		    ));
		    $response[] = $this->caller->mkResponse('', $this->caller->mkprop('C:', 'calendar-data', $ical));
		    $result = true;
    	}
	    return $result;
    }
    
    /**
     * To be called by REPORT
     * Get the requested entires from the calendar and return them in
     * the reference $files. 
     *
     * @param array $options    the options that should be applied
     * @param array $files      reference to the variable that holds the result
     * @return boolean          TRUE if request was successfull; else: FALSE
     */
    function calendar_multiget($options, &$files)
    {
        $res = false;
        $event_IDs      = array();
        $todo_IDs       = array();
        $helpdesk_IDs   = array();
        
        $hrefs = $options->get('D:href');
        
        foreach ($hrefs as $href) {
        	$item = $this->get_phprojekt_id($href->data);
        	switch ($item['type']) {
        		case 'todo':
	        		$todo_IDs[] = $item;
	        		break;
        		case 'helpdesk':
        			$helpdesk_IDs[] = $item;
        			break;
        		case 'event':
                    $event_IDs[] = $item;
                    break;
        	}
        }

        if (!empty($event_IDs)) {
        	$event = new caldav_vevent($this);
            $res = $event->calendar_multiget($options, $files, $event_IDs) || $res;
        }
        
        if (!empty($todo_IDs)) {
            $todo = new caldav_vtodo($this);
            $res = $todo->calendar_multiget($options, $files, $todo_IDs) || $res;
        }
        
        if (!empty($helpdesk_IDs)) {
            $helpdesk = new caldav_vhelpdesk($this);
            $res = $helpdesk->calendar_multiget($options, $files, $helpdesk_IDs) || $res;
        }
        
    	return $res;
    }
    
    /**
     * To be called by REPORT
     * Get the requested entires from the calendar and return them in
     * the reference $files. 
     *
     * @param array $options    the options that should be applied
     * @param array $files      reference to the variable that holds the result
     * @return boolean          TRUE if request was successfull; else: FALSE
     */
    function calendar_query($options, &$files) {
    	// Response to requests to inbox or outbox
        if ($options->path == '/inbox') {
        	// At the moment no action is performed
            return true;
        } else if ($options->path == '/outbox') {
        	// At the moment no action is performed
            return true;
        }
        
        // Handle other requests
    	$files['files'] = empty($files['files']) ? array() : $files['files'];

        $res = false;
        
        $filter =& $options->get('C:filter');
        $filter = $filter[0]->flat();
        
        // EVENTS
        if (isset($filter['VEVENT'])) {
            $event = new caldav_vevent($this);
            $res = $event->calendar_query($options, $files) ? true : false;
        }
        
        // TODOS
        // HELPDESK
        if (isset($filter['VTODO'])) {
            $todo = new caldav_vtodo($this);
            $res = $todo->calendar_query($options, $files) ? true : false;
            $helpdesk = new caldav_vhelpdesk($this);
            $res = $helpdesk->calendar_query($options, $files) ? true : false;
        }
        
        return $res;
    }
    
    /**
     * Translates an array of attendees to their userids
     *
     * @param array $attendes   Array containing the attendees
     * @return array            Array with userids
     */
    function attendee_to_id($attendes) {
    	$result = array();
    	foreach ($attendes as $att_id => $attendee) {
    		if (isset($attendee['http'])) {
               	$result[$att_id] = $this->user['id'];
    		} else if (isset($attendee['mailto'])) {
                $query = 'SELECT ID FROM '.DB_PREFIX.'users
                   WHERE email = "'.addslashes($attendee['mailto']).'"
                     AND is_deleted is NULL
                   LIMIT 1';
                $res = db_query($query) or db_die();
                while ($row = db_fetch_row($res)) {
                	$result[$att_id] = $row[0];
                }
    		}
    	}
    	return array_unique($result);
    }
}
register_view('caldav');