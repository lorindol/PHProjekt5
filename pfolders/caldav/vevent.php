<?php

/**
 * Implementation of the PHProject CALDAV
 * for events
 * 
 * @author Gustavo Solt <solt@mayflower.de>
 * @version $Id: vevent.php
 */
class caldav_vevent
{
	/**
	 * Parent caldav object
	 *
	 * @var caldav
	 */
    var $_caldav = null;
    var $_db     = null;
    var $whereID = null;
    var $user_ID = null;

    function caldav_vevent($caldav)
    {
        $this->_caldav = $caldav;
        $this->_db     = $caldav->db;
        $this->user_ID = $caldav->user['id'];
    }

    function get_update($data, $row)
    {
        // $row
        // 0 : id
        // 1 : event (one line w/out \n)
        // 2 : date
        // 3 : begin
        // 4 : end
        // 5 : remark
        // 6 : contact-vorname
        // 7 : contact-nachname
        // 8 : contact-firma
        // 9 : contact-email
        // 10: visibility
        // 11: sync2
        // 12: ical_ID
        // 13: seria_id
        // 14: serie_typ
        // 15: serie_bis

        $return = array();

        $data = $this->process_ical($data);
        
        if ($data['SUMMARY'] != $row[1]) {
            $return[] = sprintf('event = "%s"', $this->_db->quote($data['SUMMARY']));
        }

        if ($data['datum'] != $row[2]) {
            $return[] = sprintf('datum = "%s"', $this->_db->quote($data['datum']));
        }

        if ($data['anfang'] != $row[3]) {
            $return[] = sprintf('anfang = "%s"', $this->_db->quote($data['anfang']));
        }

        if ($data['ende'] != $row[4]) {
            $return[] = sprintf('ende = "%s"', $this->_db->quote($data['ende']));
        }

        if ($data['DESCRIPTION'] != $row[5]) {
            $return[] = sprintf('remark = "%s"', $this->_db->quote($data['DESCRIPTION']));
        }
        
        if (isset($data['STATUS'])) {
        	$return[] = sprintf('`partstat` = "%s"', $this->_db->quote($data['STATUS']));
        }
        
        return $return;
    }
    
    function get_insert($data)
    {
        $dbTSnull = date('YmdHis', time() + PHPR_TIMEZONE*3600);

        $data = $this->process_ical($data);

        if (isset($data['RRULE'])) {
            list($serie_typ, $serie_weekday, $serie_bis) = $this->get_serial_typ($data['RRULE'], $data['datum']);
            $serie_typ = serialize(array('typ'     => $serie_typ,
                                         'weekday' => $serie_weekday));
        } else {
            $serie_typ = ''; 
            $serie_bis = '';
        }
        
        if (isset($data['STATUS'])) {
        	$partstat = $data['STATUS'];
        } else {
        	$data['STATUS'] = 2;
        }
                    
        $values_data = array(
                        'parent'    => (int) $data['parent'],
                        'serie_id'  => (int) $data['serie_id'],
                        'serie_typ' => '"'.$this->_db->quote($serie_typ).'"',
                        'serie_bis' => '"'.$this->_db->quote($serie_bis).'"',
                        'von'       => (int) $this->_caldav->user['id'],
                        'an'        => (int) isset($data['an'])?$data['an']:$this->_caldav->user['id'],
                        'event'     => '"'.$this->_db->quote($data['SUMMARY']).'"',
                        'remark'    => '"'.$this->_db->quote($data['DESCRIPTION']).'"',
                        'projekt'   => (int) 0,
                        'datum'     => '"'.$this->_db->quote($data['datum']).'"',
                        'anfang'    => '"'.$this->_db->quote($data['anfang']).'"',
                        'ende'      => '"'.$this->_db->quote($data['ende']).'"',
                        'ort'       => '""',
                        'contact'   => (int) 0,
                        'remind'    => (int) 0,
                        'visi'      => (int) 0,
                        'partstat'  => (int) $partstat,
                        'priority'  => (int) 0,
                        'status'    => (int) 0,
                        'sync1'     => $dbTSnull,
                        'sync2'     => $dbTSnull,
                        'upload'    => '""',
                        'ical_ID'   => isset($data['UID'])?'"'.$this->_db->quote($data['UID']).'"':'null',
        );

        $fields = array();
        $values = array();
        foreach ($values_data as $field => $value) {
            $fields[] = $field;
            $values[] = $value;
        }

        return array(implode(",",$fields),implode(",",$values));
    }

