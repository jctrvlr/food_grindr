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
    
    public function getRest($user, $zip, $last) {
        if(!isset($last)) {
            // Get zipcode to send to check location
            $loc_arr = getCoord($zip);
            $check_loc = checkLocation($loc_arr['name'], $zip, $loc_arr['latitude'], $loc_arr['longitude']);
            if($check_loc) {
                getRest($user, $zip, TRUE);
            } else {
                $options = array();
                $options[0] = 'error';
                $options[1] = 'dataServer - getRest';
                $options[2] = 'checkLocation returned false and/or error code';
                sendLogs($options);
            }
        }
        $statement = "select * from restaurants where zipcode = '".$zip."';";
        // Get restaurants from zipcode
        $resp = mysqli_fetch_all($this->logindb->query($statement), MYSQLI_ASSOC);
        // Get user preferences
        $pref = getUser($user);
        $chosen_rest = chooseRest($resp, $last); // Only input resp for now -- Later input preference
        $rest_array = array();
        // Set other fields here from database/restchoose function
        $rest_array['dist'] = distance($lat1, $lon1, $lat2, $lon2, $unit);
        // Return one restaurant
        return json_encode($rest_array);
    }

    

    public function restResp($user, $res_id, $like, $zip, $last) {
        $statement = "insert into responses (user, res_id, liked) values ('".$user."','".$res_id."','".$like."');";
        $response = $this->logindb->query($statement);
        if($response) {
            
        }
        // Store response in database table 
        // Then call getRest()
        $res = getRest($user, $zip, $last);
        // Return one restaurant
        return $res;
    }

    public function insertLoc($loc, $ent_type, $ent_id, $lat, $lon) {
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
            $options = array();
            $options[0] = 'error';
            $options[1] = 'dataServer - insertLoc()';
            $options[2] = 'Failed to insert into database: '. $response;
            sendLogs($options);
			return false;
		}
    }
    // TODO FINISH INSERTRES
    public function insertRes($res_arr) {
        $json = json_decode($res_arr, true);
        foreach($json as $item) {
            $location = json_decode($item['location'], true);
            $statement = "insert ignore into restaurants (res_id, name, address, zipcode, longitude, latitude, cuisine, price_range, thumbnail, online_delivery) values ('".$item['id']."','".$item['name']."','".$location['address']."','".$location['zipcode']."','".$location['longitude']."','".$location['latitude']."','".$item['cuisines']."','".$item['price_range']."','".$item['thumb']."','".$item['has_online_delivery']."');";
            $response = $this->logindb->query($statement);

            if ($response) {
                return true;
            } else {
                $options = array();
                $options[0] = 'error';
                $options[1] = 'dataServer - insertRes()';
                $options[2] = 'Failed to insert into database: '. $response;
                sendLogs($options);
                return false;
            }
        }
    }

    private function checkLocation($loc, $zip, $lat, $lon) {
        // Check if location is in database and if empty send request through rabbitmq to backend to get locations
        $statement = "select * from city where zip = '".$zip."';";
        $response = $this->logindb->query($statement);
        if($response) { // Check if exists in database here
            if($response->num_rows === 0) {
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
            } else {
                return true;
            }
        } else {
           // Error Logging
           $options = array();
           $options[0] = 'error';
           $options[1] = 'data functions - checkLocation';
           $options[2] = 'Error querying db.';
           sendLogs($options);
        }
    }

    private function getUser($user) {
        //TODO WRITE CODE TO GET USER -- pref etc.
    }   

    private function sortArr($res_array, $pref) {
        //TODO WRITE SORTING ALGORITHM BASD ON PREFERENCE
    }

    private function chooseRest($res_arr, $last) {
        if(is_string($last)) {
            // Find $last (last restaurant sent) in array and give next item
            $last_key = array_search($last, $res_arr);
            $chosen_rest = $res_arr[$last_key+1];
        } else {
            $chosen_rest = $res_arr[0];
        }
        // TODO Sorting algorithm based on preference? sortArr()
        return $chosen_rest; // Return one restaurant
    }

    private function getCoord($zip) {
        $statement = "select * from zips where zipcode='".$zip."';";
        $response = $this->logindb->query($statement);
        while($row = $response->fetch_assoc()) {
            $loc_arr = array();
            $loc_arr['name']->$row["City"];
            $loc_arr['latitude']->$row["Lat"];
            $loc_arr['longitude']->$row["Long"];
            return $loc_arr;
        }
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