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
      $profile2 = profile2_load_by_user($node->uid);
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
      //capture the recurring appointment details for use in the jquery delete handler
      $token = $node->field_recurring_token['und'][0]['value'];
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
          if ($_POST['delayed_cancel'] == 'true') {
              $_POST['op'] = 'Delete';
          }
           switch ($_POST['op']) {
                case 'Save Changes':
               //process the form
                     if ($_POST['customer-edit'] != null) {
                          $node->field_customer_name['und'][0]['value'] = $_POST['customer-edit'];
                          $node->field_customer['und'][0]['target_id'] = '0';
                          node_save($node);
                     }

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

                     if ($status == 'keep' || $status == 'add') {
                          $new_array[$array_key]['target_id'] = $svc_id;
                          $array_key++;
                     } elseif (!$status) {
                         //we've gone past the last one
                          break;
                     }
                }
                $date = strtotime( $_POST['datetime'] );
                $node->field_desired_services['und'] = $new_array;
                $node->field_date['und'][0]['value'] = date('Y-m-d H:i:s',$date);
                node_save($node);
                break;
           case 'Delete':
                //delete confirmation has already happened in jQuery
                //test for and deal with recurring appointments
                $token = $node->field_recurring_token['und'][0]['value'];
                switch ($_POST['token']) {
                    case 'No':
                        //not a recurring appointment, dead easy to deal with
                        node_delete($node->nid);
                        break;
                    case 'ONE':
                        //recurring appointment but we only want to cancel this one - not the whole series
                        //we are deactivating the node instead of deleting it so it can serve as
                        //a bookmark to make sure the cron doesn't put it back
                        //first, check to see if this has a status of NEW - which means it was
                        //next in line to generate future appointments - and that status will have to
                        //be shifted forward to the next valid appointment
                        if ($node->field_recurring_status['und'][0]['value'] == 'NEW') {
                            //this was "next in line", need to shift the NEW flag to next appt
                            //but can't do that until the end of the enclosed loop
                            $date_search = $node->field_date['und'][0]['value'];
                            $query = new EntityFieldQuery();
                            //find active appointments in the chain past the date of this appointment
                            //the first active one we find gets the NEW designation
                            $query->entityCondition('entity_type', 'node')
                                ->entityCondition('bundle', 'booking')
                                ->propertyCondition('status', 1)
                                ->fieldCondition('field_date', 'value', $date_search, '>')
                                ->fieldCondition('field_recurring_token', 'value', $token, '=');
                            $result = $query->execute();
                            if (isset($result['node'])) {
                                $a_keys = array_keys($result['node']);
                                $r_node = node_load($a_keys[0]); //we only need the first node
                                $r_node->field_recurring_status['und'][0]['value'] = 'NEW';
                                node_save($r_node);
                            }
                        }
                        $node->field_recurring_status['und'][0]['value'] = 'CANCELED';
                        $node->status = '0';
                        node_save($node);
                        break;
                    case 'ALL':
                        //build and execute query to find all recurring appointments in the series
                        $query = new EntityFieldQuery();
                        $query->entityCondition('entity_type', 'node')
                            ->entityCondition('bundle', 'booking')
                            ->fieldCondition('field_recurring_token', 'value', $token, '=');
                        $result = $query->execute();
                        if (isset($result['node'])) {
                            //gather and delete them
                            $keys = array_keys($result['node']);
//                            var_dump($result['node']);
//                            var_dump ($keys);
//                            die();
                            //$etids = entity_load('node', $keys);
                            node_delete_multiple($keys);
                            unset ($result, $keys);
                            //unset($etids);
                        }
                        break;
                    }

                
                echo '<script type="text/javascript">';
                echo 'window.location = "' . $return_page . '"';
                echo '</script>';
                break;
           case 'Cancel Edit':
           case 'Finished':
                echo '<script type="text/javascript">';
                echo 'window.location = "' . $return_page . '"';
                echo '</script>';
                break;
      
      }
      }

 $url = $_SERVER['REQUEST_URI'];
 $url_array = explode('?', $url);
