<?php
/**
 * Sortbox main class
 *
 * @package    sortbox
 * @module     main
 * @author     Gustavo solt
 * @licence    GPL, see www.gnu.org/copyleft/gpl.html
 * @copyright  2000-2006 Mayflower GmbH www.mayflower.de
 * @version    $Id: 
 */
if (!defined('lib_included')) die('Please use index.php!');

/**
 * Class PHProjektSortbox
 * Display and selection of a group of objects and sort them
 *
 * @author Gustavo solt
 * @copyright (c) 2004 Mayflower GmbH
 * @package PHProjekt
 * @access public
 */
class PHProjektSortbox {
    /** 
     * Type: single (only sort) or double (select and sort)
     *
     * @access public
     */
    var $type;
    
    /**
     * Datasource: Source which delivers the objects, mainly user and contacts table. 
     * Based on the data source a file is includet that contains the source specific functions.
     * Every data source has to provide documentation of the needed fields in sourcedata, 
     * as well as the functions
     * fetch_fields(); save_fields();
     *
     * @access private
     */
    var $datasource;
    
    /** 
     * Name: Formular identifier
     *
     * @access private
     */
    var $name = 'sortbox_';
    
    /**
     * Array containing names and values of the hidden fields that are needed in the form. 
     * Examples are mode, view and the like 
     *
     * @access private
     * @var array ( formelementNAME => formElementValue, ... )
     */
    var $hidden_fields = array();

    /** 
     * Constructor
     *
     * @param $options    Array with options
     *    'field_to_sort' - Fiel of the db to sort
     *    'extra_value'   - extra field for the select
     *    'datasource'    - Source for get data
     *    'type'          - Type of the sortbox single/double
     * @access public
     */
    function PHProjektSortbox($options) {

        if ($options['datasource'] != NULL && in_array($options['datasource'], array('designer','project'))){
            require_once(dirname(__FILE__).DIRECTORY_SEPARATOR."datasource_".$options['datasource'].".php");
            $class = "Sortbox_".$options['datasource'];
            $this->datasource = new $class($options);
            $this->name = $this->name.$options['datasource'];
        } else {
            die(__('data source missing'));
        }

        if ($options['type'] == "single" || $options['type'] == "double") {
            $this->type = $options['type'];
        } else {
            die(__('Invalid type').' '.$options['type']);
        }
    }

