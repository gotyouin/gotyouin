/**
* @file ChooseAccountView.js
*
* Pick which account type to register for, then get linked to webView for that account type.
*
* @author Hal Burgiss 2013-01-24
*/

// Constructor
function ChooseAccountView() {
	"use strict";

	// DBS application wide configuration variables and settings
	var $ = require( '/application/Application' );

	$.info('choose account module');

	// MainView is the same for all screens. This is the top nav bar and main view //
	var MainView = require( '/ui/common/views/MainView' );
	var self = new MainView();

	// subview to hold the main content area for this screen
	var iphoneSpace;
	if( $.isIphone ) { iphoneSpace = -14; } else { iphoneSpace = 0; }
	var webHeader = Ti.UI.createWebView({
		top: 0,
		left: 0,
		touchEnabled: false,
		width: '100%',
		height: 85
	});
	webHeader.html = '<html style="width:100%;"><head><meta content="width=device-width; initial-scale=1.0; maximum-scale=1.0; user-scalable=0;" name="viewport" /></head><body style="text-align:center; font-size:14px; color:#fff; background:#272727;"><p style="float:left; text-align:center; display:block; width:100%;"><strong>Signing up is easy</strong>. First tell us what kind of account you are signing up for.</p></body></html>';
	self.add( webHeader );
	
	var mainContent = Ti.UI.createScrollView({
		backgroundColor: 'transparent',
		width:'100%',
		height: 'auto',
//		height: Titanium.UI.FILL, // fill parent container
		layout:'vertical',
		top: 0
	});
	self.add( mainContent );

	var txtFormat = {
		top: 20,
		bottom: 0,
		height: Titanium.UI.SIZE,
		textAlign: 'center',
		width:'95%',
		font: { fontSize: '14dp' }
	};
	
	var SignupBtnW, SignupBtnH;
	if( $.displayWidth * 0.9 < 586 ){
		SignupBtnW = $.displayWidth * 0.9;
		SignupBtnH = $.displayWidth * 0.9 * 120/586;
	}else{
		SignupBtnH = 120;
		SignupBtnW = 586;
	}

	var buttonFormat = {
		width: SignupBtnW,
		left: '5%',
		top: 10,
		height: SignupBtnH,
		color: '#272727',
		borderWidth: 0,
		backgroundImage: '/images/Big-btn.png',
		backgroundColor: 'transparent',
		textAlign:'left',
		font: { fontSize: '15dp', fontFamily:'TitilliumWeb-Bold' }
	};
	var BigButton = require( '/ui/common/BigButton' );

	// open the new window for creating the accounts (webView)
	var Window = require( '/ui/common/NewWindow' );

	// Start laying out the 4 buttons
	var customer = new BigButton( "I'M A CUSTOMER", buttonFormat );
	mainContent.add ( customer );
	customer.addEventListener( 'click', function() {
		$.slidingMenu.done();
		new Window( 'create_customer' ).open( $.defWindowAnimation );
	});

	// Handle both barbers and independent barbers.
	var barber = new BigButton( "I'M A BARBER", buttonFormat );
	mainContent.add ( barber );
	barber.addEventListener( 'click', function() {
		// Make sure the user knows the difference about what an independent barber is. 2013-01-30
		var Confirm = require( '/ui/common/Alert' );						
		var confirm = new Confirm( "There are two kinds of barbers: I'm in a Got You In Member Shop. Or, I'm doing my own thing.", 
								{ title : 'Barber Type', buttonNames: [ "With a Shop", "On my Own", "Cancel" ] }
							);
		if ( $.isAndroid ) {
			var backButton = false;
			confirm.addEventListener( 'android:back', function(e) {
				backButton = true;
				confirm.hide();
				e.index = 2;
			});
		}			
		confirm.addEventListener( 'click', function( e ) {
			$.slidingMenu.done();
			if ( $.isAndroid && backButton ) {
				// FIXME: does not work 2013-02-02
				backButton = false;
				return false;
			}	
			if ( e.index == 2 )	return; // FIXME: still submits on Android back button 2013-02-02	
			if ( e.cancel === e.index || e.cancel === true ) { 
				new Window( 'create_independent' ).open( $.defWindowAnimation );
			} else {
				new Window( 'create_barber' ).open( $.defWindowAnimation );
			}
		});
		

	});

	var owner = new BigButton( "I'M A SHOP OWNER", buttonFormat );
	mainContent.add ( owner );
	owner.addEventListener( 'click', function() {
		$.slidingMenu.done();		
		new Window( 'create_owner' ).open( $.defWindowAnimation );
	});

	var school = new BigButton( 'BARBER SCHOOL', buttonFormat );
	mainContent.add ( school );
	school.addEventListener( 'click', function() {
		$.slidingMenu.done();
		// owners and barber schools use the same registration		
		new Window( 'create_owner' ).open( $.defWindowAnimation );
	});
	
	// text blurbs for home screen
	var Echo = require( '/ui/common/Echo' );
	// message
	mainContent.add( new Echo( 'All registration forms are also available at http://www.gotyouin.com', txtFormat ) );

	return self;
}

module.exports = ChooseAccountView;
