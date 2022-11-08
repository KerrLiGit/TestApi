<?php

class Controller_Answer extends Controller {

	function __construct() {
		$this->model = new Model_Answer();
		$this->view = new View();
	}

	/**
	 * @throws Exception
	 */
	function action_index($index = null) {
		Api::auth();
		if ($index == null) {
			$data = match (strtolower($_SERVER["REQUEST_METHOD"])) {
				'get' => $this->model->get_answer(),
				'post' => $this->model->post_answer(),
				default => throw new Exception(405)
			};
		}
		else {
			$data = match (strtolower($_SERVER["REQUEST_METHOD"])) {
				'get' => $this->model->get_answer_index($index),
				'put' => $this->model->put_answer_index($index),
				'delete' => $this->model->delete_answer_index($index),
				default => throw new Exception(405)
			};
		}
		$this->view->generate('view_api.php', 'view_api_template.php', $data);
	}

}