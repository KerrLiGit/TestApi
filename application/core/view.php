<?php
class View {
	//public $view_template; // здесь можно указать общий вид по умолчанию.

	function generate($view_content, $view_template, $json = null) {
		/*
		if (is_array($json)) {
			// преобразуем элементы массива в переменные
			extract($json);
		}
		*/
		include 'application/views/' . $view_template;
	}
}