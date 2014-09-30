<?php
/**
 * form for manage branchs
 *
 * @package    projects
 * @subpackage options
 * @author     Albrecht Guenther, $Author: albrecht $
 * @licence    GPL, see www.gnu.org/copyleft/gpl.html
 * @copyright  2000-2006 Mayflower GmbH www.mayflower.de
 * @version    $Id: projects_options.php,v 1.39 2008-03-04 10:52:00 albrecht Exp $
 */

// check whether the lib has been included - authentication!
if (!defined("lib_included")) die("Please use index.php!");

// check role
if (check_role('projects') < 1) die('You are not allowed to do this!');


if ($action == 'cop_branch') {
    // check form token
    check_csrftoken();

    include_once(LIB_PATH.'/branches.inc.php');
    copy_branch($root_ID, $new_parent_ID,0,true);
    $mode = 'view';
    include_once('./projects_view.php');
}
if ($action == 'template_branch') {
    // check form token
    check_csrftoken();

    include_once(LIB_PATH.'/branches.inc.php');

    $baseTimestamp = 0;
    
    $TempRes = db_query("SELECT anfang, name
                           FROM ".DB_PREFIX."projekte
                          WHERE ID = ".(int)$template_ID."
                            AND is_deleted is NULL");
    
    if ($row = db_fetch_row($TempRes)) {

        if ($date_format_object->is_db_date($row[0])) {
            $baseTimestamp = $date_format_object->get_timestamp_from_date($row[0]);
            $name = $row[1];
        }
    }

    $temp_ID = copy_branch($template_ID, $new_parent_ID, $user_ID, true, $baseTimestamp, true);

    // The name of the new project (at this point it was generated)
    $newName = $name.'('.__('Copy of').' '.(int)$template_ID.')';

    // Updating template status (the copied project will not be a template) and changing the name of the copied project
    $TempRes = db_query("UPDATE ".DB_PREFIX."projekte set template = '', name='$newName' WHERE ID = ".(int)$temp_ID);



    $mode = 'view';
    include_once('./projects_view.php');
}
else if ($action == 'move_branch') {
    // check form token
    check_csrftoken();

    include_once(LIB_PATH.'/branches.inc.php');
    move_branch($ID, $field, $days);
    $mode = 'view';
    include_once('./projects_view.php');
}
else {
    $output .= breadcrumb($module, array(array('title'=>__('Options'))));
    $output .= '</div>';
    $output .= $content_div;

    // button bar
    $buttons = array();
    $buttons[] = array('type' => 'link', 'href' => 'projects.php?mode=forms&amp;action=new'.$sid, 'text' => __('New'), 'active' => false);
    $buttons[] = array('type' => 'link', 'href' => 'projects.php?mode=options'.$sid, 'text' => __('Options'), 'active' => true);
    $buttons[] = array('type' => 'link', 'href' => 'projects.php?mode=stat'.$sid, 'text' => __('Statistics'), 'active' => false);
    $buttons[] = array('type' => 'link', 'href' => 'projects.php?mode=stat&amp;mode2=mystat'.$sid, 'text' => __('My Statistic'), 'active' => false);
    $buttons[] = array('type' => 'link', 'href' => 'projects.php?mode=gantt'.$sid, 'text' => __('Gantt'), 'active' => false);
    $buttons[] = array('type' => 'link', 'href' => 'projects.php?type='.$type.'&amp;mode=view', 'text' => __('List View'), 'active' => false);
    $output .= get_buttons_area($buttons);

    // prepare values for function
    $where = "where (acc like 'system' or ((von = ".(int)$user_ID." or acc like 'group' or acc like '%\"$user_kurz\"%') and $sql_user_group)) AND is_deleted is NULL";
    // copy project branches
    $hidden_fields = array ( "mode"     => "options",
                             "action"   => "cop_branch");
    
    // get order
    $order = " ".sort_string('projects');
    
    $copy_html  = "
    <br />
    <form action='projects.php' method='post'>
    ".hidden_fields($hidden_fields)."
    <fieldset><legend>".__('Copy project branch').": </legend>
    <label for='root_ID'>".__('Copy this element<br /> (and all elements below)').":</legend>
    <select name='root_ID' id='root_ID'><option value='0'></option>
    ".show_elements_of_tree("projekte","name",$where,"personen",$order,'',"parent",0)."
    </select>
    <br class='clear' /><label class='options' for='new_parent_ID' >".__('And put it below this element').": </label>
    <select name='new_parent_ID' id='new_parent_ID' class='options'><option value='0'></option>
    ".show_elements_of_tree("projekte","name",$where,"acc",$order,'',"parent",0)."
    </select></fieldset>";
    if (SID) $copy_html .= "<input type='hidden' name='".session_name()."' value='".session_id()."' />\n";
    $copy_html .= "<br />".get_buttons(array(array('type' => 'submit', 'value' => __('OK'), 'active' => false)))
    ."&nbsp;</form>\n";




    // where for templates
    $where_templates = str_replace("where (", "WHERE template = 'yes' AND (", $where);

    $hidden_fields = array ( "mode"     => "options",
                             "action"   => "template_branch");

    $copy_html  .= "
    <br />
    <form name='template' action='projects.php' method='post'>
    ".hidden_fields($hidden_fields)."
    <fieldset><legend>".__('Create project from template').": </legend>
    <label for='template_ID'>".__('Copy this template').":</legend>
    <select name='template_ID' id='template_ID'><option value='0'></option>
    ".show_elements_of_tree("projekte","name",$where_templates." AND is_deleted is NULL","personen"," order by name",'',"parent",0)."
    </select>
    <!--
    <br class='clear' /><label class='options' for='new_parent_ID' >".__('And put it below this element').": </label>
    <select name='new_parent_ID' id='new_parent_ID' class='options'><option value='0'></option>
    ".show_elements_of_tree("projekte","name",$where,"acc"," order by name",'',"parent",0)."
    </select>
    -->
    </fieldset>";
    if (SID) $copy_html .= "<input type='hidden' name='".session_name()."' value='".session_id()."' />\n";
    $copy_html .= "<br />".get_buttons(array(array('type' => 'submit', 'value' => __('OK'), 'active' => false)))
    ."&nbsp;</form>\n";


    //prepare values for function
    $where = "where $sql_user_group AND is_deleted is NULL";
    // move project branches

    $hidden_fields = array ( "mode"     => "options",
                             "action"   => "move_branch");
    $form_fields = array();
    $form_fields[] = array('type' => 'parsed_html', 'html' => '<br /><form action="projects.php" method="post"><fieldset><legend>'.__('Edit timeframe of a project branch').'</legend>');
    $form_fields[] = array('type' => 'parsed_html', 'html' => hidden_fields($hidden_fields));
    $form_fields[] = array('type' => 'parsed_html', 'html' => '<br />'.__('Please choose the date and the project (including all elements below) in order to change its timespan').'<br />');
    $options = array();
    $options[] = array('value' => 'anfang', 'text' => __('Begin'));
    $options[] = array('value' => 'ende', 'text' => __('End'));
    $form_fields[] = array('type' => 'select', 'name' => 'field', 'label' => __('Date').__(':'), 'options' => $options);

    $options = array();
    $options[] = array('value' => '0', 'text' => '');


    // missing show_elements_of_tree here ...
    $tmp = get_elements_of_tree("projekte","name",$where,"personen",$order,'',"parent",0);
    foreach($tmp as $option_data) {
        $options[] = array('value' => $option_data['value'], 'text' => (str_repeat('&nbsp;&nbsp;', $option_data['depth'])).$option_data['text'], 'selected' => $option_data['selected']);
    }
    $form_fields[] = array('type' => 'select', 'name' => 'ID', 'label' => __('Project').__(':'), 'options' => $options);

    $options = array();
    $options[] = array('value' => '0', 'text' => '');
    $options[] = array('value' => 'ende', 'text' => __('End'));
    for ($i=-100; $i<=-1; $i++){
        $options[] = array('value' => $i, 'text' => $i.' '.__('Days'));
    }
    $options[] = array('value' => '0', 'text' => '0 '.__('Days'), 'selected' => true);
    for ($i=1; $i<101; $i++){
        $options[] = array('value' => $i, 'text' => $i.' '.__('Days'));
    }
    $form_fields[] = array('type' => 'select', 'name' => 'days', 'label' => __('by').__(':'), 'options' => $options);
    if(SID) $form_fields[] = array('type' => 'hidden', 'name' => session_name(), 'value' => session_id());
    $form_fields[] = array('type' => 'parsed_html', 'html' => "</fieldset><br />".get_buttons(array(array('type' => 'submit', 'value' => __('OK'), 'active' => false)))."&nbsp;</form>\n");
    $edit_html= get_form_content($form_fields);

    $output .= $copy_html.$edit_html;

    echo $output;
}

?>
