<?php
if (isset($json) && array_key_exists('data', $json) && $json['data'] != null) {
	echo 'Привет, ' . $json['data']['name'] . '!';
}
else {
	echo 'Пользователь не существует.';
}