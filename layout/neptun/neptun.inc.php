<?php





$modules = array(

    // position # module index name # module name # translation index name # image and/or text (0=hidden, 1=only text, 2=image only, 3=text and image)

    array(0, 'summary', 'summary', 'Summary', 1),

    array(1, 'calendar', 'calendar', 'Calendar', 1),

    array(2, 'contacts', 'contacts', 'Contacts', 1),

    array(3, 'chat', 'chat', 'Chat', 1),

    array(4, 'forum', 'forum', 'Forum', 1),

    array(5, 'filemanager', 'filemanager', 'Files', 1),

    array(6, 'projects', 'projects', 'Projects', 1),

    array(7, 'timecard', 'timecard', 'Timecard', 1),

    array(8, 'notes', 'notes', 'Notes', 1),

    array(9, 'rts','helpdesk', 'helpdesk', 1),

    array(10, 'quickmail', 'mail', 'Mail', 1),

    array(11, 'todo', 'todo', 'Todo', 1),

    array(12, 'links', 'links', 'Links', 0),

    array(13, 'bookmarks', 'bookmarks', 'Bookmarks', 0),

    array(14, 'votum', 'votum', 'Voting system', 0),
    array(15, 'costs', 'costs', 'Costs', 1),

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

    'show_headlines' => false,

);



define('PHPR_BGCOLOR1','#C2C2C2');

define('PHPR_BGCOLOR2','#D5D5D5');

define('PHPR_BGCOLOR3','#E0E0E0');





?>

