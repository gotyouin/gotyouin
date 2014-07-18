<?php
/**
* Testing stuff only. Can be deleted after implemented.
*
* @author Hal Burgiss  2013-02-09
*/


// bootstrap ... must be in the root folder for some drupal stuff ???
chdir( dirname( dirname( __FILE__ ) ) );
define( 'DRUPAL_ROOT', getcwd() ) ; 

error_reporting( E_ALL & ~E_NOTICE );
require_once DRUPAL_ROOT . '/includes/bootstrap.inc';
drupal_bootstrap( DRUPAL_BOOTSTRAP_DATABASE );

// appointments are set by cron for today.
$today = date( 'Y-m-d' );

try {

	// testing only
	$userID = 1;

	// run the same query again, and update the processed date string to show we already grabbed this one.
	// TODO: retest changed query 2013-02-08
	$result = db_query(" INSERT INTO gotyouin_notifications
		SET
			message = 'You have an appointment for today', notification_date = '$today', uid = $userID
		"
	     ) ;
	
	 if ( $result->rowCount() !=  1 ) {
		error_log( "Notification Error: mismatch rowCount vs number of notifications" );
	 }

} catch ( Exception $e ) {

	error_log( 'Error on setting appointment notification: ' . $e );
}
