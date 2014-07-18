/**
* @file CreateAccountsView.js
*
* After the user has selected an account type, bounce them to a Drupal webView to
* complete account registration / sign up.
*
* @author Hal Burgiss  2013-01-24
*/

//Constructor
function CreateAccount( dataURL ) {
	"use strict";

	// DBS application configuration variables
	var $ = require( '/application/Application' );

	$.info('create accounts view module');

	// MainView is the same for all screens. This is the top nav bar and main view //
	var MainView = require( '/ui/common/views/MainView' );

	var actInd = Titanium.UI.createActivityIndicator({
		width: Titanium.UI.SIZE,
		height: Titanium.UI.SIZE,
		message: ' loading ... ',
		font: { fontSize : '24dp' },
		top: '10%',
//		style: Ti.UI.iPhone.ActivityIndicatorStyle.DARK,
		color: '#ffffff',
		IndicatorColor: '#ffffff'
	});
	actInd.show();

	var WebView = require( '/ui/common/WebView' );
	var url;

	// sniff out the account type to be created, open appropriate drupal url.
	switch ( dataURL ) {
		case 'create_barber':
			url = '/barber/register?app=1';
			break;
		case 'create_independent':
			url = '/ibarber/register?app=1';
			break;
		case 'create_owner':
			url = '/sowner/register?app=1';
			break;
		default:
			url = '/customer/register?app=1';
	}

	var webView =  new WebView( url );
	var self = new MainView( webView );
	self.add( actInd );
	self.add( webView );

	webView.addEventListener( "load", function(e) {
		if ( actInd !== null ) {
			self.remove( actInd ); actInd = null;
		}

			var initWindow = Ti.UI.createWindow({
				zIndex: -1
			});

			initWindow.hide();
			initWindow.open();
			var DrupalUser = require( '/application/GetDrupalUser' );
			new DrupalUser( initWindow );
			// make the login button say account
			Ti.App.fireEvent( 'updateButtonLabel', { data : true } );

			//$.userNeedsUpdating = true;  // well ... it depends what happened here, but safest to do an update.

			try {
				setTimeout( function() {
					initWindow.close();
					initWindow = null;
					$.info( 'Closing initWindow2 for Drupal user code');
				}, 2500 );
			} catch (err) {
				$.info( 'Error closing initWindow in Account: ' + err.message );
			}

		// Force update local user profile data from Drupal
		$.userNeedsUpdating = true;
		$.homeNeedsUpdating = true;

	});

	return self;
}

module.exports = CreateAccount;
