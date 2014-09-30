<?php
/**
 * Contect Menu functions
 *
 * @package    	lib
 * @subpackage 	main
 * @author     	Albrecht Guenther, $Author: gustavo $
 * @licence     GPL, see www.gnu.org/copyleft/gpl.html
 * @copyright  	2000-2006 Mayflower GmbH www.mayflower.de
 * @version    	$Id: contextmenu.inc.php,v 1.39 2007-05-31 08:11:51 gustavo Exp $
 */

// check whether the lib has been included - authentication!
if (!defined("lib_included")) die("Please use index.php!");

/**
 * Class contextmenu
 *
 * @abstract Provides contextmenus (mouse right click) for several occasions: list view, column header etc.
 * @package lib
 */
class contextmenu
{
    var $menucolID;
    var $menulistID;
    var $menusysID;

    /**
     * Contextmenu for an entry in a list - mostly used if the whole line is referenced to the element
     *
     * @param  string	$module					- Module name
     * @param  array		$listentries_single		- Simple option
     * @param  array		$listentries_selected 	- Multiple options
     * @return  string								The whole html div
     */
    function menu_table($module, $listentries_single, $listentries_selected) {
        // operations in all modules: modify, copy and delete
        $listmenu_start = array(
        );
        // closing the table entries with 'select all' - 'deselect all'
        $listmenu_end = array(
            '0'=>array('selectAll()',__('Select all')),
            '1'=>array('deselectAll()',__('Deselect all'))
        );

        $this->menulistID = $this->create_menuID();
        $str .= $this->menu_start($this->menulistID,'-1450px','-2000px','100px','200px','200px','');
        $str .= $this->menu_entries($listmenu_start);
        if ($this->menu_entries($listentries_single)) {
            $str .= $this->menu_entries($listentries_single);
            $str .= $this->menu_line('<hr />');
        }
        $str .= $this->menu_entries($listentries_selected);
        $str .= $this->menu_line('<hr />');
        $str .= $this->menu_script($listmenu_end);
        $str .= $this->menu_close();
        return $str;
    }

    /**
     * Contextmenu for a column header of a table
     *
     * @param  string 	$module			- Module name
     * @param  string 	$link				- Module link
     * @param bool   		$is_related_obj - Related Object
     * @param bool   		$is_addon 		- True if called from a addon
     * @return  string						The whole html div
     */
    function menu_columnheader($module, $link=null, $is_related_obj=false, $is_addon=false) {
        if (!$link) $link = $module;

        $width = array(
            '0'=>array("nop();' onmousedown='resizeImage(20,\"relative\")",__('wider')),
            '1'=>array("nop();' onmousedown='resizeImage(-20,\"relative\")",__('narrower')),
        );
        if($is_related_obj){
            global $ID;
            $direction = array(
                '0'=>array('doLink',basename($_SERVER['SCRIPT_NAME'])."?mode=forms&amp;ID=".$ID."&amp;sort_module=".$module."&amp;direction_rel=ASC&amp;sort_col=",'','',__('ascending')),
                '1'=>array('doLink',basename($_SERVER['SCRIPT_NAME'])."?mode=forms&amp;ID=".$ID."&amp;sort_module=".$module."&amp;direction_rel=DESC&amp;sort_col=",'','',__('descending'))
            );
        }
        else{
            $addon = $is_addon ? '&amp;addon='.$module : '';
            $direction = array(
                '0'=>array('doLink',$link.".php?mode=view&amp;sort_module=".$module.$addon."&amp;direction=ASC&amp;sort=",'','',__('ascending')),
                '1'=>array('doLink',$link.".php?mode=view&amp;sort_module=".$module.$addon."&amp;direction=DESC&amp;sort=",'','',__('descending'))
            );
            unset($addon);
        }
        $this->menucolID = $this->create_menuID();
        $str .= $this->menu_start($this->menucolID,'-350px','-2000px','100px','150px','80px',__('Column'));
        $str .= $this->menu_line('<b>'.__('Width').'</b>');
        $str .= $this->menu_script($width);
        // doesn't work at the moment :-(
        // $str .= $this->set_width();
        $str .= $this->save_width();
        $str .= $this->menu_line('<b>'.__('Sorting').'</b>');
        $str .= $this->menu_entries($direction);
        $str .= $this->menu_close();
        return $str;
    }

