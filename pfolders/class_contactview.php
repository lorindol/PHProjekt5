<?php
require_once(WEBDAV_PATH.'class_view.php');

// $Id: class_contactview.php,v 1.2 2007-12-14 16:19:55 gustavo Exp $

define('CONTACT_COLUMN', 'CONCAT(nachname, ", ", vorname, " (", firma,")")');

/**
* Implementation of the PHProject WebDAV Contact view
* 
* @author Johannes Schlueter <johannes@schlueters.de>
* @version $Id: class_contactview.php,v 1.2 2007-12-14 16:19:55 gustavo Exp $
*/
class contactview extends view {
	function contactiew() {
	  parent::view();
	}
	
	/**
	* Check wether the PHProjekt contact module is enabled
	* @ return bool
	*/
	function checkAvailability() {
	  global $adressen; // PHProjekt configuration option
	  return $adressen > 0;
	}

	function PROPFIND($options, &$files) {
		a('PROPFIND', __LINE__, $options);

		$files['files'] = array();
		$options['path'] = urldecode($options['path']);

		$current = $this->contactinfo($options['path'], $options['props']);

		if (!$current) {
			if ($current = $this->fileinfo($options['path'], $options['props'])) {
				$files['files'][] = $current;
				return true;
			}
     
			return false;
		}
        
		//$files['files'][] = $current;
        
		if (empty($options["depth"])) {
			return true;
		}
        
		if (substr($options["path"],-1) != "/") {
			$options["path"] .= "/";
		}

		$myid = $this->_path_id($options['path']);
		if ($myid == 0 && $myid !== 0) {
			return false;
		}

		foreach ($this->getSubcontacts($myid) AS $contact) {
			$files['files'][] = $this->contactinfo($options['path'].$contact, $options['props']);
		}

		// No files when at root level (no contact selected)
		if ($myid == 0) {
			return true;
		}

		foreach ($this->getContactFiles($myid) AS $file) {
			$files['files'][] = $this->fileinfo($options['path'].$file, $options['props']);
		}

		a('PROPFIND(done)', __LINE__, $files);
		return true;
	}

	/**
	* Get conatacts (folders) from contact $id
	*/
	function getSubContacts($id) {
		$retval = array();
       	        $sql = sprintf('SELECT %s FROM %s WHERE '.
                                 'parent=%u AND (acc LIKE "system" OR (gruppe=%u AND (von=%u '.
                                 'OR acc_read LIKE "group" OR acc_read LIKE "system" OR acc_read LIKE "%%%s%%"))) AND is_deleted is NULL',
				 CONTACT_COLUMN,
				 WD_TAB_CONTACTS,
				 $id,
				 $this->user['group'],
				 $this->user['id'],
				 $this->db->quote($this->user['kurz'])
				 );
		$query = $this->db->query($sql);

		while ($row = $query->get_next_row()) {
			$retval[] = $row[0];
		}
		return $retval;
	}

	/**
	* Get files associated with a contact
	*/
	function getContactFiles($contact) {
		$retval = array();

		$sql = sprintf('SELECT filename FROM %s WHERE contact=%u AND typ="f" AND ('.
			       'acc LIKE "system" OR (gruppe=%u AND (von=%u '.
                               'OR acc LIKE "group" OR acc LIKE "system" OR acc LIKE "%%%s%%"))) AND is_deleted is NULL',
		               WD_TAB_FILES,
		               $contact,
		               $this->user['group'],
			       $this->user['id'],
			       $this->user['kurz']
			       );
		$query = $this->db->query($sql);

		while ($row = $query->get_next_row()) {
			$retval[] = $row[0];
		}
		return $retval;
	}
        
	function GET($options) {
		// TODO: Konqui macht erst propfind, jenes kommt aber nicht mit Dateien klar...
        
		$file = $this->_get_file_info($options['path']);
		$fspath = $this->filedir.'/'.$file['tempname'];
		a('GET', __LINE__, $fspath);
        
		if (!file_exists($fspath)  || !$file['is_readable']) {
			$out = array('exists' => file_exists($fspath), 'readable' => $file['is_readable']);
			a('GET', __LINE__, $out);
			return false;
		}
        
		$options['mimetype'] = 'application/executable';
		$options['mtime'] = filemtime($fspath);
		$options['size'] = filesize($fspath);
		$options['stream'] = fopen($fspath, 'r');
		a('GET', __LINE__, $options);
        
		return $options;
	}
    
