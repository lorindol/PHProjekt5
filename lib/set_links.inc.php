<?php

// set_links.php - PHProjekt Version 5.2
// copyright  ©  2000-2005 Albrecht Guenther  ag@phprojekt.com
// www.phprojekt.com
// Author: Albrecht Guenther, $Author: gustavo $
// $Id: set_links.inc.php,v 1.29.2.1 2007/01/13 16:41:59 gustavo Exp $

// check whether the lib has been included - authentication!

define('PATH_PRE','../');
include_once(PATH_PRE.'lib/lib.inc.php');

include_once(LIB_PATH.'/dbman_lib.inc.php');

if ($action == 'store') echo store_links();
else                    echo set_links($ID_s, $module);

echo '
</div>
</body>
</html>
';


function set_links($ID, $module) {

    $str  = set_page_header();

    // button bar
    $buttons = array();
    $buttons[] = array('type' => 'text', 'text' => '<b>'.__('Links').'</b>');
    $str .= get_buttons_area($buttons);
    $str .= '<div class="hline"></div>';
    $arr_ID = explode(',', $ID);

    $html .= "
<br />
<form action='set_links.inc.php' method='post' name='links'>
    <table cellpadding='3' rules='none' cellspacing='0' border='1'>
        <tr>
            <td width='160'>&nbsp;&nbsp;<b>".__('Name')."</b></td>
            <td width='280'><b>".__('Remark')."</b></td>
            <td width='150'><b>".__('From date')."</b></td>
            <td width='120'><b>".__('Priority')."</b></td>
        </tr>
        <tr>
            <td colspan='4' height='20'></td>
        </tr>\n";

    foreach ($arr_ID as $ID) {
        if ($ID > 0) {
            // fetch name of record in database
            switch ($module) {
                case 'contacts':
                    $name = slookup('contacts', 'vorname,nachname', 'ID', $ID,'1');
                    break;
                case 'projects':
                    $name = slookup('projekte', 'name', 'ID', $ID,'1');
                    break;
                case 'notes':
                    $name = slookup('notes', 'name', 'ID', $ID,'1');
                    break;
                case 'helpdesk':
                    $name = slookup('rts', 'name', 'ID', $ID,'1');
                    break;
                case 'filemanager':
                    $name = slookup('dateien', 'filename', 'ID', $ID,'1');
                    break;
                case 'mail':
                    $name = slookup('mail_client', 'subject', 'ID', $ID,'1');
                    break;
                case 'todo':
                    $name = slookup('todo', 'remark', 'ID', $ID,'1');
                    break;
            }

            if ($name == '') {
                $name = '['.__('No Value').']';
            }

            $html .= "
        <input type='hidden' name='action' value='store' />
        <input type='hidden' name='module' value='".$module."' />
        <input type='hidden' name='record_ID[]' value='".$ID."' />
        <tr>
            <td>&nbsp;&nbsp;".$name."</td>
            <input type='hidden' name='name[".$ID."]' value='".$name."' size='30' />
            <td><input type='text' name='title[".$ID."]' size='30' /></td>
            <td>
                <input type='text' id='date[".$ID."]' name='date[".$ID."]' size='10' ".dojoDatepicker('date['.$ID.']', date("Y-m-d"))." />
            </td>
            <td>
                <select name='priority[".$ID."]'>\n";
            for ($i=1; $i<=10; $i++) {
                $html .= "<option value='".$i."'>".$i."</option>\n";
            }
            $html .= "
                </select>
            </td>
        </tr>
        <tr>
            <td colspan='4' height='10'></td>
        </tr>\n";
        }
    }
    $html .= "
        <tr>
            <td colspan='4' height='10' style='text-align:right;'>".get_go_button()."&nbsp;</td>
        </tr>
    </table>
</form>\n";

    $str .= '
        <br />
        <div class="inner_content">
            <a name="oben" id="oben"></a>
            <div class="boxHeaderLeft"></div>
            <div class="boxHeaderRight"></div>
            <div class="boxContent">'.$html.'</div>
        </div>
        <br style="clear:both" /><br />
    </div>
    <br style="clear:both" /><br />
';

    return $str;
}


