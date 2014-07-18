<?php
/**

	// cron to set recurring appts.

 * Let's rebuild the cron job from scratch since the current one seems to be
 * generating a memory black hole - possible recursion issues, so let's write
 * it from scratch instead of patching the other one.
 * 
 * @author Jenny Chalek, DBS>Interactive, 2013-05-09
 */

// necessary for cron since db in use is triggered based on HTTP_HOST!
if ( !empty( $argv[1] ) && $argv[1] == 'live' || strstr( dirname( __FILE__ ), 'gotyouin.com' ) ) {
	$_SERVER['HTTP_HOST'] = 'gotyouin.com';
} else {
    $_SERVER['HTTP_HOST'] = 'staging305.resultsbydesign.com';
}
//temp for debugging - use this only on local
//$debug = true;
//$_SERVER['HTTP_HOST'] = 'gotyouin.loc';

$_SERVER['REMOTE_ADDR'] = '0.0.0.0';

define('DRUPAL_ROOT', dirname(  dirname( __FILE__ ) ) );
require_once DRUPAL_ROOT . '/includes/bootstrap.inc';
drupal_bootstrap(DRUPAL_BOOTSTRAP_FULL);

/**
 * 1. Query all appointments newer than yesterday which have recurring flag set
 *    we will focus on the appointment date/time and the recurring multiplier.
 */
//Determine yesterday's date
$date_start = new DateTime();
$date_start->modify('-1 day');

//Determine date 4 months in the future
$date_end = new DateTime();
$date_end->modify('+4 months');
$max = $date_end->format('U');

//build and execute query to find all recurring appointments newer than above
$query = new EntityFieldQuery();
$query->entityCondition('entity_type', 'node')
    ->entityCondition('bundle', 'booking')
    ->propertyCondition('status', 1)
    ->fieldCondition('field_date', 'value', $date_start->format("Y-m-d"), '>') # UNCOMMENT TO REMOVE TIME RESTRICTION
    //->fieldCondition('field_recurring_token', 'value', 'No', '<>');
    /*temporarily ignore legacy appts for testing */
    //->fieldCondition('field_recurring_token', 'value', 'Yes', '!=') 
    ->fieldCondition('field_recurring_status', 'value', 'NEW', '='); //we only want the ones whose child appointments haven't been generated yet
$result = $query->execute();

if (isset($result['node'])) {
    $keys = array_keys($result['node']);
    $etids = entity_load('node', $keys);
    unset ($result, $keys);
} else {
    if ($debug) echo "No recurring appointments to process.";
    die();
}

/**
 * 2. Generate recurring appointment instances based on the above query.
 */
