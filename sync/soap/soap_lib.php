<?php
/**
 * @package    soap
 * @subpackage main
 * @author     Albrecht Guenther, $Author: albrecht $
 * @licence    GPL, see www.gnu.org/copyleft/gpl.html
 * @copyright  2000-2006 Mayflower GmbH www.mayflower.de
 * @version    $Id: soap_lib.php,v 1.26.2.4 2007/08/02 10:09:00 albrecht Exp $
 */

// catch all suspicious output
error_reporting(0);
ini_set('display_errors', 'Off');
ob_start();

require_once('./config.inc.php');
require_once('./func.inc.php');
setErrorFile();

if (APP_USER_ERROR) {
    $old_error_handler = set_error_handler('userErrorHandler');
    //trigger_error('test error');
}

startSyncLog();

// give the script enough time to run...
set_time_limit(3600);

error_reporting(E_ALL & ~E_NOTICE);
//error_reporting(0);

// avoid session start in lib.inc.php

define('soap_request', '1');

// is there already a pear installed?

// ini_set('include_path', dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR.'pear'.PATH_SEPARATOR.ini_get('include_path'));


// misc pre-defines (global vars)
$module    = '';
define('PATH_PRE','../../');
$sync_data = array();

// termine = calendar / todo = todos
$format['termine']  = array( '', 'event', 'remark', 'anfang', 'ende' , 'visi'); // note2 can be html
$format['contacts'] = array( '', 'nachname', 'vorname', 'firma', 'tel1', 'tel2', 'fax', 'mobil', //
                                 'land', 'stadt', 'plz', 'strasse', 'url', 'email', 'anrede' );
$format['notes']    = array( '', 'name', 'remark' ); // remark can be html
$format['todo']     = array( '', 'remark', 'note', 'anfang', 'deadline', 'progress', 'acc', 'datum');// remark can be html

$visi2vcal = array(2=>'PUBLIC', 1=>'CONFIDENTIAL', 0=>'PRIVATE');
$vcal2visi = array_flip($visi2vcal);

$acc2vcal = array('system'=>'PUBLIC', 'group'=>'PERSONAL', 'private'=>'CONFIDENTIAL');
//$vcal2acc = array_flip($acc2vcal);
$vcal2acc = array('PUBLIC'=>'group', 'PERSONAL' => 'group', 'private' => 'CONFIDENTIAL');




/**
 * Throws a soap fault and exits
 *
 * @param string $message Error message
 * @param string $detail Some more extensive info.
 */
