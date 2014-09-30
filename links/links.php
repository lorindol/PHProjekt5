<?php

// links.php - PHProjekt Version 5.2
// copyright  ©  2000-2005 Albrecht Guenther  ag@phprojekt.com
// www.phprojekt.com
// Author: Albrecht Guenther, $Author: nina $
// $Id: links.php,v 1.19 2006/10/19 09:40:55 nina Exp $

$module = 'links';
$tablename['links'] = 'db_records';
$contextmenu = 1;

$GLOBALS['db_fieldnames']['links']['ID']     = 't_ID';
$GLOBALS['db_fieldnames']['links']['parent'] = 't_parent';

define('PATH_PRE','../');
include_once(PATH_PRE.'lib/lib.inc.php');

links_init();

$_SESSION['common']['module'] = 'links';

require_once(LIB_PATH.'/dbman_lib.inc.php');
$fields = build_array($module, $ID, $mode, 't_ID');

echo set_page_header();
if ($justform > 0) {
    $content_div = '<div class="justformcontent">';
} else {
    include_once(LIB_PATH.'/navigation.inc.php');
    $content_div = '<div id="global-content">';
}
include_once('links_'.MODE.'.php');
echo '
    </div>
</div>

</div>
</body>
</html>
';


/**
 * initialize the links stuff and make some security checks
 *
 * @return void
 */
function links_init() {
    global $ID, $mode, $output, $t_reminder_datum, $date_format_object;

    $output = '';

    $ID = $_REQUEST['ID'] = (int) $_REQUEST['ID'];

    // convert user date format back to db/iso date format (from the form)
    // use $_POST here, cause dbman_data.inc.php uses also the superglobal $_POST
    if (isset($_POST['t_reminder_datum'])) {
        $t_reminder_datum = $_POST['t_reminder_datum'] = $date_format_object->convert_user2db(xss($_POST['t_reminder_datum']));
    }

    if (!isset($_REQUEST['mode']) || !in_array($_REQUEST['mode'], array('view', 'forms', 'data'))) {
        $_REQUEST['mode'] = 'view';
    }
    $mode = xss($_REQUEST['mode']);
    define('MODE',$mode);

}

?>
