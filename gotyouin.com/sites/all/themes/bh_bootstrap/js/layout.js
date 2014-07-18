(function ($) {

  Drupal.behaviors.bh_bootstrap = {

    attach: function(context, settings) {



      $('.page-user-customer .views-field.views-field-nid a').click(function(){

        var link = $(this);

        if( $(this).find('.icon-trash').attr('id') == '' )
        {

          var x=confirm("Would you really like to cancel this appointment? This will email the barber notifying them of your request.")
          if (x==true)
          {
            $.get( $(this).attr('href') );
          }    

        } else {

          $( "#dialog" ).dialog({
            resizable: false,
            height:300,
            modal: true,
            buttons: {
              "Cancel all appointments": function() {
                $( this ).dialog( "close" );
                var x=confirm("Would you really like to cancel ALL of these appointments? This will notify the barber of your request -- they will delete the appointment from their schedule.")
                if (x==true)
                {
                  $.get( link.attr('href') + '&rDelete=TRUE' );
                }    
              },
              "Only this appointment": function() {
                $( this ).dialog( "close" );
                var x=confirm("Would you really like to cancel this appointment? This will notify the barber of your request -- they will delete the appointment from their schedule")
                if (x==true)
                {
//                  $.get( $(this).attr('href') );
                  $.get( link.attr('href') );
                }    
              },
              Cancel: function() {
                $( this ).dialog( "close" );
              }
            }
          });

        }

        return false;


      });

      $('.page-node-add-booking #edit-field-recurring-multiplier, .page-node-add-booking #edit-field-email-time-access').hide();

      $('.page-user-edit .tabs .nav-pills li a').each(function(){

        if( $(this).html() == 'Account<span class="element-invisible">(active tab)</span>' ) { $(this).html('User Info<span class="element-invisible">(active tab)</span>');  }
        if( $(this).html() == 'Account' ) { $(this).html('User Info');  }
        if( $(this).html() == 'Barber<span class="element-invisible">(active tab)</span>' ) { $(this).html('Barber Info<span class="element-invisible">(active tab)</span>');  }
        if( $(this).html() == 'Barber' ) { $(this).html('Barber Info');  }
        if( $(this).html() == 'Shop Owner<span class="element-invisible">(active tab)</span>' ) { $(this).html('Shop Info<span class="element-invisible">(active tab)</span>');  }
        if( $(this).html() == 'Shop Owner' ) { $(this).html('Shop Info');  }
        if( $(this).html() == 'Independent Barber<span class="element-invisible">(active tab)</span>' ) { $(this).html('Barber Info<span class="element-invisible">(active tab)</span>');  }
        if( $(this).html() == 'Independent Barber' ) { $(this).html('Barber Info');  }

      });

      $('.page-user-edit .tabs .primary li a').each(function(){

        if( $(this).html() == 'View' ) { $(this).html('View Profile'); }

      });

      $('.page-node-edit.node-type-booking .field-name-field-barber, .page-node-edit.node-type-booking  #edit-field-recurring-multiplier, .page-node-edit.node-type-booking #edit-field-email-time-access,  .page-node-edit.node-type-booking #edit-field-recurring-token').hide();

      $('.page-user-barbers td.views-field-field-barber-enabled').each(function(){

        switch ( parseInt($(this).html()) )
        {
          case 1:
            $(this).html('Yes');
          break;
          case 0:
            $(this).html('No');
          break;
        }      

      });

      $('#block-views-barbers-block-1 .views-row').click(function(){

	//	showBusy();
        var url = $(this).find('.views-field-uid a').attr('href');
        url = url.replace('/new-appointment?barber_id=','');
        window.location = '/user/' + url;


      });
        
      $('.form-item-field-location-und-0-delete-location').hide();

      $('.views-table').addClass('table');

      $('.hours a').click(function(){

        $('.hours .hours-full').toggle();
        return false;

      });

      var maxDate = new Date(); 
      maxDate.setMonth(maxDate.getMonth() + 4);

      $('label[for=edit-field-date-value], #edit-field-date-value').hide();

      $('body.page-user-customer #edit-field-date-value, body.page-user-appointments #edit-field-date-value, body.page-user-shop-appointments #edit-field-date-value').mobiscroll().date({
          theme: 'default',
          display: 'inline',
          mode: 'scroller',
          dateOrder: 'mmddyyyy',
          dateFormat: 'yy-mm-dd',
          minDate: new Date(),
          maxDate: maxDate
      });

      $('.page-node-add-booking .form-item-field-customer-und,.page-node-add-booking .form-item-field-barber-und,.page-node-add-booking  .vertical-tabs').hide();
      
      $('#views-exposed-form-appointments-page .views-table').addClass('table-striped');

    /*##################################################################################################################################################
        SEARCHES: BARBERS & SHOPS
    ##################################################################################################################################################*/


    $('.page-search-barbershops #search-view-map').click(function(){

      var url = window.location.href;
      url = url.replace('barbershops','barbershops/map');
      window.location = url;
      return false;

    });

    $('.page-search-barbers #search-view-map').click(function(){

      var url = window.location.href;
      url = url.replace('barbers','barbers/map');
      window.location = url;
      return false;

    });

    if( !($('body').hasClass('page-search-barbers-map')) )
    {
      $('.page-search-barbershops .view-header, .page-search-barbers .view-header').prepend('<div id="search-zip-name"><label for="zip_name">Search by Name or Zip</label><input type="text" name="zip_name" /><button>Submit</button></div>');
    }

    $('input[name=zip_name]').on("focus", function(event){

      $('label[for=zip_name]').hide();

    });

    $('label[for=zip_name]').on("click", function(event){

      $('label[for=zip_name]').hide();
      $('input[name=zip_name]').focus();

    });

    $('#search-zip-name button').on("click", function(event){

      $('views-exposed-form-barbers-page').submit();

    });

    $('input[name=zip_name]').on("blur", function(event){

      if($(this).val() != '') { return false; }
      $('label[for=zip_name]').show();

    });


	if ( $( "body" ).hasClass( "page-search" ) ) {

	    // set search field value
         var thisValue; 
         var shopURL = $( 'div.view-header ul.menu-search li a[href="/search/barbershops"]' ) ;
         var barberURL = $( 'div.view-header ul.menu-search li a[href="/search/barbers"]' ) ;
	    
	    if( $('#edit-distance-postal-code').val() != '' )
	    {
	      // for zipcode search, we make sure if they click the other tab it prepopulates.
		 thisValue = $('#edit-distance-postal-code').val();
		 $('input[name=zip_name]').val( thisValue );
		 $('label[for=zip_name]').hide();

			 $( function() {
				$( shopURL ).attr( 'href' , '/search/barbershops' + location.search );
				$( barberURL ).attr( 'href' , '/search/barbers' + location.search );
			});

		// FIXME: there is no #edit-title on barber search / zipcode page.
	    } else if ( $('#edit-title').val() != '' ) {

		if ( location.pathname == '/search/barbers' ) {
			 thisValue =  $('#edit-field-last-name-value').val();
		} else {
			 thisValue =  $('#edit-title').val();
		}
		 $('input[name=zip_name]').val( thisValue );
		 $('label[for=zip_name]').hide();
			 
		if ( location.search.length > 0 ) {
			 $( function() {
				if ( ! location.search.match(/title=/) ) {
					$( shopURL ).attr( 'href' , encodeURI( '/search/barbershops' + location.search + '&title=' + thisValue ) );
				}
                    if ( ! location.search.match(/field_first_name_value/) ) {
					$( barberURL ).attr( 'href' , encodeURI( '/search/barbers' + location.search + '&field_first_name_value=' + thisValue ) );
				}
			});
		}
	    }
	}

    // do the search
    $('#search-zip-name button').on("click", function(event){

	// activity indicators
	$('div.region-content').css( { opacity : .4 } );
	$('#busy').show();

      $('#edit-distance-postal-code').val( null );
      $('#edit-title').val( null );
      $('#edit-field-display-name-value').val( null );
      $('#edit-field-first-name-value').val( null );
      $('#edit-field-last-name-value').val( null );

      if( $('input[name=zip_name]').val() != '')
      {

        var value = Number( $('input[name=zip_name]').val() );
        if(Math.floor(value) == value)
        {

          // value is integer, search by zip
          $('#edit-distance-postal-code').val( $('input[name=zip_name]').val() );

        } else {

          // value is text, search by name
          $('#edit-title, #edit-field-display-name-value, #edit-field-first-name-value, #edit-field-last-name-value ').val( $('input[name=zip_name]').val() );

        }

      }

      $('#views-exposed-form-shops-page, #views-exposed-form-barbers-page').submit();

    });



    $('.page-search-barbershops .views-field-street span.distance, .page-search-barbers .views-field-street span.distance').each(function(){

      if( $(this).html() == '')
      {

        $('.page-search-barbershops .views-field-street span.sep, .page-search-barbers .views-field-street span.sep').hide();

      }

    });

    $('.page-search-barbers .views-field-field-display-name').each(function(){

      if($(this).find('h2').html() == '')
      {

        $(this).hide();

      } else {

        $(this).parent().find('.views-field-field-last-name').hide();

      }

    });

    $('.page-search-barbers .views-field-type').each(function(){

      if( $(this).find('.field-content').html() == 'Barber' )
      {

        $(this).hide();

      }

    });

    }

  }

	$( function() {

		// iOS issue: caches the opacity and displays #busy div on back button! 2013-02-26 This fixes it.
		$(window).bind( "load unload pageshow", function() {
			$( "div.region-content" ).css( { opacity : 1 } );
			$( "#busy" ).hide();
		});

		// activity indicators on search, etc. 2013-03-03
		$( 'ul.menu-search li a, a#search-view-map, div.pagination div.item-list li a,  div.views-field-field-last-name a, div.views-row, .btn-schedule a, ul.nav-tabs a, a[href="/register-now"], ul#signup-list a, #edit-submit' ).on( "click", function(e) {
			// there is ajax on the barber rating click ... force hiding the busy indicator. Make exception.
			if ( $( e.target ).parent().hasClass( 'star' )  ) {
				hideBusy();
			} else {
				showBusy();
			}
		});

		// stop the annoying prompt to save passwords on mobile. 2013-03-05
		if ( is_mobile_app == 1 ) {
			$( "form" ).attr( "autocomplete", "off" );
		}
		// remove cancel owner button
		$( ".page-user-edit #user-profile-form #edit-cancel").remove();
		// don't show status / enabled radios
		if ( ! user.is_admin ) {
			$( "form div.form-item-status" ).remove();
		}
		// no need
		$( "div.form-item-htmlmail-plaintext" ).hide();

/*
		$( "div#available-times button" ).click( function() {
			if ( ! confirm( "Book an appointment now for this time?" ) ) {
				return false;
			}
		})
*/
	});

	// show the hidden spinner / activity indicator
	function showBusy() {
		$( "div.region-content" ).css( { opacity : .4 } );
		$( "#busy" ).show();
	}

	// hide the spinner / activity indicator 2013-03-08
	function hideBusy() {
		$( "div.region-content" ).css( { opacity : 1 } );
		$( "#busy" ).hide();
	}

})(jQuery);
