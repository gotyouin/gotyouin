<div class="user-profile">
<?php
//******************************************************************************
//----BASIC PARSING AND ASSIGNMENT OF VALUES SPECIFIC TO USER TYPES----------JPC
/*------------------------------------------------------------------------------
 * IDENTITY OF THE "VIEWER"
 * Additions by Jenny Chalek 2013-2-7
 * This determines the information of what person is VIEWING this profile
 * and $v_type will tell us which profile the viewer has - this allows us to
 * conditionally hide stuff that only the owner of the profile should see.
 * Then we can compare this to the owner's info to determine whether the 
 * viewer is viewing their own profile or someone else's
 */

//we will need this helper file to process paid subscription cancellations
require_once('includes/PayflowNVPAPI.php'); //payflow api utility function file from paypal's website


if (!$user->uid == 0) {
     // array with user data     [barber] => Profile Object
	$profile2viewer = profile2_load_by_user($user->uid);
     $viewer_is_shop_owner = false;
     //First, let's check whether the viewer is a shop owner
     //this will be treated as a fact unrelated to the person's other profile(s)
     if(array_key_exists('shop_owner', $profile2viewer)) {
          $viewer_is_shop_owner = true;
          $viewer_owner_profile = $profile2viewer['shop_owner'];
          $viewer_owner_wrapper = entity_metadata_wrapper('profile2',$viewer_owner_profile);
     }
     
     //Then parse the other profile types of the viewer
     if (array_key_exists('barber', $profile2viewer)) {
          $v_type = 'barber';
          $profile_viewer = $profile2viewer['barber'];
     } elseif (array_key_exists('independent_barber', $profile2viewer)) {
          $v_type = 'independent_barber';
          $profile_viewer = $profile2viewer['independent_barber'];
     } elseif (array_key_exists('main', $profile2viewer)) {
          $v_type = 'main';
          $profile_viewer = $profile2viewer['main'];
     } elseif (array_key_exists('shop_owner', $profile2viewer)) {
          //leave this here to catch situations where shop owners have not
          //set up their barber profiles yet
          $v_type = 'shop_owner';
          $profile_viewer = $profile2viewer['shop_owner'];
     } else {
          $p_type = 'main'; //catch all customer bit
          $profile_viewer = $profile2viewer['main'];
     }
     //Let's wrap the VIEWER'S data in an entity_metadata_wrapper for easier manipulation
     $ewrapper_viewer = entity_metadata_wrapper('profile2',$profile_viewer);
} else {
    $v_type = 'none';
}
//------------------------------------------------------------------------------

/*---------------------------------------------------------------------------JPC
 * IDENTITY OF THE "OWNER"
 * Here's where we find out if the owner and viewer are the same person,
 * and adjust our variables accordingly.
 */
if ($user->uid == $elements['#account']->uid) {
     //Person viewing their own profile - both profiles are the same
     //We simply copy our viewer profile into to the owner profile
     //and set the $self flag to true
     $profile_owner = $profile_viewer;
     $ewrapper_owner = $ewrapper_viewer;
     $p_type = $v_type;
     $self = true;
     $shop_owner = $viewer_is_shop_owner;

     $owner_profile_info = $viewer_owner_profile;
     //var_dump($owner_profile_info);
     $owner_wrapper = $viewer_owner_wrapper;     
   
} else {
     //we need to gather information about the person whose profile this is
     //Someone else viewing this profile - let's retrieve and wrap owner profile2 info
     //echo $elements['#account']->uid."<br />";
     $profile2o = profile2_load_by_user($elements['#account']->uid);
     //First, if the profile belongs to a shop owner, we need to gather that
     //information separately, because it is treated differently
     //Start by assuming this isn't a shop owner
     $shop_owner = false;
     if (array_key_exists('shop_owner', $profile2o)) {
          $shop_owner = true;
          $owner_profile_info = $profile2o['shop_owner'];
          //var_dump($owner_profile_info);
          $owner_wrapper = entity_metadata_wrapper('profile2',$owner_profile_info);
          //need to also retrieve the related shop node?
     }
     
     //Now, deal with the other three profile types
     $profile2o = profile2_load_by_user($elements['#account']->uid);
     
     if (array_key_exists('barber', $profile2o)) {
          $p_type = 'barber';
          $profile_owner = $profile2o['barber'];
     } elseif (array_key_exists('independent_barber', $profile2o)) {
          $p_type = 'independent_barber';
          $profile_owner = $profile2o['independent_barber'];
     }  elseif (array_key_exists('main', $profile2o)) {
          $p_type = 'main';
          $profile_owner = $profile2o['main'];
     } elseif (array_key_exists('shop_owner', $profile2o)) {
          //leave this here to catch situations where shop owners have not
          //set up their barber profiles yet
          $p_type = 'shop_owner';
          $profile_owner = $profile2o['shop_owner'];
     } 
     //Let's wrap the OWNER'S data in an entity_metadata_wrapper for easier manipulation
     $ewrapper_owner = entity_metadata_wrapper('profile2',$profile_owner);
     $self = false;
}
//------------------------------------------------------------------------------