//The basic query string will tell us whether we are on "edit" or "view"
//(this template controls both
 if (substr($url_array[0], -4) == 'edit') { //this is an edit screen
//put all the edit-specific variable values here
      $edit = true;
      $cols = 'cols-4';
 } else { //this is a view screen
//put all the view-specific variable values here
      $edit = false;
      $cols = 'cols-3';
 }

 /**
  * This node template controls the tabs for an individual booking node
  * Default is "view," the $edit flag is for an edit page
  * @author Jenny Chalek 2013-02-28
  */
 if ($edit) {
      ?>
      <form class="form-horizontal" method="post" id="booking-node-form" accept-charset="UTF-8">
          <input name="token" id="token" type="hidden" value="<?php echo $token; ?>" />
      <?php
      }
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
      echo "</h4>";

      echo '<div class="views-field views-field-edit-customer">';
      echo '&nbsp;<i class="icon-pencil"></i><span> (click to change)</span></div>';
      echo '<input id="customer-edit" name="customer-edit" type="text">';

      echo "<h5>Appointment Date and Time: ";

      $jsonDate = date('M d, Y g:i a', strtotime($node->field_date['und'][0]['value']));

      if ($edit) {
           echo "</h5>";
           echo '<div class="views-field views-field-edit-node">';
           echo '&nbsp;<i class="icon-pencil"></i><span> (click to edit)</span></div>';
           echo '<input name="datetime" id="datetime" class="i-txt" />';
      } else {
           echo date('M d, Y - g:ia', strtotime($node->field_date['und'][0]['value']));
           echo "</h5>";
      }

     //gather chosen desired services - we only need the node ids to sort through
     //which services are chosen. start with an empty array
      $services = array();
      foreach ($node->field_desired_services['und'] as $value) {
           if ($value['target_id'] != 0) {
                $services[] = $value['target_id'];
           }
      }
      ?>

      <table class="views-table <?php echo $cols; ?> table">
           <thead>
                <tr>
                     <th>Service</th>
                     <th>Time Required</th>
                     <th>Price</th>
                     <?php
                     if ($edit) {
                          echo "<th>+/-</th>";
                     }
                     ?>
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
          //determine whether the service is actively chosen for this appointment or not
                if (in_array($target_value->nid, $services)) {
                     $active = true;
                } else {
                     $active = false;
                }

               //display an active service whether or not it's the edit screen
               //display a not-active service only if it's the edit screen
                if ($active || $edit) {
                     if ($edit) {
                          if ($active) {
                               $stat = 'keep';
                               $icon = '<td class="views-field views-field-delete-node">';
                               $icon .= '<div class="round-btn"><i class="icon-plus hidden icon-white"></i><i class="icon-trash icon-white"></i></div> <span class="hidden">Add</span><span class="hidden">Delete</span></td>';
                          } else {
                               $stat = 'na';
                               $icon = '<td class="views-field views-field-add-node">';
                               $icon .= '<div class="round-btn"><i class="icon-plus icon-white"></i><i class="icon-trash hidden icon-white"></i></div> <span class="hidden">Add</span><span class="hidden">Delete</span></td>';
                          }
                     }
                     echo "<tr class='".$stat."'>";
                     echo "<td>" . $target_value->title . "</td>";
                     echo "<td>" . $target_value->field_typical_time_required['und'][0]['value'] . " minutes</td>";
                     echo "<td>$" . $target_value->field_price['und'][0]['value'] . "</td>";
                     if ($edit) {
                          echo '<input type="hidden" id="svc' . $count . '" name="svc-' . $count . '" value="' . $target_value->nid . '" />';
                          echo '<input type="hidden" id="stat" name="stat-' . $count . '" value="' . $stat . '" />';
                          $count++;
                          echo $icon;
                     }
                     echo "</tr>";
                     if ($active) { //adds to the running total of time and price at the bottom
                          $total_time += $target_value->field_typical_time_required['und'][0]['value'];
                          $total_price += $target_value->field_price['und'][0]['value'];
                     }
                }
           }
           ?>
           <input type="hidden" name="delayed_cancel" id="delayed_cancel" value="" />
           <tfoot>
                <tr>
                     <td><strong>TOTAL:</strong></td>
                     <td><strong><?php echo $total_time; ?> minutes</strong></td>
                     <td><strong>$<?php echo number_format($total_price, 2); ?></strong></td>
                     <?php if ($edit) { ?>
                          <td></td>
<?php } ?>
                </tr>
           </tfoot>
      </table> 

      <?php if ($edit) { ?>
           <div class="form-actions form-wrapper" id="edit-actions">
                <input class="btn form-submit" type="submit" id="edit-submit" name="op" value="Save Changes">
                <input class="btn form-submit" type="submit" id="edit-cancel" name="op" value="Cancel Edit">
                <input class="btn form-submit" type="submit" id="edit-finish" name="op" value="Finished">
                <input class="btn form-submit" type="submit" id="edit-delete" name="op" value="Delete">
           </div>
      </form>
 <?php }

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

