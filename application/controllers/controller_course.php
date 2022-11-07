<?php

class Controller_Course extends Controller {

	function __construct() {
		$this->model = new Model_Course();
		$this->view = new View();
	}

	/**
	 * @throws Exception
	 */
	function action_index($index = null) {
		Api::auth();
		if ($index == null) {
			$data = match (strtolower($_SERVER["REQUEST_METHOD"])) {
				'get' => $this->model->get_course(),
				'post' => $this->model->post_course(),
				default => throw new Exception(405)
			};
		}
		else {
			$data = match (strtolower($_SERVER["REQUEST_METHOD"])) {
				'get' => $this->model->get_course_index($index),
				'put' => $this->model->put_course_index($index),
				'delete' => $this->model->delete_course_index($index),
				default => throw new Exception(405)
			};
		}
		$this->view->generate('view_api.php', 'view_api_template.php', $data);
	}

	/**
	 * @throws Exception
	 */
	function action_test($index = null) {
		Api::auth();
		if ($index == null) {
			throw new Exception(405);
		}
		$data = match (strtolower($_SERVER["REQUEST_METHOD"])) {
			'get' => $this->model->get_course_index_test($index),
			default => throw new Exception(405)
		};
		$this->view->generate('view_api.php', 'view_api_template.php', $data);
	}

}