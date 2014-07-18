/**
* @file WebViewHidden.js
*
* Hidden webviews generator for Got You In, where we are just exchanging http data with Drupal.
*
* NOTE: *ALL webViews* must have the query string '?app=1' added to tell Drupal
* we are a mobile phone calling home.
*
* @author Hal Burgiss  2013-01-17
*/

function WebViewHidden( url ) {

	var $ = require( '/application/Application' ), self, Logger, logger;

	// Check network status and load a network error screen if we have no
	// network.
	// TODO: this checks the cell / wifi network. We should have a test of the
	// site itself. WARNING: If done via Ti HTTPClient(), it is asynchronous.
	if (  Titanium.Network.networkType !== Titanium.Network.NETWORK_NONE ) {

		try {

			$.info( 'Loading HIDDEN webview ' + $.siteURL + url );
			self =  Ti.UI.createWebView( {
				url : $.siteURL + url ,
				top: 0,
				height: 'auto',
				backgroundColor: 'transparent',
				hideLoadIndicator: true
				//willHandleTouches: true,
				//touchEnabled: true
//				animate: true
			});
			self.addEventListener( 'error', function(e) {
				// log error
				Logger = require( '/application/Logging' );
				logger = new Logger();
				logger.error( 'Hidden Webview error', 'URL: ' + url + ', message: '  + e.message );
				$.info( 'Hidden Webview error: ' + e.message );
			});


		} catch( error ) {

			// log error
			Logger = require( '/application/Logging' );
			logger = new Logger();
			logger.error( 'Error on hidden webView ', 'URL: ' + url + ' error: '  + error.message );
			$.info( 'Error on hidden webView URL: ' + url + ' error: '  + error.message );
		}

	} else {

		// no network available
		self = Ti.UI.createView({
			backgroundColor: $.defBackgroundColor
		});

		var text = Ti.UI.createLabel({
			color: $.defFontColor,
			text: 'Sorry network error ... please try again later.',
			textAlign: 'center',
			font: { fontSize: '28dp' },
			top: '20%',
			left:'4%',
			right:'4%'
		});
		self.add( text );

		// log activity
		Logger = require( '/application/Logging' );
		logger = new Logger();
		logger.activity( 'Network Unavailable' );
	}

	return self;
}

module.exports = WebViewHidden;
