<?php

/**
 * Representation of a <C:filter /> element
 *
 * @author Michel Hartmann (michel-hartmann@mayflower.de
 */
class xml_filter extends xml_object {
    
	/**
	 * get a flat representation of the filter and its children
	 *
	 * @return array   Array containing all filters
	 */
	function flat() {
        $result = array();
        if ($this->name == 'C:time-range') {
            $result['C:time-range'] = $this->attrs;
        }
        if (isset($this->attrs['name'])) {
            $result[$this->attrs['name']] = true;
            if (isset($this->attrs['C:text-match'])) {
                $result[$this->attrs['name']] = $this->attrs['C:text-match'];
            }
        }
        foreach ($this->childs as $child) {
        	$result = array_merge($result, $child->flat());
        }
        return $result;
	}
}

?>