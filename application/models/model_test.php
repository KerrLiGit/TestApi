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
		$tests = $mysqli->query('SELECT * FROM test');
		$response = array();
		while ($test = $tests->fetch_assoc()) {
			$response[] = $test;
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
		$test = $stmt->get_result();
		$response = $test->fetch_assoc();
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
		$stmt = $mysqli->prepare('INSERT INTO test (`name`, courseid, `number`) VALUES (?, ?, ?)');
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

	/*
	 * Generate answers for question by testid and seed
	 */
	/**
	 * @throws Exception
	 */
	public function generate($testid, $seed) {
		$mysqli = Session::get_sql_connection();
		$stmt = $mysqli->prepare('SELECT courseid, `name`, `number` FROM test WHERE testid = ?');
		$stmt->bind_param('i', $testid);
		if (!$stmt->execute()) {
			throw new Exception(500);
		}
		$test = $stmt->get_result()->fetch_assoc();
		$stmt = $mysqli->prepare('SELECT questionid, `name`, content, `type` FROM question 
										WHERE testid = ? ORDER BY RAND(?) LIMIT ?');
		$stmt->bind_param('iii',$testid, $seed, $test['number']);
		if (!$stmt->execute()) {
			throw new Exception(500);
		}
		$result = $stmt->get_result();
		$questions = array();
		while ($question = $result->fetch_assoc()) {
			require_once 'model_question.php';
			$model_question = new Model_Question();
			$question['answers'] = $model_question->generate($question['questionid'], $seed)['data'];
			$questions[] = $question;
		}
		return array(
			'success' => 'true',
			'data' => array(
				'name' => $test['name'],
				'courseid' => $test['courseid'],
				'number' => $test['number'],
				'questions' => $questions
			)
		);
	}

	/*
	 * Check answers for questions in test by testid and seed
	 */
	/**
	 * @throws Exception
	 */
	public function check($index, $seed = null) {
		$request = (array) json_decode(file_get_contents('php://input'));
		if (empty($request)) {
			throw new Exception(400);
		}
		else if (is_array($request) && count($request) == 0) {
			return array(
				'success' => 'true',
				'data' => array(
					'score' => 0
				)
			);
		}

		foreach ($request as $question) {

		}

	}

}