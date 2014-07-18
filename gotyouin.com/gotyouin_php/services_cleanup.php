<?php
/**
 * @file email_reminders.php
 * @author Jenny Chalek, DBS>Interactive, 2013-02-06
 * 
 * Down 'n' dirty cleanup script! USE WITH EXTREME CAUTION
 * This script is designed to be run in case of an emergency to clean up bad
 * data in the services system. This bad data is widespread at the moment
 * because I made a million little test services then deleted them without
 * severing the "tie" to the barbers in question. Hopefully, I'll only ever need
 * to use this script once.
 */
               
define('DRUPAL_ROOT', dirname(  dirname( __FILE__ ) ) );
require_once DRUPAL_ROOT . '/includes/bootstrap.inc';
drupal_bootstrap(DRUPAL_BOOTSTRAP_FULL);
$services_owned = array(); //we will use this to keep track of which nodes are mapped to barbers

/**
 * This section will check all users to see if they have a reference to a
 * service node that no longer exists, then remove the reference, while
 * preserving references to services that do exist
 */

for ($i = 61; $i <= 61; $i++) {
     //echo $i;
     $profile2 = profile2_load_by_user($i);
     if (empty($profile2)) {
          echo "USER ID ".$i." DOESN'T EXIST - NEXT<br />";
          continue;
     }
    //var_dump($profile2);
     if (array_key_exists('barber', $profile2)) {
          $profile_object = $profile2['barber'];
     } elseif (array_key_exists('independent_barber', $profile2)) {
          $profile_object = $profile2['independent_barber'];
     } else {
          echo "USER ID ".$i." IS NEITHER TYPE OF BARBER - NEXT<br />";
          continue;
     }
     $existing_services = $profile_object->field_services['und'];
     //var_dump ($existing_services);
     
     if(isset ($field_services)) unset($field_services);
     $field_services = array();

     if (empty($existing_services)) {
          echo "USER ID ".$i." HAS NO SERVICES TO EDIT - NEXT<br />";
          continue;
     }
     foreach ($existing_services as $key=>$value) {
          if (node_load($value['target_id'])) {
               //keep it, it exists - push into holding variable
               echo "SERVICE NODE ".$value['target_id']." EXISTS - ADD IT<br />";
               $services_owned[][$value['target_id']] = $value['target_id'];
               $field_services[] = $value;
          } else {
               echo "SERVICE NODE ".$value['target_id']." DOESN'T EXIST - NEXT<br />";
               //skip it - the node is deleted
          }
     }
     //die();
     if (!empty($field_services)) {
          $profile_object->field_services['und'] = $field_services;
          echo "SERVICE NODES ADDED<br />";
          
     } else {
          $profile_object->field_services['und'] = array();
          echo "NO SERVICE NODES ADDED - FIELD CLEARED OUT<br />";
     }
     echo "<br />";
     profile2_save($profile_object);
}
die();
/**
 * This section iterates through services to find those that are "orphaned"
 * and delete them.
 */

//build our query to iterate through services
$query = new EntityFieldQuery();
$query->entityCondition('entity_type', 'node')
      ->entityCondition('bundle', 'service');
$result = $query->execute();

foreach (array_keys($result['node']) as $nid) {
     if ($services_owned[$nid] == $nid) {
          echo "THIS NODE ".$nid." HAS AN OWNER, LEAVE IT ALONE.<br />";
     } else {
          echo "THIS NODE ".$nid." IS AN ORPHAN, KILL IT!<br />";
          node_delete($nid);
     }
     echo "<br />";
}
