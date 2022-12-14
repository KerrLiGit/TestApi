<?php

class Controller_Group extends Controller {

	function __construct() {
		$this->model = new Model_Group();
		$this->view = new View();
	}

	/**
	 * @throws Exception
	 */
	function action_index($index = null) {
		Api::auth();
		if ($index == null) {
			$data = match (strtolower($_SERVER["REQUEST_METHOD"])) {
				'get' => $this->model->get_group(),
				'post' => $this->model->post_group(),
				default => throw new Exception(405)
			};
		}
		else {
			$data = match (strtolower($_SERVER["REQUEST_METHOD"])) {
				'get' => $this->model->get_group_index($index),
				'put' => $this->model->put_group_index($index),
				'delete' => $this->model->delete_group_index($index),
				default => throw new Exception(405)
			};
		}
		$this->view->generate('view_api.php', 'view_api_template.php', $data);
	}

	/**
	 * @throws Exception
	 */
	function action_student($index = null) {
		Api::auth();
		if ($index == null) {
			throw new Exception(405);
		}
		$data = match (strtolower($_SERVER["REQUEST_METHOD"])) {
			'get' => $this->model->get_group_index_student($index),
			default => throw new Exception(405)
		};
		$this->view->generate('view_api.php', 'view_api_template.php', $data);
	}

}