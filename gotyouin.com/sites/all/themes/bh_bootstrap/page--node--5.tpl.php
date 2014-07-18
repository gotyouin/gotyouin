<?php
/**
 * @file page--node--5.tpl.php
 * @author Jenny Chalek, DBS>Interactive, 2013-1-14
 * 
 * Payment handling logic for recurring payments using PayPal Payflow Link
 * in coordination with a PayPal Advanced merchant account
 * Modification is based on default theme implementation 
 * to display a single Drupal page.
 * */

/**
 * ------------------------------------------------------------------------
 * Custom code for PayPal implementation - JPC 2013-01-15
 * @author Jenny Chalek <jenny@dbswebsite.com>
 * 
 * Code needs to handle the following tasks to set up recurring billing
 * 1.  Determine where we are in the process to know what to render.
 * 2.  Determine whether trial period is over.
 * 3.  Determine the price point for the subscription by account type.
 * 4.  Gather the associated billing address.
 * 5.  Present legal billing agreement to get explicit permission.
 * 6.  Request and receive a Secure Token using an initial sale for the 
 *     subscription amount in order to invoke the hosted PayPal checkout page.
 * 7.  Load hosted checkout page into iframe using the Secure Token.
 * 8.  Check for successful return - handle cancel or error - supply a GET
 *     url as the "return" url values depending on error/success/cancel.
 * 9.  Gather the PNREF returned by success response for use in converting
 *     to a recurring profile.
 * 10. Upon agreement, use PNREF and actual recurring transaction data
 *     (price, term, etc) to create a recurring profile. This causes PayPal
 *     to actually perform the first billing action.
 * 11. Retrieve the PROFILEID and database in Drupal user account.
 * 
 * KEY to related profile fields
 * field_paypalprofile - the actual profile id number within paypal
 * field_bill_date - the first "recurring" date generated when the profile 
 *                   is first converted from a sales transaction, updated 
 *                   to the next bill date upon successful billing - by 
 *                   checking the profile's status 4 days after, we can see
 *                   whether the account got cancelled and thus block the 
 *                   drupal account or demote the user to "must subscribe"
 * field_subscription_type - the code for the subscription, eg. IND_MONT
 * field_subscription_status - "ACTIVE" if active, all other statuses invalid
 * field_payment_status - the latest payment status as either recorded here
 *                        or queried in the cron - in the format 
 *                        RESULT||PAYMENTNUMBER||RESPMSG
 * ------------------------------------------------------------------------
 **/

/**
 * -----------------------------------------------------------------------------
 * Initial requirements and custom routing decisions for PayPal implementation
 * @author Jenny Chalek <jenny@dbswebsite.com>
 * -----------------------------------------------------------------------------
 * */
require_once('includes/PayflowNVPAPI.php'); //payflow api utility function file from paypal's website
require_once('includes/ppform.php'); //contains function to generate the billing form

//$rec = recurringDate('MONT');
//echo date_format($rec,'mdY');
//echo "<br />";
//echo date_format($rec,'Y-m-d');
//die();

//create/capture the profile object that we will need throughout
$uprofiles = profile2_load_by_user($user->uid);
if (!empty($uprofiles)) {
     if (array_key_exists('shop_owner',$uprofiles)) {
          $uprofile = $uprofiles['shop_owner'];
     } elseif (array_key_exists('independent_barber',$uprofiles)) {
          $uprofile = $uprofiles['independent_barber'];
     }
}
//if(isset($_POST)) var_dump($_POST);
/**
 * Short circuit the request right here if we just returned inside the payment iframe.
 * If we don't, the page will try to re-render the whole page in the iframe, 
 * causing an endless loop and risking the creation of a black hole.
 * Store payflow response and redirect parent window with javascript.
 */
