<?php
/**
 * @package    search
 * @subpackage main
 * @author     Albrecht Guenther, $Author: gustavo $
 * @licence    GPL, see www.gnu.org/copyleft/gpl.html
 * @copyright  2000-2006 Mayflower GmbH www.mayflower.de
 * @version    $Id: search.php,v 1.21 2008-01-02 22:14:17 gustavo Exp $
 */

$options_module = 1;

define('PATH_PRE','../');
$module = $_SESSION['common']['module'] = 'search';
include_once(PATH_PRE.'lib/lib.inc.php');

$output = '';
$tabs = array();

echo set_page_header();
include_once(LIB_PATH.'/navigation.inc.php');
$show_form = 1;
$s_var = 'searchterm'.$searchformcount;
if ($searchformcount) $searchterm = $$s_var;
include_once(LIB_PATH.'/searchform.inc.php');

echo breadcrumb($module);
echo '</div>';
echo '<div id="global-content">';
include_once('search_view.php');
echo '
    <br />
    <br />
</div>
</body>
</html>
';

?>
