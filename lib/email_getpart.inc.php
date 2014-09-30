<?php

// email_getpart.inc.php - PHProjekt Version 5.2
// copyright  ©  2000-2005 Albrecht Guenther  ag@phprojekt.com
// www.phprojekt.com
// Author: Albrecht Guenther, $auth$
// $Id: email_getpart.inc.php,v 1.4 2006/08/22 08:05:47 albrecht Exp $

// check whether the lib has been included - authentication!
if (!defined("lib_included")) die("Please use index.php!");


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
