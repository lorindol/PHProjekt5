<?php
/**
 *
 * @package    projects
 * @subpackage main
 * @author     
 * @licence    GPL, see www.gnu.org/copyleft/gpl.html
 * @copyright  2000-2006 Mayflower GmbH www.mayflower.de
 * @version    $Id: 
 */

function ps_get_projects ($projectlist, $costcentrelist, $costunitlist, $contractorlist) {
	global $user_ID, $module, $user_kurz;
	static $projects = null;
	static $tree_elements = null;

	if (is_null($tree_elements)) {
		$tree_elements = (array) get_elements_of_tree("projekte",
            "name", "", "acc", "ORDER BY name", "", "parent");
	}

	if (is_null($projects) and isset($projectlist)) {
		$result = db_query("SELECT p.ID, p.parent, p.name, p.contractor_id, p.costcentre_id, pc.costunit_id
	                    	  FROM ".DB_PREFIX."projekte p
	                     LEFT JOIN ".DB_PREFIX."projekte_costunit pc
	                            ON p.ID = pc.projekte_id
	                    	 WHERE p.ID IN (".implode(",",$projectlist).")")
								or db_die();

		$projects = array();
		while ($row = db_fetch_row($result)) {
			if (!array_key_exists($row[0], $projects)) {
				$projects[$row[0]] = array('pid'        => $row[0],
										   'parent'     => $row[1],
								       	   'name'       => $row[2],
								       	   'contractor' => $row[3],
								       	   'costcentre' => $row[4]);
			}
			if (!is_null($row[5])) {
				$projects[$row[0]]['costunit'][] = $row[5];
			}
		}
	}
	$results = array();

	foreach ((array) $projectlist as $project_id) {
		$parents = array_merge((array) $project_id,
					array_reverse(
						get_parent_ids($tree_elements, $project_id)));

		$results[$project_id] = $projects[$project_id];

		foreach ($parents as $parent_id) {
			foreach(array('costunit','contractor','costcentre') as $type) {
				if (!is_null($projects[$parent_id][$type])
				 && is_null($results[$project_id][$type]))
				 {
					$results[$project_id][$type] = $projects[$parent_id][$type];
				 }
			}
		}
	}

	foreach ($results as $id=>$p) {
		if (array_search($p['contractor'], $contractorlist) === false
		  && $contractorlist[0] != 'gesamt') {
			unset ($results[$id]);
			continue;
		}

		if (array_search($p['costcentre'], $costcentrelist) === false
		  && $costcentrelist[0] != 'gesamt') {
			unset ($results[$id]);
			continue;
		}

		$found = false;
		if($costunitlist[0] == 'gesamt'){
		    $found = true;
		}
		foreach ((array) $p['costunit'] as $costunit) {
			if (array_search($costunit, $costunitlist) !== false
			||   $costunitlist[0] == 'gesamt') {
				$found = true;
				break;
			}
		}

		if (!$found) {
			unset ($results[$id]);
			continue;
		}
	}

	return $results;

}


/**
 * Get the overruled fraction
 *
 * @todo Use ps_get_overruled_celement for that
 * @param int $costunit_id
 * @param int $project_id
 * @return float
 */
function ps_get_fraction ($costunit_id, $project_id) {
	static $fractions = null;
	static $tree_elements = null;

	if (is_null($tree_elements)) {
		$tree_elements = get_elements_of_tree("projekte", "name",  "", "acc",
							"ORDER BY name", "",  "parent");
	}

	if (is_null($fractions)) {
		$fractions = array();
		$result    = db_query("SELECT projekte_ID, costunit_id, fraction
		            		FROM ".DB_PREFIX."projekte_costunit") or db_die();
		while($row = db_fetch_row($result)) {
			$fractions[$row[1]][$row[0]] = $row[2];
		}
	}

	if (array_key_exists($costunit_id, $fractions) &&
		array_key_exists($project_id, $fractions[$costunit_id])) {
			return $fractions[$costunit_id][$project_id] / 100;
	} else {
		$parent_ids = get_parent_ids($tree_elements, $project_id);
		if (count($parent_ids) > 0) {
			return ps_get_fraction($costunit_id, array_pop($parent_ids));
		}
	}

	return 0.0;
}

function ps_get_debitavalue ($projectId) {
	$tree_elements = get_elements_of_tree("projekte",
                        "name, costcentre_id",
                        "",
                        "acc",
                        "ORDER BY name",
                        "",
                        "parent");

	$cids = (array) get_parent_ids($tree_elements, $projectId);
	$cids[] = $projectId;
	/**
	$result = db_query("SELECT p.name,
							((p.budget/p.stundensatz)/(TO_DAYS(p.ende) - TO_DAYS(p.anfang))) * (TO_DAYS(NOW())- TO_DAYS(p.anfang))
	                      FROM ".DB_PREFIX."projekte p
	                 LEFT JOIN ".DB_PREFIX."timeproj t
	                        ON t.projekt = p.ID
	                     WHERE (acc LIKE 'system' OR ((von = ".(int)$user_ID." OR acc LIKE 'group' OR acc LIKE '%\"$user_kurz\"%')".group_string($module)."))
	                       AND p.ID IN (".implode(',', $cids).")
	                  GROUP BY p.ID");

	$debitvalue = "";
	$results = array();
	while ($row = db_fetch_row($result)) {
		$results[] = $row[1];
	}
    */
	$result = db_query("SELECT p.name, p.budget, p.stundensatz, TO_DAYS(p.anfang)-1, TO_DAYS(p.ende), TO_DAYS(NOW())
                      FROM ".DB_PREFIX."projekte p
                     WHERE (acc LIKE 'system' OR ((von = ".(int)$user_ID." OR acc LIKE 'group' OR acc LIKE '%\"$user_kurz\"%')".group_string($module)."))
                       AND p.ID IN (".implode(',', $cids).")
                  GROUP BY p.ID") or db_die();

	$debitvalue = "";
	$results = array();
	while ($row = db_fetch_row($result)) {
	    $results[] = $row[1];
	}
	return $results;
}

function ps_get_realprojectperiod($projectId) {
	static $cache = null;

	if (is_null($cache)) {
		$cache = array();

		$query = "SELECT p.id, tp.datum
		            FROM ".DB_PREFIX."projekte p,
		                 ".DB_PREFIX."timeproj tp
		           WHERE p.id = tp.projekt
		        ORDER BY tp.datum";
		$result = db_query($query) or db_die();

		while ($row = db_fetch_row($result)) {
			if (!array_key_exists($row[0], $cache)) {
				$cache[$row[0]]['begin'] = $row[1];
			}

			$cache[$row[0]]['end'] = $row[1];
		}
	}

	if (array_key_exists($projectId, $cache)) {
		return array($cache[$projectId]['begin'], $cache[$projectId]['end']);
	}

	return array();
}
?>
