<?php
/**
* exort a single contact to vcard
*
* @package    contacts
* @module     export
* @author     Albrecht Guenther, $Author: polidor $
* @licence    GPL, see www.gnu.org/copyleft/gpl.html
* @copyright  2000-2006 Mayflower GmbH www.mayflower.de
* @version    $Id: vcard_ex.php,v 1.16.2.1 2007/01/08 16:36:20 polidor Exp $
**/

define('PATH_PRE','../');
include_once(PATH_PRE."lib/lib.inc.php");

// check form token
check_csrftoken();

if (!isset($_GET['redirect'])) {
    header('Location: vcard_ex.php?redirect=1&contact_ID='.intval($_GET['contact_ID']).'&name='.xss($_GET['name']).'&csrftoken='.xss($_GET['csrftoken']).'&'.SID);
    exit;
}

// **********
// set header
$name = xss($_GET['name']);
$name = eregi_replace("[^a-z]", "_", $name).".vcf";
$file_download_type = "attachment";
include_once(LIB_PATH."/get_contenttype.inc.php");
// end set header

$query = "SELECT ID, vorname, nachname, anrede, firma, bemerkung, tel1, tel2,
                 mobil, fax, strasse, stadt, state, plz, land, email, url
            FROM ".DB_PREFIX."contacts
           WHERE ID = ".(int)$contact_ID;
$res = db_query($query) or db_die();
$row = db_fetch_row($res);
for ($i=0;$i < count($row); $i++) {
    $row[$i] = rtrim(eregi_replace("\n|\r", " ", $row[$i]));
}

echo "BEGIN:VCARD\n";
echo "VERSION:2.1\n";
echo "N:$row[2];$row[1];;$row[3]\n";
echo "FN:$row[1] $row[2]\n";
echo "ORG:$row[4]\n";
echo "NOTE:$row[5]\n";
echo "TEL;WORK;VOICE:$row[6]\n";
echo "TEL;HOME;VOICE:$row[7]\n";
echo "TEL;CELL;VOICE:$row[8]\n";
echo "TEL;WORK;FAX:$row[9]\n";
echo "ADR;WORK:;;$row[10];$row[11];$row[12];$row[13];$row[14]\n";
echo "EMAIL;PREF;INTERNET:$row[15]\n";
echo "EMAIL;INTERNET:$row[16]\n";
echo "URL:$row[17]\n";
echo "END:VCARD\n";

?>
