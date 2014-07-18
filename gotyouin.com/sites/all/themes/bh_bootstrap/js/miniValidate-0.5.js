/**
* @file miniValidate-0.5.js
*
* WARNING: This is a customized version of this ... do not update from DBS repo!
*
* A very quick and dirty and limited, and somewhat clumsy form validator. CSS
* needs to be handled separately. Handles inputs and textareas. Please use
* minified version. This should only be used in minimal situations. Use jQuery
* validate() elsewhere.
*
* Usage: Works by adding classes to form fields. Class names are like
* validateEmail, which does validation and makes it a required field.
* validateEmailOptional, validates, but its not a required field. May break if
* more than one of the predefined classes is used on the same form field.
*
* @hacked, bug fixed, modified and added to by Hal Burgiss  2013-02-19 for DBS
*/

/// ripped from: 
/*!Author: stian karlsen / http://stiankarlsen.me/super-simple-form-validation / v0.3*/

// ****************************************************************** //
// ****************************************************************** //
// @@ What: Super Simple Form Validation with jQuery               ** //
// @@ Who: Stian Karlsen                                           ** //
// @@ Version: 0.5                                                 ** //
// @@ License: None                                                ** //
// @@ Website: http://stiankarlsen.me/super-simple-form-validation ** //
// ****************************************************************** //
// ****************************************************************** //

