<?php

// access_form.inc.php - PHProjekt Version 5.2
// copyright  ©  2000-2005 Albrecht Guenther  ag@phprojekt.com
// www.phprojekt.com
// Author: Albrecht Guenther, $Author: alexander $
// $Id: access_form.inc.php,v 1.40.2.1 2007/01/23 11:53:06 alexander Exp $

// check whether lib.inc.php has been included
if (!defined('lib_included')) die('Please use index.php!');

include_once(dirname(__FILE__).'/show_group_users.inc.php');

// access selection
function access_form2($acc_read, $exclude_user, $acc_write, $same_as_parent=0, $write_access_allowed=0, $fieldname='acc', $read_acc='') {
    global $user_ID, $user_group, $user_kurz, $sql_user_group;
    // set default mode
    if (!$acc_read) {
        if (PHPR_ACC_DEFAULT == 1) $acc_read = 'group';
        else                       $acc_read = 'private';
    }
    // access: 1 = alone, 2 = all, 3 = some
    // personal file

    $str = '
    <div style="margin-right:5em;float:left;">
    <input type="radio" name="'.$fieldname.'" id="'.$fieldname.'1" value="private"';
    if ($acc_read == 'private') $str .= ' checked="checked"';
    $str .= read_o($read_acc)." /> <label for='".$fieldname."1'>".__('none')."</label><br />\n";
    // choose profile
    $str .= '<input type="radio" name="'.$fieldname.'" id="'.$fieldname.'2" value="4"';
    if ($acc_read == '4') $str .= ' checked="checked"';
    $str .= read_o($read_acc)." />\n";
    $str .= '<label for="'.$fieldname.'2"> '.__('Profile')."</label>:\n";
    $str .= '<select name="profil"'.read_o($read_acc)." style='width:120px;'>\n";
    $str .= "<option value=''></option>\n";
    $query = "SELECT ID, bezeichnung, personen, von
                FROM ".DB_PREFIX."profile
               WHERE (acc LIKE 'system'
                      OR ((von = ".(int)$user_ID." OR acc LIKE 'group' OR acc LIKE '%\"$user_kurz\"%')
                          AND $sql_user_group))
            ORDER BY bezeichnung";
    $result2 = db_query($query) or db_die();
    while ($row2 = db_fetch_row($result2)) {
        $von = $row2[1].' ('.str_replace(',', ', ', slookup('users', 'nachname,vorname', 'ID', $row2[3],true)).')';
        $str .= "<option value='".$row2[0]."'";
        if ($acc_read == $row2[2]) $str .= ' selected="selected"';
        $str .= " title='$von'>$von</option>\n";
    }
    $str .= "</select><br />\n";
    // file for all
    $str .= "<input type='radio' name='".$fieldname."' id='".$fieldname."3' value='group'";
    if ($acc_read === 'group') $str .= ' checked="checked"';
    $str .= read_o($read_acc)." />\n";
    $str .= "<label for='".$fieldname."3'>".__('Group')."</label><br />\n";
    // all groups
    if (PHPR_ACC_ALL_GROUPS) {
        $str .= "<input type='radio' name='".$fieldname."' id='".$fieldname."4' value='system'".read_o($read_acc);
        if ($acc_read == 'system') $str .= ' checked="checked"';
        $str .= " />\n";
        $str .= "<label for='".$fieldname."4'>".__('All groups')."</label> <br />\n";
    }
    // same level as directory
    if ($same_as_parent > 0) {
        $str .= "<input type='radio' name='".$fieldname."' id='".$fieldname."5' value='same_as_parent'".read_o($read_acc)." />\n";
        $str .= "<label for='".$fieldname."5'> ".__('As parent object')."</label> <br />\n";
    }
    $str .= '
    </div>
    <div style="float:left;">
    ';
    // choose users
    $str .= "<input type='radio' name='".$fieldname."' id='".$fieldname."6' value='3'".read_o($read_acc);
    if (!in_array($acc_read, array('private', 'group', 'system'))) $str .= ' checked="checked"';
    $str .= " />\n";
    $str .= '
        <label for="'.$fieldname.'6">'.__('Some').':</label><br />
        <select name="persons[]" multiple="multiple" style="width:130px;" size="5"'.read_o($read_acc).'>
            '.show_group_users($user_group, $exclude_user, serialize(array($acc_read)), true).'
        </select>
        <input type="image" src="../img/cont.gif" title="'.__('Access selector').'" name="action_form_to_access_selector" '. read_o($read_acc) .'/>
    </div>
    <div style="clear:both;">
';
    // last row in this table cell: write access!
    if ($write_access_allowed > 0) {
        // set default write mode
        if (!isset($acc_write)) {
            if (!isset($ID)) {
                if (PHPR_ACC_WRITE_DEFAULT == '1') $acc_write = 'on';
            }
        }
        $checked = ($acc_write == 'w' or $acc_write == 'on') ? ' selected="selected"' : '';
        $str .= '<br />
    <br style="clear:both"/><br /><select name="acc_write"'.read_o($read_acc).'>
        <option value="" title="'.__('only read access to selection').'">'.__('only read access to selection').'</option>
        <option value="w"'.$checked.' title="'.__('read and write access to selection').'">'.__('read and write access to selection').'</option>
    </select>';
    }
    $str .= '
    </div>
';
    return $str;
}