if (isset($_POST['RESULT']) || isset($_GET['RESULT'])) {
     $response = array_merge($_GET, $_POST);
     $success = ($response['RESULT'] == 0);
     //echo $response['PPREF']."<br />";
     //var_dump($response);
     //die();

     if ($success) {
          echo "<span style='font-family:sans-serif;font-weight:bold;'>Transaction approved! Thank you for your subscription.</span>";
     } else {
          echo "<span style='font-family:sans-serif;'>Transaction failed! Please try again with another payment method.</span>";
     }
     
     //if we have gotten this far, we will have set the $_SESSION['subscription'] variable already
     $date_recurring = recurringDate(substr($_SESSION['subscription'],-4));
     
     if($response['TENDER'] == 'P') {
         $acct_convert = $response['PPREF'];
     } else {
         $acct_convert = $response['PNREF'];
     }
     
     $result = createRecurringProfile($response['AMT'], $response['TENDER'], 
             $acct_convert, $date_recurring, substr($_SESSION['subscription'],-4));
     
     $success = saveppProfile($uprofile, $result['PROFILEID'], $date_recurring, 
             $_SESSION['subscription'], $response['RESULT'].'||'.$response['RESPMSG'], $result);
     //handle fail errors here?
     exit(0);
}
$whichone = '';
global $environment; //this variable is used in the helper file
$environment = "live";
if ($environment == "sandbox" || $environment == "pilot") {
     $payflow_url = "https://pilot-payflowlink.paypal.com"; //this doesn't work
     $mode = 'TEST';
} else {
     $payflow_url = "https://payflowlink.paypal.com";
     $mode = 'LIVE';
}

//gather correct subscription information based on type of user
if (array_key_exists('4', $user->roles)) {
     $role = "a Shop Owner";
} elseif (array_key_exists('5', $user->roles)) {
     $role = "an Independent Barber";
} else {
     $whichone = "none";
}

$pp_form = '';

/**
 * Otherwise, process the other possibilities - either we need to render the
 * form to collect billing information, or generate a PayPal hosted checkout
 * page to collect payment
 */
