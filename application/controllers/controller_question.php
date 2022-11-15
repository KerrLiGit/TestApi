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

	/**
	 * @throws Exception
	 */
	function action_generate($index = null, $params = null) {
		$seed = mt_rand();
		if (!array_key_exists(0, $params)) {
			$seed = mt_rand();
		}
		if (array_key_exists(0, $params) && is_numeric($params[0])) {
			$seed = $params[0];
		}
		else if (array_key_exists(0, $params)) {
			throw new Exception(400);
		}
		Api::auth();
		if ($index != null) {
			$data = match (strtolower($_SERVER["REQUEST_METHOD"])) {
				'post' => $this->model->generate($index, $seed),
				default => throw new Exception(405)
			};
		}
		else throw new Exception(405);
		$this->view->generate('view_api.php', 'view_api_template.php', $data);
	}

	/**
	 * @throws Exception
	 */
	function action_check($index = null, $params = null) {
		$seed = mt_rand();
		if (!array_key_exists(0, $params)) {
			$seed = mt_rand();
		}
		if (array_key_exists(0, $params) && is_numeric($params[0])) {
			$seed = $params[0];
		}
		else if (array_key_exists(0, $params)) {
			throw new Exception(400);
		}
		Api::auth();
		if ($index != null) {
			$data = match (strtolower($_SERVER["REQUEST_METHOD"])) {
				'post' => $this->model->check($index, $seed),
				default => throw new Exception(405)
			};
		}
		else throw new Exception(405);
		$this->view->generate('view_api.php', 'view_api_template.php', $data);
	}

}