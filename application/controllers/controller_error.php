<?php

class Controller_Error extends Controller {

	function __construct() {
		$this->model = new Model_Error();
		$this->view = new View();
	}

	function action_code($params) {
		$code = $params[0];
		$data = $this->model->get_error($code);
		$this->view->generate('view_error.php', 'view_template.php', $data);
	}

}