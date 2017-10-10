#!/usr/bin/php
<?php
require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');
require_once('login.php.inc');

function getRest() {
    // return json array with one restaurant info
}

function restResp() {
    // return json array with one restaurant info after storing response
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
      //return getRest($request['']);
    case "rest_response":
      return restResp($request['email'], $request['rest_id'], $request['like'], $request['']);
  }
  return array("returnCode" => '0', 'message'=>"Server received request and processed");
}

$server = new rabbitMQServer("rabbitMQData.ini","testServer");

$server->process_requests('requestProcessor');
exit();
?>
