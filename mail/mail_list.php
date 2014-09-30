<?php

// mail_list.php - PHProjekt Version 5.2
// copyright  ©  2000-2005 Albrecht Guenther  ag@phprojekt.com
// www.phprojekt.com
// Author: Albrecht Guenther, $Author: alexander $
// $Id: mail_list.php,v 1.16 2006/11/20 10:25:34 alexander Exp $

// check whether the lib has been included - authentication!
if (!defined("lib_included")) die("Please use index.php!");


$ref = "mail.php?mode=view&ID=$row[0]&action=form&form=$row[11]$sid";
tr_tag($ref);

// show icon for folders
if ($row[11] == "d") {
  echo "<td colspan=6>\n";
  // show level of record with blanks
  for ($e = 0; $e < $level; $e++) echo "&nbsp;&nbsp;&nbsp;&nbsp;";

  // show button 'open'
  // look whether elements below this level exist
  $exist = 0;
  $untouched = 0;
  $result3 = db_query("select ID, touched, typ
                         from ".DB_PREFIX."mail_client
                        where parent = ".(int)$ID);
  while ($row3 = db_fetch_row($result3)) {
    // mail in this dir exist
    if ($row3[0]) { $exist = 1; }
    // count unred mails
    if (!$row3[1] and $row3[2] <> "d") { $untouched++; }
  }
  if ($untouched) { echo "<b>"; }
  if (!$arrdir[$ID] and $exist and $tree_mode <> "open") { echo "<a href='mail.php?mode=view&element_mode=open&ID=$row[0]$sid'><img src='".IMG_PATH."/close.gif' border=0>&nbsp;</a>"; }
  // show button 'close'
  else { echo "<a href='mail.php?mode=view&element_mode=close&ID=$row[0]$sid'><img src='".IMG_PATH."/open.gif' alt='' border=0>&nbsp;</a>"; }
  // link to form
  echo "<a href='mail.php?mode=view&ID=$row[0]&action=form&form=$row[11]$sid'>$row[2]</a>&nbsp;\n";
  if ($untouched) { echo "($untouched)</b>\n"; }
  echo "</td>\n";
}

// mails
else {

  // subject, first some attributes: unred, sent and level
  echo "<td>";
  // look whether this mail is unred, is yes: mark it bold
  if (!$row[10] and $row[11] <> "d") { echo "<b>"; }
  // mark it as italic if it's a sent mail
  elseif ($row[11] == "s") { echo "<i>"; }
  // show level of record with blanks
  for ($e = 0; $e < $level; $e++) echo "&nbsp;&nbsp;&nbsp;&nbsp;";

  // title
  echo "&nbsp;&nbsp;&nbsp;&nbsp;<a href='$ref'>$row[2]</a>&nbsp;</td>\n";
  // Sender
  if (ereg("&lt;",$row[4])) {$sender=explode("&lt;",$row[4]);$sender1=substr($sender[1],0,-4);$sender2=$sender[0];}
  else { $sender2 = $sender1 = $row[4]; }
  sendmail_link(html_out($sender1), html_out($sender2));
  // checkbox delete, buttons reply and forward
  // if the flag 'select_all' is set, mark the checkbox as checked
  if ($sel_all) { $flag = "checked"; }

  if (check_role("mail") > 1) {
    echo "<td nowrap>";
    echo "<input id=checkbox type=checkbox name='delete_mail[]' value='$row[0]' $flag>\n";
    echo "<a href='mail.php?mode=send_form&form=email&action2=reply&ID=$row[0]$sid'><img src='".IMG_PATH."/arrowleft.gif' alt='".__('Reply')."' title='".__('Reply')."' border=0 width=10></a>&nbsp;\n";
    echo "<a href='mail.php?mode=send_form&form=email&action2=forward&ID=$row[0]$sid'><img src='".IMG_PATH."/arrowright.gif' alt='".__('Forward')."' title='".__('Forward')."' border=0 width=10></a>&nbsp;\n";
    echo "</td>";
  }

  // date
  echo "<td>".$date_format_object->convert_dbdatetime2user($row[13])."&nbsp;</td>\n";

  // delete
  if (check_role("mail") > 1) {
    echo "<td><a href='mail.php?mode=view&action=delete&delete_ID=$row[0]$sid'><img src='".IMG_PATH."/r.gif' alt='".__('Delete')."' title='".__('Delete')."' border=0 width=7></a>&nbsp;</td>\n";
  }
  // look for attachments
  echo "<td>";
  $result3 = db_query("select ID,parent,filename,tempname,filesize
                         from ".DB_PREFIX."mail_attach
                        where parent = ".(int)$ID) or db_die();
  while ($row3 = db_fetch_row($result3)) {
    // initialize random string for ID
    if ($rnd) echo '<br />';
    $rnd = rnd_string(9);
    // determine filesize
    if ($row3[4] > 1000000)  {$fsize = floor($row3[4]/1000000)." M";}
    elseif ($row3[4] > 1000) {$fsize = floor($row3[4]/1000)." k";}
    else {$fsize = $row3[4];}
    // write data to the array for downloading
    $file_ID[$rnd] = "$row3[2]|$row3[3]|$row3[0]";
    echo "<a href='../mail/mail_down.php?rnd=".$rnd.$sid."' target=_blank>$row3[2] ($fsize)</a>\n";
  }
  echo "&nbsp;</td>\n";
}

// category
echo "<td>$row[7]&nbsp;</td>\n";

echo "</tr>\n";

$_SESSION['file_ID'] =& $file_ID;

?>
