/**
* @file AppointmentsView.js
*
* Generic Webview to show appointments for barbers and owners linked from menu.
* (This may be very similar to the Barbers home screen as of 2013-02-05.
*
* @author Hal Burgiss 2013-02-01
*/

//Constructor
function AppointmentsView() {
	"use strict";

	// DBS application configuration variables
	var $ = require( '/application/Application' );

	var js = require( '/application/library/JavascriptExtensions' );
	// custom date protoype
	var field_date = js.dateToYMD();
	$.info( 'Field date: ' + field_date );
	var WebView = require('/ui/common/WebView'), webView;
	
	if ( $.user.userType === 'Owner') {
		webView = new WebView( '/user/' + $.user.userID + '/shop/appointments?app1' );		
	} else {
		webView = new WebView( '/user/' + $.user.userID + '/appointments?app=1&field_date_value=' + field_date );
	}

	$.info('appointments view module');

	// MainView is the same for all screens. This is the top nav bar and main view //
	var MainView = require( '/ui/common/views/MainView' );
	var self = new MainView( webView );

	$.info( 'AccountsView webview is called' );

	var actInd = Ti.UI.createActivityIndicator({
		width: Ti.UI.SIZE,
		height: Ti.UI.SIZE,
		message: ' loading ... ',
		font: { fontSize : '24dp' },
		top: '10%',
		color: $.defAccentColor,
		IndicatorColor: $.defAccentColor
	});
	actInd.show();
	self.add( actInd );

	
	try {

		self.add( webView );
		webView.height = 0;
	
		$.info( 'AccountsView webview is loaded' );

	
		webView.addEventListener( "load", function(e) {
			if ( actInd !== null ) {
				self.remove( actInd ); actInd = null;
			}
			// Handle 404 -- pageStatus is from custom DBS js in footer.php
			var response = webView.evalJS( "pageStatus" );
			if ( response && '404' === response ) {
				var Echo = require( '/ui/common/Echo' );
				self.add ( new Echo( 'Sorry, but there is a problem, please try later.', { left: "10%", right: "10%", top: "20%" } ) );
				self.remove( webView );
				webView = null;
			} else {
				webView.height = 'auto';
			}
		});
		$.info( 'AccountsView Listener loaded' );

	} catch( error ) {

		var Echo = require( '/ui/common/Echo' );
		var Logger = require( '/application/Logging' );
		var logger = new Logger();

		// error handling.
		self.add( new Echo( 'There was a problem, please try again.' ) );
		logger.error( 'Error on Appointments view: ' + error.message );
		$.info( 'Appt Error: ' + error.message );
	
	} finally {

	}

	$.info( 'AccountsView is done' );

	return self;
}

module.exports = AppointmentsView;
