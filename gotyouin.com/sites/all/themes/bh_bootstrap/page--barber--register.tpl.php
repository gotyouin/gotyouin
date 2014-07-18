<?php
/**
 * Started with default page.tpl.php and made customizations
 * @file
 */
?>

<?php 
/**
 * Let's intercept any spambot submissions using the old blank field trick.
 */
if ($_POST['hideme'] != '') {
    //echo "Nice try, Spammy McSpammerson.";
    die();
}
?>

<?php include('header.php'); ?>
<!--<script type="text/javascript" src="http://ajax.microsoft.com/ajax/jquery.validate/1.5.5/jquery.validate.min.js"></script>-->

<?php 
//we will need this information about shop ids later for form validation
$query = new EntityFieldQuery();
$query->entityCondition('entity_type', 'node')
        ->entityCondition('bundle', 'shop');
        //->fieldCondition('field_date', 'value', '2013-04-13 00:00:00', '<=');
$result = $query->execute();
$keys = array_keys($result['node']);
$keylist = implode(',',$keys);
$keylist = ','.$keylist.',';
?>

<div class="container">
  <?php /*
  <?php if ($breadcrumb): ?>
  <?php print $breadcrumb; ?>
  <?php endif; ?>
  */ ?>

  <?php if( arg(0) == 'shops'): ?>
  <?php endif; ?>

  <?php print $messages; ?>
  <?php if ($page['highlighted']): ?>
  <?php print render($page['highlighted']); ?>
  <?php endif; ?>
  <div class="row">
    <?php if ($page['sidebar_first']): ?>
    <div class="span<?php print $sidebar_first_size; ?>">
      <?php print render($page['sidebar_first']); ?>
    </div>
    <?php endif; ?>  
    <div class="span<?php print $content_size; ?>">
      <?php print render($title_prefix); ?>
      <?php if ($title && !$page['highlighted'] && arg(0) != 'user'): ?>
      <h1 class="title" id="page-title"><?php print $title; ?></h1>
      <?php elseif ($title && $page['highlighted'] ): ?>
      <h2 class="title" id="page-title"><?php print $title; ?></h2>
      <?php endif; ?>
      <?php print render($title_suffix); ?>
      <?php if ($tabs['#primary'] != ''): ?><div class="tabs"><?php print render($tabs); ?></div><?php endif; ?>
      <?php print render($page['help']); ?>
      <?php if ($action_links): ?><ul class="action-links"><?php print render($action_links); ?></ul><?php endif; ?>
      <?php print render($page['content']); ?>
      <input id="hideme" name="hideme" type="hidden" value="" />
     <div id='step-1'>
          <h3>Step #1 - create your Barber's account</h3>
          <p>You will need the Shop ID of the shop where you work</p>
     </div>
      
      <div class="field-type-entityreference field-name-field-shop field-widget-options-select form-wrapper" id="edit-enter-barber-field-shop"><div class="form-item control-group form-type-select form-item-profile-barber-field-shop-und">
                <label for="edit-enter-barber-field-shop" class="control-label">Shop ID <span class="form-required" title="This field is required.">*</span></label>
                <div class="controls">
                     <input class="text-full form-text required" type="text" id="edit-enter-barber-field-shop" />
                </div>
           </div>
      </div>

      <?php print $feed_icons; ?>      
    </div> 
    <?php if ($page['sidebar_second']): ?>
    <div class="span<?php print $sidebar_second_size; ?>">
      <?php print render($page['sidebar_second']); ?>
    </div>
    <?php endif; ?>
  </div><!-- .row -->
</div>  
<?php //if ($page['row_post_content']): print render($page['row_post_content']); endif; ?>
<?php include('footer.php'); ?>

