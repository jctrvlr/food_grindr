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
		$req['last'] = $_SESSION['last'];
		$response = $client->send_request($req);
		$json = json_decode($response, true);
		$_SESSION["last"] = $json['name'];
		break;
	}
	case "rest_response": {
		$req = array();
		$req['type'] = 'rest_response';
		$req['user']=$_SESSION['email'];
		$req['res_id']=$request['res_id'];
		$req['like']=$request['like'];
		$req['zip']=$_SESSION['zipcode'];
		$req['last']=$_SESSION['last'];
		$response = $client->send_request($req);
		$json = json_decode($response, true);
		$_SESSION["last"] = $json["name"];
		break;
	}
}

echo json_encode($response);
exit(0);

?>
