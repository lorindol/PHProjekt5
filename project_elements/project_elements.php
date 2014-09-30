<?php
/**
 * Project_elements controller script
 *
 * @package    projekt
 * @subpackage project_elements
 * @author     Nina Schmitt
 * @licence    GPL, see www.gnu.org/copyleft/gpl.html
 * @copyright  2000-2006 Mayflower GmbH www.mayflower.de
 * @version    
 */

$module = 'project_elements';

define('PATH_PRE','../');
include_once(PATH_PRE.'lib/lib.inc.php');
project_elements_init();

$_SESSION['common']['module'] = 'project_elements';


require_once(LIB_PATH.'/dbman_lib.inc.php');
$fields = build_array('project_elements', $ID, $mode);

echo set_page_header();

if ($justform > 0) {
    $content_div = '<div id="global-content" class="popup">';
} else {
    include_once(LIB_PATH.'/navigation.inc.php');
    $content_div = '<div id="global-content">';
}

define('MODE',$mode);
include_once('./project_elements_'.MODE.'.php');
echo '</div>';

$colour_array=array(1 => '#CCCCCC',
                    2 => '#336699',
                    3 => '#FFFF00',
                    4 => '#009900',
                    5 => '#CC3333',
                    6 => '#FF6666',
                    7 => '#CC0099',
                    8 => '#33FF99',
                    9 => '#CCCC66',
                    10 => '#CCFFCC',
                    11 => '#FFCCCC');

echo "\n</div>\n</body>\n</html>\n";


/**
 * initialize the projects stuff and make some security checks
 *
 * @return void
 */
function project_elements_init() {
    global $ID, $mode, $mode2, $justform, $output, $treemode, $begin, $end, $date_format_object;

    $output = '';
    $ID       = $_REQUEST['ID']       = (int) $_REQUEST['ID'];
       // convert user date format back to db/iso date format (from the form)
    // use $_POST here, cause dbman_data.inc.php uses also the superglobal $_POST
    if (isset($_POST['begin'])) {
        echo $begin = $_POST['begin'] = $date_format_object->convert_user2db(xss($_POST['begin']));
    }
    if (isset($_POST['end'])) {
        echo $end = $_POST['end'] = $date_format_object->convert_user2db(xss($_POST['end']));
    }

    if ( !isset($_REQUEST['mode']) ||
         !in_array($_REQUEST['mode'], array('forms', 'data')) ) {
        $_REQUEST['mode'] = 'forms';
    }
    $mode = xss($_REQUEST['mode']);

}

?>
