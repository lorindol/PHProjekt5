<?php
/**
* members controller script
*
* @package    contacts
* @module     members
* @author     Albrecht Guenther, Gustavo Solt, $Author: nina $
* @licence    GPL, see www.gnu.org/copyleft/gpl.html
* @copyright  2000-2006 Mayflower GmbH www.mayflower.de
* @version    $Id: members.php,v 1.11 2006/10/05 13:41:21 nina Exp $
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
