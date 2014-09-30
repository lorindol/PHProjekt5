<?php
/**
 * calendar gantt
 *
 * @package    calendar
 * @subpackage main
 * @author     Gustavo Solt, $Author: gustavo $
 * @licence    GPL, see www.gnu.org/copyleft/gpl.html
 * @copyright  2000-2006 Mayflower GmbH www.mayflower.de
 * @version    $Id: calendar_gantt.php,
 */

define('PATH_PRE','../');
require_once(PATH_PRE.'lib/lib.inc.php');
require_once(PATH_PRE.'lib/dbman_lib.inc.php');

if (empty($_SESSION['calendardata']['res'])) {
    die();
}
include_once (PATH_PRE."lib/chart/src/jpgraph.php");
include_once (PATH_PRE."lib/chart/src/jpgraph_gantt.php");

// -------------------------------------
// initialize variables
// the displayed year
$year = date("Y");
if (preg_match("/^\d{4}$/", $_GET['year'])) { $year = intval($_GET['year']); }

$graph = new GanttGraph (0,0, "auto");
$graph->SetDateRange("$year-01-01", "$year-12-31");
$graph->ShowHeaders( GANTT_HDAY | GANTT_HWEEK | GANTT_HMONTH | GANTT_HYEAR );
$graph->scale->week->SetStyle(WEEKSTYLE_FIRSTDAY2WNBR);
$graph->scale->SetDateLocale("");

// Now walk through the results and add a GanttBar for each resultrow
$last_user = 0;
$last_line = -1;
foreach ($_SESSION['calendardata']['res'] as $line => $row) {
    if ($last_user != $row['an']) {
        $last_user = $row['an'];
        $show_user = slookup('users','vorname, nachname','ID',$last_user);
        $last_line++;
    } else {
        $show_user = '';
    }

    // create, configure and add the bar
    $gantt_bar = new GanttBar ($last_line , $show_user, $row['datum'], $row['datum'], '');
    $gantt_bar->SetPattern(GANTT_SOLID, "red");
    $graph->Add($gantt_bar);
}
// Display the Gantt chart
$graph->Stroke();

?>
