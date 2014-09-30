<?php
// bg.inc.php, bulgarian version - optimized for use with default skin and version 4.1 of PHProjekt
// translation by George Smilianov <smilianov.dir.bg>, <smilianov@dir.bg>
// file version 1.1

$chars = array("�","�","�","�","�","�","�","�","�","�","�","�","�","�","�","�","�","�","�","�","�","�","�","�","�","�","�");
$name_month = array("", "������", "��������", "����", "�����", "���", "���", "���", "������", "���������", "��������", "�������", "��������");
$l_text31a = array("�� ������������", "15 ���.", "30 ���.", " 1 ���", " 2 ����", " 4 ����", " 1 ���");
$l_text31b = array(0, 15, 30, 60, 120, 240, 1440);
$name_day = array("������", "����������", "�������", "�����", "���������", "�����", "������");
$name_day2 = array("��", "��", "��", "��", "��", "��","��");
            

//Lang Data

$_lang[" already exists. "]  = " ���� ����������. ";
$_lang[" days "]  = " ��� ";
$_lang[" is changed."]  = " � ��������.";
$_lang[" is deleted."]  = " � ������.";
$_lang["(could be modified later)"]  = "(���� �� ���� �������� ��-�����)";
$_lang["(keep old password: leave empty)"]  = "(��������� �� ������� ������: �������� ������)";
$_lang["-interval:"]  = "-��������:";
$_lang[":"]  = ":";
$_lang["<"]  = "<";
$_lang["<="]  = "<=";
$_lang["<h2>Profiles</h2>In this section you can create, modify or delete profiles:"]  = "<h2>�������</h2> ��� ������ �� ���������, ��������� ��� ��������� �������:";
$_lang["<li>If you encounter any errors during the installation, please look into the <a href=help/faq_install.html target=_blank>install faq</a>or visit the <a href=http://www.PHProjekt.com/forum.html target=_blank>Installation forum</a></i>"]  = "<li>��� ���������� ������� ������ �� ����� �� ������������, ���� ���������� ��� <a href='help/faq_install.html' target=_blank>����� �������� ������� ������� ������������</a> ��� �������� <a href='http://www.PHProjekt.com/forum.html' target=_blank>������ ������� ������������</a></i>";
$_lang[">"]  = ">";
$_lang[">="]  = ">=";
$_lang["?"]  = "?";
$_lang["A file with this name already exists!"]  = "���� ��� ������ ��� ���� ����������!";
$_lang["accepted"]  = "�����";
$_lang["access"]  = "������";
$_lang["Access error for mailbox"]  = "������ ��� ������� � ���������� �����";
$_lang["Accounts"]  = "����������";
$_lang["Action for duplicates"]  = "�������� �� ��������� ��";
$_lang["Activate"]  = "���������";
$_lang["Active"]  = "�������";
$_lang["added"]  = "�������";
$_lang["Additional address"]  = "������������ ������";
$_lang["Additional alert box"]  = "Additional alert box";
$_lang["Additional number"]  = "������������ �����";
$_lang["Administrator"]  = "�������������";
$_lang["Aim"]  = "���";
$_lang["Alarm"]  = "������";
$_lang["All"]  = "������";
$_lang["All events are deleted"]  = "������ ������� �� �������";
$_lang["all fields"]  = "������ ������";
$_lang["All groups"]  = "������ �����";
$_lang["All Links are valid."]  = "������ ������ �� �������.";
$_lang["All links in events to this project are deleted"]  = "������ ������ ��� ������� � ���� ������ �� �������";
$_lang["all modules"]  = "������ ������";
$_lang["All profiles are deleted"]  = "������ ������� �� �������";
$_lang["All todo lists of the user are deleted"]  = "������ '�� �������' ������� �� ����������� �� �������";
$_lang["Alternative list view"]  = "������������ �������� ������";
$_lang["and leave on server"]  = "� ������ �� �������";
$_lang["Appears as a tipp while moving the mouse over the field: Additional comments to the field or explanation if a regular expression is applied"]  = "������� �� ���� ���������, ������ ������� ������� ��� ������: ������������ ��������� ������ ������ �� ��������";
$_lang["Apply import pattern"]  = "������� ������� �����";
$_lang["approve"]  = "����������";
$_lang["Are you sure?"]  = "������� �� ���?";
$_lang["As parent object"]  = "���� ���� ����������";
$_lang["Assigned"]  = "��������";
$_lang["Assigning projects"]  = "����������� �� �������";
$_lang["Attachment"]  = "��������� �������";
$_lang["Automatic assign to group:"]  = "����������� ����������� ��� �����:";
$_lang["Automatic assign to user:"]  = "����������� ����������� ��� ����������:";
$_lang["back"]  = "�����";
$_lang["bank account deleted"]  = "������� ������ �������";
$_lang["Begin"]  = "������";
$_lang["Begin > End"]  = "������ > ����";
$_lang["Begin:"]  = "������:";
$_lang["Body"]  = "����";
$_lang["Bookmark"]  = "�������";
$_lang["Bookmarks"]  = "�������";
$_lang["Budget"]  = "������";
$_lang["Calculated budget"]  = "������� ������";
$_lang["Calendar"]  = "��������";
$_lang["Calendar user"]  = "Kalenderbenutzer";
$_lang["calendar week"]  = "���������� �������";
$_lang["cancel"]  = "�����";
$_lang["cannot end before the end of project"]  = "������ �� ������ ����� ����������� �� �������";
$_lang["cannot end before the start of project"]  = "������ �� ������ ����� ����������� �� �������";
$_lang["cannot start before the end of project"]  = "������ �� ������� ����� ����������� �� �������";
$_lang["cannot start before the start of project"]  = "������ �� ������� ����� �������� �� �������";
$_lang["Category"]  = "���������";
$_lang["Category deleted"]  = "����������� � �������";
$_lang["changed"]  = "��������";
$_lang["Chat"]  = "���";
$_lang["Check"]  = "��������";
$_lang["Check for duplicates during import"]  = "��������� �� ��������� �� �� ����� �� �������";
$_lang["Check for mail"]  = "�������� �� ����";
$_lang["Check the content of the previous field!"]  = "��������� ������������ �� ���������� ����!";
$_lang["Choose group"]  = "������ �����";
$_lang["Close window"]  = "������� ���������";
$_lang["columns"]  = "������";
$_lang["Comment"]  = "��������";
$_lang["Contact"]  = "�������";
$_lang["Contact Manager"]  = "���������� �� ��������";
$_lang["Contacts"]  = "��������";
$_lang["contains"]  = "�������";
$_lang["Content for select Box"]  = "���������� �� ��������� �����";
$_lang["Copy"]  = "��������";
$_lang["Create"]  = "���������";
$_lang["Create new element"]  = "������ ��� �������";
$_lang["Create new event"]  = "��������� �� �������";
$_lang["create vcard"]  = "��������� �� vcard";
$_lang["Created by"]  = "��������� ��";
$_lang["Crypt upload file with password"]  = "���������� � ������";
$_lang["Daily"]  = "������";
$_lang["Date"]  = "����";
$_lang["Day"]  = "���";
$_lang["Days"]  = "���";
$_lang["Deactivate"]  = "�����������";
$_lang["Deadline"]  = "����� ����";
$_lang["Default value"]  = "�������� �� ������������";
$_lang["Delete"]  = "���������";
$_lang["Delete group and merge contents with group"]  = "��������� �� ����� � ������� �� ������������ � �����";
$_lang["Deletion of super admin root not possible"]  = "����������� �� ����� ������������������ ����� �� � ��������";
$_lang["Dependency"]  = "����������";
$_lang["descending"]  = "descending";
$_lang["Describe your request"]  = "������� ������ ������";
$_lang["Description"]  = "��������";
$_lang["Directory"]  = "����������";
$_lang["Discard duplicates"]  = "��������� �� �������";
$_lang["Display"]  = "������";
$_lang["Dispose as child"]  = "��������� ���� child";
$_lang["does not contain"]  = "�� �������";
$_lang["Does not exist"]  = "�� ����������";
$_lang["done"]  = "�����";
$_lang["Element Type"]  = "��� �� ��������";
$_lang["Email Address"]  = "Email �����";
$_lang["End"]  = "����";
$_lang["End:"]  = "����:";
$_lang["ended"]  = "���������";
$_lang["ends with"]  = "�������� �";
$_lang["Event"]  = "Event";
$_lang["Events"]  = "�������";
$_lang["exact"]  = "�����";
$_lang["export"]  = "�������";
$_lang["External contacts"]  = "������ ��������";
$_lang["Fax"]  = "����";
$_lang["Field name in database"]  = "��� �� ����(��) � ���� �����";
$_lang["Field name in form"]  = "��� �� ���� ��� �����";
$_lang["Field separator"]  = "���������� �� ����";
$_lang["Fields to match"]  = "������ �� ���������";
$_lang["File"]  = "����";
$_lang["File Downloads"]  = "������� �� �������";
$_lang["File management"]  = "���������� �� �������";
$_lang["Files"]  = "�������";
$_lang["Filter"]  = "������";
$_lang["finished"]  = "��������";
$_lang["First hour of the day:"]  = "����� ��� �� ����";
$_lang["First insert"]  = "����� ��������";
$_lang["First module view on startup"]  = "����� ����� ��� ����������";
$_lang["for invalid links"]  = "�� ��������� ������";
$_lang["Forum"]  = "�����";
$_lang["Forward"]  = "�����";
$_lang["Gantt"]  = "Gantt";
$_lang["Generate a new password"]  = "���������� �� ���� ������";
$_lang["Group"]  = "������";
$_lang["Group created"]  = "����� ���������";
$_lang["Group management"]  = "���������� �� �����";
$_lang["Group members"]  = "������� �� �����";
$_lang["Group name"]  = "��� �� �����";
$_lang["has been canceled"]  = "���� �������";
$_lang["has been changed"]  = "���� ��������";
$_lang["has been created"]  = "���� ��������";
$_lang["Help"]  = "�����";
$_lang["Helpdesk"]  = "�����";
$_lang["horizontal"]  = "������������";
$_lang["host name"]  = "host ���";
$_lang["Hourly rate"]  = "����� �� ���";
$_lang["Hours"]  = "������";
$_lang["imcoming Mails"]  = "������� ����";
$_lang["Import"]  = "������";
$_lang["Import list"]  = "������ �� ������� �� ����������";
$_lang["Import pattern"]  = "������ �����";
$_lang["in"]  = "�";
$_lang["in mails"]  = "� ���������";
$_lang["Inactive"]  = "���������";
$_lang["Insert a valid Internet address! "]  = "������� ������� �������� �����! ";
$_lang["insert additional working time"]  = "insert additional working time";
$_lang["insert db field (only for contacts)"]  = "�������� ���� �� ������ ����� (���� �� ��������)";
$_lang["internal"]  = "��������";
$_lang["is in field"]  = "� � ������";
$_lang["is taken to the bookmark list."]  = "� ������� � ������� � �������.";
$_lang["just one <b>Alternative</b> or"]  = "���� ���� <b>������������</b> ���";
$_lang["Language"]  = "����";
$_lang["Last status change"]  = "�������� �������";
$_lang["ldap name"]  = "ldap ���";
$_lang["Leader"]  = "�����";
$_lang["Legend"]  = "�������";
$_lang["Link"]  = "������";
$_lang["List"]  = "�������";
$_lang["locked by"]  = "�������� ��";
$_lang["logged in as"]  = "Angemeldet als";
$_lang["Logging"]  = "������������";
$_lang["Login"]  = "�������";
$_lang["Login name"]  = "�������������� ���";
$_lang["Logout"]  = "�����";
$_lang["Mail"]  = "����";
$_lang["Max. file size"]  = "���������� ������ �� ����";
$_lang["max. minutes before the event"]  = "����. ������ ����� �������";
$_lang["Me"]  = "��";
$_lang["Member of following groups"]  = "���� �� �������� �����";
$_lang["minutes"]  = "������";
$_lang["mobile // mobile phone"]  = "������� ���.";
$_lang["Modify"]  = "�ndern";
$_lang["Modify element"]  = "����������� ��������";
$_lang["Module"]  = "�����";
$_lang["Module Designer"]  = "����� ��������";
$_lang["Module element"]  = "����� �������";
$_lang["Move"]  = "�������";
$_lang["multiple events"]  = "����������� �������";
$_lang["Multiple select"]  = "����� �� �����";
$_lang["My Statistic"]  = "��� ����������";
$_lang["Name"]  = "���";
$_lang["name and network path"]  = "��� � ������� ������";
$_lang["Name of the database"]  = "Name of the database";
$_lang["Name of the existing database"]  = "��� �� �������������� ���� �����";
$_lang["name of the rule"]  = "��� �� ���������";
$_lang["Name or short form already exists"]  = "����� �� ������ ����� ���� ����������";
$_lang["New"]  = "���";
$_lang["New contact"]  = "��� �������";
$_lang["New files"]  = "���� �������";
$_lang["New forum postings"]  = "���� ��������� ��� ������";
$_lang["New mail arrived"]  = "���������  ���� ����";
$_lang["New notes"]  = "���� �������";
$_lang["New Password"]  = "���� ������";
$_lang["New password"]  = "���� ������";
$_lang["New Polls"]  = "���� �������";
$_lang["New profile"]  = "Neuer Verteiler";
$_lang["New Sub-Project"]  = "��� ���������";
$_lang["News"]  = "������";
$_lang["No"]  = "��";
$_lang["No access"]  = "���� ������";
$_lang["No events yet today"]  = "���� ��� ��� ���� �������";
$_lang["No value"]  = "���� ��������";
$_lang["No Value"]  = "���� ��������";
$_lang["normal"]  = "��������";
$_lang["Normal user"]  = "�������� ����������";
$_lang["Notes"]  = "�������";
$_lang["Notice of receipt"]  = "���������� ��� ����������";
$_lang["Notify all group members"]  = "������������ �� ������ ������� �� �����";
$_lang["objects"]  = "������";
$_lang["of"]  = "of";
$_lang["offered"]  = "��������";
$_lang["Old password"]  = "����� ������";
$_lang["Old Password"]  = "����� ������";
$_lang["Once"]  = "������";
$_lang["Only main projects"]  = "���� ������ �������";
$_lang["Only this project"]  = "���� ���� ������";
$_lang["open"]  = "open";
$_lang["Options"]  = "�����";
$_lang["or"]  = "���";
$_lang["or profile"]  = "��� ������";
$_lang["ordered"]  = "�������";
$_lang["Orphan files"]  = "��������� �������";
$_lang["Parent object"]  = "���������� �����";
$_lang["part of the word"]  = "���� �� ������";
$_lang["Participants"]  = "���������";
$_lang["Participants:"]  = "���������:";
$_lang["Participation"]  = "Participation";
$_lang["Password"]  = "������";
$_lang["Passwords dont match!"]  = "�������� �� ��������!";
$_lang["Person"]  = "�����";
$_lang["Persons"]  = "����";
$_lang["Phone"]  = "�������";
$_lang["Please check start and end time! "]  = "���� ��������� ������� �� ������ � ����! ";
$_lang["Please check the date!"]  = "���� ��������� ������!";
$_lang["Please check the end time! "]  = "���� ��������� ������� �� ����! ";
$_lang["Please check the start time! "]  = "���� ��������� ������� �� ������! ";
$_lang["please check the status!"]  = "���� ��������� �������!";
$_lang["Please check your date and time format! "]  = "���� ��������� ������� �� ������ ���� � �����! ";
$_lang["Please choose a project"]  = "���� �������� ������";
$_lang["Please choose a user"]  = "���� �������� ����������";
$_lang["Please choose an element"]  = "���� �������� �������";
$_lang["Please choose an export file (*.csv)"]  = "���� �������� ��������� ���� (*.csv)";
$_lang["Please choose at least one person"]  = "���� �������� ���� ���� �����";
$_lang["Please choose at least one project"]  = "���� �������� ���� ���� ������";
$_lang["Please enter a regular expression to check the input on this field"]  = "���� �������� �������� �����, �� �� ��������� ����� �� ���� ����";
$_lang["Please fill in the fields below"]  = "���� ��������� �������� ����";
$_lang["Please fill in the following field"]  = "���� ��������� �������� ����";
$_lang["Please insert a name"]  = "���� �������� ���";
$_lang["Please remark:<ul><li>A blank database must be available<li>Please ensure that the webserver is able to write the file config.inc.php"]  = "���� ����������:<ul><li>������ �� ��� ������ ���� �����<li>����, ������� ��, �� �������� ���� �� ���� ��� ����� 'config.inc.php'";
$_lang["Please select a file"]  = "���� �������� ����";
$_lang["Please select a file (*.csv)"]  = "���� �������� ���� (*.csv)";
$_lang["Please select a vcard (*.vcf)"]  = "���� �������� vcard (*.vcf)";
$_lang["Please select at least one (valid) address."]  = "���� �������� ���� ���� (�������) �����.";
$_lang["Please select at least one bookmark"]  = "���� �������� ���� ���� �������";
$_lang["Please select at least one name! "]  = "���� �������� ���� ���� ���! ";
$_lang["Please select!"]  = "���� ��������!";
$_lang["Please specify a description!"]  = "���� ���������� ��������!";
$_lang["Please specify a description! "]  = "���� ���������� ��������! ";
$_lang["Please specify the question for the poll! "]  = "���� ���������� �������� �� �����������! ";
$_lang["Poll created on the "]  = "����������� ��������� �� ";
$_lang["Position"]  = "�������";
$_lang["posting (and all comments) with an ID"]  = "��������� (� ������ ���������) � ID";
$_lang["Predefined selection"]  = "������������� ���������� ��������";
$_lang["Predefined value for creation of a record. Could be used in combination with a hidden field as well"]  = "������������� ���������� �������� �� ��������� �� �����. ���� �� ���� ���������� � ���������� � ������ ����";
$_lang["Previous"]  = "��������";
$_lang["print"]  = "�����������";
$_lang["Priority"]  = "���������";
$_lang["private"]  = "������";
$_lang["Profiles"]  = "�������";
$_lang["Project"]  = "������";
$_lang["Project Name"]  = "��� �� ������";
$_lang["Project summary"]  = "���������� �� �������";
$_lang["Projects"]  = "�������";
$_lang["public"]  = "��������";
$_lang["Put the word AND between several phrases"]  = "������� AND ����� ������� �����";
$_lang["Question:"]  = "������:";
$_lang["Re-Opened"]  = "������� ������";
$_lang["Read access"]  = "������ �� ������";
$_lang["Receive"]  = "����������";
$_lang["Received"]  = "��������";
$_lang["Receiver"]  = "���������";
$_lang["Record import failed because of wrong field count"]  = "������������ �� ������ �������� ������ ������ ���� ������";
$_lang["records"]  = "������";
$_lang["Regular Expression:"]  = "�������� �����:";
$_lang["rejected"]  = "���������";
$_lang["remark"]  = "���������";
$_lang["Remark"]  = "�������";
$_lang["Repeat"]  = "���������";
$_lang["Reply"]  = "��������";
$_lang["Resource"]  = "������";
$_lang["Retype new password"]  = "�������� ������ ������ ������";
$_lang["Role"]  = "����";
$_lang["Role deleted, assignment to users for this role removed"]  = "������ �������, ������������ �� ����������� ��� ���� ���� �� ����������";
$_lang["Roles"]  = "����";
$_lang["rows"]  = "������";
$_lang["Rules"]  = "�������";
$_lang["Salutation"]  = "Salutation";
$_lang["Save"]  = "���������";
$_lang["Save password"]  = "��������� �� ��������";
$_lang["schedule invisible to others"]  = "������ � ����� �� �������";
$_lang["schedule readable to others"]  = "���������� �� ������ �� ����� �� �������";
$_lang["schedule visible but not readable"]  = "���������� �� ������ �� ����� �� ������� �� �� ���� �� ���� �����";
$_lang["Search"]  = "�������";
$_lang["Select all"]  = "������ �� ������";
$_lang["Select by db query"]  = "�������� ���� ��������� ��� ���� �����";
$_lang["Select the type of this form element"]  = "�������� ���� �� �������� �� ���� �����";
$_lang["Self"]  = "�������������";
$_lang["Send date"]  = "���� �� ���������";
$_lang["Send single mails"]  = "��������� �� �������� ���������";
$_lang["Sender"]  = "��������";
$_lang["sent Mails"]  = "��������� ����";
$_lang["Settings"]  = "���������";
$_lang["several to choose?"]  = "������� �� ��������?";
$_lang["Short form"]  = "���� �����";
$_lang["Short Form"]  = "���� �����";
$_lang["Signature"]  = "������";
$_lang["Single account query"]  = "��������� �� �������� ���������";
$_lang["Single Text line"]  = "���� ��� �����";
$_lang["Skin"]  = "������";
$_lang["Skip field"]  = "�������� ����";
$_lang["solved"]  = "��������";
$_lang["Some"]  = "�������";
$_lang["Standard"]  = "����������";
$_lang["Starts in"]  = "������� �";
$_lang["starts with"]  = "������� �";
$_lang["Statistics"]  = "����������";
$_lang["Status"]  = "������";
$_lang["stopped"]  = "�����";
$_lang["Sub-Project of"]  = "��������� ��";
$_lang["Suggestion"]  = "�����������";
$_lang["Sum"]  = "����";
$_lang["Sum for"]  = "���� ��";
$_lang["Summary"]  = "������ ����������";
$_lang["Textarea"]  = "�������������";
$_lang["The bookmark is deleted"]  = "���� ������� � �������";
$_lang["The category has been created"]  = "����������� ���� ���������";
$_lang["The category has been modified"]  = "����������� ���� ���������";
$_lang["The contact has been deleted"]  = "�������� ���� ������";
$_lang["the data set is now modified."]  = "������ �� ����� � ��������.";
$_lang["The duration of the project is incorrect."]  = "����� �� ��������� �� ������� � ������.";
$_lang["the files"]  = "���������";
$_lang["the forum"]  = "�������";
$_lang["The list has been imported."]  = "������� ���� ����������.";
$_lang["The list has been rejected."]  = "������� �� ���������.";
$_lang["The new contact has been added"]  = "������ ������� ���� �������";
$_lang["The new password must have 5 letters at least"]  = "������ ������ ������ �� ��� ���� 5 �����";
$_lang["The profile has been deleted."]  = "�������� ���� ������.";
$_lang["The project has been modified"]  = "���� ������ ���� ��������";
$_lang["The project is deleted"]  = "���� ������ � ������";
$_lang["The project is now in the list"]  = "������� ���� � � �������";
$_lang["The role has been created"]  = "������ ���� ���������";
$_lang["The role has been modified"]  = "������ ���� ���������";
$_lang["The server sent an error message."]  = "�������� ������� ��������� �� ������.";
$_lang["the user is now in the list."]  = "������������ ���� � � �������.";
$_lang["these fields have to be filled in."]  = "���� ������ ������ �� ����� ���������.";
$_lang["This address already exists with a different description"]  = "���� ����� ���� ���������� � �������� ��������";
$_lang["This combination first name/family name already exists."]  = "���� ����������� ����� ���/������� ���� ����������.";
$_lang["This login name already exists! Please chosse another one."]  = "���� �������������� ��� ���� ����������! ���� �������� �����.";
$_lang["This name already exists"]  = "���� ��� �� ���� ���� ����������";
$_lang["This short name already exists!"]  = "���� ����� ��� ���� ����������!";
$_lang["Threads older than"]  = "����� ��-����� ��";
$_lang["threads older than x days are deleted."]  = "����� ��-����� �� x �� �������.";
$_lang["time-axis:"]  = "������� �����:";
$_lang["Timecard"]  = "������� �����";
$_lang["Timezone difference [h] Server - user"]  = "������� ��� ��������� ���� ������-���������� [�.]";
$_lang["to"]  = "��";
$_lang["Todays Events"]  = "������� �� ����";
$_lang["Todo"]  = " '�� �������'";
$_lang["Todo lists"]  = "'�� �������' �������";
$_lang["Tooltip"]  = "���������";
$_lang["Type"]  = "���";
$_lang["undo"]  = "��������";
$_lang["Until"]  = "��";
$_lang["Upload"]  = "�������";
$_lang["Use only normal characters and numbers, no special characters,spaces etc."]  = "����������� ���� �������� ����� � ������, ��� ��������� �����, ������ ����� ��� ��.";
$_lang["Used for fixed amount of values (separate with the pipe: | ) or for the sql statement, see element type"]  = "���������� �� �������� ���� �� ��������� (��������� �: | ) ��� �� sql statement -��, ��� ���� �� ��������";
$_lang["User"]  = "����������";
$_lang["user file deleted"]  = "������������� ���� ������";
$_lang["User management"]  = "���������� �� �������������";
$_lang["User w/Chief Rights"]  = "���������� � ����������� �����";
$_lang["Username"]  = "������������� ���";
$_lang["Valid characters"]  = "������� �����";
$_lang["Value appears in the alt tag of the blue button (mouse over) in the list view"]  = "���������� �� ������� � alt ������� �� ����� ����� � ����������� ������";
$_lang["Version"]  = "������";
$_lang["Version management"]  = "���������� �� ��������";
$_lang["vertical"]  = "����������";
$_lang["View"]  = "���";
$_lang["view mail list"]  = "����������� �� �������  ����";
$_lang["Visibility"]  = "��������";
$_lang["Voting system"]  = "������� �� ���������";
$_lang["waiting"]  = "�����";
$_lang["Warning, violation of dependency"]  = "��������, ��������� �� ������������";
$_lang["Working"]  = "�������";
$_lang["Write"]  = "�����";
$_lang["Write access"]  = "������ �� �����";
$_lang["Wrong password"]  = "������ ������";
$_lang["Yes"]  = "��";
$_lang["You are not allowed to overwrite this file since somebody else uploaded it"]  = "�� �� � ��������� �� ����������� ���� ���� ������ ����� ���� �� �� ����";
$_lang["You didnt repeat the new password correctly"]  = "�� ���������� ������ ������ ��������";
$_lang["You have to fill in the following fields: family name, short name and password."]  = "������ �� ��������� �������� ������: �������, ����� ��� � ������.";
$_lang["You should give at least one answer! "]  = "������ �� ������ ���� ���� �������! ";
$_lang["Your mail has been sent successfully"]  = "������ ���� ���� ��������� �������";
$_lang["Your new password has been stored"]  = "������ ���� ������ ���� ���������";
$_lang["Zip code"]  = "Zip ���";
?>
