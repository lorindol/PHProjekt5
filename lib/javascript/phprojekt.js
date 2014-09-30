/**
 * Javascript functions for dates
 */

/*
 * Check the field value with searchfor
 * Return txt if faild
 *
 * @param string name      - id of the field
 * @param string txt       - Text for the alert
 * @param string searchfor - Exp to check
 * @return void
 */
function chktime(name, txt, searchfor) {
    e = document.getElementById(name);
    value = e.value;
    if(value != "" && value != searchfor.exec(value)) {
        alert(txt);
    }
}

/* Functions for the timecard checks */
var monthDays = new Array(31,28,31,30,31,30,31,31,30,31,30,31);
var next;
var mo;

/*
 * convert a date string from the user format to the db/iso format
 * eg. 20.06.2000 => 2000-06-20
 * 
 * seq_y, seq_m, seq_d and userSeparator are globals created when the dateformat is called
 *
 * @param string userDate - Date to format
 * @param string userSeparator - date parts Separator
 * @param string seq_y
 * @param string seq_m
 * @param string seq_d
 * @return date Array
 */
function convertDateFormatUser2Db(userDate) {
    if (userDate) {
        var parts = userDate.split(userSeparator);
        if (parts.length != 3) {
            return userDate;
        }
        var dbDate = parts[seq_y]+"-"+parts[seq_m]+"-"+parts[seq_d];
        return dbDate;
    }
    return "";
}

/*
 * convert a date string from the db/iso format to the user format
 * eg. 2000-06-20 => 20.06.2000
 *
 * seq_y, seq_m, seq_d and userSeparator are globals created when the dateformat is called
 *
 * @param string dbDate - Date to format
 * @return date Array
 */
function convertDateFormatDb2User(dbDate) {
    if (dbDate) {
        var parts = dbDate.split("-");
        if (parts.length != 3) {
            return dbDate;
        }
        var rel = new Array();
        rel[seq_y] = parts[0];
        rel[seq_m] = parts[1];
        rel[seq_d] = parts[2];
        var userDate = rel.join(userSeparator);
        return userDate;
    }
    return "";
}

/*
 * Check the value to the user format 
 *
 * @param string theField   - id of the date field to check
 * @param string alertText  - msg to display
 * @return boolean          - correct or not
 */
function checkUserDateFormat(theField, alertText) {

    // Return if don't exist
    if (dojo.widget.byId("picker_"+theField) == null ) {
        return true;
    } else {
        var field = dojo.widget.byId("picker_"+theField).inputNode;
        var string = field.value;

        if (string != "") {
            // searchfor is global, created when the datepicker is called
            var result = searchfor.test(string);
            if (result == false) {
                alert(alertText);
                field.focus();
                return false;
            }
        }
        return true;
    }
}

/*
 * Check if one date is bigger than the other
 *
 * @param string theField1  - id of the first date field to check
 * @param string theField2  - id of the second date field to check
 * @param string alertText  - msg to display
 * @return boolean          - correct or not
 */
function checkDates(theField1, theField2, alertText) {
    
    // Return if don't exist
    if (dojo.widget.byId("picker_"+theField1) == null && dojo.widget.byId("picker_"+theField2) == null) {
        return true;
    } else {
        var field1 = dojo.widget.byId("picker_"+theField1).inputNode;
        var field2 = dojo.widget.byId("picker_"+theField2).inputNode;

        if (convertDateFormatUser2Db(field1.value) > convertDateFormatUser2Db(field2.value)) {
            alert(alertText);
            field2.focus();
            return false;
        }
        return true;
    }
}

/*
 * Check the calendar time format
 * @param string frm - The form
 * @param string fld - The field
 * @param string txt - msg to display
 * @return boolean   - correct or not
 */
function checkCalendarTimeFormat(frm, fld, txt) {
	var string = document.forms[frm].elements[fld].value;
	if (string != "" && string != "----") {
 	        var timesearchfor = /^\d+$/;
		var result = timesearchfor.test(string);
		if (result == false) {
			alert(txt);
			document.forms[frm].elements[fld].focus();
			return false;
		}
	}
	return true;
}

