<?php
/**
 * Class for manage and display stored settings
 *
 * @package    	lib
 * @subpackage 	main
 * @author     	Albrecht Guenther, $Author: gustavo $
 * @licence     GPL, see www.gnu.org/copyleft/gpl.html
 * @copyright  	2000-2006 Mayflower GmbH www.mayflower.de
 * @version    	$Id:
 */

define('PATH_PRE','./');
include_once(PATH_PRE.'lib/lib.inc.php');
include_once(PATH_PRE.'lib/dbman_list.inc.php');
include_once(PATH_PRE.'lib/dbman_filter.inc.php');

/**
 * Class for manage and display stored settings
 * @package lib
 */
class save_selections_form {

    var $output = '';

    var $user_ID = 0;
    var $mode2 = '';
    var $module = '';
    var $page = '';
    var $post_values = array();

    // Settings
    var $use_settings = false;
    var $settings_error = '';

    var $use_filter_options = false;
    var $use_period = false;
    var $use_project = false;
    var $use_users = false;

    /**
     * Construct class
     *
     * @param string		$page				- Value for switch into many modules forms
     * @param string		$mode2			- Value for switch into many modules forms
     * @param string		$module			- Module name
     * @param array		$post_values		- Array with all the POST values
     * @return string							HTML output
     */
    function save_selections_form($page,$mode2,$module,$post_values) {
        global $user_ID, $f_sort;

        $this->user_ID = $user_ID;
        $this->page = xss($page);
        $this->mode2 = qss($mode2);
        $this->module = qss($module);
        $this->post_values = xss_array($post_values);

        if ($this->post_values['use_sort'] == "on")  {
            $f_sort['saved_settings']['sort']       =  xss($f_sort['projects']['sort']);
            $f_sort['saved_settings']['direction']  =  xss($f_sort['projects']['direction']);
        }
        else{
            if ($module == 'projects') {
                $f_sort['saved_settings']['sort']       = 'next_proj,name';
            }
            else {
                $f_sort['saved_settings']['sort']       = 'ID';
            }
            $f_sort['saved_settings']['direction']  = 'ASC';
        }

        if ($this->post_values['action'] == "calc" && $this->post_values['do'] == "displaySavedSetting") {
        	if(empty($this->post_values['savedSetting'])) {
        		$this->settings_error = "<b>".__("You have to choose a saved setting")."</b><br /></br />";
            }
        }

        // get the save settings
        if(($this->post_values['do'] == "displaySavedSetting") && (empty($this->settings_error)) && (intval($this->post_values['savedSetting']) != 0)) {
            $result = db_query(sprintf("
					SELECT name, settings
					  FROM ".DB_PREFIX."savedata
                     WHERE id = %d
                       AND user_ID = %d", (int)$this->post_values['savedSetting'],(int)$this->user_ID));
            $row = db_fetch_row($result);
            $settings = unserialize($row[1]);
            foreach($settings as $k => $v) {
                $this->post_values[$k] = $v;
            }
		}

        foreach($this->post_values as $k => $v) {
            global $$k;
            $_SESSION['saved_settings'][$this->module][$k] = $v;
            $GLOBALS[$k] = $v;
        }
    }

    /**
     * Start the form
     *
     * @param string		$action		- Form Action value
     * @param string		$name			- Form name value
     * @param string		$target		- Form target value
     * @param string		$js			- Javascript string
     * @return string						HTML output
     */
    function start_form($action, $name, $target = '', $js = '') {
        $output = '
        <form action="'.qss($action).'" method="post" name="'.qss($name).'" ';

        if (!empty($target)) {
            $output .= 'target="'.qss($target).'" ';
        }

        if (!empty($js)) {
            $output .= $js;
        }

        $output .= '>
        <fieldset>
        ';
        return $output;
    }

    /**
     * Put the hidden values
     *
     * @param array	$hidden		- Array with the data of the hidden values
     * @return string					HTML output
     */
    function hidden($hidden) {
        $output = '';
        if (is_array($hidden)) {
            foreach($hidden as $k => $v) {
                if (is_array($v)) {
                    foreach($v as $z) {
                        $output .= '
        <input type="hidden" name="'.xss($k).'[]" value="'.xss($z).'" />
                        ';
                    }
                } else {
                    $output .= '
        <input type="hidden" name="'.xss($k).'" value="'.xss($v).'" />
                    ';
                }
            }
        }
        return $output;
    }

    /**
     * End form
     *
     * @param void
     * @return string		HTML output
     */
    function end_form() {
        $output = '
        </fieldset>
        </form>
        ';
        return $output;
    }

    /**
     * Display saved settings select
     *
     * @param int		$value		- Number of the selected settings
     * @return string				HTML output
     */
    function show_saved_statistic_settings($value) {
        $output = '';
        $options = '';
        $result = db_query("SELECT id, name, settings
                              FROM ".DB_PREFIX."savedata
                             WHERE user_ID = ".(int)$this->user_ID ."
                               AND module  = '".qss($this->module)."'") or db_die();

        while($row = db_fetch_row($result)) {
            if (!empty($this->mode2)) {
                $settings = unserialize($row[2]);
                if ($settings['mode2'] != $this->mode2) {
                    continue;
                }
            }
            if ($value == $row[0])  $selected = ' selected="selected"';
            else                    $selected = "";
            $options .= sprintf("<option value='%s' %s>%s</option>\n", $row[0], $selected, $row[1]);
        }

        $output .= '
        <input type="hidden" name="action" value="calc"/>
        <input type="hidden" name="do" value="displaySavedSetting"/>
        ';
        if (!empty($this->mode2)) {
            $output .= '
        <input type="hidden" name="mode2" value="'.$this->mode2.'"/>
            ';
        }
        $output .= '
        <select name="savedSetting">
        '.$options.'
        </select>
        ';

	    return $output;
    }
    /**
     * Display saved settings select
     *
     * @param void
     * @return string				HTML output
     */
    function saved_settings() {
        $output = '';
        $hidden = array('mode'  => 'stat',
                        'mode2' => xss($this->mode2));
        $output .= $this->start_form($this->page,'settings');
        $output .= $this->hidden($hidden);
        $output .= '
        <legend>'.__('Project summary').'</legend>
        <label class="label_block" for="projectlist">'.__('Saved Settings').':</label>
        ';
        $output .= $this->show_saved_statistic_settings($this->post_values['savedSetting']);
        $output .= get_buttons(array(
            array("type" => "submit", "name" => "setting_apply", "value" => __("Apply"), "active" => false)));

        if (!empty($this->settings_error)) {
            $output .= $this->settings_error;
        }
        $output .= $this->end_form();

        $this->use_settings = true;
        return $output;
    }

    /**
     * Display the filter checkbox
     *
     * @param array		$radio_buttons		- Array with the extra checkbox data
     * @return string								HTML output
     */
    function filters_options($radio_buttons = array()) {
        $hidden = array('mode'  => 'stat',
                        'mode2' => xss($this->mode2));

        $output .= $this->start_form($this->page,'show_group_sel');
        $output .= $this->hidden($hidden);
        $output .= '
        <legend>'.__('Filter configuration').'</legend>
        ';

        $output .= $this->display_radio_buttons($radio_buttons,false,'onchange="document.show_group_sel.submit();"');

        if (!isset($_REQUEST['use_filters']))       $_REQUEST['use_filters']        = $this->post_values['use_filters'];
        if (!isset($_REQUEST['exclude_archived']))  $_REQUEST['exclude_archived']   = $this->post_values['exclude_archived'];
        if (!isset($_REQUEST['exclude_read']))      $_REQUEST['exclude_read']       = $this->post_values['exclude_read'];
        if (!isset($_REQUEST['use_sort']))          $_REQUEST['use_sort']           = $this->post_values['use_sort'];

    	$fields_required= array(__('Consider current set filters')  =>  'use_filters',
                                __('Exclude archived elements')     =>  'exclude_archived',
                                __('Exclude read elements')         =>  'exclude_read',
                                __('Consider order from listview')  =>  'use_sort');
    	$output .= get_special_flags($fields_required,$_REQUEST,"onchange='document.show_group_sel.submit();'" );
        $output .= '
        <noscript><br />
        '.get_buttons(array(array('type' => 'submit', 'value' => __('OK'), 'active' => false))).'</noscript>
        ';
        $output .= $this->end_form();

        $this->use_filter_options = true;
        return $output;
    }

    /**
     * Start the form
     *
     * @param string		$action		- Form Action value
     * @param string		$name			- Form name value
     * @param string		$target		- Form target value
     * @param string		$js			- Javascript string
     * @return string						HTML output
     */
    function start_make_form($action, $name, $target = 'self', $js = '') {
        $output = $this->start_form($action, $name, $target, $js);
        $output .= '
        <legend>'.__('Filter configuration').'</legend>
        ';
        $hidden = array('mode'              =>  stat,
                        'mode2'             =>  $this->mode2,
                        'action'            =>  'calc',
                        'use_filters'       =>   $_REQUEST['use_filters'],
                        'exclude_archived'  =>   $_REQUEST['exclude_archived'],
                        'exclude_read'      =>   $_REQUEST['exclude_read']);

        /*
        foreach($this->post_values as $k => $v) {
            if (
                ($k != 'userlist')
                && ($k != 'projectlist')
            )
            {
                $hidden[$k] = $v;
            }
        }
         */

        $output .= $this->hidden($hidden);

        return $output;
    }

    /**
     * End the form
     *
     * @param void
     * @return string						HTML output
     */
    function end_make_form() {
        $output .= '
        <br />'.get_buttons(array(array('type' => 'submit', 'value' => __('OK'), 'active' => false)))."\n";
        $output .= $this->end_form();

        return $output;
    }

    /**
     * Display the period select
     *
     * @param void
     * @return string						HTML output
     */
    function period() {
        global $date_format_object;

    	$start_day   = "01";
	    $start_month = "01";
    	$start_year  = date("Y");
	    $end_day     = date("d");
    	$end_month   = date("m");
        $end_year    = date("Y");

	    // start day value
    	$saveDateAnfang = (isset($this->post_values['anfang']))         ? $this->post_values['anfang']          : "$start_year-$start_month-$start_day";
        $saveDateEnde	= (isset($this->post_values['ende']))           ? $this->post_values['ende']            : "$end_year-$end_month-$end_day";
        $period_select  = (isset($this->post_values['period_select']))  ? $this->post_values['period_select']   : '';

        // value of the radio button
        if ($period_select) {
            $checked_period = 'checked="checked"';
            $checked_date   = '';
        } else {
            $checked_date   = 'checked="checked"';
            $checked_period = '';
        }

        $output = '
	    <fieldset>
    	<legend>'.__('period').'</legend>
        <input type="radio" name="periodtype" id="periodtype0" value="0" '.$checked_date.' />
        <label for="periodtype0">'.__('individual period').'</label><br />
        <label class="label_block" for="anfang">'.__('Begin:').'</label>
	    <input type="text" name="anfang" id="anfang" '.$date_format_object->get_maxlength_attribute().' '.$date_format_object->get_title_attribute().' '.dojoDatepicker('anfang', $saveDateAnfang).' /><br />
        <label class="label_block" for="ende">'.__('End:').'</label>
        <input type="text" name="ende" id="ende" '.$date_format_object->get_maxlength_attribute().' '.$date_format_object->get_title_attribute().' '.dojoDatepicker('ende', $saveDateEnde).' /><br style="clear:both" />
        <input type="radio" name="periodtype" id="periodtype1" value="1" '.$checked_period.' />
        <label for="periodtype1">'.__('fixed period').'</label><br />
        <label class="label_block" for="period_select">'.__('period').':</label>
        '.periode_get_date_selectbox("period_select",$period_select,'onchange="document.forms.frm.periodtype1.checked=true;"').'<br /><br />'.__('Remember, for see the project, the start date and the end date must be between the two dates that you put.<br /><br />- If the project start before the start date that you put, the project will not be showed.<br />- If the project end after the end date that you put, the project will not be showed.').'
        </fieldset>
        ';

        $this->use_period = true;
        return $output;
    }

    /**
     * Display a sinlge select for project selection
     *
     * @param void
     * @return string						HTML output
     */
    function single_project_selection() {
        $show_group = (isset($this->post_values['show_group'])) ? $this->post_values['show_group'] : 0;
        $output = '
	    <div style="float:left;margin-right:30px">
	    <label class="label_inline" for="projectlist">'.__('Project').':</label><br />
	    <select style="float:left;min-width:200px;" name="project_ID" id="project_ID">
        ';
        $output .= $this->show_projects("0",'');
        $output .= '
	    </select>
	    </div>
        <br />
        <br />
        ';

        $this->use_project = true;
        return $output;
    }

    /**
     * Display the two selects for project selection
     *
     * @param boolean	$use_sort		- Use sort buttons?
     * @return string							HTML output
     */
    function project_selection($use_sort) {
        $show_group = (isset($this->post_values['show_group'])) ? $this->post_values['show_group'] : 0;
        $output = '
	    <div style="float:left;margin-right:30px">
	    <label class="label_inline" for="projectlist">'.__('Projects').':</label><br />
        ';
        if ($use_sort) {
            $output .= '
        <table border="0">
        <tr>
            <td width="200" valign="top">
            ';
        }
        $output .= '
	    <select style="float:left;min-width:200px;" name="projectlist[]" id="projectlist" multiple="multiple" size="20">
    	    <option value="gesamt"';
        if ($this->post_values['projectlist'][0]=='gesamt' || !is_array($this->post_values['projectlist'])) {
            $output .= ' selected="selected"';
        }
        $output .= '>'.__('All').'</option>'."\n";
        $output .= $this->show_projects("0",'');
        $output .= '
	    </select>
        ';
        if ($use_sort) {
            $output .= '
            </td>
            <td valign="middle">
            ';
        }

        if ($use_sort) {
            $output .= "<input class='button' type='submit' name='movdownup' value='&uarr;' onclick=\"movePosOption('projectlist[]','up'); return false;\" /><br /><br />\n";
            $output .= "<input class='button' type='submit' name='movupdown' value='&darr;' onclick=\"movePosOption('projectlist[]','down'); return false;\" />\n";
            $output .= "
            </td>
        </tr>
        </table>";
        }

        $output .= '
	    </div>
        ';

        $this->use_project = true;
        return $output;
    }

    /**
     * Display user selection selects
     *
     * @param boolean	$use_sort		- Use sort buttons?
     * @return string							HTML output
     */
    function user_selection($use_sort) {
        global $user_name,$user_firstname,$user_type,$user_group,$user_kurz,$mode2;

        $output = '
        <div>
        <label for="userlist">'.__('Persons').':</label><br />
        ';

        if ($mode2 == 'mystat') {
            $output .= '
<div id="global-header">
    <!-- begin tab_selection -->
    <div id="global-panels-top">
    <ul>
        <li>Timescale</li>	</ul></div>	<div id="global-panels-top-right"><ul>
	<li><a href="../index.php?redirect=help&amp;link=timescale" id="help" target="_blank" title="Help">Help</a></li></ul></div>

    <!-- end tab_selection --></div><div id="global-content">
<div class="module_bar_top">
<a href="../timescale/timescale.php?mode2=capacityplan&amp;csrftoken=f3b6ee2815c359535791c701bccadd1d" class="button_link_active">Capacity plan</a> <a href="../timescale/timescale.php?mode2=taskplan&amp;csrftoken=cd08f907d1764284726dd736fa0d00ed" class="button_link_inactive">Task plan</a>
</div>
        <input type="hidden" name="userlist[]" id="userlist" value="'.$this->user_ID.'" />
        '.$user_name.', '.$user_firstname.'<br style="clear:both" />
            ';
        } else {
            if ($use_sort) {
                $output .= '
        <table border="0">
        <tr>
            <td width="200" valign="top">
                ';
            }
            $output .= '
        <select style="min-width:200px;" name="userlist[]" id="userlist" multiple="multiple" size="20">
            ';
		    // option 'all users' only available for usrs with chief status
		    if ($user_type == 2) {
                $output .= '
            <option value="gesamt"';
            }
            if ($this->post_values['userlist'][0] == 'gesamt' || !is_array($this->post_values['userlist'])) {
                $output .= ' selected="selected"';
            }
			$output.= '>'.__('All').'</option>'."\n";

    		// fetch all users from this group
	    	$result = db_query("SELECT ".DB_PREFIX."users.ID, nachname, vorname, kurz
                                  FROM ".DB_PREFIX."users, ".DB_PREFIX."grup_user
                                 WHERE ".DB_PREFIX."users.ID = user_ID
                                   AND ".DB_PREFIX."users.is_deleted is NULL
                                   AND grup_ID = ".(int)$user_group."
                              ORDER BY nachname") or db_die();
    		while ($row = db_fetch_row($result)) {
                $output .= '
            <option value="'.$row[0].'"';
                if ($this->post_values['userlist'][0] > 0 and in_array($row[0], $this->post_values['userlist'])) {
                   $output .= ' selected="selected"';
                }
	    		$output .= '>'.$row[1].', '.$row[2].'</option>'."\n";
		    }
            $output .= '
        </select>
            ';

            if ($use_sort) {
                $output .= '
            </td>
            <td valign="middle">
                ';
            }

            if ($use_sort) {
                $output .= "<input class='button' type='submit' name='movdownup' value='&uarr;' onclick=\"movePosOption('userlist[]','up'); return false;\" /><br /><br />\n";
                $output .= "<input class='button' type='submit' name='movupdown' value='&darr;' onclick=\"movePosOption('userlist[]','down'); return false;\" />\n";
                $output .= "
            </td>
        </tr>
        </table>";
            }
        }

        $output .= '
        </div>
        ';

        $this->use_users = true;
        return $output;
	}

	/**
	 * Display the selects for make the selection
	 *
	 * @param boolean		$use_project		- Display project selects?
	 * @param boolean		$use_users			- Display user selects?
	 * @param array			$check_buttons		- Array with extra checkbox data
	 * @param array			$radio_buttons		- Array with extra radio buttons data
	 * @param string		$additional			- Additional input
	 * @param boolean		$use_sort			- Use sort buttons?
	 * @return string								HTML output
	 */
    function multiple_selects($use_project = true, $use_users = true, $check_buttons = array(), $radio_buttons = array(), $additional=null, $use_sort = false) {
        $output = '
        <fieldset>
        <legend>'.__('Project / User selections').'</legend><br />
        ';

        if ($use_project) {
            $output .= $this->project_selection($use_sort);
        }
        if ($use_users) {
            $output .= $this->user_selection($use_sort);
        }

        if (!is_null($additional)) {
        	$output .= $additional;
        }

        foreach($check_buttons as $k => $v) {
            if (!isset($_REQUEST[$v])) {
                $_REQUEST[$v] = $this->post_values[$v];
            }
        }

        if (!$use_users) {
            $output .= '
        <br />
        <div style="min-width:200px; display:inline">
        <table>
        <tr>
            <td height="270">&nbsp;</td>
        </tr>
        </table>
        </div>
        <br />
            ';
        }
        if (!empty($check_buttons)) {
            $output .= get_special_flags($check_buttons,$_REQUEST);
        }
        $output .= $this->display_radio_buttons($radio_buttons);

        $output .= '
        </fieldset>
        ';

        return $output;
    }

    /**
     * Display radio buttons
     *
     * @param array		$radio_buttons		- Array with the extra radio buttons data
     * @param boolean	$inline					- Use inline style?
     * @param string		$onchange			- onChange javascript string
     * @return string								HTML output
     */
    function display_radio_buttons($radio_buttons,$inline = true, $onchange = '') {
        $output = '';
        if (!empty($radio_buttons)) {
            $count = 0;
            if (!empty($radio_buttons['text_display'])) {
                $output .= '<br />'.$radio_buttons['text_display'].": ";
            }

            if (!$inline) {
                $output .= '<br />';
            }
            // if no radio button is checked, select the first one
            !$this->post_values[$radio_buttons['field_name']] ? $check_first = true : $check_first = false;

            foreach ($radio_buttons['field_data'] as $data) {
                if ($data['value'] == $this->post_values[$radio_buttons['field_name']] or $check_first) {
                    $check = 'checked="checked"';
                    $check_first = false;
                } else {
                    $check = '';
                }
                $output .= '
            <input type="radio" name="'.$radio_buttons['field_name'].'" id="'.$radio_buttons['field_name'].$count.'" value="'.$data['value'].'" '.$check.' '.$onchange.'>
            <label for="'.$radio_buttons['field_name'].$count.'">'.$data['text'].'</label>
                ';
                if (!$inline) {
                    $output .= '<br />';
                }
                $count++;
            }
        }
        $output .= '<br />';
        return $output;
    }

    /**
     * Display checkbox buttons
     *
     * @param array		$check_buttons		- Array with the extra chekbox data
     * @return string								HTML output
     */
    function display_check_buttons($check_buttons) {
        $output = '';
        if (!empty($check_buttons)) {
            foreach($check_buttons as $data) {
            	if ($data['value'] = "on") {
                    $flag = ' checked="checked"';
                } else {
                    $flag = '';
                }
                $output .= '
        <br />
        <input type="checkbox" name="'.$data['name'].'" '.$flag.' />
        <label for='.$data['name'].'> '.$data['text'].'</label>
                ';
            }
        }
        return $output;
    }

    /**
     * Display all the visible project and subprojects
     *
     * @param int		$parent_ID		- Parent ID value
     * @param int		$indent			- Indent for show sub projects
     * @return string					HTML output
     */
    function show_projects($parent_ID, $indent = 0) {
        global $leader, $sql_user_group, $mode2, $user_type, $fields;

        $output = '';

        // fetch parent project
        $before_where = special_sql_filter_flags('projects', $this->post_values);
	    $where = "";
	    $where = special_sql_filter_flags('projects', $this->post_values, false);
        if (isset($this->post_values['use_filters']) && ($this->post_values['use_filters'] == "on") ) {
            // List of fields in the db table, needed for filter
            require_once(LIB_PATH.'/dbman_lib.inc.php');
            $fields = build_array('projects', '', 'view');
            $where .= main_filter('', '', '', '', 'projects','','');
        }

        // 1. case myprojects - independent from the group
    	if (($mode2 == "mystat") and $this->post_values['show_group'] <> '1') {
	        $query = "SELECT ID, name, chef
                        FROM ".DB_PREFIX."projekte $before_where
                       WHERE ".DB_PREFIX."projekte.is_deleted is NULL
                         AND ".DB_PREFIX."projekte.parent = ".(int)$parent_ID." $where".sort_string('saved_settings');
    	}
	    // 2. case: all of the group
    	else {
	        $query = "SELECT ID, name
                        FROM ".DB_PREFIX."projekte $before_where
                       WHERE ".DB_PREFIX."projekte.is_deleted is NULL
                         AND parent = ".(int)$parent_ID." $where
                         AND $sql_user_group".sort_string('saved_settings');
	    }
    	$result = db_query($query) or db_die();
	    while ($row = db_fetch_row($result)) {
    		$output .= "<option value='$row[0]'";
	    	if ($this->post_values['projectlist'][0] and in_array($row[0], $this->post_values['projectlist'])) $output.= ' selected="selected"';
    		$output .= ">";
	    	for ($i = 1; $i <= $indent; $i++) {
		    	$output .= "&nbsp;&nbsp;";
			}
    		$output .= $row[1]."</option>\n";
            $indent++;
            $output .= $this->show_projects($row[0], $indent);
            $indent--;
	    }

        return $output;
    }

    /**
     * display some special extra fields
     *
     * @param string		$type				- Type of the fiels
     * @param array		$parameters		- Misc array with parameters for the fiels
     * @return string							HTML output
     */
    function extra_fields($type,$parameters = array()) {
        $output = '';

        switch ($type) {
            case 'fieldset_start':
                $output .= '
        <fieldset>
	    <legend>'.$parameters['legend'].'</legend>
                ';
                break;
            case 'multiple_select':
                if (isset($parameters['in_div'])) {
                    $output .= '
        <div style="'.$parameters['in_div'].'">
                    ';
                }

                $output .= '
        <label class="'.$parameters['class'].'" for="'.$parameters['name'].'">'.$parameters['text'].':</label>
        <br />
                ';

                if (isset($parameters['use_sort'])) {
                    $output .= '
        <table border="0">
        <tr>
            <td width="200" valign="top">
                    ';
                }

                $output .= '
	        <select style="'.$parameters['style'].'" name="'.$parameters['name'].'[]" id="'.$parameters['name'].'" multiple="multiple" size="'.$parameters['size'].'">
                <option value="gesamt" ';
                if ($this->post_values[$parameters['name']][0]=='gesamt' || !is_array($this->post_values[$parameters['name']])) {
                    $sel = ' selected="selected"';
                } else {
                    $sel = '';
                }
                $output .= $sel .'>'.__("All").'</option>'."\n";

                if (!isset($this->post_values[$parameters['name']])) {
                    $this->post_values[$parameters['name']] = array();
                }
                foreach($parameters['values'] as $v => $display) {
                    if (in_array($v,$this->post_values[$parameters['name']])) {
                        $sel = ' selected="selected"';
                    } else {
                        $sel = '';
                    }
                    $output .= '
                <option value="'.$v.'" '.$sel.'>'.xss($display).'</option>
                    ';
	            }
                $output.= '
            </select>
                ';


                if (isset($parameters['use_sort'])) {
                    $output .= '
            </td>
            <td valign="middle">
                    ';
                    $output .= "<input class='button' type='submit' name='movdownup' value='&uarr;' onclick=\"movePosOption('".$parameters['name']."[]','up'); return false;\" /><br /><br />\n";
                    $output .= "<input class='button' type='submit' name='movupdown' value='&darr;' onclick=\"movePosOption('".$parameters['name']."','down'); return false;\" />\n";
                    $output .= "
            </td>
        </tr>
        </table>
                    ";
                }

                if (isset($parameters['in_div'])) {
                    $output .= '
        </div>
                    ';
                }
                break;
            case 'radio_buttons':
                $output .= '
	    <br style="clear: both" /><br />
                ';
                $output .= $this->display_radio_buttons($parameters['radio_buttons'],true);
                break;
            case 'fieldset_end':
                $output .= '
        </fieldset>
                ';
                break;
            case 'simple_select':
                if (isset($parameters['in_div'])) {
                    $output .= '
        <div style="'.$parameters['in_div'].'">
                    ';
                }

                $output .= '
        <label class="'.$parameters['class'].'" for="'.$parameters['name'].'">'.$parameters['text'].':</label>
        <br />
            <select style="'.$parameters['style'].'" name="'.$parameters['name'].'" id="'.$parameters['name'].'">
                ';

                foreach($parameters['values'] as $v => $display) {
                    if (isset($this->post_values[$parameters['name']])) {
                        if ($v == $this->post_values[$parameters['name']]) {
                            $sel = ' selected="selected"';
                        } else {
                            $sel = '';
                        }
                    } else {
                        $sel = '';
                    }
                    $output .= '
                <option value="'.$v.'" '.$sel.'>'.xss($display).'</option>
                    ';
	            }
                $output.= '
            </select>
                ';

                if (isset($parameters['in_div'])) {
                    $output .= '
        </div>
                    ';
                }
                break;
        }

        return $output;
    }

    /**
     * Get a POST values
     *
     * @param string		$field		- Field to get
     * @return misc					Value of the field
     */
    function get_post_value($field) {
        return (isset($this->post_value[$field])) ? $this->post_value[$field] : '';
    }

    /**
     * Special flags for timecale
     *
     * @param void
     * @return string				HTML output
     */
    function consider_special_flags(){
        global $mode2;
        $fields_required= array(__('Consider current set filters')=>'use_filters',
        __('Exclude archived elements')=>'exclude_archived',
        __('Exclude read elements')=>'exclude_read');
        $checked=array();
        $checked['use_filters']=$_REQUEST['use_filters'];
        $checked['exclude_archived']=$_REQUEST['exclude_archived'];
        $checked['exclude_read']=$_REQUEST['exclude_read'];

        $output .= "<form style='display:inline;' action='timescale.php?mode2=$mode2' method='post' name='change_settings' target><br />";
        $output.= '<fieldset class="settings">';
        $output.= '<input type="hidden" name="change_settings" value="change_settings">';
        $output .= get_special_flags($fields_required,$checked,"onchange='document.change_settings.submit();'");
        $output .= "</fieldset>
                    </form>";
        return $output;

    }
}
?>