<script type='text/javascript'>
     jQuery(function() {
         var newdate = new Date("<?php echo $jsonDate; ?>");
          var now = new Date();             
          jQuery('#datetime').mobiscroll().datetime({
              /*minDate: new Date("March 11, 2013"),*/
              minDate: now,
              dateOrder: 'mmddyyyy',
              theme: 'default',
              display: 'inline',
              mode: 'scroller'
          });
          
          jQuery('#datetime').mobiscroll('setDate',newdate,true);
          
          jQuery(".dw-inline").hide();
          
          jQuery("#customer-edit").hide();
          
          jQuery("#page-title").text("Booking Details");

          //alert('test1');
               
          jQuery(".views-field-delete-node").click(
               function(){
                    // alert('test2');
                    if (jQuery(this).parent().hasClass("keep")){
                         jQuery(this).parent().removeClass("keep");
                         jQuery(this).parent().addClass("del");
                         jQuery(this).parent().children("#stat").val("del");
                         jQuery(this).find('.icon-plus').removeClass('hidden');
                         jQuery(this).find('.icon-trash').addClass('hidden');
                    } else {
                         jQuery(this).parent().addClass("keep");
                         jQuery(this).parent().removeClass("del");
                         jQuery(this).parent().children("#stat").val("keep");
                         jQuery(this).find('.icon-plus').addClass('hidden');
                         jQuery(this).find('.icon-trash').removeClass('hidden');
                    }
               }
          );
              
          jQuery(".views-field-add-node").click(
               function(){
                    //alert('test3');
                    if (jQuery(this).parent().hasClass("na")){
                         jQuery(this).parent().children("#stat").val("add");
                         jQuery(this).parent().addClass("add");
                         jQuery(this).parent().removeClass("na");
                         jQuery(this).find('.icon-plus').addClass('hidden');
                         jQuery(this).find('.icon-trash').removeClass('hidden');
                    } else {
                         jQuery(this).parent().children("#stat").val("add");
                         jQuery(this).parent().addClass("na");
                         jQuery(this).parent().removeClass("add");
                         jQuery(this).find('.icon-plus').removeClass('hidden');
                         jQuery(this).find('.icon-trash').addClass('hidden');
                    }
               }
          );
          
//          jQuery("tr").children("#stat:hidden[value='keep']").parent().css({
//                'background-color' : 'rgba(0, 0, 0, 0)', 
//                'color' : 'black'
//            });
//          jQuery("tr").children("#stat:hidden[value='na']").parent().css({
//                'background-color' : 'rgba(0, 0, 0, 0)', 
//                'color' : 'gray'
//            });
          
          jQuery(".views-field-edit-node").click(
          function(){
               jQuery(".dw-inline").toggle();
          });
          
          jQuery(".views-field-edit-customer").click(
          function(){
               jQuery("#customer-edit").toggle();
          });
          
          jQuery("#edit-delete").click(
          function(){
              
              
            
              recur = false;
              type = 'No';
              if (jQuery("#token").val() != "No" && jQuery("#token").val() != "") {
              
                jQuery( "#dialog" ).dialog({
                    resizable: false,
                    height:300,
                    modal: true,
                    buttons: {
                        "Cancel all appointments": function() {
                            jQuery( this ).dialog( "close" );
                            var x=confirm("Are you sure you want to cancel ALL of these appointments?")
                            if (x==true)
                            {
                                type = 'ALL';
                                recur = true;
                                jQuery("#token").val(type);
                                jQuery("#delayed_cancel").val("true");
                                jQuery("#booking-node-form").submit();
                            }    
                        },
                        "Only this appointment": function() {
                            jQuery( this ).dialog( "close" );
                            var x=confirm("Would you really like to cancel this appointment?")
                            if (x==true)
                            {
                                type = 'ONE';
                                jQuery("#token").val(type);
                                jQuery("#delayed_cancel").val("true");
                                jQuery("#booking-node-form").submit();
                            }    
                        },
                        Cancel: function() {
                            jQuery( this ).dialog( "close" );
                        }
                    }
                });
                return false; //we're not going to process this in real time
                
//                  if (confirm("This appointment is part of a recurring series - do you want to delete all the other appointments in the series also? Choose Cancel to delete only this appointment.")) {
//                      type = 'ALL';
//                      recur = true;
//                  } else {
//                      type = 'ONE';
//                  }
                  
              } else {
                if (!confirm ("Would you really like to cancel this appointment?")) {
                      return false;
                 }
              }
               
               jQuery("#token").val(type);
          });
          
          jQuery("#edit-cancel").click(
          function(){
               window.location = "<?php echo $return_page; ?>";
               return false;
          });
     });
</script>