/*
 * decress one day
 * eg. 2000-06-20 => 2000-06-21
 *
 * seq_y, seq_m, seq_d and userSeparator are globals created when the dateformat is called
 *
 * @param string obj - actual Date
 */
function nM(obj) {
    bothString  = new String(obj.inputNode.value);
    bothString  = convertDateFormatUser2Db(bothString);
    splitString = bothString.split("-");
    yr = splitString[0];
    mn = splitString[1];
    day  = splitString[2];
    next = (day*1) + 1;
    yy = (yr*1);
    if (((yy % 4)==0) && (((yy % 100)!=0) || ((yy % 400)==0))) {
        monthDays = new Array(31,29,31,30,31,30,31,31,30,31,30,31);
    } else {
        monthDays = new Array(31,28,31,30,31,30,31,31,30,31,30,31);
    }
    mo = (mn*1)-1;
    var vgl = monthDays[mo];
    if (day >= vgl) {
        mn = (mn*1)+1;
        next = 1;
    }
    if (mn==13) {
        mn = 1;
        yy = (yr*1)+1;
    }
    bothString = yy+"-"+twoDigits(mn)+"-"+twoDigits(next);
    obj.inputNode.value  = convertDateFormatDb2User(bothString);
}

/*
 * incress one day
 * eg. 2000-06-20 => 2000-06-21
 *
 * seq_y, seq_m, seq_d and userSeparator are globals created when the dateformat is called
 *
 * @param string obj - actual Date
 */
function lM(obj) {
    bothString  = new String(obj.inputNode.value);
    bothString  = convertDateFormatUser2Db(bothString);
    splitString = bothString.split("-");
    yr = splitString[0];
    mn = splitString[1];
    day  = splitString[2];
    next = (day*1) - 1;
    yy = (yr*1);
    if (((yy % 4)==0) && (((yy % 100)!=0) || ((yy % 400)==0))) {
        monthDays = new Array(31,29,31,30,31,30,31,31,30,31,30,31);
    } else {
        monthDays = new Array(31,28,31,30,31,30,31,31,30,31,30,31);
    }
    if (next == 0) {
        mn = (mn*1)-1;
        next = monthDays[mn-1];
    }
    if (mn == 0) {
        mn = 12;
        yy = (yr*1)-1;
        next = monthDays[mn-1];
    }
    bothString = yy+"-"+twoDigits(mn)+"-"+twoDigits(next);
    obj.inputNode.value  = convertDateFormatDb2User(bothString);
}

function twoDigits(x) {
    x = "0" + x;
    return x.match(/\d\d$/);
}

function getNetto(anf, end, nanf, nend) {
    if (nanf) {
        a = anf.value;
        e = end.value;
        netto  = ((e.substr(0,2) - a.substr(0,2))*60 +(e.substr(2,2) - a.substr(2,2)));
        nettoh = Math.floor(netto/60);
        nettom = netto - (nettoh * 60);
        nanf.value = nettom;
        nend.value = nettoh;
    };
}

/**
 * End Javascript functions for dates
 */

/**
 * Javascript functions to move items from one select to other
 * (single or multiple selects)
 */

/**
 * Move selected items from source select to target select
 *
 * @param string source - Name of the source select field
 * @param string target - Name of the target select field
 * @return void
 */
function moveOption(source,target) {
    var moved = 'no';
    
    var mytarget = getElement(target);
    
    if (mytarget) {
        var mysource = getElement(source);
        if (mysource) {
            do{
                if (mysource.selectedIndex >= 0 && mysource.options[mysource.selectedIndex].value != '') {
                    newtext = mysource.options[mysource.selectedIndex].text;
                    newvalue = mysource.options[mysource.selectedIndex].value;
                    size = mytarget.options.length;
                    newoption = new Option(newtext,newvalue,false,false);
                    mytarget.options[size] = newoption;
                    mytarget.options[size].selected = true;
                    mysource.options[mysource.selectedIndex] = null;
                    //mysource.value = '';
                    mysource.focus();
                    moved = 'yes';
                }
            } while (mysource.type == 'select-multiple' && mysource.selectedIndex >= 0);
            
            if (moved == 'yes') {
                mysubmit = document.getElementById('submit_selector');
                if (mysubmit) {
                    mysubmit.disabled = false;
                }
            }
            
        }
    }
}

