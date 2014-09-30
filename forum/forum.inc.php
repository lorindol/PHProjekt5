<?php
/**
* forum library script
*
* @package    forum
* @module     main
* @author     Albrecht Guenther, $Author: polidor $
* @licence    GPL, see www.gnu.org/copyleft/gpl.html
* @copyright  2000-2006 Mayflower GmbH www.mayflower.de
* @version    $Id: forum.inc.php,v 1.30.2.3 2007/08/11 16:27:28 polidor Exp $
*/
if (!defined('lib_included')) die('Please use index.php!');


/**
* lists all threads in a specified forum
* @author Nina Schmitt
* @param int fID: forum ID
* @return html-string
*/
function threads($fID) {
    global $a, $sid, $tree_mode;
    global $arrproj, $max, $liste;
    global $date_format_object;
    global $menu3,$module;
    global $sort;
    global $perpage, $user_ID, $user_kurz;
    
    $result = db_query("SELECT  *
                        FROM    ".DB_PREFIX."forum
                                ".sql_filter_flags($module, array('archive', 'read'))."
                        WHERE   parent = ".(int)$fID." 
                            AND (antwort=0 OR antwort IS NULL OR parent = 0)
                            AND (acc LIKE 'system'
                    OR ((von = ".(int)$user_ID."
                         OR acc LIKE 'group'
                         OR acc LIKE '%\"$user_kurz\"%')
                       ".group_string($module).")) 
                            ".sql_filter_flags($module, array('archive', 'read'), false)) or db_die();
    $liste = make_list($result);

    // button bar
    $buttons = array();

    // form start
    $hidden = array('mode' => 'forms', 'fID' => $fID);
    if(SID) $hidden[session_name()] = session_id();
    $buttons[] =(array('type' => 'link', 'href' => 'forum.php?sort='.$sort.'&mode=view', 'text' => __('Summary'), 'active' => false));
    $buttons[] = array('type' => 'form_start', 'hidden' => $hidden);
    // submit
    $buttons[] = array('type' => 'submit', 'name' => 'newbei', 'value' => __('New posting'), 'active' => false);
    // form end
    $buttons[] = array('type' => 'form_end');
    // delete posting
    $buttons[] = array('type' => 'link', 'href' => 'forum.php?mode=options&amp;tree_mode='.$tree_mode.'&amp;fID='.$fID.$sid.'&amp;csrftoken='.make_csrftoken(), 'text' => __('Delete posting'), 'active' => false);
    $output = '<div id="global-content">';
    $output .= get_buttons_area($buttons, 'oncontextmenu="startMenu(\''.$menu3->menusysID.'\',\'\',this)"');
    $output .= get_top_page_navigation_bar();
    $output .='<a name="content"></a>';

    $output .="<table class=\"ruler\" id=\"contacts\" summary=\"__('In this table you can find all threads listed')\">
    <thead>
        <tr>
            <th class=\"column2\" scope=\"col\" title=\"Titel\">".__('Title')."</th>
            <th scope=\"col\" title=\"Autor\">".__('Author')."</th>
            <th scope=\"col\" title=\"Datum\">".__('Date')."</th>
        </tr>
    </thead><tbody>";
    $int=0;
    if($max>count($liste))$max=count($liste);
    for ($i=($_SESSION['page']['forum']*$perpage); $i < $max; $i++) {
        $result = db_query("SELECT  *
                            FROM    ".DB_PREFIX."forum
                                    ".sql_filter_flags($module, array('archive', 'read'))."
                            WHERE   ID = ".(int)$liste[$i]." 
                            ".sql_filter_flags($module, array('archive', 'read'), false)) or db_die();

        $row = db_fetch_row($result);
        $output1='';
        tr_tag("forum.php?mode=forms&amp;ID=$row[0]&amp;fID=$fID",'',$row[0]);
        $output .= $output1;
        $output .= " <td scope='row'  class=\"column-1\">".

        forum_buttons($row[0])."
        <a href=forum.php?mode=forms&amp;ID=$row[0]&amp;fID=$fID>".html_out($row[3])."</a></td>
        <td>".slookup("users","nachname,vorname","ID",$row[2],'1')."</td>
        <td>".$date_format_object->convert_dbdatetime2user($row[6])."</td>
        </tr>";
        $a = 0;
        $nr_answers = 0;
        $int++;
        if ($arrproj[$liste[$i]]) {
            $r = antworten($row[0], $int);
            $output .= $r[0];
            $int = $r[1];
        }

    }

    if ($int==0) $output.="<tr><td></td><td></td><td></td></tr>";
    $output.="</tbody></table>";
    $output .= get_bottom_page_navigation_bar();
    $output .= '</div>';
    return $output;
}

/**
* shows answers for an existing thread
* @author Nina Schmitt
* @param int fID: forum ID
* @param int ID: thread ID
* @return output
*/
function show_ans($fID, $ID) {
    global $a, $perpage;
    global $arrproj, $max, $liste;
    global $date_format_object;

    $result = db_query("select * FROM ".DB_PREFIX."forum
            WHERE parent = ".(int)$fID."  AND antwort = ".(int)$ID)or db_die();
    $liste= make_list($result);

    $output.="<table class=\"ruler\" id=\"contacts\" summary=\"__('In this table you can find all threads listed')\">
    <thead>
        <tr>
            <th class=\"column-1\" scope=\"col\" title=\"Titel\">".__('Succeeding answers')."</th>
            <th scope=\"col\" title=\"Autor\">".__('Author')."</th>
            <th scope=\"col\" title=\"Datum\">".__('Date')."</th>
        </tr>
    </thead><tbody>";
    if($max>count($liste))$max=count($liste);
    for ($i=($_SESSION['page']['forum']*$perpage); $i < $max; $i++) {
        $result = db_query("SELECT * from ".DB_PREFIX."forum where ID = ".(int)$liste[$i])
        or db_die();

        $row = db_fetch_row($result);

        $output1='';
        tr_tag("forum.php?mode=forms&amp;ID=$row[0]&amp;fID=$fID",'',$row[0]);
        $output.=$output1;
        $output.="<td scope='row'  class=\"column-1\">".
        forum_buttons($row[0])."
        <a href=forum.php?mode=forms&amp;ID=$row[0]&amp;fID=$fID>".html_out($row[3])."</a></td>
        <td>".slookup("users","nachname,vorname","ID",$row[2],'1')."</td>
        <td>".$date_format_object->convert_dbdatetime2user($row[6])."</td>
        </tr>";
        $a = 0;
        $nr_answers = 0;
        $int++;
        if ($arrproj[$liste[$i]]) {
            $r=antworten($row[0], $int);
            $output.= $r[0];
            $int= $r[1];
        }

    }
    $output.="</tbody></table>";
    return $output;
}

/**
* generates answers for existing topic
* @author Nina Schmitt
* @param int antwort: parent
* @param int int: count for line layout
* @return array output and int
*/
function antworten($antwort, $int="", $output2="") {
    global $a, $sid, $fID, $tree_mode;
    global $user_group, $user_kurz, $user_ID, $arrproj;
    global $date_format_object;

    $output1='';
    $result = db_query("select ID,antwort,von,titel,remark,kat,datum,gruppe,lastchange,notify
                        from ".DB_PREFIX."forum
                       where antwort = ".(int)$antwort." and
                             (von = ".(int)$user_ID." or acc like 'system' or
                             ((acc like 'group'  or acc like '%$user_kurz%') and
                             ".DB_PREFIX."forum.gruppe = ".(int)$user_group."))
                    order by lastchange desc") or db_die();
    while ($row = db_fetch_row($result)) {
        $nr_answers++;

        if ($tree_mode == "open" or $row[0] > 0) {
            tr_tag("forum.php?mode=forms&amp;ID=$row[0]&amp;fID=$fID",'',$row[0]);
            $output2 .= $output1;
            $output2 .= " <td scope='row'  class=\"column-1\">";
            for ($e = 0; $e <= $a; $e++) { $output2.="&nbsp;&nbsp;&nbsp;&nbsp;\n";  }
            $output2 .= forum_buttons($row[0])."
        <a href=forum.php?mode=forms&amp;ID=$row[0]&amp;fID=$fID>".html_out($row[3])."</a></td>
        <td>".slookup("users","nachname,vorname","ID",$row[2],'1')."</td>
        <td>".$date_format_object->convert_dbdatetime2user($row[6])."</td>
    </tr>";
        }
        $int++;
        $a++;
        if ($arrproj[$row[0]]) {

            $r=antworten($row[0], $int);
            $output2.= $r[0];
            $int= $r[1]; }
            $a--;
    }
    return array($output2, $int);
}

/**
* form to create buttons for topics with answers (+/-)
* @author Nina Schmitt
* @param int ID: topic ID
* @return string string with link to image
*/
function forum_buttons($ID) {
    global $arrproj, $sid, $tree_mode,  $fID;

    // if the radio button 'open' was selected: set all main projects to open:
    if ($tree_mode == "open") { $arrproj[$ID] = 1; }
    // find out whether there is at at least 1 subproject
    $result = db_query("select ID
                        from ".DB_PREFIX."forum
                       where antwort = ".(int)$ID) or db_die();
    $row = db_fetch_row($result);

    if ($row[0] > 0) {
        // show button 'open'
        if (!$arrproj[$ID]) { $ret= "<a name='A".$row[0]."' href='forum.php?element_mode=open&amp;ID=$ID&amp;fID=$fID&amp;$sid#A".$row[0]."'><img src='".IMG_PATH."/open.gif' alt='' border=0>&nbsp;</a>"; }
        // show button 'close'
        else { $ret= "<a name='A".$row[0]."' href='forum.php?element_mode=close&amp;ID=$ID&amp;fID=$fID&amp;$sid#A$row[0]'><img src='".IMG_PATH."/close.gif' border=0>&nbsp;</a>"; }
    }
    // otherwise indent it
    else { $ret= "<img src='".IMG_PATH."/t.gif' width=12 height=5 border=0>"; }
    return  $ret;
}

/**
* function whiuch returns the number of articles for a specific forum
* @author Nina Schmitt
* @param int ID: forum ID
* @param string field: field in which forum:ID is saved in Database
* @param string cons: string wit additional constraints for sql query
* @return int number of articles in forum
*/
function get_articles($ID, $field, $cons="") {
    $result = db_query("SELECT COUNT(ID) FROM ".DB_PREFIX."forum
                         WHERE ".qss($field)." = ".(int)$ID." $cons") or db_die();
    $row = db_fetch_row($result);
    return $row[0];
}

/**
* function which returns the date of the last articles in a specified forum
* @author Nina Schmitt
* @param int ID: forum ID
* @param string field: field in which forum:ID is saved in Database
* @param string cons: string wit additional constraints for sql query
* @return string date of last article in forum
*/
function get_lastarticle($ID, $field, $cons="") {
    global $date_format_object;

    $result = db_query("SELECT datum
                          FROM ".DB_PREFIX."forum
                         WHERE ".qss($field)." = ".(int)$ID." $cons
                      ORDER BY datum DESC") or db_die();
    $row = db_fetch_row($result);
    return $date_format_object->convert_dbdatetime2user($row[0]);
}


/**
 * create data for breadcrumb-trail
 * @param int $fID forum-id
 * @param int $ID  post-id
 * @param misc $newbei inidcator for creating a new post
 * @param misc $newfor inidcator for creating a new forum
 * @param string $mode if = 'options': delete
 */
function breadcrumb_data($fID="", $ID="", $newbei="", $newfor="", $mode="") {
    $result = array();

    // show a forum / s.th inside a forum
    if ($fID) {
        $result[] = array('title' => htmlentities(slookup('forum', 'titel', 'ID', $fID,'1')),
                           'url'  => 'forum.php?fID='.$fID );
    }

    // delete forum / postings
    if ($mode == 'options' && empty($fID)) {  $result[] = array('title' => __('Delete forum')); }
    if ($mode == 'options' && !empty($fID)) { $result[] = array('title' => __('Delete posting')); }

    // show a posting
    if ($ID) {
        $result[] = array('title' => htmlentities(slookup('forum', 'titel', 'ID', $ID,'1')));
    }

    // create forum/thread
    if ($newfor) { $result[] = array('title'=> __('Create new forum')); }
    if ($newbei) { $result[] = array('title'=> __('New posting')); }

    return $result;
}

?>
