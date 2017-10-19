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
$client = new rabbitMQClient("rabbitMQReview.ini","testServer");
$request = $_POST;

switch ($request["type"])
{
	case "review": {
		$req=array();
		$req['type']="review";
		$req['email']=$request["email"];
		$req['resid']=$request["resid"];
		$req['rating']=$request["rating"];
		$response = $client->send_request($req);
		break;
	}
}

echo json_encode($response);
exit(0);

?>
