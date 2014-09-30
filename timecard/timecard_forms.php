<?php

// timecard_forms.php - PHProjekt Version 5.2
// copyright  ©  2004-2005 Nina Schmitt
// www.phprojekt.com
// Author: Nina Schmitt, $Author: alexander $
// $Id: timecard_forms.php,v 1.41.2.1 2007/01/22 12:11:56 alexander Exp $

// check whether the lib has been included - authentication!
if (!defined('lib_included')) die('Please use index.php!');

$css_void_background_image = true;

// check role
if (check_role("timecard") < 1) die("You are not allowed to do this!");

$output.= show_leftbox($date);
$output.= show_rightbox($month,$year);

/**
 * This fucntion displays the left box - the form for worktime bookings
 *
 * @param String $date
 * @return string $output
 */
function show_leftbox($date){
	global $user_ID, $name_day, $timestart, $timestop, $submode;
	if(!$timestop)$timestop="2000";
	if(!$timestart)$timestart="0800";
	$week_day = get_weekday($date);
	//start output leftbox
	$output = "<div id='left_container'>\n";
	//header
    $output .= "<h1>".__('insert additional working time').__(':')." ".$name_day[$week_day].", ".$date."</h1>\n";
    //main content
	$output .= '
    <form name="pickdate" action="timecard.php?mode=data" method="post">
    <fieldset>
    <legend>'.__('choose day').'</legend>
    '.hidden_fields(array('submode' => $submode)).'
    <input type="text" id="date" name="date" '.dojoDatepicker('date', $date).' />
    '.get_buttons(array(array('type' => 'button', 'name' => 'day_back', 'value' =>'&lt;', 'active' => false, 'onclick' => 'lM(dojo.widget.byId(\'picker_date\')); document.forms[\'pickdate\'].submit();'))).'
    '.get_buttons(array(array('type' => 'button', 'name' => 'day_forward', 'value' => '>', 'active' => false, 'onclick' => 'nM(dojo.widget.byId(\'picker_date\')); document.forms[\'pickdate\'].submit();'))).'
    '.get_go_button($class='button', $type='button', $name='',$value=__('GO')).'
    </fieldset>
    </form>
    <br />
    <form action="timecard.php?mode=data" name="nachtragen1" method="post">
    <fieldset>
    <legend>'.__('Assign work time').__(':')." ".$name_day[$week_day].", ".$date.'</legend>
    ';
    $hidden_fields = array ( "submode"     => "days",
                             "date"     => $date,
                             "action"   => "add",
                             "ID"       => '');
	$anf=$timestart;
	$end=$timestop;
	$netto= ((substr($end,0,2) - substr($anf,0,2))*60 + substr($end,2,4) - substr($anf,2,4));
	$nettoh = floor($netto/60);
	$nettom = $netto - $nettoh * 60;
	$output.= hidden_fields($hidden_fields);
	$output.= '<label for="timestart">'.__('Begin').':</label> <input type="text" name="timestart" id="timestart" value="'.$timestart.'" maxlength="4" 
onblur="getNetto(document.nachtragen1.timestart,document.nachtragen1.timestop)"/> <label for="timestop">'.__('End').':</label> <input type="text" name="timestop" id="timestop" value="'.$timestop.'" maxlength="4" onblur="getNetto(document.nachtragen1.timestart,document.nachtragen1.timestop,document.nachtragen1.nettom,document.nachtragen1.nettoh)"/>
';	
	$output.=get_go_button($class='button', $type='button', $name='',$value=__('GO')).'</fieldset></form><br />';
	$output.='<form action="timecard.php?mode=data" name="nachtragen" method="post"><fieldset><legend>'.__('My working time overview').__(':')." ".$name_day[$week_day].", ".$date.'</legend>';
   $hidden_fields = array ( "submode"     => "days",
                            "date"     => $date,
                            "action"   => "worktime_insert_after");
    $output.= hidden_fields($hidden_fields);
	$output.= "<table>
	<thead>
    	<tr>
    	    <th scope=\"col\" title=\"".__('Start')."\">".__('Start')."</th>
			<th scope=\"col\" title=\"".__('End')."\">".__('End')."</th>
 			<th scope=\"col\" title=\"".__('Hours')."\">".__('Hours')."</th>
 			<th scope=\"col\" title=\"".__('save+close')."'\">".__('Delete')."</th>
		</tr>
	</thead>";
	$outbody="<tbody>";
	$result=db_query("SELECT anfang, ende, ID
					  FROM ".DB_PREFIX."timecard
					  WHERE users = ".(int)$user_ID." AND datum = '$date'")
	or db_die();
	$i=0;
	$gesh = 0;
	$gesm = 0;
	$nachtragen = false;
	while($row = db_fetch_row($result)){
		$outbody.= "<tr";
		if($i%2==1){
			$outbody.= " class=\"even\" ";
		}
        else{
			$outbody.= " class=\"odd\" ";
        }
		$row[0] = check_4d($row[0]);
		$row[1] = check_4d($row[1]);
		//reset h and m for every loop
		$h=0;
		$m=0;
		if($row[0]&&$row[1]){
			//Nettozeit der einzelnen Buchung
			$bsum =(substr($row[1],0,2) - substr($row[0],0,2))*60 + substr($row[1],2,4) - substr($row[0],2,4);
			$h= floor($bsum/60);
			$m = $bsum - $h * 60;
		}
		$outbody.="> <td scope='row'>$row[0]</td>";
		if(!$row[1]){
			$nachtragen =true;
			$outbody.="<td><input type='text' name='ende[$row[2]]' maxlength='4' value=''".'onblur="getNetto(document.nachtragen1.timestart,document.nachtragen1.timestop,document.nachtragen1.nettom,document.nachtragen1.nettoh)"'." /></td>";
			$outbody.="<td></td>";

		}
		else $outbody.="<td>$row[1]</td><td>$h h $m m</td>";

		$outbody.="<td><input type='checkbox' name='del[$i]' value='$row[2]'/></td>
		</tr>";
		// Store time for total worktime
		$gesh= $gesh+$h;
		$gesm= $gesm+ $m;
		$i++;
	}
	// in case no entry is displayed display a dummy line for layout reasons
	if($i==0)$outbody.="<tr><td></td><td></td><td></td><td></td></tr>";
	// calculate total worktime(per day)
	$hg= $gesh + floor($gesm/60);
	$mg = $gesm-  floor($gesm/60)*60;
	$outbody.="</tbody>";
	$output.="<tfoot><tr><td></td>";
	if($nachtragen==true) $output.="<td>". get_buttons(array(array('type' => 'submit', 'name' => 'fills2', 'value' => __('OK'), 'active' => false)))."</td>";
	else $output.=" <td></td>";
	$output.="<td>$hg h $mg m</td>
 	<td>". get_buttons(array(array('type' => 'submit', 'name' => 'deli', 'value' => __('Delete'), 'active' => false)))."</td></tr>
    </tfoot>$outbody</table>";
	$output .= '</fieldset></form></div>';
	return $output;
}

