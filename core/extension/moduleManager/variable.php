<?php
//zmienne dla debugowania
$this->debug = [
	'style' => true,
];
//zmienne dla menadżera modułów
$this->manager = [
	'view' => 0,
];
//nazwa linka dla danych GET
$this->urlGetData = 'coreModuleManager';
//zmienne dla API
$this->vapi_use = false;
$this->vapi_url = 'https://fourframework.hmcloud.pl/';
$this->vapi_type = 0;
//offline data
$this->vapi_offline = false; //zapisane typy w trybie offline / vapi_use musi być wyłączone