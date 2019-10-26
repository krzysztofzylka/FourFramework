<?php
//by Krzysztof Żyłka
//fourframework.hmcloud.pl
class core{
	public static $error = [-1, '', '']; //last error
	public static $info = ['version' => '0.2.3a Alpha','releaseDate' => '26.10.2019','reversion' => '']; //info
	public static $private = []; //private data
	public static $path = ['core' => 'core/', 'controller' => 'controller/', 'view' => 'view/', 'model' => 'model/', 'module' => 'module/', 'base' => 'core/base/', 'temp' => 'core/base/temp/']; //all path
	public static $controller = []; //all controller array
	public static $model = []; //all model array
	public static $module = []; //all module array
	public static $module_add = []; //array add data for module
	public static $library = []; //class library
	public static $debug = [ //debug array
		'showError' => True, //show error
		'saveError' => True, //save error to file
		'showCoreError' => True //show core error
	];
	public static function init(){ //init function
		self::setError(); //reset error table
		//init debug
		if(self::$debug['showError']) //show error
			error_reporting(E_ALL);
		//get reversion path
		while(!file_exists(self::$info['reversion']."core/core.php")) //loop search core.php file
			self::$info['reversion'] .= '../'; //add reversion to path
		//add reversion to path and create dir
		foreach(self::$path as $name => $value){ //path loop
			self::$path[$name] = self::$info['reversion'].$value; //add reversion to path
			if(!file_exists(self::$path[$name])) //if not exists
				mkdir(self::$path[$name], 0700, true); //create dir
		}
		//debug
		if(self::$debug['saveError']){ //save error to file
			ini_set("log_errors", 1); //set error in ini
			$path = self::$path['base'].'log/'; //create php log path
			if(!file_exists($path)) //if not exists
				mkdir($path, 0700, true); //create dir
			$path .= 'php_error.log'; //add filename to path
			ini_set("error_log", $path); //set error log path to ini
		}
		//include library class
		self::$library = include('library.php');
		return true; //return success
	}
	public static function setError(int $number=-1, string $name='', string $description=''){ //set error
		self::$error = [$number, $name, $description]; //set error
		if(self::$debug['showCoreError'] == true and $number > -1) //if enabled and error number > -1
			echo '<b>Core error:</b> ('.$number.') [<i>'.$name.'</i>] '.$description; //show error
		return false; //return false
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
		if(in_array($name, array_keys(self::$model))) //search in array
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
		if(in_array($name, array_keys(self::$module))) //search 
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