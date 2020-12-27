<?php
return new class(){
	public $__list = [];
	public function __get($name){
		$path = core::$path['library'].$name;
		if(is_file($path.'.php')){
			array_push($this->__list, $name);
			return include_once($path.'.php');
		}elseif(is_dir($path)){
			$initPath = $path.'/init.php';
			if(file_exists($initPath))
				return include_once($initPath);
		}
		core::setError(1, 'library file not found');
		trigger_error($name.' library not found', E_USER_ERROR);
		return false;
	}
	public function __list(array $config = []){
		if(!isset($config['version']))
			$config['version'] = false;
		$return = [];
		$scanDir = array_diff(scandir(core::$path['library']), ['.', '..']);
		foreach($scanDir as $name){
			$path = core::$path['library'].$name;
			if(is_file($path)){
				if(substr($name, strlen($name)-4) <> '.php') continue;
				$libName = str_replace('.php', '', $name);
			}elseif(is_dir($path)){
				if(!file_exists($path.'/init.php')) continue;
				$libName = $name;
			}
			$libVersion = $config['version']===true?(isset(core::$library->$libName->version)?core::$library->{$libName}->version:'-'):null;
			array_push($return, ['name' => $libName, 'version' => $libVersion]);
		}
		return $return;
	}
}
?>