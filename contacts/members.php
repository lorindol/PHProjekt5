<?php
/**
 * members controller script
 *
 * @package    contacts
 * @subpackage members
 * @author     Albrecht Guenther, Gustavo Solt, $Author: gustavo $
 * @licence    GPL, see www.gnu.org/copyleft/gpl.html
 * @copyright  2000-2006 Mayflower GmbH www.mayflower.de
 * @version    $Id: members.php,v 1.14 2007-05-31 08:10:56 gustavo Exp $
 */

$module = "members";
define('PATH_PRE','../');
require_once(PATH_PRE.'lib/lib.inc.php');

members_init();

// Clear the message_stack
unset ($_SESSION['message_stack'][$module]);

if (PHPR_LDAP) {
    include_once(LIB_PATH.'/ldap.php');
}

global $he_add;
$he_add = array('<style type="text/css">
a span.tooltip{
    visibility: hidden;
    display: none;
    position: absolute;
    background-color: white;
    padding: 2px;
    border: 1px solid black;
    text-align: left;
    margin-top: 5px;
    text-decoration: none;
}

a:hover span.tooltip{
    visibility:visible;
    display:block;
}
</style>');

echo set_page_header();

if (!$mode) $mode = 'view';
else        $mode = xss($mode);

$ID = (int) $ID;

require_once(LIB_PATH.'/dbman_lib.inc.php');

include_once(LIB_PATH.'/navigation.inc.php');

include_once('members_'.MODE.'.php');


echo "\n</div>\n</body>\n</html>\n";


/**
 * initialize the members stuff and make some security checks
 *
 * @param void
 * @return void
 */
function members_init() {
    global $ID, $mode, $output;

    $output = '';

    $ID = $_REQUEST['ID'] = (int) $_REQUEST['ID'];

    if (!isset($_REQUEST['mode']) || !in_array($_REQUEST['mode'], array('view', 'forms', 'data'))) {
        $_REQUEST['mode'] = 'view';
    }
    $mode = xss($_REQUEST['mode']);
    define('MODE',$mode);

}

?>
