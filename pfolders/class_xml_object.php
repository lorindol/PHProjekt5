<?php
/**
 * Class to represeeent the structure and the content of an XML-File
 * This class was developed to represent the XML content of a WebDAV-REPORT
 * 
 * @author Michel Hartmann (michel.hartmann@mayflower.de) 
 */
require_once(WEBDAV_PATH.'class_xml_filter.php');

class xml_object {
	var $name = null;
	var $parent = null;
	var $attrs = array();
	var $childs = array();
	var $data = null;
	var $path = null;

	/**
	 * Create a new XML-Element and register it at his parent object
	 * (Do not use this function. Instead use xml_object::factory() to create
	 * new objects)
	 *
	 * @param String $name         Tagname of the Element
	 * @param xml_object $parent   Parent Elemement
	 * @param array $attrs         Attributes
	 * @param String $data         Inner content
	 * @return xml_object
	 */
	function xml_object($name, &$parent, $attrs = array(), $data = null) {
		$this->name = $this->convertTagName($name);
		$this->parent =& $parent;
		foreach ($attrs as $k => $v) {
			$this->set_attr($k, $v);
		}
		$this->data = $data;
		if ($this->parent != null)
    		$this->parent->add_child($this);
	}
	
	/**
     * Create a new XML-Element and register it at his parent object.
     * If a special Class is defined for the Element it will be used instead of
     * xml_object.
     *
     * @param String $name         Tagname of the Element
     * @param xml_object $parent   Parent Elemement
     * @param array $attrs         Attributes
     * @param String $data         Inner content
     * @return xml_object
     */
	function &factory($name, &$parent, $attrs = array(), $data = null) {
		switch (xml_object::convertTagName($name)) {
			case 'C:filter':
			case 'C:comp-filter':
                $object =& new xml_filter($name, $parent, $attrs, $data);
                break;
			default:
				$object =& new xml_object($name, $parent, $attrs, $data);
				break;
		}
		return $object;
	}
	
	/**
	 * Set the value of an attribute
	 *
	 * @param String $name     Name of the attribute
	 * @param mixed $value     Value of the attribute
	 */
	function set_attr($name, $value) {
		$this->attrs[$this->convertTagName($name)] = $value;
	}
	
	/**
	 * register a child element
	 *
	 * @param xml_object $child    Child that should be registered
	 */
	function add_child(&$child) {
		if (!in_array($child, $this->childs))
            $this->childs[] =& $child;
	}
	
	/**
	 * get the tagnames of all child elements
	 *
	 * @return array   Array containing all tagnames
	 */
	function get_childnames() {
		$res = array();
		foreach ($this->childs as $child) {
			$res[] = $child->name;
		}
		return $res;
	}
	
	/**
	 * Get the Tags of all childs. The Tags are returned with namepace and
	 * tagname 
	 *
	 * @return array   Array containing all Tags with namespace
	 */
	function get_childtags() {
		$tmp =
		$res = array();
        foreach ($this->childs as $child) {
            list($tmp['ns'], $tmp['name']) = $this->taginfo($child->name);
            $res[] = $tmp;
        }
        return $res;
	}
	
	/**
	 * Get all child elements matching a defined path. if no path is
	 * defined the element itself is returned
	 * 
	 * @param String part1     First part of the path
	 * @param String part2     Second part of the path
	 * ...
	 * @return array           Array containing all matching elements
	 */
	function get() {
        $path = func_get_args();
		if (count($path) == 0) return array(&$this);
		
		$name = array_shift($path);
		
		$res = array();
		for ($i = 0; $i < count($this->childs); $i++) {
			if ($this->childs[$i]->name == $name) {
				$tmp =& call_user_func_array(array($this->childs[$i], 'get'), $path);
				if (is_array($tmp)) {
                    for ($j = 0; $j < count($tmp); $j++) {
                    	$res[] =& $tmp[$j];
                    }
				}
			}
		}
		if (empty($res)) $res = false;
		array_unshift($path, $name);
		return $res;
	}
	
	/**
     * Convert the tag names into uppercase and translate some common tags
     * into shorter equivalents.
     *
     * @param String $tag   The tag name that should be convertet
     * @return String       The converted tag name
     */
    function convertTagName($tag) {
        $tag = strtr($tag, array(
            'urn:ietf:params:xml:ns:caldav:' => 'C:',
            'DAV::' => 'D:',
        ));
        return $tag;
    }
    
    /**
     * Sets the path for the element and all child elements 
     *
     * @param unknown_type $path
     * @param unknown_type $convert
     */
    function set_path($path, $convert = true) {
    	$this->path = $path;
        for ($i = 0; $i < count($this->childs); $i++) { 
            $this->childs[$i]->set_path($path, false);
        }
    }
    
    /**
     * Divides a tagname into namespace and tagname
     *
     * @param String $name  tagname to be parsed
     * @return array        Array containing Namespace and tagname
     */
    function taginfo($name) {
    	$tmp = explode(':', $name);
    	$name = array_pop($tmp);
    	if (!empty($tmp))
    	   $ns = implode(':', $tmp).':';
    	if ($ns == 'D:') $ns = 'DAV:';
    	return array($ns, $name);
    }
}

?>