<?php
return [
	'dbeditor' => 
		'<a href="index.php?type=dbeditor&page=main">'.$lang->get('dblist').'</a>
		<h1>'.$lang->get('table').'</h1>',
		
	'module' => 
		'<a href="index.php?type=module&page=main">'.$lang->get('list').'</a>
		<a href="index.php?type=module&page=api">'.$lang->get('downloadfromserver').'</a>',
		
	'option' => '<a href="index.php?type=option&page=manager">'.$lang->get('manager').'</a>
		<a href="index.php?type=option&page=lang">'.$lang->get('language').'</a>',
		
	'service' => '<a href="index.php?type=service&page=main">'.$lang->get('description').'</a>
		<h1>'.$lang->get('framework').'</h1>
		<a href="index.php?type=service&page=core_checkfile">'.$lang->get('checkframeworkfile').'</a>
		<h1>'.$lang->get('dblibrary').'</h1>
		<a href="index.php?type=service&page=db_recovery_password">'.$lang->get('recoverypassword').'</a>
		<h1>'.$lang->get('server').'</h1>
		<a href="index.php?type=service&page=server_apachemodulelist">'.$lang->get('apachemodulelist').'</a>',
		
	'library' => '<a href="index.php?type=library&page=list">'.$lang->get('list').'</a>
		<a href="index.php?type=library&page=usage">'.$lang->get('usage').'</a>
		<h1>'.$lang->get('librarytest').'</h1>
		<a href="index.php?type=library&page=test_class">class</a>
		<a href="index.php?type=library&page=test_crypt">crypt</a>
		<a href="index.php?type=library&page=test_generate">generate</a>
		<a href="index.php?type=library&page=test_memory">memory</a>',
	
	'autoconfig' => '<a href="index.php?type=autoconfig&page=main">'.$lang->get('info').'</a>
		<a href="index.php?type=autoconfig&page=core_path">'.$lang->get('pathlist').'</a>
		<a href="index.php?type=autoconfig&page=module">'.$lang->get('module').'</a>
		<h1>'.$lang->get('configurationlibrary').'</h1>
		<a href="index.php?type=autoconfig&page=lib_database">database</a>
		<a href="index.php?type=autoconfig&page=lib_crypt">crypt</a>
		<a href="index.php?type=autoconfig&page=lib_network">network</a>',
		
	'account' => '<a href="index.php?type=account&page=logout">'.$lang->get('logout').'</a>',
	
	'default' => '<a href="index.php?type=default&page=main">'.$lang->get('info').'</a>
		<a href="index.php?type=default&page=logs">'.$lang->get('logs').'</a>
		<a href="index.php?type=default&page=pathList">'.$lang->get('pathlist').'</a>
		<a href="index.php?type=default&page=updater">'.$lang->get('updater').'</a>',
];
?>