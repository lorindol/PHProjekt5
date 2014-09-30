<?php
/**
 * @package    votum
 * @subpackage main
 * @author     Albrecht Guenther, $Author: gustavo $
 * @licence    GPL, see www.gnu.org/copyleft/gpl.html
 * @copyright  2000-2006 Mayflower GmbH www.mayflower.de
 * @version    $Id: votum.php,v 1.20 2007-05-31 08:13:12 gustavo Exp $
 */

$options_module = 1;
$module = 'votum';

define('PATH_PRE','../');
include_once(PATH_PRE.'lib/lib.inc.php');

votum_init();

$_SESSION['common']['module'] = 'votum';

echo set_page_header();
//echo "<br /><h2>".__('results of the vote: ')."</h2><br />\n";

include_once(PATH_PRE.'lib/navigation.inc.php');

include_once('./votum_'.MODE.'.php');
echo '

</div>
</body>
</html>
';


/**
 * initialize the votum stuff and make some security checks
 *
 * @return void
 */
function votum_init() {
    global $ID, $mode, $mode2, $output;

    $output = '';

    $ID = $_REQUEST['ID'] = (int) $_REQUEST['ID'];

    if (!isset($_REQUEST['mode']) || !in_array($_REQUEST['mode'], array('view', 'forms', 'data'))) {
        $_REQUEST['mode'] = 'view';
    }
    $mode = $_REQUEST['mode'];
    define('MODE',$mode);

    if (!isset($_REQUEST['mode2']) || $_REQUEST['mode2'] != 'votum') {
        $_REQUEST['mode2'] = '';
    }
    $mode2 = xss($_REQUEST['mode2']);
}

?>
