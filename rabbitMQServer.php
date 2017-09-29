#!/usr/bin/php
<?php
require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');
require_once('login.php.inc');

function doLogin($username,$password)
{
  // lookup username in databas
  // check password
  $login = new logindb();
  $output = $login->validateLogin($username,$password);
  if($output){
    // TODO - Return json object instead of true/false 
    return true;
  } else {
    // TODO - Return json object instead of true/false 
    return false;
  }
}

function doSignup($email, $fname, $lname, $pass){
  $login = new logindb();
  $output = $login->signup($email, $fname, $lname, $pass);
  if($output){
    // TODO - Return json object instead of true/false 
    return true;
  } else {
    // TODO - Return json object instead of true/false 
    return false;
  }
}

function doLog($level,$loc,$msg) {
  //Decide where to put logs
  file_put_contents('./logs/log_'.date("j.n.Y").'.txt', $msg, FILE_APPEND);
  //If ERROR send to ADMINS
  if($level === 'ERROR') {
    $to = 'jic6@njit.edu,kn96@njit.edu,kld22@njit.edu,ga68@njit.edu';
    $subj = "ERROR - ".$loc;
    mail($to, $subj, $msg);
  }
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
    case "log_event":
      return doLog($request['level'],$request['loc'],$request['message']);
    case "login":
      return doLogin($request['username'],$request['password']);
    case "signup":
      return doSignup($request['email'],$request['f_name'],$request['l_name'],$request['pass']);
    case "validate_session":
      return doValidate($request['sessionId']);
  }
  return array("returnCode" => '0', 'message'=>"Server received request and processed");
}

$server = new rabbitMQServer("testRabbitMQ.ini","testServer");

$server->process_requests('requestProcessor');
exit();
?>

