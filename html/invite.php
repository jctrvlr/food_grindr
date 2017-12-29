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
$client = new rabbitMQClient("rabbitMQInvite.ini","testServer");
$request = $_POST;

switch ($request["type"])
{
	case "create_invite": {
		$req=array();
		$req['type'] = "create_invite";
		$req['user'] = $_SESSION["email"];
		$req['rest'] = $request['rest'];
        $req['date'] = $request['date'];
        $req['time'] = $request['time'];
        $req['recip'] = $request['recip'];
		$response = $client->send_request($req);
		break;
    }
    case "update_notif": {
        $req=array();
        $req['type'] = "update_notif";
        $req['notif_id'] = $request['notif_id'];
        $req['status'] = $request['status'];
        $response = $client->send_request($req);
        break;
    }
    case "update_invite": {
        $req=array();
        $req['type'] = "update_invite";
        $req['inv_id'] = $request['inv_id'];
        $req['status'] = $request['status'];
        $response = $client->send_request($req);
        break;
    }
    case "get_invites": {
        $req=array();
        $req['type'] = "get_invites";
        $req['user']= $_SESSION["email"];
        $response = $client->send_request($req);
        break;
    }
    case "get_notif": {
        $req=array();
        $req['type'] = "get_notif";
        $req['user']= $_SESSION["email"];
        $response = $client->send_request($req);
        break;
    }
}

echo json_encode($response);
exit(0);

?>
