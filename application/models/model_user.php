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
		$request = (array) json_decode(file_get_contents('php://input'));
		if (!empty($request)) {
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
		$response = $users->fetch_assoc();
		return array(
			'success' => 'true',
			'data' => $response
		);
	}

	/*
	 * Find all users (only one column in $action)
	 */
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
	 * Find user by userid (only one column in $action)
	 */
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
		$response = $users->fetch_assoc();
		return array(
			'success' => 'true',
			'data' => $response
		);
	}

	/*
	 * Checking if json is user
	 */
	/**
	 * @throws exception
	 */
	private function is_user($json): bool {
		$mysqli = Session::get_sql_connection();
		$roles_query = $mysqli->query('SELECT role FROM role');
		$roles = array();
		while ($role = $roles_query->fetch_assoc()) {
			$roles[] = $role['role'];
		}
		$role_pattern = '/^' . implode('|', $roles) . '$/';
		foreach ($json as $key => $value) {
			if (($key == 'name' && preg_match('/^[А-Я][а-я]+ [А-Я][а-я]+ [А-Я][а-я]+$/u', $value)) ||
				($key == 'groupid' && is_numeric($value)) ||
				($key == 'role' && preg_match($role_pattern, $value))) {
				continue;
			}
			else {
				return false;
			}
		}
		return count($json) == 3;
	}

	/*
	 * Create new user
	 */
	/**
	 * @throws Exception
	 */
	public function post_user(): array {
		$request = (array) json_decode(file_get_contents('php://input'));
		if (empty($request) || !self::is_user($request)) {
			throw new Exception(400);
		}
		$mysqli = Session::get_sql_connection();
		$stmt = $mysqli->prepare('INSERT INTO `user` (name, `groupid`, role) VALUES (?, ?, ?)');
		$stmt->bind_param('sss', $request['name'], $request['groupid'], $request['role']);
		if (!$stmt->execute()) {
			Route::addlog($mysqli->error);
			throw new Exception(500);
		}
		return array(
			'success' => 'true'
		);
	}

	/*
	 * Update user by index
	 */
	/**
	 * @throws Exception
	 */
	public function put_user_index($index): array {
		$request = (array) json_decode(file_get_contents('php://input'));
		if (empty($request) || !self::is_user($request)) {
			throw new Exception(400);
		}
		$mysqli = Session::get_sql_connection();
		$stmt = $mysqli->prepare('UPDATE `user` SET name = ?, `groupid` = ?, role = ? WHERE userid = ?');
		$stmt->bind_param('sssi', $request['name'], $request['groupid'], $request['role'], $index);
		if (!$stmt->execute()) {
			throw new Exception(500);
		}
		return array(
			'success' => 'true'
		);
	}

	/*
	 * Update action at user by index
	 */
	/**
	 * @throws Exception
	 */
	public function put_user_index_action($index, $action): array {
		$request = (array) json_decode(file_get_contents('php://input'));
		if (empty($request) || !self::is_user($request)) {
			throw new Exception(400);
		}
		$mysqli = Session::get_sql_connection();
		$stmt = $mysqli->prepare('UPDATE `user` SET `' . $action . '` = ? WHERE userid = ?');
		$stmt->bind_param('si', $request[$action], $index);
		if (!$stmt->execute()) {
			throw new Exception(500);
		}
		return array(
			'success' => 'true'
		);
	}

	/*
	 * Delete user by index
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