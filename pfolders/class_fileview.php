<?php
require_once(WEBDAV_PATH.'class_view.php');

/**
* File/Folder View
* @version $Id: class_fileview.php,v 1.4 2008-02-10 09:37:00 michele Exp $
*/
class fileview extends view {    
    var $parentField = "div1";

    /**
    * Constructor
    *
    * initializes the WebDAV-System and sets some base properties
    *
    * @access public
    */
    function fileview() {
      parent::view();

      if (defined('PHPR_VERSION')) {
        $this->parentField = 'parent';
      }
    }
    
    /**
    * Check wether PHProjekt file manager is available
    * This check is already done elsewhere so this method alwaysreturns true
    * @return bool true
    */
    function checkAvailability() {
        return true;
    }
    
    function PROPFIND($options, &$files) {
        a('PROPFIND', __LINE__, $options['path']);
        
        $current = $this->fileinfo($options['path'], $options['props']);
        if (!$current) {
        	a('PROPFIND', __LINE__, $current);
            return false;
        }
        
        //$options['path'] = urldecode($options['path']);
        
        $files['files'] = array();
        //$files['files'][] = $current;
        
        
        if (empty($options["depth"]))  {
            return true;
        }
        
        if (substr($options["path"],-1) != "/") {
            $options["path"] .= "/";
        }

        $rows = array();
        $sql = 'SELECT filename FROM '.WD_TAB_FILES.
                                         ' WHERE '.$this->parentField.' = '.
                                         (int) $this->_path_id($options['path']).
                                         ' AND (typ = "f" OR typ = "d") '.
				         'AND (acc LIKE "system" OR (gruppe='.(int) $this->user['group'].
                                         ' AND (von='.(int) $this->user['id'].
                                         ' OR acc LIKE "group" OR acc LIKE "system" OR acc LIKE "%'.$this->user['kurz'].'%"))) AND is_deleted is NULL';
        $query = $this->db->query($sql);
        if (!$query) {
            return "500 Internal Server Error";
        }

        while ($row = $query->get_next_row()) {
        	$rows[] = $row;
        }
        
        foreach ($rows AS $current) {
        	$files['files'][] = $this->fileinfo($options['path'].$current[0], $options['props']);
        }
        
        return true;
    }
        
    function GET($options) {        
        $file = $this->_get_file_info($options['path']);
        a('GET', __LINE__, $file);
        $fspath = $this->filedir.'/'.$file['tempname'];
        a('GET', __LINE__, $fspath);
        
        if (!file_exists($fspath)  || !$file['is_readable']) {
        	a('GET', __LINE__, array(file_exists($fspath), $file['is_readable']));
        	return false;
        }
        
        $options['path'] = $this->pathPrefix.$options['path'];
        $options['mimetype'] = 'application/executable';
        $options['mtime'] = filemtime($fspath);
        $options['size'] = filesize($fspath);
        $options['stream'] = fopen($fspath, 'r');
        a('GET', __LINE__, $options);
        
        return $options;
    }
    
    function PUT(&$options) {
        a('PUT', __LINE__, $options);
        
        $dir = $this->filedir.'/';
        $fname = '';
        
        $path = substr($options['path'], 0, ($l = (int) strrpos($options['path'], '/')) +1);
        $pid  = $this->_path_id($path);
        $name = str_replace($path, '', $options['path']);
        
        $oldfile = $this->_get_file_info($options['path']);
        
        a('PUT', __LINE__, $oldfile);
        
        if (!is_array($oldfile)) {
        	return $this->phprojekt->addFile($pid, $name, $options['stream']);
        } else {
        	return $this->phprojekt->updateFile($oldfile['ID'], $oldfile['tempname'], $options['stream']);
        }
    }
    
