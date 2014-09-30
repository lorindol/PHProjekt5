<?php

/**
 * Implementation of the PHProject CALDAV
 * for helpdesk
 * 
 * @author Gustavo Solt <solt@mayflower.de>
 * @version $Id: vevent.php
 */
class caldav_vhelpdesk
{
    var $_caldav = null;
    var $_db     = null;
    var $whereID = null;

    function caldav_vhelpdesk($caldav)
    {
        $this->_caldav    = $caldav;
        $this->_db        = $caldav->db;
        $this->user_ID    = $caldav->user['id'];
        $this->user_group = $caldav->user['group'];
        $this->user_kurz  = $caldav->user['kurz'];
    }

    function get_update($data, $row)
    {
        // Format of data
        // 0 : id
        // 1 : name
        // 2 : von
        // 3 : note
        // 4 : due_date
        // 5 : submit
        // 6 : status
        // 7 : priority
        $return = array();

        if ($data['SUMMARY'] != $row[1]) {
            $return[] = sprintf('name = "%s"', $this->_db->quote($data['SUMMARY']));
        }

        if (isset($data['DUE'])) {
            $deadline = date("Y-m-d",$data['DUE']);
            if ($deadline != $row[4]) {
                $return[] = sprintf('due_date = "%s"', $this->_db->quote($deadline));
            }
        } else {
            $return[] = sprintf('due_date = %s', 'NULL');
        }

        if (isset($data['STATUS'])) {
            $status = $this->get_status($data['STATUS']);
            if ($status != $row[6]) {
                $return[] = sprintf('status = %d', $this->_db->quote($status));
            }
        }

        if ($data['DESCRIPTION'] != $row[3]) {
            $return[] = sprintf('note = "%s"', $this->_db->quote($data['DESCRIPTION']));
        }
     
        return $return;
    }

    function get_status($value)
    {
        switch ($value) {
            case 'NEEDS-ACTION':
                $status = 1;
                break;
            case 'IN-PROCESS':
                $status = 2;
                break;
            case 'CANCELLED':
                $status = 5;
                break;
            case 'COMPLETED':
                $status = 5;
                break;
        }

        return $status;
    }

