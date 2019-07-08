<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
include('../core/core.php');
$core = new core();
$core->path['dir_template'] = 'manager/template/';
$db = $core->library->db;
$DBpath = $core->path['dir_db'];
$_version = '1.2';

//inc
require('function/loadModule.php');
require('function/option.php');
require('function/language.php');
?>
<!doctype html>
<html lang="pl">
<head>
	<meta charset="utf-8">
	<title><?php echo $lang->get('title'); ?></title>
	<link rel="stylesheet" href="template/style.css">
</head>
<body>
<?php
if(isset($_SESSION['userID'])){
	include('function/top_menu.php');
	$inc = include('function/data_menu_left.php');
	$menu = "";
	$path = "";
	if(!isset($_GET['type']))
		$_GET['type'] = '';
	switch($_GET['type']){
		case 'dbeditor':
			$menu .= $inc['dbeditor'];
			$tableList = $db->tableList();
			$hideList = explode('|', optionRead('dbeditor_hideTable'));
			$tableList = array_diff($tableList, $hideList);
			foreach($tableList as $name)
				$menu .= '<a href="index.php?type=dbeditor&page=table&table='.$name.'">'.$name.'</a>';
			if(!isset($_GET['page']))
				$_GET['page'] = 'main';
			$page = basename(htmlspecialchars($_GET['page']));
			$path = 'site/dbeditor/'.$page.'.php';
			break;
		case 'module':
			$menu .= $inc['module'];
			if(!isset($_GET['page']))
				$_GET['page'] = 'main';
			$page = basename(htmlspecialchars($_GET['page']));
			$path = 'site/module/'.$page.'.php';
			break;
		case 'option':
			$menu .= $inc['option'];
			if(!isset($_GET['page']))
				$_GET['page'] = 'manager';
			$page = basename(htmlspecialchars($_GET['page']));
			$path = 'site/option/'.$page.'.php';
			break;
		case 'service':
			$menu .= $inc['service'];
			if(!isset($_GET['page']))
				$_GET['page'] = 'main';
			$page = basename(htmlspecialchars($_GET['page']));
			$path = 'site/service/'.$page.'.php';
			break;
		case 'library':
			$menu .= $inc['library'];
			if(!isset($_GET['page']))
				$_GET['page'] = 'list';
			$page = basename(htmlspecialchars($_GET['page']));
			$path = 'site/library/'.$page.'.php';
			break;
		case 'autoconfig':
			$menu .= $inc['autoconfig'];
			if(!isset($_GET['page']))
				$_GET['page'] = 'main';
			$page = basename(htmlspecialchars($_GET['page']));
			$path = 'site/autoconfig/'.$page.'.php';
			break;
		case 'account':
			$menu .= $inc['account'];
			$page = basename(htmlspecialchars($_GET['page']));
			$path = 'site/account/'.$page.'.php';
			break;
		case '':
		default:
			$menu .= $inc['default'];
			if(!isset($_GET['page']))
				$_GET['page'] = 'main';
			$page = basename(htmlspecialchars($_GET['page']));
			$path = 'site/default/'.$page.'.php';
			break;
	}
	ob_start();
	if(is_readable($path))
		require_once(htmlspecialchars($path));
	else
		echo '<h1>'.$lang->get('error').'</h1>'.$lang->get('pagenotexists');
	$data = ob_get_contents();
	ob_end_clean();
	$data = mb_convert_encoding($data, 'HTML-ENTITIES', "UTF-8");
	$core->templateSet('menu2', $menu);
	$core->templateSet('data', $data);
	echo $core->Template('template');
}else{
	include('site/login.php');
}
?>
</body>
</html>