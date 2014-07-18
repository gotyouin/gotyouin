/**
 * @file GetDrupalUser.js
 *
 * Handles the local storage of Drupal user related data, that was pulled from
 * the Drupal site when a user registers (via webView), or other account type
 * activites. This is used either for both new user registrations, and updated
 * profile, eg via login. This code is run in a webview (hidden), and is opened to
 * '/gotyouin_php/gotyouin_user_status.php', where the heavy lifting is done on
 * the Drupal end. That php script will do all the voodoo to check the current
 * browser session for a logged in user and grab the account details, including
 * notifications, which are then read back in by the mobile app.
 *
 * @author Hal Burgiss 2013-01-22
 */

var isRunning = false;

/**
 * @param {Object} View, can be a Window object or View object, required
 */
var DrupalUser = function( View ) {
	"use strict";

	var $ = require('/application/Application'), drupal = {};
	$.info('Running drupal user setup / update code');
	//$.info('Exiting drupal user');
	//return;
	//if ( $.init === true ) return;
	//$.init = true;

	//return;

	// checking to see if we have multiple instances running.
	$.info('Drupal User Running: ' + isRunning );
	isRunning = true;

	var js = require( '/application/library/JavascriptExtensions' );

	// We will update on *every* webview load if we are in the login/account area.
	// Try to minimize that.
	if ( $.userLoggedIn() ) {
		// have 60 seconds of not needing to update. TODO: what is proper interval? 300?
		$.info( 'Last D update logged in: ' + $.lastDrupalUpdate );
		if ( js.unixTime() < $.lastDrupalUpdate + 15 ) {
			$.lastDrupalUpdate = js.unixTime();
			$.info( 'We dont need a Drupal update yet' );
			return;
		}
	} else {
		$.info( 'Last D update not logged in: ' + $.lastDrupalUpdate );
		if ( js.unixTime() < $.lastDrupalUpdate + 5 ) {
			$.lastDrupalUpdate = js.unixTime();
			$.info( 'We dont need a Drupal update yet' );
			return;
		}
	}

	// check network status first
	if ( Titanium.Network.networkType === Titanium.Network.NETWORK_NONE ) {

		// Log error to database
		var Logger = require( '/application/Logging' );
		var logger = new Logger();
		logger.error( 'Error Getting Drupal user: Network was down ' );
		$.info( 'Network is down');

		return;
	}

	try {
		// YANOTHER hidden webview to extract user data from Drupal to see if
		// we have a logged in user, and a valid account. Special hidden webview.

		var WebView = require( '/ui/common/WebViewHidden' );
		$.info( 'Drupal sync setting webview' );
		var webView = new WebView( '/gotyouin_php/gotyouin_user_status.php?UUID=' + Ti.Platform.id, 'hidden' );

		View.add( webView );
		webView.hide();
		webView.height = 0;
		$.info( 'Webview should be ready' );

		// ie we have a valid user in the system already
		//var existingUser = ( $.user !== null && Object.keys( $.user ) > 1 && ! isNaN ( $.user.userID ) );
		var existingUser = ( $.user !== null && ! isNaN($.user.userID) ), dataLength;

		// user based updates via Drupal.
		$.lastDrupalUpdate = $.lastUpdate = js.unixTime();


		// must handle via onload event
		webView.addEventListener( "load", function(e) {

			$.info( 'Drupal webview load event fired' );
			var session, sessionName, _user, loggedin, notifications;

			// check for valid looking return data for a drupal user and session
			if ( $.isset( webView ) && webView.evalJS("sessVal") && typeof webView.evalJS("sessVal") == "string" && webView.evalJS("sessVal").length > 1 ) {
				$.info('SessVal: ' + webView.evalJS("sessVal"));
				session = webView.evalJS("sessVal").trim();
				sessionName = webView.evalJS("sessName").trim();
				loggedin = webView.evalJS("loggedin").trim();
				_user = JSON.parse(webView.evalJS("user"));

				// These are newly dl'd notifications, and include only the message.
				// We will add an expiration date on our end and store locally as json.
				notifications = JSON.parse( webView.evalJS("notifications") );
				if ( notifications.length > 0 ) {
					$.newNotification = true;
				}
				$.info( "New notifications dl'd now: " + notifications.length );
				// do not confuse with local $.user, not yet anyway
				$.info( 'Getting drupal user data ...');

			} else {
				$.info('Failed to find logged in user');
				// We have to be careful of saved user values, but the
				// login has expired here. Session values are nulled out in
				// app.js in case this does happen. Name, etc is still preserved
				// from localStorage. The way to know is we have name, etc. but user is Anon.
				if ( ! $.isset( $.user ) ) {
					$.user = {};
				}
				$.user.userType = 'Anonymous';
				$.user.hasAccount = false;		// default
				if ( null !== Ti.App.Properties.getString('hasAccount') && Ti.App.Properties.getString( 'hasAccount' ) !== undefined ) {
					if ( Ti.App.Properties.getString( 'hasAccount') == 'true' || Ti.App.Properties.getString('hasAccount') === true ) {
						$.info( 'hasAccount == true' );
						$.user.hasAccount = true;
					}
				}
				$.homeNeedsUpdating = true;
				View.remove( webView );
				webView = null;
				// new 2013-02-28 TODO: make on static, persistant window for drupal sync, ie $.drupalSyncWindow'
				// this will break if we have a view instead if of a window.
				//alert( typeof View.remove === 'function' );
				//View.close();
				//View = null;

				$.info('Exiting Drupal User init');
				isRunning = false;
				return;
			}

			// Create the app $.user data from the Drupal user data, only if successful
			drupal.userID		= _user.uid;
			drupal.userType	= _user.user_type;
			drupal.firstName	= _user.firstName;
			drupal.lastName	= _user.lastName;
			drupal.phone		= _user.phone;
			drupal.session		= session;
			// session cookie value
			drupal.sessionName	= sessionName; // session cookie name (can be used to reconstruct session cookie, which is http-only BTW)
			drupal.lastUpdate	= new Date();
			drupal.hasAccount	= true;

			var js = require( '/application/library/JavascriptExtensions' ), i, expires;
			expires = js.dateToYMD().replace( /-/g, ''); // eg 20130209, today's date.
			$.info( 'Date Expires: ' + expires );

			// Add any new notifications to whatever might be existing notifications.
			// Notifications come out of the db as newest ones first in the array.
			// notifications are put into arrays with elements defined like :
			// { 'text' : 'message goes here', 'expires' : 20130201 }
			for ( i=0; i< notifications.length; i++ ) {
				$.info( 'Adding notification' );
				notifications[i] = { 'text': notifications[i], 'expires' : expires };
			}

			if ( notifications.length > 0 ) {

				// combine array, old with the new, with new at the top of the array, newest at index[0]
				$.info( 'Combining notifications');
				$.notifications = notifications.concat( $.notifications );
			}

			// don't let it get too long
			var maxLength = ( drupal.userType == 'Customer' ) ? 10 : 36;
			if ( $.notifications.length > maxLength ) {
				$.notifications.length = maxLength;
			}

			$.info('Total Notifications: ' + $.notifications.length );
			// save to local storage
			Ti.App.Properties.setList( 'notifications', $.notifications ) ;

			if ( 'Owner' === drupal.userType ) {
				drupal.shopID = _user.shopID;
			}

			// check it all
			$.info( JSON.stringify( drupal ) );

			// create, or update, the drupal user data to local session
			$.user = drupal;

			// track whether user has an account or not. Once set to true, it is never set to false again.
			Ti.App.Properties.setString( 'hasAccount', 'true' );

			$.info( 'Comparing ... id: ' + $.user.userID + ' type: ' + $.user.userType );

			// save user data to persistant storage
			Ti.App.Properties.setObject( 'user', $.user );

			// update menu since if user changes, quite possibly the navigation needs to be updated.
			$.userNeedsUpdating = true;
			$.slidingMenu.reset();


			// sanity check
			var __user = Ti.App.Properties.getObject( 'user' );
			$.info( typeof __user );
			if ( $.isset( __user )) {
				$.info( 'Comparing storage ... id: ' + __user.userID + ' type: ' + __user.userType );
			}

			// bingo, user should be added.
			$.info( "Added/updated User: " + $.user.firstName + ' ' + $.user.lastName + ' ID: ' + $.user.userID + ' Type: ' + $.user.userType );

			var Logger = require( '/application/Logging' );
			var logger = new Logger();
			logger.activity( 'Adding / Updating Drupal user' );
		});

		// load webview, required even for hidden webviews
		//View.add( webView );
		//webView.hide();
		//webView.height = 0;


		setTimeout(function() {
			//		TODO: 2013-01-31
			if ( $.isset( View ) && $.isset( webView )) {
				View.remove( webView );
				webView = null;
			}
		}, 3000);

	} catch( error ) {

		$.info( 'ERROR: ' + error.message );
		// tell user?
		alert( 'Sorry, but an error has occured ... ' + error.message );

		// Log error to database
		var Logger = require( '/application/Logging' );
		var logger = new Logger();
		logger.error( 'Error adding Drupal user: ' + error.message );
	} finally {
		$.info( 'Finally: on GetDrupalUser' );
		isRunning = false;
	}
};

module.exports = DrupalUser;
