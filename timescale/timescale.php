<?php
/**
 * @package    misc
 * @subpackage timescale
 * @author     Gustavo Solt, $Author: gustavo $
 * @licence    GPL, see www.gnu.org/copyleft/gpl.html
 * @copyright  2000-2006 Mayflower GmbH www.mayflower.de
 * @version    $Id: timescale.php
 */

define('PATH_PRE','../');

include_once(PATH_PRE.'lib/lib.inc.php');
include_once(PATH_PRE.'lib/dbman_lib.inc.php');
include_once(PATH_PRE.'lib/save_selections_form.php');

$module = 'timescale';

$mode2 = timescale_init();

echo set_page_header();

include_once(LIB_PATH.'/navigation.inc.php');

define('MODE',$mode2);

global $date_format_object;
$date_format_text = __('Date format').' = '.$date_format_object->get_user_format();

$tabs    = array();

// form start
if (SID) $hidden[session_name()] = session_id();
$output  = '<div id="global-header">';
$output .= get_tabs_area($tabs);
$output .= '</div>';

// button bar
$output .='<div id="global-content">';
$buttons = array();

$active = (MODE == 'calendar') ? true : false;
$buttons[] = array('type' => 'link', 'href' => 'timescale.php?mode2=calendar'.$sid.'&amp;csrftoken='.make_csrftoken(), 'text' => __('Calendar'), 'active' => $active);

$active = (MODE == 'capacityplan') ? true : false;
$buttons[] = array('type' => 'link', 'href' => 'timescale.php?mode2=capacityplan'.$sid.'&amp;csrftoken='.make_csrftoken(), 'text' => __('Capacity plan'), 'active' => $active);

$active = (MODE == 'taskplan') ? true : false;
$buttons[] = array('type' => 'link', 'href' => 'timescale.php?mode2=taskplan'.$sid.'&amp;csrftoken='.make_csrftoken(), 'text' => __('Task plan'), 'active' => $active);

$active = (MODE == 'workload') ? true : false;
$buttons[] = array('type' => 'link', 'href' => 'timescale.php?mode2=workload'.$sid.'&amp;csrftoken='.make_csrftoken(), 'text' => __('Work Load'), 'active' => $active);

$output .= get_buttons_area($buttons);
// end buttons

if (!isset($_POST['anfang'])) {
    $start_date = date('Y-m-d');
} else {
    $start_date = xss($_POST['anfang']);
}
if (!isset($_POST['ende'])) {
    $end_date   = date('Y-m-d',mktime(0,0,0,date("m")+1,date("d"),date("Y")));
} else {
    $end_date = xss($_POST['ende']);
}

$timescale_explanation = '<fieldset>
        <legend>'.__('Surveys').'</legend>
        <label class="label_block">'.__('Calendar').':</label>'.__('Events of a selection of users.').'<br/>
        <label class="label_block">'.__('Capacity plan').':</label>'.__('Tasks and Events of a selection of users grouped by project.').'<br/>
        <label class="label_block">'.__('Task plan').':</label>'.__('Tasks of a selection of Projects.').'<br/>
        <label class="label_block">'.__('Work Load').':</label>'.__('Work load of a selection of users.').'<br/>
        </fieldset>';

