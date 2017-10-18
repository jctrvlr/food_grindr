<?php
require_once('logger.inc');

class loginDB {
	private $logindb;

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

	public function validateLogin($email,$password) {
		$em = $this->logindb->real_escape_string($email);
		$pw = $password;
		$statement = "select * from users where email = '$em';";
		$response = $this->logindb->query($statement);

		while ($row = $response->fetcsh_assoc())
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
	
	public function signup($email, $f_name, $l_name, $pass) {
		$em = $this->logindb->real_escape_string($email);
		$fn = $this->logindb->real_escape_string($f_name);
		$ln = $this->logindb->real_escape_string($l_name);
		$pw = password_hash($pass, PASSWORD_DEFAULT);

		$statement = "insert into users (f_name, l_name, email, pass) values ('".$fn."', '".$ln."', '".$em."', '".$pw."');";
		$response = $this->logindb->query($statement);
		$options = array();
		$options[0] = 'debug';
		$options[1] = 'loginServer';
		$options[2] = 'Signup function '. $response;
		sendLogs($options);
		if ($response) {
			return true;
		} else {
			return false;
		}
	}
}
?>
