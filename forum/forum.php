<?php
/**
* forum controller script
*
* @package    filemanager
* @module     main
* @author     Albrecht Guenther, $Author: alexander $
* @licence    GPL, see www.gnu.org/copyleft/gpl.html
* @copyright  2000-2006 Mayflower GmbH www.mayflower.de
* @version    $Id: forum.php,v 1.31.2.3 2007/01/23 15:35:47 alexander Exp $
*/
define('PATH_PRE','../');
$module = 'forum';
require_once(PATH_PRE.'lib/lib.inc.php');
$sort = isset($sort) ? qss($sort) : 'ASC';
include_once('forum.inc.php');
require_once(LIB_PATH.'/dbman_lib.inc.php');

$_SESSION['common']['module'] = 'forum';

$fields = array( 'titel'=>__('Title'), 'remark'=>__('Text'), 'von'=>__('From') );

$output = '';
echo set_page_header();
if ($justform > 0) {
    $content_div = '<div id="global-content" class="popup">';
} else {
    include_once(LIB_PATH.'/navigation.inc.php');
    $content_div = '<div id="global-content">';
}

if (!$mode) $mode = 'view';
else        $mode = xss($mode);
$tree_mode = isset($tree_mode) ? qss($tree_mode) : '';

$ID  = isset($ID) ? (int) $ID : -1;
$fID = isset($fID) ? (int) $fID : -1;

// call the distinct selectors
require_once('forum_selector_data.php');

// put the values in the form
global $fields;
$fields_temp = $fields;
if (isset($formdata['persons']) && $mode == "forms") {
    $persons = $formdata['persons'];
}
if (isset($_POST['titel']) && $mode == "forms") {
    $titel = xss($_POST['titel']);
}
if (isset($_POST['remark']) && $mode == "forms") {
    $remark = xss($_POST['remark']);
}
if (isset($_POST['notify_others']) && $mode == "forms") {
    $notify_others = xss($_POST['notify_others']);
}
if (isset($_POST['notify_me']) && $mode == "forms") {
    $notify_me = xss($_POST['notify_me']);
}

include_once(LIB_PATH.'/navigation.inc.php');

define('MODE',$mode);
include_once('./forum_'.MODE.'.php');

echo '</div>';

echo "\n</div>\n</body>\n</html>\n";

?>
