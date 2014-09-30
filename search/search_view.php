<?php
/**
 * @package    search
 * @subpackage main
 * @author     Albrecht Guenther, $Author: gustavo $
 * @licence    GPL, see www.gnu.org/copyleft/gpl.html
 * @copyright  2000-2006 Mayflower GmbH www.mayflower.de
 * @version    $Id: search_view.php,v 1.23 2008-01-02 22:14:18 gustavo Exp $
 */

// check whether lib.inc.php has been included
if (!defined('lib_included')) die('Please use index.php!');

// button bar
$buttons = array();
$buttons[] = array('type' => 'text', 'text' => __('Search term').': '.stripslashes($searchterm));
$output .= get_buttons_area($buttons);

if ($searchterm) {
	check_csrftoken();
    include_once('./search_forms.php');
}
else {
    $ou = '';
}

$output .= '
<br />
<div class="inner_content">
    '.$out1.'
    <br />
    '.$ou.'
</div>
';

echo $output;

?>
