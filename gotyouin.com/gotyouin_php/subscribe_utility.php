<?php
/**
 * @file subscribe_utility.php
 * @author Jenny Chalek, DBS>Interactive, 2013-03-19
 * 
 * This script is designed to retroactively get around the fact that Drupal
 * Entity Field Query cannot test for "NULL" values - and the lack of a value in 
 * a profile2 field will cause that profile2 not to be found when a condition
 * based on that field. Namely, if there is no value in "field_subscription_status,"
 * the profile won't even show up if the condition is "ACTIVE" "<>" So we are
 * going to create a workaround to retroactively assign "NEW" to all profile2
 * profiles which do not have anything in that field. This literally queries
 * every user in the system, so it would not be efficient to build this logic
 * directly into the main paypal_cron.php. Going forward, all new accounts
 * created will have a default of NEW in this field, thus avoiding this problem
 */

// necessary for cron since db in use is triggered based on HTTP_HOST!
if ( !empty( $argv[1] ) && $argv[1] == 'live' || strstr( dirname( __FILE__ ), 'gotyouin.com' ) ) {
	$_SERVER['HTTP_HOST'] = 'gotyouin.com';
}

$_SERVER['REMOTE_ADDR'] = '0.0.0.0';
define('DRUPAL_ROOT', dirname(  dirname( __FILE__ ) ) );
require_once DRUPAL_ROOT . '/includes/bootstrap.inc';
drupal_bootstrap(DRUPAL_BOOTSTRAP_FULL);

$bundle = array('shop_owner','independent_barber');
$query = new EntityFieldQuery();
$query->entityCondition('entity_type', 'profile2')
       ->entityCondition('bundle', $bundle);
$result = $query->execute();
$count_changed = 0;

if (isset($result['profile2'])) {
    $keys = array_keys ($result['profile2']);
    echo count($keys);
    
    foreach ($keys as $key){
         $profile = profile2_load($key);
         profile2_save($profile);
         $check = $profile->field_subscription_status['und']['0']['value'];
         if ($check == "ACTIVE" || $check == "PENDING" || $check == "NEW") {
              //do nothing
         } else {
              $count_changed++;
              $profile->field_subscription_status['und']['0']['value'] = 'NEW';
              profile2_save($profile);
         }
    }
}
echo $count_changed." profiles changed.";