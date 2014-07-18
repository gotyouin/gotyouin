/**
* @file SmallButton.js
*
* This is creates a smallish Button.
*
* @author Hal Burgiss  2013-01-15
*/

function SmallButton( title, args  /* args is optional, must be json object if specified */ ) {

	// DBS application configuration variables
	var $ = require( '/application/Application' ), attrname;

	var _defaults = {
		title: title,
		borderRadius: 1,		// DO NOT use 'dp' prefix!!!
		borderWidth: 1,
		borderColor: '#ccc',
		color: $.appDefFontColor,
		//backgroundColor: $.defAccentColor,
		backgroundGradient: {
			type: 'linear',
			startPoint: { x: '50%', y: '0%' },
			endPoint: { x: '50%', y: '100%' },
			colors: [ { color: 'ccc'}, { color: '222'}] 
		},
		style: Ti.UI.iPhone.SystemButtonStyle.PLAIN,
		font: { fontSize: '14dp' },
		top: '4dp', 
		left: '10%',
		width: '24%', 
		height: '32dp'
	};

	if ( null !== args && typeof args != 'object' ) args = {};

	// over-ride _defaults with passed args, if any
	for (attrname in args) { _defaults[attrname] = args[attrname]; }

	return Ti.UI.createButton( _defaults );
}

module.exports = SmallButton;
