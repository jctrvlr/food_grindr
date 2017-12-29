#!/usr/bin/php
<?php
require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');

$client = new rabbitMQClient("rabbitMQData.ini","testServer");
$request = array();
$request['type'] = "find_pref";
$response = $client->publish($request);

?>