switch(MODE) {
    case "resourceplan":
    case "capacityplan":
    default:
        $output .= '
<br />
<div class="inner_content">
    <a name="content"></a>
    <a name="oben" id="oben"></a>
    <br style="clear:both"/>
    <table>
    <colgroup>
      <col width="410"/>
      <col width="410"/>
    </colgroup>
    <tr>
        <td colspan="2">
        ';

        // Config
        $page   = 'timescale.php';
        $mode2  = MODE;
        $module = 'timescale';
        $error  = '';
        $radio_buttons_filter = array();
        $form_js = "onsubmit=\"return checkUserDateFormat('anfang','".__('Begin').':\n'.$date_format_text."') && checkUserDateFormat('ende','".__('End').':\n'.$date_format_text."') && checkDates('anfang','ende','".__('Begin > End')."!');\"";
        $use_project = false;
        $use_users   = true;
        $check_buttons_select = array();
        $radio_buttons_select = array();

        $output .= $timescale_explanation;
        
        // Class
        $class = new save_selections_form($page,$mode2,$module,$_REQUEST);

        // Saved Settings
        $output .= $class->saved_settings();
        
        //consider special flags
        //$output .= $class->consider_special_flags();

        // Form
        $output .= $class->start_make_form('timescale_'.MODE.'.php','frm','_blank',$form_js);

        // Period
        $output .= $class->period();
        // Multiple selections
        $output .= $class->multiple_selects($use_project,$use_users,$check_buttons_select,$radio_buttons_select,'',true);
        
        $parameters['legend']   = __('Select Fields');
        $output .= $class->extra_fields('fieldset_start',$parameters);
        unset($parameters);

        $parameters['in_div']   = 'float:left;margin-right:30px';
        $parameters['class']    = 'label_inline';
        $parameters['name']     = 'fields';
        $parameters['text']     = __('Fields');
        $parameters['style']    = 'float:left;min-width:200px;';
        $parameters['size']     = '10';
        $parameters['use_sort'] = '1';

        $query = "SELECT db_name, form_name
                    FROM ".DB_PREFIX."db_manager
                   WHERE db_table LIKE 'projekte'
                     AND db_inactive <> 1
                   ORDER BY form_pos, ID";
        $result = db_query($query) or db_die();
        while ($row = db_fetch_row($result)) {
            $parameters['values'][$row[0]] = enable_vars($row[1]);
        }
        $parameters['values']['costcentre_id'] = __('Costcentre');
        $parameters['values']['costunit_id']   = __('Costunit');
        $parameters['values']['aufwand_gebucht']   = 'Bereits gebuchter Aufwand';
        unset($parameters['values']['name']);
        $output .= $class->extra_fields('multiple_select',$parameters);
        unset($parameters);
        $output .= $class->extra_fields('fieldset_end');

        // End Form
        $output .= $class->end_make_form();
        $output .= '
        </td>
    </tr>
    </table>
</div>
<br style="clear:both" /><br />
</div>
        ';
        break;
    case "calendar":
        $output .= '
<br />
<div class="inner_content">
    <a name="content"></a>
    <a name="oben" id="oben"></a>
    <br style="clear:both"/>
    <table>
    <colgroup>
      <col width="410"/>
      <col width="410"/>
    </colgroup>
    <tr>
        <td colspan="2">
        ';

        // Config
        $page   = 'timescale.php';
        $mode2  = MODE;
        $module = 'timescale';
        $error  = '';
        $radio_buttons_filter = array();
        $form_js = "onsubmit=\"return checkUserDateFormat('anfang','".__('Begin').':\n'.$date_format_text."') && checkUserDateFormat('ende','".__('End').':\n'.$date_format_text."') && checkDates('anfang','ende','".__('Begin > End')."!');\"";
        $use_project = false;
        $use_users   = true;
        $check_buttons_select = array();
        $radio_buttons_select = array();

        $output .= $timescale_explanation;
        
        // Class
        $class = new save_selections_form($page,$mode2,$module,$_REQUEST);

        // Saved Settings
        $output .= $class->saved_settings();

        // Form
        $output .= $class->start_make_form('timescale_'.MODE.'.php','frm','_blank',$form_js);
        // Period
        $output .= $class->period();

        // Multiple selections
        $output .= $class->multiple_selects($use_project,$use_users,$check_buttons_select,$radio_buttons_select,'',true);

        // End Form
        $output .= $class->end_make_form();
        $output .= '
        </td>
    </tr>
    </table>
</div>
<br style="clear:both" /><br />
</form>
</div>
        ';
        break;
    case "redsalestasklist":
    case "taskplan":
        $output .= '
<br />
<div class="inner_content">
    <a name="content"></a>
    <a name="oben" id="oben"></a>
    <br style="clear:both"/>
    <table>
    <colgroup>
      <col width="410"/>
      <col width="410"/>
    </colgroup>
    <tr>
        <td colspan="2">
        ';

        // Config
        $page   = 'timescale.php';
        $mode2  = MODE;
        $module = 'timescale';
        $error  = '';
        $radio_buttons_filter = array();
        $form_js = "onsubmit=\"return checkUserDateFormat('anfang','".__('Begin').':\n'.$date_format_text."') && checkUserDateFormat('ende','".__('End').':\n'.$date_format_text."') && checkDates('anfang','ende','".__('Begin > End')."!');\"";
        $use_project = true;
        $use_users   = false;
        $check_buttons_select = array(
        __('Show only tasks which are not assigned to an user') => 'nonuser');
        $radio_buttons_select = array();

        $output .= $timescale_explanation;
        
        // Class
        $class = new save_selections_form($page,$mode2,$module,$_REQUEST);

        // Saved Settings
        $output .= $class->saved_settings();
        
        // Form
        $output .= $class->start_make_form('timescale_'.MODE.'.php','frm','_blank',$form_js);

        // Period
        $output .= $class->period();
        // Multiple selections
        $output .= $class->multiple_selects($use_project,$use_users,$check_buttons_select,$radio_buttons_select,null,true);
        $parameters['legend']   = __('Select Fields');
        $output .= $class->extra_fields('fieldset_start',$parameters);
        unset($parameters);

        $parameters['in_div']   = 'float:left;margin-right:30px';
        $parameters['class']    = 'label_inline';
        $parameters['name']     = 'fields';
        $parameters['text']     = __('Fields');
        $parameters['style']    = 'float:left;min-width:200px;';
        $parameters['size']     = '10';
        $parameters['use_sort'] = '1';

        $query = "SELECT db_name, form_name
                    FROM ".DB_PREFIX."db_manager
                   WHERE db_table LIKE 'projekte'
                     AND db_inactive <> 1
                   ORDER BY form_pos, ID";
        $result = db_query($query) or db_die();
        while ($row = db_fetch_row($result)) {
            $parameters['values'][$row[0]] = enable_vars($row[1]);
        }
        $parameters['values']['costcentre_id'] = __('Costcentre');
        $parameters['values']['costunit_id']   = __('Costunit');
        $parameters['values']['aufwand_gebucht']   = 'Bereits gebuchter Aufwand';
        unset($parameters['values']['name']);
        $output .= $class->extra_fields('multiple_select',$parameters);
        unset($parameters);

        $parameters['in_div']   = 'float:left;margin-left:1px';
        $parameters['class']    = 'label_inline';
        $parameters['name']     = 'sort_field';
        $parameters['text']     = __('Sorting');
        $parameters['style']    = 'float:left;min-width:200px;';
        $parameters['use_sort'] = '1';

        $parameters['values'][0] = '';
        $query = "SELECT db_name, form_name
                    FROM ".DB_PREFIX."db_manager
                   WHERE db_table LIKE 'projekte'
                     AND db_inactive <> 1
                   ORDER BY form_pos, ID";
        $result = db_query($query) or db_die();
        while ($row = db_fetch_row($result)) {
            $parameters['values'][$row[0]] = enable_vars($row[1]);
        }
        $parameters['values']['costcentre_id'] = __('Costcentre');
        $parameters['values']['costunit_id']   = __('Costunit');
        unset($parameters['values']['name']);
        $output .= $class->extra_fields('simple_select',$parameters);
        unset($parameters);

        $parameters['in_div']   = 'float:left;margin-left:1px';
        $parameters['class']    = 'label_inline';
        $parameters['name']     = 'direction';
        $parameters['text']     = __('Chat Direction');
        $parameters['style']    = 'float:left;min-width:200px;';
        $parameters['use_sort'] = '1';

        $parameters['values'][0] = '';
        $parameters['values']['desc'] = enable_vars(__('Desc'));
        $parameters['values']['asc'] = enable_vars(__('Asc'));
        $output .= $class->extra_fields('simple_select',$parameters);
        unset($parameters);

        $output .= $class->extra_fields('fieldset_end');

        // End Form
        $output .= $class->end_make_form();
        $output .= '
        </td>
    </tr>
    </table>
</div>
<br style="clear:both" /><br />
</div>
        ';
        break;

    case "handywm":
    case "projectplan":
        if (!isset($_POST['phases'])) {
            $phases  = ($_POST['phases'] == 'on') ? 'checked="checked"' : '';
        } else {
            $phases  = 'checked="checked"';
        }

        $output .= '
<br />
<div class="inner_content">
    <a name="content"></a>
    <a name="oben" id="oben"></a>
    <br style="clear:both"/>
    <table>
    <colgroup>
      <col width="410"/>
      <col width="410"/>
    </colgroup>
    <tr>
        <td colspan="2">
        ';

        // Config
        $page   = 'timescale.php';
        $mode2  = MODE;
        $module = 'timescale';
        $error  = '';
        $radio_buttons_filter = array();
        $form_js = "onsubmit=\"return checkUserDateFormat('anfang','".__('Begin').':\n'.$date_format_text."') && checkUserDateFormat('ende','".__('End').':\n'.$date_format_text."') && checkDates('anfang','ende','".__('Begin > End')."!');\"";
        $use_project = false;
        $use_users   = false;
        $check_buttons_select = array();
        $radio_buttons_select = array();

        $output .= $timescale_explanation;
        
        // Class
        $class = new save_selections_form($page,$mode2,$module,$_REQUEST);
        // Saved Settings
        $output .= $class->saved_settings();
        
        //consider special flags
        $output .= $class->consider_special_flags();
        // Form
        $output .= $class->start_make_form('timescale_'.MODE.'.php','frm','_blank',$form_js);
        // Period
        $output .= $class->period();
        // Project selection
        $output .= $class->single_project_selection();
        // End Form
        $output .= $class->end_make_form();
        $output .= '
        </td>
    </tr>
    </table>
</div>
<br style="clear:both" /><br />
</div>
        ';
        break;
    case "workload":
        $output .= '
<br />
<div class="inner_content">
    <a name="content"></a>
    <a name="oben" id="oben"></a>
    <br style="clear:both"/>
    <table>
    <colgroup>
      <col width="410"/>
      <col width="410"/>
    </colgroup>
    <tr>
        <td colspan="2">
        ';

        // Config
        $page   = 'timescale.php';
        $mode2  = MODE;
        $module = 'timescale';
        $error  = '';
        $radio_buttons_filter = array();
        $form_js = "onsubmit=\"return checkUserDateFormat('anfang','".__('Begin').':\n'.$date_format_text."') && checkUserDateFormat('ende','".__('End').':\n'.$date_format_text."') && checkDates('anfang','ende','".__('Begin > End')."!');\"";
        $use_project = false;
        $use_users   = true;
        $check_buttons_select = array();
        $radio_buttons_select = array();

        $output .= $timescale_explanation;
        
        // Class
        $class = new save_selections_form($page,$mode2,$module,$_REQUEST);

        // Saved Settings
        $output .= $class->saved_settings();
        
        // Form
        $output .= $class->start_make_form('timescale_'.MODE.'.php','frm','_blank',$form_js);

        // Period
        $output .= $class->period();
        // Multiple selections
        $output .= $class->multiple_selects($use_project,$use_users,$check_buttons_select,$radio_buttons_select,'',true);
        
        // End Form
        $output .= $class->end_make_form();
        $output .= '
        </td>
    </tr>
    </table>
</div>
<br style="clear:both" /><br />
</div>
        ';
        break;
}

echo $output;

echo '
</div>
</body>
</html>
';

/**
 * initialize the timescale
 *
 * @return void
 */
function timescale_init() {

    $mode2 = (isset($_REQUEST['mode2'])) ? xss($_REQUEST['mode2']) : 'capacityplan';
    if (!in_array($mode2, array('calendar','capacityplan','resourceplan','redsalestasklist','handywm','projectplan','taskplan','workload'))) {
        $mode2 = 'capacityplan';
    }
    return $mode2;
}
?>
