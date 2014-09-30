<?php
/**
* calendar form handler
*
* @package    calendar
* @module     main
* @author     Albrecht Guenther, $Author: polidor $
* @licence    GPL, see www.gnu.org/copyleft/gpl.html
* @copyright  2000-2006 Mayflower GmbH www.mayflower.de
* @version    $Id: calendar_forms.php,v 1.167.2.3 2007/04/28 15:01:27 polidor Exp $
*/
if (!defined('lib_included')) die('Please use index.php!');

// functions for date-checking if invitees are present
require_once('./calendar_dateconflicts.php');
// selector-tranformation stuff
require_once(LIB_PATH.'/selector/selector.inc.php');

/**
 * get the main form view.
 *
 * @return string  the main form view
 */
function calendar_forms_get_view() {
    global $ID, $formdata, $day, $month, $year, $user_ID, $act_for;
    global $view, $read_o, $only_readable, $settings, $cal_visi;

    if ($ID) {
        // edit/show an existing event: fetch values from database
        if (!$formdata) {
            // get data from db for the first time..
            unset($_SESSION['calendardata']['current_event']);
            if (!$formdata = calendar_get_event($ID)) {
                message_stack_in(__('Problems getting event data from db!'), 'calendar', 'error');
                return;
            }
        }
        // check if this event can be edit completely, in part, not at all or is only visible
        // completely   = if this is my own event or I am a proxy (and other access rights do not match)
        // in part      = if I am an invitee of that event
        // not at all   = if I only have read access
        // only visible = get out with an error
        if (!calendar_can_read_events($_SESSION['calendardata']['current_event']['an'],
                                      $_SESSION['calendardata']['current_event']['visi'])) {
            unset($_SESSION['calendardata']['current_event']);
            message_stack_in(__('You are not allowed to read this event!'), 'calendar', 'error');
            return;
        }
        else if (!calendar_can_edit_events($_SESSION['calendardata']['current_event']['an'],
                                           $_SESSION['calendardata']['current_event']['visi'])) {
            $only_readable = true;
        }
        else {
            $only_readable = false;
        }
        // if $only_readable == true then all fields are read only
        // if parent != 0 then this is an invitation event and
        //                edit access is given only on some fields
        // if parent == 0 then this event is full editable
        $read_o = ($only_readable) ? $only_readable : $_SESSION['calendardata']['current_event']['parent'];
    }
    else {
        // create a new event
        unset($_SESSION['calendardata']['current_event']);
        if ( !$formdata['invitees'] && isset($_SESSION['calendardata']['combisel']) &&
            count($_SESSION['calendardata']['combisel']) > 0 && $view == 3 ) {
            $formdata['invitees'] = $_SESSION['calendardata']['combisel'];
        }
        if (empty($formdata['visi'])) {
            $formdata['visi'] = (!empty($cal_visi)) ? $cal_visi : PHPR_DEFAULT_VISI;
        }
        $formdata['visi'] = (int) $formdata['visi'];
        if (empty($formdata['partstat'])) {
            $formdata['partstat'] = 2;
        }
        else {
            $formdata['partstat'] = (int) $formdata['partstat'];
        }
        if (empty($formdata['anfang'])) {
            $formdata['anfang'] = (($settings['tagesanfang']) ? $settings['tagesanfang'] : PHPR_DAY_START).'00';
            $formdata['anfang'] = substr('0'.$formdata['anfang'], -4);
            //$formdata['ende']   = (($settings['tagesanfang']) ? $settings['tagesanfang'] + 1 : PHPR_DAY_START + 1).'00';
            //$formdata['ende']   = substr('0'.$formdata['ende'], -4);
        }
        else {
            $formdata['anfang'] = preg_replace('~[^\d]~', '', $formdata['anfang']);
        }
        $formdata['event'] = html_out(stripslashes($formdata['event']));
        $day    = substr('0'.$day, -2);
        $month  = substr('0'.$month, -2);
        $read_o = 0;
        $only_readable = false;
    }
    if (empty($formdata['datum'])) {
        $formdata['datum'] = "$year-$month-$day";
    }
    elseif (!preg_match('/^\d{0,4}-\d{0,2}-\d{0,2}$/', $formdata['datum'])) {
        $formdata['datum'] = date('Y-m-d');
    }
    $serial_events = calendar_forms_get_serial_event_links();

    $day   = (int) substr($formdata['datum'], -2);
    $month = (int) substr($formdata['datum'], 5, 2);
    $year  = (int) substr($formdata['datum'], 0, 4);

    // prepare invitees stuff for the form
    settype($formdata['invitees'], 'array');
    $current_user = ($act_for) ? $act_for : $user_ID;
    if (!in_array($current_user, $formdata['invitees'])) {
        $formdata['invitees'][] = $current_user;
    }

    $user_list = calendar_forms_prepare_invitees($current_user);

    // proxy act for, foreign entry
    if ($view == 4) {
        if (!$act_for && $formdata['an'] != $user_ID && calendar_can_act_for($formdata['an'])) {
            $act_for = $formdata['an'];
        }
    }

    $ret = '';
    // check if the last submit was an action to remove an event..
    if (isset($_REQUEST['action_remove_event'])) {
        $ret .= calendar_forms_show_delete_event_form('event');
    }
    else if (isset($_REQUEST['action_remove_serial'])) {
        $ret .= calendar_forms_show_delete_event_form('serial');
    }
    else {
        $ret .= calendar_forms_show_main_form();
    }

    if (count($formdata['invitees']) > 0 && !$only_readable) {
        $colliding_invitees = array();

        if (isset($_REQUEST['action_check_dateconflict'])) {
            $formdata_ende      = calendar_calculate_endtime($formdata);
            $colliding_invitees = check_concrete_date($formdata['invitees'], $formdata['datum'],
                                                      $formdata['anfang'], $formdata_ende, $ID);
        }
        $ret .= calendar_forms_show_invitees($user_list, $colliding_invitees, $serial_events);

        if (isset($_REQUEST['action_check_dateconflict']) && empty($formdata['serie_typ'])) {
            $ret .= calendar_forms_show_dateproposals($colliding_invitees);
        }
    }
    #$ret .= "\n</div>\n";

    return $ret;
}

