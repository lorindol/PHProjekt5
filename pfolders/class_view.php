<?php
/**
* View base class
* @abstract
* @version $Id: class_view.php,v 1.1 2007-10-29 08:46:34 gustavo Exp $
*/
class view {
  var $caller;
  var $db;
  var $phprojekt;
  var $filedir;
  var $user = array('id' => 0, 'group'=>0);
  var $pathPrefix;
  var $phprojektVersion;

  /**
   * Constructor
   */
  function view() {
    global $version; // From PHProjekt configuration

    $this->phprojektVersion = $version;
  }

  /**
   * Populate osme important variables used in most views in most casses
   * @param class webdav Reference to the calling webdav instance
   * @param class db Reference to the DB object
   * @param array User-data
   * @param string Prefix of the Path
   */
  function populate(&$caller, &$db, $filedir, $user, $pathPrefix) {
    $this->caller     = &$caller;
    $this->db         = &$db;
    $this->filedir    = $filedir;
    $this->user       = $user;
    $this->pathPrefix = $pathPrefix;
    	
    $this->phprojekt = new phprojekt($db, $user, $filedir);
  }
    
  /**
   * Checks wether the view is available
   * This Method should be overridden
   * @abstract
   * @return bool
   */
  function checkAvailability() {
    return false;
  }
}
?>
