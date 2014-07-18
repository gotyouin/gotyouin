<?php
/**
 * @file paypal_cron.php
 * @author Jenny Chalek, DBS>Interactive, 2013-03-19
 * 
 * This script is designed to be called from cron, and will take care of several
 * recurring paypal tasks, including
 * 1. Determine which customers are at the end of the 30 day trial, and send
 *    email telling them to subscribe, complete with link to subscription page
 * 2. Determine which customers are in arrears, either because:
 *    a. They never followed through with subscribing
 *    b. Their payment has been declined - maybe choose x number of times before
 *       this gets "invoked."
 *    c. They have cancelled the account via the website or via paypal
 *    d. They have filed a "chargeback" complaint
 * 3. With above mentioned arrears customers, escalating system to 
 *    a. Email a nagging reminder
 *    b. Cancel/block account after x number of "chances"
 */

// necessary for cron since db in use is triggered based on HTTP_HOST!
if ( !empty( $argv[1] ) && $argv[1] == 'live' || strstr( dirname( __FILE__ ), 'gotyouin.com' ) ) {
	$_SERVER['HTTP_HOST'] = 'gotyouin.com';
    $subscribe_link = "<a href='http://www.gotyouin.com/subscribe'>subscribe</a>";
} else {
    $subscribe_link = "<a href='http://staging305.resultsbydesign.com/subscribe'>subscribe</a>";
}

$_SERVER['REMOTE_ADDR'] = '0.0.0.0';
define('DRUPAL_ROOT', dirname(  dirname( __FILE__ ) ) );
require_once DRUPAL_ROOT . '/includes/bootstrap.inc';
drupal_bootstrap(DRUPAL_BOOTSTRAP_FULL);
require_once(DRUPAL_ROOT.'/sites/all/themes/bh_bootstrap/includes/PayflowNVPAPI.php'); //payflow api utility function file from paypal's website
//echo DRUPAL_ROOT;
//var_dump ($_SERVER);
//echo $subscribe_link;
//die();

//$user_test = user_load('192');
//var_dump ($user_test);
//die();

//Set up some reference dates
$ref_date = date_create();
$today = date_format($ref_date, 'Y-m-d H:i:s');
date_sub($ref_date, new DateInterval('P3D'));
$bill_date = date_format($ref_date, 'Y-m-d H:i:s');

$m = date_create();
date_add($m, new DateInterval('P1M'));
date_sub($m, new DateInterval('P3D'));
$mont = date_Format($m, 'Y-m-d H:i:s');

$s = date_create();
date_add($s, new DateInterval('P6M'));
date_sub($s, new DateInterval('P3D'));
$smyr = date_Format($s, 'Y-m-d H:i:s');

$y = date_create();
date_add($y, new DateInterval('P1Y'));
date_sub($y, new DateInterval('P3D'));
$year = date_Format($y, 'Y-m-d H:i:s');

$ts = date_create();
date_sub($ts, new DateInterval('P28D'));
$ts28 = date_timestamp_get($ts);
date_sub($ts, new DateInterval('P3D'));
$ts31 = date_timestamp_get($ts);

//evaluateSubscriptions($bill_date, $mont, $smyr, $year);
//deactivateExpiredTrials($ts31);
warnExpiringTrials($ts28, $subscribe_link);
//warnFailingSubscriptions($today);

/**
 * Check for 'ACTIVE' subscriptions 3 days past the bill date to determine 
 * whether to cancel the account or set the next billing date.
 */