    /** 
     * Create the select boxes 
     *
     * @param $fields    Array of fields 
     * @param $preselect Preselection of active objects 
     * @param $size      rowcount of multiple select box 
     * @access
     */
    function show_select($fields, $preselect, $size) {
        $selstr = '';

        if ($this->type == "double") {
            $srcslct = array();
            $dstslct = array();
            if (!empty($fields['display'])) {
                foreach ($fields['display'] as $k => $v) {
                    if (isset($preselect[$k])) $dstslct[$k] = $v;
                    else                       $srcslct[$k] = $v;
                }
            }
            // Keep the order
            $dstslct = $this->order($dstslct);
           
            $selstr.= "<table border='0'>\n\t<tr>\n\t<td width='200'>\n";
            $selstr.= "\t\t".__('found elements')."<br />\n";
            $selstr.= "\t\t<select size='$size' name='sortbox_srcs[]' multiple='multiple'>\n";
            foreach ($srcslct as $k => $v) {
                $selstr.= "\t\t\t<option value='".xss($k)."' title='".xss($fields['tisplay'][$k])."'>".xss($v)."</option>\n";
            }
            $selstr.= "\t\t</select>\n";
            $selstr.= "\t</td>\n\t<td width='50px' valign='middle'>\n";
            $selstr.= "\t\t<input class='button' type='submit' name='movsrcdst' value='&rarr;' onclick=\"moveOption('sortbox_srcs[]','sortbox_dsts[]'); return false;\" /><br /><br />\n";
            $selstr.= "\t\t<input class='button' type='submit' name='movdstsrc' value='&larr;' onclick=\"moveOption('sortbox_dsts[]','sortbox_srcs[]'); return false;\" />\n";
            $selstr.= "\t</td>\n\t<td width='200'>\n";
            $selstr.= "\t\t".__('chosen elements')."<br />\n";
            $selstr.= "\t\t<select size='$size' name='sortbox_dsts[]' multiple='multiple'>\n";
            foreach ($dstslct as $k => $v) {
                $selstr.= "\t\t\t<option value='".xss($k)."' title='".xss($fields['tisplay'][$k])."'>".xss($v)."</option>\n";
            }
            $selstr.= "\t\t</select>\n";
            $selstr.= "\t</td>\n\t<td valign='middle'>\n";
            $selstr.= "\t\t<input class='button' type='submit' name='movdownup' value='&uarr;' onclick=\"movePosOption('sortbox_dsts[]','up'); return false;\" /><br /><br />\n";
            $selstr.= "\t\t<input class='button' type='submit' name='movupdown' value='&darr;' onclick=\"movePosOption('sortbox_dsts[]','down'); return false;\" />\n";
            $selstr.= "\t</td>\n\t</tr>\n</table>\n";
        } else {
            $dstslct = array();
            if (!empty($fields['display'])) {
                foreach ($fields['display'] as $k => $v) {
                    $dstslct[$k] = $v;
                }
            }
            $selstr.= "<table border='0'>\n\t<tr valign='top'>\n\t<td width='200'>\n";
            $selstr.= "\t\t".__('found elements')."<br />\n";
            $selstr.= "\t\t<select size='$size' name='sortbox_dsts[]' multiple='multiple'>\n";
            foreach ($dstslct as $k => $v) {
                $selstr.= "\t\t\t<option value='".xss($k)."' title='".xss($fields['tisplay'][$k])."'>".xss($v)."</option>\n";
            }
            $selstr.= "\t\t</select>\n";
            $selstr.= "\t</td>\n\t<td valign='top'>\n";
            $selstr.= "\t\t<input class='button' type='submit' name='movsrcdst' value='UP' onclick=\"movePosOption('sortbox_dsts[]','up'); return false;\" style='margin-top:20px' /><br /><br />\n";
            $selstr.= "\t\t<input class='button' type='submit' name='movdstsrc' value='DOWN' onclick=\"movePosOption('sortbox_dsts[]','down'); return false;\" />\n";
            $selstr.= "\t</td>\n\t</tr>\n</table>\n";
        }

        return $selstr;
    }


