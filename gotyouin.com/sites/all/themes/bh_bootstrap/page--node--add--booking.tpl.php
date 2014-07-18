<?php
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
<!--<script type="text/javascript" src="http://ajax.microsoft.com/ajax/jquery.validate/1.5.5/jquery.validate.min.js"></script>-->

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
      <?php //print render($page['content']); ?>

       <?php //Replace the inner content of the edit tab with our content
       //by wrapping that content within this div nest ?>
      <div class="region region-content">
           <div id="block-system-main" class="block-system">
                <div class ="content">
      
     <?php
      //--CONTENT OF NODE HERE--------------------------------------------------
      //Strip the query string off the end url to see the return destination page
      $return_page = $_GET['destination'];
      if ($_GET['destination']) {
           $return_page = '/' . $_GET['destination'];
      } else {
           $return_page = '/user';
      }

     //Double-check whether the barber has a valid profile2 profile type to be 
     //relevant here - i.e. a barber or indepdendent barber
      $profile2 = profile2_load_by_user($user->uid);
      if (array_key_exists('barber', $profile2)) {
           $profile_object = $profile2['barber'];
      } elseif (array_key_exists('independent_barber', $profile2)) {
           $profile_object = $profile2['independent_barber'];
      } else {
           //barber info not available, abort, redirect to the previous page
           echo '<script type="text/javascript">';
           echo 'window.location = "' . $return_page . '"';
           echo '</script>';
      }
     //collect the details of all the barber's offered services
     //start with an empty array to collect the service details
      $available_services = $profile_object->field_services['und'];
      $targets = array();
      foreach ($available_services as $value) {
           foreach ($value as $target) {
                $targets[] = node_load($target);
           }
      }

      if (!empty($_POST)) {
           //echo $_POST['datetime']."<br />";
           //echo date_format($_POST['datetime'], 'Y-m-d');
           $appt_date = date('Y-m-d H:i:s', strtotime($_POST['datetime']));
           //echo date_format($_POST['datetime'], 'Y-m-d H:i:s');
           //die();
           switch ($_POST['op']) {
                case 'Add':
               //process the form
               $appt_date = date('Y-m-d H:i:s', strtotime($_POST['datetime']));
//                     if ($_POST['customer-edit'] != null) {
//                          $node->field_customer_name['und'][0]['value'] = $_POST['customer-edit'];
//                          $node->field_customer['und'][0]['target_id'] = '0';
//                          node_save($node);
//                     }

               //we will need a loop to iterate through possible service
               //input submissions. We need a second "counter to iterate
               //through and create the sequential array drupal expects
                $array_key = 0;
                $new_array = array();
                for ($i = 0;; $i++) {
                     $svc = 'svc-' . $i;
                     $svc_id = $_POST[$svc];

                     $stat = 'stat-' . $i;
                     $status = $_POST[$stat];

                     if ($status == 'add') {
                          $new_array[$array_key]['target_id'] = $svc_id;
                          $array_key++;
                     } elseif (!$status) {
                         //we've gone past the last one
                          break;
                     }
                }

//                $node->field_desired_services['und'] = $new_array;
//                node_save($node);
                 
               // creating a new object $node and setting its 'type' and uid property
               $values = array(
                 'type' => 'booking',
                 'uid' => $user->uid,
                 'status' => 1,
                 'comment' => 1,
                 'promote' => 0,
               );
               $entity = entity_create('node', $values);
               
               $f_barber_array = array();
               $f_barber_array['target_id'] = $user->uid;
               $f_barber_array['target_type'] = 'node';
               
               // The entity is now created, but we have not yet simplified use of it.
               // Now create an entity_metadata_wrapper around the new node entity
               // to make getting and setting values easier
               $ewrapper = entity_metadata_wrapper('node', $entity);
               
               if (!empty($new_array)) $ewrapper->field_desired_services->set($new_array);
               if ($_POST['customer-edit'] != null) {
                    $ewrapper->field_customer_name->set($_POST['customer-edit']);
               }
               // Entity API cannot set date field values so the 'old' method must
               // be used
               $entity->field_barber[LANGUAGE_NONE][0] = $f_barber_array;
               $entity->field_date[LANGUAGE_NONE][0] = array(
                  /*'value' => date_format($_POST['datetime'], 'Y-m-d hh:mm:ss')*/
                  'value' => $appt_date
                );

               //$ewrapper->title->set('YOUR TITLE');
               // Setting the value of an entity reference field only requires passing
               // the entity id (e.g., nid) of the entity to which you want to refer
               // The nid 15 here is just an example.
               //$ref_nid = 15;
               // Note that the entity id (e.g., nid) must be passed as an integer not a
               // string
               //$ewrapper->field_my_entity_ref->set(intval($ref_nid));

               

               // Now just save the wrapper and the entity
               // There is some suggestion that the 'true' argument is necessary to
               // the entity save method to circumvent a bug in Entity API. If there is
               // such a bug, it almost certainly will get fixed, so make sure to check.
               $ewrapper->save(true);
               entity_save('node', $entity);

                echo '<script type="text/javascript">';
                echo 'alert("You have successfully scheduled an appointment!");';
//               echo 'window.location = "' . $return_page . '"';
               echo '</script>';
                
                break;
           
               case 'Cancel':
                    echo '<script type="text/javascript">';
                    echo 'window.location = "' . $return_page . '"';
                    echo '</script>';
                    break;
      }
 }

