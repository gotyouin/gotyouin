<?php
//die('hal shop node is here');
/**
 * @file
 * Default theme implementation to display a single Drupal page.
 *
 * Available variables:
 *
 * General utility variables:
 * - $base_path: The base URL path of the Drupal installation. At the very
 *   least, this will always default to /.
 * - $directory: The directory the template is located in, e.g. modules/system
 *   or themes/bartik.
 * - $is_front: TRUE if the current page is the front page.
 * - $logged_in: TRUE if the user is registered and signed in.
 * - $is_admin: TRUE if the user has permission to access administration pages.
 *
 * Site identity:
 * - $front_page: The URL of the front page. Use this instead of $base_path,
 *   when linking to the front page. This includes the language domain or
 *   prefix.
 * - $logo: The path to the logo image, as defined in theme configuration.
 * - $site_name: The name of the site, empty when display has been disabled
 *   in theme settings.
 * - $site_slogan: The slogan of the site, empty when display has been disabled
 *   in theme settings.
 *
 * Navigation:
 * - $main_menu (array): An array containing the Main menu links for the
 *   site, if they have been configured.
 * - $secondary_menu (array): An array containing the Secondary menu links for
 *   the site, if they have been configured.
 * - $breadcrumb: The breadcrumb trail for the current page.
 *
 * Page content (in order of occurrence in the default page.tpl.php):
 * - $title_prefix (array): An array containing additional output populated by
 *   modules, intended to be displayed in front of the main title tag that
 *   appears in the template.
 * - $title: The page title, for use in the actual HTML content.
 * - $title_suffix (array): An array containing additional output populated by
 *   modules, intended to be displayed after the main title tag that appears in
 *   the template.
 * - $messages: HTML for status and error messages. Should be displayed
 *   prominently.
 * - $tabs (array): Tabs linking to any sub-pages beneath the current page
 *   (e.g., the view and edit tabs when displaying a node).
 * - $action_links (array): Actions local to the page, such as 'Add menu' on the
 *   menu administration interface.
 * - $feed_icons: A string of all feed icons for the current page.
 * - $node: The node object, if there is an automatically-loaded node
 *   associated with the page, and the node ID is the second argument
 *   in the page's path (e.g. node/12345 and node/12345/revisions, but not
 *   comment/reply/12345).
 *
 * Regions:
 * - $page['help']: Dynamic help text, mostly for admin pages.
 * - $page['highlighted']: Items for the highlighted content region.
 * - $page['content']: The main content of the current page.
 * - $page['sidebar_first']: Items for the first sidebar.
 * - $page['sidebar_second']: Items for the second sidebar.
 * - $page['header']: Items for the header region.
 * - $page['footer']: Items for the footer region.
 *
 * @see template_preprocess()
 * @see template_preprocess_page()
 * @see template_process()
 */
?>

<?php include('header.php'); ?>
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

<script>
	var uid = <?php echo $user->uid;?>;
	var themeFolder = '/<?php echo path_to_theme(); ?>';
</script>
<script type="text/javascript"  src='/<?php echo path_to_theme(); ?>/js/miniValidate-0.5.min.js'></script>
<script type='text/javascript'>
     jQuery(function() {    

		//.prependTo('#user-register-form');
          //jQuery ('#step-2').appendTo('#user-register-form');
          //jQuery ('#shop-finder').insertBefore('#edit-actions');
          //jQuery ("label[for='edit-title']:contains('Shop Name')").hide();
          jQuery ("label[for='edit-title']:contains('Shop Name')").text('Shop or School Name');
          jQuery ('#edit-field-shop-enabled').hide();
          jQuery ('#edit-field-verifed-by-admin').hide();
          jQuery ('#edit-profile-shop-owner-field-shop').hide();
          jQuery ('#edit-profile-shop-owner-field-your-shop-id-is').hide();
          jQuery ('#edit-field-first-name').insertBefore('.form-item-name');
          jQuery ('#edit-field-last-name').insertBefore('.form-item-name');
          jQuery ('#edit-field-phone').insertBefore('.form-item-name');

		// enhancements 2013-02-24
		jQuery( '#edit-delete' ).remove();
		if ( ! user.is_admin ) {
			<?php // user gets their own acct, so is confusing for an admin ?>
			jQuery( "ul.nav" ).append( '<li><a class="btn" href="/user/' + uid + '/edit">Account</a></li>' );
		}
		jQuery( 'ul.nav-tabs li a' ).addClass('btn');
		jQuery( 'ul.nav-tabs' ).addClass('nav-pills');
//		jQuery( 'ul.nav-tabs' ).removeClass('nav-tabs');
    });   

		// set up validation 2013-02-22
		jQuery('#edit-title, #edit-field-location-und-0-street,#edit-field-location-und-0-city, #edit-field-location-und-0-province').addClass( 'validateEmpty' );
		jQuery('#edit-field-location-und-0-postal-code').addClass( 'validateZipcode' );

		jQuery( "form#shop-node-form" ).miniValidate();

</script>
