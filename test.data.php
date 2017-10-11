<?php
require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');

$loc = 'testLoc';
$lat = 'testLat';
$lon = 'testLon';

$client = new rabbitMQClient("rabbitMQLoc.ini","testServer");
$request = array();
$request['type'] = "get_loc";
$request['loc'] = $loc;
$request['lat'] = $lat;
$request['lon'] = $lon;
$response = $client->publish($request);

echo $response;
?>