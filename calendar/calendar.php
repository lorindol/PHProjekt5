<?php
/**
* calendar controller script
*
* @package    calendar
* @module     main
* @author     Albrecht Guenther, $Author: polidor $
* @licence    GPL, see www.gnu.org/copyleft/gpl.html
* @copyright  2000-2006 Mayflower GmbH www.mayflower.de
* @version    $Id: calendar.php,v 1.106.2.5 2007/09/04 21:25:40 polidor Exp $
*/

define('PATH_PRE','../');
$module = 'calendar';
require_once(PATH_PRE.'lib/lib.inc.php');
require_once(PATH_PRE.'lib/dbman_lib.inc.php');
include_once('./calendar.inc.php');
include_once('./calendar_forms.php');
$_SESSION['common']['module'] = 'calendar';
$contextmenu = 1;

// check role
if (!PHPR_CALENDAR || check_role('calendar') < 1) die('You are not allowed to do this!');

// Archive mode
archive_mode('calendar');
// Archive Flag
// set_archiv_flag = 1 -> Move to archive
// set_archiv_flag = 0 -> Take back from archive
if (isset($set_archiv_flag))   set_archiv_flag($ID_s, 'calendar', $set_archiv_flag);

calendar_init();


#############################################
// fallback if no settings are done
$screen          = isset($screen) ? (int) $screen : 800; // width of the screen/browser window px
$cal_leftframe   = isset($cal_leftframe) ? (int) $cal_leftframe : 210; // width of the left frame in new calendar px
$timestep_daily  = isset($timestep_daily) ? (int) $timestep_daily : 15; // timestep of single day view
$timestep_weekly = isset($timestep_weekly) ? (int) $timestep_weekly : 30; // timestep of single week view
$ppc             = isset($ppc) ? (int) $ppc : 6; // px per char for new calendar (not exact in case of proportional font)
$cut             = isset($cut) ? (int) $cut : 1; // cut the event text length yes:1 no:0
$nav_pos         = isset($nav_pos) ? (int) $nav_pos : 0;
$nav_space       = isset($nav_space) ? (int) $nav_space : 0;
$wd_date = 10 * $ppc;                         // column width
$wd_time =  4 * $ppc;
$wd_week =  2 * $ppc;
#############################################
// background for events
//                   frei       *1)           offen      zugestimmt abgelehnt
//$calcolor = array('#FDFDFD', $terminfarbe, '#6699ff', '#55BB66', '#FF5544');
// *1) = Organisator, der nicht selbst teilnimmt oder Termin im alten System ohne Einladung

$cal_class = array( 'calendar_day_current'
,'calendar_day_current'
,'calendar_event_open'
,'calendar_event_accept'
,'calendar_event_reject'
,'calendar_event_canceled'
);

$cal_wd = ($screen - $cal_leftframe - ($nav_pos * $nav_space) - 45);

// the sign for adding a new event
define('PHPR_CALENDAR_ADD_SIGN', '<img src="../img/open.gif" alt=""/>');

