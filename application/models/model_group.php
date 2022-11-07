<?php

class Model_Group extends Model {

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
	 * Find all groups
	 */
	/**
	 * @throws Exception
	 */
	public function get_group(): array {
		$request_string = file_get_contents('php://input');
		$request = (array) json_decode($request_string);
		if (!empty($request_string) || !empty($request)) {
			throw new Exception(400);
		}
		$mysqli = Session::get_sql_connection();
		$users = $mysqli->query('SELECT * FROM `group`');
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
	 * Find group by groupid
	 */
	/**
	 * @throws Exception
	 */
	public function get_group_index($index): array {
		$request_string = file_get_contents('php://input');
		$request = (array) json_decode($request_string);
		if (!empty($request_string) || !empty($request)) {
			throw new Exception(400);
		}
		$mysqli = Session::get_sql_connection();
		$stmt = $mysqli->prepare('SELECT * FROM `group` WHERE groupid = ?');
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
	 * Find all groups (only one attribute in $action)
	 */
	/**
	 * @throws Exception
	 */
	public function get_group_action($action): array {
		$request_string = file_get_contents('php://input');
		$request = (array) json_decode($request_string);
		if (!empty($request_string) || !empty($request)) {
			throw new Exception(400);
		}
		$mysqli = Session::get_sql_connection();
		$users = $mysqli->query('SELECT `' . $action . '` FROM `group`');
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
	 * Find group by groupid (only one attribute in $action)
	 */
	/**
	 * @throws Exception
	 */
	public function get_group_index_action($index, $action): array {
		$request_string = file_get_contents('php://input');
		$request = (array) json_decode($request_string);
		if (!empty($request_string) || !empty($request)) {
			throw new Exception(400);
		}
		$mysqli = Session::get_sql_connection();
		$stmt = $mysqli->prepare('SELECT `' . $action . '` FROM `group` WHERE groupid = ?');
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
	 * Checking if json is group
	 * Field all_attributes is boolean
	 * If all_attributes is true, json must have all group attributes
	 * Else some attributes may be skipped
	 */
	/**
	 * @throws exception
	 */
	private function is_group($json, $all_attributes = true): bool {
		$name_pattern = '/^[А-Я]{4}(-[0-9]{2}){2}$/u';
		foreach ($json as $key => $value) {
			if (($key == 'name' && preg_match($name_pattern, $value))) {
				continue;
			}
			else {
				return false;
			}
		}
		if ($all_attributes === true) {
			return count($json) == 1;
		}
		return true;
	}

	/*
	 * Create new group
	 */
	/**
	 * @throws Exception
	 */
	public function post_group(): array {
		$request = (array) json_decode(file_get_contents('php://input'));
		if (empty($request) || !self::is_group($request, true)) {
			throw new Exception(400);
		}
		$mysqli = Session::get_sql_connection();
		$stmt = $mysqli->prepare('INSERT INTO `group` (name) VALUES (?)');
		$stmt->bind_param('s',  $request['name']);
		if (!$stmt->execute()) {
			throw new Exception(500);
		}
		return array(
			'success' => 'true'
		);
	}

	/*
	 * Update group (or some group attributes) by index
	 */
	/**
	 * @throws Exception
	 */
	public function put_group_index($index): array {
		$request = (array) json_decode(file_get_contents('php://input'));
		if (empty($request) || !self::is_group($request, false)) {
			throw new Exception(400);
		}
		if (!is_numeric($index)) {
			throw new Exception(400);
		}
		$attributes = array();
		foreach ($request as $key => $value) {
			$attributes[] = '`' . $key . '` = "' . $value . '"';
		}
		$query = 'UPDATE `group` SET ' . implode(', ', $attributes) . ' WHERE groupid = ' . $index;
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
	 * Delete group by index
	 */
	/**
	 * @throws Exception
	 */
	public function delete_group_index($index): array {
		$request = (array) json_decode(file_get_contents('php://input'));
		if (!empty($request)) {
			throw new Exception(400);
		}
		$mysqli = Session::get_sql_connection();
		$stmt = $mysqli->prepare('DELETE FROM `group` WHERE groupid = ?');
		$stmt->bind_param('i', $index);
		if (!$stmt->execute()) {
			throw new Exception(500);
		}
		return array(
			'success' => 'true'
		);
	}

}