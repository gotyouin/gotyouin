/**
* @file BigButton.js
*
* This is creates a large Button that covers most of the screen width.
*
* @author Hal Burgiss  2013-01-15
*/

function BigButton( title, args  /* args is optional, must be json object if specified */  ) {
	"use strict";

	// DBS application configuration variables
	var $ = require( '/application/Application' );

	var _defaults = {
		title: title,
//		borderStyle: Ti.UI.INPUT_BORDERSTYLE_ROUNDED,
		borderRadius: 5,		// note DO NOT ADD 'dp' !!! 
		borderWidth: 1,
		borderColor: '#ccc',
		color: '#fff',
		backgroundColor: $.defAccentColor,
		font: { fontSize: '20dp', fontFamily:'TitilliumWeb-Regular' },
		top: '10%', 
		left: '10%',
		style: Ti.UI.iPhone.SystemButtonStyle.PLAIN,
		width: '80%', 
		height: '48dp'
	};
	
	if ( null !== args && typeof args != 'object' ) args = {};

	// over-ride _defaults with passed args, if any
	var attrname;
	for (attrname in args) { _defaults[attrname] = args[attrname]; }

	return Ti.UI.createButton( _defaults );
}

module.exports = BigButton;
