<?php

// settings_data_profile.php - PHProjekt Version 5.2
// copyright  ©  2000-2005 Albrecht Guenther  ag@phprojekt.com
// www.phprojekt.com
// Author: Franz Graf, $Author: alexander $
// $Id: settings_data_profile.php,v 1.20.2.3 2007/01/24 15:59:52 alexander Exp $

// check whether the lib has been included - authentication!
if (!defined('lib_included')) die('Please use settings.php!');


// Update:
if ($_REQUEST['profile_id'] && $_REQUEST['profile_id'] > 0) {

    // Check permission:
    // The user may update/delete this profile if it already exists
    // and if the 'von'-entry equals with the user's id
    $query = "SELECT COUNT(*)
                FROM ".DB_PREFIX."profile
               WHERE ID  = ".intval($_REQUEST['profile_id'])." 
                 AND von = ".(int)$user_ID;
    $result = db_query($query) or db_die();
    $row = db_fetch_row($result);

    if ($row && $row[0] > 0) {
        // okay - it's the user's profile let him do what he wants
        if ($_REQUEST['action_write_profile']) {
            include_once(LIB_PATH."/access.inc.php");
            $access = assign_acc($acc, 'profile');
            $data = array( 'id'    => $profile_id,
                           'name'  => $profile_name,
                           'users' => $profile_users,
                           'acc'   => $access );
            update_profile($data);
        }
        else if ($_REQUEST['action_delete_profile']) {
            delete_profile($profile_id);
        }
    }
}
else {
    include_once(LIB_PATH."/access.inc.php");
    $access = assign_acc($acc, 'profile');
    $data = array( 'name'  => $profile_name,
                   'users' => $profile_users,
                   'acc'   => $access );
    insert_profile($data);
}



// ---------------------------------------

/**
* Delete the profile identified by the given ID
* It is NOT checked, if the profile really belongs to that user!
* This HAS to be done before.
*
* @param int $id    ID of the profile to delete
*/
function delete_profile($id) {
    $query = "DELETE
                FROM ".DB_PREFIX."profile
               WHERE ID = ".(int)$id;
    db_query($query) or db_die();
    message_stack_in(__('The profile has been deleted.'), 'settings', 'notice');
}


/**
* Insert a new profile.
* Parameterchecks are NOT performed at this point.
* If the array of usernames is empty, the insert does NOT get executed.
*
* @author       Franz Graf
* @param string $data['name']   Name of this profile
* @param array  $data['users']  array of user-id's
* @param string $data['acc']    access rights
*/
function insert_profile($data) {
    global $user_ID, $user_group;

    prepare_profile_data($data);
    if (empty($data['name'])) {
        message_stack_in(__('Please specify a description! '), 'settings', 'error');
        return;
    }
    if (count($data['users']) == 0) {
        message_stack_in(__('Please select at least one name! '), 'settings', 'error');
        return;
    }

    $query = "SELECT bezeichnung
                FROM ".DB_PREFIX."profile
               WHERE von = ".(int)$user_ID." 
                 AND bezeichnung = '".qss($data['name'])."'";
    $result = db_query($query);
    $row = db_fetch_row($result);

    if ($row[0] == $data['name'])
        message_stack_in(__('A Profile with the same name already exists'), 'settings', 'error');
    else {
        $data['users'] = serialize(xss_array($data['users']));
        $query = "INSERT INTO ".DB_PREFIX."profile
                         (        von      ,         bezeichnung     ,     personen        ,        gruppe      ,     acc           )
                  VALUES (".(int)$user_ID.", '".xss($data['name'])."', '".$data['users']."',".(int)$user_group.", '".$data['acc']."')";
        db_query($query) or db_die();
        message_stack_in(xss($data['name']).__(' is created as a profile.<br />'), 'settings', 'notice');
    }
}


/**
* Update an already existing profile of this user.
* Parameterchecks are NOT performed at this point.
*
* @author       Franz Graf
* @param int    $data['id']     ID of the profile to update
* @param string $data['name']   (new) Name of this profile
* @param array  $data['users']  array of user-id's
* @param string $data['acc']    access rights
*/
function update_profile($data) {
    global $user_group;

    prepare_profile_data($data);
    if (empty($data['name'])) {
        message_stack_in(__('Please specify a description! '), 'settings', 'error');
        return;
    }
    if (count($data['users']) == 0) {
        message_stack_in(__('Please select at least one name! '), 'settings', 'error');
        return;
    }

    $data['users'] = serialize(xss_array($data['users']));
    $query = "UPDATE ".DB_PREFIX."profile
                 SET bezeichnung = '".xss($data['name'])."',
                     personen    = '".$data['users']."',
                     gruppe      = ".(int)$user_group.",
                     acc         = '".$data['acc']."'
               WHERE ID = ".(int)$data['id'];
    db_query($query) or db_die();
    message_stack_in(xss($data['name']).__('is changed.<br />'), 'settings', 'notice');
}


/**
* Prepare the form data for saving.
*
* @param string &$data['name']   Name of the profile
* @param array  &$data['users']  array of user-shortnames(!)
*/
function prepare_profile_data(&$data) {

    $data['name'] = trim($data['name']);
    settype($data['users'], 'array');

    // convert users id's to 'kurz'
    $ids = '';
    if (count($data['users']) > 0) {
        //$ids = implode(",", $data['users']);
        foreach ($data['users'] as $oneUser) {
            $ids .= (int)$oneUser.",";
        }
        if (strlen($ids) > 0) {
            $ids = substr($ids,0,-1);
        }
        else {
            $ids = 0;
        }
        
        $data['users'] = array();
        $query = "SELECT kurz
                    FROM ".DB_PREFIX."users
                   WHERE ID IN ($ids)";
        $res = db_query($query) or db_die();
        while ($row = db_fetch_row($res)) {
            $data['users'][] = $row[0];
        }
    }
}

?>
