<?php
  define('DRUPAL_ROOT', dirname(dirname(dirname(dirname(dirname(dirname( __FILE__ )))))) );
  if ( ! isset( $_SERVER['REMOTE_ADDR'] ) ) {
  	// boostrap.inc happy
  	$_SERVER['REMOTE_ADDR'] = '0.0.0.0';
  }
  require_once DRUPAL_ROOT . '/includes/bootstrap.inc';
  drupal_bootstrap(DRUPAL_BOOTSTRAP_FULL);

  $profile2 = profile2_load_by_user( $_GET['barber_id'] );

  if( isset($profile2['barber']) ) { $profileType = 'barber'; } else if ( isset($profile2['independent_barber']) ) { $profileType = 'independent_barber'; }

  $day = strtotime($_GET['date']);
  $day = date('w', $day);

  /* based on the given date, associate the ids that are used in the barber availability array */
  switch($day)
  {

    case 0:
      $did = array(0,1);
    break;
    case 1:
      $did = array(2,3);
    break;
    case 2:
      $did = array(4,5);
    break;
    case 3:
      $did = array(6,7);
    break;
    case 4:
      $did = array(8,9);
    break;
    case 5:
      $did = array(10,11);
    break;
    case 6:
      $did = array(12,13);
    break;
  }

  /* generate every possible appointment time based on barber availability for the selected day */
  foreach($profile2[$profileType]->field_barber_availability['und'] as $day)
  {

    foreach($did as $d)
    {
      if($day['day'] == $d)
      {
        /* convert times from military time to a datetime object */
        if( strlen($day['starthours']) == 3 ) { $day['starthours'] = '0' . $day['starthours']; }
        $day['starthours'] = date('g:ia', strtotime($day['starthours']) );
        $start = new DateTime( $_GET['date'] . ' ' .  $day['starthours']);

        if( strlen($day['endhours']) == 3 ) { $day['endhours'] = '0' . $day['endhours']; }
        $day['endhours'] = date('g:ia', strtotime($day['endhours']) );
        $end = new DateTime( $_GET['date'] . ' ' .  $day['endhours']);



        /* loop that generates the possible appointments */
        while($end > $start)
        {
          $index = $start->format('U');
          $start->modify('+' . $_GET['duration'] . ' minutes');
          if($start > $end) { continue; }
          $blocks[$index]['end'] = $start->format('F j, Y g:ia');
          $start->modify('-' . $_GET['duration'] . ' minutes');
          $blocks[$index]['start'] = $start->format('F j, Y g:ia');
          $start->modify('+15 minutes');

          $count++;
        }
      }
    }
  }

  /* get all the bookings for the barber on the selected day */
  $day = new DateTime($_GET['date']);

  $query = new EntityFieldQuery();
  $query->entityCondition('entity_type', 'node')
  ->entityCondition('bundle', 'booking')
  ->fieldCondition('field_barber', 'target_id', $_GET['barber_id'], '=')
  ->fieldCondition('field_date', 'value', db_like($day->format('Y-m-d')) . '%', 'LIKE')
  ->propertyCondition('status', 1);

  $result = $query->execute();

  if (isset($result['node'])) {
    $etids = array_keys($result['node']);
    $etids = entity_load('node', $etids);
  }

  foreach($etids as $etid) //this iterates through appointments that this barber already has for this day
  {
    $time = 0;

    /* generate length of existing appointment */
    //by iterating through the services in it
    foreach($etid->field_desired_services['und'] as $service)
    {
        if ($service['target_id'] != 0) {
            $svc = node_load($service['target_id']);
            $time = $time + $svc->field_typical_time_required['und'][0]['value'];
        }
    }

    /* loop thru generated possible appointments to check for conflicts with existing ones */
    //$count = 0; //why is this here?
    
    //we establish the start and end time of an existing booking to check
    //this should only change once per loop, so we moved it to here
    //rather than inside the block testing loop
    $bookStart = new DateTime($etid->field_date['und'][0]['value']);
    $bookEnd   = new DateTime($etid->field_date['und'][0]['value']);
    $bookEnd->modify('+' . $time . ' minutes');
      
    foreach($blocks as $key => $block) //now loop through the "blocks" that potentially could be used
    {
      //we collect the start and stop time in each iteration for the block
      //whose availability is being determined
      $appStart = new DateTime($block['start']);
      $appEnd   = new DateTime($block['end']);

      if($appStart >= $bookStart && $appStart < $bookEnd) {
          //the start time of the proposed appointment is the same as another - no dice
          unset($blocks[$key]);
      } elseif($appEnd > $bookStart && $appEnd <= $bookEnd) {
          //the start time of the proposed appointment falls within the time period of another - nope
          unset($blocks[$key]);
      } elseif($appStart <= $bookStart && $appEnd >= $bookEnd) {
          //the start time of the proposed appointment is before the start time of the booking
          //AND the end time is after the end time of the booking
          //this will catch the "exceptions" that were squeaking through the previous rules
          unset($blocks[$key]);
      }
    }
  }

  $count = 0;
  //echo '<h2>' . date('l, F m', strtotime($block['start'])) . '</h2>';
  //now that we've weeded out impossibilities, iterate through and generate time choices for the customer
  foreach($blocks as $block)
  {
    $count++;
    $time = strtotime($block['start']);
    print '<button class="btn btn-danger"><span class="text">' . date('g:ia', strtotime($block['start'])) . ' &mdash; ' . date('g:ia', strtotime($block['end'])) . '</span><span class="time" style="display:none">' . date('g:ia',$time) . '</span></button><br/>';
  }

  if($count == 0) { print "No appointments are available for the selected day"; }