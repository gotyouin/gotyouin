/**
* @file MainView.js
*
* The "main" view for all windows. This creates the nav bar and top View at
* the top of each screen, presumably for *all* screens. Content areas (native
* or web) are added to this main view. This class is typically invoked from all
* View screens, eg from new HomeView().
*
* @author Hal Burgiss  2013-01-15
*/

// Constructor
function MainView( webView ) {
	"use strict";

	// DBS application configuration variables
	var $ = require( '/application/Application' );

	$.info('mainview module');

	// This the main view, equivalent to html body tag.
	var self = Ti.UI.createView({
		//backgroundColor: '#ffffff',
		backgroundImage: '/images/GYI-Pattern-dark.png',
		//backgroundRepeat: true,
		width:'100%',
		//contentWidth: Ti.UI.SIZE, // 2013-02-04
		//contentHeight: Ti.UI.SIZE,
		//height: '100%',
		height: Ti.UI.FILL,
		layout:'vertical',
		top: 0
	});

	// Top nav / Toolbar. This bar holds the 2 buttons, android vs iphone versions
	var topBar;
	if ( $.isIphone ) {

		// We use a ios specific control for iphone
		// See http://docs.appcelerator.com/titanium/latest/#!/api/Titanium.UI.iOS.Toolbar
		// iOS button styles: http://docs.appcelerator.com/titanium/latest/#!/api/Titanium.UI.iPhone.SystemButton
		topBar = Ti.UI.iOS.createToolbar( {
			top:0,
			borderTop:false,
			borderBottom:true,
			width:'100%',
			height: Titanium.UI.SIZE,
			barColor: $.defBackgroundColor
		});
	} else {

		// Android uses a regular view for top nav bar -- horizontal layout! This bar holds the 2 buttons
		topBar = Ti.UI.createView({
			width:'100%',
			/*height: Titanium.UI.SIZE,*/
			height: '55dp', /* See navButton obj */
			style:Titanium.UI.iPhone.SystemButtonStyle.PLAIN,
			backgroundGradient:{type:'linear',
			colors:['#282828','#171717'],
			startPoint:{x:0,y:0},
			endPoint:{x:2,y:50},
			backFillStart:false},
			layout:'horizontal',
			top:0
		});
	}
	self.add( topBar );

	// common settings for buttons on top nav bar.
	var navButton = {
		borderRadius: 4,
		height: '45dp',  /* See topBar obj */
		width: '20%',
		top: '5dp',
		color: '#ffffff',
		borderColor: '#000000',
		borderWidth: 1,
		backgroundGradient:{type:'linear',
			colors:['#444444','#101010'],
			startPoint:{x:0,y:0},
			endPoint:{x:2,y:50},
			backFillStart:false
			}
	};

	// buttons for top nav bar, menu button first
	var buttonMenu, winTitle;
	if ( $.isAndroid ) {
		buttonMenu = Titanium.UI.createButton({
			title: 'Menu',
			top: navButton.top,
//			left: '5%',
			left: '2%',
			/*
			borderRadius: navButton.borderRadius,
			*/
			borderRadius: 5,
			borderWidth: navButton.borderWidth,
			borderColor: navButton.borderColor,
			color: navButton.color,
			width: navButton.width,
			height: navButton.height,
			backgroundGradient: navButton.backgroundGradient
		});

		// android only
		winTitle = Ti.UI.createLabel({
			text : $.windowTitle,
			/*backgroundColor: 'yellow',*/
			width: '56%',
			textAlign: 'center',
			font: { fontSize: '18dp' }
		});

	} else {
			buttonMenu = Titanium.UI.createButton({
				title: 'Menu',
				style: Ti.UI.iPhone.SystemButtonStyle.BORDERED
			});
	}

	buttonMenu.addEventListener( 'click',function(e) {
		/*
		if ( $.userNeedsUpdating === true ) {
			if ( Titanium.Network.networkType !== Titanium.Network.NETWORK_NONE ) {
				var DrupalUser = require( '/application/GetDrupalUser' );
				new DrupalUser( self );
				$.userNeedsUpdating = false;
			}
		}
		*/
		// toggle the sliding window for the menu open/closed
		$.slidingMenu.isClosed = ! $.slidingMenu.isClosed;
		if ( ! $.slidingMenu.isClosed ) {
			$.info( 'Menu clicked to open: ' );
			$.slidingMenu.left = -$.displayWidth;
			$.slidingMenu.visible = true;
			$.slidingMenu.height = 'auto';
			$.slidingMenu.zIndex = 999;
			$.slidingMenu.open();

			// now slide from left
			$.slidingMenu.animate( {right:0, left:0, duration:500} );

		} else {
			$.info( "Menu clicked to close");
			$.slidingMenu.done();
		}
	});

	// login / account button on Home screen.
	var buttonLogin;
	if ( 'home' === $.thisView ) {

		if ( $.isAndroid ) {

				// sign in button
				buttonLogin = Titanium.UI.createButton({
				title: ( $.userLoggedIn() ) ? 'Account' : 'Log In',
				top: navButton.top,
				borderRadius: 5,
				borderWidth: navButton.borderWidth,
				borderColor: navButton.borderColor,
				color: navButton.color,
				width: navButton.width,
				height: navButton.height,
				backgroundGradient: navButton.backgroundGradient
			});
		} else {
			buttonLogin = Titanium.UI.createButton({
				title: ( $.userLoggedIn() ) ? 'Account' : 'Log In',
				style: Ti.UI.iPhone.SystemButtonStyle.BORDERED
			});
		}
		buttonLogin.addEventListener( 'click',function(e) {

			// onclick, log in
			var Window = require( '/ui/common/NewWindow' );
			new Window( 'login' ).open( $.defWindowAnimation );
		});

		// add a custom event listener to update the button title.
		Ti.App.addEventListener( 'updateButtonLabel', function( data ) {
			$.info( 'Received event: ' + ( $.userLoggedIn() ) ? 'Account' : 'Log In' ) ;
			buttonLogin.title = ( $.userLoggedIn() ) ? 'Account' : 'Log In';
		});

	} else {

		// cancel button, all screens except home
		if ( $.isAndroid ) {
			buttonLogin = Titanium.UI.createButton({
				title: 'Back',
				top: navButton.top,
				borderRadius: 5,
				borderWidth: navButton.borderWidth,
				borderColor: navButton.borderColor,
				color: navButton.color,
				width: navButton.width,
				height: navButton.height,
				backgroundGradient: navButton.backgroundGradient
			});

		} else {

			buttonLogin = Titanium.UI.createButton({
				title: 'Back',
				style: Ti.UI.iPhone.SystemButtonStyle.BORDERED
			});
			//buttonLogin = Titanium.UI.createButton({
				//systemButton : Ti.UI.iPhone.SystemButton.CANCEL
			//});
		}

		// the same button is used for login on the home screen, and 'Back' on others.
		buttonLogin.addEventListener( 'click',function(e) {
			$.slidingMenu.done();

			$.info( 'Cancel button clicked: ' + $.thisView );

			var activeWindow;
			if ( $.isset( webView ) && webView.canGoBack() ) {
				webView.goBack();
				$.info( 'Cancel Webview, going back' );
			} else {
				activeWindow = $.windows.pop();
				activeWindow.close( $.defWindowAnimation );
				$.info( 'Mainview: Closing window on button click' );
			}

			// special handling of the choose account window, which does not need to there on back button.
			/* var currWindow = $.windows[ $.windows.length ];
			$.info( 'Looking for Choose Acct window' );
			if ( $.isset( currWindow) && currWindow.thisView == 'choose_account' && $.userLoggedIn() ) {
				activeWindow = $.windows.pop();
				activeWindow.close( $.defWindowAnimation );
				$.info( 'Removing choose Acct Window from array' );
			}
*/

/*
			if ( $.userNeedsUpdating === true ) {
				//var DruaplUser = require( '/application/GetDrupalUser' );
				//new DrupalUser( self );
				$.userNeedsUpdating = false;
			}
*/

		});
	}

	// add the buttons to the topnav bar
	if ( $.isIphone ) {

		// iphone
		var flexSpace = Titanium.UI.createButton({
			// purely for spacing
			systemButton: Titanium.UI.iPhone.SystemButton.FLEXIBLE_SPACE
		});
		var navTitle = Ti.UI.createLabel({
			color: '#ccc',
			text: $.windowTitle,
			font: { fontSize: '18dp' },
			left: '5%',  // text align
			height: Titanium.UI.FILL, // fill parent container
			top: '1%'
		});
		topBar.items = [ buttonMenu, flexSpace, navTitle, flexSpace, buttonLogin ];
	} else {

		// android
		topBar.add( buttonMenu );
		topBar.add( winTitle );
		topBar.add( buttonLogin );

		// handle back button
		self.addEventListener( 'android:back', function() {
			if ( $.isset( webView ) && webView.canGoBack() ) {
				webView.goBack();
				$.info( 'Cancel Webview, going back' );
			}
			return false;
		});
	}

	// 2013-01-30, what is this? Do we need it?
	$.slidingMenu.open();
	$.slidingMenu.visible = true;
	//$.slidingMenu.isClosed = true;

	$.info( 'Mainview finished loading');
	return self;
}

module.exports = MainView;
