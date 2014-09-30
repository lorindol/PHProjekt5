<?php
// timecard_forms.php - PHProjekt Version 5.2
// copyright  ©  2004 Nina Schmitt
// www.phprojekt.com

// Author: Nina Schmitt, $Author: gustavo $
// $Id: timecard_books.php,v 1.81.2.9 2007/10/01 16:58:25 gustavo Exp $


$css_void_background_image = true;

// check whether the lib has been included - authentication!
$module = 'timecard';
if(!defined('PATH_PRE')){
    define('PATH_PRE','../');
}
include_once(PATH_PRE.'lib/lib.inc.php');
include_once("./timecard_date.inc.php");
include_once("./timecard.inc.php");

// check role
$fld_nr=1;
if (check_role("timecard") < 1) { die("You are not allowed to do this!"); }

if($action == "clockin") {
    // Projekt lists
    $my_projekts = array();
    $result_projects = db_query("SELECT project_ID FROM ".DB_PREFIX."project_users_rel where user_ID = ".(int)$user_ID);

    while ($row_projekts = db_fetch_row($result_projects)) {
        $my_projekts[] = $row_projekts[0];
    }
    show_project_clock($my_projekts);
}
else{
    $output.= '<div style="width:1120px">';
    $output.= '<div id="left_container" style="width:auto">';
    $output.= show_leftbox($date);
    $output.= '</div><div id="right_container">';
    $output.= show_rightbox($month,$year);
    $output.= '</div>';
    $output.= '</div>';
}
?>
<?php
function show_rightbox($month,$year){
    global $name_day, $name_month, $user_ID, $submode;
    $output = '<h1>'.__('My project bookings overview').' '.$name_month[intval($month)]." ".$year.'</h1>';
    //main content
    $hidden_fields = array ( "mode" => "view", "submode" => $submode);
    $output.= '
    <form style="display: inline;" action="timecard.php" name="date" method="post">
    <fieldset>
    <legend>'.__('choose month').'</legend>
    '.hidden_fields($hidden_fields).'
    <select name="month">';
    for ($a=1; $a<13; $a++) {
        $mo = date("n", mktime(0,0,0,$a,1,$year));
        $name_of_month = $name_month[$mo];
        if ($mo == $month) {$output .= "<option value='$a' selected='selected'>$name_of_month</option>\n";}
        else {$output .= "<option value='$a'>$name_of_month</option>\n";}
    }
    $output .= '</select><select name="year">';
    for ($i=$year-2; $i<=$year+5; $i++) {
        if ( $i == $year) {$output .= "<option selected='selected'>$i</option>\n";}
        else {$output .="<option>$i</option>\n";}
    }
    $output .= '</select>'."\n";
    $output .= get_go_button($class='button', $type='button', $name='',$value=__('GO')).'
    </fieldset>
    </form>
    <br />';

    //table with detailed worktime entries
    $output.="<table class=\"ruler\" style='margin-right:5px' summary=\"$tc_sum\">
    <thead>
        <tr>
            <th scope=\"col\" title=\"".__('Weekday')."\">".__('Weekday')."</th>
            <th scope=\"col\" title=\"".__('Date')."\">".__('Date')."</th>
            <th scope=\"col\" title=\"".__('Start')."\">".__('Start')."</th>
            <th scope=\"col\" title=\"".__('End')."\">".__('End')."</th>
            <th scope=\"col\" colspan='2' title=\"".__('Hours')."\">".__('Hours')." h/m</th>
        </tr>
    </thead>";
    $outbody="<tbody>";
    //get start end end time of each workday
    $query="SELECT MAX(ID), datum, MIN(anfang) as anf, MAX(ende) as max
                        FROM ".DB_PREFIX."timecard
                        WHERE users = ".(int)$user_ID." AND datum like '$year-$month-%'
                        GROUP by datum ORDER BY datum DESC";
    $result = db_query($query)
    or db_die();
    $int=0;
    $booked_time=0;
    $date_row=array();
    while($row_tmp = db_fetch_row($result)){
        $date_row[$row_tmp[1]]= $row_tmp;
    }
   $query="SELECT  datum
                        FROM ".DB_PREFIX."timeproj
                        WHERE users = ".(int)$user_ID." AND datum like '$year-$month-%'                        
                        GROUP by datum ORDER BY datum DESC";
    $result = db_query($query)
    or db_die();
    while($row_tmp = db_fetch_row($result)){
        if(!isset($date_row[$row_tmp[0]]))$date_row[$row_tmp[0]]= array(0=>'',1=>$row_tmp[0],2=>'',3=>'');
        
    }
    krsort($date_row);
    foreach ($date_row as $row){
        $int++;
        $week_day=get_weekday($row[1]);
        $dsum='';
        if (($row[2] or $row[2]==0) and $row[3]) {
            $result2= db_query("SELECT anfang, ende
								FROM ".DB_PREFIX."timecard
								WHERE users = ".(int)$user_ID." AND  datum like '$row[1]'
								ORDER BY datum desc")
            or db_die();
            while($row2 = db_fetch_row($result2)){
                $row2[0] = check_4d($row2[0]);
                $row2[1] = check_4d($row2[1]);
                if($row2[0]&&$row2[1]){
                    $dsum =$dsum+(substr($row2[1],0,2) - substr($row2[0],0,2))*60 + substr($row2[1],2,4) - substr($row2[0],2,4);
                }

            }
            $sum1 = $sum1 + $dsum;
        }
        $h1 = floor($dsum/60);
        $m1 = $dsum - $h1 * 60;
        $outbody.= "<tr";
        if($int%2==1){
            $outbody.= " class=\"unev\" ";
        }
        $href = "timecard.php?submode=proj&amp;year=$year&amp;date=$row[1]&amp;month=$month";
        $outbody.="> <td scope='row' class='timecard_day' style='padding-left:3px'><a class='navLink' href='$href'>$name_day[$week_day]</a></td>
        <td class='timecard_day'>$row[1]</td>
        <td class='timecard_day' style='text-align:right;'>".check_4d($row[2])."</td>
        <td class='timecard_day' style='text-align:right;'>".check_4d($row[3])."</td>
        <td class='timecard_day' style='text-align:right;'>$h1</td>
        <td class='timecard_day' style='text-align:right;'>$m1</td>
        </tr>";
        $proj_array= show_prbookings($row[1], $booked_time);
        $outbody.=$proj_array[0];
        $booked_time= $proj_array[1];
    }
    if($int==0)$outbody.="<tr><td></td><td></td><td></td><td></td><td></td></tr>";
    $outbody.="</tbody>";
    $output.="<tfoot>
    <tr>
      <td colspan='6' style='padding-right:20px;text-align:right;' >";
    $h1 = floor($sum1/60);
    $m1 = $sum1 - $h1 * 60;
    $booked_time= $sum1-$booked_time;
    $negative=false;
    if($booked_time<0){
        $negative= true;
        $booked_time= abs($booked_time);
    }
    $h2= floor($booked_time/60);
    $m2= $booked_time- $h2*60;
    if($negative){
        $output.="<font color='red'> $month/$year: ".__('still to allocate:')." - $h2 ".__('Hours').", $m2 ".__('minutes')."</font>" ;
    }
    else
    $output.=" $month/$year: ".__('still to allocate:')." $h2 ".__('Hours').", $m2 ".__('minutes') ;
    $output.="
    </td></tr>
    </tfoot>$outbody </table>";
    return $output;
}

/**
 * This fucntion displays the left box - the form for project bookings
 *
 * @param String $date
 * @return string $output
 */
function show_leftbox($date) {
    global $sql_user_group, $sort, $timecard_submode, $name_day, $user_ID;
    $result=db_query("SELECT anfang,ende from ".DB_PREFIX."timecard WHERE users = ".(int)$user_ID." AND datum = '$date'")or db_die();
    while($row2 = db_fetch_row($result)){
        $row2[0] = check_4d($row2[0]);
        $row2[1] = check_4d($row2[1]);
        if($row2[0]&&$row2[1]){
            $dsum =$dsum+(substr($row2[1],0,2) - substr($row2[0],0,2))*60 + substr($row2[1],2,4) - substr($row2[0],2,4);
        }
    }
    $output = "<div>\n";
    //header
    $week_day=get_weekday($date);
    $output.= '<h1>'.__('Assigning projects').__(':').' '.$name_day[$week_day].', '.$date.'</h1>';
    // main content
    $output.='<div>';
    global $date_format_object;
    list($seq, $user_separator, $searchfor) = $date_format_object->get_javascript_convert_value_functions();
    $output.= '
        <form name="pickdate" action="timecard.php?mode=data" method="post">
        <fieldset style="width:500px">
        <legend>'.__('choose day').'</legend>
        '. $name_day[$week_day].', '.$date
        .hidden_fields(array( "submode" => "proj")).'
        <input type="text" id="date"  name="date" '.dojoDatepicker('date', $date).' />
        '.get_buttons(array(array('type' => 'button', 'name' => 'day_back', 'value' =>'&lt;', 'active' => false, 'onclick' => 'lM(dojo.widget.byId(\'picker_date\'), \''.$user_separator.'\', \''.$seq['y'].'\', \''.$seq['m'].'\', \''.$seq['d'].'\'); document.forms[\'pickdate\'].submit();'))).'
        '.get_buttons(array(array('type' => 'button', 'name' => 'day_forward', 'value' => '>', 'active' => false, 'onclick' => 'nM(dojo.widget.byId(\'picker_date\'), \''.$user_separator.'\', \''.$seq['y'].'\', \''.$seq['m'].'\', \''.$seq['d'].'\'); document.forms[\'pickdate\'].submit();'))).'
        '.get_go_button($class='button', $type='button', $name='',$value=__('GO')).'
        </fieldset>
        </form>

