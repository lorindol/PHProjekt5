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
var FCKCloseWindow = function(name)
{
	this.Name = name;
}

FCKCloseWindow.prototype.Execute = function()
{
	parent.window.close();
}

// manage the plugins' button behavior
FCKCloseWindow.prototype.GetState = function()
{
	return FCK_TRISTATE_OFF;
	// default behavior, sometimes you wish to have some kind of if statement here
}

FCKCommands.RegisterCommand('CloseWindow', new FCKCloseWindow('CloseWindow'));

// Create the "Plaholder" toolbar button.
//opject_name, button_alt_text
var oCloseWindowItem = new FCKToolbarButton('CloseWindow', FCKLang.CloseWindow ) ; 
oCloseWindowItem.IconPath = FCKConfig.PluginsPath + 'CloseWindow/closewindow.gif' ;
FCKToolbarItems.RegisterItem('CloseWindow', oCloseWindowItem ) ;


