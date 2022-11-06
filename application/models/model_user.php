<?php

class Model_User extends Model {

	/*
	 * Authorisation for API users
	 */
	/**
	 * @throws exception
	 */
	public function auth() {
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

	/**
	 * @throws Exception
	 */
	public function get_user(): array {
		$request = (array) json_decode(file_get_contents('php://input'));
		if (!empty($request)) {
			throw new Exception(400);
		}
		$mysqli = Session::get_sql_connection();
		$users = $mysqli->query('SELECT * FROM user');
		$result = array();
		while ($user = $users->fetch_assoc()) {
			$result[] = $user;
		}
		return array(
			'success' => 'true',
			'data' => $result
		);
	}

	/**
	 * @throws Exception
	 */
	public function get_user_index($index): array {
		$request = (array) json_decode(file_get_contents('php://input'));
		if (!empty($request)) {
			throw new Exception(400);
		}
		$mysqli = Session::get_sql_connection();
		$stmt = $mysqli->prepare('SELECT * FROM user WHERE userid = ?');
		$stmt->bind_param('i', $index);
		if (!$stmt->execute()) {
			throw new Exception(500);
		}
		$users = $stmt->get_result();
		$result = $users->fetch_assoc();
		return array(
			'success' => 'true',
			'data' => $result
		);
	}

	/**
	 * @throws Exception
	 */
	public function get_user_action($action): array {
		$request = (array) json_decode(file_get_contents('php://input'));
		if (!empty($request)) {
			throw new Exception(400);
		}
		$mysqli = Session::get_sql_connection();
		$users = $mysqli->query('SELECT `' . $action . '` FROM user');
		$result = array();
		while ($user = $users->fetch_assoc()) {
			$result[] = $user;
		}
		return array(
			'success' => 'true',
			'data' => $result
		);
	}

	/**
	 * @throws Exception
	 */
	public function get_user_index_action($index, $action): array {
		$request = (array) json_decode(file_get_contents('php://input'));
		if (!empty($request)) {
			throw new Exception(400);
		}
		$mysqli = Session::get_sql_connection();
		$stmt = $mysqli->prepare('SELECT `' . $action . '` FROM user WHERE userid = ?');
		$stmt->bind_param('i', $index);
		if (!$stmt->execute()) {
			throw new Exception(500);
		}
		$users = $stmt->get_result();
		$result = $users->fetch_assoc();
		return array(
			'success' => 'true',
			'data' => $result
		);
	}



}