<?php

/**
 * Implementation of the PHProject CALDAV
 * for todos
 * 
 * @author Gustavo Solt <solt@mayflower.de>
 * @version $Id: vevent.php
 */
class caldav_vtodo
{
    var $_caldav = null;
    var $_db     = null;
    var $whereID = null;

    function caldav_vtodo($caldav)
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
        // 1 : remark
        // 2 : ext
        // 3 : note
        // 4 : deadline
        // 5 : datum
        // 6 : status
        // 7 : priority
        // 8 : progress
        // 9 : sync2
        // 10: anfang
        $return = array();

        if ($data['SUMMARY'] != $row[1]) {
            $return[] = sprintf('remark = "%s"', $this->_db->quote($data['SUMMARY']));
        }

        $anfang = date("Y-m-d",$data['DTSTART']);
        if ($anfang != $row[10]) {
            $return[] = sprintf('anfang = "%s"', $this->_db->quote($anfang));
        }

        if (isset($data['DUE'])) {
            $deadline = date("Y-m-d",$data['DUE']);
            if ($deadline != $row[4]) {
                $return[] = sprintf('deadline = "%s"', $this->_db->quote($deadline));
            }
        } else {
            $return[] = sprintf('deadline = %s', 'NULL');
        }

        if (isset($data['STATUS'])) {
            $status = $this->get_status($data['STATUS']);
            if ($status != $row[6]) {
                $return[] = sprintf('status = %d', $this->_db->quote($status));
            }
        }

        if (isset($data['PERCENT-COMPLETE'])) {
            if ($data['PERCENT-COMPLETE'] != $row[8]) {
                $return[] = sprintf('progress = %d', $this->_db->quote($data['PERCENT-COMPLETE']));
                if ($data['PERCENT-COMPLETE'] == 100) {
                    $return[] = sprintf('status = %d', 5);
                }
            }
        }

        if ($data['DESCRIPTION'] != $row[3]) {
            $return[] = sprintf('note = "%s"', $this->_db->quote($data['DESCRIPTION']));
        }
     
