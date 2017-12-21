<?php
require_once('logger.inc');

class loginDB {
	private $logindb;

	public function __construct() {
		$this->logindb = new mysqli("127.0.0.1","root","Ireland2018","it490");
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

	public function updateInfo($zip, $fn, $ln, $pw, $oe) {
		if(is_null($zip)) {
			return FALSE;
		} else if (is_null($fn)) {
			return FALSE;
		} else if (is_null($ln)) {
			return FALSE;
		}
		$z = $this->logindb->real_escape_string($zip);
		$f = $this->logindb->real_escape_string($fn);
		$l = $this->logindb->real_escape_string($ln);
		if (is_null($pw)) {
			$st = "UPDATE users SET f_name = '".$f."', l_name = '".$l."' WHERE email='".$oe."';";
			$response = $this->logindb->query($st);
			echo "is null".PHP_EOL;
			var_dump($response);
			if(!$response) {
				return false;
			} 
		} else {
			$p = $this->logindb->real_escape_string($pw);
			$pass = password_hash($p, PASSWORD_DEFAULT);
			$st = "UPDATE users SET f_name = '".$f."', l_name = '".$l."', pass = '".$pass."' WHERE email='".$oe."';";
			$response = $this->logindb->query($st);
			echo "is not null".PHP_EOL;
			var_dump($response);
			if(!$response) {
				return false;	
			}
		}
		$st = "UPDATE preferences SET zipcode = '".$z."' WHERE email='".$oe."';";
		$res = $this->logindb->query($st);
		echo "insert prefs".PHP_EOL;
		var_dump($res);
		if(!$res) {
			return false;
		}
		return true;
	}

	public function validateLogin($email,$password) {
		$em = $this->logindb->real_escape_string($email);
		$pw = $password;
		$statement = "select * from users where email = '$em';";
		$response = $this->logindb->query($statement);

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
				$st = "select zipcode from preferences where email='".$em."';";
				$res = $this->logindb->query($st);
				$r = mysqli_fetch_all($this->logindb->query($st), MYSQLI_ASSOC);
				var_dump($r);
				$arr = array();
				$arr['resp'] = $row;
				$arr['pw'] = TRUE;
				$arr['zipcode'] = $r[0]['zipcode'];
				var_dump($arr);
				return json_encode($arr);// password match
			}
			echo "passwords did not match for $email".PHP_EOL;
		}
		// Info logging
		$options = array();
		$options[0] = 'info';
		$options[1] = 'loginServer';
		$options[2] = $email.' cannot be found in database.';
		sendLogs($options);
		$arr = array();
		$arr['pw'] = FALSE;
		return $arr;//no users matched username
	}
	
	public function signup($email, $f_name, $l_name, $pass, $zip) {
		$em = $this->logindb->real_escape_string($email);
		$fn = $this->logindb->real_escape_string($f_name);
		$ln = $this->logindb->real_escape_string($l_name);
		$pw = password_hash($pass, PASSWORD_DEFAULT);
		$zip = $this->logindb->real_escape_string($zip);

		$statement = "insert into users (f_name, l_name, email, pass) values ('".$fn."', '".$ln."', '".$em."', '".$pw."');";
		$response = $this->logindb->query($statement);
		//$options = array();
		//$options[0] = 'debug';
		//$options[1] = 'loginServer';
		//$options[2] = 'Signup function '. $response;
		//sendLogs($options);
		if ($response) {
			$st = "insert into preferences (email, zipcode) values ('".$em."','".$zip."');";
			$res = $this->logindb->query($st);
			if($res) {
				return true;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}
}
?>
