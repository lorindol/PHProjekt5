<?php

// searchform.inc.php - PHProjekt Version 5.2
// copyright  ©  2000-2005 Albrecht Guenther  ag@phprojekt.com
// www.phprojekt.com
// Author: Albrecht Guenther, $auth$
// $Id: searchform.inc.php,v 1.39.2.5 2007/05/09 19:02:28 albrecht Exp $

// check whether lib.inc.php has been included
if (!defined('lib_included')) die('Please use index.php!');


// Stichwortsuche - keyword search
$out1 = '
<form action="search.php" method="post">
<fieldset>
<legend>'.__('Extended search').'</legend>
'.(SID ? '    <input type="hidden" name="'.session_name().'" value="'.session_id().'" />' : '').'
    <input type="hidden" name="searchformcount" value="'.$searchformcount.'" />
    <input type="hidden" name="csrftoken" value="'.make_csrftoken().'" />
    <!-- hint how to combine several phrases -->
    <br />
    <label for="searchterma'.$searchformcount.'" class="label_block">'.__('Search term').':</label>
    <!-- input form -->
    <input type="text" name="searchterm'.$searchformcount.'" id="searchterma'.$searchformcount.'" class="search_options" value="'.preg_replace('~"~', '&#34;', stripslashes($searchterm)).'" />
    &nbsp;&nbsp;&nbsp;('.__('Put the word AND between several phrases').')
    <br class="clear" />
    <br class="clear" />
    <label for="gebiet" class="label_block">'.__('Search area').':</label><select name="gebiet" id="gebiet">
<option value="all">'.__('all modules').'</option>
';

if (PHPR_CALENDAR and check_role('calendar') > 0)     $out1 .= '<option value="termine">'.__('Events')."</option>\n";
if (PHPR_FORUM and check_role('forum') > 0)           $out1 .= '<option value="forum">'.__('the forum')."</option>\n";
if (PHPR_FILEMANAGER and check_role('filemanager') > 0) $out1 .= '<option value="files">'.__('the files')."</option>\n";
if (PHPR_CONTACTS and check_role('contacts') > 0)     $out1 .= '<option value="contacts">'.__('Contacts')."</option>\n";
if (PHPR_NOTES and check_role('notes') > 0)           $out1 .= '<option value="notes">'.__('Notes')."</option>\n";
if (PHPR_QUICKMAIL == 2  and check_role('mail') > 0)  $out1 .= '<option value="mails">'.__('in mails')."</option>\n";
if (PHPR_TODO and check_role('todo') > 0)             $out1 .= '<option value="todo">'.__('Todo')."</option>\n";
if (PHPR_BOOKMARKS and check_role('bookmarks') > 0) $out1 .= '<option value="bookmarks">'.__('Bookmark')."</option>\n";
if (PHPR_RTS and check_role('helpdesk') > 0)          $out1 .= '<option value="helpdesk">'.__('Helpdesk')."</option>\n";

$out1 .= '
    </select>
    <br /><br />';
$fields_required= array(__('Consider current set filters')=>'use_filters',
                        __('Exclude archived elements')=>'exclude_archived',
                        __('Exclude read elements')=>'exclude_read');
$out1 .= '<fieldset>';
$out1 .= get_special_flags($fields_required,xss_array($_REQUEST) );
$out1 .= '</fieldset>';
$out1 .= '<input type="submit" name="submit" value="'.__('OK').'" class="button"/>
</fieldset>
</form>
';

$out = '
<form action="../search/search.php" target="_top" style="display:inline;">
    <fieldset class="navsearch">
    <input type="hidden" name="searchformcount" value="'.$searchformcount.'" />
    <input type="hidden" name="csrftoken" value="'.make_csrftoken().'" />
    <input type="hidden" name="module" value="search" />
'.(SID ? '    <input type="hidden" name="'.session_name().'" value="'.session_id().'" />' : '').'
';

$out .= '    <input class="navsearchbox" type="text" name="searchterm'.$searchformcount.'" id="searchterm'.$searchformcount.'" value="'.__('Search').'" onfocus="this.value=\'\'" />';
$out .=
    get_go_button('arrow_search', 'image').'
    <input type="hidden" name="gebiet" value="all" />
    </fieldset>
</form>
';


#$out = '
#<form action="../search/search.php" target="_top" style="display:inline;">
#    <fieldset class="navsearch">
#    <input type="hidden" name="searchformcount" value="" />
#    <input type="hidden" name="module" value="search" />
#
#    <input class="navsearchbox" type="text" name="searchterm" id="searchterm" value="'.__('Search').'" onfocus="this.value=\'\'" /><input type="submit" class="navSearchButton" value="" />
#    <input type="hidden" name="gebiet" value="all" />
#    </fieldset>
#</form>
#';


$out = '
<form action="../search/search.php" target="_top">
<fieldset class="navsearch">
<input class="navsearchbox" type="text" name="searchterm" id="searchterm" value="Suche" onfocus="this.value=\'\'"/>
<input type="submit" class="navSearchButton" value="" />
<input type="hidden" name="searchformcount" value="" />
<input type="hidden" name="module" value="search" />
<input type="hidden" name="gebiet" value="all" />
</fieldset>
</form>
';

?>
