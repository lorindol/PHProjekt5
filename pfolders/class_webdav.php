<?php
// $Id: class_webdav.php,v 1.4 2008-02-10 09:37:00 michele Exp $

require_once(WEBDAV_PATH.'class_db.php');
require_once('_parse_report.php');

if (!include_once('HTTP/WebDAV/Server.php')) {
    define('WEBDAV_NOT_FOUND', 1);
    class HTTP_WebDAV_Server {}
}

/**
* Implementation of the PEAR:HTTP_WebDAV_Server class
* 
* This class extends the PEAR WebDAV-Server class of
* Hartmut Holzgraefe and handles the requests by
* analyzing the group a user uses, identifying the
* selected view and call the handler function or
* offers selection for group and view.
*
* @author Johannes Schlueter <johannes@schlueters.de>
* @version $Id: class_webdav.php,v 1.4 2008-02-10 09:37:00 michele Exp $
*/
class webdav extends HTTP_WebDAV_Server {
    /**
    * Instance of the primitive wrapper over the DB abstraction
    * @var object
    */
    var $db;
    
    /**
    * PHProjekt configuration option used to tell where the
    * PHProjekt files are stored on the disc (sometime this
    * property might disappear - if I manage to move it out here
    * all code using this variable should be in der phprojekt class
    * @var string
    */
    var $filedir;
    
    /**
    * Array holding the users ID and Group
    * array('id' => int, 'group' => int)
    * @var array
    */
    var $user = array('id' => 0, 'group'=>0);
    
    /**
    * First part of the Path-String, containing group and
    * selected view
    * This part needs to be part of any path returned to the user
    * @var string
    */
    var $pathPrefix;
    
    /**
    * PHProjekt Wrapper
    */
    var $phprojekt;
    
    /**
    * Instance of the handler class
    * @var object
    */
    var $handler;
    
    var $namespaces = array();
    
    /**
    * Constructor
    *
    * initializes the WebDAV-System and sets some base properties
    *
    * @access public
    */
    function webdav() {
        global $dateien;
        
        parent::HTTP_WebDAV_Server();
        $this->http_auth_realm = 'WebDAV for PHProjekt';
        
        $this->db = &new db_conn();
        $this->filedir = $dateien;
        
        $this->phprojekt = new phprojekt($this->db, $this->user, NULL);
    }
    
    /**
     * POST method handler.
     *
     * @param  void
     * @return boolean  TRUE or FALSE
     */
    function http_POST() {
        $options = array();
    	$options['path'] = $this->path;
    	$options['data'] = file_get_contents('php://input');
    	$response = array();
    	$this->POST($options, $response);
    	$xml = '<?xml version="1.0" encoding="utf-8" ?>
<C:response xmlns="DAV:" xmlns:C="urn:ietf:params:xml:ns:caldav">
<C:response>
<C:request-status>2.0;Success</C:request-status>'
.$this->buildXmlProp($response[0]['props'], '  ').'
</C:response>
</C:response>
';
    	$etag = md5($xml);
        header("ETag: \"$etag\"");
    	$this->http_status("200 OK");
        header('Content-Type: text/xml; charset="utf-8"');
        echo $xml;
    	return true;
    }
    
    /**
     * POST method handler
     *
     * @param array $options    options to be applied
     * @param array $response   reference to the variable holding the result
     * @return boolean          TRUE or FALSE
     */
    function POST($options, &$response) {
    	$options['path'] = $this->splitPath($options['path']);
        if ($options['path'] == false) {
            return false;
        }
        if (method_exists($this->handler, 'POST')) {
           return $this->handler->POST($options, $response);
        } else {
            a('not defined for '.get_class($this->handler));
        }
    	return false;
    }
    
    /**
     * OPTIONS method handler
     * 
     * @param void
     * @return void
     */
    function http_OPTIONS() 
    {
        // Microsoft clients default to the Frontpage protocol 
        // unless we tell them to use WebDAV
        header("MS-Author-Via: DAV");

        // get allowed methods
        $allow = $this->_allow();
        // dav header
        $dav = array(1, 'calendar-schedule');        // assume we are always dav class 1 compliant
        if (isset($allow['LOCK'])) {
            $dav[] = 2;         // dav class 2 requires that locking is supported 
        }

        // tell clients what we found
        $this->http_status("200 OK");
        header("DAV: "  .join(", ", $dav));
        header("Allow: ".join(", ", $allow));

        header("Content-length: 0");
    }
    
