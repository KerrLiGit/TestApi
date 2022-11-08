<?php

class Model_Answer extends Model {

	/*
	 * Find all answers
	 */
	/**
	 * @throws Exception
	 */
	public function get_answer(): array {
		$mysqli = Session::get_sql_connection();
		$users = $mysqli->query('SELECT * FROM answer');
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
	 * Find answer by answerid
	 */
	/**
	 * @throws Exception
	 */
	public function get_answer_index($index): array {
		$mysqli = Session::get_sql_connection();
		$stmt = $mysqli->prepare('SELECT * FROM answer WHERE answerid = ?');
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
	 * Checking if json is answer
	 * Field all_attributes is boolean
	 * If all_attributes is true, json must have all answer attributes, else some attributes may be skipped
	 */
	/**
	 * @throws exception
	 */
	private function is_answer($json, $all_attributes = true): bool {
		$content_pattern = '/^[a-zA-Zа-яА-Я0-9 \.,-]+$/u';
		foreach ($json as $key => $value) {
			if (($key == 'questionid' && is_numeric($value)) ||
				($key == 'content' && preg_match($content_pattern, $value)) ||
				($key == 'correct' && is_bool($value))) {
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
	 * Create new answer
	 */
	/**
	 * @throws Exception
	 */
	public function post_answer(): array {
		$request = (array) json_decode(file_get_contents('php://input'));
		if (empty($request) || !self::is_answer($request, true)) {
			throw new Exception(400);
		}
		$mysqli = Session::get_sql_connection();
		$stmt = $mysqli->prepare('INSERT INTO answer (questionid, content, correct) VALUES (?, ?, ?)');
		$stmt->bind_param('sis', $request['questionid'], $request['content'], $request['correct']);
		if (!$stmt->execute()) {
			throw new Exception(500);
		}
		return array(
			'success' => 'true'
		);
	}

	/*
	 * Update answer (or some answer attributes) by answerid
	 */
	/**
	 * @throws Exception
	 */
	public function put_answer_index($index): array {
		$request = (array) json_decode(file_get_contents('php://input'));
		if (empty($request) || !self::is_answer($request, false)) {
			throw new Exception(400);
		}
		if (!is_numeric($index)) {
			throw new Exception(400);
		}
		$attributes = array();
		foreach ($request as $key => $value) {
			$attributes[] = '`' . $key . '` = "' . $value . '"';
		}
		$query = 'UPDATE answer SET ' . implode(', ', $attributes) . ' WHERE answerid = ' . $index;
		$mysqli = Session::get_sql_connection();
		if (!$mysqli->query($query)) {
			throw new Exception(500);
		}
		return array(
			'success' => 'true'
		);
	}

	/*
	 * Delete answer by answerid
	 */
	/**
	 * @throws Exception
	 */
	public function delete_answer_index($index): array {
		$mysqli = Session::get_sql_connection();
		$stmt = $mysqli->prepare('DELETE FROM answer WHERE answerid = ?');
		$stmt->bind_param('i', $index);
		if (!$stmt->execute()) {
			throw new Exception(500);
		}
		return array(
			'success' => 'true'
		);
	}

}