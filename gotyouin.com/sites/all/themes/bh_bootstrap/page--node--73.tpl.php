<?php
/**
 * @author Jenny Chalek 2013-02-03
 * 
 * Custom implementation of a service generator to allow creating
 * of custom services "instances" - that is, a service linked to that barber's
 * personal choice of timeframe and cost. URL = /services-setup
 */
?>

<?php 
/*------------------------------------------------------------------------------
 * FIRST, STUFF WE NEED THROUGHOUT 
 * we will need the profile 2 profile for both adding and subtracting services 
 * if there is no logged in user, this page isn't relevant - redirect
 */
if ($user->uid == 0) {
//there is no user logged in, abort
//redirect to the user register page
//TODO: add a "message" to the redirect? as a GET argument?
     ?>
     <script type="text/javascript">
          window.location = "register-now";
     </script>
     <?php
}

//Now that we know there is a logged-in user, determine whether they are of the
//type to which this page is relevant - i.e. a barber or indepdendent barber
$profile2 = profile2_load_by_user($user->uid);
if (array_key_exists('barber', $profile2)) {
   $profile_object = $profile2['barber'];
} elseif (array_key_exists('independent_barber', $profile2)) {
   $profile_object = $profile2['independent_barber'];
} elseif (array_key_exists('shop_owner', $profile2)) {
     $profile_object = $profile2['shop_owner'];
} else {
//Customer, abort to user page
     ?>
     <script type="text/javascript">
             window.location = "user";
     </script>
     <?php
}

//Check to see if we have a return path
$ret = $_GET['ret'];
if (!empty($ret)) {
     switch ($ret) {
          case 0:
               $return_loc = "";
               break;
     }
}
//------------------------------------------------------------------------------

/*------------------------------------------------------------------------------
 * GATHER AND FORMAT THE DATA FOR THE EXISTING SERVICES FOR USE IN JAVASCRIPT
 * Now that we know this page is relevant to the user, let's gather a list of 
 * the existing services, if any, AND create a multidimentional array that's 
 * friendly to pass into json/javascript
 */
$existing_services = $profile_object->field_services['und'];

$jMultiArray = array(); //top-level multidimensional array

if (is_array($existing_services)) { //don't bother if no services exist
     foreach ($existing_services as $value) {
          $jArray = array(); //we will use this holding variable
          $node_load = node_load($value['target_id']); //get the relevant node
          $jArray['nid'] = $value['target_id'];
          $jArray['title'] = $node_load->title;
          $jArray['price'] = $node_load->field_price['und']['0']['value'];
          $jArray['type'] = $node_load->field_service_type['und']['0']['tid'];
          $jArray['time'] = $node_load->field_typical_time_required['und']['0']['value'];

          //push this value into the multdimensional array
          $jMultiArray[] = $jArray;
          unset($jArray); //clear this holding variable out before reusing it
     }
}
//------------------------------------------------------------------------------

/*------------------------------------------------------------------------------
 * IF FORM SUBMITTED, PARSE THE DATA INTO A FORMAT WE CAN USE
 */