    /**
    * Checks wether the user is known to the system or not
    *
    * @param string Type of authentification
    * @param string Submitted username
    * @param string Submitted password
    * @retturn bool
    * @access protected
    */
    function check_auth($type, $user, $pw) {
        global $pw_crypt, $login_kurz, $ldap; // PHProjekt options ...

        if ($user == '' || $pw == '') {
            return false;
        }
    	
    	if (!$login_kurz) {
            $field_name = "nachname";
        } elseif ($login_kurz == "1") {
            $field_name = "kurz";
        } elseif (($login_kurz == "2") || ($ldap == "1")) {
            $field_name="loginname";
        }

        if (!ini_get('magic_quotes_gpc')) {
            $user = addslashes($user);
            $pass = addslashes($pw);
        }

        $query = $this->db->query('SELECT ID, pw, gruppe, kurz FROM '.WD_TAB_USER. ' WHERE '.$field_name.'="'.$this->db->quote($user).'"');
        if (!$query) {
            return false;
        }
        $row = $query->get_next_row();
    	
        $enc_pw = (PHPR_PW_CRYPT == '1') ? md5('phprojektmd5'.$pw) : $pw;
        $enc_pw_old = (PHPR_PW_CRYPT == '1') ? md5($pw) : $pw;

        if ($row[1] != $enc_pw) {
          if ($row[1] != $enc_pw_old || $enc_pw == '') return false;
        }
    	
        $this->user['id']    = $row[0];
        $this->user['group'] = $row[2];
        $this->user['kurz']  = $row[3];
        
        $this->phprojekt->setUser($this->user);
    
        return true;
    }
    
    function PROPFIND($options, &$files) {
    	global $views;
        $options['path'] = guessed_encoding_to_utf8(urldecode($options['path']));
        
        $path = $this->splitPath($options['path']);
        if ($path == false) {
            return false;
        }
        $result = false;
        foreach($options['props'] as $prop) {
        	$prop = webdav::getMethodName($prop);
        	foreach ($views as $view) {
        		if (method_exists($view, $prop)) {
        			$result = call_user_func_array(array($view, $prop), array($options, &$files, $this)) || $result;
        		}
        	}
        }
        if ($result) {
        	return $result;
        }
        
        // At root-level so show group-list
        if (!$path['group']) {
            $files['files'] = $this->getGroups($options);
            return true;
        }
        
        // Selected group but no view - so select view
        if (!$path['view']) {
            $files['files'] = $this->getViews($options);
            return true;
        }
        
        $options['path'] = str_replace($this->pathPrefix, '', $options['path']);
        // Now call the handler and return it's return value     
        return $this->handler->PROPFIND($options, $files);
    }
    
    function GET(&$options) {
        if ($options['path'] == '/') {
        	show_help(NULL, $this);
        	exit;
        }
        
        $retval = $this->callHandler('GET', $options);
        if ($retval) {
            $options = $retval;
            $retval =true;
        }
        return $retval;
    }
    
    /**
    * Split the path into group, view and the path inside the view
    * @param string some path
    * @return array
    */
    function splitPath($path) {
   	    @list(,$group, $view, $path) = explode('/', $path, 4);
   		
   	    $group_id = $this->phprojekt->groupNameToID($group);
   	    if ($group_id === false) {
   	        return false;
   	    }
   		
   	    $this->user['group'] = $group_id;
   		
   	    if ($group) {
   	        $this->pathPrefix = '/'.$group;
   	    }
        if ($view) {
   	        if (class_exists($view)) {
   	            $this->handler = &new $view();
   	            if (!is_subclass_of($this->handler, 'view') || !$this->handler->checkAvailability() ) {
   	                 return false;
   	            }
   	        }
   	        $this->pathPrefix .= '/'.$view;
   	        $this->handler->populate($this, $this->db, $this->filedir, $this->user, $this->pathPrefix);
   	    }

   	    $path = (string) $path;
   
        return array(
   	        'group' => $group_id,
   	        'group_name' => $group,
   	        'view'  => $view,
   	        'path'  => '/'.$path);
    }
        