/*---------------------------------------------------------------------------JPC
 * Now that we have access to the data structures, set the values of some
 * variables that will be used within the page rendering
 * 
 * OWNER VARIABLES:
 * $node_shop - node object of an owner's shop
 * $ewrapper_shop - entity wrapper to manipulate shop node easily
 * $which_array - field_hours_of_operation for the shop
 * $shop_url - link to the shop's page
 * $bottom_button - full text to generate the bottom button
 * 
 * BARBER/INDEPENDENT BARBER VARIABLES:
 * $which_array - field_barber_availability for the barber
 * $appt_url - generated url with barber id argument for appt setups
 * $bottom_button - full text to generate the bottom button
 * 
 * 
 * "OTHER" VARIABLES
 * $appt_url generic url for appt setups
 * $bottom_button full text to generate the bottom button
 */
$shop_exists = false;
if ($shop_owner) {
          
     if ($owner_wrapper->get('field_your_shop_id_is')->value()) {
          $shop_exists = true;
          $node_shop = node_load($owner_wrapper->get('field_your_shop_id_is')->value());
          $ewrapper_shop = entity_metadata_wrapper('node',$node_shop);
          $which_array = $ewrapper_shop->get('field_hours_of_operation')->value();
          $shop_url = "/node/".$owner_wrapper->get('field_your_shop_id_is')->value();
          $bottom_button = '<a href="'.$shop_url.'" class="btn btn-danger">Visit Shop</a>';
     } else {
          $which_array = false;
          $bottom_button = '';
     }     
}

if ($p_type == 'barber' || $p_type == 'independent_barber') {
     $which_array = $ewrapper_owner->get('field_barber_availability')->value();
     $appt_url = "/new-appointment?barber_id=".$elements['#account']->uid;
     if($user->uid == $elements['#account']->uid) { $appt_url = '/node/add/booking'; }
     $bottom_button = '<a href="'.$appt_url.'" class="btn btn-danger">New Appointment</a>';
     if ($v_type != 'main' && $v_type != 'none') { //don't show this if a customer
        $bookings_button = '<a href="/user/'. $user->uid . '/appointments" class="btn btn-danger">Show Appointments</a>';
     }
} else {
     $appt_url = "/new-appointment";
     $bottom_button = '<a href="'.$appt_url.'" class="btn btn-danger">Schedule Appointment</a>';
}
//------------------------------------------------------------------------------
//******************************************************************************
?>

<?php
  if( isset($elements['#account']->picture->uri) ){
    $default_thumbnail = image_style_url('barb_profile', $elements['#account']->picture->uri);
    ?>
  <div class="user-picture"><img src="<?php print $default_thumbnail; ?>" /></div>
  <?php }else{ ?>
    <div class="user-picture"><img src="/sites/all/themes/bh_bootstrap/images/avatar.png" /></div>
  <?php } ?>

<h1 class="title" id="page-title">
<?php
  if( isset($elements['#account']->field_display_name['und'][0]['value']) )
  {

    print $elements['#account']->field_display_name['und'][0]['value'];

  } else {

    print $elements['#account']->field_first_name['und'][0]['value'] . ' ' . $elements['#account']->field_last_name['und'][0]['value'];

  }
?>
</h1>

<?php

if ($p_type == 'shop_owner' || $p_type == 'independent_barber'  || $p_type == 'barber')
{
  print views_embed_view('barber_rating', 'block');


} else {

  print views_embed_view('barber_rating', 'block_1');

}

?>

<?php

