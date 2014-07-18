<?php
/**
 * Custom template for the all/most user editing page
 */
/** First, we pull the default content from the generic page.tpl.php
 * @file
 * Default theme implementation to display a single Drupal page.
 */

/*
 * This section finds out which edit screen the user is viewing
 */
$request_string = $_SERVER['REQUEST_URI'];
$request = explode('/',$request_string);
$p_view = array_pop($request);
$show_services = false;
$show_appts = false;
$is_owner = false;

if ($p_view == 'main') {
     $show_appts = true;
}

if ($p_view == 'barber' || $p_view == 'independent_barber') {
     $show_services = true;
}

/**
 * This section finds out what profile2 profile the user has
 */
$profiles = profile2_load_by_user($user->uid);

if ($p_view == 'shop_owner' || array_key_exists('shop_owner', $profiles)) {
     $is_owner = true;
     $owner = $profiles['shop_owner'];
     if (!array_key_exists('barber', $profiles)) {
          $array = array();
          $array['user'] = $user->uid;
          $array['type'] = 'barber';
          $barberprofile = profile2_create($array);
          $barberprofile->field_shop['und']['0'] = $owner->field_shop['und']['0'];
          profile2_save($barberprofile);
          //now re-load the profiles array
          
          $profiles = profile2_load_by_user($user->uid);
     }
}

if (array_key_exists('barber', $profiles)) {
     $p_type = 'barber';
     $profile = $profiles['barber'];
     if ($is_owner) {
          $profile->field_shop['und']['0'] = $owner->field_shop['und']['0'];
          profile2_save($profile);
     }
} elseif (array_key_exists('independent_barber', $profiles)) {
     $p_type = 'independent_barber';
     $profile = $profiles['independent_barber'];
} elseif (array_key_exists('main', $profiles)) {
     $p_type = 'main';
     $profile = $profiles['main'];
}
//Let's wrap the data in an entity_metadata_wrapper for easier manipulation
$ewrapper = entity_metadata_wrapper('profile',$profile);
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


<?php
/*------------------------------------------------------------------------------
 * If customer (type main), construct a table of their most recent appt info
 */
if ($p_view == 'main') {
  ?>
  <div id='appt-history'><h3>Appointment History</h3>
  <table cellpadding="0" cellspacing="0" class="table">
    <thead>
      <tr>
        <th>Barber</th>
        <th>Phone</th>
        <th>When</th>
      </tr>
    </thead>
    <tbody>
  <?php
     $query = new EntityFieldQuery();
     $query->entityCondition('entity_type', 'node')
             ->entityCondition('bundle', 'booking')
             ->fieldCondition('field_customer', 'target_id', $user->uid, '=')
             ->fieldOrderBy('field_date', 'value', 'DESC');
     $result = $query->execute();
     if (!isset($result['node'])) {
          echo "<tr>None found</tr>";
     }
     foreach (array_keys($result['node']) as $nid) {
          //echo "nid is ".$nid."<br />";
          $booking = getBookingDetails($nid);
          echo "<tr>".$booking."</tr>";
     }
     echo "</tbody></table></div>";
}

/*------------------------------------------------------------------------------
 * If shop owner and viewing the shop owner page, construct a fragment showing 
 * the owner's Shop ID and providing an edit button
 */
if ($p_view == 'shop_owner') {
     $shop_id = $owner->field_your_shop_id_is['und']['0']['value'];
     if ($shop_id == '') {
          $shop_exists = false;
          $shop_url = "/node/add/shop";
          $bottom_button = '<a href="'.$shop_url.'" class="btn btn-danger">Set Up My Shop</a>';
     } else {
          $shop_exists = true;
          $shop_url = "/node/".$shop_id."/edit";
          $bottom_button = '<a href="'.$shop_url.'" class="btn btn-danger">Edit My Shop</a>';
     }
     
     if ($bottom_button) { ?>
          <div id="edit_my_shop" class="btn-schedule">
               <?php echo $bottom_button; ?>
          </div> <?php
     }
}

/*------------------------------------------------------------------------------
 * If barber, need to display ID of the shop, with an edit button if not a shop 
 * owner? Or for all, but will delete shop if shop owner? TODO: decide
 */
