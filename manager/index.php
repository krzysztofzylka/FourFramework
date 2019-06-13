<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
include('../core/core.php');
$core = new core();
$core->path['dir_template'] = 'manager/template/';
$db = $core->library->db;
$DBpath = $core->path['dir_db'];
$_version = '1.1';

//load module
$mod_path = $core->path['dir_module'];
$core->path['dir_module'] = 'module/';
$core->loadModule('language');
$core->path['dir_module'] = $mod_path;

//module language
$lang = $core->module['language'];
$lang_name = "";
if(optionRead('language')===null)
	$lang_name == "pl";
else{
	$lang_name = optionRead('language');
	$check = 'module/language/lang/lang-'.$lang_name.'.php';
	if(!file_exists($check))
		$lang_name = "pl";
}
$lang->loadLang($lang_name);

function optionRead($name){
	global $db;
	$query = $db->getData('manager_option', ['name='.$name], false);
	return $query['value'];
}
function optionWrite($name, $value){
	global $db;
	$update = $db->updateData('manager_option', ['name='.$name], ['value='.$value]);
	if($update == 0 or $update == false){
		$db->addData('manager_option', ['name' => $name, 'value' => $value]);
	}
}

// if(file_exists(optionRead('dbeditor_dbpath')))
	// $db->setDatabaseDir(optionRead('dbeditor_dbpath'));

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
	$menu = '';
	$incmen = include('function/data_menu.php');
	foreach($incmen as $item){
		$menu .= '<a href="'.(isset($item['url'])?$item['url']:(isset($item['type'])?'index.php?type='.$item['type'].(isset($item['page'])?'&page='.$item['page']:''):'#')).'" class="'.(isset($item['class'])?$item['class']:'').'">'.$item['name'].'</a>';
	}
	$core->templateSet('menu', $menu);
	$menu2 = "";
	ob_start();
	$path = "";
	if(!isset($_GET['type']))
		$_GET['type'] = '';
	switch($_GET['type']){
		case 'dbeditor':
			$menu2 .= '<a href="index.php?type=dbeditor&page=main">'.$lang->get('dblist').'</a>
			<h1>'.$lang->get('table').'</h1>';
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
			$menu2 .= '<a href="index.php?type=module&page=main">'.$lang->get('list').'</a>
			<a href="index.php?type=module&page=api">'.$lang->get('downloadfromserver').'</a>';
			if(!isset($_GET['page']))
				$_GET['page'] = 'main';
			$page = basename(htmlspecialchars($_GET['page']));
			$path = 'site/module/'.$page.'.php';
			break;
		case 'option':
			$menu2 .= '<a href="index.php?type=option&page=manager">'.$lang->get('manager').'</a>
			<a href="index.php?type=option&page=lang">'.$lang->get('language').'</a>';
			if(!isset($_GET['page']))
				$_GET['page'] = 'manager';
			$page = basename(htmlspecialchars($_GET['page']));
			$path = 'site/option/'.$page.'.php';
			break;
		case 'service':
			$menu2 .= '<a href="index.php?type=service&page=main">'.$lang->get('description').'</a>
			<h1>'.$lang->get('server').'</h1>
			<a href="index.php?type=service&page=server_apachemodulelist">'.$lang->get('apachemodulelist').'</a>';
			if(!isset($_GET['page']))
				$_GET['page'] = 'main';
			$page = basename(htmlspecialchars($_GET['page']));
			$path = 'site/service/'.$page.'.php';
			break;
		case 'library':
			$menu2 .= '<a href="index.php?type=library&page=list">'.$lang->get('list').'</a>
			<a href="index.php?type=library&page=usage">'.$lang->get('usage').'</a>
			<h1>'.$lang->get('librarytest').'</h1>
			<a href="index.php?type=library&page=test_class">class</a>
			<a href="index.php?type=library&page=test_crypt">crypt</a>
			<a href="index.php?type=library&page=test_generate">generate</a>
			<a href="index.php?type=library&page=test_memory">memory</a>';
			if(!isset($_GET['page']))
				$_GET['page'] = 'list';
			$page = basename(htmlspecialchars($_GET['page']));
			$path = 'site/library/'.$page.'.php';
			break;
		case 'autoconfig':
			$menu2 .= '<a href="index.php?type=autoconfig&page=main">'.$lang->get('info').'</a>
			<a href="index.php?type=autoconfig&page=core_path">'.$lang->get('pathlist').'</a>
			<a href="index.php?type=autoconfig&page=module">'.$lang->get('module').'</a>
			<h1>'.$lang->get('configurationlibrary').'</h1>
			<a href="index.php?type=autoconfig&page=lib_database">database</a>
			<a href="index.php?type=autoconfig&page=lib_crypt">crypt</a>
			<a href="index.php?type=autoconfig&page=lib_network">network</a>';
			if(!isset($_GET['page']))
				$_GET['page'] = 'main';
			$page = basename(htmlspecialchars($_GET['page']));
			$path = 'site/autoconfig/'.$page.'.php';
			break;
		case 'account':
			$menu2 .= '<a href="index.php?type=account&page=logout">'.$lang->get('logout').'</a>';
			// if(!isset($_GET['page']))
				// $_GET['page'] = 'main';
			$page = basename(htmlspecialchars($_GET['page']));
			$path = 'site/account/'.$page.'.php';
			break;
		case '':
		default:
			$menu2 .= '<a href="index.php?type=default&page=main">'.$lang->get('info').'</a>
			<a href="index.php?type=default&page=logs">'.$lang->get('logs').'</a>
			<a href="index.php?type=default&page=pathList">'.$lang->get('pathlist').'</a>
			<a href="index.php?type=default&page=updater">'.$lang->get('updater').'</a>';
			if(!isset($_GET['page']))
				$_GET['page'] = 'main';
			$page = basename(htmlspecialchars($_GET['page']));
			$path = 'site/default/'.$page.'.php';
			break;
	}
	if(is_readable($path))
		require_once(htmlspecialchars($path));
	else
		echo '<h1>'.$lang->get('error').'</h1>'.$lang->get('pagenotexists');
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