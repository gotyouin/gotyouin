/**
* @file Init.js
*
* Important odds and ends that get run only when the app is first started.
*
* @author Hal Burgiss  2013-01-17
*/

( function() {
	"use strict";

	// app wide settings and config options for Got You In
	var $ = require( '/application/Application' );
	var Logger, logger;

//	Ti.App.Properties.setList( 'notifications', [ {'text' : 'My first message', 'expires' : 20130210 }] ) ;

	try {

		// persistant variables that survive closing of app
		if ( null === Ti.App.Properties.getString('installDate') || Ti.App.Properties.getString( 'installDate' ) === undefined  ) {
			Ti.App.Properties.setString( 'installDate', new Date() );
		}

		$.registerID = Ti.App.Properties.getString( 'registerID' ) ;
		$.info( 'Init: Registered User ID: ' + $.registerID );

		// Special hidden window that stays open to sync data with Drupal backend
		$.drupalSyncWindow = Ti.UI.createWindow({
			zIndex: -1,
			exitOnClose: false
		});

		$.drupalSyncWindow.hide();
		$.drupalSyncWindow.open();

		// check network status first
		if (  Titanium.Network.networkType != Titanium.Network.NETWORK_NONE ) {

			if ( null === $.registerID || ! $.isset( $.registerID ) || parseInt( $.registerID, 10 ) < 1 ) {

				// this gets done one time only -- when first installed (unless of network error.
				require( '/application/RegisterNewUser' );

				// track whether user has an account or not.
				Ti.App.Properties.setString( 'hasAccount', 'false' );
			}

			$.info( 'Init: Initializing user data');
			$.info( 'Init: Running OS version: ' + parseInt( Ti.Platform.version, 10 ) );
// testing
//Ti.App.Properties.setList( 'notifications', [ { 'name' : 'appointemt', 'expires' : 210 }, { 'name' : 'test 2', 'expires' : 212 } ] ) ;

			$.notifications = Ti.App.Properties.getList( 'notifications', [] ) ;
			$.info( 'Initializing notifications: ' + $.notifications.length );
			//$.info( 'TEST: ' + $.notifications[1].name );

			var initWindow;
/*
			// Open a window for hidden webview.
			initWindow = Ti.UI.createWindow({
				zIndex: -1
			});

			initWindow.hide();
			initWindow.open();

*/
			// Update user data from Drupal, see if we are logged in, etc. FIXME: This may not be working.
			var DrupalUser = require( '/application/GetDrupalUser' );
			new DrupalUser( $.drupalSyncWindow );

			setTimeout( function() {
				// persistant variables that survive closing of app
				if ( null !== Ti.App.Properties.getString('hasAccount') && Ti.App.Properties.getString( 'hasAccount' ) !== undefined ) {
					if ( Ti.App.Properties.getString( 'hasAccount') == 'true' || Ti.App.Properties.getString('hasAccount') === true ) {
						$.info( 'hasAccount == true' );
						if (! $.isset( $.user )) {
							$.user = {};
							$.user = $.user.userType = 'Anonymous';
						}
						$.user.hasAccount = true;
					}
				}
				if ( $.isset( initWindow ) ) {
					initWindow.close();
					initWindow = null;
				}
				$.info( 'Init: Closing initWindow for Drupal user code' );
			}, 2500 );

			try {
				Ti.Geolocation.setAccuracy( Ti.Geolocation.ACCURACY_HUNDRED_METERS );
				Ti.Geolocation.getCurrentPosition( function(e) {
					if ( e.success ) {
						$.info(  'LAT: ' + e.coords.latitude );
						$.geoCoords = [ e.coords.latitude, e.coords.longitude ];
					}
				});

			} catch( error ) {

				Logger = require( '/application/Logging' );
				logger = new Logger();
				logger.error( 'Error getting Geo Data: ' + error.message );
				$.info( 'Failed to get Geo data:' + error.message );
			}

		} else {
			// no network
			$.info( 'No network on init' );

			// Log error
			Logger = require( '/application/Logging' );
			logger = new Logger();
			logger.error( 'Error on init: NO NETWORK' );
			alert( 'Network is not available' );

		}
		// temp testing 2013-01-23, dead soldiers
		Ti.App.Properties.removeProperty( 'Xuser' );
		Ti.App.Properties.removeProperty( 'userID' );
		Ti.App.Properties.removeProperty( 'startDate' );
		Ti.App.Properties.removeProperty( 'windowsList' );
		Ti.App.Properties.removeProperty( 'activationDate' );
		Ti.App.Properties.removeProperty( 'homeNotification' );

	} catch( error ) {

		Logger = require( '/application/Logging' );
		logger = new Logger();
		logger.error( 'Error on init: ' + error.message );
		//alert( 'Error on init: ' + error.message );

	} finally {

		// this section runs "no matter what"
		var js = require( '/application/library/JavascriptExtensions' );

		// This codes runs after the phone has been suspended and is waking back up.
		// Its important to check for notifications and keep user data from getting stale.
		Ti.App.addEventListener( 'resume', function() {
			$.info( 'Resume fired');
			if ( ! $.userLoggedIn() ) {
				$.info( 'Init: User is not logged in ' + JSON.stringify( $.user ) );
				return;
			}

			// this interval is in SECONDS!
			// TODO: this will not work if user starts app logged out, then logs in.
			var interval = ( $.user.userType == 'Customer' ) ? 7200  : 3600 ;
			if ( js.unixTime() > parseInt( $.lastUpdate +  interval, 10 )  ) {
				$.info( 'Resuming from background');
				try {
					// check for user related updates via Drupal.
					var Update = require( '/application/BackgroundUpdate' );
					new Update();
				} catch (error) {
					$.info( 'Error: ' + error.message );
				}
			}
		});

		$.startDate = new Date().toLocaleString();
		$.startTime = js.unixTime(); // unix	timestamp
		// windows array will hold all open windows EXCEPT the home screen.
		$.windows = [];
		$.num = 0;
		$.info( 'Init.js has completed' );

		Logger = require( '/application/Logging' );
		logger = new Logger();
		logger.activity( 'Start App' );
	}

})();

