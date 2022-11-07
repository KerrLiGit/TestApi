<?php

class Model_Test extends Model {

	/*
	 * Find all tests
	 */
	/**
	 * @throws Exception
	 */
	public function get_test(): array {
		$mysqli = Session::get_sql_connection();
		$users = $mysqli->query('SELECT * FROM test');
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
	 * Find test by testid
	 */
	/**
	 * @throws Exception
	 */
	public function get_test_index($index): array {
		$mysqli = Session::get_sql_connection();
		$stmt = $mysqli->prepare('SELECT * FROM test WHERE testid = ?');
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
	 * Checking if json is test
	 * Field all_attributes is boolean
	 * If all_attributes is true, json must have all test attributes, else some attributes may be skipped
	 */
	/**
	 * @throws exception
	 */
	private function is_test($json, $all_attributes = true): bool {
		$name_pattern = '/^[a-zA-Zа-яА-Я0-9 \.,-]+$/u';
		foreach ($json as $key => $value) {
			if (($key == 'name' && preg_match($name_pattern, $value)) ||
				($key == 'courseid' && is_numeric($value)) ||
				($key == 'number' && is_numeric($value))) {
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
	 * Create new test
	 */
	/**
	 * @throws Exception
	 */
	public function post_test(): array {
		$request = (array) json_decode(file_get_contents('php://input'));
		if (empty($request) || !self::is_test($request, true)) {
			throw new Exception(400);
		}
		$mysqli = Session::get_sql_connection();
		$stmt = $mysqli->prepare('INSERT INTO test (name, courseid, number) VALUES (?, ?, ?)');
		$stmt->bind_param('sii', $request['name'], $request['courseid'], $request['number']);
		if (!$stmt->execute()) {
			throw new Exception(500);
		}
		return array(
			'success' => 'true'
		);
	}

	/*
	 * Update test (or some test attributes) by testid
	 */
	/**
	 * @throws Exception
	 */
	public function put_test_index($index): array {
		$request = (array) json_decode(file_get_contents('php://input'));
		if (empty($request) || !self::is_test($request, false)) {
			throw new Exception(400);
		}
		if (!is_numeric($index)) {
			throw new Exception(400);
		}
		$attributes = array();
		foreach ($request as $key => $value) {
			$attributes[] = '`' . $key . '` = "' . $value . '"';
		}
		$query = 'UPDATE test SET ' . implode(', ', $attributes) . ' WHERE testid = ' . $index;
		$mysqli = Session::get_sql_connection();
		if (!$mysqli->query($query)) {
			throw new Exception(500);
		}
		return array(
			'success' => 'true'
		);
	}

	/*
	 * Delete test by testid
	 */
	/**
	 * @throws Exception
	 */
	public function delete_test_index($index): array {
		$mysqli = Session::get_sql_connection();
		$stmt = $mysqli->prepare('DELETE FROM test WHERE testid = ?');
		$stmt->bind_param('i', $index);
		if (!$stmt->execute()) {
			throw new Exception(500);
		}
		return array(
			'success' => 'true'
		);
	}

}