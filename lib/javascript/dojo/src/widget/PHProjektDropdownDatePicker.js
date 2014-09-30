/*
	Copyright (c) 2004-2006, The Dojo Foundation
	All Rights Reserved.

	Licensed under the Academic Free License version 2.1 or above OR the
	modified BSD license. For more information on Dojo licensing, see:

		http://dojotoolkit.org/community/licensing.shtml
*/

dojo.provide("dojo.widget.PHProjektDropdownDatePicker");
dojo.require("dojo.widget.*");
dojo.require("dojo.widget.DropdownContainer");
dojo.require("dojo.widget.PHProjektDatePicker");
dojo.require("dojo.event.*");
dojo.require("dojo.html");

dojo.widget.defineWidget(
	"dojo.widget.PHProjektDropdownDatePicker",
	dojo.widget.DropdownContainer,
	{
		iconURL: dojo.uri.dojoUri("src/widget/templates/images/dateIcon.gif"),
		iconAlt: "Select a Date",
		zIndex: "10",
		datePicker: null,
		
		dateFormat: "%Y-%m-%d",
		date: null,
		
		fillInTemplate: function(args, frag){
			dojo.widget.PHProjektDropdownDatePicker.superclass.fillInTemplate.call(this, args, frag);
			var source = this.getFragNodeRef(frag);
			
			if(args.date)   { this.date = new Date(args.date); }
			if(args.months) { 
                var parts = args.months.split("-");
                for (var i = 0; i < parts.length; i++) {                
                    dojo.date.months[i] = parts[i+1];
                }
            }
			if(args.days) { 
                var parts = args.days.split("-");
                for (var i = 0; i < parts.length; i++) {
                    dojo.date.days[i] = parts[i];
                }
            }

            if (args.firstdayweek)  {
                dojo.date.firstSaturday = 7 + parseInt(args.firstdayweek);
            }

			var dpNode = document.createElement("div");
			this.containerNode.appendChild(dpNode);
			
			var dateProps = { widgetContainerId: this.widgetId };
			if(this.date){
				dateProps["date"] = this.date;
				dateProps["storedDate"] = dojo.widget.PHProjektDatePicker.util.toRfcDate(this.date);
				this.inputNode.value = dojo.date.format(this.date, this.dateFormat);
                this.inputNode.value = convertDateFormatDb2User(this.inputNode.value);
			}

			this.datePicker = dojo.widget.createWidget("PHProjektDatePicker", dateProps, dpNode);
			dojo.event.connect(this.datePicker, "onSetDate", this, "onSetDate");
			this.containerNode.style.zIndex = this.zIndex;
			this.containerNode.style.backgroundColor = "transparent";
		},
		
		onSetDate: function(){
			this.inputNode.value = convertDateFormatDb2User(dojo.date.format(this.datePicker.date, this.dateFormat));
			this.hideContainer();
		},
		
		onInputChange: function(){
			var tmp = new Date(this.inputNode.value);
			this.datePicker.date = tmp;
			this.datePicker.setDate(dojo.widget.PHProjektDatePicker.util.toRfcDate(tmp));
			this.datePicker.initData();
			this.datePicker.initUI();
		}
	},
	"html"
);

dojo.widget.tags.addParseTreeHandler("dojo:PHProjektDropdownDatePicker");
