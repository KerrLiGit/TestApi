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
				($key == 'testid' && is_numeric($value)) ||
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
		$stmt = $mysqli->prepare('INSERT INTO question (name, testid, content, `type`) VALUES (?, ?, ?, ?)');
		$stmt->bind_param('siss',
			$request['name'], $request['testid'], $request['content'], $request['type']);
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
					'type' => $question['type']
				)
			);
		}
		if ($question['type'] == 'radio') {
			$stmt = $mysqli->prepare('
				(
    				(SELECT answerid, content FROM answer 
    				  WHERE questionid = ? AND correct = true LIMIT 1)
    				UNION
    				(SELECT answerid, content FROM answer 
    				  WHERE questionid = ? AND correct = false ORDER BY RAND(?) LIMIT ?)
				)
				ORDER BY RAND(?)'
			);
		}
		else if ($question['type'] == 'checkbox') {
			$stmt = $mysqli->prepare('
				(
					(SELECT answerid, content FROM answer 
					  WHERE questionid = ? AND correct = true LIMIT 1)
    				UNION 
    				(SELECT answerid, content FROM answer 
    				  WHERE questionid = ? ORDER BY RAND(?) LIMIT ?)
    			) 
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
	 * Check answers for question by questionid and seed
	 * NOT TESTED!!!
	 */
	/**
	 * @throws exception
	 */
	public function check($index, $seed = null):array {
		$request = (array) json_decode(file_get_contents('php://input'));
		if (is_array($request) && count($request) == 0) {
			return array(
				'success' => 'true',
				'data' => array(
					'score' => 0
				)
			);
		}
		if (empty($request) || !is_array($request) || ($request != null && !array_key_exists(0, $request))) {
			throw new Exception(400);
		}
		$generated_question = $this->generate($index, $seed)['data'];
		$mysqli = Session::get_sql_connection();
		$stmt = $mysqli->prepare('SELECT score FROM questiontype WHERE `type` = ?');
		$stmt->bind_param('s', $generated_question['type']);
		if (!$stmt->execute()) {
			throw new Exception(500);
		}
		$max_score = $stmt->get_result()->fetch_assoc()['score'];
		$score = 0;
		Route::addlog(print_r($generated_question, true));
		if ($generated_question['type'] == 'radio') {
			if (count($request) > 1) {
				throw new Exception(400);
			}
			$stmt->prepare('SELECT correct FROM answer WHERE answerid = ?');
			if (!array_key_exists('answerid', (array) $request[0])) {
				throw new Exception(400);
			}
			$answerid = ((array) $request[0])['answerid'];
			$stmt->bind_param('i', $answerid);
			if (!$stmt->execute()) {
				throw new Exception(500);
			}
			$correct = $stmt->get_result()->fetch_assoc()['correct'];
			if ($correct) {
				$score = $max_score;
			}
		}
		else if ($generated_question['type'] == 'checkbox') {
			$correct_cnt = 0;
			foreach ($request as $answer) {
				if (!array_key_exists('answerid', $answer)) {
					throw new Exception(400);
				}
				$answerid = $answer['answerid'];
				$stmt->prepare('SELECT correct FROM answers WHERE answerid = ?');
				$stmt->bind_param('i', $answerid);
				if (!$stmt->execute()) {
					throw new Exception(500);
				}
				$correct = $stmt->get_result()->fetch_assoc()['correct'];
				if ($correct) {
					$correct_cnt++;
				}
				else {
					$correct_cnt = 0;
					break;
				}
			}
			if ($correct_cnt == 0)
				$score = 0;
			else {
				// проверить что при всез правильных ответах score будет равен max_score
				$score = round($max_score * (count($request) / $correct_cnt));
			}
		}
		else if ($generated_question['type'] == 'text') {
			if (!array_key_exists('content', (array) $request[0])) {
				throw new Exception(400);
			}
			$content = ((array) $request[0])['content'];
			$stmt = $mysqli->prepare('SELECT correct FROM answer WHERE content = ?');
			$stmt->bind_param('s', $content);
			if (!$stmt->execute()) {
				throw new Exception(400);
			}
			$result = $stmt->get_result();
			if ($correct = $result->fetch_assoc()['correct']) {
				if ($correct) {
					$score = $max_score;
				}
			}
		}
		return array(
			'success' => 'true',
			'data' => array(
				'score' => $score
			)
		);
	}

}