    function get_item($ID = null, $ical_ID = null, $extraWhere = array())
    {
        global $user_ID;
        $user_ID = $this->user_ID;

        if (null !== $ID) {
            $this->whereID = "(". WD_TAB_EVENTS.".ID = ".(int)$ID." OR ". WD_TAB_EVENTS.".serie_id = ".(int)$ID.")";
        } else if (null !== $ical_ID) {
            $this->whereID = WD_TAB_EVENTS.".ical_ID = \"".$ical_ID."\"";
        } else {
            $this->whereID = '1';
        }
        
        $where = calendar_get_permission_where($user_ID);

        if (!empty($extraWhere)) {
            $where .= ' AND '.implode(' AND ', $extraWhere);
        }

        $sql   = "SELECT ".WD_TAB_EVENTS.".ID,
                         ".WD_TAB_EVENTS.".event,
                         ".WD_TAB_EVENTS.".datum,
                         ".WD_TAB_EVENTS.".anfang,
                         ".WD_TAB_EVENTS.".ende,
                         ".WD_TAB_EVENTS.".remark,
                         ".WD_TAB_CONTACTS.".vorname,
                         ".WD_TAB_CONTACTS.".nachname,
                         ".WD_TAB_CONTACTS.".firma,
                         ".WD_TAB_CONTACTS.".email,
                         ".WD_TAB_EVENTS.".visi,
                         ".WD_TAB_EVENTS.".sync2,
                         ".WD_TAB_EVENTS.".ical_ID,
                         ".WD_TAB_EVENTS.".serie_id,
                         ".WD_TAB_EVENTS.".serie_typ, 
                         ".WD_TAB_EVENTS.".serie_bis,
                         ".WD_TAB_EVENTS.".partstat,
                         ".WD_TAB_EVENTS.".parent,
                         ".WD_TAB_EVENTS.".von
                    FROM ".WD_TAB_EVENTS."
               LEFT JOIN ".WD_TAB_CONTACTS." ON ".WD_TAB_EVENTS.".contact = ".WD_TAB_CONTACTS.".ID
                   WHERE ".WD_TAB_EVENTS.".an = ".(int)$this->user_ID." 
                     AND ".WD_TAB_EVENTS.".is_deleted is NULL
                     AND ".WD_TAB_CONTACTS.".is_deleted is NULL
                     AND ". $this->whereID ."
                         ". $where;

        return $this->_db->query($sql);
    }

    function calendar_multiget($options, &$files, $event_IDs)
    {
    	$where = array();
        $data_IDs = array();
        foreach ($event_IDs as $item) {
            $data_IDs[] = $item['ID'];
        }
        $where[] = sprintf('%s.ID IN (%s)',
            WD_TAB_EVENTS,
            implode(',', $data_IDs)
        );
        return $this->build_multistatus($where, $options, $files);
    }
    
    function calendar_query($options, &$files)
    {
        $where = array();
        // Filter by date
        $filter =& $options->get('C:filter');
        $filter =& $filter[0]->flat();
        if (isset($filter['C:time-range'])
            && !empty($filter['C:time-range']['start'])
            && !empty($filter['C:time-range']['end'])) {
            $where[] = sprintf("(%s.datum >= \"%s\" AND %s.datum <= \"%s\") ",
                    WD_TAB_EVENTS,
                    $this->_db->quote($this->_caldav->ical_date_to_db($filter['C:time-range']['start'])),
                    WD_TAB_EVENTS,
                    $this->_db->quote($this->_caldav->ical_date_to_db($filter['C:time-range']['end'])));
        }
        // Filter by filename
        if ($filter['VEVENT'] !== true) {
            $data_ID = $this->_caldav->get_phprojekt_id($filter['VEVENT']);
            if (!empty($data_ID['ID'])) {
                $where[] = sprintf(WD_TAB_EVENTS.".ID = %s", $data_ID['ID']);
             }
        }
        return $this->build_multistatus($where, $options, $files);
    }
    
