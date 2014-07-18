<?php
/**
* @file gotyouin_user_status.php
*
* Called from a mobile webView to check if user is logged in or not and grab
* profile and session values. Optionally can be used to logout a logged in user
* by using query string ?logout=true (or just link to user/logout).
*
* @author Hal Burgiss 2013-01-27
*/

function tmplog( $string ) {
	$string = date('r') . ': ' . $string;
	file_put_contents( '/tmp/gyiuser.log', $string, FILE_APPEND );
}
tmplog( 'Starting: UUID ' . $uuid . "\n"  );


// bootstrap ... must be in the root folder for this to work .... hmmm I wonder why ???
chdir( $_SERVER['DOCUMENT_ROOT'] );
define( 'DRUPAL_ROOT', getcwd() ) ;

// $base_url must be reset
$base_url = 'http://'  . $_SERVER[ 'HTTP_HOST' ];
require_once DRUPAL_ROOT . '/includes/bootstrap.inc';

drupal_bootstrap( DRUPAL_BOOTSTRAP_FULL );

tmplog( 'Drupal Loaded: UUID ' . $uuid . "\n"  );


// debugging.
$debug = true;
if ( $_GET ) {
//	if ( $debug && $argv ) $_POST['action'] = $argv[1];
}

ini_set('log_errors','On');
ini_set('display_errors','false');

if ( $debug ) {
//	error_reporting(E_ALL);
//     ini_set('log_errors','On');
//     ini_set('display_errors','true');
//     ini_set('display_startup_errors','true');
}

$uuid = 'none';
if ( $debug && isset( $_GET['UUID'] ) ) {
	$uuid =  $_GET['UUID'];
}


tmplog( 'Preuser: UUID ' . $uuid . "\n"  );

//file_put_contents( '/tmp/gyi_error', print_r( $user, true ) );

//$last_registration_id = db_insert( "gotyouin_users")->fields( array( "last_activity_date" => "$this->date", "uuid" => "$uuid", "platform" => "$platform" ) )->execute();

if ( $user && $user->uid > 0 ) {

	tmplog( 'Getting User: UUID ' . $uuid . "\n"  );

	if ( $_GET && isset( $_GET['logout'] ) ) {
		
		// NOTE: webView can also load /user/logout url. This will get a
		// access denied page if user is not logged in.
		module_load_include('pages.inc', 'user');
		user_logout();
		session_destroy();
		// redirects here to / automatically
	}

	// we are logged in
	$loggedin = 'Yes';
	
	// get the value of this session
	$session = $_COOKIE[ session_name() ]; 

	// get all the extended profile data
	$user = user_load( $user->uid );

	// default
	$user->user_type = 'Customer';


	// figure out which type of user we have, eg Barber?
	foreach ( $user->roles as $role ) {
		// Barbers are either 'Regular Barber' or 'Independent Barber' 2013-02-05
		if ( stristr( $role, 'barber' ) ||  stristr( $role, 'owner' ) || stristr( $role, 'customer' ) ) {
			$user->user_type = ucwords( strtolower( $role ) );
		}
	}

	// since owners are dual role, we want to make sure they are identified as Owners (not barbers).
	foreach ( $user->roles as $role ) {
		if ( stristr( $role, 'owner' ) ) {
			$user->user_type = ucwords( strtolower( $role ) );
		}
	}

	// Get the shop id for owners only.
	if ( $user->user_type === 'Owner' ) {
		$profile = profile2_load_by_user( $user->uid );
		$user->shopID = $profile['shop_owner']->field_your_shop_id_is['und'][0]['value'];
		if ( empty( $user->shopID ) ) {
			$user->shopID = -1;
		}
	}

	// flatten out the user profile data from Drupal.
	$user->firstName = $user->field_first_name['und'][0]['value'];
	$user->lastName = $user->field_last_name['und'][0]['value'];
//	$user->displayName = $user->field_display_name['und'][0]['value'];
	$user->phone = $user->field_phone['und'][0]['value'];

/*
// tmp debug stuff. 2013-02-06 for crashes on some phones
$user = new stdClass(); 
$user->uid = '1';
$user->user_type = 'Customer';
$user->firstName = 'Hal';
$user->lastName = 'Test';
//	$user->displayName = $user->field_display_name['und'][0]['value'];
$user->phone = '5025627795';
$notifications = array();
*/

// get notifications
tmplog( 'Getting Notifications: UUID ' . $uuid . "\n"  );
include DRUPAL_ROOT . '/gotyouin_php/gotyouin_notifications.php';
//////////////////////////////////////////////////////////////////

//file_put_contents( '/tmp/gyi_error', print_r( $user, true ) );


//echo "<pre>";print_r( $notifications );die();

} else {

	// not logged in
	$loggedin = 'No';
	$session = '';
	$user->user_type = 'Anonymous';
	$notifications = array();
}

tmplog( 'Finished getting user: UUID ' . $uuid . "\n"  );

//echo "<pre>";print_r( $user );die();

header("Expires: Mon, 26 Jul 2012 05:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT"); 
header("Cache-Control: no-store, no-cache, must-revalidate"); 
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

//////////////////////////////////////////////
// Ti webViews:
//	- Content via HTML can be shown
//	- JavaScript values can be read 

?>
<!DOCTYPE html>
<html lang="en">
	<head></head>
	<body>
		<script>
			<?php // this information is being made available to GYI webView (typically hidden). ?>
			var sessVal = "<?php echo $session;?>";
			var sessName = "<?php echo session_name();?>";
			var loggedin = "<?php echo $loggedin;?>";
			var user = JSON.stringify( <?php echo json_encode( $user );?> ); <?php // do NOT quote! This is the Drupal user object ?>
			var notifications = JSON.stringify( <?php echo json_encode( $notifications );?> ); <?php // do NOT quote! This is array of notifications ?>
		</script>
	</body>
</html>
<?php
tmplog( 'Finished HTML: UUID ' . $uuid . "\n"  );

