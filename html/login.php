<?php
require_once('get_host_info.inc');
require_once('path.inc');
require_once('rabbitMQLib.inc');

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
	case "login":
		$req=array();
		$req['type']="login";
		$req['username']=$request["uname"];
		$req['password']=$request["pword"];
		$response = $client->send_request($req);
	
	case "signup":
		$req=array();
		$req['type']="signup";
		$req['email']=$request['email'];
		$req['f_name']=$request['f_name'];
		$req['l_name']=$request['l_name'];
		$req['pass']=$request['pass'];
		$response=$client->send_request($req);
	default:
		break;
}
echo json_encode($response);
exit(0);

?>