    /**
     * Contextmenu for actions concerning the list view of a module
     *
     * @param string $module	- The module name
     * @return string 			The whole html div
     */
    function menu_page($module) {
        if ($module == 'forum') {
                global $fID;
                $page_actions = array(
                    '1' => array('doLink',$module.".php?fID=".$fID."&amp;toggle_archive_flag=1",'','',show_archive_flag($module)),
                    );
        }
        else {
            $page_actions = array(
                '1' => array('doLink',$module.".php?mode=view&amp;toggle_archive_flag=1",'','',show_archive_flag($module)),
                '2' => array('doLink',$module.".php?mode=view&amp;toggle_read_flag=1",'','',show_read_flag($module)),
                '3' => array('doLink',$module.".php?mode=view&amp;tree_mode=open",'','',"&nbsp;".__('Tree view').": ".__('open')),
                '4' => array('doLink',$module.".php?mode=view&amp;tree_mode=close",'','',"&nbsp;".__('Tree view').": ".__('closed'))
                );
        }

        if ($module != 'forum') {
            if (PHPR_SUPPORT_HTML) $page_actions[] = array('doLink',$module.".php?mode=view&amp;toggle_html_editor_flag=1",'','','&nbsp;'.show_html_editor_flag($module));
        }
        $page_actions[] = array('doLink',$module.".php?mode=view&amp;toggle_show_all_groups=1",'','','&nbsp;'.show_group_flag($module));
        $this->menusysID = $this->create_menuID();
        $str  = $this->menu_start($this->menusysID,'-450px','-2000px','100px','200px','80px',$module);
        $str .= $this->menu_entries($page_actions);
        $str .= $this->menu_close();
        return $str;
    }

    /**
     * Creates a name for this menu
     *
     * @param void
     * @return string 	A uniques string for the menu name like menu1, menu2 etc.
     */
    function create_menuID() {
        static $name;
        if (!isset($name)) $name = 0;
        $name++;
        return 'menu'.$name;
    }

    /**
     * Contextmenu for actions concerning the list view of a module
     *
     * @param int $id			- Menu ID
     * @param int $top		- Top value
     * @param int $left		- Left value
     * @param int $z			- Z-index value
     * @param int	$width	- Width value
     * @param int	$height	- Height value
     * @param string $title	- Without use
     * @return string 		HTML part of the div
     */
    function menu_start($id, $top, $left, $z, $width, $height, $title) {
        $z = 99; // context menu should always overlay other layers
        $str = "
            <div id='".$id."' style='position:absolute;top:".$top.";left:".$left.";z-index:".$z.";width:".$width.";height:".$height.";'>
            <table class='contextmenu' cellpadding='0' cellspacing='0' width='".str_replace('px', '', $width)."'>";
        return $str;
    }

    /**
     * Javascript function
     *
     * @param array $entries	- Array with the data of the items
     * @return string 			HTML part of the div
     */
    function menu_entries($entries) {
        $str = '';
        if ($entries) {
            foreach ($entries as $menuentry) {
                $str .= "<tr><td><a href='#' onclick='javascript:".$menuentry[0]."(\"".$menuentry[1]."\",\"".$menuentry[2]."\",\"".$menuentry[3]."\")'>".$menuentry[4]."</a></td></tr>\n";
            }
        }
        return $str;
    }

    /**
     * Close link
     *
     * @param void
     * @return string 	HTML part of the div
     */
    function menu_close() {
        $str = $this->menu_line('<hr />');
        $str .= "<tr><td><a class='menu' href='javascript:nop()' onmousedown='document.onmouseup=hideMenu'>".__('Close')."</a></td></tr></table></div>\n";
        return $str;
    }

    /**
     * Make a line
     *
     * @param string $string	- Text in the line
     * @return string				HTML part of the div
     */
    function menu_line($string) {
        return   "<tr><td>".$string."</td></tr>";
    }

    /**
     * Javascript function link
     *
     * @param array	$actions	- Array with the javascript options
     * @return string				HTML part of the div
     */
    function menu_script($actions) {
        $str = '';
        foreach ($actions as $action) {
            $str .= "<tr><td><a href='javascript:".$action[0]."'>".$action[1]."</a></td></tr>\n";
        }
        return $str;
    }