/**
* sets the reminder flag to all entries given by post
* @author Albrecht Günther / Alex Haslberger
* @return void
*/
function store_links() {
    global $user_group, $onload;

    foreach ($_POST['record_ID'] as $ID) {
        //set_links_flag($ID, $_POST['module'], $_POST['date'][$ID], $_POST['priority'][$ID], $_POST['remark'][$ID], 'private', $user_group, 0, 0);
        set_links_flag($ID, xss($_POST['module']), xss($_POST['date'][$ID]), xss($_POST['priority'][$ID]), xss($_POST['title'][$ID]), xss($_POST['name'][$ID]), 'private', $user_group, 0, 0);
    }
    $onload[] = 'window.close();';
    $str = set_page_header();
    return $str;
}


/**
* sets the links flag to a specific entry
* @author Alex Haslberger
* @param int    $ID id of the entry
* @param string $name link name
* @param string $module module to which the entry belongs
* @param string $reminder_datum date to be remembered
* @param int    $wichtung prioroty of the entry
* @param string $remark users remarks to this entry
* @param string $acc read access flag to this entry
* @param int    $group which group this entry belongs to
* @param int    $parent
* @param int    $archiv flag if entry is in archiv or not
* @return void
*/
#function set_links_flag($ID, $module, $reminder_datum, $wichtung, $remark, $acc, $gruppe, $parent, $archiv) {
#function set_links_flag($ID, $name, $module, $reminder_datum, $wichtung, $remark, $acc, $gruppe, $parent, $archiv) {
function set_links_flag($ID, $module, $reminder_datum, $wichtung, $title, $name, $acc, $gruppe, $parent, $archiv) {
    global$user_ID, $dbIDnull, $dbTSnull;

    if ($module == 'helpdesk') $module = 'rts';

    // for database security purposes
    $reminder_datum = addslashes($reminder_datum);
    $title          = addslashes($title);
    $name           = addslashes($name);
    $acc            = addslashes($acc);
    $wichtung       = (int) $wichtung;
    $gruppe         = (int) $gruppe;
    $parent         = (int) $parent;
    $archiv         = (int) $archiv;

    // check if ID has already an entry
    $result = db_query("SELECT t_ID
                          FROM ".DB_PREFIX."db_records
                         WHERE t_record = ".(int)$ID." 
                           AND t_author = ".(int)$user_ID." 
                           AND t_module = '".DB_PREFIX."$module'");
    $row = db_fetch_row($result);
    // insert / update entry
    if ($row[0] > 0) {
        $result = db_query("UPDATE ".DB_PREFIX."db_records
                               SET t_datum = '$dbTSnull',
                                   t_reminder_datum = '".xss($reminder_datum)."',
                                   t_wichtung = ".(int)$wichtung.",
                                   t_name = '".xss($name)."',
                                   t_remark = '".xss($title)."',
                                   t_acc = '$acc',
                                   t_gruppe = ".(int)$gruppe.",
                                   t_parent = ".(int)$parent.",
                                   t_reminder = 1
                             WHERE t_ID = ".(int)$row[0]) or db_die();
    }
    else {
       $query = "INSERT INTO ".DB_PREFIX."db_records
                        (        t_author ,                  t_module  ,        t_record,  t_datum  ,        t_reminder_datum  ,       t_wichtung ,        t_remark ,  t_acc,        t_gruppe,         t_parent,          t_archiv, t_reminder,        t_name  )
                 VALUES (".(int)$user_ID.",'".DB_PREFIX.xss($module)."',".(int)$ID."    ,'$dbTSnull','".xss($reminder_datum)."',".(int)$wichtung.",'".xss($title)."','$acc' ,".(int)$gruppe.", ".(int)$parent." , ".(int)$archiv.", 1         ,'".xss($name)."')";
      $result = db_query($query) or db_die();
    }
}

?>
