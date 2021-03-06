<?php
/**
 * LDAP functions
 *
 * Modified March 15, 2003 James Bourne <jbourne@mtroyal.ab.ca>
 * Updated to add more logging of errors
 * Changed top end of array index for ldap->db array
 *
 * @package    	lib
 * @subpackage 	main
 * @author     	Moritz Kiese, $Author: gustavo $
 * @licence     GPL, see www.gnu.org/copyleft/gpl.html
 * @copyright  	2000-2006 Mayflower GmbH www.mayflower.de
 * @version    	$Id: ldap.php,v 1.10 2007-05-31 08:11:53 gustavo Exp $
 */

// check whether lib.inc.php has been included
if (!defined('lib_included')) die('Please use index.php!');


/**
 * Get ldap configuration of the user
 *
 * @param int 	$ID 	- User id
 * @return void
 */
function get_ldap_usr_data($ID) {
    global $ldap_conf;

    // fetch values of this user
    $result = db_query("SELECT ID, vorname, nachname, kurz, pw, firma, gruppe, email, acc,
                               tel1, tel2, fax, strasse, stadt, plz, land, sprache, mobil,
                               loginname, ldap_name, anrede, sms, role, proxy, settings
                          FROM ".DB_PREFIX."users
                         WHERE ID = ".(int)$ID."
                           AND is_deleted is NULL") or db_die();
    $user_data = db_fetch_row($result);

    $user_ldap_conf = $user_data[19];

    if (($ldap_con = ldap_connect($ldap_conf[$user_ldap_conf]['srv'])) != false) {
        // try to set LDAPv3 protocol - fallback (and default) is LDAPv2, if this went wrong
        // note: ldap_set_option must be between ldap_connect and ldap_bind
        ldap_set_option($ldap_con, LDAP_OPT_PROTOCOL_VERSION, 3);
        if (ldap_bind($ldap_con, $ldap_conf[$user_ldap_conf]['srch_dn'], $ldap_conf[$user_ldap_conf]['srch_dn_pw'])) {
            $ldap_usr = ldap_search($ldap_con, $ldap_conf[$user_ldap_conf]['base_dn'], "(uid=$user_data[18])");
            if ($ldap_usr != false) {
                $ldap_user_data = ldap_get_entries($ldap_con, $ldap_usr);
                for ($i = 0; ++$i < 25; ) {
                    if ($ldap_conf[$user_ldap_conf][$i] != '') {
                        $user_data[$i] = $ldap_user_data[0][$ldap_conf[$user_ldap_conf][$i]][0];
                    }
                }
            }
            else {
                logit("ldap_search failed for $user_data[18] in get_ldap_usr_data()");
            }
        }
        else {
            logit("ldap_bind failed in get_ldap_usr_data()".ldap_error($ldap_con));
        }
        ldap_close($ldap_con);
    }
    else {
        logit("ldap_connect failed in get_ldap_usr_data()");
    }
}
?>