    function get_item($ID = null, $ical_ID = null, $extraWhere = array())
    {
        if (null !== $ID) {
            $this->whereID = "ID = ".(int)$ID;
        } else if (null !== $ical_ID) {
            $this->whereID = 'ical_ID = "'.$this->_db->quote($ical_ID).'"';
        } else {
            $this->whereID = '1';
        }
        
        $where = '';

        if (!empty($extraWhere)) {
            $where .= ' AND '.implode(' AND ', $extraWhere);
        }

        $sql   = "SELECT ID, name, von, note, due_date, submit, status, priority
                    FROM ".WD_TAB_HELPDESK."
                   WHERE is_deleted is NULL
                     AND ".WD_TAB_HELPDESK.".status < 3
                     AND assigned = ".(int)$this->user_ID."
                     AND (acc_read LIKE 'system'
                      OR ((von = ".(int)$this->user_ID." 
                      OR acc_read LIKE 'group'
                      OR acc_read LIKE '%\"". $this->user_kurz ."\"%')
                 ";
        if (!in_array($this->user_ID, explode(",", USERS_THAT_CAN_SEE_ALL_GROUPS))) {
            $sql .= ' AND gruppe = '. (int)$this->user_group;
        }
        $sql .= '))
                     AND '. $this->whereID .'
                         '. $where;

        return $this->_db->query($sql);
    }

    function calendar_multiget($options, &$files, $helpdesk_IDs)
    {
    	$where = array();
        $data_IDs = array();
        foreach ($helpdesk_IDs as $item) {
            $data_IDs[] = $item['ID'];
        }
        $where[] = sprintf('%s.ID IN (%s)',
            WD_TAB_HELPDESK,
            implode(',', $data_IDs)
        );
        return $this->build_multistatus($where, $options, $files);
    }

    function calendar_query($options, &$files)
    {
    	$where = array();
    	$filter = $options->get('C:filter');
        $filter = $filter[0]->flat();
    	if (isset($filter['C:time-range'])
            && !empty($filter['C:time-range']['end'])) {
            $where[] = sprintf('(%s.due_date >= "%s" OR %s.due_date IS NULL) ',
                WD_TAB_HELPDESK,
                $this->_db->quote($this->_caldav->ical_date_to_db($filter['C:time-range']['end'])),
                WD_TAB_HELPDESK,
                WD_TAB_HELPDESK
            );
        }
        if ($filter['VTODO'] && $filter['VTODO'] !== true) {
            $data_ID = $this->_caldav->get_phprojekt_id($filter['VTODO']);
            if (!empty($data_ID['ID'])) {
                $where[] = sprintf('%s.ID = %s',
                    WD_TAB_HELPDESK,
                    $data_ID['ID']
                );
            } else {
                $where[] = sprintf('%s.ical_ID = "%s"',
                    WD_TAB_HELPDESK,
                    $filter['VTODO']
                );
            }
        }
        return $this->build_multistatus($where, $options, $files);
    }
    
    function build_multistatus($where, $options, &$files)
    {
        $query = $this->get_item(null, null, $where);
        if (!$query) return false;
        
    	while ($row = $query->get_next_row()) {
    		$options->path = 'caldav/PHPROJEKT-HELPDESK-'.$row[0].'.ics';
    		$file = $this->_caldav->fileinforeport($options);
    		$props = $options->get('D:prop');
    		$props = $props[0]->get_childnames();
            if (in_array('D:getetag', $props)) {
                $file['props'][] = $this->_caldav->caller->mkprop('D:', 'getetag', $this->_caldav->caller->getetag($row));
            }
            if (in_array('C:calendar-data', $props)) {
                $file['props'][]  = $this->_caldav->caller->mkprop('C:', 'calendar-data', export_user_cal(array(), 'ical', array(), array($row)));
            }
		    $files['files'][] = $file;
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
        } else {
        	$row = false;
        }
        
        if ($row && isset($row[0])) {
            $changes = $this->get_update($data,$row);
            if (!empty($changes)) {
                $changes_string = implode(",", $changes);
                $sql = sprintf('UPDATE %s SET %s WHERE '. $this->whereID .'
                                AND von = %s',
                                WD_TAB_HELPDESK,
                                $changes_string,
                                $this->user_ID);
                $this->_db->query($sql);
            }
        } else {
            // Insert
        }
        return '200 OK';
    }

    function delete($data_ID)
    {
        if (!empty($data_ID['ID'])) {
            $where = '(ID = %d)';

            if (PHPR_SOFT_DELETE) {
                $sql = sprintf('UPDATE %s SET is_deleted = 1
                                 WHERE '.$where,
                            WD_TAB_HELPDESK,
                            (int)$data_ID['ID'],
                            (int)$data_ID['ID']);
            } else {
                $sql = sprintf('DELETE FROM %s
                                 WHERE '.$where,
                            WD_TAB_HELPDESK,
                            (int)$data_ID['ID'],
                            (int)$data_ID['ID']);
            }
        } else {
            if (PHPR_SOFT_DELETE) {
                $sql = sprintf('UPDATE %s SET is_deleted = 1
                                 WHERE (ical_ID = "%s")',
                            WD_TAB_HELPDESK,
                            $data_ID['ical_ID'],
                            $data_ID['ical_ID']);
            } else {
                $sql = sprintf('DELETE FROM %s
                                 WHERE (ical_ID = "%s")',
                            WD_TAB_HELPDESK,
                            $data_ID['ical_ID'],
                            $data_ID['ical_ID']);
            }
        }

        $this->_db->query($sql);
    }
}
?>
