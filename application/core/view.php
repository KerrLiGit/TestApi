<?php
class View {
	//public $view_template; // здесь можно указать общий вид по умолчанию.

	function generate($view_content, $view_template, $data = null) {
		if (is_array($data)) {
			// преобразуем элементы массива в переменные
			extract($data);
		}
		include 'application/views/' . $view_template;
	}
}