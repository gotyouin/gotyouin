<?php
  define('DRUPAL_ROOT', dirname(dirname(dirname(dirname(dirname(dirname( __FILE__ )))))) );
  if ( ! isset( $_SERVER['REMOTE_ADDR'] ) ) {
  	// boostrap.inc happy
  	$_SERVER['REMOTE_ADDR'] = '0.0.0.0';
  }
  require_once DRUPAL_ROOT . '/includes/bootstrap.inc';
  drupal_bootstrap(DRUPAL_BOOTSTRAP_FULL);

  $query = new EntityFieldQuery();

  $query->entityCondition('entity_type', 'node')
    ->entityCondition('bundle', 'service')
    ->propertyCondition('status', 1)
    ->propertyCondition('uid', $_GET['barber_id']);

  $result = $query->execute();

  if (isset($result['node'])) {
    $etids = array_keys($result['node']);
    $etids = entity_load('node', $etids);
  }

  switch($_GET['result'])
  {
    case "table":
?>
<div id="new-app-services">
  <table cellpadding="0" cellspacing="0" class="table">
    <thead>
      <tr>
        <th class="service_price">Price</th>
        <th>Service</th><th class="service_time">Time Requirement</th>
        <th class="service_select">Select</th>
      </tr>
    </thead>
    <tbody>
<?php
    foreach($etids as $service)
    {
      print '<tr class="service-'. $service->nid . '">
                    <td class="service_price">$'. $service->field_price['und'][0]['value'] .'</td>
                    <td class="service_title">'. $service->title .'</td>
                    <td class="service_time"><span>'. $service->field_typical_time_required['und'][0]['value'] .'</span> minutes</td>
                    <td class="service_select"><button class="btn btn-primary">Add</a></td>
                  </tr>';
  }
?>
    </tbody>
    <tfoot>
      <tr>
        <td class="service_price" colspan="2"><strong>Total Price:</strong> $<span>0</span></td>
        <td class="service_time"><strong>Total Time:</strong> <span>0</span> minutes</td>
        <td class="service_select"><a href="#" class="btn btn-danger">Clear</a></td>
      </tr>
    </tfoot>
  </table>
</div>
<?php
    break;
    case "options":

      foreach($etids as $service)
      {

        print '<label class="checkbox"><input type="checkbox" id="edit-field-services-'. $service->nid .'" name="field_services[' . $service->nid . ']" value="' . $service->nid . '" class="form-checkbox">' . $service->nid . '</label>';

      }

    break;
  } 
?>

