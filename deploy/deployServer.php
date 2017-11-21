#!/usr/bin/php
<?php
require_once('../path.inc');
require_once('../get_host_info.inc');
require_once('../rabbitMQLib.inc');

$connect = new mysqli("127.0.0.1","root","monkey2017","deploy");

function doLog($level,$loc,$msg) 
{
  //Decide where to put logs
  file_put_contents('./logs/log_'.date("j.n.Y").'.txt', $msg, FILE_APPEND);
  //If ERROR send to ADMINS
  if($level === 'ERROR') 
  {
    $to = 'jic6@njit.edu,kn96@njit.edu,kld22@njit.edu,ga68@njit.edu';
    $subj = "ERROR - ".$loc;
    mail($to, $subj, $msg);
  }
}

    
function createVersion($target, $name) 
{
    global $connect;
    // find ip of target computer
    $sql = "select ip from hostname where host = '".$target."';";
    $result = $connect->query($sql);

    while($row = $result->fetch_assoc())
    {
        $ip = $row["ip"];
    }

    $sql = "select version from version where bundle = '".$name."';";
    $result = $connect->query($sql);
       
    //  figure out is bundle exists on deploy
    if ($result->num_rows > 0) 
    {
        //  find version #
        while($row = $result->fetch_assoc()) 
        {
            $ver[] = $row["version"];
        }
        
        //  finding the latest version which is the maximum version 
        $version = max($ver);
        
        //  creating the next version
        $version = $version + 1;

        //  log version in db
        $sql = "insert into version (bundle, version) values ('".$name."', '".$version."');";
        $result = $connect->query($sql);
    } 
    else 
    {
        //  creating first version
        $version = 1;

        //  log version in db
        $sql = "insert into version (bundle, version) values ('".$name."', '".$version."');";
        $result = $connect->query($sql);
    }
    
    //  SCP file from temp on client computer
    $scp = 'scp -rv dj@' . $ip . ':/tmp/' . $name . '.bundle /var/bundles/' . $name . '-' . $version;
    exec($scp, $output, $return);
    echo $return;
    // Return will return non-zero upon an error
    if ($return) {
        return true;
    } else {
        return false;
    }
}

function deployVersion($name, $version, $target) 
{
    global $connect;
    // Find ip of target computer
    $sql = "select ip from hostname where host = '".$target."';";
    $result = $connect->query($sql);

    while($row = $result->fetch_assoc())
    {
        $ip = $row["ip"];
    }
   
    //  Does the bundle-version exist?
    $sql = "select * from version where bundle = '".$name."' and version = '".$version."';";
    $result = $connect->query($sql);
    
    if ($result->num_rows > 0)
    {
        //  Copy the bundle from deploy server to temp folder of client
        $scp = 'scp -rv /var/bundles/'.$name.'-'.$version . ' dj@'.$ip.':/tmp/'.$name.'-'.$version.'.bundle';
        exec($scp, $output, $return);
        
        if ($return)
        {
            // Send Error
            return false;
        }
    }
    else
    {
        // send error saying that version does not exist
        return false;
    }
    $client = new rabbitMQClient("deployClient.ini","testServer");
    $req=array();
    $req['type'] = "create_version";
    $req['name'] = $name;
    $req['ver'] = $ver;
    $req['target'] = $target;
    $response = $client->send_request($req);
}

function deprecateVersion($name, $version) 
{
    global $connect;
    //  move the name-version bundle to cold storage (deprecated folder)
    
    //  Find out if version exists
    $sql = "select * from version where bundle = '".$name."' and version = '".$version."';";
    $result = $connect->query($sql);

    if ($result->num_rows > 0)
    {
        //  enable deprecated flag
        $sql = "update version set deprecated = 'Yes' where bundle = '".$name."' and version = '".$version."';";
        $connect->query($sql);

        //  move from bundles to deprecated folder
        $mv = 'sudo mv -f /var/bundles/'.$name.'.bundle /var/deprecated/'.$name.'.bundle';
        exec($scp, $output, $return);

        if (!$return)
        {
            return true;
        } 
        else 
        {
            return false;
        }
}
}

function rollback($name, $version, $target) 
{
    global $connect;
    //  Rollback 1 version
    //  check if version 1
    if ($version == 1)
    {
        return false;
    }
    else 
    {
        // find out if version exists
        $sql = "select * from version where bundle = '".$name."' and version = '".$version."';";
        $result = $connect->query($sql);

        if ($result->num_rows > 0)
        {
            //find ip of client
            $sql = "select ip from hostname where host = '".$target."';";
            $result = $connect->query($sql);
        
            while($row = $result->fetch_assoc())
            {
                $ip = $row["ip"];
            }
            
            //  using previous version
            $version = $version - 1;

            //  moving to tmp
            $scp = 'scp -rv /var/bundles/'.$name.'-'.$version . ' dj@'.$ip.':/tmp/'.$name.'-'.$version.'.bundle';
            exec($scp, $output, $return);

            if ($return)
            {
                // Send Error
                return "false";
            }
            $client = new rabbitMQClient("deployClient.ini","testServer");
            $req=array();
            $req['type'] = "create_version";
            $req['name'] = $name;
            $req['ver'] = $ver;
            $req['target'] = $target;
            $response = $client->send_request($req);
        }
    }
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
    case "create_version":
        return createVersion($request['target'], $request['name']);
    case "deploy_version":
        return deployVersion($request['name'], $request['version'], $request['target']);
    case "deprecate_version":
        return deprecateVersion($request['name'], $request['version']);
    case "rollback":
        return rollback($request['name'], $request['version'], $request['target']);
    case "log_event":
      return doLog($request['level'],$request['loc'],$request['message']);
  }
  return array("returnCode" => '0', 'message'=>"Server received request and processed");
}

$server = new rabbitMQServer("deployRabbit.ini","testServer");

$server->process_requests('requestProcessor');
exit();
?>