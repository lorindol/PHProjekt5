<?php

// err_pro.php - PHProjekt Version 5.2
// copyright  ©  2000-2005 Albrecht Guenther  ag@phprojekt.com
// www.phprojekt.com
// Author: Albrecht Guenther, $Author: polidor $
// $Id: err_pro.php,v 1.21 2006/10/25 05:31:19 polidor Exp $

// check whether the lib has been included - authentication!
if (!defined("lib_included")) die("Please use index.php!");

if ($cancel) {
    include_once("./projects_view.php");
}
switch(true){
    case isset($upboth):
        $upanf=true;
        $upende=true;
    case isset($upanf):
        $_POST['anfang'] = $anfang_alt;
        $anfang=$anfang_alt;
        $_POST['upanf'] = "";
    case isset($upende):
        $_POST['ende'] = $ende_alt;
        $ende = $ende_alt;
        $_POST['upende'] = "";
        break;
    case isset($upboth1):
        $upanf1=true;
        $upende1=true;
    case isset($upanf1):
        $_POST['anfang'] = $anfang_alt;
        $anfang=$anfang_alt;
        $_POST['upanf'] = "";
    case isset($upende1):
        $_POST['ende'] = $ende_alt;
        $ende = $ende_alt;
        $_POST['upende'] = "";
        break;
    case isset($downboth):
        $downanf=true;
        $downende=true;
    case isset($downanf):
        move_up($parent,'anfang',$anfang);
    case isset($downende):
        move_up($parent,'ende',$ende);
        break;
    case isset($downboth1):
        $downanf1=true;
        $downende1=true;
    case isset($downanf1):
        move_down($ID,'anfang',$anfang);
    case isset($downende1):
        move_down($ID,'ende',$ende);
        break;
}
include_once("./projects_data.php");


