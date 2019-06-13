<?php
$array =  [
	[
		'name' => $lang->get('mainpage'),
		'url' => 'index.php',
	],
	[
		'name' => $lang->get('module'),
		'type' => 'module',
	],
	[
		'name' => $lang->get('library'),
		'type' => 'library',
	],
	[
		'name' => $lang->get('dbeditor'),
		'type' => 'dbeditor',
	],
	[
		'name' => $lang->get('configuration'),
		'type' => 'autoconfig',
	],
	[
		'name' => $lang->get('option'),
		'type' => 'option',
	],
	[
		'name' => $lang->get('logout'),
		'type' => 'account',
		'page' => 'logout',
		'class' => 'right',
	],
];

if(optionRead('show_service_menu')==1){
	array_push($array, ['name'=>$lang->get('service'), 'type'=>'service']);
}
return $array;
?>