/**
 * get the main form header.
 *
 * @param  string $buttons
 * @return string
 */
function calendar_forms_show_main_header($buttons='') {
    global $ID, $day, $month, $year, $view, $act_for, $formdata, $date_format_object;

    $create_by = '';
    if ($ID) {
        $pagetitle = __('Deadline').' '.__('Modify').' / '.__('Delete').' ';
        if ($formdata['von'] != $formdata['an']) {
            $create_by = ' ('.__('Created by').': '.
                         slookup('users', 'nachname,vorname', 'ID', $formdata['von'],'1').')';
        }
    }
    else {
        $pagetitle = __('Create and Delete Events');
    }

    $date_format_text = __('Date format').' = '.$date_format_object->get_user_format();
    $hidden_fields = array ('ID'    => $ID,
                            'day'   => $day,
                            'month' => $month,
                            'year'  => $year,
                            'view'  => $view,
                            'mode'  => 'data');
    
    if (empty($buttons)) $buttons = '<br class="clearboth" />';
    
    $ret = '
    <form enctype="multipart/form-data" action="./calendar.php" method="post" name="frm" onsubmit="return checkUserDateFormat(\'formdata[datum]\',\''.__('Date').': '.$date_format_text.'\') &amp;&amp; checkCalendarTimeFormat(\'frm\',\'formdata[anfang]\',\''.__('From').': '.__('Please check the start time! ').'\') &amp;&amp; checkCalendarTimeFormat(\'frm\',\'formdata[ende]\',\''.__('Until').': '.__('Please check the end time! ').'\') &amp;&amp; checkUserDateFormat(\'formdata[serie_bis]\',\''.__('multiple events').'\n'.__('Until').': '.$date_format_text.'\');">
        '.hidden_fields($hidden_fields).'
        '.($act_for ? '<input type="hidden" name="act_for" value="'.$act_for.'" />' : '').'
        '.$buttons.'
            <h1>'.$pagetitle.'</h1>
            <fieldset>
            <legend>'.__('Basis data').$create_by.'</legend>

        ';

    return $ret;
}

/**
 * get the main form footer.
 *
 * @return string
 */
function calendar_forms_show_main_footer() {
    $ret = "\n    </form>\n";
    return $ret;
}

/**
 * get the main form.
 *
 * @return string
 */
