/**
* @file AppController.js
*
* AppController.js handles controller-like logic to decide which content (ie which View) gets
* added to the Window, and then adds that content. Called from NewWindow.js.
*
* @author Hal Burgiss  2013-01-11
*/

// Constructor
function AppController( dataURL /* required */, dataOther /* optional */ ) {
	"use strict";

	// DBS Got You In application configuration / settings variables
	var $ = require( '/application/Application' ), self;

	$.thisView = dataURL;
	$.info( 'Controller loading dataURL: ' + $.thisView );
	
	// The dataURL passed here from NewWindow tells us which View(s) to load, and the Window Title.
	if ( 'home' === dataURL || dataURL === undefined || dataURL === null ) {

		// Home screen
		var HomeView = require( '/ui/common/views/HomeView' );
		
		// self == the MainView + specific content for that view
		self = new HomeView();

	} else if ( 'login' == dataURL ) {

		// login screen (webview)
		$.windowTitle = ( $.userLoggedIn() ) ? 'Account' : 'Log In';
		var LoginView = require( '/ui/common/views/LoginView' );
		self = new LoginView();

	} else if ( 'company' === dataURL ) {

		$.windowTitle = 'About ' + $.title;	
		var CompanyView = require( '/ui/common/views/CompanyView' );
		self = new CompanyView();
	
	} else if ( 'info' === dataURL ) {
		
		// probably will be removed for production ...
		$.windowTitle = 'System Info';
		var InfoView = require( '/ui/common/views/InfoView' );
		self = new InfoView();

	} else if ( 'log_out' === dataURL ) {

		$.windowTitle = 'Log Out';
		var LogoutView = require( '/ui/common/views/LogoutView' );
		self = new LogoutView();

	} else if ( 'choose_account' === dataURL ) {

		// choose account type. This native view will eventually link to webView.
		$.windowTitle = 'Choose Account Type';	
		var ChooseAccountView = require( '/ui/common/views/ChooseAccountView' );
		self = new ChooseAccountView();

	} else if ( 'create_customer' == dataURL || 'create_barber' == dataURL || 'create_independent' == dataURL || 'create_owner' == dataURL ) {

		// set the window title
		$.windowTitle = 'Register';
		switch ( dataURL ) {
			case 'create_customer':
				$.windowTitle = 'Customer Registration';
				break;

			case 'create_barber':
			case 'create_independent':
				$.windowTitle = 'Barber Registration';
				break;

			case 'create_owner':
				$.windowTitle = 'Owner Registration';
				break;

		}

		// this will create the webView for the actual registration process.
		var CreateAccountsView = require( '/ui/common/views/CreateAccountsView' );
		self = new CreateAccountsView( dataURL );

	} else if ( 'barber_search' === dataURL ) {

		// Webview for barber search results on home screen.
		$.windowTitle = 'Search Results';
		var SearchResultsView = require( '/ui/common/views/SearchResultsView' );
		self = new SearchResultsView( dataOther /* search input field value */ );
	
	} else if ( 'feedback' === dataURL ) {

		// Webview for Feedback form
		$.windowTitle = 'Feedback';
		var FeedbackView = require( '/ui/common/views/FeedbackView' );
		self = new FeedbackView();

	} else if ( 'appointments' === dataURL ) {

		// Webview for Appointments
		$.windowTitle = 'Appointments';
		var AppointmentsView = require( '/ui/common/views/AppointmentsView' );
		self = new AppointmentsView();

	} else if ( 'new_appointment' === dataURL ) {

		// Webview for creating a New Appointment
		$.windowTitle = 'New Appointment';
		var NewAppointmentView = require( '/ui/common/views/NewAppointmentView' );
		self = new NewAppointmentView();

	} else if ( 'services' === dataURL ) {

		// Webview for list of shop services
		$.windowTitle = 'Services';
		var ServicesView = require( '/ui/common/views/ServicesView' );
		self = new ServicesView();

	} else if ( 'shop_information' === dataURL ) {

		// Webview for Shop Information (owners)
		$.windowTitle = 'Shop Information';
		var ShopInformationView = require( '/ui/common/views/ShopInformationView' );
		self = new ShopInformationView();

	} else if ( 'barbers' === dataURL ) {

		// Webview to show barbers (owners only)
		$.windowTitle = 'Barbers';
		var BarbersView = require( '/ui/common/views/BarbersView' );
		self = new BarbersView();

	} else if ( 'notifications' === dataURL ) {

		// Webview to show barbers (owners only)
		$.windowTitle = 'Notifications';
		var NotificationView = require( '/ui/common/views/NotificationsView' );
		self = new NotificationView();

	} else {

		// error logic ... if the request fails for some reason (should NEVER happen)
		alert( 'Failed on _dataURL: ' + dataURL );

		// internal programming error
		self = Ti.UI.createView({
			backgroundColor: $.defBackgroundColor
		});
	
		var text = Ti.UI.createLabel({
			color: $.defFontColor,
			text: 'Internal error loading dataURL, sorry.',
			textAlign: 'center',
			font: { fontSize: '28dp' },
			top: '20%',
			left:'4%',
			right:'4%'
		});
		self.add( text );

		// log error to databaes
		var Logger = require( '/application/Logging' );
		var logger = new Logger();
		logger.error( 'Error loading data: ' + dataURL, 'Misfire of controller logic' );
	}

	$.info( 'Window Title: ' + $.windowTitle);
	return self;
}

module.exports = AppController;
