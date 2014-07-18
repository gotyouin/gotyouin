/**
* @file Alert.js
*
* Creates a dialog box, more like JavaScript confirm(), but could be used for
* any dialog box with one or more buttons. This is true modal, unlike the built
* in alert(), which seems non-modal (at least on iphone)
*
* @author Hal Burgiss 2013-01-30
*/

function Alert( message, args /* args is optional, must be json object if specified */ ) {

	// DBS application configuration variables
	var $ = require( '/application/Application' );

	var defaults = {
		message: message,
		title: 'Confirm?',
		buttonNames: [ 'Yes', 'No' ],
		cancel: 1   // cancel defaults to second button
	};

	if ( null !== args && typeof args != 'object' ) args = {};

	// over-ride _defaults with passed args, if any
	var attrname;
	for (attrname in args) {
		defaults[attrname] = args[attrname];
	}

	self = Ti.UI.createAlertDialog(  defaults ) ;
	self.show();
	return self;
}

module.exports = Alert;
