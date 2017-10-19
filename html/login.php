<?php
session_start();
require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');
require_once('logger.inc');


if (!isset($_POST))
{
	$msg = "NO POST MESSAGE SET, POLITELY FUCK OFF";
	echo json_encode($msg);
	exit(0);
}
$client = new rabbitMQClient("testRabbitMQ.ini","testServer");
$request = $_POST;

switch ($request["type"])
{
	case "login": {
		$req=array();
		$req['type']="login";
		$req['email']=$request["email"];
		$req['pass']=$request["pword"];
		$response = $client->send_request($req);
		$json = json_decode($response, true);
		$_SESSION['email'] = $json['resp']['email'];
		$_SESSION['zipcode'] = $json['zipcode'];
		$_SESSION['f_name'] = $json['resp']['f_name'];
		$_SESSION['l_name'] = $json['resp']['l_name'];
		$response = $json['pw'];
		break;
	}
	case "signup": {
		$req = array();
		$req['type'] = 'signup';
		$req['email']=$request['email'];
		$req['f_name']=$request['f_name'];
		$req['l_name']=$request['l_name'];
		$req['pass']=$request['pword'];
		$req['zip']=$request["zip"];
		$response = $client->send_request($req);
		break;
	}
	case "get_settings": {
		$response = array();
		$response['email'] = $_SESSION['email'];
		$response['zipcode'] = $_SESSION['zipcode'];
		$response['f_name'] = $_SESSION['f_name'];
		$response['l_name'] = $_SESSION['l_name'];
		break;
	}
	case "update_info": {
		$req = array();
		$req['type']="update_info";
		$req['zip']=$request['zip'];
		$req['f_name']=$request['f_name'];
		$req['l_name']=$request['l_name'];
		$req['old_em']=$_SESSION['email'];
		if($request['pass'] !== "********") {
			$req['password']=$request['pass'];
		} else {
			$req['password']=NULL;
		}
		$response = $client->send_request($req);
		if($response) {
			$_SESSION['zipcode'] = $request['zip'];
		}
		break;
	}
}

echo json_encode($response);
exit(0);

?>
