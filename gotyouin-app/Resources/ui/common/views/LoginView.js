/**
* @file LoginView.js
*
* Login webview, called from MainView. Also used for 'Account' screens.
*
* @author Hal Burgiss  2013-01-16
*/

//Constructor
function LoginView() {
	"use strict";

	// DBS application settings and configuration variables
	var $ = require( '/application/Application' );

	$.info('login view module');

	var WebView = require( '/ui/common/WebView' );
	var webView =  new WebView( '/user/login?app=1' );

	// MainView is the same for all screens. This is the top nav bar and main view //
	var MainView = require( '/ui/common/views/MainView' );
	var self;
	if ( $.userLoggedIn() ) {
		// really need these states to be the same ... window will close on back button click or not?
		// Right now its 'not', we go back one screen
		self = new MainView( webView );
	} else {
		self = new MainView( webView );
	}

	var actInd = Titanium.UI.createActivityIndicator({
		width: Titanium.UI.SIZE,
		height: Titanium.UI.SIZE,
		message: ' wait ... ',
		font: { fontSize : '24dp' },
		top: '10%',
		color: $.defAccentColor,
		IndicatorColor: $.defAccentColor
	});
	actInd.show();
	self.add( actInd );

	self.add( webView );

	webView.addEventListener( "load", function(e) {
		if ( actInd !== null ) {
			self.remove( actInd ); actInd = null;
		}

		// Update user data from Drupal, see if we are logged in, etc.
		/*
		There is some kind of bizarre iphone only error being triggered by this code here
		that causes any attempt to leave the account page, to lock up the device.
		Probably sensitive to how we are attaching views to views for this purpose ????
		Seems fixed 2013-02-08, probably glitchy behavior for where/how we were attaching
		the webview. We use our own window now. Also, the behavior changed after THE FIRST
		TIME the device went into suspend mode. jeez.
		 */
			$.info( 'Login webview loaded');
			var loginWIndow = Ti.UI.createWindow({
				zIndex: -1,
				exitOnClose: false
			});

			loginWIndow.hide();
			loginWIndow.open();
			var DrupalUser = require( '/application/GetDrupalUser' );

			// FIXME / TODO not finished 20013-03-01
			new DrupalUser( $.drupalSyncWindow );
			// make the login button say account
			Ti.App.fireEvent( 'updateButtonLabel', { data : true } );

			$.info('Login says we need to update');
			$.userNeedsUpdating = true;  // well ... it depends what happened here, but safest to do an update.
/*
 * test

			try {
				setTimeout( function() {
					$.info( 'Closing Login window');
					loginWIndow.close();
					// without this extra delay, Android 4 crashed here ... wtf
					setTimeout ( function() {
						loginWIndow = null;
					}, 1000);
					$.info( 'Closing loginWIndow2 for Drupal user code loginview');
				}, 4500 );
			} catch (err) {
				$.info( 'Error closing loginWIndow in Account: ' + err.message );
			}
*/

	});
	return self;
}

module.exports = LoginView;
