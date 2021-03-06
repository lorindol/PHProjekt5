<?php
/**
 * contacts form view
 *
 * @package    contacts
 * @subpackage external contacts
 * @author     Albrecht Guenther, $Author: gustavo $
 * @licence    GPL, see www.gnu.org/copyleft/gpl.html
 * @copyright  2000-2006 Mayflower GmbH www.mayflower.de
 * @version    $Id: contacts_forms.php,v 1.105 2008-02-25 16:55:44 gustavo Exp $
 */

if (!defined('lib_included')) die('Please use index.php!');
include_once(LIB_PATH.'/access_form.inc.php');
// selector-tranformation stuff
require_once(LIB_PATH.'/selector/selector.inc.php');

if (isset($project_personen)) {
    update_project_personen_table($ID, $project_personen,'project');
}

// ***********
// case import
if ($import){
    include_once('./contacts_import_forms.php'); }

    // ******************
    // create/edit contact
    // ******************

    else {
        // check permission and fetch values for viewing or modifying a record
        if ($ID > 0) {
            // check permission
            $result = db_query("SELECT ID, von, acc_write,gruppe
                                  FROM ".DB_PREFIX."contacts
                                 WHERE ID = ".(int)$ID."
                                   AND is_deleted is NULL
                                   AND (acc_read like 'system' or ((von = ".(int)$user_ID." or acc_read like 'group' or acc_read like '%\"$user_kurz\"%')".group_string()."))") or db_die();
            $row = db_fetch_row($result);
            if (!$row[0]) { die("You are not privileged to do this!"); }
            if ($row[1] <> $user_ID and $row[2] <> 'w') { $read_o = 1; }
            else $read_o = 0;
            if ($row[1] <> $user_ID and PHPR_ALTER_ACC!=1) $read_acc = 1;
            else $read_acc = 0;
            change_group($row[3]);
            // fetch values from db
            $query = "SELECT anrede,vorname,nachname,firma,email,email2,url,tel1,tel2,mobil,fax,strasse,
                             stadt,plz,land,state,div1,div2,kategorie,parent,von,acc_write,bemerkung,acc_read
                        FROM ".DB_PREFIX."contacts
                       WHERE ID = ".(int)$ID."
                         AND is_deleted is NULL";
            $result = db_query($query) or db_die();
            $row = db_fetch_row($result);
            touch_record('contacts', $ID);
        }


        //unset ID when copying project
        $ID=prepare_ID_for_copy($ID,$copyform);
        // **********
        // start form
        if($ID)$head=slookup('projekte','name','ID',$ID,'1');
        else $head=__('New contact');
        if(!$head) $head=__('New contact');
        if ($approve_contacts)$head=__('Import');


        /******************************
        *           tabs
        ******************************/
        $tabs = array();
        // $tabs[] = array('href' => $_SERVER['SCRIPT_NAME'], 'active' => false, 'id' => 'tab2', 'target' => '_self', 'text' => __('Export'), 'position' => 'right');
        $buttons = array();
        // form start
        $hidden = array('mode'=>'data','input'=>1,'ID' => $ID, 'action' => $action);
        foreach ($view_param as $key => $value) {
            $hidden[$key]= $value;
        }
        if (SID) $hidden[session_name()] = session_id();
        $buttons[] = array('type' => 'form_start', 'hidden' => $hidden, 'enctype' => 'multipart/form-data', 'name' => 'frm');

        $output .= breadcrumb($module, breadcrumb_data($action));
        $output .= '</div>';
        $output.=get_buttons($buttons);
        $output .= $content_div;
        /******************************
        *         buttons
        ******************************/
        $buttons = get_default_buttons($read_o,$ID,$justform,'contacts',true,$sid);

        if (!$read_o and check_role("contacts") > 1 and $approve_contacts) {
            $hidden = array('mode'=>'data','input'=>1);
            if (SID) $hidden[session_name()] = session_id();
            // $buttons[] = array('type' => 'form_start', 'hidden' => $hidden);
            // text
            $buttons[] = array('type' => 'text', 'text' => '<b>&nbsp;&nbsp;&nbsp;'.__('Import list').' </b>');
            // approve contacts
            $buttons[] = array('type' => 'submit', 'name' => 'imp_approve', 'value' => __('approve'), 'active' => false);
            $buttons[] = array('type' => 'submit', 'name' => 'imp_undo', 'value' => __('undo'), 'active' => false);
            // form end
            $buttons[] = array('type' => 'form_end');
        }
        // export vcard
        if (!$import_contacts && $row[2] && $ID > 0) {
            $buttons[] = array('type' => 'link', 'href' => 'vcard_ex.php?contact_ID='.$ID.'&name='.urlencode($row[2]).'&amp;csrftoken='.make_csrftoken().'&amp;'.SID, 'text' => __('create vcard'), 'active' => false);
        }
        $output .= get_buttons_area($buttons);

        //array for output
        $out_array=array();

        /*******************************
        *       basic fields
        *******************************/
        $basic_fields = build_form($fields);

        /******************************
        *       categorization
        ******************************/
        $form_fields = array();
        $cat = "<br /><label for='parent' class='label_block'>".__('Parent object').":</label>\n";
        // value of the parent
        if (!isset($parent)) {
            if (!isset($_POST[$parent])) $str_parent = $row[19];
            else $str_parent = xss($_POST[$parent]);
        } else $str_parent = $parent;

        $cat .= selector_create_select_contacts('parent',$str_parent, 'action_form_to_contact_selector', $ID);
        if (PHPR_CONTACTS_PROFILES) {
            // fetch all profiles where the contact is member
            $result3 = db_query("SELECT ".DB_PREFIX."contacts_profiles.ID, name
                                   FROM ".DB_PREFIX."contacts_profiles, ".DB_PREFIX."contacts_prof_rel
                                  WHERE contacts_profiles_ID = ".DB_PREFIX."contacts_profiles.ID
                                    AND ".DB_PREFIX."contacts_profiles.is_deleted is NULL
                                    AND ".DB_PREFIX."contacts_prof_rel.is_deleted is NULL
                                    AND contact_ID = ".(int)$ID) or db_die();
            while ($row3 = db_fetch_row($result3)) { $profile_member[] = $row3[0]; }
            $cat .=  "<br /> <br /><label for='profile_lists' class='center2'>".__('Profiles')."</label>\n";
            $cat .=  "<select name='profile_lists[]' id='profile_lists' multiple='multiple' size='6' ".read_o($read_o).">
        <option value=''></option>\n";
            // show all profiles
            $result3 = db_query("SELECT ID, name
                                   FROM ".DB_PREFIX."contacts_profiles
                                  WHERE von = ".(int)$user_ID." 
                               ORDER BY name") or db_die();
            while ($row3 = db_fetch_row($result3)) {
                $cat .= "<option value=$row3[0]";
                // compare the array of profiles where the contact is listed with the current profile - if yes, mark it as selected
                // the first condition is there to avoid that a warning will appear if the array is empty - IMO a silly warning because it wouldn't harm anything ...
                if ($profile_member[0] > 0) { if (in_array($row3[0], $profile_member)) {$cat .=" selected"; }}
                $cat .= "> $row3[1]</option>\n";
            }
            $cat .="</select>\n";
        }

        $form_fields[] = array('type' => 'parsed_html', 'html' => $cat);
        $categorization_fields = get_form_content($form_fields);
        $form_fields = array();

        // values of the organisations
        if (!isset($organisations)) {
            if (!isset($_POST['organisations'])) {
                $organisations = array();
                $tmp_result = db_query("SELECT organisation_ID
                                          FROM ".DB_PREFIX."organisation_contacts_rel
                                         WHERE contact_ID = ".(int)$ID) or db_die();
                while ($tmp_row = db_fetch_row($tmp_result)) {
                    $organisations[] = $tmp_row[0];
                }
            } else $organisations = xss_array($_POST['organisations']);
        }

        $organisation_assignment =  "";
        $organisation_assignment .= selector_create_select_organisations('organisations[]',$organisations, 'action_form_to_organisation_selector', '','',5,1);

        
        // table of selected contact
        $organisation_table = '
    <table class="relations">
        <caption>'.__('Organisations').'</caption>
        <thead>
            <tr>
                <td title="'.__('Name').'">'.__('Family Name').'</td>
                <td title="'.__('Role').'">'.__('Role').'</td>
                <td title="'.__('or new Role').'">'.__('or new Role').'</td>
            </tr>
        </thead>
        <tbody>';

        if (isset($ID)&&($ID!=0)) {
            $query = "SELECT organisation_ID, role FROM ".DB_PREFIX."organisation_contacts_rel
                       WHERE contact_ID = ".(int)$ID;
            $result = db_query($query) or db_die();
            while ($row = db_fetch_row($result)) {
                $organisation_query = "SELECT ID, name
                                         FROM ".DB_PREFIX."organisations
                                        WHERE ID = ".(int)$row[0]."
                                          AND is_deleted is NULL";
                $organisation_result = db_query($organisation_query) or db_die();
                $organisation_row = db_fetch_row($organisation_result);
                $organisation_table .= "
            <tr>
                <td>".$organisation_row[1]."</td>
                <td>".make_select_roles($row[0],$row[1],'contacts','organisation')."</td>
                <td><input name='c_".$row[0]."_text_role' type='text' size='8' maxlength='200'></td>
            </tr>
                ";
            }
        } else if (is_array($organisations)&&(!empty($organisations))) {
            foreach($organisations as $tmp => $organisation_id) {
                $organisation_query = "SELECT ID, name
                                         FROM ".DB_PREFIX."organisations
                                        WHERE ID = ".(int)$organisation_id."
                                          AND is_deleted is NULL";
                $organisation_result = db_query($organisation_query);
                $organisation_row = db_fetch_row($organisation_result);
                $organisation_role_str = 'c_'.$organisation_id.'_role';
                $organisation_role = (isset($_POST[$organisation_role_str])) ? xss($_POST[$organisation_role_str]) : '';
                $organisation_table .= "
            <tr>
                <td>".$organisation_row[1]."</td>
                <td>".make_select_roles($organisation_id,$organisation_role,'contacts','organisation')."</td>
                <td><input name='c_".$organisation_id."_text_role' type='text' size='8' maxlength='200'></td>
            </tr>
                ";
            }
        }
        $organisation_table .= '</tbody></table>';

        $organisation_form = '
        <div style="float:left;width:30%;padding:10px;">'.$organisation_assignment.'</div>
        <div style="float:right;width:50%;padding:10px;">'.$organisation_table.'</div>
        <br style="clear:both"/>
        ';
        $form_fields[] = array('type' => 'parsed_html', 'html' => $organisation_form);

        $organisation_fields = get_form_content($form_fields);


        /******************************
        *         assignment
        ******************************/
        include_once("../lib/access_form.inc.php");
        $form_fields = array();

        // values of the access
        if (!isset($persons)) {
            if (!isset($_POST[$persons])) $str_persons = $row[23];
            else $str_persons = xss($_POST[$persons]);
        } else $str_persons = $acc = serialize($persons);

        if (!isset($acc_write)) {
            if (!isset($_POST['acc_write'])) $acc_write = $row[21];
            else $acc_write = xss($_POST['acc_write']);
        }

        // acc_read, exclude the user itself, acc_write, no parent possible, write access=yes
        $form_fields[] = array('type' => 'parsed_html', 'html' => access_form($str_persons, 1, $acc_write, 0, 1,'acc',$read_acc));
        $assignment_fields = get_form_content($form_fields);
        
        $out_array = array_merge($out_array,$basic_fields);
        $out_array[]=array(__('Categorization'),$categorization_fields);
        $out_array[]=array(__('Organisation'), $organisation_fields);
        $out_array[]=array(__('Release'),$assignment_fields);

        if (!$read_o ) {
            // Projects
            if ($ID > 0) {
                if (PHPR_PROJECTS and check_role("projects") > 0) {
                    include_once(LIB_PATH."/selector/selector.inc.php");
                    global $date_format_object;

                    // values of the projects
                    if (!isset($project_personen)) {
                        if (!isset($_POST[$project_personen])) {
                            $tmp_result = db_query("SELECT project_ID
                                                      FROM ".DB_PREFIX."project_contacts_rel
                                                     WHERE contact_ID = ".(int)$ID."
                                                       AND is_deleted is NULL") or db_die();
                            while ($tmp_row = db_fetch_row($tmp_result)) {
                                $project_personen[] = $tmp_row[0];
                            }
                        } else $project_personen = xss_array($_POST[$project_personen]);
                    }

                    $project_assignment = '
                <fieldset>
                <legend>'.__('Projects').'</legend>
                '. selector_create_select_projects('project_personen[]', $project_personen, 'action_form_to_project_selector', '0', '', '7', '1') .'<br />
                </fieldset>
                ';

                    // table of selected projects
                    $project_table = '
                        <table class="relations">
                                <caption>'.__('Projects').'</caption>
                                <thead>
                                    <tr>
                                        <td title="'.__('Title').'">'.__('Title').'</td>
                                        <td title="'.__('Begin').'">'.__('Begin').'</td>
                                        <td title="'.__('End').'">'.__('End').'</td>
                                        <td title="'.__('Role').'">'.__('Role').'</td>
                                        <td title="'.__('or new Role').'">'.__('or new Role').'</td>
                                    </tr>
                                </thead>
                                <tbody>';

                    $query = "SELECT project_ID, role
                                FROM ".DB_PREFIX."project_contacts_rel
                               WHERE contact_ID = ".(int)$ID."
                                 AND is_deleted is NULL";
                    $result = db_query($query) or db_die();

                    while ($row = db_fetch_row($result)) {
                        $project_query = "SELECT ID, name, anfang, ende
                                            FROM ".DB_PREFIX."projekte
                                           WHERE ID = ".(int)$row[0]."
                                             AND is_deleted is NULL";
                        $project_result = db_query($project_query) or db_die();
                        $project_row = db_fetch_row($project_result);
                        $project_table .= "
                            <tr>
                                <td>".$project_row[1]."</td>
                                <td>".$date_format_object->convert_db2user($project_row[2])."</td>
                                <td>".$date_format_object->convert_db2user($project_row[3])."</td>
                                <td>".make_select_roles($row[0],$row[1],'contacts')."</td>
                                <td><input name='".$row[0]."_text_role' type='text' size='8' maxlength='200'></td>
                            </tr>";
                    }
                    $project_table .= '</tbody></table>';                  
                    $out_array[]=array(__('Projects'),'
                <div style="float:left;width:20%;padding:10px;">'.$project_assignment.'</div>
                <div style="float:right;width:66%;padding:25px;">'.$project_table.'</div>
                <br style="clear:both"/>
                ');




                }

            }

        }

        if (!$read_o ){
            /******************************
            *    show related objects
            ******************************/
            if ($ID > 0) {
                $output2 = '';
                $output2 .= "<br />\n";
                $referer = "contacts.php?mode=forms&amp;ID=$ID";
                $contact_ID = $ID;
                // include the lib
                include_once(LIB_PATH."/show_related.inc.php");

                // show related todos
                if (PHPR_TODO and check_role("todo") > 0) {
                    $query = "contact = ".(int)$ID;
                    $output2 .= show_related_todo($query,$referer);
                }
                // related notes, show only for existing projects
                if (PHPR_NOTES and check_role("notes") > 0) {
                    $query = "contact = ".(int)$ID;
                    $output2 .= show_related_notes($query,$referer);
                }
                // show related files
                if (PHPR_FILEMANAGER and check_role("filemanager") > 0) {
                    $query = "contact=".(int)$ID;
                    $output2 .= show_related_files($query,$referer);
                }

                // show related events
                if (PHPR_CALENDAR and check_role("calendar") > 0) {
                    $query = "contact = ".(int)$ID;
                    $output2 .= show_related_events($query,$referer);
                }

                // show related helpdesk
                if (PHPR_RTS > 0 && check_role("helpdesk") > 0) {
                    $query = "contact = ".(int)$ID;
                    $output2 .= show_related_helpdesk($query,$referer);
                }

                // show related emails
                if (PHPR_QUICKMAIL > 0 && check_role("mail") > 0) {
                    $query = "contact = ".(int)$ID;
                    $output2 .= show_related_mail($query,$referer);
                }

                // show history
                if (PHPR_HISTORY_LOG == 2) {
                    $output2 .= history_show('contacts', $ID);
                }
            }
        }

        $out_array[]=array(__('Related Objects'),$output2);
    }

    // close the big form
    $output.= generate_output($out_array);
    $out_array=array();
    $output .= '<div class="hline"></div>';
    $output .= get_buttons_area($buttons);
    $output .= '<div class="hline"></div>';
    $output .= '</form>';

    echo $output;
?>
