<?php
require(dirname(__FILE__) . 'get_host_info.inc');
require(dirname(__FILE__) . 'path.inc');
require(dirname(__FILE__) . 'rabbitMQLib.inc');
require(dirname(__FILE__) . 'logger.inc');

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
$samp_options = array();
$samp_options[0] = 'debug';
$samp_options[1] = 'login.php';
$samp_options[2] = 'Right before sending back response: '.$response;
sendLogs($samp_options);

echo json_encode($response);
echo 'test';
exit(0);

?>