if (isset($_POST['BILL_SUBMIT'])) {
     $whichone = 'payment';
     //need to generate a secure token and host the payment page
     if ($_GET['APP'] == 1) {
          $template = 'mobile';
     } else {
          $template = 'minLayout';
     }
     //need to "capture" the subscription type for use later in the process
     $_SESSION['subscription'] = $_POST['SUBSCRIPTION'];
     switch ($_SESSION['subscription']) {
          case 'TEST_MONT':
               $amount = "1.00";
               break;
          case 'IND_MONT':
               $amount = "25.00";
               break;
          case 'IND_SMYR':
               $amount = "135.00";
               break;
          case 'IND_YEAR':
               $amount = "255.00";
               break;
          case 'SHOP_MONT':
               $amount = "50.00";
               break;
          case 'SHOP_SMYR':
               $amount = "270.00";
               break;
          case 'SHOP_YEAR':
               $amount = "510.00";
               break;
     }
     $_SESSION['amount'] = $amount;
     $result = createSecureToken($template);
     //die();
     if ($result) {
          $securetoken = $_SESSION['securetoken'];
          $securetokenid = $_SESSION['securetokenid'];
          $pp_form = "<iframe src='" . $payflow_url;
          $pp_form .= "?SECURETOKEN=" . $securetoken . "&SECURETOKENID=" . $securetokenid . "&MODE=" . $mode . "' ";
          $pp_form .= "width='490' height='565' border='0' frameborder='0' ";
          //Change this one back to no scrolling when live - scrolls for testing
          //because this allows us to see the printed out server response
          $pp_form .= "scrolling='no' allowtransparency='true'>\n</iframe>";
          //$pp_form .= "scrolling='yes' allowtransparency='true'>\n</iframe>";          
     }
} elseif ($whichone != 'none') {
     $whichone = 'billing';
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
      <?php elseif ($title && $page['highlighted']): ?>
      <h2 class="title" id="page-title"><?php print $title; ?></h2>
      <?php endif; ?>
      <?php print render($title_suffix); ?>
      <?php if ($tabs['#primary'] != ''): ?><div class="tabs"><?php print render($tabs); ?></div><?php endif; ?>
      <?php print render($page['help']); ?>
      <?php if ($action_links): ?><ul class="action-links"><?php print render($action_links); ?></ul><?php endif; ?>
      <?php /* REMOVED AND MANUALLY INCLUDED BLOCK ID BELOW print render($page['content']); */ ?>
      <?php print $feed_icons; ?>      
    </div> 
    <?php if ($page['sidebar_second']): ?>
    <div class="span<?php print $sidebar_second_size; ?>">
      <?php print render($page['sidebar_second']); ?>
    </div>
    <?php endif; ?>
  </div><!-- .row -->
  <?php //if ($page['row_post_content']): print render($page['row_post_content']); endif; ?>
<div id="block-system-main" class="block block-system">
<div class="content">  
<?php
     //generate the billing information collection form
     if ($whichone == 'payment') {
          echo "<h4>I want to enroll as ".$role."</h4>";
          echo "<p>By continuing, I agree to the terms and conditions of this service.</p>";
          echo "<h5>Billing information (click the back button on your browser if incorrect):</h5>";
          echo $_POST['BILLTOFIRSTNAME'].' '.$_POST['BILLTOLASTNAME']."<br />";
          echo $_POST['BILLTOSTREET']."<br />";
          echo $_POST['BILLTOCITY'].' '.$_POST['BILLTOSTATE'].' '.$_POST['BILLTOZIP']."<br />";
          //need to "capture" the subscription type for use later in the process
          $_SESSION['SUBSCRIPTION'] = $_POST['SUBSCRIPTION'];
          echo $pp_form;
     } elseif ($whichone == 'billing') {
          //@TODO: add billing agreement, place to view terms and conditions, etc.
          echo "<h4>I want to enroll as ".$role."</h4>";
          echo "<p>By continuing, I agree to the terms and conditions of this service.</p>";
          echo buildForm($role, $uprofile);
          //include ('includes/ppform.html');
     } else {
          echo '<p style="text-align:center;">This service is always free for barbers&apos; customers. <br/>
               Did you want to register as a <a href="sowner/register">shop owner</a>
               or <a href="ibarber/register">independent barber</a>?<br/>
               If you already have an account, did you forget to <a href="/user/login">log in?</a></p>';
     }

     //MISCELLANEOUS UTILITY FUNCTIONS FOLLOW
     /**
      * Create a Secure Token in order to communicate with the hosted embedded
      * PayPal pages. Secure Tokens cannot be generated directly from a
      * transaction type "recurring" (TRXTYPE=R), so we are going to create a 
      * workaround using an initial "sale" transaction type (TRXTYPE=S) for 
      * the appropriate membership amount. This will give us a transaction 
      * that we can then convert to a recurring billing profile.
      * 
      * @params $amount - pass in the price of the subscription based on whether 
      * this is a shop or an independent barber.
      * $template - pass in the mobile template argument if mobile device
      * 
      * @return associative array $response containing 
      *   'RESULT' => string '0' for success
      *   'SECURETOKEN' => string
      *   'SECURETOKENID' => string
      *   'RESPMSG' => string 'Approved'
      **/
     function createSecureToken ($template='minLayout') {
          $request = array(
               "PARTNER" => "PayPal",
              /*-------------------------------------------*/
              /* replace with real account info to go live */
               "VENDOR" => "johnmark",
               "USER" => "johnmark",
               "PWD" => "litm0113",
              /*-------------------------------------------*/
               "TRXTYPE" => "S",
               "AMT" => $_SESSION['amount'],
               "CURRENCY" => "USD",
               "CREATESECURETOKEN" => "Y",
               "SECURETOKENID" => uniqid('DBSSecTokenID-'), //unique, never used before
              
              /*-------------------------------------------*/
              /* dynamic version */
               "RETURNURL" => $GLOBALS['base_url']."/subscribe",
               "CANCELURL" => $GLOBALS['base_url']."/subscribe",
               "ERRORURL" => $GLOBALS['base_url']."/subscribe",
              /*-------------------------------------------*/
              
              /*-------------------------------------------*/
              /* local version */
//               "RETURNURL" => "http://gotyouin.loc/subscribe",
//               "CANCELURL" => "http://gotyouin.loc/subscribe",
//               "ERRORURL" => "http://gotyouin.loc/subscribe",
              /*-------------------------------------------*/

               "BILLTOFIRSTNAME" => $_POST['BILLTOFIRSTNAME'],
               "BILLTOLASTNAME" => $_POST['BILLTOLASTNAME'],
               "BILLTOSTREET" => $_POST['BILLTOSTREET'],
               "BILLTOCITY" => $_POST['BILLTOCITY'],
               "BILLTOSTATE" => $_POST['BILLTOSTATE'],
               "BILLTOZIP" => $_POST['BILLTOZIP'],
               "BILLTOCOUNTRY" => $_POST['BILLTOCOUNTRY'],
               "EMAIL" => $user->mail,
               "TEMPLATE" => $template,
          );
          
          $response = run_payflow_call($request);

          if ($response['RESULT'] != 0) {
               //pre($response, "Payflow call failed");
              echo "Sorry, there was an error. Try going back to the previous page.";
               return false;
          } else {
               $_SESSION['securetoken'] = $response['SECURETOKEN'];
               $_SESSION['securetokenid'] = $response['SECURETOKENID'];
               $_SESSION['result'] = $response['RESULT'];
               $_SESSION['respmsg'] = $response['RESPMSG'];
               return true;
          }
     }
     
     /**
      * Code to create recurring profile from successful sales transaction
      * @param $amt
      * @param $tender
      * @param $pnref
      * @param $date_recurring
      * @param $payperiod
      * @return payflow call result
      */
     function createRecurringProfile($amount, $tender, $pnref, $date_recurring, $payperiod){
          $request = array(
              "PARTNER" => "PayPal",
              /* ------------------------------------------- */
              /* replace with real account info to go live */
              "VENDOR" => "johnmark",
              "USER" => "johnmark",
              "PWD" => "litm0113",
              /* ------------------------------------------- */
              "TRXTYPE" => "R",
              "ACTION" => "A",
              "AMT" => $_SESSION['amount'],
              "TENDER" => $tender,
              "ORIGID" => $pnref,
              "PAYPERIOD" => $payperiod,
              "TERM" => 0, /*recurring until explicitly canceled*/
              "START" => date_format($date_recurring,'mdY'),
              "PROFILENAME" => $_SESSION['subscription'],
              "RETRYNUMDAYS" => 2, /*will retry 2 days in a row for a failed payment*/
              "MAXFAILPAYMENTS" => 1, /*will cancel the account once retry days are over*/
          );
 
          $result = run_payflow_call($request);
          return($result);
     }
     
     /**
      * Generates a recurring date for monthly billing based on the day's date, 
      * and allowing for changing end-of-month dates to the 1st of the next 
      * month so recurring billing will always work regardless of month length
      * @return string representation of the generated date
      */
     function recurringDate ($period) {
          switch ($period) {
               case 'MONT':
                    $mod = '+1 month';
                    break;
               case 'SMYR':
                    $mod = '+6 month';
                    break;
               case 'YEAR':
                    $mod = '+1 year';
                    break;
          }
          $date_today = date_create();
          $year = date_format($date_today,'Y');
          $month = date_format($date_today,'m');
          $day = date_format($date_today,'d');
          if ($day < 29) {
               //add the appropriate time period, no need to adjust the date
               $date_bill = date_modify($date_today, $mod);
          } else {
               //go to the first of the month immediately following
               //then add the time period to the date
               $day = 1;
               $month++;
               if ($month > 12) {
                    $month = $month - 12;
                    $year++;
               }
               $date_bill = date_create($year.'-'.$month.'-'.$day);
               $date_bill = date_modify($date_bill, $mod);
          }
          return $date_bill;
          return date_format($date_bill,'mdY'); //string representation of date
     }

     /**
      * Saves the paypal profile ID and billing date in the user's associated
      * profile2 profile, which depends on which profile2 they have
      * @param $userid = uid of logged in user
      * @param $profileID
      * @param $bill_date
      */
     function saveppProfile ($uprofile, $profileID, $bill_date, $subscription_type, $payment_status, $resp_array) {
          if (!empty($uprofile)) {
               $uprofile->field_paypalprofile['und']['0']['value'] = $profileID;
               $uprofile->field_bill_date['und']['0']['value'] = date_format($bill_date,"Y-m-d");
               $uprofile->field_subscription_type['und']['0']['value'] = $subscription_type;
               $uprofile->field_payment_status['und']['0']['value'] = $payment_status;
               if($resp_array['RESULT'] == 0){
                    $uprofile->field_subscription_status['und']['0']['value'] = 'ACTIVE';
               } else {
                    $uprofile->field_subscription_status['und']['0']['value'] = $resp_array['RESPMSG'];
               }
               return profile2_save($uprofile);
          } else {
               return false;
          }
     }
?>
</div>
</div>
</div>
<?php include('footer.php'); ?>
<script type='text/javascript'>
     jQuery(function() {
         
    });   
</script>
