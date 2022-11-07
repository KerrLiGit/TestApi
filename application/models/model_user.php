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

	/*
	 * Find all users
	 */
	/**
	 * @throws Exception
	 */
	public function get_user(): array {
		$request_string = file_get_contents('php://input');
		$request = (array) json_decode($request_string);
		if (!empty($request_string) || !empty($request)) {
			throw new Exception(400);
		}
		$mysqli = Session::get_sql_connection();
		$users = $mysqli->query('SELECT * FROM `user`');
		$response = array();
		while ($user = $users->fetch_assoc()) {
			$response[] = $user;
		}
		return array(
			'success' => 'true',
			'data' => $response
		);
	}

	/*
	 * Find user by userid
	 */
	/**
	 * @throws Exception
	 */
	public function get_user_index($index): array {
		$request_string = file_get_contents('php://input');
		$request = (array) json_decode($request_string);
		if (!empty($request_string) || !empty($request)) {
			throw new Exception(400);
		}
		$mysqli = Session::get_sql_connection();
		$stmt = $mysqli->prepare('SELECT * FROM `user` WHERE userid = ?');
		$stmt->bind_param('i', $index);
		if (!$stmt->execute()) {
			throw new Exception(500);
		}
		$users = $stmt->get_result();
		$response = $users->fetch_assoc();
		return array(
			'success' => 'true',
			'data' => $response
		);
	}

	/*
	 * Find all users (only one attribute in $action)
	 */
	/**
	 * @throws Exception
	 */
	public function get_user_action($action): array {
		$request_string = file_get_contents('php://input');
		$request = (array) json_decode($request_string);
		if (!empty($request_string) || !empty($request)) {
			throw new Exception(400);
		}
		$mysqli = Session::get_sql_connection();
		$users = $mysqli->query('SELECT `' . $action . '` FROM `user`');
		$response = array();
		while ($user = $users->fetch_assoc()) {
			$response[] = $user;
		}
		return array(
			'success' => 'true',
			'data' => $response
		);
	}

	/*
	 * Find user by userid (only one attribute in $action)
	 */
	/**
	 * @throws Exception
	 */
	public function get_user_index_action($index, $action): array {
		$request_string = file_get_contents('php://input');
		$request = (array) json_decode($request_string);
		if (!empty($request_string) || !empty($request)) {
			throw new Exception(400);
		}
		$mysqli = Session::get_sql_connection();
		$stmt = $mysqli->prepare('SELECT `' . $action . '` FROM `user` WHERE userid = ?');
		$stmt->bind_param('i', $index);
		if (!$stmt->execute()) {
			throw new Exception(500);
		}
		$users = $stmt->get_result();
		$response = $users->fetch_assoc();
		return array(
			'success' => 'true',
			'data' => $response
		);
	}

	/*
	 * Checking if json is user
	 * Field all_attributes is boolean
	 * If all_attributes is true, json must have all user attributes
	 * Else some attributes may be skipped
	 */
	/**
	 * @throws exception
	 */
	private function is_user($json, $all_attributes = true): bool {
		$mysqli = Session::get_sql_connection();
		$roles_query = $mysqli->query('SELECT role FROM role');
		$roles = array();
		while ($role = $roles_query->fetch_assoc()) {
			$roles[] = $role['role'];
		}
		$role_pattern = '/^' . implode('|', $roles) . '$/';
		$name_pattern = '/^[А-Я][а-я]+ [А-Я][а-я]+ [А-Я][а-я]+$/u';
		foreach ($json as $key => $value) {
			if (($key == 'name' && preg_match($name_pattern, $value)) ||
				 ($key == 'groupid' && is_numeric($value)) ||
				 ($key == 'role' && preg_match($role_pattern, $value))) {
				continue;
			}
			else {
				return false;
			}
		}
		if ($all_attributes == true) {
			return count($json) == 3;
		}
		return true;
	}

	/*
	 * Create new user
	 */
	/**
	 * @throws Exception
	 */
	public function post_user(): array {
		$request = (array) json_decode(file_get_contents('php://input'));
		if (empty($request) || !self::is_user($request, true)) {
			throw new Exception(400);
		}
		$mysqli = Session::get_sql_connection();
		$stmt = $mysqli->prepare('INSERT INTO `user` (name, `groupid`, role) VALUES (?, ?, ?)');
		$stmt->bind_param('sss', $request['name'], $request['groupid'], $request['role']);
		if (!$stmt->execute()) {
			throw new Exception(500);
		}
		return array(
			'success' => 'true'
		);
	}

	/*
	 * Update user (or some user attributes) by userid
	 */
	/**
	 * @throws Exception
	 */
	public function put_user_index($index): array {
		$request = (array) json_decode(file_get_contents('php://input'));
		if (empty($request) || !self::is_user($request, false)) {
			throw new Exception(400);
		}
		if (!is_numeric($index)) {
			throw new Exception(400);
		}
		$attributes = array();
		foreach ($request as $key => $value) {
			$attributes[] = '`' . $key . '` = "' . $value . '"';
		}
		$query = 'UPDATE `user` SET ' . implode(', ', $attributes) . ' WHERE userid = ' . $index;
		Route::addlog($query);
		$mysqli = Session::get_sql_connection();
		if (!$mysqli->query($query)) {
			throw new Exception(500);
		}
		return array(
			'success' => 'true'
		);
	}

	/*
	 * Delete user by userid
	 */
	/**
	 * @throws Exception
	 */
	public function delete_user_index($index): array {
		$request = (array) json_decode(file_get_contents('php://input'));
		if (!empty($request)) {
			throw new Exception(400);
		}
		$mysqli = Session::get_sql_connection();
		$stmt = $mysqli->prepare('DELETE FROM `user` WHERE userid = ?');
		$stmt->bind_param('i', $index);
		if (!$stmt->execute()) {
			throw new Exception(500);
		}
		return array(
			'success' => 'true'
		);
	}

}