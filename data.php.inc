<?php
require_once('rabbitMQLib.inc');
require_once('logger.inc');

/**
 * Data class that is defined to get and process information from database that is required on the frontend.
 * This is also called to store data that is required for the backend.
 * 
 * @author John Cummings
 * @version 0.1.0
 */
class dataProc {
    private $datadb;
    private $ent_id;
    private $pref;

    /**
     * Constructor
     */
    public function __construct() {
        $this->datadb = new mysqli("127.0.0.1","root","Ireland2018","it490");

		if ($this->datadb->connect_errno != 0)
		{
			echo "Error connecting to database: ".$this->datadb->connect_error.PHP_EOL;
			// Error logging
			$options = array();
			$options[0] = 'e';
			$options[1] = 'loginServer';
			$options[2] = 'Error connecting to database: '.$this->datadb->connect_error.PHP_EOL;
			sendLogs($options);
			exit(1);
		}
		echo "correctly connected to database".PHP_EOL;
    }
    /**
     * ADMIN FUNCTIONS
     */
    
    /**
     * This method is called from the frontend to retrieve
     * a list of the top liked restaurants which are then displayed
     * in a table on the admin page.
     * 
     * @return resp - Associated array of restaurant ID, restaurant name, and sum of likes for said restaurant ordered from most likes to least likes.
     */
    public function adminGetTop() {
        $statement = "SELECT restaurants.res_id, restaurants.name, SUM(responses.liked) AS SUM FROM restaurants INNER JOIN responses ON restaurants.res_id=responses.res_id GROUP BY restaurants.res_id ORDER BY SUM DESC;";
        $resp = mysqli_fetch_all($this->datadb->query($statement), MYSQLI_ASSOC);
        return $resp;
    }
    public function updateInfo($em, $fn, $ln, $pw) {
		if (is_null($fn)) {
			return FALSE;
		} else if (is_null($ln)) {
			return FALSE;
		}
		$f = $this->datadb->real_escape_string($fn);
		$l = $this->datadb->real_escape_string($ln);
		if (is_null($pw)) {
			$st = "UPDATE users SET f_name = '".$f."', l_name = '".$l."' WHERE email='".$em."';";
			$response = $this->datadb->query($st);
			echo "is null".PHP_EOL;
			var_dump($response);
			if(!$response) {
				return false;
			} 
		} else {
			$p = $this->datadb->real_escape_string($pw);
			$pass = password_hash($p, PASSWORD_DEFAULT);
			$st = "UPDATE users SET f_name = '".$f."', l_name = '".$l."', pass = '".$pass."' WHERE email='".$em."';";
			$response = $this->datadb->query($st);
			echo "is not null".PHP_EOL;
			var_dump($response);
			if(!$response) {
				return false;	
			}
		}
		echo "insert prefs".PHP_EOL;
		var_dump($res);
		if(!$res) {
			return false;
		}
		return true;
	}
    public function getAllUsers() {
        $statement = "SELECT id, f_name, l_name, email FROM users;";
        $resp = mysqli_fetch_all($this->datadb->query($statement), MYSQLI_ASSOC);
        return $resp;
    }
    public function getRestUsers($restid) {
        $statement = "SELECT id.users, f_name.users, l_name.users, email.users FROM responses INNER JOIN users ON responses.user = users.email WHERE responses.res_id = '"+$rest+"';";
        $resp = mysqli_fetch_all($this->datadb->query($statement), MYSQLI_ASSOC);
        return $resp;
    }
    public function deleteUser($userid) {
        $statement = "DELETE FROM users WHERE `id` = "+$userid+";";
        $resp = $this->datadb->query($statement);
        if($resp) {
            return true;
        }
    }
    public function lockUser($userid) {
        $statement = "UPDATE users SET `lock` = 1 WHERE `id`="+$userid+";";
        echo "locking user ".$userid.PHP_EOL;
        $resp = $this->datadb->query($statement);
        echo $resp.PHP_EOL;
        if($resp) {
            return true;
        }
    }
    public function unlockUser($userid) {
        $statement = "UPDATE users SET `lock` = 0 WHERE `id`="+$userid+";";
        $resp = $this->datadb->query($statement);
        echo $resp.PHP_EOL;
        if($resp) {
            return true;
        }
    }
    /**
     * END ADMIN FUNCTIONS
     */
    /**
     * This method is called by the frontend to retrieve the favorites for a specified user. These favorite restaurants
     * are then parsed, and displayed on the users favorites page.
     * 
     * @param user - Username of the user that is provided by the frontend through stored session variables that load when the user logs in
     * @return ret - Associative array containing favorites for provided user with restaurant name and rating.
     */
    public function getFavorites($user) {
        $statement = "SELECT responses.user, restaurants.res_id, restaurants.name, restaurants.address, restaurants.longitude, restaurants.latitude, restaurants.cuisine, restaurants.price_range, restaurants.thumbnail, restaurants.online_delivery, restaurants.menu FROM responses INNER JOIN restaurants ON responses.res_id=restaurants.res_id WHERE responses.liked=1 AND responses.user='".$user."' GROUP BY responses.res_id;";
        $resp = mysqli_fetch_all($this->datadb->query($statement), MYSQLI_ASSOC);
        $ret = array();
        for($i=0; $i < count($resp); $i++) {
            $resid = $resp[$i]['res_id'];
            $st = "SELECT * FROM ratings where res_id = ".$resid.";";
            $response = $this->datadb->query($st);
            $count = 0;
            $sum = 0;
            while($row = $response->fetch_assoc()) {
                $count++;
                $sum = $sum + $row['rating'];
            }
            $rating = $sum / $count;
            $rating = (int)round($rating);
            
            $ret[$i]['res'] = $resp[$i];
            $ret[$i]['rating'] = $rating;
        }
        return $ret;
    }

