/**
* @file HomeView.js
*
* Home screen. Note: The content here is largely determined by the
* /application/HomeScreenController.js, depending on userType.
*
* @author Hal Burgiss  2013-01-14
*/

//Home View Constructor
function HomeView() {
	"use strict";

	// DBS application wide configuration variables and settings
	var $ = require( '/application/Application' );

	$.info('homeview module');

	// MainView is the same for all screens. This is the top nav bar and main view //
	var MainView = require( '/ui/common/views/MainView' );
	var self = new MainView();

	// subview to hold the main content area for this screen
	var mainContent = Ti.UI.createScrollView({
		backgroundColor: 'transparent',
		width:'100%',
		height: Titanium.UI.FILL,
		layout:'vertical',
		textAlign: 'center'
	});
	self.add( mainContent );

	// Check if user status needs updating, after the init stuff has had a
	// chance to run. This is done twice, to catch faster and slower
	// devices.
	setTimeout( function() {
			if ( $.homeNeedsUpdating === true || ( $.isset( $.user) && $.isset( $.user.userType) && $.user.userType == 'Anonymous' && $.isset( $.user.firstName ) ) ) {
				// we are here because someone's session expired and we have saved values for them.
				homeController.reset();
			}
	}, 1000 );

	// hmmm ... need a slight delay here, seems it takes longer for the window than the view.
	setTimeout( function() {
		// check if notification needs to be updated on click and focus events
		//$.currentWindow.addEventListener( 'click', function() { Notification.text = $.notification ; } );

			if ( $.homeNeedsUpdating === true || ( $.isset( $.user) && $.isset( $.user.userType) && $.user.userType == 'Anonymous' && $.isset( $.user.firstName ) ) ) {

				// we are here because someone's session expired and we have saved values for them.
				homeController.reset();
			}

		// NOTE: Home screen content can change based on whether logged in or not.
		$.currentWindow.addEventListener( 'focus', function() {

			if ( $.homeNeedsUpdating === true || $.userNeedsUpdating ) {

				// update the home screen if something has happened to
				// cause a recreation, eg a logout.
				homeController.reset();
			}
		} );
	}, 3000 );


	/**
	* Home screen content is handled by the HomeScreenController, and is tailored to userTypes. 2013-02-01
	*/
	var HomeScreenController = require( '/application/HomeScreenController' ), homeView = null;
	var homeController = new HomeScreenController( mainContent, homeView );

	// Load a hidden webview to talk to the server
	//var WebView = require( '/ui/common/WebView' ), webView;

	// setInterval here is used to keep session fresh, and to check for notifications
	// using a hidden webview.
//	var intervalSeconds = ( $.isset( $.user) && $.isset( $.user.userType ) && $.user.userType === 'Customer') ? 14400 : 3600;
/*

 // does not work if iphone is asleep

	var intervalSeconds = ( $.isset( $.user) && $.isset( $.user.userType ) && $.user.userType === 'Customer') ? 1440 : 360;
	if ( $.isIphone ) Ti.App.idleTimerDisabled = true; // FIXME
	setInterval( function() {
		$.info("Starting test interval ...");
		if ( ! $.userLoggedIn() ) {
			$.info( 'Exiting test interval ... ' );
			return;
		}

		try {

			if (  Titanium.Network.networkType !== Titanium.Network.NETWORK_NONE ) {
				// routine checkin for notifications
				webView =  new WebView( '/gotyouin_php/gotyouin_user_status.php?testing=' + Ti.Platform.osname + '&UUID=' + Ti.Platform.id );
				webView.hide();
				webView.height = 0;
				self.add ( webView );
				webView.addEventListener( 'load', function( e ) {
					// sanity check
					if ( $.isset( webView ) && webView.evalJS("sessVal") && typeof webView.evalJS("sessVal") == "string" && webView.evalJS("sessVal").length > 1 ) {
						$.info('Running eval code on setinterval');
						var notifications = JSON.parse( webView.evalJS("notifications") );	// array

						if ( $.isset( notifications ) && notifications.length > 0 ) {

							// add any new notifications to whatever might be existing notifications.
							$.notifications = notifications.concat( $.notifications );
							// set max length
							$.notifications.length = ( $.user.userType === 'Customer' ) ? 5 : 25;
							// set flag that we have new notifications waiting
							$.newNotifications = true;
						}
					}
				});

				$.info('Running setinterval update');
				setTimeout( function() {
					try {
						if ( $.isset( webView ) ) {
							self.remove( webView );
						}
					} catch(e) {
						$.info( 'Error killing home screen webView');
					} finally {
						notifications = webView = null;
					}
				}, 30000 );
			} else {
				$.info( 'No network on interval' );
			}

		} catch ( error ) {

			// Log error to database
			var Logger = require( '/application/Logging' );
			var logger = new Logger();
			logger.error( 'Error running setInterval from Home: ' + error.message );
		}

		$.info( 'Loading session webview for testing' );
	}, intervalSeconds * 1000   );
*/
	return self;
}

module.exports = HomeView;
