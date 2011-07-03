<?php

abstract class DatabaseManager {

	final static function get($database = false) {
		static $connections = array();

		if ($database === false)
			return $connections;

		$database = $database ? ENVIRONMENT . '_' . $database : ENVIRONMENT;

		if (!isset($connections[$database])) {
			global $_database_environments;

			$connections[$database] = new Database($_database_environments[$database]);
		}

		return $connections[$database];
	}

	final static function get_time() {
		$connections = self::get();

		$time = 0;

		foreach ($connections as $cn)
			$time += $cn->database->_time;

		return $time;
	}

}

?>