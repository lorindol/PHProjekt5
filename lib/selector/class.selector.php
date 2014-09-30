<?php
/**
* selector main class
*
* @package    selector
* @module     main
* @author     Martin Brotzeller, $Author: gustavo $
* @licence    GPL, see www.gnu.org/copyleft/gpl.html
* @copyright  2000-2006 Mayflower GmbH www.mayflower.de
* @version    $Id: class.selector.php,v 1.45.2.2 2007/02/25 14:57:18 gustavo Exp $
*/
if (!defined('lib_included')) die('Please use index.php!');


/**
* Class PHProjektSelector
*
* Display and selection of a group of objects, mainly users and contacts
*
* @author Martin Brotzeller
* @copyright (c) 2004 Mayflower GmbH
* @package PHProjekt
* @access public
*/
class PHProjektSelector {
    /** Type: single or multiple select
    *
    * @access public
    */
    var $type;
    
    /** View: selectbox or radio/checkboxes
    *
    * @access public
    */
    var $view;
    
    /**
     * Datasource: Source which delivers the objects, mainly user and contacts table. 
     * Based on the data source a file is includet that contains the source specific functions.
     * Every data source has to provide documentation of the needed fields in sourcedata, 
     * as well as the functions
     * fetch_fields(); show_filters(); and parse_filters();
     *
     * @access private
     */
    var $datasource;
    
    /** 
     * Sourcedata: Data needed to use the data source, depending on the current source. 
     *
     * @access private
     */
    var $sourcedata;
    
    /** 
     * Name: Formular identifier
     *
     * @access private
     */
    var $name;
    
    /**
     * Name of the submitbutton in the finish formular, that gets posted when the selection is done. 
     * Intention: this form only gets transfered if the javascript based changes aren't sent to the 
     * server yet and though updated in the selector. 
     * Needed data is formname.buttonname, i.e. finishForm.buttonname
     *
     * @access public
     */
    var $finishFormSubmitName = 'finishForm.finishButton';


    /**
     * Array containing names and values of the hidden fields that are needed in the form. 
     * Examples are mode, view and the like 
     *
     * @access private
     * @var array ( formelementNAME => formElementValue, ... )
     */
    var $hidden_fields = array();

    /** 
     * construktor
     *
     * @param $name       unique identifier of the selector
     * @param $datasource data source providing the data 
     * @param $sourcedata data describing reading and display of the data
     * @param $type       type of display
     * @param $view       mode of display
     * @access public
     */
    function PHProjektSelector($name, $datasource=NULL, $sourcedata=array(), $type='single', $view='select') {

        if (!isset($name) || $name=="") die(__('Please insert a name'));
        $this->name = $name;
        if ($datasource != NULL && in_array($datasource, array('contacts', 'users', 'projects'))){
            require_once(dirname(__FILE__).DIRECTORY_SEPARATOR."datasource_".$datasource.".php");
            $this->sourcedata =& $sourcedata;
            $this->datasource =  $datasource;
        } else {
            die(__('data source missing'));
        }

        if ($type=="single" || $type="multiple") $this->type = $type;
        else die(__('Invalid type').' '.$type);
        if ($view=="select") $this->view  = $view;
        else die(__('Invalid view').' '.$view);

    }

    /**
     * Add hidden fields to selector form ($mode, $view etc). 
     * the array key is the variable name, the value the variables value
     *
     * @access public
     * @param $fields array (ElementName => ElementValue, ...)
     */
    function set_hidden_fields($fields) {
        if (!is_array($fields)) return;
        foreach ($fields AS $name => $value) {
            $this->hidden_fields[(string)$name] = (string)$value;
        }
    }