if ($p_view == 'barber' /*&& $is_owner == false*/ ) {
     $shopid = $profile->field_shop['und']['0']['target_id'];
?>
      <div class="field-type-entityreference field-name-field-shop 
           field-widget-options-select form-wrapper" 
           id="edit-enter-barber-field-shop">
           <div class="form-item control-group form-type-select 
                form-item-profile-barber-field-shop-und">
                <label for="edit-enter-barber-field-shop" 
                       class="control-label">Shop ID</label>
                <div class="controls">
                     <input class="text-full form-text required" type="text" 
                            <?php if ($is_owner) echo ' disabled '; ?>
                            id="edit-enter-barber-field-shop" 
                            value="<?php echo $shopid; ?>" />
                </div>
           </div>
      </div>    
<?php
}

/*------------------------------------------------------------------------------
 * construct a tabular view of the services with a link to the edit page
 */
//determine whether the user is of the type to which this page is relevant - 
//i.e. a barber or indepdendent barber
if ($show_services) {
     if (isset($profile)) {
          $existing_services = $profile->field_services['und'];
          //$existing_services = $ewrapper->get('field_services')->value();
          $appt_url = "/services-setup";
          $button = '<a href="'.$appt_url.'" class="btn btn-danger">Add/Edit Services</a>';
          //here is code taken from services.php in booking module--------------
?>
          
     <div id="new-app-services">
          <h3>My Services</h3>
          <div class="btn-schedule">
               <?php echo $button;
if (!is_array($existing_services)) { //don't bother if no services exist
     echo "</div></div>";
} else {
?>
</div>

     <table cellpadding="0" cellspacing="0" class="table">
    <thead>
      <tr>
        <th>Service</th>
        <th>Price</th>
        <th>Time Required</th>
      </tr>
    </thead>
    <tbody>
         
<?php
foreach ($existing_services as $value) {
     $node_load = node_load($value['target_id']); //get the relevant node

     echo '<tr class="service-' . $value['target_id'] . '">';
     echo '<td>' . $node_load->title . '</td>';
     echo '<td>$' . $node_load->field_price['und']['0']['value'] . '</td>';
     echo '<td>' . $node_load->field_typical_time_required['und']['0']['value'] . '</td>';
     echo '</tr>';
}
?>
         
    </tbody>
  </table>
</div>

<?php
}
}
}
?>
</div>
     <?php //if ($page['row_post_content']): print render($page['row_post_content']); endif; ?>
<?php include('footer.php'); ?>

<?php
/**
 * Utility functions
 */
function getBookingDetails ($b_nid) {
     $b_node = node_load($b_nid);
     $barberid = $b_node->field_barber['und']['0']['target_id'];
     $barberdetails = getApptBarberDetails($barberid);
     $date = $b_node->field_date['und']['0']['value'];
     $apptdate = getApptDate($date);
     
     //now that we have the raw ingredients, build the table row
     $tablerow = "<td>".$barberdetails['barbername'];
     
     if (isset($barberdetails['shopname'])) {
          $tablerow .= " at ".$barberdetails['shopname'];
     }
     $tablerow .= "</td> ";
     $tablerow .= "<td>".$barberdetails['barberphone'];
     if (isset($barberdetails['shopphone'])) {
          $tablerow .= " or ".$barberdetails['shopphone'];
     }
     $tablerow .= "</td>";
     $tablerow .= "<td>".$apptdate."</td>";
     //echo "Tablerow = ".$tablerow;
     return $tablerow;
}
function getApptBarberDetails($barberid) {
     $barber = user_load($barberid);
     $barbername = $barber->field_first_name['und']['0']['value'];
     $barbername .= " " . $barber->field_last_name['und']['0']['value'];
     $barberphone = format_phone($barber->field_phone['und']['0']['value']);
     $profiles = profile2_load_by_user($barberid);
     $shopdetails = getApptShopDetails($profiles);
     
     
     $barberdetails = array();
     $barberdetails['barbername'] = $barbername;
     $barberdetails['barberphone'] = $barberphone;
     
     if ($shopdetails) { //if there were shop details found, regular barber
          $barberdetails['shopname'] = $shopdetails['shopname'];
          $barberdetails['shopphone'] = $shopdetails['shopphone'];
     }
          
     return $barberdetails;
}
function getApptShopDetails($profiles) {
     if (array_key_exists('barber', $profiles)) {
          $profile = $profiles['barber'];
          $shopid = $profile->field_shop['und']['0']['target_id'];
          
          $shopnode = node_load($shopid);
          $shopname = $shopnode->title;
          $shopphone = format_phone($shopnode->field_phone['und']['0']['value']);
          
          $shopdetails = array();
          $shopdetails['shopname'] = $shopname;
          $shopdetails['shopphone'] = $shopphone;
          
          return $shopdetails;
     } else {
          return false;
     }
}
function getApptDate($date) {
     //Need to do a timezone adjustment because, for some reason, drupal
     //profile2 field is spitting out timestands relative to GMT (+0000)
     //so we need to let it "translate" those dates based on this concept
     $tzadjust = $date . " +0000";
     $date_format = strtotime($tzadjust);

     $year = date('Y', $date_format);
     $day = date('jS', $date_format);
     $month = date('F', $date_format);
     $time = date('g:i a', $date_format);
     $apptdate = $month." ".$day.", ".$year." at ".$time;
     return $apptdate;
}
function format_phone($phone){
	$phone = preg_replace("/[^0-9]/", "", $phone);
	if(strlen($phone) == 7)
		return preg_replace("/([0-9]{3})([0-9]{4})/", "$1-$2", $phone);
	elseif(strlen($phone) == 10)
		return preg_replace("/([0-9]{3})([0-9]{3})([0-9]{4})/", "$1-$2-$3", $phone);
	else
		return $phone;
}
?>

