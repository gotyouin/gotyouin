<?php
/**
 * @file
 * Custom landing page for 30 day free trial
 * Based on Default theme implementation to display a single Drupal page.
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
//we need to process the form submit first, because we will be redirecting
//and you can't do that without error unless it's the first item output from php
if (isset($_POST['user-type'])) {
     switch ($_POST['user-type']) {
          case 'shop_owner':
               $path = 'Location: sowner/register';
               break;
          case 'independent_barber':
               $path = 'Location: ibarber/register';
               break;
     }
     header($path);
     exit;
}
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
      <?php if ($title && !$page['highlighted']): ?>
      <h1 class="title" id="page-title"><?php print $title; ?></h1>
      <div class="content">
      
      <div class="inner">
        <a class="btn-main" href="/subscribe" style="margin-bottom:1em;">Enroll Now <i class="icon-play"></i></a>
        <div class="feature-example feature-example-fullbleed displaypiece">
        	<img alt="" src="/sites/all/themes/bh_bootstrap/images/splash.jpg">
	   	</div>
       
            
      <?php elseif ($title && $page['highlighted']): ?>
      <h2 class="title" id="page-title"><?php print $title; ?></h2>
      <?php endif; ?>
      <?php print render($title_suffix); ?>
      <?php /* if ($tabs['#primary'] != ''): ?><div class="tabs"><?php print render($tabs); ?></div><?php endif; ?>
      <?php print render($page['help']); ?>
      <?php if ($action_links): ?><ul class="action-links"><?php print render($action_links); ?></ul><?php endif; */?>
      <?php /* print render($page['content']); */ ?>
      <?php print $feed_icons; ?>      
      <!--<h3>
           Yes! I want to try the Got You In service for 30 days for free.
      </h3>-->
       <p style="text-align:ccenter;">Got You In is always free for customers. <a href='customer/register'>Go here to register as a customer.</a></p>
             <p><a href="/sowner/register" class="btn btn-main">Register as a Shop Owner <i class="icon-play"></i></a></p>
      <p><a href="/sowner/register" class="btn btn-main">Register as a School Administrator <i class="icon-play"></i></a></p>
      <p><a href="/register-now" id="barber-register" class="btn btn-main">Register as a Barber <i class="icon-play"></i></a></p>
      <div id="hidden-barbers" style="display:none;">
      <p>Which type of barber are you.</p>
	      <a href="/ibarber/register" class="btn btn-main">Independent Barber <i class="icon-play"></i></a>
	      <a href="/barber/register" class="btn btn-main">a Barber whose shop has registered<i class="icon-play"></i></a>
      </div>
      <script>
      jQuery(document).ready(function() {
   		// some code here
   		jQuery("#barber-register").on("click", function(e){
			jQuery("div#busy").hide()
			jQuery("#hidden-barbers").toggle();
			e.preventDefault();
			return false;
    	});
 	  });
      </script>
          
      <?php /*<form method='POST' name='pick-one' class='trial'>
           <label for='user-type'>I am... </label>
           <select name='user-type' class='user-type'>
                <option value='shop_owner'>a Barber Shop Owner or Barber School Administrator</option>
                <option value='independent_barber'>an Independent Barber</option>
           </select>
           <input type='submit' id='submit' value='GO!' name='GO!' />
           <p>Or are you a <a href='customer/register'>Customer</a> looking for an easy, free way to schedule haircuts online? It's always free for Customers.</p>
           <p>Or a <a href='barber/register'>Barber</a> whose shop has an account?</p>
      </form> */?>
      </div> 
      
    </div>
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
