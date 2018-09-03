<?php

class Users {

	// This function gets called *a lot*, so it must be very quick to run. Cache stuff if necessary.
	public static function LoggedUserID() {
		// This is in the PHP file and sends a Javascript alert to the client
		return isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
	}

	public static function GetUserData($userID) {
		return DB::SingleRow("SELECT dogeaddress, wavesaddress AS doge_address, waves_address FROM users WHERE id = " . DB::DQ($userID) . ";");
	}

	public static function SetDogeAddress($userID, $address) {
		DB::Execute("UPDATE users SET dogeaddress = '" . $address . "' WHERE id = " . DB::DQ($userID) . ";");
	}

	public static function SetWavesAddress($userID, $address) {
		DB::Execute("UPDATE users SET wavesaddress = '" . $address . "' WHERE id = " . DB::DQ($userID) . ";");
	}

    public static function SetWavesPrivatekey($userID, $privatekey) {
		DB::Execute("UPDATE users SET privatekey = '" . $privatekey . "' WHERE id = " . DB::DQ($userID) . ";");
	}

	public static function GetWavesAddress($userID) {
		return DB::SingleRow("SELECT wavesaddress FROM users WHERE id = " . DB::DQ($userID) . ";");
	}

	public static function GetWavesPrivatekey($userID) {
		return DB::SingleRow("SELECT privatekey FROM users WHERE id = " . DB::DQ($userID) . ";");
	}

}
