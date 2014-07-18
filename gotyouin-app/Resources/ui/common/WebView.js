/**
* @file WebView.js
*
* Default webview generator for Got You In. This instance should only be used
* for visible webViews attached to user windows (and not hidden webviews )
*
* NOTE: *ALL webViews* must have the query string '?app=1' added to tell Drupal
* we are a mobile phone calling home.
*
* @author Hal Burgiss  2013-01-17
*/

function WebView( url ) {

	var $ = require( '/application/Application' ), self, Logger, logger;

	// Check network status and load a network error screen if we have no
	// network.
	// TODO: this checks the cell / wifi network. We should have a test of the
	// site itself. WARNING: If done via Ti HTTPClient(), it is asynchronous.
	if (  Titanium.Network.networkType !== Titanium.Network.NETWORK_NONE ) {

		try {

			$.info( 'Loading webview ' + $.siteURL + url );
			self =  Ti.UI.createWebView( {
				url : $.siteURL + url ,
				top: 0,
				height: 'auto',
				backgroundColor: 'transparent',
				hideLoadIndicator: false,
				willHandleTouches: true,
				touchEnabled: true
//				animate: true
			});
			self.addEventListener( 'error', function(e) {
				// log error
				Logger = require( '/application/Logging' );
				logger = new Logger();
				logger.error( 'Webview error', 'URL: ' + url + ', message: '  + e.message );
				$.info( ' Webview error: ' + e.message );
			});


			if ( $.isAndroid ) {

				setTimeout( function() {
					var thisWindow = $.currentWindow;
					$.info( 'THISVIEW: ' + thisWindow.thisView );
					$.info( $.thisView +' ' + $.windows.length );
					$.info( '.....................................................');

					// save reference to webView for Android back button (see NewWindow.js)
					//$.info( 'Adding webview referenece for Android')
					//$.info( $.currentWindow instanceof Ti.UI.Window );
					//$.currentWindow.webView = self;
					//$.info( $.currentWindow.webView instanceof Array );

					// handle android back button
					$.info( 'Removing back button event listener' );
					thisWindow.removeEventListener( 'android:back', $.androidBackDefault );
					var androidBackDefault = function() {
						$.info( 'Android backbutton listener firing' );
						if ( $.isset( self ) && self.canGoBack() ) {
							self.goBack();
						} else {
							thisWindow.close();
							$.windows.pop();
						}
					};

					thisWindow.addEventListener( 'android:back', androidBackDefault );
				},500);

			}

		} catch( error ) {

			// log error
			Logger = require( '/application/Logging' );
			logger = new Logger();
			logger.error( 'Error on webView ', 'URL: ' + url + ' error: '  + error.message );
			$.info( 'Error on webView URL: ' + url + ' error: '  + error.message );
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

module.exports = WebView;
