<?php
/**
 * bookmarks controller script
 *
 * @package    bookmarks
 * @subpackage main
 * @author     Albrecht Guenther, $Author: gustavo $
 * @licence    GPL, see www.gnu.org/copyleft/gpl.html
 * @copyright  2000-2006 Mayflower GmbH www.mayflower.de
 * @version    $Id: bookmarks.php,v 1.32 2008-01-03 15:48:42 gustavo Exp $
 */

$module = 'bookmarks';
define('PATH_PRE','../');
include_once(PATH_PRE.'lib/lib.inc.php');
$_SESSION['common']['module'] = 'bookmarks';


// Bookmark - Redirect
// TODO: dont forget the referer stuff
if (isset($_POST['lesezeichen'])) $lesezeichen = xss($_POST['lesezeichen']);
if (isset($lesezeichen)) {
    header('Location: '.xss($lesezeichen));
    exit;
}

bookmarks_init();

$options_module = 1;
$fields = array('url'=>"URL", 'bezeichnung'=>__('Name'), 'bemerkung'=>__('Text'));
echo set_page_header();

include_once(LIB_PATH.'/navigation.inc.php');

include_once('./bookmarks_'.MODE.'.php');
echo '
</div>
</body>
</html>
';


/**
 * initialize the bookmarks stuff and make some security checks
 *
 * @return void
 */
function bookmarks_init() {
    global $ID, $mode, $mode2, $output;

    $output = '';

    $ID = $_REQUEST['ID'] = (int) $_REQUEST['ID'];

    if (!isset($_REQUEST['mode']) || !in_array($_REQUEST['mode'], array('view', 'forms', 'data'))) {
        $_REQUEST['mode'] = 'view';
    }
    $mode = xss($_REQUEST['mode']);
    define('MODE',$mode);

    if (!isset($_REQUEST['mode2']) || $_REQUEST['mode2'] != 'bookmarks') {
        $_REQUEST['mode2'] = '';
    }
    $mode2 = xss($_REQUEST['mode2']);
}

?>