    /** 
     * Create the select boxes 
    *
    * @param $fields    Array of fields 
    * @param $preselect Preselection of active objects 
    * @param $submiturl url form gets submitted to 
    * @param $size      rowcount of multiple select box 
    * @access
    */
    function show_select($fields, $preselect, $size) {
        $selstr = '';

        $selstr .= "\n<input type='hidden' name='parse".$this->name."' value='".$this->type.$this->view."' />\n";

        if ($this->type == "multiple") {
            $srcslct = array();
            $dstslct = array();
            if (!empty($fields['display'])) {
                foreach ($fields['display'] as $k => $v) {
                    if (isset($preselect[$k])) $dstslct[$k] = $v;
                    else                       $srcslct[$k] = $v;
                }
            }
            $selstr.= "<table border='0'>\n\t<tr>\n\t<td width='200'>\n";
            $selstr.= "\t\t".__('found elements')."<br />\n";
            $selstr.= "\t\t<select size='$size' name='".$this->name."srcs[]' multiple='multiple'>\n";
            foreach ($srcslct as $k => $v) {
                $selstr.= "\t\t\t<option value='".xss($k)."' title='".xss($fields['tisplay'][$k])."'>".xss($v)."</option>\n";
            }
            $selstr.= "\t\t</select>\n";
            $selstr.= "\t</td>\n\t<td width='50' valign='middle'>\n";
            $selstr.= "\t\t<input class='button' type='submit' name='movsrcdst' value='&rarr;' onclick=\"moveOption('".$this->name."srcs[]','".$this->name."dsts[]'); return false;\" /><br /><br />\n";
            $selstr.= "\t\t<input class='button' type='submit' name='movdstsrc' value='&larr;' onclick=\"moveOption('".$this->name."dsts[]','".$this->name."srcs[]'); return false;\" />\n";
            $selstr.= "\t</td>\n";
            $selstr.= "\t<td>&nbsp;</td>\n";
            $selstr.= "\t<td>&nbsp;</td>\n";
            $selstr.= "\t<td width='200'>\n";
            $selstr.= "\t\t".__('chosen elements')."<br />\n";
            $selstr.= "\t\t<select size='$size' name='".$this->name."dsts[]' multiple='multiple'>\n";
            foreach ($dstslct as $k => $v) {
                $selstr.= "\t\t\t<option value='$k' title='".xss($fields['tisplay'][$k])."'>".xss($v)."</option>\n";
            }
            $selstr.= "\t\t</select>\n";
            $selstr.= "\t</td>\n\t</tr>\n</table>\n";
        } else {
            $srcslct = array();
            $dstslct = '';
            if (!empty($fields['display'])) {
                foreach ($fields['display'] as $k => $v) {
                        if (!isset($preselect[$k])) $srcslct[$k] = $v;
                }
                if (isset($preselect)&&!empty($preselect)) {
                    $srcslct[0] = "-----";
                    foreach ($fields['display'] as $k => $v) {
                        if (isset($preselect[$k])) {
                            $srcslct[$k] = $v;
                            $dstslct[0] = $v;
                        }
                    }
                }
            }
            $selstr.= "<table border='0'>\n\t<tr valign='top'>\n\t<td width='200'>\n";
            $selstr.= "\t\t".__('found elements')."<br />\n";
            $selstr.= "\t\t<select size='$size' name='".$this->name."srcs[]' onclick=\"selectOne('".$this->name."');\">\n";
            foreach ($srcslct as $k => $v) {
                if (isset($preselect[$k])) {
                    $selstr.= "\t\t\t<option value='".xss($k)."' title='".xss($fields['tisplay'][$k])."' selected='selected'>".xss($v)."</option>\n";
                } else {
                    $selstr.= "\t\t\t<option value='".xss($k)."' title='".xss($fields['tisplay'][$k])."'>".xss($v)."</option>\n";
                }
            }
            $selstr.= "\t\t</select>\n";
            $selstr.= "\t</td>\n\t<td valign='top'>\n";
            $selstr.= "\t\t&nbsp;&nbsp;\n";
            $selstr.= "\t</td>\n\t<td width='200' class='contC' valign='top'>\n";
            $selstr.= "\t\t".__('chosen element')."<br />\n";
            $selstr.= "\t\t<input type='text' size='25' disabled=disabled name='".$this->name."dsts' value='".$dstslct[0]."' />\n";
            $selstr.= "\t</td>\n\t</tr>\n</table>\n";
        }

        return $selstr;
    }