function soapFaultDie($message, $detail='') {
    if ( (isset($__ENV['SERVER_PORT'])) && ($_ENV['SERVER_PORT'] != 80) ) {
       $port = sprintf(':%d', $__ENV['SERVER_PORT']);
    } else {
        $port = '';
    }

    $uri = sprintf('%s://%s%s%s',
       (isset($_SERVER['HTTPS']) && (strtolower($_SERVER['HTTPS'])==='on')) ? 'https' : 'http',
       $_SERVER['SERVER_NAME'],
       $port,
       $_SERVER['REQUEST_URI']);

    $fault = sprintf('<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/"
              soap:encodingStyle="http://schemas.xmlsoap.org/soap/
                                  encoding/">
  <soap:Body>
     <soap:Fault>
        <faultcode>Server</faultcode>
        <faultstring><![CDATA[%s]]></faultstring>
        <faultactor><![CDATA[%s]]></faultactor>
        <detail><![CDATA[\n%s\n]]>
        </detail>
     </soap:Fault>
  </soap:Body>
</soap:Envelope>', htmlentities($message), htmlentities($uri) , $detail);
    header('Status: 500 -');
    header('Server: PHProjekt-SOAP');
    header('Content-Type: text/xml; charset=UTF-8');
    header('Content-Length: '.strlen($fault));
    echo $fault;
    ob_end_flush();
    // Over and out
    die();
}



/**
 * Escapes HTML in a CDATA block
 *
 * @param string $str
 * @return string
 */
function escapeHTML($str) {
    if (strip_tags($str) != $str) {
        $str = sprintf("<![CDATA[\n%s\n]]>",$str);
    }
    return $str;
}



/**
 * Main Sync module, this routine does the actual work for the sync functions
 *
 * @param string $namespace
 * @param array $exchangeset
 * @param string $loginstring
 * @param string $user_pw
 * @param string $sync_date
 * @param boolean $delete
 * @return unknown
 */
function syncModule($namespace, &$exchangeset, $loginstring, $user_pw, $sync_date, $delete, $private) {
    // we have to global all this stuff to figure out what rights the user has
    global $user_ID, $user_acc, $sql_user_group, $user_group, $sync_data, $module, $format;

    appDebug(array(__FUNCTION__, __LINE__, "(namespace $namespace, exchangeset ".var_export($exchangeset,true).", loginstring ".var_export($loginstring,true).", passwort ".var_export($user_pw,true).", sync_date $sync_date)\n"), 4);

    @include_once(PATH_PRE.'lib/lib.inc.php');

    if (APP_DEBUG_LEVEL & 128) {
        outputEnvironmentData();
    }

    $sync_data = array();
    $user_acc = getPhprojektAccess($private);
    $sync_date = date("YmdHis", $sync_date);
    appDebug(array(__FUNCTION__, __LINE__, "[$module][get] - User-ID: $user_ID - User: $loginstring - ".
                    "Password: $user_pw - Syncdate: $sync_date\n\$exchangeset: ".var_export($exchangeset, true)."\n"), 4);
    $pimIDs = array();
    if (is_array($exchangeset)) {
        // mehrere elemente werden als array übergeben...
        foreach ($exchangeset as $data) {
            if (is_object($data) && isset($data->Element)) $data=$data->Element;
            $record = array();
            foreach ($format[$module] as $key=>$val) {
                if (is_object($data[$key])) {
                    $tmpobj = &$data[$key];
                    $record[] = $tmpobj->value;
                } else {
                    $record[] = $data[$key];
                }
            }
            $pimIDs[] = "'".$record[0]."'";
            // nachschauen ob record bereits gesynct wurde
            checkSyncmode($record, $sync_date);
        }
    } else if (is_object($exchangeset)) {
        // ein einzelnes element wird aber als objekt übergeben...
        $tmpobj = &$exchangeset;
        $record = $tmpobj->ItemAryElement;
        $pimIDs[] = "'".$record[0]."'";
        checkSyncmode($record, $sync_date);
    }
    if ($delete) deleteData($pimIDs, $sync_date);
    $ary = preparePimData($namespace, $sync_date);
    return sendXML($namespace, $ary);
}


/**
 * Synchronizes calendar data
 *
 * @see syncModule
 * @param array $exchangeset
 * @param string $username
 * @param string $password
 * @param string $syncdate
 * @param boolean $delete
 * @return boolean
 */
function SyncCalendar(&$exchangeset, $username, $password, $syncdate, $delete, $private) {
    global $module;
    appDebug(__FUNCTION__.":".__LINE__.": SyncCalendar(&$exchangeset, $username, $password, $syncdate, $delete)", 4);
    $module = 'termine';
    return syncModule('SyncCalendar', $exchangeset, $username, $password, $syncdate, $delete, $private);
}

/**
 * Synchronizes contact data
 *
 * @see syncModule
 * @param array $exchangeset
 * @param string $username
 * @param string $password
 * @param string $syncdate
 * @param boolean $delete
 * @return boolean
 */
function SyncContacts(&$exchangeset, $username, $password, $syncdate, $delete, $private) {
    global $module;
    $module = 'contacts';
    return syncModule('SyncContacts', $exchangeset, $username, $password, $syncdate, $delete, $private);
}

/**
 * Synchronizes notes data
 *
 * @see syncModule
 * @param array $exchangeset
 * @param string $username
 * @param string $password
 * @param string $syncdate
 * @param boolean $delete
 * @return boolean
 */
function SyncNotes(&$exchangeset, $username, $password, $syncdate, $delete, $private) {
    global $module;
    $module = 'notes';
    return syncModule('SyncNotes', $exchangeset, $username, $password, $syncdate, $delete, $private);
}

/**
 * Synchronizes todo data
 *
 * @see syncModule
 * @param array $exchangeset
 * @param string $username
 * @param string $password
 * @param string $syncdate
 * @param boolean $delete
 * @return boolean
 */
function SyncTodos(&$exchangeset, $username, $password, $syncdate, $delete, $private) {
    global $module;
    $module = 'todo';
    return syncModule('SyncTodos', $exchangeset, $username, $password, $syncdate, $delete, $private);
}

/**
 * get list of projects a users can read
 *
 * @param string $loginstring
 * @param string $user_pw
 * @return string
 */
function ListProjects( $loginstring, $user_pw) {
    global $user_ID, $user_group, $user_acc, $dbIDnull, $dbTSnull, $version;
    global $user_kurz, $sql_user_group, $sync_data, $module, $format, $user_email;

    appDebug(array(__FUNCTION__, __LINE__, "(namespace $namespace, exchangeset ".var_export($exchangeset,true).", loginstring ".var_export($loginstring,true).", passwort ".var_export($user_pw,true).", sync_date $sync_date)\n"), 4);

    @include_once(PATH_PRE.'lib/lib.inc.php');

    if (APP_DEBUG_LEVEL & 128) {
        outputEnvironmentData();
    }



    $module='projekte';

    $user_acc = getPhprojektAccess();	

    appDebug(array(__FUNCTION__, __LINE__, "[$module][get] - User-ID: $user_ID - User: $loginstring - ".
                    "Password: $user_pw\n\$exchangeset: ".var_export($exchangeset, true)."\n"), 4);

    @include_once(PATH_PRE.'lib/dbman_lib.inc.php');

    appDebug(array(__FUNCTION__, __LINE__, "[$module][get] - User-ID: $user_ID - User: $loginstring - ".
                    "Password: $user_pw\n\$exchangeset: ".var_export($exchangeset, true)."\n"), 4);

    if (APP_DEBUG_LEVEL & 128) {
        outputEnvironmentData();
    }


    $sync_date = date("YmdHis", $sync_date);


    $where = "(acc like 'system' or ((von = ".(int)$user_ID." or acc like 'group' or acc like '%\"$user_kurz\"%') and $sql_user_group)) $where";

    $query="select ID, name from ".DB_PREFIX.$tablename['projects']." where $where";
    $result = db_query($query);

    appDebug(array(__FUNCTION__, __LINE__,'Projects query '.$query.' results in '.var_export($row,true)),8);

    $ary=array();
    while ($data = db_fetch_row($result)) {
        if (is_array($data)) {
            $ary[]=array($data[0], strtr($data[1], array( '&' => '&amp;',
                                                      '<' => '&lt;',
                                                      '>' => '&gt;' )));
        }
    }
    return sendXML('ListProjects', $ary);
    exitSync();
}




/**
 * Sets a new entry into the timecard
 *
 * @param array $ExchangeSet
 * @param string $Username
 * @param string $Password
 * @param string $syncdate
 * @param boolean $delete
 */
function SetTimeCard($exchangeset, $loginstring, $user_pw, $syncdate, $delete) {

    global $user_ID, $user_kurz, $sql_user_group, $module;
    global $user_group, $user_acc, $dbIDnull, $dbTSnull, $version;
    global $sql_user_group, $sync_data, $module, $format, $user_email;

    appDebug(array(__FUNCTION__, __LINE__, "[$module][get] - User-ID: $user_ID - User: $loginstring - ".
                    "Password: $user_pw\n\$exchangeset: ".var_export($exchangeset, true)."\n"), 4);

    @include_once(PATH_PRE.'lib/lib.inc.php');
    //@include_once(PATH_PRE."timecard/timecard_date.inc.php");
    check_role("timecard");

    foreach ($exchangeset as $key=>$data) {
        if (is_object($data) && isset($data->Element)) {
            $exchangeset[$key] = $data->Element;
        }
    }

    foreach ($exchangeset as $entry) {
        // First insert into timecard table
        $project    = $entry[0];
        $datum      = $entry[1];
        $time       = $entry[2];
        $hour       = $entry[3];
        $minute     = $entry[4];
        $note       = $entry[5];
        $start      = $entry[6];
        $end        = $entry[7];
        $query = "INSERT INTO ".DB_PREFIX."timeproj 
                              (        users    ,        projekt  ,        datum    ,        h,             m,               note ) 
                       VALUES (".(int)$user_ID.",".(int)$project.",'".xss($datum)."',".(int)$hour.",".(int)$minute.", '".xss($note)."') ";

        db_query($query) or db_die();
        $sqlstring = xss("insert into ".DB_PREFIX."timecard
                                 (users,              datum,  anfang)
                          values (".(int)$user_ID.",'$datum', ".(int)$time.")");

        $result = db_query($sqlstring) or db_die();
    }
    return sendXML('SetTimeCard', null);
}


/**
 * Synchronizes ids between pim and phprojekt
 *
 * @param array$exchangeset
 * @param string $loginstring
 * @param string $user_pw
 * @return void
 */
function SyncResync(&$exchangeset, $loginstring, $user_pw) {
    global $user_ID, $user_group, $user_acc, $dbIDnull, $dbTSnull;
    global $user_kurz, $sql_user_group, $sync_data, $module, $format;

    include_once(PATH_PRE.'lib/lib.inc.php');
    appDebug(array(__FUNCTION__, __LINE__, "[$module][get] - User-ID: $user_ID - User: $loginstring - ".
                    "Password: $user_pw\n\$exchangeset: ".var_export($exchangeset, true)."\n"), 4);
    if (is_array($exchangeset)) {
        // mehrere elemente werden als array übergeben...
        // array wandeln
        foreach ($exchangeset as $key=>$data) {
            if (is_object($data)  && isset($data->Element)) {
                $exchangeset[$key] = $data->Element;
            }
        }

        // update sync ids
        updateSyncIds($exchangeset);
    } else if (is_object($exchangeset)) {
        // ein einzelnes element wird aber als objekt übergeben...
        $tmpobj = &$exchangeset;
        $record[0] = $tmpobj->ItemAryElement;
        updateSyncIds($record);
    }
    // send this data-empty xml-def back and exit to make sync.exe happy...
    return sendXML('SyncResync', '');
    exitSync();
}




/**
 * Check type of sync and call correct sync function
 *
 * @param array $pim_record
 * @param string $sync_date
 */
function checkSyncmode($pim_record, $sync_date) {
    global $module, $format, $user_ID, $user_group, $sync_data, $user_acc;

    $syncID = $pim_record[0];
    
    $module = qss($module);
    
    // fetch sync record
    $query = "SELECT ".DB_PREFIX."sync_rel.ID, phprojekt_ID, modified,
                     ".DB_PREFIX.$module.".sync2, sync_checksum
                FROM ".DB_PREFIX."sync_rel, ".DB_PREFIX.$module."
               WHERE phprojekt_module='$module'
                 AND modified > $sync_date
                 AND sync_ID='$syncID'
                 AND ".DB_PREFIX.$module.".ID=phprojekt_ID
                 AND ".DB_PREFIX."sync_rel.user_ID='$user_ID'";
    appDebug(array(__FUNCTION__, __LINE__, "SQL query:\n$query\n"), 8);
    $result = db_query($query);
    $row = db_fetch_row($result);
    $PHProjekt_ID  = $row[1];
    $last_sync     = $row[2];
    $phprojekt_mod = $row[3];
    $sync_checksum = $row[4];
    $pim_checksum  = createSyncChecksum($pim_record, 1);

    // now check whether insert or update at all

    if ($row[0] > 0) {
        //
        // 1. case: match
        //
        if ($last_sync >= $phprojekt_mod && $pim_checksum==$sync_checksum) {
            // skip if last sync is newer than or equal the phprojekt modification(s)
            //      and pim data == db data (checksum)
            appDebug(array(__FUNCTION__, __LINE__, "[$module] - data equal: nothing to sync from pim to phprojekt."), 1);
        } else if ($last_sync >= $phprojekt_mod && $pim_checksum != $sync_checksum &&
                   in_array($PHProjekt_ID, $user_acc['write'])) {
            // update phprojekt data if last sync is newer than or equal the phprojekt modification(s)
            //        and pim data != db data (checksum)
            //        and user is allowed to change the entry
            appDebug(array(__FUNCTION__, __LINE__, "[$module] - pim data is newer: sync from pim to phprojekt."), 1);
            updatePhprojektData($PHProjekt_ID, $pim_record);
        } else if ($last_sync < $phprojekt_mod) {
            // update sync data if last sync is older than phprojekt modification(s)
            appDebug(array(__FUNCTION__, __LINE__, "[$module] - phprojekt data is newer: sync from phprojekt to pim."), 1);
            $phprojekt_values = fetchPhprojektValues($PHProjekt_ID);
            $phprojekt_values[1] = $syncID;
            $sync_data['mod'][] = $phprojekt_values;
            // define new $pim_checksum
            $pim_checksum = createSyncChecksum($phprojekt_values, 2);
        }
        // update the sync mod time/checksum anyway
        updateSyncRecord($row[0], $pim_checksum);
    } else {
        //
        // 2. case: no match -> insert new record if not already deleted in phprojekt
        //
        // check if we have to CREATE a new entry in phprojekt
        $module = qss($module);
        
        $query = "SELECT ".DB_PREFIX."sync_rel.phprojekt_ID, ".DB_PREFIX.$module.".ID
                    FROM ".DB_PREFIX."sync_rel
               LEFT JOIN ".DB_PREFIX.$module." ON ".DB_PREFIX."sync_rel.phprojekt_ID=".DB_PREFIX.$module.".ID
                   WHERE ".DB_PREFIX."sync_rel.phprojekt_module='$module'
                     AND ".DB_PREFIX."sync_rel.sync_ID='$syncID'
                     AND ".DB_PREFIX."sync_rel.user_ID=".(int)$user_ID;
        appDebug(array(__FUNCTION__, __LINE__, "SQL query:\n$query\n"), 8);
        $result = db_query($query);
        $row = db_fetch_row($result);
        if (!$row[0]>0 && !$row[1]>0) {
            // we have a new item
            $PHProjekt_ID = insertPimRecord($pim_record);
            insertSyncrecord($PHProjekt_ID, $syncID, $pim_checksum);
            appDebug(array(__FUNCTION__, __LINE__, "[$module] - new pim data found: create new entry in phprojekt."), 1);
        } else {
            appDebug(array(__FUNCTION__, __LINE__, "[$module] - new pim data found, but in phprojekt already deleted: new entry in phprojekt NOT created."), 1);
        }
    }
    $sync_data['nomod'][] = $PHProjekt_ID;
}


/**
 * updates the timestamp/sync_checksum for this entry
 *
 * @param integer $ID
 * @param string $sync_checksum
 */
function updateSyncRecord($ID, $sync_checksum) {
    global $dbTSnull;

    $query = "UPDATE ".DB_PREFIX."sync_rel
                 SET modified='$dbTSnull', sync_checksum='$sync_checksum'
               WHERE ID = ".(int)$ID;
    appDebug(array(__FUNCTION__, __LINE__, "SQL query:\n$query\n"), 8);
    $result = db_query($query);
}


/**
 * Insert sync information for the record into the database
 *
 * @param integer $phprojekt_ID
 * @param integer $sync_ID
 * @param string $checksum
 * @param string $sync_type
 * @param string $sync_version
 */
function insertSyncrecord($phprojekt_ID, $sync_ID, $checksum, $sync_type='outlook', $sync_version='0.1') {
    global $module, $dbIDnull, $dbTSnull, $user_ID;

    $query = xss("INSERT INTO ".DB_PREFIX."sync_rel
                         (        user_ID,    sync_type ,  sync_ID,sync_module,       phprojekt_ID,phprojekt_module, created,modified,sync_checksum, sync_version)
                  VALUES (".(int)$user_ID.",'$sync_type','$sync_ID','$module',".(int)$phprojekt_ID.",'$module','$dbTSnull','$dbTSnull','$checksum','$sync_version')");
    appDebug(array(__FUNCTION__, __LINE__, "SQL query:\n$query\n"), 8);
    $result = db_query($query);
}


/**
 * update the record of PHProjekt with the values from the pim record
 *
 * @param integer $ID
 * @param array $pim
 */
function updatePhprojektData($ID, $pim) {
    global $module, $format, $dbTSnull, $vcal2acc, $vcal2visi;
    
    $module = qss($module);

    $sql_string = array();
    switch ($module) {
        case 'termine':
            $sql_string[] = "datum='".substr($pim[3],6,4).'-'.substr($pim[3],3,2).'-'.substr($pim[3],0,2)."'";
            $sql_string[] = $format[$module][1]."='".addslashes(utf8_decode($pim[1]))."'";
            $sql_string[] = $format[$module][2]."='".addslashes(utf8_decode($pim[2]))."'";
            $start = str_replace(':','',substr($pim[3],11,5));
            $end   = str_replace(':','',substr($pim[4],11,5));

            // Everything that is not numeric is the whole day. maybe it works :-)
            if (!(is_numeric($start) && is_numeric($end))) {
                $sql_string[] = $format[$module][3]."='----'";
                $sql_string[] = $format[$module][4]."='----'";
            } else {
                $sql_string[] = $format[$module][3]."='".str_replace(':','',substr($pim[3],11,5))."'";
                $sql_string[] = $format[$module][4]."='".str_replace(':','',substr($pim[4],11,5))."'";
            };
            $sql_string[] = $format[$module][5]."='".addslashes($vcal2visi[$pim[5]])."'";
            break;

        case 'contacts':
            for ($i=1; $i < count($format[$module]); $i++) {
                $sql_string[] = $format[$module][$i]."='".addslashes(utf8_decode($pim[$i]))."'";
            }
            break;
        case 'notes':
            $pim[2] = rebuildNotes(utf8_decode($pim[2]));
            $sql_string[] = $format[$module][1]."='".addslashes(utf8_decode($pim[1]))."'";
            $sql_string[] = $format[$module][2]."='".addslashes($pim[2])."'";
            $sql_string[] = "div2='$dbTSnull'";
            break;
        case 'todo': // array( '', 'remark', 'note', 'anfang', 'deadline', 'progress', 'acc', 'datum')
            $sql_string[] = $format[$module][1]."='".addslashes(utf8_decode($pim[1]))."'";
            $sql_string[] = $format[$module][2]."='".addslashes(utf8_decode($pim[2]))."'";
            $startdate = (substr($pim[3],0,10) != '01.01.4501') ? substr($pim[3],6,4).'-'.substr($pim[3],3,2).'-'.substr($pim[3],0,2) : '';
            $duedate   = (substr($pim[4],0,10) != '01.01.4501') ? substr($pim[4],6,4).'-'.substr($pim[4],3,2).'-'.substr($pim[4],0,2) : '';
            $createdate= (substr($pim[7],0,10) != '01.01.4501') ? substr($pim[7],6,4).substr($pim[7],3,2).substr($pim[7],0,2).'100000' : '';
            $sql_string[] = $format[$module][3]."='".$startdate."'";
            $sql_string[] = $format[$module][4]."='".$duedate."'";
            $sql_string[] = $format[$module][5]."='".addslashes(utf8_decode($pim[5]))."'";
            $sql_string[] = $format[$module][6]."='".addslashes($vcal2acc[$pim[6]])."'";

            break;
    }
    // write into db
    $query = "UPDATE ".DB_PREFIX.$module."
                 SET ".implode(',',$sql_string).", sync2='$dbTSnull'
               WHERE ID = ".(int)$ID;
    appDebug(array(__FUNCTION__, __LINE__, "SQL query:\n$query\n"), 8);
    $result = db_query($query);
}

/**
 * fetch the values of a record in PHProjekt in case it should be returned to sync
 *
 * @param integer $ID
 * @return array
 */
function fetchPhprojektValues($ID) {
    global $module, $format;
    
    $module = qss($module);

    $phprojekt_fields = array(ID);
    if ($module=='termine') {
        // we need this one for the date entry (yyyy-mm-dd)
        // cause the other fields contains only the time (start/end)
        $phprojekt_fields[] = 'datum';
    }
    // build fields
    for ($i=1; $i < count($format[$module]); $i++) {
        $phprojekt_fields[] = $format[$module][$i];
    }
    // fetch values
    $query = "SELECT ".implode(',',$phprojekt_fields)."
                FROM ".DB_PREFIX.$module."
               WHERE ID = ".(int)$ID;
    appDebug(array(__FUNCTION__, __LINE__, "SQL query:\n$query\n"), 8);
    $result = db_query($query);
    $row = db_fetch_row($result);
    // special handling
    return convertPhprojektData($row);
}

/**
 * convert data from phprojekt to pim format
 *
 * @param array $row db result set
 * @return array
 */
function convertPhprojektData(&$row) {
    global $module, $format, $visi2vcal, $acc2vcal;

    // predefine this with two entries: [0]=$PHProjekt_ID - [1]=$syncID
    $values = array($row[0].':'.$module, '');
    switch ($module) {
        case 'termine':
            $values[] = utf8_encode($row[2]);
            $values[] = escapeHTML(utf8_encode($row[3]));
            $myDate = substr($row[1],8,2).'.'.substr($row[1],5,2).'.'.substr($row[1],0,4);
            $values[] = $myDate.' '.((($row[4] == '') || ($row[4] == '----')) ? '00:00' : substr($row[4],0,2).':'.substr($row[4],2,2)).':00';
            $values[] = $myDate.' '.((($row[5] == '') || ($row[5] == '----') || ($row[5] == '2400')) ? '23:59' : substr($row[5],0,2).':'.substr($row[5],2,2)).':00';
            // if we don't know what this field is use private as a default
            $values[] = isset($visi2vcal[$row[6]]) ? $visi2vcal[$row[6]] : 'PRIVATE';
            break;
        case 'contacts':
            for ($i=1; $i < count($format[$module]); $i++) {
                $values[] = utf8_encode($row[$i]);
            }
            break;
        case 'notes':
            $values[] = utf8_encode($row[1]);
            $values[] = utf8_encode($row[1]."\n".escapeHTML($row[2]));

            break;
        case 'todo':
            $values[] = escapeHTML(utf8_encode($row[1]));
            $values[] = utf8_encode($row[2]);
            $values[] = $row[3] != '' ? substr($row[3],8,2).'.'.substr($row[3],5,2).'.'.substr($row[3],0,4) : '';
            $values[] = $row[4] != '' ? substr($row[4],8,2).'.'.substr($row[4],5,2).'.'.substr($row[4],0,4) : '';
            $values[] = utf8_encode($row[5]);
            // if we don't know what this field is use private as a default
            $values[] = isset($acc2vcal[$row[6]]) ? $acc2vcal[$row[6]] : 'PRIVATE';
            $values[] = $row[7] != '' ? substr($row[7],6,2).'.'.substr($row[7],4,2).'.'.substr($row[7],0,4) : '';

            break;
    }
    return $values;
}


/**
 * inserts a new record from pim into the PHProjekt db
 *
 * @param array $pim
 * @return integer new record id
 */
function insertPimRecord($pim) {
    global $module, $format, $user_ID, $user_group, $dbIDnull, $dbTSnull, $vcal2visi, $vcal2acc;

    // since the name of the db-fields for the same purpose vary
    // from module to module we have to set them manually here
    switch ($module) {
        case 'termine':
            $sql_fields = array( 'von', 'an', 'datum','parent', 'serie_id', 'serie_typ', 'serie_bis',  'partstat', 'priority', 'status',
                                 $format[$module][1], $format[$module][2],
                                 $format[$module][3], $format[$module][4],
                                 $format[$module][5] );
            $myDate = substr($pim[3],6,4).'-'.substr($pim[3],3,2).'-'.substr($pim[3],0,2);
            $start = str_replace(':','',substr($pim[3],11,5));
            $end   = str_replace(':','',substr($pim[4],11,5));
            if (!(is_numeric($start) && is_numeric($end))) {
                $start = '----';
                $end   = '----';
            };

            $visi = isset($vcal2visi[$pim[5]]) ? $vcal2visi[$pim[5]] : '2';

            $sql_values = array( "'$user_ID'", "'$user_ID'", "'$myDate'", '0', '0', "''", "''", '2', '0', '0',
                                 "'".addslashes(utf8_decode($pim[1]))."'", "'".addslashes(utf8_decode($pim[2]))."'",
                                 "'".$start."'",
                                 "'".$end."'" ,
                                 "'".$visi."'" );
            break;
        case 'contacts':
            $sql_fields[] = 'von';
            $sql_values[] = "'$user_ID'";
            $sql_fields[] = 'gruppe';
            $sql_values[] = "'$user_group'";
            for ($i=1; $i<count($format[$module]); $i++) {
                $sql_fields[] = $format[$module][$i];
                $sql_values[] = "'".addslashes(utf8_decode($pim[$i]))."'";
            }
            $sql_fields[] = 'acc_read';
            $sql_values[] = "'private'";
            break;
        case 'notes':
            $pim[2] = rebuildNotes(utf8_decode($pim[2]));
            $sql_fields = array( 'von', 'div1', 'div2', $format[$module][1],
                                 $format[$module][2], 'gruppe', 'acc' );
            $sql_values = array( "'$user_ID'", "'$dbTSnull'", "'$dbTSnull'", "'".addslashes(utf8_decode($pim[1]))."'",
                                 "'".addslashes($pim[2])."'", "'$user_group'", "'private'" );
            break;
        case 'todo':
            $sql_fields = array( 'von', $format[$module][1], $format[$module][2], $format[$module][3],
                                 $format[$module][4], $format[$module][5], 'gruppe', $format[$module][6], $format[$module][7]);
            $myDate1 = (substr($pim[3],0,10) != '01.01.4501') ? substr($pim[3],6,4).'-'.substr($pim[3],3,2).'-'.substr($pim[3],0,2) : '';
            if ($myDate1 == '') $myDate1 = '';
            $myDate2 = (substr($pim[4],0,10) != '01.01.4501') ? substr($pim[4],6,4).'-'.substr($pim[4],3,2).'-'.substr($pim[4],0,2) : '';

            if ($myDate2 == '') $myDate1 = '';
            $acc = isset($vcal2acc[$pim[6]]) ? $vcal2acc[$pim[6]] : 'private';
            $myDate3 = (substr($pim[7],0,10) != '01.01.4501') ? substr($pim[7],6,4).substr($pim[7],3,2).substr($pim[7],0,2).'100000' : '';


            $sql_values = array( "'$user_ID'", "'".addslashes(utf8_decode($pim[1]))."'", "'".addslashes(utf8_decode($pim[2]))."'",
                                 "'$myDate1'", "'$myDate2'", "'".$pim[5]."'", "'$user_group'", "'$acc'", "'$myDate3'" );
            break;
    }
    // run db query
    switch ($module) {
	case 'termine':
    		$query = "INSERT INTO ".DB_PREFIX.qss($module)."
              		            (".implode(',',$sql_fields).", sync1,       sync2)
                   		     VALUES (".implode(',',$sql_values).", '$dbTSnull', '$dbTSnull')";
		break;
	case 'todo':
    		$query = "INSERT INTO ".DB_PREFIX.qss($module)."
              		            (".implode(',',$sql_fields).", sync1,       sync2)
                   		     VALUES (".implode(',',$sql_values).", '$dbTSnull', '$dbTSnull')";error_log($query.'+++');
		break;
	default:
    		$query = "INSERT INTO ".DB_PREFIX.qss($module)."
              		            (".implode(',',$sql_fields).", sync1,       sync2, gruppe)
                   		     VALUES (".implode(',',$sql_values).", '$dbTSnull', '$dbTSnull', ".(int)$user_group.")"; 
		break;
   }

    appDebug(array(__FUNCTION__, __LINE__, "SQL query:\n$query\n"), 8);
    $result = db_query($query);

    // get the ID of this record
    $query = "SELECT MAX(ID) FROM ".DB_PREFIX.$module;
    appDebug(array(__FUNCTION__, __LINE__, "SQL query:\n$query\n"), 8);
    $result = db_query($query);
    $row = db_fetch_row($result);
    return $row[0];
}


/**
 * get the id's from phprojekt data which the user is allowed to read/write
 *
 * @return array
 */
function getPhprojektAccess($withprivate = true) {
    return array( 'read'  => getPhprojektReadAccess($withprivate),
                  'write' => getPhprojektWriteAccess($withprivate),
                  'owner' => getPhprojektOwnerAccess($withprivate),
                  'none'  => array('-1') );
}


/**
 * get the id's from phprojekt data which the user is allowed to read
 *
 * @return array
 */
function getPhprojektReadAccess($withprivate = true) {
    global $module, $user_ID, $user_kurz, $sql_user_group;

    if ($withprivate)
        $privacy = "";
    else if ($module != 'termine') {
            $privacy = "AND acc <> 'private'";
        } else {
            $privacy = 'AND visi <> 1';
        };
    
    switch ($module) {
        case 'termine':
            $where = "an='$user_ID'";
            break;
        case 'contacts':
            $where = "(acc_read LIKE 'system'
                       OR ((von='$user_ID' OR acc_read LIKE 'group' OR acc_read LIKE '%\"$user_kurz\"%')
                           AND $sql_user_group)) $privacy";
            break;
        case 'notes':
            $where = "(acc LIKE 'system'
                       OR ((von='$user_ID' OR acc LIKE 'group' OR acc LIKE '%\"$user_kurz\"%')
                           AND $sql_user_group)) $privacy";
            break;
        case 'todo':
            $where = "(acc LIKE 'system'
                       OR ((von='$user_ID' OR ext='$user_ID' OR acc LIKE 'group' OR acc LIKE '%\"$user_kurz\"%')
                           AND $sql_user_group)) $privacy";
            break;
        case 'projekte':
            $where = "(acc LIKE 'system'
                       OR ((von='$user_ID' OR acc LIKE 'group' OR acc LIKE '%\"$user_kurz\"%')
                           AND $sql_user_group)) $privacy";
            break;


    }
    // initialize array with dummy value
    $ret = array('-1');
    $query = 'SELECT ID FROM '.DB_PREFIX.qss($module).' WHERE '.$where;
    appDebug(array(__FUNCTION__, __LINE__, "SQL query:\n$query\n"), 8);
    $result = db_query($query);
    while ($row = db_fetch_row($result)) {
        $ret[] = $row[0];
    }
    return $ret;
}


