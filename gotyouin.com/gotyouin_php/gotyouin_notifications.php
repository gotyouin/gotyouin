<?php
/**
* @file gotyouin_notifications.php
*
* Handles the checking and extracting of any pending notifications. Called from
* ./gotyouin_user_status.php, typically.
*
* @author Hal Burgiss  2013-02-02
*/

/** NOTE: assumes Drupal is already bootstrapped and database is opened. **/

$today = date( 'Y-m-d' );
$notifications = array();

/*

this returns invalid result set for some reason, the mysql syntax is valid.

$r = db_select( 'gotyouin_notifications', 'g' )
	->fields( 'g', array( 'notification_date','message' ) )
	->condition( 'notification_date', "$today", '>=' )
	->condition( 'notification_date', "DATE_ADD( CURRENT_DATE(), INTERVAL 1 DAY)", '<' )
	->condition( 'processed', "0000-00-00", '=' )
	->execute();
*/


try {

	// Get all notification for this user today! with newest ones first. These will be
	// merged with any existing notifications the user has.
	$result = db_query(" SELECT * FROM gotyouin_notifications g 
		WHERE 
			g.uid = $user->uid
		AND
			g.notification_date >= '$today' 
		AND 
			g.notification_date < DATE_ADD( CURRENT_DATE(), INTERVAL 1 DAY)
		AND
			g.processed = '0000-00-00 00:00:00'
		ORDER BY 
			id DESC
		" ) ;
	
	while( $n = $result->fetchObject() ) {
	//		$notifications[] = array( $n->message, $n->notification_date );
		$notifications[] = $n->message;
	}
	//echo "<pre>";print_r( $notifications );die();
	
	/*
	// does not work at all 2013-02-02 ????
	$num = db_update( 'gotyouin_notifications')
			->fields( array( 'processed' => 'NOW()' ) )
			->condition( 'notification_date', "$today", '>=' )
			->condition( 'notification_date', "DATE_ADD( CURRENT_DATE(), INTERVAL 1 DAY)", '<' )
			->condition( 'processed', "0000-00-00", '=' )
			->execute();
	*/
	
	// run the same query again, and update the processed date string to show we already grabbed this one.
	// TODO: retest changed query 2013-02-08
	$result = db_query(" UPDATE gotyouin_notifications
		SET 
			processed = NOW()
	     WHERE 
			uid = $user->uid
	     AND 
	          notification_date < DATE_ADD( CURRENT_DATE(), INTERVAL 1 DAY)
	     AND
		     processed = '0000-00-00 00:00:00'
		"
	     ) ;
	
	 if ( $result->rowCount() != count( $notifications ) ) {
		error_log( "Notification Error: mismatch rowCount vs number of notifications" );
	 }

} catch ( Exception $e ) {

	error_log( 'Error on notification query: ' . $e );
}
