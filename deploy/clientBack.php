#!/usr/bin/php
<?php
require_once('../path.inc');
require_once('../get_host_info.inc');
require_once('../rabbitMQLib.inc');

function runScript($v, $n, $target) {
    $fname = $n."-".$v;
    $key = preg_split('[-]', $target);
    var_dump($key);
    switch($key[1]) {
        case("fe"): {
            exec("sudo cp -r /tmp/".$fname."/ /var/git/");
            exec("sudo ln -sf /var/git/".$fname." /var/www/html/");
            echo "Successfully deployed front-end files.";
            break;
        }
        case("be"): {
            $date = date('m/d/Y h:i:s a', time());
            exec("sudo cp -r /tmp/".$fname."/ /var/git/");
            exec("sudo nohup php /var/git/".$fname."/rabbitMQServer.php >> /var/logs/".$date." &");
            exec("sudo nohup php /var/git/".$fname."/rabbitMQServerData.php >> /var/logs/".$date." &");
            exec("sudo nohup php /var/git/".$fname."/rabbitMQServerReview.php >> /var/logs/".$date." &");
            exec("sudo mysql -u root -pIreland2018 it490 < it490.sql >> /var/logs/".$date." &");
            echo "Successfully deployed back-end files.";
            break;
        }
        case("dmz"): {
            $date = date('m/d/Y h:i:s a', time());
            exec("sudo cp -r /tmp/".$fname."/ /var/git/");
            exec("sudo nohup php /var/git/".$fname."/php_backend/rabbitMQServerBackend.php >> /var/logs/".$date." &");
            echo "Successfully deployed dmz files.";
            break;
        }
        default: {
            echo "Error invalid type";
            break;
        }
    }
    // run script. depnding on target type
    // if backend, frontend, dmz
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
    case "run_script":
        return runScript($request['ver'], $request['name'], $request['target']);
  }
  return array("returnCode" => '0', 'message'=>"Server received request and processed");
}

$server = new rabbitMQServer("deployClient.ini","testServer");

$server->process_requests('requestProcessor');
exit();

?>