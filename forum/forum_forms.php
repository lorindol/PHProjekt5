<?php
/**
* forum forms script
*
* @package    forum
* @module     main
* @author     Albrecht Guenther, $Author: polidor $
* @licence    GPL, see www.gnu.org/copyleft/gpl.html
* @copyright  2000-2006 Mayflower GmbH www.mayflower.de
* @version    $Id: forum_forms.php,v 1.52.2.3 2007/02/27 16:05:39 polidor Exp $
*/
if (!defined('lib_included')) die('Please use index.php!');

// check role
if (check_role("forum") < 1) die("You are not allowed to do this!");


/**
* writetext()- gibt das Antwortformular auf einen Forumsbeitrag aus
* @author Nina Schmitt
* @param
* @return
*/
function writetext() {
    global $ID, $fID, $antwort, $sid, $mode, $mode2;
    global $user_group, $sql_user_group,  $tree_mode,$user_kurz, $user_ID;
    global $date_format_object,$module;

    // button bar
    $buttons = array();
    // form start
    $hidden = array('mode' => 'forms', 'fID' => $fID);
    if(SID) $hidden[session_name()] = session_id();
    $buttons[] = array('type' => 'form_start', 'hidden' => $hidden, 'enctype' => "multipart/form-data");
    $buttons[] =(array('type' => 'submit', 'name' => 'answer', 'value' => __('OK'), 'active' => false));
    $buttons[] =(array('type' => 'link', 'href' => 'forum.php?fID='.$fID.'&sort='.$sort.'&mode=view', 
    'text' => __('List View'), 'active' => false));


    // Check if is an answer
    $result = db_query("SELECT antwort
                          FROM ".DB_PREFIX."forum
                         WHERE ID = ".(int)$ID) or db_die();
    $row = db_fetch_row($result);
    if ($row[0] == 0) {
        if (check_archiv_flag($ID.$sid,$module)) {
            $buttons[]  = array('type' => 'link', 'href' => 'forum.php?mode=view&tree_mode='.$tree_mode.'&fID='.$fID.$sid.'&set_archiv_flag=0&ID_s='.$ID.$sid, 'text' => __('Take back from Archive'), 'active' => false);
        }
        else {
            $buttons[]  = array('type' => 'link', 'href' => 'forum.php?mode=view&tree_mode='.$tree_mode.'&fID='.$fID.$sid.'&set_archiv_flag=1&ID_s='.$ID.$sid, 'text' => __('Move to archive'), 'active' => false);
        }
    }
    
    $output .= get_buttons_area($buttons);

    // check permission
    $result = db_query("select ID, von, acc, acc_write
                          from ".DB_PREFIX."forum
                         where ID = ".(int)$ID." and
                               $sql_user_group") or db_die();
    $row = db_fetch_row($result);
    if (!$row[0] or check_role("forum") < 1 or ($row[1] <> $user_ID and !eregi("system|group|$user_kurz",$row[2])) or (1<2)) {
        //die("You are not privileged to do this!");
    }

    $result = db_query("select ID,antwort,von,titel,remark,kat,datum,gruppe,lastchange,notify
                          from ".DB_PREFIX."forum
                         where ID = ".(int)$ID) or db_die();
    $row = db_fetch_row($result);

    // B  <tr><td width=450><i>".slookup('users','vorname,nachname','ID',$row[2])."<i>".$date_format_object->convert_dbdatetime2user($row[6])."</i><h3>".html_out($row[3])."</h3></td></tr>";
    // find out how many comments are already there and adjustthe wordwrap to it
    $linelength = 80;
    $linelength = $linelength + findlinelength($row[4]);

    // add linebreaks, convert web and mail links to clickable links
    $posting = wordwrap(html_out($row[4]),$linelength);
    // begin regexp - turn text links into clickable links
    $posting = @eregi_replace("(((f|ht){1}tp://)[a-zA-Z0-9@:%_.~#-\?&]+[a-zA-Z0-9@:%_~#\?&])", "<a href=\"\\1\" target=\"_blank\">\\1</a>", $posting); //http
    // in case of problems change the above line with the next one
    $posting = @eregi_replace("([[:space:]()[{}])(www.[a-zA-Z0-9@:%_.~#-\?&]+[a-zA-Z0-9@:%_~#\?&])", "\\1<a href=\"http://\\2\" target=\"_blank\">\\2</a>", $posting); // www.
    // in case of problems change the above line with the next one
    $posting = @eregi_replace("([_\.0-9a-z-]+@([0-9a-z][0-9a-z-]+\.)+[a-z]{2,3})","<a href=\"mailto:\\1\">\\1</a>", $posting); // @
    // end regexp for clickable links
    // add linebreaks, convert web and mail links to clickable links
    $posting = '<br />'.nl2br($posting).'<br /><br />';
    $posting .= "<br />".show_ans($fID,$ID).'<br />';

    // comment
    global $titel,$remark;
    if (!isset($titel)) $ti= "Re: ".html_out($row[3]);
    else $ti = xss($titel);

    if (!isset($remark)) $remark = '';

    $hidden_fields = array ( "mode" => "data",
                             "fID"  => $fID,
                             "ID"   => $ID);
    $form_fields=array();
    $form_fields[] = array('type' => 'text', 'name' => 'titel', 'class'=>'fullsize', 'label' => __('Title').__(':'),'label_class'=>'label_block', 'value'=>htmlspecialchars($ti));
    $form_fields[] = array('type' => 'textarea', 'name' => 'remark','class'=>'fullsize', 'label_class'=>'label_block','label' => __('Text').__(':'), 'value'=>$remark);
                                 
    $comment = '<br />'.get_form_content($form_fields).hidden_fields($hidden_fields).'
    </fieldset>

<div class="clear">
   <fieldset>
<legend>'.__('Notification').'</legend>
        <span id="center">';

    global $notify_me,$notify_others;
    if ($notify_me == 'on') $chek_me = 'checked=checked';
    if ($notify_others == 'on') $chek_other = 'checked=checked';
    if (PHPR_FORUM_NOTIFY) {
        $comment .= "<input type=checkbox name=notify_others ".$chek_other.">".__('Notify all group members')."<br />\n";
    }
    // checkbox for notification for myself on comments
    $comment .= "<input type=checkbox name=notify_me ".$chek_me."> ".__('Notify me on comments')."
    </span>\n";

    // access form
    include_once("../lib/access_form.inc.php");
    // acc_read, exclude the user itself, acc_write, no parent possible, write access=yes
    // values of the access
    global $persons;
    if (!isset($persons)) {
        if (!isset($_POST[$persons])) $str_persons = $row[11];
        else $str_persons = xss($_POST[$persons]);
    } else $str_persons = serialize($persons);
    #$comment .= access_form2($str_persons, 0, $row[12], 0, 0).'</div>';
    $comment .= "</fieldset>";
    $comment .= access_form2($str_persons, 0, $row[12], 0, 0).'</div>';

    $output .= '
    <br />
    <div class="inner_content">
        <a name="content"></a>
        <div class="boxHeaderLeft">'.__('Thread title').__(':').' '.html_out($row[3]).'</div>
        <div class="boxHeaderRight">'.slookup('users', 'vorname', 'ID', $row[2],'1').', '.$date_format_object->convert_dbdatetime2user($row[6]).'</div>
        <div class="boxContent">'.$posting.'</div>
        <br style="clear:both"/><br />

        <fieldset>
        <legend>'.__('Comment').'</legend>
        '.$comment.'
        </fieldset></form>
    </div>
    ';
    unset($posting);
    unset($comment);

    return $output;
}


/**
*determines how often a string was commented (and thus the extended the length)
* @author Nina Schmitt
* @param string: the concerned text
* @return int   the length of the text
*/
function findlinelength($text) {
    while (!$i) {
        $commentprefix .= ">";
        if (ereg($commentprefix,$text)) { $addlength += 6; }
        else { $i = 1; }
    }
    return $addlength;
}

/**
* form to create a new forum or topic
* @author Nina Schmitt
* @param int fID: forum ID
* @return
*/
function create_forum($fID=""){

    // button bar
    $buttons = array();
    // form start
    $hidden = array('mode' => 'forms', 'fID' => $fID);
    if(SID) $hidden[session_name()] = session_id();
    $buttons[] = array('type' => 'form_start', 'name' => 'forumneu', 'hidden' => $hidden, 'onsubmit' =>
     'return chkForm(\'forumneu\',\'titel\',\''.__('Please insert a name').'\');');


    // new forum
    if(empty($fID)){
        // create new forum
        $action= "createfor";
        $buttons[] =(array('type' => 'submit', 'name' => $action, 'value' => __('OK'), 'active' => false));
        $buttons[] =(array('type' => 'link', 'href' => 'forum.php?fID='.$fID.'&sort='.$sort.'&mode=view', 'text' => __('List View'), 'active' => false));

        $output .= get_buttons_area($buttons);
        $output .='<div class="hline"></div>';

        $head= __('Create new forum');
        
    }
    // new topic
    else {
        $action= "createbei";
        $buttons[] =(array('type' => 'submit', 'name' => $action, 'value' => __('OK'), 'active' => false));
        $buttons[] =(array('type' => 'link', 'href' => 'forum.php?fID='.(int) $fID.'&sort='.$sort.'&mode=view', 'text' => __('List View'), 'active' => false));

        $output .= get_buttons_area($buttons);
        $output .='<div class="hline"></div>';

        $head= __('New Thread');
        
    }



    /*******************************
    *       basic fields
    *******************************/
    global $persons,$notify_me,$notify_others,$remark,$titel;

    if (!isset($titel)) $ti= "";
    else $ti = xss($titel);

    if (!isset($remark)) $remark = '';

    $form_fields = array();
    $form_fields[] = array('type' => 'text', 'name' => 'titel', 'label' => __('Title').__(':'), 'value' => $ti, 'class'=>'fullsize', 'label_class'=>'label_block', 'width' => '500px');
    $form_fields[] = array('type' => 'textarea', 'name' => 'remark', 'label' => __('Text').__(':'), 'value' => $remark, 'class'=>'fullsize', 'label_class'=>'label_block', 'width' => '500px', 'height' => '200px');
    $form_fields[] = array('type' => 'hidden', 'name' => 'mode', 'value' => 'data');
    $form_fields[] = array('type' => 'hidden', 'name' => 'fID', 'value' => $fID);
    if (PHPR_FORUM_NOTIFY) {
        $form_fields[] = array('type' => 'checkbox', 'name' => 'notify_others', 'label_right' => __('Notify all group members').__(':'), 'checked' => $notify_others);
    }
    // checkbox for notification for myself on comments
    $form_fields[] = array('type' => 'checkbox', 'name' => 'notify_me', 'label_right' => __('Notify me on comments').__(':'), 'checked' => $notify_me);
    // access
    include_once("../lib/access_form.inc.php");
    // acc_read, exclude the user itself, acc_write, no parent possible, write access=yes
    // values of the access
    if (!isset($persons)) {
        if (!isset($_POST[$persons])) $str_persons = $row[11];
        else $str_persons = xss($_POST[$persons]);
    } else $str_persons = serialize($persons);
    $form_fields[] = array('type' => 'parsed_html', 'html' => $html);
    $basic_fields = get_form_content($form_fields);
    // release fieldset
    $release_fields = array(array('type' => 'parsed_html', 'html' => access_form2($str_persons, 0, $row[12], 0, 0)));
    $release_fieldset = get_form_content($release_fields);

    $output .= '
    <br />
    '.hidden_fields('').'
    <div class="inner_content">
        <a name="content"></a>
        <a name="oben" id="oben"></a>
        <fieldset>
        <legend>'.$head.'</legend>
        '.$basic_fields.'
        </fieldset>
        '.$release_fieldset.'
    </div>
    </form>
    <br />
    ';

    return $output;
}

// tabs
$tabs = array();
$output = '<div id="global-header">';
$output .= get_tabs_area($tabs);
$output .= breadcrumb($module, breadcrumb_data($fID, $ID, $newbei, $newfor));
$output .= '</div>';
$output .= '<div id="global-content">';
if      ($newfor) $output .= create_forum();
else if ($newbei) $output .= create_forum($fID);
else if ($ID > 0) $output .= writetext();
$output .= "</div>";
echo $output;
?>
