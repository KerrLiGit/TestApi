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
		$this->model->auth();
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
	 * @throws Exception
	 */
	function action_name($index = null) {
		$this->model->auth();
		if ($index == null) {
			$data = match (strtolower($_SERVER["REQUEST_METHOD"])) {
				'get' => $this->model->get_user_action('name'),
				default => throw new Exception(405)
			};
		}
		else {
			$data = match (strtolower($_SERVER["REQUEST_METHOD"])) {
				'get' => $this->model->get_user_index_action($index, 'name'),
				default => throw new Exception(405)
			};
		}
		$this->view->generate('view_api.php', 'view_api_template.php', $data);
	}

	/**
	 * @throws Exception
	 */
	function action_role($index = null) {
		$this->model->auth();
		if ($index == null) {
			$data = match (strtolower($_SERVER["REQUEST_METHOD"])) {
				'get' => $this->model->get_user_action('role'),
				default => throw new Exception(405)
			};
		}
		else {
			$data = match (strtolower($_SERVER["REQUEST_METHOD"])) {
				'get' => $this->model->get_user_index_action($index, 'role'),
				default => throw new Exception(405)
			};
		}
		$this->view->generate('view_api.php', 'view_api_template.php', $data);
	}

	/**
	 * @throws exception
	 */
	function action_groupid($index = null) {
		$this->model->auth();
		if ($index == null) {
			$data = match (strtolower($_SERVER["REQUEST_METHOD"])) {
				'get' => $this->model->get_user_action('groupid'),
				default => throw new Exception(405),
			};
		}
		else {
			$data = match (strtolower($_SERVER["REQUEST_METHOD"])) {
				'get' => $this->model->get_user_index_action($index, 'groupid'),
				default => throw new Exception(405),
			};
		}
		$this->view->generate('view_api.php', 'view_api_template.php', $data);
	}

}