<div><br /><b>'.__('Sum working time').':</b> '.$ho.' h '.$mo.' m ';
    $output.=" <a href='timecard.php?mode=view&amp;tree2_mode=open&amp;ID=$row2[0]&amp;PHPSESSID=$PHPSESSID&amp;date=$date&amp;day=$day&amp;submode=proj&amp;month=$month&amp;year=$year'>
    <img src='../img/open.gif' alt=''/></a>
    <a href='timecard.php?mode=view&amp;tree2_mode=close&amp;ID=$row2[0]&amp;PHPSESSID=$PHPSESSID&amp;day=$day&amp;submode=proj&amp;month=$month&amp;year=$year'>
    <img src='../img/close.gif'/></a>";
    $output.="</div><br />";

    $output.='<form action="timecard.php" name="book" method="post"><fieldset><legend>'.__('Sum working time').
    __(':').' '.$name_day[$week_day].', '.$date.'</legend>';
    $hidden_fields = array ( "mode"     => "data",
    "action"   => "proj",
    "submode"     => "proj",
    "datum"    => $date);
    $output.= hidden_fields($hidden_fields);
    $output.="<table summary=\"$tc_sum\" style='width:500px'>
    <thead>
        <tr>
            <th scope=\"col\" title=\"".__('Project')."\">".__('Project')." </th>
            <th scope=\"col\" title=\"".__('Comment')."\">".__('Comment')."</th>
            <th scope=\"col\" title=\"".__('Hours')."\">".__('Hours')." h/m </th>
            <th scope=\"col\" title='".__('Delete')."'>".__('Delete')."</th>
        </tr>
    </thead>";
    $outbody="<tbody>";
    // Projekt lists
    $my_projekts = array();
    $result_projects = db_query("SELECT project_ID FROM ".DB_PREFIX."project_users_rel where user_ID = ".(int)$user_ID);

    while ($row_projekts = db_fetch_row($result_projects)) {
        $my_projekts[] = $row_projekts[0];
    }

    $outbody.=list_projects_tree( 0 , $my_projekts);
    $result4 = db_query("select SUM(h), SUM(m)
                      from ".DB_PREFIX."timeproj
                      where datum = '$date' and
                          users = ".(int)$user_ID) or db_die();
    $row4 = db_fetch_row($result4);
    $gesh =$row4[0];
    $gesm =$row4[1];
    $hg= $gesh + floor($gesm/60);
    $mg = $gesm-  floor($gesm/60)*60;
    $ges_h_m= $gesh*60+$gesm;
    $res_h_m= $dsum-$ges_h_m;
    $negative=false;
    if($res_h_m<0){
        $negative=true;
    }
    $res_h_m=abs($res_h_m);
    $rh = floor($res_h_m/60);
    $rm = $res_h_m - floor($res_h_m/60)*60;
    $outbody.="</tbody>";
    $output.="<tfoot>
    <tr>
        <td></td>";
    if($negative) $output.="    <td>".__('still to allocate:')."<font color='red'> - $rh h  $rm m</font> </td>";
    else $output.="    <td>".__('still to allocate:')." $rh h $rm m </td>";
    $output.=" <td>$hg h $mg m</td>
        <td></td>";
    $output.="
    </tr>
  </tfoot>$outbody</table>";
    $output.= "<input type='hidden' name='date' value='$date'/>\n";




    $output .=
    get_buttons(array(array('type' => 'submit', 'name' => 'save', 'value' =>  __('OK'), 'active' => false)))
    .get_buttons(array(array('type' => 'submit', 'name' => 'delsep', 'value' => __('Delete'), 'active' => false))).
    '
</fieldset></form>
</div></div>
';
    return $output;
}


