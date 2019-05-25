<?php
if(isset($_GET['name'])){
	$name = basename(htmlspecialchars($_GET['name']));
	$load = $core->loadModule($name);
	if($core->lastError['number'] > -1){
		echo '<h1>Błąd</h1>Nie udało się wczytać modułu<br /><br /><b>Błąd:</b><br />'.$core->lastError['name'];
	}else{
		$path = $core->path['dir_module'].$name.'/';
		$config = $core->module_config[$name];
		$path_ap = $path.$config['adminpanel'];
		$module = $core->module[$name];
		if(isset($path_ap)){
			echo '<h1>'.$config['name'].' - AdminPanel</h1>';
			include($path_ap);
		}else
			echo '<h1>Błąd</h1>
			Moduł nie posiada panelu administracyjnego';
	}
}else
	echo '<h1>Błąd</h1>
	Nie wybrano modułu';
?>