        return $return;
    }

    function get_insert($data)
    {
        $dbTSnull = date('YmdHis', time() + PHPR_TIMEZONE*3600);
        if (isset($data['DTSTART'])) {
            $anfang   = date("Y-m-d",$data['DTSTART']);
        } else {
            $anfang   = date("Y-m-d");
        }
        
        if (isset($data['DUE']) && !empty($data['DUE'])) {
            $deadline = date("Y-m-d",$data['DUE']);
        }

        if (isset($data['STATUS'])) {
            $status = $this->get_status($data['STATUS']);
        } else {
            $status = 0;
        }

        if (!isset($data['DESCRIPTION'])) {
            $data['DESCRIPTION'] = '';
        }

        if (!isset($data['PERCENT-COMPLETE'])) {
            $data['PERCENT-COMPLETE'] = 0;
        }

        $values_data = array(
                    'von'       => (int) $this->user_ID,
                    'remark'    => '"'.$this->_db->quote($data['SUMMARY']).'"',
                    'ext'       => (int) $this->user_ID,
                    'note'      => '"'.$this->_db->quote($data['DESCRIPTION']).'"',
                    'deadline'  => (isset($data['DUE']) && !empty($data['DUE'])) ? '"'.$this->_db->quote($deadline).'"' : 'NULL',
                    'datum'     => '"'.date("YmdHis").'"',
                    'status'    => (int) $status,
                    'priority'  => (int) 0,
                    'progress'  => (int) $data['PERCENT-COMPLETE'],
                    'project'   => (int) 0,
                    'contact'   => (int) 0,
                    'sync1'     => $dbTSnull,
                    'sync2'     => $dbTSnull,
                    'anfang'    => '"'.$this->_db->quote($anfang).'"',
                    'gruppe'    => (int) $this->user_group,
                    'acc'       => '"'.$this->_db->quote('group').'"',
                    'acc_write' => '"'.$this->_db->quote('w').'"',
                    'ical_ID'   => '"'.$this->_db->quote($data['UID']).'"');

        $fields = array();
        $values = array();
        foreach ($values_data as $field => $value) {
            $fields[] = $field;
            $values[] = $value;
        }

        return array(implode(",",$fields),implode(",",$values));
    }

    function get_status($value)
    {
        switch ($value) {
            case 'NEEDS-ACTION':
                $status = 2;
                break;
            case 'IN-PROCESS':
                $status = 3;
                break;
            case 'CANCELLED':
                $status = 4;
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
            $this->whereID = "ical_ID = \"".$ical_ID."\"";
        } else {
            $this->whereID = '1';
        }
        
        $where = '';

        if (!empty($extraWhere)) {
            $where .= ' AND '.implode(' AND ', $extraWhere);
        }

        $sql   = "SELECT ID, remark, ext, note, deadline, datum, status,
                         priority, progress, sync2, anfang
                    FROM ".WD_TAB_TODOS."
                   WHERE is_deleted is NULL
                     AND (progress < 100 OR progress is NULL)
                     AND ".WD_TAB_TODOS.".status < 5
                     AND ext = ".(int)$this->user_ID."
                     AND (acc LIKE 'system'
                      OR ((von = ".(int)$this->user_ID." 
                      OR acc LIKE 'group'
                      OR acc LIKE '%\"". $this->user_kurz ."\"%')
                 ";
        if (!in_array($this->user_ID, explode(",", USERS_THAT_CAN_SEE_ALL_GROUPS))) {
            $sql .= " AND gruppe = ". (int)$this->user_group;
        }
        $sql .= "))
                     AND ". $this->whereID ."
                         ". $where;
        return $this->_db->query($sql);
    }

    function calendar_multiget($options, &$files, $todo_IDs)
    {
    	$where = array();
        $data_IDs = array();
        foreach ($todo_IDs as $item) {
            $data_IDs[] = $item['ID'];
        }
        $where[] = sprintf('%s.ID IN (%s)',
            WD_TAB_TODOS,
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
            && !empty($filter['C:time-range']['start'])
            && !empty($filter['C:time-range']['end'])) {
            $where[] = sprintf('(%s.deadline >= "%s" OR %s.deadline IS NULL) AND "%s" >= %s.anfang',
                $this->_db->quote($this->_caldav->ical_date_to_db($filter['C:time-range']['start'])),
                WD_TAB_TODOS,
                WD_TAB_TODOS,
                $this->_db->quote($this->_caldav->ical_date_to_db($filter['C:time-range']['end'])),
                WD_TAB_TODOS
            );
        }
        if ($filter['VTODO'] !== true) {
            $data_ID = $this->_caldav->get_phprojekt_id($filter['VTODO']);
            if (!empty($data_ID['ID'])) {
                $where[] = sprintf('AND %s.ID = %s',
                    WD_TAB_TODOS,
                    $data_ID['ID']
                );
            } else {
                $where = sprintf('AND %s.ical_ID = "%s"',
                    WD_TAB_TODOS,
                    $filter['VTODO']
                );
            }
        }
        return $this->build_multistatus($where, $options, $files);
    }
    
    function build_multistatus($where, $options, &$files) {
        $query = $this->get_item(null, null, $where);
        if (!$query) return false;
        
        while ($row = $query->get_next_row()) {
            $options->path = 'caldav/PHPROJEKT-TODO-'.$row[0].'.ics';
            $file = $this->_caldav->fileinforeport($options);
            $props = $options->get('D:prop');
            $props = $props[0]->get_childnames();
            if (in_array('D:getetag', $props)) {
                $file['props'][] = $this->_caldav->caller->mkprop('D:', 'getetag', $this->_caldav->caller->getetag($row));
            }
            if (in_array('C:calendar-data', $props)) {
                $file['props'][]  = $this->_caldav->caller->mkprop('C:', 'calendar-data', export_user_cal(array(), 'ical', array($row)));
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
                                AND ext = %s',
                                WD_TAB_TODOS,
                                $changes_string,
                                $this->user_ID);
                $this->_db->query($sql);
            }
            $code = '200 OK';
        } else {
            list($fields,$values) = $this->get_insert($data);
            $sql = sprintf('INSERT INTO %s (%s) VALUES (%s)',
                            WD_TAB_TODOS,
                            $fields,
                            $values);
            $this->_db->query($sql);
            $code = '201 Created';
        }
        return $code;
    }

    function delete($data_ID)
    {
        if (!empty($data_ID['ID'])) {
            $where = '(ID = %d)';

            if (PHPR_SOFT_DELETE) {
                $sql = sprintf('UPDATE %s SET is_deleted = 1
                                 WHERE '.$where,
                            WD_TAB_TODOS,
                            (int)$data_ID['ID'],
                            (int)$data_ID['ID']);
            } else {
                $sql = sprintf('DELETE FROM %s
                                 WHERE '.$where,
                            WD_TAB_TODOS,
                            (int)$data_ID['ID'],
                            (int)$data_ID['ID']);
            }
        } else {
            if (PHPR_SOFT_DELETE) {
                $sql = sprintf('UPDATE %s SET is_deleted = 1
                                 WHERE (ical_ID = "%s")',
                            WD_TAB_TODOS,
                            $data_ID['ical_ID'],
                            $data_ID['ical_ID']);
            } else {
                $sql = sprintf('DELETE FROM %s
                                 WHERE (ical_ID = "%s")',
                            WD_TAB_TODOS,
                            $data_ID['ical_ID'],
                            $data_ID['ical_ID']);
            }
        }

        $this->_db->query($sql);
    }
}
?>
