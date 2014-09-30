<?php

// timecard.php - PHProjekt Version 5.2
// copyright  ©  2000-2005 Albrecht Guenther  ag@phprojekt.com
// www.phprojekt.com
// Author: Albrecht Guenther, $Author: florian $
// $Id: timecard.php,v 1.32.2.2 2007/02/15 13:49:08 florian Exp $

$module = 'timecard';
define('PATH_PRE','../');
include_once(PATH_PRE.'lib/lib.inc.php');
timecard_init();

include_once('./timecard_date.inc.php');

$_SESSION['common']['module'] = 'timecard';

echo set_page_header();

include_once(LIB_PATH.'/navigation.inc.php');
include_once('./timecard_'.MODE.'.php');
echo '
    </div>

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
