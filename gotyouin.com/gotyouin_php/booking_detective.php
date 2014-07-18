<?php
/**
 * This is a utility script file designed to sort out anomalies that are not
 * obvious involving back-end profile details
 * @author Jenny Chalek, DBS>Interactive, 2013-06-18
 */

// necessary for cron since db in use is triggered based on HTTP_HOST!
//if ( !empty( $argv[1] ) && $argv[1] == 'live' || strstr( dirname( __FILE__ ), 'gotyouin.com' ) ) {
//	$_SERVER['HTTP_HOST'] = 'gotyouin.com';
//} else {
//    $_SERVER['HTTP_HOST'] = 'staging305.resultsbydesign.com';
//}

$_SERVER['REMOTE_ADDR'] = '0.0.0.0';

define('DRUPAL_ROOT', dirname(  dirname( __FILE__ ) ) );
require_once DRUPAL_ROOT . '/includes/bootstrap.inc';
drupal_bootstrap(DRUPAL_BOOTSTRAP_FULL);

//$bad_node = node_load('4555');
//$good_node = node_load('4545');

$bad_node = node_load('4558');
$good_node = node_load('4566');

echo "<br />-------------------DUMP OF BAD APPT NODE-------------------<br />";
echo "<pre>";
var_dump ($bad_node);
echo "</pre>";

echo "<br />-------------------DUMP OF GOOD APPT NODE-------------------<br />";
echo "<pre>";
var_dump ($good_node);
echo "</pre>";

die();

if (!empty($_GET)) {
    $user = $_GET['user']."<br />";
} else {
    echo "Please specify a user";
    die();
}

$profile = profile2_load_by_user($user);

foreach ($profile as $key=>$type) {
    //echo $key."<br />";
    echo "<pre>";
    var_dump ($type);
    echo "</pre>";
}
die();
echo "<br />-------------------DUMP OF WHOLE PROFILE2 SERIES-------------------<br />";
echo "<pre>";
var_dump ($profile);
echo "</pre>";
echo "<br />-------------------DUMP OF BARBER FIELD_SERVICES-------------------<br />";
echo "<pre>";
var_dump ($profile['barber']->field_services);
echo "</pre>";
echo "<br />-------------DUMP OF INDEPENDENT BARBER FIELD_SERVICES-------------<br />";
echo "<pre>";
var_dump ($profile['independent_barber']->field_services);
echo "</pre>";

if ($profile['barber']) {
    $service_array = $profile['barber']->field_services['und'];
} elseif ($profile['independent_barber']) {
    $service_array = $profile['independent_barber']->field_services['und'];
}

$nids = array();
foreach ($service_array as $service) {
    $nids[] = $service['target_id'];
}

$services = node_load_multiple($nids);
//var_dump($services);

echo "<br />---------------DUMP OF FIELD_SERVICES TIMES REQUIRED----------------<br />";
foreach ($services as $service) {
    echo "<pre>";
    var_dump($service->field_typical_time_required);
    echo "</pre>";
}