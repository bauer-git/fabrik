/**
 * Calendar Visualization
 *
 * @copyright: Copyright (C) 2005-2013, fabrikar.com - All rights reserved.
 * @license:   GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

var fabrikFullcalendar = new Class({
	Implements: [Options],
	options: {
	},

	initialize: function (ref, options) {
		this.el  = document.id(ref);
		this.setOptions(options);
		
		this.date = new Date();

		this.windowopts = {
				'id': 'addeventwin',
				title: 'add/edit event',
				loadMethod: 'xhr',
				minimizable: false,
				evalScripts: true,
				width: 380,
				height: 320,
				onContentLoaded: function (win) {
					win.fitToContent();
				}.bind(this)
			};
		
		if (typeOf(this.el.getElement('.addEventButton')) !== 'null') {
			this.el.getElement('.addEventButton').addEvent('click', function (e) {
				this.openAddEvent(e);
			}.bind(this));
		}

		Fabrik.addEvent('fabrik.form.submitted', function (form, json) {
			//Fabrik.Windows['chooseeventwin'].close();
			//this.addEvForm(json);
			//this.ajax.updateEvents.send();
			jQuery('#calendar').fullCalendar( 'refetchEvents' );
			Fabrik.Windows.addeventwin.close();
		}.bind(this));
		
		var eventSources = [];
		var urls = this.options.url;
		
		this.options.eventLists.each(function (eventList, eventListKey) {
			eventSources.push({
				events: new Function ("start", "end", "tz", "callback",
						"new Request({" +
							"'url': '" + this.options.url.add + "&listid=" + eventList.value + "&eventListKey=" + eventListKey + "'," +
							"'evalScripts': true," +
							"'onSuccess': function (e, json) {\n" +
								"if (typeOf(json) !== 'null') {\n" +
									/*"var json = r.stripScripts(true);" +*/
									"this.processEvents(json, callback);\n" +
								"}" +
							"}.bind(this, callback)" +
						"}).send();"
					).bind(this),
				color: eventList.colour
			});
		}.bind(this));
		
		var self = this;
	    jQuery('#calendar').fullCalendar({
			header: {
				left: 'prev,next today',
				center: 'title',
				right: 'month,agendaWeek,agendaDay'
			},
	    	eventSources: eventSources,
	        // put your options and callbacks here
	        eventClick: function (calEvent, jsEvent, view) {
	        	self.viewEntry(calEvent);
	        	return false;
	        },
	        dayRender: function(date, cell) {
	        	cell.bind('dblclick', {date: date}, function(e) {
	        		alert('double click: ' + e.data.date.toString());
	        		var view = 'month';
	        		self.openAddEvent(e, view,  e.data.date)
	        	});
	        }
	    })
	},
	
	processEvents: function (json, callback) {
		json = $H(JSON.decode(json));
		var events = [];
		json.each(function (e) {
			events.push(
				{
					title: e.label,
					start: e.startdate_locale,
					url: e.link,
					listid: e._listid,
					rowid: e.__pk_val,
					formid: e._formid
				}
			)
		}.bind(events));
		callback(events);
	},
	
	/**
	 * Create window for add event form
	 * 
	 * @param  object  o
	 */
	addEvForm: function (o)
	{
		if (typeof(jQuery) !== 'undefined') {
			jQuery(this.popOver).popover('hide');
		}
		
		this.windowopts.id = 'addeventwin';
		var url = 'index.php?option=com_fabrik&controller=visualization.fullcalendar&view=visualization&task=addEvForm&format=raw&listid=' + o.listid + '&rowid=' + o.rowid;
		url += '&jos_fabrik_calendar_events___visualization_id=' + this.options.calendarId;
		url += '&visualizationid=' + this.options.calendarId;
		
		if (o.nextView) {
			url += '&nextview=' + o.nextView;
		}
		
		url += '&fabrik_window_id=' + this.windowopts.id;

		if (typeof(this.doubleclickdate) !== 'undefined') {
			url += '&start_date=' + this.doubleclickdate;
		}
		
		this.windowopts.type = 'window';
		this.windowopts.contentURL = url;
		var f = this.options.filters;
	
		this.windowopts.onContentLoaded = function (win)
		{
			f.each(function (o) {
				if (document.id(o.key)) {
					switch (document.id(o.key).get('tag')) {
					case 'select':
						document.id(o.key).selectedIndex = o.val;
						break;
					case 'input':
						document.id(o.key).value = o.val;
						break;
					}
				}
			});
			win.fitToContent(false);
		}.bind(this);
		
		Fabrik.getWindow(this.windowopts);
	},
	
	viewEntry: function (calEvent) {
		var o = {};
		o.id = calEvent.formid;
		o.rowid = calEvent.rowid;
		o.listid = calEvent.listid;
		o.nextView = 'details';
		this.addEvForm(o);
	},
	
	/**
	 * Open the add event form.
	 * 
	 * @param e    Event
	 * @param view The view which triggered the opening
	 */
	openAddEvent: function (e, view, moment)
	{
		var rawd, day, hour, min, m, o, now, thisDay;
		
		if (this.options.canAdd === false) {
			return;
		}
		
		if (this.options.viewType === 'monthView' && this.options.readonlyMonth === true) {
			return;
		}
		
		e.stop();
		
		if (e.target.hasClass('addEventButton')) {
			now = new Date();
			rawd = now.getTime();
		} else {
			//rawd = this._getTimeFromClassName(e.target.className);
			now = new Date();
			now = moment.toDate();
			rawd = now.getTime();
		}
		
		if (!this.dateInLimits(rawd)) {
			return;
		}
		
		if (e.target.get('data-date')) {
			console.log('data-date = ', e.target.get('data-date'));
			
		}
		this.date.setTime(rawd);
		d = 0;
		if (!isNaN(rawd) && rawd !== '') {
			thisDay = new Date();
			thisDay.setTime(rawd);
			m = thisDay.getMonth() + 1;
			m = (m < 10) ? "0" + m : m;
			day = thisDay.getDate();
			day = (day <  10) ? "0" + day : day;
			
			if (view !== 'month') {
				hour = thisDay.getHours();
				hour = (hour <  10) ? "0" + hour : hour;
				min = thisDay.getMinutes();
				min = (min <  10) ? "0" + min : min;
			} else {
				hour = '00';
				min = '00';
			}
			
			this.doubleclickdate = thisDay.getFullYear() + "-" + m + "-" + day + ' ' + hour + ':' + min + ':00';
			d = '&jos_fabrik_calendar_events___start_date=' + this.doubleclickdate;
		}

		if (this.options.eventLists.length > 1) {
			this.openChooseEventTypeForm(this.doubleclickdate, rawd);
		} else {
			o = {};
			o.rowid = '';
			o.id = '';
			d = '&' + this.options.eventLists[0].startdate_element + '=' + this.doubleclickdate;
			o.listid = this.options.eventLists[0].value;
			this.addEvForm(o);
		}
	},
	
	dateInLimits: function (time) {
		var d = new Date();
		d.setTime(time);
		
		if (this.options.dateLimits.min !== '') {
			var min = new Date(this.options.dateLimits.min);
			if (d < min) {
				alert(Joomla.JText._('PLG_VISUALIZATION_CALENDAR_DATE_ADD_TOO_EARLY'));
				return false;
			}
		}
		
		if (this.options.dateLimits.max !== '') {
			var max = new Date(this.options.dateLimits.max);
			if (d > max) {
				alert(Joomla.JText._('PLG_VISUALIZATION_CALENDAR_DATE_ADD_TOO_LATE'));
				return false;
			}
		}
		
		return true;
	},
	
	openChooseEventTypeForm: function (d, rawd)
	{
		// Rowid is the record to load if editing
		var url = 'index.php?option=com_fabrik&tmpl=component&view=visualization&controller=visualization.fullcalendar&task=chooseaddevent&id=' + this.options.calendarId + '&d=' + d + '&rawd=' + rawd;

		// Fix for renderContext when rendered in content plugin
		url += '&renderContext=' + this.el.id.replace(/visualization_/, '');
		this.windowopts.contentURL = url;
		this.windowopts.id = 'chooseeventwin';
		this.windowopts.onContentLoaded = function ()
		{
			var myfx = new Fx.Scroll(window).toElement('chooseeventwin');
		};
		Fabrik.getWindow(this.windowopts);
	},

})
