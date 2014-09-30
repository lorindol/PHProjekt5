<?php
/**
 * Filter popup
 *
 * @package    	lib
 * @subpackage 	main
 * @author     	Johannes Schlueter
 * @licence     GPL, see www.gnu.org/copyleft/gpl.html
 * @copyright  	2000-2006 Mayflower GmbH www.mayflower.de
 * @version    	$Id: dbman_filter_pop.php,v 1.42 2008-03-04 10:51:59 albrecht Exp $
 */

define('PATH_PRE','../');
include_once(PATH_PRE.'lib/lib.inc.php');
include_once(PATH_PRE.'lib/dbman_filter.inc.php');
include_once(PATH_PRE.'lib/dbman_list.inc.php');
include_once(PATH_PRE.'lib/dbman_lib.inc.php');

// clean up some vars
$ID            		= (int) $_REQUEST['ID'];
$use            		= (int) $_REQUEST['use'];
$dele           		= (int) $_REQUEST['dele'];
$add            		= xss($_REQUEST['add']);
$mode           		= xss($_REQUEST['mode']);
$module        		= xss($_REQUEST['module']);
$opener        		= xss($_REQUEST['opener']);
$caption        		= xss($_REQUEST['caption']);
$expert        		= xss($_REQUEST['expert']);
$expert_filter  	= stripslashes($_REQUEST['expert_filter']);

include_once(PATH_PRE.'lib/dbman_filter_pop_selector_data.php');

if (empty($module) && empty($_REQUEST['filterform'])) die('Wrong call');
/*
if (isset($formdata['persons']) && $mode == "forms") {
    $persons = $formdata['persons'];
}
*/
if (PHPR_EXPERT_FILTERS == 1) {
    if (empty($expert_filter) && $expert == 1 && is_string($flist[$module]) && strlen($flist[$module]) > 0) {
        $fields = build_array($module,0);
        $table = get_table_by_module($module);
        $expert_filter = expert_filter_translate($flist[$module],$fields,true);
    }

    if (((!empty($expert_filter)) || $expert == 1) && !isset($table)) {
        $fields = build_array($module,0);
        $table = get_table_by_module($module);
    }
}

if ($module == 'contacts') $actionstring = 'action=contacts';
else                       $actionstring = 'module='.$module;

$caption = 'o_'.$module;
$caption = $$caption;

$js_close = "
<script type='text/javascript'>
<!--
window.opener.location.href = '../$opener/$opener.php?$actionstring&mode=$mode&ID=$ID"."$sid&';
window.close();
//-->
</script>\n";

$js_reload = "
<script type='text/javascript'>
<!--
window.location.href = '../$module/$module.php?$actionstring"."$sid';
//-->
</script>\n";

//echo set_page_header();

if ($speichern && strlen($speichern) > 0 && $expert == 1 && PHPR_EXPERT_FILTERS == 1) {
    include_once(PATH_PRE.'lib/dbman_filter.inc.php');
    if (expert_filter_check($expert_filter, $table, $fields) <> 1) {
        $_REQUEST['test'] = 1;
        $speichern = '';
    }
}