function oerror($parent) {
    global $anfang, $ende;

    // the project is subproject? check whether the start and end time is within the limits of the parent project
    $result2 = db_query("select anfang, ende,ID
                           from ".DB_PREFIX."projekte
                          where ID = ".(int)$parent) or db_die();
    $row2 = db_fetch_row($result2);
    // timespan exceeds timespan of parent -> die ...
    $anfang_time    = makeTime($anfang);
    $anfangalt = makeTime($row2[0]);
    $ende_time      = makeTime($ende);
    $endealt   = makeTime($row2[1]);
    if ($anfang_time   < $anfangalt or $ende_time > $endealt) {
        //tabs
        $tabs = array();
        $output = '<div id="global-header">';
        $output .= get_tabs_area($tabs);
        $output .= breadcrumb($module);
        $output .= '</div>';
        $output .= '<div id="global-content">';
        $output .=' <div class="status_bar">
        <span class="status_bar">
            '.__('Status').':&nbsp;'.__('A conflict exists with the following parent project:').'  &nbsp; '.
        slookup('projekte','name','ID', $parent,'1')." - ".__('Begin').": ".slookup('projekte','anfang','ID', $parent,'1')."
           ".__('End').": ".slookup('projekte','ende','ID', $parent,'1').'
        </span></div>';
        $output .= "<form name='form' action='projects.php' method='POST'>";
        foreach ($_POST as $pk => $pval) {
            $output .= "<input type='hidden' name='".xss($pk)."' value='".xss($pval)."' />\n";
        }
        $output .= "<input type='hidden' name='inclu' value='err_pro.php' />";
        $output .="<div class='header'>".__('You can choose between the following options:')."</div>";
        $output .="<div class='formbody_mailops'>";
        $output .= "<ul><li>".__('Discard changes')." ".get_go_button_with_name("cancel")."</li>";

        if ($anfang_time   < $anfangalt && $ende_time > $endealt) {
            $output .= "<input type='hidden' name='anfang_alt' value='$row2[0]' />";
            $output .= "<input type='hidden' name='ende_alt' value='$row2[1]' />";
            $output .= "
            <li> ".__('Set the start of current project to')."$row2[0]  ".__('and')." ".
            __('the end to')." $row2[1] ".get_go_button_with_name("upboth")."
            </li>
            <li> ".__('Set the start of all affected parent projects to')." $anfang ".__('and')." ".
            __('the end to')." $ende ".get_go_button_with_name("downboth")."
             </li>";

        }
        else if ($anfang_time   < $anfangalt) {
            $output .= "<input type='hidden' name='anfang_alt' value='$row2[0]' />";
            $output .= "
        <li>".__('Set the start of current project to ')."$row2[0]  ".get_go_button_with_name("upanf")."
        </li>
        <li>".__('Set the start of all affected parent projects to')." $anfang ".get_go_button_with_name("downanf")."
        </li>";

        }
        else if ($ende_time > $endealt) {
            $output .= "<input type='hidden' name='ende_alt' value='$row2[1]' />";
            $output .= "
        <li> ".__('Set the end of current project to')." $row2[1] ".get_go_button_with_name("upende")."
        </li>
         <li> ".__('Set the end of all affected parent projects to')." $ende ".get_go_button_with_name("downende")."
          </li>  ";
        }
        $output .= "</ul><br /></div></form>\n";
        echo $output;
        die();
    }
}

function uerror($parent) {
    global $anfang, $ende;

    $anfang_time    = makeTime($anfang);
    $ende_time      = makeTime($ende);
    foreach ($parent as $kidproj) {
        // the project is subproject? check whether the start and end time is within the limits of the parent project
        $result2 = db_query("select anfang, ende,ID
                               from ".DB_PREFIX."projekte
                              where ID = ".(int)$kidproj) or db_die();
        $row2 = db_fetch_row($result2);
        // timespan exceeds timespan of parent -> die ...
        $anfangalt = makeTime($row2[0]);
        $endealt = makeTime($row2[1]);
        if (($anfang_time   > $anfangalt or $ende_time < $endealt)&&$kidproj>0) {
            //tabs
            $tabs = array();
            $output = '<div id="global-header">';
            $output .= get_tabs_area($tabs);
            $output .= breadcrumb($module);
            $output .= '</div>';
            $output .= '<div id="global-content">';
            $output .=' <div class="status_bar">
            <span class="status_bar">
            '.__('Status').':&nbsp;'.__('A conflict exists with the following subproject:').'  &nbsp; '.
            slookup('projekte','name','ID', $kidproj,'1')." - ".__('Begin').": ".slookup('projekte','anfang','ID', $kidproj,'1')."
           ".__('End').": ".slookup('projekte','ende','ID', $kidproj,'1').'
            </span></div>';
            $output .="<form name='form' action='projects.php' method='post'>";
            foreach($_POST as $pk => $pval){
                $output .= "<input type='hidden' name='".xss($pk)."' value='".xss($pval)."' />\n";
            }
            $output .= "<input type='hidden' name='inclu' value='err_pro.php' />";
            $output .="<div class='header'>".__('You can choose between the following options:')."</div>";
            $output .="<div class='formbody_mailops'>";
            $output .= "<ul><li>".__('Discard changes')." ".get_go_button_with_name("cancel")."</li>";
            if ($anfang_time   > $anfangalt && $ende_time < $endealt) {
                $output .= "<input type='hidden' name='anfang_alt' value='$row2[0]' />";
                $output .= "<input type='hidden' name='ende_alt' value='$row2[1]' />";
                $output .= "
            <li> ".__('Set the start of current project to')." $row2[0] ".('and').
                __('set the end to')."
               ".$row2[1]." ".get_go_button_with_name("upboth1")."
            </li>
            <li> ".__('Set the start of all affected subprojects to')." $ende ".__('and')." ".
                __('set the end to')."$anfang"." ".get_go_button_with_name("downboth1")."
             </li>";
            }
            else if ($anfang_time   > $anfangalt) {
                $output .= "<input type='hidden' name='anfang_alt' value='$row2[0]' />";
                $output .= "
        	<li>".__('Set the start of current project to')." $row2[0]
        	".get_go_button_with_name("upanf1")."
        	</li>
        	<li>".__('Set the start of all affected subprojects to')." $anfang
        	 ".get_go_button_with_name("downanf1")."
        	</li>";    
            }
            else if ($ende_time < $endealt) {
                $output .= "<input type='hidden' name='ende_alt' value='$row2[1]' />";
                $output .= "
     	   	<li> ".__('Set the end  of the current Projekt to')." $row2[1] ".get_go_button_with_name("upende1")."
        	</li>
         	<li> ".__('Set the end of all affected subprojects to')." $ende ".get_go_button_with_name("downende1")."
          	</li>  ";
            }
            $output .= "</ul><br /></div></form>\n";
            echo $output;
            die();
        }
    }
}

function move_up($ID, $field, $newdate) {
    // get the value
    $query="SELECT ".qss($field).", parent
                   FROM ".DB_PREFIX."projekte
                   WHERE ID = ".(int)$ID;
    $result = db_query($query) or db_die();
    $row = db_fetch_row($result);
    $get_update=false;
    switch($field){
        case'anfang':
        $get_update= strcmp($newdate, $row[0]) > 0 ? false : true;
        break;
        case'ende':
        $get_update= strcmp($newdate, $row[0]) < 0 ? false : true;
        break;
    }
    // update current project
    if($get_update){
        $query="UPDATE ".DB_PREFIX."projekte
                       SET ".qss($field)." = '$newdate'
                       WHERE ID = ".(int)$ID;
        $result = db_query($query) or db_die();
    }
    // update parentds
    if($row[1]>0)move_up($row[1], $field, $newdate);
}

/**
 * This function moves the date of all subprojects and current project!
 *
 * @author Nina Schmitt
 * @param int $ID
 * @param string $field
 * @param string $newdar
 */
function move_down($ID, $field, $newdate) {
    // get the value
    $query="SELECT ".qss($field)."
                   FROM ".DB_PREFIX."projekte
                   WHERE ID = ".(int)$ID;
    $result = db_query($query) or db_die();
    $get_update=false;
    switch($field){
        case'anfang':
        $get_update= strcmp($newdate, $row[0]) > 0 ? false : true;
        case'ende':
        $get_update= strcmp($newdate, $row[0]) < 0 ? false : true;
    }
    // update current project
    if($get_update){
        $query="UPDATE ".DB_PREFIX."projekte
                       SET ".qss($field)." = '$newdate'
                       WHERE ID = ".(int)$ID;
        $result = db_query($query) or db_die();
    }
    // update subproject
    $query="SELECT ID
                 FROM ".DB_PREFIX."projekte
                 WHERE parent = ".(int)$ID;
    $result = db_query($query) or db_die();
    while ($row = db_fetch_row($result)) {
        move_down($row[0], $field, $newdate);
    }
}


function makeTime($time) {
    $temp  = explode('-', $time);
    $mtime = mktime(0, 0, 0, (int)$temp[1], (int)$temp[2], (int)$temp[0]);
    return $mtime;
}

?>
