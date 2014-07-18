<?php
/**
 * @file email_reminders.php
 * @author Jenny Chalek, DBS>Interactive, 2013-01-25
 * 
 * This script is designed to be called from cron, and will send email
 * reminders to customers on the day of their appointments and push any
 * notifications to the phone (for appointments, etc.)
 *
 * See /etc/cron.d/dbs_clients, run once daily.
 */


// necessary for cron since db in use is triggered based on HTTP_HOST!
if ( !empty( $argv[1] ) && $argv[1] == 'live' ) { 
	$_SERVER['HTTP_HOST'] = 'gotyouin.com';
}


# Quiet notices 2013-02-12:
$_SERVER['REMOTE_ADDR'] = '0.0.0.0';

define('DRUPAL_ROOT', dirname(  dirname( __FILE__ ) ) );
require_once DRUPAL_ROOT . '/includes/bootstrap.inc';
drupal_bootstrap(DRUPAL_BOOTSTRAP_FULL);

/**
 * Ideally, we will run this script with cron at 8:00 am each day to send out 
 * email reminders for all bookings for the next 24 hours. 
 */
$now = date_create(); //find out when "now" is

$tomorrow = date_create();
date_add($tomorrow,new DateInterval('P1D')); //find out when 24 hours from "now" is

$yesterday = date_create();
date_sub($yesterday,new DateInterval('P1D')); //find out when "yesterday" is

$range_appt = array(date_format($now, 'Y-m-d H:i:s'),date_format($tomorrow, 'Y-m-d H:i:s'));
$range_rating = array(date_format($yesterday, 'Y-m-d H:i:s'),date_format($now, 'Y-m-d H:i:s'));

$today = date( 'Y-m-d' ); //we will simply gather the date for use with phone notifications

//build our query to search through bookings for same day notification
$query = new EntityFieldQuery();
$query->entityCondition('entity_type', 'node')
        ->entityCondition('bundle', 'booking')
        ->fieldCondition('field_date', 'value', $range_appt, 'BETWEEN');
        //->fieldCondition('field_date', 'value', '2013-04-13 00:00:00', '<=');
$result = $query->execute();

foreach (array_keys($result['node']) as $nid) {
     $node = node_load($nid);
     $barberid = $node->field_barber['und']['0']['target_id'];
     $userid = $node->field_customer['und']['0']['target_id'];
     $date = $node->field_date['und']['0']['value'];
     
     $customer = user_load($userid);
     $barber = user_load($barberid);
     mailReminder($date,$customer,$barber);
     
     /**
      * Hal's phone notification code - writes to database
      */
     if ($userid) { //don't even try if $userid is empty - some orphaned data was causing errors
         try {
              // run the same query again, and update the processed date string to show we already grabbed this one.
              // TODO: retest changed query 2013-02-08
              $result = db_query(" INSERT INTO gotyouin_notifications
                   SET
                        message = 'You have an appointment for today', notification_date = '$today', uid = $userid
                   "
                   ) ;

               if ( $result->rowCount() !=  1 ) {
                   error_log( "Notification Error: mismatch rowCount vs number of notifications" );
               }

         } catch ( Exception $e ) {

              error_log( 'Error on setting appointment notification: query string = ' .$query_string. ' ' . $e );
         }
     }
}

//build our query to search through bookings for next day ratings reminders
$query = new EntityFieldQuery();
$query->entityCondition('entity_type', 'node')
        ->entityCondition('bundle', 'booking')
        ->fieldCondition('field_date', 'value', $range_rating, 'BETWEEN');
        //->fieldCondition('field_date', 'value', '2013-04-13 00:00:00', '<=');
$result = $query->execute();

foreach (array_keys($result['node']) as $nid) {
     $node = node_load($nid);
     $barberid = $node->field_barber['und']['0']['target_id'];
     $userid = $node->field_customer['und']['0']['target_id'];
     $date = $node->field_date['und']['0']['value'];
     
     $customer = user_load($userid);
     $barber = user_load($barberid);
     mailRating($date,$customer,$barberid,$barber);
}

function mailReminder($date, $customer, $barber) {
     $c_display = $customer->field_first_name['und']['0']['value'];
     $c_display .= " ".$customer->field_last_name['und']['0']['value'];
     $b_display = $barber->field_first_name['und']['0']['value'];
     $b_display .= " ".$barber->field_last_name['und']['0']['value'];
     
     $date_format = strtotime($date);
     
     $year = date('Y',$date_format);
     $day = date('jS',$date_format);
     $month = date('F',$date_format);
     $time = date('g:i a',$date_format);
     
     $to = $customer->mail;
     $from = "Got You In Appointment Reminder <noreply@gotyouin.com>";
     
     $body = "Dear ".$c_display.":<br /><br />";
     $body .= "This email is to remind you of your appointment with ";
     $body .= $b_display." on ".$month." ".$day.", ".$year." at ".$time.".<br /><br />";
     $body .= "Sincerely, Got You In!";
     
     $params = array(
         'subject' => 'Appointment Reminder',
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

function mailRating($date, $customer, $barberid, $barber) {
     $c_display = $customer->field_first_name['und']['0']['value'];
     $c_display .= " ".$customer->field_last_name['und']['0']['value'];
     $b_display = $barber->field_first_name['und']['0']['value'];
     $b_display .= " ".$barber->field_last_name['und']['0']['value'];
     $b_link = "www.gotyouin.com/user/".$barberid."' >".$b_display."</a>";
     
     $date_format = strtotime($date);
     
     $year = date('Y',$date_format);
     $day = date('jS',$date_format);
     $month = date('F',$date_format);
     $time = date('g:i a',$date_format);
     
     $to = $customer->mail;
     $from = "Got You In Appointment Rating <noreply@gotyouin.com>";
     
     $body = "Dear ".$c_display.":<br /><br />";
     $body .= "Thank you for scheduling your appointment through Got You In. ";
     $body .= "Please take a moment to rate your experience with ";
     $body .= $b_link." on ".$month." ".$day.", ".$year." at ".$time.".<br /><br />";
     $body .= "Sincerely, Got You In!";
     
     $params = array(
         'subject' => 'Barber Rating',
         'body' => $body,
         'from' => $from,
         'headers' => array(
             'From' => $from,
             'Sender' => $from,
             'Return-Path' => $from,
         ),
     );
     
     $result = drupal_mail(
          'htmlmail', 'reminder', $to, language_default(), $params
     );
}
