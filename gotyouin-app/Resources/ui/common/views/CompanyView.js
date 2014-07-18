/**
* @file CompanyView.js
*
* The Company / About Us content. This is not a webview. 
*
* NOTE: This is temporarily removed from the navigation. 2013-02-04
*
* @author Hal Burgiss  2013-01-21
*/

function CompanyView() {

	// DBS application configuration variables
	var $ = require( '/application/Application' );

	$.info('companyview module');

	// MainView is the same for all screens. This is the top nav bar and main view //
	var MainView = require( '/ui/common/views/MainView' );
	var self = new MainView();

	// subview to hold the main content area for this screen
	var mainContent = Ti.UI.createScrollView({
		backgroundColor: $.defBackgroundColor,
		width:'100%',
		height: 'auto',
		layout:'vertical',
		top: '1%'
	});
	self.add( mainContent );

	var Echo = require( '/ui/common/Echo' );
	mainContent.add ( new Echo( 'Got You In!\nPO Box 835\nLaVergne, TN 37086\n(502) 414-1541\ninfo@gotyouin.com', {top: '20%'} ) );
	
	return self;
}

module.exports = CompanyView;
