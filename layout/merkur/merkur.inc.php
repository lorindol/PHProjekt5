<?php

// merkur.inc.php - PHProjekt Version 5.2
// copyright  ©  2000-2005 Albrecht Guenther  ag@phprojekt.com
// www.phprojekt.com
// Author: 
// $Id: 

$modules = array(
    // position # module index name # module name # translation index name # image and/or text (0=hidden, 1=only text, 2=image only, 3=text and image)
    array(0, 'summary', 'summary', 'Summary', 3),
    array(1, 'calendar', 'calendar', 'Calendar', 3),
    array(2, 'contacts', 'contacts', 'Contacts', 3),
    array(3, 'chat', 'chat', 'Chat', 3),
    array(4, 'forum', 'forum', 'Forum', 3),
    array(5, 'filemanager', 'filemanager', 'Files', 3),
    array(6, 'projects', 'projects', 'Projects', 3),
    array(7, 'timecard', 'timecard', 'Timecard', 3),
    array(8, 'notes', 'notes', 'Notes', 3),
    array(9, 'rts','helpdesk', 'helpdesk', 3),
    array(10, 'quickmail', 'mail', 'Mail', 3),
    array(11, 'todo', 'todo', 'Todo', 3),
    array(12, 'links', 'links', 'Links', 3),
    array(13, 'bookmarks', 'bookmarks', 'Bookmarks', 3),
    array(14, 'votum', 'votum', 'Voting system', 3),
    array(15, 'costs', 'costs', 'Costs', 3),
);

$controls = array(
    array(0, 'logout', true),
    array(1, 'logged_as', false),
    array(2, 'search_field', true),
    array(3, 'group_box', true),
    array(4, 'settings', true),
    array(5, 'help', true),
    array(6, 'admin', true),
    array(7, 'timecard_buttons', false),
);

$config = array(
    'show_headlines' => true,
);

define('PHPR_BGCOLOR1','#C2C2C2');
define('PHPR_BGCOLOR2','#D5D5D5');
define('PHPR_BGCOLOR3','#E0E0E0');


?>
