<?php

/*
 * Modified  March 15, 2003 James Bourne <jbourne@mtroyal.ab.ca>
 *   - Added more comments as required
 *   - Brought ldap data (bottom) in line with db
 *
 * Modified March 16, 2003 James Bourne <jbourne@mtroyal.ab.ca>
 *   - Changed short name to use uid as this should always be set
 */

// $Id: ldapconf.inc.php,v 1.3 2006/08/22 08:05:47 albrecht Exp $

// check whether the lib has been included - authentication!
if (!defined("lib_included")) die("Please use index.php!");

/* LDAP stuff. Please modify the lines below according to your needs. */
$ldap_conf[1]['conf_name'] = 'default';
$ldap_conf[1]['srv']       = 'localhost'; // LDAP server name. 'localhost' for localhost.
$ldap_conf[1]['srch_dn']   = 'uid=readonly,ou=People,dc=yourdomain,dc=com'; // This LDAP user will be used for searching the LDAP Tree and getting the data for the addressbook

/*
 * Password for the given account. If not needed leave empty.
 * Note, if you leave this empty you must give access to read all person
 * entries and (if needed) the ldap_group_dn below.  If you do not do this
 * you will have problems accessing the LDAP data store
*/
$ldap_conf[1]['srch_dn_pw'] = 'secret';

/* ldap_sync:
'1' : Just authenticate users via LDAP --- no user data will be read from the LDAP server
'2' : Get some user-information via LDAP (contacts will retrieve its data from the LDAP tree as well)
*/
$ldap_conf[1]['ldap_sync'] = '2';
$ldap_conf[1]['nt_domain'] = ''; // this is the name of the NT-Domain, the Exchange server will use for authentication. (This is only important for MS-Exchange-users, of course), otherwise leave ''
$ldap_conf[1]['base_dn']   = 'dc=yourdomain,dc=com';

/*
 * groupauth: 0 or 1, do not do or to do group based ACL
 *
 * ldap_lang: default language to use when creating users
 *
 * ldap_group_dn: if left '' all who exist in the tree can login, otherwise
 *    only members of this group.  Can be groupOfNames or groupOfUniqueNames
 *
 * memberattr: the member attribute of the group.  Either member or uniqueMember
 *
 * autocreate: automatically create new users or fail them until created (1/0)
 *   Do not use with nt_domain above
 *
 * newusergrp: the numeric ID of the group you wish new users added to
 *
 * company: default company name you want new logins to be created under
 */
$ldap_conf[1]['groupauth']     = 1;
$ldap_conf[1]['ldap_lang']     = 'en';
$ldap_conf[1]['ldap_group_dn'] = 'cn=phprojekt,ou=group,dc=yourdomain,dc=com';
$ldap_conf[1]['memberattr']    = 'uniqueMember';
$ldap_conf[1]['autocreate']    = 1; /* 1 for yes, 0 for no */
$ldap_conf[1]['newusergrp']    = 1;    /* default */
$ldap_conf[1]['company']       = 'Your Company';

/*
 * The value of the fields below has to be the name of the
 * corresponding LDAP field as it relates to the users table.
*/

$ldap_conf[1][0]  = ''; // phprojekt ID
$ldap_conf[1][1]  = 'givenname'; // firstname
$ldap_conf[1][2]  = 'sn'; // lastname
$ldap_conf[1][3]  = 'uid'; // short name
$ldap_conf[1][4]  = ''; // password
$ldap_conf[1][5]  = 'o'; // Employer
$ldap_conf[1][6]  = ''; // Group
$ldap_conf[1][7]  = 'mail'; // email address
$ldap_conf[1][8]  = ''; // access rights
$ldap_conf[1][9]  = 'telephonenumber'; // phone number 1
$ldap_conf[1][10] = 'homephone'; //phone number 2
$ldap_conf[1][11] = 'facsimiletelephonenumber'; //fax-number
$ldap_conf[1][12] = 'postaladdress'; //street address
$ldap_conf[1][13] = 'l'; //city or locality
$ldap_conf[1][14] = 'postalcode'; //zip
$ldap_conf[1][15] = 'c'; // country
$ldap_conf[1][16] = ''; // language
$ldap_conf[1][17] = 'mobile'; // cell phone
$ldap_conf[1][18] = 'uid'; // login name
$ldap_conf[1][19] = ''; // ldap_name
$ldap_conf[1][20] = 'title'; // addresses
$ldap_conf[1][21] = ''; // sms
$ldap_conf[1][22] = ''; // role
$ldap_conf[1][23] = ''; // proxy
$ldap_conf[1][24] = ''; // settings

?>
