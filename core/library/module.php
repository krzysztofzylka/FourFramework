<?php
return $this->module = new class(){ 
	public $version = '1.3';
	public function getConfig(string $name, bool $loadFile = false){
		core::setError();
        if (!isset(core::$module_add[$name])) {
			if($loadFile == true)
				if(file_exists(core::$path['module'].$name.'/config.php'))
					return include(core::$path['module'].$name.'/config.php');
				else return core::setError(2, 'module not found');
			return core::setError(1, 'module not found');
        }
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
		$module = $list[$moduleName];
		if(!isset($module['config']['adminPanel']))
			return core::setError(2, 'Module dont have admin panel');
		$file = is_array($module['config']['adminPanel'])?$module['path'].$module['config']['adminPanel']['path']:$module['path'].$module['config']['adminPanel'];
		if(!file_exists($file))
			return core::setError(3, 'AdminPanel file not found', ['path' => $file]);
		include($file);
	}
	public function debug(string $moduleName){
		core::setError();
		$module = core::loadModule($moduleName);
		$config = $this->getConfig($moduleName);
		$variable = get_object_vars($module);
		$anonymousvariable = [];
		foreach((array)$module as $key => $value){
			preg_match_all("/(class@anonymous(.*)0x[A-Z0-9]{8})(.*)/im", $key, $matches, PREG_SET_ORDER, 0);
			if(count($matches) > 0 and $matches[0][3]<>null)
				$anonymousvariable[$matches[0][3]] = $value;
		}
		$return = ['name' => $moduleName, 'config' => $config, 'variable [private]' => $anonymousvariable, 'variable [public]' => $variable, 'function' => get_class_methods($module), 'fileList' => core::$library->file->dirToArray($config['path'])];
		return $return;
	}
};
?>