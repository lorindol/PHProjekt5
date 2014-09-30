<?php
// alb.inc.php, albanian version
// translation by Veton Mavriqi <veton.mavriqi@edu.hel.fi>

$chars = array("A","B","C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z");
$name_month = array("", "Jan", "shku", "Mars", "Pri", "Maj", "Qer", "Korr", "Gush", "Shta", "Tet", "Nën", "Dhje");
$l_text31a = array("zakonisht", "15 min.", "30 min.", " 1 hour", " 2 hours", " 4 hours", " 1 day");
$l_text31b = array(0, 15, 30, 60, 120, 240, 1440);
$name_day = array("E dielë", "E hënë", "E marte", "E mërkure", "E enjëte", "E premte", "E shtune");
$name_day2 = array("Hënë", "Ma", "Mërk", "Enj", "Pre","Shtu","Diel");

$_lang['No Entries Found']= "Nuk Ka Hyrje";
$_lang['No Todays Events']= "Nuk Ka Ngjarje Për Sot";
$_lang['No new forum postings']= "NUk Ka Postime Forumi";
$_lang['in category']= "në kategori";
$_lang['Filtered']= "I filtruar";
$_lang['Sorted by']= "I klasifikuar nga";
$_lang['go'] = "Shko";
$_lang['back'] = "prapa";
$_lang['print'] = "printoje";
$_lang['export'] = "dërgoje";
$_lang['| (help)'] = "| (ndihëm)";
$_lang['Are you sure?'] = "A je i sigurt?";
$_lang['items/page'] = "Caqe/faqe";
$_lang['records'] = "ruan";
$_lang['previous page'] = "faqja e mëparshme";
$_lang['next page'] = "faqja e ardhëme";
$_lang['first page'] = "faqja e parë";
$_lang['last page'] = "faqja e fundit";
$_lang['Move']  = "zhvendose";
$_lang['Copy'] = "Kopioje";
$_lang['Delete'] = "Fëshije";
$_lang['Save'] = "Ruaje";
$_lang['Directory'] = "Dosje";
$_lang['Also Delete Contents'] = "Fëshije edhe mbrendësin";
$_lang['Sum'] = "gjith";
$_lang['Filter'] = "Filtrim";
$_lang['Please fill in the following field'] = "Mbusheni fushën ju lutem";
$_lang['approve'] = "pranoje";
$_lang['undo'] = "qbëje";
$_lang['Please select!'] = "Zgjedheni ju lutem!";
$_lang['New'] = "I ri";
$_lang['Select all'] = "Zgjedhi të gjitha";
$_lang['Printable view'] = "Dukja printuese";
$_lang['New record in module '] = "Ruajtje e re në modulë ";
$_lang['Notify all group members'] = "Vërej të gjithë antarët e rinjë të grupit";
$_lang['Yes'] = "Po";
$_lang['No'] = "Jo";
$_lang['Close window'] = "Mbylle Dritaren";
$_lang['No Value'] = "Pa vlerë"; 
$_lang['Standard'] = "Standard";  
$_lang['Create'] = "Krijoje";
$_lang['Modify'] = "Modifikoje";   
$_lang['today'] = "sot";

// admin.php
$_lang['Password'] = "Fjalëkalimi";
$_lang['Login'] = "Shkruarja";
$_lang['Administration section'] = "Sekcioni Administrativ";
$_lang['Your password'] = "Fjalëkalimi juaj";
$_lang['Sorry you are not allowed to enter. '] = "Na vjen keq ti nuk ke të drejt të shkruhesh. ";
$_lang['Help'] = "Ndihmë";
$_lang['User management'] = "Managjmenti i shfrytëzuesve";
$_lang['Create'] = "Krijo";
$_lang['Projects'] = "Projekte";
$_lang['Resources'] = "Resurset";
$_lang['Resources management'] = "Resourset e managjmentit";
$_lang['Bookmarks'] = "Shenjë dalluese";
$_lang['for invalid links'] = "për linke invalide";
$_lang['Check'] = "Kontrolloje";
$_lang['delete Bookmark'] = "fëshije një shenjë dalluese";
$_lang['(multiple select with the Ctrl-key)'] = "(mund të zgjedhësh më tepër me 'Ctrl')";
$_lang['Forum'] = "Forumi";
$_lang['Threads older than'] = "Titujt më të vjetër se";
$_lang[' days '] = " ditë ";					
$_lang['Chat'] = "Chat";
$_lang['save script of current Chat'] = "Ruaje skriptin për Chat-in e tashëm";
$_lang['Chat script'] = "Chat scripti";
$_lang['New password'] = "Fjalëkalimi i ri";
$_lang['(keep old password: leave empty)'] = "(Mbaje fjlëkalimin e vjetër: leje zbrazët)";
$_lang['Default Group<br /> (must be selected below as well)'] = "Grupi zakonshëm<br /> (duhet të zgjedhet më posht)";
$_lang['Access rights'] = "të drejtat e hyrjes";
$_lang['Zip code'] = "Kutia Postale";
$_lang['Language'] = "Gjuha";
$_lang['schedule readable to others'] = "Orari i lexuar edhe për të tjerë";
$_lang['schedule invisible to others'] = "Orari nuk i duket të tjerëve";
$_lang['schedule visible but not readable'] = "Orari duket por nuk lexohet";
$_lang['these fields have to be filled in.'] = "Këto fusha duhet të jenë të mbushura.";
$_lang['You have to fill in the following fields: family name, short name and password.'] = "Duhet të mbushen fushat që vijojn: Mbiemri, shkurtesa dhe fjalëkalimi.";
$_lang['This family name already exists! '] = "Ky mbiemër veq egziston! ";
$_lang['This short name already exists!'] = "Kjo shkurtes veq egziston!";
$_lang['This login name already exists! Please chosse another one.'] = "Emri egziston! Zgjedheni ndonjë tjetër ju lutem.";
$_lang['This password already exists!'] = "Ky fjalëkalim veq egziston!";
$_lang['This combination first name/family name already exists.'] = "Ky kombinim emri/mbiemri egziston.";
$_lang['the user is now in the list.'] = "shfrytëzuesi është tani në listë.";
$_lang['the data set is now modified.'] = "data është edituar.";
$_lang['Please choose a user'] = "Zgjedheni shfrytëzuesin ju lutem";
$_lang['is still listed in some projects. Please remove it.'] = "është ende i listuar në ndonjë projekt. largojeni ju lutem.";
$_lang['All profiles are deleted'] = "Të gjitha profilet janë të fëshira";
$_lang['A Profile with the same name already exists'] = "A profile with the same name already exists";
$_lang['is taken out of all user profiles'] = "është hequr naga të gjitha profilet";
$_lang['All todo lists of the user are deleted'] = "Të gjitha listat e detyrave të shfrytëzuesit jan hequr";
$_lang['is taken out of these votes where he/she has not yet participated'] = "është larguar nga këto vota ku ai/ajo nuk ka participuar ende";
$_lang['All events are deleted'] = "Të gjitha ngjarjet janë hequr";
$_lang['user file deleted'] = "Dosja e shfrutëzuesit është hequr";
$_lang['bank account deleted'] = "Kontoja është fëshirë";
$_lang['finished'] = "përfunduar";
$_lang['Please choose a project'] = "Zgjedheni një projekt julutem";
$_lang['The project is deleted'] = "Projekti është fëshirë";
$_lang['All links in events to this project are deleted'] = "Të gjitha ngjarjet në këtë projekt janë fëshirë";
$_lang['The duration of the project is incorrect.'] = "Zgjatja e projektit është gabim.";
$_lang['The project is now in the list'] = "Projekti është tani në listë";
$_lang['The project has been modified'] = "Projekti është ndryshuar";
$_lang['Please choose a resource'] = "Zgjedheni resursin ju lutemi ";
$_lang['The resource is deleted'] = "Resursi është fëshirë";
$_lang['All links in events to this resource are deleted'] = "Të gjitha linket në ngjarjet e këtij resursi janë fëshirë";
$_lang[' The resource is now in the list.'] = " Resursi është tani në listë.";
$_lang[' The resource has been modified.'] = " Resursi është ndryshuar.";
$_lang['The server sent an error message.'] = "Serveri ka dërguar në mesazh për gabim.";
$_lang['All Links are valid.'] = "Të gjithë linkat janë në përdorim.";
$_lang['Please select at least one bookmark'] = "Zgjedhe një bookmark ju lutem";
$_lang['The bookmark is deleted'] = "bookmarku është fëshirë";
$_lang['threads older than x days are deleted.'] = "titujt më të vjetër se x ditë janë fëshirë";
$_lang['All chat scripts are removed'] = "Të gjithë skriptat e Chat-it janë fëshirë";
$_lang['or'] = "ose";
$_lang['Timecard management'] = "Managjmenti i kartelëorës";
$_lang['View'] = "Tregoje";
$_lang['Choose group'] = "Zgjedhe grupin";
$_lang['Group name'] = "Emri i grupit";
$_lang['Short form'] = "Forum i shkurt";
$_lang['Category'] = "Kategoria";
$_lang['Remark'] = "Rishenoje";
$_lang['Group management'] = "Managjmenti i grupit";
$_lang['Please insert a name'] = "Ju lutem shkruajeni një emrër";
$_lang['Name or short form already exists'] = "Emri ose forma e shkurt egziston";
$_lang['Automatic assign to group:'] = "Bashkangjite automatikisht në grup:";
$_lang['Automatic assign to user:'] = "Bashkangjite automatikisht te shfrytëzuesi:";
$_lang['Help Desk Category Management'] = "Managjmenti i Kategorisë së Help Deskut";
$_lang['Category deleted'] = "Kategoria është hequr";
$_lang['The category has been created'] = "Kategoria është krijuar";
$_lang['The category has been modified'] = "Kategoria është ndryshuar";
$_lang['Member of following groups'] = "Antarë i grupit vijues";
$_lang['Primary group is not in group list'] = "Grupi fillestar nuk është në listë";
$_lang['Login name'] = "Emri i shfrytëzuesit";
$_lang['You cannot delete the default group'] = "Nuk mund ta fëshish grupinstandard";
$_lang['Delete group and merge contents with group'] = "Fëshije grupin dhe sheno mbrendësin në grup";
$_lang['Please choose an element'] = "Zgjedheni një element ju lutem";
$_lang['Group created'] = "Grupi është krijuar";
$_lang['File management'] = "Managjmenti i dosjes";
$_lang['Orphan files'] = "Dosjet jetime";
$_lang['Deletion of super admin root not possible'] = "Fëshirja e rrënjës së super adminit nuk është e mundur";
$_lang['ldap name'] = "ldap emri";
$_lang['mobile // mobile phone'] = "mobili"; // telefon mobil
$_lang['Normal user'] = "Shfrytëzues normal";
$_lang['User w/Chief Rights'] = "Shfrytëzuesi me drejtat e shefit";
$_lang['Administrator'] = "Administratori";
$_lang['Logging'] = "Shkruhem...";
$_lang['Logout'] = "Shkruaju jashtë";
$_lang['posting (and all comments) with an ID'] = "postoj (dhe të ghitha komentet) me një ID";
$_lang['Role deleted, assignment to users for this role removed'] = "Roli është fëshirë, bashkangjitjet për këtë rolë janë hequr";
$_lang['The role has been created'] = "Roli është krijuar";
$_lang['The role has been modified'] = "Roli është ndryshuar";
$_lang['Access rights'] = "Të drejtat në qasje";
$_lang['Usergroup'] = "Grupi i shfrytëzuesit";
$_lang['logged in as'] = "I shkruar si";

//chat.php
$_lang['Quit chat']= "Përfundoje chat-in";

