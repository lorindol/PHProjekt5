<?php
/**
 * main module script to handle communication with addons
 *
 * @package    Addons
 * @subpackage main
 * @author     Albrecht Guenther, $Author: gustavo $
 * @licence    	GPL, see www.gnu.org/copyleft/gpl.html
 * @copyright  2000-2006 Mayflower GmbH www.mayflower.de
 * @version    $Id: addon.php,v 1.18 2007-09-13 13:24:56 gustavo Exp $
 */

// if (!defined('lib_included')) die('Please use index.php!');
define('PATH_PRE','../');
include_once(PATH_PRE.'lib/lib.inc.php');
if (preg_match("/[^a-zA-Z0-9_-]/",$addon)) die('You are not allowed to do this');
define('ADDON',$addon);

$_SESSION['common']['module'] = 'addons';


/**
 *  The following is to let addon developers issue commands that affect the head of a page
 * the [addon].inc.php file may contain
 *
 * - CSS links for the page head
 * - JavaScript links that should be put into the page head
 * - Settings that let PHProjekt change the page title
 *
 * No output may be generated. If OB is activated, we prevent this.
 */
if(dirname(realpath($addon)) == dirname(realpath(__FILE__)) && is_file("./".ADDON."/".ADDON.".inc.php")) {
       ob_start();
       $l=ob_get_level();
       include_once("./".ADDON."/".ADDON.".inc.php");
       if($l != ob_get_level()){
           error_log("Wrong output buffering level in addon $addon!");
       }
       ob_end_clean();
}


echo set_page_header();
include_once(LIB_PATH.'/navigation.inc.php');
echo '<div class="content">';

if(dirname(realpath($addon)) == dirname(realpath(__FILE__))){
    include_once("./".ADDON."/index.php");
}

echo '</div>';

echo "\n</div>\n</body>\n</html>\n";

?>
