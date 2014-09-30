<?php

/**

 * Since for Dotnet-Soap a WSDL based switch is 

 * not possible, this one is done in php. 

 * Simply take soap.php as the end-point of your 

 * soap-request. 

 *
 * @package    soap
 * @subpackage main
 * @author     Albrecht Guenther, $Author: albrecht $
 * @licence    GPL, see www.gnu.org/copyleft/gpl.html
 * @copyright  2000-2006 Mayflower GmbH www.mayflower.de
 * @version    $Id: soap.php
 */



if ( (substr(PHP_VERSION,0,3) >= 5.1) &&  (function_exists('get_loaded_extensions')) && (in_array('soap', get_loaded_extensions()) )) {

    define('FILE','soap5.php');

} else {

    define('FILE','soap4.php');

}

require_once("./".FILE); 

?>