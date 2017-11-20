#!/usr/bin/php
<?php
require_once('../path.inc');
require_once('../get_host_info.inc');
require_once('../rabbitMQLib.inc');

global $connect = new mysqli("127.0.0.1","root","monkey2017","bundle");

function doLog($level,$loc,$msg) {
  //Decide where to put logs
  file_put_contents('./logs/log_'.date("j.n.Y").'.txt', $msg, FILE_APPEND);
  //If ERROR send to ADMINS
  if($level === 'ERROR') {
    $to = 'jic6@njit.edu,kn96@njit.edu,kld22@njit.edu,ga68@njit.edu';
    $subj = "ERROR - ".$loc;
    mail($to, $subj, $msg);
  }
}

    //  name = bundle name
    //  target = origin computer
function createVersion($target, $name) {
    // Find ip of target computer
    $sql = "select ip from hostname where host = '".$target."';";
    $result = $connect->query($sql);

    while($row = $result->fetch_assoc())
    {
        $ip = $row["ip"];
    }

    $sql = "select version from version where bundle = '".$name."';";
    $result = $connect->query($sql);
       
    //  Figure out is bundle exists on deploy
    if ($result->num_rows > 0) 
    {
        //  Find version #
        while($row = $result->fetch_assoc()) 
        {
            $ver[] = $row["version"];
        }
        
        $version = max($ver);
        
        $version = $version + 1;

        $sql = "insert into version (bundle, version) values ('".$name."', '".$version."');";
        $result = $connect->query($sql);
    } 
    else 
    {
        $version = 1;
        echo "0 results";
        $sql = "insert into version (bundle, version) values ('".$name."', '".$version."');";
        $result = $connect->query($sql);
    }
    
    //  SCP file from temp on client computer
    $scp = 'scp -rv root@' . $ip . ':/tmp/' . $name . '.bundle /var/bundles/' . $name . '-' . $version;
    exec($scp, $output, $return);
    
    // Return will return non-zero upon an error
    if (!$return) {
        return true;
    } else {
        return false;
    }
}

function deployVersion($name, $version, $target) {
    
    //  SCP bundle to temp folder on client computer and send command to deployClient to run scripts on client computer
    //  scp /var/bundles/bundlename.bundle /tmp/bundlename
    // Find ip of target computer
    $sql = "select ip from hostname where host = '".$target."';";
    $result = $connect->query($sql);

    while($row = $result->fetch_assoc())
    {
        $ip = $row["ip"];
    }
   
    //  Does the bundle-version exist?
    $sql = "select * from version where bundle = '".$name."';";
    $result = $connect->query($sql);
    while($row = $result->fetch_assoc())
    {
        $ver[] = $row['version'];
    }

    $currentVersion = max($ver);
    if($currentVersion == $version)
    {
        //  Copy the bundle from deploy server to temp folder of client
        $scp = 'scp -rv /var/bundles/'.$name.'-'.$version . ' root@'.$ip.':/tmp/'.$name.'-'.$version.'.bundle';
        exec($scp, $output, $return);
        
        if ($return)
        {
            // Send Error
            return "false";
        }
    }
    elseif($currentVersion > $version)
    {
        // send error saying that version is out of date 
    }
    else
    {
        // send error saying that version does not exist
    }

    

    //  If doen't exist send error message
    //  Names of bundles are folders
    //  Get the folder with the name and the version and target is computer (Dev, qa)
    
    //  Find the bundle with the name and version
    //  Use exec
    
    //  Send command to deploy client to run the scripts (runScript)
    //  Make a new client RMQ 
    
    /*
    
        $client = new rabbitMQClient("rabbitMQData.ini","testServer");
        $request = array();
        $request['name'] = $name;
        $request['version'] = $version;
        $request['target'] = $target;
        $response = $client->publish($request);

    */
}

function deprecateVersion($name, $version) {
    // move the name/version combination bundle to cold storage?
    //  Find out if version exists
    $sql = "select * from version where bundle = '".$name."' and version = '".$version."';";
    $result = $connect->query($sql);

    if ($result->num_rows > 0)
    {
        
        $sql = "update version set deprecated = 'Yes' where bundle = '".$name."' and version = '".$version."';";
        $connect->query($sql);

        $mv = 'sudo mv -f /var/bundles/'.$name.'.bundle /var/deprecated/'.$name.'.bundle';
        exec($scp, $output, $return);

        if (!$return) {
            return true;
        } else {
            return false;
        }
    //  move bundle to deprecated folder
    //  add deprecated tag in version db
    //  return true
}

function rollback($name, $version, $target) {
    //  Rollback 1 version
    //  Take the name and version
    //  If version = 1 send error
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
            
            $version = $version - 1;
            $scp = 'scp -rv /var/bundles/'.$name.'-'.$version . ' root@'.$ip.':/tmp/'.$name.'-'.$version.'.bundle';
            exec($scp, $output, $return);

            if ($return)
            {
                // Send Error
                return "false";
            }
        }
    }
    //  Find it and go back one version
    //  If not found send error
    //  Go back on version if found 
    //  SCP to target's temp folder
    //  Send runScript command to target with rollback version
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
        return createVersion($request['filename'], $request['target'], $request['name']);
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

$server = new rabbitMQServer("testRabbitMQ.ini","testServer");

$server->process_requests('requestProcessor');
exit();
?>