    function build_multistatus($where, $options, &$files) {
    	$query = $this->get_item(null, null, $where);
        if (!$query) return false;
        
        $multiples = array();
        while ($row = $query->get_next_row()) {
            if ($row[17]) {
                $invitees = calendar_get_event_invitees($row[17], $row[12]);
            } else {
            	$invitees = calendar_get_event_invitees($row[0], $row[12]);
            }
            $row['invitees'] = array();
            if (count($invitees) > 0) {
                foreach ($invitees as $item) {
                    if (!($item['parent'] == $row[0]
                            || $item['ID'] == $row[17]
                            || $row[0] == $item['ID']
                            || ($row[17] && $row[17] == $item['parent']))
                         || isset($row['invitees'][$item['an']])) {
                       continue;
                    }
                    $result = db_query('SELECT `vorname`, `nachname`, `email` FROM '.DB_PREFIX.'users WHERE `ID` = '.(int)$item['an']) or db_die();
                    $result = db_fetch_row($result);
                    if ($result[0]) {
                        $row['invitees'][$item['an']] = array(
                            'name' => $result[0].' '.$result[1],
                            'email' => $result[2],
                        );
                    }
                }
            }
            if ($row[13] == 0 || !isset($multiples[$row[13]])) {
                $options->path = 'caldav/PHPROJEKT-EVENT-'.$row[0].'.ics';
                $file = $this->_caldav->fileinforeport($options);
                $props = $options->get('D:prop');
                $props = $props[0]->get_childnames();
                if (in_array('D:getetag', $props)) {
                    $file['props'][] = $this->_caldav->caller->mkprop('D:', 'getetag', $this->_caldav->caller->getetag($row));
                }
                if (in_array('C:calendar-data', $props)) {
                    $file['props'][]  = $this->_caldav->caller->mkprop('C:', 'calendar-data', export_user_cal(array($row), 'ical'));
                }
                $files['files'][] = $file;
                if ($row[13] == 0) {
                    $multiples[$row[0]] = 1;
                } else {
                    $multiples[$row[13]] = 1;
                }
            }
        }
        return true;
    }

