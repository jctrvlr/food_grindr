#!/usr/bin/php
<?php
require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');
require_once('data.php.inc');

function getRest($user, $zip, $last) {
  $dat = new dataProc();
  $output = $dat->getRest($user, $zip, $last);
  return $output;
    // return json array with one restaurant info
}

function restResp($user, $res_id, $like, $zip, $last) {
  $dat = new dataProc();
  $output = $dat->restResp($user, $res_id, $like, $zip, $last);
  return $output;
    // return json array with one restaurant info after storing response
}

function insertLoc($loc, $ent_type, $ent_id, $lat, $lon) {
  $dat = new dataProc();
  $output = $dat->insertLoc($loc, $ent_type, $ent_id, $lat, $lon);
  return $output;
}

function insertRes($res_arr) {
  $dat = new dataProc();
  $output = $dat->insertRes($res_arr);
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
      return getRest($request['user'], $request['zip'], $request['last']); // last is last restaurant name, user is email
    case "rest_response":
      return restResp($request['user'], $request['res_id'], $request['like'], $request['zip'], $request['last']); //Anything else?
    case "insert_loc":
      return insertLoc($request['loc'], $request['ent_type'], $request['ent_id'], $request['lat'], $request['lon']);
    case "insert_res":
      return insertRes($request["rest_arr"]);
  }
  return array("returnCode" => '0', 'message'=>"Server received request and processed");
}

$server = new rabbitMQServer("rabbitMQData.ini","testServer");

$server->process_requests('requestProcessor');
exit();
?>
