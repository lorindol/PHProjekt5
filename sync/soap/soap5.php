<?php
/**
 * Soap Interface for PHP 5 with soap extension
 *
 * @package    soap
 * @subpackage main
 * @author     Albrecht Guenther, $Author: albrecht $
 * @licence    GPL, see www.gnu.org/copyleft/gpl.html
 * @copyright  2000-2006 Mayflower GmbH www.mayflower.de
 * @version    $Id: soap5.php
 */

// Init PHProjekt stuff 
require_once('./soap_lib.php'); 


/**
 * Class to represent complex soap type - we cant do arrays in arrays, so we do arrays of complex types with arrays. 
 *
 */
class ItemAryElementContainer {
    
    /**
     * array with data entries
     *
     * @var array 
     */
    var $Element = array();
}


/**
 * Create SoapVar Object from data array 
 *
 * @param string $name soap function name 
 * @param array $dataary exchangeset
 * @return SoapVar Soap array
 */
function sendXML($name, $dataary) {
    
    $outer = array();

    if (is_array($dataary)) foreach ($dataary as $entrykey=>$entry) {
        $elementary = array();
        foreach ($entry as $item) {
            $elementary[] = strip_tags($item); 
        } 
        $elementobject = new SoapVar($elementary, 
                                       SOAP_ENC_ARRAY,
                                       'ItemAryElement', 
                                       'urn:PHProjekt'); 
        $inner = new ItemAryElementContainer();
                                               
        $inner->Element = $elementobject; 
        $innerobject = new SoapVar($inner,  
                                   SOAP_ENC_OBJECT, 
                                   'ItemAryElementContainer', 
                                   'urn:PHProjekt');
        $outer[] = $innerobject; 
    }
   $outerobject = new SoapVar($outer, 
                                SOAP_ENC_ARRAY,
                                'ItemAry', 
                                'urn:PHProjekt');
    return $outerobject; 
};






// Init Soap Server 
$wsdl = sprintf('%s://%s%s/wsdl.php',   
                (isset($_SERVER['HTTPS']) && (strtolower($_SERVER['HTTPS'])==='on')) ? 'https' : 'http',
                $_SERVER['SERVER_NAME'], 
                dirname($_SERVER['SCRIPT_NAME'])); 

// Create Soap Server Object
$server = new SoapServer($wsdl,array('uri' => 'urn:PHProjekt', 
                                     'encoding'=>'UTF-8' ));
                                    
// Add soap methods - that are actually functions :-)                                     
$server->addFunction(array('SyncCalendar', 
                           'SyncContacts', 
                           'SyncNotes', 
                           'SyncTodos', 
                           'SyncResync', 
                           'ListProjects', 
                           'SetTimeCard'));                           
                           
appDebug(array('', __LINE__, "\n\n\nEingang:\n".var_export($HTTP_RAW_POST_DATA, true)."\n\n\n"), 1);

// Handle soap request, $HTTP_RAW_POST_DATA can be omitted 
$server->handle();



?>