//contacts.php
$_lang['Contact Manager'] = "Managjeri kontaktues";
$_lang['New contact'] = "Kontakt i ri";
$_lang['Group members'] = "Antarë të grupit";
$_lang['External contacts'] = "Kontakt i jashtëm";
$_lang['&nbsp;New&nbsp;'] = "&nbsp;I ri&nbsp;";
$_lang['Import'] = "Importoje";
$_lang['The new contact has been added'] = "Kontakti i ri është shtuar";
$_lang['The date of the contact was modified'] = "Data e kontaktit është ndryshuar";
$_lang['The contact has been deleted'] = "Kontakti është hequr";
$_lang['Open to all'] = "Hape për të gjithë";
$_lang['Picture'] = "Fotografi";
$_lang['Please select a vcard (*.vcf)'] = "Ju lutem zgjedhe një vizitëkart (*.vcf)";
$_lang['create vcard'] = "Krijo vizitëkart";
$_lang['import address book'] = "importoje adresarin";
$_lang['Please select a file (*.csv)'] = "Ju lutem zgjedhe në fajll (*.csv)";
$_lang['Howto: Open your outlook express address book and select file/export/other book<br />Then give the file a name, select all fields in the next dialog and finish'] = "Si të: Hape adresarin express në outlook dhe zgjedhe 'file'/'export'/'other book'<br />
dhe pastaj emroje fajllin, zgjedhi të gjitha fushat në dialogun e ardhëshme dhe  kliko në 'finish'";
$_lang['Open outlook at file/export/export in file,<br />choose comma separated values (Win), then select contacts in the next form,<br />give the export file a name and finish.'] = "Hape outlook-un në 'file/export/export in file',<br />
zgjedhe me mënyrën që është e ndarë me 'presje (Win)', pastaj zgjedhe 'kontaktin' në formularin e ardhëshëm,<br />
emroje fajllin që eksportoni dhe eksportoje .";
$_lang['Please choose an export file (*.csv)'] = "Zgjedheni një fajll për eksport ju lutem (*.csv)";
$_lang['Please export your address book into a comma separated value file (.csv), and either<br />1) apply an import pattern OR<br />2) modify the columns of the table with a spread sheet to this format<br />(Delete colums in you file that are not listed here and create empty colums for fields that do not exist in your file):'] = "Ju lutem eksportoni adresarin tuaj në mënyrë të ndarë me presje (.csv),<br />
dhe ndryshoj kolonat tabelën me këtë llojë të formatit:<br />
titulli, emri, mbiemri, kompania, adresa elektronike, adresa elektronike 2, telefoni 1, telefoni 2, faksi, telefoni mobil, rruga, kodi postal, qyteti, shteti, 
shteti, kategoria, shenimet , webadresa.<br /> Fëshij kolonat në fajllin tënd që nuk janë në listë dhe krijo  fusha të zbrazëta që nuk egzistojn në fajllin tënd";
$_lang['Please insert at least the family name'] = "Ju lutem vendoseni të paktën mbiemrin";
$_lang['Record import failed because of wrong field count'] = "Regjistrimi i importimit nuk pati sukses";
$_lang['Import to approve'] = "Importoje për aprovim";
$_lang['Import list'] = "Importoje listën";
$_lang['The list has been imported.'] = "Lista është importuar.";
$_lang['The list has been rejected.'] = "Lista nuk është pranuar.";
$_lang['Profiles'] = "Profilet";
$_lang['Parent object'] = "Objekti më i lartë";
$_lang['Check for duplicates during import'] = "Kontrolloje për dyfishime gajtë importit";
$_lang['Fields to match'] = "Fushat për tu përshtatur";
$_lang['Action for duplicates'] = "Aksioni për dyfishime";
$_lang['Discard duplicates'] = "Anuloje duplikatin";
$_lang['Dispose as child'] = ">Klasifikoji si fëmijë";
$_lang['Store as profile'] = "Ruaje si profilë";    
$_lang['Apply import pattern'] = "Apovoje importin pattern";
$_lang['Import pattern'] = "Importoje pattern-in";
$_lang['For modification or creation<br />upload an example csv file'] = "Për modifikim ose krijim<br />dërgoje një shembull csv fajll"; 
$_lang['Skip field'] = "Skipo fushën";
$_lang['Field separator'] = "Ndarësi i fushës";
$_lang['Contact selector'] = "Selektori Kontaktues";
$_lang['Use doublet'] = "Përdore dubletin";
$_lang['Doublets'] = "Dublet (Kopiues)";

// filemanager.php
$_lang['Please select a file'] = "Ju lutem zgjedheni një fajll";
$_lang['A file with this name already exists!'] = "Fajlli me këtë emër veq egziston!";
$_lang['Name'] = "Emri";
$_lang['Comment'] = "Komenti";
$_lang['Date'] = "Data";
$_lang['Upload'] = "Dërgoje";
$_lang['Filename and path'] = "Emri i fajllit dhe shtegu";
$_lang['Delete file'] = "Fëshije fajllin";
$_lang['Overwrite'] = "Mbishkruaj";
$_lang['Access'] = "Hyrja";
$_lang['Me'] = "unë";
$_lang['Group'] = "të gjithë";
$_lang['Some'] = "disa";
$_lang['As parent object'] = "disa dosje";
$_lang['All groups'] = "Të gjitha grupet";
$_lang['You are not allowed to overwrite this file since somebody else uploaded it'] = "Nuk ke të drejt të mbishkruash kur dikush tjetër e ka dërguar";
$_lang['personal'] = "personale";
$_lang['Link'] = "Linku";
$_lang['name and network path'] = "Emri dhe shtegu i rrjetit";
$_lang['with new values'] = "Me vlera të reja";
$_lang['All files in this directory will be removed! Continue?'] = "Të gjithë fajllat në këtë dosje do të fëshihen! Vazhdo?";
$_lang['This name already exists'] = "Ky emër veq egziston";
$_lang['Max. file size'] = "Max. madhësia e fajllit";
$_lang['links to'] = "linkat deri te";
$_lang['objects'] = "objektet";
$_lang['Action in same directory not possible'] = "Akcioni në të njejtën dosje nuk është i mundur";
$_lang['Upload = replace file'] = "Dërgoje = zavendësoje fajllin";
$_lang['Insert password for crypted file'] = "Shkruaje fjalëkalimin për crypted file";
$_lang['Crypt upload file with password'] = "Kripto dërgoje fajllin me fjalëkalim";
$_lang['Repeat'] = "Përsërite";
$_lang['Passwords dont match!'] = "Fjalëkalimet nuk përputhen!";
$_lang['Download of the password protected file '] = "Shkarkimi i fjalëkalimit është fajll i ruajtur ";
$_lang['notify all users with access'] = "vërej të gjithë shfrytëzuesit me të drejt hyrje";
$_lang['Write access'] = "drejta e shkrimit";
$_lang['Version'] = "Verzioni";
$_lang['Version management'] = "Verzioni managjues";
$_lang['lock'] = "ngujoje";
$_lang['unlock'] = "Çngujoje";
$_lang['locked by'] = "ngujuar nga";
$_lang['Alternative Download'] = "Shkarkim alternativ";
$_lang['Download'] = "Shkarkoje";
$_lang['Select type'] = "Zgjedhe tipin";
$_lang['Create directory'] = "Krijoje një Dosje";
$_lang['filesize (Byte)'] = "Madhësia e Fajllit (Byte)";

// filter
$_lang['contains'] = 'përmban';
$_lang['exact'] = 'saktësisht';
$_lang['starts with'] = 'fillon me';
$_lang['ends with'] = 'përfundon me';
$_lang['>'] = '>';
$_lang['>='] = '>=';
$_lang['<'] = '<';
$_lang['<='] = '<=';
$_lang['does not contain'] = 'nuk përmban'; 
$_lang['Please set (other) filters - too many hits!'] = "Ju lutemi vendosëni (tjetër) filter - shumë të qëlluara!";

$_lang['Edit filter'] = "Editoje filterin";
$_lang['Filter configuration'] = "Konfigurimi i Filterit";
$_lang['Disable set filters'] = "Pamundësoje vendosjen e filtereve";
$_lang['Load filter'] = "Mbusheni filterin";
$_lang['Delete saved filter'] = "Fshije filterin e ruajtur";
$_lang['Save currently set filters'] = "Ruaj filterët e vendosur së fundi";
$_lang['Save as'] = "Ruaje si";
$_lang['News'] = 'Risi';

// form designer
$_lang['Module Designer'] = "dizajner i modulit";
$_lang['Module element'] = "Element i modulit"; 
$_lang['Module'] = "Moduli";
$_lang['Active'] = "Aktiv";
$_lang['Inactive'] = "Inaktiv";
$_lang['Activate'] = "Aktivoje";
$_lang['Deactivate'] = "Çaktivoje"; 
$_lang['Create new element'] = "Krijo një element të ri";
$_lang['Modify element'] = "Ndryshoje një element";
$_lang['Field name in database'] = "Emri i fushës në dejtabejs";
$_lang['Use only normal characters and numbers, no special characters,spaces etc.'] = "Shfrytëzo vetën karaktere normale dhe numra, pa karaktere speciale,zbraztësirë, ë, etj.";
$_lang['Field name in form'] = "Emri i fushës në form";
$_lang['(could be modified later)'] = "(mund të modifikohet më vonë)"; 
$_lang['Single Text line'] = "Linje e tekstit të thjesht";
$_lang['Textarea'] = "Zona e tekstit";
$_lang['Display'] = "Shfaqe";
$_lang['First insert'] = "Insertimi i parë";
$_lang['Predefined selection'] = "Selekcioni i paradefinuar";
$_lang['Select by db query'] = "Zgjedh sipas db kërkesës";
$_lang['File'] = "Fajll";

$_lang['Email Address'] = "Adresa elektronike";
$_lang['url'] = "url";
$_lang['Checkbox'] = "Checkbox";
$_lang['Multiple select'] = "Zgjedhje e shumëfisht"; 
$_lang['Display value from db query'] = "Trego vlerën prej db kërkesës";
$_lang['Time'] = "Koha";
$_lang['Tooltip'] = "Tooltip"; 
$_lang['Appears as a tipp while moving the mouse over the field: Additional comments to the field or explanation if a regular expression is applied'] = "Paraqitet si amulim kur të lëviz miun në fushë: Additional comments to the field or explanation if a regular expression is applied";
$_lang['Position'] = "Pozicioni";
$_lang['is current position, other free positions are:'] = "është pozicioni momental, pozicionet e tjera të lira janë:"; 
$_lang['Regular Expression:'] = "Shfaqje e rregullt:";
$_lang['Please enter a regular expression to check the input on this field'] = "ju lutem vendose frazën e rregullt për të kontrolluar hyrjen për këtë fushë";
$_lang['Default value'] = "Vlerea e rregullt";
$_lang['Predefined value for creation of a record. Could be used in combination with a hidden field as well'] = "Paradefinimi për të krijuar një rekord. Mund të përdoret në kombinim me fushat e fshehura poashtu";
$_lang['Content for select Box'] = "Content for select Box";
$_lang['Used for fixed amount of values (separate with the pipe: | ) or for the sql statement, see element type'] = "Përdorur për shuma të caktuara të vlerës (ndaje me një pipe: | ) ose për sql statement, see element type";
$_lang['Position in list view'] = "Pozita për shikimin e listës";                                                                                     
$_lang['Only insert a number > 0 if you want that this field appears in the list of this module'] = "Vetëm vendose një numër > 0 Nëse dëshiron që kjo fushë të paraqitet në listën e këtij moduli";
$_lang['Alternative list view'] = "Shikimi i listës alternative";
$_lang['Value appears in the alt tag of the blue button (mouse over) in the list view'] = "Vlera paraqitet në trarin alt në sustën e kaltër (mouse over) në shikimin e listës";
$_lang['Filter element'] = "Elementi filtërues";                                                                                   
$_lang['Appears in the filter select box in the list view'] = "Paraqitet në filter select box në shikimin e listës";
$_lang['Element Type'] = "Tipi i elementit";
$_lang['Select the type of this form element'] = "Zgjedhe tipin për këtë element të formës";
$_lang['Check the content of the previous field!'] = "Kontrolloje përmbajtjen e fushës paraprake!";
$_lang['Span element over'] = "Mbetja e periudhës elementare";
$_lang['columns'] = "kolonat";
$_lang['rows'] = "rreshtat";
$_lang['Telephone'] = "Telefoni";
$_lang['History'] = "Historia";
$_lang['Field'] = "Fusha";
$_lang['Old value'] = "Vlera e vjetër";
$_lang['New value'] = "Vlera e re";
$_lang['Author'] = "Autori"; 
$_lang['Show Date'] = "Tregoje datën";
$_lang['Creation date'] = "Data e krijimit";
$_lang['Last modification date'] = "Data e modifikimit të fundit";
$_lang['Email (at record cration)'] = "Email:i (në krijimin e rekordit)";
$_lang['Contact (at record cration)'] = "Kontakti (në krijimin e rekordit)"; 
$_lang['Select user'] = "Zgjedhe një shfrytëzues";
$_lang['Show user'] = "Tregoje shfrytëzuesin";