    /**
     * Save width link
     *
     * @param void
     * @return string		HTML part of the div
     */
    function save_width() {
        global $fields, $tdw, $module;
        $str .= "<tr><td><form method='post' action='".$module.".php' name='tdwfrm'>\n";
        $hidden = array('mode'=>'view','filter'=>$field_name,'rule'=>'like','save_tdwidth'=>1);
        if (SID) $hidden[session_name()] = session_id();
        $str .= hidden_fields($hidden);
        if (is_array($fields)) {
            $n_fields = 0;
            foreach ($fields as $field_name => $field) {
                if ($field['list_pos'] > 0) $n_fields++;
            }
            foreach ($fields as $field_name => $field) {
                if (!isset($tdw[$module][$field_name]) or $tdw[$module][$field_name] <> 94) {
                    $tdw[$field_name] = floor(100/$n_fields)*14;
                } else {
                    $tdw[$field_name] = $tdw[$module][$field_name];
                }
                $str .= "<input type='hidden' name='ii$field_name' value='".$tdw[$field_name]."' />\n";
            }
        }
        $str .= "<a href='#' onclick='showsize(); document.forms.tdwfrm.submit();'>".__('Save width')."</a></form></td></tr>\n";
        $str .= $this->menu_line('<hr />');
        return $str;
    }

    /**
     * Set width link
     *
     * @param void
     * @return string		HTML part of the div
     */
    function set_width() {
        global $fields, $tdw, $module;
        $str .= "<tr><td><form name='setwidth1' onsubmit=\"resizeImage(document.setwidth1.size.value,'absolut')\">\n";
        $str .= " &nbsp;". __('Width').": <input type='text' name='size' size='3' onfocus='document.onmouseup=nop;' /></form></td></tr>\n";
        return $str;
    }

    /**
     * Create the context menu
     *
     * @param  string 	$module					- Module name
     * @param  string 	$link						- Module link
     * @param bool   		$is_related_obj 		- Related Object
     * @param  array		$listentries_single		- Simple option
     * @param  array		$listentries_selected 	- Multiple options
     * @param int			$contextmenu			- Second contextmenu value
     * @return  string								The whole html div
     */
    function draw_contextmenus($module, $link, $is_related_obj, $listentries_single, $listentries_selected, $contextmenu) {
        global $menu2;
        global $menu3;
        $menu2 = null;
        $menu3 = new contextmenu();
        $html = $menu3->menu_page($module);
        if ($contextmenu > 0) {
            $is_addon = $_SESSION['common']['module'] == 'addons';
            $menu2 = new contextmenu();
            $html .= $menu2->menu_columnheader($module, $link, $is_related_obj, $is_addon);
            $html .= $menu2->menu_table($module, $listentries_single, $listentries_selected);
            unset($is_addon);
        }
        echo $html;
    }
}

/**
 * Show read elements link
 *
 * @param string $module - Module name
 * @return string       		Space or text link
 */
function show_read_flag($module) {
    (isset($_SESSION['show_read_elements']["$module"]) && $_SESSION['show_read_elements']["$module"] > 0) ? $str = '&nbsp;'. __('Show read elements') : $str = '&nbsp;'. __('Hide read elements') ;
    return $str;
}

/**
 * Show archived elements link
 *
 * @param string $module - Module
 * @return string      		Space or text link
 */
function show_archive_flag($module) {
    (isset($_SESSION['show_archive_elements'][$module]) and $_SESSION['show_archive_elements']["$module"] > 0) ? $str = '&nbsp;'. __('Show archive elements') : $str = '&nbsp;'. __('Hide archive elements');
    return $str;
}

/**
 * Show html editor link
 *
 * @param string $module - Module
 * @return string       		Space or text link
 */
function show_html_editor_flag($module) {
    (isset($_SESSION['show_html_editor'][$module]) and $_SESSION['show_html_editor']["$module"] > 0) ? $str = __('switch off html editor') : $str = __('switch on html editor');
    return $str;
}

/**
 * Show all group link
 *
 * @param string $module - Module
 * @return string       		Space or text link
 */
function show_group_flag($module) {
    (isset($_SESSION['show_all_groups'][$module]) and $_SESSION['show_all_groups']["$module"] > 0) ? $str =  __('show records from current group only') : $str = __('show records from all groups');
    return $str;
}
?>
