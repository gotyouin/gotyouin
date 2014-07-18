<div class="row-profile">
  <?php if (isset($node->field_photos['und'])){ ?>
  	<div class="header-img">
  		<img src="<?php echo file_create_url(file_build_uri($node->field_photos['und'][0]['filename'])); ?>" alt="" />
  	</div>
  	<?php }else{ ?>
  <div class="header-img"><img src="/sites/all/themes/bh_bootstrap/images/header-bg.jpg" alt="" /></div>
  <?php } ?>
  <h1 id="display-title"><?php echo $node->title; ?></h1>
  <div class="loc">
      <?php /*echo theme('image_style', array('medium' => 'banner', 'path' => file_build_uri($node->field_photos['und'][0]['filename'])));*/ ?>
      <?php /*echo $node->field_photos['und'][0]['uri']; 
      <pre><?php var_DUMP($node); ?></pre>*/ ?>
      <?php print $node->field_location['und'][0]['street']; ?><br/>
      <?php print $node->field_location['und'][0]['city']; ?>, <?php print $node->field_location['und'][0]['province']; ?> <?php print $node->field_location['und'][0]['postal_code']; ?>
  </div>
  <div class="right-side">
    <div class="directions">
      <a href="https://maps.google.com/maps?t=h&q=loc:<?php print $node->field_location['und'][0]['latitude']; ?>,<?php print $node->field_location['und'][0]['longitude']; ?>&z=17" class="btn btn-primary">Directions</a>
    </div>
    <div class="call">
      <a href="tel:<?php print $node->field_phone['und'][0]['value']; ?>" class="btn btn-primary">Call</a>
    </div>
  </div>
  <?php
  $today = date('D');
  foreach ( $node->field_hours_of_operation['und'] as $day )
  {

    /* in order to properly convert from military time, we need to add leading zeros where needed */
    if( strlen($day['starthours']) == 3 ) { $day['starthours'] = "0" . $day['starthours']; }
    if( strlen($day['endhours']) == 3 ) { $day['endhours'] = "0" . $day['endhours']; }
    /* convert from military time */
    $day['starthours'] = date('g:ia', strtotime($day['starthours']) );
    $day['endhours'] = date('g:ia', strtotime($day['endhours']) );


    /* conditional for determining the hours of operation for the current day */
    switch ( $today )
    {

      case "Mon":
        if($day['day'] == 2) { $starthours[0] = $day['starthours']; $endhours[0] = $day['endhours']; }
        if($day['day'] == 3) { $starthours[1] = $day['starthours']; $endhours[1] = $day['endhours']; }
      break;
      case "Tue":
        if($day['day'] == 4) { $starthours[0] = $day['starthours']; $endhours[0] = $day['endhours']; }
        if($day['day'] == 5) { $starthours[1] = $day['starthours']; $endhours[1] = $day['endhours']; }
      break;
      case "Wed":
        if($day['day'] == 6) { $starthours[0] = $day['starthours']; $endhours[0] = $day['endhours']; }
        if($day['day'] == 7) { $starthours[1] = $day['starthours']; $endhours[1] = $day['endhours']; }
      break;
      case "Thu":
        if($day['day'] == 8) { $starthours[0] = $day['starthours']; $endhours[0] = $day['endhours']; }
        if($day['day'] == 9) { $starthours[1] = $day['starthours']; $endhours[1] = $day['endhours']; }
      break;
      case "Fri":
        if($day['day'] == 10) { $starthours[0] = $day['starthours']; $endhours[0] = $day['endhours']; }
        if($day['day'] == 11) { $starthours[1] = $day['starthours']; $endhours[1] = $day['endhours']; }
      break;
      case "Sat":
        if($day['day'] == 12) { $starthours[0] = $day['starthours']; $endhours[0] = $day['endhours']; }
        if($day['day'] == 13) { $starthours[1] = $day['starthours']; $endhours[1] = $day['endhours']; }
      break;
      case "Sun":
        if($day['day'] == 0) { $starthours[0] = $day['starthours']; $endhours[0] = $day['endhours']; }
        if($day['day'] == 1) { $starthours[1] = $day['starthours']; $endhours[1] = $day['endhours']; }
      break;

    }
    if(isset($starthours)){
      /* remove unnecessary minutes when hour is even */
      $starthours[0] = str_replace(':00','',$starthours[0]);
      $starthours[1] = str_replace(':00','',$starthours[1]);
    }
    if(isset($endhours)){
      $endhours[0] = str_replace(':00','',$endhours[0]);
      $endhours[1] = str_replace(':00','',$endhours[1]);
    }

    /* build full hours table */
    $hours[$day['day']]['start'] = $day['starthours'];
    $hours[$day['day']]['end'] = $day['endhours'];

  }

  ?>
  <div class="hours">
    Open <?php if( isset($starthours[0]) && isset($endhours[0]) ) { print $starthours[0]; ?>-<?php print $endhours[0]; } ?> <?php if( isset($starthours[1]) && isset($endhours[1]) ) { print '& ' . $starthours[1] . '-' . $endhours[1]; } ?> <a href="#">View Full Hours</a>
    <div class="hours-full" style="display:none;">
      <table cellpadding="0" cellspacing="0" class="table">
        <thead>
          <th>Day</th>
          <th>Open</th>
          <th>Close</th>
        </thead>
        <tbody>
          <tr>
            <td<?php if( isset($hours[1]['start']) && isset($hours[1]['end']) ) { ?> rowspan="2"<?php } ?>>Sun</td>
            <?php if( isset($hours[0]['start']) && isset($hours[0]['end']) ) { ?>
            <td><?php print $hours[0]['start']; ?></td>
            <td><?php print $hours[0]['end']; ?></td>
            <?php } else { ?>
            <td colspan="2" class="closed">Closed</td>
            <?php } ?>
          </tr>
          <?php if( isset($hours[1]['start']) && isset($hours[1]['end']) ) { ?>
          <tr>
            <td><?php print $hours[1]['start']; ?></td>
            <td><?php print $hours[1]['end']; ?></td>
          </tr>
          <?php } ?>
          <tr>
            <td<?php if( isset($hours[3]['start']) && isset($hours[3]['end']) ) { ?> rowspan="2"<?php } ?>>Mon</td>
            <?php if( isset($hours[2]['start']) && isset($hours[2]['end']) ) { ?>
            <td><?php print $hours[2]['start']; ?></td>
            <td><?php print $hours[2]['end']; ?></td>
            <?php } else { ?>
            <td colspan="2" class="closed">Closed</td>
            <?php } ?>
          </tr>
          <?php if( isset($hours[3]['start']) && isset($hours[3]['end']) ) { ?>
          <tr>
            <td><?php print $hours[3]['start']; ?></td>
            <td><?php print $hours[3]['end']; ?></td>
          </tr>
          <?php } ?>
          <tr>
            <td<?php if( isset($hours[5]['start']) && isset($hours[5]['end']) ) { ?> rowspan="2"<?php } ?>>Tue</td>
            <?php if( isset($hours[4]['start']) && isset($hours[4]['end']) ) { ?>
            <td><?php print $hours[4]['start']; ?></td>
            <td><?php print $hours[4]['end']; ?></td>
            <?php } else { ?>
            <td colspan="2" class="closed">Closed</td>
            <?php } ?>
          </tr>
          <?php if( isset($hours[5]['start']) && isset($hours[5]['end']) ) { ?>
          <tr>
            <td><?php print $hours[5]['start']; ?></td>
            <td><?php print $hours[5]['end']; ?></td>
          </tr>
          <?php } ?>
          <tr>
            <td<?php if( isset($hours[7]['start']) && isset($hours[7]['end']) ) { ?> rowspan="2"<?php } ?>>Wed</td>
            <?php if( isset($hours[6]['start']) && isset($hours[6]['end']) ) { ?>
            <td><?php print $hours[6]['start']; ?></td>
            <td><?php print $hours[6]['end']; ?></td>
            <?php } else { ?>
            <td colspan="2" class="closed">Closed</td>
            <?php } ?>
          </tr>
          <?php if( isset($hours[7]['start']) && isset($hours[7]['end']) ) { ?>
          <tr>
            <td><?php print $hours[7]['start']; ?></td>
            <td><?php print $hours[7]['end']; ?></td>
          </tr>
          <?php } ?>
          <tr>
            <td<?php if( isset($hours[9]['start']) && isset($hours[9]['end']) ) { ?> rowspan="2"<?php } ?>>Thu</td>
            <?php if( isset($hours[8]['start']) && isset($hours[8]['end']) ) { ?>
            <td><?php print $hours[8]['start']; ?></td>
            <td><?php print $hours[8]['end']; ?></td>
            <?php } else { ?>
            <td colspan="2" class="closed">Closed</td>
            <?php } ?>
          </tr>
          <?php if( isset($hours[9]['start']) && isset($hours[9]['end']) ) { ?>
          <tr>
            <td><?php print $hours[9]['start']; ?></td>
            <td><?php print $hours[9]['end']; ?></td>
          </tr>
          <?php } ?>
          <tr>
            <td<?php if( isset($hours[11]['start']) && isset($hours[11]['end']) ) { ?> rowspan="2"<?php } ?>>Fri</td>
            <?php if( isset($hours[10]['start']) && isset($hours[10]['end']) ) { ?>
            <td><?php print $hours[10]['start']; ?></td>
            <td><?php print $hours[10]['end']; ?></td>
            <?php } else { ?>
            <td colspan="2" class="closed">Closed</td>
            <?php } ?>
          </tr>
          <?php if( isset($hours[11]['start']) && isset($hours[11]['end']) ) { ?>
          <tr>
            <td><?php print $hours[11]['start']; ?></td>
            <td><?php print $hours[11]['end']; ?></td>
          </tr>
          <?php } ?>
          <tr>
            <td<?php if( isset($hours[13]['start']) && isset($hours[13]['end']) ) { ?> rowspan="2"<?php } ?>>Sat</td>
            <?php if( isset($hours[12]['start']) && isset($hours[12]['end']) ) { ?>
            <td><?php print $hours[12]['start']; ?></td>
            <td><?php print $hours[12]['end']; ?></td>
            <?php } else { ?>
            <td colspan="2" class="closed">Closed</td>
            <?php } ?>
          </tr>
          <?php if( isset($hours[13]['start']) && isset($hours[13]['end']) ) { ?>
          <tr>
            <td><?php print $hours[13]['start']; ?></td>
            <td><?php print $hours[13]['end']; ?></td>
          </tr>
          <?php } ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php /* our barbers is displayed via View:Block */ ?>
<script>
<?php // cosmetic stuff for the view, reformat names conditional on display name or not ?>
     jQuery( function() {
          var barbers = jQuery( "div.views-row" );
          barbers.each( function() {
               var displayName = jQuery( this ).find( "div.views-field-field-display-name h2" );
               if ( displayName.text().length > 1 ) {
                    displayName.text( '"' + displayName.text() + '"'  ).css( { 'font-size' : '135%' } );
               }
          });
     });
</script>