<script type="text/javascript" src='/<?php echo path_to_theme(); ?>/js/miniValidate-0.5.js'></script>
<script>
	jQuery( function() {
		var p_view = '<?php echo $p_view; ?>';

		jQuery("#edit-field-location-und-0-delete-location").hide();
		jQuery("#edit-field-verifed-by-admin-und").hide();
          //shop owner specific
          jQuery("#edit-profile-shop-owner-field-complementary").hide();
          jQuery("#edit-profile-shop-owner-field-shop").hide();
          jQuery("#edit-profile-shop-owner-field-your-shop-id-is-und-0-value").prop('disabled', true);
          jQuery ('#edit_my_shop').insertBefore('#edit-actions');
          jQuery ('#appt-history').insertBefore('#edit-actions');
          jQuery ('#edit-enter-barber-field-shop').insertBefore('#edit-profile-barber-field-bio');
          
          //jQuery("#edit-profile-shop-owner-field-your-shop-id-is").hide();
          //we need to hide these because the services data is generated in a 
          //more complex way and needs special code instead
          //independent barber specific
          jQuery("#edit-profile-independent-barber-field-services-term").hide();
          jQuery("#edit-profile-independent-barber-field-services").hide();
          jQuery("#edit-profile-barber-field-shop").hide();
          //barber specific
          jQuery("#edit-profile-barber-field-services-term").hide();
          jQuery("#edit-profile-barber-field-services").hide();
          //both barbers
          jQuery ('#new-app-services').insertAfter('#edit-actions');
		jQuery('#new-app-services').before('<div style="border:2px #ddd solid"></div>');
		// 2013-03-18

		if ( p_view === 'shop_owner' ) {
          	jQuery ('#edit_my_shop').insertAfter('#edit-actions');
			jQuery('#edit_my_shop').before('<div style="border:2px #ddd solid;margin-bottom:1em;"></div>');
			jQuery( "form#user-profile-form" ).miniValidate();
		}
		
		if ( p_view === 'barber' || p_view === 'edit' || p_view === 'independent_barber' ) {
			jQuery( '#edit-profile-independent-barber-field-ifacebook-und-0-value, #edit-profile-barber-field-facebook-und-0-value' ).addClass( 'validateUrlOptional' );
			jQuery( '#edit-profile-independent-barber-field-linkedini-und-0-value, #edit-profile-barber-field-linkedin-und-0-value' ).addClass( 'validateUrlOptional' );
			jQuery( '#edit-profile-independent-barber-field-google-plusi-und-0-value, #edit-profile-barber-field-google-plus-und-0-value' ).addClass( 'validateUrlOptional' );
			jQuery( "form#user-profile-form" ).miniValidate();
		}
	
	});
</script>