function show_rightbox($month,$year){
	global $name_day, $name_month, $user_ID,$submode;
	$output = "<div id='right_container'>";
	//header
    $output .= '<h1>'.__('My working time overview').' '.$name_month[intval($month)]." ".$year.'</h1>';
	//main content
    $hidden_fields = array ( "mode" => "view",
                             "submode" => $submode);
	$output.= '
    <form action="timecard.php" name="date" method="post">
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
	$output .='
    </select> '.get_go_button($class='button', $type='button', $name='',$value=__('GO')).'
    </fieldset></form><br />';

	//table with detailed worktime entries
	$output.="<table>
    <thead>
        <tr>
            <th scope=\"col\" title=\"".__('Weekday')."\">".__('Weekday')."</th>
            <th scope=\"col\" title=\"".__('Date')."\">".__('Date')."</th>
            <th scope=\"col\" title=\"".__('Start')."\">".__('Start')."</th>
            <th scope=\"col\" title=\"".__('End')."\">".__('End')."</th>
            <th scope=\"col\" colspan='2' title=\"".__('Hours')."\">".__('Hours')." h/m </th>
        </tr>
    </thead>";
	$outbody="<tbody>";
	//get start end end time of each workday
	$result = db_query("SELECT MAX(ID), datum, MIN(anfang) as anf, MAX(ende) as max
                        FROM ".DB_PREFIX."timecard
                        WHERE users = ".(int)$user_ID." AND datum like '$year-$month-%' 
                        GROUP by datum ORDER BY datum DESC")
	or db_die();
	$int=0;
	$sum1 = 0;
	while($row = db_fetch_row($result)){
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
			$outbody.= " class=\"odd\" ";
		}
        else{
			$outbody.= " class=\"even\" ";
        }
		$href = "timecard.php?submode=days&amp;year=$year&amp;date=$row[1]&amp;month=$month";
		$outbody.="> <td scope='row'><a href='$href'>$name_day[$week_day]</a></td>
        <td>$row[1]</td>
        <td>".check_4d($row[2])."</td>
        <td>".check_4d($row[3])."</td>
        <td>$h1</td>
        <td>$m1</td>
        </tr>";
		$outbody.= show_bookings($row[1]);
	}
	if($int==0)$outbody.="<tr><td></td><td></td><td></td><td></td><td></td></tr>";
	$outbody.="</tbody>";
	$output.="<tfoot>
    <tr>
      <td colspan='6'>";
	$h1 = floor($sum1/60);
	$m1 = $sum1 - $h1 * 60;
	$output.=__('Sum for')." $month/$year: $h1 ".__('Hours').", $m1 ".__('minutes');
	$output.="
    </td></tr>
    </tfoot>$outbody </table>";
	$output.="</div>";
	return $output;
}
function show_bookings($day) {
	global $user_ID;
	$result21 = db_query("select anfang, ende
                         from ".DB_PREFIX."timecard
                        where users = ".(int)$user_ID." and
                              datum like '$day'
                     order by anfang ASC") or db_die();
	while ($row21 = db_fetch_row($result21)) {
		$h="";
		$m="";
		if ($row21[1]) {
			$row21[0] = check_4d($row21[0]);
			$row21[1] = check_4d($row21[1]);
			$bsum =(substr($row21[1],0,2) - substr($row21[0],0,2))*60 + substr($row21[1],2,4) - substr($row21[0],2,4);
			$h= floor($bsum/60);
			$m = $bsum - $h*60;
		}
		$out.= "<tr><td></td><td></td>";
		$out.= "<td>".check_4d($row21[0])."</td>";
		$out.= "<td>".check_4d($row21[1])."</td>";
		(!$h) ? $h=0 : $h = $h;
		(!$m) ? $m=0 : $m = $m;
		$out.= "<td>$h </td>\n";
		$out.= "<td>$m </td>\n";
		$out.= "</tr>\n";
	}
	return $out;
}
?>

