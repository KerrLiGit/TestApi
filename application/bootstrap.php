<?php
require_once 'core/model.php';
require_once 'core/view.php';
require_once 'core/controller.php';
require_once 'core/route.php';
require_once 'addon/session.php';
require_once 'addon/api.php';
// запускаем маршрутизатор
try {
	Route::start();
} catch (Exception $e) {
	//header('Location: ' . $_SERVER['HTTP_HOST'] . '/error/' . $e->getMessage());
	require_once 'controllers/controller_error.php';
	require_once 'models/model_error.php';
	$controller = new Controller_Error();
	$controller->action_index($e->getMessage());
}