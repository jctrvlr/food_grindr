<?php

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
        // Return one restaurant
    }

    public function restResp() {
        // Store response in database table 
    }

    private function checkLocation($loc) {
        // Check if location is in database and if empty send request through rabbitmq to backend to get locations
    }
}