/**
 * Get document element
 *
 * @param string n - Name of the field
 * @param string d - document element
 * @return form element
 */
function getElement(n, d) {
    var p,i,x;
    if (!d) {
        d = document;
    }

    if ((p = n.indexOf("?")) > 0 && parent.frames.length) {
        d = parent.frames[n.substring(p+1)].document;
        n = n.substring(0,p);
    }
    if (!(x=d[n]) && d.all) {
        x = d.all[n];
    }
    for (i = 0;!x && i < d.forms.length;i++) {
        x = d.forms[i][n];
    }
    for (i = 0;!x && d.layers && i < d.layers.length;i++) {
        x = getElement(n,d.layers[i].document);
    }
    if (!x && d.getElementById) {
        x = d.getElementById(n);
    }
    return x;
}

/**
 * Select all the items
 *
 * @param string name - Name of the field
 * @return void
 */
function selector_selectAll(name) {
    var myfield = getElement(name);
    if (myfield) {
        for(i = 0;i < myfield.options.length;i++) {
            myfield.options[i].selected = true;
        }
    }
}

/**
 * Select one item from source(src) and put the value in target(dsts)
 * Disable or not the submit button (submit_selector)
 * 
 * @param string name - Name of the field
 * @return void
 */
function selectOne(name) {
    if (document.forms['finishForm'].elements[name+'srcs[]'].selectedIndex != -1) {
        var text = document.forms['finishForm'].elements[name+'srcs[]'].options[document.forms['finishForm'].elements[name+'srcs[]'].selectedIndex].text;
        document.forms['finishForm'].elements[name+'dsts'].value = text;
        if (text != '-----'){
            document.getElementById('submit_selector').disabled = false;
        } else {
            document.getElementById('submit_selector').disabled = true;
        }
    }
}

/**
 * Get all the selected items and make a link with them
 * Go to the link
 * 
 * @param string link1 - First string for link
 * @param string link2 - Second string for link
 * @param string type  - Type of the select (multiple/single)
 * @param string name  - Name of the field
 * @return void
 */
function get_filter_delete_link(link1, link2, type, name) {
    var preselected = '&preselect=';
    if (type == 'multiple') {
        for (var i = 0; i < document.forms['finishForm'].elements[name+'dsts[]'].length ; i++) {
            preselected = preselected + document.forms['finishForm'].elements[name+'dsts[]'].options[i].value + '-';
        }
    } else {
            if (document.forms['finishForm'].elements[name+'srcs[]'].length != 0) {
                // if something is selected
                if (document.forms['finishForm'].elements[name+'srcs[]'].selectedIndex > 0) {
                    preselected = preselected + document.forms['finishForm'].elements[name+'srcs[]'].options[document.forms['finishForm'].elements[name+'srcs[]'].selectedIndex].value;
                }
            }
    }
    link = link1 + preselected + link2;
    location.href = link;
}

/**
 * Move selected items to up or down in the select
 *
 * @param string source    - Name of the source select field
 * @param string direction - up/down
 * @return void
 */
function movePosOption(source,direction) {
    var mysource = getElement(source);
    if (mysource) {
        if (mysource.selectedIndex >= 0 && mysource.options[mysource.selectedIndex].value != '' && mysource.length > 1) {
            actualPos = mysource.selectedIndex;
            if (direction == 'up') {
                nextPos = actualPos - 1;
            } else { 
                nextPos = actualPos + 1;
            }
                
            if (nextPos > -1 && nextPos < mysource.length) {
                actualText = mysource.options[actualPos].text;
                actualValue = mysource.options[actualPos].value;

                nextText = mysource.options[nextPos].text;
                nextValue = mysource.options[nextPos].value;
                
                mysource.options[nextPos].text  = actualText;
                mysource.options[nextPos].value = actualValue;
                
                mysource.options[actualPos].text  = nextText;
                mysource.options[actualPos].value = nextValue;
                
                mysource.value = actualValue;
                mysource.selectedIndex = nextPos;
                mysource.focus();
            }
        }
    }
}