    function getViews() {
        global $projekte, $adressen; // From the PHProjekt configuration file
        global $views;
        
    	$retval = array();
    	$retval[] = $this->getVirtualDirInfo($this->pathPrefix);
    	
    	foreach ($views AS $view) {
    	    $retval[] = $this->getVirtualDirInfo($this->pathPrefix.'/'.$view.'/');
    	}
    	
    	return $retval;
    }
    
    function getVirtualDirInfo($group) {
    	$retval = array();
    	$uri = $group;
    	$retval['path'] = $uri;

        $retval['props'][] = $this->mkprop('displayname', strtoupper($uri));

        $retval['props'][] = $this->mkprop('creationdate',    time());
        $retval['props'][] = $this->mkprop('getlastmodified', time());

        $retval['props'][] = $this->mkprop('getcontentlength', 0);
        $retval['props'][] = $this->mkprop('resourcetype', 'collection');
        $retval['props'][] = $this->mkprop('getcontenttype', 'httpd/unix-directory');
        $retval['props'][] = $this->mkprop('principal-collection-set',
                $this->mkprop('href', $uri)
        );
        return $retval;
    }
    
    /**
    * calls the handler for a specific method   
    * This method wraps the call a bit so so common preprocessing of
    * $otions is done at one place
    * @param string The name of the requested method
    * @param array Options
    */
    function callHandler($method, &$options) {
        $dbg = array(get_class($this->handler), $method);
        
        $options['path'] = urldecode($options['path']);
        // Add the origpath to the options to make 
        //  moves form one to a other directory posible
        $options['origpath'] = $options['path'];
        $path = $this->splitPath($options['path']);
        $method = array(&$this->handler, $method);
        
        // Outside a special view you can't do most things ;-)
        if (!$path || !$path['group'] || !$path['view']) {
            return false;
        }
        $options['path'] = $path['path'];
        
        return call_user_func($method, $options);
    }

    function getGroups($options) {
    	$retval = array();
    	$retval[] = $this->getVirtualDirInfo('/');
    	
    	foreach ($this->phprojekt->getGroups() AS $option) { 
    		$retval[] = $this->getVirtualDirInfo('/'.$option);	
    	}
    	return $retval;
    }
    
    // The following are quite thin layers between the WebDAV-Class and the handler
    // I'd like touset method call overloading - but theWebDAV class woudn't recognize
    // the functions...
    
    function PUT(&$options) {
        return $this->callHandler('PUT', $options);
    }
    
    function MKCOL(&$options) {
        return $this->callHandler('MKCOL', $options);
    }    

    function DELETE(&$options) {
        return $this->callHandler('DELETE', $options);
    }
    
    /**
     * Handler to move or rename a entry 
     *
     * @param array $options
     * @return object
     */
    function MOVE(&$options) {
    	if ($options['path'] == '/') {
        	show_help(NULL, $this);
        	exit;
        }
        return $this->callHandler('MOVE', $options);
    }

    // {{{ http_PROPFIND() 

