<?php

class Controller_Openapi extends Controller {

	function __construct() {
		$this->view = new View();
	}

	function action_index() {
		$this->view->generate('view_openapi.php', 'view_template.php');
	}

}