function evaluateSubscriptions ($bill_date, $mont, $smyr, $year) {
     $query = new EntityFieldQuery();
     $query->entityCondition('entity_type', 'user')
           /* we only want to pull users who haven't been blocked/unsubscribed */
           ->propertyCondition('status', 1);
     $result = $query->execute();
     $uids = array_keys($result['user']);
     
     $bundle = array('shop_owner','independent_barber');
     $query = new EntityFieldQuery();
     $query->entityCondition('entity_type', 'profile2')
             ->entityCondition('bundle', $bundle)
             ->propertyCondition('uid',$uids,'IN')
             ->fieldCondition('field_bill_date', 'value', $bill_date, '<')
             ->fieldCondition('field_subscription_status', 'value', 'ACTIVE', '=');
     $result = $query->execute();
     
     if (isset($result['profile2'])) {
         foreach ($result['profile2'] as $pid=>$value) {
              $profile2 = profile2_load($pid);
              $pp_profile = $profile2->field_paypalprofile;
              $pp_status = PaypalStatus($pp_profile);
              if (pp_status != 'ACTIVE') {
                   $profile2->field_subscription_status['und']['0']['value'] = $pp_status;
                   profile2_save($profile2);
                   $user_acct = user_load($profile2->uid);
                   user_block_user_action($user_acct);
                   //cut these people off, email them to let them know
                   //with instructions to call and reinstate?
                   //maybe include the paypal failure reason?
                   $msg = "Your GotYouIn account has been canceled. ";
                   $msg .= "You will not be billed any additional payments.<br /><br />";
                   $msg .= "If you did not mean to cancel, please call customer ";
                   $msg .= "service at 502-414-1541 for assistance.<br /><br />";
                   mailReminder('GotYouIn Account Canceled',$user_acct, $msg);
              } else {
                   $sub_period = substr($profile2->field_subscription_type, -4);
                   switch ($sub_period) {
                        case 'MONT':
                             $profile2->field_bill_date['und']['0']['value'] = $mont;
                             break;
                        case 'SMYR':
                             $profile2->field_bill_date['und']['0']['value'] = $smyr;
                             break;
                        case 'YEAR':
                             $profile2->field_bill_date['und']['0']['value'] = $year;
                             break;
                   }
                   profile2_save($profile2);
              }
         }
     }
}

function deactivateExpiredTrials($timestamp){
     //find $timestamp - 31 days
     $query = new EntityFieldQuery();
     $query->entityCondition('entity_type', 'user')
           ->entityCondition('created', 'value', $timestamp, '<=')
           /* we only want to pull users who haven't been blocked/unsubscribed */
           ->propertyCondition('status', 1);
           /* the person didn't subscribe, this is the cutoff date */
     $result = $query->execute();
     //var_dump($result['user']);
     $uids = array_keys($result['user']);
     //var_dump($uids);
     //die();
     
     $bundle = array('shop_owner','independent_barber');
     $query = new EntityFieldQuery();
     $query->entityCondition('entity_type', 'profile2')
             ->entityCondition('bundle', $bundle)
             ->propertyCondition('uid',$uids,'IN')
             ->fieldCondition('field_subscription_status', 'value', 'PENDING', '=');
     $result = $query->execute();
     //var_dump($result);
     //die();
     
     foreach ($result['profile2'] as $pid=>$value) {
          $profile2 = profile2_load($pid);
          $user_acct = user_load($profile2->uid);
          user_block_user_action($user_acct);
          //cut these people off, email them to let them know
          //with instructions to call and reinstate?
          $msg = "Your GotYouIn account has been canceled. ";
          $msg .= "Your trial subscription has ended and your account has been canceled.<br /><br />";
          $msg .= "If you did not mean to cancel, please call customer ";
          $msg .= "service at 502-414-1541 for assistance.<br /><br />";
          mailReminder('GotYouIn Trial Ended',$user_acct, $msg);
     }
}

