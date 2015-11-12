/**
 * Yes/No Element
 *
 * @copyright: Copyright (C) 2005-2013, fabrikar.com - All rights reserved.
 * @license:   GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

FbYesno = new Class({
	Extends: FbRadio,
	initialize: function (element, options) {
		this.plugin = 'fabrikyesno';
		this.parent(element, options);
	},
	
	getChangeEvent: function () {
		return this.options.changeEvent;
	}
	
});
