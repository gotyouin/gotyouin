/**
* @file app.js
*
* The main script to bootstrap/launch Got You In, and open home screen. 2013-01-09
*
* Other stuff:
*
*	- Commonly used app specific UI elements are in ui/common
*	- "Views" (ie content) are in ui/common/views (and some in partials subfolder)
*	- Backend / library type functionality is in /application
*	- Global variables are defined in /application/Application.js
*
* @dbsinterctive 2013-01-09
*/

/// Got You In! is a single context application with mutliple windows in a stack using
/// commonJS modules
( function() {

	// DBS application-wide configuration variables and settings for Got You In
	var $ = require( '/application/Application' );

	// Pull in saved user data from localStorage. Init.js will check if this is still valid.
	if ( null !== Ti.App.Properties.getObject( 'user' ) ) {
		if ( Ti.App.Properties.getObject( 'user' ) !== undefined ) {
			$.user =  Ti.App.Properties.getObject( 'user' );
			$.info( 'Getting saved user data: ' + $.user.userID );
			if ( $.isset( $.user ) && $.isset( $.user.session ) ) {
				$.user.session = null;
				$.user.sessionName = null;
			}
		}
	}

	// create, then hide, the sliding menu
	var SlidingMenu = require( '/ui/common/SlidingMenu' );
	$.slidingMenu = new SlidingMenu();

	// Open the root window. This window is always open.
	var Window = require( '/ui/common/NewWindow' );
	new Window( 'home' ).open();

	// initialization stuff
	require( '/application/Init' );

	// 2013-01-21, this is necessary for some odd freaking reason for Android emulator.
	$.slidingMenu.open();
	$.slidingMenu.close();

})();
