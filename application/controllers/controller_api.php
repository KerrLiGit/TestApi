<?php

class Controller_Api extends Controller {

	function __construct() {
		$this->view = new View();
	}

	function action_index($index = null) {
		$this->view->generate('view_openapi.php', 'view_api_template.php');
	}

}