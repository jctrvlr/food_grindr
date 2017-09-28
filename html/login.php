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
$response = "unsupported request type, politely FUCK OFF";
switch ($request["type"])
{
	case "login":
		$req = array();
		$req['type']="login";
		$req['username']=$request["uname"];
		$req['password']=$request["pword"];
		$response = $client->send_request($req);
	case "signup":
		$req = array();
		$req['type'] = "signup"
	break;
}
echo json_encode($response);
exit(0);

?>
