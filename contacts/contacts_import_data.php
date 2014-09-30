<?php
/**
* stores imported contacts into db
*
* @package    contacts
* @module     import
* @author     Albrecht Guenther, $Author: gustavo $
* @licence    GPL, see www.gnu.org/copyleft/gpl.html
* @copyright  2000-2006 Mayflower GmbH www.mayflower.de
* @version    $Id: contacts_import_data.php,v 1.27.2.1 2007/01/13 15:00:44 gustavo Exp $
*/
if (!defined('lib_included')) die('Please use index.php!');

// array for field_delimiters
$delimiters = array( 0 => ',', 1 => ';' );

// Remove mysterious leading newline
$import_contacts = trim($import_contacts);
include_once(LIB_PATH.'/access.inc.php');
$access = assign_acc($acc, 'contacts');
switch ($import_contacts) {

    // vcard
    case 'vcard':
        // read vcard, assign values to db fields
        $lines = file($userfile);
        reset($lines);
        // Main loop
        while (list(, $text) = each ($lines)) {
            // Skip lines until next vcard starts
            while (strtoupper(trim($text)) != "BEGIN:VCARD")
                list(, $text) = each ($lines);

            // Found one: First we clear, then we parse
            $name = ''; $company = ''; $note = ''; $tel1 = ''; $tel2 = ''; $fax = '';
            $mail = ''; $mail2 = ''; $url = ''; $adr = ''; $mob = '';

            while (list(, $text) = each ($lines)) {
                // Done?
                if (strtoupper(trim($text)) == "END:VCARD") break;
                // Apparently not
                $a = explode(":", addslashes(trim($text)), 2);
                if ($a[0] == "ORG") $company = $a[1];  // company
                if ($a[0] == "N" or $a[0] == "n") {
                    $name = explode(";",$a[1]);
                    $name[0] = check_lastname($name[0], $company);
                }
                if (eregi("NOTE",$a[0])) {// note
                    if (eregi("ENCODING=QUOTED-PRINTABLE",$a[0])) { $a[1] = quoted_printable_decode($a[1]); }
                    $note = $a[1];
                }
                if (eregi("TEL",$a[0]) and eregi("WORK",$a[0]) and !eregi("FAX",$a[0])) {if($tel1=="") $tel1 = $a[1]; } // tel1
                if (eregi("TEL",$a[0]) and eregi("HOME",$a[0]) and !eregi("FAX",$a[0])) {if($tel2=="") $tel2 = $a[1]; } // tel2
                if (eregi("TEL",$a[0]) and eregi("CELL",$a[0]) and !eregi("FAX",$a[0])) {if($mob=="") $mob = $a[1]; } // mobile
                if (eregi("TEL",$a[0]) and eregi("FAX",$a[0])) {
                    if ($fax == "") $fax = $a[1];
                }
                if (eregi("EMAIL",$a[0]) and (eregi("INTERNET",$a[0]) or eregi("HOME",$a[0]) or eregi("WORK",$a[0]))) {
                    if ($mail == "") $mail = $a[1];
                    elseif ($mail2 == "") $mail2 = $a[1];
                } // email
                if (eregi("URL",$a[0])) {  // web
                    if ($url == "") $url = $a[1];
                }
                if (eregi("ADR", $a[0])) { // adress
                    if ($a[2]) $a[1] = $a[1].$a[2];
                    if (eregi("ENCODING=QUOTED-PRINTABLE",$a[0])) { $a[1] = quoted_printable_decode($a[1]); }
                    if ($adr == "") $adr = explode(";", $a[1]);
                }
            } // End while vcard parse

            // db action
            // first check for doublettes
            $parent = 0; $error = 0;
            if ($doublet_action <> '') {
                $imp_records = array('vorname'=>$name[1],'nachname'=>$name[0],'firma'=>$company,'email'=>$mail,'strasse'=>$adr[2],'plz'=>$adr[5],'stadt'=>$adr[3],'land'=>$adr[6]);
                // FYI imp_records = imported fields, ex_records = existing fields
                $parent = check_for_doublettes($imp_records, $ex_records);
                // if a similar records has been found, check whether the records should be discarded
                if ($parent > 0 and $doublet_action == 'discard') $error = 1;
            }
            if (!$error) {
                if ($doublet_action == 'replace') {
                    $result = db_query("UPDATE ".DB_PREFIX."contacts
                                           SET vorname = '".xss($name[1])."',
                                               nachname ='".xss($name[0])."', 
                                               gruppe = ".(int)$user_group.", 
                                               firma = '".xss($company)."',
                                               email = '".xss($mail)."',
                                               tel1 = '".xss($tel1)."',
                                               tel2 = '".xss($tel2)."',
                                               fax = '".xss($fax)."',
                                               strasse = '".xss($adr[2])."',
                                               stadt = '".xss($adr[3])."',
                                               plz = '".xss($adr[5])."',
                                               land = '".xss($adr[6])."',
                                               kategorie = '".xss($kategorie)."',
                                               bemerkung = '".xss($note)."',
                                               von = ".(int)$user_ID.",
                                               email2 = '".xss($mail2)."',
                                               mobil = '".xss($mob)."',
                                               url = '".xss($url)."',
                                               anrede = '".xss($name[3])."',
                                               state = '".xss($adr[4])."',
                                               import= 1,
                                               acc_read = '$access', 
                                               acc_write = '$acc_write'
                                         WHERE ID=".(int)$parent) or db_die();
                }
                else {
                    $result = db_query("INSERT INTO ".DB_PREFIX."contacts
                                               (        vorname    ,        nachname   ,        gruppe      ,        firma      ,        email   ,        tel1    ,        tel2   ,        fax     ,        strasse   ,        stadt     ,        plz       ,        land      ,        kategorie    ,        bemerkung,        von      ,        email2   ,        mobil  ,        url    ,        anrede     ,        state     ,import,        parent  ,  acc_read,  acc_write )
                                        VALUES ('".xss($name[1])."','".xss($name[0])."',".(int)$user_group.",'".xss($company)."','".xss($mail)."','".xss($tel1)."','".xss($tel2)."','".xss($fax)."','".xss($adr[2])."','".xss($adr[3])."','".xss($adr[5])."','".xss($adr[6])."','".xss($kategorie)."','".xss($note)."' ,".(int)$user_ID.",'".xss($mail2)."','".xss($mob)."','".xss($url)."','".xss($name[3])."','".xss($adr[4])."','1'   ,".(int)$parent.",'$access' ,'$acc_write')") or db_die();
                }
            }
        }
        break;

    // import outlook express
    case 'oe':
        $i = 0;
        $fp = fopen($userfile,"r");
        while ($a = fgetcsv($fp, 2000, ";")) {
            if ($i > 0) {
                if (count($a) != 29) echo "<b>$i. ".__('Record import failed because of wrong field count')."!</b><br />\n";
                else {
                    array_walk($a,'arr_addsl');
                    $a[1] = check_lastname($a[1], $a[24]);
                    // routine to check for doublettes  - some explanation see above, section vcard
                    $parent = 0; $error = 0;
                    if ($doublet_action <> '') {
                        $parent = 0; $error = 0;
                        $imp_records = array('vorname'=>$a[0],'nachname'=>$a[1],'firma'=>$a[24],'email'=>$a[5],'strasse'=>$a[15],'plz'=>$a[17],'stadt'=>$a[16],'land'=>$a[19]);
                        $parent = check_for_doublettes($imp_records, $ex_records);
                        if ($parent > 0 and $doublet_action == 'discard') $error = 1;
                    }
                    // end check for doublettes
                    if (!$error) {
                        if ($doublet_action == 'replace') {
                            $result = db_query("UPDATE ".DB_PREFIX."contacts
                                                   SET vorname = '".xss($a[0])."',
                                                       nachname= '".xss($a[1])."',
                                                       gruppe = ".(int)$user_group.",
                                                       firma = '".xss($a[24])."',
                                                       email = '".xss($a[5])."',
                                                       tel1 = '".xss($a[21])."',
                                                       tel2 = '".xss($a[11])."',
                                                       fax = '".xss($a[22])."',
                                                       strasse = '".xss($a[15])."',
                                                       stadt = '".xss($a[16])."',
                                                       plz = '".xss($a[17])."',
                                                       land = '".xss($a[19])."',
                                                       kategorie = '".xss($kategorie)."',
                                                       bemerkung = '".xss($a[28])."',
                                                       von = ".(int)$user_ID.",
                                                       email2 = '".xss($a[13])."',
                                                       url = '".xss($a[14])."',
                                                       state = '".xss($a[18])."',
                                                       import= 1,
                                                       acc_read = '$access', 
                                                       acc_write = '$acc_write'
                                                 WHERE ID = ".(int)$parent) or db_die();
                        }
                        else {
                            $result = db_query("INSERT INTO ".DB_PREFIX."contacts
                                                       (        vorname ,        nachname,        gruppe      ,        firma    ,        email   ,        tel1     ,        tel2     ,        fax      ,        strasse  ,        stadt    ,        plz      ,        land     ,        kategorie    ,        bemerkung,        von      ,        email2   ,        url      ,        state    ,import,        parent  ,  acc_read,  acc_write)
                                                VALUES ('".xss($a[0])."','".xss($a[1])."',".(int)$user_group.",'".xss($a[24])."','".xss($a[5])."','".xss($a[21])."','".xss($a[11])."','".xss($a[22])."','".xss($a[15])."','".xss($a[16])."','".xss($a[17])."','".xss($a[19])."','".xss($kategorie)."','".xss($a[28])."',".(int)$user_ID.",'".xss($a[13])."','".xss($a[14])."','".xss($a[18])."','1'   ,".(int)$parent.",'$access' ,'$acc_write')") or db_die();
                        }
                    }
                }
            }
            $i++;
        }
        fclose ($fp);
        break;

    // import outlook
    case 'outlook':
        $i = 0;
        $fp = fopen($userfile,'r');
        while ($a = fgetcsv($fp, 2000, ',')) {
            if ($i > 0) {
                if (count($a) >92) echo "<b>$i. ".__('Record import failed because of wrong field count')."!</b><br />\n";
                else {
                    array_walk($a,'arr_addsl');
                    $a[3] = check_lastname($a[3], $a[5]);
                    // routine to check for doublettes  - some explanation see above, section vcard
                    $parent = 0; $error = 0;
                    if ($doublet_action <> '') {
                        $imp_records = array('vorname'=>$a[1],'nachname'=>$a[3],'firma'=>$a[5],'email'=>$a[55],'strasse'=>$a[8],'plz'=>$a[13],'stadt'=>$a[11],'land'=>$a[14]);
                        $parent = check_for_doublettes($imp_records, $ex_records);
                        if ($parent > 0 and $doublet_action == 'discard') $error = 1;
                    }
                    // end check for doublettes
                    if (!$error) {
                        if ($doublet_action == 'replace') {
                            $result = db_query("UPDATE ".DB_PREFIX."contacts 
                                                   SET vorname = '".xss($a[1])."',
                                                       nachname = '".xss($a[3])."',
                                                       gruppe = ".(int)$user_group.",
                                                       firma = '".xss($a[5])."',
                                                       email = '".xss($a[55])."',
                                                       tel1 = '".xss($a[31])."',
                                                       tel2 = '".xss($a[37])."',
                                                       fax = '".xss($a[30])."',
                                                       strasse = '".xss($a[8])."',
                                                       stadt = '".xss($a[11])."',
                                                       plz = '".xss($a[13])."',
                                                       land = '".xss($a[14])."',
                                                       kategorie = '".xss($kategorie)."',
                                                       bemerkung = '".xss($a[73])."',
                                                       von = ".(int)$user_ID.",
                                                       email2 = '".xss($a[57])."',
                                                       mobil = '".xss($a[40])."',
                                                       url = '".xss($a[86])."',
                                                       anrede = '".xss($a[0])."',
                                                       state = '".xss($a[12])."',
                                                       import = 1,
                                                       acc_read='$access', 
                                                       acc_write='$acc_write'
                                                 WHERE ID = ".(int)$parent) or db_die();
                        }
                        else {
                            $result = db_query("INSERT INTO ".DB_PREFIX."contacts
                                                       (        vorname ,        nachname,        gruppe      ,        firma   ,        email    ,        tel1     ,        tel2     ,        fax      ,        strasse ,        stadt    ,        plz      ,        land     ,        kategorie    ,        bemerkung,        von      ,        email2   ,        mobil    ,        url      ,        anrede ,         state    ,import,        parent  ,  acc_read,  acc_write )
                                                VALUES ('".xss($a[1])."','".xss($a[3])."',".(int)$user_group.",'".xss($a[5])."','".xss($a[55])."','".xss($a[31])."','".xss($a[37])."','".xss($a[30])."','".xss($a[8])."','".xss($a[11])."','".xss($a[13])."','".xss($a[14])."','".xss($kategorie)."','".xss($a[73])."',".(int)$user_ID.",'".xss($a[57])."','".xss($a[40])."','".xss($a[86])."','".xss($a[0])."','".xss($a[12])."','1'   ,".(int)$parent.",'$access' ,'$acc_write')") or db_die();
                        }
                    }
                }
            }
            $i++;
        }
        fclose ($fp);
        break;

        // import kd3 addressbook cvs file
        case 'kde3':
        $fp = fopen($userfile,'r');
        // ignore first record which only contains a description of the fields
        $a = fgetcsv($fp, 2000, ",");
        while ($a = fgetcsv($fp, 2000, ",")) {
            // ignore empty records
            if (count(array_unique($a))>1) {
                $a[1] = check_lastname($a[1], $a[5]);
                // routine to check for doublettes  - some explanation see above, section vcard
                $parent = 0; $error = 0;
                if ($doublet_action <> '') {
                    $imp_records = array('vorname'=>$a[0],'nachname'=>$a[1],'firma'=>$a[5],'email'=>$a[28],'strasse'=>$a[20],'plz'=>$a[23],'stadt'=>$a[21],'land'=>$a[24]);
                    $parent = check_for_doublettes($imp_records, $ex_records);
                    if ($parent > 0 and $doublet_action == 'discard') $error = 1;
                }
                // end check for doublettes
                if (!$error) {
                    // company name is set import business address
                    if ($a[5] != "") {
                        $result = db_query("INSERT INTO ".DB_PREFIX."contacts
                                                   (        vorname ,        nachname,        gruppe      ,        firma   ,        email   ,        tel1    ,        tel2     ,        fax      ,        strasse  ,        stadt    ,        plz      ,        land     ,        kategorie    ,        bemerkung,        von      ,        mobil    ,        anrede  ,        state    ,import,        parent  ,  acc_read,  acc_write)
                                            VALUES ('".xss($a[0])."','".xss($a[1])."',".(int)$user_group.",'".xss($a[5])."','".xss($a[6])."','".xss($a[9])."','".xss($a[10])."','".xss($a[13])."','".xss($a[20])."','".xss($a[21])."','".xss($a[23])."','".xss($a[24])."','".xss($kategorie)."','".xss($a[8])."' ,".(int)$user_ID.",'".xss($a[11])."','".xss($a[3])."','".xss($a[22])."','1'   ,".(int)$parent.",'$access' ,'$acc_write')") or db_die();
                    }
                    // seems to be a home address
                    else {
                        $result = db_query("INSERT INTO ".DB_PREFIX."contacts
                                                   (        vorname ,        nachname,        gruppe      ,        firma   ,        email   ,        tel1     ,        tel2    ,        fax      ,        strasse  ,        stadt    ,        plz      ,        land     ,       kategorie     ,        bemerkung,       von      ,        mobil    ,        anrede  ,        state    ,import,        parent  ,  acc_read,  acc_write )
                                            VALUES ('".xss($a[0])."','".xss($a[1])."',".(int)$user_group.",'".xss($a[5])."','".xss($a[6])."','".xss($a[10])."','".xss($a[9])."','".xss($a[12])."','".xss($a[15])."','".xss($a[16])."','".xss($a[17])."','".xss($a[19])."','".xss($kategorie)."','".xss($a[8])."',".(int)$user_ID.",'".xss($a[11])."','".xss($a[3])."','".xss($a[17])."','1'   ,".(int)$parent.",'$access' ,'$acc_write')") or db_die();
                    }
                }
            }
        }
        break;

    // import other list
    case 'other':

        // prepare sql string
        if ($apply_pattern > 0) {
            $pattern_array = unserialize(slookup('contacts_import_patterns','pattern','ID',$apply_pattern,'1'));
        }
        else {
            // include dbman_lib.inc.php to create the fields array
            include_once('../lib/dbman_lib.inc.php');
            build_array('contacts', 0, 'forms');
            foreach($fields as $field_name => $field) {$sql_fields[] = $field_name; }
            $sql_fieldstring = implode(",",$sql_fields);
        }

        // ok, lets start reading the import records
        $fp = fopen($userfile, "r");
        while ($a = fgetcsv($fp, 4000, $delimiters[$csv_field_delimiter])) {
            $error = 0;
            array_walk($a, 'arr_addsl');
            $sql_values = array();
            // build values
            // 1.case - the user has chosen an import pattern
            if ($apply_pattern > 0) {
                $sql_fields = array();
                // fetch pattern
                foreach ($pattern_array as $position => $db_field) {
                    if ($db_field <> 'void') {
                        $sql_fields[] = $db_field;
                        $sql_values[] = $a[$position];
                        // prepare array for doublet check
                        if ($doublet_check <> '' and in_array($db_field,$doublet_fields)) {
                            $imp_records[$db_field] = $a[$position];
                        }
                    }
                }
                $sql_fieldstring = implode(",",$sql_fields);
            }
            // 2. otherwise we hope that he has sorted his input file in the right way ;)
            else {
                // prepare array for doublet check
                if ($doublet_check <> '') {
                    $imp_records = array('vorname'=>$a[1],'nachname'=>$a[2],'firma'=>$a[3],'email'=>$a[4],'strasse'=>$a[10],'plz'=>$a[11],'stadt'=>$a[12],'land'=>$a[13]);
                }
                for ($i = 0; $i < count($a); $i++) { $sql_values[] = $a[$i]; }
            }
            // in all cases we build now the sql string for the import of the values
            $sql_valuestring = "'".implode("','",$sql_values)."'";

            // routine to check for doublettes  - some explanation see above, section vcard
            $parent = 0;
            $error  = 0;
            if ($doublet_action <> '') {
                $parent = check_for_doublettes($imp_records, $ex_records);
                if ($parent > 0 and $doublet_action == 'discard') $error = 1;
            }
            // end check for doublettes
            // last check - do we have the same number of sql_fields and sql_values?
            if (count($sql_fields) <> count($sql_values)) {
                for ($i=0;$i<=3;$i++) $firstvalues[$i] = "'".$sql_values[$i]."'";
                message_stack_in("This record (".implode(",",$firstvalues).") hasn't been inserted has been cancelled because the number of fields (".count($sql_fields).") does not match the number of values (".count($sql_values)."). Please check your input file again and compare it with the list given in the first text",$module,'error');
                $error = 1;
            }
            // now for the real db action - finally!
            if (!$error) {
                if ($parent > 0 && $doublet_action == 'replace') {
                    $sql_string = '';
                    foreach ($sql_fields as $key => $name) {
                        $sql_string .= ", ".qss($name)." = '".$sql_values[$key]."'";
                    }
                    //echo "$sql_string<br />";
                    $result = db_query("UPDATE ".DB_PREFIX."contacts
                                           SET gruppe = ".(int)$user_group.", 
                                               import = 1 
                                               $sql_string
                                         WHERE von = ".(int)$user_ID." 
                                           AND ID = ".(int)$parent) or db_die();
                }
                else if(!$isheader) {
                    $result = db_query("INSERT INTO ".DB_PREFIX."contacts
                                               (        gruppe      ,        von      ,import,        parent  ,  acc_read,  acc_write , ".$sql_fieldstring." )
                                        VALUES (".(int)$user_group.",".(int)$user_ID.",1     ,".(int)$parent.",'$access' ,'$acc_write', ".$sql_valuestring." )") or db_die();
                }
            }
            unset($isheader);
            $i++;
        }
        fclose ($fp);
        break;
}

// set session flag that imported contacts have to be approved
$approve_contacts = 1;
$_SESSION['approve_contacts'] =& $approve_contacts;

?>