    /**
     * This method is used to retrieve one restaurant and return it to the frontend to be displayed on the main page.
     * 
     * @param user - Username of the user that is provided by the frontend through stored session variables that are loaded when the user logs in.
     * @param zip - Zipcode of the user that is provided by the frontend through stored session variables that are loaded when the user logs in. 
     * @param last - Name of the last restaurant that was returned by this function to the frontend. The frontend provides this by saving the last restaurant name that was returned to it in a session variable. 
     * @return - Returns json encoded array which contains all of the different information that is stored in the database about the restaurant.
     */
    public function getRest($user, $zip, $last) {
        if(!isset($last)) {
            // Get zipcode to send to check location
            $loc_arr = $this->getCoord($zip);
            $check_loc = $this->checkLocation($loc_arr['name'], $zip, $loc_arr['latitude'], $loc_arr['longitude']);
            if($check_loc) {
                $this->ent_id = $this->getEnt($zip);
                $this->getRest($user, $zip, TRUE);
            } else {
                $options = array();
                $options[0] = 'error';
                $options[1] = 'dataServer - getRest';
                $options[2] = 'checkLocation returned false and/or error code';
                sendLogs($options);
            }
        }
        $this->ent_id = $this->getEnt($zip);
        $statement = "select * from restaurants where entity_id = '".$this->ent_id."';";
        // Get restaurants from zipcode
        $resp = mysqli_fetch_all($this->datadb->query($statement), MYSQLI_ASSOC);
        // Get user preferences
        $this->pref = $this->getUser($user);

        $chosen_rest = $this->chooseRest($resp, $last); 
        // Set other fields here from database/restchoose function
        // $rest_array['dist'] = distance($lat1, $lon1, $lat2, $lon2, $unit);
        // Return one restaurant
        return json_encode($chosen_rest);
    }

    /**
     * Retrieves entity code for existing location by using zipcode and returns the entity code
     * 
     * @param zipcode - zipcode of city
     * @return entityid - Entity ID of the zipcode that is stored in the database. 
     */
    private function getEnt($zipcode) {
        $statement = "select * from city where zip=".$zipcode.";";
        $resp = $this->datadb->query($statement);
        $row = mysqli_fetch_array($resp, MYSQLI_ASSOC);
        return $row['entity_id'];
    }

    /**
     * 
     */
    public function restResp($user, $res_id, $like, $zip, $last) {
        $statement = "insert into responses (user, res_id, liked) values ('".$user."','".$res_id."',".$like.");";
        $response = $this->datadb->query($statement);
        if($response) {
            $res = $this->getRest($user, $zip, $last);
        } else {
            $options = array();
            $options[0] = 'error';
            $options[1] = 'dataServer - insertLoc()';
            $options[2] = 'Failed to insert into database: '. $response;
            sendLogs($options);

        }
        // Return one restaurant
        return $res;
    }