if ($p_type == 'shop_owner' || $p_type == 'independent_barber'  || $p_type == 'barber') {

	// set up social media references for barber user types 2013-03-18
	$twitter = $facebook = $linkedin = $googleplus = $instagram = null;
	
	// profile data is gathered 2 ways, depending on whether the person is viewing their own profile or not.
	if (empty( $profile2o ) ) {
		$_profile = $profile2viewer;
	} else {
		$_profile = $profile2o;
	}
	
	/*
	probably phase 2 ... difficult inside a webview to tell if the user has a given app installed or not.

	// Handle apple specific uri schemes for iOS users. 2013-03-20
	$is_ios = (preg_match("/iP(od|hone|ad)/i", $_SERVER["HTTP_USER_AGENT"]) ) ? true : false;
		fb://$profile
		twitter://$user
	*/

	// TODO: see about using custom iOS URL schemes for iPhone.
	if ( $p_type === 'barber' ) {
		if ( strlen( trim( $_profile['barber']->field_twitter_handle['und'][0]['safe_value'] ) ) ) {
			if ( $is_ios ) {
			} else if ( stristr( $_profile['barber']->field_twitter_handle['und'][0]['safe_value'], '/' ) ) {
				// assume someone put in a url.
				$_handle = trim(  $_profile['barber']->field_twitter_handle['und'][0]['safe_value'] );
				$twitter = '<a id="social-twitter" _target="blank" rel="nofollow" href="' . $_handle . '"><img src="/sites/all/themes/bh_bootstrap/images/icons/fc-webicon-twitter-m.png" alt="Twitter" /></a>' ;
			} else {
				$_handle = trim(str_replace( '@', '', $_profile['barber']->field_twitter_handle['und'][0]['safe_value'] ));
				$twitter = '<a id="social-twitter" _target="blank" rel="nofollow" href="http://twitter.com/' . $_handle . '"><img src="/sites/all/themes/bh_bootstrap/images/icons/fc-webicon-twitter-m.png" alt="Twitter" /></a>' ;
			}
		}
		if ( strlen( trim( $_profile['barber']->field_instagram['und'][0]['safe_value'] ) ) ) {
			if ( stristr( $_profile['barber']->field_instagram['und'][0]['safe_value'], '/' ) ) {
				// assume someone put in a url.
				$_handle = trim(  $_profile['barber']->field_instagram['und'][0]['safe_value'] );
				$instagram = '<a id="social-instagram" _target="blank" rel="nofollow" href="' . $_handle . '"><img src="/sites/all/themes/bh_bootstrap/images/icons/fc-webicon-instagram-m.png" alt="Instagram" /></a>' ;
			} else {
				$_handle = trim(str_replace( '@', '', $_profile['barber']->field_instagram['und'][0]['safe_value'] ));
				$instagram = '<a id="social-instagram" _target="blank" rel="nofollow" href="http://instagram.com/' . $_handle . '"><img src="/sites/all/themes/bh_bootstrap/images/icons/fc-webicon-instagram-m.png" alt="Instagram" /></a>' ;
			}
		}
		if ( strlen( trim( $_profile['barber']->field_facebook['und'][0]['safe_value'] ) ) ) {
			$_url = trim( $_profile['barber']->field_facebook['und'][0]['safe_value'] );
			if ( !  preg_match( '@https?://@', $_url ) ) {
				$_url = 'http://' . $_url;
			}
			$facebook = '<a id="social-facebook" _target="blank" rel="nofollow" href="' . $_url . '"><img src="/sites/all/themes/bh_bootstrap/images/icons/fc-webicon-facebook-m.png" alt="Facebook" /></a>' ;
		}
		if ( strlen( trim( $_profile['barber']->field_google_plus['und'][0]['safe_value'] ) ) ) {
			$_url = trim( $_profile['barber']->field_google_plus['und'][0]['safe_value'] );
			if ( !  preg_match( '@https?://@', $_url ) ) {
				$_url = 'http://' . $_url;
			}
			$googleplus = '<a id="social-googleplus" _target="blank" rel="nofollow" href="' . $_url . '"><img src="/sites/all/themes/bh_bootstrap/images/icons/fc-webicon-googleplus-m.png" alt="GooglePlus" /></a>' ;
		}
		if ( strlen( trim( $_profile['barber']->field_linkedin['und'][0]['safe_value'] ) ) ) {
			$_url = trim( $_profile['barber']->field_linkedin['und'][0]['safe_value'] );
			if ( !  preg_match( '@https?://@', $_url ) ) {
				$_url = 'http://' . $_url;
			}
			$linkedin = '<a id="social-linkedin" _target="blank" rel="nofollow" href="' . $_url . '"><img src="/sites/all/themes/bh_bootstrap/images/icons/fc-webicon-linkedin-m.png" alt="LinkedIn" /></a>' ;
		}
	}

	// field names are different
	if ( $p_type === 'independent_barber' ) {
 		if ( strlen( trim( $_profile['independent_barber']->field_twitter_handlei['und'][0]['safe_value'] ) ) ) {
			if ( stristr( $_profile['independent_barber']->field_twitter_handlei['und'][0]['safe_value'], '/' ) ) {
				// assume someone put in a url.
				$_handle = trim(  $_profile['independent_barber']->field_twitter_handlei['und'][0]['safe_value'] );
				$twitter = '<a id="social-twitter" _target="blank" rel="nofollow" href="' . $_handle . '"><img src="/sites/all/themes/bh_bootstrap/images/icons/fc-webicon-twitter-m.png" alt="Twitter" /></a>' ;
			} else {
				$_handle = trim(str_replace( '@', '', $_profile['independent_barber']->field_twitter_handlei['und'][0]['safe_value'] ));
				$twitter = '<a id="social-twitter" _target="blank" rel="nofollow" href="http://twitter.com/' . $_handle . '"><img src="/sites/all/themes/bh_bootstrap/images/icons/fc-webicon-twitter-m.png" alt="Twitter" /></a>' ;
			}
		}
		if ( strlen( trim( $_profile['independent_barber']->field_instagrami['und'][0]['safe_value'] ) ) ) {
			if ( stristr( $_profile['independent_barber']->field_instagrami['und'][0]['safe_value'], '/' ) ) {
				// assume someone put in a url.
				$_handle = trim(  $_profile['independent_barber']->field_instagrami['und'][0]['safe_value'] );
				$instagram = '<a id="social-instagram" _target="blank" rel="nofollow" href="' . $_handle . '"><img src="/sites/all/themes/bh_bootstrap/images/icons/fc-webicon-instagram-m.png" alt="Instagram" /></a>' ;
			} else {
				$_handle = trim(str_replace( '@', '', $_profile['independent_barber']->field_instagrami['und'][0]['safe_value'] ));
				$instagram = '<a id="social-instagram" _target="blank" rel="nofollow" href="http://instagram.com/' . $_handle . '"><img src="/sites/all/themes/bh_bootstrap/images/icons/fc-webicon-instagram-m.png" alt="Instagram" /></a>' ;
			}
		}
		if ( strlen( trim( $_profile['independent_barber']->field_facebooki['und'][0]['safe_value'] ) ) ) {
			$_url = trim( $_profile['independent_barber']->field_facebooki['und'][0]['safe_value'] );
			if ( !  preg_match( '@https?://@', $_url ) ) {
				$_url = 'http://' . $_url;
			}
			$facebook = '<a id="social-facebook" _target="blank" rel="nofollow" href="' . $_url . '"><img src="/sites/all/themes/bh_bootstrap/images/icons/fc-webicon-facebook-m.png" alt="Facebook" /></a>' ;
		}
		if ( strlen( trim( $_profile['independent_barber']->field_google_plusi['und'][0]['safe_value'] ) ) ) {
			$_url = trim( $_profile['independent_barber']->field_google_plusi['und'][0]['safe_value'] );
			if ( !  preg_match( '@https?://@', $_url ) ) {
				$_url = 'http://' . $_url;
			}
			$googleplus = '<a id="social-googleplus" _target="blank" rel="nofollow" href="' . $_url . '"><img src="/sites/all/themes/bh_bootstrap/images/icons/fc-webicon-googleplus-m.png" alt="GooglePlus" /></a>' ;
		}
		if ( strlen( trim( $_profile['independent_barber']->field_linkedini['und'][0]['safe_value'] ) ) ) {
			$_url = trim( $_profile['independent_barber']->field_linkedini['und'][0]['safe_value'] );
			if ( !  preg_match( '@https?://@', $_url ) ) {
				$_url = 'http://' . $_url;
			}
			$linkedin = '<a id="social-linkedin" _target="blank" rel="nofollow" href="' . $_url . '"><img src="/sites/all/themes/bh_bootstrap/images/icons/fc-webicon-linkedin-m.png" alt="LinkedIn" /></a>' ;
		}
	}
unset( $_profile );
//dump( $user );
	?>
	<div id="social-media">
		<?php if ( $twitter ) echo $twitter; ?>
		<?php if ( $facebook ) echo $facebook; ?>
		<?php if ( $linkedin ) echo $linkedin; ?>
		<?php if ( $googleplus ) echo $googleplus; ?>
		<?php if ( $instagram ) echo $instagram; ?>
	</div>
	<?php

     //TODO: FIX THIS TO GET RID OF DUAL BIOS PROPERLY
     if ($ewrapper_owner->get('field_bio')->value() != "int(0)") {
          echo '<div class="profile-bio">'.$ewrapper_owner->get('field_bio')->value().'</div>';
     }
} ?>