function list_projects_tree($parent, $my_projekts = array(), $treedata = array()) {

    global $user_ID, $sql_user_group,$par_obj,$user_ID,$arrproj1,$show_tree, $date, $indent, $fld_nr; // deprecated: $h_sum, $m_sum



    if ((int)$parent == 0) {

        $query="SELECT p.ID, p.chef, p.kategorie, p.anfang, p.ende, p.name, p.parent, p.personen, rel.user_ID

                       FROM ".DB_PREFIX."projekte p

                       LEFT JOIN ".DB_PREFIX."project_users_rel rel ON rel.project_ID = p.ID and rel.user_ID = ".(int)$user_ID."

                       WHERE $sql_user_group AND p.anfang <= '$date'

                             AND p.ende >='$date'
                             AND rel.user_Id = ". (int)$user_ID ."
                             ORDER BY p.next_proj, p.name ";



        $result = db_query($query) or db_die();



        if ($result) {

            while ($row = db_fetch_row($result)) {

                $tmpParentID = $row[6];



                if (!isset($treedata[$tmpParentID])) {

                    $treedata[$tmpParentID] = array();

                }



                $treedata[$tmpParentID][] = $row;

            }

        }

    }

    if (isset($treedata[$parent]) && is_array($treedata[$parent])) {

        foreach ($treedata[$parent] as $row2) {



            $is_participant = ($row[8] != '');



            if (in_array($row2[0], $my_projekts)) {

                $is_part = true;

            }

            else {

                $is_part = false;

            }



            // $is_part=user_is_part($row2[0], $user_ID);

            if

            // 1. the user has access to it: be a member or the project leader

            ((($is_part==true) or $row2[1] == $user_ID) and

            // 2. aditional check: status of the project must be active

            $row2[2] == '3') {

                $display=true;

            }

            else $display=false;

            $par_obj[$row2[0]]="";

            $subpro="false";



            //suche nach subprojekt:

            // find out whether there is at at least 1 subproject

            $in=0;

            //only show + - if subrojects exits

            if (isset($treedata[$row2[0]]) ) {



                $subpro="true";

                $outputlat .= "<tr";

                if($i%2==1){

                    $output1 .= " class=\"unev\" ";

                }



                $outputlat .= "> <td scope='row'>";

                // indent

                for ($i=0; $i < $indent; $i++) { $outputlat.= "&nbsp;&nbsp;&nbsp;&nbsp;"; }

                $in=1;

                // show button 'open'

                if (!$arrproj1[$row2[0]]) {

                    $no = "open".$row2[0];

                    $outputlat.="<input type='hidden' name='bID[]' value='$row2[0]' />

             			<a href='timecard.php?submode=proj&date=$date&element2_mode=open&element2_ID=$row2[0]'>

             			".tree_icon("open","name='$no' style='border-style:none;'")."</a>&nbsp; ";

                }

                // show button 'close'

                else {

                    $nc= "close".$row2[0];

                    $outputlat.= "<input type='hidden' name='bID[]' value='$row2[0]' />

              			<a href='timecard.php?submode=proj&date=$date&element2_mode=close&element2_ID=$row2[0]'>

             			".tree_icon("close","name='$no' style='border-style:none;'")."</a>&nbsp; ";

                }

                $outputlat.= html_out($row2[5]);

                $outputlat.="</td>\n";

            }

            else{

                $outputlat .= "<tr";

                if($i%2==1){

                    $output1 .= " class=\"unev\" ";

                }

                $outputlat .= "> <td scope='row'>";

                // indent

                for ($i=0; $i < $indent; $i++) { $outputlat.= "&nbsp;&nbsp;&nbsp;&nbsp;"; }

                $outputlat.= "<img src='".IMG_PATH."/t.gif' width='9' height='9' />&nbsp;".html_out($row2[5]);{



                }

                $outputlat.= "</td>\n";

            }

            // name of the project

            // show input fields for hour and minute

            if ($display==true){



                $id = $row2[0];
 $outputlat.= "<td><textarea name='note[]' style='height:18px'></textarea></td>
        	<td style='white-space:nowrap;text-align:right;'><input type='text' name='h[]' id='h_$id' size='2' maxlength='2' onchange=\"chktime('h_$id','0 - 23!',/[2][0-3]|[0-1]?\d?/)\" />
        	<input type='text' name='m[]' id='m_$id' size='2' maxlength='2' onchange=\"chktime('m_$id','0 - 59!',/[0-5]?\d?/)\" />  </td>\n";
            $outputlat.="<td>";

                $outputlat.= "<input type='hidden' name='module_id[]'   value=''>

    			<input type='hidden' name='module_name[]' value=''>";

                $outputlat.= "<input type='hidden' name='nr[]' value='$id' /></td></tr>\n";

            }

            else{

                $outputlat.= "<td></td><td></td><td></td></tr>\n";

            }

            $outputlat.= show_bookings1($date, $row2[0]);

            $mod_arr= get_additional_mod_bookings($date, $row2[0]);

            $outputlat .= timecard_project_related_moduletimes($user_ID, $date, $row2[0], 'todo', $fld_nr,$indent,$mod_arr['todo']);

            $outputlat .= timecard_project_related_moduletimes($user_ID, $date, $row2[0], 'helpdesk', $fld_nr,$indent,$mod_arr['helpdesk']);



            $fld_nr += 5;



            $indent++;

            if((($arrproj1[$row2[0]]==1))){

                $outputlat.= list_projects_tree($row2[0], $my_projekts, $treedata);

            }

            $indent--;

        }

    }

    // summarize all work time of a day

    /*

    $result4 = db_query("select SUM(h), SUM(m)

    from ".DB_PREFIX."timeproj

    where datum = '$date' and

    users = ".(int)$user_ID) or db_die();

    $row4 = db_fetch_row($result4);

    $h_sum =$row4[0];

    $m_sum =$row4[1];

    */

    if (empty($outputlat))$outputlat="<tr><td></td><td></td><td></td><td></td></tr>";

    return $outputlat;



}



