<?php
require_once(WEBDAV_PATH.'class_view.php');

// $Id: class_projectview.php,v 1.2 2007-12-14 16:19:55 gustavo Exp $

/**
* Implementation of the PHProject WebDAV Prject view
* 
* @author Johannes Schlueter <johannes@schlueters.de>
* @version $Id: class_projectview.php,v 1.2 2007-12-14 16:19:55 gustavo Exp $
*/
class projectview extends view {
	function projectiew() {
	  parent::view();
	}
	
	/**
	* Check wether the PHProjekt projects module is enabled
	* @ return bool
	*/
	function checkAvailability() {
	    global $projekte; // PHProjekt configuration option
        return $projekte > 0;
    }
    
	function PROPFIND($options, &$files) {
		$files['files'] = array();
		$options['path'] = guessed_encoding_to_utf8($options['path']);

		/*$current = $this->projectinfo($options['path'], $options['props']);

		if (!$current) {
			echo "!\$current\n";
			if ($current = $this->fileinfo($options['path'], $options['props'])) {
				$files['files'][] = $current;
				return true;
			}
     
			return false;
		}
        */
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

		foreach ($this->getSubprojects($myid) AS $project) {
			$files['files'][] = $this->projectinfo($options['path'].$project, $options['props']);
		}

		// No files when at root level (no project selected)
		if ($myid == 0) {
			return true;
		}

		foreach ($this->getProjectFiles($myid) AS $file) {
			$files['files'][] = $this->fileinfo($options['path'].$file, $options['props']);
		}

		return true;
	}

	/**
	* Get subprojects (folders) from project $id
	*/
	function getSubprojects($id) {
		$retval = array();

		if ($this->phprojektVersion < 4.2) {
		  $sql = sprintf('SELECT name FROM %s WHERE parent=%u AND gruppe=%u AND is_deleted is NULL',
				 WD_TAB_PROJECTS,
				 $id,
				 $this->user['group']
				 );
		} else { 
		  $sql = sprintf('SELECT name FROM %s WHERE '.
                                 'parent=%u AND (acc LIKE "system" OR (gruppe=%u AND (von=%u '.
                                 'OR acc LIKE "group" OR acc LIKE "system" OR acc LIKE "%%%s%%"))) AND is_deleted is NULL',
				 WD_TAB_PROJECTS,
				 $id,
				 $this->user['group'],
				 $this->user['id'],
				 $this->db->quote($this->user['kurz'])
				 );
		}
		
		$query = $this->db->query($sql);

		while ($row = $query->get_next_row()) {
			$retval[] = $row[0];
		}
		return $retval;
	}