<?php
//******************************************************************************
//----SHOP OWNER SPECIFIC----------------------------------------------------JPC
//JPC added to display shop information on a shop owner's page
if ($shop_owner){
     if ($shop_exists) {
          print "<h3>My Shop: ".$owner_wrapper->get('field_shop')->value()->title."</h3>";
          //echo "IT'S GETTING PAST LINE 204<br />";
          if ($self) {
               //this way, only shop owners can see their own shop id
               echo '<p class="tagline">';
               print "My Shop ID is: ". 
                     $owner_wrapper->get('field_your_shop_id_is')->value()."</p><p>";
          }
     } else {
          
     }
}
//------------------------------------------------------------------------------
//******************************************************************************
          
  if( isset($elements['#account']->roles['6']) ) # IF PROFILE VIEWED HAS BARBER ROLE
  {
//       foreach($ewrapper_owner as $key=>$value) {
//            echo "$key = $value<br />";
//       }
       $shop_url = "/node/".$ewrapper_owner->get('field_shop')->value()->nid;
       
    print "<p class='tagline barber-at'><a href='".$shop_url."'>Barber at " . $ewrapper_owner->get('field_shop')->value()->title."</a>";
  }
  
?>
</p>

<?php if ($bottom_button) { ?>
   <div class="btn-schedule">
        <?php echo $bottom_button ?>
   </div>
<?php } ?>
<?php if ($bookings_button) { ?>
   <div class="spacer" style="margin: 1em"></div>
   <div class="btn-schedule">
        <?php echo $bookings_button ?>
   </div>
<?php } ?>

