(function ($) {

  Drupal.behaviors.dbs_booking = {

    attach: function(context, settings) {

    /*-------------------------------
          CUSTOM JAVASCRIPT
    -------------------------------*/

      $('#edit-submit-button, #dbs-booking-form .form-item-field-services, #dbs-booking-form .form-item-field-date, #btn-generate-times, #dbs-booking-form .form-item-field-recurring,  #dbs-booking-form .form-item-field-recurring-mult').hide();

      $('#dbs-booking-form .form-item-field-recurring input').change(function(){

        if( $(this).val() == 'Yes' ) { $('#dbs-booking-form .form-item-field-recurring-mult').show(); } else { $('#dbs-booking-form .form-item-field-recurring-mult').hide(); }
 
      });

      /*-----------------------------
            BARBER DROPDOWN
      -----------------------------*/

      $('#edit-field-barber').change(function(){

        if( $(this).val() != 0 ) // if a Barber is selected in the Dropdown
        {

          // get a fresh start
          $('#new-app-services').remove();
          $('#dbs-booking-form .form-item-field-services').after('<div id="new-app-services"></div>'); 
          var totalTime = 0;
          var totalPrice = 0;

          // AJAX: Generate the services table based on the barber selected
          $.get('/sites/all/modules/dbs_booking/ajax/services.php?result=table&barber_id=' + $('#edit-field-barber').val(), function(data) {
            $('#new-app-services').append(data);


          /*##################################################################################################################################################
              WARNING: Duplicate code.  I had to include this code in two places to work with tables loaded on page load AND tables generated by AJAX.
                                        Both tables are identical, and work the same way.  However, the same code exists in two places in this file
                                        Any changes made to one instance, should be copied to the other
          ##################################################################################################################################################*/

              $('#dbs-booking-form tbody .service_select .btn').on("click", function(event){

                var sid = $(this).parent().parent().attr('class').replace('service-','');
                var totalPrice = parseInt( $('#dbs-booking-form tfoot .service_price span').html() );
                var totalTime = parseInt( $('#dbs-booking-form tfoot .service_time span').html() );



                if( $(this).hasClass('btn-primary') )
                {

                  $(this).removeClass('btn-primary');
                  $(this).addClass('btn-success');
                  $(this).html('Selected');
                  $('#edit-field-services-' + sid).attr('checked', true);
                  totalPrice = totalPrice + parseInt( $(this).parent().parent().find('.service_price').html().replace('$','') );
                  totalTime = totalTime + parseInt( $(this).parent().parent().find('.service_time span').html() );

                } else {

                  $(this).removeClass('btn-success');
                  $(this).addClass('btn-primary');
                  $(this).html('Add');
                  $('#edit-field-services-' + sid).attr('checked', false);
                  totalPrice = totalPrice - parseInt( $(this).parent().parent().find('.service_price').html().replace('$','') );
                  totalTime = totalTime - parseInt( $(this).parent().parent().find('.service_time span').html() );

                }

                $('#dbs-booking-form tfoot .service_price span').html(totalPrice);
                $('#dbs-booking-form tfoot .service_time span').html(totalTime);
                if(totalTime > 0) { $('#dbs-booking-form .form-item-field-date, #btn-generate-times,  #dbs-booking-form .form-item-field-recurring,  #dbs-booking-form').show(); } else { $('#dbs-booking-form .form-item-field-date, #btn-generate-times,  #dbs-booking-form .form-item-field-recurring,  #dbs-booking-form .form-item-field-recurring-mult').hide(); } 

                return false;

              });

              $('#dbs-booking-form .service_select .btn-danger').on("click", function(event){

                $('#dbs-booking-form .form-item-field-services .checkbox input').each(function(){

                  $(this).attr('checked',false);

                });

                $('#dbs-booking-form tbody .service_select .btn-success').each(function(){

                  $(this).removeClass('btn-success');
                  $(this).addClass('btn-primary');
                  $(this).html('Add');

                });

                $('#dbs-booking-form tfoot .service_price span').html('0');
                $('#dbs-booking-form tfoot .service_time span').html('0');
                $('#dbs-booking-form .form-item-field-date, #btn-generate-times').hide();

                return false;

              });

          /*##################################################################################################################################################
              END OF DUPLICATE CODE (FIRST INSTANCE)
          ##################################################################################################################################################*/

          });

          // AJAX: To generate checkboxes needed for form to function
          $.get('/sites/all/modules/dbs_booking/ajax/services.php?result=options&barber_id=' + $('#edit-field-barber').val(), function(data) {
            $('#dbs-booking-form .form-item-field-services .controls').append(data);
          });


        } else {

          $('#new-app-services').remove();

        }

        $('#dbs-booking-form .form-item-field-date, #btn-generate-times').hide();

      });

      /*##################################################################################################################################################
          START OF DUPLICATE CODE (SECOND INSTANCE)
      ##################################################################################################################################################*/



      $('#dbs-booking-form tbody .service_select .btn').on("click", function(event){

        var sid = $(this).parent().parent().attr('class').replace('service-','');
        var totalPrice = parseFloat( $('#dbs-booking-form tfoot .service_price span').html() );
        var totalTime = parseFloat( $('#dbs-booking-form tfoot .service_time span').html() );



        if( $(this).hasClass('btn-primary') )
        {

          $(this).removeClass('btn-primary');
          $(this).addClass('btn-success');
          $(this).html('Selected');
          $('#edit-field-services-' + sid).attr('checked', true);
          totalPrice = totalPrice + parseFloat( $(this).parent().parent().find('.service_price').html().replace('$','') );
          totalTime = totalTime + parseFloat( $(this).parent().parent().find('.service_time span').html() );

        } else {

          $(this).removeClass('btn-success');
          $(this).addClass('btn-primary');
          $(this).html('Add');
          $('#edit-field-services-' + sid).attr('checked', false);
          totalPrice = totalPrice - parseFloat( $(this).parent().parent().find('.service_price').html().replace('$','') );
          totalTime = totalTime - parseFloat( $(this).parent().parent().find('.service_time span').html() );

        }

        totalPrice = totalPrice.toString();
        if(totalPrice.length == 4) { totalPrice = totalPrice + '0'; }

        $('#dbs-booking-form tfoot .service_price span').html(totalPrice);
        $('#dbs-booking-form tfoot .service_time span').html(totalTime);

        if(totalTime > 0) { $('#dbs-booking-form .form-item-field-date, #btn-generate-times,  #dbs-booking-form .form-item-field-recurring,  #dbs-booking-form').show(); } else { $('#dbs-booking-form .form-item-field-date, #btn-generate-times,  #dbs-booking-form .form-item-field-recurring,  #dbs-booking-form .form-item-field-recurring-mult').hide(); } 

        return false;

      });

      $('#dbs-booking-form .service_select .btn-danger').on("click", function(event){

        $('#dbs-booking-form .form-item-field-services .checkbox input').each(function(){

          $(this).attr('checked',false);

        });

        $('#dbs-booking-form tbody .service_select .btn-success').each(function(){

          $(this).removeClass('btn-success');
          $(this).addClass('btn-primary');
          $(this).html('Add');

        });

        $('#dbs-booking-form tfoot .service_price span').html('0');
        $('#dbs-booking-form tfoot .service_time span').html('0');
        $('#dbs-booking-form .form-item-field-date, #btn-generate-times').hide();

        return false;

      });

      /*##################################################################################################################################################
          END OF DUPLICATE CODE (SECOND INSTANCE)
      ##################################################################################################################################################*/

      /*--------------------------------
                FIELD: DATE
      --------------------------------*/

      $('.page-new-appointment #edit-field-date').hide();

      var maxDate = new Date();
      maxDate.setMonth(maxDate.getMonth() + 3);

      $('.page-new-appointment #edit-field-date').mobiscroll().date({
          theme: 'default',
          display: 'inline',
          mode: 'scroller',
          dateOrder: 'mmddyyyy',
          minDate: new Date(),
          maxDate: maxDate
      });    

      $('#btn-generate-times').on("click", function(){

          if( $('input[name=field_barber]').length > 0 )
          {
            var barber_id = $('input[name=field_barber]').val();
          } else {
            var barber_id = $('select[name=field_barber]').val();
          }
          $.get('/sites/all/modules/dbs_booking/ajax/available-times.php?date=' + $('#edit-field-date').val() + '&barber_id=' + barber_id + '&duration=' + $('tfoot .service_time span').html(), function(data) {
   

          $('#available-times').html(data);

            $('#available-times button').on("click", function(){
              $('input[name=field_time]').attr('value', $(this).find('.time').html() );
              if ( !window.confirm( "Are you sure you want to schedule an appointment for " + $(this).find('.text').html() + '?' ) ) { return false; }
              $('#dbs-booking-form').submit();
              return false;

            });


          });

        return false;

      });

    /* End custom js */

    }

  }

})(jQuery);
