<?php

class Model_Main extends Model {

	public function get_data() {
		$json = array(
			'message' => "See specification in /openapi"
		);
		$data = array(
			'json' => $json
		);
		return $data;
	}

}