<?php
//******************************************************************************
//----begin hours generation-------------------------------------------------JPC
if ($p_type != 'main' && $which_array != false) { //don't show hours if this is a customer profile - JPC
     foreach ( (array)$which_array as $day)
    {
      $hours[$day['day']]['start'] = $day['starthours'];
      $hours[$day['day']]['end'] = $day['endhours'];
      if( strlen($hours[$day['day']]['start']) == 3 ) { $hours[$day['day']]['start'] = '0' . $hours[$day['day']]['start']; }
      if( strlen($hours[$day['day']]['end']) == 3 ) { $hours[$day['day']]['end'] = '0' . $hours[$day['day']]['end']; }
      $hours[$day['day']]['start'] = date('g:ia', strtotime($hours[$day['day']]['start']) );
      $hours[$day['day']]['end'] = date('g:ia', strtotime($hours[$day['day']]['end']) );
      $hours[$day['day']]['start'] = str_replace(':00','',$hours[$day['day']]['start']);
      $hours[$day['day']]['end'] = str_replace(':00','',$hours[$day['day']]['end']);
    }
if ($p_type == 'shop_owner') {
     $hourhead = "Shop Hours";
} else {
     $hourhead = "My Hours";
}
?>
<div class="hours">
  <h3><?php echo $hourhead; ?></h3>
  <table cellpadding="0" cellspacing="0" class="table">
    <tr>
      <td>Sunday</td>
      <td>
      <?php
        if( isset($hours[0]['start']) && isset($hours[0]['end']) )
        {
          print $hours[0]['start'] . '-' . $hours[0]['end'];
          if( isset($hours[1]['start']) && isset($hours[1]['end']) )
          {
            print ' & ' . $hours[1]['start'] . '-' . $hours[1]['end'];
          }
        } else {
          print "Closed";
        }
      ?>
      </td>
    </tr>
    <tr>
      <td>Monday</td>
      <td>
      <?php
        if( isset($hours[2]['start']) && isset($hours[2]['end']) )
        {
          print $hours[2]['start'] . '-' . $hours[2]['end'];
          if( isset($hours[3]['start']) && isset($hours[3]['end']) )
          {
            print ' & ' . $hours[3]['start'] . '-' . $hours[3]['end'];
          }
        } else {
          print "Closed";
        }
      ?>
      </td>
    </tr>
    <tr>
      <td>Tuesday</td>
      <td>
      <?php
        if( isset($hours[4]['start']) && isset($hours[4]['end']) )
        {
          print $hours[4]['start'] . '-' . $hours[4]['end'];
          if( isset($hours[5]['start']) && isset($hours[5]['end']) )
          {
            print ' & ' . $hours[5]['start'] . '-' . $hours[5]['end'];
          }
        } else {
          print "Closed";
        }
      ?>
      </td>
    </tr>
    <tr>
      <td>Wednesday</td>
      <td>
      <?php
        if( isset($hours[6]['start']) && isset($hours[6]['end']) )
        {
          print $hours[6]['start'] . '-' . $hours[6]['end'];
          if( isset($hours[7]['start']) && isset($hours[7]['end']) )
          {
            print ' & ' . $hours[7]['start'] . '-' . $hours[7]['end'];
          }
        } else {
          print "Closed";
        }
      ?>
      </td>
    </tr>
    <tr>
      <td>Thursday</td>
      <td>
      <?php
        if( isset($hours[8]['start']) && isset($hours[8]['end']) )
        {
          print $hours[8]['start'] . '-' . $hours[8]['end'];
          if( isset($hours[9]['start']) && isset($hours[9]['end']) )
          {
            print ' & ' . $hours[9]['start'] . '-' . $hours[9]['end'];
          }
        } else {
          print "Closed";
        }
      ?>
      </td>
    </tr>
    <tr>
      <td>Friday</td>
      <td>
      <?php
        if( isset($hours[10]['start']) && isset($hours[10]['end']) )
        {
          print $hours[10]['start'] . '-' . $hours[10]['end'];
          if( isset($hours[11]['start']) && isset($hours[11]['end']) )
          {
            print ' & ' . $hours[11]['start'] . '-' . $hours[11]['end'];
          }
        } else {
          print "Closed";
        }
      ?>
      </td>
    </tr>
    <tr>
      <td>Saturday</td>
      <td>
      <?php
        if( isset($hours[12]['start']) && isset($hours[12]['end']) )
        {
          print $hours[12]['start'] . '-' . $hours[12]['end'];
          if( isset($hours[13]['start']) && isset($hours[13]['end']) )
          {
            print ' & ' . $hours[13]['start'] . '-' . $hours[13]['end'];
          }
        } else {
          print "Closed";
        }
      ?>
      </td>
    </tr>
  </table>
</div>
<?php
} 
//----end hours generation------------------------------------------------------
//******************************************************************************

