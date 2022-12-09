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
		$questions = $mysqli->query('SELECT * FROM question');
		$response = array();
		while ($question = $questions->fetch_assoc()) {
			$response[] = $question;
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
		$question = $stmt->get_result();
		$response = $question->fetch_assoc();
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
		$mysqli = Session::get_sql_connection();
		$stmt = $mysqli->query('SELECT `type` FROM questiontype');
		$types = array();
		while ($type = $stmt->fetch_assoc()) {
			$types[] = $type['type'];
		}
		$type_pattern = '/^' . implode('|', $types) . '$/u';
		$name_pattern = '/^[a-zA-Zа-яА-Я0-9 \.,-]+$/u';
		foreach ($json as $key => $value) {
			if (($key == 'name' && preg_match($name_pattern, $value)) ||
				($key == 'themeid' && is_numeric($value)) ||
				($key == 'content' && preg_match($name_pattern, $value)) ||
				($key == 'type' && preg_match($type_pattern, $value))) {
				continue;
			}
			else {
				return false;
			}
		}
		if ($all_attributes == true) {
			return count($json) == 4;
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
		$stmt = $mysqli->prepare('INSERT INTO question (name, themeid, content, `type`) VALUES (?, ?, ?, ?)');
		$stmt->bind_param('siss',
			$request['name'], $request['themeid'], $request['content'], $request['type']);
		if (!$stmt->execute()) {
			return array(
				'success' => 'false',
				'error' => array(
					'code' => 200,
					'message' => 'Wrong themeid or type'
				)
			);
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
			return array(
				'success' => 'false',
				'error' => array(
					'code' => 200,
					'message' => 'Wrong themeid or type'
				)
			);
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

	/*
	 * Generate answers for question by questionid and seed
	 */
	/**
	 * @throws exception
	 */
	public function generate($index, $seed = null):array {
		$mysqli = Session::get_sql_connection();
		$stmt = $mysqli->query('SELECT `type`, `limit` FROM questiontype');
		$limit = array();
		while ($questiontype = $stmt->fetch_assoc()) {
			$limit[$questiontype['type']] = $questiontype['limit'];
		}
		$stmt = $mysqli->prepare('SELECT * FROM question WHERE questionid = ?');
		$stmt->bind_param('i', $index);
		if (!$stmt->execute()) throw new Exception(500);
		$question = $stmt->get_result()->fetch_assoc();
		if ($question == null) {
			throw new Exception(400);
		}
		if ($question['type'] == 'text') {
			return array(
				'success' => 'true',
				'data' => array(
					'name' => $question['name'],
					'content' => $question['content'],
					'type' => $question['type'],
					'answers' => array()
				)
			);
		}
		if ($question['type'] == 'radio') {
			$stmt = $mysqli->prepare('
				WITH
				correct_answer AS (
    				SELECT answerid, content FROM answer
    				WHERE questionid = ? AND correct = true
    				LIMIT 1
				),
				incorrect_answer AS (
    				SELECT answerid, content FROM answer
    				WHERE questionid = ? AND correct = false
    				ORDER BY RAND(?) LIMIT ?
				)
				SELECT answerid, content FROM correct_answer
				UNION
				SELECT answerid, content FROM incorrect_answer
				ORDER BY RAND(?)'
			);
		}
		else if ($question['type'] == 'checkbox') {
			$stmt = $mysqli->prepare('
				WITH
				correct_answer AS (
				    SELECT answerid, content FROM answer
				    WHERE questionid = ? AND correct = true
    				LIMIT 1
				),
				incorrect_answer AS (
    				SELECT answerid, content FROM answer
    				WHERE questionid = ? AND answerid NOT IN
        				(SELECT answerid FROM correct_answer)
    				ORDER BY RAND(?) LIMIT ?
				)
				SELECT answerid, content FROM correct_answer
				UNION
				SELECT answerid, content FROM incorrect_answer
				ORDER BY RAND(?)'
			);
		}
		$limit = $limit[$question['type']] - 1;
		$stmt->bind_param('iiiii',
			$question['questionid'], $question['questionid'], $seed, $limit, $seed);
		if (!$stmt->execute()) throw new Exception(500);
		$result = $stmt->get_result();
		$answers = array();
		while ($answer = $result->fetch_assoc()) {
			$answers[] = $answer;
		}
		return array(
			'success' => 'true',
			'data' => array(
				'name' => $question['name'],
				'content' => $question['content'],
				'type' => $question['type'],
				'answers' => $answers
			)
		);
	}

	/*
	 * Counting score in question (type - radio) by user answers
	 */
	/**
	 * @throws exception
	 */
	private function check_radio($max_score, $answers): int {
		foreach ($answers as $answer) {
			$answer = (array) $answer;
			if (array_key_exists('answerid', $answer) && is_numeric($answer['answerid'])) {
				continue;
			}
			throw new Exception(400);
		}
		if (count($answers) > 1) {
			throw new Exception(400);
		}
		$mysqli = Session::get_sql_connection();
		$stmt = $mysqli->prepare('SELECT correct FROM answer WHERE answerid = ?');
		$answerid = ((array) $answers[0])['answerid'];
		$stmt->bind_param('i', $answerid);
		if (!$stmt->execute()) {
			throw new Exception(500);
		}
		$correct = $stmt->get_result()->fetch_assoc()['correct'];
		if ($correct) {
			return $max_score;
		}
		return 0;
	}

	/*
	 * Returns count of correct answers in generated question by questionid and seed
	 */
	/**
	 * @throws Exception
	 */
	private function count_correct_answers($questionid, $seed) {
		$mysqli = Session::get_sql_connection();
		$limit = $mysqli->query(
			'SELECT `limit` FROM questiontype WHERE `type` = "checkbox"')->fetch_assoc()['limit'] - 1;
		$stmt = $mysqli->prepare('
			WITH
			correct_answer AS (
    			SELECT answerid, content, correct FROM answer
    			WHERE questionid = ? AND correct = true
    			ORDER BY RAND(?) LIMIT 1
			),
			incorrect_answer AS (
    			SELECT answerid, content, correct FROM answer
    			WHERE questionid = ? AND answerid NOT IN
        			(SELECT answerid FROM correct_answer)
    			ORDER BY RAND(?) LIMIT ?
			)
			SELECT SUM(cnt) AS sum FROM (
    			SELECT COUNT(*) AS cnt FROM correct_answer WHERE correct = true
   				UNION ALL
   				SELECT COUNT(*) AS cnt FROM incorrect_answer WHERE correct = true
    			ORDER BY RAND(?)
			) AS cnt_answer');
		$stmt->bind_param('iiiiii', $questionid, $seed, $questionid, $seed, $limit, $seed);
		if (!$stmt->execute()) {
			throw new Exception(500);
		}
		return $stmt->get_result()->fetch_assoc()['sum'];
	}

	/*
	 * Counting score in question (type - checkbox) by user answers
	 */
	/**
	 * @throws Exception
	 */
	private function check_checkbox($max_score, $request, $index, $seed): int {
		foreach ($request as $answer) {
			$answer = (array) $answer;
			if (array_key_exists('answerid', $answer) && is_numeric($answer['answerid'])) {
				continue;
			}
			throw new Exception(400);
		}
		$mysqli = Session::get_sql_connection();
		$stmt = $mysqli->prepare('SELECT correct FROM answer WHERE answerid = ?');
		foreach ($request as $answer) {
			$answer = (array)$answer;
			$stmt->bind_param('i', $answer['answerid']);
			if (!$stmt->execute()) {
				throw new Exception(500);
			}
			$correct = $stmt->get_result()->fetch_assoc()['correct'];
			if (!$correct) {
				return 0;
			}
		}
		$correct_cnt = $this->count_correct_answers($index, $seed);
		return (int) round($max_score * count($request) / (float) $correct_cnt);
	}

	/*
	 * Counting score in question (type - text) by user answers
	 */
	/**
	 * @throws Exception
	 */
	private function check_text($max_score, $request) {
		foreach ($request as $answer) {
			$answer = (array) $answer;
			if (array_key_exists('content', $answer) && is_numeric($answer['content'])) {
				continue;
			}
			throw new Exception(400);
		}
		if (count($request) > 1) {
			throw new Exception(400);
		}
		$content = ((array) $request[0])['content'];
		$mysqli = Session::get_sql_connection();
		$stmt = $mysqli->prepare('SELECT correct FROM answer WHERE content = ?');
		$stmt->bind_param('s', $content);
		if (!$stmt->execute()) {
			throw new Exception(400);
		}
		$result = $stmt->get_result();
		if ($correct = $result->fetch_assoc()['correct']) {
			if ($correct) {
				return $max_score;
			}
		}
		return 0;
	}

	/*
	 * Check answers for question by questionid and seed
	 */
	/**
	 * @throws exception
	 */
	public function check($index, $seed = null, $request = null):array {
		if ($request == null) {
			$request = (array)json_decode(file_get_contents('php://input'));
		}
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
		$request = array_unique($request, SORT_REGULAR);
		$mysqli = Session::get_sql_connection();
		$stmt = $mysqli->prepare('SELECT `name`, `type` FROM question WHERE questionid = ?');
		$stmt->bind_param('i', $index);
		if (!$stmt->execute()) {
			throw new Exception(500);
		}
		$question = $stmt->get_result()->fetch_assoc();
		$stmt = $mysqli->prepare('SELECT score FROM questiontype WHERE `type` = ?');
		$stmt->bind_param('s', $question['type']);
		if (!$stmt->execute()) {
			throw new Exception(500);
		}
		$max_score = $stmt->get_result()->fetch_assoc()['score'];
		$score = 0;
		if ($question['type'] == 'radio') {
			$score = $this->check_radio($max_score, $request);
		}
		else if ($question['type'] == 'checkbox') {
			$score = $this->check_checkbox($max_score, $request, $index, $seed);
		}
		else if ($question['type'] == 'text') {
			$score = $this->check_text($max_score, $request);
		}
		return array(
			'success' => 'true',
			'data' => array(
				'score' => $score,
				'max_score' => $max_score
			)
		);
	}

}