	function PUT(&$options) {
	  a('PUT', __LINE__, $options);
        
	  $path = substr($options['path'], 0, ($l = (int) strrpos($options['path'], '/')) +1);
        
	  // First select a contact then put a file into it so no files at "root" level
	  if ($path == '/') {
            a('PUT', __LINE__, $path);
            return '409 Conflict';
	  }
        
	  $pid  = $this->_path_id($path);
	  $name = str_replace($path, '', $options['path']);
        
	  $oldfile = $this->_get_file_info($options['path']);
        
	  a('PUT', __LINE__, $oldfile);

	  if (!is_array($oldfile)) {
	    // Check wether fileview/DEFAULT_FOLDER/<filename> exists and prevent overwriting
	    $sql = 'SELECT ID FROM '.WD_TAB_FILES.' WHERE filename="'.$name.'" AND is_deleted is NULL AND div1='.DEFAULT_FOLDER_ID;
	    $query = $this->db->query($sql);
	    if ($query->get_next_row()) {
	      return '409 Conflict';
	    }

	    $acc = $this->phprojekt->getContactRights($pid);
	    return $this->phprojekt->addFile(DEFAULT_FOLDER_ID, $name, $options['stream'], array('contact' => $pid, 'acc' => $acc));
	  } else {
	    return $this->phprojekt->updateFile($oldfile['ID'], $oldfile['tempname'], $options['stream']);
	  }
	}
    
    function MKCOL(&$options) {
        // Currently (or: forever) no new contacts from within WebDAV
        return false;
    }
    
    
    function DELETE(&$options) {
        a('DELETE', __LINE__, $options);
        $file = $this->_get_file_info($options['path']);
        a('DELETE', __LINE__, $file);
        
        if (!$file) {
        	return "404 Not found";
        }
        
        if (!$file['is_readable']) {
        	return "404 Not found";
        }
        
        $this->db->query('DELETE FROM '.WD_TAB_FILES.' WHERE ID='.(int) $file['ID']);
        
        if (!$file['is_dir']) {
        	@unlink($this->filedir.'/'.$file['tempname']);
        }
        
        return "204 No Content";
    }
    
    function MOVE(&$options) {
        $options['path'] = guessed_encoding_to_utf8($options['path']);
        $options['dest'] = guessed_encoding_to_utf8(urldecode($options['dest']));
        $dat = $this->_get_file_info($options['path']);
        $options['dest'] = str_replace(guessed_encoding_to_utf8($this->pathPrefix), '', $options['dest']);
        
        // TODO: Anderes Verzeichnis!
        
        if ($dat['path'] == $path = substr($options['dest'], 0, ($l = (int) strrpos($options['dest'], '/')) +1)) {
        	$filename = $this->db->quote(urldecode(str_replace($path, '', $options['dest'])));
        	$query = $this->db->query('UPDATE '.WD_TAB_FILES.' SET filename="'.$filename.'" WHERE ID='.$dat['ID']);
        } else {
        	return false;
        }
    }

    function contactinfo($uri, $props, $have_prefix = false) {
      a('contactinfo', __LINE__, $uri);

      if (!$have_prefix) {
        $uri = $this->pathPrefix.$uri;
      }
      $retval = array();

      $retval['path'] = htmlspecialchars(str_replace('%2F', '/', rawurlencode($uri)));
      //$retval['path'] = $uri;
      $retval['props'][] = $this->caller->mkprop('displayname',     utf8_encode(strtoupper($uri)));
      $retval['props'][] = $this->caller->mkprop('creationdate',    time());
      $retval['props'][] = $this->caller->mkprop('getlastmodified', time());

      $retval['props'][] = $this->caller->mkprop('getcontentlength', 0);
      $retval['props'][] = $this->caller->mkprop('resourcetype', 'collection');
      $retval['props'][] = $this->caller->mkprop('getcontenttype', 'httpd/unix-directory');

      return $retval;
    }

