#!/usr/bin/php
<?php
require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');
require_once('data.php.inc');

function getLocations($loc, $lat, $lon) {
  $url = "https://developers.zomato.com/api/v2.1/locations?query=".$loc."&lat="$lat."&lon=".$lon;
  $ch = curl_init();
  $curl_setopt($ch, CURLOPT_URL, $url);
  $curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Accept: application/json',
    'user-key: 1b90ae9b4a76dd0cf044bfc1332206cf'
  ));

  $resp = curl_exec($ch);
  curl_close($ch);
  $json = json_decode($resp, true);
  $ent_id = $json['data']['location_suggestions'][0]['entity_id'];
  $ent_type = $json['data']['location_suggestions'][0]['entity_type'];

  $client = new rabbitMQClient("rabbitMQData.ini","testServer");
  $request = array();
  $request['type'] = "insert_loc";
  $request['loc'] = $loc;
  $request['lat'] = $lat;
  $request['lon'] = $lon;
  $request['ent_type'] = $ent_type;
  $request['ent_id'] = $ent_id;
  $response = $client->send_request($request); 
  $r = getRestaurants($ent_id, $ent_type);

  return $r;

}

function getRestaurants($ent_id, $ent_type) {
  $url = "https://developers.zomato.com/api/v2.1/location_details?entity_id=".$ent_id."&entity_type=".$ent_type;
  $ch = curl_init();
  $curl_setopt($ch, CURLOPT_URL, $url);
  $curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Accept: application/json',
    'user-key: 1b90ae9b4a76dd0cf044bfc1332206cf'
  ));
  $resp = curl_exec($ch);
  curl_close($ch);
  $json = json_decode($resp, true);
  $rest_arr = $json['data']['best_rated_restaurant'];

  $client = new rabbitMQClient("rabbitMQData.ini","testServer");
  $request = array();
  $request['type'] = "insert_res";
  $request['rest_arr'] = $rest_arr;
  $response = $client->send_request($request);

  return $response;
  

}


function requestProcessor($request)
{
  echo "received request".PHP_EOL;
  var_dump($request);
  if(!isset($request['type']))
  {
    return "ERROR: unsupported message type";
  }
  switch ($request['type'])
  {
    case "get_loc":
      return getLocations($request['loc'], $request['lat'], $request['lon']); 
  }
  return array("returnCode" => '0', 'message'=>"Server received request and processed");
}

$server = new rabbitMQServer("rabbitMQBack.ini","testServer");

$server->process_requests('requestProcessor');
exit();
?>