foreach($etids as $etid) {
    //debugging line -----------------------------------------------------------
    if ($debug) echo "----------------------------------------------------------------------<br />";
    if ($debug) echo "Appointment recurring token = ".$etid->field_recurring_token['und'][0]['value']." interval = $interval <br />";
    
    //gather information about each queried appointment
    $appt_date = $etid->field_date['und'][0]['value'];
    
    //debugging line -----------------------------------------------------------
    if ($debug) echo "Original barber id = ".$etid->field_barber['und']['0']['target_id']." <br />";
    if ($debug) echo "Original customer id = ".$etid->field_customer['und']['0']['target_id']." <br />";
    if ($debug) echo "Original node id = $etid->nid <br />";
    if ($debug) echo "Original time and date = $appt_date <br /><br />";
    
    $date = new DateTime($appt_date);
    $interval = $etid->field_recurring_multiplier['und'][0]['value'];
    $interval++;
    if ($interval > 1) { $s = 's'; } else { $s = ''; }
    //$date->modify('+'.$interval.' week' . $s);    
    
    $recur = $date->format('U'); //$recur gets regenerated with each additional date
    $appts = array();
    
    //gather the dates into an array
    while ($recur < $max) {
        $date->modify('+'.$interval.' week' . $s);
        $appts[] = $date->format('Y-m-d H:i:s');
        $recur = $date->format('U');
    }

    /**
     * 3. Find out if these instances already exist. Create instances that don't exist.
     */
    $next = true; //we want to know whether this is the next appt slot in line being 
                  //evaluated because we will set that one to NEW instead of ACTIVE
    foreach ($appts as $appt) {
        if ($debug) echo "Checking for an appointment for date: $appt <br />";
        //find out of the appt already exists
        $query = new EntityFieldQuery();
        $query->entityCondition('entity_type', 'node')
            ->entityCondition('bundle', 'booking')
            //we want to find cancelled ones too, to avoid putting them back
            //->propertyCondition('status', 1) 
            ->fieldCondition('field_date', 'value', $appt, '=')
            ->fieldCondition('field_barber', 'target_id', $etid->field_barber['und']['0']['target_id'], '=')
            ->fieldCondition('field_customer', 'target_id', $etid->field_customer['und']['0']['target_id'], '=');
        $result = $query->execute();

        if (!isset($result['node'])) {
            //doesn't exist - create
            $node = new stdClass();
            $node->title = "Appointment";
            $node->type = "booking";
            node_object_prepare($node);
            $node->language = LANGUAGE_NONE;
            $node->uid = $etid->uid;
            $node->status = 1;
            $node->promote = 0;
            $node->comment = 0;
            $node->field_barber['und'][0] = array(
              'target_id' => $etid->uid,
              'target_type' => 'user',
            );
            $node->field_recurring_multiplier['und'][0]['value'] = $etid->field_recurring_multiplier['und'][0]['value'];
            $node->field_recurring_token['und'][0]['value'] = $etid->field_recurring_token['und'][0]['value'];
            
            if ($next) {
                $node->field_recurring_status['und'][0]['value'] = 'NEW';
                $next = false;
                
                //debugging line -----------------------------------------------
                if ($debug) echo "will create the NEW appointment<br />";
                
            } else {
                $node->field_recurring_status['und'][0]['value'] = 'ACTIVE';
                
                //debugging line -----------------------------------------------
                if ($debug) echo "will create an ACTIVE appointment<br />";
            }
            $node->field_date['und'][0]['value'] =  $appt;
            $node->field_customer[$node->language][0] = array(
              'target_id' => $etid->field_customer['und'][0]['target_id'],
              'target_type' => 'user',
            );
            foreach($etid->field_desired_services['und'] as $service)
            {
              $node->field_desired_services[$node->language][] = array(
                'target_id' => $service,
                'target_type' => 'node',
              );
            }
            $node = node_submit($node);
            node_save($node);
            
            //debugging line ---------------------------------------------------
            if ($debug) echo "Created an appointment node id = $node->nid <br />";
            
        } else {
            //does exist, alter it as necessary with NEW vs. ACTIVE values depending on its position
            $a_keys = array_keys($result['node']);
            $e_node = node_load($a_keys[0]); //there should only be one node
            
            //filter out cancelled appointments to avoid reactivating or createing
            if ($e_node->status == '1') {
                if ($next) {
                    $e_node->field_recurring_status['und'][0]['value'] = 'NEW';
                    node_save($e_node);
                    $next = false;
                    
                    //debugging line -----------------------------------------------------------
                    if ($debug) echo "found the appointment to change to NEW node id = $e_node->nid <br />";
                    
                } else {
                    $e_node->field_recurring_status['und'][0]['value'] = 'ACTIVE';
                    node_save($e_node);
                    
                    //debugging line -----------------------------------------------------------
                    if ($debug) echo "found an appointment in the series, set ACTIVE node id = $e_node->nid <br />";
                }
            }
        }
    }
    //debugging line -----------------------------------------------------------
    if ($debug) echo "<br />";
    //now that we've generated the recurring appointments, replace the original appointment's 
    //NEW status with ACTIVE so the same appointment won't get pulled and queried again
    $etid->field_recurring_status['und'][0]['value'] = 'ACTIVE';
    node_save($etid);
    //debugging line -----------------------------------------------------------
    if ($debug) echo $etid->field_recurring_status['und'][0]['value'];
    if ($debug) echo "<br /><br />";
}