// $url = $_SERVER['REQUEST_URI'];
// $url_array = explode('?', $url);
////The basic query string will tell us whether we are on "edit" or "view"
////(this template controls both
// if (substr($url_array[0], -4) == 'edit') { //this is an edit screen
////put all the edit-specific variable values here
//      $edit = true;
//      $cols = 'cols-4';
// } else { //this is a view screen
////put all the view-specific variable values here
//      $edit = false;
//      $cols = 'cols-3';
// }

 /**
  * This node template controls the booking create node
  * @author Jenny Chalek 2013-03-07
  */
      ?>
      <form class="form-horizontal" method="post" id="booking-node-form" accept-charset="UTF-8">
      <?php
      
      echo "<h4>Customer: ";

      $customerID = $node->field_customer['und'][0]['target_id'];
      if ($customerID != 0) {
           $customer = user_load($customerID);
      } else {
           $customer = null;
      }

      if ($customer == null) {
           echo $node->field_customer_name['und'][0]['value'];
      } elseif (empty($customer->field_display_name)) {
           echo $customer->field_first_name['und'][0]['safe_value'] . " " . $customer->field_last_name['und'][0]['safe_value'];
      } else {
           echo $customer->field_display_name['und'][0]['safe_value'];
      }
      
      echo '<input id="customer-edit" name="customer-edit" type="text">';
      echo "</h4>";

      echo "<h5>Appointment Date and Time: ";

      //$jsonDate = date('m d, Y g:i a', strtotime($node->field_date['und'][0]['value']));

      echo '<input name="datetime" id="datetime" class="i-txt" />';
      echo "</h5>";

     //gather chosen desired services - we only need the node ids to sort through
     //which services are chosen. start with an empty array
//      $services = array();
//      foreach ($node->field_desired_services['und'] as $value) {
//           if ($value['target_id'] != 0) {
//                $services[] = $value['target_id'];
//           }
//      }
      ?>

      <table id="add-remove-services" class="views-table <?php echo $cols; ?> table" width="100%">
           <thead>
                <tr>
                     <th>Service</th>
                     <th>Time Required</th>
                     <th>Price</th>
                     <th>Add / Delete</th>
                </tr>
           </thead>         

           <?php
           $total_time = 0;
           $total_price = 0;
          //we are using a counter to uniquely name the hidden service fields for processing
           $count = 0;
          //start with an empty array to collect the service details
          //$targets = array();
           foreach ($targets as $target_value) {
                
                echo "<tr>";
                echo "<td>" . $target_value->title . "</td>";
                echo "<td>" . $target_value->field_typical_time_required['und'][0]['value'] . '<span class="webonly"> minutes</span></td>';
                echo "<td>$" . $target_value->field_price['und'][0]['value'] . "</td>";

                $stat = 'na';
                $icon = '<td class="views-field views-field-add-node">';
                $icon .= '<span class="round-btn">toggle</span></td>';

                echo '<input type="hidden" id="svc' . $count . '" name="svc-' . $count . '" value="' . $target_value->nid . '" />';
                echo '<input type="hidden" id="stat" name="stat-' . $count . '" value="' . $stat . '" />';
                $count++;
                echo $icon;

                echo "<tr>";
//                     if ($active) {
//                          $total_time += $target_value->field_typical_time_required['und'][0]['value'];
//                          $total_price += $target_value->field_price['und'][0]['value'];
//                     }
                
           }
           ?>
           
      </table> 

      
           <div class="form-actions form-wrapper" id="edit-actions">
                <input class="btn btn-main form-submit" type="submit" id="edit-submit" name="op" value="Add" disabled="disabled" />
                <input class="btn form-submit" type="submit" id="edit-cancel" name="op" value="Cancel" />
           </div>
      </form>
 <?php