/**
 * get the id's from phprojekt data which the user is allowed to write
 *
 * @return array
 */
function getPhprojektWriteAccess($withprivate = true) {
    global $module, $user_ID, $user_kurz, $sql_user_group;

    if ($withprivate)
        $privacy = "";
    else if ($module != 'termine') {
            $privacy = "AND acc <> 'private'";
        } else {
            $privacy = 'AND visi <> 1';
        };

    switch ($module) {
        case 'termine':
            $where = "von='$user_ID'";
            break;
        case 'contacts':
            $where = "(von='$user_ID'
                       OR (acc_write<>''
                           AND (acc_read LIKE 'system'
                                OR ((acc_read LIKE 'group' OR acc_read LIKE '%\"$user_kurz\"%') AND $sql_user_group)))) $privacy";
            break;
        case 'notes':
            $where = "(von='$user_ID'
                       OR (acc_write<>''
                           AND (acc LIKE 'system'
                                OR ((acc LIKE 'group' OR acc LIKE '%\"$user_kurz\"%') AND $sql_user_group)))) $privacy";
            break;
        case 'todo':
            $where = "(von='$user_ID'
                       OR (acc_write<>''
                           AND (acc LIKE 'system'
                                OR (ext='$user_ID' OR (acc LIKE 'group' OR acc LIKE '%\"$user_kurz\"%') AND $sql_user_group)))) $privacy";
            break;
        case 'projekte':
            $where = "(von='$user_ID'
                       OR (acc_write<>''
                           AND (acc LIKE 'system'
                                OR (acc LIKE 'group' OR acc LIKE '%\"$user_kurz\"%') AND $sql_user_group)))  $privacy";
            break;

    }
    // initialize array with dummy value
    $ret = array('-1');
    $query = 'SELECT ID FROM '.DB_PREFIX.qss($module).' WHERE '.$where;
    appDebug(array(__FUNCTION__, __LINE__, "SQL query:\n$query\n"), 8);
    $result = db_query($query);
    while ($row = db_fetch_row($result)) {
        $ret[] = $row[0];
    }
    return $ret;
}