function access_form($acc_read, $exclude_user, $acc_write, $same_as_parent=0, $write_access_allowed=0, $fieldname='acc') {
    global $user_ID, $user_group, $read_o, $user_kurz, $sql_user_group;

    // set default mode
    if (!$acc_read) {
        if (PHPR_ACC_DEFAULT == 1) $acc_read = 'group';
        else $acc_read = 'private';
    }

    // access: 1 = alone, 2 = all, 3 = some
    // personal file
    $str .= "<table>\n<tr>\n<td><input type='radio' name='".$fieldname."' value='private'";
    if ($acc_read == 'private') $str .= ' checked="checked"';
    $str .= read_o($read_o)." /> ".__('Me')." &nbsp; \n";
    // file for all
    $str .= "<input type='radio' name='".$fieldname."' value='group'";
    if ($acc_read == 'group') $str .= ' checked="checked"';
    $str .= read_o($read_o)." /> ".__('Group')." <br />\n";
    // choose profile
    $str .= '<input type="radio" name="'.$fieldname.'" value="4"';
    if ($acc_read == '4') $str .= ' checked="checked" ';
    $str .= read_o($read_o);
    $str .= ' /> '.__('Profile').': ';
    $str .= '<select name="profil"'.read_o($read_o)." style='width:120px;'>\n";
    $str .= "<option value=''></option>\n";
    $query = "SELECT ID, bezeichnung, personen, von
                FROM ".DB_PREFIX."profile
               WHERE (acc LIKE 'system'
                      OR ((von = ".(int)$user_ID." OR acc LIKE 'group' OR acc LIKE '%\"$user_kurz\"%')
                          AND $sql_user_group))
            ORDER BY bezeichnung";
    $result2 = db_query($query) or db_die();
    while ($row2 = db_fetch_row($result2)) {
        $von = $row2[1].' ('.str_replace(',', ', ', slookup('users', 'nachname,vorname', 'ID', $row2[3],'1')).')';
        $str .= "<option value=".$row2[0];
        if ($acc_read == $row2[2]) $str .= ' selected="selected"';
        $str .= " title='$von'>$von</option>\n";
    }
    $str .= "</select><br />\n";

    // all groups
    if (PHPR_ACC_ALL_GROUPS) {
        $str .= "<input type='radio' name='".$fieldname."' value='system'".read_o($read_o);
        if ($acc_read == 'system') $str .= ' checked="checked"';
        $str .= ' />'.__('All groups')." <br />\n";
    }

    // same level as directory
    if ($same_as_parent > 0) {
        $str .= "<input type='radio' name='".$fieldname."' value='same_as_parent'".read_o($read_o)." /> ".__('As parent object')." <br />\n";
    }

    // last row in this table cell: write access!
    if ($write_access_allowed > 0) {
        $str .= '<img src="'.IMG_PATH.'/s.gif" width="140" height="1" vspace="2" alt="" /><br />'."\n";
        // set default write mode
        if (!$acc_write) {
            if (PHPR_ACC_WRITE_DEFAULT == '1') $acc_write = 'on';
        }
        $checked = ($acc_write == 'w' or $acc_write == 'on') ? ' checked="checked"' : '';
        $str .= '<input type="checkbox" name="acc_write" value="w"'.$checked.read_o($read_o).' /> '.__('Write access')."</td>\n";
    }
    // choose users
    $str .= "<td>\n<input type='radio' name=".$fieldname." value='3'".read_o($read_o);
    if (ereg(";}", $acc_read)) $str .= ' checked="checked"';
    $str .= ' />'.__('Some').":<br />\n";
    $str .= '<select name="persons[]" multiple="multiple" style="width:130px;" size="5"'.read_o($read_o).">\n";
    // select user from this group
    // show all members of the group, exclude yourself
    $str .= show_group_users($user_group, $exclude_user, $acc_read);
    $str .= "</select>\n</td>\n</tr>\n</table>\n";

    // end access
    return $str;
}

?>
