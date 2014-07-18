/**
* @file RegisterNewUser.js
*
* Handles registering a user in the registration database, first time the phone
* is used, for tracking purposes. This is not a Drupal user database function,
* but purely for tracking phone users. TODO: Partially implemented 2013-01-22
*
* @author Hal Burgiss  2013-01-17
*/

( function() {
	
	var $ = require( '/application/Application' );
	var url = $.siteURL + '/gotyouin_php/gotyouin_mobile_services.php';

	$.info( 'Running user registration code' );
	
	if (  Titanium.Network.networkType === Titanium.Network.NETWORK_NONE ) {

		$.info( 'Failed with registration code -- no network' );
		return;
	}
	
	try {
		var client = Ti.Network.createHTTPClient({
			// function called when the response data is available
			onload : function(e) {
				$.info("Received registration text from server: " + this.responseData);
				var data = JSON.parse( this.responseData );
				$.info("Parsed: " + data.hash );
				if ( parseInt( data.registerID, 10 ) > 1000 ) {
						// save both as session var, and long term local storage
					$.registerID = data.registerID;
					$.info( 'Register ID: ' + $.registerID );
					Ti.App.Properties.setString( 'registerID', $.registerID );
					Ti.App.Properties.setString( 'hash', data.hash );
				}
			},
			// function called when an error occurs, including a timeout
			onerror : function(e) {
				$.info( e.error );
//				alert( e.error );
			},
			timeout : 12000  // in milliseconds
		});
		 
		// Prepare to send.
		client.open( "POST", url );
	
		// Send the request to database on main site
		client.send( { 'action' : 'register_user', 'uuid' : Ti.Platform.id, 'platform' : Ti.Platform.osname } ); 
	
	} catch(error) {

		// handle errors
		$.info( 'ERROR: ' + error.message );

		// Log error
		var Logger = require( '/application/Logging' );
		var logger = new Logger();
		logger.error( 'Error registering new user: ' + error.message );
	}
})();