function warnExpiringTrials($timestamp, $subscribe_link) {
     //find $timestamp - 28 days
     $query = new EntityFieldQuery();
     $query->entityCondition('entity_type', 'user')
           ->entityCondition('created', 'value', $timestamp, '<=')
           /* we only want to pull users who haven't been blocked/unsubscribed */
           ->propertyCondition('status', 1);
     $result = $query->execute();
     $uids = array_keys($result['user']);
     
     $bundle = array('shop_owner','independent_barber');
     $query = new EntityFieldQuery();
     
     $query->entityCondition('entity_type', 'profile2')
           ->entityCondition('bundle', $bundle)
           ->propertyCondition('uid',$uids,'IN')
           //->fieldCondition('field_subscription_status', 'value', 'JPCTEST', '=');
           ->fieldCondition('field_subscription_status', 'value', 'NEW', '=');
               /* the person has never subscribed, trial period ending
                * time to email a warning/reminder to subscribe */
     $result = $query->execute();
     
     foreach ($result['profile2'] as $pid=>$value) {
          $profile2 = profile2_load($pid);
          $profile2->field_subscription_status['und']['0']['value'] = 'PENDING';
          profile2_save($profile2);
          $user_acct = user_load($profile2->uid);
          //email these people to let them know they need to subscribe
          
          $msg = "Thank you for using Got You In, ";
          $msg .= "\"The world's 1st universal scheduling app, specifically designed for barbers!\" ";
          $msg .= "Your free trial is due to end soon, ";
          $msg .= "so take a quick minute to $subscribe_link. ";
          $msg .= "This ensures new customers can find you through the app and your current ";
          $msg .= "customers continue to seamlessly book appointments ";
          $msg .= "with you through their mobile app. This is a one time process. <br /><br />";
          $msg .= "Also, make sure you're taking advantage of our unique features: ";
          $msg .= "<ol><li>Encourage all of your customers to rate you within the app. ";
          $msg .= "Then, people looking for a barber will see you're a 5-Star Barber. </li>";
          $msg .= "<li>Manage your social media accounts within Got You In. ";
          $msg .= "You can manage Facebook, Twitter, Linked, etc. while ";
          $msg .= "you're in your Got You In account </li></ol>";
          $msg .= "Note: If you are not logged in, you will be prompted to do so. ";
          $msg .= "You can then choose the big red SIGN UP button, and choose ";
          $msg .= "the big blue ENROLL NOW option at the top to return to that page. <br /> <br />";
          
          //simply log in to your account, click the big red SIGN UP button, and choose the big blue ENROLL NOW option
          
          mailReminder('GotYouIn Free Trial Ending',$user_acct, $msg);          
     }
}

function warnFailingSubscriptions($today){
     //NOT USED AT THIS TIME - CODE STUB FOR LATER DEVELOPMENT
     //email them to let them know their payment failed or whatever?
     //maybe not, since Paypal will try again for 3 days
     $bundle = array('shop_owner','independent_barber');
     $query = new EntityFieldQuery();
     $query->entityCondition('entity_type', 'profile2')
             ->entityCondition('bundle', $bundle)
             ->fieldCondition('field_bill_date', 'value', $bill_date, '<')
             ->fieldCondition('field_subscription_status', 'value', 'ACTIVE', '=');
     $result = $query->execute();
     foreach ($result['profile2'] as $pid=>$value) {
          $profile2 = profile2_load($pid);
          $pp_profile = $profile2->field_paypalprofile;
          $pp_status = PaypalStatus($pp_profile);
          if (pp_status != 'ACTIVE') {
               $profile2->field_subscription_status = $pp_status;
               profile2_save($profile2);
               $user_acct = user_load($profile2->uid);
               $email = $user_acct->mail;
               //cut these people off, email them to let them know
               //with instructions to call and reinstate?
               //maybe include the paypal failure reason?
          }          
     }
}

/**
 * PaypalStatus
 * Call this function to evaluate the status of an individual paypal account
 * @param type string $recurring_ID - paypal's recurring account # for billing
 */
function PaypalStatus($recurring_ID, $p_history='N') {
     $request = array(
         "PARTNER" => "PayPal",
         /* ------------------------------------------- */
         /* replace with real account info to go live */
         "VENDOR" => "johnmark",
         "USER" => "johnmark",
         "PWD" => "litm0113",
         /* ------------------------------------------- */
         "TRXTYPE" => "R",
         "ACTION" => "I",
         "PAYMENTHISTORY" => $p_history,
         "ORIGPROFILEID" => $recurring_ID,
     );
     
     $response = run_payflow_call($request);
     return $response['STATUS'];
     //testing
}

function mailReminder($subject, $user_acct, $msg) {
     $display = $user_acct->field_first_name['und']['0']['value'];
     $display .= " ".$user_acct->field_last_name['und']['0']['value'];
     
     $to = $user_acct->mail;
     $from = "Got You In <noreply@gotyouin.com>";
     
     $body = "Dear ".$display.":<br /><br />";
     $body .= $msg;
     $body .= "Sincerely, Got You In!";
     
     $params = array(
         'subject' => $subject,
         'body' => $body,
         'from' => $from,
         'headers' => array(
             'From' => $from,
             'Sender' => $from,
             'Return-Path' => $from,
             'Bcc' => 'jenny@dbswebsite.com',
             'Bcc' => 'hal@garth2.resultsbydesign.com',
         ),
     );
     
     $result = drupal_mail(
          'htmlmail', 'reminder', $to, language_default(), $params
     );
}