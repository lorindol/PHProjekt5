<?php

// votum.php - PHProjekt Version 5.2
// copyright  ©  2000-2005 Albrecht Guenther  ag@phprojekt.com
// www.phprojekt.com
// Author: Albrecht Guenther, $Author: nina $
// $Id: votum.php,v 1.19 2006/10/04 09:32:49 nina Exp $

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