    function update($data_ID, $data, $options)
    {
        if (!empty($data_ID['ID'])) {
            $query = $this->get_item($data_ID['ID']);
        } else if (!empty($data_ID['ical_ID'])) {
            $query = $this->get_item(null, $data_ID['ical_ID']);
        }

        if ($query) {
        	$row = $query->get_next_row();
        	if ($row[17]) {
        		$attendees = $this->_caldav->attendee_to_id($data['ATTENDEE']);
        		foreach ($attendees as $key => $attendee) {
        			if ($attendee == $this->user_ID) {
        				$partstat = isset($data['ATTENDEE'][$key]['PARTSTAT']) ?
        				            $data['ATTENDEE'][$key]['PARTSTAT'] : 'ACCEPTED';
        				switch ($partstat) {
        					case 'TENTATIVE':
                                $partstat = '1';
        					case 'ACCEPTED':
        						$partstat = '2';
        						break;
        					case 'DECLINED':
        						$partstat = '3';
        						break;
        					default:
        						$partstat = false;
        				}
        				if (!$partstat) {
        					return '403 Forbidden';
        				}
        				$sql = sprintf('UPDATE %s SET `partstat` = %d WHERE `ID` = %d',
        				    WD_TAB_EVENTS, $partstat, $data_ID['ID']);
        				$this->_db->query($sql);
        				return '200 OK';
        			}
        		}
        	}
        } else {
        	$row = false;
        }
        
        $data = $this->process_ical($data);
        
        if ($row && isset($row[0])) {
            if (isset($data['EXDATE'])) {
                $datum = date('Y-m-d', $data['EXDATE']);
                $this->deleteOneEvent($options, $datum);
            }
            if (!empty($data_ID['c']) && isset($data['RRULE'])) {
                // You can't edit one item only from a ical_ID
                // sinse all the events have the same ical_ID
                // and you don't know the serial_id
                // the calendar must be reloaded to get the correct phprojekt-id
                return false;
            } else {
                $changes = $this->get_update($data, $row);
                if (!empty($changes)) {
                    $changes_string = implode(",", $changes);
                    $sql = sprintf('UPDATE %s SET %s WHERE '. $this->whereID .' AND an = %s',
                                    WD_TAB_EVENTS,
                                    $changes_string,
                                    $this->user_ID);
                    $this->_db->query($sql);
                }
            }
            $mail_title  = __('Date changed').': '.$data['SUMMARY'].' '
                         . '('.$data['datum'].', '
                         . $data['anfang'].'-'.$data['ende'].')';
            $code = '200 OK';
        } else {
            list($fields,$values) = $this->get_insert($data);
            $sql = sprintf('INSERT INTO %s (%s) VALUES (%s)',
                            WD_TAB_EVENTS,
                            $fields,
                            $values);
            $this->_db->query($sql);
            $sql = sprintf('SELECT MAX(ID) FROM %s', WD_TAB_EVENTS);
            $query = $this->_db->query($sql);
            $row = $query->get_next_row();
            $data_ID['ID'] = $row[0];
            if (isset($data['RRULE'])) {
                $this->insert_serial_events($data['RRULE'], $data_ID['ID'], $fields, $values);
            }
            $mail_title  = __('New Date').': '.$data['SUMMARY'].' '
                         . '('.$data['datum'].', '
                         . $data['anfang'].'-'.$data['ende'].')';
            $code = '201 Created';
        }
        // Update Attendees
        if (!empty($data['ATTENDEE'])) {
        	$data['parent'] = $data_ID['ID'];
        	unset($data['UID']);
        	$att_events = array();
        	$query = $this->_db->query('SELECT `an`,`ID` FROM `'.WD_TAB_EVENTS.'`
                          WHERE `parent` = "'.$this->_db->quote($data_ID['ID']).'"');
            while ($row = $query->get_next_row()) {
            	$att_events[$row[0]] = $row[1];
            }
            $att_old = $att_events;
            $attendees = $this->_caldav->attendee_to_id($data['ATTENDEE']);
	        foreach ($attendees as $attendee) {
	        	// Don't add the owner to the attendees
	        	if ($attendee == $this->user_ID) continue;
	        	// Prepare data
                $data['an'] = $attendee;
                $data['STATUS'] = 1;
                if (isset($att_old[$attendee])) {
                	// Update Attendees
	                $changes = $this->get_update($data,$row);
	                if (!empty($changes)) {
	                    $changes_string = implode(",", $changes);
	                    $sql = sprintf('UPDATE %s SET %s WHERE ID = %s',
	                                    WD_TAB_EVENTS,
	                                    $changes_string,
	                                    (int)$att_old[$attendee]);
	                    $this->_db->query($sql);
	                    unset($att_old[$attendee]);
	                }
                } else {
                	// Add new Attendees
                	list($fields,$values) = $this->get_insert($data);
		            $sql = sprintf('INSERT INTO %s (%s) VALUES (%s)',
		                            WD_TAB_EVENTS,
		                            $fields,
		                            $values);
		            $this->_db->query($sql);
		            $sql = sprintf('SELECT MAX(`ID`) FROM %s WHERE `an` = %d AND `parent` = %d',
                                    WD_TAB_EVENTS,
                                    $data['an'],
                                    $data['parent']);
                    $this->_db->query($sql);
                    $row = $query->get_next_row();
                    $att_events[$data['an']] = $row[0];
                }
	        }
            // Delete old Attendees
            if (!empty($att_old)) {
                $sql = sprintf('DELETE FROM %s WHERE ID IN (%s)',
                                WD_TAB_EVENTS,
                                implode(', ', $att_old));
                $this->_db->query($sql);
            }
            // Send notification mails
            if ($data['X-MOZ-SEND-INVITATIONS'] == 'TRUE') {
            	require_once(PHPROJEKT_PATH.'lib/notification.inc.php');
		        foreach ($data['ATTENDEE'] as $att_id => $attendee) {
	                $data['ID'] = $data['parent'];
		        	if (isset($attendees[$att_id])) {
		        		$attendee['ID'] = $attendees[$att_id];
	                    if (isset($att_events[$attendee['ID']]))
	                        $data['ID'] = $att_events[$attendee['ID']];
		        	} else if (isset($attendee['mailto'])) {
		        		$attendee['ID'] = $attendee['mailto'];
		        	} else {
		        		continue;
		        	}
		            $export_array = array(array(
		                $data['ID'],                    // UID:
		                $data['SUMMARY'],               // SUMMARY
		                $data['datum'],                 // DTSTART date
		                $data['anfang'],                // DTSTART time
		                $data['ende'],                  // DTEND time
		                $data['DESCRIPTION'],           // DESCRIPTION
		                null,                           // ??? (deprecated)
		                null,                           // ??? (deprecated)
		                null,                           // ??? (deprecated)
		                null,                           // ??? (deprecated)
		                2,                              // CLASS (1: PRIVATE; 2: PUBLIC)
		                null,                           // LAST-MODIFIED
		                null,                           // ???
		                null,                           // ???
		                null,                           // RRULE
		                null,                           // RRULE END
		                1,                              // STATUS
		                'invitees' => null              // ATTENDEES
		            ));
		            $backlink = is_numeric($attendee['ID']) ? '&mode=forms&ID='.(int) $data['ID'] : '';
		            $vcal = export_user_cal($export_array, 'ical');
		            $notify = new Notification(
									$this->user_ID,
									false,
									'calendar',
									array($attendee['ID']),
									$data['ID'],
									'&view=0'.$backlink,
									'',
									html_entity_decode($mail_title),
									'new',
									'',
									0,
									'',
									$vcal
								);
		            $notify->notify();
		        }
            }
        }
        return $code;
    }

    /**
     * Delete one event from serial events
     */
    function deleteOneEvent($options, $datum)
    {
        $data_ID = $this->_caldav->get_phprojekt_id($options['path']);

        if (!empty($data_ID['ID'])) {
            if (PHPR_SOFT_DELETE) {
                $sql = sprintf('UPDATE %s SET is_deleted = 1
                                 WHERE (ID = %d OR serie_id = %d)
                                   AND datum = "%s"',
                                WD_TAB_EVENTS,
                                (int)$data_ID['ID'],
                                (int)$data_ID['ID'],
                                $datum);
            } else {
                $sql = sprintf('DELETE FROM %s
                                 WHERE (ID = %d OR serie_id = %d)
                                   AND datum = "%s"',
                                WD_TAB_EVENTS,
                                (int)$data_ID['ID'],
                                (int)$data_ID['ID'],
                                $datum);
            }
            $this->_db->query($sql);
        } else {
            // You can't delete one item only from a ical_ID
            // sinse all the events have the same ical_ID and you don't know the serial_id
            // the calendar must be reloaded to get the correct phprojekt-id
        }
    }

    function insert_serial_events($rule,$serie_id,$fields,$values)
    {
        $values = explode(",",$values);

        //datum
        $datum = ereg_replace('"','',$values[9]);

        list($serie_typ, $serie_weekday, $serie_bis) = $this->get_serial_typ($rule, $datum);

        $multipleData['serie_typ']      = $serie_typ;
        $multipleData['serie_weekday']  = $serie_weekday;
        $multipleData['datum']          = $datum;
        $multipleData['serie_bis']      = $serie_bis;

        // Check and remove from the Rule, the deleted events
        $events = calendar_calculate_serial_events($multipleData);

        // serie_id
        $values[1] = $serie_id;
        // serie_typ
        $values[2] = '"'.$this->_db->quote(serialize(array('typ'     => $serie_typ,
                                                       'weekday' => $serie_weekday))).'"';
        // serie_bis
        $values[3] = '"'.$this->_db->quote($multipleData['serie_bis']).'"';

        foreach ($events as $tmp => $datum) {
            $values[9] = '"'.$this->_db->quote($datum).'"';
            $valuesTmp = implode(",",$values);
            $sql = sprintf('INSERT INTO %s (%s) VALUES (%s)',
                            WD_TAB_EVENTS,
                            $fields,
                            $valuesTmp);
            $this->_db->query($sql);
        }

        return $values[2];
    }

    function get_serial_typ($rule, $datum)
    {
        // serie_typ
        switch($rule['FREQ']) {
            case 'DAILY':
                $serie_typ = 'd1';
                break;
            case 'WEEKLY':
                if (isset($rule['INTERVAL'])) {
                    if ($rule['INTERVAL'] > 4) {
                        $rule['INTERVAL'] = 4;
                    }
                    $serie_typ = 'w'.$rule['INTERVAL'];
                } else {
                    $serie_typ = 'w1';
                }
                break;
            case 'MONTHLY':
                $serie_typ = 'm1';
                break;
            case 'YEARLY':
                $serie_typ = 'y1';
                break;
        }

        // serie_weekday
        $serie_weekday = array();        
        if (isset($rule['BYDAY'])) {
            // Weeks days
            $weeksDays = array( 'MO' => 0, 'TU' => 1, 'WE' => 2, 'TH' => 3,
                                'FR' => 4, 'SA' => 5, 'SU' => 6);
            $byday = explode(',',$rule['BYDAY']);
            foreach($byday as $weekday) {
                $serie_weekday[$weeksDays[$weekday]] = 1;
            }
        }

        // serie_bis
        if (isset($rule['UNTIL'])) {
            $serie_bis = date("Y-m-d",$rule['UNTIL']-86400);
        } else {
            list($y,$m,$d) = split("-",$datum);
            $serie_bis = date("Y-m-d",mktime(0,0,0,$m,$d,$y+1));
        }

        if ($serie_typ{0} != 'w') $serie_weekday = array();

        return array($serie_typ, $serie_weekday, $serie_bis);
    }
    
    function process_ical($data) {
        if (isset($data['DTSTART-ALLDAY'])) {
            $data['datum'] = date('Y-m-d', $data['DTSTART-ALLDAY']);
        } else {
            $data['datum'] = date('Y-m-d', $data['DTSTART']);
        }
        
        if (isset($data['DTSTART-ALLDAY'])) {
            $data['anfang'] = '----';
        } else {
            $data['anfang'] = date('Hi', $data['DTSTART']);
        }

        if (isset($data['DTEND-ALLDAY'])) {
            $data['ende'] = '----';
        } else {
            $data['ende'] = date('Hi', $data['DTEND']);
        }
        
        if (!isset($data['DESCRIPTION'])) {
            $data['DESCRIPTION'] = '';
        }
        
        if (!isset($data['parent'])) {
            $data['parent'] = 0;
        }
        if (!isset($data['serie_id'])) {
            $data['serie_id'] = 0;
        }
        
        return $data;
    }

    function delete($data_ID)
    {
        if (!empty($data_ID['ID'])) {
            $query = $this->get_item($data_ID['ID']);
            $row   = $query->get_next_row();
            if ($row[13] != 0) {
                // Get the parent serial event
                $query2 = $this->get_item($row[13]);
                $row2   = $query2->get_next_row();
                $data_ID['ID'] = $row2[0];
            }
            $where = '(ID = %d OR parent = %d OR serie_id = %d)';
            
            if (PHPR_SOFT_DELETE) {
                $sql = sprintf('UPDATE %s SET is_deleted = 1
                                 WHERE '.$where,
                            WD_TAB_EVENTS,
                            (int)$data_ID['ID'],
                            (int)$data_ID['ID'],
                            (int)$data_ID['ID']);
            } else {
                $sql = sprintf('DELETE FROM %s
                                 WHERE '.$where,
                            WD_TAB_EVENTS,
                            (int)$data_ID['ID'],
                            (int)$data_ID['ID'],
                            (int)$data_ID['ID']);
            }
        } else {
            // Events recently created will delete all the serie
            if (PHPR_SOFT_DELETE) {
                $sql = sprintf('UPDATE %s SET is_deleted = 1
                                 WHERE (ical_ID = "%s")',
                            WD_TAB_EVENTS,
                            $data_ID['ical_ID'],
                            $data_ID['ical_ID']);
            } else {
                $sql = sprintf('DELETE FROM %s
                                 WHERE (ical_ID = "%s")',
                            WD_TAB_EVENTS,
                            $data_ID['ical_ID'],
                            $data_ID['ical_ID']);
            }
        }

        $this->_db->query($sql);
    }
}
?>
