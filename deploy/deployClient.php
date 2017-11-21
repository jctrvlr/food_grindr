#!/usr/bin/php
<?php
require_once('../path.inc');
require_once('../get_host_info.inc');
require_once('../rabbitMQLib.inc');

/** PHP arguments - 
*   Command type, 
*       create - 
*           filename (created by client sent to server), origin = gethostname(), name
*       deploy
*           name, version, target
*       deprecate
*           name, version
*       rollback
*           name, version, target
*
*/

$args = parseArgs($argv);
var_dump($args);

switch($args[0]) {
    case("create"): {
        create($args);
        break;
    }
    case("deploy"): {
        deploy($args);
        break;
    }
    case("deprecate"): {
        deprecate($args);
        break;
    }
    case("rollback"): {
        rollback($args);
        break;
    }
    case("help"): {
        $helpmess = "To create a new bundle type './deployClient.php create <name>'";
        echo $helpmess;
        break;
    }
    default: {
        echo $args[0] . " is not a valid command. Type './deployClient.php help' to get more options.";
        break;
    }
}

function create($args) {
    $name = $args[1];
    $cwd = getcwd();
    $tar = gethostname();
    $genname = $name . ".bundle";
    if(strpos($tar, "be") !== false) {
        exec("sudo mysqldump -u root -pIreland2018 it490 > it490.sql");
    }
    exec("sudo cp -r ../".$cwd." /tmp/".$genname);
    $client = new rabbitMQClient("deployRabbit.ini","testServer");
    $req=array();
    $req['type'] = "create_version";
    $req['name'] = $name;
	$req['target'] = $tar;
    $response = $client->send_request($req);
    if($response) {
        echo "Package ".$name." has been saved for deployment.";
    }
}

function deploy($args) {
    $name = $args[1];
    $version = $args[2];
    $target = $args[3];
    $fname = $name."-".$version;

    $client = new rabbitMQClient("deployRabbit.ini","testServer");
    $req=array();
    $req['type'] = "deploy_version";
    $req['name'] = $name;
    $req['version'] = $version;
	$req['target'] = $target;
    $response = $client->send_request($req);
    if($response) {
        runScript($target, $fname);
    }
}

function deprecate($args) {
    $name = $args[1];
    $version = $args[2];

    $client = new rabbitMQClient("deployRabbit.ini","testServer");
    $req=array();
    $req['type'] = "deprecate_version";
    $req['name'] = $name;
    $response = $client->send_request($req);
    if($response) {
        echo $name."-".$version." has been deprecated from the deploy server.".PHP_EOL;
    }
}

function rollback($args) {
    $name = $args[1];
    $version = $args[2];
    $target = $args[3];
    $fname = $name."-".$version;

    $client = new rabbitMQClient("deployRabbit.ini","testServer");
    $req=array();
    $req['type'] = "deploy_version";
    $req['name'] = $name;
    $req['version'] = $version;
	$req['target'] = $target;
    $response = $client->send_request($req);
    if($response) {
        runScript($target, $fname);
    }
}

function runScript($target, $n) {
    list($type, $location) = split('[/.-]', $target);
    switch($type) {
        case("fe"): {
            exec("sudo cp -r /tmp/".$n."/ /var/git/");
            exec("sudo ln -sf /var/git/".$n." /var/www/html/");
            echo "Successfully deployed front-end files.";
        }
        case("be"): {
            exec("sudo cp -r /tmp/".$n."/ /var/git/");
            exec("sudo nohup php /var/git/".$n."/rabbitMQServer.php");
            exec("sudo nohup php /var/git/".$n."/rabbitMQServerData.php");
            exec("sudo nohup php /var/git/".$n."/rabbitMQServerReview.php");
            exec("sudo mysql -u root -pIreland2018 it490 < it490.sql");
            echo "Successfully deployed back-end files.";
        }
        case("dmz"): {
            exec("sudo cp -r /tmp/".$n."/ /var/git/");
            exec("sudo nohup php /var/git/".$n."/php_backend/rabbitMQServerBackend.php");
            echo "Successfully deployed dmz files.";
        }
        default: {
            echo "Error invalid type";
        }
    }
    // run script. depnding on target type
    // if backend, frontend, dmz
}

function parseArgs($argv){
    array_shift($argv); $o = array();
    foreach ($argv as $a){
        if (substr($a,0,2) == '--'){ $eq = strpos($a,'=');
            if ($eq !== false){ $o[substr($a,2,$eq-2)] = substr($a,$eq+1); }
            else { $k = substr($a,2); if (!isset($o[$k])){ $o[$k] = true; } } }
        else if (substr($a,0,1) == '-'){
            if (substr($a,2,1) == '='){ $o[substr($a,1,1)] = substr($a,3); }
            else { foreach (str_split(substr($a,1)) as $k){ if (!isset($o[$k])){ $o[$k] = true; } } } }
        else { $o[] = $a; } }
    return $o;
}

?>