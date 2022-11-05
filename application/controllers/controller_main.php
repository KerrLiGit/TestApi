<?php

class Controller_Main extends Controller {

	function __construct() {
		$this->model = new Model_Main();
		$this->view = new View();
	}

	function action_index() {
		$data = $this->model->get_data();
		$this->view->generate('view_main.php', 'view_template.php', $data);
	}

}