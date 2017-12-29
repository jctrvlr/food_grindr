#!/usr/bin/php
<?php
require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');
require_once('invite.php.inc');

/*
// Create Invite
//
// Creates invite, sends notification/email to user if already registered.
// If not registered it sends email inviting user to join service.
//
// @param - $user - User sending invite
// @param - $rest - Restaurant to be invited too
// @param - $date - Date of invite
// @param - $time - Time of invite
// @param - $recip - Email of recipient of invite
*/
function createInvite($user, $rest, $date, $time, $recip) {
  $inv = new inviteProc();
  $output = $inv->createInvite($user, $rest, $date, $time, $recip);
  return $output;
    // return success object
}

function updateNotif($notifId, $status) {
    $inv = new inviteProc();
    $output = $inv->updateNotif($notifId, $status);
    return $output;
}

function updateInvite($invId, $status) {
    $inv = new inviteProc();
    $output = $inv->updateInvite($invId, $status);
    return $output;
}
function getInvites($user) {
    $inv = new inviteProc();
    $output = $inv->getInvites($user);
    return $output;
}
function getNotif($user) {
    $inv = new inviteProc();
    $output = $inv->getNotif($user);
    return $output;
}

function requestProcessor($request)
{
  echo "received request".PHP_EOL;
  var_dump($request);
  if(!isset($request['type']))
  {
    return "ERROR: unsupported message type";
  }
  switch ($request['type'])
  {
    case "create_invite":
        return createInvite($request['user'], $request['rest'], $request['date'], $request['time'], $request['recip']);
    case "update_notif":
        return updateNotif($request['notif_id'], $request['status']);
    case "update_invite":
        return updateInvite($request['inv_id'], $request['status']);
    case "get_invites":
        return getInvites($request['user']);
    case "get_notif":
        return getNotif($request['user']);
  }
  return array("returnCode" => '0', 'message'=>"Server received request and processed");
}

$server = new rabbitMQServer("rabbitMQInvite.ini","testServer");

$server->process_requests('requestProcessor');
exit();
?>
