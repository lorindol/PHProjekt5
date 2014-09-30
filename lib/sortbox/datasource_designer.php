<?php
/**
 * Sortbox for module designer
 *
 * @package    sortbox
 * @module     main
 * @author     Gustavo Solt
 * @licence    GPL, see www.gnu.org/copyleft/gpl.html
 * @copyright  2000-2006 Mayflower GmbH www.mayflower.de
 * @version    $Id: 
 */

if (!defined('lib_included')) die('Please use index.php!');

/**
 * Class Sortbox_designer
 * Class for show the values and sort them
 *
 * @author Gustavo solt
 * @copyright (c) 2004 Mayflower GmbH
 * @package PHProjekt
 * @access public
 */
class Sortbox_designer {

    /** 
     * Name: Formular identifier
     *
     * @access private
     */
    var $name = 'desginer';

    /**
     * Array containing options for this class
     * 'field_to_sort'   - Wich field want sort
     * 'extra_value'     - Extra parameter (misc)
     */
    var $options = array ();

    /** 
     * Constructor
     *
     * @param $options    options for this class
     * @access public
     */
    function Sortbox_designer ($options) {
        $this->options = $options;
    }
                                
    /** 
     * fetch_fields() - Query the data source 
     *
     * @access public
     */
    function fetch_fields($preselect) {

        $query = "SELECT ID, form_name, ".$this->options['field_to_sort']."
                    FROM ".DB_PREFIX."db_manager
                    WHERE db_table = '".$this->options['extra_value']."'
                      AND db_inactive = 0
                    ORDER BY ".$this->options['field_to_sort']." ASC";
        $result = db_query($query) or db_die();

        $hits = array(  'display'=>array(),
                        'tisplay'=>array());

        $create_preselect = false;
        if (empty($preselect)) {
            $create_preselect = true;
        }

        while ($row = db_fetch_row($result)) {
            $hits['display'][$row[0]] = enable_vars($row[1]);
            $hits['tisplay'][$row[0]] = enable_vars($row[1]);
            if ($create_preselect) {
                if (intval($row[2]) != 0) {
                    $preselect[$row[0]] = $row[0];
                }
            }
        }
        array_unique($preselect);

        return array($hits,$preselect);
    }

    /** 
     * save_fields() - Update the data source 
     *
     * @param selected - Array containing selected options
     * @access public
     */
    function save_fields($selected) {

        if (is_array($selected)) {
            // Set all to 0
            $query = "UPDATE ".DB_PREFIX."db_manager
                         SET ".$this->options['field_to_sort']." = 0
                       WHERE db_table = '".$this->options['extra_value']."'";
            $result = db_query($query) or db_die();

            // Set the pos for the selected
            $pos = 1;
            foreach($selected as $tmp => $id) {
                $query = "UPDATE ".DB_PREFIX."db_manager
                             SET ".$this->options['field_to_sort']." = ".$pos."
                            WHERE ID = ".(int)intval($id)." ";
                $result = db_query($query) or db_die();
                $pos++;
            }
        }
    }
}
?>