<script type="text/javascript" src='/<?php echo path_to_theme(); ?>/js/miniValidate-0.5.min.js'></script>
<script>
     jQuery(function() {
          //.prependTo('#user-register-form');
          //jQuery ('#step-2').appendTo('#user-register-form');
          //jQuery ('#shop-finder').insertBefore('#edit-actions');
          jQuery ('#hideme').prependTo('#user-register-form');
          jQuery ('#edit-profile-barber-field-shop').hide();
          jQuery ('#edit-profile-barber-field-shop-und').hide();
          jQuery ('#edit-profile-barber-field-services').hide();
          jQuery ('#edit-profile-barber-field-services-term').hide();
          jQuery ('#edit-enter-barber-field-shop').prependTo('#user-register-form');
          jQuery ('#step-1').prependTo('#user-register-form');
          //jQuery ("label[for='edit-title']:contains('Shop Name')").hide();
          jQuery ('#edit-field-first-name').insertBefore('.form-item-name');
          jQuery ('#edit-field-last-name').insertBefore('.form-item-name');
          jQuery ('#edit-field-phone').insertBefore('.form-item-name');
          jQuery ('#edit-profile-barber-field-bio').insertBefore('#edit-actions');
          //jQuery ('#edit-profile-barber-field-services').insertBefore('#edit-actions');
          jQuery ('#edit-profile-barber-field-barber-availability').insertBefore('#edit-actions');
          jQuery ('#edit-picture').insertBefore('#edit-actions');
          
          jQuery ('#edit-enter-barber-field-shop').change(function() {
               jQuery('#edit-profile-barber-field-shop').val(jQuery(this).val());
          });
		
		jQuery( '#profile-barber-field-facebook-add-more-wrapper' ).appendTo( '#profile-barber-field-bio-add-more-wrapper' );
		jQuery( '#profile-barber-field-google-plus-add-more-wrapper' ).appendTo( '#profile-barber-field-bio-add-more-wrapper' );
		jQuery( '#profile-barber-field-linkedin-add-more-wrapper' ).appendTo( '#profile-barber-field-bio-add-more-wrapper' );
		jQuery( '#profile-barber-field-twitter-handle-add-more-wrapper' ).appendTo( '#profile-barber-field-bio-add-more-wrapper' );
		jQuery( '#profile-barber-field-instagram-add-more-wrapper' ).appendTo( '#profile-barber-field-bio-add-more-wrapper' );
          
          jQuery ('#user-register-form').submit(function() {
               //alert("at least it's getting this far");
               var json = '<?php echo $keylist; ?>';
               //alert (json);
               //jQuery ('input#edit-enter-barber-field-shop').css("background-color","red");
               var shopselected = jQuery('input#edit-enter-barber-field-shop').val();
               
               shopsearch = ','+shopselected+',';
               
               if (json.indexOf(shopsearch) == -1) {
				// turn off loading indicator.
				jQuery( "#busy" ).hide();
				jQuery( ".region-content" ).css({ opacity: 1});
                    alert ("You need to enter a valid Shop ID. Ask the owner of your shop for assistance.");
                    jQuery ('input#edit-enter-barber-field-shop').css("background-color","red");
                    jQuery ('input#edit-enter-barber-field-shop').focus();
                    return false;
               } else {
                    jQuery('select#edit-profile-barber-field-shop-und').prop('value', shopselected);
                    return true;
               }
               //need to reject form if shop id is not valid
          });
		
		// set up validation 2013-02-22
		jQuery('#edit-enter-barber-field-shop').addClass( 'validateNumber' );
		jQuery('#edit-pass-pass2, #edit-pass-pass1, #edit-name, #edit-field-first-name-und-0-value, #edit-field-last-name-und-0-value').addClass( 'validateEmpty' );
		jQuery('#edit-field-phone-und-0-value').addClass( 'validatePhone' );
		jQuery('#edit-mail').addClass( 'validateEmail' );
		jQuery( '#edit-profile-independent-barber-field-ifacebook-und-0-value, #edit-profile-barber-field-facebook-und-0-value' ).addClass( 'validateUrlOptional' );
		jQuery( '#edit-profile-independent-barber-field-linkedini-und-0-value, #edit-profile-barber-field-linkedin-und-0-value' ).addClass( 'validateUrlOptional' );
		jQuery( '#edit-profile-independent-barber-field-google-plusi-und-0-value, #edit-profile-barber-field-google-plus-und-0-value' ).addClass( 'validateUrlOptional' );

		jQuery( "form#user-register-form" ).miniValidate();
    });     
</script>