/**
 * get the id's from phprojekt data which the user is the owner (von)
 *
 * @return array
 */
function getPhprojektOwnerAccess($withprivate = true) {
    global $module, $user_ID;

    if ($withprivate)
        $privacy = "";
    else if ($module != 'termine') {
            $privacy = "AND acc <> 'private'";
        } else {
            $privacy = 'AND visi <> 1';
        };


    // initialize array with dummy value
    $ret = array('-1');
    $query = 'SELECT ID FROM '.DB_PREFIX.qss($module)." WHERE von = ".(int)$user_ID." $privacy";
    appDebug(array(__FUNCTION__, __LINE__, "SQL query:\n$query\n"), 8);
    $result = db_query($query);
    while ($row = db_fetch_row($result)) {
        $ret[] = $row[0];
    }
    return $ret;
}


/**
 * prepare data for pim (phprojekt => pim)
 *
 * @param string $namespace
 * @param string $sync_date
 * @return array
 */
function preparePimData($namespace, $sync_date) {
    global $module, $format, $user_ID, $sync_data, $user_acc;

    $phprojekt_fields = array('ID');
    if ($module=='termine') {
        // we need this one for the date entry (yyyy-mm-dd)
        // cause the other fields contains only the time (start/end)
        $phprojekt_fields[] = 'datum';
    }
    // build fields
    for ($i=1; $i<count($format[$module]); $i++) {
        $phprojekt_fields[] = $format[$module][$i];
    }
    // fetch values
    $query = "SELECT ".implode(',', $phprojekt_fields)."
                FROM ".DB_PREFIX.qss($module)."
               WHERE ((sync2 > $sync_date) or (sync2 = '$dbTSnull'))
                 AND ID IN ('".implode("','", $user_acc[checkPhprojekt2Pim()])."')";
    appDebug(array(__FUNCTION__, __LINE__, "SQL query:\n$query\n"), 8);
    $result = db_query($query);
    settype($sync_data['nomod'], 'array');
    while ($row = db_fetch_row($result)) {
        // skip this entry if ID is in skip-array
        if (in_array($row[0], $sync_data['nomod'])) {
            continue;
        }
        // convert data
        $conv_data = convertPhprojektData($row);
        // add phprojekt_ID to $sync_data['nomod'] to prevent multiple entries with the same ID
        $sync_data['nomod'][] = $conv_data[0];
        // add this to pim
        $sync_data['mod'][] = $conv_data;
        // insert new record in sync table with syncID=0
        // syncID must be updated with the value returned from pim!!!
        insertSyncrecord($row[0], '0', createSyncChecksum($conv_data, 2));
    }

    // create data ary
    $ary=array();
    if (is_array($sync_data['mod'])) {
        foreach ($sync_data['mod'] as $data) {
            if (is_array($data)) {
                $myary=array();
                foreach ($data as $val) {
                    // replace <> to &lt; &gt; to prevent strange probs...
                    $val = strtr($val, array( '&' => '&amp;',
                                              '<' => '&lt;',
                                              '>' => '&gt;' ) );
                   $myary[]=$val;
                }
                $ary[]=$myary;
            }
        }
    } else {
        // escape if there is no data to return back to pim
        appDebug(array(__FUNCTION__, __LINE__, "[$module] - no data found to sync from phprojekt to pim."), 1);
    }

    return $ary;
}