if (!empty($_POST)) {
    
     //die();
    //need to iterate through possible incrementally named post variables
    //such as type-1, type-2, etc
    //to better organize this, we will make an associative array
    //to match up "sets" of post values in case they aren't read in order
    //one might expect
    $result_array = array();
    foreach ($_POST as $key=>$value) {
         if ($key == 'type' || $key == 'time' || $key == 'price' || $key == 'nid') {
              //These are the submit values of the hidden "master" div
              //and are not meant to be used
             continue;
         } elseif ($key == 'delete') {
              $delete_us = explode('|', $value);
         } else {
              //the $key will be of the pattern price-1, type-2 etc.
              //we can split the key based on the dash...
              $names = explode("-",$key);
              
              //then use the two halves to create and/or populate a structure
              //the first half is the actual 'name' key needed; the last half is
              //the unique key of the "set" of values
              //- eg. if the $key name is type-1, we will create if it doesn't
              //exist, and populate if it does, the value $result_array['1']
              //with a nested array containing the pair ['type'] = $value
              //the end result being a multidimentional array correctly
              //organized into key-value pairs that we can iterate through
              //to create the services node(s) programmatically
              $result_array[$names[1]][$names[0]] = $value;
         }
    }
    //the result array should now be fleshed out with he name-value pairs
    //the key in this case will be the ordinal associated with the iteration
    //(not important to know, just needed to keep things straight)
    //the value on the other hand, will be an array of the real name-value pairs

    if(isset($field_services)) unset($field_services); //sanity check/clean slate
    $field_services = array();
    
    foreach ($result_array as $key=>$value) {
         $success = create_a_service($value['type'], $value['time'], $value['price'], $value['nid']);
         if (!$success == false) {
              $field_services[]['target_id'] = $success; //push the node id into the field_service array
         }
    }
     $profile_object->field_services['und'] = $field_services;
     profile2_save($profile_object);
     
     //Now, we check for any nodes that need deletion and delete them
     if (is_array($delete_us)) {
          foreach ($delete_us as $d_value) {
               if (is_numeric($d_value)) node_delete($d_value);
          }
     }
     
     //redirect to the user's public profile page?
     ?>
          <script type="text/javascript">
          window.location = "user";
          </script>
     <?php
}
//------------------------------------------------------------------------------
?>
          
<?php
/*------------------------------------------------------------------------------
 * This whole section is copied/pasted directly from the plain page.tpl.php to
 * produce the common elements of the page
 */
?>
<?php include('header.php'); ?>

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
            <?php if ($title && !$page['highlighted'] && arg(0) != 'user'): ?>
                <h1 class="title" id="page-title"><?php print $title; ?></h1>
            <?php elseif ($title && $page['highlighted']): ?>
                <h2 class="title" id="page-title"><?php print $title; ?></h2>
            <?php endif; ?>
            <?php print render($title_suffix); ?>
            <?php if ($tabs['#primary'] != ''): ?><div class="tabs"><?php print render($tabs); ?></div><?php endif; ?>
            <?php print render($page['help']); ?>
            <?php if ($action_links): ?><ul class="action-links"><?php print render($action_links); ?></ul><?php endif; ?>

            <?php //print render($page['content']); ?>
            


<?php
/*------------------------------------------------------------------------------
 * Now to emit the HTML form itself
 */
?>
  <div id='service-form-div'>
  <em>All fields are required</em>
       <form id="service-form" method="post">
            <input type="text" name="delete" id="delete" value="" style="display:none" />
            <div id="service-master" class ="serviceInput" style="display:none">
                 <label for="type">Service: *</label> 
                 <select name="type" id="type">
                      <option value="0">Select...</option>
                      <option value="6">Bigen Blackout</option>
                      <option value="5">Cut & Razor</option>
                      <option value="7">Designs</option>
                      <option value="4">Kid Cut</option>
                      <option value="8">Misc</option>
                      <option value="3">Regular Cut</option>
                      <option value="1">Shape Up / Line Up</option>
                      <option value="2">Wash</option>
                 </select>
                 <label for="time">Typical Time Needed (minutes): *</label>
                 <input type="text" name="time" id="time" alt="Typical Time Required" />
                 <label for="price">Price: *</label>
                 <input type="text" name="price" id="price" />
                 <input type="button" name="btnDel" class="remove btn btn-danger" value="remove this service" />
                 <input type="hidden" name="nid" id="nid" value="" />
            </div>

            <div>
                 <input type="button" class="btn btn-success" id="btnAdd" value="add another service" />
                 <input type="submit" class="btn btn-main" value="ALL FINISHED" />
            </div>
       </form>
  </div>
<?php
//------------------------------------------------------------------------------
?>            

<?php
/*------------------------------------------------------------------------------
 * More stuff directly from default template
 */
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
<?php //if ($page['row_post_content']): print render($page['row_post_content']); endif;  ?>
<?php include('footer.php'); ?>
<?php
//------------------------------------------------------------------------------
?>
          
<?php
/*------------------------------------------------------------------------------
 * Custom server-side code to manipulate service nodes begins here
 */

