<?php

/**
 * Sets the favorite-flag for a group of project-ids
 *
 * @param array $IDs        ids of the projects
 * @param integer $user     Userid of the user
 * @param 0|1 $favorite     0 if not favorite, 1 if favorite
 * @return void
 */
function set_favorite($IDs, $user_ID, $favorite) {
	if (is_array($IDs)) {
		 $IDs = implode(', ', $IDs);
	}
    $result = db_query('UPDATE '.DB_PREFIX.'project_users_rel
                            SET favorite = "'.(int)$favorite.'"
                            WHERE user_ID = '.(int)$user_ID.' 
                            AND project_ID IN ('.$IDs.')') or db_die();
}

?>