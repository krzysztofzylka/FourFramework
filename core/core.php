<?php
class core{
	public static $error = [-1, '', '']; //last error
	public static $info = ['version' => '1.2 Alpha','releaseDate' => '18.07.2019','reversion' => '']; //info
	public static $private = []; //private data
	public static $path = ['core' => 'core/', 'controller' => 'controller/', 'view' => 'view/', 'model' => 'model/', 'module' => 'module/', 'base' => 'core/base/', 'temp' => 'core/base/temp/'];
	public static $controller = []; //all controller array
	public static $model = []; //all model array
	public static $module = []; //all module array
	public static $module_add = []; //array add data for module
	public static $library = []; //class library
	public static function init(){ //init function
		self::setError(); //reset error table
		//get reversion path
		while(!file_exists(self::$info['reversion']."core/core.php"))
			self::$info['reversion'] .= '../';
		//add reversion to path and create dir
		foreach(self::$path as $name => $value){
			self::$path[$name] = self::$info['reversion'].$value; //add reversion to path
			if(!file_exists(self::$path[$name])) //if not exists
				mkdir(self::$path[$name], 0444, true); //create dir
		}
		//include library class
		self::$library = include('library.php');
		return true; //return success
	}
	public static function setError(int $number=-1, string $name='', string $description=''){ //set error
		self::$error = [$number, $name, $description]; //set error
		return false;
	}
	public static function loadView(string $name){ //load View file
		self::setError(); //reset error
		$name = htmlspecialchars(basename($name)); //protect $name
		$path = self::$path['view'].$name.'.php'; //create file path
		if(!file_exists($path)) //check file exists
			return self::setError(1, 'file not exists', 'file not exists in path: ('.$path.')'); //return error 1
		return include($path); //include view file
	}
	public static function loadController(string $name){ //load Controller class
		self::setError(); //reset error
		$name = htmlspecialchars(basename($name)); //protect $name
		if(in_array($name, array_keys(self::$controller)))
			return self::setError(3, 'the class has already been loaded', ''); //return error 3
		$path = self::$path['controller'].$name.'.php'; //create file path
		if(!file_exists($path)) //check file exists
			return self::setError(1, 'file not exists', 'file not exists in path: ('.$path.')'); //return error 1
		$includeClass = include($path); //include class to var
		if(!is_object($includeClass)) //check include data
			return self::setError(2, 'the class is incorrect', 'the class does not return an object'); //return error 2
		self::$controller[$name] = $includeClass; //add class to array
		return $includeClass; //return class
	}
	public static function loadModel(string $name){ //load Model class
		self::setError(); //reset error
		$name = htmlspecialchars(basename($name)); //protect $name
		if(in_array($name, array_keys(self::$model)))
			return self::setError(3, 'the class has already been loaded', ''); //return error 3
		$path = self::$path['model'].$name.'.php'; //create file path
		if(!file_exists($path)) //check file exists
			return self::setError(1, 'file not exists', 'file not exists in path: ('.$path.')'); //return error 1
		$includeClass = include($path); //include class to var
		if(!is_object($includeClass)) //check include data
			return self::setError(2, 'the class is incorrect', 'the class does not return an object'); //return error 2
		self::$model[$name] = $includeClass; //add class to array
		return $includeClass; //return class
	}
	public static function loadModule(string $name){ //load module
		self::setError(); //reset error
		$name = htmlspecialchars(basename($name)); //protect $name
		if(in_array($name, array_keys(self::$module)))
			return self::setError(1, 'the class has already been loaded', ''); //return error 1
		$path = self::$path['module'].$name.'/'; //create file path
		if(!file_exists($path.'config.php')) //check config file
			return self::setError(2, 'config file config.php not found', ''); //return error 2
		$config = include($path.'config.php'); //load config to var
		$config['name'] = $name; //add name to config
		$config['path'] = $path; //add path to config
		self::$module_add[$name]['config'] = $config; //add config to data array
		if(!is_array($config)) //check config
			return self::setError(3, 'config file config.php error', 'the data returned is not a table'); //return error 3
		if(isset($config['include']) and is_array($config['include'])) //include adding files
			foreach($config['include'] as $name) //loop
				include($path.$name); //include
		if(isset($config['moduleFile']) and !file_exists($path.$config['moduleFile'])) //check module file
			return self::setError(4, 'module file not found', $path.$config['moduleFile']); //return error 4
		else //module file isset
			self::$module[$name] = include($path.$config['moduleFile']); //include to array
		return self::$module[$name]; //return module class
	}
}
?>