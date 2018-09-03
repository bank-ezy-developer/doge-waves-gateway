<?php

class DB {
	public static $sql;

	public static function Connect() {
		self::$sql = new mysqli('localhost', 'dbuser', 'dbpassword', 'dbtable');
	}

	public static function SingleRow($query) {
		$rs = self::$sql->query($query);
		self::_check_error($query);
		if($rs && $rs->num_rows != 0){
			return $rs->fetch_array(MYSQLI_ASSOC);
		}
		return null;
	}

	public static function RS($query) {
		$rs = self::$sql->query($query);
		self::_check_error($query);
		if($rs && $rs->num_rows != 0){
			return $rs;
		}
		return null;
	}

	// REFACTOR: This should *always* return an empty array instead of null if there are no rows, to make it easier on foreach's
	//   However, a bunch of functionality might depend on that NULL, so we should refactor those callers, and get rid of the argument
	public static function FillArray($query, $returnEmptyArrayInsteadOfNull = false) {
		$rs = self::$sql->query($query);
		self::_check_error($query);
		if($rs && $rs->num_rows != 0){
			$result = array();
			while ($row = $rs->fetch_array(MYSQLI_ASSOC)) {
				$result[] = $row;
			}
			return $result;
		}
		if ($returnEmptyArrayInsteadOfNull) { return array(); }
		return null;
	}

	public static function Scalar($query) {
		$rs = self::$sql->query($query);
		self::_check_error($query);
		if($rs && $rs->num_rows != 0){
			$row = $rs->fetch_array(MYSQLI_NUM);
			return $row[0];
		}
		return null;
	}

	public static function Execute($query) {
		self::$sql->query($query);
		self::_check_error($query);
	}

	public static function InsertID() {
		return self::$sql->insert_id;
	}

	public static function DQ($value) {
		return self::$sql->real_escape_string($value);
	}


	public static function UniqueRandomID($table, $field, $IDType = "int") {
		do {
			if ($IDType == "hash") {
				$randomID = sha1(microtime() . (string)rand());
			} else {
				$randomID = mt_rand();
			}
			$query = "SELECT " . $field . " FROM " . $table . " WHERE " . $field . " = '" . DB::DQ($randomID) . "'";
			$existingID = DB::Scalar($query);
		} while ($existingID != null);

		return $randomID;
	}


	public static function BeginTransaction() {
		DB::Execute("START TRANSACTION;");
	}
	public static function Commit() {
		DB::Execute("COMMIT;");
	}
	public static function Rollback() {
		DB::Execute("ROLLBACK;");
	}

	private static function _check_error($query) {
		if(self::$sql->errno > 0) {
			trigger_error("MySQL error " . self::$sql->error . "\r\n" . $query . "\r\n");
		}
	}
}


DB::Connect();
