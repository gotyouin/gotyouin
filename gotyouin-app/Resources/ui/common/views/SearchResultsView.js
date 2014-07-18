/**
* @file SearchResultsView.js
*
* Webview for the Barber search that is run from the home screen.
*
* @author Hal Burgiss 2013-01-24
*/

//Constructor
function SearchResultsView( searchTerm ) {
	"use strict";

	// DBS application configuration variables
	var $ = require( '/application/Application' ), url;

	$.info('search results view module, searching for: ' + searchTerm);


	// Need the URL first .... check for zipcode vs name
	if ( searchTerm.match( /^\d{5}$/ )  ) {

		// we have a zipcode, Fred
		//url = '/search/barbershops/?postal_code=' + searchTerm + '&search_distance=25&search_unit=mile&app=1';
		url = '/search/barbers/?title=&distance%5Bpostal_code%5D=' + searchTerm + '&distance%5Bsearch_distance%5D=25&distance%5Bsearch_units%5D=mile&app=1';
	} else {

		// if not zipcode, then it must barber name
		//url = '/search/barbershops/?field_display_name_value=' + encodeURIComponent( searchTerm ) + '&search_distance=25&search_unit=mile&app=1';
		url = '/search/barbers?distance%5Bpostal_code%5D=&distance%5Bsearch_distance%5D=25&distance%5Bsearch_units%5D=mile&field_display_name_value=' + searchTerm + '&field_first_name_value=' + searchTerm + '&field_last_name_value=' + searchTerm + '&app=1';
	}
	$.info( 'URL: ' + url );

	// MainView is the same for all screens. This is the top nav bar and main view //
	var MainView = require( '/ui/common/views/MainView' );
	var WebView = require( '/ui/common/WebView' );
	var webView =  new WebView( url );
	var self = new MainView( webView );

	var actInd = Titanium.UI.createActivityIndicator({
		width: Titanium.UI.SIZE,
		height: Titanium.UI.SIZE,
		message: ' loading ... ',
		font: { fontSize : '24dp' },
		top: '10%',
//		style: Ti.UI.iPhone.ActivityIndicatorStyle.DARK,
		color: $.defAccentColor,
		IndicatorColor: $.defAccentColor
	});
	actInd.show();
	self.add( actInd );

	self.add( webView );

	webView.addEventListener( "load", function(e) {
		if ( actInd !== null ) {
			self.remove( actInd ); actInd = null;
		}

		$.info( 'Search results page loaded');

		try {
			var location = webView.evalJS( "pathName" );
			$.info( "Location: " + location );

			// special sauce for if someone logs in AFTER doing a search.
			if ( location.match(/thank-you/) ) {

				var initWindow = Ti.UI.createWindow({
					zIndex: -1
				});

				initWindow.hide();
				initWindow.open();
				var DrupalUser = require( '/application/GetDrupalUser' );
				new DrupalUser( initWindow );
				// make the login button say account
				Ti.App.fireEvent( 'updateButtonLabel', { data : true } );

				$.homeNeedsUpdating = $.userNeedsUpdating = true;  // well ... it depends what happened here, but safest to do an update.

				try {
					setTimeout( function() {
						initWindow.close();
						// without this extra delay, Android 4 crashed here ... wtf
						setTimeout ( function() {
							initWindow = null;
						}, 2000);
						$.info( 'Closing initWindow2 for Drupal user code');
					}, 4500 );
				} catch (err) {
					$.info( 'Error closing initWindow in Account: ' + err.message );
				}

			}

		} catch (err ) {
			$.info( 'Error search results: ' + err.message);
		}

	});

	return self;
}

module.exports = SearchResultsView;