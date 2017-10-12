#!/usr/bin/php
<?php
require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');

$loc1 = 'testLoc';
$lat1 = 'testLat';
$lon1 = 'testLon';

$client = new rabbitMQClient("rabbitMQLoc.ini","testServer");
$request = array();
$request['type'] = "get_loc";
$request['loc'] = $loc1;
$request['lat'] = $lat1;
$request['lon'] = $lon1;
$response = $client->send_request($request);
