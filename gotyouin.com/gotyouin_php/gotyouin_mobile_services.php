<?php
/**
* @file gotyouin_mobile_services.php
*
* Handles direct remote calls from mobile devices (via ajax) to Drupal database
* and internals for logging purposes.  USAGE: There must be a POST, and there
* must be an action key/value specified in that POST. When client first
* registers, a registration id is returned and a private hash. The hash must
* accompany all further requests. TODO!
*
* Any return ajax info, should be JSON formatted (and careful of quoting).
*
* @author Hal Burgiss  2013-01-17
*/

// bootstrap
define( 'DRUPAL_ROOT', dirname( dirname( __FILE__ ) ) );
require_once DRUPAL_ROOT . '/includes/bootstrap.inc';

// debugging.
$debug = true;
if ( $debug && $argv ) $_POST['action'] = $argv[1];

error_reporting(E_ALL);

$logfile = '/tmp/gotyouin_mobile.log';
if ( $debug ) file_put_contents( $logfile, print_r( $_POST, true ) );

// no echoes during ajax
//if ( $debug ) echo "Starting\n";

// execute the request
new mobile_services();

class mobile_services 
{
	private $action;
	private $date;
	private $post;
	// DO NOT CHANGE, will likely break a lot of stuff:
	private $salt = '(8jAnaabNho1^^asdf&,nn=-9jnnaALl..=/%2ks(L';

	function __construct() {
		$this->post = $_POST;
		$this->action = $this->post['action'];
		// mysql date format
		$this->date = 	date( "Y-m-d H:i:s" );

		// parse post
		if ( ! $_POST || empty( $this->action ) ) { 
			header('HTTP/1.0 400 Bad Request');
			die('400 Bad Request');
		}
		
		// Load the database essentials
		drupal_bootstrap( DRUPAL_BOOTSTRAP_DATABASE );

		// parse the action
		switch ( $this->action ) {
			case 'register_user':
				$this->registerUser();
				break;

			case 'log_error':
				$this->logError();
				break;

			case 'log_activity':
				$this->logActivity();
				break;

			default:
				header('HTTP/1.0 400 Bad Request');
				die('400 Bad Request');
		}
	}

	/**
	* @return string, JSON formatted, with registered user id, and hash
	*
	* This is run whenever the app is run first time. The registration id is
	* NOT the same as the Drupal user id. At this point, in the process, the
	* user process does not have a user account. The hash that is returned
	* must be used for all subsequent queries.
	*
	* @author Hal Burgiss  2013-01-22
	*/
	private function registerUser() {
		extract( $this->post );
		$last_registration_id = db_insert( "gotyouin_users")->fields( array( "last_activity_date" => "$this->date", "uuid" => "$uuid", "platform" => "$platform" ) )->execute();
		if ( is_numeric( $last_registration_id ) && $last_registration_id > 0 ) {

			$private_hash = $this->hash( $last_registration_id );
			// added new user to user tracking system.
			// note: careful of quoting, Ti is very unforgiving
			die( '{ "registerID" : "' . $last_registration_id . '", "hash": "' .  $private_hash . '"}' );
		} else {
			
			// error
			error_log( "Failed to retrieve user id for App" );
			die( '{"error" : "Error: failed to retrieve user id"}' );
		}
	}

	/**
	* @return echos JSON result success / error messages
	*
	* This logs user 'activities', arbitarirly defined, just to see what people are up to.
	*
	* @author Hal Burgiss  2013-01-22
	*/
	private function logActivity() {
		extract( $this->post );

		mysql_real_escape_string( $message );
		mysql_real_escape_string( $activity );
		
		try {
			$last_id = db_insert( "gotyouin_user_activities" )->fields( array( "registered_user_id" => (int)$register_id, "drupal_user_id" => (int)$drupal_user_id, "activity" => "$activity", "message" => "$message" ) )->execute();
		} catch ( Exception $error ) {
			error_log( "Failed to record App Activity: $error" );
			die( '{ "result" : "Error: failed to post activity"}' );
		}

		if ( is_numeric( $last_id ) && $last_id > 0 ) {

			// note: careful of quoting, Ti is very unforgiving
			die( '{ "result" : "success"}' );
		} else {
			
			// error
			error_log( "Failed to record App Activity" );
			die( '{ "result" : "Error: failed to post activity"}' );
		}
	}


	/**
	* @return echos JSON result success / error messages
	*
	* This logs error situations in the app.
	*
	* @author Hal Burgiss  2013-01-22
	*/
	private function logError() {
		extract( $this->post );

		mysql_real_escape_string( $message );
		mysql_real_escape_string( $error_data );
		mysql_real_escape_string( $error );
		
		try {
			$last_id = db_insert( "gotyouin_mobile_errors" )->fields( array( "registered_user_id" => (int)$register_id, "error" => "$error", "message" => "$message", "data" => "$error_data" )  )->execute();
		} catch ( Exception $error ) {
			error_log( "Failed to record App error: $error" );
			die( '{ "result" : "Error: failed to post error"}' );
		}

		if ( is_numeric( $last_id ) && $last_id > 0 ) {

			// note: careful of quoting, Ti is very unforgiving
			die( '{ "result" : "success"}' );
		} else {
			
			// error
			error_log( "Failed to record error from App" );
			die( '{ "result" : "Error: failed to post error"}' );
		}
	}

	/**
	* @return $string, hashed with sha1 unique for every user. The idea is to
	* include this with every request as a sanity check.
	*
	* @param user_id, to be hashed (this is the registration id)
	*
	* @author Hal Burgiss  2013-01-22
	*/
	private function hash( $user_id ) {
		return hash( 'sha1', $user_id . $this->salt );
	}

}
