<?php

class Route {

	/*
	 * Метод для тестового вывода в файл !log.php
	 */
	static function addlog($str) {
		$tempdir = 'C:\web\www\TestApi.local'; // если есть такой каталог
		if (!file_exists($tempdir)) // иначе
			$tempdir = sys_get_temp_dir(); // системный - c:\windows\temp
		$logfile = $tempdir .'\!log.txt';
		$fd = fopen($logfile, 'a+');
		if ($fd) {
			date_default_timezone_set("Europe/Moscow");
			fwrite($fd, date("Y-m-d H:i:s") . " " . $str . "\r\n");
			fclose($fd);
		}
	}

	/*
	 * Основной метод запуска приложения
	 * Запрос к срверу имеет вид /controller/index/action
	 */
	/**
	 * @throws Exception
	 */
	static function start() {
		// контроллер и действие по умолчанию
		$controller_name = 'Main';
		$index = null;
		$action_name = 'index';
		$i = 1;

		$routes = explode('/', $_SERVER['REQUEST_URI']);

		// получаем имя контроллера
		if (array_key_exists($i, $routes) && !empty($routes[$i])) {
			$controller_name = $routes[$i];
			$i++;
		}

		// получаем индекс
		if (array_key_exists($i, $routes) && !empty($routes[$i])) {
			$index = $routes[$i];
			$i++;
		}
		if (!empty($index) && !is_numeric($index)) {
			$index = null;
			$i--;
		}

		// получаем имя экшена
		if (array_key_exists($i, $routes) && !empty($routes[$i])) {
			$action_name = $routes[$i];
			$i++;
		}

		// получаем дополнительные параметры
		$params = array();
		while (array_key_exists($i, $routes) && !empty($routes[$i])) {
			$params[] = $routes[$i];
			$i++;
		}

		// добавляем префиксы
		$model_name = 'Model_' . $controller_name;
		$controller_name = 'Controller_' . $controller_name;
		$action_name = 'action_' . $action_name;

		// подцепляем файл с классом модели (файла модели может и не быть, тогда цепляется Model_Main)
		$model_file = strtolower($model_name) . '.php';
		$model_path = "application/models/" . $model_file;
		if (file_exists($model_path)) {
			include "application/models/" . $model_file;
		}
		else {
			include "application/models/" . 'model_main.php';
		}

		// подцепляем файл с классом контроллера
		$controller_file = strtolower($controller_name) . '.php';
		$controller_path = "application/controllers/" . $controller_file;
		if (file_exists($controller_path)) {
			include "application/controllers/" . $controller_file;
		}
		else {
			throw new Exception(404);
		}

		// создаем контроллер
		$controller = new $controller_name;
		$action = $action_name;

		if (method_exists($controller, $action)) {
			// вызываем действие контроллера
			$controller->$action($index, $params);
		}
		else {
			throw new Exception(404);
		}
	}

}