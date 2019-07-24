<?php
if(isset($_GET['name'])){
	$name = basename(htmlspecialchars($_GET['name']));
	$load = $core->loadModule($name);
	if($core->lastError['number'] > -1){
		echo '<h1>'.$lang->get('error').'</h1>'.$lang->get('errorloadingmodule').'<br /><br /><b>'.$lang->get('error').':</b><br />'.$core->lastError['name'];
	}else{
		$path = $core->path['dir_module'].$name.'/';
		$config = $core->module_config[$name];
		$path_ap = $path.$config['adminpanel'];
		$module = $core->module[$name];
		if(isset($path_ap)){
			echo '<h1>'.$config['name'].' - '.$lang->get('adminpanel').'</h1>';
			include($path_ap);
		}else
			echo '<h1>'.$lang->get('error').'</h1>
			'.$lang->get('moduledonthaveap');
	}
}else
	echo '<h1>'.$lang->get('error').'</h1>
	'.$lang->get('noselectmodule').'';
?>