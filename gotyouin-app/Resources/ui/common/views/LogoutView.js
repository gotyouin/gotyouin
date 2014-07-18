/**
* @file LogoutView.js
*
* Logs user out. We don't bother checking if user is logged in or not, we just
* do it.
*
* @author Hal Burgiss  2013-01-16
*/

//Constructor
function LogoutView() {
	"use strict";

	// DBS application configuration variables
	var $ = require( '/application/Application' );

	var thisWindow = $.currentWindow;

	$.info('logout view module');

	// MainView is the same for all screens. This is the top nav bar and main view //
	var MainView = require( '/ui/common/views/MainView' );
	var self = new MainView();

	var actInd = Ti.UI.createActivityIndicator({
		width: Ti.UI.SIZE,
		height: Ti.UI.SIZE,
		message: ' logging out ... ',
		font: { fontSize : '24dp' },
		top: '10%',
		color: $.defAccentColor,
		IndicatorColor: $.defAccentColor
	});
	actInd.show();
	self.add( actInd );

	var Echo = require( '/ui/common/Echo' );
	var Logger = require( '/application/Logging' );
	var logger = new Logger();

	try {

		// This webview is "hidden", and we force an attempted logout without
		// checking if the user is logged in or not. Who cares?
		var WebView = require( '/ui/common/WebView' );
		//var webView =  new WebView( '/user/logout' );
		var webView =  new WebView( '/gotyouin_php/gotyouin_logout.php' );

		self.add( webView );
		webView.hide();
		webView.height = 0;
		logger.activity( 'Log out' );

		setTimeout( function() {
			$.info( 'Logging out ... closing window');
			try {
				if ( $.isset( thisWindow ) ) {
					// FIXME: try to auto close this window.
					$.info( 'Closing logout window');
					if ( $.isset( webView ) ) {
						//self.remove( webView ); webView = null;
					}
					//thisWindow.close( $.defWindowAnimation );
					$.closeAllWindows();
				}
			} catch (error) {
				$.info( 'Logout: ' + error.message );
			}

		}, 4500);

		webView.addEventListener( "load", function(e) {
			$.info( 'Log out page has loaded');
			if ( actInd !== null ) {
				self.remove( actInd ); actInd = null;
				if ( $.isset( webView ) ) {
					//self.remove( webView ); webView = null;
				}
			}

			// TODO: Just assuming the logout works here. FIXME
			self.add( new Echo( 'Got You In' ) );
			self.add( new Echo( 'You have been logged out!' ) );
			//
			Ti.App.Properties.removeProperty( 'user' );
			//$.user = null;
			$.user = {};
			$.user.userType = 'Anonymous';
			$.user.hasAccount = false;		// default
			if ( null !== Ti.App.Properties.getString('hasAccount') && Ti.App.Properties.getString( 'hasAccount' ) !== undefined ) {
				if ( Ti.App.Properties.getString( 'hasAccount') == 'true' || Ti.App.Properties.getString('hasAccount') === true ) {
					//$.info( 'hasAccount == true' );
					$.user.hasAccount = true;
				}
			}

			Ti.App.fireEvent( 'updateButtonLabel', { data : true } );
			$.slidingMenu.reset();

		});

	} catch( error ) {

		// error handling.
		self.add( new Echo( 'There was a problem, please try again.' ) );
		logger.error( 'Error logging out: ' + error.message );

	} finally {
		$.userNeedsUpdating = $.homeNeedsUpdating = true;
		Ti.App.fireEvent( 'updateButtonLabel', { data : true } );
	}

	return self;
}

module.exports = LogoutView;
