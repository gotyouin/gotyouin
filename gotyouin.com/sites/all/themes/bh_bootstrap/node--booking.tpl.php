<?php
/**
 * This node template controls the "view" tab for an individual booking node
 * @author Jenny Chalek 2013-02-28
 */
echo "<h4>Customer: ";
$customer = $node->field_customer['und'][0]['entity'];
if ($customer == null) {
     echo $node->field_customer_name['und'][0]['safe_value'];
} elseif (empty($customer->field_display_name)) {
     echo $customer->field_first_name['und'][0]['value']." ".$customer->field_last_name['und'][0]['value'];
} else {
     echo $customer->field_display_name['und'][0]['value'];
}
echo "</h4>";

/*
 * This would be redundant - the barber already knows who they are 
echo "Barber: ";
$barber = $node->field_barber['und'][0]['entity'];
if (empty($barber->field_display_name)) {
     echo $barber->field_first_name['und'][0]['value']." ".$barber->field_last_name['und'][0]['value']."<br />";
} else {
     echo $barber->field_display_name['und'][0]['value']."<br />";
}
*/

echo "<h5>Appointment Date and Time: ";
echo date('M d, Y - g:ia', strtotime($node->field_date['und'][0]['value']));
echo "</h5>";

$services = $node->field_desired_services['und'];
?>

<table class="views-table cols-3 table">
     <thead>
          <tr>
          <th>Service</th>
          <th>Time Required</th>
          <th>Price</th>
          </tr>
     </thead>

<?php
$total_time = 0;
$total_price = 0;

foreach ($services as $value) {
     echo "<tr>";
     echo "<td>".$value['entity']->title."</td>";
     echo "<td>".$value['entity']->field_typical_time_required['und'][0]['value']." minutes</td>";
     echo "<td>$".$value['entity']->field_price['und'][0]['value']."</td>";
     echo "<tr>";
     $total_time += $value['entity']->field_typical_time_required['und'][0]['value'];
     $total_price += $value['entity']->field_price['und'][0]['value'];
     //var_dump($value['entity']->field_price);
}
?>
     <tfoot>
          <tr>
               <td><strong>TOTAL:</strong></td>
               <td><strong><?php echo $total_time; ?> minutes</strong></td>
               <td><strong>$<?php echo number_format($total_price, 2); ?></strong></td>
          </tr>
     </tfoot>
</table>

<script>
  jQuery( function() {
    jQuery("#page-title").text("Booking Details");
  });
</script>