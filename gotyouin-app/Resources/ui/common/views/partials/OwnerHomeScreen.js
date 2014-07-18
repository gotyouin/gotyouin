/**
* @file OwnerHomeScreen.js
*
* Homescreen content for Owners. As of 2013-02-05, this is a list of Barbers for this shop.
*
* @author Hal Burgiss  2013-01-31
*/

function OwnerHomeScreen( view ) {
	$ = require( '/application/Application' );

	$.info('owners home view module');

	var Echo = require( '/ui/common/Echo' );
	view.add ( new Echo( 'Welcome ' + $.user.firstName, {top: '5dp', bottom: '10dp', font: {fontSize: '20dp', fontWeight: 'bold' } } ) );
	view.add ( new Echo( 'Here are your barbers', {top: '2dp', bottom: '4dp', font: {fontSize: '16dp', fontWeight: 'bold' } } ) );

	var actInd = Ti.UI.createActivityIndicator({
		width: Ti.UI.SIZE,
		height: Ti.UI.SIZE,
		message: ' loading ... ',
		font: { fontSize : '24dp' },
		top: '10%',
		color: $.defAccentColor,
		IndicatorColor: $.defAccentColor
	});
	actInd.show();
	view.add( actInd );

	try {

		// Load the webView
		$.info( 'Starting Owner webview code');
		var WebView = require( '/ui/common/WebView' );
		var webView =  new WebView( '/user/' + $.user.userID + '/barbers?app=1' );
		view.add( webView );

		webView.addEventListener( "load", function(e) {
			if ( actInd !== null ) {
				view.remove( actInd ); actInd = null;
			}
			// Handle 404 -- pageStatus is from custom DBS js in footer.php
			var response = webView.evalJS( "pageStatus" );
			if ( response && '404' === response ) {
				var Echo = require( '/ui/common/Echo' );
				view.add ( new Echo( 'Sorry, but there is a problem, please try later.', { left: "10%", right: "10%", top: "20%" } ) );
				view.remove( webView );
				webView = null;
			} else {
				webView.height = 'auto';
			}
		});

	} catch( error ) {

		var Echo = require( '/ui/common/Echo' );
		var Logger = require( '/application/Logging' );
		var logger = new Logger();

		// error handling.
		view.add( new Echo( 'There was a problem, please try again.' ) );
		logger.error( 'Error on OwnersHomeScreen: ' + error.message );

	} finally {

	}

	return this;

}

module.exports = OwnerHomeScreen;
