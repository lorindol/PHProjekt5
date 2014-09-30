<?php

/**

 * Since for Dotnet-Soap a WSDL based switch is 

 * not possible, this one is done in php. 

 * Simply take soap.php as the end-point of your 

 * soap-request. 

 *

 * @author Johann-Peter Hartmann

 * @package psync

 */


if ( (substr(PHP_VERSION,0,3) >= 5.1) &&  (function_exists('get_loaded_extensions')) && (in_array('soap', get_loaded_extensions()) )) {

    define('FILE','soap5.php');

} else {

    define('FILE','soap4.php');

}

require_once("./".FILE); 

?>
