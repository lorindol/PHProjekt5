<?php
/**
 * Navigation in modules
 *
 * @package    	lib
 * @subpackage 	main
 * @author     	Nina Schmitt
 * @licence     GPL, see www.gnu.org/copyleft/gpl.html
 * @copyright  	2000-2006 Mayflower GmbH www.mayflower.de
 * @version
 */

// check whether the lib has been included - authentication!
if (!defined("lib_included")) die("Please use index.php!");

/**
 * Class for manage the navigation links
 *
 * @package  lib
 */
class PHProjekt_Module_Navigation {

    var $output 						= '';
    var $config 						= array();
    var $tabs 							= array();
    var $buttons 						= array();
    var $actor_access              		= '';
    var $application_mode          	= '';
    var $application_module        	= '';
    var $application_module_ID     = 0;
    var $all_modules               		= array();
    var $all_views                 		= array();
    var $contextmenu               		='';

    /**
    * @param array	$tabs	 			- Array with the tabs data
    * @param array	$buttons 			- Array with the buttons data
    * @param object	$contextmenu	- Exists contextmenu
    * @return void
    */
    function PHProjekt_Module_Navigation($tabs,$buttons, $contextmenu='this'){
        // some application paras
        $this->tabs                     			= $tabs;
        $this->buttons                   		= $buttons;
        $this->actor_access              		= $GLOBALS['user_type'];
        $this->application_mode       		= $GLOBALS['mode'];
        $this->application_module        	= $GLOBALS['module'];
        $this->application_module_ID    	= get_application_module_ID($this->application_module);
        $this->all_modules               		= $_SESSION['main_modules_data'];
        $this->all_views                 		= $_SESSION['view_data'];
        $this->contextmenu               		= $contextmenu;
    }

    /**
     * This function draws the sub navigation
     *
     * @param void
     * @return void
     */
    function get_output(){
       $this -> get_buttons();
       $this->output = get_module_tabs($this->tabs,$this->buttons, $this->contextmenu);
       return $this->output;
    }

    /**
     * This function return the button for each module
     *
     * @param void
     * @return void
     */
    function get_buttons(){
        $buttons= $this->buttons;
        //new button always if user has write access!
        if(check_role($this->application_module)==2){
            // No repeat new button
            $found = 0;
            foreach ($buttons as $tmp => $data) {
                if ($data['text'] == __('New')) {
                    $found = 1;
                }
            }
            if (!$found) {
                $this->application_module;
                $href = "../%s&amp;mode=forms&amp;action=new".$sid;
                $href= sprintf($href, $this->all_modules[$this->application_module][3]);
                $this->buttons[] = array('type' => 'link', 'href' => $href, 'text' => __('New'), 'active' => false);
            }
        }
        if (isset($this->all_views[$this->application_module_ID])){
            foreach ($this->all_views[$this->application_module_ID] as $button_name=>$button) {
                $href='../'.$button[3];
                if(check_role($button_name)>=1) $this->buttons[] = array('type' => 'link', 'href' => $href, 'text' => enable_vars($button[1]), 'active' => false);
            }
        }
    }
}
?>
