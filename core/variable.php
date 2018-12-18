<?php
//główne zmienne
$this->reversion = ''; //powroty karetki do głównego folderu
//zmienne dla modułów
$this->module = [];
$this->module_list = [];
$this->module_config = [];
//zmienne dla modeli
$this->model = [];
$this->model_list = [];
//dla szablonu
$this->template_dir = 'template/'; //ścieżka do folderu z plikami szablonu
$this->template_extension = '.inc.tpl'; //rozszerzenie pliku szablonu
$this->array_template_list = []; //zmienna zawierająca liste danych
$this->array_template = []; //zmienna zawierająca dane
//zmienne dla logów
$this->log_dir = 'log/'; //ścieżka do folderu z logami
$this->log_file = 'log_'.date('Y_m').'.log'; //plik logów
$this->log_save = true; //tworzenie logów
$this->log_hide_type = ['message', 'info']; //typy logów które mają być niedodawanie do logów
//zmienne dla logów błędów PHP
$this->error = true; //wyświetlenie błędów PHP
$this->php_error = true; //dodawanie do logów błędów PHP
$this->php_error_file = 'log_php_'.date('Y_m').'.log'; //plik logów
$this->php_error_dir = 'log/'; //folder błędów PHP
//rozszerzenia
$this->db = null; //baza danych
$this->moduleManager = null; //menadżer modułów
$this->test = null; //menadżer testowania
//API
$this->API_secure = false; //wlacza połączenie z API za pomocą SSL (https), może spowolnić kod około 10 razy
$this->API = ($this->API_secure==true?'https':'http').'://www.fourframework.hmcloud.pl'; //url do API
//biblioteki
$this->library = null;