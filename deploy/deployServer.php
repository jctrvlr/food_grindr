#!/usr/bin/php
<?php
require_once('../path.inc');
require_once('../get_host_info.inc');
require_once('../rabbitMQLib.inc');

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

function createVersion($filename, $target, $name) {
    // SCP bundle with filename from temp on client computer 
    // Figure out if name exists already
}

function deployVersion($name, $version, $target) {
    // SCP bundle to temp folder on client computer and send command to deployClient to run scripts on client computer
}

function deprecateVersion($name, $version) {
    // move the name/version combination bundle to cold storage?
}

function rollback($name, $version, $target) {
    // Rollback 1 version
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
    case "create_version":
        return createVersion($request['target'], $request['name']);
    case "deploy_version":
        return deployVersion($request['name'], $request['version'], $request['target']);
    case "deprecate_version":
        return deprecateVersion($request['name'], $request['version']);
    case "rollback":
        return rollback($request['name'], $request['version'], $request['target']);
    case "log_event":
        return doLog($request['level'],$request['loc'],$request['message']);
  }
  return array("returnCode" => '0', 'message'=>"Server received request and processed");
}

$server = new rabbitMQServer("deployRabbit.ini","testServer");

$server->process_requests('requestProcessor');
exit();
?>