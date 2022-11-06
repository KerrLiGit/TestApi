<?php

class Controller_Userpage extends Controller {

	function __construct() {
		$this->model = new Model_User();
		$this->view = new View();
	}

	/**
	 * @throws Exception
	 */
	function action_index($index) {
		$this->model->auth();
		if ($index == null) {
			throw new Exception(400);
		}
		else {
			$data = match (strtolower($_SERVER["REQUEST_METHOD"])) {
				'get' => $this->model->get_user_index($index),
				default => throw new Exception(405),
			};
		}
		$this->view->generate('view_userpage.php', 'view_template.php', $data);
	}

}