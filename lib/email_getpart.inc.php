<?php
/**
 * Get a specific part of one mail
 *
 * @package    	lib
 * @subpackage 	main
 * @author     	Albrecht Guenther, $auth$
 * @licence     GPL, see www.gnu.org/copyleft/gpl.html
 * @copyright  	2000-2006 Mayflower GmbH www.mayflower.de
 * @version    	$Id: email_getpart.inc.php,v 1.8 2007-05-31 08:11:52 gustavo Exp $
 */

// check whether the lib has been included - authentication!
if (!defined("lib_included")) die("Please use index.php!");

/**
 * Get a specific part of one mail
 *
 * @param int   		$link2       		- Imap_stream
 * @param int    		$x           			- Identificator msg_number
 * @param object 	$structure   		- Object mail
 * @param string 	$part_number 	- Value of the part_number
 * @return misc               				Mail part or false
 */
function get_part($link2, $x, $mime_type, $structure=false, $part_number=false) {
    if (!$structure) $structure = imap_fetchstructure($link2, $x);
    if ($structure) {
        if ($mime_type == email_get_mime_type($structure)) {
            if (!$part_number) $part_number = '1';
            $text = imap_fetchbody($link2, $x, $part_number);
            if ($structure->encoding == 3) return imap_base64($text);
            else if ($structure->encoding == 4) return imap_qprint($text);
            else return $text;
        }
        /* multipart */
        if ($structure->type == 1) {
            while(list($index, $sub_structure) = each($structure->parts)) {
                if ($part_number) $prefix = $part_number.'.';
                $body_tmp = get_part($link2, $x, $mime_type, $sub_structure, $prefix.($index + 1));
                if ($body_tmp) return $body_tmp;
            }
        }
    }
    return false;
}

/**
 * Get a structure type
 *
 * @param object 	$structure   	- Object mail
 * @return string             			Part type
 */
function email_get_mime_type(&$structure) {
    $primary_mime_type = array( 'TEXT',
                                'MULTIPART',
                                'MESSAGE',
                                'APPLICATION',
                                'AUDIO',
                                'IMAGE',
                                'VIDEO',
                                'OTHER' );
    if ($structure->subtype) {
        return $primary_mime_type[(int) $structure->type].'/'.$structure->subtype;
    }
    return 'TEXT/PLAIN';
}
?>
