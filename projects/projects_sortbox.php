<?php

/*
 * This file is used for make the otions and show the sor box
 * Author: Gustavo Solt gustavo.solt@gmail.com
 */
 
define('PATH_PRE','../');
include_once(PATH_PRE.'lib/lib.inc.php');
require_once(LIB_PATH.'/dbman_lib.inc.php');
require_once(LIB_PATH."/sortbox/class.sortbox.php");

echo set_page_header();

echo '
        <div id="global-header">
            <div id="global-panels-top">
                <ul>
                    <li class="active">Admin</li>
                </ul>
            </div>
        </div>';
include_once(LIB_PATH.'/navigation.inc.php');
echo '<div id="global-content">';

$action = isset($_GET['action']) ? xss($_GET['action']) : (isset($_POST['action']) ? xss($_POST['action']) : '');


$extra_value = isset($_GET['extra_value']) ? xss($_GET['extra_value']) : (isset($_POST['extra_value']) ? xss($_POST['extra_value']) : '');

$options = array ('field_to_sort' => 'next_proj',
                  'extra_value'   => $extra_value,
                  'datasource'    => 'project',
                  'type'          => 'single');

switch ($action) {
    case "sort":
        $sortbox = new PHProjektSortbox($options);
        $sortbox->show_window(15);
        break;
    // save sort field
    case "sort_save":
        require_once(PATH_PRE."/lib/sortbox/datasource_".$options['datasource'].".php");
        $class_name = "Sortbox_".$options['datasource'];
        $class = new $class_name($options);
        if (isset($_SESSION['sortbox_'.$options['datasource']]['javascript']) && (!$_SESSION['sortbox_'.$options['datasource']]['javascript'])) {
            $class->save_fields($_SESSION['sortbox_'.$options['datasource']]['data']);
        } else if (isset($_POST['sortbox_dsts']) && !empty($_POST['sortbox_dsts'])) {
            $class->save_fields($_POST['sortbox_dsts']);
        } 
        $sortbox = new PHProjektSortbox($options);
        $sortbox->show_window(15);
        break;
    default:
        break;
}
echo '</div>';

echo "\n</div>\n</body>\n</html>\n";
?>
