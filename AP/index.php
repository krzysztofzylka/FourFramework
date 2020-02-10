<?php
ob_start();
session_start();
include('../core/core.php'); //wczytywanie rdzenia
core::init(); //inicjowanie rdzenia
foreach(['controller', 'view', 'model'] as $name) //tworzenie ścieżek do folderów controller, view oraz model
	core::$path[$name] = __DIR__.'/'.$name.'/';
core::$debug['showCoreError'] = false; //ukrycie błędów rdzenia
core::$library->global->write('wersja', '1.1'); //wersja adminpanelu

//łączenie z bazą danych
$config = ['type' => 'sqlite', 'path' => 'database/adminpanel.sqlite3'];
core::$library->database->connect($config);

//ładowanie biblioteki smarty
$old_module = core::$path['module']; //zapis ścieżki modułów
$old_ff = core::$info['frameworkPath']; //zapis ścieżki rdzenia
core::$info['frameworkPath'] = __dir__.'/'; //zmiana ścieżki rdzenia
core::$path['module'] = __DIR__.'/module/'; //zmiana ścieżki na moduły od AP
core::loadModule('smarty')->setCaching(false); //ładowanie modułu smarty
core::loadModule('account'); //ładowanie modułu użytkowników
core::$info['frameworkPath'] = $old_ff; //przywrócenie ścieżki rdzenia
core::$path['module'] = $old_module; //przywrócenie ścieżki modułów

//ładowanie zmiennych do smarty
core::$module['smarty']->smarty->assign('title', 'FourFramework - Admin Panel'); //tytuł strony

//ładowanie głównego kontrollera
core::loadController('main');

ob_end_flush();
?>