if ($nav == $module && !(isset($_REQUEST['action_form_to_access_selector']) ||
    isset($_REQUEST['action_form_to_access_selector_x']) ||
    isset($_REQUEST['action_access_selector_to_form_ok']) ||
    isset($_REQUEST['action_access_selector_to_form_cancel']) ||
    isset($_REQUEST['filterform']) ||
    isset($_REQUEST['filterdel']))) {
    //    die( print_r($_REQUEST));
    list($flist[$module],$sort,$directio, $operator) = load_filter($use, $module);
    header('Location: ../'.$module.'/'.$module.'.php?'.$actionstring.'&'.'mode='.$mode.'&sort='.$sort.'&direction='.$direction.'&operator='.$operator.$add.$sid);
    exit;
}
else {
    set_style();
    echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
    "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>'.__('Filter configuration').'</title>
'.(count($css_inc) > 0 ? implode("", $css_inc) : '').'
<style type="text/css">
body {
    background-image: none;
}
</style>
<link type="text/css" rel="shortcut icon" href="/'.PHPR_INSTALL_DIR.'favicon.ico" />
<link type="text/css" rel="stylesheet" media="screen" href="../layout/default/default_css.php" />
<script type="text/javascript" src="/'.PHPR_INSTALL_DIR.'lib/javascript/phprojekt.js"></script>\n
<!--[if gte IE 5]><link type="text/css" rel="stylesheet" media="screen" href="../layout/default/default_css_ie.php" /><![endif]-->
<script type=\'text/javascript\'>
function addFieldFilter() {
    x = document.frm;
    txt = x.expert_filter.value + x.add_field.value + " ";
    x.expert_filter.value = txt;
    x.add_field.value = "";
    x.expert_filter.focus();
}
</script>
</head>
<body>
<div id="global-main">';
    if ($mode == 'selector') {
    include_once(PATH_PRE.'lib/dbman_filter_pop_selector.php');
    die();
}

echo '
<div class="module_bar_top">'.__('Filter configuration').'</div>
<div id="global-content" class="popup" style="margin-left: 0px; margin-top: 0px; padding: 5px;">
    <fieldset>
    <legend>'.__('Filter configuration').' '.$caption.'</legend>
    <a href="./dbman_filter_pop.php?aufheben=1&amp;module='.$module.'&amp;opener='.$opener.'&amp;mode='.$mode.'&amp;'.$actionstring.'&amp;ID='.$ID.$sid.'">'.__('Disable set filters').'</a>
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';

    if (PHPR_EXPERT_FILTERS == 1) {
        if ($expert == 1) {
            echo '    <a href="./dbman_filter_pop.php?module='.$module.'&amp;opener='.$opener.'&amp;mode='.$mode.'&amp;'.$actionstring.'&amp;ID='.$ID.$sid.'">'.__('Normal mode').'</a>';
        } else {
            echo '    <a href="./dbman_filter_pop.php?module='.$module.'&amp;opener='.$opener.'&amp;mode='.$mode.'&amp;'.$actionstring.'&amp;expert=1&amp;ID='.$ID.$sid.'">'.__('Expert mode').'</a>';
        }
    }
    echo '<hr />';
    /*******************************
    *      assignment fields
    *******************************/
    $form_fields = array();

    include_once(LIB_PATH."/access_form.inc.php");
    // acc_read, exclude the user itself, acc_write, no parent possible, write access=yes
    include_once(LIB_PATH."/access.inc.php");

    // values of the access
    if (!isset($persons)) {
        if (!isset($_POST[$persons])) $str_persons = '';
        else $str_persons = xss($_POST[$persons]);
    } else $str_persons = $acc = serialize($persons);

    if (!isset($acc_write)) {
    	if (!isset($_POST['acc_write'])) $acc_write = '';
	else $acc_write = xss($_POST['acc_write']);
    }

    $form_fields[] = array('type' => 'parsed_html', 'html' => access_form($str_persons, 1, $acc_write, 0, 1,'acc',$read_acc));
    $assignment_fields = get_form_content($form_fields);

    /*****************************/
    if ($use) {
        list($flist[$module],$sort,$direction, $operator) = load_filter($use, $module);
        $_SESSION['f_sort'][$module]['sort'] = $sort;
        $_SESSION['f_sort'][$module]['direction'] = $direction;
        $_SESSION['flist']['operators'][$module] = $operator;
        echo $js_close;
    } else if ($dele) {
        delete_filter($dele, $module);
        echo $js_close;
    } else if ($aufheben) {
        $flist[$module] = array();
        echo $js_close;
    } else if ($speichern && strlen($speichern) > 0) {
        if ($expert == 1 && strlen($expert_filter) > 0 && PHPR_EXPERT_FILTERS == 1) {
            $expert_filter = addslashes($expert_filter);
            $expert_filter = expert_filter_translate($expert_filter, $fields);

            save_filter($module, $speichern,'','',$expert_filter);
        } elseif (is_array($_SESSION['flist'][$module]) and count($_SESSION['flist'][$module]) > 0) {
            save_filter($module, $speichern);
        } else {
            message_stack_in(__('Empty Filter cannot be saved'),$module,"error");
        }
        echo $js_close;
    } else {
        $filter       = get_filters($module,'');
        $filterDelete = get_filters($module,'w');

        $hiddenfields = "<input type='hidden' name='module' value='$module' />\n".
        "<input type='hidden' name='opener' value='$opener' />\n".
        "<input type='hidden' name='mode'   value='$mode' />\n".
        "<input type='hidden' name='ID'     value='$ID' />\n".
        "<input type='hidden' name='expert' value='$expert' />\n";
        if (SID) $hiddenfields .= "<input type='hidden' name='".session_name()."' value='".session_id()."' />";

        if ($expert == 1 && PHPR_EXPERT_FILTERS == 1) {
            echo '
    <form name="frm" action="dbman_filter_pop.php" method="post">
        <label class="label_block">'.__('Expert filter').'</label>
            <textarea name="expert_filter" class="halfsize">'.str_replace("/", "",stripslashes($expert_filter)).'</textarea>
            <br />
          <label class="label_block">'.__('Add field').'</label>
            <select id="add_field" name="add_field" onchange="addFieldFilter();">
                <option value=""> </option>';

            foreach ($fields as $key => $value) {
                if (substr($value['form_name'],0,3) == '__(') {
                    $nameToShow = str_replace(" ","_",__(substr($value['form_name'],4,-2)));
                } else {
                    $nameToShow = $value['form_name'];
                }
                echo '<option value=\''.str_replace("'", "&#39;",$nameToShow).'\'>'.$nameToShow.'</option>';
            }

            echo '</select>
            '.get_buttons(array(array('type' => 'submit', 'active' => false, 'name' => 'test', 'value' => __('Test Filter')))).'
        	'.$hiddenfields;

            if (isset($_REQUEST['test'])) {
	     //echo '<br />';
                if (expert_filter_check($expert_filter, $table, $fields) == 1) {
                    echo '<b>'.__("It works").'!</b>';
                } else {
                    echo '<b><font color="red">'.__("It doesn't work").'!</font></b>';
                }
            }

            echo '
    <br />';
        } else {
            echo'
               <form action="dbman_filter_pop.php" method="post">';
        }

        echo '
        '.$assignment_fields.'
        <label class="label_block">'.__('Save currently set filters').'</label>
            <input name="speichern" />
            '.get_buttons(array(array('type' => 'submit', 'active' => false, 'name' => '', 'value' => __('Save')))).'
        '.$hiddenfields.'

    </form>
    <br />
    <form action="dbman_filter_pop.php" method="post">
        <label class="label_block">'.__('Load filter').'</label>
        <select name="use">
            <option value=""></option>
';
        foreach ($filter as $id=>$value) {
            echo '<option value="'.$id.'">'.$value."</option>\n";
        }

        echo '
        </select>
        '.get_buttons(array(array('type' => 'submit', 'active' => false, 'name' => '', 'value' => __('use')))).'
        '.$hiddenfields.'
    </form>
    <form action="dbman_filter_pop.php" method="get">
        <label class="label_block">'.__('Delete saved filter').'</label>
        <select name="dele">
            <option value=""></option>
';
        foreach ($filterDelete as $id=>$value) {
            echo '<option value="'.$id.'">'.$value."</option>\n";
        }
        echo '
        </select>
        '.get_buttons(array(array('type' => 'submit', 'active' => false, 'name' => '', 'value' => __('Delete'), 'onclick' => 'return window.confirm(\''.__('Are you sure?').'\');'))).'
        '.$hiddenfields.'
    </form>
    </fieldset>
    <a href="javascript:window.close()">'.__('Close window').'</a>
';
        echo '
</div>

</div>
</body>
</html>
';
    }
    $_SESSION['flist'] =& $flist;
}
?>
