<?php
session_start();

require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');
require_once('logger.inc');



// Hardcoded session variables that are set when log in.$_COOKIE
$_SESSION["email"] = "jic6@njit.edu";
$_SESSION["zipcode"] = "07103";

if (!isset($_POST))
{
	$msg = "NO POST MESSAGE SET, POLITELY FUCK OFF";
	echo json_encode($msg);
	exit(0);
}
$client = new rabbitMQClient("rabbitMQData.ini","testServer");
$request = $_POST;

switch ($request["type"])
{
	case "get_res": {
		$req=array();
		$req['type'] = "get_rest";
		$req['user'] = $_SESSION["email"];
		$req['zip'] = $_SESSION["zipcode"];
		$req['last'] = $_SESSION["last"];
		$response = $client->send_request($req);
		$_SESSION["last"] = $response;
		break;
	}
	case "send_res": {
		$req = array();
		$req['type'] = 'signup';
		$req['email']=$request['email'];
		$req['f_name']=$request['f_name'];
		$req['l_name']=$request['l_name'];
		$req['pass']=$request['pword'];
		$response = $client->send_request($req);
		break;
	}
}

echo json_encode($response);
exit(0);

?>