/**
 * End Javascript functions to move items from one select to other
 */

/**
 * Javascript functions for contextmenu, listview and altview
 */

// Marker and pointer for list view
var marked  = new Array();
var allRows = new Array();

function hiliOn(tr, i) {
	if (typeof(marked[i]) == 'undefined' || !marked[i]) {
		tr.style.backgroundColor = hiliColor;
	}
}

function hiliOff(tr, i) {
	if (typeof(marked[i]) == 'undefined' || !marked[i]) {
		tr.style.backgroundColor = allRows[i][0];
	}
}

function marker(tr, i) {
	if (typeof(marked[i]) == 'undefined' || !marked[i]) {
		marked[i] = true;
		tr.style.backgroundColor = markColor;
	}
	else {
		marked[i] = false;
		tr.style.backgroundColor = allRows[i][0];
	}
}

function selectAll() {
    var label;
	for (var i in allRows) {
		marked[i] = true;
		label = "ID" + i;
		document.getElementById(label).style.backgroundColor = markColor;
	}
}

function deselectAll() {
    var label;
	marked = new Array;
	for (var i in allRows) {
	    label = "ID" + i;
		document.getElementById(label).style.backgroundColor = allRows[i][0];
	}
}


// Context menu
var recID;
var menuName;
var column;
var menuStatus
var menuWidth = new Array();

function startMenu(m,i,c) {
    var label;
	if (typeof(marked[i]) == 'undefined' || !marked[i]) {
        // exlude menu1(boton area) and menu2(header field) 
        if ((i != '')&&(m == 'menu3')) {
    		deselectAll();
	    	marked[i] = true;
	    	label = "ID" + i;
		    document.getElementById(label).style.backgroundColor = markColor;
        }
	}

	ie5=(document.getElementById && document.all && document.styleSheets)?1:0;
	nn6=(document.getElementById && !document.all)?1:0;
	if(menuStatus == 1)doHide();
	menuName = m;
	recID = i;
	column = c;
	var mWid = 200;
	if(m == 'menu2'){
		mWid = 150;
	}
	menuWidth[m] = mWid;
	menuHeight=90;
	menuStatus=0;
	document.oncontextmenu=showMenu;
	document.onmouseup=hideMenu;
}


function showMenu(e) {

	var xPos;
	var yPos;
	if (ie5) {
		if (event.clientX<document.body.offsetWidth-menuWidth[menuName]) {
            xPos=event.clientX+document.body.scrollLeft;
		} else {
            xPos=event.clientX+document.body.scrollLeft-menuWidth[menuName];
        }
		
        if (event.clientY>document.body.offsetHeight-menuHeight) {
            yPos=event.clientY+document.body.scrollTop;
		} else {
            yPos=event.clientY+document.body.scrollTop-menuHeight;
        }
	}
	else {
		if (e.pageX>window.screen.width-menuWidth[menuName] - 20) {
			xPos=e.pageX-menuWidth[menuName];
		} else {
            xPos=e.pageX;
        }
		if (e.pageY + 550>window.screen.height){
			yPos=e.pageY - 100;
		} else {
            yPos=e.pageY;
        }
	}

	cMenu = document.getElementById(menuName);
	cMenu.style.left = xPos + "px";
	cMenu.style.top  = yPos + "px";
	menuStatus = 1;
	return false;

}

function hideMenu(e) {
	if (menuStatus==1 && ((ie5 && event.button==1) || (nn6 && e.which==1))) setTimeout("doHide()",250);
}

function doHide() {
	document.getElementById(menuName).style.top = -750+"px";
	menuStatus = 0;
	document.oncontextmenu = nop;
}

function doLink(url, target, msg) {
	if (msg) {
		if (!confirm(msg)) return
	}
    //Ich habe es wieder rein, weil sonst das sortieren usw. nicht mehr funktioniert
	url = url + recID + '&' + SID;
	switch (target) {
		case "_blank":
		window.open(url,'new','left=5px,top=5px,height=540px,width=760px,scrollbars=1,resizable');
		break;
		case "_top":
		window.open(url,'new','left=5px,top=5px,width=760px,height=540px,scrollbars=1,resizable');
		break;
		default:
		location.href = url;
		break;
	}
}

