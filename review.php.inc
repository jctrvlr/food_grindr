<?php
require_once('logger.inc');

class reviewDB {
	private $reviewdb;

	public function __construct() {
		$this->reviewdb = new mysqli("127.0.0.1","root","koolkid20U","it490");

		if ($this->reviewdb->connect_errno != 0)
		{
			echo "Error connecting to database: ".$this->reviewdb->connect_error.PHP_EOL;
			// Error logging
			$options = array();
			$options[0] = 'e';
			$options[1] = 'loginServer';
			$options[2] = 'Error connecting to database: '.$this->reviewdb->connect_error.PHP_EOL;
			sendLogs($options);
			exit(1);
		}
		echo "correctly connected to database".PHP_EOL;
	}

	public function validateLogin($email,$password) {
		$em = $this->reviewdb->real_escape_string($email);
		$pw = $password;
		$statement = "select * from users where email = '$em';";
		$response = $this->reviewdb->query($statement);

		while ($row = $response->fetch_assoc())
		{
			echo "checking password for $email".PHP_EOL;
			if (password_verify($pw, $row["pass"]))
			{
				echo "passwords match for $email".PHP_EOL;
				// Info logging
				$options = array();
				$options[0] = 'info';
				$options[1] = 'loginServer';
				$options[2] = $email.' has logged in.';
				sendLogs($options);
				return true;// password match
			}
			echo "passwords did not match for $email".PHP_EOL;
		}
		// Info logging
		$options = array();
		$options[0] = 'info';
		$options[1] = 'loginServer';
		$options[2] = $email.' cannot be found in database.';
		sendLogs($options);
		
		return false;//no users matched username
	}

	public function insertReview($email,$resid,$rating)
	{
		$em = $this->reviewdb->real_escape_string($email);
		$ri = $this->reviewdb->real_escape_string($resid);
		$rg = $this->reviewdb->real_escape_string($rating);

		$sql = "insert into ratings (res_id, email, rating) values ('".$fn."', '".$ln."', '".$em."');";

		$response = $this->reviewdb->query($statement);
		
		$options = array();
		$options[0] = 'debug';
		$options[1] = 'loginServer';
		$options[2] = 'Review function '. $response;
		sendLogs($options);
		if ($response) {
			return true;
		} else {
			return false;
		}
	}

	public function getReview($resid)
	{
		$sql = "select * from review whee resid = ".$resid.";";
		$response = $this->reviewdb->query($sql);
		while($row = $response->fetch_assoc())
		{
			$count++;
			$sum = $sum + $row['rating'];
		}
		$rating = $sum / $count;
		return $rating;

		}
	}
}
?>
