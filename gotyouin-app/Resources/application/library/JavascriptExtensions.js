/**
 * @file JavascriptExtensions.js
 *
 * Odds and ends to add JS functionality
 *
 */

( function() {

	/**
	* @param {Object} dateObject, optional. Defaults to today.
	*
	* Creates a date string as YYYY-mm-dd format
	*/
// Prototypes works on iphone but NOT android!!!
//	Date.prototype.dateToYMD = function( dateObject ) {
	exports.dateToYMD = function( dateObject ) {
		if ( dateObject === undefined || dateObject === null ) dateObject = new Date();

		var d = dateObject.getDate();
		var m = dateObject.getMonth() + 1;
		var y = dateObject.getFullYear();
		return y +'-'+ ( m<=9?'0'+m:m ) +'-'+ (d<=9?'0'+d:d);
	};

	/**
	* @param {Object} none
	*
	* Creates a Unix Timestamp (seconds since Jan 1 1970)
	*/
	exports.unixTime = function() {
		return Math.round( +new Date() / 1000 );
	};




})();
