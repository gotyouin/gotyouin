<?php

  $_SERVER['REMOTE_ADDR'] = '0.0.0.0';

	$date = date('r');

//	file_put_contents( '/tmp/gotyouintesting', $date . ': Loading customerDelete' . "\n", FILE_APPEND );

  define('DRUPAL_ROOT', dirname( dirname( __FILE__ ) ) );
  require_once DRUPAL_ROOT . '/includes/bootstrap.inc';
  drupal_bootstrap(DRUPAL_BOOTSTRAP_FULL);

  $node = node_load ( $_GET['nid'] );

  $barber = user_load ( $node->field_barber['und'][0]['target_id'] );
  $customer = user_load ( $node->field_customer['und'][0]['target_id'] );

  $time = new DateTime($node->field_date['und'][0]['value']);
  //$time = $time->setTimestamp(mktime($node->field_date['und'][0]['value']));
  $time = $time->format('F j, Y g:ia');

  $to = $barber->mail;
  $subject = $customer->field_first_name['und'][0]['value'] . ' ' . $customer->field_last_name['und'][0]['value'] . " has requested to cancel an appointment";
  if( $_GET['token'] != '' && $_GET['rDelete'] == TRUE ) {

    // IF APPOINTMENT IS RECURRING
    
    $message = '<html><body>';
	  $message .= $customer->field_first_name['und'][0]['value'] . ' ' . $customer->field_last_name['und'][0]['value'] . " has requested for the following appointment AND all recurring appointments to be cancelled.<br/><br/>";
    $message .= $time . '<br/><br/>';
    $message .= 'If you are already logged into your account, <a href="http://' . $_SERVER['HTTP_HOST'] . '/node/' . $_GET['nid'] . "/edit" . '">click here</a> to go directly to the appointment to complete the deletion process.';
    $message .= '</body></html>';

  } else {

    // IF APPOINTMENT IS NOT RECURRING

    $message = '<html><body>';
	  $message .= $customer->field_first_name['und'][0]['value'] . ' ' . $customer->field_last_name['und'][0]['value'] . " has requested that the following appointment be cancelled.<br/><br/>";
    $message .= $time . '<br/><br/>';
    $message .= 'If you are already logged into your account, <a href="http://' . $_SERVER['HTTP_HOST'] . '/node/' . $_GET['nid'] . "/edit" . '">click here</a> to go directly to the appointment to complete the deletion process.';
    $message .= '</body></html>';

  }

//  $to = 'chase@ch4ze.com';
  $from = "noreply@gotyouin.com";
  $headers = 'Content-type: text/html; charset=utf-8' . "\r\n";
  $headers .= "From:" . $from;
  mail($to,$subject,$message,$headers);

//	file_put_contents( '/tmp/gotyouintesting', $date . ": Mail Sent To: $to\n", FILE_APPEND );

