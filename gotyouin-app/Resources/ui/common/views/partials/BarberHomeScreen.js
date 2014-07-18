/**
* @file BarberHomeScreen.js
*
* Homescreen content for Barbers (both types). As of 2013-02-05, identical to
* AppointmentsView.js, which is a menu link.
*
* @author Hal Burgiss  2013-01-31
*/

function BarberHomeScreen( view  ) {
	$ = require( '/application/Application' );

	$.info('barber home view module');

	var Echo = require( '/ui/common/Echo' );
	view.add ( new Echo( 'Welcome ' + $.user.firstName, {top: '6dp', bottom: '12dp', font: {fontSize: '20dp', fontWeight: 'bold' } } ) );
	view.add ( new Echo( 'Here are your appointments', {top: '2dp', bottom: '4dp', font: {fontSize: '16dp', fontWeight: 'bold' } } ) );
	
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
		
		var js = require( '/application/library/JavascriptExtensions' );
		// custom date protoype
		//var field_date = new Date().dateToYMD();		
		var field_date = js.dateToYMD();
		
		$.info( 'Field date: ' + field_date );
		var WebView = require('/ui/common/WebView');
		var webView = new WebView( '/user/' + $.user.userID + '/appointments?app=1&field_date_value=' + field_date );
		view.add( webView );
		//webView.height = 0;
	
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
		logger.error( 'Error on Barber Home View: ' + error.message );
		$.info( 'Barber Appt Error: ' + error.message );
	
	} finally {

	}


	return this;

}

module.exports = BarberHomeScreen;
