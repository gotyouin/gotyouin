/**
* @file BackgroundUpdate.js
*
* Performs a update of user related data, including notifications, on 'resume'
* events ONLY (when phone wakes up). Note: setInterval() does not fire on iphone when
* paused (Android does), so we can't use this for periodic updates. See
* /gotyouin_php/gotyouin_user.php which is where we are connecting to here.
*
* @author Hal Burgiss 2013-02-10
*/

//Constructor
function BackgroundUpdate() {
	"use strict";

	// DBS application settings and configuration variables
	var $ = require( '/application/Application' );

	$.info('running background update module');

	var initWindow = Ti.UI.createWindow({
		zIndex: -1
	});

/*
   2013-02-10, cause wierd ios error when device resumes

	if ( $.isIphone ) {
		Ti.App.addEventListener( 'pause', function() {
			$.info( 'Closing initW1')
			try {
				if ( $.isset( initWindow ) ) {
					initWindow.close();
					initWindow = null;
				}
			} catch( error ) {
				$.info( 'Error closing initWindow in background' );
			}
		});
	}
*/
	initWindow.hide();
	initWindow.open();
	var DrupalUser = require( '/application/GetDrupalUser' );
	new DrupalUser( initWindow );

	$.userNeedsUpdating = true;  // well ... it depends what happened here, but safest to do an update.


	try {
		setTimeout( function() {
			$.info( 'Closing initW2');
			if ( $.isset( initWindow ) ) {
				initWindow.close();
				initWindow = null;
			}

			$.info( 'Closing initWindow3 for Drupal user code');
		}, 4500 );
	} catch (err) {
		$.info( 'Error closing initWindow in Background update: ' + err.message );
	}


	return this;
}

module.exports = BackgroundUpdate;
