<?php
/**
* filemanager forms script
*
* @package    filemanager
* @module     main
* @author     Albrecht Guenther, $Author: polidor $
* @licence    GPL, see www.gnu.org/copyleft/gpl.html
* @copyright  2000-2006 Mayflower GmbH www.mayflower.de
* @version    $Id: filemanager_forms.php,v 1.78.2.1 2007/01/10 02:53:05 polidor Exp $
*/
if (!defined('lib_included')) die('Please use index.php!');
include_once(LIB_PATH.'/access_form.inc.php');

// check role
if (check_role("filemanager") < 1) die("You are not allowed to do this!");

if (eregi("xxx", $parent)) $parent = substr($parent, 14);

if ($justform == 2) $onload = array('window.opener.location.reload();', 'window.close();');
else if ($justform > 0) $justform++;

// check permission and fetch values for viewing or modifying a record
if ($ID > 0) {
    // check permission
    $result = db_query("select ID, von, acc_write, acc, parent, typ, lock_user, filename, tempname,version, versioning,gruppe
                          from ".DB_PREFIX."dateien
                         where ID = ".(int)$ID." and
                               (acc like 'system' or ((von = ".(int)$user_ID." or acc like 'group' or acc like '%\"$user_kurz\"%')".group_string()."))") or db_die();
    $row = db_fetch_row($result);
    if (!$row[0]) die("You are not privileged to do this!");
    if ($row[1] <> $user_ID and $row[2] <> 'w') $read_o = 1;
    else $read_o = 0;
    if ($row[1] <> $user_ID and PHPR_ALTER_ACC!=1) $read_acc = 1;
    else $read_acc = 0;
    $typ    = $row[5];
    $parent = $row[4];
    $vers = $row[9];
    $versioning = $row[10]==1;
    change_group($row[11]);
}

//unset ID when copying project
if ($copy <> '')$copy_output="<input type='hidden' name='copy_ID' value='$ID' />";
else $copy_output='';
$ID=prepare_ID_for_copy($ID,$copy);

if ($ID) $head = slookup('dateien', 'filename', 'ID', $ID,'1');
else     $head = __('New file');
if (!$head) $head = __('New file');
// tabs
$tabs = array();

// form start
if ($justform == 2) $justform = 1;
$hidden = array('mode' => 'data', 'ID'=>$ID, 'name'=>$user_name, session_name()=> session_id(), 'action'=>xss($action), 'justform' => $justform);
$form_fields = array();
$buttons = array();
if (SID) $hidden[session_name()] = session_id();
if ($justform > 0) $hidden['justform'] = '1';
$buttons[] = array('type' => 'form_start', 'name' => 'frm', 'hidden' => $hidden, 'enctype'=>"multipart/form-data");
$output = '<div id="global-header">';
$output .= get_tabs_area($tabs);
$output .= breadcrumb($module, breadcrumb_data($ID));
$output .= '</div>';
$output .= $content_div;
$output .= get_buttons($buttons);

// button bar

$buttons = get_default_buttons($read_o,$ID,$justform,'filemanager',true,$sid,true);

// print & lock/unlock
if (($ID > 0) && ($justform < 1)) {
    if (!$read_o) {
        if ($row[6]) {
            $buttons[] = array('type' => 'link', 'href' => 'filemanager.php?mode=data&amp;action=lockfile&amp;unlock=true&amp;lock=false&amp;ID='.$ID.'&amp;csrftoken='.make_csrftoken(), 'text' => __('Unlock file'), 'active' => false);
        }
        else {
            $buttons[] = array('type' => 'link', 'href' => 'filemanager.php?mode=data&amp;action=lockfile&amp;unlock=false&amp;lock=true&amp;ID='.$ID.'&amp;csrftoken='.make_csrftoken(), 'text' => __('Lock file'), 'active' => false);
        }
    }
    if ($row[6] == 0 || ($row[6] > 0 && $row[6] == $user_ID)) {
        $buttons[] = array('type' => 'link', 'href' => 'filemanager_down.php?mode=down&amp;mode2=attachment&amp;ID='.$ID, 'text' => __('Download').": ".__('Attachment'), 'active' => false);
        if (PHPR_DOWNLOAD_INLINE_OPTION == 1) {
            $buttons[] = array('type' => 'link', 'href' => 'filemanager_down.php?mode=down&amp;mode2=inline&amp;ID='.$ID, 'text' => __('Download').": ".__('Inline'), 'active' => false);
        }
    }
}

$output .= get_buttons_area($buttons);


//add hidden copystring in case copy was activated:
$output .= $copy_output;

/*******************************
*       basic fields
*******************************/

/********************************
// choose (upload, mkdir, link)
********************************/
$elem_types = array('f'=>__('Upload'),'d'=>__('Directory'),'l'=>__('Link'));

// selected filetype (file, directory, link)
if (!$ID && !$typ) $typ = 'f';

// update -> show selected type and draw hidden field
if ($ID) {
    $tmp = '';
    foreach ($elem_types as $elem_type => $elem_name) {
        if (ereg($elem_type, $typ)) {
            $tmp .= $elem_name;
            $form_fields[] = array('type' => 'hidden', 'name' => 'typ', 'value' => $elem_type);
            break;
        }
    }
    if (strlen($tmp)) $form_fields[] = array('type' => 'parsed_html', 'html' => $tmp);
    unset($tmp);
}
// insert -> show all possible types as links
else if ($justform == 0) {
    $tmp = '<br />';
    foreach ($elem_types as $elem_type => $elem_name) {
        if (ereg($elem_type, $typ)) {
            $tmp .= $elem_name;
            $form_fields[] = array('type' => 'hidden', 'name' => 'typ', 'value' => $elem_type);
            break;
        }
    }
    $typ_link = "<a href='./filemanager.php?mode=forms&amp;new_note=1&amp;typ=";
    $tmp .= "&nbsp;&nbsp;".$typ_link."f".$sid."'>".__('Upload')."</a>";
    $tmp .= "&nbsp;|&nbsp;".$typ_link."d".$sid."'>".__('Create directory')."</a>";
    $tmp .= "&nbsp;|&nbsp;".$typ_link."l".$sid."'>".__('Link')."</a><br /><br />";
    $form_fields[] = array('type' => 'parsed_html', 'html' => $tmp);
    unset($tmp);
}
// in case a new file should be uploaded from projects or contacts
elseif ($justform == 1 and !$ID) {
  $form_fields[] = array('type' => 'hidden', 'name' => 'typ', 'value' => 'f');
}

$tmp = '<div';
if (in_array($typ,  array('d', 'l'))) $tmp .= ' style="display:none;"';
$tmp .= '>';

$form_fields[] = array('type' => 'parsed_html', 'html' => $tmp);
if (!((isset($_REQUEST['ID'])and ($row[6] > '0') and ($row[6]!=$user_ID )))) {
    $form_fields[] = array('type' => 'file', 'name' => 'userfile', 'label' => __('File').__(':'), 'value' => '');
    $form_fields[] = array('type' => 'parsed_html', 'html' => '<label class="label_block">&nbsp;</label><span class="options">('.__('Max. file size').': '.ini_get('upload_max_filesize').'Byte)</span>');
}

$form_fields[] = array('type' => 'parsed_html', 'html' => '</div>');

$tmp = '<div';
if (in_array($typ,  array('f', 'd'))) $tmp .= ' style="display:none;"';
$tmp .= '>';

$form_fields[] = array('type' => 'parsed_html', 'html' => $tmp);
$form_fields[] = array('type' => 'text', 'name' => 'filepath', 'label' => __('name and network path').__(':'), 'value' => $row[8]);
$form_fields[] = array('type' => 'parsed_html', 'html' => '</div>');
$form_fields[] = array('type' => 'parsed_html', 'html' => '<script type="text/javascript">sh_fields_filemanager("typ");</script>');
$form_fields[] = array('type' => 'parsed_html', 'html' => build_form($fields));
$basic_fields = get_form_content($form_fields);

/*******************************
*    categorization fields
*******************************/
$form_fields = array();
$tmp = '
<label class="label_block" for="parent">'.__('Parent object').__(':').' </label>
<select class="form" id="parent" name="parent"'.read_o($read_o).'>
<option value="0"></option>
';

// define access rule
$access = " and (von = ".(int)$user_ID." or ((acc like 'group' or acc like '%\"$user_kurz\"%') and acc_write = 'w')) and $sql_user_group";
$where = "where (typ like 'd' OR typ like 'fv') ".$access;
$tmp .= show_elements_of_tree("dateien","filename",$where,"acc"," order by typ,filename",$parent,"parent",$ID);

$tmp .= '</select>';
$form_fields[] = array('type' => 'parsed_html', 'html' => $tmp);

$tmp = '<div';
if (in_array($typ, array('l', 'd'))) $tmp .= ' style="display:none;"';
$tmp .= '>';

$form_fields[] = array('type' => 'parsed_html', 'html' => $tmp);
$form_fields[] = array('type' => 'text', 'name' => 'new_sub_dir', 'label' => __('Create directory').__(':'), 'value' => '');
$form_fields[] = array('type' => 'password', 'name' => 'cryptstring', 'label' => __('Crypt upload file with password').__(':'), 'value' => '');
$form_fields[] = array('type' => 'parsed_html', 'html' => '<br style="clear:both"/>');
$form_fields[] = array('type' => 'password', 'name' => 'cryptstring2', 'label' => __('Repeat').__(':'), 'value' => '');


//only show versioning in case its activated in config.inc
if (PHPR_FILE_VERSIONING == 1){
    $form_fields[] = array('type' => 'checkbox', 'name' => 'versioning', 'label' => __('Version management').__(':'), 'value' => '1', 'checked'=>$versioning);
    $form_fields[] = array('type' => 'parsed_html', 'html' => $vers.'. '.__('Version'));
}
$form_fields[] = array('type' => 'parsed_html', 'html' => '<script type="text/javascript">sh_fields_filemanager("typ");</script>');
$form_fields[] = array('type' => 'parsed_html', 'html' => '</div>');
$categorization_fields = get_form_content($form_fields);

/*******************************
*    assignment fields
*******************************/
$form_fields = array();
include_once("../lib/access_form.inc.php");

// values of the access
if (!isset($persons)) {
    if (!isset($_POST[$persons])) $str_persons = $row[3];
    else $str_persons = xss($_POST[$persons]);
} else $str_persons = $acc = serialize($persons);
if (!isset($acc_write)) {
    if (!isset($_POST['acc_write'])) $acc_write = $row[2];
    else $acc_write = xss($_POST['acc_write']);
}
// acc_read, exclude the user itself, acc_write, no parent possible, write access=yes
$form_fields[] = array('type' => 'parsed_html', 'html' => access_form2($str_persons, 1, $acc_write, 0, 1,'acc', $read_acc));
$assignment_fields = get_form_content($form_fields);

if (PHPR_HISTORY_LOG == 2) $history = history_show('dateien', $ID);

$file_history = file_history_show($ID,$date_format_object);
if($file_history<>''){
    $file_history = '<div class="boxHeaderLeft">'.__('File History').'</div>
                    <div class="boxHeaderRight"><a class="formBoxHeader" href="#oben">'.__('Basis data').'</a></div>
                    <div class="boxContent">'.$file_history.'</div>
                    <br style="clear:both" /><br /><br />';
}
$output .= '
    <br />
    <div class="inner_content">
        <a name="content"></a>
        <a name="oben" id="oben"></a>
        <fieldset>
        <legend>'.__('Basis data').'</legend>
        '.$basic_fields.'
        </fieldset>

        <fieldset>
        <legend>'.__('Categorization').'</legend>
        '.$categorization_fields.'
        </fieldset>

        '.$assignment_fields.'
        '.$file_history.'
        '.$history.'
    </div>
    <br />
</form>
';
$output .= '</div>';
echo $output;

?>
