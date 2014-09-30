<?php

// file_download.php - PHProjekt Version 5.2
// copyright  ©  2000-2005 Albrecht Guenther  ag@phprojekt.com
// www.phprojekt.com
// Author: Nina

define('PATH_PRE','../');
include_once(PATH_PRE.'lib/lib.inc.php');
download_attached_file($download_attached_file, $module);

/**
 * Download a file using a random value stored on $file_ID array
 *
 * @param string $rnd random string used to find the file inside $file_ID array
 * @param string $module module name where the file was requested (unused)
 */
function download_attached_file($rnd, $module='') {
    global $name, $file_ID;

    $arr = explode('|', $file_ID[$rnd]);

    // Prevent escaping from the attach dir
    if ((ereg('/', $arr[1])) or (ereg('^\.+$', $arr[1]))) die("You are not allowed to do this!");

    // assign the filename
    $name = $arr[0];

    // have a look whether this file exists
    if (!file_exists(PATH_PRE.PHPR_DOC_PATH.'/'.$arr[1])) die("panic! specified file not found ...");

    // include content type definition
    include_once(LIB_PATH.'/get_contenttype.inc.php');

    // stream the file
    readfile(PATH_PRE.PHPR_DOC_PATH.'/'.$arr[1]);
}