function proc_marked(url, target, msg) {
	var list = "";
	for (var i in marked) {
		var is = i.split('xxx');
		var i1 = i;
		if (is[1]>0) {
			i1 = is[1];
		}
		if (marked[i]) {
			list = list + i1 + ",";
		}
	}
	if (list != "") {
		if(msg) {
			if (!confirm(msg)) return;
		}
		url = url + list.replace(/,$/,"") + '&' + SID;
		switch (target) {
			case "_blank":
			window.open(url,'new','left=5px,top=5px,height=540px,width=760px,scrollbars=1,resizable');
			break;
			case "_top":
			window.open(url,'new','left=5px,top=5px,height=540px,width=760px,scrollbars=1,resizable');
			break;
			default:
			location.href = url;
			break;
		}
	}
}

function nop() {}

//alternative view
var showbox=false;
function show_alternative_view(text, width,height,id){
	df=document.getElementById(id);
	if (typeof width!="undefined") df.style.width=width+"px";
	if (typeof height!="undefined") df.style.height=height+"px";
	//delay of 1 second
    var enddate = null;
    var endsec = 0;
    var startdate = new Date();
    var startsec = startdate.getTime();
    while ((endsec-startsec)<1000){
      enddate = new Date();
      endsec = enddate.getTime();
    }
	df.innerHTML=text;
	showbox=true;
	return false;
}

function getPosition(e)
{
	if(showbox){
		var posx=0;
		var posx=0;
		var posx=(!document.all)?e.pageX : event.clientX+document.body.scrollLeft;
		var posy=(!document.all)?e.pageY : event.clientY+ document.body.scrollTop;
		df.style.left = posx+12+ "px";
		df.style.top  = posy+12+ "px";
		df.style.display='block';
		df.style.visibility='visible';
	}

}
function hide_alternative_view(id){
	df=document.getElementById(id);
	showbox=false;
	df.style.visibility="hidden";
	df.style.display="none";
	df.style.left="-750px";
	df.style.width='';
}

document.onmousemove=getPosition;

/**
 * End Javascript functions for contextmenu, listview and altview
 */

/**
 * Javascript functions for forms
 */

/* General checks for forms */
var prev_fld = '';

function chkChrs(frm, fld, txt, searchfor, how) {
	var string = document.forms[frm].elements[fld].value;
	var stopit;
	if (how) {
		var proof = searchfor.exec(string);
		stopit = (proof != string);
	}
	else {
		stopit = searchfor.test(string);
	}
	if (stopit && (fld == prev_fld || prev_fld == '')) {
		alert(txt);
		prev_fld = fld;
		document.forms[frm].elements[fld].focus();
		return
	}
	else prev_fld = '';
}

function reg_exp(frm, fld, txt, searchfor) {
	string = document.forms[frm].elements[fld].value;
	if (string != '' && string != searchfor.exec(string)) {
		alert(txt);
		return false;
	}
}

function chkNumbers(frm, fld, txt) {
	var string = document.forms[frm].elements[fld].value;
	if (string != "") {
	        var searchfor = /^\d+$/;
		var result = searchfor.test(string);
		if(result==false) {
			alert(txt);
			document.forms[frm].elements[fld].focus();
			return false;
		}
	}
	return true;

}

function chkEqualFields(frm, fld1, fld2, txt) {
	var stringA = document.forms[frm].elements[fld1].value;
	var stringB = document.forms[frm].elements[fld2].value;
	if (stringA != stringB) {
		alert(txt);
		document.forms[frm].elements[fld1].focus();
		return false;
	}
		
	return true;
}

function chkForm(frm) {
	for (var i=1; i<chkForm.arguments.length; i++){
		var fld=chkForm.arguments[i];
		i++;
		var txt=chkForm.arguments[i];
		if (document.forms[frm].elements[fld].value == "") {
			alert(txt);
			document.forms[frm].elements[fld].focus();
			return false;
		}
	}
	return true;
}

