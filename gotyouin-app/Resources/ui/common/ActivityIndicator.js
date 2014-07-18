/**
 * @file ActivityIndicator.js
 *
 * Creates a generic window with an activity indicator, self executing.
 *   
 * @halburgiss 2013-01-11
 */

( function ActivityIndicator() {

	var $ = require( '/application/Application' );

	var self = Titanium.UI.createWindow({
		title:'Wait ...',
		backgroundColor: $.defBackgroundColor
	});

	var actInd = Ti.UI.createActivityIndicator({
		width: Titanium.UI.SIZE,
		height: Titanium.UI.SIZE,
		message: ' Loading ... ',
		font: { fontSize : '32dp' },
//		style: Ti.UI.iPhone.ActivityIndicatorStyle.DARK,
		color: $.defAccentColor,
		IndicatorColor: $.defAccentColor
	});

	actInd.show();
	self.add( actInd );
	self.open();

	setTimeout( function() {
		self.close(); self = actInd = null;
	}, 5000 );


})();

