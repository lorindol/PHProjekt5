/*
	Copyright (c) 2004-2006, The Dojo Foundation
	All Rights Reserved.

	Licensed under the Academic Free License version 2.1 or above OR the
	modified BSD license. For more information on Dojo licensing, see:

		http://dojotoolkit.org/community/licensing.shtml
*/

dojo.provide("dojo.widget.PHProjektDatePicker");
dojo.provide("dojo.widget.PHProjektDatePicker.util");
dojo.require("dojo.widget.DomWidget");
dojo.require("dojo.date");

// NOTE: this function is only used as mixin (never as a constructor)
dojo.widget.PHProjektDatePicker = function() {
	// the following aliases prevent breaking people using 0.2.x
	this.months = dojo.date.months,
	this.weekdays = dojo.date.days,
	this.toRfcDate = dojo.widget.PHProjektDatePicker.util.toRfcDate,
	this.fromRfcDate = dojo.widget.PHProjektDatePicker.util.fromRfcDate,
	this.initFirstSaturday = dojo.widget.PHProjektDatePicker.util.initFirstSaturday
};

dojo.requireAfterIf("html", "dojo.widget.html.PHProjektDatePicker");

dojo.widget.PHProjektDatePicker.util = new function() {
	this.months = dojo.date.months;
	this.weekdays = dojo.date.days;
	
	this.toRfcDate = function(jsDate) {
		if(!jsDate) {
			var jsDate = new Date();
		}
		// because this is a date picker and not a time picker, we don't return a time
		return dojo.date.format(jsDate, "%Y-%m-%d");
	}
	
	this.fromRfcDate = function(rfcDate) {
		// backwards compatible support for use of "any" instead of just not 
		// including the time
		if(rfcDate.indexOf("Tany")!=-1) {
			rfcDate = rfcDate.replace("Tany","");
		}
		var jsDate = new Date();
		dojo.date.setIso8601(jsDate, rfcDate);
		return jsDate;
	}
	
	this.initFirstSaturday = function(month, year) {
		if(!month) {
			month = this.date.getMonth();
		}
		if(!year) {
			year = this.date.getFullYear();
		}
		var firstOfMonth = new Date(year, month, 1);

		return {year: year, month: month, date: dojo.date.firstSaturday - firstOfMonth.getDay()};
	}
}
