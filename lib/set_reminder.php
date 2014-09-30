<?php

// $Id: set_reminder.php,v 1.19 2006/10/05 09:12:15 nina Exp $

// include lib to fetch the sessiond data and to perform check
define('PATH_PRE','../');
include_once(PATH_PRE.'lib/lib.inc.php');
include_once(LIB_PATH.'/dbman_lib.inc.php');


function set_reminder($ID, $module) {

    $str .= set_page_header();

    $str .= "<br /><b>".__('Set Links')."</b><br /><br />\n";
    $arr_ID = explode(',', $ID);
    $str .= "<form action='set_reminder.php' method='post' name='reminder'>\n<table cellpadding='3' rules='none' cellspacing='0' border='1'>\n";
    // header
    $str .= "<tr><td>".__('Name')."</td><td>".__('Remark')."</td><td>".__('From date')."</td><td>".__('Priority')."</td></tr>\n";

    foreach ($arr_ID as $ID) {
        if ($ID > 0) {
            // fetch name of record in database
            switch($module) {
                case 'contacts':
                    $name = slookup('contacts', 'vorname,nachname', 'ID', $ID,'1');
                    break;
                case 'projects':
                    $name = slookup('projekte', 'name', 'ID', $ID,'1');
                    break;
                case 'notes':
                    $name = slookup('notes', 'name', 'ID', $ID,'1');
                    break;
            }
            $str .= "<input type=hidden name=action value='store' />\n";
            $str .= "<input type=hidden name=module value='".$module."' />\n";
            $str .= "<input type=hidden name=record_ID[] value='".$ID."' />\n";
            $str .= "<tr><td>".$name."</td>\n";
            // store the name of the module as well inorder to avoid lookups in the list view
            $str .= "<input type=hidden name='name[".$ID."]' value='".$name."' size='30' />\n";
            $str .= "<td><input type='text' id='remark[".$ID."]' name='remark[".$ID."]' size='30' ".dojoDatepicker('remark['.$ID.']', '')." /></td>\n";
            $str .= "<td><select name='priority[".$ID."]'>";
            for ($i=1; $i<=10; $i++) {
                $str .= "<option value='".$i."'>".$i."</option>\n";
            }
            $str .= "</select></td></tr>\n";
        }
    }
    $str .= "</table>\n";
    $str .= "<input type='submit' value=".__('go')." /></forms>\n";
    return $str;
}


/**
* sets the reminder flag to all entries given by post
* @author Albrecht Günther / Alex Haslberger
* @return void
*/
function store_reminder() {
    global $user_group, $onload;
    foreach ($_POST['record_ID'] as $ID) {
        set_reminder_flag($ID, xss($_POST['module']), xss($_POST['date'][$ID]), xss($_POST['priority'][$ID]), xss($_POST['remark'][$ID]), 'private', $user_group, 0, 0);
    }
    $onload[] = 'window.close();';
    $str = set_page_header();
    return $str;
}


if ($action == 'store') echo store_reminder();
else                    echo set_reminder($ID_s, $module);

?>

</div>
</body>
</html>
