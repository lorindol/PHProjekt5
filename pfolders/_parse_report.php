<?php
require_once(WEBDAV_PATH.'class_xml_object.php');

class _parse_report  {
	/**
	 * The XMLParser that is used
	 *
	 * @access private
	 * @var XMLParser
	 */
    var $parser;
    /**
     * Reference to the current position in the DOM
     *
     * @access private
     * @var Array
     */
    var $pointer = null;
    /**
     * Status of the parsing proccess. Should be true after Completion
     *
     * @access public
     * @var boolean
     */
    var $success = false;
  
    /**
     * Constructor for _parse_report.
     * Parses the content of a file
     *
     * @access public
     * @param String $path  The Path of the file that should be parsed
     */
    function _parse_report($path) {
        $data = implode('', file($path));

        $this->parser = xml_parser_create_ns();
        xml_set_object($this->parser, $this);
        xml_parser_set_option($this->parser, XML_OPTION_CASE_FOLDING, false);
        xml_parser_set_option($this->parser, XML_OPTION_SKIP_WHITE, true);
        xml_set_element_handler($this->parser, "tag_open", "tag_close");
        xml_set_character_data_handler($this->parser, "cdata");
        xml_parse($this->parser, $data);
        $this->success = true;
    }
  
    /**
     * Start collection the data of a node. This function is called by the
     * parser whenever it detects an opening tag.
     *
     * @param XMLParser $parser     The parser that is used to parse the XML
     * @param String $tag           The name of the current tag
     * @param Array $attributes     The attributes that are defined for this tag
     */
    function tag_open($parser, $tag, $attributes) {
    	$this->pointer =& xml_object::factory($tag, $this->pointer, $attributes);
    }

    /**
     * Parse the CDATA content of a node.
     *
     * @param XMLParser $parser     The parser that is used to parse the XML
     * @param String $cdata         The CDATA in the current node
     */
    function cdata($parser, $cdata) {
    	if (trim($cdata) !== '' && $cdata !== false && $cdata !== null)
            $this->pointer->data = $cdata;
    }

    /**
     * Parse the collected data of a node. This function is called by the
     * parser whenever it detects a closing tag.
     *
     * @param XMLParser $parser     The parser that is used to parse the XML 
     * @param String $tag           The name of the closing tag
     */
    function tag_close($parser, $tag) {
    	if ($this->pointer->parent !== null)
    	   $this->pointer =& $this->pointer->parent;
    }
        
    /**
     * Parses an array containing a nested filter array and returns a flat
     * representation of it. The result is an array containing all
     *
     * @param array $filter     Array containing the nested filter definition
     */
    function flatFilter($filter) {
    	a($filter);
    	$result = array();
    	if (isset($filter['COMP-FILTER'])) {
    		$result = array_merge($result, $this->flatFilter($filter['COMP-FILTER']));
    	}
    	if (isset($filter['NAME'])) {
    		$result[$filter['NAME']] = true;
            if (isset($filter['C:TEXT-MATCH'])) {
                $result[$filter['NAME']] = $filter['C:TEXT-MATCH'];
            }
        }
        if (isset($filter['C:TIME-RANGE'])) {
        	$result['C:TIME-RANGE'] = $filter['C:TIME-RANGE'];
        }
    	return $result;
    }
}
?>