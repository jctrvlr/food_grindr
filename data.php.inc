<?php
require_once('rabbitMQLib.inc');
require_once('logger.inc');

class dataProc {
    private $datadb;

    public function __construct() {
        $this->logindb = new mysqli("127.0.0.1","root","Ireland2018","login");

		if ($this->logindb->connect_errno != 0)
		{
			echo "Error connecting to database: ".$this->logindb->connect_error.PHP_EOL;
			// Error logging
			$options = array();
			$options[0] = 'e';
			$options[1] = 'loginServer';
			$options[2] = 'Error connecting to database: '.$this->logindb->connect_error.PHP_EOL;
			sendLogs($options);
			exit(1);
		}
		echo "correctly connected to database".PHP_EOL;
    }
    
    public function getRest($user, $loc, $last) {
        // Get loc rest list and preferences of user
        // TODO Sorting algorithm based on preference?
        // Find $last (last restaurant sent) in array and give next item
        $rest_array = array();
        // Set other fields here from database/restchoose function
        $rest_array['dist'] = distance($lat1, $lon1, $lat2, $lon2, $unit);
        // Return one restaurant
        return $rest_array;
    }

    public function restResp() {
        // Store response in database table 
        // Then call getRest()
    }

    public function insertLoc($loc, $ent_type, $ent_id, $lat, $lon) {
        $loc = $this->logindb->real_escape_string($loc);
		$ent_type = $this->logindb->real_escape_string($ent_type);
        $ent_id = $this->logindb->real_escape_string($ent_id);
        $lat = $this->logindb->real_escape_string($lat);
        $lon = $this->logindb->real_escape_string($lon);

		$statement = "insert into city (name, entity_id, entity_type, longitude, latitude) values ('".$loc."', '".$ent_id."', '".$ent_type."', '".$lon."', '".$lat."');";
		$response = $this->logindb->query($statement);
		$options = array();
		$options[0] = 'debug';
		$options[1] = 'dataServer';
		$options[2] = 'insertLoc function: '. $response;
		sendLogs($options);
		if ($response) {
			return true;
		} else {
			return false;
		}
    }

    public function insertRes($res_arr) {
        // iterate thru array and insert each restaurant
        
    }

    private function checkLocation($loc, $zip, $lat, $lon) {
        // Check if location is in database and if empty send request through rabbitmq to backend to get locations
        if(exists) { // TODO Check if exists in database here

        } else {
            $client = new rabbitMQClient("rabbitMQLoc.ini","testServer");
            $request = array();
            $request['type'] = "get_loc";
            $request['loc'] = $loc;
            $request['lat'] = $lat;
            $request['lon'] = $lon;
            $response = $client->send_request($request); // return true if successfully inserted into db
            if(json_decode($response)) {
                return true;
            } else {
                // Error Logging
				$options = array();
				$options[0] = 'error';
				$options[1] = 'data functions - checkLocation';
				$options[2] = 'Error inserting new restaurant locations into db.';
				sendLogs($options);
            }
        }
    }

    private function getCoord($zip) {
        // Get coordinates from zip/coor database
    }

    private function distance($lat1, $lon1, $lat2, $lon2, $unit) {
        $theta = $lon1 - $lon2;
        $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
        $dist = acos($dist);
        $dist = rad2deg($dist);
        $miles = $dist * 60 * 1.1515;
        $unit = strtoupper($unit);

        if ($unit == "K") {
            return ($miles * 1.609344);
        } else if ($unit == "N") {
            return ($miles * 0.8684);
        } else {
            return $miles;
        }
    }
}