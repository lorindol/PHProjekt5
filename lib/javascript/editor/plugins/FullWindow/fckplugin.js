/*
 * FCKeditor - The text editor for internet
 * Copyright (C) 2003-2004 Frederico Caldeira Knabben
 * 
 * Licensed under the terms of the GNU Lesser General Public License:
 * 		http://www.opensource.org/licenses/lgpl-license.php
 * 
 * For further information visit:
 * 		http://www.fckeditor.net/
 * 
 * Original plugin:
 * Written By Michal Aichinger, 2005
 * michal.aichinger@netdogs.cz
 * 
 * Update september 2005:
 * Written by Edwin Vlieg
 * info@flydesign.nl
 */
// Register the related command.

var FCKFullWindow = function(name) 
{ 
    this.Name = name; 
}


FCKFullWindow.prototype.Execute = function() 
{ 
    var oDialogInfo = new Object() ;
	oDialogInfo.Editor = window;
	FCKDialog.Show(oDialogInfo, 'FullScreen', FCKConfig.PluginsPath + 'FullWindow/fullwindow.html?name=' + FCK.Name, 800, 780);
} 
 
// manage the plugins' button behavior 
FCKFullWindow.prototype.GetState = function() 
{ 
    return FCK_TRISTATE_OFF; 
    // default behavior, sometimes you wish to have some kind of if statement here 
} 

FCKCommands.RegisterCommand('FullWindow', new FCKFullWindow('FullWindow'));

// Create the "Plaholder" toolbar button.
var oFullWindowItem = new FCKToolbarButton("FullWindow", FCKLang.FullWindow ) ;
oFullWindowItem.IconPath = FCKConfig.PluginsPath + 'FullWindow/fullwindow.gif' ;
FCKToolbarItems.RegisterItem('FullWindow', oFullWindowItem ) ;
