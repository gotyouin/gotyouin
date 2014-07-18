/**
* @file AccountButtonContainer.js
*
* Homescreen widget to start the account registration process.
*
* @author Hal Burgiss  2013-01-31
*/

function AccountButtonContainer( view, logoSizeW, logoSizeH, SignUpH, SignUpW ) {
	$ = require( '/application/Application' );

	var self = Ti.UI.createImageView({
			image : '/images/GYI-Signup-white.png',
			height: SignUpH,
			width : SignUpW,
			backgroundColor: 'transparent',
			top: 20
	});

	view.add ( self );
	//self.top = $.displayHeight - logoSizeH - SignUpH - 20*5 - 115;

	self.addEventListener( 'click', function() {
		// open the new window for choosing acct type
		$.slidingMenu.done();
		var Window = require( '/ui/common/NewWindow' );
		new Window( 'choose_account' ).open( $.defWindowAnimation );
	});

	return self;
}

module.exports = AccountButtonContainer;