// forum.php
$_lang['Please give your thread a title'] = "Please give your thread a title";
$_lang['New Thread'] = "Rrezik i ri";
$_lang['Title'] = "Titulli";
$_lang['Text'] = "Tekst";
$_lang['Post'] = "Postë";
$_lang['From'] = "Prej";
$_lang['open'] = "hapur";
$_lang['closed'] = "mbyllur";
$_lang['Notify me on comments'] = "Më lajmëro në komente";
$_lang['Answer to your posting in the forum'] = "Përgjigju në postat tua në forum";
$_lang['You got an answer to your posting'] = "Ke një përgjigje në postën tënde \n ";
$_lang['New posting'] = "Postim i ri";
$_lang['Create new forum'] = "Krijo një forum të ri";
$_lang['down'] ='poshtë';
$_lang['up']= "lartë";
$_lang['Forums']= "Forume";
$_lang['Topics']="Tema";
$_lang['Threads']="kërcnime";
$_lang['Latest Thread']="Kërcnimi i fundit";
$_lang['Overview forums']= "Forume përshkruese";
$_lang['Succeeding answers']= "Përgjigje të suksesëshme";
$_lang['Count']= "Konto";
$_lang['from']= "prej";
$_lang['Path']= "Shtegu";
$_lang['Thread title']= "Titull kërcnues";
$_lang['Notification']= "Lajmërim";
$_lang['Delete forum']= "Fëshij forumin";
$_lang['Delete posting']= "Fëshij postimin";
$_lang['In this table you can find all forums listed']= "Në këtë tabelë mund të gjesh të gjitha forumet e listuara";
$_lang['In this table you can find all threads listed']= "Në këtë tabelë mund të gjesh të gjitha rreziqet e listuara";

// index.php
$_lang['Last name'] = "Mbiemri";
$_lang['Short name'] = "Emri i shkurt";
$_lang['Sorry you are not allowed to enter.'] = "Na vjen keq ju nuk keni leje të hyni.";
$_lang['Please run index.php: '] = "Ju lutem startoje index.php: ";
$_lang['Reminder'] = "Kujtuesi";
$_lang['Session time over, please login again'] = "Sesioni kohor mbaroi, Ju lutemi hyni shkruaju përsëri";
$_lang['&nbsp;Hide read elements'] = "&nbsp;Fshehi elementet lexuese";
$_lang['&nbsp;Show read elements'] = "&nbsp;Tregoj elementet lexuese";
$_lang['&nbsp;Hide archive elements'] = "&nbsp;Fshehi elementet arkivore";
$_lang['&nbsp;Show archive elements'] = "&nbsp;Tregoj elementet arkivore";
$_lang['Tree view'] = "Shikimi i degës";
$_lang['New todo'] = "Një todo e re";
$_lang['New note'] = "Një notë e re";
$_lang['New document'] = "Dokument i ri";
$_lang['Set bookmark'] = "Vendose një bukmark";
$_lang['Move to archive'] = "Vendose në arkiv";
$_lang['Mark as read'] = "Shenoje si te¨lexuar";
$_lang['Export as csv file'] = "Eksportoje si csv fajll";
$_lang['Deselect all'] = "Diselktoji të gjitha";
$_lang['selected elements'] = "elementet e selektuara";
$_lang['wider'] = "i zgjeruar";
$_lang['narrower'] = "i ngushtuar";
$_lang['ascending'] = "i ngritshëm";
$_lang['descending'] = "i ulshëm";
$_lang['Column'] = "Kolonë";
$_lang['Sorting'] = "Sortues";
$_lang['Save width'] = "Ruaje gjersinë";
$_lang['Width'] = "Gjerësia";
$_lang['switch off html editor'] = "çkyçe html editorin";
$_lang['switch on html editor'] = "kyçe html editorin";
$_lang['hits were shown for'] = "gjetje të treguara për";
$_lang['there were no hits found.'] = "Nuk ka asnjë gjetje.";
$_lang['Filename'] = "Emri i fajllit";
$_lang['First Name'] = "Emri";
$_lang['Family Name'] = "Mbiemri";
$_lang['Company'] = "Kompania";
$_lang['Street'] = "Rruga";
$_lang['City'] = "Qyteti";
$_lang['Country'] = "Shteti";
$_lang['Please select the modules where the keyword will be searched'] = "Ju lutemi zgjedhe modulin nga duhet të kërkohet fjala kyçe";
$_lang['Enter your keyword(s)'] = "Vendoseni fjalën(t) kyçe";
$_lang['Salutation'] = "Përshëndetja";
$_lang['State'] = "Sheti";
$_lang['Add to link list'] = "Shtoje në listën e linqeve";    

// setup.php
$_lang['Welcome to the setup of PHProject!<br />'] = "Mirë se vjen në instalimin e PHProject!<br />";
$_lang['Please remark:<ul><li>A blank database must be available<li>Please ensure that the webserver is able to write the file config.inc.php'] = "Ju lutem rishenoje:<ul>
<li>Një data bejs e zbrasët
<li>Ju lutem siguroni që webserveri është i lirë për të shkruar 'config.inc.php'";
$_lang['<li>If you encounter any errors during the installation, please look into the <a href=help/faq_install.html target=_blank>install faq</a>or visit the <a href=http://www.PHProjekt.com/forum.html target=_blank>Installation forum</a></i>'] = "<li>Nëse hasni në ndonjë penges gjatë instalimit ju lutem kërkoni në: <a href='help/faq_install.html' target=_blank>install faq</a>
ose vizitoni  <a href='http://www.PHProjekt.com/forum.html' target=_blank>Forumi i instalimit</a></i>";
$_lang['Please fill in the fields below'] = "Ju lutem mbushni fushat më posht";
$_lang['(In few cases the script wont respond.<br />Cancel the script, close the browser and try it again).<br />'] = "(Në disa raste skripti nuk përgjigjet.<br />
Anuloje skriptin, Mbylle programin dhe provo edhe një herë).<br />";
$_lang['Type of database'] = "Tipi i data bejsit";
$_lang['Hostname'] = "Hostname";
$_lang['Username'] = "Username";

$_lang['Name of the existing database'] = "Emri i data bejsit egzistues";
$_lang['config.inc.php not found! Do you really want to update? Please read INSTALL ...'] = "config.inc.php nuk gjindet! A dëshironi të freskoni? Ju lutem lexoni INSTALL ...";
$_lang['config.inc.php found! Maybe you prefer to update PHProject? Please read INSTALL ...'] = "config.inc.php u gjend! Ndoshta ti preferon që PHProjekti të freskohet? Ju lutem lexoni INSTALL ...";
$_lang['Please choose Installation,Update or Configure!'] = "Ju lutem zgjedheni 'Instalimin','Freskimin' ose 'Konfigurimin'!";
$_lang['Sorry, I cannot connect to the database! <br />Please close all browser windows and restart the installation.'] = "Më vjen keq, Nuk mund të lidhi te database! <br />Ju lutem rregullojeni dhe filloni instalimin përsëri.";
$_lang['Sorry, it does not work! <br /> Please set DBDATE to Y4MD- or let phprojekt change this environment-variable (php.ini)!'] = "Më vjen keq por nuk punon! <br /> Ju lutem vendoseni DBDATE në 'Y4MD-' ose lëre phprojektin të ndërroj environment-variable (php.ini)!";
$_lang['Seems that You have a valid database connection!'] = "Si duket ju keni një lidhej valide!";
$_lang['Please select the modules you are going to use.<br /> (You can disable them later in the config.inc.php)<br />'] = "Ju lutem zgjedhni modulet që do ti përdorni.<br /> (You can disable them later in the config.inc.php)<br />";
$_lang['Install component: insert a 1, otherwise keep the field empty'] = "Instaloj komponentet: Vendose a '1', përndryshe mbaje fushën të zbrazët";
$_lang['Group views'] = "Dukja e Grupeve";
$_lang['Todo lists'] = "Lista e detyrave";

$_lang['Voting system'] = "Sistemi i votimit";


$_lang['Contact manager'] = "Managjeri kontaktues";
$_lang['Name of userdefined field'] = "Emri i fushës së lirë të shfrytëzuesit";
$_lang['Userdefined'] = "Definimi i shfrytëzuesit";
$_lang['Profiles for contacts'] = "Profilet përe kontaktim";
$_lang['Mail'] = "Posta";
$_lang['send mail'] = " dërgo e-mail";
$_lang[' only,<br /> &nbsp; &nbsp; full mail client'] = " vetëm,<br /> &nbsp; &nbsp; klient i plotë postal";



$_lang['1 to show appointment list in separate window,<br />&nbsp; &nbsp; 2 for an additional alert.'] = "'1' për të parë listën e takimeve në dritare të ndarë,<br />
&nbsp; &nbsp; '2' për lajmërin të ndarë.";
$_lang['Alarm'] = "Alarmi";
$_lang['max. minutes before the event'] = "max. minuta para ngjarjes";
$_lang['SMS/Mail reminder'] = "SMS/Mail kujtues";
$_lang['Reminds via SMS/Email'] = "kujtues përmes SMS/Email";
$_lang['1= Create projects,<br />&nbsp; &nbsp; 2= assign worktime to projects only with timecard entry<br />&nbsp; &nbsp; 3= assign worktime to projects without timecard entry<br />&nbsp; &nbsp; (Selection 2 or 3 only with module timecard!)'] = "'1'= Krijo projekte,<br />
&nbsp; &nbsp; '2'= shto kohën e punës në projekte vetëm me hyrje me kartel kohe<br />
&nbsp; &nbsp; '3'= shto kohën e punës në projekte me hyrje pa kartel kohe<br />&nbsp; &nbsp; (Selection '2' ose '3' vetëm me modul kartelë kohore!)";