/**
 * create_a_service function to create nodes of custom type "service"
 * code borrowed and lovingly adapted from example code at
 * http://drupal.org/node/1388922
 * 
 * @param $sid - service id
 * @param $time - time required
 * @param $price - price
 * @param $nid - node id if service already exists
 * @global $user - the Drupal global user object
 * @return boolean
 */
function create_a_service($sid, $time, $price, $nid) {
     global $user;
     //Let's short-circuit it here if there are empty values
     if ($sid == '0' || $time == '' || $price == '') {
          if ($nid == '') {
               return false; //never existed
          } else {
          //this node existed previously so we need to delete it
               node_delete($nid);
               return false;
          }
     }
     
     //Cast type to float to avoid Entity Wrapper error trying to push string into double
     $price = (float)$price;

     // @TODO: pull this dynamically from the taxonomy instead of
     // doing it by hand - we are trying to save time right now
     switch ($sid) {
          case '1':
               $service = "Shape Up / Line Up";
               break;
          case '2':
               $service = "Wash";
               break;
          case '3':
               $service = "Regular Cut";
               break;
          case '4':
               $service = "Kid Cut";
               break;
          case '5':
               $service = "Cut & Razor";
               break;
          case '6':
               $service = "Bigen Blackout";
               break;
          case '7':
               $service = "Designs";
               break;
          case '8':
               $service = "Misc";
               break;
     }

     if ($nid) { //existing node, need to update
          //this node will already be linked to the person's profile
          //so all we really need to do is update the information
          $node_load = node_load($nid);

          //Let's use an entity_metadat_wrapper to simplify getting and setting
          $ewrapper = entity_metadata_wrapper('node',$node_load);
          $ewrapper->title->set($service);
          $ewrapper->field_price->set($price);
          $ewrapper->field_service_type->set($sid);
          $ewrapper->field_typical_time_required->set($time);

          $ewrapper->save(true);
          entity_save('node', $node_load);
          
          return $node_load->nid;          
     } else {
          $values = array(
              'type' => 'service',
              'uid' => $user->uid,
              'status' => 1,
              'comment' => 1,
              'promote' => 0,
          );
          $entity = entity_create('node', $values);

          // Now create an entity_metadata_wrapper around the new node entity
          // to make getting and setting values easier
          $ewrapper = entity_metadata_wrapper('node', $entity);

          // Using the wrapper, we do not have to worry about telling Drupal
          // what language we are using. The Entity API handles that for us.
          // Now let's assign the values
          $ewrapper->title->set($service);
          $ewrapper->field_service_type->set($sid);
          $ewrapper->field_typical_time_required->set($time);
          $ewrapper->field_price->set($price);

          // Now just save the wrapper and the entity
          // There is some suggestion that the 'true' argument is necessary to
          // the entity save method to circumvent a bug in Entity API. If there is
          // such a bug, it almost certainly will get fixed, so make sure to check.
          $ewrapper->save(true);
          entity_save('node', $entity);
          return $entity->nid;
     }
}
//------------------------------------------------------------------------------
?>   

