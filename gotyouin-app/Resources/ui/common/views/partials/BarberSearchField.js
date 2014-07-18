/**
* @file BarberSearchField.js
*
* Homescreen widget to search for a barber. Customers and Anon users only.
*
* @author Hal Burgiss  2013-01-31
*/

function BarberSearchField( view, logoSizeH, SignUpH  ) {
	$ = require( '/application/Application' );

	var self = Ti.UI.createView({
		width:'100%',
		height: Titanium.UI.SIZE,
		layout:'vertical',
		backgroundColor: 'transparent',
		top:0
	});
	view.add( self );

	// input field for search.
	var TextField = require( '/ui/common/TextField' );

	var searchInput = new TextField( {
			hintText: 'Enter a Name or Zipcode',
			top: '20dp',
			left: null,
			borderWidth: 1,
			color: '#272727',
			height: '36dp',
			width: '80%' } );

	self.add( searchInput );

	// search submit button
	//var SmallButton = require( '/ui/common/SmallButton' );
//	var BigButton = require( '/ui/common/BigButton' );
	var searchButton = Titanium.UI.createButton({
		title:'FIND A BARBER',
		width: '80%',
		height: '40dp',
		top: '20dp',
		style:Titanium.UI.iPhone.SystemButtonStyle.PLAIN,
		borderRadius:4,
		font:{fontSize:16,fontWeight:'bold', fontFamily:'TitilliumWeb-Regular'},
		color: '#ffffff',
		backgroundGradient:{
			type:'linear',
			colors:['#e71c2c','#8c111b'],
			startPoint:{x:0,y:0},
			endPoint:{x:2,y:50},
			backFillStart:false},
		borderWidth:1,
		borderColor:'#e71c2c'
	});
	self.add ( searchButton );
	searchButton.addEventListener( 'click', function( e ) {

		try {
			// Barber search: some sanity checks first, then do a search which will be either for a name or a zipcode 2013-01-26
			var searched = searchInput.value.trim();
			$.info( searched );

			if ( searched.length < 2 || searched.length > 24 ) {
				alert( 'Please provide a better search term' );
				return false;
			}

			// if its all numbers, then it must be 5 digits exactly for a zipcode.
			if ( searched.match(/^\d+$/ ) && !searched.match(/^\d{5}$/) ) {
				alert( 'Invalid search value, please try again.' );
				return false;
			}

			$.slidingMenu.done();
			// open the new window / webView for search results,see SearchResultsView.js
			var Window = require( '/ui/common/NewWindow' );
			new Window( 'barber_search', searched ).open( $.defWindowAnimation );

		} catch (err) {

			// Log error to database
			alert( 'Sorry about that, can you try again?' );
			var Logger = require( '/application/Logging' );
			var logger = new Logger();
			logger.error( 'Error home screen barber search : ' + err.message );

		}
	});

	return self;
}

module.exports = BarberSearchField;
