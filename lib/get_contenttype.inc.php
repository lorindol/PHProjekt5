<?php

// get_contenttype.inc.php - PHProjekt Version 5.2
// copyright  ©  2000-2005 Albrecht Guenther  ag@phprojekt.com
// www.phprojekt.com
// Author: Albrecht Guenther, $auth$
// $Id: get_contenttype.inc.php,v 1.5.2.2 2007/01/11 16:23:18 gustavo Exp $

// check whether the lib has been included - authentication!
if (!defined("lib_included")) die("Please use index.php!");

// Send the general header
if (eregi("IE 5",$HTTP_USER_AGENT)) $msie5 = 1;

header("Expires: Mon, 10 Dec 2001 08:00:00 GMT");
header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT");

if ($_SERVER['HTTPS'] != 'on') {
    if ($msie5) {
        // IE cannot download from sessions without a cache
        header('Cache-Control: public');
    }
    else {
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        // header ("Cache-Control: no-cache, must-revalidate");
        header("Pragma: no-cache");
    }
}
else {
    // for SSL connections you have to replace the two previous lines with
    header("Cache-Control: must-revalidate, post-check=0,pre-check=0");
    header("Pragma: public");
}

$file_download_type = $mode2;

// fallback if no download type is set
if (!$file_download_type) $file_download_type = 'attachment';

// alternative download mode?
if ($alt_down) $file_download_type = $alt_down;

$contenttype = content_type($name);
//echo "COntentty".$contenttype;
//echo"filename: $name";
if ($file_download_type == 'inline' && PHPR_DOWNLOAD_INLINE_OPTION == 1) {
    // Check the content assign to mime to filetype
    header("Content-type: $contenttype");
    header("Content-disposition: inline; filename=\"$name\"");
}
else {
    if($msie5) {
        header("Content-type: application/force-download");
        header("Content-disposition: inline; filename=\"$name\";");
    }
    else {
        header("Content-type: $contenttype"); // application/octet-stream");
        header("Content-disposition: attachment; filename=\"$name\";");
    }
}


function content_type($name) {
    // Defines the content type based upon the extension of the file
    $contenttype  = 'application/octet-stream';
    $contenttypes = array( 'html' => 'text/html',
                           'htm'  => 'text/html',
                           'txt'  => 'text/plain',
                           'gan'  => 'text/xml',
                           'xml'  => 'text/xml',
                           'gif'  => 'image/gif',
                           'jpg'  => 'image/jpeg',
                           'png'  => 'image/png',
                           'sxw'  => 'application/vnd.sun.xml.writer',
                           'sxg'  => 'application/vnd.sun.xml.writer.global',
                           'sxd'  => 'application/vnd.sun.xml.draw',
                           'sxc'  => 'application/vnd.sun.xml.calc',
                           'sxi'  => 'application/vnd.sun.xml.impress',
                           'xls'  => 'application/vnd.ms-excel',
                           'ppt'  => 'application/vnd.ms-powerpoint',
                           'doc'  => 'application/msword',
                           'rtf'  => 'text/rtf',
                           'zip'  => 'application/zip',
                           'mp3'  => 'audio/mpeg',
                           'pdf'  => 'application/pdf',
                           'tgz'  => 'application/x-gzip',
                           'gz'   => 'application/x-gzip',
                           'vcf'  => 'text/vcf' );

    $name = ereg_replace("§"," ",$name);
    foreach ($contenttypes as $type_ext => $type_name) {
        if (preg_match ("/$type_ext$/i", $name)) $contenttype = $type_name;
    }
    return $contenttype;
}

?>
