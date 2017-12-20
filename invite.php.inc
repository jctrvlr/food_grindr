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
class inviteProc {
    private $db;
    private $ent_id;
    private $pref;

    /**
     * Constructor
     */
    public function __construct() {
        $this->db = new mysqli("127.0.0.1","root","Ireland2018","it490");

		if ($this->db->connect_errno != 0)
		{
			echo "Error connecting to database: ".$this->db->connect_error.PHP_EOL;
			// Error logging
			$options = array();
			$options[0] = 'e';
			$options[1] = 'loginServer';
			$options[2] = 'Error connecting to database: '.$this->db->connect_error.PHP_EOL;
			sendLogs($options);
			exit(1);
		}
		echo "correctly connected to database".PHP_EOL;
    }
    public function createInvite($user, $rest, $date, $time, $recip) {
        if($this->checkUser($recip)) {
            // user has account
            $statement = "INSERT INTO invites (usr_email, recip_email, rest, inv_date, inv_time, inv_status) VALUES ('"+$user+"', '"+$recip+"', '"+$rest+"', '"+$date+"', '"+$time+"', 'pending');";
            $resp = $this->db->query($statement);
            $type = "curr_user";
            $this->sendEmail($recip, $type, $rest, $date, $time, $user);
            $this->createNotif($recip, $rest, $date, $time, $user);
            $succ = array();
            $succ["type"] = "curr_user";
            $succ["rest"] = $rest;
            $succ["date"] = $date;
            $succ["time"] = $time;
            $succ["recip"] = $recip;
            return $succ;
        } else {
            // user doesn't have account yet
            $statement = "INSERT INTO invites (usr_email, recip_email, rest, inv_date, inv_time, inv_status) VALUES ('"+$user+"', '"+$recip+"', '"+$rest+"', '"+$date+"', '"+$time+"', 'pending');";
            $resp = $this->db->query($statement);
            $type = "non_user";
            $this->sendEmail($recip, $type, $rest, $date, $time, $user);
            $this->createNotif($recip, $rest, $date, $time, $user);
            $succ = array();
            $succ["type"] = "non_user";
            $succ["rest"] = $rest;
            $succ["date"] = $date;
            $succ["time"] = $time;
            $succ["recip"] = $recip;
            return $succ;
        }
    }
    public function getInvites($user) {
        $statement = "SELECT * FROM invites WHERE usr_email = '"+$user+"';";
        $response = mysqli_fetch_all($this->db->query($statement), MYSQLI_ASSOC);
        if(mysqli_num_rows($response) == 0) {
            // no Invites
            return false;
        } else {
            return $response;
        }
    }
    public function getNotif($user) {
        $statement = "SELECT * FROM notifications WHERE usr_email = '"+$user+"';";
        $response = mysqli_fetch_all($this->db->query($statement), MYSQLI_ASSOC);
        if(mysqli_num_rows($response) == 0) {
            // no Notifications
            return false;
        } else {
            return $response;
        }
    }
    public function updateNotif($notif_id, $status) {
        $statement = "UPDATE notifications SET notif_status = '"+$status+"' WHERE notif_id = '"+$notif_id+"';";
        $resp = $this->db->query($statement);
        return true;
    }
    public function updateInvite($invite_id, $status) {
        $statement = "UPDATE invites SET inv_status = '"+$status+"' WHERE inv_id = '"+$invite_id+"';";
        $resp = $this->db->query($statement);
        return true;
    }
    private function createNotif($recip, $rest, $date, $time, $user) {
        $statement = "INSERT INTO notifications (notif_type, usr_email, notif_rest, notif_date, notif_time, notif_inviter, notif_status) VALUES ('invite', '"+ $recip + "', '"+$rest+"', '"+$date+"', '"+$time+"', '"+$user+"', 'pending');";
        $resp = $this->db->query($statement);
        if($resp) {
            return true;
        } else {
            // Error logging
			$options = array();
			$options[0] = 'e';
			$options[1] = 'inviteServer';
			$options[2] = 'Error creating notification'.PHP_EOL;
			sendLogs($options);
        }
    }
    private function checkUser($recip) {
        $statement = "SELECT * FROM users WHERE email = '"+$recip+"';";
        $response = mysqli_fetch_all($this->db->query($statement), MYSQLI_ASSOC);
        if(mysqli_num_rows($response) == 0) {
            return false;
        } else {
            return true;
        }
    }
    private function sendEmail($recip, $type, $rest, $date, $time, $user) {
        if($type === "curr_user") {
            $msg = $user + " has invited you to join him/her at " + $rest + " on " + $date + " at " + $time;
            $msg = wordwrap($msg, 70);
            $sbjt = "Invite to " + $rest + " from " + $user;
            mail($recip, $sbjt, $msg);
            return;
        } else if ($type === "non_user") {
            $msg = $user + " has invited you to join him/her at " + $rest + " on " + $date + " at " + $time;
            $msg .= "To accept this invite you must signup for Food Grindr! Sign up now and you will be able to accept the invite and view other restaurants in your area.";
            $msg = wordwrap($msg, 70);
            $sbjt = "Invite to Food Grindr and " + $rest + " from " + $user;
            mail($recip, $sbjt, $msg);
            return;
        } else {
            // Error logging
			$options = array();
			$options[0] = 'e';
			$options[1] = 'inviteServer';
			$options[2] = 'Error sending email'.PHP_EOL;
            sendLogs($options);
            return;
        }
    }
}