/*
 * Create a new html editor
 *
 * @param string fieldname - Name of the textarea field 
 * @param string path_pre  - General phprojekt path
 * @return void
 */
function newFCKeditor(fieldname,path_pre) {
    var field = new FCKeditor(fieldname);
    field.BasePath = path_pre+'lib/javascript/';
    field.Width = '490';
    field.Height = '150';
    field.ReplaceTextarea();
}

/* Filemanager */

/*
 * Show or hide an element field for filemanager
 *
 * @param string elem      - id of the field
 * @param boolean showme   - True for show, False for hide
 * @return void
 */
function showhide_filemanager(elem,showme) {
    df = document.getElementById(elem);
    if (df) {
        if (showme) {
            fs = df.style;
            fs.display = 'block';
        } else {
            fs = df.style;
            fs.display = 'none';
        }
    }
    return false;
}

/*
 * Show or hide a group of fields in filemanager
 *
 * @param string slct      - id of the field to switch
 * @return void
 */
function sh_fields_filemanager(slct) {
    sl = document.getElementById(slct);
    switch(sl.value) {
        case 'f':
            showhide_filemanager('up1',true);
            showhide_filemanager('up2',true);
            showhide_filemanager('link1',false);
            break;
        case 'l':
            showhide_filemanager('up1',false);
            showhide_filemanager('up2',false);
            showhide_filemanager('link1',true);
            break;
        default:
            showhide_filemanager('up1',false);
            showhide_filemanager('up2',false);
            showhide_filemanager('link1',false);
    }
}

/* Protokoll */

/*
 * Show or hide an element field for protokoll form
 *
 * @param string elem      - id of the field
 * @param boolean showme   - True for show, False for hide
 * @param boolean overf    - Boolean flag
 * @return void
 */
function showhide_protokoll(elem,showme,overf) {
    overf = true;
    df = document.getElementById(elem);
    if (df) {
        fs = df.style
        if (showme) {
            if (elem == 'itop') {
                fs.display = 'inline';
            } else {
                fs.display = 'block';
            }
            fs.visibility = 'visible';
        } else {
            if (overf) {
                fs.display = 'none';
                fs.value = '';
            } else {
                fs.visibility = 'hidden';
            }
        }
    }
    return false;
}

/*
 * Show or hide a group of fields in protokoll
 *
 * @param string slct      - id of the field to switch
 * @return void
 */
function sh_fields_protokoll(slct) {
    sl = document.getElementById(slct);
    switch (sl.value) {
        case 'todo':
            showhide_protokoll('t_author',true,true);
            showhide_protokoll('luser',false,true);
            showhide_protokoll('luser2',true,true);
            showhide_protokoll('muser',false,true);
            showhide_protokoll('suser',true,true);
            showhide_protokoll('c',true,false);
            showhide_protokoll('idate',true,false);
            showhide_protokoll('ctct',true,true);
            showhide_protokoll('itop',true,false);
            break;
    case 'termin':
            showhide_protokoll('itop',true,false);
            showhide_protokoll('idate',true,false);
            showhide_protokoll('t_author',false,true);
            showhide_protokoll('suser',false,true);
            showhide_protokoll('muser',true,true);
            showhide_protokoll('ctct',false,true);
            break;
    case 'top':
            showhide_protokoll('itop',false,false);
            showhide_protokoll('idate',false,false);
            showhide_protokoll('suser',false,true);
            showhide_protokoll('muser',false,true);
            showhide_protokoll('ctct',false,true);
            showhide_protokoll('t_author',false,true);
            break;
    case 'beschluss':
            showhide_protokoll('itop',true,false);
            showhide_protokoll('idate',false,false);
            showhide_protokoll('suser',false,true);
            showhide_protokoll('muser',false,true);
            showhide_protokoll('ctct',false,true);
            showhide_protokoll('t_author',false,true);
            break;
    case 'feststellung':
            showhide_protokoll('itop',true,false);
            showhide_protokoll('idate',false,false);
            showhide_protokoll('suser',true,true);
            showhide_protokoll('muser',false,true);
            showhide_protokoll('luser',true,true);
            showhide_protokoll('luser2',false,true);
            showhide_protokoll('ctct',false,true);
            showhide_protokoll('t_author',false,true);
            break;
    default:
            showhide_protokoll('itop',false,false);
            showhide_protokoll('idate',false,false);
            showhide_protokoll('suser',false,true);
            showhide_protokoll('muser',false,true);
            showhide_protokoll('ctct',false,true);
            showhide_protokoll('t_author',false,true);
            break;
    }
}