/*
 *
 */
/**
 * Check wether a record should be send to the pim - if the user can read it, he can sync it.
 *
 * @return string
 */
function checkPhprojekt2Pim() {
    global $module, $phprojekt2pim, $user_acc;

    $ret = 'read';
    if (isset($phprojekt2pim[$module]) && isset($user_acc[$phprojekt2pim[$module]])) {
        $ret = $phprojekt2pim[$module];
    }
    return $ret;
}

/**
 * creates the checksum for sync_rel table
 *
 * @param array $data all data fields
 * @param integer $start start of the significant data
 * @return string md5-string
 */
function createSyncChecksum(&$data, $start=0) {
    $newData = array();
    if (is_array($data)) {
        for ($i=$start; $i < count($data); $i++) {
            $newData[] = $data[$i];
        }
    } else {
        $newData[] = $data;
    }
    return md5(serialize($newData));
}


/**
 *  update sync_ID in table sync_rel
 *  this is required for entries which didn't exists on the pim side.
 *  in the first step we send that objects with an empty sync_ID
 *  after this sync.exe returns an array with the new added sync_ID's
 *
 * @param array $data
 */
function updateSyncIds($data) {
    if (!is_array($data)) {
        return;
    }
    // main loop
    foreach ($data as $aData) {

        if (!is_array($aData)) {
            continue;
        }
        $id = explode(':', $aData[0]);
        $query = "UPDATE ".DB_PREFIX."sync_rel
                     SET sync_ID='".$aData[1]."'
                   WHERE phprojekt_ID=".(int)$id[0]." 
                     AND phprojekt_module='".qss($id[1])."'";
        appDebug(array(__FUNCTION__, __LINE__, "SQL query:\n$query\n"), 8);
        $result = db_query($query);
    }
}


