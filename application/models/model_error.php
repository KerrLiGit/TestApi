<?php

class Model_Error extends Model {

	private function get_status($code) {
		$text = '';
		if ($code !== NULL) {
			switch ($code) {
				case 200:
					$text = 'OK';
					break;
				case 204:
					$text = 'No Content';
					break;
				case 400:
					$text = 'Bad Request';
					break;
				case 401:
					$text = 'Unauthorized';
					break;
				case 403:
					$text = 'Forbidden';
					break;
				case 404:
					$text = 'Not Found';
					break;
				case 405:
					$text = 'Method Not Allowed';
					break;
				case 500:
					$text = 'Internal Server Error';
					break;
				default:
					exit('Unknown http status code "' . htmlentities($code) . '"');
			}
		}
		return $text;
	}

	public function get_error($code) {
		$json = array(
			'success' => 'false',
			'error' => array(
				'code' => $code,
				'message' => self::get_status($code)
			)
		);
		return $json;
	}

}