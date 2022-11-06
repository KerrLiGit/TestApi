<?php

class Xmysqli extends mysqli {
	function __construct($host, $mylogin, $mypass, $dbname, $login) {
		@parent::__construct($host, $mylogin, $mypass, $dbname, 3306, false);

		if (mysqli_connect_errno()) // check if a connection established
			throw new exception(mysqli_connect_error(), mysqli_connect_errno());

		/*
		if (!is_null($login)) {
			$session = $this->thread_id;
			$stmt = $this->prepare('DELETE FROM `session` WHERE `session` = ?;');
			$stmt->bind_param("i", $session);
			$stmt->execute();
			$stmt = $this->prepare('INSERT INTO `session` (`session`, login) VALUES (?, ?);');
			$stmt->bind_param("is", $session, $login);
			$stmt->execute();
		}
		*/
	}

	function __destruct() {
		/*
		$idconn = $this->thread_id;
		$stmt = $this->prepare('DELETE FROM `session` WHERE `session` = ?;');
		$stmt->bind_param("i", $idconn);
		$stmt->execute();
		*/
	}
}