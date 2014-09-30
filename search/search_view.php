<?php

// votum_view.php - PHProjekt Version 5.2
// copyright  ©  2000-2005 Albrecht Guenther  ag@phprojekt.com
// www.phprojekt.com
// Author: Albrecht Guenther, $Author: albrecht $
// $Id: search_view.php,v 1.18.2.2 2007/05/09 19:02:28 albrecht Exp $

// check whether lib.inc.php has been included
if (!defined('lib_included')) die('Please use index.php!');



// button bar
$buttons = array();
$buttons[] = array('type' => 'text', 'text' => __('Search term').': '.stripslashes($searchterm));
$output .= get_buttons_area($buttons);

if ($searchterm) {
	check_csrftoken();
    include_once('./search_forms.php');
    //$output .= $ou;
}
else {
    $ou = '';
}

$output .= '
<br />
<div class="inner_content">
    '.$out1.'
    <br />
    '.xss($ou).'
</div>
';

echo $output;

?>
