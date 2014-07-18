/**
* @file TextField.js
*
* This is the base textField definition for Got You In.
*
* @author Hal Burgiss  2013-01-15
*/

// constructor
function TextField( args  /* args is optional, must be json object if specified */ ) {
	"use strict";

	// DBS application configuration variables
	var $ = require( '/application/Application' ), attrname;

	var _defaults = {
		borderStyle: Ti.UI.INPUT_BORDERSTYLE_ROUNDED,
		borderRadius: 5,		// DO NOT use dp prefix!!!
		borderWidth: 1,
		borderColor: '#ccc', 
		color: '#222222',
		backgroundColor: '#ddd',
		font: { fontSize: '16dp' },
		top: '10%', 
		left: '15%',
		width: '70%', 
		height: '40dp'
	};

	if ( $.isAndroid ) {
		// Android by default, shows the keyboard if there is ANY text input field on the screen. Why?
		_defaults.softKeyboardOnFocus = Ti.UI.Android.SOFT_KEYBOARD_HIDE_ON_FOCUS;
	}

	if ( null !== args && typeof args != 'object' ) args = {};

	// over-ride _defaults with passed args, if any
	for (attrname in args) { _defaults[attrname] = args[attrname]; }

	// create textfield object
	var self = Ti.UI.createTextField( _defaults );

	// Stop Androids autofocus of textFields.
	if ( $.isAndroid ) {
		self.blur();
		self.addEventListener( 'click',  function(e) {
			self.softKeyboardOnFocus = Titanium.UI.Android.SOFT_KEYBOARD_SHOW_ON_FOCUS;
			self.focus();
		});
	}
	
	return self;
}

module.exports = TextField;