    /**
     * PROPFIND method handler.
     * Overriding HTTP_WebDAV_Server::http_PROPFIND to make use of webdav::buildXmlProp
     * 
     * @Override    HTTP_WebDAV_Server::http_PROPFIND
     *
     * @param  void
     * @return void
     */
    function http_PROPFIND() 
    {
        $options = Array();
        $files   = Array();

        $options["path"] = $this->path;
        
        // search depth from header (default is "infinity)
        if (isset($this->_SERVER['HTTP_DEPTH'])) {
            $options["depth"] = $this->_SERVER["HTTP_DEPTH"];
        } else {
            $options["depth"] = "infinity";
        }       

        // analyze request payload
        $propinfo = new _parse_propfind("php://input");
        if (!$propinfo->success) {
            $this->http_status("400 Error");
            return;
        }
        $options['props'] = $propinfo->props;

        // call user handler
        if (!$this->PROPFIND($options, $files)) {
            $files = array("files" => array());
            if (method_exists($this, "checkLock")) {
                // is locked?
                $lock = $this->checkLock($this->path);

                if (is_array($lock) && count($lock)) {
                    $created          = isset($lock['created'])  ? $lock['created']  : time();
                    $modified         = isset($lock['modified']) ? $lock['modified'] : time();
                    $files['files'][] = array("path"  => $this->_slashify($this->path),
                                              "props" => array($this->mkprop("displayname",      $this->path),
                                                               $this->mkprop("creationdate",     $created),
                                                               $this->mkprop("getlastmodified",  $modified),
                                                               $this->mkprop("resourcetype",     ""),
                                                               $this->mkprop("getcontenttype",   ""),
                                                               $this->mkprop("getcontentlength", 0))
                                              );
                }
            }

            if (empty($files['files'])) {
                $this->http_status("404 Not Found");
                return;
            }
        }
        
        // collect namespaces here
        $ns_hash = array();
        
        // Microsoft Clients need this special namespace for date and time values
        $ns_defs = "xmlns:ns0=\"urn:uuid:c2f41010-65b3-11d1-a29f-00aa00c14882/\"";    
    
        // now we loop over all returned file entries
        foreach ($files["files"] as $filekey => $file) {
            
            // nothing to do if no properties were returend for a file
            if (!isset($file["props"]) || !is_array($file["props"])) {
                continue;
            }
            
            // now loop over all returned properties
            foreach ($file["props"] as $key => $prop) {
                // as a convenience feature we do not require that user handlers
                // restrict returned properties to the requested ones
                // here we strip all unrequested entries out of the response
                
                switch($options['props']) {
                case "all":
                    // nothing to remove
                    break;
                    
                case "names":
                    // only the names of all existing properties were requested
                    // so we remove all values
                    unset($files["files"][$filekey]["props"][$key]["val"]);
                    break;
                    
                default:
                    $found = false;
                    
                    // search property name in requested properties 
                    foreach ((array)$options["props"] as $reqprop) {
                        if (   $reqprop["name"]  == $prop["name"] 
                               && @$reqprop["xmlns"] == $prop["ns"]) {
                            $found = true;
                            break;
                        }
                    }
                    
                    // unset property and continue with next one if not found/requested
                    if (!$found) {
                        $files["files"][$filekey]["props"][$key]="";
                        continue(2);
                    }
                    break;
                }
                
                // namespace handling 
                if (empty($prop["ns"])) continue; // no namespace
                $ns = $prop["ns"]; 
                if ($ns == "DAV:") continue; // default namespace
                if (isset($ns_hash[$ns])) continue; // already known

                // register namespace 
                $ns_name = "ns".(count($ns_hash) + 1);
                $ns_hash[$ns] = $ns_name;
                $ns_defs .= " xmlns:$ns_name=\"$ns\"";
            }
        
            // we also need to add empty entries for properties that were requested
            // but for which no values where returned by the user handler
            if (is_array($options['props'])) {
                foreach ($options["props"] as $reqprop) {
                    if ($reqprop['name']=="") continue; // skip empty entries
                    
                    $found = false;
                    
                    // check if property exists in result
                    foreach ($file["props"] as $prop) {
                        if (   $reqprop["name"]  == $prop["name"]
                               && @$reqprop["xmlns"] == $prop["ns"]) {
                            $found = true;
                            break;
                        }
                    }
                    
                    if (!$found) {
                        if ($reqprop["xmlns"]==="DAV:" && $reqprop["name"]==="lockdiscovery") {
                            // lockdiscovery is handled by the base class
                            $files["files"][$filekey]["props"][] 
                                = $this->mkprop("DAV:", 
                                                "lockdiscovery", 
                                                $this->lockdiscovery($files["files"][$filekey]['path']));
                        } else {
                            // add empty value for this property
                            $files["files"][$filekey]["noprops"][] =
                                $this->mkprop($reqprop["xmlns"], $reqprop["name"], "");

                            // register property namespace if not known yet
                            if ($reqprop["xmlns"] != "DAV:" && !isset($ns_hash[$reqprop["xmlns"]])) {
                                $ns_name = "ns".(count($ns_hash) + 1);
                                $ns_hash[$reqprop["xmlns"]] = $ns_name;
                                $ns_defs .= " xmlns:$ns_name=\"$reqprop[xmlns]\"";
                            }
                        }
                    }
                }
            }
        }
        
        // now we generate the reply header ...
        $this->http_status("207 Multi-Status");
        header('Content-Type: text/xml; charset="utf-8"');
        
        // ... and payload
        echo "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";
        echo "<D:multistatus xmlns:D=\"DAV:\">\n";
        
        foreach ($files["files"] as $file) {
            // ignore empty or incomplete entries
            if (!is_array($file) || empty($file) || !isset($file["path"])) continue;
            $path = $file['path'];                  
            if (!is_string($path) || $path==="") continue;

            echo " <D:response $ns_defs>\n";
        
            /* TODO right now the user implementation has to make sure
             collections end in a slash, this should be done in here
             by checking the resource attribute */
            $href = $this->_mergePathes($this->_SERVER['SCRIPT_NAME'], $path);
        
            echo "  <D:href>$href</D:href>\n";
        
            // report all found properties and their values (if any)
            if (isset($file["props"]) && is_array($file["props"])) {
                echo "   <D:propstat>\n";
                echo "    <D:prop>\n";

                foreach ($file["props"] as $key => $prop) {
                    
                    if (!is_array($prop)) continue;
                    if (!isset($prop["name"])) continue;
                    
                    if (!isset($prop["val"]) || $prop["val"] === "" || $prop["val"] === false) {
                        // empty properties (cannot use empty() for check as "0" is a legal value here)
                        if ($prop["ns"]=="DAV:") {
                            echo "     <D:$prop[name]/>\n";
                        } else if (!empty($prop["ns"])) {
                            echo "     <".$ns_hash[$prop["ns"]].":$prop[name]/>\n";
                        } else {
                            echo "     <$prop[name] xmlns=\"\"/>";
                        }
                    } else if ($prop["ns"] == "DAV:") {
                        // some WebDAV properties need special treatment
                        switch ($prop["name"]) {
                        case "creationdate":
                            echo "     <D:creationdate ns0:dt=\"dateTime.tz\">"
                                . gmdate("Y-m-d\\TH:i:s\\Z", $prop['val'])
                                . "</D:creationdate>\n";
                            break;
                        case "getlastmodified":
                            echo "     <D:getlastmodified ns0:dt=\"dateTime.rfc1123\">"
                                . gmdate("D, d M Y H:i:s ", $prop['val'])
                                . "GMT</D:getlastmodified>\n";
                            break;
                        case "resourcetype":
                            echo "     <D:resourcetype><D:$prop[val]/></D:resourcetype>\n";
                            break;
                        case "supportedlock":
                            echo "     <D:supportedlock>$prop[val]</D:supportedlock>\n";
                            break;
                        case "lockdiscovery":  
                            echo "     <D:lockdiscovery>\n";
                            echo $prop["val"];
                            echo "     </D:lockdiscovery>\n";
                            break;
                        default:                                    
                            echo "     <D:$prop[name]>"
                                . $this->buildXmlProp(($prop['val']), '       ')
                                ."     </D:$prop[name]>\n";                               
                            break;
                        }
                    } else {
                        // properties from namespaces != "DAV:" or without any namespace 
                            echo "     <$prop[name] xmlns=\"\">"
                                . $this->buildXmlProp($prop['val'], '       ')
                                ."     </$prop[name]>\n";                               
                    }
                }

                echo "   </D:prop>\n";
                echo "   <D:status>HTTP/1.1 200 OK</D:status>\n";
                echo "  </D:propstat>\n";
            }
       
            // now report all properties requested but not found
            if (isset($file["noprops"])) {
                echo "   <D:propstat>\n";
                echo "    <D:prop>\n";

                foreach ($file["noprops"] as $key => $prop) {
                    if ($prop["ns"] == "DAV:") {
                        echo "     <D:$prop[name]/>\n";
                    } else if ($prop["ns"] == "") {
                        echo "     <$prop[name] xmlns=\"\"/>\n";
                    } else {
                        echo "     <" . $ns_hash[$prop["ns"]] . ":$prop[name]/>\n";
                    }
                }

                echo "   </D:prop>\n";
                echo "   <D:status>HTTP/1.1 404 Not Found</D:status>\n";
                echo "  </D:propstat>\n";
            }
            
            echo " </D:response>\n";
        }
        
        echo "</D:multistatus>\n";
    }
    
