<?php

class Model_Course extends Model {

	/*
	 * Find all courses
	 */
	/**
	 * @throws Exception
	 */
	public function get_course(): array {
		$mysqli = Session::get_sql_connection();
		$courses = $mysqli->query('SELECT * FROM course');
		$response = array();
		while ($course = $courses->fetch_assoc()) {
			$response[] = $course;
		}
		return array(
			'success' => 'true',
			'data' => $response
		);
	}

	/*
	 * Find course by courseid
	 */
	/**
	 * @throws Exception
	 */
	public function get_course_index($index): array {
		$mysqli = Session::get_sql_connection();
		$stmt = $mysqli->prepare('SELECT * FROM course WHERE courseid = ?');
		$stmt->bind_param('i', $index);
		if (!$stmt->execute()) {
			throw new Exception(500);
		}
		$course = $stmt->get_result();
		$response = $course->fetch_assoc();
		return array(
			'success' => 'true',
			'data' => $response
		);
	}

	/*
	 * Checking if json is course
	 * Field all_attributes is boolean
	 * If all_attributes is true, json must have all course attributes, else some attributes may be skipped
	 */
	/**
	 * @throws exception
	 */
	private function is_course($json, $all_attributes = true): bool {
		$name_pattern = '/^[a-zA-Zа-яА-Я0-9 \.,-]+$/u';
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
	 * Create new course
	 */
	/**
	 * @throws Exception
	 */
	public function post_course(): array {
		$request = (array) json_decode(file_get_contents('php://input'));
		if (empty($request) || !self::is_course($request, true)) {
			throw new Exception(400);
		}
		$mysqli = Session::get_sql_connection();
		$stmt = $mysqli->prepare('INSERT INTO course (name) VALUES (?)');
		$stmt->bind_param('s',  $request['name']);
		if (!$stmt->execute()) {
			throw new Exception(500);
		}
		return array(
			'success' => 'true'
		);
	}

	/*
	 * Update course (or some course attributes) by courseid
	 */
	/**
	 * @throws Exception
	 */
	public function put_course_index($index): array {
		$request = (array) json_decode(file_get_contents('php://input'));
		if (empty($request) || !self::is_course($request, false)) {
			throw new Exception(400);
		}
		if (!is_numeric($index)) {
			throw new Exception(400);
		}
		$attributes = array();
		foreach ($request as $key => $value) {
			$attributes[] = '`' . $key . '` = "' . $value . '"';
		}
		$query = 'UPDATE course SET ' . implode(', ', $attributes) . ' WHERE courseid = ' . $index;
		$mysqli = Session::get_sql_connection();
		if (!$mysqli->query($query)) {
			throw new Exception(500);
		}
		return array(
			'success' => 'true'
		);
	}

	/*
	 * Delete course by courseid
	 */
	/**
	 * @throws Exception
	 */
	public function delete_course_index($index): array {
		$mysqli = Session::get_sql_connection();
		$stmt = $mysqli->prepare('DELETE FROM course WHERE courseid = ?');
		$stmt->bind_param('i', $index);
		if (!$stmt->execute()) {
			throw new Exception(500);
		}
		return array(
			'success' => 'true'
		);
	}

	/*
	 * Find tests on course by courseid
	 */
	/**
	 * @throws Exception
	 */
	public function get_course_index_test($index): array {
		$mysqli = Session::get_sql_connection();
		$stmt = $mysqli->prepare('SELECT * FROM test WHERE courseid = ?');
		$stmt->bind_param('i', $index);
		if (!$stmt->execute()) {
			throw new Exception(500);
		}
		$users = $stmt->get_result();
		$response = array();
		while ($user = $users->fetch_assoc()) {
			$response[] = $user;
		}
		return array(
			'success' => 'true',
			'data' => $response
		);
	}

}