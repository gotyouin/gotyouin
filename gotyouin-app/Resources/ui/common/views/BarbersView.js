/**
* @file BarbersView.js
*
* Webview of Barbers associated with a shop --- owners menu only. As of
* 2013-02-05, very similar content for owners home screen.
*
* @author Hal Burgiss 2013-02-01
*/

//Constructor
function BarbersView() {
	"use strict";

	// DBS application configuration variables
	var $ = require( '/application/Application' );

	$.info('barbers view module');

	// create the webView
	var WebView = require( '/ui/common/WebView' );
	var webView =  new WebView( '/user/' + $.user.userID + '/barbers?app=1' );

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
			var location = webView.evalJS( "location.href" );
			$.info( "Location: " + location );
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
		logger.error( 'Error on BarbersView: ' + error.message );

	} finally {

	}

	return self;
}

module.exports = BarbersView;
