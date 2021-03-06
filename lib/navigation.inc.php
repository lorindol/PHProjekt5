<?php
/**
 * Navigation class
 *
 * @package    	lib
 * @subpackage 	main
 * @author     	Alexander Haslberger, $Author: gustavo $
 * @licence     GPL, see www.gnu.org/copyleft/gpl.html
 * @copyright  	2000-2006 Mayflower GmbH www.mayflower.de
 * @version    	$Id: navigation.inc.php,v 1.83 2007-10-10 23:14:04 gustavo Exp $
 */

// check whether the lib has been included - authentication!
if (!defined("lib_included")) die("Please use index.php!");

/**
 * Class for manage the navigations links
 *
 * @package lib
 */
class PHProjekt_Navigation {

    var $output = '';
    var $module_entries = array();
    var $addons_entries = array();
    var $control_entries = array();
    var $controls = array();
    var $all_modules = array();
    var $config = array();
    var $skin = 'default';
    var $sub_nav_entries = array();
    var $sub_nav = array();
    var $sub_modules = array();

    /**
     * Construct function
     *
     * @param void
     * @return void
     */
    function PHProjekt_Navigation(){
        // some application paras
        $this->actor_id                  			= isset($GLOBALS['user_ID']) ? qss($GLOBALS['user_ID']) : '';
        $this->actor_user_group          		= isset($GLOBALS['user_group']) ? qss($GLOBALS['user_group']) : '';
        $this->actor_access              			= isset($GLOBALS['user_type']) ? qss($GLOBALS['user_type']) : '';
        $this->application_mode          		= isset($GLOBALS['mode']) ? qss($GLOBALS['mode']) : '';
        $this->application_mode2         		= isset($GLOBALS['mode2']) ? qss($GLOBALS['mode2']) : '';
        $this->application_action        		= isset($GLOBALS['action']) ? qss($GLOBALS['action']) : '';
        $this->application_sure          		= isset($GLOBALS['sure']) ? qss($GLOBALS['sure']) : '';
        $this->application_view          		= isset($GLOBALS['view']) ? qss($GLOBALS['view']) : '';
        $this->application_module        		= isset($GLOBALS['module']) ? qss($GLOBALS['module']) : '';
        $this->application_language      		= isset($GLOBALS['langua']) ? qss($GLOBALS['langua']) : '';
        $this->application_nav_searchbox 	= isset($GLOBALS['nav_searchbox']) ? qss($GLOBALS['nav_searchbox']) : '';
        $this->application_addon         		= isset($GLOBALS['addon']) ? qss($_REQUEST['addon']) : '';
    }

    /**
     * Show menu links
     *
     * @param void
     * @return void
     */
    function render() {
        // import layout config data
        $this->set_config_data();
        // add global
        uasort($this->controls, array('PHProjekt_Navigation', 'sort_entries'));
        $this->add_controls();
        $this->render_controls();
        // add modules
        $this->add_modules();
        uasort($this->module_entries, array('PHProjekt_Navigation', 'sort_entries'));
        $this->render_modules();
        // add addons
        $this->add_addons();
        $this->render_addons();
    }

    /**
     * Add module links
     *
     * @param void
     * @return void
     */
    function add_modules() {
        foreach($this->all_modules as $mod_name=>$module){
            if(check_role($mod_name)>=1){
                $this->module_entries[$mod_name] = array($module[0], enable_vars($module[1]), $module[2], $module[3]);
            }
        }
    }