    /**
     * REPORT method handler
     * Overriding HTTP_WebDAV_Server::http_REPORT to make use of webdav::buildXmlProp
     * 
     * @Override    HTTP_WebDAV_Server::http_REPORT
     *
     * @param  void
     * @return void
     */
    function http_REPORT() 
    {
        $options = Array();
        $files   = Array();
       
        // analyze request payload
        $report = new _parse_report("php://input");
        if (!$report->success) {
            $this->http_status("400 Error");
            return;
        }
        $options = $report->pointer;
        $options->path = $this->path;
        unset($report);

        // call user handler
        if (!$this->REPORT($options, $files)) {
            $files = array("files" => array());

            if (empty($files['files'])) {
                $this->http_status("404 Not Found");
                return;
            }
        }
        
        // collect namespaces here
        $ns_hash = array(
            'C:' => 'C'
        );
        
        $ns_defs = "xmlns=\"urn:ietf:params:xml:ns:caldav\"";
    
        // now we generate the reply header ...
        
        $props = $options->get('D:prop');
        $props = $props[0]->get_childtags();
        $xml = $this->multistatus($files['files'], $props);
        
        $etag = md5($xml);
        header("ETag: \"$etag\"");

        $this->http_status("207 Multi-Status");
        header('Content-Type: text/xml; charset="utf-8"');
        echo $xml;
    }


