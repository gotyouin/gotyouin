/**
* @file Echo.js
*
* Echoes out text using Ti createLabel() with some defaults set.
*
* @author Hal Burgiss 2013-01-22
*/

function Echo( labelText, args /* args is optional, must be json format if specified */ ) {

	// DBS application configuration variables
	var $ = require( '/application/Application' );

	var defaults = {
		text: labelText,
		height: 'auto',
		width: 'auto',
		color: $.defFontColor,
		backgroundColor: 'transparent',
		font: { fontSize: $.defFontSize },
//		left: '20%',
		top: '4%'
//		textAlign: Ti.UI.TEXT_ALIGNMENT_CENTER
	};

	if ( null !== args && typeof args != 'object' ) {
		args = {};
	}

	// over-ride defaults with passed args, if any
	var attrname;
	for ( attrname in args ) { 
		defaults[ attrname ] = args[ attrname ]; 
	}

	return Ti.UI.createLabel(  defaults ) ;
}

module.exports = Echo;
