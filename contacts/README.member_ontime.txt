/**
 * vacation/absence display for members list view
 *
 * @package    contacts
 * @subpackage members
 * @author     Florian Anderiasch, $Author: florian $
 * @licence    GPL, see www.gnu.org/copyleft/gpl.html
 * @copyright  2000-2007 Mayflower GmbH www.mayflower.de
 * @version    $Id: README.member_ontime.txt,v 1.1 2007-11-19 15:43:24 florian Exp $
 */

Additional feature for the phprojekt vacation addon for 5.3:
show the current vacation/absence status of groupmembers in contacts module.

To enable this feature you have to define the following constant in config.inc.php:

define('PHPROJEKT_HAVE_VACATION', 1);

The default behaviour is off, so you won't even notice it until enabled.
Per default you get the following color codes:
- light red	(absent)	person has a vacation/absence entry on this day
- light green	(present)	person started timecard and should be available
- light orange	(expected)	person has no vacation/absence, but no timecard start
- medium orange	(gone)		person has a finished timecard entry on this day (>16:00)
- light blue	(appointment)	person has an appointment in the calendar right now

- blue border			person has 1 or more appointments today


The following files were modified:

- contacts/members_view.php
	all the code, the colors are hardcoded in this file

- lang/en.inc.php
	language strings in a seperate section

- lang/de.inc.php
	only translation as of now
