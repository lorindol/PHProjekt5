<?php

define('PATH_PRE','../');
include_once(PATH_PRE.'lib/lib.inc.php');


// first write an updated src script
$content = "
<SEARCH
 version=\"5\"
 name=\"PHProjekt search\"
 description=\"PHProjekt\"
 action=\"".PHPR_HOST_PATH.PHPR_INSTALL_DIR."search/search.php?\"
 searchForm=\"".PHPR_HOST_PATH.PHPR_INSTALL_DIR."search/search.php\"
 method=\"GET\"
 >
<input name=\"module\" value=\"search\">
<input name=\"gebiet\" value=\"all\">
<input name=\"sourceid\" value=\"Mozilla-search\">
<input name=\"searchterm\" user>

<interpret
 resultListStart=\"<!-- RESULT LIST START -->\"
 resultListEnd=\"<!-- RESULT LIST END -->\"
 resultItemStart=\"<!-- RESULT ITEM START -->\"
 resultItemEnd=\"<!-- RESULT ITEM END -->\"
>
</search>

<browser
 update=\"http://mycroft.mozdev.org/update.php/id0/myplugin.src\"
 updateIcon=\"http://mycroft.mozdev.org/update.php/id0/myplugin.png\"
 updateCheckDays=\"700\"
>
";

$fp = fopen('./phprojekt.src','w+');
fwrite($fp,$content);
fclose($fp);

echo set_page_header();

echo '

<script
  type="text/javascript">
<!--
function errorMsg()
{
  alert("Netscape 6 or Mozilla is needed to install a sherlock plugin");
}
function addEngine(name,ext,cat,type)

{
  if ((typeof window.sidebar == "object") && (typeof
  window.sidebar.addSearchEngine == "function"))
  {
    window.sidebar.addSearchEngine(
      "'.PHPR_HOST_PATH.PHPR_INSTALL_DIR.'search/"+name+".src",
      "'.PHPR_HOST_PATH.PHPR_INSTALL_DIR.'img/"+name+"."+ext,
      name,
      cat );
  }
  else
  {
    errorMsg();
  }
}
//-->
</script>
<br /><br />
<table border=0>
<tr><td width=200>&nbsp;</td><td> <a href="javascript:addEngine(\'phprojekt\',\'png\',\'\',0)">Klick here to install PHProjekt search plugin</a>.<br /><br /></td></tr>
<tr><td width=200>&nbsp;</td><td> After you approved the question in the alert box the PHProjekt search plugin appears in the firefox search box.<br /><br /></td></tr>
<tr><td width=200>&nbsp;</td><td> Then return to your <a href="../index.php">PHProjekt main page</a></td></tr>
</table>
</body>
</html>
';
