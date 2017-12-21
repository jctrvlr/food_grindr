#!/usr/bin/php
<?php
require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');
require_once('data.php.inc');

function getRest($user, $zip, $last) {
  $dat = new dataProc();
  $output = $dat->getRest($user, $zip, $last);
  return $output;
    // return json array with one restaurant info
}

function findPref(){
  $dat = new dataProc();
  $output = $dat->findPref();
  return $output;
}

function getAllUsers() {
  $dat = new dataProc();
  $output = $dat->getAllUsers();
  return $output;
}

function getRestUsers($restid) {
  $dat = new dataProc();
  $output = $dat->getRestUsers($restid);
  return $output;
}

function deleteUser($userid) {
  $dat = new dataProc();
  $output = $dat->deleteUser($userid);
  return $output;
}

function lockUser($userid) {
  $dat = new dataProc();
  $output = $dat->lockUser($userid);
  return $output;
}

function unlockUser($userid) {
  $dat = new dataProc();
  $output = $dat->unlockUser($userid);
  return $output;
}

function getFavorites($user) {
  $dat = new dataProc();
  $output = $dat->getFavorites($user);
  return $output;
}

function restResp($user, $res_id, $like, $zip, $last) {
  $dat = new dataProc();
  $output = $dat->restResp($user, $res_id, $like, $zip, $last);
  return $output;
    // return json array with one restaurant info after storing response
}

function insertLoc($loc, $ent_type, $ent_id, $lat, $lon, $zip) {
  $dat = new dataProc();
  $output = $dat->insertLoc($loc, $ent_type, $ent_id, $lat, $lon, $zip);
  return $output;
}

function insertRes($res_arr, $ent_id) {
  $dat = new dataProc();
  $output = $dat->insertRes($res_arr, $ent_id);
  return $output;
}

function getData() {
  $dat = new dataProc();
  $output = $dat->adminGetTop();
  return $output;
}

function updateInfo($em, $fn, $ln, $pw) {
  $data = new datadb();
  $output = $data->updateInfo($em, $fn, $ln, $pw);
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
    case "get_all_users":
      return getAllUsers();
    case "get_rest_users":
      return getRestUsers($request['rest_id']);
    case "delete_user":
      return deleteUser($request['user_id']);
    case "lock_user":
      return lockUser($request['user_id']);
    case "unlock_user":
      return unlockUser($request['user_id']);
    case "update_info":
      return updateInfo($request['email'], $request['f_name'], $request['l_name'], $request['pass']);
    case "get_rest":
      return getRest($request['user'], $request['zip'], $request['last']); // last is last restaurant name, user is email
    case "rest_response":
      return restResp($request['user'], $request['res_id'], $request['like'], $request['zip'], $request['last']); //Anything else?
    case "insert_loc":
      return insertLoc($request['loc'], $request['ent_type'], $request['ent_id'], $request['lat'], $request['lon'], $request['zip']);
    case "insert_res":
      return insertRes($request["rest_arr"], $request['ent_id']);
    case "find_pref":
      return findPref();
    case "get_favorites":
      return getFavorites($request['user']);
    case "get_data":
      return getData();
  }
  return array("returnCode" => '0', 'message'=>"Server received request and processed");
}

$server = new rabbitMQServer("rabbitMQData.ini","testServer");

$server->process_requests('requestProcessor');
exit();
?>
