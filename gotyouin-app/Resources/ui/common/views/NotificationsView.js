/**
* @file NotificationsView.js
*
* Notifications screen. This is not a webview. Notifications originate via cron jobs
* on the server (see /gotyouin_php/gotyouin_notifications.php), with the client
* app checking periodically (once an hour?) and saving these locally (see
* HomeView as of 2013-02-04). NOTE: Phase I has a notificaitons area of the home screen
* for logged in Customers only.
*
* @author Hal Burgiss 2013-02-04
*/

// pull to refressh tutorial: http://developer.appcelerator.com/blog/2010/05/how-to-create-a-tweetie-like-pull-to-refresh-table.html

function NotificationsView() {

	// DBS application configuration variables
	var $ = require( '/application/Application' );


// FIXME DUMMY DATA:
if ( $.user.userType == 'Customer') {
	$.notifications = [
		'Your have an appointment today at 3:00PM',
		'Your next appointment is Saturday at 3:00',
		'Welcome to Got You In!'
	];
} else {
	$.notifications = [
		'New appointment by John Mark for Saturday 3:00',
		'New appointment by Chase C. for Friday at 2:30',
		'Mark S. cancelled appointment for Friday',
		'Your schedule for Saturday is looking pretty damn good'
	];	
}


	$.info( 'notifications view module' );
	
	// Reset new notification flag, as user will have seen these
	$.newNotifications = false;

	var maxNotifications = ( 'Customer' === $.userType ) ? 5 : 30;
						
	// MainView is the same for all screens. This is the top nav bar and main view //
	var MainView = require( '/ui/common/views/MainView' );
	var self = new MainView();

	// subview to hold the main content area for this screen
	var mainContent = Ti.UI.createScrollView({
		backgroundColor: 'transparent',
		width:'100%',
		height: 'auto',
		layout:'vertical',
		top: 0
	});
	self.add( mainContent );

	if ( ! $.isset( $.notifications ) || $.notifications.length === 0 ) {
		
		// handle no notification scenario
		var Echo = require( '/ui/common/Echo' );
		mainContent.add ( new Echo( 'Nothing to report right now!', {top: '20%'} ) );
		return self;
	}

	// use a tableView for display:
	// create the tableView to hold notifications
	var tableView = Ti.UI.createTableView( 
		{	data : [],
			font : { fontSize: '15dp' },
			borderWidth: 1,
			borderColor: $.defBackgroundColor,
			borderRadius: 12,
			backgroundColor: $.defBackgroundColor,
			top: '5%',
			left: '5%',
			right: '5%',
			separatorColor: '#bbb'
	});
	
	// calculate row height
	var rowHeight = ( $.user !== null && $.user.userType === 'Owner') ? 44 : 42;
	
	// config stuff for table rows
	var tableData = []; 
	var rowFields = {
		height: '42dp',
		color: $.defFontColor,
		font: { fontSize: '12dp' }
	};

	mainContent.add( tableView );

	for ( i = 0; i < $.notifications.length; i++ ){
		tableData.push( { title: $.notifications[i], height: rowFields.height, color: rowFields.color, font: rowFields.font, backgroundColor: '#1b1b1b' } );
	}

	tableView.height = ( $.isIphone ) ? tableData.length * rowHeight : (tableData.length * 62) + 6  ; // TODO: using font size to calc number of rows, works well for ios but not android	
	
	// Make it happen
	tableView.setData( tableData );			

	return self;
}

module.exports = NotificationsView;