/**
 * Show project related times that do not belong to a module
 */
function show_bookings1($day,$proj) {
    global $user_ID;
    $query = "SELECT p.name, t.h, t.m, t.note, t.ID, t.module
                FROM ".DB_PREFIX."timeproj t, ".DB_PREFIX."projekte p
               WHERE projekt = p.ID and
                     users = ".(int)$user_ID." and
                     projekt= ".(int)$proj." and
                     datum like '$day' and
                     (t.module = '' OR t.module IS NULL)
            ORDER BY name";

    $result3 = db_query($query) or db_die();
    while ($row3 = db_fetch_row($result3)) {
        $output1 .= "<tr class='book2'><td class='book2' style='text-align:right;'></td>";
        $output1 .= "<td class='book2' >".html_out($row3[3])."</td>";
        (!$row3[1]) ? $h=0 : $h=$row3[1];
        (!$row3[2]) ? $m=0 : $m=$row3[2];
        $output1 .= "<td class='book2' style='text-align:right;'>$h h $m m</td>\n <td class='book2' style='text-align:center;'><input type='checkbox' name='del[]' value='$row3[4]'/></td>";
        $output1 .= "</tr>\n";
    }
    return $output1;
}

function show_project_clock($my_projekts = array()){
    global $sql_user_group, $user_kurz,$user_ID, $module;
    $tabs = array();
    $output = '<div id="global-header">';
    $output .= get_tabs_area($tabs);
    $output .= breadcrumb($module, array(array('title' => __('activate project stop watch') )));
    $output .= '</div>';
    $form_fields = array();
    $form_fields[] = array('type' => 'hidden', 'name' => 'action', 'value' => 'clock_in');
    $options = array();
    $set_timezone      = isset($settings['timezone'])    ? $settings['timezone']    : PHPR_TIMEZONE;
    $date = date("Y-m-d", mktime(date("H")+$set_timezone,date("i"),date("s"),date("m"),date("d"),date("Y")));
    $time  = date("H:i", mktime(date("H")+$set_timezone,date("i"),date("s"),date("m"),date("d"),date("Y")));
    $query="select ID,personen,chef,kategorie,anfang,ende,name   from ".DB_PREFIX."projekte where $sql_user_group";
    $result= db_query($query);
    while ($row2 = db_fetch_row($result)) {
        if (in_array($row2[0], $my_projekts)) {
            $is_part = true;
        }
        else {
            $is_part = false;
        }
        //$is_part=user_is_part($row2[0], $user_ID);

        // BIG check: list the project under certain conditions
        if
        // 1. the user has access to it: be a member or the project leader
        ((($is_part==true) or $row2[2] == $user_ID) and
        // 2. aditional check: status of the project must be null or active
        ($row2[3] == '3' or !$row2[3]) and
        // 3. check whether the mentioned day is between begin and end of this record
        ($row2[4] <= $date and $row2[5] >= $date)) {
            $options[] = array('value' => $row2[0], 'text' => $row2[6]);
        }
    }
    $form_fields[] = array('type' => 'select', 'name' => 'projekt', 'label' => __('project choice').__(':'), 'options' => $options);
    $form_fields[] = array('type' => 'text', 'name' => 'note', 'label' => __('Comment').__(':'), 'value' => '');
    $buttons  = get_buttons(array(array('type' => 'submit', 'name' => 'akt', 'value' => __('activate'), 'active' => false)));
    $buttons .= get_buttons(array(array('type' => 'link', 'name' => 'canc', 'href' =>'timecard.php', 'text' => __('cancel'), 'active' => false)));
    $form_fields[] = array('type' => 'parsed_html', 'html' => $buttons);
    $html = '
    <form action="timecard_data.php" name="book" method="post">
    <fieldset>
    <legend>'.__('activate project stop watch').'</legend>'.hidden_fields('').get_form_content($form_fields).'</fieldset></form><br />
    ';

    $output .= '<div id="global-content">';
    $output .= '<br />';
    if(isset($_SESSION['message_stack']["timecard"]))$output .= message_stack_out("timecard");
    $output .= '<a name="content"></a>
    '.$html.'
    </div>';
    echo $output;
}
function show_prbookings($day, $booked_time) {
    global $user_ID;
    $result2 = db_query("select name, h, m, ".DB_PREFIX."timeproj.note, ".DB_PREFIX."timeproj.module, ".DB_PREFIX."timeproj.module_id
                         from ".DB_PREFIX."timeproj, ".DB_PREFIX."projekte
                        where projekt = ".DB_PREFIX."projekte.ID and
                              users = ".(int)$user_ID." and
                              datum like '$day'
                     order by name") or db_die();
    while ($row2 = db_fetch_row($result2)) {
        $mod_name='';
        switch($row2[4]){
            case'todo':
            $mod_name=slookup('todo','remark','ID',$row2[5],'1').": ";
            break;
            case'helpdesk':
            $mod_name=slookup('rts','name','ID',$row2[5],'1').": ";
            break;
            default:
                break;
        }
        $out.= "<tr class='book2'><td class='book2'></td>
     ";
        $out.="<td class='book2'>$row2[0]</td>";
        $out.="<td class='book2'>".$mod_name.$row2[3]."</td><td class='book2'></td>";
        (!$row2[1]) ? $h=0 : $h=$row2[1];
        (!$row2[2]) ? $m=0 : $m=$row2[2];
        $booked_time= $booked_time+(60*$h)+$m;
        $out.= "<td class='book2' style='text-align:right;'>$h</td>\n";
        $out.= "<td class='book2' style='text-align:right;'>$m</td>\n";
        $out.= "</tr>\n";
    }
    return array($out, $booked_time);
}

/**
 * Function gets array of all module_bookings for specific day and projekt
 * @author Nina Schmitt
 * @param ID $day
 * @param ID $proj
 * @return array $mod_arr
 */
function get_additional_mod_bookings($day,$proj){
    global $user_ID;
    $mod_arr=array();
    $query = "SELECT  t.module, t.module_ID
                FROM ".DB_PREFIX."timeproj t, ".DB_PREFIX."projekte p
               WHERE projekt = p.ID and
                     users = ".(int)$user_ID." and
                     projekt= ".(int)$proj." and
                     datum like '$day' and
                     (t.module != '' AND t.module IS NOT NULL)
            ORDER BY name";

    $result3 = db_query($query) or db_die();
    while ($row3 = db_fetch_row($result3)) {
        $mod_arr[$row3[0]][]=$row3[1];
    }
    return $mod_arr;
}

?>
