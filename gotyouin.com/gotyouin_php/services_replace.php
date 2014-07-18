<?php
/**
 * @file services_replace.php
 * @author Jenny Chalek, DBS>Interactive, 2013-02-10
 * 
 * Down 'n' dirty cleanup script! USE WITH EXTREME CAUTION
 * This script was created to correct a widespread misspelling of a service
 * (Beijing Blackout should be Bigen Blackout), but can be reused if similar
 * things happen in the future by simply changing the $find and $replace 
 * variables declared at the beginning
 */

$find = "Beijing Blackout";
$replace = "Bigen Blackout";
define('DRUPAL_ROOT', dirname(  dirname( __FILE__ ) ) );
require_once DRUPAL_ROOT . '/includes/bootstrap.inc';
drupal_bootstrap(DRUPAL_BOOTSTRAP_FULL);

//build our query to iterate through services
$query = new EntityFieldQuery();
$query->entityCondition('entity_type', 'node')
      ->entityCondition('bundle', 'service')
      ->propertyCondition('title', $find, '=');
$result = $query->execute();

var_dump($result);

foreach (array_keys($result['node']) as $nid) {
    $node = node_load($nid);
    //Let's use an entity_metadat_wrapper to simplify getting and setting
    $ewrapper = entity_metadata_wrapper('node',$node);
    $ewrapper->title->set($replace);
    $ewrapper->save(true);
    entity_save('node', $node);
}