    /**
     * Show module links
     *
     * @param void
     * @return string	 	HTML output
     */
    function render_modules() {
        $this->output .= $this->render_headline('Modules');
        $this->output .= "\n<ul>";
        $this->numeration['modules'] = 0;

        foreach($this->module_entries as $k => $v){

            // special case for timecard
            if ($k == 'timecard') {
                if (check_role("timecard") < 2) {
                    continue;
                }
            }

            $class = ($this->is_active($k)) ? ' class="active"' : '';
            // contacts => Two modules in one

            switch ($v2) {
                case 2:
                    $str  = '<li';
                    $str .= ($class != '') ? $class.'>' : '>';
                    $str .= "\n\t";
                    $str .= '<a href="../%s%s" title="%s">';
                    $str .= '<img src="../layout/%s/img/%s.png" alt="%s" width="20" />';
                    $str .= '</a></li>%s';
                    $this->output .= sprintf($str, $v[3],SID, strip_tags($v[1]), $this->skin, $k, $v[1], "\n");
                    break;
                case 3:
                    $str  = '<li';
                    $str .= ($class != '') ? $class.'>' : '>';
                    $str .= "\n\t";
                    $str .= '<a%s href="../%s%s" title="%s">';
                    $str .= '<img src="../layout/%s/img/%s.png" alt="%s" width="20" /> %s';
                    $str .= '</a></li>%s';
                    $this->output .= sprintf($str, $class, $v[3],SID, strip_tags($v[1]), $this->skin, $k, $v[1], $v[1], "\n");
                default:
                    // add text
                    $str  = '<li';
                    $str .= ($class != '') ? $class.'>' : '>';
                    $str .= "\n\t";
                    $str .= '<a%s href="../%s%s" title="%s">%s';
                    $str .= '</a></li>%s';
                    $this->output .= sprintf($str, $class, $v[3],  SID, strip_tags($v[1]), $v[1], "\n");
                    break;
            }
        }
        $this->output .= "\n</ul>";
    }

    /**
     * Add addons links
     *
     * @param void
     * @return void
     */
    function add_addons()  {
    	
        // check if the addons are rendered
        if (isset($_SESSION['addons']) && is_array($_SESSION['addons'])) {
            // use the addond found on previous session
            $this->addons_entries = $_SESSION['addons'];
        }
        else {
            // check whether the addon directory exists at all
            $addons_dir = dirname(__FILE__).'/../addons/';
            if(file_exists($addons_dir)){
                // open the addon directory
                $fp = opendir($addons_dir);
                // read all objects in this dir, set the count of found addons to zero ...
                while($file = readdir($fp)) {
                    // but exclude links, index files, system files etc.
                    if(is_dir($addons_dir.$file) and $file != 'CVS' and
                        !ereg('^[_.]', $file) ){
                        $this->addons_entries[] = $file;
                    }
                }
                closedir($fp);

                // Save addons found on session
                $_SESSION['addons'] = $this->addons_entries;
            }
        }
    }

    /**
     * Show addons links
     *
     * @param void
     * @return string 		HTML output
     */
    function render_addons() {
        if(!$this->addons_entries){
            return '';
        }
        $this->output .= $this->render_headline('Addons');
        $this->output .= "\n<ul>";
        $this->numeration['addons'] = 0;
        foreach($this->addons_entries as $addon){
            $selected = ($this->is_active($addon)) ? 'Selected' : '';
            $sid = "&amp;".SID;
            $str  = '<li><a href="../addons/addon.php?addon=%s%s" title="%s" target="_top">';
            $str .= '<span class="navLink%s">%s</span></a></li>%s';
            $this->output .= sprintf($str, $addon, $sid, ucfirst($addon), $selected, ucfirst($addon), "\n");
        }
        $this->output .= "\n</ul>";
    }

