<?php

// mail_fetch.php - PHProjekt Version 5.2
// copyright  ©  2000-2005 Albrecht Guenther  ag@phprojekt.com
// www.phprojekt.com
// Author: Albrecht Guenther, $Author: polidor $
// $Id: mail_fetch.php,v 1.29 2006/09/24 22:16:48 polidor Exp $

// check whether the lib has been included - authentication!
if (!defined("lib_included")) die("Please use index.php!");

// check role
if (check_role("mail") < 1) die("You are not allowed to do this!");

// FETCH MAIL
// Note: fetch mail function was moved to lib/fetchmail.php.

// RULES
/**
 * This function will check and apply all the user rules on received email
 *
 * @param array $email the email were check the rules
 * @param int $parent_contact default related contact ID
 * @param int $parent_project default related project ID
 * @param int $mail2contact mail2contact check boolean
 * @return array an array with the following elements:
 *               'parents'        List of parent directories to save the mail
 *               'parent_project' Project related by rule
 *               'parent_contact' Contact related by rule
 *               'mail2contact'   if mail contact auto assign is checked, will be set in 1, else it will be 0
 */
function apply_rules($email, $parent_contact = 0, $parent_project = 0, $mail2contact = 0) {
    global $user_ID, $n_deleted;

    $deleted = false;
    $moved   = false;
    
    // fetch rules, write to array
    $i = 0;
    $result2 = db_query("SELECT ID, von, title, phrase, type, is_not, parent, action, projekt, contact 
                         FROM ".DB_PREFIX."mail_rules 
                        WHERE von = ".(int)$user_ID) or db_die();
    
    while ($row2 = db_fetch_row($result2)) {

        // check for general rules: incoming dir and assign to contact
        if ($row2[4] == 'incoming') $incoming     = $row2[6]; // parent
        if ($row2[4] == 'mail2con') $mail2contact = 1;

        // write field and phrase in array
        $rules[$i] =  array("$row2[4]" => "$row2[3]");

        // create another array with the same counter, place target dir.
        $rules_action[$i] = $row2[7]; // action
        $dir_ID[$i]       = $row2[6]; // parent
        $projekt_ID[$i]   = $row2[8]; // projekt
        $contact_ID[$i]   = $row2[9]; // contact
        $i++;
    }

    // check for applicable rule
    unset($action);
    unset($parents);
    
    // only one rule applicable at the moment, sorry.
    for ($i = 0; $i < count($rules); $i++) {
        // loop over all
        list($field, $keyword2) = each($rules[$i]);
        // special rule for body since we have to search through 2 fields
        if ($field  == 'body') {
            if($keyword2 <> '' and (eregi($keyword2,$email[body_text]) or eregi($keyword2,$email[body_html]))) {
                $parent = $dir_ID[$i];
                $action = $rules_action[$i];
                if( slookup('projekte','ID','ID',$projekt_ID[$i],'1') > 0) $parent_project = $projekt_ID[$i];
                if( slookup('contacts','ID','ID',$contact_ID[$i],'1') > 0) $parent_contact = $contact_ID[$i];
            }
        }
        else { // applicable for all other fields
            if (isset($email[$field])) {
                if($keyword2 <> '' and eregi($keyword2,$email[$field])) {
                    $parent = $dir_ID[$i];
                    $action = $rules_action[$i];
                    if( slookup('projekte','ID','ID',$projekt_ID[$i],'1') > 0) $parent_project = $projekt_ID[$i];
                    if( slookup('contacts','ID','ID',$contact_ID[$i],'1') > 0) $parent_contact = $contact_ID[$i];
                }
            }
        }
    }

    if (!isset($parents[0]) && !$deleted) {
        $parents[0] = 0;
    }

    // now for the actions
    if ($action == 'delete') {
        unset($parents);
        $n_deleted++; // raise number of deleted mails
        $deleted = true;
    }
    // move -> sort into given dir
    elseif($action == 'move' && !$deleted) {
        $parents[0] = $parent;
        $moved = true;
    }
    // copy -> mail goes into given dir and in the root
    elseif($action == 'copy' && !$deleted) {
        $parents[1] = $parents[0];
        $parents[0] = $parent;
    }

    // check for default rule
    if ($incoming > 0 && !$deleted && !$moved) {
        // any other rule already given? -> add the rule
        if ($parents[0] > 0) { $parents[1] = $incoming; }
        // not other rule? -> move into this folder
        else { $parents[0] = $incoming; }
    }
    // no rules -> normal save into root dir
    /*
    else {
    $parents[0] = 0;
    }
    */
    // preparing return array
    $toReturn = array();

    $toReturn['parents']        = $parents;
    $toReturn['parent_project'] = $parent_project;
    $toReturn['parent_contact'] = $parent_contact;
    $toReturn['mail2contact']   = $mail2contact;

    return $toReturn;
}
?>