/*
 * Copy the value of the source select, to the target select
 *
 * @param string ins - id of the source field
 * @param string out - id of the target field
 * @param string ins2 - id of the source field
 * @param string out2 - id of the target field
 * @return void
 */
function part_inherit(ins,out,ins2,out2){
    part = document.getElementById(ins);
    if (part) {
        rec = document.getElementById(out);
        rec.options.length = part.options.length;
        for (i = 0; i < part.options.length; i++) {
            rec.options[i].text = part.options[i].text;
            rec.options[i].value = part.options[i].value;
            rec.options[i].selected = part.options[i].selected;
        }
    }
    part2 = document.getElementById(ins2);
    if (part2) {
        rec2 = document.getElementById(out2);
        rec2.options.length = part2.options.length;
        for (j = 0; j < part2.options.length; j++) {
            rec2.options[j].text = part2.options[j].text;
            rec2.options[j].value = part2.options[j].value;
            rec2.options[j].selected = part2.options[j].selected;
        }
    }
    return false;
}

/*
 * Copy the value of the source select, to the target
 *
 * @param string in - id of the source field
 * @param string out - id of the target field
 * @return void
 */
function copy_values(ins,out,additional){
    part = document.getElementById(ins);
    if (part) {
        rec = document.getElementById(out);
        //unset rec
        for (i = 0; i < rec.options.length; i++) {
                rec.options[i] = null;            
        }
        j=0;
        rec.options.length = part.options.length;
        for (i = 0; i < part.options.length; i++) {
            if(part.options[i].selected == true){
                rec.options[j].text = part.options[i].text;
                rec.options[j].value = part.options[i].value;
                if(part.options[i].value==additional) var rec_selected=j;
                j++;
            }
        }
        if(rec_selected)rec.options[rec_selected].selected=true;
    }
    return false;
}

/* Mail */

//js function to include a string with the name of a db-field for the personalized newsletter
var flag = 0;
function insPlHold() {
    i = "mem[]";
    x = document.frm;
    txt = x.body.value + x.placehold.value;
    x.body.value = txt;
    x.placehold.value = '';
    x.body.focus();
    if (flag == 0) {
        flag = 1;
        x.elements[i].value = '';
        x.elements[i].disabled = true;
        document.getElementById("action_form_to_user_selector").disabled = true;
        if (x.action.value == 'fax') {
            x.additional_fax.value = '';
            x.additional_fax.disabled = true;
        }
        if (x.action.value == 'email') {
            x.additional_mail.value = '';
            x.additional_mail.disabled = true;
            x.cc.value = '';
            x.cc.disabled = true;
            x.bcc.value = '';
            x.bcc.disabled = true;
        }
    }
}

function remove_dbfields() {
    i = "mem[]";
    x = document.frm;
    txt = x.body.value;
    regex = /\|db-field:[^\|]*\|/g;
    x.body.value = txt.replace(regex, '');
    x.body.focus();
    
    x.elements[i].disabled = false;
    document.getElementById("action_form_to_user_selector").disabled = false;
    if (x.action.value == 'fax') {
        x.additional_fax.disabled = false;
    }
    if (x.action.value == 'email') {
        x.additional_mail.disabled = false;
        x.cc.disabled = false;
        x.bcc.disabled = false;
    }
    flag = 0;
}

function activate_html() {
    document.frm.mode.value = "send_form";
    document.frm.html.value = "true";
    document.frm.submit();
}

/* Projects */
function formSubmit2(theForm) {
    theForm.target = '_self';
    theForm.action = 'projects.php?mode=gantt';
}

function formSubmit1(theForm) {
    theForm.target = '_blank';
    theForm.action = 'projects_gantt.php';
}
/**
 * End Javascript functions for forms
 */