/**
 * special handling for outlook-notes:
 * Subject is identical to content, so only one is needed
 * TODO: that should be done on the client side
 *
 * @param array $data
 * @return array
 */
function rebuildNotes($data) {
    $lines = explode("\n", trim($data));
    settype($lines, 'array');
    if (count($lines)<2) {
        // if there is only one element, return an empty string
        $ret = '';
    } else {
        // else, return the imploded array with the first item stripped
        $ret = array_shift($lines);
        $ret = implode("\n", $lines);
    }
    return $ret;
}


/**
 * check & build stuff to delete items from pim and/or phprojekt
 *
 * @param array $pimIDs
 * @param string $sync_date
 */
function deleteData(&$pimIDs, $sync_date) {
    global $module, $sync_data, $user_ID;

    // if there are no items on the pim side (null, zero, niente, nix, ...)
    // then we have to delete all the stuff in phprojekt that belongs to current user
    // and module! with '-1' we make SQL IN() happy...
    if (count($pimIDs)==0) {
        $pimIDs[0] = '-1';
    }

    //
    // get all items in sync_rel which are not in $pimIDs and delete them
    // from sync_rel- and $module-table
    //
    $query = "SELECT ".DB_PREFIX."sync_rel.ID, ".DB_PREFIX."sync_rel.phprojekt_ID
                FROM ".DB_PREFIX."sync_rel, ".DB_PREFIX.qss($module)."
               WHERE ".DB_PREFIX."sync_rel.phprojekt_module='$module'
                 AND ".DB_PREFIX."sync_rel.modified > '$sync_date' 
                 AND ".DB_PREFIX."sync_rel.sync_ID NOT IN (".implode(",", $pimIDs).")
                 AND ".DB_PREFIX."sync_rel.user_ID=".(int)$user_ID."
                 AND ".DB_PREFIX.$module.".von=".(int)$user_ID;
    appDebug(array(__FUNCTION__, __LINE__, "SQL query:\n$query\n"), 8);
    $result = db_query($query);
    $res[0] = array();
    while ($row = db_fetch_row($result)) {
        $res[0][] = $row[0];
        $res[1][] = $row[1];
    }
    if (count($res[0])>0) {
        appDebug(array(__FUNCTION__, __LINE__, "[$module] - delete in sync_rel and phprojekt table."), 1);
        $query = "DELETE FROM ".DB_PREFIX."sync_rel WHERE ID IN ('".implode("','", $res[0])."')";
        appDebug(array(__FUNCTION__, __LINE__, "SQL query:\n$query\n"), 8);
        $result = db_query($query);

        $query = "DELETE FROM ".DB_PREFIX.qss($module)." WHERE ID IN ('".implode("','", $res[1])."')";
        appDebug(array(__FUNCTION__, __LINE__, "SQL query:\n$query\n"), 8);
        $result = db_query($query);
    }
    
    $module = qss($module);

    // get data relations from sync_rel and $module and delete them in pim
    // if they were deleted in phprojekt
    $query = "SELECT ".DB_PREFIX."sync_rel.phprojekt_ID, ".DB_PREFIX.$module.".ID,
                     ".DB_PREFIX."sync_rel.sync_ID, ".DB_PREFIX."sync_rel.ID
                FROM ".DB_PREFIX."sync_rel
           LEFT JOIN ".DB_PREFIX.$module." ON ".DB_PREFIX."sync_rel.phprojekt_ID=".DB_PREFIX.$module.".ID
               WHERE ".DB_PREFIX."sync_rel.phprojekt_module='$module'
                 AND ".DB_PREFIX."sync_rel.sync_ID IN (".implode("','", $pimIDs).")
                 AND ".DB_PREFIX."sync_rel.modified > '$sync_date'
                 AND ".DB_PREFIX."sync_rel.user_ID=".(int)$user_ID;
    appDebug(array(__FUNCTION__, __LINE__, "SQL query:\n$query\n"), 8);
    $result = db_query($query);
    $res = array();
    while ($row = db_fetch_row($result)) {
        if ($row[0]>0 && !$row[1]>0) {
            $sync_data['mod'][] = array('deleted', $row[2]);
            $res[] = $row[3];
        }
    }
    if (count($res)>0) {
        $query = "DELETE FROM ".DB_PREFIX."sync_rel WHERE ID IN ('".implode("','", $res)."')";
        appDebug(array(__FUNCTION__, __LINE__, "SQL query:\n$query\n"), 8);
        $result = db_query($query);
    }

}

/**
 * Returns all the new events that a user got in a system that have not been marked as 
 * read. 
 */
function getNotifications($loginstring, $user_pw) {
    // we have to global all this stuff to figure out what rights the user has
    global $user_ID, $user_group, $user_acc, $dbIDnull, $dbTSnull, $version;
    global $user_kurz, $sql_user_group, $sync_data, $module, $format, $user_email;
    global $user_ID;

    $module = 'summary';
    $user_acc = getPhprojektAccess();

    include_once(PATH_PRE.'lib/dbman_lib.inc.php');
    
    $res = db_query("select ".DB_PREFIX."notifications.ID, 
                            ".DB_PREFIX."notifications.date, 
                            ".DB_PREFIX."notifications.title, 
                            ".DB_PREFIX."notifications.content 
                      from  ".DB_PREFIX."notifications 
                     where  ".DB_PREFIX."notifications.user_ID='$user_ID'");
    $result = array();
    while ($row = db_fetch_row()) {
        $res[$row[0]] = array($row[1], $row[2], $row[3]);
    }
    
}


?>