$_lang['Name of the directory where the files will be stored<br />( no file management: empty field)'] = "Emri i dosjes ku fajlli do të ruhet<br />( no file management: empty field)";
$_lang['absolute path to this directory (no files = empty field)'] = "absolute path to this directory (no files = empty field)";
$_lang['Time card'] = "Kartela kohore";
$_lang['1 time card system,<br />&nbsp; &nbsp; 2 manual insert afterwards sends copy to the chief'] = "'1' sistemi i kartelës kohore,<br />
&nbsp; &nbsp; '2' Vendosja manuale pasi i dërgon shefit një mesazh";
$_lang['Notes'] = "Shenime";
$_lang['Password change'] = "Ndërrimi i fjalëkalimit";
$_lang['New passwords by the user - 0: none - 1: only random passwords - 2: choose own'] = "Fjalëkalimet e reja prej shfrytëzuesit - 0: asgjë - 1: vetëm fjalëkalime tërëndomta - 2: zgjedhe tëndën";
$_lang['Encrypt passwords'] = "Encrypt fjalëkalimet";
$_lang['Login via '] = "Shkruaju përmes ";
$_lang['Extra page for login via SSL'] = "Extra faqe për shkruarje në SSL";
$_lang['Groups'] = "Grupet";
$_lang['User and module functions are assigned to groups<br />&nbsp; &nbsp; (recommended for user numbers > 40)'] = "Funksionet e grupeve dhe shfrytëzuesve janë shënuara<br />
&nbsp; &nbsp; (recommended for user numbers > 40)";
$_lang['User and module functions are assigned to groups'] = "Shfrytëzuesit dhe funksionet e moduleve janë të shënuara në grupe";
$_lang['Help desk'] = "Help desk";
$_lang['Help Desk Manager / Trouble Ticket System'] = "Help Desk Manager / Trouble Ticket System";
$_lang['RT Option: Customer can set a due date'] = "RT Option: Klienti mund të vendos një datë skalimi";
$_lang['RT Option: Customer Authentification'] = "RT Option: Autorizimi i klientit";
$_lang['0: open to all, email-address is sufficient<br />1: registered contact enter his family name<br />2: his email'] = "0: Nëse nevojitet hape për të gjitha e-mail adresat, 1: Klienti duhet të jet në listën kontaktuese dhe të shenoj mbiemrin";
$_lang['RT Option: Assigning request'] = "RT Option: Kërkesë lajmërimi";
$_lang['0: by everybody, 1: only by persons with status chief'] = "0: prej çdo kujt, 1: vetëm prej personave me statusin 'chief'";
$_lang['Email Address of the support'] = "Adresa elektronike e ndihëmsit";
$_lang['Scramble filenames'] = "Fajll-emra të dëmtuar";
$_lang['creates scrambled filenames on the server<br />assigns previous name at download'] = "Krijon fajll-emra të dëmtuar në server<br />
shton emrin e mëparshëm në shkarkim";

$_lang['0: last name, 1: short name, 2: login name'] = "0: mbiemri, 1: pseudenimi, 2: emri shfrytzues";
$_lang['Prefix for table names in db'] = "Prefiksi për tabelën emëruese në db";
$_lang['Alert: Cannot create file config.inc.php!<br />Installation directory needs rwx access for your server and rx access to all others.'] = "Rrezikë: Nuk mund të krijohet 'config.inc.php'!<br />
Installation directory needs rwx access for your server and rx access to all others.";
$_lang['Location of the database'] = "Lokacioni i databejsit";
$_lang['Type of database system'] = "Tipi i sistemit të databejsit";
$_lang['Username for the access'] = "Emri për hyrje";
$_lang['Password for the access'] = "Fjalëkalimi për hyrje";
$_lang['Name of the database'] = "Emri i databejsit";
$_lang['Prefix for database table names'] = "Prefiksi për data bejsin në tabelën e emrave";
$_lang['First background color'] = "Ngjyra e sfondës së parë";
$_lang['Second background color'] = "Ngjyra e sfondës së dytë";
$_lang['Third background color'] = "Ngjyra e sfondës së tretë";
$_lang['Color to mark rows'] = "Ngjyra e rreshtave të shënuar";
$_lang['Color to highlight rows'] = "Ngjyra e rreshtave të titujve";
$_lang['Event color in the tables'] = "Ngjyra e ngjarjes në tabelë";
$_lang['company icon yes = insert name of image'] = "ikoni i kompanisë po = shkruaje emrin e fig";
$_lang['URL to the homepage of the company'] = "URL për në faqet e kompanisë";
$_lang['no = leave empty'] = "jo = lere të zbrazët";
$_lang['First hour of the day:'] = "ora e parë e ditës:";
$_lang['Last hour of the day:'] = "ora e fundit e ditës:";
$_lang['An error ocurred while creating table: '] = "u shfaq një gabim gjatë krijimit të tabelës: ";
$_lang['Table dateien (for file-handling) created'] = "Tabela 'dateien' (for file-handling) krijuar";
$_lang['File management no = leave empty'] = "fajll managjmenti jo = lere zbrazët";
$_lang['yes = insert full path'] = "po = shkruaje shtegun e plotë";
$_lang['and the relative path to the PHProjekt directory'] = "dhe shtegun relativë deri te PHProjekti ";
$_lang['Table profile (for user-profiles) created'] = "Tabela 'profile' (for user-profiles) është krijuar";
$_lang['User Profiles yes = 1, no = 0'] = "Profilet e shfrytëzuesit po = 1, jo = 0";
$_lang['Table todo (for todo-lists) created'] = "Tabela 'todo' (for todo-lists) është krijuar";
$_lang['Todo-Lists yes = 1, no = 0'] = "Lista-e detyrave = 1, jo = 0";
$_lang['Table forum (for discssions etc.) created'] = "Tabela 'forum' (for discssions etc.) është krijuar";
$_lang['Forum yes = 1, no = 0'] = "Forumi po = 1, jo = 0";
$_lang['Table votum (for polls) created'] = "Tabela 'votum' (for votes) është krijuar";
$_lang['Voting system yes = 1, no = 0'] = "Sistemi i votimit po = 1, jo = 0";
$_lang['Table lesezeichen (for bookmarks) created'] = "Tabela 'lesezeichen' (për buk-mark) është krijuar";
$_lang['Bookmarks yes = 1, no = 0'] = "Bookmarks po = 1, jo = 0";
$_lang['Table ressourcen (for management of additional ressources) created'] = "Tabela 'ressourcen' (for management of additional ressources) është krijuar";
$_lang['Resources yes = 1, no = 0'] = "Resurset po = 1, jo = 0";
$_lang['Table projekte (for project management) created'] = "Tabela 'projekte' (for project management) është krijuar";
$_lang['Table contacts (for external contacts) created'] = "Tabela e kontakteve (for external contacts) është krijuar";
$_lang['Table notes (for notes) created'] = "Tabela për shënime (for notes) është krijuar";
$_lang['Table timecard (for time sheet system) created'] = "Tabela e kohëkartelës (for time sheet system) është krijuar";
$_lang['Table groups (for group management) created'] = "Tabela e grupeve (for group management) krijuar";
$_lang['Table timeproj (assigning work time to projects) created'] = "Tabela timeproj (assigning work time to projects) është krijuar";
$_lang['Table rts and rts_cat (for the help desk) created'] = "Tabela rts and rts_cat (for the help desk) është krijuar";
$_lang['Table mail_account, mail_attach, mail_client und mail_rules (for the mail reader) created'] = "Tabela mail_account, mail_attach, mail_client und mail_rules (for the mail reader) është krijuar";
$_lang['Table logs (for user login/-out tracking) created'] = "Table logs (for user login/-out tracking) është krijuar";
$_lang['Tables contacts_profiles und contacts_prof_rel created'] = "Tables contacts_profiles und contacts_prof_rel është krijuar";
$_lang['Project management yes = 1, no = 0'] = "Project management po = 1, jo = 0";
$_lang['additionally assign resources to events'] = "bashkangjit sipas dëshirës resurse në ngjarje";
$_lang['Address book  = 1, nein = 0'] = "Adresari  = 1, nein = 0";
$_lang['Mail no = 0, only send = 1, send and receive = 2'] = "Mail jo = 0, vetëm dërgo = 1, dërgo dhe prano = 2";
$_lang['Chat yes = 1, no = 0'] = "Chati po = 1, jo = 0";
$_lang['Name format in chat list'] = "Emri i formatit në listën e Chat-it";
$_lang['0: last name, 1: first name, 2: first name, last name,<br /> &nbsp; &nbsp; 3: last name, first name'] = "0: mbiemri, 1: emri, 2: emri, mbiemri,<br /> &nbsp; &nbsp; 3: mbiemri, emri";
$_lang['Timestamp for chat messages'] = "vula kohore për mesazhet e Chat-it";
$_lang['users (for authentification and address management)'] = "'shfrytëzuesit' (for authentification and address management)";
$_lang['Table termine (for events) created'] = "'termini tabelar' (for events) është krijuar";
$_lang['The following users have been inserted successfully in the table user:<br />root - (superuser with all administrative privileges)<br />test - (chief user with restricted access)'] = "Këta shfrytëzues janë shtuar në mënyrë të suksesëshme në tabelë 'user':<br />
'root' - (superuser with all administrative privileges)<br />
'test' - (chief user with restricted access)";
$_lang['The group default has been created'] = "Grupi 'default' është krijuar";
$_lang['Please do not change anything below this line!'] = "Mos ndryshoni asgjë nënë këtë linje!";
$_lang['Database error'] = "Gabim në databejs";
$_lang['Finished'] = "Përfunduar";
$_lang['There were errors, please have a look at the messages above'] = "Ka pasur gabime: Ju lutem lexoni mesazhin më poshtë";
$_lang['All required tables are installed and <br />the configuration file config.inc.php is rewritten<br />It would be a good idea to makea backup of this file.<br />'] = "Të gjitha tabelat e kërkuara janë instaluar dhe <br />
konfigurimi i fajllit 'config.inc.php' është rishkruar<br />
Do të ishte ide e mirë të bëni
një backup të këtij fajlli.<br />";
$_lang['The administrator root has the password root. Please change his password here:'] = "Administratori e ka fjalëkalimin 'root'. Ju lutem ndërroje fjalëkalimin e tij këtu:";
$_lang['The user test is now member of the group default.<br />Now you can create new groups and add new users to the group'] = "Shfrytëzuesi 'test' është tani antarë i grupit 'default'.<br />
Tani mund të krijosh grupe të reja dhe të shtosh antarë në grupin e ri";
$_lang['To use PHProject with your Browser go to <b>index.php</b><br />Please test your configuration, especially the modules Mail and Files.'] = "Për të përdor PHProject me programin tuaj shko në <b>index.php</b><br />
Testoje konfigurimin tuaj ju lutem, posaqrisht modulet 'Mail' dhe 'Files'.";

