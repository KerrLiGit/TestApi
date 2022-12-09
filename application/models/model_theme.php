<?php

class Model_Theme extends Model {

	/*
	 * Find all themes
	 */
	/**
	 * @throws Exception
	 */
	public function get_theme(): array {
		$mysqli = Session::get_sql_connection();
		$themes = $mysqli->query('SELECT * FROM theme');
		$response = array();
		while ($theme = $themes->fetch_assoc()) {
			$response[] = $themes;
		}
		return array(
			'success' => 'true',
			'data' => $response
		);
	}

	/*
	 * Find theme by themeid
	 */
	/**
	 * @throws Exception
	 */
	public function get_theme_index($index): array {
		$mysqli = Session::get_sql_connection();
		$stmt = $mysqli->prepare('SELECT * FROM theme WHERE themeid = ?');
		$stmt->bind_param('i', $index);
		if (!$stmt->execute()) {
			throw new Exception(500);
		}
		$theme = $stmt->get_result();
		$response = $theme->fetch_assoc();
		return array(
			'success' => 'true',
			'data' => $response
		);
	}

	/*
	 * Checking if json is theme
	 * Field all_attributes is boolean
	 * If all_attributes is true, json must have all theme attributes, else some attributes may be skipped
	 */
	/**
	 * @throws exception
	 */
	private function is_theme($json, $all_attributes = true): bool {
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
	 * Create new theme
	 */
	/**
	 * @throws Exception
	 */
	public function post_theme(): array {
		$request = (array) json_decode(file_get_contents('php://input'));
		if (empty($request) || !self::is_theme($request, true)) {
			throw new Exception(400);
		}
		$mysqli = Session::get_sql_connection();
		$stmt = $mysqli->prepare('INSERT INTO theme (`name`, courseid, `number`) VALUES (?, ?, ?)');
		$stmt->bind_param('sii', $request['name'], $request['courseid'], $request['number']);
		if (!$stmt->execute()) {
			return array(
				'success' => 'false',
				'error' => array(
					'code' => 200,
					'message' => 'Wrong courseid'
				)
			);
		}
		return array(
			'success' => 'true'
		);
	}

	/*
	 * Update theme (or some theme attributes) by themeid
	 */
	/**
	 * @throws Exception
	 */
	public function put_theme_index($index): array {
		$request = (array) json_decode(file_get_contents('php://input'));
		if (empty($request) || !self::is_theme($request, false)) {
			throw new Exception(400);
		}
		if (!is_numeric($index)) {
			throw new Exception(400);
		}
		$attributes = array();
		foreach ($request as $key => $value) {
			$attributes[] = '`' . $key . '` = "' . $value . '"';
		}
		$query = 'UPDATE theme SET ' . implode(', ', $attributes) . ' WHERE themeid = ' . $index;
		$mysqli = Session::get_sql_connection();
		if (!$mysqli->query($query)) {
			throw new Exception(500);
		}
		return array(
			'success' => 'true'
		);
	}

	/*
	 * Delete theme by themeid
	 */
	/**
	 * @throws Exception
	 */
	public function delete_theme_index($index): array {
		$mysqli = Session::get_sql_connection();
		$stmt = $mysqli->prepare('DELETE FROM theme WHERE themeid = ?');
		$stmt->bind_param('i', $index);
		if (!$stmt->execute()) {
			throw new Exception(500);
		}
		return array(
			'success' => 'true'
		);
	}

	/*
	 * Generate answers for question by themeid and seed
	 */
	/**
	 * @throws Exception
	 */
	public function generate($themeid, $seed) {
		$mysqli = Session::get_sql_connection();
		$stmt = $mysqli->prepare('SELECT courseid, `name`, `number` FROM theme WHERE themeid = ?');
		$stmt->bind_param('i', $themeid);
		if (!$stmt->execute()) {
			throw new Exception(500);
		}
		$theme = $stmt->get_result()->fetch_assoc();
		$stmt = $mysqli->prepare('SELECT questionid, `name`, content, `type` FROM question 
										WHERE themeid = ? ORDER BY RAND(?) LIMIT ?');
		$stmt->bind_param('iii',$themeid, $seed, $theme['number']);
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
				'name' => $theme['name'],
				'courseid' => $theme['courseid'],
				'number' => $theme['number'],
				'questions' => $questions
			)
		);
	}

	/*
	 * Check answers for questions in theme by themeid and seed
	 */
	/**
	 * @throws Exception
	 */
	public function check($themeid, $seed = null) {
		$questions = (array) json_decode(file_get_contents('php://input'));
		if (empty($questions)) {
			throw new Exception(400);
		}
		else if (is_array($questions) && count($questions) == 0) {
			return array(
				'success' => 'true',
				'data' => array(
					'score' => 0
				)
			);
		}
		foreach ($questions as $question) {
			$question = (array) $question;
			if (array_key_exists('questionid', $question) &&
			    array_key_exists('answers', $question)) {
				continue;
			}
			throw new Exception(400);
		}
		$score = 0;
		require_once 'model_question.php';
		foreach ($questions as $question) {
			$question = (array) $question;
			$mysqli = Session::get_sql_connection();
			$stmt = $mysqli->prepare('SELECT themeid FROM question WHERE questionid = ?');
			$stmt->bind_param('i', $question['questionid']);
			if (!$stmt->execute()) {
				throw new Exception(500);
			}
			$question_themeid = $stmt->get_result()->fetch_assoc()['themeid'];
			if ($question_themeid == $themeid) {
				$model = new Model_Question();
				$score += $model->check($question['questionid'], $seed, $question['answers'])['data']['score'];
			}
		}
		$max_score = 0;
		$test = $this->generate($testid, $score)['data'];
		$mysqli = Session::get_sql_connection();
		$stmt = $mysqli->prepare('SELECT score FROM questiontype WHERE type = ?');
		foreach ($test['questions'] as $question) {
			$stmt->bind_param('s', $question['type']);
			if (!$stmt->execute()) {
				throw new Exception(500);
			}
			$max_score += $stmt->get_result()->fetch_assoc()['score'];
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