<!----------------------------------------------------------------------------->
<!--Now for the jQuery client-side processing-->
<script>
     jQuery(function() {

          //clone_and_rename is a utility function to encapsulate the cloning
          //of service entry divs and their proper renaming to ensure unique 
          //names/ids in the submitted form
          function clone_and_rename(sid, stime, sprice, nodeid) {
               if (!nodeid) {
                    sid = 0;
               }
               if( typeof clone_and_rename.counter == 'undefined' ) {
                    clone_and_rename.counter = 0;
               }
               clone_and_rename.counter++;
               
               var newElem = jQuery('#service-master').clone(true).attr({
                    id: 'service-'+clone_and_rename.counter,
                    name: 'service-'+clone_and_rename.counter,
				class: 'service',
                    style: 'display:block'
               });
               newElem.children('#type').prop({
                    id: 'type-' + clone_and_rename.counter,
                    name: 'type-' + clone_and_rename.counter,
                    value: sid
               });
               
               newElem.children("label[for='type']").attr("for", "type-" + clone_and_rename.counter);
               
               newElem.children('#time').attr({
                    id: 'time-' + clone_and_rename.counter,
                    name: 'time-' + clone_and_rename.counter,
				class: 'time',
                    value: stime
               });
               newElem.children("label[for='time']").attr("for", "time-" + clone_and_rename.counter);
               
               newElem.children('#price').attr({
                    id: 'price-' + clone_and_rename.counter,
                    name: 'price-' + clone_and_rename.counter,
                    class: 'price',
				value: sprice
               });
               newElem.children("label[for='price']").attr("for", "price-" + clone_and_rename.counter);
               
               newElem.children('#nid').attr({
                    id: 'nid-' + clone_and_rename.counter,
                    name: 'nid-' + clone_and_rename.counter,
                    value: nodeid
               });
               newElem.children("label[for='nid']").attr("for", "nid-" + clone_and_rename.counter);
               
               jQuery('.serviceInput:last').after(newElem);
               return clone_and_rename.counter;
          }
          //retrieve the existing_services array from the php array version of it
          var json = <?php echo (json_encode($jMultiArray)); ?>;
          //alert (j);
          for (x in json) {
               clone_and_rename(
                    json[x]['type'],
                    json[x]['time'],
                    json[x]['price'],
                    json[x]['nid']
               );
          }
          
          //make initial blank copy of the main service-master block
          clone_and_rename();
          
          //adds a div of entry controls for an additional service
          jQuery('#btnAdd').click(function() {
			// handle validation
			if ( jQuery( "#type-" + clone_and_rename.counter ).val() == 0 ) {
				alert( 'Please select a service' );
				jQuery( "#type-" + clone_and_rename.counter ).focus();
				return false;
			}
			if ( jQuery( "#time-" + clone_and_rename.counter ).val().length == 0 ) {
				alert( 'Please add a time first' );
				jQuery( "#time-" + clone_and_rename.counter ).focus();
				return false;
			}
			if ( jQuery( "#price-" + clone_and_rename.counter ).val().length == 0 ) {
				alert( 'If this is free, add a "0", if not add an amount' );
				jQuery( "#price-" + clone_and_rename.counter ).focus();
				return false;
			}
			clone_and_rename();
          });

          //deletes the associated service div
          jQuery('.remove').click(function() {
               var nid = (jQuery(this).siblings('input:hidden').val());
               if (nid == '') {
                    //this is an entry on the form only - not yet saved to db
                    //can delete without consequence
                    jQuery(this).parent().remove();
               } else {
                    if (confirm("This service is already saved to your profile - are you sure you want to remove it?")) {
                         //this entry is already a node, and this is its id
                         //so confirm before deleting then add this node id to 
                         //the 'delete' field for processing after POST
                         var del_list = jQuery("#delete").val(); //preserve existing deletes
                         jQuery("#delete").val(del_list + nid + "|"); //and add to it
                         jQuery(this).parent().remove(); //now we can remove the fields
                    }
               }
          });

		// validation stuff. revisit after push interim changes live - JPC 2013-03-05
          /*
		jQuery(".time").addClass( "validateNumberOptional" );
		jQuery(".price").addClass( "validateCurrencyOptional" );
		jQuery("form#service-form").miniValidate();
		jQuery("form#service-form").submit( function() {
               //alert("NO!");
               //return false;
			if ( jQuery( "#type-1").val() == 0 &&  ! confirm('You have not completed adding any services. Are you sure you want to continue?') ) {
                    jQuery( "#type-1").focus();
				return false;
			}
			if ( ! jQuery( "#time-1").val().length &&  ! confirm('You have not completed adding any services. Are you sure you want to continue?') ) {
                    jQuery( "#time-1").focus();
				return false;
			}
			if ( ! jQuery( "#price-1").val().length &&  ! confirm('You have not completed adding any services. Are you sure you want to continue?') ) {
                    jQuery( "#price-1").focus();
				return false;
			}
		}); */
     });
</script>
<script type="text/javascript" src='/<?php echo path_to_theme(); ?>/js/miniValidate-0.5.min.js'></script>
