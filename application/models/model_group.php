<?php

class Model_Group extends Model {

	/*
	 * Find all groups
	 */
	/**
	 * @throws Exception
	 */
	public function get_group(): array {
		$mysqli = Session::get_sql_connection();
		$groups = $mysqli->query('SELECT * FROM `group`');
		$response = array();
		while ($group = $groups->fetch_assoc()) {
			$response[] = $group;
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
		$mysqli = Session::get_sql_connection();
		$stmt = $mysqli->prepare('SELECT * FROM `group` WHERE groupid = ?');
		$stmt->bind_param('i', $index);
		if (!$stmt->execute()) {
			throw new Exception(500);
		}
		$group = $stmt->get_result();
		$response = $group->fetch_assoc();
		return array(
			'success' => 'true',
			'data' => $response
		);
	}

	/*
	 * Checking if json is group
	 * Field all_attributes is boolean
	 * If all_attributes is true, json must have all group attributes, else some attributes may be skipped
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
	 * Update group (or some group attributes) by groupid
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
		$mysqli = Session::get_sql_connection();
		if (!$mysqli->query($query)) {
			throw new Exception(500);
		}
		return array(
			'success' => 'true'
		);
	}

	/*
	 * Delete group by groupid
	 */
	/**
	 * @throws Exception
	 */
	public function delete_group_index($index): array {
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

	/*
	 * Find students on group by groupid
	 */
	/**
	 * @throws Exception
	 */
	public function get_group_index_student($index): array {
		$mysqli = Session::get_sql_connection();
		$stmt = $mysqli->prepare('SELECT * FROM `user` WHERE groupid = ?');
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