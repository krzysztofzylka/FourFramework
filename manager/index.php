<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
include('../core/core.php');
$core = new core();
$core->path['dir_template'] = 'manager/template/';
$db = $core->library->db;
$DBpath = $core->path['dir_db'];
$_version = '1.0';

function optionRead($name){
	global $db;
	$query = $db->getData('manager_option', ['name='.$name], false);
	return $query['value'];
}
function optionWrite($name, $value){
	global $db;
	$db->updateData('manager_option', ['name='.$name], ['value='.$value]);
}
?>
<!doctype html>
<html lang="pl">
<head>
	<meta charset="utf-8">
	<title>FourFramework Manager</title>
	<link rel="stylesheet" href="template/style.css">
</head>
<body>
<?php
if(isset($_SESSION['userID'])){
	$core->templateSet('menu', '<a href="index.php">Strona główna</a>
	<a href="index.php?type=module">Moduły</a>
	<a href="index.php?type=library">Biblioteki</a>
	<a href="index.php?type=dbeditor">DBEditor</a>
	<a href="index.php?type=autoconfig">Konfigurator</a>
	<a href="index.php?type=option">Opcje</a>');
	$menu2 = "";
	ob_start();
	$path = "";
	if(!isset($_GET['type']))
		$_GET['type'] = '';
	switch($_GET['type']){
		case 'dbeditor':
			$menu2 .= '<a href="index.php?type=dbeditor&page=main">Lista baz danych</a>
			<h1>Tabele</h1>';
			$tableList = $db->tableList();
			$hideList = explode('|', optionRead('dbeditor_hideTable'));
			$tableList = array_diff($tableList, $hideList);
			foreach($tableList as $name){
				$menu2 .= '<a href="index.php?type=dbeditor&page=table&table='.$name.'">'.$name.'</a>';
			}
			if(!isset($_GET['page']))
				$_GET['page'] = 'main';
			$page = basename(htmlspecialchars($_GET['page']));
			$path = 'site/dbeditor/'.$page.'.php';
			break;
		case 'module':
			$menu2 .= '<a href="index.php?type=module&page=main">Lista</a>
			<a href="index.php?type=module&page=api">Pobieranie z serwera</a>';
			if(!isset($_GET['page']))
				$_GET['page'] = 'main';
			$page = basename(htmlspecialchars($_GET['page']));
			$path = 'site/module/'.$page.'.php';
			break;
		case 'option':
			$menu2 .= '<a href="index.php?type=option&page=manager">Menadżer</a>';
			if(!isset($_GET['page']))
				$_GET['page'] = 'manager';
			$page = basename(htmlspecialchars($_GET['page']));
			$path = 'site/option/'.$page.'.php';
			break;
		case 'library':
			$menu2 .= '<a href="index.php?type=library&page=list">Lista</a>
			<a href="index.php?type=library&page=usage">Wykorzystanie kodu</a>';
			if(!isset($_GET['page']))
				$_GET['page'] = 'list';
			$page = basename(htmlspecialchars($_GET['page']));
			$path = 'site/library/'.$page.'.php';
			break;
		case 'autoconfig':
			$menu2 .= '<a href="index.php?type=autoconfig&page=main">Informacje</a>
			<a href="index.php?type=autoconfig&page=core_path">Ścieżki do plików oraz folderów</a>
			<a href="index.php?type=autoconfig&page=module">Moduły</a>
			<h1>Konfiguracja bibliotek</h1>
			<a href="index.php?type=autoconfig&page=lib_database">database</a>
			<a href="index.php?type=autoconfig&page=lib_crypt">crypt</a>
			<a href="index.php?type=autoconfig&page=lib_network">network</a>';
			if(!isset($_GET['page']))
				$_GET['page'] = 'main';
			$page = basename(htmlspecialchars($_GET['page']));
			$path = 'site/autoconfig/'.$page.'.php';
			break;
		case '':
		default:
			$menu2 .= '<a href="index.php?type=default&page=main">Informacje</a>
			<a href="index.php?type=default&page=logs">Logi</a>
			<a href="index.php?type=default&page=pathList">Lista ścieżek dla rdzenia</a>
			<a href="index.php?type=default&page=updater">Aktualizator</a>';
			if(!isset($_GET['page']))
				$_GET['page'] = 'main';
			$page = basename(htmlspecialchars($_GET['page']));
			$path = 'site/default/'.$page.'.php';
			break;
	}
	if(is_readable($path))
		require_once(htmlspecialchars($path));
	else
		echo '<h1>Błąd</h1>Wybrana strona nie istnieje';
	$data = ob_get_contents();
	ob_end_clean();
	$data = mb_convert_encoding($data, 'HTML-ENTITIES', "UTF-8");
	$core->templateSet('menu2', $menu2);
	$core->templateSet('data', $data);
	echo $core->Template('template');
}else{
	include('site/login.php');
}
?>
</body>
</html>