<?php

// mail_view.php - PHProjekt Version 5.2
// copyright  ©  2000-2005 Albrecht Guenther  ag@phprojekt.com
// www.phprojekt.com
// Author: Albrecht Guenther, $Author: polidor $
// $Id: mail_view.php,v 1.84.2.8 2007/05/27 00:41:07 polidor Exp $

// check whether the lib has been included - authentication!
if (!defined("lib_included")) die("Please use index.php!");

// check role
if (check_role("mail") < 1) die("You are not allowed to do this!");

// sadly enough here comes the third check whether the imap library is installed ;-)
if (!function_exists('imap_open')) die("Sorry but the full functionality of the mail client requires the imap-extension
                                        of php. Please ensure that this extension is active on your system.<br />
                                        In the meantime if you want to use the mail send module, set PHPR_QUICKMAIL=1; in the config.inc.php");

include_once(LIB_PATH."/email_getpart.inc.php");
sort_mode($module,'date_inserted');
read_mode($module);
archive_mode($module);
html_editor_mode($module);
group_mode($module);

$set_archiv_flag = isset($set_archiv_flag) ? (int) $set_archiv_flag : 0;
$set_read_flag = isset($set_read_flag) ? (int) $set_read_flag : 0;
if ($set_archiv_flag > 0) set_archiv_flag($ID_s,$module);
if ($set_read_flag > 0) set_read_flag($ID_s,$module);
if (isset($save_tdwidth)) store_column_width($module);
$up = isset($up) ? (int) $up : 0;
$sort = isset($sort) ? qss($sort) : '';
$tree_mode = isset($tree_mode) ? qss($tree_mode) : '';
$is_related_obj = isset($is_related_obj) ? (bool) $is_related_obj : false;
$rule = isset($rule) ? check_rule($rule) : '';
$filter_ID = isset($filter_ID) ? (int) $filter_ID : null;
$operator = isset($operator) && $operator == 'OR' ? $operator : ' AND ';

// unset all references to attachments and let the script create them
//session_unregister("file_ID");
//unset($file_ID);
unreg_sess_var($file_ID);

// seems that the last function doesn't work properly on some php installations -> additional function to make sure that ...
$file_ID = array();

// fields for rules
$rules_fields = array("subject" => __('Subject'),
"body" => __('Body'),
"sender" => __('Sender'),
"recipient" => __('Receiver'),
"cc" => "Cc");
// action for rules
$rules_action  = array("copy" => __('Copy'), "move" => __('Move'), "delete" => __('Delete'));

// ***************
// fetch new mails
// ***************
$outmail = '';
if ($action == "fetch_new_mail" || $action == "process_new_mail") {

    // take care of download and delete arrays if is process_new_emails
    if ($action == "process_new_mail") {
        $_REQUEST['view_only'] = 0;
    }
    else {
        $_POST['download'] = 'all';
        $_POST['delete']   = 'all';
    }

    include("./mail_fetch.php");
    include_once(LIB_PATH."/fetchmail.php");

    // if no special account is given - loop over all mail accounts
    if (!$account_ID) {
        $where = ' collect = 1 '; // select all account set as default collect emails
    }
    else {
        $where = " ID = ".(int)$account_ID." "; // select one specific account
    }

    $result = db_query("SELECT ID, von, accountname, hostname, type, username, password, deletion
                               FROM ".DB_PREFIX."mail_account 
                               WHERE von = ".(int)$user_ID." AND $where") or db_die();

    while ($row = db_fetch_row($result)) {
        
        $account_ID = $row[0];

        $list = '';

        // check if it is an imap type account
        if(ereg('imap',$row[4])) {
            $imap = "on";
        }
        else {
            $imap = "off";
        }


        // mail server connection
        $conn = new fetchmail($row[3], $row[5], $row[6], $row[4]);
        
        if ($row[7] == 1) {
            $checked         = 'checked ';
            if ($action <> "process_new_mail") {
                $_POST['delete'] = 'all';
                $no_delete = false;
            }
        }
        else {
            $check = '';
            if ($action <> "process_new_mail") {
                if ($_POST['delete'] == 'all') {
                    $_POST['delete'] = array();
    
                }
            }
            $no_delete = true;
        }


        if (xss($_REQUEST['view_only']) == 1) {
            $mails_on_server = $conn->get_mail_list();



            if (is_array($mails_on_server) && count($mails_on_server) > 0) {

                $list = "<table 'width=100%'>
                                      
                              <form name='messages' action='mail.php' method='post'>
                                  <input type='hidden' name='mode' value='view' />
                                  <input type='hidden' name='action' value='process_new_mail' />
                                  <input type='hidden' name='view_only' value='1' />
                                  <input type='hidden' name='account_ID' value='{$row[0]}' />
                                  <thead>
                                      <tr>
                                          <th align='center'>".__("Download")."</th>
                                          <th align='center'>".__("Delete on Server")."</th>
                                          <th align='center'>".__("Subject")."</th>
                                          <th align='center'>".__("Sender")."</th>
                                          <th align='center'>".__("Date")."</th>
                                          <th align='center'>".__("Size")."</th>
                                      </tr>
                                  </thead>";
                foreach ($mails_on_server as $msgno => $one_message) {

                    $list .= "
                                  <tr>
                                    <td><input type='checkbox' name='download[]' value='$msgno' checked /></td>
                                    <td><input type='checkbox' name='delete[]' value='$msgno' $checked /></td>
                                    <td>{$one_message['subject']}</td>
                                    <td>{$one_message['from']}</td>
                                    <td>{$one_message['date']}</td>
                                    <td align='right'>{$one_message['size']}</td>
                                  </tr>";
                }
                $list .= "
                              </table>";
                $list .= "    <input type='submit' class='button' name='Update' value='Update {$row[2]}' />
                          </form>
                          <br />";
            }
            else {
                $list .= "<br />$user_name / $row[2]: ".__('No new emails on server');
            }
        }
        elseif (xss($_REQUEST['list_mails']) == 1) {
            
            $list .= $conn->get_mail_table();
            
        }
        else {

            $total_size = 0;

            $total_messages = 0;

            $conn->get_mail($_POST['download'],$_POST['delete'], PHPR_ATT_PATH);

            foreach ($conn->email as $emailNbr => $oneEmail) {
                
                $message_ID = addslashes($oneEmail['message_ID']);
                    
                $skip_mail = 0;

                // check whether this message is already in the database
                // NOTE: only if download all emails we will made this check, to allow to download more than one time
                //       each email in case of be necessary
                if (($no_delete || $imap=="on") && $oneEmail['message_ID'] <> '' && $_POST['download'] == 'all') {

                    if (!empty($message_ID)) {

                        $result3 = db_query("SELECT ID
                                                    FROM ".DB_PREFIX."mail_client
                                                    WHERE message_ID = '".(int) $message_ID."'") or db_die();
    
                        $row3 = db_fetch_row($result3);
    
                        if ($row3[0] > 0) {
                            $skip_mail = 1;
                        }
                    }
                }

                // if there is already an entry in
                if (!$skip_mail) {


                    $result_rules = apply_rules($oneEmail);

                    $parents        = $result_rules['parents'];
                    $parent_contact = $result_rules['parent_contact'];
                    $parent_project = $result_rules['parent_project'];
                    $mail2contact   = $result_rules['mail2contact'];

                    // try to assign the mail2contact - overrules some special rules if exist
                    if ($mail2contact == 1) {

                        if (ereg("&lt;",$oneEmail['sender'])) {
                            $sender_tmp   = explode("&lt;",$oneEmail['sender']);
                            $sender_email = substr($sender_tmp[1],0,-4);
                        }
                        elseif (ereg("<",$oneEmail['sender'])) {
                            $sender_tmp   = explode("<",$oneEmail['sender']);
                            $sender_email = substr($sender_tmp[1],0,-1);
                        }
                        else {
                            $sender_email = $oneEmail['sender'];
                        }

                        $result3 = db_query("SELECT ID
                                                 FROM ".DB_PREFIX."contacts 
                                                 WHERE (acc LIKE 'system' 
                                                        OR ((von = ".(int)$user_ID." OR acc LIKE 'group' OR acc LIKE '%\"$user_kurz\"%') 
                                                        ".group_string($module).")) 
                                                        AND email LIKE '$sender_email'") or db_die();

                        $row3 = db_fetch_row($result3);

                        if ($row3[0] > 0) {
                            $parent_contact = $row3[0];
                        }
                    } // end assign mail2contact

                    $i = 0;
                    while (isset($parents[$i])) {
                        if ($parent_contact == 0) {
                            $parent_contact = 0;
                        }
                        if ($parent_project == 0) {
                            $parent_project = 0;
                        }
                        
                        $oneEmail['message_ID'] = addslashes($oneEmail['message_ID']);

                        // save mail to db
                        $query = "INSERT INTO ".DB_PREFIX."mail_client
                                                   (von,                  subject,          body,               sender,          recipient,          cc,date_received,touched,typ,parent, date_sent,            header,          replyto,          body_html,           contact,          projekt ,  message_ID, gruppe , date_inserted , account_ID ) 
                                            VALUES (".(int)$user_ID.",'{$oneEmail[subject]}','{$oneEmail[body_text]}','{$oneEmail[sender]}','{$oneEmail[recipient]}','{$oneEmail[cc]}','$dbTSnull',0,'m',".(int)$parents[$i].",'{$oneEmail[date]}','{$oneEmail[header]}','{$oneEmail[replyto]}','{$oneEmail[body_html]}',".(int)$parent_contact.",".(int)$parent_project.",'$message_ID', ".(int)$user_group.", '$dbTSnull' ,".(int)$account_ID.")";
                        
                        $result3 = db_query($query) or db_die();

                        // fetch ID as reference for attachment storage
                        $result3 = db_query("SELECT max(ID)
                                             FROM ".DB_PREFIX."mail_client 
                                             WHERE von = ".(int)$user_ID."  
                                             AND date_received LIKE '$dbTSnull'") or db_die();

                        $row3 = db_fetch_row($result3);

                        $mail_ID = $row3[0];

                        // process attachments
                        if (isset($oneEmail['attachment']) && count($oneEmail['attachment']) > 0) {

                            // loop over all attachments
                            foreach ($oneEmail['attachment'] as $a => $oneAttachment) {

                                // fetch filesize
                                $att_size = $oneAttachment['att_size'];

                                // fetch original name
                                $att_name = $oneAttachment['att_name'];

                                // fetch attachment file name
                                $att_tempname = $oneAttachment['att_tempname'];

                                $result3 = db_query("INSERT INTO ".DB_PREFIX."mail_attach
                                                               (parent,  filename,       tempname,   filesize ) 
                                                        VALUES (".(int)$mail_ID.",'$att_name','$att_tempname',".(int)$att_size.")") or db_die();

                                $total_size += $att_size;

                            }  // end loop over attachments

                        }  // end if there are attachments
                        $i++;
                    } // end loop over different rules (parents)

                    $total_messages++;



                } // end bracket to skip mail because it's already in the db
                else {
                    @unlink(PATH_PRE.$file_path.'/'.$att_tempname);
                }

            }

            $list .= "<br />$user_name / $row[2]: $total_messages ".__('records')." ($total_size Bytes) ";

        }


        $outmail.= $list;
    } // end of while accounts to be checked


    $action = "";
} // end fetch mail
// *************

// *********
// list view
// *********

if (!$action) {

    // context menu

    // entries for right mouse menu - action for single record
    $listentries_single = array();

    $csrftoken = make_csrftoken();

    // entries for right mouse menu - action for selected records
    $listentries_selected = array(
    '0'=>array('proc_marked',PATH_PRE."$module/$module.php?mode=data&amp;up=$up&amp;sort=$sort&amp;tree_mode=$tree_mode&amp;csrftoken=$csrftoken&amp;delete_c=1&amp;ID_s=",'','',__('Delete')),
    '1'=>array('proc_marked',PATH_PRE."lib/set_links.inc.php?module=$module&amp;csrftoken=$csrftoken&amp;ID_s=",'_blank','',__('Add to link list')),
    '2'=>array('proc_marked',PATH_PRE.$module."/".$module.".php?set_archiv_flag=1&amp;csrftoken=$csrftoken&amp;ID_s=",'','',__('Move to archive')),
    '3'=>array('proc_marked',PATH_PRE.$module."/".$module.".php?set_read_flag=1&amp;csrftoken=$csrftoken&amp;ID_s=",'','',__('Mark as read')),
    );

    $listentries_single = array (
    '1'=>array('proc_marked',PATH_PRE.$module."/".$module.".php?mode=send_form&amp;form=email&amp;action2=reply&amp;csrftoken=$csrftoken&amp;ID=",'','',__('Reply')),
    '2'=>array('proc_marked',PATH_PRE.$module."/".$module.".php?mode=send_form&amp;form=email&amp;action2=replyall&amp;csrftoken=$csrftoken&amp;ID=",'','',__('Reply to all')),
    '3'=>array('proc_marked',PATH_PRE.$module."/".$module.".php?mode=send_form&amp;form=email&amp;action2=forward&amp;csrftoken=$csrftoken&amp;ID=",'','',__('Forward')),
    '3'=>array('proc_marked',PATH_PRE.$module."/".$module.".php?mode=send_form&amp;action2=addContact&amp;csrftoken=$csrftoken&amp;ID=",'','',__('Add to Contacts')),
    );

    // context menu
    include_once(LIB_PATH.'/contextmenu.inc.php');
    $link = isset($link) ? xss($link) : '';
    contextmenu::draw_contextmenus($module, $link, $is_related_obj, $listentries_single, $listentries_selected, $contextmenu);
    // end context menu
    // ****************

    //anfang navi


    // call the main filter routine
    // $where = "1=1 ";
    $where = main_filter($filter,$rule,$keyword,$filter_ID,'mail','',$operator);

    $result = db_query("select ID
                             from ".DB_PREFIX."mail_client
                             ".sql_filter_flags($module, array('archive', 'read'))."
                             WHERE 
                             (von = ".(int)$user_ID." OR (acc LIKE 'system' OR
                              ((von = ".(int)$user_ID."  
                                OR acc LIKE 'group'
                                OR acc LIKE '%\"$user_kurz\"%')
                               ".group_string($module)."))) ".sql_filter_flags($module, array('archive', 'read'), false)." 
                               $where 
                             ".sort_string()) or db_die();

    $liste= make_list($result);

    //tabs
    $tabs = array();
    $output = '<div id="global-header">';
    $output .= get_tabs_area($tabs);
    $output .= breadcrumb($module);
    $output .= '</div>';

    // button bar
    $buttons = array();
    $buttons[] = array('type' => 'link', 'href' => $_SERVER['SCRIPT_NAME'].'?mode=send_form&amp;form=email'.$sid.'&amp;csrftoken='.make_csrftoken(), 'text' => __('New'), 'active' => false);

    $buttons[] = array('type' => 'link', 'href' => $_SERVER['SCRIPT_NAME'].'?mode=view&amp;action=fetch_new_mail&amp;sort='.$sort.'&amp;up='.$up.'&amp;view_only=1'.$sid, 'text' => __('Mails on server'), 'active' => false);
    $buttons[] = array('type' => 'link', 'href' => $_SERVER['SCRIPT_NAME'].'?mode=view&amp;action=fetch_new_mail&amp;sort='.$sort.'&amp;up='.$up.$sid.'&amp;csrftoken='.make_csrftoken(), 'text' => __('Receive'), 'active' => false);
    //$buttons[] = array('type' => 'link', 'href' => $_SERVER['SCRIPT_NAME'].'?mode=view&amp;action=fetch_new_mail&amp;sort='.$sort.'&amp;up='.$up.'&amp;no_del=1'.$sid, 'text' => __('and leave on server'), 'active' => false);
    $buttons[] = array('type' => 'link', 'href' => $_SERVER['SCRIPT_NAME'].'?mode=forms&amp;form=d'.$sid.'&amp;csrftoken='.make_csrftoken(), 'text' => __('Directory').' '.__('Create'), 'active' => false);
    $buttons[] = array('type' => 'link', 'href' => $_SERVER['SCRIPT_NAME'].'?mode=options'.$sid.'&amp;csrftoken='.make_csrftoken(), 'text' => __('Options'), 'active' => false);
    $buttons[] = array('type' => 'link', 'href' => $_SERVER['SCRIPT_NAME'].'?mode=data&amp;action=empty_trash_can&amp;sort='.$sort.'&amp;up='.$up.$sid.'&amp;csrftoken='.make_csrftoken(), 'text' => __('Empty Trash Can'), 'active' => false);
    $output .= '<div id="global-content">';
    $output .= get_buttons_area($buttons, 'oncontextmenu="startMenu(\''.$menu3->menusysID.'\',\'\',this)"');

    $sql= " WHERE 
            (von = ".(int)$user_ID." OR
            (acc LIKE 'system' OR
              ((von = ".(int)$user_ID." 
                OR acc LIKE 'group'
                OR acc LIKE '%\"$user_kurz\"%')
               ".group_string($module).")))
            ".sql_filter_flags($module, array('archive', 'read'), false)." 
            $where 
            ".sort_string();

    $result_rows = build_table(array('ID','von','message_ID','parent'), $module, $sql, $_SESSION['page'][$module], $perpage);
    $output .= '<a name="content"></a>'.get_all_filter_bars('mail',$outmail.'<br />'.$result_rows);
    $output .= '</div>';
    echo $output;
    $_SESSION['file_ID'] =& $file_ID;
}

?>
