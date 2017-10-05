<?php
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
	case "login":
		$req=array();
		$req['type']="login";
		$req['email']=$request["email"];
		$req['pass']=$request["pword"];
		$options = array();
		$options[0] = 'info';
		$options[1] = 'login.php';
		$options[2] = 'before response';
		sendLogs($options);
		$response = $client->send_request($req);
	case "signup":
		$req = array();
		$req['type'] = 'signup';
		$req['email']=$request['email'];
		$req['f_name']=$request['f_name'];
		$req['l_name']=$request['l_name'];
		$req['pass']=$request['pword'];
		$response = $client->send_request($req);
	break;
}

echo JSON.encode($response);
exit(0);

?>