    /**
     * Display selection in a separate window, including filters 
     *
     * @param $preselect  Preselection of objects 
     * @param $size       rowcount of multiple select box
     * @param $postaction form action to submit data to 
     * @access
     */
    function show_window($preselect, $size, $postaction="") {
        global $filters, $selektor_answer;

        if (empty($postaction)) $postaction = $_SERVER['SCRIPT_NAME'];
        $postaction = xss($postaction);
        $_SESSION['filters'] =& $filters;
        $sarr =& $filters[$this->name];
        $options = $this->sourcedata;
        if (!empty($sarr)) {
            foreach ($sarr as $k=>$v) {
                $options['where'][] = xss($v);
            }
        }
        if (!is_array($options['where'][0])) {
            $options['where'][0] = "(".$options['where'][0];
            $tmp_qry = '';
            if (isset($preselect) && is_array($preselect) && count($preselect) > 0) {
                // this is necessary to extract the keys (we can't apply (int) using implode)
                $tmp_ids = "";
                if (is_array($preselect) && count($preselect) > 0) {
                    
                    foreach ($preselect as $tmp_key => $tmp_value) {
                        $tmp_ids .= (int)$tmp_key.",";
                    }
                    $tmp_ids = substr($tmp_ids,0,-1);
                }
                $tmp_qry = " OR ".$options['ID']." IN (".$tmp_ids.")";
                
                // old fashion way
                //$tmp_qry = " OR ".$options['ID']." IN (".implode(",",array_keys($preselect)).")";
                
                
            }
            $options['where'][] = "1=1)".$tmp_qry;
            unset($tmp_qry);
        } else {
            $options['wherechosen'] = array_keys($preselect);
        }
        $fetch  = $this->datasource."fetch_fields";
        $fields = $fetch($options,$preselect);
        $sthis  = urlencode((serialize(array('this'=>$this, 'preselect'=>$preselect, 'size'=>$size))));

        if ($this->type == "multiple") {
            echo "
<form action='".$postaction."' method='post' onSubmit=\"selector_selectAll('".$this->name."dsts[]');\" name='finishForm'>
            ";
        } else {
            echo "
<form action='".$postaction."' method='post' name='finishForm'>
            ";
        }

        // hack: build get params from hidden fields for href links...
        if (!$_SESSION[$this->name]['javascript']) {
            $getprm = array_merge($this->hidden_fields, array('preselect'=>implode('-', array_keys($preselect))));
        } else {
            $getprm = $this->hidden_fields;
        }
        $dspl = $this->datasource."display_filters1";
        echo $dspl($options, $sthis, $this->name, $getprm);

        if ($fields['overflow'] === true) {
            $lim = $options['limit'] ? $options['limit'] : PHPR_FILTER_MAXHITS + count($preselect);
            if (!empty($this->sourcedata['filter'])) {
                echo __('too many hits').". ".__('please extend filter').".<br /><br />\n";
            } else {
                echo __('too many hits').".<br /><br />\n";
            }
            if (is_array($options['where'][0])) {
                $options['wherechosen'] = array_keys($preselect);
                foreach ($options['where'] as $k => $v) {
                    if (is_array($v)) {
                        $options['where'][$k][0] .= ' AND 1=0 ';
                    }
                }
            } else {
                // if no filters set, just display active objects
                unset($options['where']);
                $tmp_qry = '';
                if (isset($preselect) && is_array($preselect) && count($preselect) > 0) {
                    $tmp_qry = $options['ID']." IN ('".implode("','",array_keys($preselect))."')";
                }
                if ($tmp_qry <>'') {
                    $options['where'][0] = "( ". $tmp_qry." )";
                }
                unset($tmp_qry);
            }
            $selektor_answer = '';
            $fields2 = $fetch($options,$preselect);

            $tmp_fields = $fields;
            $fields = array();
            foreach($tmp_fields['display'] as $k => $v) {
                $fields['display'][$k] = $v;
            }
            foreach($tmp_fields['tisplay'] as $k => $v) {
                $fields['tisplay'][$k] = $v;
            }
            $fields['overflow'] = $tmp_fields['overflow'];

            foreach($fields2['display'] as $k => $v) {
                $fields['display'][$k] = $v;
            }
            foreach($fields2['tisplay'] as $k => $v) {
                $fields['tisplay'][$k] = $v;
            }
        }
        if (isset($selektor_answer) && $selektor_answer != '') {
            echo "<div class='answer'>$selektor_answer</div>\n";
        }
        echo $this->show_select($fields, $preselect, $size);

        // add all needed hidden fields
        foreach ($this->hidden_fields as $name => $value) {
            echo "<input type='hidden' name='".$name."' value='".xss($value)."' />\n";
        }
        unset($name, $value);
    }

    /** 
     * Returns selected results depend the type select 
     *
     * @access public
     */
    function get_chosen() {
        $result = array();
        if ($this->type == "multiple") {
            if (!empty($_POST[$this->name."dsts"])) {
                foreach($_POST[$this->name."dsts"] as $key => $val){
                    $result[xss($val)] = "on";
                }
            }
        } else {
            if (!empty($_POST[$this->name."srcs"])) {
                foreach($_POST[$this->name."srcs"] as $key => $val){
                    $result[xss($val)] = "on";
                }
            }
        }
        return $result;
    }
}

?>
