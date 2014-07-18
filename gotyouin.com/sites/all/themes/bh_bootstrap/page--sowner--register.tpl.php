<?php

/*
 * Custom registration logic for Shop Owners to direct them to create a shop
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

<div class="container">
     <?php /*
       <?php if ($breadcrumb): ?>
       <?php print $breadcrumb; ?>
       <?php endif; ?>
      */ ?>

     <?php if (arg(0) == 'shops'): ?>
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
               <?php if ($title && !$page['highlighted']): ?>
                    <h1 class="title" id="page-title"><?php print $title; ?></h1>
               <?php elseif ($title && $page['highlighted']): ?>
                    <h2 class="title" id="page-title"><?php print $title; ?></h2>
               <?php endif; ?>
               <?php print render($title_suffix); ?>
               <?php if ($tabs['#primary'] != ''): ?><div class="tabs"><?php print render($tabs); ?></div><?php endif; ?>
               <?php print render($page['help']); ?>
               <?php if ($action_links): ?><ul class="action-links"><?php print render($action_links); ?></ul><?php endif; ?>

     <?php print render($page['content']);
     //print ("Here's where the magic needs to happen");
     ?>
               <input id="hideme" name="hideme" type="hidden" value="" />
               <div id='signup-trial'>Sign up for  your 30 day free trial account!
               <a href="/mobile-pricing">(see pricing)</a></div>
               <div id='step-1'>
                    <h3>Shop Owners and Barber School Administrators</h3>
                    <h3>Step #1 - create your Shop Owner's account</h3>
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
<?php //if ($page['row_post_content']): print render($page['row_post_content']); endif;  ?>
<?php include('footer.php'); ?>

<!--Some necessary jQuery machinations-->
<script type="text/javascript" src='/<?php echo path_to_theme(); ?>/js/miniValidate-0.5.min.js'></script>
<script type="text/JavaScript">
     jQuery(function() {
          jQuery ('#step-1').prependTo('#user-register-form');
          jQuery ('#signup-trial').prependTo('#user-register-form');
          jQuery ('#hideme').prependTo('#user-register-form');
          //jQuery ('#step-2').appendTo('#user-register-form');
          //jQuery ('#shop-finder').insertBefore('#edit-actions');
          jQuery ('#edit-profile-shop-owner-field-complementary').hide();
          jQuery ('#edit-profile-shop-owner-field-shop').hide();
          jQuery ('#edit-profile-shop-owner-field-your-shop-id-is').hide();
          jQuery ('#edit-field-first-name').insertBefore('.form-item-name');
          jQuery ('#edit-field-last-name').insertBefore('.form-item-name');
          jQuery ('#edit-field-phone').insertBefore('.form-item-name');
          jQuery ('#edit-profile-shop-owner-field-bio').insertBefore('#edit-actions');
          jQuery ('#edit-picture').insertBefore('#edit-actions');

		// set up validation 2013-02-22
		jQuery('#edit-pass-pass2, #edit-pass-pass1, #edit-name, #edit-field-first-name-und-0-value, #edit-field-last-name-und-0-value').addClass( 'validateEmpty' );
		jQuery('#edit-field-phone-und-0-value').addClass( 'validatePhone' );
		jQuery('#edit-mail').addClass( 'validateEmail' );

		jQuery( "form#user-register-form" ).miniValidate();
		
    });
</script>
