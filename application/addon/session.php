<?php

require_once "xmysqli.php";

class Session {

	public static function safe_session_start() {
		if(!isset($_SESSION))
			session_start();
	}

	/**
	 * @throws exception
	 */
	public static function get_sql_connection() {
		self::safe_session_start();
		$login = NULL;
		if (array_key_exists('user', $_SESSION))
			$login = $_SESSION['user']['login'];

		// исп. постоянные соединения
		//$mysqli = new Xmysqli("p:localhost", "u133692_root", "root", "u133692_teacherbase", $login);
		//$mysqli = new Xmysqli("p:localhost", "root", "", "teacherbase", $login);
		$mysqli = new Xmysqli("p:localhost", "root", "", "testbase", $login);
		$mysqli->query("SET GLOBAL sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''));");
		return $mysqli;
	}

	public static function delete_session() {
		self::safe_session_start();
		if (array_key_exists('user', $_SESSION)) {
			unset($_SESSION['user']);
		}
	}

	public static function request_uri() {
		return $_SERVER['REQUEST_URI'];
	}

}