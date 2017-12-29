<?php
require_once('logger.inc');

/**
 * Class that gets reviews and inserts them into a database
 * 
 * @author Khari Davis-Fletcher
 */
class reviewDB {
	private $reviewdb;
	
	/**
	 * Constructor
	 */
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

	/**
	 * Inserts review into ratings db
	 * 
	 * @param email email of user giving review
	 * @param resid restaurant id of restaurant being reviewd
	 * @param rating review of restaurant
	 * @return if there is a response
	 */
	public function insertReview($email,$resid,$rating)
	{

		$em = $email;
		$ri = $resid;
		$rg = $rating;

		$sql = "insert into ratings (res_id, email, rating) values ('".$ri."', '".$em."', '".$rg."');";
		$response = $this->reviewdb->query($sql);
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
	
	/**
	 * Obtains review from db using resid as a unique identifier
	 * 
	 * @param resid used to identify restaurant
	 * @return arr array containing the rating and resid
	 */
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
