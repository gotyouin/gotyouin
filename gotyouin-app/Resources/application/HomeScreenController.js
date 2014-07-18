/**
* @file HomeScreenController.js
*
* This controller handles the placing of the appropriate content for different
* user types on the home screen, eg Barber vs Owner. And can handle changes in
* userType, eg logged out vs logged in.
*
* @author Hal Burgiss  2013-02-01
*/

function HomeScreenController( mainContent, homeView ) {

	// Global settings and variables
	$ = require( '/application/Application' );

	// calulate dimensions
	var logoSizeW, logoSizeH;
	if($.displayWidth * 0.8 < 447) {
		logoSizeW = $.displayWidth * 0.8;
		logoSizeH = $.displayWidth * 0.8 * 234/447;
	}else{
		logoSizeH = 234;
		logoSizeW = 447;
	}

	var SignUpW, SignUpH;
	if($.displayWidth * 0.9 < 472){
		SignUpW = $.displayWidth * 0.9;
		SignUpH = $.displayWidth * 0.9 * 141/474;
	}else{
		SignUpH = 141;
		SignUpW = 472;
	}

	$.info( 'HomeView Logged in: ' + $.userLoggedIn() );
	$.info('Homeview usertype: ' + JSON.stringify( $.user ) );
	/**
	* @return void, does the working of recreating the home screen view as needed, typically when $.homeNeedsUpating is set to true.
	*/
	this.reset = function() {

		$.slidingMenu.reset();
		$.info('Starting homecontroller reset');
		Ti.App.fireEvent( 'updateButtonLabel', { data : true } );

		// subview to hold the main content area for the home screen
		if ( homeView !== null ) {
				//homeView.remove( logo );
				if ( $.isset( mainContent ) ) {
					if ( $.isset( mainContent.logo ) ) {
						mainContent.remove( mainContent.logo );
						mainContent.logo = null;
					}
					if ( $.isset( mainContent.notificationView ) ) {
						mainContent.remove( mainContent.notification );
						mainContent.remove( mainContent.notificationView );
						mainContent.notification = null;
						mainContent.notificationView = null;
					}
					mainContent.remove( homeView );
				}
			logo = homeView = Notification = NotificationView = null;
			$.info( 'Removing homeview');
		}

		homeView = Ti.UI.createView({
			backgroundImage: '/images/GYI-Pattern-dark.png',
			//backgroundRepeat: true,
			width:'100%',
			height: Ti.UI.SIZE,
			layout:'vertical',
			top: 0
		});


		// Handle userType specific content, eg for a Barber
		var SearchField, searchField, logo;
		if ( ! $.userLoggedIn() /* || $.user.userType === 'Anonymous' */ ) {

			// Logo
			logo = Ti.UI.createImageView({
				image : '/images/GYI-logo.png',
				height: logoSizeH,
				width : logoSizeW,
				backgroundColor: 'transparent',
				top: 20
			});
			mainContent.logo = logo;
			mainContent.add( logo );

			// create account callout
			var AccountButton = require( '/ui/common/views/partials/AccountButtonContainer' );
			var accountButtonContainer = new AccountButton( homeView, logoSizeW, logoSizeH, SignUpH, SignUpW );

			// search for barber
			SearchField = require( '/ui/common/views/partials/BarberSearchField' );
			searchField = new SearchField( homeView, logoSizeH, SignUpH );


			$.info("Loading Anonymous Home Screen");

		} else {

			$.info( 'Home for: ' + $.user.userType );

			// Barbers and Owners and Logged In Customers
			if (  $.user.userType === 'Customer' ) {

				// set up notifications: Phase I is Customer user type only
				//TODO: 2013-02-04, rethink this, should we have a notifications page?
				// subview to hold the notifications area for the home screen
				var NotificationView = Ti.UI.createView({
					backgroundColor: $.defBackgroundColor,
					width:'90%',
					bottom: '8dp',
					borderRadius: 4,
					BorderWidth: '1dp',
					BorderColor: '#333',
					layout:'vertical',
					textAlign: 'center',
					top: '5dp',
					height: 0		// NOTE: hidden by default, only show if there is a notification
				});
				var Notification = Ti.UI.createLabel({
					color: $.defFontColor,
					text: 'text',
					font: { fontSize: '15dp' },
					textAlign: 'left',  // text align
					left: '10dp',
					height: Titanium.UI.FILL // fill parent container
				});
				NotificationView.add( Notification );
				mainContent.add( NotificationView );
				mainContent.notificationView = NotificationView;
				mainContent.notification = Notification;
				NotificationView.hide();


				// Logo
				logo = Ti.UI.createImageView({
					image : '/images/GYI-logo.png',
					height: logoSizeH,
					width : logoSizeW,
					top: 10
				});
				mainContent.logo = logo;
				mainContent.add( logo );

				$.info( 'Loading Customer Type. Has notifications: ' + $.notifications.length );

				setTimeout( function() {

					// Get the home screen notification text. Use a delay to allow for network stuff.
					// Phase I this is for logged in Customer only.
					if ( $.notifications.length > 0 ) {
						// Check the expiration date on the notification. We are
						// showing notifications for one day only (the day they are
						// pulled from the DB).
						$.info( 'Customer has notifications, expires ' + $.notifications[0].expires);
						var js = require( '/application/library/JavascriptExtensions' ), today;
						today = js.dateToYMD().replace( /-/g, ''); // eg 20130209, today's date.
						if ( $.notifications[0].expires.toString() === today.toString() ) {
							$.info( 'Showing notification!' );
							Notification.text =  $.notifications[0].text;
							NotificationView.show();
							NotificationView.height = '25dp';
							$.info( 'Notification for today: ' + Notification.text );
							if ( $.newNotifications === true ) {
								$.newNotifications = false;
								Ti.Media.vibrate();
							}

						}
					}
				}, 3500 );

				SearchField = require( '/ui/common/views/partials/BarberSearchField' );
				searchField = new SearchField( homeView, logoSizeH, SignUpH );
				$.info("Loading Customer Home Screen");

			} else if ( $.user.userType === 'Barber' || $.user.userType === 'Independent Barber' || $.user.userType === 'Regular Barber' ) {
				var BarberHomeScreen = require( '/ui/common/views/partials/BarberHomeScreen' );
				new BarberHomeScreen( homeView );
				$.info("Loading Barber Home Screen");

			} else if ( $.user.userType === 'Owner' ) {
				var OwnerHomeScreen = require( '/ui/common/views/partials/OwnerHomeScreen' );
				new OwnerHomeScreen( homeView );
				$.info("Loading Owner Home Screen");
				/*
				if ( $.isset( $.homeWin ) ) {
					$.info( 'Opening homeWin' );
					$.info( 'HomeWin view:' + $.homeWin.thisView );
					$.homeWin.open();
				}
				*/
			}
		}


		// make it happen
		mainContent.add( homeView );
		$.userNeedsUpdating = $.homeNeedsUpdating = false;
		$.info( 'Home controller is reset' );
	};

	// Constructor needs to pull the trigger here to initialze.
	this.reset();

	return this;

}

module.exports = HomeScreenController;
