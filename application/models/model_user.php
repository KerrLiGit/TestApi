<?php

class Model_User extends Model {

	/*
	 * Find all users
	 */
	/**
	 * @throws Exception
	 */
	public function get_user(): array {
		try {
			$mysqli = Session::get_sql_connection();
			$users = $mysqli->query('SELECT * FROM `user`');
		}
		catch (Exception $e) {
			throw new Exception(500);
		}
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
	 * Checking if json is user
	 * Field all_attributes is boolean
	 * If all_attributes is true, json must have all user attributes, else some attributes may be skipped
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
			return array(
				'success' => 'false',
				'message' => 'Wrong role or groupid'
			);
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
		$mysqli = Session::get_sql_connection();
		if (!$mysqli->query($query)) {
			return array(
				'success' => 'false',
				'message' => 'Wrong some user attributes'
			);
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

	/*
	 * Checking if json is profile
	 * Field all_attributes is boolean
	 * If all_attributes is true, json must have all profile attributes, else some attributes may be skipped
	 */
	/**
	 * @throws exception
	 */
	private function is_profile($json, $all_attributes = true): bool {
		foreach ($json as $key => $value) {
			if (($key == 'login' && is_string($value)) ||
				($key == 'password' && is_string($value))) {
				continue;
			}
			else {
				return false;
			}
		}
		if ($all_attributes) {
			return count($json) == 2;
		}
		return true;
	}

	/*
	 * User registration
	 */
	/**
	 * @throws Exception
	 */
	public function login($index): array {
		$request = (array) json_decode(file_get_contents('php://input'));
		if (empty($request) || !self::is_profile($request, true)) {
			throw new Exception(400);
		}
		$mysqli = Session::get_sql_connection();
		$stmt = $mysqli->prepare('INSERT INTO profile (userid, login, password) VALUES (?, ?, MD5(?))');
		$stmt->bind_param('iss', $index, $request['login'], $request['password']);
		if (!$stmt->execute()) {
			return array(
				'success' => 'false',
				'message' => 'This login already exists'
			);
		}
		return array(
			'success' => 'true'
		);
	}

	/*
	 * User authorisation
	 */
	/**
	 * @throws Exception
	 */
	public function signin():array {
		$request = (array) json_decode(file_get_contents('php://input'));
		if (empty($request) || !self::is_profile($request, true)) {
			throw new Exception(400);
		}
		$mysqli = Session::get_sql_connection();
		$stmt = $mysqli->prepare('SELECT userid FROM profile WHERE login = ? AND password = MD5(?)');
		$stmt->bind_param('ss', $request['login'], $request['password']);
		if (!$stmt->execute()) {
			throw new Exception(500);
		}
		$userid = $stmt->get_result()->fetch_assoc()['userid'];
		if ($userid != null) {
			Session::safe_session_start();
			if (isset($_SESSION) && array_key_exists('userid', $_SESSION)) {
				unset($_SESSION['userid']);
			}
			$_SESSION['userid'] = $userid;
			return array(
				'success' => 'true'
			);
		}
		else {
			return array(
				'success' => 'false',
				'message' => 'Wrong login or password'
			);
		}
	}

	/*
	 * User signout
	 */
	public function signout(): array {
		Session::safe_session_start();
		if (isset($_SESSION) && array_key_exists('userid', $_SESSION)) {
			unset($_SESSION['userid']);
		}
		return array(
			'success' => 'true'
		);
	}

}