    function fileinfo($uri, $props, $have_prefix = false) {
        a('fileinfo', __LINE__, $uri);

        $retval = array();

        $path = explode('/', $uri);
        $fname = end($path);

        $file = $this->_get_file_info($uri, $props);
        if (!$file) {
        	return false;
        }
        
        if (!$have_prefix) {
        	$uri = $this->pathPrefix.$uri;
        }
        
        $retval['path'] = htmlspecialchars(str_replace('%2F', '/', rawurlencode($uri)));
        //$retval['path'] = $uri;

        $retval['props'][] = $this->caller->mkprop('displayname', utf8_encode(strtoupper($uri)));

        $retval['props'][] = $this->caller->mkprop('creationdate',    $file['cdate']);
        $retval['props'][] = $this->caller->mkprop('getlastmodified', $file['mdate']);

        if ($file['is_dir']) {
            $retval['props'][] = $this->caller->mkprop('getcontentlength', 0);
            $retval['props'][] = $this->caller->mkprop('resourcetype', 'collection');
            $retval['props'][] = $this->caller->mkprop('getcontenttype', 'httpd/unix-directory');
        } else {
            $retval['props'][] = $this->caller->mkprop('resourcetype', '');
            $retval['props'][] = $this->caller->mkprop('getcontentlength', $file['size']);
            if ($file['is_readable']) {
                $retval['props'][] =  $this->caller->mkprop('getcontenttype', get_mime_type_wd($fname));
            } else {
                $retval['props'][] = $this->caller->mkprop('getcontenttype', 'application/x-non-readable');
            }
        }
        
        return $retval;
    }
    
    /**
    * Gets the Database-ID from a path
    * @param string URI
    * @return int ID
    */
    function _path_id($uri) {
    	static $ids; // Sometimes this method could be called multiple times for an URI

    	if (isset($ids[$uri])) {
		return $ids[$uri];
    	}
    	a('URI', '', $uri);
    	if ($uri != '/') {
	    	$path = explode('/', $uri);
    		$length = sizeof($path);
    		$last = $path[$length-2];
    	} else {
    		$path = array('');
    		$length = 1;
    		$last = '';
    	}
    	
        if ($last == '') {
            $id = 0;
        } else {
    	    $sql = sprintf('SELECT ID FROM %s WHERE  %s = "%s" AND is_deleted is NULL AND gruppe=%u',
                           WD_TAB_CONTACTS,
                           CONTACT_COLUMN,
                           $this->db->quote($last),
                           $this->user['group']);

            $query = $this->db->query($sql);
    	    if ($id = $query->get_next_row()) {
                $id= $id[0];
            }
        }
    	
        return $ids[$uri] = $id;
    }
    
    /**
    * Returns details on a file or folder (collection).
    * @param string URI
    * @return array
    */
    function _get_file_info($uri) {
    	$path = substr($uri, 0, ($l = (int) strrpos($uri, '/')) +1);
    	$filename = basename($uri);
    	
    	$temp =array('uri'=>$uri, 'path'=>$path, 'l' => $l);
    	a('get_file', __LINE__, $temp);
    	
    	$pid = $this->_path_id($path);
    	
    	if ($uri == $path) {
    		// current dir
    		return array('mdate' => time(),
    	                 'cdate' => time(),
    	                 'is_dir' => true,
    	                 'is_readable' => true,
    	                 'size' => 0,
    	                 'tempname' => '');
    	} else {
    		// some file
                $sql = 'SELECT UNIX_TIMESTAMP(datum), typ, filesize, tempname, ID, pw  FROM %s WHERE '.
		       'filename="%s" AND contact=%u AND typ="f" AND '.
		       '(acc LIKE "system" OR (gruppe=%u AND (von=%u '.
    		       'OR acc LIKE "group" OR acc LIKE "system" OR acc LIKE "%%%s%%"))) AND is_deleted is NULL';
    		$sql = sprintf(
    		    $sql,
    		    WD_TAB_FILES, 
    		    $this->db->quote($filename), 
    		    $pid, 
    		    $this->user['group'], 
    		    $this->user['id'], 
    		    $this->db->quote($this->user['kurz']));
		
    		$query = $this->db->query($sql);
    		$row = $query->get_next_row();
    		if (!$row || !is_array($row)) {
    			return false;
    		}
    		
    		return array('mdate' => $row[0],
    	                 'cdate' => $row[0],
    	                 'is_dir' => $row[1] == 'd',
    	                 'is_readable' => $row[5] == '' ? true : false,
    	                 'size' => $row[2],
    	                 'tempname' => $row[3],
    	                 'path' => $path,
    	                 'parent' => $pid,
    	                 'ID' => $row[4]);
    	}
    }
}
register_view('contactview');
