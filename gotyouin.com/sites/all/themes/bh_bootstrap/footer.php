<?php if( !isset($_GET['app']) && !isset( $_COOKIE['gotyouin_mobile_app' ] ) ) { 
?>
<section id="blog-n-ad">
  <div class="container">
  	<div class="row">
  	<?php print render($page['footer']); ?>
  	</div>
  </div>
</section>
<footer class="footer">
  <div class="container">
  	<nav>
  		<ul>
  			<li><a href="/features-pricing">Features &amp; Pricing</a></li>
  			<li><a href="/node/20">Company</a></li>
  			<!--<li><a href="/node/55">Support</a></li>-->
  			<li><a href="/blog">Blog</a></li>
  			<li><a href="/contact">Contact Us</a></li>
  			<li><a href="/node/14">Terms &amp; Conditions</a></li>
  		</ul>
  	</nav>
  	<!-- AddThis Button BEGIN -->
	<div class="addthis_toolbox addthis_default_style ">
	<a class="addthis_button_facebook_like" fb:like:layout="button_count"></a>
	<a class="addthis_button_tweet"></a>
	<a class="addthis_button_pinterest_pinit"></a>
	<a class="addthis_counter addthis_pill_style"></a>
	</div>
	<script type="text/javascript">var addthis_config = {"data_track_addressbar":false};</script>
	<script type="text/javascript" src="//s7.addthis.com/js/300/addthis_widget.js#pubid=ra-4e64e83a3dbacd86"></script>
	<!-- AddThis Button END -->
  	<p class="copy">&copy; <?php echo date('Y'); ?> Got You In. All Rights Reserved. | <a href="/node/15">Privacy Policy</a> | <a href="/node/13">User Agreement</a> | <a href="http://dbswebsite.com">Louisville Web Design</a> by <a href="http://www.dbswebsite.com">DBS>Interactive</a></p>
  </div>
</footer>
<?php } /* STOPS FOOTER FROM DISPLAYING IF APP */ ?>
<div id="busy" class="busy"></div> <?php // hidden div for activity indicator 2013-02-26 ?>
<script>

jQuery('body.page-search-barbers .view-id-barbers .view-content .views-row').on("click", function(e) {
    var target  = jQuery(e.target);
    target.addClass('active');
    if( target.is('a') ) {
        return true; // True, because we don't want to cancel the 'a' click.
    }
    //jQuery(this).find.('.views-field-field-last-name a').click();
    window.location = jQuery(this, '.views-field-field-last-name a').find('a').attr("href");
          return false;
});
jQuery('body.page-search-barbershops .view-id-shops .view-content .views-row').on("click", function(e) {
    var target  = jQuery(e.target);
    target.addClass('active');
    if( target.is('a') ) {
        return true; // True, because we don't want to cancel the 'a' click.
    }
    window.location = jQuery(this, '.views-field-title a').find('a').attr("href");
          return false;
});
jQuery('body.page-search-barbers .view-id-barbers .view-content .views-row .views-field-uid .field-content a').addClass( 'btn' );

<?php
if ( ! isset( $_GET['field_date_value'] ) || empty( $_GET['field_date_value'] )  ) {
	// freshly loaded pages with mobiscroll have no date value (in hidden field), and thus do not work unless the date is changed 2013-03-15
	// set it here.
?>
	jQuery( '#edit-field-date-value' ).val( '<?php echo date( 'Y-m-d' ); ?>' );
<?php
}

?>
</script>
