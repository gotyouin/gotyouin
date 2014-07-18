/**
* @file NewWindow.js
*
* Default window creation module for Got You In. Creates the window for each
* screen and related functionalty.
*
* NOTE: Much of the window and view creation seems to happen in parallel. So
* timing *can* be an issue. FYI.
*
* @author Hal Burgiss  2013-01-16
*/

/**

Each Titanium screen is a "window". Some screens are native, but many are webviews.

Window creation workflow:

	- Create new Window object
	- Add the Controller logic to the Window object (AppController.js)
	- Controller parses the dataURL argument, and starts to add the correct View for content
	- Typically these Views will load MainView.js first to start the shared items of the content window
		stuff (topNavBar, etc), then each Window will add its own unique content to MainView
		is added.

*/

// DBS - Got You In application configuration variables and settings
var $ = require( '/application/Application' );

/**
 * @param string dataURL, the route for this action, required
 * @param string dataOther, optional data for this action
 */
function NewWindow( dataURL, dataOther ) {
	"use strict";

	var self;

	// note this is defined in 2 places due to suspected timing issues.
	$.thisView = dataURL;

	$.slidingMenu.done();

	// the app controller will decide which windows and views to open
	var Controller = require('/application/AppController');
	var controller = new Controller( dataURL, dataOther );

	// iPhone typically makes use of the platform-specific navigation controller .... not used for Got You In 2013-01-25 !!1
	if ( false ) {

		// not in play right now ... using the android version as of 2013-01-25
		// $.info( 'Launching iPhone' );

		// iphone: create Window object instance
		self = Ti.UI.createWindow({
			backgroundColor: $.defBackgroundColor,
			color: $.defFontColor
		});

		//create master window  container that will hold the main view(s)
		var masterContainerWindow = Ti.UI.createWindow({
			title: $.title
		});
		masterContainerWindow.add( controller );

		//create iOS specific NavGroup UI
		var navGroup = Ti.UI.iPhone.createNavigationGroup({
			window:masterContainerWindow
		});
		self.add( navGroup );
		self.addEventListener( 'close', function() {
			self = null;
			navGroup = null;
			masterContainerWindow.close();
			masterContainerWindow = null;
		});

	} else {

		// Window now used for Android and iPhone
		$.info( 'Default Window, this view will be: ' + $.thisView );

		// This NEEDS to be here !! 2012-02-02
		$.slidingMenu.done();

		//create default instance of window object
		self = Ti.UI.createWindow({
			title: $.title,
			exitOnClose: ( 'home' === $.thisView ) ? true : false,
			//modal: false,
			navBarHidden:true,
			//modal:true,
			backgroundColor: $.defBackgroundColor,
			orientationModes : [Titanium.UI.PORTRAIT, Titanium.UI.UPSIDE_PORTRAIT],
			zIndex: 0
		});

		// save view / controller name.
		self.thisView = $.thisView;

		// save reference to this window in global space
		$.currentWindow = self;

		if ( $.isAndroid ) {

			// Android 2.3 has issues with closing keyboard on home screen.
			// Have to handle that differently.
			if ( parseInt( Ti.Platform.version, 10  ) < 4 ) {
				$.info( 'Android less than 4.0' );
				self.exitOnClose = false;
			}
		}

		// add routing logic
		self.add( controller );

		if ( 'home' !== $.thisView ) {
			self.addEventListener( 'close', function() {
				$.info( 'Window close event firing');
				if ( self !== null ) {
					// remove all child views
					removeChildren( self );
					$.info( 'Removing window children' );
				}
				self = null;
				$.info('Window closed');
			} );
		} else {

			// debug
			//$.homeWin = self;
			// never want to close the home window!
			//self.addEventListener( 'close', function() { self = null; } );
		}

		/*		this code breaks clicks on webViews!!! Totally! 2013-02-05
		self.addEventListener( 'click', function() {
			//$.slidingMenu.done();
			return true;
		});
		*/
		/*
		if ( 'choose_account' == $.thisView ) {
			self.addEventListener( 'focus', function() {
				$.info( 'Wanting to close choose account window')
				if ( $.userLoggedIn() ) {
					$.info( 'Removing Choose Acct screen' );
					self.close();
				}
				self = null;
			} );
		}
		*/
	}

	if ( $.windows ) {

		// array will hold all open windows (at least that's the idea)
		$.windows.push( self );
		$.info( 'Windows length now: ' + $.windows.length );
	}

	if (  $.isAndroid ) {
		$.info( 'Setting up android back button' );

		// close window on back button navigation for Android
		$.androidBackDefault = function() {

			$.slidingMenu.done();

			if ( self.thisView == 'home' && parseInt( Ti.Platform.version, 10 ) < 4 ) {

				// Special sauce for Android 2.3, that will exit the application if
				// the keyboard is open on home screen, and back button is pressed. Yow.
				$.info( 'Android home screen staying open');
				self.open();
				return;
			}

			if ( typeof controller == 'object' ) {
				self.remove( controller );
				controller = null;
				$.info( 'Removing controller main view 2' );
			}

			if ( $.windows ) {

				// keep our array in step with open windows.
				$.windows.pop();
			}
			$.info( 'Removing window from array on back button click' );
			self.close();
//			self = null; // nulled out on close event

		};

		// back button stuff.
		self.addEventListener( 'android:back', $.androidBackDefault );
	}

	if ( $.debug && 'home' === dataURL ) {
		self.addEventListener( 'focus', function() {
			/*
			if ( $.homeNeedsUpdating === true || $.userNeedsUpdating ===  true) {
				$.slidingMenu.reset();
				// TODO: need maincontent reference for this to work
				//var HomeScreen = require( '/application/HomeScreenController' );
				//var homeScreen = new HomeScreen();
				//homeScreen.reset();
			}
			*/

			// debug 2013-01-11
			$.info( 'Num non-root Windows: ' + $.windows.length );
		});
	}

	return self;
}

/**
 * helper function to recursively remove child views from a window when closed, and free memory
 */
function removeChildren( win ) {
	var i;
	$.info('Removing children from window:' + win.thisView );
	if ( ! $.isset( win.children )) return;
	for (i in win.children) {
		if ( ! $.isset( win.children[i] )) continue;
		var child = win.children[i];
		if ( $.isset( child ) ) {
			removeChildren( child );
			win.remove( child );
			$.info('Removing a child');
		}
		child = null;
	}
}

module.exports = NewWindow;
