<?php
/**
 * @package    soap
 * @subpackage main
 * @author     Albrecht Guenther, $Author: albrecht $
 * @licence    GPL, see www.gnu.org/copyleft/gpl.html
 * @copyright  2000-2006 Mayflower GmbH www.mayflower.de
 * @version    $Id: config.inc.php,v 1.1.6.1 2007/08/02 10:09:00 albrecht Exp $
 */

/*
 *   APP_DEBUG_LEVEL's (add them together for useful output, eg. 1+4+8+16):
 *      0 => switch off debugging
 *      1 => common messages
 *      2 => more common messages
 *      4 => specific and detailed messages
 *      8 => SQL queries
 *     16 => messages regarding to XML stuff
 *     32 => core XML messages (i/o)
 *     64 => special messages (eg. from userErrorHandler)
 *    128 => output some checks (db-table, php- and phprojekt-version, ...)
 *    256 => 
 *    512 => 
 *   1024 => 
 *
 */
define('APP_DEBUG_LEVEL', 1+2+4+8+16+32+128);


/*
 *   if set, use this error log file
 *   (this needs read/write access to the file location!)
 *
 */
define('APP_ERROR_FILE', './psync_error.log');


/*
 *   use user defined error handler
 *
 */
define('APP_USER_ERROR', false);


/*
 *   which data should be synchronized from phprojekt to pim?
 *
 *   read  = only the data, where the user has read access (this is almost all)
 *   write = only the data, where the user has write access
 *   owner = only the data, where the user is the owner from
 *   none  = don't sync any data from this module to the pim
 *
 */
$phprojekt2pim = array( 'termine'  => 'read',
                        'contacts' => 'read',
                        'notes'    => 'read',
                        'todo'     => 'read' );


?>