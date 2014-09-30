<?php
/**
 * forum options script
 *
 * allows special operations like deleting nodes
 *
 * @package    forum
 * @subpackage main
 * @author     Albrecht Guenther, $Author: gustavo $
 * @licence    GPL, see www.gnu.org/copyleft/gpl.html
 * @copyright  2000-2006 Mayflower GmbH www.mayflower.de
 * @version    $Id: forum_options.php,v 1.47 2007-05-31 08:11:15 gustavo Exp $
 */

if (!defined('lib_included')) die('Please use index.php!');

// check role
if (check_role("forum") < 2) die("You are not allowed to do this!");

/**
* show_postings() Zeigt Foren und Forenbeiträge an und bietet die Option diese zu löschen
* @author Nina Schmitt
*/
function show_postings() {
    global $user_ID, $fID, $date_format_object;

    if (empty($fID)) {
        $button_new = __('New');
        $button_delete = __('Delete forum');
        $head=__('Delete forum');
    } else {
        $button_new = __('New posting');
        $button_delete = __('Delete posting');
        $head =__('Delete posting');
    }

    // button bar
    $buttons = array();
    // form start
    $hidden = array('mode' => 'forms');
    if(SID) $hidden[session_name()] = session_id();
    $buttons[] = array('type' => 'form_start', 'hidden' => $hidden);
    // create new forum button
    $buttons[] = array('type' => 'submit', 'name' => 'newfor', 'value' => $button_new, 'active' => false);
    // form end
    $buttons[] = array('type' => 'form_end');
    // delete posting
    $buttons[] = array('type' => 'link', 'href' => 'forum.php?mode=options&tree_mode='.$tree_mode.'&fID='.$fID.$sid, 'text' => $button_delete, 'active' => false);
    $output .= get_buttons_area($buttons);
    $output .='<div class="hline"></div>';
    $output .= '<a name="content"></a>';

    if ($fID) {
        $result = db_query("SELECT ID, titel, datum
                              FROM ".DB_PREFIX."forum
                             WHERE von = ".(int)$user_ID."
                               AND parent = ".(int)$fID."
                               AND is_deleted is NULL
                          ORDER BY datum desc") or db_die();
    }
    else {
        $result = db_query("SELECT ID, titel, datum
                              FROM ".DB_PREFIX."forum
                             WHERE von =".(int)$user_ID."
                               AND (parent = 0 OR parent IS NULL)
                               AND is_deleted is NULL
                          ORDER BY datum desc") or db_die();
    }
    $options = '';
    while ($row = db_fetch_row($result)) {
        $result2 = db_query("SELECT ID
                               FROM ".DB_PREFIX."forum
                              WHERE antwort =".(int)$row[0]."
                                AND is_deleted is NULL") or db_die();
        $row2 = db_fetch_row($result2);
        if (!$row2[0]) {
            $options .= "<option value='$row[0]'>".html_out(substr($row[1],0,40))." (".$date_format_object->convert_dbdatetime2user($row[2]).")\n";
            $op = true;
        }
    }
    if ($op) {
        $options = '<option value=""></option>'.$options;
    }

    $hidden_fields = array ( "mode" => "options");
    $output .= '
    <br />
    <form action="forum.php" method="post">
    '.hidden_fields($hidden_fields).'
    <div class="inner_content">
        <a name="oben" id="oben"></a>
        <div class="boxHeader">'.$head.'</div>
        <div class="boxContent">
        <br />
        <select name="ID">'.$options.'</select>
        '.get_buttons(array(array('type' => 'submit', 'active' => false, 'name' => 'loeschen', 'value' => __('Delete'), 'onclick' => 'return confirm(\''.__('Are you sure?').'\')'))).
        get_buttons(array(array('type' => 'link', 'active' => false, 'href' => 'forum.php?fID='.$fID.'&sort='.$sort.'&mode=view', 'text' => __('List View')))).'
        <br /><br />
        </div>
        <br style="clear:both"/><br />
    </div>
    </form>
    <br style="clear:both"/><br />
    </form>
    ';

   return $output;
}


// this function is called via the options menu:
// delete a posting but only if there is no comment for it available
function delete_posting($ID) {

    // check form token
    check_csrftoken();

    // check permission
    include_once(LIB_PATH."/permission.inc.php");
    check_permission("forum","von",$ID);
    delete_record_id('forum',"WHERE parent = ".(int)$ID);
    // db action
    delete_record_id('forum',"WHERE ID = ".(int)$ID);

    // remove the db_record
    remove_link($ID, 'forum');

    // ... and call the list
    $ID = "";
    $mode = "view";
}


// show own records ...
if (!$ID){
    $output .= breadcrumb($module, breadcrumb_data($fID, $ID, $newbei, $newfor, $mode));
    $output .= '</div>';
    $output .= '<div id="global-content">';
    $output .= show_postings();
    $output .= '</div>';
    echo $output;
}
// ... and delete them
else {
    delete_posting($ID);
    include_once("./forum_view.php");
}

?>