/**
 * Javascript functions for use links
 */

/*
 * Open a new window
 *
 * @param string data - All data to make the link
 * @return void
 */
function make_new_win(url,title,parameters) {
    var w = window.open(url,title,parameters);
    w.focus();
}

/*
 * Open a new window for edit filters
 *
 * @param string data - All data to make the link
 * @return void
 */
function manage_filters(path_pre,filtermodule,module,mode,ID) {
    var url = path_pre+"lib/dbman_filter_pop.php?module="+filtermodule+"&opener="+module+"&mode="+mode+"&ID="+ID;
    var title = "Filter";
    var parameters = "left=100,top=100,width=600,height=270,toolbar=0,location=0,directories=0,status=0,menubar=0,scrollbars=0,resizable=1";
    make_new_win(url,title,parameters);
}

/*
 * Open a new window for edit stats
 *
 * @param string data - All data to make the link
 * @return void
 */
function manage_project_statistic(path_pre,module,mode,ID) {
    var url = path_pre+"projects/projects_stats_pop.php?module="+module+"&opener="+module+"&mode="+mode+"&ID="+ID;
    var title = "Stats";
    var parameters = "left=100,top=100,width=600,height=200,toolbar=0,location=0,directories=0,status=0,menubar=0,scrollbars=0,resizable=1";
    make_new_win(url,title,parameters);
}

/*
 * Open a new window for edit a related object
 *
 * @param string data - All data to make the link
 * @return void
 */
function manage_related_object(path_pre,module_alias,projekt_ID,contact_ID,sid,getstring) {
    if (module_alias == 'addons') {
        var url = path_pre+module_alias+"/addon.php?mode=forms&projekt_ID="+projekt_ID+"&contact_ID="+contact_ID+"&justform=1&"+getstring+sid;
    }   else if (module_alias != 'mail') {
        var url = path_pre+module_alias+"/"+module_alias+".php?mode=forms&projekt_ID="+projekt_ID+"&contact_ID="+contact_ID+"&justform=1"+getstring+sid;
    } else {
        var url = path_pre+module_alias+"/"+module_alias+".php?mode=send_form&form=email&projekt_ID="+projekt_ID+"&contact_ID="+contact_ID+"&justform=1"+getstring+sid;
    }
    var title = 'related_object';
    var parameters = 'width=1140px,height=540px,scrollbars=yes,resizable=yes';
    make_new_win(url,title,parameters);
}

function go_web() {
	x = document.frm.url.value;
	if (x.substr(0,4) != "http") x = "http://" + x;
	window.open(x,"_blank");
}

function mailto(field,adress,sessID,quickmail) {
	// sessid is deprecated -> FIXME
	if(field != 0) adress = document.frm[field].value;
	if (quickmail != 0){
		x = '&' + SID;
		path = self.location.href;
		path = path.replace(/[^\/]+\/[^\/]+\.php.*/,'');
		location.href = path + "mail/mail.php?mode=send_form&amp;form=email&recipient=" + adress + x;
	}
	else location.href = "mailto:" + adress;
}

function go_phone(phonetype,phonenumber) {
	window.open("../misc/cti_" + phonetype + ".inc.php?phonenumber=" + phonenumber,"_blank","toolbar=0,location=0,directories=0,status=0,menubar=0,scrollbars=0,left=200,top=200,width=400,height=150,resizable=1")
}
/**
 * End Javascript functions for use links
 */


/**
 * Misc Javascript functions
 */

/*
 * Reload the parent window
 * and close the actual popup
 *
 * @param void
 * @return void
 */
function ReloadParentAndClose() {
    self.opener.location.reload();
    self.close();
}

/*
 * Display or hidde an id block
 *
 * @param string elem    - Id to hide/show
 * @param boolean status - True for hide
 * @return void
 */
function change_open(elem,status){
    df = document.getElementById(elem);
    if (df) {
        fs = df.style;
        if (status != true) {
            fs.display = 'block';
            fs.visibility = 'visible';
        } else {
            fs.display = 'none';
            fs.value = '';
        }
    }

    return false;
}

/**
 * End Misc Javascript functions
 */
