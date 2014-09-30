<?php
/**
* script to administrate field properties of many modules
*
* could only be accessed by the root user
*
* @package    admin
* @module     module designer
* @author     Albrecht Guenther, $Author: polidor $
* @licence    GPL, see www.gnu.org/copyleft/gpl.html
* @copyright  2000-2006 Mayflower GmbH www.mayflower.de
* @version    $Id: module_designer.php,v 1.46.2.5 2007/03/13 22:15:34 polidor Exp $
*/
// if (!defined('lib_included')) die('Please use index.php!');
define('PATH_PRE','../');
include_once(PATH_PRE.'lib/lib.inc.php');
include_once(LIB_PATH.'/dbman_lib.inc.php');

$css_void_background_image = 1;

$modules_available['contacts']    = __('Contacts');
$modules_available['notes']       = __('Notes');
$modules_available['projekte']    = __('Projects');
$modules_available['rts']         = __('Helpdesk');
$modules_available['links']       = __('Links');
$modules_available['todo']        = __('Todo');
$modules_available['mail_client'] = __('Mail');

$form_types = array('text' => __('Single Text line'),
                    'textarea' => __('Textarea'),
                    'display' => __('Display'),
                    'text_create' => __('First insert'),
                    'select_values' => __('Predefined selection'),
                    'select_multiple' => __('Multiple select'),
                    'select_category' => __('Category'),
                    'upload' => __('File'),
                    'date' => __('Date'),
                    'email' => __('Email Address'),
                    'email_create' => __('Email (at record cration)'),
                    'url' => __('url'),
                    'contact' => __('Contact'),
                    'contact_create' => __('Contact (at record cration)'),
                    'project' => __('Project'),
                    'checkbox' => __('Checkbox'),
                    'timestamp_show' => __('Show Date'),
                    'timestamp_create' => __('Creation date'),
                    'timestamp_modify' => __('Last modification date'),
                    'phone' => __('Telephone'),
                    'authorID' => __('Author'),
                    'userID' => __('Select user'),
                    'user_show' => __('Show user'),
                    'time' => __('Time'));

// security check - only super administrators can work here
if ($user_type==3 and $user_ID == 1) {}
else die("you are not priviledged to do this!");
// fix
if ($filter_show == "on") $filter_show = 1;
echo set_page_header();

// tabs
$module = 'Module Designer';
$tabs   = array();

// button bar
$buttons = array();
$buttons[] = array('type' => 'button', 'name' => 'close', 'value' => __('Close window'), 'active' => false, 'onclick' => 'window.close();');

echo '<div id="global-header">'.get_tabs_area($tabs).'</div>';
include_once(LIB_PATH.'/navigation.inc.php');
echo '<div id="global-content">'.get_buttons_area($buttons);

