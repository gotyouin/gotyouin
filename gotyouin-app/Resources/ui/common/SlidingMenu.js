/**
* @file SlidingMenu.js
*
* This is the slide out menu available on all screens, activated by the left
* button in topNavBar. Included from MainView.js. Under the hood, this is
* specialized window that is created once, but opens and closes as needed.
*
* TODO: Make this handle the full screen height ala Facebook menu. 2013-01-23
*
* @author Hal Burgiss  2013-01-18
*/

function SlidingMenu() {
	"use strict";

	var $ = require( '/application/Application' );
	$.info( 'Creating sliding menu' );

	// Sliding menu Window that will hold menu in tableView
	var self = Ti.UI.createWindow({
//		transparentBackground: true,
		backgroundColor: $.defBackgroundColor,
		animated: true,
	//	color: $.defFontColor,
		isClosed: true,
		top:   '45dp',
		height: 'auto',
		left:  0,
		width: '200dp',
		zIndex: 999
	});

	// config stuff for table rows
	var tableData, rowFields = {
		height: '42dp',
		color: $.defFontColor,
		font: { fontSize: $.defFontSize }
	};

	/**
	 * @return void, close this window, and makes it go way
	 */
	self.done = function() {

		self.close();
		self.left = $.displayWidth;
		self.visible = false;
		//self.zIndex = -999;
		//self.height = 0;
		self.isClosed = true;
	};

	/**
	* @return array tableData of menu items, populated based on userType
	*/
	self.makeData = function() {

		tableData = [];

		// No place like home.
		tableData.push( { title: "Home", height: rowFields.height, color: rowFields.color, font: rowFields.font, backgroundColor: $.defBackgroundColor } );

		// handle special cases for barbers and owners first.
		if ( $.userLoggedIn() && $.user.userType.match(/Barber/i) ) {
			$.info( ' Loading for user, user Type is: ' + $.user.userType );
			tableData.push( { title: "Appointments", height: rowFields.height, color: rowFields.color, font: rowFields.font, backgroundColor: '#1b1b1b' } );
		}

		if ( $.userLoggedIn() && 'Owner' === $.user.userType ) {
			tableData.push( { title: "Appointments", height: rowFields.height, color: rowFields.color, font: rowFields.font, backgroundColor: '#1b1b1b' } );
			// only for barbers 2013-02-03:  tableData.push( { title: "Services", height: rowFields.height, color: rowFields.color, font: rowFields.font, backgroundColor: '#1b1b1b' } );
			tableData.push( { title: "Shop Information", height: rowFields.height, color: rowFields.color, font: rowFields.font, backgroundColor: '#1b1b1b' } );
			tableData.push( { title: "Barbers", height: rowFields.height, color: rowFields.color, font: rowFields.font, backgroundColor: '#1b1b1b' } );
		}

		if ( $.userLoggedIn() ) {
		// disabling	tableData.push( { title: "Notifications", height: rowFields.height, color: rowFields.color, font: rowFields.font, backgroundColor: '#1b1b1b' } );
		}

		if (  ($.userLoggedIn() && 'Owner' != $.user.userType) || ! $.userLoggedIn() ) {

			// This is for everyone *except* shop owners ... but handled differently for customers vs barbers
			tableData.push( { title: "New Appointment", height: rowFields.height, color: rowFields.color, font: rowFields.font, backgroundColor: $.defBackgroundColor } );
		}

		// tableData.push( { title: "Company", height: rowFields.height, color: rowFields.color, font: rowFields.font, backgroundColor: $.defBackgroundColor });
		tableData.push( { title: "Feedback", height: rowFields.height, color: rowFields.color, font: rowFields.font, backgroundColor: $.defBackgroundColor } );

		if ( $.userLoggedIn() ) {
			tableData.push( { title: "Log Out", height: rowFields.height, color: rowFields.color, font: rowFields.font, backgroundColor: $.defBackgroundColor } );
		} else {
			tableData.push( { title: "Log In", height: rowFields.height, color: rowFields.color, font: rowFields.font, backgroundColor: $.defBackgroundColor } );
		}

		if ( $.debug ) {
			tableData.push( { title: "Debug Info", height: rowFields.height, color: rowFields.color, font: rowFields.font, backgroundColor: '#1b1b1b' } );
		}

		// calculate row height
		var rowHeight = ( $.user !== null && $.user.userType === 'Owner') ? 44 : 42;
		tableView.height = ( $.isIphone ) ? tableData.length * rowHeight : (tableData.length * 62) + 6  ; // TODO: using font size to calc number of rows, works well for ios but not android

		return tableData;

	}; // end method makeData()

	// create the tableView
	var tableView = Ti.UI.createTableView(
		{	data : [],
			top: 0,
			font : { fontSize: $.defFontSize },
			borderWidth: 1,
			borderColor: $.defBackgroundColor,
			backgroundColor: $.defBackgroundColor,
			separatorColor: '#000000'
	});

	// add Data row to tableView, and size view
	// .setData seems to require 2 steps to work, first is set to empty array.
	tableView.setData( [] );
	tableData = self.makeData();

	tableView.setData( tableData );
	self.add( tableView );

	var Window = require( '/ui/common/NewWindow' );

	//Handle menu clicks on here. See AppController.js for actual routing.
	tableView.addEventListener( 'click', function(e) {
			self.done();

			if ( $.userNeedsUpating ) {
				setTimeout( function() {
					//var DrupalUser = require( '/application/GetDrupalUser' );
					//new DrupalUser( self );
				}, 30000 );
			}
			$.userNeedsUpdating = false;

			switch ( e.rowData.title ) {
//				case 'Company':
//					new Window( 'company' ).open( $.defWindowAnimation );
//					break;

				case 'Notifications':
					new Window( 'notifications' ).open( $.defWindowAnimation );
					break;

				case 'Debug Info':
					new Window( 'info' ).open( $.defWindowAnimation );
					break;

				case 'Feedback':
					new Window( 'feedback' ).open( $.defWindowAnimation );
					break;

				case 'Log Out':
					new Window( 'log_out' ).open( $.defWindowAnimation );
					break;

				case 'Log In':
					new Window( 'login' ).open( $.defWindowAnimation );
					break;

				case 'Appointments':
					new Window( 'appointments' ).open( $.defWindowAnimation );
					break;

				case 'New Appointment':
				//FIXME: alert don't cut it
					var Confirm = require( '/ui/common/Alert'), confirm;

					if ( ! $.userLoggedIn() ) {
						$.info( 'hasAcct: ' + Ti.App.Properties.getString('hasAccount') );
							//if ( Ti.App.Properties.getString('hasAccount') == 'true' || Ti.App.Properties.getString('hasAccount') == true) {
// FIXME: something is resetting this to false incorrectly	feb 2013
							if ( $.isset( $.user ) && $.isset( $.user.hasAccount ) && $.user.hasAccount ) {
								confirm = new Confirm( 'If you have an account, please login first. If not, please create an account.', { 'buttonNames': ['OK', 'Cancel'] } );
								confirm.addEventListener( 'click', function( e ) {
									if ( e.index === 0) {
										new Window( 'login' ).open( $.defWindowAnimation );
									}
								});
							} else {
								confirm = new Confirm( 'Please create an account first, or login if you already have an account.', { 'buttonNames': ['OK', 'Cancel'] });
								confirm.addEventListener( 'click', function( e ) {
									if ( e.index === 0) {
										new Window( 'choose_account' ).open( $.defWindowAnimation );
									}
								});
							}
					} else {
						new Window( 'new_appointment' ).open( $.defWindowAnimation );
					}
					break;

				case 'Services':
					new Window( 'services' ).open( $.defWindowAnimation );
					break;

				case 'Shop Information':
					new Window( 'shop_information' ).open( $.defWindowAnimation );
					break;

				case 'Barbers':
					new Window( 'barbers' ).open( $.defWindowAnimation );
					break;

				case 'Home':
					// close all existing windows, and get to the home screen
					if ( $.windows.length > 0 ) {
						$.homeNeedsUpdating = true;
						$.closeAllWindows();
					}
					break;
			}
	});

	/**
	* @return void, recreates the tableView menu options based on the current user's role / type.
	*/
	self.reset = function() {
		$.info( 'Menu reset' );
		tableView.setData( [] );		// this step seems to be required to empty the array first.
		tableView.setData( self.makeData() );
	};

	return self;
}

module.exports = SlidingMenu;
