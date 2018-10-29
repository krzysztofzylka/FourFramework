<?php
//tablica z konfiguracją
return Array(
	//zmienne dla szablonów
	'array_template' => Array(),
	'array_template_list' => Array(),
	//zmienne dla logów
	'log_dir' => 'log/',
	'log_string' => "{year}-{month}-{day} {hour}:{min}:{sec} [{type}] {string}\r\n",
	'log_file' => 'logs_'.date('Y').'_'.date('m').'.log',
	//zmienna do wyświetlania błędów
	'error' => true,
);