//----------------------------------------------------------------------------
                     
                     ?>

                </div>
           </div>
      </div>
      <?php
      
      ?>
      
      
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
<script type='text/javascript'>
     jQuery(function() {    
          jQuery('#edit-title').val('Booking');
          jQuery('.form-item-title').hide();
		
		// validation
		jQuery( "#edit-submit" ).attr( "disabled", "disabled" );
		jQuery('#customer-edit' ).addClass( 'validateEmpty' );
          //jQuery('#edit-field-desired-services').hide();
		jQuery( "form#booking-node-form" ).miniValidate( function() {
			if (  jQuery( "#datetime" ).val() == orginalDateTime ) {
				alert( "Please select a date in the future!" );
				return false;
			}
		});
  
          var now = new Date();
          jQuery('#datetime').mobiscroll().datetime({
              /*minDate: new Date("March 11, 2013"),*/
              minDate: now,
              dateOrder: 'mmddyyyy',
              theme: 'default',
              display: 'inline',
              mode: 'scroller'
          });
          jQuery('#datetime').mobiscroll('setDate',now,true);
          var orginalDateTime = jQuery( "#datetime" ).val();

          jQuery("#page-title").text("Add a Booking");
               
		jQuery( "td.views-field-add-node span"  ).html( '<i class="icon-plus icon-white"></i>' );
          jQuery(".views-field-add-node").click(
               function(){
          		if ( jQuery( this ).hasClass('hot') ) {
          			jQuery( this ).removeClass( 'hot' );
          			jQuery( this  ).children( 'span' ).html( '<i class="icon-plus icon-white"></i>' );
          			jQuery(this).parent().children("#stat").val("na");
                         jQuery(this).parent().toggleClass('active');
          		} else {
          			jQuery( this ).addClass( 'hot' );
                         jQuery(this).parent().children("#stat").val("add");
          			jQuery( this  ).children( 'span' ).html( '<i class="icon-minus icon-white"></i>' );
                         jQuery(this).parent().toggleClass('active');
          		}
          		if ( jQuery( ".views-field-add-node.hot" ).length > 0 ) {
          			jQuery( "#edit-submit" ).removeAttr( "disabled" );
          		} else {
          			jQuery( "#edit-submit" ).attr( "disabled", "disabled" );
          		}
          		
          		/*
          		// FF does not recognize this color scheme 2013-03-07
                    if (jQuery(this).parent().css("background-color") == "rgba(0, 0, 0, 0)"){
          		alert('1');
                         jQuery(this).parent().children("#stat").val("add");
                         jQuery(this).parent().css({
                              'background-color' : '#484', 
                              'color' : 'black'
                          });
                    } else {
          		alert('2');
                         jQuery(this).parent().children("#stat").val("na");
                         jQuery(this).parent().css({
                              'background-color' : 'rgba(0, 0, 0, 0)', 
                              'color' : 'gray'
                          });
                    }
          		*/
               }
          );
          
          jQuery("tr").children("#stat:hidden[value='keep']").parent().removeClass('active');
          jQuery("tr").children("#stat:hidden[value='na']").parent().removeClass('active');
            
 /*         jQuery("#edit-submit").click(
          function(){
               if (jQuery("tr").children("#stat:hidden[value='add']").length == 0) {
                    alert("Please select at least one service by tapping on the add link");
                    return false;
               }
          });
*/                    
          jQuery("#edit-cancel").click(
          function(){
               window.location = "<?php echo $return_page; ?>";
               return false;
          });
    });     
</script>