    /**
     * Add controls links
     *
     * @param void
     * @return void
     */
    function add_controls(){
        foreach($this->controls as $control){
            // activated ?
            if(!$control[2]){
                continue;
            }
            switch($control[1]){
                // logged as
                case 'logged_as':
                    $this->control_entries[] = __('logged in as').':<br />'.str_replace(',', ' ', slookup('users', 'vorname,nachname', 'ID', $this->actor_id,1));
                    break;
                // search field
                case 'search_field':
                    $this->control_entries[] =
                        '<form action="../search/search.php" target="_top">'
                        .'<input type="hidden" name="csrftoken" value="'.make_csrftoken().'" />'
                        ."\n\t"
                        .'<input type="text" name="searchterm" id="searchterm" title="'.__('Search').'" value="'.__('Search').'" onfocus="this.value=\'\'" />'
                        ."\n\t"
                        .'<input type="submit" class="nav_button" value="&#187;" />'
                        ."\n\t"
                        .'<input type="hidden" name="searchformcount" value="" />'
                        ."\n\t"
                        .'<input type="hidden" name="module" value="search" />'
                        ."\n\t"
                        .'<input type="hidden" name="gebiet" value="all" />'
                        ."\n\t"
                        .'</form>';
                    break;
                // group box
                case 'group_box':
                    $group_box = $this->get_group_box();
                    if(strlen($group_box)){
                        $this->control_entries[] = $group_box;
                    }
                    break;
                // settings
                case 'settings':
                    $class = ($this->is_active('settings')) ? ' class="active"' : '';
                    $this->control_entries[] = '<a'.$class.' href="../settings/settings.php?'.SID.'" title="'.strip_tags(__('Settings')).'" target="_top">'.__('Settings').'</a>';
                    break;
                // help
                case 'help':
                    // now the help routine
                    if(ereg($this->application_language, "de|en|es|nl|fr|tr|zh|fi")){
                        $help = __('Help');
                    }
                    else{
                        $help = __('?');
                    }
                    $this->control_entries[] = '<a href="'.get_helplink().'" title="'.strip_tags($help).'" target="_blank">'.$help.'</a>';
                    break;
                // admin
                case 'admin':
                    if ($this->actor_access==3) {
                        $selected = ($this->is_active('admin')) ? 'Selected' : '';
                        $this->control_entries[] = '<a href="../index.php?module=admin&amp;'.SID.'" title="Admin" target="_top">
                                                    <span class="navLink'.$selected.'">Admin</span></a>';
                    }
                    break;
                // logout
                case 'logout':
                    $this->control_entries[] = '<a href="../index.php?module=logout&amp;'.SID.'" title="'.strip_tags(__('Logout')).'" target="_top">'.__('Logout').'</a>';
                    break;
                // timecard buttons
                case 'timecard_buttons':
                    if (PHPR_TIMECARD and check_role('timecard') > 1) {
                        $mode    = $this->application_mode;
                        $action  = $this->application_action;
                        $sure    = $this->application_sure;
                        $view    = $this->application_view;
                        $today1 = date('Y-m-d', mktime(date('H') + PHPR_TIMEZONE, date('i'), date('s'), date('m'), date('d'), date('Y')));
                        $result1 = db_query("SELECT ID
                                               FROM ".DB_PREFIX."timecard
                                              WHERE datum = '$today1'
                                                AND (ende IS NULL)
                                                AND users = ".$this->actor_id) or db_die();
                        $row1 = db_fetch_row($result1);
                        // buttons for 'come' and 'leave', alternate display
                        $just_timed_in  = ($mode == 'data' && $action == '1' && $sure == '1');
                        $just_timed_out = ($mode == 'data' && $action != '1' && $sure == '1');

                        if (($row1[0] > 0 && !$just_timed_out) || $just_timed_in) {
                            $this->control_entries[] = get_buttons(array(array('type' => 'link', 'href' => '../timecard/timecard.php?mode=data&amp;view='.$view.'&amp;action=&amp;sure=1&amp;'.SID, 'text' => __('End'), 'stopwatch' => 'started')));
                        }
                        else {
                            $this->control_entries[] = get_buttons(array(array('type' => 'link', 'href' => '../timecard/timecard.php?mode=data&amp;view='.$view.'&amp;action=1&amp;sure=1&amp;'.SID, 'text' => __('Begin'), 'stopwatch' => 'stopped')));
                        }
                        // Projektzuweisung
                        $resultq = db_query("SELECT ID, div1, h, m
                                               FROM ".DB_PREFIX."timeproj
                                              WHERE users = ".(int)$this->actor_id."
                                                AND (div1 LIKE '".date("Ym")."%')") or db_die();
                        $rowq = db_fetch_row($resultq);
                        // buttons for 'come' and 'leave', alternate display
                        $just_clocked_out = ($mode == 'data' && $action == 'clock_out');
                        if ($rowq[0] > 0 && !$just_clocked_out && !$just_timed_out) {
                            $this->control_entries[] = get_buttons(array(array('type' => 'link', 'href' => '../timecard/timecard.php?mode=data&amp;view='.$view.'&amp;action=clock_out&amp;'.SID, 'text' => __('stop stop watch'), 'stopwatch' => 'started')));
                        }
                        else {
                            $this->control_entries[] = get_buttons(array(array('type' => 'link', 'href' => '../timecard/timecard.php?mode=books&amp;view='.$view.'&amp;action=clockin&amp;'.SID, 'text' => str_replace('-', '', __('Project stop watch')), 'stopwatch' => 'stopped')));
                        }
                    }
                    break;
            }
        }
    }

    /**
     *  Show controls links
     *
     * @param void
     * @return string 		HTML output
     */
    function render_controls() {
        if(!$this->control_entries){
            return '';
        }
        $this->output .= $this->render_headline(__('Controls'));
        $this->output .= "\n<ul>";
        $this->numeration['controls'] = 0;

        foreach($this->control_entries as $control){
            $control = "\n\t".$control;
            $str  = '<li%s>%s</li>%s';
            $this->output .= sprintf($str, (strstr($control, 'class="active"') ? ' class="active"' : ''), $control, "\n");
        }
        $this->output .= "\n</ul>";
    }

    /**
     * Show headline
     *
     * @param void
     * @return string		HTML output
     */
    function render_headline($headline){
        if(isset($this->config['show_headlines']) && $this->config['show_headlines']){
            return "<h4>".__($headline)."</h4>";
        }
        return '';
    }

    /**
     * Sort links
     *
     * @param string	$a 		- First element
     * @param string	$b 	- Second element
     * @return string			- Sorted element
     */
    function sort_entries($a, $b){
        if($a[0] == $b[0]){
            return 0;
        }
        return ($a[0] < $b[0]) ? -1 : 1;
    }

    /**
     * This method will echo the navigation bar
     *
     * @uses PHPR_INSTALL_DIR to determine the path where is necessary to get the logo
     * @uses PHPR_LOGO to check if is set any logo
     * @param void
     * @return void
    */
    function draw() {
        // check if there is set a different logo on config.inc.php
        if (defined('PHPR_LOGO') && file_exists(PHPR_INSTALL_DIR.PHPR_LOGO)) {
            $logo_src = "/".PHPR_INSTALL_DIR.PHPR_LOGO;
        }
        else {
            $logo_src = '/'.PHPR_INSTALL_DIR.'layout/'.$this->skin.'/img/logo.png';
        }
        echo "\n\n<!-- START NAVIGATION -->\n";
        echo '<div id="global-navigation">';
        #echo '<img id="logo" src="'.$logo_src.'" alt=""  title=" PHProjekt '.PHPR_VERSION.' - '.str_replace(',', ' ', slookup('users', 'vorname,nachname', 'ID', $this->actor_id)).' "/><br class="navbr" /><br class="navbr" />';
        echo "\n";
        #echo '<ul>';
        echo $this->output;
        #echo "\n";
        #echo '</ul>';
        #echo "\n";
        echo '</div>';
        echo "\n<!-- END NAVIGATION -->\n\n";
    }

    /**
     * Get and show the group select
     *
     * @param void
     * @return void
     */
    function get_group_box() {
        // determine whether this is the first or second from onthis page
        // -> must know this to get the onchange-js properly working
        if ($this->application_nav_searchbox) $form_nr = 1;
        else                $form_nr = '0';
        $out = '';
        $groups = $_SESSION['user_all_groups'];
        if (count($groups)>0){
            $optgrplabel = __('Usergroup') . ':';
            $out .=
            "\t"
            .'<form name="grsel" action="../index.php" method="post">'
            .' <input type="hidden" name="csrftoken" value="'.make_csrftoken().'" />'
            ."\n\t"
            .'<input type="hidden" name="module" value="'.$this->application_module.'" />'
            ."\n\t"
            .'<input type="hidden" name="mode2"  value="'.$this->application_mode2.'" />'
            ."\n\t"
            .(SID ? '<input type="hidden" name="'.session_name().'" value="'.session_id().'" />'."\n\t" : '')
            .'<select name="change_group" id="change_group" onchange="document.grsel.submit();">'
            .'<optgroup label="'.$optgrplabel.'">';
            foreach ($groups as $key =>$item) {
                $out .=
                "\n\t"
                .'<option value="'.$key.'"'.
                ($this->actor_user_group == $key ? ' selected="selected"' : '').
                ' title="'.$item['name'].'">'.$item['kurz']."</option>";
            }

            $out .= "\n</optgroup>\n\t</select>";
            $out .= "\n\t".get_go_button('nav_button', 'button', '', '&#187;');
            $out .= "\n\t</form>";
        }
        return $out;
    }

    /**
     * Return if the module is active
     *
     * @param string 	$k		- Module name
     * @return boolean			True if is active
     */
    function is_active($k){
        // Normal modules
        if ($this->application_module == $k)
            return true;
        // Submodules
         else if($this->sub_modules[$this->application_module] == $k)
            return true;
        // Addons
        else if (isset($this->application_addon) && $this->application_addon == $k)
            return true;
        // Fix for members in module contacts
        else if ($this->application_module == "members" && $k == "contacts")
            return true;
        // Other values
        else
            return false;
    }

    /**
     * Set Skin
     *
     * @param string	$skin	- Skin to use
     * @return void
     */
    function set_skin($skin = 'default'){
        $file = dirname(__FILE__).'/../layout/'.$skin.'/'.$skin.'.inc.php';
        if(file_exists($file)){
            $this->skin = $skin;
        }
        else{
            $this->skin = 'default';
        }
    }

    /**
     * Set the config values
     *
     * @param void
     * @return void
     */
    function set_config_data(){
        define('FILE_SKIN',dirname(__FILE__).'/../layout/'.$this->skin.'/'.$this->skin.'.inc.php');
        include(FILE_SKIN);
        include_once(LIB_PATH.'/dbman_lib.inc.php');
        $this->controls = $controls;
        $this->config = $config;
        $this->all_modules = $_SESSION['main_modules_data'];
        $this->sub_nav = $_SESSION['sub_nav'];
        $this->sub_modules = $_SESSION['sub_modules'];
    }

    /**
     * This function draws the sub navigation
     *
     * @param void
     * @return void
     */
    function draw_sub_nav(){
        $this -> organize_sub_nav();
        echo get_tabs_area($this ->sub_nav_entries);
    }

    /**
     * @param void
     * @return void
     */
    function organize_sub_nav(){
        $parent = $this->sub_modules[$this->application_module] ? $this->sub_modules[$this->application_module] : $this->application_module;
        if (is_array($this->sub_nav[$parent])){
            $parent_tab= $this->module_entries[$parent];
            $href= '../'.$parent_tab[3];
                $active = $this->application_module == $parent ? true : false;
                if(!$active) $this ->sub_nav_entries[] = array('href' => $href, 'id' => 'tab'.$i, 'target' => '_self', 'text' =>$parent_tab[1] , 'position' => 'left',  'active' => $active);
                    $i=0;
            foreach ($this->sub_nav[$parent] as $mod =>$modules){
                $i++;
                $href= '../'.$modules[3];
                $active = $this->application_module == $mod ? true : false;
                if (!$active and check_role($mod)>=1) $this ->sub_nav_entries[] = array('href' => $href, 'id' => 'tab'.$i, 'target' => '_self', 'text' => enable_vars($modules[1]), 'position' => 'left',  'active' => $active);
            }
        }
    }
}

$nav = new PHProjekt_Navigation();
$nav->set_skin($skin);
$nav->render();
$nav->draw();
if(SUBNAV==true)$nav->draw_sub_nav();
?>
