<?php
return $this->module = new class(){ 
	public $version = '1.0';
	public function getConfig(string $name){
		core::setError();
		if(!isset(core::$module_add[$name]))
			return core::setError(1, 'module not found');
		return core::$module_add[$name]['config'];
	}
	public function moduleList(bool $loadConfig = false) : array{
		core::setError();
		$return = [];
		$scan = scandir(core::$path['module']);
		$scan = array_diff($scan, ['.', '..', '.htaccess']);
		foreach($scan as $name){
			$path = core::$path['module'].$name.'/config.php';
			if(file_exists($path)){
				$return[$name] = [
					'name' => $name,
					'path' => core::$path['module'].$name.'/',
					'loaded' => isset(core::$module_add[$name])?true:false,
					'config' => $loadConfig===true?include($path):null,
				];
			}
		}
		return $return;
	}
	public function loadAdminPanel(string $moduleName){
		core::setError();
		$list = $this->moduleList(true);
		if(!isset($list[$moduleName]))
			return core::setError(1, 'Not found module');
		$list = $this->moduleList(true);
		$module = $list[$moduleName];
		if(!isset($module['config']['adminPanel']))
			return core::setError(2, 'Module dont have admin panel');
		$file = $module['path'].$module['config']['adminPanel'];
		if(!file_exists($file))
			return core::setError(3, 'AdminPanel file not found', ['path' => $file]);
		include($file);
	}
};
?>