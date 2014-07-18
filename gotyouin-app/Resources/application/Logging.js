/**
* @file Logging.js
*
* Handles 'activity' and 'error' logging. All such is pretty arbitrarily
* sprinkled around in the code. On the server side, the submissions are
* processed by /gotyouin_php/gotyouin_mobile_services.php, and databased
* in gotyouin_* tables.
*
* @author Hal Burgiss  2013-01-17
*/

function Logger() {
	"use strict";

	var $ = require( '/application/Application' );
	var url = $.siteURL + '/gotyouin_php/gotyouin_mobile_services.php';

	/**
	* App activity logger method, 'activity' is required, message is optional.
	*/
	this.activity = function( activity, message ) {

		if (  Titanium.Network.networkType === Titanium.Network.NETWORK_NONE ) {
			// bummer, can't log
			// TODO: Stored message and activity in a local storage for later transmission.
			return;
		}

		message = message || '';
		$.info( 'Activity logger code' );
		try {
			var client = Ti.Network.createHTTPClient({
				// function called when the response data is available
				onload : function(e) {
					$.info( "Received activity log text: " + this.responseData);
					var result = JSON.parse( this.responseData );
					$.info( "Activity Parsed: " + result.result );
				},
				// function called when an error occurs, including a timeout
				onerror : function(e) {
					$.info( e.error );
				},
				timeout : 10000  // in milliseconds
			});

			// Prepare to send.
			client.open( "POST", url );

			// Send the request to database on main site
			var userid =  ($.user) ? $.user.userID : 0;
			client.send( {
				'action' : 'log_activity',
				'register_id' : $.registerID,
				'drupal_user_id' : userid,
				'activity' : activity,
				'message' : message,
				'hash' :  Ti.App.Properties.getString('hash')
			});

		} catch(error) {

			// handle errors
			$.info( 'ERROR: ' + error.message );
			this.error( 'Log Activity', 'Error from Logging.js: ' + error.message );
		}

	}; //end method

	/**
	* App Error logger method, 'activity' is required, message is optional.
	*/
	this.error = function( error, message ) {
		message = message || '';
		$.info( 'Error logger code' );

		if (  Titanium.Network.networkType === Titanium.Network.NETWORK_NONE ) {
			// bummer, can't log
			// TODO: Stored message and activity in a local storage for later transmission.
			return;
		}

		try {
			var client = Ti.Network.createHTTPClient({
				// function called when the response data is available
				onload : function(e) {
					$.info( "Received error logging text: " + this.responseData);
					var result = JSON.parse( this.responseData );
					$.info( "Error log text Parsed: " + result.result );
				},
				// function called when an error occurs, including a timeout
				onerror : function(e) {
					$.info( e.error );
				},
				timeout : 8000  // in milliseconds
			});

			// Prepare to send.
			client.open( "POST", url );

			// Send the request to database on main site
			var userid =  ($.user) ? $.user.userID : 0;
			var error_data = $.user || {};
			error_data.startDate = $.startDate;
			error_data.appVersion = Ti.App.version;
			error_data.installDate = Ti.App.Properties.getString('installDate');
			error_data.platform = Ti.Platform.osname;
			error_data.version = Ti.Platform.version;
			error_data.model =  Ti.Platform.model;
			error_data.model = Ti.Platform.availableMemory;
			client.send( {
				'action' : 'log_error',
				'register_id' : $.registerID,
				'drupal_user_id' : userid,
				'error' : error,
				'message' : message,
				'hash' :  Ti.App.Properties.getString('hash'),
				'error_data' : JSON.stringify( error_data )
			});

		} catch( e ) {

			// handle errors
			$.info( 'ERROR: ' + e.message );
		} //end error


	}; // end method

	return this;
}

module.exports = Logger;