// catch the output cause we need the warning/error messages (urghs?!)
// and some other correct settings for the header ($mode, ...)
ob_start();
$do_the_main_switch = true;
// handle the combi stuff
if ($view == 3) {
    //if (isset($_REQUEST['action_combi_to_selector'])) {
    if ( isset($_REQUEST['action_select_user_or_profile']) &&
    isset($_REQUEST['user_or_profile_selection']) ) {
        if ($_REQUEST['user_or_profile_selection'] == 'action_combi_to_selector') {
            $combisel = array();
            if (isset($_SESSION['calendardata']['combisel'])) {
                $combisel = $_SESSION['calendardata']['combisel'];
            }
            $formdata['_selector_type'] = 'member';
            $formdata['_title']    = __('User selection');
            $formdata['_selector'] = $combisel;
            $formdata['_view']     = $view;
            $formdata['_mode']     = $mode;
            $formdata['_axis']     = $axis;
            $formdata['_dist']     = $dist;
            $formdata['_return']   = 'action_selector_to_combi_ok';
            $formdata['_cancel']   = 'action_selector_to_combi_cancel';
            $formdata['_selector_name'] = 'calendar_selector_';
            $_SESSION['calendardata']['formdata'] = $formdata;
            $delete_selector_filters = true;
            require_once('./calendar_selector.php');
            $mode = 'forms';
            $do_the_main_switch = false;
        }
        else if (is_numeric($_REQUEST['user_or_profile_selection'])) {
            // do the select-users-from-profile stuff
            $profile_id = (int) $_REQUEST['user_or_profile_selection'];
            calendar_select_users_from_profile($profile_id);
        }
    }
    else if ( isset($_REQUEST['action_selector_to_combi_ok']) ||
    isset($_REQUEST['action_selector_to_combi_cancel']) ) {
        if (isset($_REQUEST['action_selector_to_combi_ok'])) {
            $selector = xss_array($_POST[$_SESSION['calendardata']['formdata']['_selector_name']."dsts"]);
            $_SESSION['calendardata']['combisel'] = $selector;
        }
        $mode = $_SESSION['calendardata']['formdata']['_mode'];
        $axis = $_SESSION['calendardata']['formdata']['_axis'];
        $dist = $_SESSION['calendardata']['formdata']['_dist'];
        unset($_SESSION['calendardata']['formdata']);
        unset($_REQUEST['filterform']);
    }
    else if (isset($_REQUEST['action_selector_to_selector']) ||
    isset($_REQUEST['filterform']) ||
    isset($_REQUEST['filterdel'])) {
        require_once('./calendar_selector.php');
        $mode = 'forms';
        $do_the_main_switch = false;
    }

    $act_as   = 0;
    $combisel = array();

    if (isset($_SESSION['calendardata']['combisel'])) {
        if (count($_SESSION['calendardata']['combisel']) > 1) {
            $combisel = $_SESSION['calendardata']['combisel'];
            if ($mode == 2 || $mode == 'year') {
                // switch to mode 1 cause mode 2 and 'year'
                // are not available in the combi view
                $mode = 1;
            }
            if ($mode != 'forms' && $mode != 'data' &&
            $mode <= 4 && $do_the_main_switch) {
                $mode += 4;
            }

        } else if ($_SESSION['calendardata']['combisel'][0]) {
            if ($_SESSION['calendardata']['combisel'][0] != $user_ID) {
                $act_as = $_SESSION['calendardata']['combisel'][0];
            }
        }
    }
}