switch ($action) {
  case 'activate':
  $result = db_query("UPDATE ".DB_PREFIX."db_manager
                         SET db_inactive = 0
                       WHERE ID = ".(int)$ID) or db_die();
    list_values();
    break;

  case 'deactivate':
   $result = db_query("update ".DB_PREFIX."db_manager
                         set db_inactive = '1'
                       where ID = ".(int)$ID) or db_die();
    list_values();
    break;

  // modify existing field
  case 'edit':
    edit($ID);
    break;
  // submit record for edit or create
  case 'submit':

    // security check: does the PHProjekt root try to insert a sql query into the database via the module designer?
    if (eregi('sql',$form_type)) die('Sorry, no sql allowed here');

    // modify properties of existing field
    if ($ID > 0) {
    $result = db_query("update ".DB_PREFIX."db_manager
                             set form_name = '".$form_name."',
                                form_type = '$form_type',
                                form_tooltip = '$form_tooltip',
                                form_colspan = ".(int)$form_colspan.",
                                form_rowspan = ".(int)$form_rowspan.",
                                form_regexp = '$form_regexp',
                                form_default = '$form_default',
                                form_select = '$form_select',
                                form_length = '$form_length',
                                list_alt = '$list_alt',
                                filter_show = '$filter_show'
                          where ID = ".$ID) or db_die();
    }
    // crate new field
    else {
      // 1. step - create this field in the mentioned table
      // only if this field is a textarea give the db field the type text, otherwise a varchar255
      // fetch db definitions
      define("setup_included", "1");
      include("../setup/db_var.inc.php");
      // only for textarea we take a text field, for all other cases we hope that a varchar255 will be enough
      if ($form_type == 'textarea') $fieldtype = $db_text[PHPR_DB_TYPE];
      else                          $fieldtype = $db_varchar255[PHPR_DB_TYPE];

      $result = db_query("alter table ".qss(DB_PREFIX.$db_table)."
                                  add ".qss($db_name1)." $fieldtype") or db_die();
      $result = db_query("update ".qss(DB_PREFIX.$db_table)."
                             set ".qss($db_name1)." = ''") or db_die();

      // 2. step  - insert this field as a new record into the table db_manager
      $query = "insert into ".DB_PREFIX."db_manager
           (  db_table,   db_name,   form_name,   form_type,   form_tooltip,          form_colspan  ,        form_rowspan  ,  form_regexp,   form_default,   form_select,         form_length,       list_alt,   filter_show, db_inactive)
    values ('".qss($db_table)."','".qss($db_name1)."','".xss($form_name)."','".qss($form_type)."','".qss($form_tooltip)."',".(int)$form_colspan.",".(int)$form_rowspan.",'".qss($form_regexp)."','".qss($form_default)."','".qss($form_select)."',".(int)$list_length.",'".qss($list_alt)."','".qss($filter_show)."',0)";
      $result = db_query($query) or db_die();
    }
    list_values();
    break;
  // list fields
  default:
    list_values();
    break;
}


function list_values() {
  global  $sid;

   // list available fields
  echo "<table width='90%'>\n";
  echo "<thead><tr><th>".__('Module element')."</th><th>".__('Module')."</th>\n";
  echo "<th>".__('Type')."</th><th nowrap=\"nowrap\">".__('Form position')."</th>
  <th nowrap=\"nowrap\">".__('List position')."</th>
  <th>".__('Status')."</th><th>&nbsp;</th></tr></thead><tbody>\n";
  $result = db_query("select ID, form_name, db_table, form_type, db_inactive, form_pos, list_pos
                        from ".DB_PREFIX."db_manager
                    order by db_table, form_pos") or db_die();
  while ($row = db_fetch_row($result)) {
    // show title of each new module
    if ($row[2] <> $db_table_prev) {
      echo "<tr><td colspan='3'><b>".$row[2]."</b> ";
      echo "(<a href='./module_designer.php?action=edit&amp;ID=0&amp;db_table=".$row[2].$sid."'>".__('New')."</a> )</td><td><a target='_blank' href='./admin_sortbox.php?action=sort&amp;field_to_sort=form_pos&amp;extra_value=".$row[2]."'>".__('Sort form position')."</a></td><td><a target='_blank' href='./admin_sortbox.php?action=sort&amp;field_to_sort=list_pos&amp;extra_value=".$row[2]."'>".__('Sort list position')."</a></td>";
      echo "<td colspan='2'></td>";
      echo "</tr>\n";
    }
    $db_table_prev = $row[2];

    echo "<tr><td><a href='module_designer.php?action=edit&amp;ID=".$row[0].$sid."'>".enable_vars($row[1])."</a></td>";
    echo "<td>$row[2]</td><td>$row[3]</td><td>$row[5]</td><td>$row[6]</td>";
    // 1. field active
    if (!$row[4]) { echo "<td><div style='color:green'>".__('Active')."</div></td><td><a href='./module_designer.php?action=deactivate&amp;ID=".$row[0].$sid."'>".__('Deactivate')."</a></td></tr>\n"; }
    // 2. field inact ... you got it! ;)
    else          { echo "<td><div style='color:red'>".__('Inactive')."</div></td><td><a href='./module_designer.php?action=activate&amp;ID=".$row[0].$sid."'>".__('Activate')."</a></td></tr>\n"; }
  }
  echo "</tbody></table>";
}

function edit($ID) {
  global $modules_available, $form_types, $sid, $db_table;

  // start form
  echo "<form action='module_designer.php' method='post'>
    <fieldset>
    <legend>".(($ID > 0) ? __('Modify element') : __('Create new element'))."</legend>
  ";
    // fetch record
  if ($ID > 0) {
    $result = db_query("select ID, db_table, db_name, form_name, form_type, form_tooltip,
                               form_regexp, form_default, form_select, list_alt, filter_show,
                               form_colspan, form_rowspan, form_length
                          from ".DB_PREFIX."db_manager
                         where ID = ".(int)$ID) or db_die();
    $row = db_fetch_row($result);
    $db_table = $row[1];

    // Set default length
    if (intval($row[13]) == 0) {
        $row[13] = 15;
    }
  }

  $hidden = array('ID'=>$ID, 'action'=>'submit');
  if (SID) $hidden[session_name()] = session_id();
  echo hidden_fields($hidden);
  echo "<table cellpadding='3' cellspacing='1' border='0' width='600'>";

  // show module

  echo "<tr><td width='150'>".__('Module')."</td><td width='200'>";
  echo "<input type='hidden' name='db_table' value='".$db_table."' />\n";
  echo "<input type='hidden' name='ID' value='".$ID."' />\n";
  echo "<input type='hidden' name='action' value='submit' />\n";
  echo $modules_available[$db_table]."&nbsp;</td><td width='250'>&nbsp;</td></tr>\n";

  // name of field
  echo "<tr><td>".__('Field name in database')."</td><td>\n";
  if (!$ID) { echo "<input type=text name=db_name1 size=14>"; }
  else echo $row[2];
  echo "</td><td>".__('Use only normal characters and numbers, no special characters,spaces etc.')."</td></tr>\n";

  // name in form
  echo "<tr><td>".__('Field name in form').'</td><td><input type="text" size="40" name="form_name" value="'.str_replace('"', '&quot;', $row[3]).'" /></td><td>'.__('(could be modified later)')."</td></tr>\n";

  // type of form element
  echo "<tr><td>".__('Element Type')."</td><td><select name='form_type'>";
  foreach ($form_types as $form_type => $type_name) {
    echo "<option value='$form_type'";
    if ($form_type == $row[4]) echo " selected='selected'";
    echo ">$type_name</option>";
  }
  echo "</select></td><td>".__('Select the type of this form element')."</td></tr>\n";

  // tooltip
  echo "<tr><td>".__('Tooltip')."</td><td><textarea rows='5' cols='28' name='form_tooltip' >".$row[5]."</textarea></td><td>".__('Appears as a tipp while moving the mouse over the field: Additional comments to the field or explanation if a regular expression is applied')."</td></tr>\n";

  // if it's not a new element take the name of the table from the sql result.

  // colspan and rowspan
  echo "<tr><td>".__('Span element over')."</td><td><select name='form_colspan'>\n";
  for ($i=1;$i<=5;$i++) {
    echo "<option value='$i'";
    if ($i == $row[11]) echo 'selected="selected"';
    echo ">$i</option>";
  }
  echo "</select></td><td>".__('columns')."</td></tr>\n";
  echo "<tr><td>".__('Span element over')."</td><td><select name='form_rowspan'>\n";
  for ($i=1;$i<=5;$i++) {
    echo "<option value='$i'";
    if ($i == $row[12]) echo 'selected="selected"';
    echo ">$i</option>";
  }
  echo "</select></td><td>".__('rows')."</td></tr>\n";

  // Regular expression
  echo "<tr><td>".__('Regular Expression:')."</td><td><input type='text' size='40' name='form_regexp' value='".$row[6]."' /></td><td>".__('Please enter a regular expression to check the input on this field')."</td></tr>\n";

  // default value
  echo "<tr><td>".__('Default value')."</td><td><input type='text' size='40' name='form_default' value='".$row[7]."' /></td><td>".__('Predefined value for creation of a record. Could be used in combination with a hidden field as well')."</td></tr>\n";

  // select
  echo "<tr><td>".__('Content for select Box')."</td><td><textarea rows='5' cols='28' name='form_select' >".$row[8]."</textarea></td><td>".__('Used for fixed amount of values (separate with the pipe: | ) or for the sql statement, see element type')."</td></tr>\n";

  // field length
  echo "<tr><td>".__('Length of the field')."</td><td><input type='text' size='3' name='form_length' value='".$row[13]."' /></td><td>".__('Specific the size of the field in the form (Only for text fields)')."</td></tr>\n";

  // appears in the alt tag
  echo "<tr><td>".__('Alternative list view')."</td><td><input type='checkbox' name='list_alt'";
  if ($row[9] <> '') echo " checked= 'checked'";
  echo " /></td><td>".__('Value appears in the alt tag of the blue button (mouse over) in the list view')."</td></tr>\n";

  // appears in the filter
  echo "<tr><td>".__('Filter element')."</td><td><input type='checkbox' name='filter_show'";
  if ($row[10] <> '') echo " checked='checked'";
  echo " /></td><td>".__('Appears in the filter select box in the list view')."</td></tr>\n";

  // end of form
  echo "</table><input type='submit' value='".__('go')."' /></fieldset></form>";
  // back button
  echo "<a href='module_designer.php?action=null".$sid."'>".__('back')."</a><br /><br />";
}

?>
</div>
</div>
</body>
</html>
