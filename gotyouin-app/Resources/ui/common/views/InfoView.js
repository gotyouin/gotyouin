/**
* @file InfoView.js
*
* Device and app specific info, used for debugging. Probably will not go into production.
*
* TODO: Make pretty
*
* @author Hal Burgiss 2013-01-23
*/

function InfoView() {
	"use strict";

	// DBS application configuration variables
	var $ = require( '/application/Application' );

	$.info('infoview module');

	// MainView is the same for all screens. This is the top nav bar and main view //
	var MainView = require( '/ui/common/views/MainView' ), self, mainContent, Echo;
	self = new MainView();

	// subview to hold the main content area for this screen
	mainContent = Ti.UI.createScrollView({
		backgroundColor: $.defBackgroundColor,
		width:'100%',
		height: 'auto',
		layout:'vertical',
		top: '1%'
	});
	self.add( mainContent );

	Echo = require( '/ui/common/Echo' );

	mainContent.add ( new Echo( 'Got You In', {top: '5dp', font: {fontSize: '20dp', fontWeight: 'bold' } } ) );
	mainContent.add ( new Echo( 'Application Information', {top: '1%', font: {fontSize: '18dp', fontWeight: 'bold' } } ) );

	try {
		var props = Ti.App.Properties.listProperties();

		// over-ride default styles for Echo's on this screen
		var textStyles = { left: '10%', top: '4dp', font: { fontSize: '14dp' } };

		mainContent.add ( new Echo( 'Current Time' + ' = ' + new Date(), textStyles ) );
		mainContent.add ( new Echo( 'Data Store: ' + $.siteURL, textStyles ) );

		var i, value, ilen;
		for ( i=0, ilen=props.length; i<ilen; i++ ){
			value = Ti.App.Properties.getString(props[i]);
			if ( props[i] == 'ti.deploytype' ) continue;
			if ( props[i] == 'ti.ui.defaultunit' ) continue;
			if ( props[i].match(/^(Webkit|WebData)/i) ) continue;
			if ( props[i].match(/^NS/) ) continue;
			if ( props[i].match(/^Apple/) ) continue;
			//TODO: Filter out ^Web, ^NS, ^Apple
			Ti.API.info( props[i] + ' = ' + value );
			mainContent.add ( new Echo( props[i] + ' = ' + value, textStyles) );
		}

		mainContent.add ( new Echo( 'App Version' + ' = ' + Ti.App.version, textStyles) );
		mainContent.add ( new Echo( 'UUID' + ' = ' + Ti.Platform.id, textStyles) );
		mainContent.add ( new Echo( 'Platform' + ' = ' + Ti.Platform.osname, textStyles) );
		mainContent.add ( new Echo( 'OS Version' + ' = ' + Ti.Platform.version, textStyles) );
		mainContent.add ( new Echo( 'Model' + ' = ' + Ti.Platform.model + '\n',  textStyles ) );
		mainContent.add ( new Echo( 'Memory' + ' = ' + Ti.Platform.availableMemory + '\n',  textStyles ) );
		mainContent.add ( new Echo( 'Last Sync' + ' = ' + new Date( $.lastUpdate * 1000 ) + '\n',  textStyles ) );

		mainContent.add ( new Echo( 'Start Date' + ' = ' + $.startDate + '\n', textStyles) );

		mainContent.add ( new Echo( 'Register ID: ' + $.registerID , textStyles) );

		if ( null === $.user ) {
			mainContent.add ( new Echo( 'User is not configured, anonymous', textStyles) );
		} else {
			mainContent.add ( new Echo( 'Locally Stored Values: ', textStyles) );
			var j;
			for ( j in $.user ) {
				if ( $.user.hasOwnProperty(j) ) {
					mainContent.add ( new Echo( j + ' = ' + $.user[j], textStyles) );
				}
			}

			mainContent.add ( new Echo( 'Notifications: ', textStyles) );
			j = 0;
			for ( j = 0; j < $.notifications.length; j++ ) {
				mainContent.add ( new Echo( 'Notification: ' + $.notifications[ j ] , textStyles) );
			}
			mainContent.add ( new Echo( 'USER ID: ' + $.user.userID , textStyles) );
			mainContent.add ( new Echo( 'USER Type: ' + $.user.userType , textStyles) );
			if ( $.user.userType === 'Owner' ) {
				mainContent.add ( new Echo( 'Shop ID: ' + $.user.shopID , textStyles) );
			}
		}

		if ( $.geoCoords.length > 0 ) {
			mainContent.add ( new Echo( 'Longitude: ' + $.geoCoords[0] , textStyles) );
			mainContent.add ( new Echo( 'Latitude: ' + $.geoCoords[1] , textStyles) );
		} else {
			mainContent.add ( new Echo( 'No geodata' , textStyles) );
		}

if ( $.isAndroid ) {

		// HB: something in here is causing serious issues for iphone ?????
		var WebView = require( '/ui/common/WebView' ), webView;
		webView =  new WebView( '/gotyouin_php/gotyouin_user_status.php' );
		webView.addEventListener( "load", function(e) {
			var session = webView.evalJS( "sessVal" ).trim();
			var loggedin = webView.evalJS( "loggedin" ).trim();
			var user = JSON.parse( webView.evalJS( "user" ) );
			$.info( 'Loading session webview session var: ' + session );
			$.info( 'Loading session webview loggedin: ' + loggedin );
			//sessionTxt.text += session;
			//loggedinTxt.text += loggedin;
			$.info( 'Loading session webview user id: ' + user.uid );
			$.info( 'Loading session webview user type: ' + user.user_type );
			//userTxt.text += user.uid;

			mainContent.add ( new Echo( 'Session: ' + session, textStyles) );
			mainContent.add ( new Echo( 'Logged In: ' + loggedin, textStyles) );
			mainContent.add ( new Echo( 'User ID: ' + user.uid, textStyles) );
			mainContent.add ( new Echo( 'User Type: ' + user.user_type, textStyles) );
		});

		// load webview (required even for hidden webviews)
		webView.hide();
		webView.height = 0;
		mainContent.add( webView );
		setTimeout( function() {
			mainContent.remove( webView );
			webView = null;
		}, 6000 );
}

	} catch( error ) {
		alert( 'Error getting info, sorry: ' + error.message );
		$.info( error.message );
		var Logger = require( '/application/Logging' ), logger;
		logger = new Logger();
		logger.error( 'Error getting SysInfo: ' + error.message );
	}

	mainContent.add ( new Echo( 'Got You In!\nPO Box 835\nLaVergne, TN 37086\n(502) 414-1541\ninfo@gotyouin.com\n\n', {top: '5%'} ) );

	return self;
}

module.exports = InfoView;
