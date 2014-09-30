<?php
/**
* Wrapper to manipulate PHProjekt data
* @version $Id: class_phprojekt.php,v 1.4 2008-02-17 13:54:26 gustavo Exp $
*/
class phprojekt {
    /**
    * Database-Object
    */    
    var $db;
    
    /**
    * Settings for the current User
    */
    var $user;
    
    
    /**
    * Location of the PHProjekt upload folder
    */
    var $dir;
    

    /**
     * PHProjekt version
     */
    var $version;

    /**
    * Our Constructor
    * @param object Reference to DB-Object
    * @param array User data
    * @param string Filemanager files dir
    */
    function phprojekt(&$db, $user = null, $dir = null) {
        global $version; // From PHProjekt configuration

        $this->db   = &$db;
        $this->user = $user;
        $this->dir  = $dir;
        $this->version = $version;
    }
    
    /**
    * Sets the user data when not set in the constructor
    * @var array $user
    * @return void
    */
    function setUser($user) {
    	$this->user = $user;
    }
    
    /**
    * Add a new file to the PHProjekt filemanager
    * @param int ID of the parent folder
    * @param string Name of the file
    * @param resource Data Stream from HTTP_WebDAV_Server's $options-Array
    * @param array ('project' => Project ID, 'contact' => Contact ID, 'acc' = Access rights)
    * @return string HTTP/WebDAV-Status-Code
    */
    function addFile($pid, $name, $stream, $options = array()) {
        $fname = '';
        $dir = $this->dir.'/';
        
        // Create a random "scrambeled" file name
        while (file_exists($dir.$fname)) {
            srand((double)microtime()*1000000);
        	$char = "123456789abcdefghijkmnopqrstuvwxyzABCDEFGHIJKLMANOPQRSTUVWXYZ";
        	while (strlen($fname) < 12) { $fname .= substr($char,(rand()%(strlen($char))),1); }
        }
        	
        // Create file and prepare writing
        $fp = fopen($dir.$fname , 'w');
        if(!is_resource($fp) || !is_resource($stream)) {
             return '409 Conflict';
        }
        
        // Write the data to our file
        while(!feof($stream)) {
        	fwrite($fp, fread($stream, 4096));
        }
        fclose($fp);
        
        $project = isset($options['project']) ? $options['project'] : 0;
        $contact = isset($options['contact']) ? $options['contact'] : 0;

        $acc = isset($options['acc']) ? $options['acc'] : $this->getFolderRights($pid);
        $acc_write = isset($options['acc_write']) ? $options['acc_write'] : $this->getFolderWriteRights($pid);
        
        // Register the file in the database
        $sql = sprintf('INSERT INTO '.WD_TAB_FILES.'
                                   (ID, von, filename,    acc, acc_write, datum, filesize, gruppe, tempname,  typ, parent, div2, version, contact) 
                            VALUES (%s,  %u,     "%s", \'%s\',    \'%s\',  "%s",       %u,     %u,     "%s", "%s",     %u,   %u,       0,      %u)',
                       $this->db->null,
                       $this->user['id'],
                       $this->db->quote($name),
                       $this->db->quote($acc),
                       $this->db->quote($acc_write),
                       date('YmdHis'),
                       filesize($dir.$fname),
                       $this->user['group'],
                       $fname,
                       'f',
                       $pid,
                       $project,
	                   $contact
                       );
        if ($this->db->query($sql)) {
            return '201 Created';
        } else {
            return '409 Conflict';
        }
    }
    
    /**
    * Updates the file
    * @param int ID of the item
    * @param string "scrambled" file name
    * @param resource Open Stream to new content
    * @return string HTTP(WebDAV) Status code
    */
    function updateFile($id, $fname, $stream) {
        $dir = $this->dir.'/';
        
        // Open file and prepare writing from beginning
        $fp = fopen($dir.$fname , 'w');
        if(!is_resource($fp) || !is_resource($stream)) {
             return '409 Conflict';
        }
        
        // Write the data to our file
        while(!feof($stream)) {
        	fwrite($fp, fread($stream, 4096));
        }
        fclose($fp);
        
        // Update data in the Database
        $sql = sprintf('UPDATE '.WD_TAB_FILES.' SET datum="%s", filesize=%u WHERE ID=%s',
        	           date('YmdHis'),
        	           filesize($dir.$fname),
        	           $id);
        
        $this->db->query($sql);

        // Return success (Yes, 204 No content)
        return '204 No Content';
    }
    
    /**
    * Delete a file or directory
    * @param int ID of the dataset
    * @fname string name of the scrambeled file on the server or NULL if the "file" is a directory
    */
    function deleteFile($id, $fname = NULL) {
        $this->db->query('DELETE FROM '.WD_TAB_FILES.' WHERE ID='.(int) $id);
        
        if (!$fname) {
        	@unlink($this->dir.'/'.$fname);
        }
        
        return "204 No Content";
    }
    
    /**
    * Get the rights for an element at the PHProjekt file manager
    * @param int ID of the Element
    * @return string acc (normal 'private' or 'group')
    */
    function getFolderRights($id) {
        if ($id == 0) {
            return DEFAULT_ACC;
        }
        
        return $this->getRights(WD_TAB_FILES, $id);
    }
    
    /**
     * Get the write rights for an element at the PHProjekt file manager
     *
     * @param int $id of the Element
     * @return string acc_write (normal '' or 'w')
     */
    function getFolderWriteRights($id) {
    	if ($id == 0) {
    		return DEFAULT_ACC_WRITE;
    	}
    	
    	return $this->getWriteRights(WD_TAB_FILES, $id);
    }

    function getProjectRights($id) {
        if ($id == 0 || $this->version<4.2){
            return false;
        }

        return $this->getRights(WD_TAB_PROJECTS, $id);
    }

    function getContactRights($id) {
      if ($id == 0) {
        return false;
      }

      return $this->getRights(WS_TAB_CONTACTS, $id);
    }

    /**
     * Get the rights of a element
     *
     * @param string $table Tablename
     * @param int $id of a Element
     * @return string acc (normal 'private' or 'group')
     */
    function getRights($table, $id) {
        $sql = 'SELECT acc FROM '.$table.' WHERE ID='.(int) $id;
        $query = $this->db->query($sql);
        if ($query && $row = $query->get_next_row()) {
            return $row[0];
        } else {
            return false;
        }
    }
    
    /**
     * Get the Write access rights for a element
     *
     * @param string $table tablename
     * @param int $id of the element
     * @return string acc_write (normal '' or 'w')
     */
    function getWriteRights($table, $id) {
    	$sql = 'SELECT acc_write FROM '.$table.' WHERE ID='.(int) $id;
    	$query = $this->db->query($sql);
    	if ($query && $row = $query->get_next_row()) {
    		return $row[0];
    	} else {
    		return false;
    	}
    }
    
    function getProjectIDByName($name) {
		$last = guessed_encoding_to_iso_8859_1($last);
    	$sql = sprintf('SELECT ID FROM %s WHERE  name = \'%s\' AND gruppe=%u',
                           WD_TAB_PROJECTS,
                           $this->db->quote($name),
                           $this->user['group']);
        $query = $this->db->query($sql);
    	if ($id = $query->get_next_row()) {
            return $id[0];
    	} else {
    		return false;
    	}
    }
    
    /**
    * Convert a group name to corresponding ID
    * This function returns the id of the selected group or
    * 0 if no group was selected ($name == '') and false
    * if the current user isn't a member of the group or 
    * the group dosen't exist
    * @var string name of the group
    * @return int
    */
    function groupNameToID($name) {
    	if ($name == '') {
    		return 0;
    	}
    	$sql = sprintf('SELECT %1$s.ID '.
    	                   'FROM %1$s '.
    	                   'INNER JOIN %2$s ON grup_ID = %1$s.ID '.
    	                   'WHERE %1$s.name="%3$s" AND user_ID=%4$u', 
    	               WD_TAB_GROUPS,
    	               WD_TAB_GROUUSER,
    	               $this->db->quote(guessed_encoding_to_iso_8859_1($name)),
    	               $this->user['id']);
    	$query = $this->db->query($sql);
    	$row = $query->get_next_row();
    	if (!$row) {
    		return false;
    	}
    	
    	return utf8_encode($row[0]);
    }
    
    /**
    * Get all groups the user belongs to
    * @return array
    */
    function getGroups() {
    	$retval = array();

    	$sql = sprintf('SELECT %1$s.ID, %1$s.name FROM %2$s INNER JOIN %1$s ON grup_ID = %1$s.ID WHERE user_ID=%3$u',
    	               WD_TAB_GROUPS,
    	               WD_TAB_GROUUSER,
    	               $this->user['id']);
    	$query = $this->db->query($sql);
    	while ($row = $query->get_next_row()) {
    		$retval[$row[0]] = utf8_encode($row[1]);
    	}
    	return $retval;
    }

}
?>
