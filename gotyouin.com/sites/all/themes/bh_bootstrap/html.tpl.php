<?php
/**
 * @file html.tpl.php
 * Default theme implementation to display the basic html structure of a single
 * Drupal page.
 *
 * Variables:
 * - $css: An array of CSS files for the current page.
 * - $language: (object) The language the site is being displayed in.
 *   $language->language contains its textual representation.
 *   $language->dir contains the language direction. It will either be 'ltr' or 'rtl'.
 * - $rdf_namespaces: All the RDF namespace prefixes used in the HTML document.
 * - $grddl_profile: A GRDDL profile allowing agents to extract the RDF data.
 * - $head_title: A modified version of the page title, for use in the TITLE
 *   tag.
 * - $head_title_array: (array) An associative array containing the string parts
 *   that were used to generate the $head_title variable, already prepared to be
 *   output as TITLE tag. The key/value pairs may contain one or more of the
 *   following, depending on conditions:
 *   - title: The title of the current page, if any.
 *   - name: The name of the site.
 *   - slogan: The slogan of the site, if any, and if there is no title.
 * - $head: Markup for the HEAD section (including meta tags, keyword tags, and
 *   so on).
 * - $styles: Style tags necessary to import all CSS files for the page.
 * - $scripts: Script tags necessary to load the JavaScript files and settings
 *   for the page.
 * - $page_top: Initial markup from any modules that have altered the
 *   page. This variable should always be output first, before all other dynamic
 *   content.
 * - $page: The rendered page content.
 * - $page_bottom: Final closing markup from any modules that have altered the
 *   page. This variable should always be output last, after all other dynamic
 *   content.
 * - $classes String of classes that can be used to style contextually through
 *   CSS.
 *
 * @see template_preprocess()
 * @see template_preprocess_html()
 * @see template_process()
 *
 * @ingroup themeable
 */

// Define whether we are a mobile or not for GYI. 2013-01-29 HB
// NOTE: the php variable is not visible to other scripts for some freakin
// reason. Test for both query string, mobile domain name and presence of cookie.
$is_mobile_app = false;
if ( ( isset( $_GET['app'] ) && $_GET['app'] == '1' ) || $_SERVER['HTTP_HOST'] == 'gotyouin.dbsclients.com' ) {
	$is_mobile_app = true;
	setcookie( 'gotyouin_mobile_app', 'true', time()+60*60*24*90, '/' );
}
if ( isset( $_COOKIE[ 'gotyouin_mobile_app' ] ) ) {
	$is_mobile_app = true;
}
if ( $is_mobile_app ) {
	$classes .= ' app-is-mobile';
	// special settings for mobile.
	ini_set('error_reporting', E_ALL & ~E_NOTICE);
	ini_set('display_errors', 'Off');
}

// create user object for javascript (see template.php for custom additions to Drupal $user object)
$dbs_user =  $user;
// remove some junk
unset( $dbs_user->roles, $dbs_user->pass, $dbs_user->sid, $dbs_user->signature, $dbs_user->theme, $dbs_user->created, $dbs_user->access, $dbs_user->hostname, $dbs_user->timestamp );
// encode it
$dbs_user = json_encode( $dbs_user );


?>
<!DOCTYPE html>
<html lang="en">
<head profile="<?php print $grddl_profile; ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" />
  <!--[if lt IE 9 ]>
  <script src="/sites/all/themes/bh_bootstrap/js/modernizr.custom.37834.js"></script>
  <![endif]--> 
  <?php print $head; ?>
  <title><?php print $head_title; ?></title>
  <link href='http://fonts.googleapis.com/css?family=Titillium+Web:400,600,700' rel='stylesheet' type='text/css'>
  <link href='http://fonts.googleapis.com/css?family=Open+Sans:400,600,700,300' rel='stylesheet' type='text/css'>
  <?php if ( !$is_mobile_app ) { ?><link rel="stylesheet" href="<?php echo DBS_STATIC; ?>/sites/all/themes/bh_bootstrap/interior.css?v=1.21"><?php } ?>
  <?php 
  //print $styles;
  print str_replace('www.gotyouin.com','static-gyi.dbsclients.com',$styles);
  ?>
  <link rel="stylesheet" href="<?php echo DBS_STATIC; ?>/sites/all/themes/bh_bootstrap/bh_bootstrap-drupal.css?v=1.22">
  <?php
  // 2013-03-02 HB 1.9.x seems to fix wierd Android 2.3 crash issues.
  // TODO: see template.php and js_alter function for a better way to do this.
  //echo  preg_replace( "#http://[a-z0-9\.]+/sites/all/modules/jquery_update/replace/jquery/1\.7/jquery.min.js\?v=1\.7\.1#",'//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js',$scripts ) ;
  //print $scripts;
  print str_replace('www.gotyouin.com','static-gyi.dbsclients.com',$scripts);
  ?>
  <?php //print $scripts; ?>
  <!--<link rel="stylesheet" href="http://maker.github.com/ratchet/css/docs.css">-->
  <!--<link rel="stylesheet" href="http://shuttersandshuttlesc.ipage.com/others.css">-->
  <?php if (drupal_is_front_page()){ ?><script src="<?php echo DBS_STATIC; ?>/sites/all/themes/bh_bootstrap/js/onscreen-iphone.js"></script><?php } ?>
  <script src="<?php echo DBS_STATIC; ?>/sites/all/themes/bh_bootstrap/js/jquery-ui-1.10.3.custom.min.js"></script>
  <script>
	var is_mobile_app = <?php echo ( $is_mobile_app ) ? 1 : 0?>;
	var siteURL = "http://<?php echo $_SERVER['HTTP_HOST'] ?>";
	<?php // IMPORTANT for mobile app: ?>
	var pathName = location.pathname;
	<?php // the user object has most of the Drupal user stuff + custom booleans for roles, eg user.is_owner ?>
	var user = <?php echo $dbs_user; ?>;
</script>

<script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

  ga('create', 'UA-47410435-1', 'gotyouin.com');
  ga('send', 'pageview');
</script>

</head>
<body class="<?php print $classes;?>" <?php print $attributes;?>>
  <div id="skip-link">
    <a href="#main-content" class="element-invisible element-focusable"><?php print t('Skip to main content'); ?></a>
  </div>
  <?php print $page_top; ?>
  <?php print $page; ?>
  <?php print $page_bottom; ?>
  <div id="dbs-ajax-loader"></div>
  <div id="dialog" title="Recurring Appointment" style="display:none;">
    <p>Would you like to delete ALL recurring appointments? Or only this one?</p>
  </div>
</body>
</html>
