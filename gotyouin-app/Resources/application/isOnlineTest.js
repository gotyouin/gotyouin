/**
* @file isOnlineTest.js
*
* Make sure we have a good connection since we are using webviews heavily.
*
* @author Hal Burgiss  2013-01-11
*/

// TODO: This file is not being used ... test for NETWORK_NONE explicitly for
// now due the httpclient() / ajax requests are all asynchronous (and slow).

// NOTE: Probably should make this an occasional background task and keep a
// global var. updated.



( function isOnline() {

	var OnLine = null;

	// return true or false, based on network connectivity
	if ( Titanium.Network.networkType === Titanium.Network.NETWORK_NONE ) {
		// check cell connection
		Titanium.API.info( 'no connection ' );
		OnLine = false;
		return false;
	}

	Titanium.API.info( 'connection present ' );
	OnLine = true;
	return true;
	
/*

NOTE 2013-01-14: This code checks for a valid return from the website, and is probably
more reliable than a simple cell network check. But it is purely asynchronous,
and takes too long with the response coming too late.

	// DBS application configuration variables
	var $ = require( '/application/Application' );

	// check http level connection
	var _url = $.siteURL + '/gotyouin_php/is_online.php';

	var xhr = Ti.Network.createHTTPClient( {
		onload: function( e ) {
			// this function is called when data is returned from the server and available for use
			// this.responseText holds the raw text return of the message (used for text/JSON)
			// this.responseXML holds any returned XML (including SOAP)
			// this.responseData holds any returned binary data
			// Note the return value from php includes quotes!!! Wow. 2013-01-14
			$.info( 'Success: ' + this.responseData );
			if ( this.responseData != '"success"' ) {
				OnLine = false;
				$.info( 'Success is: ' + OnLine );
				return OnLine;
			} else {
				OnLine = true;
				$.info( 'Success is: ' + OnLine );
				return OnLine;
			}
		},
		onerror: function(e) {
			// this function is called when an error occurs, including a timeout
			$.info( 'Error: ' + e.error );
			OnLine = false;
			return false;
			Ti.API.debug(e.error);
		},
		timeout:4000  
	});
	xhr.open( "GET", _url, false);
	xhr.send();  // request is actually sent with this statement

	// must be OK
	return Online;

*/
})();

module.exports = OnLine;