$_lang['Alarm x minutes before the event'] = "Alarmi x minuta para ngjarjes";
$_lang['Additional Alarmbox'] = "Mundësit e dritarës së alarmit";
$_lang['Mail to the chief'] = "Postojë shefit";
$_lang['Out/Back counts as: 1: Pause - 0: Workingtime'] = "Jasht/Prapa numrimi si: 1: Pauz - 0: Kohë punuese";
$_lang['Passwords will now be encrypted ...'] = "Fjalëkalimi do të kryptohet ...";
$_lang['Filenames will now be crypted ...'] = "Fajllat do të kryptohen tani...";
$_lang['Do you want to backup your database right now? (And zip it together with the config.inc.php ...)<br />Of course I will wait!'] = "A dëshironi ta ruani ddatabejsin tani? (Dhe paketojeni së bashku me config.inc.php ...)<br />
Natyrisht do të pres!";
$_lang['Next'] = "Tjetra";
$_lang['Notification on new event in others calendar'] = "Vëreje shtimin e ngjarjes së re në kalendarin e të tjerëve";
$_lang['Path to sendfax'] = "Shtegu për të dërguar Fax";
$_lang['no fax option: leave blank'] = "Ska faks: Lere të zbrazët";
$_lang['Please read the FAQ about the installation with postgres'] = "Lexoje faq-in rrethë instalimit me postgres";
$_lang['Length of short names<br /> (Number of letters: 3-6)'] = "Gjatësia e pseudenimeve<br /> (Number of letters: 3-6)";
$_lang['If you want to install PHProjekt manually, you find<a href=http://www.phprojekt.com/files/sql_dump.tar.gz target=_blank>here</a> a mysql dump and a default config.inc.php'] = "Nëse do të instalosh PHProjekt në mënyrë manuale, mund të gjeni
<a href='http://www.phprojekt.com/files/sql_dump.tar.gz' target=_blank>Këtu</a> a mysql dump and a default config.inc.php";
$_lang['The server needs the privilege to write to the directories'] = "Serverit i duhen të drejtat për të 'shkruar' në dosje";
$_lang['Header groupviews'] = "Emri në grup";
$_lang['name, F.'] = "emri, ";
$_lang['shortname'] = "shkurtesa";
$_lang['loginname'] = "identifikimi";
$_lang['Please create the file directory'] = "Ju lutem krijoni një fajll direktivë";
$_lang['default mode for forum tree: 1 - open, 0 - closed'] = "forma standarde për trarin e forumit: 1 - hapur, 0 - mbyllur";
$_lang['Currency symbol'] = "Simboli i valutës";
$_lang['current'] = "aktuale";
$_lang['Default size of form elements'] = "Madhësia standarde e elementeve në form";
$_lang['use LDAP'] = "përdor LDAP";
$_lang['Allow parallel events'] = "Lejo ngjarjet paralele";
$_lang['Timezone difference [h] Server - user'] = "Diferenca në zonat kohore [h] Serveri - shfrytëzuesi";
$_lang['Timezone'] = "Zona kohore";
$_lang['max. hits displayed in search module'] = "max. Kërime paraqitura në modulin e kërkimit";
$_lang['Time limit for sessions'] = "Kohë e kufizuar për sesione";
$_lang['0: default mode, 1: Only for debugging mode'] = "0: metoda standarde, 1: vetëm për metoda të pagabueshme";
$_lang['Enables mail notification on new elements'] = "Lejon lajmërimin e postës në elementet e reja";
$_lang['Enables versioning for files'] = "Lejon versionin për fajlla";
$_lang['no link to contacts in other modules'] = "ska linke për kontakt në modulet e tjera";
$_lang['Highlight list records with mouseover'] = "Ngrite listën e zgjedhjeve me 'ngritje të miut'";
$_lang['Track user login/logout'] = "Përcjelle shfrytzuesin kur hyn/largohet";
$_lang['Access for all groups'] = "Hyrja për të gjitha grupet";
$_lang['Option to release objects in all groups'] = "Opcione për të lëshuar objekte në të gjitha grupet";
$_lang['Default access mode: private=0, group=1'] = "Metoda e qasjes standarde: private=0, group=1"; 
$_lang['Adds -f as 5. parameter to mail(), see php manual'] = "Shton '-f' si 5. parametri në postë(), see php manual";
$_lang['end of line in body; e.g. \r\n (conform to RFC 2821 / 2822)'] = "përfundimi i vijës në trup; e.g. \\r\\n (conform to RFC 2821 / 2822)";
$_lang['end of header line; e.g. \r\n (conform to RFC 2821 / 2822)'] = "fund i rreshtit të titullit; e.g. \\r\\n (conform to RFC 2821 / 2822)";
$_lang['Sendmail mode: 0: use mail(); 1: use socket'] = "Metoda e dërgimit të postës: 0: use mail(); 1: use socket";
$_lang['the real address of the SMTP mail server, you have access to (maybe localhost)'] = "Adresa e vërtet e serverit për SMTP mail , ju keni qasje në (maybe localhost)";
$_lang['name of the local server to identify it while HELO procedure'] = "emri i serverit lokal për të identifikuar të while HELO procedura";
$_lang['Authentication'] = "Autorizimi";
$_lang['fill out in case of authentication via POP before SMTP'] = "mbushe në rast të autorizimit të POP para SMTP";
$_lang['real username for POP before SMTP'] = "emri i vërtet për POP para SMTP";
$_lang['password for this pop account'] = "fjalëkalimi për pop llogarin"; 
$_lang['the POP server'] = "serveri pop";
$_lang['fill out in case of SMTP authentication'] = "mbushe në rastë të SMTP autorizimi";
$_lang['real username for SMTP auth'] = "emri i vërtet për SMTP auth";
$_lang['password for this account'] = "fjalëkalimi për këtë llogari";
$_lang['SMTP account data (only needed in case of socket)'] = "SMTP account data (only needed in case of socket)";
$_lang['No Authentication'] = "Nuk ke autorizim"; 
$_lang['with POP before SMTP'] = "me POP para SMTP";
$_lang['SMTP auth (via socket only!)'] = "SMTP autor (via socket only!)";
$_lang['Log history of records'] = "Shkruaj historin e rekordeve";
$_lang['Send'] = " dërguar";
$_lang['Host-Path'] = "Shegu nikoqirë";
$_lang['Installation directory'] = "Dosja e instalimit";
$_lang['0 Date assignment by chief, 1 Invitation System'] = "0 Data e nënshkruar nga chief, 1 Sistemi ftues";
$_lang['0 Date assignment by chief,<br />&nbsp;&nbsp;&nbsp;&nbsp; 1 Invitation System'] = "0 Date assignment by chief,<br />&nbsp;&nbsp;&nbsp;&nbsp; 1 Invitation System";
$_lang['Default write access mode: private=0, group=1'] = "Default write access mode: private=0, group=1";
$_lang['Select-Option accepted available = 1, not available = 0'] = "Select-Option accepted available = 1, not available = 0";
$_lang['absolute path to host, e.g. http://myhost/'] = "absolute path to host, e.g. http://myhost/";
$_lang['installation directory below host, e.g. myInstallation/of/phprojekt5/'] = "installation directory below host, e.g. myInstallation/of/phprojekt5/";

// l.php
$_lang['Resource List'] = "Lista e resurseve";
$_lang['Event List'] = "Lista e ngjarjeve";
$_lang['Calendar Views'] = "Pamjet e kalendarit";

$_lang['Personnel'] = "Personeli";
$_lang['Create new event'] = "Krijo një ngjarje";
$_lang['Day'] = "Dita";

$_lang['Until'] = "Deri";

$_lang['Note'] = "Shënim";
$_lang['Project'] = "Projekti";
$_lang['Res'] = "Resursi";
$_lang['Once'] = "Një herë";
$_lang['Daily'] = "Për ditë";
$_lang['Weekly'] = "Çdo javë";
$_lang['Monthly'] = "Çdo muaj";
$_lang['Yearly'] = "Çdo vitë";

$_lang['Create'] = "Krijo";

$_lang['Begin'] = "Fillo";
$_lang['Out of office'] = "Jashtë zyres";
$_lang['Back in office'] = "Ktheht në zyre";
$_lang['End'] = "Fund";
$_lang['@work'] = "Punë";
$_lang['We'] = "ja";
$_lang['group events'] = "grup ngjarjesh";
$_lang['or profile'] = "ose profile";
$_lang['All Day Event'] = "ngjarje për tër ditën";
$_lang['time-axis:'] = "time-axis:";
$_lang['vertical'] = "vertikal";
$_lang['horizontal'] = "horizontal";
$_lang['Horz. Narrow'] = "hor. ngusht";
$_lang['-interval:'] = "-intervali:";
$_lang['Self'] = "Vetë";

$_lang['...write'] = "...shkruaj";

$_lang['Calendar dates'] = "Kalendar datash";
$_lang['List'] = "Lista";
$_lang['Year'] = "Viti";
$_lang['Month'] = "Muaji";
$_lang['Week'] = "Java";
$_lang['Substitution'] = "Zavendësues";
$_lang['Substitution for'] = "Zavendësues për";
$_lang['Extended&nbsp;selection'] = "Extended&nbsp;selection";
$_lang['New Date'] = "Një datë e re e shënuar";
$_lang['Date changed'] = "Data është ndërruar";
$_lang['Date deleted'] = "Data është fshirë";

// links
$_lang['Database table'] = "Databejs tabela";
$_lang['Record set'] = "Record set";
$_lang['Resubmission at:'] = "Të rikontrollohet në:";
$_lang['Set Links'] = "Link";
$_lang['From date'] = "Prej datës";
$_lang['Call record set'] = "Thirre vendosjen e rekordit";


//login.php
$_lang['Please call login.php!'] = "Shkruaju në login.php!";

// m1.php
$_lang['There are other events!<br />the critical appointment is: '] = "Ka edhe ndodhi tjera!<br />Takimi kritikë është: ";
$_lang['Sorry, this resource is already occupied: '] = "Na vjen keq: Ky resurs është i zënë: ";
$_lang[' This event does not exist.<br /> <br /> Please check the date and time. '] = " Kjo ngjarje nuk egziston.<br /> <br /> Ju lutem kontrolloni datën dhe kohën. ";
$_lang['Please check your date and time format! '] = "Ju lutem kontrolloni datën dhe formatin e kohës! ";
$_lang['Please check the date!'] = "Ju lutem kontrolloni datën!";
$_lang['Please check the start time! '] = "Ju lutem kontrolloni kohën startuese! ";
$_lang['Please check the end time! '] = "Ju lutem kontrolloni kohën e përfundimit! ";
$_lang['Please give a text or note!'] = "Ju lutem japni ndonjë tekst ose shënim!";
$_lang['Please check start and end time! '] = "Kontrolloje kohën e fillimit dhe të përfundimit! ";
$_lang['Please check the format of the end date! '] = "Ju lutem kontrolloje formatin në përfundimin e datës! ";
$_lang['Please check the end date! '] = "Kontrolloje datën e përfundimit! ";





$_lang['Resource'] = "Resurset";
$_lang['User'] = "Shfrytëzuesi";

$_lang['delete event'] = "Fëshije ngjarjen";
$_lang['Address book'] = "Libri i adresave";


$_lang['Short Form'] = "Shkurtesa";

$_lang['Phone'] = "Tel";
$_lang['Fax'] = "Faks";



$_lang['Bookmark'] = "Bukmark";
$_lang['Description'] = "Përshkimi";

$_lang['Entire List'] = "Lista e tërë";

$_lang['New event'] = "Ngjarje e re";
$_lang['Created by'] = "Krijuar nga";
$_lang['Red button -> delete a day event'] = "Susta e kuqe -> fëshije ngjarjen e ditës";
$_lang['multiple events'] = "Ngjarje të shumta";
$_lang['Year view'] = "Dukja e vitit";
$_lang['calendar week'] = "kalendari javorë";

//m2.php
$_lang['Create &amp; Delete Events'] = "Krijo &amp; fëshije ngjarjen";
$_lang['normal'] = "normal";
$_lang['private'] = "privat";
$_lang['public'] = "publik";
$_lang['Visibility'] = "Dukshmëria";

//mail module
$_lang['Please select at least one (valid) address.'] = "Ju lutem zgjedheni një adresë (valide).";
$_lang['Your mail has been sent successfully'] = "Posta juaj është dërguar me sukses";
$_lang['Attachment'] = "Bashkangjitjet";
$_lang['Send single mails'] = "Dërgo një postë të vetme";
$_lang['Does not exist'] = "Nuk egziston";
$_lang['Additional number'] = "Numër shtesë";
$_lang['has been canceled'] = "Është pezulluar";

$_lang['marked objects'] = "objektet e shënuara";
$_lang['Additional address'] = "Adresë shtesë";
$_lang['in mails'] = "në posta";
$_lang['Mail account'] = "Konto postale";
$_lang['Body'] = "Trupi";
$_lang['Sender'] = "Dërguesi";

$_lang['Receiver'] = "Pranuesi";
$_lang['Reply'] = "Përgjigju";
$_lang['Forward'] = "Më tutje";
$_lang['Access error for mailbox'] = "Gabim në aksesin e kutis postale";
$_lang['Receive'] = "Pranon";
$_lang['Write'] = "Shkruaj";
$_lang['Accounts'] = "Kontot";
$_lang['Rules'] = "Rregullat";
$_lang['host name'] = "emri i nikoqirit";
$_lang['Type'] = "Shtype";
$_lang['misses'] = "mungon";
$_lang['has been created'] = "Është krijuar";
$_lang['has been changed'] = "Është ndërruar";
$_lang['is in field'] = "është në fushë";
$_lang['and leave on server'] = "dhe lere në server";
$_lang['name of the rule'] = "emri i rregullës";
$_lang['part of the word'] = "pjesë e fjalës";
$_lang['in'] = "në";
$_lang['sent mails'] = "mesazhe të dërguar";
$_lang['Send date'] = "Data e dërgimit";
$_lang['Received'] = "Pranuar";
$_lang['to'] = "pranuesi";
$_lang['imcoming Mails'] = "duke ardhurë";
$_lang['sent Mails'] = "Posta të dërguara";
$_lang['Contact Profile'] = "Profili kontaktues";
$_lang['unread'] = "të palexuara";
$_lang['view mail list'] = "shiko listën postale";
$_lang['insert db field (only for contacts)'] = "shtoje një db fushë (only for contacts)";
$_lang['Signature'] = "Nënshkrimi";

