<?php
/**
 * Soap Interface for PHP 4 
 *
 * @package    soap
 * @subpackage main
 * @author     Albrecht Guenther, $Author: albrecht $
 * @licence    GPL, see www.gnu.org/copyleft/gpl.html
 * @copyright  2000-2006 Mayflower GmbH www.mayflower.de
 * @version    $Id: soap4.php
 */

// Init PHProjekt stuff 
error_reporting(0);
require_once('./soap_lib.php'); 

// Init pear::soap
if (!include('SOAP/Server.php')) {
    ini_set('include_path', ini_get('include_path').PATH_SEPARATOR.
                            dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR.'pear'.PATH_SEPARATOR.
                            dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR.'pear'.DIRECTORY_SEPARATOR.'PEAR'
            );
    // give it another try
    if (!include('SOAP/Server.php')) {
        die('No SOAP/Server.php in '.ini_get("include_path")); 
        soapFaultDie('Pear::Soap is not installed, please install it according to pear.php.net.'); 
    };    
};




/**
 * Since Pear::Soap is not able to create a working soap answer, we have to do that on our own
 *
 * @param string $name soap function name 
 * @param array $dataary exchangeset
 */
function sendXML($name, $dataary) {
    
    $xmlData ='<?xml version=\'1.0\' encoding=\'UTF-8\'?'.'>    
<SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" xmlns:xsi="http://www.w3.org/1999/XMLSchema-instance" xmlns:xsd="http://www.w3.org/1999/XMLSchema">       
<SOAP-ENV:Body>    
<ns1:SyncResponse xmlns:ns1="urn:PHProjekt" SOAP-ENV:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/">'."\n\n";
    
    $outercount = sizeof($dataary);
    // $xmlData .= sprintf('<ReplySet xsi:type="ns1:ExchangeSet">'."\n\n");
    
    $xmlData .= sprintf('<ItemAry xmlns:ns3="http://schemas.xmlsoap.org/soap/encoding/" xsi:type="ns3:Array" ns3:arrayType="ns1:ItemAryElementContainer[%d]">'."\n\n", $outercount);
    
    if (is_array($dataary)) foreach ($dataary as $entrykey=>$entryval) {
        $innercount = sizeof($entryval);
        $xmlData .= sprintf('<item xsi:type="ns1:ItemAryElementContainer">
        <Element xmlns:ns3="http://schemas.xmlsoap.org/soap/encoding/" xsi:type="ns3:Array" ns3:arrayType="xsd:string[%d]">
        ', $innercount);        
        foreach ($entryval as $item) {
            $xmlData .= sprintf('<item xsi:type="xsd:string">%s</item>'."\n\n", 
                                strtr($item, array('&' => '&amp;',
                                                       '<' => '&lt;',
                                                       '>' => '&gt;')));
        }
        $xmlData .= '</Element>
        </item>
        ';
    }
    $xmlData .= '</ItemAry>
    ';

    $xmlData .= '</ns1:SyncResponse>    
</SOAP-ENV:Body>    
</SOAP-ENV:Envelope>';
    ob_end_clean();
    appDebug(array(__FUNCTION__, __LINE__, "[$name][put] - \$xmlData:\n$xmlData\n"), 32);
    ob_end_clean();
    header('Status: 200 OK');
    header('Server: PHProjekt-SOAP');
    header('Content-Type: text/xml; charset=UTF-8');
    header('Content-Length: '.strlen($xmlData));
    echo $xmlData;
    exit();
};







// Create and initialize the soap server 
$server = new SOAP_Server;
$server->addToMap('SyncCalendar',
array('ExchangeSet'=>'array', 'Username'=>'string', 'Password'=>'string', 'Syncdate'=>'string', 'Delete'=>'boolean', 'Private'=>'boolean'),
array('ExchangeSet'=>'array'));

$server->addToMap('SyncContacts',
array('ExchangeSet'=>'array', 'Username'=>'string', 'Password'=>'string', 'Syncdate'=>'string', 'Delete'=>'boolean', 'Private'=>'boolean'),
array('ExchangeSet'=>'array'));

$server->addToMap('SyncNotes',
array('ExchangeSet'=>'array', 'Username'=>'string', 'Password'=>'string', 'Syncdate'=>'string', 'Delete'=>'boolean', 'Private'=>'boolean'),
array('ExchangeSet'=>'array'));

$server->addToMap('SyncTodos',
array('ExchangeSet'=>'array', 'Username'=>'string', 'Password'=>'string', 'Syncdate'=>'string', 'Delete'=>'boolean', 'Private'=>'boolean'),
array('ExchangeSet'=>'array'));

// for new entries (phprojekt => sync) we get here the created entryid's
$server->addToMap('SyncResync',
array('ExchangeSet'=>'array','Username'=>'string', 'Password'=>'string'),
array('ExchangeSet'=>'array'));

$server->addToMap('ListProjects',
array('Username'=>'string', 'Password'=>'string'),
array('ExchangeSet'=>'array') );

$server->addToMap('SetTimeCard',
array('ExchangeSet'=>'array', 'Username'=>'string', 'Password'=>'string', 'Syncdate'=>'string', 'Delete'=>'boolean', 'Private'=>'boolean'),
array());

appDebug(array('', __LINE__, "\n\n\nEingang:\n".var_export($HTTP_RAW_POST_DATA, true)."\n\n\n"), 1);

$server->service(str_replace('encoding="utf-16"', 'encoding="utf-8"',$HTTP_RAW_POST_DATA));



?>