<?
/**
 * @package    search
 * @subpackage main
 * @author     Albrecht Guenther, $Author: gustavo $
 * @licence    GPL, see www.gnu.org/copyleft/gpl.html
 * @copyright  2000-2006 Mayflower GmbH www.mayflower.de
 * @version    $Id: search_xml.php
 */

include_once '../config.inc.php';

header("Content-Type: text/xml");

echo '<?xml version="1.0" encoding="UTF-8"?>';
echo '<OpenSearchDescription xmlns="http://a9.com/-/spec/opensearch/1.1/">'."\n";
echo '<ShortName>phprojekt</ShortName>'."\n";
echo '<Description>Use phprojekt search</Description>'."\n";
echo '<Tags>phprojekt</Tags>'."\n";
echo '<Image height="46" width="46" type="image/*">'.PHPR_HOST_PATH.PHPR_INSTALL_DIR.'img/phprojekt.png</Image>'."\n";
echo '<Image height="16" width="16" type="image/*">'.PHPR_HOST_PATH.PHPR_INSTALL_DIR.'favicon.ico</Image>'."\n";
echo '<Url type="text/html" template="'.PHPR_HOST_PATH.PHPR_INSTALL_DIR.'search/search.php?searchterm={searchTerms}&amp;searchformcount=&amp;module=search&amp;gebiet=all"/>'."\n";
echo '<Parameter name="searchterm" value="{searchTerms}" />'."\n";

echo '</OpenSearchDescription>'."\n";

?>
