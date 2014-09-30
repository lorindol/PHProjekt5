<?php
/**
 * @package    timecard
 * @subpackage main
 * @author     Albrecht Guenther, $Author: gustavo $
 * @licence    GPL, see www.gnu.org/copyleft/gpl.html
 * @copyright  2000-2006 Mayflower GmbH www.mayflower.de
 * @version    $Id: timecard.php,v 1.35 2007-05-31 08:13:09 gustavo Exp $
 */

$module = 'timecard';
define('PATH_PRE','../');
include_once(PATH_PRE.'lib/lib.inc.php');
timecard_init();

include_once('./timecard_date.inc.php');

$_SESSION['common']['module'] = 'timecard';

echo set_page_header();

if ($justform > 0) {
    $content_div = '<div id="global-content" class="popup">';
} else {
    include_once(LIB_PATH.'/navigation.inc.php');
    $content_div = '<div id="global-content">';
}

// show login/logout buttons
if (PHPR_PROJECTS > 0 and ($action <> '1' and $action <> '2') and check_role('timecard') < 1) {
    #echo '<a ';
    #// include snippet with the buttons for login/logout into timecard
    #include_once(LIB_PATH.'/tc_login.inc.php');
    #show_timecard_button();
}

include_once('./timecard_'.MODE.'.php');

if ($justform > 0) echo "\n</div>\n";

echo '

</div>
</body>
</html>
';


/**
 * initialize the timecard stuff and make some security checks
 *
 * @return void
 */
function timecard_init() {
    global $ID, $mode, $submode, $output;
    global $timecard_first_submode;

    $output = '';
    $ID = $_REQUEST['ID'] = isset($_REQUEST['ID']) ? (int) $_REQUEST['ID'] : 0;
    
    if (!isset($_REQUEST['mode']) || !in_array($_REQUEST['mode'], array('view', 'forms', 'data', 'books'))) {
        $_REQUEST['mode'] = 'view';
    }
    $mode = xss($_REQUEST['mode']);
    define('MODE',$mode);

    if (!isset($_REQUEST['submode'])) {
        if (isset($_SESSION['timecard_submode'])) {
            $_REQUEST['submode'] = $_SESSION['timecard_submode'];
        } else if ($timecard_first_submode) {// projects
            $_REQUEST['submode'] = 'proj';
        } else {                        // working time
            $_REQUEST['submode'] = 'days';
        }
    }
    $_SESSION['timecard_submode'] = $_REQUEST['submode'];
    
    $submode = xss($_REQUEST['submode']);
}
?>