/**
* @param callback, optional function to be invoked during submit().
*/
jQuery.fn.miniValidate = function( callback ) {
	"use strict";

	// clean it up:
	jQuery( "input" ).blur(  function() { 
		jQuery( this ).val( jQuery.trim( jQuery( this ).val() ) ); 
	});

	// custom for Got You In, create div for activity indicator / spinner1.gif
	// OSX issue: cache the opacity and displays on back button! 2013-02-26
	jQuery(window).bind( "unload", function() {
		jQuery( "div.region-content" ).css( { opacity : 1 } );
	});

	// Custom error messages ... keep short in case we are mobile.
	var emptyError  = "<p class='error-message empty-message'>Required</p>";
	var emailError  = "<p class='error-message email-message'>Invalid email</p>";
	var numberError = "<p class='error-message number-message'>Numbers only</p>";
	var currencyError = "<p class='error-message currency-message'>Numbers and decimal only</p>";
	var urlMessage    = "<p class='error-message url-message'>Invalid URL</p>";
	var textareaError  = "<p class='error-message empty-message'>Required</p>";
	var phoneError    = "<p class='error-message phone-message'>Invalid Phone Number</p>";
	var zipcodeError   = "<p class='error-message zipcode-message'>Invalid Zipcode</p>";

	// Regular expressions
	var emailRegEx  = /^([A-Za-z0-9_\-\.\+\!#=\$%\&])+\@([A-Za-z0-9_\-\.])+\.([A-Za-z]{2,4})$/;
	var numberRegEx = /^[0-9]+$/;
//	var urlRegEx    = /^(https?:\/\/)?([\da-z\.-]+)\.([a-z\.]{2,6})([\/\w \.-]*)*\/?$/;
	//modified to require some kind of minimal path. 2013-03-18
	var urlRegEx    = /^(https?:\/\/)?([\da-zA-Z\.-]+)\.([A-Za-z\.]{2,6})\/([A-Za-z0-9]+)/;

	// DBS added 2013-02-19 
	var zipcodeRegEx= /^[0-9]{5}$/;
	var phoneRegEx  = /^[0-9]{3}[- ]?[0-9]{3}[- ]?[0-9]{4}$/;
	var currencyRegEx = /^([0-9]+)\.?([0-9]{1,2})?$/;
				
	// Create variables from classnames
	// empty aka required field
	var vEmpty        = jQuery( ".validateEmpty" );

	var vZipcode      = jQuery( "input.validateZipcode" );
	var vZipcodeOpt   = jQuery( "input.validateZipcodeOptional" );

	var vPhone      = jQuery( "input.validatePhone" );
	var vPhoneOpt   = jQuery( "input.validatePhoneOptional" );

	var vEmail        = jQuery( "input.validateEmail" );
	var vEmailOpt     = jQuery( "input.validateEmailOptional" );
	
	var vNumber       = jQuery( "input.validateNumber" );
	var vNumberOpt    = jQuery( "input.validateNumberOptional" );
	
	var vCurrency       = jQuery( "input.validateCurrency" );
	var vCurrencyOpt    = jQuery( "input.validateCurrencyOptional" );

	var vUrl          = jQuery( "input.validateUrl" );
	var vUrlOpt       = jQuery( "input.validateUrlOptional" );
	
	var vTextarea     = jQuery( "textarea.validateTextarea" );
	var i;

	// handle the submit, check each field, stopping if we find a problem. Run callback, if we have one.
	jQuery( this ).submit( function() {	

		// just in case submit button is triggering other activity stuff.
		jQuery( "#busy" ).hide();
		jQuery( "div.region-content" ).css({ opacity: 1});

		// if we have a callback function, run it and return it if it evaluates to false.
		if ( typeof callback === 'function' ) {
			var _return = callback();
			if ( _return === false ) return false;
		}

		var isClean = true, focused = false;
		
		// note: this assumes *one* submit button. Counts hidden fields too.
		var form_length = jQuery( ":input" ).length - 1;

		jQuery( '.error-message' ).remove();

		/**
		* We have to loop through all fields, in order to see if there are
		* potentially multiples of the same type :( Inefficient, FIXME.
		*/
		for (i=0;i < form_length;i++) {
			
					//console.log( 1,vEmpty[i] );
			// ***************************************
			// VALIDATE REQUIRED FIELD FOR ANY INPUT First
			// ***************************************
			if ( jQuery( vEmpty[i] ).length ) {  // check if element exists
				// if it does
				//console.log( 2, vEmpty[i] );
				if ( jQuery( vEmpty[i] ).val() === '' ) {
				//console.log( 3, vEmpty[i] );
					jQuery( vEmpty[i] ).addClass( 'error' );
					jQuery( vEmpty[i] ).before( emptyError );
					if ( ! focused ) {
						jQuery( vEmpty[i] ).focus();
						focused = true;
					}
					isClean = false;
				} else {
				//console.log( 4, vEmpty[i] );
					jQuery( vEmpty[i] ).removeClass( 'error' ); 
//					jQuery( "p.empty-message" ).hide();
				}
			}
//		}
//		}

//		if ( isClean ) jQuery( "p.empty-message" ).hide();

		// note: this assumes *one* submit button.
//		var form_length = jQuery( ":input" ).length - 1 - vEmpty.length;


		// loop throught the other field possibilities
//		for (i=0;i < form_length ;i++) {

			// ***************************************
			// VALIDATE REQUIRED Phone Number
			// ***************************************
			if ( jQuery( vPhone[i] ).length ) {  // check if element exists
				// if it does
				if( !phoneRegEx.test( jQuery( vPhone[i]  ).val() ) ){
					jQuery( vPhone[i] ).addClass( 'error' );
					jQuery( vPhone[i] ).before( phoneError );
					if ( ! focused ) {
						jQuery( vPhone[i] ).focus();
						focused = true;
					}
					isClean = false;
				} else jQuery( vPhone[i] ).removeClass( 'error' );
//					jQuery( "p.phone-message" ).hide();
			}


				// ***************************************
				// VALIDATE OPTIONAL Phone Number
				// ***************************************
				if ( jQuery( vPhoneOpt[i] ).length ) { // check if element exists
					// if it does
					if ( jQuery( vPhoneOpt[i] ).val() === '' ) {
						// if it is empty, do nothing
					} else if( !phoneRegEx.test( jQuery( vPhoneOpt[i] ).val() ) ){
						jQuery( vPhoneOpt[i] ).addClass( 'error' );
						jQuery( vPhoneOpt[i] ).before( phoneError );
						if ( ! focused ) {
							jQuery( vPhoneOpt[i] ).focus();
							focused = true;
						}
						isClean = false;
					} else jQuery( vPhoneOpt[i] ).removeClass( 'error' );
//						jQuery( "p.phone-message" ).hide();
				}

			// ***************************************
			// VALIDATE REQUIRED EMAIL
			// ***************************************
			if ( jQuery( vEmail[i] ).length ) { // check if element exists
				// if it does
				if( !emailRegEx.test( jQuery( vEmail[i] ).val() ) ){
					jQuery( vEmail[i] ).addClass( 'error' );
					jQuery( vEmail[i] ).before( emailError );
					if ( ! focused ) {
						jQuery( vEmail[i] ).focus();
						focused = true;
					}
					isClean = false;
				} else jQuery( vEmail[i] ).removeClass( 'error' );
//					jQuery( "p.email-message" ).hide(); 
			}



				// ***************************************
				// VALIDATE OPTIONAL EMAIL
				// ***************************************
				if ( jQuery( vEmailOpt[i] ).length ) { // check if element exists
					// if it does
					if ( jQuery( vEmailOpt[i] ).val() === '' ) {
						// if it is empty, do nothing
					} else if( !emailRegEx.test( vEmailOpt.val() ) ){
						jQuery( vEmailOpt[i] ).addClass( 'error' );
						jQuery( vEmailOpt[i] ).before( emailError );
						if ( ! focused ) {
							jQuery( vEmptyOpt[i] ).focus();
							focused = true;
						}
						isClean = false;
					} else jQuery( vEmailOpt[i] ).removeClass( 'error' );
//						jQuery( "p.email-message" ).hide(); 
				}


			// ***************************************
			// VALIDATE REQUIRED Zipcode
			// ***************************************
			if ( jQuery( vZipcode[i] ).length ) { // check if element exists
				// if it does
				if( !zipcodeRegEx.test( jQuery( vZipcode[i] ).val() ) ){
					jQuery( vZipcode[i] ).addClass( 'error' );
					jQuery( vZipcode[i] ).before( zipcodeError );
					if ( ! focused ) {
						jQuery( vZipcode[i] ).focus();
						focused = true;
					}
					isClean = false;
				} else jQuery( vZipcode[i] ).removeClass( 'error' );
//					jQuery( "p.zipcode-message" ).hide(); 
			}

				// ***************************************
				// VALIDATE OPTIONAL Zipcode
				// ***************************************
				if ( jQuery( vZipcodeOpt[i] ).length ) { // check if element exists
					// if it does
					if ( jQuery( vZipcodeOpt[i] ).val() === '' ) {
						// if it is empty, do nothing
					} else if( !zipcodeRegEx.test( jQuery( vZipcodeOpt[i] ).val() ) ){
						jQuery( vZipcodeOpt[i] ).addClass( 'error' );
						jQuery( vZipcodeOpt[i] ).before( zipcodeError );
						if ( ! focused ) {
							jQuery( vZipcodeOpt[i] ).focus();
							focused = true;
						}
						isClean = false;
					} else jQuery( vZipcodeOpt[i] ).removeClass( 'error' );
//						jQuery( "p.zipcode-message" ).hide();
				}

			// ***************************************
			// VALIDATE REQUIRED NUMBER
			// ***************************************
			if ( jQuery( vNumber[i] ).length ) { // check if element exists

				// if it does
				if( !numberRegEx.test( jQuery( vNumber[i] ).val() ) ){
					jQuery( vNumber[i] ).addClass( 'error' );
					jQuery( vNumber[i] ).before( numberError );
					if ( ! focused ) {
						jQuery( vNumber[i] ).focus();
						focused = true;
					}
					isClean = false;
				} else jQuery( vNumber[i] ).removeClass( 'error' );
//					jQuery( "p.number-message" ).hide();
			}
			
				// ***************************************
				// VALIDATE OPTIONAL NUMBER
				// ***************************************
				if ( jQuery( vNumberOpt[i] ).length ) { // check if element exists
					// if it does
					if ( jQuery( vNumberOpt[i] ).val() === '' ) {
						// if it is empty, do nothing
					} else if( !numberRegEx.test( jQuery( vNumberOpt[i] ).val() ) ){
						jQuery( vNumberOpt[i] ).addClass( 'error' );
						jQuery( vNumberOpt[i] ).before( numberError );
						if ( ! focused ) {
							jQuery( vNumberOpt[i] ).focus();
							focused = true;
						}
						isClean = false;
					} else jQuery( vNumberOpt[i] ).removeClass( 'error' );
//						jQuery( "p.number-message" ).hide();
				}

			// ***************************************
			// VALIDATE REQUIRED Currency
			// ***************************************
			if ( jQuery( vCurrency[i] ).length ) { // check if element exists
				// if it does
				if( !currencyRegEx.test( jQuery( vCurrency[i] ).val() ) ){
					jQuery( vCurrency[i] ).addClass( 'error' );
					jQuery( vCurrency[i] ).before( currencyError );
					if ( ! focused ) {
						jQuery( vCurrency[i] ).focus();
						focused = true;
					}
					isClean = false;
				} else jQuery( vCurrency[i] ).removeClass( 'error' );
//					jQuery( "p.currency-message" ).hide();
			}
			
				// ***************************************
				// VALIDATE OPTIONAL Currency
				// ***************************************
				if ( jQuery( vCurrencyOpt[i] ).length ) { // check if element exists
					// if it does
					if ( jQuery( vCurrencyOpt[i] ).val() === '' ) {
						// if it is empty, do nothing
					} else if( !currencyRegEx.test( jQuery( vCurrencyOpt[i] ).val() ) ){
						jQuery( vCurrencyOpt[i] ).addClass( 'error' );
						jQuery( vCurrencyOpt[i] ).before( currencyError );
						if ( ! focused ) {
							jQuery( vCurrencyOpt[i] ).focus();
							focused = true;
						}
						isClean = false;
					} else jQuery( vCurrencyOpt[i] ).removeClass( 'error' );
//						jQuery( "p.currency-message" ).hide();
				}
			
			// ***************************************
			// URL - Validate URLs
			// ***************************************
			if ( jQuery( vUrl[i] ).length ) { // check if element exists
				// if it does
				if( !urlRegEx.test( jQuery( vUrl[i] ).val() ) ){
					jQuery( vUrl[i] ).addClass( 'error' );
					jQuery( vUrl[i] ).before( urlMessage );
					if ( ! focused ) {
						jQuery( vUrl[i] ).focus();
						focused = true;
					}
					isClean = false;
				} else jQuery( vUrl[i] ).removeClass( 'error' );
//					jQuery( "p.url-message" ).hide();
			}
			
				// ***************************************
				// VALIDATE OPTIONAL URL
				// ***************************************
				if ( jQuery( vUrlOpt[i] ).length ) { // check if element exists
					// if it does
					if ( jQuery( vUrlOpt[i] ).val() === '' ) {
						// if it is empty, do nothing
					} else if( !urlRegEx.test( jQuery( vUrlOpt[i] ).val() ) ){
						jQuery( vUrlOpt[i] ).addClass( 'error' );
						jQuery( vUrlOpt[i] ).before( urlMessage );
						if ( ! focused ) {
							jQuery( vUrlOpt[i] ).focus();
							focused = true;
						}
						isClean = false;
					} else jQuery( vUrlOpt[i] ).removeClass( 'error' );
//						jQuery( "p.email-message" ).hide();
				}
				
			// ***************************************
			// VALIDATE TEXTAREA
			// ***************************************
			if ( jQuery( vTextarea[i] ).length ) { // check if element exists
				// if it does
				if ( jQuery( vTextarea[i] ).val() ==='' ) {
					jQuery( vTextarea[i] ).addClass( 'error' );
					jQuery( vTextarea[i] ).before( textareaError );
					if ( ! focused ) {
						jQuery( vTextarea[i] ).focus();
						focused = true;
					}
					isClean = false;
				} else jQuery( vTextarea[i] ).removeClass( 'error' ); 
//					jQuery( "p.empty-message" ).hide();
			}

		} // end for

		if ( isClean ) {
			//custom stuff for Got You In 2013-02-24, show activity indicator.
			// Great idea but does not show on iphone !!!! 2013-02-26, neither does animated gif.
			jQuery( '#busy' ).show();
			jQuery( "div.region-content" ).css( { opacity : .4 } );
		} else {
			jQuery( '#busy' ).hide();
			jQuery( "div.region-content" ).css( { opacity : 1 } );
		}

		// good to go or not?
		return isClean;
	
	} ); // end of submit event

}; // end of miniValidate function

