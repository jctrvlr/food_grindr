#!/usr/bin/php
<?php
require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');
require_once('review.php.inc');

function doReview($email, $resid, $rating)
{
  echo "test";
  $review = new reviewDB();
  echo "test2";
  $output = $review->getReview($email, $resid, $rating);
  echo $email;
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
    case "review":
      return doReview($request['email'], $request['resid'], $request['rating']);
  }
  return array("returnCode" => '0', 'message'=>"Server received request and processed");
}

$server = new rabbitMQServer("rabbitMQReview.ini","testServer");

$server->process_requests('requestProcessor');
exit();
?>