    public function insertLoc($loc, $ent_type, $ent_id, $lat, $lon, $zip) {
		$statement = "insert ignore into city (name, entity_id, entity_type, longitude, latitude, zip) values ('".$loc."', '".$ent_id."', '".$ent_type."', '".$lon."', '".$lat."', '".$zip."');";
        $response = $this->datadb->query($statement);
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
    public function insertRes($res_arr, $entity_id) {
        foreach($res_arr as $item) {
            $location = $item['restaurant']['location'];
            $statement = "insert ignore into restaurants (entity_id, res_id, name, address, zipcode, longitude, latitude, cuisine, price_range, thumbnail, online_delivery) values ('".$entity_id."','".$item['restaurant']['id']."','".$item['restaurant']['name']."','".$location['address']."','".$location['zipcode']."','".$location['longitude']."','".$location['latitude']."','".$item['restaurant']['cuisines']."','".$item['restaurant']['price_range']."','".$item['restaurant']['thumb']."','".$item['restaurant']['has_online_delivery']."');";
            $response = $this->datadb->query($statement);

           // if ($response) {
                //return true;
           // } else {
               // $options = array();
               // $options[0] = 'error';
                //$options[1] = 'dataServer - insertRes()';
                //$options[2] = 'Failed to insert into database: '. $response;
                //sendLogs($options);
                //return false;
            //}
        }
        return true;
    }

    private function checkLocation($loc, $zip, $lat, $lon) {
        // Check if location is in database and if empty send request through rabbitmq to backend to get locations
        $statement = "select * from city where zip = '".$zip."';";
        $response = $this->datadb->query($statement);
        if(mysqli_num_rows($response) == 0) { // Check if exists in database here
            $client = new rabbitMQClient("rabbitMQBack.ini","testServer");
            $request = array();
            $request['type'] = "get_loc";
            $request['loc'] = $loc;
            $request['lat'] = $lat;
            $request['lon'] = $lon;
            $request['zip'] = $zip;

            $response = $client->send_request($request); 
            echo "Response from get_loc in checkLocation".$response.PHP_EOL;
            if($response) {
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
    }

    private function getUser($user) {
        $statement = "select cuisine from preferences where email = '".$user."';";
        $response = $this->datadb->query($statement);
        $row = mysqli_fetch_array($response, MYSQLI_ASSOC);

        return $row['cuisine'];
    }   

    private function chooseRest($arr, $last) {
        echo "Inside chooseRest".PHP_EOL;
        $chosen_rest = array();
        $arr = $this->sortArr($arr); // Sorting function when it works
        if(is_string($last)) {
            // Find $last (last restaurant sent) in array and give next item
            $last_key = $this->searchForName($last, $arr);
            echo "Key: ".$last_key;
            $key = $last_key+1;
            if($key > count($arr)-1){
                $key = 0;
            }
            $chosen_rest = $arr[$key];
        } else {
            $chosen_rest = $arr[0];
        }
        return $chosen_rest; // Return one restaurant
    }

    private function searchForName($name, $array) {
        foreach ($array as $key => $val) {
            if ($val['name'] === $name) {
                return $key;
            }
        }
        return null;
    }

    // Utility Functions
    private function getCoord($zip) {
        $statement = "select * from zipcode where zipcode='".$zip."';";
        $response = $this->datadb->query($statement);
        while($row = $response->fetch_assoc()) {
            $loc_arr = array();
            $loc_arr['name']=$row["city"];
            $loc_arr['latitude']=$row["lat"];
            $loc_arr['longitude']=$row["lon"];
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

    // Sorting functions
    private function sortArr($res_array) {
        if(is_null($this->pref)){
            return $res_array;
        }
        $i = 0;
        while($i < count($res_array)) {
            $key = $this->findKey($res_array, "cuisine", $this->pref);
            $moverest = $res_array[$key];
            unset($res_array[$key]);
            array_unshift($res_array, $moverest);
            $i++;
        }    
        return $res_array;
    }

    private function findKey($restaurants, $field, $value)
    {
       foreach($restaurants as $key => $restaurant)
       {
          if (strpos($restaurant[$field], $value))
             return $key;
       }
       return false;
    }



    // Preference calculator
    public function findPref() {
        $statement = "select email from users;";
        // Get different emails
        $resp = mysqli_fetch_all($this->datadb->query($statement), MYSQLI_ASSOC);
        for($x = 0; $x <= count($resp)-1; $x++) {
            $statement = "SELECT responses.res_id, responses.liked, restaurants.cuisine FROM responses INNER JOIN restaurants ON responses.res_id=restaurants.res_id WHERE responses.user = '".$resp[$x]['email']."';";
            $res = mysqli_fetch_all($this->datadb->query($statement), MYSQLI_ASSOC);
            $count = array();
            foreach($res as $item) {
                $cuisine = $item['cuisine'];
                $cuisine = str_replace(' ','',$cuisine);
                $cuisine = explode(",", $cuisine);
                foreach($cuisine as $i) {
                    if(array_key_exists($i, $count)){
                        $count[$i]++;
                    } else {
                        $count[$i]=1;
                    }
                }

            }
            arsort($count);
            $ak = array_keys($count);
            if(empty($ak)) {
                return;
            }
            $st = "UPDATE preferences SET cuisine='".$ak[0]."' WHERE email='".$resp[$x]['email']."';";
            $this->datadb->query($st);
        }
        return true;
    }
}
