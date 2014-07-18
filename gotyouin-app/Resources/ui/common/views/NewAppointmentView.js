/**
* @file NewAppointView.js
*
* Webview for creating a New Appointment, for customers, barbers and owners
*
* @author Hal Burgiss 2013-02-01
*/

//Constructor
function NewAppointmentView() {
	"use strict";

	// DBS application configuration variables
	var $ = require( '/application/Application' );

	$.info('new appointment view module');

	// Load the webView
	var WebView = require( '/ui/common/WebView' ), webView;
	//var webView =  new WebView( '/new-appointment?app=1' );
	if ( $.user.userType == 'Barber' || $.user.userType == 'Regular Barber' || $.user.userType == 'Independent Barber' ) {
		$.info( 'New appt for barber');
		webView =  new WebView( '/node/add/booking?app=1' );
	} else { 
		webView =  new WebView( '/search/barbers?app=1' );
	}
	
	// MainView is the same for all screens. This is the top nav bar and main view //
	var MainView = require( '/ui/common/views/MainView' );
	var self = new MainView( webView );

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

	} catch( error ) {

		var Echo = require( '/ui/common/Echo' );
		var Logger = require( '/application/Logging' );
		var logger = new Logger();

		// error handling.
		self.add( new Echo( 'There was a problem, please try again.' ) );
		logger.error( 'Error on New Appointment form: ' + error.message );
	
	} finally {

	}

	return self;
}

module.exports = NewAppointmentView;
