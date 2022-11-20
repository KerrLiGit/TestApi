<?php

class Controller_User extends Controller {

	function __construct() {
		$this->model = new Model_User();
		$this->view = new View();
	}

	/**
	 * @throws Exception
	 */
	function action_index($index = null) {
		Api::auth();
		if ($index == null) {
			$data = match (strtolower($_SERVER["REQUEST_METHOD"])) {
				'get' => $this->model->get_user(),
				'post' => $this->model->post_user(),
				default => throw new Exception(405)
			};
		}
		else {
			$data = match (strtolower($_SERVER["REQUEST_METHOD"])) {
				'get' => $this->model->get_user_index($index),
				'put' => $this->model->put_user_index($index),
				'delete' => $this->model->delete_user_index($index),
				default => throw new Exception(405)
			};
		}
		$this->view->generate('view_api.php', 'view_api_template.php', $data);
	}

	/**
	 * @throws exception
	 */
	function action_login($index = null) {
		Api::auth();
		if ($index != null) {
			$data = match (strtolower($_SERVER["REQUEST_METHOD"])) {
				'post' => $this->model->login($index),
				default => throw new Exception(405)
			};
		}
		else return;
		$this->view->generate('view_api.php', 'view_api_template.php', $data);
	}

	/**
	 * @throws exception
	 */
	function action_signin($index = null) {
		Api::auth();
		if ($index == null) {
			$data = match (strtolower($_SERVER["REQUEST_METHOD"])) {
				'post' => $this->model->signin(),
				default => throw new Exception(405)
			};
		}
		else return;
		$this->view->generate('view_api.php', 'view_api_template.php', $data);
	}

	/**
	 * @throws exception
	 */
	function action_signout($index = null) {
		Api::auth();
		if ($index == null) {
			$data = match (strtolower($_SERVER["REQUEST_METHOD"])) {
				'post' => $this->model->signout(),
				default => throw new Exception(405)
			};
		}
		else return;
		$this->view->generate('view_api.php', 'view_api_template.php', $data);
	}

}