$_lang['SMS'] = "SMS";
$_lang['Single account query'] = "Konto private";
$_lang['Notice of receipt'] = "Vini re në rastin e pranimit";
$_lang['Assign to project'] = "Lajmëro në projekt";
$_lang['Assign to contact'] = "Lajmëro në kontakt";  
$_lang['Assign to contact according to address'] = "Lajmëro në kontakt sipas adresës";
$_lang['Include account for default receipt'] = "Përfshijë kontot për pranuesit standard";
$_lang['Your token has already been used.<br />If it wasnt you, who used the token please contact your administrator.'] = "Shenja juaj veq është përdorur.<br />Nëse ju nuk e keni përdorur atëherë kontakto administratorin";
$_lang['Your token has already been expired.'] = "Shenjës suaj i ka kaluar afati";
$_lang['Unconfirmed Events'] = "Ngjarje të pakonfirmuara";
$_lang['Visibility presetting when creating an event'] = "Dukshmëria e rivendosur gjatë krijimit të një ngajrje të re";
$_lang['Subject'] = "Subjekt";
$_lang['Content'] = "Përmbajtja";
$_lang['answer all'] = "përgjigju të gjithëve";
$_lang['Create new message'] = "Krijo një mesazh të ri";
$_lang['Attachments'] = "Bashkangjitjet";
$_lang['Recipients'] = "Pranuesit";
$_lang['file away message'] = "file away message";
$_lang['Message from:'] = "Mesazhi prej:";

//notes.php
$_lang['Mail note to'] = "Posta e notuar për";
$_lang['added'] = "është shtuar";
$_lang['changed'] = "është ndërruar";

// o.php
$_lang['Calendar'] = "Kalendari";
$_lang['Contacts'] = "Kontaktet";


$_lang['Files'] = "Fajllat";



$_lang['Options'] = "Opcionet";
$_lang['Timecard'] = "Krtela Kohore";

$_lang['Helpdesk'] = "Helpdesk";

$_lang['Info'] = "Info";
$_lang['Todo'] = "Todo";
$_lang['News'] = "Lajme";
$_lang['Other'] = "Tjetër";
$_lang['Settings'] = "Veçorit";
$_lang['Summary'] = "Përshkrimi";

// options.php
$_lang['Description:'] = "Përshkrimi:";
$_lang['Comment:'] = "Komente:";
$_lang['Insert a valid Internet address! '] = "Shkruaje një adresë valide të Internetit! ";
$_lang['Please specify a description!'] = "Përshkruaje!";
$_lang['This address already exists with a different description'] = "Kjo adresë egziston me përshkim tjetër";
$_lang[' already exists. '] = " veq egziston. ";
$_lang['is taken to the bookmark list.'] = "është marrë në listë bukmark.";
$_lang[' is changed.'] = " është ndërruar.";
$_lang[' is deleted.'] = " është fëshirë.";
$_lang['Please specify a description! '] = "Përshkruaje ju lutem! ";
$_lang['Please select at least one name! '] = "Ju lutem zgjedheni të paktën një emër! ";
$_lang[' is created as a profile.<br />'] = " është krijuar si profilë.<br />";
$_lang['is changed.<br />'] = "është ndryshuar.<br />";
$_lang['The profile has been deleted.'] = "Profili është fëshirë.";
$_lang['Please specify the question for the poll! '] = "Përshkruaje pyetjen për votim! ";
$_lang['You should give at least one answer! '] = "Duhet të japni të paktën një përgjigje! ";
$_lang['Your call for votes is now active. '] = "Thirrja juaj për votim është aktive tani. ";
$_lang['<h2>Bookmarks</h2>In this section you can create, modify or delete bookmarks:'] = "<h2>Bukmark</h2>Në këtë seksion ju mund të krijoni, ndryshoni apo të fshini një bukmark:";
$_lang['Create'] = "Krijo";


$_lang['<h2>Profiles</h2>In this section you can create, modify or delete profiles:'] = "<h2>Profiles</h2>Në këtë seksion ju mund të krijoni, ndryshoni apo të fshini një profil:";
$_lang['<h2>Voting Formula</h2>'] = "<h2>Formula e votimit</h2>";
$_lang['In this section you can create a call for votes.'] = "Në këtë seksion ju mund të krijoni nje¨thirrja për votë.";
$_lang['Question:'] = "Pyetjet:";
$_lang['just one <b>Alternative</b> or'] = "vetëm një <b>Alternative</b> or";
$_lang['several to choose?'] = "disa për ti zgjedhur?";

$_lang['Participants:'] = "Pjesëmarrësit:";

$_lang['<h3>Password Change</h3> In this section you can choose a new random generated password.'] = "<h3>Ndërrimi i Password-it</h3> In this section you can choose a new random generated password.";
$_lang['Old Password'] = "Passwordi i vjetër";
$_lang['Generate a new password'] = "Gjenero një password të ri";
$_lang['Save password'] = "Ruaje password-in";
$_lang['Your new password has been stored'] = "Passwordi juaj i ri është regjistruar";
$_lang['Wrong password'] = "Passwordi i gabuar";
$_lang['Delete poll'] = "Fëshije votën";
$_lang['<h4>Delete forum threads</h4> Here you can delete your own threads<br />Only threads without a comment will appear.'] = "<h4>Fëshiji bisedat nga forumi</h4> Këtu mund ti fëshish bisedat e tua<br />
Only threads without a comment will appear.";

$_lang['Old password'] = "Passwordi i vjetër";
$_lang['New Password'] = "Passwordi i ri";
$_lang['Retype new password'] = " Rishkruaje Password-in e ri";
$_lang['The new password must have 5 letters at least'] = "Passwordi i ri duhet ti ket të paktën 5 sinjale";
$_lang['You didnt repeat the new password correctly'] = "Nuk e keni përsërit passwordin në mënyr korrekte";

$_lang['Show bookings'] = "Trego rezervimet";
$_lang['Valid characters'] = "Karaktere Valide ";
$_lang['Suggestion'] = "Sugjerime";
$_lang['Put the word AND between several phrases'] = "Vendose fjalën AND në mesë të shumë frazave"; // translators: please leave the word AND as it is
$_lang['Write access for calendar'] = "Shkruaje një qasje për kalendar";
$_lang['Write access for other users to your calendar'] = "Shkruaje një qasje për shfrytzuesit tjerë në kalendarin tënd";
$_lang['User with chief status still have write access'] = "Shfrytëzuesi me statusin e shefit ka ende qasje shkrimi";

// projects
$_lang['Project Listing'] = "Lista e Projekteve";
$_lang['Project Name'] = "Emri i Projektit";


$_lang['o_files'] = "Të dhëna";
$_lang['o_notes'] = "Nota";
$_lang['o_projects'] = "Projekte";
$_lang['o_todo'] = "Todo";
$_lang['Copyright']="Copyright";
$_lang['Links'] = "Link";
$_lang['New profile'] = "Profil i ri";
$_lang['In this section you can choose a new random generated password.'] = "Në këtë seksion ju mund të zgjedhni një password të ri të gjeneruar.";
$_lang['timescale'] = "skica kohore";
$_lang['Manual Scaling'] = "Skica Manuale";
$_lang['column view'] = "Treguesi i kolonës";
$_lang['display format'] = "Tregoje formatin";
$_lang['for chart only'] = "Vetëm për diagram:";
$_lang['scaling:'] = "diagramues:";
$_lang['colours:'] = "ngjyra";
$_lang['display project colours'] = "tregoje projektin e ngjyrave";
$_lang['weekly'] = "çdo javë";
$_lang['monthly'] = "çdo muaj";
$_lang['annually'] = "manual";
$_lang['automatic'] = "automatik";
$_lang['New project'] = "Projekt i ri";
$_lang['Basis data'] = "Data bazike";
$_lang['Categorization'] = "Kategorizim";
$_lang['Real End'] = "Fund real";
$_lang['Participants'] = "Pjesëmarrësit";
$_lang['Priority'] = "Prioriteti";
$_lang['Status'] = "Statusi";
$_lang['Last status change'] = "I fundit <br />ndërroje";
$_lang['Leader'] = "Lideri";
$_lang['Statistics'] = "Statistikat";
$_lang['My Statistic'] = "Statistikat e mia";

$_lang['Person'] = "Personi";
$_lang['Hours'] = "Orë";
$_lang['Project summary'] = "Projekti i referatit";
$_lang[' Choose a combination Project/Person'] = " Zgjihde kombinimin e projektit/Personit";
$_lang['(multiple select with the Ctrl/Cmd-key)'] = "(multiple select with the 'Ctrl'-key)";

$_lang['Persons'] = "Personat";
$_lang['Begin:'] = "Fillo:";
$_lang['End:'] = "Fund:";
$_lang['All'] = "Të gjitha";
$_lang['Work time booked on'] = "Koha e punës e zën në";
$_lang['Sub-Project of'] = "Subjekti i";
$_lang['Aim'] = "Qëllimi";
$_lang['Contact'] = "Kontakti";
$_lang['Hourly rate'] = "Taksa e orëve";
$_lang['Calculated budget'] = "Bugjeti i llogaritur";
$_lang['New Sub-Project'] = "Nënprojekti i ri";
$_lang['Booked To Date'] = "Zënë deri tani";
$_lang['Budget'] = "Bugjeti";
$_lang['Detailed list'] = "Lista e detajeve";
$_lang['Gantt'] = "Gantt";
$_lang['offered'] = "ofruar";
$_lang['ordered'] = "porositur";
$_lang['Working'] = "duke punuar";
$_lang['ended'] = "përfunduar";
$_lang['stopped'] = "ndalur";
$_lang['Re-Opened'] = "i/e hapur përsëri";
$_lang['waiting'] = "duke pritur";
$_lang['Only main projects'] = "Vetëm projektet kryesore";
$_lang['Only this project'] = "Vetëm këtë projekt";
$_lang['Begin > End'] = "Fillo > Fund";
$_lang['ISO-Format: yyyy-mm-dd'] = "ISO-Formati: yyyy-mm-dd";
$_lang['The timespan of this project must be within the timespan of the parent project. Please adjust'] = "The timespan of this project must be within the timespan of the parent project. Please adjust";
$_lang['Please choose at least one person'] = "Ju lutem zgjidheni të paktën një person";
$_lang['Please choose at least one project'] = "Ju lutem zgjidheni të paktën një projekt";
$_lang['Dependency'] = "Varsëshmëria";
$_lang['Previous'] = "I përparëm";

$_lang['cannot start before the end of project'] = "Nuk mund të fillosh para se të përfundoj projekti";
$_lang['cannot start before the start of project'] = "Nuk mund të fillosh para se të startohet projekti";
$_lang['cannot end before the start of project'] = "Nuk mund të përfundosh para se të filloj projekti";
$_lang['cannot end before the end of project'] = "Nuk mund të përfundosh para se të përfundoj projekti";
$_lang['Warning, violation of dependency'] = "Vini re thyerje e varsëshmëris";
$_lang['Container'] = "Kontaineri";
$_lang['External project'] = "Projekt i jashtëm";
$_lang['Automatic scaling'] = "Scalaim automatik";
$_lang['Legend'] = "Legjenda";
$_lang['No value'] = "Pa vlerë";
$_lang['Copy project branch'] = "Kopjoje degën e projektit";
$_lang['Copy this element<br /> (and all elements below)'] = "Kopjoje këtë element<br /> (and all elements below)";
$_lang['And put it below this element'] = "dhe vendose nën këtë element";
$_lang['Edit timeframe of a project branch'] = "Editoje korrnizën kohore për këtë degë të projektit"; 

