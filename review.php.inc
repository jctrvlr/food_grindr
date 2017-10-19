<?php
require_once('logger.inc');

class reviewDB {
	private $reviewdb;

	public function __construct() {
		$this->reviewdb = new mysqli("127.0.0.1","root","Ireland2018","it490");

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

	public function insertReview($email,$resid,$rating)
	{
		$em = $this->reviewdb->real_escape_string($email);
		$ri = $this->reviewdb->real_escape_string($resid);
		$rg = $this->reviewdb->real_escape_string($rating);

		$sql = "insert into ratings (res_id, email, rating) values ('".$ri."', '".$em."', '".$rg."');";
		echo $sql;
		$response = $this->reviewdb->query($statement);
		var_dump($response);
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
		$arr = array();
		$arr["res_id"] = $resid;
		$arr["rating"] = $rating;

		return $arr;

		}
	}
?>