	function MKCOL(&$options) {
		// TODO: wenn keine Rechte auf parent: 403 Forbidden
		a('MKCOL', __LINE__, $options);

		$path = substr($options['path'], 0, ($l = (int) strrpos($options['path'], '/')) +1);
		$filename = str_replace($path, '', $options['path']);
		$pid = $this->_path_id($path);

		if ($pid === false) {
			a('MKCOL', __LINE__, $pid);
			return '409 Confilct';
		}

		$sql = sprintf('INSERT INTO %s (von, filename, acc, datum, filesize, gruppe, typ, %s) VALUES(%u, "%s", "%s", "%s", 0, %u, "d", %u)',
                       WD_TAB_FILES,
            		   $this->parentField,
            		   $this->user['id'],
            		   $filename,
            		   addslashes($this->phprojekt->getFolderRights($pid)),
            		   date('YmdHis'),
            		   $this->user['group'],
            		   $pid);

		if (!$this->db->query($sql)) {
			return '500 Internal Server Error';
		}

		return true;
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
    	// variable to set a new parent in database if nesesary 
    	$setparent = '';
        $options['path'] = guessed_encoding_to_utf8($options['path']);
        // If the SCRIPT_NAME is set than the dest_url not exist but the dest exist 
        if (isset($options['dest_url'])) {
            $options['dest'] = guessed_encoding_to_utf8(urldecode($options['dest_url']));
        } else {
            $options['dest'] = guessed_encoding_to_utf8(urldecode($options['dest']));
        }
        $dat = $this->_get_file_info($options['path']);
        
        $l = (int) strrpos($options['dest'], '/');
        $path = substr($options['dest'], 0, $l+1);
        $filename = urldecode(str_replace($path, '', $options['dest']));
        
        // Add follow lines to get the parent Ids for moving from one parent to a other.
        $destpid = $this->_path_id($path);
        if ($destpid == '') $destpid = 0;
        $l = (int) strrpos($options['origpath'], '/');
        $sourcepid = $this->_path_id(substr($options['origpath'], 0, $l+1));
        if ($sourcepid == '') $sourcepid = 0;
        
        //if the parent is different set the new parent id
        if ( $destpid != $sourcepid ) {
        	$setparent = ',parent='.$destpid;
        }
        
        if (!$this->db->query('UPDATE '.WD_TAB_FILES.' SET filename="'.$filename.'"'.$setparent.' WHERE ID='.$dat['ID'])) {
            return false;
        }
        
        return true;
    }

    function fileinfo($uri, $props, $have_prefix = false) {
        $retval = array();
        
        $path = explode('/', $uri);
        $fname = end($path);
        $file = $this->_get_file_info($uri, $props);
        if (!$file) {
            return false;
        }
        
        $fsname = $this->filedir.'/'.$file['tempname'];

        if (!$have_prefix) {
            $uri = $this->pathPrefix.$uri;
            a('fileinfo', __LINE__, array($this->pathPrefix, $uri));
        }
        
        $retval['path'] = htmlspecialchars(str_replace('%2F', '/', rawurlencode($uri)));

        //$retval['props'][] = $this->caller->mkprop('displayname', utf8_encode(strtoupper($uri)));

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
                $retval['props'][] = $this->caller->mkprop('getcontenttype', get_mime_type_wd($fsname));
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
            // if the Path Array is shorter then 2 (Only in the Root directory) Set last to ''
            if($length >= 2) {
                $last = $path[$length-2];
            } else {
                $last = '';
            }
    	} else {
            $path = array('');
            $length = 1;
            $last = '';
    	}
    	
        if ($last == '') {
            $id = 0;
        } else {
            $sql = 'SELECT ID FROM %s WHERE filename="%s" AND '.
                 '(acc LIKE "system" OR (gruppe=%u AND (von=%u '.
       	         'OR acc LIKE "group" OR acc LIKE "system" OR acc LIKE "%%%s%%"))) AND is_deleted is NULL';
            $sql = sprintf($sql, WD_TAB_FILES, $last, $this->user['group'], $this->user['id'], $this->user['kurz']);
            $query = $this->db->query($sql);
            if ($id = $query->get_next_row()) {
                $id = $id[0];
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
            // working dir
            return array('mdate' => time(),
                         'cdate' => time(),
                         'is_dir' => true,
                         'is_readable' => true,
                         'size' => 0,
                         'tempname' => '');
        } else {
            // some file
            $query = $this->db->query('SELECT UNIX_TIMESTAMP(datum), typ, filesize, tempname, ID, pw FROM '.WD_TAB_FILES.' WHERE filename = "'.$filename.'" AND '.$this->parentField.' = '.(int) $pid.' AND (typ = "f" OR typ = "d")'.
                                          ' AND (von='.(int) $this->user['id'].
                                          ' OR (gruppe='.(int) $this->user['group'].
                                          ' AND (acc LIKE "group" OR acc LIKE "system" OR acc LIKE "%'.$this->user['kurz'].'%")))');

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
register_view('fileview');
