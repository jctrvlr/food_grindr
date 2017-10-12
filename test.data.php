#!/usr/bin/php
<?php
require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');

function insertRes1($res_arr) {
  $output = insertRes($res_arr);
  return $output;
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
    case "get_rest":
      return getRest($request['']); // figure out what requests
    case "rest_response":
      return restResp($request['email'], $request['rest_id'], $request['like'], $request['']);
    case "insert_loc":
      return insertLoc($request['loc'], $request['ent_type'], $request['ent_id'], $request['lat'], $request['lon']);
    case "insert_res":
      return insertRes1($request["rest_arr"]);
  }
  return array("returnCode" => '0', 'message'=>"Server received request and processed");
}

$server = new rabbitMQServer("rabbitMQData.ini","testServer");

$server->process_requests('requestProcessor');
exit();


function insertRes($res_arr) {
    $json = json_decode($res_arr, true);
        
    foreach($json as $item) {
        $id = $item[0]->id;
        $name = $item[0]->name;
        $location = $item[0]->location->address;
        $zip = $item[0]->location->zipcode;
        $lat = $item[0]->location->latitude;
        $lon = $item[0]->location->longitude;
        $cuisine = $item[0]->cuisine;
        $online = $item[0]->has_online_delivery;
        echo $id.PHP_EOL;
        echo $name.PHP_EOL;
        echo $location.PHP_EOL;
        echo $zip.PHP_EOL;
        echo $lat.PHP_EOL;
        echo $lon.PHP_EOL;
        echo $cuisine.PHP_EOL;
        echo $online.PHP_EOL;

        $statement = "insert ignore into restaurants (res_id, name, address, zipcode, longitude, latitude, cuisine, price_range, thumbnail, online_delivery) values ('";
        echo $statement.PHP_EOL;
    }
    // iterate thru array and insert each restaurant

}
echo $response;
?>