/*******************************************************************************
 * ----begin services generation------------------------------------------------
 * construct a tabular view of the services
 */
//determine whether the owner of this profile is of the type to which this page 
//applies - i.e. a barber or indepdendent barber
if ($p_type == 'barber' || $p_type == 'independent_barber') {
     if (isset($profile_owner)) {
     $existing_services = $profile_owner->field_services['und'];
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
//----end services generation---------------------------------------------------

//Cancel button for paid accounts
  if ($self){
       if ($p_type == 'shop_owner' || $p_type == 'independent_barber') {
            ?>
              <div><a href="/cancel-account" 
               class="btn btn-danger">Cancel My Account</a></div>
            <?php
       }
  }
?>
<script>
  jQuery( function() {
    if ( user.is_customer) {
		<?php // Customer only. User gets their own uid, so is confusing for an admin ?>
		jQuery( "ul.nav" ).append( '<li><a href="/user/' + user.uid + '/customer">Appointments</a></li>' );
    }
    jQuery("#edit-field-location-und-0-delete-location").hide();
    jQuery("#edit-field-verifed-by-admin-und").hide();
          //we need to hide these because the services data is generated in a 
          //more complex way and needs special code instead
          jQuery("#edit-profile-independent-barber-field-services-term").hide();
          jQuery("#edit-profile-independent-barber-field-services").hide();
          jQuery("#edit-profile-barber-field-services-term").hide();
          jQuery("#edit-profile-barber-field-services").hide();
          //need to hide this because shop shouldn't be able to give itself a free account
          jQuery("#edit-profile-shop-owner-field-complementary").hide();
          jQuery("#edit-profile-shop-owner-field-shop").hide();
          jQuery ('#new-app-services').insertBefore('#edit-actions');
  });
</script>
</div>
