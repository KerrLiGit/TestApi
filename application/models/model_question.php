<?php

class Model_Question extends Model {

	/*
	 * Find all questions
	 */
	/**
	 * @throws Exception
	 */
	public function get_question(): array {
		$mysqli = Session::get_sql_connection();
		$users = $mysqli->query('SELECT * FROM question');
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
	 * Find question by questionid
	 */
	/**
	 * @throws Exception
	 */
	public function get_question_index($index): array {
		$mysqli = Session::get_sql_connection();
		$stmt = $mysqli->prepare('SELECT * FROM question WHERE questionid = ?');
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
	 * Checking if json is question
	 * Field all_attributes is boolean
	 * If all_attributes is true, json must have all question attributes, else some attributes may be skipped
	 */
	/**
	 * @throws exception
	 */
	private function is_question($json, $all_attributes = true): bool {
		$name_pattern = '/^[a-zA-Zа-яА-Я0-9 \.,-]+$/u';
		foreach ($json as $key => $value) {
			if (($key == 'name' && preg_match($name_pattern, $value)) ||
				($key == 'testid' && is_numeric($value)) ||
				($key == 'content' && preg_match($name_pattern, $value))) {
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
	 * Create new question
	 */
	/**
	 * @throws Exception
	 */
	public function post_question(): array {
		$request = (array) json_decode(file_get_contents('php://input'));
		if (empty($request) || !self::is_question($request, true)) {
			throw new Exception(400);
		}
		$mysqli = Session::get_sql_connection();
		$stmt = $mysqli->prepare('INSERT INTO question (name, testid, content) VALUES (?, ?, ?)');
		$stmt->bind_param('sis', $request['name'], $request['testid'], $request['content']);
		if (!$stmt->execute()) {
			throw new Exception(500);
		}
		return array(
			'success' => 'true'
		);
	}

	/*
	 * Update question (or some question attributes) by questionid
	 */
	/**
	 * @throws Exception
	 */
	public function put_question_index($index): array {
		$request = (array) json_decode(file_get_contents('php://input'));
		if (empty($request) || !self::is_question($request, false)) {
			throw new Exception(400);
		}
		if (!is_numeric($index)) {
			throw new Exception(400);
		}
		$attributes = array();
		foreach ($request as $key => $value) {
			$attributes[] = '`' . $key . '` = "' . $value . '"';
		}
		$query = 'UPDATE question SET ' . implode(', ', $attributes) . ' WHERE questionid = ' . $index;
		$mysqli = Session::get_sql_connection();
		if (!$mysqli->query($query)) {
			throw new Exception(500);
		}
		return array(
			'success' => 'true'
		);
	}

	/*
	 * Delete question by questionid
	 */
	/**
	 * @throws Exception
	 */
	public function delete_question_index($index): array {
		$mysqli = Session::get_sql_connection();
		$stmt = $mysqli->prepare('DELETE FROM question WHERE questionid = ?');
		$stmt->bind_param('i', $index);
		if (!$stmt->execute()) {
			throw new Exception(500);
		}
		return array(
			'success' => 'true'
		);
	}

}