$_lang['of this element<br /> (and all elements below)'] = "të këtij elementi<br /> (and all elements below)";  
$_lang['by'] = "prej";
$_lang['Probability'] = "sipas gjasës";
$_lang['Please delete all subelements first'] = "Ju lutemi fëshij të gjithë nënprojektet së pari";
$_lang['Assignment'] ="Detyrat";
$_lang['display'] = "Shfaqi";
$_lang['Normal'] = "Normal";
$_lang['sort by date'] = "të ndara sipas datës";
$_lang['sort by'] = "Ndara prej";
$_lang['Calculated budget has a wrong format'] = "Bugjeti i llagaritur ka format të gabuar";
$_lang['Hourly rate has a wrong format'] = "Shuma e orëve ka format të gabuar";

// r.php
$_lang['please check the status!'] = "Ju lutem kontrollojeni statusin!";
$_lang['Todo List: '] = "Lista todo: ";
$_lang['New Remark: '] = "Remark i ri: ";
$_lang['Delete Remark '] = "Fëshije një Remark ";
$_lang['Keyword Search'] = "Këkimi i Fjalës kyçe";
$_lang['Events'] = "Ngjarjet";
$_lang['the forum'] = "forumi";
$_lang['the files'] = "fajllat";
$_lang['Addresses'] = "Adresat";
$_lang['Extended'] = "I zgjeruar";
$_lang['all modules'] = "Të gjitha modulet";
$_lang['Bookmarks:'] = "Bookmarksat:";
$_lang['List'] = "Lista";
$_lang['Projects:'] = "Projektet:";

$_lang['Deadline'] = "Afati i fundit";

$_lang['Polls:'] = "Votat:";

$_lang['Poll created on the '] = "Votimi krijuar në ";


// reminder.php
$_lang['Starts in'] = "Fillon më";
$_lang['minutes'] = "minuta";
$_lang['No events yet today'] = "End nuk ka ngjarje për sot";
$_lang['New mail arrived'] = "Një postë e re ka arritur";

//ress.php

$_lang['List of Resources'] =  "Lista e Resurseve";
$_lang['Name of Resource'] = "Emri i Resursit";
$_lang['Comments'] =  "Komente";


// roles
$_lang['Roles'] = "Roles";
$_lang['No access'] = "No access";
$_lang['Read access'] = "Read access";

$_lang['Role'] = "Role";

// helpdesk - rts
$_lang['Request'] = "Kërkesa";

$_lang['pending requests'] = "Kërkesa në progres";
$_lang['show queue'] = "trego rendin";
$_lang['Search the knowledge database'] = "Kërko në databejs";
$_lang['Keyword'] = "Fjala kyçe";
$_lang['show results'] = "Tregoj rezultatet";
$_lang['request form'] = "forma e kërkesës";
$_lang['Enter your keyword'] = "shkruaje fjalën tënde kyqe";
$_lang['Enter your email'] = "Shkruaje adresën tënde";
$_lang['Give your request a name'] = "Na jap emrin e kërkesës suaj";
$_lang['Describe your request'] = "Përshkruaje kërkesën tuaj";

$_lang['Due date'] = "Due date";
$_lang['Days'] = "Ditë";
$_lang['Sorry, you are not in the list'] = "Na vjen keq, nuk jeni në listë";
$_lang['Your request Nr. is'] = "Nr. i kërkesës suaj është";
$_lang['Customer'] = "Konsumator";


$_lang['Search'] = "Kërko";
$_lang['at'] = "në";
$_lang['all fields'] = "Të gjitha fushat";


$_lang['Solution'] = "Zgjedhje";
$_lang['AND'] = "DHE";

$_lang['pending'] = "në progres";
$_lang['stalled'] = "stalled";
$_lang['moved'] = "Larguar";
$_lang['solved'] = "zgjedhur";
$_lang['Submit'] = "dërguar";
$_lang['Ass.'] = "kujt";
$_lang['Pri.'] = "Kalimi";
$_lang['access'] = "access";
$_lang['Assigned'] = "bashkangjitur";

$_lang['update'] = "freskoje";
$_lang['remark'] = "remark";
$_lang['solve'] = "zgjidhur";
$_lang['stall'] = "stall";
$_lang['cancel'] = "refuzoje";
$_lang['Move to request'] = "lëviz në kërkes";
$_lang['Dear customer, please refer to the number given above by contacting us.Will will perform your request as soon as possible.'] = "i nderuar konsumator, ju lutem referoje numrin e dhën nga ne me rastin e kontaktit.
Do ti referohemi mesazhit tuaj sa më parë që është e mundur.";
$_lang['Your request has been added into the request queue.<br />You will receive a confirmation email in some moments.'] = "Your request has been added into the request queue.<br />
Do të pranoni një postë konfirmimi në disa qaste.";
$_lang['n/a'] = "n/a";
$_lang['internal'] = "i mbrendshëm";

$_lang['has reassigned the following request'] = "e ka shtuar këkesën që vijon";
$_lang['New request'] = "Kërkesa e re";
$_lang['Assign work time'] = "Shtoje kohën e punës";
$_lang['Assigned to:'] = "Shtuar:";

$_lang['Your solution was mailed to the customer and taken into the database.'] = "Zgjedhja juaj është hjekur nga konsumatori dhe bashkangjitur në Databejs.";
$_lang['Answer to your request Nr.'] = "Përgjigja juaj në kërkesën numër.";
$_lang['Fetch new request by mail'] = "Merr një kërkesë nga popsta";
$_lang['Your request was solved by'] = "Kërkesa juaj është zgjedhur nga";

$_lang['Your solution was mailed to the customer and taken into the database'] = "Zgjedhja juaj është postuar te konsumatori dhe bashkangjitur në Databejs";
$_lang['Search term'] = "Termet e kërkimit";
$_lang['Search area'] = "Vendi i kërekimit";
$_lang['Extended search'] = "Kërkim i përzgjatur";
$_lang['knowledge database'] = "Databejs i ekspertuar";
$_lang['Cancel'] = "çbëje";
$_lang['New ticket'] = "Tiketë e re";
$_lang['Ticket status'] ="Status tiketa";

// please adjust this states as you want -> add/remove states in helpdesk.php
$_lang['unconfirmed'] = 'pakonfirmuar';
$_lang['new'] = 'i ri';
$_lang['assigned'] = 'lajmëreuar';
$_lang['reopened'] = 'rihapur';
$_lang['resolved'] = 'rizgjidhur';
$_lang['verified'] = 'verifikuar';

// settings.php
$_lang['The settings have been modified'] = "Veqoritë janë edituar (Ndryshuar)";
$_lang['Skin'] = "Skin";
$_lang['First module view on startup'] = "Shikimi i modulit të parë në start";
$_lang['none'] = "asnjë";
$_lang['Check for mail'] = "Kontrolloje postën";
$_lang['Additional alert box'] = "Boks i pikatur shtesë";
$_lang['Horizontal screen resolution <br />(i.e. 1024, 800)'] = "Resolucioni horizontal i monitorit <br />(i.e. 1024, 800)";
$_lang['Chat Entry'] = "hyrje në Chat";
$_lang['single line'] = "vijë e vetme";
$_lang['multi lines'] = "vija të shumta";
$_lang['Chat Direction'] = "Drejtimi Chat";
$_lang['Newest messages on top'] = "Mesazhet e reje në krye";
$_lang['Newest messages at bottom'] = "Mesazhet e reja në fund";
$_lang['File Downloads'] = "File Downloads";

$_lang['Inline'] = "Në linje";
$_lang['Lock file'] = "Bllokoje fajllin";
$_lang['Unlock file'] = "çbllokoje fajllin";
$_lang['New file here'] = "Fajlli i ri këtu";
$_lang['New directory here'] = "Dosje e re këtu";
$_lang['Position of form'] = "Pozita e formës";
$_lang['On a separate page'] = "Në faqe të ndara";
$_lang['Below the list'] = "Nën listë";
$_lang['Treeview mode on module startup'] = "Metoda e degës në start të modulit";
$_lang['Elements per page on module startup'] = "Elemente për faqe në startim të modulit";
$_lang['General Settings'] = "Veçorit e përgjithshme";
$_lang['First view on module startup'] = "Pamja e parë në startim të modulit";
$_lang['Left frame width [px]'] = "Gjerësia e kornizës së majt [px]";
$_lang['Timestep Daywiew [min]'] = "Pamja e ditës në hapin e kohës [min]";
$_lang['Timestep Weekwiew [min]'] = "Pamja e javës në hapin e kohës [min]";
$_lang['px per char for event text<br />(not exact in case of proportional font)'] = "px per char for event text<br />(not exact in case of proportional font)";
$_lang['Text length of events will be cut'] = "Gjatësia e tekstit në ngjarje do te¨shkurtohet";
$_lang['Standard View'] = "Pamje Standarde";
$_lang['Standard View 1'] = "Pamje Standarde 1";
$_lang['Standard View 2'] = "Pamje Standard 2";
$_lang['Own Schedule'] = "Orari peresonal";
$_lang['Group Schedule'] = "Orari grupor";
$_lang['Group - Create Event'] = "Grupi - krijo një ngjarje";
$_lang['Group, only representation'] = "Grupi, vetëm representimi";
$_lang['Holiday file'] = "Fajlli i pushimit";

// summary
$_lang['Todays Events'] = "Ngjarjet sot";
$_lang['New files'] = "Fajllat e ri";
$_lang['New notes'] = "Notat e reja";
$_lang['New Polls'] = "Votat e reja";
$_lang['Current projects'] = "Projektet e tanishme";
$_lang['Help Desk Requests'] = "Helpdesk Këkesa";
$_lang['Current todos'] = "Todo-të e tanishëm";
$_lang['New forum postings'] = "Postimet e forumit të ri";
$_lang['New Mails'] = "Mesazhat e ri";

//timecard

$_lang['Theres an error in your time sheet: '] = "Ka ndodhur një gabim: ";




$_lang['Consistency check'] = "Shpeshësia e kontrollit";
$_lang['Please enter the end afterwards at the'] = "Ju lutemi vendosëni fundin në prapa";
$_lang['insert'] = "vendose";
$_lang['Enter records afterwards'] = "Vendosi rekordet në fund";
$_lang['Please fill in only emtpy records'] = "Ju lutemi mbusheni vetëm rekordin e zbrazët";

$_lang['Insert a period, all records in this period will be assigned to this project'] = "Vendoseni një periodë, të gjitha rekordet do të futen në këtë projekt";
$_lang['There is no record on this day'] = "Nuk ka asnjë rekord për këtë ditë";
$_lang['This field is not empty. Please ask the administrator'] = "Fusha nuk është e zbrazët, ju lutem pyeteni administratorin";
$_lang['There is no open record with a begin time on this day!'] = "Nuk ka rekorde në kohën e filluar për këtë ditë!";
$_lang['Please close the open record on this day first!'] = "Mbylleni së pari rekordin e hapur për këtë ditë!";
$_lang['Please check the given time'] = "Kontrolloje kohën e dhënë";
$_lang['Assigning projects'] = "Projektet e paralajmëruara";
$_lang['Select a day'] = "Zgjedhe ditën";
$_lang['Copy to the boss'] = "Kopje shefit";
$_lang['Change in the timecard'] = "Ndryshime në kartelën kohore";
$_lang['Sum for'] = "Sum for";

$_lang['Unassigned time'] = "Koha e pa paralajmëruar";
$_lang['delete record of this day'] = "fshije rekordin për këtë ditë";
$_lang['Bookings'] = "Rezervimet";

