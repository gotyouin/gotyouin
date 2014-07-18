<?php
/**
* @file gotyouin_logout.php
*
* Force a user logout programmatically and destroy session.
*
* @author Hal Burgiss 2013-02-13
*/

// bootstrap ... must be in the root folder for this to work .... hmmm I wonder why ???
chdir( $_SERVER['DOCUMENT_ROOT'] );
define( 'DRUPAL_ROOT', getcwd() ) ;

// $base_url must be reset
$base_url = 'http://'  . $_SERVER[ 'HTTP_HOST' ];
require_once DRUPAL_ROOT . '/includes/bootstrap.inc';

drupal_bootstrap( DRUPAL_BOOTSTRAP_FULL );

ini_set('log_errors','On');
ini_set('display_errors','false');

$_user = $user;

// for user logout
module_load_include('pages.inc', 'user');
module_invoke_all('user_logout', $user);

// Destroy the current session, and reset $user to the anonymous user.
session_destroy();

//file_put_contents( '/tmp/gyi_logout', 'fired: ' . $_user->uid . "\n", FILE_APPEND );
//file_put_contents( '/tmp/gyi_logout', 'fired: ' . $user->uid . "\n", FILE_APPEND );
die('<html><body>Logged Out</body></html>');
