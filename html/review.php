<?php
session_init();
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
	case "insert_review": {
		$req=array();
		$req['type']="insert_review";
		$req['email']=$_SESSION["email"];
		$req['resid']=$request["res_id"];
		$req['rating']=$request["rating"];
		$response = $client->send_request($req);
		break;
	}
}

echo json_encode($response);
exit(0);

?>
