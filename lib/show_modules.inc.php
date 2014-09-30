<?php

// show_modules.inc.php - PHProjekt Version 5.2
// copyright  ©  2000-2005 Albrecht Guenther  ag@phprojekt.com
// www.phprojekt.com
// Author: Albrecht Guenther, $auth$
// $Id: show_modules.inc.php,v 1.18 2006/08/22 08:05:48 albrecht Exp $

// check whether the lib has been included - authentication!
if (!defined("lib_included")) die("Please use index.php!");

// array with the name of all basic modules
// scheme; internal name of module, external name
$mod_arr[] = array('summary', 'href="../summary/summary.php?'.$sid.'" title="'.__('Summary').'">', __('Summary'));
if (PHPR_CALENDAR and check_role('calendar') > 0) $mod_arr[] = array('calendar', 'href="../calendar/calendar.php?'.$sid.'" title="'.__('Calendar').'">', __('Calendar'));
if (PHPR_CONTACTS and check_role('contacts') > 0) $mod_arr[] = array('contacts', 'href="../contacts/contacts.php?'.$sid.'" title="'.__('Contacts').'">', __('Contacts'));
if (PHPR_CHAT and check_role('chat') > 0) $mod_arr[] = array('chat', 'href="../chat/chat.php?'.$sid.'" title="'.__('Chat').'">', __('Chat'));
if (PHPR_FORUM and check_role('forum') > 0) $mod_arr[] = array('forum', 'href="../forum/forum.php?'.$sid.'" title="'.__('Forum').'">', __('Forum'));
if (PHPR_FILEMANAGER and check_role('filemanager') > 0) $mod_arr[] = array('filemanager', 'href="../filemanager/filemanager.php?'.$sid.'" title="'.__('Files').'">', __('Files'));
if (PHPR_PROJECTS and check_role('projects') > 0) $mod_arr[] = array('projects', 'href="../projects/projects.php?'.$sid.'" title="'.__('Projects').'">', __('Projects'));
if (PHPR_TIMECARD and check_role('timecard') > 0) $mod_arr[] = array('timecard', 'href="../timecard/timecard.php?'.$sid.'" title="'.__('Timecard').'">', __('Timecard'));
if (PHPR_NOTES and check_role('notes') > 0) $mod_arr[] = array('notes', 'href="../notes/notes.php?'.$sid.'" title="'.__('Notes').'">', __('Notes'));
if (PHPR_RTS and check_role('helpdesk') > 0) $mod_arr[] = array('helpdesk', 'href="../helpdesk/helpdesk.php?'.$sid.'" title="'.__('Helpdesk').'">', __('Helpdesk'));
if (PHPR_QUICKMAIL and check_role('mail') > 0) $mod_arr[] = array('mail', 'href="../mail/mail.php?'.$sid.'" title="'.__('Mail').'">', __('Mail'));
if (PHPR_TODO and check_role('todo') > 0) $mod_arr[] = array('todo', 'href="../todo/todo.php?'.$sid.'" title="'.__('Todo').'">', __('Todo'));
if (PHPR_LINKS) $mod_arr[] = array('links', 'href="../links/links.php?'.$sid.'" title="'.__('Links').'">', __('Links'));
if (PHPR_BOOKMARKS and check_role('bookmarks') > 0) $mod_arr[] = array('bookmarks', 'href="../bookmarks/bookmarks.php?'.$sid.'" title="'.__('Bookmarks').'">', __('Bookmarks'));
if (PHPR_VOTUM and check_role('votum') > 0) { $mod_arr[] = array('votum', 'href="../votum/votum.php?'.$sid.'" title="'.__('Voting system').'">', __('Voting system'));
}
?>
