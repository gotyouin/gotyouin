/**
* @file Application.js
*
* Global settings and defaults are defined here for Got You in. This
* object will be exported typically as '$', and will hold all references
* that need to be in the global app namespace. Any element of $.* will be
* accessible any time this file has been 'required' and will have a lifetime of
* the current session.
*
* NOTE: global namespacing is considered to be a bad practice if overused, and
* should not be used just for convenience alone, but where its necessary.
*
* DBS 2013-01-10
*/

app = {
	'live' : false,			// see conditionals below this section for live vs staging toggles
	'debug' : true,
	'siteURL' : 'http://staging20.resultsbydesign.com', // for webviews and data, see conditionals below for live
	'info' : function( str ) { return Ti.API.info( 'DBS ' + new Date().toLocaleString() + ': ' + str ); },
	'title' : 'Got You In',
	'lastUpdate' : 0,		// last time we background synced on a resume event with Drupal for notifications, etc
	'lastDrupalUpdate' : 0,	//  last time we did any sync with Drupal ... this will happen on ANY account / login related page
	'isIphone' : ( 'iphone' === Ti.Platform.osname ),
	'isAndroid' : ( 'android' === Ti.Platform.osname ),
	'defBackgroundColor' : '#1b1b1b',
	'defFontColor' : '#ffffff',
//	'defAccentColor' : '#29347f',
	'defAccentColor' : '#fff',
	'defAccentColorb' : '#19204e',
	'defAccentColor2' : '#e71c2c',
	'defAccentColor2b' : '#8d111b',
	'defFontSize' : '18dp',
	'defWindowAnimation' : ( 'iphone' === Ti.Platform.osname ) ? {transition: Titanium.UI.iPhone.AnimationStyle.FLIP_FROM_LEFT } : null,  // only works for iphone
	'displayHeight' :  Ti.Platform.displayCaps.platformHeight,
	'displayWidth' :  Ti.Platform.displayCaps.platformWidth,
	'isTablet' :  Ti.Platform.osname === 'ipad' || (Ti.Platform.osname === 'android' && (this.width > 899 || this.height > 899)),
	'geoCoords' : [],			// array long , latitude, checked in Init.js
	'notifications' : [],		// the notifications array of json objects
	'newNotifications' : false,	// whether we have received a new notification or not
	'slidingMenu' : null,		// the main slide out menu, this is created once and is a globally available window.
	'thisView' : null,			// current dataURL for the current view as $string, name of view, eg 'login'
	'currentWindow' : null,		// current window $obj
	'drupalSyncWindow': null,	// window used to sync user data between app and Drupal
	'windowTitle' : 'Got You In', // for window title bar
	'windows' : null,			// array of open windows, exclusive of the main / home screen window.
	'registerID' : 0,			// registration id from `gotyouin_users` table (NOT the drupal user id, eg /user/19/edit.
	'hasAccount': false,		// this is used to for users that have been logged out, to know if they need to create an account or just login. FIXME: use Ti.App.Properties for this
	'startDate' : null,			// date the app was started (NOT installation date).
	'startTime' : null,			// same, but as unix timestamp
	'userNeedsUpdating' : false,	// if something happened (eg a login), where ui / menus need to be updated.
	'homeNeedsUpdating' : false,	// if something happens to cause the home screen to be recreated, do it if set to true, eg after a login
	'user' : null				// basic Drupal user profile info, eg first name, phone number, userType, etc, etc
};

// conditionals for live vs not live
if ( true === app.live ) {
	app.debug = false;
	// new domain for mobile exclusive usage 2013-02-16:
	app.siteURL =  'http://gotyouin.dbsclients.com';
	app.info  = function() { return false; };
}

/**
* @return string userType of the current user.
*/
app.userType = function() {
	if ( null === app.user || undefined === app.user || undefined === app.user.userType || '' === app.user || 0 === app.user.userID  ) {
		app.info( 'No user, running anonymously' );
		return 'Anonymous';
	} else  {
		app.info( app.user.userType );
		return app.user.userType;
	}
};

/**
* @return bool true if current user is logged in
*/
app.userLoggedIn = function() {
	return app.user !== null && app.user.userType !== undefined && app.user.userType !== null && app.user.userType !== 'Anonymous';
};

// FIXME: should be in our library code somewhere
/**
 * @param {Object} expr, see if the variable is set or not
 */
app.isset = function( expr ) {
	return typeof( expr ) != "undefined" && expr !== null && expr !== "undefined";
};


/**
 * @return void
 *
 * Close all open windows except home screen, and reset our windows array.
 */
app.closeAllWindows = function() {
	var num = 0, win, i;
	app.info( 'Closing All Windows, ' + app.windows.length + ' total' );
	if ( app.windows.length === 0 ) {
		return;
	}
	for ( i in app.windows ) {
		num++;
		win = app.windows[i];
		if ( app.isset( win ) && typeof( win == 'object') ) {
			if ( app.isset( win.thisView ) ) {
				app.info( 'Closing: ' + win.thisView );
			}
			win.close();
			app.info( 'Closing Window: ' + num );
		}
	}
	// reset array
	app.windows = [];
	app.info( 'We are done closing all windows' );
};

/**
 *  @return void
 *
 *  2013-02-20 Reset user to anonymous, eg on Log out
 */
app.resetUser = function() {
	app.user = {};
	app.user.userType = 'Anonymous';
}

app.info( 'Launching ' + app.title );
app.info( ( app.isIphone ) ? ' Running on iPhone' : ' Running on Android' );
app.info( 'Using data store: ' + app.siteURL );

module.exports = app;
