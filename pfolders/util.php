<?php
/**
* This files holds some functions used at different places
* or without a "real" connection to other parts...
* @version $Id: util.php,v 1.1 2007-10-29 08:46:34 gustavo Exp $
*/

/**
* Crypt function used to check the passowr dat login
*
* This function was taken from phprojekt/lib/crypt.inc.php
* which disappered with PHProjekt 5.0
*/
if (!function_exists('encrypt')) {
    function encrypt($password, $saltstring) {
        $salt = substr($saltstring, 0, 2);
        $enc_pw = crypt($password, $salt);
        return $enc_pw;
    }
}

/**
* Register a view
* This function should be run from inside an View-File. It calls the
* view's checkAvailability method. If the view is available it is added
* to the global $views array
* @globals $views
* @param string name Name of the view class
* @return void
*/
function register_view($name) {
    global $views;
    if (call_user_func(array($name, 'checkAvailability'))) {
        $views[] = $name;
    }
}


/**
* Page shown when called using a web-browser.
* @param string Error-Message
* @param object webdav Calling WebDAV-Instance or empty when called from procedural code
*/
function show_help($failure = NULL, $caller = NULL) {
    global $version, $db_type; // From PHProjekt
    global $views;

    $documentation = file_exists('help/index.html') ? 'help/index.html' : false;
    	
    echo '<?xml version="1.0" encoding="us-ascii"?>'.
         '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">';
    printf('<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en"><head><title>WebDAV for PHProjekt %s</title>',
           WD_VERSION);

    echo '</head><body>'.
         '<h1>PFolders - WebDAV for PHProjekt '.WD_VERSION.' info page</h1>';
    	
    if (is_object($caller) && !isset($caller->user['id'])) {
        echo '<div style="border: solid red 1px;"><p><b>Login don\'t work!</b></p>
              <p>Please check your settings and disable this Addon - it might be a security issue!</p></div>';
    } elseif (!empty($failure)) {
        echo '<p style="border: solid red 1px;"><b>Error:</b> '.$failure.'</p>';
    } else {
        echo '<p style="border:solid green 1px;"><b>WebDAV for PHProjekt seems to be installed correct.</b><br/>
              WebDAV-Adress: '.
              (isset($_SERVER['HTTPS']) ? 'https://' : 'http://').
              $_SERVER['HTTP_HOST'].
              $_SERVER['SCRIPT_NAME'].'/ (guessed)</p>';
    }
    	
    echo '<p>When reporting failures please share this system information:<br/>
    Server: '.$_SERVER['SERVER_SOFTWARE'].'<br/>
    PHP: '.PHP_VERSION.'/'.PHP_SAPI.'/'.PHP_OS.'<br/>
    PHProjekt: '.$version.'/'.$db_type.'<br/>
    WebDAV for PHProjekt: '.WD_VERSION.'<br/>
    Registered views: '.implode(', ', $views).'</p>
        
    <p><b>Please use a WebDAV-Client for using this addon.</b><br/>
    Configuration steps for some common clients can be found in the '.
    ($documentation ? '<a href="'.$documentation.'">' : '').
    'documentation'.($documentation ? '</a>' : '').'.</p>
        
    <!-- p>The WebDAV for PHProjekt homepage is at <a 
    href="http://phprojektwebdav.sourceforge.net/">http://phprojektwebdav.sourceforge.net/</a>.</p -->
        
    </body></html>';

    exit;
}
    
/**
* Returns mime-type of a file
* Depending on the server configuration it either uses mime_content_type()
* or fileinfo. Ifthese are not available it uses popen + file if this won't
* work it falls back to application/octet-stream
* @param string filename
* @return string mime-type
*/
function get_mime_type_wd($filename) {
    /*
    * @var resource fileinfo init ifleinfo only once
    */
    static $fileinfo;
    
    $retval = NULL;
    
    do { // Simulate goto with  do { break; } while(0)
        if (function_exists('mime_content_type')) {
            $retval = @mime_content_type($filename);
            if ($retval) {
            	break;
            }
        }
    
        if (extension_loaded('fileinfo')) {
            if (!$fileinfo) {
                $fileinfo = finfo_open(FILEINFO_MIME);
            }
            $retval = finfo_file($fileinfo, $filename);
            break;
        }
    
        if (executable_file_exists()) {
            $fp = popen("file -i ".escapeshellarg($filename)." 2>/dev/null", "r");
            $reply = fgets($fp);
            pclose($fp);

            if (strpos($reply, "$filename: ") == 0) {
                $reply = substr($reply, strlen($filename)+2);
            }
            $retval = $reply;
            break;
        } else {
        	$retval = 'application/octet-stream';
        }
    } while(0);
    
    $retval = trim($retval);
    if (ereg("^[[:alnum:]_-]+/[[:alnum:]_-]+;?.*", $retval, $matches)) {
        return $retval;
    } else {
        return 'application/octet-stream';
    }
}

/**
* Check wether an executable "file" is available
* @return bool
*/
function executable_file_exists() {
    static $result = null;
    
    if ($result !== null) {
        return $result;
    }
    $result = false;
    
    $path = getenv("PATH");
    
    if (!strncmp(PHP_OS, "WIN", 3)) {
        $exts = array(".exe", ".com");
    } else {
        $exts = array("");
    }

    foreach (explode(PATH_SEPARATOR, $path) as $dir) {
        if (!@is_dir($dir)) {
            continue;
        }
        foreach ($exts as $ext) {
            if (function_exists('is_executable') && @is_executable("$dir/file".$ext)) {
                $result = true;
                return $result;
            }
        }        
    }
    return $result;
}

/**
* Debugging function
* Holds some logging functions or something like that,
* should be emtpy for a release.
*/
function a($ort = null, $line = null, $var = null) {
	return true;
	$log = debug_backtrace();
	for ($i = 0; $i < count($log); $i++) {
		unset($log[$i]['object']);
	}
	if ($ort === null) {
		return error_log(print_r(array($log[1], $log[2], $log[3]), true));
	} elseif ($line === null) {
		$var = $ort;
		$ort = '';
		if (isset($log[1]['file'])) $ort = str_replace('/data/www/htdocs/phprojekt53_beta/', '', $log[1]['file']);
		if (isset($log[1]['function'])) $ort .= ' '.$log[1]['function'];
		$line = '';
		if (isset($log[1]['line'])) $line = $log[1]['line'];
	}
	return error_log("$ort ($line):\n".print_r($var, true));
}

?>