    /**
     * Get the response to a REPORT-Request
     *
     * @param array $options    options to be applied
     * @param array $files      reference to the variable holding the result
     * @return boolean          TRUE or FALSE
     */
    function REPORT($options, &$files) {
        $options->path = guessed_encoding_to_utf8(urldecode($options->path));
        
        $path = $this->splitPath($options->path);
        if ($path == false) {
            return false;
        }
        
        // At root-level so show group-list
        if (!$path['group']) {
            $files['files'] = $this->getGroups($options);
            return true;
        }
        
        // Selected group but no view - so select view
        if (!$path['view']) {
            $files['files'] = $this->getViews($options);
            return true;
        }
        
        $options->set_path(str_replace($this->pathPrefix, '', $options->path));
        // Now call the handler and return it's return value     
        return $this->handler->REPORT($options, $files);
    }


    /**
     * Builds the XML-representation of a prop-element
     *
     * @param array $element    prop-element
     * @param string $indent    indention to prefix each line with
     * @return string           xml-representation of the prop
     */
    function buildXmlProp($element, $indent = '') {
        if (!is_array($element)) {
            $element = $this->_prop_encode(htmlspecialchars($element));
            if (strpos($element, "\n") && strpos($element, '<![CDATA[') !== 0) {
                $element = sprintf('<![CDATA[%s]]>', $element);
            }
            return $element;
        }
        if (!isset($element['val']) || $element['val'] === '' || $element === false) {
            $noval = true;
        } else {
            $noval = false;
        }
        $nl = "\n";
        $attr = '';
        $ns = '';
        if ($element["ns"]=="DAV:") {
            $ns = 'D:';
        } else if (!empty($element['ns'])) {
            $ns = $element['ns'];
        } else {
            $attr = ' xmlns=""';
        }
        $ret = $nl.$indent.'<'.$ns.$element['name'].$attr.($noval?'/':'').">";
        if (!isset($element['val']) || !is_array($element['val'])) {
            $indent2 = '';
        } else {
        	$indent2 = $indent;
        }
        if ($noval) {
            return $ret;
        }
        $ret .= $this->buildXmlProp($element['val'], $indent2)
              . $indent2.'</'.$ns.$element['name'].">".$nl.$indent;
        return $ret;
    }
    