$_lang['insert additional working time'] = "Vendose kohën punuese shtesë";
$_lang['Project assignment']= "Lajmërim Projekti";
$_lang['Working time stop watch']= "Ora ndalëse e kohës së punës";
$_lang['stop watches']= "Orët ndaluese";
$_lang['Project stop watch']= "Ora e ndaljes së projektit";
$_lang['Overview my working time']= "Rishikoje kohën time të punës";
$_lang['GO']= "SHKO";
$_lang['Day view']= "Pamje e ditës";
$_lang['Project view']= "Pamje e projektit";
$_lang['Weekday']= "Ditë pune";
$_lang['Start']= "Starto";
$_lang['Net time']= "Neto kkoha";
$_lang['Project bookings']= "Projekt bookings";
$_lang['save+close']= "ruaje+mbylle";
$_lang['Working times']= "Orari i punës";
$_lang['Working times start']= "Orari i punës fillon";
$_lang['Working times stop']= "Orari i punës mbaron";
$_lang['Project booking start']= "Rezervimi i projekteve fillon";
$_lang['Project booking stop']= "Rezervimi i projekteve mbaron";
$_lang['choose day']= "zgjidhe ditën";
$_lang['choose month']= "zgjidhe muajin";
$_lang['1 day back']= "1 ditë pas";
$_lang['1 day forward']= "1 dite¨përpara";
$_lang['Sum working time']= "Shum orari i punës";
$_lang['Time: h / m']= "Time: h / m";
$_lang['activate project stop watch']= "aktivizoje stop orën të projektit";
$_lang['activate']= "aktivizoje";
$_lang['project choice']= "zgjedhje projekti";
$_lang['stop stop watch']= "ndaloje stop orën";
$_lang['still to allocate:']= "ende për të matur:";
$_lang['You are not allowed to delete entries from timecard. Please contact your administrator']= "Ju nuk keni të drejt të fshini nga kartela kohore. Kontakto administratorin";
$_lang['You cannot delete entries at this date. Since there have been %s days. You just can edit entries not older than %s days.']= "Nuk mund të fshish të hyrat për kët ditë. Prej atëherë kanë qenë %s ditë. Ju mund të editoni të hyrat që nuk janë më të vjetra se %n ditë.";
$_lang['You cannot delete bookings at this date. Since there have been %s days. You just can edit bookings of entries not older than %s days.']= "Nuk mund të fshish rezervimet për kët ditë. Prej atëherë kanë qenë %s ditë. Ju mund të editoni rezervimet që nuk janë më të vjetra se %n ditë.";
$_lang['You cannot add entries at this date. Since there have been %s days. You just can edit entries not older than %s days.']= "Nuk mund të shtosh të hyrat për këtë ditë. Prej atëherë kanë qenë %s ditë. Ju mund të editoni të hyrat që nuk janë më të vjetra se %n ditë.";
$_lang['You cannot add  bookings at this date. Since there have been %s days. You just can add bookings for entries not older than %s days.']= "Nuk mund të shtosh rezervimet për kët ditë. Prej atëherë kanë qenë %s ditë. Ju mund të editoni rezervimet që nuk janë më të vjetra se %n ditë.";
$_lang['activate+close']="aktivizoje+mbylle";

// todos
$_lang['accepted'] = "Pranuar";
$_lang['rejected'] = "refuzuar";
$_lang['own'] = "e imja";
$_lang['progress'] = "progres";
$_lang['delegated to'] = "deleguar";
$_lang['Assigned from'] = "nënshkruar nga";
$_lang['done'] = "gati";
$_lang['Not yet assigned'] = "Nuk është përpiluar ende";
$_lang['Undertake'] = "E zënë";
$_lang['New todo'] = "Todo e re"; 
$_lang['Notify recipient'] = "Lajmëroje pranuesin";

// votum.php
$_lang['results of the vote: '] = "resultatet e votimit: ";
$_lang['Poll Question: '] = "Pyetja e votimit: ";
$_lang['several answers possible'] = "mundësia e disa përgjigjeve";
$_lang['Alternative '] = "Alternative ";
$_lang['no vote: '] = "pa vota: ";
$_lang['of'] = "prej";
$_lang['participants have voted in this poll'] = "pjesmarrësit kanë votuar";
$_lang['Current Open Polls'] = "Votat e hapura për tani";
$_lang['Results of Polls'] = "Lista e rezultateve për të gjitha votat";
$_lang['New survey'] ="Parashikim i ri";
$_lang['Alternatives'] ="Alternativat";
$_lang['currently no open polls'] = "Momentalisht nuk ka sondazh të ri";

// export_page.php
$_lang['export_timecard']       = "Eksportoje Kartelën kohore";
$_lang['export_timecard_admin'] = "Eksportoje Kartelën kohore";
$_lang['export_users']          = "Eksportoj shfrytëzuesit";
$_lang['export_contacts']       = "Eksportoj kontaktet";
$_lang['export_projects']       = "Eksportoj projektet";
$_lang['export_bookmarks']      = "Eksportoj bukmarksat";
$_lang['export_timeproj']       = "Eksportoj projektet kohore";
$_lang['export_project_stat']   = "Eksportoj stat projektuese";
$_lang['export_todo']           = "Eksportoj todo-të";
$_lang['export_notes']          = "Eksportoj notat";
$_lang['export_calendar']       = "Eksportoj të gjitha ngjarjet kalendarike";
$_lang['export_calendar_detail']= "Eksportoje një detaj kalendarik";
$_lang['submit'] = "nënshtuar";
$_lang['Address'] = "Adresa";
$_lang['Next Project'] = "Projekti i ardhshëm";
$_lang['Dependend projects'] = "Projektet e varura";
$_lang['db_type'] = "Tipi Data bejs";
$_lang['Log in, please'] = "Shkruaju, ju lutemi";
$_lang['Recipient'] = "Pranuesi";
$_lang['untreated'] = "pa trajtuar";
$_lang['Select participants'] = "Zgjedhi pjesëmarrësit";
$_lang['Participation'] = "Pjesëmarrje";
$_lang['not yet decided'] = "Nuk kam vendosur ende";
$_lang['accept'] = "pranoje";
$_lang['reject'] = "refuzoje";
$_lang['Substitute for'] = "Zavendësuesi për";
$_lang['Calendar user'] = "Përdoruesi i kalendarit";
$_lang['Refresh'] = "Freskoje";
$_lang['Event'] = "Ngjarje";
$_lang['Upload file size is too big'] = "Madhësia e fajllit të shtuar është tepër e madhe";
$_lang['Upload has been interrupted'] = "Shtimi është ndërpritur";
$_lang['view'] = "Pamje";
$_lang['found elements'] = "elemente të gjetura";
$_lang['chosen elements'] = "elemente të zgjedhura";
$_lang['too many hits'] = "Rezultati është më i madh sesa kemi mundësi ta shfaqim.";
$_lang['please extend filter'] = "Ju lutem ndajini filterët.";
$_lang['Edit profile'] = "Edito profilin";
$_lang['add profile'] = "shto një profil";
$_lang['Add profile'] = "Shto një profil";
$_lang['Added profile'] = "Profile të shtuara.";
$_lang['No profile found'] = "Ska profil.";
$_lang['add project participants'] = "shto pjesmarrësit e prjoktit";
$_lang['Added project participants'] = "Pjesëmarrësit e projektit janë shtuar.";
$_lang['add group of participants'] = "shtoje grupin e pjesëmarrësve";
$_lang['Added group of participants'] = "Pjesëmarrësit janë shtuar.";
$_lang['add user'] = "shto onjë përdoruesr";
$_lang['Added users'] = "përdoruesi(t) është/janë shtuar.";
$_lang['Selection'] = "Selekcion";
$_lang['selector'] = "selektor";
$_lang['Send email notification']= "Send&nbsp;email&nbsp;notification";
$_lang['Member selection'] = "Member&nbsp;selection";
$_lang['Collision check'] = "Kontrolloje goditjen";
$_lang['Collision'] = "Goditje";
$_lang['Users, who can represent me'] = "Përdorues që mund të më prezentojnë";
$_lang['Users, who can see my private events'] = "Përdorues që mund të shohin<br />ngjarjet e mia private";
$_lang['Users, who can read my normal events'] = "Përdorues që mund të lexojnë<br />ngjarjet e mia private";
$_lang['quickadd'] = "Shtim i shpejtë";
$_lang['set filter'] = "Vendose filterin";
$_lang['Select date'] = "Zgjedhe datën";
$_lang['Next serial events'] = "Ngjarjet e ardhëshme serike";
$_lang['All day event'] = "Ngjarje e tërë ditës";
$_lang['Event is canceled'] = "Event&nbsp;is&nbsp;canceled";
$_lang['Please enter a password!'] = "Shkruani Passwordin ju lutem!";
$_lang['You are not allowed to create an event!'] = "Nuk mund të krijoni një ngajrje!";
$_lang['Event successfully created.'] = "Ngjarja është krijuar me sukses.";
$_lang['You are not allowed to edit this event!'] = "Nuk jeni i lejuar të editoni këtë ngajrje!";
$_lang['Event successfully updated.'] = "Ngjarja është freskuar me sukses.";
$_lang['You are not allowed to remove this event!'] = "Ju nuk jeni i lejuar të fshini këtë ngjarje!";
$_lang['Event successfully removed.'] = "Ngjarja e fshirë me sukses.";
$_lang['Please give a text!'] = "Ju lutemi jepni një tekst!";
$_lang['Please check the event date!'] = "Kontrolloje datën e ngjarjes!";
$_lang['Please check your time format!'] = "Kontrolloje formatin e kohës!";
$_lang['Please check start and end time!'] = "Kontrolloje fillimin dhe mbarimin e kohës!";
$_lang['Please check the serial event date!'] = "Kontrolloje datën e ngjarjes serike!";
$_lang['The serial event data has no result!'] = "Të dhënat e ngjarjes serike nuk kanë rezultat!";
$_lang['Really delete this event?'] = "Me siguri e fshin këtë ngjarje?";
$_lang['use'] = "Në përdorim";
$_lang[':'] = ":";
$_lang['Mobile Phone'] = "Telefon mobil";
$_lang['submit'] = "Nënshtro";
$_lang['Further events'] = "Ngjarje të mëpastajme";
$_lang['Remove settings only'] = "Fshijë veçoritë vetëm";
$_lang['Settings removed.'] = "Veçoritë janë fshirë.";
$_lang['User selection'] = "Seleksionimi i ppërdoruesit";
$_lang['Release'] = "Liroje";
$_lang['none'] = "asnjë";
$_lang['only read access to selection'] = "Vetëm të drejta leximi në selekcion";
$_lang['read and write access to selection'] = "të drejtat për të lexuar dhe edituar në selkcion";
$_lang['Available time'] = "Koha në dispozicion";
$_lang['list view'] = "Pamje e listës";
$_lang['o_dateien'] = "Managjeri i fajllit";
$_lang['Location'] = "Lokacion";
$_lang['date_received'] = "datë_pranimi";
$_lang['subject'] = "Subjekti";
$_lang['kat'] = "Categoria";
$_lang['projekt'] = "Projekti";
$_lang['Location'] = "Locacioni";
$_lang['name'] = "Titulli";
$_lang['contact'] = "Kontakti";
$_lang['div1'] = "Ndrysh1";
$_lang['div2'] = "Ndrysh2";
$_lang['kategorie'] = "Kategoria";
$_lang['anfang'] = "Beginn";
$_lang['ende'] = "Ende";
$_lang['status'] = "Status";
$_lang['filename'] = "Titulli";
$_lang['deadline'] = "Termini";
$_lang['ext'] = "ext";
$_lang['priority'] = "Prioritet";
$_lang['project'] = "Projekti";
$_lang['Accept'] = "Pranoje";
$_lang['Please enter your user name here.'] = "Shkruani emrin e përdoruesit këtu.";
$_lang['Please enter your password here.'] = "Shkruani password-in këtu.";
$_lang['Click here to login.'] = "Kliko këtu për të hyrë.";
$_lang['No New Polls'] = "Ska asnjë sontazh";
$_lang['&nbsp;Hide read elements'] = "&nbsp;Fshehi elementet lexues";
$_lang['&nbsp;Show read elements'] = "&nbsp;Tregoji elementet lexues";
$_lang['&nbsp;Hide archive elements'] = "&nbsp;Fshehi elementet arkivor";
$_lang['&nbsp;Show archive elements'] = "&nbsp;Tregoj elementet arkivor";
?>
