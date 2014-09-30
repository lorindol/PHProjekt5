<?php
/**
* forum options script
*
* allows special operations like deleting nodes
*
* @package    forum
* @module     main
* @author     Albrecht Guenther, $Author: polidor $
* @licence    GPL, see www.gnu.org/copyleft/gpl.html
* @copyright  2000-2006 Mayflower GmbH www.mayflower.de
* @version    $Id: forum_options.php,v 1.43.2.1 2007/08/11 16:27:28 polidor Exp $
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
    $buttons[] = array('type' => 'link', 'href' => 'forum.php?mode=options&tree_mode='.$tree_mode.'&fID='.$fID.$sid.'&amp;csrftoken='.make_csrftoken(), 'text' => $button_delete, 'active' => false);
    $output .= get_buttons_area($buttons);
    $output .='<div class="hline"></div>';
    $output .= '<a name="content"></a>';

    if ($fID) {
        $result = db_query("select ID, titel, datum
                              from ".DB_PREFIX."forum
                         where von = ".(int)$user_ID." AND parent = ".(int)$fID." 
                          order by datum desc") or db_die();
    }
    else {
        $result = db_query("select ID, titel, datum
                              from ".DB_PREFIX."forum
                             where von =".(int)$user_ID." AND (parent = 0 OR parent IS NULL) 
                          order by datum desc") or db_die();
    }
    $options = '';
    while ($row = db_fetch_row($result)) {
        $result2 = db_query("select ID
                               from ".DB_PREFIX."forum
                              where antwort =".(int)$row[0]) or db_die();
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
    $result = db_query("DELETE
                          FROM ".DB_PREFIX."forum
                         WHERE parent = ".(int)$ID) or db_die();
    // db action
    $result = db_query("delete from ".DB_PREFIX."forum
                        where ID = ".(int)$ID) or db_die();

    // remove the db_record
    remove_link($ID, 'forum');

    // ... and call the list
    $ID = "";
    $mode = "view";
}


// show own records ...
if (!$ID){
    // tabs
    $tabs   = array();
    $output = '<div id="global-header">';
    $output .= get_tabs_area($tabs);
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