    /**
     * Translates a XML-Tag into a String that only contains characters
     * allowed in method names. The returned value can be used to call a
     * function that handles the request. 
     * 
     * @param String $tag   Tag name that should be translated
     * @return String       the translated string
     */
    function getMethodName($tag) {
    	if (is_array($tag) && isset($tag['name'])) {
    		$tag = $tag['name'];
    	}
    	$tag = explode(':', $tag);
        $tag = strtolower(array_pop($tag));
        $tag = strtr($tag, array(
          '-' => '_',
        ));
        return $tag;
    }
    
    /**
     * Ensures that an url ist absolute. If it's relative it is prefixed
     * with domainname and path
     *
     * @param string $path  the path/uri that should be checked 
     * @return string       the abolute url
     */
    function absoluteUri($path) {
    	if (preg_match('#^(mailto|https?|ftp):#i', $path)) {
    		return $path;
    	}
    	$uri = (@$this->_SERVER["HTTPS"] === "on" ? "https:" : "http:");
        $uri .= '//'.$this->_SERVER['HTTP_HOST'].$this->_SERVER['SCRIPT_NAME'];
        if ($this->pathPrefix) {
        	$pathPrefix = explode('/', $this->pathPrefix);
        	$uri = $this->_mergePathes($uri, $pathPrefix[1]);
        }
        $path = htmlspecialchars(str_replace('%2F', '/', rawurlencode($path)));
        return $this->_mergePathes($uri, $path);
    }
    
    /**
     * Builds an <href> prop-element to be parsed by WebDav::buildXmlProp
     *
     * @param string $path  path to the 
     * @return array        href-prop as an array
     */
    function mkHref($path) {
        return $this->mkprop('href', $this->absoluteUri($path));
    }
    
    /**
     * Builds a response-element to be parsed by function multistatus
     *
     * @param string $url   the request-url of the props
     * @param array $props  the props that should be represented
     * @return array        response as an array
     */
    function mkResponse($url, $props) {
    	return array(
            'path' => $this->absoluteUri($url),
            'props' => $props,
        );
    }
    
    /**
     * Builds the multistatus-response to a request in XML-format. 
     *
     * @param array $responses  Array with the props that should be returned
     * @return string           the response in xml-format
     */
    function multistatus($responses) {
    	$ret = '<?xml version="1.0" encoding="utf-8" ?>
<multistatus xmlns="DAV:" xmlns:D="DAV:" xmlns:C="urn:ietf:params:xml:ns:caldav">';
    	if (is_array($responses)) {
        foreach ($responses as $response) {
        	$ret .= '
  <response>
    <href>'.$response['path'].'</href>
    <propstat>
';
        	foreach ($response['props'] as $prop) {
        		$ret .= '      <prop>'
        		      . $this->buildXmlProp($prop, '        ')
                      . '</prop>
';
        	}
            $ret .='      <status>HTTP/1.1 200 OK</status>
    </propstat>
  </response>
';
        }
    	}
        $ret .= '</multistatus>';
    	return $ret;
    }

    /**
     * Calculate the etag for all kind of objects 
     *
     * @param mixed $object     Object, Array, ... to calculate the etag for
     * @return String           etag for the object
     */
    function getetag($object) {
    	return md5(print_r($object, true)."++");
    }
    
    /**
     * helper for property element creation
     *
     * @param  string  XML namespace (optional)
     * @param  string  property name
     * @param  string  property value
     * @return array   property array
     */
    function mkprop() 
    {
        $args = func_get_args();
        if (count($args) == 3) {
        	if ($args[0] == 'D:') $args[0] = 'DAV:';
            return array("ns"   => $args[0], 
                         "name" => $args[1],
                         "val"  => $args[2]);
        } else {
            return array("ns"   => "DAV:", 
                         "name" => $args[0],
                         "val"  => $args[1]);
        }
    }
} 


?>
