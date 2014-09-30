<?php
require_once(WEBDAV_PATH.'class_view.php');

/**
 * Implementation of the PHProject WebDAV Contact view
 */
class acl extends view {
    function acl() {
      parent::view();
    }
    
    /**
    * Check wether the PHProjekt contact module is enabled
    * @return bool
    */
    function checkAvailability() {
      global $adressen; // PHProjekt configuration option
      return $adressen > 0;
    }
    
    function REPORT($options, &$files)
    {
   		$action = webdav::getMethodName($options->name);
   		if (method_exists($this, $action)) {
   			return call_user_func_array(array($this, $action), array($options, &$files)) || $result;
   		}
        return false;
    }
    
    function PROPFIND($options, &$files) {
        return false;
    }
        
    function GET($options) {
        return $options;
    }
    
    function PUT(&$options) {
        return false;
    }
    
    function MKCOL(&$options) {
        // Currently (or: forever) no new contacts from within WebDAV
        return false;
    }
    
    function DELETE(&$options) {
        return false;
    }
    
    function MOVE(&$options) {
        return false;
    }
    
    function principal_property_search($options, &$files) {
    	$props = $options->get('D:prop');
        $props = $props[0]->get_childnames();
    	$user =& $this->user;
    	
    	$sql = sprintf('SELECT email, kurz
                      FROM %susers
                     WHERE status = 0
                        AND is_deleted is NULL
                        AND ID = %d', DB_PREFIX, $user['id']);
        $result = db_query($sql) or db_die();
        while ($row = db_fetch_row($result)) {
        	$user['email'] = $row[0];
        	$user['kurz'] = $row[1];
        }
    	
        foreach ($props as $prop) {
            switch ($prop) {
                case 'C:calendar-home-set':
                    $fileProps[] = $this->caller->mkprop('C:', 'calendar-home-set',
                       $this->caller->mkHref('')
                   );
                   break;
                case 'C:calendar-user-address-set':
                    if ($user['email']) {
                        $fileProps[] = $this->caller->mkprop('C:', 'calendar-user-address-set',
                           $this->caller->mkHref('mailto:'.$user['email'])
                        );
                    }
                    break;
                case 'C:schedule-inbox-URL':
                    $fileProps[] = $this->caller->mkprop('C:', 'schedule-inbox-URL',
                       $this->caller->mkHref('caldav/inbox')
                    );
                    break;
                case 'C:schedule-outbox-URL':
                    $fileProps[] = $this->caller->mkprop('C:', 'schedule-outbox-URL',
                       $this->caller->mkHref('caldav/outbox')
                    );
                    break;
            }
        }
        $files['files'][] = $this->caller->mkResponse('contactview/'.$user['kurz'], $fileProps);
    	return true;
    	
    	$users = $this->getUsers(null, array('email'));
    	
    	foreach ($users as $user) {
            $fileProps = array();
            if ($user['ID'] == $this->user['id']) {
                $path = '';
            } else {
            	$path = '/'.$user['kurz'];
            }
	    	foreach ($props as $prop) {
	    		switch ($prop) {
	    			case 'C:calendar-home-set':
	    				$fileProps[] = $this->caller->mkprop('C:', 'calendar-home-set',
                           $this->caller->mkHref($path)
                       );
                       break;
	                case 'C:calendar-user-address-set':
	                	if ($user['email']) {
		                    $fileProps[] = $this->caller->mkprop('C:', 'calendar-user-address-set',
		                       $this->caller->mkHref('mailto:'.$user['email'])
		                    );
	                	}
	                    break;
	                case 'C:schedule-inbox-URL':
	                    $fileProps[] = $this->caller->mkprop('C:', 'schedule-inbox-URL',
                           $this->caller->mkHref('caldav'.$path.'/inbox')
                        );
	                    break;
	                case 'C:schedule-outbox-URL':
	                    $fileProps[] = $this->caller->mkprop('C:', 'schedule-outbox-URL',
                           $this->caller->mkHref('caldav'.$path.'/outbox')
                        );
	                    break;
	    		}
	    	}
	    	$files['files'][] = $this->caller->mkResponse('contactview/'.$user['kurz'], $fileProps);
    	}
    	return true;
    }
    
    function getUsers($group = null, $cols = array()) {
    	$select = '';
    	if (!empty($cols))
    	   $select = ', '.implode(', ', $cols);
    	if ($group) {  // select user from this group
		    $sql = "SELECT ".DB_PREFIX."users.ID, kurz $select
		              FROM ".DB_PREFIX."users, ".DB_PREFIX."grup_user
		             WHERE ".DB_PREFIX."users.ID = user_ID
		               AND grup_ID = ".(int)$group." 
		               AND ".DB_PREFIX."users.status = 0
		               AND ".DB_PREFIX."users.is_deleted is NULL
		               AND (".DB_PREFIX."users.usertype = 0 OR 
		                    ".DB_PREFIX."users.usertype = 2 OR 
		                    ".DB_PREFIX."users.usertype = 3)";
		} else {    // if user is not assigned to a group or group system is not activated
		    $sql = "SELECT ID, kurz $select
		              FROM ".DB_PREFIX."users
		             WHERE status = 0
		                AND is_deleted is NULL
		                AND (".DB_PREFIX."users.usertype = 0 OR 
		                    ".DB_PREFIX."users.usertype = 2 OR 
		                    ".DB_PREFIX."users.usertype = 3)";
		}
		$result = db_query($sql) or db_die();
		$cols = array_merge(array('ID', 'kurz'), $cols);
		$users = array();
		while ($row = db_fetch_row($result)) {
			$user = array();
			foreach ($cols as $c => $n) {
				$user[$n] = $row[$c];
			}
			$users[$user['ID']] = $user;
		}
		return $users;
    }
    
    function principal_collection_set($options, &$files, &$parent) {
    	$uri = (@$parent->_SERVER["HTTPS"] === "on" ? "https:" : "http:");
        $uri.= '//'.$parent->_SERVER['HTTP_HOST'].$parent->_SERVER['SCRIPT_NAME'];
        $files['files'] = array(
            array(
                'path' => '/Mayflower',
                'props' => array(
                    $parent->mkprop('principal-collection-set', $parent->mkHref('acl'))
                )
            )
        );
        return true;
    }
}

register_view('acl');