if ($view == 4 && empty($act_for)) {
    // show nothing if "act_for" is not available on "view 4"
    echo '';

} else if ($do_the_main_switch) {
    // special stuff for $mode == 'data'
    if ($mode == 'data') {
        // Fix submit when ckick enter in any field
        if (isset($_REQUEST['action_form_to_contact_selector_x']) && $_REQUEST['action_form_to_contact_selector_x'] == "-1") {
            unset($_REQUEST['action_form_to_contact_selector_x']);
            unset($_REQUEST['action_form_to_contact_selector_y']);
            if (isset($_REQUEST['ID']) && $_REQUEST['ID'] == 0) $_REQUEST['action_create_event'] = __('OK');
            else $_REQUEST['action_update_event'] = __('OK');
        }
        if (isset($_REQUEST['action_form_to_project_selector_x']) && $_REQUEST['action_form_to_project_selector_x'] == "-1") {
            unset($_REQUEST['action_form_to_project_selector_x']);
            unset($_REQUEST['action_form_to_project_selector_y']);
            if (isset($_REQUEST['ID']) && $_REQUEST['ID'] == 0) $_REQUEST['action_create_event'] = __('OK');
            else $_REQUEST['action_update_event'] = __('OK');
        }
        if (isset($_REQUEST['action_remove']) || isset($_REQUEST['action_check_dateconflict'])) {
            echo calendar_forms_get_view();

        } else if (isset($_REQUEST['action_cancel_event'])) {
            if (!empty($_SESSION['calendardata']['refback'])) {
                header('Location: '.str_replace("&amp;","&",$_SESSION['calendardata']['refback']));
                exit();
            }
            $mode = (!empty($cal_mode)) ? $cal_mode : 1;

        } else if (isset($_REQUEST['action_delete_file'])) {
            if (calendar_can_delete_file()) {
                calendar_delete_file($_REQUEST['ID']);
            }

            // Selector for Members
        } else if (isset($_REQUEST['action_form_to_selector']) ||
        isset($_REQUEST['action_form_to_selector_x'])) {
            $formdata['_selector_type'] = 'member';
            $formdata['_title']    = __('Member selection');
            $formdata['_selector'] = $formdata['invitees'];
            $formdata['_ID']       = $ID;
            $formdata['_view']     = $view;
            $formdata['_mode']     = $mode;
            $formdata['_act_for']  = $act_for;
            $formdata['_return']   = 'action_selector_to_form_ok';
            $formdata['_cancel']   = 'action_selector_to_form_cancel';
            $formdata['_selector_name'] = 'calendar_selector_';
            $_SESSION['calendardata']['formdata'] = $formdata;
            $delete_selector_filters = true;
            require_once('./calendar_selector.php');

        } else if (isset($_REQUEST['action_selector_to_selector'])) {
            require_once('./calendar_selector.php');

        } else if (isset($_REQUEST['action_selector_to_form_ok']) ||
        isset($_REQUEST['action_selector_to_form_cancel'])) {
            // back from selector (okay or cancel)
            if (isset($_REQUEST['action_selector_to_form_ok'])) {
                // pressed okay
                $selector = xss_array($_POST[$_SESSION['calendardata']['formdata']['_selector_name']."dsts"]);
                $_SESSION['calendardata']['formdata']['invitees'] = $selector;
            }
            // okay & cancel
            $formdata = $_SESSION['calendardata']['formdata'];
            unset($_SESSION['calendardata']['formdata']);
            unset($_REQUEST['filterform']);
            echo calendar_forms_get_view();

            // Selector for Contacts
        } else if (isset($_REQUEST['action_form_to_contact_selector']) ||
        isset($_REQUEST['action_form_to_contact_selector_x'])) {
            $formdata['_selector_type'] = 'contact';
            $formdata['_title']         = __('Contact selection');
            $formdata['_selector']      = $formdata['contact'];
            $formdata['_ID']            = $ID;
            $formdata['_view']          = $view;
            $formdata['_mode']          = $mode;
            $formdata['_act_for']       = $act_for;
            $formdata['_return']        = 'action_contact_selector_to_form_ok';
            $formdata['_cancel']        = 'action_contact_selector_to_form_cancel';
            $formdata['_selector_name'] = 'calendar_selector_';
            $_SESSION['calendardata']['formdata'] = $formdata;
            $delete_selector_filters = true;
            require_once('./calendar_selector.php');

        } else if (isset($_REQUEST['action_contact_selector_to_form_ok']) ||
        isset($_REQUEST['action_contact_selector_to_form_cancel'])) {
            // back from selector (okay or cancel)
            if (isset($_REQUEST['action_contact_selector_to_form_ok'])) {
                // pressed okay
                $selector = xss_array($_POST[$_SESSION['calendardata']['formdata']['_selector_name']."srcs"]);
                $_SESSION['calendardata']['formdata']['contact'] = $selector;
            }
            // okay & cancel
            $formdata = $_SESSION['calendardata']['formdata'];
            unset($_SESSION['calendardata']['formdata']);
            unset($_REQUEST['filterform']);
            echo calendar_forms_get_view();

            // Selector for Projekts
        } else if (isset($_REQUEST['action_form_to_project_selector']) ||
        isset($_REQUEST['action_form_to_project_selector_x'])) {
            $formdata['_selector_type'] = 'project';
            $formdata['_title']         = __('Project selection');
            $formdata['_selector']      = $formdata['projekt'];
            $formdata['_ID']            = $ID;
            $formdata['_view']          = $view;
            $formdata['_mode']          = $mode;
            $formdata['_act_for']       = $act_for;
            $formdata['_return']        = 'action_project_selector_to_form_ok';
            $formdata['_cancel']        = 'action_project_selector_to_form_cancel';
            $formdata['_selector_name'] = 'calendar_selector_';
            $_SESSION['calendardata']['formdata'] = $formdata;
            $delete_selector_filters = true;
            require_once('./calendar_selector.php');

        } else if (isset($_REQUEST['action_project_selector_to_form_ok']) ||
        isset($_REQUEST['action_project_selector_to_form_cancel'])) {
            // back from selector (okay or cancel)
            if (isset($_REQUEST['action_project_selector_to_form_ok'])) {
                // pressed okay
                $selector = xss_array($_POST[$_SESSION['calendardata']['formdata']['_selector_name']."srcs"]);
                $_SESSION['calendardata']['formdata']['projekt'] = $selector;
            }
            // okay & cancel
            $formdata = $_SESSION['calendardata']['formdata'];
            unset($_SESSION['calendardata']['formdata']);
            unset($_REQUEST['filterform']);
            echo calendar_forms_get_view();
        } else if (isset($_REQUEST['filterform']) ||
        isset($_REQUEST['filterdel'])) {
            require_once('./calendar_selector.php');
        } else {
            require_once('./calendar_data.php');
            if (!calendar_action_data()) {
                echo calendar_forms_get_view();
            } else {
                $query_str = "";
                if (isset($_REQUEST['action_create_update_event'])) {
                    $result = db_query("SELECT MAX(ID)
                                        FROM ".DB_PREFIX."termine
                                        ") or db_die();
                    $row = db_fetch_row($result);
                    $new_id = $row[0];
                    $query_str = "calendar.php?ID=".$new_id."&amp;mode=forms&amp;view=0";
                    header('Location: '.$query_str);
                    exit();
                }
                else if (isset($_REQUEST['action_apply_event'])) {
                    if ($_SESSION['calendardata']['current_event']['serie_id']) {
                        $update_id = $_SESSION['calendardata']['current_event']['serie_id'];
                    } else {
                        $update_id = $_SESSION['calendardata']['current_event']['ID'];
                    }
                    $query_str = "calendar.php?ID=".$update_id."&mode=forms&view=0";
                    
                    header('Location: '.$query_str);
                    exit();
                }
                else if (!empty($_SESSION['calendardata']['refback'])) {
                    header('Location: '.str_replace("&amp;","&",$_SESSION['calendardata']['refback'].$query_str));
                    exit();
                }
                $mode = (!empty($cal_mode)) ? $cal_mode : 1;
            }
        }
    }

    // the main switch, without 'data'
    switch ($mode) {
        case 1:
            // day view
            calendar_calc_wd(0, 2);
            $time_step = $timestep_daily;
            require_once('./calendar_view_day.php');
            $calendar_view_day = new Calendar_View_Day();
            echo $calendar_view_day->get_view();
            break;
        case 2:
            // working week view (mo-fr)
        case 3:
            // week view (mo-su)
            calendar_calc_wd($wd_time, 7);
            $time_step = $timestep_weekly;
            require_once('./calendar_view_week.php');
            $calendar_view_week = new Calendar_View_Week();
            echo $calendar_view_week->get_view();
            break;
        case 4:
            // month view
            calendar_calc_wd(0, 7);
            require_once('./calendar_view_month.php');
            echo calendar_view_month_get_view();
            break;
        case 5:
        case 6:
        case 7:
        case 8:
            // combi view
            require_once('./calendar_view_combi.php');
            break;
        case 'year':
            // year view
            require_once('./calendar_view_year.php');
            echo calendar_view_year_get_view();
            break;
        case 'view':
            // list view
            require_once('./calendar_view.php');
            break;
        case 'forms':
            // new/edit/show single event view
            echo calendar_forms_get_view();
            break;
    }
    // reset $mode if the combi stuff has changed that..
    if ($mode > 4) $mode -= 4;
}

$calendar_view = ob_get_contents();
ob_end_clean();

if ($mode != 'forms' && $mode != 'data') {
    // save the url for the referer-back stuff
    $_SESSION['calendardata']['refback'] = xss($_SERVER['REQUEST_URI']);

    // build the meta entry to reload the calendar view
    if (isset($cal_freq) && $cal_freq > 0 && $view == 0) {
        $he_add = array( '<meta http-equiv="refresh" content="'.($cal_freq * 60).
        '; URL='.xss($_SERVER['REQUEST_URI']).'" />' );
    }
}
#echo "<br />".str_repeat('-',100).$_SESSION['calendardata']['refback']."\n";

//$js_inc[] = ' src="calendar.js">';
echo set_page_header();

if ($justform <= 0) {
    require_once(PATH_PRE.'lib/navigation.inc.php');
}


echo '
<!-- begin calendar control content -->
';
require_once('./calendar_control.php');
echo '

<!-- end calendar control content -->

<!-- begin calendar content -->


<!-- begin calendar view content -->
<a name="content"></a>
'.$calendar_view.'
<!-- end calendar view content -->

</div>
<!-- end calendar content -->

</div>
</body>
</html>
';


/**
 * initialize the calendar stuff and make some security checks
 *
 * @return void
 */
function calendar_init() {
    global $ID, $mode, $view, $year, $month, $day, $act_for, $formdata, $invitees, $selector;
    global $axis, $dist, $cal_mode, $justform, $output, $serie_weekday;
    global $date_format_object;

    $output = '';

    // convert user date format back to db/iso date format (from the form)
    if (isset($_REQUEST['formdata']['datum'])) {
        $_REQUEST['formdata']['datum'] = $date_format_object->convert_user2db($_REQUEST['formdata']['datum']);
    }
    if (isset($_REQUEST['formdata']['serie_bis'])) {
        $_REQUEST['formdata']['serie_bis'] = $date_format_object->convert_user2db($_REQUEST['formdata']['serie_bis']);
    }

    if (!isset($_REQUEST['day']) || !isset($_REQUEST['month']) || !isset($_REQUEST['year']) ||
    isset($_REQUEST['action_select_today'])) {
        // set this to today if a date component is missing
        // or the "today" button was pressed
        today();
        $_REQUEST['day']   = $day;
        $_REQUEST['month'] = $month;
        $_REQUEST['year']  = $year;
    } else if (isset($_REQUEST['formdata']['datum'])) {
        // else set this to the given date in the form to go back to that date
        $_REQUEST['day']   = substr($_REQUEST['formdata']['datum'], -2);
        $_REQUEST['month'] = substr($_REQUEST['formdata']['datum'], 5, 2);
        $_REQUEST['year']  = substr($_REQUEST['formdata']['datum'], 0, 4);
    }
    $day   = (int) $_REQUEST['day'];
    $month = (int) $_REQUEST['month'];
    $year  = (int) $_REQUEST['year'];

    // check date stuff
    if ($year<1000 || $year>date('Y')+1000) $year = date('Y');
    if      ($month<1)  $month = 1;
    else if ($month>12) $month = 12;
    $max_days = date('t', mktime(0,0,0, $month, 1, $year));
    if      ($day<1)         $day = 1;
    else if ($day>$max_days) $day = $max_days;

    settype($_REQUEST['selector'], 'array');
    $selector = $_REQUEST['selector'];
    settype($_REQUEST['invitees'], 'array');
    $invitees = $_REQUEST['invitees'];
    settype($_REQUEST['serie_weekday'], 'array');
    $serie_weekday = $_REQUEST['serie_weekday'];
    if (isset($_REQUEST['formdata'])) {
        $_REQUEST['formdata']['invitees']      = $invitees;
        $_REQUEST['formdata']['serie_weekday'] = $serie_weekday;
        $formdata = $_REQUEST['formdata'];
    }

    $ID       = $_REQUEST['ID']       = isset($_REQUEST['ID']) ? (int) $_REQUEST['ID'] : 0;
    $justform = $_REQUEST['justform'] = isset($_REQUEST['justform']) ? (int) $_REQUEST['justform'] : 0;

    if (isset($_REQUEST['contact_ID'])) {
        $formdata['contact'] = $_REQUEST['contact_ID'] = (int) $_REQUEST['contact_ID'];
    }
    if (isset($_REQUEST['projekt_ID'])) {
        $formdata['projekt'] = $_REQUEST['projekt_ID'] = (int) $_REQUEST['projekt_ID'];
    }

    if (!isset($_REQUEST['mode'])) $_REQUEST['mode'] = (!empty($cal_mode)) ? $cal_mode : 1;
    $mode = $_REQUEST['mode'] = xss($_REQUEST['mode']);
    if (!isset($_REQUEST['view'])) $_REQUEST['view'] = 0;
    $view = $_REQUEST['view'] = xss($_REQUEST['view']);
    if (!isset($_REQUEST['axis']) || !in_array($_REQUEST['axis'], array('v','h','x'))) {
        $_REQUEST['axis'] = 'v';
    }
    $axis = $_REQUEST['axis'];
    if (!isset($_REQUEST['dist'])) $_REQUEST['dist'] = 0;
    $dist = $_REQUEST['dist'] = xss($_REQUEST['dist']);

    // checks and defs due to act_for
    if (!isset($_REQUEST['act_for']) || !calendar_can_act_for($_REQUEST['act_for'])) {
        $_REQUEST['act_for'] = 0;
    }
    $act_for = $_REQUEST['act_for'] = (int) $_REQUEST['act_for'];
}

?>
