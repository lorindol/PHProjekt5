<?php

// search.php - PHProjekt Version 5.2
// copyright  ©  2000-2005 Albrecht Guenther  ag@phprojekt.com
// www.phprojekt.com
// Author: Albrecht Guenther, $Author: alexander $
// $Id: search.php,v 1.17.2.3 2007/01/23 09:06:36 alexander Exp $

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

echo '<div id="global-header">';
echo get_tabs_area($tabs);
echo breadcrumb($module);
echo '</div>';
echo '<div id="global-content">';
include_once('search_view.php');
echo '
    </div>

</div>
</body>
</html>
';

?>