    /**
     * Display selection in a separate window
     *
     * @param $size       rowcount of multiple select box
     * @access
     */
    function show_window($size) {

        $this->hidden_fields = array_merge($this->hidden_fields, 
                        array('field_to_sort' => $this->datasource->options['field_to_sort'],
                              'extra_value'   => $this->datasource->options['extra_value'],
                              'datasource'    => $this->datasource->options['datasource'],
                              'type'          => $this->datasource->options['type'],
                              'action'        => 'sort_save'));

        $postaction = $_SERVER['SCRIPT_NAME'];

        $sort_name = $this->datasource_name;
        $preselect = array();

        // Preselected items without javascript
        if (    (isset($_POST['movsrcdst'])) ||
                (isset($_POST['movdstsrc'])) || 
                (isset($_POST['movdownup'])) || 
                (isset($_POST['movupdown'])) ) {

            $_SESSION[$this->name]['javascript'] = false;
            // Add: the entries from the left select box are valid and needs to be added to the selected entries in the session
            if (isset($_POST['movsrcdst']) && isset($_POST['sortbox_srcs'])) {
                foreach ($_POST['sortbox_srcs'] as $tmp_val) {
                    $_SESSION[$this->name]['data'][$tmp_val] = $tmp_val;
                }
            }
            unset($tmp_val);
            
            // Remove: Entries from the right box should be removed 
            if (isset($_POST['movdstsrc']) && isset($_POST['sortbox_dsts'])) {
                foreach ($_POST['sortbox_dsts'] as $tmp_val) {
                    unset($_SESSION[$this->name]['data'][$tmp_val]);
                }
            }
            unset($tmp_val);

            // write current data back into the session
            $preselect = $_SESSION[$this->name]['data'];

        } else if (isset($_SESSION[$this->name]['data'])) {
            $preselect = $_SESSION[$this->name]['data'];
        } else if (isset($_POST['sortbox_dsts'])) {
            $_SESSION[$this->name]['javascript'] = true;
            foreach (xss_array($_POST['sortbox_dsts']) as $k => $v) {
                $preselect[$v] = $v;
            }
        }

        // Sorted items without javascript
        if ((isset($_POST['movupdown'])) || (isset($_POST['movdownup']))) {

            // Sort preselected - Up
            if (isset($_POST['movdownup']) && isset($_POST['sortbox_dsts'])) {
                $count = 0;
                $tmp_array = array();
                foreach($preselect as $tmp_val => $tmp_on) {
                    if ($count == 0) {
                        $save_id = $tmp_val;
                    } else if ($tmp_val == $_POST['sortbox_dsts'][0]) {
                        $tmp_array[$tmp_val] = $tmp_val;
                        $tmp_array[$save_id] = $save_id;
                    } else {
                        $tmp_array[$save_id] = $save_id;
                        $save_id = $tmp_val;
                    }
                    $count++;
                }
                $tmp_array[$save_id] = $save_id;

                // write current data back into the session
                $preselect                     = $tmp_array;
                $_SESSION[$this->name]['data'] = $tmp_array;
        
                unset($tmp_val);
                unset($save_id);
                unset($tmp_array);
            }

            // Sort preselected - Down
            if (isset($_POST['movupdown']) && isset($_POST['sortbox_dsts'])) {
                $tmp_array = array();
                foreach($preselect as $tmp_val => $tmp_on) {
                    if ($tmp_val == $_POST['sortbox_dsts'][0]) {
                        $save_id = $tmp_val;
                    } else {
                        $tmp_array[$tmp_val] = $tmp_val;
                        if ($save_id != '') {
                            $tmp_array[$save_id] = $save_id;
                        }
                        $save_id = $tmp_val;
                    }
                    $count++;
                }
                $tmp_array[$save_id] = $save_id;

                // write current data back into the session
                $preselect                     = $tmp_array;
                $_SESSION[$this->name]['data'] = $tmp_array;
        
                unset($tmp_val);
                unset($save_id);
                unset($tmp_array);
            }
        }

        list($fields,$preselect) = $this->datasource->fetch_fields($preselect);
        if (    (isset($_POST['movsrcdst'])) ||
                (isset($_POST['movdstsrc'])) || 
                (isset($_POST['movdownup'])) || 
                (isset($_POST['movupdown'])) ) {
            $_SESSION[$this->name]['data'] = $preselect;
        }

        echo "<form action='".$postaction."' method='post' onSubmit=\"selector_selectAll('sortbox_dsts[]'); \" name='finishForm'><fieldset><legend>".__('Sorting')."</legend>";

        echo $this->show_select($fields, $preselect, $size);

        // add all needed hidden fields
        foreach ($this->hidden_fields as $name => $value) {
            echo "<input type='hidden' name='".$name."' value='".xss($value)."' />\n";
        }
        
        echo get_buttons(array(array("type" => "submit", "name" => 'save', "value" => __('Apply'))));
        echo get_buttons(array(array("type" => "button", "name" => 'close', "value" => __('Close window'), "onclick" => 'javascript:ReloadParentAndClose();')));
        echo "</fieldset></form>\n";
        unset($name, $value);
    }

    function order($array) {
        $pos = 0;
        $array_pos = array();
        
        // Without javascript
        if (isset($_SESSION[$this->name]['data']) && is_array($_SESSION[$this->name]['data'])) {
            foreach ($_SESSION[$this->name]['data'] as $tmp_id => $tmp_val) {
                $array_pos[$pos] = $tmp_id;
                $pos++;
            }
        } else {
            foreach ($array as $tmp_id => $tmp_val) {
                $array_pos[$pos] = $tmp_id;
                $pos++;
            }
        }

        // Save the sort
        $tmp_array = array();
        foreach ($array_pos as $pos => $tmp_val) {
            foreach ($array as $tmp_id => $tmp_text) {
                if ($tmp_id == $tmp_id) {
                    $tmp_array[$tmp_id] = $array[$tmp_id];
                }
            }
        }

        return $tmp_array;
    }
}
?>
