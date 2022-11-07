<?php

require_once "xmysqli.php";

class Api {

	/*
	 * Authorisation for API users
	 */
	/**
	 * @throws exception
	 */
	public static function auth() {
		if (array_key_exists('HTTP_APIKEY', $_SERVER)) {
			$mysqli = Session::get_sql_connection();
			$stmt= $mysqli->prepare('SELECT COUNT(*) FROM apikey WHERE apikey = ?');
			$stmt->bind_param('s', $_SERVER['HTTP_APIKEY']);
			if (!$stmt->execute()) {
				throw new Exception(500);
			}
			$right_key = $stmt->get_result()->fetch_assoc()['COUNT(*)'];
			if ($right_key)
				return;
			throw new Exception(403);
		}
		throw new Exception(401);
	}

}