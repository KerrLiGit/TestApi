<?php

class Controller_Error extends Controller {

	function __construct() {
		$this->model = new Model_Error();
		$this->view = new View();
	}

	function action_index($index) {
		$json = $this->model->get_error($index);
		$this->view->generate('view_error.php', 'view_api_template.php', $json);
	}

}