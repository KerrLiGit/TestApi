<?php

class Controller_Question extends Controller {

	function __construct() {
		$this->model = new Model_Question();
		$this->view = new View();
	}

	/**
	 * @throws Exception
	 */
	function action_index($index = null) {
		Api::auth();
		if ($index == null) {
			$data = match (strtolower($_SERVER["REQUEST_METHOD"])) {
				'get' => $this->model->get_question(),
				'post' => $this->model->post_question(),
				default => throw new Exception(405)
			};
		}
		else {
			$data = match (strtolower($_SERVER["REQUEST_METHOD"])) {
				'get' => $this->model->get_question_index($index),
				'put' => $this->model->put_question_index($index),
				'delete' => $this->model->delete_question_index($index),
				default => throw new Exception(405)
			};
		}
		$this->view->generate('view_api.php', 'view_api_template.php', $data);
	}

}