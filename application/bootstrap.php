<?php
require_once 'core/model.php';
require_once 'core/view.php';
require_once 'core/controller.php';
require_once 'core/route.php';
require_once 'addon/session.php';
// запускаем маршрутизатор
try {
	Route::start();
} catch (Exception $e) {
	header('Location: http://' . $_SERVER['HTTP_HOST'] . '/error/code/' . $e->getMessage());
}