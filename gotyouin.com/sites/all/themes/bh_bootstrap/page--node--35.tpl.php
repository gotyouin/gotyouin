<?php

/**
 * This template is for the thank you page - registration-thank-you
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



      <?php
      // Make the user object available
      global $user;
      // Grab the user roles
      $roles = $user->roles;
      if($roles){
        $confirmedrole = 'customer';
        foreach ($roles as $role) {
            //print_r($roles);
            //echo 'ggg';
            //echo $roles[4];
            if ($role == 'owner'){ $confirmedrole = 'owner'; }
            if ($role == 'barber'){ $confirmedrole = 'barber'; }
            if ($role == 'anonymous user'){ $confirmedrole = 'anonymous user'; }
        }
          if($confirmedrole == 'owner'){ ?>
          <h2>It looks like something didn't go as planned.</h2> 
          <p>If you are trying to set up a new account, you may already be logged in.</p>
          <p>To edit your information.</p>
          <p><a href="/user" class="btn-main">Click Here <i class="icon-play"></i></a></p>
          <?php }
          elseif($confirmedrole == 'anonymous user'){ ?>
          <h2>To access this feature you will first need create an account.</h2> 
          <p><a href="/register-now" class="btn-main">Get Started <i class="icon-play"></i></a></p>
          <?php }
          elseif($confirmedrole == 'barber'){ ?>
          <h2>It looks like something didn't go as planned.</h2> 
          <p>Maybe one of these links is what you were looking for?</p>
          <ul class="applinks">
            <li><a href="/user" class="btn-main">Edit Account Info <i class="icon-play"></i></a></li>
            <li><a href="/user" class="btn-main">Create a Custom Appointment <i class="icon-play"></i></a></li>
          </ul>
          <?php }
          else { ?>
          <h2>It looks like something didn't go as planned.</h2> 
          <p>Maybe one of these links is what you were looking for?</p>
          <ul class="applinks">
            <li><a href="/user" class="btn-main">Login <i class="icon-play"></i></a></li>
            <li><a href="/user" class="btn-main">Create an Account <i class="icon-play"></i></a></li>
          </ul> 
          <?php }
        //}
      }
      ?>
      <?php /* print render($page['content']); */ ?>
  




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

<script type='text/javascript'>
     jQuery(function() {
          if (jQuery ('.alert-block').length==0) {
               jQuery ('#block-system-name').hide();
          } else {
               jQuery ('#block-system-name').hide();
               jQuery ('#page-title').after(jQuery ('.alert-block'));
          }
    });     
</script>