	/**
	* Get files associated with an project
	*/
	function getProjectFiles($project) {
		$retval = array();

		$sql = sprintf('SELECT filename FROM %s WHERE div2=%u AND typ="f" AND ('.
			       'acc LIKE "system" OR (gruppe=%u AND (von=%u '.
                               'OR acc LIKE "group" OR acc LIKE "system" OR acc LIKE "%%%s%%"))) AND is_deleted is NULL',
		               WD_TAB_FILES,
		               $project,
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
        
		return $options;
	}
    
	function PUT(&$options) {
	  $path = substr($options['path'], 0, ($l = (int) strrpos($options['path'], '/')) +1);
        
	  // First select a project then put a file into it so no files at "root" level
	  if ($path == '/') {
            return '409 Conflict';
	  }
        
	  $pid  = $this->_path_id($path);
	  $name = str_replace($path, '', $options['path']);
        
	  $oldfile = $this->_get_file_info($options['path']);
        
	  if (!is_array($oldfile)) {
	    // Check wether fileview/DEFAULT_FOLDER/<filename> exists and prevent overwriting
	    $sql = 'SELECT ID FROM '.WD_TAB_FILES.' WHERE filename="'.$this->db->quote($name).'" AND div1='.DEFAULT_FOLDER_ID;
	    $query = $this->db->query($sql);
	    if ($query->get_next_row()) {
	      return '409 Conflict';
	    }

	    if ( $this->phprojektVersion<4.2) {
	      return $this->phprojekt->addFile(DEFAULT_FOLDER_ID, $name, $options['stream'], array('project' => $pid));
	    } else {
	      $acc = $this->phprojekt->getProjectRights($pid);
	      return $this->phprojekt->addFile(DEFAULT_FOLDER_ID, $name, $options['stream'], array('project' => $pid, 'acc' => $acc));
	    }
	  } else {
	    return $this->phprojekt->updateFile($oldfile['ID'], $oldfile['tempname'], $options['stream']);
	  }
        }
    
    function MKCOL(&$options) {
        // Currently (or: forever) no new projects from within WebDAV
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
        a('MOVE', __LINE__, $options);
        $options['path'] = guessed_encoding_to_utf8($options['path']);
        $options['dest'] = guessed_encoding_to_utf8(urldecode($options['dest']));
        $dat = $this->_get_file_info($options['path']);
        $options['dest'] = str_replace(guessed_encoding_to_utf8($this->pathPrefix), '', $options['dest']);
        
        // TODO: Anderes Verzeichnis!
        
        if ($dat['path'] == $path = substr($options['dest'], 0, ($l = (int) strrpos($options['dest'], '/')) +1)) {
            $filename = urldecode(str_replace($path, '', $options['dest']));
            $query = $this->db->query('UPDATE '.WD_TAB_FILES.' SET filename="'.$this->db->quote($filename).'" WHERE ID='.$dat['ID']);
        } else {
            return false;
        }
    }

    function projectinfo($uri, $props, $have_prefix = false) {
        $uri = guessed_encoding_to_utf8($uri);
        if (!$have_prefix) {
       	    $uri = $this->pathPrefix.$uri;
        }
        $retval = array();

        // url encode everything but slashes - so you can't have a slash in your names...
        $retval['path'] = htmlspecialchars(str_replace('%2F', '/', rawurlencode($uri)));
        //$retval['props'][] = $this->caller->mkprop('displayname',     strtoupper($uri));
        $retval['props'][] = $this->caller->mkprop('creationdate',    time());
        $retval['props'][] = $this->caller->mkprop('getlastmodified', time());

        $retval['props'][] = $this->caller->mkprop('getcontentlength', 0);
        $retval['props'][] = $this->caller->mkprop('resourcetype', 'collection');
        $retval['props'][] = $this->caller->mkprop('getcontenttype', 'httpd/unix-directory');

        return $retval;
    }

    function fileinfo($uri, $props, $have_prefix = false) {
        $retval = array();

        $path = explode('/', $uri);
        foreach ($path as $key => $value) {
            $path[$key] = guessed_encoding_to_iso_8859_1(urldecode($value));
        }
        $fname = end($path);

        $file = $this->_get_file_info($uri, $props);
        if (!$file) {
            return false;
        }
        
        if (!$have_prefix) {
            $uri = $this->pathPrefix.$uri;
        }
        
        $fname =$this->filedir.'/'.$file['tempname'];
        
        $retval['path'] = htmlspecialchars(str_replace('%2F', '/', rawurlencode($uri)));

        //$retval['props'][] = $this->caller->mkprop('displayname', strtoupper($uri));

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
                $retval['props'][] = $this->caller->mkprop('getcontenttype', get_mime_type_wd($fname));
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
            $ids[$uri] = 0;
        } else {
        	$ids[$uri] = $this->phprojekt->getProjectIDByName($last);
        }
    	
        return $ids[$uri];
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
                $sql = 'SELECT UNIX_TIMESTAMP(datum), typ, filesize, tempname, ID, pw FROM %s WHERE '.
		       'filename="%s" AND div2=%u AND typ="f" AND '.
		       '(acc LIKE "system" OR (gruppe=%u AND (von=%u '.
    		       'OR acc LIKE "group" OR acc LIKE "system" OR acc LIKE "%%%s%%"))) AND is_deleted is NULL';
		$sql = sprintf($sql, WD_TAB_FILES, $this->db->quote($filename), $pid, $this->user['group'], $this->user['id'], $this->db->quote($this->user['kurz']));
		
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
register_view('projectview');