function calendar_forms_show_main_form() {
    global $formdata, $ID, $day, $month, $year, $view, $read_o, $act_for, $sid;
    global $user_ID, $name_day2, $date_format_object, $only_readable;

    $af = ($act_for) ? '&amp;act_for='.$act_for : '';

    // disable some fields for invitees
    $readonly = read_o($read_o, 'readonly');
    $disabled = read_o($read_o);

    // disable all fields on read only events
    $only_readable_readonly = read_o($only_readable, 'readonly');
    $only_readable_disabled = read_o($only_readable);

    $date_format_len   = $date_format_object->get_maxlength_attribute();
    $date_format_title = $date_format_object->get_title_attribute();
    $duration_vals     = array('30'=>'0,5', '60'=>'1', '90'=>'1,5', '120'=>'2', '180'=>'3', '240'=>'4');
    
    $ret .= calendar_forms_show_main_header(calendar_forms_get_buttons_area($only_readable));
    $ret .= '
<table>
    <tbody>
        <tr>
            <td><label class="label_block" for="datum">'.__('Date').'</label></td>
            <td><input class="halfsize" id="datum" name="formdata[datum]" '.dojoDatepicker('formdata[datum]', $formdata['datum']) .' '.$date_format_len.' '.$date_format_title.' '.$readonly.' /></td>
        </tr>
        <tr>
            <td><label class="label_block" for="anfang">'.__('From').'</label></td>
            <td><input class="halfsize" type="text" maxlength="5" id="anfang" name="formdata[anfang]" value="'.$formdata['anfang'].'"'.$readonly.' /></td>
        </tr>
        <tr>
            <td><label class="label_block" for="ende">'.__('Until').'</label></td>
            <td><input class="halfsize" type="text" maxlength="5" id="ende" name="formdata[ende]" value="'.$formdata['ende'].'"'.$readonly.' /></td>
        </tr>
        <tr>
            <td><label class="label_block" for="duration" title="'.__('Hours').'">'.__('Hrs.').'</label></td>
            <td><select class="halfsize" id="duration" title="'.__('Hours').'" name="formdata[duration]"'.$disabled.'>
                <option value=""></option>'."\n";
                foreach ($duration_vals as $k=>$v) {
                    $ret .= '<option value="'.$k.'"'.($k==$formdata['duration'] ? ' selected="selected"' : '').'>'.$v.'</option>'."\n";
                }
                $ret .= '</select></td>
        </tr>
        <tr>
            <td><label class="label_block" for="event">'.__('Text').'</label></td>
            <td><input class="halfsize" type="text" maxlength="128" id="event" name="formdata[event]" value="'.$formdata['event'].'"'.$readonly.' /></td>
        </tr>
        <tr>
            <td><label class="label_block" for="remark">'.__('Remark').'</label></td>
            <td><textarea class="halfsize" id="remark" name="formdata[remark]" '.$readonly.'>'.html_out($formdata['remark']).'</textarea></td>
        </tr>';
        // check for textarea with html editor
        if ($_SESSION['show_html_editor']['calendar'] == 1) {
            $ret .= '
                <script type="text/javascript">window.onload = function() { newFCKeditor(\'remark\',\''.PATH_PRE.'\'); }</script>'."\n";
        }
        $ret .= '
        <tr>
            <td><label class="label_block" for="ort">'.__('Location').'</label></td>
            <td><input class="halfsize" type="text" maxlength="128" id="ort" name="formdata[ort]" value="'.$formdata['ort'].'"'.$readonly.' /></td>
        </tr>
        <tr>
            <td><label class="label_block" for="priority">'.__('priority').'</label></td>
            <td><select class="halfsize" id="priority" name="formdata[priority]"'.$disabled.'>'."\n";
            for ($i=0; $i<10; $i++) {
                if ($i == 0) {
                    $i_show = '-';
                } else {
                    $i_show = $i;
                }
                $ret .= '<option value="'.$i.'"'.($i==$formdata['priority'] ? ' selected="selected"' : '').'>'.$i_show.'</option>'."\n";
            }
            $ret .= '
            </select></td>
        </tr>';
        // visibility of this event
        $dotcol = ($formdata['visi']==2) ? 'r' : 't';
        $ret .= '
        <tr>
            <td><label class="label_block" for="visi">'.__('Visibility').'</label></td>
            <td><select class="halfsize" id="visi" name="formdata[visi]"'.$disabled.'>
                <option value="1"'.($formdata['visi']==1 ? ' selected="selected"' : '').' title="'.__('private').'">'.__('private').'</option>
                <option value="0"'.($formdata['visi']==0 ? ' selected="selected"' : '').' title="'.__('normal').'">'.__('normal').'</option>
                <option value="2"'.($formdata['visi']==2 ? ' selected="selected"' : '').' title="'.__('public').'">'.__('public').'</option>
                <!-- option value="3"'.($formdata['visi']==3 ? ' selected="selected"' : '').' title="'.__('confidential').'">'.__('confidential').'</option -->
                </select>
                &nbsp;<img name="warn" src="'.IMG_PATH.'/'.$dotcol.'.gif" width="10" height="10" alt="" border="0" />
            </td>
        </tr>';
        // accept or decline invitation
        $ret .= '
        <tr>
            <td><label class="label_block" for="partstat">'.__('Participation').'</label></td>
            <td><select class="halfsize" id="partstat" name="formdata[partstat]"'.$only_readable_disabled.'>
                <option value="1"'.($formdata['partstat']==1 ? ' selected="selected"' : '').' title="'.__('not yet decided').'">'.__('not yet decided').'</option>
                <option value="2"'.($formdata['partstat']==2 ? ' selected="selected"' : '').' title="'.__('accept').'">'.__('accept').'</option>
                <option value="3"'.($formdata['partstat']==3 ? ' selected="selected"' : '').' title="'.__('reject').'">'.__('reject').'</option>
                </select>
            </td>
        </tr>';
        // file upload
        if (!$read_o) {
            $ret .= '
        <tr>
            <td><label class="label_block" for="uploadfile">'.__('Upload').'</label></td>
            <td><input class="halfsize" type="file" id="uploadfile" name="uploadfile" /></td>
        </tr>';
        }
        $upload = strlen($formdata['upload']) ? $formdata['upload'] : $_SESSION['calendardata']['current_event']['upload'];
        if (strlen($upload)) {
            $tmp      = explode('|', $upload);
            $filename = $tmp[0];
            $realname = $tmp[1];
            
            
            // creating random value for file download
            $rnd = rnd_string(9);
            $file_ID[$rnd] = $upload;
            $_SESSION['file_ID'] =& $file_ID;
            
            $ret .= '
        <tr>
            <td colspan="2">'.$filename.'&nbsp;&nbsp;&nbsp;
            <a href="../lib/file_download.php?download_attached_file='.$rnd.'&module=calendar" target="_blank">'.__('view').'</a>
            '.(!$read_o ? '|&nbsp;<a href="./calendar.php?mode=data&amp;view='.$view.$af.$sid.'&amp;action_delete_file=1&amp;ID='.$ID.'&amp;referer='.urlencode(xss($_SERVER['REQUEST_URI'])).'">'.__('delete').'</a>' : '').'
            </td>
        </tr>'."\n";
        }
        // select contact (only if module is active)
        if (PHPR_CONTACTS) {
            $selected_contacts = array();
            $ret .= '
        <tr>
            <td><label class="label_block" for="contact">'.__('Contacts').'</label></td>
            <td>'.selector_create_select_contacts('formdata[contact]', $formdata['contact'], 'action_form_to_contact_selector', '0', 'style="width:120px;" class="calendar"').'</td>
        </tr>'."\n";
        }
        // select projekt (only if module is active)
        if (PHPR_PROJECTS) {
            $ret .= '
        <tr>
            <td><label class="label_block" for="projekt">'.__('Project').'</label></td>
            <td>'.selector_create_select_projects('formdata[projekt]', $formdata['projekt'], 'action_form_to_project_selector', '0', 'style="width:120px;" class="calendar"').'</td>
        </tr>'."\n";
        }
        $ret .= '
    </tbody>
</table>
    </fieldset>

    <fieldset>
    <legend>'.__('multiple events').'</legend>'."\n";
        // recurring events
        $ret .= '
<table>
    <tbody>
        <tr>
            <td><label class="label_block" for="serie_typ">&nbsp;'.__('multiple events').'</label>
                <select class="halfsize" style="width:120px;" id="serie_typ" name="formdata[serie_typ]"'.$disabled.'>
                <option value="" title="'.__('Once').'">'.__('Once').'</option>
                <option value="d1"'.($formdata['serie_typ']=='d1' ? ' selected="selected"' : '').' title="'.__('Daily').'">'.__('Daily').'</option>
                <option value="w1"'.($formdata['serie_typ']=='w1' ? ' selected="selected"' : '').' title="'.__('weekly').'">'.__('weekly').'</option>
                <option value="w2"'.($formdata['serie_typ']=='w2' ? ' selected="selected"' : '').' title="'.__('every 2 weeks').'">'.__('every 2 weeks').'</option>
                <option value="w3"'.($formdata['serie_typ']=='w3' ? ' selected="selected"' : '').' title="'.__('every 3 weeks').'">'.__('every 3 weeks').'</option>
                <option value="w4"'.($formdata['serie_typ']=='w4' ? ' selected="selected"' : '').' title="'.__('every 4 weeks').'">'.__('every 4 weeks').'</option>
                <option value="m1"'.($formdata['serie_typ']=='m1' ? ' selected="selected"' : '').' title="'.__('monthly').'">'.__('monthly').'</option>
                <option value="y1"'.($formdata['serie_typ']=='y1' ? ' selected="selected"' : '').' title="'.__('annually').'">'.__('annually').'</option>
                </select>
            </td>
            <td><label class="label_block" for="serie_bis">'.__('Until').'</label>
                <input class="halfsize" type="text" '.$date_format_len.' '.$date_format_title.' id="serie_bis" name="formdata[serie_bis]" '.dojoDatepicker('formdata[serie_bis]', $formdata[serie_bis]).' '.$readonly.' />
            </td>
        </tr>
        <tr>
     
        <td colspan="2">
        <fieldset><legend>'.__('Days').'</legend>'."\n";
        foreach ($name_day2 as $k=>$v) {
            $checked = (isset($formdata['serie_weekday'][$k])) ? ' checked="checked"' : '';
            $ret .= '&nbsp;&nbsp;<input type="checkbox" id="serie_weekday_'.$k.'" name="serie_weekday['.$k.']" value="1"'.$checked.$readonly.' />'."\n";
            $ret .= '<label for="serie_weekday_'.$k.'">'.$v.'</label>'."\n";
        }
        $ret .= '</fieldset>
            </td>
        </tr>';
        // event is canceled..
        if ($ID) {
            $checked = ($formdata['status']) ? ' checked="checked"' : '';
            $ret .= '
        <tr>
            <td colspan="2"><label class="label_block" for="status" style="white-space:nowrap">'.__('Event is canceled').'</label>
            <input type="checkbox" id="status" name="formdata[status]" value="1"'.$checked.$readonly.' /></td>
        </tr>'."\n";
        }
        $ret .= '</tbody></table></fieldset>';
        // show this only on write access
        if (!$read_o) {
            $checked = ($formdata['send_emailnotification']) ? ' checked="checked"' : '';
            $ret .= '
        <fieldset><legend>'.__('Send email notification').'</legend><table><tbody>
        <tr>
            <td colspan="2"><label class="label_block" for="send_emailnotification" style="white-space:nowrap">'.__('Yes').'</label>
            <input type="checkbox" id="send_emailnotification" name="formdata[send_emailnotification]" value="1" '.$checked.'/>
            </td>
        </tr>
        </tbody></table></fieldset>
        <fieldset><legend>'.__('Member selection').'</legend><table><tbody>
        <tr>
            <td colspan="2"><label class="label_block" for="choose_invitees">&nbsp;'.__('Participants').'</label>
            '.selector_create_select_users("invitees[]", $formdata['invitees'], 'action_form_to_selector', '0','id="choose_invitees" class="halfsize"').'
        </tr>
        <tr>
            <td><input type="submit" name="action_check_dateconflict" value="'.__('Collision check').'" /></td>
            <td></td>
        </tr>'."\n";
        }
        if ($ID && !$only_readable) {
            $ret .= '
        <tr>
            <td colspan="2"><a href="../misc/export.php?ID='.$ID.'&amp;medium=vcs&amp;file=calendar_detail'.$sid.'" class="navbutton navbutton_inactive">'.__('export').'</a></td>
        </tr>';
        }
        $ret .= '
    </tbody>
</table>
    </fieldset>

';
    
    $ret .= calendar_forms_get_buttons_area($only_readable);
    $ret .= calendar_forms_show_main_footer();
    return $ret;
}

/**
 * get the delete form.
 *
 * @param  string $type
 * @return string
 */
function calendar_forms_show_delete_event_form($type) {
    global $date_format_object;

    if ($type == 'event') {
        $del_name  = 'action_remove_event_yes';
        $del_value = __('OK');
    }
    else {
        $del_name  = 'action_remove_serial_yes';
        $del_value = __('Delete multiple event completely');
    }

    $ret  = calendar_forms_show_main_header();
    
    $readonly = 'readonly="readonly"';
    $date_format_len   = $date_format_object->get_maxlength_attribute();
    $date_format_title = $date_format_object->get_title_attribute();

    $ret .= '
    <input type="hidden" name="send_emailnotification" value="'.(empty($GLOBALS['formdata']['send_emailnotification'])?0:1).'" />
    
        <fieldset class="calendar" style="height:auto">
            <legend></legend>
            <table>
            <tbody>
            <tr>
                <td><label class="label_block" for="datum">'.__('Date').'</label></td>
                <td><input class="halfsize" id="datum" name="datum" value="'. $date_format_object->convert_db2user($_SESSION['calendardata']['current_event']['datum']) .'" '.$readonly.' /></td>
            </tr>
            
            <tr>
                <td><label class="label_block" for="anfang">'.__('From').'</label></td>
                <td><input class="halfsize" type="text" maxlength="5" id="anfang" name="formdata[anfang]" value="'.$_SESSION['calendardata']['current_event']['anfang'].'" '.$readonly.' /></td>
            </tr>
            
            <tr>
                <td><label class="label_block" for="ende">'.__('Until').'</label></td>
                <td><input class="halfsize" type="text" maxlength="5" id="ende" name="formdata[ende]" value="'.$_SESSION['calendardata']['current_event']['ende'].'" '.$readonly.' /></td>
            </tr>

            <tr>
                <td><label class="label_block" for="event">'.__('Text').'</label></td>
                <td><input class="halfsize" type="text"  maxlength="128" id="event" name="event" value="'.htmlspecialchars($_SESSION['calendardata']['current_event']['event']).'" '.$readonly.' /></td>
            </tr>

            <tr>
                <td><label class="label_block" for="ort">'.__('Location').'</label></td>
                <td><input class="halfsize" type="text" maxlength="128" id="ort" name="ort" value="'.$_SESSION['calendardata']['current_event']['ort'].'" '.$readonly.' /></td>
            <tr>

            <tr>
                <td>&nbsp;</td>
                <td>'.__('Really delete this event?').'</td>
            </tr>
            <tr>
                <td>&nbsp;</td>
                <td>
                <input type="submit" class="button" name="'.$del_name.'" value="'.$del_value.'" />
                &nbsp;&nbsp;&nbsp;
                <input type="submit" class="button" name="action_cancel_event" value="'.__('List View').'" />
            </td>
            </tr>
            </tbody>
            </table>
        </fieldset>
    <br class="clearboth" /><br class="clearboth" />'."\n\n";

    $ret .= calendar_forms_show_main_footer();
    return $ret;
}

/**
 * prepare the view of the corresponding invitees for an event.
 *
 * @param  int   the ID of the current user (or act for)
 * @return array
 */
function calendar_forms_prepare_invitees($current_user) {
    global $ID, $formdata;

    $serie_id = $_SESSION['calendardata']['current_event']['serie_id'];
    if ($ID && $_SESSION['calendardata']['current_event']['parent']) {
        $invitees = calendar_get_event_invitees($_SESSION['calendardata']['current_event']['parent'], $serie_id);
    }
    else if ($ID) {
        $invitees = calendar_get_event_invitees($_SESSION['calendardata']['current_event']['ID'], $serie_id);
    }
    else {
        $invitees = array();
    }

    $part_stat = array( 1 => __('not yet decided'), 2 => __('accept'), 3 => __('reject') );
    $user_list = array();

    // the data from the form
    foreach ($formdata['invitees'] as $val) {
        if (empty($val)) {
            continue;
        }
        $k = str_replace(',', ', ', slookup('users', 'nachname,vorname', 'ID', $val,'1'));
        $pstat = ($val == $current_user) ? $formdata['partstat'] : 1;
        $user_list[$k] = array( 'ID'       => $val
                               ,'partstat' => $part_stat[$pstat]
                               ,'sync2'    => '---'
                              );
    }

    // the data from the db
    foreach ($invitees as $val) {
        if (!in_array($val['an'], $formdata['invitees'])) {
            continue;
        }
        if (array_key_exists($val['partstat'], $part_stat)) {
            $def_stat = $part_stat[$val['partstat']];
        }
        else {
            $def_stat = '&middot;?&middot;';
        }

        $k = str_replace(',', ', ', slookup('users', 'nachname,vorname', 'ID', $val['an'],'1'));
        $user_list[$k] = array( 'ID'       => $val['an']
                               ,'partstat' => $def_stat
                               ,'sync2'    => $val['sync2']
                              );
    }

    ksort($user_list);
    return $user_list;
}

/**
 * build and show the corresponding invitees for an event.
 *
 * @param  array  $user_list
 * @param  array  $colliding_invitees
 * @param  array  $serial_events
 * @return string
 */
function calendar_forms_show_invitees(&$user_list, &$colliding_invitees, &$serial_events) {
    global $ID, $formdata, $date_format_object;

    if ($ID && $_SESSION['calendardata']['current_event']['parent']) {
        $tmp_collide = '';
    }
    else {
        $tmp_collide = '<th>&nbsp;'.__('Collision').'&nbsp;</th>';
    }

    $ret = '
<div class="calendar_form_left" style="background-color:transparent;">
    <table cellspacing="1" cellpadding="1" class="calendar_table" border="0">
        <tr>
            <th>&nbsp;'.__('Date').'&nbsp;</th>
            <th>&nbsp;'.__('Name').'&nbsp;</th>
            <th>&nbsp;'.__('Participation').'&nbsp;</th>
            <th>&nbsp;'.__('Last modification date').'&nbsp;</th>
            '.$tmp_collide.'
        </tr>'."\n";

    // loop thru the events
    foreach ($serial_events as $e_key=>$e_val) {
        $first_user = true;
        if (isset($_REQUEST['action_check_dateconflict'])) {
            $serie_id = calendar_get_current_serie_id();
            if (!$serie_id && $ID) {
                $serie_id = $ID;
            }
            $formdata_ende      = calendar_calculate_endtime($formdata);
            $colliding_invitees = check_concrete_date( $formdata['invitees'], $e_key,
                                                       $formdata['anfang'],
                                                       $formdata_ende, $serie_id );
        }
        else {
            $colliding_invitees = array();
        }

        // loop thru the users
        foreach ($user_list as $k=>$v) {
            if ($ID && $_SESSION['calendardata']['current_event']['parent']) {
                $tmp_collide = '';
            }
            else {
                if (!isset($colliding_invitees[$v['ID']])) {
                    $tmp_collide = __('check');
                    $class = 'calendar_event_open';
                }
                else if ($colliding_invitees[$v['ID']] === false) {
                    $tmp_collide = __('no');
                    $class = 'calendar_event_accept';
                }
                else {
                    $tmp_collide = __('yes');
                    $class = 'calendar_event_reject';
                }
                $tmp_collide = '<td class="'.$class.'">&nbsp;'.$tmp_collide.'&nbsp;</td>';
            }

            if (strlen($v['sync2']) > 3) {
                $daytime = $date_format_object->convert_dbdatetime2user($v['sync2']);
            }
            else {
                $daytime = $v['sync2'];
            }

            $ret .= "        <tr>\n";
            if ($first_user) {
                $ret .= '<td rowspan="'.count($user_list).'">&nbsp;'.$e_val.'&nbsp;</td>';
            }
            $ret .= '
            <td>&nbsp;'.$k.'&nbsp;</td>
            <td>&nbsp;'.$v['partstat'].'&nbsp;</td>
            <td>&nbsp;'.$daytime.'&nbsp;</td>
            '.$tmp_collide.'
        </tr>'."\n";
            $first_user = false;
        }
    }

    $ret .= '
    </table>
    <br class="clearboth" /><br class="clearboth" />
</div>
';
    return $ret;
}

/**
 * get & build the serial events.
 *
 * @return array
 */
function calendar_forms_get_serial_event_links() {
    global $user_ID, $formdata, $view, $act_for, $sid, $date_format_object;

    // the first event is already loaded in the form so we didn't need a link for that
    $ret = array($formdata['datum'] => $date_format_object->convert_db2user($formdata['datum']));

    $serie_id = calendar_get_current_serie_id();
    if ($serie_id) {
        $uID = ($act_for) ? $act_for : $user_ID;
        $data = calendar_get_serial_events($uID, $serie_id, $formdata['datum']);
        // remove the first entry (is already given with $formdata['datum'])
        if (count($data) > 0) array_shift($data);
    }
    else {
        $data = calendar_calculate_serial_events($formdata);
    }

    // return unformated date if there are no serial events (should be on a new event)
    if (count($data) == 0) {
        return $ret;
    }

    $af = ($act_for) ? '&amp;act_for='.$act_for : '';

    for ($ii=0; $ii<count($data); $ii++) {
        if ($serie_id) {
            $date_view = $date_format_object->convert_db2user($data[$ii]['datum']);
            $ret[$data[$ii]['datum']] = '<a href="./calendar.php?ID='.$data[$ii]['ID'].
                                        '&amp;mode=forms&amp;view='.$view.$af.$sid.
                                        '" title="'.$date_view.'">'.$date_view.'</a>';
        }
        else {
            $ret[$data[$ii]] = $date_format_object->convert_db2user($data[$ii]);
        }
    }

    return $ret;
}

/**
 * show the date proposals.
 *
 * @param  array $colliding_invitees
 * @return string
 */
function calendar_forms_show_dateproposals(&$colliding_invitees) {
    global $ID, $formdata, $date_format_object;

    // this should be solved in a better way...
    $found = false;
    foreach ($colliding_invitees as $item) {
        if ($item !== false) {
            $found = true;
            break;
        }
    }

    if (!$found) {
        return '';
    }

    $formdata_ende = calendar_calculate_endtime($formdata);
    $proposals     = search_time_slot( $formdata['invitees'],
                                       $formdata['datum'],
                                       $formdata['anfang'],
                                       $formdata_ende, $ID );
    $ret = '';
    foreach ($proposals as $k => $tmp_date) {
        $ret .= '
        <tr>
            <td>&nbsp;'.$date_format_object->convert_db2user($tmp_date['date']).'&nbsp;</td>
            <td>&nbsp;'.$tmp_date['from'].' - '.$tmp_date['to'].'&nbsp;</td>
        </tr>'."\n";
    }

    $ret = '
<div class="calendar_form_right" style="background-color:transparent;">
    <table cellspacing="1" cellpadding="1" class="calendar_table" border="0">
        <tr>
            <th colspan="2">&nbsp;'.__('Available time').'&nbsp;</th>
        <tr>
        '.$ret.'
    </table>
    <br class="clearboth" /><br class="clearboth" />
</div>'."\n";

    return $ret;
}

/**
 * get the button area.
 *
 * @param  boolean $only_readable
 * @return string
 */
function calendar_forms_get_buttons_area($only_readable=false) {
    global $ID, $sid, $view, $axis, $dist, $year, $month, $day, $act_as, $act_for;
    
    $archive = null;
    $buttons = array();
    
    if (!$only_readable) {
        if ($ID) {
            // update/remove buttons
            $buttons[] = array('type'=>'submit', 'name'=>'action_update_event', 'value'=>__('OK'), 'active'=>false);
            $buttons[] = array('type'=>'submit', 'name'=>'action_apply_event', 'value'=>__('Apply'), 'active'=>false);
            if (!$_SESSION['calendardata']['current_event']['parent']) {
                $buttons[] = array('type'=>'submit', 'name'=>'action_remove_event', 'value'=>__('Delete'), 'active'=>false);
            }
            
            // archive actions
            $_act = ($act_as) ? '&amp;act_as='.$act_as : (($act_for) ? '&amp;act_for='.$act_for : '');
            $_params = ($view == 3) ? '&amp;axis='.$axis.'&amp;dist='.$dist : '';
            $_params = '&amp;view='.$view.'&amp;year='.$year.'&amp;month='.((int) $month).
                       '&amp;day='.((int) $day).$_params.$_act.$sid;#.'" class="';
            $_class = 'navbutton navbutton_inactive';
            if (check_archiv_flag($ID.$sid, 'calendar')) {
                $archive = array('type'=>'link', 'href'=>'./calendar.php?mode=view&amp;set_archiv_flag=0&amp;ID_s='.$ID.$sid.$_params, 'text'=>__('Take back from Archive'), 'active'=>false);
            }
            else {
                $archive = array('type'=>'link', 'href'=>'./calendar.php?mode=view&amp;set_archiv_flag=1&amp;ID_s='.$ID.$sid.$_params, 'text'=>__('Move to archive'), 'active'=>false);
            }
        }
        else {
            // create button
            $buttons[] = array('type'=>'submit', 'name'=>'action_create_event', 'value'=>__('OK'), 'active'=>false);
            $buttons[] = array('type'=>'submit', 'name'=>'action_create_update_event', 'value'=>__('Apply'), 'active'=>false);
        }
    }
    $buttons[] = array('type'=>'submit', 'name'=>'action_cancel_event', 'value'=>__('List View'), 'active'=>false);
    
    // delete a serial event completely?
    if ($ID && !$_SESSION['calendardata']['current_event']['parent'] &&
        ($_SESSION['calendardata']['current_event']['serie_id'] ||
         $_SESSION['calendardata']['current_event']['serie_typ'])) {
        $buttons[] = array('type'=>'submit', 'name'=>'action_remove_serial', 'value'=>__('Delete multiple event completely'), 'active'=>false);
    }
    
    if (!empty($archive)) {
        $buttons[] = $archive;
    }
    
    $ret = '<br class="clearboth" /><div class="hline"></div>'.
           get_buttons_area($buttons).
           '<div class="hline"></div><br class="clearboth" />';
    
    return $ret;
}

?>
