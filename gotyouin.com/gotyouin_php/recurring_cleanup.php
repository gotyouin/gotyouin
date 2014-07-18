<?php
/**
 * One-time script to fix the recurring appointment bug that ate Tokyo.
 * Purge any appointment found that's 6 months in the future or later.
 * 
 * @author Jenny Chalek, DBS>Interactive, 2013-05-09
 */

$_SERVER['REMOTE_ADDR'] = '0.0.0.0';

define('DRUPAL_ROOT', dirname(  dirname( __FILE__ ) ) );
require_once DRUPAL_ROOT . '/includes/bootstrap.inc';
drupal_bootstrap(DRUPAL_BOOTSTRAP_FULL);

//Determine 6 months from now date
$date = new DateTime();
//$date->modify('+6 months');

//build and execute query to find all recurring appointments newer than above
$query = new EntityFieldQuery();
$query->entityCondition('entity_type', 'node')
    ->entityCondition('bundle', 'booking')
    ->propertyCondition('status', 1)
    ->fieldCondition('field_date', 'value', $date->format("Y-m-d"), '>') # UNCOMMENT TO REMOVE TIME RESTRICTION
    ->fieldCondition('field_recurring_token', 'value', 'Yes', '=');
$result = $query->execute();

if (isset($result['node'])) {
    $keys = array_keys($result['node']);
    echo count($keys);
    die();
    //node_delete_multiple($keys);
}