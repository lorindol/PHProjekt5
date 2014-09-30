<?php
/**
 * project form view
 *
 * @package    projects
 * @subpackage main
 * @author     Albrecht Guenther, $Author: albrecht $
 * @licence    GPL, see www.gnu.org/copyleft/gpl.html
 * @copyright  2000-2006 Mayflower GmbH www.mayflower.de
 * @version    $Id: projects_forms.php,v 1.152 2008-03-04 10:52:00 albrecht Exp $
 */

// check whether the lib has been included - authentication!
if (!defined("lib_included")) die("Please use index.php!");

// check role
if (check_role("projects") < 1) die("You are not allowed to do this!");

include_once(LIB_PATH.'/selector/selector.inc.php');
include_once(LIB_PATH.'/access_form.inc.php');
if ($justform == 2) {
    $onload[] = 'window.opener.location.reload();';
    $onload[] = 'window.close();';
}
else if ($justform > 0) {
    $justform++;
}

if (isset($personen) && ($ID!=0)) {
    update_project_personen_table($ID, $personen,'user',xss_array($_POST));
}

if (isset($contact_personen) && ($ID!=0)) {
    update_project_personen_table($ID, $contact_personen,'contact',xss_array($_POST));
}

// update project? -> fetch values form record
if ($action <> "new" and $ID > 0) {
    $result = db_query("SELECT ID, name, anfang, ende, chef, contact, stundensatz, budget, wichtung,
                               ziel, note, depend_mode, depend_proj, next_mode, next_proj, probability,
                               ende_real, kategorie, status, statuseintrag, parent, acc,
                               acc_write, von, gruppe, template, costcentre_id, contractor_id 
                          FROM ".DB_PREFIX."projekte
                         WHERE (acc LIKE 'system'
                                OR ((von = ".(int)$user_ID." OR acc LIKE 'group' OR acc LIKE '%\"$user_kurz\"%')
                                    ".group_string()."))
                           AND ID = ".(int)$ID."
                           AND is_deleted is NULL") or db_die();
    $row = db_fetch_row($result);

    // check access
    // genreal acces - either the user has direct access to it or the user has chief status
    if (!$row[0] and $user_type!=3) die("You are not privileged to do this!");

    if (($row[23] <> $user_ID and $row[22] <> 'w') or check_role("projects") < 2) $read_o = 1;
    if ($row[23] <> $user_ID and PHPR_ALTER_ACC!=1) $read_acc = 1;
    else $read_acc = 0;

    // get values
    $project_name   = html_out($row[1]);
    $anfang         = $row[2];
    $ende           = $row[3];
    $chef           = $row[4];
    $contact        = $row[5];
    $stundensatz    = $row[6];
    $budget         = $row[7];
    $wichtung       = $row[8];
    $ziel           = $row[9];
    $note           = $row[10];
    $depend_mode    = $row[11];
    $depend_proj    = $row[12];
    $next_mode      = $row[13];
    $next_proj      = $row[14];
    $probability    = $row[15];
    $ende_real      = $row[16];
    $category       = $row[17];
    $status         = $row[18];
    $statuseintrag  = $row[19];
    if (!isset($parent)) $parent   = $row[20];
    $acc            = $row[21];
    $acc_write      = $row[22];
    change_group($row[24]);
    $template       = $row[25];
    if (!isset($costcentre_id)) $costcentre_id  = (int) $row[26];
    if (!isset($contractor_id)) $contractor_id  = (int) $row[27];
}
// set variables for a new project:
else {

    set_new_project();

}
//unset ID when copying project
$ID=prepare_ID_for_copy($ID,$copyform);
// tabs
$buttons = array();
if ($justform == 2) $justform = 1;

$hidden = array_merge(array('ID'=>$ID, 'type'=>$type, 'mode'=>'data', 'gruppe'=>'user_group', 'justform'=>$justform, 'project_name'=>$project_name), $view_param);

if (SID) $hidden[session_name()] = session_id();

// form start
$date_format_text = __('Date format').' = '.$date_format_object->get_user_format();
$buttons[] = array( 'type'     => 'form_start',
'hidden'   => $hidden,
'enctype' => "multipart/form-data",
'name'     => 'frm',
'onsubmit' => "return chkForm('frm','name','".__('Please insert a name')."') &amp;&amp; ".
"checkUserDateFormat('anfang','".__('Begin').':\n'.$date_format_text."') &amp;&amp; ".
"checkUserDateFormat('ende','".__('End').':\n'.$date_format_text."') &amp;&amp;".
"checkDates('anfang','ende','".__('Begin > End')."!') &amp;&amp; ".
"chkFloat('frm','budget','".__('Calculated budget has a wrong format')."') &amp;&amp; ".
"chkNumbers('frm','stundensatz','".__('Hourly rate has a wrong format')."') &amp;&amp; ".
"checkProjectFormSum('frm','costunit_fraction[]', '".__('Costunit fraction sum must be 100% or 0%')."') &amp;&amp; ".
"!checkDuplicate('frm', 'costunit_id[]', '".__('Duplicates in costunit')."');");

// breadcrumb-stuff
// show a project
if (!empty($ID)) {
    $title = htmlentities(slookup('projekte', 'name', 'ID', (int) $ID,'1'));
}
else {
    $title = __('New');
}

$output .= breadcrumb($module, array(array('title'=>$title)));unset($title);
$output .= '</div>';
$output .= get_buttons($buttons);
$output .= $content_div;

unset($title);

// check if project is deleteable
$deleteable = false; // default deleteable status

if (!$read_o and check_role("projects") > 1 and $ID > '0') {
    $result2 = db_query("SELECT ID
                           FROM ".DB_PREFIX."projekte
                          WHERE parent = ".(int)$ID."
                            AND is_deleted is NULL") or db_die();
    $row2 = db_fetch_row($result2);
    if ($row2[0] == '' and $row[23] == $user_ID) {
        $deleteable = true;
    }
}

$buttons = get_default_buttons($read_o, $ID, $justform, $module, $deleteable, $sid);

/*************************************
anlegen hidden value
************************************
if (!$read_o && $user_role > 1 && $ID == 0) {
$buttons[] = array('type' => 'hidden', 'name' => 'anlegen', 'value' => 'neu_anlegen');
}

************************************
aendern hidden value
*************************************/
if (!$read_o && $user_role > 1 && $ID > 0) {
    $buttons[] = array('type' => 'hidden', 'name' => 'aendern', 'value' => 'aendern');
}

if (!$read_o && $ID > 0 && $justform < 1) {
    $buttons[] = array('type' => 'link', 'href' => 'phprojekt2gp.php?project_ID='.$ID, 'text' => '-> GP', 'active' => false,  'target' => '_blank');
    $buttons[] = array('type' => 'link', 'href' => 'projects.php?mode=uploadforms&amp;project_ID='.$ID, 'text' => 'GP ->', 'active' => false,  'target' => '_blank');
}

if ($read_o and check_role("projects") > 1 and $user_ID == $chef) {
    // modify status
    $buttons[] = array('type' => 'submit', 'name' => 'modify_status_b', 'value' => __('Modify status'), 'active' => false);
    // hidden
    $buttons[] = array('type' => 'hidden', 'name' => 'modify_status', 'value' => 'modify_status');
}

$output .= get_buttons_area($buttons);


$out_array=array();
/*************************************
Header Box 1 (DB Manager data)
*************************************/
$form_data = array();

$form_data = build_form($fields);

$out_array= array_merge($out_array,$form_data);

$result = db_query ("SELECT id, name FROM ".DB_PREFIX."controlling_costunits ORDER BY name") or db_die();
$costunits = array();
while ($row = db_fetch_row($result))
{
	$costunits[] = array("id" => $row[0],
						 "name" => $row[1]);
}

$result = db_query ("SELECT projekte_ID, costunit_id, fraction
                       FROM ".DB_PREFIX."projekte_costunit
                   ORDER BY costunit_id") or db_die();
while ($row = db_fetch_row($result))
{
	if ($row[1] > 0) {
		$saved_costunits[$row[0]][] = array("pid" => $row[0],
							 				"costunit_id" => $row[1],
							 				"fraction" => $row[2]);
	}
}

// OVERRULE:
$overrule = get_project_overrules($ID, $costcentre_id, $saved_costunits, $costunits);

if (is_project_overruled($overrule)) {
	$controlling.= "<span class='label_block'>".__("Overruled by project")."&nbsp;&nbsp;
					<b>".$overrule['ccname']."</b> (".__('Costcentre').") /
					<b>".$overrule['cuname']."</b> (".__('Costunit').")</span>
					<br style='clear' /><br style='clear' />";
}

$controlling = "<br /><label for='parent' class='label_block'>".__('Costcentre')."</label>\n";
$controlling .= "<select name='costcentre_id'".read_o($read_o).">
                    <option value='NULL'".read_o($read_o).">----</option>";
$result = db_query ("SELECT id, name FROM ".DB_PREFIX."controlling_costcentres ORDER BY name")
                or db_die();

while ($row = db_fetch_row($result))
{
  if (empty($costcentre_id) && !empty($parent)) {
    $selected = ($row[0]==$overrule['costcentre'])?'selected':'';
  } else if (!empty($costcentre_id) && $row[0] == $costcentre_id) {
    $selected = 'selected';
  } else if ($row[0] == 0) {
    $selected = 'selected';
  } else {
    $selected = '';
  }

	$controlling.= '<option value="'.$row[0].'" '.$selected.read_o($read_o).'>'.$row[1].' ('.$row[0].')</option>';
}

$controlling.= "</select>";

$controlling.= "<br style='clear'/>";

$i = 0;
if (!isset($costunit_id)) {
    $selection = (count($overrule['costunit']) == 0) ? $saved_costunits[$ID] : $overrule['costunit'];
} else {
    $selection = array();
    foreach($costunit_id as $k => $v) {
        $selection[] = array('costunit_id' => $v,
                             'fraction'    => (isset($costunit_fraction[$k])) ? $costunit_fraction[$k] : 0);
    }
}
foreach((array) $selection as $saved)
{
	$controlling .= "<span";

	if ($i == 0) {
		 $controlling .= " id='cloneable'";
	}

	$controlling .= ">\n<label for='parent' class='label_block'>".__('Costunit')."</label>\n";
	$controlling .= "<select name='costunit_id[]'".read_o($read_o).">\n
	                    <option value='NULL'".read_o($read_o).">----</option>\n";

	foreach ($costunits as $cr)
	{
		$selected = ($cr['id']==$saved['costunit_id'])?'selected':'';
		$controlling.= '<option value="'.$cr['id'].'" '.$selected.read_o($read_o).'>
			'.$cr['name'].' ('.$cr['id'].')</option>';
	}

	$controlling.= "</select>";
	$controlling.= '<input type="text" name="costunit_fraction[]" value="'.$saved['fraction'].'" size="3"/> %;';
	$controlling.= '<input type="button"
		                onClick="javascript:cloneItem(\'frm\', \'cloneable\', false);"
		                  value="'.__("add").'" />';

	$controlling.= '<input type="button" ';
	if ($i++ == 0) {
		$controlling.= 'disabled="disabled" ';
	}
	$controlling .= 'onClick="javascript:dropItem(this.parentNode);" value="'.__("delete").'" />';
	$controlling .= "<br style='clear'/></span>";
}

if ($i == 0) {
	$controlling .= "<span id='cloneable'>\n
	<label for='parent' class='label_block'>".__('Costunit')."</label>\n";
	$controlling .= "<select name='costunit_id[]'".read_o($read_o).">\n
	                    <option value='NULL'".read_o($read_o).">----</option>\n";

	foreach ($costunits as $cr)
	{
		$controlling.= '<option value="'.$cr['id'].'" '.read_o($read_o).'>
			'.$cr['name'].' ('.$cr['id'].')</option>';
	}

	$controlling.= "</select>";
	$controlling.= '<input type="text" name="costunit_fraction[]" value="100" size="3"/> %;';
	$controlling.= '<input type="button"
		                onClick="javascript:cloneItem(\'frm\', \'cloneable\', false);"
		                  value="'.__("add").'" />';

	$controlling.= '<input type="button" disabled="disabled" onClick="javascript:dropItem(this.parentNode);" value="'.__("delete").'" />';

	$controlling .= "<br style='clear'/></span>";
}

$controlling .= "<label for='parent' class='label_block'>".__('Contractor')."</label>\n";
$controlling .= "<select name='contractor_id'".read_o($read_o).">
                    <option value='NULL'".read_o($read_o).">----</option>";

$result = db_query ("SELECT id, vorname, nachname FROM ".DB_PREFIX."users") or db_die();

while ($row = db_fetch_row($result))
{
	$selected = ($row[0]==$contractor_id)?'selected':'';
	$controlling.= '<option value="'.$row[0].'" '.$selected.read_o($read_o).'>'.$row[2].', '.$row[1].'</option>';
}

$controlling.= "</select><br style='clear'/><br />";



$tree_elements = get_elements_of_tree("projekte",
                        "name, costcentre_id",
                        "",
                        "acc",
                        "ORDER BY name",
                        "",
                        "parent");

if (!isset($ID)) {
    $ID = 0;
}
$cids = (array) get_parent_ids($tree_elements, $ID);
$cids[] = $ID;
$result = db_query("SELECT p.name, p.budget, p.stundensatz
                      FROM ".DB_PREFIX."projekte p
                     WHERE (acc LIKE 'system' OR ((von = ".(int)$user_ID." OR acc LIKE 'group' OR acc LIKE '%\"$user_kurz\"%')".group_string($module)."))
                       AND p.ID IN (".implode(',', $cids).")
                  GROUP BY p.ID") or db_die();

$debitvalue = "<br style='clear'/>";
while ($row = db_fetch_row($result)) {
    $query_time = "SELECT SUM(h), SUM(m)
                     FROM ".DB_PREFIX."timeproj
                    WHERE projekt= '$ID'";
    $result_time = db_query($query_time) or db_die();
    $row_time = db_fetch_row($result_time);
    $hours   = (int) ($row_time[0] + floor($row_time[1]/60)) ;
    $minutes = (int) $row_time[1]%60;
    $booked=floor(($row_time[0]*60 + $row_time[1])/3.6);
    $booked = $booked/100;

    $debitvalue.= "<label for='parent' class='label_block'>".$row[0]."</label>\n<br />";
    $debitvalue.= "<label for='sollwert' class='label_block'>".__('Costs planned')."</label>$row[1] ".__('days')."\n<br />";
    $debitvalue.= "<label for='sollwert2' class='label_block'>".__('Costs so far')."</label>$booked ".__('days');
    $debitvalue.= "(<b>".$hours."h ".$minutes."m)<br style='clear'/></b>\n";
    $debitvalue.= "<br /><br />";
}

// Stoplight
if ($ID > 0 && PHPR_PROJECT_PROGRESS == 1) {
    $stoplight = get_project_stoplight($ID,'time_ratio');
    $stoplight_box = '<fieldset>';
    $stoplight_box .= '<legend>'.__('Stoplights').'</legend>';
    $stoplight_box .= '<label for="stoplights" class="label_block">'.$stoplight['name'].':</label>&nbsp;';
    $stoplight_box .= $stoplight['value']."&nbsp;<img src='../img/".$stoplight['color'].".gif' title='".$stoplight['name'].": ".$stoplight['formula']."' border='0' width='7' heigh='7' />"."<br />";

    $stoplight = get_project_stoplight($ID,'progress_budget');
    $stoplight_box .= '<label for="stoplights" class="label_block">'.$stoplight['name'].':</label>&nbsp;';
    $stoplight_box .= $stoplight['value']."&nbsp;<img src='../img/".$stoplight['color'].".gif' title='".$stoplight['name'].": ".$stoplight['formula']."' border='0' width='7' heigh='7' />"."&nbsp;&nbsp;&nbsp;";

    $stoplight_box .= "<br /><br />";
    $stoplight_box .= '</fieldset>';
    
    $debitvalue .= $stoplight_box;
}
/* End stoplight box */

//$out_array[] = array(__('Debit value'), $debitvalue);

$out_array[] = array(__('Finance'), $controlling.$debitvalue);

/*************************************
Header Box 2 (Categorization)
*************************************/

$categorization = "<br style='clear'/>";
$categorization .= '
    <label for="parent" class="label_block">'.__('Sub-Project of').':</label>'.
selector_create_select_projects('parent', $parent, 'action_form_to_parent_selector', $ID, 'class="halfsize" id="parent"'.read_o($read_o));

// sort subprojects
if ($ID > 0) {
    $result2 = db_query("SELECT COUNT(ID)
                           FROM ".DB_PREFIX."projekte
                          WHERE parent = ".(int)$ID."
                            AND is_deleted is NULL") or db_die();
    $row2 = db_fetch_row($result2);
    if ($row2[0] > 1) {
        $categorization .= "&nbsp; &nbsp; (<a target='_blank' href='./projects_sortbox.php?action=sort&amp;extra_value=".$ID."'>".__('Sort sub projects')."</a>)\n";
    }
}
$categorization .= "<br />\n";

// status - progres
$read_o_status = $user_ID == $chef ? 0 : 1;
$categorization .= "<label for='parent' class='label_block'>".__('Status')." [%]:</label>\n";
$categorization .= "<input name='status' value='$status' type='text' class='halfsize' ".read_o($read_o_status, 'readonly')."/>\n";
$categorization .= '<br style="clear:both" /><br />'."\n";

// sort subprojects - first look which records in the same branch exist
if ($ID > 0) {
    $categorization_elements = '';
    $result2 = db_query("SELECT ID, name
                           FROM ".DB_PREFIX."projekte
                          WHERE (acc LIKE 'system'
                                OR ((von = ".(int)$user_ID." OR acc LIKE 'group' OR acc LIKE '%\"$user_kurz\"%')
                                    ".group_string('projects').")) 
                            AND parent = ".(int)$parent." 
                            AND ID <> ".(int)$ID." 
                            AND is_deleted is NULL 
                          ORDER BY name") or db_die();
    $row2 = db_fetch_row($result2);
    
    
    if ($row2[1] <> '') {
        // dependency
        // check where there are any other projects on this level
        $categorization .= "<label for='depend_mode' class='label_block'>".__('Dependency').":</label>\n";
        $categorization .= "<select class='halfsize' name='depend_mode'".read_o($read_o)."><option value='0'>\n";
        foreach ($dependencies as $dep1 => $dep2) {
            $categorization .= "<option value='$dep1'";
            if ($dep1 == $depend_mode) $categorization .= ' selected="selected"';
            $categorization .= ">$dep2:</option>\n";
        }
        $categorization .= "</select>\n";
        // fetch all of these neighbours and display them
        $categorization .= "<label for='depend_mode' class='label_block'>".__('Dependend projects').":</label>\n";
        $categorization .= "<select class='halfsize' name='depend_proj'".read_o($read_o)."><option value='0'>\n";
        
        // first row
        $categorization .= "<option value='$row2[0]'";
        if ($row2[0] == $depend_proj) $categorization .= ' selected="selected"';
        $categorization .= ">$row2[1]</option>\n";

        while ($row2 = db_fetch_row($result2)) {
            $categorization .= "<option value='$row2[0]'";
            if ($row2[0] == $depend_proj) $categorization .= ' selected="selected"';
            $categorization .= ">$row2[1]</option>\n";
        }
        $categorization .= "</select>\n";
    }
    // otherwise set the dependency to 0 to avoid that this project has an 'old' dependency
    else {
        $output.= "<input type='hidden' name='dependency' value='0' />\n";
    }
    
    // project template
    $categorization .= "<br /><label for='template'  class='label_block'>".__('Is a template').":</label>\n";
    $categorization .= "<input type='checkbox' class='halfsize' name='template' value='yes' ".read_o($read_o)." ";
    if ($template == 'yes') {
        $categorization .= " checked";
    }
    $categorization .= ">\n";
}
$categorization.= "<br /><br />";
$out_array[]=array('Categorization', $categorization);

/**************************************************
Header Box 3 (Project related times)
**************************************************/
// project-related times
include_once(LIB_PATH."/timeproj.inc.php");
$project_specific_times = timeproj_get_list_box($ID, '', $user_ID);
$out_array[]=array('project related times',$project_specific_times);
/**************************************************
Header Box 4 (Assignment of Participants)
**************************************************/
$box_right_data = array();
$box_right_data['type']         = 'anker';
$box_right_data['anker_target'] = 'oben';

// access
// select participants

// values of the access
if (!isset($persons)) {
    if (!isset($_POST['persons'])) $str_persons = $acc;
    else $str_persons = xss($_POST['persons']);
}
else $str_persons = serialize($persons);

if (!isset($acc_write)) {
    if (isset($_POST['acc_write'])) {
        $acc_write = xss($_POST['acc_write']);
    }
}

// acc_read, exclude the user itself, acc_write, no parent possible, write access=yes
$out_array[]=array(__('Release') , access_form($str_persons, 1, $acc_write, 0, 1,'acc',$read_acc));

/**************************************************
(Participants)
**************************************************/

// values of the participants
if (!isset($personen)) {
    if (!isset($_POST['personen'])) {
        if($ID>0){
            $tmp_result = db_query("SELECT user_ID FROM ".DB_PREFIX."project_users_rel
                                     WHERE project_ID = ".(int)$ID." 
                                       AND user_ID <> 0
                                       AND is_deleted is NULL") or db_die();
            while ($tmp_row = db_fetch_row($tmp_result)) {
                $personen[] = $tmp_row[0];
            }
        }
        else $personen = array();
    }
    else $personen = xss_array($_POST['personen']);
}

if (is_array($personen)) $acc_read = $personen;
else                     $acc_read = unserialize($personen);

$assignment = selector_create_select_users('personen[]', $acc_read, 'action_form_to_participants_selector', '0', read_o($read_o));

// table of selected users
$user_table = '
    <table class="relations">
        <caption>'.__('Participants').'</caption>
        <thead>
            <tr>
                <td title="'.__('Family Name').'">'.__('Family Name').'</td>
                <td title="'.__('First Name').'">'.__('First Name').'</td>
                <td title="'.__('email').'">'.__('email').'</td>
                <td title="'.__('Expenditure planned').'">'.__('Expenditure planned').'</td>
                <td title="'.__('Role').'">'.__('Role').'</td>
                <td title="'.__('or new Role').'">'.__('or new Role').'</td>
            </tr>
        </thead>
        <tbody>
    ';

if (isset($ID)&&($ID!=0)) {
    $query = "SELECT user_ID, role, user_unit FROM ".DB_PREFIX."project_users_rel
               WHERE project_ID = ".(int)$ID;
    $result = db_query($query);
    while ($row = db_fetch_row($result)) {
        $user_query = "SELECT ID, vorname, nachname, email
                         FROM ".DB_PREFIX."users
                        WHERE ID = ".(int)$row[0]."
                          AND is_deleted is NULL";
        $user_result = db_query($user_query);
        $user_row = db_fetch_row($user_result);
        $user_table .= "
            <tr>
                <td>".$user_row[2]."</td>
                <td>".$user_row[1]."</td>
                <td>".$user_row[3]."</td>
                <td><input name='u_".$row[0]."_unit' type='text' size='8' maxlength='200' value='".$row[2]."'></td>
                <td>".make_select_roles($row[0],$row[1],'users')."</td>
                <td><input name='u_".$row[0]."_text_role' type='text' size='8' maxlength='200'></td>
            </tr>
        ";
    }
} else if (is_array($personen)&&(!empty($personen))) {
    foreach($personen as $personen_id) {
        $user_query = "SELECT ID, vorname, nachname, email
                         FROM ".DB_PREFIX."users
                        WHERE ID = ".(int)$personen_id."
                          AND is_deleted is NULL";
        $user_result = db_query($user_query);
        $user_row = db_fetch_row($user_result);
        $user_role_str = 'u_'.$personen_id.'_role';
        $user_role = (isset($_POST[$user_role_str])) ? xss($_POST[$user_role_str]) : '';
        $user_table .= "
            <tr>
                <td>".$user_row[2]."</td>
                <td>".$user_row[1]."</td>
                <td>".$user_row[3]."</td>
                <td><input name='u_".$personen_id."_unit' type='text' size='8' maxlength='200'></td>
                <td>".make_select_roles($personen_id,$user_role,'users')."</td>
                <td><input name='u_".$personen_id."_text_role' type='text' size='8' maxlength='200'></td>
            </tr>
        ";
    }
}
$user_table .= '</tbody></table>';

if (isset($ID)&&($ID!=0)) {
    $userlist = '';
    if (is_array($personen)&&(!empty($personen))) {
        foreach ($personen as $tmp => $uID) {
            if (empty($userlist)) {
                $userlist .= $uID;
            } else {
                $userlist .= '_'.$uID;
            }
        }
    }
    $workload = '<center><a target="_blank" href="../timescale/timescale_workload.php?anfang='.$anfang.'&amp;ende='.$ende.'&amp;userlist='.$userlist.'">'.__('Check availability').'</a></center>';
} else {
    $workload = '';
}

$out_array[] =array(__('Participants'), '
        <div style="float:left;width:10%;padding:23px;">'.$assignment.'</div>
        <div style="float:right;width:70%;padding:23px;">'.$user_table.'</div>
        <br style="clear:both"/>'.$workload);

/**************************************************
(Contacts)
**************************************************/

// values of the contacts
if (!isset($contact_personen)) {
    if (!isset($_POST['contact_personen'])) {
        $tmp_result = db_query("SELECT contact_ID FROM ".DB_PREFIX."project_contacts_rel
                                 WHERE project_ID = ".(int)$ID."
                                   AND is_deleted is NULL") or db_die();
        while ($tmp_row = db_fetch_row($tmp_result)) {
            $contact_personen[] = $tmp_row[0];
        }
    } else $contact_personen = xss_array($_POST['contact_personen']);
}

$contact_assignment = selector_create_select_contacts('contact_personen[]', $contact_personen,'action_form_to_contact_selector', '0', read_o($read_o), '7', '1').'
';

// table of selected contact
$contact_table = '
    <table class="relations">
        <caption>'.__('Contacts').'</caption>
        <thead>
            <tr>
                <td title="'.__('Family Name').'">'.__('Family Name').'</td>
                <td title="'.__('First Name').'">'.__('First Name').'</td>
                <td title="'.__('email').'">'.__('email').'</td>
                <td title="'.__('Role').'">'.__('Role').'</td>
                <td title="'.__('or new Role').'">'.__('or new Role').'</td>
            </tr>
        </thead>
        <tbody>';

if (isset($ID)&&($ID!=0)) {
    $query = "SELECT contact_ID, role FROM ".DB_PREFIX."project_contacts_rel
              WHERE project_ID = ".(int)$ID;
    $result = db_query($query) or db_die();
    while ($row = db_fetch_row($result)) {
        $contact_query = "SELECT ID, vorname, nachname, email
                            FROM ".DB_PREFIX."contacts
                           WHERE ID = ".(int)$row[0]."
                             AND is_deleted is NULL";
        $contact_result = db_query($contact_query) or db_die();
        $contact_row = db_fetch_row($contact_result);
        $contact_table .= "
            <tr>
                <td>".$contact_row[2]."</td>
                <td>".$contact_row[1]."</td>
                <td>".$contact_row[3]."</td>
                <td>".make_select_roles($row[0],$row[1],'contacts')."</td>
                <td><input name='c_".$row[0]."_text_role' type='text' size='8' maxlength='200'></td>
            </tr>
        ";
    }
} else if (is_array($contact_personen)&&(!empty($contact_personen))) {
    foreach($contact_personen as $tmp => $contact_id) {
        $contact_query = "SELECT ID, vorname, nachname, email
                         FROM ".DB_PREFIX."contacts
                           WHERE ID = ".(int)$contact_id."
                             AND is_deleted is NULL";
        $contact_result = db_query($contact_query);
        $contact_row = db_fetch_row($contact_result);
        $contact_role_str = 'c_'.$contact_id.'_role';
        $contact_role = (isset($_POST[$contact_role_str])) ? xss($_POST[$contact_role_str]) : '';
        $contact_table .= "
            <tr>
                <td>".$contact_row[2]."</td>
                <td>".$contact_row[1]."</td>
                <td>".$contact_row[3]."</td>
                <td>".make_select_roles($contact_id,$contact_role,'contacts')."</td>
                <td><input name='c_".$contact_id."_text_role' type='text' size='8' maxlength='200'></td>
            </tr>
        ";
    }
}
$contact_table .= '</tbody></table>';
$out_array[] =array(__('Contacts'), '

        <div style="float:left;width:10%;padding:23px;">'.$contact_assignment.'</div>
        <div style="float:right;width:70%;padding:23px;">'.$contact_table.'</div>
        <br style="clear:both"/>');


/**************************************************
Phases
**************************************************/
if ($ID > 0) {
    $condition=' WHERE project_ID='.(int)$ID;

    //save old fields
    $fields_tmp=$fields;
    $fields = build_array('project_elements', $ID, 'view');
    $phases  = '<br />'.build_table(array('ID', 'von','von','parent'), 'project_elements', $condition, 0, $perpage, "projects.php?mode=forms&amp;ID=$ID", 700, true,__('Phases'));
	$fields=$fields_tmp;
	if(!$read_o) {
		$phases.='<br/><input title="'.__('New phase').'" value="'.__('New phase').'" class="button" onclick ="var w = window.open(\''.PATH_PRE.'project_elements/project_elements.php?mode=forms&justform=1&project_ID='.$ID.'\',\''.__('Phases').'\',\'width=1140px,height=540px,scrollbars=yes,resizable=yes\'); w.focus();" type="button">';
	}
	$out_array[]=array(__('Phases'),$phases);
}

/**************************************************
related objects
**************************************************/

$related=array();

$output2 = '';
if (($ID > 0) && ($justform < 1)) {
    $output2 .= "<br /><br />\n";
    $projekt_ID = $ID;
    // include the lib
    include_once(LIB_PATH."/show_related.inc.php");
    $referer = "projects.php?mode=forms&amp;ID=$ID";
    // show related todos
    if (PHPR_TODO and check_role("todo") > 0) {
        $query = "project = ".(int)$ID;
        $output2 .= show_related_todo($query, $referer);
        $output2 .= "<br />\n";
    }

    // related notes, show only for existing projects
    if (PHPR_NOTES and check_role("notes") > 0) {
        $query = "projekt = ".(int)$ID;
        $output2 .= show_related_notes($query, $referer);
        $output2 .= "<br />\n";
    }

    // show related files
    if (PHPR_FILEMANAGER and check_role("filemanager") > 0) {
        $query = "div2 = '$ID'";
        $output2 .= show_related_files($query, $referer);
        $output2 .= "<br />\n";
    }

    // show related events
    if (PHPR_CALENDAR and check_role("calendar") > 0) {
        $query = "projekt = ".(int)$ID;
        $output2 .= show_related_events($query, $referer);
        $output2 .= "<br />\n";
    }

    // show related helpdesk
    if (PHPR_RTS > 0 && check_role("helpdesk") > 0) {
        $query = "proj = ".(int)$ID;
        $output2.= show_related_helpdesk($query,$referer);
        $output2.= "<br />\n";
    }

    // show related emails
    if (PHPR_QUICKMAIL > 0 && check_role("mail") > 0) {
        $query = "projekt = ".(int)$ID;
        $output2.= show_related_mail($query,$referer);
        $output2.= "<br />\n";
    }


    // show related cost
    if (PHPR_COSTS > 0 && check_role("costs") > 0) {
        $query = "projekt = ".(int)$ID;
        $output2.= show_related_costs($query,$referer);
        $output2.= "<br />\n";
    }

    // show history
    if (PHPR_HISTORY_LOG == 2) $output2 .= history_show('projekte', $ID);
    
    $out_array[]=array(__('Related Objects'),$output2);
}

// end show related objects
// ************************

// close the big form

$output.= generate_output($out_array);

$out_array=array();

$output .= '<div class="hline"></div>';
$output .= get_buttons_area($buttons);
$output .= '<div class="hline"></div>';

$output .= '</form>';

echo $output;

// end  of big form :-)


/**
 * set variables for a new root project
 * use global vars
 *
 * @param void
 * @return void
 */
function set_new_project() {
    global $ID, $anfang, $ende, $row;

    $ID      = $row[0] = 0;
    $anfang  = date("Y")."-".date("m")."-".date("d");
    $ende    = date("Y")."-12-31";
    $row[16] = 0;   // stundensatz / hourly rate
    $row[17] = 0;   // budget
}

function get_project_overrules ($ID, $saved_costcentre, $saved_costunits, $costunits) {
	global $records;

	$tree_elements = get_elements_of_tree("projekte",
                        "name, costcentre_id",
                        "",
                        "acc",
                        "ORDER BY name",
                        "",
                        "parent");
	$myDepth = null;
	$overrule = array("costunit"=>array(), "costcentre"=>null, "ccname"=>"-", "cuname"=> "-");
	foreach(array_reverse($tree_elements) as $el) {
	    if ($el['value'] == $ID) {
	        $myDepth = $el['depth'];
	    }

	    if (!is_null($myDepth) && ((int)$el['depth']) < $myDepth) {
	        if ($records[$el['value']][4] > 0 && empty($saved_costcentre) && empty($overrule['costcentre'])) {
	            $overrule['costcentre'] = $records[$el['value']][4];
	            $overrule['ccname'] = $records[$el['value']][3];
	        }

	        if (count($saved_costunits[$ID]) == 0 && is_array($saved_costunits[$el['value']]) && count($overrule['costunit']) == 0) {
	            $overrule['costunit'] = $saved_costunits[$el['value']];
	            $overrule['cuname'] = $records[$el['value']][3];
	        }
	     }

	    if (((int)$el['depth']) == 0 && !is_null($myDepth)) {
	    	$myDepth = NULL;
	    }
	}

	return $overrule;
}

function is_project_overruled ($overrule) {
	if (count($overrule['costunit']) > 0
	 || count($overrule['costcentre']) > 0) {
	 	return true;
	}

	return false;
}
?>
